<?php

namespace Modules\Front\Services;

use App\Models\Menu;
use App\Models\NoticeboardTemplate;
use App\Models\Page;
use App\Models\Setting;
use App\Models\Slider;
use App\Models\SliderImage;
use Carbon\Carbon;

class FooterService
{
    /**
     * Get all structured footer data in a single call.
     */
    public function getFooterData(?int $languageId = 1): array
    {
        $lang = ($languageId === 2) ? 'pb' : 'en';

        return [
            'columns' => $this->getFooterColumns($languageId, $lang),
            'top_links' => $this->getTopFooterLinks($lang),
            'top_links_title' => ($lang === 'pb') ? 'ਨੀਤੀਆਂ ਅਤੇ ਸਹਾਇਤਾ' : 'Policies & Help',
            'partner_logos' => $this->getPartnerLogos(),
            'copyright' => $this->getCopyrightInfo($lang),
            'stats' => $this->getFooterStats(),
        ];
    }

    /**
     * Retrieve 3 configurable category columns from settings and menus.
     */
    private function getFooterColumns(int $languageId, string $lang): array
    {
        $settings = Setting::whereIn('config_key', [
            'footer_cat1_title', 'footer_cat1_title_pb', 'footer_cat1_menus',
            'footer_cat2_title', 'footer_cat2_title_pb', 'footer_cat2_menus',
            'footer_cat3_title', 'footer_cat3_title_pb', 'footer_cat3_menus',
        ])->pluck('config_value', 'config_key');

        $columns = [];

        for ($i = 1; $i <= 3; $i++) {
            $titleKey = ($lang === 'pb') ? "footer_cat{$i}_title_pb" : "footer_cat{$i}_title";
            $fallbackTitleKey = "footer_cat{$i}_title";
            $title = $settings->get($titleKey) ?: $settings->get($fallbackTitleKey);

            $menusKey = "footer_cat{$i}_menus";
            $rawMenus = $settings->get($menusKey);

            $menuIds = [];
            if ($rawMenus) {
                $unserialized = @unserialize($rawMenus);
                if (is_array($unserialized)) {
                    $menuIds = array_map('intval', $unserialized);
                }
            }

            $links = [];
            if (!empty($menuIds)) {
                $menus = Menu::with(['englishDescription', 'descriptions'])
                    ->whereIn('menu_id', $menuIds)
                    ->where('status', true)
                    ->get();

                // Sort as ordered in $menuIds
                $menus = $menus->sortBy(fn($m) => array_search($m->menu_id, $menuIds));

                foreach ($menus as $m) {
                    $desc = ($languageId === 2)
                        ? ($m->descriptions->firstWhere('language_id', 2)?->message ?? $m->englishDescription?->message ?? $m->title)
                        : ($m->englishDescription?->message ?? $m->title);

                    $resolved = $this->resolveMenuLink($m);

                    $links[] = [
                        'id' => $m->menu_id,
                        'label' => $desc,
                        'url' => $resolved['url'],
                        'external' => $resolved['external'],
                    ];
                }
            }

            if ($title || !empty($links)) {
                $columns[] = [
                    'title' => $title ?: "Section {$i}",
                    'links' => $links,
                ];
            }
        }

        return $columns;
    }

    /**
     * Retrieve links from Slider 6 (Footer Top Slider) or GIGW default policies.
     */
    private function getTopFooterLinks(string $lang): array
    {
        $slider = Slider::where('slider_id', 6)->where('status', true)->first();

        if ($slider) {
            $images = SliderImage::where('slider_id', 6)
                ->where('status', true)
                ->orderBy('sort_order', 'asc')
                ->get();

            if ($images->isNotEmpty()) {
                return $images->map(function ($item) use ($lang) {
                    $title = ($lang === 'pb' && !empty($item->title_pb)) ? $item->title_pb : $item->title;
                    $url = $this->resolveLinkString($item->link);

                    return [
                        'label' => $title,
                        'url' => $url['url'],
                        'external' => $url['external'],
                    ];
                })->toArray();
            }
        }

        // Standard GIGW fallback policy links
        return [
            ['label' => 'Website Policies', 'url' => '/website-policies', 'external' => false],
            ['label' => 'Help / User Manual', 'url' => '/noticeboard/user-manual', 'external' => false],
            ['label' => 'Contact Us', 'url' => '/contact', 'external' => false],
            ['label' => 'Sitemap', 'url' => '/sitemap', 'external' => false],
        ];
    }

    /**
     * Retrieve partner logos (Slider 3: Footer Section).
     * Reuses locally bundled govt logos as reliable fallbacks per Ponytail rule.
     */
    private function getPartnerLogos(): array
    {
        $slider = Slider::where('slider_id', 3)->where('status', true)->first();

        // Local asset map for zero 3rd-party risk
        $localMap = [
            'data portal' => ['img' => 'assets/images/front/footer/data-gov-logo.png', 'name' => 'Data Gov'],
            'digital india' => ['img' => 'assets/images/front/footer/digital-india-logo.png', 'name' => 'Digital India'],
            'meity' => ['img' => 'assets/images/front/footer/Meity_logo.png', 'name' => 'MeitY'],
            'mygov' => ['img' => 'assets/images/front/footer/mygov-footer-logo.png', 'name' => 'MyGov'],
            'pmindia' => ['img' => 'assets/images/front/footer/pm-india-logo.png', 'name' => 'PM India'],
            'national portal of india' => ['img' => 'assets/images/front/footer/india-gov-logo.png', 'name' => 'India.gov.in'],
            'national informatics centre' => ['img' => 'assets/images/front/footer/Meity_logo.png', 'name' => 'NIC'],
        ];

        if ($slider) {
            $items = SliderImage::where('slider_id', 3)
                ->where('status', true)
                ->orderBy('sort_order', 'asc')
                ->get();

            if ($items->isNotEmpty()) {
                $logos = [];
                foreach ($items as $img) {
                    $key = strtolower(trim($img->title));
                    $local = $localMap[$key] ?? null;

                    $imageUrl = !empty($img->image) ? asset($img->image) : ($local['img'] ?? 'assets/images/front/footer/india-gov-logo.png');
                    $name = !empty($img->title) ? $img->title : ($local['name'] ?? 'Govt of India');

                    $logos[] = [
                        'name' => $name,
                        'imageUrl' => $imageUrl,
                        'linkUrl' => $img->link ?: '#',
                    ];
                }
                return $logos;
            }
        }

        // Default local logos list if slider empty
        return [
            ['name' => 'Data Gov', 'imageUrl' => 'assets/images/front/footer/data-gov-logo.png', 'linkUrl' => 'https://data.gov.in/'],
            ['name' => 'Digital India', 'imageUrl' => 'assets/images/front/footer/digital-india-logo.png', 'linkUrl' => 'https://www.digitalindia.gov.in/'],
            ['name' => 'MeitY', 'imageUrl' => 'assets/images/front/footer/Meity_logo.png', 'linkUrl' => 'https://www.meity.gov.in/'],
            ['name' => 'MyGov', 'imageUrl' => 'assets/images/front/footer/mygov-footer-logo.png', 'linkUrl' => 'https://www.mygov.in/'],
            ['name' => 'PM India', 'imageUrl' => 'assets/images/front/footer/pm-india-logo.png', 'linkUrl' => 'https://www.pmindia.gov.in/'],
            ['name' => 'India.gov.in', 'imageUrl' => 'assets/images/front/footer/india-gov-logo.png', 'linkUrl' => 'https://www.india.gov.in/'],
        ];
    }

    /**
     * Retrieve localized copyright and developer credits.
     */
    private function getCopyrightInfo(string $lang): array
    {
        $settings = Setting::whereIn('config_key', [
            'website_copyright1', 'website_copyright1_pb',
            'website_copyright2', 'website_copyright2_pb',
        ])->pluck('config_value', 'config_key');

        $line1 = ($lang === 'pb')
            ? ($settings->get('website_copyright1_pb') ?: $settings->get('website_copyright1'))
            : $settings->get('website_copyright1');

        $line2 = ($lang === 'pb')
            ? ($settings->get('website_copyright2_pb') ?: $settings->get('website_copyright2'))
            : $settings->get('website_copyright2');

        return [
            'year' => date('Y'),
            'organization' => $line1 ?: 'State Dam Safety Organization',
            'developed_by' => $line2 ?: 'Website Designed and Developed by National Informatics Centre (NIC)',
        ];
    }

    /**
     * Retrieve visitor count and dynamic last updated date for GIGW compliance.
     */
    private function getFooterStats(): array
    {
        // 1. Last Updated Date: from latest updated notice or page in database
        $latestNotice = NoticeboardTemplate::orderBy('updated_at', 'desc')->value('updated_at');
        $latestPage = Page::orderBy('updated_at', 'desc')->value('updated_at');

        $lastUpdatedCarbon = null;
        if ($latestNotice && $latestPage) {
            $lastUpdatedCarbon = Carbon::parse($latestNotice)->gt(Carbon::parse($latestPage))
                ? Carbon::parse($latestNotice)
                : Carbon::parse($latestPage);
        } elseif ($latestNotice) {
            $lastUpdatedCarbon = Carbon::parse($latestNotice);
        } elseif ($latestPage) {
            $lastUpdatedCarbon = Carbon::parse($latestPage);
        }

        $lastUpdatedFormatted = $lastUpdatedCarbon
            ? $lastUpdatedCarbon->format('d-m-Y H:i:s')
            : date('d-m-Y H:i:s');

        // 2. Total Visitors: base offset + live count
        $baseVisitors = 19200;
        $dbVisitorCount = (int) Setting::where('config_key', 'total_visitors')->value('config_value');
        $totalVisitors = $baseVisitors + $dbVisitorCount;

        return [
            'totalVisitors' => $totalVisitors,
            'lastUpdated' => $lastUpdatedFormatted,
        ];
    }

    /**
     * Helper to resolve Menu link into Angular route or external URL.
     */
    private function resolveMenuLink(Menu $m): array
    {
        if (!empty($m->page_id)) {
            return ['url' => "/pages/{$m->page_id}/1", 'external' => false];
        }

        if (!empty($m->external_link)) {
            return $this->resolveLinkString($m->external_link);
        }

        if (!empty($m->url)) {
            return $this->resolveLinkString($m->url);
        }

        return ['url' => '/', 'external' => false];
    }

    /**
     * Helper to resolve arbitrary link string.
     */
    private function resolveLinkString(?string $link): array
    {
        if (empty($link)) {
            return ['url' => '/', 'external' => false];
        }

        $trimmed = trim($link);

        if (preg_match('/^https?:\/\//i', $trimmed)) {
            return ['url' => $trimmed, 'external' => true];
        }

        if (str_ends_with(strtolower($trimmed), '.pdf')) {
            $fileUrl = str_starts_with($trimmed, '/') ? $trimmed : '/' . $trimmed;
            return ['url' => $fileUrl, 'external' => true];
        }

        $route = str_starts_with($trimmed, '/') ? $trimmed : '/' . $trimmed;
        return ['url' => $route, 'external' => false];
    }
}
