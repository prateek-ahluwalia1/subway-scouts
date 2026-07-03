<?php

namespace App\Http\Resources;

use App\Models\Customer;
use App\Models\User;
use Illuminate\Http\Resources\Json\JsonResource;

class AllNewJobRosterResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $request = $this;
        return [
            'id' => $this->id,
            'roster_name' => $this->roster_name,
            'start' => usaToAus($this->start),
            'end' =>   ($this->end != '' && $this->end != null ? usaToAus($this->end) : null),
            "user_id" => $this->user_id!= '' && $this->user_id!= 'null' && $this->user_id!= null ?  (User::where(function($query) use ($request){
                foreach (json_decode($this->user_id, true) as $key => $value) {
                   $query->orWhere('id', $value);
                }
             })->select('id','name')->get()) : '',

             "customer_id" => $this->customer_id!= '' && $this->customer_id!= 'null' && $this->customer_id!= null ?  (Customer::where(function($query) use ($request){
                foreach (json_decode($this->customer_id, true) as $key => $value) {
                   $query->orWhere('id', $value);
                }
             })->select('id','name')->get()) : '',
            "status" => $this->status,
        ];
    }
}
