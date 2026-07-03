<?php
namespace App\Http\Controllers;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use App\Models\AwardPayrate as payrate;
use Illuminate\Support\Facades\Session;
use Carbon\Carbon;


class AwardPayrates extends Controller
{
    public function payrates(Request $request)
  {
   // $payrates = payrate::groupBy('hours', 'level', 'weekend')->get();
   $payrates = payrate::get();
   foreach ($payrates as $key => $payrate) {
    $payrate->groupData = payrate::where('title', $payrate->title)->select('level')->get();
  }
 
  return response()->json(array('success' => true, 'payrates' => $payrates));

  
}
public function create_payrate( Request $request){
  if($request->has('payrate_id') && $request->payrate_id!=null ){
    return redirect('update_award_payrates/'.$request->payrate_id);
        // $this->update_payrates($request->payrate_id,$request);
        // exit();
  }else{
    $is_already = payrate::where(['level' => $request->level, 'hours' => $request->hours, 'title' => $request->title])->first();
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
      'casual_ph' => $request->casual_ph,
      'award_rate' => 1
    ];
    $res= payrate::insert($payrate);
    if($res){
      return response()->json(array('success' => true,'message' => 'AwardRates Sucessfully add'));
    }else{
      return response()->json(array('success' => false, 'message' => 'something went wrong!!!!'));
    }
  }
}
public function get_payrates($id,Request $request)
{
    // $payrate = payrate::where(['id' => $id])->first();
    // if (!empty($payrate)) {
    //     return response()->json(['success' => true, 'message' => "Payrates retrieve", 'payrates' => $payrate]);
    // }else{
    //     return response()->json(['success' => false, 'message' => "Payrates fail"]);
    // }
  if ($id != 0) {
    $payrate = payrate::where(['id' => $id])->first();
  }else{
    $query = payrate::where(['level' => $request->level, 'hours' => $request->hours]);
    if ($request->has('name') && $request->name != '') {
      $query->where('title', $request->name);
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
  $res = payrate::where('id',$payrate_id)->update($payrate);
  // $payrate_check = payrate::where(['level' => $request->job_level, 'title' => $request->title])->first();
  if (!empty($payrate_check)) {
    $res= payrate::where('id',$payrate_check->id)->update($payrate);
    if ($res) {
              $this->logPayRate($payrate_check, $payrate_check->id,$formattedDate,$request->admin_id);
            }
  }else{
  $res = payrate::where('id',$payrate_id)->update($payrate);
    // $res= payrate::insert($payrate);
  }
  if($res){
    return response()->json(array('success' => true));
  }else{
    return response()->json(array('success' => true,'message' => 'AwardRates Updated!!'));
  }
}
function logPayRate($pay_rate, $pay_rate_id, $effective_date_to,$admin_id)
{
  DB::table('payrates_history')->insert([
    'payrate_id' => $pay_rate_id,
    'data' => json_encode($pay_rate),
    'changed_by' => $admin_id,
    'effective_from' => $pay_rate->effective_date,
    'effective_to' => $effective_date_to,
  ]);
}
public function delete_payrate($id){
  $res= payrate::where('id',$id)->delete();
  if($res){
    return response()->json(array('success' => true,'message' => 'AwardRates deleted!!'));
  }else{
    return response()->json(array('success' => false));
  }
}
function award_rate_model()
{
  $html = view('admin/payrate/award_rates_form')->render();
  return response()->json($html);
}
}