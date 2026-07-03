<?php

namespace App\Http\Controllers\reports;

use App\Exports\AwardOverTimeExport;
use App\Exports\HrsByEmpReportExport;
use App\Exports\CasualHoursExport;
use App\Http\Controllers\Controller;
use App\Models\GuardLeave;
use App\Models\JobRoster;
use App\Models\Guard;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class CasualHrsWeeklyReport extends Controller
{


    function generateCasualHoursWeeklyReport()
    {
        // return Excel::download(new InvoiceReportExport, 'invoice_report.xlsx');
        $filename = time() . '_casual_hours_report.xlsx';
        Excel::store(new CasualHoursExport, 'excel/casualhours/' . $filename, 'excels');
        return response()->json(['success' => true, 'message' => 'Casual Hours Report.', 'path' =>
        'https://' . request()->getHttpHost() . '/excel/casualhours/' . $filename]);
    }


    public function getReportData($request)
    {  
        if(isset($request['date']) && $request['date'] != ''){
            $date = $request['date'];
            [$from, $to] = array_map('trim', explode(' - ', $date));

            $startCarbon = Carbon::createFromFormat('d-m-Y', $from);
            $endCarbon = Carbon::createFromFormat('d-m-Y', $to);

            $weeksDifference = $startCarbon->diffInWeeks($endCarbon);
            $mainArr = [];

            for ($i = 0; $i <= $weeksDifference; $i++) {
                $currentWeekStartDate = $startCarbon->copy()->addWeeks($i)->startOfWeek();
                $currentWeekEndDate = $currentWeekStartDate->copy()->endOfWeek();

                $currentWeekStartDateFormatted = $currentWeekStartDate->format('Y-m-d');
                $currentWeekEndDateFormatted = $currentWeekEndDate->format('Y-m-d');

                $datesArr[] = $currentWeekStartDate->format('m-d-Y');

                $weeks = Guard::select('guards.*', 'job_rosters.*', 'customers.*', 'sites.*')
                ->join('job_rosters', 'guards.id', '=', 'job_rosters.guard_id')
                ->join('sites', 'job_rosters.site_id', '=', 'sites.id')
                ->join('customers', 'sites.customer_id', '=', 'customers.id')
                ->where('job_rosters.job_status', 'completed')
                ->where('guards.staff_type', 'casual')
                ->where('guards.guard_status', 'active')
                ->where('guards.is_available', 'yes')
                ->where('guards.admin_approval_status', 'active')
                ->whereDate('job_rosters.start', '>=', $currentWeekStartDateFormatted)
                ->whereDate('job_rosters.start', '<=', $currentWeekEndDateFormatted)
                ->whereNotNull('job_rosters.guard_id')
                ->get();


                foreach ($weeks as $value) {
                    $customerId = $value->customer_id;
                    $siteId = $value->site_id;

                    if (!isset($mainArr[$customerId][$siteId])) {
                       
                        $fullName = $value->first_name;

                        if (!empty($value->middle_name)) {
                            $fullName .= ' ' . $value->middle_name;
                        }
                        
                        if (!empty($value->last_name)) {
                            $fullName .= ' ' . $value->last_name;
                        }
                        
                        $mainArr[$customerId][$siteId] = [
                            'site_name' => $value->site_name,
                            'staff_name' => $fullName,
                            'staff_id' => $value->guard_id,
                            'site_id' => $siteId,
                            'customer_name' => $value->name,
                            'customer_id' => $customerId,
                            'dates' => [],
                            'po_wo' => !empty($value->po_wo) ? $value->po_wo : $value->po_wo,
                        ];
                    }

                    $currentDate = $currentWeekStartDate->format('m-d-Y');

                    $casualhours = $value->hours;

                    if (is_numeric($casualhours)) {
                        
                        // Add casual hours to the existing hours if the date exists
                        if (isset($mainArr[$customerId][$siteId]['dates'][$currentDate])) {
                            $mainArr[$customerId][$siteId]['dates'][$currentDate] += $value->hours;
                        } else {
                            $mainArr[$customerId][$siteId]['dates'][$currentDate] = $value->hours;
                        }
                    } else {
                        // Handle the case where $approvedLeaveRequests is not a number
                        // Log an error message
                        Log::error("Invalid casual hours data for value: $casualhours");
                        // Set a default value for casualhours (e.g., 0)
                        $value->hours = 0;
                    }
                }
            }

            $returnArr = [
                'mainArr' => $mainArr,
                'dates' => $datesArr,
            ];

            return $returnArr;
        }
    }


}
