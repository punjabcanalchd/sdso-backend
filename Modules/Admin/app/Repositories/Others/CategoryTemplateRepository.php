<?php

namespace Modules\Admin\Repositories\Others;

use App\Models\CategoryTemplate;
use App\Models\CategoryTemplateDescription;
use Illuminate\Support\Facades\DB;

class CategoryTemplateRepository
{
    public function getAll(int $limit, ?string $search, ?string $sortColumn = 'template_id', ?string $sortDirection = 'desc')
    {
        $query = CategoryTemplate::with('descriptions');

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'ilike', "%{$search}%")
                  ->orWhereHas('descriptions', function ($dq) use ($search) {
                      $dq->where('message', 'ilike', "%{$search}%");
                  });
            });
        }

        $allowedSorts = ['name', 'status', 'display_on_home_page', 'created_at', 'template_id'];
        $sortColumn = in_array($sortColumn, $allowedSorts) ? $sortColumn : 'template_id';
        $sortDirection = strtolower($sortDirection) === 'asc' ? 'asc' : 'desc';

        return $query->orderBy($sortColumn, $sortDirection)->paginate($limit);
    }

    public function findById($id): ?CategoryTemplate
    {
        $category = CategoryTemplate::findByPublicId($id) ?? CategoryTemplate::find($id);
        if ($category) {
            $category->load('descriptions');
        }
        return $category;
    }

    public function create(array $data): CategoryTemplate
    {
        return DB::transaction(function () use ($data) {
            $status = isset($data['status']) ? (bool) $data['status'] : true;
            $displayOnHome = isset($data['display_on_home_page']) ? (bool) $data['display_on_home_page'] : false;

            $category = CategoryTemplate::create([
                'name'                 => $data['name_en'] ?? $data['name'] ?? '',
                'status'               => $status,
                'display_on_home_page' => $displayOnHome,
            ]);

            // English payload
            $enPayload = [
                'n'  => $data['name_en'] ?? $data['name'] ?? '',
                'mt' => $data['meta_title_en'] ?? '',
                'md' => $data['meta_description_en'] ?? '',
                'mk' => $data['meta_keyword_en'] ?? '',
                'p'  => !empty($data['parent_id']) ? (int) $data['parent_id'] : null,
                'u'  => $data['external_url'] ?? '',
                's'  => isset($data['sort_order']) ? (int) $data['sort_order'] : 1,
                'a'  => $data['access_type'] ?? 'public',
            ];

            CategoryTemplateDescription::create([
                'template_id' => $category->template_id,
                'language_id' => 1,
                'message'     => $this->encodePayload($enPayload),
            ]);

            // Punjabi payload
            $pbPayload = [
                'n'  => $data['name_pb'] ?? '',
                'mt' => $data['meta_title_pb'] ?? '',
                'md' => $data['meta_description_pb'] ?? '',
                'mk' => $data['meta_keyword_pb'] ?? '',
            ];

            CategoryTemplateDescription::create([
                'template_id' => $category->template_id,
                'language_id' => 2,
                'message'     => $this->encodePayload($pbPayload),
            ]);

            return $category->load('descriptions');
        });
    }

    public function update($id, array $data): ?CategoryTemplate
    {
        return DB::transaction(function () use ($id, $data) {
            $category = $this->findById($id);
            if (!$category) {
                return null;
            }

            $updateData = [];
            if (isset($data['name_en'])) {
                $updateData['name'] = $data['name_en'];
            } elseif (isset($data['name'])) {
                $updateData['name'] = $data['name'];
            }
            if (isset($data['status'])) {
                $updateData['status'] = (bool) $data['status'];
            }
            if (isset($data['display_on_home_page'])) {
                $updateData['display_on_home_page'] = (bool) $data['display_on_home_page'];
            }

            if (!empty($updateData)) {
                $category->update($updateData);
            }

            // Existing English description
            $currentEn = $category->descriptions->firstWhere('language_id', 1);
            $existingEn = $this->decodePayload($currentEn?->message, $category->name);

            $enPayload = [
                'n'  => $data['name_en'] ?? $existingEn['name'],
                'mt' => array_key_exists('meta_title_en', $data) ? $data['meta_title_en'] : $existingEn['meta_title'],
                'md' => array_key_exists('meta_description_en', $data) ? $data['meta_description_en'] : $existingEn['meta_description'],
                'mk' => array_key_exists('meta_keyword_en', $data) ? $data['meta_keyword_en'] : $existingEn['meta_keyword'],
                'p'  => array_key_exists('parent_id', $data) ? (!empty($data['parent_id']) ? (int) $data['parent_id'] : null) : $existingEn['parent_id'],
                'u'  => array_key_exists('external_url', $data) ? $data['external_url'] : $existingEn['external_url'],
                's'  => array_key_exists('sort_order', $data) ? (int) $data['sort_order'] : $existingEn['sort_order'],
                'a'  => array_key_exists('access_type', $data) ? $data['access_type'] : $existingEn['access_type'],
            ];

            CategoryTemplateDescription::updateOrCreate(
                [
                    'template_id' => $category->template_id,
                    'language_id' => 1,
                ],
                [
                    'message' => $this->encodePayload($enPayload),
                ]
            );

            // Existing Punjabi description
            $currentPb = $category->descriptions->firstWhere('language_id', 2);
            $existingPb = $this->decodePayload($currentPb?->message, '');

            $pbPayload = [
                'n'  => array_key_exists('name_pb', $data) ? $data['name_pb'] : $existingPb['name'],
                'mt' => array_key_exists('meta_title_pb', $data) ? $data['meta_title_pb'] : $existingPb['meta_title'],
                'md' => array_key_exists('meta_description_pb', $data) ? $data['meta_description_pb'] : $existingPb['meta_description'],
                'mk' => array_key_exists('meta_keyword_pb', $data) ? $data['meta_keyword_pb'] : $existingPb['meta_keyword'],
            ];

            CategoryTemplateDescription::updateOrCreate(
                [
                    'template_id' => $category->template_id,
                    'language_id' => 2,
                ],
                [
                    'message' => $this->encodePayload($pbPayload),
                ]
            );

            return $category->fresh(['descriptions']);
        });
    }

    public function encodePayload(array $data, int $maxLength = 255): string
    {
        // Filter out null or empty strings except name to keep payload tiny
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

        // If too long, trim description first
        if (isset($clean['md']) && is_string($clean['md'])) {
            $clean['md'] = mb_substr($clean['md'], 0, 40);
        }
        $json = json_encode($clean, JSON_UNESCAPED_UNICODE);
        if (strlen($json) <= $maxLength) {
            return $json;
        }

        // Trim keywords
        if (isset($clean['mk']) && is_string($clean['mk'])) {
            $clean['mk'] = mb_substr($clean['mk'], 0, 30);
        }
        $json = json_encode($clean, JSON_UNESCAPED_UNICODE);
        if (strlen($json) <= $maxLength) {
            return $json;
        }

        // Trim meta title
        if (isset($clean['mt']) && is_string($clean['mt'])) {
            $clean['mt'] = mb_substr($clean['mt'], 0, 40);
        }
        $json = json_encode($clean, JSON_UNESCAPED_UNICODE);
        if (strlen($json) <= $maxLength) {
            return $json;
        }

        return mb_strcut($json, 0, $maxLength);
    }

    public function decodePayload(?string $message, string $defaultName = ''): array
    {
        if (empty($message)) {
            return [
                'name'             => $defaultName,
                'meta_title'       => '',
                'meta_description' => '',
                'meta_keyword'     => '',
                'parent_id'        => null,
                'external_url'     => '',
                'sort_order'       => 1,
                'access_type'      => 'public',
            ];
        }

        if (str_starts_with(trim($message), '{')) {
            $data = json_decode($message, true);
            if (is_array($data)) {
                return [
                    'name'             => $data['n'] ?? $data['name'] ?? $defaultName,
                    'meta_title'       => $data['mt'] ?? $data['meta_title'] ?? '',
                    'meta_description' => $data['md'] ?? $data['meta_description'] ?? '',
                    'meta_keyword'     => $data['mk'] ?? $data['meta_keyword'] ?? '',
                    'parent_id'        => isset($data['p']) ? (int) $data['p'] : ($data['parent_id'] ?? null),
                    'external_url'     => $data['u'] ?? $data['external_url'] ?? '',
                    'sort_order'       => isset($data['s']) ? (int) $data['s'] : ($data['sort_order'] ?? 1),
                    'access_type'      => $data['a'] ?? $data['access_type'] ?? 'public',
                ];
            }
        }

        return [
            'name'             => $message ?: $defaultName,
            'meta_title'       => '',
            'meta_description' => '',
            'meta_keyword'     => '',
            'parent_id'        => null,
            'external_url'     => '',
            'sort_order'       => 1,
            'access_type'      => 'public',
        ];
    }

    public function updateStatus($id, bool $status): ?CategoryTemplate
    {
        $category = $this->findById($id);
        if (!$category) {
            return null;
        }

        $category->status = $status;
        $category->save();

        return $category;
    }

    public function updateDisplayOnHome($id, bool $display): ?CategoryTemplate
    {
        $category = $this->findById($id);
        if (!$category) {
            return null;
        }

        $category->display_on_home_page = $display;
        $category->save();

        return $category;
    }
}