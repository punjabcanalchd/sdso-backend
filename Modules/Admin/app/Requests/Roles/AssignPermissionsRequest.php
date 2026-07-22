<?php

namespace Modules\Admin\Requests\Roles;

use App\Http\Requests\BaseRequest;

class AssignPermissionsRequest extends BaseRequest
{
    public function rules(): array
    {
        return [
            'permissions' => 'required|array',
            'permissions.*' => 'exists:permissions,name',
        ];
    }
}
