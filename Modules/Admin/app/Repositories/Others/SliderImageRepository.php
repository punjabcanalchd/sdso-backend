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

    public function paginate(int $sliderId, int $perPage = 30): LengthAwarePaginator
    {
        return $this->model
            ->where('slider_id', $sliderId)
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