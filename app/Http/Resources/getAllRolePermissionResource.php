<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class getAllRolePermissionResource extends JsonResource
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
            'id' => $this->id,
            'role' => $this->role,
            'permissions' => json_decode($this->permissions),
        ];
    }
}
