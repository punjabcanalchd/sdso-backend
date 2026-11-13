<?php

namespace Modules\Front\Repositories;

use App\Models\Menu;
use Illuminate\Database\Eloquent\Collection;

class MenuRepository
{
    /**
     * Retrieve all active menus with both English and Punjabi descriptions.
     */
    public function getActiveMenus($language)
    {
       $data =  Menu::with(['englishDescription', 'punjabiDescription'])
            ->where(function ($query) {
                $query->where('status', true)
                      ->orWhere('status', 1);
            })
            ->orderBy('sort_order')
            ->get();

     
        
        return $data ;
    }
}

