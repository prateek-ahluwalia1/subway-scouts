<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Car;

class CarController extends Controller
{
   
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function storeCar(Request $request)
    {
        $store_car = new Car;

        $store_car->patrol_site_id = $request->patrol_site_id;
        $store_car->name = $request->name;
        $store_car->car_model = $request->car_model;
        $store_car->rego_num = $request->rego_num;
        $store_car->rego_exp_date = $request->rego_exp_date;
        $store_car->insurance = $request->insurance;
        $store_car->insurance_exp_date = $request->insurance_exp_date;
        $store_car->maintenance = $request->maintenance;
        $store_car->odometer = $request->odometer;
        $store_car->kilometer = $request->kilometer;

        $store_car->save();

        return response()->json(['message' => "Car Added Successfully!" ,  'code' => 200, 'success' => true]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function updateCar(Request $request)
    {
        
        $update_car = Car::findOrFail($request->id);

        if($update_car){

        $update_car->name = $request->name;
        $update_car->patrol_site_id = $request->patrol_site_id;
        $update_car->car_model = $request->car_model;
        $update_car->rego_num = $request->rego_num;
        $update_car->rego_exp_date = $request->rego_exp_date;
        $update_car->insurance = $request->insurance;
        $update_car->insurance_exp_date = $request->insurance_exp_date;
        $update_car->maintenance = $request->maintenance;
        $update_car->odometer = $request->odometer;
        $update_car->kilometer = $request->kilometer;
        
        $update_car->update();

        return response()->json(['message' => "Car Updated Successfully!" ,  'code' => 200, 'success' => true]);
        }
        else
        {
        return response()->json(['msg' =>  'Record Not Found!',  'code' => 404, 'success' => false]);
        }
        
    }

    public function getAllCars()
    {
        $getAll = Car::all();
        return response()->json(['data' => $getAll ,  'code' => 200, 'success' => true]);
    }

    
    public function editCar(Request $request)
    {
        $edit_car = Car::where('id', $request->id)->first();
        if($edit_car){
            return response()->json(['data' =>  $edit_car,  'code' => 200, 'success' => true]);
        }else{
            return response()->json(['data' =>  '',  'code' => 404, 'success' => false]);
        }
    }

    public function deleteCar(Request $request)
    {
        $delete_car = Car::where('id', $request->id)->first();
        if($delete_car){
            $delete_car->delete(); 
            return response()->json(['msg' =>  'Record Deleted Successfully!',  'code' => 200, 'success' => true]);
        }else{
            return response()->json(['msg' =>  'Record Not Found!',  'code' => 404, 'success' => false]);
        }
    }

   
}
