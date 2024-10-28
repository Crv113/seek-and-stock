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
            'starting_date_timestamp' => 'required|integer',
            'ending_date_timestamp' => 'required|integer',
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
            'starting_date_timestamp' => 'integer',
            'ending_date_timestamp' => 'integer',
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
        $event = Event::with([
            'races.track',
            'races.lapTimes' => function($query) {
                $query->where('fastest', true);
            },
            'races.lapTimes.bike.category'
        ])->findOrFail($id);

        return new EventResource($event);
    }
}
