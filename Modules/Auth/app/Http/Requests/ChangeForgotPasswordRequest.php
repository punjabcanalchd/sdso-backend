<?php
namespace Modules\Auth\Http\Requests;

use App\Http\Requests\BaseRequest;
use App\Validation\Rules\CommonRules;
use App\Helpers\RSAHelper;

class ChangeForgotPasswordRequest extends BaseRequest
{
    protected function prepareForValidation(): void
    {
        $mergeData = [];

        if ($this->filled('new_password')) {
            $mergeData['new_password'] = RSAHelper::decrypt($this->new_password);
        }
        if ($this->filled('confirm_password')) {
            $mergeData['confirm_password'] = RSAHelper::decrypt($this->confirm_password);
        }

        if (!empty($mergeData)) {
            $this->merge($mergeData);
        }
    }

    public function rules(): array
    {
        return [
            'hash' => ['required', 'string'],
            'new_password' => CommonRules::password(),
            'confirm_password' => [
                CommonRules::password(),
                'same:new_password',
            ],
        ];
    }
}
?>
        