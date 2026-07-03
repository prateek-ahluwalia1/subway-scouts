<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class JobSignInSignOutResource extends JsonResource
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
            'signin_date' => !empty($this->signin_time) ? usaToAus($this->signin_time) : '',
            'signin_time' => !empty($this->signin_time) ? usaToAusTime($this->signin_time) : '',
            'signin_notes' => (!empty($this->signin_notes) ? $this->signin_notes : ''),
            'signin_selfie' => !empty($this->signin_selfie) ? returnImgPathCheck('uploads', $this->signin_selfie) : '',
            'signin_location' => !empty($this->location) ? $this->location : '',
            'signout_date' => !empty($this->signout_time) ? usaToAus($this->signout_time) : '',
            'signout_time' => !empty($this->signout_time) ? usaToAusTime($this->signout_time) : '',
            'signout_notes' => !empty($this->signout_notes) ? $this->signout_notes : '',
            'signout_selfie' => !empty($this->signout_selfie) ? returnImgPathCheck('uploads', $this->signout_selfie) : '',
            'signout_location' => !empty($this->signout_location) ? $this->signout_location : '', //
        ];
    }
}
