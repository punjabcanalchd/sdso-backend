<?php

namespace App\Validation\Sanitizers;

class InputSanitizer
{
    public static function sanitize(array $data): array {
        array_walk_recursive($data, function (&$value) {
            if (is_string($value)) {
                $value = trim($value);
                $value = strip_tags($value);
                $value = preg_replace('/\s+/',' ', $value);
            }
        });
        return $data;
    }
}