<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class VisaDetailResource extends JsonResource
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
            'name' => $this->guard_name,
            'country' => $this->country,
            'dob' => $this->dob,
            'passport_no' => $this->passport_no,
            'is_correct' => $this->is_correct,
            'action_type' => json_decode($this->details, true),
            'date' => $this->created_at,
        ];
    }
}
