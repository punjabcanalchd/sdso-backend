<?php

namespace Modules\Admin\Services\Others;

use App\Models\ImageResizer;
use App\Models\MediaCategoryTemplate;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Modules\Admin\Repositories\Others\MediaCategoryRepository;
use Modules\Admin\Repositories\Others\MediaGalleryRepository;

class MediaCategoryService
{
    public function __construct(
        protected MediaCategoryRepository $repository,
        protected MediaGalleryRepository $galleryRepository
    ) {
    }

    /**
     * Get paginated categories with calculated media counts and decoded titles.
     */
    public function getCategories(int $limit, ?string $search, ?string $sortColumn, ?string $sortDirection): LengthAwarePaginator
    {
        $paginated = $this->repository->getAll($limit, $search, $sortColumn, $sortDirection);

        $categoryIds = $paginated->getCollection()->map(function ($item) {
            return (string) $item->mediacat_id;
        })->toArray();

        $mediaCounts = $this->repository->getMediaCounts($categoryIds);

        $paginated->getCollection()->transform(function ($item) use ($mediaCounts) {
            return $this->formatCategoryListItem($item, $mediaCounts);
        });

        return $paginated;
    }

    /**
     * Get single category details by public_id or primary key ID.
     */
    public function getCategoryById(string|int $id): ?array
    {
        $category = $this->repository->findById($id);
        if (!$category) {
            return null;
        }

        return $this->formatCategoryDetail($category);
    }

    /**
     * Get dropdown categories.
     */
    public function getDropdown(): array
    {
        return $this->repository->getDropdown();
    }

    /**
     * Create category with multilingual descriptions and optional image syncing.
     */
    public function createCategory(array $data, array|UploadedFile|null $files = [], ?string $existingImage = null): array
    {
        if ($files instanceof UploadedFile) {
            $files = [$files];
        } elseif (!is_array($files)) {
            $files = [];
        }

        return DB::transaction(function () use ($data, $files, $existingImage) {
            $parentId = !empty($data['parent_id']) ? (int) $data['parent_id'] : null;
            $status = isset($data['status']) ? (bool) $data['status'] : true;
            $displayOnHome = isset($data['display_on_home_page']) ? (bool) $data['display_on_home_page'] : true;

            $category = $this->repository->createCategory([
                'name'                 => $data['name_en'],
                'status'               => $status,
                'display_on_home_page' => $displayOnHome,
            ]);

            $imageFileNames = [];
            foreach ($files as $file) {
                if ($file instanceof UploadedFile) {
                    $fileName = Str::uuid() . '.' . $file->getClientOriginalExtension();
                    ImageResizer::store($file, 'uploads', $fileName);
                    $imageFileNames[] = $fileName;

                    try {
                        $galId = $this->galleryRepository->create([
                            'category_name'  => (string) $category->mediacat_id,
                            'status'         => true,
                            'title_en'       => $data['name_en'],
                            'title_pb'       => $data['name_pb'],
                            'select_img'     => $fileName,
                            'select_video'   => null,
                            'select_img_vid' => '0',
                        ]);
                        $this->galleryRepository->createDescriptions($galId, $data['name_en'], $data['name_pb']);
                    } catch (\Throwable $e) {
                        \Illuminate\Support\Facades\Log::error('MediaCategoryService createCategory gallery error: ' . $e->getMessage());
                        throw new \Exception('Failed to attach image to media category: ' . $e->getMessage());
                    }
                }
            }

            $primaryImage = !empty($imageFileNames) ? $imageFileNames[0] : $existingImage;

            // English description
            $enPayload = [
                't'   => $data['name_en'],
                'd'   => $data['description_en'] ?? '',
                'img' => $primaryImage,
                'p'   => $parentId,
            ];
            $this->repository->saveDescription($category->mediacat_id, 1, $this->encodePayload($enPayload));

            // Punjabi description
            $pbPayload = [
                't' => $data['name_pb'],
                'd' => $data['description_pb'] ?? '',
            ];
            $this->repository->saveDescription($category->mediacat_id, 2, $this->encodePayload($pbPayload));

            return $this->formatCategoryDetail($category->fresh(['descriptions']));
        });
    }

    /**
     * Update category with multilingual descriptions and sync existing gallery records.
     */
    public function updateCategory(string|int $id, array $data, array|UploadedFile|null $files = [], array $deletedImages = [], ?string $existingImage = null): ?array
    {
        $category = $this->repository->findById($id);
        if (!$category) {
            return null;
        }

        if ($files instanceof UploadedFile) {
            $files = [$files];
        } elseif (!is_array($files)) {
            $files = [];
        }

        return DB::transaction(function () use ($category, $data, $files, $deletedImages, $existingImage) {
            $currentEn = $category->descriptions->firstWhere('language_id', 1);
            $existingEn = $this->decodePayload($currentEn?->message, $category->name);

            $parentId = array_key_exists('parent_id', $data)
                ? (!empty($data['parent_id']) ? (int) $data['parent_id'] : null)
                : $existingEn['parent_id'];

            $status = isset($data['status']) ? (bool) $data['status'] : $category->status;
            $displayOnHome = isset($data['display_on_home_page']) ? (bool) $data['display_on_home_page'] : $category->display_on_home_page;

            $this->repository->updateCategory($category, [
                'name'                 => $data['name_en'],
                'status'               => $status,
                'display_on_home_page' => $displayOnHome,
            ]);

            // 1. Process deleted images if any
            if (!empty($deletedImages)) {
                $catIdStr = (string) $category->mediacat_id;
                foreach ($deletedImages as $del) {
                    $delStr = (string) $del;
                    if (!empty($existingEn['image']) && ($delStr === '0' || $delStr === 'cat_' . $category->mediacat_id || $delStr === $existingEn['image'])) {
                        try {
                            ImageResizer::deleteFile('uploads/' . $existingEn['image']);
                        } catch (\Throwable $e) {}
                    }
                    $itemsToDelete = DB::table('media_gallery_templates')
                        ->where('category_name', $catIdStr)
                        ->where(function ($q) use ($delStr) {
                            if (is_numeric($delStr)) {
                                $q->where('mediagal_id', (int) $delStr)
                                  ->orWhere('select_img', $delStr);
                            } else {
                                $q->where('select_img', $delStr);
                            }
                        })
                        ->get();

                    foreach ($itemsToDelete as $item) {
                        if (!empty($item->select_img)) {
                            try {
                                ImageResizer::deleteFile('uploads/' . $item->select_img);
                            } catch (\Throwable $e) {}
                        }
                        $this->galleryRepository->delete($item->mediagal_id);
                    }
                }
            }

            // Sync legacy image to gallery if gallery was empty and legacy image is not deleted
            $existingGalleryCount = DB::table('media_gallery_templates')
                ->where('category_name', (string) $category->mediacat_id)
                ->count();

            if ($existingGalleryCount === 0 && !empty($existingEn['image'])) {
                $isLegacyDeleted = false;
                foreach ($deletedImages as $del) {
                    $delStr = (string) $del;
                    if ($delStr === '0' || $delStr === 'cat_' . $category->mediacat_id || $delStr === $existingEn['image']) {
                        $isLegacyDeleted = true;
                        break;
                    }
                }
                if (!$isLegacyDeleted) {
                    try {
                        $galId = $this->galleryRepository->create([
                            'category_name'  => (string) $category->mediacat_id,
                            'status'         => true,
                            'title_en'       => $data['name_en'],
                            'title_pb'       => $data['name_pb'],
                            'select_img'     => $existingEn['image'],
                            'select_video'   => null,
                            'select_img_vid' => '0',
                        ]);
                        $this->galleryRepository->createDescriptions($galId, $data['name_en'], $data['name_pb']);
                    } catch (\Throwable $e) {
                        \Illuminate\Support\Facades\Log::error('MediaCategoryService createCategory gallery error: ' . $e->getMessage());
                        throw new \Exception('Failed to attach image to media category: ' . $e->getMessage());
                    }
                }
            }

            // 2. Add newly uploaded files without deleting existing ones
            $newImageFileNames = [];
            foreach ($files as $file) {
                if ($file instanceof UploadedFile) {
                    $fileName = Str::uuid() . '.' . $file->getClientOriginalExtension();
                    ImageResizer::store($file, 'uploads', $fileName);
                    $newImageFileNames[] = $fileName;

                    try {
                        $galId = $this->galleryRepository->create([
                            'category_name'  => (string) $category->mediacat_id,
                            'status'         => true,
                            'title_en'       => $data['name_en'],
                            'title_pb'       => $data['name_pb'],
                            'select_img'     => $fileName,
                            'select_video'   => null,
                            'select_img_vid' => '0',
                        ]);
                        $this->galleryRepository->createDescriptions($galId, $data['name_en'], $data['name_pb']);
                    } catch (\Throwable $e) {
                        \Illuminate\Support\Facades\Log::error('MediaCategoryService createCategory gallery error: ' . $e->getMessage());
                        throw new \Exception('Failed to attach image to media category: ' . $e->getMessage());
                    }
                }
            }

            // 3. Update titles on all remaining gallery records for this category
            DB::table('media_gallery_templates')
                ->where('category_name', (string) $category->mediacat_id)
                ->update([
                    'title_en'   => $data['name_en'],
                    'title_pb'   => $data['name_pb'],
                    'updated_at' => now(),
                ]);

            $remainingGalleryIds = DB::table('media_gallery_templates')
                ->where('category_name', (string) $category->mediacat_id)
                ->pluck('mediagal_id');
            foreach ($remainingGalleryIds as $galId) {
                $this->galleryRepository->updateDescriptions($galId, $data['name_en'], $data['name_pb']);
            }

            // 4. Resolve primary thumbnail for media_category_template_descriptions
            $primaryImage = DB::table('media_gallery_templates')
                ->where('category_name', (string) $category->mediacat_id)
                ->orderBy('mediagal_id', 'desc')
                ->value('select_img');

            if (!$primaryImage) {
                if (!empty($newImageFileNames)) {
                    $primaryImage = end($newImageFileNames);
                } elseif (!empty($existingImage)) {
                    $primaryImage = $existingImage;
                }
            }

            // English description
            $enPayload = [
                't'   => $data['name_en'],
                'd'   => array_key_exists('description_en', $data) ? ($data['description_en'] ?? '') : $existingEn['description'],
                'img' => $primaryImage,
                'p'   => $parentId,
            ];
            $this->repository->saveDescription($category->mediacat_id, 1, $this->encodePayload($enPayload));

            // Punjabi description
            $currentPb = $category->descriptions->firstWhere('language_id', 2);
            $existingPb = $this->decodePayload($currentPb?->message, '');

            $pbPayload = [
                't' => $data['name_pb'],
                'd' => array_key_exists('description_pb', $data) ? ($data['description_pb'] ?? '') : $existingPb['description'],
            ];
            $this->repository->saveDescription($category->mediacat_id, 2, $this->encodePayload($pbPayload));

            return $this->formatCategoryDetail($category->fresh(['descriptions']));
        });
    }

    /**
     * Toggle status.
     */
    public function updateStatus(string|int $id, bool $status): ?MediaCategoryTemplate
    {
        $category = $this->repository->findById($id);
        if (!$category) {
            return null;
        }

        $this->repository->updateStatus($category, $status);
        return $category;
    }

    /**
     * Toggle display on home page.
     */
    public function updateDisplayOnHome(string|int $id, bool $display): ?MediaCategoryTemplate
    {
        $category = $this->repository->findById($id);
        if (!$category) {
            return null;
        }

        $this->repository->updateDisplayOnHome($category, $display);
        return $category;
    }

    public function encodePayload(array $data, int $maxLength = 255): string
    {
        $clean = [];
        foreach ($data as $k => $v) {
            if ($v !== '' && $v !== null) {
                $clean[$k] = $v;
            }
        }

        $json = json_encode($clean, JSON_UNESCAPED_UNICODE);
        if (strlen($json) <= $maxLength) {
            return $json;
        }

        if (isset($clean['d']) && is_string($clean['d'])) {
            $allowedDLen = max(10, $maxLength - (strlen($json) - strlen($clean['d'])));
            $clean['d'] = mb_substr($clean['d'], 0, $allowedDLen);
        }
        $json = json_encode($clean, JSON_UNESCAPED_UNICODE);
        if (strlen($json) <= $maxLength) {
            return $json;
        }

        unset($clean['d']);
        $json = json_encode($clean, JSON_UNESCAPED_UNICODE);
        if (strlen($json) <= $maxLength) {
            return $json;
        }

        return mb_strcut($json, 0, $maxLength);
    }

    public function decodePayload(?string $message, string $defaultTitle = ''): array
    {
        if (empty($message)) {
            return [
                'title'       => $defaultTitle,
                'description' => '',
                'image'       => null,
                'parent_id'   => null,
            ];
        }

        if (str_starts_with(trim($message), '{')) {
            $data = json_decode($message, true);
            if (is_array($data)) {
                return [
                    'title'       => $data['t'] ?? $data['title'] ?? $data['name'] ?? $defaultTitle,
                    'description' => $data['d'] ?? $data['description'] ?? '',
                    'image'       => $data['img'] ?? $data['image'] ?? null,
                    'parent_id'   => isset($data['p']) ? (int) $data['p'] : ($data['parent_id'] ?? null),
                ];
            }
        }

        return [
            'title'       => $message ?: $defaultTitle,
            'description' => '',
            'image'       => null,
            'parent_id'   => null,
        ];
    }

    private function formatCategoryListItem(MediaCategoryTemplate $item, $mediaCounts): array
    {
        $english = $item->descriptions->firstWhere('language_id', 1);
        $punjabi = $item->descriptions->firstWhere('language_id', 2);

        $en = $this->decodePayload($english?->message, $item->name);
        $pb = $this->decodePayload($punjabi?->message, '');

        $catIdStr = (string) $item->mediacat_id;
        $galleryCount = (int) ($mediaCounts->get($catIdStr, 0));
        $hasCatImg = !empty($en['image']) ? 1 : 0;
        $mediaCount = max($galleryCount, $hasCatImg);

        return [
            'id'                         => $item->public_id ?? (string) $item->mediacat_id,
            'mediacat_id'                => $item->mediacat_id,
            'name'                       => $en['title'],
            'name_en'                    => $en['title'],
            'name_pb'                    => $pb['title'],
            'description_en'             => $en['description'],
            'description_pb'             => $pb['description'],
            'category_image'             => $en['image'],
            'parent_id'                  => $en['parent_id'],
            'media_count'                => $mediaCount,
            'mediaCount'                 => $mediaCount,
            'display_on_home_page'       => (bool) $item->display_on_home_page,
            'display_on_home_page_label' => $item->display_on_home_page ? 'Yes' : 'No',
            'status'                     => (bool) $item->status,
            'status_label'               => $item->status ? 'Active' : 'Inactive',
            'created_at'                 => $item->created_at ? $item->created_at->format('Y-m-d H:i:s') : null,
        ];
    }

    private function formatCategoryDetail(MediaCategoryTemplate $category): array
    {
        $english = $category->descriptions->firstWhere('language_id', 1);
        $punjabi = $category->descriptions->firstWhere('language_id', 2);

        $en = $this->decodePayload($english?->message, $category->name);
        $pb = $this->decodePayload($punjabi?->message, '');

        $galleryItems = DB::table('media_gallery_templates')
            ->where('category_name', (string) $category->mediacat_id)
            ->orderBy('mediagal_id', 'asc')
            ->get();

        $images = $galleryItems->map(function ($item) {
            return [
                'id'        => $item->mediagal_id,
                'file_name' => $item->select_img,
                'url'       => $item->select_img,
                'title_en'  => $item->title_en,
                'title_pb'  => $item->title_pb,
            ];
        })->toArray();

        if (empty($images) && !empty($en['image'])) {
            $images = [
                [
                    'id'        => 'cat_' . $category->mediacat_id,
                    'file_name' => $en['image'],
                    'url'       => $en['image'],
                    'title_en'  => $en['title'],
                    'title_pb'  => $pb['title'],
                ]
            ];
        }

        return [
            'id'                   => $category->public_id ?? (string) $category->mediacat_id,
            'mediacat_id'          => $category->mediacat_id,
            'name'                 => $en['title'],
            'name_en'              => $en['title'],
            'name_pb'              => $pb['title'],
            'description_en'       => $en['description'],
            'description_pb'       => $pb['description'],
            'display_on_home_page' => $category->display_on_home_page ? 1 : 0,
            'status'               => $category->status ? 1 : 0,
            'category_image'       => $en['image'],
            'images'               => $images,
            'parent_id'            => $en['parent_id'],
            'created_at'           => $category->created_at ? $category->created_at->format('Y-m-d H:i:s') : null,
        ];
    }
}
