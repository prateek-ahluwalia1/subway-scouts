<?php

namespace App\Http\Middleware;

use App\Models\Customer;
use App\Models\Guard;
use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Http\Middleware\BaseMiddleware;

class CheckCustomer extends BaseMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next, $role = null)
    {
        $token =Str::replace('Bearer', '', $request->header('AuthorizationToken'));
        $token =Str::replace(' ', '', $token);
        $user = User::where('auth_token', $token)->first();
        $customer = Customer::where('auth_token', $token)->first();
        if($user){
            return $next($request);
        }elseif($customer){
            return $next($request);
        }else{
            return response()->json(['success' => false, 'message' => 'Unauthenticated!']);
        }
    }
}
