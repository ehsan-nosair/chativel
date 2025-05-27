<?php

namespace EhsanNosair\Chativel\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

class MessageResource extends JsonResource
{
    public function toArray($request)
    {
        $currentUser = auth()->user();

        return [
            'id' => $this->id,
            'from_me' => $this->sender_type == $currentUser->getMorphClass() && $this->sender_id == $currentUser->getKey(),
            'message' => $this->message,
            'created_at' => Carbon::parse($this->created_at)->setTimeZone(config('chativel.timezone', 'app.timezone'))->format('g:i A'),
            'is_read' => $this->is_read,
            'images' => $this->getMedia('image'),
            'files' => $this->getMedia('file'),
        ];
    }
}
