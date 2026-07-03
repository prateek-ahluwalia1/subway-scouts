<?php

namespace App\Http\Resources;

use App\Models\Guard;
use Illuminate\Http\Resources\Json\JsonResource;

class FetchCustomerSitesResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $jobRosterSum = round(collect($this->jobRoster)->sum('hours'), 2);
        return [
            'id' => $this->id,
            'site_name' => !empty($this->site_name) ? $this->site_name : '',
            'total_amount' => $this->total_amount,
            'total_hours' => $this->total_hours,
            'site_description' => !empty($this->site_description) ? $this->site_description : '',
            'customer_id' => $this->customer_id,
            'count' => $this->count,
            'jobRoster' =>  jobRosterResource::collection($this->jobRoster), 
            'jobRosterSum' => $jobRosterSum,
        ];
    }
}
