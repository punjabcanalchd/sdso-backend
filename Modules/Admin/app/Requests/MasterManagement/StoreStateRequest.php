<?php

namespace Modules\Admin\Requests\MasterManagement;

use App\Http\Requests\BaseRequest;

class StoreStateRequest extends BaseRequest
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
        return [
            'languages' => ['required', 'array', 'size:2'],

            'languages.*.language_id' => [
                'required',
                'integer',
                'in:1,2',
            ],

            'languages.*.name' => [
                'required',
                'string',
            ],

            'languages.*.description' => [
                'required',
                'string',
            ],

            'lgdstatecode' => [
                'required',
                'numeric',
                'unique:states,lgdstatecode',
            ],

            'status' => [
                'required',
                'boolean',
            ],
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
            'languages.required' => 'Languages are required.',
            'languages.array' => 'Languages must be provided in the correct format.',
            'languages.size' => 'Exactly two languages are required.',

            'languages.*.language_id.required' => 'Language ID is required.',
            'languages.*.language_id.integer' => 'Language ID must be an integer.',
            'languages.*.language_id.in' => 'Invalid language ID.',

            'languages.*.name.required' => 'Title is required.',
            'languages.*.name.string' => 'Title must be a valid string.',

            'languages.*.description.required' => 'Description is required.',
            'languages.*.description.string' => 'Description must be a valid string.',

            'lgdstatecode.required' => 'LGD state code is required.',
            'lgdstatecode.numeric' => 'LGD state code must be a number.',
            'lgdstatecode.unique' => 'This LGD state code already exists.',

            'status.required' => 'Status is required.',
            'status.boolean' => 'Status must be a boolean value.',
        ];
    }
}
