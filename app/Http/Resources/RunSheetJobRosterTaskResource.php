<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class RunSheetJobRosterTaskResource extends JsonResource
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
            'task' =>$this->task,
            'task_start' => usaToAusDateTime($this->task_start),
            'task_end' => usaToAusDateTime($this->task_end),
        ];
    }
}
