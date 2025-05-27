<?php

namespace EhsanNosair\Chativel;

use Carbon\Carbon;
use EhsanNosair\Chativel\Events\ChativelConnected;
use EhsanNosair\Chativel\Models\Chativel\Conversation;
use EhsanNosair\Chativel\Models\Chativel\ChatableStatus;

class Chativel {
    public function chatablesSearch(string $searchTerm)
    {
        $results = collect();
        $currentChatable = auth()->user();

        foreach (config('chativel.chatables') as $model) {
            $results = $results->merge(
                $model::query()
                    ->where(function ($queryBuilder) use ($searchTerm, $model) {
                        foreach (method_exists($model, 'searchableColumns') ? $model::searchableColumns() : [config('chativel.deafult_search_column')] as $column) {
                            $queryBuilder->orWhere($column, 'LIKE', "%{$searchTerm}%");
                        }
                    })
                    ->when($currentChatable instanceof $model, function ($query) use ($currentChatable) {
                        $query->where('id', '!=', $currentChatable->id);
                    })
                    ->get()
            );
        }

        return $results;
    }

    public function getConversationWith($chatableType, $chatableId)
    {
        $conversation = Conversation::whereHas('participants', fn($query) => 
            $query->where('participant_type', $chatableType)
                  ->where('participant_id', $chatableId)
        )
        ->whereHas('participants', fn($query) => 
            $query->where('participant_type', auth()->user()::class)
                  ->where('participant_id', auth()->id())
        )
        ->first();

        if (!$conversation) {
            $conversation = Conversation::create([]);
            $conversation->participants()->createMany([
                [
                    'participant_type' => $chatableType,
                    'participant_id' => $chatableId,
                    'joined_at' => Carbon::now()
                ],
                [
                    'participant_type' => auth()->user()::class,
                    'participant_id' => auth()->id(),
                    'joined_at' => Carbon::now()
                ],
            ]
            );
        }

        return $conversation;
    }

    public function getOtherParticipant($selectedConversation)
    {
        return $selectedConversation->participants
            ->reject(function ($participant) {
                return $participant->participant_id == auth()->id() && $participant->participant_type == auth()->user()::class;
            })
            ->first();
    }

    public function sendMessage($data, $conversation, $otherParticipant)
    {
        $newMessage = $conversation->messages()->create([
            'sender_type' => auth()->user()::class,
            'sender_id' => auth()->id(),
            'message' => $data['message'] ?? null,
        ]);

        $lastAttachment = null;
        foreach ($data['attachments'] ?? [] as $file) {
            $collection = $this->getAttachmentType($file->getMimeType());
            $lastAttachment = $newMessage->addMedia($file->getRealPath()) 
                ->usingFileName($file->getClientOriginalName())
                ->toMediaCollection($collection); 
        }

        $newMessage->statuses()->create([
            'user_type' => $otherParticipant->chatable::class,
            'user_id' => $otherParticipant->chatable->id,
        ]);

        // update conversation last action info
        $this->updateLastAction($conversation, $data['message'] ?? null, $lastAttachment, $newMessage->created_at);

        return $newMessage;
    }

    protected function updateLastAction($conversation, $message, $lastAttachment, $message_date)
    {
        $last_action_type = null;
        $last_action_content = null;
        if ($lastAttachment) {
            $last_action_type = $this->getAttachmentType($lastAttachment->mime_type);
        }elseif ($message) {
            $last_action_type = 'text';
            $last_action_content = $message;
        }

        if ($last_action_type || $last_action_content) {
            $conversation->update([
                'last_action_type' => $last_action_type, 
                'last_action_content' => $last_action_content,
                'last_action_participant_type' => auth()->user()::class,
                'last_action_participant_id' => auth()->id(),
                'last_action_date' => $message_date
            ]);
        }
    }

    public function getAttachmentType($mimeType)
    {            
        if (str_starts_with($mimeType, 'image/')) {
            $type = 'image';
        } elseif (str_starts_with($mimeType, 'video/')) {
            $type = 'video';
        } else {
            $type = 'file';
        }

        return $type;
    }

    public function markAllMessagesAsRead($conversation)
    {
        $conversation->messagesStatuses()
            ->where('user_type', auth()->user()::class)->where('user_id', auth()->id())
            ->update(['read_at' => Carbon::now()]);
        
        $conversation->messages()->whereDoesntHave('statuses', fn($query) => 
            $query->whereNull('read_at')
        )->update(['is_read' => true]);
    }

    public function markMessageAsRead($message)
    {
        $message->statuses()->where('user_type', auth()->user()::class)->where('user_id', auth()->id())
            ->update(['read_at' => Carbon::now()]);

        $atLeastOneNotRead = $message->statuses()->whereNull('read_at')->first();
        if (!$atLeastOneNotRead) {
            $message->update(['is_read' => true]);
        }
    }

    public function myConversationsPaginator($page)
    {
        $conversations = Conversation::whereNotNull('last_action_date')
            ->whereHas('participants', function($query){
                $query->where('participant_type', auth()->user()::class)->where('participant_id', auth()->id());
            })
            ->with(['participants' => function($query){
                $query->where(function($subquery){
                    $subquery->where('participant_type', '!=', auth()->user()::class)
                          ->orWhere('participant_id', '!=', auth()->id());
                })->with(['chatable']);
            }])
            ->latest('last_action_date')
            ->paginate(10, ['*'], 'page', $page);

        return $conversations;
    }

    public function isParticipant($conversation)
    {
        return $conversation->participants()->where('participant_type', auth()->user()::class)->where('participant_id', auth()->id())->exists();
    }

    public function sayIamConnected()
    {
        ChatableStatus::updateOrCreate([
            'model_type' => auth()->user()::class,
            'model_id' => auth()->id(),
        ],[
            'last_seen' => Carbon::now()
        ]);

        broadcast(new ChativelConnected())->toOthers();
    }
}
