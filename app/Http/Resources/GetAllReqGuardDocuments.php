<?php

namespace App\Http\Resources;

use App\Models\DocumentCategory;
use App\Models\GuardWorkDetail;
use Illuminate\Http\Resources\Json\JsonResource;

class GetAllReqGuardDocuments extends JsonResource
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
            'doc_required' => $this->doc_required,
            'id' => $this->id,
            'is_deleteable' => $this->is_deleteable,
            'guard_id' => $this->guard_id ?: '', // Using null coalescing operator
            'document_name' => $this->document_name ?: '',
            'document_type' => $this->document_type ?: '',
            'document_expire' => ($this->document_expire === 'current, pending renewal') ? 
                                 $this->document_expire : 
                                 ($this->document_expire ? usaToAus($this->document_expire) : ''),
            'notes' => $this->notes ?: '',
            'document_no' => $this->document_no ?: '',
            'c_f_roster' => (bool) $this->c_f_roster,
            'c_f_profile' => (bool) $this->c_f_profile,
            'file' => $this->file ? returnImgPath('guard_documents', $this->file) : '',
        ];
    }
    
}
