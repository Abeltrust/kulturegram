@extends('layouts.app')

@section('title', 'Discover - Kulturegram')

@section('content')
    <div class="max-w-6xl mx-auto px-3 sm:px-4 py-4 sm:py-6">
        <!-- Header -->
        <div class="mb-4 sm:mb-6 text-center md:text-left">
            <h1 class="text-xl sm:text-2xl md:text-3xl font-bold text-stone-800 dark:text-white">Discover</h1>
            <p class="text-stone-50 dark:text-stone-400 text-xs sm:text-sm md:text-base">
                Explore stories, cultures, people, and places from around the world.
            </p>
        </div>

        <!-- Search -->
        <form method="GET" action="{{ route('discover.index') }}" class="mb-4 sm:mb-6">
            <div class="relative">
                <input type="hidden" name="tab" value="{{ $tab }}">
                <input type="text" name="search" value="{{ $search }}"
                    class="w-full rounded-xl border border-stone-300 dark:border-stone-700 bg-white dark:bg-stone-900 pl-10 sm:pl-12 pr-4 py-2 sm:py-3 placeholder-stone-400 dark:placeholder-stone-500 text-stone-700 dark:text-stone-200 focus:ring-amber-500 focus:border-amber-500 text-sm sm:text-base"
                    placeholder="Search">
                <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-stone-400">
                    <i data-lucide="search" class="w-4 sm:w-5 h-4 sm:h-5"></i>
                </span>
                <button type="submit" class="absolute inset-y-0 right-0 px-3 sm:px-4 text-stone-500 hover:text-amber-600">
                    <i data-lucide="filter" class="w-4 sm:w-5 h-4 sm:h-5"></i>
                </button>
            </div>
        </form>

        <!-- Tabs -->
        <div class="flex flex-wrap gap-1.5 sm:gap-2 md:gap-3 mb-5 sm:mb-6 justify-center md:justify-start">
            @php
                $tabs = ['stories' => 'Stories', 'cultures' => 'Cultures', 'people' => 'People', 'places' => 'Places'];
            @endphp
            @foreach ($tabs as $key => $label)
                <a href="{{ route('discover.index', ['tab' => $key, 'search' => $search]) }}" class="px-2 sm:px-3 md:px-4 py-1.5 sm:py-2 rounded-lg text-xs sm:text-sm font-medium transition-all duration-200
                              {{ $tab === $key
                ? 'glass-orange text-white'
                : 'bg-white dark:bg-stone-800 text-stone-600 dark:text-stone-300 border border-stone-200 dark:border-stone-700 hover:border-stone-300 dark:hover:border-stone-600' }}">
                    {{ $label }}
                </a>
            @endforeach
        </div>

        <!-- Data Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4 md:gap-6">
            @forelse ($data as $item)
                @if ($tab === 'stories')
                    <div class="bg-white dark:bg-stone-900 rounded-xl sm:rounded-2xl shadow-sm overflow-hidden border border-stone-200 dark:border-stone-800">
                        <img src="{{ Storage::url($item->image) }}" class="w-full h-36 sm:h-44 md:h-48 object-cover" loading="lazy">
                        <div class="p-3 sm:p-4">
                            <span
                                class="inline-block text-[10px] sm:text-xs font-medium bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-500 px-2 py-0.5 sm:py-1 rounded-full mb-2">
                                {{ $item->category ?? 'Story' }}
                            </span>
                            <h3 class="font-semibold text-stone-800 dark:text-stone-100 text-sm sm:text-base line-clamp-2">
                                {{ $item->title ?? Str::limit($item->content, 60) }}</h3>
                            <p class="text-xs sm:text-sm text-stone-500 dark:text-stone-400 line-clamp-2">{{ Str::limit($item->content, 80) }}</p>
                            <div class="mt-3 flex items-center justify-between text-xs sm:text-sm text-stone-500 dark:text-stone-500">
                                <div class="flex items-center space-x-2">
                                    <span
                                        class="w-5 h-5 sm:w-6 sm:h-6 flex items-center justify-center rounded-full bg-amber-500 text-white text-[10px] sm:text-xs">
                                        {{ strtoupper(substr($item->user->name, 0, 1)) }}
                                    </span>
                                    <span class="truncate max-w-[80px] sm:max-w-[100px]">{{ $item->user->name }}</span>
                                </div>
                                <div class="flex items-center space-x-1">
                                    <i data-lucide="heart" class="w-3.5 sm:w-4 h-3.5 sm:h-4 text-rose-500"></i>
                                    <span>{{ $item->taps_count }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                @elseif ($tab === 'cultures')
                    <div class="bg-white dark:bg-stone-900 rounded-xl sm:rounded-2xl shadow-sm border border-stone-200 dark:border-stone-800 p-3 sm:p-4">
                        <h3 class="font-semibold text-stone-800 dark:text-stone-100 text-sm sm:text-base">{{ $item->name }}</h3>
                        <p class="text-xs sm:text-sm text-stone-500 dark:text-stone-400">{{ Str::limit($item->description, 80) }}</p>
                    </div>
                @elseif ($tab === 'people')
                    <div
                        class="bg-white dark:bg-stone-900 rounded-xl sm:rounded-2xl shadow-sm border border-stone-200 dark:border-stone-800 p-3 sm:p-4 flex flex-col justify-between h-full">
                        <!-- User Info Section -->
                        <a href="{{ route('profile.user', $item->id) }}"
                            class="flex items-center space-x-3 mb-3 hover:bg-stone-50 dark:hover:bg-stone-800 p-1 -m-1 rounded-lg transition">
                            <img src="{{ $item->profile_photo_url }}"
                                class="w-10 h-10 sm:w-12 sm:h-12 rounded-full object-cover border border-stone-200 dark:border-stone-800"
                                loading="lazy">
                            <div class="overflow-hidden flex-1">
                                <h3 class="font-semibold text-stone-800 truncate text-sm sm:text-base">{{ $item->name }}</h3>
                                <p class="text-xs sm:text-sm text-stone-500 truncate">{{ $item->bio ?? 'No bio available' }}</p>
                            </div>
                        </a>

                        <!-- Interactions Section -->
                        <div class="flex items-center justify-between pt-3 border-t border-stone-100">
                            @auth
                                <!-- Vibe (Tap) Button -->
                                <button onclick="toggleUserTap({{ $item->id }})" id="user-vibe-btn-{{ $item->id }}"
                                    class="flex items-center justify-center space-x-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold transition-all shadow-sm flex-1 mr-2
                                                    {{ auth()->user()->hasTapped($item)
                                        ? 'bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-500 hover:bg-amber-200'
                                        : 'bg-stone-100 dark:bg-stone-800 text-stone-700 dark:text-stone-300 hover:bg-stone-200' }}">
                                    <i data-lucide="{{ auth()->user()->hasTapped($item) ? 'thumbs-up' : 'thumbs-up' }}" id="user-tap-icon-{{ $item->id }}"
                                        class="w-4 h-4 {{ auth()->user()->hasTapped($item) ? 'fill-current' : '' }}"></i>
                                    <span id="user-tap-text-{{ $item->id }}">
                                        {{ auth()->user()->hasTapped($item) ? 'Vibed' : 'Vibe' }}
                                    </span>
                                </button>

                                <!-- Lock-in Button -->
                                @if(auth()->id() !== $item->id)
                                    <button onclick="toggleUserLockIn({{ $item->id }})" id="user-lockin-btn-{{ $item->id }}"
                                        class="flex items-center justify-center space-x-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold transition-all shadow-sm flex-1
                                                            {{ auth()->user()->hasLockedIn($item)
                                            ? 'bg-stone-100 dark:bg-stone-800 text-stone-700 dark:text-stone-300 hover:bg-red-50 dark:hover:bg-red-900/20 hover:text-red-600'
                                            : 'glass-orange text-white hover:scale-[1.02]' }}">
                                        <i data-lucide="{{ auth()->user()->hasLockedIn($item) ? 'user-check' : 'user-plus' }}"
                                            id="user-lockin-icon-{{ $item->id }}" class="w-4 h-4"></i>
                                        <span id="user-lockin-text-{{ $item->id }}">
                                            {{ auth()->user()->hasLockedIn($item) ? 'Locked In' : 'Lock In' }}
                                        </span>
                                    </button>
                                @endif
                            @else
                                <div class="text-xs text-stone-400">Sign in to connect</div>
                            @endauth
                        </div>
                    </div>
                @elseif ($tab === 'places')
                    <div class="bg-white dark:bg-stone-900 rounded-xl sm:rounded-2xl shadow-sm border border-stone-200 dark:border-stone-800 overflow-hidden">
                        <div class="p-3 sm:p-4">
                            <h3 class="font-semibold text-stone-800 dark:text-stone-100 text-sm sm:text-base">{{ $item->title }}</h3>
                            <p class="text-xs sm:text-sm text-stone-500 dark:text-stone-400">{{ $item->location }}</p>
                            <p class="text-[10px] sm:text-xs text-stone-400">By {{ $item->organizer->name }}</p>
                        </div>
                    </div>
                @endif
            @empty
                <p class="col-span-full text-center text-stone-500 text-sm">No results found.</p>
            @endforelse
        </div>

        <!-- Trending Stats -->
        <div class="mt-8 sm:mt-10">
            <h2 class="font-semibold text-stone-800 dark:text-stone-100 mb-3 sm:mb-4 text-center md:text-left text-sm sm:text-base">Trending
                This Week</h2>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 sm:gap-4">
                <div class="bg-rose-50 dark:bg-rose-900/10 text-rose-600 dark:text-rose-400 rounded-lg sm:rounded-xl p-3 sm:p-4 text-center">
                    <i data-lucide="heart" class="w-5 h-5 sm:w-6 sm:h-6 mx-auto mb-1 sm:mb-2"></i>
                    <p class="text-lg sm:text-xl font-bold">{{ number_format($trendingStats['taps_this_week']) }}</p>
                    <p class="text-xs sm:text-sm">TAPs this week</p>
                </div>
                <div class="bg-blue-50 dark:bg-blue-900/10 text-blue-600 dark:text-blue-400 rounded-lg sm:rounded-xl p-3 sm:p-4 text-center">
                    <i data-lucide="users" class="w-5 h-5 sm:w-6 sm:h-6 mx-auto mb-1 sm:mb-2"></i>
                    <p class="text-lg sm:text-xl font-bold">{{ number_format($trendingStats['new_connections']) }}</p>
                    <p class="text-xs sm:text-sm">My connections</p>
                </div>
                <div class="bg-green-50 dark:bg-green-900/10 text-green-600 dark:text-green-400 rounded-lg sm:rounded-xl p-3 sm:p-4 text-center">
                    <i data-lucide="globe" class="w-5 h-5 sm:w-6 sm:h-6 mx-auto mb-1 sm:mb-2"></i>
                    <p class="text-lg sm:text-xl font-bold">{{ number_format($trendingStats['cultures_featured']) }}</p>
                    <p class="text-xs sm:text-sm">Cultures featured</p>
                </div>
            </div>
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
                            const icon = document.getElementById(`user-tap-icon-${userId}`);
                            const count = document.getElementById(`user-tap-count-${userId}`);
                            const btn = icon.closest('button');

                            count.textContent = data.count;

                            if (data.tapped) {
                                btn.classList.add('bg-amber-100', 'text-amber-700', 'hover:bg-amber-200');
                                btn.classList.remove('bg-stone-100', 'text-stone-700', 'hover:bg-stone-200');
                                icon.classList.add('fill-current');
                                document.getElementById(`user-tap-text-${userId}`).textContent = 'Vibed';
                            } else {
                                btn.classList.remove('bg-amber-100', 'text-amber-700', 'hover:bg-amber-200');
                                btn.classList.add('bg-stone-100', 'text-stone-700', 'hover:bg-stone-200');
                                icon.classList.remove('fill-current');
                                document.getElementById(`user-tap-text-${userId}`).textContent = 'Vibe';
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
                        const btn = document.getElementById(`user-lockin-btn-${userId}`);
                        const icon = document.getElementById(`user-lockin-icon-${userId}`);
                        const text = document.getElementById(`user-lockin-text-${userId}`);

                        if (data.lockedIn) {
                            btn.className = "flex items-center justify-center space-x-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold transition-all shadow-sm flex-1 bg-stone-100 text-stone-700 hover:bg-red-50 hover:text-red-600";
                            icon.setAttribute('data-lucide', 'user-check');
                            text.textContent = 'Locked In';
                        } else {
                            btn.className = "flex items-center justify-center space-x-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold transition-all shadow-sm flex-1 glass-orange text-white hover:scale-[1.02]";
                            icon.setAttribute('data-lucide', 'user-plus');
                            text.textContent = 'Lock In';
                        }
                        lucide.createIcons();
                    })
                    .catch(error => console.error('Error toggling lock-in:', error));
            }
        @endauth
    </script>
@endpush