<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class GuardExternalResource extends JsonResource
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
            'id' => $this->id,
            'guard_id' => $this->guard_id,
            'customer_name' => !empty($this->customer) && !empty($this->customer->name) ? $this->customer->name : 'N/A',
            'customer_id' => !empty($this->customer) && !empty($this->customer->id) ? $this->customer->id : 'N/A',
            'external_id' => !empty($this->external_id) ? $this->external_id : 'N/A',
        ];
    }
}
