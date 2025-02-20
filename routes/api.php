<?php

use App\Http\Controllers\Api\EventController;
use App\Http\Controllers\Api\TrackController;
use App\Http\Controllers\UserController;
use App\Http\Middleware\VerifyApiKey;
use Illuminate\Support\Facades\Route;


Route::middleware('auth:sanctum')->group(function () {

    Route::get('/user', [UserController::class, 'show']);
    Route::put('/user', [UserController::class, 'update']);
    Route::post('/user/logout', [UserController::class, 'logout']);

    Route::post('/tracks', [TrackController::class, 'store']);
    Route::put('/tracks/{id}', [TrackController::class, 'update']);

    Route::post('/events', [EventController::class, 'store']);
});



Route::middleware([VerifyApiKey::class])->group(function () {
    Route::get('/tracks', [TrackController::class, 'index']);

    Route::get('/events', [EventController::class, 'index']);
    Route::get('/events/{id}', [EventController::class, 'show']);
    Route::get('events/{id}/results', [EventController::class, 'getEventResults']);
});
