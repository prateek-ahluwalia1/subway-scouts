<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class EmailHistoryResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'lead_id' => $this->lead_id,
            'user_id' => $this->user_id,
            'subject' => $this->subject,
            'message' => $this->message,
            'to' => $this->to ? json_decode($this->to) : null,
            'attachments' => $this->attachments ? json_decode($this->attachments) : null,
            'user' => $this->user,
            'lead' => $this->lead,
            'created_at' => $this->created_at,
        ];
    }
}
