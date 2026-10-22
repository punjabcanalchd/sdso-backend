<?php

namespace Modules\Admin\Requests\Others;

use App\Http\Requests\BaseRequest;


class StoreSmsTemplateRequest extends BaseRequest
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

        $rules['templateid'] = ['required','string'];

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

            'subject.required' => 'Template name is required.',

            'templateid.required' => 'Template ID is required.',

            'subject.array' => 'Template name must be provided in the correct format.',

            'subject.*.string' => 'Template name must be a valid string.',

            'subject.*.required' => 'Template name is required.',

            'subject.*.string' => 'Template name must be a valid string.',

            'description.required' => 'Description is required.',

            'description.array' => 'Description must be provided in the correct format.',

            'description.*.required' => 'Description is required.',

            'description.*.string' => 'Description must be a valid string.',

            'status.boolean' => 'Status must be a boolean value.',

            'name.string' => 'Name must be a valid string.',

            'templateid.string' => 'Template ID must be a valid string.',
        ];
    }
}
