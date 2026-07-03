<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class BeforeUpdateStagesResource extends JsonResource
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
            'Scrumboard'  => !empty($this->lists) ? $this->lists->title : '',
            'Stage Title'  => !empty($this->title) ? $this->title : '',
        ];

    }
}
