<?php

namespace App\Validation\Traits;

use App\Validation\Sanitizers\InputSanitizer;

trait SanitizesInput
{
    protected function prepareForValidation()
    {
        $this->merge(InputSanitizer::sanitize($this->all()));
    }
}