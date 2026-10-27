@extends('layouts.app')

@section('title', 'Cultural Hub - Explore Global Heritage')

@section('content')
    <div class="max-w-[1600px] mx-auto px-4 sm:px-6 lg:px-10 py-6 sm:py-10 relative">
        <!-- Background Decorative Elements -->
        <div class="fixed inset-0 pointer-events-none z-0 opacity-30 dark:opacity-20">
            <div class="absolute top-[10%] left-[5%] w-96 h-96 bg-amber-500/10 rounded-full blur-[120px]"></div>
            <div class="absolute bottom-[10%] right-[5%] w-[30rem] h-[30rem] bg-orange-600/10 rounded-full blur-[150px]"></div>
        </div>

        <div
            class="mb-6 sm:mb-8 flex flex-col sm:flex-row sm:items-center sm:justify-between space-y-4 sm:space-y-0 text-center sm:text-left">
            <div>
                <h2 class="text-2xl sm:text-3xl font-bold text-stone-800 dark:text-white mb-2 sm:mb-3">
                    Cultural Hub
                </h2>
                <p class="text-sm sm:text-base text-stone-600 dark:text-stone-400 leading-relaxed max-w-2xl">
                    Discover, preserve, and celebrate the rich tapestry of global cultures.
                    Every tradition has a story, every heritage deserves preservation.
                </p>
            </div>
            <div class="flex-shrink-0 w-full sm:w-80">
                <form action="{{ route('cultural-hub.index') }}" method="GET" class="relative group">
                    @if($category !== 'all')
                        <input type="hidden" name="category" value="{{ $category }}">
                    @endif
                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                        <i data-lucide="search" class="w-4 h-4 text-stone-400 group-focus-within:text-amber-500 transition-colors"></i>
                    </div>
                    <input type="text" 
                           name="search" 
                           value="{{ request('search') }}" 
                           placeholder="Search stories..." 
                           class="w-full pl-11 pr-4 py-3 bg-white/50 dark:bg-stone-900/50 backdrop-blur-md border border-stone-200 dark:border-stone-800 rounded-2xl text-sm font-bold placeholder:text-stone-400 focus:ring-4 focus:ring-amber-500/10 focus:border-amber-500 transition-all">
                    <button type="submit" class="hidden"></button>
                </form>
            </div>
            @if(session('success'))
                <div class="mb-6 p-4 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 text-green-700 dark:text-green-400 rounded-xl flex items-center space-x-3">
                    <i data-lucide="check-circle" class="w-5 h-5 transition-colors"></i>
                    <p class="text-sm font-medium">{{ session('success') }}</p>
                </div>
            @endif
        </div>

        <!-- Living Heritage Preservation Banner -->
        <div class="mb-10 bg-white dark:bg-stone-900 border border-amber-200/80 dark:border-amber-900/50 rounded-3xl p-6 sm:p-8 shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-6 relative overflow-hidden">
            <div class="space-y-2 max-w-2xl">
                <div class="inline-flex items-center space-x-2 px-3 py-1 rounded-full bg-amber-50 dark:bg-amber-950/60 border border-amber-200 dark:border-amber-800/80 text-[10px] font-bold text-amber-700 dark:text-amber-400 uppercase tracking-widest">
                    <i data-lucide="sparkles" class="w-3 h-3"></i>
                    <span>Cultural Preservation Intelligence</span>
                </div>
                <h3 class="text-xl sm:text-2xl font-extrabold text-stone-900 dark:text-stone-100 tracking-tight">
                    Living Heritage Capture
                </h3>
                <p class="text-xs sm:text-sm text-stone-600 dark:text-stone-400 leading-relaxed">
                    Preserve oral histories, sacred folktales, and endangered knowledge. Assist your memory with <strong>Muse Voice Transcribe</strong>, <strong>Muse Spark</strong>, <strong>Muse Wonder</strong>, and <strong>SAM 3.1</strong>.
                </p>
            </div>
            <div class="flex flex-wrap items-center gap-2.5 sm:gap-3 w-full sm:w-auto flex-shrink-0">
                <a href="{{ route('cultural-hub.living-heritage.create') }}"
                   class="w-full sm:w-auto inline-flex items-center justify-center space-x-2 px-5 py-3 bg-amber-500 hover:bg-amber-600 text-white font-bold rounded-xl text-xs uppercase tracking-wider transition-colors shadow-sm">
                    <i data-lucide="mic" class="w-4 h-4"></i>
                    <span>Start Capture</span>
                </a>
                <a href="{{ route('cultural-hub.living-heritage.compare') }}"
                   class="flex-1 sm:flex-initial inline-flex items-center justify-center space-x-2 px-4 sm:px-5 py-3 bg-stone-100 dark:bg-stone-800 hover:bg-stone-200 dark:hover:bg-stone-700 text-stone-800 dark:text-stone-200 font-bold rounded-xl text-xs uppercase tracking-wider transition-colors border border-stone-200 dark:border-stone-700 text-center">
                    <i data-lucide="git-compare" class="w-4 h-4"></i>
                    <span>Model Lab</span>
                </a>
                <a href="{{ route('cultural-hub.create') }}"
                   class="flex-1 sm:flex-initial inline-flex items-center justify-center space-x-2 px-4 py-3 bg-white dark:bg-stone-900 hover:bg-stone-50 dark:hover:bg-stone-800 text-stone-600 dark:text-stone-400 hover:text-stone-900 dark:hover:text-stone-100 font-bold rounded-xl text-xs uppercase tracking-wider transition-colors border border-stone-200 dark:border-stone-800 text-center">
                    <span>Manual Entry</span>
                </a>
            </div>
        </div>

        <!-- Main Discovery Container -->
        <div class="lg:grid lg:grid-cols-12 lg:gap-12 items-start" x-data="{ mobileFiltersOpen: false }">
            
            <!-- Main Content Area (Column 1-9 on LARGE) -->
            <div class="lg:col-span-9 space-y-12 order-last lg:order-first">
                
                <!-- Mobile Dropdown Toolbar (Premium Glassmorphism) -->
                <div class="lg:hidden flex items-center justify-between backdrop-blur-xl bg-white/80 dark:bg-stone-900/80 p-4 sm:p-5 rounded-2xl sm:rounded-[2rem] border border-stone-200/80 dark:border-stone-800/80 shadow-md relative z-40 mb-6">
                    <div class="flex items-center space-x-3 sm:space-x-4 min-w-0">
                        <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-2xl bg-orange-500/10 flex items-center justify-center border border-orange-500/20 flex-shrink-0">
                            <i data-lucide="compass" class="w-5 h-5 sm:w-6 sm:h-6 text-amber-600 animate-spin-slow"></i>
                        </div>
                        <div class="min-w-0">
                            <p class="text-[10px] font-black text-amber-600 uppercase tracking-[0.2em] mb-0.5">Exploring</p>
                            <p class="text-sm sm:text-base font-black text-stone-900 dark:text-white uppercase tracking-tight truncate">{{ $categories[(string)$category] ?? 'Global Heritage' }}</p>
                        </div>
                    </div>

                    <!-- Three-Dot Trigger -->
                    <div class="relative flex-shrink-0">
                        <button @click="mobileFiltersOpen = !mobileFiltersOpen" 
                                class="w-10 h-10 sm:w-12 sm:h-12 flex items-center justify-center rounded-xl sm:rounded-[1.25rem] bg-stone-50 dark:bg-stone-800 hover:bg-amber-500 hover:text-white transition-all duration-300 shadow-inner">
                            <i data-lucide="more-vertical" class="w-5 h-5 sm:w-6 sm:h-6"></i>
                        </button>

                        <!-- Mobile Menu Dropdown (Cinematic Transition) -->
                        <div x-show="mobileFiltersOpen" 
                             @click.away="mobileFiltersOpen = false"
                             x-transition:enter="transition ease-out duration-300"
                             x-transition:enter-start="opacity-0 scale-90 -translate-y-4"
                             x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                             class="absolute right-0 mt-3 w-64 max-w-[calc(100vw-2.5rem)] backdrop-blur-2xl bg-white/95 dark:bg-stone-900/95 rounded-2xl sm:rounded-3xl shadow-[0_20px_50px_rgba(0,0,0,0.2)] border border-stone-200 dark:border-stone-800 overflow-hidden py-2 z-50"
                             x-cloak>
                            @foreach($categories as $key => $label)
                                <a href="{{ route('cultural-hub.index', ['category' => $key]) }}"
                                   class="flex items-center justify-between px-5 py-3 hover:bg-amber-500/10 transition-colors group">
                                    <div class="flex items-center space-x-3">
                                        <div class="w-2 h-2 rounded-full {{ $category === $key ? 'bg-amber-500 shadow-[0_0_12px_rgba(245,158,11,0.8)]' : 'bg-stone-200 dark:bg-stone-700' }}"></div>
                                        <span class="text-xs font-black uppercase tracking-wider {{ $category === $key ? 'text-amber-600' : 'text-stone-600 dark:text-stone-400' }}">
                                            {{ $label }}
                                        </span>
                                    </div>
                                    @if($category === $key)
                                        <i data-lucide="check" class="w-4 h-4 text-amber-500"></i>
                                    @endif
                                </a>
                            @endforeach
                        </div>
                    </div>
                </div>

                <!-- Cultures Grid (Refined Tile Style) -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 mb-20">
                    @forelse($cultures as $culture)
                        <div class="border border-stone-200/90 dark:border-stone-800 bg-white dark:bg-[#131218] rounded-3xl overflow-hidden shadow-sm hover:shadow-xl hover:border-amber-500/40 transition-all duration-300 flex flex-col h-full group">
                            
                            <!-- Image Section with Gradient & Badges -->
                            <div class="h-48 relative overflow-hidden bg-stone-100 dark:bg-stone-800/50">
                                @if($culture->image)
                                    <img src="{{ Storage::url($culture->image) }}" 
                                         alt="{{ $culture->name }}"
                                         class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-105"
                                         loading="lazy">
                                @else
                                    <div class="w-full h-full flex items-center justify-center bg-stone-100 dark:bg-stone-900">
                                        <i data-lucide="landmark" class="w-12 h-12 text-stone-300 dark:text-stone-700"></i>
                                    </div>
                                @endif
                                <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/20 to-transparent"></div>
                                
                                <!-- Category & Audio Badges -->
                                <div class="absolute top-3 left-3 right-3 flex items-center justify-between gap-2">
                                    <span class="px-2.5 py-1 bg-amber-500 text-black text-[9px] font-black uppercase tracking-widest rounded-lg shadow-sm">
                                        {{ $culture->category }}
                                    </span>

                                    @if($culture->audio_path || $culture->audio_url)
                                        <span class="px-2 py-0.5 bg-black/60 backdrop-blur-md border border-white/20 rounded-md text-[9px] font-bold text-amber-300 flex items-center gap-1 shadow-sm">
                                            <i data-lucide="headphones" class="w-3 h-3"></i>
                                            <span>Oral Audio</span>
                                        </span>
                                    @endif
                                </div>

                                <!-- Region on Image Bottom -->
                                <div class="absolute bottom-3 left-3 right-3 flex items-center justify-between text-xs text-white">
                                    <span class="flex items-center gap-1 font-bold text-amber-300 text-[11px] uppercase tracking-wider truncate">
                                        <i data-lucide="map-pin" class="w-3.5 h-3.5"></i>
                                        {{ $culture->region }}
                                    </span>
                                    @if($culture->language)
                                        <span class="text-[10px] text-stone-300 font-mono truncate max-w-[100px]">
                                            {{ $culture->language }}
                                        </span>
                                    @endif
                                </div>
                            </div>

                            <!-- Content Info Body -->
                            <div class="p-5 sm:p-6 flex-1 flex flex-col justify-between space-y-4">
                                <div class="space-y-2">
                                    <h3 class="font-bold text-stone-900 dark:text-stone-100 text-lg leading-snug group-hover:text-amber-600 dark:group-hover:text-amber-400 transition line-clamp-1">
                                        {{ $culture->name }}
                                    </h3>
                                    
                                    <p class="text-stone-600 dark:text-stone-400 text-xs sm:text-sm line-clamp-2 leading-relaxed">
                                        {{ $culture->significance ?? $culture->description }}
                                    </p>
                                </div>

                                <!-- Educational Metrics Strip -->
                                <div class="pt-3 border-t border-stone-100 dark:border-stone-800/80 flex items-center justify-between text-[11px] font-semibold text-stone-500 dark:text-stone-400">
                                    <span class="flex items-center gap-1" title="Community Lock-ins">
                                        <i data-lucide="lock" class="w-3.5 h-3.5 text-amber-500"></i>
                                        <span>{{ $culture->locked_in_count }}</span>
                                    </span>
                                    <span class="flex items-center gap-1" title="Resonance Pulses">
                                        <i data-lucide="zap" class="w-3.5 h-3.5 text-orange-500"></i>
                                        <span>{{ $culture->resonance_count }}</span>
                                    </span>
                                    <span class="flex items-center gap-1 text-[10px] text-stone-400">
                                        <i data-lucide="calendar" class="w-3 h-3"></i>
                                        {{ $culture->created_at->format('M Y') }}
                                    </span>
                                </div>

                                <!-- Primary Read Action -->
                                <a href="{{ route('cultural-hub.show', $culture->id) }}" 
                                   class="w-full py-2.5 px-4 rounded-xl bg-stone-100 dark:bg-stone-800/90 hover:bg-amber-500 hover:text-white dark:hover:bg-amber-500 dark:hover:text-white text-stone-800 dark:text-stone-200 text-xs font-bold uppercase tracking-wider text-center transition-all flex items-center justify-center gap-2 group/btn shadow-sm">
                                    <span>Explore Cultural Dossier</span>
                                    <i data-lucide="arrow-right" class="w-3.5 h-3.5 transition-transform group-hover/btn:translate-x-1"></i>
                                </a>
                            </div>
                        </div>
                    @empty
                        <!-- Empty state remains same -->
                        <div class="col-span-full text-center py-24 backdrop-blur-md bg-stone-50/50 dark:bg-stone-900/10 rounded-[4rem] border-4 border-dashed border-stone-200 dark:border-stone-800">
                            <i data-lucide="globe" class="w-24 h-24 text-amber-500/10 mx-auto mb-8"></i>
                            <h3 class="text-2xl font-black text-stone-800 dark:text-white uppercase tracking-tighter mb-4">The Archive lies Silent</h3>
                            <p class="text-stone-500 text-base max-w-sm mx-auto mb-10 font-bold">No cultural stories have been enshrined in this category yet. Be the first to share your legacy.</p>
                            <a href="{{ route('cultural-hub.create') }}" class="inline-flex items-center space-x-3 px-10 py-5 glass-orange text-white rounded-[2rem] font-black uppercase tracking-widest text-xs hover:scale-105 transition-transform shadow-xl">
                                <i data-lucide="plus" class="w-5 h-5"></i>
                                <span>ENSHRINE STORY</span>
                            </a>
                        </div>
                    @endforelse
                </div>

                <!-- Pagination (Premium Aesthetic) -->
                @if($cultures->hasPages())
                    <div class="pt-12 border-t border-stone-100 dark:border-stone-800">
                        <!-- Desktop Pagination -->
                        <div class="hidden sm:block">
                            {{ $cultures->links() }}
                        </div>
                        
                        <!-- Mobile Explore More Button -->
                        <div class="sm:hidden flex justify-center">
                            @if($cultures->hasMorePages())
                                <a href="{{ $cultures->nextPageUrl() }}" 
                                   class="inline-flex items-center space-x-3 px-8 py-4 glass-orange text-white rounded-full font-black uppercase tracking-widest text-[10px] shadow-lg hover:scale-105 active:scale-95 transition-all">
                                    <span>Explore More Traditions</span>
                                    <i data-lucide="chevron-down" class="w-4 h-4"></i>
                                </a>
                            @else
                                <div class="px-8 py-4 bg-stone-100 dark:bg-stone-800 rounded-full text-stone-400 text-[10px] font-black uppercase tracking-widest">
                                    End of the Archive
                                </div>
                            @endif
                        </div>
                    </div>
                @endif
            </div>

            <!-- Desktop Sidebar (Column 10-12) -->
            <aside class="hidden lg:block lg:col-span-3 sticky top-28 space-y-10 lg:order-last">
                <div class="backdrop-blur-2xl bg-white/70 dark:bg-stone-900/70 rounded-[3rem] border border-white/40 dark:border-stone-800/50 p-10 shadow-2xl shadow-amber-500/5">
                    <!-- Premium Search Filter -->
                    <form action="{{ route('cultural-hub.index') }}" method="GET" class="mb-10 p-1.5 bg-stone-100/50 dark:bg-stone-800/50 rounded-2xl flex items-center border border-stone-200 dark:border-stone-700 focus-within:border-amber-400 focus-within:ring-8 focus-within:ring-amber-400/5 transition-all group">
                        @if($category !== 'all')
                            <input type="hidden" name="category" value="{{ $category }}">
                        @endif
                        <div class="w-12 h-12 flex items-center justify-center text-stone-400 group-focus-within:text-amber-500 transition-colors">
                            <i data-lucide="search" class="w-5 h-5"></i>
                        </div>
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="SEARCH LEGENDS..." class="bg-transparent border-none focus:ring-0 text-[10px] font-black text-stone-800 dark:text-stone-200 w-full placeholder:text-stone-400 uppercase tracking-[0.2em]">
                        <button type="submit" class="hidden"></button>
                    </form>

                    <div class="flex items-center space-x-3 text-amber-600 mb-8 uppercase tracking-[0.3em] text-[10px] font-black">
                        <div class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></div>
                        <span>Discovery Vault</span>
                    </div>

                    <div class="space-y-3">
                        @foreach($categories as $key => $label)
                            <a href="{{ route('cultural-hub.index', ['category' => $key]) }}"
                                class="flex items-center justify-between group px-6 py-4 rounded-[1.5rem] transition-all duration-500 {{ $category === $key ? 'bg-stone-900 dark:bg-white text-white dark:text-stone-900 shadow-2xl scale-[1.05] z-10' : 'text-stone-500 dark:text-stone-400 hover:bg-white dark:hover:bg-stone-800 hover:shadow-xl hover:scale-[1.02]' }}">
                                <div class="flex items-center space-x-4">
                                    @php
                                        $icon = match($key) {
                                            'all' => 'globe-2',
                                            'festivals' => 'party-popper',
                                            'traditions' => 'scroll',
                                            'music' => 'music-4',
                                            'heritage' => 'landmark',
                                            'crafts' => 'hammer',
                                            'language' => 'languages',
                                            default => 'sparkles'
                                        };
                                    @endphp
                                    <i data-lucide="{{ $icon }}" class="w-5 h-5 {{ $category === $key ? 'text-amber-500' : 'text-stone-400 group-hover:text-amber-500' }} transition-colors"></i>
                                    <span class="text-[11px] font-black uppercase tracking-tight">{{ $label }}</span>
                                </div>
                                <i data-lucide="arrow-right" class="w-4 h-4 opacity-0 group-hover:opacity-100 transition-all transform -translate-x-2 group-hover:translate-x-0"></i>
                            </a>
                        @endforeach
                    </div>
                </div>

                <!-- Premium Sidebar CTA -->
                <div class="glass-orange rounded-[3rem] p-10 text-white shadow-xl group overflow-hidden relative">
                    <div class="absolute inset-0 bg-[url('https://www.transparenttextures.com/patterns/carbon-fibre.png')] opacity-10"></div>
                    <div class="relative z-10">
                        <i data-lucide="crown" class="w-12 h-12 mb-6 text-amber-200 opacity-60 group-hover:rotate-12 transition-transform duration-500"></i>
                        <h3 class="font-black text-2xl mb-3 tracking-tighter leading-tight">Become a Story Guardian</h3>
                        <p class="text-[10px] font-bold opacity-80 leading-relaxed mb-10 uppercase tracking-widest text-amber-100">Every ritual, every craft, every song counts.</p>
                        <a href="{{ route('cultural-hub.create') }}" class="w-full inline-flex items-center justify-center py-5 bg-white text-amber-600 rounded-2xl text-[11px] font-black uppercase tracking-[0.2em] shadow-xl hover:bg-stone-900 hover:text-white transition-all duration-300 transform group-hover:scale-105 active:scale-95">
                            ENSHRINE NOW
                        </a>
                    </div>
                </div>
            </aside>
        </div>

        @guest
            <div class="bg-stone-900 dark:bg-white rounded-[4rem] p-16 mt-24 text-center relative overflow-hidden shadow-[0_50px_100px_rgba(0,0,0,0.3)] dark:shadow-[0_50px_100px_rgba(0,0,0,0.1)]">
                <div class="absolute inset-0 opacity-20 pointer-events-none">
                    <div class="absolute top-0 right-0 w-[500px] h-[500px] bg-amber-500 rounded-full blur-[150px] translate-x-1/2 -translate-y-1/2 opacity-30 dark:opacity-20"></div>
                    <div class="absolute bottom-0 left-0 w-[500px] h-[500px] bg-orange-600 rounded-full blur-[150px] -translate-x-1/2 translate-y-1/2 opacity-30 dark:opacity-20"></div>
                </div>
                
                <div class="relative z-10">
                    <div class="w-20 h-20 bg-amber-500 rounded-[2rem] flex items-center justify-center mx-auto mb-10 shadow-2xl rotate-12">
                        <i data-lucide="shield-check" class="w-10 h-10 text-white"></i>
                    </div>
                    <h3 class="text-4xl sm:text-5xl font-black text-white dark:text-stone-900 mb-6 tracking-tighter leading-none">The Human Legacy <br> Needs You</h3>
                    <p class="text-stone-400 dark:text-stone-500 text-lg max-w-2xl mx-auto mb-12 font-medium leading-relaxed">Join thousands of guardians in the global initiative to preserve our shared traditions. Unlock your potential to contribute to Kulturegram.</p>
                    <a href="{{ route('register') }}"
                        class="inline-flex items-center space-x-4 px-12 py-6 bg-amber-500 text-white rounded-[2rem] font-black uppercase tracking-[0.3em] text-[10px] hover:scale-110 active:scale-95 transition-all shadow-2xl shadow-amber-500/50">
                        <span>INITIATE MEMBERSHIP</span>
                        <i data-lucide="arrow-right" class="w-4 h-4"></i>
                    </a>
                </div>
            </div>
        @endguest    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            lucide.createIcons();
        });

        function toggleCultureLockin(id) {
            @guest
                window.location.href = "{{ route('login') }}";
                return;
            @endguest

            fetch(`/cultural-hub/${id}/lock-in`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.lockedIn !== undefined) {
                    const icon = document.getElementById(`culture-lock-icon-${id}`);
                    const count = document.getElementById(`culture-lock-count-${id}`);
                    
                    if (data.lockedIn) {
                        icon.classList.add('text-amber-500');
                        icon.classList.remove('text-stone-400');
                        icon.setAttribute('data-lucide', 'check-circle');
                        count.classList.add('text-amber-600');
                        count.classList.remove('text-stone-400');
                    } else {
                        icon.classList.remove('text-amber-500');
                        icon.classList.add('text-stone-400');
                        icon.setAttribute('data-lucide', 'lock');
                        count.classList.remove('text-amber-600');
                        count.classList.add('text-stone-400');
                    }
                    count.textContent = data.count;
                    lucide.createIcons();
                }
            });
        }

        function toggleCultureResonance(id) {
            @guest
                window.location.href = "{{ route('login') }}";
                return;
            @endguest

            fetch(`/cultural-hub/${id}/resonance`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.resonated !== undefined) {
                    const icon = document.getElementById(`culture-vibe-icon-${id}`);
                    const count = document.getElementById(`culture-vibe-count-${id}`);
                    
                    if (data.resonated) {
                        icon.classList.add('text-orange-500', 'fill-orange-500');
                        icon.classList.remove('text-stone-400');
                        count.classList.add('text-orange-600');
                        count.classList.remove('text-stone-400');
                    } else {
                        icon.classList.remove('text-orange-500', 'fill-orange-500');
                        icon.classList.add('text-stone-400');
                        count.classList.remove('text-orange-600');
                        count.classList.add('text-stone-400');
                    }
                    count.textContent = data.count;
                }
            });
        }
    </script>
@endpush