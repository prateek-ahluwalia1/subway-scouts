<?php

namespace App\Http\Controllers\reports;

use App\Exports\AwardOverTimeExport;
use App\Exports\HrsByEmpReportExport;
use App\Exports\TenHoursExport;
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

class TenHrsShiftReport extends Controller
{


    function generateTenHoursShiftReport()
    {
        // return Excel::download(new InvoiceReportExport, 'invoice_report.xlsx');
        $filename = time() . '_10_hours_shift_report.xlsx';
        Excel::store(new TenHoursExport, 'excel/tenhours/' . $filename, 'excels');
        return response()->json(['success' => true, 'message' => '10 Hours Shift Report.', 'path' =>
        'https://' . request()->getHttpHost() . '/excel/tenhours/' . $filename]);
    }
    function getReportData($request)
{
    if (!empty($request['date'])) {
        $date = explode('-', $request['date']);
        $from = DateTime::createFromFormat('d/m/Y', trim($date[0]))->format('Y-m-d 00:00');
        $to   = DateTime::createFromFormat('d/m/Y', trim($date[1]))->format('Y-m-d 23:59');
    } else {
        $today = today();
        $from = $today->startOfWeek()->format('Y-m-d 00:00');
        $to   = $today->endOfWeek()->format('Y-m-d 23:59');
    }

    // restrict to Monday → Friday
    $fromDate = Carbon::parse($from)->startOfWeek(Carbon::MONDAY)->format('Y-m-d 00:00');
    $toDate   = Carbon::parse($to)->endOfWeek(Carbon::FRIDAY)->format('Y-m-d 23:59');

    $sql = "
        SELECT
            SUM(jr.`hours`) AS total,
            g.`id` AS guard_id,
            CONCAT(g.first_name, ' ', COALESCE(g.middle_name, ''), ' ', g.last_name) AS guard_name
        FROM job_rosters AS jr
        INNER JOIN sites AS j ON j.`id` = jr.`site_id`
        INNER JOIN guards AS g ON g.`id` = jr.`guard_id`
        INNER JOIN customers AS cust ON j.`customer_id` = cust.`id`
        LEFT JOIN contractors AS c ON j.`contractor_id` = c.`id`
        LEFT JOIN guard_external_ids AS gi 
               ON gi.`customer_id` = cust.`id` 
              AND gi.`guard_id` = jr.`guard_id`
        WHERE jr.`shift_payable` = 'yes'
          AND g.`guard_status` = 'active'
          AND jr.`start` BETWEEN '$fromDate' AND '$toDate'
          AND jr.`deleted_at` IS NULL
        GROUP BY jr.guard_id
        HAVING total <= 10
        ORDER BY g.first_name ASC
    ";

    $query = DB::select($sql);

    $data = [];
    foreach ($query as $q) {
        $q->id1 = 'N/A';
        $q->id2 = 'N/A';
        $ids = DB::table('guard_external_ids')->where('guard_id', $q->guard_id)->get();
        foreach ($ids as $id) {
            if (preg_match('/AMG/i', $id->external_id)) {
                $q->id1 = $id->external_id;
            }
            if (!preg_match('/AMG/i', $id->external_id) && $id->external_id > 0) {
                $q->id2 = $id->external_id;
            }
        }
        $data[] = $q;
    }

    return $data;
}   
    
}
