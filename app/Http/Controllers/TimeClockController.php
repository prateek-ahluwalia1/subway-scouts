<?php

namespace App\Http\Controllers;

use App\Http\Resources\TimeClockResource;
use App\Models\JobRoster;
use Illuminate\Http\Request;

class TimeClockController extends Controller
{
    public function getTimeClockJobs(Request $request)
    {
        $query = JobRoster::query();
        $query->where('start', '<=', date('Y-m-d H:i'))->where('end', '>=', date('Y-m-d H:i'))
        ->where('guard_id', '!=', 'null')->where('guard_id', '!=', NULL)->where('guard_id', '!=', 'NULL')
        ->with('guardz', 'rosterActivity', 'greenCall', 'site');
        $record = TimeClockResource::collection($query->orderBy('start', 'asc')->get());
        return response()->json(['success' => true, 'data' => $record]);
    }
}
