<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
// for creating log
use Illuminate\Support\Facades\Log;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;
use App\Traits\HasPublicId;

/**
 * SMS template
 *
 * @mixin Builder
 */
class SmsTemplate extends Model
{
    // use Loggable; //for creating log
    use HasFactory, LogsActivity, HasPublicId;

    protected $primaryKey = 'template_id';

    protected $appends = [
        'public_id',
    ];

    protected $smsURL = '';

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


    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->logOnlyDirty()
            ->dontLogEmptyChanges();
    }
}
