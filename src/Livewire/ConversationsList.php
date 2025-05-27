<?php

namespace EhsanNosair\Chativel\Livewire;

use EhsanNosair\Chativel\Facades\Chativel;
use EhsanNosair\Chativel\Pages\ChativelPage;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Component;

class ConversationsList extends Component
{
    public $selectedConversation;

    public Collection $conversations;

    public $currentPage = 1;

    public function getListeners()
    {
        $listeners = [];

        $listeners[] = 'messageFromMe';
        $listeners['echo:chativel.conversations,.message.created'] = 'newMessageListener';

        return $listeners;
    }

    public function mount()
    {
        $this->conversations = collect();
        $this->loadMoreConversations();
    }

    public function loadMoreConversations()
    {
        $this->conversations->push(...$this->paginator->getCollection());

        $this->currentPage = $this->currentPage + 1;
    }

    #[Computed()]
    public function paginator()
    {
        return Chativel::myConversationsPaginator($this->currentPage);
    }

    public function goToConversation(int $conversationId)
    {
        $this->redirect(ChativelPage::getUrl(parameters: ['conversationId' => $conversationId]), navigate: true);
    }

    public function newMessageListener($data)
    {
        $this->conversations = collect();
        $this->currentPage = 1;
        $this->loadMoreConversations();
    }

    public function messageFromMe()
    {
        $this->conversations = collect();
        $this->currentPage = 1;
        $this->loadMoreConversations();
    }

    public function render()
    {
        return view('chativel::chativel.livewire.conversations-list');
    }

    public function placeholder()
    {
        return view('chativel::chativel.placeholders.conversation-list');
    }
}
