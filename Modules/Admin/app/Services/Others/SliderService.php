<?php

namespace Modules\Admin\Services\Others;

use App\Models\ApplicationStat;
use App\Models\Setting;
use App\Models\Slider;
use Modules\Admin\Repositories\Others\SliderRepository;

class SliderService
{
    public function __construct(
        protected SliderRepository $sliderRepository
    ) {}

    /**
     * Get slider listing.
     */
    public function getPaginatedSliders(
        int $limit,
        ?string $search = null,
        ?string $sortColumn = null,
        ?string $sortDirection = null
    ) {
        return $this->sliderRepository->getAll(
            $limit,
            $search,
            $sortColumn,
            $sortDirection ?? 'desc'
        );
    }

    // public function getPaginatedSliders(): array
    // {
    //     $pagination = defined('website_pagination')
    //         ? website_pagination
    //         : 30;

    //     $results = $this->sliderRepository->paginate($pagination);

    // $enableGifBanner = ApplicationStat::where(
    //     'config_key',
    //     'enable_gif_banner'
    // )
    //     ->where('stats_id', 1)
    //     ->first();

    // $tourismBanner = Setting::where(
    //     'config_key',
    //     'tourism_slider'
    // )->first();

    // $homeBanner = Setting::where(
    //     'config_key',
    //     'home_slider'
    // )->first();

    //     return [
    //         'results' => $results,
    //         // 'enable_gif_banner' => $enableGifBanner,
    //         // 'tourism_slider_id' => $tourismBanner?->config_value,
    //         // 'home_slider_id' => $homeBanner?->config_value,
    //     ];
    // }

    /**
     * Get slider by ID.
     */
    public function getSlider(int $id): Slider
    {
        $slider = $this->sliderRepository->findById($id);

        if (! $slider) {
            abort(404, 'Slider not found.');
        }

        return $slider;
    }

    /**
     * Create slider.
     */
    public function createSlider(array $data): Slider
    {
        return $this->sliderRepository->create($data);
    }

    /**
     * Update slider.
     */
    public function updateSlider(int $id, array $data): Slider
    {
        $slider = $this->getSlider($id);

        return $this->sliderRepository->update(
            $slider,
            $data
        );
    }

    /**
     * Delete slider.
     */
    public function deleteSlider(int $id): bool
    {
        $slider = $this->getSlider($id);

        return $this->sliderRepository->delete($slider);
    }
}
