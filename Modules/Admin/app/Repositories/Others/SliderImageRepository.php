<?php

namespace Modules\Admin\Repositories\Others;

use App\Models\SliderImage;

class SliderImageRepository
{
    protected SliderImage $model;

    public function __construct(SliderImage $model)
    {
        $this->model = $model;
    }

    /**
     * Get paginated slider images.
     */
    public function paginate(
        int $sliderId,
        int $perPage = 30
    ) {
        return $this->model
            ->where('slider_id', $sliderId)
            ->orderBy('id', 'DESC')
            ->paginate($perPage);
    }

    /**
     * Find slider image by ID.
     */
    public function find(int $id): SliderImage
    {
        return $this->model->findOrFail($id);
    }

    /**
     * Create slider image.
     */
    public function create(array $data): SliderImage
    {
        return $this->model->create($data);
    }

    /**
     * Update slider image.
     */
    public function update(
        SliderImage $model,
        array $data
    ): bool {
        return $model->update($data);
    }

    /**
     * Delete slider image.
     */
    public function delete(SliderImage $model): bool
    {
        return $model->delete();
    }
}
