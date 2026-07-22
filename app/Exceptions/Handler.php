<?php

namespace App\Exceptions;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Support\Facades\Log;
use Throwable;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;

class Handler extends ExceptionHandler
{
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    public function register(): void
    {
        // Log all exceptions
        $this->reportable(function (Throwable $e) {
            Log::error($e->getMessage(), [
                'exception' => $e,
            ]);
        });

        // Return JSON 401 for JWT token errors instead of HTML 500
        $this->renderable(function (TokenExpiredException $e) {
            return response()->json(['success' => false, 'message' => 'Token has expired.'], 401);
        });

        $this->renderable(function (TokenInvalidException $e) {
            return response()->json(['success' => false, 'message' => 'Token is invalid.'], 401);
        });

        $this->renderable(function (JWTException $e) {
            return response()->json(['success' => false, 'message' => 'Token not provided.'], 401);
        });

        $this->renderable(function (AuthenticationException $e) {
            return response()->json(['success' => false, 'message' => 'Unauthenticated.'], 401);
        });
    }
}