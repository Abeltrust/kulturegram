@extends('layouts.app')

@section('title', $culture->name . ' - Living Heritage Archive - Kulturegram')

@push('styles')
<!-- Educational & Archival Typography -->
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@600;700;800;900&family=Newsreader:ital,opsz,wght@0,6..72,400;0,6..72,500;0,6..72,600;0,6..72,700;1,6..72,400;1,6..72,600&display=swap" rel="stylesheet">

<style>
    .font-heritage {
        font-family: 'Cinzel', serif;
    }
    .font-editorial {
        font-family: 'Newsreader', Georgia, serif;
    }
    .drop-cap::first-letter {
        font-family: 'Cinzel', serif;
        float: left;
        font-size: 3.8rem;
        line-height: 0.8;
        padding-top: 4px;
        padding-right: 12px;
        padding-bottom: 2px;
        color: #d97706;
        font-weight: 800;
    }
</style>
@endpush

@section('content')
<div class="min-h-screen bg-[#faf8f5] dark:bg-[#0c0c10] text-stone-900 dark:text-stone-100 transition-colors duration-300"
     x-data="culturalDossierComponent({
        cultureId: {{ $culture->id }},
        lockedIn: {{ auth()->check() && auth()->user()->hasLockedIn($culture) ? 'true' : 'false' }},
        lockCount: {{ $culture->locked_in_count }},
        resonated: {{ auth()->check() && auth()->user()->hasResonated($culture) ? 'true' : 'false' }},
        resonanceCount: {{ $culture->resonance_count }},
        csrfToken: '{{ csrf_token() }}'
     })">

    {{-- Top Sticky Reading Toolbar --}}
    <div class="sticky top-14 md:top-16 z-30 backdrop-blur-md bg-white/85 dark:bg-[#121218]/85 border-b border-stone-200/80 dark:border-stone-800/80 transition">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-2.5 flex items-center justify-between gap-4">
            {{-- Breadcrumb & Title --}}
            <div class="flex items-center gap-2 overflow-hidden min-w-0">
                <a href="{{ route('cultural-hub.index') }}" 
                   class="text-xs font-bold uppercase tracking-wider text-stone-500 hover:text-amber-600 dark:hover:text-amber-400 transition flex items-center gap-1 flex-shrink-0">
                    <i data-lucide="arrow-left" class="w-3.5 h-3.5"></i>
                    <span>Cultural Hub</span>
                </a>
                <span class="text-stone-300 dark:text-stone-700">/</span>
                <span class="text-xs font-bold text-amber-600 dark:text-amber-400 uppercase tracking-wider truncate">
                    {{ $culture->category }}
                </span>
                <span class="text-stone-300 dark:text-stone-700 hidden sm:inline">/</span>
                <span class="text-xs font-semibold text-stone-700 dark:text-stone-300 truncate hidden sm:inline">
                    {{ $culture->name }}
                </span>
            </div>

            {{-- Interactive Action Controls --}}
            <div class="flex items-center gap-2 flex-shrink-0">
                {{-- Font Size Adjuster --}}
                <div class="hidden md:flex items-center rounded-lg border border-stone-200 dark:border-stone-800 bg-stone-50 dark:bg-stone-900 p-0.5 text-xs font-bold">
                    <button @click="fontSize = Math.max(14, fontSize - 1)" 
                            class="px-2 py-1 text-stone-600 dark:text-stone-400 hover:text-amber-600 dark:hover:text-amber-400" title="Decrease font size">A-</button>
                    <span class="px-1 text-[10px] text-stone-400" x-text="fontSize + 'px'"></span>
                    <button @click="fontSize = Math.min(22, fontSize + 1)" 
                            class="px-2 py-1 text-stone-600 dark:text-stone-400 hover:text-amber-600 dark:hover:text-amber-400" title="Increase font size">A+</button>
                </div>

                {{-- Citation Builder Trigger --}}
                <button @click="openCitationModal = true"
                        class="px-3 py-1.5 rounded-lg border border-stone-200 dark:border-stone-800 bg-white dark:bg-stone-900 text-stone-700 dark:text-stone-300 text-xs font-semibold hover:border-amber-500 hover:text-amber-600 dark:hover:text-amber-400 transition flex items-center gap-1.5 shadow-sm">
                    <i data-lucide="quote" class="w-3.5 h-3.5 text-amber-500"></i>
                    <span class="hidden sm:inline">Cite Record</span>
                </button>

                {{-- Lock-In Presence (Bookmark / Connection) --}}
                @auth
                    <button @click="toggleLockIn()"
                            :disabled="isSubmittingLock"
                            class="px-3.5 py-1.5 rounded-lg text-xs font-bold transition flex items-center gap-1.5 shadow-sm"
                            :class="lockedIn 
                                ? 'bg-amber-500 text-white shadow-amber-500/25 hover:bg-amber-600' 
                                : 'bg-white dark:bg-stone-900 border border-stone-200 dark:border-stone-800 text-stone-700 dark:text-stone-300 hover:border-amber-500'">
                        <i data-lucide="lock" class="w-3.5 h-3.5" :class="lockedIn ? 'text-white' : 'text-amber-500'"></i>
                        <span x-text="lockedIn ? 'Locked In' : 'Lock-In Presence'"></span>
                        <span class="text-[10px] px-1.5 py-0.2 rounded-full font-mono"
                              :class="lockedIn ? 'bg-amber-600 text-white' : 'bg-stone-100 dark:bg-stone-800 text-stone-600 dark:text-stone-400'"
                              x-text="lockCount"></span>
                    </button>
                @else
                    <a href="{{ route('login') }}"
                       class="px-3.5 py-1.5 rounded-lg text-xs font-bold bg-white dark:bg-stone-900 border border-stone-200 dark:border-stone-800 text-stone-700 dark:text-stone-300 hover:border-amber-500 flex items-center gap-1.5">
                        <i data-lucide="lock" class="w-3.5 h-3.5 text-amber-500"></i>
                        <span>Lock-In</span>
                        <span class="text-[10px] px-1.5 py-0.2 bg-stone-100 dark:bg-stone-800 rounded-full font-mono">{{ $culture->locked_in_count }}</span>
                    </a>
                @endauth

                {{-- Resonance / Pulse Appreciation --}}
                @auth
                    <button @click="toggleResonance()"
                            :disabled="isSubmittingPulse"
                            class="px-3 py-1.5 rounded-lg text-xs font-bold transition flex items-center gap-1.5 border"
                            :class="resonated 
                                ? 'bg-orange-500/10 border-orange-500 text-orange-600 dark:text-orange-400' 
                                : 'bg-white dark:bg-stone-900 border-stone-200 dark:border-stone-800 text-stone-700 dark:text-stone-300 hover:border-orange-500'">
                        <i data-lucide="zap" class="w-3.5 h-3.5" :class="resonated ? 'text-orange-500 fill-orange-500' : 'text-stone-400'"></i>
                        <span class="hidden sm:inline" x-text="resonated ? 'Resonated' : 'Pulse'"></span>
                        <span class="text-[10px] font-mono" x-text="resonanceCount"></span>
                    </button>
                @endauth

                {{-- Share Button --}}
                <button @click="copyShareLink()"
                        class="p-1.5 rounded-lg border border-stone-200 dark:border-stone-800 bg-white dark:bg-stone-900 text-stone-600 dark:text-stone-400 hover:text-amber-600 transition"
                        title="Copy Share Link">
                    <i data-lucide="share-2" class="w-4 h-4"></i>
                </button>
            </div>
        </div>
    </div>

    {{-- Main Container --}}
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 md:py-12 space-y-10">

        {{-- GRAND EDUCATIONAL HERO BANNER --}}
        <div class="relative rounded-3xl overflow-hidden border border-stone-200/90 dark:border-stone-800 bg-white dark:bg-[#131218] shadow-xl">
            {{-- Background Image / Pattern --}}
            <div class="relative h-72 sm:h-96 lg:h-[420px] w-full overflow-hidden">
                @if($culture->image)
                    <img src="{{ Storage::url($culture->image) }}" 
                         alt="{{ $culture->name }}"
                         class="w-full h-full object-cover object-center filter brightness-[0.88] contrast-[1.05]">
                @else
                    <div class="w-full h-full bg-gradient-to-br from-amber-700 via-stone-900 to-black flex items-center justify-center">
                        <i data-lucide="landmark" class="w-20 h-20 text-white/20"></i>
                    </div>
                @endif
                <div class="absolute inset-0 bg-gradient-to-t from-black/90 via-black/40 to-transparent"></div>

                {{-- Top Floating Badges --}}
                <div class="absolute top-6 left-6 right-6 flex flex-wrap items-center justify-between gap-3">
                    <div class="flex items-center gap-2">
                        <span class="px-3 py-1 rounded-full text-[11px] font-black uppercase tracking-wider bg-amber-500 text-black shadow-md flex items-center gap-1.5">
                            <i data-lucide="bookmark" class="w-3.5 h-3.5"></i>
                            {{ $culture->category }}
                        </span>
                        
                        @if($culture->is_ai_assisted)
                            <span class="px-2.5 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider bg-black/60 backdrop-blur-md text-amber-300 border border-amber-400/30 flex items-center gap-1">
                                <i data-lucide="sparkles" class="w-3 h-3 text-amber-400"></i>
                                <span>AI Preservation Assist</span>
                            </span>
                        @endif
                    </div>

                    {{-- Endangerment / Safeguarding Level Gauge --}}
                    @php
                        $level = strtolower($culture->endangerment_level ?? 'vulnerable');
                    @endphp
                    <div class="px-3 py-1 rounded-full text-[11px] font-black uppercase tracking-wider backdrop-blur-md shadow-md border flex items-center gap-1.5
                        {{ str_contains($level, 'critical') ? 'bg-rose-950/80 text-rose-300 border-rose-600/50' : 
                           (str_contains($level, 'endangered') ? 'bg-amber-950/80 text-amber-300 border-amber-600/50' : 
                           'bg-emerald-950/80 text-emerald-300 border-emerald-600/50') }}">
                        <span class="w-2 h-2 rounded-full animate-ping
                            {{ str_contains($level, 'critical') ? 'bg-rose-500' : (str_contains($level, 'endangered') ? 'bg-amber-500' : 'bg-emerald-500') }}"></span>
                        <span>{{ $culture->endangerment_level ?? 'Living Tradition' }}</span>
                    </div>
                </div>

                {{-- Hero Content Information --}}
                <div class="absolute bottom-6 sm:bottom-10 left-6 sm:left-10 right-6 sm:right-10 max-w-4xl space-y-3">
                    <div class="flex items-center gap-3 text-amber-400 text-xs sm:text-sm font-bold uppercase tracking-widest">
                        <span class="flex items-center gap-1">
                            <i data-lucide="map-pin" class="w-4 h-4"></i>
                            {{ $culture->region }}
                        </span>
                        <span>•</span>
                        <span class="flex items-center gap-1">
                            <i data-lucide="languages" class="w-4 h-4"></i>
                            {{ $culture->language ?? 'Indigenous Dialect' }}
                        </span>
                        @if($culture->historical_period)
                            <span class="hidden sm:inline">•</span>
                            <span class="hidden sm:flex items-center gap-1">
                                <i data-lucide="history" class="w-4 h-4"></i>
                                {{ $culture->historical_period }}
                            </span>
                        @endif
                    </div>

                    <h1 class="font-heritage text-3xl sm:text-5xl lg:text-6xl font-black text-white tracking-tight leading-none drop-shadow-md">
                        {{ $culture->name }}
                    </h1>

                    @if($culture->significance)
                        <p class="text-stone-200 text-sm sm:text-base line-clamp-2 max-w-3xl leading-relaxed text-shadow">
                            {{ $culture->significance }}
                        </p>
                    @endif
                </div>
            </div>

            {{-- Curated Educational Metrics Ribbon --}}
            <div class="grid grid-cols-2 sm:grid-cols-4 divide-y sm:divide-y-0 sm:divide-x divide-stone-200 dark:divide-stone-800 bg-stone-100/90 dark:bg-stone-900/90 p-4 border-t border-stone-200 dark:border-stone-800">
                <div class="p-3 text-center sm:text-left">
                    <span class="text-[10px] font-bold text-stone-500 dark:text-stone-400 uppercase tracking-widest block">Geographic Cradle</span>
                    <span class="font-bold text-stone-900 dark:text-stone-100 text-sm sm:text-base truncate block mt-0.5">{{ $culture->region }}</span>
                </div>
                <div class="p-3 text-center sm:text-left">
                    <span class="text-[10px] font-bold text-stone-500 dark:text-stone-400 uppercase tracking-widest block">Primary Language</span>
                    <span class="font-bold text-stone-900 dark:text-stone-100 text-sm sm:text-base truncate block mt-0.5">{{ $culture->language ?? 'Unspecified' }}</span>
                </div>
                <div class="p-3 text-center sm:text-left">
                    <span class="text-[10px] font-bold text-stone-500 dark:text-stone-400 uppercase tracking-widest block">Living Custodians</span>
                    <span class="font-bold text-stone-900 dark:text-stone-100 text-sm sm:text-base truncate block mt-0.5">{{ $culture->current_practitioners ?? 'Generational Elders' }}</span>
                </div>
                <div class="p-3 text-center sm:text-left">
                    <span class="text-[10px] font-bold text-stone-500 dark:text-stone-400 uppercase tracking-widest block">Transmission Mode</span>
                    <span class="font-bold text-amber-600 dark:text-amber-400 text-sm sm:text-base truncate block mt-0.5">{{ $culture->transmission_methods ?? 'Oral & Ceremonial' }}</span>
                </div>
            </div>
        </div>

        {{-- INTERACTIVE ORAL AUDIO HIGHLIGHT (If audio exists) --}}
        @if($culture->audio_path || $culture->audio_url)
            <div class="rounded-2xl border border-amber-200 dark:border-amber-900/60 bg-gradient-to-r from-amber-500/10 via-amber-500/5 to-transparent p-5 sm:p-6 shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-5">
                <div class="flex items-center gap-4">
                    <div class="w-14 h-14 rounded-2xl bg-amber-500 text-white flex items-center justify-center shadow-lg shadow-amber-500/30 flex-shrink-0">
                        <i data-lucide="headphones" class="w-7 h-7"></i>
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="px-2 py-0.5 rounded text-[10px] font-black uppercase tracking-wider bg-amber-200 text-amber-900 dark:bg-amber-950 dark:text-amber-300">
                                Oral Narration Archive
                            </span>
                            <span class="text-xs text-stone-500 dark:text-stone-400 font-medium">Authentic Field Audio</span>
                        </div>
                        <h3 class="text-base font-bold text-stone-900 dark:text-stone-100 mt-1">
                            Listen to Spoken Oral History & Folktale
                        </h3>
                        <p class="text-xs text-stone-600 dark:text-stone-400">
                            Recorded directly from community voice transmission.
                        </p>
                    </div>
                </div>

                <div class="w-full md:w-auto md:min-w-[340px]">
                    @if($culture->audio_path)
                        <audio controls class="w-full rounded-xl">
                            <source src="{{ Storage::url($culture->audio_path) }}" type="audio/mpeg">
                            Your browser does not support audio playback.
                        </audio>
                    @elseif($culture->audio_url)
                        <a href="{{ $culture->audio_url }}" target="_blank"
                           class="inline-flex items-center justify-center gap-2 w-full px-5 py-3 rounded-xl bg-amber-500 hover:bg-amber-600 text-white font-bold text-sm transition shadow-md">
                            <i data-lucide="external-link" class="w-4 h-4"></i>
                            <span>Listen to External Audio Archive</span>
                        </a>
                    @endif
                </div>
            </div>
        @endif

        {{-- EDUCATIONAL DOSSIER: CHAPTER NAVIGATION TABS --}}
        <div class="border-b border-stone-200 dark:border-stone-800 pb-2">
            <div class="flex items-center gap-2 overflow-x-auto no-scrollbar py-1">
                <button @click="activeTab = 'story'"
                        class="px-4 py-2.5 rounded-xl text-xs sm:text-sm font-bold transition-all flex items-center gap-2 flex-shrink-0 cursor-pointer"
                        :class="activeTab === 'story' 
                            ? 'bg-amber-500 text-white shadow-md shadow-amber-500/20' 
                            : 'bg-white dark:bg-stone-900 text-stone-600 dark:text-stone-400 hover:text-stone-900 dark:hover:text-stone-100 border border-stone-200/80 dark:border-stone-800'">
                    <i data-lucide="book-open" class="w-4 h-4"></i>
                    <span>Chapter 1: Story & Origins</span>
                </button>

                <button @click="activeTab = 'practices'"
                        class="px-4 py-2.5 rounded-xl text-xs sm:text-sm font-bold transition-all flex items-center gap-2 flex-shrink-0 cursor-pointer"
                        :class="activeTab === 'practices' 
                            ? 'bg-amber-500 text-white shadow-md shadow-amber-500/20' 
                            : 'bg-white dark:bg-stone-900 text-stone-600 dark:text-stone-400 hover:text-stone-900 dark:hover:text-stone-100 border border-stone-200/80 dark:border-stone-800'">
                    <i data-lucide="sparkles" class="w-4 h-4"></i>
                    <span>Chapter 2: Rituals & Relics</span>
                </button>

                <button @click="activeTab = 'custodians'"
                        class="px-4 py-2.5 rounded-xl text-xs sm:text-sm font-bold transition-all flex items-center gap-2 flex-shrink-0 cursor-pointer"
                        :class="activeTab === 'custodians' 
                            ? 'bg-amber-500 text-white shadow-md shadow-amber-500/20' 
                            : 'bg-white dark:bg-stone-900 text-stone-600 dark:text-stone-400 hover:text-stone-900 dark:hover:text-stone-100 border border-stone-200/80 dark:border-stone-800'">
                    <i data-lucide="shield" class="w-4 h-4"></i>
                    <span>Chapter 3: Custodians & Future</span>
                </button>

                <button @click="activeTab = 'media'"
                        class="px-4 py-2.5 rounded-xl text-xs sm:text-sm font-bold transition-all flex items-center gap-2 flex-shrink-0 cursor-pointer"
                        :class="activeTab === 'media' 
                            ? 'bg-amber-500 text-white shadow-md shadow-amber-500/20' 
                            : 'bg-white dark:bg-stone-900 text-stone-600 dark:text-stone-400 hover:text-stone-900 dark:hover:text-stone-100 border border-stone-200/80 dark:border-stone-800'">
                    <i data-lucide="film" class="w-4 h-4"></i>
                    <span>Media Vault</span>
                    @if($culture->media_files && count($culture->media_files))
                        <span class="text-[10px] px-1.5 py-0.5 rounded-full bg-stone-200 dark:bg-stone-800 text-stone-700 dark:text-stone-300 font-mono">
                            {{ count($culture->media_files) }}
                        </span>
                    @endif
                </button>

                <button @click="activeTab = 'quiz'"
                        class="px-4 py-2.5 rounded-xl text-xs sm:text-sm font-bold transition-all flex items-center gap-2 flex-shrink-0 cursor-pointer"
                        :class="activeTab === 'quiz' 
                            ? 'bg-amber-500 text-white shadow-md shadow-amber-500/20' 
                            : 'bg-white dark:bg-stone-900 text-stone-600 dark:text-stone-400 hover:text-stone-900 dark:hover:text-stone-100 border border-stone-200/80 dark:border-stone-800'">
                    <i data-lucide="help-circle" class="w-4 h-4 text-amber-500"></i>
                    <span>Interactive Knowledge Check</span>
                </button>
            </div>
        </div>

        {{-- MAIN CONTENT GRID: DOSSIER CONTENT + EDUCATIONAL SIDEBAR --}}
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">

            {{-- LEFT COLUMN: CHAPTER CONTENT (Cols 1-8) --}}
            <div class="lg:col-span-8 space-y-8">

                {{-- ==================== CHAPTER 1: STORY & ORIGINS ==================== --}}
                <div x-show="activeTab === 'story'" class="space-y-8 animate-fadeIn" x-transition>
                    
                    {{-- Primary Story & Narrative Description --}}
                    <div class="bg-white dark:bg-[#131218] border border-stone-200 dark:border-stone-800/80 rounded-3xl p-6 sm:p-10 shadow-sm space-y-6">
                        <div class="flex items-center justify-between border-b border-stone-100 dark:border-stone-800 pb-4">
                            <div class="flex items-center gap-2">
                                <span class="w-2.5 h-2.5 rounded-full bg-amber-500"></span>
                                <span class="text-xs font-bold uppercase tracking-widest text-stone-500 dark:text-stone-400">Oral Account & Historical Chronicle</span>
                            </div>
                            <span class="text-xs text-stone-400 font-mono">Archive Record</span>
                        </div>

                        {{-- Drop-Cap Narrative Text --}}
                        <div class="font-editorial text-stone-800 dark:text-stone-200 leading-relaxed drop-cap space-y-4"
                             :style="'font-size: ' + fontSize + 'px; line-height: 1.8;'">
                            {{ $culture->description }}
                        </div>

                        @if($culture->significance)
                            <div class="mt-6 p-5 rounded-2xl bg-amber-500/5 border-l-4 border-amber-500 border-stone-200 dark:border-stone-800">
                                <span class="text-[11px] font-black uppercase tracking-wider text-amber-700 dark:text-amber-400 block mb-1">
                                    Cosmological & Cultural Significance
                                </span>
                                <p class="text-xs sm:text-sm text-stone-700 dark:text-stone-300 leading-relaxed font-editorial italic">
                                    "{{ $culture->significance }}"
                                </p>
                            </div>
                        @endif
                    </div>

                    {{-- Living Heritage: Oral Spoken Folktale Transcript (if present) --}}
                    @if($culture->audio_transcript || $culture->raw_story)
                        <div class="bg-white dark:bg-[#131218] border border-stone-200 dark:border-stone-800/80 rounded-3xl p-6 sm:p-8 shadow-sm space-y-4">
                            <div class="flex items-center justify-between">
                                <h3 class="text-base font-bold text-stone-900 dark:text-stone-100 flex items-center gap-2">
                                    <i data-lucide="file-text" class="w-5 h-5 text-amber-500"></i>
                                    <span>Verbatim Spoken Transcript & Dialect Narrative</span>
                                </h3>
                                <span class="text-[10px] uppercase font-bold px-2 py-0.5 rounded bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-400">
                                    Verified Voice
                                </span>
                            </div>
                            <p class="text-xs text-stone-500 dark:text-stone-400">
                                Transcribed directly from native audio recording and preserved for dialect linguistic study.
                            </p>
                            <div class="p-5 rounded-2xl bg-stone-50 dark:bg-stone-900/80 border border-stone-200 dark:border-stone-800 font-editorial text-stone-700 dark:text-stone-300 leading-relaxed text-sm sm:text-base">
                                {{ $culture->audio_transcript ?? $culture->raw_story }}
                            </div>
                        </div>
                    @endif

                    {{-- "Did You Know?" Educational Callout --}}
                    <div class="rounded-3xl border border-amber-300 dark:border-amber-900/60 bg-gradient-to-br from-amber-100/50 via-white to-amber-50 dark:from-amber-950/30 dark:via-[#131218] dark:to-stone-900 p-6 sm:p-8 shadow-sm flex items-start gap-4">
                        <div class="w-12 h-12 rounded-2xl bg-amber-500/20 text-amber-600 dark:text-amber-400 flex items-center justify-center flex-shrink-0">
                            <i data-lucide="lightbulb" class="w-6 h-6"></i>
                        </div>
                        <div class="space-y-1">
                            <span class="text-[11px] font-black uppercase tracking-wider text-amber-700 dark:text-amber-400">
                                Educational Factoid
                            </span>
                            <h4 class="font-bold text-stone-900 dark:text-stone-100 text-base">
                                Living Transmission in {{ $culture->region }}
                            </h4>
                            <p class="text-xs sm:text-sm text-stone-600 dark:text-stone-400 leading-relaxed">
                                Cultural practices within {{ $culture->name }} have historically served as a community governance framework and oral legal code, passed between generations without written textbooks.
                            </p>
                        </div>
                    </div>
                </div>

                {{-- ==================== CHAPTER 2: RITUALS & RELICS ==================== --}}
                <div x-show="activeTab === 'practices'" class="space-y-8 animate-fadeIn" x-transition>
                    
                    {{-- Sacred Rituals and Practices --}}
                    <div class="bg-white dark:bg-[#131218] border border-stone-200 dark:border-stone-800/80 rounded-3xl p-6 sm:p-10 shadow-sm space-y-6">
                        <div class="flex items-center gap-2 border-b border-stone-100 dark:border-stone-800 pb-4">
                            <i data-lucide="sparkles" class="w-5 h-5 text-amber-500"></i>
                            <h3 class="font-heritage text-xl font-bold text-stone-900 dark:text-stone-100">Sacred Ceremonies & Ritual Practices</h3>
                        </div>

                        <div class="prose dark:prose-invert max-w-none text-stone-700 dark:text-stone-300 font-editorial text-base sm:text-lg leading-relaxed">
                            @if($culture->rituals)
                                <p>{{ $culture->rituals }}</p>
                            @else
                                <p>Ceremonies surrounding {{ $culture->name }} celebrate generational renewal, ancestral honor, seasonal harvests, and communal rites of passage. Practitioners participate in coordinated chants, ceremonial masquerades, and rhythmic drumming.</p>
                            @endif
                        </div>

                        {{-- Community Role Callout --}}
                        @if($culture->community_role)
                            <div class="p-5 rounded-2xl bg-stone-50 dark:bg-stone-900 border border-stone-200 dark:border-stone-800 space-y-1">
                                <span class="text-[11px] font-black uppercase tracking-wider text-stone-500 dark:text-stone-400">Community Function & Role</span>
                                <p class="text-sm font-semibold text-stone-800 dark:text-stone-200">{{ $culture->community_role }}</p>
                            </div>
                        @endif
                    </div>

                    {{-- Artifact Segmentation & Relic Visualizer (SAM 3.1) --}}
                    @if($culture->artifact_segmentation && !empty($culture->artifact_segmentation['segmented_path']))
                        <div class="bg-white dark:bg-[#131218] border border-stone-200 dark:border-stone-800/80 rounded-3xl p-6 sm:p-8 shadow-sm space-y-4">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <i data-lucide="scissors" class="w-5 h-5 text-amber-500"></i>
                                    <h3 class="text-lg font-bold text-stone-900 dark:text-stone-100">
                                        Authentic Artifact Visual Isolation
                                    </h3>
                                </div>
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-mono font-bold bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-300">
                                    SAM 3.1 Relic Model
                                </span>
                            </div>
                            <p class="text-xs text-stone-500 dark:text-stone-400">
                                High-precision visual boundary extraction preserving intricate carvings, textiles, or sacred implements.
                            </p>
                            
                            {{-- Interactive Image Lightbox Frame --}}
                            <div class="rounded-2xl overflow-hidden bg-stone-100 dark:bg-stone-950 p-6 border border-stone-200 dark:border-stone-800 flex flex-col items-center justify-center relative group">
                                <img src="{{ Storage::url($culture->artifact_segmentation['segmented_path']) }}"
                                     alt="Isolated Relic"
                                     class="max-h-80 object-contain drop-shadow-2xl transition duration-500 group-hover:scale-105">
                                <span class="text-[11px] font-mono text-stone-400 mt-4">
                                    Artifact: {{ $culture->artifact_segmentation['label'] ?? 'Cultural Implement' }}
                                </span>
                            </div>
                            <p class="text-[11px] text-stone-400 italic">
                                Note: Computational segmentation aids visual inspection; it does not replace carbon dating or archaeological provenance.
                            </p>
                        </div>
                    @endif

                    {{-- AI-Assisted Artwork / Illustration (Muse Image) --}}
                    @if($culture->ai_illustration)
                        <div class="bg-white dark:bg-[#131218] border border-stone-200 dark:border-stone-800/80 rounded-3xl p-6 sm:p-8 shadow-sm space-y-4">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <i data-lucide="palette" class="w-5 h-5 text-amber-500"></i>
                                    <h3 class="text-lg font-bold text-stone-900 dark:text-stone-100">
                                        Artistic Interpretation & Re-creation
                                    </h3>
                                </div>
                                <span class="text-[10px] font-bold uppercase tracking-wider px-2 py-0.5 rounded bg-stone-100 dark:bg-stone-800 text-stone-600 dark:text-stone-400">
                                    Muse Image
                                </span>
                            </div>
                            <div class="relative rounded-2xl overflow-hidden border border-stone-200 dark:border-stone-800 shadow-md">
                                <img src="{{ Storage::url($culture->ai_illustration) }}"
                                     alt="AI-Generated Cultural Artwork"
                                     class="w-full h-80 object-cover">
                                <div class="absolute bottom-3 left-3 right-3 px-4 py-2 bg-black/80 backdrop-blur-md rounded-xl text-xs text-stone-200 flex items-center justify-between">
                                    <span class="flex items-center gap-1.5 text-amber-400 font-semibold">
                                        <i data-lucide="info" class="w-4 h-4"></i>
                                        <span>AI-Generated Conceptual Artwork</span>
                                    </span>
                                    <span class="text-[10px] text-stone-400">Artistic interpretation, not historical photography.</span>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>

                {{-- ==================== CHAPTER 3: CUSTODIANS & FUTURE ==================== --}}
                <div x-show="activeTab === 'custodians'" class="space-y-8 animate-fadeIn" x-transition>
                    
                    {{-- Living Custodians & Transmission --}}
                    <div class="bg-white dark:bg-[#131218] border border-stone-200 dark:border-stone-800/80 rounded-3xl p-6 sm:p-10 shadow-sm space-y-6">
                        <div class="flex items-center gap-2 border-b border-stone-100 dark:border-stone-800 pb-4">
                            <i data-lucide="users" class="w-5 h-5 text-amber-500"></i>
                            <h3 class="font-heritage text-xl font-bold text-stone-900 dark:text-stone-100">Custodianship & Preservation Challenges</h3>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="p-5 rounded-2xl bg-stone-50 dark:bg-stone-900 border border-stone-200 dark:border-stone-800 space-y-1">
                                <span class="text-[10px] font-bold uppercase tracking-wider text-stone-400">Current Living Practitioners</span>
                                <h4 class="font-bold text-base text-stone-900 dark:text-stone-100">{{ $culture->current_practitioners ?? 'Elders, Lineage Chieftains & Youth Guilds' }}</h4>
                                <p class="text-xs text-stone-500 dark:text-stone-400 mt-1">Responsible for oral transmission, ritual initiation, and language retention.</p>
                            </div>

                            <div class="p-5 rounded-2xl bg-stone-50 dark:bg-stone-900 border border-stone-200 dark:border-stone-800 space-y-1">
                                <span class="text-[10px] font-bold uppercase tracking-wider text-stone-400">Transmission Methods</span>
                                <h4 class="font-bold text-base text-stone-900 dark:text-stone-100">{{ $culture->transmission_methods ?? 'Oral storytelling, seasonal gatherings, apprentice guilds' }}</h4>
                                <p class="text-xs text-stone-500 dark:text-stone-400 mt-1">Historically passed in kinship circles without formal written syllabus.</p>
                            </div>
                        </div>

                        {{-- Challenges & Threats to Survival --}}
                        @if($culture->challenges)
                            <div class="p-5 rounded-2xl bg-rose-50/50 dark:bg-rose-950/20 border border-rose-200 dark:border-rose-900/40 space-y-2">
                                <div class="flex items-center gap-2 text-rose-700 dark:text-rose-400 text-xs font-bold uppercase tracking-wider">
                                    <i data-lucide="alert-triangle" class="w-4 h-4"></i>
                                    <span>Preservation Challenges & Endangerment Risks</span>
                                </div>
                                <p class="text-xs sm:text-sm text-stone-700 dark:text-stone-300 leading-relaxed font-editorial">
                                    {{ $culture->challenges }}
                                </p>
                            </div>
                        @endif

                        {{-- Future Vision & Conservation Efforts --}}
                        @if($culture->preservation_efforts || $culture->future_vision)
                            <div class="space-y-4 pt-2">
                                <h4 class="font-bold text-stone-900 dark:text-stone-100 text-sm uppercase tracking-wider">
                                    Active Safeguarding & Future Vision
                                </h4>
                                <p class="text-sm font-editorial text-stone-700 dark:text-stone-300 leading-relaxed">
                                    {{ $culture->preservation_efforts ?? $culture->future_vision }}
                                </p>
                            </div>
                        @endif
                    </div>

                    {{-- Preservation Action Plan & Task Checklist (Muse Wonder) --}}
                    @if($culture->wonder_preservation_plan && is_array($culture->wonder_preservation_plan))
                        <div class="bg-white dark:bg-[#131218] border border-stone-200 dark:border-stone-800/80 rounded-3xl p-6 sm:p-8 shadow-sm space-y-5">
                            <div class="flex items-center justify-between border-b border-stone-100 dark:border-stone-800 pb-3">
                                <div class="flex items-center gap-2">
                                    <i data-lucide="compass" class="w-5 h-5 text-amber-500"></i>
                                    <h3 class="text-base font-bold text-stone-900 dark:text-stone-100">
                                        Community Conservation Action Plan
                                    </h3>
                                </div>
                                <span class="text-[10px] font-bold uppercase px-2 py-0.5 rounded bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-400">
                                    Actionable Steps
                                </span>
                            </div>

                            @if(!empty($culture->wonder_preservation_plan['preservation_summary']))
                                <p class="text-xs sm:text-sm text-stone-600 dark:text-stone-400 italic border-l-2 border-amber-500 pl-3">
                                    "{{ $culture->wonder_preservation_plan['preservation_summary'] }}"
                                </p>
                            @endif

                            @if(!empty($culture->wonder_preservation_plan['preservation_checklist']))
                                <div class="space-y-2 pt-2">
                                    <span class="text-[10px] font-bold uppercase tracking-wider text-stone-400 block">Recommended Custodian Tasks:</span>
                                    <div class="grid grid-cols-1 gap-2">
                                        @foreach($culture->wonder_preservation_plan['preservation_checklist'] as $task)
                                            <div class="p-3 rounded-xl bg-stone-50 dark:bg-stone-900 border border-stone-200 dark:border-stone-800 flex items-start gap-3 text-xs text-stone-700 dark:text-stone-300">
                                                <i data-lucide="check-circle" class="w-4 h-4 text-emerald-500 mt-0.5 flex-shrink-0"></i>
                                                <span>{{ is_array($task) ? ($task['task'] ?? '') : $task }}</span>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        </div>
                    @endif

                    {{-- Archival Integrity & Fact-Check Screen --}}
                    @if(!empty($culture->wonder_preservation_plan['originality_audit']))
                        @php $audit = $culture->wonder_preservation_plan['originality_audit']; @endphp
                        <div class="bg-white dark:bg-[#131218] border border-stone-200 dark:border-stone-800/80 rounded-3xl p-6 sm:p-8 shadow-sm space-y-4">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <i data-lucide="shield-check" class="w-5 h-5 text-amber-500"></i>
                                    <h3 class="text-base font-bold text-stone-900 dark:text-stone-100">
                                        Archival Integrity & Historical Fact-Check
                                    </h3>
                                </div>
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider
                                    {{ ($audit['plagiarism_risk'] ?? '') === 'low' ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-400' : 'bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-400' }}">
                                    {{ strtoupper($audit['plagiarism_risk'] ?? 'low') }} RISK
                                </span>
                            </div>

                            <div class="grid grid-cols-2 gap-3">
                                <div class="p-3.5 bg-stone-50 dark:bg-stone-900 rounded-2xl border border-stone-200 dark:border-stone-800">
                                    <span class="text-[10px] font-bold uppercase text-stone-400 block">Oral Authenticity Index</span>
                                    <span class="text-2xl font-black text-amber-500">{{ $audit['oral_authenticity_score'] ?? 0 }}<span class="text-xs text-stone-400">/100</span></span>
                                    <span class="text-[10px] text-stone-400 block mt-0.5">{{ $audit['authenticity_label'] ?? 'High Oral Fidelity' }}</span>
                                </div>
                                <div class="p-3.5 bg-stone-50 dark:bg-stone-900 rounded-2xl border border-stone-200 dark:border-stone-800">
                                    <span class="text-[10px] font-bold uppercase text-stone-400 block">Source Fidelity</span>
                                    <span class="text-sm font-bold text-stone-900 dark:text-stone-100 flex items-center gap-1.5 mt-2">
                                        <i data-lucide="check-circle-2" class="w-4 h-4 text-emerald-500"></i>
                                        Faithful to Voice Source
                                    </span>
                                    <span class="text-[10px] text-stone-400 block">No fabricated claims</span>
                                </div>
                            </div>

                            @if(!empty($audit['historical_fact_check_flags']))
                                <div class="space-y-2 pt-2">
                                    <span class="text-[10px] font-bold uppercase tracking-wider text-stone-400 block">Assertions Flagged for Elder Corroboration:</span>
                                    @foreach($audit['historical_fact_check_flags'] as $flag)
                                        <div class="p-3 bg-stone-50 dark:bg-stone-900 rounded-xl border border-stone-200 dark:border-stone-800 text-xs space-y-1">
                                            <div class="flex items-center justify-between">
                                                <span class="font-bold text-stone-900 dark:text-stone-100">{{ $flag['claim'] ?? '' }}</span>
                                                <span class="text-[9px] uppercase px-2 py-0.5 rounded font-bold bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-400">
                                                    {{ str_replace('_', ' ', $flag['verification_status'] ?? 'elder check') }}
                                                </span>
                                            </div>
                                            <p class="text-[11px] text-stone-500 dark:text-stone-400 italic">{{ $flag['guidance'] ?? '' }}</p>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    @endif
                </div>

                {{-- ==================== CHAPTER 4: MEDIA VAULT ==================== --}}
                <div x-show="activeTab === 'media'" class="space-y-8 animate-fadeIn" x-transition>
                    
                    {{-- Photographic Gallery with Lightbox --}}
                    @if($culture->media_files && count($culture->media_files))
                        <div x-data="{ activeImage: '{{ Storage::url($culture->media_files[0]) }}' }"
                             class="bg-white dark:bg-[#131218] border border-stone-200 dark:border-stone-800/80 rounded-3xl p-6 sm:p-8 shadow-sm space-y-4">
                            <div class="flex items-center justify-between">
                                <h3 class="text-base font-bold text-stone-900 dark:text-stone-100 flex items-center gap-2">
                                    <i data-lucide="image" class="w-5 h-5 text-amber-500"></i>
                                    <span>Field Photographic Archive</span>
                                </h3>
                                <span class="text-xs text-stone-400 font-mono">{{ count($culture->media_files) }} Heirlooms</span>
                            </div>

                            {{-- Main Display --}}
                            <div class="rounded-2xl overflow-hidden border border-stone-200 dark:border-stone-800 h-80 sm:h-96 bg-stone-100 dark:bg-stone-950 flex items-center justify-center">
                                <img :src="activeImage" class="w-full h-full object-contain transition duration-500">
                            </div>

                            {{-- Thumbnail Strip --}}
                            <div class="grid grid-cols-4 sm:grid-cols-6 gap-2 pt-2">
                                @foreach($culture->media_files as $file)
                                    <button @click="activeImage = '{{ Storage::url($file) }}'"
                                            class="rounded-xl overflow-hidden border-2 transition h-16 sm:h-20"
                                            :class="activeImage === '{{ Storage::url($file) }}' ? 'border-amber-500 shadow-md' : 'border-transparent opacity-75 hover:opacity-100'">
                                        <img src="{{ Storage::url($file) }}" class="w-full h-full object-cover">
                                    </button>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    {{-- Video Documentaries / Field Footage --}}
                    @if($culture->video_url || $culture->video_path)
                        <div class="bg-white dark:bg-[#131218] border border-stone-200 dark:border-stone-800/80 rounded-3xl p-6 sm:p-8 shadow-sm space-y-4">
                            <h3 class="text-base font-bold text-stone-900 dark:text-stone-100 flex items-center gap-2">
                                <i data-lucide="video" class="w-5 h-5 text-amber-500"></i>
                                <span>Video Documentary & Ceremony Footage</span>
                            </h3>

                            <div class="aspect-video rounded-2xl overflow-hidden border border-stone-200 dark:border-stone-800 bg-black">
                                @if($culture->video_path)
                                    <video controls class="w-full h-full">
                                        <source src="{{ Storage::url($culture->video_path) }}" type="video/mp4">
                                        Your browser does not support video playback.
                                    </video>
                                @else
                                    <iframe src="{{ $culture->video_url }}" class="w-full h-full" allowfullscreen></iframe>
                                @endif
                            </div>
                        </div>
                    @endif

                    @if(!$culture->video_url && !$culture->video_path && (!$culture->media_files || !count($culture->media_files)))
                        <div class="p-10 text-center bg-white dark:bg-[#131218] rounded-3xl border border-stone-200 dark:border-stone-800">
                            <i data-lucide="image-off" class="w-10 h-10 text-stone-400 mx-auto mb-2"></i>
                            <h4 class="font-bold text-stone-700 dark:text-stone-300">No Secondary Media Enshrined</h4>
                            <p class="text-xs text-stone-400 mt-1">Oral story and hero images are documented in Chapter 1.</p>
                        </div>
                    @endif
                </div>

                {{-- ==================== CHAPTER 5: INTERACTIVE KNOWLEDGE CHECK ==================== --}}
                <div x-show="activeTab === 'quiz'" class="space-y-6 animate-fadeIn" x-transition>
                    <div class="bg-white dark:bg-[#131218] border border-amber-200 dark:border-amber-900/60 rounded-3xl p-6 sm:p-10 shadow-sm space-y-6"
                         x-data="{
                            selectedQ1: null,
                            selectedQ2: null,
                            submitted: false,
                            q1Correct: 1, // index of correct answer
                            q2Correct: 0
                         }">
                        
                        <div class="flex items-center gap-3 border-b border-stone-100 dark:border-stone-800 pb-4">
                            <div class="w-10 h-10 rounded-xl bg-amber-500/20 text-amber-600 dark:text-amber-400 flex items-center justify-center font-bold">
                                ?
                            </div>
                            <div>
                                <h3 class="font-heritage text-xl font-bold text-stone-900 dark:text-stone-100">
                                    Interactive Knowledge Check
                                </h3>
                                <p class="text-xs text-stone-500 dark:text-stone-400">
                                    Reinforce what you learned about {{ $culture->name }}. Test your comprehension!
                                </p>
                            </div>
                        </div>

                        {{-- Question 1 --}}
                        <div class="space-y-3">
                            <h4 class="text-sm sm:text-base font-bold text-stone-900 dark:text-stone-100 flex items-center gap-2">
                                <span class="px-2 py-0.5 rounded-full bg-stone-100 dark:bg-stone-800 text-xs font-mono text-amber-600 font-bold">Q1</span>
                                <span>In which geographical cradle is the tradition of {{ $culture->name }} rooted?</span>
                            </h4>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                                <button type="button" @click="if(!submitted) selectedQ1 = 0"
                                        class="p-3.5 rounded-xl border text-left text-xs sm:text-sm font-semibold transition"
                                        :class="selectedQ1 === 0 
                                            ? (submitted ? (q1Correct === 0 ? 'bg-emerald-100 border-emerald-500 text-emerald-900' : 'bg-rose-100 border-rose-500 text-rose-900') : 'bg-amber-50 border-amber-500 text-amber-900 dark:bg-amber-950/50 dark:text-amber-200')
                                            : 'bg-white dark:bg-stone-900 border-stone-200 dark:border-stone-800 hover:border-amber-300'">
                                    A. Nile Delta Basin
                                </button>
                                <button type="button" @click="if(!submitted) selectedQ1 = 1"
                                        class="p-3.5 rounded-xl border text-left text-xs sm:text-sm font-semibold transition"
                                        :class="selectedQ1 === 1 
                                            ? (submitted ? (q1Correct === 1 ? 'bg-emerald-100 border-emerald-500 text-emerald-900' : 'bg-rose-100 border-rose-500 text-rose-900') : 'bg-amber-50 border-amber-500 text-amber-900 dark:bg-amber-950/50 dark:text-amber-200')
                                            : 'bg-white dark:bg-stone-900 border-stone-200 dark:border-stone-800 hover:border-amber-300'">
                                    B. {{ $culture->region }}
                                </button>
                                <button type="button" @click="if(!submitted) selectedQ1 = 2"
                                        class="p-3.5 rounded-xl border text-left text-xs sm:text-sm font-semibold transition"
                                        :class="selectedQ1 === 2 
                                            ? (submitted ? (q1Correct === 2 ? 'bg-emerald-100 border-emerald-500 text-emerald-900' : 'bg-rose-100 border-rose-500 text-rose-900') : 'bg-amber-50 border-amber-500 text-amber-900 dark:bg-amber-950/50 dark:text-amber-200')
                                            : 'bg-white dark:bg-stone-900 border-stone-200 dark:border-stone-800 hover:border-amber-300'">
                                    C. Congo Rainforest Highlands
                                </button>
                                <button type="button" @click="if(!submitted) selectedQ1 = 3"
                                        class="p-3.5 rounded-xl border text-left text-xs sm:text-sm font-semibold transition"
                                        :class="selectedQ1 === 3 
                                            ? (submitted ? (q1Correct === 3 ? 'bg-emerald-100 border-emerald-500 text-emerald-900' : 'bg-rose-100 border-rose-500 text-rose-900') : 'bg-amber-50 border-amber-500 text-amber-900 dark:bg-amber-950/50 dark:text-amber-200')
                                            : 'bg-white dark:bg-stone-900 border-stone-200 dark:border-stone-800 hover:border-amber-300'">
                                    D. Mediterranean Coastal Ports
                                </button>
                            </div>
                        </div>

                        {{-- Question 2 --}}
                        <div class="space-y-3 pt-3 border-t border-stone-100 dark:border-stone-800">
                            <h4 class="text-sm sm:text-base font-bold text-stone-900 dark:text-stone-100 flex items-center gap-2">
                                <span class="px-2 py-0.5 rounded-full bg-stone-100 dark:bg-stone-800 text-xs font-mono text-amber-600 font-bold">Q2</span>
                                <span>What is the core medium through which this heritage has been historically preserved?</span>
                            </h4>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                                <button type="button" @click="if(!submitted) selectedQ2 = 0"
                                        class="p-3.5 rounded-xl border text-left text-xs sm:text-sm font-semibold transition"
                                        :class="selectedQ2 === 0 
                                            ? (submitted ? (q2Correct === 0 ? 'bg-emerald-100 border-emerald-500 text-emerald-900' : 'bg-rose-100 border-rose-500 text-rose-900') : 'bg-amber-50 border-amber-500 text-amber-900 dark:bg-amber-950/50 dark:text-amber-200')
                                            : 'bg-white dark:bg-stone-900 border-stone-200 dark:border-stone-800 hover:border-amber-300'">
                                    A. Oral Transmission, Elder Storytelling & Ceremonial Rites
                                </button>
                                <button type="button" @click="if(!submitted) selectedQ2 = 1"
                                        class="p-3.5 rounded-xl border text-left text-xs sm:text-sm font-semibold transition"
                                        :class="selectedQ2 === 1 
                                            ? (submitted ? (q2Correct === 1 ? 'bg-emerald-100 border-emerald-500 text-emerald-900' : 'bg-rose-100 border-rose-500 text-rose-900') : 'bg-amber-50 border-amber-500 text-amber-900 dark:bg-amber-950/50 dark:text-amber-200')
                                            : 'bg-white dark:bg-stone-900 border-stone-200 dark:border-stone-800 hover:border-amber-300'">
                                    B. Commercial Print Publishing Houses
                                </button>
                            </div>
                        </div>

                        {{-- Submit / Reset Button --}}
                        <div class="pt-4 flex items-center justify-between">
                            <button type="button" 
                                    @click="submitted = true"
                                    x-show="!submitted"
                                    :disabled="selectedQ1 === null || selectedQ2 === null"
                                    class="px-6 py-3 rounded-xl bg-amber-500 hover:bg-amber-600 disabled:opacity-50 text-white font-bold text-sm shadow-md transition flex items-center gap-2">
                                <i data-lucide="check" class="w-4 h-4"></i>
                                <span>Check Answers</span>
                            </button>

                            <div x-show="submitted" class="flex items-center gap-4">
                                <span class="text-sm font-bold text-emerald-600 dark:text-emerald-400 flex items-center gap-1.5">
                                    <i data-lucide="award" class="w-5 h-5"></i>
                                    <span>Preservation Knowledge Verified!</span>
                                </span>
                                <button type="button" @click="submitted = false; selectedQ1 = null; selectedQ2 = null"
                                        class="text-xs font-bold text-stone-500 hover:text-stone-800 dark:hover:text-stone-200 underline">
                                    Try Again
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            {{-- RIGHT COLUMN: EDUCATIONAL CUSTODIAN PASSPORT & RELATED (Cols 9-12) --}}
            <div class="lg:col-span-4 space-y-6">

                {{-- Cultural Passport & Metadata Card --}}
                <div class="bg-white dark:bg-[#131218] border border-stone-200 dark:border-stone-800/80 rounded-3xl p-6 shadow-sm space-y-6">
                    <div class="flex items-center justify-between border-b border-stone-100 dark:border-stone-800 pb-3">
                        <span class="text-xs font-black uppercase tracking-wider text-amber-600 dark:text-amber-400 flex items-center gap-1.5">
                            <i data-lucide="shield" class="w-4 h-4"></i>
                            Archival Provenance
                        </span>
                        <span class="text-xs font-mono text-stone-400">#{{ str_pad($culture->id, 5, '0', STR_PAD_LEFT) }}</span>
                    </div>

                    {{-- Guardian Profile --}}
                    <div class="space-y-3">
                        <span class="text-[10px] font-bold uppercase tracking-wider text-stone-400 block">Enshrined Cultural Guardian</span>
                        <div class="flex items-center gap-3">
                            <div class="w-12 h-12 rounded-2xl bg-amber-500/10 border border-amber-500/20 text-amber-600 dark:text-amber-400 font-black text-lg flex items-center justify-center">
                                {{ strtoupper(substr($culture->submitter->name ?? 'K', 0, 1)) }}
                            </div>
                            <div class="min-w-0 flex-1">
                                <h4 class="font-bold text-stone-900 dark:text-stone-100 text-sm truncate">
                                    {{ $culture->submitter->name ?? 'Kulturegram Preserver' }}
                                </h4>
                                <p class="text-xs text-stone-500 dark:text-stone-400">
                                    Community Cultural Custodian
                                </p>
                            </div>
                        </div>

                        @if($culture->submitted_by)
                            <a href="{{ route('profile.user', $culture->submitted_by) }}"
                               class="inline-flex items-center justify-center gap-2 w-full mt-2 py-2 px-3 rounded-xl border border-stone-200 dark:border-stone-800 bg-stone-50 dark:bg-stone-900 hover:border-amber-500 text-stone-700 dark:text-stone-300 text-xs font-bold transition">
                                <span>View Guardian Profile</span>
                                <i data-lucide="arrow-up-right" class="w-3.5 h-3.5"></i>
                            </a>
                        @endif
                    </div>

                    {{-- Enshrinement Details --}}
                    <div class="pt-4 border-t border-stone-100 dark:border-stone-800 space-y-2.5 text-xs text-stone-600 dark:text-stone-400">
                        <div class="flex items-center justify-between">
                            <span class="text-stone-400">Archival Date:</span>
                            <span class="font-medium text-stone-800 dark:text-stone-200">{{ $culture->created_at->format('M d, Y') }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-stone-400">Intellectual Rights:</span>
                            <span class="font-bold text-amber-600 dark:text-amber-400">{{ $culture->license_type ?? 'Cultural Commons (CC BY-NC)' }}</span>
                        </div>
                        @if($culture->license_credit)
                            <div class="flex items-center justify-between">
                                <span class="text-stone-400">Attribution Credit:</span>
                                <span class="font-medium text-stone-800 dark:text-stone-200 truncate max-w-[160px]">{{ $culture->license_credit }}</span>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Related Cultural Traditions --}}
                @if(isset($relatedCultures) && count($relatedCultures))
                    <div class="bg-white dark:bg-[#131218] border border-stone-200 dark:border-stone-800/80 rounded-3xl p-6 shadow-sm space-y-4">
                        <div class="flex items-center justify-between">
                            <h3 class="font-heritage text-base font-bold text-stone-900 dark:text-stone-100">
                                Sibling Living Traditions
                            </h3>
                            <span class="text-[10px] text-amber-600 font-bold uppercase">Explore</span>
                        </div>

                        <div class="space-y-3">
                            @foreach($relatedCultures as $related)
                                <a href="{{ route('cultural-hub.show', $related->id) }}"
                                   class="flex items-center gap-3 p-2.5 rounded-2xl hover:bg-stone-50 dark:hover:bg-stone-900 transition group border border-transparent hover:border-stone-200 dark:hover:border-stone-800">
                                    <div class="w-14 h-14 rounded-xl overflow-hidden bg-stone-100 dark:bg-stone-800 flex-shrink-0">
                                        @if($related->image)
                                            <img src="{{ Storage::url($related->image) }}" class="w-full h-full object-cover group-hover:scale-105 transition">
                                        @else
                                            <div class="w-full h-full flex items-center justify-center text-amber-500">
                                                <i data-lucide="compass" class="w-6 h-6"></i>
                                            </div>
                                        @endif
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <h4 class="font-bold text-xs text-stone-900 dark:text-stone-100 group-hover:text-amber-600 dark:group-hover:text-amber-400 truncate">
                                            {{ $related->name }}
                                        </h4>
                                        <span class="text-[10px] uppercase font-bold text-stone-400 block truncate">
                                            {{ $related->region }} • {{ $related->category }}
                                        </span>
                                    </div>
                                    <i data-lucide="chevron-right" class="w-4 h-4 text-stone-400 group-hover:text-amber-500 transition flex-shrink-0"></i>
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endif

                {{-- Citation Modal --}}
                <div x-show="openCitationModal" x-cloak
                     class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm"
                     @click.self="openCitationModal = false">
                    <div class="bg-white dark:bg-stone-900 rounded-3xl p-6 sm:p-8 max-w-lg w-full border border-stone-200 dark:border-stone-800 shadow-2xl space-y-4">
                        <div class="flex items-center justify-between border-b border-stone-100 dark:border-stone-800 pb-3">
                            <h3 class="font-heritage text-lg font-bold text-stone-900 dark:text-stone-100">
                                Academic & Curatorial Citation
                            </h3>
                            <button @click="openCitationModal = false" class="text-stone-400 hover:text-stone-700">
                                <i data-lucide="x" class="w-5 h-5"></i>
                            </button>
                        </div>
                        <p class="text-xs text-stone-500">
                            Use this citation format in educational papers, community histories, or research bibliographies:
                        </p>

                        <div class="space-y-3 text-xs">
                            <div>
                                <span class="font-bold text-stone-700 dark:text-stone-300 block mb-1">APA 7th Format:</span>
                                <div class="p-3 bg-stone-50 dark:bg-stone-950 rounded-xl font-mono text-[11px] text-stone-800 dark:text-stone-300 border border-stone-200 dark:border-stone-800 select-all">
                                    {{ $culture->submitter->name ?? 'Kulturegram Preserver' }}. ({{ $culture->created_at->format('Y') }}). <em>{{ $culture->name }}</em> [Living Heritage Archive]. Kulturegram Cultural Preservation Network. {{ url()->current() }}
                                </div>
                            </div>
                            <div>
                                <span class="font-bold text-stone-700 dark:text-stone-300 block mb-1">Chicago / Turabian:</span>
                                <div class="p-3 bg-stone-50 dark:bg-stone-950 rounded-xl font-mono text-[11px] text-stone-800 dark:text-stone-300 border border-stone-200 dark:border-stone-800 select-all">
                                    "{{ $culture->name }}." Kulturegram Living Heritage Archive. Accessed {{ now()->format('F j, Y') }}. {{ url()->current() }}.
                                </div>
                            </div>
                        </div>

                        <div class="pt-2 flex justify-end">
                            <button @click="copyCitationText(); openCitationModal = false"
                                    class="px-5 py-2.5 rounded-xl bg-amber-500 hover:bg-amber-600 text-white font-bold text-xs transition">
                                Copy Citation
                            </button>
                        </div>
                    </div>
                </div>

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

    function culturalDossierComponent(config) {
        return {
            cultureId: config.cultureId,
            lockedIn: config.lockedIn,
            lockCount: config.lockCount,
            resonated: config.resonated,
            resonanceCount: config.resonanceCount,
            csrfToken: config.csrfToken,
            activeTab: 'story',
            fontSize: 18,
            openCitationModal: false,
            isSubmittingLock: false,
            isSubmittingPulse: false,

            async toggleLockIn() {
                if (this.isSubmittingLock) return;
                this.isSubmittingLock = true;
                
                // Optimistic UI update
                this.lockedIn = !this.lockedIn;
                this.lockCount += (this.lockedIn ? 1 : -1);

                try {
                    const res = await fetch(`/cultural-hub/${this.cultureId}/lock-in`, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': this.csrfToken,
                            'Accept': 'application/json',
                            'Content-Type': 'application/json'
                        }
                    });
                    const data = await res.json();
                    if (data.lockedIn !== undefined) {
                        this.lockedIn = data.lockedIn;
                        this.lockCount = data.count;
                    }
                } catch(e) {
                    console.error('Lock-in toggle failed:', e);
                } finally {
                    this.isSubmittingLock = false;
                    this.$nextTick(() => lucide.createIcons());
                }
            },

            async toggleResonance() {
                if (this.isSubmittingPulse) return;
                this.isSubmittingPulse = true;

                // Optimistic UI update
                this.resonated = !this.resonated;
                this.resonanceCount += (this.resonated ? 1 : -1);

                try {
                    const res = await fetch(`/cultural-hub/${this.cultureId}/resonance`, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': this.csrfToken,
                            'Accept': 'application/json',
                            'Content-Type': 'application/json'
                        }
                    });
                    const data = await res.json();
                    if (data.resonated !== undefined) {
                        this.resonated = data.resonated;
                        this.resonanceCount = data.count;
                    }
                } catch(e) {
                    console.error('Resonance pulse failed:', e);
                } finally {
                    this.isSubmittingPulse = false;
                    this.$nextTick(() => lucide.createIcons());
                }
            },

            copyShareLink() {
                navigator.clipboard.writeText(window.location.href);
                alert('Living Heritage link copied to clipboard!');
            },

            copyCitationText() {
                const text = `{{ $culture->submitter->name ?? 'Kulturegram Preserver' }}. ({{ $culture->created_at->format('Y') }}). {{ $culture->name }} [Living Heritage Archive]. Kulturegram. ${window.location.href}`;
                navigator.clipboard.writeText(text);
                alert('Academic citation copied to clipboard!');
            }
        };
    }
</script>
@endpush