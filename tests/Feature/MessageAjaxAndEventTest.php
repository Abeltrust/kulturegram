<?php

namespace Tests\Feature;

use App\Models\Conversation;
use App\Models\Event;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MessageAjaxAndEventTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_create_event_for_today_with_various_types(): void
    {
        $user = User::factory()->create();

        // Test creating event scheduled for today
        $response = $this->actingAs($user)->post(route('events.store'), [
            'title' => 'Ancient Pottery Masterclass',
            'description' => 'Hands-on exploration of ancient Nok terracotta pottery.',
            'event_date' => now()->format('Y-m-d'),
            'event_time' => '16:00',
            'location' => 'Craft Studio',
            'type' => 'class', // New type
            'price' => 0,
            'is_online' => false,
        ]);

        $response->assertRedirect(route('events.index'));
        $this->assertDatabaseHas('events', [
            'title' => 'Ancient Pottery Masterclass',
            'type' => 'class',
            'organizer_id' => $user->id,
        ]);
    }

    public function test_user_can_create_online_event_without_explicit_location(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('events.store'), [
            'title' => 'Global Heritage Stream',
            'description' => 'Online community discussion.',
            'event_date' => now()->addDay()->format('Y-m-d'),
            'event_time' => '18:00',
            'is_online' => true,
            'type' => 'training',
            'price' => 0,
        ]);

        $response->assertRedirect(route('events.index'));
        $this->assertDatabaseHas('events', [
            'title' => 'Global Heritage Stream',
            'location' => 'Online',
            'type' => 'training',
        ]);
    }

    public function test_user_can_send_message_via_ajax_without_page_refresh(): void
    {
        $sender = User::factory()->create();
        $recipient = User::factory()->create();

        $conversation = Conversation::create(['type' => 'direct']);
        $conversation->participants()->attach([$sender->id, $recipient->id]);

        $response = $this->actingAs($sender)->postJson(route('messages.store', $conversation), [
            'content' => 'Hello there, culture explorer!',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'message' => [
                'content' => 'Hello there, culture explorer!',
                'user_id' => $sender->id,
                'is_me' => true,
            ],
        ]);

        $this->assertDatabaseHas('messages', [
            'conversation_id' => $conversation->id,
            'user_id' => $sender->id,
            'content' => 'Hello there, culture explorer!',
        ]);
    }

    public function test_user_can_fetch_message_updates_without_refresh(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        $conversation = Conversation::create(['type' => 'direct']);
        $conversation->participants()->attach([$user1->id, $user2->id]);

        $msg1 = $conversation->messages()->create([
            'user_id' => $user2->id,
            'content' => 'Welcome to Kulturegram!',
            'status' => 'sent',
        ]);

        $response = $this->actingAs($user1)->getJson(route('messages.updates', $conversation) . '?after_id=0');

        $response->assertStatus(200);
        $response->assertJsonCount(1, 'messages');
        $response->assertJsonFragment([
            'id' => $msg1->id,
            'content' => 'Welcome to Kulturegram!',
            'is_me' => false,
        ]);
    }
}
