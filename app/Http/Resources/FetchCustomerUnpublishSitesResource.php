<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class FetchCustomerUnpublishSitesResource extends JsonResource
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
            'start' => usaToAusDateTime($this->start),
            'end' => usaToAusDateTime($this->end),
            'site_name' => $this->site_name,
            'guard_name' => $this->first_name.' '.$this->middle_name.' '.$this->last_name,
            'conflict' => $this->conflict,
            'doc_conf' => $this->doc_conf,
            'conf_start' => $this->conf_start,
            'conf_end' => $this->conf_end,
            'work_limitaion_conf' => $this->work_limitaion_conf
        ];
    }
}
