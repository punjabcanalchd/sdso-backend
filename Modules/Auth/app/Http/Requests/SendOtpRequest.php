<?php

namespace Modules\Auth\Http\Requests;

use App\Http\Requests\BaseRequest;
use App\Validation\Rules\CommonRules;

class SendOtpRequest extends BaseRequest
{
    protected function prepareForValidation(): void
    {
        parent::prepareForValidation();
        $mobileNumber = $this->input('mobile_number')
            ?? $this->input('mobileNumber')
            ?? $this->input('ownerMobileNumber');

        $this->merge([
            'mobile_number' => $mobileNumber
        ]);
    }

    public function rules(): array
    {
        return [
            'mobile_number' => CommonRules::phone()
        ];
    }

    public function messages(): array
    {
        return [
            'mobile_number.regex' => 'Please enter a valid mobile number.',
        ];
    }
}