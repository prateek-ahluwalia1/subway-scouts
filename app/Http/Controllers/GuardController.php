<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreGuardRequest;
use App\Http\Resources\AllGuardResource;
use App\Http\Resources\ContractorFileNameResource;
use App\Http\Resources\CustomerFileNameResource;
use App\Models\portal\PortalSettings;
use App\Http\Resources\GetAllReqGuardDocuments;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\GuardImport;
use App\Imports\PayrateImport;
use Illuminate\Support\Facades\Log;
use App\Imports\LocationImport;
use App\Http\Resources\EditGuardDocumentResource;
use App\Http\Resources\EditGuardEmploymentResource;
use App\Http\Resources\EditGuardResource;
use App\Http\Resources\EditTrainedGuardResource;
use App\Http\Resources\FilterGuardLicenseResource;
use App\Http\Resources\GaurdUniFormDetailsResource;
use App\Http\Resources\GetAllGuardDocuments;
use App\Http\Resources\GetDocumentTypeResource;
use App\Http\Resources\GetDocumentComplianceResource;
use App\Http\Resources\GetGuardBySiteResource;
use App\Http\Resources\GetGuardSitesResource;
use App\Http\Resources\GuardBeforeAndAfterWeekShiftsResource;
use App\Http\Resources\GuardEmergencyContactResource;
use App\Http\Resources\GuardEmpContractorDetailsResource;
use App\Http\Resources\GuardFileNameResource;
use App\Http\Resources\GuardInternalAndExternalIdsResource;
use App\Http\Resources\guardLeavesResource;
use App\Http\Resources\GuardPersonalRefrenceResource;
use App\Http\Resources\GuardProfileTrakerResource;
use App\Http\Resources\GuardRefrenceResource;
use App\Http\Resources\NextSevenResource;
use App\Http\Resources\PerviousSevenResource;
use App\Http\Resources\ShowGuardFeedBackResource;
use App\Http\Resources\StaffDataResource;
use App\Http\Resources\StaffStatusActivityResource;
use App\Http\Resources\TwoWeaksGuardShiftResource;
use App\Http\Resources\VisaDetailResource;
use App\Mail\GuardEmailVerifay;
use App\Models\BuildInFormHistory;
use App\Models\ContractorDocument;
use App\Models\CustomerDocument;
use App\Models\DocumentCategory;
use App\Models\EmploymentPackChecklist;
use App\Models\Guard;
use App\Models\GuardDocument;
use App\Models\GuardEmergencyContact;
use App\Models\GuardFeedBack;
use App\Models\GuardInduction;
use App\Models\GuardInternalAndExternalIds;
use App\Models\GuardLeave;
use App\Models\GuardPersonalRefrence;
use App\Models\GuardRefrence;
use App\Models\GuardTrainedSite;
use App\Models\GuardWorkDetail;
use App\Models\JobRoster;
use App\Models\JobRosterAction;
use App\Models\Site;
use App\Models\Contractor;
use App\Models\SiteGuard;
use App\Models\StaffContractorDetail;
use App\Models\StaffUniform;
use App\Models\User;
use App\Models\Questionnaire;
use App\Models\VisaDetails;
use GrahamCampbell\ResultType\Success;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Validator;
use Dompdf\Dompdf;
use App\Models\RunSheet;
use App\Models\RunSheetJobRoster;
use Illuminate\Support\Facades\File;
use Dompdf\Options;
use ZipArchive;
use App\Models\GuardPayslip;
use DateInterval;
use DatePeriod;
use DateTime;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;
use PragmaRX\Google2FA\Google2FA;
use Smalot\PdfParser\Parser;
use setasign\Fpdi\Fpdi;



class GuardController extends Controller
{
   public function guardEmailVerifay($email, $otp, $pass)
   {
    $guard = Guard::where('email', $email)->first();
    if($guard){
      $otp = Str::random(4);
      $guard->email_varifay_otp = $otp;
      $guard->save();
      isEmailVarifay($guard->email, $otp, $pass);
   }
}


public function againGuardEmailVerify(Request $request)
   {
    $guard = Guard::where('email', $request->email)->first();
    if($guard){
      $otp = Str::random(4);
      $guard->email_varifay_otp = $otp;
      $guard->save();
      isEmailVarifay($guard->email, $otp, $pass='ABCD@123');
      return response()->json([ 'success' => true, 'message' => 'Email have been send please check inbox and spam' ]);   
   }
}

public function quickOnboardingStaff(Request $request)
{
     $validator = Validator::make($request->all(), [
       
        'email' => 'required|email|unique:guards,email',
    ], [
        'email.unique' => 'This email address is already taken. Please use a different email.',
    ]);

    if ($validator->fails()) {
      $firstErrorMessage = $validator->errors()->first();
      
      return response()->json([
         'success' => false,
         'message' => $firstErrorMessage,
         'code' => 200
      ], 200);
   }

   $customers = fetchCustoemrs();
   
   $quickOnboardingStaff = new Guard();
   $quickOnboardingStaff->first_name = $request->first_name;
   $quickOnboardingStaff->middle_name = $request->middle_name;
   $quickOnboardingStaff->last_name = $request->last_name;
   $quickOnboardingStaff->email = $request->email;
   $quickOnboardingStaff->password = Hash::make($request);
   $quickOnboardingStaff->phone = $request->phone;
   $quickOnboardingStaff->guard_type = $request->guard_type;
   // $quickOnboardingStaff->staff_type = $request->staff_type;
   $quickOnboardingStaff->state = $request->state;
   $quickOnboardingStaff->profile_completion = $request->profile_completion;
   $quickOnboardingStaff->site_id = json_encode(array());
   $quickOnboardingStaff->run_sheet_id = json_encode(array());
   $quickOnboardingStaff->guard_status = 'active';
   $quickOnboardingStaff->name = $request->first_name.' '. ($request->middle_name ? $request->middle_name.' ' :'') .$request->last_name;
   //$quickOnboardingStaff->training = ($request->has('training')&& $request->training == 'on' ? 1 : 0);
   $quickOnboardingStaff->guard_postion = $request->guard_postion;
   $quickOnboardingStaff->joining_date = isset($request->joining_date) ? dbFormate($request->joining_date) : null;
   $quickOnboardingStaff->customer_id =  json_encode($customers);
   $quickOnboardingStaff->save();
   $getStaff = Guard::find($quickOnboardingStaff->id);
   if ($quickOnboardingStaff->id >= 10 && $quickOnboardingStaff->id <= 99) {
      $uniqueNum = '0'.$quickOnboardingStaff->id;
   }else if($quickOnboardingStaff->id >= 0 && $quickOnboardingStaff->id <= 9){
      $uniqueNum = '00'.$quickOnboardingStaff->id;
   }else{
      $uniqueNum = $quickOnboardingStaff->id;
   }
   $getBusinessName = "Subway";
   $getStaff->internal_id = $getBusinessName.'-'.$uniqueNum;
   $getStaff->update();

   // $inductions = Questionnaire::all();

   $now = Carbon::now();
   // $inductionHistoryData = [];
   // $guardQuestionnaireDetailsData = [];

   // foreach ($inductions as $induction) {
   //    $inductionHistoryData[] = [
   //       'guard_id' => $quickOnboardingStaff->id,
   //       'induction_id' => $induction->id,
   //       'state' => "Victoria",
   //       'read_status' => 0,
   //       'created_at' => $now,
   //       'updated_at' => $now,
   //    ];

   //    $guardQuestionnaireDetailsData[] = [
   //       'guard_id' => $quickOnboardingStaff->id,
   //       'questionnaire_id' => $induction->id,
   //       'marks' => 0,
   //       'certificate_path' => null,
   //       'expiry_date' => null,
   //       'created_at' => $now,
   //       'updated_at' => $now,
   //    ];
   // }
   
   // DB::table('induction_history')->insert($inductionHistoryData);
   // DB::table('guard_questionnaire_details')->insert($guardQuestionnaireDetailsData);

   jobRosterActions($request->admin_id, 'add_staff_quick_onboarding', $quickOnboardingStaff->id, 'Staff');
   // $this->guardEmailVerifay($request->email, $request->header('Business-Id'), $request->password);
   return response()->json(['message' => "Quick Onboarding is sent to the newly created staff!" ,  'code' => 200, 'success' => true]);
}

public function createNewStaff(Request $request)
{
   $validator = Validator::make($request->all(), [
       
        'email' => 'required|email|unique:guards,email',
    ], [
        'email.unique' => 'This email address is already taken. Please use a different email.',
    ]);

    if ($validator->fails()) {
      $firstErrorMessage = $validator->errors()->first();
      
      return response()->json([
         'success' => false,
         'message' => $firstErrorMessage,
         'code' => 200
      ], 200);
   }

   $customers = fetchCustoemrs();

   $cords = explode(",",$request->coordinates);
   $lat = $cords[0];
   $lng = $cords[1];
   $createNewStaff = new Guard();
   $createNewStaff->first_name = $request->first_name;
   $createNewStaff->middle_name = $request->middle_name;
   $createNewStaff->last_name = $request->last_name;
   $createNewStaff->name = $request->first_name.' '. ($request->middle_name ? $request->middle_name.' ' :'') .$request->last_name;
   $createNewStaff->email = $request->email;
   $createNewStaff->phone = $request->phone;
   $createNewStaff->password = Hash::make($request);
   $createNewStaff->address = $request->address;
   $createNewStaff->profile_image = $request->profile_image;
   $createNewStaff->guard_type = $request->guard_type;
   $createNewStaff->staff_type = $request->staff_type;
   //$createNewStaff->training = ($request->has('training')&& $request->training == 'on' ? 1 : 0);
   $createNewStaff->state = $request->state;
   $createNewStaff->position = $request->position;
   $createNewStaff->dob = $request->dob;
   $createNewStaff->gender =  $request->gender;
   $createNewStaff->country =  $request->home_country;
   $createNewStaff->suburb =  $request->suburb;
   $createNewStaff->city =  $request->city;
   $createNewStaff->coordinates = $request->coordinates;
   $createNewStaff->latitude =  $lat;
   $createNewStaff->longitude =  $lng;
   $createNewStaff->postal_code =  $request->postal_code;
   $createNewStaff->emergency_contact_name =  $request->emergency_contact_name;
   $createNewStaff->emergency_contact_relation = $request->emergency_contact_relation;
   $createNewStaff->emergency_contact_phone =  $request->emergency_contact_phone;
   $createNewStaff->emergency_contact_email = $request->emergency_contact_email;
   $createNewStaff->customer_id =  json_encode($customers);
   $createNewStaff->contractor_id =  json_encode($request->contractor_id);
   $createNewStaff->profile_completion =  $request->profile_completion;
   $createNewStaff->site_id = json_encode(array());
   $createNewStaff->run_sheet_id = json_encode(array());
   $createNewStaff->guard_status = 'new';
   $createNewStaff->guard_postion = $request->guard_postion;
   $createNewStaff->customer_id = json_encode(fetchCustoemrs());
   $createNewStaff->joining_date = $request->joining_date;
   $createNewStaff->annual_leave_hours = $request->annual_leave;
   $createNewStaff->sick_leave_hours = $request->sick_leave;
   $createNewStaff->save();
   if(isset($request->guard_type) && $request->guard_type == 'direct'){
      $getStaff = Guard::find($createNewStaff->id);
      if ($createNewStaff->id >= 10 && $createNewStaff->id <= 99) {
         $uniqueNum = '0'.$createNewStaff->id;
      }else if($createNewStaff->id >= 0 && $createNewStaff->id <= 9){
         $uniqueNum = '00'.$createNewStaff->id;
      }else{
         $uniqueNum = $createNewStaff->id;
      }
      $getBusinessName = "AMG";
      $getStaff->internal_id = $getBusinessName.'-'.$uniqueNum;
      $getStaff->update();
   }else if(isset($request->guard_type) && $request->guard_type == 'contractor'){
      $getStaff = Guard::find($createNewStaff->id);
      if ($createNewStaff->id >= 10 && $createNewStaff->id <= 99) {
         $uniqueNum = '0'.$createNewStaff->id;
      }else if($createNewStaff->id >= 0 && $createNewStaff->id <= 9){
         $uniqueNum = '00'.$createNewStaff->id;
      }else{
         $uniqueNum = $createNewStaff->id;
      }
      $connectionName = 'mysql2';
      $getBusinessName = DB::connection($connectionName)
         ->table('business_data')->where('id', $request->header('Business-Id'))
         ->select('id','sub_title')
         ->first();
      if(isset($request->contractor_id)){
         $getContractor = Contractor::where('id', $request->contractor_id)->select('id', 'name')->first();
      }else{
         $getContractor = null;
      }
      $getStaff->internal_id = $getBusinessName->sub_title.'-'.$uniqueNum.'-'.$getContractor->name;
      $getStaff->update();
   }
   $this->guardEmailVerifay($request->email, $request->header('Business-Id'), $request->password);
    
      $inductions = Questionnaire::all();

      $now = Carbon::now();
      $inductionHistoryData = [];
      $guardQuestionnaireDetailsData = [];

      foreach ($inductions as $induction) {
         $inductionHistoryData[] = [
            'guard_id' => $createNewStaff->id,
            'induction_id' => $induction->id,
            'state' => "Victoria",
            'read_status' => 0,
            'created_at' => $now,
            'updated_at' => $now,
         ];

         $guardQuestionnaireDetailsData[] = [
            'guard_id' => $createNewStaff->id,
            'questionnaire_id' => $induction->id,
            'marks' => 0,
            'expiry_date' => null,
            'certificate_path' => null,
            'created_at' => $now,
            'updated_at' => $now,
         ];
      }
      

      DB::table('induction_history')->insert($inductionHistoryData);
      DB::table('guard_questionnaire_details')->insert($guardQuestionnaireDetailsData);

   jobRosterActions($request->admin_id, 'add_new_staff', $createNewStaff->id, 'Staff');
   return response()->json(['message' => "Staff Store Successfully Please Verify Your Email" ,  'code' => 200, 'success' => true]);
}

public function getAllGuards(Request $request)
{
   $limit = 10;
   $offset = 0;
   if($request->has('pageIndex') && $request->has('pageSize'))
   {
      $offset = $request->pageIndex * $request->pageSize;
      $limit = $request->pageSize;
   }

   $query = Guard::with(['guardFeedback']);
   //  ->leftJoin('guards_documents', function ($join) {
   //      $join->on('guards.id', '=', 'guards_documents.guard_id');
   //  });

   // if($request->has('document_type') && !empty($request->document_type)){
   //    $document_type = $request->document_type;
   //    $query = $query->with(['guardDocuments' => function($que) use ($document_type){
   //       $que->where('document_type', $document_type);
   //    }]);
   // }
//    if ($request->has('residence') && $request->residence == 'citizen') {
//       $query->whereDoesntHave('guardDocuments', function ($que) {
//           $que->where('document_category', 'citizen');
//       });
//   }

   if ($request->has('guard_status') && $request->guard_status == 'active') {
      $query->where('guard_status', 'active')->where('admin_approval_status', 'active');
   }
   if ($request->has('guard_status') && $request->guard_status == 'inactive') {
      $query->where('guard_status', 'inactive');
   }
   //allow all guards
   // if ($request->has('customers') && !empty($request->customers)) {
   //    $query->whereJsonContains('customer_id', $request->customers);
   // }

   $total = $query->count();

   $guards = $query->skip($offset)->take($limit)->orderBy('first_name', 'asc')->get();

   $guardz = AllGuardResource::collection($guards);

   $active_guard_count = Guard::where('guard_status', 'active')->where('admin_approval_status', 'active')->count();
   $inactive_guard_count = Guard::where('guard_status', 'inactive')->count();
   // $new_guard_count = Guard::where('guard_status', 'new')
   //  ->orWhere('guard_status', 'document_exp')
   //  ->count();
   // $deleted_guard_count = Guard::where('guard_status', 'deleted')->count();

   return response()->json(['success' => true, 'data' => $guardz, 'active_guard_count' => $active_guard_count, 'inactive_guard_count'=> $inactive_guard_count
      ,'length' => $total, 'pageIndex' => $request->pageIndex, 'pageSize' => $request->pageSize]);
   }

   public function getAllCustomerGuards(Request $request)
   {   
      $guardz = Guard::select('id', 'first_name', 'middle_name', 'last_name','name', 'state','customer_id');
     
     if (isset($request->state) && is_array($request->state)) {
         $guardz->whereIn('state', $request->state);
     }
     elseif(isset($request->state) && !empty($request->state)){
      $guardz->where('state', $request->state);
     }
   
      $guardz = $guardz->where('guard_status', 'active')->orderBy('first_name', 'asc')->get();
      
      $guardz = AllGuardResource::collection($guardz);
      if (count($guardz) > 0) {
         return response()->json(['success' => true, 'data' => $guardz]);
      }
      return response()->json(['success' => false, 'data' => $guardz]);
   }


public function getActiveGuards() {
   $active_guards = Guard::where(function($q){
      // $q->where('is_available', 'yes');
      $q->where('guard_status', 'active');
      // $q->where('admin_approval_status', 'active');
   })->select('id', 'first_name', 'middle_name', 'last_name')->orderBy('first_name', 'asc')->get();
   return response()->json(['success' => true, 'data' => $active_guards]);
}

public function getGuardSites(Request $request)
{
 $guard = Guard::where('id', $request->id)->first();
 if($guard){
   $customers = json_decode($guard->customer_id);
   $sites = json_decode($guard->site_id);
   if(!empty($customers) || !empty($sites)){
      $query = Site::query();
      if(!empty($customers))
      {
         $query->whereIn('customer_id', $customers);
      }
      if(!empty($sites))
      {
         $query->whereIn('id', $sites);
      }
      $is_holiday = false;
      if(isset($request->date)){
         $date = $request->date;
         $date = explode(' ', $date);
         $date = str_replace('-', '', $date[0]);
         $checkHoliday = DB::table('public_holidays')->where('date', $date)->first();
         !empty($checkHoliday) ? $is_holiday = true : $is_holiday = false;
      }
      $sites = $query->select('id', 'site_name')->orderBy('site_name', 'asc')->get();
      $guard_sites = GetGuardSitesResource::collection($sites);
      return response()->json(['success' => true, 'data' => $guard_sites, 'is_holiday' => $is_holiday]);
   }else{
      return response()->json(['success' => false, 'message' => 'No Location Found!']);
   }
}else{
   return response()->json(['success' => false, 'message' => 'Staff not Found!']);
}
}

public function onCovid_19(Request $request)
{
   $guard = Guard::where('id', $request->id)->first();
   if($guard){
      if($guard->covid_19 == 0){
         $guard->covid_19 = 1;
         $guard->update();
         jobRosterActions($request->admin_id, 'covid_status_on', $guard->id, 'Staff');
         return response()->json(['success' => true, 'message' => 'Staff covid marchal on!']);
      }elseif($guard->covid_19 == 1){
         $guard->covid_19 = 0;
         $guard->update();
         jobRosterActions($request->admin_id, 'covid_status_off', $guard->id, 'Staff');
         return response()->json(['success' => true, 'message' => 'Staff covid marchal off!']);
      }else{
         return response()->json(['success' => false, 'message' => 'no covid marchal status found!']);
      }
   }else{
      return response()->json(['success' => false, 'message' => 'No Staff found!']);
   }
}


public function activateGuard(Request $request)
{
   $guard = Guard::where('id', $request->id)->first();

   if($guard->admin_approval_status == 'inactive'){
      // if($guard->is_email_approved == 'yes'){
         $guard->admin_approval_status = 'active';
         $guard->guard_status = 'active';
         $guard->update();
         jobRosterActions($request->admin_id, 'staff_active', $guard->id, 'Staff', '', '', $request->reason);

         return response()->json(['message' => 'Staff Activated' , 'success' => true]);
      // }else{
      //    return response()->json(['message' => 'First verify Staff email!', 'success' => false]);
      // }
   }elseif($guard->admin_approval_status == 'active'){
      $guard->admin_approval_status ='inactive';
      $guard->guard_status = 'inactive';
      $guard->update();
      $reason = GuardDocument::where('guard_id', $request->id)->get();
      jobRosterActions($request->admin_id, 'staff_inactive', $guard->id, 'Staff', '', '', $request->reason);

      return response()->json(['message' => 'Staff Inactive' , 'success' => true]);
   }else{
      return response()->json(['message' => 'Staff not found!' , 'success' => false]);
   }
}

public function guardAvailable(Request $request)
{
   $guard = Guard::where('id', $request->id)->first();
   if($guard){
      if($guard->is_available == 'yes'){
         $guard->is_available = 'no';
         $guard->update();
         $date = date('Y-m-d');
         JobRoster::where('start', $date)->update(['guard_id' => null]);
         jobRosterActions($request->admin_id, 'guard_availablity_off', $guard->id, 'Staff');
         return response()->json(['message' => 'Staff inactive' , 'success' => true]);
      }elseif($guard->is_available == 'no'){
         $guard->is_available = 'yes';
         $guard->update();
         jobRosterActions($request->admin_id, 'guard_availablity_on', $guard->id, 'Staff');
         return response()->json(['message' => 'Staff active successfully!' , 'success' => true]);
      }
   }else{
      return response()->json(['message' => 'Staff not found!' , 'success' => false]);
   }  
}

public function guardEdit(Request $request)
{
   $guard = Guard::where('id', $request->id)->first();
   $guard->visaNumber = GuardDocument::where(['guard_id'=>$guard->id, 'document_name'=>'visa'])->first();
   $guard->passportNumber = GuardDocument::where(['guard_id'=>$guard->id, 'document_name'=>'passport'])->first();
   $grd = (new EditGuardResource($guard));
   return response()->json([ 'success' => true, 'data' => $grd , 'code' => 200 ]);
}
public function updateLeaves(Request $request){
   $guard = Guard::find($request->id);
   $guard->annual_leave_hours = $request->annual_leave;
   $guard->sick_leave_hours = $request->sick_leave;
   $guard->update();
   return response()->json(['success' => true, 'message' => 'Leaves Updated']);
}
public function update(Request $request)
{
   $cords = explode(",",$request->coordinates);
   $lat = $cords[0];
   $lng = $cords[1];
   $guard = Guard::where('id', $request->id)->first();
   $old_data = $guard;
   if(empty($guard)){
      return response()->json(['message' => "Staff Not Found" ,  'code' => 404, 'success' => true]);
   }else{
      $guard->first_name = $request->first_name;
      $guard->middle_name = $request->middle_name;
      $guard->last_name = $request->last_name;
      $guard->name = $request->first_name.' '. ($request->middle_name ? $request->middle_name.' ' :'') .$request->last_name;
      $guard->email = $request->email;
      $guard->phone = $request->phone;
      $guard->address = $request->address;
      $guard->coordinates = $request->coordinates;
      $guard->latitude =  $lat;
      $guard->longitude =  $lng;
      $guard->city = $request->city; 
      $guard->state = $request->state; 
      $guard->postal_code = $request->postal_code;  
      $guard->dob = $request->dob; 
      $guard->gender = $request->gender; 
      $guard->emergency_contact_name = $request->emergency_contact_name;
      $guard->emergency_contact_phone = $request->emergency_contact_phone; 
      $guard->suburb = $request->suburb;
      $guard->guard_type = $request->guard_type;
      $guard->country = $request->home_country;
      $guard->staff_type = $request->staff_type;
      $guard->annual_leave_hours = $request->annual_leave;
      $guard->sick_leave_hours = $request->sick_leave;
      $guard->guard_postion = $request->guard_postion;
      $guard->emergency_contact_email = $request->emergency_contact_email;
      $guard->emergency_contact_relation = $request->emergency_contact_relation;
      $guard->joining_date = $request->joining_date;
      $guard->customer_id	 = json_encode($request->customer_id); 
      $guard->contractor_id	 = json_encode($request->contractor_id);
      //$guard->site_id = array();
      if($request->has('profile_image')){ 
         $guard->profile_image = str_replace(url('')."/"."guard/","",$request->profile_image);
      }
      if($request->has('password')  && !empty($request->password)){
         $guard->password = Hash::make($request->password); 
      }
      $guard->save();
      if(isset($request->guard_type) && $request->guard_type == 'direct'){
         $getStaff = Guard::find($guard->id);
         if ($guard->id >= 10 && $guard->id <= 99) {
            $uniqueNum = '0'.$guard->id;
         }else if($guard->id >= 0 && $guard->id <= 9){
            $uniqueNum = '00'.$guard->id;
         }else{
            $uniqueNum = $guard->id;
         }
         $getBusinessName = "AMG";
         $getStaff->internal_id = $getBusinessName.'-'.$uniqueNum;
         $getStaff->update();
      }else if(isset($request->guard_type) && $request->guard_type == 'contractor'){
         $getStaff = Guard::find($guard->id);
         if ($guard->id >= 10 && $guard->id <= 99) {
            $uniqueNum = '0'.$guard->id;
         }else if($guard->id >= 0 && $guard->id <= 9){
            $uniqueNum = '00'.$guard->id;
         }else{
            $uniqueNum = $guard->id;
         }
         // $connectionName = 'mysql2';
         // $getBusinessName = DB::connection($connectionName)
         //    ->table('business_data')->where('id', $request->header('Business-Id'))
         //    ->select('id','sub_title')
         //    ->first();
        if (isset($request->contractor_id)) {
            $getContractor = Contractor::where('id', $request->contractor_id)->select('id', 'name')->first();
         } else {
            $getContractor = null;
         }

         $contractorName = $getContractor && !empty($getContractor->name) ? $getContractor->name : 'N/A';

         $getStaff->internal_id = 'AMG' . '-' . $uniqueNum . '-' . $contractorName;
         $getStaff->update();

      }
      $updated_column = $guard->getChanges();
    
      jobRosterActions($request->admin_id, 'staff_update', $guard->id, 'Staff', $old_data, $updated_column);
      return response()->json(['message' => "Staff updated" ,  'code' => '200', 'success' => 'true'],200);
   }
}

public function deleteGuard(Request $request)
{
   $guard = Guard::where('id', $request->id)->first();
   $old_data = $guard;
   if(!empty($guard)){
      $guard->guard_status = 'deleted';
      $guard->admin_approval_status = 'inactive';
      $guard->update();
      jobRosterActions($request->admin_id, 'staff_delete', $guard->id, 'Staff', $old_data, '', $request->reason);
      return response()->json(['message' => "Staff Deleted Successfully" ,  'code' => 200, 'success' => true]);
   }else{
      return response()->json(['message' => "Staff Not Found" ,  'code' => 404, 'success' => false],404);
   }
}


public function guardLeave(Request $request)
{
   $guard_leave = new GuardLeave();
   $guard_leave->guard_id = $request->guard_id;
   $guard_leave->start = $request->start;
   $guard_leave->end	 = $request->end;
   $guard_leave->date_added = $request->date_added;
   $guard_leave->notes = $request->notes;
   $guard_leave->status = $request->status;
   $guard_leave->save();
   jobRosterActions($request->admin_id, 'add_staff_leave', $guard_leave->id, 'staff_leave');
   jobRosterActions($request->admin_id, 'add_staff_leave', $request->guard_id, 'staff_leave');
   return response()->json(['message' => "Staff Leave Send  Successfully",  'code' => '200', 'success' => 'true'],200);
}
public function deleteGuardInduction(Request $request)
{
   $deleteInduction = GuardInduction::where('id', $request->id)->first();
   if($deleteInduction){
      $deleteInduction->delete();
      return response()->json(['message' => 'Induction Record Deleted' , 'success' => true]);
   }else{
      return response()->json(['message' => 'Record Not Found!' , 'success' => false]);
   }
}
public function updateEmploymentDetail(Request $request)
{
   $old_data = GuardWorkDetail::where('guard_id', $request->id)->first();
   $updateEmpDetails = GuardWorkDetail::where('guard_id', $request->id)->first();
   $is_check =0;
   if(!$updateEmpDetails){
      $updateEmpDetails = new GuardWorkDetail();
      $is_check =1;
   }

   if ((int)$request->limit_exceed === 1) {

      if (!$request->filled('otp')) {
         return response()->json([
               'success' => false,
               'message' => 'Hours exceed limit. Manager OTP required.',
               'require_otp' => true,
         ], 200);
      }

      $getAdmin = User::find(257);

      if (!$getAdmin || empty($getAdmin->google2fa_secret)) {
         return response()->json([
               'success' => false,
               'message' => 'Manager does not have 2FA setup.',
               'require_otp' => true,
         ], 200);
      }

      $google2fa = new Google2FA();

      if (!$google2fa->verifyKey($getAdmin->google2fa_secret, $request->otp)) {
         return response()->json([
               'success' => false,
               'message' => 'Wrong OTP! Please try again.',
               'require_otp' => true,
         ], 200);
      }
   }

   $currentDate = Carbon::now()->format('Y-m-d');
   $week_array = $this->calculateFutureMonthFourthnight($currentDate);
   $fortnight_start_date = new DateTime($week_array['week_start']);
   $fortnight_end_date = new DateTime($week_array['week_end']);
   $dates_periods = array();
   $period = new DatePeriod(
      new DateTime($fortnight_start_date->format("Y-m-d")),
      new DateInterval('P1D'),
      new DateTime($fortnight_end_date->format("Y-m-d"))
   );

   foreach ($period as $key => $value) {
      array_push($dates_periods, $value->format('Y-m-d'));
   }

   array_push($dates_periods, $fortnight_end_date->format("Y-m-d"));

   $updateEmpDetails->guard_id = $request->id;
   $updateEmpDetails->letter_url = $request->letter_url ?? null;
   $updateEmpDetails->start_time = $request->start_time;
   $updateEmpDetails->end_time = $request->end_time;
   $updateEmpDetails->limit_exceed = $request->limit_exceed ?? 0;
   $updateEmpDetails->hired_on = isset($request->hired_on) ? dbFormate($request->hired_on) : null ;
   $updateEmpDetails->job_level = $request->job_level;
   $updateEmpDetails->payrate_state = $request->payrate_state;
   $updateEmpDetails->payrate = $request->payrate;
   $updateEmpDetails->other_name = $request->other_name;
   $updateEmpDetails->tfn_file = str_replace(url('')."/"."guard_employment_details/","",$request->tfn_file);
   $updateEmpDetails->tfn_file_no = $request->tfn_file_no;
   $updateEmpDetails->superannutation_file = str_replace(url('')."/"."guard_employment_details/","",$request->superannutation_file);
   $updateEmpDetails->superannutation_no = $request->superannutation_no;
   $updateEmpDetails->account_holder = $request->account_holder;
   $updateEmpDetails->superannutation_name = $request->superannutation_name;
   $updateEmpDetails->abn_name = $request->abn_name;
   $updateEmpDetails->abn_no = $request->abn_no;
   $updateEmpDetails->superannuation_fund = $request->superannuation_fund;
   $updateEmpDetails->superannuation_fund_usi = $request->superannuation_fund_usi;
   $updateEmpDetails->member_number = $request->member_number;

   $updateEmpDetails->bank_name = $request->bank_name;
   $updateEmpDetails->bsb = $request->bsb;
   $updateEmpDetails->bank_account_no = $request->bank_account_no;
   $updateEmpDetails->work_hours_limitation_status = 1;
   // $updateEmpDetails->weekly_work_hours_limitation = $request->weekly_work_hours_limitation;
   $updateEmpDetails->authorized_by = $request->authorized_by;
   $updateEmpDetails->letter_from_educational_institute = $request->letter_from_educational_institute;
   $updateEmpDetails->guard_document_type = $request->guard_document_type;
   if($request->guard_document_type == 'student_visa'){
      if($request->limit_exceed == 1)
         {
               $guardStart = new DateTime($request->start_time);
               $guardEnd = new DateTime($request->end_time);
               $interval = new DateInterval('P1D');
               $dateRange = new DatePeriod($guardStart, $interval, $guardEnd->modify('+1 day'));

               $guardDates = [];
               foreach ($dateRange as $date) {
                  $guardDates[] = $date->format('Y-m-d');
               }
               $hasCompleteFortnight = false;

               $guardDateCount = count($guardDates);

               for ($i = 0; $i <= $guardDateCount - 14; $i++) {
                  $fourteenDays = array_slice($guardDates, $i, 14);
                  
                  $allExist = true;
                  foreach ($fourteenDays as $day) {
                     if (!in_array($day, $dates_periods)) {
                           $allExist = false;
                           break;
                     }
                  }
                  
                  if ($allExist) {
                     $hasCompleteFortnight = true;
                     break;
                  }
               }

               if ($hasCompleteFortnight) {
                  $updateEmpDetails->weekly_work_hours_limitation = 72;    
               } else {
                  $updateEmpDetails->weekly_work_hours_limitation = 48;
               }
         }else{
                  $updateEmpDetails->weekly_work_hours_limitation = 48;
         }
   }else{
      $guard = Guard::find($request->id);
      if($guard->staff_type == 'part_time'){
         $updateEmpDetails->weekly_work_hours_limitation = 72;
      }else{
         $updateEmpDetails->weekly_work_hours_limitation = 76;
      }
   }
   if($request->has('induction') && !empty($request->induction) ){

      foreach ($request->induction as $key => $ind) {
         $guard_induction = GuardInduction::where('id', $ind['id'])->first();
         if($guard_induction){
            $old_data_ind = $guard_induction;
            $image = str_replace(url('')."/"."guard_employment_details/","",$ind['induction_file']);
            $guard_induction->guard_id	 = $request->id;
            $guard_induction->induction_file = $image;
            $guard_induction->customer = $ind['customer'];
            $guard_induction->site = $ind['site'];
            $guard_induction->customer_site_status = $ind['customer_site_status'];
            $guard_induction->save();
            $induction_updated = $guard_induction->getChanges();
            jobRosterActions($request->admin_id, 'update_induction', $guard_induction->id, 'guards_induction', $old_data_ind, $induction_updated);
            jobRosterActions($request->admin_id, 'update_induction', $request->id, 'guards_induction', $old_data_ind, $induction_updated);
         }else{
            $guard_induction = new GuardInduction();
            $guard_induction->induction_file = $ind['induction_file'];
            $guard_induction->guard_id	 = $request->id;
            $guard_induction->customer = $ind['customer'];
            $guard_induction->site = $ind['site'];
            $guard_induction->customer_site_status = $ind['customer_site_status'];
            $guard_induction->save();
            jobRosterActions($request->admin_id, 'add_induction', $guard_induction->id, 'guards_induction');
            jobRosterActions($request->admin_id, 'add_induction', $request->id, 'guards_induction');
         }
      }
   }
   $check_old_data_exist = GuardDocument::where('guard_id', $request->id)->where('document_category', '!=', 'other-doc')->first();
   if((!isset($old_data)) || (isset($old_data->guard_document_type) && !$check_old_data_exist)){
      $document_categories = DocumentCategory::where('document_category', $request->guard_document_type)->first();
      if($document_categories){
         foreach (json_decode($document_categories->document_type) as $key => $value) {  
            $guard_documents = new GuardDocument();
            $guard_documents->guard_id = $request->id;
            $guard_documents->document_category = ($document_categories->document_category != '' ? $document_categories->document_category : 'other');
            $guard_documents->document_type = $key;
            $guard_documents->document_name = $value;
            $guard_documents->is_deleteable = 0;
            $guard_documents->save();
            jobRosterActions($request->admin_id, 'add_staff_document', $guard_documents->id, 'staff_documents');
            jobRosterActions($request->admin_id, 'add_staff_document', $request->id, 'staff_documents');
         }
      }
   }else{
      // if($old_data->guard_document_type != $request->guard_document_type){
      //    if($request->has('guard_document_type') && !empty($request->guard_document_type)){
      //       $document_categories = DocumentCategory::where('document_category', $request->guard_document_type)->first();
      //       if($document_categories){
      //       $old_data_doc = GuardDocument::where('guard_id', $request->id)->where('document_category', '!=', 'other-doc')->first();
      //       $old_guard_documents = GuardDocument::where('guard_id', $request->id)->where('document_category', '!=', 'other-doc')->delete();
      //       jobRosterActions($request->admin_id, 'delete_staff_document', $document_categories->id, 'staff_documents', $old_data_doc);
      //       jobRosterActions($request->admin_id, 'delete_staff_document', $request->id, 'staff_documents', $old_data_doc);
   
      //       $guard_documents = GuardDocument::where('guard_id', $request->id)->where([['document_category', '!=', 'other-doc'], ['file', null]])->delete();
      //       foreach (json_decode($document_categories->document_type) as $key => $value) {  
      //          if(!GuardDocument::where(['guard_id'=>$request->id, 'document_type'=> $key])->first()){
      //             $guard_documents = new GuardDocument();
      //             $guard_documents->guard_id = $request->id;
      //             $guard_documents->document_category = ($document_categories->document_category != '' ? $document_categories->document_category : 'other');
      //             $guard_documents->document_type = $key;
      //             $guard_documents->document_name = $value;
      //             $guard_documents->is_deleteable = 0;
      //             $guard_documents->save();
      //             jobRosterActions($request->admin_id, 'add_staff_document', $guard_documents->id, 'staff_documents');
      //             jobRosterActions($request->admin_id, 'add_staff_document', $request->id, 'staff_documents');
      //          }
      //       }
            
      //       }
      //    }
      // }
      if($old_data->guard_document_type != $request->guard_document_type){
         if($request->has('guard_document_type') && !empty($request->guard_document_type)){
            $document_categories = DocumentCategory::where('document_category', $request->guard_document_type)->first();
            
            if($document_categories){
                  $old_docs = GuardDocument::where('guard_id', $request->id)
                                          ->where('document_category', '!=', 'other-doc')
                                          ->get()
                                          ->keyBy('document_type');
                  
                  $new_doc_types = json_decode($document_categories->document_type, true);
                  $new_document_category = $document_categories->document_category ?: 'other';
                  
                  $old_doc_types = $old_docs->keys()->toArray();
                  $new_doc_keys = array_keys($new_doc_types);
                  
                  $to_delete_types = array_diff($old_doc_types, $new_doc_keys);
                  
                  $to_add_types = array_diff($new_doc_keys, $old_doc_types);
                  
                  $common_types = array_intersect($old_doc_types, $new_doc_keys);
                  
                  if(!empty($common_types)) {
                     $common_doc_ids = [];
                     foreach($common_types as $doc_type) {
                        if($old_docs->has($doc_type)) {
                              $common_doc_ids[] = $old_docs[$doc_type]->id;
                        }
                     }
                     
                     GuardDocument::whereIn('id', $common_doc_ids)
                                 ->update(['document_category' => $new_document_category]);
                     
                     foreach($common_doc_ids as $doc_id) {
                        jobRosterActions($request->admin_id, 'update_staff_document', $doc_id, 'staff_documents');
                     }
                  }
                  
                  if(!empty($to_delete_types)) {
                     $docs_to_delete = $old_docs->whereIn('document_type', $to_delete_types);
                     
                     foreach($docs_to_delete as $doc) {
                        $doc_id = $doc->id;
                        $doc->delete();
                        
                        jobRosterActions($request->admin_id, 'delete_staff_document', $doc_id, 'staff_documents', $doc);
                        jobRosterActions($request->admin_id, 'delete_staff_document', $request->id, 'staff_documents', $doc);
                     }
                  }
                  
                  if(!empty($to_add_types)) {
                     $documents_to_insert = [];
                     
                     foreach($to_add_types as $doc_type) {
                        if(!GuardDocument::where(['guard_id' => $request->id, 'document_type' => $doc_type])->exists()) {
                              $documents_to_insert[] = [
                                 'guard_id' => $request->id,
                                 'document_category' => $new_document_category,
                                 'document_type' => $doc_type,
                                 'document_name' => $new_doc_types[$doc_type],
                                 'is_deleteable' => 0,
                                 'created_at' => now(),
                                 'updated_at' => now()
                              ];
                        }
                     }
                     
                     if(!empty($documents_to_insert)) {
                        GuardDocument::insert($documents_to_insert);
                        
                        foreach($documents_to_insert as $new_doc) {
                              $saved_doc = GuardDocument::where([
                                 'guard_id' => $new_doc['guard_id'],
                                 'document_type' => $new_doc['document_type']
                              ])->first();
                              
                              if($saved_doc) {
                                 jobRosterActions($request->admin_id, 'add_staff_document', $saved_doc->id, 'staff_documents');
                                 jobRosterActions($request->admin_id, 'add_staff_document', $request->id, 'staff_documents');
                              }
                        }
                     }
                  }
                  
                  if(!empty($to_delete_types)) {
                     GuardDocument::where('guard_id', $request->id)
                     ->where('document_category', '!=', 'other-doc')
                     ->whereNull('file')
                     ->whereIn('document_type', $to_delete_types)
                     ->delete();
                  }
            }
         }
      }
   }


   $updateEmpDetails->save();

   if($is_check == 1){
      jobRosterActions($request->admin_id, 'add_staff_emp_details', $updateEmpDetails->id, 'staff_work_details');
      jobRosterActions($request->admin_id, 'add_staff_emp_details', $request->id, 'staff_work_details');
      return response()->json(['message' => "Employment Details Added Successfully!",  'code' => 200, 'success' => true]);
   }else{
      $updated_column = $updateEmpDetails->getChanges();
      jobRosterActions($request->admin_id, 'update_staff_details', $updateEmpDetails->id, 'staff_work_details', $old_data, $updated_column);
      jobRosterActions($request->admin_id, 'update_staff_details', $request->id, 'staff_work_details', $old_data, $updated_column);
      return response()->json(['message' => "Employment Details Updated Successfully!",  'code' => 200, 'success' => true]);
   }
}

public function editEmploymentDetail(Request $request)
{

   $employmentDetail = GuardWorkDetail::where('guard_id', $request->id)->with(['inductions','guardz'])->first();
   if($employmentDetail){  
      $grd = (new EditGuardEmploymentResource($employmentDetail));
      return response()->json([ 'success' => true, 'data' => $grd , 'code' => 200 ]);
   }else{
      return response()->json([ 'success' => false, 'data' => '' , 'code' => 404 ]);
   }
}

public function addGuardDocuments(Request $request)
{
   $guard_work_details = GuardWorkDetail::where('guard_id', $request->id)->first();
   if($guard_work_details && $guard_work_details->guard_document_type){
      
      if(GuardDocument::where(['guard_id'=> $request->id, 'document_type'=> $request->document_type])->first()){
         if($request->document_type != 'other'){
         return response()->json(['message' => "This type of document is already exist!", 'success' => false], 404);
         }
      }
      $addDocuments = new GuardDocument();
      $addDocuments->guard_id = $request->id;
      $addDocuments->document_name = $request->document_name;
      $addDocuments->is_deleteable = 1;
      $addDocuments->document_category = $guard_work_details->guard_document_type;
      $addDocuments->document_expire = $request->document_expire ? dbFormate($request->document_expire) : null;
      $addDocuments->notes = $request->notes;
         //$addDocuments->type = (!empty($request->type) && $request->has('type') ? $request->type : '');
      $addDocuments->side = (!empty($request->side) && $request->has('side') ? $request->side : '');
      $addDocuments->document_no = (!empty($request->document_no) && $request->has('document_no') ? $request->document_no : '');
      $addDocuments->document_type = (!empty($request->document_type) && $request->has('document_type') ? $request->document_type : '');
      $addDocuments->document_name = (!empty($request->document_name) && $request->has('document_name') ? returnAction($request->document_name) : '');
      $addDocuments->c_f_roster = ($request->c_f_roster == 'on' ? true : false);
      $addDocuments->c_f_profile = ($request->c_f_profile == 'on' ? true : false);
      if($request->has('file')){
         $addDocuments->file = $request->file; 
      }
      $addDocuments->save();
      jobRosterActions($request->admin_id, 'add_staff_documents', $addDocuments->id, 'staff_documents');
      jobRosterActions($request->admin_id, 'add_staff_documents', $request->id, 'staff_documents');
      return response()->json(['message' => "Staff Documents Add Successfully!",'code' => 200, 'success' => true]);
   }else{
      return response()->json(['message' => "Staff Residencial status not updated!", 'success' => false], 404);
   }
}


public function editGuardDocument(Request $request)
{
   $guardDocuments = GuardDocument::where('id', $request->id)->first();
   $grd = (new EditGuardDocumentResource($guardDocuments));
   return response()->json([ 'success' => true, 'data' => $grd , 'code' => 200 ]);
}

public function getAllGuardDocument(Request $request)
{
   $guardDocuments = GuardDocument::where('guard_id', $request->id)->orderBy('document_name', 'asc')->get();
   $grd = GetAllGuardDocuments::collection($guardDocuments);
   return response()->json([ 'success' => true, 'data' => $grd , 'code' => 200 ]);
}

public function getDocumentType(Request $request)
{
  $guard_work_details = GuardWorkDetail::where('guard_id', $request->id)->first();
  if(!empty($guard_work_details->guard_document_type)){
   $guard_document_type = DocumentCategory::where('document_category', $guard_work_details->guard_document_type)->first();
   $gdt = (new GetDocumentTypeResource($guard_document_type));
   return response()->json([ 'success' => true, 'data' => $gdt , 'code' => 200 ]);
}else{
   return response()->json([ 'success' => false, 'data' => '' , 'code' => 404 ]);
} 
}

public function updateGuardDocuments(Request $request)
{
   //faizan

   $updateDocuments = GuardDocument::where('id', $request->id)->first();
   $old_data = $updateDocuments;
   $updateDocuments->document_name = $request->document_name;
   $updateDocuments->guard_id = $request->guard_id;
   if(!empty($request->document_expire) && $request->document_expire == 'current, pending renewal')
   {
   $updateDocuments->document_expire = $request->document_expire;
   }else{
   $updateDocuments->document_expire = !empty($request->document_expire) ? dbFormate($request->document_expire) : '' ;
   }
   $updateDocuments->notes = $request->notes;
   $updateDocuments->side = (!empty($request->side) && $request->has('side') ? $request->side : '');
   $updateDocuments->document_no = (!empty($request->document_no) && $request->has('document_no') ? $request->document_no : '');
   $updateDocuments->document_type = (!empty($request->document_type) && $request->has('document_type') ? $request->document_type : '');
   $updateDocuments->c_f_roster = ($request->c_f_roster == 'on' ? true : false);
   $updateDocuments->c_f_profile = ($request->c_f_profile == 'on' ? true : false);
   if($request->has('file')){
      $updateDocuments->file = $request->file; 
      $updateDocuments->file = str_replace(url('')."/"."guard_documents/","",$request->file);
   }
   $updateDocuments->save();
   $updated_column = $updateDocuments->getChanges();
   $updated_column['document_name'] = $request->document_name;

   jobRosterActions($request->admin_id, 'update_staff_documents', $updateDocuments->id, 'staff_documents', $old_data, $updated_column);
   jobRosterActions($request->admin_id, 'update_staff_documents', $request->guard_id, 'staff_documents', $old_data, $updated_column);

   $check_guard_doc = checkGuardDocumentStatus($request->guard_id);
       //return $check_guard_doc;
   if($check_guard_doc == 'active'){
      $guard_status_update = Guard::where('id', $request->guard_id)->first();
      $old_data = $guard_status_update;
      if($guard_status_update->guard_status == 'new' && $guard_status_update->admin_approval_status == 'inactive' || $guard_status_update->guard_status == 'document_exp'){
         if($guard_status_update->is_email_approved == 'yes'){
            $guard_status_update->guard_status = 'pending';
            $guard_status_update->update();
         }

         JobRoster::where(['guard_id'=> $request->guard_id, 'job_status'=>'completed'])->update(['doc_conf' => 'active']);

         JobRoster::where(['guard_id'=> $request->guard_id, 'job_status'=>'pending'])->where('start', '>=' ,  date("Y-m-d 23:59") )->update(['doc_conf' => 'active']);
         $reason = GuardDocument::where('guard_id', $request->guard_id)->get();
         jobRosterActions($request->admin_id, 'staff_status_pending', $updateDocuments->id, 'staff_documents', $old_data, null, $reason); 
         jobRosterActions($request->admin_id, 'staff_status_pending', $request->guard_id, 'staff_documents', $old_data, null, $reason); 
      }elseif($guard_status_update->guard_status == 'new' && $guard_status_update->admin_approval_status == 'active'){
         if($guard_status_update->is_email_approved == 'yes'){         
            $guard_status_update->guard_status = 'active';
            $guard_status_update->update();
         }
         $reason = GuardDocument::where('guard_id', $request->guard_id)->get();
         jobRosterActions($request->admin_id, 'staff_status_pending', $request->guard_id, 'staff_documents', $old_data, null, $reason); 

      }
      
         // elseif($guard_status_update->guard_status == 'pending' && $guard_status_update->admin_approval_status == 'active'){
         //    $guard_status_update->guard_status = 'active';
         //    $guard_status_update->update();
         // }
   }
   
//   else{
//       $guard_status_update = Guard::where('id', $request->guard_id)->first();
//       $guard_status_update->guard_status = 'pending';
//       $guard_status_update->update();
//       jobRosterActions($request->admin_id, 'staff_status_pending', $updateDocuments->id, 'staff_documents', $old_data); 
//   }
   return response()->json(['message' => "Staff Documents Updated Successfully!",'code' => 200, 'success' => true]);
}

public function updateGuardReqDocuments(Request $request)
{
   $updateDocuments = GuardDocument::where('id', $request->id)->first();
   $old_data = $updateDocuments;
   $updateDocuments->document_name = $request->document_name;
   $updateDocuments->guard_id = $request->guard_id;
   if(!empty($request->document_expire) && $request->document_expire == 'current, pending renewal')
   {
   $updateDocuments->document_expire = $request->document_expire;
   }else{
   $updateDocuments->document_expire = !empty($request->document_expire) ? dbFormate($request->document_expire) : '' ;
   }
   $updateDocuments->notes = $request->notes;
   $updateDocuments->side = (!empty($request->side) && $request->has('side') ? $request->side : '');
   $updateDocuments->document_no = (!empty($request->document_no) && $request->has('document_no') ? $request->document_no : '');
   $updateDocuments->document_type = (!empty($request->document_type) && $request->has('document_type') ? $request->document_type : '');
   $updateDocuments->c_f_roster = ($request->c_f_roster == 'on' ? true : false);
   $updateDocuments->c_f_profile = ($request->c_f_profile == 'on' ? true : false);
   if($request->has('file')){
      $updateDocuments->file = $request->file; 
      $updateDocuments->file = str_replace(url('')."/"."guard_documents/","",$request->file);
   }
   $updateDocuments->save();
   $updated_column = $updateDocuments->getChanges();
   $updated_column['document_name'] = $request->document_name;

   jobRosterActions($request->admin_id, 'update_staff_documents', $updateDocuments->id, 'staff_documents', $old_data, $updated_column);
   jobRosterActions($request->admin_id, 'update_staff_documents', $request->guard_id, 'staff_documents', $old_data, $updated_column);

   $check_guard_doc = checkGuardReqDocumentStatus($request->guard_id);
   if($check_guard_doc == 'active'){
      $guard_status_update = Guard::where('id', $request->guard_id)->first();
      $old_data = $guard_status_update;
      if($guard_status_update->guard_status == 'inactive' || $guard_status_update->guard_admin_approval == 1 || $guard_status_update->guard_status == 'document_exp'){
         if($guard_status_update->is_email_approved == 'yes'){
            $guard_status_update->guard_status = 'active';
            $guard_status_update->update();
         }

         // JobRoster::where(['guard_id'=> $request->guard_id, 'job_status'=>'completed'])->update(['doc_conf' => 'active']);

         // JobRoster::where(['guard_id'=> $request->guard_id, 'job_status'=>'pending'])->where('start', '>=' ,  date("Y-m-d 23:59") )->update(['doc_conf' => 'active']);
         $reason = GuardDocument::where('guard_id', $request->guard_id)->get();
         jobRosterActions($request->admin_id, 'staff_status_pending', $updateDocuments->id, 'staff_documents', $old_data, null, $reason); 
         jobRosterActions($request->admin_id, 'staff_status_pending', $request->guard_id, 'staff_documents', $old_data, null, $reason); 
      }
      
   }
   
   return response()->json(['message' => "Staff Documents Updated Successfully!",'code' => 200, 'success' => true]);
}

public function deleteGuardDocument(Request $request)
{
   $guardDocuments = GuardDocument::where('id', $request->id)->first();
   $old_data = $guardDocuments;
   if(!empty($guardDocuments)){
      $guardDocuments->delete();
         // logging('delete', $request->id, 'login_user', 'Guard Documents');
      jobRosterActions($request->admin_id, 'staff_document', $guardDocuments->id, 'staff_documents', $old_data);
      jobRosterActions($request->admin_id, 'staff_document', $old_data->guard_id, 'staff_documents', $old_data);
      return response()->json(['message' => "Staff Documents Deleted Successfully" ,  'code' => 200, 'success' => true]);
   }else{
      return response()->json(['message' => "Staff Documents Not Found" ,  'code' => '404', 'success' => 'true'],404);
   }
}


public function saveFeedback(Request $request)
{
   $guardFeedBack = new GuardFeedBack();
   $guardFeedBack->guard_id = $request->guard_id;
   $guardFeedBack->admin_id = $request->admin_id;
   $guardFeedBack->feedback = $request->feedback;
   $guardFeedBack->save();
   jobRosterActions($request->admin_id, 'add_staff_feedback', $guardFeedBack->id, 'staff_feedbacks'); 
   jobRosterActions($request->admin_id, 'add_staff_feedback', $request->guard_id, 'staff_feedbacks'); 
   return response()->json(['message' => "Feedback Saved" ,  'code' => 200, 'success' => true]);
}

public function updateFeedback(Request $request)
{
      //$guardFeedBack = GuardFeedBack::where('guard_id', $request->guard_id)->where('admin_id', $request->admin_id)->first();
   $user = User::where('id', $request->admin_id)->first(); 
   //    ->orWhere(function ($query) use ($request){
   //       $query->where('admin_id', $request->admin_id);
   //   })

   //where('guard_id', $request->guard_id)->where('admin_id', $request->admin_id)
      //dd($request->guard_id);



      //dd($request->admin_id);
   $guardFeedBack = GuardFeedBack::where(function($query) use ($request){
      $query->where('guard_id', $request->guard_id)->where('admin_id', $request->admin_id)->where('id', $request->id);
   })->first();

   if($user->userType == 'super-admin'){
      $guardFeedBack = GuardFeedBack::where('guard_id', $request->guard_id)->where('id', $request->id)->first();
   }
   if(!empty($guardFeedBack)){
      $old_data = $guardFeedBack;
      $guardFeedBack->guard_id = $request->guard_id;
         //$guardFeedBack->admin_id = $request->admin_id;
      $guardFeedBack->feedback = $request->feedback;
      $guardFeedBack->on_edit = 'edit';
      $guardFeedBack->update();
      jobRosterActions($request->admin_id, 'staff_guard_feedback', $guardFeedBack->id, 'staff_feedbacks', $old_data); 
      jobRosterActions($request->admin_id, 'staff_guard_feedback', $request->guard_id, 'staff_feedbacks', $old_data);
      return response()->json(['message' => "feedback Updated successfully!" ,  'code' => 200, 'success' => true]);

   }else{
      return response()->json(['message' => "you are not authorized to Updated this feedback !" ,  'code' => 200, 'success' => true]);
   }

}
public function showGuardFeedBack(Request $request)
{
   $guardFeedBack = GuardFeedBack::where('guard_id', $request->guard_id)->with('admin')->get();
   if($guardFeedBack){
      $gfb = ShowGuardFeedBackResource::collection($guardFeedBack);
      return response()->json([ 'success' => true, 'data' => $gfb , 'code' => 200 ]);
   }else{
      return response()->json([ 'success' => false, 'message' => 'feedback not found!' ,'data' => '', 'code' => 404 ]);
   }
}

public function deketeFeedback(Request $request)
{
   $user = User::where('id', $request->admin_id)->first();
   $guardFeedBack = GuardFeedBack::where(function($query) use ($request){
      $query->where('guard_id', $request->guard_id)->where('admin_id', $request->admin_id)->where('id', $request->id);
   })->first();
   if($user->userType == 'super-admin'){
      $guardFeedBack = GuardFeedBack::where('guard_id', $request->guard_id)->where('id', $request->id)->first();
   }
   if(!empty($guardFeedBack)){
      $old_data = $guardFeedBack;
      $guardFeedBack->delete();
      jobRosterActions($request->admin_id, 'delete_staff_feedback', $guardFeedBack->id, 'staff_feedbacks', $old_data); 
      jobRosterActions($request->admin_id, 'delete_staff_feedback', $request->guard_id, 'staff_feedbacks', $old_data);
      return response()->json([ 'success' => true, 'message' => 'Feedback Delete Successfully!' , 'code' => 200 ]);
   }else{
      return response()->json([ 'success' => false, 'message' => 'Feedback not Found!' , 'code' => 404 ]);
   }
}


public function guardRestore(Request $request)
{
   $guard = Guard::where('id', $request->id)->first();
   if($guard->guard_status == "deleted"){
      $check_guard_doc = checkGuardDocumentStatus($request->id);
      if($check_guard_doc == 'active'){
         if($guard->admin_approval_status == 'inactive' && $guard->is_email_approved == 'yes'){
            $guard->guard_status = 'pending';
            $guard->update();
            jobRosterActions($request->admin_id, 'staff_status_pending', $guard->id, 'Staff');
         }elseif($guard->guard_status == 'pending' && $guard->admin_approval_status == 'active'){
            $guard->guard_status = 'active';
            $guard->update();
            jobRosterActions($request->admin_id, 'staff_status_active', $guard->id, 'Staff');
         }elseif($guard->admin_approval_status == 'active' && $guard->is_email_approved == 'yes'){
            $guard->guard_status = 'active';
            $guard->update();
            jobRosterActions($request->admin_id, 'staff_status_active', $guard->id, 'Staff');
         }elseif($guard->admin_approval_status == 'inactive' && $guard->is_email_approved == 'no'){
            $guard->guard_status = 'new';
            $guard->update();
            jobRosterActions($request->admin_id, 'staff_status_new', $guard->id, 'Staff');
         }
      }elseif($check_guard_doc != 'active' && $guard->is_email_approved == 'yes' || $check_guard_doc != 'active' && $guard->is_email_approved == 'no'){
         $guard->guard_status = 'new';
         $guard->update();
         $reason = GuardDocument::where('guard_id', $request->id)->get();
         jobRosterActions($request->admin_id, 'staff_status_new', $guard->id, 'Staff', null, null,$reason);
      }
   }
   return response()->json([ 'success' => true, 'message' => 'Staff status updated' , 'code' => 200 ]);
}


public function addInternalAndExternalIds(Request $request)
{

   $guard = Guard::where('id', $request->guard_id)->first();
   if($guard){
      $guard->internal_id = $request->internal_id;
      $guard->save();
      if($request->has('guard_external_ids')){
         foreach ($request->guard_external_ids as $key => $ex) {
            if(isset($ex['id'])){
               $guard_external_id = GuardInternalAndExternalIds::where('id', $ex['id'])->first();
               $old_data = $guard_external_id;
               $guard_external_id->guard_id = $request->guard_id;
               $guard_external_id->external_id = $ex['external_id'];
               $guard_external_id->customer_id = $ex['customer_id'];
               $guard_external_id->save();
               jobRosterActions($request->admin_id, 'staff_guard_external', $guard->id, 'staff_internal_and_external_ids', $old_data);
            }else{
               $guard_external_id = new GuardInternalAndExternalIds();
               $guard_external_id->guard_id = $request->guard_id;
               $guard_external_id->external_id = $ex['external_id'];
               $guard_external_id->customer_id = $ex['customer_id'];
               $guard_external_id->save();
               jobRosterActions($request->admin_id, 'add_staff_external', $guard->id, 'staff_internal_and_external_ids');
            }
         }
         return response()->json([ 'success' => true, 'message' => 'Action Perform Successfully!' , 'code' => 200 ]);
      }  
   }
}

public function editInternalAndExternalIds(Request $request)
{
   $guard = Guard::where('id', $request->guard_id)->with('guardExternalIds')->first();
   if($guard){
      $gurd_ex_ids = (new GuardInternalAndExternalIdsResource($guard));
      return response()->json([ 'success' => true, 'data' => $gurd_ex_ids , 'code' => 200 ]);
   }
}

public function deleteExternalId(Request $request)
{
   $externalId = GuardInternalAndExternalIds::where('id', $request->id)->first();
   if($externalId){
      $old_data = $externalId; 
      $externalId->delete();
      jobRosterActions($request->admin_id, 'delete_staff_external_id', $externalId->id, 'staff_internal_and_external_ids', '',  $old_data);
      return response()->json([ 'success' => true, 'message' => 'External Id Deleted Successfully!' , 'code' => 200 ]);
   }else{
      return response()->json([ 'success' => false, 'message' => 'External Id Not Found!' , 'code' => 404 ]);
   }
}

public function getStaffData(Request $request)
{
    $start = Carbon::parse(dbFormate($request->start))->startOfDay();
    $end = $start->copy()->addDays(13)->endOfDay();

    $week_array = $this->calculateFutureMonthFourthnight($request->start);
    
    $start = $week_array['week_start'] . ' 00:00';
    $end = $week_array['week_end'] . ' 23:59';

   //  $query = Guard::where('id', $request->guard_id)
   //      ->with(['guardExternalIds'])
   //      ->with(['guardJobRoster' => function ($que2) use ($start, $end) {
   //          $que2->whereBetween('job_rosters.start', [$start, $end])
   //              ->join('sites', 'job_rosters.site_id', '=', 'sites.id')
   //              ->select('job_rosters.*', 'sites.site_name', 'sites.site_description')
   //              ->orderBy('job_rosters.start', 'ASC');
   //      }])
   //      ->with(['guardDocuments' => function ($que) use ($request) {
   //          $que->whereIn('document_type', $request->document_type)
   //              ->where('guard_id', $request->guard_id);
   //      }]);
   $query = Guard::where('id', $request->guard_id)
    ->with(['guardExternalIds'])
    ->with(['guardJobRoster' => function ($que2) use ($start, $end) {

        $que2->where('job_rosters.start', '>=', $start)
             ->where('job_rosters.start', '<=', $end)
             ->join('sites', 'job_rosters.site_id', '=', 'sites.id')
             ->select(
                 'job_rosters.*',
                 'sites.site_name',
                 'sites.site_description'
             )
             ->orderBy('job_rosters.start', 'ASC');
    }])
    ->with(['guardDocuments' => function ($que) use ($request) {

        $que->whereIn('document_type', $request->document_type)
            ->where('guard_id', $request->guard_id);
    }]);

    $staff = $query->get();
    $staff = StaffDataResource::collection($staff);

    $total_hours = JobRoster::where('guard_id', $request->guard_id)
        ->whereBetween('start', [$start, $end])
        ->sum('hours');

    $total_hours = round($total_hours, 2);

    return response()->json([
        'success' => true,
        'data' => $staff,
        'total_hours' => $total_hours
    ]);
}

function calculateFutureMonthFourthnight($givenDate)
{
    $startDate = '2025-12-01';
    
    // Try to parse with auto-detection logic
    $date = $this->parseDateWithAutoDetection($givenDate);
    
    $formattedDate = $date->format('Y-m-d H:i:s');
    $endDate = $formattedDate;
    $startTime = strtotime($startDate);
    $endTime = strtotime($endDate);

    $secondsDiff = $endTime - $startTime;
    $daysDiff = floor($secondsDiff / (60 * 60 * 24));
    $totalFourthnight = floor($daysDiff/14);
    $totalFourthnight = $totalFourthnight * 14;
    $FourthnightStartDate = date('Y-m-d', strtotime($startDate . ' + '.$totalFourthnight.' days'));
    $FourthnightEndDate = date('Y-m-d', strtotime($FourthnightStartDate . ' + 13 days'));
    
    $ret['week_start'] = $FourthnightStartDate;
    $ret['week_end'] = $FourthnightEndDate;
    return $ret;
}

private function parseDateWithAutoDetection($dateString)
{
    // Match both date and optional time
    preg_match('/^(\d{1,2})-(\d{1,2})-(\d{4})(?: (\d{1,2}):(\d{1,2}))?$/', $dateString, $matches);
    
    if (empty($matches)) {
        return Carbon::parse($dateString);
    }
    
    $first = (int)$matches[1];
    $second = (int)$matches[2];
    $year = (int)$matches[3];
    $hour = isset($matches[4]) ? (int)$matches[4] : 0;
    $minute = isset($matches[5]) ? (int)$matches[5] : 0;
    
    // Your format is m-d-Y, so first part is month (01), second is day (12)
    if ($first >= 1 && $first <= 12 && $second >= 1 && $second <= 31) {
        // Month is first, day is second (m-d-Y format)
        if ($first > 12) {
            // First number can't be > 12 if it's supposed to be month
            return Carbon::parse($dateString);
        }
        
        // Check if second part could be a valid day
        if ($second > 31) {
            return Carbon::parse($dateString);
        }
        
        $date = Carbon::create($year, $first, $second, $hour, $minute);
    } else {
        return Carbon::parse($dateString);
    }
    
    if ($date === false || !$date->isValid()) {
        return Carbon::parse($dateString);
    }
    
    return $date;
}

public function getAllSystemGuards()
{
   $guards = Guard::select('id', 'first_name', 'middle_name', 'last_name')->orderBy('first_name', 'asc')->get();
   return response()->json(['success' => true, 'data' => $guards]);
}

   // nwe
   // public function filterGuardLicense(Request $request)
   // {
   //    $today = Carbon::now()->toDateString();
   //    $query = Guard::query();
   //    if($request->has('document_type') && $request->document_type != '' && $request->document_type != null && !empty($request->document_type)){
   //    $guards = $query->whereHas('guardDocuments', function($q) use ($request, $today){
   //       foreach ($request->document_type as $key => $value) {
   //       $q->where('document_type', $value);
   //       $q->WhereNotNull('document_expire')->whereDate('document_expire', '>=', $today);
   //       $q->WhereNotNull('document_no');
   //       $q->WhereNotNull('file');
   //       }
   //    })->with(['guardDocuments' => function ($query) use ($request, $today) {
   //       // $query->whereIn('document_type', $request->document_type);
   //       // $query->WhereNotNull('document_expire')->whereDate('document_expire', '>=', $today);
   //       // $query->WhereNotNull('document_no');
   //       // $query->WhereNotNull('file');
   //       foreach ($request->document_type as $key => $value) {
   //          $query->where('document_type', $value);
   //          $query->WhereNotNull('document_expire')->whereDate('document_expire', '>=', $today);
   //          $query->WhereNotNull('document_no');
   //          $query->WhereNotNull('file');
   //          }
   //    }])->select('id', 'first_name', 'middle_name', 'last_name')->get();
   // }else{
   //    $guards = $query->with(['guardDocuments' => function ($query) use ($request, $today) {
   //       $query->WhereNotNull('document_expire')->whereDate('document_expire', '>=', $today);
   //       $query->WhereNotNull('document_no');
   //       $query->WhereNotNull('file');
   //    }])->get();
   // }
   //    $gud = FilterGuardLicenseResource::collection($guards);
   //    return response()->json(['success' => true, 'data' => $gud]);
   // }

   //old
   public function filterGuardLicense(Request $request)
   {
      $today = Carbon::now()->toDateString();
      $query = Guard::query();
      $query->where('guard_status', 'active');
   
      if ($request->has('document_type') && !empty($request->document_type)) {
         $documentTypes = $request->document_type;
         $guards = $query->whereHas('guardDocuments', function($q) use ($documentTypes, $today){
            $q->whereIn('document_type', $documentTypes);
            if (!in_array('vaccination', $documentTypes)) {
               $q->whereNotNull('document_expire')->whereDate('document_expire', '>=', $today);
               $q->whereNotNull('document_no');
            }
            $q->whereNotNull('file');
         })->with(['guardDocuments' => function ($query) use ($documentTypes, $today) {
            $query->whereIn('document_type', $documentTypes);
            if (!in_array('vaccination', $documentTypes)) {
               $query->whereNotNull('document_expire')->whereDate('document_expire', '>=', $today);
               $query->whereNotNull('document_no');
            }
            $query->whereNotNull('file');
         }])->orderBy('first_name', 'ASC')->select('id', 'first_name', 'middle_name', 'last_name')->get();
      } else {
         $guards = $query->with(['guardDocuments' => function ($query) use ($today) {
            $query->whereNotNull('document_expire')->whereDate('document_expire', '>=', $today);
            $query->whereNotNull('document_no');
            $query->whereNotNull('file');
         }])->orderBy('first_name', 'ASC')->get();
      }
   
      $gud = FilterGuardLicenseResource::collection($guards);
      return response()->json(['success' => true, 'data' => $gud]);
   }
   


public function filterAddGuardOnSite(Request $request)
{
   $site_id = (int) $request->site_id;

   $site = Site::where('id', $site_id)->first();
   $state = $site->state;

   //allow all guards
  $customers_guards = Guard::where(['state'=> $state, 'guard_status'=>'active'])
//   ->whereJsonDoesntContain('site_id', [$site_id])
  ->select('id','first_name','middle_name','last_name')->orderBy('first_name')->get();
   $site_guards = Guard::where('guard_status', 'active')
   // ->whereJsonContains('site_id', $site_id)
   ->where('is_available', 'yes')->where('admin_approval_status', 'active')->select('id', 'first_name', 'middle_name', 'last_name')->orderBy('first_name')->get();
   return response()->json(['success' => true, 'site_guards' => $site_guards, 'customers_guards' => $customers_guards]);
}

public function filterAddGuardOnRunSheet(Request $request)
{
   $run_sheet_id = (int) $request->run_sheet_id;

   $site = RunSheet::where('id', $run_sheet_id)->first();
   $state = $site->state;

  $customers_guards = Guard::where(['state'=> $state, 'guard_status'=>'active'])
  ->whereJsonDoesntContain('run_sheet_id', [$run_sheet_id])
  ->select('id','first_name','middle_name','last_name')->orderBy('first_name')->get();
   $run_sheet_guards = Guard::whereJsonContains('run_sheet_id', $run_sheet_id)
   ->where('guard_status', 'active')->where('is_available', 'yes')->where('admin_approval_status', 'active')->select('id', 'first_name', 'middle_name', 'last_name')->orderBy('first_name')->get();
   return response()->json(['success' => true, 'run_sheet_guards' => $run_sheet_guards, 'customers_guards' => $customers_guards]);
}




public function addCustomerSiteGuard(Request $request)
{
    $action = $request->input('action');
    $guardIds = $request->input('guard_id');
    $siteId = (int) $request->input('site_id');

    if (!is_array($guardIds)) {
        $guardIds = [$guardIds]; // Ensure $guardIds is an array
    }

    if ($action === 'add_to_site') {
        $this->addStaffToSite($guardIds, $siteId);
        return response()->json(['success' => true, 'msg' => 'Staff Added To Location Successfully!']);
    } elseif ($action === 'add_to_site_at_once') {
        $this->addStaffToSiteAtOnce($guardIds, $siteId);
        return response()->json(['success' => true, 'msg' => 'All Staff Added To Site Successfully!']);
    } elseif ($action === 'remove_to_site_at_once') {
        $this->removeStaffFromSiteAtOnce($guardIds, $siteId);
        return response()->json(['success' => true, 'msg' => 'Staff Removed From Location Successfully!']);
    } else {
        $this->removeStaffFromSite($guardIds, $siteId);
        return response()->json(['success' => true, 'msg' => 'Staff Removed From Site Successfully!']);
    }
}

public function addCustomerRunSheetGuard(Request $request)
{
    $action = $request->input('action');
    $guardIds = $request->input('guard_id');
    $runSheetId = (int) $request->input('run_sheet_id');

    if (!is_array($guardIds)) {
        $guardIds = [$guardIds]; // Ensure $guardIds is an array
    }

    if ($action === 'add_to_site') {
        $this->addStaffToRunSheet($guardIds, $runSheetId);
        return response()->json(['success' => true, 'msg' => 'Staff Added To RunSheet Successfully!']);
    } elseif ($action === 'add_to_site_at_once') {
        $this->addStaffToRunSheetAtOnce($guardIds, $runSheetId);
        return response()->json(['success' => true, 'msg' => 'All Staff Added To RunSheet Successfully!']);
    } elseif ($action === 'remove_to_site_at_once') {
        $this->removeStaffFromRunSheetAtOnce($guardIds, $runSheetId);
        return response()->json(['success' => true, 'msg' => 'Staff Removed From RunSheet Successfully!']);
    } else {
        $this->removeStaffFromRunSheet($guardIds, $runSheetId);
        return response()->json(['success' => true, 'msg' => 'Staff Removed From RunSheet Successfully!']);
    }
}

private function addStaffToSite($guardIds, $siteId)
{
    foreach ($guardIds as $guardId) {
        $guard = Guard::find($guardId);
        if ($guard) {
            $siteIds = json_decode($guard->site_id, true);

            if (!is_array($siteIds)) {
                $siteIds = [];
            }

            if (!in_array($siteId, $siteIds)) {
                $siteIds[] = $siteId;
            }

            // Remove duplicate numeric keys (if any) and reindex the array
            $siteIds = array_values(array_unique($siteIds));

            $guard->site_id = json_encode($siteIds);
            $guard->save();
        }
    }
}


private function addStaffToRunSheet($guardIds, $siteId)
{
    foreach ($guardIds as $guardId) {
        $guard = Guard::find($guardId);
        if ($guard) {
            $siteIds = json_decode($guard->run_sheet_id, true);

            if (!is_array($siteIds)) {
                $siteIds = [];
            }

            if (!in_array($siteId, $siteIds)) {
                $siteIds[] = $siteId;
            }

            // Remove duplicate numeric keys (if any) and reindex the array
            $siteIds = array_values(array_unique($siteIds));

            $guard->run_sheet_id = json_encode($siteIds);
            $guard->save();
        }
    }
}

private function addStaffToSiteAtOnce($guardIds, $siteId)
{
    $guards = Guard::whereIn('id', $guardIds)->get();
    foreach ($guards as $guard) {
        $siteIds = json_decode($guard->site_id, true);

        if (!is_array($siteIds)) {
            $siteIds = [];
        }

        if (!in_array($siteId, $siteIds)) {
            $siteIds[] = $siteId;
        }

        // Remove duplicate numeric keys (if any) and reindex the array
        $siteIds = array_values(array_unique($siteIds));

        $guard->site_id = json_encode($siteIds);
        $guard->save();
    }
}

private function addStaffToRunSheetAtOnce($guardIds, $siteId)
{
    $guards = Guard::whereIn('id', $guardIds)->get();
    foreach ($guards as $guard) {
        $siteIds = json_decode($guard->run_sheet_id, true);

        if (!is_array($siteIds)) {
            $siteIds = [];
        }

        if (!in_array($siteId, $siteIds)) {
            $siteIds[] = $siteId;
        }

        // Remove duplicate numeric keys (if any) and reindex the array
        $siteIds = array_values(array_unique($siteIds));

        $guard->run_sheet_id = json_encode($siteIds);
        $guard->save();
    }
}

private function removeStaffFromSiteAtOnce($guardIds, $siteId)
{
    foreach ($guardIds as $guardId) {
        $guard = Guard::find($guardId);
        if ($guard) {
            $siteIds = json_decode($guard->site_id, true);

            if (!is_array($siteIds)) {
                $siteIds = [];
            }

            $index = array_search($siteId, $siteIds);
            if ($index !== false) {
                unset($siteIds[$index]);
            }

            // Remove duplicate numeric keys (if any) and reindex the array
            $siteIds = array_values(array_unique($siteIds));

            $guard->site_id = json_encode($siteIds);
            $guard->save();
        }
    }
}

private function removeStaffFromRunSheetAtOnce($guardIds, $siteId)
{
    foreach ($guardIds as $guardId) {
        $guard = Guard::find($guardId);
        if ($guard) {
            $siteIds = json_decode($guard->run_sheet_id, true);

            if (!is_array($siteIds)) {
                $siteIds = [];
            }

            $index = array_search($siteId, $siteIds);
            if ($index !== false) {
                unset($siteIds[$index]);
            }

            // Remove duplicate numeric keys (if any) and reindex the array
            $siteIds = array_values(array_unique($siteIds));

            $guard->run_sheet_id = json_encode($siteIds);
            $guard->save();
        }
    }
}

private function removeStaffFromSite($guardIds, $siteId)
{
    foreach ($guardIds as $guardId) {
        $guard = Guard::find($guardId);
        if ($guard) {
            $siteIds = json_decode($guard->site_id, true);

            if (!is_array($siteIds)) {
                $siteIds = [];
            }

            $index = array_search($siteId, $siteIds);
            if ($index !== false) {
                unset($siteIds[$index]);
            }

            // Remove duplicate numeric keys (if any) and reindex the array
            $siteIds = array_values(array_unique($siteIds));

            $guard->site_id = json_encode($siteIds);
            $guard->save();
        }
    }
}

private function removeStaffFromRunSheet($guardIds, $siteId)
{
    foreach ($guardIds as $guardId) {
        $guard = Guard::find($guardId);
        if ($guard) {
            $siteIds = json_decode($guard->run_sheet_id, true);

            if (!is_array($siteIds)) {
                $siteIds = [];
            }

            $index = array_search($siteId, $siteIds);
            if ($index !== false) {
                unset($siteIds[$index]);
            }

            // Remove duplicate numeric keys (if any) and reindex the array
            $siteIds = array_values(array_unique($siteIds));

            $guard->run_sheet_id = json_encode($siteIds);
            $guard->save();
        }
    }
}


// public function getGuardbySite(Request $request)
// {
//    $site_id = (int) $request->site_id;
//    $guard = Guard::whereJsonContains('site_id', $site_id)->select('id', 'first_name', 'middle_name', 'last_name')->get();
//    return response()->json(['success' => true, 'data' => $guard]);
// }

public function getGuardbySite(Request $request)
{
    $start = Carbon::now()->startOfWeek();
    $end = Carbon::now()->endOfWeek();

    $site_id = (int) $request->site_id;
    $site = Site::find($site_id);
    $state = $site->state;

   //  $guards = Guard::whereJsonContains('site_id', $site_id)
   //      ->where('guard_status', 'active')
   //      ->where('admin_approval_status', 'active')
   //      ->where('is_available', 'yes')
   //      ->orderBy('first_name')
   //      ->get();
   $guards = Guard::where('guard_status', 'active')
        ->where('admin_approval_status', 'active')
        ->where('is_available', 'yes')
        ->orderBy('first_name')
        ->get();

    foreach ($guards as $guard) {
        if ($request->has('state')) {
            $guard->hours = JobRoster::where('start', '>=', $start)
                ->where('start', '<=', $end)
                ->where('guard_id', $guard->id)
                ->sum('hours');
        } else {
            $guard->hours = JobRoster::where('start', '>=', $start)
                ->where('start', '<=', $end)
                ->where('guard_id', $guard->id)
                ->sum('hours');
        }
    }
    $is_holiday = false;
    if(isset($request->date)){
       $date = $request->date;
       $date = explode(' ', $date);
       $date = str_replace('-', '', $date[0]);
       $checkHoliday = DB::table('public_holidays')->where('date', $date)->first();
       !empty($checkHoliday) ? $is_holiday = true : $is_holiday = false;
    }
    $gd = GetGuardBySiteResource::collection($guards);
    return response()->json(['success' => true, 'data' => $gd, 'is_holiday' => $is_holiday]);
}
public function getGuardbyRunsheet(Request $request)
{
    $start = Carbon::now()->startOfWeek();
    $end = Carbon::now()->endOfWeek();

    $runsheet_id = (int) $request->runsheet_id;
    $runSheet = RunSheet::find($runsheet_id);
   //  $state = $runSheet->state;

    $guards = Guard::whereJsonContains('run_sheet_id', $runsheet_id)
        ->where('guard_status', 'active')
        ->where('admin_approval_status', 'active')
        ->where('is_available', 'yes')
        ->orderBy('first_name')
        ->get();

    foreach ($guards as $guard) {
        if ($request->has('state')) {
            $guard->hours = RunSheetJobRoster::where('start', '>=', $start)
                ->where('start', '<=', $end)
                ->where('guard_id', $guard->id)
                ->sum('hours');
        } else {
            $guard->hours = RunSheetJobRoster::where('start', '>=', $start)
                ->where('start', '<=', $end)
                ->where('guard_id', $guard->id)
                ->sum('hours');
        }
    }
    $gd = GetGuardBySiteResource::collection($guards);
    return response()->json(['success' => true, 'data' => $gd]);
}

public function inRadiusGuards(Request $request)
{
   $coords = explode(',', $request->coordinates);
   $qry = "SELECT id, first_name, middle_name, last_name, coordinates, profile_image, (6371 * acos (cos (radians(".$coords[0]."))* cos(radians(latitude))* cos( radians(".$coords[1].") - radians(longitude) )+ sin (radians(".$coords[0].") )* sin(radians(latitude)))) AS distance FROM `guards` WHERE `admin_approval_status` = 'active' and `admin_approved` = 1 and `coordinates` != '' HAVING `distance` < " . $request->radius . " ORDER BY `distance` ASC";

   // $qry = "SELECT id, first_name, middle_name, last_name, coordinates, profile_image, (((acos(sin((" . $coords[0] . "*pi()/180)) * sin((`Latitude`*pi()/180))+cos((" . $coords[0] . "*pi()/180)) * cos((`Latitude`*pi()/180)) * cos(((" . $coords[1] . "- `Longitude`)*pi()/180))))*180/pi())*60*1.1515) as distance FROM `guards` WHERE `admin_approval_status` = 'active' and `admin_approved` = 1 and `coordinates` != '' HAVING `distance` < " . ($request->radius * 1609.34) . " ORDER BY `distance` ASC";
   $query = DB::select($qry);
   if(count($query) > 0)
   {
      foreach ($query as $key => $q) {
         $cod = explode(',', $q->coordinates);
         $q->lat = trim($cod[0]);
         $q->lng = trim($cod[1]);
         if ($q->profile_image != null && $q->profile_image != '') {
            $q->profile_image = returnImgPath('guard',$q->profile_image);
         }
      }
      return response()->json(['success' => true, 'msg' => 'Staff found Successfully!', 'data' => $query]);

   }else{
      return response()->json(['success' => false, 'msg' => 'No Staff found!', 'data' => $query]);
   }
}

public function getSpecificGuards(Request $request)
{
    $query = Guard::where('state', $request->state)
        ->leftJoin('guards_documents', function ($join) {
            $join->on('guards.id', '=', 'guards_documents.guard_id')
                ->where('guards_documents.document_name', 'Security License');
        });

   //allow all guards
   //  if ($request->has('site_ids') && !empty($request->site_ids) && is_array($request->site_ids)) {
   //      $query->where(function ($que) use ($request) {
   //          foreach ($request->site_ids as $sId) {
   //              $que->orWhereJsonContains('site_id', $sId);
   //          }
   //      });
   //  } elseif (!empty($request->site_ids) && !is_array($request->site_ids)) {
   //      $query->whereJsonContains('site_id', $request->site_ids);
   //  }

   //  if ($request->has('customer_ids') && !empty($request->customer_ids) && is_array($request->customer_ids)) {
   //      $query->where(function ($que) use ($request) {
   //          foreach ($request->customer_ids as $cId) {
   //              $que->orWhereJsonContains('customer_id', $cId);
   //          }
   //      });
   //  }

    $guards = $query->where('guard_status', 'active')
        ->select('guards.id', 'guards.first_name', 'guards.middle_name', 'guards.last_name', 'guards.phone', 'guards_documents.document_no as license_no', 'guards_documents.document_expire as license_expire')
        ->orderBy('guards.first_name', 'ASC')
        ->get();

    if ($guards->count() > 0) {
        return response()->json(['success' => true, 'msg' => 'Staff found Successfully!', 'data' => $guards]);
    } else {
        return response()->json(['success' => false, 'msg' => 'No Staff found!', 'data' => $guards]);
    }
}


public function getGuardProfileTracker(Request $request)
{
  $model = JobRosterAction::query();
  $activites = $model
  ->where('roster_id', $request->guard_id)
  ->get();
  $acts = GuardProfileTrakerResource::collection($activites);
  return response()->json(['success' => true, 'data' => $acts]);
  
}

public function staffStatusActivity(Request $request)
{
    $rosterId = $request->guard_id;
    
    $actions = [
        'staff_delete',
        'staff_active',
        'staff_inactive',
        'guard_availablity_on',
        'guard_availablity_off',
        'staff_status_pending',
        'staff_status_active',
        'staff_status_new',
    ];

    $activities = JobRosterAction::whereIn('action_type', $actions)
        ->where('roster_id', $rosterId)
        ->get();

    $acts = StaffStatusActivityResource::collection($activities);

    

    return response()->json(['success' => true, 'data' => $acts]);
}





public function getGuardLeaves(Request $request)
{
   $guardLeaves = GuardLeave::where('guard_id', $request->guard_id)->get();
   $gl = guardLeavesResource::collection($guardLeaves);
   return response()->json(['success' => true, 'data' => $gl]);
}


public function getGuardsBeforeAndAfterWeekShift(Request $request)
{
  $today = date("Y-m-d H:i");
  $startTime = date("Y-m-d H:i");
  $start =  dbFormate($today);
   $next_seven_days = date('Y-m-d H:i',strtotime($start. '+ 7 day'));
   $pervious_seven_days = date('Y-m-d H:i',strtotime($start. '- 7 day'));
   $next_seven = JobRoster::where('guard_id', $request->guard_id)->where(function($q) use($startTime, $next_seven_days){
      $q->where('job_rosters.start', '>=', $startTime)->where('job_rosters.start', '<=', $next_seven_days)->orderBy('job_rosters.start' , 'ASC')
      ->join('sites', 'job_rosters.site_id', '=', 'sites.id')->select('job_rosters.*', 'sites.site_name', 'sites.site_description')->orderBy('job_rosters.start' , 'ASC');
   })->get();
   $n_s = GuardBeforeAndAfterWeekShiftsResource::collection($next_seven);
   $pervious_seven = JobRoster::where('guard_id', $request->guard_id)->where(function($q) use($startTime, $pervious_seven_days){
      $q->where('job_rosters.start', '<=', $startTime)->where('job_rosters.start', '>=', $pervious_seven_days)->orderBy('job_rosters.start' , 'ASC')
      ->join('sites', 'job_rosters.site_id', '=', 'sites.id')->select('job_rosters.*', 'sites.site_name', 'sites.site_description')->orderBy('job_rosters.start' , 'ASC');
   })->get();
   $p_s = GuardBeforeAndAfterWeekShiftsResource::collection($pervious_seven);
   return response()->json(['success' => true, 'next' => $n_s, 'pervious' => $p_s]);
}


public function addEmploymentPackChecklist(Request $request)
{
   $chk = 0;
   $emp_pack_chk = EmploymentPackChecklist::where('guard_id', $request->guard_id)->first();
   if(empty($emp_pack_chk)){
      $emp_pack_chk = new EmploymentPackChecklist();
      $chk = 1;
   }
   $emp_pack_chk->guard_id = $request->guard_id;
   $emp_pack_chk->emp_form_filled = $request->emp_form_filled;
   $emp_pack_chk->tfn_form_filled = $request->tfn_form_filled;
   $emp_pack_chk->super_form_filled = $request->super_form_filled;
   $emp_pack_chk->copy_of_passport_dob = $request->copy_of_passport_dob;
   $emp_pack_chk->copy_current_victoria_security_license = $request->copy_current_victoria_security_license;
   $emp_pack_chk->copy_security_certificate = $request->copy_security_certificate;
   $emp_pack_chk->copy_of_visa = $request->copy_of_visa;
   $emp_pack_chk->copy_current_firstaid_rsa = $request->copy_current_firstaid_rsa;
   $emp_pack_chk->copy_recent_cv = $request->copy_recent_cv;
   $emp_pack_chk->copy_driver_license = $request->copy_driver_license;
   $emp_pack_chk->save();
   if($chk = 1){
      return response()->json(['message' => "Employment Pack Checklist Add Successfully" ,  'code' => 200, 'success' => true]);
   }else{
      return response()->json(['message' => "Employment Pack Checklist Update Successfully" ,  'code' => 200, 'success' => true]);
   }
}


public function getEmploymentPackChecklist(Request $request)
{
   $getEmploymentPackChecklist = EmploymentPackChecklist::where('guard_id', $request->guard_id)->first();
   if($getEmploymentPackChecklist){
      return response()->json(['data' => $getEmploymentPackChecklist ,  'code' => 200, 'success' => true]);
   }else{
      return response()->json(['data' => '' ,  'code' => 404, 'success' => false]);
   }
}


public function getEmpDetails(Request $request)
{
   $getEmpDetails = Guard::where('id', $request->guard_id)->with('empDetails')->first();
   if($getEmpDetails){
      $ged =  new GuardEmpContractorDetailsResource($getEmpDetails);
      return response()->json(['data' => $ged ,  'code' => 200, 'success' => true]);
   }else{
      return response()->json(['data' => '' ,  'code' => 404, 'success' => false]);
   }

}



public function updateEmpDetails(Request $request)
{
   $updatempDetails = GuardWorkDetail::where('guard_id', $request->guard_id)->first();
   if($updatempDetails){
      $old_data = $updatempDetails;
      $updatempDetails->sr_name = $request->sr_name;
      $updatempDetails->home_phone = $request->home_phone;
      $updatempDetails->abn_type = $request->abn_type;
      $updatempDetails->gst = $request->gst;
      $updatempDetails->car = $request->car;
      $updatempDetails->car_reg = $request->car_reg;
      $updatempDetails->abn_no = $request->abn_no;
      $updatempDetails->tfn_file_no = $request->tfn_file_no;
      $updatempDetails->day_of_commencement = $request->day_of_commencement;
      $updatempDetails->other_qualification = $request->other_qualification;
      $updatempDetails->availability_days = $request->availability_days;
      $updatempDetails->other_res = $request->other_res;
      $updatempDetails->update();
      $updated_column = $updatempDetails->getChanges();
      jobRosterActions($request->admin_id, 'update_staff_employment_details', $updatempDetails->id, 'staff_employment_details', '',  $old_data, $updated_column);
      jobRosterActions($request->admin_id, 'update_staff_employment_details', $request->guard_id, 'staff_employment_details', '',  $old_data, $updated_column);
      
      return response()->json(['msg' => 'Record Update Successfully!' ,  'code' => 200, 'success' => true]);
   }else{
      $updatempDetails = new GuardWorkDetail();
      $updatempDetails->guard_id = $request->guard_id;
      $updatempDetails->sr_name = $request->sr_name;
      $updatempDetails->home_phone = $request->home_phone;
      $updatempDetails->abn_type = $request->abn_type;
      $updatempDetails->gst = $request->gst;
      $updatempDetails->car = $request->car;
      $updatempDetails->car_reg = $request->car_reg;
      $updatempDetails->abn_no = $request->abn_no;
      $updatempDetails->tfn_file_no = $request->tfn_file_no;
      $updatempDetails->day_of_commencement = $request->day_of_commencement;
      $updatempDetails->other_qualification = $request->other_qualification;
      $updatempDetails->availability_days = $request->availability_days;
      $updatempDetails->other_res = $request->other_res;
      $updatempDetails->save();
      jobRosterActions($request->admin_id, 'add_staff_employment_details', $updatempDetails->id, 'staff_employment_details');
      jobRosterActions($request->admin_id, 'add_staff_employment_details', $request->guard_id, 'staff_employment_details');
      return response()->json(['msg' => 'Record Added Successfully!' ,  'code' => 404, 'success' => false]);
   }
}


public function getUniFormDetails(Request $request)
{
   $getUniFormDetails = Guard::where('id', $request->guard_id)->with('guardUniForm')->first();
   if($getUniFormDetails){
      $gfd = new GaurdUniFormDetailsResource($getUniFormDetails);
      return response()->json(['data' =>  $gfd ,  'code' => 200, 'success' => true]);
   }else{
      return response()->json(['data' => '' ,  'code' => 404, 'success' => false]);
   }
}

public function updateUniFormDetails(Request $request)
{
   $updateUniFormDetails = StaffUniform::where('guard_id' , $request->guard_id)->first();
   if($updateUniFormDetails){
      $old_data = $updateUniFormDetails;
      $updateUniFormDetails->date_of_issue = dbFormate($request->date_of_issue);
      $updateUniFormDetails->signature = $request->signature;
      $updateUniFormDetails->date_of_return = dbFormate($request->date_of_return);
      $updateUniFormDetails->form_fill_date = dbFormate($request->form_fill_date);
      $updateUniFormDetails->note = $request->note;
      $updateUniFormDetails->uniform_type = $request->uni_type;
      $updateUniFormDetails->update();
      $UniFormUpdated = $updateUniFormDetails->getChanges();
      jobRosterActions($request->admin_id, 'update_staff_uniform_details', $updateUniFormDetails->id, 'staff_Uniform', $old_data, $UniFormUpdated);
      return response()->json(['msg' => 'Record Update Successfully!' ,  'code' => 200, 'success' => true]);
   }else{
      return response()->json(['msg' => 'Record Update Successfully!' ,  'code' => 404, 'success' => false]);
   }
}


public function getGuardEmergencyContactDetails(Request $request)
{
   $getEmergencyContact = Guard::where('id', $request->guard_id)->with('guardEmergencyContact','empDetails')->first();
   if($getEmergencyContact){
      $gfd = new GuardEmergencyContactResource($getEmergencyContact);
      return response()->json(['data' =>  $gfd ,  'code' => 200, 'success' => true]);
   }else{
      return response()->json(['data' => '' ,  'code' => 404, 'success' => false]);
   }
}


public function updateGuardEmergencyContactDetails(Request $request)
{
   $updateGuardEmergencyContact = GuardEmergencyContact::where('guard_id' , $request->guard_id)->first();
   $old_data = $updateGuardEmergencyContact;
   if(empty($updateGuardEmergencyContact)){
      $updateGuardEmergencyContact = new GuardEmergencyContact();
   }
   $updateGuardEmergencyContact->guard_id = $request->guard_id;
   $updateGuardEmergencyContact->name = $request->name;
   $updateGuardEmergencyContact->bank_name = $request->bank_name;
   $updateGuardEmergencyContact->bsb = $request->bsb;
   $updateGuardEmergencyContact->tax_file_no = $request->tax_file_no;
   $updateGuardEmergencyContact->superannuation_membership_number = $request->superannuation_membership_number;
   $updateGuardEmergencyContact->contact_no = $request->contact_no;
   $updateGuardEmergencyContact->relationship = $request->relationship;
   $updateGuardEmergencyContact->account_name = $request->account_name;
   $updateGuardEmergencyContact->account_type = $request->account_type;
   $updateGuardEmergencyContact->super_fund_name = $request->super_fund_name;
   $updateGuardEmergencyContact->criminal_history = json_encode($request->criminal_history);
   $updateGuardEmergencyContact->save();
   jobRosterActions($request->admin_id, 'update_staff_emergence_contact', $updateGuardEmergencyContact->id, 'staff_emergency_contact', '',  $old_data);
   return response()->json(['msg' => 'Record Save And Update Successfully!' ,  'code' => 200, 'success' => true]);
}

public function getPersonalRefrences(Request $request)
{
   $getPersonalRefrences = GuardPersonalRefrence::where('guard_id', $request->guard_id)->first();
   if($getPersonalRefrences){
      $gfd = new GuardPersonalRefrenceResource($getPersonalRefrences);
      return response()->json(['data' =>  $gfd ,  'code' => 200, 'success' => true]);
   }else{
      return response()->json(['data' => '' ,  'code' => 404, 'success' => false]);
   }
}

public function updatePersonalRefrences(Request $request)
{
   $updatePersonalRefrences = GuardPersonalRefrence::where('guard_id' , $request->guard_id)->first();
   if(empty($updatePersonalRefrences)){
      $updatePersonalRefrences = new GuardPersonalRefrence();
   }
   $updatePersonalRefrences->guard_id = $request->guard_id;
   $updatePersonalRefrences->name = $request->name;
   $updatePersonalRefrences->relationship = $request->relationship;
   $updatePersonalRefrences->contact_no = $request->contact_no;
   $updatePersonalRefrences->applicant_signature = $request->applicant_signature;
   $updatePersonalRefrences->print_full_name = $request->print_full_name;
   $updatePersonalRefrences->current_date = dbFormate($request->current_date);
   $updatePersonalRefrences->check_and_interviewed_by = $request->check_and_interviewed_by;
   $updatePersonalRefrences->save();
   
   return response()->json(['msg' => 'Record Save And Update Successfully!' ,  'code' => 200, 'success' => true]);
}

public function updateRefrence(Request $request)
{
   $updateRefrence = GuardRefrence::where('guard_id' , $request->guard_id)->first();
   $old_data = $updateRefrence;
   if(empty($updateRefrence)){
      $updateRefrence = new GuardRefrence();
   }
   $updateRefrence->guard_id = $request->guard_id;
   $updateRefrence->description = $request->description;
   $updateRefrence->phy_dis = $request->phy_dis;
   $updateRefrence->ner_dis = $request->ner_dis;
   $updateRefrence->bron_dis = $request->bron_dis;
   $updateRefrence->med_cond = $request->med_cond;
   $updateRefrence->work_inj = $request->work_inj;
   $updateRefrence->smoke = $request->smoke;
   $updateRefrence->work_history = json_encode($request->work_history);
   $updateRefrence->save();
   jobRosterActions($request->admin_id, 'update_staff_refrence_details', $updateRefrence->id, 'update_staff_refrence_details', '',  $old_data);
   return response()->json(['msg' => 'Record Save And Update Successfully!' ,  'code' => 200, 'success' => true]);
}

public function getRefrence(Request $request)
{
   $getPersonalRefrences = GuardRefrence::where('guard_id', $request->guard_id)->first();
   if($getPersonalRefrences){
      $gfd = new GuardRefrenceResource($getPersonalRefrences);
      return response()->json(['data' =>  $gfd ,  'code' => 200, 'success' => true]);
   }else{
      return response()->json(['data' => '' ,  'code' => 404, 'success' => false]);
   }
}


public function update_guard_avability(Request $request)
{
  foreach ($request->submittedAvailability as $key => $s) {
   $day = strtolower($s['day']);
   $data[$day] = $s['toggleValue'] == true ? 1 : 0;
   $data[$day.'_type'] = $s['data'];
   if ($s['data'] == 'others') {
      $data[$day.'_from'] = $s['startTime'];
      $data[$day.'_to'] = $s['endTime'];
   }
   
}

$guard = DB::table('guard_availability')->where('guard_id', $request->guard_id)->first();
if(!empty($guard))
{
   $data['updated_at'] = date('Y-m-d H:i:s');
   $added = DB::table('guard_availability')->where('guard_id', $request->guard_id)->update($data);
}else{
   $data['created_at'] = date('Y-m-d H:i:s');
   $data['guard_id'] = $request->guard_id;
   $added = DB::table('guard_availability')->insert($data);

}
if ($added) {
 return response()->json(array('success' => true, 'message' => 'Staff availability added successfully.'));
} else {
 return response()->json(array('success' => true, 'message' => 'Fail to add Staff availability!'));
}
}


public function get_guard_avability(Request $request)
{
  $guard = DB::table('guard_availability')->where('guard_id', $request->guard_id)->first();
  if (!empty($guard)) {
   $data[0]['day'] = 'Monday';
   $data[0]['toggleValue'] = $guard->monday == 1 ? true : false;
   $data[0]['data'] = $guard->monday_type;
   if ($guard->monday_type == 'others') {
   $data[0]['startTime'] = $guard->monday_from;
   $data[0]['endTime'] = $guard->monday_to;
   }else{
   $data[0]['startTime'] = '';
   $data[0]['endTime'] = '';
}
// 
   $data[1]['day'] = 'Tuesday';
   $data[1]['toggleValue'] = $guard->tuesday == 1 ? true : false;
   $data[1]['data'] = $guard->tuesday_type;
   if ($guard->tuesday_type == 'others') {
   $data[1]['startTime'] = $guard->tuesday_from;
   $data[1]['endTime'] = $guard->tuesday_to;
   }else{
   $data[1]['startTime'] = '';
   $data[1]['endTime'] = '';
}
   // 
   $data[2]['day'] = 'Wednesday';
   $data[2]['toggleValue'] = $guard->wednesday == 1 ? true : false;
   $data[2]['data'] = $guard->wednesday_type;
   if ($guard->wednesday_type == 'others') {
   $data[2]['startTime'] = $guard->wednesday_from;
   $data[2]['endTime'] = $guard->wednesday_to;
   }else{
   $data[2]['startTime'] = '';
   $data[2]['endTime'] = '';
}
   // 
   $data[3]['day'] = 'Thursday';
   $data[3]['toggleValue'] = $guard->thursday == 1 ? true : false;
   $data[3]['data'] = $guard->thursday_type;
   if ($guard->thursday_type == 'others') {
   $data[3]['startTime'] = $guard->thursday_from;
   $data[3]['endTime'] = $guard->thursday_to;
}else{
   $data[3]['startTime'] = '';
   $data[3]['endTime'] = '';
}
   // 
   $data[4]['day'] = 'Friday';
   $data[4]['toggleValue'] = $guard->friday == 1 ? true : false;
   $data[4]['data'] = $guard->friday_type;
   if ($guard->friday_type == 'others') {
   $data[4]['startTime'] = $guard->friday_from;
   $data[4]['endTime'] = $guard->friday_to;
   }else{
   $data[4]['startTime'] = '';
   $data[4]['endTime'] = '';
}
   // 
   $data[5]['day'] = 'Saturday';
   $data[5]['toggleValue'] = $guard->saturday == 1 ? true : false;
   $data[5]['data'] = $guard->saturday_type;
   if ($guard->saturday_type == 'others') {
   $data[5]['startTime'] = $guard->saturday_from;
   $data[5]['endTime'] = $guard->saturday_to;
   }else{
   $data[5]['startTime'] = '';
   $data[5]['endTime'] = '';
}
   // 
   $data[6]['day'] = 'Sunday';
   $data[6]['toggleValue'] = $guard->sunday == 1 ? true : false;
   $data[6]['data'] = $guard->sunday_type;
   if ($guard->sunday_type == 'others') {
   $data[6]['startTime'] = $guard->sunday_from;
   $data[6]['endTime'] = $guard->sunday_to;
}else{
   $data[6]['startTime'] = '';
   $data[6]['endTime'] = '';
}

   return response()->json(['success' => true, 'message' => 'Staff availability found.', 'data' => $data]);
}else{
   return response()->json(['success' => false, 'message' => 'No staff availability found.', 'data' => null]);
}
}

function distance($lat1, $lon1, $lat2, $lon2)
{   
    $lat1 = doubleval($lat1);
    $lon1 = doubleval($lon1);
    $lat2 = doubleval($lat2);
    $lon2 = doubleval($lon2);

    $theta = $lon1 - $lon2;
    $dist = sin(deg2rad($lat1)) * sin(deg2rad($lat2)) + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta));
    $dist = acos($dist);
    $dist = rad2deg($dist);
    $miles = $dist * 60 * 1.1515;
    $miles = $miles * 1.609;
    

    return $miles;

}
function check_victoria_license($request)
{
    $url = "https://www.lars.police.vic.gov.au/LARS/LARS.asp?File=/Components/Screens/PSINFP03/PSINFP03.asp?Process=SEARCH";
    $input_xml = "<XML><HEADER><PROCESS>SEARCH</PROCESS><TIMESTAMP>20211020043340</TIMESTAMP><SECURITYTOKEN>02A42A1B-588D-4EE8-8760-2A81E6221A9A</SECURITYTOKEN></HEADER><PAYLOAD><GNDTLE01 id='idSearchPane'><CONTROL name='dropdownlist'>%</CONTROL><CONTROL name='searchtext'></CONTROL><CONTROL name='SearchCriteriadropdownlist'>X</CONTROL><CONTROL name='SearchAuthNb'>" . $request->license_number . "</CONTROL><CONTROL name='Index'></CONTROL><CONTROL name='Page'>1</CONTROL></GNDTLE01></PAYLOAD></XML>";

        // new here
    $headers = array(
        "Content-type: text/xml",
        "Content-length: " . strlen($input_xml),
        "Connection: close",
    );

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $input_xml);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

    $data = curl_exec($ch);
    curl_close($ch);

    if (strpos($data, 'No Results Found')) {
        return response()->json(['success' => false, 'message' => 'Sorry! Your license is not valid according to LRD Victoria Database.']);
    } else {
        $data = explode('ALT="Spacer"/></td></tr><tr valign=\'top\' RecordKey=\'', $data);
        if (isset($data[1])) {
            $data = explode('bgcolor=\'white\' row=\'1\'  onmouseover="PSINFE04_fMouseOver(this);"  onmouseout="PSINFE04_fMouseOut(this);"  ondblclick="fDetails();"  onclick="PSINFE04_fMouseClick(this);">', $data[1]);

            $data = str_replace('</tr><tr style=\'font-size:4px\'><td align=\'right\' bgcolor=\'#BDC3D6\' colspan=\'6\'>&nbsp;</td></tr></table>
                </td></tr><tr style=\'font-size:4px\'><td align=\'right\' bgcolor=\'#BDC3D6\' colspan=\'6\'>&nbsp;</td></tr></table>
                </td></tr><tr style=\'font-size:4px\'><td align=\'right\' bgcolor=\'#BDC3D6\' colspan=\'6\'>&nbsp;</td></tr></table>', '', $data[1]);
            $data = str_replace('</tr></table>', '', $data);
            $data = str_replace('</td>', '', $data);
            $data = str_replace('&nbsp;', '', $data);
            $data = explode('<td>', $data);
            if (isset($data[4])) {
                return response()->json(['success' => true, 'message' => 'Congrats! Your License is valid and verified from the LRD Victoria Database.', 'expiry' => $data[4]]);
            } else {
                return response()->json(['success' => false, 'message' => 'Sorry! Your license is not valid according to LRD Victoria Database.']);
            }
        } else {
            return response()->json(['success' => false, 'message' => 'Sorry! Your license is not valid according to LRD Victoria Database.']);
        }
    }
}
function check_queensland_license($request, $name)
{
    $url = "https://ftlr.fairtrading.qld.gov.au/home/search?LicenceNumber=" . $request->license_number . "&GivenName=&LastName=&CompanyName=&MasterType=";
        // new here
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);

    $result = curl_exec($ch);
    curl_close($ch);
    $result = json_decode($result, true);
    if (count($result) > 0) {
        if ($result[0]['licenceNumber'] == $request->license_number) {
            $expiry = date('d/m/Y', strtotime($result[0]['expiryDateStr']));
            return response()->json(['success' => true, 'message' => 'Congrats! Your License is valid and verified from the LRD Queensland Database.', 'expiry' => $expiry]);
        } else {
            return response()->json(['success' => false, 'message' => 'Sorry! Your license is not valid according to LRD Queensland Database.', 'name' => strtolower($name)]);
        }
    } else {
        return response()->json(['success' => false, 'message' => 'Sorry! Your license is not valid according to LRD Queensland Database.']);
    }
}


function documentsOnlineVerification(Request $request)
{
    if ($request->has('guard_id')) {
        $guard = DB::table('guards')->where('id', $request->guard_id)->select('state', 'name')->first();
        if ($guard->state == 'Queensland') {
            return $this->check_queensland_license($request, $guard->name);
        } else {
            return $this->check_victoria_license($request);
        }
    } else {
        return $this->check_victoria_license($request);
    }
}



public function saveGuardLocation(Request $request, $id)
{
    $in_radius = true;
    $validationRules = [
         'time' => 'required',
         'location' => 'required',
         'jobId' => 'required',
         'guardId' => 'required'
   ];
   
   $validator = Validator::make($request->all(), $validationRules);
   
   if ($validator->fails()) {
         return response()->json([
            'status' => 'Error',
            'success' => false,
            'error' => $validator->errors()
         ]);
   }
   //  $this->setValidationRules(['time' => 'required', 'location' => 'required', 'jobId' => 'required', 'guardId' => 'required']);
   //  if ($this->isValidRequest()) {
   //    return response()->json([
   //       'status' => "OK",
   //       'success' => false, 
   //       'error' => $this->getErrors()
   //    ]);
   //  }
   //  $job = new JobResource($this->repo->getJobById($request->input('jobId'), $this->currentUser->id));
   $jobId = $request->jobId;
   $guardId = $request->guardId;
   $job = Site::with([
      'jobRoster' => function ($q) use ($guardId) {
          $q->where('job_rosters.guard_id', '=', $guardId);
      },
      'customer',
  ])->find($jobId);

    $last_send_notification = Guard::where('id', $guardId)->value('last_send_notification');
    $diff = 4;
    if ($last_send_notification != null) {
        $to_time = time();
        $from_time = $last_send_notification;
        $diff = round(abs($to_time - $from_time) / 60,2);
    }
    Guard::where('id', $guardId)->update(['last_seen' => time()]);
    if (!$request->has('location') || $request->location == '') {
        $coordinates = explode(',', '0,0');
    }else{
        $coordinates = explode(',', $request->input('location'));
    }
    $coordinates1 = explode(',', $job->coordinates);
    $internet_enabled = true;
    if ($request->has('internet_enabled') && ($request->internet_enabled == 'false' || $request->internet_enabled == 'false')) {
        $internet_enabled = false;
    }
        // print_r($job->coordinates);
    $distance = $this->distance(trim($coordinates[0]), trim($coordinates[1]), trim($coordinates1[0]), trim($coordinates1[1]) );
    if($job->alert_radius > 0){
        $alert_radius = $job->alert_radius/1000;
    }else{
        $alert_radius = 0.18;
    }
    $signin_activity = DB::table('job_roster_activites')
    ->where(['guard_id' => $job->jobRoster[0]->guard_id, 'job_roster_id' => $id, 'status' => 1])
    ->first();
    if (empty($signin_activity)) {
      //   $this->statusCode = self::STATUS_CODE_200;
        return response()->json([ 
            'statu' => "OK",
            'success' => false,
            'message' => 'Job Already completed!',
            'signin_status' => 0
        ]);
    }
    $same_time = false;
        // || ($request->has('appClose') && ($request->appClose == true || $request->appClose == 'true') && $request->appClose != false && $request->appClose != 'false')
    if ($request->has('location_enabled') && ($request->location_enabled == 'false' || $request->location_enabled == 'false')) {

        if (($request->location_enabled == 'false' || $request->location_enabled == 'false')) {
            $same_time = true;
            DB::table('job_roster_activites')->where(['guard_id' => $job->jobRoster[0]->guard_id, 'job_roster_id' => $id, 'status' => 1])->update(['last_location_time' => $request->timestamp, 'last_location' => $request->location]);
        }
    }
    if($internet_enabled == true && $request->location != '0,0' && ($distance > $alert_radius && $job->jobRoster[0]->break_status == 0 && !empty($signin_activity))) {
        DB::table('guard_location_at_job')->insert([
            'roster_id' => $id,
            'guard_id' => $job->jobRoster[0]->guard_id,
            'job_id' => $request->input('jobId'),
            'coordinates' => $request->input('location'),
            'event_time' => $request->input('time'),
            'distance' => round($distance, 2),
            'seen_status' => 'unseen'
        ]);
        $in_radius = false;

        $guard = DB::table('guards')->where('id', $job->jobRoster[0]->guard_id)->first();
        $job1 = DB::table('sites')->where('id', $request->input('jobId'))->first();
        $notification = array(
            'guard_id' => $job->jobRoster[0]->guard_id, 
            'record_id' => $id,
            'message' => $guard->name.' leave their location at '. $job1->site_name,
            'type' => 'leave_location',
            'send_time' => time(),
            'title' => 'Staff Leave their Site'
        );
        if ($diff > 3) {
        Guard::where('id', $guardId)->update(['last_send_notification' => time()]);
        DB::table('portal_notifications')->insert($notification);
        }

        DB::table('roster_complete_activity')->insert([
            'roster_id' => $id,
            'activity' => $guard->name.' leave their location.',
            'type' => 'leave_location',
            'record_id' =>  $id,
            'activity_time' => time(),
            'activity_by' => $job->jobRoster[0]->guard_id
        ]);
    }elseif ($same_time) {
        $guard = DB::table('guards')->where('id', $job->jobRoster[0]->guard_id)->first();
        $job1 = DB::table('sites')->where('id', $request->input('jobId'))->first();
        $notification = array(
            'guard_id' => $job->jobRoster[0]->guard_id, 
            'record_id' => $id,
            'message' => $guard->name.' maybe turned off their GPS or close the app at '. $job1->site_name,
            'type' => 'leave_location',
            'send_time' => time(),
            'title' => 'Staff turned off GPS or close their app'
        );
        if ($diff > 3) {
        Guard::where('id', $guardId)->update(['last_send_notification' => time()]);
        DB::table('portal_notifications')->insert($notification);
    }
        DB::table('roster_complete_activity')->insert([
            'roster_id' => $id,
            'activity' => $guard->name.' maybe turned off their GPS or close the app.',
            'type' => 'leave_location',
            'record_id' =>  $id,
            'activity_time' => time(),
            'activity_by' => $job->jobRoster[0]->guard_id
        ]);
    }
    elseif($internet_enabled == true && $request->location == '0,0')
    {
        $guard = DB::table('guards')->where('id', $job->jobRoster[0]->guard_id)->first();
        $job1 = DB::table('sites')->where('id', $request->input('jobId'))->first();
        $notification = array(
            'guard_id' => $job->jobRoster[0]->guard_id, 
            'record_id' => $id,
            'message' => 'Due to some reason we are not able to track staff at '. $job1->site_name,
            'type' => 'internet',
            'send_time' => time(),
            'title' => 'Technical Issue'
        );
        if ($diff > 3) {
        User::where('id', $guardId)->update(['last_send_notification' => time()]);
        DB::table('portal_notifications')->insert($notification);
    }
        DB::table('roster_complete_activity')->insert([
            'roster_id' => $id,
            'activity' => 'Due to some reason we are not able to track staff.',
            'type' => 'internet',
            'record_id' =>  $id,
            'activity_time' => time(),
            'activity_by' => $job->jobRoster[0]->guard_id
        ]);   
    }
    Guard::where('id', $guardId)->update(['in_radius' => $in_radius]);
    return response()->json([
        'success' => true,
        'message' => 'Event Log successfull.',
        'inRadius' => $in_radius,
        'signin_status' => 1,
        'status' => 'OK'
   ]);
}

   public function visaVarificationRecheck(Request $request){
      $response = Http::post('http://62.72.13.17/search', ["family_name"=> $request->family_name,"date_of_birth"=> $request->date_of_birth,"document_number"=> $request->passport_number,"select_country"=> $request->select_country, 'type'=>'new', 'email'=>$request->email, 'password'=>$request->password]);
      if($response){
         $jsonResponse = json_decode($response, true);
         VisaDetails::where('guard_id', $request->guard_id)->delete();
         $visaDetail = new VisaDetails();
         $visaDetail->guard_id = $request->guard_id;
         $visaDetail->guard_name = $request->family_name;
         $visaDetail->country = $request->select_country;
         $visaDetail->dob = $request->dob;
         $visaDetail->passport_no = $request->passport_number;
         $visaDetail->details = $response;
         $visaDetail->is_correct = 1;
         $visaDetail->save();
         return response()->json([
            'success' => true,
            'message' => 'Recored Founded'
         ]);
      }
      else{
         return response()->json([
            'success' => false,
            'message' => 'Recored Not Found'
         ]);

      }

   }
   public function visaVarification(Request $request){
      return $response = Http::post('http://62.72.13.17/search', ["family_name"=> $request->family_name,"date_of_birth"=> $request->date_of_birth,"document_number"=> $request->passport_number,"select_country"=> $request->select_country, 'type'=>'new', 'email'=>$request->email, 'password'=>$request->password]);
      // $statusCode = $response->status();
      // $dataArray = json_decode($response, true);
      // $encodedJson = json_encode($dataArray, JSON_PRETTY_PRINT);
      // return $encodedJson;
   }
   public function gmt_to_date($gmt)
   {
      $result=(object)[];
      $result->signin_time = $gmt;
      if(strpos($result->signin_time,'GMT')!==false){
         $result->signin_time_2= explode(" ",$result->signin_time);
         $result->signin_time_2=$result->signin_time_2[3].'-'. date('m',strtotime($result->signin_time_2[1])).'-'.$result->signin_time_2[2].' '.$result->signin_time_2[4];
      }
      else{
         if(strpos($result->signin_time,'M')!==false||strpos($result->signin_time,'T')!==false||strpos($result->signin_time,'W')!==false||strpos($result->signin_time,'F')!==false ||strpos($result->signin_time,'S')!==false)
         {
               $result->signin_time_2= explode(" ",$result->signin_time);
               $result->signin_time_2=$result->signin_time_2[3].'-'. date('m',strtotime($result->signin_time_2[1])).'-'.$result->signin_time_2[2].' '.$result->signin_time_2[4];
         }
         else{
               $result->signin_time_2=$result->signin_time;
         }
      }
      return $result->signin_time_2;
   }
   public function guard_job_rating($roster_id)
   {
      $rating=0;
      $job=DB::table('job_rosters')
      ->join('sites', 'sites.id', '=', 'job_rosters.site_id')
      ->join('job_roster_activites', 'job_roster_activites.job_roster_id', '=', 'job_rosters.id')
      ->where('job_rosters.id',$roster_id)->first();
         //   green  call 

      $green=DB::table('green_call')->where('job_id',$roster_id)->count();
      if($green==2){
         $rating+=33.2;
      }elseif($green==1){
         $rating+=16.6;
      }else{
         $rating+=0;
      }
                  //    welfare call 

      $green=DB::table('welfare_call_data')->where('job_roster_id',$roster_id)->count();
      if($green==0){
         $rating+=0;
      }else{
         $rating+=16.6;
      }

                  //status
      $status=DB::table('job_new_roster')->where('id',$roster_id)->first();
      if($status->job_status=="completed" || $status->job_status=="confirmed" ){
         $rating+=16.6;
      }else{
         $rating+=0;
      }
      DB::table('job_new_roster')->where('id',$roster_id)->update([
         'job_rating'=>$rating
      ]);
                  // return $rating;
      $guard=DB::table('job_new_roster')->where('id',$roster_id)->first();
      return true;

   }
   function auto_sign_out(Request $request)
   {
      // $config_dbs = DB::connection('mysql2')->table('business_data')->get();
      // foreach($config_dbs as $db)
      // {
         // $connectionConfig['driver'] = 'mysql';
         // $connectionConfig['host'] = env('DB_HOST');
         // $connectionConfig['database'] = $db->database_name;
         // $connectionConfig['username'] = env('DB_USERNAME');
         // $connectionConfig['password'] = env('DB_PASSWORD');
         // // Create a new database connection dynamically
         // $newConnection = 'mysql';
         // config(['database.connections.' . $newConnection => $connectionConfig]);
         // $dynamicDbConnection = DB::connection($newConnection);
         $current_time = time();
         $closeTime = $current_time + (60*60+24);
         $not_signout_shifts = DB::table('job_roster_activites')
         ->join('job_rosters', 'job_roster_activites.job_roster_id', '=','job_rosters.id')
         ->where('job_roster_activites.status', 1)
         ->where('job_rosters.end','<=',date('Y-m-d H:i:s', $closeTime))
         ->where('job_rosters.end','!=',null)
         ->select('job_roster_activites.id', 'job_roster_activites.job_roster_id', 'job_rosters.end')->get();
         $counter = 0;
         foreach ($not_signout_shifts as $not_closed) {
            $job_end_time = $not_closed->end;
            $job_end_time = strtotime($job_end_time);
            $current_time = time();
            $diff = round(($current_time - $job_end_time) / 60,2);
            if ($diff > 30) {
                  DB::table('job_roster_activites')->where('id', $not_closed->id)->update(['signout_time' => date('Y-m-d H:i:s'), 'auto_signout' => 1, 'status' => 0]);
                  DB::table('job_rosters')->where('id', $not_closed->job_roster_id)->update(['job_status' => 'completed', 'signin_status' => 0, 'in_paysheet'=>0]);
                  DB::table('roster_complete_activity')->insert([
                     'roster_id' => $not_closed->job_roster_id,
                     'activity' => 'Auto signout from the job.',
                     'type' => 'auto_signout',
                     'record_id' => $not_closed->id,
                     'activity_time' => time(),
                     'activity_by' => ''
                  ]);
                  $counter++;
            }
         }
      //    DB::disconnect($newConnection);
      // }
      return response()->json([
         'success' => true,
         'message' => 'Signout successfully',
         'closed_shifts' => $counter
      ]);
   }


   public function getAavailableGuards(Request $request, $call_from = null)
   {
      if(!isset($request->start) && !isset($request->end)){
      
         $request->merge([
            'start' => date('Y-m-d 00:00'),
            'end' => date('Y-m-d 23:59'),
         ]);
      }
      
    $prev_shift = [];
    $next_shift = [];
    $guards = '';
    $customer = Site::where('id', $request->siteId)->select('customer_id', 'trained')->first();
    // $customerId = $customer->customer_id;
    //     foreach ($request->customerId as $key => $customerId){
     
     //return $customerId = '"' . $customerId . '"';
     
     //allow all guards
    if ($customer->trained == 'yes') {
        $guards = DB::table('guards')
        //->join('guard_sites_trained', 'guard_sites_trained.guard_id', '=', 'guards.id')
        ->join('guards_documents', 'guards_documents.guard_id', '=', 'guards.id')
        //->where('guard_sites_trained.site_id', $request->siteId)
        ->where('guards.is_available', 'yes')
        ->where('guards.admin_approval_status', 'active')
        //->where('guards.address', '!=', '')
        ->where('guards.phone', '!=', '')
        ->where('guards.first_name', '!=', '')
        ->where('guards.first_name', '!=', null)
        // ->where('guards.middle_name', '!=', '')
        // ->where('guards.middle_name', '!=', null)
        // ->where('guards.last_name', '!=', null)
        // ->where('guards.last_name', '!=', '')
        ->where('guards.email', '!=', '')
        ->where('guards.email', '!=', null)     
        //   ->where('guards_documents.security_license_number', '!=', '')
        //  ->where(function ($query) {
        //     $query->where('guards_documents.document_type', '=', 'security_license')
        //      ->orWhere('guards_documents.document_no', '!=', '')->orWhere('guards_documents.file' , '!=', '');
        //  })
        //->where('guards.security_license_file', '!=', '')
      //   ->whereJsonContains('site_id', $request->siteId)
        ->where('guards.guard_status', 'active')
        ->select('guards.id', 'guards.first_name', 'guards.middle_name', 'guards.last_name', 'guards.profile_image', 'guards.state', 'guards.phone')->orderBy('first_name', 'ASC')
        ->groupBy('guards.id')->get();
    }else{
        $guards = DB::table('guards')
        ->join('guards_documents', 'guards_documents.guard_id', '=', 'guards.id')
        ->where('is_available', 'yes')
        ->where('admin_approval_status', 'active')
        ->where('address', '!=', '')
        ->where('phone', '!=', '')
        ->where('guards.first_name', '!=', '')
        ->where('guards.first_name', '!=', null)
        //   ->where('guards.middle_name', '!=', '')
        //   ->where('guards.middle_name', '!=', null)
      //   ->where('guards.last_name', '!=', null)
      //   ->where('guards.last_name', '!=', '')
      ->where('email', '!=', '')
        ->where('email', '!=', null)
        ->where(function ($query) {
           $query->where('guards_documents.document_type', 'security_license')
          ->orWhere('guards_documents.document_no', '!=', null)->orWhere('guards_documents.file' , '!=', null);
         })
         // ->where('emergency_contact_phone','!=','')
         //   ->where('security_license_number', '!=', '')
         //   ->where('security_license_file', '!=', '')
         // ->where('payroll_bank_account_number','!=','')
         // ->where('payroll_bank_name','!=','')
         // ->whereJsonContains('site_id', $request->siteId)
         ->where('guard_status', 'active')
        ->select('guards.id', 'guards.first_name', 'guards.middle_name', 'guards.last_name', 'guards.profile_image', 'guards.state', 'guards.phone')->orderBy('first_name', 'ASC')
        ->groupBy('guards.id')->get();
    }

    $available_gaurds = array();
    foreach ($guards as $guard) {
            // if ($guard->id > 0 && $guard->state != '') {
            //         config(['app.timezone' => $this->timezone[$guard->state]]);
            //         date_default_timezone_set($this->timezone[$guard->state]);
            //     }else{
            //         config(['app.timezone' => $this->timezone['Victoria']]);
            //         date_default_timezone_set($this->timezone['Victoria']);
            //     }
        $max_hours = $this->count_today_working_hours($request->start, $request->end, $guard->id);
        $guard->working_hours = $max_hours;
            // $max_hours = 0;
        $already = DB::table('job_rosters')->where('guard_id', $guard->id)->where('start', '<=', dbFormateDateTimeStart($request->start))->where('end', '>=', dbFormateDateTimeStart($request->start))->first();
        if (empty($already)) {
            $already = DB::table('job_rosters')->where('guard_id', $guard->id)->where('start', '<=', dbFormateDateTimeEnd($request->end))->where('end', '>=', dbFormateDateTimeEnd($request->end))->first();
        }
        if (empty($already)) {
            $already = DB::table('job_rosters')->where('guard_id', $guard->id)->where('start', '>=', dbFormateDateTimeStart($request->start))->where('end', '<=', dbFormateDateTimeEnd($request->end))->first();
        }
        if (empty($already)) {
            $already = DB::table('job_rosters')->where('guard_id', $guard->id)->where('start', '>=', dbFormateDateTimeStart($request->start))->where('end', '<=', dbFormateDateTimeEnd($request->end))->first();
        }
        if (empty($already)) {
            $is_available = true;
                // $validate = $this->checkGuardLastShift($guard->id, $request->start, $request->end);
                //     if (!$validate['status']) {
                //     $is_available = false;
                //     }

            $prev_shift_res =  DB::table('job_rosters')->where('guard_id', $guard->id)->where('end', '<', dbFormateDateTimeStart($request->start))->orderBy('start', 'desc')->first();
            if (!empty($prev_shift_res)) {

                $site = DB::table('sites')->where('id', $prev_shift_res->site_id)->first();
                $site_name = !empty($site) ? $site->site_name : 'N/A';
                    // $prev_shift='';
                $prev_shift = [
                    'guard_id' => $prev_shift_res->guard_id,
                    'date' => Date("d-m-Y", strtotime($prev_shift_res->start)),
                    'start' => Date("H:i", strtotime($prev_shift_res->start)),
                    'end' => Date("H:i", strtotime($prev_shift_res->end)),
                    'site' => $site_name,
                    'job_time_end' => ($prev_shift_res->end != '' && $prev_shift_res->end != null && $prev_shift_res->end > 0) ? date('Y-m-d H:i', strtotime($prev_shift_res->end)) : date("Y-m-d H:i", strtotime($prev_shift_res->end))
                ];
                    // $hours = $this->getTimeDiff(date('Y-m-d H:i', $prev_shift_res->job_end), $request->start);
                    // $hours = $this->getTimeDiff(date('m/d/Y H:i', strtotime($prev_shift_res->temp_end)), date('m/d/Y H:i', strtotime($request->start)));
                if ($prev_shift_res->end != '' && $prev_shift_res->end != null && $prev_shift_res->end > 0) {
                    $seconds = strtotime($request->start) - strtotime($prev_shift_res->end);
                } else {
                    $seconds = strtotime($request->start) - strtotime($prev_shift_res->end);
                }
                $hours = $seconds / (60 * 60);
                $guard->previous_shift_diff = $hours;

                if ($hours < 8 && $max_hours > 12) {
                    $is_available = false;
                }
            } else {
                $prev_shift = [];
            }
            $next_shift_res =  DB::table('job_rosters')->where('guard_id', $guard->id)->where('start', '>', dbFormateDateTimeStart($request->end))->orderBy('start', 'asc')->first();
            if (!empty($next_shift_res)) {

                $site = DB::table('sites')->where('id', $next_shift_res->site_id)->first();
                $site_name = !empty($site) ? $site->site_name : 'N/A';
                $next_shift = [
                    'guard_id' => $next_shift_res->guard_id,
                    'date' => Date("d-m-Y", strtotime($next_shift_res->start)),
                    'start' => Date("H:i", strtotime($next_shift_res->start)),
                    'end' => Date("H:i", strtotime($next_shift_res->end)),
                    'site' => $site_name,
                ];
                    // $hours = $this->getTimeDiff(date('m/d/Y H:i', strtotime($request->end)), date('m/d/Y H:i', strtotime($next_shift_res->temp_start)));
                $seconds = strtotime($next_shift_res->start) - strtotime($request->end);
                $hours = $seconds / (60 * 60);
                $guard->next_shift_diff = $hours;

                if ($hours < 8 && $max_hours > 12) {
                    $is_available = false;
                }
                    //   $validate = $this->checkGuardLastShift($next_shift_res->guard_id, $next_shift_res->temp_start, $next_shift_res->temp_end, $next_shift_res->event_id);
                    // if (!$validate['status']) {
                    // $is_available = false;
                    // }
            } else {
                $next_shift = [];
            }
            $guard->next_shift = $next_shift;
            $guard->prev_shift = $prev_shift;
            if ($is_available) {
                $available_gaurds[] = $guard;
            }
        }
    }
    
        // }
        
    $next_shift = '';
    $prev_shift = '';
        // if ($request->has('return') && $request->return == 'html') 
        // {

        // }else{

    if ($call_from == 'api') {
        if (count($available_gaurds) > 0) {
            return response()->json(['success' => true, 'siteId' => $request->siteId, 'guards' => $available_gaurds, 'prev_shift' => $prev_shift, 'next_shift' => $next_shift]);
        } else {
            return response()->json(['success' => false, 'siteId' => $request->siteId, 'guards' => $available_gaurds, 'prev_shift' => $prev_shift, 'next_shift' => $next_shift]);
        }
    } else {

        return response()->json(['siteId' => $request->siteId, 'guards' => $available_gaurds, 'prev_shift' => $prev_shift, 'next_shift' => $next_shift]);
    }
        // }
}

function count_today_working_hours($start, $end, $guard_id)
{
    $today_start = strtotime(date('Y-m-d 00:00:00', strtotime($start)));
    $today_end = strtotime(date('Y-m-d 23:59:59', strtotime($start)));
    if (strtotime($end) > $today_end) {
        $current_shift_today_duration = ($today_end - strtotime($start)) / (60 * 60);
    } else {
        $current_shift_today_duration = (strtotime($end) - strtotime($start)) / (60 * 60);
    }
    $today_working_hours = 0;
    $jobs_today = JobRoster::where('start', '<', Date('Y-m-d H:i', $today_start))
    ->where('end', '>', Date('Y-m-d H:i', $today_start))
    ->where('guard_id', '=', $guard_id);
    $jobs_today = $jobs_today->get();

    $jobs_today2 = JobRoster::where('start', '>=', Date('Y-m-d H:i', $today_start))
    ->where('end', '<=', Date('Y-m-d H:i', $today_end))
    ->where('guard_id', '=', $guard_id);
    $jobs_today2 = $jobs_today2->get();


    $jobs_today1 = JobRoster::where('start', '<', Date('Y-m-d H:i', $today_end))
    ->where('end', '>', Date('Y-m-d', $today_end))
    ->where('guard_id', '=', $guard_id);
    $jobs_today1 = $jobs_today1->get();


        // $jobs_today = $jobs_today->merge($jobs_today1);
        // $jobs_today = $jobs_today->merge($jobs_today2);

    foreach ($jobs_today as $jt) {
        if ($jt->job_end == '') {
            $jt->job_end = time();
        }
        if ($jt->job_start < $today_start) {
            $jt->job_start = $today_start;
        }
        if ($jt->job_end > $today_end) {
            $jt->job_end = $today_end;
        }
        $today_working_hours += (($jt->job_end - $jt->job_start) / (60 * 60));
    }
    foreach ($jobs_today2 as $jt2) {
        if ($jt2->job_end == '') {
            $jt2->job_end = time();
        }
        if ($jt2->job_start < $today_start) {
            $jt2->job_start = $today_start;
        }
        if ($jt2->job_end > $today_end) {
            $jt2->job_end = $today_end;
        }
        $today_working_hours += (($jt2->job_end - $jt2->job_start) / (60 * 60));
    }
    foreach ($jobs_today1 as $jt1) {
        if ($jt1->job_end == '') {
            $jt1->job_end = time();
        }
        if ($jt1->job_start < $today_start) {
            $jt1->job_start = $today_start;
        }
        if ($jt1->job_end > $today_end) {
            $jt1->job_end = $today_end;
        }
        $today_working_hours += (($jt1->job_end - $jt1->job_start) / (60 * 60));
    }
    return round($today_working_hours + $current_shift_today_duration);
}




public function saveAndUpdateTrainedGuardOnSite(Request $request) {
   
   if($request->has('guard_trained_on_sites') && !empty($request->guard_trained_on_sites)){
      foreach ($request->guard_trained_on_sites as $key => $gt){
       if(isset($gt['id']) && !empty($gt['id'])){
         $guard_trained = GuardTrainedSite::where('id', $gt['id'])->first();
         $old_data = $guard_trained;
         $guard_trained->guard_id = $gt['guard_id']; 
         $guard_trained->site_id = $gt['site_id']; 
         $guard_trained->customer_id = $gt['customer_id'];
         $guard_trained->status = $gt['status'];
         $guard_trained->save();
         jobRosterActions($request->admin_id, 'update_guard_trained_on_sites', $guard_trained->id, 'guard_trained_on_sites',$old_data );
         jobRosterActions($request->admin_id, 'update_guard_trained_on_sites', $gt['guard_id'], 'guard_trained_on_sites',$old_data );
         return response()->json(['success' => true, 'message' => 'Staff training added Successfully!']);
      }else{
         $guard_trained = new GuardTrainedSite();
         $guard_trained->guard_id = $gt['guard_id']; 
         $guard_trained->site_id = $gt['site_id']; 
         $guard_trained->customer_id = $gt['customer_id'];
         $guard_trained->status = $gt['status'];
         $guard_trained->save();
         jobRosterActions($request->admin_id, 'add_guard_trained_on_sites', $guard_trained->id, 'guard_trained_on_sites');
         jobRosterActions($request->admin_id, 'add_guard_trained_on_sites', $gt['guard_id'], 'guard_trained_on_sites');
         return response()->json(['success' => true, 'message' => 'Staff training updated Successfully!']);
      }

      
   }
 }else{
   return response()->json(['success' => false, 'message' => 'Please filled all fields!']);
 }

}

public function getAllTrainedGuardOnSite(Request $request) {
   
   $allTrainedGuard = GuardTrainedSite::where('guard_id', $request->guard_id)->orderBy('first_name', 'asc')->get();
   if($allTrainedGuard){
      $gt = EditTrainedGuardResource::collection($allTrainedGuard);
      return response()->json(['success' => true, 'data' => $gt]);
   }else{
      return response()->json(['success' => false, 'error' => 'No record found']);
   }
}

public function editTrainedGuardOnSite(Request $request) {
   
   $editTrainedGuard = GuardTrainedSite::where('guard_id', $request->guard_id)->get();
   if($editTrainedGuard){
      $gt = EditTrainedGuardResource::collection($editTrainedGuard);
      return response()->json(['success' => true, 'data' => $gt]);
   }else{
      return response()->json(['success' => false, 'error' => 'No record found']);
   }
}

 public function deleteTrainedGuardOnSite(Request $request) {
   
   $deleteTrainedGuard = GuardTrainedSite::where('id', $request->id)->first();
   if($deleteTrainedGuard){
      $old_data = $deleteTrainedGuard;
      $deleteTrainedGuard->delete();
      jobRosterActions($request->admin_id, 'delete_guard_trained_on_sites', $deleteTrainedGuard->id, 'guard_trained_on_sites', $old_data);
      jobRosterActions($request->admin_id, 'delete_guard_trained_on_sites', $deleteTrainedGuard->id, 'guard_trained_on_sites', $old_data);
      return response()->json(['success' => true, 'message' => 'Staff training deleted Successfully!']);
   }else{
      return response()->json(['success' => false, 'error' => 'No record found']);
   }
 }
   public function getVisaDetails(){
      $visaDetails = VisaDetails::orderBy('guard_name', 'asc')->get();
      return response()->json(['success' => true, 'data' => VisaDetailResource::collection($visaDetails)]);
   }

   public function getGuardDocumentName(Request $request){

      $data = '';
      if($request->type == 'staff'){
         $data = GuardDocument::where('guard_id', $request->id)->select('id', 'document_name','file','created_at', 'document_expire')->orderBy('document_name', 'asc')->get();
         $dt = GuardFileNameResource::collection($data);
      }
      if($request->type == 'customer'){
         $data = CustomerDocument::where('customer_id', $request->id)->select('id', 'type as document_name', 'document as file', 'document_expire','created_at')->orderBy('type', 'asc')->get();
         $dt = GuardFileNameResource::collection($data);
      }
      if($request->type == 'contractor'){
         $data = ContractorDocument::where('contractor_id', $request->id)->select('id', 'type as document_name', 'document as file', 'document_expire','created_at')->orderBy('type', 'asc')->get();
         $dt = ContractorFileNameResource::collection($data);
      }
      return response()->json(['success' => true, 'data' => $dt ]);
      //return response()->json(['data' => ['success' => true, 'staff' => $guards_doc, 'customer' => $cus_docs, 'contractor' => $cont_docs]]);
   }

public function findGuard(Request $request)
{
   $limit = 10;
   $offset = 0;
   if($request->has('pageIndex') && $request->has('pageSize'))
   {
      $offset = $request->pageIndex * $request->pageSize;
      $limit = $request->pageSize;
   }

   //  $query = Guard::where(function ($query) use ($request) {
   //      $query->where('first_name', 'like', '%' . $request->searchTerm . '%')
   //          ->orWhere('middle_name', 'like', '%' . $request->searchTerm . '%')
   //          ->orWhere('last_name', 'like', '%' . $request->searchTerm . '%');
   //  });
   $query = Guard::where(function ($query) use ($request) {
      $searchTerm = $request->searchTerm;
          $query->whereRaw("CONCAT_WS(' ', first_name, middle_name, last_name, phone, email) LIKE ?", ['%' . $searchTerm . '%']);
      });

      $document_type = $request->document_type;
      $query = $query->with(['guardDocuments' => function($que) use ($document_type){
         $que->where('document_type', $document_type);
      }]);

    if ($request->status == 'active') {
        $query->where('guard_status', 'active')
            ->where('is_available', 'yes')
            ->where('admin_approval_status', 'active');
    } elseif ($request->status == 'inactive') {
        $query->where(function ($query) {
            $query->where('is_available', 'no')->orWhere('guard_status', 'inactive');
        });
    } elseif ($request->status == 'pending') {
        $query->where('guard_status', 'pending');
    } elseif ($request->status == 'new') {
        $query->where(function ($query) {
            $query->where('guard_status', 'new')
                ->orWhere('guard_status', 'document_exp');
        });
    } elseif ($request->status == 'deleted') {
        $query->where('guard_status', 'deleted');
    }
    $results = $query->get();
    $guardz = AllGuardResource::collection($results);
    // Count logic
    $count = $query->count();
    $total = $query->count();

    return response()->json(['success' => true, 'data' => $guardz, 'count' => $count, 'length' => $total, 'pageIndex' => $request->pageIndex, 'pageSize' => $request->pageSize]);
}


function getStaffContractorDetails(Request $request) {
   $data = [];
   $details = DB::table('guards')
    ->select('guards.*', 'guards_documents.*', 'guard_work_details.*', 'staff_uniforms.*', 'staff_contractor_details.*')
    ->leftJoin('guards_documents', 'guards.id', '=', 'guards_documents.guard_id')
    ->leftJoin('guard_work_details', 'guards.id', '=', 'guard_work_details.guard_id')
    ->leftJoin('staff_uniforms', 'guards.id', '=', 'staff_uniforms.guard_id')
    ->leftJoin('staff_contractor_details', 'guards.id', '=', 'staff_contractor_details.guard_id')
    ->where('guards.id', $request->id)
    ->first();
    if($details->sign){
      $response = Http::get($details->sign);
      if ($response->ok()) {
         $base64Image = base64_encode($response->body());
         $base64Img = $base64Image;
         $details->base64_sign = $base64Image;
         
      } else {
            $details->base64_sign = null;
      }
    }else {
      $details->base64_sign = null;
}
   if (!empty($details)) {
       $data = [
           'name' => $details->first_name ?? null,
           'sr_name' => $details->last_name ?? null,
           'dob' => usaToAus($details->dob) ?? null,
           'email' => $details->email ?? null,
           'state' => $details->state ?? null,
           'residential_address' => $details->address ?? null,
           'postal_code' => $details->postal_code ?? null,
           'emergency_contact_phone' => $details->emergency_contact_phone ?? null,
           'emergency_contact_name' => $details->emergency_contact_name ?? null,
           'phone' => $details->phone ?? null,
       ];

       if (!empty($details->empDetails)) {
           $data['abn_no'] = $details->empDetails->abn_no ?? null;
           $data['account_no'] = $details->empDetails->bank_account_no ?? null;
           $data['bank_name'] = $details->empDetails->bank_name ?? null;
           $data['guard_document_type'] = $details->empDetails->guard_document_type ?? null;
           $data['tfn_file_no'] = $details->empDetails->tfn_file_no ?? null;
           $data['bsb'] = $details->empDetails->bsb ?? null;
       }

       if (!empty($details->guardUniForm)) {
           $data['uni_type'] = $details->guardUniForm;
       }

       if (!empty($details->guardDocuments)) {
           foreach ($details->guardDocuments as $value) {
               if ($value->document_type == 'security_license') {
                   $data['sec_lic_no'] = !empty($value->document_no) ? $value->document_no : null;
                   $data['sec_lic_exp'] = !empty($value->document_expire) ? usaToAus($value->document_expire) : null;
                   break; // Assuming you only need one security license
               }
           }
       }
       if (!empty($details->contractorDetail)) {
         $data['account_type'] = $details->contractorDetail->account_type ?? null;
         $data['bron_dis'] = $details->contractorDetail->bron_dis ?? null;
         $data['car'] = $details->contractorDetail->car ?? null;
         $data['car_reg'] = $details->contractorDetail->car_reg ?? null;
         $data['criminal_history'] = json_decode($details->contractorDetail->criminal_history) ?? null;
         $data['other_document'] = $details->contractorDetail->other_document ?? null;
         $data['interview_by'] = $details->contractorDetail->interview_by ?? null;
         $data['pr_cont_ref_no'] = $details->contractorDetail->pr_cont_ref_no ?? null;
         $data['first_comp_joining_date'] = $details->contractorDetail->first_comp_joining_date ?? null;
         $data['first_comp_ending_date'] = $details->contractorDetail->first_comp_ending_date ?? null;
         $data['second_comp_joining_date'] = $details->contractorDetail->second_comp_joining_date ?? null;
         $data['second_comp_ending_date'] = $details->contractorDetail->second_comp_ending_date ?? null;
         $data['pos_in_first_comp'] = $details->contractorDetail->pos_in_first_comp ?? null;
         $data['pos_in_second_comp'] = $details->contractorDetail->pos_in_second_comp ?? null;
         $data['other_qualification'] = json_decode($details->contractorDetail->other_qualification) ?? null;
         $data['abn_type'] = json_decode($details->contractorDetail->abn_type) ?? null;
         $data['days'] = json_decode($details->contractorDetail->days) ?? null;
         $data['date_of_issue'] = json_decode($details->contractorDetail->date_of_issue) ?? null;
         $data['date_of_return'] = json_decode($details->contractorDetail->date_of_return) ?? null;
         $data['day_of_commencement'] = $details->contractorDetail->day_of_commencement ?? null;
         $data['description'] = $details->contractorDetail->description ?? null;
         $data['duties_one'] = $details->contractorDetail->duties_one ?? null;
         $data['duties_two'] = $details->contractorDetail->duties_two ?? null;
         $data['gst'] = $details->contractorDetail->gst ?? null;
         $data['home_phone'] = $details->contractorDetail->home_phone ?? null;
         $data['med_cond'] = $details->contractorDetail->med_cond ?? null;
         $data['ner_dis'] = $details->contractorDetail->ner_dis ?? null;
         $data['note'] = $details->contractorDetail->note ?? null;
         $data['first_comp_phone'] = $details->contractorDetail->first_comp_phone ?? null;
         $data['second_comp_phone'] = $details->contractorDetail->second_comp_phone ?? null;
         $data['phy_dis'] = $details->contractorDetail->phy_dis ?? null;
         $data['ref_date'] = $details->contractorDetail->ref_date ?? null;
         $data['ref_fullname'] = $details->contractorDetail->ref_fullname ?? null;
         $data['ref_name'] = $details->contractorDetail->ref_name ?? null;
         $data['ref_relationship'] = $details->contractorDetail->ref_relationship ?? null;
         $data['relationship'] = $details->contractorDetail->relationship ?? null;
         $data['sign'] = $details->contractorDetail->sign ?? null;
         $data['smoke'] = $details->contractorDetail->smoke ?? null;
         $data['super_fund_name'] = $details->contractorDetail->super_fund_name ?? null;
         $data['superannuation_Membership'] = $details->contractorDetail->superannuation_Membership ?? null;
         $data['tax_file'] = $details->contractorDetail->tax_file ?? null;
         $data['work_inj'] = $details->contractorDetail->work_inj ?? null;
         $data['comp_name_one'] = $details->contractorDetail->comp_name_one;
         $data['comp_name_two'] = $details->contractorDetail->comp_name_two;
         $data['contact_per_one'] = $details->contractorDetail->contact_per_one;
         $data['contact_per_two'] = $details->contractorDetail->contact_per_two;
     }
   }
   return response()->json(['success' => true, 'data' => $details]);
}


public function downloadStaffContractorForm(Request $request){
      $data = [];
      $data['is_record_full'] = false;
      $details = Guard::where('id', $request->id)->with(['guardDocuments', 'empDetails', 'guardUniForm', 'contractorDetail'])->first();
      if (!empty($details)) {
          $data = [
              'name' => $details->first_name ?? null,
              'sr_name' => $details->last_name ?? null,
              'dob' => usaToAus($details->dob) ?? null,
              'email' => $details->email ?? null,
              'state' => $details->state ?? null,
              'residential_address' => $details->address ?? null,
              'postal_code' => $details->postal_code ?? null,
              'emergency_contact_phone' => $details->emergency_contact_phone ?? null,
              'emergency_contact_name' => $details->emergency_contact_name ?? null,
              'phone' => $details->phone ?? null,
          ];
   
          if (!empty($details->empDetails)) {
              $data['abn_no'] = $details->empDetails->abn_no ?? null;
              $data['account_no'] = $details->empDetails->bank_account_no ?? null;
              $data['bank_name'] = $details->empDetails->bank_name ?? null;
              $data['guard_document_type'] = $details->empDetails->guard_document_type ?? null;
              $data['tfn_file_no'] = $details->empDetails->tfn_file_no ?? null;
              $data['bsb'] = $details->empDetails->bsb ?? null;
          }
   
          if (!empty($details->guardUniForm)) {
              $data['uni_type'] = $details->guardUniForm;
          }
   
          if (!empty($details->guardDocuments)) {
              foreach ($details->guardDocuments as $value) {
                  if ($value->document_type == 'security_license') {
                      $data['sec_lic_no'] = !empty($value->document_no) ? $value->document_no : null;
                      $data['sec_lic_exp'] = !empty($value->document_expire) ? usaToAus($value->document_expire) : null;
                      break; // Assuming you only need one security license
                  }
              }
          }
          if (!empty($details->contractorDetail)) {
            $data['account_type'] = $details->contractorDetail->account_type ?? null;
            $data['bron_dis'] = $details->contractorDetail->bron_dis ?? null;
            $data['car'] = $details->contractorDetail->car ?? null;
            $data['car_reg'] = $details->contractorDetail->car_reg ?? null;
            $data['criminal_history'] = json_decode($details->contractorDetail->criminal_history) ?? null;
            $data['other_document'] = $details->contractorDetail->other_document ?? null;
            $data['interview_by'] = $details->contractorDetail->interview_by ?? null;
            $data['pr_cont_ref_no'] = $details->contractorDetail->pr_cont_ref_no ?? null;
            $data['first_comp_joining_date'] = usaToAus($details->contractorDetail->first_comp_joining_date) ?? null;
            $data['first_comp_ending_date'] = usaToAus($details->contractorDetail->first_comp_ending_date) ?? null;
            $data['second_comp_joining_date'] = usaToAus($details->contractorDetail->second_comp_joining_date) ?? null;
            $data['second_comp_ending_date'] = usaToAus($details->contractorDetail->second_comp_ending_date) ?? null;
            $data['pos_in_first_comp'] = $details->contractorDetail->pos_in_first_comp ?? null;
            $data['pos_in_second_comp'] = $details->contractorDetail->pos_in_second_comp ?? null;
            $data['other_qualification'] = json_decode($details->contractorDetail->other_qualification) ?? null;
            $data['abn_type'] = json_decode($details->contractorDetail->abn_type) ?? null;
            $data['days'] = json_decode($details->contractorDetail->days) ?? null;
            $data['date_of_issue'] = json_decode($details->contractorDetail->date_of_issue) ?? null;
            $data['date_of_return'] = json_decode($details->contractorDetail->date_of_return) ?? null;
            $data['day_of_commencement'] = $details->contractorDetail->day_of_commencement ?? null;
            $data['description'] = $details->contractorDetail->description ?? null;
            $data['duties_one'] = $details->contractorDetail->duties_one ?? null;
            $data['duties_two'] = $details->contractorDetail->duties_two ?? null;
            $data['gst'] = $details->contractorDetail->gst ?? null;
            $data['home_phone'] = $details->contractorDetail->home_phone ?? null;
            $data['med_cond'] = $details->contractorDetail->med_cond ?? null;
            $data['ner_dis'] = $details->contractorDetail->ner_dis ?? null;
            $data['note'] = $details->contractorDetail->note ?? null;
            $data['first_comp_phone'] = $details->contractorDetail->first_comp_phone ?? null;
            $data['second_comp_phone'] = $details->contractorDetail->second_comp_phone ?? null;
            $data['phy_dis'] = $details->contractorDetail->phy_dis ?? null;
            $data['ref_date'] = $details->contractorDetail->ref_date ?? null;
            $data['ref_fullname'] = $details->contractorDetail->ref_fullname ?? null;
            $data['ref_name'] = $details->contractorDetail->ref_name ?? null;
            $data['ref_relationship'] = $details->contractorDetail->ref_relationship ?? null;
            $data['relationship'] = $details->contractorDetail->relationship ?? null;
            $data['sign'] = $details->contractorDetail->sign ?? null;
            $data['smoke'] = $details->contractorDetail->smoke ?? null;
            $data['super_fund_name'] = $details->contractorDetail->super_fund_name ?? null;
            $data['superannuation_Membership'] = $details->contractorDetail->superannuation_Membership ?? null;
            $data['tax_file'] = $details->contractorDetail->tax_file ?? null;
            $data['work_inj'] = $details->contractorDetail->work_inj ?? null;
            $data['comp_name_one'] = $details->contractorDetail->comp_name_one;
            $data['comp_name_two'] = $details->contractorDetail->comp_name_two;
            $data['contact_per_one'] = $details->contractorDetail->contact_per_one;
            $data['contact_per_two'] = $details->contractorDetail->contact_per_two;
        }
      }


      if (count(array_filter($data, function($value) {
            return empty($value);
         })) === 0) {
            $data['is_record_full'] = true;
      }



        $html = view('staff_contractor_details', compact('data'));
        // echo $html;
        // exit;
        $dompdf = new Dompdf();
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        $output = $dompdf->output();
        $public_path = public_path();
        $public_path = str_replace('247StaffingSolution/public/', '', $public_path);
        $folder ='/emp_contractor_form';
        $path = $public_path.$folder;
        $file_name = time() . 'contractor_form.pdf';
        $result = file_put_contents($path.'/'.$file_name, $output);
        $name = $file_name;

        # ADD TO HISTORY TABLE TO DELETE AFTER 1 MONTH
        $transient_file = DB::table('transient_files')->insert([
         'folder' => 'emp_contractor_form',
               'file_name' => $name,  
        ]);

        return response()->json(['success' =>  true, 'message' => 'Staff Contractor Form generate successfully.','path' => 'https://'.request()->getHttpHost().'/emp_contractor_form/'.$name]);
   

}




function saveAndUpdateStaffContractorDetails(Request $request)  {

   if($request->has('bsb') ||$request->has('abn_no') || $request->has('account_no') || $request->has('account_name') || $request->has('guard_document_type') || $request->has('tfn_file_no')) {
      $empdetails = GuardWorkDetail::where('guard_id', $request->id)->first();
      if( $empdetails ) {
         $empdetails->update(['abn_no'=> !empty($request->abn_no) ? $request->abn_no : $empdetails->abn_no, 
         'bank_account_no'=> !empty($request->account_no) ? $request->account_no : $empdetails->bank_account_no,
         'bank_name'=> !empty($request->account_name) ? $request->account_name : $empdetails->bank_name,
         'guard_document_type'=> !empty($request->guard_document_type) ? $request->guard_document_type : $empdetails->guard_document_type, 
         'tfn_file_no' => !empty($request->tfn_file_no) ? $request->tfn_file_no : $empdetails->tfn_file_no,
         'bsb' => !empty($request->bsb) ? $request->bsb : $empdetails->bsb,
      ]);
      }
   }

   // if($request->has('uni_type') ) {
   //    $staffUni = StaffUniform::where('guard_id', $request->id)->first();
   //    if( $staffUni ) {
   //       foreach ($staffUni as $key => $value) {
            
   //       }
   //    }
   // }

   if(($request->has('sec_lic_no') && !empty($request->sec_lic_no)) || ($request->has('sec_lic_exp') && !empty($request->sec_lic_no))) {
      $staffDocs = GuardDocument::where('guard_id', $request->id)->where('document_type', 'security_license')->first();
      if( $staffDocs ) {
         $staffDocs->document_no = !empty($request->sec_lic_no) ? $request->sec_lic_no : $staffDocs->document_no ;
         $staffDocs->document_expire = !empty($request->sec_lic_exp) ? dbFormate($request->sec_lic_exp) : $staffDocs->document_expire;
         $staffDocs->update();
      }
   }

   if($request->has('name') || $request->has('sr_name') || $request->has('dob') || $request->has('email') || $request->has('emergency_contact_phone') || $request->has('emergency_contact_name') || $request->has('state') || $request->has('residential_address') || $request->has('postal_code')){
      $staff = Guard::where('id', $request->id)->first();
      if( $staff ) {
         $staff->first_name = !empty($request->name) ? $request->name : $staff->first_name;
         $staff->last_name = !empty($request->sr_name) ? $request->sr_name : $staff->last_name;
         $staff->dob = !empty($request->dob) ? dbFormate($request->dob) : $staff->dob;
         $staff->email = !empty($request->email) ? $request->email : $staff->email;
         $staff->emergency_contact_phone = !empty($request->emergency_contact_phone) ? $request->emergency_contact_phone : $staff->emergency_contact_phone;
         $staff->emergency_contact_name = !empty($request->emergency_contact_name) ? $request->emergency_contact_name : $staff->emergency_contact_name;
         $staff->state = !empty($request->state) ? $request->state : $staff->state;
         $staff->address = !empty($request->residential_address) ? $request->residential_address : $staff->address;
         $staff->postal_code = !empty($request->postal_code) ? $request->postal_code : $staff->$request->postal_code;
         $staff->is_form_submit = 1;
         $staff->update();
      }
   }

   $staffContDetails =  StaffContractorDetail::where('guard_id', $request->id)->first();
      if(empty($staffContDetails)){
         $staffContDetails = new StaffContractorDetail();
      }
      $staffContDetails->account_type = $request->account_type;
      $staffContDetails->guard_id = $request->id;
      $staffContDetails->bron_dis = $request->bron_dis;
      $staffContDetails->car = $request->car;
      $staffContDetails->car_reg = $request->car_reg;
      $staffContDetails->criminal_history = json_encode($request->criminal_history);
      $staffContDetails->other_document = $request->other_document;
      $staffContDetails->interview_by = $request->interview_by;
      $staffContDetails->pr_cont_ref_no = $request->pr_cont_ref_no;
      $staffContDetails->first_comp_joining_date = $request->first_comp_joining_date;
      $staffContDetails->first_comp_ending_date = $request->first_comp_ending_date;
      $staffContDetails->second_comp_joining_date	 = $request->second_comp_joining_date;
      $staffContDetails->second_comp_ending_date	 = $request->second_comp_ending_date;
      $staffContDetails->pos_in_first_comp	 = $request->pos_in_first_comp;
      $staffContDetails->pos_in_second_comp	 = $request->pos_in_second_comp;
      $staffContDetails->other_qualification	 = json_encode($request->other_qualification);
      $staffContDetails->abn_type	         = json_encode($request->abn_type);
      $staffContDetails->days	 = json_encode($request->days);
      // $staffContDetails->date_of_issue	 = $request->date_of_issue;
      // $staffContDetails->date_of_return	 = $request->date_of_return; uniform related!
      $staffContDetails->day_of_commencement	 = $request->day_of_commencement;
      $staffContDetails->description	 = $request->description;
      $staffContDetails->duties_one	 = $request->duties_one;
      $staffContDetails->duties_two	 = $request->duties_two;
      $staffContDetails->gst	 = $request->gst;
      $staffContDetails->home_phone	 = $request->home_phone;
      $staffContDetails->med_cond	 = $request->med_cond;
      $staffContDetails->ner_dis	 = $request->ner_dis;
      $staffContDetails->note	 = $request->note;
      $staffContDetails->first_comp_phone	 = $request->first_comp_phone;
      $staffContDetails->second_comp_phone	 = $request->second_comp_phone;
      $staffContDetails->comp_name_one	 = $request->comp_name_one;
      $staffContDetails->comp_name_two	 = $request->comp_name_two;
      $staffContDetails->contact_per_one	 = $request->contact_per_one;
      $staffContDetails->contact_per_two	 = $request->contact_per_two;
      $staffContDetails->phy_dis	 = $request->phy_dis;
      $staffContDetails->ref_date	 = $request->ref_date;
      $staffContDetails->ref_fullname	 = $request->ref_fullname;
      $staffContDetails->ref_name	 = $request->ref_name;
      $staffContDetails->ref_relationship	 = $request->ref_relationship;
      $staffContDetails->relationship	 = $request->relationship;
      $staffContDetails->sign	 = $request->sign;
      $staffContDetails->smoke	 = $request->smoke;
      $staffContDetails->super_fund_name	 = $request->super_fund_name;
      $staffContDetails->superannuation_Membership	 = $request->superannuation_Membership;
      $staffContDetails->tax_file	 = $request->tax_file;
      $staffContDetails->work_inj	 = $request->work_inj;
      $staffContDetails->save();
      if(isset($request->form_id)){
         $formHistory = BuildInFormHistory::where(['guard_id'=>$request->id, 'form_id'=>$request->form_id])->first();  
         $formHistory->is_data_saved = 1;
         $formHistory->update();
      }
      return response()->json(['success' => true, 'msg' => 'Record save and updated!']);
}


// public function saveAndUpdateStaffContractorDetails(Request $request, Guard $guard, GuardWorkDetail $guardWorkDetail, GuardDocument $guardDocument, StaffContractorDetail $staffContractorDetail)
// {
//    //  // Validate the request
//    //  $request->validate([
//    //      'name' => 'sometimes|string',
//    //      'sr_name' => 'sometimes|string',
//    //      'dob' => 'sometimes|date',
//    //      // Add validation rules for other fields
//    //  ]);

//     // Update GuardWorkDetail
//     if ($request->hasAny(['abn_no', 'account_no', 'account_name', 'guard_document_type'])) {
//         $guardWorkDetail->where('guard_id', $request->id)->firstOrFail()
//             ->update($request->only(['abn_no', 'account_no', 'account_name', 'guard_document_type']));
//     }

//     // Update GuardDocument
//     if ($request->hasAny(['sec_lic_no', 'sec_lic_exp'])) {
//         $guardDocument->where('guard_id', $request->id)
//             ->where('document_type', 'security_license')->firstOrFail()
//             ->update([
//                 'document_no' => $request->sec_lic_no,
//                 'document_expire' => $request->sec_lic_exp,
//             ]);
//     }

//     // Update Guard
//     if ($request->hasAny(['name', 'sr_name', 'dob', 'email', 'emergency_contact_phone', 'emergency_contact_name', 'state'])) {
//         $guard->where('id', $request->id)->firstOrFail()
//             ->update($request->only(['name', 'sr_name', 'dob', 'email', 'emergency_contact_phone', 'emergency_contact_name', 'state']));
//     }

//     // Update or create StaffContractorDetail
//     $staffContractDetail = $staffContractorDetail->firstOrNew(['guard_id' => $request->id]);
//     $staffContractDetail->fill($request->only([
//         'account_type', 'bron_dis', 'car', 'car_reg', 'criminal_history', 'other_document', 'residential_address',
//         'interview_by', 'pr_cont_ref_no', 'first_comp_joining_date', 'first_comp_ending_date',
//         'second_comp_joining_date', 'second_comp_ending_date', 'pos_in_first_comp', 'pos_in_second_comp',
//         'other_qualification', 'abn_type', 'days', 'date_of_issue', 'date_of_return', 'day_of_commencement',
//         'description', 'duties_one', 'duties_two', 'gst', 'home_phone', 'med_cond', 'ner_dis', 'note',
//         'first_comp_phone', 'second_comp_phone', 'phy_dis', 'ref_date', 'ref_fullname', 'ref_name',
//         'ref_relationship', 'relationship', 'sign', 'smoke', 'super_fund_name', 'superannuation_Membership',
//         'tax_file', 'work_inj',
//     ]));
//     $staffContractDetail->save();

//     return response()->json(['success' => true, 'msg' => 'Record saved and updated!']);
// }

public function updateCustomerIds()
{
    $guards = Guard::get();
    foreach ($guards as $guard) {
        $customerIdsArray = json_decode($guard->customer_id, true);

        if (is_array($customerIdsArray)) {
          
            $numericArray = array_values($customerIdsArray);
            $jsonString = json_encode($numericArray);

            $guard->update([
                'customer_id' => $jsonString,
            ]);
        }
    }
    return response()->json(['success' => true, 'msg' => 'Record updated!']);
}

public function importGuards(Request $request)
    {   
     try {
            $file = $request->excel;
            
            $destinationPath        = 'import/';
            $extension              = $file->getClientOriginalExtension();
            $fname                  = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
            $shortenedExtension     = substr($extension, 0, 4);
            $fileName               = sprintf('%s.%s', $fname, $shortenedExtension);
            
            $file->move($destinationPath, $fileName);

            // $guardStore = Excel::import(new GuardImport, 'import/' . $fileName);
            // $guardStore = Excel::import(new LocationImport, 'import/' . $fileName);
               $guardStore = Excel::import(new PayrateImport, 'import/' . $fileName);

            

            return response()->json(['success' => true, 'msg' => 'File Import Successfully']);
        } 
        catch (\Exception $e) {
        \Log::error('Import failed: ' . $e->getMessage(), [
            'exception' => $e,
            'file' => $request->file('excel')?->getClientOriginalName()
        ]);
        
        return response()->json([
            'success' => false,
            'message' => 'Import failed',
            'error' => $e->getMessage(),
        ], 500);
    }
   }
   public function deleteDocs(Request $request){
      if($request->path){
         $fileExtension = pathinfo($request->path, PATHINFO_EXTENSION);
         $fileName = pathinfo($request->path, PATHINFO_FILENAME);
         $filePath = public_path('guard_documents/'.$fileName.'.'.$fileExtension);
         File::delete($filePath);
         DB::table('guards_documents')->where('id', $request->id)->update(['file'=>null]);
         return response()->json([
            'success' => true,
            'message' => 'Image deleted'
         ]);
      }else{
         return response()->json([
            'success' => true,
            'message' => 'Image not found'
         ]);
         
      }
   }
   public function showChart()
   {
       return view('chart');
   }

   public function generatePDF()
   {
       $chartView = view('chart')->render();
       
       preg_match('/<img id="chartImage" src="([^"]*)"/', $chartView, $matches);
       $chartImage = $matches[1];

       $pdfView = view('pdf', compact('chartImage'))->render();

       $options = new Options();
       $options->set('isHtml5ParserEnabled', true);
       $options->set('isRemoteEnabled', true);

       $dompdf = new Dompdf($options);
       $dompdf->loadHtml($pdfView);

       $dompdf->render();

       return $dompdf->stream('chart.pdf');
   }

   //compilance data code start
   public function getGuardDocument(Request $request)
   {
   if(!empty($request->type)){
      $guard_document_type = DocumentCategory::where('document_category', $request->type)->first();
      $gdt = (new GetDocumentComplianceResource($guard_document_type));
      return response()->json([ 'success' => true, 'data' => $gdt , 'code' => 200 ]);
   }else{
      return response()->json([ 'success' => false, 'data' => '' , 'code' => 404 ]);
   } 
   }

   public function updateDocumentCategory(Request $request)
   {
   if(!empty($request->type) && !empty($request->compliance)){
      $guard_document_type = DocumentCategory::where('document_category', $request->type)->first();
      
      $guard_document_type->document_compliance = json_encode($request->compliance);
      $guard_document_type->save();

      return response()->json([ 'success' => true, 'message' => 'Document Updated', 'code' => 200 ]);
   }else{
      return response()->json([ 'success' => false, 'message' => 'Document Not Found', 'code' => 404 ]);
   } 
   }

   public function getAllReqGuardDocument(Request $request)
{
      $guard = GuardWorkDetail::where('guard_id', $request->id)->first();

      if (empty($guard->guard_document_type)) {
         return "Please First Add Your Residential Status!";
      }else{
      $guard_category = DocumentCategory::where('document_category', $guard->guard_document_type)->first();
      }

    if ($guard_category && $guard_category->document_compliance === null) {
        return response()->json([
            'success' => false,
            'message' => 'Please select compliance.',
            'code' => 400
        ]);
    }

    $cleanedJsonString = trim($guard_category->document_compliance, '"');
    $complianceArray = json_decode($cleanedJsonString, true) ?? [];

    $guardDocuments = GuardDocument::where('guard_id', $request->id)->orderBy('document_name', 'asc')->get();

    $guardDocuments->transform(function ($document) use ($complianceArray) {
      $document->doc_required = isset($complianceArray[$document->document_type]) && $complianceArray[$document->document_type] === true;
      return $document;
  });

    $grd = GetAllReqGuardDocuments::collection($guardDocuments);

    // Return the response
    return response()->json([
        'success' => true,
        'data' => $grd,
        'code' => 200
    ]);
}

public function guardStatus(Request $request)
{
   $guardDetail = GuardWorkDetail::where('guard_id', $request->id)->first();
   $guard = Guard::where('id', $request->id)->with('guardDocuments')->first();

   if (empty($guardDetail->guard_document_type)) {
      return response()->json(['message' => 'Please First Add Your Residential Status!', 'success' => false, 'code' => 400]);
   } else {
      $guard_category = DocumentCategory::where('document_category', $guardDetail->guard_document_type)->first();
   }

   $complianceArray = $guard_category && $guard_category->document_compliance !== null 
      ? json_decode(trim($guard_category->document_compliance, '"'), true) 
      : [];
      
   $today = date("Y/m/d");
   $today_time = strtotime($today);
   $expiredDocuments = [];

   if (!$request->bypass) {
      foreach ($guard->guardDocuments as $document) {
         $is_required = isset($complianceArray[$document->document_type]) && $complianceArray[$document->document_type] === true;

         if ($is_required) {
            if (empty($document->document_expire)) {
                $expiredDocuments[] = ucfirst($document->document_name) . " - Not Uploaded";
            } elseif ($today_time > strtotime($document->document_expire)) {
                $expiredDocuments[] = ucfirst($document->document_name) . " - Expired";
            }
        }
      }

      if (!empty($expiredDocuments)) {
         return response()->json([
            'message' => 'Some documents are expired!',
            'expired_documents' => $expiredDocuments,
            'success' => false,
            'code' => 400
         ]);
      }
   }

   if ($guard->guard_admin_approval == 0) {
      if ($guard->is_email_approved == 'yes') {
         $guard->guard_admin_approval = 1;
         $guard->guard_status = 'active';
         $guard->update();
         jobRosterActions($request->admin_id, 'staff_active', $guard->id, 'Staff', $old_data ?? null, '', $request->reason);

         return response()->json(['message' => 'Staff Activated', 'success' => true]);
      } else {
         return response()->json(['message' => 'First verify Staff email!', 'success' => false]);
      }
   } elseif ($guard->guard_admin_approval == 1) {
      if ($guard->is_email_approved == 'yes') {
         $guard->guard_admin_approval = 0;
         $guard->guard_status = 'inactive';
         $guard->update();
         jobRosterActions($request->admin_id, 'staff_inactive', $guard->id, 'Staff', $old_data ?? null, '', $request->reason);

         return response()->json(['message' => 'Staff Inactive', 'success' => true]);
      } else {
         return response()->json(['message' => 'First verify Staff email!', 'success' => false]);
      }
   } else {
      return response()->json(['message' => 'Staff not found!', 'success' => false]);
   }
}

public function createNewStaffSave(StoreGuardRequest $request)
{
   $customers = fetchCustoemrs();

   $cords = explode(",",$request->coordinates);
   $lat = $cords[0];
   $lng = $cords[1];
   $createNewStaff = new Guard();
   $createNewStaff->first_name = $request->first_name;
   $createNewStaff->middle_name = $request->middle_name;
   $createNewStaff->last_name = $request->last_name;
   $createNewStaff->name = $request->first_name.' '. ($request->middle_name ? $request->middle_name.' ' :'') .$request->last_name;
   $createNewStaff->email = $request->email;
   $createNewStaff->phone = $request->phone;
   $createNewStaff->password = Hash::make($request);
   $createNewStaff->address = $request->address;
   $createNewStaff->profile_image = $request->profile_image;
   $createNewStaff->guard_type = $request->guard_type;
   $createNewStaff->staff_type = $request->staff_type;
   $createNewStaff->state = $request->state;
   $createNewStaff->position = $request->position;
   $createNewStaff->dob = $request->dob;
   $createNewStaff->gender =  $request->gender;
   $createNewStaff->country =  $request->home_country;
   $createNewStaff->suburb =  $request->suburb;
   $createNewStaff->city =  $request->city;
   $createNewStaff->coordinates = $request->coordinates;
   $createNewStaff->latitude =  $lat;
   $createNewStaff->longitude =  $lng;
   $createNewStaff->postal_code =  $request->postal_code;
   $createNewStaff->emergency_contact_name =  $request->emergency_contact_name;
   $createNewStaff->emergency_contact_relation = $request->emergency_contact_relation;
   $createNewStaff->emergency_contact_phone =  $request->emergency_contact_phone;
   $createNewStaff->emergency_contact_email = $request->emergency_contact_email;
   $createNewStaff->customer_id =  json_encode($customers);
   $createNewStaff->contractor_id =  json_encode($request->contractor_id);
   $createNewStaff->profile_completion =  $request->profile_completion;
   $createNewStaff->site_id = json_encode(array());
   $createNewStaff->run_sheet_id = json_encode(array());
   $createNewStaff->guard_status = 'inactive';
   $createNewStaff->guard_postion = $request->guard_postion;
   $createNewStaff->customer_id = json_encode(fetchCustoemrs());
   $createNewStaff->joining_date = $request->joining_date;
   $createNewStaff->annual_leave_hours = $request->annual_leave;
   $createNewStaff->sick_leave_hours = $request->sick_leave;
   $createNewStaff->save();
   if(isset($request->guard_type) && $request->guard_type == 'direct'){
      $getStaff = Guard::find($createNewStaff->id);
      if ($createNewStaff->id >= 10 && $createNewStaff->id <= 99) {
         $uniqueNum = '0'.$createNewStaff->id;
      }else if($createNewStaff->id >= 0 && $createNewStaff->id <= 9){
         $uniqueNum = '00'.$createNewStaff->id;
      }else{
         $uniqueNum = $createNewStaff->id;
      }
      $connectionName = 'mysql2';
      $getBusinessName = DB::connection($connectionName)
         ->table('business_data')->where('id', $request->header('Business-Id'))
         ->select('id','sub_title')
         ->first();
      $getStaff->internal_id = $getBusinessName->sub_title.'-'.$uniqueNum;
      $getStaff->update();
   }else if(isset($request->guard_type) && $request->guard_type == 'contractor'){
      $getStaff = Guard::find($createNewStaff->id);
      if ($createNewStaff->id >= 10 && $createNewStaff->id <= 99) {
         $uniqueNum = '0'.$createNewStaff->id;
      }else if($createNewStaff->id >= 0 && $createNewStaff->id <= 9){
         $uniqueNum = '00'.$createNewStaff->id;
      }else{
         $uniqueNum = $createNewStaff->id;
      }
      $connectionName = 'mysql2';
      $getBusinessName = DB::connection($connectionName)
         ->table('business_data')->where('id', $request->header('Business-Id'))
         ->select('id','sub_title')
         ->first();
      if(isset($request->contractor_id)){
         $getContractor = Contractor::where('id', $request->contractor_id)->select('id', 'name')->first();
      }else{
         $getContractor = null;
      }
      $getStaff->internal_id = $getBusinessName->sub_title.'-'.$uniqueNum.'-'.$getContractor->name;
      $getStaff->update();
   }
   $this->guardEmailVerifay($request->email, $request->header('Business-Id'), $request->password);
   jobRosterActions($request->admin_id, 'add_new_staff', $createNewStaff->id, 'Staff');
   return response()->json(['message' => "Staff Store Successfully Please Verify Your Email" ,  'code' => 200, 'success' => true]);
}

public function quickOnboardingStaffSave(StoreGuardRequest $request)
{

   $customers = fetchCustoemrs();
   
   $quickOnboardingStaff = new Guard();
   $quickOnboardingStaff->first_name = $request->first_name;
   $quickOnboardingStaff->middle_name = $request->middle_name;
   $quickOnboardingStaff->last_name = $request->last_name;
   $quickOnboardingStaff->email = $request->email;
   $quickOnboardingStaff->password = Hash::make($request);
   $quickOnboardingStaff->phone = $request->phone;
   $quickOnboardingStaff->guard_type = $request->guard_type;
   $quickOnboardingStaff->state = $request->state;
   $quickOnboardingStaff->profile_completion = $request->profile_completion;
   $quickOnboardingStaff->site_id = json_encode(array());
   $quickOnboardingStaff->run_sheet_id = json_encode(array());
   $quickOnboardingStaff->guard_status = 'inactive';
   $quickOnboardingStaff->name = $request->first_name.' '. ($request->middle_name ? $request->middle_name.' ' :'') .$request->last_name;
   $quickOnboardingStaff->guard_postion = $request->guard_postion;
   $quickOnboardingStaff->joining_date = isset($request->joining_date) ? dbFormate($request->joining_date) : null;
   $quickOnboardingStaff->customer_id =  json_encode($customers);
   $quickOnboardingStaff->save();
   $getStaff = Guard::find($quickOnboardingStaff->id);
   if ($quickOnboardingStaff->id >= 10 && $quickOnboardingStaff->id <= 99) {
      $uniqueNum = '0'.$quickOnboardingStaff->id;
   }else if($quickOnboardingStaff->id >= 0 && $quickOnboardingStaff->id <= 9){
      $uniqueNum = '00'.$quickOnboardingStaff->id;
   }else{
      $uniqueNum = $quickOnboardingStaff->id;
   }
   $connectionName = 'mysql2';
   $getBusinessName = DB::connection($connectionName)
      ->table('business_data')->where('id', $request->header('Business-Id'))
      ->select('id','sub_title')
      ->first();
   $getStaff->internal_id = $getBusinessName->sub_title.'-'.$uniqueNum;
   $getStaff->update();
   jobRosterActions($request->admin_id, 'add_staff_quick_onboarding', $quickOnboardingStaff->id, 'Staff');
   $this->guardEmailVerifay($request->email, $request->header('Business-Id'), $request->password);
   return response()->json(['message' => "Quick Onboarding is sent to the newly created staff!" ,  'code' => 200, 'success' => true]);
}
//compliance data code end

//Guards Payslips
// public function uploadPayslips(Request $request)
// {
//     $fileName = $request->pdf;
//     $fullPath = public_path('payslip/' . $fileName);

//     if (!file_exists($fullPath)) {
//         return response()->json([
//             'success' => false,
//             'message' => 'File not found on server.',
//         ], 404);
//     }

//     $parser = new \Smalot\PdfParser\Parser();
//     $pdf    = $parser->parseFile($fullPath);
//     $pages  = $pdf->getPages();

//     $payslipFolder = storage_path('app/public/payslips');
//     if (!file_exists($payslipFolder)) {
//         mkdir($payslipFolder, 0777, true);
//     }
    
//     foreach ($pages as $pageNumber => $page) {
//         $text = $page->getText();

//         preg_match('/AMG\d+/i', $text, $matches);

//         if (!empty($matches[0])) {
//             $externalId = $matches[0];

//             $guard = DB::table('guard_external_ids')
//                         ->where('external_id', $externalId)
//                         ->first();

//             if ($guard) {
//                 $guardId = $guard->guard_id;

//                 $newPdf = new \setasign\Fpdi\Fpdi();
//                 $newPdf->AddPage();
//                 $newPdf->setSourceFile($fullPath);
//                 $tpl = $newPdf->importPage($pageNumber + 1);
//                 $newPdf->useTemplate($tpl);

//                 $newFileName = $externalId . '_' . $guardId . '_' . \Illuminate\Support\Str::random(8) . '.pdf';
//                 $newFilePath = $payslipFolder . '/' . $newFileName;

//                 $newPdf->Output($newFilePath, 'F');

//                 GuardPayslip::create([
//                     'guard_id'   => $guardId,
//                     'file_url'   => request()->getSchemeAndHttpHost() . '/storage/payslips/' . $newFileName,
//                     'start_date' => $request->start_date ?? null,
//                     'end_date'   => $request->end_date ?? null,
//                     'status' => 1,
//                 ]);

//                 $guard = Guard::where('id', $guardId)->select('id', 'notification_token')->first();
//                 $notificaion['notification_token'] = $guard['notification_token'];
//                 $notificaion['message'] = "Payslip Upload Successfully.";
//                 $notificaion['title'] = 'Payslip Uploaded.';
//                 $notificaion['page'] = 'payslip-upload';
//                 send_push_notification($notificaion);
//             }
//         }
//     }

//     if (file_exists($fullPath)) {
//         unlink($fullPath);
//     }

//     return response()->json([
//         'success' => true,
//         'message' => 'PDF processed, payslips generated.',
//     ]);
// }

//Guards Payslips
public function uploadPayslips(Request $request)
{
    $fileName = $request->pdf;
    $fullPath = public_path('payslip/' . $fileName);

    if (!file_exists($fullPath)) {
        return response()->json([
            'success' => false,
            'message' => 'File not found on server.',
        ], 404);
    }

    $parser = new \Smalot\PdfParser\Parser();
    $pdf    = $parser->parseFile($fullPath);
    $pages  = $pdf->getPages();

    $payslipFolder = storage_path('app/public/payslips');
    if (!file_exists($payslipFolder)) {
        mkdir($payslipFolder, 0777, true);
    }
    
    // Initialize counters
    $totalGuardsFound = 0;
    $successfullySent = 0;
    $failedGuards = [];
    $processedExternalIds = [];
    
    foreach ($pages as $pageNumber => $page) {
        $text = $page->getText();

        preg_match('/AMG\d+/i', $text, $matches);

        if (!empty($matches[0])) {
            $externalId = $matches[0];
            
            // Count total unique guards found in PDF
            if (!in_array($externalId, $processedExternalIds)) {
                $totalGuardsFound++;
                $processedExternalIds[] = $externalId;
            }

            $guard = DB::table('guard_external_ids')
                        ->where('external_id', $externalId)
                        ->first();

            if ($guard) {
                $guardId = $guard->guard_id;

                try {
                    $newPdf = new \setasign\Fpdi\Fpdi();
                    $newPdf->AddPage();
                    $newPdf->setSourceFile($fullPath);
                    $tpl = $newPdf->importPage($pageNumber + 1);
                    $newPdf->useTemplate($tpl);

                    $newFileName = $externalId . '_' . $guardId . '_' . \Illuminate\Support\Str::random(8) . '.pdf';
                    $newFilePath = $payslipFolder . '/' . $newFileName;

                    $newPdf->Output($newFilePath, 'F');

                    GuardPayslip::create([
                        'guard_id'   => $guardId,
                        'file_url'   => request()->getSchemeAndHttpHost() . '/storage/payslips/' . $newFileName,
                        'start_date' => $request->start_date ?? null,
                        'end_date'   => $request->end_date ?? null,
                        'status' => 1,
                    ]);

                    $guard = Guard::where('id', $guardId)->select('id', 'notification_token')->first();
                    $notificaion['notification_token'] = $guard['notification_token'];
                    $notificaion['message'] = "Payslip Upload Successfully.";
                    $notificaion['title'] = 'Payslip Uploaded.';
                    $notificaion['page'] = 'payslip-upload';
                    send_push_notification($notificaion);
                    
                    // Increment success counter
                    $successfullySent++;
                    
                } catch (\Exception $e) {
                    // Track failed guards
                    $failedGuards[] = [
                        'external_id' => $externalId,
                        'guard_id' => $guardId,
                        'error' => $e->getMessage()
                    ];
                }
            } else {
                // Track guards not found in system
                $failedGuards[] = [
                    'external_id' => $externalId,
                    'guard_id' => null,
                    'error' => 'Guard not found in system'
                ];
            }
        }
    }

    if (file_exists($fullPath)) {
        unlink($fullPath);
    }

    return response()->json([
        'success' => true,
        'message' => 'PDF processed, payslips generated.',
        'statistics' => [
            'total_guards_found' => $totalGuardsFound,
            'successfully_sent' => $successfullySent,
            'failed' => count($failedGuards),
            'failed_details' => $failedGuards
        ]
    ]);
}

public function getGuardPayslips(Request $request)
{
    try {
        $startDate = $request->start_date;
        $endDate   = $request->end_date;

        if (empty($startDate) || empty($endDate)) {
            $startDate = now()->startOfWeek()->format('Y-m-d');
            $endDate   = now()->endOfWeek()->format('Y-m-d');
        }

        $guardIds = $request->guard_id ?? [];

        $query = DB::table('guard_payslips')
            ->join('guards', 'guard_payslips.guard_id', '=', 'guards.id')
            ->where('guard_payslips.status', 1)
            ->select(
                'guard_payslips.*',
                DB::raw("CONCAT(guards.first_name, ' ', guards.last_name) as guard_name")
            );

              $query->where(function ($q) use ($startDate, $endDate) {
                $q->where('guard_payslips.start_date', '<=', $endDate)
                ->where('guard_payslips.end_date', '>=', $startDate);
            });

        if (!empty($guardIds)) {
            $query->whereIn('guard_payslips.guard_id', (array) $guardIds);
        }

        $payslips = $query->get();

        return response()->json([
            'status'   => true,
            'message'  => 'Guard payslips retrieved successfully',
            'data'     => $payslips,
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'status'  => false,
            'message' => 'Error: ' . $e->getMessage(),
        ], 500);
    }
}

public function autoUpdatePayslipStatus()
{
    $today = \Carbon\Carbon::now()->format('Y-m-d');
    
    $updatedCount = DB::table('guard_payslips')
        ->where('status', 1)
        ->whereNotNull('end_date')
        ->whereRaw('DATE(DATE_ADD(end_date, INTERVAL 2 MONTH)) <= ?', [$today])
        ->update(['status' => 0]);

    return response()->json([
        'success' => true,
        'message' => "Status automatically updated for {$updatedCount} payslips.",
        'updated_count' => $updatedCount,
        'check_date' => $today
    ]);
}

public function generateAllCertificatesZip()
    {
        $wilsonIds = [
            'AMG1735','AMG1869','AMG1927','AMG1296','AMG970','AMG1416','AMG1929',
            'AMG604','AMG1637','AMG1900','AMG1011','AMG1578','AMG1710','AMG1135','AMG1889','AMG1217','AMG1895',
            'AMG1572','AMG1101','AMG1599','AMG1840','AMG1446','AMG1558','AMG1439','AMG1907','AMG1807'
        ];

        $zipUrls = [];
        $zipFolder = 'guard_certificates';
        Storage::disk('public')->makeDirectory($zipFolder);

        foreach ($wilsonIds as $wilsonId) {
            // Step 1: Get guard_id
            $external = DB::table('guard_external_ids')
                ->where('external_id', $wilsonId)
                ->select('guard_id')
                ->first();

            if (!$external) {
                $zipUrls[$wilsonId] = ['error' => 'Wilson ID not found'];
                continue;
            }

            $guardId = $external->guard_id;

            // Step 2: Get remote certificate URLs
            $certificateUrls = DB::table('guard_questionnaire_details')
                ->where('guard_id', $guardId)
                ->whereNotNull('certificate_path')
                ->where('certificate_path', 'like', 'http%')
                ->pluck('certificate_path')
                ->toArray();

            if (empty($certificateUrls)) {
                $zipUrls[$wilsonId] = ['error' => 'No remote certificates found'];
                continue;
            }

            // Step 3: Create ZIP
            $zipFileName = "{$wilsonId}_certificates.zip";
            $zipPath = storage_path("app/public/{$zipFolder}/{$zipFileName}");

            $zip = new ZipArchive();
            if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
                $zipUrls[$wilsonId] = ['error' => 'Cannot create ZIP'];
                continue;
            }

            $tempFiles = []; // To delete later
            $addedCount = 0;

            foreach ($certificateUrls as $index => $url) {
                try {
                    $response = Http::timeout(30)->get($url);

                    if ($response->successful() && $response->header('Content-Type') === 'application/pdf') {
                        // Generate safe filename
                        $extension = pathinfo(parse_url($url, PHP_URL_PATH), PATHINFO_EXTENSION) ?: 'pdf';
                        $fileName = "{$wilsonId}_certificate_" . ($index + 1) . ".{$extension}";

                        // Save to temp
                        $tempPath = sys_get_temp_dir() . '/' . Str::random(10) . "_{$fileName}";
                        file_put_contents($tempPath, $response->body());
                        $tempFiles[] = $tempPath;

                        // Add to ZIP
                        $zip->addFile($tempPath, $fileName);
                        $addedCount++;
                    }
                } catch (\Exception $e) {
                    \Log::error("Failed to download: {$url} | Error: " . $e->getMessage());
                }
            }

            // If no files added, add a note
            if ($addedCount === 0) {
                $zip->addFromString('README.txt', "No certificates could be downloaded for {$wilsonId}\nCheck URLs or network.");
            }

            $zip->close();

            // Clean up temp files
            foreach ($tempFiles as $temp) {
                @unlink($temp);
            }

            $url = asset("storage/{$zipFolder}/{$zipFileName}");
            $zipUrls[$wilsonId] = [
                'url' => $url,
                'files_downloaded' => $addedCount,
                'total_urls' => count($certificateUrls)
            ];
        }

        return response()->json([
            'message' => 'All guards processed successfully!',
            'total_guards' => count($wilsonIds),
            'generated_at' => now()->format('d M Y h:i A'),
            'download_links' => $zipUrls
        ], 200);
    }

    public function runUpdateInductionStatus()
    {
        Artisan::call('guard:updateinductionstatus');

        return response()->json([
            'status' => 'success',
            'message' => 'Guard induction status job executed successfully',
            'output' => Artisan::output(),
        ]);
    }
}