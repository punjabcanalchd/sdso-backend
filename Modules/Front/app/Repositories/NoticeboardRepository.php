<?php

namespace Modules\Front\Repositories;


use App\Models\CategoryTemplate;
use App\Models\NoticeboardTemplate;

class NoticeboardRepository
{
    public function findByIdAndLanguage($id, $languageId)
    {
        // 1. First, check if $id refers to a CategoryTemplate (e.g. /noticeboard/6 or /noticeboard/9)
        $category = CategoryTemplate::where('template_id', $id)->first();

        if ($category) {
            $catDesc = $category->descriptions->firstWhere('language_id', $languageId)
                ?? $category->descriptions->firstWhere('language_id', 1);

            $notices = NoticeboardTemplate::where('category_name', $id)
                ->where('status', true)
                ->with(['descriptions' => function ($query) use ($languageId) {
                    $query->whereIn('language_id', [$languageId, 1]);
                }])
                ->orderBy('publish_date', 'desc')
                ->orderBy('template_id', 'desc')
                ->get();

            $formattedNotices = $notices->map(function ($notice) use ($languageId) {
                $desc = $notice->descriptions->firstWhere('language_id', $languageId)
                    ?? $notice->descriptions->firstWhere('language_id', 1);

                $title = $notice->name;
                if ($desc && !empty($desc->message)) {
                    if (str_starts_with(trim($desc->message), '{')) {
                        $data = json_decode($desc->message, true);
                        if (is_array($data)) {
                            $title = $data['title'] ?? $notice->name;
                        }
                    } else {
                        $title = $desc->message;
                    }
                }

                $fileUrl = null;
                if ($notice->upload_notice) {
                    if (file_exists(public_path('attachments/noticeboard/' . $notice->upload_notice))) {
                        $fileUrl = asset('attachments/noticeboard/' . $notice->upload_notice);
                    } else {
                        $fileUrl = asset('uploads/' . $notice->upload_notice);
                    }
                }

                return [
                    'message' => $title,
                    'publish_date' => $notice->publish_date,
                    'upload_notice' => $notice->upload_notice,
                    'file_url' => $fileUrl,
                ];
            })->toArray();

            $categoryName = $catDesc?->message ?: $category->name;
            if (str_starts_with(trim($categoryName), '{')) {
                $catData = json_decode($categoryName, true);
                if (is_array($catData) && !empty($catData['n'])) {
                    $categoryName = $catData['n'];
                }
            }

            return [
                'category_data' => [
                    'template_id' => $category->template_id,
                    'name' => $categoryName,
                    'description' => [
                        'message' => $categoryName,
                    ],
                ],
                'notificationarray' => [
                    $category->template_id => $formattedNotices,
                ],
                'noticeboard_data' => $formattedNotices,
                'cid' => $category->template_id,
                'module_meta_title' => $categoryName,
            ];
        }

        // 2. Fallback: single notice by template_id
        $notice = NoticeboardTemplate::where('template_id', $id)
            ->with(['descriptions' => function ($query) use ($languageId) {
                $query->whereIn('language_id', [$languageId, 1]);
            }, 'category'])
            ->first();

        if ($notice) {
            $desc = $notice->descriptions->firstWhere('language_id', $languageId)
                ?? $notice->descriptions->firstWhere('language_id', 1);

            $fileUrl = null;
            if ($notice->upload_notice) {
                if (file_exists(public_path('attachments/noticeboard/' . $notice->upload_notice))) {
                    $fileUrl = asset('attachments/noticeboard/' . $notice->upload_notice);
                } else {
                    $fileUrl = asset('uploads/' . $notice->upload_notice);
                }
            }

            return [
                'type' => 'single',
                'template_id' => $notice->template_id,
                'name' => $notice->name,
                'title' => $notice->name,
                'description' => $desc?->message ?: '',
                'publish_date' => $notice->publish_date,
                'upload_notice' => $notice->upload_notice,
                'file_url' => $fileUrl,
                'category' => $notice->category,
            ];
        }

        return null;
    }
}