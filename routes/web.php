<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\DiscordAuthController;

Route::get('/login/discord', [DiscordAuthController::class, 'redirectToDiscord']);
Route::get('/callback/discord', [DiscordAuthController::class, 'handleDiscordCallback']);

Route::get('/', function () {
    return view('welcome');
});
