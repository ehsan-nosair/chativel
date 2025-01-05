<?php

namespace EhsanNosair\Chativel\Pages;

use Filament\Pages\Page;
use EhsanNosair\Chativel\Facades\Chativel;
use Illuminate\Contracts\Support\Htmlable;
use EhsanNosair\Chativel\Events\ChativelConnected;
use EhsanNosair\Chativel\Models\Chativel\Conversation;

class ChativelPage extends Page
{
    protected static string $view = 'chativel::chativel.filament.pages.chativel-page';

    public $selectedConversation;
    public $otherParticipant;
    protected $listeners = [
        'boradcastStatus'
    ];

    public static function getSlug(): string
    {
        return config('chativel.slug') . '/{conversationId?}';
    }

    public function getTitle() : string
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
        broadcast(new ChativelConnected())->toOthers();
        if ($conversationId) {
            $this->selectedConversation = Conversation::with(['participants', 'participants.chatable'])->findOrFail($conversationId);
            $this->otherParticipant = Chativel::getOtherParticipant($this->selectedConversation);
        }
    }

    public function boradcastStatus()
    {
        broadcast(new ChativelConnected())->toOthers();
    }
}
