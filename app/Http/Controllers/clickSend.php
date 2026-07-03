<?php

namespace App\Http\Controllers;

use App\Http\Resources\FilterGuardResource;
use App\Models\Guard;
use App\Models\JobRoster;
use App\Models\portal\PortalSettings;
use App\Models\Site;
use Illuminate\Http\Request;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Client;
use ClickSend\Configuration;
use ClickSend\Api\SMSApi;
use Illuminate\Support\Str;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class clickSend extends Controller
{

    public function filterGuards(Request $request)
    {
        $guards_ids = [];
        if($request->has('customer_id')  && $request->has('site_id')  && $request->has('guard_status') ){
         $sites = Site::whereIn('customer_id', $request->customer_id)->where('id', $request->site_id)->get(); 
         foreach ($sites as $key => $s) {
            $jobrosters = JobRoster::where('site_id', $s->id)->get();
            foreach ($jobrosters as $key => $value) {
                $guards_ids[] = $value->guard_id;
            }
        }
        $guards =  Guard::whereIn('id', $guards_ids)->where('guard_status', $request->guard_status)->get();
        $gds = FilterGuardResource::collection($guards);
        return response()->json(["data" => $gds ,  'code' => 200, 'success' => true]); 

    }elseif(($request->has('customer_id') && $request->has('guard_status'))){
        $sites = Site::whereIn('customer_id', $request->customer_id)->get(); 
        foreach ($sites as $key => $s) {
            $jobrosters = JobRoster::where('site_id', $s->id)->get();
            foreach ($jobrosters as $key => $value) {
                $guards_ids[] = $value->guard_id;
            }
        }
        $guards =  Guard::whereIn('id', $guards_ids)->where('guard_status', $request->guard_status)->get();
        $gds = FilterGuardResource::collection($guards);
        return response()->json(["data" => $gds ,  'code' => 200, 'success' => true]);
    }elseif($request->has('customer_id')){
        $sites = Site::whereIn('customer_id', $request->customer_id)->get(); 
        foreach ($sites as $key => $s) {
           $jobrosters = JobRoster::where('site_id', $s->id)->get();
           foreach ($jobrosters as $key => $value) {
               $guards_ids[] = $value->guard_id;
           }
       }
       $guards =  Guard::whereIn('id', $guards_ids)->get();
       $gds = FilterGuardResource::collection($guards);
       return response()->json(["data" => $gds ,  'code' => 200, 'success' => true]);
   } 
   elseif($request->has('site_id')){
       $jobrosters = JobRoster::whereIn('site_id', $request->site_id)->get();
       foreach ($jobrosters as $key => $value) {
           $guards_ids[] = $value->guard_id;
       }
       $guards =  Guard::whereIn('id', $guards_ids)->get();
       $gds = FilterGuardResource::collection($guards);
       return response()->json(["data" => $gds ,  'code' => 200, 'success' => true]);
   }else{
    $guards = Guard::all();
    $gds = FilterGuardResource::collection($guards);
    return response()->json(["data" => $gds ,  'code' => 200, 'success' => true]);
}
}
function sendSMS(Request $request)
{
    $send = false;
    $to = [];
    $token =Str::replace('Bearer', '', $request->header('AuthorizationToken'));
    $token =Str::replace(' ', '', $token);
    $user = User::where('auth_token', $token)->first();
    $api_key = PortalSettings::select('id', 'click_send_username', 'click_send_key', 'air_call_app_id', 'air_call_token')->first();
    // getenv('CLICK_SEND_USERNAME')
    // getenv('CLICK_SEND_API_KEY')
    $config = Configuration::getDefaultConfiguration()
    ->setUsername($api_key->click_send_username)
    ->setPassword($api_key->click_send_key);
    if($request->has('to')){
        if (!is_array($request->to)){
            $to = json_decode($request->to, true);
        }else{
            $to = $request->to;
        }
        
        $guards = Guard::whereIn('id', $to)->select('id', 'phone')->get();
        
        foreach($guards as $guard){
            
            $guard->phone = str_replace('(', '', $guard->phone);
            $guard->phone = str_replace(')', '', $guard->phone);
            $guard->phone = str_replace('-', '', $guard->phone);
            $guard->phone = str_replace(' ', '', $guard->phone);
            $phone_ = str_split($guard->phone);

            if (sizeof($phone_) == 10) {
                $guard->phone = '+61'.$guard->phone;
            }
            $apiInstance = new SMSApi(new Client(),$config);
            $msg = new \ClickSend\Model\SmsMessage();
            $msg->setBody($request->body); 
            $msg->setTo($guard->phone);
            $msg->setSource("sdk"); 
            // \ClickSend\Model\SmsMessageCollection | SmsMessageCollection model
            $sms_messages = new \ClickSend\Model\SmsMessageCollection(); 
            $sms_messages->setMessages([$msg]);
            try {
                $result = $apiInstance->smsSendPost($sms_messages);
                $result = json_decode($result, true);
                // print_r($result);
                // exit;
                if ($result['response_code'] == 'SUCCESS' && $result['data']['messages'][0]['status'] == 'SUCCESS') {
                    DB::table('sms_history')->insert([
                        'admin_id' => $user->id,
                        'msg_body' => $request->body,
                        'to' => $guard->id,
                        'to_number' => trim($result['data']['messages'][0]['to']),
                        'direction' => 'out',
                            // 'direction' => $result['data']['messages'][0]['direction'],
                        'datetime' => $result['data']['messages'][0]['date'],
                        'message_id' => $result['data']['messages'][0]['message_id'],
                        'user_id' => trim($result['data']['messages'][0]['user_id']),
                        'created_at' => date('Y-m-d H:i:s')
                    ]);
                    $id = DB::getPdo()->lastInsertId();
                    jobRosterActions($request->admin_id, 'send_sms', $id, 'sms_history');
                    $send = true;
                }
            } catch (Exception $e) {
            }
        }
    }else{
        foreach($request->phone as $guard){
            $guard['number'] = str_replace('(', '', $guard['number']);
            $guard['number'] = str_replace(')', '', $guard['number']);
            $guard['number'] = str_replace('-', '', $guard['number']);
            $guard['number'] = str_replace(' ', '', $guard['number']);
            $number_ = str_split($guard['number']);
    
            if (sizeof($number_) == 10) {
                $guard['number'] = '+1'.$guard['number'];
            }
            $apiInstance = new SMSApi(new Client(),$config);
            $msg = new \ClickSend\Model\SmsMessage();
            $msg->setBody($request->body); 
            $msg->setTo($guard['number']);
            $msg->setSource("sdk"); 
            // \ClickSend\Model\SmsMessageCollection | SmsMessageCollection model
            $sms_messages = new \ClickSend\Model\SmsMessageCollection(); 
            $sms_messages->setMessages([$msg]);
            try {
                $result = $apiInstance->smsSendPost($sms_messages);
                $result = json_decode($result, true);
                // print_r($result);
                // exit;
                if ($result['response_code'] == 'SUCCESS' && $result['data']['messages'][0]['status'] == 'SUCCESS') {
                    DB::table('sms_history')->insert([
                        'admin_id' => $user->id,
                        'msg_body' => $request->body,
                        'to' => $guard['number'],
                        'to_number' => trim($result['data']['messages'][0]['to']),
                        'direction' => 'out',
                            // 'direction' => $result['data']['messages'][0]['direction'],
                        'datetime' => $result['data']['messages'][0]['date'],
                        'message_id' => $result['data']['messages'][0]['message_id'],
                        'user_id' => trim($result['data']['messages'][0]['user_id']),
                        'created_at' => date('Y-m-d H:i:s')
                    ]);
                    $id = DB::getPdo()->lastInsertId();
                    jobRosterActions($request->admin_id, 'send_sms', $id, 'sms_history');
                    $send = true;
                }
            } catch (Exception $e) {
            }
        }
    }
    if($send){
        return response()->json(['message' => "Message sent successfully." ,  'code' => 200, 'success' => true],200); 
    }else{
       return response()->json(['message' => "Fail to message sent!" ,  'code' => 200, 'success' => false],200); 
   }
}

function receiveMessage(Request $request)
{
    $data = [
        'from_number' => $request->from,
        'msg_body' => $request->body,
        'direction' => 'in',
        'datetime' => $request->timestamp,
        'message_id' => $request->message_id,
        'original_message_id' => $request->original_message_id,
        'user_id' => $request->user_id,
        'seen_status' => 'unseen',
        'created_at' => date('Y-m-d H:i:s')
    ];
    $inserted = DB::table('sms_history')->insert($data);
    $id = DB::getPdo()->lastInsertId();
    jobRosterActions($request->admin->id, 'send_sms', $id, 'sms_history');
    if ($inserted) {
        return response()->json(['response_msg' => "SMS receive successfully." , 'http_code' => 200, 'response_code' => 'SUCCESS'],200); 
    }else{
        return response()->json(['response_msg' => "Fail to receive sms!" ,  'http_code' => 401, 'response_code' => 'FAIL'],401); 
    }
}

function isNewMessage(Request $request)
{
 
    $is_any = DB::table('sms_history')->where(['seen_status' => 'unseen', 'user_id' => $request->user_id])->first();
    if (!empty($is_any)) {
        return response()->json(['message' => "New message receive." ,  'code' => 200, 'success' => true, 'msg' => $is_any],200); 
    }else{
        return response()->json(['message' => "No new message!" ,  'code' => 200, 'success' => false, 'msg' => null],200); 
    }
}


public function getMessageHistory(Request $request)
{
    $query = DB::table('sms_history')
    ->join('guards', 'guards.id', '=', 'sms_history.to', 'left');
    if ($request->has('type')) {
        if ($request->type == 'in') {
            $query->where('sms_history.direction', 'in');
        }elseif ($request->type == 'out') {
            $query->where('sms_history.direction', 'out');
        }
        // dd($request->type);
    } 
    if ($request->has('admin_id') && $request->admin_id != '') {
        $query->where('admin_id', $request->admin_id);
    } 
    if ($request->has('from') && $request->from != '') {
        $query->where('sms_history.datetime', '>=',strtotime($request->from));
    }else{
        $query->where('sms_history.datetime', '>=',strtotime(date('m/d/Y 00:00:00')));
    }  

    if ($request->has('to') && $request->to != '') {
        $query->where('sms_history.datetime', '<=',strtotime($request->to));
    }else{
        $query->where('sms_history.datetime', '<=',strtotime(date('m/d/Y 23:59:59')));
    }
    $query->orderBy('sms_history.datetime', 'DESC');
    $query->join('users','users.id', '=', 'sms_history.admin_id');
    $query->select('sms_history.*', 'guards.first_name', 'guards.last_name', 'guards.email', 'users.name');  
    $chat = $query->get();  
    if (count($chat) > 0) {
        return response()->json(['message' => "History found." ,  'http_code' => 200, 'success' => true, 'chat' => $chat],200); 
    }else{
        return response()->json(['message' => "No hoitory found!" ,  'http_code' => 200, 'success' => false, 'chat' => $chat],200); 
    }
}

function getChatUser(Request $request)
{
    $from = time() - (60*60*24*30);
    $to = time();
    if ($request->has('date') && $request->date != '') {
        $from = strtotime(($request->date .' 00:00:00'));
        $to = strtotime(($request->date .' 23:59:59'));
    }
    $users = DB::table('sms_history')
    ->join('guards', 'guards.id', '=', 'sms_history.to')
    ->where('sms_history.direction', 'out')
    ->where('sms_history.datetime', '>=', $from)
    ->where('sms_history.datetime', '<=', $to)
    ->select('sms_history.to_number', 'guards.id as id', 'guards.first_name', 'guards.last_name', 'sms_history.user_id')
    // ->orderBy('sms_history.datetime', 'DESC')
    ->groupBy('guards.id')
    ->groupBy('guards.first_name')
    ->groupBy('guards.last_name')
    ->groupBy('sms_history.to_number')
    ->groupBy('sms_history.user_id')
    ->orderBy('guards.first_name', 'asc')
    // ->groupBy('sms_history.to')
    ->get();
    if (count($users) > 0) {
        return response()->json(['message' => "User list found." ,  'http_code' => 200, 'success' => true, 'users' => $users],200); 
    }else{
        return response()->json(['message' => "No chat user found!" ,  'http_code' => 200, 'success' => false, 'users' => $users],200); 
    }
}
function getChat(Request $request)
{
    $from = time() - (60*60*24*30);
    $to = time();
    if ($request->has('date') && $request->date != '') {
        $from = strtotime(($request->date .' 00:00:00'));
        $to = strtotime(($request->date .' 23:59:59'));
    }
    $chat = DB::table('sms_history')
    // ->where('to_number', $request->to_number)
    ->where('to', $request->user_id)
    ->where('sms_history.datetime', '>=', $from)
    ->where('sms_history.datetime', '<=', $to)
    ->orderBy('datetime', 'ASC')
    ->get();
    if (count($chat) > 0) {
        DB::table('sms_history')
        ->where('to', $request->user_id)
        ->where('direction', 'in')
        ->update(['seen_status' => 'seen']);
        return response()->json(['message' => "Chat found." ,  'http_code' => 200, 'success' => true, 'chat' => $chat],200); 
    }else{
        return response()->json(['message' => "No chat found!" ,  'http_code' => 200, 'success' => false, 'chat' => $chat],200); 
    }
}

}
