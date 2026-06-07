<?php

namespace App\Actions;

use App\Models\LapTime;
use Illuminate\Support\Facades\DB;

class GetAnonymousBestLapTimes
{
    public function handle(string $guid)
    {
        $subQuery = DB::table('lap_times as lt')
            ->where('lt.player_guid', $guid)
            ->select('lt.event_id', DB::raw('MIN(lt.lap_time) as best_time'))
            ->groupBy('lt.event_id');

        $bestIds = DB::table('lap_times as lt')
            ->joinSub($subQuery, 'best', function ($join) {
                $join->on('lt.event_id', '=', 'best.event_id')
                    ->on('lt.lap_time', '=', 'best.best_time');
            })
            ->where('lt.player_guid', $guid)
            ->groupBy('lt.event_id')
            ->select(DB::raw('MIN(lt.id) as id'))
            ->pluck('id');

        return LapTime::with('event.track', 'bike.category')
            ->whereIn('id', $bestIds)
            ->orderBy('event_id')
            ->get();
    }
}
