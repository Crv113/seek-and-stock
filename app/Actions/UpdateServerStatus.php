<?php

namespace App\Actions;

use Illuminate\Support\Facades\Cache;

class UpdateServerStatus
{
    public function handle(int $playersOnline): void
    {
        Cache::put('server_status:players_online', $playersOnline, now()->addSeconds(90));
    }
}
