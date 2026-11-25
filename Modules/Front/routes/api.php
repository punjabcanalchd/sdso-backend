<?php

use Illuminate\Support\Facades\Route;
use Modules\Front\Http\Controllers\FooterController;
use Modules\Front\Http\Controllers\GalleryController;
use Modules\Front\Http\Controllers\HomeController;
use Modules\Front\Http\Controllers\MenuController;
use Modules\Front\Http\Controllers\NotificationController;
use Modules\Front\Http\Controllers\PageController;
use Modules\Front\Http\Controllers\TranslationController;

Route::prefix('front')->group(function () {


    Route::get('/home/{language?}', [HomeController::class, 'index'])
    ->where('language', 'en|pb');

    // Dynamic Translations
    Route::get('/translations/{language?}', [TranslationController::class, 'index'])
        ->where('language', 'en|pb');

    // Pages
    Route::get('/pages/{pageId}/{languageId}', [PageController::class, 'show']);
    Route::get('/pages/slug/{slug}/{languageId}', [PageController::class, 'showBySlug']);


    Route::get('/menus', [MenuController::class, 'index']);
    // Home SLider
    // Route::get('/sliders', [HomeController::class, 'getHomeSliders']);

    // Notifications, Announcements & Ticker
    // Route::get('/notifications', [NotificationController::class, 'index']);
    // Route::get('/announcements', [NotificationController::class, 'index']);
    // Route::get('/noticeboard-tabs', [NotificationController::class, 'tabs']);
    // Route::get('/ticker', [NotificationController::class, 'ticker']);

    // Noticeboard details 
    // Route::get('/noticeboard/{languageId?}/{id}', [NotificationController::class, 'noticeboard']);
  



    // Footer
    // Route::get('/footer', [FooterController::class, 'index']);

    // Home
    Route::get('/home', [HomeController::class, 'index']);

    // Gallery & Media
    Route::get('/gallery/{id?}', [GalleryController::class, 'showGallery']);
    Route::get('/media/{id?}', [GalleryController::class, 'showGallery']);
    Route::get('/work-gallery', [GalleryController::class, 'index']);
});
