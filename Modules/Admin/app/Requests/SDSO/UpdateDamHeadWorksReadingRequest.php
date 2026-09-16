<?php

namespace Modules\Admin\Requests\SDSO;

use App\Http\Requests\BaseRequest;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Crypt;


class UpdateDamHeadWorksReadingRequest extends BaseRequest
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

            'damhwcode' => ['required','string'],

            'inflow' => ['required','numeric', 'decimal:0,2'],

            'outflow' => ['required','numeric', 'decimal:0,2'],

            'waterlevel' => ['required','numeric', 'decimal:0,2'],

            'readingdate' => ['required','date_format:Y-m-d'],

            'start_time' => ['required', 'date_format:H:i'],

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
            'damhwcode.required' =>'Dam Headwork is required.',
            'damhwcode.string' => 'Dam Headwork must be a valid string.',

            'inflow.required' =>'Inflow is required.',
            'inflow.numeric' => 'Inflow must be a valid number.',
            'inflow.decimal' => 'Inflow must have at most 2 decimal places.',

            'outflow.required' =>'Outflow is required.',
            'outflow.numeric' => 'Outflow must be a valid number.',
            'outflow.decimal' => 'Outflow must have at most 2 decimal places.',

            'waterlevel.required' =>'Water Level is required.',
            'waterlevel.numeric' => 'Water Level must be a valid number.',
            'waterlevel.decimal' => 'Water Level must have at most 2 decimal places.',

            'readingdate.required' =>'Reading Date is required.',
            'readingdate.date_format' => 'Reading Date must be in the format YYYY-MM-DD.',
            'start_time.required' =>'Start Time is required.',
            'start_time.date_format' => 'Start Time must be in the format H:i.',
        ];
    }
}