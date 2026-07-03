<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class GuardJobTrakerResource extends JsonResource
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
            'created_at' => $this->created_at,
            'event_time' => $this->event_time,
            'distance' => $this->distance,
            'coordinates' => $this->coordinates,
            'address' =>   !empty($this->coordinates) ?  coordinates_to_address($this->coordinates) : 'N/A',
        ];
    }
}
