<?php

namespace Modules\Admin\Requests\Roles;

use App\Http\Requests\BaseRequest;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Crypt;

class UpdateRoleRequest extends BaseRequest
{
    public function rules(): array
    {
        // Resolve the real DB role ID from the public_id route parameter for unique-ignore
        $realRoleId = 0;
        $publicId = $this->route('public_id');
        if ($publicId) {
            try {
                $realRoleId = (int) Crypt::decryptString(urldecode($publicId));
            } catch (\Exception $e) {
                $realRoleId = 0;
            }
        }

        return [
            'name' => ['required', 'string', Rule::unique('roles', 'name')->ignore($realRoleId)],
        ];
    }
}
