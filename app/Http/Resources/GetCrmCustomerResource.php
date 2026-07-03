<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class GetCrmCustomerResource extends JsonResource
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
            'id' => $this->id,
            'assign_operation' => $this->assign_operation,
            'annual_revenue' => $this->annual_revenue,
            'actual_revenue' => $this->actual_revenue,
            'manual_revenue' => $this->manual_revenue,
            'city' => $this->city,
            'company' => $this->company,
            'country' => $this->country,
            'created_at' => $this->created_at,
            'description' => $this->description,
            'email' => $this->email,
            'fax' => $this->fax,
            'loss_value' => $this->loss_value,
            'won_status' => $this->won_status,
            'image' => $this->image,
            'title' => $this->title,
            'job_title' => $this->job_title,
            'priority' => $this->priority,
            'lead_status' => $this->lead_status,
            'industry' => $this->industry,
            'lead_source' => $this->lead_source,
            'name' => $this->name,
            'no_emp' => $this->no_emp,
            'phone' => $this->phone,
            'lost_lead_option' => $this->lost_lead_option,
            'rating' => $this->rating,
            'secondary_email' => $this->secondary_email,
            'skype_id' => $this->skype_id,
            'state' => $this->state,
            'street' => $this->street,
            'twitter' => $this->twitter,
            'updated_at' => $this->updated_at,
            'website' => $this->website,
            'zip_code' => $this->zip_code,          
            'comp_size' => $this->comp_size,
            'industries' => $this->industries,          
            'abn' => $this->abn,          
            'voucher' => $this->voucher,  
            'leaad_client_name' => $this->leaad_client_name, 
            'sub_company' => $this->sub_company, 
            'no_of_traveler' => $this->no_of_traveler,         
            'lost_lead_reason' => $this->lost_lead_reason,          
            'saleperson_id' => $this->saleperson_id,  
            'travel_date' => $this->travel_date,  
            'booking_expense' => $this->booking_expense,        
            'other_industry' => !empty($this->other_industry) ? $this->other_industry : null,          
            'suburb' => !empty($this->suburb) ? $this->suburb : null,          
            'address' => !empty($this->address) ? $this->address : null,          
            'postal_code' => !empty($this->postal_code) ? $this->postal_code : null,          
            'createdby_id' => !empty($this->CreatedBy) ? $this->CreatedBy->id : null,
            'createdby_name' => !empty($this->CreatedBy) ? $this->CreatedBy->name : null,
            'createdby_email' => !empty($this->CreatedBy) ? $this->CreatedBy->email : null,
            'createdby_phone' => !empty($this->CreatedBy) ? $this->CreatedBy->phone : null,
            'handledby_id' => !empty($this->HandledBy) ? $this->HandledBy->id : null,
            'handledby_name' => !empty($this->HandledBy) ? $this->HandledBy->name : null,
            'handledby_email' => !empty($this->HandledBy) ? $this->HandledBy->email : null,
            'handledby_phone' => !empty($this->HandledBy) ? $this->HandledBy->phone : null,
        ];
    }
}
