<?php

namespace Modules\Front\Repositories;

use App\Models\MediaCategoryTemplate;
use App\Models\MediaGalleryTemplate;
use Illuminate\Database\Eloquent\Collection;

class GalleryRepository
{
    /**
     * Get active media categories that should be displayed on the home page.
     */
    public function getHomeCategories(): Collection
    {
        $categories = MediaCategoryTemplate::with('descriptions')
            ->where('status', true)
            ->where('display_on_home_page', true)
            ->orderBy('mediacat_id', 'asc')
            ->get();

        // If no categories are explicitly marked for home page, fallback to active categories
        if ($categories->isEmpty()) {
            $categories = MediaCategoryTemplate::with('descriptions')
                ->where('status', true)
                ->orderBy('mediacat_id', 'asc')
                ->get();
        }

        return $categories;
    }

    /**
     * Get all active media categories.
     */
    public function getAllActiveCategories(): Collection
    {
        return MediaCategoryTemplate::with('descriptions')
            ->where('status', true)
            ->orderBy('mediacat_id', 'asc')
            ->get();
    }

    /**
     * Get active gallery items for given category identifiers or all active items.
     */
    public function getActiveItems(?array $categoryIdentifiers = null, ?int $limit = null): Collection
    {
        $query = MediaGalleryTemplate::with('descriptions')
            ->where('status', true)
            ->whereNotNull('select_img')
            ->where('select_img', '!=', '');

        if (!empty($categoryIdentifiers)) {
            $query->whereIn('category_name', $categoryIdentifiers);
        }

        $query->orderBy('mediagal_id', 'desc');

        if ($limit && $limit > 0) {
            $query->limit($limit);
        }

        return $query->get();
    }

    /**
     * Find a category by public_id or numeric mediacat_id.
     */
    public function findCategory(string|int $id): ?MediaCategoryTemplate
    {
        if (is_numeric($id)) {
            return MediaCategoryTemplate::with('descriptions')->where('mediacat_id', (int) $id)->first();
        }

        return MediaCategoryTemplate::findByPublicId($id);
    }

    /**
     * Get all active gallery items with descriptions.
     */
    public function getAllActiveGalleryItems(): Collection
    {
        return MediaGalleryTemplate::with('descriptions')
            ->where('status', true)
            ->whereNotNull('select_img')
            ->where('select_img', '!=', '')
            ->orderBy('mediagal_id', 'asc')
            ->get();
    }

    /**
     * Get active gallery items for a specific category.
     */
    public function getItemsByCategory(string|int $categoryId): Collection
    {
        $category = $this->findCategory($categoryId);
        $identifiers = [(string) $categoryId];

        if ($category) {
            $identifiers[] = (string) $category->mediacat_id;
            if (!empty($category->public_id)) {
                $identifiers[] = $category->public_id;
            }
            if (!empty($category->name)) {
                $identifiers[] = $category->name;
            }
        }

        return MediaGalleryTemplate::with('descriptions')
            ->where('status', true)
            ->whereNotNull('select_img')
            ->where('select_img', '!=', '')
            ->whereIn('category_name', array_values(array_unique($identifiers)))
            ->orderBy('mediagal_id', 'asc')
            ->get();
    }
}

