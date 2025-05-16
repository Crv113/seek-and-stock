<?php
namespace App\Actions;

use App\Models\LapTime;
use Illuminate\Support\Facades\DB;

class GetEventResults
{
    public function handle($eventId)
    {
         // Sous-requête : on sélectionne le meilleur temps par joueur (MIN lap_time)
        $minLapTimes = DB::table('lap_times')
            ->select('player_guid', DB::raw('MIN(lap_time) as min_lap_time'))
            ->where('event_id', $eventId)
            ->whereIn('player_guid', function ($query) {
                $query->select('guid')->from('users');
            })
            ->groupBy('player_guid');

        // Deuxième sous-requête : on retrouve les id les plus petits pour chaque (guid + lap_time)
        $selectedIds = DB::table('lap_times')
            ->joinSub($minLapTimes, 'best_laps', function ($join) {
                $join->on('lap_times.player_guid', '=', 'best_laps.player_guid')
                    ->on('lap_times.lap_time', '=', 'best_laps.min_lap_time');
            })
            ->selectRaw('MIN(lap_times.id) as id')
            ->groupBy('lap_times.player_guid');

        // Requête finale : on récupère les lignes complètes
        $fastestLapTimes = LapTime::with('user')
            ->joinSub($selectedIds, 'final_ids', function ($join) {
                $join->on('lap_times.id', '=', 'final_ids.id');
            })
            ->orderBy('lap_times.lap_time')
            ->get();

        return $fastestLapTimes;

    }
}
