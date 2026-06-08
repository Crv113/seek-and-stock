<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AnonymousPlayerResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $bestLapTimes = collect($this->best_lap_times);

        return [
            'id' => $this->id,
            'player_name' => $this->player_name,
            'user_id' => $this->user_id,
            'participation_count' => $bestLapTimes->count(),
            'victory_count' => $this->victory_count ?? 0,
            'best_lap_times_by_track' => $bestLapTimes
                ->groupBy(fn ($lt) => $lt->event->track->id)
                ->map(fn ($group) => $group->sortBy('lap_time')->first())
                ->values(),
            'bike_stats_by_category' => $bestLapTimes
                ->filter(fn ($lt) => $lt['bike'] && isset($lt['bike']['category']['name']))
                ->groupBy(fn ($lt) => $lt['bike']['category']['name'])
                ->mapWithKeys(function ($group, $categoryName) {
                    return [
                        $categoryName => collect($group)
                            ->groupBy(fn ($lt) => $lt['bike']['name'])
                            ->map(fn ($bikes) => $bikes->count()),
                    ];
                }),
        ];
    }
}
