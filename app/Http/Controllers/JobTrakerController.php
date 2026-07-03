<?php

namespace App\Http\Controllers;

use App\Http\Resources\JobTrakerJobResource;
use App\Http\Resources\TimeSheetDetailsResource;
use App\Models\JobRoster;
use Illuminate\Http\Request;
use Carbon\Carbon;
use DB;

class JobTrakerController extends Controller
{

    function getJobTraker(Request $request)
    {
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
        // ->where('job_status', 'completed')->where('job_rosters.admin_approved', 1)
        $timesheet = JobRoster::whereIn('guard_id', $request->guard_id)
        ->join('guards', 'guards.id', '=', 'job_rosters.guard_id')
        ->select('guards.id', 'guards.first_name','guards.middle_name', 'guards.last_name', DB::raw('SUM(job_rosters.morning_hours) AS morning_hours'), DB::raw('SUM(job_rosters.night_hours) AS night_hours'), DB::raw('SUM(job_rosters.saturday_morning_hours) AS saturday_morning_hours'), DB::raw('SUM(job_rosters.saturday_night_hours) AS saturday_night_hours'), DB::raw('SUM(job_rosters.sunday_morning_hours) AS sunday_morning_hours'), DB::raw('SUM(job_rosters.sunday_night_hours) AS sunday_night_hours'), DB::raw('SUM(job_rosters.ph_morning_hours) AS ph_morning_hours'), DB::raw('SUM(job_rosters.ph_night_hours) AS ph_night_hours'), DB::raw('SUM(job_rosters.hours) AS hours'))
        //->whereBetween('start', [$start, $end])
        ->whereDate('start', '>=', $start)
        ->whereDate('start', '<=', $end)
        ->groupBy('guards.id')
        ->groupBy('guards.first_name')
        ->groupBy('guards.middle_name')
        ->groupBy('guards.last_name')
        ->get();
        if(count($timesheet) > 0){
            return response()->json(['success' =>  true, 'code' => 200, 'message' => 'JobTraker found.', 'data' => $timesheet]);
        }else{
            return response()->json(['success' =>  false, 'code' => 200, 'message' => 'No JobTraker found!', 'data' => $timesheet]);
        }
    }


    public function getJobTrakerDetails(Request $request)
    {
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
        $rosters =  JobRoster::where('guard_id', $request->guard_id)->where(function($q) {
            // $q->where('job_status', 'completed');
            // $q->orWhere('job_rosters.admin_approved', 1);
        })
        ->whereDate('start', '>=', $start)
        ->whereDate('start', '<=', $end)
        ->with(['site', 'guardz', 'site.customer', 'rosterActivity'])->get();
        $data = TimeSheetDetailsResource::collection($rosters);
        if (count($data) > 0) {
         return response()->json(['success' => true, 'data' => $data]);
     }
     return response()->json(['success' => false, 'data' => $data]);
 }

    public function jobStatusManualApproved(Request $request)
    {
        $jobStatusManualApproved = JobRoster::where('id', $request->roster_id)->first();
        if($jobStatusManualApproved){
            $old_data = $jobStatusManualApproved;
            if($jobStatusManualApproved->admin_approved == 0){
                $jobStatusManualApproved->admin_approved = 1;
                $jobStatusManualApproved->admin_approved_by = $request->admin_id;
                $jobStatusManualApproved->in_paysheet = 1;
                $jobStatusManualApproved->update();
                
                jobRosterActions($request->admin_id, 'manual_approved_shift', $jobStatusManualApproved->id, 'job_rosters', $old_data);
                return response()->json(['success' => true, 'msg' => 'Status Updated Successfully!']);
            }else{
                $jobStatusManualApproved->admin_approved = 0;
                $jobStatusManualApproved->in_paysheet = 0;
                $jobStatusManualApproved->admin_approved_by = $request->admin_id;
                $jobStatusManualApproved->update();
                jobRosterActions($request->admin_id, 'manual_disapproved_shift', $jobStatusManualApproved->id, 'job_rosters', $old_data);
                return response()->json(['success' => true, 'msg' => 'Status Updated Successfully!']);
            }
        }else{
            return response()->json(['success' => true, 'msg' => 'Status Updated Successfully!']);
        }
    
}
function jobTrakerJobs(Request $request)
{
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

    $query = JobRoster::join('sites', 'sites.id', '=', 'job_rosters.site_id')
    ->join('guards', 'guards.id', '=', 'job_rosters.guard_id')
    ->join('customers', 'customers.id', '=', 'sites.customer_id')
    ->join('job_roster_activites', 'job_roster_activites.job_roster_id', '=', 'job_rosters.id');
    
    if($request->has('customer_ids') && !empty($request->customer_ids))
    {
        $query->whereIn('customers.id', $request->customer_ids);
    }
     if($request->has('sites_ids') && !empty($request->sites_ids))
    {
        $query->whereIn('job_rosters.site_id', $request->sites_ids);
    }
     if($request->has('guard_ids') && !empty($request->guard_ids))
    {
        $query->whereIn('job_rosters.guard_id', $request->guard_ids);
    }
    if($request->has('state') && !empty($request->state))
    {
        $query->whereIn('sites.state', $request->state);
    }
    
    // if($request->has('site_type') && !empty($request->site_type))
    // {
    //     $query->where('sites.site_status', $request->site_status);
    // }
    // if ($request->type == 'Past-jobs') 
    // {
    //     $query->whereBetween('job_rosters.start', [dbFormate($request->start), dbFormate($request->end)]);
    //     $query->where('job_rosters.end', '<=', date('Y-m-d H:i'));
    // }elseif($request->type == 'Ongoing-jobs')
    // {
    //     $query->where('job_rosters.start', '<=', date('Y-m-d H:i'));
    //     $query->where('job_rosters.end', '>=', date('Y-m-d H:i'));
    //     $query->where('job_rosters.signin_status', 1);
    // }
    // elseif($request->type == 'Missed-job')
    // {
    //     $query->where('job_rosters.start', '<=', date('Y-m-d H:i'));
    //     $query->where('job_rosters.end', '>=', date('Y-m-d H:i'));
    //     $query->where('job_rosters.signin_status', 0);
    // }
    // elseif($request->type == 'Upcoming-jobs')
    // {
    //     $query->where('job_rosters.start', '>=', date('Y-m-d H:i'));
    // } 
    $query->whereDate('job_rosters.start', '>=', $start);
    $query->whereDate('job_rosters.start', '<=', $end);
    $query->select('job_rosters.id', 'job_rosters.hours', 'job_rosters.start', 'job_rosters.end', 'job_rosters.job_status as status', 'job_rosters.admin_approved as approved', 'guards.first_name', 'guards.last_name', 'guards.middle_name', 'job_rosters.guard_id', 'job_rosters.site_id', 'job_rosters.admin_approved_by', 'sites.site_name', 'customers.name as customer_name', 'job_roster_activites.signin_time as authorized_start_time', 'job_roster_activites.signout_time as authorized_end_time');
    $data = JobTrakerJobResource::collection($query->get());  
    if(count($data) > 0){
            return response()->json(['success' =>  true, 'code' => 200, 'message' => 'JobTraker found.', 'data' => $data ]);
        }else{
            return response()->json(['success' =>  false, 'code' => 200, 'message' => 'No JobTraker found!', 'data' => $data]);
        }
}

public function jobsCount(Request $request)
{
    $query = JobRoster::join('sites', 'sites.id', '=', 'job_rosters.site_id')
    ->join('guards', 'guards.id', '=', 'job_rosters.guard_id')
    ->join('customers', 'customers.id', '=', 'sites.customer_id');
    if($request->has('customer_ids') && !empty($request->customer_ids))
    {
        $query->whereIn('customers.id', $request->customer_ids);
    }
     if($request->has('sites_ids') && !empty($request->sites_ids))
    {
        $query->whereIn('job_rosters.site_id', $request->sites_ids);
    }
     if($request->has('guard_ids') && !empty($request->guard_ids))
    {
        $query->whereIn('job_rosters.guard_id', $request->guard_ids);
    }
    if($request->has('state') && !empty($request->state))
    {
        $query->whereIn('sites.state', $request->state);
    }
    if($request->has('site_type') && !empty($request->site_type))
    {
        $query->where('sites.site_status', $request->site_status);
    }
    $past_jobs_query = $query;
    $past_jobs = $past_jobs_query->where('job_rosters.end', '<=', date('Y-m-d H:i'))->get()->count();
    $ongoing_jobs_query = $query;
    $ongoing_jobs = $ongoing_jobs_query->where('job_rosters.start', '<=', date('Y-m-d H:i'))->where('job_rosters.end', '>=', date('Y-m-d H:i'))->where('job_rosters.signin_status', 1)->get()->count();
    $missed_jobs_query = $query;
    $missed_jobs = $missed_jobs_query->where('job_rosters.start', '<=', date('Y-m-d H:i'))->where('job_rosters.end', '>=', date('Y-m-d H:i'))->where('job_rosters.signin_status', 0)->get()->count();
    $upcoming_jobs_query = $query;
    $upcoming_jobs = $upcoming_jobs_query->where('job_rosters.start', '>=', date('Y-m-d H:i'))->get()->count();
    return response()->json(['success' =>  true, 'code' => 200, 'message' => 'Count.', 'data' => [
        'past_jobs' => $past_jobs,
        'ongoing_jobs' => $ongoing_jobs,
        'missed_jobs' => $missed_jobs,
        'upcoming_jobs' => $upcoming_jobs,
    ]]);

}
}
