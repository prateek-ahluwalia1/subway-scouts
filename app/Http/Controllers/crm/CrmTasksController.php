<?php

namespace App\Http\Controllers\crm;

use App\Http\Controllers\Controller;
use App\Http\Resources\EditCrmTasksResource;
use App\Http\Resources\GetAllCrmTasksResource;
use App\Models\CrmTasks;
use Illuminate\Http\Request;

class CrmTasksController extends Controller
{


    public function getAllCrmTasks(Request $request){
        if($request->type == 'saleperson'){
            $tasks = CrmTasks::where('agent_id', $request->admin_id)->orWhere('created_by', $request->admin_id)->with('lead')->get();
        }else{
            $tasks = CrmTasks::with('lead')->get();
        }
        $tsk = GetAllCrmTasksResource::collection($tasks);
        return response()->json(['success' => true, 'data' => $tsk]);
    }    

    public  function addAndUpdateCrmTask(Request $request)  {

        $is_check = false;
        $crmTasks = CrmTasks::where('id', $request->id)->first();
        if(!$crmTasks){
            $crmTasks = new CrmTasks();
            $is_check = true;
        }
        $crmTasks->task_owner = $request->task_owner;
        $crmTasks->subject = $request->subject;
        $crmTasks->due_date = $request->due_date;
        $crmTasks->contact = json_encode($request->contact);
        $crmTasks->status = $request->status;
        $crmTasks->agent_id = $request->agent_id;
        $crmTasks->created_by = $request->created_by;
        $crmTasks->priority = $request->priority;
        $crmTasks->selectedType = $request->selectedType;
        $crmTasks->reminder = ($request->reminder == 'on' ? true : false);
        $crmTasks->repeat = ($request->repeat == 'on' ? true : false);
        $crmTasks->description = $request->description;
        $crmTasks->save();
        if($is_check == true){
            return response()->json(['success' => true, 'msg' => 'Task Added']);
        }else{
            return response()->json(['success' => true, 'msg' => 'Task Updated']); 
        }
    }

  public function editCrmTasks(Request $request)  {
    $crmTasks = CrmTasks::where('id', $request->id)->with('lead')->first();
    if($crmTasks){
        $tsk = new EditCrmTasksResource($crmTasks);
        return response()->json(['success' => true, 'data' => $tsk]);
    }else{
        return response()->json(['success' => false, 'error' => 'Task not found!']);
    }
  }

   public function deleteCrmTasks(Request $request){
    $crmTasks = CrmTasks::where('id', $request->id)->first();
    if($crmTasks){
        $crmTasks->delete();
        return response()->json(['success' => true, 'msg' => 'Task Deleted']);
    }else{
        return response()->json(['success' => false, 'error' => 'Task not found!']);
    }
  }
}
