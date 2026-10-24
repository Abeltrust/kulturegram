<?php

namespace App\Http\Controllers;

use App\Models\Community;
use App\Models\CommunityPost;
use App\Models\Comment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Post;
use App\Models\User;

class CommunityController extends Controller
{

    public function index(Request $request)
    {
        $filter = $request->get('filter', 'all');
        $search = $request->get('search');

        $query = Community::with(['creator', 'members'])
            ->public()
            ->latest();

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        switch ($filter) {
            case 'joined':
                if (Auth::check()) {
                    $query->whereHas('members', function ($q) {
                        $q->where('user_id', Auth::id());
                    });
                }
                break;
            case 'recommended':
                $query->orderBy('members_count', 'desc');
                break;
        }

        $communities = $query->paginate(12);

        // ---- Trending logic: Communities with many members that friends belong to ----
        $trending = collect();
        if (Auth::check()) {
            // Get IDs of communities the user is in
            $myCommunityIds = Auth::user()->communities()->pluck('communities.id');

            // Get friends (users who are in the same communities)
            $friendIds = \DB::table('community_members')
                ->whereIn('community_id', $myCommunityIds)
                ->where('user_id', '!=', Auth::id())
                ->pluck('user_id')
                ->unique();

            // Find communities these friends are in that the user is NOT in
            $trending = Community::whereHas('members', function ($q) use ($friendIds) {
                $q->whereIn('user_id', $friendIds);
            })
                ->whereDoesntHave('members', function ($q) {
                    $q->where('user_id', Auth::id());
                })
                ->withCount('members')
                ->orderBy('members_count', 'desc')
                ->take(6)
                ->get();

            // Fallback: If no friend-based trends, just show most popular public ones
            if ($trending->isEmpty()) {
                $trending = Community::whereDoesntHave('members', function ($q) {
                    $q->where('user_id', Auth::id());
                })
                    ->withCount('members')
                    ->orderBy('members_count', 'desc')
                    ->take(6)
                    ->get();
            }
        }


        $communities = $query->paginate(12);

        return view('communities.index', compact('communities', 'filter', 'search', 'trending'));
    }

    public function show(Community $community)
    {
        // Load related creator, members, and community posts
        $community->load([
            'creator',
            'members',
            'communityPosts.user' => function ($query) {
                $query->latest()->limit(10);
            }
        ]);

        // Fetch posts directly with pagination
        $posts = $community->communityPosts()
            ->with(['user', 'taps', 'comments.user'])
            ->latest()
            ->paginate(10);


        return view('communities.show', compact('community', 'posts'));
    }



    public function create()
    {
        return view('communities.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:communities',
            'description' => 'required|string',
            'category' => 'required|string',
            'rules' => 'nullable|string',
            'is_private' => 'boolean',
            'image' => 'nullable|image',
        ]);

        $community = new Community($request->all());
        $community->created_by = Auth::id();

        if ($request->hasFile('image')) {
            $community->image = $request->file('image')->store('communities', 'public');
        }

        $community->save();

        // Add creator as admin member
        $community->members()->attach(Auth::id(), ['role' => 'admin']);
        $community->increment('members_count');

        return redirect()->route('communities.show', $community)
            ->with('success', 'Community created successfully!');
    }

    public function join(Community $community)
    {
        $user = Auth::user();

        if ($community->members()->where('user_id', $user->id)->exists()) {
            return response()->json(['error' => 'Already a member'], 400);
        }

        $community->members()->attach($user->id);
        $community->increment('members_count');

        return redirect()->back()->with('success', "Joined {$community->name}!");
    }

    public function leave(Community $community)
    {
        $user = Auth::user();

        $community->members()->detach($user->id);
        $community->decrement('members_count');

        return redirect()->back()->with('success', "Left {$community->name}.");
    }

    public function edit(Community $community)
    {
        if (Auth::id() !== $community->created_by && !$community->members()->where('user_id', Auth::id())->wherePivot('role', 'admin')->exists()) {
            abort(403, 'Unauthorized. Only community admins can edit this community.');
        }

        return view('communities.edit', compact('community'));
    }

    public function update(Request $request, Community $community)
    {
        if (Auth::id() !== $community->created_by && !$community->members()->where('user_id', Auth::id())->wherePivot('role', 'admin')->exists()) {
            abort(403, 'Unauthorized. Only community admins can update this community.');
        }

        $request->validate([
            'name' => 'required|string|max:255|unique:communities,name,' . $community->id,
            'description' => 'required|string',
            'category' => 'required|string',
            'rules' => 'nullable|string',
            'is_private' => 'boolean',
            'image' => 'nullable|image|max:10240',
        ]);

        $community->fill($request->except(['image', 'is_private']));
        $community->is_private = $request->boolean('is_private');

        if ($request->hasFile('image')) {
            if ($community->image && \Illuminate\Support\Facades\Storage::disk('public')->exists($community->image)) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($community->image);
            }
            $community->image = $request->file('image')->store('communities', 'public');
        }

        $community->save();

        return redirect()->route('communities.show', $community)
            ->with('success', 'Community updated successfully!');
    }

    public function destroy(Community $community)
    {
        if (Auth::id() !== $community->created_by) {
            abort(403, 'Unauthorized. Only the community creator can delete this community.');
        }

        if ($community->image && \Illuminate\Support\Facades\Storage::disk('public')->exists($community->image)) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($community->image);
        }

        $community->delete();

        return redirect()->route('communities.index')
            ->with('success', 'Community removed successfully.');
    }
}
