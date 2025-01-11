<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\EventUserRelation;
use App\Models\EventUserAttendance;
use App\Models\EventUserRequest;
use Illuminate\Http\Request;

class EventUserRequestController extends Controller
{
    public function store(Request $request)
    {
        $requesting_user_id = $request->user_id->id;
        $event = $request->event_id;
        $event_id = $event->id;

        $record = EventUserRelation
            ::where('user_id', $requesting_user_id)
                ->where('event_id', $event_id)
                ->first();

        $user = auth()->user();
        if(! $user)
        {
            return response('Must be authenticated to perform this action', 401);
        }
        if($user->id != $requesting_user_id)
        {
            return response('Cannot generate a request to join on somebody else\'s behalf', 403);
        }

        if($record)
        {
            return response('You are already invited, attending or requesting to join this event', 400);
        }
        if(! $event->is_private)
        {
            return response('Cannot request to join a public event', 400);
        }
        if($event->organizer_id == $requesting_user_id)
        {
            return response('Cannot request to join your own event', 400);
        }

        EventUserRequest::create([
            'user_id' => $requesting_user_id,
            'event_id' => $event_id,
        ]);
    }

    public function destroy(EventUserRequest $request, $decision)
    {
        $requesting_user_id = $request->user_id;
        $event = Event::find($request->event_id);
        $user = auth()->user();

        if(! $user)
        {
            return response('Not Authorized', 401);
        }
        if($user->id == $requesting_user_id)
        {
            EventUserRequest::destroy($request->id);
        }
        elseif($user->id != $event->organizer_id)
        {
            return response('Not authorized to delete this request', 403);
        }
        else
        {
            if($decision == 'accept')
            {
                EventUserRequest::destroy($request->id);
                EventUserAttendance::create([
                    'user_id' => $requesting_user_id,
                    'event_id' => $request->event_id,
                ]);
            }
            elseif($decision == 'reject')
            {
                EventUserRequest::destroy($request->id);
            }
            else
            {
                return response("Unrecognized decision $decision", 400);
            }
        }
    }
}
