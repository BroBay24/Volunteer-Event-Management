<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class EventController extends Controller
{
    public function index()
    {
        $events = \App\Models\Event::all();
        return response()->json([
            'data' => $events
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'event_date' => 'required|date',
        ]);

        $event = \App\Models\Event::create($validated);

        return response()->json([
            'message' => 'Event created successfully',
            'data' => $event
        ], 201);
    }

    public function show($id)
    {
        $event = \App\Models\Event::with('users:id,name,email')->find($id);

        if (!$event) {
            return response()->json(['message' => 'Event not found'], 404);
        }

        return response()->json([
            'data' => $event
        ]);
    }

    public function join(Request $request, $id)
    {
        $event = \App\Models\Event::find($id);

        if (!$event) {
            return response()->json(['message' => 'Event not found'], 404);
        }

        // Check if user already joined
        if ($event->users()->where('user_id', $request->user()->id)->exists()) {
             return response()->json(['message' => 'You already joined this event'], 409);
        }

        $event->users()->attach($request->user()->id);

        return response()->json([
            'message' => 'Joined event successfully'
        ]);
    }
}
