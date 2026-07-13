<?php

namespace App\Actions;

use Illuminate\Support\Facades\DB;

class GetEventCategories
{
    public function handle($eventId)
    {
        return DB::table('lap_times')
            ->join('bikes', 'bikes.id', '=', 'lap_times.bike_id')
            ->join('categories', 'categories.id', '=', 'bikes.category_id')
            ->where('lap_times.event_id', $eventId)
            ->where(function ($query) {
                $query->whereIn('lap_times.player_guid', fn ($q) => $q->select('guid')->from('users'))
                    ->orWhereIn('lap_times.player_guid', fn ($q) => $q->select('guid')->from('anonymous_users'));
            })
            ->select('categories.id', 'categories.name')
            ->distinct()
            ->orderBy('categories.name')
            ->get();
    }
}
