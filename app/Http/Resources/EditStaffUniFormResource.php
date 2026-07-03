<?php

namespace App\Http\Resources;

use App\Models\Customer;
use Illuminate\Http\Resources\Json\JsonResource;

class EditStaffUniFormResource extends JsonResource
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
            'guard_id' => $this->guard_id,
            'customer_id' => Customer::where('id', $this->customer_id)->select('id', 'name')->first(),
            // 'customer_id' =>  $this->customer_id!= '' && $this->customer_id!= 'null' && $this->customer_id!= null ?  (Customer::where(function($query) use ($request){
            //     foreach (json_decode($this->customer_id, true) as $key => $value) {
            //        $query->orWhere('id', $value);
            //     }
            //  })->select('name')->get()) : '',
            'uniform_type' => $this->uniform_type,
            'note' => !empty($this->note) ? $this->note : null,
            'quantity' => !empty($this->quantity) ? $this->quantity : null,
            'size' => !empty($this->size) ? $this->size : null,
            'return_status' => ($this->return_status == 1) ? true : false,
        ];
    }
}
