<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class EditBusinessSettingResource extends JsonResource
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
           'business_name' => $this->title,
           'business_status' => $this->business_type,
           'address'  => $this->address,
           'email' => $this->email,
           'domain' => $this->domain,
           'business_type' => $this->guard,
           'app_id'  => $this->app_id,
           'package'  => $this->package,
           'server_key' => $this->server_key,
           'about_company' => $this->about_company,
           'database_name' => $this->database_name,
           'temp_logo' => returnImgPath('business_setting',$this->logo),
           'about_file' => returnImgPath('business_setting',$this->about_company_file),
        ];
    }
}
