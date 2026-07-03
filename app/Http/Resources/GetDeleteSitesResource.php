<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class GetDeleteSitesResource extends JsonResource
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
            'site_name' => $this->site_name,
            'reason' => $this->reason,
            'action_by' => !empty($this->action_by) ? getAdminName($this->action_by) : '',
            'created_at' => !empty($this->created_at) ? usaToAusDateTime($this->created_at) : '',
        ];
    }
}
