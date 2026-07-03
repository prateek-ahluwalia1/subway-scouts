<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSiteRequest;
use App\Http\Resources\AllSitesResource;
use App\Http\Resources\EditSiteResource;
use App\Http\Resources\GetDeleteSitesResource;
use App\Http\Resources\SiteTrakerResource;
use App\Models\ChargeRate;
use App\Models\DeleteSiteReason;
use App\Models\JobRoster;
use App\Models\JobRosterAction;
use App\Models\Payrate;
use App\Models\PayRatesNew;
use App\Models\Site;
use App\Models\SiteQrCode;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SiteController extends Controller
{
  public function saveAndUpdate(StoreSiteRequest $request)
  {
      $site = Site::where('id', $request->id)->first();
      $old_data = $site;
      $is_check = 0;
      if(!$site){
          $site = new Site();
          $is_check = 1;
      }
      $site->booking_id = substr(uniqid(), 0, 4).'-'.substr(uniqid(), 5, 4);
      $site->customer_id = $request->customer_id;
      $site->site_type = ($request->site_type ? $request->site_type : 'active');
      $site->site_name = $request->site_name;
      $site->type = !empty($request->type) ? $request->type : 'metro';
      $site->site_description = ($request->site_description == null || $request->site_description == '') ? '' : $request->site_description;
      $site->start = !empty($request->site_start_date) ? dbFormate($request->site_start_date) : null;
      $site->end = !empty($request->site_end_date)  ? dbFormate($request->site_end_date) : null;
      $site->job_instrcutions = ($request->job_instrcutions == null || $request->job_instrcutions == '') ? '' : $request->job_instrcutions;
      $site->staff_type = $request->staff_type;
      $site->site_budget = $request->site_budget;
      $site->unpublished_site = $request->unpublished_site;
      $site->trained = ($request->has('site_trained') ? $request->site_trained : 'no' );
      $site->state = $request->site_state;
      $site->sos_phone = $request->sos_phone;
      $site->address = $request->address;
      $site->coordinates = $request->coordinates;
      $site->signin_radius = ($request->signin_radius == null || $request->signin_radius == '') ? 0 : $request->signin_radius;
      $site->alert_radius = ($request->radius_alert == null || $request->radius_alert == '') ? 0 : $request->radius_alert;
      $site->level = $request->site_level; 
      $site->payrol = $request->payrol;
      $site->site_payrate_level = ($request->site_payrate_level == null || $request->site_payrate_level == '') ? null : $request->site_payrate_level;
      $site->site_payrate = ($request->site_payrate == null || $request->site_payrate == '') ? null : $request->site_payrate;
      $site->site_chargerate_level = ($request->site_chargerate_level == null || $request->site_chargerate_level == '') ? 0 : $request->site_chargerate_level;
      $site->site_charge_rate = ($request->site_charge_rate == null || $request->site_charge_rate == '') ? 0 : $request->site_charge_rate;
      $site->break = ($request->site_break == 'yes' ? 1 : 0);
      $site->break_payable = $request->site_break_payable;
      $site->break_chargeable = $request->site_break_chargeable;
      $site->break_deduction_payable = $request->break_deduction_payable;
      $site->break_deduction_chargeable = $request->break_deduction_chargeable;
      $site->site_tasks = json_encode((!empty($request->site_tasks) && $request->site_tasks !=null) ? $request->site_tasks : [] ,true);
      $site->welfare_call = $request->welfare_call;
      $site->welfare_call_type = $request->welfare_call_type;
      $site->welfare_timing = $request->welfare_timing;
      $site->green_call = $request->green_call;
      $site->first_green_call = $request->first_green_call;
      $site->second_green_call = $request->second_green_call;
      $site->site_hours = $request->site_hours;
      $site->po_wo = $request->po_wo;
      $site->first_green_call_time = $request->first_green_call_time;
      $site->second_green_call_time = $request->second_green_call_time;
      $site->payrate_affective_day = $request->dateSelectionOfPay;
      $site->chargerate_affective_day = $request->dateSelectionOfCharge;
      $site->payrate_affective_from = $request->payrate_affective_from;
      $site->chargerate_affective_from = $request->chargerate_affective_from;
      //$site->site_update_reason = $request->site_update_reason;
      $site->site_updated_by = $request->admin_id;
      if($is_check == 0){
          if($request->has('job_instruction_file')){
              $image = str_replace(url('')."/"."site/","",$request->job_instruction_file);
              $site->job_instruction_file = $image;
          }
      }else{
          if($request->has('job_instruction_file')){
              $site->job_instruction_file = $request->job_instruction_file;
          }
      }
      if($request->has('site_guard_ids') && !empty($request->site_guard_ids)){
          $site->site_guard_ids = json_encode($request->site_guard_ids);
      }
      # START PATROLLING MODULE
      if(isset($request->is_patrolling_site) && $request->is_patrolling_site == true) {
        $site->is_patrolling_site = $request->is_patrolling_site;
        $site->patrolling_type = $request->patrolling_type;
        $site->monitoring_person = $request->monitoring_person;
        $site->monitoring_contact = $request->monitoring_contact;
        $site->after_hours = $request->after_hours;
        $site->alarm_dispatch_instruction = $request->alarm_dispatch_instruction;
        $site->keys = json_encode($request->keys);
        $site->internal_patrolling = $request->internal_patrolling;
        $site->intern_no_calls = $request->intern_no_calls;
        $site->intern_time_type = $request->intern_time_type;
        $site->intern_particular_times = json_encode($request->intern_particular_times);
        $site->external_patrolling = $request->external_patrolling;
        $site->extern_no_calls = $request->extern_no_calls;
        $site->extern_time_type = $request->extern_time_type;
        $site->extern_particular_times = json_encode($request->extern_particular_times);
        $site->intermediate_patrolling = $request->intermediate_patrolling;
        $site->intermed_no_calls = $request->intermed_no_calls;
        $site->intermed_time_type = $request->intermed_time_type;
        $site->intermed_particular_times = json_encode($request->intermed_particular_times);  
        $site->alarm_panels = json_encode($request->alarm_panels);  
        $site->is_alarm_patrol_site = $request->is_alarm_patrol_site;

      }
      
      $dirtyAttributes = $site->getDirty();
      if(isset($request->id)){
        $getOld = Site::where('id', $request->id)->first();
        if($request->site_payrate != $getOld->site_payrate){
          DB::table('site_payrate_history')->insert([
            'site_id' => $site->id,
            'payrate_id' => $getOld->site_payrate ?? 0,
            'apply_date' =>  $getOld->payrate_affective_from,
            'apply_to_date' => null,
            'changed_by' => $request->admin_id
          ]);
        }
      }
      if(isset($request->id)){
        $getOld = Site::where('id', $request->id)->first();
        if($request->site_charge_rate != $getOld->site_charge_rate){
          DB::table('site_chargerate_history')->insert([
            'site_id' => $site->id,
            'chargerate_id' => $getOld->site_charge_rate,
            'apply_date' => $getOld->chargerate_affective_from,
            'changed_by' => $request->admin_id
          ]);
        }
      }
      $site->save();

      if($is_check == 1){
        if(isset($request->is_patrolling_site) && $request->is_patrolling_site == true){
          foreach($request->scanners as $scanner){
            $siteQRCode = new SiteQrCode();
            $siteQRCode->site_id = $site->id;
            $siteQRCode->unique_key = $scanner['id'];
            $siteQRCode->name = $scanner['name'];
            $siteQRCode->location = $scanner['location'];
            $siteQRCode->qr = 'qr';
            $siteQRCode->save();
          }
        }
        if($request->site_name != '' && $request->site_name != null){
          jobRosterActions($request->admin_id, 'add', $site->id, 'location');
        }
        return response()->json(['message' => "Location Created" ,  'code' => 200, 'success' => true, 'id'=>$site->id],200);
    }else{
        if(isset($request->is_patrolling_site) && $request->is_patrolling_site == true){
          SiteQrCode::where('site_id', $site->id)->delete();
          foreach($request->scanners as $scanner){
            $siteQRCode = new SiteQrCode();
            $siteQRCode->site_id = $site->id;
            $siteQRCode->unique_key = $scanner['unique_key'];
            $siteQRCode->name = $scanner['name'];
            $siteQRCode->location = $scanner['location'];
            $siteQRCode->scan_path = $scanner['scan_path'];
            $siteQRCode->scanner_file = $scanner['scanner_file'];
            $siteQRCode->qr = 'qr';
            $siteQRCode->save();
          }
        }
        $updatedSite = $site->getChanges();
        jobRosterActions($request->admin_id, 'update', $site->id, 'location', $old_data, $updatedSite, $request->site_update_reason);
        
         $old_payrate = Payrate::where('id', $old_data->site_payrate)->first();
        
         $old_chargerate = ChargeRate::where('id', $old_data->site_charge_rate)->first();
        
        if (isset($dirtyAttributes['site_payrate'])) {
            payrateHistory($request->site_payrate, $old_payrate, '00-00-00 ','11-11-11 ',$request->admin_id); 
        }
        if (isset($dirtyAttributes['site_charge_rate'])){
            chargerateHistory($request->site_charge_rate, $old_chargerate, '00-00-00 ','11-11-11 ',$request->admin_id); 
        }
        return response()->json(['message' => "Location Updated", 'code' => 200, 'success' => true],200);
    }
 }

   public function editSite(Request $request)
   {
      $site = Site::where('id', $request->id)->first();
      $sit = (new EditSiteResource($site));
      $model = JobRosterAction::query();
      $activites = $model->where('roster_id', $request->id)->where('action_on', 'location')->orderBy('created_at', 'desc')->get();
      $acts = SiteTrakerResource::collection($activites);
      return response()->json([ 'success' => true, 'data' => $sit, 
       'site_traker' => $acts, 'code' => 200 ]);
   }



   
   public function deleteSite(Request $request)
    {
        $site = Site::find($request->id);

        if (!$site) {
            return response()->json(['success' => false, 'message' => 'Location not found']);
        }
        if($site->site_name != '' && $site->site_name != Null){

          $shiftsCount = JobRoster::where('site_id', $request->id)->count();
          
          if ($shiftsCount > 0 && $request->is_confirm !== 'yes' && !$request->has('is_confirm')) {
              return response()->json(['success' => false, 'message' => 'This location has shifts. Do you really want to delete this location?']);
          }
          if (($request->has('is_confirm') && $request->is_confirm == 'yes') || ((!$request->has('is_confirm') && $shiftsCount == 0))) {
            $site_delete_reason = new DeleteSiteReason();
            $site_delete_reason->site_id = $site->id;
            $site_delete_reason->reason = $request->reason;
            $site_delete_reason->site_name = $site->site_name;
            $site_delete_reason->action_by = $request->admin_id;
            $site_delete_reason->save();
          }
          // Common deletion and action logging
          JobRoster::where('site_id', $site->id)->delete();
          jobRosterActions($request->admin_id, 'delete_site', $site->id, 'location', $site);
        }

        $site->delete();

        return response()->json(['success' => true, 'message' => 'Location Deleted']);
    }

   


    public function getDeleteSiteReasons(){

      $reasons = DeleteSiteReason::select('site_name', 'reason', 'created_at', 'action_by')->get();
      if($reasons){
        $r = GetDeleteSitesResource::collection($reasons);
        return response()->json(['success' => true, 'data' => $r]);
      }else{
        return response()->json(['success' => false, 'data' => '']);
      }

      
    }


    public function getAllSites(Request $request)
    {
        $start = Carbon::now()->startOfWeek()->toDateString(); 
        $start = date('Y-m-d 00:00', strtotime($start));
        $end = Carbon::now()->endOfWeek()->toDateString();
        $end = date('Y-m-d 23:59', strtotime($end));
    
        $limit = $request->input('pageSize', 10);
        $offset = $request->input('pageIndex', 0) * $limit;
    
        $model = Site::query();
        $site_ids = [];
    
        if ($request->filled('customer_ids')) {
            $model->whereIn('customer_id', $request->customer_ids);
        }
    
        if ($request->filled('status')) {
            if ($request->status !== 'all') {
                if ($request->status == 'active') {
                    $site_ids = JobRoster::whereBetween('start', [$start, $end])->pluck('site_id')->toArray();
                } else {
                    $active_site_ids = JobRoster::whereBetween('start', [$start, $end])->pluck('site_id')->toArray();
                    $all_sites = Site::pluck('id')->toArray(); 
    
                    // Exclude active site IDs
                    $site_ids = array_diff($all_sites, $active_site_ids);
                }
    
                // if (count($site_ids) > 0) {
                    $model->whereIn('id', $site_ids);
                // }
            }
        }
        $total = $model->count();
    
        $sites = $model->skip($offset)->take($limit)
            ->select('id', 'site_name', 'site_description', 'site_status', 'level')
            ->orderBy('site_name', 'asc')
            ->get();
    
        $sitess = AllSitesResource::collection($sites);
    
        return response()->json([
            'success' => true,
            'data' => $sitess,
            'length' => $total,
            'pageIndex' => $request->pageIndex,
            'pageSize' => $request->pageSize,
            'code' => 200,
        ]);
    }
    



   public function activeAndInactiveSitesByCustomer(Request $request)
   {
    $sites = Site::whereIn('customer_id', $request->customer_id)->where('site_status', $request->site_status)->select('id','site_name','site_description')->get();
    $sitess = AllSitesResource::collection($sites);
    return response()->json([ 'success' => true, 'data' => $sitess, 'code' => 200 ]);
   }

    public function getPayRateAndLevel(Request $request)
    {
      $getPayRateAndLevel = Payrate::where('level', $request->level)->where('status', 'active')->select('id','title')->get();
      return response()->json([ 'success' => true, 'data' => $getPayRateAndLevel, 'code' => 200 ]);
    }

    public function getChargeRateAndLevel(Request $request)
    {
      $getChargeRateAndLevel = ChargeRate::where('level', $request->level)->select('id','title')->get();
      return response()->json([ 'success' => true, 'data' => $getChargeRateAndLevel, 'code' => 200 ]);
    }

    public function findSites(Request $request)
    {
      $limit = 10;
      $offset = 0;
      if($request->has('pageIndex') && $request->has('pageSize'))
      {
          $offset = $request->pageIndex * $request->pageSize;
          $limit = $request->pageSize;
      }
      
      $query = Site::where(function ($query) use ($request) {
          $searchTerm = $request->searchTerm;
              $query->whereRaw("CONCAT_WS(' ', site_name, site_description) LIKE ?", ['%' . $searchTerm . '%']);
          });
        if ($request->status == 'active') {
            $query->where('site_status', 'active');
        } elseif ($request->status == 'inactive') {
            $query->where(function ($query) {
                $query->where('site_status', 'inactive');
            });
        }
        $sites = $query->get();
        $sitess = AllSitesResource::collection($sites);
        // Count logic
        $count = $query->count();
        $total = $query->count();
        return response()->json(['success' => true, 'data' => $sitess, 'count' => $count, 'length' => $total, 'pageIndex' => $request->pageIndex, 'pageSize' => $request->pageSize]);
    }

}
