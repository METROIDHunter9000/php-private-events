<?php

namespace Database\Seeders;

use App\Models\{Event, User, EventUserRelation};
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        $user1 = User::factory()->create([
            'name' => 'User 1',
            'email' => 'user1@example.com',
        ]);
        $user2 = User::factory()->create([
            'name' => 'User 2',
            'email' => 'user2@example.com',
        ]);
        $user3 = User::factory()->create([
            'name' => 'User 3',
            'email' => 'user3@example.com',
        ]);
        $user4 = User::factory()->create([
            'name' => 'User 4',
            'email' => 'user4@example.com',
        ]);

        $priv1 = Event::factory()->private()->create([
            'title' => 'Private Event 1',
            'date' => new \DateTimeImmutable("2024-10-23"),
            'organizer_id' => $user1,
        ]);

        $pub1 = Event::factory()->public()->create([
            'title' => 'Public Event 1',
            'date' => new \DateTimeImmutable("2025-02-03"),
            'organizer_id' => $user1,
        ]);

        EventUserRelation::factory()->create([
            'type' => 'request',
            'event_id' => $priv1,
            'user_id' => $user2,
        ]);

        EventUserRelation::factory()->create([
            'type' => 'invitation',
            'event_id' => $priv1,
            'user_id' => $user3,
        ]);

        EventUserRelation::factory()->create([
            'type' => 'rsvp',
            'event_id' => $pub1,
            'user_id' => $user2,
        ]);

        EventUserRelation::factory()->create([
            'type' => 'rsvp',
            'event_id' => $priv1,
            'user_id' => $user4,
        ]);
    }
}
