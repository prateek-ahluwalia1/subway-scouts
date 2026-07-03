<?php

namespace App\Http\Controllers;

use App\Models\Guard;
use App\Models\User;
use Illuminate\Http\Request;
use Mail;
use DB;

class EmailController extends Controller
{
    function sendEmail(Request $request)
    {
        $to = [];
        if (!is_array($request->to)) {
            $to = json_decode($request->to, true);
        }else{
            $to = $request->to;
        }
        // $token =Str::replace('Bearer', '', $request->header('AuthorizationToken'));
        // $token =Str::replace(' ', '', $token);
        // $user = User::where('auth_token', $token)->first();
            $data['title'] = $request->subject;
            $data['body'] = $request->message;
            $subject = $request->subject ? $request->subject : '';
            $message = $request->message;


        $guards = Guard::whereIn('id', $to)->select('id', 'email')->get();
        $emails = [];
        foreach ($guards as $g) {
            $emails[] = $g->email; 
        }
        if ($request->has('newStaff') && !empty($request->newStaff)) {
            foreach ($request->newStaff as $key => $g) {
                $emails[] = $g['email']; 
            }
        }
        // $message = $request->message;
        // $bcc = explode(',', $request->bcc);
        $bcc = $request->bcc;
        // $bcc = $request->bcc;
        // $cc = explode(',', $request->cc);
        $cc = $request->cc;
        Mail::send('mail.email', $data, function($message)use($emails, $bcc, $cc, $subject){
            $message->from('no-reply@thescouts.com.au', 'AMG Security')
            ->to($emails);
            if(!empty($bcc))
            {
                foreach ($bcc as $key => $b) {
                    $message->bcc($b['email']);
                }
            }
            if(!empty($cc))
            {
                foreach ($cc as $ky => $c) {
                    $message->cc($c['email']);
                }
            }
            // ->cc($cc)
            $message->subject($subject);
        });
        jobRosterActions($request->admin_id, 'send_email', '', 'emails', json_encode($request->to));
        return response()->json(['message' => "E-mail sent." ,  'code' => 200, 'success' => true],200);
    }
}
