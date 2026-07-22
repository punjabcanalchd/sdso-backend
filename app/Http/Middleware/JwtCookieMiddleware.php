<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class JwtCookieMiddleware
{
    public function handle(
        Request $request,
        Closure $next
    ) {

        $token = $request->cookie('access_token');

        // Log::channel('test')->info('JwtCookieMiddleware invoked', [
        //     'cookie_token' => $token
        // ]);

        if ($token) {

            $request->headers->set(
                'Authorization',
                'Bearer ' . $token
            );
        }

        return $next($request);
    }
}