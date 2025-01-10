<?php

use App\Models\{Event, User, EventUserRelation, EventUserAttendance, EventUserInvitation};

test('organizer can invite other users to their event', function() {
    $organizer = User::factory()->create();
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();
    $event = Event::factory()->private()->create([
        'organizer_id' => $organizer,
    ]);

    $this->actingAs($organizer)->post('/invitations', [
        'user_id' => $user1,
        'event_id' => $event,
    ])->assertStatus(200);

    $this->actingAs($organizer)->post('/invitations', [
        'user_id' => $user2,
        'event_id' => $event,
    ])->assertStatus(200);

    $record1 = EventUserInvitation::where('user_id', $user1->id)->where('event_id', $event->id)->first();
    $record2 = EventUserInvitation::where('user_id', $user2->id)->where('event_id', $event->id)->first();
    expect($record1)->not->toBeNull();
    expect($record2)->not->toBeNull();
});

test('user cannot invite people to an event he did not organize', function () {
    $organizer = User::factory()->create();
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();
    $event = Event::factory()->private()->create([
        'organizer_id' => $organizer,
    ]);

    $this->actingAs($user1)->post('/invitations', [
        'user_id' => $user2,
        'event_id' => $event,
    ])->assertStatus(403);

    $record = EventUserInvitation::where('user_id', $user2->id)->where('event_id', $event->id)->first();
    expect($record)->toBeNull();
});

test('organizer cannot invite himself to his own event', function () {
    $organizer = User::factory()->create();
    $event = Event::factory()->private()->create([
        'organizer_id' => $organizer,
    ]);

    $this->actingAs($organizer)->post('/invitations', [
        'user_id' => $organizer,
        'event_id' => $event,
    ])->assertStatus(400);

    $record = EventUserInvitation::where('user_id', $organizer->id)->where('event_id', $event->id)->first();
    expect($record)->toBeNull();
});

test('organizer cannot invite anyone to a public event', function () {
    $organizer = User::factory()->create();
    $user1 = User::factory()->create();
    $event = Event::factory()->public()->create([
        'organizer_id' => $organizer,
    ]);

    $this->actingAs($organizer)->post('/invitations', [
        'user_id' => $user1,
        'event_id' => $event,
    ])->assertStatus(400);

    $record = EventUserInvitation::where('user_id', $user1->id)->where('event_id', $event->id)->first();
    expect($record)->toBeNull();
});

test('organizer cannot invite anyone who is already invited, attending, or requesting to join', function () {
    $organizer = User::factory()->create();
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();
    $user3 = User::factory()->create();
    $event = Event::factory()->private()->create([
        'organizer_id' => $organizer,
    ]);

    $this->actingAs($organizer)->post('/invitations', [
        'user_id' => $user1,
        'event_id' => $event,
    ])->assertStatus(200);
    $this->actingAs($user2)->post('/requests', [
        'user_id' => $user2,
        'event_id' => $event,
    ])->assertStatus(200);
    EventUserAttendance::create([
        'user_id' => $user3->id,
        'event_id' => $event->id,
    ]);

    $this->actingAs($organizer)->post('/invitations', [
        'user_id' => $user1,
        'event_id' => $event,
    ])->assertStatus(400);
    $this->actingAs($organizer)->post('/invitations', [
        'user_id' => $user2,
        'event_id' => $event,
    ])->assertStatus(400);
    $this->actingAs($organizer)->post('/invitations', [
        'user_id' => $user3,
        'event_id' => $event,
    ])->assertStatus(400);

    $record1 = EventUserInvitation::where('user_id', $user1->id)->where('event_id', $event->id)->first();
    $record2 = EventUserInvitation::where('user_id', $user2->id)->where('event_id', $event->id)->first();
    $record3 = EventUserInvitation::where('user_id', $user3->id)->where('event_id', $event->id)->first();
    expect($record1)->toBeNull();
    expect($record2)->toBeNull();
    expect($record3)->toBeNull();
});

test('organizer can delete an invitation to their event', function () {
    $organizer = User::factory()->create();
    $invitee = User::factory()->create();
    $event = Event::factory()->private()->create([
        'organizer_id' => $organizer,
    ]);
    $this->actingAs($organizer)->post('/invitations', [
        'user_id' => $invitee,
        'event_id' => $event,
    ])->assertStatus(200);

    $record = EventUserInvitation
        ::where('user_id', $invitee->id)
            ->where('event_id', $event->id)
            ->first();

    $response = $this->actingAs($organizer)
                    ->delete(
                        route(
                            'invitations.destroy',
                            [
                                'invitation' => $record->id,
                                'decision' => 'this is optional'
                            ]
                        )
                    );

    $response->assertStatus(200);
    $record_invitation = EventUserInvitation
        ::where('user_id', $invitee->id)
            ->where('event_id', $event->id)
            ->first();
    $record_attendance = EventUserAttendance
        ::where('user_id', $invitee->id)
            ->where('event_id', $event->id)
            ->first();
    expect($record_invitation)->toBeNull();
    expect($record_attendance)->toBeNull();
});

test('user can accept an invitation', function () {
    $organizer = User::factory()->create();
    $invitee = User::factory()->create();
    $event = Event::factory()->private()->create([
        'organizer_id' => $organizer,
    ]);
    $this->actingAs($organizer)->post('/invitations', [
        'user_id' => $invitee,
        'event_id' => $event,
    ])->assertStatus(200);

    $record = EventUserInvitation
        ::where('user_id', $invitee->id)
            ->where('event_id', $event->id)
            ->first();

    $response = $this->actingAs($invitee)
                    ->delete(
                        route(
                            'invitations.destroy',
                            [
                                'invitation' => $record->id,
                                'decision' => 'accept'
                            ]
                        )
                    );

    $response->assertStatus(200);
    $record_invitation = EventUserInvitation
        ::where('user_id', $invitee->id)
            ->where('event_id', $event->id)
            ->first();
    $record_attendance = EventUserAttendance
        ::where('user_id', $invitee->id)
            ->where('event_id', $event->id)
            ->first();
    expect($record_invitation)->toBeNull();
    expect($record_attendance)->not->toBeNull();
});

test('user can reject an invitation', function () {
    $organizer = User::factory()->create();
    $invitee = User::factory()->create();
    $event = Event::factory()->private()->create([
        'organizer_id' => $organizer,
    ]);
    $this->actingAs($organizer)->post('/invitations', [
        'user_id' => $invitee,
        'event_id' => $event,
    ])->assertStatus(200);

    $record = EventUserInvitation
        ::where('user_id', $invitee->id)
            ->where('event_id', $event->id)
            ->first();

    $response = $this->actingAs($invitee)
                    ->delete(
                        route(
                            'invitations.destroy',
                            [
                                'invitation' => $record->id,
                                'decision' => 'reject'
                            ]
                        )
                    );

    $response->assertStatus(200);
    $record_invitation = EventUserInvitation
        ::where('user_id', $invitee->id)
            ->where('event_id', $event->id)
            ->first();
    $record_attendance = EventUserAttendance
        ::where('user_id', $invitee->id)
            ->where('event_id', $event->id)
            ->first();
    expect($record_invitation)->toBeNull();
    expect($record_attendance)->toBeNull();
});

test('user cannot delete somebody else\'s invitation to somebody else\'s event', function () {
    $organizer = User::factory()->create();
    $invitee = User::factory()->create();
    $badActor = User::factory()->create();
    $event = Event::factory()->private()->create([
        'organizer_id' => $organizer,
    ]);
    $this->actingAs($organizer)->post('/invitations', [
        'user_id' => $invitee,
        'event_id' => $event,
    ])->assertStatus(200);

    $record = EventUserInvitation
        ::where('user_id', $invitee->id)
            ->where('event_id', $event->id)
            ->first();

    $response = $this->actingAs($badActor)
                    ->delete(
                        route(
                            'invitations.destroy',
                            [
                                'invitation' => $record->id,
                                'decision' => 'reject'
                            ]
                        )
                    );

    $response->assertStatus(403);
    $record = EventUserInvitation
        ::where('user_id', $invitee->id)
            ->where('event_id', $event->id)
            ->first();
    expect($record)->not->toBeNull();
});
