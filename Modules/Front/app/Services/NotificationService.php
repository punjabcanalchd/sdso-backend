<?php

namespace Modules\Front\Services;

use App\Models\NoticeboardTemplate;
use Carbon\Carbon;
use Modules\Front\Repositories\NotificationRepository;

class NotificationService
{
    protected NotificationRepository $notificationRepository;

    public function __construct(NotificationRepository $notificationRepository)
    {
        $this->notificationRepository = $notificationRepository;
    }

    /**
     * Get active notifications / announcements, optionally filtered by category.
     */
    public function getNotifications(?int $limit = 10, ?int $categoryId = null)
    {
        $notices = $this->notificationRepository->getActiveNotices($limit, $categoryId);

        return $notices->map(function ($item) {
            return $this->formatNotice($item);
        });
    }

    /**
     * Get category tabs for the home noticeboard.
     */
    public function getNoticeBoardTabs()
    {
        $categories = $this->notificationRepository->getHomeCategories();

        return $categories->map(function ($cat) {
            $enDesc = $cat->descriptions->firstWhere('language_id', 1);
            $pbDesc = $cat->descriptions->firstWhere('language_id', 2);

            $nameEn = $this->extractCategoryName($enDesc?->message, $cat->name);
            $namePb = $this->extractCategoryName($pbDesc?->message, '');

            $notices = $this->notificationRepository
                ->getNoticesByCategory($cat->template_id, 5)
                ->map(function ($item) {
                    return $this->formatNotice($item);
                });

            return [
                'id' => $cat->template_id,
                'name' => $nameEn ?: $cat->name,
                'name_pb' => $namePb,
                'route' => '/noticeboard/' . $cat->template_id,
                'notices' => $notices,
            ];
        });
    }

    /**
     * Get active items for the news ticker.
     */
    public function getTickerItems(?int $limit = 10, ?int $categoryId = null)
    {
        $limit = ($limit && $limit > 0) ? min($limit, 10) : 10;

        if (!$categoryId) {
            $notificationCategory = $this->notificationRepository->findCategoryByNamePattern('%notification%');
            $categoryId = $notificationCategory?->template_id;
        }

        $items = $this->getNotifications($limit, $categoryId);

        if ($items->isEmpty() && $categoryId) {
            $items = $this->getNotifications($limit, null);
        }

        return $items;
    }

    /**
     * Get noticeboard page data for controllers (replaces old noticeboard method logic).
     */
    public function getNoticeboardPageData(int $categoryId, int $languageId = 1)
    {
        $categoryData = $this->notificationRepository
            ->findActiveCategoryById($categoryId);

        if (!$categoryData) {
            return null;
        }

        // Get description for selected language
        $categoryDescription = $categoryData->descriptions
            ->firstWhere('language_id', $languageId);

        // Fallback to English
        if (!$categoryDescription) {
            $categoryDescription = $categoryData->descriptions
                ->firstWhere('language_id', 1);
        }

        $moduleMetaTitle = $this->extractCategoryName($categoryDescription?->message, $categoryData->name);

        $pagination = defined('website_pagination')
            ? website_pagination
            : 30;

        $noticeboardData = $this->notificationRepository
            ->getPaginatedNoticesByCategory($categoryId, $pagination);

        $notificationArray = [];

        foreach ($noticeboardData as $key => $value) {

            $description = $value->descriptions
                ->firstWhere('language_id', $languageId);
            $english = $value->descriptions
                ->firstWhere('language_id', 1);

            $parsedLang = $this->parseNoticeMessage($description?->message, $value->name ?? '');
            $parsedEn = $this->parseNoticeMessage($english?->message, $value->name ?? '');

            // Fallback chain for title
            $title = !empty($parsedLang['title'])
                ? $parsedLang['title']
                : (!empty($parsedEn['title']) ? $parsedEn['title'] : ($value->name ?? ''));

            // Fallback chain for description (HTML body)
            $htmlBody = !empty($parsedLang['description'])
                ? $parsedLang['description']
                : (!empty($parsedEn['description']) ? $parsedEn['description'] : '');

            $displayMessage = !empty($htmlBody) ? $htmlBody : $title;

            $notificationArray[$value->category_name][$key] = [
                'id' => $value->template_id,
                'title' => $title,
                'description' => $htmlBody,
                'message' => $displayMessage,
                'publish_date' => $value->publish_date,
                'upload_notice' => $value->upload_notice,
            ];
        }

        return [
            'noticeboard_data' => $noticeboardData,
            'notificationarray' => $notificationArray,
            'cid' => $categoryId,
            'category_data' => $categoryData,
            'module_meta_title' => $moduleMetaTitle,
        ];
    }

    /**
     * Find specific notice by ID and Language using repository.
     */
    public function getNoticeByIdAndLanguage($id, $languageId)
    {
        return $this->notificationRepository->findByIdAndLanguage($id, $languageId);
    }

    /**
     * Format a NoticeboardTemplate model into a normalized array payload.
     */
    public function formatNotice(NoticeboardTemplate $item): array
    {
        $english = $item->descriptions->firstWhere('language_id', 1);
        $punjabi = $item->descriptions->firstWhere('language_id', 2);

        $englishData = $this->parseNoticeMessage($english?->message, $item->name ?? '');
        $punjabiData = $this->parseNoticeMessage($punjabi?->message, '');

        $titleEn = !empty($englishData['title']) ? $englishData['title'] : ($item->name ?: '');
        $titlePb = !empty($punjabiData['title']) ? $punjabiData['title'] : '';

        $descriptionEn = !empty($englishData['description']) ? $englishData['description'] : '';
        $descriptionPb = !empty($punjabiData['description']) ? $punjabiData['description'] : '';

        $isNew = false;
        if (!empty($item->publish_date)) {
            $publishDate = Carbon::parse($item->publish_date);
            $isNew = $publishDate->gte(Carbon::now()->subDays(30));
        }

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
     * Parse notice description JSON or plain string.
     */
    private function parseNoticeMessage(?string $message, string $defaultTitle = ''): array
    {
        if (empty($message)) {
            return [
                'title' => $defaultTitle,
                'description' => '',
            ];
        }

        $trimmed = trim($message);
        if (str_starts_with($trimmed, '{')) {
            $data = json_decode($trimmed, true);
            if (is_array($data)) {
                $title = !empty($data['title']) ? $data['title'] : $defaultTitle;
                $desc = $data['description'] ?? '';
                return [
                    'title' => $title,
                    'description' => $desc,
                ];
            }
        }

        return [
            'title' => $message,
            'description' => $message,
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