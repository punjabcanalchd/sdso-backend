<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
// for creating log
use Illuminate\Support\Facades\Log;
use Yungts97\LaravelUserActivityLog\Traits\Loggable;

/**
 * SMS template
 *
 * @mixin Builder
 */
class SmsTemplate extends Model
{
    // use Loggable; //for creating log
    use HasFactory;

    protected $primaryKey = 'template_id';

    protected $smsURL = '';

    // protected $otpUserName = 'pbwrda.otp';

    // protected $otpPin = 'H0%26xS3%25eO5';

    // protected $smsUserName = 'pbwrda.otp';

    // protected $smsPin = 'H0%26xS3%25eO5';

    // protected $signature = 'PBWRDA';

    // protected $dltEntityId = '1401459960000046848';

    // protected $dltTemplateId = '1407165900230697564';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'status',
        'templateid',
    ];

    /**
     * Get the description for the sms template.
     */
    public function descriptions()
    {
        return $this->hasMany(SmsTemplateDescription::class, 'template_id', 'template_id');
    }

    /**
     * Get the description for the sms template.
     */
    public function description()
    {
        if (! defined('default_language')) {
            define('default_language', 1);
        }

        return $this->hasOne(SmsTemplateDescription::class, 'template_id', 'template_id')->where('language_id', default_language);
    }

    public static function getSMSDetails($id): array
    {
        $data = SmsTemplate::with('description')
            ->where('templateid', $id)
            ->first();

        return $data ? $data->toArray() : [];
    }

    // -----------------------prepareOtpSmsCurl-------------------------

    public static function prepareOtpSmsCurl(string $mobileNumber, int $otp): array
    {
        try {

            $smsDetails = SmsTemplate::getSMSDetails(
                config('services.sms.template_id')
            );

            if (empty($smsDetails) || empty($smsDetails['template_id'])) {
                throw new \Exception('SMS template not found.');
            }

            $message = urlencode(
                "{$otp} is your OTP for authentication. Do not share this OTP to anyone for security reasons. From PWRDA"
            );

            $url = config('services.sms.gateway_url')
                .'?username='.config('services.sms.username')
                .'&pin='.config('services.sms.pin')
                .'&mnumber='.$mobileNumber
                .'&message='.$message
                .'&signature='.config('services.sms.signature')
                .'&dlt_entity_id='.config('services.sms.entity_id')
                .'&dlt_template_id='.$smsDetails['templateid'];

            Log::info('SMS URL Generated', [
                'url' => $url,
            ]);

            return [
                'url' => $url,
                'method' => 'GET',
                'headers' => [
                    'Cache-Control: no-cache',
                    'Connection: close',
                ],
            ];

        } catch (\Throwable $e) {

            Log::error('Failed to prepare SMS CURL request', [
                'mobile' => $mobileNumber,
                'otp' => $otp,
                'error' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile(),
            ]);

            return [
                'url' => '',
                'method' => 'GET',
                'headers' => [],
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Send SMS For Applicant Registration
     *
     * Message : Dear {name}, thanks for registering with PWRDA. Please use your PAN as Username for Login. From PWRDA
     */
    public static function ApplicantRegistration($SMS_data)
    {
        $user_name = $SMS_data['user_name'];
        $user_mobile = $SMS_data['user_mobile'];
        $smsDetails = SmsTemplate::getSMSDetails(config('services.sms.entity_id'));

        if (! empty($smsDetails)) {
            $message = $smsDetails['description']['message'];
            $message = str_replace(['{name}'], [$user_name], $message);
            $purpose = $smsDetails['name'];
        }
    }

    /**
     * Send SMS For OTP
     *
     * Message : {OTP} is your OTP for authentication. Do not share this OTP to anyone for security reasons. From PWRDA
     */
    public static function OTP($SMS_data)
    {
        $functionResponse = 0;
        try {
            $otp = $SMS_data['otp'];
            $user_mobile = $SMS_data['user_mobile'];
            $smsDetails = SmsTemplate::getSMSDetails(config('services.sms.template_id'));
            if (! empty($smsDetails)) {
                $message = $smsDetails['description']['message'];
                $message = str_replace(['{OTP}'], [$otp], $message);
                $purpose = $smsDetails['name'];
                $response = SmsTemplate::SendSMS($user_mobile, $message, config('services.sms.template_id'));

                if (strstr($response, 'Message Accepted')) {
                    $functionResponse = 1;
                }
            }
        } catch (\Exception $e) {
            Log::error('Error in sending OTP SMS: '.$e->getMessage());
        }

        return $functionResponse;
    }

    // public static function SendSMS($user_mobile, $message, $template_id)
    // {

    //     $username = 'pbwrda.sms';

    //     if ($template_id == '1407165900230697564') { // otp id
    //         $username = 'pbwrda.otp';
    //     }

    //     $pin = 'H0%26xS3%25eO5';
    //     $mnumber = $user_mobile;
    //     $message = urlencode($message);
    //     $signature = 'PBWRDA';
    //     $dlt_entity_id = '1401459960000046848';
    //     $dlt_template_id = $template_id;

    //     // $data = "username=$username&pin=$pin&mnumber=$mnumber&message=$message&signature=$signature&dlt_entity_id=$dlt_entity_id&dlt_template_id=$dlt_template_id";

    //     $data = 'username='.$username.'&pin='.$pin.'&mnumber='.$mnumber.'&message='.$message.'&signature='.$signature.'&dlt_entity_id='.$dlt_entity_id.'&dlt_template_id='.$dlt_template_id;

    //     $url = 'https://asmsgw.sms.gov.in/failsafe/MLink';
    //     if (App::environment('production') && env('IS_SCHEDULER') === true) {
    //         $url = 'https://smsgw.sms.gov.in/failsafe/MLink';
    //     }

    //     $urldata = $url.'?'.$data;

    //     // dd($urldata);

    //     $ch = curl_init();

    //     curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    //     curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

    //     // Check if cURL initialization was successful
    //     if ($ch === false) {
    //         return false;
    //     }

    //     curl_setopt($ch, CURLOPT_URL, $urldata);
    //     curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    //     $resp = curl_exec($ch);

    //     // Execute cURL request
    //     $error_n = '';

    //     // Check for errors
    //     if ($resp === false) {
    //         $error_n = 'Curl error: '.curl_error($ch);
    //     } else {
    //         // Output the response
    //         $error_n = $resp;
    //     }

    //     //  dd($error_n);

    //     curl_close($ch);

    //     // var_dump($resp);
    //     return $resp;
    // }
}
