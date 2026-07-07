<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateUserRequest;
use App\Http\Requests\StoreGuardRequest;
use App\Http\Resources\GetAdminByLoginUser;
use App\Http\Resources\subAdminResource;
use App\Http\Resources\UserProfileTrakerResource;
use PragmaRX\Google2FA\Google2FA;
use App\Models\AccesLevelDefination;
use App\Models\Contractor;
use App\Models\Customer;
use App\Models\Guard;
use App\Models\JobRosterAction;
use App\Models\User;
use Carbon\Carbon;
use GrahamCampbell\ResultType\Success;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
   public function store(StoreGuardRequest $request)
   {
      $guard = new Guard();
      $guard->first_name = $request->first_name;
      $guard->last_name = $request->last_name;
      $guard->email = $request->email;
      $guard->password = Hash::make(123456);
      $guard->phone = $request->phone;
      $guard->state = $request->state;
      $guard->save();
      return response()->json(['message' => "Staff SignUp Successfully" ,  'code' => '200', 'success' => 'true'],200);
   }
   public function enable2FA(Request $request){
      if($request->type == 'guard'){
         $guard = Guard::find($request->id);
         $guard->is_2fa_enable = $request->is_2fa_enable;
         if($request->is_2fa_enable == 1){
            $google2fa = app('pragmarx.google2fa');
            $google2fa_secret = $google2fa->generateSecretKey();
            $email = $guard->email;
            $QR_Image = $google2fa->getQRCodeInline(
               config('app.name'),
               $email,
               $google2fa_secret
            );
            $guard->google2fa_secret = $google2fa_secret;
            $guard->qr_image = $QR_Image;
         }else{
            $guard->google2fa_secret = null;
            $guard->qr_image = null;
         }
         $guard->update();
      }else if($request->type == 'customer'){
         $customer = Customer::find($request->id);
         $customer->is_2fa_enable = $request->is_2fa_enable;
         if($request->is_2fa_enable == 1){
            $google2fa = app('pragmarx.google2fa');
            $google2fa_secret = $google2fa->generateSecretKey();
            $email = $customer->email;
            $QR_Image = $google2fa->getQRCodeInline(
               config('app.name'),
               $email,
               $google2fa_secret
            );
            $customer->google2fa_secret = $google2fa_secret;
            $customer->qr_image = $QR_Image;
         }else{
            $customer->google2fa_secret = null;
            $customer->qr_image = null;
         }
         $customer->update();
      }else if($request->type == 'contractor'){
         $contractor = Contractor::find($request->id);
         $contractor->is_2fa_enable = $request->is_2fa_enable;
         if($request->is_2fa_enable == 1){
            $google2fa = app('pragmarx.google2fa');
            $google2fa_secret = $google2fa->generateSecretKey();
            $email = $contractor->email;
            $QR_Image = $google2fa->getQRCodeInline(
               config('app.name'),
               $email,
               $google2fa_secret
            );
            $contractor->google2fa_secret = $google2fa_secret;
            $contractor->qr_image = $QR_Image;
         }else{
            $contractor->google2fa_secret = null;
            $contractor->qr_image = null;
         }
         $contractor->update();
      }else{
         $user = User::find($request->id);
         $user->is_2fa_enable = $request->is_2fa_enable;
         if($request->is_2fa_enable == 1){
            $google2fa = app('pragmarx.google2fa');
            $google2fa_secret = $google2fa->generateSecretKey();
            $email = $user->email;
            $QR_Image = $google2fa->getQRCodeInline(
               config('app.name'),
               $email,
               $google2fa_secret
            );
            $user->google2fa_secret = $google2fa_secret;
            $user->qr_image = $QR_Image;
         }else{
            $user->google2fa_secret = null;
            $user->qr_image = null;
         }
         $user->update();
      }
      return response()->json([
         'success' => true,
         'message' => 'Setting has been saved'
      ]);
   }

   public function getAllSubAdmins(Request $request){
      $admins = User::select('id', 'name')->where('is_super_admin', 0)->get();
      return response()->json(['data' => $admins ,'success' => true]);
   }

   public function update(Request $request)
   {
      $admin = User::where('id', $request->id)->first();
      $old_data = $admin;
      $is_check = 0;
      $old_image = (!empty($admin->image) ? $admin->image : NULL);
      if (empty($admin)) {

         $validator = Validator::make(['email' => $request->email], [
            'email' => 'required|email|unique:users,email',
        ]);
         if ($validator->fails()) {
               $errors = $validator->errors()->all();
               return $errors;
         }
         $admin = new User();
         $is_check = 1;
         $admin->name = $request->name;
         $admin->role_id = $request->role_id;
         $admin->email = $request->email;
         $admin->password = Hash::make($request->password);
         $admin->phone = $request->phone;
         $admin->specific_customer = ($request->has('customers') ? json_encode($request->customers) : json_encode(array()));
         $admin->state = ($request->has('state') ? $request->state : '');
         $admin->image = $request->temp_img;
         $admin->status = ($request->status ? $request->status : 'inactive');
         $admin->userType = $request->userType;
         $admin->is_2fa_enable = 1;
         $admin->specific_customer = ($request->has('specific_customer') && !empty($request->specific_customer) ? json_encode($request->specific_customer) : json_encode(array()));
         $admin->specific_sites = ($request->has('specific_sites') && !empty($request->specific_sites) ? json_encode($request->specific_sites) : json_encode(array()));
         # GENRATE QR CODE SAVE INTO DB AND SEND WITH EMAIL
         // $google2fa = app('pragmarx.google2fa');
         // $google2fa_secret = $google2fa->generateSecretKey();
         // $email = $request->email;
         // $QR_Image = $google2fa->getQRCodeInline(
         //    config('app.name'),
         //    $email,
         //    $google2fa_secret
         // );
         // $admin->google2fa_secret = $google2fa_secret;
         // $admin->qr_image = $QR_Image;
         // $qr_base64 = base64_encode($QR_Image);
         $qr_base64 = Null;
         isAdminEmailVerify($request->email, $request->header('Business-Id'), $request->password);
      } else {
         $admin->name = $request->name;
         $admin->role_id = $request->role_id;
         $admin->email = $request->email;
         $admin->phone = $request->phone;
         $admin->specific_customer = ($request->has('customers') && !empty($request->customers) ? json_encode($request->customers) : json_encode(array()));
         $admin->state = ($request->has('state') ? $request->state : '');
         if ($request->has('temp_img') && !empty($request->temp_img)) {
            $temp_img = $request->temp_img;
            if (!str_starts_with($temp_img, url(''))) {
               if (!empty($old_image)) {
                  $file_path = public_path('admin/' . $old_image);
                  if (file_exists($file_path)) {
                        unlink($file_path);
                  }
               }
               $admin->image = $temp_img;
            } else {
               $admin->image = str_replace(url('') . "/admin/", "", $temp_img);
            }
      }
         $admin->status = $request->status;
         $admin->userType = $request->userType;
         $admin->specific_customer = ($request->has('specific_customer') ? json_encode($request->specific_customer) : json_encode(array()));
         $admin->specific_sites = ($request->has('specific_sites') ? json_encode($request->specific_sites) : json_encode(array()));
         // $admin->specific_sites = $request->specific_sites;  
         // $admin->code = $request->code; 
         // $admin->code_expiry = $request->code_expiry; 
         // $admin->last_login = $request->last_login; 
         // $admin->notification_token = $request->notification_token;
         if($request->has('password')  && !empty($request->password)){
            $admin->password = Hash::make($request->password); 
         }
      }
         $admin->save();
         $super_admin_email = User::where('id', $request->admin_id)->first();
         if($is_check == 1){
         if($request->status == 'active'){
               $user_name = ucwords($request->name);
               $msg = 'Welcome to AMG Security! We are thrilled to have you as a new member of our community !';
               isEmailSendSubAdmin($user_name,$msg, $request->email, $admin->status, $request->password, $qr_base64);
               $super_admin_msg = ucwords($request->name).' '.'has registered on Subway Scouts as Active Admin with this Email'.' '. $request->email;
               isEmailSendSuperAdmin($super_admin_msg, $super_admin_email->email);
         }else{
            $user_name = ucwords($request->name);
            $msg = 'Welcome to AMG Security! We are thrilled to have you as a new member of our community but your account is under verification!';
            isEmailSendSubAdmin($user_name,$msg, $request->email, $admin->status, $request->password, $qr_base64);
            $super_admin_msg = ucwords($request->name).' '.'has registered on Subway Scouts as Inactive Admin with this Email'.' '. $request->email;
            isEmailSendSuperAdmin($super_admin_msg, $super_admin_email->email);
         }
         jobRosterActions($request->admin_id, 'add_admin', $admin->id, 'users');
         return response()->json(['message' => ucwords($request->userType)." Added", 'code' => '200', 'success' => true], 200);
         }else{
            $userUpdate = $admin->getChanges();
            jobRosterActions($request->admin_id, 'update_admin', $admin->id, 'users', $old_data, $userUpdate);
            return response()->json(['message' => ucwords($request->userType)." Info Updated", 'code' => '200', 'success' => true], 200);
         }
   }

   public function deleteAdmin(Request $request)
   {
      $admin = User::where('id', $request->id)->first();
      $old_data = $admin;
      if(!empty($admin)){
         $admin->delete();
         //logging('delete', $request->id, 'login_user', 'Admin');
         jobRosterActions($request->admin_id, 'delete_admin', $admin->id, 'users', $old_data);
         return response()->json(['message' => "Admin Deleted" ,  'code' => '200', 'success' => true],200);
      }else{
         return response()->json(['message' => "Not Found" ,  'code' => '404', 'success' => true],404);
      }
   }

   public function getAccess($id){
         $access = DB::table('acces_level_defination')->where('id', $id)->first();
         $access->specific_customer =  json_decode($access->specific_customer, true);
         $access->sites =  json_decode($access->sites, true);
         return response()->json($access);
     }

     public function deleteAccess($id){
      $query=  DB::table('acces_level_defination')->where('id', $id)->delete();
      if($query){
       return response()->json(['success' => true, 'msg' => "deleted Successfully"]);
      }
   }


   public function getAdminRole(Request $request)
   {
      $role = AccesLevelDefination::pluck('id','role');
      return response()->json(['data' => $role ]); 
   }

   public function getSubAdmins(Request $request)
   {
      if(isset($request->type) && $request->type == 'saleperson'){
         $subAdmins = User::where('userType', 'saleperson')->where('is_super_admin', 0)
         ->where('status', $request->status)->select('id','name','email','phone','image', 'status','last_login','userType', 'role_id')->with('RolePermission')->orderBy('name', 'asc')->get();
         $activeSubAdminsCount = User::where('userType', 'saleperson')->where('is_super_admin', 0)
         ->where('status', 'active')->count();
   
         $inActiveSubAdminsCount = User::where('userType', 'saleperson')->where('is_super_admin', 0)
         ->where('status', 'inactive')->count();
   
         $subAd = subAdminResource::collection($subAdmins);
         return response()->json([ 'success' => true, 'data' => $subAd, 'active' =>$activeSubAdminsCount, 'inactive' => $inActiveSubAdminsCount, 'code' => 200 ]);
      }else{
         $subAdmins = User::whereNotIn('userType', ['super-admin', 'saleperson'])->where('is_super_admin', '!=', 1)->where('status', $request->status)
         ->select('id','name','email','phone','image', 'status','last_login','userType', 'role_id')->with('RolePermission')->orderBy('name', 'asc')->get();

         $activeSubAdminsCount = User::whereNotIn('userType', ['super-admin', 'saleperson'])->where('is_super_admin', '!=', 1)
         ->where('status', 'active')->count();
   
         $inActiveSubAdminsCount = User::whereNotIn('userType', ['super-admin', 'saleperson'])->where('is_super_admin', '!=', 1)
         ->where('status', 'inactive')->count();
   
         $subAd = subAdminResource::collection($subAdmins);
         return response()->json([ 'success' => true, 'data' => $subAd, 'active' =>$activeSubAdminsCount, 'inactive' => $inActiveSubAdminsCount, 'code' => 200 ]);

      }
   }

   public function getRoleUsers(){
      $roles = ['super-admin', 'saleperson'];
      $users = User::whereIn('userType', $roles)->get();
      return response()->json([
         'success' => true,
         'users' => subAdminResource::collection($users)
      ]);
   }

   public function getAllAdmins()
   {
      $admins = User::select('id', 'name','image', 'is_online')->where('status', 'active')->get();
      foreach ($admins as $key => $admin) {
         $admins[$key]['image'] = returnImgPath('admin',$admin['image']);
      }
      return response()->json([ 'success' => true,'data' => $admins, 'code' => 200 ]);
   }

   public function activeSubAdminStatus(Request $request)
   {

      $subadmin = User::where('id', $request->id)->first();
      $old_data = User::where('id', $request->id)->first();
      if($subadmin->status == "inactive"){
         $subadmin->status = 'active';
         $subadmin->save();
         $updatedsubadmin = $subadmin->getChanges();
         jobRosterActions($request->admin_id, 'active_subadmin', $subadmin->id, 'users', $old_data, $updatedsubadmin);
         return response()->json(['success' => true, 'msg' => "Status Active"]);
      }elseif($subadmin->status == "active"){
         $subadmin->status = 'inactive';
         $subadmin->save();
         $updatedsubadmin = $subadmin->getChanges();
         jobRosterActions($request->admin_id, 'inactive_subadmin', $subadmin->id, 'users', $old_data, $updatedsubadmin);
         return response()->json(['success' => true, 'msg' => "Status Inactive"]);
      }else{
         return response()->json(['success' => false, 'msg' => "User not found!"]);
      }
}


   public function getAdminsByLoginUsers(Request $request)
   {
      $getAdminsByLoginUsers = User::where('id', $request->login_user_id)->first();
      if($getAdminsByLoginUsers->userType == 'super-admin' && $getAdminsByLoginUsers->is_super_admin == 1){
         $users = User::select('id', 'name', 'image')->where('status', 'active')->orderBy('name', 'asc')->get();
         $usrs = GetAdminByLoginUser::collection($users);
         return response()->json(['success' => true, 'data' => $usrs]);
      }else{
         $users = User::where('id', $request->login_user_id)->select('id', 'name', 'image')->where('status', 'active')->orderBy('name', 'asc')->get(); 
         $usrs = GetAdminByLoginUser::collection($users);
         return response()->json(['success' => true, 'data' => $usrs]);
      }
   }


   public function getUserProfileTraker(Request $request)
   {
    $model = JobRosterAction::query();
    $activites = $model
    ->where('action_on', 'users')->get();
    $acts = UserProfileTrakerResource::collection($activites);
    return response()->json(['success' => true, 'data' => $acts]);
   }


public function storeNotificationToken(Request $request)
{
   $admin = User::where('id', $request->id)->first();
   if($admin){
      $admin->notification_token = $request->notification_token;
      $admin->update();
      return response()->json(['success' => true, 'msg' => 'notification token create successfully!']);
   }else{
      return response()->json(['success' => true, 'msg' => 'admin not found!']);
   }
}

public function updateuser(Request $request)
{
    
   $update_user = User::where('id', $request->admin_id)->first();

   $update_user->abn = $request->abn;
   $update_user->acn = $request->acn;
   $update_user->bsb = $request->bsb;
   $update_user->bank_name = $request->bank_name;
   $update_user->account_no = $request->account_no;

   $update_user->update();

   return response()->json(['message' => "User Updated Successfully!" ,  'code' => 200, 'success' => true]);
    
}
   
  
   
}
