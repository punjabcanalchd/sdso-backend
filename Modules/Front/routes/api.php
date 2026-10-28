<?php

use Illuminate\Support\Facades\Route;
use Modules\Front\Http\Controllers\HomeController;
use Modules\Front\Http\Controllers\MenuController;
use Modules\Front\Http\Controllers\PageController;
use Modules\Front\Http\Controllers\NoticeboardController;




Route::prefix('front')->group(function () {
    Route::get('/menus', [MenuController::class, 'index']);
    Route::get('/notifications', [HomeController::class, 'notifications']);
    Route::get('/announcements', [HomeController::class, 'notifications']);
    Route::get('/noticeboard-tabs', [HomeController::class, 'noticeBoardTabs']);
    Route::get('/pages/{pageId}/{languageId}', [PageController::class,'show']);
    Route::get('/pages/slug/{slug}/{languageId}', [PageController::class,'showBySlug']);
    Route::get('/noticeboard/{id}/{languageId}',[NoticeboardController::class, 'show']);


    // Route::get('/home', [HomeController::class, 'index']);
});
