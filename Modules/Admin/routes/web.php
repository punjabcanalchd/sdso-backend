<?php

use Illuminate\Support\Facades\Route;
use Modules\Admin\Http\Controllers\AdminController;

// Route::middleware(['auth', 'verified'])->group(function () {
//     Route::resource('admins', AdminController::class)->names('admin');
// });

Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [AdminController::class, 'index'])->name('index');
});