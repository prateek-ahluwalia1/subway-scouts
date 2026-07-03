<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use App\Http\Controllers\ApiController;
use App\Models\Guard;
use Illuminate\Support\Str;

class GuardLoginController extends ApiController
{
    public function __construct()
    {
        Config::set('auth.default.guard', 'guard-api');      
    }


    public function login(Request $request) {
        // if ($request->has('userType') && $request->input('userType') == '') {
            $this->request = $request;
            $this->setValidationRules(['email' => 'required|email', 'password' => 'required']);
            if ($this->isValidRequest()) {
                $this->response = ['success' => false, 'error' => $this->getErrors()];
                $this->statusCode = self::STATUS_CODE_200;
                return $this->sendResponse();
            }
    
            $guard = Guard::where('email', $request->input('email'))->first();
            if($guard && $guard->guard_status !== Guard::ACTIVE_STATUS) {
                $this->response = ['status' => false, 'error' => 'Your account is inactive please contact your Administrator.'];
                $this->statusCode = self::STATUS_CODE_200;
                return $this->sendResponse();
            }
            $credentials = request(['email', 'password']);
    
            if (!$token = auth('guard-api')->attempt($credentials)) {
                return response()->json(['error' => 'Unauthorized'], 401);
            }
            $guard = Guard::where('email', $request->email)->first();
            $guard->auth_token = $token;
            $guard->save();
            return $this->respondWithToken($token);
        //  }
    
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

    public function guardLogout(Request $request)
        {
            $token =Str::replace('Bearer', '', $request->header('Authorization'));
            $token =Str::replace(' ', '', $token);
            $guard = Guard::where('auth_token', $token)->first();
            if ($guard) {
                $guard->auth_token = NULL;
                $guard->update();
                auth()->logout();
                return response()->json(['message' => 'Successfully logged out']);
            }
       }
}
