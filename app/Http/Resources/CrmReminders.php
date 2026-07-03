<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CrmReminders extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $notify_also = json_decode($this->notify_too);
        $notifyArr = [];
        if($notify_also){
            foreach($notify_also as $notify){
                $notifyArr[] = getAdminName($notify);
            }
        }
        return [
            'id' => $this->id,
            'lead_id' => $this->lead_id,
            'created_by' => $this->created_by,
            'subject' => $this->subject,
            'date' => $this->date,
            'notify_too' => $this->notify_too,
            'description' => $this->description,
            'status' => $this->status,
            'createdBy' => $this->createdBy,
            'notifyAlso' => $notifyArr,
        ];
    }
}
