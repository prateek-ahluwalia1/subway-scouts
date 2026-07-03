<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class GuardFileNameResource extends JsonResource
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
            'file' => !empty($this->file) ? returnImgPath('guard_documents',$this->file) : 'N/A',
            'document_name' => !empty($this->document_name) ? $this->document_name : 'N/A',
            'created_at' => !empty($this->created_at) ? usaToAus($this->created_at) : 'N/A',
            'document_expire' => !empty($this->document_expire) ? usaToAus($this->document_expire) : 'N/A',
        ];
    }
}
