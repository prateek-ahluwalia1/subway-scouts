<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class AvailableGuardResource extends JsonResource
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
            'prev_shift' => $this->prev_shift,
            'next_shift' => $this->next_shift,
            'first_name' => !empty($this->first_name) ? $this->first_name : '',
            'middle_name' => !empty($this->middle_name) ? $this->middle_name : '',
            'last_name' => !empty($this->last_name) ? $this->last_name :'',
            'phone' => !empty($this->phone) ? $this->phone :'',
            'profile_image' => !empty($this->profile_image) ? returnImgPath('guard', $this->profile_image) :'',
        ];
    }
}
