<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\DB;

class CrmGetSpecifcLeadsResource extends JsonResource
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
            'annual_revenue' => $this->annual_revenue,
            'city' => $this->city,
            'company' => $this->company,
            'country' => $this->country,
            'created_at' => $this->created_at,
            'created_by' => $this->created_by,
            'description' => $this->description,
            'email' => $this->email,
            'fax' => $this->fax,
            'id' => $this->id,
            'image' => $this->image,
            'sub_company' => $this->sub_company,
            'won_status' => $this->won_status,
            'industry' => $this->industry,
            'lead_source' => $this->lead_source,
            'lead_status' => $this->lead_status,
            'manual_revenue' => $this->manual_revenue,
            'name' => $this->name,
            'no_emp' => $this->no_emp,
            'phone' => $this->phone,
            'rating' => $this->rating,
            'leaad_client_name' => is_numeric($this->leaad_client_name) ? 
                                   $this->getCustomerName((int)$this->leaad_client_name):
                                   $this->leaad_client_name,
            'saleperson_id' => $this->saleperson_id,
            'saleperson_name' => getAdminName($this->saleperson_id),
            'assign_operation_name' => getAdminName($this->assign_operation),
            'secondary_email' => $this->secondary_email,
            'skype_id' => $this->skype_id,
            'state' => $this->state,
            'street' => $this->street,
            'title' => $this->title,
            'twitter' => $this->twitter,
            'updated_at' => $this->updated_at,
            'website' => $this->website,
            'is_archived' => $this->is_archived,
            'zip_code' => $this->zip_code,
            'travel_date' => $this->travel_date,
            'actual_revenue' => $this->actual_revenue,
            'booking_expense' => $this->booking_expense,
        ];
    }
    private function getCustomerName($clientId)
    {
        $customer = DB::table('customers')->where('id', $clientId)->first();
        return $customer ? $customer->name : null;
    }
}
