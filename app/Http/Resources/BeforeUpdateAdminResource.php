<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class BeforeUpdateAdminResource extends JsonResource
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
            //'Image' => !empty($this->image) ? $this->image : '',
            'Phone' => !empty($this->phone) ? $this->phone : '',
            'Status' => !empty($this->status) ? $this->status : '',
            'State' => !empty($this->state) ? $this->state : '',
        ];
    }
}
