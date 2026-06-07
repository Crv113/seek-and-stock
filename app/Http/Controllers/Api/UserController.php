<?php

namespace App\Http\Controllers\Api;

use App\Actions\GetUserBestLapTimes;
use App\Actions\GetUsersFavoriteBikes;
use App\Actions\GetUsersParticipationCounts;
use App\Actions\GetUsersVictoryCounts;
use App\Actions\MergeAnonymousLaptimes;
use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    public function index(
        GetUsersParticipationCounts $participationCounts,
        GetUsersVictoryCounts $victoryCounts,
        GetUsersFavoriteBikes $favoriteBikes,
    ) {
        $users = User::select('id', 'name', 'discord_id', 'discord_global_name', 'discord_username', 'discord_avatar')
            ->orderBy('name')
            ->get();

        $userIds = $users->pluck('id');
        $participations = $participationCounts->handle($userIds);
        $victories = $victoryCounts->handle($userIds);
        $bikes = $favoriteBikes->handle($userIds);

        return $users->map(fn ($user) => [
            'id' => $user->id,
            'name' => $user->name,
            'discord_id' => $user->discord_id,
            'discord_global_name' => $user->discord_global_name,
            'discord_username' => $user->discord_username,
            'discord_avatar' => $user->discord_avatar,
            'participation_count' => $participations[$user->id] ?? 0,
            'victory_count' => $victories[$user->id] ?? 0,
            'favorite_bike' => $bikes[$user->id] ?? null,
        ]);
    }

    public function show(User $user, GetUserBestLapTimes $bestLapTimes, GetUsersVictoryCounts $victoryCounts): UserResource
    {
        $user->best_lap_times = $bestLapTimes->handle($user);
        $user->victory_count = $victoryCounts->handle(collect([$user->id]))[$user->id] ?? 0;

        return new UserResource($user);
    }

    public function me(GetUserBestLapTimes $bestLapTimes, GetUsersVictoryCounts $victoryCounts)
    {
        $user = auth()->user();

        $user->best_lap_times = $bestLapTimes->handle($user);
        $user->victory_count = $victoryCounts->handle(collect([$user->id]))[$user->id] ?? 0;

        return new UserResource($user);
    }

    public function update(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'guid' => 'sometimes|string|unique:users,guid',
            'name' => 'sometimes|string|nullable|unique:users,name',
        ], [
            'guid.unique' => 'This GUID is already used.',
            'name.unique' => 'This name is already used.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        if (! $request->has('guid') && ! $request->has('name')) {
            return response()->json([
                'message' => 'No data to update.',
            ], 400);
        }

        $user = Auth::user();

        if ($request->has('guid')) {
            $user->guid = $request->guid;
        }
        if ($request->has('name')) {
            $user->name = $request->name;
        }
        $user->save();

        $merged = false;
        if ($request->has('guid')) {
            $merged = (new MergeAnonymousLaptimes)->handle($user);
        }

        return response()->json([
            'message' => 'User updated successfully.',
            'user' => $user,
            'merged' => $merged,
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Logout successful']);
    }
}
