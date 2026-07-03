<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class MoreContactsResource extends JsonResource
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
            'more_contact_id' => $this->id,
            'more_email' => $this->more_email,
            'more_phone' => $this->more_phone,
            'more_notes' => $this->more_notes,
        ];
        
    }
}
