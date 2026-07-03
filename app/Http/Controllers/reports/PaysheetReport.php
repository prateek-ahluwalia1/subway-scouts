<?php

namespace App\Http\Controllers\reports;

use App\Http\Controllers\Controller;
use App\Models\Guard;
use App\Models\GuardWorkDetail;
use App\Models\Payrate;
use App\Models\Site;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\PayrollPaysheetReportExport;
use App\Exports\PaysheetReportExport;
use App\Exports\OldPaysheetReportExport;
use DateTime;
use App\Exports\QuickPaysheetReportExport;
use Carbon\Carbon;
use DB;
class PaysheetReport extends Controller
{
    function generatePaysheetReport(Request $request)
    {
        if($request->type == 'preview'){
            // $data = $this->getCompleteReportData($request);
            $data = $this->getReportData($request);
            return response()->json([
                'success' => true,
                'data' => $data
            ]);
        }
        $filename = time().'_paysheet_report.xlsx';  
        Excel::store(new PaysheetReportExport, 'excel/paysheet/'.$filename, 'excels');
        return response()->json(['success' =>  true, 'message' => 'Paysheet Report generate successfully.','path' => 'https://'.request()->getHttpHost().'/excel/paysheet/'.$filename]);
    }

     function generateOldPaysheetReport(Request $request)
    {
        if($request->type == 'preview'){
            $data = $this->getCompleteReportData($request);
            // $data = $this->getReportData($request);
            return response()->json([
                'success' => true,
                'data' => $data
            ]);
        }
        $filename = time().'_paysheet_report.xlsx';  
        Excel::store(new OldPaysheetReportExport, 'excel/paysheet/'.$filename, 'excels');
        return response()->json(['success' =>  true, 'message' => 'Paysheet Report generate successfully.','path' => 'https://'.request()->getHttpHost().'/excel/paysheet/'.$filename]);
    }

    function generatePayrollPaysheetReport(Request $request)
    {
        if($request->type == 'preview'){
            // $data = $this->getCompleteReportData($request);
            $data = $this->getReportData($request);
            return response()->json([
                'success' => true,
                'data' => $data
            ]);
        }
        $filename = time().'_payroll_paysheet_report.xlsx';  
        Excel::store(new PayrollPaysheetReportExport, 'excel/payroll/'.$filename, 'excels');
        return response()->json(['success' =>  true, 'message' => 'Payroll Paysheet Report generate successfully.','path' => 'https://'.request()->getHttpHost().'/excel/payroll/'.$filename]);
    }

    function getCompleteReportData($request)
    {
        if (isset($request['date']) && $request['date'] != '') {
            $date = $request['date'];
            $date = explode('-', $date);
            $fromTimestamp = DateTime::createFromFormat('d/m/Y', trim($date[0]));
            $toTimestamp = DateTime::createFromFormat('d/m/Y', trim($date[1]));
            $from = $fromTimestamp->getTimestamp();
            $to = $toTimestamp->getTimestamp();
            // $from = strtotime(trim($date[0]));
            // $to = strtotime(trim($date[1]));
            // $to = $to;
        }else{
            $to = time();
            $from = time() - (60*60*24*7);
        }
        //dd($this->getReportsData(date('Y-m-d', $from),date('Y-m-d 23:59:59', $to)));
        return $this->getReportsData(date('Y-m-d', $from), date('Y-m-d 23:59:59', $to), $request['customer_id']);
    }

    public function getReportsData($start, $end, $customerIDs, $jobStatus = 'completed', $specific_sites_id = null, $multiple_states = null, $customer_id = null) {
        $extra_query = '(jr.`job_status` = "completed" OR jr.`job_status` = "pending" OR jr.`job_status` = "confirmed")';
      
        if (!empty($customerIDs) && is_array($customerIDs)) { // Ensure $customerIDs is an array
            $extra_query .= " AND (";
            foreach ($customerIDs as $key => $id) {
                $extra_query .= "j.`customer_id` = '" . $id . "'";
                if ($key < count($customerIDs) - 1) {
                    $extra_query .= " OR ";
                }
            }
            $extra_query .= ")";
        }

      $now = strtotime($end);
      $your_date = strtotime($start);
      $datediff = $now - $your_date;
      $datediff = round($datediff / (60 * 60 * 24));
      $counter = $datediff / 7;
      $data = [];
      for ($j=1; $j <= $counter; $j++) { 
          
          $state = 'vic';
        //   $startDate = $start;
          $startDate = date('Y-m-d 00:00', strtotime($start));
          $endDate = date('Y-m-d 23:59', strtotime("+6 day", strtotime($start)));


          // $endDate = strtotime($start) + - 60;
          // $endDate = strtotime($start) + 60*60*24*7;
        //   $endDate = strtotime("+7 day", strtotime($startDate))-1;
          // $endDate = strtotime($end);
        //   $endDate = date('Y-m-d', $endDate);
          // if ($j > 0) {
          //     echo $counter;
          //     echo $endDate;
          //     exit();
          // }
          $sql = "SELECT
        jr.*,
        j.`id`,
        j.`booking_id`,
        j.`customer_id`,
        j.`contractor_id`,
        j.`state`,
        j.`type`,
        j.`address`,
        j.`site_name`,
        j.`site_description`,
        j.`level`,
        j.`payrol`,
        j.`site_payrate`,
        j.`break_payable`,
        j.`break`,
        cust.`name` AS customer_name,
        c.`name` AS contractor_name,
        g.`guard_type`,
        g.`position`,
        g.`phone` AS guard_phone,
        g.`id` AS guard_id,
        g.`email` AS guard_email,
        g.`address` AS guard_address,
        g.`profile_image` AS guard_image,
        CONCAT(g.first_name, ' ', g.last_name) AS guard_name,
        g.`payrates_id` AS guard_payrate_id,
        g.`suburb` AS guard_suburb,
        g.`city` AS guard_city,
        g.`state` AS guard_state,
        g.`coordinates` AS guard_coordinates,
        g.`postal_code` AS guard_postal_code,
        g.`dob` AS guard_dob,
        g.`gender` AS guard_gender,
        g.`emergency_contact_name` AS emergency_contact_name,
        g.`emergency_contact_phone` AS emergency_contact_phone,
        g.`registration_type` AS registration_type,
        g.`residential_status` AS residential_status,
        gw.`bsb` AS bsb,
        gw.`bank_name` AS payroll_bank_name,
        g.`guard_status`,
        gw.`bank_account_no` AS payroll_bank_account_number,
        g.`covid_19`,
        gw.`tfn_file_no` AS payroll_tfn_number,
        gw.`abn_no` AS payroll_abn_number,
        gw.`superannutation_no` AS payroll_superannutation,
        -- gi.`external_id`,
        -- cust.`flat_metro_week_day`,
        -- gpi.`payroll_id`,
        jr.`shift_payable` AS jr_payable,
        jr.`id` AS roster_id
    FROM job_rosters AS jr
    INNER JOIN sites AS j ON j.`id` = jr.`site_id`
    LEFT JOIN `guards` AS g ON g.`id` = jr.`guard_id`
    LEFT JOIN customers AS cust ON j.`customer_id` = cust.`id`
    LEFT JOIN `contractors` AS c ON j.`contractor_id`= c.`id`
    LEFT JOIN `guard_work_details` AS gw ON jr.`guard_id` = gw.`guard_id`
    -- LEFT JOIN `guard_external_ids` AS gi ON gi.`customer_id` = cust.`id` AND gi.`guard_id` = jr.`guard_id`
    -- LEFT JOIN `guard_payroll_ids` AS gpi ON gpi.`guard_id` = g.`id` AND gpi.`guard_id` = jr.`guard_id`
    WHERE $extra_query
        AND jr.`shift_payable` = 'yes'
        AND jr.`deleted_at` IS NULL
        AND jr.`start` BETWEEN '$startDate' AND '$endDate'
        --   AND (
        --     (jr.`start` BETWEEN '$startDate' AND '$endDate')
        --     OR
        --     (jr.`end` BETWEEN '$startDate' AND '$endDate')
        -- )
    GROUP BY jr.id
    ORDER BY g.name ASC, jr.unprofile_name";
      
          $query = DB::select($sql);
      
          $results = $query;
// dd($results);
          foreach ($results as $index => $result) {
          // calculate total hours in 7 days
          $result->external_id = null;
              $amgID = DB::table('guard_external_ids')->where('guard_id', $result->guard_id)->where('external_id', 'like', '%AMG%')->first();
              if (!empty($amgID)) {
                  $result->external_id = $amgID->external_id;
              }
              $sql = "SELECT
              SUM(jr.hours) AS shift_hour
            FROM job_rosters AS jr
            INNER JOIN sites AS j ON j.`id` = jr.`site_id`
            INNER JOIN `guards` AS g ON g.`id` = jr.`guard_id`
            INNER JOIN customers AS cust ON j.`customer_id` = cust.`id`
            WHERE " . $extra_query . " 
            AND g.`id` = '" . $result->guard_id . "' 
            AND jr.`shift_payable` = 'yes' 
            AND jr.`start` BETWEEN '" . $startDate . "' AND '" . $endDate . "' 
            ORDER BY g.name ASC, jr.unprofile_name, jr.start";

              $query1 = DB::select($sql);
          // check if weekend shifts are there
              $weekendStart = date("Y-m-d 00:00", strtotime('saturday this week', strtotime($startDate)));
              $weekendEnd = date("Y-m-d 23:59", strtotime('saturday this week', strtotime($startDate)));
              $is_public_holiday_in_saturday = DB::table('public_holidays')->where('date', date('Ymd',  strtotime('saturday this week', strtotime($startDate))))->where('state', $state)->first();
              // print_r($is_public_holiday_in_saturday);
              // exit();
          // Find if any Shift start in saturday
          $sql = "SELECT
          SUM(jr.hours) AS weekend_hours
          FROM job_rosters AS jr
          INNER JOIN sites AS j ON j.`id` = jr.`site_id`
          INNER JOIN `guards` AS g ON g.`id` = jr.`guard_id`
          INNER JOIN customers AS cust ON j.`customer_id` = cust.`id`
          WHERE " . $extra_query . " 
          AND g.`id` = '" . $result->guard_id . "' 
          AND jr.`start` BETWEEN '" . $weekendStart . "' AND '" . $weekendEnd . "' 
          ORDER BY g.name ASC, jr.unprofile_name, jr.start";

              $query2 = DB::select($sql);
          // Find if any shift only end in saturday
              $weekendStart = date("Y-m-d 00:00:00", strtotime('saturday this week', strtotime($startDate)));
              $weekendEnd = date("Y-m-d 23:59:59", strtotime('saturday this week', strtotime($startDate)));
              $sql = "SELECT
              SUM(jr.hours) AS weekend_hours
              FROM job_rosters AS jr
              INNER JOIN sites AS j ON j.`id` = jr.`site_id`
              INNER JOIN `guards` AS g ON g.`id` = jr.`guard_id`
              INNER JOIN customers AS cust ON j.`customer_id` = cust.`id`
              WHERE " . $extra_query . " 
              AND g.`id` = '" . $result->guard_id . "' 
              AND jr.`end` BETWEEN '" . $weekendStart . "' AND '" . $weekendEnd . "' 
             ORDER BY g.name ASC, jr.unprofile_name, jr.start";
             $query4 = DB::select($sql);

             // Find any hours in sunday
              $sundayStart = date("Y-m-d", strtotime('sunday this week', strtotime($startDate)));
              $sundayEnd = date("Y-m-d", strtotime('sunday this week', strtotime($startDate)));
              $is_public_holiday_in_sunday = DB::table('public_holidays')->where('date', date('Ymd', strtotime('sunday this week', strtotime($startDate))))->where('state', $state)->first();
      
              $sql = "SELECT
              SUM(jr.hours) AS sunday_hours
              FROM job_rosters AS jr
              INNER JOIN sites AS j ON j.`id` = jr.`site_id`
              INNER JOIN `guards` AS g ON g.`id` = jr.`guard_id`
              INNER JOIN customers AS cust ON j.`customer_id` = cust.`id`
              WHERE " . $extra_query . " 
              AND g.`id` = '" . $result->guard_id . "' 
              AND jr.`start` BETWEEN '" . $sundayStart . "' AND '" . $sundayEnd . "' 
              ORDER BY g.name ASC, jr.unprofile_name, jr.start";

              $query3 = DB::select($sql);
      
      
           // Find any hours in monday and shift start in sunday
              // $sundayStart = date("Y-m-d 00:00:00", strtotime('sunday next week', strtotime($startDate)));
              $sundayEnd = date("Y-m-d 23:59:59", strtotime('sunday this week', strtotime($startDate)));
      
         // echo $sundayStart;
         // exit();
        
         $sql = "SELECT 
         jr.*, 
         jr.start AS shift_start, 
         jr.end AS shift_end, 
         jr.site_id AS shift_site, 
         j.*, 
         g.*, 
         cust.*
          FROM job_rosters AS jr
          INNER JOIN sites AS j ON j.`id` = jr.`site_id`
          INNER JOIN `guards` AS g ON g.`id` = jr.`guard_id`
          INNER JOIN customers AS cust ON j.`customer_id` = cust.`id`
          WHERE " . $extra_query . " 
          AND g.`id` = '" . $result->guard_id . "' 
          AND jr.`shift_payable` = 'yes' 
          AND jr.`start` < '" . $sundayEnd . "' 
          AND jr.`end` > '" . $sundayEnd . "' 
          ORDER BY g.name ASC, jr.unprofile_name, jr.start";

          $query5 = DB::select($sql);
      
          // Find any hours in shift start on saturday and end on sunday
              $sundayStart = date("Y-m-d 00:00:00", strtotime('sunday this week', strtotime($startDate)));
                
                        $sql = "SELECT 
                        jr.*, 
                        jr.start AS shift_start, 
                        jr.end AS shift_end,
                        jr.site_id AS shift_site, 
                        j.*, 
                        g.*, 
                        cust.*
                    FROM job_rosters AS jr
                    INNER JOIN sites AS j ON j.`id` = jr.`site_id`
                    INNER JOIN `guards` AS g ON g.`id` = jr.`guard_id`
                    INNER JOIN customers AS cust ON j.`customer_id` = cust.`id`
                    WHERE " . $extra_query . " 
                        AND g.`id` = '" . $result->guard_id . "' 
                        AND jr.`shift_payable` = 'yes' 
                        AND jr.`start` < '" . $sundayStart . "' 
                        AND jr.`end` > '" . $sundayStart . "' 
                        AND jr.`start` != '" . date("Y-m-d", strtotime('sunday this week', strtotime($startDate))) . "' 
                    ORDER BY g.name ASC, jr.unprofile_name, jr.start";
                
            $query6 = DB::select($sql);
          $hour_plus = 0;
          if (!empty($query5) && empty($is_public_holiday_in_sunday)) {
      
              foreach ($query5 as $q5) {
                  $job_hours = $this->getShiftHours($sundayEnd,$q5->shift_end, $q5->shift_site);
                  $job_hours['morning'] = $this->convertIntoWhole($job_hours['morning']);
                  $job_hours['night'] = $this->convertIntoWhole($job_hours['night']);
                  $hour_plus += ($job_hours['morning'] +$job_hours['night']);
      
              }
              // echo $hour_plus;
              // exit();
          }
      
          if (!empty($query6) && empty($is_public_holiday_in_sunday)) {
      
              foreach ($query6 as $q6) {
                  $job_hours = $this->getShiftHours($sundayStart,$q6->shift_end, $q6->shift_site);
                  $job_hours['sunday_morning'] = $this->convertIntoWhole($job_hours['sunday_morning']);
                  $job_hours['sunday_night'] = $this->convertIntoWhole($job_hours['sunday_night']);
                  $query1[0]->shift_hour = $query1[0]->shift_hour - ($job_hours['sunday_morning'] +$job_hours['sunday_night']);
              }
              // echo $hour_plus;
              // exit();
          }
      
      
      
                  // $pay_rate = $result->flat_metro_week_day;
          $tempStart = date("H:i", strtotime($result->start));
          $tempEnd = date("H:i", strtotime($result->end));
                  // $total = $this->getTimeDiff($result->temp_start, $result->temp_end);
          $total = $result->hours;
          if ($result->continuation == 0 && $total < 4) {
              $end = strtotime($result->end);
              $remaining = 4 - $total;
              $end = $end + (60*60*$remaining);
              $job_hours = $this->getShiftHours($result->start,date('Y-m-d H:i', $end), $result->site_id);
          }else{
              $job_hours = $this->getShiftHours($result->start,$result->end, $result->site_id);
          }
      // print_r($job_hours);
          // exit;
      
          $job_hours['morning'] = $this->convertIntoWhole($job_hours['morning']);
          $job_hours['night'] = $this->convertIntoWhole($job_hours['night']);
          $job_hours['saturday_morning'] = $this->convertIntoWhole($job_hours['saturday_morning']);
          $job_hours['saturday_night'] = $this->convertIntoWhole($job_hours['saturday_night']);
          $job_hours['sunday_morning'] = $this->convertIntoWhole($job_hours['sunday_morning']);
          $job_hours['sunday_night'] = $this->convertIntoWhole($job_hours['sunday_night']);
          $job_hours['ph_morning'] = $this->convertIntoWhole($job_hours['ph_morning']);
          $job_hours['ph_night'] = $this->convertIntoWhole($job_hours['ph_night']);
          // if($result->ph_duration == 1)
          // {
          //     $job_hours['ph_morning'] =  $job_hours['ph_morning'] + $job_hours['morning'] + $job_hours['saturday_morning'] + $job_hours['sunday_morning'];
          //     $job_hours['ph_night'] = $job_hours['ph_night'] + $job_hours['night'] + $job_hours['saturday_night'] + $job_hours['saturday_night'];
          // }
      
          $pay_rate = 0;
          $day_rate = 0;
          $saturday_day_rate = 0;
          $saturday_night_rate = 0;
          $sunday_day_rate = 0;
          $sunday_night_rate = 0;
          $ph_day_rate = 0;
          $ph_night_rate = 0;
          $night_rate = 0;
          $ot_base_rate = 0;

          $new_pay_rates = 0;
          $day_rate_new = 0;
          $saturday_rate_new = 0;
          $saturday_rate_new = 0;
          $sunday_rate_new = 0;
          $sunday_rate_new = 0;
          $public_holiday_rate_new = 0;
          $night_rate_new = 0;

          // award rates
          $award_pay_rate = 0;
          $award_day_rate = 0;
          $award_saturday_day_rate = 0;
          $award_saturday_night_rate = 0;
          $award_sunday_day_rate = 0;
          $award_sunday_night_rate = 0;
          $award_ph_day_rate = 0;
          $award_ph_night_rate = 0;
          $award_night_rate = 0;
          $award_ot_base_rate = 0;
          // award rates
          if ($query1[0]->shift_hour > 0) {
              if ($query3[0]->sunday_hours > 0  && empty($is_public_holiday_in_sunday)) {
                  $query1[0]->shift_hour = $query1[0]->shift_hour - $query3[0]->sunday_hours;
              }
          }
          if ($hour_plus > 0) {
              $query1[0]->shift_hour = $query1[0]->shift_hour + $hour_plus;
          }
      
          $new_pay_rates = DB::table('payrates')->where('id', $result->site_payrate)->first();

           $payrate_name_new = '';
           if (!empty($new_pay_rates)) {
            $payrate_name_new = $new_pay_rates->title;

            if ($result->type == 'metro') {
                if ($result->payrol == 'award') {
                    $day_rate_new = $new_pay_rates->award_metro_mon_to_fri_day_rate;
                    $night_rate_new = $new_pay_rates->award_metro_mon_to_fri_night_rate;
                    $public_holiday_rate_new = $new_pay_rates->award_metro_pub_holi_day_rate;
                    $saturday_rate_new = $new_pay_rates->award_metro_sat_day_rate;
                    $sunday_rate_new = $new_pay_rates->award_metro_sun_day_rate;
                } elseif ($result->payrol == 'eba') {
                    $day_rate_new = $new_pay_rates->eba_metro_mon_to_fri_day_rate;
                    $night_rate_new = $new_pay_rates->eba_metro_mon_to_fri_night_rate;
                    $public_holiday_rate_new = $new_pay_rates->eba_metro_pub_holi_day_rate;
                    $saturday_rate_new = $new_pay_rates->eba_metro_sat_day_rate;
                    $sunday_rate_new = $new_pay_rates->eba_metro_sun_day_rate;
                } else {
                    $day_rate_new = $new_pay_rates->def_metro_mon_to_fri_day_rate;
                    $night_rate_new = $new_pay_rates->def_metro_mon_to_fri_night_rate;
                    $public_holiday_rate_new = $new_pay_rates->def_metro_pub_holi_day_rate;
                    $saturday_rate_new = $new_pay_rates->def_metro_sat_day_rate;
                    $sunday_rate_new = $new_pay_rates->def_metro_sun_day_rate;
                }
            } else {
                if ($result->payrol == 'award') {
                    $day_rate_new = $new_pay_rates->award_reg_mon_to_fri_day_rate;
                    $night_rate_new = $new_pay_rates->award_reg_mon_to_fri_night_rate;
                    $public_holiday_rate_new = $new_pay_rates->award_reg_pub_holi_day_rate;
                    $saturday_rate_new = $new_pay_rates->award_reg_sat_day_rate;
                    $sunday_rate_new = $new_pay_rates->award_reg_sun_day_rate;
                } elseif ($result->payrol == 'eba') {
                    $day_rate_new = $new_pay_rates->eba_reg_mon_to_fri_day_rate;
                    $night_rate_new = $new_pay_rates->eba_reg_mon_to_fri_night_rate;
                    $public_holiday_rate_new = $new_pay_rates->eba_reg_pub_holi_day_rate;
                    $saturday_rate_new = $new_pay_rates->eba_reg_sat_day_rate;
                    $sunday_rate_new = $new_pay_rates->eba_reg_sun_day_rate;
                } else {
                    $day_rate_new = $new_pay_rates->def_reg_mon_to_fri_day_rate;
                    $night_rate_new = $new_pay_rates->def_reg_mon_to_fri_night_rate;
                    $public_holiday_rate_new = $new_pay_rates->def_reg_pub_holi_day_rate;
                    $saturday_rate_new = $new_pay_rates->def_reg_sat_day_rate;
                    $sunday_rate_new = $new_pay_rates->def_reg_sun_day_rate;
                }
            }
        }

         if ($job_hours['morning'] > 0) {
          $new_pay_rate = $day_rate_new;
        }elseif ($job_hours['night'] > 0) {
            $new_pay_rate = $night_rate_new;
        }elseif ($job_hours['saturday_morning'] + $job_hours['saturday_night'] > 0) {
            $new_pay_rate = $saturday_rate_new;
        }elseif ($job_hours['sunday_morning'] + $job_hours['sunday_night'] > 0) {
            $new_pay_rate = $sunday_rate_new;
        }elseif ($job_hours['ph_morning'] + $job_hours['ph_night'] > 0) {
            $new_pay_rate = $public_holiday_rate_new;
        }else{
            $new_pay_rate = $day_rate_new;
        }

          $pay_rates = DB::table('payrates_new');
          $pay_rates->where('level', trim($result->level));
          if ($query2[0]->weekend_hours > 0 && empty($is_public_holiday_in_saturday)) {
              $weekend = 1;
              $pay_rates->where('weekend', 1);
          }elseif ($query4[0]->weekend_hours > 0 && empty($is_public_holiday_in_saturday)) {
              $pay_rates->where('weekend', 1);
              $weekend = 1;
      
          }else{
              $pay_rates->where('weekend', 0);
              $weekend = 0;
      
              // if ($query1[0]->shift_hour < 36) {
              //     // $pay_rates->where('hours', '>=', $query1[0]->shift_hour);
              //     $pay_rates->where('hours', 36);
              // }else
          }
          if ($query1[0]->shift_hour <= 36) {
              $hours = (int) $query1[0]->shift_hour * 1;
                  $pay_rates->where('hours', '>=', $hours);
                  // $pay_rates->where('hours', 36);
              }elseif ($query1[0]->shift_hour > 36 && $query1[0]->shift_hour <= 38) {
                  $pay_rates->where('hours', 38);
              }
              elseif ($query1[0]->shift_hour > 38 && $query1[0]->shift_hour <= 40) {
                  $pay_rates->where('hours', 40);
              }elseif ($query1[0]->shift_hour > 40) {
                  $pay_rates->where('hours', 48);
              }else{
                  $pay_rates->where('hours', 38);
              }
          $pay_rates->orderBy('hours', 'ASC');
          // $pay_rates_query = $pay_rates->toSql();
          $guard_rates = $payrates = $pay_rates->first();
          // $pay_rates_query = $pay_rates->toSql(). '-'.$query1[0]->shift_hour.'-'.$result->level.'-'.$weekend.'-'.gettype($a);
      
      
      
          $award_payrates = DB::table('award_payrates');
          $award_payrates->where('level', $result->level);
          if ($query2[0]->weekend_hours > 0 && empty($is_public_holiday_in_saturday)) {
              $award_payrates->where('weekend', 1);
          }elseif ($query4[0]->weekend_hours > 0 && empty($is_public_holiday_in_saturday)) {
              $award_payrates->where('weekend', 1);
          }else{
              $award_payrates->where('weekend', 0);
              // if ($query1[0]->shift_hour < 36) {
              //     $award_payrates->where('hours', 36);
              //     // $award_payrates->where('hours', '>=', $query1[0]->shift_hour);
              // }else
              if ($query1[0]->shift_hour <= 38) {
                  $award_payrates->where('hours', 38);
              }elseif ($query1[0]->shift_hour > 38 && $query1[0]->shift_hour <= 40) {
                  $award_payrates->where('hours', 40);
              }elseif ($query1[0]->shift_hour > 40) {
                  $award_payrates->where('hours', 48);
              }else{
                  $award_payrates->where('hours', 38);
              }
          }
          $award_payrates = $award_payrates->first();
      
      
          $day = Carbon::parse($result->start)->format('l');
          $payrate_name = '';
      
          if (!empty($payrates)) {
              $payrate_name = $payrates->title;
              if ($result->position == 'casual') {
                  $pay_rate = $payrates->casual_day;
                  $day_rate = $payrates->casual_day;
                  $night_rate = $payrates->casual_night;
                  $saturday_day_rate = $payrates->casual_sat;
                  $saturday_night_rate = $payrates->casual_sat;
                  $sunday_day_rate = $payrates->casual_sun;
                  $sunday_night_rate = $payrates->casual_sun;
                  $ph_day_rate = $payrates->casual_ph;
                  $ph_night_rate = $payrates->casual_ph;
              }else{ 
                  $pay_rate = $payrates->pf_day;
                  $day_rate = $payrates->pf_day;
                  $night_rate = $payrates->pf_night;
                  $saturday_day_rate = $payrates->pf_sat;
                  $saturday_night_rate = $payrates->pf_sat;
                  $sunday_day_rate = $payrates->pf_sun;
                  $sunday_night_rate = $payrates->pf_sun;
                  $ph_day_rate = $payrates->pf_ph;
                  $ph_night_rate = $payrates->pf_ph;
              }
          }
          $pay_rate = $result->over_time_value * $pay_rate;
          $day_rate = $result->over_time_value * $day_rate;
          $night_rate = $result->over_time_value * $night_rate;
          $sunday_day_rate = $result->over_time_value * $sunday_day_rate;
          $sunday_night_rate = $result->over_time_value * $sunday_night_rate;
          $saturday_day_rate = $result->over_time_value * $saturday_day_rate;
          $saturday_night_rate = $result->over_time_value * $saturday_night_rate;
          $ph_day_rate = $result->over_time_value * $ph_day_rate;
          $ph_night_rate = $result->over_time_value * $ph_night_rate;
      
      
      
          // award rates
      if (!empty($award_payrates)) {
              // $payrate_name = $award_payrates->title;
              if ($result->position == 'casual') {
                  $award_pay_rate = $award_payrates->casual_day;
                  $award_day_rate = $award_payrates->casual_day;
                  $award_night_rate = $award_payrates->casual_night;
                  $award_saturday_day_rate = $award_payrates->casual_sat;
                  $award_saturday_night_rate = $award_payrates->casual_sat;
                  $award_sunday_day_rate = $award_payrates->casual_sun;
                  $award_sunday_night_rate = $award_payrates->casual_sun;
                  $award_ph_day_rate = $award_payrates->casual_ph;
                  $award_ph_night_rate = $award_payrates->casual_ph;
              }else{ 
                  $award_award_pay_rate = $award_payrates->pf_day;
                  $award_day_rate = $award_payrates->pf_day;
                  $award_night_rate = $award_payrates->pf_night;
                  $award_saturday_day_rate = $award_payrates->pf_sat;
                  $award_saturday_night_rate = $award_payrates->pf_sat;
                  $award_sunday_day_rate = $award_payrates->pf_sun;
                  $award_sunday_night_rate = $award_payrates->pf_sun;
                  $award_ph_day_rate = $award_payrates->pf_ph;
                  $award_ph_night_rate = $award_payrates->pf_ph;
              }
          }
          $award_pay_rate = $result->over_time_value * $award_pay_rate;
          $award_day_rate = $result->over_time_value * $award_day_rate;
          $award_night_rate = $result->over_time_value * $award_night_rate;
          $award_sunday_day_rate = $result->over_time_value * $award_sunday_day_rate;
          $award_sunday_night_rate = $result->over_time_value * $award_sunday_night_rate;
          $award_saturday_day_rate = $result->over_time_value * $award_saturday_day_rate;
          $award_saturday_night_rate = $result->over_time_value * $award_saturday_night_rate;
          $award_ph_day_rate = $result->over_time_value * $award_ph_day_rate;
          $award_ph_night_rate = $result->over_time_value * $award_ph_night_rate;
          // award rates
          $activity = DB::table('job_roster_activites')->where('guard_id', $result->guard_id)->where('job_roster_id', $result->roster_id)->first();
                  // $activity = $break->result_array();
      
          if(empty($activity)){
            $activity = array();
        }
        $break = DB::table('job_breaks')->where('roster_id', $result->roster_id)->where('guard_id', $result->guard_id)->get();
        $break_time = $break;
        if(empty($break_time)){
            $break_time = array();
        }
      
        $number_of_breaks= count($break);
        if ($result->travel_time == 0) {
          $result->travel_time = 0;
      }
                  // $query_break->row_array();
                  // if(!empty($query_break)){
                  //   // $pay_rate = $customer_rates['eba_metro_weekday_day'];
                  // }
      
      if ($result->break_payable == 'no' && $result->break == 1) {
        if (isset($result->payable_and_chargeable_time)) {
                      // $total = $total - $result->payable_and_chargeable_time/60;
          $payable_and_chargeable_time = $result->payable_and_chargeable_time/60;
                      // if ($job_hours['morning'] > $payable_and_chargeable_time) {
                      //     $job_hours['morning'] = $job_hours['morning'] - $payable_and_chargeable_time;
                      // }else{
                      //     $job_hours['night'] = $job_hours['night'] - $payable_and_chargeable_time;
                      // }
          if ($payable_and_chargeable_time > 0 && $payable_and_chargeable_time <= 0.25) {
            $breakCal = $this->calculateBreakTiming($job_hours['morning'], $job_hours['night'], 0.25, 0);
            $job_hours['morning'] = $breakCal[0];
            $job_hours['night'] = $breakCal[1];
        }elseif ($payable_and_chargeable_time > 0.25 && $payable_and_chargeable_time <= 0.5) {
         $breakCal = $this->calculateBreakTiming($job_hours['morning'], $job_hours['night'], 0.25, 0.25);
         $job_hours['morning'] = $breakCal[0];
         $job_hours['night'] = $breakCal[1];
      }elseif ($payable_and_chargeable_time > 0.5 && $payable_and_chargeable_time <= 0.75) {
         $breakCal = $this->calculateBreakTiming($job_hours['morning'], $job_hours['night'], 0.5, 0.25);
         $job_hours['morning'] = $breakCal[0];
         $job_hours['night'] = $breakCal[1];
      }elseif ($payable_and_chargeable_time > 0.75 && $payable_and_chargeable_time <= 1) {
         $breakCal = $this->calculateBreakTiming($job_hours['morning'], $job_hours['night'], 0.5, 0.5);
         $job_hours['morning'] = $breakCal[0];
         $job_hours['night'] = $breakCal[1];
      }
      }else{
            $result->payable_and_chargeable_time = 0;
                      $result->break_payable = '-';
      }
      }else{
          $result->payable_and_chargeable_time = 0;
          $result->break_payable = '-';
      }
      $result->payable = $result->break_payable;
      if ($result->travel_time_value == '' || $result->travel_time_value ==  null) {
          $result->travel_time_amount = 0;
      }else{
          $result->travel_time_amount = $result->travel_time;
      }
      if ($job_hours['morning'] > 0) {
          $pay_rate = $day_rate;
      }elseif ($job_hours['night'] > 0) {
          $pay_rate = $night_rate;
      }elseif ($job_hours['saturday_morning'] + $job_hours['saturday_night'] > 0) {
          $pay_rate = $saturday_day_rate;
      }elseif ($job_hours['sunday_morning'] + $job_hours['sunday_night'] > 0) {
          $pay_rate = $sunday_day_rate;
      }elseif ($job_hours['ph_morning'] + $job_hours['ph_night'] > 0) {
          $pay_rate = $ph_day_rate;
      }else{
          $pay_rate = $day_rate;
      }
      
      $item = [
          'roster_id' => $result->roster_id,
        //   'event_id' => $result->event_id,
          'guard_id' => $result->guard_id,
          'site_id' => $result->site_id,
          'start' => $result->start,
          'end' => $result->end,
          'temp_date' => date('d/m/Y', strtotime($result->start)),
          'temp_date_end' => date('d/m/Y', strtotime($result->end)),
          'temp_start' =>  $tempStart,
          'temp_end' => $tempEnd,
          'total_hours' => $total,
          'publish_status' => $result->publish_status,
        //   'add_status' => $result->add_status,
          'job_status' => $result->job_status,
          'job_id' => $result->id,
          'booking_id' => $result->booking_id,
          'customer_id' => $result->customer_id,
          'contractor_id' => $result->contractor_id,
          'state' => $result->state,
          'level' => $result->level,
          'stateType' => $result->type,
          'address' => $result->address,
          'site_name' => $result->site_name,
          'site_description' => $result->site_description,
          'customer_name' => $result->customer_name,
          'contractor_name' => $result->contractor_name,
          'guard_type' => $result->guard_type,
          'guard_name' => $result->guard_name,
          'guard_image' => $result->guard_image,
          'guard_id' => $result->guard_id,
          'guard_email' => $result->guard_email,
          'guard_address' => $result->guard_address,
          'guard_phone' => $result->guard_phone,
      
          'guard_suburb' => $result->guard_suburb,
          'guard_city' => $result->guard_city,
          'guard_state' => $result->guard_state,
          'guard_coordinates' => $result->guard_coordinates,
          'guard_postal_code' => $result->guard_postal_code,
          'guard_dob' => $result->guard_dob,
          'guard_gender' => $result->guard_gender,
          'emergency_contact_name' => $result->emergency_contact_name,
      
          'emergency_contact_phone' => $result->emergency_contact_phone,
          'registration_type' => $result->registration_type,
          'residential_status' => $result->residential_status,
        //   'passport_number' => $result->passport_number,
        //   'passport_expiration' => $result->passport_expiration,
        //   'visa_number' => $result->visa_number,
        //   'visa_expiration' => $result->visa_expiration,
        //   'security_license_number' => $result->security_license_number,
      
        //   'security_license_expiration' => $result->security_license_expiration,
        //   'driver_license_number' => $result->driver_license_number,
        //   'driver_license_expiration' => $result->driver_license_expiration,
        //   'is_approved' => $result->is_approved,
          'bsb' => $result->bsb,
          'payroll_bank_name' => $result->payroll_bank_name,
          'payroll_bank_account_number' => $result->payroll_bank_account_number,
          'covid' => $result->covid_19,
      
        //   'fortnightly_working_hours' => $result->fortnightly_working_hours,
          'payroll_tfn_number' => $result->payroll_tfn_number,
          'payroll_abn_number' => $result->payroll_abn_number,
          'payroll_superannutation' => $result->payroll_superannutation,
          'payroll_bank_name' => $result->payroll_bank_name,
          'payroll_bank_account_number' => $result->payroll_bank_account_number,
                      // 'guard_rates' => $guard_rates,
          'break_time' => $break_time,
          'number_of_breaks' => $number_of_breaks,
          'activity'=> $activity,
          'job_hours' => $job_hours,
        //   'phone' => $result->phone,
        //   'description' => strip_tags($result->details),
          'level' => 'Level '.$result->level,
          'day_hours' => $job_hours['morning'] + $result->travel_time,
          'night_hours' => $job_hours['night'],
         'external_id' => $result->external_id,
        //  'payroll_id' => $result->payroll_id,
        //   'internal_id' => $result->internal_id,
          'total_amount' => ((($job_hours['morning'] + $result->travel_time) * $day_rate) + ($job_hours['night'] * $night_rate) + ($job_hours['sunday_morning'] * $sunday_day_rate) + ($job_hours['sunday_night'] * $sunday_night_rate) + ($job_hours['saturday_morning'] * $saturday_day_rate) + ($job_hours['saturday_night'] * $saturday_night_rate) + ($job_hours['ph_morning'] * $ph_day_rate) + ($job_hours['ph_night'] * $ph_night_rate) + $result->travel_time_amount),
          'award_total_amount' => ((($job_hours['morning'] + $result->travel_time) * $award_day_rate) + ($job_hours['night'] * $award_night_rate) + ($job_hours['sunday_morning'] * $award_sunday_day_rate) + ($job_hours['sunday_night'] * $award_sunday_night_rate) + ($job_hours['saturday_morning'] * $award_saturday_day_rate) + ($job_hours['saturday_night'] * $award_saturday_night_rate) + ($job_hours['ph_morning'] * $award_ph_day_rate) + ($job_hours['ph_night'] * $award_ph_night_rate) + $result->travel_time_amount),
          'pay_rate' => $pay_rate,
          'new_pay_rate' => $new_pay_rate,
          'day_rate' => $day_rate,
          'night_rate' => $night_rate,
          'day_rate_new' => $day_rate_new,
          'night_rate_new' => $night_rate_new,
          'award_rate' => [
              'award_day_rate' => $award_day_rate,
              'award_night_rate' => $award_night_rate,
              'award_sunday_day_rate' => $award_sunday_day_rate,
              'award_sunday_night_rate' => $award_sunday_night_rate,
              'award_saturday_day_rate' => $award_saturday_day_rate,
              'award_saturday_night_rate' => $award_saturday_night_rate,
              'award_ph_day_rate' => $award_ph_day_rate,
              'award_ph_night_rate' => $award_ph_night_rate,
          ],
          'hours' => $total,
        //   'operators_notes' => $result->operators_notes,
          'payable' => $result->shift_payable,
          'jr_payable' => $result->jr_payable,
          'payable_and_chargeable_time' => $result->payable_and_chargeable_time,
          'travel_time' => $result->travel_time,
          'travel_time_pay' => $day_rate * $result->travel_time + $result->travel_time_amount,
          'saturday_day_rate' => $saturday_day_rate,
          'saturday_night_rate' => $saturday_night_rate,
          'sunday_day_rate' => $sunday_day_rate,
          'sunday_night_rate' => $sunday_night_rate,
          'saturday_day_rate_new' => $saturday_rate_new,
          'saturday_night_rate_new' => $saturday_rate_new,
          'sunday_day_rate_new' => $sunday_rate_new,
          'sunday_night_rate_new' => $sunday_rate_new,
          'sunday_day_hours' => $job_hours['sunday_morning'],
          'sunday_night_hours' => $job_hours['sunday_night'],
          'saturday_day_hours' => $job_hours['saturday_morning'],
          'saturday_night_hours' => $job_hours['saturday_night'],
          'ph_day_hours' => $job_hours['ph_morning'],
          'ph_night_hours' => $job_hours['ph_night'],
          'ph_day_rate' => $ph_day_rate,
          'ph_night_rate' => $ph_night_rate,
          'ph_day_rate_new' => $public_holiday_rate_new,
          'ph_night_rate_new' => $public_holiday_rate_new,
          'ot' => $result->over_time_value,
          'ot_base_rate' => $ot_base_rate,
          'payrate_name' => $payrate_name,
          'payrate_name_new' => $payrate_name_new,
          'sum_hours' => $query1[0]->shift_hour,
          'position' => $result->position,
          'saturday_hours' => $query2[0]->weekend_hours,
          'sat_hours' => $query4[0]->weekend_hours,
          'startDate' => $startDate,
          'endDate' => $endDate,
          'sundayStart' => $sundayStart,
          'sundayEnd' => $sundayEnd,
          'applySundayRate' => false,
          // 'pay_rates_query' => $pay_rates_query
      ];
      
      if (isset($results[$index - 1])) {
          if ($results[$index-1]->guard_id == $results[$index]->guard_id) {
              $item['nowHours'] = $results[$index-1]->nowHours + $item['total_hours'];
              $results[$index]->nowHours = $item['nowHours'];
              if ($item['nowHours'] > 38) {
                  $item['applySundayRate'] = true;
                  if ($results[$index-1]->nowHours < 38) {
                      $item['moreHours'] = $item['nowHours'] - 38;
                      // $item['award_total_amount'] = (($item['moreHours'] + $result->travel_time) * $award_sunday_day_rate);
                  }else{
                      $item['moreHours'] = $item['nowHours']  - $results[$index-1]->nowHours;
                      $item['award_total_amount'] = ((($job_hours['morning'] + $result->travel_time) * $award_sunday_day_rate) + ($job_hours['night'] * $award_sunday_night_rate) + ($job_hours['sunday_morning'] * $award_sunday_day_rate) + ($job_hours['sunday_night'] * $award_sunday_night_rate) + ($job_hours['saturday_morning'] * $award_sunday_night_rate) + ($job_hours['saturday_night'] * $award_sunday_night_rate) + ($job_hours['ph_morning'] * $award_sunday_day_rate) + ($job_hours['ph_night'] * $award_sunday_night_rate) + $result->travel_time_amount);
                  }
              }
          }else{
              $item['nowHours'] = $item['total_hours'];
              $results[$index]->nowHours = $item['total_hours'];
          }
      }else{
          $results[$index]->nowHours = $item['total_hours'];
          $item['nowHours'] = $item['total_hours'];
      }
      if (($result->guard_id == 0 || $result->guard_id == null) && $result->unprofile_name != '') {
          $item['guard_name'] = $result->unprofile_name;
      }
                          // if($day == 'Sunday' || $day == 'sunday'){
                              // $item['sunday_day_hours'] =  $job_hours['morning'];
                              // $item['sunday_night_hours'] = $job_hours['night'];
                              // $item['sunday_day_rate'] = $day_rate;
                              // $item['sunday_night_rate'] = $night_rate;
                              // $item['saturday_day_hours'] = 0;
                              // $item['saturday_night_hours'] = 0;
                              // $item['day_hours'] = 0;
                              // $item['night_hours'] = 0;
                          // }elseif($day == 'Saturday' || $day == 'saturday'){
                              // $item['saturday_day_hours'] =  $job_hours['morning'];
                              // $item['saturday_night_hours'] = $job_hours['night'];
                              // $item['saturday_day_rate'] = $day_rate;
                              // $item['saturday_night_rate'] = $night_rate;
                              // $item['sunday_day_hours'] =  0;
                              // $item['sunday_night_hours'] = 0;
                              // $item['day_hours'] = 0;
                              // $item['night_hours'] = 0;
                          // }
      
      
      $user_activity = DB::table('job_roster_activites')->where('job_roster_id', $result->roster_id)->first();
      if (!empty($user_activity)) {
        $item['signin_notes'] = $user_activity->signin_notes;
        $item['signout_notes'] = $user_activity->signout_notes;
      }else{
        $item['signin_notes'] = 'N/A';
        $item['signout_notes'] = 'N/A';
      }
      $total_hours = explode('.', $item['total_hours']);
      if (sizeof($total_hours) > 1 ) {
        $partial = '.'.$total_hours[1];
        if ($partial < 0.1) {
          $item['total_hours'] = $total_hours[0];
      }
      if ($partial < 0.27 && $partial > 0.1) {
          $item['total_hours'] = $total_hours[0].'.25';
      }
      if ($partial > 0.27 && $partial <= 0.52) {
          $item['total_hours'] = $total_hours[0].'.5';
      }
      if ($partial > 0.52 && $partial <= 0.77) {
          $item['total_hours'] = $total_hours[0].'.75';
      }
      if ($partial > 0.77 && $partial < 1) {
          $item['total_hours'] = $total_hours[0]+ 1;
      }
      }
      if ($result->continuation == 0) {
          if ($item['total_hours'] < 4) {
            $item['total_hours'] = 4;
        }
      }
      $total_hours = explode('.', $item['hours']);
      if (sizeof($total_hours) > 1 ) {
        $partial = '.'.$total_hours[1];
        if ($partial < 0.1) {
          $item['hours'] = $total_hours[0];
      }
      if ($partial < 0.27 && $partial > 0.1) {
          $item['hours'] = $total_hours[0].'.25';
      }
      if ($partial > 0.27 && $partial <= 0.52) {
          $item['hours'] = $total_hours[0].'.5';
      }
      if ($partial > 0.52 && $partial <= 0.77) {
          $item['hours'] = $total_hours[0].'.75';
      }
      if ($partial > 0.77 && $partial < 1) {
          $item['hours'] = $total_hours[0]+ 1;
      }
      }
      if ($result->continuation == 0) {
          if ($item['hours'] < 4) {
            $item['hours'] = 4;
        }
      }
      if($result->job_status == 'completed'){
        $item['status'] = 'Approved';
      }else{
        $item['status'] = 'Unapproved';
      }
      
      if ($specific_sites_id != null && !empty($specific_sites_id)) {
        foreach ($specific_sites_id as $key => $value) {
          if ($result->site_id == $value) {
              array_push( $data, $item);
          }
      }
      }else{
          array_push( $data, $item);
      }
      }
      $endDate = strtotime('+1 day', strtotime($endDate)) ;
      $endDate = date('Y-m-d', $endDate);
      // echo $endDate.'-';
      $start = $endDate;
      }
      usort($data, function($a, $b) {
          return $a['guard_name'] <=> $b['guard_name'];
      });
      return $data;
      }
    public function getReportData($request)
    {
        ini_set('memory_limit', '64M');
      if (!empty($request['date'])) {
    $date = explode(' - ', $request['date']);

    $fromDate = \DateTime::createFromFormat('d/m/Y', trim($date[0]));
    $toDate = \DateTime::createFromFormat('d/m/Y', trim($date[1]));

    if ($fromDate && $toDate) {
        $startDate = $fromDate->format('Y-m-d 00:00');
        $endDate = $toDate->format('Y-m-d 23:59');
    } else {
        // fallback if parsing fails
        $startDate = now()->subDays(14)->format('Y-m-d 00:00');
        $endDate = now()->format('Y-m-d 23:59');
    }
} else {
    $startDate = now()->subDays(14)->format('Y-m-d 00:00');
    $endDate = now()->format('Y-m-d 23:59');
}


$extra_query = '(jr.`job_status` = "completed" OR jr.`job_status` = "pending" OR jr.`job_status` = "confirmed") AND ';

if (isset($request['customer_id']) && !empty($request['customer_id'])) {
    $customerConditions = "(";
    $i = 0;
    foreach ($request['customer_id'] as $key => $id) {
        $customerConditions .= "j.`customer_id` = '".$id."'";
        if ($i < sizeof($request['customer_id']) -1) {
            $customerConditions .= " OR ";
        }
        $i++;
    }
    $customerConditions .= ") AND ";

    $extra_query .= $customerConditions;
}

if (isset($request['state']) && !empty($request['state'])) {
    $stateConditions = "(";
    $i = 0;
    foreach ($request['state'] as $key => $id) {
        $stateConditions .= "j.`state` = '".$id."'";
        if ($i < sizeof($request['state']) -1) {
            $stateConditions .= " OR ";
        }
        $i++;
    }
    $stateConditions .= ") AND ";

    $extra_query .= $stateConditions;
}

if (isset($request['sites']) && !empty($request['sites'])) {
    $siteConditions = "(";
    $i = 0;
    foreach ($request['sites'] as $key => $id) {
        $siteConditions .= "j.`id` = '".$id."'";
        if ($i < sizeof($request['sites']) -1) {
            $siteConditions .= " OR ";
        }
        $i++;
    }
    $siteConditions .= ") AND ";

    $extra_query .= $siteConditions;
}




$subquery = DB::table('job_roster_actions AS jra_sub')
    ->select('jra_sub.roster_id', DB::raw('MAX(jra_sub.created_at) AS latest_created_at'))
    ->groupBy('jra_sub.roster_id');

$subqueryReason = DB::table('job_roster_actions AS jra_sub2')
    ->select('jra_sub2.roster_id', 'jra_sub2.reason')
    ->joinSub($subquery, 'latest_jra_sub', function ($join) {
        $join->on('jra_sub2.roster_id', '=', 'latest_jra_sub.roster_id')
            ->on('jra_sub2.created_at', '=', 'latest_jra_sub.latest_created_at');
    });

$results = DB::table('job_rosters AS jr')
    ->select(
        'jr.*',
        'j.id',
        'j.booking_id',
        'j.customer_id',
        'j.contractor_id',
        'j.po_wo as site_po_wo',
        'j.state',
        'j.address',
        'j.site_name',
        'j.site_description',
        'j.level',
        'j.payrol',
        'j.site_payrate',
        'j.break_payable',
        'j.break',
        'j.payrate_affective_from',
        'cust.name AS customer_name',
        'c.name AS contractor_name',
        'g.phone',
        'g.guard_type',
        'g.staff_type As staff_type',
        'g.phone AS guard_phone',
        'g.id AS guard_id',
        'g.email AS guard_email',
        'g.address AS guard_address',
        'g.profile_image AS guard_image',
        'g.first_name AS guard_first_name',
        DB::raw('COALESCE(g.first_name, jr.unprofile_name) AS guard_first_name'),
        'g.middle_name AS guard_middle_name',
        'g.last_name AS guard_last_name',
        'g.guard_type AS guard_type',
        'g.payrates_id AS guard_payrate_id',
        'g.suburb AS guard_suburb',
        'g.city AS guard_city',
        'g.state AS guard_state',
        'g.coordinates AS guard_coordinates',
        'g.postal_code AS guard_postal_code',
        'g.dob AS guard_dob',
        'g.gender AS guard_gender',
        'g.emergency_contact_name AS emergency_contact_name',
        'g.emergency_contact_phone AS emergency_contact_phone',
        'g.registration_type AS registration_type',
        'gw.guard_document_type AS residential_status',
        'gw.bsb AS bsb',
        'gw.account_holder AS account_holder',
        'gw.bank_name AS payroll_bank_name',
        'gw.bank_account_no AS payroll_bank_account_number',
        'latest_jra_sub.reason AS operation_notes',
        'ja.signin_time AS signin_time',
        'ja.signout_time AS signout_time',
        'g.guard_postion AS position',
        'jr.id AS id'
    )
    ->join('sites AS j', 'j.id', '=', 'jr.site_id')
    ->leftJoin('job_roster_activites AS ja', 'ja.job_roster_id', '=', 'jr.id')
    ->leftJoin('guard_work_details AS gw', 'gw.guard_id', '=', 'jr.guard_id')
    ->leftJoin('guards AS g', 'g.id', '=', 'jr.guard_id')
    ->leftJoin('customers AS cust', 'j.customer_id', '=', 'cust.id')
    ->leftJoin('contractors AS c', 'j.contractor_id', '=', 'c.id')
    ->leftJoinSub($subqueryReason, 'latest_jra_sub', 'latest_jra_sub.roster_id', '=', 'jr.id')
    ->whereRaw($extra_query . '(jr.job_status = ? OR jr.in_paysheet = ?) AND jr.shift_payable = ? AND jr.start BETWEEN ? AND ?',
    ['completed', 1, 'yes', $startDate, $endDate])
    ->orderBy('g.first_name', 'ASC')
    ->orderBy('g.last_name')
    ->orderBy('jr.start')
    ->whereNull('jr.deleted_at')
    ->get();

    $results = json_decode(json_encode($results), true);

    foreach ($results as $key => $roster) {
    $roster['day_rate'] = 0;
    $roster['night_rate'] = 0;
    $roster['public_holiday_rate'] = 0;
    $roster['saturday_rate'] = 0;
    $roster['sunday_rate'] = 0;
    $roster['total_amount'] = 0;
    $roster['ot'] = 0;

    // Add your custom rate logic here based on conditions and update $roster accordingly
    if ($roster['custome_rate'] > 0 && !empty($roster['custome_rate'])) {
        if ($roster['custome_payrate'] > 0  && !empty($roster['custome_payrate'])) {
            // Custom rate logic for custom_rate and custom_payrate
            $roster['day_rate'] = json_decode($roster['manualPayRate'])->payrate_mon_to_fri_day_rate;
            $roster['night_rate'] = json_decode($roster['manualPayRate'])->payrate_mon_to_fri_night_rate;
            $roster['public_holiday_rate'] = json_decode($roster['manualPayRate'])->payrate_pub_holi_day_rate;
            $roster['saturday_rate'] = json_decode($roster['manualPayRate'])->payrate_sun_day_rate;
            $roster['sunday_rate'] = json_decode($roster['manualPayRate'])->payrate_sun_day_rate;
        } else {
            // Custom rate logic for custom_rate without custom_payrate
            $payrate = Payrate::where('id', $roster['payrate'])->first();
            if ($payrate) {
                $roster['day_rate'] = $payrate->def_metro_mon_to_fri_day_rate;
                $roster['night_rate'] = $payrate->def_metro_mon_to_fri_night_rate;
                $roster['public_holiday_rate'] = $payrate->def_metro_pub_holi_day_rate;
                $roster['saturday_rate'] = $payrate->def_metro_sat_day_rate;
                $roster['sunday_rate'] = $payrate->def_metro_sun_day_rate;
            }
        }
    } else {
        // Default rate logic when neither custom_rate nor custom_payrate is true

        $site = Site::where('id', $roster['site_id'])->first();
        $payrate = Payrate::where('id', $site->site_payrate)->where('status', 'active')->first();
        // if(!empty($payrate)){
        //     if($roster['payrate_affective_from']){
        //         $payrate_effective_date = Carbon::createFromFormat('Y-m-d', $roster['payrate_affective_from']);
        //         $shift_date = Carbon::createFromFormat('Y-m-d H:i', $roster['start']);
        //         if ($payrate_effective_date->gt($shift_date)) {
        //             // actual payrate
        //         } elseif ($payrate_effective_date->lt($shift_date)) {
        //             // history payrate
        //             // return [$roster['site_id'], date('Y-m-d', strtotime($roster['start']));];
        //             $old_payrate = DB::table('site_payrate_history')->where('site_id', $roster['site_id'])
        //             ->whereDate('apply_date', '<=', date('Y-m-d', strtotime($roster['start'])))
        //             ->orderBy('apply_date', 'desc')
        //             ->first();
        //             if (!empty($old_payrate)) {
        //                 $payrate = Payrate::where('id', $old_payrate->payrate_id)->first();
        //             }
        //         }
        //     }else{
        //         $old_payrate = DB::table('site_payrate_history')->where('site_id', $roster['site_id'])
        //         ->whereDate('apply_date', '<=', date('Y-m-d', strtotime($roster['start'])))
        //         ->orderBy('apply_date', 'desc')
        //         ->first();
        //         if (!empty($old_payrate)) {
        //             $payrate = Payrate::where('id', $old_payrate->payrate_id)->first();
        //         }
        //     }
        // }else{
        //     $old_payrate = DB::table('site_payrate_history')->where('site_id', $roster['site_id'])
        //     ->whereDate('apply_date', '<=', date('Y-m-d', strtotime($roster['start'])))
        //     ->orderBy('apply_date', 'desc')
        //     ->first();
        //     if (!empty($old_payrate)) {
        //         $payrate = Payrate::where('id', $old_payrate->payrate_id)->first();
        //     }
            
        // }
        if (!empty($payrate)) {
            if ($site->type == 'metro') {
                if ($roster['payrol'] == 'award') {
                    $roster['day_rate'] = $payrate->award_metro_mon_to_fri_day_rate;
                    $roster['night_rate'] = $payrate->award_metro_mon_to_fri_night_rate;
                    $roster['public_holiday_rate'] = $payrate->award_metro_pub_holi_day_rate;
                    $roster['saturday_rate'] = $payrate->award_metro_sat_day_rate;
                    $roster['sunday_rate'] = $payrate->award_metro_sun_day_rate;
                } elseif ($roster['payrol'] == 'eba') {
                    $roster['day_rate'] = $payrate->eba_metro_mon_to_fri_day_rate;
                    $roster['night_rate'] = $payrate->eba_metro_mon_to_fri_night_rate;
                    $roster['public_holiday_rate'] = $payrate->eba_metro_pub_holi_day_rate;
                    $roster['saturday_rate'] = $payrate->eba_metro_sat_day_rate;
                    $roster['sunday_rate'] = $payrate->eba_metro_sun_day_rate;
                } else {
                    $roster['day_rate'] = $payrate->def_metro_mon_to_fri_day_rate;
                    $roster['night_rate'] = $payrate->def_metro_mon_to_fri_night_rate;
                    $roster['public_holiday_rate'] = $payrate->def_metro_pub_holi_day_rate;
                    $roster['saturday_rate'] = $payrate->def_metro_sat_day_rate;
                    $roster['sunday_rate'] = $payrate->def_metro_sun_day_rate;
                }
            } else {
                if ($roster['payrol'] == 'award') {
                    $roster['day_rate'] = $payrate->award_reg_mon_to_fri_day_rate;
                    $roster['night_rate'] = $payrate->award_reg_mon_to_fri_night_rate;
                    $roster['public_holiday_rate'] = $payrate->award_reg_pub_holi_day_rate;
                    $roster['saturday_rate'] = $payrate->award_reg_sat_day_rate;
                    $roster['sunday_rate'] = $payrate->award_reg_sun_day_rate;
                } elseif ($roster['payrol'] == 'eba') {
                    $roster['day_rate'] = $payrate->eba_reg_mon_to_fri_day_rate;
                    $roster['night_rate'] = $payrate->eba_reg_mon_to_fri_night_rate;
                    $roster['public_holiday_rate'] = $payrate->eba_reg_pub_holi_day_rate;
                    $roster['saturday_rate'] = $payrate->eba_reg_sat_day_rate;
                    $roster['sunday_rate'] = $payrate->eba_reg_sun_day_rate;
                } else {
                    $roster['day_rate'] = $payrate->def_reg_mon_to_fri_day_rate;
                    $roster['night_rate'] = $payrate->def_reg_mon_to_fri_night_rate;
                    $roster['public_holiday_rate'] = $payrate->def_reg_pub_holi_day_rate;
                    $roster['saturday_rate'] = $payrate->def_reg_sat_day_rate;
                    $roster['sunday_rate'] = $payrate->def_reg_sun_day_rate;
                }
            }
        }else {
            // Default rate logic for guard rate
            $guard_payrate = GuardWorkDetail::where('guard_id', $roster['guard_id'])->value('payrate');
            if (!empty($guard_payrate)) {
                $payrate = Payrate::where('id', $guard_payrate)->where('status', 'active')->first();
                // if(!empty($payrate)){
                    // $payrate_effective_date = Carbon::createFromFormat('Y-m-d', $payrate->effective_from);
                    // $shift_date = Carbon::createFromFormat('Y-m-d H:i', $roster['start']);
                    // if ($payrate_effective_date->gt($shift_date)) {
                    //     // actual payrate
                    // } elseif ($shift_date->lt($payrate_effective_date)) {
                    //     // history payrate
                    //     $payrate = Payrate::where('customer_id', $roster['customer_id'])
                    //     ->where('level', $roster['level'])
                    //     ->whereDate('effective_from', '<=', $roster['start'])
                    //     ->orderBy('effective_from', 'desc')
                    //     ->first();
                    // }
                // }else{
                //     $old_payrate = DB::table('site_payrate_history')->where('site_id', $roster['site_id'])
                //     ->whereDate('apply_date', '<=', $roster['start'])
                //     ->orderBy('apply_date', 'desc')
                //     ->first();
                //     if (!empty($old_payrate)) {
                //         $payrate = Payrate::where('id', $old_payrate->payrate_id)->first();
                //     }
                    
                // }
                if ($payrate) {
                    if ($roster['payrol'] == 'award') {
                        $roster['day_rate'] = $payrate->award_metro_mon_to_fri_day_rate;
                        $roster['night_rate'] = $payrate->award_metro_mon_to_fri_night_rate;
                        $roster['public_holiday_rate'] = $payrate->award_metro_pub_holi_day_rate;
                        $roster['saturday_rate'] = $payrate->award_metro_sat_day_rate;
                        $roster['sunday_rate'] = $payrate->award_metro_sun_day_rate;
                    } elseif ($roster['payrol'] == 'eba') {
                        $roster['day_rate'] = $payrate->eba_metro_mon_to_fri_day_rate;
                        $roster['night_rate'] = $payrate->eba_metro_mon_to_fri_night_rate;
                        $roster['public_holiday_rate'] = $payrate->eba_metro_pub_holi_day_rate;
                        $roster['saturday_rate'] = $payrate->eba_metro_sat_day_rate;
                        $roster['sunday_rate'] = $payrate->eba_metro_sun_day_rate;
                    } else {
                        $roster['day_rate'] = $payrate->def_metro_mon_to_fri_day_rate;
                        $roster['night_rate'] = $payrate->def_metro_mon_to_fri_night_rate;
                        $roster['public_holiday_rate'] = $payrate->def_metro_pub_holi_day_rate;
                        $roster['saturday_rate'] = $payrate->def_metro_sat_day_rate;
                        $roster['sunday_rate'] = $payrate->def_metro_sun_day_rate;
                    }
                }
            }
        }
    }

    $shift_hours = $roster['morning_hours'] + $roster['night_hours'] +  $roster['saturday_morning_hours'] + $roster['saturday_night_hours'] + $roster['sunday_morning_hours'] + $roster['sunday_night_hours'] + $roster['ph_morning_hours'] + $roster['ph_night_hours'];

    
    $roster['total_amount'] = ($roster['day_rate'] * $roster['morning_hours']) + ($roster['night_rate'] * $roster['night_hours']) + ($roster['public_holiday_rate'] * ($roster['ph_morning_hours'] + $roster['ph_night_hours'])) + ($roster['saturday_rate'] * ($roster['saturday_morning_hours'] + $roster['saturday_night_hours'])) + ($roster['sunday_rate'] * ($roster['sunday_morning_hours'] + $roster['sunday_night_hours']));
    if($roster['continuation'] == 0 && $roster['hours'] < 4){
        $extraHours = 4 - $roster['hours'];

        if($extraHours == $roster['morning_hours']){
            $roster['total_amount'] = $roster['total_amount'] - $extraHours * $roster['day_rate'];
        }
        if($extraHours == $roster['night_hours']){
            $roster['total_amount'] = $roster['total_amount'] - $extraHours * $roster['night_rate'];
        }
        if($extraHours == $roster['ph_morning_hours']){
            $roster['total_amount'] = $roster['total_amount'] - $extraHours * $roster['public_holiday_rate'];
        }
        if($extraHours == + $roster['ph_night_hours']){
            $roster['total_amount'] = $roster['total_amount'] - $extraHours * $roster['public_holiday_rate'];
        }
        if($extraHours == $roster['saturday_morning_hours']){
            $roster['total_amount'] = $roster['total_amount'] - $extraHours * $roster['saturday_rate'];
        }
        if($extraHours == $roster['saturday_night_hours']){
            $roster['total_amount'] = $roster['total_amount'] - $extraHours * $roster['saturday_rate'];
        }
        if($extraHours == $roster['sunday_morning_hours']){
            $roster['total_amount'] = $roster['total_amount'] - $extraHours * $roster['sunday_rate'];
        }
        if($extraHours == $roster['sunday_night_hours']){
            $roster['total_amount'] = $roster['total_amount'] - $extraHours * $roster['sunday_rate'];
        }
        $extraAmount = $extraHours * $roster['day_rate'];
        $roster['total_amount']  =  $roster['total_amount'] + $extraAmount;  
        $roster['hours'] = 4;
    }
    $results[$key] = $roster;
}


//dd($results);
// Return the modified results
return $results;
    
}



function getQuickReportData($request)
{
    if (isset($request['date']) && $request['date'] != '') {
        $date = $request['date'];
        $date = explode(' - ', $date);
        $from = strtotime(trim(str_replace('-', '/', $date[0])));
        $to = strtotime(trim(str_replace('-', '/', $date[1])));
    }else{
        $to = time();
        $from = time() - (60*60*24*14); // today to previous 14 days
    }

    $guard_paysheet = array();

    $temp1 = DB::table('job_rosters')
    ->join('guards', 'guards.id' , '=', 'job_rosters.guard_id')
    ->join('sites', 'sites.id' , '=', 'job_rosters.site_id')
    ->join('guard_work_details', 'guard_work_details.guard_id' , '=', 'job_rosters.guard_id')
    ->where('job_rosters.start', '>=', date('Y-m-d', $from))
    ->where('job_rosters.start', '<=', date('Y-m-d 23:59', $to))
    ->where('job_rosters.guard_id', '>', 0)->where(function($q) {
        $q->where('job_status', 'completed');
        $q->orWhere('job_rosters.admin_approved', 1);
    });
    $guard_roster = $temp1->select('job_rosters.*', 'guards.phone', 'guards.first_name', 'guards.middle_name', 'guards.last_name', 'guard_work_details.bsb', 'guard_work_details.tfn_file_no as tfn', 'guard_work_details.bank_account_no', 'guard_work_details.abn_no', 'sites.payrol as payrol')
    ->orderBY('job_rosters.start', 'ASC')
    ->get(); 
    
    foreach ($guard_roster as $roster) {
        // Initialize default rates
        $roster->day_rate = 0;
        $roster->night_rate = 0;
        $roster->public_holiday_rate = 0;
        $roster->saturday_rate = 0;
        $roster->sunday_rate = 0;
         if($roster->custome_rate && $roster->custome_payrate){
            // Access and set rates from the JSON object
            $roster->day_rate = json_decode($roster->manualPayRate)->payrate_mon_to_fri_day_rate;
            $roster->night_rate = json_decode($roster->manualPayRate)->payrate_mon_to_fri_night_rate;
            $roster->public_holiday_rate = json_decode($roster->manualPayRate)->payrate_pub_holi_day_rate;
            $roster->saturday_rate = json_decode($roster->manualPayRate)->payrate_sun_day_rate;
            $roster->sunday_rate = json_decode($roster->manualPayRate)->payrate_sun_day_rate;
            
        }elseif($roster->custome_rate){
            $payrate = Payrate::where('id', $roster->payrate)->first();
            if($payrate){
                $roster->day_rate =  $payrate->def_metro_mon_to_fri_day_rate;
                $roster->night_rate =  $payrate->def_metro_mon_to_fri_night_rate;
                $roster->public_holiday_rate =  $payrate->def_metro_pub_holi_day_rate;
                $roster->saturday_rate =  $payrate->def_metro_sat_day_rate;
                $roster->sunday_rate =  $payrate->def_metro_sun_day_rate;
            }
        }else{
            //for site rate
            $site = Site::where('id', $roster->site_id)->first();
            if(!empty($site)){
                $payrate = Payrate::where('id', $site->site_payrate)->first();
                if($payrate){
                    if($site->type == 'metro'){
                        if($roster->payrol == 'award'){
                            $roster->day_rate =  $payrate->award_metro_mon_to_fri_day_rate;
                            $roster->night_rate =  $payrate->award_metro_mon_to_fri_night_rate;
                            $roster->public_holiday_rate =  $payrate->award_metro_pub_holi_day_rate;
                            $roster->saturday_rate =  $payrate->award_metro_sat_day_rate;
                            $roster->sunday_rate =  $payrate->award_metro_sun_day_rate;
                        }elseif($roster->payrol == 'eba'){
                            $roster->day_rate =  $payrate->eba_metro_mon_to_fri_day_rate;
                            $roster->night_rate =  $payrate->eba_metro_mon_to_fri_night_rate;
                            $roster->public_holiday_rate =  $payrate->eba_metro_pub_holi_day_rate;
                            $roster->saturday_rate =  $payrate->eba_metro_sat_day_rate;
                            $roster->sunday_rate =  $payrate->eba_metro_sun_day_rate;
                        }else{
                            $roster->day_rate =  $payrate->def_metro_mon_to_fri_day_rate;
                            $roster->night_rate =  $payrate->def_metro_mon_to_fri_night_rate;
                            $roster->public_holiday_rate =  $payrate->def_metro_pub_holi_day_rate;
                            $roster->saturday_rate =  $payrate->def_metro_sat_day_rate;
                            $roster->sunday_rate =  $payrate->def_metro_sun_day_rate;
                        }
                    }else{
                        if($roster->payrol == 'award'){
                            $roster->day_rate =  $payrate->award_reg_mon_to_fri_day_rate;
                            $roster->night_rate =  $payrate->award_reg_mon_to_fri_night_rate;
                            $roster->public_holiday_rate =  $payrate->award_reg_pub_holi_day_rate;
                            $roster->saturday_rate =  $payrate->award_reg_sat_day_rate;
                            $roster->sunday_rate =  $payrate->award_reg_sun_day_rate;
                        }elseif($roster->payrol == 'eba'){
                            $roster->day_rate =  $payrate->eba_reg_mon_to_fri_day_rate;
                            $roster->night_rate =  $payrate->eba_reg_mon_to_fri_night_rate;
                            $roster->public_holiday_rate =  $payrate->eba_reg_pub_holi_day_rate;
                            $roster->saturday_rate =  $payrate->eba_reg_sat_day_rate;
                            $roster->sunday_rate =  $payrate->eba_reg_sun_day_rate;
                        }else{
                            $roster->day_rate =  $payrate->def_reg_mon_to_fri_day_rate;
                            $roster->night_rate =  $payrate->def_reg_mon_to_fri_night_rate;
                            $roster->public_holiday_rate =  $payrate->def_reg_pub_holi_day_rate;
                            $roster->saturday_rate =  $payrate->def_reg_sat_day_rate;
                            $roster->sunday_rate =  $payrate->def_reg_sun_day_rate;
                        }
                    }
                     
                }
            }else{
                //for guard rate
                $guard_payrate = GuardWorkDetail::where('guard_id', $roster->guard_id)->value('payrate');
                if(!empty($guard_payrate)){
                    $payrate = Payrate::where('id', $guard_payrate)->first();
                    if($payrate){
                        if($roster->payrol == 'award'){
                            $roster->day_rate =  $payrate->award_metro_mon_to_fri_day_rate;
                            $roster->night_rate =  $payrate->award_metro_mon_to_fri_night_rate;
                            $roster->public_holiday_rate =  $payrate->award_metro_pub_holi_day_rate;
                            $roster->saturday_rate =  $payrate->award_metro_sat_day_rate;
                            $roster->sunday_rate =  $payrate->award_metro_sun_day_rate;
                        }elseif($roster->payrol == 'eba'){
                            $roster->day_rate =  $payrate->eba_metro_mon_to_fri_day_rate;
                            $roster->night_rate =  $payrate->eba_metro_mon_to_fri_night_rate;
                            $roster->public_holiday_rate =  $payrate->eba_metro_pub_holi_day_rate;
                            $roster->saturday_rate =  $payrate->eba_metro_sat_day_rate;
                            $roster->sunday_rate =  $payrate->eba_metro_sun_day_rate;
                        }else{
                            $roster->day_rate =  $payrate->def_metro_mon_to_fri_day_rate;
                            $roster->night_rate =  $payrate->def_metro_mon_to_fri_night_rate;
                            $roster->public_holiday_rate =  $payrate->def_metro_pub_holi_day_rate;
                            $roster->saturday_rate =  $payrate->def_metro_sat_day_rate;
                            $roster->sunday_rate =  $payrate->def_metro_sun_day_rate;
                        }
                    }
            }
        }
    }
        if (!isset($guard_paysheet[$roster->guard_id])) {
                $guard_paysheet[$roster->guard_id] = array(
                    'guard_id' => $roster->guard_id, 
                    'name' => $roster->first_name.' '.$roster->middle_name.' '. $roster->last_name,
                    'phone' => $roster->phone, 
                    'tfn' => $roster->tfn, 
                    'pay' =>  0,
                    //'night_pay' => 0,
                    'bsb' => $roster->bsb != '' ? $roster->bsb : 'N/A',
                    'bank' => $roster->bank_account_no != '' ? $roster->bank_account_no : 'N/A',
                    'day' => $roster->morning_hours,
                    'day_pay' => $roster->morning_hours * $roster->day_rate, //f
                    'night' => $roster->night_hours,
                    'night_pay' => $roster->night_hours * $roster->night_rate,
                    'total_hours' => $roster->hours ,
                    'rate' => 0,
                    'saturday' => $roster->saturday_morning_hours + $roster->saturday_night_hours,
                    'saturday_pay' => $roster->saturday_morning_hours * $roster->saturday_rate + $roster->saturday_night_hours * $roster->saturday_rate,
                    'sunday' => $roster->sunday_morning_hours + $roster->sunday_night_hours,
                    'sunday_pay' => $roster->sunday_morning_hours * $roster->sunday_rate + $roster->sunday_night_hours * $roster->sunday_rate,
                    'ph' => $roster->ph_morning_hours + $roster->ph_night_hours,
                    'ph_pay' => $roster->ph_morning_hours * $roster->public_holiday_rate + $roster->ph_night_hours * $roster->public_holiday_rate,
                    'guard_payroll_id'=> '',
                    'from' => date('Y-m-d H:i', $from),
                    'to' => date('Y-m-d H:i', $to),
                    'payroll_abn_number' => $roster->abn_no != '' ? $roster->abn_no : "N/A", 
                    //'pay_rate' => $pay_rate
                );
            }else{
                $guard_paysheet[$roster->guard_id]['total_hours'] = $guard_paysheet[$roster->guard_id]['total_hours'] + $roster->hours;
                $guard_paysheet[$roster->guard_id]['day'] = $guard_paysheet[$roster->guard_id]['day'] + $roster->morning_hours;
                $guard_paysheet[$roster->guard_id]['day_pay'] = $guard_paysheet[$roster->guard_id]['day_pay'] + ($roster->morning_hours * $roster->day_rate);
                $guard_paysheet[$roster->guard_id]['night'] = $guard_paysheet[$roster->guard_id]['night'] + $roster->night_hours;
                $guard_paysheet[$roster->guard_id]['night_pay'] = $guard_paysheet[$roster->guard_id]['night_pay'] + ($roster->night_hours * $roster->night_rate);
                $guard_paysheet[$roster->guard_id]['saturday'] = $guard_paysheet[$roster->guard_id]['saturday'] +$roster->saturday_morning_hours + $roster->saturday_night_hours;
                $guard_paysheet[$roster->guard_id]['saturday_pay'] = $guard_paysheet[$roster->guard_id]['saturday_pay'] + ($roster->saturday_morning_hours * $roster->saturday_rate + $roster->saturday_night_hours * $roster->saturday_rate);
                $guard_paysheet[$roster->guard_id]['sunday'] = $guard_paysheet[$roster->guard_id]['sunday'] + $roster->sunday_morning_hours + $roster->sunday_night_hours;
                $guard_paysheet[$roster->guard_id]['sunday_pay'] = $guard_paysheet[$roster->guard_id]['sunday_pay'] + ($roster->saturday_morning_hours * $roster->saturday_rate + $roster->saturday_night_hours * $roster->saturday_rate);
                $guard_paysheet[$roster->guard_id]['ph'] = $guard_paysheet[$roster->guard_id]['ph'] + $roster->ph_morning_hours + $roster->ph_night_hours;
                $guard_paysheet[$roster->guard_id]['ph_pay'] = $guard_paysheet[$roster->guard_id]['ph_pay'] + ($roster->ph_morning_hours * $roster->public_holiday_rate + $roster->ph_night_hours * $roster->public_holiday_rate);
            }
    }
  return json_decode(json_encode($guard_paysheet), true);

}

function generateQuickPaysheetReport(Request $request)
    {
        if($request->type == 'preview'){
            $data = $this->getQuickReportData($request);
            return response()->json(['success'=> true, 'data'=>$data]);
        }else{
            $filename = time().'_quick_paysheet_report.xlsx';  
            Excel::store(new QuickPaysheetReportExport, 'excel/paysheet/'.$filename, 'excels');
            return response()->json(['success' =>  true, 'message' => 'Quick Paysheet Report generate successfully.','path' => 'https://'.request()->getHttpHost().'/excel/paysheet/'.$filename]);
        }
    }

   public function getShiftHours($start, $end, $siteID = null, $continuation = false, $public_holiday = null, $ph_duration = null) 
{
    $actual_start = $start;
    $actual_end = $end;
    $day_start = Carbon::parse($start)->format('l');
    $day_end = Carbon::parse($end)->format('l');

    $start = strtotime($start);
    $end = strtotime($end);

    $diff = $end - $start;
    $hours = round($diff / ( 60 * 60 ), 2);
    $hoursCond = round($diff / ( 60 * 60 ), 2);

    $morning_start = 6;
    $morning_end = 18;

    $night_start = 18;
    $night_end = 6;

    $shift_start = $this->convert_into_fraction($start);
    $shift_end = $this->convert_into_fraction($end);
    // return [$shift_start,$shift_end];
    if ($shift_end < $shift_start) {
        $diff_new = $shift_end + 24 - $shift_start;
        if ($diff > $diff_new) {
               $hours = $diff_new;
           }   
    }
    // saturday calcultions
    $saturday_start = 0;
    $saturday_end = 0;
    $total_saturday_hours = 0;

    $sunday_start = 0;
    $sunday_end = 0;
    $total_sunday_hours = 0;

    $total_ph_hours = 0;
    $ph_start = 0;
    $ph_end = 0;

    // publid holiday calculation start here
    $start_in_public_holiday = false;
    $end_in_public_holiday = false;
    if ($siteID != null) {
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

        if ($site_state && !empty($site_state->state) && isset($states_array[$site_state->state])) {
            $state = $states_array[$site_state->state];
        } else {
            $state = 'vic';
        }
    } else {
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
        $start = $public_holiday_start ? strtotime($public_holiday_start->date) + (60*60*24) : $start + (60*60*24) ;
        $day_start = Carbon::parse(date('m/d/Y', $end))->format('l');
        $hours = $hours - $total_ph_hours;
        $shift_start = 0;
        // echo 'Start in PH - '.$day_start;
    }elseif(!$start_in_public_holiday && $end_in_public_holiday){

        $ph_start_ts = strtotime(date('m/d/Y 00:00:00', $end));
        $diff = $end - $ph_start_ts;
        $total_ph_hours = round($diff / 3600, 2);
        $ph_start = $this->convert_into_fraction($ph_start_ts);
        $ph_end   = $this->convert_into_fraction($end);
        $end = $ph_start_ts;

        $shift_end = $this->convert_into_fraction($end);
        $hours = $hours - $total_ph_hours;
    }
    
  // if ($total_ph_hours == 0) {

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
        $sun_start = strtotime(date('m/d/Y 00:00:00', $end));
        // $diff = $end - $sun_start;
        // $total_sunday_hours = round($diff / ( 60 * 60 ), 2);
        $sunday_start = $this->convert_into_fraction($sun_start);
        $sunday_end = $this->convert_into_fraction($end);
        $total_sunday_hours = $sunday_end - $sunday_start;
        $shift_end = 24;
        $shift_start = 24;
        $hours = $hours - $total_sunday_hours;
    }
  // }
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

    if ($shift_end < $shift_start && $shift_end < 6 && $shift_end >= 1) {
        $shift_end += 24; 
    }

    $morning = $this->calculateHoursMorning($shift_start, $shift_end, $morning_start, $morning_end, $actual_start, $actual_end);
    if($morning > 12){
        $morning = $morning - 12;
    }

    $saturday_morning = round($this->calculateHoursMorning($saturday_start, $saturday_end, $morning_start, $morning_end, $actual_start, $actual_end), 2);

    $sunday_morning = round($this->calculateHoursMorning($sunday_start, $sunday_end, $morning_start, $morning_end, $actual_start, $actual_end), 2);

    $ph_morning = round($this->calculateHoursMorning($ph_start, $ph_end, $morning_start, $morning_end, $actual_start, $actual_end), 2);

    if ($morning < 0) {
        $morning = 0;
    }
    if ($saturday_morning < 0) {
        $saturday_morning = 0;
    }
    if ($sunday_morning < 0) {
        $sunday_morning = 0;
    }

    return [
        'morning' =>  $morning,
        'night' => round(((($hours - $morning) < 0) ? 0 : ($hours - $morning)), 2),
        'saturday_morning' => $saturday_morning,
        'saturday_night' => round(((($total_saturday_hours - $saturday_morning) < 0) ? 0 : ($total_saturday_hours - $saturday_morning)), 2),
        'sunday_morning' => $sunday_morning,
        'sunday_night' => round(((($total_sunday_hours - $sunday_morning) < 0) ? 0 : ($total_sunday_hours - $sunday_morning)), 2),
        'ph_morning' => $ph_morning,
        'ph_night' => round(((($total_ph_hours - $ph_morning) < 0) ? 0 : ($total_ph_hours - $ph_morning)), 2),
    ];
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

function calculateBreakTiming($morning, $night, $breakTime1, $breakTime2)
{
    if ($morning == 0 || $night == 0) {
        if ($morning == 0) {
            return array(
                0 => 0,
                1 => $night - $breakTime1 - $breakTime2
            ); 
        }else{
            return array(
                0 => $morning - $breakTime1 - $breakTime2,
                1 => 0
            );
        }
    }else{
        if ($morning > $night) {
            return array(
                0 => $morning - $breakTime1,
                1 => $night - $breakTime2,
            );
        }elseif ($morning < $night) {
            return array(
                0 => $night - $breakTime1,
                1 => $morning - $breakTime2,
            );
        }else {
            return array(
                0 => $morning - $breakTime1,
                1 => $night - $breakTime2,
            );
        }
    }
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



}
