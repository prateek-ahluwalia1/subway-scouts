<?php

namespace App\Http\Controllers;

use App\Http\Resources\CrmCustomerTimeLineResource;
use App\Http\Resources\EditSecondStaffUniFormResource;
use App\Http\Resources\EditStaffUniFormResource;
use App\Models\JobRosterAction;
use App\Models\StaffUniform;
use Illuminate\Http\Request;

class StaffUniFormController extends Controller
{
    public function saveStaffUniformDetials(Request $request)
    {
        if(isset($request->uniformDetail)){
            foreach($request->uniformDetail as $uniform){
                $guardUniform = new StaffUniform;
                $guardUniform->guard_id = $request->guard_id;
                $guardUniform->note = $uniform['note'];
                $guardUniform->return_status = $uniform['return_status'] == true ? 1 : 0;
                $guardUniform->customer_id = $uniform['customer_id'];
                $guardUniform->uniform_type = $uniform['uniform_type'];
                $guardUniform->quantity = $uniform['quantity'];
                $guardUniform->size = $uniform['size'];
                $guardUniform->save();
                jobRosterActions($request->admin_id, 'add_uniform', $request->guard_id, 'staff_uniforms');
            }
            return response()->json(['message' => "Staff Uniform Save Successfully" ,  'code' => 200, 'success' => true],200);
        }
    }
    public function updateStaffUniformDetials(Request $request){
        $guardUniform = StaffUniform::find($request->id);
        $guardUniform->note = $request->note;
        $guardUniform->return_status = $request->return_status == true ? 1 : 0;
        $guardUniform->customer_id = $request->customer_id;
        $guardUniform->uniform_type = $request->uniform_type;
        $guardUniform->quantity = $request->quantity;
        $guardUniform->size = $request->size;
        $dirtyAttributes = $guardUniform->getDirty();
        $guardUniform->update();
        jobRosterActions($request->admin_id, 'update_uniform', $request->guard_id, 'staff_uniforms', '', $dirtyAttributes);
        return response()->json(['message' => "Staff Uniform Updated Successfully" ,  'code' => 200, 'success' => true],200);
    }
    function staffUniFormActivity(Request $request) {
        $query = JobRosterAction::where('roster_id', $request->guard_id)->where('action_on', 'staff_uniforms')->get();
        $acts = CrmCustomerTimeLineResource::collection($query);
        return response()->json(['success' => true, 'data' => $acts]);
    }
    


    public function getStaffUniformDetials(Request $request)
    {
        $guardUniform = StaffUniform::where('guard_id', $request->id)->get();
        
        if($guardUniform){
            $guf =  EditStaffUniFormResource::collection($guardUniform);
            return response()->json(['data' => $guf ,  'code' => 200, 'success' => true]); 
        }else{
            return response()->json(['data' => '' ,  'code' => 404, 'success' => false]); 
        }
    }

    public function editStaffUniformDetials(Request $request){

        $guardUniform = StaffUniform::where('id', $request->id)->first();
        if($guardUniform){
            $guf = new EditSecondStaffUniFormResource($guardUniform);
            return response()->json(['data' => $guf ,  'code' => 200, 'success' => true]); 
        }else{
            return response()->json(['data' => '' ,  'code' => 404, 'success' => false]); 
        }
    }

}
