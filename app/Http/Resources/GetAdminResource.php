<?php

namespace App\Http\Resources;

use App\Models\Customer;
use App\Models\Site;
use Illuminate\Http\Resources\Json\JsonResource;

class GetAdminResource extends JsonResource
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
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'image' => returnImgPath('admin',$this->image),
            'specific_customer' => $this->specific_customer!= '' && $this->specific_customer!= 'null' && $this->specific_customer!= null ?  (Customer::where(function($query) use ($request){
                foreach (json_decode($this->specific_customer, true) as $key => $value) {
                   $query->orWhere('id', $value);
                }
             })->select('id','name')->get()) : '',

            'specific_sites' => $this->specific_sites!= '' && $this->specific_sites!= 'null' && $this->specific_sites!= null ?  (Site::where(function($query) use ($request){
                foreach (json_decode($this->specific_sites, true) as $key => $value) {
                   $query->orWhere('id', $value);
                }
             })->select('id','site_name')->get()) : '',
            'status' => $this->status,
            'userType' => $this->userType,
            'role_id' => $this->role_id,
            'abn' => $this->abn,
            'acn' => $this->acn,
            'bsb' => $this->bsb,
            'bank_name' => $this->bank_name,
            'account_no' => $this->account_no,
            'state' => $this->state,
            'qr_image' => $this->qr_image,
            'is_2fa_enable' => $this->is_2fa_enable,
        ];
    }
}
