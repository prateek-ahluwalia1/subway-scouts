<?php

namespace App\Http\Resources;

use App\Models\Guard;
use Illuminate\Http\Resources\Json\JsonResource;

class EditSiteResource extends JsonResource
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
           'id' =>$this->id,
           'customer_id' =>$this->customer_id,
           'contractor_id' =>$this->contractor_id,
           'level' =>$this->level,
           'state' =>$this->state,
           'staff_type' =>$this->staff_type,
           'payrol' =>$this->payrol,
           'trained' =>$this->trained,
           'green_call' =>$this->green_call,
           'first_green_call' =>$this->first_green_call,
           'second_green_call' =>$this->second_green_call,
           'first_green_call_time' =>$this->first_green_call_time,
           'second_green_call_time' =>$this->second_green_call_time,
           'welfare_call' =>$this->welfare_call,
           'site_budget' => $this->site_budget,
           'welfare_call_type' =>$this->welfare_call_type,
           'welfare_timing' =>$this->welfare_timing,
           'job_instrcutions' =>$this->job_instrcutions,
           'sos_phone' =>$this->sos_phone,
           'start' =>  !empty($this->start) ? usaToAus($this->start) : null,
           'end' =>  !empty($this->end) ? usaToAus($this->end) : null,
           'address' =>$this->address,
           'coordinates' =>$this->coordinates,
           'site_name' =>$this->site_name,
           'site_description' =>$this->site_description,
           'signin_radius' =>$this->signin_radius,
           'alert_radius' =>$this->alert_radius,
           'break' =>$this->break,
           'break_chargeable' =>$this->break_chargeable,
           'break_payable' =>$this->break_payable,
           'break_deduction_payable' =>$this->break_deduction_payable,
           'break_deduction_chargeable' =>$this->break_deduction_chargeable,
           'site_payrate' =>$this->site_payrate,
           'dateSelectionOfPay' =>$this->payrate_affective_day,
           'payrate_affective_from' =>$this->payrate_affective_from,
           'dateSelectionOfCharge' =>$this->chargerate_affective_day,
           'chargerate_affective_from' =>$this->chargerate_affective_from,
           'site_charge_rate' =>$this->site_charge_rate,
           'site_chargerate_level' =>$this->site_chargerate_level,
           'site_payrate_level' =>$this->site_payrate_level,
           'site_tasks' =>json_decode($this->site_tasks),
           'site_type' =>$this->site_type,
           'site_break' =>($this->break == 1 ? 'yes': 'no'),
           'unpublished_site' =>$this->unpublished_site,
           'job_instruction_file' =>returnImgPath('site',$this->job_instruction_file),
           'site_hours' => $this->site_hours,
           'po_wo' => $this->po_wo,
           'type' => $this->type,
           'after_hours' => $this->after_hours,
        //    'scanners' => $this->QRCodes,
           'keys' => json_decode($this->keys),
           'is_patrolling_site' => $this->is_patrolling_site,
           'patrolling_type' => $this->patrolling_type,
           'is_alarm_patrol_site' => $this->is_alarm_patrol_site,
           'alarm_panels' => json_decode($this->alarm_panels),
           'monitoring_person' => $this->monitoring_person,
           'monitoring_contact' => $this->monitoring_contact,
           'alarm_dispatch_instruction' => $this->alarm_dispatch_instruction,
            'internal_patrolling' => $this->internal_patrolling,
            'intern_no_calls' => $this->intern_no_calls,
            'intern_time_type' => $this->intern_time_type,
            'intern_particular_times' => json_decode($this->intern_particular_times),
            'external_patrolling' => $this->external_patrolling,
            'extern_no_calls' => $this->extern_no_calls,
            'extern_time_type' => $this->extern_time_type,
            'extern_particular_times' => json_decode($this->extern_particular_times),
            'intermediate_patrolling' => $this->intermediate_patrolling,
            'intermed_no_calls' => $this->intermed_no_calls,
            'intermed_time_type' => $this->intermed_time_type,
            'intermed_particular_times' => json_decode($this->intermed_particular_times),  
           'site_updated_by' => (!empty($this->site_update_reason) ? getAdminName($this->site_updated_by) : '' ),
           'site_guard_ids' => $this->site_guard_ids!= '' && $this->site_guard_ids!= 'null' && $this->site_guard_ids!= null ?  (Guard::where(function($query) use ($request){
                foreach (json_decode($this->site_guard_ids, true) as $key => $value) {
                $query->orWhere('id', $value);
                }
            })->select('id','name')->get()) : '',
            'updated_at' => usaToAus($this->updated_at),
        ];
    }
}
