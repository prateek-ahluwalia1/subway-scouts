<?php

namespace App\Http\Controllers\crm;

use App\Http\Controllers\Controller;
use App\Http\Resources\CrmEditQuotationResource;
use App\Http\Resources\GetAllQuotationResource;
use App\Models\Quotation;
use Carbon\Carbon;
use Illuminate\Http\Request;

class QuotationController extends Controller
{
    public function getAllQuotation(Request $request) {
        if($request->userType == 'super-admin'){
            if(isset($request->type) && $request->type == 'all'){
                $quotations = Quotation::latest()->get();
            }else{
                $quotations = Quotation::where('financial_type', $request->type)->latest()->get();
            }
        }else{
            if(isset($request->type) && $request->type == 'all'){
                $quotations = Quotation::where('created_by', $request->id)->latest()->get();
            }else{
                $quotations = Quotation::where(['created_by'=> $request->id, 'financial_type'=> $request->type])->latest()->get();
            }
        }
        $qu = GetAllQuotationResource::collection($quotations);
        return response()->json(['success' => true, 'data' => $qu]);         
     }
    
      public function addAndUpdateQuotation(Request $request) {   

        $quotation = Quotation::find($request->id);

        $is_new_quotation = false;
        if (!$quotation) {
            // If the quotation with the given id is not found, create a new instance
            $quotation = new Quotation();
            $is_new_quotation = true;
            $quotation->qut_owner = $request->qut_owner;
            $quotation->created_by = $request->admin_id;
        }
        // $quotation->from_name = $request->from_name;
        // $quotation->from_email = $request->from_email;
        // $quotation->from_message = $request->from_message;
        // $quotation->to_name = $request->to_name;
        // $quotation->to_email = $request->to_email;
        // $quotation->to_message = $request->to_message;
        
        $quotation->deal_name = $request->deal_name;
        $quotation->abn = $request->abn;
        $quotation->subject = $request->subject;
        $quotation->valid = $request->valid;
        $quotation->contacted_id = $request->contacted_id;
        $quotation->lead_status = $request->lead_status;
        $quotation->account_name = $request->account_name;
        $quotation->bank_name = $request->bank_name;
        $quotation->team = $request->team;
        $quotation->carrier = $request->carrier;
        $quotation->ship_country = $request->ship_country;
        $quotation->bill_country = $request->bill_country;
        $quotation->ship_code = $request->ship_code;
        $quotation->bill_code = $request->bill_code;
        $quotation->ship_state = $request->ship_state;
        $quotation->bill_state = $request->bill_state;
        $quotation->ship_city = $request->ship_city;
        $quotation->bill_city = $request->bill_city;
        $quotation->ship_street = $request->ship_street;
        $quotation->bill_street = $request->bill_street;
        $quotation->add_notes = $request->add_notes;
        $quotation->grd_total = $request->grd_total;
        $quotation->discount = $request->discount;
        $quotation->showGstInput = $request->showGstInput;
        $quotation->sub_total = $request->sub_total;
        $quotation->financial_type = $request->financial_type;
        $quotation->currency = $request->currency;
        $quotation->company = $request->company;
        if($request->has('quote') && !empty($request->quote)){
            $quotation->quote =  json_encode($request->quote);
        }
        
        $quotation->save();
        if ($is_new_quotation) {
            $quotation = Quotation::find($quotation->id);
            $quotation->invoice_no = Carbon::today()->format('ymd').$quotation->id;
            $quotation->update();
            return response()->json(['success' => true, 'msg' => 'Quotation Added']);
        } else {
            return response()->json(['success' => true, 'msg' => 'Quotation Updated']);
        }
        
      }
       public function edit(Request $request) {
        $quotation = Quotation::where('id', $request->id)->with('Lead')->first();
        if($quotation){
            $qu = new CrmEditQuotationResource($quotation);
            return response()->json(['success' => true, 'data' => $qu]);
        }else{
            return response()->json(['success' => false, 'error' => 'Record not Found']); 
        }  
       }
    
       public function deleteQuotation(Request $request) {
        $quotation = Quotation::where('id', $request->id)->first();
        if($quotation){
            $quotation->delete();
            return response()->json(['success' => true, 'msg' => 'Quotation Deleted']);
        }else{
            return response()->json(['success' => false, 'error' => 'Record not Found']); 
        }  
       }
    public function getQuotations($contacted_id){
        $data = Quotation::where('contacted_id', $contacted_id)->select('id', 'invoice_no', 'qut_owner', 'created_at', 'grd_total', 'financial_type', 'currency', 'company')->get();
        return response()->json([
            'success' => true,
            'data'=> $data
        ], 200);
    }
    public function convetQuoteIntoInvoice(Request $request){
        $old_quotation = Quotation::find($request->id);
        if($old_quotation && $old_quotation->financial_type == 'quote'){
            $quotation = new Quotation();
            $quotation->qut_owner = $old_quotation->qut_owner;
            $quotation->created_by = $request->admin_id;
            $quotation->deal_name = $old_quotation->deal_name;
            $quotation->abn = $old_quotation->abn;
            $quotation->subject = $old_quotation->subject;
            $quotation->valid = $old_quotation->valid;
            $quotation->contacted_id = $old_quotation->contacted_id;
            $quotation->lead_status = $old_quotation->lead_status;
            $quotation->account_name = $old_quotation->account_name;
            $quotation->bank_name = $old_quotation->bank_name;
            $quotation->team = $old_quotation->team;
            $quotation->carrier = $old_quotation->carrier;
            $quotation->ship_country = $old_quotation->ship_country;
            $quotation->bill_country = $old_quotation->bill_country;
            $quotation->ship_code = $old_quotation->ship_code;
            $quotation->bill_code = $old_quotation->bill_code;
            $quotation->ship_state = $old_quotation->ship_state;
            $quotation->bill_state = $old_quotation->bill_state;
            $quotation->ship_city = $old_quotation->ship_city;
            $quotation->bill_city = $old_quotation->bill_city;
            $quotation->ship_street = $old_quotation->ship_street;
            $quotation->bill_street = $old_quotation->bill_street;
            $quotation->add_notes = $old_quotation->add_notes;
            $quotation->grd_total = $old_quotation->grd_total;
            $quotation->discount = $old_quotation->discount;
            $quotation->showGstInput = $old_quotation->showGstInput;
            $quotation->sub_total = $old_quotation->sub_total;
            $quotation->financial_type = 'invoice';
            $quotation->currency = $old_quotation->currency;
            $quotation->company = $old_quotation->company;
            $quotation->quote =  $old_quotation->quote;
            $quotation->save();
            $quotation = Quotation::find($quotation->id);
            $quotation->invoice_no = Carbon::today()->format('ymd').$quotation->id;
            $quotation->update();
            return response()->json([
                'success' => true,
                'message'=> 'Quote Converted into invoice',
            ], 200);
        }else{
            return response()->json([
                'success' => false,
                'message'=> 'Quote not found or maybe its already invoice',
            ], 200);

        }
    }
}
