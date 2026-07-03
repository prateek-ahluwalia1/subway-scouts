<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;

class CheckAdminPermission
{
  /**
   * Handle an incoming request.
   *
   * @param  \Illuminate\Http\Request  $request
   * @param  \Closure  $next
   * @return mixed
   */
  public function handle(Request $request, Closure $next)
  {
    $arraypm = [];
    $pm = Route::currentRouteName();
    $token =Str::replace('Bearer', '', $request->header('AuthorizationToken'));
    $token =Str::replace(' ', '', $token);
    $user = User::where('auth_token', $token)->first();

    if ($user->userType == 'super-admin') {
      // ,'users.specific_customer', 'users.specific_sites'
      $permissions = DB::table('acces_level_defination')->join('users', 'users.access_level_id', '=', 'acces_level_defination.id')->where('users.id', $user->id)->value('acces_level_defination.permissions');
       foreach (json_decode($permissions, true) as $key => $value) {
        if($value == 'true'){
          array_push($arraypm, $key); 
        }
       }
       if(in_array($pm,$arraypm) ){
            return $next($request);
        }else{
          return response()->json(['message' => "Sorry You Don't Have Permission To Access This Route", 'code' => '401', 'success' => 'false'], 401);
        }
    }else{
      return response()->json(['message' => "Sorry You are not Super Admin", 'code' => '401', 'success' => 'false'], 404);
    }
    
  }
}