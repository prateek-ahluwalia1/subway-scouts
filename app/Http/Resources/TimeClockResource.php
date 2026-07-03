<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class TimeClockResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $cords = !empty($this->rosterActivity->location) ? explode(",",$this->rosterActivity->location) : '0,0';
        // dd($cords[0]);
        $f_name = !empty($this->guardz->first_name) ? $this->guardz->first_name : '';
        $m_name = !empty($this->guardz->middle_name) ? $this->guardz->middle_name : '';
        $l_name = !empty($this->guardz->last_name) ? $this->guardz->last_name : '';
        return [
            'id' => $this->id,
            'start_time' => usaToAusTime($this->start),
            'end_time' => usaToAusTime($this->end),
            'image' => $this->guardz ? returnImgPath('guard', $this->guardz->profile_image) : null,
            'start_date' => usaToAus($this->start),
            'staff_name' => $f_name.' '.$m_name.' '.$l_name,
            'clock_in_time' => !empty($this->rosterActivity->signin_time) ? $this->rosterActivity->signin_time : '', 
            'clock_out_time' => !empty($this->rosterActivity->signout_time) ? $this->rosterActivity->signout_time : '',
            'green_calls' => GreenCallResource::collection($this->greenCall),
            'total_hours' => $this->hours,
            'signin_selfie' => !empty($this->rosterActivity->signin_selfie) ? returnImgPathCheck('uploads', $this->rosterActivity->signin_selfie) : '',
            'signin_notes' => !empty($this->rosterActivity->signin_notes) ? $this->rosterActivity->signin_notes : '',
            'signout_selfie' => !empty($this->rosterActivity->signout_selfie) ? returnImgPathCheck('uploads', $this->rosterActivity->signout_selfie) : '',
            'signout_notes' => !empty($this->rosterActivity->signout_notes) ? $this->rosterActivity->signout_notes : '',
            'lat' => (float) $cords[0],
            'lng' => (float) $cords[1],
            'signin_status' => $this->signin_status,
            'site_name' => (!empty($this->site) && $this->site->site_name) ? $this->site->site_name : '',
            'site_description' => (!empty($this->site) && $this->site->site_description) ?  $this->site->site_description : '',
        ];
    }
}
