<?php

namespace App\Actions;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use App\Models\LapTime;

class GetUserBestLapTimes
{
    public function handle(User $user) {
        $subQuery = DB::table('lap_times as lt')
            ->join('users as u', 'u.guid', '=', 'lt.player_guid')
            ->where('u.id', $user->id)
            ->select('lt.event_id', DB::raw('MIN(lt.lap_time) as best_time'))
            ->groupBy('lt.event_id');

        $bestIds = DB::table('lap_times as lt')
            ->join('users as u', 'u.guid', '=', 'lt.player_guid')
            ->joinSub($subQuery, 'best', function ($join) {
                $join->on('lt.event_id', '=', 'best.event_id')
                    ->on('lt.lap_time', '=', 'best.best_time');
            })
            ->where('u.id', $user->id)
            ->groupBy('lt.event_id')
            ->select(DB::raw('MIN(lt.id) as id'))
            ->pluck('id');

        $bestLapTimes = LapTime::with('event', 'bike.category')
            ->whereIn('id', $bestIds)
            ->orderBy('event_id')
            ->get();

        return $bestLapTimes;
    }
}
