<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class EditTrainedGuardResource extends JsonResource
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
            'guard_id' => !empty($this->guard_id) ? $this->guard_id : 'N/A',
            'site_name' => !empty($this->site_id) ? getSiteName($this->site_id) : 'N/A',
            'site_id' => !empty($this->site_id) ? $this->site_id : 'N/A',
            'customer_name' => !empty($this->customer_id) ? getCustomerName($this->customer_id) : 'N/A',
            'customer_id' => !empty($this->customer_id) ? $this->customer_id : 'N/A',
            'status' => !empty($this->status) ? $this->status : 'N/A',
        ];
    }
}
