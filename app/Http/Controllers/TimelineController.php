<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\User;
use App\Services\ContentParserService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class TimelineController extends Controller
{
    public function index(Request $request)
    {
        $filter = $request->get('filter', 'all');

        $query = Post::with(['user', 'taps', 'resonances.user', 'lockIns', 'checkIns'])
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
            'privacy' => 'in:public,private,vault',
            'type' => 'nullable|in:text,image,video,audio',
            'tags' => 'nullable|array',
            'image' => 'nullable|file|mimes:jpeg,png,jpg,gif,webp|max:10240',
            'video' => 'nullable|file|mimes:mp4,mov,avi,webm|max:51200',
            'audio' => 'nullable|file|mimes:mp3,wav,ogg,m4a|max:20480',
        ]);

        $post = new Post($request->only(['content', 'chapter', 'location', 'privacy']));
        $post->user_id = Auth::id();
        $post->type = $request->input('type', 'text');

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
        ]);

        $resonance = $post->resonances()->create([
            'user_id' => Auth::id(),
            'content' => $request->input('content'),
        ]);

        $post->increment('resonance_count');

        // Notify post author if not self
        if ($post->user_id !== Auth::id()) {
            $post->user->notifications()->create([
                'from_user_id' => Auth::id(),
                'type' => 'resonance',
                'title' => 'New Resonance',
                'message' => Auth::user()->name . ' resonated with your story: "' . Str::limit($resonance->content, 60) . '"',
                'data' => [
                    'post_id' => $post->id,
                    'resonance_id' => $resonance->id,
                    'author_name' => Auth::user()->name,
                ],
            ]);
        }

        // Notify any users mentioned in this resonance
        $mentionedUsers = ContentParserService::getMentionedUsers($resonance->content);
        foreach ($mentionedUsers as $mentionedUser) {
            if ($mentionedUser->id !== Auth::id() && $mentionedUser->id !== $post->user_id) {
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
                'content' => $resonance->content,
                'formatted_content' => $resonance->formatted_content,
                'user' => [
                    'id' => $resonance->user->id,
                    'name' => $resonance->user->name,
                    'username' => $resonance->user->username,
                ],
                'time' => $resonance->created_at->diffForHumans(),
            ],
            'count' => $post->resonances()->count(),
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
}