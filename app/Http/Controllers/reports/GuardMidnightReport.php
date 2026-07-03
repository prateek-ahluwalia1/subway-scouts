<?php

namespace App\Http\Controllers\reports;

use App\Exports\AwardOverTimeExport;
use App\Exports\HrsByEmpReportExport;
use App\Exports\GuardMidnightExport;
use App\Http\Controllers\Controller;
use DateTime, DateInterval, DatePeriod;
use App\Models\GuardLeave;
use App\Models\JobRoster;
use App\Models\Guard;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class GuardMidnightReport extends Controller
{


    function generateGuardMidnightReport()
    {
        // return Excel::download(new InvoiceReportExport, 'invoice_report.xlsx');
        $filename = time() . '_guard_midnight_report.xlsx';
        Excel::store(new GuardMidnightExport, 'excel/midnighthours/' . $filename, 'excels');
        return response()->json(['success' => true, 'message' => 'guard Midnight Report.', 'path' =>
        'https://' . request()->getHttpHost() . '/excel/midnighthours/' . $filename]);
    }

    function getReportData($request)
    {
        if (isset($request['date']) && $request['date'] != '') {
            $date = $request['date'];
            $date = explode('-', $date);
            
            // Parse and reorder dates to match the expected format (YYYY-MM-DD)
            $from = DateTime::createFromFormat('d/m/Y', trim($date[0]));
            $to = DateTime::createFromFormat('d/m/Y', trim($date[1]));
            
            // Convert to Y-m-d format
            $fromDate = $from->format('Y-m-d 00:00');
            $toDate = $to->format('Y-m-d 23:59');
        } else {
            $today = today();
            $fromDate = $today->startOfWeek()->format('Y-m-d 00:00');
            $toDate = $today->endOfWeek()->format('Y-m-d 23:59');
        }

      $sql = "
            SELECT
                g.`id` AS guard_id,
                TRIM(CONCAT(g.first_name, ' ', COALESCE(g.middle_name, ''), ' ', g.last_name)) AS guard_name,
                gi.`external_id`,
                jr.`hours`,
                jr.`start`,
                jr.`end`
            FROM job_rosters AS jr
            LEFT JOIN `guards` AS g ON g.`id` = jr.`guard_id`
            LEFT JOIN `guard_external_ids` AS gi ON gi.`guard_id` = jr.`guard_id`
            WHERE jr.`deleted_at` = NULL
            WHERE jr.`start` >= '$fromDate'
            AND jr.`start` <= '$toDate'
            ORDER BY guard_name ASC
        ";



        $query = DB::select($sql);

        $aggregatedResults = [];

        foreach ($query as $record) {
            // Calculate Saturday hours
            $saturdayHours = $this->getSaturdayHours($record->start, $record->end);

                if (!isset($aggregatedResults[$record->guard_id])) {
                    $record->id1 = 'N/A';
                    $record->id2 = 'N/A';
                    $ids = DB::table('guard_external_ids')->where('guard_id', $record->guard_id)->get();
                    foreach ($ids as $id) {
                        if(preg_match('/AMG/i', $id->external_id)){
                            $record->id1 = $id->external_id;
                        } 
                        if (!preg_match('/AMG/i', $id->external_id) && $id->external_id > 0) {
                            $record->id2 = $id->external_id;
                        }
                    }
                    $aggregatedResults[$record->guard_id] = [
                        'guard_id' => $record->guard_id,
                        'guard_name' => $record->guard_name,
                        // 'internal_id' => $record->internal_id,
                        'external_id' => $record->external_id,
                        'wilson' => $record->id1,
                        'certis' => $record->id2,
                        'hours' => 0,
                        'saturday_hours' => 0,
                    ];
                }
                $aggregatedResults[$record->guard_id]['hours'] += $record->hours;
                $aggregatedResults[$record->guard_id]['saturday_hours'] += $saturdayHours;
        }

        $result = array_values($aggregatedResults);

        return $result;
    }


    function getSaturdayHours($temp_start, $temp_end) {
        $start = strtotime($temp_start);
        $end = strtotime($temp_end);

        $saturday_start = strtotime('Saturday 00:00', $start);
        $saturday_end = strtotime('Saturday 23:59', $start);

        if ($end <= $saturday_start || $start >= $saturday_end) {
            return 0;
        }

        $actual_start = max($start, $saturday_start);
        $actual_end = min($end, $saturday_end);

        $saturday_hours = ($actual_end - $actual_start) / 3600;

        return $saturday_hours;
    }

    
}
