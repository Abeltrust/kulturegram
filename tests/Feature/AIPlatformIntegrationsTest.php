<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\LiveStream;
use App\Models\LiveStreamMessage;
use App\Models\User;
use App\Models\Community;
use App\Models\VaultItem;
use App\Services\MetaAI\ContentPrivacyProtectionService;
use App\Services\MetaAI\SystemPrivacyAuditService;
use App\Services\MetaAI\LiveStreamModerationService;
use App\Services\MetaAI\CulturalConnectionRecommendationService;
use App\Services\MetaAI\EventDiscoveryService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AIPlatformIntegrationsTest extends TestCase
{
    use RefreshDatabase;

    /*
    |--------------------------------------------------------------------------
    | 1. AI-Powered Live Stream Monitoring & Moderation Tests
    |--------------------------------------------------------------------------
    */

    public function test_viewer_can_post_message_and_system_stores_it(): void
    {
        $host = User::factory()->create();
        $viewer = User::factory()->create();
        $stream = LiveStream::create([
            'title' => 'Yoruba Traditional Drumming & Bata Rhythms',
            'description' => 'Exploring the sacred Bata drums of Oyo heritage.',
            'category' => 'Traditional Music',
            'user_id' => $host->id,
            'is_live' => true,
        ]);

        $response = $this->actingAs($viewer)->postJson(route('live-stream.messages.post', $stream), [
            'message' => 'The talking drum cadence here is completely authentic!',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'moderation' => [
                    'flagged' => false,
                ],
            ]);

        $this->assertDatabaseHas('live_stream_messages', [
            'live_stream_id' => $stream->id,
            'user_id' => $viewer->id,
            'message' => 'The talking drum cadence here is completely authentic!',
            'flag_status' => 'clean',
        ]);
    }

    public function test_live_stream_fast_path_flags_pii_and_cites_rule(): void
    {
        $host = User::factory()->create();
        $viewer = User::factory()->create();
        $stream = LiveStream::create([
            'title' => 'Preserving Hausa Folklore',
            'description' => 'Stories from the northern emirates.',
            'category' => 'Oral History',
            'user_id' => $host->id,
            'is_live' => true,
        ]);

        $response = $this->actingAs($viewer)->postJson(route('live-stream.messages.post', $stream), [
            'message' => 'Call me on 08031234567 or email elder.ibrahim@secret.ng for the private documents',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'moderation' => [
                    'flagged' => true,
                    'category' => 'privacy_violation',
                ],
            ]);

        $this->assertDatabaseHas('live_stream_messages', [
            'live_stream_id' => $stream->id,
            'flag_status' => 'flagged',
            'flag_category' => 'privacy_violation',
            'violated_rule' => LiveStreamModerationService::RULE_PRIVACY,
        ]);
    }

    public function test_stream_host_can_view_flagged_queue_and_dismiss_flag(): void
    {
        $host = User::factory()->create();
        $stream = LiveStream::create([
            'title' => 'Igbo New Yam Festival Cultural Symposium',
            'user_id' => $host->id,
            'is_live' => true,
        ]);

        $msg = LiveStreamMessage::create([
            'live_stream_id' => $stream->id,
            'user_id' => $host->id,
            'message' => 'Debatable cultural statement here.',
            'flag_status' => 'flagged',
            'flag_category' => 'off_topic',
            'flag_reason' => 'Contextually divergent',
            'flag_rule' => 'Guidelines §4.2',
        ]);

        // Host views flagged queue
        $queueResponse = $this->actingAs($host)->getJson(route('live-stream.messages.flagged', $stream));
        $queueResponse->assertStatus(200)
            ->assertJsonFragment(['id' => $msg->id]);

        // Host dismisses flag (advisory AI flag dismissed by human host)
        $dismissResponse = $this->actingAs($host)->postJson(route('live-stream.messages.dismiss', [$stream, $msg]), [
            'notes' => 'Permissible cultural debate.',
        ]);

        $dismissResponse->assertStatus(200);
        $this->assertEquals('dismissed', $msg->fresh()->flag_status);
        $this->assertEquals($host->id, $msg->fresh()->reviewed_by);
    }

    public function test_unauthorized_user_cannot_dismiss_or_remove_stream_message(): void
    {
        $host = User::factory()->create();
        $intruder = User::factory()->create(['is_admin' => false]);
        $stream = LiveStream::create([
            'title' => 'Ancient Benin Bronze Casting',
            'user_id' => $host->id,
            'is_live' => true,
        ]);

        $msg = LiveStreamMessage::create([
            'live_stream_id' => $stream->id,
            'user_id' => $host->id,
            'message' => 'Some message',
            'flag_status' => 'flagged',
        ]);

        $response = $this->actingAs($intruder)->postJson(route('live-stream.messages.dismiss', [$stream, $msg]));
        $response->assertStatus(403);
    }

    /*
    |--------------------------------------------------------------------------
    | 2. Content Privacy Protection & System Privacy Audit Tests
    |--------------------------------------------------------------------------
    */

    public function test_content_privacy_scanner_detects_pii_and_masks_output(): void
    {
        $scanner = app(ContentPrivacyProtectionService::class);
        $rawText = "Please reach our elder on +234 802 345 6789 or admin@kulturegram.org. Password: SecretPass123!";

        $result = $scanner->scanContent($rawText);

        $this->assertTrue($result['has_pii']);
        $this->assertContains('phone_number', $result['detected_types']);
        $this->assertContains('email_address', $result['detected_types']);
        $this->assertContains('credentials', $result['detected_types']);
        $this->assertStringContainsString('[REDACTED_PHONE]', $result['masked_preview']);
        $this->assertStringContainsString('[REDACTED_EMAIL]', $result['masked_preview']);
        $this->assertStringContainsString('[REDACTED_CREDENTIALS]', $result['masked_preview']);
    }

    public function test_system_privacy_audit_runs_automated_checks(): void
    {
        $auditService = app(SystemPrivacyAuditService::class);
        $audit = $auditService->runFullAudit();

        $this->assertArrayHasKey('score', $audit);
        $this->assertArrayHasKey('automated_checks', $audit);
        $this->assertArrayHasKey('ai_synthesis', $audit);
        $this->assertGreaterThanOrEqual(80, $audit['score']);
        $this->assertCount(6, $audit['automated_checks']);
    }

    public function test_non_admin_cannot_access_privacy_audit_dashboard(): void
    {
        $user = User::factory()->create(['is_admin' => false]);

        $response = $this->actingAs($user)->get(route('admin.privacy-audit.index'));
        $response->assertStatus(403);
    }

    public function test_admin_can_access_privacy_audit_dashboard(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $response = $this->actingAs($admin)->get(route('admin.privacy-audit.index'));
        $response->assertStatus(200);
        $response->assertSee('AI Privacy Protection');
        $response->assertSee('System Policy Audit');
    }

    /*
    |--------------------------------------------------------------------------
    | 3. AI Cultural Connection Recommendations & Privacy Opt-out Tests
    |--------------------------------------------------------------------------
    */

    public function test_user_receives_explainable_recommendations_based_on_interests(): void
    {
        $currentUser = User::factory()->create([
            'cultural_interests' => ['Folklore', 'Oral Traditions', 'Yoruba Music'],
            'languages' => ['English', 'Yoruba'],
            'cultural_background' => 'Yoruba',
        ]);

        $matchedUser = User::factory()->create([
            'cultural_interests' => ['Folklore', 'Indigenous Poetry'],
            'languages' => ['Yoruba'],
            'cultural_background' => 'Yoruba',
        ]);

        $unrelatedUser = User::factory()->create([
            'cultural_interests' => ['Nordic Mythology'],
            'languages' => ['Swedish'],
            'cultural_background' => 'Scandinavian',
        ]);

        $recService = app(CulturalConnectionRecommendationService::class);
        $result = $recService->recommendUsers($currentUser, 5);

        $this->assertTrue($result['enabled']);
        $this->assertNotEmpty($result['recommendations']);
        $firstRec = $result['recommendations'][0];
        $this->assertEquals($matchedUser->id, $firstRec['user']['id']);
        $this->assertStringContainsString('Recommended because you', $firstRec['explanation']);
    }

    public function test_user_can_dismiss_recommendation_and_it_is_excluded(): void
    {
        $user = User::factory()->create([
            'cultural_interests' => ['Pottery', 'Clay Sculptures'],
        ]);
        $peer = User::factory()->create([
            'cultural_interests' => ['Pottery'],
        ]);

        $recService = app(CulturalConnectionRecommendationService::class);

        // Record dismissal
        $recService->dismiss($user, 'user', $peer->id);

        $result = $recService->recommendUsers($user, 5);
        $matchedIds = array_column(array_column($result['recommendations'], 'user'), 'id');

        $this->assertNotContains($peer->id, $matchedIds);
    }

    public function test_user_can_opt_out_of_recommendations_completely(): void
    {
        $user = User::factory()->create();
        $recService = app(CulturalConnectionRecommendationService::class);

        // Toggle opt-out
        $recService->toggleOptIn($user, false);
        $this->assertTrue($recService->isOptedOut($user->fresh()));

        $response = $this->actingAs($user)->getJson(route('cultural-discovery.recommendations'));
        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'opted_out' => true,
                'users' => [],
                'communities' => [],
            ]);
    }

    /*
    |--------------------------------------------------------------------------
    | 4. AI-Assisted Event Discovery & Organizer Reach Optimizer Tests
    |--------------------------------------------------------------------------
    */

    public function test_event_discovery_matches_public_events_with_user_interests(): void
    {
        $organizer = User::factory()->create();
        $attendee = User::factory()->create([
            'cultural_interests' => ['Textile Weaving', 'Adire Patterns'],
            'cultural_background' => 'Yoruba',
        ]);

        $matchingEvent = Event::create([
            'title' => 'Authentic Adire Indigo Dyeing Workshop',
            'description' => 'Hands-on exploration of Yoruba tie-dye and resist textile patterns.',
            'event_date' => now()->addDays(5)->toDateString(),
            'event_time' => '14:00',
            'location' => 'Lagos Cultural Center',
            'type' => 'workshop',
            'organizer_id' => $organizer->id,
            'reach_tags' => ['Adire', 'Textile Weaving', 'Yoruba Heritage'],
        ]);

        $discoveryService = app(EventDiscoveryService::class);
        $result = $discoveryService->recommendEvents($attendee, 5);

        $this->assertTrue($result['enabled']);
        $this->assertNotEmpty($result['recommendations']);
        $firstMatch = $result['recommendations'][0];
        $this->assertEquals($matchingEvent->id, $firstMatch['event']['id']);
        $this->assertStringContainsString('Recommended because you', $firstMatch['explanation']);
    }

    public function test_organizer_can_optimize_event_reach(): void
    {
        $organizer = User::factory()->create();

        $response = $this->actingAs($organizer)->postJson(route('events.ai-optimize'), [
            'title' => 'Calabar Masquerade and Dance Practice',
            'description' => 'Practice for the Ekpe masquerade dance and traditional costumes.',
            'type' => 'cultural',
            'location' => 'Calabar Heritage Grounds',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'optimized_title',
                    'optimized_description',
                    'reach_tags',
                    'target_communities',
                    'timing_suggestions',
                ],
            ]);
    }
}
