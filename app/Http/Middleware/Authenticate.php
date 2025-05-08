<?php
namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;

class Authenticate extends Middleware
{
    protected function redirectTo($request)
    {
        if ($request->getBaseUrl() == '/login/follower') {
            return route('/login/follower');
        }
        if (! $request->expectsJson()) {
            return route('login');
        }
    }
}
