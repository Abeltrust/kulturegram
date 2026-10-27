@extends('layouts.app')

@section('title', 'Discover - Kulturegram')

@section('content')
    <div class="max-w-6xl mx-auto px-3 sm:px-4 py-4 sm:py-6">
        <!-- Header -->
        <div class="mb-4 sm:mb-6 text-center md:text-left flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <h1 class="text-xl sm:text-2xl md:text-3xl font-bold text-stone-800 dark:text-white">Discover</h1>
                <p class="text-stone-50 dark:text-stone-400 text-xs sm:text-sm md:text-base">
                    Explore stories, cultures, people, and places from around the world.
                </p>
            </div>
            @auth
            <a href="{{ route('cultural-discovery.recommendations') }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-amber-500/10 hover:bg-amber-500/20 text-amber-400 border border-amber-500/30 text-xs font-bold transition self-center md:self-auto">
                <i data-lucide="compass" class="w-4 h-4"></i>
                <span>Cultural Circles & Opportunities</span>
            </a>
            @endauth
        </div>

        @auth
            @include('components.cultural-connections-card')

        @if(isset($opportunities) && !empty($opportunities['opportunities']))
            <!-- AI Cultural Opportunity Engine -->
            <div class="mb-6 sm:mb-8 bg-gradient-to-br from-stone-900/90 to-stone-950 border border-amber-500/30 rounded-2xl p-4 sm:p-5 shadow-xl relative overflow-hidden"
                 x-data="{ showPreferences: false, dismissed: [] }">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 mb-4">
                    <div class="flex items-center gap-2">
                        <span class="w-7 h-7 rounded-lg bg-amber-500/20 border border-amber-500/30 flex items-center justify-center text-amber-400">
                            <i data-lucide="sparkles" class="w-4 h-4"></i>
                        </span>
                        <div>
                            <h2 class="text-sm sm:text-base font-black text-stone-100 flex items-center gap-2">
                                Cultural Opportunity Engine
                                <span class="text-[10px] uppercase font-bold px-2 py-0.5 rounded-full bg-amber-500/20 text-amber-400 border border-amber-500/30">Personalized</span>
                            </h2>
                            <p class="text-[11px] text-stone-400">Based on your voluntarily declared cultural background & interests</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <button @click="showPreferences = !showPreferences" 
                            class="text-xs text-amber-400 hover:text-amber-300 font-semibold flex items-center gap-1 px-2.5 py-1 rounded-lg bg-stone-800 border border-stone-700 transition">
                            <i data-lucide="sliders" class="w-3.5 h-3.5"></i>
                            <span>Preferences</span>
                        </button>
                    </div>
                </div>

                {{-- Preferences Inline Drawer --}}
                <div x-show="showPreferences" x-cloak class="p-3.5 mb-4 rounded-xl bg-stone-950 border border-stone-800 text-xs text-stone-300 space-y-2">
                    <div class="font-bold text-stone-200">Recommendation Transparency & Opt-Out Controls</div>
                    <p class="text-[11px] text-stone-400">You control how Kulturegram matches opportunities. Private conversations are never analyzed for recommendations, and collaborators are only suggested if they explicitly opt in.</p>
                    <div class="flex flex-wrap gap-2 pt-1">
                        <form action="{{ route('cultural-discovery.toggle-opt-in') }}" method="POST" class="inline">
                            @csrf
                            <button type="submit" class="px-3 py-1 rounded-lg bg-stone-800 hover:bg-stone-700 text-stone-300 text-[11px] font-semibold border border-stone-700 transition">
                                Toggle Personalization
                            </button>
                        </form>
                        <a href="{{ route('profile.edit') }}" class="px-3 py-1 rounded-lg bg-stone-800 hover:bg-stone-700 text-stone-300 text-[11px] font-semibold border border-stone-700 transition">
                            Edit Cultural Interests
                        </a>
                    </div>
                </div>

                <!-- Opportunities Horizontal Scroll Cards -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3.5">
                    @foreach($opportunities['opportunities'] as $opp)
                        <div x-show="!dismissed.includes('{{ $opp['type'] }}_{{ $opp['target_id'] }}')" 
                             class="bg-stone-900/90 border border-stone-800 hover:border-amber-500/40 rounded-xl p-3.5 flex flex-col justify-between transition group">
                            <div>
                                <div class="flex items-start justify-between gap-2 mb-2">
                                    <span class="text-[10px] font-black uppercase px-2 py-0.5 rounded-full
                                        @if($opp['type'] === 'preservation_project') bg-amber-500/20 text-amber-400 border border-amber-500/30
                                        @elseif($opp['type'] === 'community') bg-emerald-500/20 text-emerald-400 border border-emerald-500/30
                                        @elseif($opp['type'] === 'event') bg-sky-500/20 text-sky-400 border border-sky-500/30
                                        @else bg-purple-500/20 text-purple-400 border border-purple-500/30 @endif">
                                        {{ str_replace('_', ' ', $opp['type']) }}
                                    </span>
                                    <button @click="fetch('{{ route('cultural-opportunities.dismiss') }}', {
                                                method: 'POST',
                                                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                                                body: JSON.stringify({ type: '{{ $opp['type'] }}', target_id: {{ $opp['target_id'] }} })
                                            }); dismissed.push('{{ $opp['type'] }}_{{ $opp['target_id'] }}')" 
                                            class="text-stone-500 hover:text-stone-300 transition" title="Dismiss this recommendation">
                                        <i data-lucide="x" class="w-3.5 h-3.5"></i>
                                    </button>
                                </div>

                                <a href="{{ $opp['url'] }}" class="block font-bold text-stone-100 group-hover:text-amber-400 text-sm mb-1 transition line-clamp-1">
                                    {{ $opp['title'] }}
                                </a>
                                <p class="text-[11px] text-stone-400 mb-2">{{ $opp['subtitle'] }}</p>

                                <div class="p-2 rounded-lg bg-stone-950/70 border border-stone-800/80 text-[10px] text-stone-300 mb-3 flex items-start gap-1.5">
                                    <i data-lucide="info" class="w-3.5 h-3.5 shrink-0 text-amber-400/80 mt-0.5"></i>
                                    <span>{{ $opp['explanation'] }}</span>
                                </div>
                            </div>

                            <a href="{{ $opp['url'] }}" class="py-1.5 text-center rounded-lg bg-stone-800 hover:bg-amber-600 text-stone-200 hover:text-white text-xs font-bold transition flex items-center justify-center gap-1">
                                <span>Explore Opportunity</span>
                                <i data-lucide="chevron-right" class="w-3 h-3"></i>
                            </a>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
        @endauth

        <!-- Search & Dynamic Tabs Container -->
        <div x-data="discoverTabs('{{ $tab }}', '{{ addslashes($search ?? '') }}')" x-init="init()" class="space-y-4 sm:space-y-6">
            <!-- Search -->
            <form method="GET" action="{{ route('discover.index') }}" @submit.prevent="handleSearch()" class="mb-4 sm:mb-6">
                <div class="relative">
                    <input type="hidden" name="tab" :value="currentTab">
                    <input type="text" name="search" x-model="searchQuery"
                        class="w-full rounded-xl border border-stone-300 dark:border-stone-700 bg-white dark:bg-stone-900 pl-10 sm:pl-12 pr-4 py-2 sm:py-3 placeholder-stone-400 dark:placeholder-stone-500 text-stone-700 dark:text-stone-200 focus:ring-amber-500 focus:border-amber-500 text-sm sm:text-base outline-none"
                        placeholder="Search...">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-stone-400">
                        <i data-lucide="search" class="w-4 sm:w-5 h-4 sm:h-5"></i>
                    </span>
                    <button type="submit" class="absolute inset-y-0 right-0 px-3 sm:px-4 text-stone-500 hover:text-amber-600 transition">
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
                    <a href="{{ route('discover.index', ['tab' => $key, 'search' => $search]) }}"
                       @click.prevent="selectTab('{{ $key }}')"
                       :class="currentTab === '{{ $key }}'
                            ? 'glass-orange text-white shadow-sm'
                            : 'bg-white dark:bg-stone-800 text-stone-600 dark:text-stone-300 border border-stone-200 dark:border-stone-700 hover:border-stone-300 dark:hover:border-stone-600'"
                       class="px-3 sm:px-4 py-1.5 sm:py-2 rounded-xl text-xs sm:text-sm font-semibold transition-all duration-200 cursor-pointer flex items-center gap-1.5 select-none">
                        <span>{{ $label }}</span>
                        <span x-show="loading && currentTab === '{{ $key }}'" class="w-3 h-3 border-2 border-white border-t-transparent rounded-full animate-spin"></span>
                    </a>
                @endforeach
            </div>

            <!-- Data Grid with Smooth Transition -->
            <div id="discover-grid-container" class="relative min-h-[250px] transition-opacity duration-200" :class="loading ? 'opacity-40 pointer-events-none' : 'opacity-100'">
                <div id="discover-grid-content">
                    @include('discover.partials.grid', ['data' => $data, 'tab' => $tab])
                </div>
            </div>
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

        document.addEventListener('alpine:init', () => {
            Alpine.data('discoverTabs', (initialTab, initialSearch) => ({
                currentTab: initialTab || 'stories',
                searchQuery: initialSearch || '',
                loading: false,
                cache: {},

                init() {
                    const initialGrid = document.getElementById('discover-grid-content');
                    if (initialGrid) {
                        this.cache[this.currentTab + ':' + this.searchQuery] = initialGrid.innerHTML;
                    }

                    window.addEventListener('popstate', (e) => {
                        const urlParams = new URLSearchParams(window.location.search);
                        const tab = urlParams.get('tab') || 'stories';
                        const search = urlParams.get('search') || '';
                        this.searchQuery = search;
                        this.selectTab(tab, false);
                    });
                },

                async selectTab(tabKey, pushHistory = true) {
                    if (this.currentTab === tabKey && !this.loading && pushHistory) {
                        return;
                    }

                    this.currentTab = tabKey;
                    const cacheKey = tabKey + ':' + this.searchQuery;

                    const url = new URL('{{ route("discover.index", [], false) }}', window.location.origin);
                    url.searchParams.set('tab', tabKey);
                    if (this.searchQuery) {
                        url.searchParams.set('search', this.searchQuery);
                    }

                    if (pushHistory) {
                        window.history.pushState({ tab: tabKey, search: this.searchQuery }, '', url.toString());
                    }

                    if (this.cache[cacheKey]) {
                        const container = document.getElementById('discover-grid-container');
                        if (container) {
                            container.innerHTML = '<div id="discover-grid-content">' + this.cache[cacheKey] + '</div>';
                            this.$nextTick(() => {
                                if (window.lucide) window.lucide.createIcons();
                            });
                        }
                        return;
                    }

                    this.loading = true;
                    try {
                        const res = await fetch(url.toString(), {
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest',
                                'Accept': 'application/json',
                                'X-Discover-Tab': tabKey
                            }
                        });
                        const data = await res.json();
                        if (data && data.html) {
                            this.cache[cacheKey] = data.html;
                            const container = document.getElementById('discover-grid-container');
                            if (container) {
                                container.innerHTML = '<div id="discover-grid-content">' + data.html + '</div>';
                            }
                        }
                    } catch (err) {
                        console.error('Failed to load tab:', err);
                        window.location.href = url.toString();
                    } finally {
                        this.loading = false;
                        this.$nextTick(() => {
                            if (window.lucide) window.lucide.createIcons();
                        });
                    }
                },

                async handleSearch() {
                    this.cache = {};
                    await this.selectTab(this.currentTab, true);
                }
            }));
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