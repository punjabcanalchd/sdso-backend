<?php

namespace Modules\Admin\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\SliderImage;
use App\Rules\SafeImage;
use Illuminate\Http\Request;
use Modules\Admin\Services\Others\SliderImageService;

class SliderImageController extends Controller
{
    protected SliderImageService $service;

    public function __construct(SliderImageService $service)
    {
        $this->service = $service;
        $this->middleware('nocache');
        $this->middleware([
            'auth',
            'common.header',
            'permissions',
        ]);
    }

    public function index($slider_id)
    {
        $decryptId = $this->service->decryptSliderId($slider_id);
        $slider = $this->service->getSlider($decryptId);

        $pagination = defined('website_pagination') && !empty(website_pagination)
            ? website_pagination
            : 30;

        $results = $this->service->getSliderImages($decryptId, $pagination);

        return view('admin.slider-image.index', [
            'results' => $results,
            'slider' => $slider,
        ]);
    }

    public function create($slider_id)
    {
        $decryptId = $this->service->decryptSliderId($slider_id);
        $slider = $this->service->getSlider($decryptId);
        $pages = $this->service->getPages();

        return view('admin.slider-image.create', [
            'model' => new SliderImage,
            'slider' => $slider,
            'page' => $pages,
        ]);
    }

    public function store(Request $request, $slider_id)
    {
        $decryptId = $this->service->decryptSliderId($slider_id);
        $this->service->getSlider($decryptId);

        $request->validate($this->rules());

        $this->service->create(
            $decryptId,
            $request->except('image_name'),
            $request->file('image_name')
        );

        return redirect()
            ->route('slider-image-admin', $slider_id)
            ->with('success', 'Your record has been added successfully.');
    }

    public function edit($id)
    {
        $model = $this->service->getByPublicId($id);
        $slider = $this->service->getSlider($model->slider_id);
        $pages = $this->service->getPages();

        return view('admin.slider-image.edit', [
            'model' => $model,
            'slider' => $slider,
            'page' => $pages,
        ]);
    }

    public function update(Request $request, $id)
    {
        $model = $this->service->getByPublicId($id);

        $request->validate($this->rules($model->id));

        $this->service->update(
            $model,
            $request->except('image_name'),
            $request->file('image_name')
        );

        $sliderId = $this->service->encryptSliderId($model->slider_id);

        return redirect()
            ->route('slider-image-admin', $sliderId)
            ->with('success', 'Your record has been updated successfully.');
    }

    public function destroy($id)
    {
        $model = $this->service->getByPublicId($id);
        $sliderId = $this->service->encryptSliderId($model->slider_id);

        $this->service->delete($model);

        return redirect()
            ->route('slider-image-admin', $sliderId)
            ->with('success', 'Your data has been deleted successfully');
    }

    protected function rules($id = null): array
    {
        if (! $id) {
            return [
                'image_name' => [
                    'required',
                    'mimes:jpeg,png,jpg',
                    new SafeImage,
                ],
            ];
        }

        return [
            'image_name' => [
                'nullable',
                'mimes:jpeg,png,jpg',
                new SafeImage,
            ],
        ];
    }
}