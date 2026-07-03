<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class GuardPersonalRefrenceResource extends JsonResource
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
            'guard_id' => $this->guard_id,
            'name' => $this->name,
            'relationship' => $this->relationship,
            'contact_no' => $this->contact_no,
            'applicant_signature' => $this->applicant_signature,
            'print_full_name' => $this->print_full_name,
            'current_date' => $this->current_date,
            'check_and_interviewed_by' => $this->check_and_interviewed_by,
        ];
    }
}
