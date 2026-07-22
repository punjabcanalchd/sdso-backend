<?php

namespace Modules\Auth\Http\Requests;

use App\Http\Requests\BaseRequest;
use App\Validation\Rules\CommonRules;
use App\Validation\Patterns\RegexPatterns;
use App\Rules\OtpVerified;
use Illuminate\Validation\Rule;
class UpdateProfileRequest extends BaseRequest
{

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'first_name' => ['sometimes', ...CommonRules::name()],
            'middle_name' => [
                'sometimes',
                'nullable',
                'string',
                'min:2',
                'max:100',
                'regex:' . RegexPatterns::ALPHA,
            ],
            'last_name' => ['sometimes', ...CommonRules::name()],
            'mobile_number' => [
                'sometimes',
                ...CommonRules::phone(),
                Rule::unique('users', 'mobile_number')->ignore(auth('api')->id()),
                new OtpVerified(),
            ],
        ];
    }

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }
}
