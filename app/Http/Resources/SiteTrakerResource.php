<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class SiteTrakerResource extends JsonResource
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
            'reason' => $this->reason,
            'roster_id' => $this->roster_id,
            'date' => dateFormat($this->created_at),
            'time' => timeFormat($this->created_at),
            'updated_column' => $this->updated_colums
            //'before_update' => new BeforeUpdateGuardResource(json_decode($this->data)),
        ];
    }
}
