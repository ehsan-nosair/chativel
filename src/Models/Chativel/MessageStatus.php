<?php

namespace EhsanNosair\Chativel\Models\Chativel;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MessageStatus extends Model
{
    use SoftDeletes;

    protected $guarded = [];

    public function message()
    {
        return $this->belongsTo(Message::class);
    }

    public function user()
    {
        return $this->morphTo();
    }
}
