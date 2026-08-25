<?php

namespace Modules\Admin\Requests\MasterManagement;

use App\Http\Requests\BaseRequest;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Crypt;


class UpdateDivisionRequest extends BaseRequest
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
                $divisionId = (int) Crypt::decryptString(urldecode($publicId));
            } catch (\Exception $e) {
                $divisionId = 0;
            }
        }
        return [

            'name' => ['required', 'array'],

            'name.*' => ['required', 'string'],

            'description' => ['required', 'array'],

            'description.*' => ['required', 'string'],

            'circle_id' => ['required','string'],

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

            'circle_id.required' =>'Circle is required.',

            'circle_id.string' => 'Circle code must be a string.',

            'name.required' => 'Title is required.',

            'name.array' => 'Title must be provided in the correct format.',

            'name.*.required' => 'Title is required.',

            'name.*.string' => 'Title must be a valid string.',

            'description.required' => 'Description is required.',

            'description.array' => 'Description must be provided in the correct format.',

            'description.*.required' => 'Description is required.',

            'description.*.string' => 'Description must be a valid string.',

            'status.required' => 'Status is required.',

            'status.boolean' => 'Status must be a boolean value.',
        ];
    }
}