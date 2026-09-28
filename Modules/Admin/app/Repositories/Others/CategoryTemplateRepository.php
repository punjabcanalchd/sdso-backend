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
            $category = CategoryTemplate::create([
                'name' => $data['name_en'] ?? $data['name'] ?? '',
                'status' => isset($data['status']) ? (bool) $data['status'] : true,
                'display_on_home_page' => isset($data['display_on_home_page']) ? (bool) $data['display_on_home_page'] : false,
            ]);

            // English description (language_id: 1)
            CategoryTemplateDescription::create([
                'template_id' => $category->template_id,
                'language_id' => 1,
                'message' => $data['name_en'] ?? $data['name'] ?? '',
            ]);

            // Punjabi description (language_id: 2)
            if (isset($data['name_pb'])) {
                CategoryTemplateDescription::create([
                    'template_id' => $category->template_id,
                    'language_id' => 2,
                    'message' => $data['name_pb'],
                ]);
            }

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

            // Update or create English description (language_id: 1)
            if (isset($data['name_en']) || isset($data['name'])) {
                CategoryTemplateDescription::updateOrCreate(
                    [
                        'template_id' => $category->template_id,
                        'language_id' => 1,
                    ],
                    [
                        'message' => $data['name_en'] ?? $data['name'],
                    ]
                );
            }

            // Update or create Punjabi description (language_id: 2)
            if (isset($data['name_pb'])) {
                CategoryTemplateDescription::updateOrCreate(
                    [
                        'template_id' => $category->template_id,
                        'language_id' => 2,
                    ],
                    [
                        'message' => $data['name_pb'],
                    ]
                );
            }

            return $category->fresh(['descriptions']);
        });
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