<?php

namespace App\Http\Resources;

use App\Models\GuardDocument;
use Illuminate\Http\Resources\Json\JsonResource;

class GuardSecurityLienceResource extends JsonResource
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
            'document_no' => $this->document_no ? $this->document_no : 'N/A',
            'document_expire' => $this->document_expire ?  usaToAus($this->document_expire) : 'N/A',
            'document_type' => $this->document_type,
            // 'visa' => $this->document_no ? $this->document_no : 'N/A',
            // 'visa_expire' => $this->document_expire ?  usaToAus($this->document_expire) : 'N/A',
        ];
    }
}
