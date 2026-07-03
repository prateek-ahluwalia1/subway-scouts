<?php

namespace App\Http\Controllers;

use App\Models\EmailTemplate;
use Illuminate\Http\Request;

class EmailTemplateController extends Controller
{
    public function storeEmailTemplate(Request $request)
    {
        $storeEmailTemplate = new EmailTemplate();
        $storeEmailTemplate->title = $request->title;
        $storeEmailTemplate->body = json_encode($request->body);
        $storeEmailTemplate->save();
        jobRosterActions($request->admin_id, 'add_email_template', $storeEmailTemplate->id, 'email_templates');
        return response()->json(['success' => true, 'message' => 'E-mail template created', 'code'=> 200]);
    }

    function getEmailTemplates(Request $request)
    {
        $templates = EmailTemplate::all();
        foreach ($templates as $key => $t) {
            $t->body = json_decode($t->body, true);
        }
        if (count($templates) > 0) {
            return response()->json(['success' => true, 'message' => 'Email templates found.', 'code'=> 200, 'templates' => $templates]);
        }else{
            return response()->json(['success' => false, 'message' => 'No template found!', 'code'=> 200, 'templates' => $templates]);
        }
    }

    function getEmailTemplatesTitleAndId(Request $request)
    {
        $templates = EmailTemplate::select('id', 'title', 'body')->get();
        if (count($templates) > 0) {
            return response()->json(['success' => true, 'message' => 'Email templates found.', 'code'=> 200, 'templates' => $templates]);
        }else{
            return response()->json(['success' => false, 'message' => 'No template found!', 'code'=> 200, 'templates' => $templates]);
        }
    }

    function getSingleEmailTemplate(Request $request)
    {
        $template = EmailTemplate::where('id', $request->id)->first();
        $template->body = json_decode($template->body, true);
        if (!empty($template)) {
            return response()->json(['success' => true, 'message' => 'Email templates found.', 'code'=> 200, 'template' => $template]);
        }else{
            return response()->json(['success' => false, 'message' => 'No template found!', 'code'=> 200, 'template' => $template]);
        }
    }

    public function updateEmailTemplate(Request $request)
    {
        $old_data = EmailTemplate::where('id', $request->id)->first();

        $update = EmailTemplate::where('id', $request->id)->update([
            'title' => $request->title, 
            'body' => json_encode($request->body)
        ]);
        if ($update) {
        jobRosterActions($request->admin_id, 'update_email_template', $update['id'], 'email_templates', $old_data);
        return response()->json(['success' => true, 'message' => 'Email Template updated', 'code'=> 200]);
        }else{
        return response()->json(['success' => false, 'message' => 'Email Template fail to update!', 'code'=> 200]);
        }
    }

    public function deleteEmailTemplate(Request $request)
    {
        $old_data = EmailTemplate::where('id', $request->id)->first();
        $delete = EmailTemplate::where('id', $request->id)->delete();
        if ($delete) {
            jobRosterActions($request->admin_id, 'delete_email_template', $old_data->id, 'email_templates', $old_data); 
        return response()->json(['success' => true, 'message' => 'Email Template deleted', 'code'=> 200]);
        }else{
        return response()->json(['success' => false, 'message' => 'Email Template fail to delete!', 'code'=> 200]);
        }
    }
}
