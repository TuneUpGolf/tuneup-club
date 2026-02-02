<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class UpdateOnlineStatus
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
        // if (auth()->check()) {
        //     extend_user_online(); // Adds/resets to 30 minutes
        // }

        return $next($request);
    }
}
