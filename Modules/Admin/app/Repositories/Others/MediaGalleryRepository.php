<?php

namespace Modules\Admin\Repositories\Others;

use App\Models\MediaCategoryTemplate;
use App\Models\MediaGalleryTemplate;
use App\Models\MediaGalleryTemplateDescription;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class MediaGalleryRepository
{
    /**
     * Resolve a public ID or string category identifier to its numeric mediacat_id string.
     */
    public function resolveCategoryId(?string $categoryId): ?string
    {
        if (empty($categoryId)) {
            return null;
        }

        if (!is_numeric($categoryId)) {
            $cat = MediaCategoryTemplate::findByPublicId($categoryId);

            if ($cat) {
                return (string) $cat->mediacat_id;
            }
        }

        return (string) $categoryId;
    }

    /**
     * Fetch gallery items by category ID with optional search.
     */
    public function getByCategory(?string $categoryId, ?string $search = null): Collection
    {
        $resolvedId = $this->resolveCategoryId($categoryId);

        $query = DB::table('media_gallery_templates');

        if (!empty($resolvedId)) {
            $query->where('category_name', $resolvedId);
        }

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('title_en', 'ilike', "%{$search}%")
                  ->orWhere('title_pb', 'ilike', "%{$search}%");
            });
        }

        return $query->orderBy('mediagal_id', 'desc')->get();
    }

    /**
     * Find a gallery template by its primary key.
     */
    public function findById($id)
    {
        return DB::table('media_gallery_templates')->where('mediagal_id', $id)->first();
    }

    /**
     * Insert a new record into media_gallery_templates.
     */
    public function create(array $data): int
    {
        return DB::table('media_gallery_templates')->insertGetId([
            'category_name'  => (string) $data['category_name'],
            'status'         => $data['status'] ?? true,
            'title_en'       => $data['title_en'] ?? '',
            'title_pb'       => $data['title_pb'] ?? '',
            'select_img'     => $data['select_img'] ?? null,
            'select_video'   => $data['select_video'] ?? null,
            'select_img_vid' => $data['select_img_vid'] ?? '0',
            'created_at'     => now(),
            'updated_at'     => now(),
        ], 'mediagal_id');
    }

    /**
     * Insert language descriptions for an image (language_id 1 = EN, 2 = PB).
     */
    public function createDescriptions(int $mediagalId, string $titleEn, string $titlePb): void
    {
        DB::table('media_gallery_template_descriptions')->insert([
            [
                'mediagal_id' => $mediagalId,
                'language_id' => 1,
                'message'     => $titleEn,
                'created_at'  => now(),
                'updated_at'  => now(),
            ],
            [
                'mediagal_id' => $mediagalId,
                'language_id' => 2,
                'message'     => $titlePb,
                'created_at'  => now(),
                'updated_at'  => now(),
            ],
        ]);
    }

    /**
     * Update title attributes on the media_gallery_templates table.
     */
    public function update(int $id, array $data): bool
    {
        $updateData = array_merge($data, ['updated_at' => now()]);

        return (bool) DB::table('media_gallery_templates')
            ->where('mediagal_id', $id)
            ->update($updateData);
    }

    /**
     * Update description messages for languages 1 and 2.
     */
    public function updateDescriptions(int $mediagalId, string $titleEn, string $titlePb): void
    {
        DB::table('media_gallery_template_descriptions')
            ->where('mediagal_id', $mediagalId)
            ->where('language_id', 1)
            ->update([
                'message'    => $titleEn,
                'updated_at' => now(),
            ]);

        DB::table('media_gallery_template_descriptions')
            ->where('mediagal_id', $mediagalId)
            ->where('language_id', 2)
            ->update([
                'message'    => $titlePb,
                'updated_at' => now(),
            ]);
    }

    /**
     * Delete gallery template and its descriptions from the database.
     */
    public function delete(int $id): bool
    {
        DB::table('media_gallery_template_descriptions')->where('mediagal_id', $id)->delete();
        return (bool) DB::table('media_gallery_templates')->where('mediagal_id', $id)->delete();
    }
}
