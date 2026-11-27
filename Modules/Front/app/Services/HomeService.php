<?php

namespace Modules\Front\Services;

use Modules\Front\Services\SliderService;
use Modules\Front\Services\NotificationService;
use Modules\Front\Services\MenuService;
use Modules\Front\Services\FooterService;
use Modules\Front\Services\SettingService;
use Modules\Front\Services\PageService;
 

class HomeService
{
    public function __construct(
        private SliderService $sliderService,
        private NotificationService $notificationService,
        private MenuService $menuService,
        private FooterService $footerService,
        private SettingService $settingService,
        private PageService $pageService,
    ) {}

    public function getHomeData(string $language = 'en'): array
    {
        // Convert language code into language ID
        $languageId = match ($language) {
            'en' => 1,
            'pb' => 2,
            default => 1,
        };

        return [
            'settings'        => $this->settingService->getHomeSettings($languageId, $language),
            'menus'           => $this->menuService->getActiveMenus($languageId),
            'sliders'         => $this->sliderService->getHomeSliders(),
            'announcements'   => $this->notificationService->getNotifications(10),
            'noticeboardTabs' => $this->notificationService->getNoticeBoardTabs(),
            'ticker'          => $this->notificationService->getTickerItems(10),
            'footer'          => $this->footerService->getFooterData($languageId),            
            'importantLinks' => $this->notificationService->getImportantLinkOnHomePage($languageId),
            'aboutUs'        => $this->$pageService->getIndexTextPage($languageId),
            // 'ticker' => $this->notificationService->getTickerItems(10),
            // 'footer' => $this->footerService->getFooterData($languageId),
        ];
    }
}