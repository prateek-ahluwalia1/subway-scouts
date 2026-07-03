<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class BeforeUpdateResource extends JsonResource
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
            'Roster Name' => !empty($this->newJobRoster) ? $this->newJobRoster->roster_name : '',
            'Start' => !empty($this->start) ? usaToAus($this->start) : '', 
            'End' => !empty($this->end) ?  usaToAus($this->end) : '',  
            'Guard Name' => !empty($this->guard_id) ? getGuardName($this->guard_id) : '',
            'Site Name' => !empty($this->site_id) ? getSiteName($this->site_id) : '',
            'Shift Payable' => !empty($this->shift_payable) ? $this->shift_payable : '',
            'shift Chargeable' => !empty($this->shift_chargeable) ? $this->shift_chargeable : '',
            'Payrate Level' => !empty($this->payrate_level) ? $this->payrate_level : '',
            'Payrate' => !empty($this->rosterPayrate) ? $this->rosterPayrate->title : '',
            'Chargerate Level' => !empty($this->chargerate_level) ? $this->chargerate_level : '',
            'Chargerate' => !empty($this->rosterChargeRate) ? $this->rosterChargeRate->title : '',
            'Training' => !empty($this->training)  && $this->training == 0 ? 'No' : 'Yes',
            'Continuation' => !empty($this->continuation) && $this->continuation == 0 ? 'No' : 'yes',
            'Over Time Value' => !empty($this->over_time_value) ? $this->over_time_value : '',
            'Travel Time Value' => !empty($this->travel_time_value) ? $this->travel_time_value : '',
            'Conflict' => !empty($this->conflict) ? 'There is conflict with other shift'.' '.usaToAusDateTime($this->conf_start).' '.usaToAusDateTime($this->conf_end) : '',
            //'doc_conf' => !empty($this->doc_conf) && $this->doc_conf ==  ? $this->doc_conf : '',
            'Work Limitaion Conf' => !empty($this->work_limitaion_conf) ? 'Exceed from work limitaion' : '',
            'Job Status' => !empty($this->job_status) ? $this->job_status : '',
            'Publish Status' => !empty($this->publish_status) && $this->publish_status == 0  ? 'Un Published' : 'Published',
            'Updated At' => !empty($this->updated_at) ? usaToAus($this->updated_at) : '',
        ];
    }
}