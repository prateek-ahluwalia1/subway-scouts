<?php

namespace App\Http\Resources;
use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

class MarkAsReadResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        if($this->mark_as_read != null){
            if(in_array($this->admin_id, json_decode($this->mark_as_read))){
                return [
                   'id' => $this->id,
                   'title' => $this->title,
                   'note' => $this->note,
                   'is_read' => true,
                   'mark_as_read' => $this->mark_as_read,
                   'created_by' => !empty($this->created_by) ? getAdminName($this->created_by): 'N/A',
                   'created_at' => Carbon::parse($this->created_at)->format('d-m-Y H:i'),
               ];
            }else{
                return [
                   'id' => $this->id,
                   'title' => $this->title,
                   'note' => $this->note,
                   'is_read' => false,
                   'mark_as_read' => $this->mark_as_read,
                   'created_by' => !empty($this->created_by) ? getAdminName($this->created_by): 'N/A',
                   'created_at' => Carbon::parse($this->created_at)->format('d-m-Y H:i'),
               ];

            }
        }else{
            return [
                'id' => $this->id,
                'title' => $this->title,
                'note' => $this->note,
                'mark_as_read' => $this->mark_as_read,
                'is_read' => false,
                'created_by' => !empty($this->created_by) ? getAdminName($this->created_by): 'N/A',
                'created_at' => Carbon::parse($this->created_at)->format('d-m-Y H:i'),
            ];
        }
    }
}