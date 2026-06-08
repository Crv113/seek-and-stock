<?php

namespace App\Http\Controllers\Api;

use App\Actions\GetAnonymousBestLapTimes;
use App\Actions\GetAnonymousPlayersFavoriteBikes;
use App\Actions\GetAnonymousPlayersParticipationCounts;
use App\Actions\GetAnonymousVictoryCounts;
use App\Http\Controllers\Controller;
use App\Http\Resources\AnonymousPlayerResource;
use App\Models\AnonymousUser;
use Illuminate\Http\JsonResponse;

class AnonymousPlayerController extends Controller
{
    public function index(
        GetAnonymousPlayersParticipationCounts $participationCounts,
        GetAnonymousPlayersFavoriteBikes $favoriteBikes,
        GetAnonymousVictoryCounts $victoryCounts,
    ): JsonResponse {
        $players = AnonymousUser::whereNull('user_id')->get();
        $ids = $players->pluck('id');

        $participations = $participationCounts->handle();
        $bikes = $favoriteBikes->handle();
        $victories = $victoryCounts->handle($ids);

        return response()->json(
            $players->map(fn ($au) => [
                'id' => $au->id,
                'player_name' => $au->player_name,
                'participation_count' => $participations[$au->id] ?? 0,
                'victory_count' => $victories[$au->id] ?? 0,
                'favorite_bike' => $bikes[$au->id] ?? null,
            ])
            ->sortByDesc('participation_count')
            ->values()
        );
    }

    public function show(
        AnonymousUser $anonymousUser,
        GetAnonymousBestLapTimes $bestLapTimes,
        GetAnonymousVictoryCounts $victoryCounts,
    ): AnonymousPlayerResource {
        $anonymousUser->best_lap_times = $bestLapTimes->handle($anonymousUser->guid);
        $anonymousUser->victory_count = $victoryCounts->handle(collect([$anonymousUser->id]))->get($anonymousUser->id, 0);

        return new AnonymousPlayerResource($anonymousUser);
    }
}
