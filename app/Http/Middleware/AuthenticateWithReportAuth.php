<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class AuthenticateWithReportAuth
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if($request->has('token')){
            $token = $request->input('token');

            $user = User::where('report_access_token',$token)->first();

            if($user){
                Auth::loginUsingId($user->getKey());

                return $next($request);
            } 
        }

        abort(403);
    }
}
