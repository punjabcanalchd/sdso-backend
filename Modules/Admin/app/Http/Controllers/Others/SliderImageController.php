<?php

namespace Modules\Admin\Http\Controllers\Others;

use App\Http\Controllers\Controller;
use App\Models\SliderImage;
use App\Rules\SafeImage;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Modules\Admin\Services\Others\SliderImageService;
use Modules\Admin\App\Repositories\Others\SliderImageRepository;

class SliderImageController extends Controller
{
    use ApiResponse;

    protected SliderImageService $service;

    public function __construct(SliderImageService $service)
    {
        $this->service = $service;
    }

    /**
     * Get slider images.
     */
   public function index(Request $request, string $publicId)
{
    $slider = $this->service->getSliderByPublicId($publicId);

    $defaultLimit = config('pagination.default_limit', 30);
    $maxLimit = config('pagination.max_limit', 100);

    $limit = (int) $request->get('per_page', $defaultLimit);
    $limit = min($limit, $maxLimit);
    $limit = max($limit, 1);

    $results = $this->service->getSliderImages(
        $slider->slider_id,
        $limit
    );

    return response()->json([
        'success' => true,
        'message' => 'Slider images fetched successfully.',
        'data' => $results,
        'addtionalDetails' => [],
    ]);
}
   

    /**
     * Store slider image.
     */
    public function store(Request $request,string $publicId)
    {
        $slider=$this->service->getSliderByPublicId($publicId);

        $request->validate($this->rules());

        $model=$this->service->create(
            $slider->slider_id,
            $request->except('image'),
            $request->file('image')
        );

        return $this->successResponse(
            $model,
            'Slider image added successfully.',
            201
        );
    }

    /**
     * Get slider image edit data.
     */
    public function edit(string $image_id)
    {
        $model = $this->service->getByPublicId($image_id);

        return $this->successResponse(
            $model,
            'Slider image details fetched successfully.'
        );
    }


    

    /**
     * Update slider image.
     */
    /**
 * Update slider image.
 */
public function update( Request $request, string $slider_id, string $image_id) {
    $model = $this->service->getByPublicId($image_id);

    $request->validate($this->rules($model->id));

    $model = $this->service->update(
        $model,
        $request->except('image'),
        $request->file('image')
    );

    return $this->successResponse(
        $model,
        'Slider image updated successfully.'
    );
}
    /**
     * Delete slider image.
     */
    public function destroy(string $id)
    {
        $model = $this->service->getByPublicId($id);

        $this->service->delete($model);

        return $this->successResponse(
            null,
            'Slider image deleted successfully.'
        );
    }

    /**
     * Validation rules.
     */
    protected function rules($id = null): array
    {
        if (! $id) {
            return [
                'image' => [
                    'required',
                    'mimes:jpeg,png,jpg',
                    // new SafeImage,
                ],
            ];
        }

        return [
            'image' => [
                'nullable',
                'mimes:jpeg,png,jpg',
                // new SafeImage,
            ],
        ];
    }
}