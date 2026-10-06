<?php

namespace Modules\Admin\Services\Others;

use App\Models\ApplicationStat;
use App\Models\Setting;
use App\Models\Slider;
use App\Traits\HasPublicId;
use Illuminate\Support\Facades\DB;
use Modules\Admin\Repositories\Others\SliderRepository;

class SliderService
{

 use HasPublicId;

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

   
    /**
     * Get slider by public ID.
     */
    public function getSliderByPublicId(string $public_id): Slider
    {
        $sliderId = (int) $this->decode($public_id);

        $slider = $this->sliderRepository->findById($sliderId);  

        if (! $slider) {
            abort(404, 'Slider not found.');
        }

        return $slider;
    }

    // /**
    //  * Get slider by ID.
    //  */
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
    public function updateSliderByPublicId(int $public_id, array $data): Slider
    {              
        $sliderId = (int) $this->decode($public_id);
        $slider = $this->sliderRepository->findById($sliderId);
        return $this->sliderRepository->update(
            $slider,
            $data
        );
    }

     public function updateStatus(string $public_id, bool $status)
    {   
        $sliderId = (int) $this->decode($public_id);
      
        $slider = Slider::where('slider_id', $sliderId)->first();
       
        if (! $slider) {
            return null;
        }

        $slider->status = $status;
        $slider->save();

        return $slider;
    }

    /**
     * Delete slider.
     */
    public function deleteSlider(int $id): bool
    {
        $slider = $this->getSlider($id);

        return $this->sliderRepository->delete($slider);
    }

    /**
     * Get animated banner status from settings.
     */
    public function getAnimatedBannerStatus(): bool
    {
        $val = DB::table('settings')->where('config_key', 'enable_animated_banner')->value('config_value');
        return $val === '1' || $val === 'true' || $val === 'Y';
    }

    /**
     * Update animated banner status in settings.
     */
    public function updateAnimatedBannerStatus(bool $status): bool
    {
        DB::table('settings')->updateOrInsert(
            ['config_key' => 'enable_animated_banner'],
            ['config_value' => $status ? '1' : '0']
        );

        return $status;
    }
}
