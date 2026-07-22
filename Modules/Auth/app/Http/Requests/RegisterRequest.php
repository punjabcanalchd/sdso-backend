<?php

namespace Modules\Auth\Http\Requests;

use App\Rules\Filename;
use App\Http\Requests\BaseRequest;
use App\Validation\Rules\CommonRules;
use App\Validation\Rules\FileRules;
use App\Validation\Patterns\RegexPatterns;
use Illuminate\Validation\Rule;
use App\Helpers\RSAHelper;

class RegisterRequest extends BaseRequest
{
    protected function prepareForValidation(): void
    {
        $mergeData = [];

        if ($this->has('id_proof') && is_string($this->id_proof)) {
            $mapped = strtoupper(str_replace(' ', '_', $this->id_proof));
            $map = [
                'PAN_CARD' => 1,
                'DRIVING_LICENSE' => 2,
            ];
            if (isset($map[$mapped])) {
                $mergeData['id_proof'] = $map[$mapped];
            }
        }

        if ($this->has('id_proof_number')) {
            $mergeData['id_proof_number'] = RSAHelper::decrypt($this->id_proof_number);
        }
        if ($this->has('password')) {
            $mergeData['password'] = RSAHelper::decrypt($this->password);
        }
        if ($this->has('confirm_password')) {
            $mergeData['confirm_password'] = RSAHelper::decrypt($this->confirm_password);
        }

        if (!empty($mergeData)) {
            $this->merge($mergeData);
        }
    }

    public function rules(): array
    {
        $size = 500;
        if(defined('adjust_file_size')){
            $size = adjust_file_size;
        }
        $rules = [];
        $rules['applicant_type']        = 'required';
        $rules['applicant_first_name']  = CommonRules::name();
        $rules['applicant_middle_name'] = [
            'nullable',
            'string',
            'min:2',
            'max:100',
            'regex:' . RegexPatterns::ALPHA,
        ];
        $rules['applicant_last_name']   = CommonRules::name();
        $rules['father_name']           = 'nullable|string';
        $rules['applicant_relation']    = 'nullable|integer';
        $rules['designation'] = [
            'required',
            'string',
            'min:2',
            'regex:' . RegexPatterns::ALPHA_NUM
        ];
        $rules['password'] = 'required|string';
        $rules['confirm_password'] = [
            'required',
            'same:password'
        ];
        $rules['mobile_number'] = [
            ...CommonRules::phone(),
            Rule::unique('users', 'mobile_number')
        ];
        $rules['email'] = [
            ...CommonRules::email(),
            Rule::unique('users', 'email')
        ];
        $rules['upload_copy_of_id_proof'] = [
            'required',
            ...array_diff(FileRules::pdf((int) ceil($size / 1024)), ['nullable']),
            new Filename(),
        ];

        $rules['id_proof'] = 'required';
        $rules['id_proof_number'] = 'required|string';

        // ID proof variations
        if ($this->id_proof == 1) {//PAN Card
            $rules['id_proof_number'] = [
                'required',
                'regex:' . RegexPatterns::PAN_CARD,
                'size:10',
                Rule::unique('users', 'id_proof_number')
            ];
        } elseif ($this->id_proof == 2) {//Driving License
            $rules['id_proof_number'] = [
                'required',
                'regex:' . RegexPatterns::DRIVING_LICENSE,
                'size:15',
                Rule::unique('users', 'id_proof_number')
            ];
        }
        return $rules;
    }
}
