<?php

use App\Http\Controllers\PushNotificationController;
use App\Http\Controllers\StoreMediaController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('EdgePlayground');
})->name('home');

Route::get('/welcome', function () {
    return Inertia::render('Welcome');
})->name('welcome');


Route::get('/camera', function () {
    return Inertia::render('Camera/GetPhoto');
})->name('camera');

Route::get('/network', function () {
    return Inertia::render('Network');
})->name('network');

Route::get('/scanner', function () {
    return Inertia::render('Scanner');
})->name('scanner');

Route::get('/alert', function () {
    return Inertia::render('Dialog/Alert');
})->name('alert');

Route::get('/microphone', function () {
    return Inertia::render('Microphone');
})->name('microphone');

Route::get('/video', function () {
    return Inertia::render('Camera/Video');
})->name('video');

Route::get('/gallery', function () {
    return Inertia::render('Camera/Gallery');
})->name('gallery');

Route::get('/biometrics', function () {
    return Inertia::render('Biometrics');
})->name('biometrics');

Route::get('/geolocation', function () {
    return Inertia::render('Geolocation');
})->name('geolocation');

Route::get('/device', function () {
    return Inertia::render('Device');
})->name('device');

Route::get('/haptics', function () {
    return Inertia::render('Haptics');
})->name('haptics');

Route::get('/browser', function () {
    return Inertia::render('Browser');
})->name('browser');

Route::get('/secure-storage', function () {
    return Inertia::render('SecureStorage');
})->name('secure-storage');

Route::get('/push-notifications', function () {
    return Inertia::render('PushNotifications');
})->name('push-notifications');

Route::get('/toast', function () {
    return Inertia::render('Dialog/Toast');
})->name('toast');

Route::get('/bluetooth', function () {
    return Inertia::render('Bluetooth');
})->name('bluetooth');

Route::get('/ar', function () {
    return Inertia::render('AR');
})->name('ar');



// Media API routes
Route::post('api/camera/store-photo', [StoreMediaController::class, 'storePhoto']);
Route::post('api/camera/store-gallery-photo', [StoreMediaController::class, 'storeGalleryPhoto']);
Route::post('api/audio/store-audio', [StoreMediaController::class, 'storeAudio'])->name('store-audio');
Route::post('api/video/store-video', [StoreMediaController::class, 'storeVideo'])->name('store-video');
Route::post('api/gallery/store-media', [StoreMediaController::class, 'storeGalleryMedia'])->name('store-gallery-media');
Route::post('api/media/{directory}', [StoreMediaController::class, 'getMediaFromDirectory'])->name('media-directory');
Route::delete('api/audio/recordings/{filename}', [StoreMediaController::class, 'deleteAudio'])->name('delete-audio');
Route::delete('api/video/recordings/{filename}', [StoreMediaController::class, 'deleteVideo'])->name('delete-video');
Route::post('api/gallery/media', [StoreMediaController::class, 'deleteGalleryMedia'])->name('delete-gallery-media');
Route::post('/api/send-push-notification', [PushNotificationController::class, 'sendPushNotification'])->name('send-push-notification');

