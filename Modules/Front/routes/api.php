<?php

use Illuminate\Support\Facades\Route;
// use Modules\Front\Http\Controllers\HomeController;
use Modules\Front\Http\Controllers\MenuController;

Route::prefix('front')->group(function () {

    Route::get('/menus', [MenuController::class, 'index']);
    // Route::get('/home', [HomeController::class, 'index']);
});
