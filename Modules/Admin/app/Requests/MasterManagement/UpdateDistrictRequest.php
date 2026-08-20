<?php

namespace Modules\Admin\Requests\MasterManagement;

use App\Http\Requests\BaseRequest;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Crypt;


class UpdateDistrictRequest extends BaseRequest
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
                $districtId = (int) Crypt::decryptString(urldecode($publicId));
            } catch (\Exception $e) {
                $districtId = 0;
            }
        }
        return [

            'name' => ['required', 'array'],

            'name.*' => ['required', 'string'],

            'description' => ['required', 'array'],

            'description.*' => ['required', 'string'],

            'lgddistcode' => [
                'required',
                'numeric',
                Rule::unique('districts', 'lgddistcode')
                    ->ignore($districtId, 'district_id'),
            ],

            'lgdstatecode' => ['required','numeric'],

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

            'lgdstatecode.required' =>'LGD state code is required.',

            'lgdstatecode.numeric' => 'LGD state code must be a number.',

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

            'lgddistcode.required' => 'LGD district code is required.',

            'lgddistcode.numeric' => 'LGD district code must be a number.',

            'lgddistcode.unique' => 'This LGD district code already exists.',
        ];
    }
}