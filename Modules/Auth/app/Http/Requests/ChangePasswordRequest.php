<?php

namespace Modules\Auth\Http\Requests;

use App\Http\Requests\BaseRequest;
use App\Validation\Rules\CommonRules;
use App\Helpers\RSAHelper;

class ChangePasswordRequest extends BaseRequest
{
    protected function prepareForValidation(): void
    {
        $mergeData = [];

        if ($this->has('old_password')) {
            $mergeData['old_password'] = RSAHelper::decrypt($this->old_password);
        }
        if ($this->has('new_password')) {
            $mergeData['new_password'] = RSAHelper::decrypt($this->new_password);
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
        return [
            'old_password' => CommonRules::password(),
            'new_password' => CommonRules::password(),
            'confirm_password' => [
                CommonRules::password(),
                'same:new_password',
            ],
        ];
    }
}
