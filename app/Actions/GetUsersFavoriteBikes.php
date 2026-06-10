<?php

namespace App\Actions;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class GetUsersFavoriteBikes
{
    public function handle(Collection $userIds): Collection
    {
        return DB::table('lap_times')
            ->join('users as u', 'u.guid', '=', 'lap_times.player_guid')
            ->join('bikes', 'bikes.id', '=', 'lap_times.bike_id')
            ->whereIn('u.id', $userIds)
            ->select('u.id as user_id', 'bikes.name as bike_name', DB::raw('COUNT(*) as cnt'))
            ->groupBy('u.id', 'bikes.name')
            ->get()
            ->groupBy('user_id')
            ->map(fn ($bikes) => $bikes->sortByDesc('cnt')->first()->bike_name);
    }
}
