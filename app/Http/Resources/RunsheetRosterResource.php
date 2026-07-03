<?php

namespace App\Http\Resources;

use App\Models\Customer;
use App\Models\User;
use Illuminate\Http\Resources\Json\JsonResource;

class RunsheetRosterResource extends JsonResource
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
            'name' => $this->name,
            'start' => !empty($this->start) ?  usaToAus($this->start) : null,
            'end' =>   !empty($this->end) ? usaToAus($this->end) : null,
            // "admins" => !empty($this->admins) ? json_decode($this->admins) : null,
            // "customers" => !empty($this->customers) ? json_decode($this->customers) : null,
            "admins" => $this->admins!= '' && $this->admins!= 'null' && $this->admins!= null ?  (User::where(function($query) use ($request){
                foreach (json_decode($this->admins, true) as $key => $value) {
                   $query->orWhere('id', $value);
                }
             })->select('id','name')->get()) : '',
            "customers" => getCustomerNameId($this->customers),
            "status" => $this->status,
        ];
    }
}
