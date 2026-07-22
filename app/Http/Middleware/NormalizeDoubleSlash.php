<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class NormalizeDoubleSlash
{
    public function handle(Request $request, Closure $next)
    {
        $path = $request->path();

        // Normalize multiple slashes
        if (strpos($path, '//') !== false) {
            $normalized = preg_replace('#/+#','/', $path);

            // Update request path internally (no redirect)
            $request->server->set('REQUEST_URI', $normalized);
        }

        return $next($request);
    }
}