<?php

namespace App\Http\Controllers\Api\Auth;


use App\Http\Controllers\Api\ApiController;
use App\Http\Resources\User\AdminResource;
use App\Http\Resources\User\UserResource;
use App\Http\Resources\User\CustomerResource;
use App\Models\Admin;
use App\Models\User;
use App\Models\Customer;
use App\Models\UserActivity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class UserController extends ApiController
{
    public $userActivityRepo;
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(UserActivity $userActivity) {
        $this->userActivityRepo = $userActivity;
    }
    private function encryptPassword($password) {

        return password_hash($password, CRYPT_SHA512);

    }
    public function register(Request $request) {

        if ($request->has('userType') && $request->input('userType') == 'customer') {
            return $this->customer_register($request);
            exit();
        }

        $this->request = $request;
        $this->setValidationRules(['firstName' => 'required', 'lastName' => 'required', 'email' => 'required|email|unique:guards', 'password' => 'required']);
        if ($this->isValidRequest()) {
            $this->response = ['success' => false, 'error' => $this->getErrors()];
            $this->statusCode = self::STATUS_CODE_200;
            return $this->sendResponse();
        }

        $user = new User();

        $already = User::where('email', $request->input('email'))->first();
        if($already) {
            $this->response = ['status' => false, 'error' => 'User with this email is already registered.'];
            $this->statusCode = self::STATUS_CODE_200;
            return $this->sendResponse();
        }
        // $undefined = undefined;
        if (!$request->has('middleName') || $request->middleName == 'undefined') {
            $user->middle_name = '';
            // $user->name =  $request->input('firstName').' '.$request->input('lastName');
        }else{
            $user->middle_name = $request->input('middleName');
            // $user->name =  $request->input('firstName').' '. $request->input('middleName').' '.$request->input('lastName');
            $user->middleName = str_replace('undefined', '', $user->middleName);
            // $user->name = str_replace('undefined', '', $user->name);
        }
        $user->first_name = $request->input('firstName');
        $user->last_name = $request->input('lastName');
        // $user->middle_name = $request->input('middleName');
        
        $user->email = $request->input('email');
        $user->password = Hash::make($request->input('password'));
        $user->phone = $request->input('phone');
        $user->guard_status = User::INACTIVE_STATUS;
        $activationCode = md5($this->encryptPassword(md5($request->input('email').md5(time()))));
        $user->activation_code = $activationCode;
        $user->auth_token = $activationCode;
        $plainPassword = '******';

        if($user->save()){
            // $this->sendMail(array('name' => $request->input('firstName').' '. $request->input('middleName').' '.$request->input('lastName'), 'email' => $request->input('email')), $activationCode, $plainPassword);
            $this->statusCode = 201;
            $this->response = [
                'success' => true,
                'message' => 'Your account has been created succesfully! Please verify your email address first to login.'
            ];
            return $this->sendResponse();
        }
        $this->statusCode = 200;
        $this->response = ['success' => false, 'error' => 'Your account has not been created.'];
        return $this->sendResponse();
    }

    public function customer_register($request)
    {
        $this->request = $request;
        $this->setValidationRules(['firstName' => 'required', 'lastName' => 'required', 'email' => 'required|email|unique:customers', 'password' => 'required', 'phone' => 'required']);
        if ($this->isValidRequest()) {
            $this->response = ['success' => false, 'error' => $this->getErrors()];
            $this->statusCode = self::STATUS_CODE_200;
            return $this->sendResponse();
        }

        $customer = new Customer();

        $already = Customer::where('email', $request->input('email'))->first();
        if($already) {
            $this->response = ['status' => false, 'error' => 'Customer with this email is already registered.'];
            $this->statusCode = self::STATUS_CODE_200;
            return $this->sendResponse();
        }

        $customer->name =  $request->input('firstName').' '.$request->input('lastName');
        $customer->email = $request->input('email');
        $customer->password = Hash::make($request->input('password'));
        $customer->phone = $request->input('phone');
        $customer->address = '';
        $customer->city = '';
        $customer->state = '';
        $customer->postal_code = '';
        $customer->auth_token = '0';
        $customer->notification_token = '';
        // $customer->timestamp_joined = time();
        // $customer->timestamp_activity = time();
        
        $customer->status = 'active';
        $plainPassword = '******';

        if($customer->save()){
            // $this->sendMail(array('name' => $request->input('firstName').' '.$request->input('lastName'), 'email' => $request->input('email')), '', $plainPassword);
            $this->statusCode = 201;
            $this->response = [
                'success' => true,
                'message' => 'Your account has been created succesfully!'
            ];
            return $this->sendResponse();
        }
        $this->statusCode = 200;
        $this->response = ['success' => false, 'error' => 'Your account has not been created.'];
        return $this->sendResponse();

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

    public function login(Request $request) {


        if ($request->has('userType') && $request->input('userType') == 'customer') {
            return $this->customer_login($request);
            
        }
        if ($request->has('userType') && $request->input('userType') == 'admin') {
            return $this->admin_login($request);
        }

        //faizan..
        // $serach_db = [];
        // $serach_business = [];
        // $ids = [];
        // if ($request->has('request_db') && !empty($request->request_db)) {
            // $newHost = env('DB_HOST');
            // $newDatabase = $request->request_db;
            // $newUsername = env('DB_USERNAME');
            // $newPassword = env('DB_PASSWORD');
            // // Get the existing configuration for the MySQL connection
            // $connectionConfig = config('database.connections.mysql');
            // // Update the credentials in the configuration
            // $connectionConfig['driver'] = 'mysql';
            // $connectionConfig['host'] = $newHost;
            // $connectionConfig['database'] = $newDatabase;
            // $connectionConfig['username'] = $newUsername;
            // $connectionConfig['password'] = $newPassword;
            // Update the configuration for the MySQL connection
            // config(['database.connections.mysql' => $connectionConfig]);
        // } else {
            // $databases = $this->dbConnections();
            // foreach ($databases as $key => $db_conn) {
            //     $connectionConfig['driver'] = 'mysql';
            //     $connectionConfig['host'] = env('DB_HOST');
            //     $connectionConfig['database'] = $db_conn->database_name;
            //     $connectionConfig['username'] = env('DB_USERNAME');
            //     $connectionConfig['password'] = env('DB_PASSWORD');
            //     // Create a new database connection dynamically
            //     $newConnection = 'mysql_' . $key;
            //     config(['database.connections.' . $newConnection => $connectionConfig]);
                // Use the new database connection
                // $user = null;
                // //$admin_log = DB::connection($newConnection)->table('users')->where('email', $request->email)->first();
                // $guard_log = DB::connection($newConnection)->table('guards')->where('email', $request->email)->first();
                // $customer_log = DB::connection($newConnection)->table('customers')->where('email', $request->email)->first();
                // //$contractor_log = DB::connection($newConnection)->table('contractors')->where('email', $request->email)->first();

                // if (!empty($guard_log)) {
                //     $user = $guard_log;
                // } elseif (!empty($customer_log)) {
                //     $user = $customer_log;
                // }

                // if ($user !== null) {

                    // $serach_db[] = $db_conn->database_name;
                    // $serach_business[] = $db_conn->title;
                    // $ids[] = $db_conn->id;
                    // $keyValuePairs = array_combine($serach_db, $serach_business);

                    // $convertedPairs = [];
                    // foreach ($keyValuePairs as $name => $value) {
                    //     $index = array_search($name, $serach_db);
                    //     $convertedPairs[] = ['name' => $value, 'value' => $serach_db[$index], 'id' => $ids[$index]];
                    // }

                // }
            // }
            // if (count($serach_db) > 1) {
            //     return response()->json(['success' => true, 'businesses' => $convertedPairs]);
            // }elseif(count($serach_db) == 0){
            //     return response()->json(['success' => false, 'error' => 'Email not Found!']);
            // } else {
            //     // Set the new credentials for the MySQL connection
            //     $newHost = env('DB_HOST');
            //     $newDatabase = $serach_db[0];
            //     $newUsername = env('DB_USERNAME');
            //     $newPassword = env('DB_PASSWORD');
            //     // Get the existing configuration for the MySQL connection
            //     $connectionConfig = config('database.connections.mysql');
            //     // Update the credentials in the configuration
            //     $connectionConfig['driver'] = 'mysql';
            //     $connectionConfig['host'] = env('DB_HOST');
            //     $connectionConfig['database'] = $newDatabase;
            //     $connectionConfig['username'] = env('DB_USERNAME');
            //     $connectionConfig['password'] = env('DB_PASSWORD');
            //     // Update the configuration for the MySQL connection
            //     config(['database.connections.mysql' => $connectionConfig]);
            // }
        // }

        // $connectionConfig = config('database.connections.mysql');
        // $connectionName = 'mysql2';
        $business_details1 = DB::table('business_data')->where('hide', 1)->first();

        $business_details = [
            'appName' => $business_details1->title,
            'businessType' => $business_details1->business_type,
            'id' => $business_details1->id,
            'domain' => $business_details1->domain,
            'oneSignalAppID' => $business_details1->app_id,
            'oneSignalSenderID' => $business_details1->one_signal_sender_id,
            'oneSignalServerKey' => $business_details1->server_key,
            'title' => $business_details1->title,
            'uniqueID' => $business_details1->unique_id,
        ];
        

        $this->request = $request;
        $this->setValidationRules(['email' => 'required|email', 'password' => 'required']);
        if ($this->isValidRequest()) {
            $this->response = ['success' => false, 'error' => $this->getErrors()];
            $this->statusCode = self::STATUS_CODE_200;
            return $this->sendResponse();
        }

        $user = User::where('email', $request->input('email'))->first();

        if(empty($user)){
            return response()->json(['success' => false, 'error' => 'User Not Found!']);
        }
        
        if($user && $user->guard_status != 'active' || $user->admin_approval_status != 'active' || $user->is_available != 'yes') {
            $this->response = ['status' => false, 'error' => 'Your account is inactive please contact your Administrator.'];
            $this->statusCode = self::STATUS_CODE_200;
            return $this->sendResponse();
        }

        if($user && Hash::check($request->input('password'), $user->password)){
            $apikey = base64_encode(Str::random(64).time());
            $coordinates = ($request->input('coordinates') != null) ? $request->input('coordinates') : '';
            // comment by mohsin so user token will not update on each login request. 21/08/2021
            // User::where('email', $request->input('email'))->update(['auth_token' => "$apikey"]);
            $user = User::where('email', $request->input('email'))->first();
            // if ($user->auth_token == '') {
            User::where('email', $request->input('email'))->update(['auth_token' => "$apikey"]);
            $user->auth_token = $apikey;
            // }
            // UserActivity::where('guard_id', $user->id)->update(['status' => 0]);
            // $this->userActivityRepo->insert([
            //     'guard_id' => $user->id,
            //     'signin_time' => $this->request->input('signin_time'),
            //     'location'=> $this->request->input('location'),
            //     'status'=> 1,
            // ]);
            // $this->userActivityRepo->insert([
            //     'guard_id' => $user->id,
            //     'location' => $coordinates,
            //     'action' => 'signin',
            //     'status' => 1 
            // ]);
            $usr =  new UserResource($user);
            return response()->json(['data' => $usr, 'business_details' => $business_details, 'auth_token' => $usr->auth_token,'success' => true]);
        }

        $this->response = ['status' => false, 'error' => 'Username or Password invalid.'];
        $this->statusCode = self::STATUS_CODE_200;
        return $this->sendResponse();
    }

    public function customer_login(Request $request) {

        // $serach_db = [];
        // $serach_business = [];
        // $ids = [];
        // if ($request->has('request_db') && !empty($request->request_db)) {
        //     $newHost = env('DB_HOST');
        //     $newDatabase = $request->request_db;
        //     $newUsername = env('DB_USERNAME');
        //     $newPassword = env('DB_PASSWORD');
        //     // Get the existing configuration for the MySQL connection
        //     $connectionConfig = config('database.connections.mysql');
        //     // Update the credentials in the configuration
        //     $connectionConfig['driver'] = 'mysql';
        //     $connectionConfig['host'] = $newHost;
        //     $connectionConfig['database'] = $newDatabase;
        //     $connectionConfig['username'] = $newUsername;
        //     $connectionConfig['password'] = $newPassword;
        //     // Update the configuration for the MySQL connection
        //     config(['database.connections.mysql' => $connectionConfig]);
        // } else {
        //     $databases = $this->dbConnections();
        //     foreach ($databases as $key => $db_conn) {
        //         $connectionConfig['driver'] = 'mysql';
        //         $connectionConfig['host'] = env('DB_HOST');
        //         $connectionConfig['database'] = $db_conn->database_name;
        //         $connectionConfig['username'] = env('DB_USERNAME');
        //         $connectionConfig['password'] = env('DB_PASSWORD');
        //         // Create a new database connection dynamically
        //         $newConnection = 'mysql_' . $key;
        //         config(['database.connections.' . $newConnection => $connectionConfig]);
        //         // Use the new database connection
        //         $user = null;
        //         //$admin_log = DB::connection($newConnection)->table('users')->where('email', $request->email)->first();
        //         $guard_log = DB::connection($newConnection)->table('guards')->where('email', $request->email)->first();
        //         $customer_log = DB::connection($newConnection)->table('customers')->where('email', $request->email)->first();
        //         //$contractor_log = DB::connection($newConnection)->table('contractors')->where('email', $request->email)->first();

        //         if (!empty($guard_log)) {
        //             $user = $guard_log;
        //         } elseif (!empty($customer_log)) {
        //             $user = $customer_log;
        //         }

        //         if ($user !== null) {

        //             $serach_db[] = $db_conn->database_name;
        //             $serach_business[] = $db_conn->title;
        //             $ids[] = $db_conn->id;
        //             $keyValuePairs = array_combine($serach_db, $serach_business);

        //             $convertedPairs = [];
        //             foreach ($keyValuePairs as $name => $value) {
        //                 $index = array_search($name, $serach_db);
        //                 $convertedPairs[] = ['name' => $value, 'value' => $serach_db[$index], 'id' => $ids[$index]];
        //             }

        //         }
        //     }
        //     if (count($serach_db) > 1) {
        //         return response()->json(['success' => true, 'data' => $convertedPairs]);
        //     } else {
        //         // Set the new credentials for the MySQL connection
        //         $newHost = env('DB_HOST');
        //         $newDatabase = $serach_db[0];
        //         $newUsername = env('DB_USERNAME');
        //         $newPassword = env('DB_PASSWORD');
        //         // Get the existing configuration for the MySQL connection
        //         $connectionConfig = config('database.connections.mysql');
        //         // Update the credentials in the configuration
        //         $connectionConfig['driver'] = 'mysql';
        //         $connectionConfig['host'] = env('DB_HOST');
        //         $connectionConfig['database'] = $newDatabase;
        //         $connectionConfig['username'] = env('DB_USERNAME');
        //         $connectionConfig['password'] = env('DB_PASSWORD');
        //         // Update the configuration for the MySQL connection
        //         config(['database.connections.mysql' => $connectionConfig]);
        //     }
        // }

        // $connectionConfig = config('database.connections.mysql');
        // $connectionName = 'mysql2';
        $business_details1 = DB::table('business_data')->where('hide', 1)->first();

        $business_details = [
            'appName' => $business_details1->title,
            'businessType' => $business_details1->business_type,
            'id' => $business_details1->id,
            'domain' => $business_details1->domain,
            'oneSignalAppID' => $business_details1->app_id,
            'oneSignalSenderID' => $business_details1->one_signal_sender_id,
            'oneSignalServerKey' => $business_details1->server_key,
            'title' => $business_details1->title,
            'uniqueID' => $business_details1->unique_id,
        ];

        $this->request = $request;
        $this->setValidationRules(['email' => 'required|email', 'password' => 'required']);
        if ($this->isValidRequest()) {
            $this->response = ['success' => false, 'error' => $this->getErrors()];
            $this->statusCode = self::STATUS_CODE_200;
            return $this->sendResponse();
        }

        $user = Customer::where('email', $request->input('email'))->first();
        if($user && $user->status !== User::ACTIVE_STATUS) {
            $this->response = ['status' => false, 'error' => 'Your account is inactive please contact your Administrator.'];
            $this->statusCode = self::STATUS_CODE_200;
            return $this->sendResponse();
        }

        if($user && Hash::check($request->input('password'), $user->password)){
            $apikey = base64_encode(Str::random(64).time());
            // comment by mohsin so user token will not update on each login request. 21/08/2021
            // Customer::where('email', $request->input('email'))->update(['auth_token' => "$apikey"]);
            $user = Customer::where('email', $request->input('email'))->first();
            // if ($user->auth_token == '') {
                Customer::where('email', $request->input('email'))->update(['auth_token' => "$apikey"]);
                $user->auth_token = $apikey;
            // }
            $usr =  new CustomerResource($user);
            return response()->json(['data' => $usr, 'business_details' => $business_details, 'success' => true]);
            // return response()->json(['data' => $usr,'auth_token' => $usr->auth_token,'success' => true]);
        }
        $this->response = ['status' => false, 'error' => 'Username or Password invalid.'];
        $this->statusCode = self::STATUS_CODE_200;
        return $this->sendResponse();
    }

    public function admin_login(Request $request) {

        // $serach_db = [];
        // $serach_business = [];
        // $ids = [];
        // if ($request->has('request_db') && !empty($request->request_db)) {
        //     $newHost = env('DB_HOST');
        //     $newDatabase = $request->request_db;
        //     $newUsername = env('DB_USERNAME');
        //     $newPassword = env('DB_PASSWORD');
        //     // Get the existing configuration for the MySQL connection
        //     $connectionConfig = config('database.connections.mysql');
        //     // Update the credentials in the configuration
        //     $connectionConfig['driver'] = 'mysql';
        //     $connectionConfig['host'] = $newHost;
        //     $connectionConfig['database'] = $newDatabase;
        //     $connectionConfig['username'] = $newUsername;
        //     $connectionConfig['password'] = $newPassword;
        //     // Update the configuration for the MySQL connection
        //     config(['database.connections.mysql' => $connectionConfig]);
        // } else {
        //     $databases = $this->dbConnections();
        //     foreach ($databases as $key => $db_conn) {
        //         $connectionConfig['driver'] = 'mysql';
        //         $connectionConfig['host'] = env('DB_HOST');
        //         $connectionConfig['database'] = $db_conn->database_name;
        //         $connectionConfig['username'] = env('DB_USERNAME');
        //         $connectionConfig['password'] = env('DB_PASSWORD');
        //         $newConnection = 'mysql_' . $key;
        //         config(['database.connections.' . $newConnection => $connectionConfig]);
        //         $dynamicDbConnection = DB::connection($newConnection);
        //         $user = null;
        //         $admin_log = $dynamicDbConnection->table('users')->where('email', $request->email)->first();
        //         // return response()->json([$admin_log,$db_conn->database_name, DB::connection()->getDatabaseName()]);
        //         //$contractor_log = DB::connection($newConnection)->table('contractors')->where('email', $request->email)->first();

        //         if (!empty($guard_log)) {
        //             $user = $guard_log;
        //         } elseif (!empty($customer_log)) {
        //             $user = $customer_log;
        //         } elseif (!empty($admin_log)) {
        //             $user = $admin_log;
        //         }

        //         if ($user !== null) {

        //             $serach_db[] = $db_conn->database_name;
        //             $serach_business[] = $db_conn->title;
        //             $ids[] = $db_conn->id;
        //             $keyValuePairs = array_combine($serach_db, $serach_business);

        //             $convertedPairs = [];
        //             foreach ($keyValuePairs as $name => $value) {
        //                 $index = array_search($name, $serach_db);
        //                 $convertedPairs[] = ['name' => $value, 'value' => $serach_db[$index], 'id' => $ids[$index]];
        //             }

        //         }
        //     }
        //     if (count($serach_db) > 1) {
        //         return response()->json(['success' => true, 'businesses' => $convertedPairs]);
        //     }elseif(empty($serach_db)){
        //         return response()->json(['success' => false, 'error' => 'Email not found!']);
        //     }else {
        //         // Set the new credentials for the MySQL connection
        //         $newHost = env('DB_HOST');
        //         $newDatabase = $serach_db[0];
        //         $newUsername = env('DB_USERNAME');
        //         $newPassword = env('DB_PASSWORD');
        //         // Get the existing configuration for the MySQL connection
        //         $connectionConfig = config('database.connections.mysql');
        //         // Update the credentials in the configuration
        //         $connectionConfig['driver'] = 'mysql';
        //         $connectionConfig['host'] = env('DB_HOST');
        //         $connectionConfig['database'] = $newDatabase;
        //         $connectionConfig['username'] = env('DB_USERNAME');
        //         $connectionConfig['password'] = env('DB_PASSWORD');
        //         // Update the configuration for the MySQL connection
        //         config(['database.connections.mysql' => $connectionConfig]);
        //     }
        // }

        // $connectionConfig = config('database.connections.mysql');
        // $connectionName = 'mysql2';
        $business_details1 = DB::table('business_data')->where('hide', 1)->first();

        $business_details = [
            'appName' => $business_details1->title,
            'businessType' => $business_details1->business_type,
            'id' => $business_details1->id,
            'domain' => $business_details1->domain,
            'oneSignalAppID' => $business_details1->app_id,
            'oneSignalSenderID' => $business_details1->one_signal_sender_id,
            'oneSignalServerKey' => $business_details1->server_key,
            'title' => $business_details1->title,
            'uniqueID' => $business_details1->unique_id,
        ];

        $this->request = $request;
        $this->setValidationRules(['email' => 'required|email', 'password' => 'required']);
        if ($this->isValidRequest()) {
            $this->response = ['success' => false, 'error' => $this->getErrors()];
            $this->statusCode = self::STATUS_CODE_200;
            return $this->sendResponse();
        }

        $user = Admin::where('email', $request->input('email'))->first();
        if($user && $user->status !== User::ACTIVE_STATUS) {
            $this->response = ['status' => false, 'error' => 'Your account is inactive please contact your Administrator.'];
            $this->statusCode = self::STATUS_CODE_200;
            return $this->sendResponse();
        }

        if($user && Hash::check($request->input('password'), $user->password)){
            $apikey = base64_encode(Str::random(64).time());
            // comment by mohsin so user token will not update on each login request. 21/08/2021
            // Customer::where('email', $request->input('email'))->update(['auth_token' => "$apikey"]);
            $user = Admin::where('email', $request->input('email'))->first();
            // if ($user->auth_token == '') {
                Admin::where('email', $request->input('email'))->update(['auth_token' => "$apikey", 'is_online' => 1]);
                $user->auth_token = $apikey;
            // }
            $usr =new AdminResource($user);
            return response()->json(['data' => $usr, 'business_details' => $business_details, 'success' => true]);
            // return response()->json(['data' => $usr, 'auth_token' => $usr->auth_token,'success' => true]);
        }
        $this->response = ['status' => false, 'error' => 'Username or Password invalid.'];
        $this->statusCode = self::STATUS_CODE_200;
        return $this->sendResponse();
    }

    function rand_string( $length ) {
        $str = "";
        $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";

        $size = strlen( $chars );
        for( $i = 0; $i < $length; $i++ ) {
            $str .= $chars[ rand( 0, $size - 1 ) ];
        }

        return $str;
    }
//and call the function this way:
    public function forgot_password(Request $request) {
        $this->request = $request;
        $this->setValidationRules(['email' => 'required|email']);
        if ($this->isValidRequest()) {
            $this->response = ['success' => false, 'error' => $this->getErrors()];
            $this->statusCode = self::STATUS_CODE_200;
            return $this->sendResponse();
        }

        $user = User::where('email', $request->input('email'))->first();
        if(empty($user) ||$user->guard_status !== User::ACTIVE_STATUS) {
            $this->response = ['status' => false, 'error' => 'Email does not exists or invalid.'];
            $this->statusCode = self::STATUS_CODE_200;
            return $this->sendResponse();
        }else{
                    // $mypass = $this->rand_string(10);
                    //     $password = Hash::make($mypass);
                    //     $result=DB::table('guards')->where('email', $request->input('email'))->update(['password'=>$password]);
            //$apikey = base64_encode(Str::random(64).time());
            $apikey = Str::random(4);
            $result=DB::table('guards')->where('email', $request->input('email'))->update(['otp'=> $apikey]);
            //$user->auth_token = $apikey;
            $this->sendMail_forgot($user,$apikey);

            $this->response = ['status' => true, 'message' => 'Please check your inbox for the password reset instructions.'];
            $this->statusCode = self::STATUS_CODE_200;
            return $this->sendResponse();
        }


        $this->response = ['status' => false, 'error' => 'Email invalid.'];
        $this->statusCode = self::STATUS_CODE_200;
        return $this->sendResponse();
    }

    public function logout(Request $request) {

        if ($request->has('userType') && $request->input('userType') == 'customer') {
            return $this->customer_logout($request);
            exit();
        }else if($request->has('userType') && $request->input('userType') == 'staff'){
            $user = User::where('auth_token', $request->header('Authorization'))->first();
            User::where('id', $user->id)->update(['auth_token' => '', 'notification_token' => '']);
                $coordinates = ($request->has('coordinates') && $request->input('coordinates') != null) ? $request->input('coordinates') : '';
                $this->userActivityRepo->insert([
                    'guard_id' => $user->id,
                    'location' => $coordinates,
                    'action' => 'signout',
                    'status' => 1 
                ]);
                $this->response = [
                    'success' => true,
                    'message' => 'You are logout successfully.'
                ];
                $this->statusCode = self::STATUS_CODE_200;
                return $this->sendResponse();
        }else{
            return $this->admin_logout($request);
            exit();
        }

        // if ($request->has('userType') && $request->input('userType') == 'admin') {
        // }
        // $user = User::where('auth_token', $request->header('Authorization'))->first();
        // if ($user) {
        //     User::where('id', $user->id)->update(['auth_token' => '', 'notification_token' => '']);
        //     /*UserActivity::where(['guard_id' => $user->id, 'status' => 1])->update([
        //             'signout_time' => $request->input('signout_time'),
        //             'status'=> 0,
        //         ]);*/
        //         $coordinates = ($request->has('coordinates') && $request->input('coordinates') != null) ? $request->input('coordinates') : '';
        //         $this->userActivityRepo->insert([
        //             'guard_id' => $user->id,
        //             'location' => $coordinates,
        //             'action' => 'signout',
        //             'status' => 1 
        //         ]);
        //         $this->response = [
        //             'success' => true,
        //             'message' => 'You are logout successfully.'
        //         ];
        //         $this->statusCode = self::STATUS_CODE_200;
        //         return $this->sendResponse();
        //     }

            $this->statusCode = self::STATUS_CODE_200;
            $this->response = ['success' => true, 'message' => 'You are logout successfully.'];
            return $this->sendResponse();
        }

        public function customer_logout(Request $request) {

            $user = Customer::where('auth_token', $request->header('Authorization'))->first();
            if ($user) {
                Customer::where('id', $user->id)->update(['auth_token' => '', 'notification_token' => '']);
            /*UserActivity::where(['guard_id' => $user->id, 'status' => 1])->update([
                    'signout_time' => $request->input('signout_time'),
                    'status'=> 0,
                ]);*/
                // $coordinates = ($request->input('coordinates') != null) ? $request->input('coordinates') : '';
                // $this->userActivityRepo->insert([
                //     'guard_id' => $user->id,
                //     'location' => $coordinates,
                //     'action' => 'signout',
                //     'status' => 1 
                // ]);
                $this->response = [
                    'success' => true,
                    'message' => 'You are logout successfully.'
                ];
                $this->statusCode = self::STATUS_CODE_200;
                return $this->sendResponse();
            }

            $this->statusCode = self::STATUS_CODE_200;
            $this->response = ['success' => true, 'message' => 'You are logout successfully.'];
            return $this->sendResponse();
        }


        public function admin_logout(Request $request) {

            $user = Admin::where('auth_token', $request->header('Authorization'))->first();
            if ($user) {
                Admin::where('id', $user->id)->update(['auth_token' => '', 'notification_token' => '', 'is_online' => 0]);
            /*UserActivity::where(['guard_id' => $user->id, 'status' => 1])->update([
                    'signout_time' => $request->input('signout_time'),
                    'status'=> 0,
                ]);*/
                // $coordinates = ($request->input('coordinates') != null) ? $request->input('coordinates') : '';
                // $this->userActivityRepo->insert([
                //     'guard_id' => $user->id,
                //     'location' => $coordinates,
                //     'action' => 'signout',
                //     'status' => 1 
                // ]);
                $this->response = [
                    'success' => true,
                    'message' => 'You are logout successfully.'
                ];
                $this->statusCode = self::STATUS_CODE_200;
                return $this->sendResponse();
            }

            $this->statusCode = self::STATUS_CODE_200;
            $this->response = ['success' => true, 'message' => 'You are logout successfully.'];
            return $this->sendResponse();
        }

        public function update_security_license(Request $request, $id)
        {
            $this->request = $request;
            $update_flag = false;
            if ($this->request->input('security_license') != '' && $this->request->input('security_license') != null) {
                $data['security_license_file'] = $this->uploader_base64($this->request->input('security_license'));
                $update_flag = true;
            }
            if ($this->request->input('security_license_back') != '' && $this->request->input('security_license_back') != null) {
                $data['security_license_file_back'] = $this->uploader_base64($this->request->input('security_license_back'));
                $update_flag = true;
            }
            if ($this->request->input('security_license_number') != '' && $this->request->input('security_license_number') != null) {
                $data['security_license_number'] = $this->request->input('security_license_number');
                $update_flag = true;
            }
            if ($this->request->input('security_license_expiration') != '' && $this->request->input('security_license_expiration') != null) {
                $data['security_license_expiration'] = $this->request->input('security_license_expiration');
                $update_flag = true;
            }
            if($update_flag){
                DB::table('guards')->where('id', $id)->update($data);    

                $this->statusCode = self::STATUS_CODE_200;
                $this->response = [
                    'success' => true,
                    'message' => 'Security License updated successfully!'
                ];
                return $this->sendResponse();
            }
            $this->statusCode = self::STATUS_CODE_200;
            $this->response = [
                'success' => true,
                'message' => 'Nothing to update.'
            ];
            return $this->sendResponse();


        }

        public function update_profile(Request $request, $id) {
            $this->request = $request;
            $data = array();

            $update_flag = false;

            if ($request->has('userType') && $request->input('userType') == 'customer') {
                if($this->request->input('password') != 'default' && $this->request->input('password') != ''){
                    $data['password'] = Hash::make($this->request->input('password'));
                    $update_flag = true;
                    if($update_flag){
                        DB::table('customers')->where('id', $id)->update($data);    

                        $this->statusCode = self::STATUS_CODE_200;
                        $this->response = [
                            'success' => true,
                            'message' => 'Profile updated successfully!'
                        ];
                        return $this->sendResponse();
                    }
                }

            }else{

                $destinationPath =  rtrim(app()->basePath('public/uploads/'), '');



                $myfile = fopen($destinationPath . "request_log-".time().".txt", "w") or die("Unable to open file!");
                $txt = json_encode($request);
                fwrite($myfile, $txt);
                fclose($myfile);







                if($this->request->input('password') != 'default' && $this->request->input('password') != ''){
                    $data['password'] = Hash::make($this->request->input('password'));
                    $update_flag = true;
                }


                if ($this->request->input('profile_image') != '' && $this->request->input('profile_image') != null) {
                    $data['profile_image'] = $this->uploader_base64($this->request->input('profile_image'), 'guard');
                    $update_flag = true;
                }


                if ($this->request->input('security_license') != '' && $this->request->input('security_license') != null) {
                    $data['security_license_file'] = $this->uploader_base64($this->request->input('security_license'));
                    $update_flag = true;
                }


                if ($this->request->input('driver_license') != '' && $this->request->input('driver_license') != null) {
                    $data['driver_license_file'] = $this->uploader_base64($this->request->input('driver_license'), 'guard_documents');
                    $update_flag = true;
                }


                if ($this->request->input('visa') != '' && $this->request->input('visa') != null) {
                    $data['visa_file'] = $this->uploader_base64($this->request->input('visa'), 'guard_documents');
                    $update_flag = true;
                }
                if ($this->request->input('passport') != '' && $this->request->input('passport') != null) {
                    $data['passport_file'] = $this->uploader_base64($this->request->input('passport'), 'guard_documents');
                    $update_flag = true;
                }


                if($update_flag){
                    DB::table('guards')->where('id', $id)->update($data);    

                    $this->statusCode = self::STATUS_CODE_200;
                    $this->response = [
                        'success' => true,
                        'message' => 'Profile updated successfully!'
                    ];
                    return $this->sendResponse();
                }
            }



            $this->statusCode = self::STATUS_CODE_200;
            $this->response = [
                'success' => true,
                'message' => 'Nothing to update.'
            ];
            return $this->sendResponse();

        }



        public function update_notification_token(Request $request, $id) {




            $this->request = $request;


            $data = array('notification_token' => $this->request->input('notification_token'));


            DB::table('guards')->where('id', $id)->update($data);    

            $this->statusCode = self::STATUS_CODE_200;
            $this->response = [
                'success' => true,
                'message' => 'Token updated successfully!'
            ];
            return $this->sendResponse();


        }


        private function uploader_base64($file, $folder = 'uploads') {
            try {
            $public_path =  rtrim(app()->basePath('public/'), '');
            // $destinationPath =  rtrim('../../uploads/');
                // $public_path = public_path();
                $public_path = str_replace('portal/public', '', $public_path);
                $public_path = str_replace('apis/public', '', $public_path);
                $public_path = str_replace('appapi.247staffingsolutions.com.au/', 'apis.247staffingsolutions.com.au/public', $public_path);
                $destinationPath = $public_path.$folder.'/';

                $newName = Str::random(25);

                $fileName = $newName . '.jpg';


                $file = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $file));
                file_put_contents($destinationPath.$fileName, $file);

                return $fileName;
            } catch (Exception $e) {
                echo $e->getMessage();
            }
        }



        public function get_profile_info(Request $request, $id) {
            if ($request->has('userType') && $request->input('userType') == 'customer') {
                $user = Customer::where('id', $id)->first();
                if($user){
                    return new CustomerResource($user);
                }
            }

            $user = User::where('id', $id)->first();

            if($user){
                return new UserResource($user);
            }

            $this->statusCode = self::STATUS_CODE_200;
            $this->response = ['success' => false, 'error' => ''];
            return $this->sendResponse();
        }

        function forgotPassword($email,$token){
            $data = [
                'token' => $token,
                'email' => $email
            ];
            Mail::send('mail.forgotPassword', $data, function($mail) use ($data){
                $mail->from('no-reply@247staffingsolution.com.au', 'The Scouts');
                $mail->to($data['email'])->subject("Forgot Password");
            });
        }

        function sendMail($user, $activationCode, $plainPassword) {

            $root= $_SERVER['HTTP_HOST'];
            $root = explode('.', $root);
            $postfix = 'staffingsolution';
            if($root[0] != 'wwww'){
                $postfix = $root[0];
            }else{
                $postfix = $root[1];
            }
            $config_data = DB::connection('mysql2')->table('business_data')->where('domain', '=', $postfix)->first();

            $to = $user['email'];

            $subject = 'Account Activation '.$config_data->title;

          // $from = 'no-reply@'.$_SERVER['HTTP_HOST'];
            $from = $postfix.'@247staffingsolution.com.au';
            if ($config_data->package == 'ci') {
                $link = 'https://'.$_SERVER['HTTP_HOST'] . '/portal/index.php/guard/activation/'.$activationCode;
            }else{
                $link = 'https://'.$_SERVER['HTTP_HOST'] . '/portal/guard_activation/'.$activationCode;
            }

            $link = str_replace('api', 'portal', $link);

          // $logo1 = 'https://'.$_SERVER['HTTP_HOST'] . '/uploads/'. $config_data->logo;
            $logo1 = 'https://'.$_SERVER['HTTP_HOST'] . '/uploads/email_footer.png';

          // $logo2 = 'https://'.$_SERVER['HTTP_HOST']."/portal/files/email-template/ASIAL-Member-Logo-11.png";

          // $logo3 = 'https://'.$_SERVER['HTTP_HOST']."/portal/files/email-template/labour-hire-authority-post-banner-1.jpg";



  // To send HTML mail, the Content-type header must be set

            $headers  = 'MIME-Version: 1.0' . "\r\n";

            $headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";



  // Create email headers

            $headers .= 'From: '.$from."\r\n".

            'Reply-To: '.$from."\r\n" .

            'X-Mailer: PHP/' . phpversion();



          // Compose a simple HTML email message

            $message = 'Hello '. $user['name'] . ',<br><br>';

            $message .= 'Thanks for registering with '.$config_data->title.'. Below, please see your username & temporary password. Click on the below-given link to complete your registration. Please fill & upload all required field & documents.<br><br>';



            $message .= 'Username: '. $user['email'] .'<br>';

            $message .= 'Password: '. $plainPassword .'<br><br>';

            $message .= 'Activate your account from following link:<br><br>';

            $message .= '<a href="'.$link.'">'. $link .'</a><br><br><br>';

            $message .= '<span style="color:blue;">Kind Regards,</span><br><br>';

            $message .= '<span style="color:blue;">National Operation Center<br>1300 613 975<span><br><br>';

            $message .= '<span style="font-size:large;font-weight:bold;color:black;">AMG PTY LTD<br>NOC: 1300 613 975<br>M: 0487 966 9778<br>P.O Box 6155 Point Cook Vic 3030<span><br>';

            $message .= '<span style="font-size:small;font-weight:bold;">E: operations@amgsecurity.com.au<span><br>';

            // $message .= '<span style="font-weight:bold;">W: <a href="https://www.amgsecurity.com.au">www.amgsecurity.com.au</a><span><br><br><br>';

            // $message .= '<img src="'.$logo1.'" style="width:100%;float:left;"/>';




  // Sending email
            try{
              mail($to, $subject, $message, $headers);
          }catch(Exception $e)
          {

          }
      }



      function sendMail_forgot($user,$apikey) {

        $root= $_SERVER['HTTP_HOST'];
        $root = explode('.', $root);
        $postfix = 'staffingsolution';
        if($root[0] != 'wwww'){
            $postfix = $root[0];
        }else{
            $postfix = $root[1];
        }
        $config_data = DB::connection('mysql2')->table('business_data')->where('domain', '=', $postfix)->first();

        $to = $user->email;

        $subject = 'Forgot Password '.$config_data->title;

          // $from = 'no-reply@'.$_SERVER['HTTP_HOST'];
        $from = $postfix.'@247staffingsolution.com.au';

    //     if ($config_data->package == 'ci') {
    //       $link = 'https://'.$_SERVER['HTTP_HOST'] . '/portal/index.php/administrator/user/forgot_password?token='.$apikey.'&userid='.$user['id'];
    //   }else{
    //       $link = 'https://'.$_SERVER['HTTP_HOST'] . '/portal/reset_gaurd_password/'.$apikey;
    //   }

    //   $link = str_replace('api', 'portal', $link);

          // $logo1 = 'https://'.$_SERVER['HTTP_HOST'] . '/portal/'. $config_data->logo;

          // $logo2 = 'https://'.$_SERVER['HTTP_HOST']."/portal/files/email-template/ASIAL-Member-Logo-11.png";

          // $logo3 = 'https://'.$_SERVER['HTTP_HOST']."/portal/files/email-template/labour-hire-authority-post-banner-1.jpg";
      $logo1 = 'https://'.$_SERVER['HTTP_HOST'] . '/uploads/email_footer.png';



  // To send HTML mail, the Content-type header must be set

      $headers  = 'MIME-Version: 1.0' . "\r\n";

        $headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";

        // Create email headers

        $headers .= 'From: ' . $from . "\r\n" .

        'Reply-To: ' . $from . "\r\n" .

        'X-Mailer: PHP/' . phpversion();


          // Compose a simple HTML email message

          $message = '<!DOCTYPE html>
          <html>
          <head>
              <title>The Scouts</title>
          </head>
          <body>
              <table style="padding: 0; border:0; width:100%; background-color:#f2f8f9;">
                  <tbody>
                      <tr>
                          <td>
                              <table style="background-color: #f2f8f9; max-width:670px; margin:0 auto; width:100%; border:0; text-align:center;">
                                  <tbody>
                                      <tr>
                                          <td style="height:80px;">&nbsp;</td>
                                      </tr>
                                      <tr>
                                          <td style="text-align:-webkit-center;">
                                              <img style="width: 8rem; height: 6rem;" src="https://app.247staffingsolutions.com.au/assets/images/logo/scouts.png" alt="The Scouts">
                                          </td>
                                      </tr>
                                      <tr>
                                          <td>
                                              <table style=" width:100%; border:0; max-width:670px; background:#fff; border-radius:3px; text-align:center;-webkit-box-shadow:0 1px 3px 0 rgba(0, 0, 0, 0.16), 0 1px 3px 0 rgba(0, 0, 0, 0.12);-moz-box-shadow:0 1px 3px 0 rgba(0, 0, 0, 0.16), 0 1px 3px 0 rgba(0, 0, 0, 0.12);box-shadow:0 1px 3px 0 rgba(0, 0, 0, 0.16), 0 1px 3px 0 rgba(0, 0, 0, 0.12)">
                                                  <tbody>
                                                      <tr>
                                                          <td style="height:40px;">&nbsp;</td>
                                                      </tr>
                                                      <tr>
                                                          <td style="padding:0 15px;">
                                                              <h1 style="color:rgb(0, 163, 126); font-weight:400; margin:0;font-size:32px;">The Scouts</h1>
                                                              <h4>Forgot Password</h4>
                                                              <a href="https://app.thescouts.com.au/#/reset-password?email=' . urlencode($user->email) . '&token=' . urlencode($apikey) . '" target="_blank" style="background:#3075BA;text-decoration:none !important; display:inline-block; font-weight:500; margin-top:16px; color:#fff;text-transform:uppercase; font-size:14px;padding:10px 12px;display:inline-block;border-radius:3px;">
                                                                  Click Here For Forgot Password
                                                              </a>
                                                          </td>
                                                      </tr>
                                                      <tr>
                                                          <td style="height:40px;">&nbsp;</td>
                                                      </tr>
                                                  </tbody>
                                              </table>
                                          </td>
                                      </tr>
                                      <tr>
                                          <td style="height:20px;">&nbsp;</td>
                                      </tr>
                                      <tr>
                                          <td style="text-align:center;">
                                              <p style="font-size:14px; color:#455056bd; line-height:18px; margin:0 0 0;">We are delighted to welcome you to a new era of staff management innovation with <strong>The Scouts</strong>.</p>
                                          </td>
                                      </tr>
                                      <tr>
                                          <td style="height:80px;">&nbsp;</td>
                                      </tr>
                                  </tbody>
                              </table>
                          </td>
                      </tr>
                  </tbody>
              </table>
          </body>
          </html>';
          

    //   $message = 'Hello '. $user->name . ',<br><br>';

    //   $message .= 'Someone requested to change the password for the following account.';

    //   $message .='<br> Click on the below-given link to reset your password. <br><br>';

    //   $message .='.<br><br>';

    //   $message .= '<a href="'.$link.'">'. $link .'</a><br><br><br>';

    //   $message .= '<span style="color:blue;">Kind Regards,</span><br><br>';

    //   $message .= '<span style="color:blue;">National Operation Center<br>1300 613 975<span><br><br>';

    //   $message .= '<span style="font-size:large;font-weight:bold;color:black;">AMG PTY LTD<br>NOC: 1300 613 975<br>M: 0487 966 9778<br>P.O Box 6155 Point Cook Vic 3030<span><br>';

    //   $message .= '<span style="font-size:small;font-weight:bold;">E: operations@amgsecurity.com.au<span><br>';

    //   $message .= '<span style="font-weight:bold;">W: https://amgsecurity.com.au<span>';

      // $message .= '<img src="'.$logo1.'" style="width:100%;float:left;"/>';



  // Sending email
      try{
          mail($to, $subject, $message, $headers);
        //   echo "i am here";
      }catch(Exception $e)
      {

      }
  }

  function verifyGuardDocument(Request $request)
  {
    $url = "https://www.lars.police.vic.gov.au/LARS/LARS.asp?File=/Components/Screens/PSINFP03/PSINFP03.asp?Process=SEARCH";
    $input_xml = "<XML><HEADER><PROCESS>SEARCH</PROCESS><TIMESTAMP>20211020043340</TIMESTAMP><SECURITYTOKEN>02A42A1B-588D-4EE8-8760-2A81E6221A9A</SECURITYTOKEN></HEADER><PAYLOAD><GNDTLE01 id='idSearchPane'><CONTROL name='dropdownlist'>%</CONTROL><CONTROL name='searchtext'></CONTROL><CONTROL name='SearchCriteriadropdownlist'>X</CONTROL><CONTROL name='SearchAuthNb'>".$request->license_number."</CONTROL><CONTROL name='Index'></CONTROL><CONTROL name='Page'>1</CONTROL></GNDTLE01></PAYLOAD></XML>";

        // new here
    $headers = array(
        "Content-type: text/xml",
        "Content-length: " . strlen($input_xml),
        "Connection: close",
    );

    $ch = curl_init(); 
    curl_setopt($ch, CURLOPT_URL,$url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $input_xml);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

    $data = curl_exec($ch); 
    curl_close($ch);

    if (strpos($data, 'No Results Found')) {
        return response()->json(['success' => false, 'message' => 'Sorry! Your license is not valid according to LRD Victoria Database.']);
    }else{
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
        // return response()->json(['success' => true, 'message' => 'Congrats! Your License is valid and verified from the LRD Victoria Database.', 'expiry' => $data[4]]); 
                $this->response = ['status' => true, 'message' => 'Congrats! Your License is valid and verified from the LRD Victoria Database.', 'expiry' => $data[4]];
                $this->statusCode = self::STATUS_CODE_200;
                return $this->sendResponse(); 
            }else{
                $this->response = ['status' => false, 'message' => 'Sorry! Your license is not valid according to LRD Victoria Database.'];
                $this->statusCode = self::STATUS_CODE_200;
                return $this->sendResponse(); 
    // return response()->json(['success' => false, 'message' => 'Sorry! Your license is not valid according to LRD Victoria Database.']);
            }

        }else
        {
            $this->response = ['status' => false, 'message' => 'Sorry! Your license is not valid according to LRD Victoria Database.'];
            $this->statusCode = self::STATUS_CODE_200;
            return $this->sendResponse(); 
// return response()->json(['success' => false, 'message' => 'Sorry! Your license is not valid according to LRD Victoria Database.']);
        }


    }

}
function getGuardPaysheet(Request $request, $id)
{
    $paysheets = DB::table('guard_uploaded_paysheets')->where('guard_id', $id)->get();
    foreach ($paysheets as $key => $paysheet) {
        $paysheet->file = 'https://'.request()->getHttpHost().'/uploads/'.$paysheet->file;
    }
    if (count($paysheets) > 0) {
        $this->response = ['status' => true, 'message' => 'Paysheet found.', 'paysheets' => $paysheets];
        $this->statusCode = self::STATUS_CODE_200;
        return $this->sendResponse(); 
    }else{
        $this->response = ['status' => false, 'message' => 'No paysheet found!'];
        $this->statusCode = self::STATUS_CODE_200;
        return $this->sendResponse(); 
    }
}
public function deleteUser(Request $request) 
{
        $this->request = $request;
        $this->setValidationRules(['id' => 'required']);
        if ($this->isValidRequest()) {
            $this->response = ['success' => false, 'error' => $this->getErrors()];
            $this->statusCode = self::STATUS_CODE_200;
           return $this->sendResponse();
        }
        if ($request->has('userType') && $request->input('userType') == 'customer') {
            $user = Customer::where('id', $request->input('id'))->update(['status' => 'deleted']);
        }else{
            $user = User::where('id', $request->input('id'))->update(['guard_status' => 'deleted']);
        }

        if($user) {
            $this->response = ['status' => true, 'message' => 'Your account is deleted successfully.'];
            $this->statusCode = self::STATUS_CODE_200;
            return $this->sendResponse();
        }
        $this->response = ['status' => false, 'error' => 'Fail to delete your account!'];
        $this->statusCode = self::STATUS_CODE_200;
        return $this->sendResponse();
    }


    public function aboutUs()
    {
        $about = \DB::table('abouts')->first();
        return response()->json(['success' => true, 'data' => $about]);
    }
}
