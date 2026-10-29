<?php

namespace Tests\Feature\Auth;

use App\Models\Country;
use App\Models\Language;
use App\Models\LocalGovernment;
use App\Models\State;
use App\Models\User;
use App\Providers\RouteServiceProvider;
use Database\Seeders\LanguageSeeder;
use Database\Seeders\LocationSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_screen_can_be_rendered(): void
    {
        $response = $this->get('/register');

        $response->assertStatus(200);
        $response->assertSee('Register');
        $response->assertSee('Your roots');
    }

    public function test_new_users_can_register_basic_and_skip_origin(): void
    {
        $response = $this->post('/register', [
            'name' => 'Basic User',
            'email' => 'basic@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'terms' => '1',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(RouteServiceProvider::HOME);

        $user = User::where('email', 'basic@example.com')->first();
        $this->assertNotNull($user);
        $this->assertNull($user->country_id);
        $this->assertNull($user->state_id);
        $this->assertNull($user->local_government_id);
        $this->assertCount(0, $user->spokenLanguages);
    }

    public function test_user_can_register_with_nigeria_state_lga_and_languages(): void
    {
        $this->seed(LocationSeeder::class);
        $this->seed(LanguageSeeder::class);

        $nigeria = Country::where('iso2', 'NG')->firstOrFail();
        $plateau = State::where('country_id', $nigeria->id)->where('name', 'Plateau')->firstOrFail();
        $josNorth = LocalGovernment::where('state_id', $plateau->id)->where('name', 'Jos North')->firstOrFail();

        $hausa = Language::where('name', 'Hausa')->firstOrFail();
        $berom = Language::where('name', 'Berom')->firstOrFail();

        $response = $this->post('/register', [
            'name' => 'Abel Bitrus',
            'username' => 'abelb',
            'email' => 'abel@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'country_id' => $nigeria->id,
            'state_id' => $plateau->id,
            'local_government_id' => $josNorth->id,
            'language_ids' => [$hausa->id, $berom->id],
            'new_languages' => ['Afizere Dialect'],
            'terms' => '1',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(RouteServiceProvider::HOME);

        $user = User::where('email', 'abel@example.com')->firstOrFail();
        $this->assertEquals($nigeria->id, $user->country_id);
        $this->assertEquals($plateau->id, $user->state_id);
        $this->assertEquals($josNorth->id, $user->local_government_id);
        $this->assertEquals('Jos North, Plateau, Nigeria', $user->location);

        // Verify spoken languages relationship
        $spokenNames = $user->spokenLanguages->pluck('name')->all();
        $this->assertContains('Hausa', $spokenNames);
        $this->assertContains('Berom', $spokenNames);
        $this->assertContains('Afizere Dialect', $spokenNames);

        // Verify new custom language record was created with is_custom = true
        $customLang = Language::where('name', 'Afizere Dialect')->firstOrFail();
        $this->assertTrue($customLang->is_custom);

        // Verify legacy languages JSON column is synced for backwards compatibility
        $this->assertIsArray($user->languages);
        $this->assertContains('Hausa', $user->languages);
        $this->assertContains('Berom', $user->languages);
        $this->assertContains('Afizere Dialect', $user->languages);
    }

    public function test_user_can_register_with_foreign_country_and_custom_local_area(): void
    {
        $this->seed(LocationSeeder::class);

        $ghana = Country::where('iso2', 'GH')->firstOrFail();

        $response = $this->post('/register', [
            'name' => 'Kwame Mensah',
            'username' => 'kwamem',
            'email' => 'kwame@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'country_id' => $ghana->id,
            'local_area' => 'Osu, Greater Accra',
            'terms' => '1',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(RouteServiceProvider::HOME);

        $user = User::where('email', 'kwame@example.com')->firstOrFail();
        $this->assertEquals($ghana->id, $user->country_id);
        $this->assertNull($user->state_id);
        $this->assertNotNull($user->local_government_id);

        $customArea = LocalGovernment::find($user->local_government_id);
        $this->assertEquals('Osu, Greater Accra', $customArea->name);
        $this->assertTrue($customArea->is_custom);
        $this->assertEquals($user->id, $customArea->created_by);
        $this->assertEquals('Osu, Greater Accra, Ghana', $user->location);

        // Check suggestions endpoint returns this custom area for Ghana
        $areasResponse = $this->getJson("/locations/countries/{$ghana->id}/areas?q=Osu");
        $areasResponse->assertStatus(200);
        $areasResponse->assertJsonFragment(['name' => 'Osu, Greater Accra']);
    }

    public function test_user_can_register_with_avatar_and_receives_welcome_flyer_session(): void
    {
        \Illuminate\Support\Facades\Storage::fake('public');

        // 1x1 transparent GIF base64 string works without GD extension installed
        $gifBinary = base64_decode('R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7');
        $avatar = \Illuminate\Http\UploadedFile::fake()->createWithContent('profile.gif', $gifBinary);

        $response = $this->post('/register', [
            'name' => 'Amara Kalu',
            'username' => 'amarak',
            'email' => 'amara@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'avatar' => $avatar,
            'terms' => '1',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(RouteServiceProvider::HOME);
        $response->assertSessionHas('registered_flyer');

        $user = User::where('email', 'amara@example.com')->firstOrFail();
        $this->assertNotNull($user->avatar);
        \Illuminate\Support\Facades\Storage::disk('public')->assertExists('avatars/' . $user->avatar);

        $flyer = session('registered_flyer');
        $this->assertEquals('Amara Kalu', $flyer['name']);
        $this->assertEquals('amarak', $flyer['username']);
        $this->assertStringContainsString($user->avatar, $flyer['avatar_url']);

        // Verify that the first-time welcome post was created on timeline with flyer
        $welcomePost = \App\Models\Post::where('user_id', $user->id)->first();
        $this->assertNotNull($welcomePost);
        $this->assertStringContainsString('Hi everyone! I am now on Kulturegram!', $welcomePost->content);
        $this->assertNotNull($welcomePost->image);
        $this->assertStringContainsString('flyers/welcome_flyer_', $welcomePost->image);
        \Illuminate\Support\Facades\Storage::disk('public')->assertExists($welcomePost->image);
    }


    public function test_registration_requires_accepted_terms(): void
    {
        $response = $this->post('/register', [
            'name' => 'Unconsenting User',
            'username' => 'unconsenting',
            'email' => 'unconsenting@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            // Missing 'terms' => '1'
        ]);

        $this->assertGuest();
        $response->assertSessionHasErrors(['terms']);
        $this->assertNull(User::where('email', 'unconsenting@example.com')->first());
    }

    public function test_terms_page_can_be_rendered(): void
    {
        $response = $this->get('/terms');

        $response->assertStatus(200);
        $response->assertSee('Terms of Service & Community Agreement', false);
        $response->assertSee('You Own Your Content');
        $response->assertSee('Locked-In Circles');
        $response->assertSee('Cultural Dignity First');
        $response->assertSee('Acceptance & User Eligibility', false);
    }

    public function test_registration_screen_shows_terms_modal_and_checkbox(): void
    {
        $response = $this->get('/register');

        $response->assertStatus(200);
        $response->assertSee('Terms of Service');
        $response->assertSee('Privacy Policy');
        $response->assertSee('termsAccepted');
        $response->assertSee('showTermsModal');
    }
}
