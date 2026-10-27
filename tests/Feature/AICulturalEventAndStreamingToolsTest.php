<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\LiveStream;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AICulturalEventAndStreamingToolsTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create([
            'name' => 'Amara Okafor',
            'email' => 'amara@example.com',
        ]);
    }

    public function test_authenticated_user_can_generate_cultural_event_description_from_key_points()
    {
        $response = $this->actingAs($this->user)->postJson(route('events.ai-suggest-description'), [
            'title' => 'Yoruba Adire Textile Workshop',
            'key_points' => 'Elders teaching resist-dyeing symbols, Indigo preparation, youth mentoring',
            'type' => 'workshop',
            'cultural_focus' => 'Yoruba heritage and traditional dyeing',
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'suggested_description',
        ]);
        $this->assertTrue($response->json('success'));
        $this->assertNotEmpty($response->json('suggested_description'));
    }

    public function test_authenticated_user_can_generate_ai_cultural_flyer()
    {
        $response = $this->actingAs($this->user)->postJson(route('events.ai-generate-flyer'), [
            'title' => 'Ancient Bronze Casting Exhibition',
            'description' => 'A showcase of historic Benin bronze casting techniques.',
            'type' => 'exhibition',
            'cultural_context' => 'Benin Kingdom art and bronzes',
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'image_path',
            'image_url',
        ]);
        $this->assertTrue($response->json('success'));
        $this->assertNotEmpty($response->json('image_path'));
        $this->assertNotEmpty($response->json('image_url'));
    }

    public function test_event_store_accepts_ai_generated_flyer_path()
    {
        $flyerPath = 'events/flyers/test_ai_cultural_flyer.svg';

        $response = $this->actingAs($this->user)->post(route('events.store'), [
            'title' => 'Festival of Oral Narratives',
            'description' => 'Stories told by elders under the moonlight.',
            'event_date' => now()->addDays(5)->format('Y-m-d'),
            'event_time' => '18:00',
            'type' => 'cultural',
            'location' => 'Community Amphitheater',
            'ai_flyer_path' => $flyerPath,
        ]);

        $response->assertRedirect(route('events.index'));

        $this->assertDatabaseHas('events', [
            'title' => 'Festival of Oral Narratives',
            'organizer_id' => $this->user->id,
            'image' => $flyerPath,
        ]);
    }

    public function test_authenticated_user_can_generate_captivating_live_stream_caption()
    {
        $response = $this->actingAs($this->user)->postJson(route('live-stream.ai-suggest-caption'), [
            'title' => 'Live Griot Chant and Kora Session',
            'description' => 'Exploring West African mandinka kora scales and oral lineage epics.',
            'category' => 'Heritage Music',
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'caption',
        ]);
        $this->assertTrue($response->json('success'));
        $this->assertNotEmpty($response->json('caption'));
    }

    public function test_authenticated_user_can_generate_live_stream_thumbnail()
    {
        $response = $this->actingAs($this->user)->postJson(route('live-stream.ai-generate-thumbnail'), [
            'title' => 'Live Traditional Pottery Masterclass',
            'category' => 'Crafts',
            'notes' => 'Hands on shaping of ancestral clay pots.',
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'image_path',
            'image_url',
        ]);
        $this->assertTrue($response->json('success'));
        $this->assertNotEmpty($response->json('image_path'));
        $this->assertNotEmpty($response->json('image_url'));
    }

    public function test_live_stream_store_accepts_ai_thumbnail_path()
    {
        $thumbPath = 'stream-thumbnails/ai/test_stream_thumb.svg';

        $response = $this->actingAs($this->user)->post(route('live-stream.store'), [
            'title' => 'Live Calabash Carving Circle',
            'description' => 'Interactive livestream carving gourds with ancestral symbols.',
            'category' => 'Artisans',
            'ai_thumbnail_path' => $thumbPath,
        ]);

        $this->assertDatabaseHas('live_streams', [
            'title' => 'Live Calabash Carving Circle',
            'user_id' => $this->user->id,
            'thumbnail' => $thumbPath,
        ]);
    }
}
