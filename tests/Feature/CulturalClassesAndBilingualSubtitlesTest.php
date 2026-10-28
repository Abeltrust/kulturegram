<?php

namespace Tests\Feature;

use App\Models\CulturalClass;
use App\Models\User;
use App\Models\Post;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Http;
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

        Http::fake([
            'https://api.meta.ai/v1/chat/completions' => Http::response([
                'choices' => [
                    [
                        'message' => [
                            'content' => json_encode([
                                'summary' => 'Interactive cultural class exploring greetings and conversational etiquette.',
                                'key_takeaways' => [
                                    'Respectful greetings build community bonds',
                                    'Pronunciation clarity conveys warmth'
                                ],
                                'subtitles' => [
                                    [
                                        'start' => 0.0,
                                        'end' => 4.0,
                                        'text' => 'Hujambo! Welcome to our cultural class.',
                                        'source_phrase' => 'Hello',
                                        'target_phrase' => 'Hujambo',
                                    ],
                                    [
                                        'start' => 4.0,
                                        'end' => 8.5,
                                        'text' => 'When greeting elders, say Shikamoo with honor.',
                                        'source_phrase' => 'Respectful elder greeting',
                                        'target_phrase' => 'Shikamoo',
                                    ],
                                ],
                                'vocabulary' => [
                                    [
                                        'source' => 'Hello',
                                        'target' => 'Hujambo',
                                        'language' => 'Swahili',
                                        'pronunciation' => 'Hoo-jahm-boh',
                                        'context' => 'General polite greeting.',
                                    ],
                                    [
                                        'source' => 'Respectful greeting to elder',
                                        'target' => 'Shikamoo',
                                        'language' => 'Swahili',
                                        'pronunciation' => 'Shee-kah-moh',
                                        'context' => 'Special deference greeting for elders.',
                                    ],
                                ],
                            ]),
                        ],
                    ],
                ],
            ], 200),
            'https://api.meta.ai/v1/audio/transcriptions' => Http::response([
                'text' => 'Barka da warhaka! Welcome to today cultural class in Hausa.',
                'language' => 'hausa',
                'segments' => [],
            ], 200),
            'https://api.meta.ai/v1/images/generations' => Http::response([
                'data' => [
                    ['url' => 'https://images.unsplash.com/photo-1547471080-7cc2caa01a7e?auto=format&fit=crop&q=80&w=1200'],
                ],
            ], 200),
            '*' => Http::response(['success' => true], 200),
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
        $createPage = $this->actingAs($this->instructor)->get(route('education.classes.create'));
        $createPage->assertStatus(200);
        $createPage->assertSee('Go Live Instantly');
        $createPage->assertSee('Schedule Live Class');

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
        $this->assertEmpty($class->timed_subtitles);
        $this->assertEmpty($class->bilingual_vocabulary);
        Storage::disk('public')->assertExists($class->video_url);

        $response->assertRedirect(route('education.classes.show', $class->slug));
    }

    public function test_instructor_can_upload_video_in_chunks_and_publish()
    {
        Storage::fake('public');

        $chunk = UploadedFile::fake()->create('chunk0.bin', 512, 'application/octet-stream');

        $chunkRes = $this->actingAs($this->instructor)->post(route('education.classes.upload-chunk'), [
            'chunk' => $chunk,
            'upload_id' => 'test_chunk_upload_12345',
            'chunk_index' => 0,
            'total_chunks' => 1,
            'filename' => 'recorded_class.webm',
        ]);

        $chunkRes->assertStatus(200);
        $chunkRes->assertJson(['success' => true, 'completed' => true]);
        $tempVideoPath = $chunkRes->json('temp_video_path');
        $this->assertNotEmpty($tempVideoPath);

        $response = $this->actingAs($this->instructor)->post(route('education.classes.store'), [
            'title' => 'Recorded Hausa Class',
            'cultural_focus' => 'Hausa Oral History',
            'target_language' => 'Hausa',
            'source_language' => 'English',
            'level' => 'beginner',
            'price_type' => 'free',
            'temp_video_path' => $tempVideoPath,
            'video_duration' => 55,
        ]);

        $class = CulturalClass::where('title', 'Recorded Hausa Class')->first();
        $this->assertNotNull($class);
        $this->assertNotEmpty($class->video_url);
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

    public function test_instructor_can_sync_and_edit_subtitles_for_published_video()
    {
        $class = CulturalClass::create([
            'instructor_id' => $this->instructor->id,
            'title' => 'Hausa Greetings Video Sync',
            'cultural_focus' => 'Hausa Greetings',
            'target_language' => 'Hausa',
            'level' => 'beginner',
            'price_type' => 'free',
            'price' => 0.00,
            'duration_seconds' => 45,
            'timed_subtitles' => [
                ['start' => 0.0, 'end' => 4.0, 'text' => 'Old generic subtitle'],
            ],
            'is_published' => true,
        ]);

        $syncRes = $this->actingAs($this->instructor)->postJson(route('education.classes.sync-subtitles', $class), [
            'transcript' => 'Sannu da zuwa! In this video we learn how to say Ina kwana in Hausa.',
            'subtitles' => [
                [
                    'start' => 0.0,
                    'end' => 4.5,
                    'text' => 'Sannu da zuwa! In this video we learn how to say Ina kwana in Hausa.',
                    'target_phrase' => 'Sannu da zuwa',
                    'source_phrase' => 'Welcome',
                ],
            ],
            'video_duration' => 52,
            'regenerate_with_ai' => false,
        ]);

        $syncRes->assertStatus(200);
        $syncRes->assertJson(['success' => true]);

        $fresh = $class->fresh();
        $this->assertEquals(52, $fresh->duration_seconds);
        $this->assertEquals('Sannu da zuwa! In this video we learn how to say Ina kwana in Hausa.', $fresh->timed_subtitles[0]['text']);
    }
}
