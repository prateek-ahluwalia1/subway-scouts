<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class GuardEmpContractorDetailsResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $sec_lic_no = '';
        $sec_lic_exp = '';
        $guard_lic = getGuardDocument($this->id, 'security_license');
        if(!empty($guard_lic)  && !empty($guard_lic->document_no) && !empty($guard_lic->document_expire)){
            $sec_lic_no = $guard_lic->document_no;
            $sec_lic_exp = $guard_lic->document_expire;
        }

        return [
            'name' => $this->first_name.' '.$this->middle_name.' '.$this->last_name,
            'sr_name' => (!empty($this->empDetails) && !empty($this->empDetails->sr_name)) ? $this->empDetails->sr_name : '', //
            'home_phone' => (!empty($this->empDetails) && !empty($this->empDetails->home_phone)) ? $this->empDetails->home_phone : '', //
            'dob' => !empty($this->dob) ? usaToAus($this->dob) : '',
            'phone' => !empty($this->phone) ? $this->phone : '',
            'email' => !empty($this->email) ? $this->email : '',
            'tfn_file_no' => (!empty($this->empDetails) && !empty($this->empDetails->tfn_file_no)) ? $this->empDetails->tfn_file_no : '',
            'abn_no' => (!empty($this->empDetails) && !empty($this->empDetails->abn_no)) ? $this->empDetails->abn_no : '',
            'abn_type' => (!empty($this->empDetails) && !empty($this->empDetails->abn_type)) ? $this->empDetails->abn_type : '', //
            'gst' => (!empty($this->empDetails) && !empty($this->empDetails->gst)) ? $this->empDetails->gst : '',    //
            'day_of_commencement' => (!empty($this->empDetails) && !empty($this->empDetails->day_of_commencement)) ? $this->empDetails->day_of_commencement : '', //
            'address' => !empty($this->address) ? $this->address : '',
            'state' => !empty($this->state) ? $this->state : '',
            'postal_code' => !empty($this->postal_code) ? $this->postal_code : '',
            'residency_status' => (!empty($this->empDetails) && !empty($this->empDetails->guard_document_type)) ? $this->empDetails->guard_document_type : '',
            'sec_lic_no' => !empty($sec_lic_no) ? $sec_lic_no : 'N/A',
            'sec_lic_exp' => !empty($sec_lic_exp) ? $sec_lic_exp : 'N/A',
            'other_qualification' => (!empty($this->empDetails) && !empty($this->empDetails->other_qualification)) ? $this->empDetails->guard_document_type : '', //
            'car' => (!empty($this->empDetails) && !empty($this->empDetails->car)) ? $this->empDetails->car : '',  //
            'car_reg' => (!empty($this->empDetails) && !empty($this->empDetails->car_reg)) ? $this->empDetails->car_reg : '',  //
        ];
    }
}
