<?php

namespace App\Http\Controllers;

use App\Http\Resources\CustomerNameResource;
use App\Http\Resources\GetDeleteRunSheetResource;
use App\Http\Resources\RunsheetResource;
use App\Models\Customer;
use App\Models\DeleteRunSheetReason;
use App\Models\RunSheet;
use App\Models\RunSheetDetail;
use App\Models\RunSheetJobRoster;
use App\Models\RunSheetRoster;
use App\Models\Site;
use DB;
use Illuminate\Http\Request;

class PatrollingController extends Controller
{
    public function getPatrollingLocations(Request $request){
        if($request->type == 'run_sheet'){
            if(isset($request->runsheet_id)){
                $excludedSiteIDs = RunSheetDetail::whereNotNull('site_id')->whereNotIn('run_sheet_id', [$request->runsheet_id])->pluck('site_id');
            }else{
                $excludedSiteIDs = RunSheetDetail::whereNotNull('site_id')->pluck('site_id');
            }
            $patrollingSites = Site::where([
                'customer_id' => $request->customer_id,
                'is_patrolling_site' => 1,
                
            ])->whereNotIn('id', $excludedSiteIDs)->get();
        }else{
            $patrollingSites = Site::where('is_patrolling_site', 1)->get();
        }

        return response()->json([
            'success' => true,
            'data' => $patrollingSites
        ]);
    }
    public function createUpdateRunSheet(Request $request)
    {
        $runSheet = RunSheet::findOrNew($request->id);
        $runSheet->title = $request->title;
        $runSheet->description = $request->description;
        $runSheet->state = $request->state;
        $runSheet->patrol_brief_file = $request->patrol_brief_file;
        $runSheet->save();
    
        if ($request->has('sites') && !empty($request->sites)) {
            $customerIds = []; // Array to store customer_ids
    
            foreach ($request->sites as $detailData) {
                $details = RunSheetDetail::findOrNew($detailData['id'] ?? null);
                $details->run_sheet_id = $runSheet->id;
                $details->customer_id = $detailData['customer_id'] ?? null;
                $details->site_id = $detailData['site_id'] ?? null;
                $details->save();
    
                // Collect customer_ids
                $customerIds[] = $detailData['customer_id'];
            }
    
            $runSheet->customer_id = $customerIds;
            $runSheet->update();
        }
    
        $message = $runSheet->wasRecentlyCreated ? 'Runsheet created!' : 'Runsheet updated!';
        return response()->json([
            'success' => true,
            'message' => $message,
        ]);
    }


    public function getAllRunSheets()
    {
        $runSheets = RunSheet::with('runSheetDetails')->get();
        
        if ($runSheets->isNotEmpty()) {
            $rs = RunsheetResource::collection($runSheets);
            return response()->json(['success' => true, 'data' => $rs]);
        } else {
            return response()->json(['success' => true, 'data' => []]);
        }
    }
    public function editRunSheets($id)
    {
        $runSheets = RunSheet::where('id', $id)->with('runSheetDetails')->first();
        return response()->json(['success' => true, 'data' => $runSheets]);
    }
    public function deleteRunSheet(Request $request)
    {
        $runsheet = RunSheet::find($request->id);

        if (!$runsheet) {
            return response()->json(['success' => false, 'message' => 'Run Sheet not found']);
        }

        $shiftsCount = RunSheetJobRoster::where('run_sheet_id', $request->id)->count();

        if ($shiftsCount > 0 && $request->is_confirm !== 'yes' && !$request->has('is_confirm')) {
            return response()->json(['success' => false, 'message' => 'This run sheet has shifts. Do you really want to delete this run sheet?']);
        }

        if (($request->has('is_confirm') && $request->is_confirm == 'yes') || ((!$request->has('is_confirm') && $shiftsCount == 0))) {
            $runsheet_delete_reason = new DeleteRunSheetReason();
            $runsheet_delete_reason->runsheet_id = $runsheet->id;
            $runsheet_delete_reason->reason = $request->reason;
            $runsheet_delete_reason->runsheet_name = $runsheet->title;
            $runsheet_delete_reason->action_by = $request->admin_id;
            $runsheet_delete_reason->save();
        }

        // Common deletion and action logging
        RunSheetJobRoster::where('run_sheet_id', $runsheet->id)->delete();
        jobRosterActions($request->admin_id, 'delete_run_sheet', $runsheet->id, 'run_sheet', $runsheet);
        $runsheet->runSheetDetails()->delete();
        $runsheet->delete();

        return response()->json(['success' => true, 'message' => 'Run sheet Deleted']);
    }
    public function runSheetRostersCustomers(Request $request)
    {
        $newJobRoster = RunSheetRoster::where('id', $request->id)->first();
        if(!empty($newJobRoster->customers)){
            $newJobRosterCustomers = Customer::where('id', $newJobRoster->customers)->get();
            $cus = CustomerNameResource::collection($newJobRosterCustomers);
            return response()->json(['success' => true, 'data' => $cus, 'roster_name' => $newJobRoster->name, 'roster_status' => $newJobRoster->status]);
        }else{
            return response()->json(['success' => true, 'data' => '']);
        }
      
    }
    public function getDeleteRunSheetReasons(){
        $reasons = DeleteRunSheetReason::select('runsheet_name', 'reason', 'created_at', 'action_by')->get();
        if($reasons){
        $r = GetDeleteRunSheetResource::collection($reasons);
          return response()->json(['success' => true, 'data' => $r]);
        }else{
          return response()->json(['success' => false, 'data' => '']);
        }   
    }
}