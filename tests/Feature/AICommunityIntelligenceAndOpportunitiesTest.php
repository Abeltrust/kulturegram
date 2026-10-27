<?php

namespace Tests\Feature;

use App\Models\Culture;
use App\Models\CulturalRecordDraft;
use App\Models\CulturalRelationship;
use App\Models\Community;
use App\Models\Event;
use App\Models\LiveStream;
use App\Models\LiveStreamMessage;
use App\Models\User;
use App\Models\AiFeedbackLog;
use App\Services\MetaAI\LiveStreamCommunityIntelligenceService;
use App\Services\MetaAI\CulturalOpportunityService;
use App\Services\MetaAI\CulturalKnowledgeGraphService;
use App\Services\MetaAI\MultilingualCulturalAccessService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class AICommunityIntelligenceAndOpportunitiesTest extends TestCase
{
    use RefreshDatabase;

    /*
    |--------------------------------------------------------------------------
    | 1. AI Community Intelligence & Catch-Up Assistant Tests
    |--------------------------------------------------------------------------
    */

    public function test_live_conversation_summary_generates_with_participant_statements_and_disclosure(): void
    {
        $host = User::factory()->create(['name' => 'Elder Adamu']);
        $viewer1 = User::factory()->create(['name' => 'Bala']);
        $viewer2 = User::factory()->create(['name' => 'Fatima']);

        $stream = LiveStream::create([
            'title' => 'Tangale Traditional Marriage Rites & Dowry',
            'description' => 'Discussion on traditional marriage customs in Biliri and Kaltungo.',
            'category' => 'Oral Traditions',
            'user_id' => $host->id,
            'is_live' => true,
        ]);

        LiveStreamMessage::create([
            'live_stream_id' => $stream->id,
            'user_id' => $viewer1->id,
            'message' => 'In ancient times, what specific gifts were presented during the Tangale proposal?',
            'flag_status' => 'clean',
        ]);

        LiveStreamMessage::create([
            'live_stream_id' => $stream->id,
            'user_id' => $viewer2->id,
            'message' => 'The elders required traditional woven textiles and farm produce as tokens of commitment.',
            'flag_status' => 'clean',
        ]);

        $response = $this->actingAs($viewer1)->postJson(route('live-stream.generate-summary', $stream), ['force' => true]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'has_content' => true,
            ])
            ->assertJsonStructure([
                'summary' => ['overview', 'current_direction', 'participant_highlights'],
                'unanswered_questions',
                'cultural_references',
                'ai_disclosure',
            ]);

        // Verify summary was saved on stream
        $stream->refresh();
        $this->assertNotNull($stream->ai_summary);
        $this->assertNotNull($stream->summary_generated_at);
        $this->assertEquals('ready', $stream->summary_status);
    }

    public function test_catch_up_assistant_returns_dossier_with_unanswered_questions_and_cultural_refs(): void
    {
        $host = User::factory()->create();
        $lateViewer = User::factory()->create();

        // Create an existing approved culture record for reference matching
        $culture = Culture::create([
            'name' => 'Tangale Bai Hunting Festival and Rites',
            'region' => 'Gombe State, Nigeria',
            'category' => 'Oral Traditions',
            'language' => 'Tangale',
            'description' => 'Annual historical hunting festival celebrating communal solidarity and valor.',
            'status' => 'approved',
            'submitted_by' => $host->id,
        ]);

        $stream = LiveStream::create([
            'title' => 'Tangale Oral Traditions and Customs',
            'category' => 'Oral Traditions',
            'user_id' => $host->id,
            'is_live' => true,
        ]);

        LiveStreamMessage::create([
            'live_stream_id' => $stream->id,
            'user_id' => $host->id,
            'message' => 'How do they inaugurate the harvest season in Tangale custom?',
            'flag_status' => 'clean',
        ]);

        $response = $this->actingAs($lateViewer)->getJson(route('live-stream.catch-up', $stream));

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'has_content' => true,
            ]);

        $data = $response->json();
        $this->assertNotEmpty($data['unanswered_questions']);
        $this->assertStringContainsString('AI-generated synthesis', $data['ai_disclosure']);
    }

    /*
    |--------------------------------------------------------------------------
    | 2. AI-Assisted Collective Cultural Preservation (Draft Workflow)
    |--------------------------------------------------------------------------
    */

    public function test_unauthorized_viewer_cannot_extract_cultural_draft(): void
    {
        $host = User::factory()->create();
        $randomViewer = User::factory()->create(['is_admin' => false]);

        $stream = LiveStream::create([
            'title' => 'Ancient Nok Terracotta Techniques',
            'category' => 'Crafts',
            'user_id' => $host->id,
            'is_live' => true,
        ]);

        $response = $this->actingAs($randomViewer)->postJson(route('live-stream.extract-draft-record', $stream));

        $response->assertStatus(403);
        $this->assertDatabaseMissing('cultural_record_drafts', [
            'live_stream_id' => $stream->id,
        ]);
    }

    public function test_host_can_extract_cultural_draft_from_stream(): void
    {
        $host = User::factory()->create();
        $stream = LiveStream::create([
            'title' => 'Oral History of the Nupe Masquerade',
            'category' => 'Ceremonies',
            'user_id' => $host->id,
            'is_live' => true,
        ]);

        LiveStreamMessage::create([
            'live_stream_id' => $stream->id,
            'user_id' => $host->id,
            'message' => 'The Ndakogboya masquerade protects the community from negative spirits.',
            'flag_status' => 'clean',
        ]);

        $response = $this->actingAs($host)->postJson(route('live-stream.extract-draft-record', $stream));

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);

        $this->assertDatabaseHas('cultural_record_drafts', [
            'live_stream_id' => $stream->id,
            'contributor_id' => $host->id,
            'status' => 'draft',
        ]);
    }

    public function test_contributor_can_update_draft_and_submit_to_moderation(): void
    {
        $contributor = User::factory()->create();
        $draft = CulturalRecordDraft::create([
            'contributor_id' => $contributor->id,
            'title' => 'Draft Nupe Oral Traditions',
            'category' => 'Ceremonies',
            'region' => 'Niger State',
            'language' => 'Nupe',
            'extracted_story' => 'Primary oral account of historical lineages.',
            'ai_summary' => 'AI synthesis of community narrative.',
            'status' => 'draft',
        ]);

        // Contributor enriches with custodian notes
        $updateResponse = $this->actingAs($contributor)->putJson(route('cultural-hub.drafts.update', $draft->id), [
            'title' => 'Verified Nupe Oral Traditions & Chieftaincy Rites',
            'category' => 'Ceremonies',
            'region' => 'Niger State, Central Nigeria',
            'language' => 'Nupe',
            'extracted_story' => 'Primary oral account with added elder confirmation.',
            'ai_summary' => 'AI synthesis of community narrative.',
            'custodian_notes' => 'Confirmed with village council of elders on lineage chronology.',
            'unanswered_questions' => ['Are there royal restrictions during the festival?'],
        ]);

        $updateResponse->assertStatus(200);

        $draft->refresh();
        $this->assertEquals('reviewed', $draft->status);
        $this->assertStringContainsString('village council', $draft->custodian_notes);

        // Submit to moderation
        $submitResponse = $this->actingAs($contributor)->post(route('cultural-hub.drafts.submit', $draft->id));
        $submitResponse->assertRedirect();

        $draft->refresh();
        $this->assertEquals('submitted_to_moderation', $draft->status);
    }

    public function test_admin_can_approve_draft_into_permanent_culture_archive(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $contributor = User::factory()->create();

        $draft = CulturalRecordDraft::create([
            'contributor_id' => $contributor->id,
            'title' => 'Efik Ekpe Secret Society Traditions',
            'category' => 'Social Customs',
            'region' => 'Calabar, Cross River',
            'language' => 'Efik',
            'extracted_story' => 'Detailed oral historical testimony from community discussion.',
            'ai_summary' => 'Structured summary of governance roles and symbols.',
            'custodian_notes' => 'Sanctioned by custodians of Ekpe heritage.',
            'status' => 'submitted_to_moderation',
        ]);

        $response = $this->actingAs($admin)->post(route('admin.drafts.approve', $draft->id));
        $response->assertRedirect();

        $draft->refresh();
        $this->assertEquals('approved', $draft->status);
        $this->assertNotNull($draft->approved_culture_id);

        // Verify public Culture record was created with AI attribution and submitted_by set
        $this->assertDatabaseHas('cultures', [
            'id' => $draft->approved_culture_id,
            'submitted_by' => $contributor->id,
            'status' => 'approved',
            'is_ai_assisted' => true,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | 3. AI Cultural Opportunity Engine Tests
    |--------------------------------------------------------------------------
    */

    public function test_cultural_opportunity_engine_recommends_projects_and_opt_in_collaborators(): void
    {
        $user = User::factory()->create([
            'cultural_background' => 'Yoruba',
            'cultural_interests' => ['Textile Weaving', 'Aso Oke'],
            'languages' => ['Yoruba', 'English'],
            'opt_in_collaboration' => true,
        ]);

        // Create another user who opted into collaboration
        $collaborator = User::factory()->create([
            'name' => 'Adekunle Preserver',
            'cultural_background' => 'Yoruba',
            'cultural_interests' => ['Aso Oke', 'Indigo Dyeing'],
            'opt_in_collaboration' => true,
        ]);

        // Create another user who did NOT opt into collaboration
        $privateUser = User::factory()->create([
            'name' => 'Private Member',
            'cultural_background' => 'Yoruba',
            'cultural_interests' => ['Aso Oke'],
            'opt_in_collaboration' => false,
        ]);

        // Create cultural preservation record needing documentation
        $culture = Culture::create([
            'name' => 'Traditional Aso Oke Loom Weaving',
            'category' => 'Traditional Crafts',
            'region' => 'Oyo State, Nigeria',
            'language' => 'Yoruba',
            'description' => 'Generational strip-weaving technique facing modernization pressures.',
            'endangerment_level' => 'vulnerable',
            'status' => 'approved',
            'submitted_by' => $user->id,
        ]);

        $response = $this->actingAs($user)->getJson(route('cultural-opportunities.index'));

        $response->assertStatus(200)
            ->assertJson([
                'opted_out' => false,
            ]);

        $opportunities = $response->json('opportunities');
        $this->assertNotEmpty($opportunities);

        // Verify opt-in collaborator was recommended
        $collabIds = collect($opportunities)->where('type', 'user')->pluck('target_id')->toArray();
        $this->assertContains($collaborator->id, $collabIds);
        $this->assertNotContains($privateUser->id, $collabIds); // Non-opted-in user never suggested

        // Verify preservation project was recommended with explanation
        $project = collect($opportunities)->firstWhere('type', 'preservation_project');
        $this->assertNotNull($project);
        $this->assertStringContainsString('Recommended because', $project['explanation']);
    }

    public function test_user_can_dismiss_opportunity_recommendation(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson(route('cultural-opportunities.dismiss'), [
            'type' => 'culture',
            'target_id' => 99,
        ]);

        $response->assertStatus(200)
            ->assertJson(['success' => true]);

        $this->assertDatabaseHas('cultural_recommendation_dismissals', [
            'user_id' => $user->id,
            'recommendation_type' => 'culture',
            'target_id' => 99,
        ]);
    }

    public function test_user_can_opt_out_of_opportunity_recommendations(): void
    {
        $user = User::factory()->create([
            'cultural_interests' => ['Pottery'],
            'preferences' => ['opt_in_recommendations' => true],
        ]);

        // Opt out
        $response = $this->actingAs($user)->postJson(route('cultural-opportunities.preferences'), [
            'cultural_interests' => ['Pottery'],
            'opt_in_collaboration' => false,
            'opt_in_recommendations' => false,
        ]);

        $response->assertStatus(200);

        // Request opportunities
        $getRes = $this->actingAs($user)->getJson(route('cultural-opportunities.index'));
        $getRes->assertStatus(200)
            ->assertJson([
                'opted_out' => true,
                'opportunities' => [],
            ]);
    }

    /*
    |--------------------------------------------------------------------------
    | 4. AI Feedback Loop and Continuous Improvement Tests
    |--------------------------------------------------------------------------
    */

    public function test_ai_feedback_logs_user_and_moderator_feedback_with_sanitized_metadata(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson(route('ai.feedback.store'), [
            'feedback_type' => 'summary_correction',
            'target_type' => 'Culture',
            'target_id' => 12,
            'user_correction' => 'The festival occurs during the ninth lunar month, not harvest month.',
            'metadata' => [
                'culture_id' => 12,
                'unsupported_private_token' => 'SECRET_TOKEN_DO_NOT_STORE', // Must be sanitized
            ],
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);

        $this->assertDatabaseHas('ai_feedback_logs', [
            'user_id' => $user->id,
            'feedback_type' => 'summary_correction',
            'target_type' => 'Culture',
            'target_id' => 12,
            'status' => 'logged',
        ]);

        $log = AiFeedbackLog::where('user_id', $user->id)->first();
        $this->assertArrayNotHasKey('unsupported_private_token', $log->metadata);
    }

    public function test_admin_can_view_feedback_metrics_and_update_status(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $user = User::factory()->create();

        $log = AiFeedbackLog::create([
            'user_id' => $user->id,
            'feedback_type' => 'moderation_dispute',
            'target_type' => 'LiveStreamMessage',
            'target_id' => 50,
            'reason' => 'Context was a friendly cultural greeting.',
            'status' => 'logged',
        ]);

        $response = $this->actingAs($admin)->get(route('admin.ai-feedback.index'));
        $response->assertStatus(200);

        // Update status to reviewed
        $updateRes = $this->actingAs($admin)->post(route('admin.ai-feedback.status', $log->id), [
            'status' => 'reviewed',
        ]);
        $updateRes->assertRedirect();

        $log->refresh();
        $this->assertEquals('reviewed', $log->status);
    }

    /*
    |--------------------------------------------------------------------------
    | 5. Cultural Knowledge Graph Service Tests
    |--------------------------------------------------------------------------
    */

    public function test_cultural_knowledge_graph_links_entities_with_human_explanations(): void
    {
        $graphService = app(CulturalKnowledgeGraphService::class);
        $user = User::factory()->create();

        $cultureA = Culture::create([
            'name' => 'Tangale Traditional Wrestling (Dambe Counterpart)',
            'region' => 'Gombe State',
            'category' => 'Traditional Sports',
            'language' => 'Tangale',
            'description' => 'Communal athletic martial contest during dry season.',
            'status' => 'approved',
            'submitted_by' => $user->id,
        ]);

        $cultureB = Culture::create([
            'name' => 'Tangale Bai Festival',
            'region' => 'Gombe State',
            'category' => 'Traditional Sports',
            'language' => 'Tangale',
            'description' => 'Festive celebration accompanying traditional games.',
            'status' => 'approved',
            'submitted_by' => $user->id,
        ]);

        $related = $graphService->getRelatedRecords($cultureA, 2);

        $this->assertNotEmpty($related);
        $firstMatch = $related[0];
        $this->assertEquals($cultureB->id, $firstMatch['culture']->id);
        $this->assertStringContainsString('shares the Tangale language', $firstMatch['explanation']);

        // Verify edge was stored in relational database
        $this->assertDatabaseHas('cultural_relationships', [
            'source_type' => 'Culture',
            'source_id' => $cultureA->id,
            'target_type' => 'Culture',
            'target_id' => $cultureB->id,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | 6. AI-Assisted Multilingual Cultural Access Tests
    |--------------------------------------------------------------------------
    */

    public function test_multilingual_access_preserves_original_text_and_labels_ai_translation(): void
    {
        $service = app(MultilingualCulturalAccessService::class);
        $original = 'Traditional initiation rites among the youth of Gombe hills.';

        $result = $service->translateSummary($original, 'Hausa', 'Gombe Living Heritage');

        $this->assertEquals($original, $result['original_text']);
        $this->assertNotEmpty($result['target_language']);
        $this->assertEquals('Hausa', $result['target_language']);
    }

    public function test_reading_level_adaptation_preserves_authentic_cultural_account(): void
    {
        $service = app(MultilingualCulturalAccessService::class);
        $original = 'Ancestral pottery shaping techniques handed down via female ceramic guilds.';

        $result = $service->adaptReadingLevel($original, 'learner');

        $this->assertEquals($original, $result['original_text']);
        $this->assertEquals('learner', $result['level']);
    }
}
