<?php

namespace App\Http\Middleware;

use Closure;
use \Illuminate\Support\Facades\Auth;

class HasItinerary
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
        Auth::user()->checkItinerary();

        return $next($request);
    }
}
