<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CurrentWeekUnCoverdShiftResource extends JsonResource
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
            'start' => !empty($this->start) ? usaToAusTime($this->start) : 'N/A',
            'end' => !empty($this->end) ? usaToAusTime($this->end) : 'N/A',
            'date' => !empty($this->start) ? usaToAus($this->start) : 'N/A',
            'site_name' => !empty($this->site) ? $this->site->site_name : 'N/A',
            'site_description' => !empty($this->site) ? $this->site->site_description : 'N/A',
        ];
    }
}
