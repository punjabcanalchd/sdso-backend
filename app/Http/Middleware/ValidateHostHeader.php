<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class ValidateHostHeader
{
    //     public function handle(Request $request, Closure $next)
    //     {

    //         // $hosts = env('ALLOWED_DOMAINS');
    //         // $allowedHosts = explode(',',$hosts);

    //         // // Get the host from the request
    //         // $host = $request->getHost();

    //         // // Validate the host
    //         // if (!in_array($host, $allowedHosts)) {
    //         //     abort(403, 'Forbidden: Invalid Host Header');
    //         // }

    //         // return $next($request);

    //         $allowedHosts = explode(',', env('ALLOWED_DOMAINS'));

    //         if (! in_array($request->getHost(), $allowedHosts)) {
    //             return response()->json([
    //                 'message' => 'Forbidden: Invalid Host Header',
    //             ], 403);
    //         }

    //         return $next($request);
    //     }

    public function handle(Request $request, Closure $next)
    {
        $host = $request->getHost();
        $allowedHosts = config('security.allowed_domains');

        \Log::info('Host Validation', [
            'host' => $host,
            'allowedHosts' => $allowedHosts,
            'env' => env('ALLOWED_DOMAINS'),
        ]);

        if (! in_array($host, $allowedHosts, true)) {
            return response()->json([
                'message' => 'Forbidden: Invalid Host Header',
                'host' => $host,
                'allowed' => $allowedHosts,
            ], 403);
        }

        return $next($request);
    }
}
