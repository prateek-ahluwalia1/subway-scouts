<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class GreenCallResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        //return parent::toArray($request);
        return [
            'first_green_call' => !empty($this->before_time) && ($this->before_time == 120) ? usaToAusTime($this->created_at) : '' ,
            'second_green_call' => !empty($this->before_time) && ($this->before_time == 30) ? usaToAusTime($this->created_at) : '' ,
        ];
    }
}
