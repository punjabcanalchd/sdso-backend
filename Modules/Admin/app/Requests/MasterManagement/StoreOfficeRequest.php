<?php

namespace Modules\Admin\Requests\MasterManagement;

use App\Http\Requests\BaseRequest;

class StoreOfficeRequest extends BaseRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        parent::prepareForValidation();

        if ($this->has('status')) {
            $status = $this->input('status');
            if ($status === 'ACTIVE' || $status === 1 || $status === '1' || $status === true) {
                $this->merge(['status' => 1]);
            } elseif ($status === 'INACTIVE' || $status === 0 || $status === '0' || $status === false) {
                $this->merge(['status' => 0]);
            }
        }
    }

    /*
    |--------------------------------------------------------------------------
    | VALIDATION RULES
    |--------------------------------------------------------------------------
    */
    public function rules(): array
    {
        return [
            'name'             => ['required', 'array'],
            'name.*'           => ['required', 'string'],
            'description'      => ['nullable', 'array'],
            'description.*'    => ['nullable', 'string'],
            'email'            => ['required', 'email'],
            'phonelandline'    => ['nullable', 'string'],
            'mobilenumber'     => ['required', 'string'],
            'pincode'          => ['required', 'string'],
            'officelevelcode'  => ['required'],
            'circle_id'        => ['nullable'],
            'division_id'      => ['nullable'],
            'subdivision_id'   => ['nullable'],
            'lgdstatecode'     => ['required'],
            'lgddistcode'      => ['nullable'],
            'status'           => ['required'],
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
            'name.required'            => 'Office name is required.',
            'name.array'               => 'Office name must be provided in the correct format.',
            'name.*.required'          => 'Office name in all languages is required.',
            'email.required'           => 'Email address is required.',
            'email.email'              => 'Please enter a valid email address.',
            'mobilenumber.required'    => 'Mobile number is required.',
            'pincode.required'         => 'Pin code is required.',
            'officelevelcode.required' => 'Office level is required.',
            'lgdstatecode.required'    => 'State is required.',
            'status.required'          => 'Status is required.',
        ];
    }
}
