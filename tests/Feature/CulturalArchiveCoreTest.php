<?php

namespace Tests\Feature;

use App\Models\Culture;
use App\Models\CulturalClaim;
use App\Models\CulturalContributor;
use App\Models\CulturalMedia;
use App\Models\CulturalRevision;
use App\Models\CulturalSource;
use App\Models\CulturalTranslation;
use App\Models\User;
use App\Services\MetaAI\CulturalCompanionService;
use App\Services\MetaAI\CultureStructuringService;
use App\Services\MetaAI\MetaModelApiClient;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CulturalArchiveCoreTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create([
            'email' => 'archivist@kulturegram.org',
            'is_admin' => false,
        ]);

        $this->admin = User::factory()->create([
            'email' => 'admin@kulturegram.org',
            'is_admin' => true,
        ]);
    }

    /**
     * Test existing cultural records remain accessible on index and show pages.
     */
    public function test_existing_cultural_records_remain_accessible(): void
    {
        $culture = Culture::create([
            'name' => 'Tangale Traditional Marriage',
            'region' => 'Gombe, Nigeria',
            'description' => 'A longstanding customary practice among the Tangale people involving traditional betrothal and dowry rites.',
            'category' => 'Social Customs',
            'status' => 'approved',
            'submitted_by' => $this->user->id,
        ]);

        // Index page loads
        $response = $this->get(route('cultural-hub.index'));
        $response->assertStatus(200);
        $response->assertSee('Tangale Traditional Marriage');
        $response->assertSee('Living Cultural Archive');

        // Show page loads with archival structure
        $showResponse = $this->get(route('cultural-hub.show', $culture->id));
        $showResponse->assertStatus(200);
        $showResponse->assertSee('Tangale Traditional Marriage');
        $showResponse->assertSee('#CR-' . str_pad($culture->id, 5, '0', STR_PAD_LEFT));
        $showResponse->assertSee('Published Archive');
    }

    /**
     * Test creating a cultural record with type-adaptive data and automatic source item creation.
     */
    public function test_create_record_stores_sources_and_contributors(): void
    {
        $this->actingAs($this->user);

        $payload = [
            'name' => 'Oromo Gadaa System Governance',
            'translated_title' => 'Gadaa Age-Grade Democratic System',
            'category' => 'Social Customs',
            'subtype' => 'oral_history',
            'cultural_group' => 'Oromo',
            'region' => 'Oromia, Ethiopia',
            'locality' => 'Boran & Guji Zones',
            'language' => 'Afaan Oromo',
            'sensitivity_level' => 'public',
            'description' => 'The Gadaa system is a traditional system of governance of the Oromo people, developed over centuries through oral transmission and eight-year generational cycles.',
            'speaker_name' => 'Abbaa Gadaa Elder Council',
            'speaker_is_anonymous' => false,
            'attribution_preference' => 'Recorded with Abbaa Gadaa permission',
            'license_type' => 'Community Heritage Commons',
            'claims' => [
                [
                    'claim_text' => 'Gadaa councils rotate leadership every eight years.',
                    'claim_type' => 'oral_tradition',
                    'source_excerpt' => 'Leadership rotates strictly every eight years.',
                ],
            ],
        ];

        $response = $this->post(route('cultural-hub.store'), $payload);
        $response->assertRedirect(route('cultural-hub.index'));

        $this->assertDatabaseHas('cultures', [
            'name' => 'Oromo Gadaa System Governance',
            'cultural_group' => 'Oromo',
            'language' => 'Afaan Oromo',
            'review_status' => 'submitted',
        ]);

        $created = Culture::where('name', 'Oromo Gadaa System Governance')->first();
        $this->assertNotNull($created);
        $this->assertCount(1, $created->sources);
        $this->assertEquals('written_account', $created->sources->first()->source_type);
        $this->assertCount(1, $created->claims);
        $this->assertEquals('Gadaa councils rotate leadership every eight years.', $created->claims->first()->claim_text);
        $this->assertCount(1, $created->culturalContributors);
    }

    /**
     * Test saving record as draft does not publish immediately.
     */
    public function test_saving_record_as_draft(): void
    {
        $this->actingAs($this->user);

        $payload = [
            'name' => 'Incomplete Oral Account of Ancient Migration',
            'category' => 'Oral Traditions',
            'region' => 'Nile Valley',
            'description' => 'Initial fragments of oral account gathered from local elder.',
            'is_draft' => 1,
            'status' => 'draft',
            'license_type' => 'Community Heritage Commons',
        ];

        $response = $this->post(route('cultural-hub.store'), $payload);
        $response->assertRedirect(route('cultural-hub.index'));

        $this->assertDatabaseHas('cultures', [
            'name' => 'Incomplete Oral Account of Ancient Migration',
            'status' => 'draft',
            'review_status' => 'draft',
        ]);
    }

    /**
     * Test updating a cultural record by guardian.
     */
    public function test_guardian_can_update_record(): void
    {
        $this->actingAs($this->user);

        $culture = Culture::create([
            'name' => 'Kano Dyeing Pits',
            'region' => 'Kano, Nigeria',
            'description' => 'Century-old indigo vegetable dyeing traditions in Kofar Mata.',
            'category' => 'Traditional Crafts',
            'status' => 'approved',
            'submitted_by' => $this->user->id,
        ]);

        $response = $this->put(route('cultural-hub.update', $culture->id), [
            'name' => 'Kano Dyeing Pits (Kofar Mata)',
            'region' => 'Kano, Nigeria',
            'locality' => 'Kofar Mata Dye Pits',
            'cultural_group' => 'Hausa',
            'language' => 'Hausa',
            'category' => 'Traditional Crafts',
            'description' => 'Established in 1498, the Kofar Mata dye pits are the oldest operational indigo dyeing pits in West Africa.',
            'license_type' => 'Community Heritage Commons',
        ]);

        $response->assertRedirect(route('cultural-hub.show', $culture->id));
        $this->assertDatabaseHas('cultures', [
            'id' => $culture->id,
            'name' => 'Kano Dyeing Pits (Kofar Mata)',
            'cultural_group' => 'Hausa',
        ]);
    }

    /**
     * Test unauthorized user cannot edit another guardian's record.
     */
    public function test_unauthorized_user_cannot_edit_record(): void
    {
        $otherUser = User::factory()->create();
        $this->actingAs($otherUser);

        $culture = Culture::create([
            'name' => 'Private Village Song',
            'region' => 'Highlands',
            'description' => 'A village song recorded by archivist.',
            'category' => 'Music & Dance',
            'status' => 'approved',
            'submitted_by' => $this->user->id,
        ]);

        $response = $this->get(route('cultural-hub.edit', $culture->id));
        $response->assertStatus(403);

        $updateResponse = $this->put(route('cultural-hub.update', $culture->id), [
            'name' => 'Hacked Title',
            'region' => 'Highlands',
            'category' => 'Music & Dance',
            'description' => 'Altered description by unauthorized party.',
        ]);
        $updateResponse->assertStatus(403);
    }

    /**
     * Test sensitive/restricted records are hidden from non-guardians/non-admins.
     */
    public function test_restricted_records_are_protected(): void
    {
        $restrictedCulture = Culture::create([
            'name' => 'Secret Clan Initiation Rite',
            'region' => 'Sacred Forest',
            'description' => 'Restricted knowledge reserved for clan custodians only.',
            'category' => 'Religious Practices',
            'status' => 'approved',
            'sensitivity_level' => 'restricted',
            'submitted_by' => $this->user->id,
        ]);

        // Anonymous visitor cannot view restricted record
        $anonResponse = $this->get(route('cultural-hub.show', $restrictedCulture->id));
        $anonResponse->assertStatus(403);

        // Different user cannot view restricted record
        $stranger = User::factory()->create();
        $this->actingAs($stranger);
        $strangerResponse = $this->get(route('cultural-hub.show', $restrictedCulture->id));
        $strangerResponse->assertStatus(403);

        // Submitting guardian CAN view restricted record
        $this->actingAs($this->user);
        $guardianResponse = $this->get(route('cultural-hub.show', $restrictedCulture->id));
        $guardianResponse->assertStatus(200);
        $guardianResponse->assertSee('Secret Clan Initiation Rite');

        // Admin CAN view restricted record
        $this->actingAs($this->admin);
        $adminResponse = $this->get(route('cultural-hub.show', $restrictedCulture->id));
        $adminResponse->assertStatus(200);
    }

    /**
     * Test community contribution / revision submission workflow.
     */
    public function test_community_contribution_submission(): void
    {
        $culture = Culture::create([
            'name' => 'Yoruba Oriki Chants',
            'region' => 'Oyo, Nigeria',
            'description' => 'Traditional praise poetry celebrating familial lineage and ancestral virtues.',
            'category' => 'Language & Literature',
            'status' => 'approved',
            'submitted_by' => $this->user->id,
        ]);

        $visitor = User::factory()->create();
        $this->actingAs($visitor);

        $response = $this->post(route('cultural-hub.contribute', $culture->id), [
            'revision_type' => 'additional_context',
            'title' => 'Oyo Royal Lineage Variation',
            'proposed_content' => 'In the Alaafin royal court, Oriki is accompanied by the Bata drums in specific cadence.',
            'evidence_notes' => 'Oral interview with palace drummer, Oyo.',
        ]);

        $response->assertRedirect(route('cultural-hub.show', $culture->id));

        $this->assertDatabaseHas('cultural_revisions', [
            'culture_id' => $culture->id,
            'user_id' => $visitor->id,
            'revision_type' => 'additional_context',
            'status' => 'submitted',
        ]);
    }

    /**
     * Test review status update by authorized guardian/admin.
     */
    public function test_review_status_update(): void
    {
        $culture = Culture::create([
            'name' => 'Disputed Folklore Tale',
            'region' => 'Rift Valley',
            'description' => 'A folk narrative with two conflicting regional origin claims.',
            'category' => 'Oral Traditions',
            'status' => 'approved',
            'review_status' => 'published',
            'submitted_by' => $this->user->id,
        ]);

        $this->actingAs($this->user);

        $response = $this->post(route('cultural-hub.review-status', $culture->id), [
            'review_status' => 'disputed',
            'notes' => 'Multiple elder accounts differ on origin place.',
        ]);

        $response->assertRedirect(route('cultural-hub.show', $culture->id));
        $this->assertEquals('disputed', $culture->fresh()->review_status);
        $this->assertEquals('Disputed Accounts', $culture->fresh()->review_badge['label']);
    }

    /**
     * Test AI structuring service returns grounded structure with explicit unknowns.
     */
    public function test_culture_structuring_service_grounded_fallback(): void
    {
        $client = app(MetaModelApiClient::class);
        $service = new CultureStructuringService($client);

        $source = "My grandfather told me that the fishermen of Argungu cast their nets together once a year when the moon is full. We use hand-crafted nets made from baobab fiber.";
        $result = $service->structureRecord($source, ['region' => 'Kebbi, Nigeria']);

        $this->assertTrue($result['success']);
        $this->assertNotNull($result['data']);
        $data = $result['data'];

        $this->assertEquals('Oral Traditions', $data['category']);
        $this->assertEquals('review_needed', $data['subtype']);
        $this->assertIsArray($data['claims']);
        $this->assertNotEmpty($data['claims']);
        $this->assertIsArray($data['missing_information']);
        $this->assertIsArray($data['follow_up_inquiries']);
    }

    /**
     * Test Cultural Companion answers grounded in record and flags missing information.
     */
    public function test_cultural_companion_service_grounded_response(): void
    {
        $culture = Culture::create([
            'name' => 'Argungu Fishing Festival',
            'region' => 'Kebbi, Nigeria',
            'language' => 'Hausa',
            'cultural_group' => 'Kabawa',
            'category' => 'Festivals & Ceremonies',
            'description' => 'Annual four-day festival in Kebbi State celebrating fishing skills, agriculture, and unity.',
            'significance' => 'Marks the end of growing seasons and peace between ancient kingdoms.',
            'current_practitioners' => 'Guild of local Kabawa fishermen',
            'status' => 'approved',
            'submitted_by' => $this->user->id,
        ]);

        $client = app(MetaModelApiClient::class);
        $companion = new CulturalCompanionService($client);

        $response = $companion->askCompanion($culture, 'Who are the people who practice this tradition?');
        $this->assertTrue($response['success']);
        $this->assertTrue($response['grounded']);
        $this->assertStringContainsString('Kabawa', $response['answer']);
        $this->assertNotNull($response['missing_context']);
    }

    /**
     * Test multi-facet search filters real attributes without errors.
     */
    public function test_multi_facet_search_filters(): void
    {
        Culture::create([
            'name' => 'Bambara Chiwara Headdress',
            'region' => 'Mali',
            'language' => 'Bambara',
            'cultural_group' => 'Bamana',
            'category' => 'Traditional Crafts',
            'status' => 'approved',
            'review_status' => 'published',
            'submitted_by' => $this->user->id,
            'description' => 'Carved wooden antelopes used in agricultural fertility ceremonies.',
        ]);

        Culture::create([
            'name' => 'Zulu Reed Dance (Umhlanga)',
            'region' => 'KwaZulu-Natal, South Africa',
            'language' => 'isiZulu',
            'cultural_group' => 'Zulu',
            'category' => 'Festivals & Ceremonies',
            'status' => 'approved',
            'review_status' => 'published',
            'submitted_by' => $this->user->id,
            'description' => 'Centuries-old ceremonial festival celebrating young women and unity.',
        ]);

        // Search by language
        $langSearch = $this->get(route('cultural-hub.index', ['language' => 'Bambara']));
        $langSearch->assertStatus(200);
        $langSearch->assertSee('Bambara Chiwara Headdress');
        $langSearch->assertDontSee('Zulu Reed Dance');

        // Search by cultural group
        $groupSearch = $this->get(route('cultural-hub.index', ['cultural_group' => 'Zulu']));
        $groupSearch->assertStatus(200);
        $groupSearch->assertSee('Zulu Reed Dance');
        $groupSearch->assertDontSee('Bambara Chiwara');
    }
}
