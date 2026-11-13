<?php

namespace Modules\Front\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Modules\Front\Services\HomeService;




class HomeController extends Controller
{
    public function __construct(
        private HomeService $homeService
    ) {}

     public function index(string $language = 'en')
     {        
        $homeData = $this->homeService->getHomeData($language);

        // if (!in_array($languageId, [1, 2], true)) {
        //     $languageId = 1;
        // }

        return response()->json([
            'success' => true,
            'message' => 'Homepage data fetched successfully.',
            'data' => $this->homeService->getHomeData($language),
        ], 200);
     }
    // public function index(): JsonResponse
    // {
    //     return response()->json([
    //         'success' => true,
    //         'data' => $this->homeService->getHomeData(),
    //     ]);
    // }

    // public function getHomeSliders(SliderService $sliderService): JsonResponse
    // {
        
    //     $sliderData = $sliderService->getHomeSliders();
    
    //     return response()->json([
    //         'success' => true,
    //         'message' => 'Home sliders fetched successfully.',
    //         'data'    => $sliderData
    //     ], 200);
    //     }
    }
