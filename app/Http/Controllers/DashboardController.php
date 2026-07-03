<?php

namespace App\Http\Controllers;

use App\Http\Resources\GetDashboardNotesResource;
use App\Http\Resources\GuardLeaveCountResource;
use App\Models\DashboardNotes;
use App\Models\JobRoster;
use Illuminate\Http\Request;
use App\Http\Resources\liveDashabordDataResource;
use App\Models\crm\CustomerModel;
use \Datetime;
use App\Models\Guard;
use App\Models\GuardDocument;
use App\Models\GuardLeave;
use App\Models\WelfareCall;
use App\Models\GreenCall;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function dashboardGraphData(){
        
        // $CallData = [];
        $stafCount = $this->countActiveStaff();
        $remindersComplianceExpDocs = $this->remindersComplianceExpDocs();
        $liveWelfareCallData = $this->liveWelfareCallData();
        $liveGreenCallData = $this->livegreenCallData();
        $combinedData = array_merge([$liveWelfareCallData], [$liveGreenCallData]);
        $mergedData = [];
        foreach ($combinedData as $item) {
            $mergedData = array_merge($mergedData, $item['data']->toArray());
        }
        $getNearExpireLicenseGuard = $this->getNearExpireLicenseGuard();
        $getStaffByStaffType = $this->getStaffByStaffType();
        $getBeforeSixMonthsShiftsAndHoursCount = $this->getBeforeSixMonthsShiftsAndHoursCount();
        $dashboardJobsCount = $this->dashboardJobsCount();
        $appUsages = $this->appUsages();
        $dashboardCustomerSitesCount = $this->dashboardCustomerSitesCount();
        return response()->json([
            'stafCount' => $stafCount,
            'remindersComplianceExpDocs' => $remindersComplianceExpDocs,
            'liveWelfareCallData' => $liveWelfareCallData,
            'callData' => $mergedData,
            // 'guardLeaveCount' => $guardLeaveCount,
            'getNearExpireLicenseGuard' => $getNearExpireLicenseGuard,
            'getStaffByStaffType' => $getStaffByStaffType,
            'getBeforeSixMonthsShiftsAndHoursCount' => $getBeforeSixMonthsShiftsAndHoursCount,
            'dashboardJobsCount' => $dashboardJobsCount,
            'appUsages' => $appUsages,
            'dashboardCustomerSitesCount' => $dashboardCustomerSitesCount,
        ]);

    }
    function getBeforeSixMonthsShiftsAndHoursCount() {
        $currentDate = new DateTime();
        $jobCounts = [];
    
        for ($i = 0; $i < 6; $i++) {
            $month = $currentDate->format('m');
    
            $total_shifts = JobRoster::whereMonth('start', $month)->count();
    
            $total_hours = JobRoster::whereMonth('start', $month)
                ->whereNotNull('hours')
                ->sum('hours');
    
            // Round the total_hours to 2 decimal places (change 2 to the desired number of decimal places)
            $rounded_hours = round($total_hours, 2);
        
            $jobCounts['month'][] = $month;
            $jobCounts['total_shifts'][] = $total_shifts;
            $jobCounts['total_hours'][] = $rounded_hours;
    
            $currentDate->modify('-1 month');
        }
    
        return [
            'success' => true,
            'code' => 200,
            'data' => $jobCounts,
        ];
    }
    public function getStaffByStaffType()  {
   
        $query = Guard::all();
        $total_staff = $query->count();
        $partTimeStaff = $query->where('staff_type', 'part_time')->count(); 
        $fullTimeStaff = $query->where('staff_type', 'full_time')->count(); 
        $casualTimeStaff = $query->where('staff_type', 'casual')->count(); 
        return ['total_staff' => $total_staff, 'partTimeStaff' => $partTimeStaff, 'fullTimeStaff' => $fullTimeStaff, 'casualTimeStaff' => $casualTimeStaff];
     }
     public function guardLeaveCount(Request $request) {
        $now = Carbon::now();
        $startOfWeek = $now->startOfWeek()->format('m/d/Y');
        $endOfWeek = $now->endOfWeek()->format('m/d/Y');
        $startOfMonth = $now->startOfMonth()->format('m/d/Y');
        $endOfMonth = $now->endOfMonth()->format('m/d/Y');
    
        $query = GuardLeave::with('guardss');
                
        
        if ($request->type == 'week') {
            //$query->whereBetween('start_date', [$startOfWeek, $endOfWeek]);
            $query->where('start_date', '>=', $startOfWeek)
                ->where('start_date', '<=', $endOfWeek);
        } else {
            //$query->whereBetween('start_date', [$startOfMonth, $endOfMonth]);
            $query->where('start_date', '>=', $startOfMonth)
                ->where('start_date', '<=', $endOfMonth);
        }
        
        $guard_leavs = $query->get();
        $gl = GuardLeaveCountResource::collection($guard_leavs);
        $guard_count = $guard_leavs->count();
    
        return ['data' => $gl, 'leave_count' => $guard_count];
    }
    

    public function dashboardJobsCount()
    {
        $query = JobRoster::query();

        $currentDateTime = date('Y-m-d H:i');

        $query->where('start', '<=', $currentDateTime)
        ->where('end', '>=', $currentDateTime)
        ->where('signin_status', 0);
        $missedJobCount = $query->count();

        $today = date('Y-m-d');
        $today1 = date('Y-m-d 00:00');
        $today2 = date('Y-m-d 23:59');
        $nextSevenDays = date('Y-m-d', strtotime($today . '+ 7 day'));
        $upcoming_jobs = JobRoster::whereBetween('start', [$today, $nextSevenDays])->where('signin_status', 0)
        ->count();
        
        $currentDate = Carbon::now();
        $weekStart = $currentDate->startOfWeek()->format('Y-m-d');
        $weekEnd = $currentDate->endOfWeek()->format('Y-m-d');

        $ongoing = JobRoster::where('signin_status', 1)->Where('start', '>=', $today1)->Where('start', '<=', $today2)->count();
        $completedJobCount = JobRoster::whereBetween('start', [$weekStart,$weekEnd])->where('job_status', 'completed')->count();
        $activeSites = $query->where(function ($q) use ($currentDateTime, $today, $nextSevenDays) {
            $q->whereBetween('start', [$today, $nextSevenDays]);
            $q->whereNotNull('site_id')->groupBy('site_id');
        })->count();

        $rejected_jobs = JobRoster::where('job_status', 'rejected')->count();
        $Unpublished = JobRoster::where('publish_status', 0)->where('unpublish_status', 1)->count();
        $published = JobRoster::where('publish_status', 1)->count();
        return [
            'missedJobCount' => $missedJobCount, 
            'upcoming_jobs' => $upcoming_jobs,
            'completedJobCount' => $completedJobCount,
            'ongoing' => $ongoing,
            'activeSites' => $activeSites,
            'unpublished_jobs' => $Unpublished,
            'rejected_jobs' => $rejected_jobs,
            'published_jobs' => $published,
        ];
    }
    public function dashboardLeadPercentage(Request $request)
    {
        $monthNo = $request->input('month', Carbon::now()->month);

        $currentMonthStart = Carbon::now()->month($monthNo)->startOfMonth();
        $currentMonthEnd = Carbon::now()->month($monthNo)->endOfMonth();
        $previousMonthStart = Carbon::now()->month($monthNo)->subMonth()->startOfMonth();
        $previousMonthEnd = Carbon::now()->month($monthNo)->subMonth()->endOfMonth();

        $currentMonth = CustomerModel::whereBetween('created_at', [$currentMonthStart, $currentMonthEnd])->get();
        $currentMonthCount = $currentMonth->count();
        $currentMonthWonCount = $currentMonth->where('lead_status', 'Won')->count();
        $currentMonthLostCount = $currentMonth->where('lead_status', 'Lost Lead')->count();

        // Calculate revenue for the current month
        $currentRevSum = CustomerModel::whereBetween('created_at', [$currentMonthStart, $currentMonthEnd])
            ->where('lead_status', 'Won')
            ->sum('actual_revenue');
        // $currentMonthRevenueCountAn = $currentMonthRevenueAn->sum(function ($item) {
        //     $numericValue = preg_replace("/[^0-9]/", "", $item->annual_revenue);
        //     return is_numeric($numericValue) ? (int)$numericValue : 0;
        // });

        // $currentMonthRevenueMa = CustomerModel::whereBetween('created_at', [$currentMonthStart, $currentMonthEnd])
        //     ->where('lead_status', 'Won')
        //     ->get(['manual_revenue']);
        // $currentMonthRevenueCountMa = $currentMonthRevenueMa->sum(function ($item) {
        //     $numericValue = preg_replace("/[^0-9]/", "", $item->manual_revenue);
        //     return is_numeric($numericValue) ? (int)$numericValue : 0;
        // });

        // $currentRevSum = $currentMonthRevenueCountAn + $currentMonthRevenueCountMa;
        // $currentRevSum = $currentMonthRevenueCountAn;

        // Fetch data for the previous month
        $previousMonth = CustomerModel::whereBetween('created_at', [$previousMonthStart, $previousMonthEnd])->get();
        $previousMonthCount = $previousMonth->count();
        $previousMonthWonCount = $previousMonth->where('lead_status', 'Won')->count();
        $previousMonthLostCount = $previousMonth->where('lead_status', 'Lost Lead')->count();

        // Calculate revenue for the previous month
        $previousRevSum = CustomerModel::whereBetween('created_at', [$previousMonthStart, $previousMonthEnd])
            ->where('lead_status', 'Won')
            ->sum('actual_revenue');
        // $previousMonthRevenueCountAn = $previousMonthRevenueAn->sum(function ($item) {
        //     $numericValue = preg_replace("/[^0-9]/", "", $item->annual_revenue);
        //     return is_numeric($numericValue) ? (int)$numericValue : 0;
        // });

        // $previousMonthRevenueMa = CustomerModel::whereBetween('created_at', [$previousMonthStart, $previousMonthEnd])
        //     ->where('lead_status', 'Won')
        //     ->get(['manual_revenue']);
        // $previousMonthRevenueCountMa = $previousMonthRevenueMa->sum(function ($item) {
        //     $numericValue = preg_replace("/[^0-9]/", "", $item->manual_revenue);
        //     return is_numeric($numericValue) ? (int)$numericValue : 0;
        // });

        // $previousRevSum = $previousMonthRevenueCountAn + $previousMonthRevenueCountMa;
        // $previousRevSum = $previousMonthRevenueCountAn;

        // Calculate the percentages and revenue difference
        $created = $previousMonthCount == 0 ? ($currentMonthCount > 0 ? 100 : 0) : (($currentMonthCount - $previousMonthCount) / $previousMonthCount) * 100;
        $won = $previousMonthWonCount == 0 ? ($currentMonthWonCount > 0 ? 100 : 0) : (($currentMonthWonCount - $previousMonthWonCount) / $previousMonthWonCount) * 100;
        $lost = $previousMonthLostCount == 0 ? ($currentMonthLostCount > 0 ? 100 : 0) : (($currentMonthLostCount - $previousMonthLostCount) / $previousMonthLostCount) * 100;

        // Return the response
        return response()->json([
            "created" => $created,
            "won" => $won,
            "lost" => $lost,
            "revenue" => $currentRevSum - $previousRevSum,
        ]);
    }
    public function dashboardCustomerCount(Request $request)
    {
        $monthNo = $request->input('month', Carbon::now()->month);

        $currentMonthStart = Carbon::now()->month($monthNo)->startOfMonth()->toDateString();
        $currentMonthEnd = Carbon::now()->month($monthNo)->endOfMonth()->toDateString();
        $previousMonthStart = Carbon::now()->month($monthNo)->subMonth()->startOfMonth()->toDateString();
        $previousMonthEnd = Carbon::now()->month($monthNo)->subMonth()->endOfMonth()->toDateString();

        $getWonLeads = DB::table('crm_customers')
            ->whereDate('created_at', '>=', $currentMonthStart)
            ->whereDate('created_at', '<=', $currentMonthEnd)
            ->where('lead_status', 'Won')
            ->get();

        $grossProfit = 0;
        $grossLoss = 0;
        foreach ($getWonLeads as $lead) {
            $sumActual = preg_replace("/[^0-9]/", "", $lead->actual_revenue);
            $sumActual = is_numeric($sumActual) ? (int)$sumActual : 0;
            $getDiff = (int)$sumActual - (int)$lead->booking_expense;
            if ($getDiff > 0) {
                $grossProfit += $getDiff;
            } else {
                $grossLoss += $getDiff;
            }
        }

        $getCurrentMonth = DB::table('crm_customers')
            ->whereDate('created_at', '>=', $currentMonthStart)
            ->whereDate('created_at', '<=', $currentMonthEnd)
            ->get();

        $monthlySaleAnnual = $getCurrentMonth->where('lead_status', 'Won')->sum(function ($item) {
            $numericValue = preg_replace("/[^0-9]/", "", $item->actual_revenue);
            return is_numeric($numericValue) ? (int)$numericValue : 0;
        });

        $monthlyLossManual = $getCurrentMonth->where('lead_status', 'Loss Lead')->sum(function ($item) {
            $numericValue = preg_replace("/[^0-9]/", "", $item->manual_revenue);
            return is_numeric($numericValue) ? (int)$numericValue : 0;
        });

        $monthlyLossAnnual = $getCurrentMonth->where('lead_status', 'Loss Lead')->sum(function ($item) {
            $numericValue = preg_replace("/[^0-9]/", "", $item->annual_revenue);
            return is_numeric($numericValue) ? (int)$numericValue : 0;
        });

        $totalCountCurrentMonth = $getCurrentMonth->where('lead_status', '!=', 'Lost Lead')->count();

        $totalCountLastMonth = DB::table('crm_customers')
            ->where('lead_status', '!=', 'Lost Lead')
            ->whereDate('created_at', '>=', $previousMonthStart)
            ->whereDate('created_at', '<', $previousMonthEnd)
            ->count();

        $percentageChange = 0;
        if ($totalCountLastMonth != 0) {
            $percentageChange = (($totalCountCurrentMonth - $totalCountLastMonth) / $totalCountLastMonth) * 100;
        }

        return response()->json([
            'success' => true,
            'totalCountCurrentMonth' => $totalCountCurrentMonth,
            'percentageChange' => $percentageChange,
            'grossAmount' => ceil($grossProfit),
            'monthlySale' => ceil((int)$monthlySaleAnnual),
            'expectedLoss' => ceil(abs($grossLoss)),
            'dealClosed' => $getCurrentMonth->where('lead_status', 'Won')->count(),
            'contactInFuture' => $getCurrentMonth->where('lead_status', 'Contact in Future')->count()
        ]);
    }
    public function dashboardCustomerSitesCount(){
        $currentDate = Carbon::today();
        $previous6Months = [];
        $previous6Months[] = $currentDate->monthName;
        $dataByMonths = [];
        for ($i = 1; $i <= 5; $i++) {
            $previous6Months[] = $currentDate->subMonth()->format('F');
        }
        $previous6Months = array_reverse($previous6Months);
        foreach($previous6Months as $month){
            $monthNum = Carbon::parse($month)->month;
            $siteCount[] = DB::table('job_rosters')
            ->whereMonth('created_at', $monthNum)
            ->groupBy('site_id')
            ->count();
            $staffCount[] = DB::table('job_rosters')
            ->whereMonth('created_at', $monthNum)
            ->groupBy('guard_id')
            ->count();
        }
        return [
            'months' => $previous6Months,
            'sitesCount' => $siteCount,
            'staffCount' => $staffCount,
        ];
    }
    public function dashboardCustomerLossRevenue(){

        $startDate = Carbon::now()->subMonths(6)->startOfMonth();
        $results = CustomerModel::select(DB::raw('MONTH(created_at) as month, annual_revenue as Amount'))
        ->where('lead_status', 'Lost Lead')
        ->where('created_at', '>=', $startDate)
        ->groupBy(DB::raw('YEAR(created_at), MONTH(created_at)'))
        ->orderBy(DB::raw('YEAR(created_at)'), 'DESC')
        ->orderBy(DB::raw('MONTH(created_at)'), 'DESC')
        ->get();
        return $results;
    }
    public function appUsages()
    {
        //new query..
        $currentDate = new DateTime();
        $jobCounts = [];
        for ($i = 0; $i < 6; $i++) {

            $month = $currentDate->format('m');
            $totalJobs = JobRoster::whereMonth('start', $month)->count();
            $completeJobs = JobRoster::whereMonth('start', $month)
            ->where(function ($query) {
                $query->where('job_status', 'completed')
                ->orWhere('admin_approved', 1);
            })->count();
            
            $missedJobs = JobRoster::whereMonth('start', $month)
            ->where(function ($query) {
                $query->where('job_status', 'confirmed');
                $query->where('signin_status', 0);
            })->count();
            $jobCounts[] = [
                'month' => $month,
                'total_jobs' => $totalJobs,
                'complete_jobs' => $completeJobs,
                'missed_jobs' => $missedJobs
            ];

            $currentDate->modify('-1 month');
        }

        return[
            'data' => $jobCounts,
        ];



    }
    public function remindersComplianceExpDocs(){
        $expiredRecords = GuardDocument::whereNotNull('document_expire')
        ->whereNotIn('document_type', ['security_license'])
        ->whereDate('document_expire', '<', Carbon::now())
        ->with('GuardDetails')
        ->whereHas('GuardDetails', function ($query) {
            $query->where('guard_status', 'active');
        })
        ->get();
        return ['data' => $expiredRecords];
    }
    public function liveDashabordData(Request $request)
    {
        // $limit = 10;
        // $offset = 0;
        // if($request->has('pageIndex') && $request->has('pageSize'))
        // {
        //     $offset = $request->pageIndex * $request->pageSize;
        //     $limit = $request->pageSize;
        // }
        $roster = JobRoster::with(['site', 'guardz', 'greenCall', 'WelfareCall', 'rosterActivity'])->whereDate('start', date('Y-m-d'))
        // ->where(function($que){
        //     $que->orWhere(function($q) {
        //         $q->where('start', '>=', date('Y-m-d').'00:00'); 
        //     });
        //     $que->orWhere(function($q){
        //         $q->where('start', '<=', date('Y-m-d H:i').'00:00'); 
        //         $q->where('end', '>=', date('Y-m-d H:i').'23:59'); 
        //     });
        // })
        ->where('publish_status', 1)
        ->where('guard_id', '>', 0)
        ->orderBy('start', 'asc')
        // ->skip($offset)
        // ->take($limit)
        ->get();
        
        
        // $total = JobRoster::where(function($que){
        //     $que->orWhere(function($q) {
        //         $q->where('start', '>=', date('Y-m-d H:i')); 
        //     });
        //     $que->orWhere(function($q){
        //         $q->where('start', '<=', date('Y-m-d H:i')); 
        //         $q->where('end', '>=', date('Y-m-d H:i')); 
        //     });
        // })
        // ->where('publish_status', 1)
        // ->where('guard_id', '>', 0)
        // ->get()->count();
        
        $total = $roster->count();
        
        
        
        $jobNewRosters = liveDashabordDataResource::collection($roster);
        if (count($jobNewRosters) > 0) {
            return response()->json(['success' => true, 'data' => $jobNewRosters, 'length' => $total, 'pageIndex' => $request->pageIndex, 'pageSize' => $request->pageSize, 'previousPageIndex' => $request->previousPageIndex]);
        }else{
            return response()->json(['success' => false, 'data' => $jobNewRosters, 'length' => $total, 'pageIndex' => $request->pageIndex, 'pageSize' => $request->pageSize, 'previousPageIndex' => $request->previousPageIndex]);
        }
    }


    // function storeDashboardNotes(Request $request){
    //     $check = 0;
    //     $storeDashboardNotes = DashboardNotes::where('id', $request->id)->first();
    //     if(empty($storeDashboardNotes)){
    //        $storeDashboardNotes =  new  DashboardNotes();
    //        $check = 1;
    //     }
    //     $storeDashboardNotes->admin_id = $request->admin_id;
    //     $storeDashboardNotes->notes = json_encode($request->notes);
    //     $storeDashboardNotes->save();
    //     if($check == 0){
    //         return response()->json(['success' => true, 'msg' => 'Notes Updated Successfully!']);
    //     }else{
    //         return response()->json(['success' => true, 'msg' => 'Notes Added Successfully!']);
    //     }  
    // }
    
    function storeDashboardNotes(Request $request)
    {
        $data = [
            'admin_id' => $request->admin_id,
            'notes' => json_encode($request->notes),
        ];

        DashboardNotes::updateOrCreate(['id' => $request->id], $data);

        if ($request->id) {
            return response()->json(['success' => true, 'msg' => 'Notes Updated Successfully!']);
        } else {
            return response()->json(['success' => true, 'msg' => 'Notes Added Successfully!']);
        }
    }

    function getDashboardNotes(Request $request){

        $getDashboardNotes = DashboardNotes::where('admin_id', $request->admin_id)->select('id','notes')->get();
        if($getDashboardNotes){
            $gdn = GetDashboardNotesResource::collection($getDashboardNotes);
            return response()->json(['success' => true, 'data' => $gdn]);
        }else{
            return response()->json(['success' => false, 'data' => 'Record not found!']); 
        }  
    }

    function editDashboardNote(Request $request)
    {
        $editDashboardNote = DashboardNotes::where('admin_id', $request->admin_id)
            ->where('id', $request->id)
            ->select('id', 'notes')
            ->firstOrFail();

        return response()->json(['success' => true, 'data' => $editDashboardNote]);
    }

    function deleteDashboardNotes(Request $request)
    {
        $deletedRows = DashboardNotes::where('id', $request->id)->delete();

        if ($deletedRows) {
            return response()->json(['success' => true, 'msg' => 'Record Deleted Successfully!']);
        } else {
            return response()->json(['success' => true, 'msg' => 'Record Not Found!']);
        }
    }



    function getNearExpireLicenseGuard()
    {
        // $limit = 10;
        // $offset = 0;
        // if($request->has('pageIndex') && $request->has('pageSize'))
        // {
        //     $offset = $request->pageIndex * $request->pageSize;
        //     $limit = $request->pageSize;
        // }
        $currentDate = date('Y-m-d');
        $nextFifteendays = date('Y-m-d', strtotime('+15 day'));

        $guards_list = Guard::join('guards_documents', 'guards_documents.guard_id', '=', 'guards.id')
        ->where('guards_documents.document_type', 'security_license')
        ->where('guards.guard_status', 'active')
        ->whereBetween('guards_documents.document_expire', [$currentDate, $nextFifteendays])
        // ->skip($offset)
        // ->take($limit)
        ->select('guards.id', 'guards.first_name', 'guards.middle_name', 'guards.last_name', 'guards.email', 'guards.phone', 'guards_documents.document_expire')
        ->get();

        $total = Guard::join('guards_documents', 'guards_documents.guard_id', '=', 'guards.id')
        ->where('guards_documents.document_type', 'security_license')
        ->where('guards.guard_status', 'active')
        ->whereBetween('guards_documents.document_expire', [$currentDate, $nextFifteendays])
        ->select('guards.id')
        ->get()->count();
        if (count($guards_list) > 0) {
            return ['data' => $guards_list, 'length' => $total];
        }else{
           return ['data' => $guards_list, 'length' => $total];
        }
    }

    function getNearExpireVisaGuard(Request $request)
    {
        $limit = 10;
        $offset = 0;
        if($request->has('pageIndex') && $request->has('pageSize'))
        {
            $offset = $request->pageIndex * $request->pageSize;
            $limit = $request->pageSize;
        }
        $currentDate = date('Y-m-d');
        $nextFifteendays = date('Y-m-d', strtotime('+15 day'));
        $guards_list = Guard::join('guards_documents', 'guards_documents.guard_id', '=', 'guards.id')
        ->where('guards_documents.document_type', 'visa')
        ->whereBetween('guards_documents.document_expire', [$currentDate, $nextFifteendays])
        ->skip($offset)
        ->take($limit)
        ->select('guards.id', 'guards.first_name', 'guards.middle_name', 'guards.last_name', 'guards.email', 'guards.phone', 'guards_documents.document_expire')
        ->get();

        $total = Guard::join('guards_documents', 'guards_documents.guard_id', '=', 'guards.id')
        ->where('guards_documents.document_type', 'visa')
        ->whereBetween('guards_documents.document_expire', [$currentDate, $nextFifteendays])
        ->select('guards.id')
        ->get()->count();
        if (count($guards_list) > 0) {
            return response()->json(['success' => true, 'data' => $guards_list, 'length' => $total, 'pageIndex' => $request->pageIndex, 'pageSize' => $request->pageSize, 'previousPageIndex' => $request->previousPageIndex]);
        }else{
            return response()->json(['success' => false, 'data' => $guards_list, 'length' => $total, 'pageIndex' => $request->pageIndex, 'pageSize' => $request->pageSize, 'previousPageIndex' => $request->previousPageIndex]);
        }
    }

    function getNearExpirePassportGuard(Request $request)
    {
        $limit = 10;
        $offset = 0;
        if($request->has('pageIndex') && $request->has('pageSize'))
        {
            $offset = $request->pageIndex * $request->pageSize;
            $limit = $request->pageSize;
        }
        $currentDate = date('Y-m-d');
        $nextFifteendays = date('Y-m-d', strtotime('+15 day'));
        $guards_list = Guard::join('guards_documents', 'guards_documents.guard_id', '=', 'guards.id')
        ->where('guards_documents.document_type', 'passport')
        ->whereBetween('guards_documents.document_expire', [$currentDate, $nextFifteendays])
        ->skip($offset)
        ->take($limit)
        ->select('guards.id', 'guards.first_name', 'guards.middle_name', 'guards.last_name', 'guards.email', 'guards.phone', 'guards_documents.document_expire')
        ->get();

        $total = Guard::join('guards_documents', 'guards_documents.guard_id', '=', 'guards.id')
        ->where('guards_documents.document_type', 'passport')
        ->whereBetween('guards_documents.document_expire', [$currentDate, $nextFifteendays])
        ->select('guards.id')
        ->get()->count();
        if (count($guards_list) > 0) {
            return response()->json(['success' => true, 'data' => $guards_list, 'length' => $total, 'pageIndex' => $request->pageIndex, 'pageSize' => $request->pageSize, 'previousPageIndex' => $request->previousPageIndex]);
        }else{
            return response()->json(['success' => false, 'data' => $guards_list, 'length' => $total, 'pageIndex' => $request->pageIndex, 'pageSize' => $request->pageSize, 'previousPageIndex' => $request->previousPageIndex]);
        }
    }

    function liveWelfareCallData()
    {
        // $limit = 10;
        // $offset = 0;
        // if($request->has('pageIndex') && $request->has('pageSize'))
        // {
        //     $offset = $request->pageIndex * $request->pageSize;
        //     $limit = $request->pageSize;
        // }
        $welfare_data = WelfareCall::join('job_rosters', 'job_rosters.id', '=', 'welfare_call_data.job_roster_id')
        ->join('guards', 'guards.id', '=', 'welfare_call_data.guard_id')
        ->where('job_rosters.signin_status', 1)
        ->orderBy('welfare_call_data.send_time', 'ASC')
        // ->skip($offset)
        // ->take($limit)
        ->select('guards.id as guard_id', 'welfare_call_data.id as id', 'guards.first_name', 'guards.middle_name', 'guards.last_name', 'guards.email', 'guards.phone', 'welfare_call_data.status', 'welfare_call_data.send_time', 'welfare_call_data.response_time','guards.phone',  \DB::raw("'welfare_call' as type"))
        ->get();
        
        $total = WelfareCall::join('job_rosters', 'job_rosters.id', '=', 'welfare_call_data.job_roster_id')
        ->join('guards', 'guards.id', '=', 'welfare_call_data.guard_id')
        ->where('job_rosters.signin_status', 1)
        ->orderBy('welfare_call_data.send_time', 'ASC')
        ->select('welfare_call_data.id')
        ->get()->count();

        if (count($welfare_data) > 0) {
            foreach ($welfare_data as $key => $w) {
                $w->send_time = date('d-m-Y H:i', $w->send_time);
                $w->response_time = $w->response_time != null ? date('d-m-Y H:i', $w->response_time) : 'N/A';
            }
            return ['success' => true, 'data' => $welfare_data, 'length' => $total];
        }else{
            return ['success' => false, 'data' => $welfare_data, 'length' => $total];
        }
    }

    function livegreenCallData()
    {   
        $todayDate = Carbon::now();
        $startTime = $todayDate->startOfDay()->timestamp;
        $endTime = $todayDate->endOfDay()->timestamp;

        $greencall_data = GreenCall::join('job_rosters', 'job_rosters.id', '=', 'green_call.job_id')
        ->join('guards', 'guards.id', '=', 'green_call.guard_id')
        ->leftJoin('users', 'users.id', '=', 'green_call.admin_id')
        ->where('job_rosters.signin_status', 0)
        ->whereBetween('green_call.send_time', [$startTime, $endTime])
        ->orderBy('green_call.send_time', 'ASC')
        ->select(
            'green_call.id as id',
            'guards.id as guard_id',
            'guards.first_name',
            'guards.middle_name',
            'guards.last_name',
            'guards.email',
            'guards.phone',
            'green_call.manual_call',
            'green_call.status',
            'green_call.send_time',
            'green_call.response_time',
            'users.name as admin_name',
            \DB::raw("DATE_FORMAT(green_call.updated_at, '%Y-%m-%d %H-%i') AS updated_time"),
            \DB::raw("'green_call' as type"),
        )
        ->get();
    
        if (count($greencall_data) > 0) {
            foreach ($greencall_data as $key => $g) {
                $g->send_time = date('d-m-Y H:i', $g->send_time);
                $g->response_time = $g->response_time != null ? date('d-m-Y H:i', $g->response_time) : 'N/A';
            }
            return ['success' => true, 'data' => $greencall_data];
        }else{
            return ['success' => false, 'data' => $greencall_data];
        }
    }


    public function countActiveStaff() {

        $start = Carbon::now()->startOfWeek()->toDateString(); 
        $start = date('Y-m-d 00:00', strtotime($start));

        $end = Carbon::now()->endOfWeek()->toDateString();
        $end = date('Y-m-d 23:59', strtotime($end));
        
        $activeStaff = Guard::where('guard_status', 'active')
        ->where('is_available', 'yes')
        ->where('admin_approval_status', 'active')
        ->count();

        $currentStaff = JobRoster::where('start', '>=', $start)->where('start', '<=', $end)
        ->whereNotNull('guard_id')->groupBy('guard_id')->count();

        $currentDate = new DateTime();
        $jobCounts = [];
        for ($i = 0; $i < 7; $i++) {
            
            $day = $currentDate->format('d');

            //$totalJobs = JobRoster::whereDay('start', $day)->count();

            $guards = JobRoster::whereDay('start', $day)
            ->where(function ($query) {
                $query->where('job_status', 'completed')
                ->orWhere('admin_approved', 1)->whereNotNull('guard_id')->groupBy('guard_id');
            })->count();

            $activeStaff = Guard::where('guard_status', 'active')
            ->where('is_available', 'yes')
            ->where('admin_approval_status', 'active')
            ->count();

            $jobCounts[] = [
                'day' => $day,
                'current_guard' => $guards,
                'active_guard' => $activeStaff,
            ];
            $currentDate->modify('-1 day');
        }
        return ['activeStaff' => $activeStaff, 'currentStaff' => $currentStaff, 'graph' => $jobCounts];

    }

    function publishAndUnpublishShiftCountOneWeek(Request $request) {
        if ($request->has('start') && $request->start != '') {
            $start = dbFormate($request->start) . ' 00:00';
        } else {
            $start = Carbon::now()->startOfWeek()->toDateString();
            $start = date('Y-m-d 00:00', strtotime($start));
        }
    
        if ($request->has('end') && $request->end != '') {
            $end = dbFormate($request->end) . ' 23:59';
        } else {
            $end = Carbon::now()->endOfWeek()->toDateString();
            $end = date('Y-m-d 23:59', strtotime($end));
        }
    
        $roster = JobRoster::where('start', '>=', $start)
            ->where('start', '<=', $end)
            ->with('site')
            ->get();
    
        $mainArray = [];
    
        $currentDate = new DateTime($start);
    
        while ($currentDate <= new DateTime($end)) {
            $formattedDate = $currentDate->format('Y-m-d');
    
            foreach ($roster as $value) {
                $siteId = isset($value->site->id) ? $value->site->id : null;
                if ($siteId !== null) {
                    if (!isset($mainArray[$formattedDate][$siteId])) {
                        $mainArray[$formattedDate][$siteId] = [
                            'site' => !empty($value->site->site_name) ? $value->site->site_name : '',
                            'publish' => 0,
                            'unpublish' => 0,
                        ];
                    }
    
                    if ($value->start >= $formattedDate . ' 00:00' && $value->start <= $formattedDate . ' 23:59') {
                        if ($value->publish_status == 1) {
                            $mainArray[$formattedDate][$siteId]['publish']++;
                        } else {
                            $mainArray[$formattedDate][$siteId]['unpublish']++;
                        }
                    }
                }
            }
    
            $currentDate->modify('+1 day');
        }
    
        $resultArray = [];
    
        foreach ($mainArray as $date => $data) {
            foreach ($data as $siteId => $siteData) {
                $location = $siteData['site'];
    
                // Initialize the location record if it doesn't exist yet
                if (!isset($resultArray[$location])) {
                    $resultArray[$location] = [
                        'location' => $location,
                        
                    ];
                }
    
                // Update the counts for the specific day of the week
                $dayOfWeek = (new DateTime($date))->format('l');
                $resultArray[$location][$dayOfWeek] = $siteData['publish'] . ' | ' . $siteData['unpublish'];
            }
        }
    
        // Convert the associative array to a sequential array
        $resultArray = array_values($resultArray);
    
        return response()->json(['success' => true, 'data' => $resultArray]);
    }



    function getStaffConfirmation(Request $request){

        if($request->has('start') && $request->start != '')
        {
            $start = dbFormate($request->start). ' 00:00';
            
        }else{
            $start = Carbon::now()->startOfWeek()->toDateString(); 
            $start = date('Y-m-d 00:00', strtotime($start));
        }
        if($request->has('end') && $request->end != '')
        {
            $end = dbFormate($request->end). ' 23:59';
        }else{
            $end = Carbon::now()->endOfWeek()->toDateString();
            $end = date('Y-m-d 23:59', strtotime($end));
        }
        $shiftCounts = JobRoster::where('start', '>=', $start)
        ->where('start', '<=', $end)->
            select('job_status', \DB::raw('COUNT(*) as count'))
        ->whereIn('job_status', ['confirmed', 'pending', 'rejected'])
        ->groupBy('job_status')
        ->get();

        $shiftCountsArray = $shiftCounts->pluck('count', 'job_status')->toArray();

        $confirmedShiftsCount = $shiftCountsArray['confirmed'] ?? 0;
        $unconfirmedShiftsCount = $shiftCountsArray['pending'] ?? 0;
        $rejectedShiftsCount = $shiftCountsArray['rejected'] ?? 0;
        
        //Retrieve confirmed JobRoster instances
        $confirmedShifts = JobRoster::where('start', '>=', $start)
            ->where('start', '<=', $end)
            ->where('job_status', 'confirmed')
            ->join('guards', 'guards.id', '=', 'job_rosters.guard_id')
            ->select('job_rosters.id', 'guards.id as guard_id', 'guards.first_name', 'guards.middle_name', 'guards.last_name', 'guards.profile_image', DB::raw('COUNT(job_rosters.id) as shift_count'))
            ->groupBy('job_rosters.guard_id')->get();
    
        //Retrieve pending JobRoster instances
        $pendingShifts = JobRoster::where('start', '>=', $start)
        ->where('start', '<=', $end)
        ->where('job_status', 'pending')
        ->join('guards', 'guards.id', '=', 'job_rosters.guard_id') // Modify the join condition
        ->select('job_rosters.id', 'job_rosters.job_status', 'guards.id as guard_id', 'guards.first_name', 'guards.middle_name', 'guards.last_name', 'guards.profile_image', DB::raw('COUNT(job_rosters.id) as shift_count'))
        ->groupBy('job_rosters.guard_id')->get();
        
        return response()->json([
            'success' => true,
            'confirmedShifts' => $confirmedShifts, 'pendingShifts' => $pendingShifts,
            'confirmedShiftsCount' => $confirmedShiftsCount, 'unconfirmedShiftsCount' => $unconfirmedShiftsCount, 'rejectedShiftsCount' => $rejectedShiftsCount,
            
        ]);
    }

    public function greencalltoggle(Request $request)
    {
    
        $greencall = GreenCall::where('id', $request->id)->first();

        if ($greencall) {
            $greencall->manual_call = 1;
            $greencall->admin_id = $request->admin_id;
            $greencall->update();
        }

        return response()->json(['success' => true, 'msg' => 'Updated Successfully!']);
        
    }  

    public function callnotesupdate(Request $request)
    {

        if ($request->type == "green_call") {
            $greencall_notes = GreenCall::where('id', $request->id)->first();
            
            if ($greencall_notes) {
                
                $existingData = json_decode($greencall_notes->admin_notes_id, true) ?? [];
            
                if (!is_array($existingData)) {
                    $existingData = [];
                }
            
                $newData = [
                    'admin_id' => $request->admin_id,
                    'note' => $request->note,
                    'admin_name' => $request->admin_name,
                ];
            
                $existingData[] = $newData;
                $updatedData = json_encode($existingData);
                $greencall_notes->admin_notes_id = $updatedData;
                $greencall_notes->save();
            }
        } elseif ($request->type == "welfare_call") {
            $welfarecall_notes = WelfareCall::where('id', $request->id)->first();
            
            if ($welfarecall_notes) {
                
                $existingData = json_decode($welfarecall_notes->admin_notes_id, true) ?? [];
            
                if (!is_array($existingData)) {
                    $existingData = [];
                }
            
                $newData = [
                    'admin_id' => $request->admin_id,
                    'note' => $request->note,
                    'admin_name' => $request->admin_name,
                ];
            
                $existingData[] = $newData;
                $updatedData = json_encode($existingData);
                $welfarecall_notes->admin_notes_id = $updatedData;
                $welfarecall_notes->save();
            }
        }
        
        return response()->json(['success' => true, 'msg' => 'Updated Successfully!']);
        
    }  

    public function getcallnotes(Request $request)
    {
    
        if ($request->type == "green_call") {
            $get_notes = GreenCall::where('id', $request->id)->value('admin_notes_id');
            $notes = json_decode($get_notes, true);

        }elseif($request->type == "welfare_call"){
            $get_notes = WelfareCall::where('id', $request->id)->value('admin_notes_id');
            $notes = json_decode($get_notes, true);

        }

        return response()->json(['success' => true, 'data' => $notes]);
        
    } 

    
}
