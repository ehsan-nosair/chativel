<?php

namespace EhsanNosair\Chativel\Traits;

use EhsanNosair\Chativel\Models\Chativel\ChatableStatus;

trait Chatable
{
    public static function searchableColumns(): array
    {
        return [
            'name',
        ];
    }

    public function getDisplayColumnAttribute()
    {
        return $this->name;
    }

    public function lastSeen()
    {
        return $this->morphOne(ChatableStatus::class, 'model');
    }
}
