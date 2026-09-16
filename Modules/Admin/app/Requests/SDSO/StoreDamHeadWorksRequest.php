<?php

namespace Modules\Admin\Requests\SDSO;

use App\Http\Requests\BaseRequest;
use App\Validation\Rules\CommonRules;
use App\Validation\Rules\SecurityRules;
use App\Helpers\RSAHelper;
use App\Validation\Patterns\RegexPatterns;
use Illuminate\Support\Facades\Crypt;
use App\Validation\Rules\FileRules;

class StoreDamHeadWorksRequest extends BaseRequest
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

        $rules['lgdstatecode'] = ['required','string'];

        $rules['lgddistcode'] = ['required','string'];

        $rules['officecode'] = ['required','string'];

        $rules['entitycode'] = ['required','string'];

        $rules['startlat'] = ['required','numeric'];

        $rules['startlong'] = ['required','numeric'];

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

            'lgdstatecode.required' =>'LGD state is required.',

            'lgdstatecode.string' => 'LGD state is required.',

            'lgddistcode.required' =>'LGD district is required.',

            'lgddistcode.string' => 'LGD district is required.',

            'officecode.required' =>'Office is required.',

            'officecode.string' => 'Office is required.',

            'entitycode.required' =>'Entity Type is required.',

            'entitycode.string' => 'Entity Type is required.',

            'startlong.required' =>'Longitude is required.',

            'startlong.numeric' => 'Longitude must be numeric.',

            'startlat.required' =>'Latitude is required.',

            'startlat.numeric' => 'Latitude must be numeric.',

            'name.required' => 'Name is required.',

            'name.array' => 'Title must be provided in the correct format.',

            'name.*.required' => 'Title is required.',

            'name.*.string' => 'Title must be a valid string.',

            'description.required' => 'Description is required.',

            'description.array' => 'Description must be provided in the correct format.',

            'description.*.required' => 'Description is required.',

            'description.*.string' => 'Description must be a valid string.',

            'status.boolean' => 'Status must be a boolean value.',
        ];
    }
}
