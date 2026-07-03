<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;

class CheckSuperAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next): RedirectResponse|Response|JsonResponse
    {
        $token =Str::replace('Bearer', '', $request->header('AuthorizationToken'));
        $token =Str::replace(' ', '', $token);
        $user = User::where('auth_token', $token)->where('is_super_admin', 1)->where('userType', 'super-admin')->first();
        if ($user) {
            return $next($request);
        } else {
            return response()->json(['success' => false, 'message' => 'You do not have access to this route!'], 401);
        }
    }
}
