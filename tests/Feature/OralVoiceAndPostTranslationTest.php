<?php

namespace Tests\Feature;

use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OralVoiceAndPostTranslationTest extends TestCase
{
    use RefreshDatabase;

    public function test_ai_voice_format_structures_timeline_story(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson(route('timeline.ai-voice-format'), [
            'raw_transcript' => 'Um, so you know, my grandmother taught me how to make Akara in Ibadan. Like you know, it was truly wonderful and sacred.',
            'type' => 'timeline_post',
            'chapter' => 'Culinary Heritage',
            'location' => 'Ibadan, Nigeria',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);

        $json = $response->json();
        $this->assertNotEmpty($json['data']['formatted_content']);
        $this->assertStringNotContainsString('Um, so you know', $json['data']['formatted_content']);
        $this->assertNotEmpty($json['data']['chapter']);
    }

    public function test_ai_voice_format_structures_cultural_hub_record(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson(route('cultural-hub.ai-voice-format'), [
            'raw_transcript' => 'Our elders in Billiri recount how the Tangale warrior dances were performed before harvesting each autumn. It involved drum rhythms and traditional attire.',
            'type' => 'culture_record',
            'cultural_group' => 'Tangale',
            'region' => 'Gombe State, Nigeria',
            'category' => 'Traditional Performance',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);

        $json = $response->json();
        $this->assertNotEmpty($json['data']['proposed_title']);
        $this->assertNotEmpty($json['data']['formatted_description']);
        $this->assertEquals('Tangale', $json['data']['cultural_group']);
        $this->assertStringContainsString('Gombe State', $json['data']['region']);
        $this->assertNotEmpty($json['data']['suggested_tags']);
    }

    public function test_ai_voice_format_validation_requires_transcript_or_audio(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson(route('timeline.ai-voice-format'), [
            'raw_transcript' => '   ',
        ]);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
            ]);
    }

    public function test_user_can_create_post_with_language_and_oral_transcript(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('timeline.store'), [
            'content' => 'Ogun is the orisha of iron, metal work, and technological progress.',
            'chapter' => 'Spiritual Heritage',
            'language' => 'Yoruba',
            'oral_transcript' => 'Ogun is the orisha of iron and metal work.',
            'is_ai_formatted' => '1',
            'privacy' => 'public',
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('posts', [
            'user_id' => $user->id,
            'language' => 'Yoruba',
            'oral_transcript' => 'Ogun is the orisha of iron and metal work.',
            'is_ai_formatted' => true,
            'chapter' => 'Spiritual Heritage',
        ]);
    }

    public function test_translate_post_endpoint_translates_to_requested_language(): void
    {
        $author = User::factory()->create();
        $viewer = User::factory()->create();

        $post = Post::factory()->create([
            'user_id' => $author->id,
            'content' => 'Bonjour mon ami! Bienvenue sur Kulturegram, nous celebrons nos traditions.',
            'language' => 'French',
            'chapter' => 'Community',
        ]);

        $response = $this->actingAs($viewer)->postJson(route('timeline.translate', $post), [
            'target_language' => 'English',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'target_language' => 'English',
            ]);

        $json = $response->json();
        $this->assertNotEmpty($json['translated_text']);
        $this->assertNotEmpty($json['formatted_translated_content']);
    }

    public function test_translate_post_defaults_to_viewer_preferred_language(): void
    {
        $author = User::factory()->create();
        $viewer = User::factory()->create([
            'preferences' => ['preferred_language' => 'Hausa'],
        ]);

        $post = Post::factory()->create([
            'user_id' => $author->id,
            'content' => 'Greetings to all family and elders in our village.',
            'language' => 'English',
        ]);

        $response = $this->actingAs($viewer)->postJson(route('timeline.translate', $post), []);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'target_language' => 'Hausa',
            ]);
    }

    public function test_timeline_feed_renders_translation_bar_and_oral_badge(): void
    {
        $author = User::factory()->create();
        $viewer = User::factory()->create([
            'preferences' => ['preferred_language' => 'English'],
        ]);

        $post = Post::factory()->create([
            'user_id' => $author->id,
            'content' => 'E ku aaro gbogbo eniyan, e je ki a se itoju ede ati asa wa.',
            'language' => 'Yoruba',
            'privacy' => 'public',
            'is_ai_formatted' => true,
            'oral_transcript' => 'E ku aaro gbogbo eniyan',
        ]);

        $response = $this->actingAs($viewer)->get(route('timeline.index', ['filter' => 'all']));

        $response->assertStatus(200);
        $response->assertSee('See Translation (to English)');
        $response->assertSee('Oral Story');
        $response->assertSee('post-original-content-' . $post->id, false);
        $response->assertSee('post-translated-content-' . $post->id, false);
        $response->assertSee('togglePostTranslation(' . $post->id, false);
    }
}
