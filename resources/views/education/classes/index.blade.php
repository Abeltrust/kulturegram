@extends('layouts.app')

@section('title', 'Cultural Classes & Language Studio - Kulturegram')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 sm:py-12">

    {{-- Breadcrumb & Top Bar --}}
    <div class="flex items-center justify-between gap-4 mb-8">
        <div class="flex items-center space-x-2 text-xs sm:text-sm text-stone-500 dark:text-stone-400">
            <a href="{{ route('education.index') }}" class="hover:text-amber-500 transition">Educational Hub</a>
            <span>/</span>
            <span class="text-stone-800 dark:text-stone-100 font-semibold">Cultural Classes & Language Studio</span>
        </div>

        @auth
            <a href="{{ route('education.classes.create') }}" 
               class="px-5 py-2.5 rounded-xl bg-gradient-to-r from-amber-500 to-orange-600 hover:from-amber-600 hover:to-orange-700 text-white font-bold text-xs sm:text-sm shadow-lg shadow-amber-500/20 transition-all flex items-center gap-2 hover:scale-[1.02]">
                <i data-lucide="plus-circle" class="w-4 h-4"></i>
                <span>Host a Class</span>
            </a>
        @else
            <a href="{{ route('login') }}" 
               class="px-5 py-2.5 rounded-xl bg-stone-800 hover:bg-stone-700 text-stone-200 font-bold text-xs sm:text-sm transition flex items-center gap-2">
                <i data-lucide="log-in" class="w-4 h-4"></i>
                <span>Sign in to Teach</span>
            </a>
        @endauth
    </div>

    {{-- Hero Section --}}
    <div class="relative rounded-3xl overflow-hidden bg-gradient-to-br from-stone-900 via-stone-900 to-stone-950 border border-stone-800 p-8 sm:p-12 mb-10 shadow-2xl">
        <div class="absolute -right-16 -top-16 w-80 h-80 bg-amber-500/10 rounded-full blur-3xl pointer-events-none"></div>
        <div class="absolute -left-16 -bottom-16 w-80 h-80 bg-orange-600/10 rounded-full blur-3xl pointer-events-none"></div>

        <div class="relative z-10 max-w-3xl">
            <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-amber-500/10 border border-amber-500/20 text-amber-400 text-xs font-bold mb-4">
                <i data-lucide="sparkles" class="w-3.5 h-3.5"></i>
                <span>AI Subtitles & Bilingual Cultural Studio</span>
            </div>
            <h1 class="text-3xl sm:text-5xl font-black text-white leading-tight tracking-tight mb-4">
                Learn Heritage Languages, <span class="bg-gradient-to-r from-amber-400 via-orange-400 to-amber-200 bg-clip-text text-transparent italic font-serif">Word by Word</span>
            </h1>
            <p class="text-stone-300 text-sm sm:text-base leading-relaxed mb-6 max-w-2xl">
                Watch traditional classes where custodians and language instructors teach greetings, idioms, rituals, and expressions. Follow along with real-time interactive subtitles and dual-language vocabulary breakdowns.
            </p>

            <div class="flex flex-wrap gap-3">
                <a href="#classes-grid" class="px-6 py-3 bg-amber-500 hover:bg-amber-600 text-white rounded-xl font-bold text-xs sm:text-sm transition shadow-md shadow-amber-500/25 flex items-center gap-2">
                    <i data-lucide="play" class="w-4 h-4 fill-current"></i>
                    <span>Browse Classes</span>
                </a>
                <a href="{{ route('education.index') }}" class="px-6 py-3 bg-stone-800/80 hover:bg-stone-800 text-stone-200 border border-stone-700/80 rounded-xl font-bold text-xs sm:text-sm transition flex items-center gap-2">
                    <i data-lucide="calendar" class="w-4 h-4"></i>
                    <span>Live Workshops & Rituals</span>
                </a>
            </div>
        </div>
    </div>

    {{-- Filter & Search Controls --}}
    <div id="classes-grid" class="space-y-6 mb-10">
        {{-- Language Pills --}}
        @php
            $activeLang = request('language', 'all');
            $languages = [
                'all' => 'All Languages',
                'Hausa' => 'Hausa',
                'Yoruba' => 'Yoruba',
                'Igbo' => 'Igbo',
                'Swahili' => 'Swahili',
                'Fulani' => 'Fulani',
                'Twi' => 'Twi',
                'Amharic' => 'Amharic',
            ];
        @endphp
        <div class="flex items-center gap-2 overflow-x-auto pb-2 scrollbar-hide">
            @foreach ($languages as $key => $label)
                <a href="{{ route('education.classes.index', array_merge(request()->query(), ['language' => $key])) }}"
                   class="px-4 py-2 rounded-xl text-xs sm:text-sm font-bold whitespace-nowrap transition-all border
                          {{ $activeLang === $key 
                             ? 'bg-amber-500 border-amber-500 text-white shadow-sm' 
                             : 'bg-white dark:bg-stone-900 border-stone-200 dark:border-stone-800 text-stone-600 dark:text-stone-400 hover:border-amber-400' }}">
                    {{ $label }}
                </a>
            @endforeach
        </div>

        {{-- Search & Secondary Filters --}}
        <form method="GET" action="{{ route('education.classes.index') }}" class="grid grid-cols-1 sm:grid-cols-12 gap-3">
            <input type="hidden" name="language" value="{{ request('language', 'all') }}">

            <div class="sm:col-span-6 relative">
                <input type="text" name="search" value="{{ request('search') }}"
                       placeholder="Search by topic, greeting, phrase, or instructor..."
                       class="w-full rounded-xl border border-stone-200 dark:border-stone-800 bg-white dark:bg-stone-900 pl-10 pr-4 py-2.5 text-xs sm:text-sm text-stone-800 dark:text-stone-200 focus:ring-2 focus:ring-amber-500 outline-none">
                <i data-lucide="search" class="w-4 h-4 text-stone-400 absolute left-3.5 top-3"></i>
            </div>

            <div class="sm:col-span-3">
                <select name="price_type" onchange="this.form.submit()"
                        class="w-full rounded-xl border border-stone-200 dark:border-stone-800 bg-white dark:bg-stone-900 px-3 py-2.5 text-xs sm:text-sm text-stone-800 dark:text-stone-200 focus:ring-2 focus:ring-amber-500 outline-none">
                    <option value="all" {{ request('price_type') === 'all' ? 'selected' : '' }}>Pricing: All</option>
                    <option value="free" {{ request('price_type') === 'free' ? 'selected' : '' }}>Free Classes Only</option>
                    <option value="paid" {{ request('price_type') === 'paid' ? 'selected' : '' }}>Paid Masterclasses</option>
                </select>
            </div>

            <div class="sm:col-span-3 flex gap-2">
                <select name="level" onchange="this.form.submit()"
                        class="w-full rounded-xl border border-stone-200 dark:border-stone-800 bg-white dark:bg-stone-900 px-3 py-2.5 text-xs sm:text-sm text-stone-800 dark:text-stone-200 focus:ring-2 focus:ring-amber-500 outline-none">
                    <option value="all" {{ request('level') === 'all' ? 'selected' : '' }}>Level: All</option>
                    <option value="beginner" {{ request('level') === 'beginner' ? 'selected' : '' }}>Beginner</option>
                    <option value="intermediate" {{ request('level') === 'intermediate' ? 'selected' : '' }}>Intermediate</option>
                    <option value="advanced" {{ request('level') === 'advanced' ? 'selected' : '' }}>Advanced</option>
                </select>
                <button type="submit" class="px-4 py-2.5 rounded-xl bg-stone-800 dark:bg-stone-700 text-stone-200 hover:text-white font-bold text-xs transition">
                    Filter
                </button>
            </div>
        </form>
    </div>

    {{-- Classes Grid --}}
    @if ($classes->count() > 0)
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 sm:gap-8 mb-12">
            @foreach ($classes as $item)
                <div class="group bg-white dark:bg-stone-900 rounded-3xl overflow-hidden border border-stone-200 dark:border-stone-800 shadow-sm hover:shadow-xl transition-all duration-300 flex flex-col h-full hover:border-amber-500/40">
                    
                    {{-- Thumbnail & Badges --}}
                    <div class="relative h-48 sm:h-52 overflow-hidden bg-stone-950">
                        @if ($item->resolvedThumbnailUrl())
                            <img src="{{ $item->resolvedThumbnailUrl() }}" 
                                 class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" 
                                 alt="{{ $item->title }}"
                                 loading="lazy">
                        @else
                            <div class="w-full h-full flex flex-col items-center justify-center bg-gradient-to-br from-stone-800 to-stone-950 p-6 text-center">
                                <i data-lucide="languages" class="w-10 h-10 text-amber-500/60 mb-2"></i>
                                <span class="text-xs font-bold text-stone-400">{{ $item->cultural_focus }}</span>
                            </div>
                        @endif

                        {{-- Floating Pill Badges --}}
                        <div class="absolute top-3 left-3 flex flex-wrap gap-1.5 z-10">
                            @if ($item->target_language)
                                <span class="bg-stone-900/90 backdrop-blur-md px-2.5 py-1 rounded-full text-[10px] font-black uppercase tracking-wider text-amber-400 border border-amber-500/30 shadow-sm">
                                    {{ $item->target_language }}
                                </span>
                            @endif
                            <span class="bg-black/75 backdrop-blur-md px-2.5 py-1 rounded-full text-[10px] font-bold text-stone-200 border border-stone-700/80">
                                {{ ucfirst(str_replace('_', ' ', $item->level)) }}
                            </span>
                        </div>

                        {{-- Price Tag --}}
                        <div class="absolute top-3 right-3 z-10">
                            @if ($item->isFree())
                                <span class="bg-emerald-500/95 backdrop-blur-md px-3 py-1 rounded-full text-[11px] font-black uppercase tracking-wider text-white shadow-md">
                                    Free
                                </span>
                            @else
                                <span class="bg-amber-500/95 backdrop-blur-md px-3 py-1 rounded-full text-[11px] font-black uppercase tracking-wider text-white shadow-md">
                                    ${{ number_format($item->price, 2) }}
                                </span>
                            @endif
                        </div>

                        {{-- Duration Badge & Subtitle Indicator --}}
                        <div class="absolute bottom-3 left-3 right-3 flex items-center justify-between text-[11px] font-semibold text-white/90 z-10">
                            <span class="bg-black/70 backdrop-blur-md px-2 py-0.5 rounded-lg flex items-center gap-1 border border-white/10">
                                <i data-lucide="clock" class="w-3 h-3 text-amber-400"></i>
                                <span>{{ $item->formattedDuration() }}</span>
                            </span>

                            @if (!empty($item->timed_subtitles))
                                <span class="bg-black/70 backdrop-blur-md px-2 py-0.5 rounded-lg flex items-center gap-1 border border-amber-500/30 text-amber-300">
                                    <i data-lucide="subtitles" class="w-3 h-3"></i>
                                    <span>AI Subtitles</span>
                                </span>
                            @endif
                        </div>

                        {{-- Play Overlay on Hover --}}
                        <a href="{{ route('education.classes.show', $item->slug) }}" 
                           class="absolute inset-0 bg-stone-900/30 group-hover:bg-stone-900/50 flex items-center justify-center transition-all opacity-0 group-hover:opacity-100">
                            <div class="w-12 h-12 rounded-full bg-amber-500 text-white flex items-center justify-center shadow-lg transform group-hover:scale-110 transition-transform">
                                <i data-lucide="play" class="w-5 h-5 fill-current ml-0.5"></i>
                            </div>
                        </a>
                    </div>

                    {{-- Body Content --}}
                    <div class="p-5 flex-1 flex flex-col">
                        <div class="text-[11px] font-bold text-amber-600 dark:text-amber-500 uppercase tracking-wider mb-1">
                            {{ $item->cultural_focus }}
                        </div>

                        <h3 class="text-base sm:text-lg font-black text-stone-800 dark:text-stone-100 group-hover:text-amber-500 transition-colors line-clamp-1 mb-2">
                            <a href="{{ route('education.classes.show', $item->slug) }}">
                                {{ $item->title }}
                            </a>
                        </h3>

                        <p class="text-xs sm:text-sm text-stone-500 dark:text-stone-400 line-clamp-2 mb-4 leading-relaxed">
                            {{ $item->description ?: $item->ai_summary }}
                        </p>

                        {{-- Vocabulary Pills Snippet --}}
                        @if (!empty($item->bilingual_vocabulary))
                            <div class="mb-4 flex flex-wrap gap-1.5">
                                @foreach (array_slice($item->bilingual_vocabulary, 0, 2) as $vocab)
                                    <span class="inline-flex items-center gap-1 text-[10px] font-semibold bg-stone-100 dark:bg-stone-800/80 text-stone-700 dark:text-stone-300 px-2 py-0.5 rounded-md border border-stone-200 dark:border-stone-700">
                                        <span class="text-amber-600 dark:text-amber-400 font-bold">{{ $vocab['target'] ?? '' }}</span>
                                        <span class="text-stone-400">·</span>
                                        <span>{{ $vocab['source'] ?? '' }}</span>
                                    </span>
                                @endforeach
                            </div>
                        @endif

                        {{-- Instructor & Action Footer --}}
                        <div class="mt-auto pt-4 border-t border-stone-100 dark:border-stone-800 flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <img src="{{ $item->instructor->profile_photo_url }}" 
                                     class="w-7 h-7 rounded-full object-cover border border-stone-200 dark:border-stone-700" 
                                     alt="{{ $item->instructor->name }}">
                                <div class="overflow-hidden">
                                    <div class="text-xs font-bold text-stone-700 dark:text-stone-300 truncate max-w-[100px]">
                                        {{ $item->instructor->name }}
                                    </div>
                                    <div class="text-[10px] text-stone-400">
                                        {{ $item->enrollments_count }} {{ Str::plural('learner', $item->enrollments_count) }}
                                    </div>
                                </div>
                            </div>

                            <a href="{{ route('education.classes.show', $item->slug) }}" 
                               class="px-3.5 py-1.5 rounded-xl bg-stone-100 dark:bg-stone-800 hover:bg-amber-500 hover:text-white dark:hover:bg-amber-500 text-stone-700 dark:text-stone-300 text-xs font-bold transition flex items-center gap-1">
                                <span>Watch</span>
                                <i data-lucide="chevron-right" class="w-3.5 h-3.5"></i>
                            </a>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="mt-8">
            {{ $classes->links() }}
        </div>
    @else
        <div class="bg-white dark:bg-stone-900 rounded-3xl border border-dashed border-stone-200 dark:border-stone-800 p-12 text-center my-8">
            <div class="w-16 h-16 rounded-2xl bg-amber-500/10 text-amber-500 flex items-center justify-center mx-auto mb-4">
                <i data-lucide="book-open" class="w-8 h-8"></i>
            </div>
            <h3 class="text-xl font-black text-stone-800 dark:text-stone-100 mb-2">No Cultural Classes Found</h3>
            <p class="text-xs sm:text-sm text-stone-500 dark:text-stone-400 max-w-md mx-auto mb-6">
                Be the first custodian or language teacher to share a cultural lesson with AI synchronized subtitles.
            </p>
            <a href="{{ route('education.classes.create') }}" 
               class="inline-flex items-center gap-2 px-6 py-3 rounded-xl bg-amber-500 hover:bg-amber-600 text-white font-bold text-sm shadow-lg shadow-amber-500/20 transition">
                <i data-lucide="plus-circle" class="w-4 h-4"></i>
                <span>Teach the First Class</span>
            </a>
        </div>
    @endif

</div>
@endsection

@push('scripts')
<script>
    document.addEventListener("DOMContentLoaded", () => {
        if (typeof lucide !== 'undefined') {
            lucide.createIcons();
        }
    });
</script>
<style>
    .scrollbar-hide::-webkit-scrollbar { display: none; }
    .scrollbar-hide { -ms-overflow-style: none; scrollbar-width: none; }
</style>
@endpush
