<?php

namespace App\Http\Resources;

use App\Models\Customer;
use Illuminate\Http\Resources\Json\JsonResource;

class EditGuardResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        //return parent::toArray($request);
        return [
            'id' => $this->id,
            'first_name' => $this->first_name,
            'middle_name' => $this->middle_name,
            'last_name' => $this->last_name,
            'email' => $this->email,
            'phone' => $this->phone,
            'address' => $this->address,
            'profile_image' => returnImgPath('guard',$this->profile_image),
            'guard_type' => $this->guard_type,
            'state' => $this->state,
            'staff_type' => $this->staff_type,
            'position' => $this->position,
            // 'dob' =>  !empty($this->dob) ? usaToAus($this->dob) : '',
            'dob' =>  $this->dob,
            'gender' => $this->gender,
            'home_country' => $this->country,
            'suburb' => $this->suburb,
            'city' => $this->city,
            'annual_leave' => $this->annual_leave_hours,
            'sick_leave' => $this->sick_leave_hours,
            'coordinates' => $this->coordinates,
            'postal_code' => $this->postal_code,
            'visa_number' => !empty($this->visaNumber->document_no) ? $this->visaNumber->document_no : '',
            'passport_number' => !empty($this->passportNumber->document_no) ? $this->passportNumber->document_no : '',
            'emergency_contact_name' => $this->emergency_contact_name,
            'emergency_contact_phone' => $this->emergency_contact_phone,
            'customer_id' =>  $this->customer_id!= '' && $this->customer_id!= 'null' && $this->customer_id!= null ?  (Customer::where(function($query) use ($request){
                foreach (json_decode($this->customer_id, true) as $key => $value) {
                   $query->orWhere('id', $value);
                }
             })->select('id','name')->get()) : '',
            'contractor_id' => $this->contractor_id,
            'profile_completion' => $this->profile_completion,
            'created_at' =>  !empty($this->created_at) ? usaToAus($this->created_at) : '',
            'joining_date' => $this->joining_date,
            'guard_postion' => $this->guard_postion,
            'emergency_contact_email' => $this->emergency_contact_email,
            'emergency_contact_relation' => $this->emergency_contact_relation,
            
            
        ];
    }
}
