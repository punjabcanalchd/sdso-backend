<?php

namespace Modules\Auth\Http\Controllers;

use App\Helpers\RSAHelper;
use Illuminate\Support\Facades\Cookie;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log;
use Modules\Auth\Http\Requests\LoginRequest;
use Modules\Auth\Http\Requests\RegisterRequest;
use Modules\Auth\Http\Requests\ForgotPasswordRequest;
use Modules\Auth\Http\Requests\ChangeForgotPasswordRequest;
use Modules\Auth\Http\Requests\UpdateProfileRequest;
use Modules\Auth\Services\AuthService;
use Modules\Auth\Services\CaptchaService;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    protected AuthService $authService;
    protected CaptchaService $captchaService;

    public function __construct(
        AuthService $authService,
        CaptchaService $captchaService
    ) {
        $this->authService = $authService;
        $this->captchaService = $captchaService;
    }

    /**
     * Login
     */
    public function login(LoginRequest $request)
    {
        /**
         * Captcha Verification
         */
        if (defined('captcha_validation') && captcha_validation) {
            $captchaCheck = $this->captchaService->verify(
                $request->captcha_id,
                $request->captcha_input
            );

            if (!$captchaCheck['success']) {
                return response()->json($captchaCheck, $captchaCheck['status']);
            }
        }

        /**
         * Authenticate
         */
        $response = $this->authService->login([
            'email' => $request['email'],
            'password' => RSAHelper::decrypt($request['password']),
        ]);

        /**
         * Failed Login
         */
        if (!$response['success']) {
            return response()->json([
                'success' => false,
                'message' => $response['message'],
            ], $response['status']);
        }

        /**
         * Success Response
         */
        return response()

            ->json([
                'success' => true,
                'message' => $response['message'],
                'user' => $response['data']['user'],
            ], 200)

            /**
             * Access Token Cookie
             */
            ->cookie(
                'access_token',
                $response['data']['accessToken'],
                config('jwt.ttl'),
                $response['data']['cookiePath'],
                config('session.domain'),
                config('session.secure'),
                config('session.http_only'),
                false,
                config('session.same_site')
            );
    }

    /**
     * Current User
     */
    public function user()
    {
        Log::channel('test')->info('User info requested', [
            'user_id' => auth('api')->id(),
        ]);
        return response()->json([
            'success' => true,
            'user' => $this->authService->user(),
        ]);
    }

    /**
     * Refresh Token
     */
    public function refresh()
    {
        $response = $this->authService->refresh();
        if (!$response['success']) {
            return response()->json([
                'success' => false,
                'message' => $response['message'],
            ], $response['status']);
        }

        return response()
            ->json([
                'success' => true,
                'message' => $response['message'],
            ], 200)
            ->cookie(
                'access_token',
                $response['data']['accessToken'],
                config('jwt.ttl'),
                $response['data']['cookiePath'],
                config('session.domain'),
                config('session.secure'),
                config('session.http_only'),
                false,
                config('session.same_site')
            );
    }

    /**
     * Logout
     */
    public function logout()
    {
        $user = auth('api')->user();
        $cookiePath = $user && $user->role ? '/' . $user->role->slug : '/';

        $this->authService->logout();

        return response()
            ->json([
                'success' => true,
                'message' => 'Logged out',
            ])
            ->withCookie(Cookie::forget(
                'access_token',
                $cookiePath,
                config('session.domain')
            ));
    }

    /**
     * Generate Captcha
     */
    public function getCaptcha()
    {
        $response = $this->captchaService->generate();

        return response()->json($response, $response['status']);
    }

    /**
     * Register
     */
    public function register(RegisterRequest $request)
    {
        $result = $this->authService->register($request->validated());       
        return response()->json($result, $result['status']);
    }

    /**
     * Forgot Password
     */
    public function forgotPasswordEmail(ForgotPasswordRequest $request)
    {        
        $response = $this->authService->forgotPasswordEmail(
            $request->email
        );

        return response()->json($response, $response['status']);
    }

    /**
     * Validate Forgot Password Hash
     */
    public function validateForgotPasswordHash(string $hash)
    {
        $response = $this->authService->validateForgotPasswordHash($hash);

        return response()->json($response, $response['status']);
    }

    /**
     * Change Forgot Password
     */
    public function changeForgotPassword(ChangeForgotPasswordRequest $request)
    {
                $response = $this->authService->changeForgotPassword(
            $request->hash,
            $request->new_password,
            $request->confirm_password
        );
        return response()->json($response, $response['status']);
    }

    public function validateVerificationHash(string $hash)
    {
        $response = $this->authService->validateVerificationHash($hash);
        return response()->json($response, $response['status']);
    }

    public function verify(Request $request)
    {
        $request->validate(['hash' => 'required|string']);
        
        $response = $this->authService->verifyAccount($request->hash);

        return response()->json($response, $response['status']);
    }

    /**
     * Get Profile
     */
    public function getProfile()
    {
        $response = $this->authService->getProfile();

        return response()->json([
            'status' => true,
            'message' => $response['message'],
            'data' => $response['data']
        ], $response['status']);
    }

    /**
     * Update Profile
     */
    public function updateProfile(UpdateProfileRequest $request)
    {
        $response = $this->authService->updateProfile($request->validated());

        if (!$response['success']) {
            return response()->json([
                'status' => false,
                'message' => $response['message']
            ], $response['status']);
        }

        return response()->json([
            'status' => true,
            'message' => $response['message'],
            'data' => $response['data']
        ], $response['status']);
    }

    /**
     * Change Password
     */
    public function changePassword(\Modules\Auth\Http\Requests\ChangePasswordRequest $request)
    {
        $response = $this->authService->changePassword(
            $request->old_password,
            $request->new_password
        );

        if (!$response['success']) {
            return response()->json([
                'status' => false,
                'message' => $response['message']
            ], $response['status']);
        }

        return response()->json([
            'status' => true,
            'message' => $response['message'],
            'data' => $response['data']
        ], $response['status']);
    }

}