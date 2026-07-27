<?php

namespace Modules\Front\Services;

use App\Models\Menu;

class MenuService
{
    public function getActiveMenus()
    {
        return Menu::with([
            'englishDescription',
            'children.englishDescription',

        ])
            ->whereNull('parent_id')
            ->where('status', true)
            ->orderBy('sort_order')
            ->get();
    }
}
