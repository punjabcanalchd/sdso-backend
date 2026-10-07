<?php

namespace Modules\Admin\Requests\Others;

use App\Http\Requests\BaseRequest;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Crypt;


class UpdateEmailTemplateRequest extends BaseRequest
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
        // Get district_id from route
        $publicId = $this->route('public_id');
        if ($publicId) {
            try {
                $templateID = (int) Crypt::decryptString(urldecode($publicId));
            } catch (\Exception $e) {
                $templateID = 0;
            }
        }
        return [

            'subject' => ['required', 'array'],

            'subject.*' => ['required', 'string'],

            'description' => ['required', 'array'],

            'description.*' => ['required', 'string'],

            'name' => ['required','string'],

            'status' => ['required', 'boolean'],
        ];
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

            'subject.*.required' => 'Subject is required.',

            'subject.*.string' => 'Subject must be a valid string.',

            'description.required' => 'Description is required.',

            'description.array' => 'Description must be provided in the correct format.',

            'description.*.required' => 'Description is required.',

            'description.*.string' => 'Description must be a valid string.',

            'status.required' => 'Status is required.',

            'status.boolean' => 'Status must be a boolean value.',

            'name.required' => 'Template name is required.',

            'name.string' => 'Template name must be a valid string.',
        ];
    }
}