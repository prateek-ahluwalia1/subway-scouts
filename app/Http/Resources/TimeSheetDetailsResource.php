<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class TimeSheetDetailsResource extends JsonResource
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
        return[
            // 'id' => $this->id,
            // 'site_name' => (!empty($this->site) && !empty($this->site->site_name)) ?   $this->site->site_name : '',
            // 'site_description' => (!empty($this->site) && !empty($this->site->site_description)) ? $this->site->site_description : '',
            // 'staff_name' => $this->guardz != null ?   $this->guardz->first_name.' '.$this->guardz->middle_name.' '. $this->guardz->last_name : '',
            // 'payroll_id' => $this->guardz != null && $this->guardz->internal_id  ? $this->guardz->internal_id : '',  
            // 'staff_type' => $this->guardz->guard_type,
            // 'customer' =>   (!empty($this->site) && !empty($this->site->customer) && !empty($this->site->customer->name)) ? $this->site->customer->name : '',
            // 'start_date' => usaToAus($this->start),
            // 'start_time' => usaToAusTime($this->start),
            // 'finish_time'=> usaToAusTime($this->end),
            // 'actual_start_time'=> $this->rosterActivity != null ? $this->rosterActivity->signin_time : '',
            // 'actual_end_time'=> $this->rosterActivity != null ? $this->rosterActivity->signout_time : '',
            // 'travel_time' => ($this->travel_time == 1 ? $this->travel_time_value : 0),
            // 'total_hours' => $this->hours,
            // 'admin_approved' => $this->admin_approved,
            // 'job_status' => $this->job_status,

            'id' => $this->id,
            'approved' => $this->admin_approved,
            'in_paysheet' => $this->in_paysheet,
            'authorized_end_time' => $this->rosterActivity != null ? usaToAusTime($this->rosterActivity->signout_time) : '',
            'authorized_start_time' => $this->rosterActivity != null ? usaToAusTime($this->rosterActivity->signin_time) : '',
            'customer_name' => (!empty($this->site) && !empty($this->site->customer) && !empty($this->site->customer->name)) ? $this->site->customer->name : '',
            'end' => usaToAus($this->end),
            'start' => usaToAus($this->start),
            'start_date' => usaToAus($this->start),
            'start_time' => usaToAusTime($this->start),
            'finish_time'=> usaToAusTime($this->end),
            'first_name' => $this->first_name == null ? $this->unprofile_name : $this->first_name,
            'middle_name' => $this->middle_name,
            'last_name' => $this->last_name,
            'guard_id' => $this->guard_id,
            'hours' => ($this->continuation == 0 && $this->hours < 4) ? 4 : $this->hours,
            'site_id' => $this->site_id,
            'site_name' => (!empty($this->site) && !empty($this->site->site_name)) ?   $this->site->site_name : '',
            'status' => $this->job_status,
            'admin_name' => getAdminName($this->admin_approved_by),
        ];
    }
}
