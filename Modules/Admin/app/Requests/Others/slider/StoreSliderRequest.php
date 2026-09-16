<?php

namespace Modules\Admin\Requests\Other\slider;

use Illuminate\Foundation\Http\FormRequest;

class StoreSliderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:255',
            ],

            'status' => [
                'nullable',
                'boolean',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Slider name is required.',
            'name.string' => 'Slider name must be a valid string.',
            'name.max' => 'Slider name may not be greater than 255 characters.',
            'status.boolean' => 'Status must be true or false.',
        ];
    }
}
