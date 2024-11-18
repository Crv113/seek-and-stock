<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\EventResource;
use App\Models\Event;
use Illuminate\Http\Request;

class EventController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Event::all();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'starting_date' => 'required|string',
            'ending_date' => 'required|string',
        ]);

        $event = Event::create($request->all());
        return response()->json($event, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Event $event)
    {
        return $event;
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Event $event)
    {
        $request->validate([
            'name' => 'string',
            'starting_date' => 'string',
            'ending_date' => 'string',
        ]);

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

    public function getEventResults($id)
    {
        //TODO: refacto ce code + revoir format retour
        $event = Event::with([
            'track',
            'races.lapTimes' => function($query) {
                $query->where('fastest', true);
            },
            'races.lapTimes.bike.category'
        ])->findOrFail($id);

        $fastest_lap_times = [];
        foreach ($event->races as $race) {
            foreach ($race->lapTimes as $lap_time) {
                $fastest_lap_times[] = $lap_time;
            }
        }

        $results = [];
        foreach ($fastest_lap_times as $fastest_lap_time) {
            if(array_key_exists($fastest_lap_time->player_guid, $results)) {
                if($fastest_lap_time->lap_time <= $results[$fastest_lap_time->player_guid]->lap_time) {
                    $results[$fastest_lap_time->player_guid] = $fastest_lap_time;
                }
            } else {
                $results[$fastest_lap_time->player_guid] = $fastest_lap_time;
            }

        }

        uasort($results, function ($a, $b) {
            return $a->lap_time <=> $b->lap_time;
        });

        $event->lapTimes = collect($results);
        return new EventResource($event);
    }
}

