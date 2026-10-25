<?php

namespace Tests\Feature;

use App\Models\Post;
use App\Models\User;
use App\Services\ContentParserService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PostMentionAndTagTest extends TestCase
{
    use RefreshDatabase;

    public function test_post_creation_extracts_hashtags_into_tags_column(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('timeline.store'), [
            'content' => 'Exploring our ancestral roots in #Lagos and celebrating #Heritage traditions! #lagos',
            'privacy' => 'public',
        ]);

        $response->assertRedirect(route('timeline.index'));

        $post = Post::where('user_id', $user->id)->first();
        $this->assertNotNull($post);
        $this->assertIsArray($post->tags);
        $this->assertContains('lagos', $post->tags);
        $this->assertContains('heritage', $post->tags);
        $this->assertCount(2, $post->tags); // Deduplicated and lowercased
    }

    public function test_post_creation_notifies_mentioned_users(): void
    {
        $author = User::factory()->create(['name' => 'Kofi Mensah', 'username' => 'kofi']);
        $mentioned1 = User::factory()->create(['name' => 'Amina Bello', 'username' => 'amina']);
        $mentioned2 = User::factory()->create(['name' => 'Chioma Eze', 'username' => 'chioma']);

        $response = $this->actingAs($author)->post(route('timeline.store'), [
            'content' => 'Grateful for the cultural workshop with @amina and @chioma today! #Community',
            'privacy' => 'public',
        ]);

        $response->assertRedirect(route('timeline.index'));

        // Check notification for amina
        $this->assertDatabaseHas('notifications', [
            'user_id' => $mentioned1->id,
            'from_user_id' => $author->id,
            'type' => 'mention',
            'title' => 'Mentioned in a Story',
        ]);

        // Check notification for chioma
        $this->assertDatabaseHas('notifications', [
            'user_id' => $mentioned2->id,
            'from_user_id' => $author->id,
            'type' => 'mention',
            'title' => 'Mentioned in a Story',
        ]);

        // Author should not be notified
        $this->assertDatabaseMissing('notifications', [
            'user_id' => $author->id,
            'type' => 'mention',
        ]);
    }

    public function test_author_self_mention_does_not_create_notification(): void
    {
        $author = User::factory()->create(['name' => 'Kwame', 'username' => 'kwame']);

        $this->actingAs($author)->post(route('timeline.store'), [
            'content' => 'Notes by @kwame regarding oral history.',
            'privacy' => 'public',
        ]);

        $this->assertDatabaseMissing('notifications', [
            'user_id' => $author->id,
            'type' => 'mention',
        ]);
    }

    public function test_resonance_notifies_post_author(): void
    {
        $postAuthor = User::factory()->create(['name' => 'Emeka', 'username' => 'emeka']);
        $commenter = User::factory()->create(['name' => 'Tunde', 'username' => 'tunde']);

        $post = Post::factory()->create([
            'user_id' => $postAuthor->id,
            'content' => 'Original cultural story.',
            'privacy' => 'public',
        ]);

        $response = $this->actingAs($commenter)->postJson(route('timeline.resonance', $post), [
            'content' => 'This resonates deeply with my family history.',
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $this->assertDatabaseHas('notifications', [
            'user_id' => $postAuthor->id,
            'from_user_id' => $commenter->id,
            'type' => 'resonance',
            'title' => 'New Resonance',
        ]);
    }

    public function test_resonance_with_mention_notifies_mentioned_user(): void
    {
        $postAuthor = User::factory()->create(['name' => 'Emeka', 'username' => 'emeka']);
        $commenter = User::factory()->create(['name' => 'Tunde', 'username' => 'tunde']);
        $friend = User::factory()->create(['name' => 'Zainab', 'username' => 'zainab']);

        $post = Post::factory()->create([
            'user_id' => $postAuthor->id,
            'content' => 'Great oral tradition.',
            'privacy' => 'public',
        ]);

        $response = $this->actingAs($commenter)->postJson(route('timeline.resonance', $post), [
            'content' => 'Check this out @zainab, this reminds me of our grandmother!',
        ]);

        $response->assertStatus(200);

        // Friend received mention notification
        $this->assertDatabaseHas('notifications', [
            'user_id' => $friend->id,
            'from_user_id' => $commenter->id,
            'type' => 'mention',
            'title' => 'Mentioned in a Resonance',
        ]);
    }

    public function test_timeline_can_filter_by_tag(): void
    {
        $user = User::factory()->create();

        $post1 = Post::factory()->create([
            'user_id' => $user->id,
            'content' => 'Story about #yoruba festivals',
            'tags' => ['yoruba'],
            'privacy' => 'public',
        ]);

        $post2 = Post::factory()->create([
            'user_id' => $user->id,
            'content' => 'Story about culinary arts',
            'tags' => ['food'],
            'privacy' => 'public',
        ]);

        $response = $this->actingAs($user)->get(route('timeline.index', ['tag' => 'yoruba']));

        $response->assertStatus(200);
        $response->assertSee('Filtering stories tagged with');
        $response->assertSee('#yoruba');
        $response->assertSee('Story about');
    }

    public function test_content_parser_formats_links_safely(): void
    {
        $raw = "Hello @johndoe check #AfricanCulture! <script>alert('xss')</script>";
        $formatted = ContentParserService::format($raw);

        // Mentions transformed to link
        $this->assertStringContainsString('href="' . url('/profile/johndoe') . '"', $formatted);
        $this->assertStringContainsString('@johndoe</a>', $formatted);

        // Tags transformed to link
        $this->assertStringContainsString('href="' . url('/timeline?tag=africanculture') . '"', $formatted);
        $this->assertStringContainsString('#AfricanCulture</a>', $formatted);

        // XSS script tags properly escaped
        $this->assertStringNotContainsString("<script>", $formatted);
        $this->assertStringContainsString("&lt;script&gt;", $formatted);
    }
}
