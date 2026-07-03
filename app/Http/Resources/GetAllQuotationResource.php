<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class GetAllQuotationResource extends JsonResource
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
            'from_name' => $this->from_name,
            'from_email' => $this->from_email,
            'from_message' => $this->from_message,
            'to_name' => $this->to_name,
            'to_email' => $this->to_email,
            'to_message' => $this->to_message,
            'qut_owner' => $this->qut_owner,
            'invoice_no' => $this->invoice_no,
            'abn' => $this->abn,
            'deal_name' => $this->deal_name,
            'subject' => $this->subject,
            'valid' => $this->valid,
            'contacted_id' => $this->contacted_id,
            'contact_name' => !empty($this->Lead) ? $this->Lead->name: '',
            'lead_status' => $this->lead_status,
            'account_name' => $this->account_name,
            'team' => $this->team,
            'carrier' => $this->carrier,
            'ship_country' => $this->ship_country,
            'bill_country' => $this->bill_country,
            'ship_code' => $this->ship_code,
            'bill_code' => $this->bill_code,
            'ship_state' => $this->ship_state,
            'bill_state' => $this->bill_state,
            'ship_city' => $this->ship_city,
            'bill_city' => $this->bill_city,
            'grd_total' => $this->grd_total,
            'discount' => $this->discount,
            'showGstInput' => $this->showGstInput,
            'sub_total' => $this->sub_total,
            'ship_street' => $this->ship_street,
            'bill_street' => $this->bill_street,
            'lead_address' => $this->lead,
            'financial_type' => $this->financial_type,
            'add_notes' => $this->add_notes,
            'quote' => !empty($this->quote) ? json_decode($this->quote) : '',
            'created_at' => $this->created_at
        ];
    }
}
