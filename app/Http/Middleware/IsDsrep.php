<?php 

namespace App\Http\Middleware;

use Closure;
use App\Exceptions\MediAPIException;
use \Illuminate\Support\Facades\Auth;

class IsDsrep
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
        $privilegedUserTypes = config('shl.distributor_sales_rep_type');

        $user = Auth::user();

        if($user->u_tp_id != $privilegedUserTypes) throw new MediAPIException("You haven't permissions to login via app.",10);

        return $next($request);
    }
}