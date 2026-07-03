<?php

namespace App\Http\Controllers\reports;

use App\Exports\ChargableDailyReportExport;
use App\Http\Controllers\Controller;
use App\Models\JobRoster;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Collection;

class ChargableHrsDaliyReport extends Controller
{


function generateChargeableHrsDaliyReport()
{
    // return Excel::download(new InvoiceReportExport, 'invoice_report.xlsx');
    $filename = time().'_chargeable_weekly_report.xlsx';
    Excel::store(new ChargableDailyReportExport, 'excel/Chargeable/'.$filename, 'excels');
    return response()->json(['success' => true, 'message' => 'Chargeable weekly hours Report generate successfully.','path' =>
    'https://'.request()->getHttpHost().'/excel/Chargeable/'.$filename]);
}


    public function getReportData($request)
    {
        $currentMonthStartDate = Carbon::now()->startOfMonth();
        $currentMonthEndDate = Carbon::now()->endOfMonth();

        $mainArr = [];
        $datesArr = [];

        while ($currentMonthStartDate->lte($currentMonthEndDate)) {
            $currentDate = $currentMonthStartDate->format('m-d-Y');
            $datesArr[] = $currentDate;

            $dataForDay = JobRoster::whereNotNull('guard_id')
                ->where(function ($q) {
                    $q->where('job_status', 'completed')
                        ->orWhere('job_rosters.admin_approved', 1);
                })
                ->whereDate('start', '=', $currentDate) // Fetch data for the current date
                ->with(['site', 'site.customer'])
                ->get();

            foreach ($dataForDay as $value) {
                $customerId = $value->site->customer->id;
                $siteId = $value->site->id;

                if (!isset($mainArr[$customerId][$siteId])) {
                    $mainArr[$customerId][$siteId] = [
                        'site_name' => $value->site->site_name,
                        'site_id' => $siteId,
                        'customer_name' => $value->site->customer->name,
                        'customer_id' => $customerId,
                        'dates' => [],
                        'po_wo' => !empty($value->po_wo) ? $value->po_wo : $value->site->po_wo,
                    ];
                }

                $mainArr[$customerId][$siteId]['dates'][$currentDate] = $value->hours;
            }

            $currentMonthStartDate->addDay(); // Move to the next date in the current month
        }

        $returnArr = [
            'mainArr' => $mainArr,
            'dates' => $datesArr
        ];

        return $returnArr;
    }







}


