<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Session;
use App\Models\Guard_payroll_id as guard_payroll_ids;
use DatePeriod;
use DateInterval;
use App\Http\Requests\StoreJobRosterRequest;
use App\Http\Resources\AllNewJobRosterResource;
use App\Http\Resources\AllTemplateShifts;
use App\Mail\GenericMail;
use App\Http\Resources\AvailableGuardResource;
use App\Http\Resources\CurrentWeekUnCoverdShiftResource;
use App\Http\Resources\CustomerNameResource;
use App\Http\Resources\EditJobNewRosterResource;
use App\Http\Resources\EditJobRoster;
use App\Http\Resources\FetchCustomerSitesResource;
use App\Http\Resources\FetchCustomerSitesWithGuardResource;
use App\Http\Resources\FetchCustomerUnpublishSitesResource;
use App\Http\Resources\RosterDeletedShifts;
use Illuminate\Support\Facades\Mail;
use App\Models\CustomerInvoice;
use Dompdf\Dompdf;
use App\Models\Customer;
use App\Models\Payrate;
use App\Models\Guard;
use App\Models\JobNewRoster;
use App\Models\JobRoster;
use App\Models\GuardDocument;
use App\Models\JobRosterTask;
use App\Models\Site;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\JobRosterAction;
use App\Models\GuardWorkDetail;
use App\Models\PatrollingReport;
use App\Models\QrScanner;
use DateTime;
use App\Http\Resources\GetGuardBySiteResource;
use App\Http\Resources\GuardShiftActivityResource;
use PragmaRX\Google2FA\Google2FA;

class JobRosterController extends Controller
{
  public function store(Request $request)
  {
    $jobNewRoster = new JobNewRoster();
    $jobNewRoster->roster_name = $request->roster_name;
    $jobNewRoster->state = $request->state;
    $jobNewRoster->start = dbFormate($request->start);
    $jobNewRoster->end = ($request->end == '' && $request->end == null   ? $request->end : dbFormate($request->end));
    $jobNewRoster->customer_id = json_encode($request->customer_id);
    $jobNewRoster->site_id = json_encode($request->site_id);
    $jobNewRoster->user_id = json_encode($request->user_id);
    //$jobNewRoster->status = 'active';
    $jobNewRoster->save();
    jobRosterActions($request->admin_id, 'add_new_jobroster', $jobNewRoster->id, 'new_job_roster');
    return response()->json(['message' => "New Roster Created", 'code' => 200, 'success' => true]);
}

public function getAllJobNewRosters(Request $request)
{
    // if($request->type == 'super-admin'){

        $query = JobNewRoster::query();
        if($request->has('state') && !empty($request->state)){
            $query->where('state', $request->state);
        }
        $jobNewRoster = $query->where('status', $request->status)->get();
        $jobNewRosters = AllNewJobRosterResource::collection($jobNewRoster);
        return response()->json(['success' => true, 'data' => $jobNewRosters, 'code' => 200]);


    // }elseif($request->type == 'customer'){
    //     $jobNewRoster = JobNewRoster::where('status', $request->status)->get();
    //     $finalArr = [];
    //     foreach($jobNewRoster as $item){
    //         if(in_array($request->id, json_decode($item->customer_id))){
    //             $finalArr[] = $item;
    //         }
    //     }
    //     $jobNewRosters = AllNewJobRosterResource::collection($finalArr);
    //     return response()->json(['success' => true, 'data' => $jobNewRosters, 'code' => 200]);
    // }
}


public function editJobNewRoster(Request $request)
{
    $editJobNewRoster = JobNewRoster::where('id', $request->id)->first();
    if($editJobNewRoster){
       $jnr =  new EditJobNewRosterResource($editJobNewRoster);
       return response()->json(['success' => true, 'data' => $jnr, 'code' => 200]);
    }else{
        return response()->json(['success' => true, 'data' => '', 'code' => 200]); 
    }
}

public function updateJobNewRoster(Request $request)
{
   $updateJobNewRoster = JobNewRoster::where('id', $request->id)->first();
   $old_data = JobNewRoster::where('id', $request->id)->first();;
   if($updateJobNewRoster){
    $updateJobNewRoster->roster_name = $request->roster_name;
    $updateJobNewRoster->state = $request->state;
    $updateJobNewRoster->start = dbFormate($request->start);
    $updateJobNewRoster->end = ($request->end == '' && $request->end == null   ? $request->end : dbFormate($request->end));
    $updateJobNewRoster->customer_id = json_encode($request->customer_id);
    $updateJobNewRoster->site_id = json_encode($request->site_id);
    $updateJobNewRoster->user_id = json_encode($request->user_id);
    $updateJobNewRoster->save();
    $updatedNewRoster = $updateJobNewRoster->getChanges();
    jobRosterActions($request->admin_id, 'update_new_jobroster', $updateJobNewRoster->id, 'new_job_roster', $old_data, $updatedNewRoster);
    return response()->json(['message' => "Roster Updated", 'code' => 200, 'success' => true]);
   } 
}

public function todayrostercount(Request $request)
{
    $date = Carbon::today()->format('Y-m-d');
    $todaysJobRosters = JobRoster::join('guards', 'job_rosters.guard_id', '=', 'guards.id')
    ->whereDate('job_rosters.start', $date)
    ->select('guards.profile_image')
    ->groupBy('guard_id')
    ->get();
    

    foreach($todaysJobRosters as $g)
        {
            $g->profile_image = returnImgPath('guard',$g->profile_image);
           
        }

    $count = $todaysJobRosters->count();
    $profileImages = $todaysJobRosters->pluck('profile_image')->toArray();


    return response()->json(['success' => true, 'count' => $count, 'profile_image' => $profileImages]);
}

public function newJobRosterCustomers(Request $request)
    {
      $newJobRoster = JobNewRoster::where('id', $request->id)->first();
      if(!empty($newJobRoster->customer_id)){
        $newJobRosterCustomers = Customer::where(function($query) use ($request, $newJobRoster){
            foreach (json_decode($newJobRoster->customer_id) as $key => $value) {
               $query->orWhere('id', $value);
            }
         })->select('id','name')->orderBy('name')->get();
          $cus = CustomerNameResource::collection($newJobRosterCustomers);
          return response()->json(['success' => true, 'data' => $cus, 'roster_name' => $newJobRoster->roster_name, 'roster_status' => $newJobRoster->status]);
      }else{
        return response()->json(['success' => true, 'data' => '']);
      }
      
    }




// public function updateJobRosterStatus(Request $request)
// {
//     $jobNewRoster = JobNewRoster::where('id', $request->id)->first();
//     $old_data = $jobNewRoster;
//     $shifts = JobRoster::where('roster_id', $request->id)->get();

//     if($jobNewRoster){

//         if(count($shifts) > 0  && $request->status == 'deleted' && $request->confirm == 'yes'){
//             DB::table('job_rosters')->where(['roster_id'=> $request->id])->delete();
//             $jobNewRoster->status = $request->status;
//             $jobNewRoster->update();
//             jobRosterActions($request->admin_id, 'update_new_jobroster_status', $jobNewRoster->id, 'new_job_roster', $old_data);
//             return response()->json(['success' => true, 'msg' => 'Status Updated', 'code' => 200]);
//         }

//         if(count($shifts) > 0  && $request->status == 'deleted'){
//             return response()->json(['success' => false, 'msg' => 'This roster has shifts. Are you sure you want to delete it?']);
//         }

//         $jobNewRoster->status = $request->status;
//         $jobNewRoster->update();
//         jobRosterActions($request->admin_id, 'update_new_jobroster_status', $jobNewRoster->id, 'new_job_roster', $old_data);
//         return response()->json(['success' => true, 'msg' => 'Status Updated', 'code' => 200]);
//     }else{
//         return response()->json(['success' => false, 'msg' => 'Roster Not Found!', 'code' => 404]);
//     }
// }

public function updateJobRosterStatus(Request $request)
{
    $jobNewRoster = JobNewRoster::find($request->id);

    if (!$jobNewRoster) {
        return response()->json(['success' => false, 'msg' => 'Roster Not Found!', 'code' => 404]);
    }

    $old_data = $jobNewRoster;
    $shifts = JobRoster::where('roster_id', $request->id)->get();

    if (count($shifts) > 0 && $request->status == 'deleted' && $request->confirm == 'yes') {
        DB::table('job_rosters')->where(['roster_id' => $request->id])->delete();
    } elseif (count($shifts) > 0 && $request->status == 'deleted') {
        return response()->json(['success' => false, 'msg' => 'This roster has shifts. Are you sure you want to delete it?']);
    }

    $jobNewRoster->status = $request->status;
    $jobNewRoster->update();
    $jobNewRoster_updated = $jobNewRoster->getChanges();
    jobRosterActions($request->admin_id, 'update_new_jobroster_status', $jobNewRoster->id, 'new_job_roster', $old_data, $jobNewRoster_updated);

    return response()->json(['success' => true, 'msg' => 'Status Updated', 'code' => 200]);
}



public function getSitesByCustomes(Request $request)
{
    $sites = Site::whereIn('customer_id', $request->customer_id)->select('id', 'site_name')->get();
    return response()->json(['success' => true, 'data' => $sites, 'code' => 200]);
}

public function fetchCustomerSites(Request $request)
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
    $roster_id = $request->roster_id;

    if($request->type == 'location'){
        
        $site_type = $request->site_type;
        $query = Site::with(['jobRoster' => function ($que) use ($start, $end, $site_type, $roster_id){
            if ($site_type == 'active') {
                $que->where('start', '>=', $start)
                ->where('start', '<=', $end)->where('roster_id', $roster_id)->orderBy('job_rosters.start', 'asc')->orderBy('job_rosters.end', 'desc');

            }elseif($site_type == 'inactive')
            {
                $que->where('start', '>=', $start)
                ->where('start', '<=', $end)->where('roster_id', $roster_id)->orderBy('job_rosters.start', 'asc')->orderBy('job_rosters.end', 'desc');

            }else{
                $que->where('start', '>=', $start)
                ->where('start', '<=', $end)->where('roster_id', $roster_id)->orderBy('job_rosters.start', 'asc')->orderBy('job_rosters.end', 'desc');

            }
            $que->with('jobRosterTask');

        }]);

        if ($request->has('customer_id') && !empty($request->customer_id)) {
            $query->whereIn('customer_id', $request->customer_id);
        }

        if ($request->has('state')) {
            $query->where('sites.state', $request->state);
        }

        if ($request->has('site_id')) {
            $query->whereIn('sites.id', $request->site_id);
        }

        if($site_type == 'active')
        {
            $query->join('job_rosters', 'job_rosters.site_id', '=', 'sites.id');
            $query->where('job_rosters.start', '>=', $start)
                ->where('job_rosters.start', '<=', $end)->where('roster_id', $roster_id)->orderBy('job_rosters.start', 'asc')->orderBy('job_rosters.end', 'desc')->where('job_rosters.deleted_at', null);

        }elseif($site_type == 'all'){
            $query->join('job_rosters', 'job_rosters.site_id', '=', 'sites.id', 'left')->orderBy('job_rosters.start', 'asc')->orderBy('job_rosters.end', 'desc');
        }else{
            $query->join('job_rosters', 'job_rosters.site_id', '=', 'sites.id', 'left')->orderBy('job_rosters.start', 'asc')->orderBy('job_rosters.end', 'desc');
        }
        $query->select('sites.id', 'sites.site_name', 'sites.site_description', 'sites.customer_id', DB::raw("COUNT(job_rosters.id) count"))
        // ->where('job_rosters.deleted_at', null)
        ->groupBy('sites.id')
        ->groupBy('sites.site_description')
        ->groupBy('sites.site_name')
        ->groupBy('sites.customer_id')
        ->orderBy('sites.site_name');
        $sites = $query->get();

        
        if(empty($sites)){
            return response()->json(['success' => false, 'data' => null, 'code' => 404]); 
        }
        if($site_type == 'inactive')
        {
            $inactive_sites = [];
            foreach ($sites as $key => $s) {
                if (count($s->jobRoster) == 0) {
                    $inactive_sites[] = $s;
                }
            }
            $sts = FetchCustomerSitesResource::collection($inactive_sites);
        }else{
            $sts = FetchCustomerSitesResource::collection($sites);
        }
        // ->where('publish_status', 0)
        // $queryCount = JobRoster::where('start', '>=', $start)->where('start', '<=', $end)
        // ->where('guard_id', '!=',  '')->where('guard_id', '!=',  'NULL')->where('guard_id', '!=', NULL)
        // ->where('publish_status', 0)->where(function($q){
        //     $q->orWhere('shift_type','!=','template');
        //     //$q->orWhere('shift_type', '!=', NULL);
        // })->where('roster_id', $roster_id)->where('deleted_at', null)->whereNotNull('site_id')->count();
        $publishCount = JobRoster::query()
            ->join('sites', 'sites.id', '=', 'job_rosters.site_id')
            ->where('job_rosters.start', '>=', $start)
            ->where('job_rosters.start', '<=', $end)
            ->whereNotNull('job_rosters.guard_id')
            ->where('job_rosters.guard_id', '!=', '')
            ->where('job_rosters.guard_id', '!=', 'NULL')
            ->where('job_rosters.publish_status', 0)
            ->where('job_rosters.roster_id', $request->roster_id)
            ->whereNull('job_rosters.deleted_at');

        if ($request->has('customer_id') && !empty($request->customer_id)) {
            $publishCount->where(function ($que) use ($request) {
              foreach ($request->customer_id as $key => $cId) {
                    if ($key == 0) {
                        $que->where('sites.customer_id', $cId);
                    } else {
                        $que->orWhere('sites.customer_id', $cId);
                    }
                }
            });
        }

        $queryCount = $publishCount->count();
      
        //for hours calculates
         //dd($start);
         //dd($end);

        $dateRange = getDatesFromRange(dbFormate($request->start),dbFormate($request->end));
        if($dateRange){

            

            if($request->has('site_id') && !empty($request->site_id)){
                foreach ($dateRange as $key => $value) {
                    $record = JobRoster::whereIn('site_id', $request->site_id)
                        ->whereDate('job_rosters.start', $value)
                        ->where(function ($q) {
                            $q->orWhere('job_rosters.shift_type', '!=', 'template');
                            $q->orWhereNull('job_rosters.shift_type');
                        })
                        ->where('job_rosters.roster_id', $roster_id)
                        ->sum('job_rosters.hours');
                
                    $data_arry[dateFormat($value)] = $record;
                    $total_count = $total_count + $record;
                }
            }else{
                foreach ($dateRange as $key => $value) {
                    $record = JobRoster::join('sites', 'job_rosters.site_id', '=', 'sites.id')
                        ->whereIn('sites.customer_id', $request->customer_id)
                        ->whereDate('job_rosters.start', $value)
                        ->where(function ($q) {
                            $q->orWhere('job_rosters.shift_type', '!=', 'template');
                            $q->orWhereNull('job_rosters.shift_type');
                        })
                        ->where('job_rosters.roster_id', $roster_id)
                        ->sum('job_rosters.hours');
                
                    $data_arry[dateFormat($value)] = round($record, 2);
                    $total_count = $total_count + $record;
                }
            }
        }

        $total_count = round($total_count, 2);

        //dd($data_arry);
        return response()->json(['success' => true, 'data' => $sts, 'unpublish_shift_count' => $queryCount,
            'days_hours' => $data_arry, 'total_hours' => $total_count,
            'code' => 200]);
    }
    // Guard type
    else{

        $guard_type = $request->guard_type;
        $roster_id = $request->roster_id;
        $siteIds = [];
        if ($request->has('customer_id')) {
            $siteIds = DB::table('sites')
            ->select('id')
            ->whereIn('customer_id', $request->customer_id)
            ->distinct()
            ->get()
            ->pluck('id');            
        }
       $query = Guard::with(['guardJobRoster' => function($que) use ($start, $end, $guard_type, $roster_id, $siteIds){
            if ($guard_type == 'active') {
                //$que->whereBetween('start', [$start, $end]);
                $que->where('start', '>=', $start)
                ->where('start', '<=', $end)->where('roster_id', $roster_id)->whereIn('site_id', $siteIds);
            }elseif($guard_type == 'inactive')
            {
                //$que->whereBetween('start', [$start, $end]);
                $que->where('start', '>=', $start)
                ->where('start', '<=', $end)->where('roster_id', $roster_id)->whereIn('site_id', $siteIds);
            }else{
                //$que->whereBetween('start', [$start, $end]);
                $que->where('start', '>=', $start)
                ->where('start', '<=', $end)->where('roster_id', $roster_id)->whereIn('site_id', $siteIds);
            }
            // $que->where('update_status', 0);
        }]);
        if ($request->has('state')) {
            $query->where('guards.state', $request->state);
        }
        if ($request->has('guard_id')) {
            $query->where('guards.id', $request->guard_id);
        }
        if($guard_type == 'active')
        {
            $query->join('job_rosters', 'job_rosters.guard_id', '=', 'guards.id');
            $query->where('job_rosters.start', '>=', $start)->whereNull('job_rosters.deleted_at')

                ->where('job_rosters.start', '<=', $end)->where('roster_id', $roster_id)->orderBy('job_rosters.start', 'asc');
        }elseif($guard_type == 'all'){
            $query->leftJoin('job_rosters', 'job_rosters.guard_id', '=', 'guards.id')
                ->orderBy('job_rosters.start', 'asc');
        }else{
            $query->leftJoin('job_rosters', 'job_rosters.guard_id', '=', 'guards.id')
                ->where('guards.guard_status', 'active')->whereNull('job_rosters.deleted_at')
                ->orderBy('job_rosters.start', 'asc');
        }
        if ($request->has('customer_id')) {
            $query->whereIn('job_rosters.site_id', $siteIds);
        }
        $query->where('guards.guard_status', '!=', 'deleted')->select('guards.id', 'guards.phone', 'guards.first_name', 'guards.middle_name','guards.last_name', 'guards.email', 'guards.profile_image')->orderBy('job_rosters.start', 'asc')
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
        if($guard_type == 'inactive')
        {
            $inactive_sites = [];
            foreach ($sites as $key => $s) {
                if (count($s->guardJobRoster) == 0) {
                    $inactive_sites[] = $s;
                }
            }
            $sts = FetchCustomerSitesWithGuardResource::collection($inactive_sites);
        }else{
            $sts = FetchCustomerSitesWithGuardResource::collection($sites);
        }
        
        $dateRange = getDatesFromRange(dbFormate($request->start),dbFormate($request->end));
        if($dateRange){
            foreach ($dateRange as $key => $value) {
                $record = JobRoster::whereDate('start', $value)
                ->where(function($q){
                    $q->orWhere('shift_type','!=','template');
                    $q->orWhereNull('shift_type');
                    $q->whereNotNull('guard_id');
                })
                ->where('roster_id', $roster_id)->sum('hours');
                $data_arry[dateFormat($value)] = round($record,2);
                $total_count = $total_count + $record;
            }
        }

        $total_count = round($total_count, 2);
    
        // $queryCount = JobRoster::where('start', '>=', $start)->where('start', '<=', $end)
        // ->where('guard_id', '!=',  '')->where('guard_id', '!=',  'NULL')->where('guard_id', '!=', NULL)
        // ->where('publish_status', 0)->where(function($q){
        //     $q->orWhere('shift_type','!=','template');
        //     $q->orWhere('shift_type',Null);
        // })
        // ->where('roster_id', $roster_id)->count();

        $queryCount = JobRoster::where('start', '>=', $start)->where('start', '<=', $end)
        ->where('guard_id', '!=',  '')->where('guard_id', '!=',  'NULL')->where('guard_id', '!=', NULL)
        ->where('publish_status', 0)->where(function($q){
            $q->orWhere('shift_type','!=','template');
            //$q->orWhere('shift_type', '!=', NULL);
        })->where('roster_id', $roster_id)->where('deleted_at', null)->whereNotNull('site_id')->count();

        //$total_count = number_format( $total_count, 2, '.', '' );
        return response()->json(['success' => true, 'data' => $sts, 'days_hours' => $data_arry, 'total_hours' => $total_count, 'unpublish_shift_count' => $queryCount, 'code' => 200]); 
    }
 }
    return response()->json(['success' => false, 'data' => null, 'code' => 404]); 

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

    if($request->type == 'location'){
        $roster_id = $request->roster_id;
        $query = JobRoster::where(function($q) use ($start, $end, $roster_id){
            $q->whereDate('job_rosters.start', '>=', $start)->whereDate('job_rosters.start', '<=', $end)->where('publish_status', 0)->where('roster_id', $roster_id);
        });

        if ($request->has('customer_id') && !empty($request->customer_id)) {
            $query->whereIn('sites.customer_id', $request->customer_id);
        }
        if ($request->has('state')) {
            $query->where('sites.state', $request->state);
        }
        if ($request->has('site_id')) {
            $query->where('sites.id', $request->site_id);
        }
        $query->join('sites', 'job_rosters.site_id', '=', 'sites.id');
        $query->join('guards', 'job_rosters.guard_id', '=', 'guards.id'
        )->select('job_rosters.*','guards.first_name', 'guards.middle_name', 'guards.last_name', 'sites.site_name');

        $sts = $query->get();
        $sts = FetchCustomerUnpublishSitesResource::collection($sts);
        }else{
            // Guard type
            $roster_id = $request->roster_id;
            $query = JobRoster::where(function($q) use ($start, $end, $roster_id){
                $q->whereDate('job_rosters.start', '>=', $start)->whereDate('job_rosters.start', '<=', $end)->where('publish_status', 0)->where('roster_id', $roster_id);
            });
            if ($request->has('customer_id') && !empty($request->customer_id)) {
                $query->whereIn('sites.customer_id', $request->customer_id);
            }
            if ($request->has('state')) {
                $query->where('guards.state', $request->state);
            }
            // if ($request->has('site_id')) {
            //     $query->where('sites.id', $request->site_id);
            // }
            $query->join('sites', 'job_rosters.site_id', '=', 'sites.id');
            $query->join('guards', 'job_rosters.guard_id', '=', 'guards.id'
            )->select('job_rosters.*','guards.first_name', 'guards.middle_name', 'guards.last_name', 'sites.site_name');
            $sts = $query->get();
            $sts = FetchCustomerUnpublishSitesResource::collection($sts);
        }
        $queryCount = JobRoster::whereDate('start', '>=', $start)->whereDate('start', '<=', $end)->where('publish_status', 0)->count();

        $dateRange = getDatesFromRange($start,$end);
        if($dateRange){
            foreach ($dateRange as $key => $value) {
                $record = JobRoster::whereDate('start', $value)->get()->sum('hours');
                $data_arry[dateFormat($value)]=$record;
                $total_count = $total_count + $record;
            }
        }
        return response()->json(['success' => true, 'data' => $sts, 'unpublish_shift_count' => $queryCount,
        'days_hours' => $data_arry, 'total_hours' => $total_count, 'code' => 200]);
    }
}




public function editJobRoster(Request $request)
{
 $jobRoster = JobRoster::where('id', $request->id)->with(['jobRosterTask', 'guardz'])->first();
 if($jobRoster){
    $jobrt = (new EditJobRoster($jobRoster));
    return response()->json(['success' => true,'code' => 200 , 'data' => $jobrt]);
 }else{
    return response()->json(['success' => false,'code' => 404 , 'msg' => 'Shift Not Found!']);
 }
 
}


public function deleteShift(Request $request)
{
  $shift  = JobRoster::where('id', $request->id)->first();

//   $old_data = $shift;
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
        $this->send_push_notification_test($notificaion);
        // sendSmsToGuard($guard->phone, 'Hi '.$guard->first_name.' '.$guard->last_name.' '. 'One of your shift has been deleted!'. ' '.$date);
    }
    removeConflictOnDeleteShift($shift->start, $shift->end, $shift->roster_id, $shift->guard_id);
    # REMOVE CONFILICT FROM THE SHIFT
    $getShiftWithConflicts = JobRoster::where('conflicted_with', $shift->id)->get();
    if($getShiftWithConflicts){
        foreach($getShiftWithConflicts as $getShiftWithConflict){
            $getShift = JobRoster::find($getShiftWithConflict['id']);
            $getShift->conflicted_with = null;
            $getShift->conflict = null;
            $getShift->conf_start = null;
            $getShift->conf_end = null;
            $getShift->update();
        }
    }
    DB::table('job_roster_activites')->where(['guard_id'=> $shift->guard_id, 'job_roster_id'=>$request->id])->delete();
    DB::table('incident_reports')->where(['roster_id'=> $shift->id])->delete();
    DB::table('job_roster_tasks')->where(['job_roster_id'=> $shift->id])->delete();
    DB::table('job_breaks')->where(['roster_id'=> $shift->id])->delete();
    DB::table('welfare_call_data')->where(['job_roster_id'=> $shift->id])->delete();
    DB::table('green_call')->where(['job_id'=> $shift->id])->delete();
    jobRosterActions($request->admin_id, 'delete_shift', $shift->id, 'job_roster', $shift);
    $shift->reason = $request->reason;
    $shift->deleted_by = $request->admin_id;
    $shift->update();
    JobRoster::find($request->id)->delete();
    $admin_name = getAdminName($request->admin_id);
    $currnet_time = time();
    shiftCompleteActivity($shift->id, $admin_name. ' Delete this Shift', 'delete_shift', $shift->id, $currnet_time, $request->admin_id);
    return response()->json(['message' => "Shift Deleted" ,  'code' => 200, 'success' => true]);
}else{
   return response()->json(['message' => "Shift Not Found" ,  'code' => 404, 'success' => false],404);
}

}

public function updateRosterTime(Request $request)
{
    

    $jobRosterTime = JobRoster::where('id', $request->id)->first();
    $old_data = $jobRosterTime;
    $hours = $this->getShiftHours(dbFormateDateTime($request->start), dbFormateDateTime($request->end), $jobRosterTime->site_id, $jobRosterTime->continuation);
    $guardWorkingHours =  calCulateGuardWeekHours(dbFormateDateTime($request->start),dbFormateDateTime($request->end));
    if($jobRosterTime){
        $jobRosterTime->start = dbFormateDateTime($request->start);
        $jobRosterTime->end = dbFormateDateTime($request->end);
        $jobRosterTime->hours = $guardWorkingHours;
        $jobRosterTime->morning_hours = (!empty($hours['morning']) ? $hours['morning'] : 0.0);
        $jobRosterTime->night_hours = (!empty($hours['night']) ? $hours['night'] : 0.0);
        $jobRosterTime->saturday_morning_hours = (!empty($hours['saturday_morning']) ? $hours['saturday_morning'] : 0.0);
        $jobRosterTime->saturday_night_hours = (!empty($hours['saturday_night']) ? $hours['saturday_night'] : 0.0);
        $jobRosterTime->sunday_morning_hours = (!empty($hours['sunday_morning']) ? $hours['sunday_morning'] : 0.0);
        $jobRosterTime->sunday_night_hours = (!empty($hours['sunday_night']) ? $hours['sunday_night'] : 0.0);
        $jobRosterTime->ph_morning_hours = (!empty($hours['ph_morning']) ? $hours['ph_morning'] : 0.0);
        $jobRosterTime->ph_night_hours = (!empty($hours['ph_night']) ? $hours['ph_night'] : 0.0);
        $jobRosterTime->update();
        $check = checkGuardShiftTimingUpdate($request->start, $request->end, $jobRosterTime->guard_id, $jobRosterTime->roster_id);
        $jobRosterTime->conf_start = (!empty($check['start']) ? dbFormateDateTime($check['start']) : '');
        $jobRosterTime->conf_end = (!empty($check['end']) ? dbFormateDateTime($check['end']) : '');
        $jobRosterTime->conflict =  (!empty($check['conf']) ? $check['conf'] : '');
        $jobRosterTime->update();
        $updatedjobRosterTime = $jobRosterTime->getChanges();
        jobRosterActions($request->admin_id,'update_shift_time',$jobRosterTime->id, 'job_roster',$old_data, $updatedjobRosterTime);

        $admin_name = getAdminName($request->admin_id);
        $currnet_time = time();
        shiftCompleteActivity($jobRosterTime->id, $admin_name. ' Update Time of this Shift', 'update_shift_time', $jobRosterTime->id, $currnet_time, $request->admin_id);

        return response()->json(['message' => "Shift Time Updated" ,  'code' => 200, 'success' => true]);
    }else{
        return response()->json(['message' => "Shift not Found!" ,  'code' => 404, 'success' => false]);
    }
}
# OLD
// public function updateShift(Request $request){
//     $updateShift = JobRoster::where('id', $request->id)->first();
//     $old_data = $updateShift;
//     $cus_payrate = '';
//     $cus_chargerate = '';

//     if($updateShift){

//         // if(checkShiftDayHours($request->start, $request->end, $request->guard_id)){
//         //     return response()->json(['success' => false, 'message' => 'You have already completed eight hours in this Day!']);
//         // }
        
//         if($request->has('guard_id') && (!empty($request->guard_id))){
//             $previous_shift_guard_id = $updateShift->guard_id;
//             $hours = $this->getShiftHours(dbFormateDateTime($request->start), dbFormateDateTime($request->end), $request->site_id);
//             $check2 = checkGuardDocuments($request->guard_id);
//             $guardWorkingHours = calCulateGuardWeekHours(dbFormateDateTime($request->start), dbFormateDateTime($request->end));
//             $guardWorkLimitation = checkGuardWorkLimitation($request->guard_id, $guardWorkingHours);
//             $checkAdmin = checkAdmin($request->admin_id);
            
//             // $updateShift->start = dbFormateDateTime($request->start);
//             // $updateShift->end = dbFormateDateTime($request->end);
//             // $updateShift->update();

//             if($checkAdmin != 'super-admin'){
//                 $diff =  checkShiftDayHours($request->start, $request->end, $request->guard_id);
//             if($diff < 9){
//                return response()->json(['success' => false, 'message' => 'You must rest for eight hours before starting a new shift!']); 
//             }
//             }
//             $check = checkGuardShiftTiming($request->start, $request->end, $request->guard_id, $request->roster_id);
            
//             if($check['status']=='true' && $checkAdmin == 'admin'){
//                 if($check['status']=='true' || !empty($check['start']) || !empty($check['end']) || !empty($check['conf'])){
//                     return response()->json(['success' => false, 'message' => '<b>Sorry, there is a scheduling conflict for this shift, so it has not been created yet.</b>', 'code'=> 404]);
//                 }
//                 if(!empty($check2) && $check2 != 'active'){
//                     return response()->json(['success' => false, 'message' => '<b>Sorry, there is a document conflict for this shift, so it has not been created yet.</b>', 'code'=> 404]);
//                 }
//             }
            
//             if($check['status']=='true' && !isset($request->shift_confirm)){
//                 if($checkAdmin == 'super-admin' || (!empty($check2) && $check2 != 'active')){
//                     return response()->json(['success' => false, 'message' => '<b>Hi, Super Admin this shift has conflict <br> Do you really want to create this shift !</b>', 'code'=> 404]);
//                 }
//             }
//             if($check['status']=='false' || !empty($check['start']) || !empty($check['end']) || !empty($check['conf']) || (!empty($check2) && $check2 != 'active')){

//                 if($request->has('custome_rate') && $request->custome_rate == true){
//                     if($request->has('custome_payrate') && $request->custome_payrate == true){
//                         $cus_payrate = json_encode($request->manualPayRate);
//                     }
//                 }
//                 if($request->has('custome_rate') && $request->custome_rate == true){
//                     if($request->has('custome_chagerate') && $request->custome_chagerate == true){
//                         $cus_chargerate = json_encode($request->manualChargeRate);
//                     }
//                 }
//                 if($updateShift->job_status == 'rejected'){
//                     $updateShift->job_status = 'pending';
//                     $updateShift->rejected_by = null;
//                 }

//                 $updateShift->job_status = $old_data->job_status;
//                 $updateShift->site_id = $request->site_id;
//                 $updateShift->guard_id = ($request->has('guard_id') && !empty($request->guard_id) ? $request->guard_id : '');
//                 $updateShift->start = dbFormateDateTime($request->start);
//                 $updateShift->end = dbFormateDateTime($request->end);
//                 $updateShift->shift_payable = !empty($request->shift_payable) && ($request->has('shift_payable')) ? $request->shift_payable : 'yes';
//                 $updateShift->shift_chargeable = !empty($request->shift_chargeable) && ($request->has('shift_payable')) ? $request->shift_chargeable : 'yes';
//                 $updateShift->custome_rate = ($request->custome_rate == 'on' ? true : false);
//                 $updateShift->payrate_level = $request->payrate_level;
//                 $updateShift->payrate = $request->payrate;
//                 $updateShift->chargerate_level = $request->chargerate_level;
//                 $updateShift->chargerate = $request->chargerate;
//                 $updateShift->un_published_shift = ($request->un_published_shift == 'on' ? true : false);
//                 $updateShift->public_holidays = ($request->public_holidays == 'on' ? true : false);
//                 $updateShift->covid_marshal = ($request->covid_marshal == 'on' ? true : false);
//                 $updateShift->training = ($request->training == 'on' ? true : false);
//                 $updateShift->continuation = ($request->continuation == 'on' ? true : false);
//                 $updateShift->over_time = ($request->over_time == 'on') ? true : false;
//                 $updateShift->over_time_value = ($request->over_time_value) ? $request->over_time_value : 0;
//                 $updateShift->travel_time = ($request->travel_time == 'on') ? true : false;
//                 $updateShift->travel_time_value = ($request->travel_time_value) ? $request->travel_time_value : 0;
//                 $updateShift->shift_create_status = 'pending';
//                 $updateShift->conflict = (!empty($check['conf']) ? $check['conf'] : '');
//                 $updateShift->doc_conf = (!empty($check2) ? $check2 : '');
//                 $updateShift->work_limitaion_conf = (!empty($guardWorkLimitation['difference']) ? $guardWorkLimitation['difference'] : '');
//                 $updateShift->conf_start = (!empty($check['start']) ? dbFormateDateTime($check['start']) : '');
//                 $updateShift->conf_end = (!empty($check['end']) ? dbFormateDateTime($check['end']) : '');
//                 $updateShift->morning_hours = (!empty($hours['morning']) ? roundHours($hours['morning']) : 0.0);
//                 $updateShift->night_hours = (!empty($hours['night']) ? roundHours($hours['night']) : 0.0);
//                 $updateShift->saturday_morning_hours = (!empty($hours['saturday_morning']) ? roundHours($hours['saturday_morning']) : 0.0);
//                 $updateShift->saturday_night_hours = (!empty($hours['saturday_night']) ? roundHours($hours['saturday_night']) : 0.0);
//                 $updateShift->sunday_morning_hours = (!empty($hours['sunday_morning']) ? roundHours($hours['sunday_morning']) : 0.0);
//                 $updateShift->sunday_night_hours = (!empty($hours['sunday_night']) ? roundHours($hours['sunday_night']) : 0.0);
//                 $updateShift->ph_morning_hours = (!empty($hours['ph_morning']) ? roundHours($hours['ph_morning']) : 0.0);
//                 $updateShift->ph_night_hours = (!empty($hours['ph_night']) ? roundHours($hours['ph_night']) : 0.0);
//                 $updateShift->update_status = 1;
//                 $updateShift->hours = roundHours($guardWorkingHours);
//                 $updateShift->last_update = time();
//                 $updateShift->custome_rate = $request->custome_rate;
//                 $updateShift->custome_payrate = $request->custome_payrate;
//                 $updateShift->custome_chagerate = $request->custome_chagerate;
//                 $updateShift->manualPayRate = $cus_payrate;
//                 $updateShift->manualChargeRate = $cus_chargerate;
//                 $updateShift->unprofile_name = $request->unprofile_name;
//                 $updateShift->po_wo = $request->po_wo;
//                 $updateShift->publish_status = $request->publish_status;
//                 $updateShift->job_instrcutions = $request->job_instrcutions;
//                 $updateShift->job_instruction_text = $request->job_instruction_text;
//                 $updateShift->roster_id = $request->roster_id;
//                 $updateShift->save();
//                 if($previous_shift_guard_id != $request->guard_id && isset($guard->notification_token)){
//                     $guard = Guard::where('id', $previous_shift_guard_id)->first();
//                     $prams['message'] = 'Roster for the week '.usaToAusDateTime($request->start).' - '.usaToAusDateTime($request->end).' has been removed.';
//                     $prams['title'] = 'Roster Removed';
//                     $prams['page'] = 'roster';
//                     $prams['notification_token'] = $guard->notification_token;
//                     send_push_notification($prams);
//                 }
//                 if($request->has('publish_status') && $request->publish_status == 1){
//                     if($request->has('guard_id') && !empty($request->guard_id) && isset($guard->notification_token)){
    
//                         $guard = Guard::where('id', $request->guard_id)->first();
//                         $prams['message'] = 'Roster for the week '.usaToAusDateTime($request->start).' - '.usaToAusDateTime($request->end).' has been published.';
//                         $prams['title'] = 'Roster Published';
//                         $prams['page'] = 'roster';
//                         $prams['notification_token'] = $guard->notification_token;
//                         send_push_notification($prams);
    
//                        $prams['message'] = 'Roster for the week '.usaToAusDateTime($request->start).' - '.usaToAusDateTime($request->end).' has been published. Please open app and confirm your roster.';
//                        $prams['subject'] = 'Roster Published';
//                        $prams['email'] = $guard->email;
//                        generalEmails($prams);
    
//                        sendSmsToGuard($guard->phone, 'Hi '.$guard->first_name.' '.$guard->last_name.' '. 'your shift has been published successfully!');
    
//                     }
//                 }
//                 $admin_name = getAdminName($request->admin_id);
//                 $currnet_time = time();
//                 shiftCompleteActivity($updateShift->id, $admin_name. ' Update this Shift', 'update_shift', $updateShift->id, $currnet_time, $request->admin_id);
//                 jobRosterActions($request->admin_id, 'update_shift', $updateShift->id, 'job_roster',  $old_data);
                

//                 if($request->has('job_roster_tasks') && !empty($request->job_roster_tasks)){
//                     foreach ($request->job_roster_tasks as $key => $task) {
//                         $updateTask =  JobRosterTask::where('id', $task['id'])->first();
//                         $old_task = $updateTask;
//                         $is_check = 0; 
//                         if(!$updateTask){
//                             $updateTask =  new JobRosterTask();
//                             $is_check = 1;
//                         }
//                         $updateTask->job_roster_id = $request->id;
//                         $updateTask->task = $task['task'];
//                         $updateTask->task_start = dbFormateDateTime($task['task_start']);
//                         $updateTask->task_end = dbFormateDateTime($task['task_end']);
//                         $updateTask->save();
//                         if($is_check == 1){
//                             jobRosterActions($request->admin_id,'add_shift_tasks', $updateTask->id, 'job_roster_tasks' );
//                         }else{
//                             jobRosterActions($request->admin_id, 'update_shift_tasks', $updateTask->id ,'job_roster_tasks', $old_task);
//                         }
                        
//                     }
//                 }
//                 if(!empty($check['start']) && !empty($check['end']) && !empty($check['conf']) && !empty($check2)){
//                     return response()->json(['success' => true, 'message' => '<b>Conflicted Shift</b>', 'code'=> 200]); 
//                 }elseif(!empty($check['start']) && !empty($check['end']) && !empty($check['conf'])){
//                     return response()->json(['success' => true, 'message' => '<b>Conflicted Shift</b>', 'code'=> 200]);
//                 }else {
//                     return response()->json(['success' => true, 'message' => '<b>Conflicted Shift</b>', 'code'=> 200]);
//                 }

//             }else{
//                 if($request->has('custome_rate') && $request->custome_rate == true){
//                     if($request->has('custome_payrate') && $request->custome_payrate == true){
//                         $cus_payrate = json_encode($request->manualPayRate);
//                     }
//                 }

//                 if($request->has('custome_rate') && $request->custome_rate == true){
//                     if($request->has('custome_chagerate') && $request->custome_chagerate == true){
//                         $cus_chargerate = json_encode($request->manualChargeRate);
//                     }
//                 }

//                 if($updateShift->job_status == 'rejected'){
//                     $updateShift->job_status = 'pending';
//                     $updateShift->rejected_by = null;
//                 }

//                 $updateShift->job_status = $old_data->job_status;

//                 $updateShift->site_id = $request->site_id;
//                 $updateShift->guard_id = ($request->has('guard_id') && !empty($request->guard_id) ? $request->guard_id : '');
//                 $updateShift->start = dbFormateDateTime($request->start);
//                 $updateShift->end = dbFormateDateTime($request->end);
//                 $updateShift->shift_payable = !empty($request->shift_payable) && ($request->has('shift_payable')) ? $request->shift_payable : 'yes';
//                 $updateShift->shift_chargeable = !empty($request->shift_chargeable) && ($request->has('shift_payable')) ? $request->shift_chargeable : 'yes';
//                 $updateShift->custome_rate = ($request->custome_rate == 'on' ? true : false);
//                 $updateShift->payrate_level = $request->payrate_level;
//                 $updateShift->payrate = $request->payrate;
//                 $updateShift->chargerate_level = $request->chargerate_level;
//                 $updateShift->chargerate = $request->chargerate;
//                 $updateShift->un_published_shift = ($request->un_published_shift == 'on' ? true : false);
//                 $updateShift->public_holidays = ($request->public_holidays == 'on' ? true : false);
//                 $updateShift->covid_marshal = ($request->covid_marshal == 'on' ? true : false);
//                 $updateShift->training = ($request->training == 'on' ? true : false);
//                 $updateShift->continuation = ($request->continuation == 'on' ? true : false);
//                 $updateShift->over_time = ($request->over_time == 'on') ? true : false;
//                 $updateShift->over_time_value = ($request->over_time_value) ? $request->over_time_value : 0;
//                 $updateShift->travel_time = ($request->travel_time == 'on') ? true : false;
//                 $updateShift->travel_time_value = ($request->travel_time_value) ? $request->travel_time_value : 0;
//                 $updateShift->shift_create_status = 'pending';
//                 $updateShift->morning_hours = (!empty($hours['morning']) ? roundHours($hours['morning']) : 0.0);
//                 $updateShift->night_hours = (!empty($hours['night']) ? roundHours($hours['night']) : 0.0);
//                 $updateShift->saturday_morning_hours = (!empty($hours['saturday_morning']) ? roundHours($hours['saturday_morning']) : 0.0);
//                 $updateShift->saturday_night_hours = (!empty($hours['saturday_night']) ? roundHours($hours['saturday_night']) : 0.0);
//                 $updateShift->sunday_morning_hours = (!empty($hours['sunday_morning']) ? roundHours($hours['sunday_morning']) : 0.0);
//                 $updateShift->sunday_night_hours = (!empty($hours['sunday_night']) ? roundHours($hours['sunday_night']) : 0.0);
//                 $updateShift->ph_morning_hours = (!empty($hours['ph_morning']) ? roundHours($hours['ph_morning']) : 0.0);
//                 $updateShift->ph_night_hours = (!empty($hours['ph_night']) ? roundHours($hours['ph_night']) : 0.0);
//                 $updateShift->last_update = time();
//                 $updateShift->update_status = 1;
//                 $updateShift->hours = roundHours($guardWorkingHours);
//                 $updateShift->custome_rate = $request->custome_rate;
//                 $updateShift->custome_payrate = $request->custome_payrate;
//                 $updateShift->custome_chagerate = $request->custome_chagerate;
//                 $updateShift->manualPayRate = $cus_payrate;
//                 $updateShift->manualChargeRate = $cus_chargerate;
//                 $updateShift->unprofile_name = $request->unprofile_name;
//                 $updateShift->po_wo = $request->po_wo;
//                 $updateShift->publish_status = $request->publish_status;
//                 $updateShift->job_instrcutions = $request->job_instrcutions;
//                 $updateShift->job_instruction_text = $request->job_instruction_text;
//                 $updateShift->roster_id = $request->roster_id;

//                 $updateShift->conflict = null;
//                 $updateShift->conf_start = null;
//                 $updateShift->conf_end = null;


//                 $updateShift->update();
//                 if($previous_shift_guard_id != $request->guard_id && isset($guard->notification_token)){
//                     $guard = Guard::where('id', $previous_shift_guard_id)->first();
//                     $prams['message'] = 'Roster for the week '.usaToAusDateTime($request->start).' - '.usaToAusDateTime($request->end).' has been removed.';
//                     $prams['title'] = 'Roster Removed';
//                     $prams['page'] = 'roster';
//                     $prams['notification_token'] = $guard->notification_token;
//                     send_push_notification($prams);
//                 }
//                 if($request->has('publish_status') && $request->publish_status == 1){
//                     if($request->has('guard_id') && !empty($request->guard_id) && isset($guard->notification_token)){
    
//                         $guard = Guard::where('id', $request->guard_id)->first();
//                         $prams['message'] = 'Roster for the week '.usaToAusDateTime($request->start).' - '.usaToAusDateTime($request->end).' has been published.';
//                         $prams['title'] = 'Roster Published';
//                         $prams['page'] = 'roster';
//                         $prams['notification_token'] = $guard->notification_token;
//                         send_push_notification($prams);
    
//                        $prams['message'] = 'Roster for the week '.usaToAusDateTime($request->start).' - '.usaToAusDateTime($request->end).' has been published. Please open app and confirm your roster.';
//                        $prams['subject'] = 'Roster Published';
//                        $prams['email'] = $guard->email;
//                        generalEmails($prams);
    
//                        sendSmsToGuard($guard->phone, 'Hi '.$guard->first_name.' '.$guard->last_name.' '. 'your shift has been published successfully!');
    
//                     }
//                 }
//                 jobRosterActions($request->admin_id, 'update_shift', $updateShift->id,'job_roster', $old_data);
//                 $admin_name = getAdminName($request->admin_id);
//                 $currnet_time = time();
//                 shiftCompleteActivity($updateShift->id, $admin_name. ' Update this Shift', 'update_shift', $updateShift->id, $currnet_time, $request->admin_id);

//                 if($request->has('job_roster_tasks') && !empty($request->job_roster_tasks)){
//                     foreach ($request->job_roster_tasks as $key => $task) {
//                         $updateTask =  JobRosterTask::where('id', $task['id'])->first();
//                         $old_task = $updateTask; 
//                         $is_check = 0; 
//                         if(!$updateTask){
//                             $updateTask =  new JobRosterTask();
//                             $is_check = 1;     
//                         }
//                         $updateTask->job_roster_id = $request->id;
//                         $updateTask->task = $task['task'];
//                         $updateTask->task_start = dbFormateDateTime($task['task_start']);
//                         $updateTask->task_end = dbFormateDateTime($task['task_end']);
//                         $updateTask->save();

//                         if($is_check == 1){
//                             jobRosterActions($request->admin_id, 'add_shift_tasks', $updateTask->id, 'job_roster_tasks');
//                         }else{
//                             jobRosterActions($request->admin_id, 'update_shift_tasks', $updateTask->id,'job_roster_tasks', $old_task);
//                         }

                        
//                     }
//                 }
//                 return response()->json(['success' => true, 'message' => 'Shift updated at the'.' '.getRosterName($request->roster_id), 'code' => 200]);
//             }
//         }else{
//             $guardWorkingHours = calCulateGuardWeekHours(dbFormateDateTime($request->start), dbFormateDateTime($request->end));

//             $hours = $this->getShiftHours(dbFormateDateTime($request->start), dbFormateDateTime($request->end), $request->site_id);

//             if($request->has('custome_rate') && $request->custome_rate == true){
//                 if($request->has('custome_payrate') && $request->custome_payrate == true){
//                     $cus_payrate = json_encode($request->manualPayRate);
//                 }
//             }

//             if($request->has('custome_rate') && $request->custome_rate == true){
//                 if($request->has('custome_chagerate') && $request->custome_chagerate == true){
//                     $cus_chargerate = json_encode($request->manualChargeRate);
//                 }
//             }
//             //$updateShift->job_status = $old_data->job_status;
//             $updateShift->job_status = 'pending';
//             $updateShift->site_id = $request->site_id;
//             $updateShift->guard_id = ($request->has('guard_id') && !empty($request->guard_id) ? $request->guard_id : '');
//             $updateShift->start = dbFormateDateTime($request->start);
//             $updateShift->end = dbFormateDateTime($request->end);
//             $updateShift->shift_payable = !empty($request->shift_payable) && ($request->has('shift_payable')) ? $request->shift_payable : 'yes';
//             $updateShift->shift_chargeable = !empty($request->shift_chargeable) && ($request->has('shift_payable')) ? $request->shift_chargeable : 'yes';
//             $updateShift->custome_rate = ($request->custome_rate == 'on' ? true : false);
//             $updateShift->payrate_level = $request->payrate_level;
//             $updateShift->payrate = $request->payrate;
//             $updateShift->chargerate_level = $request->chargerate_level;
//             $updateShift->chargerate = $request->chargerate;
//             $updateShift->un_published_shift = ($request->un_published_shift == 'on' ? true : false);
//             $updateShift->public_holidays = ($request->public_holidays == 'on' ? true : false);
//             $updateShift->covid_marshal = ($request->covid_marshal == 'on' ? true : false);
//             $updateShift->training = ($request->training == 'on' ? true : false);
//             $updateShift->continuation = ($request->continuation == 'on' ? true : false);
//             $updateShift->over_time = ($request->over_time == 'on') ? true : false;
//             $updateShift->over_time_value = ($request->over_time_value) ? $request->over_time_value : 0;
//             $updateShift->travel_time = ($request->travel_time == 'on') ? true : false;
//             $updateShift->travel_time_value = ($request->travel_time_value) ? $request->travel_time_value : 0;
//             $updateShift->shift_create_status = 'pending';
//             $updateShift->morning_hours = (!empty($hours['morning']) ? roundHours($hours['morning']) : 0.0);
//             $updateShift->night_hours = (!empty($hours['night']) ? roundHours($hours['night']) : 0.0);
//             $updateShift->saturday_morning_hours = (!empty($hours['saturday_morning']) ? roundHours($hours['saturday_morning']) : 0.0);
//             $updateShift->saturday_night_hours = (!empty($hours['saturday_night']) ? roundHours($hours['saturday_night']) : 0.0);
//             $updateShift->sunday_morning_hours = (!empty($hours['sunday_morning']) ? roundHours($hours['sunday_morning']) : 0.0);
//             $updateShift->sunday_night_hours = (!empty($hours['sunday_night']) ? roundHours($hours['sunday_night']) : 0.0);
//             $updateShift->ph_morning_hours = (!empty($hours['ph_morning']) ? roundHours($hours['ph_morning']) : 0.0);
//             $updateShift->ph_night_hours = (!empty($hours['ph_night']) ? roundHours($hours['ph_night']) : 0.0);
//             $updateShift->last_update = time();
//             $updateShift->update_status = 1;
//             $updateShift->hours = roundHours($guardWorkingHours);
//             $updateShift->custome_rate = $request->custome_rate;
//             $updateShift->custome_payrate = $request->custome_payrate;
//             $updateShift->custome_chagerate = $request->custome_chagerate;
//             $updateShift->manualPayRate = $cus_payrate;
//             $updateShift->manualChargeRate = $cus_chargerate;
//             $updateShift->unprofile_name = $request->unprofile_name;
//             $updateShift->po_wo = $request->po_wo;
//             $updateShift->job_instrcutions = $request->job_instrcutions;
//             $updateShift->job_instruction_text = $request->job_instruction_text;
//             $updateShift->roster_id = $request->roster_id;
//             $updateShift->conflict = null;
//             $updateShift->conf_start = null;
//             $updateShift->conf_end = null;
//             $updateShift->save();
//             jobRosterActions($request->admin_id, 'update_shift', $updateShift->id, 'job_roster', $old_data);

//             $admin_name = getAdminName($request->admin_id);
//             $currnet_time = time();
//             shiftCompleteActivity($updateShift->id, $admin_name. ' Update this Shift', 'update_shift', $updateShift->id, $currnet_time, $request->admin_id);

//             if($request->has('job_roster_tasks') && !empty($request->job_roster_tasks)){
//                 foreach ($request->job_roster_tasks as $key => $task) {
//                         $updateTask =  JobRosterTask::where('id', $task['id'])->first();
//                         $old_task = $updateTask; 
//                         $is_check = 0; 
//                         if(!$updateTask){
//                             $updateTask =  new JobRosterTask();
//                             $is_check = 1;     
//                         }
//                         $updateTask->job_roster_id = $request->id;
//                         $updateTask->task = $task['task'];
//                         $updateTask->task_start = dbFormateDateTime($task['task_start']);
//                         $updateTask->task_end = dbFormateDateTime($task['task_end']);
//                         $updateTask->save();
//                         if($is_check == 1){
//                             jobRosterActions($request->admin_id, 'add_shift_tasks', $updateTask->id, 'job_roster_tasks');
//                         }else{
//                             jobRosterActions($request->admin_id, 'update_shift_tasks', $updateTask->id,'job_roster_tasks', $old_task);
//                         }
//                     }
//             }
//             return response()->json(['success' => true, 'message' => 'Shift updated at the'.' '.getRosterName($request->roster_id), 'code' => 200]);
//         }
//     }
// }

// public function addNewShiftUpdatedVersion(Request $request){
//     if(isset($request->guard_id) && $request->guard_id > 0){
//         # CHECK DIFFERENCE BETWEEN SHIFTS
//         $checkAdmin = checkAdmin($request->admin_id);
//         if($checkAdmin != 'super-admin'){
//             $diff =  checkShiftDayHours($request->start, $request->end, $request->guard_id);
//             if($diff < 9){
//                 return response()->json(['success' => false, 'message' => 'You must rest for eight hours before starting a new shift!']); 
//             }
//         }
//         # CHECK GUARD DOCS ARE SET AND NOT EXPIRED
//         $checkGuardDocs = true;
//         $guardDetails = GuardWorkDetail::where('guard_id', $request->guard_id)->first();
//         if (empty($guardDetails->guard_document_type)) {
//             // return "Please First Add Your Residential Status!";
//             $checkGuardDocs = false;
//         }
//         $today = strtotime(date("Y/m/d"));
//         $guard = Guard::where('id', $request->guard_id)->with('guardDocuments')->first();
//         foreach ($guard->guardDocuments as $document) {
//             if ($document->c_f_roster == 1) {
//                 if ($document->document_category == 'citizen') {
//                     if ($document->document_type == 'security_license' &&
//                         ($document->document_expire === '' || $document->document_expire === null || $today > strtotime($document->document_expire))) {
//                         // return "Security License Expired!";
//                         $checkGuardDocs = false;
//                     } else {
//                         // return 'active';
//                         $checkGuardDocs = true;
//                     }
//                 } else {
//                     if (in_array($document->document_type, ['visa', 'passport', 'security_license']) &&
//                         ($document->document_expire === '' || $document->document_expire === null || $today > strtotime($document->document_expire))) {
//                         // return ucfirst($document->document_type) . " Expired!";
//                         $checkGuardDocs = false;
//                     }
//                 }
//             }
//         }
//         if ($checkGuardDocs == false && !isset($request->shift_confirm)) {
//             if($checkAdmin == 'super-admin'){
//                 return response()->json(['data'=>$checkGuardDocs,'success' => false, 'message' => '<b>Hi1, Super Admin this shift has document expired or not updated <br> Do you really want to create this shift !</b>', 'code'=> 404]);
//             }
//             if($checkAdmin == 'admin'){
//                 return response()->json(['success' => false, 'message' => '<bHi Admin, this guard has document expired or not updated so you cant create a shift!</b>', 'code'=> 404]);
//             }
//         }
//         # CHECK SHIFT CONFILICT
//         $start_time = dbFormateDateTime($request->start);
//         $end_time = dbFormateDateTime($request->end);
//         $conflictingShift = JobRoster::where('guard_id', $request->guard_id)
//         ->where(function ($query) use ($start_time, $end_time) {
//             $query->where(function ($q) use ($start_time) {
//                 $q->where('start', '<=', $start_time)->where('end', '>', $start_time);
//             })->orWhere(function ($q) use ($end_time) {
//                 $q->where('start', '<=', $end_time)->where('end', '>=', $end_time);
//             })->orWhere(function ($q) use ($start_time, $end_time) {
//                 $q->where('start', '>=', $start_time)->where('end', '<=', $end_time);
//             });
//         })
//         ->select('id', 'start', 'end')->first();
//         // return $conflictingShift;
//         if ($conflictingShift && !isset($request->shift_confirm)) {
//             if($checkAdmin == 'super-admin'){
//                 return response()->json(['data'=>$conflictingShift,'success' => false, 'message' => '<b>Hi2, Super Admin this shift has conflict <br> Do you really want to create this shift !</b>', 'code'=> 404]);
//             }
//             if($checkAdmin == 'admin'){
//                 return response()->json(['success' => false, 'message' => '<bHi Admin, this shift has conflict so you cant create a shift!</b>', 'code'=> 404]);
//             }
//         }
//         # CHECK GUARD WORK LIMITATION
//         $guardWorkingHours = calCulateGuardWeekHours(dbFormateDateTime($request->start), dbFormateDateTime($request->end));
//         $w_l_h = '';
//         $now = Carbon::now();
//         $weekStartDate = $now->startOfWeek()->toDateString();
//         $weekEndDate = $now->endOfWeek()->toDateString();
//         $guardOnLimitations = Guard::where('id', $request->guard_id)->first();

//         if ($guardOnLimitations->work_limitation_status == 1) {
//             $w_l_h = $guardOnLimitations->weekly_work_hours_limitation ?? 40;
//         }

//         $sumOfOneWeekHour = JobRoster::where('guard_id', $request->guard_id)
//             ->where(function ($query) use ($weekStartDate, $weekEndDate) {
//                 $query->whereBetween('start', [$weekStartDate, $weekEndDate])
//                     ->orWhereBetween('end', [$weekStartDate, $weekEndDate]);
//             })
//             ->sum('total_week_hours');

//         if (!empty($sumOfOneWeekHour) && !empty($w_l_h)) {
//             $sum = $sumOfOneWeekHour + $guardWorkingHours;
//             if ($sum > $w_l_h) {
//                 $difference = $sum - $w_l_h - $guardWorkingHours;
//                 $message = 'You cannot create a shift because you exceed your work limitations.';
//                 return response()->json([
//                     'success' => false,
//                     'message' => $message,
//                     'data' => $difference,
//                 ]);
//             }
//         } elseif (!empty($guardOnLimitations->weekly_work_hours_limitation) && $guardOnLimitations->weekly_work_hours_limitation < $guardWorkingHours) {
//             $sum = $sumOfOneWeekHour + $guardWorkingHours;

//             if ($sum > $w_l_h) {
//                 $difference = $sum - $w_l_h;
//                 $message = 'You cannot create a shift because you exceed your work limitations.';
//                 return response()->json([
//                     'success' => false,
//                     'message' => $message,
//                     'data' => $difference,
//                 ]);
//             }
//         }
//     }

//     # CALCULATE CUSTOM PAYRATE AND CHARGE RATE IF SET IN SHIFT
//     $cus_payrate = '';
//     $cus_chargerate = '';
//     if($request->has('custome_rate') && $request->custome_rate == true){
//         if($request->has('custome_payrate') && $request->custome_payrate == true){
//             $cus_payrate = json_encode($request->manualPayRate);
//         }
//     }
//     if($request->has('custome_rate') && $request->custome_rate == true){
//         if($request->has('custome_chagerate') && $request->custome_chagerate == true){
//             $cus_chargerate = json_encode($request->manualChargeRate);
//         }
//     }
//     # CALCULATE GUARD SHIFT AND WORKING HOURS
//     $hours = $this->getShiftHours(dbFormateDateTime($request->start), dbFormateDateTime($request->end), $request->site_id);
//     $guardWorkingHours = calCulateGuardWeekHours(dbFormateDateTime($request->start), dbFormateDateTime($request->end));

//     if(isset($request->id)){
//         $addNewShift = JobRoster::findOrFail($request->id);
//         $flagUpdateShift = 1;
//     }else{
//         $addNewShift = new JobRoster();
//         $flagUpdateShift = 0;
//     }
//     $addNewShift->site_id = $request->site_id;
//     $addNewShift->guard_id = (isset($request->guard_id) && !empty($request->guard_id)) ? $request->guard_id : null;
//     $addNewShift->start = dbFormateDateTime($request->start);
//     $addNewShift->end = dbFormateDateTime($request->end);
//     $addNewShift->shift_payable = !empty($request->shift_payable) && ($request->has('shift_payable')) ? $request->shift_payable : 'yes';
//     $addNewShift->shift_chargeable = !empty($request->shift_chargeable) && ($request->has('shift_payable')) ? $request->shift_chargeable : 'yes';
//     $addNewShift->custome_rate = ($request->custome_rate == 'on' ? true : false);
//     $addNewShift->payrate_level = $request->payrate_level;
//     $addNewShift->payrate = $request->payrate;
//     $addNewShift->chargerate_level = $request->chargerate_level;
//     $addNewShift->chargerate = $request->chargerate;
//     $addNewShift->un_published_shift = ($request->un_published_shift == 'on' ? true : false);
//     $addNewShift->public_holidays = ($request->public_holidays == 'on' ? true : false);
//     $addNewShift->covid_marshal = ($request->covid_marshal == 'on' ? true : false);
//     $addNewShift->training = ($request->training == 'on' ? true : false);
//     $addNewShift->continuation = ($request->continuation == 'on' ? true : false);
//     $addNewShift->over_time = ($request->over_time == 'on') ? true : false;
//     $addNewShift->over_time_value = ($request->over_time_value) ? $request->over_time_value : 0;
//     $addNewShift->travel_time = ($request->travel_time == 'on') ? true : false;
//     $addNewShift->travel_time_value = ($request->travel_time_value) ? $request->travel_time_value : 0;
//     $addNewShift->shift_create_status = 'pending';
//     $addNewShift->total_week_hours = $guardWorkingHours;
//     $addNewShift->shift_type = ($request->has('shift_type') && !empty($request->shift_type) ? $request->shift_type : '');
//     $addNewShift->conflict = (!empty($conflictingShift) ? 'conflict' : null);
//     $addNewShift->doc_conf = ($checkGuardDocs == false ? 'conflict' : null);
//     $addNewShift->work_limitaion_conf = (!empty($guardWorkLimitation['difference']) ? $guardWorkLimitation['difference'] : '');
//     $addNewShift->conf_start = (!empty($conflictingShift) ? dbFormateDateTime($conflictingShift->start) : '');
//     $addNewShift->conf_end = (!empty($conflictingShift) ? dbFormateDateTime($conflictingShift->end) : '');
//     $addNewShift->morning_hours = (!empty($hours['morning']) ? roundHours($hours['morning']) : 0.0);
//     $addNewShift->night_hours = (!empty($hours['night']) ? roundHours($hours['night']) : 0.0);
//     $addNewShift->saturday_morning_hours = (!empty($hours['saturday_morning']) ? roundHours($hours['saturday_morning']) : 0.0);
//     $addNewShift->saturday_night_hours = (!empty($hours['saturday_night']) ? roundHours($hours['saturday_night']) : 0.0);
//     $addNewShift->sunday_morning_hours = (!empty($hours['sunday_morning']) ? roundHours($hours['sunday_morning']) : 0.0);
//     $addNewShift->sunday_night_hours = (!empty($hours['sunday_night']) ? roundHours($hours['sunday_night']) : 0.0);
//     $addNewShift->ph_morning_hours = (!empty($hours['ph_morning']) ? roundHours($hours['ph_morning']) : 0.0);
//     $addNewShift->ph_night_hours = (!empty($hours['ph_night']) ? roundHours($hours['ph_night']) : 0.0);
//     $addNewShift->last_update = time();
//     $addNewShift->hours = roundHours($guardWorkingHours);
//     $addNewShift->publish_status = $request->publish_status;
//     $addNewShift->custome_rate = $request->custome_rate;
//     $addNewShift->custome_payrate = $request->custome_payrate;
//     $addNewShift->custome_chagerate = $request->custome_chagerate;
//     $addNewShift->manualPayRate = $cus_payrate;
//     $addNewShift->manualChargeRate = $cus_chargerate;
//     $addNewShift->unprofile_name = $request->unprofile_name;
//     $addNewShift->po_wo = $request->po_wo;
//     $addNewShift->job_instrcutions = $request->job_instrcutions;
//     $addNewShift->job_instruction_text = $request->job_instruction_text;
//     $addNewShift->roster_id = $request->roster_id;
//     $addNewShift->save();
//     if($request->publish_status == 1 && isset($request->guard_id) && $request->guard_id > 0){
//         if($request->has('guard_id') && !empty($request->guard_id)){
//             # SEND MAIL
//             $guard = Guard::where('id', $request->guard_id)->first();
//             $prams['message'] = 'Roster for the week '.usaToAusDateTime($request->start).' - '.usaToAusDateTime($request->end).' has been published. Please open app and confirm your roster.';
//             $prams['subject'] = 'Roster Published';
//             $prams['email'] = $guard->email;
//             generalEmails($prams);
//             # SEND NOTIFICATION IF TOKEN EXIST
//             if(isset($guard->notification_token)){
//                 $prams['message'] = 'Roster for the week '.usaToAusDateTime($request->start).' - '.usaToAusDateTime($request->end).' has been published.';
//                 $prams['title'] = 'Roster Published';
//                 $prams['page'] = 'roster';
//                 $prams['notification_token'] = $guard->notification_token;
//                 send_push_notification($prams);
//             }
//         }
//         sendSmsToGuard($guard->phone, 'Hi '.$guard->first_name.' '.$guard->last_name.' '. 'your shift has been published successfully!');
//     }
//     jobRosterActions($request->admin_id, 'add_shift', $addNewShift->id, 'job_roster');
//     $admin_name = getAdminName($request->admin_id);
//     $currnet_time = time();
//     shiftCompleteActivity($addNewShift->id, $admin_name. ' Added this Shift', 'add_shift', $addNewShift->id, $currnet_time, $request->admin_id);
//     # SAVE TASK

//     if($request->has('job_roster_tasks') && !empty($request->job_roster_tasks)){
//         if($flagUpdateShift == 1){
//             foreach ($request->job_roster_tasks as $key => $task) {
//                 $updateTask =  JobRosterTask::where('id', $task['id'])->first();
//                 $old_task = $updateTask; 
//                 $is_check = 0; 
//                 if(!$updateTask){
//                     $updateTask =  new JobRosterTask();
//                     $is_check = 1;     
//                 }
//                 $updateTask->job_roster_id = $request->id;
//                 $updateTask->task = $task['task'];
//                 $updateTask->task_start = dbFormateDateTime($task['task_start']);
//                 $updateTask->task_end = dbFormateDateTime($task['task_end']);
//                 $updateTask->save();
//                 if($is_check == 1){
//                     jobRosterActions($request->admin_id, 'add_shift_tasks', $updateTask->id, 'job_roster_tasks');
//                 }else{
//                     jobRosterActions($request->admin_id, 'update_shift_tasks', $updateTask->id,'job_roster_tasks', $old_task);
//                 }
//             }
//         }else{
//             foreach ($request->job_roster_tasks as $key => $task) {
//                 $newTask =  new JobRosterTask();
//                 $newTask->job_roster_id = $addNewShift->id;
//                 $newTask->task = $task['task'];
//                 $newTask->task_start = dbFormateDateTime($task['task_start']);
//                 $newTask->task_end = dbFormateDateTime($task['task_end']);
//                 $newTask->save();
//                 jobRosterActions($request->admin_id, 'add_shift_tasks', $newTask->id,'job_roster_tasks');
//             }
//         }
//     }
//     if(($flagUpdateShift == 1) && (empty($conflictingShift) || $checkGuardDocs == false)){
//         $addNewShift->conflict = null;
//         $addNewShift->conf_start = null;
//         $addNewShift->conf_end = null;
//         $addNewShift->update();
//     }
//     if(!empty($conflictingShift) || $checkGuardDocs == false){
//         return response()->json(['success' => true, 'message' => '<b>Conflicted Shift Created!</b>', 'code'=> 200]);
//     }
//     return response()->json([
//         'success' => true,
//         'message' => 'Normal Shift Created.'
//     ]);
// }

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
            $diff =  checkShiftDayHours($request->start, $request->end, $request->guard_id, $request->id);
            if($diff > 9){
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
                return response()->json(['data'=>$checkGuardDocs,'success' => false, 'message' => '<b>Hi1, Super Admin this shift has document expired or not updated <br> Do you really want to create this shift !</b>', 'code'=> 404]);
            }
            if($checkAdmin == 'admin'){
                return response()->json(['success' => false, 'message' => '<bHi Admin, this guard has document expired or not updated so you cant create a shift!</b>', 'code'=> 404]);
            }
        }
        # CHECK SHIFT CONFILICT
        if(isset($request->id) && $request->id > 0){
            $addNewShift = JobRoster::findOrFail($request->id);
            $start_time = dbFormateDateTime($request->start);
            $end_time = dbFormateDateTime($request->end);
            $conflictingShift = JobRoster::where('guard_id', $request->guard_id)
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
            $conflictingShift = JobRoster::where('guard_id', $request->guard_id)
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

        $sumOfOneWeekHour = JobRoster::where('guard_id', $request->guard_id)
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
    $start_time = Carbon::createFromFormat("m-d-Y H:i", $request->start);
    $start_time_hours_minutes = $start_time->format('H:i');
    if($start_time_hours_minutes === '23:59'){
        $start_time->addMinutes(1);
        $modified_time_str = $start_time->format("m-d-Y H:i");
    }else{
        $modified_time_str = $request->start;
    }
    $end_time = Carbon::createFromFormat("m-d-Y H:i", $request->end);
    $end_time_hours_minutes = $end_time->format('H:i');
    if($end_time_hours_minutes === '23:59'){
        $end_time->addMinutes(1);
        $modified_time_end = $end_time->format("m-d-Y H:i");
    }else{
        $modified_time_end = $request->end;
    }
    $timestamp_start = Carbon::parse(dbFormateDateTime($modified_time_str));
    $timestamp_start_dst = Carbon::parse(dbFormateDateTime($modified_time_str))->isDST();
    $timestamp_end = Carbon::parse(dbFormateDateTime($modified_time_end));
    $timestamp_end_dst = Carbon::parse(dbFormateDateTime($modified_time_end))->isDST();
    if ($timestamp_start_dst && !$timestamp_end_dst) {
        // DST is active at the start but not at the end
        $dst_hours = $timestamp_start->diffInHours($timestamp_end) - 1;
        $modified_time_end = $timestamp_end->subHour();
    } elseif (!$timestamp_start_dst && $timestamp_end_dst) {
        // DST is active at the end but not at the start
        $dst_hours = $timestamp_start->diffInHours($timestamp_end) + 1;
        $modified_time_end = $timestamp_end->addHour();
    } else {
        // DST status is the same at both start and end timestamps
        $dst_hours = calCulateGuardWeekHours(dbFormateDateTime($modified_time_str), dbFormateDateTime($modified_time_end));
    }
    // $total = $result->hours;
    // $total = $dst_hours;
    // $guardWorkingHours = calCulateGuardWeekHours(dbFormateDateTime($modified_time_str), dbFormateDateTime($modified_time_end));
    $guardWorkingHours = $dst_hours;
    // $guardWorkingHours = calCulateGuardWeekHours(dbFormateDateTime($modified_time_str), dbFormateDateTime($modified_time_end));
    $continuation = false;
    if(isset($request->continuation) && $request->continuation == true){
        $continuation = true;
    }
    # CALCULATE GUARD SHIFT AND WORKING HOURS
    $hours = $this->getShiftHours(dbFormateDateTime($modified_time_str), dbFormateDateTime($modified_time_end), $request->site_id, $continuation);
    if(isset($request->id)){
        $addNewShift = JobRoster::findOrFail($request->id);
        $old_data = $addNewShift;
        
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
            $removeConflict = JobRoster::where('conflicted_with', $request->id)->get();
            if($removeConflict){
                foreach($removeConflict as $shift){
                    $updateShift = JobRoster::find($shift['id']);
                    $updateShift->conflicted_with = null;
                    $updateShift->conflict = null;
                    $updateShift->conf_start = null;
                    $updateShift->conf_end = null;
                    $updateShift->update();
                }
            }
        }

        // if($request->guard_id == null){
        //     $removeNotes = JobRoster::where('id', $request->id)->where('operation_notes', '!=',  NULL)->first();
        //     if($removeNotes){
        //             $removeNotes->operation_notes = null;
        //             $removeNotes->update();
                
        //     }
        // }

        $flagUpdateShift = 1;
    }else{
        $addNewShift = new JobRoster();
        $flagUpdateShift = 0;
    }
    $addNewShift->site_id = $request->site_id;
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
    $addNewShift->reimbursement = ($request->reimbursement == 'on') ? true : false;
    $addNewShift->reimbursement_text = $request->reimbursement_text;
    $addNewShift->reimbursement_value = $request->reimbursement_value;
    $addNewShift->shift_create_status = 'pending';
    $addNewShift->total_week_hours = $guardWorkingHours;
    $addNewShift->shift_type = ($request->has('shift_type') && !empty($request->shift_type) ? $request->shift_type : '');
    $addNewShift->conflict = ((isset($conflictingShift) && !empty($conflictingShift)) ? 'conflict in '.getRosterdName($request->roster_id) : null);
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
    if($request->publish_status == 1){
        $addNewShift->publish_status = $request->publish_status;
    }
    $addNewShift->custome_rate = $request->custome_rate;
    $addNewShift->custome_payrate = $request->custome_payrate;
    $addNewShift->custome_chagerate = $request->custome_chagerate;
    $addNewShift->manualPayRate = $cus_payrate;
    $addNewShift->manualChargeRate = $cus_chargerate;
    $addNewShift->unprofile_name = isset($request->guard_id) ? null : $request->unprofile_name;
    $addNewShift->po_wo = $request->po_wo;
    $addNewShift->job_instrcutions = $request->job_instrcutions;
    $addNewShift->job_instruction_text = $request->job_instruction_text;
    // $addNewShift->roster_id = $request->roster_id;
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

    $roster_changes = $addNewShift->getChanges();
    $tableDefaults = [
        'reimbursement' => false,
        'un_published_shift' => false,
        'public_holidays' => false,
        'covid_marshal' => false,
        'training' => false,
        'continuation' => false,
        'over_time' => false,
        'travel_time' => false,
        'conf_start' => '',
        'conf_end' => '',
        'custome_rate' => false,
        'custome_payrate' => false,
        'custome_chagerate' => true,
    ];
    
    $modified_changes = array_diff_assoc($roster_changes, $tableDefaults);
    
    jobRosterActions($request->admin_id, 'update_shift', $addNewShift->id, 'job_roster', $old_data, $modified_changes);
    $admin_name = getAdminName($request->admin_id);
    $currnet_time = time();
    shiftCompleteActivity($addNewShift->id, $admin_name. ' Update this Shift', 'update_shift', $addNewShift->id, $currnet_time, $request->admin_id);
    # SAVE TASK

    if($request->has('job_roster_tasks') && !empty($request->job_roster_tasks)){
        if($flagUpdateShift == 1){
            foreach ($request->job_roster_tasks as $key => $task) {
                $updateTask =  JobRosterTask::where('id', $task['id'])->first();
                $old_task = $updateTask; 
                $is_check = 0; 
                if(!$updateTask){
                    $updateTask =  new JobRosterTask();
                    $is_check = 1;     
                }
                $updateTask->job_roster_id = $request->id;
                $updateTask->task = $task['task'];
                $updateTask->task_start = dbFormateDateTime($task['task_start']);
                $updateTask->task_end = dbFormateDateTime($task['task_end']);
                $updateTask->save();
                if($is_check == 1){
                    jobRosterActions($request->admin_id, 'add_shift_tasks', $updateTask->id, 'job_roster_tasks');
                }else{
                    $task_changes = $updateTask->getchanges();
                    jobRosterActions($request->admin_id, 'update_shift_tasks', $updateTask->id,'job_roster_tasks', $old_task, $task_changes);
                }
            }
        }else{
            foreach ($request->job_roster_tasks as $key => $task) {
                $newTask =  new JobRosterTask();
                $newTask->job_roster_id = $addNewShift->id;
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
        $fetchConflictedShift = JobRoster::where('conflicted_with', $addNewShift->id)
        ->where('roster_id', $request->roster_id)
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
                    $updateConflict = JobRoster::find($shift['id']);
                    $updateConflict->conflict = null;
                    $updateConflict->conf_start = null;
                    $updateConflict->conf_end = null;
                    $updateConflict->update();
                }else{
                    $updateConflict = JobRoster::find($shift['id']);
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
public function splitUpdateShift($request){
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
            $diff =  checkShiftDayHours($request->start, $request->end, $request->guard_id, $request->id);
            if($diff > 9){
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
                return response()->json(['data'=>$checkGuardDocs,'success' => false, 'message' => '<b>Hi1, Super Admin this shift has document expired or not updated <br> Do you really want to create this shift !</b>', 'code'=> 404]);
            }
            if($checkAdmin == 'admin'){
                return response()->json(['success' => false, 'message' => '<bHi Admin, this guard has document expired or not updated so you cant create a shift!</b>', 'code'=> 404]);
            }
        }
        # CHECK SHIFT CONFILICT
        if(isset($request->id) && $request->id > 0){
            $addNewShift = JobRoster::findOrFail($request->id);
            $start_time = dbFormateDateTime($request->start);
            $end_time = dbFormateDateTime($request->end);
            $conflictingShift = JobRoster::where('guard_id', $request->guard_id)
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
            $conflictingShift = JobRoster::where('guard_id', $request->guard_id)
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

        $sumOfOneWeekHour = JobRoster::where('guard_id', $request->guard_id)
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
    if($request->custome_rate == true){
        if($request->custome_payrate == true){
            $cus_payrate = json_encode($request->manualPayRate);
        }
    }
    if($request->custome_rate == true){
        if($request->custome_chagerate == true){
            $cus_chargerate = json_encode($request->manualChargeRate);
        }
    }
    $start_time = Carbon::createFromFormat("m-d-Y H:i", $request->start);
    $start_time_hours_minutes = $start_time->format('H:i');
    if($start_time_hours_minutes === '23:59'){
        $start_time->addMinutes(1);
        $modified_time_str = $start_time->format("m-d-Y H:i");
    }else{
        $modified_time_str = $request->start;
    }
    $end_time = Carbon::createFromFormat("m-d-Y H:i", $request->end);
    $end_time_hours_minutes = $end_time->format('H:i');
    if($end_time_hours_minutes === '23:59'){
        $end_time->addMinutes(1);
        $modified_time_end = $end_time->format("m-d-Y H:i");
    }else{
        $modified_time_end = $request->end;
    }
    $timestamp_start = Carbon::parse(dbFormateDateTime($modified_time_str));
    $timestamp_start_dst = Carbon::parse(dbFormateDateTime($modified_time_str))->isDST();
    $timestamp_end = Carbon::parse(dbFormateDateTime($modified_time_end));
    $timestamp_end_dst = Carbon::parse(dbFormateDateTime($modified_time_end))->isDST();
    if ($timestamp_start_dst && !$timestamp_end_dst) {
        // DST is active at the start but not at the end
        $dst_hours = $timestamp_start->diffInHours($timestamp_end) - 1;
    } elseif (!$timestamp_start_dst && $timestamp_end_dst) {
        // DST is active at the end but not at the start
        $dst_hours = $timestamp_start->diffInHours($timestamp_end) + 1;
    } else {
        // DST status is the same at both start and end timestamps
        $dst_hours = calCulateGuardWeekHours(dbFormateDateTime($modified_time_str), dbFormateDateTime($modified_time_end));
    }
    // $total = $result->hours;
    // $total = $dst_hours;
    // $guardWorkingHours = calCulateGuardWeekHours(dbFormateDateTime($modified_time_str), dbFormateDateTime($modified_time_end));
    $guardWorkingHours = $dst_hours;
    // $guardWorkingHours = calCulateGuardWeekHours(dbFormateDateTime($modified_time_str), dbFormateDateTime($modified_time_end));
    $continuation = false;
    if(isset($request->continuation) && $request->continuation == true){
        $continuation = true;
    }
    # CALCULATE GUARD SHIFT AND WORKING HOURS
    $hours = $this->getShiftHours(dbFormateDateTime($modified_time_str), dbFormateDateTime($modified_time_end), $request->site_id, $continuation);
    if(isset($request->id)){
        $addNewShift = JobRoster::findOrFail($request->id);
        $old_data = $addNewShift;
        
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
            $removeConflict = JobRoster::where('conflicted_with', $request->id)->get();
            if($removeConflict){
                foreach($removeConflict as $shift){
                    $updateShift = JobRoster::find($shift['id']);
                    $updateShift->conflicted_with = null;
                    $updateShift->conflict = null;
                    $updateShift->conf_start = null;
                    $updateShift->conf_end = null;
                    $updateShift->update();
                }
            }
        }

        // if($request->guard_id == null){
        //     $removeNotes = JobRoster::where('id', $request->id)->where('operation_notes', '!=',  NULL)->first();
        //     if($removeNotes){
        //             $removeNotes->operation_notes = null;
        //             $removeNotes->update();
                
        //     }
        // }

        $flagUpdateShift = 1;
    }else{
        $addNewShift = new JobRoster();
        $flagUpdateShift = 0;
    }
    $addNewShift->site_id = $request->site_id;
    $addNewShift->guard_id = (isset($request->guard_id) && !empty($request->guard_id)) ? $request->guard_id : null;
    $addNewShift->start = dbFormateDateTime($request->start);
    $addNewShift->end = dbFormateDateTime($request->end);
    $addNewShift->shift_payable = !empty($request->shift_payable) ? $request->shift_payable : 'yes';
    $addNewShift->shift_chargeable = !empty($request->shift_chargeable) ? $request->shift_chargeable : 'yes';
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
    $addNewShift->reimbursement = ($request->reimbursement == 'on') ? true : false;
    $addNewShift->reimbursement_text = $request->reimbursement_text;
    $addNewShift->reimbursement_value = $request->reimbursement_value;
    $addNewShift->shift_create_status = 'pending';
    $addNewShift->total_week_hours = $guardWorkingHours;
    $addNewShift->shift_type = (!empty($request->shift_type) ? $request->shift_type : '');
    $addNewShift->conflict = ((isset($conflictingShift) && !empty($conflictingShift)) ? 'conflict in '.getRosterdName($request->roster_id) : null);
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
    if($request->publish_status == 1){
        $addNewShift->publish_status = $request->publish_status;
    }
    $addNewShift->custome_rate = $request->custome_rate;
    $addNewShift->custome_payrate = $request->custome_payrate;
    $addNewShift->custome_chagerate = $request->custome_chagerate;
    $addNewShift->manualPayRate = $cus_payrate;
    $addNewShift->manualChargeRate = $cus_chargerate;
    $addNewShift->unprofile_name = isset($request->guard_id) ? null : $request->unprofile_name;
    $addNewShift->po_wo = $request->po_wo;
    $addNewShift->job_instrcutions = $request->job_instrcutions;
    $addNewShift->job_instruction_text = $request->job_instruction_text;
    // $addNewShift->roster_id = $request->roster_id;
    $addNewShift->updated_by = $request->admin_id;
    $addNewShift->on_call_job = isset($request->on_call_job) ? $request->on_call_job : 0;
    $addNewShift->save();
    if($request->publish_status == 1 && isset($request->guard_id) && $request->guard_id > 0){
        if(!empty($request->guard_id)){
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

    $roster_changes = $addNewShift->getChanges();
    $tableDefaults = [
        'reimbursement' => false,
        'un_published_shift' => false,
        'public_holidays' => false,
        'covid_marshal' => false,
        'training' => false,
        'continuation' => false,
        'over_time' => false,
        'travel_time' => false,
        'conf_start' => '',
        'conf_end' => '',
        'custome_rate' => false,
        'custome_payrate' => false,
        'custome_chagerate' => true,
    ];
    
    $modified_changes = array_diff_assoc($roster_changes, $tableDefaults);
    
    jobRosterActions($request->admin_id, 'update_shift', $addNewShift->id, 'job_roster', $old_data, $modified_changes);
    $admin_name = getAdminName($request->admin_id);
    $currnet_time = time();
    shiftCompleteActivity($addNewShift->id, $admin_name. ' Update this Shift', 'update_shift', $addNewShift->id, $currnet_time, $request->admin_id);
    # SAVE TASK

    if(!empty($request->job_roster_tasks)){
        if($flagUpdateShift == 1){
            foreach ($request->job_roster_tasks as $key => $task) {
                $updateTask =  JobRosterTask::where('id', $task['id'])->first();
                $old_task = $updateTask; 
                $is_check = 0; 
                if(!$updateTask){
                    $updateTask =  new JobRosterTask();
                    $is_check = 1;     
                }
                $updateTask->job_roster_id = $request->id;
                $updateTask->task = $task['task'];
                $updateTask->task_start = dbFormateDateTime($task['task_start']);
                $updateTask->task_end = dbFormateDateTime($task['task_end']);
                $updateTask->save();
                if($is_check == 1){
                    jobRosterActions($request->admin_id, 'add_shift_tasks', $updateTask->id, 'job_roster_tasks');
                }else{
                    $task_changes = $updateTask->getchanges();
                    jobRosterActions($request->admin_id, 'update_shift_tasks', $updateTask->id,'job_roster_tasks', $old_task, $task_changes);
                }
            }
        }else{
            foreach ($request->job_roster_tasks as $key => $task) {
                $newTask =  new JobRosterTask();
                $newTask->job_roster_id = $addNewShift->id;
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
        $fetchConflictedShift = JobRoster::where('conflicted_with', $addNewShift->id)
        ->where('roster_id', $request->roster_id)
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
                    $updateConflict = JobRoster::find($shift['id']);
                    $updateConflict->conflict = null;
                    $updateConflict->conf_start = null;
                    $updateConflict->conf_end = null;
                    $updateConflict->update();
                }else{
                    $updateConflict = JobRoster::find($shift['id']);
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
public function splitShift(Request $request){
    $updateShift = JobRoster::find($request->common['id']);
    $updateShift->start = $request->first_shift['start'];
    $updateShift->end = $request->first_shift['end'];
    $updateShift->guard_id = $request->first_shift['guard_id'];
    $updateShift->admin_id = $request->admin;
    $updateShift->shift_confirm = isset($request->shift_confirm) ? $request->shift_confirm : null;
    if(!$updateShift->publish_status == 1){
        $updateShift->publish_status = $request->common['publish_status'];
    }
    $updateResponse = $this->splitUpdateShift($updateShift);
    if($updateResponse->getData(true)['success'] == true){
        $addShift = JobRoster::with('jobRosterTask')->find($request->common['id']);
        $addShift->id = null;
        $addShift->start = $request->second_shift['start'];
        $addShift->end = $request->second_shift['end'];
        $addShift->guard_id = $request->second_shift['guard_id'];
        $addShift->admin_id = $request->admin;
        $addResponse = $this->splitAddNewShift($addShift);
        if($addResponse->getData(true)['success'] == 'true'){
            return $addResponse;
        }else{
            $addShift->guard_id = null;
            $addResponse = $this->splitAddNewShift($addShift);
            return $addResponse;
        }
    }else{
        return $updateResponse;
    }

}
public function splitAddNewShift($request){

    $jobNewRoster = JobNewRoster::where('id', $request->roster_id)->first();

    if (
        (dbFormate($request->start) < $jobNewRoster->start) ||
        (
            isset($jobNewRoster->end) &&
            ($jobNewRoster->end !== null) &&
            (dbFormate($request->start) > $jobNewRoster->end)
        )
    ) {
        return response()->json([
            'success' => false,
            'hide' => true,
            'message' => 'Shift timing must fall within the roster date.'
        ]);
    }


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
            if($diff > 9){
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
                    if ($document->document_expire == 'current, pending renewal' && $document->document_type == 'security_license'){ 
                           $checkGuardDocs = true;
                    } else {
                        if ($document->document_type == 'security_license' &&
                        ($document->document_expire === '' || $document->document_expire === null || $today > strtotime($document->document_expire))) {
                        $docExpire = "Security License Expired!";
                        $checkGuardDocs = false;
                        } else {
                           // return 'active';
                           $checkGuardDocs = true;
                        }
                    }
                } else {
                    if ($document->document_expire == 'current, pending renewal' && $document->document_type == 'security_license')
                    {
                        $checkGuardDocs = true;
                    } else {
                        if (in_array($document->document_type, ['visa', 'passport', 'security_license']) &&
                            ($document->document_expire === '' || $document->document_expire === null || $today > strtotime($document->document_expire))) {
                            $docExpire = ucfirst($document->document_type) . " Expired!";
                            $checkGuardDocs = false;
                        }
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
            $addNewShift = JobRoster::findOrFail($request->id);
            $start_time = dbFormateDateTime($request->start);
            $end_time = dbFormateDateTime($request->end);
            $conflictingShift = JobRoster::where('guard_id', $request->guard_id)
            ->where(function ($query) use ($start_time, $end_time) {
                $query->where(function ($q) use ($start_time) {
                    $q->where('start', '<=', $start_time)->where('end', '>=', $start_time);
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
            $conflictingShift = JobRoster::where('guard_id', $request->guard_id)
            ->where(function ($query) use ($start_time, $end_time) {
                $query->where(function ($q) use ($start_time) {
                    $q->where('start', '<=', $start_time)->where('end', '>=', $start_time);
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

        $sumOfOneWeekHour = JobRoster::where('guard_id', $request->guard_id)
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
    if($request->shift_type == 'template'){
        $addNewShift = new JobRoster();
        $addNewShift->start = dbFormateDateTime($request->start);
        $addNewShift->end = dbFormateDateTime($request->end);
        $addNewShift->shift_create_status = 'pending';
        $addNewShift->shift_type = 'template';
        $addNewShift->save();
        if(!empty($request->job_roster_tasks)){
            foreach ($request->job_roster_tasks as $key => $task) {
                $newTask =  new JobRosterTask();
                $newTask->job_roster_id = $addNewShift->id;
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
    if($request->custome_rate == true){
        if($request->custome_payrate == true){
            $cus_payrate = json_encode($request->manualPayRate);
        }
    }
    if($request->custome_rate == true){
        if($request->custome_chagerate == true){
            $cus_chargerate = json_encode($request->manualChargeRate);
        }
    }
    $PH_checkBox = ($request->public_holidays == 'on' ? true : false);
    # CALCULATE GUARD SHIFT AND WORKING HOURS
    $continuation = false;
    if(isset($request->continuation) && $request->continuation == true){
        $continuation = true;
    }
    $start_time = Carbon::createFromFormat("m-d-Y H:i", $request->start);
    $start_time_hours_minutes = $start_time->format('H:i');
    if($start_time_hours_minutes === '23:59'){
        $start_time->addMinutes(1);
        $modified_time_str = $start_time->format("m-d-Y H:i");
    }else{
        $modified_time_str = $request->start;
    }
    $end_time = Carbon::createFromFormat("m-d-Y H:i", $request->end);
    $end_time_hours_minutes = $end_time->format('H:i');
    if($end_time_hours_minutes === '23:59'){
        $end_time->addMinutes(1);
        $modified_time_end = $end_time->format("m-d-Y H:i");
    }else{
        $modified_time_end = $request->end;
    }
    $hours = $this->getShiftHours(dbFormateDateTime($modified_time_str), dbFormateDateTime($modified_time_end), $request->site_id, $continuation, $PH_checkBox, $PH_checkBox);
    $timestamp_start = Carbon::parse(dbFormateDateTime($modified_time_str));
    $timestamp_start_dst = Carbon::parse(dbFormateDateTime($modified_time_str))->isDST();
    $timestamp_end = Carbon::parse(dbFormateDateTime($modified_time_end));
    $timestamp_end_dst = Carbon::parse(dbFormateDateTime($modified_time_end))->isDST();
    if ($timestamp_start_dst && !$timestamp_end_dst) {
        // DST is active at the start but not at the end
        $dst_hours = $timestamp_start->diffInHours($timestamp_end) - 1;
    } elseif (!$timestamp_start_dst && $timestamp_end_dst) {
        // DST is active at the end but not at the start
        $dst_hours = $timestamp_start->diffInHours($timestamp_end) + 1;
    } else {
        // DST status is the same at both start and end timestamps
        $dst_hours = calCulateGuardWeekHours(dbFormateDateTime($modified_time_str), dbFormateDateTime($modified_time_end));
    }
    // $total = $result->hours;
    // $total = $dst_hours;
    // $guardWorkingHours = calCulateGuardWeekHours(dbFormateDateTime($modified_time_str), dbFormateDateTime($modified_time_end));
    $guardWorkingHours = $dst_hours;

    if(isset($request->id)){
        $addNewShift = JobRoster::findOrFail($request->id);
        $flagUpdateShift = 1;
    }else{
        $addNewShift = new JobRoster();
        $flagUpdateShift = 0;
    }
    $addNewShift->site_id = $request->site_id;
    $addNewShift->guard_id = (isset($request->guard_id) && !empty($request->guard_id)) ? $request->guard_id : null;
    $addNewShift->start = dbFormateDateTime($request->start);
    $addNewShift->end = dbFormateDateTime($request->end);
    $addNewShift->shift_payable = !empty($request->shift_payable)? $request->shift_payable : 'yes';
    $addNewShift->shift_chargeable = !empty($request->shift_chargeable) ? $request->shift_chargeable : 'yes';
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
    $addNewShift->reimbursement = ($request->reimbursement == 'on') ? true : false;
    $addNewShift->reimbursement_text = $request->reimbursement_text;
    $addNewShift->reimbursement_value = $request->reimbursement_value;
    $addNewShift->shift_create_status = 'pending';
    $addNewShift->total_week_hours = $guardWorkingHours;
    $addNewShift->shift_type = (!empty($request->shift_type) ? $request->shift_type : '');
    $addNewShift->conflict = (!empty($conflictingShift) ? 'conflict in '.getRosterdName($request->roster_id) : null);
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
    $addNewShift->roster_id = $request->roster_id;
    $addNewShift->created_by = $request->admin_id;
    $addNewShift->on_call_job = isset($request->on_call_job) ? $request->on_call_job : 0;
    $addNewShift->save();
    if($request->publish_status == 1 && isset($request->guard_id) && $request->guard_id > 0 && $request->un_published_shift == 0){
        if(!empty($request->guard_id)){
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

    if(!empty($request->job_roster_tasks)){
        if($flagUpdateShift == 1){
            foreach ($request->job_roster_tasks as $key => $task) {
                $updateTask =  JobRosterTask::where('id', $task['id'])->first();
                $old_task = $updateTask; 
                $is_check = 0; 
                if(!$updateTask){
                    $updateTask =  new JobRosterTask();
                    $is_check = 1;     
                }
                $updateTask->job_roster_id = $request->id;
                $updateTask->task = $task['task'];
                $updateTask->task_start = dbFormateDateTime($task['task_start']);
                $updateTask->task_end = dbFormateDateTime($task['task_end']);
                $updateTask->save();
                if($is_check == 1){
                    jobRosterActions($request->admin_id, 'add_shift_tasks', $updateTask->id, 'job_roster_tasks');
                }else{
                    $task_changes = $updateTask->getChanges();
                    jobRosterActions($request->admin_id, 'update_shift_tasks', $updateTask->id,'job_roster_tasks', $old_task, $task_changes);
                }
            }
        }else{
            if($request->shift_type == 'template_rost'){
                $getTemplateShiftTask = JobRoster::where(['start'=> $request->start, 'end'=>$request->end, 'roster_id'=>$request->roster_id, 'shift_type'=>'template'])->with('jobRosterTask')->first();
                if($getTemplateShiftTask->jobRosterTask){
                    foreach($getTemplateShiftTask->jobRosterTask as $task){
                        $newTask =  new JobRosterTask();
                        $newTask->job_roster_id = $addNewShift->id;
                        $newTask->task = $task['task'];
                        $newTask->task_start = dbFormateDateTime($task['task_start']);
                        $newTask->task_end = dbFormateDateTime($task['task_end']);
                        $newTask->save();
                        jobRosterActions($request->admin_id, 'add_shift_tasks', $newTask->id,'job_roster_tasks');
                    }
                }
            }else{
                foreach ($request->job_roster_tasks as $key => $task) {
                    $newTask =  new JobRosterTask();
                    $newTask->job_roster_id = $addNewShift->id;
                    $newTask->task = $task['task'];
                    $newTask->task_start = dbFormateDateTime($task['task_start']);
                    $newTask->task_end = dbFormateDateTime($task['task_end']);
                    $newTask->save();
                    jobRosterActions($request->admin_id, 'add_shift_tasks', $newTask->id,'job_roster_tasks');
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
// function calculateFutureMonthFourthnight($givenDate)
// {
//     $startDate = '2025-12-01';
//     $date = Carbon::parse($givenDate);
//     // $date = Carbon::createFromFormat('m-d-Y H:i', $givenDate);
//     // $date = DateTime::createFromFormat('Y-m-d H:i', $givenDate);



//     $formattedDate = $date;
//     $endDate = $formattedDate;
//     $startTime = strtotime($startDate);
//     $endTime = strtotime($endDate);

//     $secondsDiff = $endTime - $startTime;
//     $daysDiff = floor($secondsDiff / (60 * 60 * 24));
//     $totalFourthnight = floor($daysDiff/14);
//     $totalFourthnight = $totalFourthnight * 14;
//     $FourthnightStartDate = date('Y-m-d', strtotime($startDate . ' + '.$totalFourthnight.' days'));
//     $FourthnightEndDate = date('Y-m-d', strtotime($FourthnightStartDate . ' + 13 days'));
//     $ret['week_start'] = $FourthnightStartDate;
//     $ret['week_end'] = $FourthnightEndDate;
//     return $ret;
// }
function calculateFutureMonthFourthnight($givenDate)
{
    $startDate = '2025-12-01';
    
    // Try to parse with auto-detection logic
    $date = $this->parseDateWithAutoDetection($givenDate);
    
    $formattedDate = $date->format('Y-m-d H:i:s');
    $endDate = $formattedDate;
    $startTime = strtotime($startDate);
    $endTime = strtotime($endDate);

    $secondsDiff = $endTime - $startTime;
    $daysDiff = floor($secondsDiff / (60 * 60 * 24));
    $totalFourthnight = floor($daysDiff/14);
    $totalFourthnight = $totalFourthnight * 14;
    $FourthnightStartDate = date('Y-m-d', strtotime($startDate . ' + '.$totalFourthnight.' days'));
    $FourthnightEndDate = date('Y-m-d', strtotime($FourthnightStartDate . ' + 13 days'));
    
    $ret['week_start'] = $FourthnightStartDate;
    $ret['week_end'] = $FourthnightEndDate;
    return $ret;
}

private function parseDateWithAutoDetectionDrop($dateString)
{
    // Try to match dd-mm-yyyy or mm-dd-yyyy pattern
    preg_match('/^(\d{1,2})-(\d{1,2})-(\d{4})\s*(\d{2}:\d{2})?/', $dateString, $matches);

    if (count($matches) < 4) {
        return Carbon::parse($dateString);
    }

    $first  = (int)$matches[1];
    $second = (int)$matches[2];
    $year   = (int)$matches[3];
    $hasTime = isset($matches[4]) && $matches[4] !== '';
    $format  = $hasTime ? 'H:i' : '';

    if ($first > 12 && $second <= 12) {
        $date = Carbon::createFromFormat(trim('d-m-Y ' . $format), $dateString);

    } elseif ($first <= 12 && $second > 12) {
        $date = Carbon::createFromFormat(trim('m-d-Y ' . $format), $dateString);

    } elseif ($first > 12 && $second > 12) {
        return Carbon::parse($dateString);

    } else {
        $date = Carbon::createFromFormat(trim('m-d-Y ' . $format), $dateString);
        if ($date === false || !$date->isValid()) {
            $date = Carbon::createFromFormat(trim('d-m-Y ' . $format), $dateString);
        }
    }

    if ($date === false || !$date->isValid()) {
        return Carbon::parse($dateString);
    }

    return $date;
}

private function parseDateWithAutoDetection($dateString)
{
    preg_match('/^(\d{1,2})-(\d{1,2})-(\d{4})/', $dateString, $matches);
    
    if (count($matches) !== 4) {
        return Carbon::parse($dateString);
    }
    
    $first = (int)$matches[1];
    $second = (int)$matches[2];
    $year = (int)$matches[3];
    
    if ($first > 12 && $first <= 31) {
        $date = Carbon::createFromFormat('d-m-Y H:i', $dateString);
    } elseif ($second > 12 && $second <= 31) {
        $date = Carbon::createFromFormat('m-d-Y H:i', $dateString);
    } else {
        $date = Carbon::createFromFormat('d-m-Y H:i', $dateString);
    }
    
    if ($date === false || !$date->isValid()) {
        return Carbon::parse($dateString);
    }
    
    return $date;
}

function get_current_month_hours_guards($guardId, $start, $end, $shifIsPressed, $event_id = 0 )
{
    $data = JobRoster::where('guard_id', $guardId)
    ->where('start', '>=', date('Y-m-d H:i', strtotime($start)))
    ->where('start', '<=', date('Y-m-d 23:59', strtotime($end)))
    ->where('id', '!=', $event_id)
    ->whereNull('deleted_at')
    ->sum('hours');
    return $data;
}
public function checkGuardsAvailibilty(Request $request){
    # CHECK GUARD IS FULL TIMER OR PART TIMER
    $guard = Guard::find($request->guard_id);
    if(($guard->staff_type == 'part_time') && !isset($request->shift_confirm)){
        return response()->json(['success' => false,'is_part_timer'=>true, 'message' => 'You\'ve selected a part-time guard. Would you like to continue or check full-time guards?']); 
    }
    $week_array = $this->calculateFutureMonthFourthnight($request->start);
    $currentMonthData = $this->get_current_month_hours_guards($request->guard_id, $week_array['week_start'], $week_array['week_end'], isset($request->shifIsPressed) ? true : false);
    $max_hours = $this->count_today_working_hours($request->start, $request->end, $guard->id);
    // return [$currentMonthData + $max_hours, $currentMonthData, $max_hours];
    if($currentMonthData + $max_hours > 72){
        return response()->json(['success' => false,'exceeded'=>true, 'message' => 'Guard is not available because the hours limit is exceeded']);
    }else{
        return response()->json(['success' => true, 'message' => 'Guard is available']);
    }
}
public function getFullTimeGuardbySite(Request $request)
{
    //allow all guards
    $week_array = $this->calculateFutureMonthFourthnight($request->date.' 00:00');
    $start = $week_array['week_start'];
    $end = $week_array['week_end'];
    $site_id = (int) $request->site_id;
    $site = Site::find($site_id);
    $guards = Guard::where('guard_status', 'active')
        // ->whereJsonContains('site_id', $site_id)
        // ->where('guard_status', 'active')
        ->where('admin_approval_status', 'active')
        ->where('is_available', 'yes')
        ->where('staff_type', 'full_time')
        ->orderBy('first_name')
        ->get();
    $mainGuards = [];
    foreach ($guards as $guard) {
        $currentMonthData = $this->get_current_month_hours_guards($guard->id, $start, $end, isset($request->shifIsPressed) ? true : false);
        if($currentMonthData < 72){
            $guard->hours = $currentMonthData;
            $mainGuards[] = $guard;
        }
    }
    $gd = GetGuardBySiteResource::collection($mainGuards);
    return response()->json(['success' => true, 'data' => $gd]);
}

// Roster Work Start for AMG

public function getJobData($jobId)
    {
        $data = Site::where('id', $jobId)->first();
        if(!empty($data)){
            return $data;
        }else{
            return 'N/A';
        }
    }

    public function getSingleGuard($guardId)
    {
        $data = Guard::with('empDetails')->where(['id' => $guardId])->first();
        return $data;
    }
    function date_convert($date_format)
    {
        if (count(explode('-', $date_format)) > 0) {
            return $date_format;
        } else {
            list($date, $month, $year) = sscanf($date_format, '%d/%d/%d');
            if ($month < 9) {
                $month = '0' . $month;
            }
            return $month . '/' . $date . '/' . $year;
        }
    }
    public function checkGuardSecurityLicenceDocuments($guardData, $tempDate, $bypass = true)
    {
        $status = '';
        $guard = GuardWorkDetail::where('guard_id', $guardData->id)->first();

        if(empty($guard->guard_document_type)){
            $status = "Please First Add Your Residential Status!";
            return $status;
        }

        $today = date("Y/m/d");
        $today_time = strtotime($today);
        
        $guard = Guard::where('id', $guardData->id)->with('guardDocuments')->first();

    foreach ($guard->guardDocuments as $key => $value){
        if($value->c_f_roster ==  1){
            if($value->document_category == 'citizen'){
                if($value->document_type == 'security_license' && ($value->document_expire == '' || $value->document_expire == null || $today_time > strtotime($value->document_expire))){
                    $status = "Security License Expired!";
                    break;
            }else{
                $status = 'Active';
                return $status;
            }
        }else{
    
            if($value->document_type == 'visa' && ($value->document_expire == '' || $value->document_expire == null || $today_time > strtotime($value->document_expire))){
                $status = "Visa Expired!";
                break;
            }elseif($value->document_type == 'passport' && ($value->document_expire == '' || $value->document_expire == null || $today_time > strtotime($value->document_expire))){
                $status = "Passport Expired!";
                break;
            }elseif($value->document_type == 'security_license' && ($value->document_expire == '' || $value->document_expire == null || $today_time > strtotime($value->document_expire))){
                $status = "Security License Expired!";
                break;
            }else{
                $status = 'Active'; 
            }
        }

        }else{
            $status = 'Active';
        }

        }
        return $status;
    }

    function calculateChargeRate($roster_id)
    {
        $result = DB::table('job_rosters')
            ->join('sites', 'sites.id', '=', 'job_rosters.site_id')
            ->join('customers', 'customers.id', '=', 'sites.customer_id')
            ->where('job_rosters.id', $roster_id)
            ->select(
                DB::raw('COALESCE(job_rosters.chargerate, 0) AS chargerate'),
                'customers.charged_rates_id AS customer_charge_rate_id',
                'sites.level', 
                'sites.state', 
                'sites.payrol', 
                'sites.type', 
                'job_rosters.hours'
            )
            ->first();

        $rate = 0;
        if ($result) {
            if ($result->chargerate != null && $result->chargerate > 0) {
                $charge_rate = DB::table('charge_rates')->where('id', $result->chargerate)->first();
            } elseif ($result->customer_charge_rate_id != null && $result->customer_charge_rate_id > 0) {
                $charge_rate = DB::table('charge_rates')->where('id', $result->customer_charge_rate_id)->first();
            } else {
                $charge_rate = array();
            }
        } else {
            $charge_rate = array();
        }
        

        if (!empty($charge_rate)) {
            if ($result->payrol == 'Default Rates') {
                if ($result->type == 'metro') {
                    $rate = $charge_rate->flat_metro_flat_metro_week_day;
                } else {
                    $rate = $charge_rate->flat_regional_week_day;
                }
            } else {
                if ($result->type == 'metro') {
                    $rate = $charge_rate->eba_metro_weekday_day;
                } else {
                    $rate = $charge_rate->eba_regional_weekday_day;
                }
            }
        }

    }


    public function addEvent($eventId, $start, $end, $guardId, $jobId, $tempDate, $temp_start, $temp_end, $post_status = 0, 
$tasks = null, $adhoc_shift = 'no', $payable = 'yes', $chargeable = 'yes', $custom_rates = 'no', $payrate_id = 0, 
$chargerate_id = 0, $publish_status = 0, $training = 0, $continuation = 0, $travel_time = 0, $paid_by = '', 
$public_holiday = 0, $travel_time_payable = 'yes', $travel_time_chargeable = 'yes', $covid_marshal = 0,
 $unprofile_name = '', $travel_time_amount = 0, $overtime = 0, $overtime_value = 1, $travel_time_amount_chargeable = 0, $unpublish_shift = 0, $multiple_shifts_count = 1, $job_instrcutions = '', $conflict = 0, $conflict_message = 'N/A',$chargerate_level, $payrate_level, $admin_id, $roster_id, $admin_confirm)
{

    $dateEnd = DateTime::createFromFormat('d-m-Y H:i', $end);
    if ($dateEnd) {
        $formattedEnd = $dateEnd->format('Y-m-d H:i');
    } else {
        $formattedEnd = null;
    }
    $dateStart = DateTime::createFromFormat('d-m-Y H:i', $start);
    if ($dateStart) {
        $formattedStart = $dateStart->format('Y-m-d H:i');
    } else {
        $formattedStart = null;
    }

    if (!$eventId) {
        $last_row = jobroster::select('id')->orderBy('id', 'desc')->first();
        $id = 0;
        if (empty($last_row)) {
            $id = 1;
        } else {
            $id = $last_row->id + 1;
        }
            // $publish_status = 0;
    }

    if(isset($request->id) && $request->id > 0){
        $addNewShift = JobRoster::findOrFail($request->id);
        $start_time = dbFormateDateTime($request->start);
        $end_time = dbFormateDateTime($request->end);
        $conflictingShift = JobRoster::where('guard_id', $request->guard_id)
        ->where(function ($query) use ($start_time, $end_time) {
            $query->where(function ($q) use ($start_time) {
                $q->where('start', '<=', $start_time)->where('end', '>=', $start_time);
            })->orWhere(function ($q) use ($end_time) {
                $q->where('start', '<=', $end_time)->where('end', '>=', $end_time);
            })->orWhere(function ($q) use ($start_time, $end_time) {
                $q->where('start', '>=', $start_time)->where('end', '<=', $end_time);
            });
        })
        ->where('id', '!=' ,$addNewShift->id)->select('id', 'start', 'end')->first();
    }else{
        $start_time = dbFormateDateTime($formattedStart);
        $end_time = dbFormateDateTime($formattedEnd);
        $conflictingShift = JobRoster::where('guard_id', $guardId)
        ->where(function ($query) use ($start_time, $end_time) {
            $query->where(function ($q) use ($start_time) {
                $q->where('start', '<=', $start_time)->where('end', '>=', $start_time);
            })->orWhere(function ($q) use ($end_time) {
                $q->where('start', '<=', $end_time)->where('end', '>=', $end_time);
            })->orWhere(function ($q) use ($start_time, $end_time) {
                $q->where('start', '>=', $start_time)->where('end', '<=', $end_time);
            });
        })
        ->select('id', 'start', 'end')->first();
    }

        $hours = $this->getShiftHours($start_time, $end_time, $jobId, 1, $public_holiday, $public_holiday);

        $data = array(

        'morning_hours' => (!empty($hours['morning']) ? roundHours($hours['morning']) : 0.0),
        'night_hours' => (!empty($hours['night']) ? roundHours($hours['night']) : 0.0),
        'saturday_morning_hours' => (!empty($hours['saturday_morning']) ? roundHours($hours['saturday_morning']) : 0.0),
        'saturday_night_hours' => (!empty($hours['saturday_night']) ? roundHours($hours['saturday_night']) : 0.0),
        'sunday_morning_hours' => (!empty($hours['sunday_morning']) ? roundHours($hours['sunday_morning']) : 0.0),
        'sunday_night_hours' => (!empty($hours['sunday_night']) ? roundHours($hours['sunday_night']) : 0.0),
        'ph_morning_hours' => (!empty($hours['ph_morning']) ? roundHours($hours['ph_morning']) : 0.0),
        'ph_night_hours' => (!empty($hours['ph_night']) ? roundHours($hours['ph_night']) : 0.0),

        'guard_id' => $guardId,

        'site_id' => $jobId,

        'start' => $formattedStart,

        'end' => $formattedEnd,

        "temp_date" => $tempDate,
        
        "roster_id" => $roster_id,

        "start" => $formattedStart,

        "end" => $formattedEnd,

        'publish_status' => $publish_status,

        // 'add_status' => 1,

        // 'post_status' => $post_status,

        // 'tasks' => is_array($tasks) ? json_encode($tasks) : $tasks,

        // 'job_start' => strtotime($temp_start),

        // 'job_end' => strtotime($temp_end),

        'adhoc_shift' => $adhoc_shift,
        'shift_payable' => !empty($payable) ? $payable : 'yes',

        'shift_chargeable' => !empty($chargeable) ? $chargeable : 'yes',

        'last_send_welfare_call' => '',
        'custome_rate' => $custom_rates,
        'payrate' => $payrate_id,
        'chargerate' => $chargerate_id,
        // 'training' => $training,
            // 'continuation' => $continuation,
        'continuation' => 1,
        'travel_time' => $travel_time,
        // 'paid_by' => $paid_by,
        'public_holidays' => $public_holiday,
        // 'travel_time_payable' => $travel_time_payable,
        // 'travel_time_chargeable' => $travel_time_chargeable,
        'covid_marshal' => $covid_marshal,
        'unprofile_name' => $unprofile_name,
        'over_time' => $overtime,
        'over_time_value' => $overtime_value,
        'travel_time_value' => $travel_time_amount,
        // 'travel_time_amount_chargeable' => $travel_time_amount_chargeable,
        // 'unpublish_shift' => $unpublish_shift,
        'conflict' => (!empty($conflictingShift) ? 'conflict in '.getRosterdName($eventId) : null),
        'conf_end' => (!empty($conflictingShift) ? dbFormateDateTime($conflictingShift->end) : ''),
        'conf_start' => (!empty($conflictingShift) ? dbFormateDateTime($conflictingShift->start) : ''),
        'conflicted_with' => (!empty($conflictingShift) ? $conflictingShift->id : null),
        // 'conflict_message' => $conflict_message,
        'chargerate_level' => $chargerate_level,
        'payrate_level' => $payrate_level,
        'last_update' => time(),

    );
    if ($unpublish_shift == 1) {
        $data['publish_status'] = 1;
    }
    if ($job_instrcutions != '') {
        $data['job_instrcutions'] = $job_instrcutions;
    }

        // $hoursEnd = strtotime($data['start']);
        // $hoursStart = strtotime($data['end']);
        // $data['hours'] = round(abs($hoursEnd - $hoursStart) / 3600, 2);

        $data['hours'] = $hours['morning'] + $hours['night'] + $hours['saturday_morning'] + $hours['saturday_night'] + $hours['sunday_morning'] + $hours['sunday_night'] + $hours['ph_morning'] + $hours['ph_night'];

        if($admin_confirm == 1 && $guardId > 0){
            $data['job_status'] = 'confirmed';
            $data['admin_confirm'] = 1;
            $data['publish_status'] = 1;
        }else{
            $data['job_status'] = 'pending';
            $data['publish_status'] = $publish_status;
            $data['admin_confirm'] = 0;
        }
        if ($guardId == 0 || $guardId == '' || $guardId == NULL || $guardId == null) {
            $data['job_status'] = 'pending';
            $data['publish_status'] = $publish_status;
            $data['admin_confirm'] = 0;
        }

    $roster_id = jobroster::insertGetId($data);
    if(!empty($tasks)){
        foreach ($tasks as $key => $task) {
            $newTask =  new JobRosterTask();
            $newTask->job_roster_id = $roster_id;
            $newTask->task = $task['task'];
            $newTask->task_start = dbFormateDateTime($task['task_start']);
            $newTask->task_end = dbFormateDateTime($task['task_end']);
            $newTask->save();
            // jobRosterActions($request->admin_id, 'add_shift_tasks', $newTask->id,'job_roster_tasks');
        }
    }

    $rosterdata = DB::table('job_rosters')->where('id', $roster_id)->first();

    if ($multiple_shifts_count > 1) {
        for ($i = 0; $i < $multiple_shifts_count; $i++) {
            $last_row = jobroster::select('id')->orderBy('id', 'desc')->first();
            $event_id = 0;
            if (empty($last_row)) {
                $eventId = 1;
            } else {
                $eventId = $last_row->event_id + 1;
            }
            $data['guard_id'] = 0;
            $data['event_id'] = $eventId;
            // if($admin_confirm == 1){
            //  $data['job_status'] = 'confirmed';  
            //  $data['admin_confirm'] = 1;  
            // }
            $rid = DB::table('job_rosters')->insertGetId($data);
            // $this->calculateChargeRate($rid);
            $new_record_add1 = DB::table('job_rosters')->where('roster_id', $rid)->get();
            $rosterdata1 = DB::table('job_rosters')->where('roster_id', $rid)->first();
            $action = 'shift_add';
            // $this->administrator->log_user_activity($action, $new_record_add1);
            $this->updateHoursDistribution($temp_start, $temp_end, $rosterdata1->site_id, $rosterdata1->roster_id);
        }
    }


    if ($publish_status == 1 && $guardId > 0) {
        $guard_data = DB::table('guards')->join('job_rosters', 'job_rosters.guard_id', '=', 'guards.id')->where('job_rosters.id', $roster_id)->select('guards.*')->first();
        if (!empty($guard_data)) {
            $notification_data['guards'][0] = array(
                'guard_id' => $guard_data->id,
                'notification_token' => $guard_data->notification_token
            );
            $notification_data['title'] = 'New Shift';
            $notification_data['message'] = 'You are rostered on a new Shift.';
            $notification_data['page'] = 'homepage';
            // $res = $this->guard_model->send_push_notification($notification_data);
        }
    }

    if(isset($guard_data->notification_token)){
        $prams['message'] = 'You are rostered on a new Shift.';
        $prams['title'] = 'Roster Published';
        $prams['page'] = 'roster';
        $prams['notification_token'] = $guard_data->notification_token;
        send_push_notification($prams);
    }
    if ($guardId == null) {
        $guardId = 0;
    } else {

    }

    if ($roster_id != '') {
        DB::table('roster_complete_activity')->insert([
            'roster_id' => $roster_id,
            'activity' => 'Shift created',
            'type' => 'shift_create',
            'record_id' => $roster_id,
            'activity_time' => time(),
            'activity_by' => $admin_id,
        ]);
        $this->updateHoursDistribution($start, $end, $rosterdata->site_id, $rosterdata->roster_id);
        return true;
    } else {

        return false;
    }
}

function calculateHoursMorningAMG($shift_start, $shift_end, $start, $end)
{
    $shift_hours = 0;
    if ($shift_end < $shift_start) {
        $shift_end = $shift_end + 24;
    }
    $shift_hours = $shift_end - $shift_start;
    if ($shift_end < $shift_start) {
        $shift_end = $shift_end - 24;
    }

    if ($shift_hours > 12) {

        if($shift_start <= $start && $shift_end > $end){
            return 12;
        }elseif($shift_start >= $start && $shift_start <= $end && $shift_end > $start && $shift_end < $end){
         return ($end - $shift_start) + ($shift_end - $start);
     }elseif($shift_start < $start && $shift_end > $start && $shift_end <= $end)
     {
        return $shift_end - 6;
    }
    elseif($shift_start >= $start && $shift_start <= $end && $shift_end > $end)
    {
        return 18 - $shift_start;
    }
           
    else{
        return 0;
    }
}else{

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
}elseif($shift_start < $start && $shift_end > $end){
    return $end - $start;
}elseif($shift_start >= $end && $shift_end > $start && $shift_end < $end)
{
        // shift start in night in gone into day
        // echo 'Here';
    return $shift_end - $start;
}
elseif($shift_start > $start && $shift_end < $end){
    return $end - $shift_start;
}   
else{
    return 0;
}
}
}

private function updateHoursDistribution($start, $end, $siteID, $rosterID)
{
    $day_start = Carbon::createFromFormat('m-d-Y H:i', $start)->format('l');
    $day_end = Carbon::createFromFormat('m-d-Y H:i', $end)->format('l');


    $start = strtotime($start);
    $end = strtotime($end);

    $diff = $end - $start;
    $hours = round($diff / (60 * 60), 2);
    $morning_start = 6;
    $morning_end = 18;

        $night_start = 18;
        $night_end = 6;

        $shift_start = $this->convert_into_fraction($start);
        $shift_end = $this->convert_into_fraction($end);
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
        $site_state = DB::table('sites')->where('id', $siteID)->select('state')->first();
        $states_array = array(
            'Victoria' => 'vic',
            'NSW' => 'nsw',
            'New South Wales' => 'nsw',
            'Queensland' => 'qld',
            'Tasmania' => 'tas',
            'Western Australia' => 'wa',
            'South Australia' => 'sa',
            'ACT' => 'act'
        );
        $state = $states_array[$site_state->state];
        $public_holiday_start = DB::table('public_holidays')->where('date', date('Ymd', $start))->where('state', $state)->first();
        $public_holiday_end = DB::table('public_holidays')->where('date', date('Ymd', $end))->where('state', $state)->first();
        if (!empty($public_holiday_start)) {
            $start_in_public_holiday = true;
        }
        if (!empty($public_holiday_end)) {
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
        } elseif ($start_in_public_holiday && !$end_in_public_holiday) {
            $ph_end = strtotime(date('m/d/Y 23:59:59', $start));
            $diff = $ph_end - $start;
            $total_ph_hours = round($diff / (60 * 60), 2);
            $ph_start = $this->convert_into_fraction($start);
            $ph_end = $this->convert_into_fraction($ph_end);
            $start = strtotime($public_holiday_start->date) + (60 * 60 * 24);
            $day_start = Carbon::parse(date('m/d/Y', $end))->format('l');
            $hours = $hours - $total_ph_hours;
            // echo 'Start in PH - ';
        } elseif (!$start_in_public_holiday && $end_in_public_holiday) {
            $ph_start = strtotime(date('m/d/Y 00:00:00', strtotime($public_holiday_end->date)));
            $diff = $end - $ph_start;
            $total_ph_hours = round($diff / (60 * 60), 2);
            $shift_end = 0;
            $end = $ph_start;
            $hours = $hours - $total_ph_hours;

        }

        // end of public holiday calculation
        if ($day_start == 'Saturday' && $day_end == 'Saturday') {
            $total_saturday_hours = $hours;
            $saturday_start = $shift_start;
            $saturday_end = $shift_end;
            $shift_start = 0;
            $shift_end = 0;
            $hours = 0;
        } elseif ($day_start == 'Saturday' && $day_end != 'Saturday') {
            $sat_end = strtotime(date('m/d/Y 23:59:59', $start));
            $diff = $sat_end - $start;
            $total_saturday_hours = round($diff / (60 * 60), 2);
            $saturday_start = $shift_start;
            $saturday_end = $this->convert_into_fraction($sat_end);

            $shift_end = 0;
            $hours = $hours - $total_saturday_hours;
        } elseif ($day_start != 'Saturday' && $day_end == 'Saturday') {
            $sat_start = strtotime(date('m/d/Y 00:00:00', $end));
            $diff = $end - $sat_start;
            $total_saturday_hours = round($diff / (60 * 60), 2);
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
        } elseif ($day_start == 'Sunday' && $day_end != 'Sunday') {
            $sun_end = strtotime(date('m/d/Y 23:59:59', $start));
            $diff = $sun_end - $start;
            $total_sunday_hours = round($diff / (60 * 60), 2);
            $sunday_start = $shift_start;
            $sunday_end = $this->convert_into_fraction($sun_end);

            $shift_end = 0;
            $hours = $hours - $total_sunday_hours;
        } elseif ($day_start != 'Sunday' && $day_end == 'Sunday') {
            $sun_start = strtotime(date('m/d/Y 00:00:00', $end));
            $diff = $end - $sun_start;
            $total_sunday_hours = round($diff / (60 * 60), 2);
            $sunday_start = $this->convert_into_fraction($sun_start);
            $sunday_end = $this->convert_into_fraction($end);
            $shift_end = 24;
            $hours = $hours - $total_sunday_hours;
        }

        $morning = round($this->calculateHoursMorningAMG($shift_start, $shift_end, $morning_start, $morning_end), 2);
        $saturday_morning = round($this->calculateHoursMorningAMG($saturday_start, $saturday_end, $morning_start, $morning_end), 2);

        $sunday_morning = round($this->calculateHoursMorningAMG($sunday_start, $sunday_end, $morning_start, $morning_end), 2);

        $ph_morning = round($this->calculateHoursMorningAMG($ph_start, $ph_end, $morning_start, $morning_end), 2);

        if ($morning < 0) {
            $morning = 0;
        }
        if ($saturday_morning < 0) {
            $saturday_morning = 0;
        }
        if ($sunday_morning < 0) {
            $sunday_morning = 0;
        }
        return [
            'morning' =>  $morning,
            'night' => round(((($hours - $morning) < 0) ? 0 : ($hours - $morning)), 2),
            'saturday_morning' => $saturday_morning,
            'saturday_night' => round(((($total_saturday_hours - $saturday_morning) < 0) ? 0 : ($total_saturday_hours - $saturday_morning)), 2),
            'sunday_morning' => $sunday_morning,
            'sunday_night' => round(((($total_sunday_hours - $sunday_morning) < 0) ? 0 : ($total_sunday_hours - $sunday_morning)), 2),
            'ph_morning' => $ph_morning,
            'ph_night' => round(((($total_ph_hours - $ph_morning) < 0) ? 0 : ($total_ph_hours - $ph_morning)), 2),

        ];
    }

    public function checkGuardDocumentsamg($guard, $bypass = true)
    {
        $status = '';
        $guard = GuardWorkDetail::where('guard_id', $guard->id)->first();

        if(empty($guard->guard_document_type)){
            $status = "Please First Add Your Residential Status!";
            return $status;
        }

        $today = date("Y/m/d");
        $today_time = strtotime($today);
        
        $guard = Guard::where('id', $guard->id)->with('guardDocuments')->first();

    foreach ($guard->guardDocuments as $key => $value){
    
            if($value->document_type == 'visa' && ($value->document_expire == '' || $value->document_expire == null || $today_time > strtotime($value->document_expire))){
                $status = "Visa Expired!";
                break;
            }elseif($value->document_type == 'passport' && ($value->document_expire == '' || $value->document_expire == null || $today_time > strtotime($value->document_expire))){
                $status = "Passport Expired!";
                break;
            }elseif($value->document_type == 'driver_license_front' && ($value->document_expire == '' || $value->document_expire == null || $today_time > strtotime($value->document_expire))){
                $status = "Driver License Expired!";
                break;
            }elseif($value->document_type == 'security_license' && ($value->document_expire == '' || $value->document_expire == null)){
                if($value->document_expire != 'current, pending renewal' && $today_time > strtotime($value->document_expire)){
                    $status = "Security License Expired!";
                    break;
                }
            }else{
                $status = 'active'; 
            }

        }
        return $status;
     
    }

    public function checkGuardLeaves($guard_id, $start, $end)
    {
        $leaves = DB::table('guard_leave_requests')
        ->where('start', '<=', strtotime($start))
        ->where('end', '>=', strtotime($start))
        ->where('guard_id', '=', $guard_id)
        ->where('status', '=', 'approved')->first();
        if (!empty($leaves)) {
            return false;
        } else {
            $leaves = DB::table('guard_leave_requests')
            ->where('start', '<=', strtotime($end))
            ->where('end', '>=', strtotime($end))
            ->where('guard_id', '=', $guard_id)
            ->where('status', '=', 'approved')->first();
            if (!empty($leaves)) {
                return false;
            } else {
                return true;
            }
        }
    }

    function checkGuardLastShift($guard_id, $start, $end, $id = null)
    {
        // check is there any shift start in last 24 hours
        $last_24_hours = time() - (60 * 60 * 24);
        $roster = JobRoster::where('start', '>=', date('Y-m-d H:i:s', $last_24_hours))
        ->where('start', '<', $start)
        ->where('guard_id', '=', $guard_id);

        if ($id != null && $id > 0) {
            $roster->where('id', '!=', $id);
        }
        $roster = $roster->first();

        if (empty($roster)) {
            $last_24_hours = time() - (60 * 60 * 24);
            $roster1 = JobRoster::where('end', '>=', date('Y-m-d H:i:s', $last_24_hours))
            ->where('end', '<', $start)
            ->where('guard_id', '=', $guard_id);

            if ($id != null && $id > 0) {
                $roster1->where('id', '!=', $id);
            }
            $roster = $roster1->first();
        }

        $last_shift_duration = 0;
        $time_diff_between_last_shift_and_current = 10;
        if (!empty($roster) && $roster[0] != null) {
            $last_shift_duration = ($roster->job_end - $roster->job_start) / (60 * 60);
            $time_diff_between_last_shift_and_current = (strtotime($start) - $roster[0]['job_end']) / (60 * 60);
        }

        $next_roster = JobRoster::where('start', '>', $end)
        ->where('guard_id', '=', $guard_id);
        if ($id != null && $id > 0) {
            $next_roster->where('id', '!=', $id);
        }
        $next_roster = $next_roster->first();

        $next_shift_duration = 0;
        $time_diff_between_next_shift_and_current = 10;
        if (!empty($next_roster)) {
            $next_shift_duration = ($next_roster->job_end - $next_roster->job_start) / (60 * 60);
            $time_diff_between_next_shift_and_current = ($next_roster->job_start - strtotime($end)) / (60 * 60);
        }
        $today_start = strtotime(date('m/d/Y', strtotime($start)));
        $today_end = strtotime(date('m/d/Y 23:59:59', strtotime($start)));
        if (strtotime($end) > $today_end) {
            $current_shift_today_duration = ($today_end - strtotime($start)) / (60 * 60);
        } else {
            $current_shift_today_duration = (strtotime($end) - strtotime($start)) / (60 * 60);
        }
        $today_working_hours = 0;
        $jobs_today = JobRoster::where('start', '=', Date('Y-m-d', strtotime($start)))
        ->where('guard_id', '=', $guard_id);
        if ($id != null && $id > 0) {
            $jobs_today->where('id', '!=', $id);
        }
        $jobs_today = $jobs_today->get();


        $jobs_today1 = JobRoster::where('start', '!=', Date('Y-m-d', strtotime($start)))
        ->where('end', '=', Date('Y-m-d', strtotime($start)))
        ->where('guard_id', '=', $guard_id);
        if ($id != null && $id > 0) {
            $jobs_today1->where('id', '!=', $id);
        }
        $jobs_today1 = $jobs_today1->get();
        $jobs_today = $jobs_today->merge($jobs_today1);

        foreach ($jobs_today as $jt) {
            if ($jt->job_start < $today_start) {
                $jt->job_start = $today_start;
            }
            if ($jt->job_end > $today_end) {
                $jt->job_end = $today_end;
            }
            $today_working_hours += (($jt->job_end - $jt->job_start) / (60 * 60));
        }

        if ($time_diff_between_last_shift_and_current < 8 || $time_diff_between_next_shift_and_current < 8) {
            return array('status' => false);
        } else {
            return array('status' => true);
        }
    }

    function get_current_month_hours_amg_guards($guardId, $start, $end, $shifIsPressed, $id = 0 )
    {
        $month = date('m');
        $year = date('Y');
        if($shifIsPressed == false){
            $data = jobroster::where('guard_id', $guardId)
            ->where('start', '>=', date('Y-m-d H:i', strtotime($start)))
            ->where('start', '<=', date('Y-m-d 23:59', strtotime($end)))
            ->where('id', '!=', $id)
            ->get();
        }else{
            $data = jobroster::where('guard_id', $guardId)
            ->where('start', '>=', date('Y-m-d H:i', strtotime($start)))
            ->where('start', '<=', date('Y-m-d 23:59', strtotime($end)))
            ->get();

        }
        return $data;
    }

    public function availablity($start, $end, $guardId, $jobId, $id)
    {

        if (empty($end)) {

            $end = $start;
        }

        // $sqlQuery = "SELECT * FROM `job_new_roster` WHERE `event_id`!='".$eventId."' AND `guard_id` = '".$guardId."' AND (temp_start BETWEEN '".$start."' AND '".$end."')";
        $data = jobroster::where('id', '!=', $id)
        ->where('guard_id', '=', $guardId)
        ->whereBetween('start', [$start, $end])->first();

        if (empty($data)) {


            $data = jobroster::where('id', '!=', $id)
            ->where('guard_id', '=', $guardId)
            ->whereBetween('end', [$start, $end])->first();
        }
        if (empty($data)) {

            $data = jobroster::where('id', '!=', $id)
            ->where('guard_id', '=', $guardId)
            ->where('start', '<=', $start)
            ->where('end', '>=', $start)
            ->first();
        }
        if (empty($data)) {

            $data = jobroster::where('id', '!=', $id)
            ->where('guard_id', '=', $guardId)
            ->where('start', '<=', $end)
            ->where('end', '>=', $end)
            ->first();
        }

        if (!empty($data)) {
            $site = DB::table('sites')->where('id', $data->site_id)->first();
            $flag['status'] = 1;
            $flag['site_name'] = $site->site_name. '('.$site->site_description.')';
            $flag['time'] = date('d/m/Y H:i', strtotime($data->start)) .' - '.date('d/m/Y H:i', strtotime($data->end));
        } else {
            $flag['status'] = 0;
        }

        return $flag;
    }

    public function rosterData($id, $guardId = 0)
    {

        $data = jobroster::where('id', $id);
        if ($guardId) {
            $data->where("guard_id", $guardId);
        }
        $data = $data->first();

        return $data;
    }
    function get_current_month_hours_amg_guards_part_student($guardId, $start, $end, $shifIsPressed, $event_id = 0 )
    {
        $month = date('m');
        $year = date('Y');
        // whereMonth('temp_date', $month)
        // ->whereYear('temp_date', $year)
        $startOfWeek = date('Y-m-d', strtotime('monday this week', strtotime($start)));
        $endOfWeek = date('Y-m-d', strtotime('sunday this week', strtotime($end)));

        if($shifIsPressed == false){
            $data = jobroster::where('guard_id', $guardId)
                ->where('start', '>=', date('Y-m-d H:i', strtotime($start)))
                ->where('start', '<=', date('Y-m-d 23:59', strtotime($end)))
                ->where('end', '>=', date('Y-m-d H:i', strtotime($start)))
                ->where('end', '<=', date('Y-m-d 23:59', strtotime($end)))
                ->where('id', '!=', $event_id)
                ->whereRaw("DAYOFWEEK(start) != 1") // Exclude Sundays
                ->get();
        } else {
            $data = jobroster::where('guard_id', $guardId)
                ->where('start', '>=', date('Y-m-d H:i', strtotime($start)))
                ->where('start', '<=', date('Y-m-d 23:59', strtotime($end)))
                ->where('end', '>=', date('Y-m-d H:i', strtotime($start)))
                ->where('end', '<=', date('Y-m-d 23:59', strtotime($end)))
                ->whereRaw("DAYOFWEEK(start) != 1") // Exclude Sundays
                // ->where('event_id', '=', $event_id)
                ->get();
        }
        // $data = DB::statement("SELECT * FROM `job_new_roster` WHERE MONTH(temp_date) = '".$month."' AND YEAR(temp_date) = '". $year."' AND `guard_id` = ".$guardId." AND (temp_start BETWEEN '".$start."' AND '".$end."') AND (temp_end BETWEEN '".$start."' AND '".$end."')");
        return $data;
    }

    public function eventUpdateAmgGuardDrop($rosterId, $start, $end, $temp_date, $temp_start, $temp_end, $guardId, $tasks = null, $operators_notes = null, $status = null,$adhoc_shift = 'no', $payable = 'yes', $chargeable = 'yes', $custom_rates = 'no', $payrate_id = 0, $chargerate_id = 0, $publish_status = 0, $training = 0, $continuation = 0, $travel_time = 0, $paid_by = '',  $notify = 'false', $public_holiday = 0, $travel_time_payable = 'yes', $travel_time_chargeable = 'yes', $covid_marshal = 0, $unprofile_name = '', $travel_time_amount = 0, $overtime = 0, $overtime_value = 1, $travel_time_amount_chargeable = 0, $unpublish_shift = 0, $job_instrcutions = '', $conflict = 0, $conflict_message = 'N/A', $chargerate_level, $payrate_level)
{
    $rosterdata = jobroster::where(array('id' => $rosterId))->first();

    $current_guard = $guardId;

    $data = array(

        "start" => $start,

        "end" => $end,

        "temp_date" => $temp_date,

        "temp_start" => $temp_start,

        "temp_end" => $temp_end,

        "add_status" => 1,

        "guard_id" => $guardId,

        'job_start' => strtotime($temp_start),

        'job_end' => strtotime($temp_end),

            // 'record_update' => 1,
        // 'job_status' => 'pending',
        'payable' => $payable,
            // 'publish_status' => 0,
        'chargeable' => $chargeable,
        'custom_rates' => $custom_rates,
        'payrate_id' => $payrate_id,
        'chargerate_id' => $chargerate_id,
            // 'publish_status'=> $publish_status,
        'training' => $training,
            // 'continuation' => $continuation,
        'continuation' => 1,
        'travel_time' => $travel_time,
        'paid_by' => $paid_by,
        'public_holiday' => $public_holiday,
        'travel_time_payable' => $travel_time_payable,
        'travel_time_chargeable' => $travel_time_chargeable,
        'covid_marshal' => $covid_marshal,
        'unprofile_name' => $unprofile_name,
        'overtime' => $overtime,
        'travel_time_amount' => $travel_time_amount,
        'travel_time_amount_chargeable' => $travel_time_amount_chargeable,
        'overtime_value' => $overtime_value,
        'unpublish_shift' => $unpublish_shift,
        'conflict' => $conflict,
        'conflict_message' => $conflict_message,
        'chargerate_level' => $chargerate_level,
        'payrate_level' => $payrate_level

    );
    if ($job_instrcutions != '') {
        $data['job_instrcutions'] = $job_instrcutions;
    }
    $data['hours'] = round(abs($data['job_end'] - $data['job_start']) / 3600, 2);

    if ($guardId == 0 || $guardId == '') {
        $data['job_status'] = 'pending';
    }
    $new_record = jobnewroster::where('id', $rosterId)->first();
    $new_guard = $new_record->guard_id;

    
    if ($rosterdata->signin_status == 1 || $rosterdata->job_status == 'completed') {
        $data['job_status'] = $rosterdata->job_status;
        $data['publish_status'] = $rosterdata->publish_status;
    } elseif ($notify == 'true') {
        $data['record_update'] = 1;
        $data['job_status'] = $rosterdata->job_status;
        $data['publish_status'] = $publish_status;
    } elseif ($unpublish_shift == 1) {
        $data['publish_status'] = 1;
    }
    if (isset($data['publish_status']) && $data['publish_status'] == 1) {
        $data['record_update'] = 0;
    }
    if (($rosterdata->job_start != $data['job_start'] || $rosterdata->job_end != $data['job_end']) && $rosterdata->job_status != 'completed') {
        $data['publish_status'] = 0;
    }

     if($rosterdata->job_status == 'rejected' && $guard_id > 0)
        {
            $data['job_status'] = 'pending';
        }
    $action = 'shift_change';
    // $this->administrator->log_user_activity($action, $new_record);
        // $new_guard=$new_record->guard_id;

    if ($current_guard != $new_guard || $rosterdata->job_start != $data['job_start'] || $rosterdata->job_end != $data['job_end']) {
            // $data['publish_status'] = 0;


            //    $guard_added=DB::table('guards')->where('id',$new_guard)->select('id','name', 'email')->first();
            //    $guard_removed=DB::table('guards')->where('id',$current_guard)->select('id','name', 'email')->first();


            //    $message='<div style="font-family:Arial,Helvetica,sans-serif; line-height: 1.5; font-weight: normal; font-size: 15px; color: #2F3044; min-height: 100%; margin:0; padding:0; width:100%; background-color:#edf2f7">
            //    <br><table align="center" border="0" cellpadding="0" cellspacing="0" width="100%" style="border-collapse:collapse;margin:0 auto; padding:0; max-width:600px">
            //        <tbody><tr><td align="center" valign="center" style="text-align:center; padding: 40px"><a href="'.config('custom.logo').'" rel="noopener" target="_blank"><img src="'.config('custom.logo').'" style="height: 45px" alt="logo"></a></td></tr><tr>
            //        <td align="left" valign="center">
            //            <div style="text-align:left; margin: 0 20px; padding: 40px; background-color:#ffffff; border-radius: 6px"><!--begin:Email content--><div style="padding-bottom: 30px; font-size: 17px;"><strong>Guard Rostered</strong></div>';
            //   $message.='<div style="padding-bottom: 30px">You are rostered on a new Shift. </div>
            //   <div style="padding-bottom: 40px; text-align:center;"></div>';

            //   $message2='<div style="font-family:Arial,Helvetica,sans-serif; line-height: 1.5; font-weight: normal; font-size: 15px; color: #2F3044; min-height: 100%; margin:0; padding:0; width:100%; background-color:#edf2f7">
            //   <br><table align="center" border="0" cellpadding="0" cellspacing="0" width="100%" style="border-collapse:collapse;margin:0 auto; padding:0; max-width:600px">
            //       <tbody><tr><td align="center" valign="center" style="text-align:center; padding: 40px"><a href="'.config('custom.logo').'" rel="noopener" target="_blank"><img src="'.config('custom.logo').'" style="height: 45px" alt="logo"></a></td></tr><tr>
            //       <td align="left" valign="center">
            //           <div style="text-align:left; margin: 0 20px; padding: 40px; background-color:#ffffff; border-radius: 6px"><!--begin:Email content--><div style="padding-bottom: 30px; font-size: 17px;"><strong>Guard Removed</strong></div>';
            //  $message2.='<div style="padding-bottom: 30px">You have been removed from a Shift. </div>
            //  <div style="padding-bottom: 40px; text-align:center;"></div>';

            //   $notification_data['added'] = 'Roster Added';
            //   $notification_data['removed'] = 'Roster Remove';
            // if ($new_guard > 0 && !empty($guard_added)) {
            //   $this->sendGuardMail($guard_added,  $notification_data['added']  ,$message);
            //   }
            //   if ($current_guard > 0 && !empty($guard_removed)) {
            //    $this->sendGuardMail($guard_removed,  $notification_data['removed']  ,$message2);   
            //   }


    }
    // if ($notify == 'true' && $data['record_update'] == 1 && $data['publish_status'] == 0 && $data['guard_id'] > 0 && $rosterdata->publish_status == 1 && $current_guard == $new_guard) {
    //     $guard_data = DB::table('guards')->where('id', $data['guard_id'])->first();
    //     $notification_data['guards'][0] = array(
    //         'guard_id' => $guard_data->id,
    //         'notification_token' => $guard_data->notification_token
    //     );
    //     $notification_data['title'] = 'Shift Updated';
    //     $notification_data['message'] = 'One of your shift updated.';
    //     $notification_data['page'] = 'homepage';
    //     $res = $this->guard_model->send_push_notification($notification_data);
    //     $data['publish_status'] = 1;
    // }
//     if ($current_guard != $new_guard && $new_guard > 0 && $rosterdata->publish_status == 1) {
//      $guard_data = DB::table('guards')->where('id', $new_guard)->first();
//      $notification_data['guards'][0] = array(
//         'guard_id' => $guard_data->id,
//         'notification_token' => $guard_data->notification_token
//     );
//      $notification_data['title'] = 'Shift Removed';
//      $notification_data['message'] = 'One of your shift is removed.';
//      $notification_data['page'] = 'homepage';
//      $res = $this->guard_model->send_push_notification($notification_data);
//      $data['publish_status'] = 0;
//      $data['job_status'] = 'pending';
//  }
 jobnewroster::where('id', $rosterId)->update($data);
 $roster_id = $rosterdata->roster_id;
 $this->calculateChargeRate($roster_id);
 DB::table('roster_complete_activity')->insert([
    'roster_id' => $roster_id,
    'activity' => 'Shift edited',
    'type' => 'shift_edit',
    'record_id' => $roster_id,
    'activity_time' => time(),
    'activity_by' => session('userId')
]);
 $tasks = is_array($tasks) ? $tasks : json_decode($tasks, true);
 if (is_array($tasks) && !empty($tasks)) {
    foreach ($tasks as $t) {

        if (isset($t['task_id']) && $t['task_id'] != '') {
            DB::table('job_roster_tasks')->where('id', $t['task_id'])->update(
                array(
                    'roster_id' => $roster_id,
                    'guard_id' => $guardId,
                    'task_name' => $t['task_description'],
                    'task_time' => $t['task_start_time'],
                    'status' => 'pending'
                )
            );
        } else {
            DB::table('job_roster_tasks')->insert(
                array(
                    'roster_id' => $roster_id,
                    'guard_id' => $guardId,
                    'task_name' => $t['task_description'],
                    'task_time' => $t['task_start_time'],
                    'status' => 'pending'
                )
            );
            // if ($unpublish_shift == 0 && $guard_id > 0) {
            //     $guard_data = DB::table('guards')->where('id', $guard_id)->first();
            //     $notification_data['guards'][0] = array(
            //         'guard_id' => $guard_data->id,
            //         'notification_token' => $guard_data->notification_token
            //     );
            //     $notification_data['title'] = 'New task added';
            //     $notification_data['message'] = 'New Task - New task is added in your task';
            //     $notification_data['page'] = 'homepage';
            //     $res = $this->guard_model->send_push_notification($notification_data);
            // }
        }
    }
}
$this->updateHoursDistribution($temp_start, $temp_end, $rosterdata->site_id, $rosterdata->roster_id);
return true;
}

    public function guard_function($formData)
    {
        $flag = $this->availablity($formData->tempDate, $formData->end, $formData->guard_id, $formData->site_id, $formData->id);
        if ($flag['status']) {
            $formData->conflict = 1;
            $formData->conflict_message = 'These timings are contradicting with '.$flag['site_name'].' timings ('.$flag['time'].') because this guard is already added in another site.';
        }else{
            if ($formData->conflict == 0) {
                $formData->conflict = 0;
                $formData->conflict_message = '';
            }
        }
        // if ($flag) {
        //     $error["message"] = "already";

        //     // return $error;
        //     // exit();
        // } else 
        if(true){
            if (!empty($formData->save)) {
                $roster = jobroster::where('id', $formData->id)->first();

                if (empty($formData->end) || $formData->end == 'Invalid date') {
                    $formData->end = $formData->start;
                }
                // if (empty($formData->temp_end)) {
                //     $formData->temp_end = $formData->temp_start;
                // }
                $rosterData = $this->rosterData($formData->id, $formData->guard_id);

                if (!empty($rosterData)) {
                    if (isset($formData->shifIsPressed) && $formData->shifIsPressed == 'true') {
                        if ($formData->has('id') && $formData->id > 0) {
                            $tasks = DB::table('job_roster_tasks')->join('job_rosters', 'job_rosters.id', '=', 'job_roster_tasks.roster_id')->where('job_rosters.id', '=', $formData->id)->select('job_roster_tasks.task_name as task_description', 'job_roster_tasks.task_time as task_start_time')->get();
                            $formData->tasks = json_encode($tasks);
                        }

                        $eventStatus = $this->addEvent(0, $formData->start, $formData->end, $formData->guard_id, $formData->site_id, $formData->tempDate, $formData->start, $formData->end, 0, $formData->tasks,$formData->adhoc_shift, $formData->shift_payable, $formData->shift_chargeable, $formData->custome_rate, $formData->payrate, $formData->chargerate, $formData->publish_status, $formData->training, $formData->continuation, $formData->travel_time, $formData->paid_by, $formData->public_holiday, $formData->travel_time_payable, $formData->travel_time_chargeable, $formData->covid_marshal, $formData->unprofile_name, $formData->travel_time_amount, $formData->over_time, $formData->over_time_value, $formData->travel_time_amount_chargeable, $formData->unpublish_shift, $formData->multiple_shifts_count, $formData->job_instrcutions, $formData->conflict, $formData->conflict_message, $formData->chargerate_level, $formData->payrate_level, $formData->admin_id,$formData->roster_id, $formData->admin_confirm);
                        if ($eventStatus) {
                            $action = 'shift_drag_copy';
                            // $this->administrator->log_user_activity($action, $rosterData);
                        }
                    } else {
                        // $this->guard_model->log_user_activities_in_roster($formData->eventId, 'shift_change');

                        $eventStatus = $this->eventUpdateDrop($formData->id, $formData->start, $formData->end, $formData->tempDate, $formData->start, $formData->end, $formData->guard_id, $formData->tasks, $formData->operators_notes, null,$formData->adhoc_shift, $formData->shift_payable, $formData->shift_chargeable, $formData->custome_rate, $formData->payrate, $formData->chargerate, $formData->publish_status, $formData->training, $formData->continuation, $formData->travel_time, $formData->paid_by, $formData->notify, $formData->public_holiday, $formData->travel_time_payable, $formData->travel_time_chargeable, $formData->covid_marshal, $formData->unprofile_name, $formData->travel_time_amount, $formData->overtime, $formData->overtime_value, $formData->travel_time_amount_chargeable, $formData->unpublish_shift, $formData->job_instrcutions, $formData->conflict, $formData->conflict_message, $formData->chargerate_level, $formData->payrate_level, $formData->admin_id,$formData->roster_id, $formData->admin_confirm);
                    }
                    if ($eventStatus) {
                        // $this->guard_model->log_user_activities_in_roster($formData->eventId, 'shift_add');
                        $status = [
                            "message" => "Shift updated successfully",
                            "success" => true
                        ];
                    } else {
                        $status = [
                            "message" => "Failed to updated shift",
                            "success" => false
                        ];
                    }
                    
                        return $status;
                } elseif ($formData->guard_id != '' && $formData->id > 0 && !empty($roster)) {
                    if (isset($formData->shifIsPressed) && $formData->shifIsPressed == 'true') {
                        if ($formData->has('id') && $formData->id > 0) {
                            $tasks = DB::table('job_roster_tasks')->join('job_rosters', 'job_rosters.id', '=', 'job_roster_tasks.roster_id')->where('job_rosters.id', '=', $formData->id)->select('job_roster_tasks.task_name as task_description', 'job_roster_tasks.task_time as task_start_time')->get();
                            $formData->tasks = json_encode($tasks);
                        }

                        // $this->guard_model->log_user_activities_in_roster($formData->eventId, 'shift_add')
                        $eventStatus = $this->addEvent(0, $formData->start, $formData->end, $formData->guard_id, $formData->site_id, $formData->tempDate, $formData->start, $formData->end, 0, $formData->tasks,$formData->adhoc_shift, $formData->shift_payable, $formData->shift_chargeable, $formData->custome_rate, $formData->payrate, $formData->chargerate, $formData->publish_status, $formData->training, $formData->continuation, $formData->travel_time, $formData->paid_by, $formData->public_holiday, $formData->travel_time_payable, $formData->travel_time_chargeable, $formData->covid_marshal, $formData->unprofile_name, $formData->travel_time_amount, $formData->over_time, $formData->over_time_value, $formData->travel_time_amount_chargeable, $formData->unpublish_shift, $formData->multiple_shifts_count, $formData->job_instrcutions, $formData->conflict, $formData->conflict_message,$formData->chargerate_level, $formData->payrate_level, $formData->admin_id, $formData->roster_id, $formData->admin_confirm);
                        if ($eventStatus) {
                            $action = 'shift_drag_copy';
                            // $this->administrator->log_user_activity($action, $rosterData);
                        }
                    } else {
                        // $this->guard_model->log_user_activities_in_roster($formData->eventId, 'guard_assign');

                        $eventStatus = $this->eventUpdateGuardDrop($formData->id, $formData->start, $formData->end, $formData->tempDate, $formData->start, $formData->end, $formData->guard_id, $formData->tasks, $formData->operators_notes, null,$formData->adhoc_shift, $formData->shift_payable, $formData->shift_chargeable, $formData->custome_rate, $formData->payrate, $formData->chargerate, $formData->publish_status, $formData->training, $formData->continuation, $formData->travel_time, $formData->paid_by, $formData->notify, $formData->public_holiday, $formData->travel_time_payable, $formData->travel_time_chargeable, $formData->covid_marshal, $formData->unprofile_name, $formData->travel_time_amount, $formData->overtime, $formData->overtime_value, $formData->travel_time_amount_chargeable, $formData->unpublish_shift, $formData->job_instrcutions, $formData->conflict, $formData->conflict_message, $formData->chargerate_level, $formData->payrate_level, $formData->admin_id);
                    }
                    if ($eventStatus) {
                        // $this->guard_model->log_user_activities_in_roster($formData->eventId, 'shift_add');
                        $status = [
                            "message" => "Shift updated successfully",
                            "success" => true
                        ];
                    } else {
                        $status = [
                            "message" => "Failed to update shift",
                            "success" => false
                        ];
                    }
                    
                        return $status;
                } else {

                    $eventStatus = $this->addEvent($formData->eventId, $formData->start, $formData->end, $formData->guard_id, 
                    $formData->site_id, $formData->tempDate, $formData->start, $formData->end, 0, $formData->tasks,
                    $formData->adhoc_shift, $formData->shift_payable, $formData->shift_chargeable, $formData->custome_rate, $formData->payrate, 
                    $formData->chargerate, $formData->publish_status, $formData->training, $formData->continuation, $formData->travel_time, 
                    $formData->paid_by, $formData->public_holiday, $formData->travel_time_payable, $formData->travel_time_chargeable, 
                    $formData->covid_marshal, $formData->unprofile_name, $formData->travel_time_value, $formData->over_time, $formData->over_time_value,
                     $formData->travel_time_amount_chargeable, $formData->unpublish_shift, $formData->multiple_shifts_count, 
                     $formData->job_instrcutions, $formData->conflict, $formData->conflict_message,$formData->chargerate_level, $formData->payrate_level, $formData->admin_id, $formData->roster_id, $formData->admin_confirm);
                    if ($eventStatus) {
                        // $this->guard_model->log_user_activities_in_roster($formData->eventId, 'shift_add');
                        $status = [
                            "message" => "Shift added successfully",
                            "success" => true
                        ];
                    } else {
                        $status = [
                            "message" => "Failed to add shift",
                            "success" => false
                        ];
                    }
                    return $status;
                    // exit;
                }
            } else {
                $error = [
                    "message" => "Failed to add shift",
                    "success" => false
                ];
            }
        }
        return $error;
    }
    public function eventUpdateDrop($rosterId, $start, $end, $temp_date, $temp_start, $temp_end, $guard_id = null, $tasks = null, $operators_notes = null, $status = null,$adhoc_shift = 'no', $payable = 'yes', $chargeable = 'yes', $custom_rates = 0 , $payrate_id = 0, $chargerate_id = 0, $publish_status = 0, $training = 0, $continuation = 0, $travel_time = 0, $paid_by = '', $notify = 'false', $public_holiday = 0, $travel_time_payable = 'yes', $travel_time_chargeable = 'yes', $covid_marshal = 0, $unprofile_name = '', $travel_time_amount = 0, $overtime = 0, $overtime_value = 1, $travel_time_amount_chargeable = 0, $unpublish_shift = 0, $job_instrcutions = '', $conflict = 0, $conflict_message = 'N/A', $chargerate_level, $payrate_level, $admin_id, $roster_id, $admin_confirm)
    {

        $rosterdata =  DB::table('job_rosters')->where(array('id' => $rosterId))->first();
        $action = 'shift_change';
        $current_guard = $guard_id;
        // $this->administrator->log_user_activity($action, $rosterdata);
        $dateEnd = DateTime::createFromFormat('d-m-Y H:i', $end);
        if ($dateEnd) {
            $formattedEnd = $dateEnd->format('Y-m-d H:i');
        } else {
            $formattedEnd = null;
        }
        $dateStart = DateTime::createFromFormat('d-m-Y H:i', $start);
        if ($dateStart) {
            $formattedStart = $dateStart->format('Y-m-d H:i');
        } else {
            $formattedStart = null;
        }

        if(isset($rosterId) && $rosterId > 0){
            $addNewShift = JobRoster::findOrFail($rosterId);
            $start_time = dbFormateDateTime($formattedStart);
            $end_time = dbFormateDateTime($formattedEnd);
            $conflictingShift = JobRoster::where('guard_id', $guard_id)
            ->where(function ($query) use ($start_time, $end_time) {
                $query->where(function ($q) use ($start_time) {
                    $q->where('start', '<=', $start_time)->where('end', '>=', $start_time);
                })->orWhere(function ($q) use ($end_time) {
                    $q->where('start', '<=', $end_time)->where('end', '>=', $end_time);
                })->orWhere(function ($q) use ($start_time, $end_time) {
                    $q->where('start', '>=', $start_time)->where('end', '<=', $end_time);
                });
            })
            ->where('id', '!=' ,$addNewShift->id)->select('id', 'start', 'end')->first();
        }else{
            $start_time = dbFormateDateTime($formattedStart);
            $end_time = dbFormateDateTime($formattedEnd);
            $conflictingShift = JobRoster::where('guard_id', $guard_id)
            ->where(function ($query) use ($start_time, $end_time) {
                $query->where(function ($q) use ($start_time) {
                    $q->where('start', '<=', $start_time)->where('end', '>=', $start_time);
                })->orWhere(function ($q) use ($end_time) {
                    $q->where('start', '<=', $end_time)->where('end', '>=', $end_time);
                })->orWhere(function ($q) use ($start_time, $end_time) {
                    $q->where('start', '>=', $start_time)->where('end', '<=', $end_time);
                });
            })
            ->select('id', 'start', 'end')->first();
        }

        $hours = $this->getShiftHours($start_time, $end_time, $rosterdata->site_id, 1, $public_holiday, $public_holiday);

        $data = array(

            'morning_hours' => (!empty($hours['morning']) ? roundHours($hours['morning']) : 0.0),
            'night_hours' => (!empty($hours['night']) ? roundHours($hours['night']) : 0.0),
            'saturday_morning_hours' => (!empty($hours['saturday_morning']) ? roundHours($hours['saturday_morning']) : 0.0),
            'saturday_night_hours' => (!empty($hours['saturday_night']) ? roundHours($hours['saturday_night']) : 0.0),
            'sunday_morning_hours' => (!empty($hours['sunday_morning']) ? roundHours($hours['sunday_morning']) : 0.0),
            'sunday_night_hours' => (!empty($hours['sunday_night']) ? roundHours($hours['sunday_night']) : 0.0),
            'ph_morning_hours' => (!empty($hours['ph_morning']) ? roundHours($hours['ph_morning']) : 0.0),
            'ph_night_hours' => (!empty($hours['ph_night']) ? roundHours($hours['ph_night']) : 0.0),

            "start" => $formattedStart,

            "end" => $formattedEnd,

            "temp_date" => $temp_date,

            "roster_id" => $roster_id,

            // "add_status" => 1,

            // 'job_start' => strtotime($temp_start),

            // 'job_end' => strtotime($temp_end),

            // 'record_update' => 1,
            // 'job_status' => 'pending',
            'adhoc_shift' => $adhoc_shift,
            'travel_time_value' => $travel_time_amount,
            'shift_payable' => !empty($payable) ? $payable : 'yes',
            // 'publish_status' => 0,
            'shift_chargeable' => !empty($chargeable) ? $chargeable : 'yes',
            'custome_rate' => $custom_rates,
            'payrate' => $payrate_id,
            'chargerate' => $chargerate_id,
            'publish_status'=> $publish_status,
            // 'training' => (int)$training,
            'continuation' => 1,
            'travel_time' => $travel_time,
            // 'paid_by' => $paid_by,
            'public_holidays' => $public_holiday,
            // 'travel_time_payable' => $travel_time_payable,
            // 'travel_time_chargeable' => $travel_time_chargeable,
            'covid_marshal' => $covid_marshal,
            'unprofile_name' => $unprofile_name,
            
            'conflict' => (!empty($conflictingShift) ? 'conflict in '.getRosterdName($roster_id) : null),
            'conf_end' => (!empty($conflictingShift) ? dbFormateDateTime($conflictingShift->end) : ''),
            'conf_start' => (!empty($conflictingShift) ? dbFormateDateTime($conflictingShift->start) : ''),
            'conflicted_with' => (!empty($conflictingShift) ? $conflictingShift->id : null),
            'chargerate_level' => $chargerate_level,
            'payrate_level' => $payrate_level,
            'last_update' => time()

        );
        if ($job_instrcutions != '') {
            $data['job_instrcutions'] = $job_instrcutions;
        }

        $data['hours'] = $hours['morning'] + $hours['night'] + $hours['saturday_morning'] + $hours['saturday_night'] + $hours['sunday_morning'] + $hours['sunday_night'] + $hours['ph_morning'] + $hours['ph_night'];

        if ($guard_id == 0 || $guard_id == '') {
            $data['job_status'] = 'pending';
        }
       
        $data['guard_id'] = $guard_id;
        
        $new_guard = $rosterdata->guard_id;
       
        if($admin_confirm == 1 && $guard_id > 0){
            $data['job_status'] = 'confirmed';
            $data['admin_confirm'] = 1;
            $data['publish_status'] = 1;
        }else{
            $data['job_status'] = 'pending';
            $data['publish_status'] = $publish_status;
            $data['admin_confirm'] = 0;
        }
        if ($guard_id == 0 || $guard_id == '' || $guard_id == NULL || $guard_id == null) {
            $data['job_status'] = 'pending';
            $data['publish_status'] = $publish_status;
            $data['admin_confirm'] = 0;
        }
        if ($rosterdata->signin_status == 1 || $rosterdata->job_status == 'completed') {
            $data['job_status'] = $rosterdata->job_status;
            $data['publish_status'] = $rosterdata->publish_status;
            $data['admin_confirm'] = 0;
        } 
        if($rosterdata->job_status == 'rejected'){
            $data['job_status'] = 'pending';
            $data['rejected_by'] = null;
        }
        // elseif ($notify == 'true') {
        //     // $data['record_update'] = 1;
        //     $data['publish_status'] = $publish_status;
        //     $data['job_status'] = $rosterdata->job_status;
        // } 
        elseif ($unpublish_shift == 1) {
            $data['publish_status'] = 1;
        }

        if ($notify == 'true' && $data['guard_id'] > 0 && $rosterdata->publish_status == 1 && $current_guard == $new_guard) {
            $guard_data = DB::table('guards')->where('id', $data['guard_id'])->first();
            $notification_data['guards'][0] = array(
                'guard_id' => $guard_data->id,
                'notification_token' => $guard_data->notification_token
            );
            $notification_data['title'] = 'Shift Updated';
            $notification_data['message'] = 'One of your shift updated.';
            $notification_data['page'] = 'homepage';
            // $res = $this->guard_model->send_push_notification($notification_data);
            $data['publish_status'] = 1;
        }
        if ($current_guard != $new_guard && $new_guard > 0 && $rosterdata->publish_status == 1) {
         $guard_data = DB::table('guards')->where('id', $new_guard)->first();
         $notification_data['guards'][0] = array(
            'guard_id' => $guard_data->id,
            'notification_token' => $guard_data->notification_token
        );
         $notification_data['title'] = 'Shift Removed';
         $notification_data['message'] = 'One of your shift is removed.';
         $notification_data['page'] = 'homepage';
     }
     DB::table('job_rosters')->where('id', $rosterId)->update($data);
     $roster_id = $rosterdata->id;
     $this->calculateChargeRate($roster_id);
     if ($guard_id == null) {
        $guard_id = 0;
    }
    $tasks = is_array($tasks) ? $tasks : json_decode($tasks, true);
    if (is_array($tasks) && !empty($tasks)) {
        foreach ($tasks as $t) {
            if (isset($t['id']) && $t['id'] != '') {
                DB::table('job_roster_tasks')->where('id', $t['id'])->update(
                    array(
                        'job_roster_id' => $roster_id,
                        // 'guard_id' => $guard_id,
                        'task' => $t['task'],
                        'task_start' => $t['task_start'],
                        'task_end' => $t['task_end'],
                        'status' => 'pending'
                    )
                );
            } else {
                DB::table('job_roster_tasks')->insert(
                    array(
                        'job_roster_id' => $roster_id,
                        // 'guard_id' => $guard_id,
                        'task' => $t['task'],
                        'task_start' => $t['task_start'],
                        'task_end' => $t['task_end'],
                        'status' => 'pending'
                    )
                );
                if ($unpublish_shift == 0 && $guard_id > 0) {
                    $guard_data = DB::table('guards')->where('id', $guard_id)->first();
                    $notification_data['guards'][0] = array(
                        'guard_id' => $guard_data->id,
                        'notification_token' => $guard_data->notification_token
                    );
                    $notification_data['title'] = 'New task added';
                    $notification_data['message'] = 'New Task - New task is added in your task';
                    $notification_data['page'] = 'homepage';
                    // $res = $this->guard_model->send_push_notification($notification_data);
                }
            }
        }
    }
    if(isset($guard_data->notification_token)){
        $prams['message'] = 'One of your shift updated.';
        $prams['title'] = 'Shift Updated';
        $prams['page'] = 'roster';
        $prams['notification_token'] = $guard_data->notification_token;
        send_push_notification($prams);
    }
    DB::table('roster_complete_activity')->insert([
        'roster_id' => $roster_id,
        'activity' => 'Shift edited',
        'type' => 'shift_edit',
        'record_id' => $roster_id,
        'activity_time' => time(),
        'activity_by' => $admin_id
    ]);
    $this->updateHoursDistribution($temp_start, $temp_end, $rosterdata->site_id, $rosterdata->roster_id);
    return true;
}


// public function addNewShift(Request $request){

//     $jobNewRoster = JobNewRoster::where('id', $request->roster_id)->first();
//     if (
//         (dbFormate($request->start) < $jobNewRoster->start) ||
//         (
//             isset($jobNewRoster->end) &&
//             ($jobNewRoster->end !== null) &&
//             (dbFormate($request->start) > $jobNewRoster->end)
//         )
//     ) {
//         return response()->json([
//             'success' => false,
//             'hide' => true,
//             'message' => 'Shift timing must fall within the roster date.'
//         ]);
//     }


//     if(isset($request->guard_id) && $request->guard_id > 0){
//         # CHECK GUARD IS FULL TIMMER OR PART TIMMER
//         $guard = Guard::find($request->guard_id);
//         if($guard->staff_type == 'part_time' && !isset($request->shift_confirm)){
//             return response()->json(['success' => false, 'message' => 'You\'ve selected a part-time guard. Would you like to continue or check full-time guards?']); 
//         }

//         # CHECK DIFFERENCE BETWEEN SHIFTS
//         $checkAdmin = checkAdmin($request->admin_id);
//         if($checkAdmin != 'super-admin'){
//             # CHECK SHIFT SHOULD BE LESS THEN 8 HOURS IF YOUR ADMIN
//             // $startDate = Carbon::createFromFormat('m-d-Y H:i', $request->start);
//             // $endDate = Carbon::createFromFormat('m-d-Y H:i', $request->end);
//             // $checkDiff = $startDate->diff($endDate);
//             // if($checkDiff->h > 8){
//             //     return response()->json(['success' => false, 'hide' => true, 'message' => 'You can not create shift more then 8 hours!']); 
//             // }
//             $diff =  checkShiftDayHours($request->start, $request->end, $request->guard_id);
//             if($diff > 9){
//                 return response()->json(['success' => false, 'message' => 'You must rest for eight hours before starting a new shift!']); 
//             }


//         }
//         $guard_leave = checkGuardOnLeave($request->start, $request->end, $request->guard_id);
//         if($guard_leave == 'leave'){
//             return response()->json(['success' => false, 'message' => 'Sorry Staff On Leave!', 'code'=> 404]);
//         }
//         # CHECK GUARD DOCS ARE SET AND NOT EXPIRED
//         $checkGuardDocs = true;
//         $guardDetails = GuardWorkDetail::where('guard_id', $request->guard_id)->first();
//         if (empty($guardDetails->guard_document_type)) {
//             // return "Please First Add Your Residential Status!";
//             $checkGuardDocs = false;
//         }
//         // $today = strtotime(date("Y/m/d"));
//         $t = dbFormate($request->start);
//         $today = strtotime($t);
//         $guard = Guard::where('id', $request->guard_id)->with('guardDocuments')->first();
//         foreach ($guard->guardDocuments as $document) {
//             if ($document->c_f_roster == 1) {
//                 if ($document->document_category == 'citizen') {
//                     if ($document->document_expire == 'current, pending renewal' && $document->document_type == 'security_license'){ 
//                            $checkGuardDocs = true;
//                     } else {
//                         if ($document->document_type == 'security_license' &&
//                         ($document->document_expire === '' || $document->document_expire === null || $today > strtotime($document->document_expire))) {
//                         $docExpire = "Security License Expired!";
//                         $checkGuardDocs = false;
//                         } else {
//                            // return 'active';
//                            $checkGuardDocs = true;
//                         }
//                     }
//                 } else {
//                     if ($document->document_expire == 'current, pending renewal' && $document->document_type == 'security_license')
//                     {
//                         $checkGuardDocs = true;
//                     } else {
//                         if (in_array($document->document_type, ['visa', 'passport', 'security_license']) &&
//                             ($document->document_expire === '' || $document->document_expire === null || $today > strtotime($document->document_expire))) {
//                             $docExpire = ucfirst($document->document_type) . " Expired!";
//                             $checkGuardDocs = false;
//                         }
//                     }
//                 }
//             }
//         }
//         if ($checkGuardDocs == false && !isset($request->shift_confirm)) {
//             if($checkAdmin == 'super-admin'){
//                 return response()->json(['data'=>$checkGuardDocs,'success' => false, 'message' => '<b>Hi, Super Admin this shift has document expired or not updated <br> Do you really want to create this shift !</b>', 'code'=> 404]);
//             }
//             if($checkAdmin == 'admin'){
//                 return response()->json(['success' => false, 'message' => '<bHi Admin, this guard has document expired or not updated so you cant create a shift!</b>', 'code'=> 404]);
//             }
//         }
//         # CHECK SHIFT CONFILICT
//         if(isset($request->id) && $request->id > 0){
//             $addNewShift = JobRoster::findOrFail($request->id);
//             $start_time = dbFormateDateTime($request->start);
//             $end_time = dbFormateDateTime($request->end);
//             $conflictingShift = JobRoster::where('guard_id', $request->guard_id)
//             ->where(function ($query) use ($start_time, $end_time) {
//                 $query->where(function ($q) use ($start_time) {
//                     $q->where('start', '<=', $start_time)->where('end', '>=', $start_time);
//                 })->orWhere(function ($q) use ($end_time) {
//                     $q->where('start', '<=', $end_time)->where('end', '>=', $end_time);
//                 })->orWhere(function ($q) use ($start_time, $end_time) {
//                     $q->where('start', '>=', $start_time)->where('end', '<=', $end_time);
//                 });
//             })
//             ->where('id', '!=' ,$addNewShift->id)->select('id', 'start', 'end')->first();
//         }else{
//             $start_time = dbFormateDateTime($request->start);
//             $end_time = dbFormateDateTime($request->end);
//             $conflictingShift = JobRoster::where('guard_id', $request->guard_id)
//             ->where(function ($query) use ($start_time, $end_time) {
//                 $query->where(function ($q) use ($start_time) {
//                     $q->where('start', '<=', $start_time)->where('end', '>=', $start_time);
//                 })->orWhere(function ($q) use ($end_time) {
//                     $q->where('start', '<=', $end_time)->where('end', '>=', $end_time);
//                 })->orWhere(function ($q) use ($start_time, $end_time) {
//                     $q->where('start', '>=', $start_time)->where('end', '<=', $end_time);
//                 });
//             })
//             ->select('id', 'start', 'end')->first();
//         }
//         // return $conflictingShift;
//         if ($conflictingShift && !isset($request->shift_confirm)) {
//             if($checkAdmin == 'super-admin'){
//                 return response()->json(['data'=>$conflictingShift,'success' => false, 'message' => '<b>Hi, Super Admin this shift has conflict <br> Do you really want to create this shift !</b>', 'code'=> 404]);
//             }
//             if($checkAdmin == 'admin'){
//                 return response()->json(['success' => false, 'message' => '<b>Hi Admin, this shift has conflict so you cant create a shift!</b>', 'code'=> 404]);
//             }
//         }
//         # CHECK GUARD WORK LIMITATION
//         $guardWorkingHours = calCulateGuardWeekHours(dbFormateDateTime($request->start), dbFormateDateTime($request->end));
//         $guardWorkLimitation = checkGuardWorkLimitation($request->guard_id, $guardWorkingHours);
//         $w_l_h = '';
//         $now = Carbon::now();
//         $weekStartDate = $now->startOfWeek()->toDateString();
//         $weekEndDate = $now->endOfWeek()->toDateString();
//         $guardOnLimitations = Guard::where('id', $request->guard_id)->first();

//         if ($guardOnLimitations->work_limitation_status == 1) {
//             $w_l_h = $guardOnLimitations->weekly_work_hours_limitation ?? 40;
//         }

//         $sumOfOneWeekHour = JobRoster::where('guard_id', $request->guard_id)
//             ->where(function ($query) use ($weekStartDate, $weekEndDate) {
//                 $query->whereBetween('start', [$weekStartDate, $weekEndDate])
//                     ->orWhereBetween('end', [$weekStartDate, $weekEndDate]);
//             })
//             ->sum('total_week_hours');

//         if (!empty($sumOfOneWeekHour) && !empty($w_l_h)) {
//             $sum = $sumOfOneWeekHour + $guardWorkingHours;
//             if ($sum > $w_l_h) {
//                 $difference = $sum - $w_l_h - $guardWorkingHours;
//                 $message = 'You cannot create a shift because you exceed your work limitations.';
//                 return response()->json([
//                     'success' => false,
//                     'message' => $message,
//                     'data' => $difference,
//                 ]);
//             }
//         } elseif (!empty($guardOnLimitations->weekly_work_hours_limitation) && $guardOnLimitations->weekly_work_hours_limitation < $guardWorkingHours) {
//             $sum = $sumOfOneWeekHour + $guardWorkingHours;

//             if ($sum > $w_l_h) {
//                 $difference = $sum - $w_l_h;
//                 $message = 'You cannot create a shift because you exceed your work limitations.';
//                 return response()->json([
//                     'success' => false,
//                     'message' => $message,
//                     'data' => $difference,
//                 ]);
//             }
//         }
//     }
    
//     // # CREATE SHIFT TEMPLATES
//     if($request->has('shift_type') && $request->shift_type == 'template'){
//         $addNewShift = new JobRoster();
//         $addNewShift->start = dbFormateDateTime($request->start);
//         $addNewShift->end = dbFormateDateTime($request->end);
//         $addNewShift->shift_create_status = 'pending';
//         $addNewShift->shift_type = 'template';
//         $addNewShift->save();
//         if($request->has('job_roster_tasks') && !empty($request->job_roster_tasks)){
//             foreach ($request->job_roster_tasks as $key => $task) {
//                 $newTask =  new JobRosterTask();
//                 $newTask->job_roster_id = $addNewShift->id;
//                 $newTask->task = $task['task'];
//                 $newTask->task_start = dbFormateDateTime($task['task_start']);
//                 $newTask->task_end = dbFormateDateTime($task['task_end']);
//                 $newTask->save();
//                 jobRosterActions($request->admin_id, 'add_shift_tasks', $newTask->id,'job_roster_tasks');
//             }
//         }
//         return response()->json([
//             'success' => true,
//             'message' => 'Template Shift Created.'
//         ]);
//     }

//     # CALCULATE CUSTOM PAYRATE AND CHARGE RATE IF SET IN SHIFT
//     $cus_payrate = '';
//     $cus_chargerate = '';
//     if($request->has('custome_rate') && $request->custome_rate == true){
//         if($request->has('custome_payrate') && $request->custome_payrate == true){
//             $cus_payrate = json_encode($request->manualPayRate);
//         }
//     }
//     if($request->has('custome_rate') && $request->custome_rate == true){
//         if($request->has('custome_chagerate') && $request->custome_chagerate == true){
//             $cus_chargerate = json_encode($request->manualChargeRate);
//         }
//     }
//     $PH_checkBox = ($request->public_holidays == 'on' ? true : false);
//     # CALCULATE GUARD SHIFT AND WORKING HOURS
//     $continuation = false;
//     if(isset($request->continuation) && $request->continuation == true){
//         $continuation = true;
//     }
//     $start_time = Carbon::createFromFormat("m-d-Y H:i", $request->start);
//     $start_time_hours_minutes = $start_time->format('H:i');
//     if($start_time_hours_minutes === '23:59'){
//         $start_time->addMinutes(1);
//         $modified_time_str = $start_time->format("m-d-Y H:i");
//     }else{
//         $modified_time_str = $request->start;
//     }
//     $end_time = Carbon::createFromFormat("m-d-Y H:i", $request->end);
//     $end_time_hours_minutes = $end_time->format('H:i');
//     if($end_time_hours_minutes === '23:59'){
//         $end_time->addMinutes(1);
//         $modified_time_end = $end_time->format("m-d-Y H:i");
//     }else{
//         $modified_time_end = $request->end;
//     }
//     $mainStart = Carbon::createFromFormat("m-d-Y H:i", $modified_time_str);
//     $mainEnd = Carbon::createFromFormat("m-d-Y H:i", $modified_time_end);
//     // $duration = $mainEnd->diffInMinutes($mainStart);
//     // if ($duration < 240) {
//     //     $mainEnd->addMinutes(240 - $duration);
//     // }
//     $timestamp_start = Carbon::parse(dbFormateDateTime($modified_time_str));
//     $timestamp_start_dst = Carbon::parse(dbFormateDateTime($modified_time_str))->isDST();
//     $timestamp_end = Carbon::parse(dbFormateDateTime($modified_time_end));
//     $timestamp_end_dst = Carbon::parse(dbFormateDateTime($modified_time_end))->isDST();
//     $same_date = $timestamp_start->isSameDay($timestamp_end);
//     if ($timestamp_start_dst && !$timestamp_end_dst) {
//         // DST is active at the start but not at the end
//         $dst_hours = $timestamp_start->diffInHours($timestamp_end) - 1;
//         if($same_date){
//             $mainEnd->subHour();
//         }
//     } elseif (!$timestamp_start_dst && $timestamp_end_dst) {
//         // DST is active at the end but not at the start
//         $dst_hours = $timestamp_start->diffInHours($timestamp_end) + 1;
//         if($same_date){
//             $mainEnd->addHour();
//         }

//     } else {
//         // DST status is the same at both start and end timestamps
//         $dst_hours = calCulateGuardWeekHours(dbFormateDateTime($modified_time_str), dbFormateDateTime($modified_time_end));
//     }
//     // return [$mainStart, $mainEnd, $dst_hours, $timestamp_start->diffInHours($timestamp_end) - 1, $timestamp_start->diffInHours($timestamp_end) + 1];
//     $hours = $this->getShiftHours(dbFormateDateTime($mainStart), dbFormateDateTime($mainEnd), $request->site_id, $continuation, $PH_checkBox, $PH_checkBox);
//     // $total = $result->hours;
//     // $total = $dst_hours;
//     // $guardWorkingHours = calCulateGuardWeekHours(dbFormateDateTime($modified_time_str), dbFormateDateTime($modified_time_end));
//     $guardWorkingHours = $dst_hours;

//     if(isset($request->id)){
//         $addNewShift = JobRoster::findOrFail($request->id);
//         $flagUpdateShift = 1;
//     }else{
//         $addNewShift = new JobRoster();
//         $flagUpdateShift = 0;
//     }
//     $addNewShift->site_id = $request->site_id;
//     $addNewShift->guard_id = (isset($request->guard_id) && !empty($request->guard_id)) ? $request->guard_id : null;
//     $addNewShift->start = dbFormateDateTime($request->start);
//     $addNewShift->end = dbFormateDateTime($request->end);
//     $addNewShift->shift_payable = !empty($request->shift_payable) && ($request->has('shift_payable')) ? $request->shift_payable : 'yes';
//     $addNewShift->shift_chargeable = !empty($request->shift_chargeable) && ($request->has('shift_payable')) ? $request->shift_chargeable : 'yes';
//     $addNewShift->custome_rate = ($request->custome_rate == 'on' ? true : false);
//     $addNewShift->payrate_level = $request->payrate_level;
//     $addNewShift->payrate = $request->payrate;
//     $addNewShift->chargerate_level = $request->chargerate_level;
//     $addNewShift->chargerate = $request->chargerate;
//     $addNewShift->un_published_shift = ($request->un_published_shift == 'on' ? true : false);
//     $addNewShift->public_holidays = ($request->public_holidays == 'on' ? true : false);
//     $addNewShift->covid_marshal = ($request->covid_marshal == 'on' ? true : false);
//     $addNewShift->training = ($request->training == 'on' ? true : false);
//     $addNewShift->continuation = ($request->continuation == 'on' ? true : false);
//     $addNewShift->over_time = ($request->over_time == 'on') ? true : false;
//     $addNewShift->over_time_value = ($request->over_time_value) ? $request->over_time_value : 0;
//     $addNewShift->travel_time = ($request->travel_time == 'on') ? true : false;
//     $addNewShift->travel_time_value = ($request->travel_time_value) ? $request->travel_time_value : 0;
//     $addNewShift->reimbursement = ($request->reimbursement == 'on') ? true : false;
//     $addNewShift->reimbursement_text = $request->reimbursement_text;
//     $addNewShift->reimbursement_value = $request->reimbursement_value;
//     $addNewShift->shift_create_status = 'pending';
//     $addNewShift->total_week_hours = $guardWorkingHours;
//     $addNewShift->shift_type = ($request->has('shift_type') && !empty($request->shift_type) ? $request->shift_type : '');
//     $addNewShift->conflict = (!empty($conflictingShift) ? 'conflict in '.getRosterdName($request->roster_id) : null);
//     $addNewShift->conflicted_with = (!empty($conflictingShift) ? $conflictingShift->id : null);
//     $addNewShift->doc_conf = ((isset($checkGuardDocs) && $checkGuardDocs == false) ? $docExpire : null);
//     $addNewShift->work_limitaion_conf = (!empty($guardWorkLimitation['difference']) ? $guardWorkLimitation['difference'] : '');
//     $addNewShift->conf_start = (!empty($conflictingShift) ? dbFormateDateTime($conflictingShift->start) : '');
//     $addNewShift->conf_end = (!empty($conflictingShift) ? dbFormateDateTime($conflictingShift->end) : '');
//     $addNewShift->morning_hours = (!empty($hours['morning']) ? roundHours($hours['morning']) : 0.0);
//     $addNewShift->night_hours = (!empty($hours['night']) ? roundHours($hours['night']) : 0.0);
//     $addNewShift->saturday_morning_hours = (!empty($hours['saturday_morning']) ? roundHours($hours['saturday_morning']) : 0.0);
//     $addNewShift->saturday_night_hours = (!empty($hours['saturday_night']) ? roundHours($hours['saturday_night']) : 0.0);
//     $addNewShift->sunday_morning_hours = (!empty($hours['sunday_morning']) ? roundHours($hours['sunday_morning']) : 0.0);
//     $addNewShift->sunday_night_hours = (!empty($hours['sunday_night']) ? roundHours($hours['sunday_night']) : 0.0);
//     $addNewShift->ph_morning_hours = (!empty($hours['ph_morning']) ? roundHours($hours['ph_morning']) : 0.0);
//     $addNewShift->ph_night_hours = (!empty($hours['ph_night']) ? roundHours($hours['ph_night']) : 0.0);
//     $addNewShift->last_update = time();
//     $addNewShift->hours = roundHours($guardWorkingHours);
//     $addNewShift->publish_status = isset($request->publish_status) ? $request->publish_status : 0;
//     $addNewShift->custome_rate = $request->custome_rate;
//     $addNewShift->custome_payrate = $request->custome_payrate;
//     $addNewShift->custome_chagerate = $request->custome_chagerate;
//     $addNewShift->manualPayRate = $cus_payrate;
//     $addNewShift->manualChargeRate = $cus_chargerate;
//     $addNewShift->unprofile_name = $request->unprofile_name;
//     $addNewShift->po_wo = $request->po_wo;
//     $addNewShift->job_instrcutions = $request->job_instrcutions;
//     $addNewShift->job_instruction_text = $request->job_instruction_text;
//     $addNewShift->roster_id = $request->roster_id;
//     $addNewShift->created_by = $request->admin_id;
//     $addNewShift->on_call_job = isset($request->on_call_job) ? $request->on_call_job : 0;
//     $addNewShift->save();
//     if($request->publish_status == 1 && isset($request->guard_id) && $request->guard_id > 0 && $request->un_published_shift == 0){
//         if($request->has('guard_id') && !empty($request->guard_id)){
//             # SEND MAIL
//             $guard = Guard::where('id', $request->guard_id)->first();
//             $prams['message'] = 'Roster for the week '.usaToAusDateTime($request->start).' - '.usaToAusDateTime($request->end).' has been published. Please open app and confirm your roster.';
//             $prams['subject'] = 'Roster Published';
//             $prams['email'] = $guard->email;
//             generalEmails($prams);
//             # SEND NOTIFICATION IF TOKEN EXIST
//             if(isset($guard->notification_token)){
//                 $prams['message'] = 'Roster for the week '.usaToAusDateTime($request->start).' - '.usaToAusDateTime($request->end).' has been published.';
//                 $prams['title'] = 'Roster Published';
//                 $prams['page'] = 'roster';
//                 $prams['notification_token'] = $guard->notification_token;
//                 send_push_notification($prams);
//             }
//         }
//         sendSmsToGuard($guard->phone, 'Hi '.$guard->first_name.' '.$guard->last_name.' '. 'your shift has been published successfully!');
//     }
//     jobRosterActions($request->admin_id, 'add_shift', $addNewShift->id, 'job_roster');
//     $admin_name = getAdminName($request->admin_id);
//     $currnet_time = time();
//     shiftCompleteActivity($addNewShift->id, $admin_name. ' Added this Shift', 'add_shift', $addNewShift->id, $currnet_time, $request->admin_id);
//     # SAVE TASK

//     if($request->has('job_roster_tasks') && !empty($request->job_roster_tasks)){
//         if($flagUpdateShift == 1){
//             foreach ($request->job_roster_tasks as $key => $task) {
//                 $updateTask =  JobRosterTask::where('id', $task['id'])->first();
//                 $old_task = $updateTask; 
//                 $is_check = 0; 
//                 if(!$updateTask){
//                     $updateTask =  new JobRosterTask();
//                     $is_check = 1;     
//                 }
//                 $updateTask->job_roster_id = $request->id;
//                 $updateTask->task = $task['task'];
//                 $updateTask->task_start = dbFormateDateTime($task['task_start']);
//                 $updateTask->task_end = dbFormateDateTime($task['task_end']);
//                 $updateTask->save();
//                 if($is_check == 1){
//                     jobRosterActions($request->admin_id, 'add_shift_tasks', $updateTask->id, 'job_roster_tasks');
//                 }else{
//                     $task_changes = $updateTask->getChanges();
//                     jobRosterActions($request->admin_id, 'update_shift_tasks', $updateTask->id,'job_roster_tasks', $old_task, $task_changes);
//                 }
//             }
//         }else{
//             if($request->shift_type == 'template_rost'){
//                 $getTemplateShiftTask = JobRoster::where(['start'=> $request->start, 'end'=>$request->end, 'roster_id'=>$request->roster_id, 'shift_type'=>'template'])->with('jobRosterTask')->first();
//                 if($getTemplateShiftTask->jobRosterTask){
//                     foreach($getTemplateShiftTask->jobRosterTask as $task){
//                         $newTask =  new JobRosterTask();
//                         $newTask->job_roster_id = $addNewShift->id;
//                         $newTask->task = $task['task'];
//                         $newTask->task_start = dbFormateDateTime($task['task_start']);
//                         $newTask->task_end = dbFormateDateTime($task['task_end']);
//                         $newTask->save();
//                         jobRosterActions($request->admin_id, 'add_shift_tasks', $newTask->id,'job_roster_tasks');
//                     }
//                 }
//             }else{
//                 foreach ($request->job_roster_tasks as $key => $task) {
//                     $newTask =  new JobRosterTask();
//                     $newTask->job_roster_id = $addNewShift->id;
//                     $newTask->task = $task['task'];
//                     $newTask->task_start = dbFormateDateTime($task['task_start']);
//                     $newTask->task_end = dbFormateDateTime($task['task_end']);
//                     $newTask->save();
//                     jobRosterActions($request->admin_id, 'add_shift_tasks', $newTask->id,'job_roster_tasks');
//                 }
//             }
//         }
//     }
//     if(($flagUpdateShift == 1) && (empty($conflictingShift) || $checkGuardDocs == false)){
//         $addNewShift->conflict = null;
//         $addNewShift->conf_start = null;
//         $addNewShift->conf_end = null;
//         $addNewShift->update();
//     }
//     if((isset($conflictingShift) && !empty($conflictingShift)) || (isset($checkGuardDocs) && $checkGuardDocs == false)){
//         return response()->json(['success' => true, 'message' => '<b>Conflicted Shift Created!</b>', 'code'=> 200]);
//     }
//     return response()->json([
//         'success' => true,
//         'message' => 'Shift Created.'
//     ]);
// }

public function addNewShift(Request $request, $call_from = null){

    $jobNewRoster = JobNewRoster::where('id', $request->roster_id)->first();

    $all_guards = 0;
    $formData = $request;
    if (!$request->has('confirm_shift')) {
        $formData->confirm_shift = false;
    }
    if (!$request->has('publish_status')) {
        $formData->publish_status = 0;
    }
    if (!$request->has('shift_payable')) {
        $formData->shift_payable = 'yes';
    }
    if (!$request->has('shift_chargeable')) {
        $formData->shift_chargeable = 'yes';
    }
    if (!$request->has('custome_rate')) {
        $formData->custome_rate = 0;
    }
    if (!$request->has('training')) {
        $formData->training = 0;
    }
    if (!$request->has('continuation')) {
        $formData->continuation = 0;
    }
    if (!$request->has('travel_time')) {
        $formData->travel_time = 0;
    }
    if ($request->has('travel_time') && $request->travel_time == '') {
        $formData->travel_time = 0;
    }
    if (!$request->has('public_holidays')) {
        $formData->public_holiday = 0;
    }
    if (!$request->has('over_time')) {
        $formData->over_time = 0;
    }
    if (!$request->has('over_time_value')) {
        $formData->over_time_value = 1;
    }
    if ($request->has('over_time_value') && $request->over_time_value == 0) {
        $formData->over_time_value = 1;
    }
    if (!$request->has('travel_time_value')) {
        $formData->travel_time_value = 0;
    }
    if (!$request->has('travel_time_amount_chargeable')) {
        $formData->travel_time_amount_chargeable = '';
    }
    if (!$request->has('paid_by')) {
        $formData->paid_by = '';
    }
    if (!$request->has('notify')) {
        $formData->notify = 'true';
    }
    if ($request->has('job_roster_tasks')) {
        $formData->tasks = $formData->job_roster_tasks;
    } else {
        $formData->tasks = array();
    }
    if (!$request->has('operators_notes')) {
        $formData->operators_notes = null;
    }
    if (!$request->has('travel_time_payable')) {
        $formData->travel_time_payable = 'yes';
    }
    if (!$request->has('travel_time_chargeable')) {
        $formData->travel_time_chargeable = 'yes';
    }
    if (!$request->has('covid_marshal')) {
        $formData->covid_marshal = 0;
    }
    if (!$request->has('unpublish_shift')) {
        $formData->unpublish_shift = 0;
    }
    if (!$request->has('multiple_shifts_count')) {
        $formData->multiple_shifts_count = 1;
    }
    if (!$request->has('chargerate_level')) {
        $formData->chargerate_level = null;
    }
    if (!$request->has('payrate_level')) {
        $formData->payrate_level = null;
    }
   if (!$request->has('payrate')) {
        $formData->payrate = 0;
    }
      if($request->has('shift_type') && $request->shift_type == 'template'){
        $addNewShift = new JobRoster();
        $addNewShift->start = dbFormateDateTime($request->start);
        $addNewShift->end = dbFormateDateTime($request->end);
        $addNewShift->shift_create_status = 'pending';
        $addNewShift->shift_type = 'template';
        $addNewShift->save();
    
        return response()->json([
            'success' => true,
            'message' => 'Template Shift Created.'
        ]);
    }

    $formData->start = DateTime::createFromFormat('m-d-Y H:i', $formData->start)->format('d-m-Y H:i');
    $formData->end = DateTime::createFromFormat('m-d-Y H:i', $formData->end)->format('d-m-Y H:i');

    $formData->conflict = 0;
    $formData->conflict_message = 'N/A';
    if ($request->file('job_instrcutions')) {
        $public_path = public_path();
        $public_path = str_replace('portal/public', '', $public_path);
        $public_path = str_replace('apis/public', '', $public_path);
        $path = $public_path . 'uploads/';
        $file = $request->file('job_instrcutions');
        $filename = time() . $file->getClientOriginalName();
        $file->move($path, $filename);
        $formData->job_instrcutions = $filename;
    } else {
        $formData->job_instrcutions = '';
    }
    if ($request->has('unprofile_name') && $request->unprofile_name == 'undefined') {
        $request->unprofile_name = '';
    }
    if ($request->has('unprofile_name') && $request->id > 0) {
        DB::table('job_rosters')->where('id', $request->id)->update(['unprofile_name' => $request->unprofile_name]);
    } elseif ($request->has('unprofile_name') && $request->unprofile_name != '') {
        $formData->unprofile_name = $request->unprofile_name;
    } else {
        $formData->unprofile_name = '';
    }
    $jobData = $this->getJobData($formData->site_id);
    if($request->adhoc_shift == 'yes') {
        $formData->adhoc_shift = 'yes';
    }else{
        if($jobData->site_hours == 'adhoc'){
            $formData->adhoc_shift = 'yes';
        }else{
            $formData->adhoc_shift = 'no';
        }
    }
    $guardData = $this->getSingleGuard($formData->guard_id);
    if ($formData->guard_id != '' && $formData->guard_id != null && $formData->guard_id > 0) {
        if ($jobData->state != $guardData->state) {
            $error["type"] = "unauthorized";
            $error["message"] = 'Guard don\'t belong to ' . $jobData->state . '. Please change guard state first!';
            $error['confirm_shift'] = false;
            if ($call_from == 'api') {
                return $error;
            }else{
                return response()->json($error);
                exit;
            }
        }
    }
    if ($jobData->unpublished_site == 'yes') {
        $formData->unpublish_shift = 1;
    }

    if ($formData->covid_marshal == 0) {
     if ($guardData) {
        $status = $this->checkGuardSecurityLicenceDocuments($guardData, $request->tempDate);
        
        if ($status != "Active") {
            $formData->conflict = 1;
            $formData->conflict_message =  $status;
                $error["type"] = "document";
                $error["message"] = $status;
                $error['confirm_shift'] = "no";
                return response()->json($error);
                exit;
        }
    }
    if ($guardData && $formData->confirm_shift == "no") {
        $status = $this->checkGuardDocumentsamg($guardData);
           
        if ($status != "Active") {
            $formData->conflict = 1;
            $formData->conflict_message =  $status;
        }
    }
}

$time1 = strtotime($formData->end);
$time2 = strtotime($formData->start);
$diff = ($time1 - $time2) / (60 * 60);

if ($diff > 13) {
    return response()->json([
        'success' => false,
        'message' => 'You are not able to roster any guard for more than 13 hours shift.',
        'data' => [
            'shift_hours' => $diff,
        ],
    ]);
}

if ($formData->guard_id != '' && $formData->guard_id != null) {

    $validate = $this->checkGuardLeaves($formData->guard_id, $formData->start, $formData->end);
    if (!$validate) {
        $error["type"] = "unauthorized";
        $error["message"] = "This guard is on leave. You cannot roster him/her.";
        $error['confirm_shift'] = true;

        return response()->json($error);
        exit;
    }
    if (!empty($formData->save) &&  $formData->guard_id != '' && $formData->guard_id > 0 && $formData->guard_id != null) {

        if (!isset($formData->id) || !$formData->id) {
            $formData->id = 0;
        }

        $validate = $this->checkGuardLastShift($formData->guard_id, $formData->start, $formData->end, $formData->id);

        if (!$validate['status']) {
            $error["type"] = "unauthorized";
            if (isset($validate['message'])) {
                $error['message'] = $validate['message'];
            } else {
                $error["message"] = "This guard just completed his/her last job. The 8-hours rest time is not completed yet. You cannot roster him/her.";
            }
            $formData->conflict_message = $error['message'];
            $formData->conflict = 1;
        }
    }
}

if ($formData->id > 1) {
    $rosterD = DB::table('job_rosters')->where('id', $formData->id)->first();
    if ($rosterD->job_status == 'completed' || $rosterD->signin_status == 1) {
        if ($rosterD->guard_id != $formData->guard_id) {
            $error["type"] = "unauthorized";
            $error["message"] = "You can't change guard now!";
            $error['confirm_shift'] = false;
            if ($call_from == 'api') {
                return $error;
                exit();
            } else {
                return response()->json($error);
                exit;
            }
        }
    }
}

if (!empty($jobData)) {

    $jobStart = strtotime($jobData->start);
    $start = $formData->start;
    $dateTime = DateTime::createFromFormat('d-m-Y H:i', $start);

    if ($dateTime) {
        $startFormatted = $dateTime->format('Y-m-d');
        $start = strtotime($startFormatted);
    } else {
        echo "Invalid date format: $start\n";
    }

    if ($start < $jobStart) {
        $error["type"] = "document";
        $error["message"] = "The site's start date is earlier than the shift's start date.";
        return response()->json($error);
    }

    if (!empty($guardData)) {

        if ($guardData->covid == 1) {
            $error["type"] = "unauthorized";
            $error['by_pass'] = false;
            $error["message"] = "Specified guard is not available due to COVID 19.";
            if ($call_from == 'api') {
                return $error;
                exit();
            } else {
                return response()->json($error);
                exit;
            }
        }

        if ($guardData->empDetails->guard_document_type == 'student_visa') {
            if ($guardData->empDetails->work_hours_limitation_status == 1 || $guardData->empDetails->guard_document_type == 'student_visa') {

                $guardOnLimitations = GuardWorkDetail::where('guard_id', $formData->guard_id)->first();

                //old code
                $timestamp1 = strtotime($formData->start);
                $timestamp2 = strtotime($formData->end);

                if (empty($timestamp2)) {
                    $timestamp2 = strtotime($formData->start);
                }

                $week_array = $this->calculateFutureMonthFourthnight($formData->start);

                $newCurrentHours = abs($timestamp2 - $timestamp1) / (60 * 60);

                $currentMonthData = $this->get_current_month_hours_guards_part_student($formData->guard_id, $week_array['week_start'], $week_array['week_end'], $formData->id);

                $addedHours = 0;

                $calander_start_date = new DateTime($formData->start);
                $calender_end_date = new DateTime($formData->end);
                $fortnight_start_date = new DateTime($week_array['week_start']);
                $fortnight_end_date = new DateTime($week_array['week_end']);

                if (!empty($currentMonthData)) {
                    foreach ($currentMonthData as $currentMonthDatas) {
                        $timestamps1 = strtotime($currentMonthDatas->start);
                        $timestamps2 = strtotime($currentMonthDatas->end);
                        
                        $startDay = date('w', $timestamps1);
                        $endDay = date('w', $timestamps2);
                        
                        if ($startDay == 0 && $endDay == 1) {
                            $midnightSunday = strtotime('tomorrow', $timestamps1) - 1;
                            $sundayHours = abs($midnightSunday - $timestamps1) / 3600;
                            
                            $mondayStart = strtotime('tomorrow 00:00:00', $timestamps1);
                            $mondayHours = abs($timestamps2 - $mondayStart) / 3600;
                            
                            $addedHours += $mondayHours;
                        } 
                        else if ($startDay == 6 && $endDay == 0) {
                            $midnightSaturday = strtotime('tomorrow', $timestamps1) - 1;
                            $saturdayHours = abs($midnightSaturday - $timestamps1) / 3600;
                            
                            $addedHours += $saturdayHours;
                        }
                        else if ($startDay == 0 && $endDay == 0) {
                            $addedHours += abs($timestamps2 - $timestamps1) / 3600;
                        }
                        else {
                            $addedHours += abs($timestamps2 - $timestamps1) / 3600;
                        }
                    }
                }

                $dates_periods = array();
                $period = new DatePeriod(
                    new DateTime($fortnight_start_date->format("Y-m-d")),
                    new DateInterval('P1D'),
                    new DateTime($fortnight_end_date->format("Y-m-d"))
                );
                foreach ($period as $key => $value) {
                    array_push($dates_periods, $value->format('Y-m-d'));
                }
                array_push($dates_periods, $fortnight_end_date->format("Y-m-d"));

                if (in_array($calander_start_date->format("Y-m-d"), $dates_periods)) {
                    $dateTime = $this->parseDateWithAutoDetection($request->start);
                    $endTime = $this->parseDateWithAutoDetection($request->end);
                    $dayName = $dateTime->format('l');
                    $endDayName = $endTime->format('l');

                    if ($endTime->lt($dateTime)) {
                        $endTime->addDay();
                    }

                    $shiftStartDate = $dateTime->format('Y-m-d');
                    $shiftEndDate = $endTime->format('Y-m-d');
                    if (!in_array($shiftEndDate, $dates_periods)) {
                            
                        $fortnightEnd = Carbon::parse($fortnight_end_date)->endOfDay();
                        $current = $dateTime->copy();
                        $hoursInCurrentFortnight = 0;   
                        while ($current->lt($fortnightEnd)) {
                            $hourEnd = min($current->copy()->addHour(), $fortnightEnd);
                            
                            if ($current->format('l') != 'Sunday') {
                                $hoursInCurrentFortnight += $current->diffInHours($hourEnd, true);
                            }
                            
                            $current = $hourEnd;
                        }                         
                        $addedHours = $addedHours + $hoursInCurrentFortnight;
                    } else {
                        if(($dayName == 'Saturday' && $endDayName == 'Sunday')){
                            $sundayStart = $dateTime->copy()->addDay()->startOfDay();
                            $satHours = $dateTime->diffInHours($sundayStart, false);
                            
                            $addedHours = $addedHours + $satHours;
                        }elseif(($dayName == 'Sunday' && $endDayName == 'Monday')){
                            $mondayStart = $dateTime->copy()->addDay()->startOfDay();
                            $sunHours = $dateTime->diffInHours($mondayStart, false);
                            
                            $addedHours = ($addedHours + $newCurrentHours) - $sunHours;
                        }elseif(($dayName == 'Sunday' && $endDayName == 'Sunday')){
                            $addedHours = $addedHours - $newCurrentHours;
                        }else{
                            $addedHours = $addedHours + $newCurrentHours;
                        }
                    }
                    if($guardData->empDetails->limit_exceed == 1)
                    {
                        $guardStart = new DateTime($guardData->empDetails->start_time);
                        $guardEnd = new DateTime($guardData->empDetails->end_time);
                        $interval = new DateInterval('P1D');
                        $dateRange = new DatePeriod($guardStart, $interval, $guardEnd->modify('+1 day'));

                        $guardDates = [];
                        foreach ($dateRange as $date) {
                            $guardDates[] = $date->format('Y-m-d');
                        }
                        $hasCompleteFortnight = false;

                        $guardDateCount = count($guardDates);

                        for ($i = 0; $i <= $guardDateCount - 14; $i++) {
                            $fourteenDays = array_slice($guardDates, $i, 14);
                            
                            $allExist = true;
                            foreach ($fourteenDays as $day) {
                                if (!in_array($day, $dates_periods)) {
                                    $allExist = false;
                                    break;
                                }
                            }
                            
                            if ($allExist) {
                                $hasCompleteFortnight = true;
                                break;
                            }
                        }

                        if ($hasCompleteFortnight) {
                           $totalWorkingHours = 72;   
                           if ($addedHours > $totalWorkingHours) {
                                if (isset($request->otp)) {
                                    
                                    $getAdmin = User::where('id', 257)->first();

                                    if (!$getAdmin || !$getAdmin->google2fa_secret) {
                                        return response()->json([
                                            'success' => false,
                                            'message' => 'Manager does not have 2FA setup.',
                                            'data' => [
                                                'fortnight_limit' => $totalWorkingHours,
                                                'total_hours' => $addedHours,
                                                'exceed_hours' => $addedHours - $totalWorkingHours,
                                            ],
                                        ]);
                                    }
                                    
                                    $googleAuthenticator = new Google2FA();
                                    $valid = $googleAuthenticator->verifyKey($getAdmin->google2fa_secret, $request->otp);
                                    
                                    if (!$valid) {
                                        return response()->json([
                                            'success' => false,
                                            'message' => 'Wrong OTP! Please try again.',
                                            'require_otp' => true,
                                            'data' => [
                                                'fortnight_limit' => $totalWorkingHours,
                                                'total_hours' => $addedHours,
                                                'exceed_hours' => $addedHours - $totalWorkingHours,
                                            ],
                                        ]);
                                    }
                                    
                                } else {
                                    return response()->json([
                                        'success' => false,
                                        'message' => 'Hours exceed limit. Manager OTP required.',
                                        'require_otp' => true,
                                        'data' => [
                                            'fortnight_limit' => $totalWorkingHours,
                                            'total_hours' => $addedHours,
                                            'exceed_hours' => $addedHours - $totalWorkingHours,
                                        ],
                                    ]);
                                }
                            }  
                        } else {
                           $totalWorkingHours = 48;
                           if ($addedHours > $totalWorkingHours) {
                                if (isset($request->otp)) {
                                    
                                    $getAdmin = User::where('id', 257)->first();

                                    if (!$getAdmin || !$getAdmin->google2fa_secret) {
                                        return response()->json([
                                            'success' => false,
                                            'message' => 'Manager does not have 2FA setup.',
                                            'data' => [
                                                'fortnight_limit' => $totalWorkingHours,
                                                'total_hours' => $addedHours,
                                                'exceed_hours' => $addedHours - $totalWorkingHours,
                                            ],
                                        ]);
                                    }
                                    
                                    $googleAuthenticator = new Google2FA();
                                    $valid = $googleAuthenticator->verifyKey($getAdmin->google2fa_secret, $request->otp);
                                    
                                    if (!$valid) {
                                        return response()->json([
                                            'success' => false,
                                            'message' => 'Wrong OTP! Please try again.',
                                            'require_otp' => true,
                                            'data' => [
                                                'fortnight_limit' => $totalWorkingHours,
                                                'total_hours' => $addedHours,
                                                'exceed_hours' => $addedHours - $totalWorkingHours,
                                            ],
                                        ]);
                                    }
                                    
                                } else {
                                    return response()->json([
                                        'success' => false,
                                        'message' => 'Hours exceed limit. Manager OTP required.',
                                        'require_otp' => true,
                                        'data' => [
                                            'fortnight_limit' => $totalWorkingHours,
                                            'total_hours' => $addedHours,
                                            'exceed_hours' => $addedHours - $totalWorkingHours,
                                        ],
                                    ]);
                                }
                            }
                        }
                    }else{
                           $totalWorkingHours = 48;
                           if ($addedHours > $totalWorkingHours) {
                                if (isset($request->otp)) {
                                    
                                    $getAdmin = User::where('id', 257)->first();

                                    if (!$getAdmin || !$getAdmin->google2fa_secret) {
                                        return response()->json([
                                            'success' => false,
                                            'message' => 'Manager does not have 2FA setup.',
                                            'data' => [
                                                'fortnight_limit' => $totalWorkingHours,
                                                'total_hours' => $addedHours,
                                                'exceed_hours' => $addedHours - $totalWorkingHours,
                                            ],
                                        ]);
                                    }
                                    
                                    $googleAuthenticator = new Google2FA();
                                    $valid = $googleAuthenticator->verifyKey($getAdmin->google2fa_secret, $request->otp);
                                    
                                    if (!$valid) {
                                        return response()->json([
                                            'success' => false,
                                            'message' => 'Wrong OTP! Please try again.',
                                            'require_otp' => true,
                                            'data' => [
                                                'fortnight_limit' => $totalWorkingHours,
                                                'total_hours' => $addedHours,
                                                'exceed_hours' => $addedHours - $totalWorkingHours,
                                            ],
                                        ]);
                                    }
                                    
                                } else {
                                    return response()->json([
                                        'success' => false,
                                        'message' => 'Hours exceed limit. Manager OTP required.',
                                        'require_otp' => true,
                                        'data' => [
                                            'fortnight_limit' => $totalWorkingHours,
                                            'total_hours' => $addedHours,
                                            'exceed_hours' => $addedHours - $totalWorkingHours,
                                        ],
                                    ]);
                                }
                            }
                    }
                }
            }
        } elseif ($guardData->staff_type == 'part_time' && $guardData->empDetails->guard_document_type != 'student_visa') {
            if ($guardData->empDetails->guard_document_type != 'student_visa') {
            $guardOnLimitations = GuardWorkDetail::where('guard_id', $formData->guard_id)->first();
            $totalWorkingHours = 72;
            if ($guardOnLimitations && $guardOnLimitations->work_hours_limitation_status == 1) {
                $totalWorkingHours = $guardOnLimitations->weekly_work_hours_limitation ?? 72;
            }
            //old code
                $timestamp1 = strtotime($formData->start);
                $timestamp2 = strtotime($formData->end);

                if (empty($timestamp2)) {
                    $timestamp2 = strtotime($formData->start);
                }

                $week_array = $this->calculateFutureMonthFourthnight($formData->start);
                $newCurrentHours = abs($timestamp2 - $timestamp1) / (60 * 60);

                $currentMonthData = $this->get_current_month_hours_guards_part_student($formData->guard_id, $week_array['week_start'], $week_array['week_end'], $formData->id);
                $addedHours = 0;

                $calander_start_date = new DateTime($formData->start);
                $calender_end_date = new DateTime($formData->end);
                $fortnight_start_date = new DateTime($week_array['week_start']);
                $fortnight_end_date = new DateTime($week_array['week_end']);

                if (!empty($currentMonthData)) {
                    foreach ($currentMonthData as $currentMonthDatas) {
                        $timestamps1 = strtotime($currentMonthDatas->start);
                        $timestamps2 = strtotime($currentMonthDatas->end);
                        
                        $startDay = date('w', $timestamps1);
                        $endDay = date('w', $timestamps2);
                        
                       if ($startDay == 0 && $endDay == 1) {
                            $midnightSunday = strtotime('tomorrow', $timestamps1) - 1;
                            $sundayHours = abs($midnightSunday - $timestamps1) / 3600;
                            
                            $mondayStart = strtotime('tomorrow 00:00:00', $timestamps1);
                            $mondayHours = abs($timestamps2 - $mondayStart) / 3600;
                            
                            $addedHours += $mondayHours;
                        } 
                        else if ($startDay == 6 && $endDay == 0) {
                            $midnightSaturday = strtotime('tomorrow', $timestamps1) - 1;
                            $saturdayHours = abs($midnightSaturday - $timestamps1) / 3600;
                            
                            $addedHours += $saturdayHours;
                        }
                        else if ($startDay == 0 && $endDay == 0) {
                            $addedHours += abs($timestamps2 - $timestamps1) / 3600;
                        }
                        else {
                            $addedHours += abs($timestamps2 - $timestamps1) / 3600;
                        }
                    }
                }

                // $addedHours = ceil($addedHours);
                $dates_periods = array();
                $period = new DatePeriod(
                    new DateTime($fortnight_start_date->format("Y-m-d")),
                    new DateInterval('P1D'),
                    new DateTime($fortnight_end_date->format("Y-m-d"))
                );
                foreach ($period as $key => $value) {
                    array_push($dates_periods, $value->format('Y-m-d'));
                }
                array_push($dates_periods, $fortnight_end_date->format("Y-m-d"));
                
                if (in_array($calander_start_date->format("Y-m-d"), $dates_periods)) {
                    $dateTime = $this->parseDateWithAutoDetection($request->start);
                    $endTime = $this->parseDateWithAutoDetection($request->end);
                    $dayName = $dateTime->format('l');
                    $endDayName = $endTime->format('l');

                    if ($endTime->lt($dateTime)) {
                        $endTime->addDay();
                    }

                    $shiftStartDate = $dateTime->format('Y-m-d');
                    $shiftEndDate = $endTime->format('Y-m-d');
                    if (!in_array($shiftEndDate, $dates_periods)) {
                            
                        $fortnightEnd = Carbon::parse($fortnight_end_date)->endOfDay();
                        $current = $dateTime->copy();
                        $hoursInCurrentFortnight = 0;   
                        while ($current->lt($fortnightEnd)) {
                            $hourEnd = min($current->copy()->addHour(), $fortnightEnd);
                            
                            if ($current->format('l') != 'Sunday') {
                                $hoursInCurrentFortnight += $current->diffInHours($hourEnd, true);
                            }
                            
                            $current = $hourEnd;
                        }                         
                        $addedHours = $addedHours + $hoursInCurrentFortnight;
                    } else {
                        if(($dayName == 'Saturday' && $endDayName == 'Sunday')){
                            $sundayStart = $dateTime->copy()->addDay()->startOfDay();
                            $satHours = $dateTime->diffInHours($sundayStart, false);
                            
                            $addedHours = $addedHours + $satHours;
                        }elseif(($dayName == 'Sunday' && $endDayName == 'Monday')){
                            $mondayStart = $dateTime->copy()->addDay()->startOfDay();
                            $sunHours = $dateTime->diffInHours($mondayStart, false);
                            
                            $addedHours = ($addedHours + $newCurrentHours) - $sunHours;
                        }elseif(($dayName == 'Sunday' && $endDayName == 'Sunday')){
                            $addedHours = $addedHours - $newCurrentHours;
                        }else{
                            $addedHours = $addedHours + $newCurrentHours;
                        }
                    }
                    
                    if ($addedHours > $totalWorkingHours) {
                        
                        if (isset($request->otp)) {
                            
                            $getAdmin = User::where('id', 257)->first();

                            if (!$getAdmin || !$getAdmin->google2fa_secret) {
                                return response()->json([
                                    'success' => false,
                                    'message' => 'Manager does not have 2FA setup.',
                                    'data' => [
                                        'fortnight_limit' => $totalWorkingHours,
                                        'total_hours' => $addedHours,
                                        'exceed_hours' => $addedHours - $totalWorkingHours,
                                    ],
                                ]);
                            }
                            
                            $googleAuthenticator = new Google2FA();
                            $valid = $googleAuthenticator->verifyKey($getAdmin->google2fa_secret, $request->otp);
                            
                            if (!$valid) {
                                return response()->json([
                                    'success' => false,
                                    'message' => 'Wrong OTP! Please try again.',
                                    'require_otp' => true,
                                    'data' => [
                                        'fortnight_limit' => $totalWorkingHours,
                                        'total_hours' => $addedHours,
                                        'exceed_hours' => $addedHours - $totalWorkingHours,
                                    ],
                                ]);
                            }
                            
                        } else {
                            return response()->json([
                                'success' => false,
                                'message' => 'Hours exceed limit. Manager OTP required.',
                                'require_otp' => true,
                                'data' => [
                                    'fortnight_limit' => $totalWorkingHours,
                                    'total_hours' => $addedHours,
                                    'exceed_hours' => $addedHours - $totalWorkingHours,
                                ],
                            ]);
                        }
                    }
                }
            //end old code
            }
        } else {
            $guardOnLimitations = GuardWorkDetail::where('guard_id', $formData->guard_id)->first();
            $totalWorkingHours = 72;
            if ($guardOnLimitations && $guardOnLimitations->work_hours_limitation_status == 1) {
                $totalWorkingHours = $guardOnLimitations->weekly_work_hours_limitation ?? 72;
            }
            //old code
                $timestamp1 = strtotime($formData->start);
                $timestamp2 = strtotime($formData->end);

                if (empty($timestamp2)) {
                    $timestamp2 = strtotime($formData->start);
                }

                $week_array = $this->calculateFutureMonthFourthnight($formData->start);
                $newCurrentHours = abs($timestamp2 - $timestamp1) / (60 * 60);

                $currentMonthData = $this->get_current_month_hours_guards_part_student($formData->guard_id, $week_array['week_start'], $week_array['week_end'], $formData->id);

                $addedHours = 0;

                $calander_start_date = new DateTime($formData->start);
                $calender_end_date = new DateTime($formData->end);
                $fortnight_start_date = new DateTime($week_array['week_start']);
                $fortnight_end_date = new DateTime($week_array['week_end']);

                if (!empty($currentMonthData)) {
                    foreach ($currentMonthData as $currentMonthDatas) {
                        $timestamps1 = strtotime($currentMonthDatas->start);
                        $timestamps2 = strtotime($currentMonthDatas->end);
                        
                        $startDay = date('w', $timestamps1);
                        $endDay = date('w', $timestamps2);
                        
                       if ($startDay == 0 && $endDay == 1) {
                            $midnightSunday = strtotime('tomorrow', $timestamps1) - 1;
                            $sundayHours = abs($midnightSunday - $timestamps1) / 3600;
                            
                            $mondayStart = strtotime('tomorrow 00:00:00', $timestamps1);
                            $mondayHours = abs($timestamps2 - $mondayStart) / 3600;
                            
                            $addedHours += $mondayHours;
                        } 
                        else if ($startDay == 6 && $endDay == 0) {
                            $midnightSaturday = strtotime('tomorrow', $timestamps1) - 1;
                            $saturdayHours = abs($midnightSaturday - $timestamps1) / 3600;
                            
                            $addedHours += $saturdayHours;
                        }
                        else if ($startDay == 0 && $endDay == 0) {
                            $addedHours += abs($timestamps2 - $timestamps1) / 3600;
                        }
                        else {
                            $addedHours += abs($timestamps2 - $timestamps1) / 3600;
                        }
                    }
                }

                // $addedHours = ceil($addedHours);
                $dates_periods = array();
                $period = new DatePeriod(
                    new DateTime($fortnight_start_date->format("Y-m-d")),
                    new DateInterval('P1D'),
                    new DateTime($fortnight_end_date->format("Y-m-d"))
                );
                foreach ($period as $key => $value) {
                    array_push($dates_periods, $value->format('Y-m-d'));
                }
                array_push($dates_periods, $fortnight_end_date->format("Y-m-d"));
                
                   if (in_array($calander_start_date->format("Y-m-d"), $dates_periods)) {
                    $dateTime = $this->parseDateWithAutoDetection($request->start);
                    $endTime = $this->parseDateWithAutoDetection($request->end);
                    $dayName = $dateTime->format('l');
                    $endDayName = $endTime->format('l');

                    if ($endTime->lt($dateTime)) {
                        $endTime->addDay();
                    }

                    $shiftStartDate = $dateTime->format('Y-m-d');
                    $shiftEndDate = $endTime->format('Y-m-d');
                    if (!in_array($shiftEndDate, $dates_periods)) {
                            
                        $fortnightEnd = Carbon::parse($fortnight_end_date)->endOfDay();
                        $current = $dateTime->copy();
                        $hoursInCurrentFortnight = 0;   
                        while ($current->lt($fortnightEnd)) {
                            $hourEnd = min($current->copy()->addHour(), $fortnightEnd);
                            
                            if ($current->format('l') != 'Sunday') {
                                $hoursInCurrentFortnight += $current->diffInHours($hourEnd, true);
                            }
                            
                            $current = $hourEnd;
                        }                         
                        $addedHours = $addedHours + $hoursInCurrentFortnight;
                    } else {
                        if(($dayName == 'Saturday' && $endDayName == 'Sunday')){
                            $sundayStart = $dateTime->copy()->addDay()->startOfDay();
                            $satHours = $dateTime->diffInHours($sundayStart, false);
                            
                            $addedHours = $addedHours + $satHours;
                        }elseif(($dayName == 'Sunday' && $endDayName == 'Monday')){
                            $mondayStart = $dateTime->copy()->addDay()->startOfDay();
                            $sunHours = $dateTime->diffInHours($mondayStart, false);
                            
                            $addedHours = ($addedHours + $newCurrentHours) - $sunHours;
                        }elseif(($dayName == 'Sunday' && $endDayName == 'Sunday')){
                            $addedHours = $addedHours - $newCurrentHours;
                        }else{
                            $addedHours = $addedHours + $newCurrentHours;
                        }
                    }
                    
                    if ($addedHours > $totalWorkingHours) {
                        //return here
                        return response()->json([
                            'success' => false,
                            'message' => 'You cannot create a shift because you exceed your work limitations.',
                            'data' => [
                                'fortnight_limit' => $totalWorkingHours,
                                'total_hours' => $addedHours,
                                'exceed_hours' => $addedHours - $totalWorkingHours, 
                            ],
                        ]);
                    }
                }
            //end old code
        }       
        
        if (!empty($formData->save)) {

            $flag = $this->availablity($formData->start, $formData->end, $formData->guard_id, $formData->site_id, $formData->id);

            if ($flag['status']) {
                $error["message"] = "already";
                $formData->conflict = 1;
                $formData->conflict_message = 'These timings are contradicting with '.$flag['site_name'].' timings ('.$flag['time'].') because this guard is already added in another site.';
            }else{
                if ($formData->conflict == 0) {
                    $formData->conflict = 0;
                    $formData->conflict_message = '';
                }
            }
            if(true){
                if (!empty($formData->save)) {
                    if (empty($formData->end) || $formData->end == 'Invalid date') {
                        $formData->end = $formData->start;
                    }
                    if (empty($formData->end)) {
                        $formData->end = $formData->start;
                    }
                    $rosterData = $this->rosterData($formData->id);
                    if (!empty($rosterData)) {
                        if (isset($formData->shifIsPressed) && $formData->shifIsPressed == 'true') {
                            if ($formData->has('id') && $formData->id > 0) {
                                $tasks = DB::table('job_roster_tasks')->join('job_rosters', 'job_rosters.id', '=', 'job_roster_tasks.roster_id')->where('job_rosters.id', '=', $formData->id)->select('job_roster_tasks.task_name as task_description', 'job_roster_tasks.task_time as task_start_time')->get();
                                $formData->tasks = json_encode($tasks);
                            }

                                $eventStatus = $this->addEvent(0, $formData->start, $formData->end, $formData->guard_id, $formData->site_id, $formData->tempDate, $formData->start, $formData->end, 0, $formData->tasks,$formData->adhoc_shift, $formData->shift_payable
                                , $formData->shift_chargeable, $formData->custome_rate, $formData->payrate, $formData->chargerate, $formData->publish_status, $formData->training, $formData->continuation, $formData->travel_time, $formData->paid_by, $formData->public_holiday, $formData->travel_time_payable, $formData->travel_time_chargeable, $formData->covid_marshal, $formData->unprofile_name, $formData->travel_time_value, $formData->over_time, $formData->over_time_value, $formData->travel_time_amount_chargeable, $formData->unpublish_shift, $formData->multiple_shifts_count, $formData->job_instrcutions, $formData->conflict, $formData->conflict_message,$formData->chargerate_level,$formData->payrate_level,$formData->admin_id, $formData->roster_id, $formData->admin_confirm);
                                if ($eventStatus) {
                                $action = 'shift_drag_copy';
                            }

                        } else {
                                $eventStatus = $this->eventUpdateDrop($formData->id, $formData->start, $formData->end, $formData->tempDate, $formData->start, $formData->end, $formData->guard_id, $formData->tasks, $formData->operators_notes, null,$formData->adhoc_shift, $formData->shift_payable, $formData->shift_chargeable, $formData->custome_rate, $formData->payrate, $formData->chargerate, $formData->publish_status, $formData->training, $formData->continuation, $formData->travel_time, $formData->paid_by, $formData->notify, $formData->public_holiday, $formData->travel_time_payable, $formData->travel_time_chargeable, $formData->covid_marshal, $formData->unprofile_name, $formData->travel_time_value, $formData->over_time, $formData->over_time_value, $formData->travel_time_amount_chargeable, $formData->unpublish_shift, $formData->job_instrcutions, $formData->conflict, $formData->conflict_message,$formData->chargerate_level,$formData->payrate_level,$formData->admin_id, $formData->roster_id, $formData->admin_confirm);
                            }
                            if ($eventStatus) {
                                $statu = [
                                    "message" => "Shift updated successfully",
                                    "success" => true
                                ];
                            } else {
                                $statu = [
                                    "message" => "Failed to updated shift",
                                    "success" => false
                                ];
                            }
                            
                                return response()->json($statu);
                    } else {

                        $eventStatus = $this->addEvent($formData->id, $formData->start, $formData->end, $formData->guard_id, $formData->site_id, $formData->tempDate, $formData->start, $formData->end, 0, $formData->tasks,$formData->adhoc_shift, $formData->shift_payable, $formData->shift_chargeable, $formData->custome_rate, $formData->payrate, $formData->chargerate, $formData->publish_status, $formData->training, $formData->continuation, $formData->travel_time, $formData->paid_by, $formData->public_holiday, $formData->travel_time_payable, $formData->travel_time_chargeable, $formData->covid_marshal, $formData->unprofile_name, $formData->travel_time_value, $formData->over_time, $formData->over_time_value, $formData->travel_time_amount_chargeable, $formData->unpublish_shift, $formData->multiple_shifts_count, $formData->job_instrcutions, $formData->conflict, $formData->conflict_message,$formData->chargerate_level,$formData->payrate_level,$formData->admin_id, $formData->roster_id, $formData->admin_confirm);

                        if ($eventStatus) {
                            $status = [
                                "message" => "Shift added successfully",
                                "success" => true
                            ];
                        } else {
                            $status = [
                                "message" => "Failed to add shift",
                                "success" => false
                            ];
                        }
                        
                            return response()->json($status);
                    }
                }
            }
         }
    } else {

        $rosterData = $this->rosterData($formData->id);
            if (!empty($rosterData)) {

                if ($formData->has('shifIsPressed') && $formData->shifIsPressed == 'true') {
                    if ($formData->has('id') && $formData->id > 0) {
                        $tasks = DB::table('job_roster_tasks')->join('job_rosters', 'job_rosters.id', '=', 'job_roster_tasks.roster_id')->where('job_rosters.id', '=', $formData->id)->select('job_roster_tasks.task_name as task_description', 'job_roster_tasks.task_time as task_start_time')->get();
                        $formData->tasks = json_encode($tasks);
                    }
                    $eventStatus = $this->addEvent(0, $formData->start, $formData->end, null, $formData->site_id, $formData->tempDate, $formData->start, $formData->end, 0, $formData->tasks,$formData->adhoc_shift, $formData->shift_payable, $formData->shift_chargeable, $formData->custome_rate, $formData->payrate, $formData->chargerate, $formData->publish_status, $formData->training, $formData->continuation, $formData->travel_time, $formData->paid_by, $formData->public_holiday, $formData->travel_time_payable, $formData->travel_time_chargeable, $formData->covid_marshal, $formData->unprofile_name, $formData->travel_time_value, $formData->over_time, $formData->over_time_value, $formData->travel_time_amount_chargeable, $formData->unpublish_shift, $formData->multiple_shifts_count, $formData->job_instrcutions, $formData->conflict, $formData->conflict_message,$formData->chargerate_level,$formData->payrate_level,$formData->admin_id, $formData->roster_id, $formData->admin_confirm);
                    if ($eventStatus) {
                        $action = 'shift_drag_copy';
                    }

                } else {
                    $eventStatus = $this->eventUpdateDrop($formData->id, $formData->start, $formData->end, $formData->tempDate, $formData->start, $formData->end, $formData->guard_id, $formData->tasks, $formData->operators_notes, 'pending',$formData->adhoc_shift, $formData->shift_payable, $formData->shift_chargeable, $formData->custome_rate, $formData->payrate, $formData->chargerate, $formData->publish_status, $formData->training, $formData->continuation, $formData->travel_time, $formData->paid_by, $formData->notify, $formData->public_holiday, $formData->travel_time_payable, $formData->travel_time_chargeable, $formData->covid_marshal, $formData->unprofile_name, $formData->travel_time_value, $formData->over_time, $formData->over_time_value, $formData->travel_time_amount_chargeable, $formData->unpublish_shift, $formData->job_instrcutions, $formData->conflict, $formData->conflict_message,$formData->chargerate_level,$formData->payrate_level,$formData->admin_id, $formData->roster_id, $formData->admin_confirm);
                }
                if ($eventStatus) {
                    $status = [
                        "message" => "Shift updated successfully",
                        "success" => true
                    ];
                } else {
                    $status = [
                        "message" => "Failed to updated shift",
                        "success" => false
                    ];
                }
                
                    return $status;
            } else {
                if (isset($formData->post_status)) {
                    $post_status = 0;
                } else {
                    $post_status = 1;
                }

                $eventStatus = $this->addEvent($formData->id, $formData->start, $formData->end, null, $formData->site_id, $formData->tempDate, $formData->start, $formData->end, $post_status, $formData->tasks,$formData->adhoc_shift, $formData->shift_payable, $formData->shift_chargeable, $formData->custome_rate, $formData->payrate, $formData->chargerate, $formData->publish_status, $formData->training, $formData->continuation, $formData->travel_time, $formData->paid_by, $formData->public_holiday, $formData->travel_time_payable, $formData->travel_time_chargeable, $formData->covid_marshal, $formData->unprofile_name, $formData->travel_time_value, $formData->over_time, $formData->over_time_value, $formData->travel_time_amount_chargeable, $formData->unpublish_shift, $formData->multiple_shifts_count, $formData->job_instrcutions, $formData->conflict, $formData->conflict_message,$formData->chargerate_level,$formData->payrate_level, $formData->admin_id, $formData->roster_id, $formData->admin_confirm);
                if ($eventStatus) {
                    $status = [
                        "message" => "Shift added successfully",
                        "success" => true
                    ];
                } else {
                    $status = [
                        "message" => "Failed to add shift",
                        "success" => false
                    ];
                }
                return $status;
            }
        }
        }
    }

   function calculateShiftWorkingHours($start, $end)
{
    $start = Carbon::parse($start);
    $end   = Carbon::parse($end);

    $totalHours = 0;

    // iterate hour-by-hour through shift
    $current = $start->copy();

    while ($current < $end) {
        $nextHour = $current->copy()->addHour();

        // If hour extends beyond end time, cap it
        if ($nextHour > $end) {
            $nextHour = $end->copy();
        }

        // Monday = 1, Saturday = 6
        if ($current->dayOfWeek >= 1 && $current->dayOfWeek <= 6) {
            $totalHours += $current->diffInMinutes($nextHour) / 60;
        }

        $current = $nextHour;
    }

    return round($totalHours, 2);
}


public function getFullTimerAvailableGuards(Request $request, $call_from = null)
   {
      if(!isset($request->start) && !isset($request->end)){
      
         $request->merge([
            'start' => date('Y-m-d 00:00'),
            'end' => date('Y-m-d 23:59'),
         ]);
      }
      
    $prev_shift = [];
    $next_shift = [];
    $guards = '';
    $customer = Site::where('id', $request->siteId)->select('customer_id', 'trained')->first();
     //allow all guards
    if ($customer->trained == 'yes') {
        $guards = DB::table('guards')
        ->join('guards_documents', 'guards_documents.guard_id', '=', 'guards.id')
        ->where('guards.is_available', 'yes')
        ->where('guards.admin_approval_status', 'active')
        ->where('guards.phone', '!=', '')
        ->where('guards.first_name', '!=', '')
        ->where('guards.first_name', '!=', null)
        ->where('guards.email', '!=', '')
        ->where('guards.email', '!=', null)
        // ->whereJsonContains('site_id', $request->siteId)
        ->where('guards.guard_status', 'active')
        ->where('staff_type', 'full_time')
        ->select('guards.id', 'guards.first_name', 'guards.middle_name', 'guards.last_name', 'guards.profile_image', 'guards.state', 'guards.phone')->orderBy('first_name', 'ASC')
        ->groupBy('guards.id')->get();
    }else{
        $guards = DB::table('guards')
        ->join('guards_documents', 'guards_documents.guard_id', '=', 'guards.id')
        ->where('is_available', 'yes')
        ->where('admin_approval_status', 'active')
        ->where('address', '!=', '')
        ->where('phone', '!=', '')
        ->where('guards.first_name', '!=', '')
        ->where('guards.first_name', '!=', null)
      ->where('email', '!=', '')
        ->where('email', '!=', null)
        ->where(function ($query) {
           $query->where('guards_documents.document_type', 'security_license')
          ->orWhere('guards_documents.document_no', '!=', null)->orWhere('guards_documents.file' , '!=', null);
         })
        //  ->whereJsonContains('site_id', $request->siteId)
         ->where('guard_status', 'active')
         ->where('staff_type', 'full_time')
        ->select('guards.id', 'guards.first_name', 'guards.middle_name', 'guards.last_name', 'guards.profile_image', 'guards.state', 'guards.phone')->orderBy('first_name', 'ASC')
        ->groupBy('guards.id')->get();
    }

    $available_gaurds = array();
    foreach ($guards as $guard) {
        $max_hours = $this->count_today_working_hours($request->start, $request->end, $guard->id);
        $week_array = $this->calculateFutureMonthFourthnight($request->start);
        $currentMonthData = $this->get_current_month_hours_guards($guard->id, $week_array['week_start'], $week_array['week_end'], isset($request->shifIsPressed) ? true : false);
        if($currentMonthData + $max_hours < 72){
            $guard->working_hours = $max_hours;
            $already = DB::table('job_rosters')->where('guard_id', $guard->id)->where('start', '<=', dbFormateDateTimeStart($request->start))->where('end', '>=', dbFormateDateTimeStart($request->start))->first();
            if (empty($already)) {
                $already = DB::table('job_rosters')->where('guard_id', $guard->id)->where('start', '<=', dbFormateDateTimeEnd($request->end))->where('end', '>=', dbFormateDateTimeEnd($request->end))->first();
            }
            if (empty($already)) {
                $already = DB::table('job_rosters')->where('guard_id', $guard->id)->where('start', '>=', dbFormateDateTimeStart($request->start))->where('end', '<=', dbFormateDateTimeEnd($request->end))->first();
            }
            if (empty($already)) {
                $already = DB::table('job_rosters')->where('guard_id', $guard->id)->where('start', '>=', dbFormateDateTimeStart($request->start))->where('end', '<=', dbFormateDateTimeEnd($request->end))->first();
            }
            if (empty($already)) {
                $is_available = true;
                $prev_shift_res =  DB::table('job_rosters')->where('guard_id', $guard->id)->where('end', '<', dbFormateDateTimeStart($request->start))->orderBy('start', 'desc')->first();
                if (!empty($prev_shift_res)) {
    
                    $site = DB::table('sites')->where('id', $prev_shift_res->site_id)->first();
                    $site_name = !empty($site) ? $site->site_name : 'N/A';
                        // $prev_shift='';
                    $prev_shift = [
                        'guard_id' => $prev_shift_res->guard_id,
                        'date' => Date("d-m-Y", strtotime($prev_shift_res->start)),
                        'start' => Date("H:i", strtotime($prev_shift_res->start)),
                        'end' => Date("H:i", strtotime($prev_shift_res->end)),
                        'site' => $site_name,
                        'job_time_end' => ($prev_shift_res->end != '' && $prev_shift_res->end != null && $prev_shift_res->end > 0) ? date('Y-m-d H:i', strtotime($prev_shift_res->end)) : date("Y-m-d H:i", strtotime($prev_shift_res->end))
                    ];
                    if ($prev_shift_res->end != '' && $prev_shift_res->end != null && $prev_shift_res->end > 0) {
                        $seconds = strtotime($request->start) - strtotime($prev_shift_res->end);
                    } else {
                        $seconds = strtotime($request->start) - strtotime($prev_shift_res->end);
                    }
                    $hours = $seconds / (60 * 60);
                    $guard->previous_shift_diff = $hours;
    
                    if ($hours < 8 && $max_hours > 12) {
                        $is_available = false;
                    }
                } else {
                    $prev_shift = [];
                }
                $next_shift_res =  DB::table('job_rosters')->where('guard_id', $guard->id)->where('start', '>', dbFormateDateTimeStart($request->end))->orderBy('start', 'asc')->first();
                if (!empty($next_shift_res)) {
    
                    $site = DB::table('sites')->where('id', $next_shift_res->site_id)->first();
                    $site_name = !empty($site) ? $site->site_name : 'N/A';
                    $next_shift = [
                        'guard_id' => $next_shift_res->guard_id,
                        'date' => Date("d-m-Y", strtotime($next_shift_res->start)),
                        'start' => Date("H:i", strtotime($next_shift_res->start)),
                        'end' => Date("H:i", strtotime($next_shift_res->end)),
                        'site' => $site_name,
                    ];
                    $seconds = strtotime($next_shift_res->start) - strtotime($request->end);
                    $hours = $seconds / (60 * 60);
                    $guard->next_shift_diff = $hours;
                    if ($hours < 8 && $max_hours > 12) {
                        $is_available = false;
                    }
                } else {
                    $next_shift = [];
                }
                // return $is_available;
                $guard->next_shift = $next_shift;
                $guard->prev_shift = $prev_shift;
                if ($is_available) {
                    $available_gaurds[] = $guard;
                }
            }
        }
    }
    $next_shift = '';
    $prev_shift = '';

    if ($call_from == 'api') {
        if (count($available_gaurds) > 0) {
            return response()->json(['success' => true, 'siteId' => $request->siteId, 'guards' => $available_gaurds, 'prev_shift' => $prev_shift, 'next_shift' => $next_shift]);
        } else {
            return response()->json(['success' => false, 'siteId' => $request->siteId, 'guards' => $available_gaurds, 'prev_shift' => $prev_shift, 'next_shift' => $next_shift]);
        }
    } else {
        return response()->json(['siteId' => $request->siteId, 'guards' => $available_gaurds, 'prev_shift' => $prev_shift, 'next_shift' => $next_shift]);
    }
}
function count_today_working_hours($start, $end, $guard_id)
{
    $today_start = strtotime(date('Y-m-d 00:00:00', strtotime($start)));
    $today_end = strtotime(date('Y-m-d 23:59:59', strtotime($start)));
    if (strtotime($end) > $today_end) {
        $current_shift_today_duration = ($today_end - strtotime($start)) / (60 * 60);
    } else {
        $current_shift_today_duration = (strtotime($end) - strtotime($start)) / (60 * 60);
    }
    $today_working_hours = 0;
    $jobs_today = JobRoster::where('start', '<', Date('Y-m-d H:i', $today_start))
    ->where('end', '>', Date('Y-m-d H:i', $today_start))
    ->where('guard_id', '=', $guard_id);
    $jobs_today = $jobs_today->get();

    $jobs_today2 = JobRoster::where('start', '>=', Date('Y-m-d H:i', $today_start))
    ->where('end', '<=', Date('Y-m-d H:i', $today_end))
    ->where('guard_id', '=', $guard_id);
    $jobs_today2 = $jobs_today2->get();


    $jobs_today1 = JobRoster::where('start', '<', Date('Y-m-d H:i', $today_end))
    ->where('end', '>', Date('Y-m-d', $today_end))
    ->where('guard_id', '=', $guard_id);
    $jobs_today1 = $jobs_today1->get();
    foreach ($jobs_today as $jt) {
        if ($jt->job_end == '') {
            $jt->job_end = time();
        }
        if ($jt->job_start < $today_start) {
            $jt->job_start = $today_start;
        }
        if ($jt->job_end > $today_end) {
            $jt->job_end = $today_end;
        }
        $today_working_hours += (($jt->job_end - $jt->job_start) / (60 * 60));
    }
    foreach ($jobs_today2 as $jt2) {
        if ($jt2->job_end == '') {
            $jt2->job_end = time();
        }
        if ($jt2->job_start < $today_start) {
            $jt2->job_start = $today_start;
        }
        if ($jt2->job_end > $today_end) {
            $jt2->job_end = $today_end;
        }
        $today_working_hours += (($jt2->job_end - $jt2->job_start) / (60 * 60));
    }
    foreach ($jobs_today1 as $jt1) {
        if ($jt1->job_end == '') {
            $jt1->job_end = time();
        }
        if ($jt1->job_start < $today_start) {
            $jt1->job_start = $today_start;
        }
        if ($jt1->job_end > $today_end) {
            $jt1->job_end = $today_end;
        }
        $today_working_hours += (($jt1->job_end - $jt1->job_start) / (60 * 60));
    }
    return round($today_working_hours + $current_shift_today_duration);
}
# OLD
// public function addNewShift(Request $request)
// {
//     // $jobNewRoster = JobNewRoster::where('id', $request->newJobRsoterId)->first();
//     // if(($jobNewRoster->start  >=  dbFormateDateTime($request->start)) && ($jobNewRoster->end <= dbFormateDateTime($request->start))){

//     // $updateShift = JobRoster::where('id', $request->id)->first();
    
//     // $old_data = $updateShift;


//     $cus_payrate = '';
//     $cus_chargerate = '';
//     if($request->has('guard_id') && (!empty($request->guard_id))){
        
//         $checkAdmin = checkAdmin($request->admin_id);
//         if($checkAdmin != 'super-admin'){
//             $diff =  checkShiftDayHours($request->start, $request->end, $request->guard_id);
//             if($diff < 9){
//                 return response()->json(['success' => false, 'message' => 'You must rest for eight hours before starting a new shift!']); 
//             }
//         }
//         //check hours lay!
//         $hours = $this->getShiftHours(dbFormateDateTime($request->start), dbFormateDateTime($request->end), $request->site_id);
//         $check = checkGuardShiftTiming($request->start, $request->end, $request->guard_id, $request->roster_id);
//         $guardWorkingHours = calCulateGuardWeekHours(dbFormateDateTime($request->start), dbFormateDateTime($request->end));
//         $guardWorkLimitation = checkGuardWorkLimitation($request->guard_id, $guardWorkingHours);
//         $check2 = checkGuardDocuments($request->guard_id);
//         if(!empty($check['start']) || !empty($check['end']) || !empty($check['conf']) || (!empty($check2) && $check2 != 'active' ) || !empty($guardWorkLimitation)){
//             // $checkAdmin  = checkAdmin($request->admin_id);
//             if($checkAdmin == 'admin'){
//                 return response()->json(['success' => false, 'message' => '<b>Sorry, there is a scheduling conflict for this shift, so it has not been created yet.</b>', 'code'=> 404]);
//             }
//             if($checkAdmin == 'super-admin' && $request->has('shift_confirm') && $request->shift_confirm == 'yes'){

//                 if($request->has('custome_rate') && $request->custome_rate == true){
//                     if($request->has('custome_payrate') && $request->custome_payrate == true){
//                         $cus_payrate = json_encode($request->manualPayRate);
//                     }
//                 }

//                 if($request->has('custome_rate') && $request->custome_rate == true){
//                     if($request->has('custome_chagerate') && $request->custome_chagerate == true){
//                         $cus_chargerate = json_encode($request->manualChargeRate);
//                     }
//                 }
//                 $addNewShift = new JobRoster();
//                 $addNewShift->site_id = $request->site_id;
//                 $addNewShift->guard_id = ($request->has('guard_id') && !empty($request->guard_id) ? $request->guard_id : '');
//                 $addNewShift->start = dbFormateDateTime($request->start);
//                 $addNewShift->end = dbFormateDateTime($request->end);
//                 $addNewShift->shift_payable = !empty($request->shift_payable) && ($request->has('shift_payable')) ? $request->shift_payable : 'yes';
//                 $addNewShift->shift_chargeable = !empty($request->shift_chargeable) && ($request->has('shift_payable')) ? $request->shift_chargeable : 'yes';
//                 $addNewShift->custome_rate = ($request->custome_rate == 'on' ? true : false);
//                 $addNewShift->payrate_level = $request->payrate_level;
//                 $addNewShift->payrate = $request->payrate;
//                 $addNewShift->chargerate_level = $request->chargerate_level;
//                 $addNewShift->chargerate = $request->chargerate;
//                 $addNewShift->un_published_shift = ($request->un_published_shift == 'on' ? true : false);
//                 $addNewShift->public_holidays = ($request->public_holidays == 'on' ? true : false);
//                 $addNewShift->covid_marshal = ($request->covid_marshal == 'on' ? true : false);
//                 $addNewShift->training = ($request->training == 'on' ? true : false);
//                 $addNewShift->continuation = ($request->continuation == 'on' ? true : false);
//                 $addNewShift->over_time = ($request->over_time == 'on') ? true : false;
//                 $addNewShift->over_time_value = ($request->over_time_value) ? $request->over_time_value : 0;
//                 $addNewShift->travel_time = ($request->travel_time == 'on') ? true : false;
//                 $addNewShift->travel_time_value = ($request->travel_time_value) ? $request->travel_time_value : 0;
//                 $addNewShift->shift_create_status = 'pending';
//                 $addNewShift->total_week_hours = $guardWorkingHours;
//                 $addNewShift->shift_type = ($request->has('shift_type') && !empty($request->shift_type) ? $request->shift_type : '');
//                 $addNewShift->conflict = (!empty($check['conf']) ? $check['conf'] : '');
//                 $addNewShift->doc_conf = (!empty($check2) ? $check2 : '');
//                 $addNewShift->work_limitaion_conf = (!empty($guardWorkLimitation['difference']) ? $guardWorkLimitation['difference'] : '');
//                 $addNewShift->conf_start = (!empty($check['start']) ? dbFormateDateTime($check['start']) : '');
//                 $addNewShift->conf_end = (!empty($check['end']) ? dbFormateDateTime($check['end']) : '');
//                 $addNewShift->morning_hours = (!empty($hours['morning']) ? roundHours($hours['morning']) : 0.0);
//                 $addNewShift->night_hours = (!empty($hours['night']) ? roundHours($hours['night']) : 0.0);
//                 $addNewShift->saturday_morning_hours = (!empty($hours['saturday_morning']) ? roundHours($hours['saturday_morning']) : 0.0);
//                 $addNewShift->saturday_night_hours = (!empty($hours['saturday_night']) ? roundHours($hours['saturday_night']) : 0.0);
//                 $addNewShift->sunday_morning_hours = (!empty($hours['sunday_morning']) ? roundHours($hours['sunday_morning']) : 0.0);
//                 $addNewShift->sunday_night_hours = (!empty($hours['sunday_night']) ? roundHours($hours['sunday_night']) : 0.0);
//                 $addNewShift->ph_morning_hours = (!empty($hours['ph_morning']) ? roundHours($hours['ph_morning']) : 0.0);
//                 $addNewShift->ph_night_hours = (!empty($hours['ph_night']) ? roundHours($hours['ph_night']) : 0.0);
//                 $addNewShift->last_update = time();
//                 $addNewShift->hours = roundHours($guardWorkingHours);
//                 $addNewShift->publish_status = $request->publish_status;
//                 $addNewShift->custome_rate = $request->custome_rate;
//                 $addNewShift->custome_payrate = $request->custome_payrate;
//                 $addNewShift->custome_chagerate = $request->custome_chagerate;
//                 $addNewShift->manualPayRate = $cus_payrate;
//                 $addNewShift->manualChargeRate = $cus_chargerate;
//                 $addNewShift->unprofile_name = $request->unprofile_name;
//                 $addNewShift->po_wo = $request->po_wo;
//                 $addNewShift->job_instrcutions = $request->job_instrcutions;
//                 $addNewShift->job_instruction_text = $request->job_instruction_text;
//                 $addNewShift->roster_id = $request->roster_id;
//                 $addNewShift->save();
//                 if($request->publish_status == 1){
//                     //fz 
//                     if($request->has('guard_id') && !empty($request->guard_id) && isset($guard->notification_token)){
//                     $guard = Guard::where('id', $request->guard_id)->first();
//                     $prams['message'] = 'Roster for the week '.usaToAusDateTime($request->start).' - '.usaToAusDateTime($request->end).' has been published. Please open app and confirm your roster.';
//                     $prams['subject'] = 'Roster Published';
//                     $prams['email'] = $guard->email;
//                     generalEmails($prams);

//                     $prams['message'] = 'Roster for the week '.usaToAusDateTime($request->start).' - '.usaToAusDateTime($request->end).' has been published.';
//                     $prams['title'] = 'Roster Published';
//                     $prams['page'] = 'roster';
//                     $prams['notification_token'] = $guard->notification_token;
//                     send_push_notification($prams);

//                     }
//                 }
//                 jobRosterActions($request->admin_id, 'add_shift', $addNewShift->id, 'job_roster');

//                 $admin_name = getAdminName($request->admin_id);
//                 $currnet_time = time();
//                 shiftCompleteActivity($addNewShift->id, $admin_name. ' Added this Shift', 'add_shift', $addNewShift->id, $currnet_time, $request->admin_id);

//                 if($request->has('job_roster_tasks') && !empty($request->job_roster_tasks)){
//                     foreach ($request->job_roster_tasks as $key => $task) {
//                         $newTask =  new JobRosterTask();
//                         $newTask->job_roster_id = $addNewShift->id;
//                         $newTask->task = $task['task'];
//                         $newTask->task_start = dbFormateDateTime($task['task_start']);
//                         $newTask->task_end = dbFormateDateTime($task['task_end']);
//                         $newTask->save();
//                         jobRosterActions($request->admin_id, 'add_shift_tasks', $newTask->id,'job_roster_tasks');
//                     }
//                 }
//                     //<br><p>This shift has conflict with another shift dated <b>'.usaToAusDateTime($check['start']).'</b><b>'.usaToAusDateTime($check['end']).'</b></p>'.'<p>'.'<b>'."Expried". ' ' .$check2.'</b>'.'</p>'
//                     //<br><p>This shift has conflict with another shift dated <b>'.usaToAusDateTime($check['start']).'</b><b>'.usaToAusDateTime($check['end']).'</b></p>
//                 if(!empty($check['start']) && !empty($check['end']) && !empty($check['conf']) && !empty($check2)){
//                     return response()->json(['success' => true, 'message' => '<b>Conflicted Shift</b>', 'code'=> 200]); 
//                 }elseif(!empty($check['start']) && !empty($check['end']) && !empty($check['conf'])){
//                     return response()->json(['success' => true, 'message' => '<b>Conflicted Shift</b>', 'code'=> 200]);
//                 }else {
//                     return response()->json(['success' => true, 'message' => '<b>Conflicted Shift</b>', 'code'=> 200]);
//                 }
//             }

//             if($checkAdmin == 'super-admin'){
//                 return response()->json(['success' => false, 'message' => '<b>Hi, Super Admin this shift has conflict <br> Do you really want to create this shift !</b>', 'code'=> 404]);
//             }
//         }else{

//             if($request->has('custome_rate') && $request->custome_rate == true){
//                 if($request->has('custome_payrate') && $request->custome_payrate == true){
//                     $cus_payrate = json_encode($request->manualPayRate);
//                 }
//             }

//             if($request->has('custome_rate') && $request->custome_rate == true){
//                 if($request->has('custome_chagerate') && $request->custome_chagerate == true){
//                     $cus_chargerate = json_encode($request->manualChargeRate);
//                 }
//             }

            

//             $addNewShift = new JobRoster();
//             $addNewShift->site_id = $request->site_id;
//             $addNewShift->guard_id = ($request->has('guard_id') && !empty($request->guard_id) ? $request->guard_id : '');
//             $addNewShift->start = dbFormateDateTime($request->start);
//             $addNewShift->end = dbFormateDateTime($request->end);
//             $addNewShift->shift_payable = !empty($request->shift_payable) && ($request->has('shift_payable')) ? $request->shift_payable : 'yes';
//             $addNewShift->shift_chargeable = !empty($request->shift_chargeable) && ($request->has('shift_payable')) ? $request->shift_chargeable : 'yes';
//             $addNewShift->custome_rate = ($request->custome_rate == 'on' ? true : false);
//             $addNewShift->payrate_level = $request->payrate_level;
//             $addNewShift->payrate = $request->payrate;
//             $addNewShift->chargerate_level = $request->chargerate_level;
//             $addNewShift->chargerate = $request->chargerate;
//             $addNewShift->un_published_shift = ($request->un_published_shift == 'on' ? true : false);
//             $addNewShift->public_holidays = ($request->public_holidays == 'on' ? true : false);
//             $addNewShift->covid_marshal = ($request->covid_marshal == 'on' ? true : false);
//             $addNewShift->training = ($request->training == 'on' ? true : false);
//             $addNewShift->continuation = ($request->continuation == 'on' ? true : false);
//             $addNewShift->over_time = ($request->over_time == 'on') ? true : false;
//             $addNewShift->over_time_value = ($request->over_time_value) ? $request->over_time_value : 0;
//             $addNewShift->travel_time = ($request->travel_time == 'on') ? true : false;
//             $addNewShift->travel_time_value = ($request->travel_time_value) ? $request->travel_time_value : 0;
//             $addNewShift->shift_create_status = 'pending';
//             $addNewShift->total_week_hours = $guardWorkingHours;
//             $addNewShift->shift_type = ($request->has('shift_type') && !empty($request->shift_type) ? $request->shift_type : '');
//             $addNewShift->morning_hours = (!empty($hours['morning']) ? roundHours($hours['morning']) : 0.0);
//             $addNewShift->night_hours = (!empty($hours['night']) ? roundHours($hours['night']) : 0.0);
//             $addNewShift->saturday_morning_hours = (!empty($hours['saturday_morning']) ? roundHours($hours['saturday_morning']) : 0.0);
//             $addNewShift->saturday_night_hours = (!empty($hours['saturday_night']) ? roundHours($hours['saturday_night']) : 0.0);
//             $addNewShift->sunday_morning_hours = (!empty($hours['sunday_morning']) ? roundHours($hours['sunday_morning']) : 0.0);
//             $addNewShift->sunday_night_hours = (!empty($hours['sunday_night']) ? roundHours($hours['sunday_night']) : 0.0);
//             $addNewShift->ph_morning_hours = (!empty($hours['ph_morning']) ? roundHours($hours['ph_morning']) : 0.0);
//             $addNewShift->ph_night_hours = (!empty($hours['ph_night']) ? roundHours($hours['ph_night']) : 0.0);
//             $addNewShift->last_update = time();
//             $addNewShift->hours = roundHours($guardWorkingHours);
//             $addNewShift->publish_status = $request->publish_status;
//             $addNewShift->custome_rate = $request->custome_rate;
//             $addNewShift->custome_payrate = $request->custome_payrate;
//             $addNewShift->custome_chagerate = $request->custome_chagerate;
//             $addNewShift->manualPayRate = $cus_payrate;
//             $addNewShift->manualChargeRate = $cus_chargerate;
//             $addNewShift->unprofile_name = $request->unprofile_name;
//             $addNewShift->po_wo = $request->po_wo;
//             $addNewShift->job_instrcutions = $request->job_instrcutions;
//             $addNewShift->job_instruction_text = $request->job_instruction_text;
//             $addNewShift->roster_id = $request->roster_id;
//             $addNewShift->save();
//             if($request->has('publish_status') && $request->publish_status == 1){
//                 //fz  
//                 if($request->has('guard_id') && !empty($request->guard_id) && isset($guard->notification_token)){

//                     $guard = Guard::where('id', $request->guard_id)->first();
//                     $prams['message'] = 'Roster for the week '.usaToAusDateTime($request->start).' - '.usaToAusDateTime($request->end).' has been published.';
//                     $prams['title'] = 'Roster Published';
//                     $prams['page'] = 'roster';
//                     $prams['notification_token'] = $guard->notification_token;
//                     send_push_notification($prams);

//                     $prams['message'] = 'Roster for the week '.usaToAusDateTime($request->start).' - '.usaToAusDateTime($request->end).' has been published. Please open app and confirm your roster.';
//                     $prams['subject'] = 'Roster Published';
//                     $prams['email'] = $guard->email;
//                     generalEmails($prams);

//                     sendSmsToGuard($guard->phone, 'Hi '.$guard->first_name.' '.$guard->last_name.' '. 'your shift has been published successfully!');

//                 }
//             }
//             jobRosterActions($request->admin_id, 'add_shift', $addNewShift->id, 'job_roster');

//             $admin_name = getAdminName($request->admin_id);
//             $currnet_time = time();
//             shiftCompleteActivity($addNewShift->id, $admin_name. ' Added this Shift', 'add_shift', $addNewShift->id, $currnet_time, $request->admin_id);

//             if($request->has('job_roster_tasks') && !empty($request->job_roster_tasks)){
//                 foreach ($request->job_roster_tasks as $key => $task) {
//                     $newTask =  new JobRosterTask();
//                     $newTask->job_roster_id = $addNewShift->id;
//                     $newTask->task = $task['task'];
//                     $newTask->task_start = dbFormateDateTime($task['task_start']);
//                     $newTask->task_end = dbFormateDateTime($task['task_end']);
//                     $newTask->save();
//                     jobRosterActions($request->admin_id, 'add_shift_tasks', $newTask->id, 'job_roster_tasks');
//                 }
//             }
//             if($request->has('shift_type') && $request->shift_type == 'template'){
//                 return response()->json(['success' => true, 'message' => 'Template added', 'code' => 200]);
//             }elseif($request->has('shift_type') && $request->shift_type == 'template_rost'){
//                 return response()->json(['success' => true, 'message' => 'Shift Template Created', 'code' => 200]);     
//             }
//             else{
//                 return response()->json(['success' => true, 'message' => 'Shift Created', 'code' => 200]);
//             }
//         }
//     }else{
//         $guardWorkingHours = calCulateGuardWeekHours(dbFormateDateTime($request->start), dbFormateDateTime($request->end));
//         $hours = $this->getShiftHours(dbFormateDateTime($request->start), dbFormateDateTime($request->end), $request->site_id);

//         if($request->has('custome_rate') && $request->custome_rate == true){
//             if($request->has('custome_payrate') && $request->custome_payrate == true){
//                 $cus_payrate = json_encode($request->manualPayRate);
//             }
//         }

//         if($request->has('custome_rate') && $request->custome_rate == true){
//             if($request->has('custome_chagerate') && $request->custome_chagerate == true){
//                 $cus_chargerate = json_encode($request->manualChargeRate);
//             }
//         }

//         $addNewShift = new JobRoster();
//         $addNewShift->site_id = $request->site_id;
//         $addNewShift->guard_id = ($request->has('guard_id') && !empty($request->guard_id) ? $request->guard_id : '');
//         $addNewShift->start = dbFormateDateTime($request->start);
//         $addNewShift->end = dbFormateDateTime($request->end);
//         $addNewShift->shift_payable = !empty($request->shift_payable) && ($request->has('shift_payable')) ? $request->shift_payable : 'yes';
//         $addNewShift->shift_chargeable = !empty($request->shift_chargeable) && ($request->has('shift_payable')) ? $request->shift_chargeable : 'yes';
//         $addNewShift->custome_rate = ($request->custome_rate == 'on' ? true : false);
//         $addNewShift->payrate_level = $request->payrate_level;
//         $addNewShift->payrate = $request->payrate;
//         $addNewShift->chargerate_level = $request->chargerate_level;
//         $addNewShift->chargerate = $request->chargerate;
//         $addNewShift->un_published_shift = ($request->un_published_shift == 'on' ? true : false);
//         $addNewShift->public_holidays = ($request->public_holidays == 'on' ? true : false);
//         $addNewShift->covid_marshal = ($request->covid_marshal == 'on' ? true : false);
//         $addNewShift->training = ($request->training == 'on' ? true : false);
//         $addNewShift->continuation = ($request->continuation == 'on' ? true : false);
//         $addNewShift->over_time = ($request->over_time == 'on') ? true : false;
//         $addNewShift->over_time_value = ($request->over_time_value) ? $request->over_time_value : 0;
//         $addNewShift->travel_time = ($request->travel_time == 'on') ? true : false;
//         $addNewShift->travel_time_value = ($request->travel_time_value) ? $request->travel_time_value : 0;
//         $addNewShift->shift_create_status = 'pending';
//         $addNewShift->shift_type = ($request->has('shift_type') && !empty($request->shift_type) ? $request->shift_type : '');
//         $addNewShift->morning_hours = (!empty($hours['morning']) ? roundHours($hours['morning']) : 0.0);
//         $addNewShift->night_hours = (!empty($hours['night']) ? roundHours($hours['night']) : 0.0);
//         $addNewShift->saturday_morning_hours = (!empty($hours['saturday_morning']) ? roundHours($hours['saturday_morning']) : 0.0);
//         $addNewShift->saturday_night_hours = (!empty($hours['saturday_night']) ? roundHours($hours['saturday_night']) : 0.0);
//         $addNewShift->sunday_morning_hours = (!empty($hours['sunday_morning']) ? roundHours($hours['sunday_morning']) : 0.0);
//         $addNewShift->sunday_night_hours = (!empty($hours['sunday_night']) ? roundHours($hours['sunday_night']) : 0.0);
//         $addNewShift->ph_morning_hours = (!empty($hours['ph_morning']) ? roundHours($hours['ph_morning']) : 0.0);
//         $addNewShift->ph_night_hours = (!empty($hours['ph_night']) ? roundHours($hours['ph_night']) : 0.0);
//         $addNewShift->last_update = time();
//         $addNewShift->hours = roundHours($guardWorkingHours);
//         $addNewShift->custome_rate = $request->custome_rate;
//         $addNewShift->custome_payrate = $request->custome_payrate;
//         $addNewShift->custome_chagerate = $request->custome_chagerate;
//         $addNewShift->manualPayRate = $cus_payrate;
//         $addNewShift->manualChargeRate = $cus_chargerate;
//         $addNewShift->unprofile_name = $request->unprofile_name;
//         $addNewShift->po_wo = $request->po_wo;
//         $addNewShift->job_instrcutions = $request->job_instrcutions;
//         $addNewShift->job_instruction_text = $request->job_instruction_text;
//         $addNewShift->roster_id = $request->roster_id;
//         $addNewShift->save();
//         jobRosterActions($request->admin_id, 'add_shift', $addNewShift->id, 'job_roster');

//         $admin_name = getAdminName($request->admin_id);    
//         $currnet_time = time();
//         shiftCompleteActivity($addNewShift->id, $admin_name. ' Added this Shift', 'add_shift', $addNewShift->id, $currnet_time, $request->admin_id);

//         if($request->has('job_roster_tasks') && !empty($request->job_roster_tasks)){
//             foreach ($request->job_roster_tasks as $key => $task) {
//                 $newTask =  new JobRosterTask();
//                 $newTask->job_roster_id = $addNewShift->id;
//                 $newTask->task = $task['task'];
//                 $newTask->task_start = dbFormateDateTime($task['task_start']);
//                 $newTask->task_end = dbFormateDateTime($task['task_end']);
//                 $newTask->save();
//                 jobRosterActions($request->admin_id, 'add_shift_tasks', $newTask->id, 'job_roster_tasks');
//             }
//         }
//         if($request->has('shift_type') && $request->shift_type == 'template'){
//             return response()->json(['success' => true, 'message' => 'Template is created in'.' '.getRosterName($request->roster_id), 'code' => 200]);    
//         }elseif($request->has('shift_type') && $request->shift_type == 'template_rost'){
//             return response()->json(['success' => true, 'message' => 'New shift template is created successfully', 'code' => 200]);     
//         }
//         else{
//             // return response()->json(['success' => true, 'message' => 'Shift addition at the'.' '.getRosterName($request->roster_id), 'code' => 200]);
//             return response()->json(['success' => true, 'message' => 'Shift Created', 'code' => 200]);
//         }
//     }
//     //   }else{
//     //     return response()->json(['success' => false, 'message' => 'Sorry you cannot created shift in this date!']);
//     //   }


// }




# NEW
public function shiftDropAndCopy(Request $request){
    $shiftDropAndCopy = JobRoster::where('id', $request->roster_id)->with('jobRosterTask')->first();
    
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
                if($request->has('type') || $request->type == 'copy_shift'){ 
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
                # CHECK SHIFT CONFILICT
                $start_time = dbFormateDateTime($request->start);
                $end_time = dbFormateDateTime($request->end);
                if($request->has('type') && $request->type == 'copy_shift'){
                    $start_time = dbFormateDateTime($request->newStart);
                    $end_time = dbFormateDateTime($request->newEnd);
                }
                if($request->has('type') && $request->type == 'drop'){
                    $conflictingShift = JobRoster::where('guard_id', $request->guard_id)
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
                    $conflictingShift = JobRoster::where('guard_id', $request->guard_id)
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

                $guardData = $this->getSingleGuard($request->guard_id);

                if($guardData->empDetails->guard_document_type == 'student_visa'){
                    //old code
                    $timestamp1 = strtotime($this->parseDateWithAutoDetection($request->start));
                    $timestamp2 = strtotime($this->parseDateWithAutoDetection($request->end));

                    if (empty($timestamp2)) {
                        $timestamp2 = strtotime($request->start);
                    }

                    $week_array = $this->calculateFutureMonthFourthnight($request->start);
                    $newCurrentHours = abs($timestamp2 - $timestamp1) / (60 * 60);

                    if($request->has('type') && $request->type == 'copy_shift'){
                        $timestamp1 = strtotime($this->parseDateWithAutoDetection($request->newStart));
                        $timestamp2 = strtotime($this->parseDateWithAutoDetection($request->newEnd));
                        
                        if (empty($timestamp2)) {
                        $timestamp2 = strtotime($request->newStart);
                        }
                    $week_array = $this->calculateFutureMonthFourthnight($request->newStart);
                    $newCurrentHours = abs($timestamp2 - $timestamp1) / (60 * 60);
                    }

                    $currentMonthData = $this->get_current_month_hours_guards_part_student($request->guard_id, $week_array['week_start'], $week_array['week_end'], 0);

                    $addedHours = 0;
                    
                    $requeststart = DateTime::createFromFormat('m-d-Y H:i', $request->start);
                    $requestend = DateTime::createFromFormat('m-d-Y H:i', $request->end);

                    if($request->has('type') && $request->type == 'copy_shift'){
                    $requeststart = DateTime::createFromFormat('m-d-Y H:i', $request->newStart);
                    $requestend = DateTime::createFromFormat('m-d-Y H:i', $request->newEnd);
                    }

                    $calander_start_date = $requeststart;
                    $calender_end_date = $requestend;
                    $fortnight_start_date = new DateTime($week_array['week_start']);
                    $fortnight_end_date = new DateTime($week_array['week_end']);
                    if (!empty($currentMonthData)) {
                        foreach ($currentMonthData as $currentMonthDatas) {
                            $timestamps1 = strtotime($currentMonthDatas->start);
                            $timestamps2 = strtotime($currentMonthDatas->end);
                            
                            $startDay = date('w', $timestamps1);
                            $endDay = date('w', $timestamps2);
                            
                            if ($startDay == 0 && $endDay == 1) {
                                $midnightSunday = strtotime('tomorrow', $timestamps1) - 1;
                                $sundayHours = abs($midnightSunday - $timestamps1) / 3600;
                                
                                $mondayStart = strtotime('tomorrow 00:00:00', $timestamps1);
                                $mondayHours = abs($timestamps2 - $mondayStart) / 3600;
                                
                                $addedHours += $mondayHours;
                            } 
                            else if ($startDay == 6 && $endDay == 0) {
                                $midnightSaturday = strtotime('tomorrow', $timestamps1) - 1;
                                $saturdayHours = abs($midnightSaturday - $timestamps1) / 3600;
                                
                                $addedHours += $saturdayHours;
                            }
                            else if ($startDay == 0 && $endDay == 0) {
                                $addedHours += abs($timestamps2 - $timestamps1) / 3600;
                            }
                            else {
                                $addedHours += abs($timestamps2 - $timestamps1) / 3600;
                            }
                        }
                    }

                    // $addedHours = ceil($addedHours);
                    $dates_periods = array();
                    $period = new DatePeriod(
                        new DateTime($fortnight_start_date->format("Y-m-d")),
                        new DateInterval('P1D'),
                        new DateTime($fortnight_end_date->format("Y-m-d"))
                    );
                    foreach ($period as $key => $value) {
                        array_push($dates_periods, $value->format('Y-m-d'));
                    }
                    array_push($dates_periods, $fortnight_end_date->format("Y-m-d"));
                
                    if (in_array($calander_start_date->format("Y-m-d"), $dates_periods)) {
                        $dateTime = $this->parseDateWithAutoDetection($request->start);
                        $endTime = $this->parseDateWithAutoDetection($request->end);
                        $dayName = $dateTime->format('l');
                        $endDayName = $endTime->format('l');

                        if ($endTime->lt($dateTime)) {
                            $endTime->addDay();
                        }

                        $shiftStartDate = $dateTime->format('Y-m-d');
                        $shiftEndDate = $endTime->format('Y-m-d');
                        if (!in_array($shiftEndDate, $dates_periods)) {
                                
                            $fortnightEnd = Carbon::parse($fortnight_end_date)->endOfDay();
                            $current = $dateTime->copy();
                            $hoursInCurrentFortnight = 0;   
                            while ($current->lt($fortnightEnd)) {
                                $hourEnd = min($current->copy()->addHour(), $fortnightEnd);
                                
                                if ($current->format('l') != 'Sunday') {
                                    $hoursInCurrentFortnight += $current->diffInHours($hourEnd, true);
                                }
                                
                                $current = $hourEnd;
                            }                         
                            $addedHours = $addedHours + $hoursInCurrentFortnight;
                        } else {
                            if(($dayName == 'Saturday' && $endDayName == 'Sunday')){
                                $sundayStart = $dateTime->copy()->addDay()->startOfDay();
                                $satHours = $dateTime->diffInHours($sundayStart, false);
                                
                                $addedHours = $addedHours + $satHours;
                            }elseif(($dayName == 'Sunday' && $endDayName == 'Monday')){
                                $mondayStart = $dateTime->copy()->addDay()->startOfDay();
                                $sunHours = $dateTime->diffInHours($mondayStart, false);
                                
                                $addedHours = ($addedHours + $newCurrentHours) - $sunHours;
                            }elseif(($dayName == 'Sunday' && $endDayName == 'Sunday')){
                                $addedHours = $addedHours - $newCurrentHours;
                            }else{
                                $addedHours = $addedHours + $newCurrentHours;
                            }
                        }
                        if($guardData->empDetails->limit_exceed == 1)
                        {
                            $guardStart = new DateTime($guardData->empDetails->start_time);
                            $guardEnd = new DateTime($guardData->empDetails->end_time);
                            $interval = new DateInterval('P1D');
                            $dateRange = new DatePeriod($guardStart, $interval, $guardEnd->modify('+1 day'));

                            $guardDates = [];
                            foreach ($dateRange as $date) {
                                $guardDates[] = $date->format('Y-m-d');
                            }
                            $hasCompleteFortnight = false;

                            $guardDateCount = count($guardDates);

                            for ($i = 0; $i <= $guardDateCount - 14; $i++) {
                                $fourteenDays = array_slice($guardDates, $i, 14);
                                
                                $allExist = true;
                                foreach ($fourteenDays as $day) {
                                    if (!in_array($day, $dates_periods)) {
                                        $allExist = false;
                                        break;
                                    }
                                }
                                
                                if ($allExist) {
                                    $hasCompleteFortnight = true;
                                    break;
                                }
                            }

                            if ($hasCompleteFortnight) {
                              $totalWorkingHours = 72;   
                              if ($addedHours > $totalWorkingHours) {
                                    if (isset($request->otp)) {
                                        
                                        $getAdmin = User::where('id', 257)->first();

                                        if (!$getAdmin || !$getAdmin->google2fa_secret) {
                                            return response()->json([
                                                'success' => false,
                                                'message' => 'Manager does not have 2FA setup.',
                                                'data' => [
                                                    'fortnight_limit' => $totalWorkingHours,
                                                    'total_hours' => $addedHours,
                                                    'exceed_hours' => $addedHours - $totalWorkingHours,
                                                ],
                                            ]);
                                        }
                                        
                                        $googleAuthenticator = new Google2FA();
                                        $valid = $googleAuthenticator->verifyKey($getAdmin->google2fa_secret, $request->otp);
                                        
                                        if (!$valid) {
                                            return response()->json([
                                                'success' => false,
                                                'message' => 'Wrong OTP! Please try again.',
                                                'require_otp' => true,
                                                'data' => [
                                                    'fortnight_limit' => $totalWorkingHours,
                                                    'total_hours' => $addedHours,
                                                    'exceed_hours' => $addedHours - $totalWorkingHours,
                                                ],
                                            ]);
                                        }
                                        
                                    } else {
                                        return response()->json([
                                            'success' => false,
                                            'message' => 'Hours exceed limit. Manager OTP required.',
                                            'require_otp' => true,
                                            'data' => [
                                                'fortnight_limit' => $totalWorkingHours,
                                                'total_hours' => $addedHours,
                                                'exceed_hours' => $addedHours - $totalWorkingHours,
                                            ],
                                        ]);
                                    }
                                }  
                            } else {
                            $totalWorkingHours = 48;
                            if ($addedHours > $totalWorkingHours) {
                                    if (isset($request->otp)) {
                                        
                                        $getAdmin = User::where('id', 257)->first();

                                        if (!$getAdmin || !$getAdmin->google2fa_secret) {
                                            return response()->json([
                                                'success' => false,
                                                'message' => 'Manager does not have 2FA setup.',
                                                'data' => [
                                                    'fortnight_limit' => $totalWorkingHours,
                                                    'total_hours' => $addedHours,
                                                    'exceed_hours' => $addedHours - $totalWorkingHours,
                                                ],
                                            ]);
                                        }
                                        
                                        $googleAuthenticator = new Google2FA();
                                        $valid = $googleAuthenticator->verifyKey($getAdmin->google2fa_secret, $request->otp);
                                        
                                        if (!$valid) {
                                            return response()->json([
                                                'success' => false,
                                                'message' => 'Wrong OTP! Please try again.',
                                                'require_otp' => true,
                                                'data' => [
                                                    'fortnight_limit' => $totalWorkingHours,
                                                    'total_hours' => $addedHours,
                                                    'exceed_hours' => $addedHours - $totalWorkingHours,
                                                ],
                                            ]);
                                        }
                                        
                                    } else {
                                        return response()->json([
                                            'success' => false,
                                            'message' => 'Hours exceed limit. Manager OTP required.',
                                            'require_otp' => true,
                                            'data' => [
                                                'fortnight_limit' => $totalWorkingHours,
                                                'total_hours' => $addedHours,
                                                'exceed_hours' => $addedHours - $totalWorkingHours,
                                            ],
                                        ]);
                                    }
                                }
                            }
                        }else{
                            $totalWorkingHours = 48;
                            if ($addedHours > $totalWorkingHours) {
                                    if (isset($request->otp)) {
                                        
                                        $getAdmin = User::where('id', 257)->first();

                                        if (!$getAdmin || !$getAdmin->google2fa_secret) {
                                            return response()->json([
                                                'success' => false,
                                                'message' => 'Manager does not have 2FA setup.',
                                                'data' => [
                                                    'fortnight_limit' => $totalWorkingHours,
                                                    'total_hours' => $addedHours,
                                                    'exceed_hours' => $addedHours - $totalWorkingHours,
                                                ],
                                            ]);
                                        }
                                        
                                        $googleAuthenticator = new Google2FA();
                                        $valid = $googleAuthenticator->verifyKey($getAdmin->google2fa_secret, $request->otp);
                                        
                                        if (!$valid) {
                                            return response()->json([
                                                'success' => false,
                                                'message' => 'Wrong OTP! Please try again.',
                                                'require_otp' => true,
                                                'data' => [
                                                    'fortnight_limit' => $totalWorkingHours,
                                                    'total_hours' => $addedHours,
                                                    'exceed_hours' => $addedHours - $totalWorkingHours,
                                                ],
                                            ]);
                                        }
                                        
                                    } else {
                                        return response()->json([
                                            'success' => false,
                                            'message' => 'Hours exceed limit. Manager OTP required.',
                                            'require_otp' => true,
                                            'data' => [
                                                'fortnight_limit' => $totalWorkingHours,
                                                'total_hours' => $addedHours,
                                                'exceed_hours' => $addedHours - $totalWorkingHours,
                                            ],
                                        ]);
                                    }
                                }
                        }
                    }   
                }else{
                    $guardOnLimitations = GuardWorkDetail::where('guard_id', $request->guard_id)->first();
                    $totalWorkingHours = 72;
                    if ($guardOnLimitations && $guardOnLimitations->work_hours_limitation_status == 1) {
                        $totalWorkingHours = $guardOnLimitations->weekly_work_hours_limitation ?? 72;

                    }
                    //old code
                    $timestamp1 = strtotime($this->parseDateWithAutoDetectionDrop($request->start));
                    $timestamp2 = strtotime($this->parseDateWithAutoDetectionDrop($request->end));

                    if (empty($timestamp2)) {
                        $timestamp2 = strtotime($request->start);
                    }

                    $week_array = $this->calculateFutureMonthFourthnight($request->start);
                    $newCurrentHours = abs($timestamp2 - $timestamp1) / (60 * 60);

                    if($request->has('type') && $request->type == 'copy_shift'){
                        $timestamp1 = strtotime($this->parseDateWithAutoDetectionDrop($request->newStart));
                        $timestamp2 = strtotime($this->parseDateWithAutoDetectionDrop($request->newEnd));
                        
                        if (empty($timestamp2)) {
                        $timestamp2 = strtotime($request->newStart);
                        }
                    $week_array = $this->calculateFutureMonthFourthnight($request->newStart);
                    $newCurrentHours = abs($timestamp2 - $timestamp1) / (60 * 60);
                    }

                    $currentMonthData = $this->get_current_month_hours_guards_part_student($request->guard_id, $week_array['week_start'], $week_array['week_end'], 0);

                    $addedHours = 0;
                    
                    $requeststart = DateTime::createFromFormat('m-d-Y H:i', $request->start);
                    $requestend = DateTime::createFromFormat('m-d-Y H:i', $request->end);

                    if($request->has('type') && $request->type == 'copy_shift'){
                    $requeststart = DateTime::createFromFormat('m-d-Y H:i', $request->newStart);
                    $requestend = DateTime::createFromFormat('m-d-Y H:i', $request->newEnd);
                    }

                    $calander_start_date = $requeststart;
                    $calender_end_date = $requestend;
                    $fortnight_start_date = new DateTime($week_array['week_start']);
                    $fortnight_end_date = new DateTime($week_array['week_end']);
                    if (!empty($currentMonthData)) {
                        foreach ($currentMonthData as $currentMonthDatas) {
                            $timestamps1 = strtotime($currentMonthDatas->start);
                            $timestamps2 = strtotime($currentMonthDatas->end);
                            
                            $startDay = date('w', $timestamps1);
                            $endDay = date('w', $timestamps2);
                            
                            if ($startDay == 0 && $endDay == 1) {
                                $midnightSunday = strtotime('tomorrow', $timestamps1) - 1;
                                $sundayHours = abs($midnightSunday - $timestamps1) / 3600;
                                
                                $mondayStart = strtotime('tomorrow 00:00:00', $timestamps1);
                                $mondayHours = abs($timestamps2 - $mondayStart) / 3600;
                                
                                $addedHours += $mondayHours;
                            } 
                            else if ($startDay == 6 && $endDay == 0) {
                                $midnightSaturday = strtotime('tomorrow', $timestamps1) - 1;
                                $saturdayHours = abs($midnightSaturday - $timestamps1) / 3600;
                                
                                $addedHours += $saturdayHours;
                            }
                            else if ($startDay == 0 && $endDay == 0) {
                                $addedHours += abs($timestamps2 - $timestamps1) / 3600;
                            }
                            else {
                                $addedHours += abs($timestamps2 - $timestamps1) / 3600;
                            }
                        }
                    }

                    // $addedHours = ceil($addedHours);
                    $dates_periods = array();
                    $period = new DatePeriod(
                        new DateTime($fortnight_start_date->format("Y-m-d")),
                        new DateInterval('P1D'),
                        new DateTime($fortnight_end_date->format("Y-m-d"))
                    );
                    foreach ($period as $key => $value) {
                        array_push($dates_periods, $value->format('Y-m-d'));
                    }
                    array_push($dates_periods, $fortnight_end_date->format("Y-m-d"));
                    
                    if (in_array($calander_start_date->format("Y-m-d"), $dates_periods)) {
                        
                        $dateTime = $this->parseDateWithAutoDetectionDrop($request->start);
                        $endTime = $this->parseDateWithAutoDetectionDrop($request->end);
                        
                        if($request->has('type') && $request->type == 'copy_shift'){
                        $dateTime = $this->parseDateWithAutoDetectionDrop($request->newStart);
                        $endTime = $this->parseDateWithAutoDetectionDrop($request->newEnd);
                        }
                        
                        $dayName = $dateTime->format('l');
                        $endDayName = $endTime->format('l');

                        if ($endTime->lt($dateTime)) {
                            $endTime->addDay();
                        }

                        $shiftStartDate = $dateTime->format('Y-m-d');
                        $shiftEndDate = $endTime->format('Y-m-d');
                        if (!in_array($shiftEndDate, $dates_periods)) {
                                
                            $fortnightEnd = Carbon::parse($fortnight_end_date)->endOfDay();
                            $current = $dateTime->copy();
                            $hoursInCurrentFortnight = 0;   
                            while ($current->lt($fortnightEnd)) {
                                $hourEnd = min($current->copy()->addHour(), $fortnightEnd);
                                
                                if ($current->format('l') != 'Sunday') {
                                    $hoursInCurrentFortnight += $current->diffInHours($hourEnd, true);
                                }
                                
                                $current = $hourEnd;
                            }                         
                            $addedHours = $addedHours + $hoursInCurrentFortnight;
                        } else {
                            if(($dayName == 'Saturday' && $endDayName == 'Sunday')){
                                $sundayStart = $dateTime->copy()->addDay()->startOfDay();
                                $satHours = $dateTime->diffInHours($sundayStart, false);
                                
                                $addedHours = $addedHours + $satHours;
                            }elseif(($dayName == 'Sunday' && $endDayName == 'Monday')){
                                $mondayStart = $dateTime->copy()->addDay()->startOfDay();
                                $sunHours = $dateTime->diffInHours($mondayStart, false);
                                
                                $addedHours = ($addedHours + $newCurrentHours) - $sunHours;
                            }elseif(($dayName == 'Sunday' && $endDayName == 'Sunday')){
                                $addedHours = $addedHours - $newCurrentHours;
                            }else{
                                $addedHours = $addedHours + $newCurrentHours;
                            }
                        }
                        
                        if ($addedHours > $totalWorkingHours) {
                            if (isset($request->otp)) {
                                    
                                $getAdmin = User::where('id', 257)->first();

                                if (!$getAdmin || !$getAdmin->google2fa_secret) {
                                    return response()->json([
                                        'success' => false,
                                        'message' => 'Manager does not have 2FA setup.',
                                        'data' => [
                                            'fortnight_limit' => $totalWorkingHours,
                                            'total_hours' => $addedHours,
                                            'exceed_hours' => $addedHours - $totalWorkingHours,
                                        ],
                                    ]);
                                }
                                
                                $googleAuthenticator = new Google2FA();
                                $valid = $googleAuthenticator->verifyKey($getAdmin->google2fa_secret, $request->otp);
                                
                                if (!$valid) {
                                    return response()->json([
                                        'success' => false,
                                        'message' => 'Wrong OTP! Please try again.',
                                        'require_otp' => true,
                                        'data' => [
                                            'fortnight_limit' => $totalWorkingHours,
                                            'total_hours' => $addedHours,
                                            'exceed_hours' => $addedHours - $totalWorkingHours,
                                        ],
                                    ]);
                                }
                                
                            } else {
                                return response()->json([
                                    'success' => false,
                                    'message' => 'Hours exceed limit. Manager OTP required.',
                                    'require_otp' => true,
                                    'data' => [
                                        'fortnight_limit' => $totalWorkingHours,
                                        'total_hours' => $addedHours,
                                        'exceed_hours' => $addedHours - $totalWorkingHours,
                                    ],
                                ]);
                            }
                        }
                    }
                }
            }
            # RUN THIS WHEN TYPE IS DROP
            if($request->has('type') && $request->type == 'drop'){
                # REMOVE CONFLICT FROM OTHERS BEFORE DROP THAT SHIFT
                $checkConflicts = JobRoster::where('conflicted_with', $request->roster_id)->get();
                if($checkConflicts){
                    foreach($checkConflicts as $confShift){
                        $removeShiftConf = JobRoster::find($confShift['id']);
                        $removeShiftConf->conflict = null;
                        $removeShiftConf->conflicted_with = null;
                        $removeShiftConf->conf_start = null;
                        $removeShiftConf->conf_end = null;
                        $removeShiftConf->update();
                    }
                } 
                # CALCULATE GUARD SHIFT AND WORKING HOURS
                $hours = $this->getShiftHours(dbFormateDateTime($request->start), dbFormateDateTime($request->end), $request->site_id);
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
                    $hours = $this->getShiftHours(dbFormateDateTime($request->newStart), dbFormateDateTime($request->newEnd), $request->site_ids, $shiftDropAndCopy->continuation);
                    $guardWorkingHours = calCulateGuardWeekHours(dbFormateDateTime($request->newStart), dbFormateDateTime($request->newEnd));
                    $addNewShift = new JobRoster();
                    $addNewShift->site_id = $request->site_ids;
                    $addNewShift->guard_id = (isset($shiftDropAndCopy->guard_id) && !empty($shiftDropAndCopy->guard_id)) ? $shiftDropAndCopy->guard_id : null;
                    $addNewShift->start = dbFormateDateTime($request->newStart);
                    $addNewShift->end = dbFormateDateTime($request->newEnd);
                }else{
                    $hours = $this->getShiftHours(dbFormateDateTime($request->start), dbFormateDateTime($request->end), $request->site_id, $shiftDropAndCopy->continuation);
                    $guardWorkingHours = calCulateGuardWeekHours(dbFormateDateTime($request->start), dbFormateDateTime($request->end));
                    $addNewShift = new JobRoster();
                    $addNewShift->site_id = $request->site_id;
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
                // $addNewShift->continuation = ($shiftDropAndCopy->continuation == 'on' ? true : false);
                $addNewShift->continuation = 1;
                $addNewShift->over_time = ($shiftDropAndCopy->over_time == 'on') ? true : false;
                $addNewShift->over_time_value = ($shiftDropAndCopy->over_time_value) ? $shiftDropAndCopy->over_time_value : 0;
                $addNewShift->travel_time = ($shiftDropAndCopy->travel_time == 'on') ? true : false;
                $addNewShift->travel_time_value = ($shiftDropAndCopy->travel_time_value) ? $shiftDropAndCopy->travel_time_value : 0;
                $addNewShift->reimbursement = ($shiftDropAndCopy->reimbursement == 'on') ? true : false;
                $addNewShift->reimbursement_text = $shiftDropAndCopy->reimbursement_text;
                $addNewShift->reimbursement_value = $shiftDropAndCopy->reimbursement_value;
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
                $addNewShift->roster_id = $shiftDropAndCopy->roster_id;
                $addNewShift->signin_status = 0;
                $addNewShift->on_call_job = 0;
                $addNewShift->created_by = $request->admin_id;
                $addNewShift->save();
                if ($shiftDropAndCopy->jobRosterTask->count() > 0) {
                    foreach ($shiftDropAndCopy->jobRosterTask as $key => $value) {
                        DB::table('job_roster_tasks')->insert([
                            'job_roster_id' => $addNewShift->id,
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
                $hours = $this->getShiftHours(dbFormateDateTime($request->start), dbFormateDateTime($request->end), $request->site_id);
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
                $shiftDropAndCopy->roster_id = $shiftDropAndCopy->roster_id;
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
                if($request->newStart && $request->newEnd){
                $hours = $this->getShiftHours(dbFormateDateTime($request->newStart), dbFormateDateTime($request->newEnd), $request->site_id, $shiftDropAndCopy->continuation);
                $guardWorkingHours = calCulateGuardWeekHours(dbFormateDateTime($request->newStart), dbFormateDateTime($request->newEnd));
                }else{
                $hours = $this->getShiftHours(dbFormateDateTime($request->start), dbFormateDateTime($request->end), $request->site_id, $shiftDropAndCopy->continuation);
                $guardWorkingHours = calCulateGuardWeekHours(dbFormateDateTime($request->start), dbFormateDateTime($request->end));
                }
                $shiftDropAndCopyNew = new JobRoster();
                $shiftDropAndCopyNew->site_id = $shiftDropAndCopy->site_id;
                $shiftDropAndCopyNew->guard_id = (!empty($shiftDropAndCopy->guard_id) ? $shiftDropAndCopy->guard_id : NULL);
                if($request->newStart && $request->newEnd){
                $shiftDropAndCopyNew->start = dbFormateDateTime($request->newStart);
                $shiftDropAndCopyNew->end = dbFormateDateTime($request->newEnd);
                }else{
                $shiftDropAndCopyNew->start = dbFormateDateTime($request->start);
                $shiftDropAndCopyNew->end = dbFormateDateTime($request->end);  
                }
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
                $shiftDropAndCopyNew->reimbursement = $shiftDropAndCopy->reimbursement;
                $shiftDropAndCopyNew->reimbursement_text = $shiftDropAndCopy->reimbursement_text;
                $shiftDropAndCopyNew->reimbursement_value = $shiftDropAndCopy->reimbursement_value;
                // $shiftDropAndCopyNew->operation_notes = $shiftDropAndCopy->operation_notes;
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
                $shiftDropAndCopyNew->roster_id = $shiftDropAndCopy->roster_id;
                $shiftDropAndCopyNew->signin_status = 0;
                $shiftDropAndCopyNew->unprofile_name = $shiftDropAndCopy->unprofile_name;
                $shiftDropAndCopyNew->po_wo = $shiftDropAndCopy->po_wo;
                $shiftDropAndCopyNew->on_call_job = 0;
                $shiftDropAndCopyNew->created_by = $request->admin_id;
                $shiftDropAndCopyNew->save();
                if ($shiftDropAndCopy->jobRosterTask->count() > 0) {
                    foreach ($shiftDropAndCopy->jobRosterTask as $key => $value) {
                        DB::table('job_roster_tasks')->insert([
                            'job_roster_id' => $shiftDropAndCopyNew->id,
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

# OLD
// public function shiftDropAndCopyold(Request $request)
// {
//     $shiftDropAndCopy = JobRoster::where('id', $request->roster_id)->with('jobRosterTask')->first();
    
    
//     if($shiftDropAndCopy){
//         if( $request->has('type') && $request->type == 'drop'){
//             if($shiftDropAndCopy->signin_status == 1){
//                 return response()->json(['success' => false, 'message' => 'Sign-in shift does not drop to the next date!', 'code'=> 404]);
//             }


//             $roster_id = $shiftDropAndCopy->roster_id; 

//             $hours = $this->getShiftHours(dbFormateDateTime($request->start), dbFormateDateTime($request->end), $request->site_id);
//             if(!empty($shiftDropAndCopy->guard_id)){
//                 # CHECK GUARD IS ON LEAVE OR NOT
//                 $guard_leave = checkGuardOnLeave($request->start, $request->end, $request->guard_id);
//                 if($guard_leave == 'leave'){
//                     return response()->json(['success' => false, 'message' => 'Sorry Staff On Leave!', 'code'=> 404]);
//                 }
//                 $check = checkGuardShiftTiming($request->newStart, $request->newEnd, $shiftDropAndCopy->guard_id, $shiftDropAndCopy->roster_id);
//                 $guardWorkingHours = calCulateGuardWeekHours(dbFormateDateTime($request->start), dbFormateDateTime($request->end));
//                 $guardWorkLimitation = checkGuardWorkLimitation($shiftDropAndCopy->guard_id, $guardWorkingHours);
//                 $check2 = checkGuardDocuments($shiftDropAndCopy->guard_id);
//                 if($check['status'] == 'true' || !empty($check['end']) || !empty($check['conf']) || (!empty($check2) && $check2 != 'active') || !empty($guardWorkLimitation)){
//                     $checkAdmin  = checkAdmin($request->admin_id);
//                     if($checkAdmin == 'admin'){
//                         return response()->json(['success' => false, 'message' => '<b>Sorry this Shift has conflicte, So you do not create yet!</b>', 'code'=> 404]);
//                     }
//                     if($checkAdmin == 'super-admin' && $request->has('shift_confirm') && $request->shift_confirm == 'yes'){
//                         $shiftDropAndCopy->start = dbFormateDateTime($request->start);
//                         $shiftDropAndCopy->end = dbFormateDateTime($request->end);
//                         $shiftDropAndCopy->conflict = (!empty($check['conf']) ? $check['conf'] : '');
//                         $shiftDropAndCopy->doc_conf = (!empty($check2) ? $check2 : '');
//                         $shiftDropAndCopy->work_limitaion_conf = (!empty($guardWorkLimitation['difference']) ? $guardWorkLimitation['difference'] : '');
//                         $shiftDropAndCopy->conf_start = (!empty($check['start']) ? dbFormateDateTime($check['start']) : '');
//                         $shiftDropAndCopy->conf_end = (!empty($check['end']) ? dbFormateDateTime($check['end']) : '');
//                         $shiftDropAndCopy->morning_hours = (!empty($hours['morning']) ? $hours['morning'] : 0.0);
//                         $shiftDropAndCopy->night_hours = (!empty($hours['night']) ? $hours['night'] : 0.0);
//                         $shiftDropAndCopy->saturday_morning_hours = (!empty($hours['saturday_morning']) ? $hours['saturday_morning'] : 0.0);
//                         $shiftDropAndCopy->saturday_night_hours = (!empty($hours['saturday_night']) ? $hours['saturday_night'] : 0.0);
//                         $shiftDropAndCopy->sunday_morning_hours = (!empty($hours['sunday_morning']) ? $hours['sunday_morning'] : 0.0);
//                         $shiftDropAndCopy->sunday_night_hours = (!empty($hours['sunday_night']) ? $hours['sunday_night'] : 0.0);
//                         $shiftDropAndCopy->ph_morning_hours = (!empty($hours['ph_morning']) ? $hours['ph_morning'] : 0.0);
//                         $shiftDropAndCopy->ph_night_hours = (!empty($hours['ph_night']) ? $hours['ph_night'] : 0.0);
//                         $shiftDropAndCopy->last_update = time();
//                         $shiftDropAndCopy->hours = $guardWorkingHours;
//                         $shiftDropAndCopy->publish_status = 0;
//                         $shiftDropAndCopy->roster_id = $roster_id;
//                         $shiftDropAndCopy->job_status = 'pending';
//                         $shiftDropAndCopy->save(); 
                                        
//                         jobRosterActions($request->admin_id, 'drop_shift', $shiftDropAndCopy->id, 'job_roster');

//                         $admin_name = getAdminName($request->admin_id);    
//                         $currnet_time = time();
//                         shiftCompleteActivity($shiftDropAndCopy->id, $admin_name. ' Drop this Shift', 'drop_shift', $shiftDropAndCopy->id, $currnet_time, $request->admin_id);
//                         return response()->json(['success' => true, 'message' => 'Conflicted Shift Drop Successfully!', 'code' => 200]);
//                     }
//                     if($checkAdmin == 'super-admin'){
//                         return response()->json(['success' => false, 'message' => '<b>Hi, Super Admin this shift has conflicte <br> Do you really want to create this shift !</b>', 'code'=> 404]);
//                     }
//                 }else{

//                     $check = checkGuardShiftTiming($request->start, $request->end, $shiftDropAndCopy->guard_id, $shiftDropAndCopy->roster_id);
//                     $check2 = checkGuardDocuments($shiftDropAndCopy->guard_id);

//                     $shiftDropAndCopy->start = dbFormateDateTime($request->start);
//                     $shiftDropAndCopy->end = dbFormateDateTime($request->end);
//                     $shiftDropAndCopy->conflict = (!empty($check['conf']) ? $check['conf'] : '');
//                     $shiftDropAndCopy->doc_conf = (!empty($check2) ? $check2 : '');
//                     $shiftDropAndCopy->work_limitaion_conf = (!empty($guardWorkLimitation['difference']) ? $guardWorkLimitation['difference'] : '');
//                     $shiftDropAndCopy->conf_start = (!empty($check['start']) ? dbFormateDateTime($check['start']) : '');
//                     $shiftDropAndCopy->conf_end = (!empty($check['end']) ? dbFormateDateTime($check['end']) : '');
//                     $shiftDropAndCopy->morning_hours = (!empty($hours['morning']) ? $hours['morning'] : 0.0);
//                     $shiftDropAndCopy->night_hours = (!empty($hours['night']) ? $hours['night'] : 0.0);
//                     $shiftDropAndCopy->saturday_morning_hours = (!empty($hours['saturday_morning']) ? $hours['saturday_morning'] : 0.0);
//                     $shiftDropAndCopy->saturday_night_hours = (!empty($hours['saturday_night']) ? $hours['saturday_night'] : 0.0);
//                     $shiftDropAndCopy->sunday_morning_hours = (!empty($hours['sunday_morning']) ? $hours['sunday_morning'] : 0.0);
//                     $shiftDropAndCopy->sunday_night_hours = (!empty($hours['sunday_night']) ? $hours['sunday_night'] : 0.0);
//                     $shiftDropAndCopy->ph_morning_hours = (!empty($hours['ph_morning']) ? $hours['ph_morning'] : 0.0);
//                     $shiftDropAndCopy->ph_night_hours = (!empty($hours['ph_night']) ? $hours['ph_night'] : 0.0);
//                     $shiftDropAndCopy->last_update = time();
//                     $shiftDropAndCopy->hours = $guardWorkingHours;
//                     $shiftDropAndCopy->publish_status = 0;
//                     $shiftDropAndCopy->roster_id = $roster_id;
//                     $shiftDropAndCopy->job_status = 'pending';
//                     $shiftDropAndCopy->save();
//                     jobRosterActions($request->admin_id, 'drop_shift', $shiftDropAndCopy->id,'job_roster');

//                     $admin_name = getAdminName($request->admin_id);    
//                     $currnet_time = time();
//                     shiftCompleteActivity($shiftDropAndCopy->id, $admin_name. ' Drop this Shift', 'drop_shift', $shiftDropAndCopy->id, $currnet_time, $request->admin_id);

//                     return response()->json(['success' => true, 'message' => 'Shift Drop Successfully!', 'code' => 200]);
//                 }
//             }
//             # DROP BUT UNASSIGN
//             else{
//                 $guardWorkingHours = calCulateGuardWeekHours(dbFormateDateTime($request->start), dbFormateDateTime($request->end));
//                 $shiftDropAndCopy->start = dbFormateDateTime($request->start);
//                 $shiftDropAndCopy->end = dbFormateDateTime($request->end);
//                 $shiftDropAndCopy->morning_hours = (!empty($hours['morning']) ? $hours['morning'] : 0.0);
//                 $shiftDropAndCopy->night_hours = (!empty($hours['night']) ? $hours['night'] : 0.0);
//                 $shiftDropAndCopy->saturday_morning_hours = (!empty($hours['saturday_morning']) ? $hours['saturday_morning'] : 0.0);
//                 $shiftDropAndCopy->saturday_night_hours = (!empty($hours['saturday_night']) ? $hours['saturday_night'] : 0.0);
//                 $shiftDropAndCopy->sunday_morning_hours = (!empty($hours['sunday_morning']) ? $hours['sunday_morning'] : 0.0);
//                 $shiftDropAndCopy->sunday_night_hours = (!empty($hours['sunday_night']) ? $hours['sunday_night'] : 0.0);
//                 $shiftDropAndCopy->ph_morning_hours = (!empty($hours['ph_morning']) ? $hours['ph_morning'] : 0.0);
//                 $shiftDropAndCopy->ph_night_hours = (!empty($hours['ph_night']) ? $hours['ph_night'] : 0.0);
//                 $shiftDropAndCopy->last_update = time();
//                 $shiftDropAndCopy->hours = $guardWorkingHours;
//                 $shiftDropAndCopy->roster_id = $roster_id;
//                 $shiftDropAndCopy->job_status = 'pending';
//                 $shiftDropAndCopy->save();
//                 jobRosterActions($request->admin_id, 'drop_shift', $shiftDropAndCopy->id,'job_roster');
//                 $admin_name = getAdminName($request->admin_id);    
//                     $currnet_time = time();
//                     shiftCompleteActivity($shiftDropAndCopy->id, $admin_name. ' Drop this Shift', 'drop_shift', $shiftDropAndCopy->id, $currnet_time, $request->admin_id);
//                 return response()->json(['success' => true, 'message' => 'Shift Drop Successfully!', 'code' => 200]);
//             }
//         }elseif($request->has('type') && $request->type == 'copy_shift'){
//             $roster_id = $shiftDropAndCopy->roster_id;
//                 //check hours lay!
//             $hours = $this->getShiftHours(dbFormateDateTime($request->newStart), dbFormateDateTime($request->newEnd), $request->site_id);
//             if(!empty($shiftDropAndCopy->guard_id)){

//                 $guard_leave = checkGuardOnLeave($request->start, $request->end, $request->guard_id);
//                 if($guard_leave == 'leave'){
//                     return response()->json(['success' => false, 'message' => 'Sorry Staff On Leave!', 'code'=> 404]);
//                 }

//                 $check = checkGuardShiftTiming($request->newStart, $request->newEnd, $shiftDropAndCopy->guard_id, $shiftDropAndCopy->roster_id);
//                 $guardWorkingHours = calCulateGuardWeekHours(dbFormateDateTime($request->newStart), dbFormateDateTime($request->newEnd));
//                 $guardWorkLimitation = checkGuardWorkLimitation($shiftDropAndCopy->guard_id, $guardWorkingHours);
//                 $check2 = checkGuardDocuments($shiftDropAndCopy->guard_id);
//                 if(!empty($check['start']) || !empty($check['end']) || !empty($check['conf']) || (!empty($check2) && $check2 != 'active') || !empty($guardWorkLimitation)){

//                     $checkAdmin  = checkAdmin($request->admin_id);
//                     if($checkAdmin == 'admin'){
//                         return response()->json(['success' => false, 'message' => '<b>Sorry this Shift has conflicte, So you do not create yet!</b>', 'code'=> 404]);
//                     }
//                     if($checkAdmin == 'super-admin' && $request->has('shift_confirm') && $request->shift_confirm == 'yes'){
//                         $shiftDropAndCopyNew = new JobRoster();
//                         $shiftDropAndCopyNew->site_id = $shiftDropAndCopy->site_id;
//                         $shiftDropAndCopyNew->guard_id = (!empty($shiftDropAndCopy->guard_id) ? $shiftDropAndCopy->guard_id : '');
//                         $shiftDropAndCopyNew->start = dbFormateDateTime($request->newStart);
//                         $shiftDropAndCopyNew->end = dbFormateDateTime($request->newEnd);
//                         $shiftDropAndCopyNew->shift_payable = !empty($shiftDropAndCopy->shift_payable) ? $shiftDropAndCopy->shift_payable : 'yes';
//                         $shiftDropAndCopyNew->shift_chargeable = !empty($shiftDropAndCopy->shift_chargeable) ? $shiftDropAndCopy->shift_chargeable : 'yes';
//                         $shiftDropAndCopyNew->custome_rate = $shiftDropAndCopy->custome_rate;
//                         $shiftDropAndCopyNew->payrate_level = $shiftDropAndCopy->payrate_level;
//                         $shiftDropAndCopyNew->payrate = $shiftDropAndCopy->payrate;
//                         $shiftDropAndCopyNew->chargerate_level = $shiftDropAndCopy->chargerate_level;
//                         $shiftDropAndCopyNew->chargerate = $shiftDropAndCopy->chargerate;
//                         $shiftDropAndCopyNew->un_published_shift = $shiftDropAndCopy->un_published_shift ;
//                         $shiftDropAndCopyNew->public_holidays = $shiftDropAndCopy->public_holidays;
//                         $shiftDropAndCopyNew->covid_marshal = $shiftDropAndCopy->covid_marshal;
//                         $shiftDropAndCopyNew->training = $shiftDropAndCopy->training;
//                         $shiftDropAndCopyNew->continuation = $shiftDropAndCopy->continuation;
//                         $shiftDropAndCopyNew->over_time = $shiftDropAndCopy->over_time;
//                         $shiftDropAndCopyNew->over_time_value = $shiftDropAndCopy->over_time_value;
//                         $shiftDropAndCopyNew->travel_time = $shiftDropAndCopy->travel_time;
//                         $shiftDropAndCopyNew->travel_time_value = $shiftDropAndCopy->travel_time_value;
//                         $shiftDropAndCopyNew->operation_notes = $shiftDropAndCopy->operation_notes;
//                         $shiftDropAndCopyNew->shift_create_status = 'pending';
//                         $shiftDropAndCopyNew->shift_type = $shiftDropAndCopy->shift_type ;
//                         $shiftDropAndCopyNew->conflict = (!empty($check['conf']) ? $check['conf'] : '');
//                         $shiftDropAndCopyNew->doc_conf = (!empty($check2) ? $check2 : '');
//                         $shiftDropAndCopyNew->work_limitaion_conf = (!empty($guardWorkLimitation['difference']) ? $guardWorkLimitation['difference'] : '');
//                         $shiftDropAndCopyNew->conf_start = (!empty($check['start']) ? dbFormateDateTime($check['start']) : '');
//                         $shiftDropAndCopyNew->conf_end = (!empty($check['end']) ? dbFormateDateTime($check['end']) : '');
//                         $shiftDropAndCopyNew->morning_hours = (!empty($hours['morning']) ? $hours['morning'] : 0.0);
//                         $shiftDropAndCopyNew->night_hours = (!empty($hours['night']) ? $hours['night'] : 0.0);
//                         $shiftDropAndCopyNew->saturday_morning_hours = (!empty($hours['saturday_morning']) ? $hours['saturday_morning'] : 0.0);
//                         $shiftDropAndCopyNew->saturday_night_hours = (!empty($hours['saturday_night']) ? $hours['saturday_night'] : 0.0);
//                         $shiftDropAndCopyNew->sunday_morning_hours = (!empty($hours['sunday_morning']) ? $hours['sunday_morning'] : 0.0);
//                         $shiftDropAndCopyNew->sunday_night_hours = (!empty($hours['sunday_night']) ? $hours['sunday_night'] : 0.0);
//                         $shiftDropAndCopyNew->ph_morning_hours = (!empty($hours['ph_morning']) ? $hours['ph_morning'] : 0.0);
//                         $shiftDropAndCopyNew->ph_night_hours = (!empty($hours['ph_night']) ? $hours['ph_night'] : 0.0);
//                         $shiftDropAndCopyNew->last_update = time();
//                         $shiftDropAndCopyNew->hours = $guardWorkingHours;
//                         $shiftDropAndCopyNew->publish_status = 0;
//                         $shiftDropAndCopyNew->roster_id = $roster_id;
//                         $shiftDropAndCopyNew->save();
//                         if ($shiftDropAndCopy->jobRosterTask->count() > 0) {
//                             foreach ($shiftDropAndCopy->jobRosterTask as $key => $value) {
//                                 DB::table('job_roster_tasks')->insert([
//                                     'job_roster_id' => $shiftDropAndCopyNew->id,
//                                     'task' => $value->task,
//                                     'task_start' => $value->task_start,
//                                     'task_end' => $value->task_end,
//                                     'status' => 'pending',
//                                 ]);
//                             }
//                          }
//                         jobRosterActions($request->admin_id, 'copy_shift', $shiftDropAndCopyNew->id, 'job_roster');

//                         $admin_name = getAdminName($request->admin_id);
//                         $currnet_time = time();
//                         shiftCompleteActivity($shiftDropAndCopyNew->id, $admin_name. ' Copy this Shift', 'copy_shift', $shiftDropAndCopyNew->id, $currnet_time, $request->admin_id);

//                         return response()->json(['success' => true, 'message' => 'Shift Copy Successfully!', 'code' => 200]); 
//                     }

//                     if($checkAdmin == 'super-admin'){
//                         return response()->json(['success' => false, 'message' => '<b>Hi, Super Admin this shift has conflicte <br> Do you really want to create this shift !</b>', 'code'=> 404]);
//                     }
//                 }else{
//                     $guard_leave = checkGuardOnLeave($request->start, $request->end, $request->guard_id);
//                     if($guard_leave == 'leave'){
//                         return response()->json(['success' => false, 'message' => 'Sorry Staff On Leave!', 'code'=> 404]);
//                     }
//                     $hours = $this->getShiftHours(dbFormateDateTime($request->newStart), dbFormateDateTime($request->newEnd), $request->site_id);
//                     $guardWorkingHours = calCulateGuardWeekHours(dbFormateDateTime($request->newStart), dbFormateDateTime($request->newEnd));
//                     $shiftDropAndCopyNew = new JobRoster();
//                     $shiftDropAndCopyNew->site_id = $request->site_ids;
//                     $shiftDropAndCopyNew->guard_id = (!empty($shiftDropAndCopy->guard_id) ? $shiftDropAndCopy->guard_id : '');
//                     $shiftDropAndCopyNew->start = dbFormateDateTime($request->newStart);
//                     $shiftDropAndCopyNew->end = dbFormateDateTime($request->newEnd);
//                     $shiftDropAndCopyNew->shift_payable = !empty($shiftDropAndCopy->shift_payable) ? $shiftDropAndCopy->shift_payable : 'yes';
//                     $shiftDropAndCopyNew->shift_chargeable = !empty($shiftDropAndCopy->shift_chargeable) ? $shiftDropAndCopy->shift_chargeable : 'yes';
//                     $shiftDropAndCopyNew->custome_rate = $shiftDropAndCopy->custome_rate;
//                     $shiftDropAndCopyNew->payrate_level = $shiftDropAndCopy->payrate_level;
//                     $shiftDropAndCopyNew->payrate = $shiftDropAndCopy->payrate;
//                     $shiftDropAndCopyNew->chargerate_level = $shiftDropAndCopy->chargerate_level;
//                     $shiftDropAndCopyNew->chargerate = $shiftDropAndCopy->chargerate;
//                     $shiftDropAndCopyNew->un_published_shift = $shiftDropAndCopy->un_published_shift ;
//                     $shiftDropAndCopyNew->public_holidays = $shiftDropAndCopy->public_holidays;
//                     $shiftDropAndCopyNew->covid_marshal = $shiftDropAndCopy->covid_marshal;
//                     $shiftDropAndCopyNew->training = $shiftDropAndCopy->training;
//                     $shiftDropAndCopyNew->continuation = $shiftDropAndCopy->continuation;
//                     $shiftDropAndCopyNew->over_time = $shiftDropAndCopy->over_time;
//                     $shiftDropAndCopyNew->over_time_value = $shiftDropAndCopy->over_time_value;
//                     $shiftDropAndCopyNew->travel_time = $shiftDropAndCopy->travel_time;
//                     $shiftDropAndCopyNew->travel_time_value = $shiftDropAndCopy->travel_time_value;
//                     $shiftDropAndCopyNew->operation_notes = $shiftDropAndCopy->operation_notes;
//                     $shiftDropAndCopyNew->shift_create_status = 'pending';
//                     $shiftDropAndCopyNew->shift_type = $shiftDropAndCopy->shift_type ;
//                     $shiftDropAndCopyNew->conflict = (!empty($check['conf']) ? $check['conf'] : '');
//                     $shiftDropAndCopyNew->doc_conf = (!empty($check2) ? $check2 : '');
//                     $shiftDropAndCopyNew->work_limitaion_conf = (!empty($guardWorkLimitation['difference']) ? $guardWorkLimitation['difference'] : '');
//                     $shiftDropAndCopyNew->conf_start = (!empty($check['start']) ? dbFormateDateTime($check['start']) : '');
//                     $shiftDropAndCopyNew->conf_end = (!empty($check['end']) ? dbFormateDateTime($check['end']) : '');
//                     $shiftDropAndCopyNew->morning_hours = (!empty($hours['morning']) ? $hours['morning'] : 0.0);
//                     $shiftDropAndCopyNew->night_hours = (!empty($hours['night']) ? $hours['night'] : 0.0);
//                     $shiftDropAndCopyNew->saturday_morning_hours = (!empty($hours['saturday_morning']) ? $hours['saturday_morning'] : 0.0);
//                     $shiftDropAndCopyNew->saturday_night_hours = (!empty($hours['saturday_night']) ? $hours['saturday_night'] : 0.0);
//                     $shiftDropAndCopyNew->sunday_morning_hours = (!empty($hours['sunday_morning']) ? $hours['sunday_morning'] : 0.0);
//                     $shiftDropAndCopyNew->sunday_night_hours = (!empty($hours['sunday_night']) ? $hours['sunday_night'] : 0.0);
//                     $shiftDropAndCopyNew->ph_morning_hours = (!empty($hours['ph_morning']) ? $hours['ph_morning'] : 0.0);
//                     $shiftDropAndCopyNew->ph_night_hours = (!empty($hours['ph_night']) ? $hours['ph_night'] : 0.0);
//                     $shiftDropAndCopyNew->last_update = time();
//                     $shiftDropAndCopyNew->hours = $guardWorkingHours;
//                     $shiftDropAndCopyNew->publish_status = 0;
//                     $shiftDropAndCopyNew->roster_id = $roster_id;
//                     $shiftDropAndCopyNew->save();
//                     if ($shiftDropAndCopy->jobRosterTask->count() > 0) {
//                         foreach ($shiftDropAndCopy->jobRosterTask as $key => $value) {
//                             DB::table('job_roster_tasks')->insert([
//                                 'job_roster_id' => $shiftDropAndCopyNew->id,
//                                 'task' => $value->task,
//                                 'task_start' => $value->task_start,
//                                 'task_end' => $value->task_end,
//                                 'status' => 'pending',
//                             ]);
//                         }
//                      }
//                     jobRosterActions($request->admin_id, 'copy_shift', $shiftDropAndCopyNew->id,'job_roster');

//                     $admin_name = getAdminName($request->admin_id);
//                     $currnet_time = time();
//                     shiftCompleteActivity($shiftDropAndCopyNew->id, $admin_name. ' Copy this Shift', 'copy_shift', $shiftDropAndCopyNew->id, $currnet_time, $request->admin_id);

//                     return response()->json(['success' => true, 'message' => 'Shift Copy Successfully!', 'code' => 200]);
//                 }
//             }else{
//                 $guardWorkingHours = calCulateGuardWeekHours(dbFormateDateTime($request->newStart), dbFormateDateTime($request->newEnd));

//                 $shiftDropAndCopyNew = new JobRoster();
//                 $shiftDropAndCopyNew->site_id = $request->site_ids;
//                 $shiftDropAndCopyNew->guard_id = (!empty($shiftDropAndCopy->guard_id) ? $shiftDropAndCopy->guard_id : '');
//                 $shiftDropAndCopyNew->start = dbFormateDateTime($request->newStart);
//                 $shiftDropAndCopyNew->end = dbFormateDateTime($request->newEnd);
//                 $shiftDropAndCopyNew->last_update = time();
//                 $shiftDropAndCopyNew->shift_payable = !empty($shiftDropAndCopy->shift_payable) ? $shiftDropAndCopy->shift_payable : 'yes';
//                 $shiftDropAndCopyNew->shift_chargeable = !empty($shiftDropAndCopy->shift_chargeable) ? $shiftDropAndCopy->shift_chargeable : 'yes';
//                 $shiftDropAndCopyNew->custome_rate = $shiftDropAndCopy->custome_rate;
//                 $shiftDropAndCopyNew->payrate_level = $shiftDropAndCopy->payrate_level;
//                 $shiftDropAndCopyNew->payrate = $shiftDropAndCopy->payrate;
//                 $shiftDropAndCopyNew->chargerate_level = $shiftDropAndCopy->chargerate_level;
//                 $shiftDropAndCopyNew->chargerate = $shiftDropAndCopy->chargerate;
//                 $shiftDropAndCopyNew->un_published_shift = $shiftDropAndCopy->un_published_shift ;
//                 $shiftDropAndCopyNew->public_holidays = $shiftDropAndCopy->public_holidays;
//                 $shiftDropAndCopyNew->covid_marshal = $shiftDropAndCopy->covid_marshal;
//                 $shiftDropAndCopyNew->training = $shiftDropAndCopy->training;
//                 $shiftDropAndCopyNew->continuation = $shiftDropAndCopy->continuation;
//                 $shiftDropAndCopyNew->over_time = $shiftDropAndCopy->over_time;
//                 $shiftDropAndCopyNew->over_time_value = $shiftDropAndCopy->over_time_value;
//                 $shiftDropAndCopyNew->travel_time = $shiftDropAndCopy->travel_time;
//                 $shiftDropAndCopyNew->travel_time_value = $shiftDropAndCopy->travel_time_value;
//                 $shiftDropAndCopyNew->operation_notes = $shiftDropAndCopy->operation_notes;
//                 $shiftDropAndCopyNew->shift_create_status = 'pending';
//                 $shiftDropAndCopyNew->morning_hours = (!empty($hours['morning']) ? $hours['morning'] : 0.0);
//                 $shiftDropAndCopyNew->night_hours = (!empty($hours['night']) ? $hours['night'] : 0.0);
//                 $shiftDropAndCopyNew->saturday_morning_hours = (!empty($hours['saturday_morning']) ? $hours['saturday_morning'] : 0.0);
//                 $shiftDropAndCopyNew->saturday_night_hours = (!empty($hours['saturday_night']) ? $hours['saturday_night'] : 0.0);
//                 $shiftDropAndCopyNew->sunday_morning_hours = (!empty($hours['sunday_morning']) ? $hours['sunday_morning'] : 0.0);
//                 $shiftDropAndCopyNew->sunday_night_hours = (!empty($hours['sunday_night']) ? $hours['sunday_night'] : 0.0);
//                 $shiftDropAndCopyNew->ph_morning_hours = (!empty($hours['ph_morning']) ? $hours['ph_morning'] : 0.0);
//                 $shiftDropAndCopyNew->ph_night_hours = (!empty($hours['ph_night']) ? $hours['ph_night'] : 0.0);
//                 $shiftDropAndCopyNew->hours = $guardWorkingHours;
//                 $shiftDropAndCopyNew->publish_status = 0;
//                 $shiftDropAndCopyNew->roster_id = $roster_id;
//                 $shiftDropAndCopyNew->save();
//                 if ($shiftDropAndCopy->jobRosterTask->count() > 0) {
//                     foreach ($shiftDropAndCopy->jobRosterTask as $key => $value) {
//                         DB::table('job_roster_tasks')->insert([
//                             'job_roster_id' => $shiftDropAndCopyNew->id,
//                             'task' => $value->task,
//                             'task_start' => $value->task_start,
//                             'task_end' => $value->task_end,
//                             'status' => 'pending',
//                         ]);
//                     }
//                  }
//                 jobRosterActions($request->admin_id, 'copy_shift', $shiftDropAndCopyNew->id, 'job_roster');

//                 $admin_name = getAdminName($request->admin_id);
//                 $currnet_time = time();
//                 shiftCompleteActivity($shiftDropAndCopyNew->id, $admin_name. ' Copy this Shift', 'copy_shift', $shiftDropAndCopyNew->id, $currnet_time, $request->admin_id);
//                 return response()->json(['success' => true, 'message' => 'Shift Copy Successfully!', 'code' => 200]);
//             }
//         }else{
//             $roster_id = $shiftDropAndCopy->roster_id;
//             if(!empty($shiftDropAndCopy->guard_id)){

//                 $guard_leave = checkGuardOnLeave($request->start, $request->end, $request->guard_id);
//                 if($guard_leave == 'leave'){
//                     return response()->json(['success' => false, 'message' => 'Sorry Staff On Leave!', 'code'=> 404]);
//                 }
//                 $hours = $this->getShiftHours(dbFormateDateTime($request->start), dbFormateDateTime($request->end), $request->site_id);
//                 $check = checkGuardShiftTiming($request->start, $request->end, $shiftDropAndCopy->guard_id, $shiftDropAndCopy->roster_id);
//                 $guardWorkingHours = calCulateGuardWeekHours(dbFormateDateTime($shiftDropAndCopy->start), dbFormateDateTime($shiftDropAndCopy->end));
//                 $guardWorkLimitation = checkGuardWorkLimitation($shiftDropAndCopy->guard_id, $guardWorkingHours);
//                 $check2 = checkGuardDocuments($shiftDropAndCopy->guard_id);
//                 if(!empty($check['start']) || !empty($check['end']) || !empty($check['conf']) || (!empty($check2) && $check2 != 'active') || !empty($guardWorkLimitation)){
//                     $checkAdmin  = checkAdmin($request->admin_id);
//                     if($checkAdmin == 'admin'){
//                         return response()->json(['success' => false, 'message' => '<b>Sorry this Shift has conflicte, So you do not create yet!</b>', 'code'=> 404]);
//                     }
//                     if($checkAdmin == 'super-admin' && $request->has('shift_confirm') && $request->shift_confirm == 'yes'){
//                         $shiftDropAndCopyNew = new JobRoster();
//                         $shiftDropAndCopyNew->site_id = $shiftDropAndCopy->site_id;
//                         $shiftDropAndCopyNew->guard_id = (!empty($shiftDropAndCopy->guard_id) ? $shiftDropAndCopy->guard_id : '');
//                         $shiftDropAndCopyNew->start = dbFormateDateTime($request->start);
//                         $shiftDropAndCopyNew->end = dbFormateDateTime($request->end);
//                         $shiftDropAndCopyNew->last_update = time();
//                         $shiftDropAndCopyNew->shift_payable = !empty($shiftDropAndCopy->shift_payable) ? $shiftDropAndCopy->shift_payable : 'yes';
//                         $shiftDropAndCopyNew->shift_chargeable = !empty($shiftDropAndCopy->shift_chargeable) ? $shiftDropAndCopy->shift_chargeable : 'yes';
//                         $shiftDropAndCopyNew->custome_rate = $shiftDropAndCopy->custome_rate;
//                         $shiftDropAndCopyNew->payrate_level = $shiftDropAndCopy->payrate_level;
//                         $shiftDropAndCopyNew->payrate = $shiftDropAndCopy->payrate;
//                         $shiftDropAndCopyNew->chargerate_level = $shiftDropAndCopy->chargerate_level;
//                         $shiftDropAndCopyNew->chargerate = $shiftDropAndCopy->chargerate;
//                         $shiftDropAndCopyNew->un_published_shift = $shiftDropAndCopy->un_published_shift ;
//                         $shiftDropAndCopyNew->public_holidays = $shiftDropAndCopy->public_holidays;
//                         $shiftDropAndCopyNew->covid_marshal = $shiftDropAndCopy->covid_marshal;
//                         $shiftDropAndCopyNew->training = $shiftDropAndCopy->training;
//                         $shiftDropAndCopyNew->continuation = $shiftDropAndCopy->continuation;
//                         $shiftDropAndCopyNew->over_time = $shiftDropAndCopy->over_time;
//                         $shiftDropAndCopyNew->over_time_value = $shiftDropAndCopy->over_time_value;
//                         $shiftDropAndCopyNew->travel_time = $shiftDropAndCopy->travel_time;
//                         $shiftDropAndCopyNew->travel_time_value = $shiftDropAndCopy->travel_time_value;
//                         $shiftDropAndCopyNew->operation_notes = $shiftDropAndCopy->operation_notes;
//                         $shiftDropAndCopyNew->shift_create_status = 'pending';
//                         $shiftDropAndCopyNew->shift_type = $shiftDropAndCopy->shift_type;
//                         $shiftDropAndCopyNew->conflict = (!empty($check['conf']) ? $check['conf'] : '');
//                         $shiftDropAndCopyNew->doc_conf = (!empty($check2) ? $check2 : '');
//                         $shiftDropAndCopyNew->work_limitaion_conf = (!empty($guardWorkLimitation['difference']) ? $guardWorkLimitation['difference'] : '');
//                         $shiftDropAndCopyNew->conf_start = (!empty($check['start']) ? dbFormateDateTime($check['start']) : '');
//                         $shiftDropAndCopyNew->conf_end = (!empty($check['end']) ? dbFormateDateTime($check['end']) : '');
//                         $shiftDropAndCopyNew->morning_hours = (!empty($hours['morning']) ? $hours['morning'] : 0.0);
//                         $shiftDropAndCopyNew->night_hours = (!empty($hours['night']) ? $hours['night'] : 0.0);
//                         $shiftDropAndCopyNew->saturday_morning_hours = (!empty($hours['saturday_morning']) ? $hours['saturday_morning'] : 0.0);
//                         $shiftDropAndCopyNew->saturday_night_hours = (!empty($hours['saturday_night']) ? $hours['saturday_night'] : 0.0);
//                         $shiftDropAndCopyNew->sunday_morning_hours = (!empty($hours['sunday_morning']) ? $hours['sunday_morning'] : 0.0);
//                         $shiftDropAndCopyNew->sunday_night_hours = (!empty($hours['sunday_night']) ? $hours['sunday_night'] : 0.0);
//                         $shiftDropAndCopyNew->ph_morning_hours = (!empty($hours['ph_morning']) ? $hours['ph_morning'] : 0.0);
//                         $shiftDropAndCopyNew->ph_night_hours = (!empty($hours['ph_night']) ? $hours['ph_night'] : 0.0);
//                         $shiftDropAndCopyNew->hours = $guardWorkingHours;
//                         $shiftDropAndCopyNew->publish_status = 0;
//                         $shiftDropAndCopyNew->roster_id = $roster_id;
//                         $shiftDropAndCopyNew->save();
//                         if ($shiftDropAndCopy->jobRosterTask->count() > 0) {
//                             foreach ($shiftDropAndCopy->jobRosterTask as $key => $value) {
//                                 DB::table('job_roster_tasks')->insert([
//                                     'job_roster_id' => $shiftDropAndCopyNew->id,
//                                     'task' => $value->task,
//                                     'task_start' => $value->task_start,
//                                     'task_end' => $value->task_end,
//                                     'status' => 'pending',
//                                 ]);
//                             }
//                          }
//                         jobRosterActions($request->admin_id, 'copy_shift', $shiftDropAndCopyNew->id,'job_roster');

//                         $admin_name = getAdminName($request->admin_id);
//                         $currnet_time = time();
//                         shiftCompleteActivity($shiftDropAndCopyNew->id, $admin_name. ' Copy this Shift', 'copy_shift', $shiftDropAndCopyNew->id, $currnet_time, $request->admin_id);

//                         return response()->json(['success' => true, 'message' => 'Shift Copy Successfully!', 'code' => 200]);
//                     }
//                     if($checkAdmin == 'super-admin'){
//                         return response()->json(['success' => false, 'message' => '<b>Hi, Super Admin this shift has conflicte <br> Do you really want to create this shift !</b>', 'code'=> 404]);
//                     }
//                 }else{

//                     $guard_leave = checkGuardOnLeave($request->start, $request->end, $request->guard_id);
//                     if($guard_leave == 'leave'){
//                         return response()->json(['success' => false, 'message' => 'Sorry Staff On Leave!', 'code'=> 404]);
//                     }

//                     $hours = $this->getShiftHours(dbFormateDateTime($request->start), dbFormateDateTime($request->end), $request->site_id);
                    
//                     $shiftDropAndCopyNew = new JobRoster();
//                     $shiftDropAndCopyNew->site_id = $shiftDropAndCopy->site_id;
//                     $shiftDropAndCopyNew->guard_id = (!empty($shiftDropAndCopy->guard_id) ? $shiftDropAndCopy->guard_id : '');
//                     $shiftDropAndCopyNew->start = dbFormateDateTime($request->start);
//                     $shiftDropAndCopyNew->end = dbFormateDateTime($request->end);
//                     $shiftDropAndCopyNew->last_update = time();
//                     $shiftDropAndCopyNew->shift_payable = !empty($shiftDropAndCopy->shift_payable) ? $shiftDropAndCopy->shift_payable : 'yes';
//                     $shiftDropAndCopyNew->shift_chargeable = !empty($shiftDropAndCopy->shift_chargeable) ? $shiftDropAndCopy->shift_chargeable : 'yes';
//                     $shiftDropAndCopyNew->custome_rate = $shiftDropAndCopy->custome_rate;
//                     $shiftDropAndCopyNew->payrate_level = $shiftDropAndCopy->payrate_level;
//                     $shiftDropAndCopyNew->payrate = $shiftDropAndCopy->payrate;
//                     $shiftDropAndCopyNew->chargerate_level = $shiftDropAndCopy->chargerate_level;
//                     $shiftDropAndCopyNew->chargerate = $shiftDropAndCopy->chargerate;
//                     $shiftDropAndCopyNew->un_published_shift = $shiftDropAndCopy->un_published_shift ;
//                     $shiftDropAndCopyNew->public_holidays = $shiftDropAndCopy->public_holidays;
//                     $shiftDropAndCopyNew->covid_marshal = $shiftDropAndCopy->covid_marshal;
//                     $shiftDropAndCopyNew->training = $shiftDropAndCopy->training;
//                     $shiftDropAndCopyNew->continuation = $shiftDropAndCopy->continuation;
//                     $shiftDropAndCopyNew->over_time = $shiftDropAndCopy->over_time;
//                     $shiftDropAndCopyNew->over_time_value = $shiftDropAndCopy->over_time_value;
//                     $shiftDropAndCopyNew->travel_time = $shiftDropAndCopy->travel_time;
//                     $shiftDropAndCopyNew->travel_time_value = $shiftDropAndCopy->travel_time_value;
//                     $shiftDropAndCopyNew->operation_notes = $shiftDropAndCopy->operation_notes;
//                     $shiftDropAndCopyNew->shift_create_status = 'pending';
//                     $shiftDropAndCopyNew->shift_type = $shiftDropAndCopy->shift_type ;
//                     $shiftDropAndCopyNew->conflict = (!empty($check['conf']) ? $check['conf'] : '');
//                     $shiftDropAndCopyNew->doc_conf = (!empty($check2) ? $check2 : '');
//                     $shiftDropAndCopyNew->work_limitaion_conf = (!empty($guardWorkLimitation['difference']) ? $guardWorkLimitation['difference'] : '');
//                     $shiftDropAndCopyNew->conf_start = (!empty($check['start']) ? dbFormateDateTime($check['start']) : '');
//                     $shiftDropAndCopyNew->conf_end = (!empty($check['end']) ? dbFormateDateTime($check['end']) : '');
//                     $shiftDropAndCopyNew->morning_hours = (!empty($hours['morning']) ? $hours['morning'] : 0.0);
//                     $shiftDropAndCopyNew->night_hours = (!empty($hours['night']) ? $hours['night'] : 0.0);
//                     $shiftDropAndCopyNew->saturday_morning_hours = (!empty($hours['saturday_morning']) ? $hours['saturday_morning'] : 0.0);
//                     $shiftDropAndCopyNew->saturday_night_hours = (!empty($hours['saturday_night']) ? $hours['saturday_night'] : 0.0);
//                     $shiftDropAndCopyNew->sunday_morning_hours = (!empty($hours['sunday_morning']) ? $hours['sunday_morning'] : 0.0);
//                     $shiftDropAndCopyNew->sunday_night_hours = (!empty($hours['sunday_night']) ? $hours['sunday_night'] : 0.0);
//                     $shiftDropAndCopyNew->ph_morning_hours = (!empty($hours['ph_morning']) ? $hours['ph_morning'] : 0.0);
//                     $shiftDropAndCopyNew->ph_night_hours = (!empty($hours['ph_night']) ? $hours['ph_night'] : 0.0);
//                     $shiftDropAndCopyNew->hours = $guardWorkingHours;
//                     $shiftDropAndCopyNew->publish_status = 0;
//                     $shiftDropAndCopyNew->roster_id = $roster_id;
//                     $shiftDropAndCopyNew->save();

//                     if ($shiftDropAndCopy->jobRosterTask->count() > 0) {
//                         foreach ($shiftDropAndCopy->jobRosterTask as $key => $value) {
//                             DB::table('job_roster_tasks')->insert([
//                                 'job_roster_id' => $shiftDropAndCopyNew->id,
//                                 'task' => $value->task,
//                                 'task_start' => $value->task_start,
//                                 'task_end' => $value->task_end,
//                                 'status' => 'pending',
//                             ]);
//                         }
//                      }
                    
//                     jobRosterActions($request->admin_id, 'copy_shift', $shiftDropAndCopyNew->id, 'job_roster');

//                     $admin_name = getAdminName($request->admin_id);
//                     $currnet_time = time();
//                     shiftCompleteActivity($shiftDropAndCopyNew->id, $admin_name. ' Copy this Shift', 'copy_shift', $shiftDropAndCopyNew->id, $currnet_time, $request->admin_id);

//                     return response()->json(['success' => true, 'message' => 'Shift Copy Successfully!', 'code' => 200]);
//                 }
                
//             }else{
//                 $guardWorkingHours = calCulateGuardWeekHours(dbFormateDateTime($shiftDropAndCopy->start), dbFormateDateTime($shiftDropAndCopy->end));
//                 $hours = $this->getShiftHours(dbFormateDateTime($request->start), dbFormateDateTime($request->end), $request->site_id);
//                 $shiftDropAndCopyNew = new JobRoster();
//                 $shiftDropAndCopyNew->site_id = $shiftDropAndCopy->site_id;
//                 $shiftDropAndCopyNew->guard_id = (!empty($shiftDropAndCopy->guard_id) ? $shiftDropAndCopy->guard_id : '');
//                 $shiftDropAndCopyNew->start = dbFormateDateTime($request->start);
//                 $shiftDropAndCopyNew->end = dbFormateDateTime($request->end);
//                 $shiftDropAndCopyNew->last_update = time();
//                 $shiftDropAndCopyNew->shift_payable = !empty($shiftDropAndCopy->shift_payable) ? $shiftDropAndCopy->shift_payable : 'yes';
//                 $shiftDropAndCopyNew->shift_chargeable = !empty($shiftDropAndCopy->shift_chargeable) ? $shiftDropAndCopy->shift_chargeable : 'yes';
//                 $shiftDropAndCopyNew->custome_rate = $shiftDropAndCopy->custome_rate;
//                 $shiftDropAndCopyNew->payrate_level = $shiftDropAndCopy->payrate_level;
//                 $shiftDropAndCopyNew->payrate = $shiftDropAndCopy->payrate;
//                 $shiftDropAndCopyNew->chargerate_level = $shiftDropAndCopy->chargerate_level;
//                 $shiftDropAndCopyNew->chargerate = $shiftDropAndCopy->chargerate;
//                 $shiftDropAndCopyNew->un_published_shift = $shiftDropAndCopy->un_published_shift ;
//                 $shiftDropAndCopyNew->public_holidays = $shiftDropAndCopy->public_holidays;
//                 $shiftDropAndCopyNew->covid_marshal = $shiftDropAndCopy->covid_marshal;
//                 $shiftDropAndCopyNew->training = $shiftDropAndCopy->training;
//                 $shiftDropAndCopyNew->continuation = $shiftDropAndCopy->continuation;
//                 $shiftDropAndCopyNew->over_time = $shiftDropAndCopy->over_time;
//                 $shiftDropAndCopyNew->over_time_value = $shiftDropAndCopy->over_time_value;
//                 $shiftDropAndCopyNew->travel_time = $shiftDropAndCopy->travel_time;
//                 $shiftDropAndCopyNew->travel_time_value = $shiftDropAndCopy->travel_time_value;
//                 $shiftDropAndCopyNew->operation_notes = $shiftDropAndCopy->operation_notes;
//                 $shiftDropAndCopyNew->shift_create_status = 'pending';
//                 $shiftDropAndCopyNew->shift_type = $shiftDropAndCopy->shift_type ;
//                 $shiftDropAndCopyNew->conflict = $shiftDropAndCopy->conflict;
//                 $shiftDropAndCopyNew->doc_conf = $shiftDropAndCopy->doc_conf;
//                 $shiftDropAndCopyNew->work_limitaion_conf = $shiftDropAndCopy->work_limitaion_conf;
//                 $shiftDropAndCopyNew->conf_start = $shiftDropAndCopy->conf_start;
//                 $shiftDropAndCopyNew->conf_end = $shiftDropAndCopy->conf_end;
//                 $shiftDropAndCopyNew->morning_hours = (!empty($hours['morning']) ? $hours['morning'] : 0.0);
//                 $shiftDropAndCopyNew->night_hours = (!empty($hours['night']) ? $hours['night'] : 0.0);
//                 $shiftDropAndCopyNew->saturday_morning_hours = (!empty($hours['saturday_morning']) ? $hours['saturday_morning'] : 0.0);
//                 $shiftDropAndCopyNew->saturday_night_hours = (!empty($hours['saturday_night']) ? $hours['saturday_night'] : 0.0);
//                 $shiftDropAndCopyNew->sunday_morning_hours = (!empty($hours['sunday_morning']) ? $hours['sunday_morning'] : 0.0);
//                 $shiftDropAndCopyNew->sunday_night_hours = (!empty($hours['sunday_night']) ? $hours['sunday_night'] : 0.0);
//                 $shiftDropAndCopyNew->ph_morning_hours = (!empty($hours['ph_morning']) ? $hours['ph_morning'] : 0.0);
//                 $shiftDropAndCopyNew->ph_night_hours = (!empty($hours['ph_night']) ? $hours['ph_night'] : 0.0);
//                 $shiftDropAndCopyNew->hours = $guardWorkingHours;
//                 $shiftDropAndCopyNew->roster_id = $roster_id;
//                 $shiftDropAndCopyNew->save();

//                 if ($shiftDropAndCopy->jobRosterTask->count() > 0) {
//                 foreach ($shiftDropAndCopy->jobRosterTask as $key => $value) {
//                     DB::table('job_roster_tasks')->insert([
//                         'job_roster_id' => $shiftDropAndCopyNew->id,
//                         'task' => $value->task,
//                         'task_start' => $value->task_start,
//                         'task_end' => $value->task_end,
//                         'status' => 'pending',
//                     ]);
//                 }
//              }
//                 jobRosterActions($request->admin_id, 'copy_shift', $shiftDropAndCopyNew->id, 'job_roster');

//                 $admin_name = getAdminName($request->admin_id);
//                 $currnet_time = time();
//                 shiftCompleteActivity($shiftDropAndCopyNew->id, $admin_name. ' Copy this Shift', 'copy_shift', $shiftDropAndCopyNew->id, $currnet_time, $request->admin_id);

//                 return response()->json(['success' => true, 'message' => 'Shift Copy Successfully!', 'code' => 200]);
//             }
//         }
//     }else{
//         return response()->json(['success' => false, 'message' => 'Shift Not Found!', 'code' => 404]);
//     }
// }



public function fetchTemplateShift(Request $request)
{
    $fetchTemplateShift = JobRoster::where('shift_type', 'template')->get();
    $fts = AllTemplateShifts::collection($fetchTemplateShift);
    return response()->json(['success' => true, 'data' => $fts, 'code' => 200]);
}



public function fetchCustomerUpdatedSites(Request $request)
{
    $data_arry = [];
    $total_count = 0;
    $deleted_roster = array();
    if ($request->has('shift_ids') && !empty($request->shift_ids)) {
        $deleted = JobRosterAction::whereIn('roster_id', $request->shift_ids)->where('action_type', 'delete_shift')->select('roster_id')->get();
        foreach ($deleted as $key => $d) {
            $deleted_roster[] = $d->roster_id;
        }
    }
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

    $last_update = $request->last_update;
    $last_update = substr($last_update, 0, -3);
    $last_update = date('m/d/Y H:i:s', $last_update);
    $last_update = strtotime($last_update);
    
    $query ='';
    if($request->type == 'location'){

        $site_type = $request->site_type;

        $query = Site::with(['jobRoster' => function ($que) use ($start, $end, $site_type, $last_update){
            if ($site_type == 'active') {
                //$que->whereBetween('start', [$start, $end]);
                $que->where('start', '>=', $start)
                ->where('start', '<=', $end);
            }elseif($site_type == 'inactive')
            {
                //$que->whereBetween('start', [$start, $end]); 
                $que->where('start', '>=', $start)
                ->where('start', '<=', $end);
            }else{
                //$que->whereBetween('start', [$start, $end]);
                $que->where('start', '>=', $start)
                ->where('start', '<=', $end);
            }
            $que->where('last_update','>=', $last_update)
            // ->where('update_status', 1)
            ->with('jobRosterTask');
        }]);

        if ($request->has('customer_id') && !empty($request->customer_id)) {
            $query->whereIn('customer_id', $request->customer_id);
        }

        if ($request->has('state')) {
            $query->where('sites.state', $request->state);
        }

        if ($request->has('site_id')) {
            $query->where('sites.id', $request->site_id);
        }


        if($site_type == 'active')
        {
            $query->join('job_rosters', 'job_rosters.site_id', '=', 'sites.id');
        }elseif($site_type == 'all'){
            $query->join('job_rosters', 'job_rosters.site_id', '=', 'sites.id', 'left');
        }else{
            $query->join('job_rosters', 'job_rosters.site_id', '=', 'sites.id', 'left');
        }
        

        $query->select('sites.id', 'sites.site_name', 'sites.site_description', 'sites.customer_id')
        ->groupBy('sites.id')
        ->groupBy('sites.site_description')
        ->groupBy('sites.site_name')
        ->groupBy('sites.customer_id');

        $sites = $query->get();
        
        if(empty($sites)){
            return response()->json(['success' => false, 'data' => null, 'code' => 404, 'deleted_roster' => $deleted_roster]); 
        }
        if(true)
        {
            $inactive_sites = [];
            foreach ($sites as $key => $s) {
                if (count($s->jobRoster) > 0) {
                    $inactive_sites[] = $s;
                }
            }
            $sts = FetchCustomerSitesResource::collection($inactive_sites);
        }else{
            $sts = FetchCustomerSitesResource::collection($sites);
        }
        $queryCount = JobRoster::where('start', '>=', $start)->where('start', '<=', $end)->where('guard_id', '!=',  '')->where('guard_id', '!=',  NULL)->where('guard_id', '!=',  'NULL')->where('publish_status', 0)->count();
        $dateRange = getDatesFromRange($start,$end);
        if($dateRange){
            foreach ($dateRange as $key => $value) {
                $record = JobRoster::where('start', $value)->where('shift_type', 'not like', 'template')->get()->sum('hours');
                $data_arry[dateFormat($value)]=$record;
                $total_count = $total_count + $record;
            }
        }
        //$total_count = number_format( $total_count, 2, '.', '' );
        return response()->json(['success' => true, 'data' => $sts, 'unpublish_shift_count' => $queryCount, 'total_count' => $total_count, 'days_hours' => $data_arry,  'code' => 200, 'deleted_roster' => $deleted_roster]);


        // $site_type = $request->site_type;
        // $query = Site::with(['jobRoster' => function ($que) use ($start, $end, $site_type, $last_update){
        //     if ($site_type == 'active') {
        //         $que->whereBetween('start', [$start, $end]);
        //     }elseif($site_type == 'inactive')
        //     {
        //         $que->whereNotBetween('start', [$start, $end]);   
        //     }else{
        //         $que->whereBetween('start', [$start, $end]);
        //     }
        //     $que->where('last_update','>=', $last_update)
        //     // ->where('update_status', 1)
        //     ->with('jobRosterTask');
        // }]);
        // if ($request->has('customer_id') && !empty($request->customer_id)) {
        //     $query->whereIn('customer_id', $request->customer_id);
        // }
        // if ($request->has('state')) {
        //     $query->where('sites.state', $request->state);
        // }
        // if ($request->has('site_id')) {
        //     $query->where('sites.id', $request->site_id);
        // }
        // $query->join('job_rosters', 'job_rosters.site_id', '=', 'sites.id', 'left')
        // ->select('sites.id', 'sites.site_name', 'sites.site_description', 'sites.customer_id')
        // ->groupBy('sites.id')
        // ->groupBy('sites.site_description')
        // ->groupBy('sites.site_name')
        // ->groupBy('sites.customer_id');
        // $sites = $query->get();
        // if(empty($sites)){
        //     return response()->json(['success' => false, 'data' => null, 'code' => 404]); 
        // }

        // $sts = FetchCustomerSitesResource::collection($sites);
        // return response()->json(['success' => true, 'data' => $sts, 'code' => 200]);
    }
    // Guard type
    else{

        $guard_type = $request->guard_type;
        $query = Guard::with(['guardJobRoster' => function($que) use ($start, $end, $guard_type, $last_update){
            if ($guard_type == 'active') {
                //$que->whereBetween('start', [$start, $end]);
                $que->where('start', '>=', $start)
                ->where('start', '<=', $end);
            }elseif($guard_type == 'inactive')
            {
                //$que->whereBetween('start', [$start, $end]);
                $que->where('start', '>=', $start)
                ->where('start', '<=', $end);
            }else{
                //$que->whereBetween('start', [$start, $end]);
                $que->where('start', '>=', $start)
                ->where('start', '<=', $end);
            }
            $que->where('last_update','>=', $last_update);
            // ->with('jobRosterTask');
            // $que->where('update_status', 0);
        }]);
        if ($request->has('state')) {
            $query->where('guards.state', $request->state);
        }
        if ($request->has('guard_id')) {
            $query->where('guards.id', $request->guard_id);
        }
        if($guard_type == 'active')
        {
            $query->join('job_rosters', 'job_rosters.guard_id', '=', 'guards.id');
        }elseif($guard_type == 'all'){
            $query->join('job_rosters', 'job_rosters.guard_id', '=', 'guards.id', 'left');
        }else{
            $query->join('job_rosters', 'job_rosters.guard_id', '=', 'guards.id', 'left');
        }
        // $query->join('job_rosters', 'job_rosters.guard_id', '=', 'guards.id', 'left');
        $query->select('guards.id', 'guards.first_name', 'guards.middle_name','guards.last_name', 'guards.email', 'guards.profile_image')
        ->groupBy('guards.id')
        ->groupBy('guards.first_name')
        ->groupBy('guards.middle_name')
        ->groupBy('guards.last_name')
        ->groupBy('guards.email')
        ->groupBy('guards.profile_image');
        
        $sites = $query->get();
        if(empty($sites)){
            return response()->json(['success' => false, 'data' => null, 'code' => 404, 'deleted_roster' => $deleted_roster]); 
        }
        if($guard_type == 'inactive')
        {
            $inactive_sites = [];
            foreach ($sites as $key => $s) {
                if (count($s->guardJobRoster) == 0) {
                    $inactive_sites[] = $s;
                }
            }
            $sts = FetchCustomerSitesWithGuardResource::collection($inactive_sites);
        }else{
            $sts = FetchCustomerSitesWithGuardResource::collection($sites);
        }

        $queryCount = JobRoster::where('start', '>=', $start)->where('start', '<=', $end)->where('guard_id', '!=',  '')->where('guard_id', '!=',  NULL)->where('guard_id', '!=',  'NULL')->where('publish_status', 0)->count();
        $dateRange = getDatesFromRange($start,$end);
        if($dateRange){
            foreach ($dateRange as $key => $value) {
                $record = JobRoster::where('start', $value)->where('guard_id', '!=' , '')->where('guard_id', '!=' , 'NULL')->where('guard_id', '!=' , NULL)->where('shift_type', 'not like', 'template')->get()->sum('hours');
                $data_arry[dateFormat($value)]=$record;
                $total_count = $total_count + $record;
            }
        }
        $total_count = number_format( $total_count, 2, '.', '' );
        return response()->json(['success' => true, 'data' => $sts, 'code' => 200 , 'total_count' => $total_count , 'days_hours' => $data_arry, 'unpublish_shift_count' => $queryCount, 'deleted_roster' => $deleted_roster]);
    }  
}

public function getShiftHours($start, $end, $siteID = null, $continuation = false, $public_holiday = null, $ph_duration = null) {
    $actual_start = $start;
    $actual_end = $end;
    $day_start = Carbon::parse($start)->format('l');
    $day_end = Carbon::parse($end)->format('l');

    $start = strtotime($start);
    $end = strtotime($end);

    $diff = $end - $start;
    $hours = round($diff / ( 60 * 60 ), 2);
    $hoursCond = round($diff / ( 60 * 60 ), 2);

    $morning_start = 6;
    $morning_end = 18;

    $night_start = 18;
    $night_end = 6;

    $shift_start = $this->convert_into_fraction($start);
    $shift_end = $this->convert_into_fraction($end);
    // return [$shift_start,$shift_end];
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

    $sunday_start = 0;
    $sunday_end = 0;
    $total_sunday_hours = 0;

    $total_ph_hours = 0;
    $ph_start = 0;
    $ph_end = 0;

    // publid holiday calculation start here
    $start_in_public_holiday = false;
    $end_in_public_holiday = false;
    if ($siteID != null) {
        $site_state = DB::table('sites')->where('id', $siteID)->select('state')->first();

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

        if ($site_state && !empty($site_state->state) && isset($states_array[$site_state->state])) {
            $state = $states_array[$site_state->state];
        } else {
            $state = 'vic';
        }
    } else {
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
        $start = $public_holiday_start ? strtotime($public_holiday_start->date) + (60*60*24) : $start + (60*60*24) ;
        $day_start = Carbon::parse(date('m/d/Y', $end))->format('l');
        $hours = $hours - $total_ph_hours;
        $shift_start = 0;
        // echo 'Start in PH - '.$day_start;
    }elseif(!$start_in_public_holiday && $end_in_public_holiday){

        $ph_start_ts = strtotime(date('m/d/Y 00:00:00', $end));
        $diff = $end - $ph_start_ts;
        $total_ph_hours = round($diff / 3600, 2);
        $ph_start = $this->convert_into_fraction($ph_start_ts);
        $ph_end   = $this->convert_into_fraction($end);
        $end = $ph_start_ts;

        $shift_end = $this->convert_into_fraction($end);
        $hours = $hours - $total_ph_hours;
    }
    
// if ($total_ph_hours == 0) {

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
// }
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

    if ($shift_end < $shift_start) {
        $shift_end += 24;
    }

    $morning = $this->calculateHoursMorning($shift_start, $shift_end, $morning_start, $morning_end, $actual_start, $actual_end);
    if($morning > 12){
        $morning = $morning - 12;
    }

    $saturday_morning = round($this->calculateHoursMorning($saturday_start, $saturday_end, $morning_start, $morning_end, $actual_start, $actual_end), 2);

    $sunday_morning = round($this->calculateHoursMorning($sunday_start, $sunday_end, $morning_start, $morning_end, $actual_start, $actual_end), 2);

    $ph_morning = round($this->calculateHoursMorning($ph_start, $ph_end, $morning_start, $morning_end, $actual_start, $actual_end), 2);

    if ($morning < 0) {
        $morning = 0;
    }
    if ($saturday_morning < 0) {
        $saturday_morning = 0;
    }
    if ($sunday_morning < 0) {
        $sunday_morning = 0;
    }

    return [
        'morning' =>  $morning,
        'night' => round(((($hours - $morning) < 0) ? 0 : ($hours - $morning)), 2),
        'saturday_morning' => $saturday_morning,
        'saturday_night' => round(((($total_saturday_hours - $saturday_morning) < 0) ? 0 : ($total_saturday_hours - $saturday_morning)), 2),
        'sunday_morning' => $sunday_morning,
        'sunday_night' => round(((($total_sunday_hours - $sunday_morning) < 0) ? 0 : ($total_sunday_hours - $sunday_morning)), 2),
        'ph_morning' => $ph_morning,
        'ph_night' => round(((($total_ph_hours - $ph_morning) < 0) ? 0 : ($total_ph_hours - $ph_morning)), 2),
    ];
}

    function calculateHoursMorning($shift_start, $shift_end, $start, $end, $actual_start, $actual_end)
    {
        if (($shift_start >= $start && $shift_start < $end) && ($shift_end > $start && $shift_end <= $end)) {
            $startDateTime = strtotime($actual_start);
            $endDateTime = strtotime($actual_end);
            return abs(($endDateTime - $startDateTime) / (60 * 60));
            // return $hoursDifference = $actual_end->diffInHours($actual_start);
            // return $shift_end - $shift_start;
        } elseif (($shift_start >= $start && $shift_start < $end) && ($shift_end > $start && $shift_end > $end)) {
            $shift_end = $end;
            return $shift_end - $shift_start;
        } elseif (($shift_start > $start && $shift_start > $end) && ($shift_end > $start && $shift_end <= $end)) {
            $shift_start = $start;
            return $shift_end - $shift_start;
        } elseif (($shift_start < $start && $shift_start < $end) && ($shift_end > $start && $shift_end <= $end)) {
            $shift_start = $start;
            return $shift_end - $shift_start;
        } 
        elseif($shift_start >= $end && $shift_end > $start && $shift_end < $end)
        {
        // shift start in night in gone into day
        // echo 'Here';
            return $shift_end - $start;
        }elseif ($shift_start < $start && $shift_end > $end) {
            return $end - $start;
        } 
        elseif($shift_start > $start && $shift_end < $end){
            // if ($shift_start >= $start && $shift_end <= $end) {
            //     return 0;
            // }
            return $end - $shift_start;
        } 
        else {
            return 0;
        }
           
    }

function convert_into_fraction($time)
{
    return date('H', $time) + (date('i', $time) / 60);
}





public function deleteJobRosterTask(Request $request)
{
  $jobRosterTask  = JobRosterTask::where('id', $request->id)->first();
  $old_data = $jobRosterTask;
  if(!empty($jobRosterTask)){
    $jobRosterTask->delete();
    jobRosterActions($request->admin_id, 'delete_tasks', $jobRosterTask->id, 'job_roster', $old_data);
    return response()->json(['message' => "Task Deleted Successfully" ,  'code' => 200, 'success' => true]);
}else{
   return response()->json(['message' => "Task Not Found" ,  'code' => 404, 'success' => false],404);
}
}


public function publishShifts(Request $request)
{
    $publishShifts = JobRoster::whereIn('id', $request->id)->get();
    $publishShiftEmails = JobRoster::join('guards', 'guards.id', '=', 'job_rosters.guard_id')->whereIn('job_rosters.id', $request->email)->select('guards.id', 'guards.email', 'job_rosters.start', 'job_rosters.end')->groupBy('guards.id')->get();
    $publishShiftPhones = JobRoster::join('guards', 'guards.id', '=', 'job_rosters.guard_id')->whereIn('job_rosters.id', $request->phone)->where('guards.notification_token', '!=', '')->groupBy('guards.id')->select('guards.id', 'guards.notification_token', 'job_rosters.start', 'job_rosters.end')->get();
    $publishShiftSMS = JobRoster::join('guards', 'guards.id', '=', 'job_rosters.guard_id')->whereIn('job_rosters.id', $request->sms)->groupBy('guards.id')->select('guards.id', 'guards.phone')->get();
    if(!empty($publishShifts)){
        JobRoster::whereIn('id', $request->id)->update(['publish_status' => 1]);
        foreach ($publishShifts as $key => $value) {
            // $value->publish_status = 1;
            // $value->update();
            jobRosterActions($request->admin_id,'shift_publish', $value->id, 'job_roster');

            $admin_name = getAdminName($request->admin_id);
            $currnet_time = time();
            shiftCompleteActivity($value->id, $admin_name. ' Publish this Shift', 'shift_publish', $value->id, $currnet_time, $request->admin_id);
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

public function getCopyShiftSites(Request $request)
{
    $sites = JobRoster::join('sites', 'sites.id', '=', 'job_rosters.site_id')
    ->where('job_rosters.start', '>=', dbFormate($request->start))
    ->where('job_rosters.start', '<=', dbFormate($request->end))
    ->where('job_rosters.guard_id', '>', 0)
    ->where('sites.site_status', 'active')
    ->groupBy('sites.id')
    ->select('sites.id', 'sites.site_name')
    ->get();
    if (count($sites) > 0) {
        return response()->json(['message' => "Location Publish list!" ,  'code' => 200, 'success' => true, 'data' => $sites]);
    }else{
        return response()->json(['message' => "No Location for publish!" ,  'code' => 404, 'success' => false, 'data' => $sites]); 
    }
}

public function getCopyShiftSitesByCustomer(Request $request)
{
    
    $from_date = DateTime::createFromFormat('m-d-Y', $request->start)->format('Y-m-d') . ' 00:00';
    $to_date = DateTime::createFromFormat('m-d-Y', $request->end)->format('Y-m-d') . ' 23:59';
        
    $sites = JobRoster::join('sites', 'sites.id', '=', 'job_rosters.site_id')
    ->where('job_rosters.start', '>=', $from_date)
    ->where('job_rosters.start', '<=', $to_date)
    // ->where('job_rosters.guard_id', '>', 0)
    ->where('sites.site_status', 'active')
    ->whereIn('sites.customer_id', $request->customer_id) 
    ->where('job_rosters.deleted_at', null) 
    ->groupBy('sites.id')
    ->select('sites.id', 'sites.site_name')
    ->orderBy('sites.site_name', 'asc')
    ->get();
    if (count($sites) > 0) {
        return response()->json(['message' => "Location Publish list!" ,  'code' => 200, 'success' => true, 'data' => $sites]);
    }else{
        return response()->json(['message' => "No Location for publish!" ,  'code' => 404, 'success' => false, 'data' => $sites]); 
    }
}

public function copyRoster(Request $request)
{
    $rosters = JobRoster::where('job_rosters.start', '>=', dbFormate($request->start))
    ->where('job_rosters.start', '<=', (dbFormate($request->end).' 23:59'))
    ->where('job_rosters.guard_id', '>', 0)
    ->whereIn('job_rosters.site_id', $request->sites)
    ->with('jobRosterTask')->get();
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
                'site_id' => $roster->site_id,
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
                'reimbursement' => $roster->reimbursement,
                'reimbursement_text' => $roster->reimbursement_text,
                'reimbursement_value' => $roster->reimbursement_value,
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
                'roster_id' => $roster->roster_id,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ];

            $hours = $this->getShiftHours($next_roster['start'], $next_roster['end'], $next_roster['site_id'], $next_roster['continuation']);
            $next_roster['morning_hours'] = $hours['morning'];
            $next_roster['night_hours'] = $hours['night'];
            $next_roster['saturday_morning_hours'] = $hours['saturday_morning'];
            $next_roster['saturday_night_hours'] = $hours['saturday_night'];
            $next_roster['sunday_morning_hours'] = $hours['sunday_morning'];
            $next_roster['sunday_night_hours'] = $hours['sunday_night'];
            $next_roster['ph_morning_hours'] = $hours['ph_morning'];
            $next_roster['ph_night_hours'] = $hours['ph_night'];
            if ($next_roster['guard_id'] > 0) {
               $check = checkGuardShiftTiming($next_roster['start'], $next_roster['end'], $next_roster['guard_id'], $roster->roster_id);
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
                $addNewShift = JobRoster::insertGetId($next_roster);

                foreach ($roster->jobRosterTask as $key => $value) {
                    DB::table('job_roster_tasks')->insert([
                        'job_roster_id' => $addNewShift,
                        'task_start' => $value->task_start,
                        'task_end' => $value->task_end,
                        'status' => 'pending',
                    ]);
                }

                jobRosterActions($request->admin_id, 'add_shift_copy', $addNewShift, 'job_roster');
                $copied++;
            }


        }else{
            $addNewShift = JobRoster::insertGetId($next_roster);
            jobRosterActions($request->admin_id, 'add_shift_copy', $addNewShift, 'job_roster');
            $copied++;
        }
    }
}
return response()->json(['message' => "Shift copy successfully." ,  'code' => 200, 'success' => true, 'conflicts' => $conflicts, 'copied' => $copied]);


}

public function copyRosterNextDates(Request $request)
{
    $formattedDates = [];
    
    $startDate = \Carbon\Carbon::createFromFormat('m-d-Y', $request->start)->startOfDay();

    foreach ($request->days as $day) {
        $dayNumber = $day - 1;
        
        $specificDate = $startDate->copy()->addDays($dayNumber);
        
        $formattedDates[] = $specificDate->format('Y-m-d');
    }

    // Get all rosters
    $rosters = JobRoster::whereIn(DB::raw('DATE(start)'), $formattedDates)
        ->whereIn('site_id', $request->sites)
        ->with('jobRosterTask')
        ->get();

    if ($rosters->isEmpty()) {
        return response()->json(['success' => true, 'message' => 'Shifts not found']);
    }

    $workLimitationViolations = [];
    $processedGuards = [];

    foreach ($rosters as $roster) {
        if ($roster->guard_id > 0 && !($request->remove_staff == true || $request->remove_staff == 'true')) {
            if (in_array($roster->guard_id, $processedGuards)) {
                continue;
            }
            $processedGuards[] = $roster->guard_id;

            foreach ($request->weeks as $week_no) {
                $newStart = date("Y-m-d H:i", strtotime(date("Y-m-d H:i", strtotime($roster->start)) . " +".$week_no." week"));
                $newEnd = date("Y-m-d H:i", strtotime(date("Y-m-d H:i", strtotime($roster->end)) . " +".$week_no." week"));
                
                $guardOnLimitations = GuardWorkDetail::where('guard_id', $roster->guard_id)->first();
                
                $guard = Guard::find($roster->guard_id);
                $guardName = $guard ? $guard->first_name . ' ' . $guard->last_name : "Guard ID: {$roster->guard_id}";
                
                $week_array = $this->calculateFutureMonthFourthnight($newStart);
                
                $currentMonthData = $this->get_current_month_hours_guards_part_student(
                    $roster->guard_id, 
                    $week_array['week_start'], 
                    $week_array['week_end'], 
                    $roster->id
                );
                
                $addedHours = 0;

                $fortnight_start_date = new DateTime($week_array['week_start']);
                $fortnight_end_date = new DateTime($week_array['week_end']);
                
                if (!empty($currentMonthData)) {
                    foreach ($currentMonthData as $currentMonthDatas) {
                        $timestamps1 = strtotime($currentMonthDatas->start);
                        $timestamps2 = strtotime($currentMonthDatas->end);
                        
                        $startDay = date('w', $timestamps1);
                        $endDay = date('w', $timestamps2);
                        
                        if ($startDay == 0 && $endDay == 1) {
                            $midnightSunday = strtotime('tomorrow', $timestamps1) - 1;
                            $sundayHours = abs($midnightSunday - $timestamps1) / 3600;
                            
                            $mondayStart = strtotime('tomorrow 00:00:00', $timestamps1);
                            $mondayHours = abs($timestamps2 - $mondayStart) / 3600;
                            
                            $addedHours += $mondayHours;
                        } 
                        else if ($startDay == 6 && $endDay == 0) {
                            $midnightSaturday = strtotime('tomorrow', $timestamps1) - 1;
                            $saturdayHours = abs($midnightSaturday - $timestamps1) / 3600;
                            
                            $addedHours += $saturdayHours;
                        }
                        else if ($startDay == 0 && $endDay == 0) {
                            // Sunday only shift
                            $addedHours += abs($timestamps2 - $timestamps1) / 3600;
                        }
                        else {
                            $addedHours += abs($timestamps2 - $timestamps1) / 3600;
                        }
                    }
                }

                $dates_periods = array();
                $period = new DatePeriod(
                    new DateTime($fortnight_start_date->format("Y-m-d")),
                    new DateInterval('P1D'),
                    new DateTime($fortnight_end_date->format("Y-m-d"))
                );
                foreach ($period as $key => $value) {
                    array_push($dates_periods, $value->format('Y-m-d'));
                }
                array_push($dates_periods, $fortnight_end_date->format("Y-m-d"));
                   
                if($guardOnLimitations->guard_document_type == 'student_visa'){
                    if($guardOnLimitations->limit_exceed == 1)
                    {
                        $guardStart = new DateTime($guardOnLimitations->start_time);
                        $guardEnd = new DateTime($guardOnLimitations->end_time);
                        $interval = new DateInterval('P1D');
                        $dateRange = new DatePeriod($guardStart, $interval, $guardEnd->modify('+1 day'));

                        $guardDates = [];
                        foreach ($dateRange as $date) {
                            $guardDates[] = $date->format('Y-m-d');
                        }
                        $hasCompleteFortnight = false;

                        $guardDateCount = count($guardDates);

                        for ($i = 0; $i <= $guardDateCount - 14; $i++) {
                            $fourteenDays = array_slice($guardDates, $i, 14);
                            
                            $allExist = true;
                            foreach ($fourteenDays as $day) {
                                if (!in_array($day, $dates_periods)) {
                                    $allExist = false;
                                    break;
                                }
                            }
                            
                            if ($allExist) {
                                $hasCompleteFortnight = true;
                                break;
                            }
                        }

                        if ($hasCompleteFortnight) {
                        $totalWorkingHours = 72;    
                        } else {
                        $totalWorkingHours = 48;
                        }
                    }else{
                        $totalWorkingHours = 48;
                    }
                }else{
                    $totalWorkingHours = 72;
                    if ($guardOnLimitations && $guardOnLimitations->work_hours_limitation_status == 1) {
                        $totalWorkingHours = $guardOnLimitations->weekly_work_hours_limitation ?? 72;
                    }  
                }

                if ($addedHours * 2 > $totalWorkingHours) {
                    $workLimitationViolations[] = [
                        'guard_id' => $roster->guard_id,
                        'guard_name' => $guardName,
                        'week_offset' => $week_no . ' week(s)',
                        'shift_date' => date('Y-m-d', strtotime($newStart)),
                        'message' => "{$guardName} exceeds fortnight work limitation",
                        'details' => [
                            'fortnight_limit' => $totalWorkingHours,
                            'total_hours' => round($addedHours * 2, 2),
                            'exceed_hours' => round($addedHours * 2 - $totalWorkingHours, 2),
                            'fortnight_period' => $week_array['week_start'] . ' to ' . $week_array['week_end'],
                            'existing_hours_in_period' => round($addedHours, 2),
                        ]
                    ];
                }
                
            }
        }
    }
    
    if (!empty($workLimitationViolations)) {
        return response()->json([
            'success' => false,
            'message' => 'Work limitation violations detected',
            'violations' => $workLimitationViolations,
            'total_violations' => count($workLimitationViolations),
            'suggestion' => 'Please reassign shifts or adjust guard work hours to proceed.',
        ]);
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

        foreach ($request->weeks as $key1 => $week_no) {
            $newStart = date("Y-m-d H:i", strtotime(date("Y-m-d H:i", strtotime($roster->start)) . " +".$week_no." week"));
            $newEnd = date("Y-m-d H:i", strtotime(date("Y-m-d H:i", strtotime($roster->end)) . " +".$week_no." week"));

            $next_roster = [
                'site_id' => $roster->site_id,
                'guard_id' => ($request->remove_staff == true && $request->remove_staff == 'true' ? '' : $roster->guard_id),
                'start' => $newStart,
                'end' => $newEnd,
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
                'reimbursement' => $roster->reimbursement,
                'reimbursement_text' => $roster->reimbursement_text,
                'reimbursement_value' => $roster->reimbursement_value,
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
                'operation_notes' => in_array($request->notes, [false, 'false'], true) ? '' : $roster->operation_notes,
                'hours' => $roster->hours,
                'roster_id' => $roster->roster_id,
                'unprofile_name' => $roster->unprofile_name,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ];

            $hours = $this->getShiftHours($next_roster['start'], $next_roster['end'], $next_roster['site_id'], $next_roster['continuation']);
            $next_roster['morning_hours'] = $hours['morning'];
            $next_roster['night_hours'] = $hours['night'];
            $next_roster['saturday_morning_hours'] = $hours['saturday_morning'];
            $next_roster['saturday_night_hours'] = $hours['saturday_night'];
            $next_roster['sunday_morning_hours'] = $hours['sunday_morning'];
            $next_roster['sunday_night_hours'] = $hours['sunday_night'];
            $next_roster['ph_morning_hours'] = $hours['ph_morning'];
            $next_roster['ph_night_hours'] = $hours['ph_night'];
            
            if ($next_roster['guard_id'] > 0) {
                // Check shift timing conflict
                $check = checkGuardShiftTiming($newStart, $newEnd, $next_roster['guard_id'], $roster->roster_id);
                $next_roster['conflict'] = (!empty($check['conf']) ? $check['conf'] : '');
                $next_roster['conf_start'] = (!empty($check['start']) ? dbFormateDateTime($check['start']) : '');
                $next_roster['conf_end'] = (!empty($check['end']) ? dbFormateDateTime($check['end']) : '');
                
                // Check guard documents
                $check2 = checkGuardDocuments($next_roster['guard_id']);
                
                $guardWorkingHours = calCulateGuardWeekHours($newStart, $newEnd);
                $guardWorkLimitation = checkGuardWorkLimitation($next_roster['guard_id'], $guardWorkingHours);
                
                $conflict = false;
                if(!empty($check['start']) || !empty($check['end']) || !empty($check['conf']) || 
                   (!empty($check2) && $check2 != 'active') || !empty($guardWorkLimitation)){
                    $conflicts++;
                    $checkAdmin = checkAdmin($request->admin_id);
                    if($checkAdmin == 'admin'){
                        $conflict = true;
                    }
                }
                
                if (!$conflict) {
                    $addNewShift = JobRoster::insertGetId($next_roster);

                    foreach ($roster->jobRosterTask as $key => $value) {
                        DB::table('job_roster_tasks')->insert([
                            'job_roster_id' => $addNewShift,
                            'task_start' => $value->task_start,
                            'task_end' => $value->task_end,
                            'status' => 'pending',
                        ]);
                    }

                    jobRosterActions($request->admin_id, 'add_shift_copy', $addNewShift, 'job_roster');
                    $copied++;
                }
            } else {
                $addNewShift = JobRoster::insertGetId($next_roster);
                
                foreach ($roster->jobRosterTask as $key => $value) {
                    DB::table('job_roster_tasks')->insert([
                        'job_roster_id' => $addNewShift,
                        'task_start' => $value->task_start,
                        'task_end' => $value->task_end,
                        'status' => 'pending',
                    ]);
                }
                
                jobRosterActions($request->admin_id, 'add_shift_copy', $addNewShift, 'job_roster');
                $copied++;
            }
        }
    }
    
    return response()->json([
        'message' => "Shift copy successfully.",
        'code' => 200,
        'success' => true,
        'conflicts' => $conflicts,
        'copied' => $copied,
        'summary' => [
            'total_shifts_processed' => $rosters->count(),
            'shifts_copied' => $copied,
            'conflicts_found' => $conflicts,
            'work_limitation_checked' => true,
            'work_limitation_violations' => 0,
        ]
    ]);
}

public function rosterActions(Request $request)
{
    $query = JobRoster::where('job_rosters.start', '>=', dbFormateDateTimeStart($request->start))
    ->where('job_rosters.start', '<=', dbFormateDateTimeEnd($request->end));
    // whereBetween('job_rosters.start', [ dbFormate($request->start),  dbFormate($request->end)]);
    if ($request->has('siteIds') && !empty($request->siteIds)) {
        $query->whereIn('job_rosters.site_id', $request->siteIds);
    }
    if ($request->has('guardIds') && !empty($request->guardIds)) {
        $query->whereIn('job_rosters.guard_id', $request->guardIds);
    }
    if ($request->has('customer_ids') && !empty($request->customer_ids)) {
        $query->join('sites', 'sites.id', '=', 'job_rosters.site_id');
        $query->whereIn('sites.customer_id', $request->customer_ids);
    }
    if ($request->has('roster_id') && $request->roster_id != '') {
        $query->where('job_rosters.roster_id', $request->roster_id);
    }
    $deleted_shifts = $query->select('job_rosters.id','job_rosters.guard_id')->get();
    if ($request->type == 'clear_shifts') {
        foreach ($deleted_shifts as $key => $d) {
            JobRoster::where('id', $d->id)->delete();
            DB::table('job_roster_activites')->where(['guard_id'=> $d->guard_id, 'job_roster_id'=>$d->id])->delete();
            DB::table('incident_reports')->where(['roster_id'=> $d->id])->delete();
            DB::table('job_roster_tasks')->where(['job_roster_id'=> $d->id])->delete();
            DB::table('job_breaks')->where(['roster_id'=> $d->id])->delete();
            jobRosterActions($request->admin_id, 'delete_shift', $d->id, 'job_roster');
        }
        return response()->json(['message' => "Shifts cleared" ,  'code' => 200, 'success' => true]);
    }elseif($request->type == 'unpublish')
    {
        // $deleted_shifts = JobRoster::whereIn('site_id', $request->siteIds)->whereBetween('start', [$request->start, $request->end])->get();
        foreach ($deleted_shifts as $key => $d) {
            JobRoster::where('id', $d->id)->update(['publish_status' => 0]);
            jobRosterActions($request->admin_id, 'shift_unpublish', $d->id, 'job_roster');
        }
        return response()->json(['message' => "shift unpublished" ,  'code' => 200, 'success' => true]);
    }elseif($request->type == 'unassign')
    {
        // $deleted_shifts = JobRoster::whereIn('site_id', $request->siteIds)->whereBetween('start', [$request->start, $request->end])->get();
        foreach ($deleted_shifts as $key => $d) {
            JobRoster::where('id', $d->id)->update(['guard_id' => NULL,
                'conflict' => '',
                'conf_start' => '',
                'conf_end' => '',
            ]);
            jobRosterActions($request->admin_id, 'shift_guard_unassign', $d->id, 'job_roster');
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
public function copyIntoCurrent($request)
{
    $query = JobRoster::where('job_rosters.start', '>=', (dbFormate($request->start).' 00:00'));
    if ($request->has('customer_ids') && !empty($request->customer_ids)) {
        $query->join('sites', 'sites.id', '=', 'job_rosters.site_id');
        $query->whereIn('sites.customer_id', $request->customer_ids);
    }
    if ($request->has('roster_id') && $request->roster_id != '') {
        $query->where('job_rosters.roster_id', $request->roster_id);
    }
    $query->where('job_rosters.start', '<=', (dbFormate($request->end).' 23:59'));
    // ->where('job_rosters.guard_id', '>', 0); # UNCOMMENT WHEN YOU WANT TO COPY ONLY ASSIGNED SHIFTS
    if (!empty($request->sites)) {
        $query->whereIn('job_rosters.site_id', $request->sites);
    }
    
    $rosters = $query->select('job_rosters.*')->with('jobRosterTask')
    ->get();

     $workLimitationViolations = [];
     $processedGuards = [];

    foreach ($rosters as $roster) {

            if ($roster->guard_id > 0) {
                    if (in_array($roster->guard_id, $processedGuards)) {
                continue;
            }
            $processedGuards[] = $roster->guard_id;

            $newStart = date("Y-m-d H:i", strtotime(date("Y-m-d H:i", strtotime($roster->start))));
            $newEnd = date("Y-m-d H:i", strtotime(date("Y-m-d H:i", strtotime($roster->end))));
            
            $guardOnLimitations = GuardWorkDetail::where('guard_id', $roster->guard_id)->first();
            
            // Get guard name for better error messages
            $previous_guard_id = $roster->guard_id;
            $guard = Guard::find($roster->guard_id);
            $guardName = $guard ? $guard->first_name . ' ' . $guard->last_name : "Guard ID: {$roster->guard_id}";
            
            $week_array = $this->calculateFutureMonthFourthnight($newStart);
            
            $currentMonthData = $this->get_current_month_hours_guards_part_student(
                $roster->guard_id, 
                $week_array['week_start'], 
                $week_array['week_end'], 
                $roster->id
            );
            
            $addedHours = 0;
                
            // Calculate existing hours in fortnight period
            if (!empty($currentMonthData)) {
                foreach ($currentMonthData as $currentMonthDatas) {
                    $timestamps1 = strtotime($currentMonthDatas->start);
                    $timestamps2 = strtotime($currentMonthDatas->end);
                    
                    $startDay = date('w', $timestamps1);
                    $endDay = date('w', $timestamps2);
                    
                    if ($startDay == 0 && $endDay == 1) {
                        // Sunday to Monday shift
                        $midnightSunday = strtotime('tomorrow', $timestamps1) - 1;
                        $sundayHours = abs($midnightSunday - $timestamps1) / 3600;
                        
                        $mondayStart = strtotime('tomorrow 00:00:00', $timestamps1);
                        $mondayHours = abs($timestamps2 - $mondayStart) / 3600;
                        
                        $addedHours += $mondayHours;
                    } 
                    else if ($startDay == 6 && $endDay == 0) {
                        // Saturday to Sunday shift
                        $midnightSaturday = strtotime('tomorrow', $timestamps1) - 1;
                        $saturdayHours = abs($midnightSaturday - $timestamps1) / 3600;
                        
                        $addedHours += $saturdayHours;
                    }
                    else if ($startDay == 0 && $endDay == 0) {
                        // Sunday only shift
                        $addedHours += abs($timestamps2 - $timestamps1) / 3600;
                    }
                    else {
                        // Regular shift
                        $addedHours += abs($timestamps2 - $timestamps1) / 3600;
                    }
                }
            }

            $fortnight_start_date = new DateTime($week_array['week_start']);
            $fortnight_end_date = new DateTime($week_array['week_end']);
                
                if (!empty($currentMonthData)) {
                    foreach ($currentMonthData as $currentMonthDatas) {
                        $timestamps1 = strtotime($currentMonthDatas->start);
                        $timestamps2 = strtotime($currentMonthDatas->end);
                        
                        $startDay = date('w', $timestamps1);
                        $endDay = date('w', $timestamps2);
                        
                        if ($startDay == 0 && $endDay == 1) {
                            $midnightSunday = strtotime('tomorrow', $timestamps1) - 1;
                            $sundayHours = abs($midnightSunday - $timestamps1) / 3600;
                            
                            $mondayStart = strtotime('tomorrow 00:00:00', $timestamps1);
                            $mondayHours = abs($timestamps2 - $mondayStart) / 3600;
                            
                            $addedHours += $mondayHours;
                        } 
                        else if ($startDay == 6 && $endDay == 0) {
                            $midnightSaturday = strtotime('tomorrow', $timestamps1) - 1;
                            $saturdayHours = abs($midnightSaturday - $timestamps1) / 3600;
                            
                            $addedHours += $saturdayHours;
                        }
                        else if ($startDay == 0 && $endDay == 0) {
                            // Sunday only shift
                            $addedHours += abs($timestamps2 - $timestamps1) / 3600;
                        }
                        else {
                            $addedHours += abs($timestamps2 - $timestamps1) / 3600;
                        }
                    }
                }

                $dates_periods = array();
                $period = new DatePeriod(
                    new DateTime($fortnight_start_date->format("Y-m-d")),
                    new DateInterval('P1D'),
                    new DateTime($fortnight_end_date->format("Y-m-d"))
                );
                foreach ($period as $key => $value) {
                    array_push($dates_periods, $value->format('Y-m-d'));
                }
                array_push($dates_periods, $fortnight_end_date->format("Y-m-d"));
                   
                if($guardOnLimitations->guard_document_type == 'student_visa'){
                    if($guardOnLimitations->limit_exceed == 1)
                    {
                        $guardStart = new DateTime($guardOnLimitations->start_time);
                        $guardEnd = new DateTime($guardOnLimitations->end_time);
                        $interval = new DateInterval('P1D');
                        $dateRange = new DatePeriod($guardStart, $interval, $guardEnd->modify('+1 day'));

                        $guardDates = [];
                        foreach ($dateRange as $date) {
                            $guardDates[] = $date->format('Y-m-d');
                        }
                        $hasCompleteFortnight = false;

                        $guardDateCount = count($guardDates);

                        for ($i = 0; $i <= $guardDateCount - 14; $i++) {
                            $fourteenDays = array_slice($guardDates, $i, 14);
                            
                            $allExist = true;
                            foreach ($fourteenDays as $day) {
                                if (!in_array($day, $dates_periods)) {
                                    $allExist = false;
                                    break;
                                }
                            }
                            
                            if ($allExist) {
                                $hasCompleteFortnight = true;
                                break;
                            }
                        }

                        if ($hasCompleteFortnight) {
                        $totalWorkingHours = 72;    
                        } else {
                        $totalWorkingHours = 48;
                        }
                    }else{
                        $totalWorkingHours = 48;
                    }
                }else{
                    $totalWorkingHours = 72;
                    if ($guardOnLimitations && $guardOnLimitations->work_hours_limitation_status == 1) {
                        $totalWorkingHours = $guardOnLimitations->weekly_work_hours_limitation ?? 72;
                    }  
                }
                                
            if ($addedHours * 2 > $totalWorkingHours) {
                $workLimitationViolations[] = [
                    'guard_id' => $roster->guard_id,
                    'guard_name' => $guardName,
                    'shift_date' => date('Y-m-d', strtotime($newStart)),
                    'message' => "{$guardName} exceeds fortnight work limitation",
                    'details' => [
                        'fortnight_limit' => $totalWorkingHours,
                        'total_hours' => round($addedHours * 2, 2),
                        'exceed_hours' => round($addedHours * 2 - $totalWorkingHours, 2),
                        'fortnight_period' => $week_array['week_start'] . ' to ' . $week_array['week_end'],
                        'existing_hours_in_period' => round($addedHours, 2),
                    ]
                ];
            }      
            
        }
    }
    
    // If there are work limitation violations, return them immediately
    if (!empty($workLimitationViolations)) {
        return response()->json([
            'success' => false,
            'message' => 'Work limitation violations detected',
            'violations' => $workLimitationViolations,
            'total_violations' => count($workLimitationViolations),
            'suggestion' => 'Please reassign shifts or adjust guard work hours to proceed.',
        ]);
    }
    
    
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
            'site_id' => $roster->site_id,
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
            'reimbursement' => $roster->reimbursement,
            'reimbursement_text' => $roster->reimbursement_text,
            'reimbursement_value' => $roster->reimbursement_value,
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
            'roster_id' => $roster->roster_id,
            'unprofile_name' => $roster->unprofile_name,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ];
        $hours = $this->getShiftHours($next_roster['start'], $next_roster['end'], $next_roster['site_id'], $next_roster['continuation']);
        $next_roster['morning_hours'] = $hours['morning'];
        $next_roster['night_hours'] = $hours['night'];
        $next_roster['saturday_morning_hours'] = $hours['saturday_morning'];
        $next_roster['saturday_night_hours'] = $hours['saturday_night'];
        $next_roster['sunday_morning_hours'] = $hours['sunday_morning'];
        $next_roster['sunday_night_hours'] = $hours['sunday_night'];
        $next_roster['ph_morning_hours'] = $hours['ph_morning'];
        $next_roster['ph_night_hours'] = $hours['ph_night'];
        if ($next_roster['guard_id'] > 0) {
           $check = checkGuardShiftTiming($next_roster['start'], $next_roster['end'], $next_roster['guard_id'], $roster->roster_id);
           $next_roster['conflict'] = (!empty($check['conf']) ? $check['conf'] : '');
           $next_roster['conf_start'] = (!empty($check['start']) ? dbFormateDateTime($check['start']) : '');
           $next_roster['conf_end'] = (!empty($check['end']) ? dbFormateDateTime($check['end']) : '');
           $guardWorkingHours = calCulateGuardWeekHours($next_roster['start'], $next_roster['end']);
           $guardWorkLimitation = checkGuardWorkLimitation($next_roster['guard_id'], $guardWorkingHours);
           $check2 = checkGuardDocuments($next_roster['guard_id']);
           $conflict = false;
           if(!empty($check['start']) || !empty($check['end']) || !empty($check['conf']) || (!empty($check2) && $check2 != 'active') || !empty($guardWorkLimitation)){
            $conflicts++;
            // $next_roster['guard_id'] = null;
            $checkAdmin  = checkAdmin($request->admin_id);
            if($checkAdmin == 'admin'){
                $conflict = true;
            }
        }
        if (!$conflict) {
            $addNewShift = JobRoster::insertGetId($next_roster);
            foreach ($roster->jobRosterTask as $key => $value) {
                DB::table('job_roster_tasks')->insert([
                    'job_roster_id' => $addNewShift,
                    'task_start' => $value->task_start,
                    'task_end' => $value->task_end,
                    'status' => 'pending',
                ]);
            }
            jobRosterActions($request->admin_id, 'add_shift_copy', $addNewShift, 'job_roster');
            $copied++;
        }

    }else{
        $addNewShift = JobRoster::insertGetId($next_roster);
        jobRosterActions($request->admin_id, 'add_shift_copy', $addNewShift, 'job_roster');
        $copied++;
    }

}
return response()->json(['message' => "Coped this to current week!.",  'code' => 200, 'success' => true, 'conflicts' => $conflicts, 'copied' => $copied]);


}

public function rolloverWeek($request)
{
    $query = JobRoster::where('job_rosters.start', '>=', (dbFormate($request->start).' 00:00'));
    if ($request->has('customer_ids') && !empty($request->customer_ids) && $request->has('siteIds') && !empty($request->siteIds)) {
        $query->join('sites', 'sites.id', '=', 'job_rosters.site_id');
        $query->whereIn('sites.customer_id', $request->customer_ids);
        $query->whereIn('sites.id', $request->siteIds);
    }
    if ($request->has('roster_id') && $request->roster_id != '') {
        $query->where('job_rosters.roster_id', $request->roster_id);
    }
    $query->where('job_rosters.start', '<=', (dbFormate($request->end).' 23:59'));
    // ->where('job_rosters.guard_id', '>', 0); # UNCOMMENT WHEN YOU WANT TO COPY ONLY ASSIGNED SHIFTS
    if (!empty($request->sites)) {
        $query->whereIn('job_rosters.site_id', $request->sites);
    }
    $rosters = $query->select('job_rosters.*')->with('jobRosterTask')
    ->get();
    
    
     $workLimitationViolations = [];
     $processedGuards = [];
     $week_no = 1;
    foreach ($rosters as $roster) {

            if ($roster->guard_id > 0) {
                    if (in_array($roster->guard_id, $processedGuards)) {
                continue;
            }
            $processedGuards[] = $roster->guard_id;

            $newStart = date("Y-m-d H:i", strtotime(date("Y-m-d H:i", strtotime($roster->start)) . " +".$week_no." week"));
            $newEnd = date("Y-m-d H:i", strtotime(date("Y-m-d H:i", strtotime($roster->end)) . " +".$week_no." week"));
            
            $guardOnLimitations = GuardWorkDetail::where('guard_id', $roster->guard_id)->first();
            
            $previous_guard_id = $roster->guard_id;
            $guard = Guard::find($roster->guard_id);
            $guardName = $guard ? $guard->first_name . ' ' . $guard->last_name : "Guard ID: {$roster->guard_id}";
            
            $week_array = $this->calculateFutureMonthFourthnight($newStart);
            
            $currentMonthData = $this->get_current_month_hours_guards_part_student(
                $roster->guard_id, 
                $week_array['week_start'], 
                $week_array['week_end'], 
                $roster->id
            );
            
            $addedHours = 0;

            $fortnight_start_date = new DateTime($week_array['week_start']);
            $fortnight_end_date = new DateTime($week_array['week_end']);
                
            if (!empty($currentMonthData)) {
                foreach ($currentMonthData as $currentMonthDatas) {
                    $timestamps1 = strtotime($currentMonthDatas->start);
                    $timestamps2 = strtotime($currentMonthDatas->end);
                    
                    $startDay = date('w', $timestamps1);
                    $endDay = date('w', $timestamps2);
                    
                    if ($startDay == 0 && $endDay == 1) {
                        $midnightSunday = strtotime('tomorrow', $timestamps1) - 1;
                        $sundayHours = abs($midnightSunday - $timestamps1) / 3600;
                        
                        $mondayStart = strtotime('tomorrow 00:00:00', $timestamps1);
                        $mondayHours = abs($timestamps2 - $mondayStart) / 3600;
                        
                        $addedHours += $mondayHours;
                    } 
                    else if ($startDay == 6 && $endDay == 0) {
                        $midnightSaturday = strtotime('tomorrow', $timestamps1) - 1;
                        $saturdayHours = abs($midnightSaturday - $timestamps1) / 3600;
                        
                        $addedHours += $saturdayHours;
                    }
                    else if ($startDay == 0 && $endDay == 0) {
                        $addedHours += abs($timestamps2 - $timestamps1) / 3600;
                    }
                    else {
                        $addedHours += abs($timestamps2 - $timestamps1) / 3600;
                    }
                }
            }

            $dates_periods = array();
            $period = new DatePeriod(
                new DateTime($fortnight_start_date->format("Y-m-d")),
                new DateInterval('P1D'),
                new DateTime($fortnight_end_date->format("Y-m-d"))
            );
            foreach ($period as $key => $value) {
                array_push($dates_periods, $value->format('Y-m-d'));
            }
            array_push($dates_periods, $fortnight_end_date->format("Y-m-d"));
                
            if($guardOnLimitations->guard_document_type == 'student_visa'){
                if($guardOnLimitations->limit_exceed == 1)
                {
                    $guardStart = new DateTime($guardOnLimitations->start_time);
                    $guardEnd = new DateTime($guardOnLimitations->end_time);
                    $interval = new DateInterval('P1D');
                    $dateRange = new DatePeriod($guardStart, $interval, $guardEnd->modify('+1 day'));

                    $guardDates = [];
                    foreach ($dateRange as $date) {
                        $guardDates[] = $date->format('Y-m-d');
                    }
                    $hasCompleteFortnight = false;

                    $guardDateCount = count($guardDates);

                    for ($i = 0; $i <= $guardDateCount - 14; $i++) {
                        $fourteenDays = array_slice($guardDates, $i, 14);
                        
                        $allExist = true;
                        foreach ($fourteenDays as $day) {
                            if (!in_array($day, $dates_periods)) {
                                $allExist = false;
                                break;
                            }
                        }
                        
                        if ($allExist) {
                            $hasCompleteFortnight = true;
                            break;
                        }
                    }

                    if ($hasCompleteFortnight) {
                    $totalWorkingHours = 72;    
                    } else {
                    $totalWorkingHours = 48;
                    }
                }else{
                    $totalWorkingHours = 48;
                }
            }else{
                $totalWorkingHours = 72;
                if ($guardOnLimitations && $guardOnLimitations->work_hours_limitation_status == 1) {
                    $totalWorkingHours = $guardOnLimitations->weekly_work_hours_limitation ?? 72;
                }  
            }
                                
            if ($addedHours * 2 > $totalWorkingHours) {
                $workLimitationViolations[] = [
                    'guard_id' => $roster->guard_id,
                    'guard_name' => $guardName,
                    'shift_date' => date('Y-m-d', strtotime($newStart)),
                    'message' => "{$guardName} exceeds fortnight work limitation",
                    'details' => [
                        'fortnight_limit' => $totalWorkingHours,
                        'total_hours' => round($addedHours * 2, 2),
                        'exceed_hours' => round($addedHours * 2 - $totalWorkingHours, 2),
                        'fortnight_period' => $week_array['week_start'] . ' to ' . $week_array['week_end'],
                        'existing_hours_in_period' => round($addedHours, 2),
                    ]
                ];
            }      
            
        }
    }
    
    if (!empty($workLimitationViolations)) {
        return response()->json([
            'success' => false,
            'message' => 'Work limitation violations detected',
            'violations' => $workLimitationViolations,
            'total_violations' => count($workLimitationViolations),
            'suggestion' => 'Please reassign shifts or adjust guard work hours to proceed.',
        ]);
    }

    $days = ['mon' => 'monday', 'tue' => 'tuesday', 'wed' => 'wednesday', 'thu' => 'thursday', 'fri' => 'friday', 'sat' => 'saturday' , 'sun' => 'sunday'];

    $conflicts = 0;
    $copied = 0;
    $week_no = 1;

    foreach ($rosters as $key => $roster) {
        if ($roster->roster_id != null || $roster->roster_id != 0) {

            $start_time = date('H:i', strtotime($roster->start));
            $end_time = date('H:i', strtotime($roster->end));

            // Fix: Use shift's own date to calculate next week's same weekday
            $shift_date = Carbon::parse($roster->start);
            $next_shift_date = $shift_date->copy()->addWeek();

            $shift_end_date = Carbon::parse($roster->end);
            $next_shift_end_date = $shift_end_date->copy()->addWeek();

            $next_roster = [
                'site_id' => $roster->site_id,
                'guard_id' => $roster->guard_id,
                'start' => $next_shift_date->format('Y-m-d') . ' ' . $start_time,
                'end' => $next_shift_end_date->format('Y-m-d') . ' ' . $end_time,
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
                'reimbursement' => $roster->reimbursement,
                'reimbursement_text' => $roster->reimbursement_text,
                'reimbursement_value' => $roster->reimbursement_value,
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
                'created_at' => now(),
                'updated_at' => now(),
                'roster_id' => $roster->roster_id,
                'unprofile_name' => $roster->unprofile_name,
            ];

            $hours = $this->getShiftHours($next_roster['start'], $next_roster['end'], $next_roster['site_id'], $next_roster['continuation']);
            $next_roster['morning_hours'] = $hours['morning'];
            $next_roster['night_hours'] = $hours['night'];
            $next_roster['saturday_morning_hours'] = $hours['saturday_morning'];
            $next_roster['saturday_night_hours'] = $hours['saturday_night'];
            $next_roster['sunday_morning_hours'] = $hours['sunday_morning'];
            $next_roster['sunday_night_hours'] = $hours['sunday_night'];
            $next_roster['ph_morning_hours'] = $hours['ph_morning'];
            $next_roster['ph_night_hours'] = $hours['ph_night'];

            if ($next_roster['guard_id'] > 0) {
                $check = checkGuardShiftTiming($next_roster['start'], $next_roster['end'], $next_roster['guard_id'], $roster->roster_id);
                $next_roster['conflict'] = !empty($check['conf']) ? $check['conf'] : '';
                $next_roster['conf_start'] = !empty($check['start']) ? dbFormateDateTime($check['start']) : '';
                $next_roster['conf_end'] = !empty($check['end']) ? dbFormateDateTime($check['end']) : '';

                $guardWorkingHours = calCulateGuardWeekHours($next_roster['start'], $next_roster['end']);
                $guardWorkLimitation = checkGuardWorkLimitation($next_roster['guard_id'], $guardWorkingHours);
                $check2 = checkGuardDocuments($next_roster['guard_id']);

                $conflict = false;
                if (!empty($check['start']) || !empty($check['end']) || !empty($check['conf']) || (!empty($check2) && $check2 != 'active') || !empty($guardWorkLimitation)) {
                    $conflicts++;
                    $checkAdmin = checkAdmin($request->admin_id);
                    if ($checkAdmin == 'admin') {
                        $conflict = true;
                    }
                }

                if (!$conflict) {
                    $addNewShift = JobRoster::insertGetId($next_roster);
                    foreach ($roster->jobRosterTask as $key => $value) {
                        DB::table('job_roster_tasks')->insert([
                            'job_roster_id' => $addNewShift,
                            'task_start' => $value->task_start,
                            'task_end' => $value->task_end,
                            'status' => 'pending',
                        ]);
                    }
                    jobRosterActions($request->admin_id, 'add_shift_copy', $addNewShift, 'job_roster');
                    $copied++;
                }
            } else {
                $addNewShift = JobRoster::insertGetId($next_roster);
                jobRosterActions($request->admin_id, 'add_shift_copy', $addNewShift, 'job_roster');
                $copied++;
            }
        }
    }

    return response()->json(['message' => "Shift rollover completed" ,  'code' => 200, 'success' => true, 'conflicts' => $conflicts, 'copied' => $copied]);


}

function createMultipleShifts(Request $request)
{
    // if ($request->type == 'unassigned') {
    //     $shifts_count = 0;
    //     foreach ($request->multiiShifts as $key => $shift) {
    //         $roster = [
    //             'site_id' => $shift['site_id'],
    //             'start' => dbFormateDateTime($shift['new_start']),
    //             'end' => dbFormateDateTime($shift['new_end']),
    //             'shift_payable' => 'yes',
    //             'shift_chargeable' => 'yes',
    //             'shift_create_status' => 'pending',
    //             'job_status' => 'pending',
    //         ];
    //         $hours = $this->getShiftHours($roster['start'], $roster['end'], $roster['site_id']);
    //         $guardWorkingHours = calCulateGuardWeekHours(dbFormateDateTime($shift['new_start']), dbFormateDateTime($shift['new_end']));
    //         $roster['morning_hours'] = $hours['morning'];
    //         $roster['night_hours'] = $hours['night'];
    //         $roster['saturday_morning_hours'] = $hours['saturday_morning'];
    //         $roster['saturday_night_hours'] = $hours['saturday_night'];
    //         $roster['sunday_morning_hours'] = $hours['sunday_morning'];
    //         $roster['sunday_night_hours'] = $hours['sunday_night'];
    //         $roster['ph_morning_hours'] = $hours['ph_morning'];
    //         $roster['ph_night_hours'] = $hours['ph_night'];
    //         $roster['hours'] = $guardWorkingHours;
    //         for ($i=0; $i < $shift['shift_count']; $i++) { 
    //             JobRoster::insert($roster);
    //             $shifts_count++;
    //         }
    //     }
    //     return response()->json(['code' => 200, 'success' => true, 'message' => $shifts_count.' Un-covered shifts create successfully.']);
    // }else{
        $conflicts = 0;
        $rost = [];
        foreach ($request->multiiShifts as $key => $shift) {
            if ($shift['shift_count'] < count($shift['guard_id'])) {
                 return response()->json(['code' => 200, 'success' => false, 'message' => 'Shift count is less then staff selected!', 'confirm_shift' => false]);
            }
            $roster = [
                'site_id' => $shift['site_id'],
                'start' => dbFormateDateTime($shift['new_start']),
                'end' => dbFormateDateTime($shift['new_end']),
                'shift_payable' => 'yes',
                'shift_chargeable' => 'yes',
                'shift_create_status' => 'pending',
                'job_status' => 'pending',
                'roster_id' => $request->roster_id,
                // 'guard_id' => $shift['guard_id']
            ];
            for ($i=0; $i < $shift['shift_count']; $i++) { 
                if (isset($shift['guard_id'][$i])) {
                    $roster['guard_id'] = $shift['guard_id'][$i];
                }else{
                    $roster['guard_id'] = null;
                }
            $hours = $this->getShiftHours($roster['start'], $roster['end'], $roster['site_id'], $roster['continuation']);
            $guardWorkingHours = calCulateGuardWeekHours(dbFormateDateTime($shift['new_start']), dbFormateDateTime($shift['new_end']));
            $roster['morning_hours'] = $hours['morning'];
            $roster['night_hours'] = $hours['night'];
            $roster['saturday_morning_hours'] = $hours['saturday_morning'];
            $roster['saturday_night_hours'] = $hours['saturday_night'];
            $roster['sunday_morning_hours'] = $hours['sunday_morning'];
            $roster['sunday_night_hours'] = $hours['sunday_night'];
            $roster['ph_morning_hours'] = $hours['ph_morning'];
            $roster['ph_night_hours'] = $hours['ph_night'];
            $roster['hours'] = $guardWorkingHours;
            if ($roster['guard_id'] > 0) {
            $check = checkGuardShiftTiming($roster['start'], $roster['end'], $roster['guard_id'], $request->roster_id);
            $roster['conflict'] = (!empty($check['conf']) ? $check['conf'] : '');
            $roster['conf_start'] = (!empty($check['start']) ? dbFormateDateTime($check['start']) : '');
            $roster['conf_end'] = (!empty($check['end']) ? dbFormateDateTime($check['end']) : '');
            $guardWorkingHours = calCulateGuardWeekHours($roster['start'], $roster['end']);
            $guardWorkLimitation = checkGuardWorkLimitation($roster['guard_id'], $guardWorkingHours);
            $check2 = checkGuardDocuments($roster['guard_id']);
            if(!empty($check['start']) || !empty($check['end']) || !empty($check['conf']) || (!empty($check2) && $check2 != 'active') || !empty($guardWorkLimitation)){
                $conflicts++;  
            }
            $rost[] = $roster;
            }else{
            $roster['conflict'] = '';
            $roster['conf_start'] = '';
            $roster['conf_end'] = '';
            $rost[] = $roster;
            }
            // JobRoster::insert($roster);

        }
    }
        if ($conflicts > 0 && (!$request->has('confirm_shift') || $request->confirm_shift == false)) {
            $checkAdmin  = checkAdmin($request->admin_id);
            if($checkAdmin == 'super-admin'){
                return response()->json(['code' => 200, 'success' => false, 'message' => $conflicts.' shifts having conflicts.', 'confirm_shift' => true]);
            }else{
                return response()->json(['code' => 200, 'success' => false, 'message' => $conflicts.' shifts having conflicts. Please contact Super Admin!', 'confirm_shift' => false]);
            }
        }else{
            JobRoster::insert($rost);
            return response()->json(['code' => 200, 'success' => true, 'message' => count($rost).' shifts created']);

        }

    // }
}
function createAsapJob(Request $request)
{
   $roster = [
    'site_id' => $request->site_id,
    'start' => dbFormateDateTime($request->new_start),
    'end' => dbFormateDateTime($request->new_end),
    'shift_payable' => 'yes',
    'shift_chargeable' => 'yes',
    'shift_create_status' => 'pending',
    'job_status' => 'pending',
    'asap' => 1,
    'radius' => $request->radius,
    'job_instrcutions' => $request->job_instrcutions,
    'publish_status' => 1,
    'roster_id' => $request->roster_id
];
$hours = $this->getShiftHours($roster['start'], $roster['end'], $roster['site_id']);
$guardWorkingHours = calCulateGuardWeekHours(dbFormateDateTime($request->new_start), dbFormateDateTime($request->new_end));
$roster['morning_hours'] = $hours['morning'];
$roster['night_hours'] = $hours['night'];
$roster['saturday_morning_hours'] = $hours['saturday_morning'];
$roster['saturday_night_hours'] = $hours['saturday_night'];
$roster['sunday_morning_hours'] = $hours['sunday_morning'];
$roster['sunday_night_hours'] = $hours['sunday_night'];
$roster['ph_morning_hours'] = $hours['ph_morning'];
$roster['ph_night_hours'] = $hours['ph_night'];
$roster['hours'] = $guardWorkingHours;
$inserted = JobRoster::insert($roster);
if(isset($guards) && $guards)
{
    foreach($guards as $grd){
        $guard = Guard::where('id', $grd['id'])->select('id', 'notification_token')->first();
        $notificaion['notification_token'] = $guard['notification_token'];
        $notificaion['message'] = "ASAP job has been published. Please check your app.";
        $notificaion['title'] = 'ASAP Job';
        $notificaion['page'] = 'asap-job-list';
        send_push_notification($notificaion);
    }
}
if($inserted)
{
    return response()->json(['code' => 200, 'success' => true, 'message' => 'ASAP Job Published.']);
}else{
    return response()->json(['code' => 200, 'success' => false, 'message' => 'Fail to create ASAP job!']);

}
}

public function testNotification($token)
{
        $notificaion['notification_token'] = $token;
        $notificaion['message'] = "Your shift has been deleted. Please check your app.";
        $notificaion['title'] = 'Delete Shift';
        $notificaion['page'] = 'homepage';
        return send_push_notification($notificaion);
}


// public function getAllAavailableGuards()
//      {
//         $guards_with_active_shifts = DB::table('job_rosters')
//         ->leftJoin('job_roster_activites', 'job_roster_activites.job_roster_id', '=', 'job_rosters.id')
//         ->where('start', '<=', date('Y-m-d H:i'))
//         ->where('end', '>=', date('Y-m-d H:i'))
//         ->select('job_rosters.guard_id as id', 'job_roster_activites.id as activity_id')
//         ->get();

//         $guard_ids = [];
//         foreach ($guards_with_active_shifts as $key => $id) {
//             if ($id->activity_id > 0) {
//                 $guard_ids[] = $id->id;
//             }
//         }
        
//         //return $guard_ids;
        
//         $available_gaurds = array();
//     // get Guards who dont have any active shift right now
//         $guards = DB::table('guards')
//         ->join('job_rosters', 'job_rosters.guard_id', '=', 'guards.id')
//          ->whereNotIn('guards.id', $guard_ids)->where('guards.guard_status', 'active')->where('guards.is_available', 'yes')->where('guards.admin_approval_status', 'active')
//         ->select('guards.id', 'guards.first_name','guards.middle_name', 'guards.last_name', 'guards.phone', 'guards.profile_image')
//         ->orderBy('name', 'ASC')
//         ->groupBy('job_rosters.guard_id')
//         ->get();
        
//         foreach ($guards as $guard) {
//             $hours = '';
//             $max_hours = 0;
//             $guard->working_hours = $max_hours;
//             $is_available = true;
//             $prev_shift_res =  DB::table('job_rosters')->where('guard_id', $guard->id)->where('end', '<', date('Y-m-d H:i:s'))->orderBy('start', 'desc')->first();
//             if (!empty($prev_shift_res)) {

//                 $site = DB::table('sites')->where('id', $prev_shift_res->site_id)->first();
//                 $site_name = !empty($site) ? $site->site_name : 'N/A';          //changed by moiz. changed to site name instead of site address.
//                     // $prev_shift='';
//                 $prev_shift = [
//                     'guard_id' => $prev_shift_res->guard_id,
//                     'temp_date' => Date("d-m-Y", strtotime($prev_shift_res->start)),
//                     'start' => Date("H:i", strtotime($prev_shift_res->start)),
//                     'end' => Date("H:i", strtotime($prev_shift_res->end)),
//                     'site' => $site_name,
//                     'job_time_end' => ($prev_shift_res->end != '' && $prev_shift_res->end != null && $prev_shift_res->end > 0) ? date('Y-m-d H:i', strtotime($prev_shift_res->end)) : date("Y-m-d H:i", strtotime($prev_shift_res->end))
//                 ];
//                 if ($prev_shift_res->end != '' && $prev_shift_res->end != null && $prev_shift_res->end > 0) {
//                     $seconds = strtotime(date('Y-m-d H:i:s')) - strtotime($prev_shift_res->end);
//                 } else {
//                     $seconds = strtotime(date('Y-m-d H:i:s')) - strtotime($prev_shift_res->end);
//                 }
//                 $hours = $seconds / (60 * 60);
//                 $guard->previous_shift_diff = $hours;

//                 if ($hours < 8 && $max_hours > 12) {
//                     $is_available = false;
//                 }
//             } else {
//                 $prev_shift = [];
//             }

//             $next_shift_res =  DB::table('job_rosters')->where('guard_id', $guard->id)->where('start', '>', date('Y-m-d H:i:s'))->orderBy('start', 'asc')->first();
//             if (!empty($next_shift_res)) {

//                 $site = DB::table('sites')->where('id', $next_shift_res->site_id)->first();
//                 $site_name = !empty($site) ? $site->address : 'N/A';
//                 $next_shift = [
//                     'guard_id' => $next_shift_res->guard_id,
//                     'date' => Date("d-m-Y", strtotime($next_shift_res->start)),
//                     'start' => Date("H:i", strtotime($next_shift_res->start)),
//                     'end' => Date("H:i", strtotime($next_shift_res->end)),
//                     'site' => $site_name,
//                 ];
//                 $guard->next_shift_diff = $hours;

//                 if ($hours < 8 && $max_hours > 12) {
//                     $is_available = false;
//                 }
//             } else {
//                 $next_shift = [];
//             }
//             $guard->next_shift = $next_shift;
//             $guard->prev_shift = $prev_shift;
//             if ($is_available) {
//                 $available_gaurds[] = $guard;
//             }
//         }
//         return response()->json(['guards' => $available_gaurds]);
//      }


public function getAllAavailableGuards(){

    $start_time = date('Y-m-d 00:00');
    $end_time = date('Y-m-d 23:59');

    //$active_shifts = JobRoster::where('start', '<=', $start)->where('end', '>=', $end)->select('guard_id')->get();
    $active_shifts = JobRoster::where(function ($que) use ($start_time, $end_time) {
        $que->orWhere(function ($que1) use ($start_time, $end_time) {
            // temp start is grater then actual start and less then actual end
            $que1->where('start', '<=', $start_time)->where('end', '>=', $start_time);
            $que1->whereNotNull('guard_id');
        });
        $que->orWhere(function ($que1) use ($start_time, $end_time) {
            
            // temp end is b/w actual start and end..
            $que1->where('start', '<=', $end_time)->where('end', '>=', $end_time);
            $que1->whereNotNull('guard_id');
        });
        $que->orWhere(function ($que1) use ($start_time, $end_time) {
            
            // is any shift lie b/w temp shift
            $que1->where('start', '>=', $start_time)->where('end', '<=', $end_time);
            $que1->whereNotNull('guard_id');
        });
    })->select('guard_id')->where('deleted_at', null)->get();
    $available_guards = Guard::whereNotIn('id', $active_shifts)->where('guard_status', 'active')->where('is_available', 'yes')->where('admin_approval_status', 'active')->select('id', 'first_name', 'middle_name', 'last_name', 'phone', 'profile_image')->get();
    foreach ($available_guards as $guard) {
        $hours = '';
        $max_hours = 0;
        $guard->working_hours = $max_hours;
        $is_available = true;
        $prev_shift_res =  DB::table('job_rosters')->where('guard_id', $guard->id)->where('end', '<', date('Y-m-d H:i:s'))->where('deleted_at', null)->orderBy('start', 'desc')->first();
        if (!empty($prev_shift_res)) {

            $site = DB::table('sites')->where('id', $prev_shift_res->site_id)->first();
            $site_name = !empty($site) ? $site->site_name : 'N/A';          //changed by moiz. changed to site name instead of site address.
                // $prev_shift='';
            $prev_shift = [
                'guard_id' => $prev_shift_res->guard_id,
                'temp_date' => Date("d-m-Y", strtotime($prev_shift_res->start)),
                'start' => Date("H:i", strtotime($prev_shift_res->start)),
                'end' => Date("H:i", strtotime($prev_shift_res->end)),
                'site' => $site_name,
                'job_time_end' => ($prev_shift_res->end != '' && $prev_shift_res->end != null && $prev_shift_res->end > 0) ? date('Y-m-d H:i', strtotime($prev_shift_res->end)) : date("Y-m-d H:i", strtotime($prev_shift_res->end))
            ];
            if ($prev_shift_res->end != '' && $prev_shift_res->end != null && $prev_shift_res->end > 0) {
                $seconds = strtotime(date('Y-m-d H:i:s')) - strtotime($prev_shift_res->end);
            } else {
                $seconds = strtotime(date('Y-m-d H:i:s')) - strtotime($prev_shift_res->end);
            }
            $hours = $seconds / (60 * 60);
            $guard->previous_shift_diff = $hours;

            if ($hours < 8 && $max_hours > 12) {
                $is_available = false;
            }
        } else {
            $prev_shift = [];
        }

        $next_shift_res =  DB::table('job_rosters')->where('guard_id', $guard->id)->where('start', '>', date('Y-m-d H:i:s'))->where('deleted_at', null)->orderBy('start', 'asc')->first();
        if (!empty($next_shift_res)) {

            $site = DB::table('sites')->where('id', $next_shift_res->site_id)->first();
            $site_name = !empty($site) ? $site->site_name : 'N/A';
            $next_shift = [
                'guard_id' => $next_shift_res->guard_id,
                'date' => Date("d-m-Y", strtotime($next_shift_res->start)),
                'start' => Date("H:i", strtotime($next_shift_res->start)),
                'end' => Date("H:i", strtotime($next_shift_res->end)),
                'site' => $site_name,
            ];
            $guard->next_shift_diff = $hours;

            if ($hours < 8 && $max_hours > 12) {
                $is_available = false;
            }
        } else {
            $next_shift = [];
        }
        $guard->next_shift = $next_shift;
        $guard->prev_shift = $prev_shift;
        if ($is_available) {
            $available_gaurds[] = $guard;
        }
    }
    $avail = AvailableGuardResource::collection($available_guards);
    return response()->json(['code' => 200, 'success' => true, 'data' => $avail]);

}

public function sendUncoverdShiftMailToAdmins()
{
    $start = Carbon::now()->startOfWeek()->toDateString(); 
    $start = date('Y-m-d 00:00', strtotime($start));

    $end = Carbon::now()->endOfWeek()->toDateString();
    $end = date('Y-m-d 23:59', strtotime($end));

    // $config_dbs = DB::connection('mysql2')->table('business_data')->get();

    // foreach ($config_dbs as $db) {
        // $connectionConfig['driver'] = 'mysql';
        // $connectionConfig['host'] = env('DB_HOST');
        // $connectionConfig['database'] = $db->database_name;
        // $connectionConfig['username'] = env('DB_USERNAME');
        // $connectionConfig['password'] = env('DB_PASSWORD');
        
        // // Create a new database connection dynamically
        // $newConnection = 'mysql';
        // config(['database.connections.' . $newConnection => $connectionConfig]);

        // Perform the query with the new connection
        $shifts = DB::table('job_rosters')
            ->where('start', '>=', $start)
            ->where('start', '<=', $end)
            ->where(function ($q) {
                $q->whereNull('guard_id');
                $q->orWhere('guard_id', '=', '');
                $q->where('shift_type', '!=', 'template');
            })
            ->get();

        // Fetch related data for each shift using a separate query
        $shifts = $this->fetchRelatedData($shifts);

        $records = CurrentWeekUnCoverdShiftResource::collection($shifts);
        $admins = DB::table('users')
            ->where('is_super_admin', 0)
            ->where('status', 'active')
            ->get();

        if (count($shifts) > 0) {
            foreach ($admins as $key => $admin) {
                sendUncoverdShiftToAdmin($shifts, $admin->email);
            }
        }

        // Disconnect from the dynamic connection
    //     DB::disconnect($newConnection);
    // }

    return response()->json(['code' => 200, 'success' => true, 'msg' => "Email sent"]);
}

// Fetch related data for the shifts
private function fetchRelatedData($shifts)
{
    // Perform a query to fetch related data for each shift
    foreach ($shifts as $shift) {
        $shift->site = DB::table('sites')
            ->where('id', $shift->site_id)
            ->first();
    }

    return $shifts;
}
    public function getAdminShiftActivity(Request $request){
        $mainArr = [];
        $activites = JobRosterAction::where(['roster_id'=>$request->roster_id])->with('Admin')->get();
        foreach ($activites as $key => $value) {
            $adminUser = User::find($value['action_by']);
            if($adminUser && $adminUser->id == $request->admin_id){
                $mainArr[] = $value;
            }
        }
        return response()->json([
            'success' => true,
            'data' => $mainArr
        ]);
    }
    public function deleteAdminShiftActivity($id){
        $activity = JobRosterAction::findOrFail($id)->delete();
        if($activity){
            return response()->json([
                'success' => true,
                'data' => 'Activity Deleted'
            ]);
        }else{
            return response()->json([
                'success' => true,
                'data' => 'Activity not existed or deleted before please try again after some time.'
            ]);
        }
    }
    public function saveAdminShiftActivity(Request $request){
        if(!isset($request->id) || $request->id == null || $request->id == 0){
            $jobRosterActions = new JobRosterAction();
        }else{
            $jobRosterActions = JobRosterAction::find($request->id);
        }
        $jobRosterActions->action_by = $request->admin_id;
        $jobRosterActions->action_type = 'admin_shift_activity'; 
        $jobRosterActions->roster_id =  $request->jobroster_id;
        $jobRosterActions->reason = $request->reason;
        $jobRosterActions->data = null;
        $jobRosterActions->updated_colums = null;
        $jobRosterActions->action_on = 'job_roster';
        $jobRosterActions->save();
        // jobRosterActions($request->admin_id, 'admin_shift_activity', $request->jobroster_id,$old_data = null,$updated_colum = null, $request->reason);
        return response()->json([
            'success' => true,
            'message' => 'Data save successfully.'
        ]); 
    }
    public function updateShfitTask(Request $request){
        $task = JobRosterTask::find($request->id);
        if($task){
            $task->start_time = $request->actual_start_time;
            $task->end_time = $request->actual_end_time;
            $task->note = $request->note;
            $task->update();
            return response()->json([
                'success' => true,
                'message' => 'Task Updated!'
            ]);
        }else{
            return response()->json([
                'success' => false,
                'message' => 'Task not found!'
            ]);
        }
    }
    public function updateIncidentReport(Request $request){
        $incidentReport = DB::table('incident_reports')->where('id', $request->id)->first();
        if($incidentReport){
            DB::table('incident_reports')->where('id', $request->id)->update([
                'injury_detail' => $request->injury_detail
            ]);
            return response()->json([
                'success' => true,
                'message' => 'Incident Report Updated!'
            ]);
        }else{
            return response()->json([
                'success' => false,
                'message' => 'Incident Report Not Found!'
            ]);
        }
    }
    public function updateFootPatrolReport(Request $request){
        $footPatrolReport = DB::table('foot_patrol_reports')->where('id', $request->id)->first();
        if($footPatrolReport){
            DB::table('foot_patrol_reports')->where('id', $request->id)->update([
                'patrolling_detail'=> $request->patrolling_detail
            ]);
            return response()->json([
                'success' => true,
                'message' => 'Foot Patrol Report Updated!'
            ]);
        }else{
            return response()->json([
                'success' => false,
                'message' => 'Foot Patrol Report Not Found!'
            ]);
        }
    }

    public function accessRoster(Request $request){
        $roster = DB::table('job_rosters')
            ->where('id', $request->roster_id)
            ->whereIn('user_id', $request->user_id)
            ->first();
    
        if (!$roster) {
            return response()->json([
                'success' => false,
                'message' => "Sorry, you don't have permission to access this roster!"
            ]);
        } 
    }
    public function getRosterDeletedShifts(Request $request){
        $query = JobRoster::query();
        if(isset($request->admin_id)){
            $query->whereIn('deleted_by', $request->admin_id);
        }
        if(isset($request->start) && isset($request->end)){
            $start = $request->start.' 00:00';
            $end = $request->end.' 23:59';
            $query->where('start', '>=', $start)->where('start', '<=', $end);
        }
        $deletedShifts = $query->onlyTrashed()->where('roster_id', $request->roster_id)->get();
        // $createdShifts = $query->where(['roster_id', $request->roster_id])->get();
        $deletedShiftsRes = RosterDeletedShifts::collection($deletedShifts);
        return response()->json([
            'success' => true,
            'deletedShifts' => $deletedShiftsRes
        ]);

    }

    function getrosterhoursum(Request $request)
    {
    
        if (isset($request['date']) && $request['date'] != '') {
            $dateRange = explode(' - ', $request['date']);
        
            $from = strtotime(str_replace('/', '-', $dateRange[0]));
            $to = strtotime(str_replace('/', '-', $dateRange[1])) + 86399; // Adding seconds to make it end of the day
        
            $startDate = date('Y-m-d H:i', $from);
            $endDate = date('Y-m-d H:i', $to);
        } else {
            $to = time();
            $from = time() - (60 * 60 * 24 * 14);
        
            $startDate = date('Y-m-d H:i', $from);
            $endDate = date('Y-m-d H:i', $to);
        }
       

    $extra_query = '(jr.`job_status` = "completed" OR jr.`job_status` = "pending" OR jr.`job_status` = "confirmed") AND ';

        if (!empty($request['customer_id'])) {
            $customerConditions = "j.`customer_id` = '".$request['customer_id']."' AND ";
            $extra_query .= $customerConditions;
        }

    $subquery = DB::table('job_roster_actions AS jra_sub')
        ->select('jra_sub.roster_id', DB::raw('MAX(jra_sub.created_at) AS latest_created_at'))
        ->groupBy('jra_sub.roster_id');

    $subqueryReason = DB::table('job_roster_actions AS jra_sub2')
        ->select('jra_sub2.roster_id', 'jra_sub2.reason')
        ->joinSub($subquery, 'latest_jra_sub', function ($join) {
            $join->on('jra_sub2.roster_id', '=', 'latest_jra_sub.roster_id')
                ->on('jra_sub2.created_at', '=', 'latest_jra_sub.latest_created_at');
        });

    $results = DB::table('job_rosters AS jr')
        ->select(
            'jr.*',
            'j.id',
            'j.booking_id',
            'j.customer_id',
            'j.contractor_id',
            'j.po_wo as site_po_wo',
            'j.state',
            'j.address',
            'j.site_name',
            'j.site_description',
            'j.level',
            'j.payrol',
            'j.site_payrate',
            'j.break_payable',
            'j.break',
            'cust.name AS customer_name',
            'c.name AS contractor_name',
            'g.phone',
            'g.first_name',
            'g.last_name',
            'g.guard_type',
            'ja.signin_time AS signin_time',
            'ja.signout_time AS signout_time',
            'g.guard_postion AS position',
            'jr.id AS id'
        )
        ->join('sites AS j', 'j.id', '=', 'jr.site_id')
        ->leftJoin('job_roster_activites AS ja', 'ja.job_roster_id', '=', 'jr.id')
        ->join('guard_work_details AS gw', 'gw.guard_id', '=', 'jr.guard_id')
        ->leftJoin('guards AS g', 'g.id', '=', 'jr.guard_id')
        ->leftJoin('customers AS cust', 'j.customer_id', '=', 'cust.id')
        ->leftJoin('contractors AS c', 'j.contractor_id', '=', 'c.id')
        ->leftJoinSub($subqueryReason, 'latest_jra_sub', 'latest_jra_sub.roster_id', '=', 'jr.id')
        ->whereRaw($extra_query . '(jr.job_status = ? OR jr.in_paysheet = ?) AND jr.shift_payable = ? AND jr.start BETWEEN ? AND ?',
        ['completed', 1, 'yes', $startDate, $endDate])
        ->orderBy('g.first_name', 'ASC')
        ->orderBy('g.last_name')
        ->orderBy('jr.start')
        ->whereNull('jr.deleted_at')
        ->get();

        $results = json_decode(json_encode($results), true);

        foreach ($results as $key => $roster) {
        $roster['day_rate'] = 0;
        $roster['night_rate'] = 0;
        $roster['public_holiday_rate'] = 0;
        $roster['saturday_rate'] = 0;
        $roster['sunday_rate'] = 0;
        $roster['total_amount'] = 0;
        $roster['ot'] = 0;

        // Add your custom rate logic here based on conditions and update $roster accordingly
        if ($roster['custome_rate'] > 0 && !empty($roster['custome_rate'])) {
            if ($roster['custome_payrate'] > 0  && !empty($roster['custome_payrate'])) {

                $roster['day_rate'] = json_decode($roster->manualPayRate)->payrate_mon_to_fri_day_rate;
                $roster['night_rate'] = json_decode($roster->manualPayRate)->payrate_mon_to_fri_night_rate;
                $roster['public_holiday_rate'] = json_decode($roster->manualPayRate)->payrate_pub_holi_day_rate;
                $roster['saturday_rate'] = json_decode($roster->manualPayRate)->payrate_sun_day_rate;
                $roster['sunday_rate'] = json_decode($roster->manualPayRate)->payrate_sun_day_rate;
            } else {
                // Custom rate logic for custom_rate without custom_payrate
                $payrate = Payrate::where('id', $roster['payrate'])->first();
                if ($payrate) {
                    $roster['day_rate'] = $payrate->def_metro_mon_to_fri_day_rate;
                    $roster['night_rate'] = $payrate->def_metro_mon_to_fri_night_rate;
                    $roster['public_holiday_rate'] = $payrate->def_metro_pub_holi_day_rate;
                    $roster['saturday_rate'] = $payrate->def_metro_sat_day_rate;
                    $roster['sunday_rate'] = $payrate->def_metro_sun_day_rate;
                }
            }
        } else {
            // Default rate logic when neither custom_rate nor custom_payrate is true

            $site = Site::where('id', $roster['site_id'])->first();
                $payrate = Payrate::where('id', $site->site_payrate)->where('status', 'active')->first();
                if (!empty($payrate)) {
                    if ($site->type == 'metro') {
                        if ($roster['payrol'] == 'award') {
                            $roster['day_rate'] = $payrate->award_metro_mon_to_fri_day_rate;
                            $roster['night_rate'] = $payrate->award_metro_mon_to_fri_night_rate;
                            $roster['public_holiday_rate'] = $payrate->award_metro_pub_holi_day_rate;
                            $roster['saturday_rate'] = $payrate->award_metro_sat_day_rate;
                            $roster['sunday_rate'] = $payrate->award_metro_sun_day_rate;
                        } elseif ($roster['payrol'] == 'eba') {
                            $roster['day_rate'] = $payrate->eba_metro_mon_to_fri_day_rate;
                            $roster['night_rate'] = $payrate->eba_metro_mon_to_fri_night_rate;
                            $roster['public_holiday_rate'] = $payrate->eba_metro_pub_holi_day_rate;
                            $roster['saturday_rate'] = $payrate->eba_metro_sat_day_rate;
                            $roster['sunday_rate'] = $payrate->eba_metro_sun_day_rate;
                        } else {
                            $roster['day_rate'] = $payrate->def_metro_mon_to_fri_day_rate;
                            $roster['night_rate'] = $payrate->def_metro_mon_to_fri_night_rate;
                            $roster['public_holiday_rate'] = $payrate->def_metro_pub_holi_day_rate;
                            $roster['saturday_rate'] = $payrate->def_metro_sat_day_rate;
                            $roster['sunday_rate'] = $payrate->def_metro_sun_day_rate;
                        }
                    } else {
                        if ($roster['payrol'] == 'award') {
                            $roster['day_rate'] = $payrate->award_reg_mon_to_fri_day_rate;
                            $roster['night_rate'] = $payrate->award_reg_mon_to_fri_night_rate;
                            $roster['public_holiday_rate'] = $payrate->award_reg_pub_holi_day_rate;
                            $roster['saturday_rate'] = $payrate->award_reg_sat_day_rate;
                            $roster['sunday_rate'] = $payrate->award_reg_sun_day_rate;
                        } elseif ($roster['payrol'] == 'eba') {
                            $roster['day_rate'] = $payrate->eba_reg_mon_to_fri_day_rate;
                            $roster['night_rate'] = $payrate->eba_reg_mon_to_fri_night_rate;
                            $roster['public_holiday_rate'] = $payrate->eba_reg_pub_holi_day_rate;
                            $roster['saturday_rate'] = $payrate->eba_reg_sat_day_rate;
                            $roster['sunday_rate'] = $payrate->eba_reg_sun_day_rate;
                        } else {
                            $roster['day_rate'] = $payrate->def_reg_mon_to_fri_day_rate;
                            $roster['night_rate'] = $payrate->def_reg_mon_to_fri_night_rate;
                            $roster['public_holiday_rate'] = $payrate->def_reg_pub_holi_day_rate;
                            $roster['saturday_rate'] = $payrate->def_reg_sat_day_rate;
                            $roster['sunday_rate'] = $payrate->def_reg_sun_day_rate;
                        }
                    }
                }else {
                // Default rate logic for guard rate
                $guard_payrate = GuardWorkDetail::where('guard_id', $roster['guard_id'])->value('payrate');
                if (!empty($guard_payrate)) {
                    $payrate = Payrate::where('id', $guard_payrate)->where('status', 'active')->first();
                    if ($payrate) {
                        if ($roster['payrol'] == 'award') {
                            $roster['day_rate'] = $payrate->award_metro_mon_to_fri_day_rate;
                            $roster['night_rate'] = $payrate->award_metro_mon_to_fri_night_rate;
                            $roster['public_holiday_rate'] = $payrate->award_metro_pub_holi_day_rate;
                            $roster['saturday_rate'] = $payrate->award_metro_sat_day_rate;
                            $roster['sunday_rate'] = $payrate->award_metro_sun_day_rate;
                        } elseif ($roster['payrol'] == 'eba') {
                            $roster['day_rate'] = $payrate->eba_metro_mon_to_fri_day_rate;
                            $roster['night_rate'] = $payrate->eba_metro_mon_to_fri_night_rate;
                            $roster['public_holiday_rate'] = $payrate->eba_metro_pub_holi_day_rate;
                            $roster['saturday_rate'] = $payrate->eba_metro_sat_day_rate;
                            $roster['sunday_rate'] = $payrate->eba_metro_sun_day_rate;
                        } else {
                            $roster['day_rate'] = $payrate->def_metro_mon_to_fri_day_rate;
                            $roster['night_rate'] = $payrate->def_metro_mon_to_fri_night_rate;
                            $roster['public_holiday_rate'] = $payrate->def_metro_pub_holi_day_rate;
                            $roster['saturday_rate'] = $payrate->def_metro_sat_day_rate;
                            $roster['sunday_rate'] = $payrate->def_metro_sun_day_rate;
                        }
                    }
                }
            }
            
        }

        $shift_hours = $roster['morning_hours'] + $roster['night_hours'] +  $roster['saturday_morning_hours'] + $roster['saturday_night_hours'] + $roster['sunday_morning_hours'] + $roster['sunday_night_hours'] + $roster['ph_morning_hours'] + $roster['ph_night_hours'];

        
        $roster['total_amount'] = ($roster['day_rate'] * $roster['morning_hours']) + ($roster['night_rate'] * $roster['night_hours']) + ($roster['public_holiday_rate'] * ($roster['ph_morning_hours'] + $roster['ph_night_hours'])) + ($roster['saturday_rate'] * ($roster['saturday_morning_hours'] + $roster['saturday_night_hours'])) + ($roster['sunday_rate'] * ($roster['sunday_morning_hours'] + $roster['sunday_night_hours']));
        if($roster['continuation'] == 0 && $roster['hours'] < 4){
            $extraHours = 4 - $roster['hours'];
            $extraAmount = $extraHours * $roster['day_rate'];
            $roster['total_amount']  =  $roster['total_amount'] + $extraAmount;  
            $roster['hours'] = 4;
        }
        $results[$key] = $roster;
    }


    if($request->type == "normal"){
        $total_hours = [
            '0' => [
                'name' => 'M-F Morning Hours',
                'hours' => 0,
                'payrate' => 0,
                'totalpay' => 0,
            ],
            '1' => [
                'name' => 'M-F Night Hours',
                'hours' => 0,
                'payrate' => 0,
                'totalpay' => 0,
            ],
            '2' => [
                'name' => 'Saturday Hours',
                'hours' => 0,
                'payrate' => 0,
                'totalpay' => 0,
            ],
            '3' => [
                'name' => 'Sunday Hours',
                'hours' => 0,
                'payrate' => 0,
                'totalpay' => 0,
            ],
            '4' => [
                'name' => 'Public Holiday Hours',
                'hours' => 0,
                'payrate' => 0,
                'totalpay' => 0,
            ],
        ];

        foreach ($results as $shift) {
            $total_hours['0']['hours'] += $shift['morning_hours'];
            $total_hours['0']['payrate'] = $shift['day_rate'];
            $total_hours['0']['totalpay'] = $total_hours['0']['hours'] * $total_hours['0']['payrate'];
            $total_hours['0']['site_po_wo'] = $shift['site_po_wo'];
            $total_hours['0']['po_wo'] = $shift['po_wo'];

            $total_hours['1']['hours'] += $shift['night_hours'];
            $total_hours['1']['payrate'] = $shift['night_rate'];
            $total_hours['1']['totalpay'] = $total_hours['1']['hours'] * $total_hours['1']['payrate'];
            $total_hours['1']['site_po_wo'] = $shift['site_po_wo'];
            $total_hours['1']['po_wo'] = $shift['po_wo'];

            $total_hours['2']['hours'] += $shift['saturday_morning_hours'] + $shift['saturday_night_hours'];
            $total_hours['2']['payrate'] = $shift['saturday_rate'];
            $total_hours['2']['totalpay'] = $total_hours['2']['hours'] * $total_hours['2']['payrate']; 
            $total_hours['2']['site_po_wo'] = $shift['site_po_wo'];
            $total_hours['2']['po_wo'] = $shift['po_wo'];

            $total_hours['3']['hours'] += $shift['sunday_morning_hours'] + $shift['sunday_night_hours'];
            $total_hours['3']['payrate'] = $shift['sunday_rate'];
            $total_hours['3']['totalpay'] = $total_hours['3']['hours'] * $total_hours['3']['payrate']; 
            $total_hours['3']['site_po_wo'] = $shift['site_po_wo'];
            $total_hours['3']['po_wo'] = $shift['po_wo'];

            $total_hours['4']['hours'] += $shift['ph_morning_hours'] + $shift['ph_night_hours'];
            $total_hours['4']['payrate'] = $shift['public_holiday_rate'];
            $total_hours['4']['totalpay'] = $total_hours['4']['hours'] * $total_hours['4']['payrate']; 
            $total_hours['4']['site_po_wo'] = $shift['site_po_wo'];
            $total_hours['4']['po_wo'] = $shift['po_wo']; 
        }

        if ($total_hours) {
            return response()->json([
                'success' => true,
                'code' => 200,
                'data' => $total_hours
            ]);
        }else{
            return response()->json([
                'success' => false,
                'code' => 200,
                'data' => $total_hours
            ]);
        }

    }elseif($request->type == "summariz"){
        
        $resultArray = [];

        $desiredFields = ['id', 'first_name', 'po_wo', 'site_po_wo', 'last_name', 'start', 'end', 'state', 'morning_hours',
        'night_hours', 'ph_morning_hours', 'ph_night_hours', 'saturday_morning_hours', 'saturday_morning_hours', 
        'sunday_morning_hours', 'sunday_night_hours', 'day_rate', 'night_rate', 'public_holiday_rate',
        'saturday_rate', 'sunday_rate',];

        if (is_array($results)) {
           
            foreach ($results as $record) {
                
                $start_date = Carbon::parse($record['start']);
                $end_date = Carbon::parse($record['end']);

                $public_holidays = ['2029-05-10', '2029-05-15'];
            
                $morning_shift_start = $start_date->copy()->setTime(6, 0, 0);
                $morning_shift_end = $start_date->copy()->setTime(18, 0, 0);
                
                $night_shift_start = $start_date->copy()->setTime(18, 0, 0);
                $night_shift_end = $night_shift_start->copy()->setTime(6, 0, 0);
                
                if ($night_shift_end->lt($night_shift_start)) {
                    $night_shift_end->addDay();
                }
                
                $morning_shift_times = [];
                $night_shift_times = [];
                $saturday_morning = [];
                $saturday_night = [];
                $sunday_morning = [];
                $sunday_night = [];
                $ph_morning = [];
                $ph_night = [];
                
                if (in_array($start_date->toDateString(), $public_holidays)) {
                    // Implement logic for public holiday
                    // Add shift times to $ph_morning and $ph_night arrays
                    $ph_morning[] = [$morning_shift_start->format('H:i'), $morning_shift_end->format('H:i')];
                    $ph_night[] = [$night_shift_start->format('H:i'), $night_shift_end->format('H:i')];
                } else {
            
                    if ($start_date->isFriday() && $end_date->isSaturday()) {
                    
                        if ($start_date->lt($morning_shift_end) && $end_date->gt($morning_shift_start)) {
                            $morning_start = max($start_date, $morning_shift_start);
                            $morning_end = min($end_date, $morning_shift_end);
                            
                            $morning_shift_times[] = [$morning_start->format('H:i'), $morning_end->format('H:i')];
                        }
        
                        if ($start_date->lt($night_shift_end) && $end_date->gt($night_shift_start)) {
                            $night_start = max($start_date, $night_shift_start);
                            $night_end = min($end_date, $night_shift_end);
                    
                            if ($night_end->lt($night_start)) {
                                $night_shift_times[] = [$night_start->format('H:i'), '23:59'];
                                $night_shift_times[] = ['00:01', $night_end->format('H:i')];
                            } else {
                                $night_shift_times[] = [$night_start->format('H:i'), '23:59'];
                            }
                        }
                        if ($start_date->lt($night_shift_end) && $end_date->gt($night_shift_start)) {
                            $saturday_night[] = ['00:01', '06:00'];
                            $saturday_morning[] = ['06:01', $end_date->format('H:i')];
                        }
                    } elseif ($start_date->isSaturday() && $end_date->isSunday()) {
                        // Check for Saturday morning shift
                        if ($start_date->lt($morning_shift_end) && $end_date->gt($morning_shift_start)) {
                            $morning_start = max($start_date, $morning_shift_start);
                            $morning_end = min($end_date, $morning_shift_end);
                            
                            $saturday_morning[] = [$morning_start->format('H:i'), $morning_end->format('H:i')];
                        }
                    
                        // Check for Saturday night shift
                        if ($start_date->lt($night_shift_end) && $end_date->gt($night_shift_start)) {
                            $night_start = max($start_date, $night_shift_start);
                            $night_end = min($end_date, $night_shift_end);
                    
                            if ($night_end->lt($night_start)) {
                                // Shift spans across midnight
                                $saturday_night[] = [$night_start->format('H:i'), '23:59'];
                                $sunday_night[] = ['00:01', $night_end->format('H:i')];
                            } else {
                                // Shift does not span across midnight
                                $saturday_night[] = [$night_start->format('H:i'), '23:59'];
                                $sunday_night[] = ['00:01', $night_end->format('H:i')];
                            }
                        }
                    
                        // Check for Sunday morning shift
                        if ($start_date->gt($morning_shift_start) && $end_date->gte($morning_shift_end)) {
                            $sunday_morning[] = [
                                $morning_shift_start->copy()->addMinute()->format('H:i'),
                                $end_date->format('H:i')
                            ];
                        }
                    }            
                    elseif ($start_date->isSunday() && $end_date->isMonday()) {
        
                        if ($start_date->lt($morning_shift_end) && $end_date->gt($morning_shift_start)) {
                            $morning_start = max($start_date, $morning_shift_start);
                            $morning_end = min($end_date, $morning_shift_end);
                            
                            $sunday_morning[] = [$morning_start->format('H:i'), $morning_end->format('H:i')];
                        }
                        
                        if ($start_date->lt($night_shift_end) && $end_date->gt($night_shift_start)) {
                            $night_start = max($start_date, $night_shift_start);
                            $night_end = min($end_date, $night_shift_end);
                
                            if ($night_end->lt($night_start)) {
                                $sunday_night[] = [$night_start->format('H:i'), '23:59'];
                                $night_shift_times[] = ['00:01', $night_end->format('H:i')];
                            } elseif($night_end->gt($night_start)){
                                $sunday_night[] = [$night_start->format('H:i'), '23:59'];
                                $night_shift_times[] = ['00:01', $night_end->format('H:i')];
                            } else {
                                $sunday_night[] = [$night_start->format('H:i'), '23:59'];
                                $night_shift_times[] = ['00:01', $end_date->format('H:i')];
                            }
                        }
                        if ($start_date->gt($morning_shift_start) && $end_date->gte($morning_shift_end)) {
                            $morning_shift_times[] = [
                                $morning_shift_start->copy()->addMinute()->format('H:i'),
                                $end_date->format('H:i')
                            ];
                        }
                        
                    }
                    elseif ($start_date->isSaturday() && $end_date->isSaturday() &&
                    $start_date->lte($morning_shift_end) && $end_date->gte($morning_shift_start)) {
                    if ($start_date->lt($night_shift_end) && $end_date->gt($night_shift_start)) {
                        $night_start = max($start_date, $night_shift_start);
                        $night_end = min($end_date, $night_shift_end);
                        $saturday_night[] = [$night_start->format('H:i'), $night_end->format('H:i')];
                    }
                    if ($start_date->lt($morning_shift_end) && $end_date->gt($morning_shift_start)) {
                        $morning_start = max($start_date, $morning_shift_start);
                        $morning_end = min($end_date, $morning_shift_end);
                        $saturday_morning[] = [$morning_start->format('H:i'), $morning_end->format('H:i')];
                    }
                    if ($start_date->lt($morning_shift_start)) {
                        $saturday_night[] = [$start_date->format('H:i'), '06:00'];
                    }
                }elseif ($start_date->isSunday() && $end_date->isSunday()) {
                    if ($start_date->lt($night_shift_end) && $end_date->gt($night_shift_start)) {
                        $night_start = max($start_date, $night_shift_start);
                        $night_end = min($end_date, $night_shift_end);
                        $sunday_night[] = [$night_start->format('H:i'), $night_end->format('H:i')];
                    }
                    if ($start_date->lt($morning_shift_end) && $end_date->gt($morning_shift_start)) {
                        $morning_start = max($start_date, $morning_shift_start);
                        $morning_end = min($end_date, $morning_shift_end);
                        $sunday_morning[] = [$morning_start->format('H:i'), $morning_end->format('H:i')];
                    }
                    if ($start_date->lt($morning_shift_start)) {
                        $sunday_night[] = [$start_date->format('H:i'), '06:00'];
                    }
                    } else {
                        if ($start_date->lt($morning_shift_end) && $end_date->gt($morning_shift_start)) {
                            $morning_shift_times[] = [
                                max($start_date, $morning_shift_start)->format('H:i'),
                                min($end_date, $morning_shift_end)->format('H:i')
                            ];
                        }
        
                        if ($start_date->lt($night_shift_end) && $end_date->gt($night_shift_start)) {
                            $night_shift_times[] = [
                                max($start_date, $night_shift_start)->format('H:i'),
                                min($end_date, $night_shift_end)->format('H:i')
                            ];
        
                            if($start_date->hour > $end_date->hour)
                            {
                                if ($end_date->gt($morning_shift_start)) {
                                    $morning_shift_times[] = [
                                        max($morning_shift_start, $night_shift_end)->format('H:i'),
                                        $end_date->format('H:i')
                                    ];
                                }
                            }
                        }
                    }
                }
                  
                if ($record['morning_hours'] > 0) {
                    $filteredData = array_intersect_key($record, array_flip($desiredFields));
                    $filteredData['hour_type'] = 'Morning Hours';
                    $filteredData['morning_hours'] = $record['morning_hours'];
                    $filteredData['morning_time'] = $morning_shift_times;

                    unset(
                        $filteredData['night_hours'],
                        $filteredData['night_rate'],
                        $filteredData['saturday_morning_hours'],
                        $filteredData['saturday_night_hours'],
                        $filteredData['saturday_rate'],
                        $filteredData['sunday_morning_hours'],
                        $filteredData['sunday_night_hours'],
                        $filteredData['sunday_rate'],
                        $filteredData['ph_morning_hours'],
                        $filteredData['ph_night_hours'],
                        $filteredData['public_holiday_rate'],
                    );
                    $resultArray[] = $filteredData;
                }
            
                if ($record['night_hours'] > 0) {
                    $filteredData = array_intersect_key($record, array_flip($desiredFields));
                    $filteredData['hour_type'] = 'Night Hours';
                    $filteredData['night_hours'] = $record['night_hours'];
                    $filteredData['night_time'] = $night_shift_times;

                    unset(
                        $filteredData['morning_hours'],
                        $filteredData['day_rate'],
                        $filteredData['saturday_morning_hours'],
                        $filteredData['saturday_night_hours'],
                        $filteredData['saturday_rate'],
                        $filteredData['sunday_morning_hours'],
                        $filteredData['sunday_night_hours'],
                        $filteredData['sunday_rate'],
                        $filteredData['ph_morning_hours'],
                        $filteredData['ph_night_hours'],
                        $filteredData['public_holiday_rate'],
                    );
                    $resultArray[] = $filteredData;
                }
                
                if ($record['saturday_morning_hours'] > 0) {
                    $filteredData = array_intersect_key($record, array_flip($desiredFields));
                    $filteredData['hour_type'] = 'Saturday Morning Hours';
                    $filteredData['saturday_morning_hours'] = $record['saturday_morning_hours'];
                    $filteredData['saturday_morning'] = $saturday_morning;

                    unset(
                        $filteredData['morning_hours'],
                        $filteredData['day_rate'],
                        $filteredData['night_hours'],
                        $filteredData['night_rate'],
                        $filteredData['saturday_night_hours'],
                        $filteredData['sunday_morning_hours'],
                        $filteredData['sunday_night_hours'],
                        $filteredData['sunday_rate'],
                        $filteredData['ph_morning_hours'],
                        $filteredData['ph_night_hours'],
                        $filteredData['public_holiday_rate'],
                    );
                    $resultArray[] = $filteredData;
                }

                if ($record['saturday_night_hours'] > 0) {
                    $filteredData = array_intersect_key($record, array_flip($desiredFields));
                    $filteredData['hour_type'] = 'Saturday Night Hours';
                    $filteredData['saturday_night_hours'] = $record['saturday_night_hours'];
                    $filteredData['saturday_night'] = $saturday_night;
                    unset(
                        $filteredData['morning_hours'],
                        $filteredData['day_rate'],
                        $filteredData['night_hours'],
                        $filteredData['night_rate'],
                        $filteredData['saturday_morning_hours'],
                        $filteredData['sunday_morning_hours'],
                        $filteredData['sunday_night_hours'],
                        $filteredData['sunday_rate'],
                        $filteredData['ph_morning_hours'],
                        $filteredData['ph_night_hours'],
                        $filteredData['public_holiday_rate'],
                    );
                    $resultArray[] = $filteredData;
                }

                if ($record['sunday_morning_hours'] > 0) {
                    $filteredData = array_intersect_key($record, array_flip($desiredFields));
                    $filteredData['hour_type'] = 'Sunday Morning Hours';
                    $filteredData['sunday_morning_hours'] = $record['sunday_morning_hours'];
                    $filteredData['sunday_morning'] = $sunday_morning;
                    unset(
                        $filteredData['morning_hours'],
                        $filteredData['day_rate'],
                        $filteredData['night_hours'],
                        $filteredData['night_rate'],
                        $filteredData['saturday_morning_hours'],
                        $filteredData['saturday_night_hours'],
                        $filteredData['saturday_rate'],
                        $filteredData['sunday_night_hours'],
                        $filteredData['ph_morning_hours'],
                        $filteredData['ph_night_hours'],
                        $filteredData['public_holiday_rate'],
                    );
                    $resultArray[] = $filteredData;
                }

                if ($record['sunday_night_hours'] > 0) {
                    $filteredData = array_intersect_key($record, array_flip($desiredFields));
                    $filteredData['hour_type'] = 'Sunday Night Hours';
                    $filteredData['sunday_night_hours'] = $record['sunday_night_hours'];
                    $filteredData['sunday_night'] = $sunday_night;

                    unset(
                        $filteredData['morning_hours'],
                        $filteredData['day_rate'],
                        $filteredData['night_hours'],
                        $filteredData['night_rate'],
                        $filteredData['saturday_morning_hours'],
                        $filteredData['saturday_night_hours'],
                        $filteredData['saturday_rate'],
                        $filteredData['sunday_morning_hours'],
                        $filteredData['ph_morning_hours'],
                        $filteredData['ph_night_hours'],
                        $filteredData['public_holiday_rate'],
                    );
                    $resultArray[] = $filteredData;
                }

                if ($record['ph_morning_hours'] > 0) {
                    $filteredData = array_intersect_key($record, array_flip($desiredFields));
                    $filteredData['hour_type'] = 'Public Holiday Morning Hours';
                    $filteredData['ph_morning_hours'] = $record['ph_morning_hours'];
                    $filteredData['ph_morning'] = $ph_morning;
                    unset(
                        $filteredData['morning_hours'],
                        $filteredData['day_rate'],
                        $filteredData['night_hours'],
                        $filteredData['night_rate'],
                        $filteredData['saturday_morning_hours'],
                        $filteredData['saturday_night_hours'],
                        $filteredData['saturday_rate'],
                        $filteredData['sunday_morning_hours'],
                        $filteredData['sunday_night_hours'],
                        $filteredData['sunday_rate'],
                        $filteredData['ph_night_hours'],
                    );
                    $resultArray[] = $filteredData;
                }
        
                if ($record['ph_night_hours'] > 0) {
                    $filteredData = array_intersect_key($record, array_flip($desiredFields));
                    $filteredData['hour_type'] = 'Public Holiday Night Hours';
                    $filteredData['ph_night_hours'] = $record['ph_night_hours'];
                    $filteredData['ph_night'] = $ph_night;
                    unset(
                        $filteredData['morning_hours'],
                        $filteredData['day_rate'],
                        $filteredData['night_hours'],
                        $filteredData['night_rate'],
                        $filteredData['saturday_morning_hours'],
                        $filteredData['saturday_night_hours'],
                        $filteredData['saturday_rate'],
                        $filteredData['sunday_morning_hours'],
                        $filteredData['sunday_night_hours'],
                        $filteredData['sunday_rate'],
                        $filteredData['ph_morning_hours'],
                    );
                    $resultArray[] = $filteredData;
                }
            }

        }

        if ($resultArray) {
            return response()->json([
                'success' => true,
                'code' => 200,
                'data' => $resultArray
            ]);
        }else{
            return response()->json([
                'success' => false,
                'code' => 200,
                'data' => $resultArray
            ]);
        }

    }
    
}

function timeToMinutes($time) {
    $timeArr = explode(':', $time);
    return ($timeArr[0] * 60) + $timeArr[1];
}

function downloadInvoiceForm(Request $request)
{

    if($request->type == "normal"){
 
        $invoice_rates = $request->rates;
        $invoice_from  = $request->invoice_from;
        $invoice_to    = $request->invoice_to;
        $payment_type  = $request->payment_type;
        $payment_type  = $request->payment_type;
        $payment_method  = $request->paymentMethod;
        
        $currentDate = Carbon::now()->format('d-M-Y');
        $customer = Customer::where('id', $request->customer_id)->first();

        $data = [
            'GST'            => $request->gst,
            'date'           => $currentDate,
            'due_date'       => $request->due_Date,
            'invoice_no'     => $request->invoice_num,
            'notes'          => $request->notes,
            'type'           => $request->type,
            'payment_type'   => $payment_type,
            'payment_method' => $payment_method,
            'email'          => $customer->email,
            'rates'          => $invoice_rates,
            'invoice_to'     => $invoice_to,
            'invoice_from'   => $invoice_from,
        ];

        $html = view('customer_normal_invoice', compact('data'));
        
        $dompdf = new Dompdf();
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        $output = $dompdf->output();
        $public_path = public_path();
        $public_path = str_replace('247StaffingSolution/public/', '', $public_path);
        $folder ='/invoice_normal_form';
        $path = $public_path.$folder;
        $file_name = time() . 'invoice_normal_form.pdf';
        $result = file_put_contents($path.'/'.$file_name, $output);
        $name = $file_name;   

    }

    return response()->json(['success' =>  true, 'message' => 'Normal Invoice generate successfully.','path' => 'https://'.request()->getHttpHost().'/invoice_normal_form/'.$name]);

}

function downloadShiftActivity(Request $request)
{
    $getShiftActivity =  \DB::table('roster_complete_activity')->where('roster_id', $request->roster_id)
        ->select('id', 'activity', 'type', 'activity_time')->get();

        if($getShiftActivity){

            $roster = JobRoster::where('id', $request->roster_id)->first();
            if($roster){
                $staff = !empty($roster->guard_id) ? getGuardName($roster->guard_id) : null;
                $location = !empty($roster->site_id) ? getSiteName($roster->site_id) : null;
                $shift_start = !empty($roster->start) ? usaToAusDateTime($roster->start) : '';
                $shift_end = !empty($roster->end) ? usaToAusDateTime($roster->end) : '';
                $customer_id = '';
                if(!empty($roster->site_id)){
                        $s = Site::where('id', $roster->site_id)->first();
                        $customer_id = $s->customer_id;
                    }
                    
                $customer = !empty($customer_id) ? getCustomerName($customer_id) : null;
            }

            $html = view('download_shift_activity', [
                'datas' => $getShiftActivity,
                'staff' => $staff,
                'location' => $location,
                'customer' => $customer,
                'shift_start' => $shift_start,
                'shift_end' => $shift_end,
                'generated_date' => now()->format('F d, Y'),
            ]);
        
        $dompdf = new Dompdf();
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        $output = $dompdf->output();
        $public_path = public_path();
        $public_path = str_replace('247StaffingSolution/public/', '', $public_path);
        $folder ='/invoice_normal_form';
        $path = $public_path.$folder;
        $file_name = time() . 'invoice_normal_form.pdf';
        $result = file_put_contents($path.'/'.$file_name, $output);
        $name = $file_name;

    }

    return response()->json(['success' =>  true, 'message' => 'Normal Invoice generate successfully.','path' => 'https://'.request()->getHttpHost().'/invoice_normal_form/'.$name]);

}

public function todayrostersites(Request $request)
{   
    $start = Carbon::today()->format('Y-m-d 00:00');
    $end = Carbon::today()->format('Y-m-d 23:59');

    $todayShifts = JobRoster::where('job_rosters.start', '>=', $start)
    ->where('job_rosters.start', '<=', $end)
    ->join('guards', 'guards.id', '=', 'job_rosters.guard_id')
    ->join('sites', 'sites.id', '=', 'job_rosters.site_id')
    ->select('job_rosters.id', 'guards.id as guard_id', 'job_rosters.start', 'job_rosters.end', 'sites.site_name', 'guards.first_name', 'guards.middle_name', 'guards.last_name')
    // ->groupBy('job_rosters.guard_id')
    ->get();

    if ($todayShifts) {
        return response()->json([
            'success' => true,
            'code' => 200,
            'data' => $todayShifts
        ]);
    }else{
        return response()->json([
            'success' => false,
            'code' => 200,
            'data' => $todayShifts
        ]);
    }
      
}  

   public function customerinvoicestore(Request $request)
   {
        if($request->type == "normal"){

        $invoice                = new CustomerInvoice();
        $invoice->customer_id   = $request->customer_id;
        $invoice->admin_id      = $request->admin_id;
        $invoice->invoice_from  = json_encode($request->invoice_from, true);
        $invoice->invoice_to    = json_encode($request->invoice_to, true);
        $invoice->invoice_rates = json_encode($request->rates, true);
        $invoice->payment_type  = json_encode($request->payment_type, true);
        $invoice->due_Date      = $request->due_date;
        $invoice->payment_method = $request->paymentMethod;
        $invoice->invoice_no    = $request->invoice_num;
        $invoice->gst           = $request->gst;
        $invoice->type          = $request->type;
        $invoice->currency      = $request->currency;
        $invoice->late_fee      = $request->late_fee;
        $invoice->notes         = $request->notes;
        $invoice->save();

        $customer    = Customer::where('id', $request->customer_id)->first();

        $invoice_rates = $request->rates;
        $invoice_from  = $request->invoice_from;
        $invoice_to    = $request->invoice_to;
        $payment_type    = $request->payment_type;
        
        $data = [
            'GST'            => $request->gst,
            'date'           => $invoice->created_at,
            'due_date'       => $invoice->due_date,
            'invoice_no'     => $request->invoice_num,
            'notes'          => $request->notes,
            'type'           => $request->type,
            'payment_method' => $request->paymentMethod,
            'payment_type'   => $payment_type,
            'email'          => $customer->email,
            'records'        => $invoice_rates,
            'invoice_to'     => $invoice_to,
            'invoice_from'   => $invoice_from,
        ];
        

        Mail::send('mail.SendInvoiceEmail', $data, function($mail) use ($data){
            $mail->from('no-reply@thescouts.com.au', 'AMG Security');
            $mail->to($data['email'])->subject("Customer Invoice Created");
        });

        return response()->json([ 'success' => true, 'code' => 200 ]);

        }elseif($request->type == "summariz")
        
        {
                if (isset($request['date']) && $request['date'] != '') {
                    $dateRange = explode(' - ', $request['date']);
                
                    $from = strtotime(str_replace('/', '-', $dateRange[0]));
                    $to = strtotime(str_replace('/', '-', $dateRange[1])) + 86399; // Adding seconds to make it end of the day
                
                    $startDate = date('Y-m-d H:i', $from);
                    $endDate = date('Y-m-d H:i', $to);
                } else {
                    $to = time();
                    $from = time() - (60 * 60 * 24 * 14);
                
                    $startDate = date('Y-m-d H:i', $from);
                    $endDate = date('Y-m-d H:i', $to);
                }
            
        
            $extra_query = '(jr.`job_status` = "completed" OR jr.`job_status` = "pending" OR jr.`job_status` = "confirmed") AND ';
        
                if (!empty($request['customer_id'])) {
                    $customerConditions = "j.`customer_id` = '".$request['customer_id']."' AND ";
                    $extra_query .= $customerConditions;
                }
        
            $subquery = DB::table('job_roster_actions AS jra_sub')
                ->select('jra_sub.roster_id', DB::raw('MAX(jra_sub.created_at) AS latest_created_at'))
                ->groupBy('jra_sub.roster_id');
        
            $subqueryReason = DB::table('job_roster_actions AS jra_sub2')
                ->select('jra_sub2.roster_id', 'jra_sub2.reason')
                ->joinSub($subquery, 'latest_jra_sub', function ($join) {
                    $join->on('jra_sub2.roster_id', '=', 'latest_jra_sub.roster_id')
                        ->on('jra_sub2.created_at', '=', 'latest_jra_sub.latest_created_at');
                });
        
            $results = DB::table('job_rosters AS jr')
                ->select(
                    'jr.*',
                    'j.id',
                    'j.booking_id',
                    'j.customer_id',
                    'j.contractor_id',
                    'j.state',
                    'j.address',
                    'j.site_name',
                    'j.site_description',
                    'j.level',
                    'j.payrol',
                    'j.site_payrate',
                    'j.break_payable',
                    'j.break',
                    'cust.name AS customer_name',
                    'c.name AS contractor_name',
                    'g.phone',
                    'g.first_name',
                    'g.last_name',
                    'g.guard_type',
                    'ja.signin_time AS signin_time',
                    'ja.signout_time AS signout_time',
                    'g.guard_postion AS position',
                    'jr.id AS id'
                )
                ->join('sites AS j', 'j.id', '=', 'jr.site_id')
                ->leftJoin('job_roster_activites AS ja', 'ja.job_roster_id', '=', 'jr.id')
                ->join('guard_work_details AS gw', 'gw.guard_id', '=', 'jr.guard_id')
                ->leftJoin('guards AS g', 'g.id', '=', 'jr.guard_id')
                ->leftJoin('customers AS cust', 'j.customer_id', '=', 'cust.id')
                ->leftJoin('contractors AS c', 'j.contractor_id', '=', 'c.id')
                ->leftJoinSub($subqueryReason, 'latest_jra_sub', 'latest_jra_sub.roster_id', '=', 'jr.id')
                ->whereRaw($extra_query . '(jr.job_status = ? OR jr.in_paysheet = ?) AND jr.shift_payable = ? AND jr.start BETWEEN ? AND ?',
                ['completed', 1, 'yes', $startDate, $endDate])
                ->orderBy('g.first_name', 'ASC')
                ->orderBy('g.last_name')
                ->orderBy('jr.start')
                ->whereNull('jr.deleted_at')
                ->get();
        
                $results = json_decode(json_encode($results), true);
        
                foreach ($results as $key => $roster) {
                $roster['day_rate'] = 0;
                $roster['night_rate'] = 0;
                $roster['public_holiday_rate'] = 0;
                $roster['saturday_rate'] = 0;
                $roster['sunday_rate'] = 0;
                $roster['total_amount'] = 0;
                $roster['ot'] = 0;
        
                // Add your custom rate logic here based on conditions and update $roster accordingly
                if ($roster['custome_rate'] > 0 && !empty($roster['custome_rate'])) {
                    if ($roster['custome_payrate'] > 0  && !empty($roster['custome_payrate'])) {
        
                        $roster['day_rate'] = json_decode($roster->manualPayRate)->payrate_mon_to_fri_day_rate;
                        $roster['night_rate'] = json_decode($roster->manualPayRate)->payrate_mon_to_fri_night_rate;
                        $roster['public_holiday_rate'] = json_decode($roster->manualPayRate)->payrate_pub_holi_day_rate;
                        $roster['saturday_rate'] = json_decode($roster->manualPayRate)->payrate_sun_day_rate;
                        $roster['sunday_rate'] = json_decode($roster->manualPayRate)->payrate_sun_day_rate;
                    } else {
                        $payrate = Payrate::where('id', $roster['payrate'])->first();
                        if ($payrate) {
                            $roster['day_rate'] = $payrate->def_metro_mon_to_fri_day_rate;
                            $roster['night_rate'] = $payrate->def_metro_mon_to_fri_night_rate;
                            $roster['public_holiday_rate'] = $payrate->def_metro_pub_holi_day_rate;
                            $roster['saturday_rate'] = $payrate->def_metro_sat_day_rate;
                            $roster['sunday_rate'] = $payrate->def_metro_sun_day_rate;
                        }
                    }
                } else {
                    // Default rate logic when neither custom_rate nor custom_payrate is true
        
                    $site = Site::where('id', $roster['site_id'])->first();
                        $payrate = Payrate::where('id', $site->site_payrate)->where('status', 'active')->first();
                        if (!empty($payrate)) {
                            if ($site->type == 'metro') {
                                if ($roster['payrol'] == 'award') {
                                    $roster['day_rate'] = $payrate->award_metro_mon_to_fri_day_rate;
                                    $roster['night_rate'] = $payrate->award_metro_mon_to_fri_night_rate;
                                    $roster['public_holiday_rate'] = $payrate->award_metro_pub_holi_day_rate;
                                    $roster['saturday_rate'] = $payrate->award_metro_sat_day_rate;
                                    $roster['sunday_rate'] = $payrate->award_metro_sun_day_rate;
                                } elseif ($roster['payrol'] == 'eba') {
                                    $roster['day_rate'] = $payrate->eba_metro_mon_to_fri_day_rate;
                                    $roster['night_rate'] = $payrate->eba_metro_mon_to_fri_night_rate;
                                    $roster['public_holiday_rate'] = $payrate->eba_metro_pub_holi_day_rate;
                                    $roster['saturday_rate'] = $payrate->eba_metro_sat_day_rate;
                                    $roster['sunday_rate'] = $payrate->eba_metro_sun_day_rate;
                                } else {
                                    $roster['day_rate'] = $payrate->def_metro_mon_to_fri_day_rate;
                                    $roster['night_rate'] = $payrate->def_metro_mon_to_fri_night_rate;
                                    $roster['public_holiday_rate'] = $payrate->def_metro_pub_holi_day_rate;
                                    $roster['saturday_rate'] = $payrate->def_metro_sat_day_rate;
                                    $roster['sunday_rate'] = $payrate->def_metro_sun_day_rate;
                                }
                            } else {
                                if ($roster['payrol'] == 'award') {
                                    $roster['day_rate'] = $payrate->award_reg_mon_to_fri_day_rate;
                                    $roster['night_rate'] = $payrate->award_reg_mon_to_fri_night_rate;
                                    $roster['public_holiday_rate'] = $payrate->award_reg_pub_holi_day_rate;
                                    $roster['saturday_rate'] = $payrate->award_reg_sat_day_rate;
                                    $roster['sunday_rate'] = $payrate->award_reg_sun_day_rate;
                                } elseif ($roster['payrol'] == 'eba') {
                                    $roster['day_rate'] = $payrate->eba_reg_mon_to_fri_day_rate;
                                    $roster['night_rate'] = $payrate->eba_reg_mon_to_fri_night_rate;
                                    $roster['public_holiday_rate'] = $payrate->eba_reg_pub_holi_day_rate;
                                    $roster['saturday_rate'] = $payrate->eba_reg_sat_day_rate;
                                    $roster['sunday_rate'] = $payrate->eba_reg_sun_day_rate;
                                } else {
                                    $roster['day_rate'] = $payrate->def_reg_mon_to_fri_day_rate;
                                    $roster['night_rate'] = $payrate->def_reg_mon_to_fri_night_rate;
                                    $roster['public_holiday_rate'] = $payrate->def_reg_pub_holi_day_rate;
                                    $roster['saturday_rate'] = $payrate->def_reg_sat_day_rate;
                                    $roster['sunday_rate'] = $payrate->def_reg_sun_day_rate;
                                }
                            }
                        }else {
                        // Default rate logic for guard rate
                        $guard_payrate = GuardWorkDetail::where('guard_id', $roster['guard_id'])->value('payrate');
                        if (!empty($guard_payrate)) {
                            $payrate = Payrate::where('id', $guard_payrate)->where('status', 'active')->first();
                            if ($payrate) {
                                if ($roster['payrol'] == 'award') {
                                    $roster['day_rate'] = $payrate->award_metro_mon_to_fri_day_rate;
                                    $roster['night_rate'] = $payrate->award_metro_mon_to_fri_night_rate;
                                    $roster['public_holiday_rate'] = $payrate->award_metro_pub_holi_day_rate;
                                    $roster['saturday_rate'] = $payrate->award_metro_sat_day_rate;
                                    $roster['sunday_rate'] = $payrate->award_metro_sun_day_rate;
                                } elseif ($roster['payrol'] == 'eba') {
                                    $roster['day_rate'] = $payrate->eba_metro_mon_to_fri_day_rate;
                                    $roster['night_rate'] = $payrate->eba_metro_mon_to_fri_night_rate;
                                    $roster['public_holiday_rate'] = $payrate->eba_metro_pub_holi_day_rate;
                                    $roster['saturday_rate'] = $payrate->eba_metro_sat_day_rate;
                                    $roster['sunday_rate'] = $payrate->eba_metro_sun_day_rate;
                                } else {
                                    $roster['day_rate'] = $payrate->def_metro_mon_to_fri_day_rate;
                                    $roster['night_rate'] = $payrate->def_metro_mon_to_fri_night_rate;
                                    $roster['public_holiday_rate'] = $payrate->def_metro_pub_holi_day_rate;
                                    $roster['saturday_rate'] = $payrate->def_metro_sat_day_rate;
                                    $roster['sunday_rate'] = $payrate->def_metro_sun_day_rate;
                                }
                            }
                        }
                    }
                    
                }
        
                $shift_hours = $roster['morning_hours'] + $roster['night_hours'] +  $roster['saturday_morning_hours'] + $roster['saturday_night_hours'] + $roster['sunday_morning_hours'] + $roster['sunday_night_hours'] + $roster['ph_morning_hours'] + $roster['ph_night_hours'];
        
                
                $roster['total_amount'] = ($roster['day_rate'] * $roster['morning_hours']) + ($roster['night_rate'] * $roster['night_hours']) + ($roster['public_holiday_rate'] * ($roster['ph_morning_hours'] + $roster['ph_night_hours'])) + ($roster['saturday_rate'] * ($roster['saturday_morning_hours'] + $roster['saturday_night_hours'])) + ($roster['sunday_rate'] * ($roster['sunday_morning_hours'] + $roster['sunday_night_hours']));
                if($roster['continuation'] == 0 && $roster['hours'] < 4){
                    $extraHours = 4 - $roster['hours'];
                    $extraAmount = $extraHours * $roster['day_rate'];
                    $roster['total_amount']  =  $roster['total_amount'] + $extraAmount;  
                    $roster['hours'] = 4;
                }
                $results[$key] = $roster;
            }

            $shift_record = [];

            $desiredFields = ['id', 'first_name', 'last_name', 'start', 'end', 'state', 'morning_hours',
            'night_hours', 'ph_morning_hours', 'ph_night_hours', 'saturday_morning_hours', 'saturday_morning_hours', 
            'sunday_morning_hours', 'sunday_night_hours', 'day_rate', 'night_rate', 'public_holiday_rate',
            'saturday_rate', 'sunday_rate',];
    
            if (is_array($results)) {
    
                foreach ($results as $record) {
        
                    if ($record['morning_hours'] > 0) {
                        $get_shift = array_intersect_key($record, array_flip($desiredFields));
                        $get_shift['hour_type'] = 'Morning Hours';
                        $get_shift['hours'] = $record['morning_hours'];
                        $get_shift['rate'] = $record['day_rate'];
                        unset(
                            $get_shift['morning_hours'],
                            $get_shift['day_rate'],
                            $get_shift['night_hours'],
                            $get_shift['night_rate'],
                            $get_shift['saturday_morning_hours'],
                            $get_shift['saturday_night_hours'],
                            $get_shift['saturday_rate'],
                            $get_shift['sunday_morning_hours'],
                            $get_shift['sunday_night_hours'],
                            $get_shift['sunday_rate'],
                            $get_shift['ph_morning_hours'],
                            $get_shift['ph_night_hours'],
                            $get_shift['public_holiday_rate'],
                        );
                        $shift_record[] = $get_shift;
                    }
                
                    if ($record['night_hours'] > 0) {
                        $get_shift = array_intersect_key($record, array_flip($desiredFields));
                        $get_shift['hour_type'] = 'Night Hours';
                        $get_shift['hours'] = $record['night_hours'];
                        $get_shift['rate'] = $record['night_rate'];
                        unset(
                            $get_shift['night_hours'],
                            $get_shift['night_rate'],
                            $get_shift['morning_hours'],
                            $get_shift['day_rate'],
                            $get_shift['saturday_morning_hours'],
                            $get_shift['saturday_night_hours'],
                            $get_shift['saturday_rate'],
                            $get_shift['sunday_morning_hours'],
                            $get_shift['sunday_night_hours'],
                            $get_shift['sunday_rate'],
                            $get_shift['ph_morning_hours'],
                            $get_shift['ph_night_hours'],
                            $get_shift['public_holiday_rate'],
                        );
                        $shift_record[] = $get_shift;
                    }
                    
                    if ($record['saturday_morning_hours'] > 0) {
                        $get_shift = array_intersect_key($record, array_flip($desiredFields));
                        $get_shift['hour_type'] = 'Saturday Morning Hours';
                        $get_shift['hours'] = $record['saturday_morning_hours'];
                        $get_shift['rate'] = $record['saturday_rate'];
                        unset(
                            $get_shift['saturday_morning_hours'],
                            $get_shift['saturday_rate'],
                            $get_shift['morning_hours'],
                            $get_shift['day_rate'],
                            $get_shift['night_hours'],
                            $get_shift['night_rate'],
                            $get_shift['saturday_night_hours'],
                            $get_shift['sunday_morning_hours'],
                            $get_shift['sunday_night_hours'],
                            $get_shift['sunday_rate'],
                            $get_shift['ph_morning_hours'],
                            $get_shift['ph_night_hours'],
                            $get_shift['public_holiday_rate'],
                        );
                        $shift_record[] = $get_shift;
                    }
    
                    if ($record['saturday_night_hours'] > 0) {
                        $get_shift = array_intersect_key($record, array_flip($desiredFields));
                        $get_shift['hour_type'] = 'Saturday Night Hours';
                        $get_shift['hours'] = $record['saturday_night_hours'];
                        $get_shift['rate'] = $record['saturday_rate'];
                        unset(
                            $get_shift['saturday_night_hours'],
                            $get_shift['saturday_rate'],
                            $get_shift['morning_hours'],
                            $get_shift['day_rate'],
                            $get_shift['night_hours'],
                            $get_shift['night_rate'],
                            $get_shift['saturday_morning_hours'],
                            $get_shift['sunday_morning_hours'],
                            $get_shift['sunday_night_hours'],
                            $get_shift['sunday_rate'],
                            $get_shift['ph_morning_hours'],
                            $get_shift['ph_night_hours'],
                            $get_shift['public_holiday_rate'],
                        );
                        $shift_record[] = $get_shift;
                    }
    
                    if ($record['sunday_morning_hours'] > 0) {
                        $get_shift = array_intersect_key($record, array_flip($desiredFields));
                        $get_shift['hour_type'] = 'Sunday Morning Hours';
                        $get_shift['hours'] = $record['sunday_morning_hours'];
                        $get_shift['rate'] = $record['sunday_rate'];
                        unset(
                            $get_shift['sunday_morning_hours'],
                            $get_shift['sunday_rate'],
                            $get_shift['morning_hours'],
                            $get_shift['day_rate'],
                            $get_shift['night_hours'],
                            $get_shift['night_rate'],
                            $get_shift['saturday_morning_hours'],
                            $get_shift['saturday_night_hours'],
                            $get_shift['saturday_rate'],
                            $get_shift['sunday_night_hours'],
                            $get_shift['ph_morning_hours'],
                            $get_shift['ph_night_hours'],
                            $get_shift['public_holiday_rate'],
                        );
                        $shift_record[] = $get_shift;
                    }
    
                    if ($record['sunday_night_hours'] > 0) {
                        $get_shift = array_intersect_key($record, array_flip($desiredFields));
                        $get_shift['hour_type'] = 'Sunday Night Hours';
                        $get_shift['hours'] = $record['sunday_night_hours'];
                        $get_shift['rate'] = $record['sunday_rate'];
                        unset(
                            $get_shift['sunday_night_hours'],
                            $get_shift['sunday_rate'],
                            $get_shift['morning_hours'],
                            $get_shift['day_rate'],
                            $get_shift['night_hours'],
                            $get_shift['night_rate'],
                            $get_shift['saturday_morning_hours'],
                            $get_shift['saturday_night_hours'],
                            $get_shift['saturday_rate'],
                            $get_shift['sunday_morning_hours'],
                            $get_shift['ph_morning_hours'],
                            $get_shift['ph_night_hours'],
                            $get_shift['public_holiday_rate'],
                        );
                        $shift_record[] = $get_shift;
                    }
    
                    if ($record['ph_morning_hours'] > 0) {
                        $get_shift = array_intersect_key($record, array_flip($desiredFields));
                        $get_shift['hour_type'] = 'Public Holiday Morning Hours';
                        $get_shift['hours'] = $record['ph_morning_hours'];
                        $get_shift['rate'] = $record['public_holiday_rate'];
                        unset(
                            $get_shift['ph_morning_hours'],
                            $get_shift['public_holiday_rate'],
                            $get_shift['morning_hours'],
                            $get_shift['day_rate'],
                            $get_shift['night_hours'],
                            $get_shift['night_rate'],
                            $get_shift['saturday_morning_hours'],
                            $get_shift['saturday_night_hours'],
                            $get_shift['saturday_rate'],
                            $get_shift['sunday_morning_hours'],
                            $get_shift['sunday_night_hours'],
                            $get_shift['sunday_rate'],
                            $get_shift['ph_night_hours'],
                        );
                        $shift_record[] = $get_shift;
                    }
            
                    if ($record['ph_night_hours'] > 0) {
                        $get_shift = array_intersect_key($record, array_flip($desiredFields));
                        $get_shift['hour_type'] = 'Public Holiday Night Hours';
                        $get_shift['hours'] = $record['ph_night_hours'];
                        $get_shift['rate'] = $record['public_holiday_rate'];
                        unset(
                            $get_shift['ph_night_hours'],
                            $get_shift['public_holiday_rate'],
                            $get_shift['morning_hours'],
                            $get_shift['day_rate'],
                            $get_shift['night_hours'],
                            $get_shift['night_rate'],
                            $get_shift['saturday_morning_hours'],
                            $get_shift['saturday_night_hours'],
                            $get_shift['saturday_rate'],
                            $get_shift['sunday_morning_hours'],
                            $get_shift['sunday_night_hours'],
                            $get_shift['sunday_rate'],
                            $get_shift['ph_morning_hours'],
                        );
                        $shift_record[] = $get_shift;
                    }
                }

                if(!empty($shift_record))
                {
                    $invoice               = new CustomerInvoice();
                    $invoice->customer_id  = $request->customer_id;
                    $invoice->admin_id     = $request->admin_id;
                    $invoice->invoice_no   = $request->invoice_num;
                    $invoice->gst          = $request->gst;
                    $invoice->invoice_from = json_encode($request->invoice_from, true);
                    $invoice->invoice_to   = json_encode($request->invoice_to, true);
                    $invoice->invoice_rates = json_encode($shift_record, true);
                    $invoice->payment_type = json_encode($request->payment_type, true);
                    $invoice->due_Date = $request->due_date;
                    $invoice->payment_method = $request->paymentMethod;
                    $invoice->type         = $request->type;
                    $invoice->currency     = $request->currency;
                    $invoice->late_fee     = $request->late_fee;
                    $invoice->notes        = $request->notes;
                    $invoice->save();
            
                    $customer = Customer::where('id', $request->customer_id)->first();
      
                    //uncommit these lines for live server
                    $invoice_from = $request->invoice_from;
                    $invoice_to = $request->invoice_to;
                    $payment_type = $request->payment_type;
                    
                    $data = [
                        'GST' => $request->gst,
                        'date' => $invoice->created_at,
                        'due_date' => $invoice->due_date,
                        'invoice_no' => $request->invoice_num,
                        'notes' => $request->notes,
                        'type' => $request->type,
                        'email' => $customer->email,
                        'records' => $shift_record,
                        'payment_type' => $payment_type,
                        'payment_method' => $request->paymentMethod,
                        'invoice_to' => $invoice_to,
                        'invoice_from' => $invoice_from,
                    ];
                    
                    Mail::send('mail.SendInvoiceEmail', $data, function($mail) use ($data){
                        $mail->from('no-reply@thescouts.com.au', 'AMG Security');
                        $mail->to($data['email'])->subject("Customer Invoice Created");
                    });
            
                    return response()->json([ 'success' => true, 'code' => 200 ]);
                    
                }else{
                    return response()->json(['success' => true, 'msg' => 'Record Not Found.']);
                }
    
            }
        }
    }
    public function startPatrolling(Request $request, $id){
        $sites = DB::table('site_qr_codes')->where('site_id', $request->site_id)->get();
        $patrolReport = new PatrollingReport();
        $patrolReport->site_id = $request->site_id;
        $patrolReport->roster_id = $id;
        $patrolReport->coordinates = $request->coordinates;
        $patrolReport->status = 'start';
        $patrolReport->guard_id = $request->guard_id;
        $patrolReport->scanner_count = count($sites);
        $patrolReport->save();
        foreach($sites as $site){
            $qrScanners = new QrScanner();
            $qrScanners->patrolling_report_id = $patrolReport->id; 
            $qrScanners->name = $site->name; 
            $qrScanners->value = $site->unique_key; 
            $qrScanners->status = 'incomplete'; 
            $qrScanners->save();
        }
        return response()->json([
            'success' => true,
            'scanners' => QrScanner::where('patrolling_report_id', $patrolReport->id)->get()
        ]);
    }  
    public function scanQR(Request $request, $id)
    {
        // $patrolReport = QrScanner::find($id);
        if(isset($id)){

            $sites = DB::table('site_qr_codes')->where('unique_key', $id)->first();
  
            $patrolReport = new QrScanner();
            $patrolReport->status = 'completed';
            $patrolReport->scan_at = Carbon::now()->format('Y-m-d H:i:s');
            $patrolReport->coordinates = null;
            $patrolReport->value = $sites->unique_key;
            $patrolReport->name = $sites->name;

            $patrolReport->save();
            // $scannedCount = QrScanner::where(['patrolling_report_id'=> $patrolReport->patrolling_report_id, 'status'=>'completed'])->count();
            // $getPatrolling = PatrollingReport::find($patrolReport->patrolling_report_id);
            // if($getPatrolling && $scannedCount == $getPatrolling->scanner_count){
            //     $getPatrolling->status = 'end';
            //     $getPatrolling->update();
            // }
            if($patrolReport){
                return response()->json([
                    'success' => true,
                    'message' => 'QR Code scanned successfully'
                ]);
            }
        }else{
            return response()->json([
                'success' => false,
                'message' => 'Invalid'
            ]);
        }
    }

    public function scanQRApp(Request $request, $id)
    {
        $patrolReport = QrScanner::find($id);
        if(isset($request->qr_code) && $request->qr_code != '' && ($request->qr_code == $patrolReport->value)){
            $patrolReport->status = 'completed';
            $patrolReport->scan_at = Carbon::now()->format('Y-m-d H:i:s');
            $patrolReport->coordinates = $request->coordinates;
            $patrolReport->update();
            $scannedCount = QrScanner::where(['patrolling_report_id'=> $patrolReport->patrolling_report_id, 'status'=>'completed'])->count();
            $getPatrolling = PatrollingReport::find($patrolReport->patrolling_report_id);
            if($getPatrolling && $scannedCount == $getPatrolling->scanner_count){
                $getPatrolling->status = 'end';
                $getPatrolling->update();
            }
            if($patrolReport){
                return response()->json([
                    'success' => true,
                    'message' => 'QR Code scanned successfully'
                ]);
            }
        }else{
            return response()->json([
                'success' => false,
                'message' => 'Invalid'
            ]);
        }
    }

    public function getScannerHistory(Request $request, $id){
        $history = PatrollingReport::where('roster_id', $id)->with('scanners')->get();
        return response()->json([
            'history' => $history,
            'success' => true
        ]);
    }

    function publishRosterNew(Request $request)
    {
        $email = false;
        $push = false;
        $publishShiftcount = 0;
        $days = array('Sun' => false, 'Mon' => false, 'Tue' => false, 'Wed' => false, 'Thu' => false, 'Fri' => false, 'Sat' => false);
        if ($request->has('selected_days') && !empty($request->selected_days)) {
            foreach ($request->selected_days as $key => $n) {
                $days[$n['value']] = true;
            }
        }
        if ($request->has('notification') && !empty($request->notification)) {
            foreach ($request->notification as $key => $n) {
                if ($n['value'] == 'push') {
                    $push = true;
                }
                if ($n['value'] == 'email') {
                    $email = true;
                }
            }
        }
            // end foreach notification
        foreach ($request->customer_ids as $key => $cId) {
            $data = $this->getAllPublishData($request->type, $cId, $request->publish_selected_ids, $request->calendarStart, $request->calendarEnd);
            $guards = $this->getAllPublishGuards($request->type, $cId, $request->publish_selected_ids, $request->calendarStart, $request->calendarEnd);
                // $updatedGuards = $this->guard_model->getAllUpdatedPublishGuards($request->type, $cId, $request->publish_selected_ids, $request->calendarStart, $request->calendarEnd);


            if ($push == true) {
                foreach ($guards as $key => $g) {
                    if ($g->notification_token != '') {
                        $message = 'Schedule for week starting ' . date('d M Y', strtotime($request->calendarStart));
                        $notification_data['guards'][0] = array(
                            'guard_id' => $g->id,
                            'notification_token' => $g->notification_token
                        );
                        $notification_data['message'] = $message;
                        $notification_data['title'] = 'New Roster Published';
                        $notification_data['page'] = 'homepage';
                        $res = $this->send_push_notification($notification_data);
                    }
                }
                // print_r($guards);
                // exit();
            }
            foreach ($data as $key => $value) {
                $day = date('D', strtotime($value->start));
                if ($days[$day] == true) {
                        //send mail notification
                    $this->updatePublishStatus($value->id);

                    if ($value->guard_id != null || $value->guard_id != 0 && $email == true) {
                        $guard = Guard::where('id', $value->guard_id)->first();
                        $site = Site::where(array('id' => $value->site_id))->first();
                        $tasks = DB::table('job_roster_tasks')->where('job_roster_id', $value->guard_id)->first();
                        if (!empty($tasks)) {
                            $task_status = "Yes";
                        } else {
                            $task_status = "No";
                        }
                        $message = '<div style="font-family:Arial,Helvetica,sans-serif; line-height: 1.5; font-weight: normal; font-size: 15px; color: #2F3044; min-height: 100%; margin:0; padding:0; width:100%; background-color:#edf2f7">
                        <br><table align="center" border="0" cellpadding="0" cellspacing="0" width="100%" style="border-collapse:collapse;margin:0 auto; padding:0; max-width:600px">
                        <tbody><tr><td align="center" valign="center" style="text-align:center; padding: 40px"><a href="' . config('custom.logo') . '" rel="noopener" target="_blank"><img src="' . config('custom.logo') . '" style="height: 45px" alt="logo"></a></td></tr><tr>
                        <td align="left" valign="center">
                        <div style="text-align:left; margin: 0 20px; padding: 40px; background-color:#ffffff; border-radius: 6px"><!--begin:Email content--><div style="padding-bottom: 30px; font-size: 17px;"><strong>New roster published</strong></div>';
                        $message .= '<div style="padding-bottom: 30px">Your new Roster from the duration of ' . date('d-m-Y', strtotime('monday this week')) . ' to ' . date('d-m-Y', strtotime('sunday this week')) . ' is below: </div>
                        <div style="padding-bottom: 40px; text-align:center;">';
                        $req['name'] = $guard->name;
                        $req['email'] = $guard->email;
                        $req['subject'] = 'New roster published';
                        $req['subject'] = 'Your Schedule for week starting ' . date('d M Y', strtotime('monday this week'));
                        $message .= '<br><b>Shift Details </b><br>';
                        $message .= '<table style="width:100%;margin-top: 6px;" border="1px"><tr style="background-color:#ececed"><th style="padding:5px;border:1px solid #eeeeee;background-color:##eeeeee;">Day & Date</th><th style="padding:5px;border:1px solid #eeeeee;background-color:##eeeeee;">Start & Finish Time</th><th style="padding:5px;border:1px solid #eeeeee;background-color:##eeeeee;">Site</th><th style="padding:5px;border:1px solid #eeeeee;background-color:##eeeeee;">Task</th></tr>';
                        $message .= '<tr><td style="text-align:center; border:1px solid #eeeeee;">' . date('D', strtotime($value->start))  . ' ' . date('d-m-Y', strtotime($value->start)) . '</td><td style="text-align:center; border:1px solid #eeeeee;">' . date('H:i', strtotime($value->start)) . ' to ' . date('H:i', strtotime($value->end)) . '<td style="text-align:center;border:1px solid #eeeeee;">' . $site->site_name . ' (' . $site->site_description . ')</td><td style="text-align:center;border:1px solid #eeeeee;">' . $task_status . '</td></tr>';
                        $message .= '</table></div>';
                        $req['message'] = $message;
                        $this->sendGuardMail($guard, $req['subject'], $message);
                    }
                }
            }
        }
            // end customer ids loop
        $result["message"] = "success";
        $result["success"] = true;
        $result['count'] =  $publishShiftcount;
        return response()->json($result);
    }

   public function getAllPublishData($type, $customerId, $selected_Ids, $start = null, $end = null)
    {
        $query = DB::table('job_rosters')
            ->join('sites', 'sites.id', '=', 'job_rosters.site_id')
            ->where('job_rosters.publish_status', '0')
            ->whereNotNull('job_rosters.guard_id')
            ->where('job_rosters.guard_id', '!=', 0)
            ->where('sites.customer_id', $customerId);

        if ($type === "guard") {
            $guardIds = collect($selected_Ids)
                ->where('name', 'publish_selected_ids')
                ->pluck('value')
                ->unique()
                ->toArray();

            if (!empty($guardIds)) {
                $query->whereIn('job_rosters.guard_id', $guardIds);
            } else {
                return []; // No guards selected
            }
        } else {
            $siteIds = collect($selected_Ids)
                ->where('name', 'publish_selected_ids')
                ->pluck('value')
                ->unique()
                ->toArray();

            if (!empty($siteIds)) {
                $query->whereIn('job_rosters.site_id', $siteIds);
            } else {
                return [];
            }
        }

        if ($start !== null && $end !== null) {
            $start = date('Y-m-d 00:00:00', strtotime($start));
            $end = date('Y-m-d 23:59:59', strtotime($end));

            $query->whereBetween('job_rosters.start', [$start, $end]);
        }

        return $query->select('job_rosters.*')->get();
    }

    function getAllPublishGuards($type, $customerId, $selected_Ids, $start = null, $end = null)
    {
        $query = DB::table('job_rosters')
            ->join('guards', 'guards.id', '=', 'job_rosters.guard_id')
            ->join('sites', 'sites.id', '=', 'job_rosters.site_id')
            ->where('job_rosters.publish_status', '0')
            ->whereNotNull('job_rosters.guard_id')
            ->where('job_rosters.guard_id', '!=', 0)
            ->where('sites.customer_id', $customerId);

        if ($type === "guard") {
            // Extract guard IDs
            $guardIds = collect($selected_Ids)
                ->where('name', 'publish_selected_ids')
                ->pluck('value')
                ->unique()
                ->toArray();

            if (!empty($guardIds)) {
                $query->whereIn('job_rosters.guard_id', $guardIds);
            } else {
                return []; // no guards selected
            }
        } else {
            // Extract site IDs
            $siteIds = collect($selected_Ids)
                ->where('name', 'publish_selected_ids')
                ->pluck('value')
                ->unique()
                ->toArray();

            if (!empty($siteIds)) {
                $query->whereIn('job_rosters.site_id', $siteIds);
            } else {
                return []; // no sites selected
            }
        }

        if ($start !== null && $end !== null) {
            $start = date('Y-m-d 00:00:00', strtotime($start));
            $end = date('Y-m-d 23:59:59', strtotime($end));

            $query->whereBetween('job_rosters.start', [$start, $end]);
        }

        return $query
            ->select('guards.id', 'guards.name', 'guards.notification_token', 'guards.email')
            ->groupBy('guards.id', 'guards.name', 'guards.notification_token', 'guards.email')
            ->get();
    }

    function send_push_notification($data){

        $content = array(
          "en" => $data['message']
          );
    
        $heading = array(
          "en" => $data['title']
          );
          $root = $_SERVER['HTTP_HOST'];
          $root = explode('.', $root);
          $sub_domain = 'staffingsolution';
          if($root[0] != 'wwww'){
              $sub_domain = $root[0];
          }else{
          $sub_domain = $root[1];
          }
          $config_data = DB::table('business_data')->first();
        $fields = array(
          'app_id' => $config_data->app_id,
          'include_player_ids' => array($data['guards'][0]['notification_token']),
                'data' => array(
                  'page' => $data['page'],
                  'job_id' => isset($data['roster_id']) ? $data['roster_id'] : '1111',
                  'job_data' => isset($data['job_data']) ? json_encode($data['job_data']) : json_encode(array())
                  ),
          'contents' => $content,
          'headings' => $heading
        );
            
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://onesignal.com/api/v1/notifications");
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json; charset=utf-8',
                  'Authorization: Basic '.$config_data->server_key));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_HEADER, FALSE);
        curl_setopt($ch, CURLOPT_POST, TRUE);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        
        $result = curl_exec($ch);
    
        // echo $result;
        
        if ($result === FALSE) {
          die('FCM Send Error: ' . curl_error($ch));
        }
        curl_close($ch);
        return $result;
      }

      public function updatePublishStatus($roster_id){

        $data = array(
    
          "publish_status" => 1,
        //   'record_update' => 0,
        //   'roll_over' => 0,
          'update_status' => 1
    
        );
    
        DB::table('job_rosters')->where('id', $roster_id)->update($data);
    
        return true;
    
      }

      function getPublishList(Request $request)
    {
        $customer_ids = $request->customer_ids;
        $roster_id = $request->roster_id;

        $start = date('Y-m-d 00:00', strtotime($request->calendarStart));
        $end = date('Y-m-d 23:59', strtotime($request->calendarEnd));
        if ($request->type == 'guard') {
            $query = Guard::join('job_rosters', 'job_rosters.guard_id', '=', 'guards.id')
            ->join('sites', 'sites.id', '=', 'job_rosters.site_id')

            ->where('job_rosters.start', '>=', $start)
            ->where('job_rosters.start', '<=',  $end)
            ->where('job_rosters.publish_status', 0)
            ->where('job_rosters.roster_id', $roster_id)
            ->whereNull('job_rosters.deleted_at')
            ->where('job_rosters.guard_id', '>', 0);

            $query->where(function ($que) use ($customer_ids) {
                foreach ($customer_ids as $key => $cId) {
                    if ($key == 0) {
                        $que->where('sites.customer_id', $cId);
                    } else {
                        $que->orWhere('sites.customer_id', $cId);
                    }
                }
            })
            ->select(DB::raw("CONCAT(guards.first_name, ' ', guards.last_name) as title"), 'guards.id')
            ->orderBy('guards.name', 'ASC')
            ->groupBy('guards.id');
        } else {
            $query = Site::join('job_rosters', 'job_rosters.site_id', '=', 'sites.id')
               
            ->where('job_rosters.start', '>=',  date('Y-m-d 00:00', strtotime($request->calendarStart)))
            ->where('job_rosters.start', '<=',  date('Y-m-d 23:59', strtotime($request->calendarEnd)))    
            ->where('job_rosters.publish_status', 0)
            ->where('job_rosters.roster_id', $roster_id)
            ->whereNull('job_rosters.deleted_at')
            ->where('job_rosters.guard_id', '>', 0);

    
            $query->where(function ($que) use ($customer_ids) {
                foreach ($customer_ids as $key => $cId) {
                    if ($key == 0) {
                        $que->where('sites.customer_id', $cId);
                    } else {
                        $que->orWhere('sites.customer_id', $cId);
                    }
                }
            })
            ->select('sites.id', 'sites.site_name as title')->orderBy('sites.site_name', 'ASC')->groupBy('sites.id');
        }
        $data = $query->get();
     
        return response()->json(['success' => true, 'data' => $data]);
    }

   public function getSelectedListPublish(Request $request)
    {
        if (empty($request->selected_list)) {
            return response()->json(['success' => false, 'message' => 'No items selected'], 400);
        }

        $start = Carbon::parse($request->calendarStart)->startOfDay()->toDateTimeString();
        $end = Carbon::parse($request->calendarEnd)->endOfDay()->toDateTimeString();

        $selected_list = $request->selected_list;
        $selected_ids = array_column($selected_list, 'value');

        if ($request->type == 'guard') {
            $query = Guard::join('job_rosters', 'job_rosters.guard_id', '=', 'guards.id')
                ->where('job_rosters.start', '>=', $start)
                ->where('job_rosters.start', '<=', $end)
                ->where('job_rosters.publish_status', 0)
                ->whereNull('job_rosters.deleted_at')
                ->where('job_rosters.guard_id', '>', 0)
                ->whereIn('guards.id', $selected_ids)
                ->select(DB::raw("CONCAT(guards.first_name, ' ', guards.last_name) as title"), 'guards.id')
                ->groupBy('guards.id')
                ->get();
        } else {
            $query = Site::join('job_rosters', 'job_rosters.site_id', '=', 'sites.id')
                ->where('job_rosters.start', '>=', $start)
                ->where('job_rosters.start', '<=', $end)
                ->where('job_rosters.publish_status', 0)
                ->whereNull('job_rosters.deleted_at')
                ->where('job_rosters.guard_id', '>', 0)
                ->whereIn('sites.id', $selected_ids)
                ->select('sites.id', 'sites.site_name as title')
                ->groupBy('sites.id')
                ->get();
        }

        return response()->json(['success' => true, 'data' => $query]);
    }

    function sendGuardMail($user, $subject, $email_message)
    {
        $root = $_SERVER['HTTP_HOST'];
        $root = explode('.', $root);
        $postfix = ($root[0] != 'www') ? $root[0] : $root[1];
    
        $config_title = config('custom.title');
        $logo_url = 'https://' . $_SERVER['HTTP_HOST'] . '/uploads/email_footer.png';
    
        $emailData = [
            'name' => $user->name,
            'message_body' => $email_message,
            'subject' => $subject,
            'logo_url' => $logo_url,
            'from_email' => $postfix . '@247staffingsolution.com.au',
            'link' => 'https://' . $_SERVER['HTTP_HOST'] . '/portal/job_roster',
        ];
    
        try {
            Mail::send('mail.publishRoster', $emailData, function ($message) use ($user, $emailData) {
                $message->to($user->email)
                        ->from($emailData['from_email'])
                        ->subject($emailData['subject']);
            });
        } catch (\Exception $e) {
            \Log::error("Mail sending failed: " . $e->getMessage());
        }
    }

    function send_push_notification_test($data){

        $content = array(
          "en" => $data['message']
          );
    
        $heading = array(
          "en" => $data['title']
          );

          $config_data = DB::table('business_data')->first();

        $fields = array(
          'app_id' => $config_data->app_id,
          'include_player_ids' => array($data['notification_token']),
                  'data' => array(
                  'page' => $data['page'],
                  'send_by' => isset($data['send_by']) ? $data['send_by']: null ,
                  ),
          'contents' => $content,
          'headings' => $heading
        );
         
    
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://onesignal.com/api/v1/notifications");
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json; charset=utf-8',
                  'Authorization: Basic '.$config_data->server_key));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_HEADER, FALSE);
        curl_setopt($ch, CURLOPT_POST, TRUE);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        
        $result = curl_exec($ch);
        
        if ($result === FALSE) {
          die('FCM Send Error: ' . curl_error($ch));
        }
        curl_close($ch);
        return $result;

      }

    function syncShiftsAmgToScouts(Request $request)
    {
        $connectionSource = 'mysql3';
        $connectionTarget = 'mysql4';

        $startDate = '2025-05-19';
        $endDate = '2025-05-25';

        // $shifts = DB::connection($connectionSource)
        //     ->table('job_new_roster')
        //     ->whereBetween('temp_date', [$startDate, $endDate])
        //     ->get();

        $shifts = DB::connection($connectionSource)
        ->table('job_new_roster')
        ->whereBetween('temp_date', [$startDate, $endDate])
        ->whereNotNull('guard_id')
        ->where('guard_id', '>', 0)
        ->get();

        foreach ($shifts as $shift) {
            $siteName = DB::connection($connectionSource)
                ->table('jobs')
                ->where('id', $shift->site_id)
                ->value('site_name');

            $newSiteId = !empty($siteName)
                ? DB::connection($connectionTarget)->table('sites')->where('site_name', $siteName)->value('id')
                : null;

            $guardEmail = DB::connection($connectionSource)
                ->table('guards')
                ->where('id', $shift->guard_id)
                ->value('email');

            $newGuardId = !empty($guardEmail)
                ? DB::connection($connectionTarget)->table('guards')->where('email', $guardEmail)->value('id')
                : null;

            $start = !empty($shift->temp_start) ? $shift->temp_start : null;
            $end = !empty($shift->temp_end) ? $shift->temp_end : null;

            $hours = $this->getShiftHours(
                dbFormateDateTime($start),
                dbFormateDateTime($end),
                $newSiteId,
                1, 0, 0
            );

            $insertData = [
                'site_id' => $newSiteId,
                'guard_id' => $newGuardId,
                'start' => $start,
                'end' => $end,
                'roster_id' => 1,
                'temp_date' => !empty($shift->temp_date) ? $shift->temp_date : null,
                'publish_status' => !empty($shift->publish_status) ? $shift->publish_status : 0,
                'job_status' => !empty($shift->job_status) ? $shift->job_status : 0,
                'shift_payable' => !empty($shift->payable) ? $shift->payable : 'no',
                'continuation' => 1,
                'hours' => !empty($shift->hours) ? $shift->hours : 0,
                'signin_status' => !empty($shift->signin_status) ? $shift->signin_status : 0,
                'adhoc_shift' => !empty($shift->adhoc_shift) ? $shift->adhoc_shift : 'no',
                'in_paysheet' => 1,

                'morning_hours' => !empty($hours['morning']) ? roundHours($hours['morning']) : 0,
                'night_hours' => !empty($hours['night']) ? roundHours($hours['night']) : 0,
                'saturday_morning_hours' => !empty($hours['saturday_morning']) ? roundHours($hours['saturday_morning']) : 0,
                'saturday_night_hours' => !empty($hours['saturday_night']) ? roundHours($hours['saturday_night']) : 0,
                'sunday_morning_hours' => !empty($hours['sunday_morning']) ? roundHours($hours['sunday_morning']) : 0,
                'sunday_night_hours' => !empty($hours['sunday_night']) ? roundHours($hours['sunday_night']) : 0,
                'ph_morning_hours' => !empty($hours['ph_morning']) ? roundHours($hours['ph_morning']) : 0,
                'ph_night_hours' => !empty($hours['ph_night']) ? roundHours($hours['ph_night']) : 0,
            ];

            DB::connection($connectionTarget)
                ->table('job_rosters')
                ->insert($insertData);
        }

        return response()->json(['message' => 'Shifts synced successfully.']);
    }

    function copyShiftNew(Request $request)
    {
        $copy_days = $request->copy_days;

        foreach ($copy_days as $key => $sw) {
           $copy_data = DB::table('job_rosters')->where('id', $request->id)->first();

           $shift_start_day = date('d', strtotime($copy_data->start));
            $shift_end_day = date('d', strtotime($copy_data->end));
            $start_time = date('H:i', strtotime($copy_data->start));
            $end_time = date('H:i', strtotime($copy_data->end));
            $end_date = $sw['value'];
            if ($shift_start_day != $shift_end_day) {
                $end_date = strtotime("+1 day", $end_date);
            }
            $copy_data->start = date('Y-m-d', $sw['value']) . ' ' . $start_time;
            $copy_data->end = date('Y-m-d',  $end_date) . ' ' . $end_time;
            // $copy_data->start = strtotime($copy_data->start);
            // $copy_data->end = strtotime($copy_data->end);

            # CHECK GUARD WORK LIMITATION
            if ($copy_data->guard_id != '' && $copy_data->guard_id != null && $copy_data->guard_id > 0)
            {
                $guardOnLimitations = GuardWorkDetail::where('guard_id', $copy_data->guard_id)->first();

                //old code
                $timestamp1 = strtotime($copy_data->start);
                $timestamp2 = strtotime($copy_data->end);

                if (empty($timestamp2)) {
                    $timestamp2 = strtotime($copy_data->start);
                }

                $week_array = $this->calculateFutureMonthFourthnight($copy_data->start);
                $newCurrentHours = abs($timestamp2 - $timestamp1) / (60 * 60);

                $currentMonthData = $this->get_current_month_hours_guards_part_student($copy_data->guard_id, $week_array['week_start'], $week_array['week_end'], 0);

                $addedHours = 0;

                $calander_start_date = new DateTime($copy_data->start);
                $calender_end_date = new DateTime($copy_data->end);
                $fortnight_start_date = new DateTime($week_array['week_start']);
                $fortnight_end_date = new DateTime($week_array['week_end']);

                if (!empty($currentMonthData)) {
                    foreach ($currentMonthData as $currentMonthDatas) {
                        $timestamps1 = strtotime($currentMonthDatas->start);
                        $timestamps2 = strtotime($currentMonthDatas->end);
                        
                        $startDay = date('w', $timestamps1);
                        $endDay = date('w', $timestamps2);
                        
                        if ($startDay == 0 && $endDay == 1) {
                            $midnightSunday = strtotime('tomorrow', $timestamps1) - 1;
                            $sundayHours = abs($midnightSunday - $timestamps1) / 3600;
                            
                            $mondayStart = strtotime('tomorrow 00:00:00', $timestamps1);
                            $mondayHours = abs($timestamps2 - $mondayStart) / 3600;
                            
                            $addedHours += $mondayHours;
                        } 
                        else if ($startDay == 6 && $endDay == 0) {
                            $midnightSaturday = strtotime('tomorrow', $timestamps1) - 1;
                            $saturdayHours = abs($midnightSaturday - $timestamps1) / 3600;
                            
                            $addedHours += $saturdayHours;
                        }
                        else if ($startDay == 0 && $endDay == 0) {
                            $addedHours += abs($timestamps2 - $timestamps1) / 3600;
                        }
                        else {
                            $addedHours += abs($timestamps2 - $timestamps1) / 3600;
                        }
                    }
                }

                $dates_periods = array();   
                $period = new DatePeriod(
                    new DateTime($fortnight_start_date->format("Y-m-d")),
                    new DateInterval('P1D'),
                    new DateTime($fortnight_end_date->format("Y-m-d"))
                );
                foreach ($period as $key => $value) {
                    array_push($dates_periods, $value->format('Y-m-d'));
                }
                array_push($dates_periods, $fortnight_end_date->format("Y-m-d"));
                
                if (in_array($calander_start_date->format("Y-m-d"), $dates_periods)) {
                    $dateTime = $this->parseDateWithAutoDetection($copy_data->start);
                    $endTime = $this->parseDateWithAutoDetection($copy_data->end);
                    $dayName = $dateTime->format('l');
                    $endDayName = $endTime->format('l');

                    if ($endTime->lt($dateTime)) {
                        $endTime->addDay();
                    }

                    $shiftStartDate = $dateTime->format('Y-m-d');
                    $shiftEndDate = $endTime->format('Y-m-d');
                    if (!in_array($shiftEndDate, $dates_periods)) {
                            
                        $fortnightEnd = Carbon::parse($fortnight_end_date)->endOfDay();
                        $current = $dateTime->copy();
                        $hoursInCurrentFortnight = 0;   
                        while ($current->lt($fortnightEnd)) {
                            $hourEnd = min($current->copy()->addHour(), $fortnightEnd);
                            
                            if ($current->format('l') != 'Sunday') {
                                $hoursInCurrentFortnight += $current->diffInHours($hourEnd, true);
                            }
                            
                            $current = $hourEnd;
                        }                         
                        $addedHours = $addedHours + $hoursInCurrentFortnight;
                    } else {
                        if(($dayName == 'Saturday' && $endDayName == 'Sunday')){
                            $sundayStart = $dateTime->copy()->addDay()->startOfDay();
                            $satHours = $dateTime->diffInHours($sundayStart, false);
                            
                            $addedHours = $addedHours + $satHours;
                        }elseif(($dayName == 'Sunday' && $endDayName == 'Monday')){
                            $mondayStart = $dateTime->copy()->addDay()->startOfDay();
                            $sunHours = $dateTime->diffInHours($mondayStart, false);
                            
                            $addedHours = ($addedHours + $newCurrentHours) - $sunHours;
                        }elseif(($dayName == 'Sunday' && $endDayName == 'Sunday')){
                            $addedHours = $addedHours - $newCurrentHours;
                        }else{
                            $addedHours = $addedHours + $newCurrentHours;
                        }
                    }

                    //here add code
                    if($guardOnLimitations->guard_document_type == 'student_visa'){
                        if($guardOnLimitations->limit_exceed == 1)
                        {
                            $guardStart = new DateTime($guardOnLimitations->start_time);
                            $guardEnd = new DateTime($guardOnLimitations->end_time);
                            $interval = new DateInterval('P1D');
                            $dateRange = new DatePeriod($guardStart, $interval, $guardEnd->modify('+1 day'));

                            $guardDates = [];
                            foreach ($dateRange as $date) {
                                $guardDates[] = $date->format('Y-m-d');
                            }
                            $hasCompleteFortnight = false;

                            $guardDateCount = count($guardDates);

                            for ($i = 0; $i <= $guardDateCount - 14; $i++) {
                                $fourteenDays = array_slice($guardDates, $i, 14);
                                
                                $allExist = true;
                                foreach ($fourteenDays as $day) {
                                    if (!in_array($day, $dates_periods)) {
                                        $allExist = false;
                                        break;
                                    }
                                }
                                
                                if ($allExist) {
                                    $hasCompleteFortnight = true;
                                    break;
                                }
                            }

                            if ($hasCompleteFortnight) {
                            $totalWorkingHours = 72;   
                            if ($addedHours > $totalWorkingHours) {
                                return response()->json([
                                        'success' => false,
                                        'message' => 'You cannot create a shift because you exceed your work limitations.',
                                        'data' => [
                                            'fortnight_limit' => $totalWorkingHours,
                                            'total_hours' => $addedHours,
                                            'exceed_hours' => $addedHours - $totalWorkingHours, 
                                        ],
                                    ]);
                                    // if (isset($request->otp)) {
                                        
                                    //     $getAdmin = User::where('id', 257)->first();

                                    //     if (!$getAdmin || !$getAdmin->google2fa_secret) {
                                    //         return response()->json([
                                    //             'success' => false,
                                    //             'message' => 'Manager does not have 2FA setup.',
                                    //             'data' => [
                                    //                 'fortnight_limit' => $totalWorkingHours,
                                    //                 'total_hours' => $addedHours,
                                    //                 'exceed_hours' => $addedHours - $totalWorkingHours,
                                    //             ],
                                    //         ]);
                                    //     }
                                        
                                    //     $googleAuthenticator = new Google2FA();
                                    //     $valid = $googleAuthenticator->verifyKey($getAdmin->google2fa_secret, $request->otp);
                                        
                                    //     if (!$valid) {
                                    //         return response()->json([
                                    //             'success' => false,
                                    //             'message' => 'Wrong OTP! Please try again.',
                                    //             'require_otp' => true,
                                    //             'data' => [
                                    //                 'fortnight_limit' => $totalWorkingHours,
                                    //                 'total_hours' => $addedHours,
                                    //                 'exceed_hours' => $addedHours - $totalWorkingHours,
                                    //             ],
                                    //         ]);
                                    //     }
                                        
                                    // } else {
                                    //     return response()->json([
                                    //         'success' => false,
                                    //         'message' => 'Hours exceed limit. Manager OTP required.',
                                    //         'require_otp' => true,
                                    //         'data' => [
                                    //             'fortnight_limit' => $totalWorkingHours,
                                    //             'total_hours' => $addedHours,
                                    //             'exceed_hours' => $addedHours - $totalWorkingHours,
                                    //         ],
                                    //     ]);
                                    // }
                                }  
                            } else {
                            $totalWorkingHours = 48;
                            if ($addedHours > $totalWorkingHours) {
                                return response()->json([
                                        'success' => false,
                                        'message' => 'You cannot create a shift because you exceed your work limitations.',
                                        'data' => [
                                            'fortnight_limit' => $totalWorkingHours,
                                            'total_hours' => $addedHours,
                                            'exceed_hours' => $addedHours - $totalWorkingHours, 
                                        ],
                                    ]);
                                    // if (isset($request->otp)) {
                                        
                                    //     $getAdmin = User::where('id', 257)->first();

                                    //     if (!$getAdmin || !$getAdmin->google2fa_secret) {
                                    //         return response()->json([
                                    //             'success' => false,
                                    //             'message' => 'Manager does not have 2FA setup.',
                                    //             'data' => [
                                    //                 'fortnight_limit' => $totalWorkingHours,
                                    //                 'total_hours' => $addedHours,
                                    //                 'exceed_hours' => $addedHours - $totalWorkingHours,
                                    //             ],
                                    //         ]);
                                    //     }
                                        
                                    //     $googleAuthenticator = new Google2FA();
                                    //     $valid = $googleAuthenticator->verifyKey($getAdmin->google2fa_secret, $request->otp);
                                        
                                    //     if (!$valid) {
                                    //         return response()->json([
                                    //             'success' => false,
                                    //             'message' => 'Wrong OTP! Please try again.',
                                    //             'require_otp' => true,
                                    //             'data' => [
                                    //                 'fortnight_limit' => $totalWorkingHours,
                                    //                 'total_hours' => $addedHours,
                                    //                 'exceed_hours' => $addedHours - $totalWorkingHours,
                                    //             ],
                                    //         ]);
                                    //     }
                                        
                                    // } else {
                                    //     return response()->json([
                                    //         'success' => false,
                                    //         'message' => 'Hours exceed limit. Manager OTP required.',
                                    //         'require_otp' => true,
                                    //         'data' => [
                                    //             'fortnight_limit' => $totalWorkingHours,
                                    //             'total_hours' => $addedHours,
                                    //             'exceed_hours' => $addedHours - $totalWorkingHours,
                                    //         ],
                                    //     ]);
                                    // }
                                }
                            }
                        }else{
                            $totalWorkingHours = 48;
                            if ($addedHours > $totalWorkingHours) {
                                return response()->json([
                                'success' => false,
                                'message' => 'You cannot create a shift because you exceed your work limitations.',
                                'data' => [
                                    'fortnight_limit' => $totalWorkingHours,
                                    'total_hours' => $addedHours,
                                    'exceed_hours' => $addedHours - $totalWorkingHours, 
                                ],
                            ]);
                                // if (isset($request->otp)) {
                                    
                                //     $getAdmin = User::where('id', 257)->first();

                                //     if (!$getAdmin || !$getAdmin->google2fa_secret) {
                                //         return response()->json([
                                //             'success' => false,
                                //             'message' => 'Manager does not have 2FA setup.',
                                //             'data' => [
                                //                 'fortnight_limit' => $totalWorkingHours,
                                //                 'total_hours' => $addedHours,
                                //                 'exceed_hours' => $addedHours - $totalWorkingHours,
                                //             ],
                                //         ]);
                                //     }
                                    
                                //     $googleAuthenticator = new Google2FA();
                                //     $valid = $googleAuthenticator->verifyKey($getAdmin->google2fa_secret, $request->otp);
                                    
                                //     if (!$valid) {
                                //         return response()->json([
                                //             'success' => false,
                                //             'message' => 'Wrong OTP! Please try again.',
                                //             'require_otp' => true,
                                //             'data' => [
                                //                 'fortnight_limit' => $totalWorkingHours,
                                //                 'total_hours' => $addedHours,
                                //                 'exceed_hours' => $addedHours - $totalWorkingHours,
                                //             ],
                                //         ]);
                                //     }
                                    
                                // } else {
                                //     return response()->json([
                                //         'success' => false,
                                //         'message' => 'Hours exceed limit. Manager OTP required.',
                                //         'require_otp' => true,
                                //         'data' => [
                                //             'fortnight_limit' => $totalWorkingHours,
                                //             'total_hours' => $addedHours,
                                //             'exceed_hours' => $addedHours - $totalWorkingHours,
                                //         ],
                                //     ]);
                                // }
                            }
                        }
                    }else{
                        $totalWorkingHours = 72;
                        if ($guardOnLimitations && $guardOnLimitations->work_hours_limitation_status == 1) {
                            $totalWorkingHours = $guardOnLimitations->weekly_work_hours_limitation ?? 72;
                        }

                        if ($addedHours > $totalWorkingHours) {
                            //return here
                            return response()->json([
                                'success' => false,
                                'message' => 'You cannot create a shift because you exceed your work limitations.',
                                'data' => [
                                    'fortnight_limit' => $totalWorkingHours,
                                    'total_hours' => $addedHours,
                                    'exceed_hours' => $addedHours - $totalWorkingHours, 
                                ],
                            ]);
                        }
                    }      
                  
                }
                //end old code
            }

            $next_roster = array(
    
                'guard_id' => $copy_data->guard_id,
                'site_id' => $copy_data->site_id,
                'temp_date' => date('Y-m-d', strtotime($copy_data->start)),
                'start' => date('Y-m-d H:i', strtotime($copy_data->start)),
                'end' => date('Y-m-d H:i', strtotime($copy_data->end)),
                'publish_status' => 0,
                // 'add_status' => $copy_data->add_status,
                'job_status' => 'pending',
                // 'post_status' => 0,
                'green_call_notification' => 'no',
                // 'roll_over' => 1,
                'update_status' => 1,
                // 'job_start' => $copy_data->job_start,
                // 'job_end' => $copy_data->job_end,
                'continuation' => $copy_data->continuation,
                'over_time' => $copy_data->over_time,
                'over_time_value' => $copy_data->over_time_value,
                'travel_time' => $copy_data->travel_time,
                'travel_time_value' => $copy_data->travel_time_value,
                'roster_id' => $copy_data->roster_id,
            );
            $next_roster['operation_notes'] = $copy_data->operation_notes;
            $next_roster['custome_rate'] = $copy_data->custome_rate;
            $next_roster['payrate'] = $copy_data->payrate;
            $next_roster['chargerate'] = $copy_data->chargerate;

            $hours = $this->getShiftHours(date('Y-m-d H:i:s', strtotime($copy_data->start)), date('Y-m-d H:i:s', strtotime($copy_data->end)), $copy_data->site_id, $copy_data->continuation);
            $guardWorkingHours =  calCulateGuardWeekHours(date('Y-m-d H:i:s', strtotime($copy_data->start)), date('Y-m-d H:i:s', strtotime($copy_data->end)));

            $next_roster['hours'] = $guardWorkingHours;
            $next_roster['morning_hours'] = (!empty($hours['morning']) ? $hours['morning'] : 0.0);
            $next_roster['night_hours'] = (!empty($hours['night']) ? $hours['night'] : 0.0);
            $next_roster['saturday_morning_hours'] = (!empty($hours['saturday_morning']) ? $hours['saturday_morning'] : 0.0);
            $next_roster['saturday_night_hours'] = (!empty($hours['saturday_night']) ? $hours['saturday_night'] : 0.0);
            $next_roster['sunday_morning_hours'] = (!empty($hours['sunday_morning']) ? $hours['sunday_morning'] : 0.0);
            $next_roster['sunday_night_hours'] = (!empty($hours['sunday_night']) ? $hours['sunday_night'] : 0.0);
            $next_roster['ph_morning_hours'] = (!empty($hours['ph_morning']) ? $hours['ph_morning'] : 0.0);
            $next_roster['ph_night_hours'] = (!empty($hours['ph_night']) ? $hours['ph_night'] : 0.0);

            if ($next_roster['guard_id'] != '' && $next_roster['guard_id'] != null && $next_roster['guard_id'] > 0) {

                $already = DB::table('job_rosters')->where('guard_id', $next_roster['guard_id'])->where('start', '<=', $next_roster['start'])->where('end', '>=', $next_roster['start'])->where('deleted_at', null)->first();
                if (empty($already)) {
                    $already = DB::table('job_rosters')->where('guard_id', $next_roster['guard_id'])->where('start', '<=', $next_roster['end'])->where('end', '>=', $next_roster['end'])->where('deleted_at', null)->first();
                }
                $guardData = DB::table('guards')->where('id', $next_roster['guard_id'])->first();
                $status = $this->checkGuardSecurityLicenceDocuments($guardData, $next_roster['temp_date']);
                // if (!empty($already)) {
                //     $next_roster['guard_id'] = 0;
                //     $conflict = 1;
                // } elseif ($status != "Active") {
                //     $next_roster['guard_id'] = 0;
                //     $conflict = 1;
                // }
            }
                // return $next_roster;
            $inserted_id = DB::table('job_rosters')->insertGetId($next_roster);
                // end of if
        }
        return response()->json(['success' => true, 'message' => 'success']);
    }

    public function rosterBulkDelete(Request $request)
    {
        $selectedShifts = $request->selected_shift_id;

        if (empty($selectedShifts) || !is_array($selectedShifts)) {
            return response()->json(['message' => "No Shifts Selected", 'code' => 400, 'success' => false], 400);
        }

        foreach ($selectedShifts as $shiftData) {
            $shiftId = $shiftData['id'] ?? null;
            if (!$shiftId) {
                continue;
            }

            $shift = JobRoster::find($shiftId);

            if (!$shift) {
                continue;
            }

            $date = dateFormat($shift->start);

            $guard = Guard::where('id', $shift->guard_id)->select('id', 'notification_token', 'first_name', 'last_name', 'phone')->first();

            if (!empty($guard->phone)) {
                sendSmsToGuard($guard->phone, 'Hi '.$guard->first_name.' '.$guard->last_name.' your shift '.$date.' has been Deleted!');
            }

            if ($shift->guard_id > 0 && $shift->publish_status == 1 && $guard->notification_token) {
                $notificaion = [
                    'notification_token' => $guard->notification_token,
                    'message' => "One of your shifts on ".$date." has been deleted. Please check your app.",
                    'title' => 'Shift Deleted',
                    'page' => 'homepage'
                ];
                $this->send_push_notification_test($notificaion);
            }

            removeConflictOnDeleteShift($shift->start, $shift->end, $shift->roster_id, $shift->guard_id);

            $conflictedShifts = JobRoster::where('conflicted_with', $shift->id)->get();
            foreach ($conflictedShifts as $conflict) {
                $conflict->update([
                    'conflicted_with' => null,
                    'conflict' => null,
                    'conf_start' => null,
                    'conf_end' => null
                ]);
            }

            DB::table('job_roster_activites')->where(['guard_id'=> $shift->guard_id, 'job_roster_id'=>$shift->id])->delete();
            DB::table('incident_reports')->where(['roster_id'=> $shift->id])->delete();
            DB::table('job_roster_tasks')->where(['job_roster_id'=> $shift->id])->delete();
            DB::table('job_breaks')->where(['roster_id'=> $shift->id])->delete();
            DB::table('welfare_call_data')->where(['job_roster_id'=> $shift->id])->delete();
            DB::table('green_call')->where(['job_id'=> $shift->id])->delete();

            jobRosterActions($request->admin_id, 'delete_shift', $shift->id, 'job_roster', $shift);

            $shift->reason = $request->reason;
            $shift->deleted_by = $request->admin_id;
            $shift->update();

            $shift->delete();

            $adminName = getAdminName($request->admin_id);
            shiftCompleteActivity($shift->id, $adminName . ' deleted this shift', 'delete_shift', $shift->id, time(), $request->admin_id);
        }

        return response()->json(['message' => "Selected Shifts Deleted", 'code' => 200, 'success' => true]);
    }

    //Hour Limitation Helper Function
    function get_current_month_hours_guards_part_student($guardId, $start, $end, $event_id = 0)
    {
        $query = jobroster::where('guard_id', $guardId)
            ->where('start', '>=', date('Y-m-d H:i', strtotime($start)))
            ->where('start', '<=', date('Y-m-d 23:59', strtotime($end)));
            // ->where('end', '>=', date('Y-m-d H:i', strtotime($start)))
            // ->where('end', '<=', date('Y-m-d 23:59', strtotime($end)));

        if ($event_id) {
            $query->where('id', '!=', $event_id);
        }

        $query->where(function($q) {
            $q->whereRaw("DAYOFWEEK(start) != 1")
            ->orWhereRaw("DAYOFWEEK(end) != 1");
        });

        return $query->get();
    }
}

