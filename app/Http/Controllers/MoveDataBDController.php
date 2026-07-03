<?php

namespace App\Http\Controllers;

use App\Models\ChargeRate;
use App\Models\DocumentCategory;
use App\Models\Guard;
use Illuminate\Support\Facades\Http;
use App\Models\GuardDocument;
use App\Models\GuardWorkDetail;
use App\Models\Questionnaire;
use App\Models\Payrate;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Http\Request;

class MoveDataBDController extends Controller
{
    //

    function getAdmins247SecurityGroupe(Request $request)  {
        
    $connectionName = 'mysql3';
    $connectionName2 = 'mysql4';
    $results = DB::connection($connectionName)
        ->table('administrators')
        ->where('status', 'active')
        ->get();

        foreach ($results as $key => $row) {
            $data = [
                'name' => $row->name,
                'email' => $row->email,
                'password' => $row->password,
                'status' => $row->status,
                'state' => $row->state,
                'specific_sites' => $row->specific_sites,
                'specific_customer' => $row->specific_customer,
                'hide_status' => $row->hide_status,  
                'is_email_verify' => 'yes',  
            ];
            
            DB::connection($connectionName2)
            ->table('users')->insert($data);
        }

    }


    function getLocation247SecurityGroupe(Request $request)  {
        
        $connectionName = 'mysql3';
        $connectionName2 = 'mysql4';
        $results = DB::connection($connectionName)
            ->table('jobs')
            ->get();
    
            foreach ($results as $key => $row) {
                $data = [
                   'booking_id' => $row->booking_id,   
                   'customer_id' => $row->customer_id,   
                   'contractor_id' => $row->contractor_id,   
                   'level' => $row->level,   
                   'state' => $row->state,
                   'payrol' => $row->payrol,   
                   'trained' => $row->trained,
                   'green_call' => $row->green_call,
                   'welfare_call' => $row->welfare_call,
                   'welfare_timing' => $row->welfare_timing,
                   'sos_phone' => $row->sos_phone,
                   'start' => $row->start,
                   'end' => $row->end,
                   'address' => $row->address,
                   'coordinates' => $row->coordinates,
                   'hourly_rate' => $row->hourly_rate,
                   'site_status' => $row->status,
                   'site_name' => $row->site_name,
                   'site_description' => $row->site_description,
                   'signin_radius' => $row->signin_radius,
                   'alert_radius' => $row->alert_radius,
                   'break' => $row->break,
                   'break_chargeable' => $row->chargeable,
                   'break_payable' => $row->payable,
                   'site_payrate' => $row->site_payrate,
                   'site_charge_rate' => $row->site_charge_rate,
                   'site_chargerate_level' => $row->site_chargerate_level,
                   'site_payrate_level' => $row->site_payrate_level,
                   'fatigue' => $row->fatigue,
                   'break_deduction_chargeable' => $row->break_deduction_chargeable,
                   'site_hours' => $row->site_hours,
                   'site_type' => $row->site_type,
                   'unpublished_site' => $row->unpublished_site,
                   'updated_at' => $row->updated_at,
                   'site_tasks' => $row->site_tasks,
                ];
                DB::connection($connectionName2)
                ->table('sites')->insert($data);
            }
    
        }

        function getCustomers247SecurityGroupe(Request $request)  {
        
            $connectionName = 'mysql3';
            $connectionName2 = 'mysql4';
            $results = DB::connection($connectionName)
                ->table('customers')
                ->get();
        
                foreach ($results as $key => $row) {
                    $data = [
                       'name' => $row->name,   
                       'email' => $row->email,   
                       'password' => $row->password,   
                       'phone' => $row->phone,   
                       'address' => $row->address,   
                       'city' => $row->city,   
                       'state' => $row->state,   
                       'postal_code' => $row->postal_code,   
                       'status' => $row->status,   
                       'web_url' => $row->url,   
                       'created_at' => $row->created_at,   
                       'updated_at' => $row->updated_at,   
                       'timestamp_joined' => $row->timestamp_joined,   
                       'timestamp_activity' => $row->timestamp_activity,   
                    ];
                    DB::connection($connectionName2)
                    ->table('customers')->insert($data);
                }
        
        }


        // function getGuards247SecurityGroupe(Request $request)  {
        
        //     $connectionName = 'mysql3';
        //     $connectionName2 = 'mysql4';
        //     $results = DB::connection($connectionName)
        //         ->table('guards')
        //         ->where('status', 'active')
        //         ->get();
        
        //         foreach ($results as $key => $row) {
        //             $data = [
        //                'first_name' => $row->first_name,      
        //                'middle_name' => $row->middle_name,      
        //                'last_name' => $row->last_name,      
        //                'email' => $row->email,      
        //                'password' => $row->password,      
        //                'phone' => $row->phone,      
        //                'address' => $row->address,   
        //                'coordinates' => $row->coordinates,   
        //                'city' => $row->city,   
        //                'suburb' => $row->suburb,   
        //                'state' => $row->state,      
        //                'postal_code' => $row->postal_code,    
        //                'dob' => $row->dob,    
        //                'gender' => $row->gender,    
        //                'emergency_contact_name' => $row->emergency_contact_name,    
        //                'emergency_contact_phone' => $row->emergency_contact_phone,    
        //                'work_limitation_status' => $row->work_limitation_status,    
        //                'weekly_work_hours_limitation' => $row->fortnightly_working_hours,    
        //                'is_available' => 'yes',    
        //                'is_email_approved' => 'yes',    
        //                'staff_type' => 'casual',    
        //                'guard_type' => $row->guard_type,    
        //                'annual_leave_hours' => $row->annual_leave_hours,
        //                'sick_leave_hours' => $row->sick_leave_hours,
        //                'payroll_bank_name' => $row->payroll_bank_name,
        //                'payroll_bank_account_number' => $row->payroll_bank_account_number,
        //                'payroll_abn_number' => $row->payroll_abn_number,
        //                'bsb' => $row->bsb,
        //             ];
        //             $guard = DB::connection($connectionName2)
        //             ->table('guards')->insertGetId($data);
        //             $getGuardID =  DB::connection($connectionName2)
        //             ->table('guards')->find($guard);
        //             // $updateEmpDetails = new GuardWorkDetail();
        //             // $updateEmpDetails->guard_id = $guard;
        //             // $updateEmpDetails->hired_on = date('Y-m-d H:i:s', $row->timestamp_joined);
        //             // $updateEmpDetails->tfn_file_no = $row->payroll_tfn_number;
        //             // $updateEmpDetails->superannutation_no = $row->payroll_superannutation;
        //             // $updateEmpDetails->account_holder = $row->payroll_bank_name;
        //             // $updateEmpDetails->superannutation_name = $row->payroll_superannutation_name;
        //             // $updateEmpDetails->abn_no = $row->payroll_abn_number;
        //             // $updateEmpDetails->bank_name = $row->payroll_bank_name;
        //             // $updateEmpDetails->bsb = $row->bsb;
        //             // $updateEmpDetails->bank_account_no = $row->payroll_bank_account_number;


        //             $visa_status = null;
        //             if($row->residential_status == 'student') $visa_status = 'student_visa';
        //             if($row->residential_status == 'bridging-visa') $visa_status = 'bridging_visa';
        //             if($row->residential_status == 'citizen') $visa_status = 'citizen';
        //             if($row->residential_status == 'subclass-485') $visa_status = 'visa_subclass_485';
        //             if($row->residential_status == 'other') $visa_status = 'other';
        //             if($row->residential_status == 'PR' || $row->residential_status == 'permanent-resident') $visa_status = 'permanent_resident';
        //             // $updateEmpDetails->guard_document_type = $visa_status;
        //             if($visa_status == 'student_visa'){
        //                 $weekly_work_hours_limitation = 24;
        //             }else{
        //                 $guard = $getGuardID;
        //                 if($guard->staff_type == 'part_time'){
        //                     $weekly_work_hours_limitation = 36;
        //                 }else{
        //                     $weekly_work_hours_limitation = 38;
        //                 }
        //             }
        //             $document_categories = DB::connection($connectionName2)
        //             ->table('document_categories')->where('document_category', $visa_status)->first();
        //             if($document_categories){
        //                 foreach (json_decode($document_categories->document_type) as $key => $value) {  
        //                     $guardDocs = [];
        //                     $docNum = null;
        //                     $docExpiry = null;
        //                     $docFile = null;
        //                     // $guard_documents = new GuardDocument();
        //                     // $guard_documents->guard_id = $guard;
        //                     // $guard_documents->document_category = ($document_categories->document_category != '' ? $document_categories->document_category : 'other');
        //                     // $guard_documents->document_type = $key;
        //                     // $guard_documents->document_name = $value;
        //                     // $guard_documents->is_deleteable = 0;
        //                     // $guard_documents->c_f_roster = 1;
        //                     if($key == 'security_license'){

        //                         if (strpos($row->security_license_expiration, '/') !== false) {
        //                             $row->security_license_expiration = str_replace('/', '-', $row->security_license_expiration);
        //                             $sl = date('Y-m-d', strtotime($row->security_license_expiration));
        //                         }else{
        //                             $sl = date('Y-m-d', strtotime($row->security_license_expiration));
        //                         }
        //                         $docNum = $row->security_license_number;
        //                         $docExpiry = $sl;
        //                         $docFile = $row->security_license_file;
        //                     }
        //                     if($key == 'citizen_ship'){
        //                         $docNum = $row->citizenship_number;
        //                         $docExpiry = $row->citizenship_expiration;
        //                         $docFile = $row->citizenship_file;
        //                     }
        //                     if($key == 'medicare'){
        //                         $docNum = $row->medicare_number;
        //                         $docExpiry = $row->medicare_expiration;
        //                         $docFile = $row->medicare_file;

        //                     }
        //                     if($key == 'birth_certificate'){
        //                         $docNum = $row->birthcertificate_number;
        //                         $docExpiry = $row->birthcertificate_expiration;
        //                         $docFile = $row->birthcertificate_file;

        //                     }
        //                     if($key == 'driver_license_front'){
        //                         $docNum = $row->driver_license_number;
        //                         $docExpiry = $row->driver_license_expiration;
        //                         $docFile = $row->driver_license_file;

        //                     }
        //                     if($key == 'driver_license_back'){
        //                         $docNum = $row->driver_license_number;
        //                         $docExpiry = $row->driver_license_expiration;
        //                         $docFile = $row->driver_license_file_back;

        //                     }
        //                     if($key == 'passport'){
        //                         $docNum = $row->passport_number;
        //                         $docExpiry = $row->passport_expiration;
        //                         $docFile = $row->passport_file;

        //                     }
        //                     if($key == 'visa'){
        //                         $docNum = $row->visa_number;
        //                         $docExpiry = $row->visa_expiration;
        //                         $docFile = $row->visa_file;

        //                     }
        //                     // $guard_documents = new GuardDocument();
        //                     $guardDocs = [
        //                         'guard_id' => $getGuardID->id,
        //                         'document_category' => ($document_categories->document_category != '' ? $document_categories->document_category : 'other'),
        //                         'document_type' => $key,
        //                         'document_name' => $value,
        //                         'is_deleteable' => 0,
        //                         'c_f_roster' => 1,
        //                         'document_no' => $docNum,
        //                         'document_expire' => $docExpiry,
        //                         'file' => $docFile
        //                     ];
        //                     $guard = DB::connection($connectionName2)
        //                     ->table('guards_documents')->insert($guardDocs);
        //                     // $guard_documents->save();
        //                 }
        //             }
        //             $empworkdetails = [
        //                 'guard_id' => $getGuardID->id,
        //                 'hired_on' => date('Y-m-d H:i:s', $row->timestamp_joined),
        //                 'tfn_file_no' => $row->payroll_tfn_number,
        //                 'superannutation_no' => $row->payroll_superannutation,
        //                 'account_holder' => $row->payroll_bank_name,
        //                 'superannutation_name' => $row->payroll_superannutation_name,
        //                 'abn_no' => $row->payroll_abn_number,
        //                 'bank_name' => $row->payroll_bank_name,
        //                 'bsb' => $row->bsb,
        //                 'bank_account_no' => $row->payroll_bank_account_number,
        //                 'guard_document_type' => $visa_status,
        //                 'weekly_work_hours_limitation' => $weekly_work_hours_limitation
        //             ];
        //             $guard = DB::connection($connectionName2)
        //                     ->table('guard_work_details')->insertGetId($empworkdetails);
        //             // $updateEmpDetails->save();
        //         }

        //         DB::disconnect($connectionName);
        
        // }

        function getGuards247SecurityGroupe(Request $request)  {
        
            $connectionName = 'mysql3';
            $connectionName2 = 'mysql4';
            $results = DB::connection($connectionName)
                ->table('guards')
                ->whereIn('status', ['active', 'inactive'])
                ->get();

                foreach ($results as $key => $row) {
                      $getGuardID =  DB::connection($connectionName2)
                    ->table('guards')->where('email', $row->email)->first();
                if(empty($getGuardID))
                {
                    $data = [
                       'first_name' => $row->first_name,      
                       'middle_name' => $row->middle_name,      
                       'last_name' => $row->last_name,      
                       'email' => $row->email,      
                       'password' => $row->password,      
                       'phone' => $row->phone,      
                       'address' => $row->address,   
                       'coordinates' => $row->coordinates,   
                       'city' => $row->city,   
                       'suburb' => $row->suburb,   
                       'state' => $row->state,      
                       'postal_code' => $row->postal_code,    
                       'dob' => $row->dob,    
                       'gender' => $row->gender,    
                       'emergency_contact_name' => $row->emergency_contact_name,    
                       'emergency_contact_phone' => $row->emergency_contact_phone,    
                       'work_limitation_status' => $row->work_limitation_status,    
                       'weekly_work_hours_limitation' => $row->fortnightly_working_hours,    
                       'is_available' => 'yes',    
                       'is_email_approved' => 'yes',    
                       'staff_type' => 'casual',    
                       'guard_type' => $row->guard_type,    
                       'annual_leave_hours' => $row->annual_leave_hours,
                       'sick_leave_hours' => $row->sick_leave_hours,
                       'payroll_bank_name' => $row->payroll_bank_name,
                       'payroll_bank_account_number' => $row->payroll_bank_account_number,
                       'payroll_abn_number' => $row->payroll_abn_number,
                       'bsb' => $row->bsb,
                    ];
                    $guard = DB::connection($connectionName2)
                    ->table('guards')->insertGetId($data);
                    $getGuardID =  DB::connection($connectionName2)
                    ->table('guards')->find($guard);
                    // $updateEmpDetails = new GuardWorkDetail();
                    // $updateEmpDetails->guard_id = $guard;
                    // $updateEmpDetails->hired_on = date('Y-m-d H:i:s', $row->timestamp_joined);
                    // $updateEmpDetails->tfn_file_no = $row->payroll_tfn_number;
                    // $updateEmpDetails->superannutation_no = $row->payroll_superannutation;
                    // $updateEmpDetails->account_holder = $row->payroll_bank_name;
                    // $updateEmpDetails->superannutation_name = $row->payroll_superannutation_name;
                    // $updateEmpDetails->abn_no = $row->payroll_abn_number;
                    // $updateEmpDetails->bank_name = $row->payroll_bank_name;
                    // $updateEmpDetails->bsb = $row->bsb;
                    // $updateEmpDetails->bank_account_no = $row->payroll_bank_account_number;


                    $visa_status = null;
                    if($row->residential_status == 'student') $visa_status = 'student_visa';
                    if($row->residential_status == 'bridging-visa') $visa_status = 'bridging_visa';
                    if($row->residential_status == 'citizen') $visa_status = 'citizen';
                    if($row->residential_status == 'subclass-485') $visa_status = 'visa_subclass_485';
                    if($row->residential_status == 'other') $visa_status = 'other';
                    if($row->residential_status == 'PR' || $row->residential_status == 'permanent-resident') $visa_status = 'permanent_resident';
                    // $updateEmpDetails->guard_document_type = $visa_status;
                    if($visa_status == 'student_visa'){
                        $weekly_work_hours_limitation = 24;
                    }else{
                        $guard = $getGuardID;
                        if($guard->staff_type == 'part_time'){
                            $weekly_work_hours_limitation = 36;
                        }else{
                            $weekly_work_hours_limitation = 38;
                        }
                    }
                    $document_categories = DB::connection($connectionName2)
                    ->table('document_categories')->where('document_category', $visa_status)->first();
                    if($document_categories){
                        foreach (json_decode($document_categories->document_type) as $key => $value) {  
                            $guardDocs = [];
                            $docNum = null;
                            $docExpiry = null;
                            $docFile = null;
                            // $guard_documents = new GuardDocument();
                            // $guard_documents->guard_id = $guard;
                            // $guard_documents->document_category = ($document_categories->document_category != '' ? $document_categories->document_category : 'other');
                            // $guard_documents->document_type = $key;
                            // $guard_documents->document_name = $value;
                            // $guard_documents->is_deleteable = 0;
                            // $guard_documents->c_f_roster = 1;
                            if($key == 'security_license'){

                                if (strpos($row->security_license_expiration, '/') !== false) {
                                    $row->security_license_expiration = str_replace('/', '-', $row->security_license_expiration);
                                    $sl = date('Y-m-d', strtotime($row->security_license_expiration));
                                }else{
                                    $sl = date('Y-m-d', strtotime($row->security_license_expiration));
                                }
                                $docNum = $row->security_license_number;
                                $docExpiry = $sl;
                                $docFile = $row->security_license_file;
                            }
                            if($key == 'citizen_ship'){
                                $docNum = $row->citizenship_number;
                                $docExpiry = $row->citizenship_expiration;
                                $docFile = $row->citizenship_file;
                            }
                            if($key == 'medicare'){
                                $docNum = $row->medicare_number;
                                $docExpiry = $row->medicare_expiration;
                                $docFile = $row->medicare_file;

                            }
                            if($key == 'birth_certificate'){
                                $docNum = $row->birthcertificate_number;
                                $docExpiry = $row->birthcertificate_expiration;
                                $docFile = $row->birthcertificate_file;

                            }
                            if($key == 'driver_license_front'){
                                $docNum = $row->driver_license_number;
                                $docExpiry = $row->driver_license_expiration;
                                $docFile = $row->driver_license_file;

                            }
                            if($key == 'driver_license_back'){
                                $docNum = $row->driver_license_number;
                                $docExpiry = $row->driver_license_expiration;
                                $docFile = $row->driver_license_file_back;

                            }
                            if($key == 'passport'){
                                $docNum = $row->passport_number;
                                $docExpiry = $row->passport_expiration;
                                $docFile = $row->passport_file;

                            }
                            if($key == 'visa'){
                                $docNum = $row->visa_number;
                                $docExpiry = $row->visa_expiration;
                                $docFile = $row->visa_file;

                            }
                            // $guard_documents = new GuardDocument();
                            $guardDocs = [
                                'guard_id' => $getGuardID->id,
                                'document_category' => ($document_categories->document_category != '' ? $document_categories->document_category : 'other'),
                                'document_type' => $key,
                                'document_name' => $value,
                                'is_deleteable' => 0,
                                'c_f_roster' => 1,
                                'document_no' => $docNum,
                                'document_expire' => $docExpiry,
                                'file' => $docFile
                            ];
                            $guard = DB::connection($connectionName2)
                            ->table('guards_documents')->insert($guardDocs);
                            // $guard_documents->save();
                        }
                    }
                    $empworkdetails = [
                        'guard_id' => $getGuardID->id,
                        'hired_on' => date('Y-m-d H:i:s', $row->timestamp_joined),
                        'tfn_file_no' => $row->payroll_tfn_number,
                        'superannutation_no' => $row->payroll_superannutation,
                        'account_holder' => $row->payroll_bank_name,
                        'superannutation_name' => $row->payroll_superannutation_name,
                        'abn_no' => $row->payroll_abn_number,
                        'bank_name' => $row->payroll_bank_name,
                        'bsb' => $row->bsb,
                        'bank_account_no' => $row->payroll_bank_account_number,
                        'guard_document_type' => $visa_status,
                        'weekly_work_hours_limitation' => $weekly_work_hours_limitation
                    ];
                    $guard = DB::connection($connectionName2)
                            ->table('guard_work_details')->insertGetId($empworkdetails);
                    // $updateEmpDetails->save();
                }
            }

                DB::disconnect($connectionName);
        
        }


        function getPayrate247SecurityGroupe(Request $request) {
            $connectionName = 'mysql3';
            $connectionName2 = 'mysql4';
            $results = DB::connection($connectionName)
                ->table('payrates')
                ->get();
                foreach ($results as $key => $row) {
                    $data = [
                       'title' => $row->title, 
                       'customer_id' => $row->customer_id, 
                       'state' => $row->state, 
                       'level' => $row->level, 
                       'position' => $row->position, 
                       'created_at' => $row->created_at, 
                       'updated_at' => $row->updated_at, 
                       'award_metro_mon_to_fri_day_rate' => $row->eba_metro_weekday_day, 
                       'award_metro_mon_to_fri_night_rate' => $row->eba_metro_weekday_night, 
                       'award_metro_pub_holi_day_rate' => $row->eba_metro_public_holiday, 
                       'def_metro_mon_to_fri_day_rate' => $row->flat_metro_week_day_day, 
                       'def_metro_mon_to_fri_night_rate' => $row->flat_metro_week_day_night, 
                       'def_metro_sat_day_rate' => $row->flat_metro_saturday, 
                       'def_metro_sun_day_rate' => $row->flat_metro_sunday, 
                       'award_metro_sat_day_rate' => $row->eba_metro_saturday_day, 
                       'award_metro_sun_day_rate' => $row->eba_metro_sunday_day,
                       'eba_metro_mon_to_fri_day_rate' => null, 
                       'eba_metro_mon_to_fri_night_rate' => null,
                       'eba_metro_pub_holi_day_rate' => null,
                       'eba_metro_sat_day_rate' => null,
                       'eba_metro_sun_day_rate' => null,

                       'ot_base_rate' => $row->ot_base_rate, 
                       'status' => ($row->archive == 1 ? 'archive' : 'active'), 
                    ];
                    DB::connection($connectionName2)
                    ->table('payrates')->insert($data);
                }

        }

        function getChargerate247SecurityGroupe(Request $request) {
            $connectionName = 'mysql3';
            $connectionName2 = 'mysql4';
            $results = DB::connection($connectionName)
                ->table('charged_rates')
                ->get();
                foreach ($results as $key => $row) {
                    $data = [
                       'title' => $row->title, 
                       'customer_id' => $row->customer_id, 
                       'state' => $row->state, 
                       'level' => $row->level, 
                       'position' => $row->position, 
                       'created_at' => $row->created_at, 
                       'updated_at' => $row->updated_at, 
                       'award_metro_mon_to_fri_day_rate' => $row->eba_metro_weekday_day, //
                       'award_metro_mon_to_fri_night_rate' => $row->eba_metro_weekday_night, //
                       'award_metro_pub_holi_day_rate' => $row->eba_metro_public_holiday,  //
                    
                       'def_metro_mon_to_fri_day_rate' => $row->flat_metro_week_day_day, 
                       'def_metro_mon_to_fri_night_rate' => $row->flat_metro_week_day_night, 
                       'def_metro_sat_day_rate' => $row->flat_metro_saturday, 
                       'def_metro_sun_day_rate' => $row->flat_metro_sunday, 
                       'award_metro_sat_day_rate' => $row->eba_metro_saturday_day, //
                       'award_metro_sun_day_rate' => $row->eba_metro_sunday_day,  // 
                       
                       'eba_metro_mon_to_fri_day_rate' => null, //
                       'eba_metro_mon_to_fri_night_rate' => null, //
                       'eba_metro_pub_holi_day_rate' => null,  //
                       'eba_metro_sat_day_rate' => null, //
                       'eba_metro_sun_day_rate' => null,  // 
                       'status' => ($row->archive == 1 ? 'archive' : 'active'), 
                    ];
                    DB::connection($connectionName2)
                    ->table('charge_rates')->insert($data);
                }

        }

        // function changeEbaToAwardRate(Request $request) {
        //     $connectionName2 = 'mysql4';
        //     $ebaRates = DB::connection($connectionName2)
        //     ->table('payrates')->where('status', 'active')
        //         ->where(function ($query) {
        //             $query->where('eba_metro_mon_to_fri_day_rate', '!=', 0)->orWhereNotNull('eba_metro_mon_to_fri_day_rate')
        //                 ->where('eba_metro_mon_to_fri_night_rate', '!=', 0)->orWhereNotNull('eba_metro_mon_to_fri_night_rate')
        //                 ->where('eba_metro_sat_day_rate', '!=', 0)->orWhereNotNull('eba_metro_sat_day_rate')
        //                 ->where('eba_metro_sun_day_rate', '!=', 0)->orWhereNotNull('eba_metro_sun_day_rate')
        //                 ->where('eba_metro_pub_holi_day_rate', '!=', 0)->orWhereNotNull('eba_metro_pub_holi_day_rate');
        //         })
        //         ->get();
                
        
        //     if ($ebaRates->count() > 0) {
        //         foreach ($ebaRates as $value) {
        //             $record = Payrate::where('status', 'active')->where('id', $value->id)->first();
        //             if($record){
        //                 $record->award_metro_mon_to_fri_day_rate = $value->eba_metro_mon_to_fri_day_rate;
        //                 $record->award_metro_mon_to_fri_night_rate = $value->eba_metro_mon_to_fri_night_rate;
        //                 $record->award_metro_sat_day_rate = $value->eba_metro_sat_day_rate;
        //                 $record->award_metro_sun_day_rate = $value->eba_metro_sun_day_rate;
        //                 $record->award_metro_pub_holi_day_rate = $value->eba_metro_pub_holi_day_rate;
        
        //                 // Set eba rates to 0
        //                 $record->eba_metro_mon_to_fri_day_rate = 0;
        //                 $record->eba_metro_mon_to_fri_night_rate = 0;
        //                 $record->eba_metro_sat_day_rate = 0;
        //                 $record->eba_metro_sun_day_rate = 0;
        //                 $record->eba_metro_pub_holi_day_rate = 0;
            
        //                 $record->update();
        //             }
        //         }
        //         return response()->json(['success' => true, 'message' => 'change payrates']);
        //     }

            
        // }

        function getGuardsIds247SecurityGroupe(Request $request)
        {
            $connectionName = 'mysql3'; //staffingsolution_amg
            $connectionName2 = 'mysql4'; //staffingsolution_scouts_amg
            $results = DB::connection($connectionName)
                ->table('guards')
                ->get();
            foreach ($results as $key => $row) {

                $getGuardID =  DB::connection($connectionName2)
                    ->table('guards')->where('email', $row->email)->first();

                $idsData =  DB::connection($connectionName)
                    ->table('guard_ids')->where('guard_id', $row->id)->get();

                if($getGuardID)
                {
                    foreach ($idsData as $key => $id) {
                        if($id->customer_id && $id->external_id)
                        {
                            $data = [
                                'guard_id' => $getGuardID->id,
                                'customer_id' => $id->customer_id,
                                'external_id' => $id->external_id,
                            ];
                            
                            $guard = DB::connection($connectionName2)
                            ->table('guard_external_ids')->insertGetId($data);
                        }
                    }
                }
            }
            DB::disconnect($connectionName);
        }

       function getGuardscpr(Request $request)
        {
            $connectionName = 'mysql3'; // staffingsolution_amg
            $connectionName2 = 'mysql4'; // staffingsolution_scouts_amg

            $guards = DB::connection($connectionName)
                ->table('guards')
                ->get();

            foreach ($guards as $guard) {
                $matchingGuard = DB::connection($connectionName2)
                    ->table('guards')
                    ->where('email', $guard->email)
                    ->first();

                if ($matchingGuard) {
                    $cprFile = DB::connection($connectionName)
                        ->table('guard_files')
                        ->where('guard_id', $guard->id)
                        ->where('file_type', 'CPR')
                        ->first();

                    if ($cprFile) {
                        DB::connection($connectionName2)
                            ->table('guards_documents')
                            ->where('guard_id', $matchingGuard->id)
                            ->where('document_type', 'cpr')
                            ->whereNull('document_expire')
                            ->whereNull('document_no')
                            ->whereNull('file')
                            ->update([
                                'document_expire' => $cprFile->file_expiry ?? null,
                                'document_no' => $cprFile->document_number ?? null,
                                'file' => $cprFile->file_path ?? null,
                                'updated_at' => now()
                            ]);
                    }
                }
            }

            DB::disconnect($connectionName);
            DB::disconnect($connectionName2);

            return response()->json(['message' => 'CPR documents updated successfully']);
        }


         function getGuardsfirstaid(Request $request)
        {
            $connectionName = 'mysql3'; // staffingsolution_amg
            $connectionName2 = 'mysql4'; // staffingsolution_scouts_amg
            
            $guards = DB::connection($connectionName)
                ->table('guards')
                ->get();

            foreach ($guards as $guard) {
                $matchingGuard = DB::connection($connectionName2)
                    ->table('guards')
                    ->where('email', $guard->email)
                    ->first();

                if ($matchingGuard) {
                    // Update first_aid document
                    DB::connection($connectionName2)
                        ->table('guards_documents')
                        ->where('guard_id', $matchingGuard->id)
                        ->where('document_type', 'first_aid')
                        ->whereNull('document_expire')
                        ->whereNull('document_no')
                        ->whereNull('file')
                        ->update([
                            'document_expire' => $guard->firstaid_license_expiration ?? null,
                            'document_no' => $guard->firstaid_license_number ?? null,
                            'file' => $guard->firstaid_license_file ?? null,
                            'updated_at' => now()
                        ]);

                    // Check if working_with_children document exists
                    $existingWWC = DB::connection($connectionName2)
                        ->table('guards_documents')
                        ->where('guard_id', $matchingGuard->id)
                        ->where('document_type', 'working_with_children')
                        ->exists();

                    if (!$existingWWC && ($guard->working_with_children_expiration || $guard->working_with_children)) {
                        $commonCategory = DB::connection($connectionName2)
                            ->table('guards_documents')
                            ->where('guard_id', $matchingGuard->id)
                            ->select('document_category')
                            ->groupBy('document_category')
                            ->orderByRaw('COUNT(*) DESC')
                            ->value('document_category');


                        $documentCategory = $commonCategory;

                        DB::connection($connectionName2)
                            ->table('guards_documents')
                            ->insert([
                                'guard_id' => $matchingGuard->id,
                                'document_category' => $documentCategory,
                                'document_name' => 'Working with Children',
                                'document_type' => 'working_with_children',
                                'document_expire' => $guard->working_with_children_expiration ?? null,
                                'file' => $guard->working_with_children ?? null,
                                'created_at' => now(),
                                'updated_at' => now()
                            ]);
                    }
                }
            }
            
            DB::disconnect($connectionName);
            DB::disconnect($connectionName2);
            
            return response()->json(['message' => 'Documents processed successfully']);
        }

        function getGuardsWilsonCirtusIds(Request $request)
        {
            $connectionName = 'mysql3'; //staffingsolution_amg
            $connectionName2 = 'mysql4'; //staffingsolution_scouts_amg
            $results = DB::connection($connectionName)
                ->table('guards')
                ->where('status', 'active')
                ->where('guard_status', 'active')
                ->get();
            foreach ($results as $key => $row) {

                $getGuardID =  DB::connection($connectionName2)
                    ->table('guards')->where('email', $row->email)->first();

                $idsData =  DB::connection($connectionName)
                    ->table('guard_ids')->where('guard_id', $row->id)->get();

                if($getGuardID)
                {
                    foreach ($idsData as $key => $id) {
                        if($id->customer_id && $id->external_id)
                        {
                            $data = [
                                'guard_id' => $getGuardID->id,
                                'customer_id' => $id->customer_id,
                                'external_id' => $id->external_id,
                            ];
                            
                            $guard = DB::connection($connectionName2)
                            ->table('guard_external_ids')->insertGetId($data);
                        }
                    }
                }
            }
            DB::disconnect($connectionName);
        }

        public function changeEbaToAwardRate(Request $request)
        {
            $connectionName2 = 'mysql4';
            $ebaRates = DB::connection($connectionName2)
                ->table('charge_rates')->where('status', 'active')
                ->where(function ($query) {
                    $query->where('eba_metro_mon_to_fri_day_rate', '!=', 0)->orWhereNotNull('eba_metro_mon_to_fri_day_rate')
                        ->where('eba_metro_mon_to_fri_night_rate', '!=', 0)->orWhereNotNull('eba_metro_mon_to_fri_night_rate')
                        ->where('eba_metro_sat_day_rate', '!=', 0)->orWhereNotNull('eba_metro_sat_day_rate')
                        ->where('eba_metro_sun_day_rate', '!=', 0)->orWhereNotNull('eba_metro_sun_day_rate')
                        ->where('eba_metro_pub_holi_day_rate', '!=', 0)->orWhereNotNull('eba_metro_pub_holi_day_rate');
                })
                ->get();
        
            if ($ebaRates->count() > 0) {
                foreach ($ebaRates as $value) {
                    DB::connection($connectionName2)
                        ->table('charge_rates')
                        ->where('status', 'active')
                        ->where('id', $value->id)
                        ->update([
                            'award_metro_mon_to_fri_day_rate' => $value->eba_metro_mon_to_fri_day_rate,
                            'award_metro_mon_to_fri_night_rate' => $value->eba_metro_mon_to_fri_night_rate,
                            'award_metro_sat_day_rate' => $value->eba_metro_sat_day_rate,
                            'award_metro_sun_day_rate' => $value->eba_metro_sun_day_rate,
                            'award_metro_pub_holi_day_rate' => $value->eba_metro_pub_holi_day_rate,
                            'eba_metro_mon_to_fri_day_rate' => 0,
                            'eba_metro_mon_to_fri_night_rate' => 0,
                            'eba_metro_sat_day_rate' => 0,
                            'eba_metro_sun_day_rate' => 0,
                            'eba_metro_pub_holi_day_rate' => 0,
                        ]);
                }
                return response()->json(['success' => true, 'message' => 'change payrates']);
            }
        
            return response()->json(['success' => false, 'message' => 'No records found to update']);
        }

        function downloadAndUploadGuardFiles()
        {
            $connectionName = 'mysql3'; // Adjust based on your database setup
            $baseDownloadUrl = "https://amgsystem.com.au/portal/media/svg/files/";
            $uploadUrl = "https://scouts-apis.amgsystem.com.au/guard_documents";
            
            // Fetch active guards
            $guards = DB::table('guards')
                ->where('id', 1)
                ->where('status', 'active')
                ->get();

            $fileFields = [
                'passport_file',
                'visa_file',
                'security_license_file',
                'security_license_file_back',
                'driver_license_file',
                'driver_license_file_back',
                'firstaid_license_file',
                'firearm_license_file',
                'medicare_file',
                'birthcertificate_file',
                'citizenship_file'
            ];

            foreach ($guards as $guard) {
                foreach ($fileFields as $field) {
                    if (!empty($guard->$field)) {
                        $fileName = $guard->$field;
                        $fileUrl = $baseDownloadUrl . $fileName;

                        try {
                            // Step 1: Download File
                            $response = Http::get($fileUrl);
                            if ($response->successful()) {
                                $fileContent = $response->body();
                                echo "Downloaded: $fileName\n";

                                // Step 2: Upload to Remote Server
                                $uploadResponse = Http::attach('file', $fileContent, $fileName)
                                    ->post($uploadUrl);

                                if ($uploadResponse->successful()) {
                                    echo "Uploaded: $fileName\n";
                                } else {
                                    echo "Failed to upload: $fileName\n";
                                }
                            } else {
                                echo "Failed to download: $fileName\n";
                            }
                        } catch (\Exception $e) {
                            echo "Error processing $fileName: " . $e->getMessage() . "\n";
                        }
                    }
                }
            }

            echo "Process completed.";
        }

                
        // Questions
        
        
        public function getquestionnaires(Request $request)
        {
            $connectionName = 'mysql3'; // Source database connection
            $connectionName2 = 'mysql4'; // Target database connection
        
            // Retrieve data from the source database
            $results = DB::connection($connectionName)
                ->table('question_answer_children')
                ->join('question_answers', 'question_answer_children.question_answer_id', '=', 'question_answers.id')
                ->select(
                    'question_answers.title',
                    'question_answers.sub_headings',
                    'question_answer_children.id',
                    'question_answer_children.question',
                    'question_answer_children.type',
                    'question_answer_children.option1',
                    'question_answer_children.option2',
                    'question_answer_children.option3',
                    'question_answer_children.option4',
                    'question_answer_children.answer'
                )
                ->get();
        
            $formattedResults = [];
        
            // Map for transforming the 'type' field
            $typeMapping = [
                'mcqs' => 'MCQs',
                'true_false' => 'True/False',
                'short_answer' => 'Short Question'
            ];
        
            foreach ($results as $result) {
                // Decode the sub_headings JSON into an array
                $subHeadings = json_decode($result->sub_headings, true);
        
                // Prepare the question data
                $questionData = [
                    'file' => null, // Set file to null
                    'type' => $typeMapping[$result->type] ?? $result->type,
                    'answer' => (string)$result->answer,
                    'optiona' => $result->option1,
                    'optionb' => $result->option2,
                    'optionc' => $result->option3,
                    'optiond' => $result->option4,
                    'question' => $result->question,
                ];
        
                // Handle options based on the type of question
                switch ($questionData['type']) {
                    case 'MCQs':
                        // No changes needed for MCQs
                        break;
                    case 'True/False':
                        $questionData['optionc'] = null;
                        $questionData['optiond'] = null;
                        break;
                    case 'Short Question':
                        $questionData['optiona'] = null;
                        $questionData['optionb'] = null;
                        $questionData['optionc'] = null;
                        $questionData['optiond'] = null;
                        break;
                }
        
                if (!isset($formattedResults[$result->title])) {
                    $formattedResults[$result->title] = [
                        'id' => $result->id,
                        'title' => $result->title,
                        'sub_headings' => $subHeadings,
                        'questions' => [],
                    ];
                }
        
                $formattedResults[$result->title]['questions'][] = $questionData;
            }
        
            // Insert the formatted results into the target database
            foreach ($formattedResults as $formattedResult) {
                DB::connection($connectionName2)->table('questionnaires')->insert([
                    'title' => $formattedResult['title'],
                    'questionnaire' => json_encode($formattedResult['questions'], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES),
                    'sub_heading' => json_encode($formattedResult['sub_headings'], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES),
                    'admin_id' => "261",
                ]);
            }
        
            return response()->json([
                'status' => true,
                'message' => 'Data inserted successfully'
                // 'data' => array_values($formattedResults)
            ]);
        }

        public function addworkwithchild(Request $request)
        {
        $guards = DB::table('guards')->get();

        foreach ($guards as $guard) {
            $existingDocs = DB::table('guards_documents')
                ->where('guard_id', $guard->id)
                ->pluck('document_type')
                ->toArray();

            if (!in_array('working_with_children', $existingDocs)) {
                $documentCategory = DB::table('guards_documents')
                    ->where('guard_id', $guard->id)
                    ->value('document_category');

                DB::table('guards_documents')->insert([
                    'guard_id' => $guard->id,
                    'document_category' => $documentCategory,
                    'document_name' => 'Working with Children',
                    'document_type' => 'working_with_children',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        return response()->json(['status' => 'success', 'message' => 'Missing documents uploaded.']);
        }


       public function uploadmissingdoc(Request $request)
        {
            $guards = DB::table('guards')->get();

            foreach ($guards as $guard) {
                $existingDocs = DB::table('guards_documents')
                    ->where('guard_id', $guard->id)
                    ->pluck('document_type')
                    ->toArray();

                $documentCategory = DB::table('guards_documents')
                    ->where('guard_id', $guard->id)
                    ->value('document_category');

                $documentsToInsert = [];

                if (!in_array('cpr', $existingDocs)) {
                    $documentsToInsert[] = [
                        'guard_id' => $guard->id,
                        'document_category' => $documentCategory,
                        'document_name' => 'CPR',
                        'document_type' => 'cpr',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }

                if (!in_array('first_aid', $existingDocs)) {
                    $documentsToInsert[] = [
                        'guard_id' => $guard->id,
                        'document_category' => $documentCategory,
                        'document_name' => 'First Aid',
                        'document_type' => 'first_aid',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }

                if (!empty($documentsToInsert)) {
                    DB::table('guards_documents')->insert($documentsToInsert);
                }
            }

            return response()->json(['status' => 'success', 'message' => 'Missing documents uploaded.']);
        }

        public function updatedocoldtonew(Request $request)
        {
            $guards = DB::table('guards')->get();

            foreach ($guards as $guard) {
                $firstAidCert = DB::table('guards_documents')
                    ->where('guard_id', $guard->id)
                    ->where('document_name', 'First Aid Certificate')
                    ->first();

                $cprCert = DB::table('guards_documents')
                    ->where('guard_id', $guard->id)
                    ->where('document_name', 'CPR Certificate')
                    ->first();

                if ($firstAidCert) {
                    DB::table('guards_documents')
                        ->where('guard_id', $guard->id)
                        ->where('document_type', 'first_aid')
                        ->update([
                            'document_expire' => $firstAidCert->document_expire,
                            'document_no' => $firstAidCert->document_no,
                            'file' => $firstAidCert->file,
                        ]);

                    DB::table('guards_documents')
                        ->where('id', $firstAidCert->id)
                        ->delete();
                }

                // Update CPR record (if exists)
                if ($cprCert) {
                    DB::table('guards_documents')
                        ->where('guard_id', $guard->id)
                        ->where('document_type', 'cpr')
                        ->update([
                            'document_expire' => $cprCert->document_expire,
                            'document_no' => $cprCert->document_no,
                            'file' => $cprCert->file,
                        ]);

                    DB::table('guards_documents')
                        ->where('id', $cprCert->id)
                        ->delete();
                }
            }
        }

       public function assignInductionsToActiveGuards()
        {
            $guards = Guard::where('guard_status', 'active')->get();
            $inductions = Questionnaire::all();

            $now = Carbon::now();
            $inductionHistoryData = [];
            $guardQuestionnaireDetailsData = [];

            foreach ($guards as $guard) {
                foreach ($inductions as $induction) {
                    $inductionHistoryData[] = [
                        'guard_id' => $guard->id,
                        'induction_id' => $induction->id,
                        'state' => $guard->state ?? null,
                        'read_status' => 0,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];

                    $guardQuestionnaireDetailsData[] = [
                        'guard_id' => $guard->id,
                        'questionnaire_id' => $induction->id,
                        'marks' => 0,
                        'certificate_path' => null,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }
            }

            DB::table('induction_history')->insert($inductionHistoryData);
            DB::table('guard_questionnaire_details')->insert($guardQuestionnaireDetailsData);

            return response()->json(['status' => 'success', 'message' => 'Inductions and questionnaire details assigned to all active guards.']);
        }

        public function assignInductionsToActiveGuards1()
        {
            $guards = Guard::where('guard_status', 'active')->get();
            $inductions = Questionnaire::all();

            $now = Carbon::now();
            $inductionHistoryData = [];
            $guardQuestionnaireDetailsData = [];

            foreach ($guards as $guard) {
                foreach ($inductions as $induction) {
                    // Check if induction already assigned
                    $exists = DB::table('guard_questionnaire_details')
                        ->where('guard_id', $guard->id)
                        ->where('questionnaire_id', $induction->id)
                        ->exists();

                    if (!$exists) {
                        $inductionHistoryData[] = [
                            'guard_id' => $guard->id,
                            'induction_id' => $induction->id,
                            'state' => $guard->state ?? null,
                            'read_status' => 0,
                            'created_at' => $now,
                            'updated_at' => $now,
                        ];

                        $guardQuestionnaireDetailsData[] = [
                            'guard_id' => $guard->id,
                            'questionnaire_id' => $induction->id,
                            'marks' => 0,
                            'certificate_path' => null,
                            'created_at' => $now,
                            'updated_at' => $now,
                        ];
                    }
                }
            }

            if (!empty($inductionHistoryData)) {
                DB::table('induction_history')->insert($inductionHistoryData);
            }

            if (!empty($guardQuestionnaireDetailsData)) {
                DB::table('guard_questionnaire_details')->insert($guardQuestionnaireDetailsData);
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Inductions assigned to active guards who did not have them before.'
            ]);
        }


        public function fixMissingInductionHistory()
        {
            $now = Carbon::now();

            // Get all entries in guard_questionnaire_details
            $questionnaireDetails = DB::table('guard_questionnaire_details')
                ->join('guards', 'guards.id', '=', 'guard_questionnaire_details.guard_id')
                ->select(
                    'guard_questionnaire_details.guard_id',
                    'guard_questionnaire_details.questionnaire_id as induction_id',
                    'guards.state',
                    'guard_questionnaire_details.marks'
                )
                ->get();

            $inductionHistoryData = [];

            foreach ($questionnaireDetails as $detail) {
                // Check if the corresponding induction_history record exists
                $exists = DB::table('induction_history')
                    ->where('guard_id', $detail->guard_id)
                    ->where('induction_id', $detail->induction_id)
                    ->exists();

                if ($exists) {
                    if($detail->marks > 0){
                        $status = 1;
                    }else{
                        $status = 0;
                    }
                    
                    $inductionHistoryData[] = [
                        'guard_id' => $detail->guard_id,
                        'induction_id' => $detail->induction_id,
                        'state' => $detail->state ?? null,
                        'read_status' => $status,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }
            }

            // Insert missing records
            if (!empty($inductionHistoryData)) {
                DB::table('induction_history')->update($inductionHistoryData);
            }

            return response()->json([
                'status' => 'success',
                'inserted_rows' => count($inductionHistoryData),
                'message' => 'Missing induction history entries have been fixed.'
            ]);
        }

            public function fixMissingInductionHistory1()
        {
            $now = Carbon::now();
            $questionnaireDetails = DB::table('guard_questionnaire_details')
                ->join('guards', 'guards.id', '=', 'guard_questionnaire_details.guard_id')
                ->select(
                    'guard_questionnaire_details.guard_id',
                    'guard_questionnaire_details.questionnaire_id as induction_id',
                    'guards.state',
                    'guard_questionnaire_details.marks'
                )
                ->get();
            $updatedCount = 0;
            foreach ($questionnaireDetails as $detail) {
                if ($detail->marks > 0) {
                    $updated = DB::table('induction_history')
                        ->where('guard_id', $detail->guard_id)
                        ->where('induction_id', $detail->induction_id)
                        ->update([
                            'read_status' => 1,
                            'updated_at' => $now,
                        ]);
                    if ($updated) {
                        $updatedCount++;
                    }
                }
            }
            return response()->json([
                'status' => 'success',
                'updated_rows' => $updatedCount,
                'message' => 'Induction history read_status updated where marks > 0.'
            ]);
        }

        public function assignInductionsToRemaningGuards()
        {
            $now = Carbon::now();

            $alreadyInductedGuardIds = DB::table('induction_history')
                ->distinct()
                ->pluck('guard_id')
                ->toArray();

            $guards = Guard::whereNotIn('id', $alreadyInductedGuardIds)
                ->where('guard_status', '=', 'deleted')
                ->get();
                // return $guards;

            if ($guards->isEmpty()) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'No new active guards to assign inductions.'
                ]);
            }

            // Step 3: get all inductions
            $inductions = Questionnaire::all();

            $inductionHistoryData = [];
            $guardQuestionnaireDetailsData = [];

            foreach ($guards as $guard) {
                foreach ($inductions as $induction) {
                    $inductionHistoryData[] = [
                        'guard_id' => $guard->id,
                        'induction_id' => $induction->id,
                        'state' => $guard->state ?? null,
                        'read_status' => 0,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];

                    $guardQuestionnaireDetailsData[] = [
                        'guard_id' => $guard->id,
                        'questionnaire_id' => $induction->id,
                        'marks' => 0,
                        'certificate_path' => null,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }
            }

            // Step 4: insert only if data exists
            if (!empty($inductionHistoryData)) {
                DB::table('induction_history')->insert($inductionHistoryData);
            }

            if (!empty($guardQuestionnaireDetailsData)) {
                DB::table('guard_questionnaire_details')->insert($guardQuestionnaireDetailsData);
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Inductions and questionnaire details assigned to new active guards.'
            ]);
        }

}
