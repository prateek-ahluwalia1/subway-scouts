<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class GuardEmergencyContactResource extends JsonResource
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
            'emergency_contact_phone' => $this->emergency_contact_phone,
            'emergency_contact_name' => $this->emergency_contact_name,
            'relationship' => (!empty($this->guardEmergencyContact) && !empty($this->guardEmergencyContact->relationship)) ? $this->guardEmergencyContact->relationship : '', //
            'bank_name' => (!empty($this->empDetails) && !empty($this->empDetails->bank_name)) ? $this->empDetails->bank_name : '', 
            'account_name' => (!empty($this->guardEmergencyContact) && !empty($this->guardEmergencyContact->account_name)) ? $this->guardEmergencyContact->account_name : '',//
            'account_type' => (!empty($this->guardEmergencyContact) && !empty($this->guardEmergencyContact->account_type)) ? $this->guardEmergencyContact->account_type : '',//
            'super_fund_name' => (!empty($this->guardEmergencyContact) && !empty($this->guardEmergencyContact->super_fund_name)) ? $this->guardEmergencyContact->super_fund_name : '',//
            'superannutation_no' => (!empty($this->empDetails) && !empty($this->empDetails->superannutation_no)) ? $this->empDetails->superannutation_no : '',
            'criminal_history' => (!empty($this->guardEmergencyContact) && !empty($this->guardEmergencyContact->criminal_history)) ? json_decode($this->guardEmergencyContact->criminal_history) : '', //
        ];
    }
}
