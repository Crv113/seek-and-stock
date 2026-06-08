<?php

namespace App\Actions;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class GetAnonymousVictoryCounts
{
    public function handle(Collection $anonymousUserIds): Collection
    {
        $bestTimesPerEvent = DB::table('lap_times')
            ->join('events', 'events.id', '=', 'lap_times.event_id')
            ->where('events.ending_date', '<=', now())
            ->select('lap_times.event_id', DB::raw('MIN(lap_times.lap_time) as best_time'))
            ->groupBy('lap_times.event_id');

        $winners = DB::table('lap_times as lt')
            ->joinSub($bestTimesPerEvent, 'best', function ($join) {
                $join->on('lt.event_id', '=', 'best.event_id')
                    ->on('lt.lap_time', '=', 'best.best_time');
            })
            ->select('lt.event_id', DB::raw('MIN(lt.id) as winning_lap_id'))
            ->groupBy('lt.event_id');

        return DB::table('lap_times as lt')
            ->joinSub($winners, 'w', 'lt.id', '=', 'w.winning_lap_id')
            ->join('anonymous_users as au', 'au.guid', '=', 'lt.player_guid')
            ->whereIn('au.id', $anonymousUserIds)
            ->select('au.id', DB::raw('COUNT(*) as cnt'))
            ->groupBy('au.id')
            ->pluck('cnt', 'au.id');
    }
}
