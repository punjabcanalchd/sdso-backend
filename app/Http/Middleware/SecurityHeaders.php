<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Generate nonce
        $nonce = base64_encode(random_bytes(16));

        // Make available globally
        app()->instance('csp_nonce', $nonce);

        $response = $next($request);

        $cspHeader = "
            default-src 'self';

            script-src 'self' 'nonce-{$nonce}' https://js.arcgis.com https://*.arcgis.com;
            style-src 'self' 'nonce-{$nonce}' https://js.arcgis.com;

            img-src 'self' data:
                https://pwrda.punjab.gov.in
                https://js.arcgis.com
                https://*.arcgis.com
                https://services.arcgisonline.com
                https://server.arcgisonline.com;

            font-src 'self' data:
                https://fonts.gstatic.com
                https://*.arcgis.com;

            connect-src 'self'
                https://localhost:8020
                https://nicdsign.kerala.nic.in
                https://services.arcgisonline.com
                https://js.arcgis.com
                https://mapservice.gov.in
                https://cdn.arcgis.com
                https://basemaps.arcgis.com;

            worker-src 'self' blob:;
            frame-ancestors 'self';
            object-src 'none';
        ";

        // Clean formatting
        $cspHeader = trim(preg_replace('/\s+/', ' ', $cspHeader));

        return $response
            ->header('X-Frame-Options', 'SAMEORIGIN')
            ->header('X-XSS-Protection', '1; mode=block')
            ->header('X-Content-Type-Options', 'nosniff')
            ->header('Referrer-Policy', 'no-referrer-when-downgrade')
            ->header('Permissions-Policy', 'geolocation=()')
            ->header('Content-Security-Policy', $cspHeader);
    }
}