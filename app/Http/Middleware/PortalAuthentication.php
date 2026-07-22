<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class PortalAuthentication
{
    public function handle(Request $request, Closure $next)
    {
        $user = auth('api')->user();

        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthorized'
            ], 401);
        }

        return $next($request);
    }
}