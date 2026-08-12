<?php

namespace App\Validation\Rules;

class FileRules
{
    public static function image( int $maxMB = 2, array $extensions = ['jpg', 'jpeg', 'png', 'webp'], ?int $width = null, ?int $height = null, bool $required = false ): array 
    { 
        $rules = [ 
            $required ? 'required' : 'nullable', '
            file', 
            'mimes:' . implode(',', $extensions), 
            'max:' . ($maxMB * 1024), 
        ]; 
        
        if ($width !== null && $height !== null) 
            { $rules[] = "dimensions:width={$width},height={$height}"; 
        } 
        
        return $rules;
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