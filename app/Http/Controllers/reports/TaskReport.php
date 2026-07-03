<?php

namespace App\Http\Controllers\reports;

use App\Http\Controllers\Controller;
use App\Http\Resources\MainTaskReportResource;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\InvoiceReportExport;
use DB;
use Dompdf\Dompdf;
use App\Models\JobRoster;
use Carbon\Carbon;

class TaskReport extends Controller
{
    function getTaskReportData(Request $request)
    {
        if($request->has('date') && $request->date != '')
        {
            $date = explode(' - ', $request->date);
            $start = dbFormate($date[0]. ' 00:00');
            $end = dbFormate($date[1]. ' 23:59');

        }else{
            $start = Carbon::now()->startOfWeek()->toDateString(); 
            $start = date('Y-m-d 00:00', strtotime($start));
            $end = Carbon::now()->endOfWeek()->toDateString();
            $end = date('Y-m-d 23:59', strtotime($end));
        }

        // $query = JobRoster::join('sites', 'sites.id', '=', 'job_rosters.site_id')
        // ->join('guards', 'guards.id', '=', 'job_rosters.guard_id')
        // ->join('customers', 'customers.id', '=', 'sites.customer_id')
        // ->join('job_roster_tasks', 'job_roster_tasks.job_roster_id', '=', 'job_rosters.id');

        // if($request->has('customer_ids') && !empty($request->customer_ids))
        // {
        //     $query->whereIn('customers.id', $request->customer_ids);
        // }
        // if($request->has('sites_ids') && !empty($request->sites_ids))
        // {
        //     $query->whereIn('job_rosters.site_id', $request->sites_ids);
        // }
        // if($request->has('guard_ids') && !empty($request->guard_ids))
        // {
        //     $query->whereIn('job_rosters.guard_id', $request->guard_ids);
        // }
        // if($request->has('state') && !empty($request->state))
        // {
        //     $query->whereIn('sites.state', $request->state);
        // }
        // if($request->has('site_type') && !empty($request->site_type))
        // {
        //     $query->where('sites.site_status', $request->site_status);
        // }
        // // , 'customers.name as customer_name'

        // $query->whereDate('job_rosters.start', $start);
        // $query->select('job_rosters.id', 'job_rosters.start', 'job_rosters.end', 'guards.first_name', 'guards.last_name', 'guards.middle_name', 'job_rosters.guard_id', 'job_rosters.site_id', 'sites.site_name', DB::raw('COUNT(job_roster_tasks.id) AS total_tasks'));
        // $query->groupBy('job_rosters.id');
        // $data = $query->get();

        $data = JobRoster::whereDate('start', '>=', $start)
        ->whereDate('start', '<=', $end)->with(['site', 'guardz', 'site.customer', 'jobRosterTask'])
        ->whereHas('site.customer', function ($query) use ($request) {
            if ($request->has('customer_ids') && !empty($request->customer_ids)) {
                $query->whereIn('customers.id', $request->customer_ids);
            }
        })
        ->whereHas('site', function ($query) use ($request) {
            if ($request->has('sites_ids') && !empty($request->sites_ids)) {
                $query->whereIn('site_id', $request->sites_ids);
            }
            if ($request->has('state') && !empty($request->state)) {
                $query->whereIn('state', $request->state);
            }
            if ($request->has('site_type') && !empty($request->site_type)) {
                $query->where('site_status', $request->site_type);
            }
        })
        ->whereHas('guardz', function ($query) use ($request) {
            if ($request->has('guard_ids') && !empty($request->guard_ids)) {
                $query->whereIn('guard_id', $request->guard_ids);
            }
        })->groupBy('id')->get();
        $data = $data->filter(function ($item) {
            return $item->jobRosterTask->count() > 0;
        });
        if (count($data) > 0) {
            $dt = MainTaskReportResource::collection($data);
            return response()->json(['success' =>  true, 'code' => 200, 'message' => 'Task data retrieve successfully.', 'data' => $dt]);

        }else{
            return response()->json(['success' =>  false, 'code' => 200, 'message' => 'No task data found!', 'data' => '']);

        }
    }
    function generateTaskReport(Request $request)
    {
        $query = JobRoster::join('sites', 'sites.id', '=', 'job_rosters.site_id')
        ->join('guards', 'guards.id', '=', 'job_rosters.guard_id')
        ->join('customers', 'customers.id', '=', 'sites.customer_id')
        ->join('job_roster_tasks', 'job_roster_tasks.job_roster_id', '=', 'job_rosters.id')
        ->where('job_rosters.id', $request->id);

    $query->select('job_rosters.id', 'job_rosters.start', 'job_rosters.end', 
        'guards.first_name', 'guards.last_name', 'guards.middle_name', 'job_rosters.guard_id', 'job_rosters.site_id',
        'sites.site_name', 'job_roster_tasks.id AS task_id','job_roster_tasks.task AS task', 
        'job_roster_tasks.task_start AS task_start', 'job_roster_tasks.task_end AS task_end', 
        'job_roster_tasks.status', 'job_roster_tasks.start_location', 'job_roster_tasks.end_location', 
        'customers.name', 'job_roster_tasks.start_time', 'job_roster_tasks.end_time');
        

        $data = $query->get();
        $name = '';
        $html = view('task-report', compact('data'));
        // echo $html;
        // exit;
        $dompdf = new Dompdf();
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        $output = $dompdf->output();
        $public_path = public_path();
        $public_path = str_replace('247StaffingSolution/public/', '', $public_path);
        $folder ='/task_report';
        $path = $public_path.$folder;
        $file_name = time() . '_task_report.pdf';
        $result = file_put_contents($path.'/'.$file_name, $output);
        $name = $file_name;
        return response()->json(['success' =>  true, 'message' => 'Task Report generate successfully.','path' => 'https://'.request()->getHttpHost().'/task_report/'.$name]);
    }
}
