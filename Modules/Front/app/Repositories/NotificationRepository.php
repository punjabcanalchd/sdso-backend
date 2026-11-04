<?php

namespace Modules\Front\Repositories;

use App\Models\CategoryTemplate;
use App\Models\NoticeboardTemplate;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class NotificationRepository
{
    /**
     * Get active notices filtered optionally by category with limit.
     */
    public function getActiveNotices(?int $limit = 10, ?int $categoryId = null): Collection
    {
        $query = NoticeboardTemplate::with(['descriptions', 'category'])
            ->where('status', true);

        if ($categoryId) {
            $query->where('category_name', $categoryId);
        }

        return $query->orderBy('publish_date', 'desc')
            ->orderBy('template_id', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get active category templates configured to display on the home page.
     */
    public function getHomeCategories(): Collection
    {
        return CategoryTemplate::with('descriptions')
            ->where('status', true)
            ->where('display_on_home_page', true)
            ->orderBy('template_id', 'asc')
            ->get();
    }

    /**
     * Get active notices for a specific category with a custom limit.
     */
    public function getNoticesByCategory(int $categoryId, int $limit = 5): Collection
    {
        return NoticeboardTemplate::with('descriptions')
            ->where('category_name', $categoryId)
            ->where('status', true)
            ->orderBy('publish_date', 'desc')
            ->orderBy('template_id', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Find a category template by name pattern (case-insensitive search).
     */
    public function findCategoryByNamePattern(string $pattern): ?CategoryTemplate
    {
        return CategoryTemplate::where('status', true)
            ->where(function ($q) use ($pattern) {
                $q->where('name', 'ilike', $pattern)
                  ->orWhere('name', 'like', $pattern);
            })
            ->orderBy('template_id', 'asc')
            ->first();
    }

    /**
     * Find an active category template by its ID with descriptions.
     */
    public function findActiveCategoryById(int $categoryId): ?CategoryTemplate
    {
        return CategoryTemplate::with('descriptions')
            ->where('status', true)
            ->where('template_id', $categoryId)
            ->first();
    }

    /**
     * Get paginated notices for a specific category.
     */
    public function getPaginatedNoticesByCategory(int $categoryId, int $perPage = 30): LengthAwarePaginator
    {
        return NoticeboardTemplate::with(['descriptions', 'category'])
            ->where('status', true)
            ->where('category_name', $categoryId)
            ->orderBy('publish_date', 'DESC')
            ->orderBy('template_id', 'DESC')
            ->paginate($perPage);
    }

    /**
     * Find a notice by template ID and specific language ID.
     */
    // public function findByIdAndLanguage($id, $languageId): ?NoticeboardTemplate
    // {
        
    //     return NoticeboardTemplate::where('template_id', $id)
    //         ->whereHas('descriptions', function ($query) use ($languageId) {
    //             $query->where('language_id', $languageId);
    //         })
    //         ->with(['descriptions' => function ($query) use ($languageId) {
    //             $query->where('language_id', $languageId);
    //         }, 'category'])
    //         ->first();
    // }

    public function findByIdAndLanguage($id, $languageId)
    {
        // First, try finding the notice with the requested language
        $notice = NoticeboardTemplate::where('template_id', $id)
            ->whereHas('descriptions', function ($query) use ($languageId) {
                $query->where('language_id', $languageId);
            })
            ->with(['descriptions' => function ($query) use ($languageId) {
                $query->where('language_id', $languageId);
            }, 'category'])
            ->first();

        // Fallback: If no description exists for Punjabi (2), fetch with English (1) or any available description
        if (!$notice) {
            $notice = NoticeboardTemplate::where('template_id', $id)
                ->with(['descriptions', 'category'])
                ->first();
        }

        return $notice;
    }
}