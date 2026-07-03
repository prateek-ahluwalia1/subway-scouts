<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreContractorRequest;
use App\Http\Resources\ContractorNameResource;
use App\Http\Resources\ContractorProfileTrakerResource;
use App\Http\Resources\ContractorUpdateDocumentResource;
use App\Http\Resources\CustomerUpdateDocumentResource;
use App\Models\Contractor;
use App\Models\ContractorDocument;
use App\Models\ContractorMoreContacts;
use App\Models\JobRosterAction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class ContractorController extends Controller
{
    public function store(Request $request)
    {
      $contractor = Contractor::where('id', $request->id)->first();
      $old_data = $contractor;
      $is_check =0;
      if(!$contractor){
         $validator = Validator::make(['email' => $request->email], [
            'email' => 'required|email|unique:contractors,email',
        ]);
         if ($validator->fails()) {
               $errors = $validator->errors()->all();
               return $errors;
         }
         $contractor = new Contractor();
         $is_check =1;
        }
        $contractor->name = ($request->name ? $request->name : '');
        $contractor->email = ($request->email ? $request->email : ''); 
        $contractor->password = Hash::make(123456);
        $contractor->phone = ($request->phone ? $request->phone : '');
        $contractor->address = ($request->address ? $request->address :'');
        $contractor->city = ($request->city ? $request->city : ''); 
        $contractor->state = ($request->state ? $request->state : ''); 
        $contractor->postal_code = ($request->postal_code ? $request->postal_code : '');
        //$contractor->web_url = ($request->web_url ? $request->web_url : '');
        $contractor->status = ($request->status ? $request->status : 'active');
        $contractor->save();
        if ($is_check == 1) {
         isContractorCreate($request->email, '123456');
         jobRosterActions($request->admin_id, 'add_customer', $contractor->id, 'contractors');
        return response()->json(['message' => "Contractor Created" ,  'code' => 200, 'success' => true],200);
        }else{
         jobRosterActions($request->admin_id, 'update_customer', $contractor->id, 'contractors', $old_data);
         return response()->json(['message' => "Contractor Updated" ,  'code' => '200', 'success' => true],200);
        }
    }

    public function update(Request $request)
    {
        $contractor = Contractor::where('id', $request->id)->first();
        $old_data = $contractor; 
      if(empty($contractor)){
         return response()->json(['message' => "Contractor Not Found" ,  'code' => '404', 'success' => true],404);
        }else{
        $contractor->name = $request->name;
        //$contractor->email = $request->email;
        $contractor->password = Hash::make(123456);
      // $contractor->phone = $request->phone;
      // $contractor->address = $request->address;
        $contractor->city = $request->city;
        $contractor->state = $request->state; 
        $contractor->postal_code = $request->postal_code; 
        $contractor->job_level = $request->job_level;
        $contractor->charged_rates_id = $request->chargerate;
        $contractor->apply_date = $request->charge_rate_apply_date;
      //   $contractor->status = $request->status;

   if($request->has('documents') && !empty($request->documents)){
      foreach ($request->documents as $key => $document1){
         if($document1['document_name']){
            $contractor_documents = ContractorDocument::where('type', $document1['document_name'])->where('contractor_id', $request->id)->first();
            $old_data = $contractor_documents;
            if(!empty($contractor_documents)){
            $image = str_replace(url('')."/"."contractor_documents/","",$document1['document']);
            $contractor_documents->document = $image;
            $contractor_documents->document_no = $document1['document_no'];
            $contractor_documents->document_expire = $document1['document_expire'];
            $contractor_documents->type = $document1['document_name'];
            // $contractor_documents->side = $document1['side'];
            $contractor_documents->save();
            $updatecontractor = $contractor_documents->getChanges();
            jobRosterActions($request->admin_id, 'update_contractor_contact', $contractor->id, 'contractor_documents', $old_data, $updatecontractor);
            }else{
               $contractor_documents = new ContractorDocument();
               $contractor_documents->contractor_id = $contractor->id;
               $contractor_documents->document = $document1['document'];
               $contractor_documents->document_no = $document1['document_no'];
               $contractor_documents->document_expire = $document1['document_expire'];
               $contractor_documents->type = $document1['document_name'];
               $contractor_documents->save();
               jobRosterActions($request->admin_id, 'add_contractor_contact', $contractor->id, 'contractor_documents');
           }
         }
      }
   }
  

   if($request->has('more_contacts') && !empty($request->more_contacts)){
      foreach ($request->more_contacts as $key => $contact){
      if($contact['more_email'] != '' ){
       if(isset($contact['more_contact_id'])){
         $contractor_more_contacts = ContractorMoreContacts::where('id', $contact['more_contact_id'])->first();
         $old_data = $contractor_more_contacts;
         $contractor_more_contacts->more_email = $contact['more_email']; 
         $contractor_more_contacts->more_phone = $contact['more_phone']; 
         $contractor_more_contacts->more_notes = $contact['more_notes'];
         $contractor_more_contacts->save();
         $updatecontractorcontacts = $contractor_more_contacts->getChanges();
         jobRosterActions($request->admin_id, 'update_contractor_more_contact', $contractor->id, 'contractor_more_contacts', $old_data, $updatecontractorcontacts);
      }else{
         $contractor_more_contacts = new ContractorMoreContacts();
         $contractor_more_contacts->contractor_id = $contractor->id;
         $contractor_more_contacts->more_email = $contact['more_email']; 
         $contractor_more_contacts->more_phone = $contact['more_phone']; 
         $contractor_more_contacts->more_notes = $contact['more_notes'];
         $contractor_more_contacts->save();
         jobRosterActions($request->admin_id, 'add_contractor_more_contact', $contractor->id, 'contractor_more_contacts');
      }

      }
   }
}
        $contractor->save();
        $updatecontractor = $contractor->getChanges();
        jobRosterActions($request->admin_id, 'update_contractor', $contractor->id, 'contractors', $old_data, $updatecontractor);
        return response()->json(['message' => "Contractor Updated" ,  'code' => '200', 'success' => true],200);
      }
    }


    public function deleteContractor(Request $request)
      {
      $contractor = Contractor::where('id', $request->id)->first();
      $old_data = $contractor; 
      if(!empty($contractor)){
         isContractorDelete($contractor->email, 'Your account with this email'.' '.$contractor->email.' '.'Deleted !');
        $contractor->delete();
         jobRosterActions($request->admin_id, 'delete_contractor', $contractor->id, 'contractors', $old_data);
         return response()->json(['message' => "Contractor Deleted" ,  'code' => '200', 'success' => true],200);
      }else{
         return response()->json(['message' => "Contractor Not Found" ,  'code' => '404', 'success' => true],404);
      }
   }

   public function getContractors(Request $request)
   {
      $query = Contractor::query();
      if($request->has('status') && !empty($request->status)){
         $query = $query->where('status', $request->status)->select('id', 'name', 'email', 'status', 'address')->orderBy('name', 'asc')->get();
      }else{
         $query = $query->select('id', 'name', 'email', 'status', 'address')->orderBy('name', 'asc')->get();
      }
      $contractors = $query;
      $active_contractors = Contractor::where('status', 'active')->count();
      $inactive_contractors = Contractor::where('status', 'inactive')->count();
      return response()->json([ 'success' => true,'data' => $contractors, 'code' => 200, 'active_customers' => $active_contractors, 'inactive_customers' => $inactive_contractors ]);
   }


   public function getContractorById(Request $request)
   {
      $contractor = Contractor::where('id', $request->id)->with(['moreContacts','otherDocuments', 'sites'])->first();
      $con = (new ContractorUpdateDocumentResource($contractor));
      return response()->json([ 'success' => true,'data' => $con, 'code' => 200 ]);
   }

   public function getContractorsName()
    {
      $contractors = Contractor::all();
      $con = ContractorNameResource::collection($contractors);
      return response()->json(['success' => true, 'data' => $con]);
    }

    public function getContractorProfileTraker(Request $request)
    {
        $model = JobRosterAction::query();
        $activites = $model->where('roster_id', $request->id)->where(function($que){
         $que->orWhere('action_on', 'contractor_more_contacts');
         $que->orWhere('action_on', 'contractor_documents');
         $que->orWhere('action_on', 'contractors');
        })->orderBy('name', 'asc')->get();
        $acts = ContractorProfileTrakerResource::collection($activites);
        return response()->json(['success' => true, 'data' => $acts]);
    }

    public function deleteContractorMoreContact(Request $request)
    {
      $contractorMoreContact = ContractorMoreContacts::where('id', $request->id)->first();
      $old_data = $contractorMoreContact;
      if(!empty($contractorMoreContact)){
         $contractorMoreContact->delete();
         jobRosterActions($request->admin_id, 'delete_customer_more_contact', $contractorMoreContact->id, 'customer_more_contacts', $old_data);
         return response()->json(['message' => "Delete Contractor Contact Successfully" ,  'code' => '200', 'success' => true],200);
      }else{
         return response()->json(['message' => "Contractor Contact Not Found" ,  'code' => 404, 'success' => false],404);
      }
    }


    public function activeContractorStatus(Request $request)
    {
       $contractor = Contractor::where('id', $request->id)->first();
       $old_data = Contractor::where('id', $request->id)->first();
       if($contractor->status == "inactive"){
          $contractor->status = 'active';
          $contractor->update();
          $contractorstatue = $contractor->getChanges();
          jobRosterActions($request->admin_id, 'active_customer', $contractor->id, 'customers', $old_data);
          return response()->json(['success' => true, 'msg' => "Contractor status Active"]);
       }elseif($contractor->status == "active"){
          $contractor->status = 'inactive';
          $contractor->update();
          $contractorstatue = $contractor->getChanges();
          jobRosterActions($request->admin_id, 'inactive_customer', $contractor->id, 'customers', $old_data);
          return response()->json(['success' => true, 'msg' => "Contractor Status Inactive"]);
       }else{
          return response()->json(['success' => false, 'msg' => "Contractor not found!"]);
       }
    }
}
