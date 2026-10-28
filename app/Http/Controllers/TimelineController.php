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
            $query->where(function($q) use ($tag) {
                $q->whereJsonContains('tags', $tag)
                  ->orWhere('content', 'like', "%#{$tag}%");
            });
        }

        // Search Filter
        if ($request->filled('search')) {
            $search = $request->search;
            $cleanSearch = mb_strtolower(ltrim($search, '#'));
            $query->where(function($q) use ($search, $cleanSearch) {
                $q->where('content', 'like', "%{$search}%")
                  ->orWhere('location', 'like', "%{$search}%")
                  ->orWhere('chapter', 'like', "%{$search}%")
                  ->orWhereJsonContains('tags', $cleanSearch)
                  ->orWhereHas('user', function($uq) use ($search) {
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
                    // Posts from users the current user has locked-in to + their own posts
                    $lockedInUsers = Auth::user()->interactions()
                        ->where('type', 'lockin')
                        ->where('interactable_type', User::class)
                        ->pluck('interactable_id')
                        ->push(Auth::id());
                    $query->whereIn('user_id', $lockedInUsers);
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

        // Dispatch notifications for mentioned users
        $mentionedUsers = ContentParserService::getMentionedUsers($post->content);
        foreach ($mentionedUsers as $mentionedUser) {
            if ($mentionedUser->id !== Auth::id()) {
                $mentionedUser->notifications()->create([
                    'from_user_id' => Auth::id(),
                    'type' => 'mention',
                    'title' => 'Mentioned in a Story',
                    'message' => Auth::user()->name . ' mentioned you in a story: "' . Str::limit($post->content, 60) . '"',
                    'data' => [
                        'post_id' => $post->id,
                        'author_name' => Auth::user()->name,
                        'url' => route('timeline.index') . '#post-' . $post->id,
                    ],
                ]);
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
        if ($post->user_id !== Auth::id() && !Auth::user()->is_admin) {
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
        if ($post->user_id !== Auth::id() && !Auth::user()->is_admin) {
            abort(403, 'You are not authorized to edit this story.');
        }

        $request->validate([
            'content' => 'required|string|max:2000',
            'chapter' => 'nullable|string',
            'location' => 'nullable|string',
            'language' => 'nullable|string|max:60',
            'privacy' => 'nullable|in:public,private,vault',
            'tags' => 'nullable|array',
            'remove_image' => 'nullable|boolean',
            'remove_video' => 'nullable|boolean',
            'remove_audio' => 'nullable|boolean',
            'image' => 'nullable|file|mimes:jpeg,png,jpg,gif,webp|max:10240',
            'video' => 'nullable|file|mimes:mp4,mov,avi,webm|max:51200',
            'audio' => 'nullable|file|mimes:mp3,wav,ogg,m4a|max:20480',
        ]);

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
                    'image' => $post->image ? Storage::url($post->image) : null,
                    'video' => $post->video ? Storage::url($post->video) : null,
                    'audio' => $post->audio ? Storage::url($post->audio) : null,
                ],
            ]);
        }

        return redirect()->back()->with('success', 'Story updated successfully!');
    }

    /**
     * Delete a timeline post.
     */
    public function destroy(Request $request, Post $post)
    {
        if ($post->user_id !== Auth::id() && !Auth::user()->is_admin) {
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

        // Delete associated resonances and interactions
        $post->resonances()->delete();
        $post->interactions()->delete();
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
                $post->user->notifications()->create([
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

        $parentId = $request->input('parent_id');
        $parentResonance = null;
        if ($parentId) {
            $parentResonance = Resonance::where('post_id', $post->id)->find($parentId);
            if (!$parentResonance) {
                return response()->json(['error' => 'Parent resonance not found for this post.'], 404);
            }
        }

        $resonance = $post->resonances()->create([
            'user_id' => Auth::id(),
            'parent_id' => $parentResonance ? $parentResonance->id : null,
            'content' => $request->input('content'),
        ]);

        $post->increment('resonance_count');

        // Notify parent resonance author if replying to someone else
        if ($parentResonance && $parentResonance->user_id !== Auth::id()) {
            $parentResonance->user->notifications()->create([
                'from_user_id' => Auth::id(),
                'type' => 'resonance_reply',
                'title' => 'New Reply to Your Resonance',
                'message' => Auth::user()->name . ' replied to your comment: "' . Str::limit($resonance->content, 60) . '"',
                'data' => [
                    'post_id' => $post->id,
                    'resonance_id' => $resonance->id,
                    'parent_id' => $parentResonance->id,
                    'author_name' => Auth::user()->name,
                ],
            ]);
        }

        // Notify post author if not self and not already notified as parent author
        if ($post->user_id !== Auth::id() && (!$parentResonance || $parentResonance->user_id !== $post->user_id)) {
            $post->user->notifications()->create([
                'from_user_id' => Auth::id(),
                'type' => 'resonance',
                'title' => $parentResonance ? 'New Reply on Your Story' : 'New Resonance',
                'message' => Auth::user()->name . ($parentResonance ? ' replied to a comment on your story: "' : ' resonated with your story: "') . Str::limit($resonance->content, 60) . '"',
                'data' => [
                    'post_id' => $post->id,
                    'resonance_id' => $resonance->id,
                    'parent_id' => $parentResonance ? $parentResonance->id : null,
                    'author_name' => Auth::user()->name,
                ],
            ]);
        }

        // Notify any users mentioned in this resonance
        $mentionedUsers = ContentParserService::getMentionedUsers($resonance->content);
        foreach ($mentionedUsers as $mentionedUser) {
            if ($mentionedUser->id !== Auth::id() && $mentionedUser->id !== $post->user_id && (!$parentResonance || $mentionedUser->id !== $parentResonance->user_id)) {
                $mentionedUser->notifications()->create([
                    'from_user_id' => Auth::id(),
                    'type' => 'mention',
                    'title' => 'Mentioned in a Resonance',
                    'message' => Auth::user()->name . ' mentioned you in a resonance on a story: "' . Str::limit($resonance->content, 60) . '"',
                    'data' => [
                        'post_id' => $post->id,
                        'resonance_id' => $resonance->id,
                        'author_name' => Auth::user()->name,
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
                $resonance->user->notifications()->create([
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
            'count' => (int)$fresh->taps_count,
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
}