<?php

namespace App\Http\Resources;

use App\Models\Customer;
use App\Models\Site;
use Illuminate\Http\Resources\Json\JsonResource;

class GuardInductionResource extends JsonResource
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
            'induction_file' => returnImgPath('guard_employment_details',$this->induction_file),
            'customer' => !empty($this->customer) ? Customer::where('id', $this->customer)->select('id', 'name')->first() : 'null',
            'site' => !empty($this->site) ? Site::where('id', $this->site)->select('id', 'site_name')->first() : 'null',
            'customer_site_status' => $this->customer_site_status,
        ];
    }
}
