<?php

namespace App\Rules;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Auth;
use Illuminate\Contracts\Validation\Rule;

class OtpVerified implements Rule
{
    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed   $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        // The rule is applied on the mobile_number field, but we only need to
        // verify that the OTP verification flag exists for the authenticated user.
        $userId = Auth::guard('api')->id();
        if (!$userId) {
            return false;
        }
        return Cache::has('otp_verified:' . $userId);
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'Mobile number cannot be updated until OTP is verified.';
    }
}
