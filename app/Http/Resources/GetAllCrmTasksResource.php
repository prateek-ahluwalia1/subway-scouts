<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class GetAllCrmTasksResource extends JsonResource
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
            'task_owner' => !empty($this->task_owner) ? $this->task_owner : '',
            'subject' => !empty($this->subject) ? $this->subject : '',
            'selectedType' => !empty($this->selectedType) ? $this->selectedType : '',
            'due_date' =>  !empty($this->due_date) ? $this->due_date : '',
            'contact' => !empty($this->lead)  ? $this->lead->name : '',
            'agent' => !empty($this->agent_id) ? getSalePersonName($this->agent_id) : '',
            'status' => !empty($this->status) ? $this->status : '',
            'priority' => !empty($this->priority) ? $this->priority : '',
            'reminder' => !empty($this->reminder) ? $this->reminder : '',
            'repeat' => !empty($this->repeat) ? $this->repeat : '',
            'description' => !empty($this->description) ? $this->description : '',
        ];
    }
}
