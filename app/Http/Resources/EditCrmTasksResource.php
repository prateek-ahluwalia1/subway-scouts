<?php

namespace App\Http\Resources;

use App\Models\crm\SalePersonModel;
use Illuminate\Http\Resources\Json\JsonResource;

class EditCrmTasksResource extends JsonResource
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
            'contact' => !empty($this->contact)  ? $this->contact : '',
            'status' => !empty($this->status) ? $this->status : '',
            'agent_id' => $this->agent_id,
            'priority' => !empty($this->priority) ? $this->priority : '',
            'reminder' => ($this->reminder == 1) ? true : false,
            'repeat' => !empty($this->repeat == 1) ? true : false,
            'description' => !empty($this->description) ? $this->description : '',
        ];
    }
}
