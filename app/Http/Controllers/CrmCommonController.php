<?php

namespace App\Http\Controllers;

use App\Console\Commands\CrmReminders;
use App\Http\Resources\CrmGetContactLeadsResource;
use App\Http\Resources\CrmGetSpecifcLeadsResource;
use App\Http\Resources\CrmReminders as ResourcesCrmReminders;
use App\Models\CrmNotification;
use App\Models\CrmReminder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\CRMLeadData;
class CrmCommonController extends Controller
{
    public function getSpecificLeads(Request $request){
        if($request->userType == 'super-admin'){
        $leadsQuery = DB::table('crm_customers')
        ->whereNotIn('lead_status', ['Contacted', 'Won', 'Lost Lead']);

        if ($request->has('agent_id') && !empty($request->agent_id)) {
            $leadsQuery->whereIn('saleperson_id', $request->agent_id);
        }

        if ($request->has('company_name') && !empty($request->company_name)) {
            $leadsQuery->whereIn('sub_company', $request->company_name);
        }

        if ($request->has('start') && !empty($request->start) && $request->has('end') && !empty($request->end)) {
            $start_date = Carbon::createFromFormat('m-d-Y', $request->start)->startOfDay();
            $end_date = Carbon::createFromFormat('m-d-Y', $request->end)->endOfDay();
            $leadsQuery->whereBetween('created_at', [$start_date, $end_date]);
        }

        $leads = $leadsQuery->latest()->get();

        }else{
            
            $leadsQuery = DB::table('crm_customers')
            ->whereNotIn('lead_status', ['Contacted', 'Won', 'Lost Lead'])
            ->where(function ($query) use ($request) {
                $query->where('created_by', $request->id)
                    ->orWhere('saleperson_id', $request->id)
                    ->orWhere('assign_operation', $request->id);
            });

        if ($request->has('company_name') && !empty($request->company_name)) {
            $leadsQuery->whereIn('sub_company', $request->company_name);
        }

        if ($request->has('start') && !empty($request->start) && $request->has('end') && !empty($request->end)) {
            $start_date = Carbon::createFromFormat('m-d-Y', $request->start)->startOfDay();
            $end_date = Carbon::createFromFormat('m-d-Y', $request->end)->endOfDay();
            $leadsQuery->whereBetween('created_at', [$start_date, $end_date]);
        }

        $leads = $leadsQuery->latest()->get();


        }
        $ld = CrmGetSpecifcLeadsResource::collection($leads);
        return response()->json(['success'=>true,'data' => $ld], 200);
    }
    
    public function getContactLeads(Request $request){
        if($request->userType == 'super-admin'){

            $leadsQuery = DB::table('crm_customers')
            ->where('lead_status', 'Contacted');
    
            if ($request->has('agent_id') && !empty($request->agent_id)) {
                $leadsQuery->whereIn('saleperson_id', $request->agent_id);
            }
    
            if ($request->has('company_name') && !empty($request->company_name)) {
                $leadsQuery->whereIn('sub_company', $request->company_name);
            }
    
            if ($request->has('start') && !empty($request->start) && $request->has('end') && !empty($request->end)) {
                $start_date = Carbon::createFromFormat('m-d-Y', $request->start)->startOfDay();
                $end_date = Carbon::createFromFormat('m-d-Y', $request->end)->endOfDay();
                $leadsQuery->whereBetween('created_at', [$start_date, $end_date]);
            }
    
            $leads = $leadsQuery->latest()->get();
    
        }else{
            $leadsQuery = DB::table('crm_customers')
            ->where('lead_status', 'Contacted')
            ->where(function ($query) use ($request) {
                $query->where('created_by', $request->id)
                    ->orWhere('saleperson_id', $request->id)
                    ->orWhere('assign_operation', $request->id);
            });
    
            if ($request->has('company_name') && !empty($request->company_name)) {
                $leadsQuery->whereIn('sub_company', $request->company_name);
            }
    
            if ($request->has('start') && !empty($request->start) && $request->has('end') && !empty($request->end)) {
                $start_date = Carbon::createFromFormat('m-d-Y', $request->start)->startOfDay();
                $end_date = Carbon::createFromFormat('m-d-Y', $request->end)->endOfDay();
                $leadsQuery->whereBetween('created_at', [$start_date, $end_date]);
            }
    
            $leads = $leadsQuery->latest()->get();
        }
        $ld = CrmGetSpecifcLeadsResource::collection($leads);
        return response()->json(['success'=>true,'data' => $ld], 200);
    }
    public function getWonLeads(Request $request){
        if($request->userType == 'super-admin'){

            $leadsQuery = DB::table('crm_customers')
            ->where('lead_status', 'Won');
    
            if ($request->has('agent_id') && !empty($request->agent_id)) {
                $leadsQuery->whereIn('saleperson_id', $request->agent_id);
            }
    
            if ($request->has('company_name') && !empty($request->company_name)) {
                $leadsQuery->whereIn('sub_company', $request->company_name);
            }
    
            if ($request->has('start') && !empty($request->start) && $request->has('end') && !empty($request->end)) {
                $start_date = Carbon::createFromFormat('m-d-Y', $request->start)->startOfDay();
                $end_date = Carbon::createFromFormat('m-d-Y', $request->end)->endOfDay();
                $leadsQuery->whereBetween('created_at', [$start_date, $end_date]);
            }
    
            $leads = $leadsQuery->latest()->get();

        }else{
            $leadsQuery = DB::table('crm_customers')
            ->where('lead_status', 'Won')
            ->where(function ($query) use ($request) {
                $query->where('created_by', $request->id)
                    ->orWhere('saleperson_id', $request->id)
                    ->orWhere('assign_operation', $request->id);
            });
    
            if ($request->has('company_name') && !empty($request->company_name)) {
                $leadsQuery->whereIn('sub_company', $request->company_name);
            }
    
            if ($request->has('start') && !empty($request->start) && $request->has('end') && !empty($request->end)) {
                $start_date = Carbon::createFromFormat('m-d-Y', $request->start)->startOfDay();
                $end_date = Carbon::createFromFormat('m-d-Y', $request->end)->endOfDay();
                $leadsQuery->whereBetween('created_at', [$start_date, $end_date]);
            }
    
            $leads = $leadsQuery->latest()->get();
        }
        $ld = CrmGetSpecifcLeadsResource::collection($leads);
        return response()->json(['success'=>true,'data' => $ld], 200);
    }
    public function getLostLeads(Request $request){
        if($request->userType == 'super-admin'){

            $leadsQuery = DB::table('crm_customers')
            ->where('lead_status', 'Lost Lead');
    
            if ($request->has('agent_id') && !empty($request->agent_id)) {
                $leadsQuery->whereIn('saleperson_id', $request->agent_id);
            }
    
            if ($request->has('company_name') && !empty($request->company_name)) {
                $leadsQuery->whereIn('sub_company', $request->company_name);
            }
    
            if ($request->has('start') && !empty($request->start) && $request->has('end') && !empty($request->end)) {
                $start_date = Carbon::createFromFormat('m-d-Y', $request->start)->startOfDay();
                $end_date = Carbon::createFromFormat('m-d-Y', $request->end)->endOfDay();
                $leadsQuery->whereBetween('created_at', [$start_date, $end_date]);
            }
    
            $leads = $leadsQuery->latest()->get();
            
        }else{
            
            $leadsQuery = DB::table('crm_customers')
            ->where('lead_status', 'Lost Lead')
            ->where(function ($query) use ($request) {
                $query->where('created_by', $request->id)
                    ->orWhere('saleperson_id', $request->id)
                    ->orWhere('assign_operation', $request->id);
            });
    
            if ($request->has('company_name') && !empty($request->company_name)) {
                $leadsQuery->whereIn('sub_company', $request->company_name);
            }
    
            if ($request->has('start') && !empty($request->start) && $request->has('end') && !empty($request->end)) {
                $start_date = Carbon::createFromFormat('m-d-Y', $request->start)->startOfDay();
                $end_date = Carbon::createFromFormat('m-d-Y', $request->end)->endOfDay();
                $leadsQuery->whereBetween('created_at', [$start_date, $end_date]);
            }
    
            $leads = $leadsQuery->latest()->get();
        }
        $ld = CrmGetSpecifcLeadsResource::collection($leads);
        return response()->json(['success'=>true,'data' => $ld], 200);
    }

    public function saveBase64Image(Request $request){
        $decodedImage = base64_decode($request->image);
        $filename = uniqid() . '.png';
        $path = public_path($request->folder. '/' . $filename);
        $data = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $request->image));
        file_put_contents($path, $data);
        $sizeInKB = filesize($path) / 1024;
        return [
            'filename' => $filename,
            'size_kb' => $sizeInKB,
        ];
    }
    public function getNotifications($id){
        $getNotifi =  CrmNotification::where('send_to', $id)->where('status', 'unseen')->with(['SendBy', 'SendTo'])->first();
        $getNotifiCount =  CrmNotification::where('send_to', $id)->where('status', 'unseen')->count();
        if(!empty($getNotifi)){
            DB::table('crm_notifications')->update(['status'=> 'seen']);
        }
        return response()->json([
            'success' => true,
            'unseen' => $getNotifi,
            'count' => $getNotifiCount
        ]);
    }
    public function addReminder(Request $request){
        if($request->reminders){
            foreach($request->reminders as $reminder){
                $CrmReminder = new CrmReminder();
                $CrmReminder->lead_id = $reminder['lead_id'];
                $CrmReminder->created_by = $reminder['created_by'];
                $CrmReminder->subject = $reminder['subject'];
                $CrmReminder->date = $reminder['date'];
                $CrmReminder->notify_too = is_array($reminder['notify_too']) ? json_encode($reminder['notify_too']) : null;
                $CrmReminder->description = $reminder['description'];
                $CrmReminder->status = 'pending';
                $CrmReminder->is_sent = 0;
                $CrmReminder->save();
            }
        }
        return  response()->json(['success'=>true, 'message'=>'Reminder added successfully'], 200);
    }
    public function getReminder($lead_id){
        $getLead = CrmReminder::where('lead_id', $lead_id)->with(['createdBy'])->get();
        $leads = ResourcesCrmReminders::collection($getLead);
        return response()->json(['success'=>true,'data'=>$leads]);
    }
    public function deleteReminder($reminder_id){
        $getReminder = CrmReminder::find($reminder_id);
        if($getReminder){
            $getReminder->delete();
            return response()->json(['success'=>true,'message'=>'Reminder Deleted']);
        }else{
            return response()->json(['success'=>false,'message'=>'Reminder Not found']);
        }
    }
    public function changeReminderStatus($reminder_id){
        $getReminder = CrmReminder::find($reminder_id);
        if($getReminder){
            $getReminder->status = 'completed';
            $getReminder->update();
            return response()->json(['success'=>true,'message'=>'Reminder Deleted']);
        }else{
            return response()->json(['success'=>false,'message'=>'Reminder Not found']);
        }
    }
    public function generateCrmLead(Request $request)
    {
        $filename = 'crm_lead_report.xlsx';
        Excel::store(new CRMLeadData, 'excel/guard/'.$filename, 'excels');
        return response()->json(['success' =>  true, 'message' => 'Staff Report generated successfully.','path' => 'https://'.request()->getHttpHost().'/excel/guard/'.$filename]);
    }
    public function getReportData(){
       return DB::table('crm_customers')->get();
    }
}
