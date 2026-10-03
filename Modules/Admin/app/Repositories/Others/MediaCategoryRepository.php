<?php

namespace Modules\Admin\Repositories\Others;

use App\Models\MediaCategoryTemplate;
use App\Models\MediaCategoryTemplateDescription;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class MediaCategoryRepository
{
    /**
     * Get paginated media categories with descriptions.
     */
    public function getAll(int $limit, ?string $search, ?string $sortColumn = 'mediacat_id', ?string $sortDirection = 'desc'): LengthAwarePaginator
    {
        $query = MediaCategoryTemplate::with('descriptions');

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'ilike', "%{$search}%")
                  ->orWhereHas('descriptions', function ($dq) use ($search) {
                      $dq->where('message', 'ilike', "%{$search}%");
                  });
            });
        }

        $allowedSorts = ['name', 'status', 'display_on_home_page', 'created_at', 'mediacat_id'];
        $sortColumn = in_array($sortColumn, $allowedSorts) ? $sortColumn : 'mediacat_id';
        $query->orderBy($sortColumn, strtolower($sortDirection) === 'asc' ? 'asc' : 'desc');

        return $query->paginate($limit);
    }

    /**
     * Fetch media counts for an array of category IDs from media_gallery_templates.
     */
    public function getMediaCounts(array $categoryIds): Collection
    {
        if (empty($categoryIds)) {
            return collect();
        }

        try {
            return DB::table('media_gallery_templates')
                ->whereIn('category_name', $categoryIds)
                ->select('category_name', DB::raw('count(*) as count'))
                ->groupBy('category_name')
                ->pluck('count', 'category_name');
        } catch (\Throwable $e) {
            return collect();
        }
    }

    /**
     * Find category by public_id or primary key ID with descriptions.
     */
    public function findById(string|int $id): ?MediaCategoryTemplate
    {
        if (is_numeric($id)) {
            return MediaCategoryTemplate::with('descriptions')->find((int) $id);
        }

        $model = MediaCategoryTemplate::findByPublicId((string) $id);
        if ($model) {
            return $model->load('descriptions');
        }

        return null;
    }

    /**
     * Get dropdown categories.
     */
    public function getDropdown(): array
    {
        return MediaCategoryTemplate::orderBy('name')
            ->get()
            ->map(function ($cat) {
                return [
                    'value' => (int) $cat->mediacat_id,
                    'label' => $cat->name,
                ];
            })
            ->toArray();
    }

    /**
     * Create category template record.
     */
    public function createCategory(array $data): MediaCategoryTemplate
    {
        return MediaCategoryTemplate::create($data);
    }

    /**
     * Create or update category description.
     */
    public function saveDescription(int $mediacatId, int $languageId, string $message): MediaCategoryTemplateDescription
    {
        return MediaCategoryTemplateDescription::updateOrCreate(
            [
                'mediacat_id' => $mediacatId,
                'language_id' => $languageId,
            ],
            [
                'message' => $message,
            ]
        );
    }

    /**
     * Update category template.
     */
    public function updateCategory(MediaCategoryTemplate $category, array $data): bool
    {
        return $category->update($data);
    }

    /**
     * Update status.
     */
    public function updateStatus(MediaCategoryTemplate $category, bool $status): bool
    {
        $category->status = $status;
        return $category->save();
    }

    /**
     * Update display on home page.
     */
    public function updateDisplayOnHome(MediaCategoryTemplate $category, bool $display): bool
    {
        $category->display_on_home_page = $display;
        return $category->save();
    }
}
