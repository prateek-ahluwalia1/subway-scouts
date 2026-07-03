<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class AfterUpdateCrmCustomerResource extends JsonResource
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
            'message' => 'Updated Record',
            'name' => !empty($this->name) ? $this->name : null,
            'email' => !empty($this->email) ? $this->email : null,
            'phone' => !empty($this->phone) ? $this->phone : null,
            'company' => !empty($this->company) ? $this->company : null,
            'saleperson_id' => !empty($this->saleperson_id) ? getAdminName($this->saleperson_id) : null,
            'fax' => !empty($this->fax) ? $this->fax : null,
            'website' => !empty($this->website) ? $this->website : null,
            'title' => !empty($this->title) ? $this->title : null,
            'lead_source' => !empty($this->lead_source) ? $this->lead_source : null,
            'lead_status' => !empty($this->lead_status) ? $this->lead_status : null,
            'industry' => !empty($this->industry) ? $this->industry : null,
            'no_emp' => !empty($this->no_emp) ? $this->no_emp : null,
            'annual_revenue' => !empty($this->annual_revenue) ? $this->annual_revenue : null,
            'rating' => !empty($this->rating) ? $this->rating : null,
            'skype_id' => !empty($this->skype_id) ? $this->skype_id : null,
            'secondary_email' => !empty($this->secondary_email) ? $this->secondary_email : null,
            'twitter' => !empty($this->twitter) ? $this->twitter : null,
            'street' => !empty($this->street) ? $this->street : null,
            'state' => !empty($this->state) ? $this->state : null,
            'country' => !empty($this->country) ? $this->country : null,
            'city' => !empty($this->city) ? $this->city : null,
            'zip_code' => !empty($this->zip_code) ? $this->zip_code : null,
            'description' => !empty($this->name) ? $this->name : null,
            'created_by' => !empty($this->created_by) ? getAdminName($this->created_by) : null,
        ];
    }
}
