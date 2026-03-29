<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\TrackableItemController;
use App\Http\Controllers\ActionLogController;
use App\Http\Controllers\PlantLookupController;
use App\Http\Controllers\SettingsController;

// ── Auth (public) ─────────────────────────────────────────────────────────────
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// ── Everything else requires authentication ───────────────────────────────────
Route::middleware('auth')->group(function () {

    Route::redirect('/', '/dashboard');
    Route::get('/dashboard', [TrackableItemController::class, 'dashboard'])->name('dashboard');
    Route::resource('items', TrackableItemController::class);
    Route::post('/items/{item}/action', [ActionLogController::class, 'store'])->name('items.action');

    // Settings
    Route::get('/settings',  [SettingsController::class, 'index'])->name('settings.index');
    Route::post('/settings', [SettingsController::class, 'update'])->name('settings.update');

    // Plant lookup via Perenual / PlantNet APIs
    Route::get('/plants/search',      [PlantLookupController::class, 'search'])->name('plants.search');
    Route::get('/plants/care/{id}',   [PlantLookupController::class, 'care'])->name('plants.care');
    Route::post('/plants/identify',   [PlantLookupController::class, 'identify'])->name('plants.identify');

});
