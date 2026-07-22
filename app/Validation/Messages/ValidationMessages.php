<?php

namespace App\Validation\Messages;

class ValidationMessages
{
    public static function messages(): array
    {
        return [
            '*.required' => 'The :attribute field is required.',
            '*.regex' => 'Invalid :attribute format.',
            '*.min' => ':attribute is too short.',
            '*.max' => ':attribute exceeds maximum length.',
            '*.mimes' => 'Invalid file type.',
            '*.email' => 'Invalid email address.',
            '*.size' => 'The :attribute must be exactly :size characters.',
            '*.same' => 'The :attribute does not match.',
            '*.unique' => 'The :attribute has already been taken.',
            '*.file' => 'The uploaded file is invalid.',
            '*.digits' => 'The :attribute must be :digits digits.',
        ];
    }
}