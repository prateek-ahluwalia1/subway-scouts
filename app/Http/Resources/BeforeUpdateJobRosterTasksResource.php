<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class BeforeUpdateJobRosterTasksResource extends JsonResource
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
            'Task' => !empty($this->task) ? $this->task : '',
            'Task Start' => !empty($this->task_start) ? $this->task_start  : '',
            'Task End' => !empty($this->task_end) ? $this->task_end : '',
            'Status' => !empty($this->status) ? $this->status : '',
            'Start Location' => !empty($this->start_location) ? $this->start_location : '',
            'End Location' => !empty($this->end_location) ? $this->end_location : '',

        ];
    }
}
