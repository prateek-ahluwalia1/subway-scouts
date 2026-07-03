<?php

namespace App\Http\Resources;

use App\Models\Guard;
use Illuminate\Http\Resources\Json\JsonResource;
use DB;

class liveDashabordDataResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $green_call_1 = '';
        $green_call_2 = '';
        foreach ($this->greenCall as $key => $gc) {
            if ($gc->before_time == 120) {
                $green_call_1 = date('H:i', strtotime($gc->created_at));
            }else{
                $green_call_2 = date('H:i', strtotime($gc->created_at));
            }
        }
        // foreach ($this->WelfareCall as $key => $gc) {
            
        // }
        return [
            'start' => $this->start !='' ? usaToAusDateTime($this->start) : null,
            'start_time' => $this->start !='' ? timeFormat($this->start) : null,
            'end' => $this->end !='' ? usaToAusDateTime($this->end) : null,
            'end_time' => $this->start !='' ? timeFormat($this->end) : null,
            'date' => $this->start !='' ? dateFormat($this->start) : null,
            'roster_id' => $this->id, 
            'site_id' => $this->site_id,
            'signin_status' => $this->signin_status,
            'status' => $this->job_status,
            'site_name' => !empty($this->site->site_name) ? $this->site->site_name : '',
            'guard_id' => $this->guard_id,
            'phone' => $this->guardz->phone,
            'first_name' => (!empty($this->guardz) && $this->guardz != null) ? $this->guardz->first_name : null,
            'middle_name' => (!empty($this->guardz) && $this->guardz != null) ? $this->guardz->middle_name : null,
            'last_name' => (!empty($this->guardz) && $this->guardz != null) ? $this->guardz->last_name : null,
            'signin_time' => (!empty($this->rosterActivity) && $this->rosterActivity != null && $this->rosterActivity->signin_time) ? timeFormat($this->rosterActivity->signin_time) : null,
            'signout_time' => (!empty($this->rosterActivity) && $this->rosterActivity != null && $this->rosterActivity->signout_time) ? timeFormat($this->rosterActivity->signout_time) : null,
            //'auto_signout' => (!empty($this->rosterActivity) && $this->rosterActivity != null && $this->rosterActivity->auto_signout) ? $this->rosterActivity->auto_signout : null,
            'auto_signout' => (!empty($this->rosterActivity) && $this->rosterActivity != null) ? $this->rosterActivity->auto_signout : null,
            'gc1' => $green_call_1,
            'gc2' => $green_call_2,
            'wc' => count($this->WelfareCall)
        ];
    }
}
