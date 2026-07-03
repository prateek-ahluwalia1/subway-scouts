<?php

namespace App\Http\Controllers\reports;

use App\Exports\AwardOverTimeExport;
use App\Exports\HrsByEmpReportExport;
use App\Models\WelfareCall;
use App\Exports\GreenCallExport;
use App\Http\Controllers\Controller;
use App\Models\GuardLeave;
use App\Models\JobRoster;
use App\Models\Guard;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Collection;
use App\Models\Site;
use App\Models\Customer;
use App\Models\GreenCall;
use App\Models\WelfareCallData;
use Illuminate\Support\Facades\DB;

class GreenCallReport extends Controller
{


    function generateGreenCallReport(Request $request)
    {
        if($request->file_type == 'preview'){
            $data = $this->getReportData($request);
            return response()->json([
                'success' => true,
                'data' => $data
            ]);
        }
        $filename = time() . '_green_call_report.xlsx';
        Excel::store(new GreenCallExport, 'excel/greencall/' . $filename, 'excels');
        return response()->json(['success' => true, 'message' => 'Green Call Report.', 'path' =>
        'https://' . request()->getHttpHost() . '/excel/greencall/' . $filename]);
    }


    public function getReportData($request)
    {  
        $extra_query = '';

        if (isset($request['date']) && $request['date'] != '') {
            $date = $request['date'];
            $date = explode(' - ', $date);
            $from = strtotime(trim(str_replace('-', '/', $date[0])));
            $to = strtotime(trim(str_replace('-', '/', $date[1])));
        }else{
            $to = time();
            $from = time() - (60*60*24*14);
        }
        $startDate = date('Y-m-d 00:00', $from);
        $endDate = date('Y-m-d 23:59', $to);

        if (isset($request['customer_id']) && !empty($request['customer_id'])) {
            $siteIds = DB::table('sites')
            ->select('id')
            ->whereIn('customer_id', $request['customer_id'])
            ->distinct()
            ->get()
            ->pluck('id');
            
            $extra_query .= "(";
            $i = 0;
            foreach ($siteIds as $key => $id) {
                $extra_query .= "job_rosters.`site_id` = '".$id."'";
                if ($i < sizeof($siteIds) -1) {
                    $extra_query .= " OR ";
                }
                $i++;
            }
            $extra_query .= ") AND ";
        }
        if (isset($request['guard_id']) && !empty($request['guard_id'])) {
            $extra_query .= "(";
            $i = 0;
            foreach ($request['guard_id'] as $key => $id) {
                $extra_query .= "guards.`id` = '".$id."'";
                if ($i < sizeof($request['guard_id']) -1) {
                    $extra_query .= " OR ";
                }
                $i++;
            }
            $extra_query .= ") AND ";
        }
        if (isset($request['sites']) && $request['sites'] != '') {
            $extra_query .= "(";
            $i = 0;
            foreach ($request['sites'] as $key => $id) {
                $extra_query .= "job_rosters.`site_id` = '".$id."'";
                if ($i < sizeof($request['sites']) -1) {
                    $extra_query .= " OR ";
                }
                $i++;
            }
            $extra_query .= ") AND ";
        }
        if($request['type'] == 'Welfare Call')
        {
        if (isset($request['response']) && !empty($request['response']) && $request['response'] != '') {
            $extra_query .= "(";
            $i = 0;
            foreach ($request['response'] as $key => $id) {
                $extra_query .= "welfare_call_data.`status` = '".$id."'";
                if ($i < sizeof($request['response']) -1) {
                    $extra_query .= " OR ";
                }
                $i++;
            }
            $extra_query .= ") AND ";
        }
       }elseif($request['type'] == 'Green Call'){
        if (isset($request['response']) && !empty($request['response']) && $request['response'] != '') {
            $extra_query .= "(";
            $i = 0;
            foreach ($request['response'] as $key => $id) {
                $extra_query .= "green_call.`status` = '".$id."'";
                if ($i < sizeof($request['response']) -1) {
                    $extra_query .= " OR ";
                }
                $i++;
            }
            $extra_query .= ") AND ";
        }

       }
        
        if($request['type'] == 'Welfare Call')
        {
            $data = JobRoster::join('welfare_call_data', 'welfare_call_data.job_roster_id', '=', 'job_rosters.id')
            ->Join('job_roster_activites', 'job_roster_activites.job_roster_id', '=', 'job_rosters.id')
            ->Join('guards', 'guards.id', '=', 'welfare_call_data.guard_id')
            ->Join('sites', 'sites.id', '=', 'job_rosters.site_id')
            ->Join('customers', 'customers.id', '=', 'sites.customer_id')
            ->whereRaw($extra_query."job_rosters.`start` BETWEEN '".$startDate."' AND '".$endDate."' AND job_rosters.deleted_at IS NULL")
            ->orderBy('sites.site_name', 'ASC')
            ->orderBy('welfare_call_data.created_at', 'ASC')
            ->select(
                'job_rosters.id as roster_id',
                'guards.id as guard_id',
                'welfare_call_data.id as wf_call_id',
                'job_rosters.start as date',
                'job_rosters.start as start_time',
                'job_rosters.end as end_time',
                'job_roster_activites.signin_time',
                'job_roster_activites.signout_time',
                'guards.first_name',
                'guards.middle_name',
                'guards.last_name',
                'sites.site_name',
                'customers.name as customer_name',
                'welfare_call_data.status as response',
                'welfare_call_data.send_time',
                'welfare_call_data.response_time',
                'sites.id as site_id',
                \DB::raw("JSON_UNQUOTE(JSON_EXTRACT(welfare_call_data.admin_notes_id, '$[0].note')) as note"),
                \DB::raw("'WC' as type"),
                \DB::raw("'Welfare Call' as call_type")
            )
            ->get();

        }elseif($request['type'] == 'Green Call')
        {
            $data = JobRoster::join('green_call', 'green_call.job_id', '=', 'job_rosters.id')
            ->Join('job_roster_activites', 'job_roster_activites.job_roster_id', '=', 'job_rosters.id')
            ->Join('guards', 'guards.id', '=', 'green_call.guard_id')
            ->Join('sites', 'sites.id', '=', 'job_rosters.site_id')
            ->Join('customers', 'customers.id', '=', 'sites.customer_id')
            ->whereRaw($extra_query."job_rosters.`start` BETWEEN '".$startDate."' AND '".$endDate."' AND job_rosters.deleted_at IS NULL")
            ->orderBy('sites.site_name', 'ASC')
            ->orderBy('green_call.created_at', 'ASC')
            ->select(
                'job_rosters.id as roster_id',
                'guards.id as guard_id',
                'green_call.id as green_call_id',
                'job_rosters.start as date',
                'job_rosters.start as start_time',
                'job_rosters.end as end_time',
                'job_roster_activites.signin_time',
                'job_roster_activites.signout_time',
                'guards.first_name',
                'guards.middle_name',
                'guards.last_name',
                'sites.site_name',
                'customers.name as customer_name',
                'green_call.status as response',
                'green_call.send_time',
                'green_call.response_time',
                'sites.id as site_id',
                \DB::raw("JSON_UNQUOTE(JSON_EXTRACT(green_call.admin_notes_id, '$[0].note')) as note"),
                \DB::raw("'GC' as type"),
                \DB::raw("'Green Call' as call_type")
            )
            ->get();

        }elseif($request['type'] == 'Both')
        {
            $welfareCallDataQuery = JobRoster::join('welfare_call_data', 'welfare_call_data.job_roster_id', '=', 'job_rosters.id')
                ->Join('job_roster_activites', 'job_roster_activites.job_roster_id', '=', 'job_rosters.id')
                ->Join('guards', 'guards.id', '=', 'welfare_call_data.guard_id')
                ->Join('sites', 'sites.id', '=', 'job_rosters.site_id')
                ->Join('customers', 'customers.id', '=', 'sites.customer_id')
                ->whereRaw($extra_query."job_rosters.`start` BETWEEN '".$startDate."' AND '".$endDate."' AND job_rosters.deleted_at IS NULL")
                ->orderBy('sites.site_name', 'ASC')
                ->orderBy('welfare_call_data.created_at', 'ASC')
                ->select(
                    'job_rosters.id as roster_id',
                    'guards.id as guard_id',
                    'welfare_call_data.id as wf_call_id',
                    'job_rosters.start as date',
                    'job_rosters.start as start_time',
                    'job_rosters.end as end_time',
                    'job_roster_activites.signin_time',
                    'job_roster_activites.signout_time',
                    'guards.first_name',
                    'guards.middle_name',
                    'guards.last_name',
                    'sites.site_name',
                    'customers.name as customer_name',
                    'welfare_call_data.status as response',
                    'welfare_call_data.send_time',
                    'welfare_call_data.response_time',
                    'sites.id as site_id',
                    \DB::raw("JSON_UNQUOTE(JSON_EXTRACT(welfare_call_data.admin_notes_id, '$[0].note')) as note"),
                    \DB::raw("'WC' as type"),
                    \DB::raw("'Both' as call_type")
                );

            if (isset($request['response']['0'])) {
                $welfareCallDataQuery->where('welfare_call_data.status', '=', $request['response']['0']);
            }

            $welfareCallData = $welfareCallDataQuery->get();
        
        $greenCallDataQuery = JobRoster::join('green_call', 'green_call.job_id', '=', 'job_rosters.id')
            ->Join('job_roster_activites', 'job_roster_activites.job_roster_id', '=', 'job_rosters.id')
            ->Join('guards', 'guards.id', '=', 'green_call.guard_id')
            ->Join('sites', 'sites.id', '=', 'job_rosters.site_id')
            ->Join('customers', 'customers.id', '=', 'sites.customer_id')
            ->whereRaw($extra_query."job_rosters.`start` BETWEEN '".$startDate."' AND '".$endDate."' AND job_rosters.deleted_at IS NULL")
            ->orderBy('sites.site_name', 'ASC')
            ->orderBy('green_call.created_at', 'ASC')
            ->select(
                'job_rosters.id as roster_id',
                'guards.id as guard_id',
                'green_call.id as green_call_id',
                'job_rosters.start as date',
                'job_rosters.start as start_time',
                'job_rosters.end as end_time',
                'job_roster_activites.signin_time',
                'job_roster_activites.signout_time',
                'guards.first_name',
                'guards.middle_name',
                'guards.last_name',
                'sites.site_name',
                'customers.name as customer_name',
                'green_call.status as response',
                'green_call.send_time',
                'green_call.response_time',
                'sites.id as site_id',
                \DB::raw("JSON_UNQUOTE(JSON_EXTRACT(green_call.admin_notes_id, '$[0].note')) as note"),
                \DB::raw("'GC' as type"),
                \DB::raw("'Both' as call_type")
            );

            if (isset($request['response']['0'])) {
                $greenCallDataQuery->where('green_call.status', '=', $request['response']['0']);
            }

            $greenCallData = $greenCallDataQuery->get();
            
        
        // $data = $greenCallData->merge($welfareCallData);
        $data = $greenCallData->concat($welfareCallData);

        }
    
        return $data;
        }

        public function greenCallReportData($request)
        {  
            $extra_query = '';

            if (isset($request['date']) && $request['date'] != '') {
                $date = $request['date'];
                $date = explode(' - ', $date);
                $from = strtotime(trim(str_replace('-', '/', $date[0])));
                $to = strtotime(trim(str_replace('-', '/', $date[1])));
            }else{
                $to = time();
                $from = time() - (60*60*24*14);
            }
            $startDate = date('Y-m-d 00:00', $from);
            $endDate = date('Y-m-d 23:59', $to);
    
            if (isset($request['customer_id']) && !empty($request['customer_id'])) {
                $siteIds = DB::table('sites')
                ->select('id')
                ->whereIn('customer_id', $request['customer_id'])
                // ->limit(25)
                ->distinct()
                ->pluck('id');
                
                $extra_query .= "(";
                $i = 0;
                foreach ($siteIds as $key => $id) {
                    $extra_query .= "job_rosters.`site_id` = '".$id."'";
                    if ($i < sizeof($siteIds) -1) {
                        $extra_query .= " OR ";
                    }
                    $i++;
                }
                $extra_query .= ") AND ";
            }
            if (isset($request['guard_id']) && !empty($request['guard_id'])) {
                $extra_query .= "(";
                $i = 0;
                foreach ($request['guard_id'] as $key => $id) {
                    $extra_query .= "guards.`id` = '".$id."'";
                    if ($i < sizeof($request['guard_id']) -1) {
                        $extra_query .= " OR ";
                    }
                    $i++;
                }
                $extra_query .= ") AND ";
            }
            if (isset($request['sites']) && $request['sites'] != '') {
                $extra_query .= "(";
                $i = 0;
                foreach ($request['sites'] as $key => $id) {
                    $extra_query .= "job_rosters.`site_id` = '".$id."'";
                    if ($i < sizeof($request['sites']) -1) {
                        $extra_query .= " OR ";
                    }
                    $i++;
                }
                $extra_query .= ") AND ";
            }
          
            
            if($request['type'] == 'Both')
            {
                $greenCallDataQuery = JobRoster::join('green_call', 'green_call.job_id', '=', 'job_rosters.id')
                ->Join('job_roster_activites', 'job_roster_activites.job_roster_id', '=', 'job_rosters.id')
                ->Join('guards', 'guards.id', '=', 'green_call.guard_id')
                ->Join('sites', 'sites.id', '=', 'job_rosters.site_id')
                ->Join('customers', 'customers.id', '=', 'sites.customer_id')
                ->whereRaw($extra_query."job_rosters.`start` BETWEEN '".$startDate."' AND '".$endDate."' AND job_rosters.deleted_at IS NULL")
                ->orderBy('sites.site_name', 'ASC')
                ->orderBy('green_call.created_at', 'ASC')
                ->select(
                    'job_rosters.id as roster_id',
                    'guards.id as guard_id',
                    'green_call.id as green_call_id',
                    'job_rosters.start as date',
                    'job_rosters.start as start_time',
                    'job_rosters.end as end_time',
                    'job_roster_activites.signin_time',
                    'job_roster_activites.signout_time',
                    'guards.first_name',
                    'guards.middle_name',
                    'guards.last_name',
                    'sites.site_name',
                    'customers.name as customer_name',
                    'green_call.status as response',
                    'green_call.send_time',
                    'green_call.response_time',
                    'sites.id as site_id',
                    \DB::raw("JSON_UNQUOTE(JSON_EXTRACT(green_call.admin_notes_id, '$[0].note')) as note"),
                    \DB::raw("'GC' as type"),
                    \DB::raw("'Green Call' as call_type")
                );
    
                if (isset($request['response']['0'])) {
                    $greenCallDataQuery->where('green_call.status', '=', $request['response']['0']);
                }
    
                $data = $greenCallDataQuery->get();

                
    
            }
        
            return $data;
            }

            public function welfareCallReportData($request)
            {  
                $extra_query = '';

                if (isset($request['date']) && $request['date'] != '') {
                    $date = $request['date'];
                    $date = explode(' - ', $date);
                    $from = strtotime(trim(str_replace('-', '/', $date[0])));
                    $to = strtotime(trim(str_replace('-', '/', $date[1])));
                }else{
                    $to = time();
                    $from = time() - (60*60*24*14);
                }
                $startDate = date('Y-m-d 00:00', $from);
                $endDate = date('Y-m-d 23:59', $to);

                if (isset($request['customer_id']) && !empty($request['customer_id'])) {
                    $siteIds = DB::table('sites')
                    ->select('id')
                    ->whereIn('customer_id', $request['customer_id'])
                    // ->limit(25)
                    ->distinct()
                    ->get()
                    ->pluck('id');
                    
                    $extra_query .= "(";
                    $i = 0;
                    foreach ($siteIds as $key => $id) {
                        $extra_query .= "job_rosters.`site_id` = '".$id."'";
                        if ($i < sizeof($siteIds) -1) {
                            $extra_query .= " OR ";
                        }
                        $i++;
                    }
                    $extra_query .= ") AND ";
                }
                if (isset($request['guard_id']) && !empty($request['guard_id'])) {
                    $extra_query .= "(";
                    $i = 0;
                    foreach ($request['guard_id'] as $key => $id) {
                        $extra_query .= "guards.`id` = '".$id."'";
                        if ($i < sizeof($request['guard_id']) -1) {
                            $extra_query .= " OR ";
                        }
                        $i++;
                    }
                    $extra_query .= ") AND ";
                }
                if (isset($request['sites']) && $request['sites'] != '') {
                    $extra_query .= "(";
                    $i = 0;
                    foreach ($request['sites'] as $key => $id) {
                        $extra_query .= "job_rosters.`site_id` = '".$id."'";
                        if ($i < sizeof($request['sites']) -1) {
                            $extra_query .= " OR ";
                        }
                        $i++;
                    }
                    $extra_query .= ") AND ";
                }
                if($request['type'] == 'Both')
                {
                    $welfareCallDataQuery = JobRoster::join('welfare_call_data', 'welfare_call_data.job_roster_id', '=', 'job_rosters.id')
                    ->Join('job_roster_activites', 'job_roster_activites.job_roster_id', '=', 'job_rosters.id')
                    ->Join('guards', 'guards.id', '=', 'welfare_call_data.guard_id')
                    ->Join('sites', 'sites.id', '=', 'job_rosters.site_id')
                    ->Join('customers', 'customers.id', '=', 'sites.customer_id')
                    ->whereRaw($extra_query."job_rosters.`start` BETWEEN '".$startDate."' AND '".$endDate."' AND job_rosters.deleted_at IS NULL")
                    ->orderBy('sites.site_name', 'ASC')
                    ->orderBy('welfare_call_data.created_at', 'ASC')
                    ->select(
                        'job_rosters.id as roster_id',
                        'guards.id as guard_id',
                        'welfare_call_data.id as wf_call_id',
                        'job_rosters.start as date',
                        'job_rosters.start as start_time',
                        'job_rosters.end as end_time',
                        'job_roster_activites.signin_time',
                        'job_roster_activites.signout_time',
                        'guards.first_name',
                        'guards.middle_name',
                        'guards.last_name',
                        'sites.site_name',
                        'sites.id as site_id',
                        'customers.name as customer_name',
                        'welfare_call_data.status as response',
                        'welfare_call_data.send_time',
                        'welfare_call_data.response_time',
                        \DB::raw("JSON_UNQUOTE(JSON_EXTRACT(welfare_call_data.admin_notes_id, '$[0].note')) as note"),
                        \DB::raw("'WC' as type"),
                         \DB::raw("'Welfare Call' as call_type")
                    );
    
                if (isset($request['response']['0'])) {
                    $welfareCallDataQuery->where('welfare_call_data.status', '=', $request['response']['0']);
                }
    
                $data = $welfareCallDataQuery->get();
        
                }
            
                return $data;
                }
    


}
