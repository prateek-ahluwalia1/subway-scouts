<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class MainTaskReportResource extends JsonResource
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
            'start' => $this->start,
            'end' => $this->end,
            'first_name' => !empty($this->guardz) ? $this->guardz->first_name: '',
            'middle_name' => !empty($this->guardz) ? $this->guardz->middle_name: '',
            'last_name' => !empty($this->guardz) ? $this->guardz->last_name: '',
            'guard_id' => $this->guard_id,
            'site_id' => $this->site_id,
            'site_name' => !empty($this->site) ? $this->site->site_name : '',
            'total_tasks' => !empty($this->jobRosterTask) ? count($this->jobRosterTask) : 0,
        ];
    }
}
