<?php

namespace App\Http\Controllers\reports;

use App\Http\Controllers\Controller;
use DateTime;
use Illuminate\Http\Request;
use DB;
use Dompdf\Dompdf;

class IncidentReport extends Controller
{

    public function get_incident_report(Request $request){
        
        $date = $request->date;

        if (strpos($date, ' - ') !== false) {
            // Split the date range
            $date = explode(' - ', $date);
            
            // Convert from 'm-d-Y' to timestamp
            $from = DateTime::createFromFormat('m-d-Y', trim($date[0]))->getTimestamp();
            $to = DateTime::createFromFormat('m-d-Y', trim($date[1]))->getTimestamp();
            
            // Format the output as 'd/m/Y'
            $startDate = date('d/m/Y', $from);
            $endDate = date('d/m/Y', $to);
        } else {
            // Case: Single date
            $from = DateTime::createFromFormat('m-d-Y', trim($date))->getTimestamp();
            $startDate = date('d/m/Y', $from);
            $endDate = $startDate; // Same for both
        }
        
    
        $report = DB::table('incident_reports')
        ->join('guards', 'guards.id', '=', 'incident_reports.guard_id')
        ->join('sites', 'sites.id','=','incident_reports.job_id')
        ->join('customers', 'customers.id','=','sites.customer_id')
        ->join('job_rosters', 'job_rosters.id', '=', 'incident_reports.roster_id')
        ->select('guards.first_name AS Guard_name', 'guards.last_name AS last_name', 'incident_reports.id AS incident_id'
            ,'incident_reports.incident_date','incident_reports.photo AS incident_image','incident_reports.incident_time AS incident_time', 'incident_reports.roster_id', 'sites.site_name', 'job_rosters.start', 'job_rosters.end', 'customers.name as customer_name')
        ->where('incident_reports.job_id',$request->site_id)
        ->whereBetween('incident_reports.incident_date', [$startDate, $endDate])
        ->orderBy('incident_reports.created_at', 'desc')
        ->get();
        

        $group = array();
        foreach ($report as $r) {
            // Ensure incident_image is a valid JSON array, else fallback to an empty array
            $images = json_decode($r->incident_image, true);
        
            if (is_array($images)) {
                foreach ($images as $key => $i) {
                    if (is_string($i)) {
                        $images[$key] = asset('incident') . '/' . $i;
                    }
                }
            } else {
                // If decoding fails, set images to an empty array
                $images = [];
            }
        
            $r->incident_image = $images;
            $r->incident_time = date("H:i", strtotime($r->incident_time));
            $r->incident_date = date("d-m-Y", strtotime($r->incident_date));
            $r->job_date = date("d-m-Y", strtotime($r->start));
            $r->job_start = date("H:i", strtotime($r->start));
            $r->job_end = date("H:i", strtotime($r->end));
        
            if (!isset($group[$r->roster_id])) {
                $group[$r->roster_id] = [
                    'Guard_name' => $r->Guard_name . ' ' . $r->last_name,
                    'site_name' => $r->site_name,
                    'customer_name' => $r->customer_name,
                    'job_date' => $r->job_date,
                    'job_start' => $r->job_start,
                    'job_end' => $r->job_end,
                    'incident' => [$r] // Array format
                ];
            } else {
                $group[$r->roster_id]['incident'][] = $r;
            }
        }
        
        $groups = array();
        foreach ($group as $key => $g) {
            $groups[] = $g;
        }
        if (count($groups) > 0) {
            return  response()->json(['success' => true, 'message' => 'Report Found.', 'data' => $groups]);
        }else{
            return  response()->json(['success' => false, 'message' => 'No report found!', 'data' => $groups]);
        }
    }

    public function generateIncidentReport(Request $request)
    {
        $report = DB::table('incident_reports')
        ->leftJoin('guards', 'guards.id', '=', 'incident_reports.guard_id')
        ->leftJoin('sites', 'sites.id','=','incident_reports.job_id')
        ->leftJoin('customers', 'customers.id','=','sites.customer_id')
        ->leftJoin('job_rosters', 'job_rosters.id', '=', 'incident_reports.roster_id')
        ->where('incident_reports.id', $request->incident_id)->select('incident_reports.*', 'guards.first_name as guard_name', 'guards.last_name', 'guards.middle_name', 'guards.phone', 'sites.address', 'sites.site_name', 'sites.site_description', 'job_rosters.start', 'job_rosters.end', 'customers.name as customer_name')->first();
        if($report){
        
        $report->people_involved = json_decode($report->people_involved, true);
        //dd($report->people_involved);
        $name = '';
        $html = view('incident-report', ['report' => $report]);
        // echo $html;
        // exit;
        $dompdf = new Dompdf();
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        $output = $dompdf->output();
        $public_path = public_path();
        $public_path = str_replace('247StaffingSolution/public/', '', $public_path);
        $folder ='/incident';
        $path = $public_path.$folder;
        $file_name = time() . '_incident_report.pdf';
        $result = file_put_contents($path.'/'.$file_name, $output);
        $name = $file_name;
        $transient_file = DB::table('transient_files')->insert([
            'folder' => 'incident',
            'file_name' => $name,  
        ]);
        return response()->json(['success' =>  true, 'message' => 'Incident Report generate successfully.','path' => 'https://'.request()->getHttpHost().'/incident/'.$name]);
        }else{
            return response()->json(['success' =>  false, 'message' => 'Incident Report not found']);
        }
    }
   public function generateFootPatrolReport(Request $request)
{   
    $report = DB::table('foot_patrol_reports')
        ->leftJoin('guards', 'guards.id', '=', 'foot_patrol_reports.guard_id')
        ->leftJoin('sites', 'sites.id','=','foot_patrol_reports.job_id')
        ->leftJoin('customers', 'customers.id','=','sites.customer_id')
        ->leftJoin('job_rosters', 'job_rosters.id', '=', 'foot_patrol_reports.roster_id')
        ->where('foot_patrol_reports.id', $request->id)
        ->select(
            'foot_patrol_reports.*', 
            'guards.first_name as guard_name', 
            'guards.last_name', 
            'guards.middle_name', 
            'guards.phone', 
            'sites.address', 
            'sites.site_name', 
            'sites.site_description', 
            'job_rosters.start', 
            'job_rosters.end', 
            'customers.name as customer_name'
        )
        ->first();

    if ($report) {
        $name = '';
        $html = view('foot-patrol-report', ['report' => $report])->render();

        $dompdf = new Dompdf();
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        $output = $dompdf->output();

        // Define public path correctly
        $public_path = public_path('footpatrol');
        
        // Ensure directory exists
        if (!file_exists($public_path)) {
            mkdir($public_path, 0777, true); // Create directory with full permissions
        }

        // Generate file name
        $file_name = time() . '_daily_shift_report.pdf';
        $file_path = $public_path . '/' . $file_name;

        // Save PDF
        $result = file_put_contents($file_path, $output);

        if ($result === false) {
            return response()->json(['success' => false, 'message' => 'Failed to save the Foot Daily Report.']);
        }

        // Add to transient_files table
        DB::table('transient_files')->insert([
            'folder' => 'footpatrol',
            'file_name' => $file_name,  
        ]);

        return response()->json([
            'success' => true, 
            'message' => 'Daily Report generated successfully.',
            'path' => url('footpatrol/' . $file_name),
        ]);
    } else {
        return response()->json(['success' => false, 'message' => 'Daily Report not found']);
    }
}

}
