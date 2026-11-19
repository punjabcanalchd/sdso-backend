<?php

namespace Modules\Front\Repositories;

use App\Models\Setting;
use Illuminate\Support\Collection;

class SettingRepository
{
    /**
     * Retrieve settings key-value pairs for given config keys.
     */
    public function getSettingsByKeys(array $keys): Collection
    {
        return Setting::whereIn('config_key', $keys)
            ->pluck('config_value', 'config_key');
    }

    /**
     * Retrieve all branding, logo, and header-related settings.
     */
    public function getHeaderAndBrandingSettings(): Collection
    {
        $keys = [
            'website_name',
            'website_url',
            'website_icon',
            'site_icon',
            'website_logo',
            'logo_english',
            'website_logo_pb',
            'logo_punjabi',
            'website_secondary_logo',
            'secondary_logo_english',
            'website_secondary_logo_pb',
            'secondary_logo_punjabi',
            'website_logo_heading_en',
            'logo_heading_en',
            'website_logo_heading_pb',
            'logo_heading_pb',
            'website_logo_sub_heading_en',
            'logo_sub_heading_en',
            'website_logo_sub_heading_pb',
            'logo_sub_heading_pb',
            'toogle_status',
            'government_officials_toggle',
            'chief_minister_logo',
            'cabinet_minister_logo',
            'chief_minister_logo_heading_en',
            'chief_minister_logo_heading_pb',
            'chief_minister_logo_sub_heading_en',
            'chief_minister_logo_sub_heading_pb',
            'cabinet_minister_logo_heading_en',
            'cabinet_minister_logo_heading_pb',
            'cabinet_minister_logo_sub_heading_en',
            'cabinet_minister_logo_sub_heading_pb',
        ];

        return $this->getSettingsByKeys($keys);
    }
}
