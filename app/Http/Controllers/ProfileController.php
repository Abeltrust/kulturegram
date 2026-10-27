<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Models\LifeChapter;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    /**
     * Upload or update user avatar.
     */
    public function photo(Request $request)
    {
        $request->validate([
            'profile_photo' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
        ]);

        $user = Auth::user();

        // Delete old avatar if exists in local storage
        if ($user->avatar && Storage::disk('public')->exists('avatars/' . $user->avatar)) {
            Storage::disk('public')->delete('avatars/' . $user->avatar);
        }

        $avatarName = time() . '_' . uniqid() . '.' . $request->file('profile_photo')->extension();
        $request->file('profile_photo')->storeAs('avatars', $avatarName, 'public');

        $user->update([
            'avatar' => $avatarName,
        ]);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'avatar_url' => $user->fresh()->profile_photo_url,
                'message' => 'Profile picture updated successfully.'
            ]);
        }

        return back()->with('success', 'Profile picture updated successfully.');
    }

    /**
     * Upload or update custom cover photo.
     */
    public function cover(Request $request)
    {
        $request->validate([
            'cover_photo' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:8192',
        ]);

        $user = Auth::user();

        if ($user->cover_photo && Storage::disk('public')->exists('covers/' . $user->cover_photo)) {
            Storage::disk('public')->delete('covers/' . $user->cover_photo);
        }

        $coverName = time() . '_' . uniqid() . '.' . $request->file('cover_photo')->extension();
        $request->file('cover_photo')->storeAs('covers', $coverName, 'public');

        $user->update([
            'cover_photo' => $coverName,
        ]);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'cover_url' => $user->fresh()->cover_photo_url,
                'message' => 'Cover banner updated successfully.'
            ]);
        }

        return back()->with('success', 'Cover banner updated successfully.');
    }

    /**
     * Authenticated User Profile & Dashboard
     */
    public function show()
    {
        $user = Auth::user();

        $posts = $user->posts()
            ->with(['taps', 'resonances.user', 'resonances.replies.user', 'resonances.interactions', 'resonances.replies.interactions'])
            ->latest()
            ->paginate(10);

        $lifeChapters = $user->lifeChapters()
            ->orderBy('order', 'asc')
            ->orderBy('created_at', 'asc')
            ->get();

        $cultures = $user->cultures()
            ->latest()
            ->get();

        $communities = $user->communities()
            ->withCount('members')
            ->latest()
            ->get();

        $organizedEvents = $user->events()
            ->latest('event_date')
            ->get();

        $attendingEvents = $user->attendingEvents()
            ->latest('event_date')
            ->get();

        $vaultItems = $user->vaultItems()
            ->latest()
            ->take(8)
            ->get();

        $vaultCount = $user->vaultItems()->count();

        $totalTaps = (int) $user->posts()->sum('taps_count') + (int) $user->taps_received;
        $totalResonance = (int) $user->posts()->sum('resonance_count');
        $contributionsCount = $user->cultures()->count();
        $storiesCount = $user->posts()->count();

        $stats = [
            'stories' => $storiesCount,
            'locked_in' => $user->locked_in_count,
            'vibes' => $totalTaps,
            'resonance' => $totalResonance,
            'contributions' => $contributionsCount,
            'vault' => $vaultCount,
            'communities' => $communities->count(),
            'events' => $organizedEvents->count() + $attendingEvents->count(),
            'preservation_score' => min(100, round(($contributionsCount * 10) + ($storiesCount * 2) + ($vaultCount * 3) + ($totalResonance * 0.1))),
        ];

        return view('profile.index', compact(
            'user',
            'posts',
            'lifeChapters',
            'cultures',
            'communities',
            'organizedEvents',
            'attendingEvents',
            'vaultItems',
            'vaultCount',
            'stats'
        ));
    }

    /**
     * Public User Profile
     */
    public function showUser($user)
    {
        $targetUser = User::where('id', $user)
            ->orWhere('username', $user)
            ->firstOrFail();

        // If authenticated user is looking at their own profile, redirect to dashboard
        if (Auth::check() && Auth::id() === $targetUser->id) {
            return redirect()->route('profile.show');
        }

        $isLockedIn = Auth::check() ? Auth::user()->hasLockedIn($targetUser) : false;
        $hasVibed = Auth::check() ? Auth::user()->hasTapped($targetUser) : false;

        $posts = $targetUser->posts()
            ->with(['taps', 'resonances.user', 'resonances.replies.user', 'resonances.interactions', 'resonances.replies.interactions'])
            ->latest()
            ->paginate(10);

        $lifeChapters = $targetUser->lifeChapters()
            ->orderBy('order', 'asc')
            ->orderBy('created_at', 'asc')
            ->get();

        $cultures = $targetUser->cultures()
            ->whereIn('status', ['approved', 'featured'])
            ->latest()
            ->get();

        $communities = $targetUser->communities()
            ->withCount('members')
            ->latest()
            ->get();

        $organizedEvents = $targetUser->events()
            ->latest('event_date')
            ->get();

        $attendingEvents = $targetUser->attendingEvents()
            ->latest('event_date')
            ->get();

        // Public vault items
        $vaultItems = $targetUser->vaultItems()
            ->where('access_level', 'public')
            ->latest()
            ->take(8)
            ->get();

        $vaultCount = $targetUser->vaultItems()
            ->where('access_level', 'public')
            ->count();

        $totalTaps = (int) $targetUser->posts()->sum('taps_count') + (int) $targetUser->taps_received;
        $totalResonance = (int) $targetUser->posts()->sum('resonance_count');
        $contributionsCount = $cultures->count();
        $storiesCount = $targetUser->posts()->count();

        $stats = [
            'stories' => $storiesCount,
            'locked_in' => $targetUser->locked_in_count,
            'vibes' => $totalTaps,
            'resonance' => $totalResonance,
            'contributions' => $contributionsCount,
            'vault' => $vaultCount,
            'communities' => $communities->count(),
            'events' => $organizedEvents->count() + $attendingEvents->count(),
            'preservation_score' => min(100, round(($contributionsCount * 10) + ($storiesCount * 2) + ($vaultCount * 3) + ($totalResonance * 0.1))),
        ];

        $user = $targetUser;

        return view('profile.index', compact(
            'user',
            'posts',
            'lifeChapters',
            'cultures',
            'communities',
            'organizedEvents',
            'attendingEvents',
            'vaultItems',
            'vaultCount',
            'stats',
            'isLockedIn',
            'hasVibed'
        ));
    }

    /**
     * Standalone User Activity & Impact History (Auth User)
     */
    public function activity(Request $request)
    {
        $user = Auth::user();
        return $this->buildActivityView($user, true);
    }

    /**
     * Standalone User Activity & Impact History (Public Profile)
     */
    public function userActivity($user)
    {
        $targetUser = User::where('id', $user)
            ->orWhere('username', $user)
            ->firstOrFail();

        $isOwner = Auth::check() && Auth::id() === $targetUser->id;

        return $this->buildActivityView($targetUser, $isOwner);
    }

    /**
     * Helper to assemble unified activity stream and impact stats
     */
    private function buildActivityView(User $user, bool $isOwner)
    {
        $activities = collect();

        // 1. Stories / Posts
        foreach ($user->posts()->latest()->take(30)->get() as $post) {
            $activities->push([
                'id' => 'post_' . $post->id,
                'type' => 'stories',
                'type_label' => 'Story Preserved',
                'icon' => 'feather',
                'color' => 'amber',
                'title' => 'Preserved Living Memory',
                'description' => \Illuminate\Support\Str::limit($post->content, 140),
                'url' => route('timeline.index'),
                'created_at' => $post->created_at,
                'badge' => $post->chapter ?: null,
                'metrics' => ($post->taps_count ?? 0) . ' Taps • ' . ($post->resonance_count ?? 0) . ' Resonance'
            ]);
        }

        // 2. Cultural Traditions
        $cultureQuery = $user->cultures()->latest();
        if (!$isOwner) {
            $cultureQuery->whereIn('status', ['approved', 'featured']);
        }
        foreach ($cultureQuery->take(30)->get() as $culture) {
            $activities->push([
                'id' => 'culture_' . $culture->id,
                'type' => 'traditions',
                'type_label' => 'Cultural Tradition',
                'icon' => 'archive',
                'color' => 'orange',
                'title' => 'Submitted Tradition: ' . $culture->name,
                'description' => \Illuminate\Support\Str::limit($culture->description, 140),
                'url' => route('cultural-hub.show', $culture),
                'created_at' => $culture->created_at,
                'badge' => ucfirst($culture->category),
                'status' => $culture->status,
                'metrics' => $culture->region
            ]);
        }

        // 3. Life Chapters
        foreach ($user->lifeChapters()->latest()->take(20)->get() as $chapter) {
            $activities->push([
                'id' => 'chapter_' . $chapter->id,
                'type' => 'chapters',
                'type_label' => 'Life Chapter',
                'icon' => 'bookmark',
                'color' => 'amber',
                'title' => 'Recorded Milestone: ' . $chapter->title,
                'description' => \Illuminate\Support\Str::limit($chapter->description, 140),
                'url' => route('profile.show'),
                'created_at' => $chapter->created_at,
                'badge' => $chapter->period ?: 'Era',
                'metrics' => $chapter->location
            ]);
        }

        // 4. Communities
        foreach ($user->communities()->withCount('members')->latest()->take(20)->get() as $community) {
            $activities->push([
                'id' => 'community_' . $community->id,
                'type' => 'communities',
                'type_label' => 'Tribe / Circle',
                'icon' => 'users',
                'color' => 'blue',
                'title' => ($community->created_by === $user->id ? 'Founded Tribe: ' : 'Joined Community: ') . $community->name,
                'description' => \Illuminate\Support\Str::limit($community->description, 140),
                'url' => route('communities.show', $community),
                'created_at' => $community->created_at,
                'badge' => $community->category,
                'metrics' => ($community->members_count ?? 1) . ' members'
            ]);
        }

        // 5. Gatherings Organized
        foreach ($user->events()->latest('event_date')->take(20)->get() as $event) {
            $activities->push([
                'id' => 'event_' . $event->id,
                'type' => 'events',
                'type_label' => 'Gathering Hosted',
                'icon' => 'calendar',
                'color' => 'purple',
                'title' => 'Organized Gathering: ' . $event->title,
                'description' => \Illuminate\Support\Str::limit($event->description, 140),
                'url' => route('events.show', $event),
                'created_at' => $event->created_at,
                'badge' => ucfirst($event->type),
                'metrics' => $event->event_date->format('M j, Y')
            ]);
        }

        // 6. Gatherings Attending
        foreach ($user->attendingEvents()->latest('event_date')->take(20)->get() as $event) {
            $activities->push([
                'id' => 'attending_' . $event->id,
                'type' => 'events',
                'type_label' => 'Workshop Registered',
                'icon' => 'ticket',
                'color' => 'emerald',
                'title' => 'Attending Gathering: ' . $event->title,
                'description' => \Illuminate\Support\Str::limit($event->description, 140),
                'url' => route('events.show', $event),
                'created_at' => $event->created_at,
                'badge' => 'Active Pass',
                'metrics' => $event->event_date->format('M j, Y')
            ]);
        }

        // 7. Vault Items
        $vaultQuery = $user->vaultItems()->latest();
        if (!$isOwner) {
            $vaultQuery->where('access_level', 'public');
        }
        foreach ($vaultQuery->take(25)->get() as $item) {
            $activities->push([
                'id' => 'vault_' . $item->id,
                'type' => 'vault',
                'type_label' => 'Vault Heirloom',
                'icon' => 'lock',
                'color' => 'rose',
                'title' => 'Archived Heirloom: ' . $item->title,
                'description' => \Illuminate\Support\Str::limit($item->description, 140),
                'url' => route('vault.show', $item),
                'created_at' => $item->created_at,
                'badge' => ucfirst($item->access_level),
                'metrics' => $item->file_size
            ]);
        }

        $sortedActivities = $activities->sortByDesc('created_at')->values();

        $totalTaps = (int) $user->posts()->sum('taps_count') + (int) $user->taps_received;
        $totalResonance = (int) $user->posts()->sum('resonance_count');
        $contributionsCount = $user->cultures()->count();
        $storiesCount = $user->posts()->count();
        $vaultCount = $isOwner ? $user->vaultItems()->count() : $user->vaultItems()->where('access_level', 'public')->count();

        $stats = [
            'stories' => $storiesCount,
            'locked_in' => $user->locked_in_count,
            'vibes' => $totalTaps,
            'resonance' => $totalResonance,
            'contributions' => $contributionsCount,
            'vault' => $vaultCount,
            'communities' => $user->communities()->count(),
            'events' => $user->events()->count() + $user->attendingEvents()->count(),
            'preservation_score' => min(100, round(($contributionsCount * 10) + ($storiesCount * 2) + ($vaultCount * 3) + ($totalResonance * 0.1))),
        ];

        return view('profile.activity', compact('user', 'stats', 'sortedActivities', 'isOwner'));
    }

    /**
     * Create life chapter
     */
    public function createChapter()
    {
        return view('profile.chapters.create');
    }

    /**
     * Store life chapter
     */
    public function storeChapter(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:2000',
            'period' => 'nullable|string|max:100',
            'location' => 'nullable|string|max:255',
        ]);

        Auth::user()->lifeChapters()->create([
            'title' => $request->title,
            'description' => $request->description,
            'period' => $request->period,
            'location' => $request->location,
        ]);

        Auth::user()->increment('chapters_count');

        return redirect()->route('profile.show')->with('success', 'Life chapter recorded successfully!');
    }

    /**
     * Delete life chapter
     */
    public function deleteChapter(LifeChapter $chapter)
    {
        if ($chapter->user_id !== Auth::id()) {
            abort(403);
        }

        $chapter->delete();
        Auth::user()->decrement('chapters_count');

        return back()->with('success', 'Life chapter removed.');
    }

    /**
     * Redirect edit to settings
     */
    public function edit(Request $request)
    {
        return redirect()->route('settings.index', Auth::id());
    }

    /**
     * Update basic profile info (Breeze compatibility)
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $request->user()->fill($request->validated());

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        $request->user()->save();

        return Redirect::route('profile.show')->with('success', 'Profile updated successfully.');
    }

    /**
     * Settings Dashboard View
     */
    public function settings($id)
    {
        if (Auth::id() != $id && (!Auth::user() || !Auth::user()->is_admin)) {
            abort(403, 'Unauthorized access to settings.');
        }

        $user = User::findOrFail($id);

        return view('profile.settings', compact('user'));
    }

    /**
     * Update Settings (all tabs)
     */
    public function updateSettings(Request $request, $id)
    {
        if (Auth::id() != $id && (!Auth::user() || !Auth::user()->is_admin)) {
            abort(403, 'Unauthorized access to settings.');
        }

        $user = User::findOrFail($id);

        if ($request->isMethod('post') || $request->isMethod('patch') || $request->isMethod('put')) {
            // Validation
            $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|email|unique:users,email,' . $user->id,
                'username' => 'nullable|string|max:255|unique:users,username,' . $user->id,
                'bio' => 'nullable|string|max:1000',
                'location' => 'nullable|string|max:255',
                'website' => 'nullable|url|max:255',
                'cultural_background' => 'nullable|string|max:255',
                'languages' => 'nullable|array',
                'cultural_interests' => 'nullable|array',
            ]);

            $userData = [
                'name' => $request->name,
                'email' => $request->email,
                'username' => $request->username,
                'bio' => $request->bio,
                'location' => $request->location,
                'website' => $request->website,
                'cultural_background' => $request->cultural_background,
            ];

            if ($request->has('languages')) {
                $userData['languages'] = $request->languages;
            }
            if ($request->has('cultural_interests')) {
                $userData['cultural_interests'] = $request->cultural_interests;
            }

            $user->update($userData);

            // Password update
            if ($request->filled('password')) {
                $request->validate([
                    'current_password' => 'nullable|current_password',
                    'password' => 'required|string|min:8|confirmed',
                ]);

                $user->update([
                    'password' => Hash::make($request->password),
                ]);
            }

            // Avatar upload in settings form
            if ($request->hasFile('avatar')) {
                $request->validate([
                    'avatar' => 'image|mimes:jpeg,png,jpg,gif,webp|max:5120',
                ]);

                if ($user->avatar && Storage::disk('public')->exists('avatars/' . $user->avatar)) {
                    Storage::disk('public')->delete('avatars/' . $user->avatar);
                }

                $avatarName = time() . '_' . uniqid() . '.' . $request->file('avatar')->extension();
                $request->file('avatar')->storeAs('avatars', $avatarName, 'public');

                $user->update(['avatar' => $avatarName]);
            }

            // Cover banner upload in settings form
            if ($request->hasFile('cover_photo')) {
                $request->validate([
                    'cover_photo' => 'image|mimes:jpeg,png,jpg,gif,webp|max:8192',
                ]);

                if ($user->cover_photo && Storage::disk('public')->exists('covers/' . $user->cover_photo)) {
                    Storage::disk('public')->delete('covers/' . $user->cover_photo);
                }

                $coverName = time() . '_' . uniqid() . '.' . $request->file('cover_photo')->extension();
                $request->file('cover_photo')->storeAs('covers', $coverName, 'public');

                $user->update(['cover_photo' => $coverName]);
            }

            // Preferences (theme, privacy, notifications)
            if ($request->has('preferences')) {
                $currentPrefs = is_array($user->preferences) ? $user->preferences : [];
                $newPrefs = is_array($request->preferences) ? $request->preferences : (json_decode($request->preferences, true) ?? []);
                $user->preferences = array_merge($currentPrefs, $newPrefs);
                $user->save();
            }

            return redirect()->back()->with('success', 'Profile and preferences updated successfully.');
        }

        return view('profile.settings', compact('user'));
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validate([
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }

    /**
     * Tap / Vibe user profile
     */
    public function tap(User $user)
    {
        $authUser = Auth::user();

        if ($authUser->id === $user->id) {
            return response()->json(['error' => 'Cannot tap yourself'], 400);
        }

        $interaction = $authUser->interactions()
            ->where('interactable_type', User::class)
            ->where('interactable_id', $user->id)
            ->where('type', 'tap')
            ->first();

        if ($interaction) {
            $interaction->delete();
            $user->decrement('taps_received');
            $tapped = false;
        } else {
            $authUser->interactions()->create([
                'interactable_type' => User::class,
                'interactable_id' => $user->id,
                'type' => 'tap',
            ]);
            $user->increment('taps_received');
            $tapped = true;

            $user->notifications()->create([
                'from_user_id' => $authUser->id,
                'type' => 'tap',
                'title' => 'New Profile Vibe',
                'message' => $authUser->name . ' vibed with your profile',
                'data' => ['user_id' => $authUser->id],
            ]);
        }

        return response()->json([
            'tapped' => $tapped,
            'count' => $user->fresh()->taps_received,
        ]);
    }

    /**
     * Lock-in user connection
     */
    public function lockin(User $user)
    {
        $authUser = Auth::user();

        if ($authUser->id === $user->id) {
            return response()->json(['error' => 'Cannot lock-in yourself'], 400);
        }

        $interaction = $authUser->interactions()
            ->where('interactable_type', User::class)
            ->where('interactable_id', $user->id)
            ->where('type', 'lockin')
            ->first();

        if ($interaction) {
            $interaction->delete();
            $user->decrement('locked_in_count');
            $lockedIn = false;
        } else {
            $authUser->interactions()->create([
                'interactable_type' => User::class,
                'interactable_id' => $user->id,
                'type' => 'lockin',
            ]);
            $user->increment('locked_in_count');
            $lockedIn = true;

            $user->notifications()->create([
                'from_user_id' => $authUser->id,
                'type' => 'lockin',
                'title' => 'New Connection',
                'message' => $authUser->name . ' locked-in with you',
                'data' => ['user_id' => $authUser->id],
            ]);
        }

        return response()->json([
            'lockedIn' => $lockedIn,
            'count' => $user->fresh()->locked_in_count,
        ]);
    }
}
