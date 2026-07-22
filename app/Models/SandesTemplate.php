<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log; // for creating log
use Yungts97\LaravelUserActivityLog\Traits\Loggable;

/**
 * SandesTemplate
 *
 * @mixin Builder
 */
class SandesTemplate extends Model
{
    // use Loggable; //for creating log
    use HasFactory;

    protected $primaryKey = 'template_id';

    protected $fillable = [
        'name',
        'status',
    ];

    /**
     * Get the description for the sandes template.
     */
    public function descriptions()
    {
        return $this->hasMany(SandesTemplateDescription::class, 'template_id', 'template_id');
    }

    /**
     * Get the description for the sandes template.
     */
    public function description()
    {
        if (! defined('default_language')) {
            define('default_language', 1);
        }

        return $this->hasone(SandesTemplateDescription::class, 'template_id', 'template_id')->where('language_id', default_language);
    }

    private static function prepareSandesCurl(
        string $mobileNumber,
        int $otp,
        bool $isRegistration,
        ?int $mobileTemplateId
    ): array {

        try {

            $templateId = self::getOtpTemplateId($isRegistration, $mobileTemplateId);

            $template = self::$sandesService->getTemplateDetails($templateId);

            if (! $template || ! isset($template->description->message)) {
                throw new \Exception('Sandes template message not found.');
            }

            $message = str_replace(
                '{OTP}',
                $otp,
                $template->description->message
            );

            $params = [
                'username' => config('services.sandes.username'),
                'password' => config('services.sandes.password'),
                'receiverid' => $mobileNumber,
                'msg' => $message,
            ];

            // Sandes URL condition
            $sandeshUrl = config('services.sandes.url');

            if (defined('sandesh_url')) {
                if (! empty(sandesh_url)) {

                    $sandeshUrl = sandesh_url;

                    if (App::environment('production') && env('IS_SCHEDULER') === true) {
                        $sandeshUrl = $sandeshUrl;
                    }
                }
            }

            $url = $sandeshUrl.'?'.http_build_query($params);

            Log::info('Sandes URL Generated', [
                'url' => $url,
            ]);

            return [
                'url' => $url,
                'method' => 'GET',
                'body' => null,
                'headers' => [],
            ];

        } catch (\Throwable $e) {

            Log::error('Failed to prepare Sandes CURL request', [
                'mobile' => $mobileNumber,
                'otp' => $otp,
                'error' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile(),
            ]);

            return [
                'url' => '',
                'method' => 'GET',
                'body' => null,
                'headers' => [],
                'error' => $e->getMessage(),
            ];
        }
    }
}
