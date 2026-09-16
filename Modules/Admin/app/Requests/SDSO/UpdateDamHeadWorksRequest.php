<?php

namespace Modules\Admin\Requests\SDSO;

use App\Http\Requests\BaseRequest;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Crypt;


class UpdateDamHeadWorksRequest extends BaseRequest
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

            'name' => ['required', 'array'],

            'name.*' => ['required', 'string'],

            'description' => ['required', 'array'],

            'description.*' => ['required', 'string'],

            'lgdstatecode' => ['required','string'],

            'lgddistcode' => ['required','string'],

            'officecode' => ['required','string'],

            'entitycode' => ['required','string'],

            'startlat' => ['required','numeric'],

            'startlong' => ['required','numeric'],

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