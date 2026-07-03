<?php

use App\Models\ChargeRate_History;
use App\Models\crm\SalePersonModel;
use App\Models\Customer;
use App\Models\Guard;
use App\Models\GuardDocument;
use App\Models\DocumentCategory;
use App\Models\GuardLeave;
use App\Models\GuardWorkDetail;
use Illuminate\Support\Facades\DB;
use App\Models\JobNewRoster;
use App\Models\JobRoster;
use App\Models\JobRosterAction;
use App\Models\Logging;
use App\Models\LoginActivities;
use App\Models\PayrateHistory;
use App\Models\portal\PortalSettings;
use App\Models\RosterCompleteActivity;
use App\Models\RunSheet;
use App\Models\RunSheetJobRoster;
use App\Models\RunSheetRoster;
use App\Models\Site;
use App\Models\User;
use Carbon\Carbon;
use Haruncpi\LaravelIdGenerator\IdGenerator;
use Illuminate\Support\Facades\Artisan;
use ClickSend\Configuration;
use ClickSend\Api\SMSApi;
use GuzzleHttp\Client;
use Illuminate\Support\Str;

function filterObjectAndGet($object,$attribute){
    return !empty($object) && isset($object->$attribute) ? $object->$attribute : null;
}
function generateOTP(){
    return (string)rand(1000,9999);
}
function numberNotNull($value){
    if(($value == 0 && $value != null && $value != '')  && !empty($value)){
        return true;
    }
    return false;
}

/**
 * @param $table
 * @param $field
 * @param $length
 * @param $prefix
 * @return string
 * @throws Exception
 */
// function generateId($table, $field, $length, $prefix)
// {
//     return IdGenerator::generate(['table' => $table, 'field' => $field, 'length' => $length, 'prefix' => $prefix]);
// }

function setUserToken($user){
    $tokenResult = $user->createToken('authToken')->plainTextToken;
    $user->access_token = $tokenResult;
    $user->token_type = "Bearer";
    return $user;
}

function isDriver($request){
    return $request->input('type') == 'driver';
}
function getStatusBooleanValue($value,$status = 'open'){
    return $value == $status ? 1 : 0;
}

function filterArrayStartWith($arr_main_array,$start_with,$delimeter){
    foreach($arr_main_array as $key => $value){
        $exp_key = explode($delimeter, $key);
        if($exp_key[0] == $start_with){
            $arr_result[$key] = $value;
        }
    }

    if(isset($arr_result)){
        return $arr_result;
    }
    return [];
}
function filterArrayStartWithTeacher($arr_main_array,$start_with,$delimeter){
    foreach($arr_main_array as $key => $value){
        $exp_key = explode($delimeter, $key);
        if($exp_key[0] == $start_with){
            $arr_result[$key] = $value;
        }
    }

    if(isset($arr_result)){
        return $arr_result;
    }
    return [];
}

function dispatchAllCommand(){ 
    Artisan::call('view:clear');
}


//247 staffing Solutions...

function returnImgPath($type, $image)
{
        if(!empty($image)){
            return url($type).'/'. $image;
        }else{
            return null;
        }     
}

function returnImgPathCheck($folder, $filename) {
    $baseUrl = 'https://app-apis.amgsystem.com.au/';
    return $baseUrl . $folder . '/' . $filename;
}

function returnImgPathpayslip($folder, $filename) {
    $baseUrl = 'https://scouts-apis.amgsystem.com.au/';
    return $baseUrl . $folder . '/' . $filename;
}

function upload_img($key, $folder = 'uploads')
{
    $name = '';
    $public_path = public_path();
    $public_path = str_replace('247StaffingSolution/public/', '', $public_path);
    $path = $public_path . $folder;
    $file_name = time() . '.jpg';
    $result = base64_to_jpeg($key, $path.$file_name);
    $name = $file_name;
    if ($result) {
        return $name;
    }else {
        return '';
    }
}


function fileUpload($file, $folder)
    {
            $rnd = Str::random(16);
            $public_path1 = public_path();
            $path = $public_path1.'/'. $folder;
            $name = $rnd.'_'.$file->getClientOriginalName();
            $file->move($path, $name);
            return $name;
    }



function base64_to_jpeg($data, $output_file)
{
    $data = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $data));
    // file_put_contents($output_file, $data);
    if(file_put_contents($output_file, $data)){
        return true;
    }else{
        return false;
    }
}
function uploader_base64($file, $folder = 'uploads') {
    try {
        $public_path =  rtrim(app()->basePath('public/'), '');
        $public_path = str_replace('portal/public', '', $public_path);
        $public_path = str_replace('apis/public', '', $public_path);
        $public_path = str_replace('appapi.247staffingsolutions.com.au/', 'apis.247staffingsolutions.com.au/public', $public_path);
        $destinationPath = $public_path.$folder.'/';
        $newName = Str::random(25);
        $fileName = $newName . '.jpg';
        $file = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $file));
        file_put_contents($destinationPath.$fileName, $file);

        return $fileName;
    } catch (Exception $e) {
        echo $e->getMessage();
    }
}




// function upload_file($key,$folder)
// {
//     $name = '';
//     $public_path = public_path();
//     $public_path = str_replace('247StaffingSolution/public/', '', $public_path);
//     $path = $public_path . $folder;
//     $file_name = time() . '.pdf';
//     //$result = base64_to_jpeg($key, $path.$file_name);
//     $pdf->save($path . '/' . $fileName);
//     $name = $file_name;
//     if ($result) {
//         return $name;
//     }else {
//         return '';
//     } 
// }


 function logging($action,$record_id,$updated_by,$action_at){
         $logging = new Logging();
         $logging->action = $action;
         $logging->record_id = $record_id; 
         $logging->updated_by = $updated_by;
         $logging->action_at = $action_at;
         $logging->save();
}

function jobRosterActions($action_by, $action_type, $roster_id, $action_on, $old_data = null, $updated_column = null, $reason = null,)
{
         $jobRosterActions = new JobRosterAction();
         $jobRosterActions->action_by = $action_by;
         $jobRosterActions->action_type = $action_type; 
         $jobRosterActions->roster_id = $roster_id;
         $jobRosterActions->reason = $reason;
         $jobRosterActions->data = json_encode($old_data);
         $jobRosterActions->updated_colums = json_encode($updated_column);
         $jobRosterActions->action_on = $action_on;
         $jobRosterActions->save();
}

function payrateHistory($payrate_id, $data, $start_from, $start_to, $change_by)
{
         $payrateHistory = new PayrateHistory();
         $payrateHistory->payrate_id = $payrate_id;
         $payrateHistory->data = json_encode($data);
         $payrateHistory->effective_from = $start_from;
         $payrateHistory->effective_to = $start_to;
         $payrateHistory->changed_by = $change_by;
         $payrateHistory->save();
}

function chargerateHistory($chargerate_id, $data, $start_from, $start_to, $change_by)
{
         $payrateHistory = new ChargeRate_History();
         $payrateHistory->chargerate_id = $chargerate_id;
         $payrateHistory->data = json_encode($data);
         $payrateHistory->effective_from = $start_from;
         $payrateHistory->effective_to = $start_to;
         $payrateHistory->changed_by = $change_by;
         $payrateHistory->save();
}

function shiftCompleteActivity($roster_id, $activity, $type, $record_id, $activity_time, $activity_by)
{
         $shiftCompleteActivity = new RosterCompleteActivity();
         $shiftCompleteActivity->roster_id = $roster_id;
         $shiftCompleteActivity->activity = $activity;
         $shiftCompleteActivity->type = $type;
         $shiftCompleteActivity->record_id = $record_id;
         $shiftCompleteActivity->activity_time = $activity_time;
         $shiftCompleteActivity->activity_by = $activity_by;
         $shiftCompleteActivity->save();
}

function adminLoginActivites($user_id, $name, $action, $type){
    $adminAct = new LoginActivities();
    $adminAct->user_id = $user_id;
    $adminAct->name = $name;
    $adminAct->action = $action;
    $adminAct->type = $type;
    $adminAct->save();
}

 function getJobData($SitId)
    {
        $data = Site::where(['id' => $SitId])->first();
        return $data;
    }
 function getSingleGuard($guardId){
        $data = Guard::where(['id' => $guardId])->first();
        return $data;
    }


    function fetchCustoemrs() {
        $customers = Customer::pluck('id');
        if($customers){
            return $customers; 
        }else{
            return null;
        }
        
    }

    //  function checkGuardSecurityLicenceDocuments($guard, $bypass = true)
    // {
    //     // $status = $this->date_convert($guard->security_license_expiration);
    //     // return $status;
    //     $today = date("m/d/Y");
    //     $today_time = strtotime($today);
    //     $passport_expiration = strtotime(date_convert($guard->passport_expiration));
    //     $visa_expiration = strtotime(date_convert($guard->visa_expiration));
    //     $security_license_expiration = strtotime(date_convert($guard->security_license_expiration));
    //     $driver_license_expiration = strtotime(date_convert($guard->driver_license_expiration));
    //     $firstaid_license_expiration = strtotime(date_convert($guard->firstaid_license_expiration));
    //     $firearm_license_expiration = strtotime(date_convert($guard->firearm_license_expiration));

    //     if($security_license_expiration && ($security_license_expiration < $today_time) && $guard->license_bypass == 0) {
    //         $status = "Security License Expired";
    //     }else {
    //         $status = 'Active';
    //     }
    //     return $status;
    // }

    // function date_convert($date_format)
    // {
    //     if (count(explode('-', $date_format)) > 0) {
    //         return $date_format;
    //     } else {
    //         list($date, $month, $year) = sscanf($date_format, '%d/%d/%d');
    //         if ($month < 9) {
    //             $month = '0' . $month;
    //         }
    //         return $month . '/' . $date . '/' . $year;
    //     }
    // }

    function checkGuardDocuments($guard_id, $bypass = true)
    {
        $status = '';
        $guard = GuardWorkDetail::where('guard_id', $guard_id)->first();

        if(empty($guard->guard_document_type)){
            $status = "Please First Add Your Residential Status!";
            return $status;
        }

        $today = date("Y/m/d");
        $today_time = strtotime($today);
        
        $guard = Guard::where('id', $guard_id)->with('guardDocuments')->first();

    //     //dd($guard);
    //     // $status = "Visa Expired!";
    //     // $t = strtotime($value->document_expire);
    //     // $t = date('m/d/Y', $t);
    //     // dd($t);

    foreach ($guard->guardDocuments as $key => $value){
        if($value->c_f_roster ==  1){
            if($value->document_category == 'citizen'){
                if($value->document_type == 'security_license' && ($value->document_expire == '' || $value->document_expire == null || $today_time > strtotime($value->document_expire))){
                    $status = "Security License Expired!";
                    break;
            }else{
                $status = 'active';
                return $status;
            }
        }else{
    
            if($value->document_type == 'visa' && ($value->document_expire == '' || $value->document_expire == null || $today_time > strtotime($value->document_expire))){
                $status = "Visa Expired!";
                break;
            }elseif($value->document_type == 'passport' && ($value->document_expire == '' || $value->document_expire == null || $today_time > strtotime($value->document_expire))){
                $status = "Passport Expired!";
                break;
            }elseif($value->document_type == 'security_license' && ($value->document_expire == '' || $value->document_expire == null || $today_time > strtotime($value->document_expire))){
                $status = "Security License Expired!";
                break;
            }else{
                $status = 'active'; 
            }
        }

        }else{
            $status = 'active';
        }

        }
        return $status;
    }

    function checkGuardDocumentStatus($guard_id, $bypass = true)
    {
        $status = '';
        $guard = GuardWorkDetail::where('guard_id', $guard_id)->first();

        if(empty($guard->guard_document_type)){
            $status = "Please First Add Your Residential Status!";
            return $status;
        }

        $today = date("Y/m/d");
        $today_time = strtotime($today);
        
        $guard = Guard::where('id', $guard_id)->with('guardDocuments')->first();

    //     //dd($guard);
    //     // $status = "Visa Expired!";
    //     // $t = strtotime($value->document_expire);
    //     // $t = date('m/d/Y', $t);
    //     // dd($t);

    foreach ($guard->guardDocuments as $key => $value){
        // if($value->c_f_roster ==  1){
            if($value->document_category == 'citizen'){
                if($value->document_type == 'security_license' && ($value->document_expire == '' || $value->document_expire == null)){
                    if($value->document_expire != 'current, pending renewal' && $today_time > strtotime($value->document_expire)){
                        $status = "Security License Expired!";
                        break;
                    }
            }else{
                $status = 'active';
                return $status;
            }
        }else{
    
            if($value->document_type == 'visa' && ($value->document_expire == '' || $value->document_expire == null || $today_time > strtotime($value->document_expire))){
                $status = "Visa Expired!";
                break;
            }elseif($value->document_type == 'passport' && ($value->document_expire == '' || $value->document_expire == null || $today_time > strtotime($value->document_expire))){
                $status = "Passport Expired!";
                break;
            }elseif($value->document_type == 'security_license' && ($value->document_expire == '' || $value->document_expire == null)){
                if($value->document_expire != 'current, pending renewal' && $today_time > strtotime($value->document_expire)){
                    $status = "Security License Expired!";
                    break;
                }
            }else{
                $status = 'active'; 
            }
        }

        // }else{
        //     $status = 'active';
        // }

        }
        return $status;
            
    }

    function checkGuardReqDocumentStatus($guard_id, $bypass = true)
    {
        $status = '';
        $guard = GuardWorkDetail::where('guard_id', $guard_id)->first();

        if (empty($guard->guard_document_type)) {
            return "Please First Add Your Residential Status!";
        }else{
        $guard_category = DocumentCategory::where('document_category', $guard->guard_document_type)->first();
        }
        $complianceArray = $guard_category && $guard_category->document_compliance !== null 
            ? json_decode(trim($guard_category->document_compliance, '"'), true) 
            : [];

        $guard = Guard::where('id', $guard_id)->with('guardDocuments')->first();

        if ($guard->guard_admin_approval == 1) {
            return 'active';
        }

        $today = date("Y/m/d");
        $today_time = strtotime($today);

        foreach ($guard->guardDocuments as $document) {
            $is_required = isset($complianceArray[$document->document_type]) && $complianceArray[$document->document_type] === true;

            if ($is_required) {
                if ($document->document_expire === '' || $document->document_expire === null || $today_time > strtotime($document->document_expire)) {
                    $status = ucfirst($document->document_name) . " Expired!";
                    return $status;
                }
            }
        }

        if ($guard->guard_admin_approval == 1) {
            return 'active';
        } else {
            return "Documents are complete but waiting for admin approval.";
        }
    }



    // elseif ($value->document_type == 'driver_license_fornt' && ($value->document_expire == '' || $value->document_expire == null || $today_time > strtotime($value->document_expire))){
    //     $status = "Driver License Expired!";
    //     break;
    // }elseif($value->document_type == 'vaccination' && ($value->document_expire == '' || $value->document_expire == null || $today_time > strtotime($value->document_expire))){
       
    //     $status = "Vaccination Expired!";
    //     break;
    // }elseif($value->document_type == 'driver_license_back' && ( $value->document_expire == '' || $value->document_expire == null || $today_time > strtotime($value->document_expire))){

    //     $status = "Driver License Expired!";
    //     break;
    // }elseif($value->document_type == 'citizen_ship' && ($value->document_expire == '' || $value->document_expire == null || $today_time > strtotime($value->document_expire))){
    //     $status = "citizen Ship Expired!";
    //     break;
    // }elseif($value->document_type == 'medicare' && ($value->document_expire == '' || $value->document_expire == null || $today_time > strtotime($value->document_expire))){
    //     $status = "Medicare Expired!";
    //     break;
    // }
    // elseif($value->document_type == 'birth_certificate' && ($value->document_expire == '' || $value->document_expire == null || $today_time > strtotime($value->document_expire))){
    //     $status = "birth Certificate Expired!";
    //     break;
    // }



    function timeStampToAus($timestamp)
    {
        $dateTime = new DateTime("@$timestamp");
        $dateTime->setTimezone(new DateTimeZone('Australia/Sydney'));
        $australianFormat = $dateTime->format('d-m-Y H:i');
        return $australianFormat;
    }

    function usaToAus($date)
    {
        if ($date == '0000-00-00' || empty($date)) {
            return null;
        }
        
        $date1 = str_replace('-', '/', $date);
        $usformat = date("d-m-Y", strtotime($date1));
        
        return $usformat;
    }

    function formatedDate($date) {
        if ($date == '0000-00-00' || empty($date) || $date == '1970-01-01') {
            return null;
        }
        
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            $dateObj = DateTime::createFromFormat('Y-m-d', $date);
        } 
        elseif (preg_match('/^\d{2}-\d{2}-\d{4}$/', $date)) {
            $dateObj = DateTime::createFromFormat('d-m-Y', $date);
        }
        else {
            return null;
        }
        
        return $dateObj ? $dateObj->format('d-m-Y') : null;
    }   
    
    function dbFormate($formate)
    {
     $formate = str_replace('-', '/', $formate);
     $usfromat = date("Y-m-d", strtotime(($formate)));
     return $usfromat;
    }
    function date_convert($date_format)
 {
    $date_split = explode('/', $date_format);
    if (sizeof($date_split) > 0) {
        list($date, $month, $year) = sscanf($date_format, '%d/%d/%d');
        if ($month < 9) {
            $month = '0' . $month;
        }
        return $month . '/' . $date . '/' . $year;
    } else {
        return $date_format;
    }
}

    function dbFormateDateTime($formate)
    {
     $formate = str_replace('-', '/', $formate);
     $usfromat = date("Y-m-d H:i", strtotime(($formate)));
     return $usfromat;
    }
    function dbFormateDateTimeEnd($formate)
    {
     $formate = str_replace('-', '/', $formate);
     $usfromat = date("Y-m-d 23:59", strtotime(($formate)));
     return $usfromat;
    }
    function dbFormateDateTimeStart($formate)
    {
     $formate = str_replace('-', '/', $formate);
     $usfromat = date("Y-m-d 00:00", strtotime(($formate)));
     return $usfromat;
    }

    function usaToAusDateTime($date)
    {
        $date1 = str_replace('-', '/', $date);
        $usfromat = date("d-m-Y H:i", strtotime(($date1)));
        return $usfromat;
    }

    function usaToAusTime($date)
    {
        $date1 = str_replace('-', '/', $date);
        $usfromat = date("H:i", strtotime(($date1)));
        return $usfromat;
    }
    
    function dateFormat($date)
    {
       $date1 = str_replace('-', '/', $date);
        $usfromat = date("D , d/m", strtotime(($date1)));
        return $usfromat; 
    }

    function stringDateFormate($date){
    // Parse the US date-time string into a Carbon instance
        $carbonDateTime = Carbon::parse($date);
        // Format the Carbon instance into the desired format
        $formattedDateTime = $carbonDateTime->format('D, j F Y');
        return $formattedDateTime;
    }

    function timeFormat($date)
    {
       $date1 = str_replace('-', '/', $date);
       $usfromat = date("H:i", strtotime(($date1)));
       return $usfromat; 
    }

    function checkGuardShiftTiming($start, $end, $guard_id, $roster_id)
    {
        $start_time = dbFormateDateTime($start);
        $end_time   = dbFormateDateTime($end);
        
        $guardTiming = JobRoster::where(function ($que) use ($start_time, $end_time, $roster_id) {
        $r_id = $roster_id;
        $que->orWhere(function ($que1) use ($start_time, $end_time, $r_id) {
            // temp start is grater then actual start and less then actual end
            $que1->where('start', '<=', $start_time)->where('end', '>', $start_time);
            //$que1->where('roster_id', $r_id);
        });
        $que->orWhere(function ($que1) use ($start_time, $end_time, $roster_id) {
            $r_id = $roster_id;
            // temp end is b/w actual start and end..
            $que1->where('start', '<=', $end_time)->where('end', '>=', $end_time);
            //$que1->where('roster_id', $r_id);
        });
        $que->orWhere(function ($que1) use ($start_time, $end_time, $roster_id) {
            $r_id = $roster_id;
            // is any shift lie b/w temp shift
            $que1->where('start', '>=', $start_time)->where('end', '<=', $end_time);
            //$que1->where('roster_id', $r_id);
        });
        })->where('guard_id', $guard_id)->first();
        //dd($guardTiming);
        if(!empty($guardTiming)){
            return ['status'=>true,'start'=> $guardTiming->start, 'end' =>$guardTiming->end, 'conf' => 'conflict'];
        }else{
            return ['status'=>'false']; 
        }
    }
    function checkGuardShiftTimingRS($start, $end, $guard_id, $roster_id)
    {
        $start_time = dbFormateDateTime($start);
        $end_time   = dbFormateDateTime($end);
        
        $guardTiming = RunSheetJobRoster::where(function ($que) use ($start_time, $end_time, $roster_id) {
        $r_id = $roster_id;
        $que->orWhere(function ($que1) use ($start_time, $end_time, $r_id) {
            // temp start is grater then actual start and less then actual end
            $que1->where('start', '<=', $start_time)->where('end', '>', $start_time);
        });
        $que->orWhere(function ($que1) use ($start_time, $end_time, $roster_id) {
            $r_id = $roster_id;
            // temp end is b/w actual start and end..
            $que1->where('start', '<=', $end_time)->where('end', '>=', $end_time);
        });
        $que->orWhere(function ($que1) use ($start_time, $end_time, $roster_id) {
            $r_id = $roster_id;
            // is any shift lie b/w temp shift
            $que1->where('start', '>=', $start_time)->where('end', '<=', $end_time);
        });
        })->where('guard_id', $guard_id)->first();
        if(!empty($guardTiming)){
            return ['status'=>true,'start'=> $guardTiming->start, 'end' =>$guardTiming->end, 'conf' => 'conflict'];
        }else{
            return ['status'=>'false']; 
        }
    }

   function checkGuardShiftTimingUpdate($start, $end, $guard_id, $roster_id)
   {
       $start_time = dbFormateDateTime($start);
       $end_time   = dbFormateDateTime($end);
       $guardTiming = JobRoster::where(function ($que) use ($start_time, $end_time, $roster_id) {
       $r_id = $roster_id;
       $que->orWhere(function ($que1) use ($start_time, $end_time, $r_id) {
           // temp start is grater then actual start and less then actual end
           $que1->where('start', '<=', $start_time)->where('end', '>', $start_time);
           //$que1->where('roster_id', $r_id);
       });
       $que->orWhere(function ($que1) use ($start_time, $end_time, $r_id) {
           // temp end is b/w actual start and end..
           $que1->where('start', '<=', $end_time)->where('end', '>=', $end_time);
           //$que1->where('roster_id', $r_id);
       });
       $que->orWhere(function ($que1) use ($start_time, $end_time, $r_id) {
           // is any shift lie b/w temp shift
           $que1->where('start', '>=', $start_time)->where('end', '<=', $end_time);
           //$que1->where('roster_id', $r_id);
       });
   })->where('guard_id', $guard_id)->where('start', '!=', $start_time)->where('end', '!=', $end )->first();
   //  dd($guardTiming);
       if(!empty($guardTiming)){
           return ['start'=> $guardTiming->start, 'end' =>$guardTiming->end, 'conf' => 'conflict', 'roster' => $guardTiming->roster_id];
       }else{
           return 0; 
       }
   }
   function checkGuardShiftTimingRSUpdate($start, $end, $guard_id, $run_sheet_id)
   {
       $start_time = dbFormateDateTime($start);
       $end_time   = dbFormateDateTime($end);
       $guardTiming = RunSheetJobRoster::where(function ($que) use ($start_time, $end_time, $run_sheet_id) {
       $r_id = $run_sheet_id;
       $que->orWhere(function ($que1) use ($start_time, $end_time, $r_id) {
           // temp start is grater then actual start and less then actual end
           $que1->where('start', '<=', $start_time)->where('end', '>', $start_time);
           //$que1->where('roster_id', $r_id);
       });
       $que->orWhere(function ($que1) use ($start_time, $end_time, $r_id) {
           // temp end is b/w actual start and end..
           $que1->where('start', '<=', $end_time)->where('end', '>=', $end_time);
           //$que1->where('roster_id', $r_id);
       });
       $que->orWhere(function ($que1) use ($start_time, $end_time, $r_id) {
           // is any shift lie b/w temp shift
           $que1->where('start', '>=', $start_time)->where('end', '<=', $end_time);
           //$que1->where('roster_id', $r_id);
       });
    })->where('guard_id', $guard_id)->where('start', '!=', $start_time)->where('end', '!=', $end )->first();
    //  dd($guardTiming);
        if(!empty($guardTiming)){
            return ['start'=> $guardTiming->start, 'end' =>$guardTiming->end, 'conf' => 'conflict', 'roster' => $guardTiming->run_sheet_id];
        }else{
            return 0; 
        }
    }




function calCulateGuardWeekHours($start, $end)
{
    // $datetime1 = new DateTime($start);
    // $datetime2 = new DateTime($end);
    // $interval = $datetime1->diff($datetime2);
    // //$minuts = $interval->format('%i')/100;
    // $minuts = $interval->format('%i');
    // return $interval->format('%h') . '.'. $minuts;

    $datetime1 = new DateTime($start);
    $datetime2 = new DateTime($end);
    $interval = $datetime1->diff($datetime2);

    $minutes = $interval->format('%i');
    $hours = $interval->format('%h');

    $minutesDecimal = $minutes / 60;
    $totalHours = $hours + $minutesDecimal;
    $totalHours = number_format($totalHours, 2); // Optional rounding
    // Use $totalHours as needed
    return $totalHours;
}

function checkGuardWorkLimitation($guard_id, $guardWorkingHours)
{
    $w_l_h = '';
    $now = Carbon::now();
    $weekStartDate =  Carbon::now()->startOfWeek()->toDateString();;
    $weekEndDate = Carbon::now()->endOfWeek()->toDateString();
    $guardOnLimitaions = Guard::where('id', $guard_id )->first();

    if($guardOnLimitaions->work_limitation_status == 1 && !empty($guardOnLimitaions->weekly_work_hours_limitation)){
        //dd('1');
        $w_l_h = $guardOnLimitaions->weekly_work_hours_limitation;
    }elseif($guardOnLimitaions->work_limitation_status == 1){
        $w_l_h = 40;
        //dd('2');
    }else{
        //dd('3');
        //$w_l_h = 0;
    }
    $sumOfOneWeekHour = JobRoster::where('guard_id', $guard_id)->whereBetween('start', [$weekStartDate, $weekEndDate])->orWhereBetween('end', [$weekStartDate, $weekEndDate])->sum('total_week_hours');
    if(!empty($sumOfOneWeekHour) && !empty($w_l_h)){
        //dd('4');
        $sum = $sumOfOneWeekHour + $guardWorkingHours;
        if($sum > $w_l_h){
            //dd('5');
            $difference = $sum - $w_l_h - $guardWorkingHours;
            $message = 'you can not create shift because you exceed form you work limitaions';
            return ['message' => $message, 'difference' => $difference];
        }else{
            //dd('5');
            return '';
        }
    }elseif(!empty($guardOnLimitaions->weekly_work_hours_limitation) && $guardOnLimitaions->weekly_work_hours_limitation < $guardWorkingHours){
        //dd('6');
        $sum = $sumOfOneWeekHour + $guardWorkingHours;
        if($sum > $w_l_h){
            //dd('7');
            $difference = $sum - $w_l_h;
            $message = 'you can not create shift because you exceed form you work limitaions';
            return ['message' => $message, 'difference' => $difference];
        }else{
            //dd('8');
            return '';
        }
        
    }else{
         //dd('9');
        return '';
    }
}



function checkAdmin($admin_id)
{
    $admin = User::where('id', $admin_id)->first();
    if($admin->userType == 'super-admin' && $admin->is_super_admin == 1 && $admin->status == 'active'){
        return 'super-admin';
    }else{
        return 'admin';
    }
}

    // function checkGuardPayrollIds($guardId, $paid_by)
    // {
    //     $check = guard_payroll_ids::where('guard_id', $guardId)->where('type', $paid_by)->first();
    //     if (empty($check)) {
    //         $rsp['status'] = false;
    //         $rsp['message'] = 'Guard don\'t have payroll id!';
    //     } else {
    //         $rsp['status'] = true;
    //         $rsp['message'] = 'Guard don\'t have payroll id!';
    //     }
    //     return $rsp;
    // }

    

    function returnAction($action)
    {
        //$str = 'add_shift';
        $str = str_replace('_', ' ', $action); // replace underscores with spaces
        $str = ucwords($str); // capitalize first letter of each word
        return $str; 
    }


    function getAdminName($id)
    {
        $user = User::where('id', $id)->first();
        if($user){
            return $user->name;
        }else{
            return 'N/A';
        }
        
    }

    function getCustomerName($id)
    {
        $user = Customer::where('id', $id)->first();
        if($user){
            return $user->name;
        }else{
            return 'N/A';
        }
        
    }

    function getCustomerNameId($id)
    {
        $user = Customer::where('id', $id)->select('id', 'name')->first();
        if($user){
            return $user;
        }else{
            return null;
        }
        
    }
    function getSalePersonName($id)
    {
        $saleperson = User::where('id', $id)->first();
        if(!empty($saleperson)){
            return $saleperson->name;
        }else{
            return 'N/A';
        }
        
    }
    function getSiteName($id)
    {
        $site = Site::where('id', $id)->first();
        if(!empty($site)){
            return $site->site_name;
        }else{
            return 'N/A';
        }
        
    }
    function getRunsheetName($id)
    {
        $runsheet = RunSheet::find($id);
        if(!empty($runsheet)){
            return $runsheet->title;
        }else{
            return 'N/A';
        }
        
    }

    function getGuardName($id)
    {
        $guard = Guard::where('id', $id)->first();
        if($guard){
            return $guard->first_name . ' '.$guard->middle_name. ' '.$guard->last_name;
        }else{
            return 'N/A';
        }
        
    }

    function getRosterdName($id)
    {
        $roster = JobNewRoster::where('id', $id)->first();
        if($roster){
            return $roster->roster_name;
        }else{
            return 'N/A';
        }
        
    }

    function getRunSheetRosterdName($id)
    {
        $roster = RunSheetRoster::where('id', $id)->first();
        if($roster){
            return $roster->name;
        }else{
            return 'N/A';
        }
        
    }


    function getGuardDocument($guard_id, $document_type){
        $guard_document = GuardDocument::where('guard_id', $guard_id)->where('document_type', $document_type)->first();
        if($guard_document){
            return $guard_document;
        }else{
            return '';
        }
    }

  function getDatesFromRange($date_time_from, $date_time_to)
    {
        //dd($date_time_to);

        $start = Carbon::createFromFormat('Y-m-d', substr($date_time_from, 0, 10));
        $end = Carbon::createFromFormat('Y-m-d', substr($date_time_to, 0, 10));
        $dates = [];
        while ($start->lte($end)) {
            $dates[] = $start->copy()->format('Y-m-d');
            $start->addDay();
        }
        return $dates;
    }

    function send_push_notification($data){

        $content = array(
          "en" => $data['message']
          );
    
        $heading = array(
          "en" => $data['title']
          );

        $config_data = DB::table('business_data')->first();
    
        $fields = array(
          'app_id' => $config_data->app_id,
          'include_player_ids' => array($data['notification_token']),
                  'data' => array(
                  'page' => $data['page'],
                  'send_by' => isset($data['send_by']) ? $data['send_by']: null ,
                  ),
          'contents' => $content,
          'headings' => $heading
        );
         
    
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://onesignal.com/api/v1/notifications");
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json; charset=utf-8',
                  'Authorization: Basic '.$config_data->server_key));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_HEADER, FALSE);
        curl_setopt($ch, CURLOPT_POST, TRUE);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        
        $result = curl_exec($ch);
        
        if ($result === FALSE) {
          die('FCM Send Error: ' . curl_error($ch));
        }
        curl_close($ch);
        return $result;

      }

      function test_send_push_notification($data){

        $content = array(
          "en" => $data['message']
          );
    
        $heading = array(
          "en" => $data['title']
          );
    
        $fields = array(
          'app_id' => '940cf8ed-4206-43a0-b542-cb93cc11e58e',
          'include_player_ids' => array($data['notification_token']),
                  'data' => array(
                  'page' => $data['page'],
                  ),
          'contents' => $content,
          'headings' => $heading
        );
         //dd($fields);
    
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://onesignal.com/api/v1/notifications");
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json; charset=utf-8',
                  'Authorization: Basic '.'NjIxNzJmZDUtMjMzOS00ZmZjLWIwM2EtZWU2MTU5ZWFkNzBh'));
                //   config('custom.server_key')
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_HEADER, FALSE);
        curl_setopt($ch, CURLOPT_POST, TRUE);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
    
        $result = curl_exec($ch);
        //dd($result);
        if ($result === FALSE) {
          die('FCM Send Error: ' . curl_error($ch));
        }
        curl_close($ch);
        return $result;

      }

      

function cometeChateCur($id, $email, $phone, $name){
    $curl = curl_init();
    curl_setopt_array($curl, [
      CURLOPT_URL => "https://2399158ea0bb9fe3.api-au.cometchat.io/v3/users",
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_ENCODING => "",
      CURLOPT_MAXREDIRS => 10,
      CURLOPT_TIMEOUT => 30,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_CUSTOMREQUEST => "POST",
      CURLOPT_POSTFIELDS => "{\"metadata\":{\"@private\":{\"email\":\"$email\",\"contactNumber\":\"$phone\"}},\"uid\":\"$name.'_'.$id\",\"name\":\"$name\"}",
      CURLOPT_HTTPHEADER => [
        "accept: application/json",
        "apikey: 2d3d4241124ba8c14b9d1eb50219cb7793b8441d",
        "content-type: application/json"
      ],
    ]);
    
    $response = curl_exec($curl);
    $err = curl_error($curl);
    
    curl_close($curl);

    if ($err) {
      return "cURL Error #:" . $err;
    } else {
        return $response;
    }
}


function checkGuardOnLeave($start, $end, $guard_id)
{
    $start_time = dbFormate($start);
    $end_time   = dbFormate($end);
    $st = strtotime($start_time);
    $se = strtotime($end_time);
    $guardTiming = GuardLeave::where(function ($que) use ($st, $se) {
    $que->orWhere(function ($que1) use ($st, $se) {
        // temp start is grater then actual start and less then actual end
        $que1->where('start', '<=', $st)->where('end', '>=', $st);
    });
    $que->orWhere(function ($que1) use ($st, $se) {
        // temp end is b/w actual start and end..
        $que1->where('start', '<=', $se)->where('end', '>=', $se);
    });
    $que->orWhere(function ($que1) use ($st, $se) {
        // is any shift lie b/w temp shift
        $que1->where('start', '>=', $st)->where('end', '<=', $se);
    });
})->where('guard_id', $guard_id)->where('status','approved')->first();
    if(!empty($guardTiming)){
        return 'leave';
    }else{
        return 'no_leave';
    }
}

function coordinates_to_address($coordinates){
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'https://maps.googleapis.com/maps/api/geocode/json?latlng='.$coordinates.'&key=AIzaSyCS-DB39Kk-Z25C5GWymVGshXIALbjXPGY');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    
    $result = curl_exec($ch);
    curl_close($ch);
    
    $address = json_decode($result, 1);
    return isset($address['results'][0]['formatted_address']) ? $address['results'][0]['formatted_address'] : $coordinates;
}

 function sendSmsToGuard($guard_no, $body){

    $api_key = PortalSettings::select('id', 'click_send_username', 'click_send_key', 'air_call_app_id', 'air_call_token')->first();

        $config = Configuration::getDefaultConfiguration()
        ->setUsername($api_key->click_send_username)
        ->setPassword($api_key->click_send_key);

        $guard_no = str_replace('(', '', $guard_no);
        $guard_no = str_replace(')', '', $guard_no);
        $guard_no = str_replace('-', '', $guard_no);
        $guard_no = str_replace(' ', '', $guard_no);

        $number_ = str_split($guard_no);

        // if (sizeof($number_) == 10) {
        //     $guard_no = '+1'.$guard_no;
        // }
        
        $apiInstance = new SMSApi(new Client(),$config);
        $msg = new \ClickSend\Model\SmsMessage();
        $msg->setBody($body); 
        $msg->setTo($guard_no);
        $msg->setSource("sdk"); 
        // \ClickSend\Model\SmsMessageCollection | SmsMessageCollection model
        $sms_messages = new \ClickSend\Model\SmsMessageCollection(); 
        $sms_messages->setMessages([$msg]);
        try {
            $result = $apiInstance->smsSendPost($sms_messages);
            $result = json_decode($result, true);

            // print_r($result);
            // exit;

            // if ($result['response_code'] == 'SUCCESS' && $result['data']['messages'][0]['status'] == 'SUCCESS') {
            //     DB::table('sms_history')->insert([
            //         'admin_id' => $user->id,
            //         'msg_body' => $request->body,
            //         'to' => $guard['number'],
            //         'to_number' => trim($result['data']['messages'][0]['to']),
            //         'direction' => 'out',
            //             // 'direction' => $result['data']['messages'][0]['direction'],
            //         'datetime' => $result['data']['messages'][0]['date'],
            //         'message_id' => $result['data']['messages'][0]['message_id'],
            //         'user_id' => trim($result['data']['messages'][0]['user_id']),
            //         'created_at' => date('Y-m-d H:i:s')
            //     ]);
            //     $id = DB::getPdo()->lastInsertId();
            //     jobRosterActions($request->admin_id, 'send_sms', $id, 'sites');
            //     $send = true;
            // }
            
        } catch (Exception $e) {
        }
}


 function roundHours($hour)
{
    $total_hours = explode('.', $hour);
    if (sizeof($total_hours) > 1 ) {
      $partial = '.'.$total_hours[1];
      if ($partial < 0.1) {
        $hour = $total_hours[0];
      }
      if ($partial < 0.27 && $partial > 0.1) {
        $hour = $total_hours[0].'.25';
      }
      if ($partial > 0.27 && $partial <= 0.52) {
        $hour = $total_hours[0].'.5';
      }
      if ($partial > 0.52 && $partial <= 0.77) {
        $hour = $total_hours[0].'.75';
      }
      if ($partial > 0.77 && $partial < 1) {
        $hour = $total_hours[0]+ 1;
      }
    }
    return $hour;
}

function removeConflictOnDeleteShift($start, $end, $roster_id, $guardId){
    $rosters = JobRoster::where('conf_start', $start)
        ->where('conf_end', $end)
        ->where('roster_id', $roster_id)
        ->where('guard_id', $guardId)
        ->select('id')
        ->get();

    if(!$rosters->isEmpty()){
        foreach ($rosters as $key => $roster) {
            $rs = JobRoster::find($roster->id);
            if ($rs) {
                $rs->conflict = null;
                $rs->conf_end = null;
                $rs->conf_start = null;
                $rs->update();
            }
        }
        return 'success';
    }
    return 'rosters not found';
}

function getRosterName($roster_id)  {
    $roster_name = JobNewRoster::where('id', $roster_id)->select('roster_name')->first();
    if($roster_name){
        return $roster_name->roster_name;
    }else{
        return '';
    }

}


function checkShiftDayHours($start, $end, $guard_id, $shift_id=0) {

    $guard_shift = JobRoster::where('start', '>=', $start)
    ->where('start', '<=', $end)->where('guard_id', $guard_id)->whereNull('deleted_at')->whereNotIn('id', [$shift_id])->select('start', 'end', 'hours')->first();
    
    if($guard_shift && $guard_shift->hours == 6){
       $datetime1 = new DateTime(dbFormateDateTime($guard_shift->end));
        $datetime2 = new DateTime(dbFormateDateTime($start));
        $interval = $datetime1->diff($datetime2);
    
        $minutes = $interval->format('%i');
        $hours = $interval->format('%h');
    
        $minutesDecimal = $minutes / 60;
        $totalHours = $hours + $minutesDecimal;
        $totalHours = round($totalHours, 2); // Optional rounding
        
        return $totalHours; 
    }else{
        return 9;
    }

}



    
