<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $bestLapTimes = collect($this->best_lap_times);

        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'guid' => $this->guid,
            'discord_id' => $this->discord_id,
            'discord_global_name' => $this->discord_global_name,
            'discord_avatar' => $this->discord_avatar,
            'discord_locale' => $this->discord_locale,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'best_lap_times' => $bestLapTimes,
            'roles' => $this->getRoleNames(),
            'participation_count' => $bestLapTimes->count(),
            'victory_count' => count($this->victories ?? []),
            'bike_stats_by_category' => $bestLapTimes
                ->filter(fn($lt) => $lt['bike'] && isset($lt['bike']['category']['name']))
                ->groupBy(fn($lt) => $lt['bike']['category']['name'])
                ->mapWithKeys(function ($group, $categoryName) {
                    return [
                        $categoryName => collect($group)
                            ->groupBy(fn($lt) => $lt['bike']['name'])
                            ->map(fn($bikes) => $bikes->count())
                    ];
                }),
        ];
    }
}
