<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class SkipJwtForPublicRoutes
{
    protected $publicRoutes = [
        'api/auth/register',
        'api/auth/login',
        'api/auth/forgot/password',
        'api/auth/reset/password',
    ];

    public function handle(Request $request, Closure $next)
    {
        if (in_array($request->path(), $this->publicRoutes)) {
            return $next($request);
        }
        
        // For other routes, JWT will handle authentication
        return $next($request);
    }
}