@extends('layouts.app')

@section('title', 'Cultural Hub - Explore Global Heritage')

@section('content')
    <div class="max-w-[1600px] mx-auto px-4 sm:px-6 lg:px-10 py-6 sm:py-10 relative">
        <!-- Background Decorative Elements -->
        <div class="fixed inset-0 pointer-events-none z-0 opacity-30 dark:opacity-20">
            <div class="absolute top-[10%] left-[5%] w-96 h-96 bg-amber-500/10 rounded-full blur-[120px]"></div>
            <div class="absolute bottom-[10%] right-[5%] w-[30rem] h-[30rem] bg-orange-600/10 rounded-full blur-[150px]"></div>
        </div>

        <!-- Header & Real Statistics -->
        <div class="mb-6 sm:mb-8 space-y-4">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 text-center sm:text-left">
                <div>
                    <h1 class="text-2xl sm:text-3xl font-extrabold text-stone-900 dark:text-stone-100 tracking-tight">
                        Living Cultural Archive
                    </h1>
                    <p class="text-xs sm:text-sm text-stone-600 dark:text-stone-400 leading-relaxed max-w-2xl mt-1">
                        Preserve oral testimonies, traditions, languages, and indigenous crafts. Evidence-traceable and community-reviewed.
                    </p>
                </div>

                {{-- Real Archive Statistics (No fabricated counts) --}}
                <div class="flex items-center justify-center sm:justify-end gap-3 text-xs">
                    <div class="px-3 py-2 rounded-xl border border-stone-200 dark:border-stone-800 bg-white dark:bg-stone-900 text-center">
                        <span class="font-bold text-stone-900 dark:text-stone-100 block">{{ $totalRecordsCount }}</span>
                        <span class="text-[10px] text-stone-500 uppercase tracking-wider">Archived Records</span>
                    </div>
                    <div class="px-3 py-2 rounded-xl border border-stone-200 dark:border-stone-800 bg-white dark:bg-stone-900 text-center">
                        <span class="font-bold text-stone-900 dark:text-stone-100 block">{{ $distinctLanguagesCount }}</span>
                        <span class="text-[10px] text-stone-500 uppercase tracking-wider">Languages</span>
                    </div>
                    <div class="px-3 py-2 rounded-xl border border-stone-200 dark:border-stone-800 bg-white dark:bg-stone-900 text-center">
                        <span class="font-bold text-stone-900 dark:text-stone-100 block">{{ $distinctRegionsCount }}</span>
                        <span class="text-[10px] text-stone-500 uppercase tracking-wider">Regions</span>
                    </div>
                </div>
            </div>

            {{-- Multi-Faceted Archival Search Bar --}}
            <form action="{{ route('cultural-hub.index') }}" method="GET" 
                  class="p-3 sm:p-4 rounded-2xl bg-white dark:bg-stone-900 border border-stone-200/90 dark:border-stone-800 shadow-sm space-y-3">
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-2.5">
                    {{-- Search Text --}}
                    <div class="relative">
                        <i data-lucide="search" class="w-4 h-4 text-stone-400 absolute left-3 top-3"></i>
                        <input type="text" name="search" value="{{ request('search') }}" 
                               placeholder="Search title, context, locality..."
                               class="w-full pl-9 pr-3 py-2 text-xs rounded-xl border border-stone-200 dark:border-stone-800 bg-stone-50 dark:bg-stone-800/50 text-stone-900 dark:text-stone-100">
                    </div>

                    {{-- Community / Group --}}
                    <div class="relative">
                        <i data-lucide="users" class="w-4 h-4 text-stone-400 absolute left-3 top-3"></i>
                        <input type="text" name="cultural_group" value="{{ request('cultural_group') }}" 
                               placeholder="Filter by community/group..."
                               class="w-full pl-9 pr-3 py-2 text-xs rounded-xl border border-stone-200 dark:border-stone-800 bg-stone-50 dark:bg-stone-800/50 text-stone-900 dark:text-stone-100">
                    </div>

                    {{-- Language --}}
                    <div class="relative">
                        <i data-lucide="languages" class="w-4 h-4 text-stone-400 absolute left-3 top-3"></i>
                        <input type="text" name="language" value="{{ request('language') }}" 
                               placeholder="Filter by language..."
                               class="w-full pl-9 pr-3 py-2 text-xs rounded-xl border border-stone-200 dark:border-stone-800 bg-stone-50 dark:bg-stone-800/50 text-stone-900 dark:text-stone-100">
                    </div>

                    {{-- Review Status --}}
                    <div>
                        <x-custom-select 
                            name="review_status" 
                            :options="[
                                'all' => 'All Verification Statuses',
                                'published' => 'Published Archive',
                                'under_review' => 'Under Community Review',
                                'awaiting_contributions' => 'Awaiting Evidence',
                                'disputed' => 'Disputed Accounts'
                            ]"
                            :value="request('review_status', 'all')"
                            placeholder="All Verification Statuses" 
                            icon="shield-check"
                            :searchable="false" />
                    </div>
                </div>

                <div class="flex items-center justify-between pt-1 flex-wrap gap-2">
                    <div class="flex items-center gap-2">
                        @if(request('search') || request('cultural_group') || request('language') || (request('review_status') && request('review_status') !== 'all') || ($category !== 'all'))
                            <a href="{{ route('cultural-hub.index') }}" class="text-[11px] font-bold text-amber-600 dark:text-amber-400 hover:underline">
                                Reset Filters
                            </a>
                        @endif
                    </div>
                    <button type="submit" class="px-5 py-2 bg-amber-500 hover:bg-amber-600 text-white font-bold text-xs rounded-xl transition flex items-center gap-1.5 shadow-sm">
                        <i data-lucide="filter" class="w-3.5 h-3.5"></i>
                        <span>Apply Archival Search</span>
                    </button>
                </div>
            </form>
        </div>

        @if(session('success'))
            <div class="mb-6 p-4 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 text-green-700 dark:text-green-400 rounded-xl flex items-center space-x-3">
                <i data-lucide="check-circle" class="w-5 h-5 transition-colors"></i>
                <p class="text-sm font-medium">{{ session('success') }}</p>
            </div>
        @endif

        <!-- Living Heritage Preservation Banner -->
        <div class="mb-10 bg-white dark:bg-stone-900 border border-amber-200/80 dark:border-amber-900/50 rounded-3xl p-6 sm:p-8 shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-6 relative overflow-hidden">
            <div class="space-y-2 max-w-2xl">
                <div class="inline-flex items-center space-x-2 px-3 py-1 rounded-full bg-amber-50 dark:bg-amber-950/60 border border-amber-200 dark:border-amber-800/80 text-[10px] font-bold text-amber-700 dark:text-amber-400 uppercase tracking-widest">
                    <i data-lucide="sparkles" class="w-3 h-3"></i>
                    <span>Living Heritage Archive</span>
                </div>
                <h2 class="page-title text-xl sm:text-2xl font-extrabold text-amber-500 tracking-tight">
                    Preserve Oral Traditions & Living Practices
                </h2>
                <p class="text-xs sm:text-sm text-stone-600 dark:text-stone-400 leading-relaxed">
                    Capture audio recordings, transcripts, and oral accounts. Support community guardians with AI transcription, structuring, and artifact segmentation.
                </p>
            </div>
            <div class="flex flex-wrap items-center gap-2.5 sm:gap-3 w-full sm:w-auto flex-shrink-0">
                <a href="{{ route('cultural-hub.create') }}"
                   class="w-full sm:w-auto inline-flex items-center justify-center space-x-2 px-5 py-3 bg-amber-500 hover:bg-amber-600 text-white font-bold rounded-xl text-xs uppercase tracking-wider transition-colors shadow-sm">
                    <i data-lucide="plus" class="w-4 h-4"></i>
                    <span>Document Heritage</span>
                </a>
                <a href="{{ route('cultural-hub.living-heritage.create') }}"
                   class="flex-1 sm:flex-initial inline-flex items-center justify-center space-x-2 px-4 sm:px-5 py-3 bg-stone-100 dark:bg-stone-800 hover:bg-stone-200 dark:hover:bg-stone-700 text-stone-800 dark:text-stone-200 font-bold rounded-xl text-xs uppercase tracking-wider transition-colors border border-stone-200 dark:border-stone-700 text-center">
                    <i data-lucide="mic" class="w-4 h-4"></i>
                    <span>Audio Capture</span>
                </a>
            </div>
        </div>

        <!-- Main Discovery Container -->
        <div class="lg:grid lg:grid-cols-12 lg:gap-12 items-start" x-data="{ mobileFiltersOpen: false }">
            
            <!-- Main Content Area -->
            <div class="lg:col-span-9 space-y-8 order-last lg:order-first">
                
                <!-- Cultures Grid -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 mb-12">
                    @forelse($cultures as $culture)
                        <div class="border border-stone-200/90 dark:border-stone-800 bg-white dark:bg-[#131218] rounded-2xl overflow-hidden shadow-sm hover:shadow-lg hover:border-amber-500/40 transition-all flex flex-col h-full group">
                            
                            <!-- Image Section with Record ID & Status -->
                            <div class="h-44 relative overflow-hidden bg-stone-100 dark:bg-stone-800/50">
                                @if($culture->image)
                                    <img src="{{ Storage::url($culture->image) }}" 
                                         alt="{{ $culture->name }}"
                                         class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105">
                                @else
                                    <div class="w-full h-full flex items-center justify-center bg-stone-100 dark:bg-stone-900">
                                        <i data-lucide="landmark" class="w-12 h-12 text-stone-300 dark:text-stone-700"></i>
                                    </div>
                                @endif
                                <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/20 to-transparent"></div>
                                
                                <!-- Top Bar: ID and Status -->
                                <div class="absolute top-2.5 left-2.5 right-2.5 flex items-center justify-between gap-1.5">
                                    <span class="px-2 py-0.5 bg-black/70 backdrop-blur-md text-amber-300 text-[10px] font-mono font-bold rounded">
                                        #CR-{{ str_pad($culture->id, 5, '0', STR_PAD_LEFT) }}
                                    </span>

                                    @php
                                        $cBadge = $culture->review_badge;
                                    @endphp
                                    <span class="px-2 py-0.5 rounded text-[9px] font-bold border backdrop-blur-md {{ $cBadge['class'] }}">
                                        {{ $cBadge['label'] }}
                                    </span>
                                </div>

                                <!-- Bottom Bar on Image: Region & Group -->
                                <div class="absolute bottom-2.5 left-2.5 right-2.5 flex items-center justify-between text-[11px] text-white">
                                    <span class="font-bold text-amber-300 truncate">
                                        {{ $culture->locality ? $culture->locality . ', ' : '' }}{{ $culture->region }}
                                    </span>
                                    @if($culture->language)
                                        <span class="text-[10px] text-stone-300 font-mono truncate max-w-[90px]">
                                            {{ $culture->language }}
                                        </span>
                                    @endif
                                </div>
                            </div>

                            <!-- Content Info Body -->
                            <div class="p-4 sm:p-5 flex-1 flex flex-col justify-between space-y-3">
                                <div class="space-y-1.5">
                                    <div class="flex items-center justify-between text-[10px] text-stone-500">
                                        <span class="font-bold uppercase tracking-wider text-amber-700 dark:text-amber-400 inline-flex items-center gap-1.5"><i data-lucide="{{ $culture->category_icon }}" class="w-3.5 h-3.5 text-amber-500"></i><span>{{ $culture->category }}</span></span>
                                        @if($culture->cultural_group)
                                            <span class="truncate max-w-[120px]">{{ $culture->cultural_group }}</span>
                                        @endif
                                    </div>

                                    <h3 class="font-bold text-stone-900 dark:text-stone-100 text-base leading-snug group-hover:text-amber-600 dark:group-hover:text-amber-400 transition line-clamp-1">
                                        {{ $culture->name }}
                                    </h3>
                                    
                                    <p class="text-stone-600 dark:text-stone-400 text-xs line-clamp-2 leading-relaxed">
                                        {{ $culture->significance ?? $culture->description }}
                                    </p>
                                </div>

                                <!-- Action Link -->
                                <a href="{{ route('cultural-hub.show', $culture->id) }}" 
                                   class="w-full py-2 px-3 rounded-xl bg-stone-100 dark:bg-stone-800 hover:bg-amber-500 hover:text-white dark:hover:bg-amber-500 dark:hover:text-white text-stone-800 dark:text-stone-200 text-xs font-bold text-center transition flex items-center justify-center gap-1.5">
                                    <span>View Archive Record</span>
                                    <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                                </a>
                            </div>
                        </div>
                    @empty
                        <div class="col-span-full text-center py-16 bg-white dark:bg-stone-900 rounded-3xl border border-stone-200 dark:border-stone-800 space-y-3">
                            <i data-lucide="archive" class="w-12 h-12 text-stone-300 dark:text-stone-700 mx-auto"></i>
                            <h3 class="text-base font-bold text-stone-900 dark:text-stone-100">No Matching Cultural Records Found</h3>
                            <p class="text-xs text-stone-500 max-w-sm mx-auto">
                                No records match the selected search or filter criteria. Be the first to contribute documentation for this tradition.
                            </p>
                            <a href="{{ route('cultural-hub.create') }}" class="inline-flex items-center gap-1.5 px-4 py-2 bg-amber-500 hover:bg-amber-600 text-white rounded-xl text-xs font-bold transition">
                                <i data-lucide="plus" class="w-4 h-4"></i>
                                <span>Document Heritage Record</span>
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
                <div class="backdrop-blur-2xl bg-white/70 dark:bg-stone-900/70 rounded-[3rem] border border-white/40 dark:border-stone-800/50 p-10 shadow-xl">
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
                                        $icon = \App\Models\Culture::getCategoryIcon($key);
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
            <div class="bg-stone-900 dark:bg-white rounded-[4rem] p-16 mt-24 text-center relative overflow-hidden shadow-xl">
                <div class="relative z-10">
                    <div class="w-20 h-20 bg-amber-500 rounded-[2rem] flex items-center justify-center mx-auto mb-10 shadow-lg rotate-12">
                        <i data-lucide="shield-check" class="w-10 h-10 text-white"></i>
                    </div>
                    <h3 class="text-4xl sm:text-5xl font-black text-white dark:text-stone-900 mb-6 tracking-tighter leading-none">The Human Legacy <br> Needs You</h3>
                    <p class="text-stone-400 dark:text-stone-500 text-lg max-w-2xl mx-auto mb-12 font-medium leading-relaxed">Join thousands of guardians in the global initiative to preserve our shared traditions. Unlock your potential to contribute to Kulturegram.</p>
                    <a href="{{ route('register') }}"
                        class="inline-flex items-center space-x-4 px-12 py-6 bg-amber-500 text-white rounded-[2rem] font-black uppercase tracking-[0.3em] text-[10px] hover:scale-105 active:scale-95 transition-all shadow-lg">
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