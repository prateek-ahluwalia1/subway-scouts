<?php

namespace App\Http\Controllers\reports;

use App\Exports\AwardOverTimeExport;
use App\Exports\HrsByEmpReportExport;
use App\Exports\AdhocHoursExport;
use App\Http\Controllers\Controller;
use DateTime, DateInterval, DatePeriod;
use App\Models\GuardLeave;
use \PDF;
use App\Models\JobRoster;
use App\Models\Staff_injury;
use App\Models\Guard_leave_monthly;
use App\Models\PatrollingReport;
use App\Models\Near_misses;
use App\Models\Monthly_point_contact;
use App\Models\Guard;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Dompdf\Options;
use Dompdf\Dompdf;
class MonthlyReport extends Controller
{
    function generate_pdf_monthly_report(Request $request){
            $monthNo = $request->input('month', Carbon::now()->month);
            $currentYear = Carbon::now()->year;
            $date = Carbon::create($currentYear, $monthNo, 1);
            $formattedDate = $date->format('F Y');
            $data['month_year'] = $formattedDate;
            $currentMonthStart = Carbon::now()->month($monthNo)->startOfMonth()->format('Y-m-d 00:00:00');
            $currentMonthEnd = Carbon::now()->month($monthNo)->endOfMonth()->format('Y-m-d 23:59:59');
            // $siteIds = [];
            if ($request->has('site_id')) {
                $siteIds = $request->site_id;
            } elseif ($request->has('customer_id')) {
                $siteIds = DB::table('sites')
                    ->select('id')
                    ->where('customer_id', $request->customer_id)
                    ->distinct()
                    ->pluck('id')
                    ->toArray();
            }
            //first graph start
            $reportData = DB::table('incident_reports')
            ->whereIn('job_id', $siteIds)
            ->whereBetween('created_at', [$currentMonthStart, $currentMonthEnd])
            ->select('injury_type', DB::raw('count(*) as injury_count'))
            ->groupBy('injury_type')
            ->get();
    
            $injuryCounts = $reportData->pluck('injury_count')->toArray();
            $injuryTypes = $reportData->pluck('injury_type')->map(function ($injuryType) {
                return strlen($injuryType) > 15 ? substr($injuryType, 0, 12) . '...' : $injuryType;
            })->toArray();
    
            // Define base colors, including #01A37E
            $baseBackgroundColors = [
                "#00ADEE",
                "#00ADEE",
                "#00ADEE",
                "#00ADEE",
                "#00ADEE",
                "#00ADEE",
                "#00ADEE"
            ];
    
            $baseBorderColors = [
                "#00ADEE",
                "#00ADEE",
                "#00ADEE",
                "#00ADEE",
                "#00ADEE",
                "#00ADEE",
                "#00ADEE"
            ];
    
            // Generate dynamic colors based on the number of injury types
            $backgroundColors = [];
            $borderColors = [];
            for ($i = 0; $i < count($injuryTypes); $i++) {
                $backgroundColors[] = $baseBackgroundColors[$i % count($baseBackgroundColors)];
                $borderColors[] = $baseBorderColors[$i % count($baseBorderColors)];
            }
    
            
                $chartConfig = [
            "type" => "bar",
            "data" => [
                "labels" => $injuryTypes,
                "datasets" => [
                    [
                        "label" => "# of Incidents",
                        "data" => $injuryCounts,
                        "backgroundColor" => $backgroundColors,
                        "borderColor" => $borderColors,
                        "borderWidth" => 1
                    ]
                ]
            ],
            "options" => [
                "scales" => [
                    "y" => [
                        "beginAtZero" => true
                    ]
                ]
            ]
        ];
    
            $monthlyChart = 'https://quickchart.io/chart?c=' . urlencode(json_encode($chartConfig));
            $data['monthlyImage'] = $monthlyChart;
            // $data['monthlyImage'] =  $base64Chart = base64_encode(file_get_contents($monthlyChart));
    
    
            //first graph end data save in $injuryTypes and $injuryCounts
    
            //second graph start
    //     $allReport = DB::table('incident_reports')
    //         ->whereIn('job_id', $siteIds)
    //         ->select('injury_type', DB::raw('count(*) as injury_count'))
    //         ->groupBy('injury_type')
    //         ->get();
    
    // $allInjuryCounts = $allReport->pluck('injury_count')->toArray();
    // $allInjuryTypes = $allReport->pluck('injury_type')->map(function ($injuryType) {
    //     return strlen($injuryType) > 15 ? substr($injuryType, 0, 12) . '...' : $injuryType;
    // })->toArray();
    
    // $baseColors = [
    //     "#01A37E", "#FF5733", "#FFC300", "#C70039", "#900C3F",
    //     "#581845", "#5DADE2", "#1ABC9C", "#F39C12", "#D35400", "#b2b2b2",
    //     "#01A37E", "#FF5733", "#FFC300", "#C70039", "#900C3F",
    //     "#581845", "#5DADE2", "#1ABC9C", "#F39C12", "#D35400", "#b2b2b2",
    //     "#581845", "#581845", "#5DADE2", "#1ABC9C", "#F39C12", "#D35400",
        
    //     // Add more colors as needed
    // ];
    
    // $backgroundColors = [];
    // $borderColors = [];
    // foreach ($allInjuryTypes as $key => $injuryType) {
    //     $index = $key % count($baseColors);
    //     $backgroundColors[] = $baseColors[$index];
    //     $borderColors[] = $baseColors[$index];
    // }
    
    // $chartConfig = [
    //     "type" => "doughnut",
    //     "data" => [
    //         "labels" => $allInjuryTypes,
    //         "datasets" => [
    //             [
    //                 "label" => "# of Incidents",
    //                 "data" => $allInjuryCounts,
    //             ]
    //         ]
    //     ],
    //     "options" => [
    //         "cutout" => "70%",
    //         "rotation" => -0.5 * pi(),
    //         "circumference" => 2 * pi(),
    //         "plugins" => [
    //             "legend" => [
    //                 "position" => "top"
    //             ]
    //         ]
    //     ]
    // ];
    
    // $chartJson = json_encode($chartConfig);
    
    // if (json_last_error() !== JSON_ERROR_NONE) {
    
    //     die('JSON Error: ' . json_last_error_msg());
    // }
    
    // $chartUrl = 'https://quickchart.io/chart?c=' . urlencode($chartJson);
    // $data['allIncidentChart'] = $chartUrl;
    $allReport = DB::table('incident_reports')
    ->whereIn('job_id', $siteIds)
    ->select('injury_type', DB::raw('count(*) as injury_count'))
    ->groupBy('injury_type')
    ->get();

    // ✅ Handle empty data properly
    if ($allReport->isEmpty()) {
        $allInjuryCounts = [1]; // show full donut visually
        $allInjuryTypes = ['No Data'];
        $totalIncidents = 0; // display number
    } else {
        $allInjuryCounts = $allReport->pluck('injury_count')->toArray();
        $allInjuryTypes = $allReport->pluck('injury_type')->map(function ($injuryType) {
            return strlen($injuryType) > 15 ? substr($injuryType, 0, 12) . '...' : $injuryType;
        })->toArray();
        $totalIncidents = array_sum($allInjuryCounts);
    }

    $baseColors = [
        "#01A37E", "#FF5733", "#FFC300", "#C70039", "#900C3F",
        "#581845", "#5DADE2", "#1ABC9C", "#F39C12", "#D35400", "#b2b2b2"
    ];

    $backgroundColors = [];
    foreach ($allInjuryTypes as $key => $injuryType) {
        $index = $key % count($baseColors);
        $backgroundColors[] = $baseColors[$index];
    }

    $chartConfig = [
        "type" => "doughnut",
        "data" => [
            "labels" => $allInjuryTypes,
            "datasets" => [
                [
                    "label" => "# of Incidents",
                    "data" => $allInjuryCounts,
                    "backgroundColor" => $backgroundColors,
                    "borderWidth" => 1
                ]
            ]
        ],
        "options" => [
            "cutout" => "70%",
            "plugins" => [
                "legend" => ["position" => "top"],
                "title" => [
                    "display" => true,
                    "text" => "Incident Report Summary"
                ],
                "doughnutlabel" => [
                    "labels" => [
                        [
                            "text" => $totalIncidents,
                            "font" => [
                                "size" => 24,
                                "weight" => "bold"
                            ]
                        ],
                        ["text" => "Total"]
                    ]
                ]
            ]
        ]
    ];

    // ✅ Force chart to render nicely even for 0
    $chartUrl = "https://quickchart.io/chart?width=400&height=400&c=" . urlencode(json_encode($chartConfig));

    $data['allIncidentChart'] = $chartUrl;

    
            //INCIDENT CATEGORIES HISTORICAL RECORDS START
            $data['incident'] = DB::table('incident_reports')
            ->whereIn('job_id', $siteIds)
            ->whereBetween('created_at', [$currentMonthStart, $currentMonthEnd])
            ->select('injury_type', 'incident_date', 'incident_time', 'injury_detail')
            ->groupBy('injury_type')
            ->get();
            
    
            //INCIDENT CATEGORIES HISTORICAL RECORDS END
           //STAFF INJURY
    
           $data['staff_injury'] = DB::table('staff_injuries')
            ->join('guards', 'staff_injuries.staff_member', '=', 'guards.id')
            ->select('staff_injuries.*',
            DB::raw("TRIM(CONCAT(guards.first_name, ' ', COALESCE(guards.middle_name, ''), ' ', guards.last_name)) AS guard_name")
            )
            ->whereIn('staff_injuries.site_id', $siteIds)
            ->whereBetween('staff_injuries.date', [$currentMonthStart, $currentMonthEnd])
            ->get();
    
           //STAFF INJURY END
           //NEAR MISSES
    
           $data['near_misses'] = DB::table('monthly_near_misses')
            ->join('guards', 'monthly_near_misses.staff_member', '=', 'guards.id')
            ->select('monthly_near_misses.*',
            DB::raw("TRIM(CONCAT(guards.first_name, ' ', COALESCE(guards.middle_name, ''), ' ', guards.last_name)) AS guard_name")
            )
            ->whereIn('monthly_near_misses.site_id', $siteIds)
            ->whereBetween('monthly_near_misses.date', [$currentMonthStart, $currentMonthEnd])
            ->get();
    
           //NEAR MISSES END
           //MONTHLY LEAVE REQUEST
    
           $data['monthly_leave'] = DB::table('guard_leave_monthly')
            ->leftjoin('guards', 'guard_leave_monthly.guard_id', '=', 'guards.id')
            ->leftjoin('sites', 'guard_leave_monthly.site_id', '=', 'sites.id')
            ->select('guard_leave_monthly.*', 
            DB::raw("TRIM(CONCAT(guards.first_name, ' ', COALESCE(guards.middle_name, ''), ' ', guards.last_name)) AS guard_name"),
            'sites.site_name as site_name')
            ->whereIn('guard_leave_monthly.site_id', $siteIds)
            ->whereBetween('guard_leave_monthly.start', [$currentMonthStart, $currentMonthEnd])
            ->get();
    
           //MONTHLY LEAVE REQUEST END
           //POINT CONTACT
    
           $data['point_contact'] = DB::table('monthly_point_contact')
            ->leftjoin('sites', 'monthly_point_contact.site_id', '=', 'sites.id')
            ->select('monthly_point_contact.*', 'sites.site_name as site_name')
            ->whereIn('monthly_point_contact.site_id', $siteIds)
            ->whereBetween('monthly_point_contact.date', [$currentMonthStart, $currentMonthEnd])
            ->get();
    
           //POINT CONTACT END
           //YEAR OF DATE
    
        $endDate = Carbon::create(null, $monthNo, 1)->endOfMonth();
        $startDate = $endDate->copy()->subMonths(5)->startOfMonth();
    
        $data['year_to_date'] = [];
    
        for ($i = 0; $i < 6; $i++) {
            $currentMonthStartt = $startDate->copy()->addMonths($i);
            $currentMonthEndd = $currentMonthStartt->copy()->endOfMonth();
    
            $injuryCount = DB::table('staff_injuries')
                ->whereIn('site_id', $siteIds)
                ->whereBetween('date', [$currentMonthStartt, $currentMonthEndd])
                ->count();
    
            $nearMissCount = DB::table('monthly_near_misses')
                ->whereIn('site_id', $siteIds)
                ->whereBetween('date', [$currentMonthStartt, $currentMonthEndd])
                ->count();
    
            $daysLostTotal = DB::table('staff_injuries')
                ->whereIn('site_id', $siteIds)
                ->whereBetween('date', [$currentMonthStartt, $currentMonthEndd])
                ->sum('days_lost');
    
            $data['year_to_date'][] = [
                'month' => $currentMonthStartt->format('F Y'),
                'injury_count' => $injuryCount,
                'near_miss_count' => $nearMissCount,
                'days_lost_total' => $daysLostTotal,
            ];
        }
           //YEAR OF DATE END
            //FOOT PERTOL REPORT START
    
            $footPatrolReports = DB::table('foot_patrol_reports')
            ->leftJoin('guards', 'guards.id', '=', 'foot_patrol_reports.guard_id')
            ->leftJoin('sites', 'sites.id','=','foot_patrol_reports.job_id')
            ->leftJoin('customers', 'customers.id','=','sites.customer_id')
            ->leftJoin('job_rosters', 'job_rosters.id', '=', 'foot_patrol_reports.roster_id')
            ->whereIn('foot_patrol_reports.job_id', $siteIds)
            ->whereBetween('foot_patrol_reports.date', [$currentMonthStart, $currentMonthEnd])
            ->select('foot_patrol_reports.*', 
            'guards.first_name as guard_name', 'guards.last_name', 'guards.middle_name', 
            'guards.phone', 'sites.address', 'sites.site_name', 'sites.site_description', 
            'job_rosters.start', 'job_rosters.end', 'customers.name as customer_name')->get();
    
            $data['foot_petrol'] = $footPatrolReports;
    
            $data['patrolling_report'] = PatrollingReport::with('scanners')
            ->leftJoin('job_rosters', 'job_rosters.id', '=', 'patrolling_reports.roster_id')
            ->leftJoin('guards', 'guards.id', '=', 'patrolling_reports.guard_id')
            ->leftJoin('sites', 'sites.id', '=', 'job_rosters.site_id')
            ->select('patrolling_reports.*',
            'job_rosters.start as start_time',
            'job_rosters.end as end_time',
            'guards.first_name',
            'guards.middle_name',
            'guards.last_name',
            'sites.site_name'
            )
            ->whereIn('patrolling_reports.site_id', $siteIds)
            ->whereBetween('patrolling_reports.created_at', [$currentMonthStart, $currentMonthEnd])
            ->get();
            
            $trained_guard = DB::table('guard_sites_trained')->whereIn('guard_sites_trained.site_id', $siteIds)
            ->select('guard_id')
            ->distinct()
            ->pluck('guard_id')
            ->toArray();
    
            $startOfMonth = Carbon::now()->month($monthNo)->startOfMonth();
            $endOfMonth = Carbon::now()->month($monthNo)->endOfMonth();
    
            $leaverequestStart = $startOfMonth->timestamp;
            $leaverequestEnd = $endOfMonth->timestamp;
    
            // Query to fetch the leave requests
            $data['guard_leave_request'] = DB::table('guard_leave_requests')
            ->leftJoin('guards', 'guards.id', '=', 'guard_leave_requests.guard_id')
                ->whereIn('guard_leave_requests.guard_id', $trained_guard)
                ->whereBetween('guard_leave_requests.start_date', [$leaverequestStart, $leaverequestEnd])
                ->get();
    
            //FOOT PERTOL REPORT END
            //GUARDS START
            $roster_guard_ids = DB::table('job_rosters')->whereIn('job_rosters.site_id', $siteIds)
            ->whereBetween('job_rosters.start', [$currentMonthStart, $currentMonthEnd])
            ->select('guard_id')
            ->distinct()
            ->pluck('guard_id')
            ->toArray();

        $query = Guard::with('guardDocuments')
            ->whereIn('id', $roster_guard_ids)
            ->orderBy('first_name', 'asc')
            ->get();

        $aggregatedResults = [];
        $documentTypes = ['security_license', 'first_aid', 'working_with_children'];

        foreach ($query as $record) {
            $id1 = 'N/A';
            $id2 = 'N/A';
            $customerId = 'N/A';

            $ids = DB::table('guard_external_ids')
                ->select('external_id', 'customer_id')
                ->where('guard_id', $record->id)
                ->where('customer_id', $request->customer_id)
                ->get();

            foreach ($ids as $id) {
                if (preg_match('/AMG/i', $id->external_id)) {
                    $id1 = $id->external_id;
                    $customerId = $id->customer_id;
                }
                if (!preg_match('/AMG/i', $id->external_id) && $id->external_id > 0) {
                    $id2 = $id->external_id;
                    $customerId = $id->customer_id;
                }
            }

            // Set up guard info
            $guardInfo = [
                'guard_id' => $record->id,
                'guard_name' => trim($record->first_name . ' ' . ($record->middle_name ?? '') . ' ' . $record->last_name),
                'customer_id' => $customerId,
                'wilson' => $id1,
                'certis' => $id2,
            ];

            foreach ($documentTypes as $docType) {
                $guardInfo[$docType . '_no'] = 'N/A';
                $guardInfo[$docType . '_exp'] = 'N/A';
            }

            foreach ($record->guardDocuments as $doc) {
                if (in_array($doc->document_type, $documentTypes)) {
                    $guardInfo[$doc->document_type . '_no'] = $doc->document_no ?? 'N/A';
                    $guardInfo[$doc->document_type . '_exp'] = $this->isValidDate($doc->document_expire) 
    ? \Carbon\Carbon::parse($doc->document_expire)->format('d-m-Y') 
    : ($doc->document_expire ?: 'N/A');
                }
            }

            $aggregatedResults[] = $guardInfo;
        }

        $data['guards'] = $aggregatedResults;
    
            //GUARDS END
            $MonthStart = Carbon::now()->month($monthNo)->startOfMonth()->format('Y-m-d') . ' 00:00';
            $MonthEnd = Carbon::now()->month($monthNo)->endOfMonth()->format('Y-m-d') . ' 23:59';
    
            //Mobile Call Data Start
    
                $welfareCallDataQuery = Jobroster::join('welfare_call_data', 'welfare_call_data.job_roster_id', '=', 'job_rosters.id')
                    ->leftJoin('job_roster_activites', 'job_roster_activites.job_roster_id', '=', 'job_rosters.id')
                    ->leftJoin('guards', 'guards.id', '=', 'welfare_call_data.guard_id')
                    ->leftJoin('sites', 'sites.id', '=', 'job_rosters.site_id')
                    ->leftJoin('customers', 'customers.id', '=', 'sites.customer_id')
                    ->whereBetween('job_rosters.start', [$MonthStart, $MonthEnd])
                    ->whereIn('sites.id', $siteIds)            
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
                        'welfare_call_data.created_at',
                        'welfare_call_data.response_time',
                        'sites.id as site_id',
                        \DB::raw("'Welfare Call' as call_type")
    
                    );
    
                $welfareCallData = $welfareCallDataQuery->get();
            
            // $greenCallDataQuery = Job_new_roster::join('green_call', 'green_call.job_id', '=', 'job_new_roster.roster_id')
            //     ->leftJoin('job_roster_activities', 'job_roster_activities.job_roster_id', '=', 'job_new_roster.roster_id')
            //     ->leftJoin('guards', 'guards.id', '=', 'green_call.guard_id')
            //     ->leftJoin('jobs', 'jobs.id', '=', 'job_new_roster.site_id')
            //     ->leftJoin('customers', 'customers.id', '=', 'jobs.customer_id')
            //     ->whereBetween('job_new_roster.start', [$MonthStart, $MonthEnd])
            //     ->whereIn('jobs.id', $siteIds)            
            //     ->orderBy('jobs.site_name', 'ASC')
            //     ->orderBy('green_call.created_at', 'ASC')
            //     ->select(
            //         'job_new_roster.roster_id as roster_id',
            //         'guards.id as guard_id',
            //         'green_call.id as green_call_id',
            //         'job_new_roster.start as date',
            //         'job_new_roster.start as start_time',
            //         'job_new_roster.end as end_time',
            //         'job_roster_activities.signin_time',
            //         'job_roster_activities.signout_time',
            //         'guards.first_name',
            //         'guards.middle_name',
            //         'guards.last_name',
            //         'jobs.site_name',
            //         'customers.name as customer_name',
            //         'green_call.status as response',
            //         'green_call.created_at',
            //         'green_call.updated_at',
            //         'jobs.id as site_id',
            //         \DB::raw("'Green Call' as call_type")
    
            //     );
    
            //     $greenCallData = $greenCallDataQuery->get();
                
            
            // $data = $greenCallData->merge($welfareCallData);
            // $mobileCallData = $greenCallData->concat($welfareCallData);
    
            // $data['greenCallYes'] = $greenCallData->where('response', 'yes')->count();
            // $data['greenCallNo'] = $greenCallData->where('response', 'no')->count();
    
            $data['welfareCallYes'] = $welfareCallData->where('response', 'yes')->count();
            $data['welfareCallNo'] = $welfareCallData->where('response', 'no')->count();
    
    
            // $data['callData'] = $mobileCallData;
    
            $query = Jobroster::query();
            $query->whereIn('site_id', $siteIds)
            ->whereBetween('start', [$MonthStart,$MonthEnd]);
            $data['JobCount'] = $query->count();
            $data['completedJobCount'] = Jobroster::whereBetween('start', [$MonthStart,$MonthEnd])->whereIn('site_id', $siteIds)->where('job_status', 'completed')->count();
            $data['rejectedJobs'] = Jobroster::whereBetween('start', [$MonthStart,$MonthEnd])->whereIn('site_id', $siteIds)->where('job_status', 'rejected')->count();
            $data['missedJobs'] = Jobroster::whereBetween('start', [$MonthStart,$MonthEnd])->whereIn('site_id', $siteIds)->where('job_status', 'missed')->count();
            $GuardCount = Jobroster::whereIn('site_id', $siteIds)
            ->whereBetween('start', [$MonthStart, $MonthEnd])
            ->distinct('guard_id')
            ->count('guard_id');
            $data['AdhocShift'] = Jobroster::whereIn('site_id', $siteIds)
            ->whereBetween('start', [$MonthStart, $MonthEnd])
            ->where('adhoc_shift', 'yes')
            ->sum('hours');
            
            $data['totalguards'] = $GuardCount;
            $autoSignIn = Jobroster::join('job_roster_activites', 'job_roster_activites.job_roster_id', '=', 'job_rosters.id')
            ->whereIn('job_rosters.site_id', $siteIds)
            ->whereBetween('job_rosters.start', [$MonthStart, $MonthEnd])
            ->where('job_roster_activites.auto_signout', 1)
            ->count();
    
            $data['autoSignInCount'] = $autoSignIn;
        
            if ($data) {
                $html = view('exports/monthly-pdf-report', [
                    'report' => $data
                ])->render();
                // Set up Dompdf options
                $options = new Options();
                $options->set('isRemoteEnabled', true);
                $options->set('isHtml5ParserEnabled', true);
                $options->set('isPhpEnabled', true);
                $options->set('defaultFont', 'DejaVu Sans');
                // Initialize Dompdf instance
                $dompdf = new Dompdf($options);
                $dompdf->loadHtml($html);
                $dompdf->setPaper('A4', 'portrait');
                $dompdf->render();
                // Define the folder where the PDF will be saved
                $pdf_directory = public_path('pdf_reports');
                if (!file_exists($pdf_directory)) {
                    mkdir($pdf_directory, 0777, true); // Create folder if it doesn't exist
                }
                // Create the PDF file name
                $random_number = rand(10000, 99999);
                $pdf_name = date('Y-m-d') . '_' . $random_number . '_monthly_report.pdf';
                $pdf_path = $pdf_directory . '/' . $pdf_name;
                // Save the generated PDF to the directory
                file_put_contents($pdf_path, $dompdf->output());
                // Get the URL of the saved PDF
                $pdf_url = url('pdf_reports/' . $pdf_name);
                // Return the URL as a JSON response
                return response()->json(['success' => true, 'pdf_url' => $pdf_url]);
            } else {
                // Return error if no data is found
                return response()->json(['success' => false, 'message' => 'Monthly report not found']);
            }
            
    }

    public function storeStaffInjury(Request $request)
    {
        $data = $request->input('data');

        if (!empty($data) && is_array($data)) {

        foreach ($data as $incident) {
        
            $staff_injury = new Staff_injury;
            $staff_injury->date = $incident['date'];
            $staff_injury->staff_member = $incident['staff_member'];
            $staff_injury->injury_type = $incident['injury_type'];
            $staff_injury->days_lost = $incident['days_lost'];
            $staff_injury->description = $incident['description'];
            $staff_injury->site_id = $incident['site_id'];


            $staff_injury->save();
        }

        return response()->json(['success' => 'Incidents recorded successfully']);
       }
       return response()->json(['error' => 'Record Not Found'], 400);

    }

    public function storeGuardLeave(Request $request)
    {
        $data = $request->input('leave_request');

        if (!empty($data) && is_array($data)) {
            foreach ($data as $leave_request) {
            
                $guardLeave = new Guard_leave_monthly;
                $guardLeave->start = $leave_request['start'];
                $guardLeave->end = $leave_request['end'];
                $guardLeave->guard_id = $leave_request['guard_id'];
                $guardLeave->description = $leave_request['description'];
                $guardLeave->site_id = $leave_request['site_id'];

                $guardLeave->save();
            }

            return response()->json(['success' => 'Guard Leave recorded successfully']);
        }

        return response()->json(['error' => 'No Guard leave requests provided'], 400);
    }

    private function isValidDate($date)
{
    if (empty($date) || !is_string($date)) {
        return false;
    }
    
    // Check if it's a valid date string
    try {
        \Carbon\Carbon::parse($date);
        return true;
    } catch (\Exception $e) {
        return false;
    }
}
    public function storePointContact(Request $request)
    {
        $data = $request->input('point_contact');

        if (!empty($data) && is_array($data)) {
            foreach ($data as $point_contact) {
            
                $pointcontact = new Monthly_point_contact;
                $pointcontact->name = $point_contact['name'];
                $pointcontact->phone = $point_contact['phone'];
                $pointcontact->email = $point_contact['email'];
                $pointcontact->date = $point_contact['date'];
                $pointcontact->site_id = $point_contact['site_id'];

                $pointcontact->save();
            }

            return response()->json(['success' => 'Point Contact Added successfully']);
        }

        return response()->json(['error' => 'Point of Contact Record Not Found.'], 400);
    }

    public function storeNearMisses(Request $request)
    {
        $data = $request->input('near_misses');

        foreach ($data as $near_miss) {
            
            $misses = new Near_misses;

            $misses->date = $near_miss['date'];
            $misses->staff_member = $near_miss['staff_member'];
            $misses->description = $near_miss['description'];
            $misses->action_taken = $near_miss['action_taken'];
            $misses->site_id = $near_miss['site_id'];
            
            $misses->save();
        }

        return response()->json(['success' => 'Near Misses recorded successfully']);
    }
}
