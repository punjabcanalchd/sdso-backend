<?php

namespace Modules\Admin\Requests\MasterManagement;

use App\Http\Requests\BaseRequest;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Validation\Rule;

class UpdateStateRequest extends BaseRequest
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

    // public function rules(): array
    // {
    //     // Get state_id from route
    //     $publicId = $this->route('public_id');
    //     if ($publicId) {
    //         try {
    //             $stateId = (int) Crypt::decryptString(urldecode($publicId));
    //         } catch (\Exception $e) {
    //             $stateId = 0;
    //         }
    //     }

    //     // return [

    //     //     'name' => ['required', 'array'],

    //     //     'name.*' => ['required', 'string'],

    //     //     'description' => ['required', 'array'],

    //     //     'description.*' => ['required', 'string'],

    //     //     'lgdstatecode' => [
    //     //         'required',
    //     //         'numeric',
    //     //         Rule::unique('states', 'lgdstatecode')
    //     //             ->ignore($stateId, 'state_id'),
    //     //     ],

    //     //     'status' => ['required', 'boolean'],
    //     // ];

    //     return [

    //         'languages' => [
    //             'required',
    //             'array',
    //             'size:2',
    //         ],

    //         'languages.*.language_id' => [
    //             'required',
    //             'integer',
    //             'in:1,2',
    //         ],

    //         'languages.*.name' => [
    //             'required',
    //             'string',
    //         ],

    //         'languages.*.description' => [
    //             'required',
    //             'string',
    //         ],

    //         'lgdstatecode' => [
    //             'required',
    //             'numeric',

    //             Rule::unique('states', 'lgdstatecode')
    //                 ->ignore($stateId, 'state_id'),
    //         ],

    //         'status' => [
    //             'required',
    //             'boolean',
    //         ],
    //     ];
    // }

    public function rules(): array
    {

        $stateId = 0;

        $publicId = $this->route('public_id');

        if ($publicId) {
            try {
                $stateId = (int) Crypt::decryptString(
                    urldecode($publicId)
                );
            } catch (\Exception $e) {
                \Log::error('State ID decryption failed', [
                    'public_id' => $publicId,
                    'error' => $e->getMessage(),
                ]);
            }
        }
        \Log::info('UpdateStateRequest reached', [
            'public_id' => $publicId,
            'state_id' => $stateId,
            'payload' => $this->all(),
        ]);

        return [
            'languages' => [
                'required',
                'array',
                'size:2',
            ],

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
                Rule::unique('states', 'lgdstatecode')
                    ->ignore($stateId, 'state_id'),
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
    // public function messages(): array
    // {
    //     return [

    //         'lgdstatecode.required' =>
    //             'LGD state code is required.',

    //         'lgdstatecode.numeric' =>
    //             'LGD state code must be a number.',

    //         'lgdstatecode.unique' =>
    //             'This LGD state code already exists.',

    //         'name.required' =>
    //             'Title is required.',

    //         'name.array' =>
    //             'Title must be provided in the correct format.',

    //         'name.*.required' =>
    //             'Title is required.',

    //         'name.*.string' =>
    //             'Title must be a valid string.',

    //         'description.required' =>
    //             'Description is required.',

    //         'description.array' =>
    //             'Description must be provided in the correct format.',

    //         'description.*.required' =>
    //             'Description is required.',

    //         'description.*.string' =>
    //             'Description must be a valid string.',

    //         'status.required' =>
    //             'Status is required.',

    //         'status.boolean' =>
    //             'Status must be a boolean value.',
    //     ];
    // }
}
