<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class GuardInternalAndExternalIdsResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        //return parent::toArray($request);
        return [
            'internal_id' => $this->internal_id == null && $this->internal_id == 'null' ? 'N/A' : $this->internal_id,
            'guard_external_ids' => GuardExternalResource::collection($this->guardExternalIds),
            
        ];
    }
}
