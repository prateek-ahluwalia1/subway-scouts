<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class EditRunSheetJobRosterResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $status = '';
        $current_date_time = time();
        $end_time = strtotime($this->end);
        $start_time = strtotime($this->start);
        if(($current_date_time > $start_time) && ($this->signin_status == 0) && ($this->job_status == 'confirmed')){
            $status = 'missed';
        }

        if (!empty($status)) {
            $is_status = $status;
         }else{
             if (!empty($this->job_status)) {
                 $is_status = $this->job_status;
             }
             else{
                 $is_status= null;
             }
         }


        return[
            'id' => $this->id,
            'run_sheet_id' => $this->run_sheet_id,
            'run_sheet_name' => $this->Runsheet->title,
            'guard_id' => $this->guard_id,
            'profile_image' => !empty($this->guardz) && !empty($this->guard_id) ? returnImgPath('guard', $this->guardz->profile_image) : '',
            'start' => usaToAusDateTime($this->start),
            'end'   =>   usaToAusDateTime($this->end),
            'shift_payable' => $this->shift_payable,
            'shift_chargeable' => $this->shift_chargeable,
            'payrate_level' => $this->payrate_level,
            'payrate' => $this->payrate,
            'chargerate_level' => $this->chargerate_level,
            'chargerate' => $this->chargerate,
            'unprofile_name' => !empty($this->unprofile_name) ? $this->unprofile_name : null,
            'un_published_shift' => ($this->un_published_shift == 0 ? false : true ),
            'public_holidays' => ($this->public_holidays == 0 ? false : true),
            'covid_marshal' => ($this->covid_marshal == 0 ? false : true),
            'training' => ($this->training == 0 ? false : true),
            'continuation' => ($this->continuation == 0 ? false : true),
            'over_time' => ($this->over_time == 0 ? false : true),
            'over_time_value' => $this->over_time_value ,
            'travel_time' => ($this->travel_time  == 0 ? false : true),
            'travel_time_value' => $this->travel_time_value ,
            'shift_create_status' => $this->shift_create_status,
            'custome_rate' => ($this->custome_rate == 0 ? false : true ),
            'custome_payrate' => ($this->custome_payrate == 0 ? false : true),
            'custome_chagerate' => ($this->custome_chagerate == 0 ? false : true),
            'po_wo' => $this->po_wo,
            'on_call_job' => $this->on_call_job,
            'status' => $is_status, //
            'signin_status' => $this->signin_status !='' ? $this->signin_status : null,
            'job_status' => $this->job_status !='' ? $this->job_status : null,
            'job_instrcutions' => $this->job_instrcutions,
            'job_instruction_text' => $this->job_instruction_text,
            'manualPayRate' => ($this->manualPayRate != null && $this->manualPayRate != '' ? json_decode($this->manualPayRate) : ''),
            'manualChargeRate' => ($this->manualChargeRate != null && $this->manualChargeRate != '' ? json_decode($this->manualChargeRate) : ''),
            'jobRosterTasks' =>  RunSheetJobRosterTaskResource::collection($this->RunSheetJobRosterTask),
        ];
    }
}
