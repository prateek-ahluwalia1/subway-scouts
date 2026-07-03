<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class RosterDeletedShifts extends JsonResource
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
            'reason' => !empty($this->reason) ? $this->reason : '',
            'deleted_by' =>  !empty($this->deleted_by) ? getAdminName($this->deleted_by) : '',
            'site_name' =>  (isset($this->site_id) && !empty($this->site_id)) ? getSiteName($this->site_id) : '',
            'runsheet_name' =>  (isset($this->run_sheet_id) && !empty($this->run_sheet_id)) ? getRunsheetName($this->run_sheet_id) : '',
            'staff_name' =>  !empty($this->guard_id) ? getGuardName($this->guard_id) : '',
            'start' => !empty($this->start) ? usaToAusDateTime($this->start) : '',
            'end' => !empty($this->end) ? usaToAusDateTime($this->end) : '',
            'deleted_at' =>  !empty($this->deleted_at) ? usaToAusDateTime($this->deleted_at) : '',
        ];
    }
}
