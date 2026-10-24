{{-- resources/views/profile/settings.blade.php --}}
@extends('layouts.app')

@section('content')
<div class="max-w-6xl mx-auto px-4 py-8" x-data="{
    activeTab: 'profile',
    avatarPreview: null,
    coverPreview: null,
    showDeleteModal: false
}">
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 text-xs font-bold text-amber-600 dark:text-amber-400 uppercase tracking-wider mb-1">
                <a href="{{ route('profile.show') }}" class="hover:underline flex items-center gap-1">
                    <i data-lucide="arrow-left" class="w-3.5 h-3.5"></i> Back to Profile
                </a>
            </div>
            <h1 class="text-2xl sm:text-3xl font-black text-stone-900 dark:text-white">Account & Privacy Settings</h1>
            <p class="text-xs sm:text-sm text-stone-500">Manage your profile identity, security, notifications, and cultural data sovereignty.</p>
        </div>

        <a href="{{ route('privacy') }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-stone-100 hover:bg-stone-200 dark:bg-stone-800 dark:hover:bg-stone-700 text-stone-700 dark:text-stone-300 text-xs font-bold transition w-fit">
            <i data-lucide="shield-check" class="w-4 h-4 text-amber-500"></i>
            Privacy Policy
        </a>
    </div>

    @if(session('success'))
        <div class="mb-6 p-4 rounded-2xl bg-green-50 dark:bg-green-950/40 border border-green-200 dark:border-green-800 text-green-700 dark:text-green-300 text-sm font-semibold flex items-center justify-between shadow-sm">
            <div class="flex items-center gap-2">
                <i data-lucide="check-circle" class="w-5 h-5 text-green-500"></i>
                <span>{{ session('success') }}</span>
            </div>
            <button type="button" @click="$el.parentElement.remove()" class="text-green-500 hover:text-green-700">
                <i data-lucide="x" class="w-4 h-4"></i>
            </button>
        </div>
    @endif

    @if($errors->any())
        <div class="mb-6 p-4 rounded-2xl bg-red-50 dark:bg-red-950/40 border border-red-200 dark:border-red-800 text-red-700 dark:text-red-300 text-sm font-semibold space-y-1">
            <div class="flex items-center gap-2 font-bold mb-1">
                <i data-lucide="alert-triangle" class="w-5 h-5 text-red-500"></i>
                <span>Please correct the errors below:</span>
            </div>
            <ul class="list-disc list-inside text-xs space-y-1 pl-4">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="grid grid-cols-1 md:grid-cols-12 gap-6 items-start">

        <!-- SIDEBAR NAVIGATION -->
        <aside class="md:col-span-4 bg-white dark:bg-stone-900 border border-stone-200/80 dark:border-stone-800 rounded-3xl p-3 sm:p-4 shadow-sm space-y-1">
            
            <button @click="activeTab = 'profile'"
                :class="activeTab === 'profile' ? 'bg-amber-500 text-white shadow-md' : 'text-stone-600 dark:text-stone-400 hover:bg-stone-100 dark:hover:bg-stone-800'"
                class="flex items-center gap-3 px-4 py-3 w-full text-left rounded-2xl text-xs sm:text-sm font-bold transition-all">
                <i data-lucide="user" class="w-4 h-4"></i>
                <span>Profile & Identity</span>
            </button>

            <button @click="activeTab = 'security'"
                :class="activeTab === 'security' ? 'bg-amber-500 text-white shadow-md' : 'text-stone-600 dark:text-stone-400 hover:bg-stone-100 dark:hover:bg-stone-800'"
                class="flex items-center gap-3 px-4 py-3 w-full text-left rounded-2xl text-xs sm:text-sm font-bold transition-all">
                <i data-lucide="key" class="w-4 h-4"></i>
                <span>Security & Password</span>
            </button>

            <button @click="activeTab = 'privacy'"
                :class="activeTab === 'privacy' ? 'bg-amber-500 text-white shadow-md' : 'text-stone-600 dark:text-stone-400 hover:bg-stone-100 dark:hover:bg-stone-800'"
                class="flex items-center gap-3 px-4 py-3 w-full text-left rounded-2xl text-xs sm:text-sm font-bold transition-all">
                <i data-lucide="shield" class="w-4 h-4"></i>
                <span>Privacy & Sovereignty</span>
            </button>

            <button @click="activeTab = 'notifications'"
                :class="activeTab === 'notifications' ? 'bg-amber-500 text-white shadow-md' : 'text-stone-600 dark:text-stone-400 hover:bg-stone-100 dark:hover:bg-stone-800'"
                class="flex items-center gap-3 px-4 py-3 w-full text-left rounded-2xl text-xs sm:text-sm font-bold transition-all">
                <i data-lucide="bell" class="w-4 h-4"></i>
                <span>Notifications</span>
            </button>

            <button @click="activeTab = 'appearance'"
                :class="activeTab === 'appearance' ? 'bg-amber-500 text-white shadow-md' : 'text-stone-600 dark:text-stone-400 hover:bg-stone-100 dark:hover:bg-stone-800'"
                class="flex items-center gap-3 px-4 py-3 w-full text-left rounded-2xl text-xs sm:text-sm font-bold transition-all">
                <i data-lucide="palette" class="w-4 h-4"></i>
                <span>Appearance</span>
            </button>

            <button @click="activeTab = 'data'"
                :class="activeTab === 'data' ? 'bg-amber-500 text-white shadow-md' : 'text-stone-600 dark:text-stone-400 hover:bg-stone-100 dark:hover:bg-stone-800'"
                class="flex items-center gap-3 px-4 py-3 w-full text-left rounded-2xl text-xs sm:text-sm font-bold transition-all">
                <i data-lucide="database" class="w-4 h-4"></i>
                <span>Data & Account</span>
            </button>

        </aside>

        <!-- MAIN FORM AREA -->
        <main class="md:col-span-8 bg-white dark:bg-stone-900 border border-stone-200/80 dark:border-stone-800 rounded-3xl p-6 sm:p-8 shadow-sm">
            
            <form action="{{ route('settings.update', $user->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PATCH')

                <!-- TAB 1: PROFILE & IDENTITY -->
                <div x-show="activeTab === 'profile'" class="space-y-6">
                    <div class="border-b border-stone-100 dark:border-stone-800 pb-4">
                        <h2 class="text-lg sm:text-xl font-black text-stone-900 dark:text-white">Profile Details</h2>
                        <p class="text-xs text-stone-500">Your public identity on Kulturegram.</p>
                    </div>

                    {{-- Avatar & Banner Upload --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pb-4 border-b border-stone-100 dark:border-stone-800">
                        {{-- Avatar --}}
                        <div class="flex items-center gap-4 p-4 rounded-2xl bg-stone-50 dark:bg-stone-800/60 border border-stone-200/60 dark:border-stone-700/60">
                            <div class="relative w-16 h-16 rounded-2xl overflow-hidden bg-stone-200 dark:bg-stone-700 shrink-0 border-2 border-white dark:border-stone-800 shadow">
                                <img id="settings-avatar-img" :src="avatarPreview ?: '{{ $user->profile_photo_url }}'" class="w-full h-full object-cover">
                            </div>
                            <div>
                                <h4 class="text-xs font-bold text-stone-900 dark:text-white uppercase tracking-wider mb-1">Avatar</h4>
                                <label class="cursor-pointer inline-flex items-center gap-1.5 px-3 py-1.5 bg-amber-500 hover:bg-amber-600 text-white rounded-xl text-xs font-bold transition shadow-sm">
                                    <i data-lucide="camera" class="w-3.5 h-3.5"></i> Choose Image
                                    <input type="file" name="avatar" class="hidden" accept="image/*"
                                        @change="avatarPreview = URL.createObjectURL($event.target.files[0])">
                                </label>
                            </div>
                        </div>

                        {{-- Cover Banner --}}
                        <div class="flex items-center gap-4 p-4 rounded-2xl bg-stone-50 dark:bg-stone-800/60 border border-stone-200/60 dark:border-stone-700/60">
                            <div class="relative w-16 h-16 rounded-2xl overflow-hidden bg-gradient-to-r from-amber-500 to-orange-600 shrink-0 border-2 border-white dark:border-stone-800 shadow">
                                @if($user->cover_photo_url)
                                    <img :src="coverPreview ?: '{{ $user->cover_photo_url }}'" class="w-full h-full object-cover">
                                @endif
                            </div>
                            <div>
                                <h4 class="text-xs font-bold text-stone-900 dark:text-white uppercase tracking-wider mb-1">Cover Banner</h4>
                                <label class="cursor-pointer inline-flex items-center gap-1.5 px-3 py-1.5 bg-stone-800 hover:bg-stone-900 text-white dark:bg-stone-700 dark:hover:bg-stone-600 rounded-xl text-xs font-bold transition shadow-sm">
                                    <i data-lucide="image" class="w-3.5 h-3.5"></i> Choose Banner
                                    <input type="file" name="cover_photo" class="hidden" accept="image/*"
                                        @change="coverPreview = URL.createObjectURL($event.target.files[0])">
                                </label>
                            </div>
                        </div>
                    </div>

                    {{-- Name & Username --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-stone-700 dark:text-stone-300 uppercase tracking-wider mb-1.5">Full Name *</label>
                            <input type="text" name="name" value="{{ old('name', $user->name) }}" required
                                class="w-full px-4 py-2.5 bg-stone-50 dark:bg-stone-800 border border-stone-200 dark:border-stone-700 rounded-xl text-sm font-semibold text-stone-900 dark:text-white focus:ring-2 focus:ring-amber-500 focus:outline-none">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-stone-700 dark:text-stone-300 uppercase tracking-wider mb-1.5">Username *</label>
                            <input type="text" name="username" value="{{ old('username', $user->username) }}" required
                                class="w-full px-4 py-2.5 bg-stone-50 dark:bg-stone-800 border border-stone-200 dark:border-stone-700 rounded-xl text-sm font-semibold text-stone-900 dark:text-white focus:ring-2 focus:ring-amber-500 focus:outline-none">
                        </div>
                    </div>

                    {{-- Email & Website --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-stone-700 dark:text-stone-300 uppercase tracking-wider mb-1.5">Email Address *</label>
                            <input type="email" name="email" value="{{ old('email', $user->email) }}" required
                                class="w-full px-4 py-2.5 bg-stone-50 dark:bg-stone-800 border border-stone-200 dark:border-stone-700 rounded-xl text-sm font-semibold text-stone-900 dark:text-white focus:ring-2 focus:ring-amber-500 focus:outline-none">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-stone-700 dark:text-stone-300 uppercase tracking-wider mb-1.5">Personal Website</label>
                            <input type="url" name="website" value="{{ old('website', $user->website) }}" placeholder="https://example.com"
                                class="w-full px-4 py-2.5 bg-stone-50 dark:bg-stone-800 border border-stone-200 dark:border-stone-700 rounded-xl text-sm text-stone-900 dark:text-white focus:ring-2 focus:ring-amber-500 focus:outline-none">
                        </div>
                    </div>

                    {{-- Location & Cultural Roots --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-stone-700 dark:text-stone-300 uppercase tracking-wider mb-1.5">Current Location</label>
                            <input type="text" name="location" value="{{ old('location', $user->location) }}" placeholder="e.g., Lagos, Nigeria"
                                class="w-full px-4 py-2.5 bg-stone-50 dark:bg-stone-800 border border-stone-200 dark:border-stone-700 rounded-xl text-sm text-stone-900 dark:text-white focus:ring-2 focus:ring-amber-500 focus:outline-none">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-stone-700 dark:text-stone-300 uppercase tracking-wider mb-1.5">Cultural Origin / Heritage</label>
                            <input type="text" name="cultural_background" value="{{ old('cultural_background', $user->cultural_background) }}" placeholder="e.g., Yoruba • Edo • Berom"
                                class="w-full px-4 py-2.5 bg-stone-50 dark:bg-stone-800 border border-stone-200 dark:border-stone-700 rounded-xl text-sm text-stone-900 dark:text-white focus:ring-2 focus:ring-amber-500 focus:outline-none">
                        </div>
                    </div>

                    {{-- Bio --}}
                    <div>
                        <label class="block text-xs font-bold text-stone-700 dark:text-stone-300 uppercase tracking-wider mb-1.5">Bio</label>
                        <textarea name="bio" rows="3" placeholder="Share your cultural perspective and heritage journey..."
                            class="w-full px-4 py-2.5 bg-stone-50 dark:bg-stone-800 border border-stone-200 dark:border-stone-700 rounded-xl text-sm text-stone-900 dark:text-white focus:ring-2 focus:ring-amber-500 focus:outline-none leading-relaxed">{{ old('bio', $user->bio) }}</textarea>
                    </div>

                    <div class="pt-4 flex justify-end">
                        <button type="submit" class="px-6 py-2.5 bg-amber-500 hover:bg-amber-600 text-white font-bold text-sm rounded-xl shadow-md transition active:scale-95">
                            Save Profile
                        </button>
                    </div>
                </div>

                <!-- TAB 2: SECURITY & PASSWORD -->
                <div x-show="activeTab === 'security'" class="space-y-6">
                    <div class="border-b border-stone-100 dark:border-stone-800 pb-4">
                        <h2 class="text-lg sm:text-xl font-black text-stone-900 dark:text-white">Security & Password</h2>
                        <p class="text-xs text-stone-500">Ensure your account and heritage vault remain protected.</p>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-stone-700 dark:text-stone-300 uppercase tracking-wider mb-1.5">Current Password</label>
                        <input type="password" name="current_password" placeholder="Enter current password to change"
                            class="w-full px-4 py-2.5 bg-stone-50 dark:bg-stone-800 border border-stone-200 dark:border-stone-700 rounded-xl text-sm text-stone-900 dark:text-white focus:ring-2 focus:ring-amber-500 focus:outline-none">
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-stone-700 dark:text-stone-300 uppercase tracking-wider mb-1.5">New Password</label>
                            <input type="password" name="password" placeholder="At least 8 characters"
                                class="w-full px-4 py-2.5 bg-stone-50 dark:bg-stone-800 border border-stone-200 dark:border-stone-700 rounded-xl text-sm text-stone-900 dark:text-white focus:ring-2 focus:ring-amber-500 focus:outline-none">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-stone-700 dark:text-stone-300 uppercase tracking-wider mb-1.5">Confirm New Password</label>
                            <input type="password" name="password_confirmation" placeholder="Repeat new password"
                                class="w-full px-4 py-2.5 bg-stone-50 dark:bg-stone-800 border border-stone-200 dark:border-stone-700 rounded-xl text-sm text-stone-900 dark:text-white focus:ring-2 focus:ring-amber-500 focus:outline-none">
                        </div>
                    </div>

                    <div class="pt-4 flex justify-end">
                        <button type="submit" class="px-6 py-2.5 bg-amber-500 hover:bg-amber-600 text-white font-bold text-sm rounded-xl shadow-md transition active:scale-95">
                            Update Password
                        </button>
                    </div>
                </div>

                <!-- TAB 3: PRIVACY & SOVEREIGNTY -->
                <div x-show="activeTab === 'privacy'" class="space-y-6">
                    <div class="border-b border-stone-100 dark:border-stone-800 pb-4">
                        <h2 class="text-lg sm:text-xl font-black text-stone-900 dark:text-white">Privacy & Cultural Sovereignty</h2>
                        <p class="text-xs text-stone-500">Configure visibility and communication permissions.</p>
                    </div>

                    <div class="space-y-4">
                        <div class="p-4 rounded-2xl bg-stone-50 dark:bg-stone-800/60 border border-stone-200/60 dark:border-stone-700/60 space-y-3">
                            <label class="flex items-center justify-between cursor-pointer">
                                <div>
                                    <p class="font-bold text-stone-900 dark:text-white text-sm">Lock-In Requirement for Direct Messages</p>
                                    <p class="text-xs text-stone-500">Only storytellers you have locked-in with can send you messages.</p>
                                </div>
                                <input type="checkbox" name="preferences[direct_message_locked_only]" value="1"
                                    {{ isset($user->preferences['direct_message_locked_only']) && $user->preferences['direct_message_locked_only'] ? 'checked' : 'checked' }}
                                    class="w-5 h-5 rounded text-amber-500 focus:ring-amber-400">
                            </label>
                        </div>

                        <div class="p-4 rounded-2xl bg-stone-50 dark:bg-stone-800/60 border border-stone-200/60 dark:border-stone-700/60 space-y-3">
                            <label class="flex items-center justify-between cursor-pointer">
                                <div>
                                    <p class="font-bold text-stone-900 dark:text-white text-sm">Public Profile Visibility</p>
                                    <p class="text-xs text-stone-500">Allow cultural enthusiasts worldwide to discover your profile and contributions.</p>
                                </div>
                                <input type="checkbox" name="preferences[public_profile]" value="1"
                                    {{ !isset($user->preferences['public_profile']) || $user->preferences['public_profile'] ? 'checked' : '' }}
                                    class="w-5 h-5 rounded text-amber-500 focus:ring-amber-400">
                            </label>
                        </div>

                        {{-- Transparency Notice & Link --}}
                        <div class="p-5 rounded-2xl bg-amber-500/10 border border-amber-500/20 text-stone-800 dark:text-stone-200 space-y-2">
                            <div class="flex items-center gap-2 font-bold text-amber-600 dark:text-amber-400 text-sm">
                                <i data-lucide="shield-check" class="w-5 h-5"></i>
                                <span>Indigenous Data Sovereignty</span>
                            </div>
                            <p class="text-xs leading-relaxed text-stone-600 dark:text-stone-300">
                                Kulturegram commits to protecting your ancestral heritage. We never sell or license your content to third parties. Read our complete policies:
                            </p>
                            <div class="flex gap-4 pt-1">
                                <a href="{{ route('privacy') }}" class="text-xs font-bold text-amber-600 dark:text-amber-400 underline">Privacy Policy</a>
                                <a href="{{ route('terms') }}" class="text-xs font-bold text-amber-600 dark:text-amber-400 underline">Terms of Service</a>
                            </div>
                        </div>
                    </div>

                    <div class="pt-4 flex justify-end">
                        <button type="submit" class="px-6 py-2.5 bg-amber-500 hover:bg-amber-600 text-white font-bold text-sm rounded-xl shadow-md transition active:scale-95">
                            Save Privacy Preferences
                        </button>
                    </div>
                </div>

                <!-- TAB 4: NOTIFICATIONS -->
                <div x-show="activeTab === 'notifications'" class="space-y-6">
                    <div class="border-b border-stone-100 dark:border-stone-800 pb-4">
                        <h2 class="text-lg sm:text-xl font-black text-stone-900 dark:text-white">Notification Preferences</h2>
                        <p class="text-xs text-stone-500">Choose which updates and alerts you wish to receive.</p>
                    </div>

                    <div class="space-y-3">
                        <label class="flex items-center justify-between p-4 rounded-2xl bg-stone-50 dark:bg-stone-800/60 border border-stone-200/60 dark:border-stone-700/60 cursor-pointer">
                            <div>
                                <p class="font-bold text-stone-900 dark:text-white text-sm">Cultural Story Reviews</p>
                                <p class="text-xs text-stone-500">Alerts when your traditions are reviewed, approved, or featured by Guardians.</p>
                            </div>
                            <input type="checkbox" name="preferences[notify_culture_approval]" value="1"
                                {{ !isset($user->preferences['notify_culture_approval']) || $user->preferences['notify_culture_approval'] ? 'checked' : '' }}
                                class="w-5 h-5 rounded text-amber-500 focus:ring-amber-400">
                        </label>

                        <label class="flex items-center justify-between p-4 rounded-2xl bg-stone-50 dark:bg-stone-800/60 border border-stone-200/60 dark:border-stone-700/60 cursor-pointer">
                            <div>
                                <p class="font-bold text-stone-900 dark:text-white text-sm">Gathering & Workshop Reminders</p>
                                <p class="text-xs text-stone-500">Reminders for events you have registered for or are hosting.</p>
                            </div>
                            <input type="checkbox" name="preferences[notify_events]" value="1"
                                {{ !isset($user->preferences['notify_events']) || $user->preferences['notify_events'] ? 'checked' : '' }}
                                class="w-5 h-5 rounded text-amber-500 focus:ring-amber-400">
                        </label>

                        <label class="flex items-center justify-between p-4 rounded-2xl bg-stone-50 dark:bg-stone-800/60 border border-stone-200/60 dark:border-stone-700/60 cursor-pointer">
                            <div>
                                <p class="font-bold text-stone-900 dark:text-white text-sm">Tribal Community Posts</p>
                                <p class="text-xs text-stone-500">Notifications when discussions occur in your joined communities.</p>
                            </div>
                            <input type="checkbox" name="preferences[notify_community]" value="1"
                                {{ !isset($user->preferences['notify_community']) || $user->preferences['notify_community'] ? 'checked' : '' }}
                                class="w-5 h-5 rounded text-amber-500 focus:ring-amber-400">
                        </label>
                    </div>

                    <div class="pt-4 flex justify-end">
                        <button type="submit" class="px-6 py-2.5 bg-amber-500 hover:bg-amber-600 text-white font-bold text-sm rounded-xl shadow-md transition active:scale-95">
                            Save Notifications
                        </button>
                    </div>
                </div>

                <!-- TAB 5: APPEARANCE -->
                <div x-show="activeTab === 'appearance'" class="space-y-6">
                    <div class="border-b border-stone-100 dark:border-stone-800 pb-4">
                        <h2 class="text-lg sm:text-xl font-black text-stone-900 dark:text-white">Appearance & Theme</h2>
                        <p class="text-xs text-stone-500">Customize the visual presentation of Kulturegram.</p>
                    </div>

                    <div class="p-5 rounded-2xl bg-stone-50 dark:bg-stone-800/60 border border-stone-200/60 dark:border-stone-700/60 flex items-center justify-between">
                        <div>
                            <h3 class="font-bold text-stone-900 dark:text-white text-sm sm:text-base">Dark Mode Interface</h3>
                            <p class="text-xs text-stone-500 mt-0.5">Toggle between ambient dark mode and daylight mode.</p>
                        </div>

                        <button type="button" id="theme-toggle-settings"
                            class="relative inline-flex items-center h-7 rounded-full w-14 transition-colors focus:outline-none focus:ring-2 focus:ring-amber-500 bg-stone-200 dark:bg-amber-500">
                            <span id="theme-toggle-slider-settings"
                                class="inline-block w-5 h-5 transform bg-white rounded-full transition-transform translate-x-1 dark:translate-x-8 shadow-sm"></span>
                        </button>
                    </div>
                </div>

                <!-- TAB 6: DATA & ACCOUNT -->
                <div x-show="activeTab === 'data'" class="space-y-6">
                    <div class="border-b border-stone-100 dark:border-stone-800 pb-4">
                        <h2 class="text-lg sm:text-xl font-black text-stone-900 dark:text-white">Data & Account Deletion</h2>
                        <p class="text-xs text-stone-500">Export your archive or permanently close your account.</p>
                    </div>

                    <div class="p-5 rounded-2xl bg-stone-50 dark:bg-stone-800/60 border border-stone-200/60 dark:border-stone-700/60 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                        <div>
                            <h4 class="font-bold text-stone-900 dark:text-white text-sm">Download Legacy Archive</h4>
                            <p class="text-xs text-stone-500 mt-0.5">Your photos, vault items, and stories are available anytime.</p>
                        </div>
                        <a href="{{ route('vault.index') }}" class="px-4 py-2 bg-stone-200 dark:bg-stone-700 hover:bg-stone-300 text-stone-800 dark:text-white rounded-xl text-xs font-bold transition w-fit">
                            Access Vault Archive
                        </a>
                    </div>

                    <div class="p-5 rounded-2xl bg-red-50/60 dark:bg-red-950/20 border border-red-200 dark:border-red-900/40 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                        <div>
                            <h4 class="font-bold text-red-700 dark:text-red-400 text-sm">Delete Account</h4>
                            <p class="text-xs text-red-600/80 dark:text-red-400/80 mt-0.5">Permanently erase your account, private vault, and personal memories.</p>
                        </div>
                        <button type="button" @click="showDeleteModal = true" class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-xl text-xs font-bold transition shadow-sm w-fit">
                            Delete Account
                        </button>
                    </div>
                </div>

            </form>
        </main>

    </div>

    <!-- DELETE ACCOUNT MODAL -->
    <div x-show="showDeleteModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm"
        @keydown.escape.window="showDeleteModal = false">
        <div class="bg-white dark:bg-stone-900 rounded-3xl max-w-md w-full p-6 sm:p-8 border border-stone-200 dark:border-stone-800 shadow-2xl space-y-4"
            @click.away="showDeleteModal = false">
            <div class="w-12 h-12 rounded-2xl bg-red-100 dark:bg-red-950/50 text-red-600 flex items-center justify-center">
                <i data-lucide="alert-triangle" class="w-6 h-6"></i>
            </div>
            <div>
                <h3 class="text-lg font-black text-stone-900 dark:text-white">Are you absolutely sure?</h3>
                <p class="text-xs text-stone-500 mt-1 leading-relaxed">
                    This action cannot be undone. All stories, personal vault files, and connections will be permanently wiped.
                </p>
            </div>

            <form action="{{ route('profile.destroy') }}" method="POST" class="space-y-4 pt-2">
                @csrf
                @method('DELETE')
                <div>
                    <label class="block text-xs font-bold text-stone-700 dark:text-stone-300 uppercase tracking-wider mb-1">Confirm with Password</label>
                    <input type="password" name="password" required placeholder="Your current password"
                        class="w-full px-4 py-2.5 bg-stone-50 dark:bg-stone-800 border border-stone-200 dark:border-stone-700 rounded-xl text-sm text-stone-900 dark:text-white focus:ring-2 focus:ring-red-500 focus:outline-none">
                </div>

                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" @click="showDeleteModal = false" class="px-4 py-2 text-sm font-bold text-stone-500 hover:text-stone-700">Cancel</button>
                    <button type="submit" class="px-5 py-2 bg-red-600 hover:bg-red-700 text-white text-sm font-bold rounded-xl shadow-md">Permanently Delete</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener("DOMContentLoaded", () => {
        lucide.createIcons();

        // Theme toggle logic in settings
        const themeToggle = document.getElementById('theme-toggle-settings');
        if (themeToggle) {
            themeToggle.addEventListener('click', () => {
                const isDark = document.documentElement.classList.toggle('dark');
                localStorage.setItem('theme', isDark ? 'dark' : 'light');
            });
        }
    });
</script>
@endpush