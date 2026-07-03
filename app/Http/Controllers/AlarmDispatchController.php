<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AlarmDispatch;

class AlarmDispatchController extends Controller
{
   
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function storeAlarmDispatch(Request $request)
    {
        $store_alarm = new AlarmDispatch;

        $store_alarm->name = $request->name;
        $store_alarm->location_id = $request->location_id;
        $store_alarm->car_id = $request->car_id;
        $store_alarm->file = $request->file;

        $store_alarm->save();

        return response()->json(['message' => "Alarm Added Successfully!" ,  'code' => 200, 'success' => true]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function updateAlarmDispatch(Request $request)
    {
        
        $update_alarm = AlarmDispatch::findOrFail($request->id);

        if($update_alarm){

        $update_alarm->name = $request->name;
        $update_alarm->location_id = $request->location_id;
        $update_alarm->car_id = $request->car_id;
        $update_alarm->file = $request->file;
               
        $update_alarm->update();

        return response()->json(['message' => "Alarm Updated Successfully!" ,  'code' => 200, 'success' => true]);
        }
        else
        {
        return response()->json(['msg' =>  'Record Not Found!',  'code' => 404, 'success' => false]);
        }
        
    }

    public function getAllAlarmDispatch()
    {
        $getAll = AlarmDispatch::all();
        return response()->json(['data' => $getAll ,  'code' => 200, 'success' => true]);
    }

    
    public function editAlarmDispatch(Request $request)
    {
        $edit_alarm = AlarmDispatch::where('id', $request->id)->first();
        if($edit_alarm){
            return response()->json(['data' =>  $edit_alarm,  'code' => 200, 'success' => true]);
        }else{
            return response()->json(['data' =>  '',  'code' => 404, 'success' => false]);
        }
    }

    public function deleteAlarmDispatch(Request $request)
    {
        $delete_alarm = AlarmDispatch::where('id', $request->id)->first();
        if($delete_alarm){
            $delete_alarm->delete(); 
            return response()->json(['msg' =>  'Record Deleted Successfully!',  'code' => 200, 'success' => true]);
        }else{
            return response()->json(['msg' =>  'Record Not Found!',  'code' => 404, 'success' => false]);
        }
    }

   
}
