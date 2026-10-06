<?php

namespace Modules\Admin\Repositories\Others;


use App\Models\SliderImage;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
class SliderImageRepository
{
    protected SliderImage $model;

    public function __construct(SliderImage $model)
    {
        $this->model = $model;
    }

    public function paginate(int $sliderId, int $perPage = 30, ?string $type = null): LengthAwarePaginator
    {
        $query = $this->model
            ->where('slider_id', $sliderId);

        if ($type === 'gif') {
            $query->where(function ($q) {
                $q->where('file_type', 'gif')
                  ->orWhereRaw("LOWER(image_name) LIKE '%.gif'");
            });
        } elseif ($type === 'image') {
            $query->where(function ($q) {
                $q->where('file_type', 'image')
                  ->orWhere(function ($sq) {
                      $sq->whereNull('file_type')
                         ->whereRaw("LOWER(image_name) NOT LIKE '%.gif'");
                  });
            });
        }

        return $query
            ->orderBy('id', 'DESC')
            ->paginate($perPage);
    }

    public function find(int $id): SliderImage
    {
        return $this->model->findOrFail($id);
    }

    public function create(array $data): SliderImage
    {
        return $this->model->create($data);
    }

    public function update(SliderImage $model, array $data): bool
    {
        return $model->update($data);
    }

    public function delete(SliderImage $model): bool
    {
        return $model->delete();
    }
}