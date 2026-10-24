<?php

namespace Tests\Feature;

use App\Models\LifeChapter;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_profile_page_is_displayed(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->get('/profile');

        $response->assertOk();
        $response->assertSee($user->name);
    }

    public function test_profile_information_can_be_updated(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->patch('/profile', [
                'name' => 'Test User',
                'email' => 'test@example.com',
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/profile');

        $user->refresh();

        $this->assertSame('Test User', $user->name);
        $this->assertSame('test@example.com', $user->email);
        $this->assertNull($user->email_verified_at);
    }

    public function test_email_verification_status_is_unchanged_when_the_email_address_is_unchanged(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->patch('/profile', [
                'name' => 'Test User',
                'email' => $user->email,
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/profile');

        $this->assertNotNull($user->refresh()->email_verified_at);
    }

    public function test_user_can_upload_profile_picture(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();

        $file = UploadedFile::fake()->create('avatar.jpg', 100, 'image/jpeg');

        $response = $this->actingAs($user)->post(route('profile.photo.upload'), [
            'profile_photo' => $file,
        ]);

        $response->assertRedirect();
        $this->assertNotNull($user->fresh()->avatar);
        Storage::disk('public')->assertExists('avatars/' . $user->fresh()->avatar);
    }

    public function test_user_can_upload_cover_banner(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();

        $file = UploadedFile::fake()->create('banner.jpg', 200, 'image/jpeg');

        $response = $this->actingAs($user)->post(route('profile.cover.upload'), [
            'cover_photo' => $file,
        ]);

        $response->assertRedirect();
        $this->assertNotNull($user->fresh()->cover_photo);
        Storage::disk('public')->assertExists('covers/' . $user->fresh()->cover_photo);
    }

    public function test_user_can_add_and_delete_life_chapter(): void
    {
        $user = User::factory()->create();

        $createResponse = $this->actingAs($user)->post(route('profile.chapters.store'), [
            'title' => 'Youth in Plateau',
            'period' => '2008 - 2014',
            'location' => 'Jos',
            'description' => 'Preserving mountain culture and stories.',
        ]);

        $createResponse->assertRedirect(route('profile.show'));
        $chapter = LifeChapter::where('user_id', $user->id)->first();
        $this->assertNotNull($chapter);
        $this->assertEquals('Youth in Plateau', $chapter->title);

        $deleteResponse = $this->actingAs($user)->delete(route('profile.chapters.destroy', $chapter));
        $deleteResponse->assertRedirect();
        $this->assertDatabaseMissing('life_chapters', ['id' => $chapter->id]);
    }

    public function test_user_can_update_settings_and_preferences(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->patch(route('settings.update', $user->id), [
            'name' => 'Updated Custodian',
            'email' => $user->email,
            'username' => 'custodian_prime',
            'bio' => 'Preserving ancestral stories.',
            'location' => 'Abuja',
            'website' => 'https://kulturegram.com',
            'cultural_background' => 'Yoruba & Edo',
            'preferences' => [
                'direct_message_locked_only' => '1',
                'public_profile' => '1',
            ],
        ]);

        $response->assertRedirect();
        $user->refresh();
        $this->assertEquals('Updated Custodian', $user->name);
        $this->assertEquals('custodian_prime', $user->username);
        $this->assertEquals('https://kulturegram.com', $user->website);
        $this->assertEquals('Yoruba & Edo', $user->cultural_background);
    }

    public function test_unauthorized_user_cannot_access_another_users_settings(): void
    {
        $owner = User::factory()->create();
        $stranger = User::factory()->create(['is_admin' => false]);

        $response = $this->actingAs($stranger)->get(route('settings.index', $owner->id));
        $response->assertStatus(403);
    }

    public function test_privacy_and_terms_pages_can_be_rendered(): void
    {
        $privacyResponse = $this->get(route('privacy'));
        $privacyResponse->assertStatus(200);
        $privacyResponse->assertSee('Privacy Policy');

        $termsResponse = $this->get(route('terms'));
        $termsResponse->assertStatus(200);
        $termsResponse->assertSee('Terms of Service');
    }

    public function test_user_can_delete_their_account(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->delete('/profile', [
                'password' => 'password',
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/');

        $this->assertGuest();
        $this->assertNull($user->fresh());
    }

    public function test_correct_password_must_be_provided_to_delete_account(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->from('/profile')
            ->delete('/profile', [
                'password' => 'wrong-password',
            ]);

        $response
            ->assertSessionHasErrors('password')
            ->assertRedirect('/profile');

        $this->assertNotNull($user->fresh());
    }

    public function test_user_can_view_activity_and_preservation_log_page(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->get(route('profile.activity'));

        $response->assertOk();
        $response->assertSee('Activity &amp; Preservation Log', false);
        $response->assertSee('Stories');
        $response->assertSee('Locked In');
        $response->assertSee('Vibes / Taps');
    }

    public function test_user_can_view_another_user_activity_log_page(): void
    {
        $user = User::factory()->create(['username' => 'alice_cultural']);
        $viewer = User::factory()->create();

        $response = $this
            ->actingAs($viewer)
            ->get(route('profile.user.activity', $user));

        $response->assertOk();
        $response->assertSee('Activity &amp; Preservation Log', false);
        $response->assertSee('alice_cultural');
    }
}

