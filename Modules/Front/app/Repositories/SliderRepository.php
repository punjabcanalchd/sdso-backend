<?php

namespace Modules\Front\Repositories;

use App\Models\Slider;
use App\Models\SliderImage;
use App\Models\Setting;

class SliderRepository
{
    public function getHomeSliderImages()
    {
        // Step 1: Get setting
        $setting = Setting::where('config_key', 'home_slider')->first();

        if (!$setting || empty($setting->config_value)) {
            return [];
        }

        // Step 2: Get slider using setting value
        $slider = Slider::where('slider_id', $setting->config_value)
            ->first();     

        if (!$slider) {
            return [];
        }

       $imageData = SliderImage::join('sliders', 'slider_images.slider_id', '=', 'sliders.slider_id')
                    ->where('slider_images.slider_id', $slider['slider_id'])
                    ->where('slider_images.status', 1)
                    ->where('sliders.status', 1)
                    ->orderBy('slider_images.sort_order', 'ASC')
                    ->select('slider_images.*','sliders.name')         
                    ->get()
                    ->toArray();
                    
        // Step 3: Get slider images
        return $imageData;
    }
}