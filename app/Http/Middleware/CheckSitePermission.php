<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Route;

class CheckSitePermission
{
    
    public function handle(Request $request, Closure $next)
    {
        // $pm = Route::currentRouteName();
        // $token =Str::replace('Bearer', '', $request->header('AuthorizationToken'));
        // $token =Str::replace(' ', '', $token);
        // $user = User::where('auth_token', $token)->with('sitePermission')->first();
        // if (in_array($pm, $user->sitePermission->specific_customer)) {
        //     return $next($request);
        // }else{
        //     return response()->json(['message' => "Sorry You Don't Have Permission To Access This Route", 'code' => '401', 'success' => false], 401);
        // }
        
    }
}
