<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class StaffDataResource extends JsonResource
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
            //dd($this->guardDocuments),
            'id' => $this->id,
            'first_name' => !empty($this->first_name) ? $this->first_name : '',
            'middle_name' => !empty($this->middle_name) ? $this->middle_name : '',
            'last_name' => !empty($this->last_name) ? $this->last_name : '',
            'profile_image' => returnImgPath('guard',$this->profile_image),
            'email' => $this->email,
            'phone'  => $this->phone,
            'internal_id'  => !empty($this->internal_id) ? $this->internal_id : 'N/A',
            'guard_documents' => GuardSecurityLienceResource::collection($this->guardDocuments),
            'guardExternalIds'=> GuardExternalResource::collection($this->guardExternalIds),
            'guardJobRoster'=> TwoWeaksGuardShiftResource::collection($this->guardJobRoster)
        ];
    }
}
