<?php

use Illuminate\Support\Facades\Route;
// use Modules\Front\Http\Controllers\FooterController;
use Modules\Front\Http\Controllers\HomeController;
// use Modules\Front\Http\Controllers\MenuController;
// use Modules\Front\Http\Controllers\NotificationController;
use Modules\Front\Http\Controllers\PageController;

Route::prefix('front')->group(function () {


    Route::get('/home/{language?}', [HomeController::class, 'index'])
    ->where('language', 'en|pb');

    // Pages
    Route::get('/pages/{pageId}/{languageId}', [PageController::class, 'show']);
    Route::get('/pages/slug/{slug}/{languageId}', [PageController::class, 'showBySlug']);


    // Route::get('/menus', [MenuController::class, 'index']);
    // Home SLider
    // Route::get('/sliders', [HomeController::class, 'getHomeSliders']);

    // Notifications, Announcements & Ticker
    // Route::get('/notifications', [NotificationController::class, 'index']);
    // Route::get('/announcements', [NotificationController::class, 'index']);
    // Route::get('/noticeboard-tabs', [NotificationController::class, 'tabs']);
    // Route::get('/ticker', [NotificationController::class, 'ticker']);

    // Noticeboard details (Teammate's feature)
    // Route::get('/noticeboard/{languageId?}/{id}', [NotificationController::class, 'noticeboard']);
  


    // Footer
    // Route::get('/footer', [FooterController::class, 'index']);

    // Home
});
