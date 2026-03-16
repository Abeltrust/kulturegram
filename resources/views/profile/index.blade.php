{{-- resources/views/profile/index.blade.php --}}
@extends('layouts.app')

@section('content')
    <div class="flex flex-col min-h-screen bg-stone-50 dark:bg-stone-950 overflow-x-hidden w-full box-border">
        <div class="max-w-4xl mx-auto p-3 sm:p-6 space-y-4 sm:space-y-6 w-full box-border">

            {{-- Profile Header Card --}}
            <div
                class="bg-white dark:bg-stone-900 rounded-2xl sm:rounded-3xl shadow-sm border border-stone-100 dark:border-stone-800 overflow-hidden relative mb-4">
                {{-- Cover Banner --}}
                <div class="w-full h-24 sm:h-48 bg-gradient-to-r from-amber-400 via-orange-500 to-rose-500"></div>

                {{-- Profile Info Section --}}
                <div class="px-3 pb-5 sm:px-8 relative flex flex-col items-center">
                    {{-- Avatar (Overlapping) --}}
                    <div
                        class="relative w-20 h-20 sm:w-36 sm:h-36 rounded-full border-4 border-white dark:border-stone-900 shadow-md overflow-hidden bg-stone-100 dark:bg-stone-800 -mt-10 sm:-mt-18 flex-shrink-0 z-10">
                            <img src="{{ $user->profile_photo_url }}" alt="{{ $user->name }}"
                                class="w-full h-full object-cover"
                                loading="lazy">

                        {{-- Upload Profile Photo --}}
                        @auth
                            @if(auth()->id() === $user->id)
                                <form action="{{ route('profile.photo.upload') }}" method="POST" enctype="multipart/form-data"
                                    class="absolute bottom-0 right-0 z-20">
                                    @csrf
                                    <label
                                        class="cursor-pointer bg-white dark:bg-stone-800 p-1.5 rounded-full shadow border border-stone-100 dark:border-stone-700 hover:bg-stone-50 dark:hover:bg-stone-700 transition flex items-center justify-center">
                                        <input type="file" name="profile_photo" class="hidden" onchange="this.form.submit()">
                                        <i data-lucide="camera" class="w-4 h-4 text-orange-500"></i>
                                    </label>
                                </form>
                            @endif
                        @endauth
                    </div>

                    {{-- Text Info --}}
                    <div class="text-center mt-2 sm:mt-3 w-full">
                        <h1 class="text-lg sm:text-2xl font-black text-stone-800 dark:text-stone-100 truncate">
                            {{ $user->name }}</h1>
                        <p
                            class="text-xs sm:text-sm text-stone-500 dark:text-stone-400 mt-1 max-w-md mx-auto leading-relaxed">
                            {{ $user->bio ?? 'Software Architect & Cultural Preservationist | Building bridges between tech & heritage' }}
                        </p>
                    </div>

                    {{-- Edit Profile or Interact Buttons --}}
                    @auth
                        @if(auth()->id() === $user->id)
                            <a href="{{ route('profile.edit') }}"
                                class="inline-flex items-center justify-center mt-4 sm:mt-5 px-5 sm:px-6 py-2 sm:py-2.5 bg-gradient-to-r from-amber-500 to-orange-600 text-white rounded-xl text-xs sm:text-sm font-bold shadow-md hover:shadow-lg transition-all active:scale-95">
                                Edit Profile
                            </a>
                        @else
                            <div class="mt-4 sm:mt-5 flex flex-wrap justify-center gap-2 sm:gap-3 w-full max-w-sm mx-auto">
                                <!-- Vibe Button -->
                                <button onclick="toggleUserTap({{ $user->id }})" id="profile-vibe-btn"
                                    class="flex items-center justify-center space-x-1.5 px-3 sm:px-4 py-2 sm:py-2.5 rounded-xl text-xs font-semibold transition-all shadow-sm flex-1 sm:flex-none
                                                                                                                {{ isset($hasVibed) && $hasVibed ? 'bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-500 hover:bg-amber-200' : 'bg-white dark:bg-stone-800 border border-stone-200 dark:border-stone-700 text-stone-700 dark:text-stone-300 hover:bg-stone-50' }}">
                                    <i data-lucide="thumbs-up" id="profile-vibe-icon"
                                        class="w-3.5 h-3.5 sm:w-4 sm:h-4 {{ isset($hasVibed) && $hasVibed ? 'fill-current' : '' }}"></i>
                                    <span id="profile-vibe-text">{{ isset($hasVibed) && $hasVibed ? 'Vibed' : 'Vibe' }}</span>
                                </button>

                                <!-- Lock-in Button -->
                                <button onclick="toggleUserLockIn({{ $user->id }})" id="profile-lockin-btn"
                                    class="flex items-center justify-center space-x-1.5 px-4 sm:px-6 py-2 sm:py-2.5 rounded-xl text-xs sm:text-sm font-bold transition-all shadow-sm flex-1 sm:flex-none
                                                                                                                {{ isset($isLockedIn) && $isLockedIn ? 'bg-stone-100 dark:bg-stone-800 text-stone-700 dark:text-stone-300 hover:bg-red-50 dark:hover:bg-red-900/20 hover:text-red-600' : 'bg-gradient-to-r from-amber-500 to-orange-600 text-white hover:shadow-md hover:scale-[1.02]' }}">
                                    <i data-lucide="{{ isset($isLockedIn) && $isLockedIn ? 'user-check' : 'user-plus' }}"
                                        id="profile-lockin-icon" class="w-3.5 h-3.5 sm:w-4 sm:h-4"></i>
                                    <span
                                        id="profile-lockin-text">{{ isset($isLockedIn) && $isLockedIn ? 'Locked In' : 'Lock In' }}</span>
                                </button>

                                <!-- Send Message Button (Restricted) -->
                                @if(isset($isLockedIn) && $isLockedIn)
                                    <a href="{{ route('messages.start', $user->id) }}"
                                        class="flex items-center justify-center space-x-1.5 px-3 sm:px-5 py-2 sm:py-2.5 bg-stone-800 text-white rounded-xl text-xs sm:text-sm font-bold shadow-sm hover:bg-stone-900 transition-all flex-none">
                                        <i data-lucide="message-circle" class="w-3.5 h-3.5 sm:w-4 sm:h-4"></i>
                                        <span class="hidden sm:inline">Message</span>
                                    </a>
                                @endif
                            </div>
                        @endif
                    @endauth
                </div>
            </div>

            {{-- Stats --}}
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-2 sm:gap-4 text-center mb-2">
                <div
                    class="bg-white dark:bg-stone-900 rounded-2xl sm:rounded-3xl shadow-sm p-3 sm:p-5 border border-stone-100 dark:border-stone-800 transition hover:shadow-md">
                    <p class="text-lg sm:text-2xl font-black text-stone-800 dark:text-stone-100">{{ $user->posts_count ?? 0 }}</p>
                    <p class="text-[10px] sm:text-xs font-bold text-stone-400 dark:text-stone-500 mt-1 uppercase tracking-wider">Stories</p>
                </div>
                <div
                    class="bg-white dark:bg-stone-900 rounded-2xl sm:rounded-3xl shadow-sm p-3 sm:p-5 border border-stone-100 dark:border-stone-800 transition hover:shadow-md">
                    <p class="text-lg sm:text-2xl font-black text-stone-800 dark:text-stone-100" id="profile-locked-in-count">
                        {{ $user->locked_in_count ?? 0 }}
                    </p>
                    <p class="text-[10px] sm:text-xs font-bold text-stone-400 dark:text-stone-500 mt-1 uppercase tracking-wider">Locked-In</p>
                </div>
                <div
                    class="bg-white dark:bg-stone-900 rounded-2xl sm:rounded-3xl shadow-sm p-3 sm:p-5 border border-stone-100 dark:border-stone-800 transition hover:shadow-md">
                    <p class="text-lg sm:text-2xl font-black text-stone-800 dark:text-stone-100" id="profile-vibes-count">
                        {{ $user->taps_received ?? 0 }}
                    </p>
                    <p class="text-[10px] sm:text-xs font-bold text-stone-400 dark:text-stone-500 mt-1 uppercase tracking-wider">Vibes</p>
                </div>
                <div
                    class="bg-white dark:bg-stone-900 rounded-2xl sm:rounded-3xl shadow-sm p-3 sm:p-5 border border-stone-100 dark:border-stone-800 transition hover:shadow-md">
                    <p class="text-lg sm:text-2xl font-black text-stone-800 dark:text-stone-100">{{ $user->cultures_contributed ?? 0 }}</p>
                    <p
                        class="text-[10px] sm:text-xs font-bold text-stone-400 dark:text-stone-500 mt-1 uppercase tracking-wider">
                        Contributions</p>
                </div>
            </div>

            {{-- Tabs & Content Options - RESTRICTED IF NOT LOCKED IN --}}
            @if(auth()->id() === $user->id || (isset($isLockedIn) && $isLockedIn))
                <div
                    class="bg-white dark:bg-stone-900 rounded-3xl shadow-sm p-5 sm:p-6 border border-stone-100 dark:border-stone-800 mb-8">
                    <div class="flex gap-6 border-b border-stone-100 dark:border-stone-800 mb-5 overflow-x-auto scrollbar-hide">
                        <button
                            class="text-sm font-bold pb-3 border-b-2 border-orange-500 text-stone-800 dark:text-stone-100 flex-shrink-0">Life
                            Story</button>
                        <button
                            class="text-sm font-semibold pb-3 text-stone-400 dark:text-stone-500 hover:text-stone-800 dark:hover:text-stone-200 transition flex-shrink-0">Cultural
                            Identity</button>
                        <button
                            class="text-sm font-semibold pb-3 text-stone-400 dark:text-stone-500 hover:text-stone-800 dark:hover:text-stone-200 transition flex-shrink-0">Achievements</button>
                        <button
                            class="text-sm font-semibold pb-3 text-stone-400 dark:text-stone-500 hover:text-stone-800 dark:hover:text-stone-200 transition flex-shrink-0">Private
                            Vault</button>
                    </div>

                    {{-- Life Chapters --}}
                    <div class="space-y-3">
                        @foreach($user->lifeChapters as $chapter)
                            <div
                                class="bg-stone-50 dark:bg-stone-800/50 p-3 rounded-xl border border-stone-100 dark:border-stone-700 flex flex-col sm:flex-row justify-between items-start gap-3">
                                <div class="flex gap-3 items-start flex-1">
                                    <div class="p-2 bg-orange-400 rounded-lg text-white flex-shrink-0">
                                        <i data-lucide="book" class="w-5 h-5"></i>
                                    </div>
                                    <div>
                                        <h3 class="font-semibold text-stone-800 dark:text-stone-100 text-sm sm:text-base">
                                            {{ $chapter->title }}</h3>
                                        <p class="text-stone-600 dark:text-stone-400 text-xs sm:text-sm mt-0.5">
                                            {{ $chapter->description }}</p>
                                        <p class="text-xs text-stone-400 dark:text-stone-500 mt-1">
                                            {{ $chapter->location ?? 'Jos, Nigeria' }} •
                                            {{ $chapter->stories_count ?? 0 }} Stories
                                        </p>
                                    </div>
                                </div>
                                <span class="text-xs text-stone-400 mt-1 sm:mt-0">{{ $chapter->start_year }} -
                                    {{ $chapter->end_year ?? 'Present' }}</span>
                            </div>
                        @endforeach

                        {{-- Add Chapter --}}
                        @auth
                            @if(auth()->id() === $user->id)
                                <a href=""
                                    class="inline-flex items-center gap-2 px-3 py-1.5 text-orange-500 border border-orange-300 dark:border-orange-500/30 rounded-lg hover:bg-orange-50 dark:hover:bg-orange-500/10 transition text-sm">
                                    <i data-lucide="plus" class="w-4 h-4"></i> Add Chapter
                                </a>
                            @endif
                        @endauth
                    </div>
                </div>
            @else
                {{-- Placeholder / Restricted View --}}
                <div
                    class="bg-white dark:bg-stone-900 rounded-2xl shadow p-8 border border-stone-100 dark:border-stone-800 mx-3 sm:mx-0 text-center">
                    <div
                        class="w-16 h-16 bg-stone-100 dark:bg-stone-800 rounded-full flex items-center justify-center mx-auto mb-4 border border-stone-200 dark:border-stone-700">
                        <i data-lucide="lock" class="w-8 h-8 text-stone-400"></i>
                    </div>
                    <h3 class="text-lg font-semibold text-stone-800 dark:text-white mb-2">Restricted Profile</h3>
                    <p class="text-stone-500 dark:text-stone-400 text-sm max-w-sm mx-auto mb-6">
                        Lock in with {{ explode(' ', trim($user->name))[0] }} to see their full life story, cultural identity,
                        and send them direct messages.
                    </p>
                    <button onclick="toggleUserLockIn({{ $user->id }})"
                        class="inline-flex items-center space-x-2 px-6 py-2.5 bg-gradient-to-r from-amber-500 to-orange-600 text-white rounded-xl text-sm font-bold shadow-md hover:shadow-lg transition-all">
                        <i data-lucide="user-plus" class="w-4 h-4"></i>
                        <span>Lock In Now</span>
                    </button>
                </div>
            @endif
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            lucide.createIcons();
        });

        @auth
                // Toggle User Vibe (Tap)
                function toggleUserTap(userId) {
                    fetch(`/users/${userId}/tap`, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        }
                    })
                        .then(response => {
                            if (!response.ok) throw new Error('Network response was not ok');
                            return response.json();
                        })
                        .then(data => {
                            if (data.error) {
                                console.error(data.error);
                                return;
                            }
                            const icon = document.getElementById(`profile-vibe-icon`);
                            const count = document.getElementById(`profile-vibes-count`);
                            const text = document.getElementById(`profile-vibe-text`);
                            const btn = document.getElementById(`profile-vibe-btn`);

                            if (count) count.textContent = data.count;

                            if (data.tapped) {
                                btn.className = "flex items-center justify-center space-x-1.5 px-4 py-2 rounded-xl text-xs font-semibold transition-all shadow-sm bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-500 hover:bg-amber-200 dark:hover:bg-amber-900/40";
                                icon.classList.add('fill-current');
                                text.textContent = 'Vibed';
                            } else {
                                btn.className = "flex items-center justify-center space-x-1.5 px-4 py-2 rounded-xl text-xs font-semibold transition-all shadow-sm bg-white dark:bg-stone-800 border border-stone-200 dark:border-stone-700 text-stone-700 dark:text-stone-300 hover:bg-stone-50 dark:hover:bg-stone-700";
                                icon.classList.remove('fill-current');
                                text.textContent = 'Vibe';
                            }
                        })
                        .catch(error => console.error('Error toggling vibe:', error));
                }

            // Toggle User Lock-in
            function toggleUserLockIn(userId) {
                fetch(`/users/${userId}/lockin`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    }
                })
                    .then(response => {
                        if (!response.ok) throw new Error('Network response was not ok');
                        return response.json();
                    })
                    .then(data => {
                        if (data.error) {
                            console.error(data.error);
                            return;
                             }
                        // Just refresh the entire page so the user can immediately see the new restricted layout / get message access
                        window.location.reload();
                    })
                    .catch(error => console.error('Error toggling lock-in:', error));
            }
        @endauth
    </script>
@endpush