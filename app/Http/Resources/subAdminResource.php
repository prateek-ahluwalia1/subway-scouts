<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class subAdminResource extends JsonResource
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
        return[
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'image' => returnImgPath('admin',$this->image),
            'status' => $this->status,
            'last_login' => !empty($this->last_login) ? $this->last_login : 'N/A',
            'userType' => $this->userType,
            'role' =>  !empty($this->RolePermission) && !empty($this->RolePermission->role) ?  $this->RolePermission->role : 'N/A',
        ];
    }
}
