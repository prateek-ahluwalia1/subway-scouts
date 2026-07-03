<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ContractorOtherDocumentsResource extends JsonResource
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
            'document_id' => $this->id,
            'document' => returnImgPath('contractor_documents',$this->document),
            'document_no' => $this->document_no,
            'document_expire' => $this->document_expire,
            'type' => $this->type,
        ];
    }
}
