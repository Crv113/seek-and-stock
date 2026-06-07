<?php

namespace App\Http\Controllers\Api;

use App\Actions\GetAnonymousBestLapTimes;
use App\Http\Controllers\Controller;
use App\Http\Resources\AnonymousPlayerResource;
use App\Models\AnonymousUser;

class AnonymousPlayerController extends Controller
{
    public function show(string $guid, GetAnonymousBestLapTimes $bestLapTimes): AnonymousPlayerResource
    {
        $anonymousUser = AnonymousUser::where('guid', $guid)->firstOrFail();

        $anonymousUser->best_lap_times = $bestLapTimes->handle($guid);

        return new AnonymousPlayerResource($anonymousUser);
    }
}
