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
        // Get district from route public_id
        $publicId = $this->route('public_id');
        $district = $publicId ? \App\Models\Districts::findByPublicId($publicId) : null;
        $districtId = $district?->district_id ?? 0;
        $currentLgdDistCode = $district?->lgddistcode;

        return [

            'name' => ['required', 'array'],

            'name.*' => [
                'required',
                'string',
                function ($attribute, $value, $fail) use ($currentLgdDistCode) {
                    preg_match('/name\.(\d+)/', $attribute, $matches);
                    $languageId = $matches[1] ?? null;

                    $exists = \App\Models\DistrictsDescription::where('name', 'ILIKE', trim($value))
                        ->when($languageId, fn($q) => $q->where('language_id', $languageId))
                        ->when($currentLgdDistCode, fn($q) => $q->where('lgddistcode', '!=', $currentLgdDistCode))
                        ->exists();

                    if ($exists) {
                        $langLabel = ($languageId == 2) ? 'Punjabi' : 'English';
                        $fail("The District name '{$value}' ({$langLabel}) already exists.");
                    }
                }
            ],

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