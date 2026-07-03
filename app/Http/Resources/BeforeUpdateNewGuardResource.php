<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class BeforeUpdateNewGuardResource extends JsonResource
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
            'First Name' => !empty($this->first_name) ? $this->first_name : 'N/A', 
            'Middle Name' => !empty($this->middle_name) ? $this->middle_name : 'N/A', 
            'Last Name' => !empty($this->last_name) ? $this->last_name : 'N/A', 
            'Profile Image'  => !empty($this->profile_image) ? returnImgPath('guard', $this->profile_image): '', 
            'Email'  => !empty($this->email) ? $this->email : 'N/A',
            'Phone'   => !empty($this->phone) ? $this->phone : 'N/A',
            'Address'  => !empty($this->address) ? $this->address : 'N/A',
            'Suburb' => !empty($this->suburb) ? $this->suburb : 'N/A',
            'Covid 19'  => !empty($this->covid_19) ? $this->covid_19 : 'N/A',
            'Coordinates' => !empty($this->coordinates) ? $this->coordinates : 'N/A',
            'Latitude'  => !empty($this->latitude) ? $this->latitude : 'N/A',
            'City' =>  !empty($this->city) ? $this->city : 'N/A',
            'State'  =>  !empty($this->state) ? $this->state : 'N/A',
            'Postal Code'  =>  !empty($this->postal_code) ? $this->postal_code : 'N/A',
            'Date of Birth'  =>  !empty($this->dob) ? $this->dob : 'N/A',
            'Gender' => !empty($this->gender) ? $this->gender : 'N/A',
            'Emergency Contact Name' => !empty($this->emergency_contact_name) ? $this->emergency_contact_name : 'N/A',
            'Emergency Contact Phone' => !empty($this->emergency_contact_phone) ? $this->emergency_contact_phone : 'N/A',
            'Emergency Contact Email' => !empty($this->emergency_contact_email) ? $this->emergency_contact_email : 'N/A',
            'Emergency Contact Relation' => !empty($this->emergency_contact_relation) ? $this->emergency_contact_relation : 'N/A',
            'Registration Type'  => !empty($this->registration_type) ? $this->registration_type : 'N/A',
            'Residential Status' => !empty($this->residential_status) ? $this->residential_status : 'N/A',
            'Is Email Approved' => !empty($this->is_email_approved) ? $this->is_email_approved : 'N/A',
            'Is Available' => !empty($this->is_available) ? $this->is_available : 'N/A',
            'Guard Status' => !empty($this->guard_status) ? $this->guard_status : 'N/A',
            'Admin Approval Status' => !empty($this->admin_approval_status) ? $this->admin_approval_status : 'N/A',
            'Work Limitation Status' => !empty($this->work_limitation_status) ? $this->work_limitation_status : 'N/A',
            'Weekly Work Hours Limitation' => !empty($this->weekly_work_hours_limitation) ? $this->weekly_work_hours_limitation : 'N/A',
            'Payroll ABN Number' => !empty($this->payroll_abn_number) ? $this->payroll_abn_number : 'N/A',
            'Payroll Bank Name' => !empty($this->payroll_bank_name) ? $this->payroll_bank_name : 'N/A',
            'BSB' => !empty($this->bsb) ? $this->bsb : 'N/A',
            'Payroll Bank Account Number' => !empty($this->payroll_bank_account_number) ? $this->payroll_bank_account_number : 'N/A',
            'Staff Type' => !empty($this->staff_type) ? $this->staff_type : 'N/A',
            'Guard Type' => !empty($this->guard_type) ? $this->guard_type : 'N/A',
            'Guard Position' => !empty($this->guard_postion) ? $this->guard_postion : 'N/A',
            'Internal ID' => !empty($this->registration_type) ? $this->registration_type : 'N/A',
            'Updated At'  => !empty($this->updated_at) ?  usaToAus($this->updated_at) : '',
        ];
    }
}
