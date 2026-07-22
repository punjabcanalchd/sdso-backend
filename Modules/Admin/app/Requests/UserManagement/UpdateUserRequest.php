<?php

namespace Modules\Admin\Requests\UserManagement;

use App\Http\Requests\BaseRequest;
use App\Validation\Rules\CommonRules;
use Illuminate\Validation\Rule;
use App\Helpers\RSAHelper;
use App\Validation\Patterns\RegexPatterns;
use Illuminate\Support\Facades\Crypt;

class UpdateUserRequest extends BaseRequest
{
    protected function prepareForValidation(): void
    {
        $mergeData = [];

        if ($this->has('password') && !empty($this->password)) {
            $mergeData['password'] = RSAHelper::decrypt($this->password);
        }
        if ($this->has('password_confirmation') && !empty($this->password_confirmation)) {
            $mergeData['password_confirmation'] = RSAHelper::decrypt($this->password_confirmation);
        }
        if ($this->has('mobile_password') && !empty($this->mobile_password)) {
            $mergeData['mobile_password'] = RSAHelper::decrypt($this->mobile_password);
        }

        // Decrypt role public_id → real integer ID
        if ($this->has('role_id') && !empty($this->role_id)) {
            try {
                $mergeData['role_id'] = (int) Crypt::decryptString(urldecode($this->role_id));
            } catch (\Exception $e) {
                $mergeData['role_id'] = null;
            }
        }

        // Decrypt each additional_role_id public_id → real integer ID
        if ($this->has('additional_role_ids') && is_array($this->additional_role_ids)) {
            $mergeData['additional_role_ids'] = array_map(function ($publicId) {
                try {
                    return (int) Crypt::decryptString(urldecode($publicId));
                } catch (\Exception $e) {
                    return null;
                }
            }, $this->additional_role_ids);
        }

        if (!empty($mergeData)) {
            $this->merge($mergeData);
        }
    }

    public function rules(): array
    {
        // Resolve the real DB user ID from the public_id route parameter for unique-ignore
        $realUserId = 0;
        $publicId = $this->route('public_id');
        if ($publicId) {
            try {
                $realUserId = (int) Crypt::decryptString(urldecode($publicId));
            } catch (\Exception $e) {
                $realUserId = 0;
            }
        }

        return [

            'first_name' => ['sometimes', ...CommonRules::name()],

            'middle_name' => [
                'sometimes',
                'nullable',
                'string',
                'min:2',
                'max:100',
                'regex:' . RegexPatterns::ALPHA,
            ],

            'last_name' => ['sometimes', ...CommonRules::name()],

            'email' => [
                'sometimes',
                ...CommonRules::email(),
                Rule::unique('users', 'email')->ignore($realUserId),
            ],

            'mobile_number' => [
                'sometimes',
                ...CommonRules::phone(),
                Rule::unique('users', 'mobile_number')->ignore($realUserId),
            ],

            'username' => [
                'sometimes',
                ...CommonRules::username(),
                Rule::unique('users', 'username')->ignore($realUserId),
            ],

            // role_id is decrypted from public_id in prepareForValidation
            'role_id' => ['sometimes', 'nullable', 'integer', 'exists:user_roles,role_id'],

            // additional_role_ids are decrypted from public_ids in prepareForValidation
            'additional_role_ids'   => ['sometimes', 'array', 'distinct'],
            'additional_role_ids.*' => ['integer', 'exists:roles,id'],

            'district_code' => ['sometimes', 'nullable', 'integer', 'exists:districts,district_code,state_code,3'],

            'office_district' => ['sometimes', 'nullable', 'integer', 'exists:districts,id,state_code,3'],

            'password' => ['sometimes', ...CommonRules::password(), 'confirmed'],

            'mobile_password' => ['sometimes', 'nullable', 'string', 'min:4', 'max:255'],

            'applicant_type' => ['sometimes', 'nullable', 'integer'],

            'current_user_role' => ['sometimes', 'nullable', 'integer'],

            'is_ip_caf_user' => ['sometimes', 'nullable', 'boolean'],

            'designation' => ['sometimes', 'nullable', 'string', 'max:255'],

            'status' => ['sometimes', 'boolean'],

        ];
    }
}