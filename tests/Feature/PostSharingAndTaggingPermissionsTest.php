<?php

namespace Tests\Feature;

use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PostSharingAndTaggingPermissionsTest extends TestCase
{
    use RefreshDatabase;

    protected User $owner;
    protected User $lockedInMember;
    protected User $nonLockedInMember;
    protected User $taggedTarget;

    protected function setUp(): void
    {
        parent::setUp();

        $this->owner = User::factory()->create([
            'name' => 'Custodian Owner',
            'username' => 'custodian_owner',
        ]);

        $this->lockedInMember = User::factory()->create([
            'name' => 'LockedIn Member',
            'username' => 'lockedin_member',
        ]);

        $this->nonLockedInMember = User::factory()->create([
            'name' => 'Visitor Member',
            'username' => 'visitor_member',
        ]);

        $this->taggedTarget = User::factory()->create([
            'name' => 'Amina Bello',
            'username' => 'aminabello',
        ]);

        // lockedInMember locks-in to owner
        $this->lockedInMember->interactions()->create([
            'interactable_type' => User::class,
            'interactable_id' => $this->owner->id,
            'type' => 'lockin',
        ]);
    }

    public function test_owner_can_create_post_with_tagged_users_and_display_header_badge(): void
    {
        $response = $this->actingAs($this->owner)->post(route('timeline.store'), [
            'content' => 'Celebrating our annual Durbar heritage festival!',
            'privacy' => 'public',
            'tagged_users' => [$this->taggedTarget->id, $this->lockedInMember->id],
        ]);

        $response->assertRedirect(route('timeline.index'));

        $post = Post::where('user_id', $this->owner->id)->first();
        $this->assertNotNull($post);
        $this->assertContains($this->taggedTarget->id, $post->tagged_users);
        $this->assertContains($this->lockedInMember->id, $post->tagged_users);

        // Tagged users receive notifications
        $this->assertDatabaseHas('notifications', [
            'user_id' => $this->taggedTarget->id,
            'from_user_id' => $this->owner->id,
            'type' => 'mention',
        ]);

        // Timeline displays the "is with @username" badge
        $feed = $this->actingAs($this->owner)->get(route('timeline.index', ['filter' => 'all']));
        $feed->assertStatus(200);
        $feed->assertSee('@aminabello');
        $feed->assertSee('is with');
    }

    public function test_locked_in_member_can_repost_and_tag_people_on_owners_post(): void
    {
        $post = Post::create([
            'user_id' => $this->owner->id,
            'content' => 'Ancient Kano city walls oral history.',
            'privacy' => 'public',
            'type' => 'text',
        ]);

        // 1. Locked-in member can repost with commentary and tagged users
        $repostRes = $this->actingAs($this->lockedInMember)->postJson(route('timeline.repost', $post), [
            'content' => 'Everyone should read this heritage story!',
            'tagged_users' => [$this->taggedTarget->id],
        ]);

        $repostRes->assertStatus(200)
            ->assertJson([
                'success' => true,
                'shares_count' => 1,
            ]);

        $repost = Post::find($repostRes->json('repost_id'));
        $this->assertNotNull($repost);
        $this->assertEquals($post->id, $repost->shared_post_id);
        $this->assertContains($this->taggedTarget->id, $repost->tagged_users);

        // 2. Locked-in member can tag people directly on the post
        $tagRes = $this->actingAs($this->lockedInMember)->postJson(route('timeline.tags.update', $post), [
            'tagged_users' => [$this->taggedTarget->id],
        ]);

        $tagRes->assertStatus(200)
            ->assertJson(['success' => true]);

        $this->assertContains($this->taggedTarget->id, $post->fresh()->tagged_users);

        // 3. Locked-in member can @mention/tag someone in a resonance on the post
        $resComment = $this->actingAs($this->lockedInMember)->postJson(route('timeline.resonance', $post), [
            'content' => 'Great insight @aminabello check this out!',
        ]);

        $resComment->assertStatus(200)
            ->assertJson(['success' => true]);
    }

    public function test_non_locked_in_member_cannot_repost_or_tag_until_they_lock_in(): void
    {
        $post = Post::create([
            'user_id' => $this->owner->id,
            'content' => 'Yoruba talking drum rhythms explained.',
            'privacy' => 'public',
            'type' => 'text',
        ]);

        // 1. Non-locked-in member cannot repost to timeline (gets 403 requires_lockin)
        $repostAttempt = $this->actingAs($this->nonLockedInMember)->postJson(route('timeline.repost', $post), [
            'content' => 'Trying to repost without locking in',
        ]);

        $repostAttempt->assertStatus(403)
            ->assertJson([
                'success' => false,
                'requires_lockin' => true,
            ]);

        // 2. Non-locked-in member cannot tag people on the post
        $tagAttempt = $this->actingAs($this->nonLockedInMember)->postJson(route('timeline.tags.update', $post), [
            'tagged_users' => [$this->taggedTarget->id],
        ]);

        $tagAttempt->assertStatus(403)
            ->assertJson([
                'success' => false,
                'requires_lockin' => true,
            ]);

        // 3. Non-locked-in member cannot @username tag people in resonances on the post
        $mentionCommentAttempt = $this->actingAs($this->nonLockedInMember)->postJson(route('timeline.resonance', $post), [
            'content' => 'Tagging @aminabello without being locked in',
        ]);

        $mentionCommentAttempt->assertStatus(403)
            ->assertJson([
                'success' => false,
                'requires_lockin' => true,
            ]);

        // 4. Non-locked-in member CAN still post a regular resonance without @tags
        $plainComment = $this->actingAs($this->nonLockedInMember)->postJson(route('timeline.resonance', $post), [
            'content' => 'Wonderful cultural story, thank you for sharing!',
        ]);

        $plainComment->assertStatus(200)
            ->assertJson(['success' => true]);

        // 5. Once the member Locks-In to the owner, reposting and tagging are unlocked!
        $this->actingAs($this->nonLockedInMember)->postJson(route('users.lockin', $this->owner))
            ->assertStatus(200)
            ->assertJson(['lockedIn' => true]);

        $unlockedRepost = $this->actingAs($this->nonLockedInMember->fresh())->postJson(route('timeline.repost', $post->fresh()), [
            'content' => 'Now locked-in and sharing with @aminabello!',
            'tagged_users' => [$this->taggedTarget->id],
        ]);

        $unlockedRepost->assertStatus(200)
            ->assertJson(['success' => true]);
    }

    public function test_tagged_user_can_remove_their_own_tag_from_post(): void
    {
        $post = Post::create([
            'user_id' => $this->owner->id,
            'content' => 'Festival gathering with friends.',
            'privacy' => 'public',
            'type' => 'text',
            'tagged_users' => [$this->taggedTarget->id, $this->lockedInMember->id],
        ]);

        $removeRes = $this->actingAs($this->taggedTarget)->deleteJson(route('timeline.tags.remove-self', $post));
        $removeRes->assertStatus(200)
            ->assertJson(['success' => true]);

        $freshTags = $post->fresh()->tagged_users;
        $this->assertNotContains($this->taggedTarget->id, $freshTags);
        $this->assertContains($this->lockedInMember->id, $freshTags);
    }
    public function test_user_can_only_message_members_they_are_locked_in_with(): void
    {
        // 1. nonLockedInMember cannot start conversation with owner
        $deniedStart = $this->actingAs($this->nonLockedInMember)->get(route('messages.start', $this->owner));
        $deniedStart->assertRedirect(route('profile.user', $this->owner));
        $deniedStart->assertSessionHas('error');

        // 2. lockedInMember CAN start conversation with owner
        $allowedStart = $this->actingAs($this->lockedInMember)->get(route('messages.start', $this->owner));
        $allowedStart->assertRedirect(); // redirects to messages.show

        $conversation = \App\Models\Conversation::latest()->first();
        $this->assertNotNull($conversation);

        // 3. lockedInMember can send message
        $sendRes = $this->actingAs($this->lockedInMember)->postJson(route('messages.store', $conversation), [
            'content' => 'Hello Custodian Owner, glad to connect!',
        ]);
        $sendRes->assertStatus(200)->assertJson(['success' => true]);

        // 4. nonLockedInMember cannot send message to that conversation
        $unauthorizedSend = $this->actingAs($this->nonLockedInMember)->postJson(route('messages.store', $conversation), [
            'content' => 'Trying to message without locking in',
        ]);
        $unauthorizedSend->assertStatus(403);
    }

    public function test_taggable_users_endpoint_filters_to_locked_in_and_co_tagged_connections(): void
    {
        // lockedInMember queries taggable users
        $res = $this->actingAs($this->lockedInMember)->getJson(route('timeline.taggable-users'));
        $res->assertStatus(200);

        $userIds = collect($res->json('users'))->pluck('id')->all();
        $this->assertContains($this->owner->id, $userIds);
        // nonLockedInMember is NOT locked in or tagged with lockedInMember, so should not be in the primary list
        $this->assertNotContains($this->nonLockedInMember->id, $userIds);
    }

    public function test_timeline_feed_includes_infrequent_updates_from_vibed_profiles(): void
    {
        // nonLockedInMember vibes owner's profile (type => tap)
        $this->nonLockedInMember->interactions()->create([
            'interactable_type' => User::class,
            'interactable_id' => $this->owner->id,
            'type' => 'tap',
        ]);

        $ownerPost = Post::create([
            'user_id' => $this->owner->id,
            'content' => 'Living tradition oral narrative from Kano',
            'privacy' => 'public',
            'type' => 'text',
        ]);

        $feedRes = $this->actingAs($this->nonLockedInMember)->get(route('timeline.index'));
        $feedRes->assertStatus(200);
        $feedRes->assertSee('Living tradition oral narrative from Kano');
    }
}
