<?php

namespace EhsanNosair\Chativel\Models\Chativel;

use Carbon\Carbon;
use EhsanNosair\Chativel\Facades\Chativel;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Conversation extends Model
{
    use SoftDeletes;

    protected $guarded = [];

    protected $casts = [
        'last_action_date' => 'datetime',
    ];

    protected $fillable = [];

    public function lastMessage(): Attribute
    {
        return Attribute::make(
            get: function () {
                switch ($this->last_action_type) {
                    case 'text':
                        return $this->last_action_content;
                        break;
                    case 'image':
                        return __('image');
                        break;
                    case 'video':
                        return __('video');
                        break;
                    case 'file':
                        return __('file');
                        break;
                    default:
                        return '';
                        break;
                }
            },
        );
    }

    public function displayName(): Attribute
    {
        $chatable = Chativel::getOtherParticipant($this)->chatable;

        return Attribute::make(
            get: fn () => $this->is_group ? $this->name : ($chatable->displayColumn ?? $chatable[config('chativel.deafult_display_column')])
        );
    }

    public function avatar(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->is_group ? asset('vendor/chativel/group-avatar.png') : ('https://ui-avatars.com/api/?name='.Str::title($this->display_name))
        );
    }

    public function formattedLastActionDate(): Attribute
    {
        return Attribute::make(
            get: function () {
                $carbonDate = Carbon::parse($this->last_action_date)->setTimeZone(config('chativel.timezone', 'app.timezone'));

                if ($carbonDate->isToday()) {
                    // Format for the current day: 04:50 PM
                    return $carbonDate->format('h:i A');
                } elseif ($carbonDate->isYesterday()) {
                    // Format for yesterday: "Yesterday"
                    return __('Yesterday');
                } else {
                    // Format for dates older than yesterday: 12/24/2024
                    return $carbonDate->format('m/d/Y');
                }
            }
        );
    }

    public function participants(): HasMany
    {
        return $this->hasMany(ConversationParticipant::class);
    }

    public function messages(): HasMany
    {
        return $this->hasMany(Message::class);
    }

    public function messagesStatuses()
    {
        return $this->hasManyThrough(MessageStatus::class, Message::class);
    }
}
