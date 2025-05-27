<?php

namespace EhsanNosair\Chativel\Pages;

use EhsanNosair\Chativel\Facades\Chativel;
use EhsanNosair\Chativel\Models\Chativel\Conversation;
use Filament\Pages\Page;

class ChativelPage extends Page
{
    protected static string $view = 'chativel::chativel.filament.pages.chativel-page';

    public $selectedConversation;

    public $otherParticipant;

    protected $listeners = [
        'boradcastStatus',
    ];

    public static function getSlug(): string
    {
        return config('chativel.slug').'/{conversationId?}';
    }

    public function getTitle(): string
    {
        return '';
    }

    public static function getNavigationLabel(): string
    {
        return __(config('chativel.navigation_label'));
    }

    public static function getNavigationIcon(): string
    {
        return config('chativel.navigation_icon');
    }

    public function mount(?int $conversationId = null)
    {
        Chativel::sayIamConnected();
        if ($conversationId) {
            $this->selectedConversation = Conversation::with(['participants', 'participants.chatable'])->findOrFail($conversationId);
            $this->otherParticipant = Chativel::getOtherParticipant($this->selectedConversation);
        }
    }

    public function boradcastStatus()
    {
        Chativel::sayIamConnected();
    }
}
