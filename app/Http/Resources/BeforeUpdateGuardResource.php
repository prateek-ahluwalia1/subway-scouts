<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class BeforeUpdateGuardResource extends JsonResource
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
            'first_name' => !empty($this->first_name) ? $this->first_name : '', 
            'middle_name' => !empty($this->middle_name) ? $this->middle_name : '', 
            'last_name' => !empty($this->last_name) ? $this->last_name : '', 
            'email' => !empty($this->email) ?  $this->email : '',
            'state' => !empty($this->state) ?  $this->state : '',
            'phone' => !empty($this->phone) ?  $this->phone : '',
            'address' => !empty($this->address) ?  $this->address : '',
            'updated_at' => !empty($this->updated_at) ?  usaToAus($this->updated_at) : '',
        ];
    }
}
