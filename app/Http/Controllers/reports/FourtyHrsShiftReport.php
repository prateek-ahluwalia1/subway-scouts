<?php

namespace App\Http\Controllers\reports;

use App\Exports\AwardOverTimeExport;
use App\Exports\HrsByEmpReportExport;
use App\Exports\FourtyHoursExport;
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

class FourtyHrsShiftReport extends Controller
{


    function generateFourtyHoursShiftReport()
    {
        // return Excel::download(new InvoiceReportExport, 'invoice_report.xlsx');
        $filename = time() . '_40_hours_shift_report.xlsx';
        Excel::store(new FourtyHoursExport, 'excel/fourtyhours/' . $filename, 'excels');
        return response()->json(['success' => true, 'message' => '40 Hours Shift Report.', 'path' =>
        'https://' . request()->getHttpHost() . '/excel/fourtyhours/' . $filename]);
    }

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
    
    $start = new DateTime($from);
    $end = new DateTime($to);
    $interval = DateInterval::createFromDateString('1 day');
    $period = new DatePeriod($start, $interval, $end);
// print_r('<pre>');
// print_r($period);
// exit();
$no = 0;
$sundays = array();
foreach ($period as $dt) {
    if ($dt->format('N') == 7) {
        $no++;
        $sundays[] = $dt->format('m/d/Y');
    }
}
// echo $no;
// print_r($sundays);
// exit();

    $sql = "SELECT
    SUM(jr.`hours`) as total,
    -- jr.`roster_id`,
    jr.`start`,
    -- jr.`temp_end`,
    -- jr.`hours`,
    -- jr.`roster_id`,
    j.`site_name`,
    j.`site_description`,
    cust.`name` AS customer_name,
    c.`name` AS contractor_name,
    g.`id` AS guard_id,
    CONCAT(g.first_name, ' ', COALESCE(g.middle_name, ''), ' ', g.last_name) AS guard_name,
    -- gi.`internal_id`,
    gi.`external_id`
    FROM job_rosters AS jr
    INNER JOIN sites AS j ON j.`id` = jr.`site_id`
    INNER JOIN `guards` AS g ON g.`id` = jr.`guard_id`
    INNER JOIN customers AS cust ON j.`customer_id` = cust.`id`
    LEFT JOIN `contractors` AS c ON j.`contractor_id`= c.`id`
    LEFT JOIN `guard_external_ids` AS gi ON gi.`customer_id` = cust.`id` AND gi.`guard_id` = jr.`guard_id`
    -- LEFT JOIN `guard_payroll_ids` AS gpi ON gpi.`guard_id` = g.`id` AND gpi.`guard_id` = jr.`guard_id`
    WHERE jr.`shift_payable` = 'yes'  
    AND jr.`deleted_at` IS NULL
    AND jr.`start` BETWEEN '$from' AND '$to'
    -- AND jr.`start` BETWEEN '".date('Y-m-d', strtotime($from))."' AND '".date('Y-m-d', strtotime($to))."' 
    GROUP BY jr.guard_id 
    ORDER BY g.name ASC, jr.unprofile_name";
    $query = DB::select($sql);
    
        foreach ($query as $key => $q) {
            $sunday_shifts = DB::table('job_rosters')
    ->where(function($que) use ($sundays){
        foreach ($sundays as $key => $sun) {
            $sunday_start = date('Y-m-d 00:00', strtotime($sun));
            $sunday_end = date('Y-m-d 23:59', strtotime($sun));
            $que->orWhereBetween('start', [$sunday_start, $sunday_end]);
            $que->orWhereBetween('end', [$sunday_start, $sunday_end]);
        }
    })
    ->where('guard_id', $q->guard_id)
    ->get();
    $amg_id = DB::table('guard_external_ids')
    ->where('guard_id', $q->guard_id)
    ->where('external_id', 'like', '%AMG%')
    ->first();
    if (!empty($amg_id)) {
        $q->external_id = $amg_id->external_id;
    }
    // $q->sunday = $sunday_shifts;
    foreach ($sunday_shifts as $key => $s) {
        $job_hours = $this->getShiftHours(date('m/d/Y H:i', strtotime($s->start)), date('m/d/Y H:i', strtotime($s->end)));
            $job_hours['morning'] = $this->convertIntoWhole($job_hours['morning']);
            $job_hours['night'] = $this->convertIntoWhole($job_hours['night']);
            $job_hours['saturday_morning'] = $this->convertIntoWhole($job_hours['saturday_morning']);
            $job_hours['saturday_night'] = $this->convertIntoWhole($job_hours['saturday_night']);
            $job_hours['sunday_morning'] = $this->convertIntoWhole($job_hours['sunday_morning']);
            $job_hours['sunday_night'] = $this->convertIntoWhole($job_hours['sunday_night']);
            $job_hours['ph_morning'] = $this->convertIntoWhole($job_hours['ph_morning']);
            $job_hours['ph_night'] = $this->convertIntoWhole($job_hours['ph_night']);
            $q->total = $q->total - $job_hours['sunday_morning'] - $job_hours['sunday_night'];
            $q->sun = $sundays;
    }
        }
    return $query;
}

private function getShiftHours ($start, $end, $siteID = null, $public_holiday = null, $ph_duration = null) {
    $day_start = Carbon::parse($start)->format('l');
    $day_end = Carbon::parse($end)->format('l');

    $start = strtotime($start);
    $end = strtotime($end);

    $diff = $end - $start;
    $hours = round($diff / ( 60 * 60 ), 2);
    $morning_start = 6;
    $morning_end = 18;

    /*$afternoon_start = strtotime("15:00");
    $afternoon_end = strtotime("23:00");*/

    $night_start = 18;
    $night_end = 6;

    $shift_start = $this->convert_into_fraction($start);
    $shift_end = $this->convert_into_fraction($end);
    if ($shift_end < $shift_start) {
        $diff_new = $shift_end + 24 - $shift_start;
        if ($hours != $diff_new) {
               $hours = $diff_new;
           }   
    }
    // echo $shift_start;
    // echo $shift_end;
    // saturday calcultions
    $saturday_start = 0;
    $saturday_end = 0;
    $total_saturday_hours = 0;

    $total_ph_hours = 0;
    $ph_start = 0;
    $ph_end = 0;

    // publid holiday calculation start here
    $start_in_public_holiday = false;
    $end_in_public_holiday = false;
    if($siteID != null){
    $site_state = DB::table('sites')->where('id', $siteID)->select('state')->first();
    $states_array = array(
        'Victoria' => 'vic',
        'New South Wales' => 'nsw',
        'NSW' => 'nsw',
        'Queensland' => 'qld',
        'Tasmania' => 'tas',
        'Western Australia' => 'wa',
        'South Australia' => 'sa',
        'ACT' => 'act'
    );
    $state = $states_array[$site_state->state];
}else{
    $state = 'vic';
}   
    $public_holiday_start = DB::table('public_holidays')->where('date', date('Ymd', $start))->where('state', $state)->first();
    if ($public_holiday != null && $public_holiday == 1) {
        $start_in_public_holiday = true;
    }elseif (!empty($public_holiday_start)) {
        $start_in_public_holiday = true;
    }

    $public_holiday_end = DB::table('public_holidays')->where('date', date('Ymd', $end))->where('state', $state)->first();
    if (!empty($public_holiday_end)) {
        $end_in_public_holiday = true;
    }elseif($public_holiday != null && $public_holiday == 1 && $ph_duration == 1){
        $end_in_public_holiday = true;
    }

    if ($start_in_public_holiday && $end_in_public_holiday) {
        $total_ph_hours = $hours;
        $hours = 0;
        $ph_start = $shift_start;
        $ph_end = $shift_end;
        $shift_start = 0;
        $shift_end = 0;
        // echo 'whole day in PH - ';

    }elseif($start_in_public_holiday && !$end_in_public_holiday)
    {
        $ph_end = strtotime(date('m/d/Y 23:59:59', $start));
        $diff = $ph_end - $start;
        $total_ph_hours = round($diff / ( 60 * 60 ), 2);
        $ph_start = $this->convert_into_fraction($start);
        $ph_end = $this->convert_into_fraction($ph_end);
        $start = strtotime($public_holiday_start->date) + (60*60*24);
        $day_start = Carbon::parse(date('m/d/Y', $end))->format('l');
        $hours = $hours - $total_ph_hours;
        $shift_start = 0;
        // echo 'Start in PH - '.$total_ph_hours;
    }elseif(!$start_in_public_holiday && $end_in_public_holiday){
        $ph_start = strtotime(date('m/d/Y 00:00:00', strtotime($public_holiday_end->date)));
        $diff = $end - $ph_start;
        $total_ph_hours = round($diff / ( 60 * 60 ), 2);
        $ph_start = $this->convert_into_fraction($ph_start);
        // $ph_end = $this->convert_into_fraction($ph_end);
        $end = $this->convert_into_fraction($end);
        $shift_end = 0;
        $ph_end = $end;
        $end = $ph_start;
        $hours = $hours - $total_ph_hours;


        // echo $hours;
    }
    // $day_start = Carbon::parse($start)->format('l');
    // $day_end = Carbon::parse($end)->format('l');
    // print_r(expression)
    // print_r(date('m/d/Y H:i', $end));
    // print('<br>-');
    // print_r($end_in_public_holiday);
    // print('<br>total sat: ');   
    // print_r($total_saturday_hours);
    // print('<br>start: ');   
    // print_r($shift_start);
    // print('<br>end:     ');   
    // print_r($shift_end);
    // print('<br>hours : ');   
    // print_r($hours);
    // exit();
    // print('<br>');
    // print_r($night_end);
    // exit();

    // end of public holiday calculation

    if ($day_start == 'Saturday' && $day_end == 'Saturday') {
        $total_saturday_hours = $hours;
        $saturday_start = $shift_start;
        $saturday_end = $shift_end;
        $shift_start = 0;
        $shift_end = 0;
        $hours = 0;
    }elseif($day_start == 'Saturday' && $day_end != 'Saturday')
    {
        $sat_end = strtotime(date('m/d/Y 23:59:59', $start));
        $diff = $sat_end - $start;
        $total_saturday_hours = round($diff / ( 60 * 60 ), 2);
        $saturday_start = $shift_start;
        $saturday_end = $this->convert_into_fraction($sat_end);
        $shift_start = 0;
        $shift_end = 0;
        $hours = $hours - $total_saturday_hours;
    }elseif($day_start != 'Saturday' && $day_end == 'Saturday')
    {
        $sat_start = strtotime(date('m/d/Y 00:00:00', $end));
        $diff = $end - $sat_start;
        $total_saturday_hours = round($diff / ( 60 * 60 ), 2);
        $saturday_start = $this->convert_into_fraction($sat_start);
        $saturday_end = $shift_end;
        $shift_end = 24;
        $hours = $hours - $total_saturday_hours;
    }
    // sunday_calcultaon
    $sunday_start = 0;
    $sunday_end = 0;
    $total_sunday_hours = 0;
    if ($day_start == 'Sunday' && $day_end == 'Sunday') {
        $total_sunday_hours = $hours;
        $sunday_start = $shift_start;
        $sunday_end = $shift_end;
        $shift_start = 0;
        $shift_end = 0;
        $hours = 0;
    }elseif($day_start == 'Sunday' && $day_end != 'Sunday')
    {
        $sun_end = strtotime(date('m/d/Y 23:59:59', $start));
        $diff = $sun_end - $start;
        $total_sunday_hours = round($diff / ( 60 * 60 ), 2);    
        $sunday_start = $shift_start;
        $sunday_end = $this->convert_into_fraction($sun_end);

        $shift_start = 0;
        $hours = $hours-$total_sunday_hours;
    }elseif($day_start != 'Sunday' && $day_end == 'Sunday')
    {
        // date_default_timezone_set('US/Pacific');
        $sun_start = strtotime(date('m/d/Y 00:00:00', $end));
        // $diff = $end - $sun_start;
        // $total_sunday_hours = round($diff / ( 60 * 60 ), 2);
        $sunday_start = $this->convert_into_fraction($sun_start);
        $sunday_end = $this->convert_into_fraction($end);
        $total_sunday_hours = $sunday_end - $sunday_start;
        $shift_end = 24;
        $shift_start = 24;
        $hours = $hours - $total_sunday_hours;
        // echo $total_sunday_hours;
    // exit();
    }
    if ($start_in_public_holiday && $end_in_public_holiday) {
        $shift_start = 0;
        $shift_end = 0;
        $saturday_start = 0;
        $saturday_end = 0;
        $sunday_start = 0;
        $sunday_end = 0;
        $total_sunday_hours = 0;
        $total_saturday_hours = 0;
    }


    
    // print_r($saturday_start);
    // print('<br>');
    // print_r($saturday_end);
    // print('<br>total sat: ');   
    // print_r($hours);
    // print('<br>start: ');   
    // print_r($shift_start);
    // print('<br>end:     ');   
    // print_r($shift_end);
    // print('<br>hours : ');   
    // print_r($hours);
    // exit();
    // print('<br>');
    // print_r($night_end);
    // exit();

    $morning = round($this->calculateHoursMorning($shift_start, $shift_end, $morning_start, $morning_end), 2);
    

    $saturday_morning = round($this->calculateHoursMorning($saturday_start, $saturday_end, $morning_start, $morning_end), 2);
    // echo '--'.$saturday_morning;
    // exit();
    $sunday_morning = round($this->calculateHoursMorning($sunday_start, $sunday_end, $morning_start, $morning_end), 2);

    $ph_morning = round($this->calculateHoursMorning($ph_start, $ph_end, $morning_start, $morning_end), 2);

    

    if ($morning < 0) {
        $morning = 0;
    }
    if ($saturday_morning < 0) {
        $saturday_morning = 0;
    }
    if ($sunday_morning < 0) {
        $sunday_morning = 0;
    }
    // print_r($morning);

    return [
        // 'morning' => $this->intersection( $start1, $end, $morning_start, $morning_end ) / 3600,
        'morning' =>  $morning,
        'night' => round(((($hours - $morning) < 0) ? 0 : ($hours - $morning)), 2),
        'saturday_morning' => $saturday_morning,
        'saturday_night' => round(((($total_saturday_hours - $saturday_morning) < 0) ? 0 : ($total_saturday_hours - $saturday_morning)), 2),
        'sunday_morning' => $sunday_morning,
        'sunday_night' => round(((($total_sunday_hours - $sunday_morning) < 0) ? 0 : ($total_sunday_hours - $sunday_morning)), 2),
        'ph_morning' => $ph_morning,
        'ph_night' => round(((($total_ph_hours - $ph_morning) < 0) ? 0 : ($total_ph_hours - $ph_morning)), 2),

        // 'night' => $this->calculateHoursNight($shift_start, $shift_end, $night_start, $night_end ),
    ];
}

function calculateHoursMorning($shift_start, $shift_end, $start, $end)
{
   $shift_hours = 0;
   $shift_end1 = $shift_end;
   if ($shift_end < $shift_start || $shift_end == 00) {
    $shift_end1 = $shift_end + 24;
}
$shift_hours = $shift_end1 - $shift_start;
//     if ($shift_end < $shift_start) {
//     $shift_end = $shift_end - 24;
// }
// echo $shift_start.' -- '.$shift_end;
//     echo '<br>';
//     echo $start.' -- '.$end;
//     echo '<br>';
    // echo $shift_end1;

    // exit();

if ($shift_hours > 12) {

    // echo $shift_hours;
        


    if($shift_start <= $start && $shift_end > $end){
        // shift start in night and end in night
        return 12;
    }elseif($shift_start >= $start && $shift_start <= $end && $shift_end >= $start && $shift_end < $end){
        // shift start in day and end in day
     return ($end - $shift_start) + ($shift_end - $start);
 }elseif($shift_start < $start && $shift_end > $start && $shift_end <= $end)
 {
            // shift start in night and end in day
    return $shift_end - 6;
}
elseif($shift_start > $start && $shift_start > $end && $shift_end <=24 && $shift_end > $start && $shift_end <= $end)
{
            // shift start in night but in between 18:00 and 24:00 and end in day
    return $shift_end - 6;
}
elseif($shift_start >= $start && $shift_start <= $end && $shift_end > $end)
{
            // shift start in day and end in night
    return 18 - $shift_start;
}
       // elseif($shift_start > $start && $shift_start < $end && $shift_end > $start){
       //     return ($end - $shift_start) + ($shift_end - $start);
       // }
else{
    return 0;
}
}else{
// echo 'i am here';
if ($shift_end <= 6 && $shift_end < $shift_start) {
    $shift_end += 24;
}
if (($shift_start >= $start && $shift_start < $end) && ($shift_end > $start && $shift_end <= $end)) {
 return $shift_end - $shift_start;
}elseif(($shift_start >= $start && $shift_start < $end) && ($shift_end > $start && $shift_end > $end))
{
$shift_end = $end;
return $shift_end - $shift_start;
}elseif(($shift_start > $start && $shift_start > $end) && ($shift_end > $start && $shift_end <= $end)){
$shift_start = $start;
return $shift_end - $shift_start;
}elseif(($shift_start < $start && $shift_start < $end) && ($shift_end > $start && $shift_end <= $end)){
$shift_start = $start;
return $shift_end - $shift_start;
}elseif($shift_start < $start && $shift_end > $end){
return $end - $start;
}elseif($shift_start >= $end && $shift_end > $start && $shift_end < $end)
{
    // shift start in night in gone into day
    // echo 'Here';
return $shift_end - $start;
}
elseif($shift_start > $start && $shift_end < $end && $shift_end > $start){
return $end - $shift_start;
}elseif(true)
{

}   
else{
return 0;
}
}

}

function convertIntoWhole($hours)
{
    $total_hours = explode('.', $hours);
    if (sizeof($total_hours) > 1 ) {
      $partial = '.'.$total_hours[1];
      if ($partial < 0.1) {
        $hours = $total_hours[0];
    }
    if ($partial < 0.27 && $partial > 0.1) {
        $hours = $total_hours[0].'.25';
    }
    if ($partial > 0.27 && $partial <= 0.52) {
        $hours = $total_hours[0].'.5';
    }
    if ($partial > 0.52 && $partial < 0.77) {
        $hours = $total_hours[0].'.75';
    }
    if ($partial > 0.77 && $partial < 1) {
        $hours = $total_hours[0]+ 1;
    }
}
return $hours;
}

function convert_into_fraction($time)
{
    return date('H', $time) + (date('i', $time) / 60);
}

}
