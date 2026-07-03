<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class GuardWelFareCallResource extends JsonResource
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
            'id' => $this->id,
            'response_time' => !empty($this->response_time) ? timeStampToAus($this->response_time) : '',
            'send_time' => !empty($this->send_time) ? timeStampToAus($this->send_time) : '',
            'coordinates' => !empty($this->coordinates) ? $this->coordinates : '',
            'response' => !empty($this->status) ? $this->status : '',
        ];
    }
}
