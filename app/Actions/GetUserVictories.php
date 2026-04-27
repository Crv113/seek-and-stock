<?php

namespace App\Actions;

use App\Models\User;
use App\Models\LapTime;
use Illuminate\Support\Facades\DB;

class GetUserVictories
{
    public function handle(User $user)
    {
        // Sous-requête : meilleur temps par event terminé, uniquement parmi les joueurs ayant un compte
        $bestLapTimes = DB::table('lap_times')
            ->select('lap_times.event_id', DB::raw('MIN(lap_times.lap_time) as best_time'))
            ->join('events', 'events.id', '=', 'lap_times.event_id')
            ->where('events.ending_date', '<=', now())
            ->whereExists(function ($query) {
                $query->select(DB::raw(1))
                    ->from('users')
                    ->whereColumn('users.guid', 'lap_times.player_guid');
            })
            ->groupBy('lap_times.event_id');

        // Deuxième requête : récupérer le lap_time du user s’il est le plus rapide
        $winningIds = DB::table('lap_times as lt')
            ->joinSub($bestLapTimes, 'best', function ($join) {
                $join->on('lt.event_id', '=', 'best.event_id')
                    ->on('lt.lap_time', '=', 'best.best_time');
            })
            ->where('lt.player_guid', $user->guid)
            ->groupBy('lt.event_id')
            ->select(DB::raw('MIN(lt.id) as id')) // en cas d’égalité, le plus ancien
            ->pluck('id');

        return LapTime::with('event', 'bike')
            ->whereIn('id', $winningIds)
            ->orderBy('event_id')
            ->get();
    }
}
