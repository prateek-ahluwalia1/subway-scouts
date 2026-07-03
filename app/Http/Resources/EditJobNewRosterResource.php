<?php

namespace App\Http\Resources;

use App\Models\Customer;
use App\Models\Site;
use App\Models\User;
use Illuminate\Http\Resources\Json\JsonResource;

class EditJobNewRosterResource extends JsonResource
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
            'roster_name' => $this->roster_name,
            'state' => $this->state,
            'start' => $this->start,
            'end' => $this->end,
            'status' => $this->status,
            'customer_id' =>  $this->customer_id!= '' && $this->customer_id!= 'null' && $this->customer_id!= null ?  (Customer::where(function($query) use ($request){
                foreach (json_decode($this->customer_id, true) as $key => $value) {
                   $query->orWhere('id', $value);
                }
             })->select('id','name')->get()) : '',
            'site_id' =>  $this->site_id!= '' && $this->site_id!= 'null' && $this->site_id!= null ?  (Site::where(function($query) use ($request){
                foreach (json_decode($this->site_id, true) as $key => $value) {
                   $query->orWhere('id', $value);
                }
             })->select('id','site_name')->get()) : '',
            "user_id" => $this->user_id!= '' && $this->user_id!= 'null' && $this->user_id!= null ?  (User::where(function($query) use ($request){
                foreach (json_decode($this->user_id, true) as $key => $value) {
                   $query->orWhere('id', $value);
                }
             })->select('id','name')->get()) : ''
        ];
    }
}


