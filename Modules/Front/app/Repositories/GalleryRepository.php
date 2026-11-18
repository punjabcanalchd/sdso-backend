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
            return MediaCategoryTemplate::with('descriptions')->where('mediacat_id', $id)->first();
        }

        return MediaCategoryTemplate::findByPublicId($id);
    }
}

