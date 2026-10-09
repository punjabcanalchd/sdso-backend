<?php

namespace Modules\Admin\Repositories;

use App\Models\Setting;
use Illuminate\Database\Eloquent\Collection;

class SettingRepository
{
    protected Setting $model;

    public function __construct(Setting $model)
    {
        $this->model = $model;
    }

    /**
     * Get all settings.
     */
    public function all(): Collection
    {
        return $this->model
            ->orderBy('setting_id')
            ->get();
    }

    /**
     * Get settings as key/value array.
     */
    public function getAllAsArray(): array
    {
        return $this->all()
            ->mapWithKeys(function ($setting) {
                return [
                    $setting->config_key => $setting->config_value,
                ];
            })
            ->toArray();
    }

    /**
     * Find setting by key.
     */
    public function findByKey(string $key): ?Setting
    {
        return $this->model
            ->where('config_key', $key)
            ->first();
    }

    /**
     * Get value by key.
     */
    public function getValue(string $key, mixed $default = null): mixed
    {
        $setting = $this->findByKey($key);

        return $setting?->config_value ?? $default;
    }

    /**
     * Create or update setting.
     */
    public function updateOrCreate(string $key, mixed $value): Setting
    {
        return $this->model->updateOrCreate(
            [
                'config_key' => $key,
            ],
            [
                'config_value' => $value,
            ]
        );
    }

    /**
     * Delete setting.
     */
    public function delete(string $key): bool
    {
        return $this->model
            ->where('config_key', $key)
            ->delete() > 0;
    }

    /**
     * Get General Settings formatted for frontend form.
     */
    public function getGeneral(): array
    {
        $raw = $this->getAllAsArray();

        $statusMap = [
            '1' => 'live',
            '2' => 'maintenance',
            '3' => 'offline',
            'live' => 'live',
            'maintenance' => 'maintenance',
            'offline' => 'offline',
        ];
        $langMap = [
            '1' => 'EN',
            '2' => 'PA',
            'EN' => 'EN',
            'PA' => 'PA',
        ];
        $captchaMap = [
            '1' => 'enabled',
            '0' => 'disabled',
            'enabled' => 'enabled',
            'disabled' => 'disabled',
        ];

        return [
            'website_name'           => $raw['website_name'] ?? '',
            'website_url'            => $raw['website_url'] ?? '',
            'website_status'         => $statusMap[$raw['website_status'] ?? 'live'] ?? 'live',
            'contact_support_email'  => $raw['website_support_email'] ?? $raw['contact_support_email'] ?? '',
            'website_support_email'  => $raw['website_support_email'] ?? $raw['contact_support_email'] ?? '',
            'site_icon'              => $raw['website_icon'] ?? $raw['site_icon'] ?? '',
            'website_icon'           => $raw['website_icon'] ?? $raw['site_icon'] ?? '',
            'default_language'       => $langMap[$raw['website_language'] ?? '2'] ?? 'PA',
            'website_language'       => $langMap[$raw['website_language'] ?? '2'] ?? 'PA',
            'facebook'               => $raw['website_facebook'] ?? $raw['facebook'] ?? '',
            'website_facebook'       => $raw['website_facebook'] ?? $raw['facebook'] ?? '',
            'twitter'                => $raw['website_twitter'] ?? $raw['twitter'] ?? '',
            'website_twitter'        => $raw['website_twitter'] ?? $raw['twitter'] ?? '',
            'youtube'                => $raw['website_youtube'] ?? $raw['youtube'] ?? '',
            'website_youtube'        => $raw['website_youtube'] ?? $raw['youtube'] ?? '',
            'instagram'              => $raw['website_instagram'] ?? $raw['instagram'] ?? '',
            'website_instagram'      => $raw['website_instagram'] ?? $raw['instagram'] ?? '',
            'linkedin'               => $raw['website_linkedin'] ?? $raw['linkedin'] ?? '',
            'website_linkedin'       => $raw['website_linkedin'] ?? $raw['linkedin'] ?? '',
            'twitter_section_widget' => $raw['website_twitter_widget'] ?? $raw['twitter_section_widget'] ?? '',
            'website_twitter_widget' => $raw['website_twitter_widget'] ?? $raw['twitter_section_widget'] ?? '',
            'records_per_page'       => (int)($raw['website_pagination'] ?? $raw['records_per_page'] ?? 30),
            'website_pagination'     => (int)($raw['website_pagination'] ?? $raw['records_per_page'] ?? 30),
            'captcha_validation'     => $captchaMap[$raw['captcha_validation'] ?? '1'] ?? 'enabled',
            'adjust_file_size'       => $raw['adjust_file_size'] ?? '',
        ];
    }

    /**
     * Update General Settings.
     */
    public function updateGeneral(array $data): array
    {
        $statusSaveMap = [
            'live' => '1',
            'maintenance' => '2',
            'offline' => '3',
            '1' => '1',
            '2' => '2',
            '3' => '3',
        ];
        $langSaveMap = [
            'EN' => '1',
            'PA' => '2',
            '1' => '1',
            '2' => '2',
        ];
        $captchaSaveMap = [
            'enabled' => '1',
            'disabled' => '0',
            '1' => '1',
            '0' => '0',
        ];

        $keyMapping = [
            'website_name'           => 'website_name',
            'website_url'            => 'website_url',
            'contact_support_email'  => 'website_support_email',
            'website_support_email'  => 'website_support_email',
            'site_icon'              => 'website_icon',
            'website_icon'           => 'website_icon',
            'facebook'               => 'website_facebook',
            'website_facebook'       => 'website_facebook',
            'twitter'                => 'website_twitter',
            'website_twitter'        => 'website_twitter',
            'youtube'                => 'website_youtube',
            'website_youtube'        => 'website_youtube',
            'instagram'              => 'website_instagram',
            'website_instagram'      => 'website_instagram',
            'linkedin'               => 'website_linkedin',
            'website_linkedin'       => 'website_linkedin',
            'twitter_section_widget' => 'website_twitter_widget',
            'website_twitter_widget' => 'website_twitter_widget',
            'records_per_page'       => 'website_pagination',
            'website_pagination'     => 'website_pagination',
            'adjust_file_size'       => 'adjust_file_size',
        ];

        foreach ($data as $k => $val) {
            if ($k === 'website_status') {
                $this->updateOrCreate('website_status', $statusSaveMap[$val] ?? $val);
            } elseif ($k === 'default_language' || $k === 'website_language') {
                $this->updateOrCreate('website_language', $langSaveMap[$val] ?? $val);
            } elseif ($k === 'captcha_validation') {
                $this->updateOrCreate('captcha_validation', $captchaSaveMap[$val] ?? $val);
            } elseif (isset($keyMapping[$k])) {
                $this->updateOrCreate($keyMapping[$k], (string)$val);
            }
        }

        return $this->getGeneral();
    }
}