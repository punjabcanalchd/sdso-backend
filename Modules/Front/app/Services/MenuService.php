<?php

namespace Modules\Front\Services;

use Modules\Front\Repositories\MenuRepository;

class MenuService
{
    public function __construct(
        protected MenuRepository $menuRepository
    ) {}

    public function getActiveMenus($language)
    {
        
        // 1. Fetch ALL active menus with both English and Punjabi descriptions via repository
        $allActiveMenus = $this->menuRepository->getActiveMenus($language);       
        $tree = [];
        $mapped = [];

        // 2. First pass: store them in a map by their ID and initialize an empty children collection
        foreach ($allActiveMenus as $menu) {
            $mapped[$menu->menu_id] = $menu;
            $mapped[$menu->menu_id]->setRelation('children', collect());
        }

        // 3. Second pass: attach children to their parents
        foreach ($allActiveMenus as $menu) {
            if ($menu->parent_id && isset($mapped[$menu->parent_id])) {
                // If it has an active parent, add it to the parent's children collection
                $mapped[$menu->parent_id]->children->push($menu);
            } elseif (! $menu->parent_id) {
                // If it has no parent, it's a top-level menu
                $tree[] = $menu;
            }
        }

        // 4. Return the infinitely nested tree
        return collect($tree);
    }
}
