<?php

namespace Modules\Admin\Requests\MasterManagement;

use App\Http\Requests\BaseRequest;
use App\Validation\Rules\CommonRules;
use App\Validation\Rules\SecurityRules;
use App\Helpers\RSAHelper;
use App\Validation\Patterns\RegexPatterns;
use Illuminate\Support\Facades\Crypt;
use App\Validation\Rules\FileRules;

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
        $rules = [];

        $rules['name'] = ['required','array'];

        $rules['name.*'] = ['required','string'];

        $rules['description'] = ['required','array'];

        $rules['description.*'] = ['required','string'];

        $rules['lgdstatecode'] = ['required','numeric','unique:states,lgdstatecode'];

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

            'lgdstatecode.required' => 'LGD state code is required.',

            'lgdstatecode.unique' => 'This LGD state code already exists.',

            'name.required' => 'Name is required.',

            'name.array' => 'Title must be provided in the correct format.',

            'name.*.required' => 'Title is required.',

            'name.*.string' => 'Title must be a valid string.',

            'description.required' => 'Description is required.',

            'description.array' => 'Description must be provided in the correct format.',

            'description.*.required' => 'Description is required.',

            'description.*.string' => 'Description must be a valid string.',

            'lgdstatecode.numeric' => 'LGD state code must be a number.',

            'status.boolean' => 'Status must be a boolean value.',
        ];
    }
}
