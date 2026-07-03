<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class FetchCustomerSitesWithGuardResource extends JsonResource
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
        $jobRosterSum = collect($this->guardJobRoster)->sum('hours');
        return [
            'id' => $this->id,
            'site_name'  => !empty($this->first_name) ? trim($this->first_name . ($this->middle_name ? ' ' . $this->middle_name : '') . ' ' . $this->last_name):'',
            'email' => $this->email,
            'profile_image' => returnImgPath('guard',$this->profile_image),
            'jobRoster' =>  jobRosterResource::collection($this->guardJobRoster),
            'jobRosterSum' => $jobRosterSum,
        ];
    }
}
