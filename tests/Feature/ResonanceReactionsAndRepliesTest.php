<?php

namespace Tests\Feature;

use App\Models\Post;
use App\Models\Resonance;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ResonanceReactionsAndRepliesTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_submit_top_level_resonance_to_post(): void
    {
        $postAuthor = User::factory()->create();
        $commenter = User::factory()->create();
        $post = Post::factory()->create(['user_id' => $postAuthor->id]);

        $response = $this->actingAs($commenter)->postJson(route('timeline.resonance', $post), [
            'content' => 'This is a deeply inspiring cultural story!',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'count' => 1,
            ]);

        $this->assertDatabaseHas('resonances', [
            'post_id' => $post->id,
            'user_id' => $commenter->id,
            'content' => 'This is a deeply inspiring cultural story!',
            'parent_id' => null,
            'taps_count' => 0,
        ]);

        // Post author should receive resonance notification
        $this->assertDatabaseHas('notifications', [
            'user_id' => $postAuthor->id,
            'from_user_id' => $commenter->id,
            'type' => 'resonance',
            'title' => 'New Resonance',
        ]);
    }

    public function test_user_can_reply_to_existing_resonance(): void
    {
        $postAuthor = User::factory()->create(['name' => 'Amara Kalu']);
        $commenter = User::factory()->create(['name' => 'Babajide Cole']);
        $replier = User::factory()->create(['name' => 'Ngozi Ade']);
        $post = Post::factory()->create(['user_id' => $postAuthor->id]);

        $rootResonance = Resonance::create([
            'user_id' => $commenter->id,
            'post_id' => $post->id,
            'content' => 'I love how traditional weaving preserves our cultural values.',
            'parent_id' => null,
        ]);

        $response = $this->actingAs($replier)->postJson(route('timeline.resonance', $post), [
            'content' => 'Completely agree @BabajideCole! The patterns hold sacred proverbs.',
            'parent_id' => $rootResonance->id,
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'count' => 2,
            ]);

        $this->assertDatabaseHas('resonances', [
            'post_id' => $post->id,
            'user_id' => $replier->id,
            'content' => 'Completely agree @BabajideCole! The patterns hold sacred proverbs.',
            'parent_id' => $rootResonance->id,
        ]);

        // Check relationship
        $this->assertEquals(1, $rootResonance->fresh()->replies()->count());
        $reply = $rootResonance->fresh()->replies()->first();
        $this->assertEquals($replier->id, $reply->user_id);
        $this->assertEquals($rootResonance->id, $reply->parent->id);

        // Check notification to parent comment author
        $this->assertDatabaseHas('notifications', [
            'user_id' => $commenter->id,
            'from_user_id' => $replier->id,
            'type' => 'resonance_reply',
            'title' => 'New Reply to Your Resonance',
        ]);

        // Check notification to post author
        $this->assertDatabaseHas('notifications', [
            'user_id' => $postAuthor->id,
            'from_user_id' => $replier->id,
            'type' => 'resonance',
            'title' => 'New Reply on Your Story',
        ]);
    }

    public function test_user_can_react_tap_to_resonance_and_toggle_off(): void
    {
        $postAuthor = User::factory()->create();
        $commenter = User::factory()->create();
        $reactor = User::factory()->create();
        $post = Post::factory()->create(['user_id' => $postAuthor->id]);

        $resonance = Resonance::create([
            'user_id' => $commenter->id,
            'post_id' => $post->id,
            'content' => 'Great perspective on our ancestral traditions.',
            'parent_id' => null,
            'taps_count' => 0,
        ]);

        // Tap on
        $response = $this->actingAs($reactor)->postJson(route('resonances.tap', $resonance));

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'tapped' => true,
                'count' => 1,
                'is_author_liked' => false,
            ]);

        $this->assertEquals(1, $resonance->fresh()->taps_count);
        $this->assertTrue($resonance->isTappedBy($reactor));

        // Notification created for commenter
        $this->assertDatabaseHas('notifications', [
            'user_id' => $commenter->id,
            'from_user_id' => $reactor->id,
            'type' => 'resonance_tap',
            'title' => 'New Reaction to Your Comment',
        ]);

        // Tap off
        $response2 = $this->actingAs($reactor)->postJson(route('resonances.tap', $resonance));

        $response2->assertStatus(200)
            ->assertJson([
                'success' => true,
                'tapped' => false,
                'count' => 0,
            ]);

        $this->assertEquals(0, $resonance->fresh()->taps_count);
        $this->assertFalse($resonance->isTappedBy($reactor));
    }

    public function test_post_author_tapping_comment_marks_it_as_author_liked(): void
    {
        $postAuthor = User::factory()->create(['name' => 'Author Sarah']);
        $commenter = User::factory()->create();
        $post = Post::factory()->create(['user_id' => $postAuthor->id]);

        $resonance = Resonance::create([
            'user_id' => $commenter->id,
            'post_id' => $post->id,
            'content' => 'Loved reading this chapter of your journey!',
            'parent_id' => null,
            'taps_count' => 0,
        ]);

        // Author taps the comment
        $response = $this->actingAs($postAuthor)->postJson(route('resonances.tap', $resonance));

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'tapped' => true,
                'count' => 1,
                'is_author_liked' => true,
            ]);

        $this->assertTrue($resonance->fresh()->isAuthorLiked());

        // Special notification for author liking comment
        $this->assertDatabaseHas('notifications', [
            'user_id' => $commenter->id,
            'from_user_id' => $postAuthor->id,
            'type' => 'resonance_tap',
            'title' => 'Story Author Liked Your Comment',
        ]);
    }
}
