<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class EditGuardDocumentResource extends JsonResource
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
            'guard_id' => $this->guard_id,
            'document_name' => $this->document_name,
            'side' => $this->side,
            'type' => $this->type,
            'document_type' => $this->document_type,
            'is_deleteable' => $this->is_deleteable,
            'document_no' => $this->document_no,
            'document_expire' => $this->document_expire ? usaToAus($this->document_expire) : null,
            'notes' => $this->notes,
            'c_f_roster' => $this->c_f_roster,
            'c_f_profile' => $this->c_f_profile,
            'file' => $this->file ? returnImgPath('guard_documents',$this->file) : null,
        ];
    }
}
