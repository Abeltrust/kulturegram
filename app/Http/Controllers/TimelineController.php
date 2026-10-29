<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\Resonance;
use App\Models\User;
use App\Services\ContentParserService;
use App\Services\MetaAI\MultilingualCulturalAccessService;
use App\Services\MetaAI\OralTraditionVoiceService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class TimelineController extends Controller
{
    public function index(Request $request)
    {
        $filter = $request->get('filter', 'all');

        $query = Post::with([
            'user',
            'sharedPost.user',
            'taps',
            'resonances.user',
            'resonances.replies.user',
            'resonances.interactions',
            'resonances.replies.interactions',
            'lockIns',
            'checkIns'
        ])
            ->public()
            ->latest();

        // Filter by Tag
        if ($request->filled('tag')) {
            $tag = mb_strtolower(ltrim($request->tag, '#'));
            $query->where(function ($q) use ($tag) {
                $q->whereJsonContains('tags', $tag)
                  ->orWhere('content', 'like', "%#{$tag}%");
            });
        }

        // Search Filter
        if ($request->filled('search')) {
            $search = $request->search;
            $cleanSearch = mb_strtolower(ltrim($search, '#'));
            $query->where(function ($q) use ($search, $cleanSearch) {
                $q->where('content', 'like', "%{$search}%")
                  ->orWhere('location', 'like', "%{$search}%")
                  ->orWhere('chapter', 'like', "%{$search}%")
                  ->orWhereJsonContains('tags', $cleanSearch)
                  ->orWhereHas('user', function ($uq) use ($search) {
                      $uq->where('name', 'like', "%{$search}%")
                         ->orWhere('username', 'like', "%{$search}%");
                  });
            });
        }

        if (Auth::check()) {
            if (!$request->has('filter') && !$request->has('tag') && !$request->has('search')) {
                $filter = 'lockedin';
            }

            switch ($filter) {
                case 'lockedin':
                    $authUser = Auth::user();
                    $authId = (int) $authUser->id;
                    $lockedInIds = $authUser->lockedInUserIds();

                    // Vibed profiles: see their updates occasionally / not frequently
                    $vibedUserIds = array_values(array_diff($authUser->vibedUserIds(), $lockedInIds));
                    $infrequentPostIds = [];

                    if (!empty($vibedUserIds)) {
                        $vibedPosts = Post::public()
                            ->whereIn('user_id', $vibedUserIds)
                            ->latest()
                            ->take(2)
                            ->pluck('id')
                            ->all();
                        $infrequentPostIds = array_merge($infrequentPostIds, $vibedPosts);
                    }

                    // Seamless AI suggested users embedded within timeline without him even knowing
                    try {
                        $recService = app(\App\Services\MetaAI\CulturalConnectionRecommendationService::class);
                        $recData = $recService->recommendUsers($authUser, 3);
                        $suggestedUserIds = [];
                        foreach (($recData['recommendations'] ?? []) as $rec) {
                            $rId = (int) ($rec['user']['id'] ?? 0);
                            if ($rId > 0 && $rId !== $authId && !in_array($rId, $lockedInIds, true) && !in_array($rId, $vibedUserIds, true)) {
                                $suggestedUserIds[] = $rId;
                            }
                        }
                        if (!empty($suggestedUserIds)) {
                            $aiSuggestedPosts = Post::public()
                                ->whereIn('user_id', array_slice($suggestedUserIds, 0, 2))
                                ->latest()
                                ->take(2)
                                ->pluck('id')
                                ->all();
                            $infrequentPostIds = array_merge($infrequentPostIds, $aiSuggestedPosts);
                        }
                    } catch (\Throwable $e) {
                        // Silently skip suggestions if recommendation service is unavailable
                    }

                    $infrequentPostIds = array_values(array_unique($infrequentPostIds));

                    $query->where(function ($q) use ($lockedInIds, $authId, $infrequentPostIds) {
                        $q->whereIn('user_id', $lockedInIds)
                          ->orWhere('user_id', $authId)
                          ->orWhereJsonContains('tagged_users', $authId);

                        if (!empty($infrequentPostIds)) {
                            $q->orWhereIn('id', $infrequentPostIds);
                        }
                    });
                    break;
                case 'cultural':
                    $query->whereNotNull('chapter')
                        ->where('chapter', 'like', '%cultural%');
                    break;
            }
        }

        $posts = $query->paginate(10);

        return view('timeline.index', compact('posts', 'filter'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'content' => 'required|string|max:2000',
            'chapter' => 'nullable|string',
            'location' => 'nullable|string',
            'language' => 'nullable|string|max:60',
            'oral_transcript' => 'nullable|string',
            'is_ai_formatted' => 'nullable|boolean',
            'privacy' => 'in:public,private,vault',
            'type' => 'nullable|in:text,image,video,audio',
            'tags' => 'nullable|array',
            'tagged_users' => 'nullable|array',
            'tagged_users.*' => 'integer|exists:users,id',
            'image' => 'nullable|file|mimes:jpeg,png,jpg,gif,webp|max:10240',
            'video' => 'nullable|file|mimes:mp4,mov,avi,webm|max:51200',
            'audio' => 'nullable|file|mimes:mp3,wav,ogg,m4a|max:20480',
        ]);

        $post = new Post($request->only(['content', 'chapter', 'location', 'privacy', 'language', 'oral_transcript', 'is_ai_formatted']));
        $post->user_id = Auth::id();
        $post->type = $request->input('type', 'text');

        if (!$post->language && Auth::check()) {
            $post->language = Auth::user()->getPreferredLanguage();
        }

        // Extract and merge hashtags
        $extractedTags = ContentParserService::extractHashtags($request->input('content'));
        $manualTags = is_array($request->tags) ? $request->tags : [];
        $mergedTags = array_values(array_unique(array_merge($manualTags, $extractedTags)));
        if (!empty($mergedTags)) {
            $post->tags = $mergedTags;
        }

        // Resolve tagged users (Owner can tag anyone via explicit picker or @username mentions)
        $mentionedUsers = ContentParserService::getMentionedUsers($post->content);
        $explicitTaggedIds = is_array($request->tagged_users)
            ? array_map('intval', $request->tagged_users)
            : [];
        $allTaggedIds = array_values(array_unique(array_filter(
            array_merge($explicitTaggedIds, $mentionedUsers->pluck('id')->map(fn ($id) => (int) $id)->all()),
            fn ($id) => $id > 0 && $id !== (int) Auth::id()
        )));

        if (!empty($allTaggedIds)) {
            $post->tagged_users = $allTaggedIds;
        }

        // Handle Media Uploads
        if ($request->hasFile('image')) {
            $post->image = $request->file('image')->store('posts', 'public');
            $post->type = 'image';
        } elseif ($request->hasFile('video')) {
            $post->video = $request->file('video')->store('posts/videos', 'public');
            $post->type = 'video';
        } elseif ($request->hasFile('audio')) {
            $post->audio = $request->file('audio')->store('posts/audio', 'public');
            $post->type = 'audio';
        }

        $post->save();

        // Update user's posts count
        Auth::user()->increment('posts_count');

        // Dispatch notifications for all tagged & mentioned users
        if (!empty($allTaggedIds)) {
            $taggedUserModels = User::whereIn('id', $allTaggedIds)->get();
            foreach ($taggedUserModels as $taggedUser) {
                if ($taggedUser->id !== Auth::id()) {
                    $this->safeNotify($taggedUser, [
                        'from_user_id' => Auth::id(),
                        'type' => 'mention',
                        'title' => 'Tagged in a Story',
                        'message' => Auth::user()->name . ' tagged you in a story: "' . Str::limit($post->content, 60) . '"',
                        'data' => [
                            'post_id' => $post->id,
                            'author_name' => Auth::user()->name,
                            'url' => route('timeline.show', $post->id),
                        ],
                    ]);
                }
            }
        }

        return redirect()->route('timeline.index')
            ->with('success', 'Story shared successfully!');
    }

    /**
     * Display a single timeline post.
     */
    public function show(Request $request, Post $post)
    {
        // Check privacy access
        if ($post->privacy === 'private' && (!Auth::check() || ($post->user_id !== Auth::id() && !Auth::user()->is_admin))) {
            abort(403, 'This story is private.');
        }

        if ($post->privacy === 'vault' && (!Auth::check() || $post->user_id !== Auth::id())) {
            abort(403, 'This story is stored in a personal vault.');
        }

        $post->load([
            'user',
            'sharedPost.user',
            'taps',
            'resonances.user',
            'resonances.replies.user',
            'resonances.interactions',
            'resonances.replies.interactions',
            'lockIns',
            'checkIns'
        ]);

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'post' => $post]);
        }

        return view('timeline.show', compact('post'));
    }

    /**
     * Retrieve post details for editing.
     */
    public function edit(Request $request, Post $post)
    {
        if ($post->user_id != Auth::id() && !Auth::user()->is_admin) {
            abort(403, 'You are not authorized to edit this story.');
        }

        return response()->json([
            'success' => true,
            'post' => [
                'id' => $post->id,
                'content' => $post->content,
                'chapter' => $post->chapter,
                'location' => $post->location,
                'language' => $post->language,
                'privacy' => $post->privacy,
                'tags' => $post->tags,
                'tagged_users' => $post->tagged_users_models->map(fn ($u) => [
                    'id' => $u->id,
                    'name' => $u->name,
                    'username' => $u->username,
                    'avatar' => $u->profile_photo_url,
                ])->values(),
                'image_url' => $post->image ? Storage::url($post->image) : null,
                'video_url' => $post->video ? Storage::url($post->video) : null,
                'audio_url' => $post->audio ? Storage::url($post->audio) : null,
            ],
        ]);
    }

    /**
     * Update a timeline post.
     */
    public function update(Request $request, Post $post)
    {
        if ($post->user_id != Auth::id() && !Auth::user()->is_admin) {
            abort(403, 'You are not authorized to edit this story.');
        }

        $request->validate([
            'content' => 'required|string|max:2000',
            'chapter' => 'nullable|string',
            'location' => 'nullable|string',
            'language' => 'nullable|string|max:60',
            'privacy' => 'nullable|in:public,private,vault',
            'tags' => 'nullable|array',
            'tagged_users' => 'nullable|array',
            'tagged_users.*' => 'integer|exists:users,id',
            'remove_image' => 'nullable|boolean',
            'remove_video' => 'nullable|boolean',
            'remove_audio' => 'nullable|boolean',
            'image' => 'nullable|file|mimes:jpeg,png,jpg,gif,webp|max:10240',
            'video' => 'nullable|file|mimes:mp4,mov,avi,webm|max:51200',
            'audio' => 'nullable|file|mimes:mp3,wav,ogg,m4a|max:20480',
        ]);

        $previousTaggedIds = is_array($post->tagged_users)
            ? array_map('intval', $post->tagged_users)
            : [];

        $post->content = $request->input('content');
        if ($request->has('chapter')) $post->chapter = $request->input('chapter');
        if ($request->has('location')) $post->location = $request->input('location');
        if ($request->has('language')) $post->language = $request->input('language');
        if ($request->has('privacy')) $post->privacy = $request->input('privacy');

        // Extract hashtags and merge with existing or manual tags
        $extractedTags = ContentParserService::extractHashtags($request->input('content'));
        $manualTags = is_array($request->tags) ? $request->tags : [];
        $mergedTags = array_values(array_unique(array_merge($manualTags, $extractedTags)));
        $post->tags = !empty($mergedTags) ? $mergedTags : null;

        // Update tagged users if provided or mentioned in content
        if ($request->has('tagged_users') || ContentParserService::extractMentionUsernames($post->content)) {
            $mentionedIds = ContentParserService::getMentionedUsers($post->content)
                ->pluck('id')
                ->map(fn ($id) => (int) $id)
                ->all();
            $explicitIds = $request->has('tagged_users')
                ? array_map('intval', (array) $request->input('tagged_users', []))
                : $previousTaggedIds;
            $updatedTaggedIds = array_values(array_unique(array_filter(
                array_merge($explicitIds, $mentionedIds),
                fn ($id) => $id > 0 && $id !== (int) $post->user_id
            )));
            $post->tagged_users = !empty($updatedTaggedIds) ? $updatedTaggedIds : null;

            $newlyTaggedIds = array_diff($updatedTaggedIds, $previousTaggedIds);
            if (!empty($newlyTaggedIds)) {
                foreach (User::whereIn('id', $newlyTaggedIds)->get() as $newTaggedUser) {
                    $this->safeNotify($newTaggedUser, [
                        'from_user_id' => Auth::id(),
                        'type' => 'mention',
                        'title' => 'Tagged in a Story',
                        'message' => Auth::user()->name . ' tagged you in a story: "' . Str::limit($post->content, 60) . '"',
                        'data' => [
                            'post_id' => $post->id,
                            'author_name' => Auth::user()->name,
                            'url' => route('timeline.show', $post->id),
                        ],
                    ]);
                }
            }
        }

        // Handle Media Removal
        if ($request->boolean('remove_image') && $post->image) {
            Storage::disk('public')->delete($post->image);
            $post->image = null;
        }
        if ($request->boolean('remove_video') && $post->video) {
            Storage::disk('public')->delete($post->video);
            $post->video = null;
        }
        if ($request->boolean('remove_audio') && $post->audio) {
            Storage::disk('public')->delete($post->audio);
            $post->audio = null;
        }

        // Handle Media Replacement/Upload
        if ($request->hasFile('image')) {
            if ($post->image) Storage::disk('public')->delete($post->image);
            $post->image = $request->file('image')->store('posts', 'public');
            $post->type = 'image';
        } elseif ($request->hasFile('video')) {
            if ($post->video) Storage::disk('public')->delete($post->video);
            $post->video = $request->file('video')->store('posts/videos', 'public');
            $post->type = 'video';
        } elseif ($request->hasFile('audio')) {
            if ($post->audio) Storage::disk('public')->delete($post->audio);
            $post->audio = $request->file('audio')->store('posts/audio', 'public');
            $post->type = 'audio';
        }

        if (!$post->image && !$post->video && !$post->audio) {
            $post->type = 'text';
        }

        $post->save();

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Story updated successfully!',
                'post' => [
                    'id' => $post->id,
                    'content' => $post->content,
                    'formatted_content' => $post->formatted_content,
                    'chapter' => $post->chapter,
                    'location' => $post->location,
                    'language' => $post->language,
                    'privacy' => $post->privacy,
                    'tags' => $post->tags,
                    'tagged_users' => $post->fresh()->tagged_users_models->map(fn ($u) => [
                        'id' => $u->id,
                        'name' => $u->name,
                        'username' => $u->username,
                        'avatar' => $u->profile_photo_url,
                    ])->values(),
                    'image' => $post->image ? Storage::url($post->image) : null,
                    'video' => $post->video ? Storage::url($post->video) : null,
                    'audio' => $post->audio ? Storage::url($post->audio) : null,
                ],
            ]);
        }

        return redirect()->back()->with('success', 'Story updated successfully!');
    }

    /**
     * Search taggable users, prioritizing users the current member is Locked-In with.
     */
    public function taggableUsers(Request $request)
    {
        $user = Auth::user();
        $q = trim((string) $request->get('q', ''));

        $connectedIds = $user->lockedInUserIds();

        // Also include users who are co-tagged on the user's posts or who tagged the user
        $coTaggedIds = Post::where(function ($sub) use ($user) {
                $sub->where('user_id', $user->id)
                    ->orWhereJsonContains('tagged_users', (int) $user->id);
            })
            ->whereNotNull('tagged_users')
            ->pluck('tagged_users')
            ->flatten()
            ->map(fn ($id) => (int) $id)
            ->filter(fn ($id) => $id > 0 && $id !== (int) $user->id)
            ->unique()
            ->all();

        $allowedIds = array_values(array_unique(array_merge($connectedIds, $coTaggedIds)));

        // Only include AI suggestions if user has no locked-in/tagged connections or is searching
        $suggestedIds = [];
        if (empty($allowedIds) || $q !== '') {
            try {
                $recService = app(\App\Services\MetaAI\CulturalConnectionRecommendationService::class);
                $recData = $recService->recommendUsers($user, 3);
                foreach (($recData['recommendations'] ?? []) as $rec) {
                    $recId = (int) ($rec['user']['id'] ?? 0);
                    if ($recId > 0 && $recId !== (int) $user->id && !in_array($recId, $allowedIds, true)) {
                        $suggestedIds[] = $recId;
                    }
                }
            } catch (\Throwable $e) {}
        }

        $allTargetIds = array_values(array_unique(array_merge($allowedIds, $suggestedIds)));

        $query = User::whereIn('id', $allTargetIds);

        if ($q !== '') {
            $cleanQ = ltrim($q, '@');
            $query->where(function ($sub) use ($cleanQ) {
                $sub->where('name', 'like', "%{$cleanQ}%")
                    ->orWhere('username', 'like', "%{$cleanQ}%");
            });
        }

        $candidates = $query->limit(20)->get(['id', 'name', 'username', 'avatar']);

        $formatted = $candidates->map(function (User $candidate) use ($connectedIds, $suggestedIds) {
            $isLockedIn = in_array((int) $candidate->id, $connectedIds, true);
            $isSuggested = in_array((int) $candidate->id, $suggestedIds, true);
            return [
                'id' => $candidate->id,
                'name' => $candidate->name,
                'username' => $candidate->username,
                'avatar' => $candidate->profile_photo_url,
                'is_locked_in' => $isLockedIn,
                'is_suggestion' => $isSuggested,
            ];
        })->sortByDesc('is_locked_in')->values();

        return response()->json([
            'success' => true,
            'users' => $formatted,
        ]);
    }

    /**
     * Repost / Quote-Share a post to the user's Kulturegram Timeline.
     * Allowed for the Post Owner and members who have Locked-In to the poster.
     * Non-Locked-In members receive 403 and must Lock-In first.
     */
    public function repost(Request $request, Post $post)
    {
        $user = Auth::user();

        if ($post->privacy !== 'public' && !$post->isOwnedBy($user)) {
            return response()->json([
                'success' => false,
                'error' => 'Only public stories can be shared.',
            ], 403);
        }

        if (!$post->canRepostAndTag($user)) {
            return response()->json([
                'success' => false,
                'requires_lockin' => true,
                'author_id' => $post->user_id,
                'author_name' => $post->user->name,
                'author_username' => $post->user->username,
                'error' => "You must Lock-In to {$post->user->name} (@{$post->user->username}) before reposting their story to your timeline or tagging members on it.",
            ], 403);
        }

        $request->validate([
            'content' => 'nullable|string|max:2000',
            'tagged_users' => 'nullable|array',
            'tagged_users.*' => 'integer|exists:users,id',
        ]);

        $targetPost = $post->shared_post_id ? ($post->sharedPost ?: $post) : $post;
        $quoteText = trim((string) $request->input('content', ''));

        $mentionedUsers = ContentParserService::getMentionedUsers($quoteText);
        $explicitTaggedIds = is_array($request->tagged_users)
            ? array_map('intval', $request->tagged_users)
            : [];
        $allTaggedIds = array_values(array_unique(array_filter(
            array_merge($explicitTaggedIds, $mentionedUsers->pluck('id')->map(fn ($id) => (int) $id)->all()),
            fn ($id) => $id > 0 && $id !== (int) $user->id
        )));

        $extractedTags = ContentParserService::extractHashtags($quoteText);
        $mergedTags = !empty($extractedTags) ? $extractedTags : $targetPost->tags;

        $repost = Post::create([
            'user_id' => $user->id,
            'shared_post_id' => $targetPost->id,
            'content' => $quoteText !== '' ? $quoteText : ('Shared a story by @' . $targetPost->user->username),
            'chapter' => $targetPost->chapter,
            'language' => $user->getPreferredLanguage(),
            'privacy' => 'public',
            'type' => 'text',
            'tags' => $mergedTags,
            'tagged_users' => !empty($allTaggedIds) ? $allTaggedIds : null,
        ]);

        $targetPost->increment('shares_count');
        if ($post->id !== $targetPost->id) {
            $post->increment('shares_count');
        }
        $user->increment('posts_count');

        // Notify original post owner if not self
        if ($targetPost->user_id !== $user->id) {
            $this->safeNotify($targetPost->user, [
                'from_user_id' => $user->id,
                'type' => 'share',
                'title' => 'Story Shared on Timeline',
                'message' => $user->name . ' shared your story on their timeline' . ($quoteText !== '' ? ': "' . Str::limit($quoteText, 50) . '"' : '.'),
                'data' => [
                    'post_id' => $repost->id,
                    'original_post_id' => $targetPost->id,
                    'author_name' => $user->name,
                    'url' => route('timeline.show', $repost->id),
                ],
            ]);
        }

        // Notify any tagged users on the repost
        if (!empty($allTaggedIds)) {
            foreach (User::whereIn('id', $allTaggedIds)->get() as $taggedUser) {
                if ($taggedUser->id !== $user->id) {
                    $this->safeNotify($taggedUser, [
                        'from_user_id' => $user->id,
                        'type' => 'mention',
                        'title' => 'Tagged in a Shared Story',
                        'message' => $user->name . ' tagged you while sharing ' . $targetPost->user->name . '\'s story.',
                        'data' => [
                            'post_id' => $repost->id,
                            'original_post_id' => $targetPost->id,
                            'author_name' => $user->name,
                            'url' => route('timeline.show', $repost->id),
                        ],
                    ]);
                }
            }
        }

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Story shared to your timeline!',
                'repost_id' => $repost->id,
                'shares_count' => $targetPost->fresh()->shares_count,
            ]);
        }

        return redirect()->route('timeline.index')->with('success', 'Story shared to your timeline!');
    }

    /**
     * Add or update tagged users on a post.
     * - Post Owner can set the full list of tagged users (add or remove).
     * - Locked-In members can tag additional people on the post.
     * - Non-Locked-In members cannot tag people on the post (403).
     */
    public function updateTags(Request $request, Post $post)
    {
        $user = Auth::user();

        if (!$post->canRepostAndTag($user)) {
            return response()->json([
                'success' => false,
                'requires_lockin' => true,
                'author_id' => $post->user_id,
                'author_name' => $post->user->name,
                'author_username' => $post->user->username,
                'error' => "You must Lock-In to {$post->user->name} (@{$post->user->username}) before tagging people on their story.",
            ], 403);
        }

        $request->validate([
            'tagged_users' => 'present|array',
            'tagged_users.*' => 'integer|exists:users,id',
        ]);

        $previousIds = is_array($post->tagged_users)
            ? array_map('intval', $post->tagged_users)
            : [];
        $incomingIds = array_values(array_unique(array_filter(
            array_map('intval', (array) $request->input('tagged_users', [])),
            fn ($id) => $id > 0 && $id !== (int) $post->user_id
        )));

        if ($post->isOwnedBy($user) || $user->is_admin) {
            // Owner replaces the full tag list
            $finalIds = $incomingIds;
        } else {
            // Locked-In member merges new tags with existing tags
            $finalIds = array_values(array_unique(array_merge($previousIds, $incomingIds)));
        }

        $post->tagged_users = !empty($finalIds) ? $finalIds : null;
        $post->save();

        $newlyAddedIds = array_diff($finalIds, $previousIds);
        if (!empty($newlyAddedIds)) {
            foreach (User::whereIn('id', $newlyAddedIds)->get() as $taggedUser) {
                if ($taggedUser->id !== $user->id) {
                    $this->safeNotify($taggedUser, [
                        'from_user_id' => $user->id,
                        'type' => 'mention',
                        'title' => 'Tagged in a Story',
                        'message' => $user->name . ' tagged you in ' . ($post->isOwnedBy($user) ? 'their' : $post->user->name . '\'s') . ' story: "' . Str::limit($post->content, 50) . '"',
                        'data' => [
                            'post_id' => $post->id,
                            'author_name' => $user->name,
                            'url' => route('timeline.show', $post->id),
                        ],
                    ]);
                }
            }

            // If a Locked-In member tagged someone on the owner's post, also notify the post owner
            if (!$post->isOwnedBy($user) && $post->user_id !== $user->id) {
                $this->safeNotify($post->user, [
                    'from_user_id' => $user->id,
                    'type' => 'mention',
                    'title' => 'Members Tagged on Your Story',
                    'message' => $user->name . ' tagged ' . count($newlyAddedIds) . ' member(s) on your story.',
                    'data' => [
                        'post_id' => $post->id,
                        'author_name' => $user->name,
                        'url' => route('timeline.show', $post->id),
                    ],
                ]);
            }
        }

        $freshPost = $post->fresh();
        return response()->json([
            'success' => true,
            'message' => 'Tagged members updated!',
            'tagged_users' => $freshPost->tagged_users_models->map(fn ($u) => [
                'id' => $u->id,
                'name' => $u->name,
                'username' => $u->username,
                'avatar' => $u->profile_photo_url,
            ])->values(),
        ]);
    }

    /**
     * Allow a tagged user to untag themselves from a post (or allow the post owner to remove a specific tag).
     */
    public function removeSelfTag(Request $request, Post $post)
    {
        $user = Auth::user();
        $targetUserId = $request->filled('user_id') && ($post->isOwnedBy($user) || $user->is_admin)
            ? (int) $request->input('user_id')
            : (int) $user->id;

        $currentIds = is_array($post->tagged_users)
            ? array_map('intval', $post->tagged_users)
            : [];

        $updatedIds = array_values(array_filter($currentIds, fn ($id) => $id !== $targetUserId));
        $post->tagged_users = !empty($updatedIds) ? $updatedIds : null;
        $post->save();

        $freshPost = $post->fresh();
        return response()->json([
            'success' => true,
            'message' => 'Tag removed.',
            'tagged_users' => $freshPost->tagged_users_models->map(fn ($u) => [
                'id' => $u->id,
                'name' => $u->name,
                'username' => $u->username,
                'avatar' => $u->profile_photo_url,
            ])->values(),
        ]);
    }

    /**
     * Delete a timeline post.
     */
    public function destroy(Request $request, Post $post)
    {
        if ($post->user_id != Auth::id() && !Auth::user()->is_admin) {
            abort(403, 'You are not authorized to delete this story.');
        }

        // Delete media files from storage
        foreach (['image', 'video', 'audio'] as $media) {
            if ($post->$media) {
                Storage::disk('public')->delete($post->$media);
            }
        }

        // Decrement user posts count
        if ($post->user && $post->user->posts_count > 0) {
            $post->user->decrement('posts_count');
        }

        // If this post was a repost, decrement shares_count on original post
        if ($post->shared_post_id) {
            Post::where('id', $post->shared_post_id)->where('shares_count', '>', 0)->decrement('shares_count');
        }

        // Delete associated resonances, interactions, and comments
        $post->resonances()->delete();
        $post->interactions()->delete();
        $post->comments()->delete();

        // Nullify any cultural class references
        \App\Models\CulturalClass::where('timeline_post_id', $post->id)->update(['timeline_post_id' => null]);

        $postId = $post->id;
        $post->delete();

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Story deleted successfully.',
                'post_id' => $postId,
            ]);
        }

        return redirect()->route('timeline.index')->with('success', 'Story deleted successfully.');
    }

    public function tap(Post $post)
    {
        $user = Auth::user();

        $interaction = $user->interactions()
            ->where('interactable_type', Post::class)
            ->where('interactable_id', $post->id)
            ->where('type', 'tap')
            ->first();

        if ($interaction) {
            $interaction->delete();
            $post->decrement('taps_count');
            $tapped = false;
        } else {
            $user->interactions()->create([
                'interactable_type' => Post::class,
                'interactable_id' => $post->id,
                'type' => 'tap',
            ]);
            $post->increment('taps_count');
            $tapped = true;

            // Create notification
            if ($post->user_id !== $user->id) {
                $this->safeNotify($post->user, [
                    'from_user_id' => $user->id,
                    'type' => 'tap',
                    'title' => 'New TAP',
                    'message' => $user->name . ' tapped your story',
                    'data' => ['post_id' => $post->id],
                ]);
            }
        }

        return response()->json([
            'tapped' => $tapped,
            'count' => $post->fresh()->taps_count,
        ]);
    }

    public function resonance(Request $request, Post $post)
    {
        $request->validate([
            'content' => 'required|string|max:1000',
            'parent_id' => 'nullable|exists:resonances,id',
        ]);

        $user = Auth::user();
        $content = (string) $request->input('content');

        // Enforce tagging rule: Non-Locked-In members cannot tag people (@username) on a post unless they Lock-In to the poster
        $extractedMentions = ContentParserService::extractMentionUsernames($content);
        if (!empty($extractedMentions) && !$post->canRepostAndTag($user)) {
            return response()->json([
                'success' => false,
                'requires_lockin' => true,
                'author_id' => $post->user_id,
                'author_name' => $post->user->name,
                'author_username' => $post->user->username,
                'error' => "You must Lock-In to {$post->user->name} (@{$post->user->username}) before tagging members (@username) on their story.",
            ], 403);
        }

        $parentId = $request->input('parent_id');
        $parentResonance = null;
        if ($parentId) {
            $parentResonance = Resonance::where('post_id', $post->id)->find($parentId);
            if (!$parentResonance) {
                return response()->json(['error' => 'Parent resonance not found for this post.'], 404);
            }
        }

        $resonance = $post->resonances()->create([
            'user_id' => $user->id,
            'parent_id' => $parentResonance ? $parentResonance->id : null,
            'content' => $content,
        ]);

        $post->increment('resonance_count');

        // Notify parent resonance author if replying to someone else
        if ($parentResonance && $parentResonance->user_id !== $user->id) {
            $this->safeNotify($parentResonance->user, [
                'from_user_id' => $user->id,
                'type' => 'resonance_reply',
                'title' => 'New Reply to Your Resonance',
                'message' => $user->name . ' replied to your comment: "' . Str::limit($resonance->content, 60) . '"',
                'data' => [
                    'post_id' => $post->id,
                    'resonance_id' => $resonance->id,
                    'parent_id' => $parentResonance->id,
                    'author_name' => $user->name,
                ],
            ]);
        }

        // Notify post author if not self and not already notified as parent author
        if ($post->user_id !== $user->id && (!$parentResonance || $parentResonance->user_id !== $post->user_id)) {
            $this->safeNotify($post->user, [
                'from_user_id' => $user->id,
                'type' => 'resonance',
                'title' => $parentResonance ? 'New Reply on Your Story' : 'New Resonance',
                'message' => $user->name . ($parentResonance ? ' replied to a comment on your story: "' : ' resonated with your story: "') . Str::limit($resonance->content, 60) . '"',
                'data' => [
                    'post_id' => $post->id,
                    'resonance_id' => $resonance->id,
                    'parent_id' => $parentResonance ? $parentResonance->id : null,
                    'author_name' => $user->name,
                ],
            ]);
        }

        // Notify any users mentioned in this resonance (allowed for Owner and Locked-In members)
        $mentionedUsers = ContentParserService::getMentionedUsers($resonance->content);
        foreach ($mentionedUsers as $mentionedUser) {
            if ($mentionedUser->id !== $user->id && $mentionedUser->id !== $post->user_id && (!$parentResonance || $mentionedUser->id !== $parentResonance->user_id)) {
                $this->safeNotify($mentionedUser, [
                    'from_user_id' => $user->id,
                    'type' => 'mention',
                    'title' => 'Mentioned in a Resonance',
                    'message' => $user->name . ' mentioned you in a resonance on a story: "' . Str::limit($resonance->content, 60) . '"',
                    'data' => [
                        'post_id' => $post->id,
                        'resonance_id' => $resonance->id,
                        'author_name' => $user->name,
                    ],
                ]);
            }
        }

        return response()->json([
            'success' => true,
            'resonance' => [
                'id' => $resonance->id,
                'parent_id' => $resonance->parent_id,
                'content' => $resonance->content,
                'formatted_content' => $resonance->formatted_content,
                'taps_count' => 0,
                'is_tapped' => false,
                'is_post_author' => $resonance->user_id === $post->user_id,
                'user' => [
                    'id' => $resonance->user->id,
                    'name' => $resonance->user->name,
                    'username' => $resonance->user->username,
                    'profile_photo_url' => $resonance->user->profile_photo_url,
                ],
                'time' => $resonance->created_at->diffForHumans(),
            ],
            'count' => $post->resonances()->count(),
        ]);
    }

    public function tapResonance(Resonance $resonance)
    {
        $user = Auth::user();

        $interaction = $user->interactions()
            ->where('interactable_type', Resonance::class)
            ->where('interactable_id', $resonance->id)
            ->where('type', 'tap')
            ->first();

        if ($interaction) {
            $interaction->delete();
            $resonance->decrement('taps_count');
            $tapped = false;
        } else {
            $user->interactions()->create([
                'interactable_type' => Resonance::class,
                'interactable_id' => $resonance->id,
                'type' => 'tap',
            ]);
            $resonance->increment('taps_count');
            $tapped = true;

            // Notify comment author if not self
            if ($resonance->user_id !== $user->id) {
                $isPostAuthor = $resonance->post && $resonance->post->user_id === $user->id;
                $this->safeNotify($resonance->user, [
                    'from_user_id' => $user->id,
                    'type' => 'resonance_tap',
                    'title' => $isPostAuthor ? 'Story Author Liked Your Comment' : 'New Reaction to Your Comment',
                    'message' => $user->name . ($isPostAuthor ? ' (Story Author) liked your resonance: "' : ' reacted to your resonance: "') . Str::limit($resonance->content, 60) . '"',
                    'data' => [
                        'post_id' => $resonance->post_id,
                        'resonance_id' => $resonance->id,
                        'is_author' => $isPostAuthor,
                    ],
                ]);
            }
        }

        $fresh = $resonance->fresh();
        return response()->json([
            'success' => true,
            'tapped' => $tapped,
            'count' => (int) $fresh->taps_count,
            'is_author_liked' => $fresh->isAuthorLiked(),
        ]);
    }

    public function checkin(Post $post)
    {
        $user = Auth::user();

        $interaction = $user->interactions()
            ->where('interactable_type', Post::class)
            ->where('interactable_id', $post->id)
            ->where('type', 'checkin')
            ->first();

        if ($interaction) {
            $interaction->delete();
            $post->decrement('check_ins_count');
            $checkedIn = false;
        } else {
            $user->interactions()->create([
                'interactable_type' => Post::class,
                'interactable_id' => $post->id,
                'type' => 'checkin',
            ]);
            $post->increment('check_ins_count');
            $checkedIn = true;

            // Increment relationship level with the post creator
            if ($post->user_id !== $user->id) {
                $user->incrementRelationshipWith($post->user);
            }
        }

        return response()->json([
            'checkedIn' => $checkedIn,
            'count' => $post->fresh()->check_ins_count,
        ]);
    }

    /**
     * AI-assisted translation of a post to the viewer's target language.
     */
    public function translatePost(Request $request, Post $post, MultilingualCulturalAccessService $translationService)
    {
        $targetLanguage = $request->input('target_language');
        if (!$targetLanguage && Auth::check()) {
            $targetLanguage = Auth::user()->getPreferredLanguage();
        }
        $targetLanguage = $targetLanguage ?: 'English';

        $culturalContext = "Story chapter: {$post->chapter}. Location: {$post->location}. Author: {$post->user->name}.";
        $result = $translationService->translatePost($post->content, $targetLanguage, $culturalContext);

        if (!empty($result['translated_text'])) {
            $result['formatted_translated_content'] = ContentParserService::format($result['translated_text']);
        }

        return response()->json($result);
    }

    /**
     * AI oral voice story formatting: transforms spoken words into a clean, ready-to-post story or culture record.
     */
    public function aiVoiceFormat(Request $request, OralTraditionVoiceService $voiceService)
    {
        $request->validate([
            'raw_transcript' => 'nullable|string',
            'audio_file' => 'nullable|file|max:25600',
            'type' => 'nullable|string|in:timeline_post,culture_record',
        ]);

        $rawTranscript = (string) $request->input('raw_transcript', '');

        // If audio file is provided, transcribe it
        if ($request->hasFile('audio_file')) {
            $transcription = $voiceService->transcribeAudio($request->file('audio_file'));
            if (!empty($transcription['transcript'])) {
                $rawTranscript = $transcription['transcript'];
            }
        }

        if (empty(trim($rawTranscript))) {
            return response()->json([
                'success' => false,
                'error' => 'Please speak into the microphone or provide an audio recording.',
            ], 422);
        }

        $type = $request->input('type', 'timeline_post');
        $context = $request->only(['chapter', 'location', 'name', 'cultural_group', 'region', 'language', 'category']);

        if ($type === 'culture_record') {
            $result = $voiceService->formatOralRecord($rawTranscript, $context);
        } else {
            $result = $voiceService->formatTimelineStory($rawTranscript, $context);
        }

        $result['raw_transcript'] = $rawTranscript;
        return response()->json($result);
    }
    /**
     * Safely dispatch a user notification without blocking main actions on failure.
     */
    protected function safeNotify(?User $recipient, array $payload): void
    {
        if (!$recipient || empty($payload)) {
            return;
        }

        try {
            $recipient->notifications()->create($payload);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('Notification dispatch skipped: ' . $e->getMessage(), [
                'recipient_id' => $recipient->id,
                'type' => $payload['type'] ?? null,
            ]);
        }
    }

}
