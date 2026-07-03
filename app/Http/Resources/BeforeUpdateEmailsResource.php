<?php

namespace App\Http\Resources;

use App\Models\Guard;
use DB;
use Illuminate\Http\Resources\Json\JsonResource;

class BeforeUpdateEmailsResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $items = $this->resource!= '' && $this->resource!= 'null' && $this->resource!= null ?  (Guard::where(function($query) use ($request){
            foreach (json_decode($this->resource, true) as $key =>  $value) {
               $query->orWhere('id', $value);
            }
         })->select('first_name', 'middle_name', 'last_name', 'email', 'phone', 'address')->get()) : '';

         $fname = !empty($items->first_name) ? $items->first_name : '';
         $mname = !empty($items->middle_name) ? $items->middle_name : '';
         $lname = !empty($items->last_name) ? $items->last_name : '';
         

        return [
            'Guard Name' => $fname.' '.$mname.' '.$lname,
            'Guard Email' => !empty($items->email) ? $items->email  : '',
            'Guard Phone' => !empty($items->phone) ? $items->phone  : '',
            'Guard Address' => !empty($items->address) ? $items->address : '',
        ];
    }
}
