<?php

namespace Modules\Auth\Http\Requests;

use App\Http\Requests\BaseRequest;
use App\Validation\Rules\CommonRules;

class VerifyOtpRequest extends BaseRequest
{

    protected function prepareForValidation(): void
    {
        parent::prepareForValidation();

        $this->merge([
            'mobileNumber' => $this->input('mobile_number') ?? $this->input('mobileNumber'),
            'sandes_verification_otp' => $this->input('otp') ?? $this->input('sandes_verification_otp'),
        ]);
    }

    public function rules(): array
    {
        return [
            'mobileNumber' => CommonRules::phone(),

            'sandes_verification_otp' => [
                'required',
                'digits:6'
            ],
            'unit_id' => [
                'nullable',
                'string'
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'mobileNumber.regex' => 'Please enter a valid mobile number.',

            'sandes_verification_otp.required' => 'OTP is required.',
            'sandes_verification_otp.digits' => 'OTP must be 6 digits.',
        ];
    }
}