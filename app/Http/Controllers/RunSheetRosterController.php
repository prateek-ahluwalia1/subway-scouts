<?php

namespace App\Http\Controllers;

use App\Http\Resources\EditJobNewRosterResource;
use App\Http\Resources\RunsheetRosterResource;
use App\Models\RunSheet;
use App\Models\RunSheetDetail;
use App\Models\RunSheetRoster;
use Illuminate\Http\Request;

class RunSheetRosterController extends Controller
{
    public function runsheets(Request $request) {
        $runsheets = RunSheetRoster::where('status', $request->status)->get();
        $rs = RunsheetRosterResource::collection($runsheets);
        if(count($rs) > 0){
            return response()->json(['data' => $rs,'code' => 200, 'success' => true]); 
        }else{
            return response()->json(['data' => '','code' => 200, 'success' => true]);  
        }
    }
    public function store(Request $request){ 
        $runSheetRoster = new RunSheetRoster();
        $runSheetRoster->name = $request->name;
        $runSheetRoster->start = !empty($request->start) ? dbFormate($request->start) : null;
        $runSheetRoster->end = ($request->end == '' && $request->end == null ? null : dbFormate($request->end));
        $runSheetRoster->customers = json_encode($request->customers);
        $runSheetRoster->admins = json_encode($request->admins);
        $runSheetRoster->save();
        jobRosterActions($request->admin_id, 'add_run_sheet_roster', $runSheetRoster->id, 'run_sheet_rosters');
        return response()->json(['message' => "RunSheet Created!", 'code' => 200, 'success' => true]);
    }
    public function edit(Request $request)
    {
        $editrunSheetRoster = RunSheetRoster::where('id', $request->id)->first();
        if($editrunSheetRoster){
            $ers = new RunsheetRosterResource($editrunSheetRoster);
            return response()->json(['success' => true, 'data' => $ers, 'code' => 200]);
        }else{
            return response()->json(['success' => true, 'data' => '', 'code' => 200]); 
        }
    }
    public function update(Request $request)
    {
        $updateRunSheet = RunSheetRoster::where('id', $request->id)->first();
        if($updateRunSheet){
            $updateRunSheet->name = $request->name;
            $updateRunSheet->start = !empty($request->start) ? dbFormate($request->start) : null;
            $updateRunSheet->end = ($request->end == '' && $request->end == null ? null : dbFormate($request->end));
            $updateRunSheet->customers = json_encode($request->customers);
            $updateRunSheet->admins = json_encode($request->admins);
            $updateRunSheet->save();
            jobRosterActions($request->admin_id, 'update_run_sheet_roster', $updateRunSheet->id, 'run_sheet_rosters');
            return response()->json(['message' => "RunSheet Updated", 'code' => 200, 'success' => true]);
        }else{
            return response()->json(['message' => "RunSheet not found", 'code' => 404, 'success' => false]);
        }
    }
    public function updateStatus(Request $request) {
        $runSheetRoster = RunSheetRoster::find($request->id);
        if (!$runSheetRoster) {
            return response()->json(['success' => false, 'message' => 'Runsheet roster not found', 'code' => 404]);
        }
        $runSheetRoster->status = $request->status;
        $runSheetRoster->update();
        return response()->json(['success' => true, 'message' => 'Status Updated', 'code' => 200]);
    }  
    public function deleteRunsheetDetail(Request $request){
        $details = RunSheetDetail::find($request->runsheet_detail_id);
        if($details){
            $runsheet = RunSheet::find($request->runsheet_id);
            $customers_ids = json_decode($runsheet->customer_id);
            $indexToRemove = array_search($details->customer_id, $customers_ids);
            if ($indexToRemove !== false) {
                unset($customers_ids[$indexToRemove]);
            }
            $cus_ids = array_values($customers_ids);
            $runsheet->customer_id = $cus_ids;
            $runsheet->update();
            $details->delete();
            return response()->json(['success' => true, 'message' => 'Data Deleted']);
        }else{
            return response()->json(['success' => false, 'message' => 'Data Not Found']);
        }
    }  
}
