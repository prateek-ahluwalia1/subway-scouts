<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class BeforeUpdateStaffWorkDetailResource extends JsonResource
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
            'Guard Name' => getGuardName($this->guard_id),
            'Hired On'  => !empty($this->hired_on) ? usaToAus($this->hired_on) : '',
            'Job Level'  => !empty($this->job_level) ? $this->job_level : '',
            'Payrate State'  => !empty($this->payrate_state) ? $this->payrate_state : '',
            'Payrate Title'  => !empty($this->payrate) ? $this->payrate->title : '',
            'TNF File'  => !empty($this->tfn_file) ? returnImgPath('guard_employment_details', $this->tfn_file) : '',
            'Superannutation File'  => !empty($this->superannutation_file) ? returnImgPath('guard_employment_details', $this->superannutation_file) : '',
            'Superannutation No'  => !empty($this->superannutation_no) ? returnImgPath('guard_employment_details', $this->superannutation_no) : '',
            'TNF File NO'  => !empty($this->tfn_file_no) ? $this->tfn_file_no : '',
            'ABN Name'  => !empty($this->abn_name) ? $this->abn_name : '',
            'ABN NO'  => !empty($this->abn_no) ? $this->abn_no : '',
            'Bank Name'  => !empty($this->bank_name) ? $this->bank_name : '',
            'BSB'  => !empty($this->bsb) ? $this->bsb : '',
            'Bank Account NO'  => !empty($this->bank_account_no) ? $this->bank_account_no : '',
            'Work Limitation Status'  => !empty($this->work_hours_limitation_status) && $this->work_hours_limitation_status == 0 ? 'OFF' : 'ON',
            'Weekly Work Limitation'  => !empty($this->weekly_work_hours_limitation) ? $this->weekly_work_hours_limitation : '',
            'Authorized By'  => !empty($this->authorized_by) ? getAdminName($this->authorized_by) : 'N/A',
            'Letter From Educational Institute'  => !empty($this->letter_from_educational_institute) ? $this->letter_from_educational_institute : 'N/A',
            'Document Type'  => !empty($this->guard_document_type) ? $this->guard_document_type : 'N/A',
            'Sr Name'  => !empty($this->sr_name) ? $this->sr_name : 'N/A',
            'Home Phone'  => !empty($this->home_phone) ? $this->home_phone : 'N/A',
            'ABN Type'  => !empty($this->abn_type) ? $this->abn_type : 'N/A',
            'Day of Commencement'  => !empty($this->day_of_commencement) ? $this->day_of_commencement : 'N/A',
            'Other Qualification'  => !empty($this->other_qualification) ? $this->other_qualification : 'N/A',
            'Car Reg'  => !empty($this->car_reg) ? $this->car_reg : 'N/A',
            'GST'  => !empty($this->gst) ? $this->gst : 'N/A',
            'Car'  => !empty($this->car) && $this->car == false ? 'NO' : 'Yes',
        ];
    }
}
