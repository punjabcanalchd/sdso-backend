<?php

namespace App\Validation\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ValidCaptchaRule implements ValidationRule
{
    public function validate(string $attribute,mixed $value,Closure $fail): void {
        if (!preg_match('/^[A-Za-z0-9]{5,6}$/', $value)) {
            $fail('Invalid captcha.');
        }
    }
}