<?php

namespace Modules\Front\Services;

use App\Models\CategoryTemplate;
use App\Models\NoticeboardTemplate;
use Carbon\Carbon;

class NotificationService
{
    /**
     * Get active notifications / announcements, optionally filtered by category.
     */
    public function getNotifications(?int $limit = 10, ?int $categoryId = null)
    {
        $query = NoticeboardTemplate::with(['descriptions', 'category'])
            ->where('status', true);

        if ($categoryId) {
            $query->where('category_name', $categoryId);
        }

        $notices = $query->orderBy('publish_date', 'desc')
            ->orderBy('template_id', 'desc')
            ->limit($limit)
            ->get();

        return $notices->map(function ($item) {
            return $this->formatNotice($item);
        });
    }

    /**
     * Get category tabs for the home noticeboard (categories with display_on_home_page = true).
     */
    public function getNoticeBoardTabs()
    {
        $categories = CategoryTemplate::with('descriptions')
            ->where('status', true)
            ->where('display_on_home_page', true)
            ->orderBy('template_id', 'asc')
            ->get();

        return $categories->map(function ($cat) {
            $enDesc = $cat->descriptions->firstWhere('language_id', 1);
            $pbDesc = $cat->descriptions->firstWhere('language_id', 2);

            $nameEn = $this->extractCategoryName($enDesc?->message, $cat->name);
            $namePb = $this->extractCategoryName($pbDesc?->message, '');

            $notices = NoticeboardTemplate::with('descriptions')
                ->where('category_name', $cat->template_id)
                ->where('status', true)
                ->orderBy('publish_date', 'desc')
                ->orderBy('template_id', 'desc')
                ->limit(5)
                ->get()
                ->map(function ($item) {
                    return $this->formatNotice($item);
                });

            return [
                'id' => $cat->template_id,
                'name' => $nameEn ?: $cat->name,
                'name_pb' => $namePb,
                'route' => '/notice',
                'notices' => $notices,
            ];
        });
    }

    /**
     * Get active items for the news ticker.
     * Uses the latest notifications of the notification category with limit up to 10.
     */
    public function getTickerItems(?int $limit = 10, ?int $categoryId = null)
    {
        $limit = ($limit && $limit > 0) ? min($limit, 10) : 10;

        if (!$categoryId) {
            // Find the notification category (e.g. 'Notifications and Circulars' or 'Notification')
            $notificationCategory = CategoryTemplate::where('status', true)
                ->where(function ($q) {
                    $q->where('name', 'ilike', '%notification%')
                      ->orWhere('name', 'like', '%Notification%');
                })
                ->orderBy('template_id', 'asc')
                ->first();

            $categoryId = $notificationCategory?->template_id;
        }

        $items = $this->getNotifications($limit, $categoryId);

        // Fallback: If no notices exist under the notification category, return latest active notices
        if ($items->isEmpty() && $categoryId) {
            $items = $this->getNotifications($limit, null);
        }

        return $items;
    }

    /**
     * Format a NoticeboardTemplate model into a normalized array payload.
     */
    public function formatNotice(NoticeboardTemplate $item): array
    {
        $english = $item->descriptions->firstWhere('language_id', 1);
        $punjabi = $item->descriptions->firstWhere('language_id', 2);

        $englishData = json_decode($english?->message, true);
        $punjabiData = json_decode($punjabi?->message, true);

        $titleEn = is_array($englishData) ? ($englishData['title'] ?? $item->name) : ($item->name ?: ($english?->message ?? ''));
        $titlePb = is_array($punjabiData) ? ($punjabiData['title'] ?? '') : ($punjabi?->message ?? '');

        $descriptionEn = is_array($englishData) ? ($englishData['description'] ?? '') : ($english?->message ?? '');
        $descriptionPb = is_array($punjabiData) ? ($punjabiData['description'] ?? '') : ($punjabi?->message ?? '');

        // Determine if notice is "New" (published within last 30 days)
        $isNew = false;
        if (!empty($item->publish_date)) {
            $publishDate = Carbon::parse($item->publish_date);
            $isNew = $publishDate->gte(Carbon::now()->subDays(30));
        }

        // File download / view url if available
        $fileUrl = $item->upload_notice ? asset('uploads/' . $item->upload_notice) : null;

        return [
            'id' => $item->template_id,
            'title' => $titleEn ?: $item->name,
            'title_pb' => $titlePb,
            'description' => $descriptionEn,
            'description_pb' => $descriptionPb,
            'date' => $item->publish_date ? Carbon::parse($item->publish_date)->format('d M Y') : ($item->created_at ? $item->created_at->format('d M Y') : ''),
            'raw_date' => $item->publish_date,
            'isNew' => $isNew,
            'file_url' => $fileUrl,
            'url' => $fileUrl ?: '/notice',
            'external' => (bool)$fileUrl,
            'category_id' => $item->category_name,
            'category_name' => $item->category?->name ?? '',
        ];
    }

    /**
     * Extract localized category name from stored JSON payload or plain string.
     */
    private function extractCategoryName(?string $message, string $default): string
    {
        if (empty($message)) {
            return $default;
        }

        if (str_starts_with(trim($message), '{')) {
            $data = json_decode($message, true);
            if (is_array($data) && !empty($data['n'])) {
                return $data['n'];
            }
        }

        return $message;
    }
}
