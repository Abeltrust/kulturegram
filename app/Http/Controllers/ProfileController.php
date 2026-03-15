<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;
use Inertia\Response;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Hash;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function photo(Request $request)
    {
        $request->validate([
            'profile_photo' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $user = Auth::user();

        // Delete old avatar if exists
        if ($user->avatar && Storage::exists('public/avatars/' . $user->avatar)) {
            Storage::delete('public/avatars/' . $user->avatar);
        }

        $avatarName = time() . '.' . $request->profile_photo->extension();
        $request->profile_photo->storeAs('public/avatars', $avatarName);

        $user->update([
            'avatar' => $avatarName,
        ]);

        return back()->with('success', 'Profile photo updated successfully.');
    }
    public function show()
    {
        $user = auth()->User()->loadCount([
            //  'stories', 
            // 'lockedIns', 
            // 'taps', 
            // 'lifeChapters'
        ])->load('lifeChapters');

        return view('profile.index', compact('user'));
    }

    public function showUser($user)
    {
        $user = User::withCount([
            'posts',
            'lifeChapters'
        ])
            ->with('lifeChapters')
            ->where('id', $user)
            ->orWhere('username', $user) // optional if you have usernames
            ->firstOrFail();

        $isLockedIn = false;
        $hasVibed = false;

        if (auth()->check()) {
            $isLockedIn = auth()->user()->hasLockedIn($user);
            $hasVibed = auth()->user()->hasTapped($user);
        }

        return view('profile.index', compact('user', 'isLockedIn', 'hasVibed'));
    }

    public function edit(Request $request): Response
    {
        return Inertia::render('Profile/Edit', [
            'mustVerifyEmail' => $request->user() instanceof MustVerifyEmail,
            'status' => session('status'),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $request->user()->fill($request->validated());

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        $request->user()->save();

        return Redirect::route('profile.edit');
    }

    public function settings($id)
    {
        $user = User::findOrFail($id);

        return view('profile.settings', compact('user'));
    }

    public function updateSettings(Request $request, $id)
    {
        $user = User::findOrFail($id);

        // Handle updates
        if ($request->isMethod('post') || $request->isMethod('patch') || $request->isMethod('put')) {
            // Update basic info
            // Update profile info
            $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|email|unique:users,email,' . $user->id,
                'username' => 'nullable|string|max:255|unique:users,username,' . $user->id,
                'bio' => 'nullable|string|max:1000',
                'location' => 'nullable|string|max:255',
                'website' => 'nullable|url|max:255',
            ]);

            $user->update([
                'name' => $request->name,
                'email' => $request->email,
                'username' => $request->username,
                'bio' => $request->bio,
                'location' => $request->location,
                'website' => $request->website,
            ]);

            // Handle password change
            if ($request->filled('password')) {
                $request->validate([
                    'password' => 'required|string|min:8|confirmed',
                ]);

                $user->update([
                    'password' => Hash::make($request->password),
                ]);
            }

            // Handle avatar upload
            if ($request->hasFile('avatar')) {
                $request->validate([
                    'avatar' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
                ]);

                // Delete old avatar if exists
                if ($user->avatar && Storage::exists('public/avatars/' . $user->avatar)) {
                    Storage::delete('public/avatars/' . $user->avatar);
                }

                $avatarName = time() . '.' . $request->avatar->extension();
                $request->avatar->storeAs('public/avatars', $avatarName);

                $user->update([
                    'avatar' => $avatarName,
                ]);
            }

            // Handle preferences (e.g., notifications, theme)
            if ($request->has('preferences')) {
                $user->preferences = json_encode($request->preferences);
                $user->save();
            }

            return redirect()->back()->with('success', 'Settings updated successfully.');
        }

        // GET request → show settings page
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
    public function tap(User $user)
    {
        $authUser = Auth::user();

        // Cannot tap yourself
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

            // Create notification
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

    public function lockin(User $user)
    {
        $authUser = Auth::user();

        // Cannot lockin yourself
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

            // Create notification
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
