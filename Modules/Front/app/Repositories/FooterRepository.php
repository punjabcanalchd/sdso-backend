<?php

namespace Modules\Front\Repositories;

use App\Models\Menu;
use App\Models\NoticeboardTemplate;
use App\Models\Page;
use App\Models\Setting;
use App\Models\Slider;
use App\Models\SliderImage;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Collection as SupportCollection;

class FooterRepository
{
    /**
     * Retrieve settings key-value pairs for footer configurations.
     */
    public function getSettingsByKeys(array $keys): SupportCollection
    {
        return Setting::whereIn('config_key', $keys)
            ->pluck('config_value', 'config_key');
    }

    /**
     * Retrieve active menus by their IDs with descriptions.
     */
    public function getMenusByIds(array $menuIds): Collection
    {
        if (empty($menuIds)) {
            return new Collection();
        }

        return Menu::with(['englishDescription', 'descriptions'])
            ->whereIn('menu_id', $menuIds)
            ->where('status', true)
            ->get();
    }

    /**
     * Check if a slider is active.
     */
    public function isSliderActive(int $sliderId): bool
    {
        return Slider::where('slider_id', $sliderId)
            ->where('status', true)
            ->exists();
    }

    /**
     * Get active images for a given slider ID ordered by sort_order.
     */
    public function getSliderImages(int $sliderId): Collection
    {
        return SliderImage::where('slider_id', $sliderId)
            ->where('status', true)
            ->orderBy('sort_order', 'asc')
            ->get();
    }

    /**
     * Get the latest update timestamp across all CMS content, settings, and activity logs.
     */
    public function getLatestWebsiteUpdatedAt(): ?string
    {
        $timestamps = [];

        // 1. Spatie Activity Log (tracks all CMS actions, logins, and updates)
        try {
            $ts = \Spatie\Activitylog\Models\Activity::max('created_at');
            if ($ts) {
                $timestamps[] = $ts;
            }
        } catch (\Throwable $e) {}

        // 2. Settings table explicit last_updated key
        try {
            $ts = Setting::where('config_key', 'website_last_updated')->value('config_value');
            if ($ts) {
                $timestamps[] = $ts;
            }
        } catch (\Throwable $e) {}

        // 3. Notices & Circulars
        try {
            $ts = NoticeboardTemplate::max('updated_at');
            if ($ts) {
                $timestamps[] = $ts;
            }
        } catch (\Throwable $e) {}

        // 4. Pages
        try {
            $ts = Page::max('updated_at');
            if ($ts) {
                $timestamps[] = $ts;
            }
        } catch (\Throwable $e) {}

        // 5. Menus
        try {
            $ts = Menu::max('updated_at');
            if ($ts) {
                $timestamps[] = $ts;
            }
        } catch (\Throwable $e) {}

        // 6. Sliders & Banners
        try {
            $ts = SliderImage::max('updated_at');
            if ($ts) {
                $timestamps[] = $ts;
            }
        } catch (\Throwable $e) {}

        if (empty($timestamps)) {
            return null;
        }

        $latest = null;
        foreach ($timestamps as $ts) {
            $c = \Carbon\Carbon::parse($ts);
            if ($latest === null || $c->gt($latest)) {
                $latest = $c;
            }
        }

        return $latest ? $latest->toDateTimeString() : null;
    }

    /**
     * Get the latest update timestamp from noticeboard templates.
     */
    public function getLatestNoticeUpdatedAt(): ?string
    {
        return NoticeboardTemplate::orderBy('updated_at', 'desc')->value('updated_at');
    }

    /**
     * Get the latest update timestamp from pages.
     */
    public function getLatestPageUpdatedAt(): ?string
    {
        return Page::orderBy('updated_at', 'desc')->value('updated_at');
    }

    /**
     * Get visitor count from settings table.
     */
    public function getDbVisitorCount(): int
    {
        return (int) Setting::where('config_key', 'total_visitors')->value('config_value');
    }
}

