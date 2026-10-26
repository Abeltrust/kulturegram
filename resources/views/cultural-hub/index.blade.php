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

        <!-- Main Discovery Container -->
        <div class="lg:grid lg:grid-cols-12 lg:gap-12 items-start" x-data="{ mobileFiltersOpen: false }">
            
            <!-- Main Content Area (Column 1-9 on LARGE) -->
            <div class="lg:col-span-9 space-y-12 order-last lg:order-first">
                
                <!-- Mobile Dropdown Toolbar (Premium Glassmorphism) -->
                <div class="lg:hidden flex items-center justify-between backdrop-blur-xl bg-white/80 dark:bg-stone-900/80 p-5 rounded-[2rem] border border-white/20 dark:border-stone-800/50 shadow-2xl relative z-40 mb-8">
                    <div class="flex items-center space-x-4">
                        <div class="w-12 h-12 rounded-2xl bg-orange-500/10 flex items-center justify-center border border-orange-500/20">
                            <i data-lucide="compass" class="w-6 h-6 text-amber-600 animate-spin-slow"></i>
                        </div>
                        <div>
                            <p class="text-[10px] font-black text-amber-600 uppercase tracking-[0.2em] mb-0.5">Exploring</p>
                            <p class="text-base font-black text-stone-900 dark:text-white uppercase tracking-tight">{{ $categories[(string)$category] ?? 'Global Heritage' }}</p>
                        </div>
                    </div>

                    <!-- Three-Dot Trigger -->
                    <div class="relative">
                        <button @click="mobileFiltersOpen = !mobileFiltersOpen" 
                                class="w-12 h-12 flex items-center justify-center rounded-[1.25rem] bg-stone-50 dark:bg-stone-800 hover:bg-amber-500 hover:text-white transition-all duration-300 shadow-inner">
                            <i data-lucide="more-vertical" class="w-6 h-6"></i>
                        </button>

                        <!-- Mobile Menu Dropdown (Cinematic Transition) -->
                        <div x-show="mobileFiltersOpen" 
                             @click.away="mobileFiltersOpen = false"
                             x-transition:enter="transition ease-out duration-300"
                             x-transition:enter-start="opacity-0 scale-90 -translate-y-4"
                             x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                             class="absolute right-0 mt-4 w-72 backdrop-blur-2xl bg-white/95 dark:bg-stone-900/95 rounded-[2.5rem] shadow-[0_20px_50px_rgba(0,0,0,0.2)] border border-white/20 dark:border-stone-800/50 overflow-hidden py-4 z-50"
                             x-cloak>
                            @foreach($categories as $key => $label)
                                <a href="{{ route('cultural-hub.index', ['category' => $key]) }}"
                                   class="flex items-center justify-between px-8 py-4 hover:bg-amber-500/10 transition-colors group">
                                    <div class="flex items-center space-x-4">
                                        <div class="w-2 h-2 rounded-full {{ $category === $key ? 'bg-amber-500 shadow-[0_0_12px_rgba(245,158,11,0.8)]' : 'bg-stone-200 dark:bg-stone-700' }}"></div>
                                        <span class="text-xs font-black uppercase tracking-widest {{ $category === $key ? 'text-amber-600' : 'text-stone-600 dark:text-stone-400' }}">
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
                        <div class="border border-stone-100 dark:border-stone-800 bg-white dark:bg-stone-900 rounded-2xl overflow-hidden shadow-sm hover:shadow-md transition flex flex-col h-full group">
                            
                            <!-- Image Section -->
                            <div class="h-40 relative overflow-hidden bg-stone-100 dark:bg-stone-800/50">
                                @if($culture->image)
                                    <img src="{{ Storage::url($culture->image) }}" 
                                         class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-105"
                                         loading="lazy">
                                @else
                                    <div class="w-full h-full flex items-center justify-center">
                                        <i data-lucide="globe" class="w-10 h-10 text-stone-300 dark:text-stone-700"></i>
                                    </div>
                                @endif
                                
                                <!-- Category Badge -->
                                <div class="absolute top-3 right-3 px-2 py-1 bg-black/50 backdrop-blur-md rounded-lg border border-white/10">
                                    <span class="text-[9px] font-black text-white uppercase tracking-widest">{{ $culture->category }}</span>
                                </div>
                            </div>

                            <!-- Content Info Body -->
                            <div class="p-5 flex-1 flex flex-col justify-between">
                                <div class="space-y-1">
                                    <h3 class="font-bold text-stone-900 dark:text-stone-100 text-lg leading-tight truncate">
                                        {{ $culture->name }}
                                    </h3>
                                    <p class="text-stone-600 dark:text-stone-400 text-sm truncate uppercase tracking-wider font-medium">
                                        {{ $culture->region }}
                                    </p>
                                </div>

                                <!-- Stats -->
                                <div class="mt-4 flex items-center justify-between text-[11px] font-bold text-stone-500 dark:text-stone-500 uppercase tracking-tight">
                                    <span class="flex items-center gap-1.5" title="Resonance">
                                        <i data-lucide="zap" 
                                           id="culture-vibe-icon-{{ $culture->id }}"
                                           class="w-3.5 h-3.5 {{ auth()->check() && auth()->user()->hasResonated($culture) ? 'text-orange-500 fill-orange-500' : '' }}"></i>
                                        <span id="culture-vibe-count-{{ $culture->id }}">{{ $culture->resonance_count }}</span>
                                    </span>
                                    <span class="flex items-center gap-1.5" title="Lock-ins">
                                        <i data-lucide="lock" 
                                           id="culture-lock-icon-{{ $culture->id }}"
                                           class="w-3.5 h-3.5 {{ auth()->check() && auth()->user()->hasLockedIn($culture) ? 'text-amber-500' : '' }}"></i>
                                        <span id="culture-lock-count-{{ $culture->id }}">{{ $culture->locked_in_count }}</span>
                                    </span>
                                    <span class="flex items-center gap-1.5">
                                        <i data-lucide="clock" class="w-3.5 h-3.5"></i>
                                        {{ $culture->created_at->diffForHumans(null, true) }}
                                    </span>
                                </div>

                                <!-- Action Buttons -->
                                <div class="mt-4 flex gap-2">
                                    @auth
                                        <button onclick="toggleCultureLockin({{ $culture->id }})" 
                                                id="culture-lock-btn-{{ $culture->id }}"
                                                class="flex-1 px-4 py-2.5 rounded-xl text-xs font-black uppercase tracking-widest glass-orange text-white transition active:scale-95">
                                            {{ auth()->user()->hasLockedIn($culture) ? 'Locked-in' : 'Lock-in' }}
                                        </button>
                                    @else
                                        <a href="{{ route('login') }}" class="flex-1 px-4 py-2.5 text-center rounded-xl text-xs font-black uppercase tracking-widest glass-orange text-white transition">
                                            Lock-in
                                        </a>
                                    @endauth

                                    <a href="{{ route('cultural-hub.show', $culture->id) }}" 
                                       class="flex-1 px-4 py-2.5 text-center rounded-xl text-xs font-black uppercase tracking-widest border border-amber-500 text-amber-600 hover:bg-amber-50 dark:hover:bg-amber-900/20 transition active:scale-95">
                                        Explore
                                    </a>
                                </div>
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