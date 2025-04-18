<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    function show(Request $request): JsonResponse
    {
        return response()->json([
            ...$request->user()->toArray(),
            'roles' => auth()->user()->roles->pluck('name'),
        ]);
    }

    function update(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'guid' => 'required|string|unique:users,guid',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $user = Auth::user();

        $user->guid = $request->guid;
        $user->save();

        return response()->json([
            'message' => 'GUID updated successfully',
            'user' => $user,
        ]);
    }

    function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Logout successful']);
    }
}
