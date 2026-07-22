<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Http\UploadedFile;
use Smalot\PdfParser\Parser;

class Filename implements ValidationRule
{
    protected string $regex;

    public function __construct(string $regex = '/^[a-zA-Z0-9 _]+$/')
    {
        $this->regex = $regex;
    }

    public function validate(string $attribute, mixed $value, \Closure $fail): void
    {
        if (!($value instanceof UploadedFile) || !$value->isValid()) {
            $fail('The uploaded file is invalid.');
            return;
        }

        $mimeType = $value->getClientOriginalExtension();
        $checkForJavaScript = true;

        if ($mimeType === 'pdf') {
            $checkForJavaScript = $this->checkForJavaScript($value->getPathname());
        }

        $explode = explode('.', $value->getClientOriginalName());
        if (count($explode) > 2) {
            $fail('The file name contains too many dots.');
            return;
        }

        $nameValid = preg_match($this->regex, $explode[0]) > 0;
        $extension = strtolower(end($explode));

        if (!($nameValid && ($mimeType === $extension || $mimeType !== 'pdf') && $checkForJavaScript)) {
            $fail('There might be an issue related to file name or content of the uploaded document.');
        }
    }

    protected function checkForJavaScript(string $pdfPath): bool
    {
        try {
            $parser = new Parser();
            $pdf = $parser->parseFile($pdfPath);
            $serialize = serialize($pdf);

            return !(str_contains($serialize, '"JS"') || str_contains($serialize, '"JavaScript"'));
        } catch (\Throwable $e) {
            return false;
        }
    }
}
