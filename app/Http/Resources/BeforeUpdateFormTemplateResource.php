<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class BeforeUpdateFormTemplateResource extends JsonResource
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
            'Title' => !empty($this->title) ? $this->title : 'N/A',
            'Type' => !empty($this->type) ? $this->type : 'N/A',
            'Body' => !empty($this->body) ? strip_tags($this->body) : 'N/A',
            'Updated At' => !empty($this->updated_at) ? usaToAusDateTime($this->updated_at) : 'N/A',
        ];
    }
}
