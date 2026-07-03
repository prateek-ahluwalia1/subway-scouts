<?php

namespace App\Http\Controllers\crm;

use App\Http\Controllers\Controller;
use App\Http\Resources\EmailHistoryResource;
use App\Models\EmailHistory;
use Illuminate\Http\Request;
use App\Models\Guard;
use App\Models\User;
use Mail;
use DB;

class EmailController extends Controller
{
    function sendEmail(Request $request)
    {
        $to = [];
        if (!is_array($request->bcc)) {
            $to = json_decode($request->bcc, true);
        } else {
            $to = $request->bcc;
        }

        $data = [
            'title' => $request->subject ?? 'TheScout',
            'body' => $request->body,
        ];

        $subject = $data['title'];
        $messageBody = $data['body'];
        $attachmentWithUrl = [];
        $media = [];
        foreach($request->attachments as $attachment){
            $newObject = new \stdClass();
            $newObject->url = $attachment['url'];
            $newObject->name = $attachment['name'];
            $media[] = $newObject;
            $attachmentWithUrl[] = public_path('crm_customers_file/'.$attachment['name']);
        }
        foreach($to as $sendTo){
            if (filter_var($sendTo, FILTER_VALIDATE_EMAIL)) {
                Mail::send('mail.email', $data, function ($message) use ($sendTo, $subject, $attachmentWithUrl) {
                $message->from('no-reply@thescouts.com.au', 'AMG Security')
                ->to($sendTo)
                ->subject($subject);
                    foreach($attachmentWithUrl as $attachment){
                        $message->attach($attachment);
                    }
                });
            }
        }
        $makeHistory = new EmailHistory();
        $makeHistory->lead_id = $request->lead_id;
        $makeHistory->user_id = $request->user_id;
        $makeHistory->to = json_encode($request->bcc);
        $makeHistory->subject = $subject;
        $makeHistory->message = $messageBody;
        $makeHistory->attachments = json_encode($media);
        $makeHistory->save();
        jobRosterActions($request->user_id, 'send_mail', $request->lead_id, 'crm_customers');#CREATED BY
        
        return response()->json(['message' => "Email send successfully." ,  'code' => 200, 'success' => true],200);
    }
    public function sendEmailHistory($id){
        $data = EmailHistory::where('lead_id', $id)->with(['User', 'Lead'])->get();
        return response()->json([
            "success" => true,
            'data' => EmailHistoryResource::collection($data),
        ]);
    }
}
