<?php

namespace Modules\Admin\Requests\MasterManagement;

use App\Http\Requests\BaseRequest;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Crypt;


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

    public function rules(): array
    {
        // Get state_id from route
        $publicId = $this->route('public_id');
        if ($publicId) {
            try {
                $stateId = (int) Crypt::decryptString(urldecode($publicId));
            } catch (\Exception $e) {
                $stateId = 0;
            }
        }
        return [

            'name' => ['required', 'array'],

            'name.*' => ['required', 'string'],

            'description' => ['required', 'array'],

            'description.*' => ['required', 'string'],

            'lgdstatecode' => [
                'required',
                'numeric',
                Rule::unique('states', 'lgdstatecode')
                    ->ignore($stateId, 'state_id'),
            ],

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

            'lgdstatecode.required' =>
                'LGD state code is required.',

            'lgdstatecode.numeric' =>
                'LGD state code must be a number.',

            'lgdstatecode.unique' =>
                'This LGD state code already exists.',

            'name.required' =>
                'Title is required.',

            'name.array' =>
                'Title must be provided in the correct format.',

            'name.*.required' =>
                'Title is required.',

            'name.*.string' =>
                'Title must be a valid string.',

            'description.required' =>
                'Description is required.',

            'description.array' =>
                'Description must be provided in the correct format.',

            'description.*.required' =>
                'Description is required.',

            'description.*.string' =>
                'Description must be a valid string.',

            'status.required' =>
                'Status is required.',

            'status.boolean' =>
                'Status must be a boolean value.',
        ];
    }
}