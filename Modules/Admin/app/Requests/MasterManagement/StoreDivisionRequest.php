<?php

namespace Modules\Admin\Requests\MasterManagement;

use App\Http\Requests\BaseRequest;
use App\Validation\Rules\CommonRules;
use App\Validation\Rules\SecurityRules;
use App\Helpers\RSAHelper;
use App\Validation\Patterns\RegexPatterns;
use Illuminate\Support\Facades\Crypt;
use App\Validation\Rules\FileRules;

class StoreDivisionRequest extends BaseRequest
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

        $rules['name'] = ['required','array'];

        $rules['name.*'] = ['required','string'];

        $rules['description'] = ['required','array'];

        $rules['description.*'] = ['required','string'];

        $rules['circle_id'] = ['required','string'];

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

            'circle_id.required' => 'Circle is required.',

            'name.required' => 'Name is required.',

            'name.array' => 'Title must be provided in the correct format.',

            'name.*.required' => 'Title is required.',

            'name.*.string' => 'Title must be a valid string.',

            'description.required' => 'Description is required.',

            'description.array' => 'Description must be provided in the correct format.',

            'description.*.required' => 'Description is required.',

            'description.*.string' => 'Description must be a valid string.',

            'circle_id.string' => 'Circle code must be a string.',

            'status.boolean' => 'Status must be a boolean value.',
        ];
    }
}
