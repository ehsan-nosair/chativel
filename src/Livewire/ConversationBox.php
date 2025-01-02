<?php

namespace EhsanNosair\Chativel\Livewire;

use DateTime;
use Carbon\Carbon;
use Livewire\Component;
use Filament\Forms\Form;
use Livewire\Attributes\On;
use App\Events\ChativelPing;
use App\Events\ChativelPong;
use Livewire\WithFileUploads;
use Livewire\Attributes\Computed;
use Illuminate\Support\Collection;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Components\Textarea;
use Illuminate\Support\Facades\Storage;
use Filament\Notifications\Notification;
use Filament\Forms\Components\FileUpload;
use EhsanNosair\Chativel\Facades\Chativel;
use Filament\Forms\Concerns\InteractsWithForms;
use EhsanNosair\Chativel\Models\Chativel\Message;
use EhsanNosair\Chativel\Events\ChativelMessageRead;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use EhsanNosair\Chativel\Events\ChativelMessageCreated;
use EhsanNosair\Chativel\Events\ChativelReadAllMessages;
use EhsanNosair\Chativel\Models\Chativel\ChatableStatus;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;

class ConversationBox extends Component implements HasForms
{
    use InteractsWithForms;

    public $selectedConversation;
    public $otherParticipant;
    public $lastSeen;

    public Collection $conversationMessages;
    public $currentPage = 1;

    public $message = '';
    public $attachments = [];
    public $attachment_file_names = [];

    public function getListeners()
    {
        $listeners = [];

        $listeners[] = 'checkStatus';

        if ($this->selectedConversation) {
            $listeners["echo:chativel.conversations,.message.created"] = 'newMessageListener';
            $listeners["echo:chativel.conversations.{$this->selectedConversation->id},.message.read"] = 'messageReadListener';
            $listeners["echo:chativel.conversations.{$this->selectedConversation->id},.message.read.all"] = 'messageReadAllListener';
        }

        if ($this->otherParticipant) {
            $listeners["echo:chativel.chatables.{$this->otherParticipant->participant_id},.connected"] = 'otherConnected';
        }

        return $listeners;
    }

    public function mount() {
        if ($this->selectedConversation) {
            $this->lastSeen = $this->otherParticipant->chatable->lastSeen;
            if ($this->selectedConversation) {
                $this->conversationMessages = collect();
                $this->loadMoreMessages();
                Chativel::markAllMessagesAsRead($this->selectedConversation);
                broadcast(new ChativelReadAllMessages($this->selectedConversation->id))->toOthers();
            }
        }
    }

    public function form(Form $form): Form
    {
        return $form->schema([
            Textarea::make('message')
                ->hiddenLabel()
                ->placeholder(__('Type a message...'))
                ->rows(1)
                ->autosize()
                ->grow(true)
                ->maxLength(config('chativel.max_message_length'))
                ->extraAttributes([
                    'class' => 'chativel-scrollbar',
                    'style' => 'max-height: 120px; overflow-y: auto;',
                    'x-data' => '{}', 
                    'x-on:keydown.enter.prevent' => '$wire.sendMessage()', 
                ]),
            SpatieMediaLibraryFileUpload::make('attachments')   
                ->hiddenLabel()
                ->multiple() 
                ->panelLayout('grid')
                ->imageEditor(fn() => config('chativel.image_editor'))
                ->directory(fn() => config('chativel.attachments_store_directory') ?? 'attachments')
                ->acceptedFileTypes(config('chativel.allowed_mime_types'))
                ->maxSize(config('chativel.max_attachment_size'))
                ->minSize(config('chativel.min_attachment_size'))
                ->maxFiles(config('chativel.max_attachments_count'))
                ->minFiles(config('chativel.min_attachments_count'))
                ->extraAttributes([
                    'class' => 'chativel-file-upload ',
                ]),
        ]);
    }
    
    public function sendMessage()
    {
        if ($this->message == '' && count($this->attachments) == 0) {
            Notification::make() 
                ->title(__('Empty message cannot be sent.'))
                ->danger()
                ->send(); 
            return;
        }
        $data = $this->form->getState();
        $data['attachments'] = $this->attachments;
        $newMessage = Chativel::sendMessage($data, $this->selectedConversation, $this->otherParticipant);

        $this->conversationMessages->prepend($newMessage);

        $this->dispatch('messageFromMe');

        broadcast(new ChativelMessageCreated($this->selectedConversation->id, $newMessage->id))->toOthers();

        $this->dispatch('close-modal', id: 'attachments-modal');

        $this->reset('message', 'attachments');
    }

    public function loadMoreMessages()
    {
        $this->conversationMessages->push(...$this->paginator->getCollection());

        $this->currentPage = $this->currentPage + 1;
    }

    #[Computed()]
    public function paginator()
    {
        return $this->selectedConversation->messages()->with(['statuses', 'media'])->latest()->paginate(10, ['*'], 'page', $this->currentPage);
    }

    public function newMessageListener($data)
    {
        if ($this->selectedConversation->id == $data['conversationId']) {
            $newMessage = Message::with(['statuses', 'media'])->find($data['messageId']);
    
            Chativel::markMessageAsRead($newMessage);
    
            broadcast(new ChativelMessageRead($this->selectedConversation->id, $newMessage->id))->toOthers();
    
            $this->conversationMessages->prepend($newMessage);
        }
    }

    public function messageReadListener($data)
    {
        if (isset($data['messageId'])) {
            if ($this->selectedConversation->is_group) {
                $atLeastOneNotRead = $this->conversationMessages->where('id', $data['messageId'])->first()
                                          ?->statuses()->whereNull('read_at')->first();
    
                if (!$atLeastOneNotRead) {
                    $this->conversationMessages->where('id', $data['messageId'])->each(function ($message) {
                        $message['is_read'] = true; 
                    });
                }
            }
            else
            {
                $this->conversationMessages->where('id', $data['messageId'])->each(function ($message) {
                    $message['is_read'] = true; 
                });
            }
        }
    }

    public function messageReadAllListener()
    {
        if ($this->selectedConversation->is_group) {
            $this->selectedConversation->messages()->whereDoesntHave('statuses', fn($query) => 
                $query->whereNull('read_at')
            )->each(function ($message) {
                $this->conversationMessages->where('id', $message->id)->each(function ($message) {
                    $message['is_read'] = true; 
                });
            });
        }
        else{
            $this->conversationMessages->each(function ($message) {
                $message['is_read'] = true; 
            });
        }
    }

    public function otherConnected($data)
    {
        if ($data['type'] == $this->otherParticipant->participant_type) {
            if ($this->lastSeen) {
                $this->lastSeen->update(['last_seen' => Carbon::now()]);
            }else{
                $this->lastSeen = ChatableStatus::create([
                    'model_type' => $this->otherParticipant->participant_type,
                    'model_id' => $this->otherParticipant->participant_id,
                    'last_seen' => Carbon::now()
                ]);
            }
        }
    }

    public function checkStatus()
    {
        if ($this->selectedConversation) {
            if ($this->lastSeen) {
                $this->lastSeen = $this->otherParticipant->chatable->lastSeen;
            }
        }
    }

    public function downloadFile($mediaId)
    {
        $media = Media::findOrFail($mediaId);
        $mediaPath = $media->getPath();
    
        if (file_exists($mediaPath)) {
            return response()->download($mediaPath);
        } else {
            session()->flash('error', 'File not found.');
        }
    }

    public function render()
    {
        return view('chativel::chativel.livewire.conversation-box');
    }
}
