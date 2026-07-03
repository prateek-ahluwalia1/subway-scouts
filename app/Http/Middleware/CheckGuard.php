<?php

namespace App\Http\Middleware;

use App\Models\Guard;
use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CheckGuard
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    { 
        $token= Str::replace('Bearer', '', $request->header('AuthorizationToken'));
        $token= Str::replace(' ', '', $token);
        $user = User::where('auth_token', $token)->first();
        $guard= Guard::where('auth_token', $token)->first();
        if($user){
            return $next($request);
        }
        elseif($guard){
            return $next($request);
        }else{
            return response()->json(['success' => false, 'message' => 'Unauthenticated!'], 401);
        }
    }
}
