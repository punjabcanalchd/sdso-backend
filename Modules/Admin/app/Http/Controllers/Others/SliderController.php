<?php

namespace Modules\Admin\Http\Controllers\Others;

use App\Http\Controllers\Controller;
use App\Models\Slider;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Admin\Requests\Others\Slider\StoreSliderRequest;
use Modules\Admin\Requests\Others\Slider\UpdateSliderRequest;
use Modules\Admin\Services\Others\SliderService;

class SliderController extends Controller
{
    use ApiResponse;

    protected SliderService $service;

    public function __construct(SliderService $service)
    {
        $this->service = $service;
    }

    /**
     * List sliders
     */
    public function index(Request $request)
    {
        $defaultLimit = config('pagination.default_limit');
        $maxLimit = config('pagination.max_limit');

        $limit = (int) $request->get('per_page', $defaultLimit);
        $limit = min($limit, $maxLimit);
        $limit = max($limit, 1);

        $sliders = $this->service->getPaginatedSliders(
            $limit,
            $request->get('search'),
            $request->get('sort_column'),
            $request->get('sort_direction')
        );

        $homeSliderId = (int) (DB::table('settings')->where('config_key', 'home_slider')->value('config_value') ?: 2);
        $animatedBannerStatus = $this->service->getAnimatedBannerStatus();

        $sliders->getCollection()->transform(function ($slider) use ($animatedBannerStatus, $homeSliderId) {
            $isHomeSlider = (int) $slider->slider_id === $homeSliderId || strcasecmp(trim($slider->name), 'home slider') === 0;
            $hasGif = $isHomeSlider || (int) ($slider->gif_count ?? 0) > 0;

            $slider->is_home_slider = $isHomeSlider;
            $slider->has_gif_option = $hasGif;
            $slider->has_animated_banner = $isHomeSlider;
            $slider->enable_animated_banner = $animatedBannerStatus;

            return $slider;
        });

        return $this->paginatedResponse(
            $sliders,
            'Sliders fetched successfully.'
        );
    }

    /**
     * Get single slider
     */
    public function show(string $public_id)
    {
        $slider = $this->service->getSliderByPublicId($public_id);

        if (! $slider) {
            return $this->errorResponse(
                'Slider not found.',
                404
            );
        }

        return $this->successResponse(
            $slider,
            'Slider fetched successfully.'
        );
    }

    /**
     * Create slider
     */
    public function store(StoreSliderRequest $request)
    {
        $slider = $this->service->createSlider(
            $request->validated()
        );

        return $this->successResponse(
            $slider,
            'Slider created successfully.',
            201
        );
    }

    /**
     * Update slider
     */
    public function update( UpdateSliderRequest $request, string $public_id) {
        $slider = $this->service->updateSliderByPublicId(
            $public_id,
            $request->validated()
        );

        if (! $slider) {
            return $this->errorResponse(
                'Slider not found.',
                404
            );
        }

        return $this->successResponse(
            $slider,
            'Slider updated successfully.'
        );
    }

    /**
     * Update status
     */
    public function updateStatus(Request $request,  string $public_id ) {

        $request->validate([
            'status' => ['required', 'boolean'],
        ]);
     

        $slider = $this->service->updateStatus(
            $public_id,
            $request->boolean('status')
        );
       
        if (! $slider) {
            return $this->errorResponse(
                'Slider not found.',
                404
            );
        }

        return $this->successResponse(
            $slider,
            'Slider status updated successfully.'
        );
    }

    /**
     * Delete slider
     */
    public function destroy(string $public_id)
    {
        $deleted = $this->service->deleteByPublicId($public_id);

        if (! $deleted) {
            return $this->errorResponse(
                'Slider not found.',
                404
            );
        }

        return $this->successResponse(
            null,
            'Slider deleted successfully.'
        );
    }

    /**
     * Toggle animated banner status.
     */
    public function toggleAnimatedBanner(Request $request)
    {
        $status = (bool) $request->input('status', false);
        $updated = $this->service->updateAnimatedBannerStatus($status);

        return $this->successResponse(
            ['enable_animated_banner' => $updated],
            'Animated banner status updated successfully.'
        );
    }
}
