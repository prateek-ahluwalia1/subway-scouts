<?php

namespace App\Http\Controllers;

use App\Exports\CustomersExport;
use App\Http\Requests\StoreCustomerRequest;
use App\Http\Resources\CustomerNameResource;
use App\Http\Resources\CustomerProfileTrakerResource;
use App\Http\Resources\CustomerUpdateDocumentResource;
use App\Http\Resources\LoginActiviteResource;
use App\Models\ContractorMoreContacts;
use App\Models\Customer;
use App\Models\CustomerDocument;
use App\Models\CustomerInvoice;
use Illuminate\Support\Facades\Mail;
use App\Models\CustomerMoreContact;
use App\Models\Guard;
use App\Models\JobNewRoster;
use App\Models\JobRoster;
use App\Models\JobRosterAction;
use App\Models\RunSheet;
use App\Models\Site;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Validator;
use DB;
use Maatwebsite\Excel\Facades\Excel;

class CustomerController extends Controller
{
    public function store(Request $request)
    {
      $customer = Customer::where('id', $request->id)->first();
      $old_data = $customer;
      $is_check =0;
      if(!$customer){
         $validator = Validator::make(['email' => $request->email], [
            'email' => 'required|email|unique:customers,email',
        ]);
         if ($validator->fails()) {
               $errors = $validator->errors()->all();
               return $errors;
         }
         $customer = new Customer();
         $is_check =1;
        }
        $customer->name = ($request->name ? $request->name : '');
        $customer->email = ($request->email ? $request->email : ''); 
        $customer->password = Hash::make(123456);
        $customer->phone = ($request->phone ? $request->phone : '');
        $customer->address = ($request->address ? $request->address :'');
        $customer->city = ($request->city ? $request->city : ''); 
        $customer->state = ($request->state ? $request->state : ''); 
        $customer->postal_code = ($request->postal_code ? $request->postal_code : '');
        $customer->web_url = ($request->web_url ? $request->web_url : '');
        $customer->status = ($request->status ? $request->status : 'active');
        $customer->save();

        $guards = Guard::select('id')->get();
         if(!empty($guards)){
            foreach ($guards as $key => $value) {
               $guards2 = Guard::where('id', $value->id)->first();
               $tempArray = json_decode($guards2->customer_id, true);
               $tempArray[] = (int) $customer->id;
               $guards2->customer_id = json_encode($tempArray);
               $guards2->update();
         }
      }

        if ($is_check == 1) {
         isCustomerCreate($request->email, '123456');
         jobRosterActions($request->admin_id, 'add_customer', $customer->id, 'customers');
        return response()->json(['message' => "Customer Created" ,  'code' => 200, 'success' => true],200);
        }else{
         jobRosterActions($request->admin_id, 'update_customer', $customer->id, 'customers', $old_data);
         return response()->json(['message' => "Customer Info Updated" ,  'code' => '200', 'success' => true],200);
        }
    }

    public function update(Request $request)
    {
      $customer = Customer::where('id', $request->id)->first();
      $old_data = $customer;
      if(empty($customer)){
         return response()->json(['message' => "Customer Not Found" ,  'code' => '404', 'success' => false],404);
      }else { 
         // https://apis.247staffingsolutions.com.au
        if($request->has('documents')){
         foreach ($request->documents as $key => $document1){
            if($document1['document_name']){
               $customer_documents = CustomerDocument::where('type', $document1['document_name'])->where('customer_id', $request->id)->first();
               $old_data = $customer_documents;
               if(!empty($customer_documents)){
               $image = str_replace(url('')."/"."customer_documents/","",$document1['document']);
               $customer_documents->document = $image;
               $customer_documents->document_no = $document1['document_no'];
               $customer_documents->document_expire = $document1['document_expire'];
               $customer_documents->type = $document1['document_name'];
               $customer_documents->save();
               $Updatedocuments = $customer_documents->getChanges();
               jobRosterActions($request->admin_id, 'update_customer_documents', $customer->id, 'customer_documents', $old_data, $Updatedocuments);
               }else{
                  $customer_documents = new CustomerDocument();
                  $customer_documents->customer_id = $customer->id;
                  $customer_documents->document = $document1['document'];
                  $customer_documents->document_no = $document1['document_no'];
                  $customer_documents->document_expire = $document1['document_expire'];
                  $customer_documents->type = $document1['document_name'];
                  $customer_documents->save();
                  jobRosterActions($request->admin_id, 'add_customer_documents', $customer->id, 'customers');
              }
            }
         }
      }
         if($request->has('more_contacts')){
            foreach ($request->more_contacts as $key => $contact){
            if($contact['more_email'] != '' ){
             if(isset($contact['more_contact_id'])){
               $customer_more_contacts = CustomerMoreContact::where('id', $contact['more_contact_id'])->first();
               $old_data = $customer_more_contacts;
               $customer_more_contacts->more_email = $contact['more_email']; 
               $customer_more_contacts->more_phone = $contact['more_phone']; 
               $customer_more_contacts->more_notes = $contact['more_notes'];
               $customer_more_contacts->save();
               $Updatecontacts = $customer_more_contacts->getChanges();
               jobRosterActions($request->admin_id, 'update_customer_more_contact', $customer->id, 'customer_more_contacts',$old_data, $Updatecontacts);
            }else{
               $customer_more_contacts = new CustomerMoreContact();
               $customer_more_contacts->customer_id = $customer->id;
               $customer_more_contacts->more_email = $contact['more_email']; 
               $customer_more_contacts->more_phone = $contact['more_phone']; 
               $customer_more_contacts->more_notes = $contact['more_notes'];
               $customer_more_contacts->save();
               jobRosterActions($request->admin_id, 'add_customer_more_contact', $customer->id, 'customer_more_contacts');
            }

            }
         }
       }
       $customer->job_level = $request->job_level;
       $customer->charged_rates_id = $request->chargerate;
       $customer->apply_date = $request->charge_rate_apply_date;
       $customer->state = ($request->state ? $request->state : '');
       $customer->save();
       $Updatecustomer = $customer->getChanges();
       jobRosterActions($request->admin_id, 'update_customer', $customer->id, 'customers', $old_data, $Updatecustomer);
       return response()->json(['message' => "Customer Info Updated",  'code' => 200, 'success' => true],200);
      }
    }

    public function deleteCustomer(Request $request)
    {
      $customer = Customer::where('id', $request->id)->first();
      $old_data = $customer;
      if(!empty($customer)){

          $removeCustomerFromStaff = Guard::whereJsonContains('customer_id', [$request->id])->get();
                     foreach ($removeCustomerFromStaff as $model) {
                $tempArray = json_decode($model->customer_id, true);
            
                if (($key = array_search($request->id, $tempArray)) !== false) {
                    unset($tempArray[$key]);
                    $model->customer_id = json_encode($tempArray);
                    $model->save();
                }
            }
         isCustomerDelete($customer->email, 'Your account with this email'.' '.$customer->email.' '.'Deleted !' );
         DB::table('customer_more_contacts')->where('customer_id', $request->id)->delete();
         $customer->delete();
         jobRosterActions($request->admin_id, 'delete_customer', $customer->id, 'customers', $old_data);
         return response()->json(['message' => "Customer Deleted" ,  'code' => '200', 'success' => true],200);
      }else{
         return response()->json(['message' => "Customer Not Found" ,  'code' => 404, 'success' => false],404);
      }
   }

   public function getCustomers(Request $request)
   {
      $query = Customer::query();
      if($request->has('status') && !empty($request->status)){
         $query = $query->where('status', $request->status)->select('id', 'name', 'email', 'status', 'address', 'city', 'phone')->orderBy('name', 'asc')->get();
      }else{
         $query = $query->select('id', 'name', 'email', 'status', 'address', 'city', 'phone')->orderBy('name', 'asc')->get();
      }
      $customers = $query;
      $active_customers = Customer::where('status', 'active')->count();
      $inactive_customers = Customer::where('status', 'inactive')->count();
      return response()->json([ 'success' => true,'data' => $customers, 'code' => 200, 'active_customers' => $active_customers, 'inactive_customers' => $inactive_customers ]);
   }


   public function activeCustomerStatus(Request $request)
   {
      $customer = Customer::where('id', $request->id)->first();
      $old_data = Customer::where('id', $request->id)->first();
      if($customer->status == "inactive"){

         $customer->status = 'active';
         $customer->update();
         $CustomerStatusUpdate = $customer->getChanges();
         jobRosterActions($request->admin_id, 'active_customer', $customer->id, 'customers', $old_data, $CustomerStatusUpdate);
         return response()->json(['success' => true, 'msg' => "Customer status Active"]);
      }elseif($customer->status == "active"){
         $customer->status = 'inactive';
         $customer->update();
         $CustomerStatusUpdate = $customer->getChanges();
         jobRosterActions($request->admin_id, 'inactive_customer', $customer->id, 'customers', $old_data, $CustomerStatusUpdate);
         return response()->json(['success' => true, 'msg' => "Customer Status Inactive"]);
      }else{
         return response()->json(['success' => false, 'msg' => "Customer not found!"]);
      }
   }
   
   public function getCustomerById(Request $request)
   {
      $customer = Customer::where('id', $request->id)->with(['moreContacts','otherDocuments', 'sites'])->first();
      $cus = (new CustomerUpdateDocumentResource($customer));
      $sites = Site::where('customer_id', $request->id)->select('id')->get();
      $sites_count  = $sites->count();
      $shift_count = JobRoster::whereIn('site_id', $sites)->count();
      return response()->json(['success' => true, 'data' => $cus, 'sites_count' => $sites_count, 'shift_count' => $shift_count]);
   }

   public function getSitesByCustomers(Request $request)
   {
     if (is_array($request->customers)) {
        $sites = Site::whereIn('customer_id', $request->customers)->where('site_status', 'active')->select('id', 'site_name', 'address', 'coordinates', 'state')->orderBy('site_name')->get();
     }else{
        $today = Carbon::now()->format('Y-m-d');
       $jobRostersActiveSites = JobRoster::whereDate('start', $today)->distinct()->pluck('site_id');
       $sites = Site::where('customer_id', $request->customers)->whereIn('id', $jobRostersActiveSites)->orderBy('site_name')->select('id', 'site_name', 'address', 'coordinates', 'state')->orderBy('site_name')->get();
     }
      return response()->json([ 'success' => true,'data' => $sites, 'code' => 200 ]);
   }


    public function getRunSheetByCustomers(Request $request)
    {
      if (is_array($request->customers)) {
         $sites = RunSheet::whereIn('customer_id', $request->customers)->select('id', 'title as site_name', 'description')->get();
      }else{
         $sites = RunSheet::whereJsonContains('customer_id', $request->customers)->select('id', 'title as site_name', 'description')->get();
      }
       return response()->json([ 'success' => true,'data' => $sites, 'code' => 200 ]);
    }

    public function deleteCustomerMoreContact(Request $request)
    {
      $customerMoreContact = CustomerMoreContact::where('id', $request->id)->first();
      $old_data = $customerMoreContact;
      if(!empty($customerMoreContact)){
         $customerMoreContact->delete();
         //logging('delete', $request->id, 'login_user', 'customer_more_contact');
         jobRosterActions($request->admin_id, 'delete_customer_more_contact', $customerMoreContact->id, 'customer_more_contacts', $old_data);
         return response()->json(['message' => "Delete Customer Contact Successfully" ,  'code' => '200', 'success' => true],200);
      }else{
         return response()->json(['message' => "Customer Contact Not Found" ,  'code' => 404, 'success' => false],404);
      }
    }

    public function getCustomersName()
    {
      // $customers = Customer::select('id', 'name')->where('status', 'active')->orderBy('name', 'asc')->get();

     $customers = Customer::select(
        'customers.id',
        'customers.name',
        'customers.phone',
        'customers.email',
        'customer_documents.document_no'
      )
      ->where('customers.status', 'active')
      ->orderBy('customers.name', 'asc')
      ->leftJoin('customer_documents', function($join) {
         $join->on('customers.id', '=', 'customer_documents.customer_id')
               ->where('customer_documents.type', 'business');
      })
      ->where('customers.status', 'active')
      ->get();

      
      $cus = CustomerNameResource::collection($customers);
      return response()->json(['success' => true, 'data' => $cus]);
    }

    public function getCustomersNameTimesheet(Request $request)
    {
      if($request->type == 'customer'){
         $customers = Customer::find($request->id)->where('status', 'active')->select('id', 'name')->orderBy('name', 'asc')->get();
         $cus = CustomerNameResource::collection($customers);
         return response()->json(['success' => true, 'data' => $cus]);
      }else{
         $customers = Customer::select('id', 'name')->where('status', 'active')->orderBy('name', 'asc')->get();
         $cus = CustomerNameResource::collection($customers);
         return response()->json(['success' => true, 'data' => $cus]);

      }
    }



    public function getCustomerProfileTraker(Request $request)
    {
        $model = JobRosterAction::query();
        $activites = $model->where('roster_id', $request->id)->where(function($que){
         $que->orWhere('action_on', 'customer_more_contacts');
         $que->orWhere('action_on', 'customer_documents');
         $que->orWhere('action_on', 'customers');
        })->latest()->get();
        $acts = CustomerProfileTrakerResource::collection($activites);
        return response()->json(['success' => true, 'data' => $acts]);
    }
   public function generateCustomersExcel(){
      $data = $this->getReportData();
      $filename = time().'_customers_report.xlsx';  
      Excel::store(new CustomersExport, 'excel/customers/'.$filename, 'excels');
      return response()->json(['success' =>  true,'message' => 'Customers Excel generate successfully.','path' => 'https://'.request()->getHttpHost().'/excel/customers/'.$filename]);
   }
   public function getReportData(){
      return DB::table('customers')->select('id', 'name', 'email', 'phone', 'address')->get();
   }

}
