<?php

namespace Modules\Front\Services;

use Modules\Front\Repositories\SettingRepository;

class SettingService
{
    public function __construct(
        protected SettingRepository $settingRepository
    ) {}

    /**
     * Get structured branding, logo, and header settings for the website.
     */
    public function getHomeSettings(?int $languageId = 1, string $language = 'en'): array
    {
        $lang = ($languageId === 2 || strtolower($language) === 'pb') ? 'pb' : 'en';
        $raw = $this->settingRepository->getHeaderAndBrandingSettings();

        // 1. Resolve Primary Logo
        $logoEn = $raw->get('website_logo') ?: $raw->get('logo_english');
        $logoPb = $raw->get('website_logo_pb') ?: $raw->get('logo_punjabi');
        $primaryLogo = ($lang === 'pb' && !empty($logoPb)) ? $logoPb : ($logoEn ?: $logoPb);

        // 2. Resolve Secondary Logo
        $secondaryEn = $raw->get('secondary_logo_english') ?: $raw->get('website_secondary_logo');
        $secondaryPb = $raw->get('secondary_logo_punjabi') ?: $raw->get('website_secondary_logo_pb');
        $secondaryLogo = ($lang === 'pb' && !empty($secondaryPb)) ? $secondaryPb : ($secondaryEn ?: $secondaryPb);

        // 3. Resolve Headings
        $headingEn = $raw->get('website_logo_heading_en') ?: $raw->get('logo_heading_en', '');
        $headingPb = $raw->get('website_logo_heading_pb') ?: $raw->get('logo_heading_pb', '');
        $subHeadingEn = $raw->get('website_logo_sub_heading_en') ?: $raw->get('logo_sub_heading_en', '');
        $subHeadingPb = $raw->get('website_logo_sub_heading_pb') ?: $raw->get('logo_sub_heading_pb', '');

        $currentHeading = ($lang === 'pb' && !empty($headingPb)) ? $headingPb : ($headingEn ?: $headingPb);
        $currentSubHeading = ($lang === 'pb' && !empty($subHeadingPb)) ? $subHeadingPb : ($subHeadingEn ?: $subHeadingPb);

        // 4. Headline for Accessibility Bar (e.g. "State Dam Safety Organization (SDSO) | Dam Safety Organisation")
        $websiteName = $raw->get('website_name', 'State Dam Safety Organization');
        if (!empty($currentHeading) && !empty($currentSubHeading)) {
            $headline = "{$currentHeading} | {$currentSubHeading}";
        } elseif (!empty($currentHeading)) {
            $headline = $currentHeading;
        } else {
            $headline = $websiteName;
        }

        // 5. Government Officials Toggle
        $officialsStatus = $raw->get('toogle_status') ?: $raw->get('government_officials_toggle', '1');
        $isOfficialsActive = in_array((string)$officialsStatus, ['1', 'active', 'true'], true);

        return [
            'logo'                => $this->resolveMediaUrl($primaryLogo),
            'logo_raw'            => $primaryLogo,
            'secondary_logo'      => $this->resolveMediaUrl($secondaryLogo),
            'logo_heading'        => $currentHeading,
            'logo_heading_en'     => $headingEn,
            'logo_heading_pb'     => $headingPb,
            'logo_sub_heading'    => $currentSubHeading,
            'logo_sub_heading_en' => $subHeadingEn,
            'logo_sub_heading_pb' => $subHeadingPb,
            'website_name'        => $websiteName,
            'headline'            => $headline,
            'site_icon'           => $this->resolveMediaUrl($raw->get('website_icon') ?: $raw->get('site_icon')),
            'officials_enabled'   => $isOfficialsActive,
            'chief_minister'      => [
                'logo'        => $this->resolveMediaUrl($raw->get('chief_minister_logo')),
                'heading'     => ($lang === 'pb' && $raw->get('chief_minister_logo_heading_pb')) ? $raw->get('chief_minister_logo_heading_pb') : $raw->get('chief_minister_logo_heading_en', ''),
                'sub_heading' => ($lang === 'pb' && $raw->get('chief_minister_logo_sub_heading_pb')) ? $raw->get('chief_minister_logo_sub_heading_pb') : $raw->get('chief_minister_logo_sub_heading_en', ''),
            ],
            'cabinet_minister'    => [
                'logo'        => $this->resolveMediaUrl($raw->get('cabinet_minister_logo')),
                'heading'     => ($lang === 'pb' && $raw->get('cabinet_minister_logo_heading_pb')) ? $raw->get('cabinet_minister_logo_heading_pb') : $raw->get('cabinet_minister_logo_heading_en', ''),
                'sub_heading' => ($lang === 'pb' && $raw->get('cabinet_minister_logo_sub_heading_pb')) ? $raw->get('cabinet_minister_logo_sub_heading_pb') : $raw->get('cabinet_minister_logo_sub_heading_en', ''),
            ],
            'raw'                 => $raw->toArray(),
        ];
    }

    /**
     * Resolve public URL for an uploaded media file path.
     */
    private function resolveMediaUrl(?string $path): ?string
    {
        if (empty($path)) {
            return null;
        }

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://') || str_starts_with($path, 'blob:')) {
            return $path;
        }

        $clean = ltrim($path, '/');
        if (str_starts_with($clean, 'uploads/')) {
            return asset($clean);
        }

        return asset('uploads/' . $clean);
    }
}
