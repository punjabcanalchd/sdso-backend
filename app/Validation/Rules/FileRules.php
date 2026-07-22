<?php

namespace App\Validation\Rules;

class FileRules
{
    public static function image(int $maxMB = 2): array {

        return [
            'nullable',
            'file',
            'mimes:jpg,jpeg,png,webp',
            'max:' . ($maxMB * 1024),
        ];
    }

    public static function pdf(int $maxMB = 5): array {

        return [
            'nullable',
            'file',
            'mimes:pdf',
            'max:' . ($maxMB * 1024),
        ];
    }
}