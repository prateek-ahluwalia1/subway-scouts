<?php

namespace App\Http\Controllers;

use App\Http\Resources\EditBusinessSettingResource;
use App\Http\Resources\GetAllBusinessSettingResource;
use App\Http\Resources\GetBusinessPermissionResource;
use App\Http\Resources\GetBusinessSettingResource;
use App\Models\BusinessConfig;
use App\Models\BussinessSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BusinessSettingController extends Controller
{
    public function store(Request $request)
    {
        $storeBusiness = new BussinessSetting();
        $storeBusiness->title = $request->business_name;
        $storeBusiness->business_type = $request->business_status;
        $storeBusiness->address = $request->address;
        $storeBusiness->email = $request->email;
        $storeBusiness->domain = $request->domain;
        $storeBusiness->guard = $request->business_type;
        $storeBusiness->app_id = $request->app_id;
        $storeBusiness->server_key = $request->server_key;
        $storeBusiness->about_company = $request->about_company;
        $storeBusiness->hide = 0;
        $storeBusiness->database_name = $request->database_name;
        if($request->has('temp_logo')){
            $storeBusiness->logo = str_replace(url('')."/"."business_setting/","",$request->temp_logo);
        }
        if($request->has('temp_about_file')){
            $storeBusiness->about_company_file = str_replace(url('')."/"."business_setting/","",$request->temp_about_file);
        }
        $storeBusiness->save();
       jobRosterActions($request->admin_id, 'add_new_business', $storeBusiness->id, 'business_data');
//     DB::connection('mysql2')->table('business_data')->insert(
//         array(
//              'title' => $request->business_name
//         )
//    );
       return response()->json(['message' => "Business Added Successfully!" ,  'code' => 200, 'success' => true]);
    }

    public function getAll()
    {
        $storeBusiness = BussinessSetting::where('hide', 1)->get();
        $sb = GetAllBusinessSettingResource::collection($storeBusiness);
        return response()->json(['data' => $sb ,  'code' => 200, 'success' => true]);
    }


    public function edit(Request $request)
    {
        $storeBusiness = BussinessSetting::where('id', $request->id)->first();
        if($storeBusiness){
            $sb = new EditBusinessSettingResource($storeBusiness);
            return response()->json(['data' =>  $sb,  'code' => 200, 'success' => true]);
        }else{
            return response()->json(['data' =>  '',  'code' => 404, 'success' => false]);
        }
    }

    public function update(Request $request)
    {
        $storeBusiness = BussinessSetting::where('id', $request->id)->first();
        if($storeBusiness){
            $storeBusiness->title = $request->business_name;
            $storeBusiness->business_type = $request->business_status;
            $storeBusiness->address = $request->address;
            $storeBusiness->email = $request->email;
            $storeBusiness->domain = $request->domain;
            $storeBusiness->guard = $request->business_type;
            $storeBusiness->app_id = $request->app_id;
            $storeBusiness->server_key = $request->server_key;
            $storeBusiness->about_company = $request->about_company;
            $storeBusiness->database_name = $request->database_name;
            if($request->has('temp_logo')){
                $storeBusiness->logo = str_replace(url('')."/"."business_setting/","",$request->temp_logo);
             }
             if($request->has('temp_about_file')){
                $storeBusiness->about_company_file = str_replace(url('')."/"."business_setting/","",$request->temp_about_file);
            }
            $storeBusiness->save();
            jobRosterActions($request->admin_id, 'update_new_business', $storeBusiness->id, 'business_data');
            return response()->json(['msg' =>  'Record Updated Successfully!',  'code' => 200, 'success' => true]);
        }else{
            return response()->json(['msg' =>  'Record Not Found!',  'code' => 404, 'success' => false]);
        }
    }

    public function delete(Request $request)
    {
        $storeBusiness = BussinessSetting::where('id', $request->id)->first();
        if($storeBusiness){
            $storeBusiness->delete(); 
            return response()->json(['msg' =>  'Record Deleted Successfully!',  'code' => 200, 'success' => true]);
        }else{
            return response()->json(['msg' =>  'Record Not Found!',  'code' => 404, 'success' => false]);
        }
    }

    public function addAndUpdateBusinessPermissions(Request $request)
    {
        $businessPermission = BusinessConfig::where('type', $request->type)->where('business_data_id', $request->business_data_id)->first();
        if(empty($businessPermission)){
            $businessPermission =  new BusinessConfig();
            $businessPermission->business_data_id = $request->business_data_id;
            $businessPermission->type = $request->type;
            $businessPermission->records_business_navbar = json_encode($request->records_business_navbar);
            $businessPermission->save();
            jobRosterActions($request->admin_id, 'add_business_config', $businessPermission->id, 'bussiness_config');
            return response()->json(['msg' =>  'Record Added Successfully!',  'code' => 200, 'success' => true]);
        }else{
        $businessPermission->business_data_id = $request->business_data_id;
        $businessPermission->type = $request->type;
        $businessPermission->records_business_navbar = json_encode($request->records_business_navbar);
        $businessPermission->save();
        jobRosterActions($request->admin_id, 'update_business_config', $businessPermission->id, 'bussiness_config');
        return response()->json(['msg' =>  'Record Updated Successfully!',  'code' => 200, 'success' => true]);
        }
    }

    public function getAddAndUpdateBusinessPermissions(Request $request)
    {
        $record = BusinessConfig::where('type', $request->type)->where('business_data_id', $request->business_data_id)->first();
        if($record){
            $rd = new GetBusinessPermissionResource($record);
            return response()->json(['data' =>  $rd,  'code' => 200, 'success' => true]);
        }else{
            return response()->json(['data' =>  '',  'code' => 404, 'success' => false]);
        }
        
    }


    public function getBusinessSettings(Request $request)
    {
        $getBusinessSettings = BussinessSetting::where('title', 'like', '%'.$request->title.'%')->with('businessConfig')->first();
        if($getBusinessSettings){
            $gbs = new GetBusinessSettingResource($getBusinessSettings);
            return response()->json(['data' =>  $gbs,  'code' => 200, 'success' => true]);  
        }else{
            return response()->json(['data' =>  '',  'code' => 404, 'success' => false]);
        }
        
    }
}
