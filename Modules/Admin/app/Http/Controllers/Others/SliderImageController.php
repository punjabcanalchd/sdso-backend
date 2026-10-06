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
    $type = $request->get('type');

    $results = $this->service->getSliderImages(
        $slider->slider_id,
        $limit,
        $type
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

        $data = $request->except('image');
        if (empty($data['file_type'])) {
            $file = $request->file('image');
            $ext = $file ? strtolower($file->getClientOriginalExtension()) : '';
            $data['file_type'] = $ext === 'gif' ? 'gif' : 'image';
        }

        $model=$this->service->create(
            $slider->slider_id,
            $data,
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

    $data = $request->except('image');
    if (empty($data['file_type']) && $request->hasFile('image')) {
        $file = $request->file('image');
        $ext = strtolower($file->getClientOriginalExtension());
        $data['file_type'] = $ext === 'gif' ? 'gif' : 'image';
    }

    $model = $this->service->update(
        $model,
        $data,
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
    public function destroy(string $slider_id, string $image_id)
    {
        $model = $this->service->getByPublicId($image_id);

        $this->service->delete($model);

        return $this->successResponse(
            null,
            'Slider image deleted successfully.'
        );
    }

    /**
     * Update Status slider image.
     */

        public function status($slider_id, $image_id)
        {
            try {
                $image = $this->service->getByPublicId($image_id);

                if (!$image) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Slider image not found.'
                    ], 404);
                }

                $image->status = $image->status == 1 ? 0 : 1;
                $image->save();

                return response()->json([
                    'success' => true,
                    'message' => $image->status == 1
                        ? 'Slider image activated successfully.'
                        : 'Slider image deactivated successfully.',
                    'data' => [
                        'status' => $image->status
                    ]
                ]);

            } catch (\Exception $e) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unable to update slider image status.',
                    'debug_message' => $e->getMessage()
                ], 500);
            }
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
                    'mimes:jpeg,png,jpg,gif,webp',
                    // new SafeImage,
                ],
            ];
        }

        return [
            'image' => [
                'nullable',
                'mimes:jpeg,png,jpg,gif,webp',
                // new SafeImage,
            ],
        ];
    }
}