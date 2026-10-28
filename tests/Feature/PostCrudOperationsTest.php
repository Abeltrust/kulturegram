<?php

namespace Tests\Feature;

use App\Models\Community;
use App\Models\CommunityPost;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PostCrudOperationsTest extends TestCase
{
    use RefreshDatabase;

    // ==========================================
    // TIMELINE POST CRUD
    // ==========================================

    public function test_user_can_create_timeline_post(): void
    {
        $user = User::factory()->create(['posts_count' => 0]);

        $response = $this->actingAs($user)->post(route('timeline.store'), [
            'content' => 'Reflecting on my family roots in Ibadan today. #Roots #Family',
            'chapter' => 'Roots & Foundation',
            'location' => 'Ibadan, Nigeria',
            'privacy' => 'public',
        ]);

        $response->assertRedirect(route('timeline.index'));

        $this->assertDatabaseHas('posts', [
            'user_id' => $user->id,
            'content' => 'Reflecting on my family roots in Ibadan today. #Roots #Family',
            'chapter' => 'Roots & Foundation',
            'location' => 'Ibadan, Nigeria',
            'privacy' => 'public',
        ]);

        $this->assertEquals(1, $user->fresh()->posts_count);
    }

    public function test_user_can_view_single_timeline_post(): void
    {
        $author = User::factory()->create();
        $viewer = User::factory()->create();

        $post = Post::factory()->create([
            'user_id' => $author->id,
            'content' => 'Exploring cultural pottery methods from ancient times.',
            'privacy' => 'public',
            'chapter' => 'Heritage Preservation',
        ]);

        $response = $this->actingAs($viewer)->get(route('timeline.show', $post));

        $response->assertStatus(200);
        $response->assertSee('Exploring cultural pottery methods from ancient times.');
        $response->assertSee('Heritage Preservation');
    }

    public function test_unauthorized_user_cannot_view_private_or_vault_timeline_post(): void
    {
        $author = User::factory()->create();
        $stranger = User::factory()->create();

        $privatePost = Post::factory()->create([
            'user_id' => $author->id,
            'privacy' => 'private',
            'content' => 'My secret family tradition.',
        ]);

        $vaultPost = Post::factory()->create([
            'user_id' => $author->id,
            'privacy' => 'vault',
            'content' => 'Private vault diary entry.',
        ]);

        // Stranger should be forbidden
        $this->actingAs($stranger)->get(route('timeline.show', $privatePost))->assertStatus(403);
        $this->actingAs($stranger)->get(route('timeline.show', $vaultPost))->assertStatus(403);

        // Author can view both
        $this->actingAs($author)->get(route('timeline.show', $privatePost))->assertStatus(200);
        $this->actingAs($author)->get(route('timeline.show', $vaultPost))->assertStatus(200);
    }

    public function test_post_author_can_retrieve_post_data_for_editing(): void
    {
        $author = User::factory()->create();
        $post = Post::factory()->create([
            'user_id' => $author->id,
            'content' => 'Initial story draft before revisions.',
            'chapter' => 'Cultural Tech Innovation',
            'location' => 'Abuja, Nigeria',
            'privacy' => 'public',
        ]);

        $response = $this->actingAs($author)->getJson(route('timeline.edit', $post));

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'post' => [
                    'id' => $post->id,
                    'content' => 'Initial story draft before revisions.',
                    'chapter' => 'Cultural Tech Innovation',
                    'location' => 'Abuja, Nigeria',
                    'privacy' => 'public',
                ]
            ]);
    }

    public function test_post_author_can_update_timeline_post(): void
    {
        $author = User::factory()->create();
        $post = Post::factory()->create([
            'user_id' => $author->id,
            'content' => 'Old draft story.',
            'chapter' => 'Roots & Foundation',
            'location' => 'Old Location',
            'privacy' => 'private',
        ]);

        $response = $this->actingAs($author)->putJson(route('timeline.update', $post), [
            'content' => 'Updated and polished cultural reflection. #Heritage #Culture',
            'chapter' => 'Software Architecture Mastery',
            'location' => 'Lagos, Nigeria',
            'privacy' => 'public',
            'language' => 'Yoruba',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Story updated successfully!',
            ]);

        $this->assertDatabaseHas('posts', [
            'id' => $post->id,
            'content' => 'Updated and polished cultural reflection. #Heritage #Culture',
            'chapter' => 'Software Architecture Mastery',
            'location' => 'Lagos, Nigeria',
            'privacy' => 'public',
            'language' => 'Yoruba',
        ]);
    }

    public function test_non_author_cannot_update_timeline_post(): void
    {
        $author = User::factory()->create();
        $stranger = User::factory()->create(['is_admin' => false]);
        $post = Post::factory()->create(['user_id' => $author->id]);

        $response = $this->actingAs($stranger)->putJson(route('timeline.update', $post), [
            'content' => 'Malicious edit attempt.',
        ]);

        $response->assertStatus(403);
    }

    public function test_post_author_can_delete_timeline_post(): void
    {
        Storage::fake('public');
        $author = User::factory()->create(['posts_count' => 5]);

        $imageFile = UploadedFile::fake()->create('test.jpg', 100, 'image/jpeg');
        $storedImagePath = $imageFile->store('posts', 'public');

        $post = Post::factory()->create([
            'user_id' => $author->id,
            'image' => $storedImagePath,
        ]);

        $this->assertTrue(Storage::disk('public')->exists($storedImagePath));

        $response = $this->actingAs($author)->deleteJson(route('timeline.destroy', $post));

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Story deleted successfully.',
                'post_id' => $post->id,
            ]);

        $this->assertDatabaseMissing('posts', ['id' => $post->id]);
        $this->assertFalse(Storage::disk('public')->exists($storedImagePath));
        $this->assertEquals(4, $author->fresh()->posts_count);
    }

    public function test_non_author_cannot_delete_timeline_post(): void
    {
        $author = User::factory()->create();
        $stranger = User::factory()->create(['is_admin' => false]);
        $post = Post::factory()->create(['user_id' => $author->id]);

        $response = $this->actingAs($stranger)->deleteJson(route('timeline.destroy', $post));

        $response->assertStatus(403);
        $this->assertDatabaseHas('posts', ['id' => $post->id]);
    }

    // ==========================================
    // COMMUNITY POST CRUD
    // ==========================================

    public function test_user_can_create_community_post(): void
    {
        $user = User::factory()->create();
        $community = Community::factory()->create();

        $response = $this->actingAs($user)->post(route('communities.posts.store', $community), [
            'content' => 'Hello everyone in the community! Glad to join.',
        ]);

        $response->assertRedirect(route('communities.show', $community->id));

        $this->assertDatabaseHas('community_posts', [
            'community_id' => $community->id,
            'user_id' => $user->id,
            'content' => 'Hello everyone in the community! Glad to join.',
        ]);
    }

    public function test_community_post_author_can_retrieve_edit_data(): void
    {
        $user = User::factory()->create();
        $community = Community::factory()->create();
        $post = CommunityPost::create([
            'community_id' => $community->id,
            'user_id' => $user->id,
            'content' => 'Original community question.',
        ]);

        $response = $this->actingAs($user)->getJson(route('communities.posts.edit', [$community, $post]));

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'post' => [
                    'id' => $post->id,
                    'content' => 'Original community question.',
                    'community_id' => $community->id,
                ]
            ]);
    }

    public function test_community_post_author_can_update_community_post(): void
    {
        $user = User::factory()->create();
        $community = Community::factory()->create();
        $post = CommunityPost::create([
            'community_id' => $community->id,
            'user_id' => $user->id,
            'content' => 'Original community post.',
        ]);

        $response = $this->actingAs($user)->putJson(route('communities.posts.update', [$community, $post]), [
            'content' => 'Updated community knowledge sharing notes.',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Post updated successfully!',
            ]);

        $this->assertDatabaseHas('community_posts', [
            'id' => $post->id,
            'content' => 'Updated community knowledge sharing notes.',
        ]);
    }

    public function test_community_post_author_can_delete_community_post(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();
        $community = Community::factory()->create();

        $imageFile = UploadedFile::fake()->create('post.jpg', 100, 'image/jpeg');
        $storedPath = $imageFile->store('community_posts', 'public');

        $post = CommunityPost::create([
            'community_id' => $community->id,
            'user_id' => $user->id,
            'content' => 'Post to be removed.',
            'image' => $storedPath,
        ]);

        $this->assertTrue(Storage::disk('public')->exists($storedPath));

        $response = $this->actingAs($user)->deleteJson(route('communities.posts.destroy', [$community, $post]));

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Post deleted successfully.',
            ]);

        $this->assertDatabaseMissing('community_posts', ['id' => $post->id]);
        $this->assertFalse(Storage::disk('public')->exists($storedPath));
    }

    public function test_stranger_cannot_update_or_delete_community_post(): void
    {
        $author = User::factory()->create();
        $stranger = User::factory()->create(['is_admin' => false]);
        $community = Community::factory()->create();
        $post = CommunityPost::create([
            'community_id' => $community->id,
            'user_id' => $author->id,
            'content' => 'Author protected post.',
        ]);

        $this->actingAs($stranger)->putJson(route('communities.posts.update', [$community, $post]), [
            'content' => 'Unauthorized update',
        ])->assertStatus(403);

        $this->actingAs($stranger)->deleteJson(route('communities.posts.destroy', [$community, $post]))
            ->assertStatus(403);

        $this->assertDatabaseHas('community_posts', ['id' => $post->id]);
    }

    // ==========================================
    // UI RENDERING TEST
    // ==========================================

    public function test_ui_renders_edit_and_delete_actions_for_post_author(): void
    {
        $author = User::factory()->create();
        $post = Post::factory()->create([
            'user_id' => $author->id,
            'content' => 'My unique story with actions menu.',
            'privacy' => 'public',
        ]);

        $response = $this->actingAs($author)->get(route('timeline.index', ['filter' => 'all']));

        $response->assertStatus(200);
        $response->assertSee('openEditTimelinePostModal(' . $post->id . ')', false);
        $response->assertSee('confirmDeleteTimelinePost(' . $post->id . ')', false);
    }
}
