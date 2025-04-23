<?php

namespace App\Http\Middleware;

use App\Providers\RouteServiceProvider;
use Illuminate\Auth\Middleware\Authenticate as Middleware;

class Authenticate extends Middleware
{
    protected function redirectTo($request)
    {
        if ($request->getBaseUrl() == '/login/student') {
            return route('/login/student');
        }
        if (!$request->expectsJson()) {
            return route('login');
        }
    }
}
