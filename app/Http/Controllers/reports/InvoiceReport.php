<?php

namespace App\Http\Controllers\reports;

use App\Http\Controllers\Controller;
use App\Models\ChargeRate;
use App\Models\GuardWorkDetail;
use App\Models\Site;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\InvoiceReportExport;
use App\Exports\ProfitLossReportExport;
use DB;
use App\Models\Payrate;
use Carbon\Carbon;

class InvoiceReport extends Controller
{
    function generateInvoiceReport(Request $request)
    {
        if($request->type == 'preview'){
            $data = $this->getReportData($request);
            return response()->json([
                'success' => true,
                'type' => $request->type,
                'data' => $data,
            ]);
        }
        $filename = time().'_invoice_report.xlsx';  
        Excel::store(new InvoiceReportExport, 'excel/invoice/'.$filename, 'excels');
        return response()->json(['success' =>  true,'type' =>  $request->type, 'message' => 'Invoice Report generate successfully.','path' => 'https://'.request()->getHttpHost().'/excel/invoice/'.$filename]);
    }
    function generateProfitLossInvoice(Request $request)
    {
        if($request->type == 'preview'){
            $data = $this->getProfitLossInvoice($request);
            return response()->json([
                'success' => true,
                'data' => $data,
                'type' => $request->type,
            ]);
        }
        $filename = time().'profit_loss_report.xlsx';  
        Excel::store(new ProfitLossReportExport, 'excel/profit_loss/'.$filename, 'excels');
        return response()->json(['success' =>  true, 'type' => $request->type, 'message' => 'Profit Loss Report generate successfully.','path' => 'https://'.request()->getHttpHost().'/excel/profit_loss/'.$filename]);
    }

//     function getReportData($request)
//     {
//         if (isset($request['date']) && $request['date'] != '') {
//             $date = $request['date'];
//             $date = explode(' - ', $date);
//             $from = strtotime(trim(str_replace('-', '/', $date[0])));
//             $to = strtotime(trim(str_replace('-', '/', $date[1])));
//         }else{
//             $to = time();
//             $from = time() - (60*60*24*14);
//         }
//         $startDate = date('Y-m-d 00:00', $from);
//         $endDate = date('Y-m-d 23:59', $to);
//         $extra_query = '(jr.`job_status` = "completed" OR jr.`job_status` = "pending" OR jr.`job_status` = "confirmed") AND ';

//         // $extra_query .= "j.`customer_id` = '".$request['customer_id']."' AND ";
//     //     if (isset($request['sites']) && $request['sites'] != '') {
//     //         $sites = explode(',', $request['sites']);
//     //         $extra_query = "(";
//     //         $i = 0;
//     //         foreach ($sites as $key => $id) {
//     //           $extra_query .= "jr.`site_id` = '".$id."'";
//     //           if ($i < sizeof($sites) -1) {
//     //             $extra_query .= " OR ";
//     //         }
//     //         $i++;
//     //     }
//     //     $extra_query .= ") AND ";
//     // }
//         if (isset($request['customer_id']) && !empty($request['customer_id'])) {
//             $extra_query = "(";
//             $i = 0;
//             foreach ($request['customer_id'] as $key => $id) {
//               $extra_query .= "j.`customer_id` = '".$id."'";
//               if ($i < sizeof($request['customer_id']) -1) {
//                 $extra_query .= " OR ";
//             }
//             $i++;
//         }
//         $extra_query .= ") AND ";
//     }
//     if (isset($request['state']) && !empty($request['state'])) {
//         $extra_query = "(";
//         $i = 0;
//         foreach ($request['state'] as $key => $id) {
//           $extra_query .= "j.`state` = '".$id."'";
//           if ($i < sizeof($request['state']) -1) {
//             $extra_query .= " OR ";
//         }
//         $i++;
//     }
//     $extra_query .= ") AND ";
// }

// $data = [];
// $sql = "SELECT
// jr.*,
// -- jr.`chargerate_id` AS shift_chargerate_id,
// j.`id`,
// j.`booking_id`,
// j.`customer_id`,
// j.`contractor_id`,
// j.`state`,
// -- j.`stateType`,
// j.`address`,
// -- j.`details`,
// j.`site_name`,
// j.`site_description`,
// j.`level`,
// j.`payrol`,
// j.`site_payrate`,
// j.`site_charge_rate`,
// -- j.`payable` AS break_payable,
// j.`break`,
// -- j.`payable_and_chargeable_time`,
// -- j.`other_metro_weekday_day`,
// -- j.`site_charge_rate` AS site_chargerate_id,
// cust.`name` AS customer_name,
// cust.`charged_rates_id` as customer_chargerate_id,
// c.`name` AS contractor_name,
// g.`phone`,
// g.`guard_type`,
// g.`phone` AS guard_phone,
// g.`id` AS guard_id,
// g.`email` AS guard_email,
// g.`address` AS guard_address,
// g.`profile_image` AS guard_image,
// g.`first_name` AS guard_first_name,
// g.`middle_name` AS guard_middle_name,
// g.`last_name` AS guard_last_name,
// g.`guard_type` AS guard_type,
// g.`payrates_id` AS guard_payrate_id,
// g.`suburb` AS guard_suburb,
// g.`city` AS guard_city,
// g.`state` AS guard_state,
// g.`coordinates` AS guard_coordinates,
// g.`postal_code` AS guard_postal_code,
// g.`dob` AS guard_dob,
// g.`gender` AS guard_gender,
// g.`emergency_contact_name` AS emergency_contact_name,
// g.`emergency_contact_phone` AS emergency_contact_phone,
// g.`registration_type` AS registration_type,
// g.`residential_status` AS residential_status,
// -- g.`passport_number` AS passport_number,
// -- g.`passport_expiration` AS passport_expiration,
// -- g.`visa_number` AS visa_number,
// -- g.`visa_expiration` AS visa_expiration,
// -- g.`security_license_number` AS security_license_number,
// -- g.`security_license_expiration` AS security_license_expiration,
// -- g.`driver_license_number` AS driver_license_number,
// -- g.`driver_license_expiration` AS driver_license_expiration,
// -- g.`is_approved` AS is_approved,
// g.`bsb` AS bsb,
// g.`payroll_bank_name` AS payroll_bank_name,
// -- g.`status` AS guard_status,
// -- g.`payroll_bank_account_number` AS payroll_bank_account_number,
// -- g.`covid` AS covid,
// -- g.`fortnightly_working_hours` AS fortnightly_working_hours,
// -- g.`payroll_tfn_number` AS payroll_tfn_number,
// -- g.`payroll_abn_number` AS payroll_abn_number,
// -- g.`payroll_superannutation` AS payroll_superannutation,
// -- g.`payroll_bank_name` AS payroll_bank_name,
// -- g.`payroll_bank_account_number` AS payroll_bank_account_number,
// -- gi.`internal_id`,
// -- gi.`external_id`,
// -- cust.`flat_metro_week_day`,
// -- gpi.`payroll_id`,
// jr.`id` As roster_id
// FROM job_rosters AS jr
// INNER JOIN sites AS j ON j.`id` = jr.`site_id`
// LEFT JOIN `guards` AS g ON g.`id` = jr.`guard_id`
// LEFT JOIN customers AS cust ON j.`customer_id` = cust.`id`
// LEFT JOIN `contractors` AS c ON j.`contractor_id`= c.`id`
// -- LEFT JOIN `guard_ids` AS gi ON gi.`customer_id` = cust.`id` AND gi.`guard_id` = jr.`guard_id`
// LEFT JOIN `guard_payroll_ids` AS gpi ON gpi.`guard_id` = g.`id` AND gpi.`guard_id` = jr.`guard_id`
// WHERE ".$extra_query."jr.`shift_payable` = 'yes' AND jr.`start` BETWEEN '".$startDate."' AND '".$endDate."' ORDER BY j.site_name ASC, jr.start";
//             // WHERE ".$extra_query."jr.`temp_date` BETWEEN '".$startDate."' AND '".$endDate."' AND j.`payable`='yes'";

//             // WHERE jr.`job_status` = '".$jobStatus."' AND jr.temp_date BETWEEN '".$startDate."' AND '".$endDate."'";
//             // INNER JOIN `guards` AS g ON g.`id` = jr.`guard_id`

//             // echo $sql;exit();
// $query = DB::select($sql);
// foreach($query as $q)
// {
//     $day_rate = 0;
//     $night_rate = 0;
//     $saturday_rate = 0;
//     $sunday_rate = 0;
//     $ph_rate = 0;
//     if ($q->custome_chagerate == 1) {
//         $charge_rate = json_decode($q->manualChargeRate);
//         $day_rate = $charge_rate->chargerate_mon_to_fri_day_rate;
//         $night_rate = $charge_rate->chargerate_mon_to_fri_night_rate;
//         $saturday_rate = $charge_rate->chargerate_sat_day_rate;
//         $sunday_rate = $charge_rate->chargerate_sun_day_rate;
//         $ph_rate = $charge_rate->chargerate_pub_holi_day_rate;
//     }else
//     {
//         if($q->chargerate > 0)
//         {
//             $chargerate = DB::table('charge_rates')->where('id', $q->chargerate)->first();
//         }elseif($q->site_charge_rate > 0)
//         {
//             $chargerate = DB::table('charge_rates')->where('id', $q->site_charge_rate)->first();
//         }elseif($q->customer_chargerate_id > 0)
//         {
//             $chargerate = DB::table('charge_rates')->where('id', $q->customer_chargerate_id)->first();
//         }else{
//             $chargerate = array();
//         }
//         if (!empty($chargerate)) {
//             if ($q->payrol == 'default') {
//                 $day_rate = $chargerate->def_metro_mon_to_fri_day_rate;
//                 $night_rate = $chargerate->def_metro_mon_to_fri_night_rate;
//                 $saturday_rate = $chargerate->def_metro_sat_day_rate;
//                 $sunday_rate = $chargerate->def_metro_sun_day_rate;
//                 $ph_rate = $chargerate->def_metro_pub_holi_day_rate;
//             }elseif($q->payrol == 'award')
//             {
//                 $day_rate = $chargerate->award_metro_mon_to_fri_day_rate;
//                 $night_rate = $chargerate->award_metro_mon_to_fri_night_rate;
//                 $saturday_rate = $chargerate->award_metro_sat_day_rate;
//                 $sunday_rate = $chargerate->award_metro_sun_day_rate;
//                 $ph_rate = $chargerate->award_metro_pub_holi_day_rate;
//             }elseif($q->payrol == 'eba')
//             {
//                 $day_rate = $chargerate->eba_metro_mon_to_fri_day_rate;
//                 $night_rate = $chargerate->eba_metro_mon_to_fri_night_rate;
//                 $saturday_rate = $chargerate->eba_metro_sat_day_rate;
//                 $sunday_rate = $chargerate->eba_metro_sun_day_rate;
//                 $ph_rate = $chargerate->eba_metro_pub_holi_day_rate;
//             }
//         }

//     }
//     $q->day_rate = $day_rate;
//     $q->night_rate = $night_rate;
//     $q->saturday_rate = $saturday_rate;
//     $q->sunday_rate = $sunday_rate;
//     $q->ph_rate = $ph_rate;
//     $q->total_amount = $q->morning_hours * $day_rate + $q->night_hours * $night_rate + $q->saturday_morning_hours * $saturday_rate + $q->saturday_night_hours * $saturday_rate + $q->sunday_morning_hours * $sunday_rate + $q->sunday_night_hours * $sunday_rate + $q->ph_morning_hours * $ph_rate + $q->ph_night_hours * $ph_rate;

// }
// return json_decode(json_encode($query), true);
// }

//new

public function getReportData($request)
{
        if (isset($request['date']) && $request['date'] != '') {
            $date = $request['date'];
            $date = explode(' - ', $date);
            $from = strtotime(trim(str_replace('-', '/', $date[0])));
            $to = strtotime(trim(str_replace('-', '/', $date[1])));
        }else{
            $to = time();
            $from = time() - (60*60*24*14);
        }
        $startDate = date('Y-m-d 00:00', $from);
        $endDate = date('Y-m-d 23:59', $to);
        $extra_query = '(jr.`job_status` = "completed" OR jr.`job_status` = "pending" OR jr.`job_status` = "confirmed") AND ';
        if (isset($request['customer_id']) && !empty($request['customer_id'])) {
            $extra_query .= "(";
            $i = 0;
            foreach ($request['customer_id'] as $key => $id) {
                $extra_query .= "j.`customer_id` = '".$id."'";
                if ($i < sizeof($request['customer_id']) -1) {
                    $extra_query .= " OR ";
                }
                $i++;
            }
            $extra_query .= ") AND ";
        }
        if (isset($request['sites']) && $request['sites'] != '') {
            $extra_query .= "(";
            $i = 0;
            foreach ($request['sites'] as $key => $id) {
                $extra_query .= "jr.`site_id` = '".$id."'";
                if ($i < sizeof($request['sites']) -1) {
                    $extra_query .= " OR ";
                }
                $i++;
            }
            $extra_query .= ") AND ";
        }
        if (isset($request['state']) && !empty($request['state'])) {
            $extra_query .= "(";
            $i = 0;
            foreach ($request['state'] as $key => $id) {
                $extra_query .= "j.`state` = '".$id."'";
                if ($i < sizeof($request['state']) -1) {
                    $extra_query .= " OR ";
                }
                $i++;
            }
            $extra_query .= ") AND ";
        }

    $sql = "SELECT
    jr.*,
    j.`id`,
    j.`booking_id`,
    j.`customer_id`,
    j.`contractor_id`,
    j.`state`,
    -- j.`stateType`,
    j.`address`,
    -- j.`details`,
    j.`site_name`,
    j.`site_description`,
    j.`level`,
    j.`payrol`,
    j.`site_payrate`,
    -- j.`site_payrate_type`,
    j.`break_payable`,
    j.`break`,
    j.`po_wo` AS site_po_wo,
    -- j.`payable_and_chargeable_time`,
    -- j.`other_metro_weekday_day`,
    -- j.`apply_date`,
    cust.`name` AS customer_name,
    c.`name` AS contractor_name,
    g.`phone`,
    g.`guard_type`,
    -- g.`pay_by`,
    -- g.`abn_id`,
    -- g.`eba_id`,
    -- g.`award_id`,
    g.`phone` AS guard_phone,
    g.`id` AS guard_id,
    g.`email` AS guard_email,
    g.`address` AS guard_address,
    g.`profile_image` AS guard_image,
    -- g.`first_name` AS guard_first_name,
   (CASE 
    WHEN g.first_name IS NOT NULL 
    THEN CONCAT(g.first_name, ' ', 
        (CASE 
            WHEN g.middle_name IS NOT NULL AND g.middle_name != '' 
            THEN CONCAT(g.middle_name, ' ') 
            ELSE '' 
        END), 
        g.last_name)
    ELSE jr.unprofile_name 
END) AS guard_full_name,
    -- g.`middle_name` AS guard_middle_name,
    -- g.`last_name` AS guard_last_name,
    g.`guard_type` AS guard_type,
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
    gw.`guard_document_type` AS residential_status,
    gw.`bsb` AS bsb,
    gw.`bank_account_no` AS payroll_bank_name,
    gw.`bank_account_no` AS payroll_bank_account_number,
    ja.`signin_time` AS signin_time,
    ja.`signout_time` AS signout_time,
    g.`guard_postion` AS position,
    jr.`id` As roster_id
    FROM job_rosters AS jr
    INNER JOIN sites AS j ON j.`id` = jr.`site_id`
    LEFT JOIN job_roster_activites AS ja ON ja.`job_roster_id` = jr.`id`
    LEFT JOIN guard_work_details AS gw ON gw.`guard_id` = jr.`guard_id`
    LEFT JOIN `guards` AS g ON g.`id` = jr.`guard_id`
    LEFT JOIN customers AS cust ON j.`customer_id` = cust.`id`
    LEFT JOIN `contractors` AS c ON j.`contractor_id`= c.`id`
    -- LEFT JOIN `guard_ids` AS gi ON gi.`customer_id` = cust.`id` AND gi.`guard_id` = jr.`guard_id`
    -- LEFT JOIN `guard_payroll_ids` AS gpi ON gpi.`guard_id` = g.`id` AND gpi.`guard_id` = jr.`guard_id`
    WHERE ".$extra_query."jr.`shift_chargeable` = 'yes' AND AND jr.`deleted_at` IS NULL AND jr.`start` BETWEEN '".$startDate."' AND '".$endDate."' AND jr.deleted_at IS NULL ORDER BY j.site_name ASC, jr.start";
    $query = DB::select($sql);
    $results = json_decode(json_encode($query), true);

    // Loop through the results and apply custom rate logic
    foreach ($results as $key => $roster) {

        $roster['day_rate'] = 0;
        $roster['night_rate'] = 0;
        $roster['public_holiday_rate'] = 0;
        $roster['saturday_rate'] = 0;
        $roster['sunday_rate'] = 0;
        $roster['total_amount'] = 0;
        $roster['ot'] = 0;

        // Add your custom rate logic here based on conditions and update $roster accordingly
        if ($roster['custome_rate'] && $roster['custome_chagerate']) {
            // Custom rate logic for custome_rate and custome_payrate
            $roster['day_rate'] = json_decode($roster['manualChargeRate'])->chargerate_mon_to_fri_day_rate;
            $roster['night_rate'] = json_decode($roster['manualChargeRate'])->chargerate_mon_to_fri_night_rate;
            $roster['public_holiday_rate'] = json_decode($roster['manualChargeRate'])->chargerate_pub_holi_day_rate;
            $roster['saturday_rate'] = json_decode($roster['manualChargeRate'])->chargerate_sun_day_rate;
            $roster['sunday_rate'] = json_decode($roster['manualChargeRate'])->chargerate_sun_day_rate;
            $roster['total_amount'] = ($roster['day_rate'] * $roster['morning_hours']) + ($roster['night_rate'] * $roster['night_hours'] ) + ($roster['public_holiday_rate'] * ($roster['ph_morning_hours'] + $roster['ph_night_hours'])) + ($roster['saturday_rate'] * ($roster['saturday_morning_hours'] + $roster['saturday_night_hours'])) + ($roster['sunday_rate'] * ($roster['sunday_morning_hours'] + $roster['sunday_night_hours']));
        } elseif($roster['custome_rate']) {
            // Custom rate logic for custome_rate without custome_payrate
            $charge_rate = ChargeRate::where('id', $roster['chargerate'])->first();
            if($charge_rate){
                $roster['day_rate'] =  $charge_rate->def_metro_mon_to_fri_day_rate;
                $roster['night_rate'] =  $charge_rate->def_metro_mon_to_fri_night_rate;
                $roster['public_holiday_rate'] =  $charge_rate->def_metro_pub_holi_day_rate;
                $roster['saturday_rate'] =  $charge_rate->def_metro_sat_day_rate;
                $roster['sunday_rate'] =  $charge_rate->def_metro_sun_day_rate;
                $roster['total_amount'] = ($roster['day_rate'] * $roster['morning_hours']) + ($roster['night_rate'] * $roster['night_hours'] ) + ($roster['public_holiday_rate'] * ($roster['ph_morning_hours'] + $roster['ph_night_hours'])) + ($roster['saturday_rate'] * ($roster['saturday_morning_hours'] + $roster['saturday_night_hours'])) + ($roster['sunday_rate'] * ($roster['sunday_morning_hours'] + $roster['sunday_night_hours']));
            }
        } else {
            // Default rate logic when neither custome_rate nor custome_payrate is true
            //for site rate
            $site = Site::where('id', $roster['site_id'])->first();
            if(!empty($site)){
                $charge_rate = ChargeRate::where('id', $site->site_charge_rate)->first();
                // if(!empty($charge_rate)){
                //     $chargerate_effective_date = Carbon::createFromFormat('Y-m-d', $charge_rate->effective_from);
                //     $shift_date = Carbon::createFromFormat('Y-m-d H:i', $roster['start']);
                //     if ($chargerate_effective_date->gt($shift_date)) {
                //         // actual payrate
                //     } elseif ($shift_date->lt($chargerate_effective_date)) {
                //         // history payrate
                //         $charge_rate = Payrate::where('customer_id', $roster['customer_id'])
                //         ->where('level', $roster['level'])
                //         ->whereDate('effective_from', '<=', $roster['start'])
                //         ->orderBy('effective_from', 'desc')
                //         ->first();
                //     }
                // }else{
                //     $old_payrate = DB::table('site_payrate_history')->where('site_id', $roster['site_id'])
                //     ->whereDate('apply_date', '<=', $roster['start'])
                //     ->orderBy('apply_date', 'desc')
                //     ->first();
                //     if (!empty($old_payrate)) {
                //         $charge_rate = Payrate::where('id', $old_payrate->payrate_id)->first();
                //     }
                    
                // }
                if($charge_rate){
                    if($site->type == 'metro'){
                        if($roster['payrol'] == 'award'){
                            $roster['day_rate'] =  $charge_rate->award_metro_mon_to_fri_day_rate;
                            $roster['night_rate'] =  $charge_rate->award_metro_mon_to_fri_night_rate;
                            $roster['public_holiday_rate'] =  $charge_rate->award_metro_pub_holi_day_rate;
                            $roster['saturday_rate'] =  $charge_rate->award_metro_sat_day_rate;
                            $roster['sunday_rate'] =  $charge_rate->award_metro_sun_day_rate;
                            $roster['total_amount'] = ($roster['day_rate'] * $roster['morning_hours']) + ($roster['night_rate'] * $roster['night_hours'] ) + ($roster['public_holiday_rate'] * ($roster['ph_morning_hours'] + $roster['ph_night_hours'])) + ($roster['saturday_rate'] * ($roster['saturday_morning_hours'] + $roster['saturday_night_hours'])) + ($roster['sunday_rate'] * ($roster['sunday_morning_hours'] + $roster['sunday_night_hours']));
                        }elseif($roster['payrol'] == 'eba'){
                            $roster['day_rate'] =  $charge_rate->eba_metro_mon_to_fri_day_rate;
                            $roster['night_rate'] =  $charge_rate->eba_metro_mon_to_fri_night_rate;
                            $roster['public_holiday_rate'] =  $charge_rate->eba_metro_pub_holi_day_rate;
                            $roster['saturday_rate'] =  $charge_rate->eba_metro_sat_day_rate;
                            $roster['sunday_rate'] =  $charge_rate->eba_metro_sun_day_rate;
                            $roster['total_amount'] = ($roster['day_rate'] * $roster['morning_hours']) + ($roster['night_rate'] * $roster['night_hours'] ) + ($roster['public_holiday_rate'] * ($roster['ph_morning_hours'] + $roster['ph_night_hours'])) + ($roster['saturday_rate'] * ($roster['saturday_morning_hours'] + $roster['saturday_night_hours'])) + ($roster['sunday_rate'] * ($roster['sunday_morning_hours'] + $roster['sunday_night_hours']));
                        }else{
                            $roster['day_rate'] =  $charge_rate->def_metro_mon_to_fri_day_rate;
                            $roster['night_rate'] =  $charge_rate->def_metro_mon_to_fri_night_rate;
                            $roster['public_holiday_rate'] =  $charge_rate->def_metro_pub_holi_day_rate;
                            $roster['saturday_rate'] =  $charge_rate->def_metro_sat_day_rate;
                            $roster['sunday_rate'] =  $charge_rate->def_metro_sun_day_rate;
                            $roster['total_amount'] = ($roster['day_rate'] * $roster['morning_hours']) + ($roster['night_rate'] * $roster['night_hours'] ) + ($roster['public_holiday_rate'] * ($roster['ph_morning_hours'] + $roster['ph_night_hours'])) + ($roster['saturday_rate'] * ($roster['saturday_morning_hours'] + $roster['saturday_night_hours'])) + ($roster['sunday_rate'] * ($roster['sunday_morning_hours'] + $roster['sunday_night_hours']));
                        }
                    }else{
                        if($roster['payrol'] == 'award'){
                            $roster['day_rate'] =  $charge_rate->award_reg_mon_to_fri_day_rate;
                            $roster['night_rate'] =  $charge_rate->award_reg_mon_to_fri_night_rate;
                            $roster['public_holiday_rate'] =  $charge_rate->award_reg_pub_holi_day_rate;
                            $roster['saturday_rate'] =  $charge_rate->award_reg_sat_day_rate;
                            $roster['sunday_rate'] =  $charge_rate->award_reg_sun_day_rate;
                            $roster['total_amount'] = ($roster['day_rate'] * $roster['morning_hours']) + ($roster['night_rate'] * $roster['night_hours'] ) + ($roster['public_holiday_rate'] * ($roster['ph_morning_hours'] + $roster['ph_night_hours'])) + ($roster['saturday_rate'] * ($roster['saturday_morning_hours'] + $roster['saturday_night_hours'])) + ($roster['sunday_rate'] * ($roster['sunday_morning_hours'] + $roster['sunday_night_hours']));
                        }elseif($roster['payrol'] == 'eba'){
                            $roster['day_rate'] =  $charge_rate->eba_reg_mon_to_fri_day_rate;
                            $roster['night_rate'] =  $charge_rate->eba_reg_mon_to_fri_night_rate;
                            $roster['public_holiday_rate'] =  $charge_rate->eba_reg_pub_holi_day_rate;
                            $roster['saturday_rate'] =  $charge_rate->eba_reg_sat_day_rate;
                            $roster['sunday_rate'] =  $charge_rate->eba_reg_sun_day_rate;
                            $roster['total_amount'] = ($roster['day_rate'] * $roster['morning_hours']) + ($roster['night_rate'] * $roster['night_hours'] ) + ($roster['public_holiday_rate'] * ($roster['ph_morning_hours'] + $roster['ph_night_hours'])) + ($roster['saturday_rate'] * ($roster['saturday_morning_hours'] + $roster['saturday_night_hours'])) + ($roster['sunday_rate'] * ($roster['sunday_morning_hours'] + $roster['sunday_night_hours']));
                        }else{
                            $roster['day_rate'] =  $charge_rate->def_reg_mon_to_fri_day_rate;
                            $roster['night_rate'] =  $charge_rate->def_reg_mon_to_fri_night_rate;
                            $roster['public_holiday_rate'] =  $charge_rate->def_reg_pub_holi_day_rate;
                            $roster['saturday_rate'] =  $charge_rate->def_reg_sat_day_rate;
                            $roster['sunday_rate'] =  $charge_rate->def_reg_sun_day_rate;
                            $roster['total_amount'] = ($roster['day_rate'] * $roster['morning_hours']) + ($roster['night_rate'] * $roster['night_hours'] ) + ($roster['public_holiday_rate'] * ($roster['ph_morning_hours'] + $roster['ph_night_hours'])) + ($roster['saturday_rate'] * ($roster['saturday_morning_hours'] + $roster['saturday_night_hours'])) + ($roster['sunday_rate'] * ($roster['sunday_morning_hours'] + $roster['sunday_night_hours']));
                        }
                    }
                     
                }
            }
    }
    
    $results[$key] = $roster;
}


// Return the modified results
return $results;
    
}

function getProfitLossInvoice($request)
{
    $mainArr = [];
    if(isset($request['customer_id'])){
        foreach($request['customer_id'] as $cid){
            if ($request['date'] && $request['date'] != '') {
                $date = $request['date'];
                $date = explode(' - ', $date);
                $from = strtotime(trim(str_replace('-', '/', $date[0])));
                $to = strtotime(trim(str_replace('-', '/', $date[1])));
            }else{
                $to = time();
                $from = time() - (60*60*24*14);
            }
            $startDate = date('Y-m-d 00:00', $from);
            $endDate = date('Y-m-d 23:59', $to);
            $extra_query = '(jr.`job_status` = "completed" OR jr.`job_status` = "pending" OR jr.`job_status` = "confirmed") AND ';
            $extra_query .= "j.`customer_id` = '".$cid."' AND ";      

            $data = [];
            $sql = "SELECT
            jr.*,
            -- jr.`chargerate_id` AS shift_chargerate_id,
            j.`id`,
            j.`booking_id`,
            j.`customer_id`,
            j.`contractor_id`,
            j.`state`,
            -- j.`stateType`,
            j.`address`,
            -- j.`details`,
            j.`site_name`,
            j.`site_description`,
            j.`level`,
            j.`payrol`,
            j.`site_payrate`,
            j.`site_charge_rate`,
            -- j.`payable` AS break_payable,
            j.`break`,
            -- j.`payable_and_chargeable_time`,
            -- j.`other_metro_weekday_day`,
            j.`site_charge_rate` AS site_chargerate_id,
            cust.`name` AS customer_name,
            cust.`email` AS customer_email,
            cust.`address` AS customer_address,
            cust.`charged_rates_id` as customer_chargerate_id,
            c.`name` AS contractor_name,
            g.`phone`,
            g.`guard_type`,
            g.`phone` AS guard_phone,
            g.`id` AS guard_id,
            g.`email` AS guard_email,
            g.`address` AS guard_address,
            g.`profile_image` AS guard_image,
            g.`first_name` AS guard_first_name,
            g.`middle_name` AS guard_middle_name,
            g.`last_name` AS guard_last_name,
            g.`guard_type` AS guard_type,
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
            -- g.`passport_number` AS passport_number,
            -- g.`passport_expiration` AS passport_expiration,
            -- g.`visa_number` AS visa_number,
            -- g.`visa_expiration` AS visa_expiration,
            -- g.`security_license_number` AS security_license_number,
            -- g.`security_license_expiration` AS security_license_expiration,
            -- g.`driver_license_number` AS driver_license_number,
            -- g.`driver_license_expiration` AS driver_license_expiration,
            -- g.`is_approved` AS is_approved,
            g.`bsb` AS bsb,
            g.`payroll_bank_name` AS payroll_bank_name,
            -- g.`status` AS guard_status,
            -- g.`payroll_bank_account_number` AS payroll_bank_account_number,
            -- g.`covid` AS covid,
            -- g.`fortnightly_working_hours` AS fortnightly_working_hours,
            -- g.`payroll_tfn_number` AS payroll_tfn_number,
            -- g.`payroll_abn_number` AS payroll_abn_number,
            -- g.`payroll_superannutation` AS payroll_superannutation,
            -- g.`payroll_bank_name` AS payroll_bank_name,
            -- g.`payroll_bank_account_number` AS payroll_bank_account_number,
            -- gi.`internal_id`,
            -- gi.`external_id`,
            -- cust.`flat_metro_week_day`,
            -- gpi.`payroll_id`,
            jr.`id` As roster_id
            FROM job_rosters AS jr
            INNER JOIN sites AS j ON j.`id` = jr.`site_id`
            LEFT JOIN `guards` AS g ON g.`id` = jr.`guard_id`
            LEFT JOIN customers AS cust ON j.`customer_id` = cust.`id`
            LEFT JOIN `contractors` AS c ON j.`contractor_id`= c.`id`
            -- LEFT JOIN `guard_ids` AS gi ON gi.`customer_id` = cust.`id` AND gi.`guard_id` = jr.`guard_id`
            LEFT JOIN `guard_payroll_ids` AS gpi ON gpi.`guard_id` = g.`id` AND gpi.`guard_id` = jr.`guard_id`
            WHERE ".$extra_query."jr.`start` BETWEEN '".$startDate."' AND '".$endDate."' AND (jr.`job_status` = 'completed' OR jr.`in_paysheet` = 1) AND jr.`deleted_at` IS NULL ORDER BY g.first_name ASC, g.last_name ASC, jr.start";
                        // WHERE ".$extra_query."jr.`temp_date` BETWEEN '".$startDate."' AND '".$endDate."' AND j.`payable`='yes'";

                        // WHERE jr.`job_status` = '".$jobStatus."' AND jr.temp_date BETWEEN '".$startDate."' AND '".$endDate."'";
                        // INNER JOIN `guards` AS g ON g.`id` = jr.`guard_id`

                        // echo $sql;exit();
            $query = DB::select($sql);
            $grand_total_chargerate = 0;
            $grand_total_chargerate_hours = 0;
            $grand_total_payrate = 0;
            $grand_total_payrate_hours = 0;
            $grand_hours = 0;
            $grand_travel_time = 0;
            $grand_tax = 0;
            $guard_total_amount = 0;
            $allRecord = [];
            foreach($query as $key => $q)
            {
                $q->day_rate = 0;
                $q->night_rate = 0;
                $q->public_holiday_rate = 0;
                $q->saturday_rate = 0;
                $q->sunday_rate = 0;
                $q->total_amount = 0;
                $q->ot = 0;
                $day_rate = 0;
                $night_rate = 0;
                $saturday_rate = 0;
                $sunday_rate = 0;
                $ph_rate = 0;
                ############################# PAYRATE START ###################################
                // Add your custom rate logic here based on conditions and update $roster accordingly
                if ($q->shift_payable == 'yes'){
                    $allRecord[] = $q;
                    if ($q->custome_rate > 0 && !empty($q->custome_rate)) {
                        if ($q->custome_payrate > 0  && !empty($q->custome_payrate)) {
                            // Custom rate logic for custom_rate and custom_payrate
                            $q->day_rate = json_decode($q->manualPayRate)->payrate_mon_to_fri_day_rate;
                            $q->night_rate = json_decode($q->manualPayRate)->payrate_mon_to_fri_night_rate;
                            $q->public_holiday_rate = json_decode($q->manualPayRate)->payrate_pub_holi_day_rate;
                            $q->saturday_rate = json_decode($q->manualPayRate)->payrate_sun_day_rate;
                            $q->sunday_rate = json_decode($q->manualPayRate)->payrate_sun_day_rate;
                        } else {
                            // Custom rate logic for custom_rate without custom_payrate
                            $payrate = Payrate::where('id', $q->payrate)->first();
                            if ($payrate) {
                                $q->day_rate = $payrate->def_metro_mon_to_fri_day_rate;
                                $q->night_rate = $payrate->def_metro_mon_to_fri_night_rate;
                                $q->public_holiday_rate = $payrate->def_metro_pub_holi_day_rate;
                                $q->saturday_rate = $payrate->def_metro_sat_day_rate;
                                $q->sunday_rate = $payrate->def_metro_sun_day_rate;
                            }
                        }
                    } else {
                        // Default rate logic when neither custom_rate nor custom_payrate is true

                        $site = Site::where('id', $q->site_id)->first();
                            $payrate = Payrate::where('id', $site->site_payrate)->where('status', 'active')->first();
                            if (!empty($payrate)) {
                                if ($site->type == 'metro') {
                                    if ($q->payrol == 'award') {
                                        $q->day_rate = $payrate->award_metro_mon_to_fri_day_rate;
                                        $q->night_rate = $payrate->award_metro_mon_to_fri_night_rate;
                                        $q->public_holiday_rate = $payrate->award_metro_pub_holi_day_rate;
                                        $q->saturday_rate = $payrate->award_metro_sat_day_rate;
                                        $q->sunday_rate = $payrate->award_metro_sun_day_rate;
                                    } elseif ($q->payrol == 'eba') {
                                        $q->day_rate = $payrate->eba_metro_mon_to_fri_day_rate;
                                        $q->night_rate = $payrate->eba_metro_mon_to_fri_night_rate;
                                        $q->public_holiday_rate = $payrate->eba_metro_pub_holi_day_rate;
                                        $q->saturday_rate = $payrate->eba_metro_sat_day_rate;
                                        $q->sunday_rate = $payrate->eba_metro_sun_day_rate;
                                    } else {
                                        $q->day_rate = $payrate->def_metro_mon_to_fri_day_rate;
                                        $q->night_rate = $payrate->def_metro_mon_to_fri_night_rate;
                                        $q->public_holiday_rate = $payrate->def_metro_pub_holi_day_rate;
                                        $q->saturday_rate = $payrate->def_metro_sat_day_rate;
                                        $q->sunday_rate = $payrate->def_metro_sun_day_rate;
                                    }
                                } else {
                                    if ($q->payrol == 'award') {
                                        $q->day_rate = $payrate->award_reg_mon_to_fri_day_rate;
                                        $q->night_rate = $payrate->award_reg_mon_to_fri_night_rate;
                                        $q->public_holiday_rate = $payrate->award_reg_pub_holi_day_rate;
                                        $q->saturday_rate = $payrate->award_reg_sat_day_rate;
                                        $q->sunday_rate = $payrate->award_reg_sun_day_rate;
                                    } elseif ($q->payrol == 'eba') {
                                        $q->day_rate = $payrate->eba_reg_mon_to_fri_day_rate;
                                        $q->night_rate = $payrate->eba_reg_mon_to_fri_night_rate;
                                        $q->public_holiday_rate = $payrate->eba_reg_pub_holi_day_rate;
                                        $q->saturday_rate = $payrate->eba_reg_sat_day_rate;
                                        $q->sunday_rate = $payrate->eba_reg_sun_day_rate;
                                    } else {
                                        $q->day_rate = $payrate->def_reg_mon_to_fri_day_rate;
                                        $q->night_rate = $payrate->def_reg_mon_to_fri_night_rate;
                                        $q->public_holiday_rate = $payrate->def_reg_pub_holi_day_rate;
                                        $q->saturday_rate = $payrate->def_reg_sat_day_rate;
                                        $q->sunday_rate = $payrate->def_reg_sun_day_rate;
                                    }
                                }
                            }else {
                            // Default rate logic for guard rate
                            $guard_payrate = GuardWorkDetail::where('guard_id', $q->guard_id)->value('payrate');
                            if (!empty($guard_payrate)) {
                                $payrate = Payrate::where('id', $guard_payrate)->where('status', 'active')->first();
                                if ($payrate) {
                                    if ($q->payrol == 'award') {
                                        $q->day_rate = $payrate->award_metro_mon_to_fri_day_rate;
                                        $q->night_rate = $payrate->award_metro_mon_to_fri_night_rate;
                                        $q->public_holiday_rate = $payrate->award_metro_pub_holi_day_rate;
                                        $q->saturday_rate = $payrate->award_metro_sat_day_rate;
                                        $q->sunday_rate = $payrate->award_metro_sun_day_rate;
                                    } elseif ($q->payrol == 'eba') {
                                        $q->day_rate = $payrate->eba_metro_mon_to_fri_day_rate;
                                        $q->night_rate = $payrate->eba_metro_mon_to_fri_night_rate;
                                        $q->public_holiday_rate = $payrate->eba_metro_pub_holi_day_rate;
                                        $q->saturday_rate = $payrate->eba_metro_sat_day_rate;
                                        $q->sunday_rate = $payrate->eba_metro_sun_day_rate;
                                    } else {
                                        $q->day_rate = $payrate->def_metro_mon_to_fri_day_rate;
                                        $q->night_rate = $payrate->def_metro_mon_to_fri_night_rate;
                                        $q->public_holiday_rate = $payrate->def_metro_pub_holi_day_rate;
                                        $q->saturday_rate = $payrate->def_metro_sat_day_rate;
                                        $q->sunday_rate = $payrate->def_metro_sun_day_rate;
                                    }
                                }
                            }
                        }
                        
                    }

                    $shift_hours = $q->morning_hours + $q->night_hours +  $q->saturday_morning_hours + $q->saturday_night_hours + $q->sunday_morning_hours + $q->sunday_night_hours + $q->ph_morning_hours + $q->ph_night_hours;

                    
                    $q->total_amount = ($q->day_rate * $q->morning_hours) + ($q->night_rate * $q->night_hours) + ($q->public_holiday_rate * ($q->ph_morning_hours + $q->ph_night_hours)) + ($q->saturday_rate * ($q->saturday_morning_hours + $q->saturday_night_hours)) + ($q->sunday_rate * ($q->sunday_morning_hours + $q->sunday_night_hours));
                    if($q->continuation == 0 && $q->hours < 4){
                        $extraHours = 4 - $q->hours;
                        if($extraHours == $q->morning_hours){
                            $q->total_amount = $q->total_amount - $extraHours * $q->day_rate;
                        }
                        if($extraHours == $q->night_hours){
                            $q->total_amount = $q->total_amount - $extraHours * $q->night_rate;
                        }
                        if($extraHours == $q->ph_morning_hours){
                            $q->total_amount = $q->total_amount - $extraHours * $q->public_holiday_rate;
                        }
                        if($extraHours == + $q->ph_night_hours){
                            $q->total_amount = $q->total_amount - $extraHours * $q->public_holiday_rate;
                        }
                        if($extraHours == $q->saturday_morning_hours){
                            $q->total_amount = $q->total_amount - $extraHours * $q->saturday_rate;
                        }
                        if($extraHours == $q->saturday_night_hours){
                            $q->total_amount = $q->total_amount - $extraHours * $q->saturday_rate;
                        }
                        if($extraHours == $q->sunday_morning_hours){
                            $q->total_amount = $q->total_amount - $extraHours * $q->sunday_rate;
                        }
                        if($extraHours == $q->sunday_night_hours){
                            $q->total_amount = $q->total_amount - $extraHours * $q->sunday_rate;
                        }
                        $extraAmount = $extraHours * $q->day_rate;
                        $q->total_amount  =  $q->total_amount + $extraAmount;  
                        $q->hours = 4;
                    }
                    $travel_rate = $q->travel_time_value * $q->day_rate;
                    $grand_total_payrate = $grand_total_payrate + $q->total_amount + $travel_rate;

                    if((isset($query[$key + 1]) && $q->guard_id == $query[$key + 1]->guard_id) || count($query) == 1){
                        $guard_total_amount = $guard_total_amount + $q->total_amount + $travel_rate;

                    }else{
                        if((isset($query[$key + 1]) && $q->guard_id != $query[$key + 1]->guard_id) || (!isset($query[$key + 1]))){
                            $guard_total_amount = $guard_total_amount + $q->total_amount + $travel_rate;
                        }
                        $annual_income = $guard_total_amount * 26;
                        if ($annual_income <= 18200) {
                            $tax = 0;
                        } elseif ($annual_income > 18200 && $annual_income <= 45000) {
                            $tax = (0.19 * ($annual_income - 18200))/26;
                        } elseif ($annual_income > 45000 && $annual_income <= 120000) {
                            $tax = (5092 + 0.325 * ($annual_income - 45000))/26;
                        } elseif ($annual_income > 120000 && $annual_income <= 180000) {
                            $tax = (29467 + 0.37 * ($annual_income - 120000))/26;
                        } elseif($annual_income > 180000){
                            $tax = (51667 + 0.45 * ($annual_income - 180000))/26;
                        }
                        $grand_tax += $tax;
                        
                        $guard_total_amount = 0;
                    }
                    $grand_total_payrate_hours = $grand_total_payrate_hours + $q->hours + $q->travel_time_value;
                }
                ############################# PAYRATE END #####################################
                if ($q->shift_chargeable == 'yes'){

                    if ($q->custome_chagerate == 1) {
                        $charge_rate = json_decode($q->manualChargeRate);
                        $day_rate = $charge_rate->chargerate_mon_to_fri_day_rate;
                        $night_rate = $charge_rate->chargerate_mon_to_fri_night_rate;
                        $saturday_rate = $charge_rate->chargerate_sat_day_rate;
                        $sunday_rate = $charge_rate->chargerate_sun_day_rate;
                        $ph_rate = $charge_rate->chargerate_pub_holi_day_rate;
                    }else
                    {
                        if($q->chargerate > 0)
                        {
                            $chargerate = DB::table('charge_rates')->where('id', $q->chargerate)->first();
                        }elseif($q->site_charge_rate > 0)
                        {
                            $chargerate = DB::table('charge_rates')->where('id', $q->site_charge_rate)->first();
                        }elseif($q->customer_chargerate_id > 0)
                        {
                            $chargerate = DB::table('charge_rates')->where('id', $q->customer_chargerate_id)->first();
                        }else{
                            $chargerate = array();
                        }
                        if (!empty($chargerate)) {
                            if ($q->payrol == 'default') {
                                $day_rate = $chargerate->def_metro_mon_to_fri_day_rate;
                                $night_rate = $chargerate->def_metro_mon_to_fri_night_rate;
                                $saturday_rate = $chargerate->def_metro_sat_day_rate;
                                $sunday_rate = $chargerate->def_metro_sun_day_rate;
                                $ph_rate = $chargerate->def_metro_pub_holi_day_rate;
                            }elseif($q->payrol == 'award')
                            {
                                $day_rate = $chargerate->award_metro_mon_to_fri_day_rate;
                                $night_rate = $chargerate->award_metro_mon_to_fri_night_rate;
                                $saturday_rate = $chargerate->award_metro_sat_day_rate;
                                $sunday_rate = $chargerate->award_metro_sun_day_rate;
                                $ph_rate = $chargerate->award_metro_pub_holi_day_rate;
                            }elseif($q->payrol == 'eba')
                            {
                                $day_rate = $chargerate->eba_metro_mon_to_fri_day_rate;
                                $night_rate = $chargerate->eba_metro_mon_to_fri_night_rate;
                                $saturday_rate = $chargerate->eba_metro_sat_day_rate;
                                $sunday_rate = $chargerate->eba_metro_sun_day_rate;
                                $ph_rate = $chargerate->eba_metro_pub_holi_day_rate;
                            }
                        }

                    }
                    $q->day_rate = $day_rate;
                    $q->night_rate = $night_rate;
                    $q->saturday_rate = $saturday_rate;
                    $q->sunday_rate = $sunday_rate;
                    $q->ph_rate = $ph_rate;
                    $q->total_amount = $q->morning_hours * $day_rate + $q->night_hours * $night_rate + $q->saturday_morning_hours * $saturday_rate + $q->saturday_night_hours * $saturday_rate + $q->sunday_morning_hours * $sunday_rate + $q->sunday_night_hours * $sunday_rate + $q->ph_morning_hours * $ph_rate + $q->ph_night_hours * $ph_rate;
                    if (!isset($data[$q->site_id])) {
                        $data[$q->site_id]['site_name'] = $q->site_name;
                        $data[$q->site_id]['site_description'] = $q->site_description;
                        $data[$q->site_id]['customer_name'] = $q->customer_name;
                        $data[$q->site_id]['customer_email'] = $q->customer_email;
                        $data[$q->site_id]['customer_address'] = $q->customer_address;
                        $data[$q->site_id]['hours'] = $q->hours;

                        $data[$q->site_id]['morning_hours'] = $q->morning_hours;
                        $data[$q->site_id]['day_rate'] = $day_rate;

                        $data[$q->site_id]['night_hours'] = $q->night_hours;
                        $data[$q->site_id]['night_rate'] = $night_rate;

                        $data[$q->site_id]['saturday_hours'] = $q->saturday_morning_hours + $q->saturday_night_hours;
                        $data[$q->site_id]['saturday_rate'] = $saturday_rate;

                        $data[$q->site_id]['sunday_hours'] = $q->sunday_morning_hours + $q->sunday_night_hours;
                        $data[$q->site_id]['sunday_rate'] = $sunday_rate;

                        $data[$q->site_id]['ph_hours'] = $q->ph_night_hours + $q->ph_morning_hours;
                        $data[$q->site_id]['ph_rate'] = $ph_rate;
                        $data[$q->site_id]['any_cutom_rate'] = $q->custome_chagerate  == 1 ? true : false;
                        $data[$q->site_id]['total_amount'] = $q->total_amount;
                        $grand_total_chargerate += $q->total_amount;
                        $grand_hours += $q->hours;
                        $grand_travel_time += $q->travel_time_value;

                    }else{
                        $data[$q->site_id]['hours'] += $q->hours;

                        $data[$q->site_id]['morning_hours'] += $q->morning_hours;
                        $data[$q->site_id]['day_rate'] = $day_rate;

                        $data[$q->site_id]['night_hours'] += $q->night_hours;
                        $data[$q->site_id]['night_rate'] = $night_rate;

                        $data[$q->site_id]['saturday_hours'] += $q->saturday_morning_hours + $q->saturday_night_hours;
                        $data[$q->site_id]['saturday_rate'] = $saturday_rate;

                        $data[$q->site_id]['sunday_hours'] += $q->sunday_morning_hours + $q->sunday_night_hours;
                        $data[$q->site_id]['sunday_rate'] = $sunday_rate;

                        $data[$q->site_id]['ph_hours'] += $q->ph_night_hours + $q->ph_morning_hours;
                        $data[$q->site_id]['ph_rate'] = $ph_rate;
                        $data[$q->site_id]['total_amount'] += $q->total_amount;
                        $grand_total_chargerate += $q->total_amount;
                        $grand_hours += $q->hours;
                        $grand_travel_time += $q->travel_time_value;
                        if ($q->custome_chagerate  == 1) {
                            $data[$q->site_id]['any_cutom_rate'] = $q->custome_chagerate  == 1 ? true : false;
                        }
                    }
                }
            }
            $new_data = array();
            foreach ($data as $key => $value) {
                $value['hours'] = round($value['hours']);
                if ($value['any_cutom_rate'] == true) {
                    $value['day_rate'] = 'Variable';
                    $value['night_rate'] = 'Variable';
                    $value['saturday_rate'] = 'Variable';
                    $value['sunday_rate'] = 'Variable';
                    $value['ph_rate'] = 'Variable';
                }
                $new_data[] = $value;
            }
            $mainArr[] = [
                'customer_name' => DB::table('customers')->where('id', $cid)->first(),
                'chargerate_hours' => number_format($grand_hours, 2),
                'travel_time' => number_format($grand_travel_time, 2),
                'charge_amount' => round($grand_total_chargerate, 2),
                'tax' => round($grand_tax, 2),
                'pay_amount' => round($grand_total_payrate, 2),
                'pay_hours' => round($grand_total_payrate_hours, 2),
                'data_from' => $startDate,
                'data_to' => $endDate,
                'allRecord' => $allRecord
            ];
        }
    }
    return $mainArr; 
    // if (!empty($new_data)) {
    //     return response()->json(['success' => true,'data' => $mainArr, 'total_hours' => number_format($grand_hours, 2), 'grand_total' => number_format($grand_total_chargerate, 2)]);
    //     // return response()->json(['success' => true, 'data' => $new_data, 'grand_total' => number_format($grand_total_chargerate, 2)]);
    // }else{
    //     return response()->json(['success' => false,'data' => $mainArr,  'total_hours' => number_format($grand_hours, 2), 'grand_total' => number_format($grand_total_chargerate, 2)]);
    //     // return response()->json(['success' => false, 'data' => $new_data, 'grand_total' => number_format($grand_total_chargerate, 2)]);

    // }
}

function sendInvoice(Request $request)
{
    $pdf_path = public_path().'/uploads/'. fileUpload($request->invoice, 'uploads');
    $emails = json_decode($request->email, true);
    foreach($emails as $e){
    $email = [
        'subject' => 'Invoice Report',
        'message' => 'Here is your invoice report.',
        'email' => $e['email'],
        'attachment' => $pdf_path
    ];
    systemEmail($email);
    }
    return response()->json(['success' => true,'message' => 'Invoice send successfully.']);



}

}

