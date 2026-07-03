<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Resources\AdminResource;
use App\Http\Resources\GetAdminResource;
use App\Models\Contractor;
use App\Models\OperationNotes;
use App\Models\portal\PortalSettings;
use Illuminate\Http\Request;
use App\Http\Controllers\ApiController;
use App\Http\Requests\ForgotPasswordRequest;
use App\Http\Resources\UserResource;
use App\Models\Customer;
use App\Models\Guard;
use App\Models\Logging;
use App\Models\TicketManagement;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

class LoginController extends ApiController
{

    public function __construct()
    {
        Config::set('auth.default.guard', 'admin-api');
        Config::set('auth.default.guard', 'customer-api');
        Config::set('auth.default.guard', 'contractor-api');
        Config::set('auth.default.guard', 'guard-api');

        
    }


    public function dbConnections()
    {
        $connectionName = 'mysql2';
        $results = DB::connection($connectionName)
            ->table('business_data')->where('hide', 1)
            ->select('id','database_name', 'title')
            ->get();
        return $results;
    }

    public function getBusinessId($id){
        return $id;
    }
    public function check2FAEnable(Request $request){
        $connectionConfig['driver'] = 'mysql';
        $connectionConfig['host'] = env('DB_HOST');
        $connectionConfig['database'] = $request->database_name;
        $connectionConfig['username'] = env('DB_USERNAME');
        $connectionConfig['password'] = env('DB_PASSWORD');
        $newConnection = 'mysql';
        config(['database.connections.' . $newConnection => $connectionConfig]);
        $dynamicDbConnection = DB::connection($newConnection);
        $admin_log = $dynamicDbConnection->table('users')->where('email', $request->email)->first();
        $guard_log = $dynamicDbConnection->table('guards')->where('email', $request->email)->first();
        $customer_log = $dynamicDbConnection->table('customers')->where('email', $request->email)->first();
        $contractor_log = $dynamicDbConnection->table('contractors')->where('email', $request->email)->first();
        if($admin_log){
            if($admin_log->is_2fa_enable == 1) return response()->json(['success'=>true]);
            else return response()->json(['success'=>false]);
        }
        if($guard_log){
            if($guard_log->is_2fa_enable == 1) return response()->json(['success'=>true]);
            else return response()->json(['success'=>false]);
        }
        if($customer_log){
            if($customer_log->is_2fa_enable == 1) return response()->json(['success'=>true]);
            else return response()->json(['success'=>false]);
        }
        if($contractor_log){
            if($contractor_log->is_2fa_enable == 1) return response()->json(['success'=>true]);
            else return response()->json(['success'=>false]);
        }
    }
    // public function login(Request $request)
    // {
    //     $request->request->add(['selectedRole' => 'admin']);
    //     $req = $request->all();
    //     $serach_db = [];
    //     $serach_business = [];
    //     $ids = [];
    //     if ($request->has('request_db') && !empty($request->request_db)) {
    //         $newHost = env('DB_HOST');
    //         $newDatabase = $request->request_db;
    //         $newUsername = env('DB_USERNAME');
    //         $newPassword = env('DB_PASSWORD');
    //         // Get the existing configuration for the MySQL connection
    //         $connectionConfig = config('database.connections.mysql');
    //         // Update the credentials in the configuration
    //         $connectionConfig['driver'] = 'mysql';
    //         $connectionConfig['host'] = $newHost;
    //         $connectionConfig['database'] = $newDatabase;
    //         $connectionConfig['username'] = $newUsername;
    //         $connectionConfig['password'] = $newPassword;
    //         // Update the configuration for the MySQL connection
    //         config(['database.connections.mysql' => $connectionConfig]);
    //     } else {
    //         $databases = $this->dbConnections();
    //         foreach ($databases as $key => $db_conn) {
    //             $connectionConfig['driver'] = 'mysql';
    //             $connectionConfig['host'] = env('DB_HOST');
    //             $connectionConfig['database'] = $db_conn->database_name;
    //             $connectionConfig['username'] = env('DB_USERNAME');
    //             $connectionConfig['password'] = env('DB_PASSWORD');
    //             // Create a new database connection dynamically
    //             $newConnection = 'mysql_' . $key;
    //             config(['database.connections.' . $newConnection => $connectionConfig]);
    //             // Use the new database connection
    //             $user = null;
    //             $admin_log = DB::connection($newConnection)->table('users')->where('email', $request->email)->first();
    //             $guard_log = DB::connection($newConnection)->table('guards')->where('email', $request->email)->first();
    //             $customer_log = DB::connection($newConnection)->table('customers')->where('email', $request->email)->first();
    //             $contractor_log = DB::connection($newConnection)->table('contractors')->where('email', $request->email)->first();

    //             if (!empty($admin_log)) {
    //                 $user = $admin_log;
    //             } elseif (!empty($guard_log)) {
    //                 $user = $guard_log;
    //             } elseif (!empty($customer_log)) {
    //                 $user = $customer_log;
    //             } elseif (!empty($contractor_log)) {
    //                 $user = $contractor_log;
    //             }

    //             if ($user !== null) {

    //                 $serach_db[] = $db_conn->database_name;
    //                 $serach_business[] = $db_conn->title;
    //                 $ids[] = $db_conn->id;
    //                 $keyValuePairs = array_combine($serach_db, $serach_business);

    //                 $convertedPairs = [];
    //                 foreach ($keyValuePairs as $name => $value) {
    //                     $index = array_search($name, $serach_db);
    //                     $convertedPairs[] = ['name' => $value, 'value' => $serach_db[$index], 'id' => $ids[$index]];
    //                 }

    //             }
    //         }
    //         if (count($serach_db) > 1) {
    //             return response()->json(['success' => true, 'data' => $convertedPairs]);
    //         }elseif(count($serach_db) == 0){
    //             return response()->json(['success' => false, 'error' => 'Email not Found!']);
    //         } else {
    //             return response()->json(['success' => true, 'data' => $convertedPairs]);
    //             // Set the new credentials for the MySQL connection
    //             // $newHost = env('DB_HOST');
    //             // $newDatabase = $serach_db[0];
    //             // $newUsername = env('DB_USERNAME');
    //             // $newPassword = env('DB_PASSWORD');
    //             // // Get the existing configuration for the MySQL connection
    //             // $connectionConfig = config('database.connections.mysql');
    //             // // Update the credentials in the configuration
    //             // $connectionConfig['driver'] = 'mysql';
    //             // $connectionConfig['host'] = env('DB_HOST');
    //             // $connectionConfig['database'] = $newDatabase;
    //             // $connectionConfig['username'] = env('DB_USERNAME');
    //             // $connectionConfig['password'] = env('DB_PASSWORD');
    //             // // Update the configuration for the MySQL connection
    //             // config(['database.connections.mysql' => $connectionConfig]);
    //         }
    //     }

    //     $connectionConfig = config('database.connections.mysql');
    //     $connectionName = 'mysql2';
    //     $business_details = DB::connection($connectionName)->table('business_data')->where('hide', 1)->where('database_name', $connectionConfig['database'])->first();

        
    //     if ($request->selectedRole == 'admin') {
    //         $this->request = $request;
    //         $this->setValidationRules(['email' => 'required|email', 'password' => 'required']);
    //         if ($this->isValidRequest()) {
    //             $this->response = ['success' => false, 'error' => $this->getErrors()];
    //             $this->statusCode = self::STATUS_CODE_200;
    //             return $this->sendResponse();
    //         }
    
    //         $googleAuthenticator = app('pragmarx.google2fa');
    //         // $qr = DB::table('google2fa_secrets')->first();
    //         $user = User::where('email', $request->input('email'))->first();
    //         if($user){
    //             if ($user && $user->status !== User::ACTIVE_STATUS) {
    //                 $this->response = ['status' => 'inactive', 'error' => 'Your account is inactive please wait for Administrator approvel.'];
    //                 $this->statusCode = self::STATUS_CODE_200;
    //                 return $this->sendResponse();
    //             }
    //             if ($user->is_email_verify !== 'yes') {
    //                 return response()->json(['error' => 'Your Email is not verified, Please first verified your email.', 'success' => false], 404);
    //             }
    //             $credentials = request(['email', 'password']);
    //             if (!$token = auth('admin-api')->attempt($credentials)) {
    //                 return response()->json(['error' => 'Unauthorized', 'success' => false], 200);
    //             }
    //             if(isset($request->otp)){
    //                 $valid = $googleAuthenticator->verifyKey($user->google2fa_secret, $request->otp);
    //                 if (!$valid) {
    //                     return response()->json([
    //                         'success' => false,
    //                         'message' => 'Wrong OTP!'
    //                     ]);
    //                 }
    //             }
    //             $now = Carbon::now();
    //             $user = User::where('email', $request->email)->first();
    //             $user->auth_token = $token;
    //             $user->is_online = 1;
    //             $user->last_login = now()->format('d-m-Y H:i:s');
    //             $user->save();
    //             $this->recordActivity($user, $action='Login');
    //             return $this->respondWithToken('admin', $token, $business_details);
    //         }   
    //         $customer = Customer::where('email', $request->input('email'))->first();
    //         if($customer){
    //             if ($customer && $customer->status !== Customer::ACTIVE_STATUS) {
    //                 $this->response = ['status' => false, 'error' => 'Your account is inactive please contact your Administrator.'];
    //                 $this->statusCode = self::STATUS_CODE_200;
    //                 return $this->sendResponse();
    //             }
    //             $credentials = request(['email', 'password']);
    //             if (!$token = auth('customer-api')->attempt($credentials)) {
    //                 return response()->json(['error' => 'Unauthorized', 'success' => false], 200);
    //             }
    //             if(isset($request->otp)){
    //                 $valid = $googleAuthenticator->verifyKey($customer->google2fa_secret, $request->otp);
    //                 if (!$valid) {
    //                     return response()->json([
    //                         'success' => false,
    //                         'message' => 'Wrong OTP!'
    //                     ]);
    //                 }
    //             }
    //             $now = Carbon::now();
    //             $customer = Customer::where('email', $request->email)->first();
    //             $customer->auth_token = $token;
    //             $customer->last_login = now()->format('d-m-Y H:i:s');
    //             $customer->save();
    //             $this->recordActivity($customer, $action='Login');
    //             return $this->respondWithToken('customer', $token, $business_details);
    //         }
    //         $contractor = Contractor::where('email', $request->input('email'))->first();
    //         if($contractor){
    //             if ($contractor && $contractor->status !== Contractor::ACTIVE_STATUS) {
    //                 $this->response = ['status' => false, 'error' => 'Your account is inactive please contact your Administrator.'];
    //                 $this->statusCode = self::STATUS_CODE_200;
    //                 return $this->sendResponse();
    //             }
    //             $credentials = request(['email', 'password']);
    //             if (!$token = auth('contractor-api')->attempt($credentials)) {
    //                 return response()->json(['error' => 'Unauthorized', 'success' => false], 200);
    //             }
    //             if(isset($request->otp)){
    //                 $valid = $googleAuthenticator->verifyKey($contractor->google2fa_secret, $request->otp);
    //                 if (!$valid) {
    //                     return response()->json([
    //                         'success' => false,
    //                         'message' => 'Wrong OTP!'
    //                     ]);
    //                 }
    //             }
    //             $now = Carbon::now();
    //             $contractor = Contractor::where('email', $request->email)->first();
    //             $contractor->auth_token = $token;
    //             $contractor->last_login = now()->format('d-m-Y H:i:s');
    //             $contractor->save();
    //             $this->recordActivity($contractor, $action='Login');
    //             return $this->respondWithToken('contractor', $token, $business_details); 
    //         }
    //         $guard = Guard::where('email', $request->input('email'))->first();
    //         if($guard){
    //             if ($guard && $guard->guard_status !== Guard::ACTIVE_STATUS) {
    //                 $this->response = ['status' => false, 'error' => 'Your account is inactive please contact your Administrator.'];
    //                 $this->statusCode = self::STATUS_CODE_200;
    //                 return $this->sendResponse();
    //             }
    //             $credentials = request(['email', 'password']);
    
    //             if (!$token = auth('guard-api')->attempt($credentials)) {
    //                 return response()->json(['error' => 'Unauthorized', 'success' => false], 200);
    //             }
    //             if(isset($request->otp)){
    //                 $valid = $googleAuthenticator->verifyKey($guard->google2fa_secret, $request->otp);
    //                 if (!$valid) {
    //                     return response()->json([
    //                         'success' => false,
    //                         'message' => 'Wrong OTP!'
    //                     ]);
    //                 }
    //             }
    //             $now = Carbon::now();
    //             $guard = Guard::where('email', $request->email)->first();
    //             $guard->auth_token = $token;
    //             $guard->last_login = now()->format('d-m-Y H:i:s');
    //             $guard->save();
    //             $this->recordActivity($guard, $action='Login');
    //             return $this->respondWithToken('guard', $token, $business_details);
    //         }else{
    //             return response()->json(['message' => 'User Not Found!', 'success' => false]);
    //         }
    //     }  
    // }
    public function login(Request $request)
    {
        // Set default role to admin
        $request->merge(['selectedRole' => 'admin']);
    
        // Validate request
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);
    
        // Load the Google Authenticator
        // $googleAuthenticator = app('pragmarx.google2fa');
           $googleAuthenticator = new \PragmaRX\Google2FA\Google2FA();
        //    $googleAuthenticator = 'abc';

    
        // Check for user in respective tables
        $user = User::where('email', $request->email)->first();
        $customer = Customer::where('email', $request->email)->first();
        $contractor = Contractor::where('email', $request->email)->first();
        $guard = Guard::where('email', $request->email)->first();
    
        // Handle inactive user account
        if ($user && $user->status !== User::ACTIVE_STATUS) {
            return response()->json(['status' => 'inactive', 'error' => 'Your account is inactive.'], 403);
        }
    
        // Handle Admin Login
        if ($user) {
            return $this->authenticateUser($user, 'admin-api', $googleAuthenticator, $request);
        }
    
        // Handle Customer Login
        if ($customer) {
            return $this->authenticateUser($customer, 'customer-api', $googleAuthenticator, $request);
        }
    
        // Handle Contractor Login
        if ($contractor) {
            return $this->authenticateUser($contractor, 'contractor-api', $googleAuthenticator, $request);
        }
    
        // Handle Guard Login
        if ($guard) {
            return $this->authenticateUser($guard, 'guard-api', $googleAuthenticator, $request);
        }
    
        return response()->json(['error' => 'Email not found!', 'success' => false], 404);
    }
    
    private function authenticateUser($user, $apiGuard, $googleAuthenticator, $request)
    {
        // Attempt authentication
        if (!$token = auth($apiGuard)->attempt($request->only(['email', 'password']))) {
            return response()->json(['error' => 'Unauthorized', 'success' => false], 401);
        }
    
        // Validate OTP if provided
        if ($request->filled('otp')) {
            $valid = $googleAuthenticator->verifyKey($user->google2fa_secret, $request->otp);
            if (!$valid) {
                return response()->json(['success' => false, 'message' => 'Wrong OTP!'], 403);
            }
        }
    
        // Update user details
        $user->auth_token = $token;
        $user->last_login = now()->format('Y-m-d H:i:s');
        $user->save();
    
        // Log activity
        $this->recordActivity($user, 'Login');
    
        // Respond with token
        return $this->respondWithToken($apiGuard, $token);
    }
    
    // private function recordActivity($user, $action)
    // {
    //     DB::table('activity_logs')->insert([
    //         'user_id' => $user->id,
    //         'action' => $action,
    //         'timestamp' => now()
    //     ]);
    // }
    
    // private function respondWithToken($apiGuard, $token)
    // {
    //     return response()->json([
    //         'success' => true,
    //         'token' => $token,
    //         'token_type' => 'bearer',
    //         'expires_in' => auth($apiGuard)->factory()->getTTL() * 60
    //     ]);
    // }
    
// private function authenticateUser($user, $apiGuard, $googleAuthenticator, $request)
// {
//     if (!$token = auth($apiGuard)->attempt(request(['email', 'password']))) {
//         return response()->json(['error' => 'Unauthorized', 'success' => false], 401);
//     }

//     if (isset($request->otp)) {
//         $valid = $googleAuthenticator->verifyKey($user->google2fa_secret, $request->otp);
//         if (!$valid) {
//             return response()->json(['success' => false, 'message' => 'Wrong OTP!'], 403);
//         }
//     }

//     $user->auth_token = $token;
//     $user->last_login = now()->format('Y-m-d H:i:s');
//     $user->save();

//     $this->recordActivity($user, 'Login');

//     return $this->respondWithToken($apiGuard, $token);
// }

    protected function respondWithToken($user_type, $token)
    {
        $apiKeys = PortalSettings::first(); 
        if ($user_type == 'admin-api') {
            
            return response()->json([
                'access_token' => $token,
                'token_type' => 'bearer',
                //'expires_in' => strtotime(date('Y-m-d H:i:s', strtotime("+60 min"))),
                'admin_id' => Auth::guard('admin-api')->user()->id,
                'admin_name' => Auth::guard('admin-api')->user()->name,
                'chat_type' => 'admin',
                'admin_email' => Auth::guard('admin-api')->user()->email,
                'userType' => Auth::guard('admin-api')->user()->userType,
                'is_received_support' => Auth::guard('admin-api')->user()->is_received_support,
                'ticket_count' => Auth::guard('admin-api')->user()->is_received_support == 1 ? TicketManagement::whereIn('status', ['open', 'answered'])->count() : TicketManagement::where('id', Auth::guard('admin-api')->user()->id)->whereIn('status', ['open', 'answered'])->count(), 
                'admin_user_type' => Auth::guard('admin-api')->user()->userType == 'super-admin' ? 'super-admin' : 'admin' ,
                'role_permissions' => Auth::guard('admin-api')->user()->RolePermission,
                'message' => "Successfully logged In!",
                'success' => true,
                'status' => Auth::guard('admin-api')->user()->status,
                // 'business' => $business_details,
                'apiKeys' => $apiKeys,
            ]);
        } elseif ($user_type == 'customer-api') {
            return response()->json([
                'access_token' => $token,
                'token_type' => 'bearer',
                'chat_type' => 'customer',
                //'expires_in' => strtotime(date('Y-m-d H:i:s', strtotime("+60 min"))),
                'admin_id' => Auth::guard('customer-api')->user()->id,
                'admin_name' => Auth::guard('customer-api')->user()->name,
                'admin_email' => Auth::guard('customer-api')->user()->email,
                'role_permissions' => [],
                'admin_user_type' => 'customer',
                'message' => "Successfully logged In!",
                'success' => true,
                'status' => Auth::guard('customer-api')->user()->status,
                // 'business' => $business_details,
                'apiKeys' => $apiKeys,
                'is_recived_support' => 0,

            ]);
        } elseif ($user_type == 'contractor-api') {
            return response()->json([
                'access_token' => $token,
                'token_type' => 'bearer',
                'chat_type' => 'contractor',
                //'expires_in' => strtotime(date('Y-m-d H:i:s', strtotime("+60 min"))),
                'admin_id' => Auth::guard('contractor-api')->user()->id,
                'admin_name' => Auth::guard('contractor-api')->user()->name,
                'admin_email' => Auth::guard('contractor-api')->user()->email,
                'role_permissions' => [],
                'admin_user_type' => 'contractor',
                'message' => "Successfully logged In!",
                'success' => true,
                'status' => Auth::guard('contractor-api')->user()->status,
                // 'business' => $business_details,
                'apiKeys' => $apiKeys,
                'is_recived_support' => 0,

            ]);
        } elseif ($user_type == 'guard-api') {
            return response()->json([
                'access_token' => $token,
                'token_type' => 'bearer',
                'chat_type' => 'staff',
                //'expires_in' => strtotime(date('Y-m-d H:i:s', strtotime("+60 min"))),
                'admin_id' => Auth::guard('guard-api')->user()->id,
                'admin_name' => Auth::guard('guard-api')->user()->first_name . ' ' . Auth::guard('guard-api')->user()->middle_name . ' ' . Auth::guard('guard-api')->user()->last_name,
                'admin_email' => Auth::guard('guard-api')->user()->email,
                'role_permissions' => [],
                'admin_user_type' => 'guard',
                'message' => "Successfully logged In!",
                'success' => true,
                'status' => Auth::guard('guard-api')->user()->guard_status,
                // 'business' => $business_details,
                'apiKeys' => $apiKeys,
                'is_recived_support' => 0,

            ]);
        } else {
            return response()->json(['message' => 'Role Not Found!', 'success' => false]);
        }
    }
    private function recordActivity($user, $action){
        $logging = new Logging();
        $logging->action_by = $user->id;
        $logging->action_by_name = $user->name;
        $logging->action_by_type = 'user';
        $logging->action = $action;
        $logging->page = $action;
        if($action == 'Logout'){
            $logging->route_leave = usaToAusDateTime(date('Y-m-d H:i:s'));
        }else{
            $logging->route_enter = usaToAusDateTime(date('Y-m-d H:i:s'));
        }
        $logging->save();
    }
    public function logout(Request $request)
    {

        $token = Str::replace('Bearer', '', $request->header('AuthorizationToken'));
        $token = Str::replace(' ', '', $token);
        $user = User::where('auth_token', $token)->first();
        $customer = Customer::where('auth_token', $token)->first();
        $guard = Guard::where('auth_token', $token)->first();
        if ($user) {
            $user->auth_token = NULL;
            $user->is_online = 0;
            $user->logout_at = now()->format('d-m-Y H:i:s');
            $user->update();
            $this->recordActivity($user, $action='Logout');
            auth()->logout();
            return response()->json(['message' => 'Successfully logged out']);
        } elseif ($customer) {
            $customer->auth_token = NULL;
            $customer->update();
            $this->recordActivity($customer, $action='Logout');
            auth()->logout();
            return response()->json(['message' => 'Successfully logged out']);
        } elseif ($guard) {
            $guard->auth_token = NULL;
            $guard->update();
            $this->recordActivity($guard, $action='Logout');
            auth()->logout();
            return response()->json(['message' => 'Successfully logged out']);
        }
    }


    public function signInWithToken(Request $request)
    {
        $admin = User::where('auth_token', $request->accessToken)->first();
        $customer = Customer::where('auth_token', $request->accessToken)->first();
        $guard = Guard::where('auth_token', $request->accessToken)->first();
        $contractor = Contractor::where('auth_token', $request->accessToken)->first();
    
        if ($admin) {
            return response()->json([
                'access_token' => $admin->auth_token,
                'admin_id' => $admin->id,
                'admin_name' => $admin->name,
                'admin_email' => $admin->email,
                'type' => 'admin'
            ]);
        } elseif ($customer) {
            return response()->json([
                'access_token' => $customer->auth_token,
                'admin_id' => $customer->id,
                'admin_name' => $customer->name,
                'admin_email' => $customer->email,
                'type' => 'customer'
            ]);
        } elseif ($guard) {
            return response()->json([
                'access_token' => $guard->auth_token,
                'admin_id' => $guard->id,
                'admin_name' => $guard->first_name,
                'admin_email' => $guard->email,
                'type' => 'guard'
            ]);
        } elseif ($contractor) {
            return response()->json([
                'access_token' => $contractor->auth_token,
                'contractor_id' => $contractor->id,
                'contractor_name' => $contractor->name,
                'contractor_email' => $contractor->email,
            ]);
        } else {
            return response()->json(['message' => 'User not found!']);
        }
    }
    
    // public function forgotPassword(ForgotPasswordRequest $request)
    // {
    //     $serach_db = [];
    //     $serach_business = [];
    //     $ids = [];
    //     if ($request->has('request_db') && !empty($request->request_db)) {
    //         $newHost = env('DB_HOST');
    //         $newDatabase = $request->request_db;
    //         $newUsername = env('DB_USERNAME');
    //         $newPassword = env('DB_PASSWORD');
    //         // Get the existing configuration for the MySQL connection
    //         $connectionConfig = config('database.connections.mysql');
    //         // Update the credentials in the configuration
    //         $connectionConfig['driver'] = 'mysql';
    //         $connectionConfig['host'] = $newHost;
    //         $connectionConfig['database'] = $newDatabase;
    //         $connectionConfig['username'] = $newUsername;
    //         $connectionConfig['password'] = $newPassword;
    //         // Update the configuration for the MySQL connection
    //         config(['database.connections.mysql' => $connectionConfig]);
    //     } else {
    //         $databases = $this->dbConnections();
    //         foreach ($databases as $key => $db_conn) {
    //             $connectionConfig['driver'] = 'mysql';
    //             $connectionConfig['host'] = env('DB_HOST');
    //             $connectionConfig['database'] = $db_conn->database_name;
    //             $connectionConfig['username'] = env('DB_USERNAME');
    //             $connectionConfig['password'] = env('DB_PASSWORD');
    //             // Create a new database connection dynamically
    //             $newConnection = 'mysql_' . $key;
    //             config(['database.connections.' . $newConnection => $connectionConfig]);
    //             // Use the new database connection
    //             $user = null;
    //             $admin_log = DB::connection($newConnection)->table('users')->where('email', $request->email)->first();
    //             $guard_log = DB::connection($newConnection)->table('guards')->where('email', $request->email)->first();
    //             $customer_log = DB::connection($newConnection)->table('customers')->where('email', $request->email)->first();
    //             $contractor_log = DB::connection($newConnection)->table('contractors')->where('email', $request->email)->first();

    //             if (!empty($admin_log)) {
    //                 $user = $admin_log;
    //             } elseif (!empty($guard_log)) {
    //                 $user = $guard_log;
    //             } elseif (!empty($customer_log)) {
    //                 $user = $customer_log;
    //             } elseif (!empty($contractor_log)) {
    //                 $user = $contractor_log;
    //             }

    //             if ($user !== null) {

    //                 $serach_db[] = $db_conn->database_name;
    //                 $serach_business[] = $db_conn->title;
    //                 $ids[] = $db_conn->id;
    //                 $keyValuePairs = array_combine($serach_db, $serach_business);

    //                 $convertedPairs = [];
    //                 foreach ($keyValuePairs as $name => $value) {
    //                     $index = array_search($name, $serach_db);
    //                     $convertedPairs[] = ['name' => $value, 'value' => $serach_db[$index], 'id' => $ids[$index]];
    //                 }

    //             }
    //         }
    //         if (count($serach_db) > 1) {
    //             return response()->json(['success' => true, 'data' => $convertedPairs, 'hide' => false]);
    //         }elseif(count($serach_db) == 0){
    //             return response()->json(['success' => false, 'error' => 'Email not Found!']);
    //         }else {
    //             // Set the new credentials for the MySQL connection
    //             $newHost = env('DB_HOST');
    //             $newDatabase = $serach_db[0];
    //             $newUsername = env('DB_USERNAME');
    //             $newPassword = env('DB_PASSWORD');
    //             // Get the existing configuration for the MySQL connection
    //             $connectionConfig = config('database.connections.mysql');
    //             // Update the credentials in the configuration
    //             $connectionConfig['driver'] = 'mysql';
    //             $connectionConfig['host'] = env('DB_HOST');
    //             $connectionConfig['database'] = $newDatabase;
    //             $connectionConfig['username'] = env('DB_USERNAME');
    //             $connectionConfig['password'] = env('DB_PASSWORD');
    //             // Update the configuration for the MySQL connection
    //             config(['database.connections.mysql' => $connectionConfig]);
    //         }
    //     }

    //     //$connectionConfig = config('database.connections.mysql');
    //     //$connectionName = 'mysql2';
    //     //$business_details = DB::connection($connectionName)->table('business_data')->where('hide', 1)->where('database_name', $connectionConfig['database'])->first();


    //     $user = User::where('email', $request->email)->first();
    //     $customer = Customer::where('email', $request->email)->first();
    //     $guard = Guard::where('email', $request->email)->first();
    //     $contractor = Contractor::where('email', $request->email)->first();

    //     if (!empty($user)) {
    //         $otp = Str::random(4);
    //         $user->otp = $otp;
    //         $user->save();
    //         $connectionConfig = config('database.connections.mysql');
    //         forgotPassword($request->email, $otp, $connectionConfig['database']);
    //         // return response()->json(['success' => true, 'message' => 'otp has send successfully!']);
    //     } else if (!empty($customer)) {
    //         $otp = Str::random(4);
    //         $customer->otp = $otp;
    //         $customer->save();
    //         $connectionConfig = config('database.connections.mysql');
    //         forgotPassword($request->email, $otp, $connectionConfig['database']);
    //         // return response()->json(['success' => true, 'message' => 'otp has send successfully!']);
    //     } else if (!empty($guard)) {
    //         $otp = Str::random(4);
    //         $guard->otp = $otp;
    //         $guard->save();
    //         $connectionConfig = config('database.connections.mysql');
    //         forgotPassword($request->email, $otp, $connectionConfig['database']);
    //         // return response()->json(['success' => true, 'message' => 'otp has send successfully!']);
    //     } else if (!empty($contractor)) {
    //             $otp = Str::random(4);
    //             $contractor->otp = $otp;
    //             $contractor->save();
    //             $connectionConfig = config('database.connections.mysql');
    //             forgotPassword($request->email, $otp, $connectionConfig['database']);
    //             // return response()->json(['success' => true, 'message' => 'otp has send successfully!']);
    //         }
    //      else {
    //         return response()->json(['success' => false, 'message' => 'User not found!']);
    //     }
    //     return response()->json(['success' => true, 'message' => 'otp has send successfully!', 'hide'=> count($serach_db) > 1 ? false : true]);
    // }

    public function forgotPassword(ForgotPasswordRequest $request)
    {
        $database = env('DB_DATABASE');

        $user = User::where('email', $request->email)->first();
        $customer = Customer::where('email', $request->email)->first();
        $guard = Guard::where('email', $request->email)->first();
        $contractor = Contractor::where('email', $request->email)->first();

        if (!empty($user)) {
            $otp = Str::random(4);
            $user->otp = $otp;
            $user->save();
            forgotPassword($request->email, $otp, $database);
        } elseif (!empty($customer)) {
            $otp = Str::random(4);
            $customer->otp = $otp;
            $customer->save();
            forgotPassword($request->email, $otp, $database);
        } elseif (!empty($guard)) {
            $otp = Str::random(4);
            $guard->otp = $otp;
            $guard->save();
            forgotPassword($request->email, $otp, $database);
        } elseif (!empty($contractor)) {
            $otp = Str::random(4);
            $contractor->otp = $otp;
            $contractor->save();
            forgotPassword($request->email, $otp, $database);
        } else {
            return response()->json(['success' => false, 'message' => 'User not found!']);
        }

        return response()->json(['success' => true, 'message' => 'OTP has been sent successfully!']);
    }


    // public function forgotPasswordStep2(Request $request)
    // {

    //         $newHost = env('DB_HOST');
    //         $newDatabase = $request->database;
    //         $newUsername = env('DB_USERNAME');
    //         $newPassword = env('DB_PASSWORD');
    //         // Get the existing configuration for the MySQL connection
    //         $connectionConfig = config('database.connections.mysql');

    //         // Update the credentials in the configuration
    //         $connectionConfig['driver'] = 'mysql';
    //         $connectionConfig['host'] = $newHost;
    //         $connectionConfig['database'] = $newDatabase;
    //         $connectionConfig['username'] = $newUsername;
    //         $connectionConfig['password'] = $newPassword;
    //         // Update the configuration for the MySQL connection
    //         config(['database.connections.mysql' => $connectionConfig]);


    //         // return $connectionConfig = config('database.connections.mysql');

    //         $otp = $request->token;
    //         $email = $request->email;
    //         $user = User::where('email', $email)->where('otp', $otp)->first();
    //         $customer = Customer::where('email', $email)->where('otp', $otp)->first();
    //         $guard = Guard::where('email', $email)->where('otp', $otp)->first();
    //         $contractor = Contractor::where('email', $email)->where('otp', $otp)->first();
    //         if (!empty($user)) {
    //             $user->otp = null;
    //             $user->password = Hash::make($request->password);
    //             $user->save();
    //             return response()->json(['message' => 'Password Update Successfully!']);
    //         } elseif (!empty($customer)) {
    //             $customer->otp = null;
    //             $customer->password = Hash::make($request->password);
    //             $customer->save();
    //             return response()->json(['message' => 'Password Update Successfully!']);
    //         } elseif (!empty($guard)) {
    //             $guard->otp = null;
    //             $guard->password = Hash::make($request->password);
    //             $guard->save();
    //             return response()->json(['message' => 'Password Update Successfully!']);
    //         } elseif (!empty($contractor)) {
    //             $contractor->otp = null;
    //             $contractor->password = Hash::make($request->password);
    //             $contractor->save();
    //             return response()->json(['message' => 'Password Update Successfully!']);
    //         }else{
    //             return response()->json(['message' => 'No User Found!']);
    //         }
    // }

    public function forgotPasswordStep2(Request $request)
    {
        $otp = $request->token;
        $email = $request->email;

        $user = User::where('email', $email)->where('otp', $otp)->first();
        $customer = Customer::where('email', $email)->where('otp', $otp)->first();
        $guard = Guard::where('email', $email)->where('otp', $otp)->first();
        $contractor = Contractor::where('email', $email)->where('otp', $otp)->first();

        if (!empty($user)) {
            $this->updatePassword($user, $request->password);
            return response()->json(['message' => 'Password updated successfully!'], 200);
        } elseif (!empty($customer)) {
            $this->updatePassword($customer, $request->password);
            return response()->json(['message' => 'Password updated successfully!'], 200);
        } elseif (!empty($guard)) {
            $this->updatePassword($guard, $request->password);
            return response()->json(['message' => 'Password updated successfully!'], 200);
        } elseif (!empty($contractor)) {
            $this->updatePassword($contractor, $request->password);
            return response()->json(['message' => 'Password updated successfully!'], 200);
        } else {
            return response()->json(['message' => 'No user found!'], 404);
        }
    }

    private function updatePassword($model, $password)
    {
        $model->otp = null;
        $model->password = Hash::make($password);
        $model->save();
    }

    public function getAdmin(Request $request)
    {
        $admin = User::where('id', $request->id)->first();
        if ($admin) {
            $adms = new GetAdminResource($admin);
            return response()->json(['success' => true, 'data' => $adms, 'code' => 200]);
        } else {
            return response()->json(['message' => 'admin not found!']);
        }
    }

    public function changePassword(Request $request)
    {
        $admin = User::find($request->admin_id);
    
        if (Hash::check($request->current_password, $admin->password)) {
            
            $admin->password = Hash::make($request->new_password);
            $admin->save();
    
            return response()->json(['success' => true, 'message' => 'Password changed successfully']);
        } else {
            return response()->json(['success' => false, 'message' => 'Current password is incorrect'], 400);
        }
    }
    
    public function guardEmailVerification($email,$token)
    { 
        $guard = Guard::where('email', $email)->first();

        if($guard){
            if($guard->email_varifay_otp == $token){
                $guard->is_email_approved = 'yes';
                $guard->guard_status = 'new';
                $guard->save();
                return view('guard-welcome', ['guard' => $guard]);
            }else{
                return response()->json(['message' => "Your Otp Expired!" ,  'code' => 404, 'success' => false]);
            }
        }else{
            return response()->json(['message' => "Staff Not Found!" ,  'code' => 404, 'success' => false]);
        }
    }

    public function adminEmailVerification($email,$business_id)
    {
        $connectionName = 'mysql2'; 
        $results = DB::connection($connectionName)->table('business_data')->where('id', $business_id)->select('database_name')->first();

            if($results){
            $newHost = env('DB_HOST');
            $newDatabase = $results->database_name;
            $newUsername = env('DB_USERNAME');
            $newPassword = env('DB_PASSWORD');
            // Get the existing configuration for the MySQL connection
            $connectionConfig = config('database.connections.mysql');

            // Update the credentials in the configuration
            $connectionConfig['driver'] = 'mysql';
            $connectionConfig['host'] = $newHost;
            $connectionConfig['database'] = $newDatabase;
            $connectionConfig['username'] = $newUsername;
            $connectionConfig['password'] = $newPassword;
            // Update the configuration for the MySQL connection
            config(['database.connections.mysql' => $connectionConfig]);
            }
            else{
                return response()->json(['success' => false, 'message' => 'Connection Not Established!'], 404);
            } 
        $admin = User::where('email', $email)->first();

        if($admin){
            if($admin->email == $email){
                $admin->is_email_verify = 'yes';
                $admin->save();
                return view('admin-welcome', ['admin' => $admin]);
            }else{
                return response()->json(['message' => "Your Email not correct!" ,  'code' => 404, 'success' => false]);
            }
        }else{
            return response()->json(['message' => "Staff Not Found!" ,  'code' => 404, 'success' => false]);
        }
    }

}
