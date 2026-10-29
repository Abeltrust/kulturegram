<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Providers\RouteServiceProvider;
use App\Services\UserOriginService;
use App\Services\WelcomeFlyerService;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(UserOriginService $origin)
    {
        return view('auth.register', ['originOptions' => $origin->options()]);
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request, UserOriginService $origin, WelcomeFlyerService $flyerService): RedirectResponse
    {
        if (!$request->filled('username') && $request->filled('name')) {
            $request->merge([
                'username' => strtolower(preg_replace('/[^A-Za-z0-9]/', '', $request->name)) . rand(10, 99)
            ]);
        }

        $validated = $request->validate(array_merge([
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:users,username',
            'email' => 'required|string|lowercase|email|max:255|unique:users,email',
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
            'terms' => ['required', 'accepted'],
        ], $origin->rules($request)), array_merge([
            'terms.required' => 'You must review and agree to the Kulturegram Terms of Service and Community Guidelines before signing up.',
            'terms.accepted' => 'You must review and agree to the Kulturegram Terms of Service and Community Guidelines before signing up.',
        ], $origin->messages()));

        $user = DB::transaction(function () use ($request, $validated, $origin, $flyerService) {
            $user = User::create([
                'name' => $request->name,
                'username' => $request->username, 
                'email' => $request->email,
                'password' => Hash::make($request->password),
            ]);

            if ($request->hasFile('avatar')) {
                $avatarName = time() . '_' . uniqid() . '.' . $request->file('avatar')->extension();
                $request->file('avatar')->storeAs('avatars', $avatarName, 'public');
                $user->avatar = $avatarName;
                $user->save();
            } else {
                // If user didn't upload a picture, assign an authentic cultural avatar as their profile picture
                $flyerService->assignCulturalAvatar($user);
            }

            $origin->save($user, $validated);

            return $user;
        });

        event(new Registered($user));

        Auth::login($user);

        // Generate welcome cultural flyer & first-time introduction post on timeline
        $welcomePost = $flyerService->createWelcomePost($user);

        // Flash cultural flyer payload for the automatic welcome popup modal
        $user->load(['country', 'state', 'localGovernment', 'spokenLanguages']);
        $locationParts = array_filter([
            $user->localGovernment?->name,
            $user->state?->name,
            $user->country?->name ?: $user->location,
        ]);
        $locationText = !empty($locationParts) ? implode(', ', $locationParts) : ($user->location ?: 'Global Village');

        session()->flash('registered_flyer', [
            'name' => $user->name,
            'username' => $user->username,
            'avatar_url' => $user->profile_photo_url,
            'location' => $locationText,
            'country_flag' => $user->country?->flag ?? '🌍',
            'languages' => $user->spokenLanguages->pluck('name')->all(),
            'flyer_path' => $welcomePost?->image,
            'flyer_url' => $welcomePost ? asset('storage/' . $welcomePost->image) : null,
            'post_id' => $welcomePost?->id,
        ]);

        return redirect(RouteServiceProvider::HOME);
    }
}
