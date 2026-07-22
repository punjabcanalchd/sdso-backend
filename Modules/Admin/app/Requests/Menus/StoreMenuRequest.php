<?php

namespace Modules\Admin\Requests\Menus;

use App\Http\Requests\BaseRequest;
use App\Validation\Rules\CommonRules;
use Illuminate\Validation\Rule;
class StoreMenuRequest extends BaseRequest
{
    public function rules(): array
    {
        return [
            'name_en' => [
                ...CommonRules::name(),
                Rule::unique('menu_descriptions', 'message')->where(function ($query) {
                    return $query->where('language_id', 1);
                }),
            ],
            'name_pa' => [
                ...CommonRules::nameOrPunjabiName(),
                Rule::unique('menu_descriptions', 'message')->where(function ($query) {
                    return $query->where('language_id', 2);
                }),
            ],
            'parent_id'     => 'nullable|integer|exists:menus,menu_id',
            'sort_order'    => 'required|integer|min:0',
            'status'        => 'required|boolean',
            'link_type'     => 'nullable|integer',
            'external_link' => 'nullable|string|max:1000',
            'page_id'       => 'nullable|integer|exists:pages,id',
        ];
    }
}
