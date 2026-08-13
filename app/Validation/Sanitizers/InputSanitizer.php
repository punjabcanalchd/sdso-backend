<?php

namespace App\Validation\Sanitizers;

class InputSanitizer
{
    /**
     * Fields that are allowed to contain HTML.
     */
    protected static array $htmlFields = [
        'description',
    ];

    public static function sanitize(array $data): array
    {
        foreach ($data as $key => &$value) {

            // Preserve HTML for specific fields
            if (in_array($key, self::$htmlFields, true)) {
                continue;
            }

            if (is_array($value)) {
                $value = self::sanitize($value);
                continue;
            }

            if (is_string($value)) {
                $value = trim($value);
                $value = strip_tags($value);
                $value = preg_replace('/\s+/', ' ', $value);
            }
        }

        return $data;
    }
}