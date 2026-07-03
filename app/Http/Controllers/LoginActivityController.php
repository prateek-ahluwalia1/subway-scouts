<?php

namespace App\Http\Controllers;

use App\Http\Resources\LoginActiviteResource;
use App\Models\JobRosterAction;
use Illuminate\Http\Request;
use Carbon\Carbon;

class LoginActivityController extends Controller
{
    public function getAdminActivity(Request $request)
    {
        if(!$request->has('action_on') && empty($request->action_on)){
            return response()->json(['success' => false, 'msg' => 'Please first select Action On!'], 404);
        }

        $model = JobRosterAction::query();

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
    if($request->has('action_type') && $request->action_type != ''){
        $model->whereIn('action_type', $request->action_type);
    }
    
    $action_on = $request->has('action_on') && !empty($request->action_on) ? $request->action_on : 'job_roster'; 
    $activites = $model->whereDate('created_at', '>=', $start)
    ->whereDate('created_at', '<=', $end)
    ->where('action_by', $request->login_user_id)
    ->where('action_on', $action_on)
    ->latest()
    ->get();

    $acts = LoginActiviteResource::collection($activites);
    return response()->json(['success' => true, 'data' => $acts]);

    }
    
}
