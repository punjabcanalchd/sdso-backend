<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;

class Locale
{
    public function handle(Request $request, Closure $next)
    {
        // Get language from header (default: en)
        $locale = $request->header('Accept-Language', 'en');

        // Supported languages
        $allowed = ['en', 'pa'];

        // Fallback to English if invalid
        if (!in_array($locale, $allowed)) {
            $locale = 'en';
        }

        // Set application locale
        App::setLocale($locale);

        return $next($request);
    }
}