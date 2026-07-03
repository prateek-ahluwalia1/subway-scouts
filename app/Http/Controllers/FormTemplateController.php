<?php

namespace App\Http\Controllers;

use App\Http\Resources\FormTemplateResource;
use App\Models\DynamicFormHistory;
use App\Models\FormTemplate;
use App\Models\BuildInFormHistory;
use App\Models\Guard;
use Illuminate\Http\Request;

class FormTemplateController extends Controller
{
    public function store(Request $request)
    {
        $storeFormTemplate = new FormTemplate();
        $storeFormTemplate->title = $request->title;
        $storeFormTemplate->type = $request->type;
        $storeFormTemplate->background = $request->background;
        $storeFormTemplate->logo = json_encode($request->logo);
        $storeFormTemplate->body = json_encode($request->body);
        $storeFormTemplate->save();
        jobRosterActions($request->admin_id, 'add_form_template', $storeFormTemplate->id, 'form_templates');
        return response()->json(['success' => true, 'message' => 'Form Template Created', 'code'=> 200]);
    }

    public function getAllFormTemplate(Request $request)
    {
        $getAllFormTemplate = FormTemplate::where('type', $request->type)->select('id', 'title', 'body', 'type', 'updated_at')->withCount('submittedCount')->get();
        return response()->json([ 'success' => true, 'data' => $getAllFormTemplate , 'code' => 200 ]);
    }

    public function editFormTemplate(Request $request)
    {
        $editFormTemplate = FormTemplate::where('id', $request->id)->first();
        $eS = (new FormTemplateResource($editFormTemplate));
        return response()->json([ 'success' => true, 'data' => $eS , 'code' => 200 ]);
    }

    public function updateFormTemplate(Request $request)
    {
        $updateFormTemplate = FormTemplate::where('id', $request->id)->first();
        $old_data = $updateFormTemplate;
        if($updateFormTemplate){
            $updateFormTemplate->title = $request->title;
            $updateFormTemplate->type = $request->type;
            $updateFormTemplate->background = $request->background;
            $updateFormTemplate->logo = json_encode($request->logo);
            $updateFormTemplate->body = json_encode($request->body);
            $updateFormTemplate->save();
            jobRosterActions($request->admin_id, 'update_form_template', $updateFormTemplate->id, 'form_templates', $old_data);
            return response()->json(['success' => true, 'message' => 'Form Template Updated', 'code'=> 200]);
        }else{
            return response()->json(['success' => false, 'message' => 'Form Template Not Found!', 'code'=> 404]);
        }
    }

    public function insertFormData(Request $request)
    {
        $insertFormData = FormTemplate::where('id', $request->id)->first();
        if($insertFormData){
            $insertFormData->form_data = json_encode($request->form_data);
            $insertFormData->update();
            return response()->json(['success' => true, 'message' => 'Form Data Inserted', 'code'=> 200]);
        }else{
            return response()->json(['success' => false, 'message' => 'Form Not Found!', 'code'=> 404]);
        }
    }

    public function deleteFormTemplate(Request $request)
    {
        $formTemplate = FormTemplate::where('id', $request->id)->first();
        $old_data = $formTemplate;
        if($formTemplate){
            $formTemplate->delete();
            jobRosterActions($request->admin_id, 'delete_form_template', $formTemplate->id, 'form_templates', $old_data);
            return response()->json(['success' => true, 'message' => 'Form Template Deleted', 'code'=> 200]);
        }else{
            return response()->json(['success' => false, 'message' => 'Form Template Not Found!', 'code'=> 404]);
        }
    }


    public function formTemplateGuardFilter(Request $request)
    {
        $guards = Guard::where(function($query) use ($request){
            foreach ($request->sites as $key => $value) {
               $query->orWhereJsonContains('site_id', $value)->where('state', $request->state);
            }
         })->select('id','first_name', 'middle_name', 'last_name')->get();
         return response()->json(['success' => true, 'data' => $guards, 'code'=> 200]);
    }

    public function sendPageLink(Request $request)
    {
        $emails = Guard::whereIn('id', $request->guard_id)->select('id','email')->get();
        foreach ($emails as $key => $email) {
            $form_history = new DynamicFormHistory();
            $form_history->guard_id = $email->id;
            $form_history->form_id = $request->form_id;
            $form_history->link_click = 0;
            $form_history->save();
            formEmailLink($email, $request->form_id);
        }
        return response()->json(['success' => true, 'message' => 'Link Sent', 'code'=> 200]);
    }

    public function sendBuiltIn(Request $request){

        $emails = Guard::whereIn('id', $request->guard_id)->select('id','email')->get();
        

        $business_id = $request->header('Business-Id');
        foreach ($emails as $key => $email) {
            $form_history = new BuildInFormHistory();
            $form_history->guard_id = $email->id;
            $form_history->form_id = $request->form_id;
            $form_history->link_click = 0;
            $form_history->save();

            formEmailBuiltIn($email->email, $request->form_id, $email->id, $business_id);
        }
        return response()->json(['success' => true, 'message' => 'Link Sended', 'code'=> 200]);
    }
    public function builtInFormLinkClicked(Request $request){
        $formHistory = BuildInFormHistory::where(['guard_id'=>$request->guard_id, 'form_id'=>$request->form_id])->first();
        if($formHistory == null || $formHistory->is_data_saved == 1){
            return response()->json(['success' => false, 'message' => 'Already Submitted']);
        }
        $formHistory->link_click = 1;
        $formHistory->update();
        return response()->json(['success' => true, 'message' => 'Please fill form']);
    }
    public function submitDynamicForm(Request $request){
        $formHistory = DynamicFormHistory::where(['guard_id'=>$request->guard_id, 'form_id'=>$request->form_id])->first();
        if(isset($formHistory) && $formHistory->link_click == 1){
            return response()->json(['success' => false, 'message' => 'Already Submitted']);
        }
        $dynamicForm = new DynamicFormHistory();
        $dynamicForm->guard_id = $request->guard_id;
        $dynamicForm->form_id = $request->form_id;
        $dynamicForm->form_data = json_encode($request->form_data);
        $dynamicForm->link_click = 1;
        $dynamicForm->save();
        return response()->json(['success' => true, 'message' => 'Information is saved']);
    }
    public function historyBuiltIn(Request $request){
        $formHistory = BuildInFormHistory::where(['form_id'=> $request->form_id, 'link_click'=> 0])->with('GuardDetails')->get();
        $clickedHistory = BuildInFormHistory::where(['form_id'=> $request->form_id, 'link_click'=> 1])->with('GuardDetails')->get();
        return response()->json([
            'success' => true,
            'history' => $formHistory,
            'clickedForm' => $clickedHistory
        ]);
    }
    public function historyDynamicForm($form_id){
        $getFormHistory = DynamicFormHistory::where('form_id', $form_id)->get();
        $formDetails = FormTemplate::where('id', $form_id)->first();
        $getFormHistory->transform(function ($history) {
            $history->form_data = json_decode($history->form_data);
            return $history;
        });
        return response()->json([
            'success' => true,
            'history' => $getFormHistory,
            'details' => $formDetails
        ]);
    }
}


