<?php

namespace Tests\Feature;

use App\Models\Culture;
use App\Models\CulturalMedia;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CulturalContributionWithMediaAndBooksTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Culture $culture;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create([
            'name' => 'Amara Okafor',
            'email' => 'amara@example.com',
        ]);

        $this->culture = Culture::create([
            'name' => 'Yoruba Gelede Masquerade',
            'category' => 'Festivals & Ceremonies',
            'region' => 'Southwest Nigeria / Benin Republic',
            'description' => 'A community ceremony celebrating women, mothers, and ancestral fertility.',
            'historical_period' => '18th Century to Present',
            'significance' => 'Honoring the spiritual power of women and mothers in society.',
            'status' => 'approved',
            'submitted_by' => $this->user->id,
        ]);
    }

    public function test_user_can_contribute_image_saved_under_specific_section_media(): void
    {
        Storage::fake('public');

        $imageFile = UploadedFile::fake()->create('gelede_headdress.jpg', 200, 'image/jpeg');

        $response = $this->actingAs($this->user)->post(route('cultural-hub.contribute', $this->culture->id), [
            'target_section' => 'history',
            'revision_type' => 'new_media',
            'title' => 'Sacred Gelede Wooden Headdress Field Scan',
            'proposed_content' => 'Documenting the hand-carved mask motifs collected during annual harvest festival.',
            'image' => $imageFile,
            'image_caption' => 'Hand-carved wooden headdress depicting female elder wisdom.',
            'image_attribution' => 'Amara Okafor Field Research (CC BY-SA 4.0)',
            'license_type' => 'Community Commons (CC BY-SA 4.0)',
        ]);

        $response->assertRedirect(route('cultural-hub.show', ['culture' => $this->culture->id, 'section' => 'media']));

        $this->assertDatabaseHas('cultural_media', [
            'culture_id' => $this->culture->id,
            'media_type' => 'photograph',
            'section' => 'history',
            'caption' => 'Hand-carved wooden headdress depicting female elder wisdom.',
            'creator_attribution' => 'Amara Okafor Field Research (CC BY-SA 4.0)',
        ]);

        $this->assertDatabaseHas('cultural_revisions', [
            'culture_id' => $this->culture->id,
            'user_id' => $this->user->id,
            'target_section' => 'history',
            'title' => 'Sacred Gelede Wooden Headdress Field Scan',
        ]);
    }

    public function test_user_can_contribute_video_and_book_reference_wikipedia_style(): void
    {
        $response = $this->actingAs($this->user)->post(route('cultural-hub.contribute', $this->culture->id), [
            'target_section' => 'practices',
            'revision_type' => 'book_reference',
            'title' => 'Scholarly Monograph by Babatunde Lawal',
            'proposed_content' => 'Comprehensive academic study analyzing the choreography and drumming of Gelede.',
            'video_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
            'video_caption' => 'Documentary on Gelede mask dancers in Ketu.',
            'book_title' => 'The Gelede Spectacle: Art, Gender, and Social Harmony in an African Culture',
            'book_author' => 'Babatunde Lawal',
            'book_year' => '1996',
            'book_publisher' => 'University of Washington Press',
            'book_isbn' => '978-0295975993',
            'book_excerpt' => 'Page 34: The Gelede spectacle is a public recognition of the primordial powers of women in society.',
            'book_url' => 'https://books.google.com/books?id=example',
        ]);

        $response->assertRedirect(route('cultural-hub.show', ['culture' => $this->culture->id, 'section' => 'media']));

        // Video media saved
        $this->assertDatabaseHas('cultural_media', [
            'culture_id' => $this->culture->id,
            'media_type' => 'video',
            'section' => 'practices',
            'external_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
            'caption' => 'Documentary on Gelede mask dancers in Ketu.',
        ]);

        // Book media saved (Wikipedia style)
        $this->assertDatabaseHas('cultural_media', [
            'culture_id' => $this->culture->id,
            'media_type' => 'book',
            'section' => 'practices',
            'book_title' => 'The Gelede Spectacle: Art, Gender, and Social Harmony in an African Culture',
            'book_author' => 'Babatunde Lawal',
            'book_publisher' => 'University of Washington Press',
            'book_isbn' => '978-0295975993',
        ]);

        // Source saved
        $this->assertDatabaseHas('cultural_sources', [
            'culture_id' => $this->culture->id,
            'source_type' => 'written_account',
            'recording_creator' => 'Babatunde Lawal',
        ]);
    }

    public function test_culture_show_page_renders_media_gallery_and_books(): void
    {
        CulturalMedia::create([
            'culture_id' => $this->culture->id,
            'media_type' => 'book',
            'section' => 'history',
            'file_path' => 'reference',
            'book_title' => 'Yoruba Ritual: Performers, Play, Agency',
            'book_author' => 'Margaret Thompson Drewal',
            'book_year' => '1992',
            'book_publisher' => 'Indiana University Press',
            'book_isbn' => '978-0253206848',
            'book_excerpt' => 'Ritual performance in Yoruba thought is a participatory creation.',
            'caption' => 'Academic Monograph on Ritual Agency',
            'access_restriction' => 'public',
            'processing_status' => 'ready',
        ]);

        $response = $this->actingAs($this->user)->get(route('cultural-hub.show', ['culture' => $this->culture->id, 'section' => 'media']));

        $response->assertStatus(200);
        $response->assertSee('Yoruba Ritual: Performers, Play, Agency');
        $response->assertSee('Margaret Thompson Drewal');
        $response->assertSee('Indiana University Press');
        $response->assertSee('978-0253206848');
    }
}

