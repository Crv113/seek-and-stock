<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\LapTimeResource;
use App\Models\Event;
use App\Models\LapTime;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class EventController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Event::with('track:id,name,image')->orderBy('ending_date', 'desc')->get();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'image' => 'image|mimes:jpeg,png,jpg,gif,svg,webp|max:2048',
            'track_id' => 'required|int',
            'starting_date' => 'required|string',
            'ending_date' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        if($request->file('image')) {
            $imagePath = $request->file('image')->store('images/events', 'public');
        }


        $event = Event::create([
            'name' => $request->input('name'),
            'image' => $imagePath ?? null,
            'track_id' => $request->input('track_id'),
            'starting_date' => $request->input('starting_date'),
            'ending_date' => $request->input('ending_date')
        ]);
        $event->load(['track:id,name,image']);
        return response()->json($event, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Event $event)
    {
        return $event->load(['track:id,name,image']);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Event $event)
    {

        $validator = Validator::make($request->all(), [
            'name' => 'string',
            'track_id' => 'string',
            'starting_date' => 'string',
            'ending_date' => 'string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }
        $event->update($request->all());
        return response()->json($event);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Event $event)
    {
        dd('no no');
        $event->delete();
        return response()->json(null, 204);
    }

    public function getEventResults($id) {

        $subQuery = DB::table('lap_times')
            ->select('player_guid', DB::raw('MIN(lap_time) as min_lap_time'), DB::raw('MIN(id) as min_id'))
            ->where('event_id', $id)
            ->whereIn('player_guid', function ($query) {
                $query->select('guid')->from('users');
            })
            ->groupBy('player_guid');

        $fastestLapTimes = LapTime::with('user')
            ->where('event_id', $id)
            ->joinSub($subQuery, 'fastest_laps', function ($join) {
                $join->on('lap_times.id', '=', 'fastest_laps.min_id');
            })
            ->orderBy('lap_times.lap_time')
            ->get();

        return LapTimeResource::collection($fastestLapTimes);

    }

    public function listUsersGuid(Event $event): JsonResponse
    {
        return response()->json($event->users()->pluck('guid'));
    }
}

