<?php

namespace Modules\Auth\Services;

use App\Helpers\CurlHelper;
use App\Models\SandesLog;
use App\Models\SandesTemplate;
use App\Models\SmsTemplate;
use App\Models\UnitRegistration;
use App\Models\User;
use App\Services\SandesService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class MobileVerificationService
{
    // private const SMS_TEMPLATE_ID = 1407165900230697564;

    public function __construct(
        private SandesService $sandesService
    ) {}

    public function generateOtp(string $mobileNumber, bool $isRegistration = false, ?int $mobileTemplateId = null): array
    {
        if ($isRegistration) {
            $phoneNumberExist = $this->checkPhoneNumberExist($mobileNumber);

            if (! $phoneNumberExist) {
                return [
                    'success' => false,
                    'status' => 422,
                    'message' => 'Phone number is already registered with another account.Try Again with a different phone number.',
                    'data' => [],
                ];
            }
        }

        // RATE LIMIT
        if ($rateLimit = $this->checkOtpRateLimit($mobileNumber)) {
            return $rateLimit;
        }

        // $otp = random_int(100000, 999999);
        $otp = 123456;
        $result = $this->sendOtpsAsync($mobileNumber, $otp, $isRegistration, $mobileTemplateId);

        Log::info('OTP Result', [
            'result' => $result,
        ]);

        return [
            'success' => $result['sms'] && $result['sandes'],
            'status' => 200,
            'message' => 'OTP processed.',
            'data' => [
                'sms_status' => $result['sms'],
                'sandes_status' => $result['sandes'],
                'otp' => $otp,
            ],
        ];

    }

    public function getOtpTemplateId(bool $isRegistration, ?int $mobileTemplateId): int
    {
        // Registration OTP always uses template 1
        if ($isRegistration) {
            return 1;
        }

        // Otherwise use provided template
        return $mobileTemplateId ?? 1;
    }

    public function checkPhoneNumberExist(string $mobileNumber): bool
    {
        return ! User::where('mobile_number', $mobileNumber)->exists();
    }

    private function sendSmsOtp(string $mobileNumber, int $otp): int
    {

        $status = $this->getEnvironmentStatus($mobileNumber, $otp, 'sms');

        if ($status === 0) {
            return SmsTemplate::OTP([
                'otp' => $otp,
                'user_mobile' => $mobileNumber,
            ]) == 1 ? 1 : 2;
        }

        if ($status === 1) {
            return 1;
        }

        return 3;

        //     $SMSData = [
        //         'otp' => $otp,
        //         'user_mobile' => $mobileNumber,
        //     ];

        //     $serverIp = request()->server('HTTP_HOST') ?? '';

        //     if (
        //         str_contains($serverIp, 'pwrda.punjab.gov.in') ||
        //         str_contains($serverIp, '10.43.250.89')
        //     ) {
        //         return SmsTemplate::OTP($SMSData) == 1 ? 1 : 2;
        //     }

        //     if (
        //         str_contains($serverIp, '10.43.250.63') ||
        //         str_contains($serverIp, '10.147.8.171') ||
        //         str_contains($serverIp, '127.0.0.1') ||
        //         str_contains($serverIp, '10.43.228.13') ||
        //         str_contains($serverIp, 'localhost')
        //     ) {
        //         return 1;
        //     }

        //     return 3;
    }

    private function sendSandesOtp(string $mobileNumber, int $otp): int
    {

        $status = $this->getEnvironmentStatus($mobileNumber, $otp, 'sandes');

        if ($status === 0) {
            return SandesTemplate::OTP([
                'otp' => $otp,
                'user_mobile' => $mobileNumber,
            ]) == 1 ? 1 : 2;
        }

        if ($status === 1) {
            return 1;
        }

        return 3;
    }

    private function sendOtpsAsync(string $mobileNumber, int $otp, bool $isRegistration, ?int $mobileTemplateId): array
    {

        $smsDetails = SmsTemplate::getSMSDetails(config('services.sms.template_id'));

        $smsRequest = SmsTemplate::prepareOtpSmsCurl(
            $mobileNumber,
            $otp,
            $smsDetails['template_id']

        );

        $sandesRequest = $this->sandesService->prepareSandesCurl(
            $mobileNumber,
            (string) $otp,
            $this->getOtpTemplateId($isRegistration, $mobileTemplateId)
        );

        $smsCurl = CurlHelper::createCurlHandle($smsRequest);
        $sandesCurl = CurlHelper::createCurlHandle($sandesRequest);

        $multiHandle = curl_multi_init();

        curl_multi_add_handle($multiHandle, $smsCurl);
        curl_multi_add_handle($multiHandle, $sandesCurl);

        $running = null;

        do {
            $status = curl_multi_exec($multiHandle, $running);

            if ($status !== CURLM_OK) {
                break;
            }

            if ($running) {
                curl_multi_select($multiHandle);
            }

        } while ($running > 0);

        // Responses
        $smsResponse = curl_multi_getcontent($smsCurl);
        $sandesResponse = curl_multi_getcontent($sandesCurl);

        // Decode responses
        $smsBody = json_decode($smsResponse, true);
        $sandesBody = json_decode($sandesResponse, true);

        // dd($smsBody);

        // HTTP info
        $smsInfo = curl_getinfo($smsCurl);
        $sandesInfo = curl_getinfo($sandesCurl);

        // Determine success
        $smsSuccess =
            ($smsInfo['http_code'] ?? 0) === 200 &&
            curl_errno($smsCurl) === 0;

        $sandesSuccess =
            ($sandesInfo['http_code'] ?? 0) === 200 &&
            is_array($sandesBody) &&
            ($sandesBody['status'] ?? '') === 'success';

        Log::info([
            'http_code' => $smsInfo['http_code'],
            'errno' => curl_errno($smsCurl),
            'error' => curl_error($smsCurl),
            'info' => $smsInfo,
        ]);

        // Remove handles
        curl_multi_remove_handle($multiHandle, $smsCurl);
        curl_multi_remove_handle($multiHandle, $sandesCurl);

        curl_close($smsCurl);
        curl_close($sandesCurl);
        curl_multi_close($multiHandle);

        return [
            'sms' => [
                'success' => $smsSuccess,
                'response' => $smsBody ?: $smsResponse,
                'http_code' => $smsInfo['http_code'] ?? 0,
            ],

            'sandes' => [
                'success' => $sandesSuccess,
                'response' => $sandesBody ?: $sandesResponse,
                'http_code' => $sandesInfo['http_code'] ?? 0,
            ],
        ];

        // Responses
        // $smsResponse = curl_multi_getcontent($smsCurl);
        // // $sandesResponse = curl_multi_getcontent($sandesCurl);

        // $sandesResponse = curl_multi_getcontent($sandesCurl);

        // $sandesBody = json_decode($sandesResponse, true);

        // $sandesResult = [
        //     'success' => $sandesBody['status'] === 'success',
        //     'response' => $sandesBody,
        //     'http_code' => curl_getinfo($sandesCurl, CURLINFO_HTTP_CODE),
        // ];

        // cURL information
        // $smsInfo = curl_getinfo($smsCurl);
        // $sandesInfo = curl_getinfo($sandesCurl);

        // Log::info('SMS Response', [
        //     'response' => $smsResponse,
        //     'info' => $smsInfo,
        //     'error' => curl_error($smsCurl),
        // ]);

        // Log::info('Sandes Response', [
        //     'response' => $sandesResponse,
        //     'info' => $sandesInfo,
        //     'error' => curl_error($sandesCurl),
        // ]);

        // // Remove handles
        // curl_multi_remove_handle($multiHandle, $smsCurl);
        // curl_multi_remove_handle($multiHandle, $sandesCurl);

        // curl_close($smsCurl);
        // curl_close($sandesCurl);
        // curl_multi_close($multiHandle);

        // return [
        //     'sms' => [
        //         'success' => curl_errno($smsCurl) === 0,
        //         'response' => $smsResponse,
        //         'http_code' => $smsInfo['http_code'] ?? 0,
        //     ],
        //     'sandes' => [
        //         'success' => curl_errno($sandesCurl) === 0,
        //         'response' => $sandesResponse,
        //         'http_code' => $sandesInfo['http_code'] ?? 0,
        //     ],
        // ];
    }

    //  GET ENVIROMENT STATUS FOR OTP
    private function getEnvironmentStatus(string $mobileNumber, int $otp, string $type): int
    {
        $serverIp = request()->server('HTTP_HOST') ?? '';

        // Production
        if (
            str_contains($serverIp, 'pwrda.punjab.gov.in') ||
            str_contains($serverIp, '10.43.250.89') ||
            ($type === 'sandes' && str_contains($serverIp, '10.43.250.95'))
        ) {
            return 0; // Execute actual API
        }

        // Local/Test
        if (
            str_contains($serverIp, '127.0.0.1') ||
            str_contains($serverIp, 'localhost') ||
            str_contains($serverIp, '10.43.250.63') ||
            str_contains($serverIp, '10.43.228.13') ||
            ($type === 'sandes' && str_contains($serverIp, '111.118.215.154')) ||
            ($type === 'sandes' && str_contains($serverIp, '142.132.143.214')) ||
            str_contains($serverIp, '10.147.8.171')
        ) {

            if ($type === 'sandes') {
                SandesLog::createSandesLog(
                    $mobileNumber,
                    'Local Test',
                    'Bypassed SMS for local testing',
                    ['success' => true],
                    $otp,
                    0
                );
            }

            return 1; // Local success
        }

        return 2; // Invalid environment
    }

    public function verifyOtp(array $data): array
    {
        $ownerMobileNumber = $data['mobileNumber'] ?? $data['mobile_number'] ?? $data['sandes_phone_number'] ?? '';
        $mobileVerificationOtp = $data['sandes_verification_otp'] ?? $data['otp'] ?? '';

        $model = new SandesLog;

        $getEntry = $model->where('sandes_no', $ownerMobileNumber)
            ->where('otp', $mobileVerificationOtp)
            ->where(function ($query) {
                $query->where('is_used', 0)
                    ->orWhereNull('is_used');
            })
            ->first();

        // Local environment bypass for hardcoded OTP testing
        $serverIp = request()->server('HTTP_HOST') ?? '';
        if ((str_contains($serverIp, '127.0.0.1') || str_contains($serverIp, 'localhost')) && $mobileVerificationOtp == '123456') {
            $getEntry = new class
            {
                public $is_used = 0;

                public function save()
                {
                    return true;
                }
            };
        }

        $requestData = json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        $encryptNumber = hash_hmac('sha256', $requestData, '!@#$%12345!@#$%');

        if (! $getEntry) {
            $responseData = [
                'status' => 'error',
                'message' => 'Invalid OTP!',
            ];

            $signString = $responseData['status'].'|'.$responseData['message'];
            $signature = hash_hmac('sha256', $signString, '!@#$%12345!@#$%');

            return [
                'success' => false,
                'status' => 400,
                'data' => [
                    'status' => 'error',
                    'message' => 'Invalid OTP!',
                    'token' => $encryptNumber,
                    'signature' => $signature,
                    'signed_data' => $signString,
                ],
            ];
        }

        $getEntry->is_used = 1;

        if (! $getEntry->save()) {
            $responseData = [
                'status' => 'error',
                'message' => 'Failed to verify OTP.',
            ];

            $signature = hash_hmac('sha256', json_encode($responseData), '!@#$%12345!@#$%');

            return [
                'success' => false,
                'status' => 500,
                'data' => [
                    'status' => 'error',
                    'message' => 'Failed to verify OTP.',
                    'token' => $encryptNumber,
                    'signature' => $signature,
                ],
            ];
        }

        if (
            ! empty($data['unit_id']) &&
            $data['unit_id'] !== '00000000000'
        ) {
            $updated = UnitRegistration::where('unit_id', $data['unit_id'])
                ->update([
                    'owner_mobile_verified' => 1,
                    'owner_mobile' => $ownerMobileNumber,
                ]);

            if (! $updated) {
                $responseData = [
                    'status' => 'error',
                    'message' => 'Failed to update contact info.',
                ];

                $signString = $responseData['status'].'|'.$responseData['message'];
                $signature = hash_hmac('sha256', $signString, '!@#$%12345!@#$%');

                return [
                    'success' => false,
                    'status' => 400,
                    'data' => [
                        'status' => 'error',
                        'message' => 'Failed to update contact info.',
                        'token' => $encryptNumber,
                        'signature' => $signature,
                        'signed_data' => $signString,
                    ],
                ];
            }
        }

        $responseData = [
            'status' => 'success',
            'message' => 'OTP verified successfully.',
        ];

        $signString = $responseData['status'].'|'.$responseData['message'];
        $signature = hash_hmac('sha256', $signString, '!@#$%12345!@#$%');

        Cache::put('otp_verified:'.Auth::guard('api')->id(), true, now()->addMinutes(10));

        return [
            'success' => true,
            'status' => 200,
            'data' => [
                'status' => 'success',
                'message' => 'OTP verified successfully.',
                'token' => $encryptNumber,
                'signature' => $signature,
                'signed_data' => $signString,
            ],
        ];
    }

    private function checkOtpRateLimit(string $mobileNumber): ?array
    {
        $lastOtp = SandesLog::where('sandes_no', $mobileNumber)
            ->latest('created_at')
            ->first();

        if ($lastOtp) {
            $secondsPassed = now()->timestamp - $lastOtp->created_at->timestamp;

            if ($secondsPassed < 30) {
                return [
                    'success' => false,
                    'status' => 429,
                    'message' => 'Please try again after 30 seconds.',
                    'data' => [],
                ];
            }
        }

        return null;
    }
}
