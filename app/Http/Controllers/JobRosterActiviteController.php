<?php

namespace App\Http\Controllers;

use App\Http\Resources\getjobRosterTaskResource;
use App\Http\Resources\GuardBreakDetailsResource;
use App\Http\Resources\GuardFootPatrolReportRecource;
use App\Http\Resources\GuardGreenCallDetailResource;
use App\Http\Resources\GuardIncidentReportRecource;
use App\Http\Resources\GuardJobTrakerResource;
use App\Http\Resources\GuardShiftActivityResource;
use App\Http\Resources\GuardWelFareCallResource;
use App\Http\Resources\JobSignInSignOutResource;
use App\Models\QrScanner;
use App\Models\JobRoster;
use App\Models\JobRosterActivity;
use App\Models\JobRosterTask;
use App\Models\PatrollingReport;
use App\Models\Site;
use Illuminate\Http\Request;
use Dompdf\Dompdf;
use Illuminate\Support\Facades\DB;

class JobRosterActiviteController extends Controller
{
    public function JobSignInSignOut(Request $request)
    {
        $guard_activites = JobRosterActivity::where('guard_id', $request->guard_id)->where('job_roster_id', $request->job_id)->first();
        if($guard_activites){
            $g_act = (new JobSignInSignOutResource($guard_activites));
            return response()->json(['success' => true,'code' => 200 , 'data' => $g_act]);
        }else{
            return response()->json(['success' => false,'code' => 404 , 'message' => 'Record Not Found!']);
        } 
    }


    public function guardBreakDetails(Request $request)
    {
        $guardBreak =  \DB::table('job_breaks')->where('roster_id', $request->roster_id)
        ->where('guard_id', $request->guard_id)
        ->select('id', 'start_time', 'end_time', 'notes', 'inform_to')->get();
        if($guardBreak){
            $gb =  GuardBreakDetailsResource::collection($guardBreak);
            return response()->json(['success' => true,'code' => 200 , 'data' => $gb]); 
        }else{
            return response()->json(['success' => false,'code' => 404 , 'data' => '']);
        }
    }



    public function guardWelfareCall(Request $request)
    {
         
        $WelfareCall =  \DB::table('welfare_call_data')->where('job_roster_id', $request->roster_id)
        ->where('guard_id', $request->guard_id)->select('id', 'response_time', 'status', 'coordinates', 'send_time')->get();
        if($WelfareCall){
           $wf_call = GuardWelFareCallResource::collection($WelfareCall);
            return response()->json(['success' => true,'code' => 200 , 'data' => $wf_call]); 
        }else{
            return response()->json(['success' => false,'code' => 404 , 'data' => '']);
        }
    }



    public function guardGreenCallDetails(Request $request)
    {  
        $greenCallDetail =  \DB::table('green_call')->where('job_id', $request->roster_id)
        ->where('guard_id', $request->guard_id)
        ->select('id','before_time', 'status', 'coordinates', 'created_at', 'send_time', 'response_time')->get();
        if($greenCallDetail){
            $gcd = GuardGreenCallDetailResource::collection($greenCallDetail);
            return response()->json(['success' => true,'code' => 200 , 'data' => $gcd]); 
        }else{
            return response()->json(['success' => false,'code' => 404 , 'data' => '']);
        }
    }



    public function guardJobTraker(Request $request)
    {
         
        $guardJobTraker =  DB::table('guard_location_at_job')->where('roster_id', $request->roster_id)
        ->where('guard_id', $request->guard_id)->select('id', 'created_at', 'event_time', 'distance', 'coordinates')->take(100)->get();
        if($guardJobTraker){

            $gt = GuardJobTrakerResource::collection($guardJobTraker);

            return response()->json(['success' => true,'code' => 200 , 'data' => $gt]); 
        }else{
            return response()->json(['success' => false,'code' => 404 , 'data' => '']);
        }
    }

    public function guardPatrollingReport(Request $request)
    {
        $roster = JobRoster::with(['Guards', 'Sites.customer'])->where('id', $request->roster_id)->first();

        if (!$roster) {
            return response()->json([
                'success' => false,
                'message' => 'Roster not found'
            ], 404);
        }

        $site = Site::where('id', 1269)->first();

        $data = QrScanner::where('scan_at', '>=', $roster->start)
        ->where('scan_at', '<=', $roster->end)
        ->get()
        ->map(function ($scan) use ($site) {
            $scan->site_name = $site->site_name;
            return $scan;
        });

        return response()->json([
            'success' => true,
            'data'    => $data,
            'shift_data' => $roster
        ]);
    }

    public function guardFootPatrolReport(Request $request)
    {
        $guardFootPatrolReport =  \DB::table('foot_patrol_reports')->where('roster_id', $request->roster_id)
        ->where('guard_id', $request->guard_id)
        ->select('id', 'site_name', 'date', 'time', 'pdf', 'patrolling_detail', 'photo', 'signature')->get();
        if($guardFootPatrolReport){

            $roster = JobRoster::where('id', $request->roster_id)->first();
            if($roster){
                $staff = !empty($roster->guard_id) ? getGuardName($roster->guard_id) : null;
                $loaction = !empty($roster->site_id) ? getSiteName($roster->site_id) : null;
                $shift_start = !empty($roster->start) ? usaToAusDateTime($roster->start) : '';
                $shift_end = !empty($roster->end) ? usaToAusDateTime($roster->end) : '';
                $customer_id = '';
                if(!empty($roster->site_id)){
                        $s = Site::where('id', $roster->site_id)->first();
                        $customer_id = $s->customer_id;
                    }    
                $customer = !empty($customer_id) ? getCustomerName($customer_id) : null;
            }

            $gIr =  GuardFootPatrolReportRecource::collection($guardFootPatrolReport);
            
            return response()->json(['success' => true,'code' => 200 , 'data' => $gIr,
            'staff' => $staff, 'loaction' => $loaction, 
            'customer' =>  $customer,
            'shift_start' => $shift_start,
            'shift_end' => $shift_end]); 

        }else{
            return response()->json(['success' => false,'code' => 404 , 'data' => '']);
        }
    }
    public function guardIncidentReport(Request $request)
    {
        $guardIncidentReport =  \DB::table('incident_reports')->where('roster_id', $request->roster_id)
        ->where('guard_id', $request->guard_id)
        ->select('id', 'site_name', 'incident_date', 'incident_time', 'injury_type', 'pdf', 'injury_detail', 'people_involved','vehicle','emergency_services', 'wittness', 'photo', 'signature')->get();
        if($guardIncidentReport){

            $roster = JobRoster::where('id', $request->roster_id)->first();
            if($roster){
                $staff = !empty($roster->guard_id) ? getGuardName($roster->guard_id) : null;
                $loaction = !empty($roster->site_id) ? getSiteName($roster->site_id) : null;
                $shift_start = !empty($roster->start) ? usaToAusDateTime($roster->start) : '';
                $shift_end = !empty($roster->end) ? usaToAusDateTime($roster->end) : '';
                $customer_id = '';
                if(!empty($roster->site_id)){
                        $s = Site::where('id', $roster->site_id)->first();
                        $customer_id = $s->customer_id;
                    }    
                $customer = !empty($customer_id) ? getCustomerName($customer_id) : null;
            }

            $gIr =  GuardIncidentReportRecource::collection($guardIncidentReport);
            
            return response()->json(['success' => true,'code' => 200 , 'data' => $gIr,
            'staff' => $staff, 'loaction' => $loaction, 
            'customer' =>  $customer,
            'shift_start' => $shift_start,
            'shift_end' => $shift_end]); 

        }else{
            return response()->json(['success' => false,'code' => 404 , 'data' => '']);
        }
    }

    function getShiftActivity(Request $request){
        $getShiftActivity =  \DB::table('roster_complete_activity')->where('roster_id', $request->roster_id)
        ->select('id', 'activity', 'type', 'activity_time')->get();

        if($getShiftActivity){

            $roster = JobRoster::where('id', $request->roster_id)->first();
            if($roster){
                $staff = !empty($roster->guard_id) ? getGuardName($roster->guard_id) : null;
                $loaction = !empty($roster->site_id) ? getSiteName($roster->site_id) : null;
                $shift_start = !empty($roster->start) ? usaToAusDateTime($roster->start) : '';
                $shift_end = !empty($roster->end) ? usaToAusDateTime($roster->end) : '';
                $customer_id = '';
                if(!empty($roster->site_id)){
                        $s = Site::where('id', $roster->site_id)->first();
                        $customer_id = $s->customer_id;
                    }
                    
                $customer = !empty($customer_id) ? getCustomerName($customer_id) : null;
            }
            $gIr =  GuardShiftActivityResource::collection($getShiftActivity);
            return response()->json(['success' => true,'code' => 200 , 'data' => $gIr,
                'staff' => $staff, 'loaction' => $loaction, 
                'customer' =>  $customer,
                'shift_start' => $shift_start,
                'shift_end' => $shift_end,
            ]); 
        }else{
            return response()->json(['success' => false,'code' => 404 , 'data' => '']);
        }
    }
    

    public function storeOperationNotes(Request $request)
    {
       $storeOperationNotes = JobRoster::where('id', $request->roster_id)->first();
       if(!empty($storeOperationNotes->operation_notes)){
        return response()->json(['success' => false,'message' => 'Operation Notes Already Store!']);
       }else{
        $storeOperationNotes->operation_notes = $request->operation_notes;
        $storeOperationNotes->save();
        return response()->json(['success' => true,'message' => 'Operation Notes Added Successfully!']);
       }
    }
    public function giveRatingJobRoster(Request $request)
    {
        $storeRating = JobRoster::where('guard_id', $request->guard_id)->where('id', $request->roster_id)->first();
        if(!empty($storeRating->rating)){
            return response()->json(['success' => false,'message' => 'Rating Already Store!']);
        }else{
            $storeRating->rating = $request->rating;
            $storeRating->rating_desc = $request->rating_desc;
            $storeRating->save();
            return response()->json(['success' => true,'message' => 'Rating Added Successfully!']);
        }
    }

   public function getJobrosterRating(Request $request)
   {
    $getRating = JobRoster::where('guard_id', $request->guard_id)
    ->where('id', $request->roster_id)->select('rating', 'rating_desc')->first();
    return response()->json(['success' => true, 'data' => $getRating]);
   }
   public function getOperationNotes(Request $request)
   {
    $getOperationNotes = JobRoster::where('guard_id', $request->guard_id)
    ->where('id', $request->roster_id)->select('operation_notes','id')->first();
    return response()->json(['success' => true, 'data' => $getOperationNotes]);
   }

   public function getJobTasks(Request $request) {
    $tasks = JobRosterTask::where('job_roster_id', $request->roster_id)->with('shift')->get();
    $ts = getjobRosterTaskResource::collection($tasks);

    $roster = JobRoster::where('id', $request->roster_id)->first();
    if($roster){
        $staff = !empty($roster->guard_id) ? getGuardName($roster->guard_id) : null;
        $loaction = !empty($roster->site_id) ? getSiteName($roster->site_id) : null;
        $shift_start = !empty($roster->start) ? usaToAusDateTime($roster->start) : '';
        $shift_end = !empty($roster->end) ? usaToAusDateTime($roster->end) : '';
        $customer_id = '';
        if(!empty($roster->site_id)){
                $s = Site::where('id', $roster->site_id)->first();
                $customer_id = $s->customer_id;
            }
            
        $customer = !empty($customer_id) ? getCustomerName($customer_id) : null;
    }

    return response()->json(['success' => true, 'data' => $ts, 
    'staff' => $staff, 'loaction' => $loaction, 
    'customer' =>  $customer,
    'shift_start' => $shift_start,
    'shift_end' => $shift_end,

    ]);

   }


   function generateJobTaskReport(Request $request){
    
    $tasks = JobRosterTask::where('job_roster_id', $request->roster_id)->with('shift')->get();
    $ts = getjobRosterTaskResource::collection($tasks); 
    $business_name = "AMG";
    $roster = JobRoster::where('id', $request->roster_id)->first();
    if($roster){
        $staff = !empty($roster->guard_id) ? getGuardName($roster->guard_id) : null;
        $loaction = !empty($roster->site_id) ? getSiteName($roster->site_id) : null;
        $shift_start = !empty($roster->start) ? usaToAusDateTime($roster->start) : '';
        $shift_end = !empty($roster->end) ? usaToAusDateTime($roster->end) : '';
        $customer_id = '';
        if(!empty($roster->site_id)){
                $s = Site::where('id', $roster->site_id)->first();
                $customer_id = $s->customer_id;
            }
            
        $customer = !empty($customer_id) ? getCustomerName($customer_id) : null;

        $name = '';
        $html = view('shift-activity-task-pdf', ['data' => $ts, 
        'staff' => $staff, 'location' => $loaction, 
        'customer' =>  $customer,
        'business_name' =>  $business_name,
        'shift_start' => $shift_start,
        'shift_end' => $shift_end]);
        $dompdf = new Dompdf();
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        $output = $dompdf->output();
        $public_path = public_path();
        $public_path = str_replace('247StaffingSolution/public/', '', $public_path);
        $folder ='/shift_task_pdf';
        $path = $public_path.$folder;
        if (!file_exists($path)) {
            mkdir($path, 0777, true);
        }
        $file_name = time() . '_shift_tasks_report.pdf';
        $result = file_put_contents($path.'/'.$file_name, $output);
        $name = $file_name;
        # ADD TO HISTORY TABLE TO DELETE AFTER 1 MONTH
        $transient_file = DB::table('transient_files')->insert([
            'folder' => 'shift_task_pdf',
            'file_name' => $name,  
        ]);
        return response()->json(['success' =>  true, 'message' => 'Shift Task Report generate successfully.','path' => 'https://'.request()->getHttpHost().'/shift_task_pdf/'.$name]);
    }



   }




}
