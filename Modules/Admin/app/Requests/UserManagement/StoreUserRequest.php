<?php

namespace Modules\Admin\Requests\UserManagement;

use App\Http\Requests\BaseRequest;
use App\Validation\Rules\CommonRules;
use App\Validation\Rules\SecurityRules;
use App\Helpers\RSAHelper;
use App\Validation\Patterns\RegexPatterns;
use Illuminate\Support\Facades\Crypt;

class StoreUserRequest extends BaseRequest
{

    public function authorize(): bool
    {
        return true;
    }

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

        // Decrypt optional additional role public_ids → real integer IDs
        if ($this->has('additional_role_ids') && is_array($this->additional_role_ids)) {
            $decryptedRoles = [];
            foreach ($this->additional_role_ids as $encId) {
                try {
                    $decryptedRoles[] = (int) Crypt::decryptString(urldecode($encId));
                } catch (\Exception $e) {
                    // Skip invalid ids
                }
            }
            $mergeData['additional_role_ids'] = $decryptedRoles;
        }

        if (!empty($mergeData)) {
            $this->merge($mergeData);
        }
    }

    /* ------------------------------------------------------------------
     * VALIDATION RULES
     * ---------------------------------------------------------------- */

    public function rules(): array
    {
        return [

            'first_name' => CommonRules::name(),

            'middle_name' => [
                'nullable',
                'string',
                'min:2',
                'max:100',
                'regex:' . RegexPatterns::ALPHA,
            ],

            'last_name' => CommonRules::name(),

            'email' => [

                ...CommonRules::email(),

                'unique:users,email'

            ],

            'password' => CommonRules::password(),

            'password_confirmation' => [
                'required',
                'same:password'
            ],

            // role_id is decrypted from public_id in prepareForValidation
            'role_id' => [
                'required',
                'integer',
                'exists:user_roles,role_id',
            ],

            // Optional additional roles — decrypted from public_id in prepareForValidation
            'additional_role_ids' => [
                'nullable',
                'array',
            ],
            'additional_role_ids.*' => [
                'integer',
                'exists:roles,id',
            ],

            'mobile_number' => [

                ...CommonRules::phone(),

                'unique:users,mobile_number'

            ],

            'current_user_role' => [

                'nullable',

                'integer'

            ],

            'password_updated_at' => [

                'nullable',

                'date'

            ],

            'applicant_type' => [

                'nullable',

                'integer'

            ],

            'office_district' => [
                'nullable',
                'integer',
                // Must reference a district whose state_code = 3
                'exists:districts,id,state_code,3',
            ],
            'district_code' => [
                'required',
                'integer',
                // Must reference a district whose state_code = 3
                'exists:districts,district_code,state_code,3',
            ],

            'is_ip_caf_user' => [

                'nullable',

                'boolean'

            ],

            'mobile_password' => [

                'nullable',

                'string',

                'min:4',

                'max:255'

            ],
            'status' => ['sometimes', 'boolean'],

        ];
    }

    /* ------------------------------------------------------------------
     * CUSTOM VALIDATION MESSAGES
     * ---------------------------------------------------------------- */

    public function messages(): array
    {
        return [

            'email.unique' => 'Email already exists.',

            'mobile_number.unique' => 'Mobile number already exists.',

            'role_id.exists' => 'Selected role is invalid.',

        ];
    }
}