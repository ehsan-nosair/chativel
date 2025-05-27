<?php

namespace EhsanNosair\Chativel\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ConversationResource extends JsonResource
{
    protected $participant_type;
    protected $participant_id;

    public function __construct($resource, $participant_type = null, $participant_id = null)
    {
        parent::__construct($resource);
        $this->participant_type = $participant_type;
        $this->participant_id = $participant_id;
    }

    public function toArray($request)
    {
        $data = [
            'id' => $this->id,
            'name' => $this->display_name,
            'avatar' => $this->avatar,
            'last_action' => $this->last_message,
            'last_action_date' => $this->formatted_last_action_date,
        ];

        if ($this->participant_type && $this->participant_id) {
            $data = array_merge($data, [
                'chatted_user_type' => $this->participant_type,
                'chatted_user_id' => $this->participant_id
            ]);
        }

        return $data;
    }
}
