<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class AdminHRMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        if (!auth()->user() || !in_array(auth()->user()->role, ['admin', 'hr'])) {
            abort(403);
        }

        return $next($request);
    }
}