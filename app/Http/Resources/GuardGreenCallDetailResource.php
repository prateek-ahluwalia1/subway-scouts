<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class GuardGreenCallDetailResource extends JsonResource
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
            'before_time' => $this->before_time,
            'status' => $this->status,
            'coordinates' => $this->coordinates,
            'send_time' => !empty($this->send_time) ? timeStampToAus($this->send_time) : '',
            'response_time' => !empty($this->response_time) ? timeStampToAus($this->response_time) : ''    
          ];
    }
}
