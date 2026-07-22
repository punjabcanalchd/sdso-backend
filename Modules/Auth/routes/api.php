<?php


use Illuminate\Support\Facades\Route;
use Modules\Auth\Http\Controllers\AuthController;
use Modules\Auth\Http\Controllers\MobileVerificationController;


Route::prefix('auth')->group(function () {

    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:5,1')->name('auth.login_user');
    Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:5,1')->name('auth.register_user');
    Route::middleware('auth:api')->group(function () {
        Route::get('/user', [AuthController::class, 'user'])->name('auth.get_authenticated_user');
        Route::post('/logout', [AuthController::class, 'logout'])->name('auth.logout_user');
        
        // Profile Routes
        Route::get('/profile', [AuthController::class, 'getProfile'])->name('auth.get_user_profile');
        Route::post('/profile', [AuthController::class, 'updateProfile'])->name('auth.update_user_profile');
        
        // Change Password
        Route::post('/changePassword', [AuthController::class, 'changePassword'])->name('auth.change_user_password');
    });
    Route::post('/getCaptcha', [AuthController::class, 'getCaptcha'])->middleware('throttle:10,1')->name('auth.get_captcha');
    // Mobile Verification
    Route::post('/sendOtp', [MobileVerificationController::class, 'sendOtp'])->middleware('throttle:5,1')->name('auth.send_mobile_otp');
    Route::post('/verifyOtp', [MobileVerificationController::class, 'verifyOtp'])->name('auth.verify_mobile_otp');
    // Forgot Password
    Route::post('/forgotPasswordEmail', [AuthController::class, 'forgotPasswordEmail'])->middleware('throttle:5,1')->name('auth.send_forgot_password_email');
    Route::get('/validateForgotPasswordHash/{hash}', [AuthController::class, 'validateForgotPasswordHash'])->name('auth.validate_forgot_password_hash');
    Route::post('/changeForgotPassword',[AuthController::class, 'changeForgotPassword'])->middleware('throttle:5,1')->name('auth.reset_forgot_password');
    Route::get('/validateVerificationHash/{hash}', [AuthController::class, 'validateVerificationHash'])->name('auth.validate_verification_hash');
    Route::post('/verifyAccount', [AuthController::class, 'AcountVerification'])->name('auth.verify_account');
});
