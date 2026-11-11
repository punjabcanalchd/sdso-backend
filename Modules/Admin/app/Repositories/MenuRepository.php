<?php

namespace Modules\Admin\Repositories;

use App\Models\Menu;
use App\Models\MenuDescription;
use Illuminate\Database\Eloquent\Collection;

class MenuRepository
{
    /**
     * Get all menus with bilingual descriptions ordered by sort_order.
     */
    public function getAll(): Collection
    {
        return Menu::with(['englishDescription', 'punjabiDescription'])
            ->orderBy('sort_order')
            ->get();
    }

    /**
     * Get menus for parent dropdown with English description.
     */
    public function getDropdown(): Collection
    {
        return Menu::with('englishDescription')
            ->orderBy('sort_order')
            ->get();
    }

    /**
     * Find a menu by its encrypted public_id.
     */
    public function findByPublicId(string $publicId): ?Menu
    {
        return Menu::findByPublicId($publicId);
    }

    /**
     * Create a menu base record.
     */
    public function create(array $data): Menu
    {
        return Menu::create($data);
    }

    /**
     * Create a menu description translation.
     */
    public function createDescription(int $menuId, int $languageId, string $message): MenuDescription
    {
        return MenuDescription::create([
            'menu_id'     => $menuId,
            'language_id' => $languageId,
            'message'     => $message,
        ]);
    }

    /**
     * Update menu attributes.
     */
    public function update(Menu $menu, array $data): bool
    {
        return $menu->update($data);
    }

    /**
     * Upsert a menu description for a specific language.
     */
    public function upsertDescription(int $menuId, int $languageId, string $message): MenuDescription
    {
        return MenuDescription::updateOrCreate(
            ['menu_id' => $menuId, 'language_id' => $languageId],
            ['message' => $message]
        );
    }

    /**
     * Update a menu's status.
     */
    public function updateStatus(Menu $menu, bool $status): bool
    {
        return $menu->update(['status' => $status]);
    }

    /**
     * Delete a menu and its associated descriptions.
     */
    public function delete(Menu $menu): bool
    {
        $menu->descriptions()->delete();
        return $menu->delete();
    }
}

