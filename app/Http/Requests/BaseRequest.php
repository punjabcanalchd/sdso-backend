<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Validation\Traits\SanitizesInput;
use App\Validation\Messages\ValidationMessages;
use App\Validation\Messages\AttributeNames;

abstract class BaseRequest extends FormRequest
{
    use SanitizesInput;

    public function authorize(): bool
    {
        return true;
    }

    public function messages(): array
    {
        return ValidationMessages::messages();
    }

    public function attributes(): array
    {
        return AttributeNames::attributes();
    }
}