<?php

namespace Modules\Admin\Requests\Others;

use App\Http\Requests\BaseRequest;
use App\Validation\Rules\CommonRules;
use App\Validation\Rules\SecurityRules;
use App\Helpers\RSAHelper;
use App\Validation\Patterns\RegexPatterns;
use Illuminate\Support\Facades\Crypt;
use App\Validation\Rules\FileRules;

class StoreEmailTemplateRequest extends BaseRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /*
    |--------------------------------------------------------------------------
    | VALIDATION RULES
    |--------------------------------------------------------------------------
    */

    public function rules(): array
    {
        $rules = [];

        $rules['subject'] = ['required','array'];

        $rules['subject.*'] = ['required','string'];

        $rules['description'] = ['required','array'];

        $rules['description.*'] = ['required','string'];

        $rules['name'] = ['required','string'];

        $rules['status'] = ['required','boolean'];


        return $rules;
    }

    /*
    |--------------------------------------------------------------------------
    | CUSTOM VALIDATION MESSAGES
    |--------------------------------------------------------------------------
    */

    public function messages(): array
    {
        return [

            'subject.required' => 'Subject is required.',

            'subject.array' => 'Subject must be provided in the correct format.',

            'subject.*.string' => 'Subject must be a valid string.',

            'name.*.required' => 'Template name is required.',

            'name.*.string' => 'Template name must be a valid string.',

            'description.required' => 'Description is required.',

            'description.array' => 'Description must be provided in the correct format.',

            'description.*.required' => 'Description is required.',

            'description.*.string' => 'Description must be a valid string.',


            'status.boolean' => 'Status must be a boolean value.',
        ];
    }
}
