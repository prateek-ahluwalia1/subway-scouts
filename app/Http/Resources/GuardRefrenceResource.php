<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class GuardRefrenceResource extends JsonResource
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
            'guard_id' => $this->guard_id,
            'description' => $this->description,
            'phy_dis' => $this->phy_dis,
            'ner_dis' => $this->ner_dis,
            'bron_dis' => $this->bron_dis,
            'med_cond' => $this->med_cond,
            'work_inj' => $this->work_inj,
            'smoke' => $this->smoke,
            'work_history' => json_decode($this->work_history),
        ];
    }
}
