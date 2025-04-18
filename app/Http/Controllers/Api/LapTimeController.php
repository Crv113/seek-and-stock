<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Bike;
use App\Models\Category;
use App\Models\Event;
use App\Models\LapTime;
use App\Models\Track;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LapTimeController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'event_name' => 'required|string',
            'track_name' => 'required|string',
            'lap_time' => 'required|numeric',
            'lap_time_sector_1' => 'required|numeric',
            'lap_time_sector_2' => 'required|numeric',
            'average_speed' => 'nullable|numeric',
            'bike_name' => 'required|string',
            'category_name' => 'required|string',
            'player_guid' => 'required|string',
            'player_name' => 'nullable|string',
        ]);

        $track = Track::where('name', $validated['track_name'])->firstOrFail();

        $now = now();
        $event = Event::where('name', $validated['event_name'])
            ->where('track_id', $track->id)
            ->where('starting_date', '<=', $now)
            ->where('ending_date', '>=', $now)
            ->firstOrFail();

        $category = Category::firstOrCreate(['name' => $validated['category_name']]);
        $bike = Bike::firstOrCreate(['name' => $validated['bike_name'], 'category_id' => $category->id]);

        $validated['lap_time'] = (float) $validated['lap_time'];
        $validated['lap_time_sector_1'] = (float) $validated['lap_time_sector_1'];
        $validated['lap_time_sector_2'] = (float) $validated['lap_time_sector_2'];
        if (isset($validated['average_speed'])) {
            $validated['average_speed'] = (float) $validated['average_speed'];
        }

        $lap_time_sector_3 = $validated['lap_time'] - ($validated['lap_time_sector_1'] + $validated['lap_time_sector_2']);

        $lapTime = LapTime::create([
            'event_id' => $event->id,
            'player_guid' => $validated['player_guid'],
            'player_name' => $validated['player_name'],
            'bike_id' => $bike->id,
//            'average_speed' => $validated['average_speed'] ?? null,
            'lap_time' => $validated['lap_time'],
            'lap_time_sector_1' => $validated['lap_time_sector_1'],
            'lap_time_sector_2' => $validated['lap_time_sector_2'],
            'lap_time_sector_3' => $lap_time_sector_3,
        ]);

        return response()->json(['message' => 'LapTime saved', 'lapTime' => $lapTime], 201);
    }

}
