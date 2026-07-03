<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class BeforeUpdateUserResource extends JsonResource
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
            'name' => !empty($this->name) ? $this->name : '',
            'email' => !empty($this->email) ? $this->email : '',
            'phone' => !empty($this->phone) ? $this->phone : '',
            'status' => !empty($this->status) ? $this->status : '',
        ];
    }
}
