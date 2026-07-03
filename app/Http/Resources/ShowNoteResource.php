<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ShowNoteResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return[
            'title' => $this->title, 
            'note' => $this->note,
            'send_to' => $this->send_to,
            'created_by' => getAdminName($this->created_by),
            'mark_as_read' => json_decode($this->mark_as_read),
            'read_by' => $this->mark_as_read != null ? in_array($this->user_id, json_decode($this->mark_as_read)) : false
        ];
    }
}
