<?php

namespace Modules\Admin\Repositories\Others;

use App\Models\Slider;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class SliderRepository
{
    protected Slider $model;

    public function __construct(Slider $model)
    {
        $this->model = $model;
    }

    public function getAll(
        int $perPage = 30,
        ?string $search = null,
        ?string $sortColumn = null,
        string $sortDirection = 'desc'
    ): LengthAwarePaginator {

        $query = $this->model->with('images');

        if (! empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('status', 'like', "%{$search}%");
            });
        }

        $allowedSortColumns = [
            'name',
            'status',
            'sort_order',
            'created_at',
            'updated_at',
        ];

        if (
            ! empty($sortColumn) &&
            in_array($sortColumn, $allowedSortColumns, true)
        ) {
            $direction = strtolower($sortDirection) === 'asc'
                ? 'asc'
                : 'desc';

            $query->orderBy($sortColumn, $direction);
        } else {
            $query->orderBy('slider_id', 'DESC');
        }

        return $query->paginate($perPage);
    }

    public function findById(int $id): ?Slider
    {
        return $this->model
            ->with('images')
            ->find($id);
    }

    public function findByPublicId(string $publicId): ?Slider
    {
        return $this->model
            ->with('images')
            ->where('public_id', $publicId)
            ->first();
    }

    public function create(array $data): Slider
    {
        return $this->model->create($data);
    }

    public function update(Slider $slider, array $data): Slider
    {
        $slider->update($data);

        return $slider->fresh('images');
    }

    public function delete(Slider $slider): bool
    {
        return $slider->delete();
    }
}
