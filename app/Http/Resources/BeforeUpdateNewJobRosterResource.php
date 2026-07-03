<?php

namespace App\Http\Resources;

use App\Models\Customer;
use App\Models\Site;
use App\Models\User;
use Illuminate\Http\Resources\Json\JsonResource;

class BeforeUpdateNewJobRosterResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
      // $us = $this->user_id!= '' && $this->user_id!= 'null' && $this->user_id!= null ?  (User::where(function($query) use ($request){
         //    foreach (json_decode($this->user_id, true) as $key => $value) {
         //       $query->orWhere('id', $value);
         //    }
         // })->select('name')->get()) : '';
      //   $cus = $this->customer_id!= '' && $this->customer_id!= 'null' && $this->customer_id!= null ?  (Customer::where(function($query) use ($request){
      //       foreach (json_decode($this->customer_id, true) as $key => $value) {
      //          $query->orWhere('id', $value);
      //       }
      //    })->select('name')->get()->toArray()) : '';

      // $st = $this->site_id!= '' && $this->site_id!= 'null' && $this->site_id!= null ?  (Site::where(function($query) use ($request){
         //    foreach (json_decode($this->site_id, true) as $key => $value) {
         //       $query->orWhere('id', $value);
         //    }
         // })->select('site_name')->get()->toArray()) : '';

         $cus = $this->customer_id != '' && $this->customer_id != 'null' && $this->customer_id != null
         ? Customer::whereIn('id', json_decode($this->customer_id, true))
               ->pluck('name')
               ->implode(',')
         : '';

         $st = $this->site_id != '' && $this->site_id != 'null' && $this->site_id != null
         ? Site::whereIn('id', json_decode($this->site_id, true))
               ->pluck('site_name')
               ->implode(',')
         : '';
         $us = $this->user_id != '' && $this->user_id != 'null' && $this->user_id != null
         ? User::whereIn('id', json_decode($this->user_id, true))
               ->pluck('name')
               ->implode(',')
         : '';

        return [
            'Roster Name' => !empty($this->roster_name) ? $this->roster_name : '',
            'Start' => !empty($this->start) ? usaToAus($this->start) : '',
            'End' => !empty($this->end) ? usaToAus($this->end) : '',
            'Customers' =>   $cus,
            'Sites' =>   $st,
            'Users' =>   $us,
            'Status' =>  $this->status,
        ];
    }
}
