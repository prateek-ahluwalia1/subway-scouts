<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePayrateRequest;
use App\Http\Resources\AllPayRateResource;
use App\Models\Payrate;
use App\Models\PayRatesNew;
use Illuminate\Http\Request;
use DB;
use Carbon\Carbon;


class PayRateController extends Controller
{
    public function store(StorePayrateRequest $request)
    {
        $payrate = Payrate::where('customer_id', $request->customer_id)->where('level', $request->level)->where('title', $request->title)->where('position', $request->position)->first();
        if($payrate){
            return response()->json(['message' => "Hi,this Payrates already exist!" ,  'code' => 404, 'success' => false]);
        }else{
            $payrates = new Payrate();
            $payrates->title = $request->title;
            $payrates->customer_id = $request->customer_id;
            $payrates->position = $request->position;
            $payrates->level = $request->level;
            $payrates->state = $request->state;
            $payrates->def_metro_mon_to_fri_day_rate = ($request->def_metro_mon_to_fri_day_rate ? $request->def_metro_mon_to_fri_day_rate : 0);
            $payrates->def_metro_mon_to_fri_night_rate = ($request->def_metro_mon_to_fri_night_rate ? $request->def_metro_mon_to_fri_night_rate : 0);
            $payrates->def_metro_sat_day_rate = ($request->def_metro_sat_day_rate ? $request->def_metro_sat_day_rate : 0);
            $payrates->def_metro_sat_night_rate = ($request->def_metro_sat_night_rate ? $request->def_metro_sat_night_rate : 0);
            $payrates->def_metro_sun_day_rate = ($request->def_metro_sun_day_rate ? $request->def_metro_sun_day_rate : 0);
            $payrates->def_metro_sun_night_rate = ($request->def_metro_sun_night_rate ? $request->def_metro_sun_night_rate : 0);
            $payrates->def_metro_pub_holi_day_rate = ($request->def_metro_pub_holi_day_rate ? $request->def_metro_pub_holi_day_rate : 0);
            $payrates->def_metro_pub_holi_night_rate = ($request->def_metro_pub_holi_night_rate ? $request->def_metro_pub_holi_night_rate : 0);
            $payrates->def_reg_mon_to_fri_day_rate = ($request->def_reg_mon_to_fri_day_rate ? $request->def_reg_mon_to_fri_day_rate : 0);
            $payrates->def_reg_mon_to_fri_night_rate = ($request->def_reg_mon_to_fri_night_rate ? $request->def_reg_mon_to_fri_night_rate : 0);
            $payrates->def_reg_sat_day_rate = ($request->def_reg_sat_day_rate ? $request->def_reg_sat_day_rate : 0);
            $payrates->def_reg_sat_night_rate = ($request->def_reg_sat_night_rate ? $request->def_reg_sat_night_rate : 0);
            $payrates->def_reg_sun_day_rate = ($request->def_reg_sun_day_rate ? $request->def_reg_sun_day_rate : 0);
            $payrates->def_reg_sun_night_rate = ($request->def_reg_sun_night_rate ? $request->def_reg_sun_night_rate : 0);
            $payrates->def_reg_pub_holi_day_rate = ($request->def_reg_pub_holi_day_rate ? $request->def_reg_pub_holi_day_rate : 0);
            $payrates->def_reg_pub_holi_night_rate = ($request->def_reg_pub_holi_night_rate ? $request->def_reg_pub_holi_night_rate : 0);
            $payrates->eba_metro_mon_to_fri_day_rate = ($request->eba_metro_mon_to_fri_day_rate ? $request->eba_metro_mon_to_fri_day_rate : 0);
            $payrates->eba_metro_mon_to_fri_night_rate = ($request->eba_metro_mon_to_fri_night_rate ? $request->eba_metro_mon_to_fri_night_rate : 0);
            $payrates->eba_metro_sat_day_rate = ($request->eba_metro_sat_day_rate ? $request->eba_metro_sat_day_rate : 0);
            $payrates->eba_metro_sat_night_rate = ($request->eba_metro_sat_night_rate ? $request->eba_metro_sat_night_rate : 0);
            $payrates->eba_metro_sun_day_rate = ($request->eba_metro_sun_day_rate ? $request->eba_metro_sun_day_rate : 0);
            $payrates->eba_metro_sun_night_rate = ($request->eba_metro_sun_night_rate ? $request->eba_metro_sun_night_rate : 0);
            $payrates->eba_metro_pub_holi_day_rate = ($request->eba_metro_pub_holi_day_rate ? $request->eba_metro_pub_holi_day_rate : 0);
            $payrates->eba_metro_pub_holi_night_rate = ($request->eba_metro_pub_holi_night_rate ? $request->eba_metro_pub_holi_night_rate : 0);
            $payrates->eba_reg_mon_to_fri_day_rate = ($request->eba_reg_mon_to_fri_day_rate ? $request->eba_reg_mon_to_fri_day_rate : 0);
            $payrates->eba_reg_mon_to_fri_night_rate = ($request->eba_reg_mon_to_fri_night_rate ? $request->eba_reg_mon_to_fri_night_rate : 0);
            $payrates->eba_reg_sat_day_rate = ($request->eba_reg_sat_day_rate ? $request->eba_reg_sat_day_rate : 0);
            $payrates->eba_reg_sat_night_rate = ($request->eba_reg_sat_night_rate ? $request->eba_reg_sat_night_rate : 0);
            $payrates->eba_reg_sun_day_rate = ($request->eba_reg_sun_day_rate ? $request->eba_reg_sun_day_rate : 0);
            $payrates->eba_reg_sun_night_rate = ($request->eba_reg_sun_night_rate ? $request->eba_reg_sun_night_rate : 0);
            $payrates->eba_reg_pub_holi_day_rate = ($request->eba_reg_pub_holi_day_rate ? $request->eba_reg_pub_holi_day_rate : 0);
            $payrates->eba_reg_pub_holi_night_rate = ($request->eba_reg_pub_holi_night_rate ? $request->eba_reg_pub_holi_night_rate : 0);

            $payrates->award_metro_mon_to_fri_day_rate = ($request->award_metro_mon_to_fri_day_rate ? $request->award_metro_mon_to_fri_day_rate : 0);
            $payrates->award_metro_mon_to_fri_night_rate = ($request->award_metro_mon_to_fri_night_rate ? $request->award_metro_mon_to_fri_night_rate : 0);
            $payrates->award_metro_sat_day_rate = ($request->award_metro_sat_day_rate ? $request->award_metro_sat_day_rate : 0);
            $payrates->award_metro_sat_night_rate = ($request->award_metro_sat_night_rate ? $request->award_metro_sat_night_rate : 0);
            $payrates->award_metro_sun_day_rate = ($request->award_metro_sun_day_rate ? $request->award_metro_sun_day_rate : 0);
            $payrates->award_metro_sun_night_rate = ($request->award_metro_sun_night_rate ? $request->award_metro_sun_night_rate : 0);
            $payrates->award_metro_pub_holi_day_rate = ($request->award_metro_pub_holi_day_rate ? $request->award_metro_pub_holi_day_rate : 0);
            $payrates->award_metro_pub_holi_night_rate = ($request->award_metro_pub_holi_night_rate ? $request->award_metro_pub_holi_night_rate : 0);
            $payrates->award_reg_mon_to_fri_day_rate = ($request->award_reg_mon_to_fri_day_rate ? $request->award_reg_mon_to_fri_day_rate : 0);
            $payrates->award_reg_mon_to_fri_night_rate = ($request->award_reg_mon_to_fri_night_rate ? $request->award_reg_mon_to_fri_night_rate : 0);
            $payrates->award_reg_sat_day_rate = ($request->award_reg_sat_day_rate ? $request->award_reg_sat_day_rate : 0);
            $payrates->award_reg_sat_night_rate = ($request->award_reg_sat_night_rate ? $request->award_reg_sat_night_rate : 0);
            $payrates->award_reg_sun_day_rate = ($request->award_reg_sun_day_rate ? $request->award_reg_sun_day_rate : 0);
            $payrates->award_reg_sun_night_rate = ($request->award_reg_sun_night_rate ? $request->award_reg_sun_night_rate : 0);
            $payrates->award_reg_pub_holi_day_rate = ($request->award_reg_pub_holi_day_rate ? $request->award_reg_pub_holi_day_rate : 0);
            $payrates->award_reg_pub_holi_night_rate = ($request->award_reg_pub_holi_night_rate ? $request->award_reg_pub_holi_night_rate : 0);

            $payrates->ot_base_rate = ($request->ot_base_rate ? $request->ot_base_rate : 0);
            $payrates->effective_from = $request->effective_from;
            $payrates->save();
            jobRosterActions($request->admin_id, 'add_payrates', $payrates->id, 'payrates');
            return response()->json(['message' => "Payrates Added", 'code' => 200, 'success' => true]);
        }
    }


    public function storePayRateOfNextLevel(Request $request)
    {
        $payrate = Payrate::where('customer_id', $request->customer_id)->where('level', $request->level)->first();
        if($payrate) {
            $payrates = Payrate::where('customer_id', $request->customer_id)->where('level', $request->level)->get();
            $chrt = AllPayRateResource::collection($payrates);
            return response()->json(['success' => true, 'data' => $chrt]);
        }else{ 
            $payrates = new Payrate();
            $payrates->title = $request->title;
            $payrates->customer_id = $request->customer_id;
            $payrates->position = $request->position;
            $payrates->level = $request->level;
            $payrates->state = $request->state;
            $payrates->def_metro_mon_to_fri_day_rate = ($request->def_metro_mon_to_fri_day_rate ? $request->def_metro_mon_to_fri_day_rate : 0);
            $payrates->def_metro_mon_to_fri_night_rate = ($request->def_metro_mon_to_fri_night_rate ? $request->def_metro_mon_to_fri_night_rate: 0);
            $payrates->def_metro_sat_day_rate = ($request->def_metro_sat_day_rate ? $request->def_metro_sat_day_rate : 0);
            $payrates->def_metro_sat_night_rate = ($request->def_metro_sat_night_rate ? $request->def_metro_sat_night_rate : 0);
            $payrates->def_metro_sun_day_rate = ($request->def_metro_sun_day_rate ? $request->def_metro_sun_day_rate : 0);
            $payrates->def_metro_sun_night_rate = ($request->def_metro_sun_night_rate ? $request->def_metro_sun_night_rate : 0);
            $payrates->def_metro_pub_holi_day_rate = ($request->def_metro_pub_holi_day_rate ? $request->def_metro_pub_holi_day_rate : 0);
            $payrates->def_metro_pub_holi_night_rate = ($request->def_metro_pub_holi_night_rate ? $request->def_metro_pub_holi_night_rate : 0);
            $payrates->def_reg_mon_to_fri_day_rate = ($request->def_reg_mon_to_fri_day_rate ? $request->def_reg_mon_to_fri_day_rate : 0);
            $payrates->def_reg_mon_to_fri_night_rate = ($request->def_reg_mon_to_fri_night_rate ? $request->def_reg_mon_to_fri_night_rate : 0);
            $payrates->def_reg_sat_day_rate = ($request->def_reg_sat_day_rate ? $request->def_reg_sat_day_rate : 0);
            $payrates->def_reg_sat_night_rate = ($request->def_reg_sat_night_rate ? $request->def_reg_sat_night_rate : 0);
            $payrates->def_reg_sun_day_rate = ($request->def_reg_sun_day_rate ? $request->def_reg_sun_day_rate : 0);
            $payrates->def_reg_sun_night_rate = ($request->def_reg_sun_night_rate ? $request->def_reg_sun_night_rate : 0);
            $payrates->def_reg_pub_holi_day_rate = ($request->def_reg_pub_holi_day_rate ? $request->def_reg_pub_holi_day_rate : 0);
            $payrates->def_reg_pub_holi_night_rate = ($request->def_reg_pub_holi_night_rate ? $request->def_reg_pub_holi_night_rate : 0);
            $payrates->eba_metro_mon_to_fri_day_rate = ($request->eba_metro_mon_to_fri_day_rate ? $request->eba_metro_mon_to_fri_day_rate : 0);
            $payrates->eba_metro_mon_to_fri_night_rate = ($request->eba_metro_mon_to_fri_night_rate ? $request->eba_metro_mon_to_fri_night_rate: 0);
            $payrates->eba_metro_sat_day_rate = ($request->eba_metro_sat_day_rate ? $request->eba_metro_sat_day_rate : 0);
            $payrates->eba_metro_sat_night_rate = ($request->eba_metro_sat_night_rate ? $request->eba_metro_sat_night_rate : 0);
            $payrates->eba_metro_sun_day_rate = ($request->eba_metro_sun_day_rate ? $request->eba_metro_sun_day_rate : 0);
            $payrates->eba_metro_sun_night_rate = ($request->eba_metro_sun_night_rate ? $request->eba_metro_sun_night_rate : 0);
            $payrates->eba_metro_pub_holi_day_rate = ($request->eba_metro_pub_holi_day_rate ? $request->eba_metro_pub_holi_day_rate : 0);
            $payrates->eba_metro_pub_holi_night_rate = ($request->eba_metro_pub_holi_night_rate ? $request->eba_metro_pub_holi_night_rate : 0);
            $payrates->eba_reg_mon_to_fri_day_rate = ($request->eba_reg_mon_to_fri_day_rate ? $request->eba_reg_mon_to_fri_day_rate : 0);
            $payrates->eba_reg_mon_to_fri_night_rate = ($request->eba_reg_mon_to_fri_night_rate ? $request->eba_reg_mon_to_fri_night_rate : 0);
            $payrates->eba_reg_sat_day_rate = ($request->eba_reg_sat_day_rate ? $request->eba_reg_sat_day_rate : 0);
            $payrates->eba_reg_sat_night_rate = ($request->eba_reg_sat_night_rate ? $request->eba_reg_sat_night_rate : 0);
            $payrates->eba_reg_sun_day_rate = ($request->eba_reg_sun_day_rate ? $request->eba_reg_sun_day_rate : 0);
            $payrates->eba_reg_sun_night_rate = ($request->eba_reg_sun_night_rate ? $request->eba_reg_sun_night_rate : 0);
            $payrates->eba_reg_pub_holi_day_rate = ($request->eba_reg_pub_holi_day_rate ? $request->eba_reg_pub_holi_day_rate : 0);
            $payrates->eba_reg_pub_holi_night_rate = ($request->eba_reg_pub_holi_night_rate ? $request->eba_reg_pub_holi_night_rate : 0);
            $payrates->award_metro_mon_to_fri_day_rate = ($request->award_metro_mon_to_fri_day_rate ? $request->award_metro_mon_to_fri_day_rate : 0);
            $payrates->award_metro_mon_to_fri_night_rate = ($request->award_metro_mon_to_fri_night_rate ? $request->award_metro_mon_to_fri_night_rate : 0);
            $payrates->award_metro_sat_day_rate = ($request->award_metro_sat_day_rate ? $request->award_metro_sat_day_rate : 0);
            $payrates->award_metro_sat_night_rate = ($request->award_metro_sat_night_rate ? $request->award_metro_sat_night_rate : 0);
            $payrates->award_metro_sun_day_rate = ($request->award_metro_sun_day_rate ? $request->award_metro_sun_day_rate : 0);
            $payrates->award_metro_sun_night_rate = ($request->award_metro_sun_night_rate ? $request->award_metro_sun_night_rate : 0);
            $payrates->award_metro_pub_holi_day_rate = ($request->award_metro_pub_holi_day_rate ? $request->award_metro_pub_holi_day_rate : 0);
            $payrates->award_metro_pub_holi_night_rate = ($request->award_metro_pub_holi_night_rate ? $request->award_metro_pub_holi_night_rate : 0);
            $payrates->award_reg_mon_to_fri_day_rate = ($request->award_reg_mon_to_fri_day_rate ? $request->award_reg_mon_to_fri_day_rate : 0);
            $payrates->award_reg_mon_to_fri_night_rate = ($request->award_reg_mon_to_fri_night_rate ? $request->award_reg_mon_to_fri_night_rate : 0);
            $payrates->award_reg_sat_day_rate = ($request->award_reg_sat_day_rate ? $request->award_reg_sat_day_rate : 0);
            $payrates->award_reg_sat_night_rate = ($request->award_reg_sat_night_rate ? $request->award_reg_sat_night_rate : 0);
            $payrates->award_reg_sun_day_rate = ($request->award_reg_sun_day_rate ? $request->award_reg_sun_day_rate : 0);
            $payrates->award_reg_sun_night_rate = ($request->award_reg_sun_night_rate ? $request->award_reg_sun_night_rate : 0);
            $payrates->award_reg_pub_holi_day_rate = ($request->award_reg_pub_holi_day_rate ? $request->award_reg_pub_holi_day_rate : 0);
            $payrates->award_reg_pub_holi_night_rate = ($request->award_reg_pub_holi_night_rate ? $request->award_reg_pub_holi_night_rate : 0);
            $payrates->ot_base_rate = ($request->ot_base_rate ? $request->ot_base_rate : 0);
            $payrates->effective_from = $request->effective_from;
            $payrates->save();
            //logging('add next level payrate', $payrates->id, 'login_user', '');
            jobRosterActions($request->admin_id, 'add_next_level_payrates', $payrates->id, 'payrates');
            return response()->json(['message' => "PayRate Next Level Add Successfully" ,  'code' => 200, 'success' => true]);
        }   
        
    }

    public function getAllPayrate()
    {
        $payrates = Payrate::where('status', 'active')->orderBy('title', 'asc')->get();
        $prt = AllPayRateResource::collection($payrates);
        return response()->json(['success' => true, 'data' => $prt]);  
    }

     public function getPayrate(Request $request)
    {
        $payrates = Payrate::where('status', 'active')->where('id', $request->id)->first();
        $prt = new AllPayRateResource($payrates);
        return response()->json(['success' => true, 'data' => $prt]);  
    }

    public function getAllArchivePayrate()
    {
        $payrates = Payrate::where('status', 'archive')->orderBy('title', 'asc')->get();
        $prt = AllPayRateResource::collection($payrates);
        return response()->json(['success' => true, 'data' => $prt]);  
    }

    public function removePayrate(Request $request)
    {
        $payrate = Payrate::where('id', $request->payrate_id)->first();
        $old_data = $payrate;
        if($payrate){
            $payrate->status = 'archive';
            $payrate->save();
            //logging('payrate_archive', $payrate->id, 'login_user', '');
            jobRosterActions($request->admin_id, 'archive_chargerate', $payrate->id, 'chargerates', $old_data);
            //payrateHistory($payrate->id, $old_data, '', '', $request->admin_id);
            return response()->json(['message' => "Payrate removed" ,  'code' => 200, 'success' => true],200); 
        }else{
            return response()->json(['message' => "Payrates not found!" ,  'code' => 404, 'success' => false],404); 
        }
    }


    public function getPayrateWithLevelAndState(Request $request)
    {
        if( $request->has('level') && $request->has('state')){
            $payrate = Payrate::where('level',$request->level)->where('state', $request->state)->where('status', 'active')->select('id','title')->get();
            return response()->json(['success' => true, 'data' => $payrate]);  
        }elseif($request->has('level')){
            $payrate = Payrate::where('level',$request->level)->where('status', 'active')->select('id','title')->get();
            return response()->json(['success' => true, 'data' => $payrate]);
        }else{
            return response()->json(['success' => false, 'message' => 'payrates not found!']);
        }
    }
    public function update(Request $request)
    {
        $is_check =0;
        // $payrate = Payrate::where('customer_id', $request->customer_id)->where('level', $request->level)->first();
        $payrate = Payrate::find($request->id);
        $old_data = $payrate;
        if(!empty($payrate)) {
            // Payrate::where('customer_id', $request->customer_id)->where('level', $request->level)->update(['status' => 'archive']);
            Payrate::where('id', $request->id)->update(['status' => 'archive']);
            jobRosterActions($request->admin_id, 'archive_payrates', $payrate->id, 'payrates');
            // $payrate->status = 'archive';
            // $payrate->save();
        }
        $payrates = new Payrate();
        $is_check =1;
        $payrates->title = $request->title;
        $payrates->customer_id = $request->customer_id;
        $payrates->position = $request->position;
        $payrates->level = $request->level;
        $payrates->state = $request->state;
        $payrates->def_metro_mon_to_fri_day_rate = ($request->def_metro_mon_to_fri_day_rate ? $request->def_metro_mon_to_fri_day_rate : 0);
        $payrates->def_metro_mon_to_fri_night_rate = ($request->def_metro_mon_to_fri_night_rate ? $request->def_metro_mon_to_fri_night_rate: 0);
        $payrates->def_metro_sat_day_rate = ($request->def_metro_sat_day_rate ? $request->def_metro_sat_day_rate : 0);
        $payrates->def_metro_sat_night_rate = ($request->def_metro_sat_night_rate ? $request->def_metro_sat_night_rate : 0);
        $payrates->def_metro_sun_day_rate = ($request->def_metro_sun_day_rate ? $request->def_metro_sun_day_rate : 0);
        $payrates->def_metro_sun_night_rate = ($request->def_metro_sun_night_rate ? $request->def_metro_sun_night_rate : 0);
        $payrates->def_metro_pub_holi_day_rate = ($request->def_metro_pub_holi_day_rate ? $request->def_metro_pub_holi_day_rate : 0);
        $payrates->def_metro_pub_holi_night_rate = ($request->def_metro_pub_holi_night_rate ? $request->def_metro_pub_holi_night_rate : 0);
        $payrates->def_reg_mon_to_fri_day_rate = ($request->def_reg_mon_to_fri_day_rate ? $request->def_reg_mon_to_fri_day_rate : 0);
        $payrates->def_reg_mon_to_fri_night_rate = ($request->def_reg_mon_to_fri_night_rate ? $request->def_reg_mon_to_fri_night_rate : 0);
        $payrates->def_reg_sat_day_rate = ($request->def_reg_sat_day_rate ? $request->def_reg_sat_day_rate : 0);
        $payrates->def_reg_sat_night_rate = ($request->def_reg_sat_night_rate ? $request->def_reg_sat_night_rate : 0);
        $payrates->def_reg_sun_day_rate = ($request->def_reg_sun_day_rate ? $request->def_reg_sun_day_rate : 0);
        $payrates->def_reg_sun_night_rate = ($request->def_reg_sun_night_rate ? $request->def_reg_sun_night_rate : 0);
        $payrates->def_reg_pub_holi_day_rate = ($request->def_reg_pub_holi_day_rate ? $request->def_reg_pub_holi_day_rate : 0);
        $payrates->def_reg_pub_holi_night_rate = ($request->def_reg_pub_holi_night_rate ? $request->def_reg_pub_holi_night_rate : 0);
        $payrates->eba_metro_mon_to_fri_day_rate = ($request->eba_metro_mon_to_fri_day_rate ? $request->eba_metro_mon_to_fri_day_rate : 0);
        $payrates->eba_metro_mon_to_fri_night_rate = ($request->eba_metro_mon_to_fri_night_rate ? $request->eba_metro_mon_to_fri_night_rate: 0);
        $payrates->eba_metro_sat_day_rate = ($request->eba_metro_sat_day_rate ? $request->eba_metro_sat_day_rate : 0);
        $payrates->eba_metro_sat_night_rate = ($request->eba_metro_sat_night_rate ? $request->eba_metro_sat_night_rate : 0);
        $payrates->eba_metro_sun_day_rate = ($request->eba_metro_sun_day_rate ? $request->eba_metro_sun_day_rate : 0);
        $payrates->eba_metro_sun_night_rate = ($request->eba_metro_sun_night_rate ? $request->eba_metro_sun_night_rate : 0);
        $payrates->eba_metro_pub_holi_day_rate = ($request->eba_metro_pub_holi_day_rate ? $request->eba_metro_pub_holi_day_rate : 0);
        $payrates->eba_metro_pub_holi_night_rate = ($request->eba_metro_pub_holi_night_rate ? $request->eba_metro_pub_holi_night_rate : 0);
        $payrates->eba_reg_mon_to_fri_day_rate = ($request->eba_reg_mon_to_fri_day_rate ? $request->eba_reg_mon_to_fri_day_rate : 0);
        $payrates->eba_reg_mon_to_fri_night_rate = ($request->eba_reg_mon_to_fri_night_rate ? $request->eba_reg_mon_to_fri_night_rate : 0);
        $payrates->eba_reg_sat_day_rate = ($request->eba_reg_sat_day_rate ? $request->eba_reg_sat_day_rate : 0);
        $payrates->eba_reg_sat_night_rate = ($request->eba_reg_sat_night_rate ? $request->eba_reg_sat_night_rate : 0);
        $payrates->eba_reg_sun_day_rate = ($request->eba_reg_sun_day_rate ? $request->eba_reg_sun_day_rate : 0);
        $payrates->eba_reg_sun_night_rate = ($request->eba_reg_sun_night_rate ? $request->eba_reg_sun_night_rate : 0);
        $payrates->eba_reg_pub_holi_day_rate = ($request->eba_reg_pub_holi_day_rate ? $request->eba_reg_pub_holi_day_rate : 0);
        $payrates->eba_reg_pub_holi_night_rate = ($request->eba_reg_pub_holi_night_rate ? $request->eba_reg_pub_holi_night_rate : 0);
        $payrates->award_metro_mon_to_fri_day_rate = ($request->award_metro_mon_to_fri_day_rate ? $request->award_metro_mon_to_fri_day_rate : 0);
            $payrates->award_metro_mon_to_fri_night_rate = ($request->award_metro_mon_to_fri_night_rate ? $request->award_metro_mon_to_fri_night_rate : 0);
            $payrates->award_metro_sat_day_rate = ($request->award_metro_sat_day_rate ? $request->award_metro_sat_day_rate : 0);
            $payrates->award_metro_sat_night_rate = ($request->award_metro_sat_night_rate ? $request->award_metro_sat_night_rate : 0);
            $payrates->award_metro_sun_day_rate = ($request->award_metro_sun_day_rate ? $request->award_metro_sun_day_rate : 0);
            $payrates->award_metro_sun_night_rate = ($request->award_metro_sun_night_rate ? $request->award_metro_sun_night_rate : 0);
            $payrates->award_metro_pub_holi_day_rate = ($request->award_metro_pub_holi_day_rate ? $request->award_metro_pub_holi_day_rate : 0);
            $payrates->award_metro_pub_holi_night_rate = ($request->award_metro_pub_holi_night_rate ? $request->award_metro_pub_holi_night_rate : 0);
            $payrates->award_reg_mon_to_fri_day_rate = ($request->award_reg_mon_to_fri_day_rate ? $request->award_reg_mon_to_fri_day_rate : 0);
            $payrates->award_reg_mon_to_fri_night_rate = ($request->award_reg_mon_to_fri_night_rate ? $request->award_reg_mon_to_fri_night_rate : 0);
            $payrates->award_reg_sat_day_rate = ($request->award_reg_sat_day_rate ? $request->award_reg_sat_day_rate : 0);
            $payrates->award_reg_sat_night_rate = ($request->award_reg_sat_night_rate ? $request->award_reg_sat_night_rate : 0);
            $payrates->award_reg_sun_day_rate = ($request->award_reg_sun_day_rate ? $request->award_reg_sun_day_rate : 0);
            $payrates->award_reg_sun_night_rate = ($request->award_reg_sun_night_rate ? $request->award_reg_sun_night_rate : 0);
            $payrates->award_reg_pub_holi_day_rate = ($request->award_reg_pub_holi_day_rate ? $request->award_reg_pub_holi_day_rate : 0);
            $payrates->award_reg_pub_holi_night_rate = ($request->award_reg_pub_holi_night_rate ? $request->award_reg_pub_holi_night_rate : 0);
            $payrates->ot_base_rate = ($request->ot_base_rate ? $request->ot_base_rate : 0);
            $payrates->effective_from = $request->effective_from;
            $payrates->save();
        //logging('add next level payrate', $payrates->id, 'login_user', '');
        if($is_check == 1){
            jobRosterActions($request->admin_id, 'add_payrates', $payrates->id, 'payrates');
            return response()->json(['message' => "Pay rate Updated" ,  'code' => 200, 'success' => true]);
            }else{
                jobRosterActions($request->admin_id, 'update_payrates', $payrates->id, 'payrates', $old_data);
                return response()->json(['message' => "Pay rate Updated" ,  'code' => 200, 'success' => true]);
            }
    }
    public function history($site_id){
        $getPRHistory = DB::table('site_payrate_history')
        ->join('users', 'site_payrate_history.changed_by', '=', 'users.id')
        ->where('site_payrate_history.site_id', $site_id)
        ->select('site_payrate_history.*', 'users.name AS changed_by_name')
        ->get();
        return response()->json([
            'success' => true,
            'history' => $getPRHistory
        ]);
    }

    // Payrates CRUD

    public function payrates(Request $request)
        {
        $payrates = PayRatesNew::groupBy('hours', 'level', 'weekend',)->where('award_rate', 0)->get();
        $awardrates = PayRatesNew::groupBy('hours', 'level', 'weekend',)->where('award_rate', 1)->get();
        foreach ($payrates as $key => $payrate) {
            $payrate->groupData = PayRatesNew::where('title', $payrate->title)->select('level')->get();
        }
        foreach ($awardrates as $key => $awardrate) {
            $awardrate->groupData = PayRatesNew::where('title', $awardrate->title)->select('level')->get();
        }
        return response()->json(array('success' => true, 'payrates' => $payrates,'awardrates' => $awardrates));

        }
// Create Payrate
    public function create_payrate( Request $request){
            if($request->has('payrate_id') && $request->payrate_id!=null ){
                return redirect('update_payrates/'.$request->payrate_id);
                    // $this->update_payrates($request->payrate_id,$request);
                    // exit();
            }else{
                $is_already = PayRatesNew::where(['level' => $request->level, 'hours' => $request->hours, 'title' => $request->title])->first();
                if (!empty($is_already)) {
                return response()->json(array('success' => false, 'message' => 'Payrate already exist!'));
                }
                $formattedDate = Carbon::createFromFormat('Y/m/d', $request->effective_date)->format('Y-m-d');

                $payrate=[
                'title' => $request->title,
                'effective_date'=>$formattedDate,
                'hours' => $request->hours,
                'level' => $request->level,
                'weekend' => ($request->has('weekend') && $request->weekend == 1) ? 1 : 0,
                'pf_day' => $request->pf_day,
                'casual_day' => $request->casual_day,
                'pf_night' => $request->pf_night,
                'casual_night' => $request->casual_night,
                'pf_sat' => $request->pf_sat,
                'casual_sat' => $request->casual_sat,
                'pf_sun' => $request->pf_sun,
                'casual_sun' => $request->casual_sun,
                'pf_ph' => $request->pf_ph,
                'casual_ph' => $request->casual_ph
                ];
                $res= PayRatesNew::insert($payrate);
                if($res){
                return response()->json(array('success' => true,'message' => 'PayRates Added Successfully!'));

                }else{
                return response()->json(array('success' => false, 'message' => 'something went wrong!!!!'));

                }
            }
    }
    // Create_award_rate
    public function create_award_rate( Request $request){
            if($request->has('payrate_id') && $request->payrate_id!=null ){
                return redirect('update_payrates/'.$request->payrate_id);
                    // $this->update_payrates($request->payrate_id,$request);
                    // exit();
            }else{
                $is_already = PayRatesNew::where(['level' => $request->level, 'hours' => $request->hours, 'award_rate' => 1])->first();
                if (!empty($is_already)) {
                return response()->json(array('success' => false, 'message' => 'Award Rate already exist!'));
                }
                $payrate=[
                'title' => $request->title,
                /* The above code is setting the value of the 'effective_date' key in an array to the
                value of ->effective_date. This code is likely part of a PHP script where
                 is an object or array containing data from a request, and the
                effective_date property is being extracted and assigned to the 'effective_date' key
                in another array or data structure. */
                'effective_date'=>$request->effective_date,
                'hours' => $request->hours,
                'level' => $request->level,
                'weekend' => ($request->has('weekend') && $request->weekend == 1) ? 1 : 0,
                'pf_day' => $request->pf_day,
                'casual_day' => $request->casual_day,
                'pf_night' => $request->pf_night,
                'casual_night' => $request->casual_night,
                'pf_sat' => $request->pf_sat,
                'casual_sat' => $request->casual_sat,
                'pf_sun' => $request->pf_sun,
                'casual_sun' => $request->casual_sun,
                'pf_ph' => $request->pf_ph,
                'casual_ph' => $request->casual_ph,
                'award_rate' => 1

                ];
                $res= PayRatesNew::insert($payrate);
                if($res){
                return response()->json(array('success' => true, 'message' => 'PayRates Added Successfully!'));

                }else{
                return response()->json(array('success' => false, 'message' => 'something went wrong!!!!'));

                }
            }
    }

// Get payrates
    public function get_payrates($id,Request $request)
    {
                // $payrate = payrate::where(['id' => $id])->first();
                // if (!empty($payrate)) {
                //     return response()->json(['success' => true, 'message' => "Payrates retrieve", 'payrates' => $payrate]);
                // }else{
                //     return response()->json(['success' => false, 'message' => "Payrates fail"]);
                // }
            if ($id != 0) {
                $payrate = PayRatesNew::where(['id' => $id])->first();
            }else{
                $query = PayRatesNew::where(['level' => $request->level, 'hours' => $request->hours]);
                if ($request->has('name') && $request->name != '') {
                $query->where('title', $request->name);
                }
                if ($request->has('weekend') && $request->weekend != false && $request->weekend != 'false') {
                $query->where('weekend', 1);
                }else{
                // $query->where('weekend', 0);
                }
                $payrate  = $query->first();

            }
            if (!empty($payrate)) {
                $payrate->effective_date = date('Y-m-d', strtotime($payrate->effective_date));
                return response()->json(['success' => true, 'message' => "payrate retrieve", 'payrates' => $payrate]);
            }else{
                return response()->json(['success' => false, 'message' => "payrate fail"]);
            }
    }

//update payrate
    public function update_payrates(Request $request)
    {
            $payrate_id=$request->id;

            $formattedDate = Carbon::createFromFormat('Y/m/d', $request->effective_date)->format('Y-m-d');
            $payrate=[
                'title' => $request->title,
                'effective_date'=>$formattedDate,
                'hours' => $request->hours,
                'level' => $request->level,
                'weekend' => ($request->has('weekend') && $request->weekend == 1) ? 1 : 0,
                'pf_day' => $request->pf_day,
                'casual_day' => $request->casual_day,
                'pf_night' => $request->pf_night,
                'casual_night' => $request->casual_night,
                'pf_sat' => $request->pf_sat,
                'casual_sat' => $request->casual_sat,
                'pf_sun' => $request->pf_sun,
                'casual_sun' => $request->casual_sun,
                'pf_ph' => $request->pf_ph,
                'casual_ph' => $request->casual_ph
            ];
                //      $res= payrate::where('id',$payrate_id)->update($payrate);
                //      if($res){
                //   return response()->json(array('success' => true));

                // }else{
                //   return response()->json(array('success' => false));

                // }
        $payrate_check = PayRatesNew::where(['level' => $request->level, 'title' => $request->title])->first();
        if (!empty($payrate_check)) {
            $res= PayRatesNew::where('id',$payrate_check->id)->update($payrate);
            if ($res) {
                    $this->logPayRate($payrate_check, $payrate_check->id,$formattedDate,$request->admin_id);
                    }
        }else{
            $res= PayRatesNew::insert($payrate);
        }
        if($res){
            return response()->json(array('success' => true,'message' => 'PayRates updated!!'));

        }else{
            return response()->json(array('success' => false));

        }

    }
    // LogPayRate 
    function logPayRate($pay_rate, $pay_rate_id, $effective_date_to,$admin_id)
        {
        DB::table('payrates_history')->insert([
            'payrate_id' => $pay_rate_id,
            'data' => json_encode($pay_rate),
            'changed_by' => $admin_id,
            'effective_from' => "test",
            'effective_to' => $effective_date_to,
        ]);
        }

// Delete Payrate
    public function delete_payrate($id){
        $res= PayRatesNew::where('id',$id)->delete();
        if($res){
            return response()->json(array('success' => true,'message' => 'Payrates deleted!!'));

        }else{
            return response()->json(array('success' => false));

        } 
        }




}
