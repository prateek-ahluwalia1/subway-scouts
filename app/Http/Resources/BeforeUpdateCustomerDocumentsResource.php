<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class BeforeUpdateCustomerDocumentsResource extends JsonResource
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

            'Customer Name' => !empty($this->customer_id) ? getCustomerName($this->customer_id) : 'N/A',
            'Document' => !empty($this->document) ? returnImgPath('', $this->document)  : 'N/A',
            'Document No' => !empty($this->document_no) ? $this->document_no  : '',
            'Document Exp' => !empty($this->document_expire) ? usaToAus($this->document_expire) : 'N/A',
            'Type'  => !empty($this->type) ? $this->type : '', 
        ];
    }
}
