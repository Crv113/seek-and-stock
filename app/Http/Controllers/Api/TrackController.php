<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Track;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class TrackController extends Controller
{
    public function index()
    {
        return Track::all();
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,svg,webp|max:2048'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $imagePath = $request->file('image')->store('images/tracks', 'public');

        $track = Track::create([
            'name' => $request->input('name'),
            'image' => $imagePath,
        ]);

        return response()->json([
            'id' => $track->id,
            'name' => $track->name,
            'image' => $track->image
        ], 201);
    }

    public function show(Track $track)
    {
        return $track;
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'string',
            'image' => 'image|mimes:jpeg,png,jpg,gif,svg,webp|max:2048'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }
        $track = Track::query()->findOrFail($id);

        if($request->has('name')) {
            $track->name = $request->input('name');
        }

        if($request->hasFile('image')) {
            $track->image = $request->file('image')->store('images/tracks', 'public');
        }

        $track->save();

        return response()->json([
            'id' => $track->id,
            'name' => $track->name,
            'image' => $track->image
        ], 201);
    }
}
