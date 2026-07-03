<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class JobTrakerJobResource extends JsonResource
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
            'approved' => $this->approved,
            'authorized_end_time' => $this->authorized_end_time,
            'authorized_start_time' => $this->authorized_start_time,
            'customer_name' => $this->customer_name,
            'end' => $this->end,
            'start' => $this->start,
            'first_name' => $this->first_name,
            'middle_name' => $this->middle_name,
            'last_name' => $this->last_name,
            'guard_id' => $this->guard_id,
            'hours' => $this->hours,
            'site_id' => $this->site_id,
            'site_name' => $this->site_name,
            'status' => $this->status,
            'admin_name' => getAdminName($this->admin_approved_by),
        ];
    }
}
