<?php

namespace Modules\Admin\Services\Others;

use App\Models\ImageResizer;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Modules\Admin\Repositories\Others\MediaGalleryRepository;

class MediaGalleryService
{
    public function __construct(protected MediaGalleryRepository $repository)
    {
    }

    /**
     * Get all gallery items for a given category with dynamic asset URLs.
     */
    public function getGalleryItems(?string $categoryId, ?string $search = null): Collection
    {
        $items = $this->repository->getByCategory($categoryId, $search);

        return $items->map(function ($item) {
            $imgName = $item->select_img;
            $imgUrl = '';

            if (!empty($imgName)) {
                $imgUrl = str_starts_with($imgName, 'http')
                    ? $imgName
                    : asset('uploads/' . ltrim($imgName, '/'));
            }

            return [
                'id'             => $item->mediagal_id,
                'mediagal_id'    => $item->mediagal_id,
                'category_name'  => $item->category_name,
                'title_en'       => $item->title_en ?? '',
                'title_pb'       => $item->title_pb ?? '',
                'select_img'     => $item->select_img,
                'image_url'      => $imgUrl,
                'status'         => (bool) $item->status,
                'created_at'     => $item->created_at,
            ];
        });
    }

    /**
     * Create a single gallery item with file storage and multilingual descriptions.
     */
    public function createGalleryItem(string $categoryId, UploadedFile $file, string $titleEn, string $titlePb): array
    {
        $resolvedCategoryId = $this->repository->resolveCategoryId($categoryId);
        $fileName = Str::uuid() . '.' . $file->getClientOriginalExtension();

        ImageResizer::store($file, 'uploads', $fileName);

        $galId = $this->repository->create([
            'category_name'  => (string) $resolvedCategoryId,
            'status'         => true,
            'title_en'       => $titleEn,
            'title_pb'       => $titlePb,
            'select_img'     => $fileName,
            'select_video'   => null,
            'select_img_vid' => '0',
        ]);

        $this->repository->createDescriptions($galId, $titleEn, $titlePb);

        return [
            'id'          => $galId,
            'mediagal_id' => $galId,
            'select_img'  => $fileName,
            'image_url'   => asset('uploads/' . $fileName),
            'title_en'    => $titleEn,
            'title_pb'    => $titlePb,
        ];
    }

    /**
     * Create multiple gallery items from batch input inside a database transaction.
     */
    public function createBatch(string $categoryId, array $rows): array
    {
        $created = [];

        DB::transaction(function () use ($categoryId, $rows, &$created) {
            foreach ($rows as $row) {
                $file = $row['file'] ?? null;
                $titleEn = $row['title_en'] ?? '';
                $titlePb = $row['title_pb'] ?? $titleEn;

                if ($file instanceof UploadedFile) {
                    $created[] = $this->createGalleryItem($categoryId, $file, $titleEn, $titlePb);
                }
            }
        });

        return $created;
    }

    /**
     * Update title translations for a gallery image.
     */
    public function updateTitles(int $id, string $titleEn, string $titlePb): bool
    {
        $item = $this->repository->findById($id);
        if (!$item) {
            return false;
        }

        DB::transaction(function () use ($id, $titleEn, $titlePb) {
            $this->repository->update($id, [
                'title_en' => $titleEn,
                'title_pb' => $titlePb,
            ]);

            $this->repository->updateDescriptions($id, $titleEn, $titlePb);
        });

        return true;
    }

    /**
     * Delete a gallery image, its multilingual descriptions, and the physical uploaded file.
     */
    public function deleteItem(int $id): bool
    {
        $item = $this->repository->findById($id);
        if (!$item) {
            return false;
        }

        $fileName = $item->select_img;

        $deleted = $this->repository->delete($id);

        if ($deleted && !empty($fileName)) {
            try {
                $filePath = public_path('uploads/' . $fileName);
                if (file_exists($filePath)) {
                    @unlink($filePath);
                }
            } catch (\Throwable $e) {
                // Log or ignore physical cleanup failure
            }
        }

        return $deleted;
    }
}
