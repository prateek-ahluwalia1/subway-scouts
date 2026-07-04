<?php

namespace App\Http\Controllers\reports;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\GuardReportExport;
use App\Exports\GuestAuthReportExport;
use DB;
use DateTime;
use App\Models\Guard;
use Carbon\Carbon;
use DateInterval;
use DatePeriod;

class GuardReport extends Controller
{
    function generateGuardReport(Request $request)
    {
        if($request->type == 'preview'){
            $getData = $this->getReportData($request);
            return response()->json([
                'success' => true,
                'data' => $getData
            ]);
        }
        $filename = time().'_staff_report.xlsx';
        Excel::store(new GuardReportExport, 'excel/guard/'.$filename, 'excels');
        return response()->json(['success' =>  true, 'message' => 'Staff Report generated successfully.','path' => 'https://'.request()->getHttpHost().'/excel/guard/'.$filename]);
    }

    function generateGuestAuthReport(Request $request)
    {
        if($request->type == 'preview'){
            $getData = $this->getGuestAuthReportData($request);
            return response()->json([
                'success' => true,
                'data' => $getData
            ]);
        }
        $filename = time().'_guest_auth_report.xlsx';
        Excel::store(new GuestAuthReportExport, 'excel/guard/'.$filename, 'excels');
        return response()->json(['success' =>  true, 'message' => 'Guest Auth Report generated successfully.','path' => 'https://'.request()->getHttpHost().'/excel/guard/'.$filename]);
    }

    public function getGuestAuthReportData(Request $request)
    {
        if ($request->date) {
            $from_to = explode("-", $request->date);
            $from = trim($from_to[0]);
            $to = trim($from_to[1]);
        
            $timestamp = strtotime($from);
            $timestamp_to = strtotime($to);
        
            $from = date("Y-m-d 00:00:00", $timestamp);
            $to = date("Y-m-d 23:59:59", $timestamp_to);
        } else {
            $to = date("Y-m-d 23:59:59", time());
            $from = date("Y-m-d 00:00:00", time() - (60 * 60 * 24 * 7));
        }    
        $reportData = DB::table('guest_login')
        ->select(
            'id',
            'name',
            'signin_selfie',
            'signin_notes',
            'signout_selfie',
            'signout_notes',
            DB::raw("DATE_FORMAT(signin_time, '%d-%m-%Y %H:%i:%s') as signin_time"),
            DB::raw("DATE_FORMAT(signout_time, '%d-%m-%Y %H:%i:%s') as signout_time")
        )
        ->whereBetween('signin_time', [$from, $to])
        ->get();
        $date = $request->date;
        return $reportData;
    }

    function getReportData($request)
    {
        
       //dd($request['date']); 
        
    $query = Guard::query();
 
    if (isset($request['guard_ids']) && !empty($request['guard_ids'])) {
        $query->whereIn('guards.id', $request['guard_ids']);
    }
    if (isset($request['state']) && !empty($request['state'])) {
        $query->whereIn('state', $request['state']);
    }
    if (isset($request['guard_status']) && !empty($request['guard_status'])) {
        if($request['guard_status'] == 'active')
        {
        $query->where('guard_status', $request['guard_status']);
        }elseif($request['guard_status'] == 'inactive')
        {
        $query->where('guard_status', $request['guard_status']);
        }
    }

    if(isset($request['date']) && !empty($request['date']))
    {
        $date = explode(' - ', $request['date']);
        $start = dbFormate($date[0]).' 00:00:00';
        //dd($start);
        $end = dbFormate($date[1]).' 23:59:59';
        //dd($end);
        $query->where('created_at', '>=', $start)->where('created_at', '<=', $end);
    }
    
    $guards = $query->with(['empDetails'])->orderBy('first_name', 'asc')->where('guard_status', '!=', 'deleted')->get();

    $currentDate = Carbon::now()->format('Y-m-d');
    $week_array = $this->calculateFutureMonthFourthnight($currentDate);
    $fortnight_start_date = new DateTime($week_array['week_start']);
    $fortnight_end_date = new DateTime($week_array['week_end']);
    $dates_periods = array();
        $period = new DatePeriod(
            new DateTime($fortnight_start_date->format("Y-m-d")),
            new DateInterval('P1D'),
            new DateTime($fortnight_end_date->format("Y-m-d"))
        );

        foreach ($period as $key => $value) {
            array_push($dates_periods, $value->format('Y-m-d'));
        }

        array_push($dates_periods, $fortnight_end_date->format("Y-m-d"));
    $guardData = [];

    foreach ($guards as $guard) {

        // if($guard->empDetails->guard_document_type == 'student_visa')
        // {
        //     if($guard->empDetails->limit_exceed == 1)
        //     {
        //         $guardStart = new DateTime($guard->empDetails->start_time);
        //         $guardEnd = new DateTime($guard->empDetails->end_time);
        //         $interval = new DateInterval('P1D');
        //         $dateRange = new DatePeriod($guardStart, $interval, $guardEnd->modify('+1 day'));

        //         $guardDates = [];
        //         foreach ($dateRange as $date) {
        //             $guardDates[] = $date->format('Y-m-d');
        //         }
        //         $hasCompleteFortnight = false;

        //         $guardDateCount = count($guardDates);

        //         for ($i = 0; $i <= $guardDateCount - 14; $i++) {
        //             $fourteenDays = array_slice($guardDates, $i, 14);
                    
        //             $allExist = true;
        //             foreach ($fourteenDays as $day) {
        //                 if (!in_array($day, $dates_periods)) {
        //                     $allExist = false;
        //                     break;
        //                 }
        //             }
                    
        //             if ($allExist) {
        //                 $hasCompleteFortnight = true;
        //                 break;
        //             }
        //         }

        //         if ($hasCompleteFortnight) {
        //             $totalWorkingHours = 72;    
        //         } else {
        //             $totalWorkingHours = 48;
        //         }
        //     }else{
        //             $totalWorkingHours = 48;
        //     }
        // }else{
        //         $totalWorkingHours = $guard->empDetails->weekly_work_hours_limitation ?? 72;
        // }
        $guardInfo = [
            'id' => $guard->id,
            'first_name' => !empty($guard->first_name) ? $guard->first_name : 'N/A',
            'middle_name' => $guard->middle_name,
            'last_name' => !empty($guard->last_name) ? $guard->last_name : '',
            'created_at' => !empty($guard->created_at) ? usaToAusDateTime($guard->created_at) : '',
            'email' => !empty($guard->email) ? $guard->email : 'N/A',
            'phone' => !empty($guard->phone) ?  $guard->phone : 'N/A',
            'address' => !empty($guard->address) ? $guard->address : 'N/A',
            'suburb' => !empty($guard->suburb) ? $guard->suburb : 'N/A',
            'city' => !empty($guard->city) ? $guard->city : 'N/A',
            'state' => !empty($guard->state) ? $guard->state : 'N/A',
            'postal_code' => !empty($guard->postal_code) ?  $guard->postal_code : 'N/A',
            'dob' => !empty($guard->dob) && $guard->dob != '1970-01-01' ?  formatedDate($guard->dob) : 'N/A',
            'gender' => !empty($guard->gender) ? $guard->gender : 'N/A',
            'emergency_contact_name' => !empty($guard->emergency_contact_name) ? $guard->emergency_contact_name : 'N/A',
            'emergency_contact_phone' => !empty($guard->emergency_contact_phone) ?  $guard->emergency_contact_phone : 'N/A',
            'emergency_contact_email' => !empty($guard->emergency_contact_email) ? $guard->emergency_contact_email : 'N/A',
            'emergency_contact_relation' => !empty($guard->emergency_contact_relation) ? $guard->emergency_contact_relation : 'N/A',
            'joining_date' => !empty($guard->joining_date) && $guard->joining_date != '1970-01-01' ? formatedDate($guard->joining_date) : 'N/A',            
            'available' => $guard->is_available,
            'status' => $guard->guard_status,
            'admin_approval_status' => $guard->admin_approval_status,
            'staff_type' => $guard->staff_type,
            'employment_type' => $guard->guard_type,
            // 'work_limitation_status' => $guard->empDetails->work_hours_limitation_status,
            // 'work_limitation_hours' => $totalWorkingHours,	
            // 'customers' =>  ($guard->customer_id != '' && $guard->customer_id != null) ?  
            //     implode(', ', Customer::whereIn('id', json_decode($guard->customer_id))->pluck('name')->toArray()) : 
            //     null,
            'customers' =>  $guard->customer_id != '' && $guard->customer_id != 'null' && $guard->customer_id != null ?  
            (Customer::where(function($query) use ($guard){
                foreach (json_decode($guard->customer_id, true) ?? [] as $key => $value) {
                   $query->orWhere('id', $value);
                }
            })->select('name')->get()) : '',
        ];

        // $guardInfo['superannutation_no'] = !empty($guard->empDetails->superannutation_no) ? $guard->empDetails->superannutation_no : 'N/A';
        // $guardInfo['superannuation_fund'] = !empty($guard->empDetails->superannuation_fund) ? $guard->empDetails->superannuation_fund : 'N/A';
        // $guardInfo['superannuation_fund_usi'] = !empty($guard->empDetails->superannuation_fund_usi) ? $guard->empDetails->superannuation_fund_usi : 'N/A';
        // $guardInfo['member_number'] = !empty($guard->empDetails->member_number) ? $guard->empDetails->member_number : 'N/A';
        // $guardInfo['tfn_no'] = !empty($guard->empDetails->tfn_file_no) ? $guard->empDetails->tfn_file_no : 'N/A';
        // $guardInfo['resident_status'] = !empty($guard->empDetails->guard_document_type) ? $guard->empDetails->guard_document_type : 'N/A';
        // $guardInfo['abn_name'] = !empty($guard->empDetails->abn_name) ? $guard->empDetails->abn_name : 'N/A';
        // $guardInfo['abn_no'] = !empty($guard->empDetails->abn_no) ?  $guard->empDetails->abn_no : 'N/A';
        // $guardInfo['bank_name'] = !empty($guard->empDetails->bank_name) ? $guard->empDetails->bank_name : 'N/A';
        // $guardInfo['bsb'] = !empty($guard->empDetails->bsb) ?  $guard->empDetails->bsb : 'N/A';
        // $guardInfo['bank_account_no'] = !empty($guard->empDetails->bank_account_no) ?   $guard->empDetails->bank_account_no : 'N/A';

        // $record = new \stdClass();
        // $record->id1 = 'N/A';
        // $record->id2 = 'N/A';
        // $record->guard_id = $guard->id;

        // $ids = DB::table('guard_external_ids')->where('guard_id', $record->guard_id)->get();
        // foreach ($ids as $id) {
        // if (preg_match('/AMG/i', $id->external_id)) {
        //     $record->id1 = $id->external_id;
        // }
        // if (!preg_match('/AMG/i', $id->external_id) && $id->external_id > 0) {
        //     $record->id2 = $id->external_id;
        // }
        // }

        // $guardInfo['wilson'] = $record->id1;
        // $guardInfo['certis'] = $record->id2;
        
        // $documentTypes = [
        //     'passport',
        //     'security_license',
        //     'visa',
        //     'driver_license_front',
        //     'medicare',
        //     'vaccination',
        //     'working_with_children',
        //     'first_aid',
        //     'cpr',
        // ];

        // foreach ($documentTypes as $documentType) {
           
        //     $found = false;
        //     foreach ($guard->documents as $document) {
        //         if ($document->document_type === $documentType) {
        //             $guardInfo[$documentType . '_no'] = !empty($document->document_no) ? $document->document_no : 'N/A';
        //             $guardInfo[$documentType . '_exp'] = !empty($document->document_expire) ? $document->document_expire : 'N/A';
        //             $found = true;
        //             break;
        //         }
        //     }
        
        //     if (!$found) {
        //         $guardInfo[$documentType . '_no'] = 'N/A';
        //         $guardInfo[$documentType . '_exp'] = 'N/A';
        //     }
        // }

        // Process empDetails relationship
     
        if($guardInfo['customers']){
            $customerArray = json_decode($guardInfo['customers'], true);
            $names = array_column($customerArray, 'name');
            $guardInfo['customers'] = implode(', ', $names);
            
        }
        $guardData[] = $guardInfo;
    }

    return $guardData;
    }


     function getGuardReport(Request $request)
    {
        $req = request()->all();
        return response()->json(['success' =>  true, 'data' => $this->getReportData($req)]);
    }

    function calculateFutureMonthFourthnight($givenDate)
    {
        $startDate = '2025-12-01';
        
        // Try to parse with auto-detection logic
        $date = $this->parseDateWithAutoDetection($givenDate);
        
        $formattedDate = $date->format('Y-m-d H:i:s');
        $endDate = $formattedDate;
        $startTime = strtotime($startDate);
        $endTime = strtotime($endDate);

        $secondsDiff = $endTime - $startTime;
        $daysDiff = floor($secondsDiff / (60 * 60 * 24));
        $totalFourthnight = floor($daysDiff/14);
        $totalFourthnight = $totalFourthnight * 14;
        $FourthnightStartDate = date('Y-m-d', strtotime($startDate . ' + '.$totalFourthnight.' days'));
        $FourthnightEndDate = date('Y-m-d', strtotime($FourthnightStartDate . ' + 13 days'));
        
        $ret['week_start'] = $FourthnightStartDate;
        $ret['week_end'] = $FourthnightEndDate;
        return $ret;
    }

    private function parseDateWithAutoDetection($dateString)
    {
        preg_match('/^(\d{1,2})-(\d{1,2})-(\d{4})/', $dateString, $matches);
        
        if (count($matches) !== 4) {
            return Carbon::parse($dateString);
        }
        
        $first = (int)$matches[1];
        $second = (int)$matches[2];
        $year = (int)$matches[3];
        
        if ($first > 12 && $first <= 31) {
            $date = Carbon::createFromFormat('d-m-Y H:i', $dateString);
        } elseif ($second > 12 && $second <= 31) {
            $date = Carbon::createFromFormat('m-d-Y H:i', $dateString);
        } else {
            $date = Carbon::createFromFormat('d-m-Y H:i', $dateString);
        }
        
        if ($date === false || !$date->isValid()) {
            return Carbon::parse($dateString);
        }
        
        return $date;
    }
}
