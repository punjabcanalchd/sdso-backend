<?php

use Illuminate\Support\Facades\Route;
use Modules\Front\Http\Controllers\HomeController;
use Modules\Front\Http\Controllers\MenuController;
use Modules\Front\Http\Controllers\NoticeboardController;
use Modules\Front\Http\Controllers\NotificationController;
use Modules\Front\Http\Controllers\PageController;

Route::prefix('front')->group(function () {
    Route::get('/menus', [MenuController::class, 'index']);

    // Notifications, Announcements & Ticker
    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::get('/announcements', [NotificationController::class, 'index']);
    Route::get('/noticeboard-tabs', [NotificationController::class, 'tabs']);
    Route::get('/ticker', [NotificationController::class, 'ticker']);

    // Noticeboard details (Teammate's feature)
    Route::get('/noticeboard/{id}/{languageId}', [NoticeboardController::class, 'show']);

    // Pages
    Route::get('/pages/{pageId}/{languageId}', [PageController::class, 'show']);
    Route::get('/pages/slug/{slug}/{languageId}', [PageController::class, 'showBySlug']);

    // Home
    Route::get('/home', [HomeController::class, 'index']);
});
