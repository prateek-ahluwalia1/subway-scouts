<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class BeforeUpdateStaffDocumentResource extends JsonResource
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
            'Guard Name' => !empty($this->guard_id) ? getGuardName($this->guard_id) : 'N/A',
            'Document Category' => !empty($this->document_category) ? $this->document_category : 'N/A',
            'Document Name' => !empty($this->document_name) ? $this->document_name : 'N/A',
            'Document Exp' => !empty($this->document_expire) ? $this->document_expire : 'N/A',
            'Document No' => !empty($this->document_no) ? $this->document_no : 'N/A',
            'Side' => !empty($this->side) ? $this->side : 'N/A',
            'Document Type' => !empty($this->document_type) ? $this->document_type : 'N/A',
            'Notes' => !empty($this->notes) ? $this->notes : 'N/A',
            'File' => !empty($this->file) ? returnImgPath('guard_documents', $this->file) : 'N/A',
            'Compulsorily for roster' => !empty($this->c_f_roster) && $this->c_f_roster == 0 ? 'No' : 'Yes', 
            'Compulsorily for profile' => !empty($this->c_f_profile) && $this->c_f_profile == 0 ? 'No' : 'Yes', 
        ];
    }
}
