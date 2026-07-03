<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class BeforeUpdateStaffFeedbackResource extends JsonResource
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
            'Admin Name' => !empty($this->admin_id) ? getAdminName($this->admin_id) : 'N/A',
            'Guard Name' => !empty($this->admin_id) ? getGuardName($this->admin_id) : 'N/A',
            'Feedback' => !empty($this->feedback) ? getAdminName($this->feedback)   : 'N/A',
            'On Edit' => !empty($this->on_edit) ? usaToAusDateTime($this->on_edit)  : 'N/A',
            'Updated At' => !empty($this->updated_at) ? usaToAusDateTime($this->updated_at)  : 'N/A',
        ];
    }
}
