<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TimelineController extends Controller
{
    public function index(Request $request)
    {
        $filter = $request->get('filter', 'all');

        $query = Post::with(['user', 'taps', 'resonances.user', 'lockIns', 'checkIns'])
            ->public()
            ->latest();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('content', 'like', "%{$search}%")
                  ->orWhere('location', 'like', "%{$search}%")
                  ->orWhere('chapter', 'like', "%{$search}%")
                  ->orWhereHas('user', function($uq) use ($search) {
                      $uq->where('name', 'like', "%{$search}%")
                         ->orWhere('username', 'like', "%{$search}%");
                  });
            });
        }

        if (Auth::check()) {
            if (!$request->has('filter')) {
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
            'type' => 'in:text,image,video,audio',
            'tags' => 'nullable|array',
            'image' => 'nullable|image', // 10MB
        ]);

        $post = new Post($request->all());
        $post->user_id = Auth::id();

        if ($request->hasFile('image')) {
            $post->image = $request->file('image')->store('posts', 'public');
        }

        $post->save();

        // Update user's posts count
        Auth::user()->increment('posts_count');

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

        return response()->json([
            'success' => true,
            'resonance' => [
                'id' => $resonance->id,
                'content' => $resonance->content,
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