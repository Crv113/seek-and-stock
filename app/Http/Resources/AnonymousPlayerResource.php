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
            'guid' => $this->guid,
            'player_name' => $this->player_name,
            'user_id' => $this->user_id,
            'best_lap_times_by_track' => $bestLapTimes
                ->groupBy(fn ($lt) => $lt->event->track->id)
                ->map(fn ($group) => $group->sortBy('lap_time')->first())
                ->values(),
        ];
    }
}
