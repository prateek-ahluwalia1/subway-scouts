<?php
namespace App\Http\Controllers\Api;


use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;


class InboxController extends ApiController
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(Request $request) {
        parent::__construct($request);
        

    }

     public function get_messages(Request $request, $id)
    {    

    $debug = array();   

        $last_timestamp = time() - (86400*2);
        if($request->input('last_timestamp') != ''){
           $last_timestamp = $request->input('last_timestamp'); 
        }
        if ($request->has('receiver_id')) {
        $receiver_id = $request->input('receiver_id');
        $result = DB::table('inbox')->where('timestamp_sent', '>=', $last_timestamp)->where(
            function($query) use ($id, $receiver_id){ $query->where(['sender_id' =>  $id, 'receiver_id' => $receiver_id])->orWhere(['sender_id' =>  $receiver_id, 'receiver_id' => $id]); }
        )->orderBy('id', 'ASC')->get();
        }else{
            $result = DB::table('inbox')->where('timestamp_sent', '>=', $last_timestamp)->where(function($query) use ($id){ $query->where('sender_id', '=', $id)->orWhere('receiver_id', '=', $id); })->orderBy('id', 'ASC')->get();
        }

        // $debug['last_timestamp'] = $last_timestamp;
        // $debug['query'] = DB::table('inbox')->where('timestamp_sent', '>=', $last_timestamp)->where(function($query) use ($id){ $query->where('sender_id', '=', $id)->orWhere('receiver_id', '=', $id); })->orderBy('id', 'ASC')->toSql();

        $messages = array(); 

        $receiver_id = 1;

  
            foreach($result as $r){

                if($r->sender_id == $id){
                    $align = 'right';
                }
                else{
                    $align = 'left';
                }


                $messages[] = array('align' => $align, 'message' => $r->message, 'datetime' => date('d-m-Y H:i', $r->timestamp_sent));

            }


            $this->statusCode = self::STATUS_CODE_200;
            $this->response = [
                'success' => true,
                'message' => 'Retrieved messages',
                'list_messages' => $messages,
                'last_timestamp' => time(),
                'receiver_id' => $receiver_id
            ];
            return $this->sendResponse();
        
       
       
    }


      public function send_message(Request $request, $id)
    {   

     
        $data = array(
            'receiver_id' => $request->input('receiver_id'),
            'sender_id' => $id,
            'message' => $request->input('message'),
            'timestamp_sent' => time(),
            'type' => 'text',
            'sender' => 'guard',
            'receiver' => 'administrator'
    );
        $result = DB::table('inbox')->insertGetId($data);

        $guard = DB::table('guards')->where('id', $id)->first();
        $notification = array(
                'guard_id' => $request->input('receiver_id'), 
                'record_id' => $result,
                'message' => $guard->name.' send you a message.',
                'type' => 'chat',
                'send_time' => time(),
                'title' => 'New Message'
            );
            DB::table('portal_notifications')->insert($notification);

            // $result = DB::table('inbox')->insert($data);
       

            $this->statusCode = self::STATUS_CODE_200;
            $this->response = [
                'success' => true,
                'message' => 'Message sent'
            ];
            return $this->sendResponse();
        
       
       
    }

}
