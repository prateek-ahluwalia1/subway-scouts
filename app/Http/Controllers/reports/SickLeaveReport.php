<?php

namespace App\Http\Controllers\reports;

use App\Exports\AwardOverTimeExport;
use App\Exports\HrsByEmpReportExport;
use App\Exports\SickLeaveExport;
use App\Http\Controllers\Controller;
use App\Models\GuardLeave;
use App\Models\JobRoster;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class SickLeaveReport extends Controller
{


    function generateSickLeaveReport()
    {
        // return Excel::download(new InvoiceReportExport, 'invoice_report.xlsx');
        $filename = time() . '_sick_leave_report.xlsx';
        Excel::store(new SickLeaveExport, 'excel/sickleave/' . $filename, 'excels');
        return response()->json(['success' => true, 'message' => 'Sick Leave Report.', 'path' =>
        'https://' . request()->getHttpHost() . '/excel/sickleave/' . $filename]);
    }



    // public function getReportData($request)
    // {
    //     if (isset($request['date']) && $request['date'] != '') {
    //         $date = $request['date'];
    //         [$from, $to] = array_map('trim', explode(' - ', $date));

    //         $startCarbon = Carbon::parse(str_replace('-', '/', $from));
    //         $endCarbon = Carbon::parse(str_replace('-', '/', $to));

    //         $weeksDifference = $startCarbon->diffInWeeks($endCarbon);
    //         $mainArr = [];

    //         for ($i = 0; $i <= $weeksDifference; $i++) {
    //             $currentWeekStartDate = $startCarbon->copy()->addWeeks($i)->startOfWeek();
    //             $currentWeekEndDate = $currentWeekStartDate->copy()->endOfWeek();
    //             $datesArr[] = $currentWeekStartDate->format('Y-m-d');

    //             $weeks = JobRoster::whereNotNull('guard_id')
    //             ->where(function ($q) {
    //                 $q->where('job_status', 'completed')
    //                 ->orWhere('job_rosters.admin_approved', 1);
    //             })
    //             ->where('start', '>=', $currentWeekStartDate->format('Y-m-d'))
    //             ->where('start', '<=', $currentWeekEndDate->format('Y-m-d'))
    //             ->with([
    //                 'site',
    //                 'guardz' => function ($query) {
    //                $query->where('staff_type', 'part_time');
    //                $query->orWhere('staff_type', 'full_time');
    //             },
    //                 'site.customer'
    //             ])
    //             ->get();


    //             foreach ($weeks as $value) {
    //                 $customerId = $value->site->customer->id;
    //                 $siteId = $value->site->id;

    //                 if (!isset($mainArr[$customerId][$siteId])) {
    //                     $mainArr[$customerId][$siteId] = [
    //                         'site_name' => $value->site->site_name,
    //                         'staff_name' => $value->guardz->first_name,
    //                         'staff_id' => $value->guardz->id,
    //                         'site_id' => $siteId,
    //                         'customer_name' => $value->site->customer->name,
    //                         'customer_id' => $customerId,
    //                         'dates' => [],
    //                         'po_wo' => !empty($value->po_wo) ? $value->po_wo : $value->site->po_wo,
    //                     ];
    //                 }
    //                 // Check if the date already exists in the "dates" array
    //                 $currentDate = $currentWeekStartDate->format('Y-m-d');
    //                 if (isset($mainArr[$customerId][$siteId]['dates'][$currentDate])) {
    //                     // If it exists, add the hours
    //                     $mainArr[$customerId][$siteId]['dates'][$currentDate] += $value->hours;
    //                 } else {
    //                     // If it doesn't exist, create a new entry
    //                     $mainArr[$customerId][$siteId]['dates'][$currentDate] = $value->hours;
    //                 }
    //             }
    //         }
    //         $returnArr = [
    //             'mainArr' => $mainArr,
    //             'dates' => $datesArr
    //         ];
    //         // dd($returnArr);
    //         return $returnArr;
    //     }
    // }


    public function getReportData($request)
    {  
        if(isset($request['date']) && $request['date'] != ''){
            $date = $request['date'];
            [$from, $to] = array_map('trim', explode(' - ', $date));

            // $startCarbon = Carbon::parse(str_replace('-', '/', $from));
            // $endCarbon = Carbon::parse(str_replace('-', '/', $to));
            $startCarbon = Carbon::createFromFormat('d-m-Y', $from);
            $endCarbon = Carbon::createFromFormat('d-m-Y', $to);

            $weeksDifference = $startCarbon->diffInWeeks($endCarbon);
            
            $mainArr = [];

            // Define a function to calculate hours from days
            $calculateHours = function ($days) {
                // Assuming 8 hours per day
                return $days * 8;
            };

            for ($i = 0; $i <= $weeksDifference; $i++) {
                $currentWeekStartDate = $startCarbon->copy()->addWeeks($i)->startOfWeek();
                $currentWeekEndDate = $currentWeekStartDate->copy()->endOfWeek();

                $currentWeekStartDateFormatted = $currentWeekStartDate->format('m/d/Y');
                $currentWeekEndDateFormatted = $currentWeekEndDate->format('m/d/Y');

                $datesArr[] = $currentWeekStartDate->format('m-d-Y');

                $weeks = GuardLeave::select('guard_leave_requests.*', 'job_rosters.*', 'sites.*', 'customers.*', 'guards.*')
                    ->join('job_rosters', 'guard_leave_requests.roster_id', '=', 'job_rosters.id')
                    ->join('sites', 'job_rosters.site_id', '=', 'sites.id')
                    ->join('customers', 'sites.customer_id', '=', 'customers.id')
                    ->leftjoin('guards', 'guard_leave_requests.guard_id', '=', 'guards.id')
                    ->where('guard_leave_requests.status', 'approved')
                    ->where('guard_leave_requests.roster_id', '!=', null)
                    ->whereBetween('guard_leave_requests.start_date', [$currentWeekStartDateFormatted, $currentWeekEndDateFormatted])
                    ->whereNotNull('guard_leave_requests.guard_id')
                    ->where(function ($query) {
                        // $query->where('job_rosters.job_status', 'completed')
                        //     ->orWhere('job_rosters.admin_approved', 1);
                    })->get();

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

                    // Check for guard leave requests with 'approved' status
                    $approvedLeaveRequests = $value->days;
                    if (is_numeric($approvedLeaveRequests)) {
                        // Calculate hours from approved leave days
                        $leaveDays = $approvedLeaveRequests;
                        $leaveHours = $calculateHours($leaveDays);

                        // Add leave hours to the existing hours if the date exists
                        if (isset($mainArr[$customerId][$siteId]['dates'][$currentDate])) {
                            $mainArr[$customerId][$siteId]['dates'][$currentDate] += $leaveHours;
                        } else {
                            $mainArr[$customerId][$siteId]['dates'][$currentDate] = $leaveHours;
                        }
                    } else {
                        // Handle the case where $approvedLeaveRequests is not a number
                        // Log an error message
                        Log::error("Invalid leave request data for value: $approvedLeaveRequests");
                        // Set a default value for leaveHours (e.g., 0)
                        $leaveHours = 0;
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
