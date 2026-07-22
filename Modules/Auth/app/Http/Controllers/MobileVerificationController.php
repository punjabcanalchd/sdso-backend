<?php

namespace Modules\Auth\Http\Controllers;

use App\Http\Controllers\Controller;
use Modules\Auth\Http\Requests\SendOtpRequest;
use Modules\Auth\Http\Requests\VerifyOtpRequest;
use Modules\Auth\Services\MobileVerificationService;

class MobileVerificationController extends Controller
{
    protected MobileVerificationService $mobileVerificationService;

    public function __construct(MobileVerificationService $mobileVerificationService)
    {
        $this->mobileVerificationService = $mobileVerificationService;
    }

    public function sendOtp(SendOtpRequest $request)
    {

        // dd('reached');
        $isRegistration = $request->filled('mobileNumber') || $request->filled('mobile_number');

        $mobileNumber = $request->input('mobile_number')
            ?? $request->input('mobileNumber')
            ?? $request->input('ownerMobileNumber');

        $response = $this->mobileVerificationService->generateOtp(
            $mobileNumber,
            $isRegistration,
            $request->mobile_template_id
        );

        return response()->json(
            $response,
            $response['status']
        );
    }

    public function verifyOtp(VerifyOtpRequest $request)
    {
        $response = $this->mobileVerificationService->verifyOtp(
            $request->validated()
        );

        return response()->json(
            $response['data'],
            $response['status']
        );
    }
}
