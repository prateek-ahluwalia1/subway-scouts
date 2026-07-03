<?php

namespace App\Http\Resources;

use App\Models\Guard;
use Illuminate\Http\Resources\Json\JsonResource;

class RunSheetJobsResource extends JsonResource
{
    
    public function toArray($request)
    {
        $status = '';
        $current_date_time = time();
        $end_time = strtotime($this->end);
        $start_time = strtotime($this->start);
        if(($current_date_time > $start_time) && ($this->signin_status == 0) && ($this->job_status == 'confirmed')){
            $status = 'missed';
        }

        if (!empty($status)) {
            $is_status = $status;
         }else{
             if (!empty($this->job_status)) {
                 $is_status = $this->job_status;
             }
             else{
                 $is_status= null;
             }
         }


        $guard = Guard::where('id', $this->guard_id)->select('first_name', 'middle_name', 'last_name', 'id', 'phone')->first();
        if ($this->update_status == 1) {
            \DB::table('run_sheet_job_rosters')->where('id', $this->id)->update(['update_status' => 0]);
        }
        $completedTasks = $this->RunSheetJobRosterTask->filter(function($task) {
            return !is_null($task['end_time']);
        })->count();

        return [
            'start' => $this->start !='' ? usaToAusDateTime($this->start) : null,
            'start_time' => $this->start !='' ? timeFormat($this->start) : null,
            'end' => $this->end !='' ? usaToAusDateTime($this->end) : null,
            'end_time' => $this->start !='' ? timeFormat($this->end) : null,
            'date' => $this->start !='' ? dateFormat($this->start) : null,
            'roster_id' => $this->id, 
            'on_call_job' => $this->on_call_job, 
            'publish_status' => $this->publish_status, 
            'run_sheet_id' => $this->run_sheet_id,
            'operation_notes' => !empty($this->operation_notes) ? $this->operation_notes : '',
            'unprofile_name' => !empty($this->unprofile_name) ? $this->unprofile_name : null,
            'training' => ($this->training == 0 ? false : true),
            'runsheet_name' => !empty($this->runsheet->title) ? $this->runsheet->title : '',
            'guard_id' => $this->guard_id,
            'first_name' => (!empty($guard) && $guard != null) ? $guard->first_name : null,
            'phone' => (!empty($guard) && $guard != null) ? $guard->phone : null,
            'middle_name' => (!empty($guard) && $guard != null) ? $guard->middle_name : null,
            'last_name' => (!empty($guard) && $guard != null) ? $guard->last_name : null,
            'status' => $is_status,
            'shiftTasksCount' => $this->RunSheetJobRosterTask->count(),
            'completedTaskCount' => $completedTasks,
            'signin_status' => $this->signin_status !='' ? $this->signin_status : null,
            'confilict' => $this->conflict !='' ? $this->conflict : null,
            'document_confilict' => $this->doc_conf !='' ? $this->doc_conf : null,
            'work_limitaion_confilict' =>  $this->work_limitaion_conf !='' ?  'You exceed form you work limitaions you only create this hours shift'.' '.$this->work_limitaion_conf: null,
            'confilict_start' => $this->conf_start !='' ? usaToAusDateTime($this->conf_start) : null,
            'confilict_end' => $this->conf_end !='' ? usaToAusDateTime($this->conf_end) : null,
            'rejected_by' => !empty($this->rejected_by) ? getGuardName($this->rejected_by) : null,
            'jobRosterTasks' => RunSheetJobRosterTaskResource::collection($this->RunSheetJobRosterTask),
        ];
    }
}
