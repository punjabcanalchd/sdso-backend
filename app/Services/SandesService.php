<?php

namespace App\Services;

use App\Models\SandesLog;
use App\Models\SandesTemplate;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log; // for creating log

class SandesService
{
    /**
     * Get template details.
     */
    public function getTemplateDetails(int $templateId): array
    {
        return SandesTemplate::with('description')
            ->findOrFail($templateId)
            ->toArray();
    }

    public function prepareSandesCurl(string $mobileNumber, string $otp, int $templateId): array
    {
        $template = $this->getTemplateDetails($templateId);
        // Log::info(config('services.sandes.url'));
        $message = str_replace(
            '{otp}',
            $otp,
            $template['description']['message']
        );

        $payload = json_encode([
            'mobile_number' => $mobileNumber,
            'otp' => $otp,
            'template_id' => $templateId,
            'message' => $message,
        ]);

        return [
            'url' => config('services.sandes.url')
            .'?username='.config('services.sandes.username')
            .'&password='.config('services.sandes.password')
            .'&receiverid='.$mobileNumber
            .'&msg='.urlencode($message),
            'method' => 'GET',
        ];
    }

    /**
     * Send Sandes message.
     */
    // public function send(
    //     string $mobileNumber,
    //     string $message
    // ): array {

    //     $response = [
    //         'success' => '',
    //         'failed' => '',
    //     ];

    //     if (!defined('sandesh_url') || empty(sandesh_url)) {
    //         return $response;
    //     }

    //     $sandeshUrl = sandesh_url;

    //     if (
    //         App::environment('production')
    //         && filter_var(env('IS_SCHEDULER'), FILTER_VALIDATE_BOOLEAN)
    //     ) {
    //         $sandeshUrl = 'http://localhost:8021/send';
    //     }

    //     try {

    //         $curl = curl_init();

    //         curl_setopt_array($curl, [
    //             CURLOPT_URL => $sandeshUrl .
    //                 '?receiverid=' . $mobileNumber .
    //                 '&msg=' . urlencode($message),
    //             CURLOPT_RETURNTRANSFER => true,
    //             CURLOPT_ENCODING => '',
    //             CURLOPT_MAXREDIRS => 10,
    //             CURLOPT_TIMEOUT => 30,
    //             CURLOPT_FOLLOWLOCATION => true,
    //             CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    //             CURLOPT_CUSTOMREQUEST => 'GET',
    //         ]);

    //         $returnResponse = curl_exec($curl);

    //         curl_close($curl);

    //         $response['success'] = $returnResponse;

    //     } catch (\Throwable $e) {

    //         $response['failed'] = $e->getMessage();
    //     }

    //     return $response;
    // }

    /**
     * Send OTP.
     */
    public function sendOtp(
        string $mobileNumber,
        string $otp,
        int $templateId = 1
    ): bool {
        // TEST MODE: bypass actual API
        if ($this->isTestEnvironment()) {
            SandesLog::createSandesLog(
                $mobileNumber,
                'TEST OTP',
                'OTP bypassed in test environment',
                ['success' => 1],
                $otp
            );

            return true;
        }
        $template = $this->getTemplateDetails($templateId);

        $message = str_replace(
            '{otp}',
            $otp,
            $template['description']['message']
        );

        $purpose = $template['name'];

        SandesLog::createSandesLog(
            $mobileNumber,
            $purpose,
            $message,
            ['success' => 0],
            $otp
        );

        $response = $this->send(
            $mobileNumber,
            $message
        );

        if (! empty($response['success'])) {

            $decoded = json_decode($response['success']);

            if (
                json_last_error() === JSON_ERROR_NONE
                && isset($decoded->status)
                && $decoded->status === 'success'
            ) {

                SandesLog::where([
                    'sandes_no' => $mobileNumber,
                    'otp' => $otp,
                ])->update([
                    'status' => 1,
                ]);

                return true;
            }
        }

        return false;
    }

    /**
     * Send template-based message.
     */
    public function sendTemplateMessage(
        int $templateId,
        string $mobileNumber,
        array $replacements = []
    ): array {

        $template = $this->getTemplateDetails($templateId);

        $message = $template['description']['message'];

        foreach ($replacements as $key => $value) {
            $message = str_replace(
                '{'.$key.'}',
                $value,
                $message
            );
        }

        return [
            'template' => $template,
            'message' => $message,
            'response' => $this->send(
                $mobileNumber,
                $message
            ),
        ];
    }

    public function sendPasswordChangedMessage(
        string $mobileNumber
    ): array {

        $result = $this->sendTemplateMessage(
            3,
            $mobileNumber
        );

        SandesLog::createSandesLog(
            $mobileNumber,
            $result['template']['name'],
            $result['message'],
            $result['response']
        );

        return $result;
    }

    public function sendForgotPasswordMessage(
        string $mobileNumber,
        string $link
    ): array {

        $result = $this->sendTemplateMessage(
            2,
            $mobileNumber,
            [
                'link' => $link,
            ]
        );

        SandesLog::createSandesLog(
            $mobileNumber,
            $result['template']['name'],
            $result['message'],
            $result['response']
        );

        return $result;
    }

    private function isTestEnvironment(): bool
    {
        $serverIp = request()->server('HTTP_HOST') ?? '';

        return
            str_contains($serverIp, '111.118.215.154') ||
            str_contains($serverIp, '10.43.250.63') ||
            str_contains($serverIp, '142.132.143.214') ||
            str_contains($serverIp, '10.43.228.13') ||
            str_contains($serverIp, '127.0.0.1') ||
            str_contains($serverIp, 'localhost');
    }
}
