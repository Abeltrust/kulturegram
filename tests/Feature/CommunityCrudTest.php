<?php

namespace Tests\Feature;

use App\Models\Community;
use App\Models\CommunityPost;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CommunityCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_communities_index_can_be_rendered(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('communities.index'));

        $response->assertStatus(200);
        $response->assertSee('Communities');
    }

    public function test_user_can_create_community(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('communities.store'), [
            'name' => 'Afrobeat Archivists',
            'description' => 'Preserving the roots of Afrobeat culture.',
            'category' => 'Music',
            'rules' => 'Respect all participants and original masters.',
            'is_private' => false,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('communities', [
            'name' => 'Afrobeat Archivists',
            'created_by' => $user->id,
        ]);
        // User should automatically be member with admin role
        $this->assertDatabaseHas('community_members', [
            'community_id' => Community::where('name', 'Afrobeat Archivists')->first()->id,
            'user_id' => $user->id,
            'role' => 'admin',
        ]);
    }

    public function test_creator_can_update_community(): void
    {
        $user = User::factory()->create();
        $community = Community::factory()->create(['created_by' => $user->id]);

        $response = $this->actingAs($user)->put(route('communities.update', $community), [
            'name' => 'Updated Community Name',
            'description' => 'Updated description content.',
            'category' => 'Art',
            'is_private' => true,
        ]);

        $response->assertRedirect(route('communities.show', $community));
        $this->assertDatabaseHas('communities', [
            'id' => $community->id,
            'name' => 'Updated Community Name',
            'is_private' => 1,
        ]);
    }

    public function test_non_admin_cannot_update_or_delete_community(): void
    {
        $creator = User::factory()->create();
        $otherUser = User::factory()->create();
        $community = Community::factory()->create(['created_by' => $creator->id]);

        $updateResponse = $this->actingAs($otherUser)->put(route('communities.update', $community), [
            'name' => 'Hacked Community',
            'description' => 'Unauthorized update',
            'category' => 'Art',
        ]);
        $updateResponse->assertStatus(403);

        $deleteResponse = $this->actingAs($otherUser)->delete(route('communities.destroy', $community));
        $deleteResponse->assertStatus(403);
    }

    public function test_user_can_join_and_leave_community(): void
    {
        $creator = User::factory()->create();
        $member = User::factory()->create();
        $community = Community::factory()->create(['created_by' => $creator->id]);

        // Join
        $joinResponse = $this->actingAs($member)->post(route('communities.join', $community));
        $joinResponse->assertRedirect();
        $this->assertTrue($community->members()->where('user_id', $member->id)->exists());

        // Leave
        $leaveResponse = $this->actingAs($member)->delete(route('communities.leave', $community));
        $leaveResponse->assertRedirect();
        $this->assertFalse($community->members()->where('user_id', $member->id)->exists());
    }

    public function test_user_can_post_and_update_post_in_community(): void
    {
        $user = User::factory()->create();
        $community = Community::factory()->create();

        // Create post
        $postResponse = $this->actingAs($user)->post(route('communities.posts.store', $community), [
            'content' => 'First discussion topic in the community!',
        ]);
        $postResponse->assertRedirect();
        $post = CommunityPost::where('community_id', $community->id)->first();
        $this->assertNotNull($post);
        $this->assertEquals('First discussion topic in the community!', $post->content);

        // Update post
        $updatePostResponse = $this->actingAs($user)->put(route('communities.posts.update', [$community, $post]), [
            'content' => 'Updated discussion topic in the community!',
        ]);
        $updatePostResponse->assertRedirect();
        $this->assertEquals('Updated discussion topic in the community!', $post->fresh()->content);
    }

    public function test_creator_can_delete_community(): void
    {
        $creator = User::factory()->create();
        $community = Community::factory()->create(['created_by' => $creator->id]);

        $response = $this->actingAs($creator)->delete(route('communities.destroy', $community));

        $response->assertRedirect(route('communities.index'));
        $this->assertDatabaseMissing('communities', ['id' => $community->id]);
    }
}
