<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class BeforeUpdateCardCommentResource extends JsonResource
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
            'Card' => !empty($this->card) ? $this->card->description : '',
            'Comment' => !empty($this->comment) ? $this->comment : '',
            'Admin Name' => !empty($this->admin_id) ? getAdminName($this->admin_id) : ''
        ];
    }
}
