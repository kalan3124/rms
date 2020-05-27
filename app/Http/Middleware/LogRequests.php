<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;

class LogRequests
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
        $user = Auth::user();

        $body = '<b>URL:-</b><a>'.
            $request->fullUrl().
            '</a><br/><b>Request:-</b><pre><code>'.
            json_encode($request->all()).
            '</code></pre><br/><b>IP Address:-</b><a>'.
            $_SERVER['REMOTE_ADDR'].
            '</a><br/><b>User:-</b><a>'.
            $user->getKey().
            '</a><br/><b>Date and time:-</b><a>'.
            date("Y-m-d H:i:s").
            '</a><hr/><br/><br/>';

        if(Storage::exists('/public/logs/'.date("Y/m/d").".html")){
            Storage::append('/public/logs/'.date("Y/m/d").".html",$body );
        } else {
            Storage::put('/public/logs/'.date("Y/m/d").".html", "<html><head><style> pre,code{   white-space: pre-wrap;white-space: -moz-pre-wrap;white-space: -pre-wrap;white-space: -o-pre-wrap;word-wrap: break-word;}</style></head><body>".$body);
        }

        return $next($request);
    }
}
