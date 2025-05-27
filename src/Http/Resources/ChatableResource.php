<?php

namespace EhsanNosair\Chativel\Http\Resources;

use Illuminate\Support\Str;
use Illuminate\Http\Resources\Json\JsonResource;

class ChatableResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'type' => get_class($this->resource),
            'display_name' => $this->display_column,
            'avatar' => "https://ui-avatars.com/api/?name=" . Str::title($this->display_column),
        ];
    }
}
