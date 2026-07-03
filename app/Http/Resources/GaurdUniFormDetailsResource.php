<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class GaurdUniFormDetailsResource extends JsonResource
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
            'id' => (!empty($this->guardUniForm) && !empty($this->guardUniForm->id)) ? $this->guardUniForm->id : '', 
            'guard_name' => $this->first_name.' '.$this->middle_name.' '.$this->last_name,
            'email' => $this->email,
            'address' => $this->address,
            'phone' => $this->phone,
            'date_of_issue' => (!empty($this->guardUniForm) && !empty($this->guardUniForm->created_at)) ? usaToAus($this->guardUniForm->created_at) : '',
            'notes' => (!empty($this->guardUniForm) && !empty($this->guardUniForm->notes)) ? $this->guardUniForm->notes : '',
            'uniform_type' => (!empty($this->guardUniForm) && !empty($this->guardUniForm->uniform_type)) ? json_decode($this->guardUniForm->uniform_type) : '',
            'date_of_return' => (!empty($this->guardUniForm) && !empty($this->guardUniForm->date_of_return)) ? usaToAus($this->guardUniForm->date_of_return) : '', //
            'form_fill_date' => (!empty($this->guardUniForm) && !empty($this->guardUniForm->form_fill_date)) ? usaToAus($this->guardUniForm->form_fill_date) : '', //
            'signature' => (!empty($this->guardUniForm) && !empty($this->guardUniForm->signature)) ? $this->guardUniForm->signature : '', //
        ];
    }
}
