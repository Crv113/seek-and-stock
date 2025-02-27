<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Event;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EventUserController extends Controller
{
    public function register(Request $request, $eventId): JsonResponse
    {
        $user = auth()->user();
        $event = Event::findOrFail($eventId);

        if ($event->users()->where('user_id', $user->id)->exists()) {
            return response()->json(['message' => 'You are already registered for this event.'], 400);
        }

        $event->users()->attach($user->id);

        return response()->json(['message' => 'Registration successful'], 200);
    }

    public function unregister(Request $request, $eventId): JsonResponse
    {
        $user = auth()->user();
        $event = Event::findOrFail($eventId);

        if (!$event->users()->where('user_id', $user->id)->exists()) {
            return response()->json(['message' => 'You are not registered for this event.'], 400);
        }

        $event->users()->detach($user->id);

        return response()->json(['message' => 'Unsubscribe successful'], 200);
    }

}
