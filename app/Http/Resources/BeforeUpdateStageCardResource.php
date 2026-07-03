<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class BeforeUpdateStageCardResource extends JsonResource
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
            'Stage' =>  !empty($this->Stage) ? $this->Stage->name : '',
            'Customer' =>  !empty($this->customer) ? $this->customer->name : '',
            'Description' => !empty($this->description) ? $this->description : '',
            'Status' => !empty($this->status) ? $this->status : '',
            'Sale Person' => !empty($this->salesperson) ? $this->salesperson->name : '',
            'Admin Name' => !empty($this->admin_id) ? getAdminName($this->admin_id) : 'N/A',
        ]; 
    }
}
