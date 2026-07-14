<?php

use App\Http\Controllers\Api\AnonymousPlayerController;
use App\Http\Controllers\Api\EventController;
use App\Http\Controllers\Api\LapTimeController;
use App\Http\Controllers\Api\ServerStatusController;
use App\Http\Controllers\Api\TrackController;
use App\Http\Controllers\Api\UserController;
use App\Http\Middleware\RoleMiddleware;
use App\Http\Middleware\VerifyApiKey;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {

    Route::get('/me', [UserController::class, 'me']);
    Route::put('/user', [UserController::class, 'update']);
    Route::post('/user/logout', [UserController::class, 'logout']);

    Route::put('/tracks/{id}', [TrackController::class, 'update']);

    //    Route::post('/events/{id}/register', [EventUserController::class, 'register']);
    //    Route::post('/events/{id}/unregister', [EventUserController::class, 'unregister']);

    Route::middleware([RoleMiddleware::class.':admin'])->group(function () {
        Route::post('/tracks', [TrackController::class, 'store']);
        Route::post('/events', [EventController::class, 'store']);
    });
});

Route::middleware([VerifyApiKey::class])->group(function () {
    Route::get('/tracks', [TrackController::class, 'index']);
    Route::get('/tracks/{track}', [TrackController::class, 'show']);
    Route::get('/tracks/{track}/results', [TrackController::class, 'results']);
    Route::post('/laptimes', [LapTimeController::class, 'store']);

    Route::get('/events', [EventController::class, 'index']);
    Route::get('/events/{event}', [EventController::class, 'show']);
    Route::get('/events/{event}/users', [EventController::class, 'listUsersGuid']);
    Route::get('events/{id}/results', [EventController::class, 'getEventResults']);
    Route::get('/events/{event}/categories', [EventController::class, 'getEventCategories']);

    Route::get('/users', [UserController::class, 'index']);
    Route::get('/users/{user}', [UserController::class, 'show']);

    Route::get('/players', [AnonymousPlayerController::class, 'index']);
    Route::get('/players/{anonymousUser}', [AnonymousPlayerController::class, 'show']);

    Route::post('/server-status', [ServerStatusController::class, 'update']);
    Route::get('/server-status', [ServerStatusController::class, 'show']);

});
