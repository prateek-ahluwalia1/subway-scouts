<?php

namespace App\Http\Controllers;

use App\Events\MessageSent;
use App\Events\MessageSentAdmin;
use App\Events\MessageSentContractors;
use App\Events\MessageSentCustomers;
use App\Events\MessageSentGuard;
use App\Http\Resources\FetchSitesByStatusResource;
use App\Jobs\manualVisaVerfication;
use App\Models\ChatHistory;
use App\Models\ChatHistoryAdmin;
use App\Models\ChatHistoryContractor;
use App\Models\chatHistoryCustomer;
use App\Models\ChatHistoryGuard;
use App\Models\CrmCustomerFile;
use App\Models\Guard;
use App\Models\GuardDocument;
use App\Models\JobRoster;
use App\Models\Logging;
use App\Models\portal\PortalSettings;
use App\Models\Site;
use App\Models\User;
use App\Models\VisaDetails;
use App\Models\AuditReport;
use Carbon\Carbon;
use GrahamCampbell\ResultType\Success;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Stevebauman\Location\Facades\Location;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Mail;
use Dompdf\Dompdf;
class GeneralController extends Controller
{
    public function getAllUnReadNotification(){
        $unreadNotifications = DB::table('portal_notifications')
        ->join('guards', 'portal_notifications.guard_id', '=', 'guards.id')
        ->where(function ($query) {
        $query->where('type', 'job_signout')
        ->orWhere('type', 'job_confirm')
        ->orWhere('type', 'leave_location')
        ->orWhere('type', 'incident_report')
        ->orWhere('type', 'job_reject')
        ->orWhere('type', 'job_signin')
        ->orWhere('type', 'leave')
        ->orWhere('type', 'green_call')
        ->orWhere('type', 'welfare_call')
        ->orWhere('type', 'sos_call')
        ->orWhere('type', 'job_accept');
        })->where('status', 'unseen')->select('portal_notifications.*', 'guards.profile_image')->latest()->take(10)->get();
        return response()->json(['success' => true, 'data'=>$unreadNotifications]);

    }
    public function getAllUnReadNotificationChat(Request $request){
        $id = $request->id;
        $unreadNotifications = DB::table('portal_notifications')->where('send_by', '!=', $request->send_by)->where('record_id', $request->id)
        // ->join('guards', 'portal_notifications.guard_id', '=', 'guards.id')
        ->where(function ($query) use($id){
        $query->where('type', 'chat');
        })->where('status', 'unseen')->select('portal_notifications.*')->latest()->get();
        return response()->json(['success' => true, 'data'=>$unreadNotifications]);
    }

    public function readSingleNotificationChat(Request $request){
        DB::table('portal_notifications')->where(['id'=> $request->noti_id, 'record_id'=>$request->admin_id, 'type'=>'chat'])->update(['status'=>'seen']);
        return response()->json([
            'success' => true,
            'message' => 'Mark as read'
        ]);
    }
    public function readSingleNotification(Request $request){
        DB::table('portal_notifications')->where('id', $request->noti_id)->update(['status'=>'seen']);
        return response()->json([
            'success' => true,
            'message' => 'Mark as read'
        ]);
    }
    public function readNotificationChat($id)
    {
        DB::table('portal_notifications')->where('record_id', $id)->update(['status' => 'seen', 'api_seen_status'=>'seen']);
        return response()->json(['success' => true]);
    }
    public function readNotification()
    {
        DB::table('portal_notifications')
         ->where(function ($query) {
            $query->where('type', 'job_signout')
            ->orWhere('type', 'job_confirm')
            ->orWhere('type', 'leave_location')
            ->orWhere('type', 'incident_report')
            ->orWhere('type', 'job_signin')
            ->orWhere('type', 'job_accept')
            ->orWhere('type', 'green_call')
            ->orWhere('type', 'sos_call')
            ->orWhere('type', 'welfare_call')
            ->orWhere('type', 'leave');
            })->update(['status' => 'seen']);
        return response()->json(['success' => true]);
    }
    public function getUnseenNotificationChat($id){
        $unseenNotification = DB::table('portal_notifications')->where('record_id', $id)
        ->where(function ($query) use ($id){
        $query->where('type', 'chat');
        })->where('api_seen_status', 'unseen')->first();
        $count = DB::table('portal_notifications')->where('record_id', $id)
        ->where(function ($query) use ($id){
        $query->where('type', 'chat');
        })->where('status', 'unseen')->count();
        if (!empty($unseenNotification)) {
            DB::table('portal_notifications')->where('id', $unseenNotification->id)->update(['api_seen_status' => 'seen']);
        }
        return response()->json(['success' => true, 'unseen' => $unseenNotification, 'count'=>$count]);
    }
    public function getUnseenNotification(){
        $unseenNotification = DB::table('portal_notifications')
        ->where(function ($query) {
        $query->where('type', 'job_signout')
        ->orWhere('type', 'job_confirm')
        ->orWhere('type', 'leave_location')
        ->orWhere('type', 'incident_report')
        ->orWhere('type', 'job_reject')
        ->orWhere('type', 'job_signin')
        ->orWhere('type', 'leave')
        ->orWhere('type', 'green_call')
        ->orWhere('type', 'welfare_call')
        ->orWhere('type', 'sos_call')
        ->orWhere('type', 'job_accept');
        })->where('api_seen_status', 'unseen')->first();
        $count = DB::table('portal_notifications')
        ->where(function ($query) {
        $query->where('type', 'job_signout')
        ->orWhere('type', 'job_confirm')
        ->orWhere('type', 'leave_location')
        ->orWhere('type', 'incident_report')
        ->orWhere('type', 'job_reject')
        ->orWhere('type', 'job_signin')
        ->orWhere('type', 'leave')
        ->orWhere('type', 'green_call')
        ->orWhere('type', 'welfare_call')
        ->orWhere('type', 'sos_call')
        ->orWhere('type', 'job_accept');
        })->where('status', 'unseen')->count();
        if (!empty($unseenNotification)) {
            DB::table('portal_notifications')->where('id', $unseenNotification->id)->update(['api_seen_status' => 'seen']);
        }
        return response()->json(['success' => true, 'unseen' => $unseenNotification, 'count'=>$count]);
    }
    function getLocation(Request $request)  {
        $ip = $request->ip();
        $currentUserInfo = Location::get($ip);
        return $currentUserInfo;
    }
    public function sendMessage(Request $request) {
        $message = new ChatHistory();
        $message->message = $request->message;
        $message->guard_id = $request->staff_id;
        $message->admin_id = $request->admin_id;
        $message->customer_id = $request->customer_id;
        $message->contractor_id = $request->contractor_id;
        $message->saleperson_id = $request->saleperson_id;
        $message->send_by = $request->send_by;
        $message->file = $request->file;
        $message->save();
        if($request->send_by == 'admin' && ($request->staff_id != null || $request->staff_id != '')){
            $guard = Guard::where('id', $request->staff_id)->select('id', 'notification_token')->first();
            if($guard['notification_token']){
                $notificaion['notification_token'] = $guard['notification_token'];
                $notificaion['message'] = $request->message;
                $notificaion['title'] = 'New Message';
                $notificaion['page'] = 'chats';
                $notificaion['send_by'] = User::where('id', $request->admin_id)->select('id', 'name', 'image', 'is_online')->first();
                send_push_notification($notificaion);
            }
        }
        $notification = array(
            'roster' => null,
            'guard_id' => $request->staff_id, 
            'customer_id' => $request->customer_id, 
            'contractor_id' => $request->contractor_id, 
            'saleperson_id' => $request->saleperson_id, 
            'record_id' => $request->admin_id,
            'message' => $request->message,
            'type' => 'chat',
            'send_by' => $request->send_by,
            'send_by_name' => $request->user_name,
            'send_time' => time(),
            'title' => 'Chat message'
        );
        DB::table('portal_notifications')->insert($notification);
        event(new MessageSent($request->staff_id, $request->admin_id, $request->customer_id,$request->contractor_id,$request->saleperson_id, $request->send_by, $request->user_name));
        return response()->json(['success'=>true,'status' => 'Message sent!']);
    }
    public function sendMessageAdmin(Request $request) {
        $message = new ChatHistoryAdmin();
        $message->message = $request->message;
        $message->sender_id = $request->sender_id;
        $message->receiver_id = $request->receiver_id;
        $message->send_by = $request->send_by;
        $message->file = $request->file;
        $message->save();
        $notification = array(
            'admins_id' => $request->sender_id,   //sender_id
            'record_id' => $request->receiver_id, //receiver_id
            'message' => $request->message,
            'type' => 'chat',
            'send_by_name' => $request->send_by,
            'send_by' => $request->sender_id,
            'send_time' => time(),
            'title' => 'Chat message',
            'chat_between' => 'admins',
        );
        DB::table('portal_notifications')->insert($notification);
        event(new MessageSentAdmin($request->sender_id, $request->receiver_id, $request->send_by));
        return response()->json(['success'=>true,'status' => 'Message sent!']);
    }
    public function fetchChatHistoryAdmin(Request $request){
        $senderId = $request->sender_id;
        $receiverId = $request->receiver_id;
        $messages = ChatHistoryAdmin::where(function ($query) use ($senderId, $receiverId) {
            $query->where('sender_id', $senderId)
                  ->where('receiver_id', $receiverId);
        })->orWhere(function ($query) use ($senderId, $receiverId) {
            $query->where('sender_id', $receiverId)
                  ->where('receiver_id', $senderId);
        })->orderBy('created_at', 'asc')->get();
        return response()->json(['success' => true, 'data' => $messages]);
    }
    public function fetchChatHistory(Request $request){
        $query = ChatHistory::query();
        $conditions = [];
        if ($request->has('admin_id')) {
            $conditions[] = "admin_id = {$request->admin_id}";
        }
        if ($request->has('staff_id')) {
            $conditions[] = "guard_id = {$request->staff_id}";
        }
        if ($request->has('customer_id')) {
            $conditions[] = "customer_id = {$request->customer_id}";
        }
        if ($request->has('contractor_id')) {
            $conditions[] = "contractor_id = {$request->contractor_id}";
        }
        if ($request->has('saleperson_id')) {
            $conditions[] = "saleperson_id = {$request->saleperson_id}";
        }

        $whereClause = implode(' AND ', $conditions);
        if (!empty($whereClause)) {
            $query->whereRaw($whereClause);
        }
        $chatHistory = $query->get();
        return response()->json(['success' => true, 'data' => $chatHistory]);
    }
    public function getPreviousChatHistoryAdmin($id){
        $history = ChatHistoryAdmin::orWhere('sender_id', $id)
        ->orWhere('receiver_id', $id)
        ->with(['Sender', 'Receiver'])
        ->groupBy('sender_id', 'receiver_id')
        ->get();
        return response()->json([
            'success' => true,
            'message' => $history
        ]);

    }
    public function getPreviousChatHistory($id){
        $history = ChatHistory::where('guard_id', $id)
        ->orWhere('admin_id', $id)
        ->orWhere('customer_id', $id)
        ->orWhere('contractor_id', $id)
        ->orWhere('saleperson_id', $id)
        ->with(['GuardDetails', 'Contractor', 'Admin', 'SalesPerson', 'Customer'])
        ->groupBy('guard_id', 'admin_id', 'customer_id', 'contractor_id', 'saleperson_id')
        ->get();
        // $history = ChatHistory::where('guard_id', $id)->orWhere('admin_id', $id)->orWhere('customer_id', $id)->orWhere('contractor_id', $id)->orWhere('saleperson_id', $id)->with(['GuardDetails', 'Contractor', 'Admin', 'SalesPerson', 'Customer'])->distinct()->get();
        return response()->json([
            'success' => true,
            'message' => $history
        ]);
    }
    public function uploadImage(Request $request)
    {
       if ($request->folder == '') {
        $request->folder = 'uploads';
       }
       $image = upload_img($request->image, '/'.$request->folder.'/');
             if ($image != '') {
                $url = asset('').$request->folder.'/'. $image;
                if ($request->folder == '') {
                $url = asset('uploads').'/'.$image;
                }
                return response()->json(array('success' => true, 'path' => $image, 'url' => $url));
             } else {
                return response()->json(array('success' => false, 'path' => '', 'url' => ''));
         }
    }

    public function saveThirdPartyApis(Request $request){
        if(isset($request->id)){
            $portalSetting = PortalSettings::find($request->id);
            $portalSetting->click_send_username = $request->click_send_username;
            $portalSetting->click_send_key = $request->click_send_key;
            $portalSetting->air_call_app_id = $request->air_call_app_id;
            $portalSetting->air_call_token = $request->air_call_token;
            $portalSetting->abn = $request->abn;
            $portalSetting->phone = $request->phone;
            $portalSetting->bank_name = $request->bank_name;
            $portalSetting->bsb = $request->bsb;
            $portalSetting->account_number = $request->account_number;
            $portalSetting->biller_code = $request->biller_code;
            $portalSetting->reference_no = $request->reference_no;
            $portalSetting->visa_mail = $request->visa_mail;
            $portalSetting->visa_password = $request->visa_password;
            $portalSetting->update();
            return response()->json([
                'success' => true,
                'message' => 'Setting updated successfully'
            ]);
        }else{
            $portalSetting = new PortalSettings();
            $portalSetting->click_send_username = $request->click_send_username;
            $portalSetting->click_send_key = $request->click_send_key;
            $portalSetting->air_call_app_id = $request->air_call_app_id;
            $portalSetting->air_call_token = $request->air_call_token;
            $portalSetting->abn = $request->abn;
            $portalSetting->phone = $request->phone;
            $portalSetting->bank_name = $request->bank_name;
            $portalSetting->bsb = $request->bsb;
            $portalSetting->account_number = $request->account_number;
            $portalSetting->biller_code = $request->biller_code;
            $portalSetting->reference_no = $request->reference_no;
            $portalSetting->visa_mail = $request->visa_mail;
            $portalSetting->visa_password = $request->visa_password;
            $portalSetting->save();
            return response()->json([
                'success' => true,
                'message' => 'Setting saved successfully'
            ]);
        }

    }

    public function getThirdPartyApis(){
        return response()->json([
            'success' => true,
            'data' => PortalSettings::first()
        ]);
    }

    public function uploadFile(Request $request)
    {
        if ($request->folder == '') {
        $request->folder = 'uploads';
       }
       if ($request->has('upload')) {
      $image = fileUpload($request->upload, '/'.$request->folder.'/');
           
       }else{
      $image = fileUpload($request->file, '/'.$request->folder.'/');
  }
             if ($image != '') {
                $url = asset('').$request->folder.'/'. $image;
                if ($request->folder == '') {
                $url = asset('uploads').'/'.$image;
                }
                return response()->json(array('success' => true, 'path' => $image, 'url' => $url));
             } else {
                return response()->json(array('success' => false, 'path' => '', 'url' => ''));
         }
    }


    function deleteCRMCustomerFile(Request $request) {
        $file = DB::table('crm_customers_files')->where('id', $request->id)->first();
    
        if (!empty($file)) {
            DB::table('crm_customers_files')->where('id', $request->id)->delete();
           
            return response()->json(['success' => true, 'message' => 'File Deleted!']);
        } else {
            return response()->json(['success' => false, 'message' => 'File not found!']);
        }
    }

   public function fileUploadWithSize(Request $request)
    {
        if(isset($request->type) && $request->type == 'mail'){
            $public_path1 = public_path();
            $path = $public_path1.'/'. $request->folder;
            $name = $request->file->move($path, $request->file->getClientOriginalName());
            $fileName = $request->file->getClientOriginalName();
            $size = $name->getSize() / 1024;
            return response()->json(array('success' => true, 'name' => $fileName, 'size' => $size, 'url' => returnImgPath($request->folder,$fileName)));
        }
        $public_path1 = public_path();
        $path = $public_path1.'/'. $request->folder;
        $name = $request->file->move($path, $request->file->getClientOriginalName());
        $fileName = $request->file->getClientOriginalName();
        $size = $name->getSize() / 1024;
        DB::table('crm_customers_files')->Insert([
            'customer_id'=> $request->customer_id,
            'uploaded_by'=> $request->admin_id,
            'size'=> $size,
            'type'=> $request->type,
            'name'=> $fileName,
            'url'=> returnImgPath($request->folder,$fileName),
            'created_at' => usaToAusDateTime(date('Y-m-d H:i:s'))
        ]);
        return response()->json(array('success' => true, 'name' => $fileName, 'size' => $size, ));

    }
    
    public function getCRMFileData($customer_id){
        $data = CrmCustomerFile::where('customer_id', $customer_id)->with('User')->get();
        if(count($data)>0){
            return response()->json([
                'success' => true,
                'data' => $data
            ], 200);
        }else{
            return response()->json([
                'success' => false,
                'message' =>'No data found.'
            ],500);
        }
    }
    
    
    public function fetchSitesByStatus(Request $request)
    {
        $start = Carbon::now()->startOfWeek()->toDateString();
        $end   = Carbon::now()->endOfWeek()->toDateString();
        $query = '';
        if($request->has('customer_id') && !empty($request->customer_id)){
            $query = Site::whereIn('customer_id', $request->customer_id);
        }
        if($request->has('customer_id') && !empty($request->customer_id) && $request->has('site_type')){
            if($request->site_type != 'all') {
                if($request->site_type == 'active'){
                    $query = $query->with(['jobRoster' => function($que) use ($start, $end){
                        $que->whereBetween('start', [$start, $end]);
                    }])->with('jobRoster.jobRosterTask')
                    ->join('job_rosters', 'job_rosters.site_id', '=', 'sites.id')
                    ->select('sites.id', 'sites.site_name', 'sites.site_description')
                    ->groupBy('sites.id')
                    ->groupBy('sites.site_description')
                    ->groupBy('sites.site_name');
                }else{
                    $query = $query->with(['jobRoster' => function($que) use ($start, $end){
                        $que->whereNotBetween('start', [$start, $end]);
                    }])->with('jobRoster.jobRosterTask')
                    ->join('job_rosters', 'job_rosters.site_id', '!=', 'sites.id')
                    ->select('sites.id', 'sites.site_name', 'sites.site_description')
                    ->groupBy('sites.id')
                    ->groupBy('sites.site_description')
                    ->groupBy('sites.site_name');
                }
            } else{
                $query = $query->with(['jobRoster' => function($que) use ($start, $end){
                    $que->whereBetween('start', [$start, $end]);
                }])->with('jobRoster.jobRosterTask');
            }
        }
        $sites = $query->get();
        $sts = FetchSitesByStatusResource::collection($sites);
        return response()->json(['success' => true, 'data' => $sts, 'code' => 200]);
    }
    public function trackUserActivity(Request $request){
        if(!$request->action_by && $request->action_by == null && $request->action_by == ''){
            return response()->json([
                'success' => false,
                'message' => 'ActionByID/UserID not found'
            ], 500);
        }else{
            $actionBy = User::select('id', 'name', 'userType')->find($request->action_by);
        }
        if($request->existed_id && $request->route_leave == null){
            $logging = Logging::find($request->existed_id);
            $logging->action = $logging->action.','.$request->action;            
            if($logging->update()){
                return response()->json([
                    'success' => true,
                    'message' => 'Activity Updated'
                ], 200);
            }else{
                return response()->json([
                    'success' => false,
                    'message' => 'Existed Record not found'
                ], 500);
            }
        }elseif($request->route_leave != null && $request->existed_id == null){
            $logging = Logging::find($request->route_leave);
            if($logging->route_leave == null){
                $logging->route_leave = usaToAusDateTime(date('Y-m-d H:i:s'));
                $logging->update();
                return response()->json([
                    'success' => true,
                    'message' => 'Route leave updated'
                ], 200);
            }else{
                return response()->json([
                    'success' => false,
                    'message' => 'Route already leaved please use different route'
                ], 500);
            }
        }else{
            $logging = new Logging();
            $logging->action_by = $actionBy->id;
            $logging->action_by_name = $actionBy->name;
            $logging->action_by_type = $actionBy->userType;
            $logging->action = $request->action;
            $logging->page = $request->page;
            $logging->route_enter = usaToAusDateTime(date('Y-m-d H:i:s'));
            $logging->save();
            return response()->json([
                'success' => true,
                'id' => $logging->id,
                'message' => 'Route enter submitted'
            ], 200);
        }
    }
    public function getUserActivity(Request $request){
        if($request->user_id && $request->start_date && $request->end_date){
            $startDate = Carbon::parse($request->start_date);
            $endDate = Carbon::parse($request->end_date);
            $dateLists = new Collection();
            while ($startDate->lte($endDate)) {
                $dateLists->push($startDate->toDateString());
                $startDate->addDay();
            }
            foreach($dateLists as $date){
                $activityLogs[$date]['Login'] = Logging::whereDate('created_at', $date)
                ->where(['page'=> 'Login', 'action_by'=> $request->user_id])
                ->orderByDesc('created_at')
                ->first();
                $activityLogs[$date]['Logout'] = Logging::whereDate('created_at', $date)
                ->where(['page'=> 'Logout', 'action_by'=> $request->user_id])
                ->orderByDesc('created_at')
                ->first();
                $activityLogs[$date]['Activity'] = Logging::whereDate('created_at', $date)
                ->where([['page', '!=', 'Login'], ['page', '!=', 'Logout'], ['action_by', $request->user_id]])
                ->orderByDesc('created_at')
                ->get();
            }
            return response()->json([
                'success' => true,
                'data' => $activityLogs,
            ], 200);
        }elseif($request->user_id){
            $activityLogs = Logging::where('action_by', $request->user_id)
            ->orderByDesc('created_at')
            ->get();
            return response()->json([
                'success' => true,
                'data' => $activityLogs,
            ], 200);
        }elseif($request->start_date && $request->end_date){
            $startDate = Carbon::parse($request->start_date);
            $endDate = Carbon::parse($request->end_date);
            $dateLists = new Collection();
            while ($startDate->lte($endDate)) {
                $dateLists->push($startDate->toDateString());
                $startDate->addDay();
            }
            foreach($dateLists as $date){
                $activityLogs[$date]['Login'] = Logging::whereDate('created_at', $date)
                ->where('page', 'Login')
                ->orderByDesc('created_at')
                ->first();
                $activityLogs[$date]['Logout'] = Logging::whereDate('created_at', $date)
                ->where('page', 'Logout')
                ->orderByDesc('created_at')
                ->first();
                $activityLogs[$date]['Activity'] = Logging::whereDate('created_at', $date)
                ->orderByDesc('created_at')
                ->get();
            }
            return response()->json([
                'success' => true,
                'data' => $activityLogs,
            ], 200);
        }else{
            $todayData = Logging::whereDate('created_at', Carbon::today())
            ->orderByDesc('created_at')
            ->get();
            return response()->json([
                'success' => true,
                'data' => $todayData,
            ], 200);
        }
    }
    public function deleteUserActivity(){
        // $config_dbs = DB::connection('mysql2')->table('business_data')->get();
        // foreach($config_dbs as $db)
        // {
            // $connectionConfig['driver'] = 'mysql';
            // $connectionConfig['host'] = env('DB_HOST');
            // $connectionConfig['database'] = $db->database_name;
            // $connectionConfig['username'] = env('DB_USERNAME');
            // $connectionConfig['password'] = env('DB_PASSWORD');
            // // Create a new database connection dynamically
            // $newConnection = 'mysql';
            // config(['database.connections.' . $newConnection => $connectionConfig]);
            // $dynamicDbConnection = DB::connection($newConnection);
            $thresholdDate = Carbon::now()->subDays(15)->toDateString();
            DB::table('logging')->whereDate('created_at', '<', $thresholdDate)->delete();
        // }
    }
    public function deletePortalNotifications(){
        // $config_dbs = DB::connection('mysql2')->table('business_data')->get();
        // foreach($config_dbs as $db)
        // {
            // $connectionConfig['driver'] = 'mysql';
            // $connectionConfig['host'] = env('DB_HOST');
            // $connectionConfig['database'] = $db->database_name;
            // $connectionConfig['username'] = env('DB_USERNAME');
            // $connectionConfig['password'] = env('DB_PASSWORD');
            // // Create a new database connection dynamically
            // $newConnection = 'mysql';
            // config(['database.connections.' . $newConnection => $connectionConfig]);
            // $dynamicDbConnection = DB::connection($newConnection);
            $thresholdDate = Carbon::now()->subDays(30)->toDateString();
            DB::table('portal_notifications')->whereDate('created_at', '<', $thresholdDate)->delete();
        // }
    }
    public function deleteTransientFiles(){
        // $config_dbs = DB::connection('mysql2')->table('business_data')->get();
        // foreach($config_dbs as $db)
        // {
            // $connectionConfig['driver'] = 'mysql';
            // $connectionConfig['host'] = env('DB_HOST');
            // $connectionConfig['database'] = $db->database_name;
            // $connectionConfig['username'] = env('DB_USERNAME');
            // $connectionConfig['password'] = env('DB_PASSWORD');
            // // Create a new database connection dynamically
            // $newConnection = 'mysql';
            // config(['database.connections.' . $newConnection => $connectionConfig]);
            // $dynamicDbConnection = DB::connection($newConnection);
            $thresholdDate = Carbon::now()->subDays(30)->toDateString();
            $getFiles = DB::table('transient_files')->whereDate('created_at', '<', $thresholdDate)->get();
            foreach($getFiles as $file){
                $filePath = public_path($file->folder.'/'.$file->file_name);
                if (File::exists($filePath)) {
            // return  $filePath;
                    File::delete($filePath);
                    DB::table('transient_files')->where('id', $file->id)->delete();
                }
            }
        //     DB::disconnect($newConnection);
        // }
    }


    public function checkGuardDocumentStatus()
    {
        $config_dbs = DB::connection('mysql2')->table('business_data')->get();
        foreach($config_dbs as $db)
        {
            $connectionConfig['driver'] = 'mysql';
            $connectionConfig['host'] = env('DB_HOST');
            $connectionConfig['database'] = $db->database_name;
            $connectionConfig['username'] = env('DB_USERNAME');
            $connectionConfig['password'] = env('DB_PASSWORD');
            $newConnection = 'mysql';
            config(['database.connections.' . $newConnection => $connectionConfig]);
            $dynamicDbConnection = DB::connection($newConnection);
            $today = date("Y/m/d");
            $date =  dbFormate($today); 
            $guard_documents = $dynamicDbConnection->table('guards_documents')->where('document_expire', '<',  $date)
            ->join('guards' , 'guards.id', '=', 'guards_documents.guard_id')->select('guards.id', 'document_category', 'document_type')->where('guards.is_email_approved', 'yes')->get();
            foreach ($guard_documents as $guard) {
                if($guard->document_category == 'citizen'){
                    if($guard->document_type == 'security_license'){
                        $dynamicDbConnection->table('guards')->where('id', $guard->id)->update(['guard_status' => 'document_exp', 'admin_approval_status'=>'inactive']);
                    }
                }else{
                    if($guard->document_type == 'passport'){
                        $dynamicDbConnection->table('guards')->where('id', $guard->id)->update(['guard_status' => 'document_exp', 'admin_approval_status'=>'inactive']);
                    }
                    if($guard->document_type == 'visa'){
                        $dynamicDbConnection->table('guards')->where('id', $guard->id)->update(['guard_status' => 'document_exp', 'admin_approval_status'=>'inactive']);
                    }
                    if($guard->document_type == 'security_license'){
                        $dynamicDbConnection->table('guards')->where('id', $guard->id)->update(['guard_status' => 'document_exp', 'admin_approval_status'=>'inactive']);
                    }
                }
                $dynamicDbConnection->table('guards')->where('is_email_approved', 'no')->update(['admin_approval_status'=>'inactive','guard_status'=>'new']);
                // $getGuard = Guard::with('empDetails')->find($guard);
                // if($getGuard->empDetails->guard_document_type == 'citizen'){
                //     $dynamicDbConnection->table('guards')->where('id', $guard->id)->update(['guard_status' => 'document_exp']);
                // }
                // \Log::info("Cron is working fine!");
            }
            DB::disconnect($newConnection);
        }
            return 'Guards status has been changed';
    }
    public function testOneSignal(){

        $data = [
            'message' => 'Test One Signal',
            'title' => 'Title One Signal',
            'notification_token' => '1288f6bd-097f-4475-9a7f-de30a090d103',
            'page' => 'homepage',
        ];
        test_send_push_notification($data);
    }
    public function appStatus(){
        $data = JobRoster::with(['guardz', 'site', 'activity'])
        ->where('signin_status', 1)
        ->orderBy('start', 'DESC')
        ->get();
        return response()->json([
            'success' => true,
            'data' => $data
        ]);
    }
    public function autoVisaVarification(){
        $config_dbs = DB::connection('mysql2')->table('business_data')->get();
        foreach($config_dbs as $db)
        {
            $connectionConfig['driver'] = 'mysql';
            $connectionConfig['host'] = env('DB_HOST');
            $connectionConfig['database'] = $db->database_name;
            $connectionConfig['username'] = env('DB_USERNAME');
            $connectionConfig['password'] = env('DB_PASSWORD');
            $newConnection = 'mysql';
            config(['database.connections.' . $newConnection => $connectionConfig]);
            $dynamicDbConnection = DB::connection($newConnection);
            $guards = Guard::where('guard_status', 'active')->whereNotNull(['dob', 'country'])
            ->with(['guardDocuments' => function ($query) {
                $query->whereIn('document_type', ['passport', 'visa'])
                  ->where('document_no', '!=', 'null');
            }])->get();
            foreach($guards as $guard){
                if($guard->guardDocuments){
                    foreach($guard->guardDocuments as $doc){
                        if($doc->document_type == 'visa'){
                            $visaNum = $doc->document_no; 
                        }
                        if($doc->document_type == 'passport'){
                            $ppNum = $doc->document_no; 
                        }
                    }
                    if($visaNum && $ppNum){
                        $carbonDate = Carbon::parse($guard['dob']);
                        $formattedDOB = $carbonDate->format('d M Y');
                        $response = Http::post('http://62.72.13.17/search', ["visa_grant_number"=> $visaNum,"date_of_birth"=> $formattedDOB,"document_number"=> $ppNum,"select_country"=> $guard->country]);
                        $jsonResponse = json_decode($response, true);
                        if($jsonResponse['status'] == 'success'){
                            VisaDetails::where('id', $guard['id'])->delete();
                            $visaDetail = new VisaDetails();
                            $visaDetail->guard_id = $guard['id'];
                            $visaDetail->guard_name = $guard['first_name'].' '.$guard['last_name'];
                            $visaDetail->country = $guard['country'];
                            $visaDetail->details = $response;
                            $visaDetail->save();          
                        }; 
                    }
                }
            }
            DB::disconnect($newConnection);
            
        }
    }
    public function manualVisaVarification(Request $request){
        if($request->requestType == 'new'){
            $guards = Guard::where('guard_status', 'active')->where('is_available', 'yes')
            ->whereNotNull(['dob', 'country'])
            ->whereHas('guardDocuments', function ($query) {
                $query->whereIn('document_type', ['passport'])
                    ->whereNotNull('document_no');
            })
            ->whereDoesntHave('guardDocuments', function ($query) {
                $query->where('document_category', 'citizen');
            })
            ->with(['guardDocuments' => function ($query) {
                $query->whereIn('document_type', ['passport'])
                    ->whereNotNull('document_no');
            }])
            ->get();
            foreach($guards as $guard){
                if($guard->guardDocuments){
                    foreach($guard->guardDocuments as $doc){
                        if($doc->document_type == 'passport'){
                            $ppNum = $doc->document_no; 
                        }
                    }
                    if($ppNum){
                        DB::table('guard_visa_temp')->insert([
                            'guard_id' => $guard['id'],
                            'visa_number' => '123',
                            'dob' => $guard['dob'],
                            'name' => $guard['first_name'].' '.$guard['last_name'],
                            'passport_number' => $ppNum,
                            'country' => $guard->country,
                        ]);
                    }
                }
            }
            $guards = DB::table('guard_visa_temp')->take(10)->get();
        }else{
            $guards = DB::table('guard_visa_temp')->take(10)->get();
        }
        foreach($guards as $guard){
            $response = Http::timeout(60)->post('http://62.72.13.17/search', ["family_name"=>$guard->name,"date_of_birth"=> $guard->dob,"document_number"=> $guard->passport_number,"select_country"=> $guard->country, "type"=>'new', 'email'=>$request->email, 'password'=>$request->password]);
            $jsonResponse = json_decode($response, true);
            DB::table('guard_visa_temp')->where('guard_id', $guard->guard_id)->delete();
            if(isset($jsonResponse) && $jsonResponse['status'] == 'success'){
                VisaDetails::where('guard_id', $guard->guard_id)->delete();
                $visaDetail = new VisaDetails();
                $visaDetail->guard_id = $guard->guard_id;
                $visaDetail->guard_name = $guard->name;
                $visaDetail->country = $guard->country;
                $visaDetail->dob = $guard->dob;
                $visaDetail->passport_no = $guard->passport_number;
                $visaDetail->details = $response;
                $visaDetail->is_correct = 1;
                $visaDetail->save();          
            }else{
                VisaDetails::where('guard_id', $guard->guard_id)->delete();
                $visaDetail = new VisaDetails();
                $visaDetail->guard_id = $guard->guard_id;
                $visaDetail->guard_name = $guard->name;
                $visaDetail->country = $guard->country;
                $visaDetail->dob = $guard->dob;
                $visaDetail->passport_no = $guard->passport_number;
                $visaDetail->details = $response;
                $visaDetail->is_correct = 0;
                $visaDetail->save();          
                // DB::table('guard_visa_temp')->where('guard_id', $guard->id)->delete();
                continue;
            }
        }
        return response()->json([
            'success' => true,
            'count' => DB::table('guard_visa_temp')->count()
        ]);
    }
    // public function manualVisaVarification(){
    //     $guards = Guard::where('guard_status', 'active')->whereNotNull(['dob', 'country'])
    //     ->with(['guardDocuments' => function ($query) {
    //         $query->whereIn('document_type', ['passport', 'visa'])
    //             ->where('document_no', '!=', 'null');
    //     }])->get();
    //     foreach($guards as $guard){
    //         if($guard->guardDocuments){
    //             foreach($guard->guardDocuments as $doc){
    //                 if($doc->document_type == 'visa'){
    //                     $visaNum = $doc->dorcument_no; 
    //                 }
    //                 if($doc->document_type == 'passport'){
    //                     $ppNum = $doc->document_no; 
    //                 }
    //             }
    //             if($visaNum && $ppNum){
    //                 $carbonDate = Carbon::createFromFormat('m-d-Y', $guard['dob']);
    //                 $formattedDOB = $carbonDate->format('d M Y');
    //                 $response = Http::post('http://62.72.13.17/search', ["visa_grant_number"=> $visaNum,"date_of_birth"=> $formattedDOB,"document_number"=> $ppNum,"select_country"=> $guard->country]);
    //                 $jsonResponse = json_decode($response, true);
    //                 if($jsonResponse['status'] == 'success'){
    //                     VisaDetails::where('guard_id', $guard['id'])->first();
    //                     DB::table('visa_details')->insert([
    //                         'guard_id' => $guard['id'],
    //                         'details' => $response,
    //                         'country' => $guard['country'],
    //                         'guard_name' => $guard['first_name'].' '.$guard['last_name'],
    //                         'is_correct' => 1
    //                     ]);    
    //                 }; 
    //             }else{
    //                 VisaDetails::where('guard_id', $guard['id'])->delete();
    //                 DB::table('visa_details')->insert([
    //                     'guard_id' => $guard['id'],
    //                     'details' => 'missing/unavailable',
    //                     'country' => $guard['country'],
    //                     'guard_name' => $guard['first_name'].' '.$guard['last_name'],
    //                     'is_correct' => 0
    //                 ]);
    //             }
    //         }
    //     }
    //     // manualVisaVerfication::dispatch();
    //     // $guards = Guard::where('guard_status', 'active')->whereNotNull(['dob', 'country'])
    //     // ->with(['guardDocuments' => function ($query) {
    //     //     $query->whereIn('document_type', ['passport', 'visa'])
    //     //         ->where('document_no', '!=', 'null');
    //     // }])->get();
    //     // foreach($guards as $guard){
    //     //     if($guard->guardDocuments){
    //     //         foreach($guard->guardDocuments as $doc){
    //     //             if($doc->document_type == 'visa'){
    //     //                 $visaNum = $doc->document_no; 
    //     //             }
    //     //             if($doc->document_type == 'passport'){
    //     //                 $ppNum = $doc->document_no; 
    //     //             }
    //     //         }
    //     //         if(isset($visaNum) && isset($ppNum) && $visaNum && $ppNum){
    //     //             $carbonDate = Carbon::parse($guard['dob']);
    //     //             $formattedDOB = $carbonDate->format('d M Y');
    //     //             $response = Http::post('http://62.72.13.17/search', ["visa_grant_number"=> $visaNum,"date_of_birth"=> $formattedDOB,"document_number"=> $ppNum,"select_country"=> $guard->country]);
    //     //             $jsonResponse = json_decode($response, true);
    //     //             if($jsonResponse['status'] == 'success'){
    //     //                 VisaDetails::where('id', $guard['id'])->delete();
    //     //                 $visaDetail = new VisaDetails();
    //     //                 $visaDetail->guard_id = $guard['id'];
    //     //                 $visaDetail->guard_name = $guard['first_name'].' '.$guard['last_name'];
    //     //                 $visaDetail->country = $guard['country'];
    //     //                 $visaDetail->details = $response;
    //     //                 $visaDetail->save();                 
    //     //             }; 
    //     //         }
    //     //     }
    //     // }
    //     return response()->json([
    //         'success' => true,
    //         'message' => 'We are updating in backgroud it will update after some time please wait Thanks.'
    //     ]);
    // }
    public function closeAppNotification(Request $request)  {
        $msg = '';
        if($request->has('type') && !empty($request->type) && $request->type == 'Left Location'){
            $msg = "You're outside the location radius. Please return inside the radius."; 
            $title = "Left Location"; 
        }else{
            $msg = "We are unable to get your location. Please restart your app"; 
            $title = "App Closed"; 
        }
        $guard = Guard::where('id', $request->id)->first();
        if($guard){
            $prams['message'] = $msg;
            $prams['title'] = $title;
            $prams['page'] = 'home';
            $prams['notification_token'] = $guard->notification_token;
            send_push_notification($prams);
            $guard->phone = str_replace('(', '', $guard->phone);
            $guard->phone = str_replace(')', '', $guard->phone);
            $guard->phone = str_replace('-', '', $guard->phone);
            $guard->phone = str_replace(' ', '', $guard->phone);
            $phone_ = str_split($guard->phone);
            if (sizeof($phone_) == 10) {
                $guard->phone = '+61'.$guard->phone;
            }
            // sendSmsToGuard($guard->phone, 'Hi '.$guard->first_name.' '.$guard->last_name.' '. $msg);
            return response()->json([
                'success' => true,
            ]); 
        }else{
            return response()->json([
                'success' => false,
                'error' => 'Staff not Found!'
            ]); 
        }
    }
    public function incomingEmails(Request $request){
        try {
            $emailData = json_decode($request->getContent());
            Log::info('Received email:', ['email_data' => $emailData]);
            return response()->json(['message' => 'Email received and processed.'], 200);
        } catch (\Exception $e) {
            // Handle and log the error.
            Log::error('Error processing email: ' . $e->getMessage());
    
            // Send an error response.
            return response()->json(['message' => 'Error processing email.'], 500);
        }
    }
    public function authenticationQr(Request $request)
    {
        $qr = DB::table('google2fa_secrets')->first();
        if(empty($qr)){
            $google2fa = app('pragmarx.google2fa');
            $registration_data = $request->all();
            $registration_data["google2fa_secret"] = $google2fa->generateSecretKey();
            $email = 'moizalig16@gmail.com';
            $QR_Image = $google2fa->getQRCodeInline(
                config('app.name'),
                $email,
                $registration_data['google2fa_secret']
            );
            DB::table('google2fa_secrets')->insert([
                'google2fa_secret' => $registration_data['google2fa_secret'],
                'qr' => $QR_Image,
                'created_by' => 1
            ]);
        }else{
            $QR_Image = $qr->qr;
        }
        return response()->json([
            'QR_Image' => $QR_Image
        ]);
    }



    function createBusinessFolder(Request $request) {
        
        $data = [
            'folder' => $request->folder,
            'parent_id' => !empty($request->parent_id) ? $request->parent_id : null,
            'created_by' => $request->created_by,
        ];
        // Insert the new record into the database
        DB::table('business_folder')->insert($data);
        //$id = DB::getPdo()->lastInsertId();
        return response()->json([
            'success' => true,
            'message' => 'Folder created!',
        ]);
    }

    function getBusinessFolder(Request $request) {   
     $records = DB::table('business_folder')->whereNull('parent_id')->select('id','folder')->get();
        return response()->json([
            'success' => true,
            'data' => $records,
        ]);
    }

    function createBusinessFile(Request $request) {
        $data = [
            'folder_id' => $request->folder_id,
            'file_link' => $request->file_link,
            'file_size' => $request->file_size,
            'expiry_date' => $request->expiry_date,
            'created_by' => $request->created_by,
            'name' => $request->name,
        ];
        if(isset($request->id) &&  $request->id != ''){
            DB::table('business_files')->where('id', $request->id)->update($data);
        }else{
            DB::table('business_files')->insert($data);
        }
        //$id = DB::getPdo()->lastInsertId();
        return response()->json([
            'success' => true,
            'message' => 'File saved!',
        ]);
    }


    function getAllBusinessfile(Request $request) {
        $files = DB::table('business_files')->where('folder_id', $request->folder_id)
            ->join('users', 'users.id', '=', 'business_files.created_by')
            ->select('business_files.*', 'users.name as created_by')
            ->get();
        $folders = DB::table('business_folder')->where('parent_id', $request->folder_id)
            ->join('users', 'users.id', '=', 'business_folder.created_by')
            ->select('business_folder.*', 'users.name as created_by')
            ->get();

        return response()->json([
            'success' => true,
            'files' => $files,
            'folders' => $folders,
        ]);
    }

    function getSpecificBusinessfile(Request $request) {
        $record = DB::table('business_files')
            ->join('users', 'users.id', '=', 'business_files.created_by')
            ->select('business_files.*', 'users.name as created_by')
            ->where('business_files.id', $request->id)
            ->first();
    
        return response()->json([
            'success' => true,
            'data' => $record,
        ]);
    }

    public function deleteBusinessFile(Request $request)
    {
        if (!empty($request->id)) {
            DB::table('business_files')->where('id', $request->id)->delete();
    
            return response()->json([
                'success' => true,
                'msg' => 'File deleted',
            ]);
        } else {
            return response()->json([
                'success' => false, // Change this to false
                'msg' => 'Record not found',
            ]);
        }
    }


    function renameFolderName(Request $request) {
        $folder = DB::table('business_folder')->where('id', $request->id)->first();
    
        if (!empty($folder)) {
            DB::table('business_folder')
                ->where('id', $request->id)
                ->update(['folder' => $request->folder]);
    
            return response()->json(['success' => true, 'message' => 'Folder name updated!']);
        } else {
            return response()->json(['success' => false, 'message' => 'Folder not found!']);
        }
    }
    

    function deleteFolder(Request $request) {
        $folder = DB::table('business_folder')->where('id', $request->id)->first();
    
        if (!empty($folder)) {
            DB::table('business_folder')->where('id', $request->id)->delete();
            DB::table('business_folder')->where(['parent_id' => $request->id])->delete();
            DB::table('business_files')->where(['folder_id' => $request->id])->delete();
    
            return response()->json(['success' => true, 'message' => 'Folder Deleted!']);
        } else {
            return response()->json(['success' => false, 'message' => 'Folder not found!']);
        }
    }



    public function sendMessageGuard(Request $request) {
        $message = new ChatHistoryGuard();
        $message->message = $request->message;
        $message->user_id = $request->user_id;
        $message->staff_id = $request->staff_id;
        $message->send_by = $request->send_by;
        $message->file = $request->file;
        $message->save();
        $notification = array(
            'record_id' => $request->user_id,   //sender_id
            'guard_id' => $request->staff_id, //receiver_id
            'message' => $request->message,
            'type' => 'chat',
            'send_by_name' => $request->send_by,
            'send_by' => $request->user_id,
            'send_time' => time(),
            'title' => 'Chat message',
            'chat_between' => 'staff',
        );
        DB::table('portal_notifications')->insert($notification);
        event(new MessageSentGuard($request->user_id, $request->staff_id, $request->send_by));
        return response()->json(['success'=>true,'status' => 'Message sent!']);
    }
    public function sendMessageCustomer(Request $request) {
        $message = new chatHistoryCustomer();
        $message->message = $request->message;
        $message->user_id = $request->user_id;
        $message->customer_id = $request->customer_id;
        $message->send_by = $request->send_by;
        $message->file = $request->file;
        $message->save();
        $notification = array(
            'record_id' => $request->user_id,   //sender_id
            'customer_id' => $request->customer_id, //receiver_id
            'message' => $request->message,
            'type' => 'chat',
            'send_by_name' => $request->send_by,
            'send_by' => $request->user_id,
            'send_time' => time(),
            'title' => 'Chat message',
            'chat_between' => 'customer',
        );
        DB::table('portal_notifications')->insert($notification);
        event(new MessageSentCustomers($request->user_id, $request->customer_id, $request->send_by));
        return response()->json(['success'=>true,'status' => 'Message sent!']);
    }

    public function sendMessageContractor(Request $request) {
        $message = new ChatHistoryContractor();
        $message->message = $request->message;
        $message->user_id = $request->user_id;
        $message->contractor_id = $request->contractor_id;
        $message->send_by = $request->send_by;
        $message->file = $request->file;
        $message->save();
        $notification = array(
            'record_id' => $request->user_id,   //sender_id
            'contractor_id' => $request->contractor_id, //receiver_id
            'message' => $request->message,
            'type' => 'chat',
            'send_by_name' => $request->send_by,
            'send_by' => $request->user_id,
            'send_time' => time(),
            'title' => 'Chat message',
            'chat_between' => 'contractor',
        );
        DB::table('portal_notifications')->insert($notification);
        event(new MessageSentContractors($request->user_id, $request->contractor_id, $request->send_by));
        return response()->json(['success'=>true,'status' => 'Message sent!']);
    }

    public function fetchChatHistoryContractor(Request $request){
        $userId = $request->user_id;
        $contractorId = $request->contractor_id;
        $messages = ChatHistoryContractor::where(function ($query) use ($userId, $contractorId) {
            $query->where('user_id', $userId)
                  ->where('contractor_id', $contractorId);
        })->orWhere(function ($query) use ($userId, $contractorId) {
            $query->where('user_id', $userId)
                  ->where('contractor_id', $contractorId);
        })->orderBy('created_at', 'asc')->get();
        return response()->json(['success' => true, 'data' => $messages]);
    }


    public function fetchChatHistoryGuard(Request $request){
        $userId = $request->user_id;
        $guardId = $request->staff_id;
        $messages = ChatHistoryGuard::where(function ($query) use ($userId, $guardId) {
            $query->where('user_id', $userId)
                  ->where('staff_id', $guardId);
        })->orWhere(function ($query) use ($userId, $guardId) {
            $query->where('user_id', $userId)
                  ->where('staff_id', $guardId);
        })->orderBy('created_at', 'asc')->get();
        return response()->json(['success' => true, 'data' => $messages]);
    }
    public function fetchChatHistoryCustomer(Request $request){
        $userId = $request->user_id;
        $customerId = $request->customer_id;
        $messages = chatHistoryCustomer::where(function ($query) use ($userId, $customerId) {
            $query->where('user_id', $userId)
                  ->where('customer_id', $customerId);
        })->orWhere(function ($query) use ($userId, $customerId) {
            $query->where('user_id', $userId)
                  ->where('customer_id', $customerId);
        })->orderBy('created_at', 'asc')->get();
        return response()->json(['success' => true, 'data' => $messages]);
    }

    public function getPreviousChatHistoryGuard($id){
        $history = ChatHistoryGuard::orWhere('user_id', $id)
        ->orWhere('staff_id', $id)
        ->with(['user', 'guardz'])
        ->groupBy('user_id', 'staff_id')
        ->get();
        return response()->json([
            'success' => true,
            'message' => $history
        ]);

    }

    public function getPreviousChatHistoryContractor($id){
        $history = ChatHistoryContractor::orWhere('user_id', $id)
        ->orWhere('contractor_id', $id)
        ->with(['user', 'contractor'])
        ->groupBy('user_id', 'contractor_id')
        ->get();
        return response()->json([
            'success' => true,
            'message' => $history
        ]);

    }
    public function getPreviousChatHistoryCustomer($id){
        $history = chatHistoryCustomer::orWhere('user_id', $id)
        ->orWhere('customer_id', $id)
        ->with(['user', 'customer'])
        ->groupBy('user_id', 'customer_id')
        ->get();
        return response()->json([
            'success' => true,
            'message' => $history
        ]);

    }
    public function testMail(){
        // $to = 'abdulsamad.idenbrid@gmail.com';
        // $subject = 'Test Email';
        // $message = 'This is a test email.';
        // $headers = 'From: your_email@example.com' . "\r\n" .
        //     'Reply-To: your_email@example.com' . "\r\n" .
        //     'X-Mailer: PHP/' . phpversion();

        // if (mail($to, $subject, $message, $headers)) {
        //     echo 'Email sent successfully';
        // } else {
        //     echo 'Error sending email';
        // }
         $data = [
            'message' => 'message',
            'token' => 'token',
            'email' => 'abdulsamad.idenbrid@gmail.com',
            'subject' => 'Subect'
        ];
        $mails = ['abdulsamad.idenbrid@gmail.com'];
        foreach($mails as $mail){
            Mail::send('mail.general', $data, function($mail) use ($data){
                $mail->from('no-reply@thescouts.com.au', 'AMG Security');
                $mail->to($data['email'])->subject($data['subject']);
            });
        }
        return 'done';
    }
    function submitAuditReport(Request $request)
    {
        $auditForm = new AuditReport();
        $auditForm->customer_id = $request->customer_id;
        $auditForm->site_id = $request->site_id;
        $auditForm->guard_id = ($request->has('guard_id') && $request->guard_id != '') ? $request->guard_id : '';
        $auditForm->guard_name = $request->guard_name;
        $auditForm->guard_email = $request->guard_email;
        $auditForm->guard_phone = $request->guard_phone;
        $auditForm->guard_security_license = $request->guard_security_license;
        $auditForm->guard_license_expiry = $request->guard_license_expiry;
        $auditForm->admin_id = $request->admin_id;
        $auditForm->have_uniform = $request->have_uniform;
        $auditForm->uniform_text = $request->uniform_text;
        if ($request->has('uniform_image') && count($request->uniform_image)>0) {
            $auditForm->uniform_image = $request->uniform_image[0];
        }
        $auditForm->have_shoes = $request->have_shoes;
        $auditForm->shoes_text = $request->shoes_text;
        if ($request->has('shoes_image') && count($request->shoes_image)>0) {
            $auditForm->shoes_image = $request->shoes_image[0];
        }
        $auditForm->have_license = $request->have_license;
        $auditForm->license_text = $request->license_text;
        if ($request->has('license_image') && count($request->license_image)>0) {
            $auditForm->license_image = $request->license_image[0];
        }
        $auditForm->have_induction_card = $request->have_induction_card;
        $auditForm->induction_card_text = $request->induction_card_text;
        if ($request->has('induction_card_image') && count($request->induction_card_image)>0) {
            $auditForm->induction_card_image = $request->induction_card_image[0];
        }
        $auditForm->have_notebook_pen = $request->have_notebook_pen;
        $auditForm->notebook_pen_text = $request->notebook_pen_text;
        if ($request->has('notebook_pen_image') && count($request->notebook_pen_image)>0) {
            $auditForm->notebook_pen_image = $request->notebook_pen_image[0];
        }
        $auditForm->log_book = $request->log_book;
        $auditForm->book_text = $request->book_text;
        if ($request->has('log_book_image') && count($request->log_book_image)>0) {
            $auditForm->log_book_image = $request->log_book_image[0];
        }
        $auditForm->on_time = $request->on_time;
        $auditForm->on_time_text = $request->on_time_text;
        if ($request->has('on_time_image') && count($request->on_time_image)>0) {
            $auditForm->on_time_image = $request->on_time_image[0];
        }
        $auditForm->job_understanding = $request->job_understanding;
        $auditForm->job_understanding_text = $request->job_understanding_text;
        if ($request->has('job_understanding_image') && count($request->job_understanding_image)>0) {
            $auditForm->job_understanding_image = $request->job_understanding_image[0];
        }
        $auditForm->have_firstaid = $request->have_firstaid;
        $auditForm->firstaid_text = $request->firstaid_text;
        if ($request->has('firstaid_image') && count($request->firstaid_image)>0) {
            $auditForm->firstaid_image = $request->firstaid_image[0];
        }
        $auditForm->have_site_knowledge = $request->have_site_knowledge;
        $auditForm->site_knowledge_text = $request->site_knowledge_text;
        if ($request->has('site_knowledge_image') && count($request->site_knowledge_image)>0) {
            $auditForm->site_knowledge_image = $request->site_knowledge_image[0];
        }
        $auditForm->have_assigned_petrol = $request->have_assigned_petrol;
        $auditForm->assigned_petrol_text = $request->assigned_petrol_text;
        if ($request->has('assigned_petrol_image') && count($request->assigned_petrol_image)>0) {
            $auditForm->assigned_petrol_image = $request->assigned_petrol_image[0];
        }
        $auditForm->have_rsa_certificate = $request->have_rsa_certificate;
        $auditForm->rsa_certificate_text = $request->rsa_certificate_text;
        if ($request->has('rsa_certificate_image') && count($request->rsa_certificate_image)>0) {
            $auditForm->rsa_certificate_image = $request->rsa_certificate_image[0];
        }
        $auditForm->have_white_card = $request->have_white_card;
        $auditForm->white_card_text = $request->white_card_text;
        if ($request->has('white_card_image') && count($request->white_card_image)>0) {
            $auditForm->white_card_image = $request->white_card_image[0];
        }
        $auditForm->have_children_check = $request->have_children_check;
        $auditForm->children_check_text = $request->children_check_text;
        if ($request->has('children_check_image') && count($request->children_check_image)>0) {
            $auditForm->children_check_image = $request->children_check_image[0];
        }
        $auditForm->have_site_eqipment = $request->have_site_eqipment;
        $auditForm->site_eqipment_text = $request->site_eqipment_text;
        if ($request->has('site_eqipment_image') && count($request->site_eqipment_image)>0) {
            $auditForm->site_eqipment_image = $request->site_eqipment_image[0];
        }
        $auditForm->have_well_groomed = $request->have_well_groomed;
        $auditForm->well_groomed_text = $request->well_groomed_text;
        if ($request->has('well_groomed_image') && count($request->well_groomed_image)>0) {
            $auditForm->well_groomed_image = $request->well_groomed_image[0];
        }
        $auditForm->have_emergency_protocol = $request->have_emergency_protocol;
        $auditForm->emergency_protocol_text = $request->emergency_protocol_text;
        if ($request->has('emergency_protocol_image') && count($request->emergency_protocol_image)>0) {
            $auditForm->emergency_protocol_image = $request->emergency_protocol_image[0];
        }
        $auditForm->have_on_site = $request->have_on_site;
        $auditForm->on_site_text = $request->on_site_text;
        if ($request->has('on_site_image') && count($request->on_site_image)>0) {
            $auditForm->on_site_image = $request->on_site_image[0];
        }

        if ($request->has('signature') && $request->signature != '') {
            $auditForm->signature = $request->signature;
        }

        $auditForm->notes = $request->notes;
        $inserted = $auditForm->save();
        if($inserted){
            return response()->json(['status' => true, 'message' => 'Audit form submitted successfully.']);
        }else{
            return response()->json(['status' => false, 'error' => 'Fail to submit audit form!']);
        }
    }
    public function getAllAudits($id){
        if($id == null || $id == 0){
            $getAudits = AuditReport::with(['guardDetails', 'site', 'customer'])->latest()->get(); 
            
        }else{
            $getAudits = AuditReport::with(['guardDetails', 'site', 'customer'])->latest()->find($id); 
        }
        return response()->json([
            'success' => true,
            'data' => $getAudits
        ]);
    }
    public function generateAuditReport($id){
        $data = DB::table('audit_reports')
        ->join('sites', 'sites.id','=','audit_reports.site_id')
        ->join('users', 'users.id','=','audit_reports.admin_id')
        ->where('audit_reports.id', $id)
        ->select('audit_reports.*', 'sites.address', 'sites.site_name', 'sites.site_description', 'users.name as audit_by')->first();
        // $pdf_data = PDF::loadView('exports/auditReport', ['data' => $data]);
        // return $pdf_data->stream($data->guard_name.'audit_report.pdf');
        if($data){
            $name = '';
            $html = view('exports/auditReport', ['data' => $data]);
            // echo $html;
            // exit;
            $dompdf = new Dompdf();
            $dompdf->loadHtml($html);
            $dompdf->setPaper('A4', 'portrait');
            $dompdf->render();
            $output = $dompdf->output();
            $public_path = public_path();
            $public_path = str_replace('247StaffingSolution/public/', '', $public_path);
            $folder ='/auditReport';
            $path = $public_path.$folder;
            $file_name = time() . '_auditReport_report.pdf';
            $result = file_put_contents($path.'/'.$file_name, $output);
            $name = $file_name;
            # ADD TO HISTORY TABLE TO DELETE AFTER 1 MONTH
            $transient_file = DB::table('transient_files')->insert([
                'folder' => 'auditReport',
                'file_name' => $name,  
            ]);
            return response()->json(['success' =>  true, 'message' => 'Audit Report generate successfully.','path' => 'https://'.request()->getHttpHost().'/auditReport/'.$name]);
        }
    }
    public function sendBirthdayMail(){
        // $config_dbs = \DB::connection('mysql2')->table('business_data')->where('hide', 1)->get();
        // foreach($config_dbs as $db)
        // {
            // $connectionConfig['driver'] = 'mysql';
            // $connectionConfig['host'] = env('DB_HOST');
            // $connectionConfig['database'] = $db->database_name;
            // $connectionConfig['username'] = env('DB_USERNAME');
            // $connectionConfig['password'] = env('DB_PASSWORD');
            // $newConnection = 'mysql';
            // config(['database.connections.' . $newConnection => $connectionConfig]);
            // $dynamicDbConnection = \DB::connection($newConnection);
            $today = Carbon::now();
            $activeGuards = DB::table('guards')->where('guard_status', 'active')->where('dob', '!=', null)
                ->select('name', 'email', 'dob')
                ->get();
            $mainArr = [];
            foreach ($activeGuards as $guard) {
                $dobDmy = Carbon::createFromFormat('m-d-Y', $guard->dob)->format('d-m-Y');
                $guardDate = Carbon::createFromFormat('d-m-Y', $dobDmy);
                if ($today->isBirthday($guardDate)) {
                    $mainArr[] = $guard; 
                }
            }
            $from = 'no-reply@thescouts.com.au';
            $headers  = 'MIME-Version: 1.0' . "\r\n";
            $subject = 'Happy Birthday From TheScouts';
            $headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
            $headers .= 'From: '.$from."\r\n".
            'Reply-To: '.$from."\r\n" .
            'X-Mailer: PHP/' . phpversion();
            $image1 = 'https://apis.thescouts.com.au/public/mail/1653986844783-bg.png';
            $image2 = 'https://apis.thescouts.com.au/public/mail/1653987052761-cake.png';
            $image3 = 'https://apis.thescouts.com.au/public/logo.png';
            
            if(count($mainArr) > 0){

            foreach ($mainArr as $guard) {
                $to = $guard->email;
                $message = '<body class="clean-body u_body"
                style="margin: 0;padding: 0;-webkit-text-size-adjust: 100%;background-color: #e7e7e7;color: #000000">
                <table id="u_body"
                    style="border-collapse: collapse;table-layout: fixed;border-spacing: 0;mso-table-lspace: 0pt;mso-table-rspace: 0pt;vertical-align: top;min-width: 320px;Margin: 0 auto;background-color: #e7e7e7;width:100%"
                    cellpadding="0" cellspacing="0">
                    <tbody>
                        <tr style="vertical-align: top">
                            <td style="word-break: break-word;border-collapse: collapse !important;vertical-align: top">
                                <div id="u_row_1" class="u-row-container v-row-padding--vertical v-row-background-image--outer"
                                    style="padding: 0px;background-image: url({{'.$image1.'}});background-repeat: no-repeat;background-position: center top;background-color: transparent">
                                    <div class="v-container-padding-padding"
                                        style="overflow-wrap:break-word;word-break:break-word;padding:10px;font-family:arial,helvetica,sans-serif;text-align: center;">
                                        <img src="'.$image3.'" alt="Logo" title="Logo" width="150"
                                            style="outline: none;text-decoration: none;-ms-interpolation-mode: bicubic;clear: both;display: inline-block !important;border: none;height: auto;float: none;margin-top: 30px;">
                                    </div>
                                    <div class="u-row"
                                        style="Margin: 0 auto;min-width: 320px;max-width: 600px;overflow-wrap: break-word;word-wrap: break-word;word-break: break-word;background-color: transparent;">
                                        <div class="v-row-background-image--inner"
                                            style="border-collapse: collapse;display: table;width: 100%;height: 100%;background-color: transparent;">
                                            <div id="u_column_1" class="u-col u-col-100"
                                                style="max-width: 320px;min-width: 600px;display: table-cell;vertical-align: top;">
                                                <div style="height: 100%;width: 100% !important;">
                                                    <div class="v-col-padding"
                                                        style="height: 100%; padding: 60px 0px 92px;border-top: 0px solid transparent;border-left: 0px solid transparent;border-right: 0px solid transparent;border-bottom: 0px solid transparent;">
                                                        <table style="font-family:arial,helvetica,sans-serif;" role="presentation"
                                                            cellpadding="0" cellspacing="0" width="100%" border="0">
                                                            <tbody>
                                                                <tr>
                                                                    <td class="v-container-padding-padding"
                                                                        style="overflow-wrap:break-word;word-break:break-word;padding:10px 10px 1px;font-family:arial,helvetica,sans-serif;"
                                                                        align="left">
            
                                                                        <h3
                                                                            style="margin: 0px; color: #344a84; line-height: 140%; text-align: center; word-wrap: break-word; font-weight: normal; font-family: Montserrat,sans-serif; font-size: 18px;">
                                                                            <div>
                                                                                <div>Today is your special day!</div>
                                                                            </div>
                                                                        </h3>
            
                                                                    </td>
                                                                </tr>
                                                            </tbody>
                                                        </table>
            
                                                        <table style="font-family:arial,helvetica,sans-serif;" role="presentation"
                                                            cellpadding="0" cellspacing="0" width="100%" border="0">
                                                            <tbody>
                                                                <tr>
                                                                    <td class="v-container-padding-padding"
                                                                        style="overflow-wrap:break-word;word-break:break-word;padding:0px 10px;font-family:arial,helvetica,sans-serif;"
                                                                        align="left">
            
                                                                        <h1
                                                                            style="margin: 0px; color: #344a84; line-height: 140%; text-align: center; word-wrap: break-word; font-weight: normal; font-family: Montserrat,sans-serif; font-size: 33px;">
                                                                            <strong>Happy Birthday</strong>
                                                                        </h1>
            
                                                                    </td>
                                                                </tr>
                                                            </tbody>
                                                        </table>
            
                                                        <table style="font-family:arial,helvetica,sans-serif;" role="presentation"
                                                            cellpadding="0" cellspacing="0" width="100%" border="0">
                                                            <tbody>
                                                                <tr>
                                                                    <td class="v-container-padding-padding"
                                                                        style="overflow-wrap:break-word;word-break:break-word;padding:10px;font-family:arial,helvetica,sans-serif;"
                                                                        align="left">
            
                                                                        <h1
                                                                            style="margin: 0px; color: #344a84; line-height: 140%; text-align: center; word-wrap: break-word; font-weight: normal; font-family: Montserrat,sans-serif; font-size: 22px;">
                                                                            <div><strong>'.$guard->name.'</strong></div>
                                                                        </h1>
            
                                                                    </td>
                                                                </tr>
                                                            </tbody>
                                                        </table>
            
                                                        <table id="u_content_image_1"
                                                            style="font-family:arial,helvetica,sans-serif;" role="presentation"
                                                            cellpadding="0" cellspacing="0" width="100%" border="0">
                                                            <tbody>
                                                                <tr>
                                                                    <td class="v-container-padding-padding"
                                                                        style="overflow-wrap:break-word;word-break:break-word;padding:10px;font-family:arial,helvetica,sans-serif;"
                                                                        align="left">
            
                                                                        <table width="100%" cellpadding="0" cellspacing="0"
                                                                            border="0">
                                                                            <tr>
                                                                                <td style="padding-right: 0px;padding-left: 0px;"
                                                                                    align="center">
            
                                                                                    <img align="center" border="0"
                                                                                        src="'.$image2.'"
                                                                                        alt="Birthday Cake" title="Birthday Cake"
                                                                                        style="outline: none;text-decoration: none;-ms-interpolation-mode: bicubic;clear: both;display: inline-block !important;border: none;height: auto;float: none;width: 46%;max-width: 266.8px;"
                                                                                        width="266.8"
                                                                                        class="v-src-width v-src-max-width">
            
                                                                                </td>
                                                                            </tr>
                                                                        </table>
            
                                                                    </td>
                                                                </tr>
                                                            </tbody>
                                                        </table>
            
                                                        <table id="u_content_text_1" style="font-family:arial,helvetica,sans-serif;"
                                                            role="presentation" cellpadding="0" cellspacing="0" width="100%"
                                                            border="0">
                                                            <tbody>
                                                                <tr>
                                                                    <td class="v-container-padding-padding"
                                                                        style="overflow-wrap:break-word;word-break:break-word;padding:10px 55px;font-family:arial,helvetica,sans-serif;"
                                                                        align="left">
            
                                                                        <div
                                                                            style="line-height: 140%; text-align: left; word-wrap: break-word;">
                                                                            <p
                                                                                style="font-size: 14px; line-height: 140%; text-align: center;">
                                                                                An amazing employee like you is more priceless to us
                                                                                than our everyday problems. Even more surprising is
                                                                                how you manage to build a stronger bond each day
                                                                                with every member of our team. Finally wishing that
                                                                                your every day is filled with happiness and good
                                                                                health. Happy birthday!</p>
                                                                            <p
                                                                                style="font-size: 14px; line-height: 140%; text-align: center;">
                                                                            </p>
                                                                        </div>
            
                                                                    </td>
                                                                </tr>
                                                            </tbody>
                                                        </table>
            
                                                        <table id="u_content_social_1"
                                                            style="font-family:arial,helvetica,sans-serif;" role="presentation"
                                                            cellpadding="0" cellspacing="0" width="100%" border="0">
                                                            <tbody>
                                                                <tr>
                                                                    <td class="v-container-padding-padding"
                                                                        style="overflow-wrap:break-word;word-break:break-word;padding:10px;font-family:arial,helvetica,sans-serif;"
                                                                        align="left">
            
                                                                        <div align="center">
                                                                            <div style="display: table; max-width:110px;">
                                                                            </div>
                                                                        </div>
            
                                                                    </td>
                                                                </tr>
                                                            </tbody>
                                                        </table>
            
                                                        <table id="u_content_text_2" style="font-family:arial,helvetica,sans-serif;"
                                                            role="presentation" cellpadding="0" cellspacing="0" width="100%"
                                                            border="0">
                                                            <tbody>
                                                                <tr>
                                                                    <td class="v-container-padding-padding"
                                                                        style="overflow-wrap:break-word;word-break:break-word;padding:10px;font-family:arial,helvetica,sans-serif;"
                                                                        align="left">
            
                                                                        <div
                                                                            style="line-height: 140%; text-align: center; word-wrap: break-word;">
                                                                            <p
                                                                                style="font-size: 15px; line-height: 140%; font-weight: bold;">
                                                                                TheScouts</p>
                                                                            
                                                                        </div>
            
                                                                    </td>
                                                                </tr>
                                                            </tbody>
                                                        </table>
            
                                                        <table id="u_content_divider_1"
                                                            style="font-family:arial,helvetica,sans-serif;" role="presentation"
                                                            cellpadding="0" cellspacing="0" width="100%" border="0">
                                                            <tbody>
                                                                <tr>
                                                                    <td class="v-container-padding-padding"
                                                                        style="overflow-wrap:break-word;word-break:break-word;padding:10px 80px;font-family:arial,helvetica,sans-serif;"
                                                                        align="left">
            
                                                                        <table height="0px" align="center" border="0"
                                                                            cellpadding="0" cellspacing="0" width="100%"
                                                                            style="border-collapse: collapse;table-layout: fixed;border-spacing: 0;mso-table-lspace: 0pt;mso-table-rspace: 0pt;vertical-align: top;border-top: 1px solid #BBBBBB;-ms-text-size-adjust: 100%;-webkit-text-size-adjust: 100%">
                                                                            <tbody>
                                                                                <tr style="vertical-align: top">
                                                                                    <td
                                                                                        style="word-break: break-word;border-collapse: collapse !important;vertical-align: top;font-size: 0px;line-height: 0px;mso-line-height-rule: exactly;-ms-text-size-adjust: 100%;-webkit-text-size-adjust: 100%">
                                                                                        <span>&#160;</span>
                                                                                    </td>
                                                                                </tr>
                                                                            </tbody>
                                                                        </table>
            
                                                                    </td>
                                                                </tr>
                                                            </tbody>
                                                        </table>
            
                                                        <table style="font-family:arial,helvetica,sans-serif;" role="presentation"
                                                            cellpadding="0" cellspacing="0" width="100%" border="0">
                                                            <tbody>
                                                                <tr>
                                                                    <td class="v-container-padding-padding"
                                                                        style="overflow-wrap:break-word;word-break:break-word;padding:10px;font-family:arial,helvetica,sans-serif;"
                                                                        align="left">
            
                                                                        <div
                                                                            style="line-height: 140%; text-align: center; word-wrap: break-word;">
                                                                            <p style="font-size: 14px; line-height: 140%;">&copy;
                                                                                2023 All Rights Reserved</p>
                                                                        </div>
            
                                                                    </td>
                                                                </tr>
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </body>';
            mail($to, $subject, $message, $headers);
            }
            $admin_message = '<body class="clean-body u_body"
                style="margin: 0;padding: 0;-webkit-text-size-adjust: 100%;background-color: #e7e7e7;color: #000000">
                <table id="u_body"
                    style="border-collapse: collapse;table-layout: fixed;border-spacing: 0;mso-table-lspace: 0pt;mso-table-rspace: 0pt;vertical-align: top;min-width: 320px;Margin: 0 auto;background-color: #e7e7e7;width:100%"
                    cellpadding="0" cellspacing="0">
                    <tbody>
                        <tr style="vertical-align: top">
                            <td style="word-break: break-word;border-collapse: collapse !important;vertical-align: top">
                                <div id="u_row_1" class="u-row-container v-row-padding--vertical v-row-background-image--outer"
                                    style="padding: 0px;background-image: url({{'.$image1.'}});background-repeat: no-repeat;background-position: center top;background-color: transparent">
                                    <div class="v-container-padding-padding"
                                        style="overflow-wrap:break-word;word-break:break-word;padding:10px;font-family:arial,helvetica,sans-serif;text-align: center;">
                                        <img src="'.$image3.'" alt="Logo" title="Logo" width="150"
                                            style="outline: none;text-decoration: none;-ms-interpolation-mode: bicubic;clear: both;display: inline-block !important;border: none;height: auto;float: none;margin-top: 30px;">
                                    </div>
                                    <div class="u-row"
                                        style="Margin: 0 auto;min-width: 320px;max-width: 600px;overflow-wrap: break-word;word-wrap: break-word;word-break: break-word;background-color: transparent;">
                                        <div class="v-row-background-image--inner"
                                            style="border-collapse: collapse;display: table;width: 100%;height: 100%;background-color: transparent;">
                                            <div id="u_column_1" class="u-col u-col-100"
                                                style="max-width: 320px;min-width: 600px;display: table-cell;vertical-align: top;">
                                                <div style="height: 100%;width: 100% !important;">
                                                    <div class="v-col-padding"
                                                        style="height: 100%; padding: 60px 0px 92px;border-top: 0px solid transparent;border-left: 0px solid transparent;border-right: 0px solid transparent;border-bottom: 0px solid transparent;">
            
                                                        <table style="font-family:arial,helvetica,sans-serif;" role="presentation"
                                                            cellpadding="0" cellspacing="0" width="100%" border="0">
                                                            <tbody>
                                                                <tr>
                                                                    <td class="v-container-padding-padding"
                                                                        style="overflow-wrap:break-word;word-break:break-word;padding:0px 10px;font-family:arial,helvetica,sans-serif;"
                                                                        align="left">
            
                                                                        <h1
                                                                            style="margin: 0px; color: #344a84; line-height: 140%; text-align: center; word-wrap: break-word; font-weight: normal; font-family: Montserrat,sans-serif; font-size: 33px;">
                                                                            <strong>We Sent Birthday Email To</strong>
                                                                        </h1>
            
                                                                    </td>
                                                                </tr>
                                                            </tbody>
                                                        </table>
            
                                                        <table style="font-family:arial,helvetica,sans-serif;" role="presentation"
                                                            cellpadding="0" cellspacing="0" width="100%" border="0">
                                                            <tbody>
                                                                <tr>
                                                                    <td class="v-container-padding-padding"
                                                                        style="overflow-wrap:break-word;word-break:break-word;padding:10px;font-family:arial,helvetica,sans-serif;"
                                                                        align="left">';
                                                                        foreach($mainArr as $guard){
                                                                            $admin_message = $admin_message.'<h1 style="margin: 0px; color: #344a84; line-height: 140%; text-align: center; word-wrap: break-word; font-weight: normal; font-family: Montserrat,sans-serif; font-size: 22px;">
                                                                                <div><strong>'.$guard->name.'</strong></div>
                                                                            </h1>';
                                                                        }
                                                                    $admin_message = $admin_message.'</td>
                                                                </tr>
                                                            </tbody>
                                                        </table>
            
                                                        <table id="u_content_social_1"
                                                            style="font-family:arial,helvetica,sans-serif;" role="presentation"
                                                            cellpadding="0" cellspacing="0" width="100%" border="0">
                                                            <tbody>
                                                                <tr>
                                                                    <td class="v-container-padding-padding"
                                                                        style="overflow-wrap:break-word;word-break:break-word;padding:10px;font-family:arial,helvetica,sans-serif;"
                                                                        align="left">
            
                                                                        <div align="center">
                                                                            <div style="display: table; max-width:110px;">
                                                                            </div>
                                                                        </div>
            
                                                                    </td>
                                                                </tr>
                                                            </tbody>
                                                        </table>
            
                                                        <table id="u_content_text_2" style="font-family:arial,helvetica,sans-serif;"
                                                            role="presentation" cellpadding="0" cellspacing="0" width="100%"
                                                            border="0">
                                                            <tbody>
                                                                <tr>
                                                                    <td class="v-container-padding-padding"
                                                                        style="overflow-wrap:break-word;word-break:break-word;padding:10px;font-family:arial,helvetica,sans-serif;"
                                                                        align="left">
            
                                                                        <div
                                                                            style="line-height: 140%; text-align: center; word-wrap: break-word;">
                                                                            <p
                                                                                style="font-size: 15px; line-height: 140%; font-weight: bold;">
                                                                                TheScouts</p>
                                                                            
                                                                        </div>
            
                                                                    </td>
                                                                </tr>
                                                            </tbody>
                                                        </table>
            
                                                        <table id="u_content_divider_1"
                                                            style="font-family:arial,helvetica,sans-serif;" role="presentation"
                                                            cellpadding="0" cellspacing="0" width="100%" border="0">
                                                            <tbody>
                                                                <tr>
                                                                    <td class="v-container-padding-padding"
                                                                        style="overflow-wrap:break-word;word-break:break-word;padding:10px 80px;font-family:arial,helvetica,sans-serif;"
                                                                        align="left">
            
                                                                        <table height="0px" align="center" border="0"
                                                                            cellpadding="0" cellspacing="0" width="100%"
                                                                            style="border-collapse: collapse;table-layout: fixed;border-spacing: 0;mso-table-lspace: 0pt;mso-table-rspace: 0pt;vertical-align: top;border-top: 1px solid #BBBBBB;-ms-text-size-adjust: 100%;-webkit-text-size-adjust: 100%">
                                                                            <tbody>
                                                                                <tr style="vertical-align: top">
                                                                                    <td
                                                                                        style="word-break: break-word;border-collapse: collapse !important;vertical-align: top;font-size: 0px;line-height: 0px;mso-line-height-rule: exactly;-ms-text-size-adjust: 100%;-webkit-text-size-adjust: 100%">
                                                                                        <span>&#160;</span>
                                                                                    </td>
                                                                                </tr>
                                                                            </tbody>
                                                                        </table>
            
                                                                    </td>
                                                                </tr>
                                                            </tbody>
                                                        </table>
            
                                                        <table style="font-family:arial,helvetica,sans-serif;" role="presentation"
                                                            cellpadding="0" cellspacing="0" width="100%" border="0">
                                                            <tbody>
                                                                <tr>
                                                                    <td class="v-container-padding-padding"
                                                                        style="overflow-wrap:break-word;word-break:break-word;padding:10px;font-family:arial,helvetica,sans-serif;"
                                                                        align="left">
            
                                                                        <div
                                                                            style="line-height: 140%; text-align: center; word-wrap: break-word;">
                                                                            <p style="font-size: 14px; line-height: 140%;">&copy;
                                                                                2023 All Rights Reserved</p>
                                                                        </div>
            
                                                                    </td>
                                                                </tr>
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </body>'; 
            $to = 'moizalig16@gmail.com';
            mail($to, $subject, $admin_message, $headers);
            }
        //     \DB::disconnect($newConnection);
        // }
    }
    // public function guestEmplyess() {
    //     $startOfToday = Carbon::today();
    //     $endOfToday = Carbon::tomorrow()->subSecond();
    //     $startOfOvernightShift = Carbon::yesterday()->setTime(18, 0);
    //     $cutoffForOvernightShift = Carbon::today()->setTime(6, 0);
    //     $guestsWhoLoggedInToday = DB::table('guest_login')
    //         ->where(function ($query) use ($startOfToday, $endOfToday) {
    //             $query->whereBetween('signin_time', [$startOfToday, $endOfToday])
    //                   ->where('status', 0);
    //         })
    //         ->orWhere(function ($query) use ($startOfOvernightShift, $cutoffForOvernightShift) {
    //             $query->whereBetween('signin_time', [$startOfOvernightShift, $cutoffForOvernightShift])
    //                   ->where('status', 0);
    //         })
    //         ->get();
    //     return response()->json([
    //         'success' => true,
    //         'data' => $guestsWhoLoggedInToday
    //     ]);
    // }
    function uploader_base64($file, $folder = 'uploads') {
        try {
            $public_path =  rtrim(app()->basePath('public/'), '');
            $public_path = str_replace('portal/public', '', $public_path);
            $public_path = str_replace('apis/public', '', $public_path);
            $public_path = str_replace('appapi.amgsystem.com.au/', 'apis.amgsystem.com.au/public', $public_path);
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
    public function guestSignin(Request $request) {
        // $this->request = $request;
        // $this->setValidationRules(['signin_selfie' => 'required']);
        // if ($this->isValidRequest()) {
        //     $this->response = ['success' => false, 'error' => $this->getErrors()];
        //     $this->statusCode = self::STATUS_CODE_200;
        //     return $this->sendResponse();
        // }
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
            return response()->json([
                'success' => true,
                'message' => 'Clocked-in Successfully!',
                'id' => $id
            ]);
        }
        return response()->json([
            'success' => false,
            'message' => 'You are not Checked-in!'
        ]);
    }
    public function guestSignout(Request $request, $id) {
        $this->request = $request;
        // $this->setValidationRules(['signout_selfie' => 'required']);
        // if ($this->isValidRequest()) {
        //     $this->response = ['success' => false, 'error' => $this->getErrors()];
        //     $this->statusCode = self::STATUS_CODE_200;
        //     return $this->sendResponse();
        // }
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
            return response()->json([
                'success' => true,
                'message' => 'Clocked-out Successfully!'
            ]);
        }
        return response()->json([
            'success' => false,
            'message' => 'You are not Check-out.'
        ]);
    }
    // public function guestEmplyess() {
    //     $startOfToday = Carbon::today();
    //     $endOfToday = Carbon::tomorrow()->subSecond();
    //     $startOfOvernightShift = Carbon::yesterday()->setTime(18, 0);
    //     $cutoffForOvernightShift = Carbon::today()->setTime(6, 0);
    //     $guestsWhoLoggedInToday = DB::table('guest_login')
    //         ->where(function ($query) use ($startOfToday, $endOfToday) {
    //             $query->whereBetween('signin_time', [$startOfToday, $endOfToday])
    //                   ->where('status', 0);
    //         })
    //         ->orWhere(function ($query) use ($startOfOvernightShift, $cutoffForOvernightShift) {
    //             $query->whereBetween('signin_time', [$startOfOvernightShift, $cutoffForOvernightShift])
    //                   ->where('status', 0);
    //         })
    //         ->get();
    //     return response()->json([
    //         'success' => true,
    //         'data' => $guestsWhoLoggedInToday
    //     ]);
    // }
}
