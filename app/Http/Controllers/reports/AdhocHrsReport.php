<?php

namespace App\Http\Controllers\reports;

use App\Exports\AwardOverTimeExport;
use App\Exports\HrsByEmpReportExport;
use App\Exports\AdhocHoursExport;
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

class AdhocHrsReport extends Controller
{


    function generateAdhocHoursShiftReport()
    {
        // return Excel::download(new InvoiceReportExport, 'invoice_report.xlsx');
        $filename = time() . '_adhoc_hours_report.xlsx';
        Excel::store(new AdhocHoursExport, 'excel/adhochours/' . $filename, 'excels');
        return response()->json(['success' => true, 'message' => 'adhoc Hours Shift Report.', 'path' =>
        'https://' . request()->getHttpHost() . '/excel/adhochours/' . $filename]);
    }

    function getReportData($request)
    {
      if (isset($request['date']) && $request['date'] != '') {
        $date = $request['date'];
        $date = explode('-', $date);
        
        // Parse and reorder dates to match DD/MM/YYYY format
        $from = DateTime::createFromFormat('d/m/Y', trim($date[0]))->format('Y-m-d 00:00:00');
        $to = DateTime::createFromFormat('d/m/Y', trim($date[1]))->format('Y-m-d 23:59:59');
    } else {
        $today = today();
        $from = $today->startOfWeek()->format('Y-m-d');
        $to = $today->endOfWeek()->format('Y-m-d');
    }

        if (isset($request['sites']) && $request['sites'] != '') {
            $sites = explode(',', $request['sites']);
        }else{
            $sites = array();
        }
        if (isset($request['customer_name']) && $request['customer_name'] != '') {
            $customer_name = $request['customer_name'];
        }else{
            $customer_name = null;
        }
        //dd($this->getReportsData(date('Y-m-d', $from),date('Y-m-d 23:59:59', $to)));
        return $this->getAdhocpermanentHoursData($from, $to);
    }

    public function getAdhocpermanentHoursData($start, $end, $jobStatus = 'completed', $specific_sites_id = null, $multiple_states = null, $customer_id = null) {
      $extra_query = '(jr.`job_status` = "completed" OR jr.`job_status` = "pending" OR jr.`job_status` = "confirmed") AND ';

      // Add specific customer filtering if the session contains it
      if (session()->has('specific_customer') && session()->get('specific_customer') != '') {
          $specific_customer = json_decode(session()->get('specific_customer'));
          if (!empty($specific_customer)) {
              $extra_query = "(";
              $i = 0;
              foreach ($specific_customer as $key => $id) {
                  $extra_query .= "j.`customer_id` = '" . $id . "'";
                  if ($i < sizeof($specific_customer) - 1) {
                      $extra_query .= " OR ";
                  }
                  $i++;
              }
              $extra_query .= ") AND ";
          }
      }
  
      // Add specific site filtering
      if (!empty($specific_sites_id)) {
          $extra_query .= "j.`id` = '" . $specific_sites_id . "' AND ";
      }
  
      // Add state filtering
      if (!empty($multiple_states)) {
          $extra_query .= "(";
          foreach ($multiple_states as $index => $state) {
              $extra_query .= "j.`state` = '" . $state . "'";
              if ($index < sizeof($multiple_states) - 1) {
                  $extra_query .= " OR ";
              }
          }
          $extra_query .= ") AND ";
      }
  
      // Add customer filtering if provided
      if (!empty($customer_id)) {
          $extra_query .= "j.`customer_id` = '" . $customer_id . "' AND ";
      }
  
      // Calculate the date range and iteration counter
      $now = strtotime($end);
      $your_date = strtotime($start);
      $datediff = $now - $your_date;
      $datediff = round($datediff / (60 * 60 * 24));
      $counter = $datediff / 7;
  
      $data = [];
      for ($j = 1; $j <= $counter; $j++) {
        $startDate = $start;
        $endDate = strtotime("+7 day", strtotime($startDate)) - 1;
        $endDate = date('Y-m-d', $endDate);

        // SQL query for fetching data
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
            CONCAT(g.first_name, ' ', g.last_name) AS guard_name,
            g.`guard_type`,
            g.`position`,
            g.`phone` AS guard_phone,
            g.`id` AS guard_id,
            g.`email` AS guard_email,
            g.`address` AS guard_address,
            g.`profile_image` AS guard_image,
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
            g.`guard_status` AS guard_status,
            gw.`bank_account_no` AS payroll_bank_account_number,
            g.`covid_19` AS covid,
            gw.`tfn_file_no` AS payroll_tfn_number,
            gw.`abn_no` AS payroll_abn_number,
            g.`payroll_superannutation_name`,
            g.`payroll_bank_name`,
            g.`payroll_bank_account_number`,
            gi.`external_id`,
            -- cust.`flat_metro_week_day`,
            gpi.`payroll_id`,
            jr.`roster_id` AS roster_id,
            'adhoc' AS shift_type
        FROM job_rosters AS jr
        INNER JOIN sites AS j ON j.`id` = jr.`site_id`
        LEFT JOIN `guards` AS g ON g.`id` = jr.`guard_id`
        LEFT JOIN `guard_work_details` AS gw ON gw.`guard_id` = jr.`guard_id`
        LEFT JOIN customers AS cust ON j.`customer_id` = cust.`id`
        LEFT JOIN `contractors` AS c ON j.`contractor_id`= c.`id`
        LEFT JOIN `guard_external_ids` AS gi ON gi.`customer_id` = cust.`id` AND gi.`guard_id` = jr.`guard_id`
        LEFT JOIN `guard_payroll_ids` AS gpi ON gpi.`guard_id` = g.`id` AND gpi.`guard_id` = jr.`guard_id`
        WHERE " . $extra_query . " jr.`shift_payable` = 'yes' AND jr.`deleted_at` IS NULL AND jr.adhoc_shift = 'yes' AND jr.`start` BETWEEN '" . $startDate . "' AND '" . $endDate . "' 
        GROUP BY jr.id 
        ORDER BY g.name ASC, jr.unprofile_name";

        $query = DB::select($sql);

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
        g.`name` AS guard_name,
        g.`guard_type`,
        g.`position`,
        g.`phone` AS guard_phone,
        g.`id` AS guard_id,
        g.`email` AS guard_email,
        g.`address` AS guard_address,
        g.`profile_image` AS guard_image,
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
        g.`guard_status` AS guard_status,
        gw.`bank_account_no` AS payroll_bank_account_number,
        g.`covid_19` AS covid,
        gw.`tfn_file_no` AS payroll_tfn_number,
        gw.`abn_no` AS payroll_abn_number,
        g.`payroll_superannutation_name`,
        g.`payroll_bank_name`,
        g.`payroll_bank_account_number`,
        gi.`external_id`,
        -- cust.`flat_metro_week_day`,
        gpi.`payroll_id`,
        jr.`roster_id` AS roster_id,
        'permanent' AS shift_type
    FROM job_rosters AS jr
    INNER JOIN sites AS j ON j.`id` = jr.`site_id`
    LEFT JOIN `guards` AS g ON g.`id` = jr.`guard_id`
    LEFT JOIN `guard_work_details` AS gw ON gw.`guard_id` = jr.`guard_id`
    LEFT JOIN customers AS cust ON j.`customer_id` = cust.`id`
    LEFT JOIN `contractors` AS c ON j.`contractor_id`= c.`id`
    LEFT JOIN `guard_external_ids` AS gi ON gi.`customer_id` = cust.`id` AND gi.`guard_id` = jr.`guard_id`
    LEFT JOIN `guard_payroll_ids` AS gpi ON gpi.`guard_id` = g.`id` AND gpi.`guard_id` = jr.`guard_id`
    WHERE " . $extra_query . " jr.`shift_payable` = 'yes' AND jr.`deleted_at` IS NULL AND jr.adhoc_shift = 'no' AND jr.`start` BETWEEN '" . $startDate . "' AND '" . $endDate . "' 
    GROUP BY jr.id 
    ORDER BY g.name ASC, jr.unprofile_name";
      
          $query1 = DB::select($sql);
    
      }

        $mergedResults = array_merge($query, $query1);

        $data = collect($mergedResults);
        
      return $data;
      }
}
