<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\EventUserRelation;
use App\Models\EventUserInvitation;
use App\Models\EventUserAttendance;
use Illuminate\Http\Request;

class EventUserInvitationController extends Controller
{
    public function store(Request $request)
    {
        $invitee_id = $request->user_id->id;
        $event = $request->event_id;
        $event_id = $event->id;

        $record = EventUserRelation
            ::where('user_id', $invitee_id)
                ->where('event_id', $event_id)
                ->first();

        $user = auth()->user();
        if(! $user)
        {
            return response('Must be authenticated to perform this action', 401);
        }
        if($user->id != $event->organizer_id)
        {
            return response('Cannot invite somebody to an event you didn\'t organize', 403);
        }

        if($record)
        {
            return response('This user is already invited, attending or requesting to join this event', 400);
        }
        if(! $event->is_private)
        {
            return response('Cannot invite anybody to a public event', 400);
        }
        if($event->organizer_id == $invitee_id)
        {
            return response('Cannot invite yourself to your own event', 400);
        }

        EventUserInvitation::create([
            'user_id' => $invitee_id,
            'event_id' => $event_id,
        ]);
    }

    public function destroy(EventUserInvitation $invitation, $decision)
    {
        $invitee_id = $invitation->user_id;
        $event = Event::find($invitation->event_id);
        $user = auth()->user();

        if(! $user)
        {
            return response('Not Authorized', 401);
        }
        if($user->id == $event->organizer_id)
        {
            EventUserInvitation::destroy($invitation->id);
        }
        elseif($user->id != $invitee_id)
        {
            return response('Not authorized to delete this invitation', 403);
        }
        else
        {
            if($decision == 'accept')
            {
                EventUserInvitation::destroy($invitation->id);
                EventUserAttendance::create([
                    'user_id' => $invitation->user_id,
                    'event_id' => $invitation->event_id,
                ]);
            }
            elseif($decision == 'reject')
            {
                EventUserInvitation::destroy($invitation->id);
            }
            else
            {
                return response("Unrecognized decision $decision", 400);
            }
        }
    }
}
