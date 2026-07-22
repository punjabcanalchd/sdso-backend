<?php

namespace App\Helpers;

class CurlHelper
{
    public static function createCurlHandle(array $request)
    {
        $ch = curl_init();

        $options = [
            CURLOPT_URL => $request['url'],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
        ];

        if (($request['method'] ?? 'GET') === 'POST') {
            $options[CURLOPT_POST] = true;
            $options[CURLOPT_POSTFIELDS] = $request['body'] ?? '';
        }

        if (! empty($request['headers'])) {
            $options[CURLOPT_HTTPHEADER] = $request['headers'];
        }

        curl_setopt_array($ch, $options);

        return $ch;
    }
}
