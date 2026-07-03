<?php

namespace App\Http\Resources;

use DateTime;
use Illuminate\Http\Resources\Json\JsonResource;

class GuardLeaveCountResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $datetime1 = new DateTime($this->guardss->joining_date);
        $datetime2 = new DateTime();
        $difference = $datetime1->diff($datetime2);
        $days = $difference->days%365;
        
        return [
            'leave_status' => !empty($this->status) ? $this->status : '',
            'leave_reason' => !empty($this->reason) ? $this->reason : '',
            'leave_approved_by' => !empty($this->approved_by) ? getAdminName($this->approved_by) : 'N/A',
            //'leave_rejected_by' => !empty($this->admin_id) ? getAdminName($this->admin_id) : 'N/A',
            'guard_first_name' => !empty($this->guardss) && !empty($this->guardss->first_name) ? $this->guardss->first_name : '',
            'guard_middle_name' => !empty($this->guardss) && !empty($this->guardss->middle_name) ? $this->guardss->middle_name : '',
            'guard_last_name' => !empty($this->guardss) && !empty($this->guardss->last_name) ? $this->guardss->last_name : '',
            'guard_email' => !empty($this->guardss) && !empty($this->guardss->email) ? $this->guardss->email : '',
            'guard_address' => !empty($this->guardss) && !empty($this->guardss->address) ? $this->guardss->address : '',
            'gaurd_type' => !empty($this->guardss) && !empty($this->guardss->guard_type) ? $this->guardss->guard_type : '',
            'id' => !empty($this->guardss) && !empty($this->guardss->id) ? $this->guardss->id : '',
            'days' => $days,
        ];
    }
}
