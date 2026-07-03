<?php

namespace App\Http\Resources;

use App\Models\Guard;
use Illuminate\Http\Resources\Json\JsonResource;

class AllGuardResource extends JsonResource
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
            'id'  =>$this->id,
            'name' => $this->first_name.' '.$this->middle_name.' '.$this->last_name,
            'first_name' => $this->first_name,
            'middle_name' => $this->middle_name,
            'last_name' => $this->last_name,
            'profile_image' =>  returnImgPath('guard',$this->profile_image),
            'email' =>   $this->email,
            'phone' =>   $this->phone,
            'is_form_submit' =>   $this->is_form_submit,
            //'security_lience_expire' =>   ($this->guardDocument->document_expire) ? $this->guardDocument->document_expire : '',
            // 'suburb'=>   $this->suburb,
            'guard_admin_approval' => $this->guard_admin_approval,
            'state' =>   $this->state,
             'guard_status' =>  $this->guard_status,
             'is_available' =>  ($this->is_available == 'yes' ? 'active' : 'inactive'),
             'is_email_approved' =>  ($this->is_email_approved == 'yes' ? 'approved' : 'Not approved'),
             'admin_approval_status' =>  $this->admin_approval_status,
             'last_login'  =>  $this->last_login,
             'guard_feedback'  => (count(CheckGuardFeedbackResource::collection($this->guardFeedback)) > 0 ? true : false),
             'guard_security_lience' => $this->when($request->has('document_type') && $request->document_type, $this->guardDocuments != '' ?  GuardSecurityLienceResource::collection($this->guardDocuments): 'N/A' ),
             'residential_status' => $this->guardDocuments->isNotEmpty() ? $this->guardDocuments->first()->document_category : 'N/A',
             //  'active_guard_count' => $active_guard_count,
            //  'inactive_guard_count' => $inactive_guard_count,
            //  'pending_guard_count' => $pending_guard_count,
            //  'new_guard_count' => $new_guard_count,
            // 'covid_19'=>  ($this->covid_19 == 0 ? false: true),
            // 'profile_completion'=>  !empty($this->profile_completion) ? $this->profile_completion : 0,
        ];
    }
}
