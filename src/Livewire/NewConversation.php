<?php

namespace EhsanNosair\Chativel\Livewire;

use EhsanNosair\Chativel\Facades\Chativel;
use EhsanNosair\Chativel\Pages\ChativelPage;
use Livewire\Component;

class NewConversation extends Component
{
    public $searchForNewConversation = '';

    public $chatables;

    public function mount()
    {
        $this->chatables = collect();
    }

    public function updatedSearchForNewConversation()
    {
        $this->chatables = collect();
        $this->chatables = Chativel::chatablesSearch($this->searchForNewConversation);
    }

    public function goToConversation(string $chatableType, int $chatableId)
    {
        $this->dispatch('close-modal', id: 'new-conversation-modal');
        $conversation = Chativel::getConversationWith($chatableType, $chatableId);
        $this->redirect(ChativelPage::getUrl(parameters: ['conversationId' => $conversation->id]), navigate: true);
    }

    public function render()
    {
        return view('chativel::chativel.livewire.new-conversation');
    }
}
