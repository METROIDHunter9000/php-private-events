<?php

use App\Models\{Event, User, EventUserRelation, EventUserAttendance};

test('user cannot rsvp to an event he organized', function () {
    $user = User::factory()->create();
    $event = Event::factory()->public()->create([
        'organizer_id' => $user,
    ]);

    $response = $this->actingAs($user)->post('/attendance', [
        'user_id' => $user,
        'event_id' => $event,
    ]);

    $response->assertStatus(400);
    $record = EventUserAttendance::where('user_id', $user->id)->where('event_id', $event->id)->first();
    expect($record)->toBeNull();
});

test('user cannot rsvp to a private event', function () {
    $organizer = User::factory()->create();
    $attendee = User::factory()->create();
    $event = Event::factory()->private()->create([
        'organizer_id' => $organizer,
    ]);

    $response = $this->actingAs($attendee)->post('/attendance', [
        'user_id' => $attendee,
        'event_id' => $event,
    ]);

    $response->assertStatus(400);
    $record = EventUserAttendance::where('user_id', $attendee->id)->where('event_id', $event->id)->first();
    expect($record)->toBeNull();
});

test('user can RSVP to a public event', function () {
    $organizer = User::factory()->create();
    $attendee = User::factory()->create();
    $event = Event::factory()->public()->create([
        'organizer_id' => $organizer,
    ]);

    $response = $this->actingAs($attendee)->post('/attendance', [
        'user_id' => $attendee,
        'event_id' => $event,
    ]);

    $response->assertStatus(200);
    $record = EventUserAttendance::where('user_id', $attendee->id)->where('event_id', $event->id)->first();
    expect($record->user_id)->toBe($attendee->id);
    expect($record->event_id)->toBe($event->id);
});

test('user cannot rsvp to an event he is already attending', function () {
    $organizer = User::factory()->create();
    $attendee = User::factory()->create();
    $event = Event::factory()->public()->create([
        'organizer_id' => $organizer,
    ]);
    $this->actingAs($attendee)->post('/attendance', [
        'user_id' => $attendee,
        'event_id' => $event,
    ]);

    $response = $this->actingAs($attendee)->post('/attendance', [
        'user_id' => $attendee,
        'event_id' => $event,
    ]);

    $response->assertStatus(400);
    $record = EventUserAttendance::where('user_id', $attendee->id)->where('event_id', $event->id)->first();
    expect($record->user_id)->toBe($attendee->id);
    expect($record->event_id)->toBe($event->id);
});

test('user cannot post attendance on behalf of a different user', function () {
    $organizer = User::factory()->create();
    $attendee = User::factory()->create();
    $badActor = User::factory()->create();
    $event = Event::factory()->public()->create([
        'organizer_id' => $organizer,
    ]);

    $response = $this->actingAs($badActor)->post('/attendance', [
        'user_id' => $attendee,
        'event_id' => $event,
    ]);

    $response->assertStatus(403);
    $record = EventUserAttendance::where('user_id', $attendee->id)->where('event_id', $event->id)->first();
    expect($record)->toBeNull();
});

test('attendee can withdraw their own attendance', function () {
    $organizer = User::factory()->create();
    $attendee = User::factory()->create();
    $event = Event::factory()->public()->create([
        'organizer_id' => $organizer,
    ]);
    $this->actingAs($attendee)->post('/attendance', [
        'user_id' => $attendee,
        'event_id' => $event,
    ]);

    $record = EventUserAttendance
        ::where('user_id', $attendee->id)
            ->where('event_id', $event->id)
            ->first();
    $response = $this->actingAs($attendee)->delete(route('attendance.destroy', $record));

    $response->assertStatus(200);
    $record = EventUserAttendance
        ::where('user_id', $attendee->id)
            ->where('event_id', $event->id)
            ->first();
    expect($record)->toBeNull();
});

test('organizer can remove any user from any of their events', function () {
    $organizer = User::factory()->create();
    $attendee = User::factory()->create();
    $event = Event::factory()->public()->create([
        'organizer_id' => $organizer,
    ]);
    $this->actingAs($attendee)->post('/attendance', [
        'user_id' => $attendee,
        'event_id' => $event,
    ]);

    $record = EventUserAttendance
        ::where('user_id', $attendee->id)
            ->where('event_id', $event->id)
            ->first();
    $response = $this->actingAs($organizer)->delete(route('attendance.destroy', $record));

    $response->assertStatus(200);
    $record = EventUserAttendance
        ::where('user_id', $attendee->id)
            ->where('event_id', $event->id)
            ->first();
    expect($record)->toBeNull();
});

test('cannot withdraw attendance for somebody else on an event you did not organize', function () {
    $organizer = User::factory()->create();
    $attendee = User::factory()->create();
    $badActor = User::factory()->create();
    $event = Event::factory()->public()->create([
        'organizer_id' => $organizer,
    ]);
    $this->actingAs($attendee)->post('/attendance', [
        'user_id' => $attendee,
        'event_id' => $event,
    ]);

    $record = EventUserAttendance
        ::where('user_id', $attendee->id)
            ->where('event_id', $event->id)
            ->first();
    $response = $this->actingAs($badActor)->delete(route('attendance.destroy', $record));

    $response->assertStatus(403);
    $record = EventUserAttendance
        ::where('user_id', $attendee->id)
            ->where('event_id', $event->id)
            ->first();
    expect($record)->not->toBeNull();
});
