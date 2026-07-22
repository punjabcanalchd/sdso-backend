<?php

namespace Modules\Admin\Requests\Roles;

use App\Http\Requests\BaseRequest;

class AssignRoleRequest extends BaseRequest
{
    public function rules(): array
    {
        return [
            'role' => 'required|string|exists:roles,name',
        ];
    }
}
