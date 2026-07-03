<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SubscriptionPlan;
use App\Http\Resources\EditSubscriptionPlanResource;

class SubscriptionPlanController extends Controller
{
   
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $plan = new  SubscriptionPlan;
        $plan->name = $request->name;
        $plan->price = $request->price;
        $plan->description = $request->description;
        $plan->permission = json_encode($request->permission, true);;
        $plan->save();

        return response()->json(['message' => "Subscription Plan Added Successfully!" ,  'code' => 200, 'success' => true]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        
        $update_plan = SubscriptionPlan::findOrFail($request->id);

        if($update_plan){

        $update_plan->name = $request->name;
        $update_plan->price = $request->price;
        $update_plan->description = $request->description;
        $update_plan->permission = json_encode($request->permission, true);
        $update_plan->update();

        return response()->json(['message' => "Subscription Plan Updated Successfully!" ,  'code' => 200, 'success' => true]);
        }
        else
        {
        return response()->json(['msg' =>  'Record Not Found!',  'code' => 404, 'success' => false]);
        }
        
    }

    public function getAllPlans()
    {
        $getAll = SubscriptionPlan::all();
        return response()->json(['data' => $getAll ,  'code' => 200, 'success' => true]);
    }

    
    public function edit(Request $request)
    {
        $storePlan = SubscriptionPlan::where('id', $request->id)->first();
        if($storePlan){
            $sp = new EditSubscriptionPlanResource($storePlan);
            return response()->json(['data' =>  $sp,  'code' => 200, 'success' => true]);
        }else{
            return response()->json(['data' =>  '',  'code' => 404, 'success' => false]);
        }
    }

    public function delete(Request $request)
    {
        $storePlan = SubscriptionPlan::where('id', $request->id)->first();
        if($storePlan){
            $storePlan->delete(); 
            return response()->json(['msg' =>  'Record Deleted Successfully!',  'code' => 200, 'success' => true]);
        }else{
            return response()->json(['msg' =>  'Record Not Found!',  'code' => 404, 'success' => false]);
        }
    }

    public function polipayment(Request $request)
    {
        $json_data = array(
            'Amount'              => $request->grand_total,
            'CurrencyCode'        => 'NZD',
            'MerchantReference'   => $request->id,
            'MerchantHomepageURL' => 'http://127.0.0.1:8000/',
            'SuccessURL'          => 'http://127.0.0.1:8000/api/subscription-plan/payment/success',
            'FailureURL'          => 'http://127.0.0.1:8000/api/subscription-plan/payment/success',
            'CancellationURL'     => 'http://127.0.0.1:8000/api/subscription-plan/payment/success',
            'NotificationURL'     => 'http://127.0.0.1:8000/api/subscription-plan/payment/success'
        );

        $json_builder = json_encode($json_data);

        $auth = base64_encode('SS64013299:Dq5@wyhBPZlT');
        $header = array(
            'Content-Type: application/json',
            'Authorization: Basic '.$auth
        );
        $ch = curl_init("https://poliapi.apac.paywithpoli.com/api/v2/Transaction/Initiate");
        curl_setopt($ch, CURLOPT_CAINFO, "D:/Shahbaz Data/cacert.pem");
        curl_setopt($ch, CURLOPT_SSLVERSION, CURL_SSLVERSION_TLSv1_2);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $json_builder);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        $response = curl_exec($ch);
        curl_close($ch);

        $json = json_decode($response, true);

        if (isset($json['NavigateURL'])) {
            
            return redirect()->to($json['NavigateURL']);

        } else {

            return response()->json(['msg' =>  'NavigateURL key is missing from the JSON response',  'code' => 404, 'success' => false]);
        }

    }

    public function paymentsuccess(Request $request)
    {
        $token = $request->token;
        if(is_null($token)) {
        $token = $_GET["token"];
        }

        $auth = base64_encode('SS64013299:Dq5@wyhBPZlt');
        $header = array();
        $header[] = 'Authorization: Basic '.$auth;

        $ch = curl_init("https://poliapi.apac.paywithpoli.com/api/v2/Transaction/GetTransaction?token=".urlencode($token));

        curl_setopt($ch, CURLOPT_CAINFO, "D:/Shahbaz Data/cacert.pem");
        curl_setopt( $ch, CURLOPT_SSLVERSION, CURL_SSLVERSION_TLSv1_2);
        curl_setopt( $ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt( $ch, CURLOPT_HEADER, 0);
        curl_setopt( $ch, CURLOPT_POST, 0);
        curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, 0);
        curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1);
        $response = curl_exec( $ch );
        curl_close ($ch);

        $json = json_decode($response, true);

        if($json['TransactionStatus'] == "Completed")
        {   
            //Here below we can update bussiness setting table and subscription plan table


            return response()->json(['msg' =>  'Transcation Completed Successfully!',  'code' => 200, 'success' => true]);
       
        }elseif($json['TransactionStatus'] == "Failed"){

            return response()->json(['msg' =>  'Transcation Failed!',  'code' => 404, 'success' => false]);
            
        }elseif($json['TransactionStatus'] == "Cancelled"){

            return response()->json(['msg' =>  'Transcation Cancelled!',  'code' => 404, 'success' => false]);  
        }
        else{

            return response()->json(['msg' =>  'Poli Payment Receipt Unverified.',  'code' => 404, 'success' => false]);
        }

    }

}
