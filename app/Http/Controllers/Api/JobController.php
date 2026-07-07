<?php
namespace App\Http\Controllers\Api;

use App\Http\Resources\Job\JobResource;
use App\Http\Resources\Job\JobRosterCollection;
use App\Http\Resources\Job\JobRosterResource;
use App\Models\Guard;
use App\Models\JobNewRoster;
use App\Models\JobRosterActivity;
use Carbon\Carbon;
use App\Models\GreenCall;
use App\Models\GuardJobActivity;
use App\Models\User;
use App\Repositories\JobRepository;
use App\Repositories\JobRosterActivityRepository;
use Exception;
use App\Repositories\JobRosterRepository;
use App\Repositories\GuardJobActivityRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use App\Models\Notification;
use DateTime;

class JobController extends ApiController
{
    public $repo;
    public $notification;
    public $jobRosterRepo;
    public $jobRosterActivityRepo;
    public $currentUser;
    public $request;
    public $greenCall;
    public $guardJobActivityRepo;
    const JOB_STATUS_CONFIRMED = 'confirmed';
    const JOB_STATUS_PENDING = 'pending';
    const JOB_STATUS_REJECTED = 'rejected';
    protected $timezone = array(
      'Victoria' => 'Australia/Melbourne',
      'New South Whales' => 'Australia/Sydney',
      'Queensland' => 'Australia/Brisbane',
      'Tasmania' => 'Australia/Hobart',
      'Western Australia' => 'Australia/Perth',
      'South Australia' => 'Australia/Adelaide',
      'ACT' => 'Australia/Canberra'
  );

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(Request $request, JobRepository $jobRepository, JobRosterRepository $jobRosterRepository, JobRosterActivityRepository $jobRosterActivityRepository, GreenCall $greenCall, GuardJobActivityRepository $guardJobActivityRepository, Notification $notification) {
        parent::__construct($request);
        
/*        header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: PUT, GET, POST");
header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");*/

$this->repo = $jobRepository;
$this->jobRosterRepo = $jobRosterRepository;
$this->jobRosterActivityRepo = $jobRosterActivityRepository;
$this->greenCall = $greenCall;
$this->guardJobActivityRepo = $guardJobActivityRepository;
$this->notification = $notification;
}
public function guard_sos_call(Request $request, $id)
{
    $this->request = $request;
    $this->setValidationRules(['location' => 'required']);
    if ($this->isValidRequest()) {
        $this->response = ['success' => false, 'error' => $this->getErrors()];
        $this->statusCode = self::STATUS_CODE_200;
        return $this->sendResponse();
    }
    $guard = DB::table('guards')->where('id', $this->currentUser->id)->first();
    $roster = DB::table('job_rosters')->where('id', $id)->first();

    $main_roster = DB::table('job_new_roster')->where('id', $roster->roster_id)->first();

    $job1 = DB::table('jobs')->where('id', $roster->site_id)->first();
    
    // $admins = DB::table('users')->where('status', 'active')->get();
    // if(count($admins) > 0){
    //     foreach ($admins as $key => $value) {
            $notification = array(
                'roster' => !empty($main_roster->id) ? $main_roster->id : null,
                'guard_id' => $guard->id, 
                'record_id' => $id,
                'message' => $guard->first_name.' '.$guard->last_name.' just clicked the emergancy SOS button at '. $job1->site_name,
                'type' => 'sos_call',
                'send_time' => time(),
                'title' => 'Guard Clicked SOS Button',
                // 'send_to' => $value->id,
            );
            DB::table('portal_notifications')->insert($notification);
        // }
    // }

    DB::table('roster_complete_activity')->insert([
        'roster_id' => $id,
        'activity' => $guard->first_name.' '.$guard->last_name.' just clicked the emergancy SOS button at location.',
        'type' => 'sos_call',
        'record_id' =>  $id,
        'activity_time' => time(),
        'activity_by' => $guard->id
    ]);
    DB::table('sos_call')->insert([
        'roster_id' => $id,
        'guard_id' => $guard->id,
        'location' => $request->location
    ]);
    $administrators = DB::table('portal_settings')
    ->where('permission_name', 'sos_call')
    ->where('permission', 1)
    ->first();
    $message = $guard->first_name.' '.$guard->last_name.' Clicked SOS Button.<br>
    <style type="text/css">
    table, th, td {
        border: 1px solid black;
    }
    </style>
    <table style="width:100%">
    <tr>
    <th>Start Date & Time</th>
    <th>End Date & Time</th>
    <th>Address</th>
    </tr>
    <tr>
    <td>'.date('d/m/Y H:i', strtotime($roster->temp_start)).'</td>
    <td>'.date('d/m/Y H:i', strtotime($roster->temp_end)).'</td>
    <td>'.$job1->site_name.' ('.$job1->site_description.')</td>
    </tr>
    </table>';
    if (!empty($administrators)) {
        $admin_emails = explode(',', $administrators->users_emails);
        foreach ($admin_emails as $key => $admin_email) {
            $email_data['name'] = '';
            $email_data['email'] = $admin_email;
            $this->notification->sendGuardMail($email_data, 'Guard Clicked SOS Button', $message);
        }
    }
    $this->statusCode = self::STATUS_CODE_200;
    $this->response = [
        'success' => true,
        'message' => 'Event Log successfull.',
    ];
    return $this->sendResponse();

}
public function jobDetail($id) {
    return new JobResource($this->repo->getJobById($id,$this->currentUser->id));
}

public function getGuardJobs(Request $request, $type, $duration) {
    $week = ($request->input('week_no') != null) ? $request->input('week_no') : 0;
    $results = $this->jobRosterRepo->getCurrentMonthJobsByGuard(($this->currentUser) ? $this->currentUser->id : [], $type, $duration, $week);
    return new JobRosterCollection(JobRosterResource::collection($results));
}

public function jobSpecificDetail(Request $request, $id) {
    $results = $this->jobRosterRepo->jobSpecificDetail(($this->currentUser) ? $this->currentUser->id : [], $id);
    return new JobRosterCollection(JobRosterResource::collection($results));
}


public function getJobs(Request $request, $type, $duration, $id) {
    $week = ($request->input('week_no') != null) ? $request->input('week_no') : 0;
    $results = $this->jobRosterRepo->getJobs($id, $type, $duration, $week);
    return new JobRosterCollection(JobRosterResource::collection($results));
}

public function confirmJob($id) {
    if ($this->jobRosterRepo->updateJobStatus($id, self::JOB_STATUS_CONFIRMED)) {
        $roster = DB::table('job_rosters')->where('id', $id)->first();
        $guard = DB::table('guards')->where('id', $roster->guard_id)->first();
        $job = DB::table('sites')->where('id', $roster->site_id)->first();

        if($roster->rejected_by != null ){
            $roster->rejected_by = null;
            $roster->job_status = 'confirmed';
            $roster->save();
        }

        $roster = DB::table('job_rosters')->where('id', $id)->first();
        $main_roster = DB::table('job_new_roster')->where('id', $roster->roster_id)->first();
        // $admins = DB::table('users')->where('status', 'active')->get();

        // if(count($admins) > 0){
        //     foreach ($admins as $key => $value) {
                $notification = array(
                    'roster' => !empty($main_roster->id) ? $main_roster->id : null,
                    'guard_id' => $roster->guard_id, 
                    'record_id' => $id,
                    'message' => $guard->first_name.' '.$guard->last_name.' confirm their job at '. $job->site_name,
                    'type' => 'job_confirm',
                    'send_time' => time(),
                    'title' => 'Job Confirmation',
                    // 'send_to' => $value->id,
                );
                DB::table('portal_notifications')->insert($notification);
        //     }
        // }

        


            DB::table('roster_complete_activity')->insert([
            'roster_id' => $id,
            'activity' => $guard->first_name.' '.$guard->last_name.' confirm their job.',
            'type' => 'job_confirm',
            'record_id' => $id,
            'activity_time' => time(),
            'activity_by' => $roster->guard_id
            ]);



            //push notification
            $admins = DB::table('users')->where('notification_token', '!=', '')->select('notification_token')->get();
            foreach($admins as $a)
            {
                $notification_data = [
                    'message' => $guard->first_name.' '.$guard->last_name.' confirm their job.',
                    'title' => 'Job Signin',
                    'fcm_token' => $a->notification_token,
                    'page' => 'homepage',
                    'roster_id' =>  $id
                ]; 
                $this->notification->new_app_send_push_notification($notification_data);
            }

        $this->statusCode = self::STATUS_CODE_200;
        $this->response = [
            'success' => true,
            'message' => 'Your job is confirmed.'
        ];
        return $this->sendResponse();
    }
    $this->statusCode = self::STATUS_CODE_200;
    $this->response = [
        'success' => false,
        'message' => 'Your job is not been confirmed.'
    ];
    return $this->sendResponse();
}

public function rejectJob($id) {
        // $this->statusCode = self::STATUS_CODE_200;
        // $this->response = [
        //     'success' => false,
        //     'message' => 'Your can\'t reject job!'
        // ];
        // return $this->sendResponse();
        // exit();
    $roster = DB::table('job_rosters')->where('id', $id)->first();
    $guard = DB::table('guards')->where('id', $roster->guard_id)->first();
    $job = DB::table('sites')->where('id', $roster->site_id)->first();
    if ($this->jobRosterRepo->updateJobStatus($id, self::JOB_STATUS_REJECTED)) {   
        DB::table('job_rosters')->where('id', $id)->update(['rejected_by' => $roster->guard_id]);
        $notification = array(
            'guard_id' => $roster->guard_id, 
            'record_id' => $id,
            'message' => $guard->first_name.' '.$guard->last_name.' rejected their job at '. $job->site_name,
            'type' => 'job_reject',
            'send_time' => time(),
            'title' => 'Job Rejection'
        );
            // DB::table('portal_notifications')->insert($notification);
            // DB::table('roster_complete_activity')->insert([
            // 'roster_id' => $id,
            // 'activity' => $guard->name.' rejected their job.',
            // 'type' => 'job_confirm',
            // 'record_id' => $id,
            // 'activity_time' => time(),
            // 'activity_by' => $roster->guard_id
            // ]);
        //     $administrators = DB::table('portal_settings')
        //     ->where('permission_name', 'job_rejection')
        //     ->where('permission', 1)
        //     ->first();
        //     $message = $guard->name.' rejected their job.<br>
        //           <style type="text/css">
        //           table, th, td {
        //             border: 1px solid black;
        //           }
        //           </style>
        //           <table style="width:100%">
        //           <tr>
        //             <th>Start Date & Time</th>
        //             <th>End Date & Time</th>
        //             <th>Address</th>
        //           </tr>
        //           <tr>
        //             <td>'.date('d/m/Y H:i', strtotime($roster->temp_start)).'</td>
        //             <td>'.date('d/m/Y H:i', strtotime($roster->temp_end)).'</td>
        //             <td>'.$job->site_name.' ('.$job->site_description.')</td>
        //           </tr>
        //         </table>';
        //         if (!empty($administrators)) {
        //             $admin_emails = explode(',', $administrators->users_emails);
        //     foreach ($admin_emails as $key => $admin_email) {
        //         $email_data['name'] = '';
        //         $email_data['email'] = $admin_email;
        //         $this->notification->sendGuardMail($email_data, 'Job Rejection', $message);
        //     }
        // }


        DB::table('roster_complete_activity')->insert([
            'roster_id' => $id,
            'activity' => $guard->first_name.' '.$guard->last_name.' rejected their job.',
            'type' => 'job_rejected',
            'record_id' => $id,
            'activity_time' => time(),
            'activity_by' => $roster->guard_id
        ]);


        //push notification
        $admins = DB::table('users')->where('notification_token', '!=', '')->select('notification_token')->get();
        foreach($admins as $a)
        {
            $notification_data = [
                'message' => $guard->first_name.' '.$guard->last_name.' rejected their job.',
                'title' => 'Job_rejected',
                'fcm_token' => $a->notification_token,
                'page' => 'homepage',
                'roster_id' =>  $id
            ]; 
            $this->notification->new_app_send_push_notification($notification_data);
        }

        $this->statusCode = self::STATUS_CODE_200;
        $this->response = [
            'success' => true,
            'message' => 'Your job is rejected.'
        ];
        return $this->sendResponse();
    }
    $this->statusCode = self::STATUS_CODE_200;
    $this->response = [
        'success' => false,
        'message' => 'Your job is not rejected.'
    ];
    return $this->sendResponse();
}

public function jobSignin(Request $request, $id) {
    $this->request = $request;
    $this->setValidationRules(['time' => 'required', 'selfie' => 'required', 'location' => 'required']);
    if ($this->isValidRequest()) {
        $this->response = ['success' => false, 'error' => $this->getErrors()];
        $this->statusCode = self::STATUS_CODE_200;
        return $this->sendResponse();
    }
        // $job_roaster = JobNewRoster::where('roster_id', $id)->first();
        // $job = new JobResource($this->repo->getJobById($job_roaster->site_id));
    $job = new JobResource($this->repo->getJobById($request->jobId, $this->currentUser->id));

    $roster_data = DB::table('job_rosters')->where('id',$id)->first();
    $job_start_time = $roster_data->start;
    $job_start_time = strtotime($job_start_time);
        //$job_start_time = $roster_data->job_start;
        // $job_start_time = strtotime($job_start_time);
    if ($request->has('signin_time')) {
        $current_time = $request->input('signin_time');
            // $current_time = str_replace('T', ' ', $current_time);
            // $current_time = str_replace('Z', ' ', $current_time);
            // $current_time = str_replace('GMT', ' ', $current_time);
            // $current_time = explode('+', $current_time);
            // $current_time = $current_time[0];
        $current_time = strtotime($current_time);
    }else{
        $current_time = time();
    }
    $diff = round(($job_start_time - $current_time) / 60,2);
        // if ($diff > 30) {
        //     $this->response = ['success' => false, 'error' => 'You can only sign-in 30 minutes prior to your shift. ', 'message' => 'You can only sign-in 30 minutes prior to your shift.'];
        //     $this->statusCode = self::STATUS_CODE_200;
        //     return $this->sendResponse();
        // }
        // elseif($diff < -30){
        //     $this->response = ['success' => false, 'error' => 'You are late more than 30 mins to your shift. Contact Operations now.', 'message' => 'You are late more than 30 mins to your shift. Contact Operations now.'];
        //     $this->statusCode = self::STATUS_CODE_200;
        //     return $this->sendResponse();
        // }

        if($request->input('location') == null || $request->input('location') == ''){
        
            return response()->json([ 'success' => false, 'message' => 'Please send coordinates.' , 'code' => 404 ]);
        }

    $coordinates = explode(',', $request->input('location'));

    
    if($job->coordinates == null ||  $job->coordinates == ''){
        return response()->json([ 'success' => false, 'message' => 'Location coordinates not set.' , 'code' => 404 ]);
    }

    $coordinates1 = explode(',', $job->coordinates);

    

    $distance = $this->distance(trim($coordinates[0]), trim($coordinates[1]), trim($coordinates1[0]), trim($coordinates1[1]) );
    if($job->signin_radius > 0){
        $signin_radius = $job->signin_radius/1000;
    }else{
        $signin_radius = 0.31;
    }
    if($distance > $signin_radius){
        $this->response = ['success' => false, 'error' => 'You are '.number_format($distance, 2).' km away from your job!', 'message' => 'You are '.number_format($distance, 2).' km away from your job!'];

        $this->statusCode = self::STATUS_CODE_200;
        return $this->sendResponse();
    }
    $is_already_signin = DB::table('job_roster_activites')->where(['job_roster_id' => $id])->first();
    if (!empty($is_already_signin)) {
       $this->response = ['success' => false, 'error' => 'You are already signin in this job!', 'message' => 'You are already signin in this job!'];
       $this->statusCode = self::STATUS_CODE_200;
       return $this->sendResponse();
   }
        // echo $distance;
        // exit();
   $field = 'selfie'; 
   $media = '';
        // if ($this->request->hasFile($field)) {
            // $files = $this->request->file($field);
            // $media = $this->uploader($files);
   $media = $this->uploader_base64($this->request->input($field));
        // }
   $this->jobRosterActivityRepo->setStatusInactive($this->currentUser->id, $id);
   $dateTime = DateTime::createFromFormat('d-m-Y H:i', $this->request->input('time'));
   $usFormat = $dateTime->format('m/d/Y h:i A');
   $model =  $this->jobRosterActivityRepo->insert([
    'guard_id' => $this->currentUser->id,
    'job_roster_id' => $id,
    'job_incident_report_id' => null,
    'signin_time' => $usFormat,
    'signin_selfie' => $media,
    'location' => $this->request->input('location'),
    'status' => 1,
    'signin_notes' => (!empty($this->request->input('notes')) && $this->request->input('notes')!= null && $this->request->input('notes')!= '') && $request->has('notes')   ? $this->request->input('notes') : null
]);
DB::table('job_rosters')->where(['id' => $id])->update(['update_status' => 1, 'signin_status' => 1, 'last_update' => time()]);
$green_call_30 = DB::table('green_call')->where(['job_id' => $id, 'guard_id' =>  $this->currentUser->id])->first();
$guard = DB::table('guards')->where('id',$this->currentUser->id)->first();
$inputTime = DateTime::createFromFormat('d-m-Y H:i', $this->request->input('time'));
$inputTimeFormatted = $inputTime->format('Y-m-d H:i');
if($inputTimeFormatted <= $roster_data->start){
    DB::table('job_rosters')->where(['id' => $id])->update(['in_paysheet' => 1]);
}
if (empty($green_call_30)) {
    
    $roster = DB::table('job_rosters')->where('id', $id)->first();
        $main_roster = DB::table('job_new_roster')->where('id', $roster->roster_id)->first();
        $admins = DB::table('users')->where('status', 'active')->get();
        // if(count($admins) > 0){
        //     foreach ($admins as $key => $value) {
                $notification = array(
                    'roster' => !empty($main_roster->id) ? $main_roster->id : null,
                    'guard_id' => $this->currentUser->id, 
                    'record_id' => $id,
                    'message' =>  $guard->first_name.' '.$guard->last_name.' missed their 30 mints green call job at '. $job->site_name,
                    'type' => 'greencall_missed',
                    'send_time' => time(),
                    'title' => 'Green Call Missed',
                    // 'send_to' => $value->id,
                );
                DB::table('portal_notifications')->insert($notification);
        //     }
        // }

            //  $administrators = DB::table('portal_settings')
            // ->where('permission_name', 'green_call_miss')
            // ->where('permission', 1)
            // ->first();
            // if (!empty($administrators)) {
            // $admins = explode(',', $administrators->users_emails);
            // foreach ($admins as $key => $admin) {
            // $email_data['name'] = '';
            // $email_data['email'] = $admin;
            // $this->notification->sendGuardMail($email_data, 'Green Call Missed', $guard->name.' missed their 30 minutes green call job at '. $job->site_name);
            // }
            // }

}
if ($model) {

    $roster = DB::table('job_rosters')->where('id', $id)->first();
    $main_roster = DB::table('job_new_roster')->where('id', $roster->roster_id)->first();
    $admins = DB::table('users')->where('status', 'active')->get();
    // if(count($admins) > 0){
    //     foreach ($admins as $key => $value) {
            $notification = array(
                'roster' => !empty($main_roster->id) ? $main_roster->id : null,
                'guard_id' => $this->currentUser->id, 
                'record_id' => $id,
                'message' =>  $guard->first_name.' '.$guard->last_name.' signin in their job at '. $job->site_name,
                'type' => 'job_signin',
                'send_time' => time(),
                'title' => 'Job Signin',
                // 'send_to' => $value->id,
            );
    //     }
    // }
    DB::table('portal_notifications')->insert($notification);


            DB::table('roster_complete_activity')->insert([
            'roster_id' => $id,
            'activity' => $guard->first_name.' '.$guard->last_name.' signin in their job',
            'type' => 'job_signin',
            'record_id' => $id,
            'activity_time' => time(),
            'activity_by' => $this->currentUser->id
            ]);


            
            $admins = DB::table('users')->where('notification_token', '!=', '')->select('notification_token')->get();
            foreach($admins as $a)
            {
                $notification_data = [
                    'message' => $guard->first_name.' '.$guard->last_name.' signin in their job.',
                    'title' => 'Job Signin',
                    'fcm_token' => $a->notification_token,
                    'page' => 'homepage',
                    'roster_id' =>  $id
                ]; 
                $this->notification->new_app_send_push_notification($notification_data);
            }
            
    $this->statusCode = self::STATUS_CODE_200;
    $this->response = [
        'success' => true,
        'message' => 'Clocked-in Successfully!'
    ];
    return $this->sendResponse();
}
$this->statusCode = self::STATUS_CODE_200;
$this->response = [
    'success' => false,
    'message' => 'You are not Check-in your job.'
];
return $this->sendResponse();
}

public function check_welfare_call(Request $request, $id) {
    $this->request = $request;
    $job_roster = DB::table('job_rosters')->where('id',$request->input('job_id'))->first();
    if (!empty($job_roster) && $job_roster->last_send_welfare_call > 0) {
    $is_already = DB::table('welfare_call_data')->where('response_time', '>', $job_roster->last_send_welfare_call)->first();
    }else{
        $is_already = array();
    }
    if (!empty($is_already)) {
        $this->statusCode = self::STATUS_CODE_200;
        $this->response = [
            'success' => false,
            'message' => 'Welfare call response already submited!',
        ];
    }else{
        $this->statusCode = self::STATUS_CODE_200;
        $this->response = [
            'success' => true,
            'message' => 'Welfare call response pending.',
        ];
    }
    return $this->sendResponse();
}

public function jobSignout(Request $request, $id) {
    $this->request = $request;
    $this->setValidationRules(['time' => 'required', 'selfie' => 'required']);
    if ($this->isValidRequest()) {
        $this->response = ['success' => false, 'error' => $this->getErrors()];
        $this->statusCode = self::STATUS_CODE_200;
        return $this->sendResponse();
    }
    $field = 'selfie'; $media = '';
        // if ($this->request->hasFile($field)) {
            // $files = $this->request->file($field);
            // $media = $this->uploader($files);
    $media = $this->uploader_base64($this->request->input($field));
        // }
    $signout_location = '';
    // if ($request->has('coordinates') && $request->coordinates != '') {
    //     $signout_location = $request->coordinates;
    // }

    if ($request->has('location') && $request->location != '') {
        $signout_location = $request->location;
    }

    $dateTime = DateTime::createFromFormat('d-m-Y H:i', $this->request->input('time'));
    $usFormat = $dateTime->format('m/d/Y h:i A');

    $model = $this->jobRosterActivityRepo->setStatusInactive($this->currentUser->id, $id, [
        'signout_time' => $usFormat,
        'signout_selfie' => $media,
        'status' => 0,
        'signout_location' => $signout_location,
        'signout_notes' => (!empty($this->request->input('notes')) && $this->request->input('notes') != null && $this->request->input('notes') != '') ? $this->request->input('notes') : ''
    ]);

    // $activity = JobRosterActivity::where('job_roster_id', $id)
    //         ->select('id', 'signin_time', 'signout_time')
    //         ->first();
    //         if ($activity) {
    //             $total_hours = $this->calculateTotalHours($activity->signin_time, $activity->signout_time);
    //             $activity->authorized_hours = $total_hours;
    //             $activity->save();
    //         }


    if ($model) {
        DB::table('job_rosters')->where('id', $id)->update(['job_status' => 'completed', 'update_status' => 1, 'signin_status' => 0, 'last_update' => time()]);
        $guard = DB::table('guards')->where('id',$this->currentUser->id)->first();
        $roster = DB::table('job_rosters')->where('id', $id)->first();
        # CALCULATE TIME DIFFERENCE SO THAT UPDATE THE VARIABLE USE IN COMPLETE PATSHEET
        $inputTime = DateTime::createFromFormat('d-m-Y H:i', $this->request->input('time'));
        $inputTimeFormatted = $inputTime->format('Y-m-d H:i');
        if($inputTimeFormatted >= $roster->end && $roster->in_paysheet == 1){
            DB::table('job_rosters')->where(['id' => $id])->update(['in_paysheet' => 1]);
        }else{
            DB::table('job_rosters')->where(['id' => $id])->update(['in_paysheet' => 0]);
        }
        $main_roster = DB::table('job_new_roster')->where('id', $roster->roster_id)->first();
        $admins = DB::table('users')->where('status', 'active')->get();
        // if(count($admins) > 0){
        //     foreach ($admins as $key => $value) {
                $notification = array(
                    'roster' => !empty($main_roster->id) ? $main_roster->id : null,
                    'guard_id' => $this->currentUser->id, 
                    'record_id' => $id,
                    'message' => $guard->first_name.' '.$guard->last_name.' signout from their job.',
                    'type' => 'job_signout',
                    'send_time' => time(),
                    'title' => 'Job Signout',
                    // 'send_to' => $value->id,
                );
                    // $this->guard_job_rating($id);
                    // $this->rosterCompleteActivityReport($id);
                DB::table('portal_notifications')->insert($notification);
        //     }
        // }

            DB::table('roster_complete_activity')->insert([
            'roster_id' => $id,
            'activity' => $guard->first_name.' '.$guard->last_name.' signout from their job.',
            'type' => 'job_signout',
            'record_id' => $id,
            'activity_time' => time(),
            'activity_by' => $this->currentUser->id
            ]);


            $admins = DB::table('users')->where('notification_token', '!=', '')->select('notification_token')->get();
            foreach($admins as $a)
            {
                $notification_data = [
                    'message' => $guard->first_name.' '.$guard->last_name.' signout in their job.',
                    'title' => 'Job Signout',
                    'fcm_token' => $a->notification_token,
                    'page' => 'homepage',
                    'roster_id' =>  $id
                ]; 
                $this->notification->new_app_send_push_notification($notification_data);
            }

        $this->statusCode = self::STATUS_CODE_200;
        $this->response = [
            'success' => true,
            'message' => 'Clocked-out Successfully!'
        ];
        return $this->sendResponse();
    }
    $this->statusCode = self::STATUS_CODE_200;
    $this->response = [
        'success' => false,
        'message' => 'You are not Check-out your job.'
    ];
    return $this->sendResponse();
}


function calCulateTotalHours($start, $end)
{
    $datetime1 = new DateTime($start);
    $datetime2 = new DateTime($end);
    $interval = $datetime1->diff($datetime2);

    $minutes = $interval->format('%i');
    $hours = $interval->format('%h');

    $minutesDecimal = $minutes / 60;
    $totalHours = $hours + $minutesDecimal;
    $totalHours = round($totalHours, 2); // Optional rounding
    // Use $totalHours as needed
    return $totalHours;
}

private function uploader($file) {
    try {
            // $destinationPath =  rtrim(app()->basePath('public/uploads/'), '/');
            // $destinationPath =  rtrim('../../uploads/');
        $public_path = public_path();
        $public_path = str_replace('portal/public', '', $public_path);
        $public_path = str_replace('apis/public', '', $public_path);
        $destinationPath = $public_path.'uploads/';
        $newName = Str::random(25);
        $fileName = $newName . '.' . $file->getClientOriginalExtension();
        $file->move($destinationPath, $fileName);
        return $fileName;
    } catch (Exception $e) {
        echo $e->getMessage();
    }
}

private function uploader_base64($file, $folder = 'uploads') {
    try {
        // $destinationPath =  rtrim('../../uploads/');
        $public_path =  rtrim(app()->basePath('public/'), '');
                $public_path = str_replace('portal/public/', '', $public_path);
                $public_path = str_replace('apis/public/', '', $public_path);
                $public_path = str_replace('app-apis.amgsystem.com.au', 'scouts-apis.amgsystem.com.au/public', $public_path);
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

public function report_incident(Request $request, $id) {
    $this->request = $request;
    $this->setValidationRules(['guard_id' => 'required', 'type' => 'required', 'detail' => 'required', 'notified_to' => 'required', 'injuries_incurred' => 'required', 'damage' => 'required', 'people_involved' => 'required', 'action_taken' => 'required', 'is_correct' => 'required']);
    if ($this->isValidRequest()) {
        $this->response = ['success' => false, 'error' => $this->getErrors()];
        $this->statusCode = self::STATUS_CODE_200;
        return $this->sendResponse();
    }

    $media = '';
    if ($this->request->input('image')) {
        $media = $this->uploader_base64($this->request->input('image'));
    }

    DB::table('job_incident_reports')->insert(['job_id' => $id, 'guard_id' => $this->request->input('guard_id'), 'type' => $this->request->input('type'), 'detail' => $this->request->input('detail'), 'notified_to' => $this->request->input('notified_to'), 'injuries_incurred' => $this->request->input('injuries_incurred'), 'damage' => $this->request->input('damage'), 'people_involved' => $this->request->input('people_involved'), 'action_taken' => $this->request->input('action_taken'), 'is_correct' => $this->request->input('is_correct'), 'detailed_description' => 'n/a', 'image' => $media, 'date_added' => time()]);

    $guard = DB::table('guards')->where('id', '=', $this->request->input('guard_id'))->first();
    $email_data['name'] = $guard->first_name;
    $email_data['email'] = $guard->email;
    $this->notification->sendGuardMail($email_data, 'New Incident Report', 'Incident Report successfully submited.');
    $this->statusCode = self::STATUS_CODE_200;
    $this->response = [
        'success' => true,
        'message' => 'Incident reported successfully!'
    ];
    return $this->sendResponse();

}



public function leave_request(Request $request, $id) {
    $this->request = $request;
    $this->setValidationRules(['start_date' => 'required', 'end_date' => 'required', 'notes' => 'required']);
    if ($this->isValidRequest()) {
        $this->response = ['success' => false, 'error' => $this->getErrors()];
        $this->statusCode = self::STATUS_CODE_200;
        return $this->sendResponse();
    }

    $datetime1 = new DateTime(date('Y-m-d', strtotime($this->request->input('start_date'))));
    $datetime2 = new DateTime(date('Y-m-d', strtotime($this->request->input('end_date'))));
    $difference = $datetime1->diff($datetime2);

    $record_id = DB::table('guard_leave_requests')->insertGetId(['guard_id' => $id, 'start' => strtotime($this->request->input('start_date')), 'end' => strtotime($this->request->input('end_date')), 'start_date' => $this->request->input('start_date'), 'end_date' => $this->request->input('end_date'), 'notes' => $this->request->input('notes'), 'date_added' => time(), 'reason' => $this->request->reason, 'days' => $difference->days == 0 ? 1 : $difference->days, 'status' => 'pending']);

    $guard = DB::table('guards')->where('id', $id)->first();

    //$roster = DB::table('job_rosters')->where('id', $id)->first();
    //$main_roster = DB::table('job_new_roster')->where('id', $roster->roster_id)->first();

    // $admins = DB::table('users')->where('status', 'active')->get();
    // if(count($admins) > 0){
    //     foreach ($admins as $key => $value) {
            DB::table('portal_notifications')->insert([
                //'roster' => !empty($main_roster->id) ? $main_roster->id : null,
                'guard_id' => $id,
                'message' => $guard->first_name.' '.$guard->last_name.' submited a leave request.',
                'type' => 'leave',
                'status' => 'unseen',
                'send_time' => time(),
                'record_id' => $record_id,
                'title' => 'Staff Leave Request',
                // 'send_to' => $value->id,
                ]);
    //     }
    // }

        // DB::table('roster_complete_activity')->insert([
        //     'roster_id' => $id,
        //     'activity' => $guard->name .' submited a leave request.',
        //     'type' => 'leave_request',
        //     'record_id' => $record_id,
        //     'activity_time' => time(),
        //     'activity_by' => $id
        //     ]);
    $this->statusCode = self::STATUS_CODE_200;
    $this->response = [
        'success' => true,
        'message' => 'Leave request submitted successfully!'
    ];

    $guard = DB::table('guards')->where('id', $id)->first();

    //$roster = DB::table('job_rosters')->where('guard_id', $id)->first();
    //$main_roster = DB::table('job_new_roster')->where('id', $roster->roster_id)->first();
    // $admins = DB::table('users')->where('status', 'active')->get();
    // if(count($admins) > 0){
    //     foreach ($admins as $key => $value) {
            // $notification = array(
            //     //'roster' => !empty($main_roster->id) ? $main_roster->id : null,
            //     'guard_id' => $guard->id, 
            //     'record_id' => $id,
            //     'message' => $guard->first_name.' '.$guard->last_name.' Leave request their job',
            //     'type' => 'leave',
            //     'send_time' => time(),
            //     'title' => 'Leave request',
            //     // 'send_to' => $value->id,
            // );
            // DB::table('portal_notifications')->insert($notification);
    //     }
    // }

    $roster = DB::table('job_rosters')->where('id', $id)->first();

            // $administrators = DB::table('portal_settings')
            // ->where('permission_name', 'leave_email_permission')
            // ->where('permission', 1)
            // ->first();
    $message = $guard->first_name.' '.$guard->last_name.' Leave request their job.<br>
    <style type="text/css">
    table, th, td {
      border: 1px solid black;
  }
  </style>
  <table style="width:100%">
  <tr>
  <th>Start Date & Time</th>
  <th>End Date & Time</th>
  <th>Address</th>
  </tr>';
  if (!empty($roster)) {
    $message .='<tr>
    <td>'.date('d/m/Y H:i', strtotime($roster->temp_start)).'</td>
    <td>'.date('d/m/Y H:i', strtotime($roster->temp_end)).'</td>
    <td>'.$job->site_name.' ('.$job->site_description.')</td>
    </tr>';
}
$message .= '</table>';
        //  if (!empty($administrators)) {
        //             $admin_emails = explode(',', $administrators->users_emails);
        //     foreach ($admin_emails as $key => $admin_email) {
        //         $email_data['email'] = $admin_email;
        //         $this->notification->sendGuardMail($email_data, 'Leave Email', $message);
        //     }
        // }
return $this->sendResponse();

}



public function get_leave_requests(Request $request, $id) {
 $this->request = $request;

 $requests = DB::table('guard_leave_requests')->where(['guard_id' => $id])->get();

 $list = array();
 foreach($requests as $req)
 {
    $list[] = array('start' => date('d-m-Y H:i', $req->start), 'end' => date('d-m-Y H:i', $req->end), 'notes' => $req->notes, 'date_added' => date('d-m-Y H:i', $req->date_added), 'leave_id' => $req->id, 'reason' =>  $req->reason, 'duration' => $req->days, 'status' => $req->status);
}


$this->statusCode = self::STATUS_CODE_200;
$this->response = [
    'success' => true,
    'message' => 'Leave requests',
    'list' => $list
];
return $this->sendResponse();

}



public function get_foot_patrol_reports(Request $request, $id) {
    $this->request = $request;
    $list = array();
    $reports = DB::table('foot_patrol_reports')
    ->where('job_id', $this->request->input('job_id'))
    ->where('guard_id', $id)
    ->where('roster_id', $this->request->input('roster_id'))
    ->get();

    foreach($reports as $rep){
        $images = array();
        $signature = '';
        if ($rep->photo != '') {
            $imgs = json_decode($rep->photo, true);
            if (is_array($imgs) && !empty($imgs)) {
                foreach ($imgs as $key => $value) {
                    $newObject = new \stdClass();
                    $newObject->imgPath = 'https://app-apis.amgsystem.com.au/uploads/'.$value['imgPath'];
                    $newObject->timestamp = $value['timestamp'];
                    $images[] = $newObject;
                }
            }else{
                $images[] = 'https://app-apis.amgsystem.com.au/uploads/'.$rep->photo;
            }
        }
        if ($rep->signature != '') {
            $signature = 'https://app-apis.amgsystem.com.au/uploads/'.$rep->signature;
        }
        // {
            $list[] = array('job_id' => $rep->job_id, 'guard_id' => $rep->guard_id, 'detail' => $rep->patrolling_detail, 'notified_to' => '', 'image' => $images, 'date_added' => $rep->date .' '. $rep->time, 'signature' => $signature,'site_name' => $rep->site_name);
        // }

    }


    $this->statusCode = self::STATUS_CODE_200;
    $this->response = [
        'success' => true,
        'message' => 'Incident reports',
        'list' => $list
    ];
    return $this->sendResponse();

}
public function get_incident_reports(Request $request, $id) {
    $this->request = $request;

    $list = array();


    $reports = DB::table('incident_reports')
    ->where('job_id', $this->request->input('job_id'))
    ->where('guard_id', $id)
    ->where('roster_id', $this->request->input('roster_id'))
    ->get();

    foreach($reports as $rep){
        $images = array();
        $signature = '';
        if ($rep->photo != '') {
            $imgs = json_decode($rep->photo, true);
            if (is_array($imgs) && !empty($imgs)) {
                foreach ($imgs as $key => $value) {
                    $newObject = new \stdClass();
                    $newObject->imgPath = 'https://app-apis.amgsystem.com.au/uploads/'.$value['imgPath'];
                    $newObject->timestamp = $value['timestamp'];
                    $images[] = $newObject;

                }
            }else{
                $images[] = 'https://app-apis.amgsystem.com.au/uploads/'.$rep->photo;
            }
        }
        if ($rep->signature != '') {
            // $signature = env('ASSET_URL').'incident/'.$rep->signature;
            $signature = 'https://app-apis.amgsystem.com.au/uploads/'.$rep->signature;
        }
        // {
            $list[] = array('job_id' => $rep->job_id, 'guard_id' => $rep->guard_id, 'type' => $rep->injury_type, 'detail' => $rep->injury_detail, 'notified_to' => '', 'injuries_incurred' => '', 'damage' => '', 'people_involved' => json_decode($rep->people_involved, true), 'action_taken' => '', 'is_correct' => '', 'detailed_description' => 'n/a', 'image' => $images, 'date_added' => $rep->incident_date .' '. $rep->incident_time, 'signature' => $signature, 'emergency_services' => json_decode($rep->emergency_services, true),'wittness' => json_decode($rep->wittness, true),'vehicle' => json_decode($rep->vehicle, true),'site_name' => $rep->site_name);
        // }

    }


    $this->statusCode = self::STATUS_CODE_200;
    $this->response = [
        'success' => true,
        'message' => 'Incident reports',
        'list' => $list
    ];
    return $this->sendResponse();

}



public function green_call_coordinates(Request $request, $id) {
    $this->request = $request;
    $status = $request->input('status');
    $job_id = explode(',', $request->input('job_id'));
        // $status = ($request->input('status') != null) ? $request->input('status') : 'no';
    if (isset($job_id[2])) {
        $greenCall_id = $job_id[2];
        $this->greenCall->where('id', $greenCall_id)->update(['coordinates' => $request->input('coordinates'),'guard_id' => $id,'status' => $status,'before_time' => $job_id[1], 'response_time' => time()]);
    }else{
    $greenCall_id = $this->greenCall->insertGetId([
        'coordinates' => $request->input('coordinates'),
        'job_id' => $job_id[0],
        'guard_id' => $id,
        'status' => $status,
        'before_time' => $job_id[1]
    ]);
    }
    $guard = DB::table('guards')->where('id', $id)->first();
    $roster = DB::table('job_rosters')->where('id', $job_id[0])->first();
    $main_roster = DB::table('job_new_roster')->where('id', $roster->roster_id)->first();
    // $admins = DB::table('users')->where('status', 'active')->get();
    // if(count($admins) > 0){
    //     foreach ($admins as $key => $value) {
            DB::table('portal_notifications')->insert([
                'roster' => !empty($main_roster->id) ? $main_roster->id : null,
                'guard_id' => $id,
                'message' => $guard->first_name.' '.$guard->last_name .' submitted Green Call before '. $job_id[1],
                'type' => 'green_call',
                'status' => 'unseen',
                'send_time' => time(),
                'record_id' => $job_id[0],
                'title' => 'Green call',
                // 'send_to' => $value->id,
            ]);
    //     }
    // }


        DB::table('roster_complete_activity')->insert([
            'roster_id' => $job_id[0],
            'activity' => 'Green Call before '. $job_id[1],
            'type' => 'green_call',
            'record_id' =>  $greenCall_id,
            'activity_time' => time(),
            'activity_by' => $id
            ]);

        //push notification
        $admins = DB::table('users')->where('notification_token', '!=', '')->select('notification_token')->get();
        foreach($admins as $a)
        {
            $notification_data = [
                'message' => $guard->first_name.' '.$guard->last_name.'submitted Green Call before.',
                'title' => 'green_call',
                'fcm_token' => $a->notification_token,
                'page' => 'homepage',
                'roster_id' =>  $id
            ]; 
            $this->notification->new_app_send_push_notification($notification_data);
        }
    $this->statusCode = self::STATUS_CODE_200;
    $this->response = [
        'success' => true,
        'message' => 'Location reported successfully!',
        'coordinates' => $this->request->input('coordinates'),
        'job_id' => $job_id[0],
        'guard_id' => $id
    ];
    return $this->sendResponse();

}


public function welfare_call(Request $request, $id) {
    if(isset($request->welfare_id)){
        $getWelfare = DB::table('welfare_call_data')->where('id', $request->welfare_id)->first();
        $currentTime = time();
        $createdAt = strtotime($getWelfare->created_at);
        $timeElapsed = $currentTime - $createdAt;
        if ($timeElapsed > 5 * 60) {
            return response()->json(['success'=>true, 'message' => 'Time is up for answering.']);
        }
    }
    $this->request = $request;
    $data = array(
        'coordinates' => $request->input('coordinates'),
        'job_roster_id' => $request->input('job_id'),
        'guard_id' => $id,
        'status' => $request->input('status'),
        'notes' => $request->input('notes'),
        'response_time' => time(),
        'send_time' => time(),
        'updated_at' => date('Y-m-d H:i:s')
    );
    $guard = DB::table('guards')->where('id', $id)->first();
    if ($request->has('welfare_id')) {
        DB::table('welfare_call_data')->where('id', $request->welfare_id)->update($data);
        $result = $request->welfare_id;
        
    }else{
    $result = DB::table('welfare_call_data')->insertGetId($data);
    }
    if($result != ''){
    $dataArr = array(
        'welfare_id' => $request->welfare_id,
        'coordinates' => $request->input('coordinates'),
        'job_roster_id' => $request->input('job_id'),
        'guard_id' => $id,
        'status' => $request->input('status'),
        'notes' => $request->input('notes'),
        'response_time' => time(),
        'send_time' => time(),
        'updated_at' => date('Y-m-d H:i:s')
    );
            DB::table('roster_complete_activity')->insert([
            'roster_id' => $request->input('job_id'),
            'activity' => json_encode($dataArr),
            'type' => 'welfare_call',
            'record_id' =>  $result,
            'activity_time' => time(),
            'activity_by' => $id
            ]);
            
            $roster = DB::table('job_rosters')->where('id', $request->input('job_id'))->first();
            $main_roster = DB::table('job_new_roster')->where('id', $roster->roster_id)->first();
            // $admins = DB::table('users')->where('status', 'active')->get();
            // if(count($admins) > 0){
            //     foreach ($admins as $key => $value) {
                    DB::table('portal_notifications')->insert([
                        'roster' => !empty($main_roster->id) ? $main_roster->id : null,
                        'guard_id' => $id,
                        'message' =>  $guard->first_name.' '.$guard->last_name.' submitted Welfare Call',
                        // 'type' => $guard->first_name.' '.$guard->last_name.' submitted Welfare Call.',
                        'type' => 'welfare_call',
                        'status' => 'unseen',
                        'send_time' => time(),
                        'record_id' => $request->input('job_id'),
                        'title' => 'Welfare call'
                    ]);
            //     }
            // }

        //push notification
        $admins = DB::table('users')->where('notification_token', '!=', '')->select('notification_token')->get();
        foreach($admins as $a)
        {
            $notification_data = [
                'message' => $guard->first_name.' '.$guard->last_name.' submitted Welfare Call.',
                'title' => 'Job Signin',
                'fcm_token' => $a->notification_token,
                'page' => 'homepage',
                'roster_id' =>  $id
            ]; 
            $this->notification->new_app_send_push_notification($notification_data);
        }

        $this->statusCode = self::STATUS_CODE_200;
        $this->response = [
            'success' => true,
            'message' => 'Welfare call log successfully!',
            'coordinates' => $this->request->input('coordinates'),
            'job_id' => $this->request->input('job_id'),
            'guard_id' => $id
        ];
    }else{
        $this->statusCode = self::STATUS_CODE_200;
        $this->response = [
            'success' => false,
            'message' => 'Fail to log Welfare call!',
            'coordinates' => $this->request->input('coordinates'),
            'job_id' => $this->request->input('job_id'),
            'guard_id' => $id
        ];
    }
    return $this->sendResponse();
}

function distance($lat1, $lon1, $lat2, $lon2)
{   
    $lat1 = doubleval($lat1);
    $lon1 = doubleval($lon1);
    $lat2 = doubleval($lat2);
    $lon2 = doubleval($lon2);

    $theta = $lon1 - $lon2;
    $dist = sin(deg2rad($lat1)) * sin(deg2rad($lat2)) + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta));
    $dist = acos($dist);
    $dist = rad2deg($dist);
    $miles = $dist * 60 * 1.1515;
    $miles = $miles * 1.609;
    return $miles;
}

// old one
// public function saveGuardLocation(Request $request, $id)
// {
//     $this->request = $request;
//     $in_radius = true;
//     $this->setValidationRules(['time' => 'required', 'location' => 'required', 'jobId' => 'required']);
//     if ($this->isValidRequest()) {
//         $this->response = ['success' => false, 'error' => $this->getErrors()];
//         $this->statusCode = self::STATUS_CODE_200;
//         return $this->sendResponse();
//     }
//     $job = new JobResource($this->repo->getJobById($request->input('jobId'), $this->currentUser->id));

//     $guard = DB::table('guards')->where('id', $job->jobRoster[0]->guard_id)->first();

//     if ($job->petrol_site == 1) {
//         $j = DB::table('job_rosters')
//                 ->where('id', '=', request()->route('id'))
//                 ->first();
//         $this->statusCode = self::STATUS_CODE_200;
//         $this->response = [
//             'success' => true,
//             'message' => 'Event Log successfull.',
//             'inRadius' => $in_radius,
//             'signin_status' => $j->signin_status,
//         ];
//         return $this->sendResponse();
//     }
//     if (!$request->has('location') || $request->location == '') {
//         $coordinates = explode(',', '0,0');
//     }else{
//         $coordinates = explode(',', $request->input('location'));
//     }
//     $coordinates1 = explode(',', $job->coordinates);
//     $internet_enabled = true;
//     if ($request->has('internet_enabled') && ($request->internet_enabled == 'false' || $request->internet_enabled == 'false')) {
//         $internet_enabled = false;
//     }

//         // print_r($job->coordinates);
//     $distance = $this->distance(trim($coordinates[0]), trim($coordinates[1]), trim($coordinates1[0]), trim($coordinates1[1]) );
//     if($job->alert_radius > 0){
//         $alert_radius = $job->alert_radius/1000;
//     }else{
//         $alert_radius = 0.18;
//     }
//     $signin_activity = DB::table('job_roster_activites')
//     ->where(['guard_id' => $job->jobRoster[0]->guard_id, 'job_roster_id' => $id, 'status' => 1])
//     ->first();
//     $same_time = false;
//         // || ($request->has('appClose') && ($request->appClose == true || $request->appClose == 'true') && $request->appClose != false && $request->appClose != 'false')
//     if ($request->has('location_enabled') && ($request->location_enabled == 'false' || $request->location_enabled == 'false')) {

//         if (($request->location_enabled == 'false' || $request->location_enabled == 'false')) {
//                 // $same_time = true;
//             DB::table('job_roster_activites')->where(['guard_id' => $job->jobRoster[0]->guard_id, 'job_roster_id' => $id, 'status' => 1])->update(['last_location_time' => $request->timestamp, 'last_location' => $request->location]);
//         }
//             // elseif($request->has('appClose') && ($request->appClose == true || $request->appClose == 'true') && $request->appClose != false && $request->appClose != 'false'){
//             //     $same_time = true;
//             // }
//     }
//                 // if ($request->has('appClose') && ($request->appClose == true || $request->appClose == 'true') && $request->appClose != false && $request->appClose != 'false') {
//             // $guard = DB::table('guards')->where('id', $job->jobRoster[0]->guard_id)->first();
//             // $job1 = DB::table('jobs')->where('id', $request->input('jobId'))->first();
//                 // $same_time = true;

//             // $notification = array(
//             //     'guard_id' => $job->jobRoster[0]->guard_id, 
//             //     'record_id' => $id,
//             //     'message' => $guard->name.' just closed their app at '. $job1->site_name,
//             //     'type' => 'leave_location',
//             //     'send_time' => time(),
//             //     'title' => 'Guard close their App'
//             // );
//             // DB::table('portal_notifications')->insert($notification);

//             // DB::table('roster_complete_activity')->insert([
//             //     'roster_id' => $id,
//             //     'activity' => $guard->name.' just closed their app at location.',
//             //     'type' => 'leave_location',
//             //     'record_id' =>  $id,
//             //     'activity_time' => time(),
//             //     'activity_by' => $job->jobRoster[0]->guard_id
//             //     ]);
//         // }else
//     if($internet_enabled == true && $request->location != '0,0' && ($distance > $alert_radius && $job->jobRoster[0]->break_status == 0 && !empty($signin_activity))) {
//         // if(($distance > $alert_radius && $job->jobRoster[0]->break_status == 0 && !empty($signin_activity)) && $same_time == false){
//         $this->guardJobActivityRepo->insert([
//             'roster_id' => $id,
//             'guard_id' => $job->jobRoster[0]->guard_id,
//             'job_id' => $request->input('jobId'),
//             'coordinates' => $request->input('location'),
//             'event_time' => $request->input('time'),
//             'distance' => round($distance, 2),
//             'seen_status' => 'unseen'
//         ]);
//         $in_radius = false;

//         $guard = DB::table('guards')->where('id', $job->jobRoster[0]->guard_id)->first();
//         $job1 = DB::table('sites')->where('id', $request->input('jobId'))->first();
//         $notification = array(
//             'guard_id' => $job->jobRoster[0]->guard_id, 
//             'record_id' => $id,
//             'message' => $guard->name.' leave their location at '. $job1->site_name,
//             'type' => 'leave_location',
//             'send_time' => time(),
//             'title' => 'Guard Leave their Site'
//         );
//             // DB::table('portal_notifications')->insert($notification);

//             // DB::table('roster_complete_activity')->insert([
//             //     'roster_id' => $id,
//             //     'activity' => $guard->name.' leave their location.',
//             //     'type' => 'leave_location',
//             //     'record_id' =>  $id,
//             //     'activity_time' => time(),
//             //     'activity_by' => $job->jobRoster[0]->guard_id
//             //     ]);

//             DB::table('portal_notifications')->insert($notification);

//             //push notification
//             $admins = DB::table('users')->where('notification_token', '!=', '')->select('notification_token')->get();
//             foreach($admins as $a)
//             {
//                 $notification_data = [
//                     'message' => $guard->first_name.' '.$guard->last_name.'leave their location at ',
//                     'title' => 'leave_location',
//                     'fcm_token' => $a->notification_token,
//                     'page' => 'homepage',
//                     'roster_id' =>  $id
//                 ]; 
//                 $this->notification->new_app_send_push_notification($notification_data);
//             }


//             DB::table('roster_complete_activity')->insert([
//                 'roster_id' => $id,
//                 'activity' => $guard->first_name.' '.$guard->last_name.' just closed their app at location.',
//                 'type' => 'leave_location',
//                 'record_id' =>  $id,
//                 'activity_time' => time(),
//                 'activity_by' => $job->jobRoster[0]->guard_id
//             ]);

//     }elseif ($same_time) {
//         $job1 = DB::table('sites')->where('id', $request->input('jobId'))->first();
//         $notification = array(
//             'guard_id' => $job->jobRoster[0]->guard_id, 
//             'record_id' => $id,
//             'message' => $guard->name.' maybe turned off their GPS or close the app at '. $job1->site_name,
//             'type' => 'leave_location',
//             'send_time' => time(),
//             'title' => 'Guard turned off GPS or close their app'
//         );
//          //push notification
//          $admins = DB::table('users')->where('notification_token', '!=', '')->select('notification_token')->get();
//          foreach($admins as $a)
//          {
//              $notification_data = [
//                  'message' => $guard->first_name.' '.$guard->last_name.'maybe turned off their GPS or close the app at  ',
//                  'title' => 'leave_location',
//                  'fcm_token' => $a->notification_token,
//                  'page' => 'homepage',
//                  'roster_id' =>  $id
//              ]; 
//              $this->notification->new_app_send_push_notification($notification_data);
//          }
         
//             // DB::table('portal_notifications')->insert($notification);

//             DB::table('roster_complete_activity')->insert([
//                 'roster_id' => $id,
//                 'activity' => $guard->first_name.' '.$guard->last_name.' maybe turned off their GPS or close the app.',
//                 'type' => 'leave_location',
//                 'record_id' =>  $id,
//                 'activity_time' => time(),
//                 'activity_by' => $job->jobRoster[0]->guard_id
//                 ]);

//     }elseif($internet_enabled == true && $request->location == '0,0')
//     {
//         $guard = DB::table('guards')->where('id', $job->jobRoster[0]->guard_id)->first();
//         $job1 = DB::table('sites')->where('id', $request->input('jobId'))->first();
//         $notification = array(
//             'guard_id' => $job->jobRoster[0]->guard_id, 
//             'record_id' => $id,
//             'message' => 'Due to some reason we are not able to track guard at '. $job1->site_name,
//             'type' => 'internet',
//             'send_time' => time(),
//             'title' => 'Technical Issue'
//         );
//             // DB::table('portal_notifications')->insert($notification);

//             DB::table('roster_complete_activity')->insert([
//                 'roster_id' => $id,
//                 'activity' => 'Due to some reason we are not able to track guard.',
//                 'type' => 'internet',
//                 'record_id' =>  $id,
//                 'activity_time' => time(),
//                 'activity_by' => $job->jobRoster[0]->guard_id
//                 ]);


                
//             //push notification
//        $admins = DB::table('users')->where('notification_token', '!=', '')->select('notification_token')->get();
//        foreach($admins as $a)
//        {
//            $notification_data = [
//                'message' => $guard->first_name.' '.$guard->last_name.' Due to some reason we are not able to track guard at.',
//                'title' => 'Job Signin',
//                'fcm_token' => $a->notification_token,
//                'page' => 'homepage',
//                'roster_id' =>  $id
//            ]; 
//            $this->notification->new_app_send_push_notification($notification_data);
//        } 
//     }

//     $j = DB::table('job_rosters')
//     ->where('id', '=', request()->route('id'))
//     ->first(); 

//     $this->statusCode = self::STATUS_CODE_200;
//     $this->response = [
//         'success' => true,
//         'message' => 'Event Log successfull.',
//         'inRadius' => $in_radius,
//         'signin_status' => $j->signin_status,
//     ];
//     return $this->sendResponse();
// }

// new one

public function saveGuardLocation(Request $request, $id)
{
    $this->request = $request;
    $in_radius = true;
    $this->setValidationRules(['time' => 'required', 'location' => 'required', 'jobId' => 'required']);
    if ($this->isValidRequest()) {
        $this->response = ['success' => false, 'error' => $this->getErrors()];
        $this->statusCode = self::STATUS_CODE_200;
        return $this->sendResponse();
    }

    $job = new JobResource($this->repo->getJobById($request->input('jobId'), $this->currentUser->id));



    $guard = DB::table('guards')->where('id', $job->jobRoster[0]->guard_id)->first();

    if ($job->petrol_site == 1) {
        $j = DB::table('job_rosters')
                ->where('id', '=', request()->route('id'))
                ->first();
        $this->statusCode = self::STATUS_CODE_200;
        $this->response = [
            'success' => true,
            'message' => 'Event Log successfull. 1',
            'inRadius' => $in_radius,
            'signin_status' => $j->signin_status,
        ];
        return $this->sendResponse();
    }
    
    $last_send_notification = Guard::where('id', $job->jobRoster[0]->guard_id)->value('last_send_notification');
    $diff = 4;
    if($last_send_notification != null) {
        $to_time = time();
        $from_time = $last_send_notification;
        $diff = round(abs($to_time - $from_time) / 60,2);
    }
    Guard::where('id', $job->jobRoster[0]->guard_id)->update(['last_seen' => time()]);
    if (!$request->has('location') || $request->location == '') {
        $coordinates = explode(',', '0,0');
    }else{
        $coordinates = explode(',', $request->input('location'));
    }
    $coordinates1 = explode(',', $job->coordinates);
    $internet_enabled = true;
    if ($request->has('internet_enabled') && ($request->internet_enabled == 'false' || $request->internet_enabled == 'false')) {
        $internet_enabled = false;
    }

        // print_r($job->coordinates);
    $distance = $this->distance(trim($coordinates[0]), trim($coordinates[1]), trim($coordinates1[0]), trim($coordinates1[1]) );
    if($job->alert_radius > 0){
        $alert_radius = $job->alert_radius/1000;
    }else{
        $alert_radius = 0.18;
    }
    $signin_activity = DB::table('job_roster_activites')
    ->where(['guard_id' => $job->jobRoster[0]->guard_id, 'job_roster_id' => $id, 'status' => 1])
    ->first();
    $same_time = false;
        // || ($request->has('appClose') && ($request->appClose == true || $request->appClose == 'true') && $request->appClose != false && $request->appClose != 'false')
    if($request->has('location_enabled') && ($request->location_enabled == 'false' || $request->location_enabled == 'false')) {

        if (($request->location_enabled == 'false' || $request->location_enabled == 'false')) {
                // $same_time = true;
            DB::table('job_roster_activites')->where(['guard_id' => $job->jobRoster[0]->guard_id, 'job_roster_id' => $id, 'status' => 1])->update(['last_location_time' => $request->timestamp, 'last_location' => $request->location]);
        }
            // elseif($request->has('appClose') && ($request->appClose == true || $request->appClose == 'true') && $request->appClose != false && $request->appClose != 'false'){
            //     $same_time = true;
            // }
    }
                // if ($request->has('appClose') && ($request->appClose == true || $request->appClose == 'true') && $request->appClose != false && $request->appClose != 'false') {
            // $guard = DB::table('guards')->where('id', $job->jobRoster[0]->guard_id)->first();
            // $job1 = DB::table('jobs')->where('id', $request->input('jobId'))->first();
                // $same_time = true;

            // $notification = array(
            //     'guard_id' => $job->jobRoster[0]->guard_id, 
            //     'record_id' => $id,
            //     'message' => $guard->name.' just closed their app at '. $job1->site_name,
            //     'type' => 'leave_location',
            //     'send_time' => time(),
            //     'title' => 'Guard close their App'
            // );
            // DB::table('portal_notifications')->insert($notification);

            // DB::table('roster_complete_activity')->insert([
            //     'roster_id' => $id,
            //     'activity' => $guard->name.' just closed their app at location.',
            //     'type' => 'leave_location',
            //     'record_id' =>  $id,
            //     'activity_time' => time(),
            //     'activity_by' => $job->jobRoster[0]->guard_id
            //     ]);
        // }else
    if($internet_enabled == true && $request->location != '0,0' && ($distance > $alert_radius && $job->jobRoster[0]->break_status == 0 && !empty($signin_activity))) {
        // if(($distance > $alert_radius && $job->jobRoster[0]->break_status == 0 && !empty($signin_activity)) && $same_time == false){
        $this->guardJobActivityRepo->insert([
            'roster_id' => $id,
            'guard_id' => $job->jobRoster[0]->guard_id,
            'job_id' => $request->input('jobId'),
            'coordinates' => $request->input('location'),
            'event_time' => $request->input('time'),
            'distance' => round($distance, 2),
            'seen_status' => 'unseen'
        ]);
        $in_radius = false;

        $guard = DB::table('guards')->where('id', $job->jobRoster[0]->guard_id)->first();
        
        
        $roster = DB::table('job_rosters')->where('id', $id)->first();
        $job1 = DB::table('sites')->where('id', $roster->site_id)->first();
        
        $main_roster = DB::table('job_new_roster')->where('id', $roster->roster_id)->first();
        // $admins = DB::table('users')->where('status', 'active')->get();
        // if(count($admins) > 0){
        //     foreach ($admins as $key => $value) {
                $notification = array(
                    'roster' => !empty($main_roster->id) ? $main_roster->id : null,
                    'guard_id' => $job->jobRoster[0]->guard_id, 
                    'record_id' => $id,
                    'message' =>  $guard->first_name.' '.$guard->last_name.'  leave their location at '. $job1->site_name,
                    'type' => 'leave_location',
                    'send_time' => time(),
                    'title' => 'Guard Leave their Site',
                    // 'send_to' => $value->id,
                );
                DB::table('portal_notifications')->insert($notification);
        //     }
        // }
            // DB::table('portal_notifications')->insert($notification);

            // DB::table('roster_complete_activity')->insert([
            //     'roster_id' => $id,
            //     'activity' => $guard->name.' leave their location.',
            //     'type' => 'leave_location',
            //     'record_id' =>  $id,
            //     'activity_time' => time(),
            //     'activity_by' => $job->jobRoster[0]->guard_id
            //     ]);

            

            //push notification
            $admins = DB::table('users')->where('notification_token', '!=', '')->select('notification_token')->get();
            foreach($admins as $a)
            {
                $notification_data = [
                    'message' => $guard->first_name.' '.$guard->last_name.'leave their location at ',
                    'title' => 'leave_location',
                    'fcm_token' => $a->notification_token,
                    'page' => 'homepage',
                    'roster_id' =>  $id
                ]; 
                $this->notification->new_app_send_push_notification($notification_data);
            }


            DB::table('roster_complete_activity')->insert([
                'roster_id' => $id,
                'activity' => $guard->first_name.' '.$guard->last_name.' just closed their app at location.',
                'type' => 'leave_location',
                'record_id' =>  $id,
                'activity_time' => time(),
                'activity_by' => $job->jobRoster[0]->guard_id
            ]);

    }elseif ($same_time) {
        $roster = DB::table('job_rosters')->where('id', $id)->first();
        $job1 = DB::table('sites')->where('id', $roster->site_id)->first();
        $notification = array(
            'guard_id' => $job->jobRoster[0]->guard_id, 
            'record_id' => $id,
            'message' => $guard->first_name.' '.$guard->last_name.' maybe turned off their GPS or close the app at '. $job1->site_name,
            'type' => 'leave_location',
            'send_time' => time(),
            'title' => 'Guard turned off GPS or close their app'
        );
         //push notification
         $admins = DB::table('users')->where('notification_token', '!=', '')->select('notification_token')->get();
         foreach($admins as $a)
         {
             $notification_data = [
                 'message' => $guard->first_name.' '.$guard->last_name.'maybe turned off their GPS or close the app at  ',
                 'title' => 'leave_location',
                 'fcm_token' => $a->notification_token,
                 'page' => 'homepage',
                 'roster_id' =>  $id
             ]; 
             $this->notification->new_app_send_push_notification($notification_data);
         }
         
            // DB::table('portal_notifications')->insert($notification);

            DB::table('roster_complete_activity')->insert([
                'roster_id' => $id,
                'activity' => $guard->first_name.' '.$guard->last_name.' maybe turned off their GPS or close the app.',
                'type' => 'leave_location',
                'record_id' =>  $id,
                'activity_time' => time(),
                'activity_by' => $job->jobRoster[0]->guard_id
                ]);

    }elseif($internet_enabled == true && $request->location == '0,0')
    {
        $guard = DB::table('guards')->where('id', $job->jobRoster[0]->guard_id)->first();
        $job1 = DB::table('sites')->where('id', $request->input('jobId'))->first();
        $roster = DB::table('job_rosters')->where('id', $id)->first();
        $main_roster = DB::table('job_new_roster')->where('id', $roster->roster_id)->first();
        // $admins = DB::table('users')->where('status', 'active')->get();
        // if(count($admins) > 0){
        //     foreach ($admins as $key => $value) {
                $notification = array(
                    'roster' => !empty($main_roster->id) ? $main_roster->id : null,
                    'guard_id' => $job->jobRoster[0]->guard_id, 
                    'record_id' => $id,
                    'message' => 'Due to some reason we are not able to track guard at '. $job1->site_name,
                    'type' => 'internet',
                    'send_time' => time(),
                    'title' => 'Technical Issue',
                    // 'send_to' => $value->id,
                );
                if ($diff > 3) {
                    User::where('id', $job->jobRoster[0]->guard_id)->update(['last_send_notification' => time()]);
                    DB::table('portal_notifications')->insert($notification);
                }
        //     }
        // }
            // DB::table('portal_notifications')->insert($notification);

            DB::table('roster_complete_activity')->insert([
                'roster_id' => $id,
                'activity' => 'Due to some reason we are not able to track guard.',
                'type' => 'internet',
                'record_id' =>  $id,
                'activity_time' => time(),
                'activity_by' => $job->jobRoster[0]->guard_id
                ]);


                
            //push notification
       $admins = DB::table('users')->where('notification_token', '!=', '')->select('notification_token')->get();
       foreach($admins as $a)
       {
           $notification_data = [
               'message' => $guard->first_name.' '.$guard->last_name.' Due to some reason we are not able to track guard at.',
               'title' => 'Job Signin',
               'fcm_token' => $a->notification_token,
               'page' => 'homepage',
               'roster_id' =>  $id
           ]; 
           $this->notification->new_app_send_push_notification($notification_data);
       } 
    }
    Guard::where('id', $job->jobRoster[0]->guard_id)->update(['in_radius' => $in_radius]);

    $j = DB::table('job_rosters')
    ->where('id', '=', request()->route('id'))
    ->first(); 

    $this->statusCode = self::STATUS_CODE_200;
    $this->response = [
        'success' => true,
        'message' => 'Event Log successfull. 2',
        'inRadius' => $in_radius,
        'signin_status' => $j->signin_status,
    ];
    return $this->sendResponse();
}



public function report_new_incident(Request $request, $id) {

    $this->request = $request;
    $this->setValidationRules(['guard_id' => 'required', 'incident_date' => 'required', 'incident_time' => 'required', 'incident_type' => 'required', 'physical_contact_involved' => 'required', 'injury' => 'required', 'people_involved' => 'required', 'recovery_value' => 'required', 'loss_value' => 'required']);
    if ($this->isValidRequest()) {
        $this->response = ['success' => false, 'error' => $this->getErrors()];
        $this->statusCode = self::STATUS_CODE_200;
        return $this->sendResponse();
    }

    $media = array();
    if ($this->request->input('photo')) {
        $photo = json_decode($this->request->input('photo'), true);
        if (is_array($photo) && !empty($photo)) {
            foreach ($photo as $key => $value) {
                $media[] = $this->uploader_base64($value, 'incident');
            }
        }else{
            $media[] = $this->uploader_base64($this->request->input('photo'), 'incident');

        }
    }
    $signature = '';
    if ($this->request->input('signature')) {
        $signature = $this->uploader_base64($this->request->input('signature'), 'incident');
    }
        //$roster = DB::table('job_new_roster')->where('roster_id', $id)->select('site_id')->first();

        //$id = $roster->site_id;
    $data = array(
        'job_id' => $id,
        'guard_id' => $this->request->input('guard_id'),
        'incident_date' => $this->request->input('incident_date'),
        'incident_time' => $this->request->input('incident_time'),
        'incident_type' => $this->request->input('incident_type'), 
        'type' => $this->request->input('incident_type'), 
        'physical_contact_involved' => $this->request->input('physical_contact_involved'),
        'weapon_involved' => $this->request->input('weapon_involved'), 
        'people_involved' => $this->request->input('people_involved'), 
        'injury' => $this->request->input('injury'), 
        'recovery_value' => $this->request->input('recovery_value'), 
        'loss_value' => $this->request->input('loss_value'), 
        'image' => json_encode($media),
        'signature' => $signature,
        'description' => $this->request->input('description'), 
        'detail' => $this->request->input('description'),
        'exit' => $this->request->input('exit'),
        'date_added' => time(), 
        'notified_to' => '', 
        'injuries_incurred' => $this->request->input('injury'), 
        'damage' => '', 
        'action_taken' => '', 
        'detailed_description' => $this->request->input('description'),
        'roster_id' => $this->request->input('roster_id')
    );

    $guard = DB::table('guards')->where('id', $this->request->input('guard_id'))->first();
    $job = DB::table('jobs')->where('id', $id)->first();
    $roster = DB::table('job_rosters')->where('id', $id)->first();
    $main_roster = DB::table('job_new_roster')->where('id', $roster->roster_id)->first();

    // $admins = DB::table('users')->where('status', 'active')->get();
    // if(count($admins) > 0){
    //     foreach ($admins as $key => $value) {

            $notification = array(
                'roster' => !empty($main_roster->id) ? $main_roster->id : null,
                'guard_id' => $this->request->input('guard_id'), 
                'record_id' => $this->request->input('roster_id'),
                'message' => $guard->first_name.' '.$guard->last_name.' report new incident at '. $job->site_name,
                'type' => 'incident_report',
                'send_time' => time(),
                'title' => 'Incident Report'
            );
            DB::table('portal_notifications')->insert($notification);
    //     }
    // }


    if($this->request->input('physical_contact_involved') == 'yes')
    {
        $data['physical_contact_details'] = $this->request->input('physical_contact_detail');
    }else{
            // $data['physical_contact_details'] = '';
    }
    if($this->request->input('weapon_involved') == 'yes')
    {
        $data['weapon_types'] = $this->request->input('weapon_types');
    }else{
            // $data['weapon_types'] = '';
    }
    if($this->request->input('injury') == 'yes')
    {
        $data['injurie_details'] = $this->request->input('injurie_details');
    }else{
            // $data['injurie_details'] = '';
    }

    $job_incident_report_id = DB::table('job_incident_reports')->insertGetId($data);

    DB::table('job_roster_activities')->where('job_roster_id', $this->request->input('roster_id'))->where('guard_id', $this->request->input('guard_id'))->update(['job_incident_report_id' => $job_incident_report_id]);
    $people_involved_detail = json_decode($this->request->input('people_involved_detail'), true);
        // print_r($people_involved_detail);
        // exit();
    if($this->request->input('people_involved') > 0){
        foreach ($people_involved_detail as $details) {
            DB::table('job_incident_report_details')->insert([
                'job_incident_report_id' => $job_incident_report_id,
                'description' => $details['description'],
                'clothing_bottom' => $details['clothing_bottom'],
                'facial_hair' => $details['facial_hair'],
                'hair' => $details['hair'], 
                'height' => $details['height'],
                'build' => $details['build'],
                'gender' => $details['gender'],
                'age' => $details['age'],
                'appearace' => $details['appearace'],
                'dob' => $details['dob'],
                'phone' => $details['phone'],
                'name' => $details['name'],
                'was_involved_as' => $details['was_involved_as']
            ]);
        }
    }
    DB::table('roster_complete_activity')->insert([
        'roster_id' => $this->request->input('roster_id'),
        'activity' => $guard->first_name.' '.$guard->last_name.' report new incident',
        'type' => 'incident_report',
        'record_id' => $job_incident_report_id,
        'activity_time' => time(),
        'activity_by' => $this->request->input('guard_id')
    ]);
    $this->statusCode = self::STATUS_CODE_200;
    $this->response = [
        'success' => true,
        'message' => 'Incident reported successfully!'
    ];
    return $this->sendResponse();

}

public function update_leave_request(Request $request, $action,  $id) {
    $this->request = $request;

    $leave_data = DB::table('guard_leave_requests')->where('id', $this->request->input('leave_id'))->first();
    if($leave_data->status == 'approved')
    {
        $this->response = ['success' => false, 'error' => "Sorry, you can't do anything now!"];
        $this->statusCode = self::STATUS_CODE_200;
        return $this->sendResponse();
    }
    if($action == 'delete')
    {
        DB::table('guard_leave_requests')->where('id', $this->request->input('leave_id'))->delete(); 
    }else{
        $this->setValidationRules(['start_date' => 'required', 'end_date' => 'required', 'notes' => 'required']);
        if ($this->isValidRequest()) {
            $this->response = ['success' => false, 'error' => $this->getErrors()];
            $this->statusCode = self::STATUS_CODE_200;
            return $this->sendResponse();
        }
        DB::table('guard_leave_requests')->where('id', $this->request->input('leave_id'))->update(['guard_id' => $id, 'start' => strtotime($this->request->input('start_date')), 'end' => strtotime($this->request->input('end_date')), 'start_date' => $this->request->input('start_date'), 'end_date' => $this->request->input('end_date'), 'notes' => $this->request->input('notes')]);
       $guard = DB::table('guards')->where('id', $id)->first();

       $updatedRecord = DB::table('guard_leave_requests')
        ->where('id', $this->request->input('leave_id'))
        ->first();

       DB::table('portal_notifications')->insert([
        //'roster' => !empty($main_roster->id) ? $main_roster->id : null,
        'guard_id' => $id,
        'message' => $guard->first_name.' '.$guard->last_name.' updated a leave request.',
        'type' => 'leave',
        'status' => 'unseen',
        'send_time' => time(),
        'record_id' => $updatedRecord->id,
        'title' => 'Update Staff Leave Request',
        // 'send_to' => $value->id,
        ]);
    }

        //push notification
        // $admins = DB::table('users')->where('notification_token', '!=', '')->select('notification_token')->get();
        // foreach($admins as $a)
        // {
        //     $notification_data = [
        //         'message' => $guard->first_name.' '.$guard->last_name.' signin in their job.',
        //         'title' => 'Job Signin',
        //         'fcm_token' => $a->notification_token,
        //         'page' => 'homepage',
        //         'roster_id' =>  $id
        //     ]; 
        //     $this->notification->new_app_send_push_notification($notification_data);
        // }
    
        $this->statusCode = self::STATUS_CODE_200;
        $this->response = [
            'success' => true,
            'message' => 'Leave request update successfully!'
        ];
        return $this->sendResponse();
    
}

public function getAsapJobs() {
    $results = $this->jobRosterRepo->getAsapJobs($this->currentUser->id);
    return new JobRosterCollection(JobRosterResource::collection($results));
}
public function accept_asap_job(Request $request, $id)
{
    $a = null;
    $b = '';
    $roster = DB::table('job_rosters')
    ->join('sites', 'sites.id', '=', 'job_rosters.site_id')
    ->where('job_rosters.asap', '=', '1')
    ->where('job_rosters.id', '=', $request->input('roster_id'))->where(
      function ($query) use ($a, $b) {
          return $query->where('job_rosters.guard_id', '=', $a)
          ->orWhere('job_rosters.guard_id', '=', $b);
      }
  )
    ->select('job_rosters.*', 'sites.id as jobId', 'sites.address', 'sites.coordinates')
    ->first();
    //   print_r($roster);
    //   exit();
    if($roster != null){
        $flag = 0;
        $is_already_assign = DB::table('job_rosters')
        ->where('guard_id', $id)
        ->whereBetween('start', [$roster->start, $roster->end])
        ->select('job_rosters.*')->first();
        if($is_already_assign != null){
            $flag = 1;
        }else{
            $is_already_assign = DB::table('job_rosters')
            ->where('guard_id', $id)
            ->whereBetween('end', [$roster->start, $roster->end])
            ->select('job_rosters.*')->first();
            if($is_already_assign != null){
                $flag = 1;
            }
        }
        if($flag == 0){
            DB::table('job_rosters')
            ->where('id', '=', $request->input('roster_id'))
            ->update(['guard_id' => $id, 'update_status' => 1, 'publish_status' => 1, 'job_status' => 'confirmed', 'last_update' => time()]);
            $guard = DB::table('guards')->where('id', $id)->first();
            $notification = array(
                'guard_id' => $id, 
                'record_id' => $request->input('task_id'),
                'message' => $guard->first_name.' '.$guard->last_name.' accepted and confirmed the job.',
                'type' => 'job_accept',
                'send_time' => time(),
                'title' => 'Accept ASAP Job',
            );


            DB::table('roster_complete_activity')->insert([
                'activity' => $guard->first_name.' '.$guard->last_name.' accepted and confirmed the job.',
                'type' => 'accept_asap_job',
                'record_id' => $id,
                'activity_time' => time(),
                'activity_by' => $id
            ]);

            //push notification
            $admins = DB::table('users')->where('notification_token', '!=', '')->select('notification_token')->get();
            foreach($admins as $a)
            {
                $notification_data = [
                    'message' => $guard->first_name.' '.$guard->last_name.' accepted and confirmed the job.',
                    'title' => 'Job Signin',
                    'fcm_token' => $a->notification_token,
                    'page' => 'homepage',
                    'roster_id' =>  $id
                ]; 
                $this->notification->new_app_send_push_notification($notification_data);
            }
        // DB::table('portal_notifications')->insert($notification);
            $this->statusCode = self::STATUS_CODE_200;
            $this->response = [
                'success' => true,
                'message' => 'Job accept successfully.'
            ];
        }else{
            $this->statusCode = self::STATUS_CODE_200;
            $this->response = [
                'success' => false,
                'message' => 'These timings are contradicting with other site timings because this guard is already added in another site.'
            ];
        }
    }else{
        $this->statusCode = self::STATUS_CODE_200;
        $this->response = [
            'success' => false,
            'message' => 'Job already accepted!'
        ];
    }
    return $this->sendResponse();
}
function auto_sign_out(Request $request)
{
    $current_time = time();
        // $closeTime = $current_time;
    $closeTime = $current_time + (60*60+24);
    $not_signout_shifts = DB::table('job_roster_activities')
    ->join('job_new_roster', 'job_roster_activities.job_roster_id', '=','job_new_roster.roster_id')
    ->where('job_roster_activities.status', 1)
    ->where('job_new_roster.temp_end','<=',date('Y-m-d H:i:s', $closeTime))
    ->select('job_roster_activities.id', 'job_roster_activities.job_roster_id', 'job_new_roster.temp_end')->get();
    foreach ($not_signout_shifts as $not_closed) {

        $job_end_time = $not_closed->temp_end;
        $job_end_time = strtotime($job_end_time);
        $current_time = time();
        $diff = round(($current_time - $job_end_time) / 60,2);
        if ($diff > 30) {
            DB::table('job_roster_activities')->where('id', $not_closed->id)->update(['signout_time' => date('D M d Y H:i:s'), 'auto_signout' => 1, 'status' => 0]);
            DB::table('job_new_roster')->where('roster_id', $not_closed->job_roster_id)->update(['job_status' => 'completed', 'signin_status' => 0]);
            DB::table('roster_complete_activity')->insert([
                'roster_id' => $not_closed->job_roster_id,
                'activity' => 'Auto signout from the job.',
                'type' => 'auto_signout',
                'record_id' => $not_closed->id,
                'activity_time' => time(),
                'activity_by' => ''
            ]);
            $this->guard_job_rating($not_closed->job_roster_id);

            // $activity = DB::table('job_roster_activites')
            // ->where('job_roster_id', $not_closed->job_roster_id)
            // ->select('id','signin_time', 'signout_time')
            // ->first();
            // $total_hours = $this->calCulateTotalHours($activity->signin_time, $request->signout_time);
            // $activity->authorized_hours = $total_hours;
            // $activity->save();
        }
    }
    $this->statusCode = self::STATUS_CODE_200;
    $this->response = [
        'success' => true,
        'message' => 'Signout successfully'
    ];
    return $this->sendResponse();
}
public function confirm_task(Request $request,$id)
{
   $this->setValidationRules(['task_id' => 'required', 'roster_id' => 'required', 'complete_time' => 'required', 'location' => 'required']);
   if ($this->isValidRequest()) {
    $this->response = ['success' => false, 'error' => $this->getErrors()];
    $this->statusCode = self::STATUS_CODE_200;
    return $this->sendResponse();
}
        // DB::table('job_new_roster')->where('roster_id', )->update(['job_status' => 'completed']);
DB::table('job_roster_tasks')
->where(['roster_id' => $request->input('roster_id'), 'id' => $request->input('task_id')])
->update(['guard_id' => $id, 'task_complete_time' => $request->input('complete_time'), 'status' => 'confirmed', 'location' => $request->input('location')]);
$guard = DB::table('guards')->where('id', $id)->first();
$roster = DB::table('job_rosters')->where('id', $id)->first();
$main_roster = DB::table('job_new_roster')->where('id', $roster->roster_id)->first();

// $admins = DB::table('users')->where('status', 'active')->get();
//     if(count($admins) > 0){
//         foreach ($admins as $key => $value) {
            $notification = array(
                'roster' => !empty($main_roster->id) ? $main_roster->id : null,
                'guard_id' => $id, 
                'record_id' => $request->input('roster_id'),
                'message' => $guard->first_name.' '.$guard->last_name.' confirmed its task.',
                'type' => 'task',
                'send_time' => time(),
                'title' => 'Task Confirmation',
                // 'send_to' => $value->id,
            );
            DB::table('portal_notifications')->insert($notification);
    //     }
    // }



$this->statusCode = self::STATUS_CODE_200;
$this->response = [
    'success' => true,
    'message' => 'Task confirmed successfully!'
];
return $this->sendResponse();
}
function time_into_decimal($time)
{
    $time = explode(':', $time);
    if (isset($time[1])) {
        $time[1] = $time[1]/60;
    }else{
        $time[1] = 0;
    }
    return ($time[0] * 1) + $time[1];
}

public function start_task(Request $request,$id)
{
   $this->setValidationRules(['task_id' => 'required', 'roster_id' => 'required', 'start_time' => 'required', 'location' => 'required']);
   if ($this->isValidRequest()) {
    $this->response = ['success' => false, 'error' => $this->getErrors()];
    $this->statusCode = self::STATUS_CODE_200;
    return $this->sendResponse();
}
$task = DB::table('job_roster_tasks')->where(['id' => $request->task_id, 'job_roster_id' => $request->roster_id])->first();
$guard = DB::table('guards')->where('id', $id)->first();
if ($guard->state != '') {
    config(['app.timezone' => $this->timezone[$guard->state]]);
    date_default_timezone_set($this->timezone[$guard->state]);
}

$current_time = $this->time_into_decimal(date('H:i', time()));
$task_time = $this->time_into_decimal(date('H:i', strtotime($task->task_start)));
$diff = ($current_time - $task_time) * 60;
if ($diff > 30) {
    $this->response = ['success' => false, 'error' => 'You can\'t start a task!'];
    $this->statusCode = self::STATUS_CODE_200;
    return $this->sendResponse();
}
$active_task = DB::table('job_roster_tasks')->where('start_time','!=', '')->where('end_time', '=', '')
        // ->where('guard_id', '=', $id)
->where('job_roster_id', '=', $request->input('roster_id'))->first();
if (!empty($active_task)) {
    $this->response = ['success' => false, 'error' => 'Please complete you previous task first!'];
    $this->statusCode = self::STATUS_CODE_200;
    return $this->sendResponse();
}
        // DB::table('job_new_roster')->where('roster_id', )->update(['job_status' => 'completed']);
        // 'guard_id' => $id, 

// DB::table('job_roster_tasks')
// ->where(['job_roster_id' => $request->input('roster_id'), 'id' => $request->input('task_id')])
// ->update(['start_time' => $request->input('start_time'), 'start_location' => $request->input('location')]);

    DB::table('job_roster_tasks')
    ->where(['job_roster_id' => $request->input('roster_id'), 'id' => $request->input('task_id')])
    ->update([
        'start_time' => $request->input('start_time'), // Make sure start_time is in a valid date/time format
        'start_location' => $request->input('location')
    ]);

$guard = DB::table('guards')->where('id', $id)->first();

$notification = array(
    'guard_id' => $id, 
    'record_id' => $request->input('roster_id'),
    'message' => $guard->first_name.' '.$guard->last_name.' start its task.',
    'type' => 'task',
    'send_time' => time(),
    'title' => 'Task start'
);
            // DB::table('portal_notifications')->insert($notification);

            DB::table('roster_complete_activity')->insert([
            'roster_id' => $request->input('roster_id'),
            'activity' => $guard->first_name.' '.$guard->last_name.' start its task.',
            'type' => 'start_task',
            'record_id' => $request->input('task_id'),
            'activity_time' => time(),
            'activity_by' => $id
            ]);

             //push notification
             $admins = DB::table('users')->where('notification_token', '!=', '')->select('notification_token')->get();
             foreach($admins as $a)
             {
                 $notification_data = [
                     'message' => $guard->first_name.' '.$guard->last_name.' start its task.',
                     'title' => 'start_task',
                     'fcm_token' => $a->notification_token,
                     'page' => 'homepage',
                     'roster_id' =>  $id
                 ]; 
                 $this->notification->new_app_send_push_notification($notification_data);
             }
$this->statusCode = self::STATUS_CODE_200;
$this->response = [
    'success' => true,
    'message' => 'Task start successfully!'
];
return $this->sendResponse();
}

public function end_task(Request $request,$id)
{

    // if($request->input('images') != null || $request->input('images') != ''){
        $imageArray = $request->input('images');
    // }

   $this->setValidationRules(['task_id' => 'required', 'roster_id' => 'required', 'end_time' => 'required', 'location' => 'required']);
   if ($this->isValidRequest()) {
    $this->response = ['success' => false, 'error' => $this->getErrors()];
    $this->statusCode = self::STATUS_CODE_200;
    return $this->sendResponse();
}
        // DB::table('job_new_roster')->where('roster_id', )->update(['job_status' => 'completed']);
DB::table('job_roster_tasks')
->where(['job_roster_id' => $request->input('roster_id'), 'id' => $request->input('task_id')])
->update(['end_time' => $request->input('end_time'), 'status' => 'completed', 'end_location' => $request->input('location'), 'note' => $request->input('task_message'), 'task_end_imgs' => !empty($imageArray) ? json_encode($imageArray): null ]);



$roster = DB::table('job_rosters')->where('id', $request->roster_id)->first();
$main_roster = DB::table('job_new_roster')->where('id', $roster->roster_id)->first();
$guard = DB::table('guards')->where('id', $id)->first();
// $admins = DB::table('users')->where('status', 'active')->get();
//             if(count($admins) > 0){
//                 foreach ($admins as $key => $value) {
                    $notification = array(
                        'roster' => !empty($main_roster->id) ? $main_roster->id : null,
                        'guard_id' => $id, 
                        'record_id' => $request->input('roster_id'),
                        'message' => $guard->first_name.' '.$guard->last_name.' completed its task.',
                        'type' => 'end_task',
                        'send_time' => time(),
                        'title' => 'Task Completion'
                    );
                    DB::table('portal_notifications')->insert($notification);
            //     }
            // }

            DB::table('roster_complete_activity')->insert([
            'roster_id' => $request->input('roster_id'),
            'activity' => $guard->first_name.' '.$guard->last_name.' completed its task.',
            'type' => 'end_task',
            'record_id' => $request->input('task_id'),
            'activity_time' => time(),
            'activity_by' => $id
            ]);
            //push notification
            $admins = DB::table('users')->where('notification_token', '!=', '')->select('notification_token')->get();
            foreach($admins as $a)
            {
                $notification_data = [
                    'message' => $guard->first_name.' '.$guard->last_name.' completed its task.',
                    'title' => 'end_task',
                    'fcm_token' => $a->notification_token,
                    'page' => 'homepage',
                    'roster_id' =>  $id
                ]; 
                $this->notification->new_app_send_push_notification($notification_data);
            }

$this->statusCode = self::STATUS_CODE_200;
$this->response = [
    'success' => true,
    'message' => 'Task completed successfully!'
];
return $this->sendResponse();
}

public function start_break(Request $request, $id)
{
    $this->setValidationRules(['notes' => 'required', 'roster_id' => 'required']);
    if ($this->isValidRequest()) {
        $this->response = ['success' => false, 'error' => $this->getErrors()];
        $this->statusCode = self::STATUS_CODE_200;
        return $this->sendResponse();
    }
    $guard = DB::table('guards')->where('id', $id)->first();
    if ($guard->state != '') {
        config(['app.timezone' => $this->timezone[$guard->state]]);
        date_default_timezone_set($this->timezone[$guard->state]);
    }
    $job_signin_details = DB::table('job_roster_activites')->where(['guard_id' => $id, 'job_roster_id' => $request->input('roster_id')])->first();

    if ($job_signin_details) {
        if ($job_signin_details->status == 0) {
            $this->response = ['success' => false, 'error' => 'You can\'t take a break!'];
            $this->statusCode = self::STATUS_CODE_200;
            return $this->sendResponse();    
        }else{
            $job_start_time = $job_signin_details->signin_time;
            $job_start_time = explode('GMT', $job_start_time);
            $job_start_time = strtotime($job_start_time[0]);
            $current_time = time();
            $diff = round(($current_time - $job_start_time) / (60 *60),2);
                // if ($diff < 4) {
                //     $this->response = ['success' => false, 'error' => 'You can take a break after 4 hours!'];
                //     $this->statusCode = self::STATUS_CODE_200;
                //     return $this->sendResponse();
                // }
        }

    }else{
        $this->response = ['success' => false, 'error' => 'You must be signin into you job to start break!'];
        $this->statusCode = self::STATUS_CODE_200;
        return $this->sendResponse();
    }

    $already_break = DB::table('job_breaks')->where(['guard_id' => $id, 'roster_id' => $request->input('roster_id')])->orderBy('id', 'desc')->first();
    if ($already_break) {
        if ($already_break->job_status == 1) {
            $this->response = ['success' => false, 'error' => 'You are already on a break!'];
            $this->statusCode = self::STATUS_CODE_200;
            return $this->sendResponse();
        }else{
            $job_start_time = $already_break->end_time;
            $job_start_time = strtotime($job_start_time);
            $current_time = time();
            $diff = round(($job_start_time - $current_time) / (60 *60),2);
            if ($diff < 4) {
                $this->response = ['success' => false, 'error' => 'You can take a break after 4 hours!'];
                $this->statusCode = self::STATUS_CODE_200;
                return $this->sendResponse();
            }
        }
    }
    $data = array(
        'roster_id' => $request->input('roster_id'),
        'guard_id' => $id,
        'start_time' => time(),
        'notes' => $request->input('notes'),
        'inform_to' => $request->input('inform'),
        'job_status' => 1, 
        'break_start_time' => time());
    DB::table('job_breaks')->insert($data);
    DB::table('job_rosters')->where(['id' => $request->input('roster_id'), 'guard_id' => $id])->update(['break_status' => 1]);

        DB::table('roster_complete_activity')->insert([
            'roster_id' => $request->input('roster_id'),
            'activity' => $guard->first_name.' '.$guard->middle_name.' '.$guard->last_name.' start break.',
            'type' => 'start_break',
            'record_id' => $request->input('roster_id'),
            'activity_time' => time(),
            'activity_by' => $id
            ]);

        //push notification
        $admins = DB::table('users')->where('notification_token', '!=', '')->select('notification_token')->get();
        foreach($admins as $a)
        {
            $notification_data = [
                'message' => $guard->first_name.' '.$guard->last_name.' start break.',
                'title' => 'start_break',
                'fcm_token' => $a->notification_token,
                'page' => 'homepage',
                'roster_id' =>  $id
            ]; 
            $this->notification->new_app_send_push_notification($notification_data);
        }
    $this->response = ['success' => True, 'message' => 'Break start successfully.'];
    $this->statusCode = self::STATUS_CODE_200;
    return $this->sendResponse();
}


public function end_break(Request $request, $id)
{
    $this->setValidationRules(['roster_id' => 'required']);
    if ($this->isValidRequest()) {
        $this->response = ['success' => false, 'error' => $this->getErrors()];
        $this->statusCode = self::STATUS_CODE_200;
        return $this->sendResponse();
    }
    $guard = DB::table('guards')->where('id', $id)->first();
    if ($guard->state != '') {
        config(['app.timezone' => $this->timezone[$guard->state]]);
        date_default_timezone_set($this->timezone[$guard->state]);
    }

    DB::table('job_breaks')->where(['roster_id' => $request->input('roster_id'), 'guard_id' => $id, 'job_status' => 1])->update(['job_status' => 0, 'end_time' => time(), 'break_end_time' => time(), 'break_end_notes' => ($request->has('notes') ? $request->notes : '')]);

    DB::table('job_rosters')->where(['id' => $request->input('roster_id'), 'guard_id' => $id])->update(['break_status' => 0]);

        DB::table('roster_complete_activity')->insert([
            'roster_id' => $request->input('roster_id'),
            'activity' => $guard->first_name.' '.$guard->middle_name.' '.$guard->last_name.' end its break.',
            'type' => 'end_break',
            'record_id' => $request->input('roster_id'),
            'activity_time' => time(),
            'activity_by' => $id
            ]);

        //push notification
        $admins = DB::table('users')->where('notification_token', '!=', '')->select('notification_token')->get();
        foreach($admins as $a)
        {
            $notification_data = [
                'message' => $guard->first_name.' '.$guard->last_name.' end its break.',
                'title' => 'end_break',
                'fcm_token' => $a->notification_token,
                'page' => 'homepage',
                'roster_id' =>  $id
            ]; 
            $this->notification->new_app_send_push_notification($notification_data);
        }
    $this->response = ['success' => True, 'message' => 'Break Completed.'];
    $this->statusCode = self::STATUS_CODE_200;
    return $this->sendResponse();

}
public function get_admin_list(Request $request)
{
    $admin = DB::table('administrators')->where(['status' => 'active', 'access_level' => 'administrator'])->select('administrators.id', 'administrators.name')->get();
    $this->response = ['success' => True, 'message' => 'Admin list', 'data' => $admin];
    $this->statusCode = self::STATUS_CODE_200;
    return $this->sendResponse();
}
public function reject_asap_job(Request $request, $id)
{
    $guard = DB::table('guards')->where('id', $id)->first();
    $id = DB::table('asap_jobs_rejected')->insertGetId(['roster_id' => $request->input('roster_id'), 'guard_id' => $id]);
    if($id != ''){
        $roster = DB::table('job_rosters')->where('id', $id)->first();
        $main_roster = DB::table('job_new_roster')->where('id', $roster->roster_id)->first();
        $notification = array(
            'roster' => !empty($main_roster->id) ? $main_roster->id : null,
            'guard_id' => $id, 
            'record_id' => $request->input('roster_id'),
            'message' => $guard->first_name.' '.$guard->last_name.' rejected ASAP job.',
            'type' => 'job_reject',
            'send_time' => time(),
            'title' => 'Reject ASAP Job'
        );

        //push notification
        $admins = DB::table('users')->where('notification_token', '!=', '')->select('notification_token')->get();
        foreach($admins as $a)
        {
            $notification_data = [
                'message' => $guard->first_name.' '.$guard->last_name.' rejected ASAP job.',
                'title' => 'Job Signin',
                'fcm_token' => $a->notification_token,
                'page' => 'homepage',
                'roster_id' =>  $request->input('roster_id')
            ]; 
            $this->notification->new_app_send_push_notification($notification_data);
        }

    $roster = DB::table('job_rosters')->where('id', $id)->first();
    $main_roster = DB::table('job_new_roster')->where('id', $roster->roster_id)->first();
    // $admins = DB::table('users')->where('status', 'active')->get();
    DB::table('roster_complete_activity')->insert([
        'roster' => !empty($main_roster->id) ? $main_roster->id : null,
        'roster_id' => $request->input('roster_id'),
        'record_id' => $request->input('roster_id'),
        'activity' => $guard->first_name.' '.$guard->last_name.' rejected ASAP job.',
        'type' => 'rejected_asap_job',
        'activity_time' => time(),
        'activity_by' => $id,
        // 'send_to' => $value->id,
    ]);
    // if(count($admins) > 0){
    //     foreach ($admins as $key => $value) {
            DB::table('portal_notifications')->insert($notification);
    //     }
    // }



        $this->statusCode = self::STATUS_CODE_200;
        $this->response = [
            'success' => true,
            'message' => 'Job reject successfully.'
        ];

    }else{
        $this->statusCode = self::STATUS_CODE_200;
        $this->response = [
            'success' => false,
            'message' => 'Job not rejected!'
        ];
    }
    return $this->sendResponse();
}

public function footPatrolReport(Request $request, $id) 
{
    $this->request = $request;
    if(!isset($id) || $id == 0){
        return response()->json([
            'success' => false,
            'message' => 'Site not found'
        ], 500);
    }
    $this->setValidationRules(['guard_id' => 'required', 'date' => 'required', 'time' => 'required']);
    if ($this->isValidRequest()) {
        $this->response = ['success' => false, 'error' => $this->getErrors()];
        $this->statusCode = self::STATUS_CODE_200;
        return $this->sendResponse();
    }

    $media = array();
    if ($this->request->input('photo')) {
        $photo = json_decode($this->request->input('photo'), true);
        if (is_array($photo) && !empty($photo)) {
            foreach ($photo as $key => $value) {
                $newObject = new \stdClass();
                // $newObject->imgPath = $this->uploader_base64($value['imgPath'], 'footpatrol');
                $newObject->imgPath = $value['imgPath'];
                $newObject->timestamp = $value['timestamp'];
                $media[] = $newObject;
            }
        }
    }
    $signature = '';
    if ($this->request->input('signature')) {
        $signature = $this->request->input('signature');
    }
    $data = array(
        'job_id' => $id,
        'guard_id' => $this->request->input('guard_id'),
        'roster_id' => $this->request->input('roster_id'),
        'date' => $this->request->input('date'),
        'time' => $this->request->input('time'),
        'fitness_to_travel' => $this->request->input('fitness_to_travel'),
        'patrolling_detail' => $this->request->input('patrolling_detail'),
        'site_name' => $this->request->input('site_name'), 
        'photo' => json_encode($media),
        'signature' => $signature
    );
    $job_patrol_report_id = DB::table('foot_patrol_reports')->insertGetId($data);

    $guard = DB::table('guards')->where('id', $this->request->input('guard_id'))->first();
    $job = DB::table('sites')->where('id', $id)->first();
    $roster = DB::table('job_rosters')->where('id', $this->request->input('roster_id'))->first();
    $main_roster = DB::table('job_new_roster')->where('id', $roster->roster_id)->first();

    // $admins = DB::table('users')->where('status', 'active')->get();
    // if(count($admins) > 0){
    //     foreach ($admins as $key => $value) {
            $notification = array(
                'roster' => !empty($main_roster->id) ? $main_roster->id : null,
                'guard_id' => $this->request->input('guard_id'), 
                'record_id' => $this->request->input('roster_id'),
                'message' => $guard->first_name.' '.$guard->last_name.' reports foot patrol at '. $job->site_name,
                'type' => 'foot_patrol_report',
                'send_time' => time(),
                'title' => 'Foot Patrol Report',
                // 'send_to' => $value->id,
            );
            DB::table('portal_notifications')->insert($notification);
    //     }
    // }
    DB::table('job_roster_activites')->where('job_roster_id', $this->request->input('roster_id'))->where('guard_id', $this->request->input('guard_id'))->update(['job_patrol_report_id' => $job_patrol_report_id]);
    DB::table('roster_complete_activity')->insert([
        'roster_id' => $this->request->input('roster_id'),
        'activity' => $guard->first_name.' '.$guard->last_name.' report new incident',
        'type' => 'foot_patrol_report',
        'record_id' => $job_patrol_report_id,
        'activity_time' => time(),
        'activity_by' => $this->request->input('guard_id')
    ]);
    //push notification
    $admins = DB::table('users')->where('notification_token', '!=', '')->select('notification_token')->get();
    foreach($admins as $a)
    {
        $notification_data = [
            'message' => $guard->first_name.' '.$guard->last_name.' report new foot patrol.',
            'title' => 'Foot Patrol Report',
            'fcm_token' => $a->notification_token,
            'page' => 'homepage',
            'roster_id' =>  $id
        ]; 
        $this->notification->new_app_send_push_notification($notification_data);
    }  
    $this->statusCode = self::STATUS_CODE_200;
    $this->response = [
        'success' => true,
        'message' => 'Foot Patrol reported successfully!'
    ];
    return $this->sendResponse();
}
public function report_incident_new(Request $request, $id) 
{
   $this->request = $request;
   $this->setValidationRules(['guard_id' => 'required', 'date' => 'required', 'time' => 'required']);
   if ($this->isValidRequest()) {
    $this->response = ['success' => false, 'error' => $this->getErrors()];
    $this->statusCode = self::STATUS_CODE_200;
    return $this->sendResponse();
}

$media = array();
if ($this->request->input('photo')) {
    $photo = json_decode($this->request->input('photo'), true);
    if (is_array($photo) && !empty($photo)) {
        foreach ($photo as $value) {
            $newObject = new \stdClass();
            $newObject->imgPath = $value['imgPath'] ?? '';
            $newObject->timestamp = $value['timestamp'] ?? '';
            $media[] = $newObject;
        }
    }
}
$signature = '';
if ($this->request->input('signature')) {
    $signature = $this->request->input('signature');
}
$data = array(
    'job_id' => $id,
    'guard_id' => $this->request->input('guard_id'),
    'roster_id' => $this->request->input('roster_id'),
    'incident_date' => $this->request->input('date'),
    'incident_time' => $this->request->input('time'),
    'site_name' => $this->request->input('site_name'), 
    'injury_type' => $this->request->input('injury_type'), 
    'injury_detail' => $this->request->input('incident_detail'),
    'people_involved' => $this->request->input('people_involved'), 
    'vehicle' => $this->request->input('vehicle'), 
    'emergency_services' => $this->request->input('emergency_services'), 
    'wittness' => $this->request->input('wittness'), 
    'photo' => json_encode($media),
    'signature' => $signature
);
$data['people_involved'] = str_replace('[{}]', '[]', $data['people_involved']);
$data['emergency_services'] = str_replace('[{}]', '[]', $data['emergency_services']);
$data['vehicle'] = str_replace('[{}]', '[]', $data['vehicle']);
$data['wittness'] = str_replace('[{}]', '[]', $data['wittness']);
$job_incident_report_id = DB::table('incident_reports')->insertGetId($data);

$guard = DB::table('guards')->where('id', $this->request->input('guard_id'))->first();
$job = DB::table('sites')->where('id', $id)->first();
$roster = DB::table('job_rosters')->where('id', $this->request->input('roster_id'))->first();
$main_roster = DB::table('job_new_roster')->where('id', $roster->roster_id)->first();

// $admins = DB::table('users')->where('status', 'active')->get();
//     if(count($admins) > 0){
//         foreach ($admins as $key => $value) {
            $notification = array(
                'roster' => !empty($main_roster->id) ? $main_roster->id : null,
                'guard_id' => $this->request->input('guard_id'), 
                'record_id' => $this->request->input('roster_id'),
                'message' => $guard->first_name.' '.$guard->last_name.' report new incident at '. $job->site_name,
                'type' => 'incident_report',
                'send_time' => time(),
                'title' => 'Incident Report',
                // 'send_to' => $value->id,
            );
            DB::table('portal_notifications')->insert($notification);
    //     }
    // }





DB::table('job_roster_activites')->where('job_roster_id', $this->request->input('roster_id'))->where('guard_id', $this->request->input('guard_id'))->update(['job_incident_report_id' => $job_incident_report_id]);


            DB::table('roster_complete_activity')->insert([
            'roster_id' => $this->request->input('roster_id'),
            'activity' => $guard->first_name.' '.$guard->last_name.' report new incident',
            'type' => 'incident_report',
            'record_id' => $job_incident_report_id,
            'activity_time' => time(),
            'activity_by' => $this->request->input('guard_id')
            ]);


            //push notification
            $admins = DB::table('users')->where('notification_token', '!=', '')->select('notification_token')->get();
            foreach($admins as $a)
            {
                $notification_data = [
                    'message' => $guard->first_name.' '.$guard->last_name.' report new incident.',
                    'title' => 'Incident Report',
                    'fcm_token' => $a->notification_token,
                    'page' => 'homepage',
                    'roster_id' =>  $id
                ]; 
                $this->notification->new_app_send_push_notification($notification_data);
            }
            
$this->statusCode = self::STATUS_CODE_200;
$this->response = [
    'success' => true,
    'message' => 'Incident reported successfully!'
];
return $this->sendResponse();
}

public function incident_report_updated(Request $request, $id) 
{
   $this->request = $request;
   $this->setValidationRules(['guard_id' => 'required', 'date' => 'required', 'time' => 'required']);
   if ($this->isValidRequest()) {
    $this->response = ['success' => false, 'error' => $this->getErrors()];
    $this->statusCode = self::STATUS_CODE_200;
    return $this->sendResponse();
}

$media = array();
if ($this->request->input('photo')) {
    $photo = json_decode($this->request->input('photo'), true);
    // $photo = $this->request->input('photo');
    if (is_array($photo) && !empty($photo)) {
        foreach ($photo as $key => $value) {
            $newObject = new \stdClass();
            $newObject->imgPath = $this->uploader_base64($value['imgPath'], 'incident');
            $newObject->timestamp = $value['timestamp'];
            $media[] = $newObject;
        }
    }
}
$signature = '';
if ($this->request->input('signature')) {
    // $signature = $this->uploader_base64($this->request->input('signature'), 'incident');
    $signature = $this->request->input('signature');
}
$data = array(
    'job_id' => $id,
    'guard_id' => $this->request->input('guard_id'),
    'roster_id' => $this->request->input('roster_id'),
    'incident_date' => $this->request->input('date'),
    'incident_time' => $this->request->input('time'),
    'site_name' => $this->request->input('site_name'), 
    'injury_type' => $this->request->input('injury_type'), 
    'injury_detail' => $this->request->input('incident_detail'),
    'people_involved' => $this->request->input('people_involved'), 
    'vehicle' => $this->request->input('vehicle'), 
    'emergency_services' => $this->request->input('emergency_services'), 
    'wittness' => $this->request->input('wittness'), 
    'photo' => json_encode($media),
    'signature' => $signature
);
$data['people_involved'] = str_replace('[{}]', '[]', $data['people_involved']);
$data['emergency_services'] = str_replace('[{}]', '[]', $data['emergency_services']);
$data['vehicle'] = str_replace('[{}]', '[]', $data['vehicle']);
$data['wittness'] = str_replace('[{}]', '[]', $data['wittness']);
$job_incident_report_id = DB::table('incident_reports')->insertGetId($data);

$guard = DB::table('guards')->where('id', $this->request->input('guard_id'))->first();
$job = DB::table('sites')->where('id', $id)->first();
$roster = DB::table('job_rosters')->where('id', $this->request->input('roster_id'))->first();
$main_roster = DB::table('job_new_roster')->where('id', $roster->roster_id)->first();

// $admins = DB::table('users')->where('status', 'active')->get();
//     if(count($admins) > 0){
//         foreach ($admins as $key => $value) {
            $notification = array(
                'roster' => !empty($main_roster->id) ? $main_roster->id : null,
                'guard_id' => $this->request->input('guard_id'), 
                'record_id' => $this->request->input('roster_id'),
                'message' => $guard->first_name.' '.$guard->last_name.' report new incident at '. $job->site_name,
                'type' => 'incident_report',
                'send_time' => time(),
                'title' => 'Incident Report',
                // 'send_to' => $value->id,
            );
            DB::table('portal_notifications')->insert($notification);
    //     }
    // }





DB::table('job_roster_activites')->where('job_roster_id', $this->request->input('roster_id'))->where('guard_id', $this->request->input('guard_id'))->update(['job_incident_report_id' => $job_incident_report_id]);


            DB::table('roster_complete_activity')->insert([
            'roster_id' => $this->request->input('roster_id'),
            'activity' => $guard->first_name.' '.$guard->last_name.' report new incident',
            'type' => 'incident_report',
            'record_id' => $job_incident_report_id,
            'activity_time' => time(),
            'activity_by' => $this->request->input('guard_id')
            ]);


            //push notification
            $admins = DB::table('users')->where('notification_token', '!=', '')->select('notification_token')->get();
            foreach($admins as $a)
            {
                $notification_data = [
                    'message' => $guard->first_name.' '.$guard->last_name.' report new incident.',
                    'title' => 'Incident Report',
                    'fcm_token' => $a->notification_token,
                    'page' => 'homepage',
                    'roster_id' =>  $id
                ]; 
                $this->notification->new_app_send_push_notification($notification_data);
            }
            
$this->statusCode = self::STATUS_CODE_200;
$this->response = [
    'success' => true,
    'message' => 'Incident reported successfully!'
];
return $this->sendResponse();
}

public function guard_rating($guard_id){
    $rating_sum=DB::table('job_new_roster')->where('guard_id',$guard_id)->sum('job_rating');
    $job=DB::table('job_new_roster')->where('guard_id',$guard_id)->count();
    $rating=round($rating_sum/$job);
    DB::table('guards')->where('id',$guard_id)->update([
        'rating'=>$rating
    ]);
        // return $rating;
    return true;
}
public function guard_job_rating($roster_id)
{
  $rating=0;
  $job=DB::table('job_new_roster')
  ->join('jobs', 'jobs.id', '=', 'job_new_roster.site_id')
  ->join('job_roster_activities', 'job_roster_activities.job_roster_id', '=', 'job_new_roster.roster_id')
  ->where('job_new_roster.roster_id',$roster_id)->first();
    ///signin
  if((strtotime($job->temp_start) >= strtotime($this->gmt_to_date($job->signin_time))) && $job->signin_time!=null ){
    $diff=strtotime($job->temp_start) - strtotime($this->gmt_to_date($job->signin_time));

    if($diff/60 >=15){
        $rating+=16.6;
    }
    else{
        $rating+=15;
    }
}else{
  $rating+=0;
}
    //signout

if((strtotime($job->temp_end) <= strtotime($this->gmt_to_date($job->signout_time))) && $job->signout_time!=null ){
    $rating+=16.6;
}else{
    if($job->signout_time==null || $job->signout_time=='' ){
        $rating+=0;
    }else{
        $rating+=15;
    }
}
    //   green  call 

$green=DB::table('green_call')->where('job_id',$roster_id)->count();
if($green==2){
    $rating+=33.2;
}elseif($green==1){
    $rating+=16.6;
}else{
    $rating+=0;
}
            //    welfare call 

$green=DB::table('welfare_call_data')->where('job_roster_id',$roster_id)->count();
if($green==0){
    $rating+=0;
}else{
    $rating+=16.6;
}

            //status
$status=DB::table('job_new_roster')->where('roster_id',$roster_id)->first();
if($status->job_status=="completed" || $status->job_status=="confirmed" ){
    $rating+=16.6;
}else{
    $rating+=0;
}
DB::table('job_new_roster')->where('roster_id',$roster_id)->update([
    'job_rating'=>$rating
]);
            // return $rating;
$guard=DB::table('job_new_roster')->where('roster_id',$roster_id)->first();
$this->guard_rating($guard->guard_id);
return true;

}

public function gmt_to_date($gmt)
{
    $result=(object)[];
    $result->signin_time=$gmt;
    if(strpos($result->signin_time,'GMT')!==false){
        $result->signin_time_2= explode(" ",$result->signin_time);
        $result->signin_time_2=$result->signin_time_2[3].'-'. date('m',strtotime($result->signin_time_2[1])).'-'.$result->signin_time_2[2].' '.$result->signin_time_2[4];
    }
    else{
        if(strpos($result->signin_time,'M')!==false||strpos($result->signin_time,'T')!==false||strpos($result->signin_time,'W')!==false||strpos($result->signin_time,'F')!==false ||strpos($result->signin_time,'S')!==false)
        {
            $result->signin_time_2= explode(" ",$result->signin_time);
            $result->signin_time_2=$result->signin_time_2[3].'-'. date('m',strtotime($result->signin_time_2[1])).'-'.$result->signin_time_2[2].' '.$result->signin_time_2[4];
        }
        else{
            $result->signin_time_2=$result->signin_time;
        }
    }
    return $result->signin_time_2;
}

  public function guestSignin(Request $request) {
        $this->request = $request;
        $this->setValidationRules(['signin_selfie' => 'required']);
        if ($this->isValidRequest()) {
            $this->response = ['success' => false, 'error' => $this->getErrors()];
            $this->statusCode = self::STATUS_CODE_200;
            return $this->sendResponse();
        }
        if ($request->has('signin_time')) {
            $current_time = $request->input('signin_time');
            $current_time = strtotime($current_time);
        }else{
            $current_time = time();
        }
        $field = 'signin_selfie'; 
        $media = '';
        $media = $this->uploader_base64($this->request->input($field));
        $id =  DB::table('guest_login')->insertGetId([
            'name' => $this->request->name,
            'signin_time' => date('Y-m-d H:i:s', time()),
            'signin_selfie' => $media,
            'status' => 0,
            'signin_notes' => ($this->request->input('signin_notes')) ? $this->request->input('signin_notes') : ''
        ]);        
        if ($id) {
            $this->statusCode = self::STATUS_CODE_200;
            $this->response = [
                'success' => true,
                'message' => 'Clocked-in Successfully!',
                'id' => $id
            ];
            return $this->sendResponse();
        }
        $this->statusCode = self::STATUS_CODE_200;
        $this->response = [
            'success' => false,
            'message' => 'You are not Checked-in!'
        ];
        return $this->sendResponse();
    }

     public function guestSignout(Request $request, $id) {
        $this->request = $request;
        $this->setValidationRules(['signout_selfie' => 'required']);
        if ($this->isValidRequest()) {
            $this->response = ['success' => false, 'error' => $this->getErrors()];
            $this->statusCode = self::STATUS_CODE_200;
            return $this->sendResponse();
        }
        $field = 'signout_selfie'; $media = '';
        $media = $this->uploader_base64($this->request->input($field));
        $model =  DB::table('guest_login')
        ->where('id', $id)
        ->update([
            'signout_time' => date('Y-m-d H:i:s', time()),
            'signout_selfie' => $media,
            'status' => 1,
            'signout_notes' => $this->request->input('signout_notes') ? $this->request->input('signout_notes') : ''
        ]);

        if ($model) {
            // $notification = array(
            //     'guard_id' => $this->currentUser->id, 
            //     'record_id' => $id,
            //     'message' => $guard->name.' signout from his/her job.',
            //     'type' => 'job_signout',
            //     'send_time' => time(),
            //     'title' => 'Job Signout'
            // );
            // $this->guard_job_rating($id);
            // $this->rosterCompleteActivityReport($id);
            // DB::table('portal_notifications')->insert($notification);
            $this->statusCode = self::STATUS_CODE_200;
            $this->response = [
                'success' => true,
                'message' => 'Clocked-out Successfully!'
            ];
            return $this->sendResponse();
        }
        $this->statusCode = self::STATUS_CODE_200;
        $this->response = [
            'success' => false,
            'message' => 'You are not Check-out.'
        ];
        return $this->sendResponse();
    }

      public function guestEmplyess() {
        $startOfToday = Carbon::today();
        $endOfToday = Carbon::tomorrow()->subSecond();
        $startOfOvernightShift = Carbon::yesterday()->setTime(18, 0);
        $cutoffForOvernightShift = Carbon::today()->setTime(6, 0);
        $guestsWhoLoggedInToday = DB::table('guest_login')
            ->where(function ($query) use ($startOfToday, $endOfToday) {
                $query->whereBetween('signin_time', [$startOfToday, $endOfToday])
                      ->where('status', 0);
            })
            ->orWhere(function ($query) use ($startOfOvernightShift, $cutoffForOvernightShift) {
                $query->whereBetween('signin_time', [$startOfOvernightShift, $cutoffForOvernightShift])
                      ->where('status', 0);
            })
            ->get();
        return response()->json([
            'success' => true,
            'data' => $guestsWhoLoggedInToday
        ]);
    }

    public function scanQR(Request $request, $id)
    {
        if (isset($id)) {

            $sites = DB::table('site_qr_codes')->where('unique_key', $id)->first();

            if (!$sites) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid QR Code'
                ]);
            }

            $data = [
                'status'      => 'completed',
                'scan_at'     => Carbon::now()->format('Y-m-d H:i:s'),
                'coordinates' => null,
                'guard_id'    => $request->guard_id,
                'roster_id'   => $request->roster_id,
                'site_id'     => $request->site_id,
                'value'       => $sites->unique_key,
                'name'        => $sites->name,
            ];

            DB::table('qr_scanners')->insert($data);

            return response()->json([
                'success' => true,
                'message' => 'QR Code scanned successfully'
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Invalid'
            ]);
        }
    }
    
     public function getBranches(){

        $activeBranches = DB::table('sites')
        ->where('site_status', 'active')
        ->select('id', 'name')
        ->get();

        if($activeBranches){
             return response()->json([
                'success' => true,
                'data' => $activeBranches
            ]);
        } else {
            return response()->json([
                'success' => false,
                'data' => [],
            ]);
        }
    }
    
    public function getTodayShifts($id){

    $siteId = $id;
    $startOfToday = Carbon::today();

    $todayShifts = DB::table('job_rosters')
        ->join('guards', 'job_rosters.guard_id', '=', 'guards.id')
        ->where('job_rosters.site_id', $siteId)
        ->where('job_rosters.guard_id', '!=', null)
        ->whereDate('job_rosters.start', $startOfToday) // Start today
        ->select(
            'job_rosters.id',
            'job_rosters.start',
            'job_rosters.end',
            'guards.id as guard_id',
            'guards.first_name',
            'guards.middle_name',
            'guards.last_name',
            DB::raw("CONCAT(guards.first_name, ' ', COALESCE(guards.middle_name, ''), ' ', guards.last_name) as full_name")
        )
        ->get();

         if($todayShifts){
             return response()->json([
                'success' => true,
                'data' => $todayShifts
            ]);
        } else {
            return response()->json([
                'success' => false,
                'data' => [],
            ]);
        }
    }
}
