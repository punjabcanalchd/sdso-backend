<?php

namespace Modules\Admin\Services;

use App\Models\Menu;
use App\Models\MenuDescription;
use Illuminate\Support\Facades\DB;

class MenuService
{
    private const LANG_EN = 1;
    private const LANG_PA = 2;

    /**
     * Resolve a Menu by its encrypted public_id or abort 404.
     */
    private function resolveMenu(string $publicId): Menu
    {
        $menu = Menu::findByPublicId($publicId);

        if (!$menu) {
            abort(404, 'Menu not found.');
        }

        return $menu;
    }

    /**
     * Format a Menu model into a consistent response array.
     */
    private function formatMenu(Menu $menu): array
    {
        $menu->load(['englishDescription', 'punjabiDescription']);

        return [
            'public_id'     => $menu->public_id,
            'menu_id'       => $menu->menu_id,
            'parent_id'     => $menu->parent_id,
            'page_id'       => $menu->page_id,
            'external_link' => $menu->external_link,
            'sort_order'    => $menu->sort_order,
            'status'        => $menu->status,
            'link_type'     => $menu->link_type,
            'name_en'       => optional($menu->englishDescription)->message,
            'name_pa'       => optional($menu->punjabiDescription)->message,
            'created_at'    => $menu->created_at,
            'updated_at'    => $menu->updated_at,
        ];
    }

    /**
     * Get all menus with their English and Punjabi names.
     */
    public function getAllMenus(): array
    {
        $menus = Menu::with(['englishDescription', 'punjabiDescription'])
            ->orderBy('sort_order')
            ->get();

        return $menus->map(function (Menu $menu) {
            return [
                'public_id'     => $menu->public_id,
                'menu_id'       => $menu->menu_id,
                'parent_id'     => $menu->parent_id,
                'page_id'       => $menu->page_id,
                'external_link' => $menu->external_link,
                'sort_order'    => $menu->sort_order,
                'status'        => $menu->status,
                'link_type'     => $menu->link_type,
                'name_en'       => optional($menu->englishDescription)->message,
                'name_pa'       => optional($menu->punjabiDescription)->message,
                'created_at'    => $menu->created_at,
                'updated_at'    => $menu->updated_at,
            ];
        })->toArray();
    }

    /**
     * Get a single menu by its encrypted public_id.
     */
    public function getMenu(string $publicId): array
    {
        $menu = $this->resolveMenu($publicId);

        return $this->formatMenu($menu);
    }

    /**
     * Get a flat list of menus for the parent dropdown (English names).
     */
    public function getDropdown(): array
    {
        $menus = Menu::with('englishDescription')
            ->orderBy('sort_order')
            ->get();

        return $menus->map(function (Menu $menu) {
            return [
                'menu_id' => $menu->menu_id,
                'name'    => optional($menu->englishDescription)->message ?? '(No Name)',
            ];
        })->toArray();
    }

    /**
     * Create a new menu with English and Punjabi descriptions.
     */
    public function createMenu(array $data): array
    {
        return DB::transaction(function () use ($data) {
            $menu = Menu::create([
                'parent_id'     => $data['parent_id'] ?? null,
                'page_id'       => $data['page_id'] ?? null,
                'external_link' => $data['external_link'] ?? null,
                'sort_order'    => $data['sort_order'],
                'status'        => $data['status'],
                'link_type'     => $data['link_type'] ?? null,
            ]);

            MenuDescription::create([
                'menu_id'     => $menu->menu_id,
                'language_id' => self::LANG_EN,
                'message'     => $data['name_en'],
            ]);

            MenuDescription::create([
                'menu_id'     => $menu->menu_id,
                'language_id' => self::LANG_PA,
                'message'     => $data['name_pa'],
            ]);

            return $this->formatMenu($menu);
        });
    }

    /**
     * Update an existing menu and its descriptions.
     */
    public function updateMenu(string $publicId, array $data): array
    {
        return DB::transaction(function () use ($publicId, $data) {
            $menu = $this->resolveMenu($publicId);

            $menu->update([
                'parent_id'     => $data['parent_id'] ?? null,
                'page_id'       => $data['page_id'] ?? null,
                'external_link' => $data['external_link'] ?? null,
                'sort_order'    => $data['sort_order'],
                'status'        => $data['status'],
                'link_type'     => $data['link_type'] ?? null,
            ]);

            // Upsert English description
            MenuDescription::updateOrCreate(
                ['menu_id' => $menu->menu_id, 'language_id' => self::LANG_EN],
                ['message' => $data['name_en']]
            );

            // Upsert Punjabi description
            MenuDescription::updateOrCreate(
                ['menu_id' => $menu->menu_id, 'language_id' => self::LANG_PA],
                ['message' => $data['name_pa']]
            );

            return $this->formatMenu($menu->fresh());
        });
    }

    /**
     * Update a menu's status (toggle).
     */
    public function updateStatus(string $publicId, bool $status): array
    {
        $menu = $this->resolveMenu($publicId);
        $menu->update(['status' => $status]);

        return $this->formatMenu($menu->fresh());
    }

    /**
     * Delete a menu and its descriptions.
     */
    public function deleteMenu(string $publicId): void
    {
        DB::transaction(function () use ($publicId) {
            $menu = $this->resolveMenu($publicId);
            $menu->descriptions()->delete();
            $menu->delete();
        });
    }
}
