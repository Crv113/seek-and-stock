<?php

namespace App\Actions;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class GetAnonymousPlayersFavoriteBikes
{
    public function handle(): Collection
    {
        return DB::table('lap_times as lt')
            ->join('anonymous_users as au', 'au.guid', '=', 'lt.player_guid')
            ->join('bikes', 'bikes.id', '=', 'lt.bike_id')
            ->whereNull('au.user_id')
            ->select('au.id as anonymous_user_id', 'bikes.name as bike_name', DB::raw('COUNT(*) as cnt'))
            ->groupBy('au.id', 'bikes.name')
            ->get()
            ->groupBy('anonymous_user_id')
            ->map(fn ($bikes) => $bikes->sortByDesc('cnt')->first()->bike_name);
    }
}
