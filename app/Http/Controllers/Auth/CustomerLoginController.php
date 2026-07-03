<?php

namespace App\Http\Controllers\Auth;

use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use App\Http\Controllers\ApiController;
use Illuminate\Support\Str;
class CustomerLoginController extends ApiController
{
    public function __construct()
    {
        Config::set('auth.default.guard', 'customer-api'); 
    }



    public function login(Request $request) { 

    //  if ($request->has('userType') && $request->input('userType') == 'customer') {
        $this->request = $request;
        $this->setValidationRules(['email' => 'required|email', 'password' => 'required']);
        if ($this->isValidRequest()) {
            $this->response = ['success' => false, 'error' => $this->getErrors()];
            $this->statusCode = self::STATUS_CODE_200;
            return $this->sendResponse();
        }

        $user = Customer::where('email', $request->input('email'))->first();
        if($user && $user->status !== Customer::ACTIVE_STATUS) {
            $this->response = ['status' => false, 'error' => 'Your account is inactive please contact your Administrator.'];
            $this->statusCode = self::STATUS_CODE_200;
            return $this->sendResponse();
        }
        $credentials = request(['email', 'password']);
        if (!$token = auth('customer-api')->attempt($credentials)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $user = Customer::where('email', $request->email)->first();
        $user->auth_token = $token;
        $user->save();
        return $this->respondWithToken($token);
    }

    protected function respondWithToken($token)
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            //'expires_in' => strtotime(date('Y-m-d H:i:s', strtotime("+60 min"))),
            // 'user' => auth()->user(),
        ]);
    }

    public function customerLogout(Request $request)
    {
            $token =Str::replace('Bearer', '', $request->header('Authorization'));
            $token =Str::replace(' ', '', $token);
            $customer = Customer::where('auth_token', $token)->first();
            if ($customer) {
                $customer->auth_token = NULL;
                $customer->update();
                auth()->logout(true);
                return response()->json(['message' => 'Successfully logged out']);
            }
    }


}
