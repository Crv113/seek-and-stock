<?php

namespace App\Actions;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class GetAnonymousPlayersParticipationCounts
{
    public function handle(): Collection
    {
        return DB::table('lap_times')
            ->join('anonymous_users as au', 'au.guid', '=', 'lap_times.player_guid')
            ->whereNull('au.user_id')
            ->select('au.id as anonymous_user_id', DB::raw('COUNT(DISTINCT lap_times.event_id) as cnt'))
            ->groupBy('au.id')
            ->pluck('cnt', 'anonymous_user_id');
    }
}
