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
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
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
