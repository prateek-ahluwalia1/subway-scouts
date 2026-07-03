<?php

namespace App\Http\Controllers\reports;

use App\Exports\AwardOverTimeExport;
use App\Exports\HrsByEmpReportExport;
use App\Exports\TwelveHourExport;
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

class TwelveHrsShiftReport extends Controller
{


    function generateTwelveHoursShiftReport()
    {
        // return Excel::download(new InvoiceReportExport, 'invoice_report.xlsx');
        $filename = time() . '_12_hours_shift_report.xlsx';
        Excel::store(new TwelveHourExport, 'excel/twelvehours/' . $filename, 'excels');
        return response()->json(['success' => true, 'message' => '12 Hours Shift Report.', 'path' =>
        'https://' . request()->getHttpHost() . '/excel/twelvehours/' . $filename]);
    }

    // function getReportData($request)
    // {
    //     if (isset($request['date']) && $request['date'] != '') {
    //         $date = $request['date'];
    //         $date = explode('-', $date);
    //         $from = strtotime(trim($date[0]));
    //         $to = strtotime(trim($date[1]));
    //         $to = $to;
    //     }else{
    //         $today = today();
    //         $to = $today->startOfWeek();
    //         $from = $today->endOfWeek();
    //     }
    //     $sql = "SELECT
    //     jr.`id`,
    //     jr.`start`,
    //     jr.`end`,
    //     jr.`hours`,
    //     jr.`roster_id`,
    //     j.`site_name`,
    //     j.`site_description`,
    //     cust.`name` AS customer_name,
    //     c.`name` AS contractor_name,
    //     g.`id` AS guard_id,
    //     g.`name` AS guard_name,
    //     -- gi.`internal_id`,
    //     gi.`external_id`
    //     FROM job_rosters AS jr
    //     INNER JOIN sites AS j ON j.`id` = jr.`site_id`
    //     LEFT JOIN `guards` AS g ON g.`id` = jr.`guard_id`
    //     LEFT JOIN customers AS cust ON j.`customer_id` = cust.`id`
    //     LEFT JOIN `contractors` AS c ON j.`contractor_id`= c.`id`
    //     LEFT JOIN `guard_external_ids` AS gi ON gi.`customer_id` = cust.`id` AND gi.`guard_id` = jr.`guard_id`
    //     LEFT JOIN `guard_payroll_ids` AS gpi ON gpi.`guard_id` = g.`id` AND gpi.`guard_id` = jr.`guard_id`
    //     WHERE jr.`shift_payable` = 'yes' AND jr.`hours` > 12 AND gi.`external_id` LIKE '%AMG%' AND jr.`temp_date` BETWEEN '".date('Y-m-d', $from)."' AND '".date('Y-m-d', $to)."' GROUP BY jr.roster_id ORDER BY g.name ASC, jr.unprofile_name ";

    //     $query = DB::select($sql);
    //     return $query;
    // }
    function getReportData($request)
    {
        if (isset($request['date']) && $request['date'] != '') {
            $date = $request['date'];
            $date = explode('-', $date);
            
            // Parse and reorder dates to match DD/MM/YYYY format
            $from = DateTime::createFromFormat('d/m/Y', trim($date[0]))->format('Y-m-d 00:00');
            $to = DateTime::createFromFormat('d/m/Y', trim($date[1]))->format('Y-m-d 23:59');
        } else {
            $today = today();
            $from = $today->startOfWeek()->format('Y-m-d 00:00');
            $to = $today->endOfWeek()->format('Y-m-d 23:59');
        }

        $sql = "SELECT
            jr.`id`,
            jr.`start`,
            jr.`end`,
            jr.`hours`,
            jr.`roster_id`,
            j.`site_name`,
            j.`site_description`,
            cust.`name` AS customer_name,
            c.`name` AS contractor_name,
            g.`id` AS guard_id,
            CONCAT(g.first_name, ' ', COALESCE(g.middle_name, ''), ' ', g.last_name) AS guard_name,
            gi.`external_id`
        FROM job_rosters AS jr
        INNER JOIN sites AS j ON j.`id` = jr.`site_id`
        LEFT JOIN `guards` AS g ON g.`id` = jr.`guard_id`
        LEFT JOIN customers AS cust ON j.`customer_id` = cust.`id`
        LEFT JOIN `contractors` AS c ON j.`contractor_id` = c.`id`
        LEFT JOIN `guard_external_ids` AS gi ON gi.`customer_id` = cust.`id` AND gi.`guard_id` = jr.`guard_id`
        WHERE jr.`shift_payable` = 'yes' 
        AND jr.`deleted_at` IS NULL
        AND jr.`hours` > 12 
        AND jr.`start` BETWEEN '$from' AND '$to'
        -- AND jr.`start` BETWEEN '" . date('Y-m-d', strtotime($from)) . "' AND '" . date('Y-m-d', strtotime($to)) . "' 
        -- GROUP BY jr.roster_id 
        ORDER BY g.name ASC, jr.unprofile_name";

        $query = DB::select($sql);

        return $query;
    }

}
