<?php

namespace App\Http\Controllers\Api;

use App\Actions\UpdateServerStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateServerStatusRequest;
use App\Http\Resources\ServerStatusResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;

class ServerStatusController extends Controller
{
    public function update(UpdateServerStatusRequest $request, UpdateServerStatus $action): JsonResponse
    {
        $action->handle($request->validated('players_online'));

        return response()->json(['success' => true]);
    }

    public function show(): ServerStatusResource
    {
        return new ServerStatusResource(Cache::get('server_status:players_online'));
    }
}
