<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class GuardShiftActivityResource extends JsonResource
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
            'activity' => !empty($this->activity) ? $this->activity : 'N/A',
            'type' => !empty($this->type) ? $this->type : 'N/A',
            'activity_time' => !empty($this->activity_time) ? timeStampToAus($this->activity_time) : 'N/A',
        ];
    }
}
