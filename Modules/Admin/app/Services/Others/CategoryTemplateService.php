<?php

namespace Modules\Admin\Services\Others;

use App\Models\CategoryTemplate;
use Modules\Admin\Repositories\Others\CategoryTemplateRepository;

class CategoryTemplateService
{
    protected CategoryTemplateRepository $repository;

    public function __construct(CategoryTemplateRepository $repository)
    {
        $this->repository = $repository;
    }

    public function getCategories(int $limit, ?string $search, ?string $sort_column, ?string $sort_direction)
    {
        $categories = $this->repository->getAll($limit, $search, $sort_column, $sort_direction);
        $categories->getCollection()->transform(function ($category) {
            return $this->formatCategory($category);
        });

        return $categories;
    }

    public function getCategoryByPublicId(string $id)
    {
        $category = $this->repository->findById($id);
        if (!$category) {
            return null;
        }

        return $this->formatCategory($category);
    }

    public function createCategory(array $data): CategoryTemplate
    {
        return $this->repository->create($data);
    }

    public function updateCategory(string $id, array $data): ?CategoryTemplate
    {
        return $this->repository->update($id, $data);
    }

    public function updateStatus(string $id, bool $status): ?CategoryTemplate
    {
        return $this->repository->updateStatus($id, $status);
    }

    public function updateDisplayOnHome(string $id, bool $display): ?CategoryTemplate
    {
        return $this->repository->updateDisplayOnHome($id, $display);
    }

    public function getDropdownList(): array
    {
        return CategoryTemplate::orderBy('name')
            ->get()
            ->map(function ($cat) {
                return [
                    'value' => (int) $cat->template_id,
                    'label' => $cat->name,
                ];
            })
            ->toArray();
    }

    public function formatCategory(CategoryTemplate $item): array
    {
        $english = $item->descriptions->firstWhere('language_id', 1);
        $punjabi = $item->descriptions->firstWhere('language_id', 2);

        $en = $this->repository->decodePayload($english?->message, $item->name);
        $pb = $this->repository->decodePayload($punjabi?->message, '');

        return [
            'id'                   => $item->public_id ?? (string) $item->template_id,
            'template_id'          => $item->template_id,
            'name'                 => $item->name,
            'name_en'              => $en['name'],
            'name_pb'              => $pb['name'],
            'meta_title_en'        => $en['meta_title'],
            'meta_description_en'  => $en['meta_description'],
            'meta_keyword_en'      => $en['meta_keyword'],
            'meta_title_pb'        => $pb['meta_title'],
            'meta_description_pb'  => $pb['meta_description'],
            'meta_keyword_pb'      => $pb['meta_keyword'],
            'parent_id'            => $en['parent_id'],
            'external_url'         => $en['external_url'],
            'sort_order'           => $en['sort_order'],
            'access_type'          => $en['access_type'],
            'display_on_home_page' => $item->display_on_home_page ? 1 : 0,
            'status'               => $item->status ? 1 : 0,
            'created_at'           => $item->created_at ? $item->created_at->format('Y-m-d H:i:s') : null,
        ];
    }
}
