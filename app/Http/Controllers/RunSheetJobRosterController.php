<?php

namespace App\Http\Controllers;

use App\Http\Resources\AllTemplateShifts;
use App\Http\Resources\EditRunSheetJobRosterResource;
use App\Http\Resources\RunSheetJobRosterShiftsByGuardResource;
use App\Http\Resources\RunSheetJobRosterShiftsResource;
use App\Models\Guard;
use App\Models\GuardWorkDetail;
use App\Models\RunSheet;
use App\Models\RunSheetJobRoster;
use App\Models\RunSheetJobRosterTask;
use DB;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Http\Resources\FetchCustomerUnpublishSitesResource;
use App\Http\Resources\RosterDeletedShifts;

class RunSheetJobRosterController extends Controller
{
    
    public function addNewShift(Request $request){

        // $jobNewRoster = JobNewRoster::where('id', $request->roster_id)->first();
    
        // if (
        //     (dbFormate($request->start) < $jobNewRoster->start) ||
        //     (
        //         isset($jobNewRoster->end) &&
        //         ($jobNewRoster->end !== null) &&
        //         (dbFormate($request->start) > $jobNewRoster->end)
        //     )
        // ) {
        //     return response()->json([
        //         'success' => false,
        //         'hide' => true,
        //         'message' => 'Shift timing must fall within the roster date.'
        //     ]);
        // }
    
    
        if(isset($request->guard_id) && $request->guard_id > 0){
            # CHECK DIFFERENCE BETWEEN SHIFTS
            $checkAdmin = checkAdmin($request->admin_id);
            if($checkAdmin != 'super-admin'){
                # CHECK SHIFT SHOULD BE LESS THEN 8 HOURS IF YOUR ADMIN
                // $startDate = Carbon::createFromFormat('m-d-Y H:i', $request->start);
                // $endDate = Carbon::createFromFormat('m-d-Y H:i', $request->end);
                // $checkDiff = $startDate->diff($endDate);
                // if($checkDiff->h > 8){
                //     return response()->json(['success' => false, 'hide' => true, 'message' => 'You can not create shift more then 8 hours!']); 
                // }
                $diff =  checkShiftDayHours($request->start, $request->end, $request->guard_id);
                if($diff < 9){
                    return response()->json(['success' => false, 'message' => 'You must rest for eight hours before starting a new shift!']); 
                }
    
    
            }
            $guard_leave = checkGuardOnLeave($request->start, $request->end, $request->guard_id);
            if($guard_leave == 'leave'){
                return response()->json(['success' => false, 'message' => 'Sorry Staff On Leave!', 'code'=> 404]);
            }
            # CHECK GUARD DOCS ARE SET AND NOT EXPIRED
            $checkGuardDocs = true;
            $guardDetails = GuardWorkDetail::where('guard_id', $request->guard_id)->first();
            if (empty($guardDetails->guard_document_type)) {
                // return "Please First Add Your Residential Status!";
                $checkGuardDocs = false;
            }
            // $today = strtotime(date("Y/m/d"));
            $t = dbFormate($request->start);
            $today = strtotime($t);
            $guard = Guard::where('id', $request->guard_id)->with('guardDocuments')->first();
            foreach ($guard->guardDocuments as $document) {
                if ($document->c_f_roster == 1) {
                    if ($document->document_category == 'citizen') {
                        if ($document->document_type == 'security_license' &&
                            ($document->document_expire === '' || $document->document_expire === null || $today > strtotime($document->document_expire))) {
                            $docExpire = "Security License Expired!";
                            $checkGuardDocs = false;
                        } else {
                            // return 'active';
                            $checkGuardDocs = true;
                        }
                    } else {
                        if (in_array($document->document_type, ['visa', 'passport', 'security_license']) &&
                            ($document->document_expire === '' || $document->document_expire === null || $today > strtotime($document->document_expire))) {
                            $docExpire = ucfirst($document->document_type) . " Expired!";
                            $checkGuardDocs = false;
                        }
                    }
                }
            }
            if ($checkGuardDocs == false && !isset($request->shift_confirm)) {
                if($checkAdmin == 'super-admin'){
                    return response()->json(['data'=>$checkGuardDocs,'success' => false, 'message' => '<b>Hi, Super Admin this shift has document expired or not updated <br> Do you really want to create this shift !</b>', 'code'=> 404]);
                }
                if($checkAdmin == 'admin'){
                    return response()->json(['success' => false, 'message' => '<bHi Admin, this guard has document expired or not updated so you cant create a shift!</b>', 'code'=> 404]);
                }
            }
            # CHECK SHIFT CONFILICT
            if(isset($request->id) && $request->id > 0){
                $addNewShift = RunSheetJobRoster::findOrFail($request->id);
                $start_time = dbFormateDateTime($request->start);
                $end_time = dbFormateDateTime($request->end);
                $conflictingShift = RunSheetJobRoster::where('guard_id', $request->guard_id)
                ->where(function ($query) use ($start_time, $end_time) {
                    $query->where(function ($q) use ($start_time) {
                        $q->where('start', '<=', $start_time)->where('end', '>', $start_time);
                    })->orWhere(function ($q) use ($end_time) {
                        $q->where('start', '<=', $end_time)->where('end', '>=', $end_time);
                    })->orWhere(function ($q) use ($start_time, $end_time) {
                        $q->where('start', '>=', $start_time)->where('end', '<=', $end_time);
                    });
                })
                ->where('id', '!=' ,$addNewShift->id)->select('id', 'start', 'end')->first();
            }else{
                $start_time = dbFormateDateTime($request->start);
                $end_time = dbFormateDateTime($request->end);
                $conflictingShift = RunSheetJobRoster::where('guard_id', $request->guard_id)
                ->where(function ($query) use ($start_time, $end_time) {
                    $query->where(function ($q) use ($start_time) {
                        $q->where('start', '<=', $start_time)->where('end', '>', $start_time);
                    })->orWhere(function ($q) use ($end_time) {
                        $q->where('start', '<=', $end_time)->where('end', '>=', $end_time);
                    })->orWhere(function ($q) use ($start_time, $end_time) {
                        $q->where('start', '>=', $start_time)->where('end', '<=', $end_time);
                    });
                })
                ->select('id', 'start', 'end')->first();
            }
            // return $conflictingShift;
            if ($conflictingShift && !isset($request->shift_confirm)) {
                if($checkAdmin == 'super-admin'){
                    return response()->json(['data'=>$conflictingShift,'success' => false, 'message' => '<b>Hi, Super Admin this shift has conflict <br> Do you really want to create this shift !</b>', 'code'=> 404]);
                }
                if($checkAdmin == 'admin'){
                    return response()->json(['success' => false, 'message' => '<b>Hi Admin, this shift has conflict so you cant create a shift!</b>', 'code'=> 404]);
                }
            }
            # CHECK GUARD WORK LIMITATION
            $guardWorkingHours = calCulateGuardWeekHours(dbFormateDateTime($request->start), dbFormateDateTime($request->end));
            $guardWorkLimitation = checkGuardWorkLimitation($request->guard_id, $guardWorkingHours);
            $w_l_h = '';
            $now = Carbon::now();
            $weekStartDate = $now->startOfWeek()->toDateString();
            $weekEndDate = $now->endOfWeek()->toDateString();
            $guardOnLimitations = Guard::where('id', $request->guard_id)->first();
    
            if ($guardOnLimitations->work_limitation_status == 1) {
                $w_l_h = $guardOnLimitations->weekly_work_hours_limitation ?? 40;
            }
    
            $sumOfOneWeekHour = RunSheetJobRoster::where('guard_id', $request->guard_id)
                ->where(function ($query) use ($weekStartDate, $weekEndDate) {
                    $query->whereBetween('start', [$weekStartDate, $weekEndDate])
                        ->orWhereBetween('end', [$weekStartDate, $weekEndDate]);
                })
                ->sum('total_week_hours');
    
            if (!empty($sumOfOneWeekHour) && !empty($w_l_h)) {
                $sum = $sumOfOneWeekHour + $guardWorkingHours;
                if ($sum > $w_l_h) {
                    $difference = $sum - $w_l_h - $guardWorkingHours;
                    $message = 'You cannot create a shift because you exceed your work limitations.';
                    return response()->json([
                        'success' => false,
                        'message' => $message,
                        'data' => $difference,
                    ]);
                }
            } elseif (!empty($guardOnLimitations->weekly_work_hours_limitation) && $guardOnLimitations->weekly_work_hours_limitation < $guardWorkingHours) {
                $sum = $sumOfOneWeekHour + $guardWorkingHours;
    
                if ($sum > $w_l_h) {
                    $difference = $sum - $w_l_h;
                    $message = 'You cannot create a shift because you exceed your work limitations.';
                    return response()->json([
                        'success' => false,
                        'message' => $message,
                        'data' => $difference,
                    ]);
                }
            }
        }
        
        // # CREATE SHIFT TEMPLATES
        if($request->has('shift_type') && $request->shift_type == 'template'){
            $addNewShift = new RunSheetJobRoster();
            $addNewShift->start = dbFormateDateTime($request->start);
            $addNewShift->end = dbFormateDateTime($request->end);
            $addNewShift->shift_create_status = 'pending';
            $addNewShift->shift_type = 'template';
            $addNewShift->save();
            if($request->has('run_sheet_job_roster_tasks') && !empty($request->run_sheet_job_roster_tasks)){
                foreach ($request->job_roster_tasks as $key => $task) {
                    $newTask =  new RunSheetJobRosterTask();
                    $newTask->run_sheet_job_roster_id = $addNewShift->id;
                    $newTask->task = $task['task'];
                    $newTask->task_start = dbFormateDateTime($task['task_start']);
                    $newTask->task_end = dbFormateDateTime($task['task_end']);
                    $newTask->save();
                    jobRosterActions($request->admin_id, 'add_shift_tasks', $newTask->id,'job_roster_tasks');
                }
            }
            return response()->json([
                'success' => true,
                'message' => 'Template Shift Created.'
            ]);
        }
    
        # CALCULATE CUSTOM PAYRATE AND CHARGE RATE IF SET IN SHIFT
        $cus_payrate = '';
        $cus_chargerate = '';
        if($request->has('custome_rate') && $request->custome_rate == true){
            if($request->has('custome_payrate') && $request->custome_payrate == true){
                $cus_payrate = json_encode($request->manualPayRate);
            }
        }
        if($request->has('custome_rate') && $request->custome_rate == true){
            if($request->has('custome_chagerate') && $request->custome_chagerate == true){
                $cus_chargerate = json_encode($request->manualChargeRate);
            }
        }
        # CALCULATE GUARD SHIFT AND WORKING HOURS
        $hours = $this->getShiftHours(dbFormateDateTime($request->start), dbFormateDateTime($request->end), $request->run_sheet_id);
        $guardWorkingHours = calCulateGuardWeekHours(dbFormateDateTime($request->start), dbFormateDateTime($request->end));
        if(isset($request->id)){
            $addNewShift = RunSheetJobRoster::findOrFail($request->id);
            $flagUpdateShift = 1;
        }else{
            $addNewShift = new RunSheetJobRoster();
            $flagUpdateShift = 0;
        }
        $addNewShift->run_sheet_id = $request->run_sheet_id;
        $addNewShift->guard_id = (isset($request->guard_id) && !empty($request->guard_id)) ? $request->guard_id : null;
        $addNewShift->start = dbFormateDateTime($request->start);
        $addNewShift->end = dbFormateDateTime($request->end);
        $addNewShift->shift_payable = !empty($request->shift_payable) && ($request->has('shift_payable')) ? $request->shift_payable : 'yes';
        $addNewShift->shift_chargeable = !empty($request->shift_chargeable) && ($request->has('shift_payable')) ? $request->shift_chargeable : 'yes';
        $addNewShift->custome_rate = ($request->custome_rate == 'on' ? true : false);
        $addNewShift->payrate_level = $request->payrate_level;
        $addNewShift->payrate = $request->payrate;
        $addNewShift->chargerate_level = $request->chargerate_level;
        $addNewShift->chargerate = $request->chargerate;
        $addNewShift->un_published_shift = ($request->un_published_shift == 'on' ? true : false);
        $addNewShift->public_holidays = ($request->public_holidays == 'on' ? true : false);
        $addNewShift->covid_marshal = ($request->covid_marshal == 'on' ? true : false);
        $addNewShift->training = ($request->training == 'on' ? true : false);
        $addNewShift->continuation = ($request->continuation == 'on' ? true : false);
        $addNewShift->over_time = ($request->over_time == 'on') ? true : false;
        $addNewShift->over_time_value = ($request->over_time_value) ? $request->over_time_value : 0;
        $addNewShift->travel_time = ($request->travel_time == 'on') ? true : false;
        $addNewShift->travel_time_value = ($request->travel_time_value) ? $request->travel_time_value : 0;
        $addNewShift->shift_create_status = 'pending';
        $addNewShift->total_week_hours = $guardWorkingHours;
        $addNewShift->shift_type = ($request->has('shift_type') && !empty($request->shift_type) ? $request->shift_type : '');
        $addNewShift->conflict = (!empty($conflictingShift) ? 'conflict in '.getRunSheetRosterdName($request->run_sheet_id) : null);
        $addNewShift->conflicted_with = (!empty($conflictingShift) ? $conflictingShift->id : null);
        $addNewShift->doc_conf = ((isset($checkGuardDocs) && $checkGuardDocs == false) ? $docExpire : null);
        $addNewShift->work_limitaion_conf = (!empty($guardWorkLimitation['difference']) ? $guardWorkLimitation['difference'] : '');
        $addNewShift->conf_start = (!empty($conflictingShift) ? dbFormateDateTime($conflictingShift->start) : '');
        $addNewShift->conf_end = (!empty($conflictingShift) ? dbFormateDateTime($conflictingShift->end) : '');
        $addNewShift->morning_hours = (!empty($hours['morning']) ? roundHours($hours['morning']) : 0.0);
        $addNewShift->night_hours = (!empty($hours['night']) ? roundHours($hours['night']) : 0.0);
        $addNewShift->saturday_morning_hours = (!empty($hours['saturday_morning']) ? roundHours($hours['saturday_morning']) : 0.0);
        $addNewShift->saturday_night_hours = (!empty($hours['saturday_night']) ? roundHours($hours['saturday_night']) : 0.0);
        $addNewShift->sunday_morning_hours = (!empty($hours['sunday_morning']) ? roundHours($hours['sunday_morning']) : 0.0);
        $addNewShift->sunday_night_hours = (!empty($hours['sunday_night']) ? roundHours($hours['sunday_night']) : 0.0);
        $addNewShift->ph_morning_hours = (!empty($hours['ph_morning']) ? roundHours($hours['ph_morning']) : 0.0);
        $addNewShift->ph_night_hours = (!empty($hours['ph_night']) ? roundHours($hours['ph_night']) : 0.0);
        $addNewShift->last_update = time();
        $addNewShift->hours = roundHours($guardWorkingHours);
        $addNewShift->publish_status = isset($request->publish_status) ? $request->publish_status : 0;
        $addNewShift->custome_rate = $request->custome_rate;
        $addNewShift->custome_payrate = $request->custome_payrate;
        $addNewShift->custome_chagerate = $request->custome_chagerate;
        $addNewShift->manualPayRate = $cus_payrate;
        $addNewShift->manualChargeRate = $cus_chargerate;
        $addNewShift->unprofile_name = $request->unprofile_name;
        $addNewShift->po_wo = $request->po_wo;
        $addNewShift->job_instrcutions = $request->job_instrcutions;
        $addNewShift->job_instruction_text = $request->job_instruction_text;
        $addNewShift->run_sheet_roster_id = $request->run_sheet_roster_id;
        $addNewShift->created_by = $request->admin_id;
        $addNewShift->on_call_job = isset($request->on_call_job) ? $request->on_call_job : 0;
        $addNewShift->save();
        if($request->publish_status == 1 && isset($request->guard_id) && $request->guard_id > 0 && $request->un_published_shift == 0){
            if($request->has('guard_id') && !empty($request->guard_id)){
                # SEND MAIL
                $guard = Guard::where('id', $request->guard_id)->first();
                $prams['message'] = 'Roster for the week '.usaToAusDateTime($request->start).' - '.usaToAusDateTime($request->end).' has been published. Please open app and confirm your roster.';
                $prams['subject'] = 'Roster Published';
                $prams['email'] = $guard->email;
                generalEmails($prams);
                # SEND NOTIFICATION IF TOKEN EXIST
                if(isset($guard->notification_token)){
                    $prams['message'] = 'Roster for the week '.usaToAusDateTime($request->start).' - '.usaToAusDateTime($request->end).' has been published.';
                    $prams['title'] = 'Roster Published';
                    $prams['page'] = 'roster';
                    $prams['notification_token'] = $guard->notification_token;
                    send_push_notification($prams);
                }
            }
            sendSmsToGuard($guard->phone, 'Hi '.$guard->first_name.' '.$guard->last_name.' '. 'your shift has been published successfully!');
        }
        jobRosterActions($request->admin_id, 'add_shift', $addNewShift->id, 'job_roster');
        $admin_name = getAdminName($request->admin_id);
        $currnet_time = time();
        shiftCompleteActivity($addNewShift->id, $admin_name. ' Added this Shift', 'add_shift', $addNewShift->id, $currnet_time, $request->admin_id);
        # SAVE TASK
    
        if($request->has('run_sheet_job_roster_tasks') && !empty($request->run_sheet_job_roster_tasks)){
            if($flagUpdateShift == 1){
                foreach ($request->run_sheet_job_roster_tasks as $key => $task) {
                    $updateTask =  RunSheetJobRosterTask::where('id', $task['id'])->first();
                    $old_task = $updateTask; 
                    $is_check = 0; 
                    if(!$updateTask){
                        $updateTask =  new RunSheetJobRosterTask();
                        $is_check = 1;     
                    }
                    $updateTask->run_sheet_job_roster_id = $request->id;
                    $updateTask->task = $task['task'];
                    $updateTask->task_start = dbFormateDateTime($task['task_start']);
                    $updateTask->task_end = dbFormateDateTime($task['task_end']);
                    $updateTask->save();
                    if($is_check == 1){
                        jobRosterActions($request->admin_id, 'add_shift_tasks', $updateTask->id, 'run_sheet_job_roster_tasks');
                    }else{
                        jobRosterActions($request->admin_id, 'update_shift_tasks', $updateTask->id,'run_sheet_job_roster_tasks', $old_task);
                    }
                }
            }else{
                if($request->shift_type == 'template_rost'){
                    $getTemplateShiftTask = RunSheetJobRoster::where(['start'=> $request->start, 'end'=>$request->end, 'roster_id'=>$request->roster_id, 'shift_type'=>'template'])->with('RunSheetJobRosterTask')->first();
                    if($getTemplateShiftTask->RunSheetJobRosterTask){
                        foreach($getTemplateShiftTask->RunSheetJobRosterTask as $task){
                            $newTask =  new RunSheetJobRosterTask();
                            $newTask->run_sheet_job_roster_id = $addNewShift->id;
                            $newTask->task = $task['task'];
                            $newTask->task_start = dbFormateDateTime($task['task_start']);
                            $newTask->task_end = dbFormateDateTime($task['task_end']);
                            $newTask->save();
                            jobRosterActions($request->admin_id, 'add_shift_tasks', $newTask->id,'run_sheet_job_roster_tasks');
                        }
                    }
                }else{
                    foreach ($request->run_sheet_job_roster_tasks as $key => $task) {
                        $newTask =  new RunSheetJobRosterTask();
                        $newTask->run_sheet_job_roster_id = $addNewShift->id;
                        $newTask->task = $task['task'];
                        $newTask->task_start = dbFormateDateTime($task['task_start']);
                        $newTask->task_end = dbFormateDateTime($task['task_end']);
                        $newTask->save();
                        jobRosterActions($request->admin_id, 'add_shift_tasks', $newTask->id,'run_sheet_job_roster_tasks');
                    }
                }
            }
        }
        if(($flagUpdateShift == 1) && (empty($conflictingShift) || $checkGuardDocs == false)){
            $addNewShift->conflict = null;
            $addNewShift->conf_start = null;
            $addNewShift->conf_end = null;
            $addNewShift->update();
        }
        if((isset($conflictingShift) && !empty($conflictingShift)) || (isset($checkGuardDocs) && $checkGuardDocs == false)){
            return response()->json(['success' => true, 'message' => '<b>Conflicted Shift Created!</b>', 'code'=> 200]);
        }
        return response()->json([
            'success' => true,
            'message' => 'Shift Created.'
        ]);
    }


    public function updateShift(Request $request){
        if(isset($request->guard_id) && $request->guard_id > 0){
            # CHECK DIFFERENCE BETWEEN SHIFTS
            $checkAdmin = checkAdmin($request->admin_id);
            if($checkAdmin != 'super-admin'){
                // $startDate = Carbon::createFromFormat('m-d-Y H:i', $request->start);
                // $endDate = Carbon::createFromFormat('m-d-Y H:i', $request->end);
                // $checkDiff = $startDate->diff($endDate);
                // if($checkDiff->h > 8){
                //     return response()->json(['success' => false, 'hide' => true, 'message' => 'You can not create shift more then 8 hours!']); 
                // }
                $diff =  checkShiftDayHours($request->start, $request->end, $request->guard_id);
                if($diff < 9){
                    return response()->json(['success' => false, 'message' => 'You must rest for eight hours before starting a new shift!']); 
                }
            }
            # CHECK GUARD DOCS ARE SET AND NOT EXPIRED
            $checkGuardDocs = true;
            $guardDetails = GuardWorkDetail::where('guard_id', $request->guard_id)->first();
            if (empty($guardDetails->guard_document_type)) {
                // return "Please First Add Your Residential Status!";
                $checkGuardDocs = false;
            }
            // $today = strtotime(date("Y/m/d"));
            $t = dbFormate($request->start);
            $today = strtotime($t);
            $guard = Guard::where('id', $request->guard_id)->with('guardDocuments')->first();
            foreach ($guard->guardDocuments as $document) {
                if ($document->c_f_roster == 1) {
                    if ($document->document_category == 'citizen') {
                        if ($document->document_type == 'security_license' &&
                            ($document->document_expire === '' || $document->document_expire === null || $today > strtotime($document->document_expire))) {
                            $docExpire = "Security License Expired!";
                            $checkGuardDocs = false;
                        } else {
                            // return 'active';
                            $checkGuardDocs = true;
                        }
                    } else {
                        if (in_array($document->document_type, ['visa', 'passport', 'security_license']) &&
                            ($document->document_expire === '' || $document->document_expire === null || $today > strtotime($document->document_expire))) {
                            $docExpire = ucfirst($document->document_type) . " Expired!";
                            $checkGuardDocs = false;
                        }
                    }
                }
            }
            if ($checkGuardDocs == false && !isset($request->shift_confirm)) {
                if($checkAdmin == 'super-admin'){
                    return response()->json(['data'=>$checkGuardDocs,'success' => false, 'message' => '<b>Hi1, Super Admin this shift has document expired or not updated <br> Do you really want to create this shift !</b>', 'code'=> 404]);
                }
                if($checkAdmin == 'admin'){
                    return response()->json(['success' => false, 'message' => '<bHi Admin, this guard has document expired or not updated so you cant create a shift!</b>', 'code'=> 404]);
                }
            }
            # CHECK SHIFT CONFILICT
            if(isset($request->id) && $request->id > 0){
                $addNewShift = RunSheetJobRoster::findOrFail($request->id);
                $start_time = dbFormateDateTime($request->start);
                $end_time = dbFormateDateTime($request->end);
                $conflictingShift = RunSheetJobRoster::where('guard_id', $request->guard_id)
                ->where(function ($query) use ($start_time, $end_time) {
                    $query->where(function ($q) use ($start_time) {
                        $q->where('start', '<=', $start_time)->where('end', '>', $start_time);
                    })->orWhere(function ($q) use ($end_time) {
                        $q->where('start', '<=', $end_time)->where('end', '>=', $end_time);
                    })->orWhere(function ($q) use ($start_time, $end_time) {
                        $q->where('start', '>=', $start_time)->where('end', '<=', $end_time);
                    });
                })
                ->where('id', '!=' ,$addNewShift->id)->select('id', 'start', 'end')->first();
            }else{
                $start_time = dbFormateDateTime($request->start);
                $end_time = dbFormateDateTime($request->end);
                $conflictingShift = RunSheetJobRoster::where('guard_id', $request->guard_id)
                ->where(function ($query) use ($start_time, $end_time) {
                    $query->where(function ($q) use ($start_time) {
                        $q->where('start', '<=', $start_time)->where('end', '>', $start_time);
                    })->orWhere(function ($q) use ($end_time) {
                        $q->where('start', '<=', $end_time)->where('end', '>=', $end_time);
                    })->orWhere(function ($q) use ($start_time, $end_time) {
                        $q->where('start', '>=', $start_time)->where('end', '<=', $end_time);
                    });
                })
                ->select('id', 'start', 'end')->first();
            }
            // return $conflictingShift;
            if ($conflictingShift && !isset($request->shift_confirm)) {
                if($checkAdmin == 'super-admin'){
                    return response()->json(['data'=>$conflictingShift,'success' => false, 'message' => '<b>Hi, Super Admin this shift has conflict <br> Do you really want to create this shift !</b>', 'code'=> 404]);
                }
                if($checkAdmin == 'admin'){
                    return response()->json(['success' => false, 'message' => '<b>Hi Admin, this shift has conflict so you cant create a shift!</b>', 'code'=> 404]);
                }
            }
            # CHECK GUARD WORK LIMITATION
            $guardWorkingHours = calCulateGuardWeekHours(dbFormateDateTime($request->start), dbFormateDateTime($request->end));
            $w_l_h = '';
            $now = Carbon::now();
            $weekStartDate = $now->startOfWeek()->toDateString();
            $weekEndDate = $now->endOfWeek()->toDateString();
            $guardOnLimitations = Guard::where('id', $request->guard_id)->first();
    
            if ($guardOnLimitations->work_limitation_status == 1) {
                $w_l_h = $guardOnLimitations->weekly_work_hours_limitation ?? 40;
            }
    
            $sumOfOneWeekHour = RunSheetJobRoster::where('guard_id', $request->guard_id)
                ->where(function ($query) use ($weekStartDate, $weekEndDate) {
                    $query->whereBetween('start', [$weekStartDate, $weekEndDate])
                        ->orWhereBetween('end', [$weekStartDate, $weekEndDate]);
                })
                ->sum('total_week_hours');
    
            if (!empty($sumOfOneWeekHour) && !empty($w_l_h)) {
                $sum = $sumOfOneWeekHour + $guardWorkingHours;
                if ($sum > $w_l_h) {
                    $difference = $sum - $w_l_h - $guardWorkingHours;
                    $message = 'You cannot create a shift because you exceed your work limitations.';
                    return response()->json([
                        'success' => false,
                        'message' => $message,
                        'data' => $difference,
                    ]);
                }
            } elseif (!empty($guardOnLimitations->weekly_work_hours_limitation) && $guardOnLimitations->weekly_work_hours_limitation < $guardWorkingHours) {
                $sum = $sumOfOneWeekHour + $guardWorkingHours;
    
                if ($sum > $w_l_h) {
                    $difference = $sum - $w_l_h;
                    $message = 'You cannot create a shift because you exceed your work limitations.';
                    return response()->json([
                        'success' => false,
                        'message' => $message,
                        'data' => $difference,
                    ]);
                }
            }
        }
    
        # CALCULATE CUSTOM PAYRATE AND CHARGE RATE IF SET IN SHIFT
        $cus_payrate = '';
        $cus_chargerate = '';
        if($request->has('custome_rate') && $request->custome_rate == true){
            if($request->has('custome_payrate') && $request->custome_payrate == true){
                $cus_payrate = json_encode($request->manualPayRate);
            }
        }
        if($request->has('custome_rate') && $request->custome_rate == true){
            if($request->has('custome_chagerate') && $request->custome_chagerate == true){
                $cus_chargerate = json_encode($request->manualChargeRate);
            }
        }
        $guardWorkingHours = calCulateGuardWeekHours(dbFormateDateTime($request->start), dbFormateDateTime($request->end));
        # CALCULATE GUARD SHIFT AND WORKING HOURS
        $hours = $this->getShiftHours(dbFormateDateTime($request->start), dbFormateDateTime($request->end), $request->run_sheet_id);
        if(isset($request->id)){
            $addNewShift = RunSheetJobRoster::findOrFail($request->id);
            
            if($addNewShift->job_status == 'confirmed' && (($addNewShift->guard_id != $request->guard_id) || ($request->guard_id == '') || ($request->guard_id == null))){
                $guard = Guard::where('id', $addNewShift->guard_id)->first();
                $addNewShift->job_status = 'pending' ;
                $addNewShift->guard_id = $request->guard_id;
                if(isset($guard->notification_token)){
                    $prams['message'] = $guard->first_name.' One of your shifts has been removed';
                    $prams['title'] = 'Remove Shift';
                    $prams['page'] = 'roster';
                    $prams['notification_token'] = $guard->notification_token;
                    send_push_notification($prams);
                }
                $addNewShift->update();
                return response()->json(['success' => true, 'message' => 'Shift unassigned', 'code'=> 200]);
            }
            # IF REQUEST DON'T HAVE GUARD_ID ON UPDATE THEN REMOVE CONFLICT FROM ALL SHIFT
            if($request->guard_id == null){
                $removeConflict = RunSheetJobRoster::where('conflicted_with', $request->id)->get();
                if($removeConflict){
                    foreach($removeConflict as $shift){
                        $updateShift = RunSheetJobRoster::find($shift['id']);
                        $updateShift->conflicted_with = null;
                        $updateShift->conflict = null;
                        $updateShift->conf_start = null;
                        $updateShift->conf_end = null;
                        $updateShift->update();
                    }
                }
            }
    
    
            $flagUpdateShift = 1;
        }else{
            $addNewShift = new RunSheetJobRoster();
            $flagUpdateShift = 0;
        }
        $addNewShift->run_sheet_id = $request->run_sheet_id;
        $addNewShift->guard_id = (isset($request->guard_id) && !empty($request->guard_id)) ? $request->guard_id : null;
        $addNewShift->start = dbFormateDateTime($request->start);
        $addNewShift->end = dbFormateDateTime($request->end);
        $addNewShift->shift_payable = !empty($request->shift_payable) && ($request->has('shift_payable')) ? $request->shift_payable : 'yes';
        $addNewShift->shift_chargeable = !empty($request->shift_chargeable) && ($request->has('shift_payable')) ? $request->shift_chargeable : 'yes';
        $addNewShift->custome_rate = ($request->custome_rate == 'on' ? true : false);
        $addNewShift->payrate_level = $request->payrate_level;
        $addNewShift->payrate = $request->payrate;
        $addNewShift->chargerate_level = $request->chargerate_level;
        $addNewShift->chargerate = $request->chargerate;
        $addNewShift->un_published_shift = ($request->un_published_shift == 'on' ? true : false);
        $addNewShift->public_holidays = ($request->public_holidays == 'on' ? true : false);
        $addNewShift->covid_marshal = ($request->covid_marshal == 'on' ? true : false);
        $addNewShift->training = ($request->training == 'on' ? true : false);
        $addNewShift->continuation = ($request->continuation == 'on' ? true : false);
        $addNewShift->over_time = ($request->over_time == 'on') ? true : false;
        $addNewShift->over_time_value = ($request->over_time_value) ? $request->over_time_value : 0;
        $addNewShift->travel_time = ($request->travel_time == 'on') ? true : false;
        $addNewShift->travel_time_value = ($request->travel_time_value) ? $request->travel_time_value : 0;
        $addNewShift->shift_create_status = 'pending';
        $addNewShift->total_week_hours = $guardWorkingHours;
        $addNewShift->shift_type = ($request->has('shift_type') && !empty($request->shift_type) ? $request->shift_type : '');
        $addNewShift->conflict = (!empty($conflictingShift) ? 'conflict in '.getRunSheetRosterdName($request->run_sheet_id) : null);
        $addNewShift->conflicted_with = ((isset($conflictingShift) && !empty($conflictingShift)) ? $conflictingShift->id : null);
        $addNewShift->doc_conf = ((isset($checkGuardDocs) && $checkGuardDocs == false) ? $docExpire : null);
        $addNewShift->work_limitaion_conf = ((isset($guardWorkLimitation) && !empty($guardWorkLimitation['difference'])) ? $guardWorkLimitation['difference'] : '');
        $addNewShift->conf_start = ((isset($conflictingShift) && !empty($conflictingShift)) ? dbFormateDateTime($conflictingShift->start) : '');
        $addNewShift->conf_end = ((isset($conflictingShift) && !empty($conflictingShift)) ? dbFormateDateTime($conflictingShift->end) : '');
        $addNewShift->morning_hours = (!empty($hours['morning']) ? roundHours($hours['morning']) : 0.0);
        $addNewShift->night_hours = (!empty($hours['night']) ? roundHours($hours['night']) : 0.0);
        $addNewShift->saturday_morning_hours = (!empty($hours['saturday_morning']) ? roundHours($hours['saturday_morning']) : 0.0);
        $addNewShift->saturday_night_hours = (!empty($hours['saturday_night']) ? roundHours($hours['saturday_night']) : 0.0);
        $addNewShift->sunday_morning_hours = (!empty($hours['sunday_morning']) ? roundHours($hours['sunday_morning']) : 0.0);
        $addNewShift->sunday_night_hours = (!empty($hours['sunday_night']) ? roundHours($hours['sunday_night']) : 0.0);
        $addNewShift->ph_morning_hours = (!empty($hours['ph_morning']) ? roundHours($hours['ph_morning']) : 0.0);
        $addNewShift->ph_night_hours = (!empty($hours['ph_night']) ? roundHours($hours['ph_night']) : 0.0);
        $addNewShift->last_update = time();
        $addNewShift->hours = roundHours($guardWorkingHours);
        $addNewShift->publish_status = $request->publish_status;
        $addNewShift->custome_rate = $request->custome_rate;
        $addNewShift->custome_payrate = $request->custome_payrate;
        $addNewShift->custome_chagerate = $request->custome_chagerate;
        $addNewShift->manualPayRate = $cus_payrate;
        $addNewShift->manualChargeRate = $cus_chargerate;
        $addNewShift->unprofile_name = $request->unprofile_name;
        $addNewShift->po_wo = $request->po_wo;
        $addNewShift->job_instrcutions = $request->job_instrcutions;
        $addNewShift->job_instruction_text = $request->job_instruction_text;
        $addNewShift->run_sheet_roster_id = $request->run_sheet_roster_id;
        $addNewShift->updated_by = $request->admin_id;
        $addNewShift->on_call_job = isset($request->on_call_job) ? $request->on_call_job : 0;
        $addNewShift->save();
        if($request->publish_status == 1 && isset($request->guard_id) && $request->guard_id > 0){
            if($request->has('guard_id') && !empty($request->guard_id)){
                # SEND MAIL
                $guard = Guard::where('id', $request->guard_id)->first();
                $prams['message'] = 'Roster for the week '.usaToAusDateTime($request->start).' - '.usaToAusDateTime($request->end).' has been published. Please open app and confirm your roster.';
                $prams['subject'] = 'Roster Published';
                $prams['email'] = $guard->email;
                generalEmails($prams);
                # SEND NOTIFICATION IF TOKEN EXIST
                if(isset($guard->notification_token)){
                    $prams['message'] = 'Roster for the week '.usaToAusDateTime($request->start).' - '.usaToAusDateTime($request->end).' has been published.';
                    $prams['title'] = 'Roster Published';
                    $prams['page'] = 'roster';
                    $prams['notification_token'] = $guard->notification_token;
                    send_push_notification($prams);
                }
            }
            sendSmsToGuard($guard->phone, 'Hi '.$guard->first_name.' '.$guard->last_name.' '. 'your shift has been published successfully!');
        }
        jobRosterActions($request->admin_id, 'update_shift', $addNewShift->id, 'job_roster');
        $admin_name = getAdminName($request->admin_id);
        $currnet_time = time();
        shiftCompleteActivity($addNewShift->id, $admin_name. ' Update this Shift', 'update_shift', $addNewShift->id, $currnet_time, $request->admin_id);
        # SAVE TASK
    
        if($request->has('run_sheet_job_roster_tasks') && !empty($request->run_sheet_job_roster_tasks)){
            if($flagUpdateShift == 1){
                foreach ($request->run_sheet_job_roster_tasks as $key => $task) {
                    $updateTask =  RunSheetJobRosterTask::where('id', $task['id'])->first();
                    $old_task = $updateTask; 
                    $is_check = 0; 
                    if(!$updateTask){
                        $updateTask =  new RunSheetJobRosterTask();
                        $is_check = 1;     
                    }
                    $updateTask->run_sheet_job_roster_id = $request->id;
                    $updateTask->task = $task['task'];
                    $updateTask->task_start = dbFormateDateTime($task['task_start']);
                    $updateTask->task_end = dbFormateDateTime($task['task_end']);
                    $updateTask->save();
                    if($is_check == 1){
                        jobRosterActions($request->admin_id, 'add_shift_tasks', $updateTask->id, 'job_roster_tasks');
                    }else{
                        jobRosterActions($request->admin_id, 'update_shift_tasks', $updateTask->id,'job_roster_tasks', $old_task);
                    }
                }
            }else{
                foreach ($request->run_sheet_job_roster_tasks as $key => $task) {
                    $newTask =  new RunSheetJobRosterTask();
                    $newTask->run_sheet_job_roster_id = $addNewShift->id;
                    $newTask->task = $task['task'];
                    $newTask->task_start = dbFormateDateTime($task['task_start']);
                    $newTask->task_end = dbFormateDateTime($task['task_end']);
                    $newTask->save();
                    jobRosterActions($request->admin_id, 'add_shift_tasks', $newTask->id,'job_roster_tasks');
                }
            }
        }
        if($flagUpdateShift == 1){
            $currentDate = now()->format('Y-m-d');
            $fetchConflictedShift = RunSheetJobRoster::where('conflicted_with', $addNewShift->id)
            ->where('run_sheet_roster_id', $request->run_sheet_roster_id)
            ->where('guard_id', $request->guard_id)
            ->select('id', 'start', 'end', 'conflict', 'conf_start', 'conf_end')
            ->get();
            if($fetchConflictedShift){
                foreach($fetchConflictedShift as $shift){
                    $conflict = (
                        $shift['start'] <= $addNewShift->end &&
                        $shift['end'] >= $addNewShift->start
                    );
                    if(!$conflict){
                        $updateConflict = RunSheetJobRoster::find($shift['id']);
                        $updateConflict->conflict = null;
                        $updateConflict->conf_start = null;
                        $updateConflict->conf_end = null;
                        $updateConflict->update();
                    }else{
                        $updateConflict = RunSheetJobRoster::find($shift['id']);
                        $updateConflict->conf_start = dbFormateDateTime($conflictingShift->start);
                        $updateConflict->conf_end = dbFormateDateTime($conflictingShift->end);
                        $updateConflict->update();
                    }
                }
            }
        }
        if(($flagUpdateShift == 1) && (empty($conflictingShift) || $checkGuardDocs == false)){
            $addNewShift->conflict = null;
            $addNewShift->conf_start = null;
            $addNewShift->conf_end = null;
            $addNewShift->update();
        }
        if((isset($conflictingShift) && !empty($conflictingShift)) || (isset($checkGuardDocs) && $checkGuardDocs == false)){
            return response()->json(['success' => true, 'message' => '<b>Conflicted Shift Created!</b>', 'code'=> 200]);
        }
        return response()->json([
            'success' => true,
            'message' => 'Shift Updated Successfully.'
        ]);
    }


    public function fetchRunSheets(Request $request)
    {
        
    $data_arry = [];
    $total_count = 0;

    if($request->has('customer_id') && !empty($request->customer_id)){
        
        if($request->has('start') && $request->start != '')
        {
            $start = dbFormate($request->start). ' 00:00';
            
        }else{
            $start = Carbon::now()->startOfWeek()->toDateString(); 
            $start = date('Y-m-d 00:00', strtotime($start));
        }
        if($request->has('end') && $request->end != '')
        {
            $end = dbFormate($request->end). ' 23:59';
        }else{
            $end = Carbon::now()->endOfWeek()->toDateString();
            $end = date('Y-m-d 23:59', strtotime($end));
        }

        $query ='';
        $run_sheet_roster_id = $request->run_sheet_roster_id;

        if($request->type == 'run_sheet'){
            
            $run_sheet_type = $request->run_sheet_type;
            
            $query = RunSheet::with(['RunSheetJobRoster' => function ($que) use ($start, $end, $run_sheet_type, $run_sheet_roster_id) {
            if ($run_sheet_type == 'active') {
                $que->where('start', '>=', $start)
                    ->where('start', '<=', $end)->where('deleted_at', null)
                    ->where('run_sheet_roster_id', $run_sheet_roster_id);
            } elseif ($run_sheet_type == 'inactive') {
                $que->where('start', '>=', $start)
                    ->where('start', '<=', $end)->where('deleted_at', null)
                    ->where('run_sheet_roster_id', $run_sheet_roster_id);
            }else{
                $que->where('start', '>=', $start)
                ->where('start', '<=', $end)->where('run_sheet_roster_id', $run_sheet_roster_id)->orderBy('run_sheet_job_rosters.start', 'asc')->where('deleted_at', null);
            }
            $que->where('run_sheet_roster_id', $run_sheet_roster_id)->where('run_sheet_job_rosters.deleted_at', null);
            $que->with('RunSheetJobRosterTask');
            }])->with('customer');
            $customer =  [$request->customer_id];
            $query->where(function ($query) use ($customer) {
                foreach ($customer as $value) {
                    $query->orWhereJsonContains('customer_id', $value);
                }
            });
            if ($request->has('run_sheet_id')) {
                $query->whereIn('run_sheets.id', $request->run_sheet_id);
            }
            if($run_sheet_type == 'active')
            {
                $query->join('run_sheet_job_rosters', 'run_sheet_job_rosters.run_sheet_id', '=', 'run_sheets.id');
                $query->where('run_sheet_job_rosters.start', '>=', $start)
                    ->where('run_sheet_job_rosters.start', '<=', $end)->where('run_sheet_roster_id', $run_sheet_roster_id)->orderBy('run_sheet_job_rosters.start', 'asc');

            }elseif($run_sheet_type == 'all'){
                $query->join('run_sheet_job_rosters', 'run_sheet_job_rosters.run_sheet_id', '=', 'run_sheets.id', 'left')->orderBy('run_sheet_job_rosters.start', 'asc');
            }else{
                $query->join('run_sheet_job_rosters', 'run_sheet_job_rosters.run_sheet_id', '=', 'run_sheets.id', 'left')->orderBy('run_sheet_job_rosters.start', 'asc');
            }
            // $sites = $query->get();
            $query->select('run_sheets.id', 'run_sheets.title', 'run_sheets.description', 'run_sheets.customer_id', DB::raw("COUNT(run_sheet_job_rosters.id) count"))
            ->where('deleted_at', null)
            ->groupBy('run_sheets.id')
            ->groupBy('run_sheets.description')
            ->groupBy('run_sheets.title')
            ->groupBy('run_sheets.customer_id')
            ->orderBy('run_sheets.title');
            $sites = $query->get();
                
            if(empty($sites)){
                return response()->json(['success' => false, 'data' => null, 'code' => 404]); 
            }
            // $sts = RunSheetJobRosterShiftsResource::collection($sites);
            if($run_sheet_type == 'inactive')
            {
                $inactive_sites = [];
                foreach ($sites as $key => $s) {
                    if (count($s->RunSheetJobRoster) == 0) {
                        $inactive_sites[] = $s;
                    }
                }
                $sts = RunSheetJobRosterShiftsResource::collection($inactive_sites);
            }else{
                $sts = RunSheetJobRosterShiftsResource::collection($sites);
            }
            
            $queryCount = RunSheetJobRoster::where('start', '>=', $start)->where('start', '<=', $end)
            ->where('guard_id', '!=',  '')->where('guard_id', '!=',  'NULL')->where('guard_id', '!=', NULL)
            ->where('publish_status', 0)->where(function($q){
                $q->orWhere('shift_type','!=','template');
            })->where('run_sheet_roster_id', $run_sheet_roster_id)->count();

            

            $dateRange = getDatesFromRange(dbFormate($request->start),dbFormate($request->end));
            if($dateRange){
                // ::where('run_sheet_id', $request->run_sheet_id)
                    foreach ($dateRange as $key => $value) {
                        $record = RunSheetJobRoster::whereDate('run_sheet_job_rosters.start', $value)
                            ->where(function ($q) {
                                $q->orWhere('run_sheet_job_rosters.shift_type', '!=', 'template');
                                $q->orWhereNull('run_sheet_job_rosters.shift_type');
                            })
                            ->where('run_sheet_job_rosters.run_sheet_roster_id', $request->run_sheet_roster_id)
                            ->sum('run_sheet_job_rosters.hours');
                    
                        $data_arry[dateFormat($value)] = $record;
                        $total_count = $total_count + $record;
                    }
                
            }
                
                $total_count = round($total_count, 2);


            //return([$sts, $queryCount, $data_arry, $total_count]);
            return response()->json(['success' => true, 'data' => $sts, 'unpublish_shift_count' => $queryCount,
                'days_hours' => $data_arry, 'total_hours' => $total_count,
                'code' => 200]);
        }
        // Guard type
        else{
            $guard_type = $request->guard_type;
            $run_sheet_roster_id = $request->run_sheet_roster_id;
            $query = Guard::with(['RunSheetJobRoster' => function ($que) use ($start, $end, $guard_type, $run_sheet_roster_id) {
                if ($guard_type == 'active') {
                    $que->where('start', '>=', $start)
                        ->where('start', '<=', $end)->where('deleted_at', null)
                        ->where('run_sheet_roster_id', $run_sheet_roster_id);
                } elseif ($guard_type == 'inactive') {
                    $que->where('start', '<', $start)
                        ->orWhere('start', '>', $end)->where('deleted_at', null)
                        ->where('run_sheet_roster_id', $run_sheet_roster_id);
                }else{
                    $que->where('start', '>=', $start)
                    ->where('start', '<=', $end)->where('run_sheet_roster_id', $run_sheet_roster_id);
                }
                $que->where('run_sheet_roster_id', $run_sheet_roster_id)->where('deleted_at', null);
                $que->with('RunSheetJobRosterTask');
            }]);
            if ($request->has('state')) {
                $query->where('guards.state', $request->state);
            }
            if ($request->has('guard_id')) {
                $query->where('guards.id', $request->guard_id);
            }
            if($guard_type == 'active')
            {
                $query->join('run_sheet_job_rosters', 'run_sheet_job_rosters.guard_id', '=', 'guards.id');
                $query->where('run_sheet_job_rosters.start', '>=', $start)
                    ->where('run_sheet_job_rosters.start', '<=', $end)->where('run_sheet_roster_id', $run_sheet_roster_id)->orderBy('run_sheet_job_rosters.start', 'asc');
            }elseif($guard_type == 'all'){
                $query->join('run_sheet_job_rosters', 'run_sheet_job_rosters.guard_id', '=', 'guards.id', 'left')->orderBy('run_sheet_job_rosters.start', 'asc');
            }else{
                $query->join('run_sheet_job_rosters', 'run_sheet_job_rosters.guard_id', '=', 'guards.id', 'left')->orderBy('run_sheet_job_rosters.start', 'asc');
            }
            $query->select('guards.id', 'guards.phone', 'guards.first_name', 'guards.middle_name','guards.last_name', 'guards.email', 'guards.profile_image')->orderBy('run_sheet_job_rosters.start', 'asc')
            ->where('deleted_at', null)
            ->groupBy('guards.id')
            ->groupBy('guards.first_name')
            ->groupBy('guards.phone')
            ->groupBy('guards.middle_name')
            ->groupBy('guards.last_name')
            ->groupBy('guards.email')
            ->groupBy('guards.profile_image');
            
            $sites = $query->get();
            if(empty($sites)){
                return response()->json(['success' => false, 'data' => null, 'code' => 404]); 
            }
            
            $sts = RunSheetJobRosterShiftsByGuardResource::collection($sites);
            
            $dateRange = getDatesFromRange(dbFormate($request->start),dbFormate($request->end));
            if($dateRange){
                foreach ($dateRange as $key => $value) {
                    $record = RunSheetJobRoster::whereDate('start', $value)
                    ->where(function($q){
                        $q->orWhere('shift_type','!=','template');
                        $q->orWhereNull('shift_type');
                        $q->whereNotNull('guard_id');
                    })
                    ->where('run_sheet_job_rosters.run_sheet_roster_id', $request->run_sheet_roster_id)
                            ->sum('run_sheet_job_rosters.hours');

                            $data_arry[dateFormat($value)] = $record;
                            $total_count = $total_count + $record;
                }
            }
            $total_count = round($total_count, 2);

            $queryCount = RunSheetJobRoster::where('start', '>=', $start)->where('start', '<=', $end)
            ->where('guard_id', '!=',  '')->where('guard_id', '!=',  'NULL')->where('guard_id', '!=', NULL)
            ->where('publish_status', 0)->where(function($q){
                $q->orWhere('shift_type','!=','template');
                //$q->orWhere('shift_type', '!=', NULL);
            })->where('run_sheet_roster_id', $run_sheet_roster_id)->count();

            //$total_count = number_format( $total_count, 2, '.', '' );
            return response()->json(['success' => true, 'data' => $sts, 'days_hours' => $data_arry, 'total_hours' => $total_count, 'unpublish_shift_count' => $queryCount, 'code' => 200]); 
        }
    }
        return response()->json(['success' => false, 'data' => null, 'code' => 404]); 
    }


    public function editRunsheetJobRoster(Request $request)
    {
        $runSheetJobRoster = RunSheetJobRoster::where('id', $request->id)->with(['RunSheetJobRosterTask', 'guardz'])->first();
        if($runSheetJobRoster){
            $run_jobrt = (new EditRunSheetJobRosterResource($runSheetJobRoster));
            return response()->json(['success' => true,'code' => 200 , 'data' => $run_jobrt]);
        }else{
            return response()->json(['success' => false,'code' => 404 , 'msg' => 'Shift Not Found!']);
        }
    }

    public function deleteShift(Request $request)
    {
    $shift  = RunSheetJobRoster::where('id', $request->id)->first();

    //$old_data = $shift;

    $date = dateFormat($shift->start); 
    if(!empty($shift)){

        $guard = Guard::where('id', $shift->guard_id)->select('id', 'notification_token')->first();
        if(!empty($guard->phone)){
        sendSmsToGuard($guard->phone, 'Hi '.$guard->first_name.' '.$guard->last_name.' '. 'your shift '.$date.' has been Deleted!');
        }
        if($shift->guard_id > 0 && $shift->publish_status == 1 && $guard->notification_token)
        {
            $notificaion['notification_token'] = $guard['notification_token'];
            $notificaion['message'] = "One of your shift".' '.$date.' '. "has been deleted. Please check your app.";
            $notificaion['title'] = 'Shift Deleted';
            $notificaion['page'] = 'homepage';
            send_push_notification($notificaion);
            // sendSmsToGuard($guard->phone, 'Hi '.$guard->first_name.' '.$guard->last_name.' '. 'One of your shift has been deleted!'. ' '.$date);
        }
        removeConflictOnDeleteShift($shift->start, $shift->end, $shift->run_sheet_roster_id, $shift->guard_id);
        # REMOVE CONFILICT FROM THE SHIFT
        $getShiftWithConflicts = RunSheetJobRoster::where('conflicted_with', $shift->id)->get();
        if($getShiftWithConflicts){
            foreach($getShiftWithConflicts as $getShiftWithConflict){
                $getShift = RunSheetJobRoster::find($getShiftWithConflict['id']);
                $getShift->conflicted_with = null;
                $getShift->conflict = null;
                $getShift->conf_start = null;
                $getShift->conf_end = null;
                $getShift->update();
            }
        }
        DB::table('run_sheet_job_roster_activites')->where(['guard_id'=> $shift->guard_id, 'job_roster_id'=>$request->id])->delete();
        DB::table('run_sheet_incident_reports')->where(['roster_id'=> $shift->id])->delete();
        DB::table('run_sheet_job_roster_tasks')->where(['run_sheet_job_roster_id'=> $shift->id])->delete();
        DB::table('run_sheet_job_breaks')->where(['roster_id'=> $shift->id])->delete();
        jobRosterActions($request->admin_id, 'delete_shift', $shift->id, 'job_roster', $shift);
        $shift->reason = $request->reason;
        $shift->deleted_by = $request->admin_id;
        $shift->update();
        RunSheetJobRoster::find($request->id)->delete();
        $admin_name = getAdminName($request->admin_id);
        $currnet_time = time();
        shiftCompleteActivity($shift->id, $admin_name. ' Delete this Shift', 'delete_shift', $shift->id, $currnet_time, $request->admin_id);
        return response()->json(['message' => "Shift Deleted" ,  'code' => 200, 'success' => true]);
    }else{
    return response()->json(['message' => "Shift Not Found" ,  'code' => 404, 'success' => false],404);
    }

    }


    public function shiftDropAndCopy(Request $request){
        $shiftDropAndCopy = RunSheetJobRoster::where('id', $request->roster_id)->with('RunSheetJobRosterTask')->first();
        if($shiftDropAndCopy){
            # CHECK SHIFT IS ASSIGNED
            if($shiftDropAndCopy->guard_id){
                # CHECK STAFF ON LEAVE OR NOT
                $guard_leave = checkGuardOnLeave($request->start, $request->end, $request->guard_id);
                if($guard_leave == 'leave'){
                    return response()->json(['success' => false, 'message' => 'Sorry Staff On Leave!', 'code'=> 404]);
                }
                # COMPLETED SHIFT NOT DROPABLE
                if($request->has('type') && $request->type == 'drop' && $shiftDropAndCopy->job_status == 'completed'){
                    return response()->json(['success' => true, 'message' => 'Completed shift does not drop to the next or previous date!', 'code'=> 404]);
                }
                # COMMON CHECKS
                # SIGNIN SHIFT NOT DROPABLE || ONGOING SHIFT
                if($shiftDropAndCopy->signin_status == 1){
                    return response()->json(['success' => false, 'message' => 'Sign-in shift does not drop to the next or previous date!', 'code'=> 404]);
                }
                # CONFIRMED SHIFT SEND NOTIFICATION TO GUARD PREVIOUS SHIFT DELETED AND PROCEED TO NEXT STEP
                if($shiftDropAndCopy->job_status == 'confirmed'){
                    $guard = Guard::where('id', $shiftDropAndCopy->guard_id)->select('id', 'notification_token')->first();
                    if($guard['notification_token']){
                        $notificaion['notification_token'] = $guard['notification_token'];
                        $notificaion['message'] = "One of your shift".' '.dateFormat($shiftDropAndCopy->start) .' '. "has been deleted. Please check your app.";
                        $notificaion['title'] = 'Shift Deleted';
                        $notificaion['page'] = 'homepage';
                        send_push_notification($notificaion);
                    }
                }
                # UPDATE THE NECESSARY DATA ON DROP
                if(isset($shiftDropAndCopy->guard_id) && $shiftDropAndCopy->guard_id > 0){
                    # CHECK DIFFERENCE BETWEEN SHIFTS
                    $checkAdmin = checkAdmin($request->admin_id);
                    if($checkAdmin != 'super-admin'){
                        $diff =  checkShiftDayHours($request->start, $request->end, $shiftDropAndCopy->guard_id);
                        if($diff < 9){
                            return response()->json(['success' => false, 'message' => 'You must rest for eight hours before starting a new shift!']); 
                        }
                    }
                    # CHECK GUARD DOCS ARE SET AND NOT EXPIRED
                    $checkGuardDocs = true;
                    $guardDetails = GuardWorkDetail::where('guard_id', $shiftDropAndCopy->guard_id)->first();
                    if (empty($guardDetails->guard_document_type)) {
                        // return "Please First Add Your Residential Status!";
                        $checkGuardDocs = false;
                    }
                    // $today = strtotime(date("Y/m/d"));
                    if(!$request->has('type') || $request->type == 'copy_shift'){ 
                        $t = dbFormate($request->newStart);
                    }else{
                        $t = dbFormate($request->start);
                    }
                    $today = strtotime($t);
                    $guard = Guard::where('id', $shiftDropAndCopy->guard_id)->with('guardDocuments')->first();
                    foreach ($guard->guardDocuments as $document) {
                        if ($document->c_f_roster == 1) {
                            if ($document->document_category == 'citizen') {
                                if ($document->document_type == 'security_license' &&
                                    ($document->document_expire === '' || $document->document_expire === null || $today > strtotime($document->document_expire))) {
                                    $docExpire = "Security License Expired!";
                                    $checkGuardDocs = false;
                                } else {
                                    //return 'active';
                                    $checkGuardDocs = true;
                                }
                            } else {
                                if (in_array($document->document_type, ['visa', 'passport', 'security_license']) &&
                                    ($document->document_expire === '' || $document->document_expire === null || $today > strtotime($document->document_expire))) {
                                    $docExpire = ucfirst($document->document_type) . " Expired!";
                                    $checkGuardDocs = false;
                                }
                            }
                        }
                    }
                    if ($checkGuardDocs == false && !isset($request->shift_confirm)) {
                        if($checkAdmin == 'super-admin'){
                            return response()->json(['data'=>$checkGuardDocs,'success' => false, 'message' => '<b>Hi, Super Admin this shift has document expired or not updated <br> Do you really want to create this shift !</b>', 'code'=> 404]);
                        }
                        if($checkAdmin == 'admin'){
                            return response()->json(['success' => false, 'message' => '<bHi Admin, this guard has document expired or not updated so you cant create a shift!</b>', 'code'=> 404]);
                        }
                    }
                    # CHECK SHIFT CONFILICT
                    $start_time = dbFormateDateTime($request->start);
                    $end_time = dbFormateDateTime($request->end);
                    if($request->has('type') && $request->type == 'copy_shift'){
                        $start_time = dbFormateDateTime($request->newStart);
                        $end_time = dbFormateDateTime($request->newEnd);
                    }
                    if($request->has('type') && $request->type == 'drop'){
                        $conflictingShift = RunSheetJobRoster::where('guard_id', $request->guard_id)
                        ->where(function ($query) use ($start_time, $end_time) {
                            $query->where(function ($q) use ($start_time) {
                                $q->where('start', '<=', $start_time)->where('end', '>', $start_time);
                            })->orWhere(function ($q) use ($end_time) {
                                $q->where('start', '<=', $end_time)->where('end', '>=', $end_time);
                            })->orWhere(function ($q) use ($start_time, $end_time) {
                                $q->where('start', '>=', $start_time)->where('end', '<=', $end_time);
                            });
                        })
                        ->where('id', '!=', $request->roster_id)
                        ->select('id', 'start', 'end')->first();
                    }else{
                        $conflictingShift = RunSheetJobRoster::where('guard_id', $request->guard_id)
                        ->where(function ($query) use ($start_time, $end_time) {
                            $query->where(function ($q) use ($start_time) {
                                $q->where('start', '<=', $start_time)->where('end', '>', $start_time);
                            })->orWhere(function ($q) use ($end_time) {
                                $q->where('start', '<=', $end_time)->where('end', '>=', $end_time);
                            })->orWhere(function ($q) use ($start_time, $end_time) {
                                $q->where('start', '>=', $start_time)->where('end', '<=', $end_time);
                            });
                        })
                        ->select('id', 'start', 'end')->first();
                    }
                    // return $conflictingShift;
                    if ($conflictingShift && !isset($request->shift_confirm)) {
                        if($checkAdmin == 'super-admin'){
                            return response()->json(['data'=>$conflictingShift,'success' => false, 'message' => '<b>Hi, Super Admin this shift has conflict <br> Do you really want to create this shift !</b>', 'code'=> 404]);
                        }
                        if($checkAdmin == 'admin'){
                            return response()->json(['success' => false, 'message' => '<b>Hi Admin, this shift has conflict so you cant create a shift!</b>', 'code'=> 404]);
                        }
                    }
                    # CHECK GUARD WORK LIMITATION
                    $guardWorkingHours = calCulateGuardWeekHours(dbFormateDateTime($request->start), dbFormateDateTime($request->end));
                    $guardWorkLimitation = checkGuardWorkLimitation($shiftDropAndCopy->guard_id, $guardWorkingHours);
                    $w_l_h = '';
                    $now = Carbon::now();
                    $weekStartDate = $now->startOfWeek()->toDateString();
                    $weekEndDate = $now->endOfWeek()->toDateString();
                    $guardOnLimitations = Guard::where('id', $shiftDropAndCopy->guard_id)->first();
            
                    if ($guardOnLimitations->work_limitation_status == 1) {
                        $w_l_h = $guardOnLimitations->weekly_work_hours_limitation ?? 40;
                    }
            
                    $sumOfOneWeekHour = RunSheetJobRoster::where('guard_id', $shiftDropAndCopy->guard_id)
                        ->where(function ($query) use ($weekStartDate, $weekEndDate) {
                            $query->whereBetween('start', [$weekStartDate, $weekEndDate])
                                ->orWhereBetween('end', [$weekStartDate, $weekEndDate]);
                        })
                        ->sum('total_week_hours');
            
                    if (!empty($sumOfOneWeekHour) && !empty($w_l_h)) {
                        $sum = $sumOfOneWeekHour + $guardWorkingHours;
                        if ($sum > $w_l_h) {
                            $difference = $sum - $w_l_h - $guardWorkingHours;
                            $message = 'You cannot create a shift because you exceed your work limitations.';
                            return response()->json([
                                'success' => false,
                                'message' => $message,
                                'data' => $difference,
                            ]);
                        }
                    } elseif (!empty($guardOnLimitations->weekly_work_hours_limitation) && $guardOnLimitations->weekly_work_hours_limitation < $guardWorkingHours) {
                        $sum = $sumOfOneWeekHour + $guardWorkingHours;
            
                        if ($sum > $w_l_h) {
                            $difference = $sum - $w_l_h;
                            $message = 'You cannot create a shift because you exceed your work limitations.';
                            return response()->json([
                                'success' => false,
                                'message' => $message,
                                'data' => $difference,
                            ]);
                        }
                    }
                }
                # RUN THIS WHEN TYPE IS DROP
                if($request->has('type') && $request->type == 'drop'){
                    # REMOVE CONFLICT FROM OTHERS BEFORE DROP THAT SHIFT
                    $checkConflicts = RunSheetJobRoster::where('conflicted_with', $request->roster_id)->get();
                    if($checkConflicts){
                        foreach($checkConflicts as $confShift){
                            $removeShiftConf = RunSheetJobRoster::find($confShift['id']);
                            $removeShiftConf->conflict = null;
                            $removeShiftConf->conflicted_with = null;
                            $removeShiftConf->conf_start = null;
                            $removeShiftConf->conf_end = null;
                            $removeShiftConf->update();
                        }
                    } 
                    # CALCULATE GUARD SHIFT AND WORKING HOURS
                    $hours = $this->getShiftHours(dbFormateDateTime($request->start), dbFormateDateTime($request->end), $request->run_sheet_id);
                    $guardWorkingHours = calCulateGuardWeekHours(dbFormateDateTime($request->start), dbFormateDateTime($request->end));

                    $shiftDropAndCopy->start = dbFormateDateTime($request->start);
                    $shiftDropAndCopy->end = dbFormateDateTime($request->end);
                    $shiftDropAndCopy->conflict = (!empty($conflictingShift) ? 'conflict' : null);
                    $shiftDropAndCopy->conflicted_with = (!empty($conflictingShift) ? $conflictingShift->id : null);
                    $shiftDropAndCopy->doc_conf = ((isset($checkGuardDocs) && $checkGuardDocs == false) ? 'conflict' : null);
                    $shiftDropAndCopy->work_limitaion_conf = (!empty($guardWorkLimitation['difference']) ? $guardWorkLimitation['difference'] : '');
                    $shiftDropAndCopy->conf_start = (!empty($conflictingShift) ? dbFormateDateTime($conflictingShift->start) : '');
                    $shiftDropAndCopy->conf_end = (!empty($conflictingShift) ? dbFormateDateTime($conflictingShift->end) : '');
                    $shiftDropAndCopy->morning_hours = (!empty($hours['morning']) ? roundHours($hours['morning']) : 0.0);
                    $shiftDropAndCopy->night_hours = (!empty($hours['night']) ? roundHours($hours['night']) : 0.0);
                    $shiftDropAndCopy->saturday_morning_hours = (!empty($hours['saturday_morning']) ? roundHours($hours['saturday_morning']) : 0.0);
                    $shiftDropAndCopy->saturday_night_hours = (!empty($hours['saturday_night']) ? roundHours($hours['saturday_night']) : 0.0);
                    $shiftDropAndCopy->sunday_morning_hours = (!empty($hours['sunday_morning']) ? roundHours($hours['sunday_morning']) : 0.0);
                    $shiftDropAndCopy->sunday_night_hours = (!empty($hours['sunday_night']) ? roundHours($hours['sunday_night']) : 0.0);
                    $shiftDropAndCopy->ph_morning_hours = (!empty($hours['ph_morning']) ? roundHours($hours['ph_morning']) : 0.0);
                    $shiftDropAndCopy->ph_night_hours = (!empty($hours['ph_night']) ? roundHours($hours['ph_night']) : 0.0);
                    $shiftDropAndCopy->last_update = time();
                    $shiftDropAndCopy->hours = roundHours($guardWorkingHours);
                    $shiftDropAndCopy->publish_status = 0;
                    $shiftDropAndCopy->signin_status = 0;
                    $shiftDropAndCopy->on_call_job = 0;
                    $shiftDropAndCopy->created_by = $request->admin_id;
                    $shiftDropAndCopy->unprofile_name = $shiftDropAndCopy->unprofile_name;
                    $shiftDropAndCopy->save();
                    
                    return response()->json([
                        'success' => true,
                        'message' => 'Shift Droped.'
                    ]);
                }
                if(!$request->has('type') || $request->type == 'copy_shift'){    
                    // return 'i copy';
                    # CALCULATE GUARD SHIFT AND WORKING HOURS
                    if($request->type == 'copy_shift'){
                        $hours = $this->getShiftHours(dbFormateDateTime($request->newStart), dbFormateDateTime($request->newEnd), $request->run_sheet_ids);
                        $guardWorkingHours = calCulateGuardWeekHours(dbFormateDateTime($request->newStart), dbFormateDateTime($request->newEnd));
                        $addNewShift = new RunSheetJobRoster();
                        $addNewShift->run_sheet_id = $request->run_sheet_ids;
                        $addNewShift->guard_id = (isset($shiftDropAndCopy->guard_id) && !empty($shiftDropAndCopy->guard_id)) ? $shiftDropAndCopy->guard_id : null;
                        $addNewShift->start = dbFormateDateTime($request->newStart);
                        $addNewShift->end = dbFormateDateTime($request->newEnd);
                    }else{
                        $hours = $this->getShiftHours(dbFormateDateTime($request->start), dbFormateDateTime($request->end), $request->run_sheet_id);
                        $guardWorkingHours = calCulateGuardWeekHours(dbFormateDateTime($request->start), dbFormateDateTime($request->end));
                        $addNewShift = new RunSheetJobRoster();
                        $addNewShift->run_sheet_id = $request->run_sheet_id;
                        $addNewShift->guard_id = (isset($shiftDropAndCopy->guard_id) && !empty($shiftDropAndCopy->guard_id)) ? $shiftDropAndCopy->guard_id : null;
                        $addNewShift->start = dbFormateDateTime($request->start);
                        $addNewShift->end = dbFormateDateTime($request->end);
                    }
                    $addNewShift->shift_payable = $shiftDropAndCopy->shift_payable;
                    $addNewShift->shift_chargeable = $shiftDropAndCopy->shift_chargeable;
                    $addNewShift->custome_rate = ($shiftDropAndCopy->custome_rate == 'on' ? true : false);
                    $addNewShift->payrate_level = $shiftDropAndCopy->payrate_level;
                    $addNewShift->payrate = $shiftDropAndCopy->payrate;
                    $addNewShift->chargerate_level = $shiftDropAndCopy->chargerate_level;
                    $addNewShift->chargerate = $shiftDropAndCopy->chargerate;
                    $addNewShift->un_published_shift = ($shiftDropAndCopy->un_published_shift == 'on' ? true : false);
                    $addNewShift->public_holidays = ($shiftDropAndCopy->public_holidays == 'on' ? true : false);
                    $addNewShift->covid_marshal = ($shiftDropAndCopy->covid_marshal == 'on' ? true : false);
                    $addNewShift->training = ($shiftDropAndCopy->training == 'on' ? true : false);
                    $addNewShift->continuation = ($shiftDropAndCopy->continuation == 'on' ? true : false);
                    $addNewShift->over_time = ($shiftDropAndCopy->over_time == 'on') ? true : false;
                    $addNewShift->over_time_value = ($shiftDropAndCopy->over_time_value) ? $shiftDropAndCopy->over_time_value : 0;
                    $addNewShift->travel_time = ($shiftDropAndCopy->travel_time == 'on') ? true : false;
                    $addNewShift->travel_time_value = ($shiftDropAndCopy->travel_time_value) ? $shiftDropAndCopy->travel_time_value : 0;
                    $addNewShift->shift_create_status = 'pending';
                    $addNewShift->total_week_hours = $guardWorkingHours;
                    $addNewShift->shift_type = $shiftDropAndCopy->shift_type;
                    $addNewShift->conflict = (!empty($conflictingShift) ? 'conflict' : null);
                    $addNewShift->conflicted_with = (!empty($conflictingShift) ? $conflictingShift->id : null);
                    $addNewShift->doc_conf = ((isset($checkGuardDocs) && $checkGuardDocs == false) ? $docExpire : null);
                    $addNewShift->work_limitaion_conf = (!empty($guardWorkLimitation['difference']) ? $guardWorkLimitation['difference'] : '');
                    $addNewShift->conf_start = (!empty($conflictingShift) ? dbFormateDateTime($conflictingShift->start) : '');
                    $addNewShift->conf_end = (!empty($conflictingShift) ? dbFormateDateTime($conflictingShift->end) : '');
                    $addNewShift->morning_hours = (!empty($hours['morning']) ? roundHours($hours['morning']) : 0.0);
                    $addNewShift->night_hours = (!empty($hours['night']) ? roundHours($hours['night']) : 0.0);
                    $addNewShift->saturday_morning_hours = (!empty($hours['saturday_morning']) ? roundHours($hours['saturday_morning']) : 0.0);
                    $addNewShift->saturday_night_hours = (!empty($hours['saturday_night']) ? roundHours($hours['saturday_night']) : 0.0);
                    $addNewShift->sunday_morning_hours = (!empty($hours['sunday_morning']) ? roundHours($hours['sunday_morning']) : 0.0);
                    $addNewShift->sunday_night_hours = (!empty($hours['sunday_night']) ? roundHours($hours['sunday_night']) : 0.0);
                    $addNewShift->ph_morning_hours = (!empty($hours['ph_morning']) ? roundHours($hours['ph_morning']) : 0.0);
                    $addNewShift->ph_night_hours = (!empty($hours['ph_night']) ? roundHours($hours['ph_night']) : 0.0);
                    $addNewShift->last_update = time();
                    $addNewShift->hours = roundHours($guardWorkingHours);
                    $addNewShift->publish_status = 0;
                    $addNewShift->custome_rate = $shiftDropAndCopy->custome_rate;
                    $addNewShift->custome_payrate = $shiftDropAndCopy->custome_payrate;
                    $addNewShift->custome_chagerate = $shiftDropAndCopy->custome_chagerate;
                    $addNewShift->manualPayRate = $shiftDropAndCopy->manualPayRate;
                    $addNewShift->manualChargeRate = $shiftDropAndCopy->manualChargeRate;
                    $addNewShift->unprofile_name = $shiftDropAndCopy->unprofile_name;
                    $addNewShift->po_wo = $shiftDropAndCopy->po_wo;
                    $addNewShift->job_instrcutions = $shiftDropAndCopy->job_instrcutions;
                    $addNewShift->job_instruction_text = $shiftDropAndCopy->job_instruction_text;
                    $addNewShift->run_sheet_roster_id = $shiftDropAndCopy->run_sheet_roster_id;
                    $addNewShift->signin_status = 0;
                    $addNewShift->on_call_job = 0;
                    $addNewShift->created_by = $request->admin_id;
                    $addNewShift->save();
                    if ($shiftDropAndCopy->RunSheetJobRosterTask->count() > 0) {
                        foreach ($shiftDropAndCopy->RunSheetJobRosterTask as $key => $value) {
                            DB::table('run_sheet_job_roster_tasks')->insert([
                                'run_sheet_job_roster_id' => $addNewShift->id,
                                'task' => $value->task,
                                'task_start' => $value->task_start,
                                'task_end' => $value->task_end,
                                'status' => 'pending',
                            ]);
                        }
                    }
                    jobRosterActions($request->admin_id, 'add_shift', $addNewShift->id, 'job_roster');
                    $admin_name = getAdminName($request->admin_id);
                    $currnet_time = time();
                    shiftCompleteActivity($addNewShift->id, $admin_name. ' Copy this Shift', 'add_shift', $addNewShift->id, $currnet_time, $request->admin_id);
                    if((isset($conflictingShift) && !empty($conflictingShift)) || (isset($checkGuardDocs) && $checkGuardDocs == false)){
                        return response()->json(['success' => true, 'message' => '<b>Copied Conflicted Shift!</b>', 'code'=> 200]);
                    }
                    return response()->json([
                        'success' => true,
                        'message' => 'Shift Copied.'
                    ]);
                }
            }else{
                # SHIFT IS UNASSIGNED JUST CHANGE NECESSARY DATA
                if($request->has('type') && $request->type == 'drop'){
                    $hours = $this->getShiftHours(dbFormateDateTime($request->start), dbFormateDateTime($request->end), $request->run_sheet_id);
                    $guardWorkingHours = calCulateGuardWeekHours(dbFormateDateTime($request->start), dbFormateDateTime($request->end));
                    $shiftDropAndCopy->start = dbFormateDateTime($request->start);
                    $shiftDropAndCopy->end = dbFormateDateTime($request->end);
                    $shiftDropAndCopy->morning_hours = (!empty($hours['morning']) ? $hours['morning'] : 0.0);
                    $shiftDropAndCopy->night_hours = (!empty($hours['night']) ? $hours['night'] : 0.0);
                    $shiftDropAndCopy->saturday_morning_hours = (!empty($hours['saturday_morning']) ? $hours['saturday_morning'] : 0.0);
                    $shiftDropAndCopy->saturday_night_hours = (!empty($hours['saturday_night']) ? $hours['saturday_night'] : 0.0);
                    $shiftDropAndCopy->sunday_morning_hours = (!empty($hours['sunday_morning']) ? $hours['sunday_morning'] : 0.0);
                    $shiftDropAndCopy->sunday_night_hours = (!empty($hours['sunday_night']) ? $hours['sunday_night'] : 0.0);
                    $shiftDropAndCopy->ph_morning_hours = (!empty($hours['ph_morning']) ? $hours['ph_morning'] : 0.0);
                    $shiftDropAndCopy->ph_night_hours = (!empty($hours['ph_night']) ? $hours['ph_night'] : 0.0);
                    $shiftDropAndCopy->last_update = time();
                    $shiftDropAndCopy->hours = $guardWorkingHours;
                    // $shiftDropAndCopy->run_sheet_roster_id = $shiftDropAndCopy->run_sheet_roster_id;
                    $shiftDropAndCopy->job_status = 'pending';
                    $shiftDropAndCopy->signin_status = 0;
                    $shiftDropAndCopy->on_call_job = 0;
                    $shiftDropAndCopy->created_by = $request->admin_id;
                    $shiftDropAndCopy->save();
                    jobRosterActions($request->admin_id, 'drop_shift', $shiftDropAndCopy->id,'job_roster');
                    $admin_name = getAdminName($request->admin_id);    
                    $currnet_time = time();
                    shiftCompleteActivity($shiftDropAndCopy->id, $admin_name. ' Drop this Shift', 'drop_shift', $shiftDropAndCopy->id, $currnet_time, $request->admin_id);
                    return response()->json(['success' => true, 'message' => 'Shift Drop Successfully!', 'code' => 200]);
                }else{
                    # HANDLED TWO CASE TYPE IS COPY_SHIFT OR SELECT COPY || SHIFT IS UNASSIGNED JUST COPY THE SAME SHIFT WITH INTERNAL DATA
                    $hours = $this->getShiftHours(dbFormateDateTime($request->start), dbFormateDateTime($request->end), $request->run_sheet_id);
                    $guardWorkingHours = calCulateGuardWeekHours(dbFormateDateTime($request->start), dbFormateDateTime($request->end));
                    $shiftDropAndCopyNew = new RunSheetJobRoster();
                    $shiftDropAndCopyNew->run_sheet_id = $shiftDropAndCopy->run_sheet_id;
                    $shiftDropAndCopyNew->guard_id = (!empty($shiftDropAndCopy->guard_id) ? $shiftDropAndCopy->guard_id : '');
                    $shiftDropAndCopyNew->start = dbFormateDateTime($request->start);
                    $shiftDropAndCopyNew->end = dbFormateDateTime($request->end);
                    $shiftDropAndCopyNew->last_update = time();
                    $shiftDropAndCopyNew->shift_payable = $shiftDropAndCopy->shift_payable;
                    $shiftDropAndCopyNew->shift_chargeable = $shiftDropAndCopy->shift_chargeable;
                    $shiftDropAndCopyNew->custome_rate = $shiftDropAndCopy->custome_rate;
                    $shiftDropAndCopyNew->payrate_level = $shiftDropAndCopy->payrate_level;
                    $shiftDropAndCopyNew->payrate = $shiftDropAndCopy->payrate;
                    $shiftDropAndCopyNew->chargerate_level = $shiftDropAndCopy->chargerate_level;
                    $shiftDropAndCopyNew->chargerate = $shiftDropAndCopy->chargerate;
                    $shiftDropAndCopyNew->un_published_shift = $shiftDropAndCopy->un_published_shift ;
                    $shiftDropAndCopyNew->public_holidays = $shiftDropAndCopy->public_holidays;
                    $shiftDropAndCopyNew->covid_marshal = $shiftDropAndCopy->covid_marshal;
                    $shiftDropAndCopyNew->training = $shiftDropAndCopy->training;
                    $shiftDropAndCopyNew->continuation = $shiftDropAndCopy->continuation;
                    $shiftDropAndCopyNew->over_time = $shiftDropAndCopy->over_time;
                    $shiftDropAndCopyNew->over_time_value = $shiftDropAndCopy->over_time_value;
                    $shiftDropAndCopyNew->travel_time = $shiftDropAndCopy->travel_time;
                    $shiftDropAndCopyNew->travel_time_value = $shiftDropAndCopy->travel_time_value;
                    $shiftDropAndCopyNew->operation_notes = $shiftDropAndCopy->operation_notes;
                    $shiftDropAndCopyNew->shift_create_status = 'pending';
                    $shiftDropAndCopyNew->shift_type = $shiftDropAndCopy->shift_type ;
                    $shiftDropAndCopyNew->conflict = $shiftDropAndCopy->conflict;
                    $shiftDropAndCopyNew->doc_conf = $shiftDropAndCopy->doc_conf;
                    $shiftDropAndCopyNew->work_limitaion_conf = $shiftDropAndCopy->work_limitaion_conf;
                    $shiftDropAndCopyNew->conf_start = $shiftDropAndCopy->conf_start;
                    $shiftDropAndCopyNew->conf_end = $shiftDropAndCopy->conf_end;
                    $shiftDropAndCopyNew->morning_hours = (!empty($hours['morning']) ? $hours['morning'] : 0.0);
                    $shiftDropAndCopyNew->night_hours = (!empty($hours['night']) ? $hours['night'] : 0.0);
                    $shiftDropAndCopyNew->saturday_morning_hours = (!empty($hours['saturday_morning']) ? $hours['saturday_morning'] : 0.0);
                    $shiftDropAndCopyNew->saturday_night_hours = (!empty($hours['saturday_night']) ? $hours['saturday_night'] : 0.0);
                    $shiftDropAndCopyNew->sunday_morning_hours = (!empty($hours['sunday_morning']) ? $hours['sunday_morning'] : 0.0);
                    $shiftDropAndCopyNew->sunday_night_hours = (!empty($hours['sunday_night']) ? $hours['sunday_night'] : 0.0);
                    $shiftDropAndCopyNew->ph_morning_hours = (!empty($hours['ph_morning']) ? $hours['ph_morning'] : 0.0);
                    $shiftDropAndCopyNew->ph_night_hours = (!empty($hours['ph_night']) ? $hours['ph_night'] : 0.0);
                    $shiftDropAndCopyNew->hours = $guardWorkingHours;
                    $shiftDropAndCopyNew->run_sheet_roster_id = $shiftDropAndCopy->run_sheet_roster_id;
                    $shiftDropAndCopyNew->signin_status = 0;
                    $shiftDropAndCopyNew->unprofile_name = $shiftDropAndCopy->unprofile_name;
                    $shiftDropAndCopyNew->po_wo = $shiftDropAndCopy->po_wo;
                    $shiftDropAndCopyNew->on_call_job = 0;
                    $shiftDropAndCopyNew->created_by = $request->admin_id;
                    $shiftDropAndCopyNew->save();
                    if ($shiftDropAndCopy->RunSheetJobRosterTask->count() > 0) {
                        foreach ($shiftDropAndCopy->RunSheetJobRosterTask as $key => $value) {
                            DB::table('job_roster_tasks')->insert([
                                'run_sheet_job_roster_id' => $shiftDropAndCopyNew->id,
                                'task' => $value->task,
                                'task_start' => $value->task_start,
                                'task_end' => $value->task_end,
                                'status' => 'pending',
                            ]);
                        }
                    }
                    jobRosterActions($request->admin_id, 'copy_shift', $shiftDropAndCopyNew->id, 'job_roster');
                    $admin_name = getAdminName($request->admin_id);
                    $currnet_time = time();
                    shiftCompleteActivity($shiftDropAndCopyNew->id, $admin_name. ' Copy this Shift', 'copy_shift', $shiftDropAndCopyNew->id, $currnet_time, $request->admin_id);
                    return response()->json(['success' => true, 'message' => 'Shift Copy Successfully!', 'code' => 200]);
                }
            }
        }else{
            return response()->json([
                'success' => false,
                'message' => 'Shift Not found maybe its deleted.'
            ]);
        }
    }



public function getShiftHours ($start, $end, $siteID = null, $public_holiday = null, $ph_duration = null) {
    $day_start = Carbon::parse($start)->format('l');
    $day_end = Carbon::parse($end)->format('l');

    $start = strtotime($start);
    $end = strtotime($end);

    $diff = $end - $start;
    $hours = round($diff / ( 60 * 60 ), 2);
    $morning_start = 6;
    $morning_end = 18;

    /*$afternoon_start = strtotime("15:00");
    $afternoon_end = strtotime("23:00");*/

    $night_start = 18;
    $night_end = 6;

    $shift_start = $this->convert_into_fraction($start);
    $shift_end = $this->convert_into_fraction($end);
    if ($shift_end < $shift_start) {
        $diff_new = $shift_end + 24 - $shift_start;
        if ($diff > $diff_new) {
               $hours = $diff_new;
           }   
    }
    // saturday calcultions
    $saturday_start = 0;
    $saturday_end = 0;
    $total_saturday_hours = 0;

    $total_ph_hours = 0;
    $ph_start = 0;
    $ph_end = 0;

    // publid holiday calculation start here
    $start_in_public_holiday = false;
    $end_in_public_holiday = false;
    if($siteID != null){
    $site_state = DB::table('run_sheets')->where('id', $siteID)->select('state')->first();
    $states_array = array(
        'Victoria' => 'vic',
        'New South Wales' => 'nsw',
        'NSW' => 'nsw',
        'Queensland' => 'qld',
        'Tasmania' => 'tas',
        'Western Australia' => 'wa',
        'South Australia' => 'sa',
        'ACT' => 'act'
    );
    $state = $site_state->state != '' ? $states_array[$site_state->state] : 'vic';
}else{
    $state = 'vic';
}   
    $public_holiday_start = DB::table('public_holidays')->where('date', date('Ymd', $start))->where('state', $state)->first();
    if ($public_holiday != null && $public_holiday == 1) {
        $start_in_public_holiday = true;
    }elseif (!empty($public_holiday_start)) {
        $start_in_public_holiday = true;
    }

    $public_holiday_end = DB::table('public_holidays')->where('date', date('Ymd', $end))->where('state', $state)->first();
    if (!empty($public_holiday_end)) {
        $end_in_public_holiday = true;
    }elseif($public_holiday != null && $public_holiday == 1 && $ph_duration == 1){
        $end_in_public_holiday = true;
    }

    if ($start_in_public_holiday && $end_in_public_holiday) {
        $total_ph_hours = $hours;
        $hours = 0;
        $ph_start = $shift_start;
        $ph_end = $shift_end;
        $shift_start = 0;
        $shift_end = 0;
        // echo 'whole day in PH - ';

    }elseif($start_in_public_holiday && !$end_in_public_holiday)
    {
        $ph_end = strtotime(date('m/d/Y 23:59:59', $start));
        $diff = $ph_end - $start;
        $total_ph_hours = round($diff / ( 60 * 60 ), 2);
        $ph_start = $this->convert_into_fraction($start);
        $ph_end = $this->convert_into_fraction($ph_end);
        $start = strtotime($public_holiday_start->date) + (60*60*24);
        $day_start = Carbon::parse(date('m/d/Y', $end))->format('l');
        $hours = $hours - $total_ph_hours;
        $shift_start = 0;
        // echo 'Start in PH - '.$day_start;
    }elseif(!$start_in_public_holiday && $end_in_public_holiday){
        $ph_start = strtotime(date('m/d/Y 00:00:00', strtotime($public_holiday_end->date)));
        $diff = $end - $ph_start;
        $total_ph_hours = round($diff / ( 60 * 60 ), 2);
        $ph_start = $this->convert_into_fraction($ph_start);
        // $ph_end = $this->convert_into_fraction($ph_end);
        $end = $this->convert_into_fraction($end);
        $shift_end = 0;
        $ph_end = $end;
        $end = $ph_start;
        $hours = $hours - $total_ph_hours;


        // echo $hours;
    }
    // $day_start = Carbon::parse($start)->format('l');
    // $day_end = Carbon::parse($end)->format('l');
    // print_r(expression)
    // print_r(date('m/d/Y H:i', $end));
    // print('<br>-');
    // print_r($end_in_public_holiday);
    // print('<br>total sat: ');   
    // print_r($total_saturday_hours);
    // print('<br>start: ');   
    // print_r($shift_start);
    // print('<br>end:     ');   
    // print_r($shift_end);
    // print('<br>hours : ');   
    // print_r($hours);
    // exit();
    // print('<br>');
    // print_r($night_end);
    // exit();

    // end of public holiday calculation

    if ($day_start == 'Saturday' && $day_end == 'Saturday') {
        $total_saturday_hours = $hours;
        $saturday_start = $shift_start;
        $saturday_end = $shift_end;
        $shift_start = 0;
        $shift_end = 0;
        $hours = 0;
    }elseif($day_start == 'Saturday' && $day_end != 'Saturday')
    {
        $sat_end = strtotime(date('m/d/Y 23:59:59', $start));
        $diff = $sat_end - $start;
        $total_saturday_hours = round($diff / ( 60 * 60 ), 2);
        $saturday_start = $shift_start;
        $saturday_end = $this->convert_into_fraction($sat_end);
        $shift_start = 0;
        $shift_end = 0;
        $hours = $hours - $total_saturday_hours;
    }elseif($day_start != 'Saturday' && $day_end == 'Saturday')
    {
        $sat_start = strtotime(date('m/d/Y 00:00:00', $end));
        $diff = $end - $sat_start;
        $total_saturday_hours = round($diff / ( 60 * 60 ), 2);
        $saturday_start = $this->convert_into_fraction($sat_start);
        $saturday_end = $shift_end;
        $shift_end = 24;
        $hours = $hours - $total_saturday_hours;
    }
    // sunday_calcultaon
    $sunday_start = 0;
    $sunday_end = 0;
    $total_sunday_hours = 0;
    if ($day_start == 'Sunday' && $day_end == 'Sunday') {
        $total_sunday_hours = $hours;
        $sunday_start = $shift_start;
        $sunday_end = $shift_end;
        $shift_start = 0;
        $shift_end = 0;
        $hours = 0;
    }elseif($day_start == 'Sunday' && $day_end != 'Sunday')
    {
        $sun_end = strtotime(date('m/d/Y 23:59:59', $start));
        $diff = $sun_end - $start;
        $total_sunday_hours = round($diff / ( 60 * 60 ), 2);
        $sunday_start = $shift_start;
        $sunday_end = $this->convert_into_fraction($sun_end);

        $shift_start = 0;
        $hours = $hours-$total_sunday_hours;
    }elseif($day_start != 'Sunday' && $day_end == 'Sunday')
    {
        $sun_start = strtotime(date('m/d/Y 00:00:00', $end));
        // $diff = $end - $sun_start;
        // $total_sunday_hours = round($diff / ( 60 * 60 ), 2);
        $sunday_start = $this->convert_into_fraction($sun_start);
        $sunday_end = $this->convert_into_fraction($end);
        $total_sunday_hours = $sunday_end - $sunday_start;
        $shift_end = 24;
        $shift_start = 24;
        $hours = $hours - $total_sunday_hours;
    }
    if ($start_in_public_holiday && $end_in_public_holiday) {
        $shift_start = 0;
        $shift_end = 0;
        $saturday_start = 0;
        $saturday_end = 0;
        $sunday_start = 0;
        $sunday_end = 0;
        $total_sunday_hours = 0;
        $total_saturday_hours = 0;
    }



    // print('<br>total sat: ');   
    // print_r($total_saturday_hours);
    // print('<br>start: ');   
    
    // print('<br>hours : ');   
    // print_r($hours);
    // exit();
    // print_r($shift_end);
    // print('<br>');

    // exit();
    if ($shift_end < $shift_start && $shift_end < 6 && $shift_end >= 1) {
        $shift_end += 24; 
    }

    // print_r($morning_start);
    // print('<br>');
    // print_r($morning_end);
    // print('<br>');
    // print_r($shift_start);
    // print('<br>end:     ');   
    // print_r($shift_end);
    $morning = round($this->calculateHoursMorning($shift_start, $shift_end, $morning_start, $morning_end), 2);
    // echo $morning;

    $saturday_morning = round($this->calculateHoursMorning($saturday_start, $saturday_end, $morning_start, $morning_end), 2);

    $sunday_morning = round($this->calculateHoursMorning($sunday_start, $sunday_end, $morning_start, $morning_end), 2);

    $ph_morning = round($this->calculateHoursMorning($ph_start, $ph_end, $morning_start, $morning_end), 2);

    // echo $ph_end;
    // exit();

    if ($morning < 0) {
        $morning = 0;
    }
    if ($saturday_morning < 0) {
        $saturday_morning = 0;
    }
    if ($sunday_morning < 0) {
        $sunday_morning = 0;
    }
    // print_r($shift_end);
    return [
        // 'morning' => $this->intersection( $start1, $end, $morning_start, $morning_end ) / 3600,
        'morning' =>  $morning,
        'night' => round(((($hours - $morning) < 0) ? 0 : ($hours - $morning)), 2),
        'saturday_morning' => $saturday_morning,
        'saturday_night' => round(((($total_saturday_hours - $saturday_morning) < 0) ? 0 : ($total_saturday_hours - $saturday_morning)), 2),
        'sunday_morning' => $sunday_morning,
        'sunday_night' => round(((($total_sunday_hours - $sunday_morning) < 0) ? 0 : ($total_sunday_hours - $sunday_morning)), 2),
        'ph_morning' => $ph_morning,
        'ph_night' => round(((($total_ph_hours - $ph_morning) < 0) ? 0 : ($total_ph_hours - $ph_morning)), 2),

        // 'night' => $this->calculateHoursNight($shift_start, $shift_end, $night_start, $night_end ),
    ];
}

function calculateHoursMorning($shift_start, $shift_end, $start, $end)
    {
           if (($shift_start >= $start && $shift_start < $end) && ($shift_end > $start && $shift_end <= $end)) {
               return $shift_end - $shift_start;
           }elseif(($shift_start >= $start && $shift_start < $end) && ($shift_end > $start && $shift_end > $end))
           {
            $shift_end = $end;
            return $shift_end - $shift_start;
           }elseif(($shift_start > $start && $shift_start > $end) && ($shift_end > $start && $shift_end <= $end)){
            $shift_start = $start;
            return $shift_end - $shift_start;
           }elseif(($shift_start < $start && $shift_start < $end) && ($shift_end > $start && $shift_end <= $end)){
            $shift_start = $start;
            return $shift_end - $shift_start;
           }
           elseif($shift_start >= $end && $shift_end > $start && $shift_end < $end)
    {
        // shift start in night in gone into day
        // echo 'Here';
        return $shift_end - $start;
    }
           elseif($shift_start < $start && $shift_end > $end){
            return $end - $start;
           }
           else{
            return 0;
           }
           //  if ($shift_start > $end && $shift_end > $end) {
           //      return 0;
           //  }elseif ($shift_start > $start && $shift_start > $end) {
           //      $shift_start = $start;
           // }

           // if ($shift_end > $start && $shift_end > $end) {
           //  $shift_end = $end;
           // }

           
    }

function convert_into_fraction($time)
{
    return date('H', $time) + (date('i', $time) / 60);
}
public function fetchCustomerUnpublishSites(Request $request)
{

$data_arry = [];
$total_count = 0;

if($request->has('customer_id') && !empty($request->customer_id)){

    if($request->has('start') && $request->start != '')
    {
        $start = dbFormate($request->start);
    }else{
        $start = Carbon::now()->startOfWeek()->toDateString(); 
    }
    if($request->has('end') && $request->end != '')
    {
        $end = dbFormate($request->end);
    }else{
        $end = Carbon::now()->endOfWeek()->toDateString();
    }

    $query ='';

    if($request->type == 'run_sheet'){
        $roster_id = $request->run_sheet_roster_id;
        $query = RunSheetJobRoster::where(function($q) use ($start, $end, $roster_id){
            $q->whereDate('run_sheet_job_rosters.start', '>=', $start)->whereDate('run_sheet_job_rosters.start', '<=', $end)->where('publish_status', 0)->where('run_sheet_job_rosters.run_sheet_roster_id', $roster_id);
        });

        // if ($request->has('customer_id') && !empty($request->customer_id)) {
        //     $query->whereIn('run_sheets.customer_id', [$request->customer_id]);
        // }
        if ($request->has('state')) {
            $query->where('run_sheets.state', $request->state);
        }
        if ($request->has('run_sheet_id')) {
            $query->where('run_sheets.id', $request->run_sheet_id);
        }
        $query->join('run_sheets', 'run_sheet_job_rosters.run_sheet_id', '=', 'run_sheets.id');
        $query->join('guards', 'run_sheet_job_rosters.guard_id', '=', 'guards.id'
        )->select('run_sheet_job_rosters.*','guards.first_name', 'guards.middle_name', 'guards.last_name', 'run_sheets.title as site_name');

        $sts = $query->get();
        $sts = FetchCustomerUnpublishSitesResource::collection($sts);
        }else{
            // Guard type
            $roster_id = $request->run_sheet_roster_id;
            $query = RunSheetJobRoster::where(function($q) use ($start, $end, $roster_id){
                $q->whereDate('run_sheet_job_rosters.start', '>=', $start)->whereDate('run_sheet_job_rosters.start', '<=', $end)->where('publish_status', 0)->where('run_sheet_job_rosters.run_sheet_roster_id', $roster_id);
            });
            if ($request->has('customer_id') && !empty($request->customer_id)) {
                $query->whereIn('run_sheets.customer_id', [$request->customer_id]);
            }
            if ($request->has('state')) {
                $query->where('guards.state', $request->state);
            }
            // if ($request->has('site_id')) {
            //     $query->where('sites.id', $request->site_id);
            // }
            $query->join('run_sheets', 'run_sheet_job_rosters.site_id', '=', 'run_sheets.id');
            $query->join('guards', 'run_sheet_job_rosters.guard_id', '=', 'guards.id'
            )->select('run_sheet_job_rosters.*','guards.first_name', 'guards.middle_name', 'guards.last_name', 'run_sheets.title as site_name');
            $sts = $query->get();
            $sts = FetchCustomerUnpublishSitesResource::collection($sts);
        }
        $queryCount = RunSheetJobRoster::whereDate('start', '>=', $start)->whereDate('start', '<=', $end)->where('publish_status', 0)->count();

        $dateRange = getDatesFromRange($start,$end);
        if($dateRange){
            foreach ($dateRange as $key => $value) {
                $record = RunSheetJobRoster::whereDate('start', $value)->get()->sum('hours');
                $data_arry[dateFormat($value)]=$record;
                $total_count = $total_count + $record;
            }
        }
        return response()->json(['success' => true, 'data' => $sts, 'unpublish_shift_count' => $queryCount,
        'days_hours' => $data_arry, 'total_hours' => $total_count, 'code' => 200]);
    }
}

public function publishShifts(Request $request)
{
    $publishShifts = RunSheetJobRoster::whereIn('id', $request->id)->get();
    $publishShiftEmails = RunSheetJobRoster::join('guards', 'guards.id', '=', 'run_sheet_job_rosters.guard_id')->whereIn('run_sheet_job_rosters.id', $request->email)->select('guards.id', 'guards.email', 'run_sheet_job_rosters.start', 'run_sheet_job_rosters.end')->groupBy('guards.id')->get();
    $publishShiftPhones = RunSheetJobRoster::join('guards', 'guards.id', '=', 'run_sheet_job_rosters.guard_id')->whereIn('run_sheet_job_rosters.id', $request->phone)->where('guards.notification_token', '!=', '')->groupBy('guards.id')->select('guards.id', 'guards.notification_token', 'run_sheet_job_rosters.start', 'run_sheet_job_rosters.end')->get();
    $publishShiftSMS = RunSheetJobRoster::join('guards', 'guards.id', '=', 'run_sheet_job_rosters.guard_id')->whereIn('run_sheet_job_rosters.id', $request->sms)->groupBy('guards.id')->select('guards.id', 'guards.phone')->get();
    if(!empty($publishShifts)){
        RunSheetJobRoster::whereIn('id', $request->id)->update(['publish_status' => 1]);
        foreach ($publishShifts as $key => $value) {
            // $value->publish_status = 1;
            // $value->update();
            jobRosterActions($request->admin_id,'runsheet_shift_publish', $value->id, 'run_sheet_job_rosters');

            $admin_name = getAdminName($request->admin_id);
            $currnet_time = time();
            shiftCompleteActivity($value->id, $admin_name. ' Publish this Shift', 'runsheet_shift_publish', $value->id, $currnet_time, $request->admin_id);
        }
        if (count($publishShiftEmails) > 0) {
            foreach ($publishShiftEmails as $email) {
                $prams['message'] = 'Roster for the week '.usaToAusDateTime($email->start).' - '.usaToAusDateTime($email->end).' has been published. Please open app and confirm your roster.';
                $prams['subject'] = 'Roster Published';
                $prams['email'] = $email->email;
                generalEmails($prams);
            }
        }
        if (count($publishShiftPhones) > 0) {
            foreach ($publishShiftPhones as $phone) {
                $prams['message'] = 'Roster for the week '.usaToAusDateTime($phone->start).' - '.usaToAusDateTime($phone->end).' has been published.';
                $prams['title'] = 'Roster Published';
                $prams['page'] = 'roster';
                $prams['notification_token'] = $phone->notification_token;
                send_push_notification($prams);
                //sendSmsToGuard($phone->phone, '');
            }           
        }
        if (count($publishShiftSMS) > 0) {
            foreach ($publishShiftSMS as $sms) {
                sendSmsToGuard($sms->phone, 'Hi '.$sms->first_name.' '.$sms->last_name.' '. 'your shift has been published successfully!');
            }           
        }
        return response()->json(['message' => "Shift Published" ,  'code' => 200, 'success' => true]);
    }else{
        return response()->json(['message' => "Shift Not Found!" ,  'code' => 404, 'success' => false]); 
    }
}
    public function deleteRunSheetTask(Request $request)
    {
        $jobRosterTask  = RunSheetJobRosterTask::where('id', $request->id)->first();
        $old_data = $jobRosterTask;
        if(!empty($jobRosterTask)){
            $jobRosterTask->delete();
            jobRosterActions($request->admin_id, 'delete_tasks', $jobRosterTask->id, 'run_sheet', $old_data);
            return response()->json(['message' => "Task Deleted" ,  'code' => 200, 'success' => true]);
        }else{
            return response()->json(['message' => "Task Not Found" ,  'code' => 404, 'success' => false],404);
        }
    }
    public function fetchTemplateShift(Request $request)
    {
        $fetchTemplateShift = RunSheetJobRoster::where('shift_type', 'template')->get();
        $fts = AllTemplateShifts::collection($fetchTemplateShift);
        return response()->json(['success' => true, 'data' => $fts, 'code' => 200]);
    }
    public function updateRunsheetTime(Request $request)
    {
        $runsheetRosterTime = RunSheetJobRoster::where('id', $request->id)->first();
        $old_data = $runsheetRosterTime;
        $hours =  calCulateGuardWeekHours(dbFormateDateTime($request->start),dbFormateDateTime($request->end));
        if($runsheetRosterTime){
            $runsheetRosterTime->start = dbFormateDateTime($request->start);
            $runsheetRosterTime->end = dbFormateDateTime($request->end);
            $runsheetRosterTime->hours = $hours;
            $runsheetRosterTime->update();
            $check = checkGuardShiftTimingRSUpdate($request->start, $request->end, $runsheetRosterTime->guard_id, $runsheetRosterTime->run_sheet_id);
            $runsheetRosterTime->conf_start = (!empty($check['start']) ? dbFormateDateTime($check['start']) : '');
            $runsheetRosterTime->conf_end = (!empty($check['end']) ? dbFormateDateTime($check['end']) : '');
            $runsheetRosterTime->conflict =  (!empty($check['conf']) ? $check['conf'] : '');
            $runsheetRosterTime->update();
            jobRosterActions($request->admin_id,'update_shift_time',$runsheetRosterTime->id, 'run_sheet_roster',$old_data);

            $admin_name = getAdminName($request->admin_id);
            $currnet_time = time();
            shiftCompleteActivity($runsheetRosterTime->id, $admin_name. ' Update Time of this Shift', 'update_runsheet_shift_time', $runsheetRosterTime->id, $currnet_time, $request->admin_id);

            return response()->json(['message' => "Shift Time Updated" ,  'code' => 200, 'success' => true]);
        }else{
            return response()->json(['message' => "Shift not Found!" ,  'code' => 404, 'success' => false]);
        }
    }
    public function getRunsheetDeletedShifts(Request $request){
        $query = RunSheetJobRoster::query();
        if(isset($request->admin_id)){
            $query->whereIn('deleted_by', $request->admin_id);
        }
        if(isset($request->start) && isset($request->end)){
            $start = $request->start.' 00:00';
            $end = $request->end.' 23:59';
            $query->where('start', '>=', $start)->where('start', '<=', $end);
        }
        $deletedShifts = $query->onlyTrashed()->where('run_sheet_id', $request->run_sheet_id)->get();
        // $createdShifts = $query->where(['roster_id', $request->roster_id])->get();
        $deletedShiftsRes = RosterDeletedShifts::collection($deletedShifts);
        return response()->json([
            'success' => true,
            'deletedShifts' => $deletedShiftsRes
        ]);

    }
    public function getCopyShiftRS(Request $request)
    {
        $runSheet = RunSheetJobRoster::join('run_sheets', 'run_sheets.id', '=', 'run_sheet_job_rosters.run_sheet_id')
        ->where('run_sheet_job_rosters.start', '>=', dbFormate($request->start))
        ->where('run_sheet_job_rosters.start', '<=', dbFormate($request->end))
        ->where('run_sheet_job_rosters.guard_id', '>', 0)
        // ->where('run_sheets.site_status', 'active')
        ->groupBy('run_sheets.id')
        ->select('run_sheets.id', 'run_sheets.title')
        ->get();
        if (count($runSheet) > 0) {
            return response()->json(['message' => "Runsheet Publish list!" ,  'code' => 200, 'success' => true, 'data' => $runSheet]);
        }else{
            return response()->json(['message' => "No Runsheet for publish!" ,  'code' => 404, 'success' => false, 'data' => $runSheet]); 
        }
    }
    public function copyRunsheetNextDates(Request $request)
    {
        $formattedDates = [];
        
        foreach ($request->days as $day) {
            $dayNumber = $day - 1;
            $startDate = date('Y-m-d', strtotime('last Monday'));
            $formattedDates[] = date('Y-m-d H:i', strtotime("+$dayNumber days", strtotime($startDate)));
        }
        $rosters = RunSheetJobRoster::whereIn(DB::raw('DATE(start)'), $formattedDates)
        ->whereIn('run_sheet_id', $request->runsheet_id)
        ->with('RunSheetJobRosterTask')
        ->get();
        // Check if $rosters is empty
        if ($rosters->isEmpty()) {
            return response()->json(['success' => true, 'message' => 'Shifts not found']);
        }
        $conflicts = 0;
        $copied = 0;
        foreach ($rosters as $key => $roster) {
            if($request->rates){
                if($roster->custome_rate == 1 && $roster->custome_payrate == 1 && $roster->custome_chagerate == 1){
                    !empty($roster->manualPayRate) ? $roster->manualPayRate : null;   
                    !empty($roster->manualChargeRate) ? $roster->manualChargeRate : null;
                }elseif($roster->custome_rate == 1){
                    !empty($roster->custome_rate) ? $roster->custome_rate : null;   
                    !empty($roster->payrate) ? $roster->payrate : null;   
                    !empty($roster->chargerate) ? $roster->chargerate : null;   
                    !empty($roster->payrate_level) ? $roster->payrate_level : null;   
                    !empty($roster->chargerate_level) ? $roster->chargerate_level : null;   
                }else{
                    $roster->custome_rate = null; 
                    $roster->payrate = null;  
                    $roster->chargerate = null;   
                    $roster->payrate_level = null;   
                    $roster->chargerate_level = null; 
                }  
            }

            foreach ($request->weeks as $key1 => $week_no) 
            {
                $next_roster = [
                    'run_sheet_id' => $roster->run_sheet_id,
                    'guard_id' => ($request->remove_staff == true && $request->remove_staff == 'true' ? '' : $roster->guard_id),
                    'start' => date("Y-m-d H:i", strtotime(date("Y-m-d H:i", strtotime($roster->start)) . " +".$week_no." week")),
                    'end' => date("Y-m-d H:i", strtotime(date("Y-m-d H:i", strtotime($roster->end)) . " +".$week_no." week")),
                    'shift_payable' => $roster->shift_payable,
                    'shift_chargeable' => $roster->shift_chargeable,
                    'custome_rate' => $roster->custome_rate,
                    'payrate' => $roster->payrate,
                    'chargerate_level' => $roster->chargerate_level,
                    'payrate_level' => $roster->payrate_level,
                    'chargerate' => $roster->chargerate,
                    'un_published_shift' => $roster->un_published_shift,
                    'public_holidays' => $roster->public_holidays,
                    'covid_marshal' => $roster->covid_marshal,
                    'training' => $roster->training,
                    'continuation' => $roster->continuation,
                    'over_time' => $roster->over_time,
                    'over_time_value' => $roster->over_time_value,
                    'travel_time' => $roster->travel_time,
                    'travel_time_value' => $roster->travel_time_value,
                    'shift_create_status' => $roster->shift_create_status,
                    'shift_type' => $roster->shift_type,
                    'doc_conf' => $roster->doc_conf,
                    'conf_end' => $roster->conf_end,
                    'work_limitaion_conf' => $roster->work_limitaion_conf,
                    'total_week_hours' => $roster->total_week_hours,
                    'update_status' => $roster->update_status,
                    'signin_status' => 0,
                    'last_update' => time(),
                    'job_status' => 'pending',
                    'break_status' => $roster->break_status,
                    'operation_notes' => ($request->notes != false && $request->notes != 'false' ? '' : $roster->operation_notes) ,
                    'hours' => $roster->hours,
                    'run_sheet_roster_id' => $roster->run_sheet_roster_id,
                    'unprofile_name' => $roster->unprofile_name,
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s'),
                ];

                $hours = $this->getShiftHours($next_roster['start'], $next_roster['end'], $next_roster['run_sheet_id']);
                $next_roster['morning_hours'] = $hours['morning'];
                $next_roster['night_hours'] = $hours['night'];
                $next_roster['saturday_morning_hours'] = $hours['saturday_morning'];
                $next_roster['saturday_night_hours'] = $hours['saturday_night'];
                $next_roster['sunday_morning_hours'] = $hours['sunday_morning'];
                $next_roster['sunday_night_hours'] = $hours['sunday_night'];
                $next_roster['ph_morning_hours'] = $hours['ph_morning'];
                $next_roster['ph_night_hours'] = $hours['ph_night'];
                if ($next_roster['guard_id'] > 0) {
                    $check = checkGuardShiftTimingRS($next_roster['start'], $next_roster['end'], $next_roster['guard_id'], $roster->run_sheet_roster_id);
                    $next_roster['conflict'] = (!empty($check['conf']) ? $check['conf'] : '');
                    $next_roster['conf_start'] = (!empty($check['start']) ? dbFormateDateTime($check['start']) : '');
                    $next_roster['conf_end'] = (!empty($check['end']) ? dbFormateDateTime($check['end']) : '');
                    $guardWorkingHours = calCulateGuardWeekHours($next_roster['start'], $next_roster['end']);
                    $guardWorkLimitation = checkGuardWorkLimitation($next_roster['guard_id'], $guardWorkingHours);
                    $check2 = checkGuardDocuments($next_roster['guard_id']);
                    $conflict = false;
                    if(!empty($check['start']) || !empty($check['end']) || !empty($check['conf']) || (!empty($check2) && $check2 != 'active') || !empty($guardWorkLimitation)){
                        $conflicts++;
                        $checkAdmin  = checkAdmin($request->admin_id);
                        if($checkAdmin == 'admin'){
                            $conflict = true;
                        }
                    }
                    if (!$conflict) {
                        $addNewShift = RunSheetJobRoster::insertGetId($next_roster);

                        foreach ($roster->RunSheetJobRosterTask as $key => $value) {
                            DB::table('run_sheet_job_roster_tasks')->insert([
                                'run_sheet_job_roster_id' => $addNewShift,
                                'task_start' => $value->task_start,
                                'task_end' => $value->task_end,
                                'status' => 'pending',
                            ]);
                        }

                        jobRosterActions($request->admin_id, 'add_shift_copy_run_sheet', $addNewShift, 'run_sheet_job_roster');
                        $copied++;
                    }


                }else{
                    $addNewShift = RunSheetJobRoster::insertGetId($next_roster);
                    jobRosterActions($request->admin_id, 'add_shift_copy_run_sheet', $addNewShift, 'run_sheet_job_roster');
                    $copied++;
                }
            }
        }
        return response()->json(['message' => "Shift copy successfully." ,  'code' => 200, 'success' => true, 'conflicts' => $conflicts, 'copied' => $copied]);
    }
    public function runsheetActions(Request $request)
    {
        $query = RunSheetJobRoster::where('run_sheet_job_rosters.start', '>=', dbFormateDateTimeStart($request->start))
        ->where('run_sheet_job_rosters.start', '<=', dbFormateDateTimeEnd($request->end));
        if ($request->has('runsheets') && !empty($request->runsheets)) {
            $query->whereIn('run_sheet_job_rosters.run_sheet_id', $request->runsheets);
        }
        if ($request->has('guardIds') && !empty($request->guardIds)) {
            $query->whereIn('run_sheet_job_rosters.guard_id', $request->guardIds);
        }
        if ($request->has('customer_ids') && !empty($request->customer_ids)) {
            $query->join('run_sheets', 'run_sheets.id', '=', 'run_sheet_job_rosters.run_sheet_id');
            // $query->whereIn('run_sheets.customer_id', [$request->customer_ids]);
            $query->orWhereJsonContains('run_sheets.customer_id', $request->customer_ids);
        }
        if ($request->has('run_sheet_roster_id') && $request->run_sheet_roster_id != '') {
            $query->where('run_sheet_job_rosters.run_sheet_roster_id', $request->run_sheet_roster_id);
        }
        $deleted_shifts = $query->select('run_sheet_job_rosters.id','run_sheet_job_rosters.guard_id')->get();
        if ($request->type == 'clear_shifts') {
            foreach ($deleted_shifts as $key => $d) {
                RunSheetJobRoster::where('id', $d->id)->delete();
                DB::table('run_sheet_job_roster_activites')->where(['guard_id'=> $d->guard_id, 'job_roster_id'=>$d->id])->delete();
                DB::table('run_sheet_incident_reports')->where(['roster_id'=> $d->id])->delete();
                DB::table('run_sheet_job_roster_tasks')->where(['run_sheet_job_roster_id'=> $d->id])->delete();
                DB::table('run_sheet_job_breaks')->where(['roster_id'=> $d->id])->delete();
                jobRosterActions($request->admin_id, 'delete_shift', $d->id, 'run_sheet_job_roster');
            }
            return response()->json(['message' => "Shifts cleared" ,  'code' => 200, 'success' => true]);
        }elseif($request->type == 'unpublish')
        {
            foreach ($deleted_shifts as $key => $d) {
                RunSheetJobRoster::where('id', $d->id)->update(['publish_status' => 0]);
                jobRosterActions($request->admin_id, 'shift_unpublish', $d->id, 'run_sheet_job_roster');
            }
            return response()->json(['message' => "shift unpublished" ,  'code' => 200, 'success' => true]);
        }elseif($request->type == 'unassign')
        {
            foreach ($deleted_shifts as $key => $d) {
                RunSheetJobRoster::where('id', $d->id)->update(['guard_id' => NULL,
                    'conflict' => '',
                    'conf_start' => '',
                    'conf_end' => '',
                ]);
                jobRosterActions($request->admin_id, 'shift_guard_unassign', $d->id, 'run_sheet_job_roster');
            }
            return response()->json(['message' => "unassigned shift posted" ,  'code' => 200, 'success' => true]);
        }
        elseif($request->type == 'rollover')
        {
            return $this->rolloverWeek($request);
        }elseif($request->type == 'copy_current')
        {
            return $this->copyIntoCurrent($request);
        }
    }
    public function rolloverWeek($request)
    {
        $query = RunSheetJobRoster::where('run_sheet_job_rosters.start', '>=', (dbFormate($request->start).' 00:00'));
        if ($request->has('customer_ids') && !empty($request->customer_ids) && $request->has('runsheetIds') && !empty($request->runsheetIds)) {
            $query->join('run_sheets', 'run_sheets.id', '=', 'run_sheet_job_rosters.run_sheet_id');
            // $query->whereIn('run_sheets.customer_id', $request->customer_ids);
            $query->orWhereJsonContains('run_sheets.customer_id', $request->customer_ids);
            $query->whereIn('run_sheets.id', $request->runsheetIds);
        }
        if ($request->has('run_sheet_roster_id') && $request->run_sheet_roster_id != '') {
            $query->where('run_sheet_job_rosters.run_sheet_roster_id', $request->run_sheet_roster_id);
        }
        $query->where('run_sheet_job_rosters.start', '<=', (dbFormate($request->end).' 23:59'));
        // ->where('run_sheet_job_rosters.guard_id', '>', 0); # UNCOMMENT WHEN YOU WANT TO COPY ONLY ASSIGNED SHIFTS
        if (!empty($request->run_sheets)) {
            $query->whereIn('run_sheet_job_rosters.run_sheet_id', $request->run_sheets);
        }
        $rosters = $query->select('run_sheet_job_rosters.*')->with('RunSheetJobRosterTask')
        ->get();
        $days = ['mon' => 'monday', 'tue' => 'tuesday', 'wed' => 'wednesday', 'thu' => 'thursday', 'fri' => 'friday', 'sat' => 'saturday' , 'sun' => 'sunday'];

        $conflicts = 0;
        $copied = 0;
        $week_no = 1;
        foreach ($rosters as $key => $roster) {
            $last_shift_day_start = strtolower(date('D', strtotime($roster->start)));
            $last_shift_day_end = strtolower(date('D',  strtotime($roster->end)));
            $shift_day = $days[$last_shift_day_start];
            $shift_day_end = $days[$last_shift_day_end];
            $start_time = date('H:i', strtotime($roster->start));
            $end_time = date('H:i', strtotime($roster->end));
            $next_roster = [
                'run_sheet_id' => $roster->run_sheet_id,
                'guard_id' => $roster->guard_id,
                'start' => date('Y-m-d', strtotime($shift_day .' next week')) .' '. $start_time,
                'end' => date('Y-m-d', strtotime($shift_day_end .' next week')).' '.$end_time,
                'shift_payable' => $roster->shift_payable,
                'shift_chargeable' => $roster->shift_chargeable,
                'custome_rate' => $roster->custome_rate,
                'payrate' => $roster->payrate,
                'chargerate_level' => $roster->chargerate_level,
                'chargerate' => $roster->chargerate,
                'manualPayRate' => $request->manualPayRate,
                'manualChargeRate' => $request->manualChargeRate,
                'un_published_shift' => $roster->un_published_shift,
                'public_holidays' => $roster->public_holidays,
                'covid_marshal' => $roster->covid_marshal,
                'training' => $roster->training,
                'continuation' => $roster->continuation,
                'over_time' => $roster->over_time,
                'over_time_value' => $roster->over_time_value,
                'travel_time' => $roster->travel_time,
                'travel_time_value' => $roster->travel_time_value,
                'shift_create_status' => $roster->shift_create_status,
                'shift_type' => $roster->shift_type,
                'doc_conf' => $roster->doc_conf,
                'conf_end' => $roster->conf_end,
                'work_limitaion_conf' => $roster->work_limitaion_conf,
                'total_week_hours' => $roster->total_week_hours,
                'update_status' => $roster->update_status,
                'signin_status' => 0,
                'on_call_job' => 0,
                'last_update' => time(),
                'job_status' => 'pending',
                'break_status' => $roster->break_status,
                'operation_notes' => $roster->operation_notes,
                'hours' => $roster->hours,
                'created_by' => $request->admin_id,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
                'run_sheet_roster_id' => $roster->run_sheet_roster_id,
                'unprofile_name' => $roster->unprofile_name,
            ];


            $hours = $this->getShiftHours($next_roster['start'], $next_roster['end'], $next_roster['run_sheet_id']);
            $next_roster['morning_hours'] = $hours['morning'];
            $next_roster['night_hours'] = $hours['night'];
            $next_roster['saturday_morning_hours'] = $hours['saturday_morning'];
            $next_roster['saturday_night_hours'] = $hours['saturday_night'];
            $next_roster['sunday_morning_hours'] = $hours['sunday_morning'];
            $next_roster['sunday_night_hours'] = $hours['sunday_night'];
            $next_roster['ph_morning_hours'] = $hours['ph_morning'];
            $next_roster['ph_night_hours'] = $hours['ph_night'];
            if ($next_roster['guard_id'] > 0) {
            $check = checkGuardShiftTiming($next_roster['start'], $next_roster['end'], $next_roster['guard_id'], $roster->run_sheet_roster_id);
            $next_roster['conflict'] = (!empty($check['conf']) ? $check['conf'] : '');
            $next_roster['conf_start'] = (!empty($check['start']) ? dbFormateDateTime($check['start']) : '');
            $next_roster['conf_end'] = (!empty($check['end']) ? dbFormateDateTime($check['end']) : '');
            $guardWorkingHours = calCulateGuardWeekHours($next_roster['start'], $next_roster['end']);
            $guardWorkLimitation = checkGuardWorkLimitation($next_roster['guard_id'], $guardWorkingHours);
            $check2 = checkGuardDocuments($next_roster['guard_id']);
            $conflict = false;
            if(!empty($check['start']) || !empty($check['end']) || !empty($check['conf']) || (!empty($check2) && $check2 != 'active') || !empty($guardWorkLimitation)){
                $conflicts++;
                $checkAdmin  = checkAdmin($request->admin_id);
                if($checkAdmin == 'admin'){
                    $conflict = true;
                }
            }
            if (!$conflict) {
                $addNewShift = RunSheetJobRoster::insertGetId($next_roster);
                foreach ($roster->RunSheetJobRosterTask as $key => $value) {
                    DB::table('	run_sheet_job_roster_tasks')->insert([
                        'run_sheet_job_roster_id' => $addNewShift,
                        'task_start' => $value->task_start,
                        'task_end' => $value->task_end,
                        'status' => 'pending',
                    ]);
                }
                jobRosterActions($request->admin_id, 'add_shift_copy', $addNewShift, 'run_sheet_job_roster');
                $copied++;
            }
        }else{
            $addNewShift = RunSheetJobRoster::insertGetId($next_roster);

            jobRosterActions($request->admin_id, 'add_shift_copy', $addNewShift, 'run_sheet_job_roster');
            $copied++;
        }
    }
    return response()->json(['message' => "Shift rollover completed" ,  'code' => 200, 'success' => true, 'conflicts' => $conflicts, 'copied' => $copied]);
    }
    public function copyIntoCurrent($request)
    {
        $query = RunSheetJobRoster::where('run_sheet_job_rosters.start', '>=', (dbFormate($request->start).' 00:00'));
        if ($request->has('customer_ids') && !empty($request->customer_ids)) {
            $query->join('run_sheets', 'run_sheets.id', '=', 'run_sheet_job_rosters.run_sheet_id');
            $query->orWhereJsonContains('run_sheets.customer_id', $request->customer_ids);
            // $query->whereIn('run_sheets.customer_id', [$request->customer_ids]);
        }
        if ($request->has('run_sheet_roster_id') && $request->run_sheet_roster_id != '') {
            $query->where('run_sheet_job_rosters.run_sheet_roster_id', $request->run_sheet_roster_id);
        }
        $query->where('run_sheet_job_rosters.start', '<=', (dbFormate($request->end).' 23:59'));
        // ->where('run_sheet_job_rosters.guard_id', '>', 0); # UNCOMMENT WHEN YOU WANT TO COPY ONLY ASSIGNED SHIFTS
        if (!empty($request->runsheets)) {
            $query->whereIn('run_sheet_job_rosters.run_sheet_id', $request->runsheets);
        }
        
        $rosters = $query->select('run_sheet_job_rosters.*')->with('RunSheetJobRosterTask')
        ->get();
        
        $days = ['mon' => 'monday', 'tue' => 'tuesday', 'wed' => 'wednesday', 'thu' => 'thursday', 'fri' => 'friday', 'sat' => 'saturday' , 'sun' => 'sunday'];

        $conflicts = 0;
        $copied = 0;
        $week_no = 1;
        foreach ($rosters as $key => $roster) {
            $last_shift_day_start = strtolower(date('D', strtotime($roster->start)));
            $last_shift_day_end = strtolower(date('D',  strtotime($roster->end)));
            $shift_day = $days[$last_shift_day_start];
            $shift_day_end = $days[$last_shift_day_end];
            $start_time = date('H:i', strtotime($roster->start));
            $end_time = date('H:i', strtotime($roster->end));
            $next_roster = [
                'run_sheet_id' => $roster->run_sheet_id,
                'guard_id' => $roster->guard_id,
                'start' => date('Y-m-d', strtotime($shift_day .' this week')) .' '. $start_time,
                'end' => date('Y-m-d', strtotime($shift_day_end .' this week')).' '.$end_time,
                'shift_payable' => $roster->shift_payable,
                'shift_chargeable' => $roster->shift_chargeable,
                'custome_rate' => $roster->custome_rate,
                'payrate' => $roster->payrate,
                'chargerate_level' => $roster->chargerate_level,
                'chargerate' => $roster->chargerate,
                'un_published_shift' => $roster->un_published_shift,
                'public_holidays' => $roster->public_holidays,
                'covid_marshal' => $roster->covid_marshal,
                'training' => $roster->training,
                'continuation' => $roster->continuation,
                'over_time' => $roster->over_time,
                'over_time_value' => $roster->over_time_value,
                'travel_time' => $roster->travel_time,
                'travel_time_value' => $roster->travel_time_value,
                'shift_create_status' => $roster->shift_create_status,
                'shift_type' => $roster->shift_type,
                'doc_conf' => $roster->doc_conf,
                'conf_end' => $roster->conf_end,
                'work_limitaion_conf' => $roster->work_limitaion_conf,
                'total_week_hours' => $roster->total_week_hours,
                'update_status' => $roster->update_status,
                'signin_status' => 0,
                'on_call_job' => 0,
                'last_update' => time(),
                'job_status' => 'pending',
                'break_status' => $roster->break_status,
                'operation_notes' => $roster->operation_notes,
                'hours' => $roster->hours,
                'created_by' => $roster->admin_id,
                'run_sheet_roster_id' => $roster->run_sheet_roster_id,
                'unprofile_name' => $roster->unprofile_name,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ];
            $hours = $this->getShiftHours($next_roster['start'], $next_roster['end'], $next_roster['run_sheet_id']);
            $next_roster['morning_hours'] = $hours['morning'];
            $next_roster['night_hours'] = $hours['night'];
            $next_roster['saturday_morning_hours'] = $hours['saturday_morning'];
            $next_roster['saturday_night_hours'] = $hours['saturday_night'];
            $next_roster['sunday_morning_hours'] = $hours['sunday_morning'];
            $next_roster['sunday_night_hours'] = $hours['sunday_night'];
            $next_roster['ph_morning_hours'] = $hours['ph_morning'];
            $next_roster['ph_night_hours'] = $hours['ph_night'];
            if ($next_roster['guard_id'] > 0) {
            $check = checkGuardShiftTimingRS($next_roster['start'], $next_roster['end'], $next_roster['guard_id'], $roster->run_sheet_roster_id);
            $next_roster['conflict'] = (!empty($check['conf']) ? $check['conf'] : '');
            $next_roster['conf_start'] = (!empty($check['start']) ? dbFormateDateTime($check['start']) : '');
            $next_roster['conf_end'] = (!empty($check['end']) ? dbFormateDateTime($check['end']) : '');
            $guardWorkingHours = calCulateGuardWeekHours($next_roster['start'], $next_roster['end']);
            $guardWorkLimitation = checkGuardWorkLimitation($next_roster['guard_id'], $guardWorkingHours);
            $check2 = checkGuardDocuments($next_roster['guard_id']);
            $conflict = false;
            if(!empty($check['start']) || !empty($check['end']) || !empty($check['conf']) || (!empty($check2) && $check2 != 'active') || !empty($guardWorkLimitation)){
                $conflicts++;
                $checkAdmin  = checkAdmin($request->admin_id);
                if($checkAdmin == 'admin'){
                    $conflict = true;
                }
            }
            if (!$conflict) {
                $addNewShift = RunSheetJobRoster::insertGetId($next_roster);
                foreach ($roster->RunSheetJobRosterTask as $key => $value) {
                    DB::table('run_sheet_job_roster_tasks')->insert([
                        'run_sheet_job_roster_id' => $addNewShift,
                        'task_start' => $value->task_start,
                        'task_end' => $value->task_end,
                        'status' => 'pending',
                    ]);
                }
                jobRosterActions($request->admin_id, 'add_shift_copy', $addNewShift, 'run_sheet_job_roster');
                $copied++;
            }

        }else{
            $addNewShift = RunSheetJobRoster::insertGetId($next_roster);
            jobRosterActions($request->admin_id, 'add_shift_copy', $addNewShift, 'run_sheet_job_roster');
            $copied++;
        }

    }
    return response()->json(['message' => "Coped this to current week!.",  'code' => 200, 'success' => true, 'conflicts' => $conflicts, 'copied' => $copied]);
    }
    public function getGuardRunsheet(Request $request)
    {
        $guard = Guard::where('id', $request->id)->first();
        if($guard){
        $customers = json_decode($guard->customer_id);
        $runsheet = json_decode($guard->run_sheet_id);
        if(!empty($customers) || !empty($runsheet)){
            $query = RunSheet::query();
            if(!empty($customers))
            {
                $query->where(function ($query) use ($customers) {
                    foreach ($customers as $value) {
                        $query->orWhereJsonContains('customer_id', $value);
                    }
                });
            }
            if(!empty($runsheet))
            {
                $query->whereIn('id', $runsheet);
            }

            $runsheet = $query->select('id', 'title')->orderBy('title', 'asc')->get();
            // $guard_sites = GetGuardSitesResource::collection($runsheet);
            return response()->json(['success' => true, 'data' => $runsheet]);
        }else{
            return response()->json(['success' => false, 'message' => 'No Runsheet Found!']);
        }
        }else{
            return response()->json(['success' => false, 'message' => 'Staff not Found!']);
        }
    }
}
