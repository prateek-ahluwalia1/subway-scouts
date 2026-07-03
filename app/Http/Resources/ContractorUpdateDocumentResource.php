<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ContractorUpdateDocumentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return[
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
            'apply_date' => !empty($this->apply_date) ? $this->apply_date : '',
            'postal_code' => $this->postal_code,
            'image' => returnImgPath('contractor', $this->image),
            'status' => $this->status,
            'moreContacts' => ContractorMoreContactsResource::collection($this->moreContacts),
            'otherDocuments' => ContractorOtherDocumentsResource::collection($this->otherDocuments),
        ];
    }
}
