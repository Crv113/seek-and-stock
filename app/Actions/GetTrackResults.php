<?php

namespace App\Actions;

use App\Models\LapTime;
use App\Models\Track;
use Illuminate\Support\Facades\DB;

class GetTrackResults
{
    public function handle(Track $track)
    {
        $minLapTimes = DB::table('lap_times')
            ->join('events', 'events.id', '=', 'lap_times.event_id')
            ->select('lap_times.player_guid', DB::raw('MIN(lap_times.lap_time) as min_lap_time'))
            ->where('events.track_id', $track->id)
            ->whereIn('lap_times.player_guid', function ($query) {
                $query->select('guid')->from('users');
            })
            ->groupBy('lap_times.player_guid');

        $selectedIds = DB::table('lap_times')
            ->join('events', 'events.id', '=', 'lap_times.event_id')
            ->joinSub($minLapTimes, 'best_laps', function ($join) {
                $join->on('lap_times.player_guid', '=', 'best_laps.player_guid')
                    ->on('lap_times.lap_time', '=', 'best_laps.min_lap_time');
            })
            ->where('events.track_id', $track->id)
            ->selectRaw('MIN(lap_times.id) as id')
            ->groupBy('lap_times.player_guid');

        return LapTime::with('user', 'bike.category')
            ->joinSub($selectedIds, 'final_ids', function ($join) {
                $join->on('lap_times.id', '=', 'final_ids.id');
            })
            ->orderBy('lap_times.lap_time')
            ->get();
    }
}
