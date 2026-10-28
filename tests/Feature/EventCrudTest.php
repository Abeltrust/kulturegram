<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EventCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_events_index_page_can_be_rendered(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('events.index'));

        $response->assertStatus(200);
        $response->assertSee('Cultural Events');
        $response->assertSee('Create Event');
        $response->assertSee(route('events.create'));
    }

    public function test_authenticated_user_can_create_event(): void
    {
        $user = User::factory()->create();

        $eventData = [
            'title' => 'Yoruba Drumming Workshop',
            'description' => 'Learn the sacred rhythms of the Bata drum.',
            'event_date' => now()->addDays(5)->format('Y-m-d'),
            'event_time' => '14:00',
            'location' => 'Lagos Cultural Center',
            'type' => 'workshop',
            'price' => 15,
            'is_online' => false,
            'max_attendees' => 25,
            'accepts_contributions' => true,
        ];

        $response = $this->actingAs($user)->post(route('events.store'), $eventData);

        $response->assertRedirect();
        $this->assertDatabaseHas('events', [
            'title' => 'Yoruba Drumming Workshop',
            'organizer_id' => $user->id,
            'price' => 15,
        ]);
    }

    public function test_organizer_can_update_event(): void
    {
        $organizer = User::factory()->create();
        $event = Event::factory()->create([
            'organizer_id' => $organizer->id,
            'title' => 'Old Event Title',
        ]);

        $response = $this->actingAs($organizer)->put(route('events.update', $event), [
            'title' => 'Updated Event Title',
            'description' => 'Updated description for the workshop.',
            'event_date' => now()->addDays(10)->format('Y-m-d'),
            'event_time' => '16:00',
            'location' => 'Online Hub',
            'type' => 'workshop',
            'price' => 20,
        ]);

        $response->assertRedirect(route('events.show', $event));
        $this->assertDatabaseHas('events', [
            'id' => $event->id,
            'title' => 'Updated Event Title',
            'price' => 20,
        ]);
    }

    public function test_non_organizer_cannot_update_or_delete_event(): void
    {
        $organizer = User::factory()->create();
        $otherUser = User::factory()->create();
        $event = Event::factory()->create(['organizer_id' => $organizer->id]);

        $updateResponse = $this->actingAs($otherUser)->put(route('events.update', $event), [
            'title' => 'Unauthorized Title',
            'description' => 'Desc',
            'event_date' => now()->addDays(5)->format('Y-m-d'),
            'event_time' => '10:00',
            'location' => 'Loc',
            'type' => 'workshop',
        ]);
        $updateResponse->assertStatus(403);

        $deleteResponse = $this->actingAs($otherUser)->delete(route('events.destroy', $event));
        $deleteResponse->assertStatus(403);
    }

    public function test_user_can_purchase_ticket_and_attend_event(): void
    {
        $organizer = User::factory()->create();
        $attendee = User::factory()->create();
        $event = Event::factory()->create([
            'organizer_id' => $organizer->id,
            'price' => 25,
        ]);

        $response = $this->actingAs($attendee)->post(route('events.ticket', $event));

        $response->assertRedirect();
        $this->assertTrue($event->attendees()->where('user_id', $attendee->id)->exists());
        $this->assertDatabaseHas('transactions', [
            'user_id' => $attendee->id,
            'event_id' => $event->id,
            'type' => 'ticket',
            'status' => 'completed',
        ]);
    }

    public function test_user_can_cancel_registration_and_leave_event(): void
    {
        $organizer = User::factory()->create();
        $attendee = User::factory()->create();
        $event = Event::factory()->create([
            'organizer_id' => $organizer->id,
            'attendees_count' => 1,
        ]);
        $event->attendees()->attach($attendee->id);

        $response = $this->actingAs($attendee)->delete(route('events.leave', $event));

        $response->assertRedirect();
        $this->assertFalse($event->attendees()->where('user_id', $attendee->id)->exists());
    }

    public function test_organizer_can_delete_event(): void
    {
        $organizer = User::factory()->create();
        $event = Event::factory()->create(['organizer_id' => $organizer->id]);

        $response = $this->actingAs($organizer)->delete(route('events.destroy', $event));

        $response->assertRedirect(route('events.index'));
        $this->assertDatabaseMissing('events', ['id' => $event->id]);
    }
}
