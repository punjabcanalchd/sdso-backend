<?php

namespace Modules\Admin\Services\Others;

use App\Models\ImageResizer;
use App\Models\Page;
use App\Models\Slider;
use App\Models\SliderImage;
use App\Repositories\Admin\Others\SliderImageRepository;
use Illuminate\Http\UploadedFile;

class SliderImageService
{
    protected SliderImageRepository $repository;

    public function __construct(
        SliderImageRepository $repository
    ) {
        $this->repository = $repository;
    }

    /**
     * Get slider.
     */
    public function getSlider(int $sliderId): Slider
    {
        return Slider::findOrFail($sliderId);
    }

    /**
     * Get slider images with pagination.
     */
    public function getSliderImages(
        int $sliderId,
        int $perPage = 30
    ) {
        return $this->repository->paginate(
            $sliderId,
            $perPage
        );
    }

    /**
     * Get active pages for dropdown.
     */
    public function getPages(): array
    {
        $pages = [];

        $pageResults = Page::where('status', 1)
            ->with('pageDescription')
            ->get();

        foreach ($pageResults as $page) {

            if ($page->pageDescription) {
                $pages[$page->page_id] =
                    $page->pageDescription->title;
            }
        }

        return $pages;
    }

    /**
     * Get slider image by encrypted ID.
     */
    public function getByPublicId(string $id): SliderImage
    {
        $decryptId = encrypt_decrypt_string(
            'decrypt',
            $id
        );

        return $this->repository->find(
            (int) $decryptId
        );
    }

    /**
     * Create slider image.
     */
    public function create(
        int $sliderId,
        array $data,
        ?UploadedFile $image = null
    ): SliderImage {

        $data['slider_id'] = $sliderId;

        if ($image) {
            $fileName = time().'-'.$image->getClientOriginalName();

            $fileName = ImageResizer::store(
                $image,
                'uploads/slider',
                $fileName
            );

            $data['image_name'] = $fileName;
        }

        return $this->repository->create($data);
    }

    /**
     * Update slider image.
     */
    public function update(
        SliderImage $model,
        array $data,
        ?UploadedFile $image = null
    ): SliderImage {

        if ($image) {

            // Delete old image
            if ($model->image_name) {
                ImageResizer::deleteFile(
                    $model->image_name
                );
            }

            $fileName = time().'-'.$image->getClientOriginalName();

            $fileName = ImageResizer::store(
                $image,
                'uploads/slider',
                $fileName
            );

            $data['image_name'] = $fileName;
        }

        $this->repository->update(
            $model,
            $data
        );

        return $model->refresh();
    }

    /**
     * Delete slider image.
     */
    public function delete(SliderImage $model): int
    {
        if ($model->image_name) {
            ImageResizer::deleteFile(
                $model->image_name
            );
        }

        return $this->repository->delete($model);
    }

    /**
     * Get encrypted slider ID.
     */
    public function encryptSliderId(int $sliderId): string
    {
        return encrypt_decrypt_string(
            'encrypt',
            $sliderId
        );
    }

    /**
     * Decrypt slider ID.
     */
    public function decryptSliderId(string $sliderId): int
    {
        return (int) encrypt_decrypt_string(
            'decrypt',
            $sliderId
        );
    }
}
