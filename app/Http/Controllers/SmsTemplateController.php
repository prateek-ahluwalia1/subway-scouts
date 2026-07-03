<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SmsTemplate;
use Illuminate\Support\Str;
use App\Models\User;

class SmsTemplateController extends Controller
{
    function getSMSTemplates()
    {
        $templates = SmsTemplate::all();
        if (count($templates) > 0) {
            return response()->json(['message' => "Templates found." ,  'code' => '200', 'success' => true, 'templates' => $templates],200);
        }else{
        return response()->json(['message' => "No template found!" ,  'code' => '200', 'success' => false, 'templates' => $templates],200);
        }
    }

    function addSMSTemplate(Request $request)
    {
        $token =Str::replace('Bearer', '', $request->header('AuthorizationToken'));
        $token =Str::replace(' ', '', $token);
        $user = User::where('auth_token', $token)->first();

        $template = SmsTemplate::insert([
            'admin_id' => $user->id,
            'title' => $request->title,
            'msg_body' => $request->msg_body,
            'created_at' => date('Y-m-d H:i:s'),
        ]);
        $id = \DB::getPdo()->lastInsertId();
        if ($template) {
            jobRosterActions($request->admin_id, 'add_sms_template', $id, 'sms_templates');
            return response()->json(['message' => "SMS Template added" ,  'code' => '200', 'success' => true],200);
        }else{
        return response()->json(['message' => "Fail to add template!" ,  'code' => '200', 'success' => false],200);
        }
    }

    function updateSMSTemplate(Request $request)
    {
        $old_data = SmsTemplate::where('id', $request->id)->first();

        $template = SmsTemplate::where('id', $request->id)->update([
            'title' => $request->title,
            'msg_body' => $request->msg_body,
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
        if ($template) {
            jobRosterActions($request->admin_id, 'update_sms_template', $request->id, 'sms_templates', $old_data);
            return response()->json(['message' => "SMSTemplate Updated" ,  'code' => '200', 'success' => true],200);
        }else{
        return response()->json(['message' => "Fail to update template!" ,  'code' => '200', 'success' => false],200);
        }
    }

    function deleteSMSTemplate(Request $request)
    {
        $old_data = SmsTemplate::where('id', $request->id)->first();

        $template = SmsTemplate::where('id', $request->id)->delete();
        if ($template) {
            jobRosterActions($request->admin_id, 'update_sms_template', $old_data->id, 'sms_templates', $old_data);
            return response()->json(['message' => "SMSTemplate Deleted" ,  'code' => '200', 'success' => true],200);
        }else{
        return response()->json(['message' => "Fail to delete template!" ,  'code' => '200', 'success' => false],200);
        }
    }
}
