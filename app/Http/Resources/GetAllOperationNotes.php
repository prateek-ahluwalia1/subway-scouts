<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class GetAllOperationNotes extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $adminNames = [];
        if($this->mark_as_read != null){
            $markAsReadArray = json_decode($this->mark_as_read);
            foreach ($markAsReadArray as $adminId) {
                $adminNames[] = getAdminName($adminId);
            }
        }
        return[
            'id' => $this->id,
            //'user_id' => $this->user_id,
            'title' => $this->title,
            'note' =>  !empty($this->note) ?  $this->note : 'N/A',
            'mark_as_read' => $adminNames,
            'updated_at' => usaToAusDateTime($this->updated_at),
            'is_read' => $this->mark_as_read != null ? in_array($this->admin_id, $markAsReadArray) : false,
            'created_at' => usaToAusDateTime($this->created_at)
        ];
    }
}
