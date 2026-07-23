<?php

namespace Modules\Auth\Http\Requests;

use App\Http\Requests\BaseRequest;
use App\Helpers\RSAHelper;
use App\Validation\Rules\CommonRules;

class LoginRequest extends BaseRequest
{
    public function rules(): array
    {
        return [
            'hrmscode' => CommonRules::positiveInteger(),
            'password' => 'required|string',
        ];
    }
}