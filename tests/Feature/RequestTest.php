<?php

use App\Models\{Event, User, EventUserRelation, EventUserAttendance, EventUserRequest};

test('user can request to join an event', function () {
    $organizer = User::factory()->create();
    $user = User::factory()->create();
    $event = Event::factory()->public()->create([
        'organizer_id' => $organizer,
    ]);

    $this->actingAs($user)->post('/requests', [
        'user_id' => $user,
        'event_id' => $event,
    ])->assertStatus(400);

    $record = EventUserRequest::where('user_id', $user->id)->where('event_id', $event->id)->first();
    expect($record)->toBeNull();
});

test('user cannot request to join his own event', function () {
    $organizer = User::factory()->create();
    $event = Event::factory()->private()->create([
        'organizer_id' => $organizer,
    ]);

    $this->actingAs($organizer)->post('/requests', [
        'user_id' => $organizer,
        'event_id' => $event,
    ])->assertStatus(400);

    $record = EventUserRequest::where('user_id', $organizer->id)->where('event_id', $event->id)->first();
    expect($record)->toBeNull();
});

test('user cannot request to join a public event', function () {
    $organizer = User::factory()->create();
    $user = User::factory()->create();
    $event = Event::factory()->public()->create([
        'organizer_id' => $organizer,
    ]);

    $this->actingAs($user)->post('/requests', [
        'user_id' => $user,
        'event_id' => $event,
    ])->assertStatus(400);

    $record = EventUserRequest::where('user_id', $user->id)->where('event_id', $event->id)->first();
    expect($record)->toBeNull();
});

test('user cannot request to join an event he is already invited to, attending, or requesting to join', function () {
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

    $this->actingAs($user1)->post('/requests', [
        'user_id' => $user1,
        'event_id' => $event,
    ])->assertStatus(400);
    $this->actingAs($user2)->post('/requests', [
        'user_id' => $user2,
        'event_id' => $event,
    ])->assertStatus(400);
    $this->actingAs($user3)->post('/requests', [
        'user_id' => $user3,
        'event_id' => $event,
    ])->assertStatus(400);

    $record1 = EventUserRequest::where('user_id', $user1->id)->where('event_id', $event->id)->first();
    $record2 = EventUserRequest::where('user_id', $user2->id)->where('event_id', $event->id)->first();
    $record3 = EventUserRequest::where('user_id', $user3->id)->where('event_id', $event->id)->first();
    expect($record1)->toBeNull();
    expect($record2)->not->toBeNull();
    expect($record3)->toBeNull();
});

test('user can delete a request to join an event', function () {
    $organizer = User::factory()->create();
    $user = User::factory()->create();
    $event = Event::factory()->private()->create([
        'organizer_id' => $organizer,
    ]);
    $this->actingAs($user)->post('/requests', [
        'user_id' => $user,
        'event_id' => $event,
    ])->assertStatus(200);

    $record = EventUserRequest
        ::where('user_id', $user->id)
            ->where('event_id', $event->id)
            ->first();

    $response = $this->actingAs($user)
                    ->delete(
                        route(
                            'requests.destroy',
                            [
                                'request' => $record->id,
                                'decision' => 'this is optional'
                            ]
                        )
                    );

    $response->assertStatus(200);
    $record_request = EventUserRequest
        ::where('user_id', $user->id)
            ->where('event_id', $event->id)
            ->first();
    $record_attendance = EventUserAttendance
        ::where('user_id', $user->id)
            ->where('event_id', $event->id)
            ->first();
    expect($record_request)->toBeNull();
    expect($record_attendance)->toBeNull();
});

test('organizer can accept a request to join', function () {
    $organizer = User::factory()->create();
    $user = User::factory()->create();
    $event = Event::factory()->private()->create([
        'organizer_id' => $organizer,
    ]);
    $this->actingAs($user)->post('/requests', [
        'user_id' => $user,
        'event_id' => $event,
    ])->assertStatus(200);

    $record = EventUserRequest
        ::where('user_id', $user->id)
            ->where('event_id', $event->id)
            ->first();

    $response = $this->actingAs($organizer)
                    ->delete(
                        route(
                            'requests.destroy',
                            [
                                'request' => $record->id,
                                'decision' => 'accept'
                            ]
                        )
                    );

    $response->assertStatus(200);
    $record_request = EventUserRequest
        ::where('user_id', $user->id)
            ->where('event_id', $event->id)
            ->first();
    $record_attendance = EventUserAttendance
        ::where('user_id', $user->id)
            ->where('event_id', $event->id)
            ->first();
    expect($record_request)->toBeNull();
    expect($record_attendance)->not->toBeNull();
});

test('organizer can reject a request to join', function () {
    $organizer = User::factory()->create();
    $user = User::factory()->create();
    $event = Event::factory()->private()->create([
        'organizer_id' => $organizer,
    ]);
    $this->actingAs($user)->post('/requests', [
        'user_id' => $user,
        'event_id' => $event,
    ])->assertStatus(200);

    $record = EventUserRequest
        ::where('user_id', $user->id)
            ->where('event_id', $event->id)
            ->first();

    $response = $this->actingAs($organizer)
                    ->delete(
                        route(
                            'requests.destroy',
                            [
                                'request' => $record->id,
                                'decision' => 'reject'
                            ]
                        )
                    );

    $response->assertStatus(200);
    $record_request = EventUserRequest
        ::where('user_id', $user->id)
            ->where('event_id', $event->id)
            ->first();
    $record_attendance = EventUserAttendance
        ::where('user_id', $user->id)
            ->where('event_id', $event->id)
            ->first();
    expect($record_request)->toBeNull();
    expect($record_attendance)->toBeNull();
});

test('user cannot accept or reject requests to an event he did not organize or on behalf of another user', function () {
    $organizer = User::factory()->create();
    $user = User::factory()->create();
    $badActor = User::factory()->create();
    $event = Event::factory()->private()->create([
        'organizer_id' => $organizer,
    ]);
    $this->actingAs($user)->post('/requests', [
        'user_id' => $user,
        'event_id' => $event,
    ])->assertStatus(200);

    $record = EventUserRequest
        ::where('user_id', $user->id)
            ->where('event_id', $event->id)
            ->first();

    $response = $this->actingAs($badActor)
                    ->delete(
                        route(
                            'requests.destroy',
                            [
                                'request' => $record->id,
                                'decision' => 'accept'
                            ]
                        )
                    );

    $response->assertStatus(403);
    $record_request = EventUserRequest
        ::where('user_id', $user->id)
            ->where('event_id', $event->id)
            ->first();
    $record_attendance = EventUserAttendance
        ::where('user_id', $user->id)
            ->where('event_id', $event->id)
            ->first();
    expect($record_request)->not->toBeNull();
    expect($record_attendance)->toBeNull();
});
