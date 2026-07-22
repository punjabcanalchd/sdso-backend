<?php

namespace App\Validation\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class StrongPasswordRule implements ValidationRule
{
    public function validate(string $attribute,mixed $value,Closure $fail
    ): void {

        if (
            !preg_match('/[A-Z]/', $value) ||
            !preg_match('/[a-z]/', $value) ||
            !preg_match('/[0-9]/', $value) ||
            !preg_match('/[@$!%*?&]/', $value)
        ) {

            $fail(
                'Password must contain uppercase, lowercase, number and special character.'
            );
        }
    }
}