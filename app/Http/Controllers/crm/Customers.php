<?php

namespace App\Http\Controllers\crm;

use App\Events\CrmNotification;
use App\Http\Controllers\Controller;
use App\Http\Resources\CrmCustomerTimeLineResource;
use App\Http\Resources\GetCrmCustomerResource;
use App\Models\JobRoster;
use App\Models\JobRosterAction;
use App\Models\User;
use Illuminate\Http\Request;
use App\Models\crm\CustomerModel as Customer;
use App\Models\Customer as NormalCustomer;
use App\Models\crm\SalePersonModel;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Carbon\Carbon;
use \Datetime;
use DB;
use Illuminate\Support\Facades\Validator;

class Customers extends Controller
{
    public function getCustomersList(Request $request)
    {
        $monthNo = $request->input('month_no', Carbon::now()->month);

        $currentMonthStart = Carbon::now()->month($monthNo)->startOfMonth()->toDateString();
        $currentMonthEnd = Carbon::now()->month($monthNo)->endOfMonth()->toDateString();

        if ($request->userType == 'super-admin') {
            $persons = Customer::whereBetween('created_at', [$currentMonthStart, $currentMonthEnd])->WhereIn('lead_status', ['Attempted to Contact','Contact in Future','Contacted','Not Contacted', 'Won', 'Won Completed', 'Won Voucher'])->get();
        } else {
            $persons = Customer::where('created_by', $request->id)
                ->whereBetween('created_at', [$currentMonthStart, $currentMonthEnd])
                ->WhereIn('lead_status', ['Attempted to Contact','Contact in Future','Contacted','Not Contacted', 'Won', 'Won Completed', 'Won Voucher'])
                ->get();
        }

        if (count($persons) > 0) {
            foreach ($persons as $key => $p) {
                $persons_name = User::where('id', $p->saleperson_id)
                    ->where('userType', 'admin')
                    ->where('is_super_admin', 0)
                    ->value('name');
                $p->agent_name = $persons_name;
            }
            return response()->json(['success' => true, 'message' => 'List found.', 'data' => $persons]);
        } else {
            return response()->json(['success' => false, 'message' => 'No list found!', 'data' => $persons]);
        }
    }
    public function dashboardCustomersList(Request $request)
    {
        $monthNo = $request->input('month_no', Carbon::now()->month);

        $currentMonthStart = Carbon::now()->month($monthNo)->startOfMonth()->toDateString();
        $currentMonthEnd = Carbon::now()->month($monthNo)->endOfMonth()->toDateString();

        if ($request->userType == 'super-admin') {
            $persons = Customer::whereBetween('created_at', [$currentMonthStart, $currentMonthEnd])->get();
        } else {
            $persons = Customer::where('created_by', $request->id)
                ->whereBetween('created_at', [$currentMonthStart, $currentMonthEnd])
                ->get();
        }

        if (count($persons) > 0) {
            foreach ($persons as $key => $p) {
                $persons_name = User::where('id', $p->saleperson_id)
                    ->where('userType', 'admin')
                    ->where('is_super_admin', 0)
                    ->value('name');
                $p->agent_name = $persons_name;
            }
            return response()->json(['success' => true, 'message' => 'List found.', 'data' => $persons]);
        } else {
            return response()->json(['success' => false, 'message' => 'No list found!', 'data' => $persons]);
        }
    }

    function getCustomersDetails($id)
    {
        $persons = Customer::where('id', $id)->with(['CreatedBy', 'HandledBy'])->first();
        if (!empty($persons)) {

            $p = new GetCrmCustomerResource($persons);
            return response()->json(['success' => true, 'message' => 'Details found.', 'data' => $p]);
        }else{
            return response()->json(['success' => false, 'message' => 'No detail found!', 'data' => '']);
        }
    }
    function archiveCustomer(Request $request)
    {
        $persons = Customer::find($request->id);
        if (!empty($persons)) {
            $persons->is_archived = 1;
            $persons->archived_by = $request->admin_id;
            $persons->update();
            return response()->json(['success' => true, 'message' => 'Lead Archived.']);
        }else{
            return response()->json(['success' => false, 'message' => 'No detail found!']);
        }
    }

    function addCustomer(Request $request)
    {
        $person = new Customer();
        $person->name = $request->name;
        $person->email = $request->email;
        $person->phone = $request->phone;
        $person->company = $request->company;
        $person->address = $request->address;
        $person->saleperson_id = $request->saleperson_id; #HANDLE BY
        $person->password = $request->has('password') ? Hash::make($request->password) : Hash::make('Temp123456');
        $person->fax = $request->fax;
        $person->website = $request->website;
        $person->title = $request->title;
        $person->job_title = $request->job_title;
        $person->priority = $request->priority;
        $person->loss_value = $request->loss_value;
        $person->won_status = $request->won_status;
        $person->lead_source = $request->lead_source;
        $person->lead_status = $request->lead_status;
        $person->industry = $request->industry;
        $person->no_emp = $request->no_emp;
        $person->annual_revenue = $request->annual_revenue;
        $person->postal_code = $request->postal_code;
        $person->manual_revenue = $request->manual_revenue;
        $person->rating = $request->rating;
        $person->skype_id = $request->skype_id;
        $person->secondary_email = $request->secondary_email;
        $person->twitter = $request->twitter;
        $person->street = $request->street;
        $person->state = $request->state;
        $person->country = $request->country;
        $person->city = $request->city;
        $person->zip_code = $request->zip_code;
        $person->lost_lead_option = $request->lost_lead_option;
        $person->description = $request->description;
        $person->created_by = $request->admin_id;
        $person->comp_size = $request->comp_size;
        $person->industries = $request->industries;
        $person->abn = $request->abn;
        $person->voucher = $request->voucher;
        if(is_int($request->leaad_client_name)){
            $person->leaad_client_name = $request->leaad_client_name;
        }elseif (is_string($request->leaad_client_name)){
            $business = $request->header('Business-Id');
            if($business != 87){
                $validator = Validator::make(['email' => $request->email], [
                    'email' => 'required|email|unique:customers,email',
                ]);
                if ($validator->fails()) {
                    return response()->json([
                        'message' => 'Customer Email is already exist. Please try another email.',
                        'success' => false
                    ]);
                }
            }
            $customer = new NormalCustomer();
            $customer->name = $request->leaad_client_name;
            $customer->email = $request->email; 
            $customer->password = Hash::make(123456);
            $customer->phone = $request->phone;
            $customer->city = $request->city;
            $customer->company_name = $request->company_name;
            $customer->status ='active';
            $customer->save();
            $person->leaad_client_name = $customer->id;
        }
        $person->sub_company = $request->sub_company;
        $person->no_of_traveler = $request->no_of_traveler;
        $person->actual_revenue = $request->actual_revenue; 
        $person->booking_expense = $request->booking_expense;
        $person->assign_operation = $request->assign_operation;
        $person->travel_date = $request->travel_date;
        if($request->lead_status == 'Won'){
            $person->won_by = $request->admin_id;     
        }
        if($request->lead_status == 'Lost Lead'){
            $person->loss_by = $request->admin_id;     
        }
        if($request->lead_status == 'Contacted'){
            $person->contacted_by = $request->admin_id;     
        }
        #CREATED BY
        $person->save();
        if(isset($request->assign_operation) && !empty($request->assign_operation)){
            $getSalePerson = User::find($request->assign_operation);
            $subject = 'You have assigned a new lead!';
            $message = 'You have assigned a new lead please login to your portal and have a look thanks </br> Team TheScouts';
            genericMail($getSalePerson->email, $subject, $message, $person);
        }
        if(isset($request->saleperson_id) && !empty($request->saleperson_id)){
            $getSalePerson = User::find($request->saleperson_id);
            $subject = 'You have assigned a new lead!';
            $message = 'You have assigned a new lead please login to your portal and have a look thanks </br> Team TheScouts';
            genericMail($getSalePerson->email, $subject, $message, $person);
        }
        if ($person) {

            jobRosterActions($request->admin_id, 'add_lead', $person->id, 'crm_customers');#CREATED BY

            return response()->json(['success' => true, 'message' => 'Lead created']);
        }else{
            return response()->json(['success' => false, 'message' => 'Fail to add lead!']);
        }
    }
    function updateCustomer(Request $request)
    {
        $person = Customer::where('id', $request->id)->first();
        $old_data = Customer::where('id', $request->id)->first();
        $person->name = $request->name;
        $person->email = $request->email;
        $person->phone = $request->phone;
        $person->other_industry = $request->other_industry;
        $person->address = $request->address;
        $person->postal_code = $request->postal_code;
        $person->suburb = $request->suburb;
        $person->company = $request->company;
        $person->saleperson_id = $request->saleperson_id;
        $person->fax = $request->fax;
        $person->website = $request->website;
        $person->title = $request->title;
        $person->job_title = $request->job_title;
        $person->priority = $request->priority;
        $person->loss_value = $request->loss_value;
        $person->won_status = $request->won_status;
        $person->lead_source = $request->lead_source;
        $person->lead_status = $request->lead_status;
        $person->industry = $request->industry;
        $person->no_emp = $request->no_emp;
        $person->annual_revenue = $request->annual_revenue;
        $person->manual_revenue = $request->manual_revenue;
        $person->rating = $request->rating;
        $person->skype_id = $request->skype_id;
        $person->secondary_email = $request->secondary_email;
        $person->twitter = $request->twitter;
        $person->street = $request->street;
        $person->state = $request->state;
        $person->country = $request->country;
        $person->city = $request->city;
        $person->zip_code = $request->zip_code;
        if(is_int($request->leaad_client_name)){
            $person->leaad_client_name = $request->leaad_client_name;
        }elseif (is_string($request->leaad_client_name)){
            $business = 87;
            if($business != 87){
                $validator = Validator::make(['email' => $request->email], [
                    'email' => 'required|email|unique:customers,email',
                ]);
                if ($validator->fails()) {
                    return response()->json([
                        'message' => 'Customer Email  is already exist. Please try another email.',
                        'success' => false
                    ]);
                }
            }
            $customer = new NormalCustomer();
            $customer->name = $request->leaad_client_name;
            $customer->email = $request->email; 
            $customer->password = Hash::make(123456);
            $customer->phone = $request->phone;
            $customer->city = $request->city;
            $customer->company_name = $request->company_name;
            $customer->status ='active';
            $customer->save();
            $person->leaad_client_name = $customer->id;
        }
        $person->description = $request->description;
        $person->comp_size = $request->comp_size;
        $person->industries = $request->industries;
        $person->abn = $request->abn;
        $person->voucher = $request->voucher;
        $person->sub_company = $request->sub_company;
        $person->no_of_traveler = $request->no_of_traveler;
        $person->actual_revenue = $request->actual_revenue;
        $person->booking_expense = $request->booking_expense;
        $person->assign_operation = $request->assign_operation;
        $person->travel_date = $request->travel_date;
        if($request->lead_status == 'Won'){
            $person->won_by = $request->admin_id;
            $person->created_at = Carbon::now();
        }
        if($request->lead_status == 'Lost Lead'){
            $person->loss_by = $request->admin_id;     
            $person->created_at = Carbon::now();
        }
        if($request->lead_status == 'Contacted'){
            $person->contacted_by = $request->admin_id;     
            $person->created_at = Carbon::now();
        }
        if ($request->has('password') && $request->password != '') {
            $person->password = Hash::make($request->password);
        }
        if ($request->has('lost_lead_reason') && $request->lost_lead_reason != '') {
            $person->lost_lead_reason = $request->lost_lead_reason;
        }
        
        $dirtyAttributes = $person->getDirty();

        $person->save();
        if(isset($request->assign_operation) && !empty($request->assign_operation) && $old_data->assign_operation != $request->assign_operation){
            $getSalePerson = User::find($request->assign_operation);
            $subject = 'You have assigned a new lead!';
            $message = 'You have assigned a new lead please login to your portal and have a look thanks </br> Team TheScouts';
            genericMail($getSalePerson->email, $subject, $message, $person);
            DB::table('crm_notifications')->insert([
                'send_to' => $request->assign_operation,
                'send_by' => $request->admin_id,
                'status' => 'unseen',
            ]);
            $adminName = User::where('id', $request->admin_id)->select('name')->first()->name; 
            event(new CrmNotification($request->assign_operation, $request->admin_id, $adminName));

        }
        if(isset($request->saleperson_id) && !empty($request->saleperson_id) && $old_data->saleperson_id != $request->saleperson_id){
            $getSalePerson = User::find($request->saleperson_id);
            $subject = 'You have assigned a new lead!';
            $message = 'You have assigned a new lead please login to your portal and have a look thanks </br> Team TheScouts';
            genericMail($getSalePerson->email, $subject, $message, $person);
        }
        if ($person) {
            jobRosterActions($request->admin_id, 'update_lead', $person->id, 'crm_customers', $old_data, $dirtyAttributes);
            return response()->json(['success' => true, 'message' => 'Lead updated']);
        }else{
            return response()->json(['success' => false, 'message' => 'Fail to update lead!']);
        }
    }

    function deleteCustomer(Request $request)
    {
        $person = Customer::find($request->id);
        $old_data = $person;
        $person->delete(); 
        if ($person) {
            jobRosterActions($request->admin_id, 'delete_lead', $request->id, 'crm_customers', $old_data);
            return response()->json(['success' => true, 'message' => 'Lead Deleted']);
        }else{
            return response()->json(['success' => false, 'message' => 'Fail to delete lead!']);
        }
    }

    public function getCrmCustomerTimeline(Request $request)
    {
       $model = JobRosterAction::query();
       $activites = $model->where('roster_id', $request->id)->where(function($que){
           $que->orWhere('action_on', 'crm_customers');
       })->get();
       $acts = CrmCustomerTimeLineResource::collection($activites);
       return response()->json(['success' => true, 'data' => $acts]);
   }
   public function getMonthlyData()
   {
        $leads = [];
        $currentDate = Carbon::now();
       // Get the first date of the month for the given date
        $firstDateOfMonth = $currentDate->copy()->startOfMonth();

// Get the last date of the month for the given date
        $lastDateOfMonth = $currentDate->copy()->endOfMonth();
        // Format the dates as needed (e.g., 'Y-m-d' for year-month-day format)
        $firstDateFormatted = $firstDateOfMonth->format('Y-m-d');
        $lastDateFormatted = $lastDateOfMonth->format('Y-m-d');
        $total_leads = Customer::whereBetween('created_at', [$firstDateFormatted, $lastDateFormatted])
        ->get();
        foreach($total_leads as $lead)
        {
            if (isset($leads[$lead->lead_status])) {
                $leads[$lead->lead_status]++;
            }else{
                $leads[$lead->lead_status] = 1;
            }
        }

        $final_leads = [];
        foreach ($leads as $key => $value) {
            $final_leads[] = ['status' => $key != '' ? $key : 'Pending', 'count' => $value];
        }
        return response()->json(['success' => true, 'data' => $final_leads]);
   }
   
   public function getGraphData($value='')
   {
    $createdLeads = [];
    $dealsWon = [];
    $dealsLoss = [];
    $months = [];
    // Get the current date
    $currentDate = Carbon::now();
// subtract 12 months to the current date
    $twelfthMonthDate = $currentDate->subMonths(11);
// Format the date as needed (e.g., 'Y-m-d' for year-month-day format)
    $twelfthMonthDateFormatted = $twelfthMonthDate->format('Y-m-d');

    for ($i=0; $i < 12 ; $i++) { 
        $currentDate = Carbon::parse($twelfthMonthDateFormatted);
        $firstDateOfMonth = $currentDate->copy()->startOfMonth();
        $lastDateOfMonth = $currentDate->copy()->endOfMonth();
        $firstDateFormatted = $firstDateOfMonth->format('Y-m-d');
        $months[] = $firstDateOfMonth->format('M y');
        $lastDateFormatted = $lastDateOfMonth->format('Y-m-d');
        // Find Total Leads
        $total_leads = Customer::whereBetween('created_at', [$firstDateFormatted, $lastDateFormatted])
        ->get();
        $createdLeads[] = count($total_leads);

        // Find Contacted Leads
        $contacted_leads = Customer::where(function ($que){
            // $que->orWhere('lead_status', 'Qualified');
            $que->orWhere('lead_status', 'Contacted');
        })
        ->whereBetween('created_at', [$firstDateFormatted, $lastDateFormatted])
        ->get();
        $dealsContacted[] = count($contacted_leads);
        // Find Won Leads
        $won_leads = Customer::where(function ($que){
            // $que->orWhere('lead_status', 'Qualified');
            $que->orWhere('lead_status', 'Won');
        })
        ->whereBetween('created_at', [$firstDateFormatted, $lastDateFormatted])
        ->get();
        $dealsWon[] = count($won_leads);

        // Find Lost Leads
        $loss_leads = Customer::where(function ($que){
            $que->orWhere('lead_status', 'Lost Lead');
        })
        ->whereBetween('created_at', [$firstDateFormatted, $lastDateFormatted])
        ->get();
        $dealsLoss[] = count($loss_leads);
        $currentDate = Carbon::parse($twelfthMonthDateFormatted);
        $twelfthMonthDate = $currentDate->addMonths(1);
        $twelfthMonthDateFormatted = $twelfthMonthDate->format('Y-m-d');
    }
    return response()->json(['success' => true, 'data' => ['createdLeads' => $createdLeads, 'dealsWon' => $dealsWon, 'dealsLoss' => $dealsLoss, 'dealsContacted' => $dealsContacted, 'months' => $months]]);
}

    public function leadStatusCount(Request $request)
    {
        $monthNo = $request->input('month', Carbon::now()->month);
        $currentMonthStart = Carbon::now()->month($monthNo)->startOfMonth()->toDateString();
        $currentMonthEnd = Carbon::now()->month($monthNo)->endOfMonth()->toDateString();
        if ($request->admin_type == 'super-admin') {
            $query = Customer::whereBetween('created_at', [$currentMonthStart, $currentMonthEnd]);
        
            $lost_leads = $query->clone()->where('lead_status', 'Lost Lead')->count();
            $contacted_leads = $query->clone()->where('lead_status', 'Contacted')->count();
            $won_leads = $query->clone()->where('lead_status', 'Won')->count();
            $all_leads = $query->clone()->whereNotIn('lead_status', ['Lost Lead', 'Contacted', 'Won'])->count();
        } else {
            $query = Customer::whereBetween('created_at', [$currentMonthStart, $currentMonthEnd]);

            $lost_leads = (clone $query)->where('lead_status', 'Lost Lead')
                ->where(function ($leadsQuery) use ($request) {
                    $leadsQuery->where('created_by', $request->admin_id)
                        ->orWhere('saleperson_id', $request->admin_id)
                        ->orWhere('assign_operation', $request->admin_id);
                })
                ->count();

            $contacted_leads = (clone $query)->where('lead_status', 'Contacted')
                ->where(function ($leadsQuery) use ($request) {
                    $leadsQuery->where('created_by', $request->admin_id)
                        ->orWhere('saleperson_id', $request->admin_id)
                        ->orWhere('assign_operation', $request->admin_id);
                })
                ->count();

            $won_leads = (clone $query)->where('lead_status', 'Won')
                ->where(function ($leadsQuery) use ($request) {
                    $leadsQuery->where('created_by', $request->admin_id)
                        ->orWhere('saleperson_id', $request->admin_id)
                        ->orWhere('assign_operation', $request->admin_id);
                })
                ->count();

            $all_leads = (clone $query)->whereNotIn('lead_status', ['Lost Lead', 'Contacted', 'Won'])
                ->where(function ($leadsQuery) use ($request) {
                    $leadsQuery->where('created_by', $request->admin_id)
                        ->orWhere('saleperson_id', $request->admin_id)
                        ->orWhere('assign_operation', $request->admin_id);
                })
                ->count();
        }


        return response()->json([
            'success' => true,
            'code' => 200,
            'data' => [
                'lost_leads' => $lost_leads,
                'contacted_leads' => $contacted_leads,
                'won_leads' => $won_leads,
                'all_leads' => $all_leads
            ],
        ]);
    }
    public function getPieGraphData(Request $request)
    {
        // Get the current month number
        $currentMonthNo = Carbon::now()->month;

        // Calculate the previous month number
        $prevMonthNo = ($currentMonthNo - 1) > 0 ? $currentMonthNo - 1 : 12;

        // Current month date range
        $currentMonthStart = Carbon::now()->startOfMonth()->toDateString();
        $currentMonthEnd = Carbon::now()->endOfMonth()->toDateString();

        // Previous month date range
        $prevMonthStart = Carbon::now()->subMonth()->startOfMonth()->toDateString();
        $prevMonthEnd = Carbon::now()->subMonth()->endOfMonth()->toDateString();

        $currentMonthQuery = Customer::whereBetween('created_at', [$currentMonthStart, $currentMonthEnd])->get();
        $prevMonthQuery = Customer::whereBetween('created_at', [$prevMonthStart, $prevMonthEnd])->get();

        $currentMonthLostLeads = $currentMonthQuery->where('lead_status', 'Lost Lead')->count();
        $currentMonthContactedLeads = $currentMonthQuery->where('lead_status', 'Contacted')->count();
        $currentMonthWonLeads = $currentMonthQuery->where('lead_status', 'Won')->count();
        $currentMonthAllLeads = $currentMonthQuery->count();

        $prevMonthLostLeads = $prevMonthQuery->where('lead_status', 'Lost Lead')->count();
        $prevMonthContactedLeads = $prevMonthQuery->where('lead_status', 'Contacted')->count();
        $prevMonthWonLeads = $prevMonthQuery->where('lead_status', 'Won')->count();
        $prevMonthAllLeads = $prevMonthQuery->count();
        $grossProfit = 0;
        $grossLoss = 0;
        $grossProfitPre = 0;
        $grossLossPre = 0;
        $getWonLeadsPre = DB::table('crm_customers')
            ->whereDate('created_at', '>=', $prevMonthStart)
            ->whereDate('created_at', '<=', $prevMonthEnd)
            ->where('lead_status', 'Won')
            ->get();
        foreach ($getWonLeadsPre as $lead) {
            $sumActual = preg_replace("/[^0-9]/", "", $lead->actual_revenue);
            $sumActual = is_numeric($sumActual) ? (int)$sumActual : 0;
            $getDiff = (int)$sumActual - (int)$lead->booking_expense;
            if ($getDiff > 0) {
                $grossProfitPre += $getDiff;
            } else {
                $grossLossPre += $getDiff;
            }
        }
        $getWonLeads = DB::table('crm_customers')
            ->whereDate('created_at', '>=', $currentMonthStart)
            ->whereDate('created_at', '<=', $currentMonthEnd)
            ->where('lead_status', 'Won')
            ->get();
        foreach ($getWonLeads as $lead) {
            $sumActual = preg_replace("/[^0-9]/", "", $lead->actual_revenue);
            $sumActual = is_numeric($sumActual) ? (int)$sumActual : 0;
            $getDiff = (int)$sumActual - (int)$lead->booking_expense;
            if ($getDiff > 0) {
                $grossProfit += $getDiff;
            } else {
                $grossLoss += $getDiff;
            }
        }
        return response()->json([
            'success' => true,
            'code' => 200,
            'current_month' => [
                'lost_leads' => $currentMonthLostLeads,
                'contacted_leads' => $currentMonthContactedLeads,
                'won_leads' => $currentMonthWonLeads,
                'all_leads' => $currentMonthAllLeads,
                'gross_profit' => ceil($grossProfit)
            ],
            'previous_month' => [
                'lost_leads' => $prevMonthLostLeads,
                'contacted_leads' => $prevMonthContactedLeads,
                'won_leads' => $prevMonthWonLeads,
                'all_leads' => $prevMonthAllLeads,
                'gross_profit' => ceil($grossProfitPre)
            ]
        ]);
    }


    public function getCrmCompanyList(){
        $getCompanyList = Customer::distinct()->pluck('sub_company');
        return response()->json(['success' => true, 'data' => $getCompanyList]);
    }
    public function Totallead(Request $request)
    {
        $monthNo = $request->input('month', Carbon::now()->month);
        $currentMonthStart = Carbon::now()->month($monthNo)->startOfMonth()->toDateString();
        $currentMonthEnd = Carbon::now()->month($monthNo)->endOfMonth()->toDateString();

        $query = Customer::whereBetween('created_at', [$currentMonthStart, $currentMonthEnd]);

        if (isset($request['agent_ids']) && !empty($request['agent_ids'])) {
            $query->whereIn('saleperson_id', $request['agent_ids']);
        }

        $total_leads = $query->get();

        return response()->json([
            'success' => true,
            'code' => 200,
            'data' => [
                'total_leads' => $total_leads,
            ],
        ]);
    }


}



