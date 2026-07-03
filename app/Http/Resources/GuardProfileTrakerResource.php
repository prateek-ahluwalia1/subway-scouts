<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class GuardProfileTrakerResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return[
            'id' => $this->id,
            'action_by' =>  !empty($this->action_by) ? getAdminName($this->action_by) : '',
            'action_type' => returnAction($this->action_type),
            'action_on' => $this->action_on,
            'roster_id' => $this->roster_id,
            'reason' => !empty($this->reason) ? $this->reason : 'N/A',
            'date' => dateFormat($this->created_at),
            'updated_data' => json_decode($this->updated_colums),
            'time' => timeFormat($this->created_at),
            'before_update' => new BeforeUpdateGuardResource(json_decode($this->data)),
        ];
    }
}
