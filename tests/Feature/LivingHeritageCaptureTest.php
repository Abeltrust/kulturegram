<?php

namespace Tests\Feature;

use App\Models\Culture;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class LivingHeritageCaptureTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
    }

    /**
     * Helper to create a fake audio file without relying on external system extensions.
     */
    protected function fakeAudioFile(string $name = 'oral_story.mp3', int $sizeKb = 200): UploadedFile
    {
        return UploadedFile::fake()->create($name, $sizeKb, 'audio/mpeg');
    }

    /**
     * Helper to create a fake image file without requiring GD extension.
     */
    protected function fakeImageFile(string $name = 'artifact.png'): UploadedFile
    {
        // 1x1 transparent PNG binary
        $png = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==');
        return UploadedFile::fake()->createWithContent($name, $png);
    }

    public function test_guest_is_redirected_from_living_heritage_wizard(): void
    {
        $response = $this->get(route('cultural-hub.living-heritage.create'));
        $response->assertRedirect(route('login'));
    }

    public function test_authenticated_user_can_render_living_heritage_wizard(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('cultural-hub.living-heritage.create'));

        $response->assertStatus(200);
        $response->assertSee('Living Heritage Capture');
        $response->assertSee('Stage 1: Capture Oral Story');
        $response->assertSee('Meta Global AI Developer Hackathon');
    }

    public function test_living_heritage_index_renders_with_status_indicators(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('cultural-hub.living-heritage.index'));

        $response->assertStatus(200);
        $response->assertSee('Living Heritage Capture');
        $response->assertSee('Preserve the stories. Protect the knowledge.');
    }

    public function test_transcribe_audio_fails_gracefully_when_api_key_is_missing(): void
    {
        Config::set('meta_ai.api_key', ''); // Missing key

        $user = User::factory()->create();
        $audio = $this->fakeAudioFile();

        $response = $this->actingAs($user)->postJson(route('cultural-hub.living-heritage.transcribe'), [
            'audio' => $audio,
            'consent' => '1',
        ]);

        $response->assertStatus(503);
        $response->assertJson([
            'success' => false,
            'configured' => false,
        ]);
        $response->assertJsonFragment([
            'error' => 'Meta Model API key is not configured. Please add META_MODEL_API_KEY to your environment to enable audio transcription.',
        ]);

        // Authentic audio must still be preserved on disk
        Storage::disk('public')->assertExists('cultures/audios/' . $audio->hashName());
    }

    public function test_transcribe_audio_requires_cultural_consent(): void
    {
        $user = User::factory()->create();
        $audio = $this->fakeAudioFile();

        $response = $this->actingAs($user)->postJson(route('cultural-hub.living-heritage.transcribe'), [
            'audio' => $audio,
            // consent missing
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['consent']);
    }

    public function test_transcribe_audio_validates_file_type(): void
    {
        $user = User::factory()->create();
        $invalidFile = UploadedFile::fake()->create('malicious.exe', 100, 'application/x-msdownload');

        $response = $this->actingAs($user)->postJson(route('cultural-hub.living-heritage.transcribe'), [
            'audio' => $invalidFile,
            'consent' => '1',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['audio']);
    }

    public function test_transcribe_audio_succeeds_with_mocked_meta_api(): void
    {
        Config::set('meta_ai.api_key', 'test_meta_key_12345');
        Config::set('meta_ai.base_url', 'https://api.meta.ai/v1');

        Http::fake([
            'https://api.meta.ai/v1/audio/transcriptions' => Http::response([
                'text' => 'Elders in Idanre recount the sacred Orosun festival when the Owa wears the beaded crown.',
                'language' => 'yoruba',
                'segments' => [],
            ], 200),
        ]);

        $user = User::factory()->create();
        $audio = $this->fakeAudioFile('festival_story.mp3');

        $response = $this->actingAs($user)->postJson(route('cultural-hub.living-heritage.transcribe'), [
            'audio' => $audio,
            'language_hint' => 'yor',
            'consent' => '1',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'configured' => true,
            'transcript' => 'Elders in Idanre recount the sacred Orosun festival when the Owa wears the beaded crown.',
            'language' => 'yoruba',
        ]);

        // Authentic audio must remain safely stored
        Storage::disk('public')->assertExists('cultures/audios/' . $audio->hashName());
    }

    public function test_prepare_story_with_muse_spark_produces_structured_cultural_record(): void
    {
        Config::set('meta_ai.api_key', 'test_meta_key_12345');
        Config::set('meta_ai.base_url', 'https://api.meta.ai/v1');

        $modelOutputJson = json_encode([
            'name' => 'Orosun Beaded Crown Ceremony',
            'region' => 'Idanre Hills, Ondo State',
            'category' => 'Festivals & Ceremonies',
            'description' => 'Annual festival where the Owa ascends the sacred stone steps adorned with the ancient bird crown.',
            'language' => 'Yoruba (Idanre Dialect)',
            'historical_period' => '17th Century to Present',
            'significance' => 'Communal unity and spiritual ancestral link',
            'rituals' => 'Climbing the 682 stone steps, pouring palm wine libation',
            'community_role' => 'King and Chief Priests',
            'transmission_methods' => 'Oral songs and youth apprenticeship',
            'challenges' => 'Urban migration of youth bead crafters',
            'tags' => ['orosun', 'idanre', 'beaded_crown', 'yoruba'],
            'missing_information_warnings' => ['Exact seasonal date not specified'],
            'suggested_follow_up_questions' => ['Which priestly guild weaves the seven sacred birds?'],
        ]);

        Http::fake([
            'https://api.meta.ai/v1/chat/completions' => Http::response([
                'choices' => [
                    [
                        'message' => [
                            'content' => $modelOutputJson,
                        ],
                    ],
                ],
            ], 200),
        ]);

        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson(route('cultural-hub.living-heritage.prepare'), [
            'story' => 'In the hills of Idanre, the Owa wears the beaded crown with seven sacred birds only during Orosun festival. Elders recount that priests climb 682 steps carrying calabashes of palm wine.',
            'region' => 'Idanre Hills',
            'language' => 'Yoruba',
            'consent' => '1',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'configured' => true,
            'record' => [
                'name' => 'Orosun Beaded Crown Ceremony',
                'category' => 'Festivals & Ceremonies',
                'language' => 'Yoruba (Idanre Dialect)',
            ],
        ]);
        $response->assertJsonPath('record.tags.0', 'orosun');
    }

    public function test_muse_wonder_generates_bounded_preservation_plan(): void
    {
        Config::set('meta_ai.api_key', 'test_meta_key_12345');
        Config::set('meta_ai.base_url', 'https://api.meta.ai/v1');

        $wonderPlanJson = json_encode([
            'missing_information' => ['Names of the royal bead guild'],
            'follow_up_questions' => ['How are the 7 birds positioned on the crown?'],
            'claims_requiring_verification' => ['Origin date of the 682 stone steps'],
            'preservation_summary' => 'Sacred ceremonial ascent celebrating the ancestral connection of Idanre royalty.',
            'preservation_checklist' => [
                ['task' => 'Record elder oral chants of the 682 steps', 'priority' => 'high', 'completed' => false],
                ['task' => 'Photograph royal crown beadwork patterns', 'priority' => 'medium', 'completed' => false],
            ],
        ]);

        Http::fake([
            'https://api.meta.ai/v1/chat/completions' => Http::response([
                'choices' => [
                    [
                        'message' => [
                            'content' => $wonderPlanJson,
                        ],
                    ],
                ],
            ], 200),
        ]);

        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson(route('cultural-hub.living-heritage.wonder-plan'), [
            'name' => 'Orosun Beaded Crown Ceremony',
            'region' => 'Idanre Hills',
            'category' => 'Festivals & Ceremonies',
            'description' => 'Annual festival where the Owa ascends the stone steps.',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'configured' => true,
            'preservation_plan' => [
                'missing_information' => ['Names of the royal bead guild'],
                'preservation_summary' => 'Sacred ceremonial ascent celebrating the ancestral connection of Idanre royalty.',
            ],
        ]);
    }

    public function test_muse_image_generates_illustration_with_explicit_ai_attribution(): void
    {
        Config::set('meta_ai.api_key', 'test_meta_key_12345');
        Config::set('meta_ai.base_url', 'https://api.meta.ai/v1');

        // 1x1 png in base64
        $fakeB64 = 'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==';

        Http::fake([
            'https://api.meta.ai/v1/images/generations' => Http::response([
                'data' => [
                    ['b64_json' => $fakeB64],
                ],
            ], 200),
        ]);

        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson(route('cultural-hub.living-heritage.illustration'), [
            'story' => 'King wearing royal beaded crown ascending stone steps in morning mist.',
            'cultural_context' => 'Idanre Hills',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'configured' => true,
            'is_ai_generated' => true,
        ]);
        $response->assertJsonFragment([
            'disclaimer' => 'AI-Generated Cultural Illustration. This is an artistic representation and not an authentic historical photograph.',
        ]);

        $imagePath = $response->json('image_path');
        $this->assertNotNull($imagePath);
        Storage::disk('public')->assertExists($imagePath);
    }

    public function test_sam_segments_authentic_artifact_without_authenticity_claims(): void
    {
        Config::set('meta_ai.api_key', 'test_meta_key_12345');
        Config::set('meta_ai.base_url', 'https://api.meta.ai/v1');

        $fakeCutoutB64 = 'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==';

        Http::fake([
            'https://api.meta.ai/v1/models/sam-3.1/segment' => Http::response([
                'segmented_b64' => $fakeCutoutB64,
                'mask_b64' => $fakeCutoutB64,
            ], 200),
        ]);

        $user = User::factory()->create();
        $artifactPhoto = $this->fakeImageFile('real_calabash.png');

        $response = $this->actingAs($user)->postJson(route('cultural-hub.living-heritage.segment'), [
            'artifact_image' => $artifactPhoto,
            'object_prompt' => 'carved calabash',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'configured' => true,
            'object_label' => 'carved calabash',
        ]);
        $this->assertStringContainsStringIgnoringCase('not constitute proof of cultural authenticity', $response->json('disclaimer'));

        // Both original and segmented images must be saved
        $segmentedPath = $response->json('segmented_path');
        $originalPath = $response->json('original_path');
        $this->assertNotNull($segmentedPath);
        $this->assertNotNull($originalPath);
        Storage::disk('public')->assertExists($segmentedPath);
        Storage::disk('public')->assertExists($originalPath);
    }

    public function test_draft_saving_preserves_draft_status_and_does_not_publish(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson(route('cultural-hub.living-heritage.store'), [
            'name' => 'Sacred Talking Drum Chants',
            'region' => 'Yorubaland',
            'category' => 'Music & Dance',
            'description' => 'The Gbedu and Iya Ilu drums speak in tonal sentences during royal ceremonies.',
            'raw_story' => 'Spoken account by Master Drummer.',
            'action' => 'draft',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'status' => 'draft',
        ]);

        $this->assertDatabaseHas('cultures', [
            'name' => 'Sacred Talking Drum Chants',
            'status' => 'draft',
            'is_ai_assisted' => true,
            'submitted_by' => $user->id,
        ]);
    }

    public function test_submission_sets_pending_review_and_never_auto_publishes(): void
    {
        $user = User::factory()->create();

        $desc = 'The Gbedu and Iya Ilu drums speak in tonal sentences during royal ceremonies. Each beat carries ancient linguistic cadence passed through generations of master drummers.';

        $response = $this->actingAs($user)->postJson(route('cultural-hub.living-heritage.store'), [
            'name' => 'Sacred Talking Drum Chants',
            'region' => 'Yorubaland',
            'category' => 'Music & Dance',
            'description' => $desc,
            'action' => 'submit',
            'tags' => 'drums,gbedu,royalty',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'status' => 'pending_review',
        ]);

        // CRITICAL: Must be pending_review, NEVER approved
        $this->assertDatabaseHas('cultures', [
            'name' => 'Sacred Talking Drum Chants',
            'status' => 'pending_review',
            'is_ai_assisted' => true,
        ]);

        $culture = Culture::where('name', 'Sacred Talking Drum Chants')->first();
        $this->assertNotEquals('approved', $culture->status);
    }

    public function test_admin_can_approve_living_heritage_submission_through_moderation(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $user = User::factory()->create();

        $culture = Culture::factory()->create([
            'name' => 'Living Heritage Folktale',
            'status' => 'pending_review',
            'is_ai_assisted' => true,
            'submitted_by' => $user->id,
        ]);

        $response = $this->actingAs($admin)->post(route('admin.cultures.approve', $culture));
        $response->assertRedirect();

        $this->assertDatabaseHas('cultures', [
            'id' => $culture->id,
            'status' => 'approved',
        ]);
    }

    public function test_comparison_lab_runs_side_by_side_without_fabricating_outputs(): void
    {
        // Hosted Meta API key configured, open-weight endpoints unconfigured
        Config::set('meta_ai.api_key', 'test_meta_key_12345');
        Config::set('meta_ai.base_url', 'https://api.meta.ai/v1');
        Config::set('meta_ai.open_weight.glimmer.endpoint', ''); // unconfigured
        Config::set('meta_ai.open_weight.spark_open.endpoint', ''); // unconfigured

        Http::fake([
            'https://api.meta.ai/v1/chat/completions' => Http::response([
                'choices' => [
                    [
                        'message' => [
                            'content' => json_encode([
                                'name' => 'Comparison Folklore Item',
                                'category' => 'Oral Traditions',
                                'description' => 'Test summary of the folklore account.',
                            ]),
                        ],
                    ],
                ],
            ], 200),
        ]);

        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson(route('cultural-hub.living-heritage.compare.run'), [
            'story' => 'Long ago in the hills of Idanre, priests climbed stone steps carrying palm wine calabashes.',
        ]);

        $response->assertStatus(200);

        // Hosted Muse Spark succeeded
        $this->assertTrue($response->json('models.muse_spark.success'));
        $this->assertTrue($response->json('models.muse_spark.configured'));

        // Open-weight models truthfully report unconfigured
        $this->assertFalse($response->json('models.muse_glimmer.configured'));
        $this->assertFalse($response->json('models.muse_glimmer.success'));
        $this->assertStringContainsString('not configured', $response->json('models.muse_glimmer.error'));

        $this->assertFalse($response->json('models.muse_spark_open.configured'));
        $this->assertFalse($response->json('models.muse_spark_open.success'));
    }

    public function test_api_credentials_never_appear_in_browser_responses(): void
    {
        $secretKey = 'super_secret_meta_api_key_xyz987654';
        Config::set('meta_ai.api_key', $secretKey);

        $user = User::factory()->create();

        $pages = [
            route('cultural-hub.living-heritage.index'),
            route('cultural-hub.living-heritage.create'),
            route('cultural-hub.living-heritage.compare'),
        ];

        foreach ($pages as $url) {
            $response = $this->actingAs($user)->get($url);
            $response->assertStatus(200);
            $this->assertStringNotContainsString($secretKey, $response->getContent(), "Secret API key leaked in HTML on {$url}");
        }
    }
}
