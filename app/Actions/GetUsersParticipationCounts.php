<?php

namespace App\Actions;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class GetUsersParticipationCounts
{
    public function handle(Collection $userIds): Collection
    {
        return DB::table('lap_times')
            ->join('users as u', 'u.guid', '=', 'lap_times.player_guid')
            ->whereIn('u.id', $userIds)
            ->select('u.id', DB::raw('COUNT(DISTINCT lap_times.event_id) as cnt'))
            ->groupBy('u.id')
            ->pluck('cnt', 'u.id');
    }
}
