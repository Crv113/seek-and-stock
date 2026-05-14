<?php

namespace App\Actions;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class GetUsersVictoryCounts
{
    public function handle(Collection $userIds): Collection
    {
        $bestTimesPerEvent = DB::table('lap_times')
            ->join('events', 'events.id', '=', 'lap_times.event_id')
            ->join('users as u', 'u.guid', '=', 'lap_times.player_guid')
            ->where('events.ending_date', '<=', now())
            ->select('lap_times.event_id', DB::raw('MIN(lap_times.lap_time) as best_time'))
            ->groupBy('lap_times.event_id');

        $winners = DB::table('lap_times as lt')
            ->joinSub($bestTimesPerEvent, 'best', function ($join) {
                $join->on('lt.event_id', '=', 'best.event_id')
                     ->on('lt.lap_time', '=', 'best.best_time');
            })
            ->join('users as u', 'u.guid', '=', 'lt.player_guid')
            ->select('lt.event_id', DB::raw('MIN(lt.id) as winning_lap_id'))
            ->groupBy('lt.event_id');

        return DB::table('lap_times as lt')
            ->joinSub($winners, 'w', 'lt.id', '=', 'w.winning_lap_id')
            ->join('users as u', 'u.guid', '=', 'lt.player_guid')
            ->whereIn('u.id', $userIds)
            ->select('u.id', DB::raw('COUNT(*) as cnt'))
            ->groupBy('u.id')
            ->pluck('cnt', 'u.id');
    }
}
