<?php

namespace Modules\Admin\Requests\MasterManagement;

use App\Http\Requests\BaseRequest;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Crypt;


class UpdateDesignationRequest extends BaseRequest
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
                $desigcode = (int) Crypt::decryptString(urldecode($publicId));
            } catch (\Exception $e) {
                $desigcode = 0;
            }
        }
        return [

            'name' => ['required', 'array'],

            'name.*' => ['required', 'string'],

            'description' => ['required', 'array'],

            'description.*' => ['required', 'string'],

            'desigsenioritylevel' => [
                'required',
                'numeric',
                Rule::unique('designations', 'desigsenioritylevel')
                    ->ignore($desigcode, 'desigcode'),
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

            'desigsenioritylevel.required' => 'Designation Level is required.',

            'desigsenioritylevel.unique' => 'This Designation Level already exists.',

            'desigsenioritylevel.numeric' => 'Designation Level must be a number.',
        ];
    }
}