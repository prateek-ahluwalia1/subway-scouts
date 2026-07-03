<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class GuardBeforeAndAfterWeekShiftsResource extends JsonResource
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
            'roster_id' => $this->id,
            'start' => !empty($this->start) ?  usaToAusTime($this->start) : 'N/A',
            'end' =>  !empty($this->end) ?  usaToAusTime($this->end) : 'N/A',
            'site' => $this->site_name,
            'date' => $this->start !='' ? stringDateFormate($this->start) : null,
            'job_status' => $this->job_status !='' ? $this->job_status : null,
        ];
    }
}
