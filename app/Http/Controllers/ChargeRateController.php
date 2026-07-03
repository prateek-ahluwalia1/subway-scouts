<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePayrateRequest;
use App\Http\Resources\ChargeRateResource;
use App\Http\Resources\getSpecificChargeRateWithLevelResource;
use App\Models\ChargeRate;
use App\Models\Customer;
use App\Models\Site;
use Carbon\Carbon;
use Illuminate\Http\Request;
use DB;
class ChargeRateController extends Controller
{
    public function store(StorePayrateRequest $request)
    {
        $charge_rate = ChargeRate::where('customer_id', $request->customer_id)->where('level', $request->level)->where('title', $request->title)->where('position', $request->position)->first();
        if($charge_rate){
            return response()->json(['message' => "Hi,this charge rate already exist!" ,  'code' => 404, 'success' => false]);
        }else{
        $charge_rate = new ChargeRate();
        $charge_rate->title = $request->title;
        $charge_rate->customer_id = $request->customer_id;
        $charge_rate->position = $request->position;
        $charge_rate->level = $request->level;
        $charge_rate->state = $request->state;
        $charge_rate->def_metro_mon_to_fri_day_rate = ($request->def_metro_mon_to_fri_day_rate ? $request->def_metro_mon_to_fri_day_rate : 0);
        $charge_rate->def_metro_mon_to_fri_night_rate = ($request->def_metro_mon_to_fri_night_rate ? $request->def_metro_mon_to_fri_night_rate: 0);
        $charge_rate->def_metro_sat_day_rate = ($request->def_metro_sat_day_rate ? $request->def_metro_sat_day_rate : 0);
        $charge_rate->def_metro_sat_night_rate = ($request->def_metro_sat_night_rate ? $request->def_metro_sat_night_rate : 0);
        $charge_rate->def_metro_sun_day_rate = ($request->def_metro_sun_day_rate ? $request->def_metro_sun_day_rate : 0);
        $charge_rate->def_metro_sun_night_rate = ($request->def_metro_sun_night_rate ? $request->def_metro_sun_night_rate : 0);
        $charge_rate->def_metro_pub_holi_day_rate = ($request->def_metro_pub_holi_day_rate ? $request->def_metro_pub_holi_day_rate : 0);
        $charge_rate->def_metro_pub_holi_night_rate = ($request->def_metro_pub_holi_night_rate ? $request->def_metro_pub_holi_night_rate : 0);
        $charge_rate->def_reg_mon_to_fri_day_rate = ($request->def_reg_mon_to_fri_day_rate ? $request->def_reg_mon_to_fri_day_rate : 0);
        $charge_rate->def_reg_mon_to_fri_night_rate = ($request->def_reg_mon_to_fri_night_rate ? $request->def_reg_mon_to_fri_night_rate : 0);
        $charge_rate->def_reg_sat_day_rate = ($request->def_reg_sat_day_rate ? $request->def_reg_sat_day_rate : 0);
        $charge_rate->def_reg_sat_night_rate = ($request->def_reg_sat_night_rate ? $request->def_reg_sat_night_rate : 0);
        $charge_rate->def_reg_sun_day_rate = ($request->def_reg_sun_day_rate ? $request->def_reg_sun_day_rate : 0);
        $charge_rate->def_reg_sun_night_rate = ($request->def_reg_sun_night_rate ? $request->def_reg_sun_night_rate : 0);
        $charge_rate->def_reg_pub_holi_day_rate = ($request->def_reg_pub_holi_day_rate ? $request->def_reg_pub_holi_day_rate : 0);
        $charge_rate->def_reg_pub_holi_night_rate = ($request->def_reg_pub_holi_night_rate ? $request->def_reg_pub_holi_night_rate : 0);
        $charge_rate->eba_metro_mon_to_fri_day_rate = ($request->eba_metro_mon_to_fri_day_rate ? $request->eba_metro_mon_to_fri_day_rate : 0);
        $charge_rate->eba_metro_mon_to_fri_night_rate = ($request->eba_metro_mon_to_fri_night_rate ? $request->eba_metro_mon_to_fri_night_rate: 0);
        $charge_rate->eba_metro_sat_day_rate = ($request->eba_metro_sat_day_rate ? $request->eba_metro_sat_day_rate : 0);
        $charge_rate->eba_metro_sat_night_rate = ($request->eba_metro_sat_night_rate ? $request->eba_metro_sat_night_rate : 0);
        $charge_rate->eba_metro_sun_day_rate = ($request->eba_metro_sun_day_rate ? $request->eba_metro_sun_day_rate : 0);
        $charge_rate->eba_metro_sun_night_rate = ($request->eba_metro_sun_night_rate ? $request->eba_metro_sun_night_rate : 0);
        $charge_rate->eba_metro_pub_holi_day_rate = ($request->eba_metro_pub_holi_day_rate ? $request->eba_metro_pub_holi_day_rate : 0);
        $charge_rate->eba_metro_pub_holi_night_rate = ($request->eba_metro_pub_holi_night_rate ? $request->eba_metro_pub_holi_night_rate : 0);
        $charge_rate->eba_reg_mon_to_fri_day_rate = ($request->eba_reg_mon_to_fri_day_rate ? $request->eba_reg_mon_to_fri_day_rate : 0);
        $charge_rate->eba_reg_mon_to_fri_night_rate = ($request->eba_reg_mon_to_fri_night_rate ? $request->eba_reg_mon_to_fri_night_rate : 0);
        $charge_rate->eba_reg_sat_day_rate = ($request->eba_reg_sat_day_rate ? $request->eba_reg_sat_day_rate : 0);
        $charge_rate->eba_reg_sat_night_rate = ($request->eba_reg_sat_night_rate ? $request->eba_reg_sat_night_rate : 0);
        $charge_rate->eba_reg_sun_day_rate = ($request->eba_reg_sun_day_rate ? $request->eba_reg_sun_day_rate : 0);
        $charge_rate->eba_reg_sun_night_rate = ($request->eba_reg_sun_night_rate ? $request->eba_reg_sun_night_rate : 0);
        $charge_rate->eba_reg_pub_holi_day_rate = ($request->eba_reg_pub_holi_day_rate ? $request->eba_reg_pub_holi_day_rate : 0);
        $charge_rate->eba_reg_pub_holi_night_rate = ($request->eba_reg_pub_holi_night_rate ? $request->eba_reg_pub_holi_night_rate : 0);
        $charge_rate->award_metro_mon_to_fri_day_rate = ($request->award_metro_mon_to_fri_day_rate ? $request->award_metro_mon_to_fri_day_rate : 0);
        $charge_rate->award_metro_mon_to_fri_night_rate = ($request->award_metro_mon_to_fri_night_rate ? $request->award_metro_mon_to_fri_night_rate : 0);
        $charge_rate->award_metro_sat_day_rate = ($request->award_metro_sat_day_rate ? $request->award_metro_sat_day_rate : 0);
        $charge_rate->award_metro_sat_night_rate = ($request->award_metro_sat_night_rate ? $request->award_metro_sat_night_rate : 0);
        $charge_rate->award_metro_sun_day_rate = ($request->award_metro_sun_day_rate ? $request->award_metro_sun_day_rate : 0);
        $charge_rate->award_metro_sun_night_rate = ($request->award_metro_sun_night_rate ? $request->award_metro_sun_night_rate : 0);
        $charge_rate->award_metro_pub_holi_day_rate = ($request->award_metro_pub_holi_day_rate ? $request->award_metro_pub_holi_day_rate : 0);
        $charge_rate->award_metro_pub_holi_night_rate = ($request->award_metro_pub_holi_night_rate ? $request->award_metro_pub_holi_night_rate : 0);
        $charge_rate->award_reg_mon_to_fri_day_rate = ($request->award_reg_mon_to_fri_day_rate ? $request->award_reg_mon_to_fri_day_rate : 0);
        $charge_rate->award_reg_mon_to_fri_night_rate = ($request->award_reg_mon_to_fri_night_rate ? $request->award_reg_mon_to_fri_night_rate : 0);
        $charge_rate->award_reg_sat_day_rate = ($request->award_reg_sat_day_rate ? $request->award_reg_sat_day_rate : 0);
        $charge_rate->award_reg_sat_night_rate = ($request->award_reg_sat_night_rate ? $request->award_reg_sat_night_rate : 0);
        $charge_rate->award_reg_sun_day_rate = ($request->award_reg_sun_day_rate ? $request->award_reg_sun_day_rate : 0);
        $charge_rate->award_reg_sun_night_rate = ($request->award_reg_sun_night_rate ? $request->award_reg_sun_night_rate : 0);
        $charge_rate->award_reg_pub_holi_day_rate = ($request->award_reg_pub_holi_day_rate ? $request->award_reg_pub_holi_day_rate : 0);
        $charge_rate->award_reg_pub_holi_night_rate = ($request->award_reg_pub_holi_night_rate ? $request->award_reg_pub_holi_night_rate : 0);
        $charge_rate->ot_base_rate = ($request->ot_base_rate ? $request->ot_base_rate : 0);
        $charge_rate->save();
        // logging('add', $charge_rate->id, 'login_user', '');
        jobRosterActions($request->admin_id, 'add_chargerate', $charge_rate->id, 'chargerates');
        return response()->json(['message' => "Charge rate added" ,  'code' => 200, 'success' => true]);
        } 
    }

    public function storeChargeRateOfNextLevel(Request $request)
    {
        $charge_rate = ChargeRate::where('customer_id', $request->customer_id)->where('level', $request->level)->first();
        if ($charge_rate) {
            $charge_rate = ChargeRate::where('customer_id', $request->customer_id)->where('level', $request->level)->get();
            $chrt = ChargeRateResource::collection($charge_rate);
            return response()->json(['success' => true, 'data' => $chrt]);
        }else{ 
        $charge_rate = new ChargeRate();
        $charge_rate->title = $request->title;
        $charge_rate->customer_id = $request->customer_id;
        $charge_rate->position = $request->position;
        $charge_rate->level = $request->level;
        $charge_rate->state = $request->state;
        $charge_rate->def_metro_mon_to_fri_day_rate = ($request->def_metro_mon_to_fri_day_rate ? $request->def_metro_mon_to_fri_day_rate : 0);
        $charge_rate->def_metro_mon_to_fri_night_rate = ($request->def_metro_mon_to_fri_night_rate ? $request->def_metro_mon_to_fri_night_rate: 0);
        $charge_rate->def_metro_sat_day_rate = ($request->def_metro_sat_day_rate ? $request->def_metro_sat_day_rate : 0);
        $charge_rate->def_metro_sat_night_rate = ($request->def_metro_sat_night_rate ? $request->def_metro_sat_night_rate : 0);
        $charge_rate->def_metro_sun_day_rate = ($request->def_metro_sun_day_rate ? $request->def_metro_sun_day_rate : 0);
        $charge_rate->def_metro_sun_night_rate = ($request->def_metro_sun_night_rate ? $request->def_metro_sun_night_rate : 0);
        $charge_rate->def_metro_pub_holi_day_rate = ($request->def_metro_pub_holi_day_rate ? $request->def_metro_pub_holi_day_rate : 0);
        $charge_rate->def_metro_pub_holi_night_rate = ($request->def_metro_pub_holi_night_rate ? $request->def_metro_pub_holi_night_rate : 0);
        $charge_rate->def_reg_mon_to_fri_day_rate = ($request->def_reg_mon_to_fri_day_rate ? $request->def_reg_mon_to_fri_day_rate : 0);
        $charge_rate->def_reg_mon_to_fri_night_rate = ($request->def_reg_mon_to_fri_night_rate ? $request->def_reg_mon_to_fri_night_rate : 0);
        $charge_rate->def_reg_sat_day_rate = ($request->def_reg_sat_day_rate ? $request->def_reg_sat_day_rate : 0);
        $charge_rate->def_reg_sat_night_rate = ($request->def_reg_sat_night_rate ? $request->def_reg_sat_night_rate : 0);
        $charge_rate->def_reg_sun_day_rate = ($request->def_reg_sun_day_rate ? $request->def_reg_sun_day_rate : 0);
        $charge_rate->def_reg_sun_night_rate = ($request->def_reg_sun_night_rate ? $request->def_reg_sun_night_rate : 0);
        $charge_rate->def_reg_pub_holi_day_rate = ($request->def_reg_pub_holi_day_rate ? $request->def_reg_pub_holi_day_rate : 0);
        $charge_rate->def_reg_pub_holi_night_rate = ($request->def_reg_pub_holi_night_rate ? $request->def_reg_pub_holi_night_rate : 0);
        $charge_rate->eba_metro_mon_to_fri_day_rate = ($request->eba_metro_mon_to_fri_day_rate ? $request->eba_metro_mon_to_fri_day_rate : 0);
        $charge_rate->eba_metro_mon_to_fri_night_rate = ($request->eba_metro_mon_to_fri_night_rate ? $request->eba_metro_mon_to_fri_night_rate: 0);
        $charge_rate->eba_metro_sat_day_rate = ($request->eba_metro_sat_day_rate ? $request->eba_metro_sat_day_rate : 0);
        $charge_rate->eba_metro_sat_night_rate = ($request->eba_metro_sat_night_rate ? $request->eba_metro_sat_night_rate : 0);
        $charge_rate->eba_metro_sun_day_rate = ($request->eba_metro_sun_day_rate ? $request->eba_metro_sun_day_rate : 0);
        $charge_rate->eba_metro_sun_night_rate = ($request->eba_metro_sun_night_rate ? $request->eba_metro_sun_night_rate : 0);
        $charge_rate->eba_metro_pub_holi_day_rate = ($request->eba_metro_pub_holi_day_rate ? $request->eba_metro_pub_holi_day_rate : 0);
        $charge_rate->eba_metro_pub_holi_night_rate = ($request->eba_metro_pub_holi_night_rate ? $request->eba_metro_pub_holi_night_rate : 0);
        $charge_rate->eba_reg_mon_to_fri_day_rate = ($request->eba_reg_mon_to_fri_day_rate ? $request->eba_reg_mon_to_fri_day_rate : 0);
        $charge_rate->eba_reg_mon_to_fri_night_rate = ($request->eba_reg_mon_to_fri_night_rate ? $request->eba_reg_mon_to_fri_night_rate : 0);
        $charge_rate->eba_reg_sat_day_rate = ($request->eba_reg_sat_day_rate ? $request->eba_reg_sat_day_rate : 0);
        $charge_rate->eba_reg_sat_night_rate = ($request->eba_reg_sat_night_rate ? $request->eba_reg_sat_night_rate : 0);
        $charge_rate->eba_reg_sun_day_rate = ($request->eba_reg_sun_day_rate ? $request->eba_reg_sun_day_rate : 0);
        $charge_rate->eba_reg_sun_night_rate = ($request->eba_reg_sun_night_rate ? $request->eba_reg_sun_night_rate : 0);
        $charge_rate->eba_reg_pub_holi_day_rate = ($request->eba_reg_pub_holi_day_rate ? $request->eba_reg_pub_holi_day_rate : 0);
        $charge_rate->eba_reg_pub_holi_night_rate = ($request->eba_reg_pub_holi_night_rate ? $request->eba_reg_pub_holi_night_rate : 0);
        $charge_rate->ot_base_rate = ($request->ot_base_rate ? $request->ot_base_rate : 0);
        $charge_rate->save();
        // logging('add next level charge rate', $charge_rate->id, 'login_user', '');
        jobRosterActions($request->admin_id, 'add_next_level_chargerate', $charge_rate->id, 'chargerates');
        return response()->json(['message' => "Charge Rate Next Level Add Successfully" ,  'code' => 200, 'success' => true]);
     }   
        
    }

    // public function getAllChargeRate()
    // {
    //     $charge_rate = ChargeRate::where('status', 'active')->orderBy('title', 'asc')->get();
    //     $chrt = ChargeRateResource::collection($charge_rate);
    //     return response()->json(['success' => true, 'data' => $chrt]);  
    // }

    public function getAllChargeRate()
    {
        $charge_rate = ChargeRate::where('status', 'active')
            ->orderBy('title', 'asc')
            //->groupBy(\DB::raw('TRIM(title)')) // Group by the title after removing leading and trailing spaces
            ->get();

        $chrt = ChargeRateResource::collection($charge_rate);
        return response()->json(['success' => true, 'data' => $chrt]);  
    }

    public function getSpecificChargeRateWithLevel(Request $request)
        {
            $charge_rate = ChargeRate::where('status', 'active')->where('title' , $request->title)
            ->where('level', $request->level)
            ->first();
            if($charge_rate){
            $chrt = new getSpecificChargeRateWithLevelResource($charge_rate);
            return response()->json(['success' => true, 'data' => $chrt]);
            }else{
                return response()->json(['success' => false, 'error' => 'No charge rate found!']);
            }  
        }

     public function getAllArchiveChargeRate()
    {
        $charge_rate = ChargeRate::where('status', 'archive')->orderBy('title', 'asc')->get();
        $chrt = ChargeRateResource::collection($charge_rate);
        return response()->json(['success' => true, 'data' => $chrt]);  
    }

    public function removeChargeRate(Request $request)
    {
        $charge_rate = ChargeRate::where('id', $request->chargerate_id)->first();
        if($charge_rate){
            $charge_rate->status = 'archive';
            $charge_rate->save();
            //logging('charge_rate_archive', $charge_rate->id, 'login_user', '');
            jobRosterActions($request->admin_id, 'archive_chargerate', $charge_rate->id, 'chargerates');
            return response()->json(['message' => "Charge Rate removed" ,  'code' => 200, 'success' => true],200); 
        }else{
            return response()->json(['message' => "Charge Rate not found!" ,  'code' => 404, 'success' => false],200); 
        }
    }

    public function update(StorePayrateRequest $request)
    {
        $is_check =0;
        $charge_rate = ChargeRate::where('customer_id', $request->customer_id)->where('level', $request->level)->first();
        if(!empty($charge_rate)){
        $charge_rate = ChargeRate::where('customer_id', $request->customer_id)->where('level', $request->level)->update(['status' => 'archive']);
        }
        
        $charge_rate = new ChargeRate();
        $is_check =1;
        $charge_rate->title = $request->title;
        $charge_rate->customer_id = $request->customer_id;
        $charge_rate->position = $request->position;
        $charge_rate->level = $request->level;
        $charge_rate->state = $request->state;
        $charge_rate->def_metro_mon_to_fri_day_rate = ($request->def_metro_mon_to_fri_day_rate ? $request->def_metro_mon_to_fri_day_rate : 0);
        $charge_rate->def_metro_mon_to_fri_night_rate = ($request->def_metro_mon_to_fri_night_rate ? $request->def_metro_mon_to_fri_night_rate: 0);
        $charge_rate->def_metro_sat_day_rate = ($request->def_metro_sat_day_rate ? $request->def_metro_sat_day_rate : 0);
        $charge_rate->def_metro_sat_night_rate = ($request->def_metro_sat_night_rate ? $request->def_metro_sat_night_rate : 0);
        $charge_rate->def_metro_sun_day_rate = ($request->def_metro_sun_day_rate ? $request->def_metro_sun_day_rate : 0);
        $charge_rate->def_metro_sun_night_rate = ($request->def_metro_sun_night_rate ? $request->def_metro_sun_night_rate : 0);
        $charge_rate->def_metro_pub_holi_day_rate = ($request->def_metro_pub_holi_day_rate ? $request->def_metro_pub_holi_day_rate : 0);
        $charge_rate->def_metro_pub_holi_night_rate = ($request->def_metro_pub_holi_night_rate ? $request->def_metro_pub_holi_night_rate : 0);
        $charge_rate->def_reg_mon_to_fri_day_rate = ($request->def_reg_mon_to_fri_day_rate ? $request->def_reg_mon_to_fri_day_rate : 0);
        $charge_rate->def_reg_mon_to_fri_night_rate = ($request->def_reg_mon_to_fri_night_rate ? $request->def_reg_mon_to_fri_night_rate : 0);
        $charge_rate->def_reg_sat_day_rate = ($request->def_reg_sat_day_rate ? $request->def_reg_sat_day_rate : 0);
        $charge_rate->def_reg_sat_night_rate = ($request->def_reg_sat_night_rate ? $request->def_reg_sat_night_rate : 0);
        $charge_rate->def_reg_sun_day_rate = ($request->def_reg_sun_day_rate ? $request->def_reg_sun_day_rate : 0);
        $charge_rate->def_reg_sun_night_rate = ($request->def_reg_sun_night_rate ? $request->def_reg_sun_night_rate : 0);
        $charge_rate->def_reg_pub_holi_day_rate = ($request->def_reg_pub_holi_day_rate ? $request->def_reg_pub_holi_day_rate : 0);
        $charge_rate->def_reg_pub_holi_night_rate = ($request->def_reg_pub_holi_night_rate ? $request->def_reg_pub_holi_night_rate : 0);
        $charge_rate->eba_metro_mon_to_fri_day_rate = ($request->eba_metro_mon_to_fri_day_rate ? $request->eba_metro_mon_to_fri_day_rate : 0);
        $charge_rate->eba_metro_mon_to_fri_night_rate = ($request->eba_metro_mon_to_fri_night_rate ? $request->eba_metro_mon_to_fri_night_rate: 0);
        $charge_rate->eba_metro_sat_day_rate = ($request->eba_metro_sat_day_rate ? $request->eba_metro_sat_day_rate : 0);
        $charge_rate->eba_metro_sat_night_rate = ($request->eba_metro_sat_night_rate ? $request->eba_metro_sat_night_rate : 0);
        $charge_rate->eba_metro_sun_day_rate = ($request->eba_metro_sun_day_rate ? $request->eba_metro_sun_day_rate : 0);
        $charge_rate->eba_metro_sun_night_rate = ($request->eba_metro_sun_night_rate ? $request->eba_metro_sun_night_rate : 0);
        $charge_rate->eba_metro_pub_holi_day_rate = ($request->eba_metro_pub_holi_day_rate ? $request->eba_metro_pub_holi_day_rate : 0);
        $charge_rate->eba_metro_pub_holi_night_rate = ($request->eba_metro_pub_holi_night_rate ? $request->eba_metro_pub_holi_night_rate : 0);
        $charge_rate->eba_reg_mon_to_fri_day_rate = ($request->eba_reg_mon_to_fri_day_rate ? $request->eba_reg_mon_to_fri_day_rate : 0);
        $charge_rate->eba_reg_mon_to_fri_night_rate = ($request->eba_reg_mon_to_fri_night_rate ? $request->eba_reg_mon_to_fri_night_rate : 0);
        $charge_rate->eba_reg_sat_day_rate = ($request->eba_reg_sat_day_rate ? $request->eba_reg_sat_day_rate : 0);
        $charge_rate->eba_reg_sat_night_rate = ($request->eba_reg_sat_night_rate ? $request->eba_reg_sat_night_rate : 0);
        $charge_rate->eba_reg_sun_day_rate = ($request->eba_reg_sun_day_rate ? $request->eba_reg_sun_day_rate : 0);
        $charge_rate->eba_reg_sun_night_rate = ($request->eba_reg_sun_night_rate ? $request->eba_reg_sun_night_rate : 0);
        $charge_rate->eba_reg_pub_holi_day_rate = ($request->eba_reg_pub_holi_day_rate ? $request->eba_reg_pub_holi_day_rate : 0);
        $charge_rate->eba_reg_pub_holi_night_rate = ($request->eba_reg_pub_holi_night_rate ? $request->eba_reg_pub_holi_night_rate : 0);
        $charge_rate->ot_base_rate = ($request->ot_base_rate ? $request->ot_base_rate : 0);
        $charge_rate->save();
        //logging('add next level charge rate', $charge_rate->id, 'login_user', '');
        if($is_check == 1){
        jobRosterActions($request->admin_id, 'add_chargerate', $charge_rate->id, 'chargerates');
        return response()->json(['message' => "Charge rate added" ,  'code' => 200, 'success' => true]);
        }else{
            jobRosterActions($request->admin_id, 'update_chargerate', $charge_rate->id, 'chargerates');
            return response()->json(['message' => "Charge rate updated" ,  'code' => 200, 'success' => true]);
        }
    }
    public function history($site_id){
        $getCRHistory = DB::table('site_chargerate_history')
        ->join('users', 'site_chargerate_history.changed_by', '=', 'users.id')
        ->where('site_chargerate_history.site_id', $site_id)
        ->select('site_chargerate_history.*', 'users.name AS changed_by_name')
        ->get();
        return response()->json([
            'success' => true,
            'history' => $getCRHistory
        ]);
    }

    // New Charge Rate CRUD
    public function charged_rates(Request $request)
    {
      $charged_rates = ChargeRate::all();

      
      return response()->json([
        'status' => true, 
        'charged_rates' => $charged_rates,
    ]);
    
    }

    public function create_charged_rate( Request $request){
        $charged_rate=[
          'title'=>$request->title,
          'effective_date'=>$request->effective_date,
          'state'=>$request->charged_rates_state,
          'level'=>$request->level,
          'state'=>$request->state,
          'customer_id'=>$request->customer_id,
          'position'=>$request->position,
          'flat_metro_flat_metro_week_day' => $request->flat_metro_week_day,
          'flat_metro_weekend' => $request->flat_metro_weekend,
          'flat_metro_public_holiday' => $request->flat_metro_public_holiday,
          'flat_regional_week_day' => $request->flat_regional_week_day,
          'flat_regional_weekend' => $request->flat_regional_weekend,
          'flat_regional_public_holiday' => $request->flat_regional_public_holiday,
          'eba_metro_weekday_day' => $request->eba_metro_weekday_day,
          'eba_metro_weekday_afternoon' => $request->eba_metro_weekday_afternoon,
          'eba_metro_weekday_night' => $request->eba_metro_weekday_night,
          'eba_metro_weekend' => $request->eba_metro_weekend,
          'eba_metro_public_holiday' => $request->eba_metro_public_holiday,
          'eba_regional_weekday_day' => $request->eba_regional_weekday_day,
          'eba_regional_weekday_afternoon' => $request->eba_regional_weekday_afternoon,
          'eba_regional_weekday_night' => $request->eba_regional_weekday_night,
          'eba_regional_weekend' => $request->eba_regional_weekend,
          'eba_regional_weekend_sun' => $request->eba_regional_weekend_sun,
          'eba_metro_weekend_sun' => $request->eba_metro_weekend_sun,
          'eba_regional_public_holiday' => $request->eba_regional_public_holiday,
          'flat_metro_week_day_day' => $request->flat_metro_week_day_day,
          'flat_regional_week_day_day' => $request->flat_regional_week_day_day,
          'flat_metro_week_day_night' => $request->flat_metro_week_day_night,
          'flat_regional_week_day_night' => $request->flat_regional_week_day_night,
          'flat_metro_friday' => $request->flat_metro_friday,
          'flat_regional_friday' => $request->flat_regional_friday,
          'flat_metro_saturday' => $request->flat_metro_saturday,
          'flat_regional_saturday' => $request->flat_regional_saturday,
          'flat_metro_sunday' => $request->flat_metro_sunday,
          'flat_regional_sunday' => $request->flat_regional_sunday,
          'flat_regional_sunday_night' => $request->flat_regional_sunday_night,
          'flat_metro_sunday_night' => $request->flat_metro_sunday_night,
          'flat_metro_saturday_night' => $request->flat_metro_saturday_night,
          'flat_regional_saturday_night' => $request->flat_regional_saturday_night,
          'flat_metro_public_holiday_night' => $request->flat_metro_public_holiday_night,
          'flat_regional_public_holiday_night' => $request->flat_regional_public_holiday_night,
          'eba_metro_saturday_day' => $request->eba_metro_saturday_day,
          'eba_regional_saturday_day' => $request->eba_regional_saturday_day,
          'eba_metro_saturday_night' => $request->eba_metro_saturday_night,
          'eba_regional_saturday_night' => $request->eba_regional_saturday_night,
          'eba_metro_sunday_day' => $request->eba_metro_sunday_day,
          'eba_regional_sunday_day' => $request->eba_regional_sunday_day,
          'eba_metro_sunday_night' => $request->eba_metro_sunday_night,
          'eba_regional_sunday_night' => $request->eba_regional_sunday_night,
          'eba_metro_public_holiday_night' => $request->eba_metro_public_holiday_night,
          'eba_regional_public_holiday_night' => $request->eba_regional_public_holiday_night,
    
                        // 'normal_rate' => $request->normal_rate,
                        // 'eba_rate' => $request->eba_rate,
        ];
        $normal_divions = array();
        $eba_divions = array();
        for ($i=0; $i < $request->division_count; $i++) { 
          $normal_divions[$i]['id'] = $request->input('division_normal_id_'.$i);
          $normal_divions[$i]['rate'] = $request->input('division_normal_rate_'.$i);
          $eba_divions[$i]['id'] = $request->input('division_eba_id_'.$i);
          $eba_divions[$i]['rate'] = $request->input('division_eba_rate_'.$i);
        }
        $charged_rate['normal_rate'] = json_encode($normal_divions);
        $charged_rate['eba_rate'] = json_encode($eba_divions);
              // print_r('<pre>');
              // print_r($charged_rate);
              // exit();
        $res= ChargeRate::insert($charged_rate);
        if($res){
          return response()->json([
            'success' => true,
            'message' => 'Charge rate added Successfully.'
        ]);
        
    
        }else{
          return response()->json(array('success' => false));
    
        }
    
    
    
 }
    
      public function get_charged_rates($id,Request $request)
      {
        if ($id != 0) {
          $charged_rate = ChargeRate::where(['id' => $id])->first();
        }else{
    
          $query = ChargeRate::where(['level' => $request->level, 'state' => $request->state, 'position' => $request->position]);
          if ($request->has('name') && $request->name != '') {
            $query->where('title', $request->name);
          }
          $charged_rate = $query->first();
        }
        if (!empty($charged_rate)) {
          $charged_rate->normal_rate = json_decode($charged_rate->normal_rate, true);
          $charged_rate->eba_rate = json_decode($charged_rate->eba_rate, true);
          $charged_rate->effective_date = date('Y-m-d', strtotime($charged_rate->effective_date));
          return response()->json(['success' => true, 'message' => "charged_rates retrieve", 'charged_rates' => $charged_rate]);
        }else{
          return response()->json(['success' => false, 'message' => "charged_rates fail"]);
        }
      }
    
    
      public function update_charged_rates($id,Request $request)
      {
        $charged_rate_id=$id;
        $charged_rate=[
          'title'=>$request->title,
          'effective_date'=>$request->effective_date,
          'state'=>$request->state,
          'customer_id'=>$request->customer_id,
          'position'=>$request->position,
          'level'=>$request->level,
          'flat_metro_flat_metro_week_day' => $request->flat_metro_week_day,
          'flat_metro_weekend' => $request->flat_metro_weekend,
          'flat_metro_public_holiday' => $request->flat_metro_public_holiday,
          'flat_regional_week_day' => $request->flat_regional_week_day,
          'flat_regional_weekend' => $request->flat_regional_weekend,
          'flat_regional_public_holiday' => $request->flat_regional_public_holiday,
          'eba_metro_weekday_day' => $request->eba_metro_weekday_day,
          'eba_metro_weekday_afternoon' => $request->eba_metro_weekday_afternoon,
          'eba_metro_weekday_night' => $request->eba_metro_weekday_night,
          'eba_metro_weekend' => $request->eba_metro_weekend,
          'eba_metro_public_holiday' => $request->eba_metro_public_holiday,
          'eba_regional_weekday_day' => $request->eba_regional_weekday_day,
          'eba_regional_weekday_afternoon' => $request->eba_regional_weekday_afternoon,
          'eba_regional_weekday_night' => $request->eba_regional_weekday_night,
          'eba_regional_weekend' => $request->eba_regional_weekend,
          'eba_regional_public_holiday' => $request->eba_regional_public_holiday,
          'eba_regional_weekend_sun' => $request->eba_regional_weekend_sun,
          'eba_metro_weekend_sun' => $request->eba_metro_weekend_sun,
    
          'flat_metro_week_day_day' => $request->flat_metro_week_day_day,
          'flat_regional_week_day_day' => $request->flat_regional_week_day_day,
          'flat_metro_week_day_night' => $request->flat_metro_week_day_night,
          'flat_regional_week_day_night' => $request->flat_regional_week_day_night,
          'flat_metro_friday' => $request->flat_metro_friday,
          'flat_regional_friday' => $request->flat_regional_friday,
          'flat_metro_saturday' => $request->flat_metro_saturday,
          'flat_regional_saturday' => $request->flat_regional_saturday,
          'flat_metro_sunday' => $request->flat_metro_sunday,
          'flat_regional_sunday' => $request->flat_regional_sunday,
    
          'flat_regional_sunday_night' => $request->flat_regional_sunday_night,
          'flat_metro_sunday_night' => $request->flat_metro_sunday_night,
          'flat_metro_saturday_night' => $request->flat_metro_saturday_night,
          'flat_regional_saturday_night' => $request->flat_regional_saturday_night,
          'flat_metro_public_holiday_night' => $request->flat_metro_public_holiday_night,
          'flat_regional_public_holiday_night' => $request->flat_regional_public_holiday_night,
    
          'eba_metro_saturday_day' => $request->eba_metro_saturday_day,
          'eba_regional_saturday_day' => $request->eba_regional_saturday_day,
          'eba_metro_saturday_night' => $request->eba_metro_saturday_night,
          'eba_regional_saturday_night' => $request->eba_regional_saturday_night,
          'eba_metro_sunday_day' => $request->eba_metro_sunday_day,
          'eba_regional_sunday_day' => $request->eba_regional_sunday_day,
          'eba_metro_sunday_night' => $request->eba_metro_sunday_night,
          'eba_regional_sunday_night' => $request->eba_regional_sunday_night,
          'eba_metro_public_holiday_night' => $request->eba_metro_public_holiday_night,
          'eba_regional_public_holiday_night' => $request->eba_regional_public_holiday_night,
                        // 'normal_rate' => $request->normal_rate,
                        // 'eba_rate' => $request->eba_rate,
        ];
        $normal_divions = array();
        $eba_divions = array();
        for ($i=0; $i < $request->division_count; $i++) { 
          $normal_divions[$i]['id'] = $request->input('division_normal_id_'.$i);
          $normal_divions[$i]['rate'] = $request->input('division_normal_rate_'.$i);
          $eba_divions[$i]['id'] = $request->input('division_eba_id_'.$i);
          $eba_divions[$i]['rate'] = $request->input('division_eba_rate_'.$i);
        }
        $charged_rate['normal_rate'] = json_encode($normal_divions);
        $charged_rate['eba_rate'] = json_encode($eba_divions);
        $charge_rate = ChargeRate::where(['level' => $request->level, 'state'=>$request->state, 'title' => $request->title])->first();
        if (!empty($charge_rate)) {
          $res= ChargeRate::where('id',$charged_rate_id)->update($charged_rate);
          if ($res) {
            $this->logChargeRate($charge_rate, $charged_rate_id, $request->effective_date, $request->admin_id);
          }
        }else{
          $res= ChargeRate::insert($charged_rate);
        }
        if($res){
          return response()->json([
            'success' => true,
            'message' => 'Charge rate updated Successfully.'
        ]);
        
    
        }else{
          return response()->json(array('success' => false));
        }
      }
    
      function logChargeRate($charge_rate, $charged_rate_id, $effective_date_to, $admin_id)
      {
        DB::table('charge_rate_histories')->insert([
          'chargerate_id' => $charged_rate_id,
          'data' => json_encode($charge_rate),
          'changed_by' =>$admin_id,
          'effective_from' => $charge_rate->effective_date,
          'effective_to' => $effective_date_to,
        ]);
      }
      public function delete_charged_rate($id){
        $res= ChargeRate::where('id',$id)->delete();
        if($res){
          return response()->json([
            'success' => true,
            'message' => 'Charge rate deleted Successfully.'
        ]);
        
    
        }else{
          return response()->json(array('success' => false));
    
        } 
    
      }
    
    
      function get_charged_rates_history(Request $request)
      {
        $charge_rate = ChargeRate::where('title', $request->title)->get();
        return response()->json(array('success' => true, 'charge_rate' => $charge_rate));
      }
    
    
}
