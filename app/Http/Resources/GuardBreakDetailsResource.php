<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class GuardBreakDetailsResource extends JsonResource
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
            'start_time' => !empty($this->start_time) ? timeStampToAus($this->start_time) : '',
            'end_time' => !empty($this->end_time) ? timeStampToAus($this->end_time) : '',
            'notes' => !empty($this->notes) ? $this->notes : '',
            'inform_to' => !empty($this->inform_to) ? $this->inform_to : '',
        ];
    }
}
