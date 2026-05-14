<?php

namespace App\Http\Controllers\Api;

use App\Actions\GetUserBestLapTimes;
use App\Actions\GetUserVictories;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    function index()
    {
        $users = User::select('id', 'name', 'discord_id', 'discord_global_name', 'discord_avatar')
            ->orderBy('name')
            ->get();

        $userIds = $users->pluck('id');

        // Participation count: distinct events per user
        $participations = DB::table('lap_times')
            ->join('users as u', 'u.guid', '=', 'lap_times.player_guid')
            ->whereIn('u.id', $userIds)
            ->select('u.id', DB::raw('COUNT(DISTINCT lap_times.event_id) as cnt'))
            ->groupBy('u.id')
            ->pluck('cnt', 'u.id');

        // Victory count: finished events won per user (tie-break by MIN lap id)
        $bestTimesPerEvent = DB::table('lap_times')
            ->join('events', 'events.id', '=', 'lap_times.event_id')
            ->join('users as u', 'u.guid', '=', 'lap_times.player_guid')
            ->where('events.ending_date', '<=', now())
            ->select('lap_times.event_id', DB::raw('MIN(lap_times.lap_time) as best_time'))
            ->groupBy('lap_times.event_id');

        $winners = DB::table('lap_times as lt')
            ->joinSub($bestTimesPerEvent, 'best', function ($join) {
                $join->on('lt.event_id', '=', 'best.event_id')
                     ->on('lt.lap_time', '=', 'best.best_time');
            })
            ->join('users as u', 'u.guid', '=', 'lt.player_guid')
            ->select('lt.event_id', DB::raw('MIN(lt.id) as winning_lap_id'))
            ->groupBy('lt.event_id');

        $victories = DB::table('lap_times as lt')
            ->joinSub($winners, 'w', 'lt.id', '=', 'w.winning_lap_id')
            ->join('users as u', 'u.guid', '=', 'lt.player_guid')
            ->whereIn('u.id', $userIds)
            ->select('u.id', DB::raw('COUNT(*) as cnt'))
            ->groupBy('u.id')
            ->pluck('cnt', 'u.id');

        // Favorite bike: most used bike across all lap_times per user
        $favoriteBikes = DB::table('lap_times')
            ->join('users as u', 'u.guid', '=', 'lap_times.player_guid')
            ->join('bikes', 'bikes.id', '=', 'lap_times.bike_id')
            ->whereIn('u.id', $userIds)
            ->select('u.id as user_id', 'bikes.name as bike_name', DB::raw('COUNT(*) as cnt'))
            ->groupBy('u.id', 'bikes.name')
            ->get()
            ->groupBy('user_id')
            ->map(fn($bikes) => $bikes->sortByDesc('cnt')->first()->bike_name);

        return $users->map(fn($user) => [
            'id'                  => $user->id,
            'name'                => $user->name,
            'discord_id'          => $user->discord_id,
            'discord_global_name' => $user->discord_global_name,
            'discord_avatar'      => $user->discord_avatar,
            'participation_count' => $participations[$user->id] ?? 0,
            'victory_count'       => $victories[$user->id] ?? 0,
            'favorite_bike'       => $favoriteBikes[$user->id] ?? null,
        ]);
    }

    function show(User $user, GetUserBestLapTimes $get_user_best_lap_times, GetUserVictories $get_user_victories): UserResource
    {
        $user->best_lap_times = $get_user_best_lap_times->handle($user);
        $user->victories = $get_user_victories->handle($user);
        return new UserResource($user);
    }

    public function me(GetUserBestLapTimes $bestLapTimes, GetUserVictories $victories)
    {
        $user = auth()->user();

        $user->best_lap_times = $bestLapTimes->handle($user);
        $user->victories = $victories->handle($user);

        return new UserResource($user);
    }

    function update(Request $request): JsonResponse
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

        if (!$request->has('guid') && !$request->has('name')) {
            return response()->json([
                'message' => 'No data to update.',
            ], 400);
        }

        $user = Auth::user();

        if($request->has("guid")) $user->guid = $request->guid;
        if($request->has("name")) $user->name = $request->name;
        $user->save();

        return response()->json([
            'message' => 'User updated successfully.',
            'user' => $user,
        ]);
    }

    function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Logout successful']);
    }
}
