<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class BeforeUpdateCrmCustomerResource extends JsonResource
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
            'name' => !empty($this->name) ?  $this->name : '', 
            'email' => !empty($this->email) ?  $this->email : '',
            'phone' => !empty($this->phone) ?  $this->phone : '',
            'company' => !empty($this->phone) ?  $this->company : '',
            'website' => !empty($this->website) ?  $this->website : '',
            'lead_source' => !empty($this->lead_source) ?  $this->lead_source : '',
            'lead_status' => !empty($this->lead_status) ?  $this->lead_status : '',
            'industry' => !empty($this->industry) ?  $this->industry : '',
            'no_emp' => !empty($this->lead_status) ?  $this->no_emp : '',
            'annual_revenue' => !empty($this->annual_revenue) ?  $this->annual_revenue : '',
            'rating' => !empty($this->rating) ?  $this->rating : '',
            'skype_id' => !empty($this->skype_id) ?  $this->skype_id : '',
            'secondary_email' => !empty($this->secondary_email) ?  $this->secondary_email : '',
            'twitter' => !empty($this->twitter) ?  $this->twitter : '',
            'street' => !empty($this->street) ?  $this->street : '',
            'state' => !empty($this->state) ?  $this->state : '',
            'city' => !empty($this->city) ?  $this->city : '',
            'zip_code' => !empty($this->zip_code) ?  $this->zip_code : '',
            'description' => !empty($this->description) ?  $this->description : '',
            'fax' => !empty($this->fax) ?  $this->fax : '',
            'title' => !empty($this->title) ?  $this->title : '',
            'saleperson_name' => !empty($this->saleperson_id) ?  getAdminName($this->saleperson_id) : '',  
        ];
    }
}
