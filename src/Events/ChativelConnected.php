<?php

namespace EhsanNosair\Chativel\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ChativelConnected implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public string $type;
    /**
     * Create a new event instance.
     */
    public function __construct()
    {
        $this->type = auth()->user()::class;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new Channel('chativel.chatables.' . auth()->id()),
        ];
    }

    public function broadcastAs(): string
    {
        return 'connected';
    }
}
