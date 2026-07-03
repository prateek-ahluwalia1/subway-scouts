<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\MoreContactsResource;

class CustomerUpdateDocumentResource extends JsonResource
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
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'address' => $this->address,
            'city' => $this->city,
            'state' => $this->state,
            'web_url' => $this->web_url,
            'job_level' => $this->job_level,
            'charge_rate' => $this->charged_rates_id,
            'apply_date' => $this->apply_date,
            'postal_code' => $this->postal_code,
            'image' => returnImgPath('customer', $this->image),
            'status' => $this->status,
            'moreContacts' => MoreContactsResource::collection($this->moreContacts),
            'otherDocuments' => OtherDocumentsResource::collection($this->otherDocuments),
        ];
        
    }
}
