<?php

namespace EhsanNosair\Chativel\Models\Chativel;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ChatableStatus extends Model
{
    use SoftDeletes;

    protected $guarded = [];

    protected $casts = [
        'last_seen' => 'datetime'
    ]; 
}
