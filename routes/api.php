<?php

use App\Http\Controllers\Api\EventController;
use App\Http\Middleware\VerifyApiKey;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::middleware([VerifyApiKey::class])->group(function () {
    Route::apiResource('events', EventController::class);
    Route::get('events/{id}/results', [EventController::class, 'getEventResults']);
});

