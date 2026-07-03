<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class GuardVisaResource extends JsonResource
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
            'visa' => $this->document_no ? $this->document_no : 'N/A',
            'visa_expire' => $this->document_expire ?  usaToAus($this->document_expire) : 'N/A',
        ];
    }
}
