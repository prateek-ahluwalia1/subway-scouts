<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Contracts\Auth\Factory as Auth;


class Authenticate
{
    /**
     * The authentication guard factory instance.
     *
     * @var \Illuminate\Contracts\Auth\Factory
     */
    protected $auth;

    /**
     * Create a new middleware instance.
     *
     * @param  \Illuminate\Contracts\Auth\Factory  $auth
     * @return void
     */
    public function __construct(Auth $auth)
    {
        $this->auth = $auth;
        //dd($this->auth);
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string|null  $guard
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        // dd('AOA');
        // //dd($request->header('Authorization'));
        // $user = User::where('auth_token', $request->header('Authorization'))->first();
        // dd($user);
        // //dd($user);
        // if($user){
        //     // $data = [
        //     //     "userId" => $user->id,
        //     //     "name" =>   $user->name,
        //     //     "phone" =>  $user->name,
        //     //     "is_super_admin" => $user->is_super_admin,
        //     //     "email" => $user->email,
        //     // ];
        //     // session($data);
        //     return $next($request);
        // }
        // return response()->json(['success' => false, 'message' => 'Token Expired!'], 401);


        
    }
}
