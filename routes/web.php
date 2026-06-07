<?php

use App\Http\Controllers\DiscordAuthController;
use Illuminate\Support\Facades\Route;

Route::get('/login/discord', [DiscordAuthController::class, 'redirectToDiscord']);
Route::get('/callback/discord', [DiscordAuthController::class, 'handleDiscordCallback']);

Route::get('/', function () {
    return view('landing');
});
