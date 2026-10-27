<?php

use Illuminate\Support\Facades\Route;
use Modules\Front\Http\Controllers\HomeController;
use Modules\Front\Http\Controllers\MenuController;
use Modules\Front\Http\Controllers\PageController;


Route::prefix('front')->group(function () {
    Route::get('/menus', [MenuController::class, 'index']);
    Route::get('/notifications', [HomeController::class, 'notifications']);
    Route::get('/announcements', [HomeController::class, 'notifications']);
    Route::get('/noticeboard-tabs', [HomeController::class, 'noticeBoardTabs']);
    Route::get('/pages/{pageId}/{languageId}', [PageController::class,'show']);
    Route::get('/pages/slug/{slug}/{languageId}', [PageController::class,'showBySlug']);
    // Route::get('/home', [HomeController::class, 'index']);
});
