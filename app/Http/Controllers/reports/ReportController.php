<?php

namespace App\Http\Controllers\reports;

use App\Http\Controllers\Controller;
use App\Models\Guard;
use App\Http\Resources\TimeSheetDetailsResource;
use App\Models\Customer;
use App\Models\QrScanner;
use Dompdf\Options;
use App\Models\GuardTimeSheetComment;
use App\Models\Site;
use Dompdf\Dompdf;
use Illuminate\Http\Request;
use App\Models\JobRoster;
use App\Models\InductionHistory;
use Carbon\Carbon;
use DB;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\TimesheetReportExport;
use App\Exports\InductionReportExport;
use App\Exports\JobtrackerReportExport;
use App\Models\AuditReport;
use App\Models\PatrollingReport;

class ReportController extends Controller
{
    function generateTimesheetReport()
    {
        $filename = time().'_time_report.xlsx';
        Excel::store(new TimesheetReportExport, 'excel/guard/'.$filename, 'excels');
        return response()->json(['success' =>  true, 'message' => 'Timesheet Report generated successfully.','path' => 'https://'.request()->getHttpHost().'/excel/guard/'.$filename]);
    }
    function generateTimesheet($request)
    {
        ini_set('memory_limit', '280M');
        $query = JobRoster::query();
        if ($request['start'] != '') {
            $start = dbFormate($request['start']);
        } else {
            $start = Carbon::now()->startOfWeek()->toDateString();
        }
        if ($request['end'] != '') {
            $end = dbFormate($request['end']);
        } else {
            $end = Carbon::now()->endOfWeek()->toDateString();
        }
        if (!empty($request['guard_id'])) {
            $query->join('guards as g1', 'g1.id', '=', 'job_rosters.guard_id');
            $query->whereIn('job_rosters.guard_id', $request['guard_id']);
        }
        if (!empty($request['customer_ids'])) {
            $sites_id = Site::whereIn('customer_id', $request['customer_ids'])->select('id')->get()->toArray();
            $query->join('sites', 'sites.id', '=', 'job_rosters.site_id');
            $query->whereIn('job_rosters.site_id', $sites_id);
        }
        if (!empty($request['sites_ids'])) {
            $query->join('sites', 'sites.id', '=', 'job_rosters.site_id');
            $query->whereIn('job_rosters.site_id', $request['sites_ids']);
        }
        if (!empty($request['state'])) {
            $query->join('sites as s', 's.id', '=', 'job_rosters.site_id')
                ->whereIn('s.state', $request['state']);
        } 
        // $timesheet = $query->where(function ($q) {
        //     $q->where('job_status', 'completed');
        //     $q->orWhere('job_rosters.admin_approved', 1);
        // })
        $timesheet = $query->leftjoin('guards', 'guards.id', '=', 'job_rosters.guard_id')
        ->leftjoin('sites as selected_site', 'selected_site.id', '=', 'job_rosters.site_id')
        ->leftjoin('customers', 'customers.id', '=', 'selected_site.customer_id')
        ->leftjoin('job_roster_activites', 'job_roster_activites.job_roster_id', '=', 'job_rosters.id')
        ->leftjoin('users', 'users.id', '=', 'job_rosters.admin_approved_by')
        ->select(
            'job_rosters.id As shift_id',
            'selected_site.site_name As site_name',
            'selected_site.break As break',
            'selected_site.state As state',
            'selected_site.break As break',
            'selected_site.break_chargeable As break_chargeable',
            'selected_site.break_payable As break_payable',
            'customers.name As customer_name',
            'job_roster_activites.signin_time As signin_time',
            'job_roster_activites.signout_time As signout_time',
            'job_rosters.unprofile_name as mock_shift',
            'guards.id',
            'guards.first_name',
            'guards.middle_name',
            'guards.last_name',
            'guards.phone',
            'guards.guard_type',
            'job_rosters.in_paysheet',
            'job_rosters.operation_notes',
            'job_rosters.travel_time_value',
            'job_rosters.admin_approved_by',
            'job_rosters.start as schedule_start_date',
            'job_rosters.end as schedule_end_date',
            'job_rosters.start',
            'users.name as status_change_by',
            DB::raw('SUM(job_rosters.morning_hours) AS morning_hours'),
            DB::raw('SUM(job_rosters.night_hours) AS night_hours'),
            DB::raw('SUM(job_rosters.saturday_morning_hours) AS saturday_morning_hours'),
            DB::raw('SUM(job_rosters.saturday_night_hours) AS saturday_night_hours'),
            DB::raw('SUM(job_rosters.sunday_morning_hours) AS sunday_morning_hours'),
            DB::raw('SUM(job_rosters.sunday_night_hours) AS sunday_night_hours'),
            DB::raw('SUM(job_rosters.ph_morning_hours) AS ph_morning_hours'),
            DB::raw('SUM(job_rosters.ph_night_hours) AS ph_night_hours'),
            DB::raw('SUM(job_rosters.hours) AS hours')
        )
        ->whereDate('job_rosters.start', '>=', $start)
        ->whereDate('job_rosters.start', '<=', $end)
        ->groupBy('job_rosters.id')
        ->groupBy('guards.first_name')
        ->groupBy('guards.middle_name')
        ->groupBy('guards.last_name')
        ->orderBy('job_rosters.start')
        ->get();
     foreach ($timesheet as $key => $t) {
    $total_hours = explode('.', $t['hours']);
    
    // Check if the decimal part exists
    $partial = isset($total_hours[1]) ? '.' . $total_hours[1] : '';

    if (is_float($t['hours'])) {
        if (empty($partial) || $partial < 0.1) {
            $timesheet[$key]['hours'] = $total_hours[0];
        } elseif ($partial < 0.27 && $partial > 0.1) {
            $timesheet[$key]['hours'] = $total_hours[0] . '.25';
        } elseif ($partial > 0.27 && $partial < 0.52) {
            $timesheet[$key]['hours'] = $total_hours[0] . '.5';
        } elseif ($partial > 0.52 && $partial < 0.77) {
            $timesheet[$key]['hours'] = $total_hours[0] . '.75';
        } elseif ($partial > 0.77 && $partial < 1) {
            $timesheet[$key]['hours'] = $total_hours[0] + 1;
        }
    }
}


        return $timesheet;
        // $name = '';
        // $html = view('time-sheet', compact('timesheet'));
        // $dompdf = new Dompdf();
        // $dompdf->loadHtml($html);
        // $dompdf->setPaper('A4', 'portrait');
        // $dompdf->render();
        // $output = $dompdf->output();
        // $public_path = public_path();
        // $public_path = str_replace('247StaffingSolution/public/', '', $public_path);
        // $folder ='/time_sheet';
        // $path = $public_path.$folder;
        // $file_name = time() . '.pdf';
        // $result = file_put_contents($path.'/'.$file_name, $output);
        // $name = $file_name;
        // # ADD TO HISTORY TABLE TO DELETE AFTER 1 MONTH
        // $transient_file = DB::table('transient_files')->insert([
        //     'folder' => 'time_sheet',
        //     'file_name' => $name,  
        // ]);
        // if ($result) {
        //     return $name;
        // }else {
        //     return '';
        // }
    }
    function getTimesheet(Request $request)
    {
    
        $limit = 10;
        $offset = 0;
        if ($request->has('pageIndex') && $request->has('pageSize')) {
            $offset = $request->pageIndex * $request->pageSize;
            $limit = $request->pageSize;
        }
    
        $query = JobRoster::query();
    
        if ($request->has('start') && $request->start != '') {
            $start = dbFormate($request->start);
        } else {
            $start = Carbon::now()->startOfWeek()->toDateString();
        }
        if ($request->has('end') && $request->end != '') {
            $end = dbFormate($request->end);
        } else {
            $end = Carbon::now()->endOfWeek()->toDateString();
        }

        if ($request->has('guard_id') && !empty($request->guard_id)) {
            $query->join('guards as g1', 'g1.id', '=', 'job_rosters.guard_id');
            $query->whereIn('job_rosters.guard_id', $request->guard_id);
        }
        if ($request->has('customer_ids') && !empty($request->customer_ids)) {
            $sites_id = Site::whereIn('customer_id', $request->customer_ids)->select('id')->get()->toArray();
            $query->join('sites', 'sites.id', '=', 'job_rosters.site_id');
            $query->whereIn('job_rosters.site_id', $sites_id);
            // $query->join('customers', 'customers.id', '=', 'sites.customer_id');
            // $query->whereIn('customers.id', $request->customer_ids);
        }

        if ($request->has('sites_ids') && !empty($request->sites_ids)) {
            $query->join('sites', 'sites.id', '=', 'job_rosters.site_id');
            $query->whereIn('job_rosters.site_id', $request->sites_ids);
        }
        if ($request->has('state') && !empty($request->state)) {
            $query->join('sites as s', 's.id', '=', 'job_rosters.site_id')
                ->whereIn('s.state', $request->state);
        }
    
        $total = $query
        // ->where(function ($q) {
        //     $q->where('job_status', 'completed');
        //     $q->orWhere('job_rosters.admin_approved', 1);
        // })
        ->leftjoin('guards as g2', 'g2.id', '=', 'job_rosters.guard_id')
            ->select(
                'job_rosters.id',
                'job_rosters.start',
                'job_rosters.end',
                'g2.id',
                'g2.first_name',
                'g2.middle_name',
                'g2.last_name',
                'job_rosters.unprofile_name',
                DB::raw('SUM(job_rosters.morning_hours) AS morning_hours'),
                DB::raw('SUM(job_rosters.night_hours) AS night_hours'),
                DB::raw('SUM(job_rosters.saturday_morning_hours) AS saturday_morning_hours'),
                DB::raw('SUM(job_rosters.saturday_night_hours) AS saturday_night_hours'),
                DB::raw('SUM(job_rosters.sunday_morning_hours) AS sunday_morning_hours'),
                DB::raw('SUM(job_rosters.sunday_night_hours) AS sunday_night_hours'),
                DB::raw('SUM(job_rosters.ph_morning_hours) AS ph_morning_hours'),
                DB::raw('SUM(job_rosters.ph_night_hours) AS ph_night_hours'),
                DB::raw('SUM(job_rosters.hours) AS hours')
            )
            ->whereDate('job_rosters.start', '>=', $start)
            ->whereDate('job_rosters.start', '<=', $end)
            ->groupBy('job_rosters.id')
            ->groupBy('g2.first_name')
            ->groupBy('g2.middle_name')
            ->groupBy('g2.last_name')
            ->whereNull('job_rosters.deleted_at')
            ->whereNotNull('job_rosters.guard_id')
            ->get()
            ->count();
    
    
        $timesheet = $query
        // ->where(function ($q) {
        //     $q->where('job_status', 'completed');
        //     $q->orWhere('job_rosters.admin_approved', 1);
        // })
        ->leftjoin('guards', 'guards.id', '=', 'job_rosters.guard_id')
            // ->skip($offset)
            // ->take($limit)
            ->select(
                'job_rosters.id As shift_id',
                 'job_rosters.start',
                'job_rosters.end',
                'guards.id',
                'guards.first_name',
                'guards.middle_name',
                'guards.last_name',
                'job_rosters.unprofile_name',
                'job_rosters.in_paysheet',
                DB::raw('SUM(job_rosters.morning_hours) AS morning_hours'),
                DB::raw('SUM(job_rosters.night_hours) AS night_hours'),
                DB::raw('SUM(job_rosters.saturday_morning_hours) AS saturday_morning_hours'),
                DB::raw('SUM(job_rosters.saturday_night_hours) AS saturday_night_hours'),
                DB::raw('SUM(job_rosters.sunday_morning_hours) AS sunday_morning_hours'),
                DB::raw('SUM(job_rosters.sunday_night_hours) AS sunday_night_hours'),
                DB::raw('SUM(job_rosters.ph_morning_hours) AS ph_morning_hours'),
                DB::raw('SUM(job_rosters.ph_night_hours) AS ph_night_hours'),
                DB::raw('SUM(job_rosters.hours) AS hours')
            )
            ->whereDate('job_rosters.start', '>=', $start)
            ->whereDate('job_rosters.start', '<=', $end)
            ->groupBy('job_rosters.id')
            ->orderBy('guards.first_name')
            ->whereNull('job_rosters.deleted_at')
            ->get();
        
            $mainArr = [];

            foreach ($timesheet as $shift) {
                $id = $shift['id'];
                $job_hours = $this->getShiftHours(
					date('m/d/Y H:i', strtotime($shift['start'])),
					date('m/d/Y H:i', strtotime($shift['end']))
				);
                if (!isset($mainArr[$id])) {
                    $mainArr[$id] = [
                        'id' => $shift['id'],
                        'first_name' => $shift['first_name'],
                        'last_name' => $shift['last_name'],
                        'middle_name' => $shift['middle_name'],
                        'shift_id' => $shift['shift_id'],
                        'unprofile_name' => $shift['id'] == null ? 'Mock Shifts' : $shift['unprofile_name'],
                        'hours' => $shift['hours'],
                        'morning_hours' => $job_hours['morning'],
                        'night_hours' => $job_hours['night'],
                        'saturday_morning_hours' => $job_hours['saturday_morning'],
                        'saturday_night_hours' => $job_hours['saturday_night'],
                        'sunday_morning_hours' => $job_hours['sunday_morning'],
                        'sunday_night_hours' => $job_hours['sunday_night'],
                        'ph_morning_hours' => $job_hours['ph_morning'],
                        'ph_night_hours' => $job_hours['ph_night'],
                        'shift_collection' => [$shift['shift_id']],
                    ];
                } else {
                    // Update the aggregated values
                    $mainArr[$id]['hours'] += $shift['hours'];
                    $mainArr[$id]['morning_hours'] += $job_hours['morning'];
                    $mainArr[$id]['night_hours'] += $job_hours['night'];
                    $mainArr[$id]['saturday_morning_hours'] += $job_hours['saturday_morning'];
                    $mainArr[$id]['saturday_night_hours'] += $job_hours['saturday_night'];
                    $mainArr[$id]['sunday_morning_hours'] += $job_hours['sunday_morning'];
                    $mainArr[$id]['sunday_night_hours'] += $job_hours['sunday_night'];
                    $mainArr[$id]['ph_morning_hours'] += $job_hours['ph_morning'];
                    $mainArr[$id]['ph_night_hours'] += $job_hours['ph_night'];
                    $mainArr[$id]['shift_collection'][] = $shift['shift_id'];
                }
            }
            
            $timesheet = array_values($mainArr);
            
 



        if (count($timesheet) > 0) {
            return response()->json([
                'success' => true,
                'code' => 200,
                'length' => $total,
                'pageIndex' => $request->pageIndex,
                'pageSize' => $request->pageSize,
                'message' => 'Timesheet found.',
                'data' => $timesheet
            ]);
        }else{
            return response()->json([
                'success' => false,
                'code' => 200,
                'length' => $total,
                'pageIndex' => $request->pageIndex,
                'pageSize' => $request->pageSize,
                'message' => 'No timesheet found!',
                'data' => $timesheet
            ]);
        }
    }

     function getTimesheetAmg($request)
    {
        ini_set('memory_limit', '128M');

        $query = JobRoster::query();

        if ($request['start'] != '') {
            $start = dbFormate($request['start']);
        } else {
            $start = Carbon::now()->startOfWeek()->toDateString();
        }
        if ($request['end'] != '') {
            $end = dbFormate($request['end']);
        } else {
            $end = Carbon::now()->endOfWeek()->toDateString();
        }

        if (!empty($request['guard_id'])) {
            $query->join('guards as g1', 'g1.id', '=', 'job_rosters.guard_id');
            $query->whereIn('job_rosters.guard_id', $request['guard_id']);
        }
        if (!empty($request['customer_ids'])) {
            $sites_id = Site::whereIn('customer_id', $request['customer_ids'])->select('id')->get()->toArray();
            $query->join('sites', 'sites.id', '=', 'job_rosters.site_id');
            $query->whereIn('job_rosters.site_id', $sites_id);
            // $query->join('customers', 'customers.id', '=', 'sites.customer_id');
            // $query->whereIn('customers.id', $request->customer_ids);
        }
        if (!empty($request['state'])) {
            $query->join('sites as s', 's.id', '=', 'job_rosters.site_id')
                ->whereIn('s.state', $request['state']);
        }

    //     $timesheet = $query
    // ->leftjoin('guards', 'guards.id', '=', 'job_rosters.guard_id')
    // ->leftjoin('sites', 'job_rosters.site_id', '=', 'sites.id')
    // ->leftjoin('customers', 'sites.customer_id', '=', 'customers.id')
    // ->select(
    //     'job_rosters.id as shift_id',
    //     'guards.id as guard_id',
    //     'sites.site_name as site_name',
    //     'sites.level as site_level',
    //     'customers.name as customer_name',
    //     'guards.state AS guard_state',
    //     'guards.first_name',
    //     'guards.phone as guard_phone',
    //     'guards.middle_name',
    //     'guards.last_name',
    //     'job_rosters.shift_payable as payable',
    //     'job_rosters.operation_notes as notes',
    //     'job_rosters.unprofile_name',
    //     'job_rosters.in_paysheet',
    //     'job_rosters.start',
    //     'job_rosters.end',
    //     'job_rosters.morning_hours',
    //     'job_rosters.night_hours',
    //     'job_rosters.saturday_morning_hours',
    //     'job_rosters.saturday_night_hours',
    //     'job_rosters.sunday_morning_hours',
    //     'job_rosters.sunday_night_hours',
    //     'job_rosters.ph_morning_hours',
    //     'job_rosters.ph_night_hours',
    //     'job_rosters.hours'
    // )
    // ->whereDate('job_rosters.start', '>=', $start)
    // ->whereDate('job_rosters.start', '<=', $end)
    // ->whereNull('job_rosters.deleted_at')
    // ->whereNotNull('job_rosters.id')
    // ->orderByRaw("CONCAT(guards.first_name, ' ', guards.middle_name, ' ', guards.last_name) ASC")

    // ->get();

    
        $total = $query
        // ->where(function ($q) {
        //     $q->where('job_status', 'completed');
        //     $q->orWhere('job_rosters.admin_approved', 1);
        // })
        ->leftjoin('guards as g2', 'g2.id', '=', 'job_rosters.guard_id')
            ->select(
                'job_rosters.id',
                'job_rosters.start',
                'job_rosters.end',
                'g2.id',
                'g2.first_name',
                'g2.middle_name',
                'g2.last_name',
                'job_rosters.unprofile_name',
                DB::raw('SUM(job_rosters.morning_hours) AS morning_hours'),
                DB::raw('SUM(job_rosters.night_hours) AS night_hours'),
                DB::raw('SUM(job_rosters.saturday_morning_hours) AS saturday_morning_hours'),
                DB::raw('SUM(job_rosters.saturday_night_hours) AS saturday_night_hours'),
                DB::raw('SUM(job_rosters.sunday_morning_hours) AS sunday_morning_hours'),
                DB::raw('SUM(job_rosters.sunday_night_hours) AS sunday_night_hours'),
                DB::raw('SUM(job_rosters.ph_morning_hours) AS ph_morning_hours'),
                DB::raw('SUM(job_rosters.ph_night_hours) AS ph_night_hours'),
                DB::raw('SUM(job_rosters.hours) AS hours')
            )
            ->whereDate('job_rosters.start', '>=', $start)
            ->whereDate('job_rosters.start', '<=', $end)
            ->groupBy('job_rosters.id')
            ->groupBy('g2.first_name')
            ->groupBy('g2.middle_name')
            ->groupBy('g2.last_name')
            ->whereNull('job_rosters.deleted_at')
            ->whereNotNull('job_rosters.guard_id')
            ->get()
            ->count();
    
    
        $timesheet = $query
        // ->where(function ($q) {
        //     $q->where('job_status', 'completed');
        //     $q->orWhere('job_rosters.admin_approved', 1);
        // })
        ->leftjoin('guards', 'guards.id', '=', 'job_rosters.guard_id')
            // ->skip($offset)
            // ->take($limit)
            ->select(
                'job_rosters.id As shift_id',
                'job_rosters.start',
                'job_rosters.end',
                'guards.id',
                'guards.first_name',
                'guards.middle_name',
                'guards.last_name',
                'job_rosters.unprofile_name',
                'job_rosters.in_paysheet',
                DB::raw('SUM(job_rosters.morning_hours) AS morning_hours'),
                DB::raw('SUM(job_rosters.night_hours) AS night_hours'),
                DB::raw('SUM(job_rosters.saturday_morning_hours) AS saturday_morning_hours'),
                DB::raw('SUM(job_rosters.saturday_night_hours) AS saturday_night_hours'),
                DB::raw('SUM(job_rosters.sunday_morning_hours) AS sunday_morning_hours'),
                DB::raw('SUM(job_rosters.sunday_night_hours) AS sunday_night_hours'),
                DB::raw('SUM(job_rosters.ph_morning_hours) AS ph_morning_hours'),
                DB::raw('SUM(job_rosters.ph_night_hours) AS ph_night_hours'),
                DB::raw('SUM(job_rosters.hours) AS hours')
            )
            ->whereDate('job_rosters.start', '>=', $start)
            ->whereDate('job_rosters.start', '<=', $end)
            ->groupBy('job_rosters.id')
            ->orderBy('guards.first_name')
            ->whereNull('job_rosters.deleted_at')
            ->whereNotNull('job_rosters.guard_id')
            ->get();
        
            $mainArr = [];

            foreach ($timesheet as $shift) {
                $id = $shift['id'];
              $job_hours = $this->getShiftHours(
					date('m/d/Y H:i', strtotime($shift['start'])),
					date('m/d/Y H:i', strtotime($shift['end']))
				);
                if (!isset($mainArr[$id])) {
                    $mainArr[$id] = [
                        'id' => $shift['id'],
                        'first_name' => $shift['first_name'],
                        'last_name' => $shift['last_name'],
                        'middle_name' => $shift['middle_name'],
                        'shift_id' => $shift['shift_id'],
                        'unprofile_name' => $shift['id'] == null ? 'Mock Shifts' : $shift['unprofile_name'],
                        'hours' => $shift['hours'],
                        'morning_hours' => $job_hours['morning'],
                        'night_hours' => $job_hours['night'],
                        'saturday_morning_hours' => $job_hours['saturday_morning'],
                        'saturday_night_hours' => $job_hours['saturday_night'],
                        'sunday_morning_hours' => $job_hours['sunday_morning'],
                        'sunday_night_hours' => $job_hours['sunday_night'],
                        'ph_morning_hours' => $job_hours['ph_morning'],
                        'ph_night_hours' => $job_hours['ph_night'],
                        'shift_collection' => [$shift['shift_id']],
                    ];
                } else {
                    // Update the aggregated values
                    $mainArr[$id]['hours'] += $shift['hours'];
                    $mainArr[$id]['morning_hours'] += $job_hours['morning'];
                    $mainArr[$id]['night_hours'] += $job_hours['night'];
                    $mainArr[$id]['saturday_morning_hours'] += $job_hours['saturday_morning'];
                    $mainArr[$id]['saturday_night_hours'] += $job_hours['saturday_night'];
                    $mainArr[$id]['sunday_morning_hours'] += $job_hours['sunday_morning'];
                    $mainArr[$id]['sunday_night_hours'] += $job_hours['sunday_night'];
                    $mainArr[$id]['ph_morning_hours'] += $job_hours['ph_morning'];
                    $mainArr[$id]['ph_night_hours'] += $job_hours['ph_night'];
                    $mainArr[$id]['shift_collection'][] = $shift['shift_id'];
                }
            }
            
            $timesheet = array_values($mainArr);
            

        if (count($timesheet) > 0) {
            return $timesheet;
        }else{
            return $timesheet;

        }
    }

    public function getTimeSheetDetails(Request $request)
    {   
        $rosters = JobRoster::
       
        whereIn('id', $request->shift_collection)
        ->with(['site', 'guardz', 'site.customer', 'rosterActivity'])->get();

       $data = TimeSheetDetailsResource::collection($rosters);
      if (count($data) > 0) {
         return response()->json(['success' => true, 'data' => $data]);
       }
      return response()->json(['success' => false, 'data' => $data]);
    }


    public function storeGuardTimeSheetComments(Request $request)
    {
        $guardComment = new GuardTimeSheetComment();
        $guardComment->g_id = $request->guard_id;
        $guardComment->comment = $request->comment;
        $guardComment->save();
        jobRosterActions($request->admin_id, 'add_guard_timesheet_comments', $guardComment->id, 'guard_time_sheet_comments');
        return response()->json(['success' =>  true, 'code' => 200, 'message' => 'Comment Store Successfully!']);
    }

    //Patrol Report Section....
    // public function generatePatrollingPDF(Request $request)
    // {
    //     $roster = JobRoster::with(['Guards', 'Sites.customer'])->where('id', $request->roster_id)->first();

    //     if (!$roster) {
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'Roster not found'
    //         ], 404);
    //     }

    //     $site = Site::where('id', 1269)->first();

    //     $data = QrScanner::where('scan_at', '>=', $roster->start)
    //     ->where('scan_at', '<=', $roster->end)
    //     ->get()
    //     ->map(function ($scan) use ($site) {
    //         $scan->site_name = $site->site_name;
    //         return $scan;
    //     });

    //     $html = view('exports/patrolling_pdf',  [
    //         'patrolling_report' => $data,
    //         'data' => $roster
    //     ])->render();

    //     $dompdf = new Dompdf();
    //     $dompdf->loadHtml($html);
    //     $dompdf->setPaper('A4', 'portrait');
    //     $dompdf->render();
    //     $output = $dompdf->output();

    //     $folder = public_path('patrolling_pdf');
    //     if (!file_exists($folder)) {
    //         mkdir($folder, 0777, true);
    //     }

    //     $file_name = time() . '_patrolling.pdf';
    //     file_put_contents($folder . '/' . $file_name, $output);

    //     $url = url('patrolling_pdf/' . $file_name);

    //     return response()->json([
    //         'success' => true,
    //         'code'    => 200,
    //         'message' => 'Patrolling Report found.',
    //         'pdf_url' => $url
    //     ]);
    // }


    public function generatePatrollingPDF(Request $request)
    {
        try {
            ini_set('memory_limit', '1024M');
            ini_set('max_execution_time', 300);

            $roster = JobRoster::with(['Guards', 'Sites.customer'])
                ->where('id', $request->roster_id)
                ->first();

            if (!$roster) {
                return response()->json([
                    'success' => false,
                    'message' => 'Roster not found'
                ], 404);
            }

            $site = Site::where('id', $roster->site_id)->first();

            if (!$site) {
                return response()->json([
                    'success' => false,
                    'message' => 'Site not found'
                ], 404);
            }

            $data = collect();
            QrScanner::whereBetween('scan_at', [$roster->start, $roster->end])
                ->orderBy('scan_at', 'asc')
                ->chunk(100, function ($scans) use (&$data, $site) {
                    foreach ($scans as $scan) {
                        $scan->site_name = $site->site_name;
                        $data->push($scan);
                    }
                });

            if ($data->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No scanning data found for this roster.'
                ], 404);
            }

            $html = view('exports/patrolling_pdf', [
                'patrolling_report' => $data,
                'data' => $roster
            ])->render();

            $options = new Options();
            $options->set('isRemoteEnabled', true);
            $options->set('isHtml5ParserEnabled', true);
            $options->set('isPhpEnabled', true);
            $options->set('defaultFont', 'DejaVu Sans');

            $dompdf = new Dompdf($options);
            $dompdf->loadHtml($html);
            $dompdf->setPaper('A4', 'portrait');
            $dompdf->render();

            $folder = public_path('patrolling_pdf');
            if (!file_exists($folder)) {
                mkdir($folder, 0777, true);
            }

            $file_name = time() . '_patrolling.pdf';
            $file_path = $folder . '/' . $file_name;
            file_put_contents($file_path, $dompdf->output());

            $url = url('patrolling_pdf/' . $file_name);

            return response()->json([
                'success' => true,
                'code'    => 200,
                'message' => 'Patrolling Report generated successfully.',
                'pdf_url' => $url
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error generating PDF: ' . $e->getMessage(),
            ], 500);
        }
    }



    public function getTaskReport(Request $request)
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

        ->join('job_roster_tasks', 'job_roster_tasks.job_roster_id', '=', 'job_rosters.id');
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
        // , 'customers.name as customer_name'
        $query->whereDate('job_rosters.start', '>=', $start)->whereDate('job_rosters.end', '<=', $end);
        $query->select('job_rosters.id', 'job_rosters.start', 'job_rosters.end', 'guards.first_name', 'guards.last_name', 'guards.middle_name', 'job_rosters.guard_id', 'job_rosters.site_id', 'sites.site_name', 'job_roster_tasks.id AS task_id','job_roster_tasks.task AS task', 'job_roster_tasks.task_start AS task_start', 'job_roster_tasks.task_end AS task_end', DB::raw('COUNT(job_roster_tasks.id) AS total_tasks'));
        $query->groupBy('job_rosters.id');
        $query->groupBy('job_rosters.start');
        $query->groupBy('job_rosters.end');
        $query->groupBy('guards.first_name');
        $query->groupBy('guards.last_name');
        $query->groupBy('guards.middle_name');
        $query->groupBy('job_rosters.guard_id');
        $query->groupBy('job_rosters.site_id');
        $query->groupBy('sites.site_name');
        $query->groupBy('job_roster_tasks.id');
        $query->groupBy('job_roster_tasks.task');
        $query->groupBy('job_roster_tasks.task_start');
        $query->groupBy('job_roster_tasks.task_end');
        $data = $query->get();
        $url =  $this->generateTaskPDF($data);
        $url1 = returnImgPath('task_report', $url);
        if(count($data) > 0){
        $url1 = returnImgPath('task_report', $url);
        return response()->json(['success' =>  true, 'code' => 200, 'message' => 'Task Report found.', 'data' => $data, 'pdf_url' => !empty($url1) ? $url1: '']);
        }else{
                return response()->json(['success' =>  false, 'code' => 200, 'message' => 'No Task Report found!', 'data' => $data]);
            }
    }

    public function generateTaskPDF($data = null)
    {
        $name = '';
        $html = view('task-report', compact('data'));
        $dompdf = new Dompdf();
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        $output = $dompdf->output();
        $public_path = public_path();
        $public_path = str_replace('247StaffingSolution/public/', '', $public_path);
        $folder ='\task_report';
        $path = $public_path.$folder;
        $file_name = time() . '.pdf';
        $result = file_put_contents($path.'/'.$file_name, $output);
        $name = $file_name;
        # ADD TO HISTORY TABLE TO DELETE AFTER 1 MONTH
        $transient_file = DB::table('transient_files')->insert([
            'folder' => 'task_report',
            'file_name' => $name,  
        ]);
        if ($result) {
            return $name;
        }else {
            return '';
        }

    }
    public function getAuditReport(Request $request){
        $getAudits = AuditReport::whereBetween('created_at', [$request->start. ' 00:00:00', $request->end. ' 23:59:59'])
            ->with(['guardDetails', 'site', 'customer', 'admin'])
            ->get();
        return response()->json([
            'success' => true,
            'data' => $getAudits
        ]);
    }
    public function deleteAuditReport($id){
        $getAudits = AuditReport::where('id', $id)->first();
        if($getAudits){
            $getAudits->delete();
            return response()->json([
                'success' => true,
                'message' => 'Audit Deleted'
            ]);
        }else{
            return response()->json([
                'success' => false,
                'message' => 'No audit found please do reload and try again. Thanks'
            ]);

        }

    }
    public function get_search_record()
    {
       $results = DB::table('job_rosters')
            ->join('sites', 'job_rosters.site_id', '=', 'sites.id')
            ->join('guards', 'job_rosters.guard_id', '=', 'guards.id')
          //   ->leftJoin('job_roster_activities', 'job_rosters.roster_id', '=', 'job_roster_activities.job_roster_id')
            ->join('customers', 'sites.customer_id', '=', 'customers.id')
            ->leftJoin('contractors', 'sites.contractor_id', '=', 'contractors.id')
            // ->leftJoin('guard_ids', 'customers.id', '=', 'guard_ids.customer_id')
            ->select('job_rosters.*', 'sites.id AS site_id', 'sites.booking_id',
                 'sites.customer_id',
            'sites.contractor_id',
            'sites.state',
            'sites.type',
            'sites.address',
            // 'sites.details',
            'sites.site_name',
            'sites.site_description',
            'sites.level',
            'sites.payrol',
            'customers.name AS customer_name' ,
            'customers.address AS customer_address' ,
            'customers.email AS customer_email' ,
            'customers.phone AS customer_phone' ,
            'customers.charged_rates_id AS customer_charge_rate_id' ,
            'contractors.name  AS contractor_name',
            'guards.guard_type',
            'guards.name  AS guard_name' ,
            'guards.address AS guard_address' ,
            'guards.email AS guard_email' ,
            'guards.phone AS guard_phone' ,
            'guards.profile_image AS guard_image' ,
            // 'guard_ids.external_id',
            // 'guard_ids.internal_id',
            // 'customers.flat_metro_week_day',
            // 'customers.eba_metro_weekday_day'
        )->where('job_rosters.shift_chargeable', "yes");
            return $results;
    }
    private function getTimeDiff($start, $end) {
        $return =  [
            'years' => 0,
            'months' => 0,
            'days' => 0,
            'hours' => 0,
            'minutes' => 0,
            'seconds' => 0
        ];
            // Declare and define two dates
        $date1 = strtotime($start);
        $date2 = strtotime($end);
    
            // Formulate the Difference between two dates
        $diff = abs($date2 - $date1);
    
    
            // To get the year divide the resultant date into
            // total seconds in a year (365*60*60*24)
        $years = floor($diff / (365*60*60*24));
    
    
            // To get the month, subtract it with years and
            // divide the resultant date into
            // total seconds in a month (30*60*60*24)
        $months = floor(($diff - $years * 365*60*60*24)
            / (30*60*60*24));
    
    
            // To get the day, subtract it with years and
            // months and divide the resultant date into
            // total seconds in a days (60*60*24)
        $days = floor(($diff - $years * 365*60*60*24 -
            $months*30*60*60*24)/ (60*60*24));
    
    
            // To get the hour, subtract it with years,
            // months & seconds and divide the resultant
            // date into total seconds in a hours (60*60)
        $hours = floor(($diff - $years * 365*60*60*24
            - $months*30*60*60*24 - $days*60*60*24)
        / (60*60));
    
    
            // To get the minutes, subtract it with years,
            // months, seconds and hours and divide the
            // resultant date into total seconds i.e. 60
        $minutes = floor(($diff - $years * 365*60*60*24
            - $months*30*60*60*24 - $days*60*60*24
            - $hours*60*60)/ 60);
    
    
    // To get the minutes, subtract it with years,
    // months, seconds, hours and minutes
        $seconds = floor(($diff - $years * 365*60*60*24
            - $months*30*60*60*24 - $days*60*60*24
            - $hours*60*60 - $minutes*60));
    
    // Print the result
        return $return = [
            'years' => $years,
            'months' => $months,
            'days' => $days,
            'hours' => $hours,
            'minutes' => $minutes,
            'seconds' => $seconds
        ];
    } 
public function getShiftHours($start, $end, $siteID = null, $continuation = false, $public_holiday = null, $ph_duration = null) 
{
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

    if ($shift_end < $shift_start && $shift_end < 6 && $shift_end >= 1) {
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
    public function customer_report_search(Request $request)
    {
        $customer_id=$request->customer_name;
        $state=$request->state;
        $status=$request->status;
        $address=$request->address;
        $date=$request->from_to;
        $guard_id=$request->guard_name;
        // $guard_type=$request->guard_type;


        $results =   $this->get_search_record();

        if ($customer_id != null) {
            $count=1;
            foreach($customer_id as $cc_id)
            {
                if($count==1){
                    $results->where('sites.customer_id', $cc_id);


                }else{
                    $results->orWhere(function($query) use ($cc_id)  {
                        $query->where('sites.customer_id', $cc_id);

                    });
                }
                $count++;
            }
        }
        if ($state != null ) {
            $count=1;
            foreach($state as $cc_id)
            {
                if($count==1){
                    $results->where('sites.state',$cc_id);


                }else{
                    $results->orWhere(function($query) use ($cc_id)  {
                        $query->where('sites.state',$cc_id);

                    });
                }
                $count++;
            }
        }
        if ($address != null ) {
            $count=1;
            foreach($address as $cc_id)
            {
                if($count==1){
                    $results->where('sites.address',$cc_id);


                }else{
                    $results->orWhere(function($query) use ($cc_id)  {
                        $query->where('sites.address',$cc_id);

                    });
                }
                $count++;
            }
        }

        if ($status != "all" ) {
                if($status=="completed"){
                $results->where('job_rosters.job_status',"completed");

            }else{
                $results->where('job_rosters.job_status',$status);

            }
        }
        if ($guard_id != null ) {
            $count=1;
            foreach($guard_id as $cc_id)
            {
                if($count==1){
                    $results->where('job_rosters.guard_id',$cc_id);


                }else{
                    $results->orWhere(function($query) use ($cc_id)  {
                        $query->where('job_rosters.guard_id',$cc_id);

                    });
                }
                $count++;
            }
        }

        if ($date != null ) {

            $date = explode('-', $date);
            $from = strtotime(trim($date[0]));
            $to = strtotime(trim($date[1])) + 60*60*24;
            $results->where('job_rosters.start', '>=', date('Y-m-d', $from))
            ->where('job_rosters.start', '<=',date('Y-m-d', $to));
        // $results->whereBetween('job_rosters.temp_date', [$from, $to]);
                                    // print_r($results);
                                    // exit();

        }

        $results= $results->get();
        foreach($results as $result)
        {   
            $rate = 0;
            // if ($result->chargerate != null && $result->chargerate > 0) {
            //     $charge_rate = DB::table('charge_rates')->where('id', $result->chargerate)->first();
            // }elseif($result->customer_charge_rate_id !=null && $result->customer_charge_rate_id > 0){
            //     $charge_rate = DB::table('charge_rates')->where('id', $result->customer_charge_rate_id)->first();
            // }else{
            //     $charge_rate = DB::table('charge_rates')->where('level', $result->level)->where('state', $result->state)->first();
            // }

            // if (!empty($charge_rate)) {
            //     if ($result->payrol == 'Default Rates') {
            //         if ($result->type == 'metropolitan') {
            //             $rate = $charge_rate->flat_metro_flat_metro_week_day;
            //         }else{
            //             $rate = $charge_rate->flat_regional_week_day;
            //         }
            //     }else{
            //         if ($result->type == 'metropolitan') {
            //             $rate = $charge_rate->eba_metro_weekday_day;
            //         }else{
            //             $rate = $charge_rate->eba_regional_weekday_day;
            //         }
            //     }
            // }

            $result->rate = $rate;
            $result->total_charged = $result->hours * $rate;
            $hours = $this->getTimeDiff($result->start, $result->end);
            $result->hours = $hours['hours'];
            $job_hours = $this->getShiftHours(date('m/d/Y H:i', strtotime($result->start)), date('m/d/Y H:i', strtotime($result->end)));
            $result->hours = $job_hours['morning'] + $job_hours['night'] + $job_hours['saturday_morning'] + $job_hours['saturday_night'] + $job_hours['sunday_morning'] + $job_hours['sunday_night'] + $job_hours['ph_morning'] + $job_hours['ph_night'];
            $result->hours_dis = $job_hours;
            $total_hours = explode('.', $result->hours);
            if (sizeof($total_hours) > 1 ) {
                $partial = '.'.$total_hours[1];
                if ($partial < 0.1) {
                $result->hours = $total_hours[0];
            }
            if ($partial < 0.27 && $partial > 0.1) {
                $result->hours = $total_hours[0].'.25';
            }
            if ($partial > 0.27 && $partial < 0.52) {
                $result->hours = $total_hours[0].'.5';
            }
            if ($partial > 0.52 && $partial < 0.77) {
                $result->hours = $total_hours[0].'.75';
            }
            if ($partial > 0.77 && $partial < 1) {
                $result->hours = $total_hours[0]+ 1;
            }
        }
        $result->temp_date=Date("d/m/Y",strtotime($result->temp_date));

        $result->start=Date("H:i",strtotime($result->start));
        $result->end=Date("H:i",strtotime($result->end));

    }

    // return ['results'=> $results];
    return response()->json([
        'success' => true,
        'result' => $results
    ]);

    }

    function get_guard_document_report(Request $request){

        // $siteIds = [];
        // if ($request->filled('site_id')) {
        //     $siteIds = $request->site_id;
        // } elseif ($request->filled('customer_id')) {
        //     $siteIds = DB::table('sites')
        //         ->select('id')
        //         ->whereIn('customer_id', $request->customer_id)
        //         ->distinct()
        //         ->pluck('id')
        //         ->toArray();
        // }
    
        // $trained_guard = DB::table('guard_sites_trained')->whereIn('guard_sites_trained.site_id', $siteIds)
        // ->select('guard_id')
        // ->distinct()
        // ->pluck('guard_id')
        // ->toArray();
        
        // $guards = DB::table('guards')->where('status','!=','deleted')->whereIn('id', $trained_guard)->orderBy('name','ASC')->with('guardDocuments')->get();
    
        // return $guards;
           //dd($request['date']); 
        
     $siteIds = [];
        if ($request->filled('site_id')) {
            $siteIds = $request->site_id;
        } elseif ($request->filled('customer_id')) {
            $siteIds = DB::table('sites')
                ->select('id')
                ->whereIn('customer_id', $request->customer_id)
                ->distinct()
                ->pluck('id')
                ->toArray();
        }
    
        $trained_guard = DB::table('guard_trained_on_sites')->whereIn('guard_trained_on_sites.site_id', $siteIds)
        ->select('guard_id')
        ->distinct()
        ->pluck('guard_id')
        ->toArray();

    $query = Guard::query();

    $query->whereIn('id', $trained_guard);
    
    $guards = $query->with(['documents', 'empDetails'])->orderBy('first_name', 'asc')->where('guard_status', '!=', 'deleted')->get();

    $guardData = [];

    foreach ($guards as $guard) {
        $guardInfo = [
            'id' => $guard->id,
            'first_name' => !empty($guard->first_name) ? $guard->first_name : 'N/A',
            'middle_name' => $guard->middle_name,
            'last_name' => !empty($guard->last_name) ? $guard->last_name : '',
            'email' => !empty($guard->email) ? $guard->email : 'N/A',
            'phone' => !empty($guard->phone) ?  $guard->phone : 'N/A',
        ];

        $documentTypes = [
            'passport',
            'visa',
        ];

        foreach ($documentTypes as $documentType) {
           
            $found = false;
            foreach ($guard->documents as $document) {
                if ($document->document_type === $documentType) {
                    $guardInfo[$documentType . '_no'] = !empty($document->document_no) ? $document->document_no : 'N/A';
                    $guardInfo[$documentType . '_exp'] = !empty($document->document_expire) ? $document->document_expire : 'N/A';
                    $found = true;
                    break;
                }
            }
        
            if (!$found) {
                $guardInfo[$documentType . '_no'] = 'N/A';
                $guardInfo[$documentType . '_exp'] = 'N/A';
            }
        }

        // Process empDetails relationship
     
        $guardData[] = $guardInfo;
    }

    return response()->json(['data' => $guardData,  'code' => 200, 'success' => true]);

    }

    public function timesheet_search($request)
    {
 
        $customer_id = $request['customer_name'] ?? null;
        $site_id = $request['specific_sites'] ?? null;
        $state       = $request['State'] ?? null;
        $status      = $request['status'] ?? null;
        $date        = $request['from_to'] ?? null;
        $guard_id    = $request['guard_name'] ?? null;
        $guard_type  = $request['guard_type'] ?? null;


        $results =   $this->get_timesheet_record();
      
        if ($request['job_status'] == 'on') {
        $results->where(function($que) {
            
            $que->orWhere('job_rosters.admin_approved_by', '>', 0);
        });
        } elseif ($request['job_status'] == 'off') {
            $results->where('job_rosters.job_status', '!=', 'completed')
                    ->where(function($que) {
                        $que->whereNull('job_rosters.admin_approved_by')
                            ->orWhere('job_rosters.admin_approved_by', '=', '');
                    });
        }
         if ($site_id != null && $site_id != '') {
        $results->where(function ($query) use ($site_id) {
            $count = 1;
            foreach ($site_id as $key => $s_id) {
            if ($count == 1) {
                $query->where('sites.id', $s_id);
            } else {
                $query->orWhere('sites.id', $s_id);
            }
            $count++;
            }
        });
        }

        if ($customer_id != null && $customer_id != '') {
        $results->where(function ($query) use ($customer_id) {
            $count = 1;
            foreach ($customer_id as $key => $c_id) {
            if ($count == 1) {
                $query->where('sites.customer_id', $c_id);
            } else {
                $query->orWhere('sites.customer_id', $c_id);
            }
            $count++;
            }
        });
        } elseif (session()->has('specific_customer') && session()->get('specific_customer') != '') {
        $specific_customer = json_decode(session()->get('specific_customer'));
        $results->where(function ($query)  use ($specific_customer) {
            foreach ($specific_customer as $key => $id) {
            if ($key == 0) {
                $query->where('sites.customer_id', $id);
            } else {
                $query->orWhere('sites.customer_id', $id);
            }
            }
        });
        }
        if ($state != null  && $state != '') {
        $results->where(function ($query) use ($state) {
            $count = 1;
            foreach ($state as $key => $s) {
            if ($count == 1) {
                $query->where('sites.state', $s);
            } else {
                $query->orWhere('sites.state', $s);
            }
            $count++;
            }
        });
        }

        if ($guard_id != null  && $guard_id != '') {

        $results->where(function ($query) use ($guard_id) {
            $count = 1;
            foreach ($guard_id as $key => $cc_id) {
            if ($count == 1) {
                $query->where('job_rosters.guard_id', $cc_id);
            } else {
                $query->orWhere('job_rosters.guard_id', $cc_id);
            }
            $count++;
            }
        });
        }
        if (is_array($guard_type) && !empty($guard_type)) {
        $results->where(function ($query) use ($guard_type) {
            $count = 1;
            foreach ($guard_type as $key => $type) {
            if ($count == 1) {
                $query->where('guards.guard_type', $type);
            } else {
                $query->orWhere('guards.guard_type', $type);
            }
            $count++;
            }
        });
        } elseif ($guard_type != null  && $guard_type != '') {
        $results->where('guards.guard_type', $guard_type);
        }

        if ($date != null) {
            $from_to = explode("-", $date);

            $from = trim($from_to[0]);
            $to = trim($from_to[1]);

            $from_date = date("Y-m-d", strtotime($from)) . ' 00:00';
            $to_date = date("Y-m-d", strtotime($to)) . ' 23:59';

            $results->where('job_rosters.start', '>=', $from_date)->where('job_rosters.start', '<=', $to_date);
        }

        $data = $results->orderBy('job_rosters.start', 'asc')->get();

        if (!empty($data)) {
        foreach ($data as $result) {
            $result->guard_id1 = 'N/A';
            $result->guard_id2 = 'N/A';
            if ($result->guard_id == 0 || $result->guard_id == null) {
            $result->guard_name = $result->unprofile_name;
            }else{
            $guard_ids = DB::table('guard_external_ids')->where('guard_id', $result->guard_id)->get();
            foreach($guard_ids as $id)
                    {
                        if(preg_match('/AMG/i', $id->external_id)){
                            $result->guard_id1 = $id->external_id;
                        }
                        if(!preg_match('/AMG/i', $id->external_id) && ($id->external_id > 0)){
                            $result->guard_id2 = $id->external_id;
                        }
                    }
        }
            $job_end_time = strtotime($result->end);
            $job_start_time = strtotime($result->start);
        
            $hours = $this->getTimeDiff($result->start, $result->end);
            if ($hours['days'] > 0) {
            $hours['hours'] = $hours['hours'] + ($hours['days'] * 24);
            }
            if ($hours['hours'] > $result->hours) {
            $hours['hours'] = $result->hours;
            }
            if ($hours['hours'] < $result->hours) {
            $hours['hours'] = $result->hours;
            }
            $result->hours_data = $hours;
            $result->total_hours = $result->hours;

            if ($hours['hours'] == 0) {
            $temp = $hours['minutes'];
            $hours['hours'] = round($temp / 60);
            $result->hours = $hours['hours'];
            } else {
            $result->hours = $hours['hours'];
            $total_hours = explode('.', $result->hours);
            if (sizeof($total_hours) > 1) {
                $partial = '.' . $total_hours[1];
                if ($partial < 0.1) {
                $result->hours = $total_hours[0];
                }
                if ($partial < 0.27 && $partial > 0.1) {
                $result->hours = $total_hours[0] . '.25';
                }
                if ($partial > 0.27 && $partial <= 0.52) {
                $result->hours = $total_hours[0] . '.5';
                }
                if ($partial > 0.52 && $partial <= 0.77) {
                $result->hours = $total_hours[0] . '.75';
                }
                if ($partial > 0.77 && $partial < 1) {
                $result->hours = $total_hours[0] + 1;
                }
            }
            }
            $result->total_hours = $result->hours;
            $result->hours = $result->hours;

            $result->start = $result->start;
            $result->end = $result->end;
            if ($result->continuation == 0) {
            if ($result->hours < 4 && $result->hours > 0) {
                $result->hours = 4;
            }
            }
            $result->hours = $result->hours + $result->travel_time;
            $check_in = strtotime($result->start);
        }
        }

        if ($data) {
        return ['results' => $data, 'parameter_status' => $status];
        }
    }

    public function get_timesheet_record()
    {
    $results = DB::table('job_rosters')
    ->join('sites', 'job_rosters.site_id', '=', 'sites.id')
    ->leftJoin('guards', 'job_rosters.guard_id', '=', 'guards.id')
    ->leftJoin('guard_payroll_ids', 'guards.id', '=', 'guard_payroll_ids.guard_id')
    ->join('customers', 'sites.customer_id', '=', 'customers.id')
    ->leftJoin('job_roster_activites', 'job_rosters.id', '=', 'job_roster_activites.job_roster_id')
        // ->leftJoin('job_roster_activities as ja', 'job_rosters.guard_id', '=', 'ja.guard_id')
    // ->leftJoin('contractors', 'sites.contractor_id', '=', 'contractors.id')
    ->leftJoin('users', 'job_rosters.admin_approved_by', '=', 'users.id')
    ->whereNull('job_rosters.deleted_at')
    // ->orderByRaw("CONCAT(guards.first_name, ' ', guards.middle_name, ' ', guards.last_name) ASC")
    ->select('job_rosters.*', 'sites.id AS job_id', 'sites.booking_id',
        'sites.customer_id',
        'sites.contractor_id',
        'sites.state',
        'sites.type',
        'sites.address',
        'sites.site_description',
        'sites.site_name',
        // 'sites.details',
        'sites.level',
        'sites.break_payable',
        // 'sites.payable_and_chargeable_time',
        'customers.name AS customer_name' ,
        // 'contractors.name  AS contractor_name',
        'guards.guard_type',
        'guards.first_name',
        'guards.middle_name',
        'guards.last_name',
        'guards.state as guard_state',
        'guards.phone  AS guard_phone',
        'guards.id  AS guard_ID',
        // 'customers.flat_metro_week_day',
        'job_roster_activites.signin_time',
        'job_roster_activites.signout_time',
        'job_roster_activites.auto_signout',
        'sites.break',
        'sites.break_chargeable',
        'sites.site_payrate as job_payable',
        'users.name AS admin_name',
        'guard_payroll_ids.payroll_id AS guard_payroll_id' 
    );
    return $results;
    }

    function jobs_status($status,$results)
    {
    if($status=="missed")
    {
    $results = $results->where(function($query) {
        $query->where('job_rosters.job_status' , "pending")
        ->orWhere('job_rosters.job_status' , "confirmed")
        ->orWhere('job_rosters.job_status' , "rejected")
        ->orWhere('job_roster_activites.auto_signout', 1);
    });

    }
    if($status == "inprogress"){

    $results=$results->where('job_rosters.job_status' , "confirmed");

    }
    if($status == "confirmed" || $status == 'completed'){
    $results = $results->where(function($query) {
    $query->where('job_rosters.job_status' , "completed")
    ->orWhere('job_rosters.job_status' , "confirmed")
    // ->orWhere('job_rosters.job_status' , "pending")
    ->orWhere('job_rosters.admin_approved_by' , '>', 0);
    });
        
    }
    if($status == "pending"){
    $results = $results->where(function($query) {
    $query->where('job_rosters.job_status' , "pending")
    ->orWhere('job_rosters.job_status' , "rejected");
    });
    }
    return $results;

    }

    function generateJobTrackerReport(Request $request)
    {
        if($request->type == 'preview'){
            $data = $this->timesheet_search($request);
            return response()->json([
                'success' => true,
                'data' => $data,
                'status' => $request->status
            ]);
        }
        $filename = time().'_job_tracker_report.xlsx';  
        Excel::store(new JobtrackerReportExport, 'excel/guard/'.$filename, 'excels');
        return response()->json(['success' =>  true, 'message' => 'Report generate successfully.','path' => 'https://'.request()->getHttpHost().'/excel/guard/'.$filename]);
    }

    function convert_into_fraction($time)
    {
        return date('H', $time) + (date('i', $time) / 60);
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

     function generateInductionReport(Request $request)
    {
        if($request->type == 'preview'){
            $getData = $this->getReportData($request);
            return response()->json([
                'success' => true,
                'data' => $getData
            ]);
        }
        $filename = time().'_induction_report.xlsx';
        Excel::store(new InductionReportExport, 'excel/induction/'.$filename, 'excels');
        return response()->json(['success' =>  true, 'message' => 'Induction Report generated successfully.','path' => 'https://'.request()->getHttpHost().'/excel/induction/'.$filename]);
    }

   function getInductionReportData($request)
    {
        $questionnaire = DB::table('questionnaires')
            ->select('title')
            ->where('id', $request['id'])
            ->first();

        $inductionHistory = InductionHistory::select(
                'induction_history.guard_id',
                \DB::raw("CONCAT(guards.first_name, ' ', guards.last_name) as name"),
                'induction_history.state',
                \DB::raw('DATE_FORMAT(guard_questionnaire_details.updated_at, "%d-%m-%Y %H:%i") as date'),
                'induction_history.read_status',
                'guard_questionnaire_details.certificate_path'
            )
            ->join('guards', 'induction_history.guard_id', '=', 'guards.id')
            ->leftJoin('guard_questionnaire_details', function($join) use ($request) {
                $join->on('guard_questionnaire_details.guard_id', '=', 'induction_history.guard_id')
                    ->where('guard_questionnaire_details.questionnaire_id', '=', $request['id']);
            })
            ->where('induction_id', $request['id'])
            ->where('guards.guard_status', 'active')
            ->groupBy('induction_history.guard_id')
            ->orderByRaw("CONCAT(guards.first_name, ' ', guards.last_name) ASC")
            ->get();

        $responseData = $inductionHistory->map(function($item) use ($questionnaire) {
            return [
                'guard_id' => $item->guard_id,
                'name' => $item->name,
                'state' => $item->state,
                'date' => $item->date,
                'read_status' => $item->read_status,
                'certificate_path' => $item->certificate_path,
                'questionnaire_title' => $questionnaire ? $questionnaire->title : null
            ];
        });

        return $responseData;

    }
}
