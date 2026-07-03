<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class BeforeUpdateCustomerResource extends JsonResource
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
            'Name' => !empty($this->name) ? $this->name : '',
            'Email' => !empty($this->email) ? $this->email : '',
            'Phone' => !empty($this->phone) ? $this->phone : '',
            'Address' => !empty($this->address) ? $this->address : '',
            'City' => !empty($this->city) ? $this->city : '',
            'State' => !empty($this->state) ? $this->state : '',
            'Postal Code' => !empty($this->postal_code) ? $this->postal_code : '',
            'Status' => !empty($this->status) ? $this->status : '',
            'Apply Date' => !empty($this->apply_date) ? usaToAus($this->apply_date) : '',
        ];
    }
}
