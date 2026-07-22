<?php

namespace Modules\Admin\Requests\Roles;

use App\Http\Requests\BaseRequest;

class StoreRoleRequest extends BaseRequest
{
    public function rules(): array
    {
        return [
            'name' => 'required|string|unique:roles,name',
        ];
    }
}
