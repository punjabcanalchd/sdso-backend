<?php

namespace Modules\Front\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Modules\Front\Services\HomeService;
use Modules\Front\Services\SliderService;

class HomeController extends Controller
{
    public function __construct(
        private HomeService $homeService
    ) {}

    public function index(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $this->homeService->getHomeData(),
        ]);
    }

    public function getHomeSliders(SliderService $sliderService): JsonResponse
    {
        
        $sliderData = $sliderService->getHomeSliders();
    
        return response()->json([
            'success' => true,
            'message' => 'Home sliders fetched successfully.',
            'data'    => $sliderData
        ], 200);
        }
    }
