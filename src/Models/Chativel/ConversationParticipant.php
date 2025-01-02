<?php

namespace EhsanNosair\Chativel\Models\Chativel;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ConversationParticipant extends Model
{
    use SoftDeletes;

    protected $guarded = [];

    public function conversation()
    {
        return $this->belongsTo(Conversation::class);
    }

    public function chatable()
    {
        return $this->morphTo('chatable', 'participant_type', 'participant_id');
    }
}
