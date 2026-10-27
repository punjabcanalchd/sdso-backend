<?php

use Illuminate\Support\Facades\Route;
use Modules\Front\Http\Controllers\HomeController;
use Modules\Front\Http\Controllers\MenuController;

Route::prefix('front')->group(function () {
    Route::get('/menus', [MenuController::class, 'index']);
    Route::get('/notifications', [HomeController::class, 'notifications']);
    Route::get('/announcements', [HomeController::class, 'notifications']);
    Route::get('/noticeboard-tabs', [HomeController::class, 'noticeBoardTabs']);
});
