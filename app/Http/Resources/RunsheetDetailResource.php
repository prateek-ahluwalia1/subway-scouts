<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class RunsheetDetailResource extends JsonResource
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
            // 'no_calls' => $this->no_calls,
            'run_sheet_id' => $this->run_sheet_id,
            'site_id' => $this->site_id,
            'customer_id' => $this->customer_id,
            'site_name' => getSiteName($this->site_id),
            'updated_at' => !empty($this->updated_at) ? usaToAusDateTime($this->updated_at) : '',
        ];
    }
}
