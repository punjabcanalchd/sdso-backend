<?php

namespace Modules\Admin\Requests\MasterManagement;

use App\Http\Requests\BaseRequest;
use App\Validation\Rules\CommonRules;
use App\Validation\Rules\SecurityRules;
use App\Helpers\RSAHelper;
use App\Validation\Patterns\RegexPatterns;
use Illuminate\Support\Facades\Crypt;
use App\Validation\Rules\FileRules;

class StoreDistrictRequest extends BaseRequest
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

    protected function prepareForValidation(): void
    {
        if ($this->has('status')) {
            $status = $this->input('status');
            if ($status === 'ACTIVE' || $status === '1' || $status === 1 || $status === true) {
                $this->merge(['status' => true]);
            } elseif ($status === 'INACTIVE' || $status === '0' || $status === 0 || $status === false) {
                $this->merge(['status' => false]);
            }
        }
    }

    public function rules(): array
    {
        $rules = [];

        $rules['name'] = ['required','array'];

        $rules['name.*'] = [
            'required',
            'string',
            function ($attribute, $value, $fail) {
                preg_match('/name\.(\d+)/', $attribute, $matches);
                $languageId = $matches[1] ?? null;

                $exists = \App\Models\DistrictsDescription::where('name', 'ILIKE', trim($value))
                    ->when($languageId, fn($q) => $q->where('language_id', $languageId))
                    ->exists();

                if ($exists) {
                    $langLabel = ($languageId == 2) ? 'Punjabi' : 'English';
                    $fail("The District name '{$value}' ({$langLabel}) already exists.");
                }
            }
        ];

        $rules['description'] = ['required','array'];

        $rules['description.*'] = ['required','string'];

         $rules['lgddistcode'] = ['required','numeric','unique:districts,lgddistcode'];

        $rules['lgdstatecode'] = ['required','numeric'];

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

            'lgddistcode.required' => 'LGD district code is required.',

            'lgddistcode.numeric' => 'LGD district code must be a number.',

            'lgddistcode.unique' => 'This LGD district code already exists.',
        ];
    }
}
