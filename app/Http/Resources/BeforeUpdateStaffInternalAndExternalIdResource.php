<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class BeforeUpdateStaffInternalAndExternalIdResource extends JsonResource
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
            'Guard Name' => !empty($this->guard_id) ? $this->guard_id : '',
            'Customer Name' => !empty($this->customer_id) ? getCustomerName($this->customer_id) : '',
            'External ID' => !empty($this->external_id) ? $this->external_id : '',
        ];
    }
}
