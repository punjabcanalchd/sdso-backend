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

        $general = [
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

        return array_merge(
            $general,
            $this->getLayout(),
            $this->getFooter(),
            $this->getManageSection(),
            $this->getLogo(),
            $this->getContactUs(),
            $this->getMetaInformation(),
            $this->getAdditionalSettings()
        );
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

    /**
     * Get Layout Settings formatted for frontend form.
     */
    public function getLayout(): array
    {
        $raw = $this->getAllAsArray();
        $unserialize = function ($val) {
            if (!$val) return [];
            $res = @unserialize($val);
            if (is_array($res)) {
                return array_values($res);
            }
            return !empty($val) ? [(string)$val] : [];
        };

        $menus = $unserialize($raw['website_menus'] ?? null);
        $selectedMenu = is_array($menus) ? (string)($menus[0] ?? '') : (string)$menus;

        $homeSliderId       = (string)($raw['home_slider'] ?? '2');
        $footerSliderId     = (string)($raw['footer_slider'] ?? '3');
        $homeLinkSliderId   = (string)($raw['home_link_slider'] ?? '4');
        $homeOtherSliderId  = (string)($raw['home_other_slider'] ?? '5');
        $homeTopFooterId    = (string)($raw['home_top_footer_slider'] ?? '6');
        $indexPageId        = (string)($raw['website_index_text_page_id'] ?? '2');
        $screenReaderPageId = (string)($raw['website_sc_reader_page_id'] ?? '23');

        return [
            'menus'                             => $selectedMenu,
            'website_menus'                     => $menus,
            'home_page_slider'                  => $homeSliderId,
            'home_slider'                       => $homeSliderId,
            'home_page_slider_timing'           => (string)($raw['slider_timing'] ?? '5000'),
            'slider_timing'                     => (string)($raw['slider_timing'] ?? '5000'),
            'footer_slider'                     => $footerSliderId,
            'home_page_link_slider'             => $homeLinkSliderId,
            'home_link_slider'                  => $homeLinkSliderId,
            'home_page_other_links_slider'      => $homeOtherSliderId,
            'home_other_slider'                 => $homeOtherSliderId,
            'home_page_top_footer_links_slider' => $homeTopFooterId,
            'home_top_footer_slider'            => $homeTopFooterId,
            'index_text_page'                   => $indexPageId,
            'website_index_text_page_id'        => $indexPageId,
            'screen_reader_page'                => $screenReaderPageId,
            'website_sc_reader_page_id'         => $screenReaderPageId,
            'default_page_banner'               => $raw['website_default_banner'] ?? '',
            'website_default_banner'            => $raw['website_default_banner'] ?? '',
            'search_page_banner'                => $raw['website_search_banner'] ?? '',
            'website_search_banner'             => $raw['website_search_banner'] ?? '',
            'punjab_map'                        => $raw['punjab_map'] ?? '',
        ];
    }

    /**
     * Update Layout Settings.
     */
    public function updateLayout(array $data): array
    {
        $keyMapping = [
            'home_page_slider_timing'           => 'slider_timing',
            'slider_timing'                     => 'slider_timing',
            'home_page_slider'                  => 'home_slider',
            'home_slider'                       => 'home_slider',
            'footer_slider'                     => 'footer_slider',
            'home_page_link_slider'             => 'home_link_slider',
            'home_link_slider'                  => 'home_link_slider',
            'home_page_other_links_slider'      => 'home_other_slider',
            'home_other_slider'                 => 'home_other_slider',
            'home_page_top_footer_links_slider' => 'home_top_footer_slider',
            'home_top_footer_slider'            => 'home_top_footer_slider',
            'index_text_page'                   => 'website_index_text_page_id',
            'website_index_text_page_id'        => 'website_index_text_page_id',
            'screen_reader_page'                => 'website_sc_reader_page_id',
            'website_sc_reader_page_id'         => 'website_sc_reader_page_id',
            'default_page_banner'               => 'website_default_banner',
            'website_default_banner'            => 'website_default_banner',
            'search_page_banner'                => 'website_search_banner',
            'website_search_banner'             => 'website_search_banner',
            'punjab_map'                        => 'punjab_map',
        ];

        $resolveId = function ($val) {
            if ($val === null || $val === '') return '';
            if (is_numeric($val)) return (string)$val;
            try {
                $decoded = app(\Modules\Admin\Services\Others\SliderService::class)->decode($val);
                return $decoded ? (string)$decoded : (string)$val;
            } catch (\Throwable $e) {
                return (string)$val;
            }
        };

        foreach ($data as $k => $val) {
            if ($k === 'menus' || $k === 'website_menus') {
                $arrVal = is_array($val)
                    ? array_values(array_filter($val, fn($item) => $item !== '' && $item !== null))
                    : ($val !== null && $val !== '' ? [(string)$val] : []);
                $serialized = serialize($arrVal);
                $this->updateOrCreate('website_menus', $serialized);
            } elseif (isset($keyMapping[$k])) {
                $targetKey = $keyMapping[$k];
                $valToSave = in_array($targetKey, [
                    'home_slider', 'footer_slider', 'home_link_slider',
                    'home_other_slider', 'home_top_footer_slider',
                    'website_index_text_page_id', 'website_sc_reader_page_id'
                ], true) ? $resolveId($val) : (string)$val;
                $this->updateOrCreate($targetKey, $valToSave);
            }
        }

        return $this->getLayout();
    }

    /**
     * Get Footer Settings formatted for frontend form.
     */
    public function getFooter(): array
    {
        $raw = $this->getAllAsArray();
        $unserialize = function ($val) {
            if (!$val) return [];
            $res = @unserialize($val);
            return is_array($res) ? array_values($res) : [];
        };

        return [
            'footer_category_1_title_en' => $raw['footer_cat1_title'] ?? '',
            'footer_cat1_title'          => $raw['footer_cat1_title'] ?? '',
            'footer_category_1_title_pb' => $raw['footer_cat1_title_pb'] ?? '',
            'footer_cat1_title_pb'       => $raw['footer_cat1_title_pb'] ?? '',
            'footer_category_1_menus'    => $unserialize($raw['footer_cat1_menus'] ?? null),
            'footer_cat1_menus'          => $unserialize($raw['footer_cat1_menus'] ?? null),

            'footer_category_2_title_en' => $raw['footer_cat2_title'] ?? '',
            'footer_cat2_title'          => $raw['footer_cat2_title'] ?? '',
            'footer_category_2_title_pb' => $raw['footer_cat2_title_pb'] ?? '',
            'footer_cat2_title_pb'       => $raw['footer_cat2_title_pb'] ?? '',
            'footer_category_2_menus'    => $unserialize($raw['footer_cat2_menus'] ?? null),
            'footer_cat2_menus'          => $unserialize($raw['footer_cat2_menus'] ?? null),

            'footer_category_3_title_en' => $raw['footer_cat3_title'] ?? '',
            'footer_cat3_title'          => $raw['footer_cat3_title'] ?? '',
            'footer_category_3_title_pb' => $raw['footer_cat3_title_pb'] ?? '',
            'footer_cat3_title_pb'       => $raw['footer_cat3_title_pb'] ?? '',
            'footer_category_3_menus'    => $unserialize($raw['footer_cat3_menus'] ?? null),
            'footer_cat3_menus'          => $unserialize($raw['footer_cat3_menus'] ?? null),

            'copyright_heading_1_en'     => $raw['website_copyright1'] ?? '',
            'website_copyright1'         => $raw['website_copyright1'] ?? '',
            'copyright_heading_1_pb'     => $raw['website_copyright1_pb'] ?? '',
            'website_copyright1_pb'      => $raw['website_copyright1_pb'] ?? '',

            'copyright_heading_2_en'     => $raw['website_copyright2'] ?? '',
            'website_copyright2'         => $raw['website_copyright2'] ?? '',
            'copyright_heading_2_pb'     => $raw['website_copyright2_pb'] ?? '',
            'website_copyright2_pb'      => $raw['website_copyright2_pb'] ?? '',
        ];
    }

    /**
     * Update Footer Settings.
     */
    public function updateFooter(array $data): array
    {
        $keyMapping = [
            'footer_category_1_title_en' => 'footer_cat1_title',
            'footer_cat1_title'          => 'footer_cat1_title',
            'footer_category_1_title_pb' => 'footer_cat1_title_pb',
            'footer_cat1_title_pb'       => 'footer_cat1_title_pb',

            'footer_category_2_title_en' => 'footer_cat2_title',
            'footer_cat2_title'          => 'footer_cat2_title',
            'footer_category_2_title_pb' => 'footer_cat2_title_pb',
            'footer_cat2_title_pb'       => 'footer_cat2_title_pb',

            'footer_category_3_title_en' => 'footer_cat3_title',
            'footer_cat3_title'          => 'footer_cat3_title',
            'footer_category_3_title_pb' => 'footer_cat3_title_pb',
            'footer_cat3_title_pb'       => 'footer_cat3_title_pb',

            'copyright_heading_1_en'     => 'website_copyright1',
            'website_copyright1'         => 'website_copyright1',
            'copyright_heading_1_pb'     => 'website_copyright1_pb',
            'website_copyright1_pb'      => 'website_copyright1_pb',

            'copyright_heading_2_en'     => 'website_copyright2',
            'website_copyright2'         => 'website_copyright2',
            'copyright_heading_2_pb'     => 'website_copyright2_pb',
            'website_copyright2_pb'      => 'website_copyright2_pb',
        ];

        foreach ($data as $k => $val) {
            if (in_array($k, ['footer_category_1_menus', 'footer_cat1_menus'], true)) {
                $serialized = is_array($val) ? serialize($val) : $val;
                $this->updateOrCreate('footer_cat1_menus', $serialized);
            } elseif (in_array($k, ['footer_category_2_menus', 'footer_cat2_menus'], true)) {
                $serialized = is_array($val) ? serialize($val) : $val;
                $this->updateOrCreate('footer_cat2_menus', $serialized);
            } elseif (in_array($k, ['footer_category_3_menus', 'footer_cat3_menus'], true)) {
                $serialized = is_array($val) ? serialize($val) : $val;
                $this->updateOrCreate('footer_cat3_menus', $serialized);
            } elseif (isset($keyMapping[$k])) {
                $this->updateOrCreate($keyMapping[$k], (string)$val);
            }
        }

        return $this->getFooter();
    }

    /**
     * Get Manage Section Settings formatted for frontend form.
     */
    public function getManageSection(): array
    {
        $raw = $this->getAllAsArray();
        $secMap = ['1' => 'active', '0' => 'inactive', 'active' => 'active', 'inactive' => 'inactive'];

        $result = [];
        for ($i = 1; $i <= 7; $i++) {
            $val = $secMap[$raw["home_page_section_{$i}"] ?? '0'] ?? 'inactive';
            $result["section_{$i}_status"] = $val;
            $result["home_page_section_{$i}"] = $val;
        }

        return $result;
    }

    /**
     * Update Manage Section Settings.
     */
    public function updateManageSection(array $data): array
    {
        $saveMap = ['active' => '1', 'inactive' => '0', '1' => '1', '0' => '0'];

        for ($i = 1; $i <= 7; $i++) {
            if (isset($data["section_{$i}_status"])) {
                $this->updateOrCreate("home_page_section_{$i}", $saveMap[$data["section_{$i}_status"]] ?? '0');
            } elseif (isset($data["home_page_section_{$i}"])) {
                $this->updateOrCreate("home_page_section_{$i}", $saveMap[$data["home_page_section_{$i}"]] ?? '0');
            }
        }

        return $this->getManageSection();
    }

    /**
     * Get Logo Settings formatted for frontend form.
     */
    public function getLogo(): array
    {
        $raw = $this->getAllAsArray();
        $officialsStatus = $raw['toogle_status'] ?? $raw['government_officials_toggle'] ?? '1';
        $isOfficialsActive = ($officialsStatus === '1' || $officialsStatus === 'active' || $officialsStatus === 'true' || $officialsStatus === true || $officialsStatus === 1);

        return [
            'logo_english'                       => $raw['website_logo'] ?? $raw['logo_english'] ?? '',
            'website_logo'                        => $raw['website_logo'] ?? '',
            'logo_punjabi'                        => $raw['website_logo_pb'] ?? $raw['logo_punjabi'] ?? '',
            'website_logo_pb'                     => $raw['website_logo_pb'] ?? '',
            'secondary_logo_english'              => $raw['secondary_logo_english'] ?? $raw['website_secondary_logo'] ?? '',
            'secondary_logo_punjabi'              => $raw['secondary_logo_punjabi'] ?? $raw['website_secondary_logo_pb'] ?? '',
            'logo_heading_en'                     => $raw['website_logo_heading_en'] ?? $raw['logo_heading_en'] ?? '',
            'website_logo_heading_en'             => $raw['website_logo_heading_en'] ?? '',
            'logo_heading_pb'                     => $raw['website_logo_heading_pb'] ?? $raw['logo_heading_pb'] ?? '',
            'website_logo_heading_pb'             => $raw['website_logo_heading_pb'] ?? '',
            'logo_sub_heading_en'                 => $raw['website_logo_sub_heading_en'] ?? $raw['logo_sub_heading_en'] ?? '',
            'website_logo_sub_heading_en'         => $raw['website_logo_sub_heading_en'] ?? '',
            'logo_sub_heading_pb'                 => $raw['website_logo_sub_heading_pb'] ?? $raw['logo_sub_heading_pb'] ?? '',
            'website_logo_sub_heading_pb'         => $raw['website_logo_sub_heading_pb'] ?? '',
            'toogle_status'                       => $isOfficialsActive,
            'government_officials_toggle'         => $isOfficialsActive ? '1' : '0',
            'chief_minister_logo'                 => $raw['chief_minister_logo'] ?? '',
            'cabinet_minister_logo'               => $raw['cabinet_minister_logo'] ?? '',
            'chief_minister_logo_heading_en'      => $raw['chief_minister_logo_heading_en'] ?? '',
            'chief_minister_logo_heading_pb'      => $raw['chief_minister_logo_heading_pb'] ?? '',
            'chief_minister_logo_sub_heading_en'  => $raw['chief_minister_logo_sub_heading_en'] ?? '',
            'chief_minister_logo_sub_heading_pb'  => $raw['chief_minister_logo_sub_heading_pb'] ?? '',
            'cabinet_minister_logo_heading_en'    => $raw['cabinet_minister_logo_heading_en'] ?? '',
            'cabinet_minister_logo_heading_pb'    => $raw['cabinet_minister_logo_heading_pb'] ?? '',
            'cabinet_minister_logo_sub_heading_en'=> $raw['cabinet_minister_logo_sub_heading_en'] ?? '',
            'cabinet_minister_logo_sub_heading_pb'=> $raw['cabinet_minister_logo_sub_heading_pb'] ?? '',
        ];
    }

    /**
     * Update Logo Settings.
     */
    public function updateLogo(array $data): array
    {
        $keyMapping = [
            'logo_english'                       => 'website_logo',
            'website_logo'                        => 'website_logo',
            'logo_punjabi'                        => 'website_logo_pb',
            'website_logo_pb'                     => 'website_logo_pb',
            'secondary_logo_english'              => 'secondary_logo_english',
            'secondary_logo_punjabi'              => 'secondary_logo_punjabi',
            'logo_heading_en'                     => 'website_logo_heading_en',
            'website_logo_heading_en'             => 'website_logo_heading_en',
            'logo_heading_pb'                     => 'website_logo_heading_pb',
            'website_logo_heading_pb'             => 'website_logo_heading_pb',
            'logo_sub_heading_en'                 => 'website_logo_sub_heading_en',
            'website_logo_sub_heading_en'         => 'website_logo_sub_heading_en',
            'logo_sub_heading_pb'                 => 'website_logo_sub_heading_pb',
            'website_logo_sub_heading_pb'         => 'website_logo_sub_heading_pb',
            'chief_minister_logo'                 => 'chief_minister_logo',
            'cabinet_minister_logo'               => 'cabinet_minister_logo',
            'chief_minister_logo_heading_en'      => 'chief_minister_logo_heading_en',
            'chief_minister_logo_heading_pb'      => 'chief_minister_logo_heading_pb',
            'chief_minister_logo_sub_heading_en'  => 'chief_minister_logo_sub_heading_en',
            'chief_minister_logo_sub_heading_pb'  => 'chief_minister_logo_sub_heading_pb',
            'cabinet_minister_logo_heading_en'    => 'cabinet_minister_logo_heading_en',
            'cabinet_minister_logo_heading_pb'    => 'cabinet_minister_logo_heading_pb',
            'cabinet_minister_logo_sub_heading_en'=> 'cabinet_minister_logo_sub_heading_en',
            'cabinet_minister_logo_sub_heading_pb'=> 'cabinet_minister_logo_sub_heading_pb',
        ];

        foreach ($data as $k => $val) {
            if ($k === 'toogle_status' || $k === 'government_officials_toggle') {
                $statusVal = ($val === true || $val === '1' || $val === 'active' || $val === 1 || $val === 'true') ? '1' : '0';
                $this->updateOrCreate('toogle_status', $statusVal);
                $this->updateOrCreate('government_officials_toggle', $statusVal);
            } elseif (isset($keyMapping[$k])) {
                $this->updateOrCreate($keyMapping[$k], (string)$val);
            }
        }

        return $this->getLogo();
    }

    /**
     * Get Contact Us Settings formatted for frontend form.
     */
    public function getContactUs(): array
    {
        $raw = $this->getAllAsArray();

        return [
            'contact_title_en'    => $raw['title_en'] ?? $raw['contact_title_en'] ?? '',
            'title_en'            => $raw['title_en'] ?? '',
            'contact_title_pb'    => $raw['title_pb'] ?? $raw['contact_title_pb'] ?? '',
            'title_pb'            => $raw['title_pb'] ?? '',
            'contact_phone'       => $raw['phone_number'] ?? $raw['contact_phone'] ?? '',
            'phone_number'        => $raw['phone_number'] ?? '',
            'contact_address_en'  => $raw['address_en'] ?? $raw['contact_address_en'] ?? '',
            'address_en'          => $raw['address_en'] ?? '',
            'contact_address_pb'  => $raw['address_pb'] ?? $raw['contact_address_pb'] ?? '',
            'address_pb'          => $raw['address_pb'] ?? '',
            'map_token'           => $raw['token_key'] ?? $raw['map_token'] ?? '',
            'token_key'           => $raw['token_key'] ?? '',
            'map_url'             => $raw['map_url'] ?? '',
            'map_api_key'         => $raw['map_api'] ?? $raw['map_api_key'] ?? '',
            'map_api'             => $raw['map_api'] ?? '',
            'block_service_url'   => $raw['block_service'] ?? $raw['block_service_url'] ?? '',
            'block_service'       => $raw['block_service'] ?? '',
            'block_service_token' => $raw['block_token'] ?? $raw['block_service_token'] ?? '',
            'block_token'         => $raw['block_token'] ?? '',
            'latitude'            => $raw['latitude'] ?? '',
            'longitude'           => $raw['longitude'] ?? '',
        ];
    }

    /**
     * Update Contact Us Settings.
     */
    public function updateContactUs(array $data): array
    {
        $keyMapping = [
            'contact_title_en'    => 'title_en',
            'title_en'            => 'title_en',
            'contact_title_pb'    => 'title_pb',
            'title_pb'            => 'title_pb',
            'contact_phone'       => 'phone_number',
            'phone_number'        => 'phone_number',
            'contact_address_en'  => 'address_en',
            'address_en'          => 'address_en',
            'contact_address_pb'  => 'address_pb',
            'address_pb'          => 'address_pb',
            'map_token'           => 'token_key',
            'token_key'           => 'token_key',
            'map_url'             => 'map_url',
            'map_api_key'         => 'map_api',
            'map_api'             => 'map_api',
            'block_service_url'   => 'block_service',
            'block_service'       => 'block_service',
            'block_service_token' => 'block_token',
            'block_token'         => 'block_token',
            'latitude'            => 'latitude',
            'longitude'           => 'longitude',
        ];

        foreach ($data as $k => $val) {
            if (isset($keyMapping[$k])) {
                $this->updateOrCreate($keyMapping[$k], (string)$val);
            }
        }

        return $this->getContactUs();
    }

    /**
     * Get Meta Information Settings formatted for frontend form.
     */
    public function getMetaInformation(): array
    {
        $raw = $this->getAllAsArray();

        return [
            'meta_title_en'               => $raw['website_meta_title'] ?? $raw['meta_title_en'] ?? '',
            'website_meta_title'          => $raw['website_meta_title'] ?? '',
            'meta_title_pb'               => $raw['website_meta_title_pb'] ?? $raw['meta_title_pb'] ?? '',
            'website_meta_title_pb'       => $raw['website_meta_title_pb'] ?? '',
            'meta_description_en'         => $raw['website_meta_description'] ?? $raw['meta_description_en'] ?? '',
            'website_meta_description'    => $raw['website_meta_description'] ?? '',
            'meta_description_pb'         => $raw['website_meta_description_pb'] ?? $raw['meta_description_pb'] ?? '',
            'website_meta_description_pb' => $raw['website_meta_description_pb'] ?? '',
            'meta_keyword_en'             => $raw['website_meta_keywords'] ?? $raw['meta_keyword_en'] ?? '',
            'website_meta_keywords'       => $raw['website_meta_keywords'] ?? '',
            'meta_keyword_pb'             => $raw['website_meta_keywords_pb'] ?? $raw['meta_keyword_pb'] ?? '',
            'website_meta_keywords_pb'    => $raw['website_meta_keywords_pb'] ?? '',
        ];
    }

    /**
     * Update Meta Information Settings.
     */
    public function updateMetaInformation(array $data): array
    {
        $keyMapping = [
            'meta_title_en'               => 'website_meta_title',
            'website_meta_title'          => 'website_meta_title',
            'meta_title_pb'               => 'website_meta_title_pb',
            'website_meta_title_pb'       => 'website_meta_title_pb',
            'meta_description_en'         => 'website_meta_description',
            'website_meta_description'    => 'website_meta_description',
            'meta_description_pb'         => 'website_meta_description_pb',
            'website_meta_description_pb' => 'website_meta_description_pb',
            'meta_keyword_en'             => 'website_meta_keywords',
            'website_meta_keywords'       => 'website_meta_keywords',
            'meta_keyword_pb'             => 'website_meta_keywords_pb',
            'website_meta_keywords_pb'    => 'website_meta_keywords_pb',
        ];

        foreach ($data as $k => $val) {
            if (isset($keyMapping[$k])) {
                $this->updateOrCreate($keyMapping[$k], (string)$val);
            }
        }

        return $this->getMetaInformation();
    }

    /**
     * Get Additional Settings formatted for frontend form.
     */
    public function getAdditionalSettings(): array
    {
        $raw = $this->getAllAsArray();
        $fileUploadStatus = $raw['file_upload_size_status'] ?? $raw['adjust_file_size_status'] ?? '1';
        $isUploadActive = ($fileUploadStatus === '1' || $fileUploadStatus === 'active' || $fileUploadStatus === 'true' || $fileUploadStatus === true || $fileUploadStatus === 1);

        return [
            'latest_news_bg_color'    => $raw['latest_news_bg_color'] ?? '#0056b3',
            'latest_news_text_color'  => $raw['latest_news_text_color'] ?? '#ffffff',
            'file_upload_size_status' => $isUploadActive,
        ];
    }

    /**
     * Update Additional Settings.
     */
    public function updateAdditionalSettings(array $data): array
    {
        $keyMapping = [
            'latest_news_bg_color'   => 'latest_news_bg_color',
            'latest_news_text_color' => 'latest_news_text_color',
        ];

        foreach ($data as $k => $val) {
            if ($k === 'file_upload_size_status') {
                $statusVal = ($val === true || $val === '1' || $val === 'active' || $val === 1 || $val === 'true') ? '1' : '0';
                $this->updateOrCreate('file_upload_size_status', $statusVal);
                $this->updateOrCreate('adjust_file_size_status', $statusVal);
            } elseif (isset($keyMapping[$k])) {
                $this->updateOrCreate($keyMapping[$k], (string)$val);
            }
        }

        return $this->getAdditionalSettings();
    }
}