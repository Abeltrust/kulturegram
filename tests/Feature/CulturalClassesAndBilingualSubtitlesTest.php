<?php

namespace Tests\Feature;

use App\Models\CulturalClass;
use App\Models\User;
use App\Models\Post;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CulturalClassesAndBilingualSubtitlesTest extends TestCase
{
    use RefreshDatabase;

    protected User $instructor;
    protected User $student;

    protected function setUp(): void
    {
        parent::setUp();

        $this->instructor = User::factory()->create([
            'name' => 'Mallam Aminu',
            'email' => 'aminu@example.com',
        ]);

        $this->student = User::factory()->create([
            'name' => 'Chidi Okeke',
            'email' => 'chidi@example.com',
        ]);
    }

    public function test_educational_hub_displays_featured_cultural_classes()
    {
        $class = CulturalClass::create([
            'instructor_id' => $this->instructor->id,
            'title' => 'Hausa Everyday Greetings',
            'cultural_focus' => 'Hausa Etiquette',
            'target_language' => 'Hausa',
            'level' => 'beginner',
            'price_type' => 'free',
            'price' => 0.00,
            'duration_seconds' => 60,
            'is_published' => true,
        ]);

        $response = $this->get(route('education.index'));

        $response->assertStatus(200);
        $response->assertSee('Cultural Classes & Language Studio', false);
        $response->assertSee('Hausa Everyday Greetings');
    }

    public function test_cultural_classes_catalog_filters_by_language_and_pricing()
    {
        CulturalClass::create([
            'instructor_id' => $this->instructor->id,
            'title' => 'Hausa Market Phrases',
            'cultural_focus' => 'Hausa Trading',
            'target_language' => 'Hausa',
            'level' => 'beginner',
            'price_type' => 'free',
            'price' => 0.00,
            'is_published' => true,
        ]);

        CulturalClass::create([
            'instructor_id' => $this->instructor->id,
            'title' => 'Yoruba Royal Greetings',
            'cultural_focus' => 'Yoruba Palace',
            'target_language' => 'Yoruba',
            'level' => 'intermediate',
            'price_type' => 'paid',
            'price' => 12.00,
            'is_published' => true,
        ]);

        // Filter Hausa
        $response = $this->get(route('education.classes.index', ['language' => 'Hausa']));
        $response->assertStatus(200);
        $response->assertSee('Hausa Market Phrases');
        $response->assertDontSee('Yoruba Royal Greetings');

        // Filter Paid
        $responsePaid = $this->get(route('education.classes.index', ['price_type' => 'paid']));
        $responsePaid->assertStatus(200);
        $responsePaid->assertSee('Yoruba Royal Greetings');
        $responsePaid->assertDontSee('Hausa Market Phrases');
    }

    public function test_instructor_can_publish_cultural_class_with_subtitles_and_timeline_share()
    {
        $response = $this->actingAs($this->instructor)->post(route('education.classes.store'), [
            'title' => 'Swahili Coastal Greetings & Etiquette',
            'cultural_focus' => 'Swahili Coast Respect',
            'target_language' => 'Swahili',
            'source_language' => 'English',
            'level' => 'beginner',
            'price_type' => 'paid',
            'price' => 9.99,
            'description' => 'Learn how to greet elders and neighbors in traditional Swahili culture.',
            'raw_transcript' => 'Hujambo! Welcome to Swahili lesson. When you greet an elder, say Shikamoo, which means I hold your feet with respect. The elder replies Marahaba.',
            'share_to_timeline' => '1',
        ]);

        $class = CulturalClass::where('target_language', 'Swahili')->first();
        $this->assertNotNull($class);
        $this->assertEquals('Swahili Coastal Greetings & Etiquette', $class->title);
        $this->assertEquals(9.99, (float) $class->price);
        $this->assertNotEmpty($class->timed_subtitles);
        $this->assertNotEmpty($class->bilingual_vocabulary);
        $this->assertTrue($class->shared_to_timeline);

        // Verify timeline post was created
        $post = Post::where('user_id', $this->instructor->id)->where('chapter', 'Cultural Class')->first();
        $this->assertNotNull($post);
        $this->assertStringContainsString('Swahili Coastal Greetings', $post->content);

        $response->assertRedirect(route('education.classes.show', $class->slug));
    }

    public function test_instructor_can_publish_class_with_actual_mp4_video_upload()
    {
        Storage::fake('public');

        $fakeVideo = UploadedFile::fake()->create('hausa_lesson.mp4', 1024, 'video/mp4');

        $response = $this->actingAs($this->instructor)->post(route('education.classes.store'), [
            'title' => 'Hausa Advanced Pronunciation',
            'cultural_focus' => 'Hausa Phonetics',
            'target_language' => 'Hausa',
            'source_language' => 'English',
            'level' => 'intermediate',
            'price_type' => 'free',
            'price' => 0.00,
            'description' => 'Master tones and vowels in Hausa.',
            'video' => $fakeVideo,
        ]);

        $class = CulturalClass::where('title', 'Hausa Advanced Pronunciation')->first();
        $this->assertNotNull($class);
        $this->assertNotNull($class->video_url);
        Storage::disk('public')->assertExists($class->video_url);

        $response->assertRedirect(route('education.classes.show', $class->slug));
    }

    public function test_interactive_class_show_page_displays_video_and_subtitles()
    {
        $class = CulturalClass::create([
            'instructor_id' => $this->instructor->id,
            'title' => 'Igbo Traditional Salutations',
            'cultural_focus' => 'Igbo Heritage',
            'target_language' => 'Igbo',
            'level' => 'beginner',
            'price_type' => 'free',
            'price' => 0.00,
            'duration_seconds' => 80,
            'timed_subtitles' => [
                [
                    'start' => 0.0,
                    'end' => 5.0,
                    'text' => 'Ndewo! Welcome to our Igbo class.',
                    'source_phrase' => 'Hello',
                    'target_phrase' => 'Ndewo',
                ],
            ],
            'bilingual_vocabulary' => [
                [
                    'source' => 'Hello / Greetings',
                    'target' => 'Ndewo',
                    'language' => 'Igbo',
                    'pronunciation' => 'N-deh-woh',
                    'context' => 'General polite greeting.',
                ],
            ],
            'is_published' => true,
        ]);

        $response = $this->get(route('education.classes.show', $class->slug));

        $response->assertStatus(200);
        $response->assertSee('Igbo Traditional Salutations');
        $response->assertSee('Ndewo! Welcome to our Igbo class.');
        $response->assertSee('Live Synchronized Transcript');
        $response->assertSee('Bilingual Vocabulary & Phrase Cards', false);
    }

    public function test_subtitles_vtt_endpoint_returns_valid_webvtt_stream()
    {
        $class = CulturalClass::create([
            'instructor_id' => $this->instructor->id,
            'title' => 'Hausa VTT Test',
            'cultural_focus' => 'Hausa',
            'target_language' => 'Hausa',
            'level' => 'beginner',
            'price_type' => 'free',
            'price' => 0.00,
            'timed_subtitles' => [
                [
                    'start' => 0.0,
                    'end' => 4.0,
                    'text' => 'Barka da safe!',
                ],
            ],
            'is_published' => true,
        ]);

        $response = $this->get(route('education.classes.subtitles', $class));

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/vtt; charset=utf-8');
        $this->assertStringContainsString('WEBVTT', $response->getContent());
        $this->assertStringContainsString('Barka da safe!', $response->getContent());
    }

    public function test_learner_can_enroll_in_cultural_class()
    {
        $class = CulturalClass::create([
            'instructor_id' => $this->instructor->id,
            'title' => 'Paid Yoruba Masterclass',
            'cultural_focus' => 'Yoruba',
            'target_language' => 'Yoruba',
            'level' => 'intermediate',
            'price_type' => 'paid',
            'price' => 15.00,
            'is_published' => true,
        ]);

        $this->assertFalse($class->isEnrolledBy($this->student));

        $response = $this->actingAs($this->student)->postJson(route('education.classes.enroll', $class));

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $this->assertTrue($class->fresh()->isEnrolledBy($this->student));
        $this->assertEquals(1, $class->fresh()->enrollments_count);
    }

    public function test_ajax_ai_transcribe_and_studio_background_endpoints()
    {
        // Test transcription endpoint
        $transcribeRes = $this->actingAs($this->instructor)->postJson(route('education.classes.ai-transcribe'), [
            'target_language' => 'Hausa',
            'cultural_focus' => 'Hausa Respect',
            'transcript' => 'In Hausa, when you meet an elder, say Ina kwana which means Good morning. The elder says Lafiya lau.',
        ]);

        $transcribeRes->assertStatus(200);
        $transcribeRes->assertJsonStructure([
            'success',
            'subtitles',
            'vocabulary',
            'summary',
        ]);
        $this->assertTrue($transcribeRes->json('success'));
        $this->assertNotEmpty($transcribeRes->json('subtitles'));

        // Test AI studio background endpoint
        $bgRes = $this->actingAs($this->instructor)->postJson(route('education.classes.ai-studio-background'), [
            'cultural_theme' => 'Ancient Kano Mud Wall Courtyard',
        ]);

        $bgRes->assertStatus(200);
        $this->assertTrue($bgRes->json('success'));
        $this->assertNotEmpty($bgRes->json('image_url'));
    }
}
