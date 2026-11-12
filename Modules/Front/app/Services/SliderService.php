<?php

namespace Modules\Front\Services;

use Modules\Front\Repositories\SliderRepository;

class SliderService
{
    protected SliderRepository $repository;

    public function __construct(SliderRepository $repository)
    {
        $this->repository = $repository;
    }

    public function getHomeSliders()
    {
        $homeSlider = $this->repository->getHomeSliderImages();

        return [
            'results' => $homeSlider,
            'total'   => count($homeSlider),
        ];
    }
}