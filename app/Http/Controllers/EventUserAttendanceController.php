<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\EventUserAttendance;
use App\Models\EventUserRelation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class EventUserAttendanceController extends Controller
{
    public function store(Request $request)
    {
        $attendee_id = $request->user_id->id;
        $event = $request->event_id;
        $event_id = $event->id;

        $record = EventUserRelation
            ::where('user_id', $attendee_id)
                ->where('event_id', $event_id)
                ->first();

        $user = auth()->user();
        if(! $user)
        {
            return response('Must be authenticated to perform this action', 403);
        }
        if($user->id != $attendee_id)
        {
            return response('Cannot RSVP on behalf of another user', 403);
        }

        if($record)
        {
            return response('Already attending this event', 400);
        }
        if($event->is_private)
        {
            return response('Cannot RSVP to private event', 400);
        }
        if($event->organizer_id == $attendee_id)
        {
            return response('Cannot RSVP to your own event', 400);
        }

        EventUserAttendance::create([
            'user_id' => $attendee_id,
            'event_id' => $event_id,
        ]);
    }

    public function destroy(EventUserAttendance $attendance)
    {
        $attendee_id = $attendance->user_id;
        $event = Event::find($attendance->event_id);
        $user = auth()->user();

        if($user->id != $attendee_id && $user->id != $event->organizer_id)
        {
            return response('Not authorized to withdraw this attendance', 403);
        }

        EventUserAttendance::destroy($attendance->id);
    }
}
