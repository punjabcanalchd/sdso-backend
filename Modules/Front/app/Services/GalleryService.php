<?php

namespace Modules\Front\Services;

use App\Models\MediaCategoryTemplate;
use App\Models\MediaGalleryTemplate;
use Modules\Front\Repositories\GalleryRepository;

class GalleryService
{
    public function __construct(
        protected GalleryRepository $galleryRepository
    ) {}

    /**
     * Get Work Gallery data for home page (categories and gallery items).
     */
    public function getWorkGalleryData(int $languageId = 1, ?int $limit = 30): array
    {
        $isPb = ($languageId === 2);
        $categories = $this->galleryRepository->getHomeCategories();

        // Build category mappings
        $categoryIdentifiers = [];
        $categoryMap = [];
        $formattedCategories = [];

        // Always include 'All' category at the start
        $formattedCategories[] = [
            'id' => 'all',
            'name' => $isPb ? 'ਸਾਰੇ' : 'All',
            'name_en' => 'All',
            'name_pb' => 'ਸਾਰੇ',
        ];

        foreach ($categories as $cat) {
            $catIdStr = (string) $cat->mediacat_id;
            $categoryIdentifiers[] = $catIdStr;
            if (!empty($cat->public_id)) {
                $categoryIdentifiers[] = $cat->public_id;
            }
            if (!empty($cat->name)) {
                $categoryIdentifiers[] = $cat->name;
            }

            $names = $this->extractCategoryNames($cat);
            $catDisplayName = $isPb ? ($names['name_pb'] ?: $names['name_en']) : $names['name_en'];

            $formattedCategories[] = [
                'id' => $cat->mediacat_id,
                'public_id' => $cat->public_id,
                'name' => $catDisplayName,
                'name_en' => $names['name_en'],
                'name_pb' => $names['name_pb'],
            ];

            // Store in lookup map for item category enrichment
            $categoryMap[$catIdStr] = [
                'id' => $cat->mediacat_id,
                'name' => $catDisplayName,
                'name_en' => $names['name_en'],
                'name_pb' => $names['name_pb'],
            ];
            if (!empty($cat->public_id)) {
                $categoryMap[$cat->public_id] = $categoryMap[$catIdStr];
            }
            if (!empty($cat->name)) {
                $categoryMap[$cat->name] = $categoryMap[$catIdStr];
            }
        }

        // Fetch active gallery items
        $items = $this->galleryRepository->getActiveItems(
            !empty($categoryIdentifiers) ? array_values(array_unique($categoryIdentifiers)) : null,
            $limit
        );

        $formattedItems = $items->map(function ($item) use ($isPb, $categoryMap) {
            return $this->formatGalleryItem($item, $isPb, $categoryMap);
        })->values()->toArray();

        return [
            'categories' => $formattedCategories,
            'items' => $formattedItems,
            'total' => count($formattedItems),
        ];
    }

    /**
     * Format a single gallery item for frontend consumption.
     */
    public function formatGalleryItem(MediaGalleryTemplate $item, bool $isPb, array $categoryMap = []): array
    {
        $catKey = (string) $item->category_name;
        $catInfo = $categoryMap[$catKey] ?? null;

        $categoryNameEn = $catInfo['name_en'] ?? $item->category_name;
        $categoryNamePb = $catInfo['name_pb'] ?? $categoryNameEn;
        $categoryDisplay = $isPb ? ($categoryNamePb ?: $categoryNameEn) : $categoryNameEn;

        // Title
        $titleEn = $item->title_en ?: '';
        $titlePb = $item->title_pb ?: $titleEn;
        $title = $isPb ? ($titlePb ?: $titleEn) : ($titleEn ?: $titlePb);

        // Caption from descriptions or title fallback
        $caption = $this->extractCaption($item, $isPb, $title);

        // Image URL
        $imageUrl = $this->resolveImageUrl($item->select_img);

        return [
            'id' => $item->mediagal_id,
            'mediagal_id' => $item->mediagal_id,
            'category_id' => $catInfo['id'] ?? $item->category_name,
            'category' => $categoryNameEn, // Keep English name for easy client-side category matching
            'category_name' => $categoryDisplay,
            'title' => $title,
            'title_en' => $titleEn,
            'title_pb' => $titlePb,
            'caption' => $caption,
            'altText' => $title ?: 'Gallery image',
            'alt_text' => $title ?: 'Gallery image',
            'imageUrl' => $imageUrl,
            'image_url' => $imageUrl,
            'created_at' => $item->created_at?->toIso8601String(),
        ];
    }

    /**
     * Extract localized category names from descriptions and base model.
     */
    private function extractCategoryNames(MediaCategoryTemplate $cat): array
    {
        $enDesc = $cat->descriptions->firstWhere('language_id', 1);
        $pbDesc = $cat->descriptions->firstWhere('language_id', 2);

        $nameEn = $this->decodeCategoryMessage($enDesc?->message, $cat->name);
        $namePb = $this->decodeCategoryMessage($pbDesc?->message, $nameEn);

        return [
            'name_en' => $nameEn ?: $cat->name,
            'name_pb' => $namePb ?: ($nameEn ?: $cat->name),
        ];
    }

    /**
     * Decode category description message (JSON payload or raw string).
     */
    private function decodeCategoryMessage(?string $message, string $default): string
    {
        if (empty($message)) {
            return $default;
        }

        if (str_starts_with(trim($message), '{')) {
            $data = json_decode($message, true);
            if (is_array($data)) {
                return $data['t'] ?? $data['title'] ?? $data['name'] ?? $default;
            }
        }

        return $message;
    }

    /**
     * Extract caption/message from descriptions table.
     */
    private function extractCaption(MediaGalleryTemplate $item, bool $isPb, string $fallback): string
    {
        $langId = $isPb ? 2 : 1;
        $desc = $item->descriptions->firstWhere('language_id', $langId);

        // Fallback to English if Punjabi description not found
        if (!$desc && $isPb) {
            $desc = $item->descriptions->firstWhere('language_id', 1);
        }

        if ($desc && !empty($desc->message)) {
            $msg = trim($desc->message);
            if (str_starts_with($msg, '{')) {
                $decoded = json_decode($msg, true);
                if (is_array($decoded)) {
                    return $decoded['d'] ?? $decoded['caption'] ?? $decoded['description'] ?? $decoded['t'] ?? $fallback;
                }
            }
            return $msg;
        }

        return $fallback;
    }

    /**
     * Resolve image URL to full accessible path.
     */
    private function resolveImageUrl(?string $imgName): string
    {
        if (empty($imgName)) {
            return '';
        }

        if (str_starts_with($imgName, 'http://') || str_starts_with($imgName, 'https://')) {
            return $imgName;
        }

        return asset('uploads/' . ltrim($imgName, '/'));
    }
}

