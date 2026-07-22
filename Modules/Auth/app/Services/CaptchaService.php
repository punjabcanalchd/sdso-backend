<?php

namespace Modules\Auth\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class CaptchaService
{
    public function generate()
    {
        $width = 150;
        $height = 50;

        $image = imagecreatetruecolor($width, $height);

        $bgColor = imagecolorallocate($image, 255, 255, 255);
        imagefilledrectangle($image, 0, 0, $width, $height, $bgColor);

        $captchaText = substr(str_shuffle("ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789"), 0, 6);
        $captchaId = Str::random(20);

        // Store in Redis
        Cache::store('redis')->put('captcha_'.$captchaId, $captchaText, now()->addMinutes(5));

        $textColor = imagecolorallocate($image, 0, 0, 0);
        $font = public_path("fonts/cour.ttf");

        $x = 10;

        if (!file_exists($font)) {
            throw new \Exception('Captcha font file not found');
        }
        foreach (str_split($captchaText) as $char) {
            imagettftext($image, 20, rand(-30, 30), $x, 35, $textColor, $font, $char);
            $x += 20;
        }

        // Noise
        for ($i = 0; $i < 50; $i++) {
            imagesetpixel(
                $image,
                rand(0, $width),
                rand(0, $height),
                imagecolorallocate($image, rand(0,255), rand(0,255), rand(0,255))
            );
        }

        ob_start();
        imagepng($image);
        $imageData = ob_get_clean();
        imagedestroy($image);

        return [
            'success' => true,
            'status' => 200,
            'message' => 'Captcha generated successfully',
            'data' => [
                'captcha_id' => $captchaId,
                'captcha_image' => 'data:image/png;base64,' . base64_encode($imageData),
            ]
        ];
    }

    public function verify($captchaId, $input)
    {
        $key = 'captcha_' . $captchaId;

        $stored = Cache::get($key);

        if (!$stored) {
            return [
                'success' => false,
                'status' => 422,
                'message' => 'Captcha expired',
                'data' => null
            ];
        }

        if (strtoupper(trim($input)) !== strtoupper($stored)) {
            return [
                'success' => false,
                'status' => 422,
                'message' => 'Invalid captcha',
                'data' => null
            ];
        }

        // Delete after success
        Cache::forget($key);

        return [
            'success' => true,
            'status' => 200,
            'message' => 'Captcha verified successfully',
            'data' => null
        ];
    }
}