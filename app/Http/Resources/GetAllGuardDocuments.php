<?php

namespace App\Http\Resources;

use App\Models\DocumentCategory;
use App\Models\GuardWorkDetail;
use Illuminate\Http\Resources\Json\JsonResource;

class GetAllGuardDocuments extends JsonResource
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
            'id' => $this->id,
            'is_deleteable' => $this->is_deleteable,
            'guard_id' => ($this->guard_id != '' ? $this->guard_id : ''),
            'document_name' => ($this->document_name != '' ? $this->document_name : ''),
            'document_type' => ($this->document_type != '' ? $this->document_type : ''),
            'document_expire' => ($this->document_expire == 'current, pending renewal' ? $this->document_expire : ($this->document_expire != '' ? usaToAus($this->document_expire) : '')),
            'notes' =>  $this->notes !='' ? $this->notes : '',
            'document_no' =>  $this->document_no !='' ? $this->document_no : '',
            'c_f_roster' =>  $this->c_f_roster == 1 ? true : false,
            'c_f_profile' =>  $this->c_f_profile == 1 ? true : false,
            'file' => $this->file != '' ?  returnImgPath('guard_documents',$this->file) : '',
            //'guard_document_type' => !empty($guard_document_type) ?  json_decode($guard_document_type->document_type) : ''
        ];

    }
}
