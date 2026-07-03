<?php

namespace App\Http\Controllers\reports;

use App\Exports\ChargableWeeklyReportExport;
use App\Http\Controllers\Controller;
use App\Models\JobRoster;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Collection;

class ChargableHrsReport extends Controller
{


function generateChargeableReport()
{
    // return Excel::download(new InvoiceReportExport, 'invoice_report.xlsx');
    $filename = time().'_chargeable_report.xlsx';
    Excel::store(new ChargableWeeklyReportExport, 'excel/Chargeable/'.$filename, 'excels');
    return response()->json(['success' => true, 'message' => 'Chargeable weekly hours Report generate successfully.','path' =>
    'https://'.request()->getHttpHost().'/excel/Chargeable/'.$filename]);
}


public function getReportData($request)
    {
        if (isset($request['date']) && $request['date'] != '') {
            $date = $request['date'];
            [$from, $to] = array_map('trim', explode(' - ', $date));

            // $startCarbon = Carbon::parse(str_replace('-', '/', $from));
            // $endCarbon = Carbon::parse(str_replace('-', '/', $to));
            $startCarbon = Carbon::createFromFormat('d-m-Y', $from);
            $endCarbon = Carbon::createFromFormat('d-m-Y', $to);

            $weeksDifference = $startCarbon->diffInWeeks($endCarbon);
            $mainArr = [];

            for ($i = 0; $i <= $weeksDifference; $i++) {
                $currentWeekStartDate = $startCarbon->copy()->addWeeks($i)->startOfWeek();
                $currentWeekEndDate = $currentWeekStartDate->copy()->endOfWeek();
                $datesArr[] = $currentWeekStartDate->format('m-d-Y');

                $weeks = JobRoster::whereNotNull('guard_id')
                ->where(function ($q) {
                    $q->where('job_status', 'completed')
                        ->orWhere('job_rosters.admin_approved', 1);
                })
                ->where('start', '>=', $currentWeekStartDate->format('Y-m-d'))
                    ->where('start', '<=', $currentWeekEndDate->format('Y-m-d'))
                    ->with(['site', 'site.customer'])
                    ->get();

                foreach ($weeks as $value) {
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

                    // Check if the date already exists in the "dates" array
                    $currentDate = $currentWeekStartDate->format('m-d-Y');
                    if (isset($mainArr[$customerId][$siteId]['dates'][$currentDate])) {
                        // If it exists, add the hours
                        $mainArr[$customerId][$siteId]['dates'][$currentDate] += $value->hours;
                    } else {
                        // If it doesn't exist, create a new entry
                        $mainArr[$customerId][$siteId]['dates'][$currentDate] = $value->hours;
                    }
                }
            }
            $returnArr = [
                'mainArr' => $mainArr,
                'dates' => $datesArr
            ];
            // dd($returnArr);
            return $returnArr;
        }
    }


}


