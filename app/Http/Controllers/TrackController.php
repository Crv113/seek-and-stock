<?php

namespace App\Http\Controllers;

use App\Models\Track;
use Illuminate\Http\Request;
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
            'key' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $track = Track::create($request->all());
        return response()->json($track, 201);
    }

    public function show(Track $track)
    {
        return $track;
    }

    public function update(Request $request, Track $track)
    {
        $request->validate([
            'key' => 'string',
            'name' => 'string',
        ]);

        $track->update($request->all());
        return response()->json($track);
    }
}
