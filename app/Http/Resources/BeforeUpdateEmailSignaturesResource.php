<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class BeforeUpdateEmailSignaturesResource extends JsonResource
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
            'Admin Name' => !empty($this->admin_id) ? getAdminName($this->admin_id) : 'N/A', 
            'Title' => !empty($this->title) ? $this->title : 'N/A', 
            'Body' => !empty($this->body) ? strip_tags($this->body) : 'N/A', 
            'Updated At' => !empty($this->updated_at) ? usaToAusDateTime($this->updated_at) : 'N/A', 
        ];
    }
}
