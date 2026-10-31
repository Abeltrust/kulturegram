@extends('layouts.app')

@section('title', $culture->name . ' - Cultural Archive Record - Kulturegram')

@push('styles')
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@600;700;800;900&family=Newsreader:ital,opsz,wght@0,6..72,400;0,6..72,500;0,6..72,600;0,6..72,700;1,6..72,400;1,6..72,600&display=swap" rel="stylesheet">

<style>
    .font-archive-title {
        font-family: 'Cinzel', serif;
    }
    .font-archive-body {
        font-family: 'Newsreader', Georgia, serif;
    }
    .archive-drop-cap::first-letter {
        font-family: 'Cinzel', serif;
        float: left;
        font-size: 3.4rem;
        line-height: 0.85;
        padding-top: 4px;
        padding-right: 12px;
        padding-bottom: 2px;
        color: #d97706;
        font-weight: 800;
    }
    @media (max-width: 640px) {
        .archive-drop-cap::first-letter {
            font-size: 2.2rem;
            line-height: 0.9;
            padding-right: 8px;
        }
    }
</style>
@endpush

@section('content')
<div class="min-h-screen bg-[#faf8f5] dark:bg-[#0c0c10] text-stone-900 dark:text-stone-100 transition-colors duration-300"
     x-data="culturalArchiveDossier({
        cultureId: {{ $culture->id }},
        lockedIn: {{ auth()->check() && auth()->user()->hasLockedIn($culture) ? 'true' : 'false' }},
        lockCount: {{ $culture->locked_in_count }},
        resonated: {{ auth()->check() && auth()->user()->hasResonated($culture) ? 'true' : 'false' }},
        resonanceCount: {{ $culture->resonance_count }},
        csrfToken: '{{ csrf_token() }}',
        activeSection: '{{ request('section', 'overview') }}', mediaTypeFilter: 'all', mediaSectionFilter: 'all'
     })">

    {{-- Top Archival Navigation & Utility Bar --}}
    <header class="relative sm:sticky sm:top-16 z-30 backdrop-blur-md bg-white/95 dark:bg-[#121218]/95 border-b border-stone-200/80 dark:border-stone-800/80 transition-colors shadow-sm">
        <div class="max-w-7xl mx-auto px-3 sm:px-6 lg:px-8 py-2.5 flex items-center justify-between gap-2 sm:gap-4">
            
            {{-- Breadcrumb & Stable Record ID --}}
            <div class="flex items-center gap-1.5 overflow-hidden min-w-0 flex-1 sm:flex-initial">
                <a href="{{ route('cultural-hub.index') }}" 
                   class="text-xs font-bold uppercase tracking-wider text-stone-500 hover:text-amber-600 dark:hover:text-amber-400 transition flex items-center gap-1 flex-shrink-0">
                    <i data-lucide="arrow-left" class="w-3.5 h-3.5"></i>
                    <span class="hidden sm:inline">Cultural Archive</span>
                    <span class="sm:hidden">Archive</span>
                </a>
                <span class="text-stone-300 dark:text-stone-700">/</span>
                <span class="text-xs font-mono font-bold text-amber-700 dark:text-amber-400 bg-amber-500/10 px-1.5 py-0.5 rounded border border-amber-500/20 flex-shrink-0">
                    #CR-{{ str_pad($culture->id, 5, '0', STR_PAD_LEFT) }}
                </span>
                <span class="text-stone-300 dark:text-stone-700 hidden md:inline">/</span>
                <span class="text-xs font-semibold text-stone-700 dark:text-stone-300 truncate hidden md:inline">
                    {{ $culture->name }}
                </span>
            </div>

            {{-- Archival Actions & Presence --}}
            <div class="flex items-center gap-1.5 sm:gap-2 flex-shrink-0">
                {{-- Text Size Adjustment (Desktop) --}}
                <div class="hidden md:flex items-center rounded-lg border border-stone-200 dark:border-stone-800 bg-stone-50 dark:bg-stone-900 p-0.5 text-xs font-bold">
                    <button @click="fontSize = Math.max(15, fontSize - 1)" 
                            class="px-2 py-1 text-stone-600 dark:text-stone-400 hover:text-amber-600 dark:hover:text-amber-400" 
                            title="Decrease reading text size">A-</button>
                    <span class="px-1 text-[10px] text-stone-400" x-text="fontSize + 'px'"></span>
                    <button @click="fontSize = Math.min(22, fontSize + 1)" 
                            class="px-2 py-1 text-stone-600 dark:text-stone-400 hover:text-amber-600 dark:hover:text-amber-400" 
                            title="Increase reading text size">A+</button>
                </div>

                {{-- Academic Citation Modal Trigger --}}
                <button @click="openCitationModal = true"
                        class="px-2.5 sm:px-3 py-1.5 rounded-lg border border-stone-200 dark:border-stone-800 bg-white dark:bg-stone-900 text-stone-700 dark:text-stone-300 text-xs font-semibold hover:border-amber-500 hover:text-amber-600 dark:hover:text-amber-400 transition flex items-center gap-1.5 shadow-sm">
                    <i data-lucide="quote" class="w-3.5 h-3.5 text-amber-500"></i>
                    <span class="hidden md:inline">Cite Record</span>
                </button>

                {{-- Guardian Edit Access --}}
                @if(auth()->check() && (auth()->id() === $culture->submitted_by || auth()->user()->is_admin))
                    <a href="{{ route('cultural-hub.edit', $culture->id) }}"
                       class="px-2.5 sm:px-3 py-1.5 rounded-lg border border-stone-200 dark:border-stone-800 bg-white dark:bg-stone-900 text-stone-700 dark:text-stone-300 text-xs font-semibold hover:border-amber-500 hover:text-amber-600 transition flex items-center gap-1 shadow-sm">
                        <i data-lucide="edit-3" class="w-3.5 h-3.5 text-amber-500"></i>
                        <span class="hidden sm:inline">Edit Record</span>
                    </a>
                @endif

                {{-- Lock-In Presence --}}
                @auth
                    <button @click="toggleLockIn()"
                            :disabled="isSubmittingLock"
                            class="px-2.5 sm:px-3 py-1.5 rounded-lg text-xs font-bold transition flex items-center gap-1.5 shadow-sm"
                            :class="lockedIn 
                                ? 'bg-amber-500 text-white shadow-amber-500/25 hover:bg-amber-600' 
                                : 'bg-white dark:bg-stone-900 border border-stone-200 dark:border-stone-800 text-stone-700 dark:text-stone-300 hover:border-amber-500'">
                        <i data-lucide="bookmark" class="w-3.5 h-3.5" :class="lockedIn ? 'text-white' : 'text-amber-500'"></i>
                        <span class="hidden sm:inline" x-text="lockedIn ? 'Preserved' : 'Preserve'"></span>
                        <span class="text-[10px] px-1.5 py-0.2 rounded-full font-mono"
                              :class="lockedIn ? 'bg-amber-600 text-white' : 'bg-stone-100 dark:bg-stone-800 text-stone-600 dark:text-stone-400'"
                              x-text="lockCount"></span>
                    </button>
                @else
                    <a href="{{ route('login') }}"
                       class="px-2.5 sm:px-3 py-1.5 rounded-lg text-xs font-bold bg-white dark:bg-stone-900 border border-stone-200 dark:border-stone-800 text-stone-700 dark:text-stone-300 hover:border-amber-500 flex items-center gap-1.5">
                        <i data-lucide="bookmark" class="w-3.5 h-3.5 text-amber-500"></i>
                        <span class="hidden sm:inline">Preserve</span>
                        <span class="text-[10px] px-1.5 py-0.2 bg-stone-100 dark:bg-stone-800 rounded-full font-mono">{{ $culture->locked_in_count }}</span>
                    </a>
                @endauth

                {{-- Pulse Appreciation --}}
                @auth
                    <button @click="toggleResonance()"
                            :disabled="isSubmittingPulse"
                            class="px-2 sm:px-3 py-1.5 rounded-lg text-xs font-bold transition flex items-center gap-1.5 border"
                            :class="resonated 
                                ? 'bg-orange-500/10 border-orange-500 text-orange-600 dark:text-orange-400' 
                                : 'bg-white dark:bg-stone-900 border-stone-200 dark:border-stone-800 text-stone-700 dark:text-stone-300 hover:border-orange-500'">
                        <i data-lucide="heart" class="w-3.5 h-3.5" :class="resonated ? 'text-orange-500 fill-orange-500' : 'text-stone-400'"></i>
                        <span class="text-[10px] font-mono" x-text="resonanceCount"></span>
                    </button>
                @endauth

                {{-- Share --}}
                <button @click="copyShareLink()"
                        class="p-1.5 rounded-lg border border-stone-200 dark:border-stone-800 bg-white dark:bg-stone-900 text-stone-600 dark:text-stone-400 hover:text-amber-600 transition flex-shrink-0"
                        title="Copy Archival Link">
                    <i data-lucide="share-2" class="w-4 h-4"></i>
                </button>
            </div>
        </div>
    </header>

    {{-- Main Archive Dossier Body --}}
    <main class="max-w-7xl mx-auto px-3 sm:px-6 lg:px-8 py-5 sm:py-8 space-y-6 sm:space-y-8">

        {{-- 1. CULTURAL IDENTITY HEADER --}}
        <section class="rounded-2xl sm:rounded-3xl border border-stone-200/90 dark:border-stone-800 bg-white dark:bg-[#131218] shadow-sm overflow-hidden" aria-label="Cultural Record Identification">
            
            <div class="p-5 sm:p-8 lg:p-10 space-y-4 sm:space-y-5">
                
                {{-- Status & Classification Bar --}}
                <div class="flex flex-wrap items-center justify-between gap-2.5">
                    <div class="flex items-center gap-2 flex-wrap">
                        {{-- Category / Subtype --}}
                        <span class="px-3 py-1 rounded-md text-[11px] sm:text-xs font-bold uppercase tracking-wider bg-stone-100 dark:bg-stone-800 text-stone-800 dark:text-stone-200 border border-stone-200 dark:border-stone-700 inline-flex items-center gap-1.5">
                            <i data-lucide="{{ $culture->category_icon }}" class="w-3.5 h-3.5 text-amber-500"></i>
                            <span>{{ $culture->category }}</span>
                            @if($culture->subtype)
                                <span class="text-stone-400 dark:text-stone-500">/</span> {{ $culture->subtype }}
                            @endif
                        </span>

                        {{-- Review / Publication Status Badge --}}
                        @php
                            $badge = $culture->review_badge;
                        @endphp
                        <span class="px-3 py-1 rounded-md text-[11px] sm:text-xs font-bold tracking-wide border flex items-center gap-1.5 {{ $badge['class'] }}">
                            <span class="w-2 h-2 rounded-full bg-current"></span>
                            <span>{{ $badge['label'] }}</span>
                        </span>

                        {{-- AI Assistance Notice --}}
                        @if($culture->is_ai_assisted)
                            <span class="px-2.5 py-1 rounded-md text-[10px] sm:text-[11px] font-semibold bg-amber-500/10 text-amber-800 dark:text-amber-300 border border-amber-500/20 flex items-center gap-1.5"
                                  title="AI contributed to structuring or transcribing this record. It is subject to human community review.">
                                <i data-lucide="sparkles" class="w-3.5 h-3.5 text-amber-500"></i>
                                <span>AI-Assisted Processing</span>
                            </span>
                        @endif

                        {{-- Sensitivity / Access Restriction --}}
                        @if($culture->sensitivity_level && $culture->sensitivity_level !== 'public')
                            <span class="px-2.5 py-1 rounded-md text-[10px] sm:text-[11px] font-semibold bg-purple-500/10 text-purple-800 dark:text-purple-300 border border-purple-500/20 flex items-center gap-1">
                                <i data-lucide="shield-alert" class="w-3.5 h-3.5 text-purple-500"></i>
                                <span>{{ ucfirst(str_replace('_', ' ', $culture->sensitivity_level)) }} Knowledge</span>
                            </span>
                        @endif
                    </div>

                    {{-- Stable Record Identification --}}
                    <div class="text-[11px] text-stone-500 dark:text-stone-400 font-mono">
                        Archive ID: <strong>#CR-{{ str_pad($culture->id, 5, '0', STR_PAD_LEFT) }}</strong>
                    </div>
                </div>

                {{-- Record Title and Optional Translated Title --}}
                <div class="space-y-1">
                    <h1 class="page-title font-archive-title text-2xl sm:text-4xl lg:text-5xl font-extrabold text-amber-500 tracking-tight leading-tight">
                        {{ $culture->name }}
                    </h1>
                    @if($culture->translated_title && $culture->translated_title !== $culture->name)
                        <p class="text-sm sm:text-lg text-stone-500 dark:text-stone-400 font-archive-body italic">
                            Translated Title: "{{ $culture->translated_title }}"
                        </p>
                    @endif
                </div>

                {{-- Documented Geographic & Community Context --}}
                <div class="flex flex-wrap items-center gap-3 sm:gap-4 text-xs sm:text-sm text-stone-600 dark:text-stone-300 pt-1">
                    @if($culture->cultural_group)
                        <div class="flex items-center gap-1.5 font-semibold text-stone-900 dark:text-stone-100">
                            <i data-lucide="users" class="w-4 h-4 text-amber-600 dark:text-amber-400"></i>
                            <span>Community: {{ $culture->cultural_group }}</span>
                        </div>
                    @endif

                    @if($culture->language)
                        <div class="flex items-center gap-1.5 font-medium">
                            <i data-lucide="languages" class="w-4 h-4 text-amber-600 dark:text-amber-400"></i>
                            <span>Language: {{ $culture->language }}</span>
                        </div>
                    @endif

                    @if($culture->region || $culture->locality)
                        <div class="flex items-center gap-1.5 font-medium">
                            <i data-lucide="map-pin" class="w-4 h-4 text-amber-600 dark:text-amber-400"></i>
                            <span>Location: {{ $culture->locality ? $culture->locality . ', ' : '' }}{{ $culture->region }}</span>
                        </div>
                    @endif

                    @if($culture->historical_period)
                        <div class="flex items-center gap-1.5 font-medium">
                            <i data-lucide="history" class="w-4 h-4 text-amber-600 dark:text-amber-400"></i>
                            <span>Historical Era: {{ $culture->historical_period }}</span>
                        </div>
                    @endif
                </div>

                {{-- Concise Archival Introduction --}}
                @if($culture->significance)
                    <blockquote class="text-stone-700 dark:text-stone-300 text-sm sm:text-base leading-relaxed font-archive-body italic border-l-2 border-amber-500 pl-3 sm:pl-4 py-1">
                        "{{ $culture->significance }}"
                    </blockquote>
                @endif
            </div>

            {{-- Authorized Cover Artwork / Photographic Record --}}
            @if($culture->image)
                <div class="relative bg-stone-100 dark:bg-stone-950 border-t border-stone-200/80 dark:border-stone-800">
                    <div class="relative w-full min-h-[240px] max-h-[520px] flex items-center justify-center overflow-hidden group">
                        <div class="absolute inset-0 bg-cover bg-center filter blur-xl opacity-25 dark:opacity-20 scale-110 pointer-events-none"
                             style="background-image: url('{{ Storage::url($culture->image) }}');"></div>
                        <img src="{{ Storage::url($culture->image) }}" 
                             alt="{{ $culture->name }}"
                             class="relative z-10 w-auto h-auto max-w-full max-h-[520px] object-contain mx-auto">
                    </div>
                    <div class="px-4 py-2 bg-stone-50/90 dark:bg-stone-900/90 border-t border-stone-200/60 dark:border-stone-800 text-[11px] text-stone-600 dark:text-stone-400 flex flex-wrap items-center justify-between gap-2">
                        <span class="flex items-center gap-1.5 font-medium">
                            <i data-lucide="image" class="w-3.5 h-3.5 text-amber-500"></i>
                            <span>Archival Visual Record</span>
                        </span>
                        <span class="font-mono text-[10px]">
                            License: {{ $culture->license_type ?? 'Community Heritage Archive Commons' }}
                            @if($culture->license_credit)
                                • Attributed to: {{ $culture->license_credit }}
                            @endif
                        </span>
                    </div>
                </div>
            @endif

            {{-- Verified Archival Metadata Ribbon --}}
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-2 sm:gap-0 sm:divide-x divide-stone-200 dark:divide-stone-800 bg-stone-50/80 dark:bg-stone-900/80 p-3 sm:p-4 text-left border-t border-stone-200/80 dark:border-stone-800">
                <div class="p-2 sm:p-3">
                    <span class="text-[10px] font-bold text-stone-500 uppercase tracking-widest block">Geographic Region</span>
                    <span class="font-bold text-stone-900 dark:text-stone-100 text-xs sm:text-sm block mt-0.5">{{ $culture->region }}</span>
                </div>
                <div class="p-2 sm:p-3">
                    <span class="text-[10px] font-bold text-stone-500 uppercase tracking-widest block">Documented Language</span>
                    <span class="font-bold text-stone-900 dark:text-stone-100 text-xs sm:text-sm block mt-0.5">{{ $culture->language ?? 'Unspecified' }}</span>
                </div>
                <div class="p-2 sm:p-3">
                    <span class="text-[10px] font-bold text-stone-500 uppercase tracking-widest block">Verified Claims</span>
                    <span class="font-bold text-stone-900 dark:text-stone-100 text-xs sm:text-sm block mt-0.5">
                        {{ $culture->claims->where('review_status', 'source_verified')->count() }} of {{ $culture->claims->count() }} Claims Verified
                    </span>
                </div>
                <div class="p-2 sm:p-3">
                    <span class="text-[10px] font-bold text-stone-500 uppercase tracking-widest block">Primary Sources</span>
                    <span class="font-bold text-amber-700 dark:text-amber-400 text-xs sm:text-sm block mt-0.5">
                        {{ $culture->sources->count() > 0 ? $culture->sources->count() . ' Documented Sources' : 'Written Narrative' }}
                    </span>
                </div>
            </div>
        </section>

        {{-- 2. STRUCTURED ARCHIVE NAVIGATION TABS --}}
        <nav class="border-b border-stone-200 dark:border-stone-800 -mx-3 sm:mx-0 px-3 sm:px-0 overflow-x-auto no-scrollbar" aria-label="Archive Record Sections">
            <div class="flex items-center gap-1 sm:gap-2 min-w-max pb-1">
                
                {{-- 1. Overview --}}
                <button @click="activeSection = 'overview'"
                        class="px-3.5 py-2 rounded-t-lg text-xs font-bold transition flex items-center gap-1.5 border-b-2"
                        :class="activeSection === 'overview' 
                            ? 'border-amber-500 text-amber-600 dark:text-amber-400 bg-amber-500/5' 
                            : 'border-transparent text-stone-600 dark:text-stone-400 hover:text-stone-900 dark:hover:text-stone-200'">
                    <i data-lucide="book-open" class="w-3.5 h-3.5"></i>
                    <span>Overview</span>
                </button>

                {{-- 2. Original Account --}}
                <button @click="activeSection = 'original_account'"
                        class="px-3.5 py-2 rounded-t-lg text-xs font-bold transition flex items-center gap-1.5 border-b-2"
                        :class="activeSection === 'original_account' 
                            ? 'border-amber-500 text-amber-600 dark:text-amber-400 bg-amber-500/5' 
                            : 'border-transparent text-stone-600 dark:text-stone-400 hover:text-stone-900 dark:hover:text-stone-200'">
                    <i data-lucide="mic" class="w-3.5 h-3.5"></i>
                    <span>Original Account</span>
                    @if($culture->sources->count() > 0)
                        <span class="text-[10px] font-mono px-1 rounded bg-stone-200 dark:bg-stone-800">{{ $culture->sources->count() }}</span>
                    @endif
                </button>

                {{-- 3. History & Context --}}
                @if($culture->historical_period || $culture->significance || $culture->description)
                    <button @click="activeSection = 'history'"
                            class="px-3.5 py-2 rounded-t-lg text-xs font-bold transition flex items-center gap-1.5 border-b-2"
                            :class="activeSection === 'history' 
                                ? 'border-amber-500 text-amber-600 dark:text-amber-400 bg-amber-500/5' 
                                : 'border-transparent text-stone-600 dark:text-stone-400 hover:text-stone-900 dark:hover:text-stone-200'">
                        <i data-lucide="history" class="w-3.5 h-3.5"></i>
                        <span>History & Context</span>
                    </button>
                @endif

                {{-- 4. Practices & Transmission --}}
                @if($culture->rituals || $culture->transmission_methods || $culture->community_role || $culture->current_practitioners)
                    <button @click="activeSection = 'practices'"
                            class="px-3.5 py-2 rounded-t-lg text-xs font-bold transition flex items-center gap-1.5 border-b-2"
                            :class="activeSection === 'practices' 
                                ? 'border-amber-500 text-amber-600 dark:text-amber-400 bg-amber-500/5' 
                                : 'border-transparent text-stone-600 dark:text-stone-400 hover:text-stone-900 dark:hover:text-stone-200'">
                        <i data-lucide="compass" class="w-3.5 h-3.5"></i>
                        <span>Practices & Transmission</span>
                    </button>
                @endif

                {{-- 5. People & Custodians --}}
                <button @click="activeSection = 'people'"
                        class="px-3.5 py-2 rounded-t-lg text-xs font-bold transition flex items-center gap-1.5 border-b-2"
                        :class="activeSection === 'people' 
                            ? 'border-amber-500 text-amber-600 dark:text-amber-400 bg-amber-500/5' 
                            : 'border-transparent text-stone-600 dark:text-stone-400 hover:text-stone-900 dark:hover:text-stone-200'">
                    <i data-lucide="users" class="w-3.5 h-3.5"></i>
                    <span>People & Custodians</span>
                </button>

                {{-- 6. Media & Artefacts (Wikipedia Style) --}}
                <button @click="activeSection = 'media'"
                        class="px-3.5 py-2 rounded-t-lg text-xs font-bold transition flex items-center gap-1.5 border-b-2"
                        :class="activeSection === 'media' 
                            ? 'border-amber-500 text-amber-600 dark:text-amber-400 bg-amber-500/5' 
                            : 'border-transparent text-stone-600 dark:text-stone-400 hover:text-stone-900 dark:hover:text-stone-200'">
                    <i data-lucide="film" class="w-3.5 h-3.5"></i>
                    <span>Media, Videos & Books</span>
                    @php
                        $mediaBadgeCount = ($culture->image ? 1 : 0) 
                            + (is_array($culture->media_files) ? count($culture->media_files) : 0)
                            + $culture->mediaAssets->count()
                            + ($culture->video_url || $culture->video_path ? 1 : 0);
                    @endphp
                    @if($mediaBadgeCount > 0)
                        <span class="text-[10px] font-mono px-1.5 py-0.2 rounded-full bg-amber-500/20 text-amber-700 dark:text-amber-300 font-bold">{{ $mediaBadgeCount }}</span>
                    @endif
                </button>

                {{-- 7. Related Knowledge --}}
                @if(!empty($relatedCultures) && count($relatedCultures) > 0)
                    <button @click="activeSection = 'related'"
                            class="px-3.5 py-2 rounded-t-lg text-xs font-bold transition flex items-center gap-1.5 border-b-2"
                            :class="activeSection === 'related' 
                                ? 'border-amber-500 text-amber-600 dark:text-amber-400 bg-amber-500/5' 
                                : 'border-transparent text-stone-600 dark:text-stone-400 hover:text-stone-900 dark:hover:text-stone-200'">
                        <i data-lucide="network" class="w-3.5 h-3.5"></i>
                        <span>Related Knowledge</span>
                    </button>
                @endif

                {{-- 8. Sources & Verification --}}
                <button @click="activeSection = 'sources'"
                        class="px-3.5 py-2 rounded-t-lg text-xs font-bold transition flex items-center gap-1.5 border-b-2"
                        :class="activeSection === 'sources' 
                            ? 'border-amber-500 text-amber-600 dark:text-amber-400 bg-amber-500/5' 
                            : 'border-transparent text-stone-600 dark:text-stone-400 hover:text-stone-900 dark:hover:text-stone-200'">
                    <i data-lucide="check-circle" class="w-3.5 h-3.5"></i>
                    <span>Sources & Verification</span>
                    @if($culture->claims->count() > 0)
                        <span class="text-[10px] font-mono px-1 rounded bg-stone-200 dark:bg-stone-800">{{ $culture->claims->count() }}</span>
                    @endif
                </button>

                {{-- 9. Contribute --}}
                <button @click="activeSection = 'contribute'"
                        class="px-3.5 py-2 rounded-t-lg text-xs font-bold transition flex items-center gap-1.5 border-b-2"
                        :class="activeSection === 'contribute' 
                            ? 'border-amber-500 text-amber-600 dark:text-amber-400 bg-amber-500/5' 
                            : 'border-transparent text-amber-700 dark:text-amber-400 hover:underline'">
                    <i data-lucide="plus-circle" class="w-3.5 h-3.5"></i>
                    <span>Contribute</span>
                </button>
            </div>
        </nav>

        {{-- SECTION 1: OVERVIEW --}}
        <div x-show="activeSection === 'overview'" class="space-y-6">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                
                {{-- Main Narrative Overview --}}
                <div class="lg:col-span-2 rounded-2xl border border-stone-200/90 dark:border-stone-800 bg-white dark:bg-[#131218] p-5 sm:p-8 space-y-4">
                    <h2 class="text-sm font-bold uppercase tracking-wider text-amber-600 dark:text-amber-400 flex items-center gap-2">
                        <i data-lucide="book-open" class="w-4 h-4"></i>
                        <span>Archival Record Overview</span>
                    </h2>

                    <div class="font-archive-body leading-relaxed text-stone-800 dark:text-stone-200 archive-drop-cap space-y-4"
                         :style="'font-size: ' + fontSize + 'px'">
                        {!! nl2br(e($culture->description)) !!}
                    </div>

                    @if($culture->is_ai_assisted)
                        <div class="p-3 bg-amber-500/5 border border-amber-500/20 rounded-xl text-xs text-amber-900 dark:text-amber-300 space-y-1">
                            <span class="font-bold flex items-center gap-1.5">
                                <i data-lucide="info" class="w-4 h-4 text-amber-600"></i>
                                <span>AI Processing Disclosure</span>
                            </span>
                            <p class="leading-relaxed">
                                AI assistance was employed to organize this account from submitted community sources. 
                                Claims and specific details should be reviewed by elders and custodians.
                            </p>
                        </div>
                    @endif
                </div>

                {{-- Sidebar: Essential Metadata & Knowledge Companion --}}
                <div class="space-y-6">
                    
                    {{-- Key Archival Attributes Card --}}
                    <div class="rounded-2xl border border-stone-200/90 dark:border-stone-800 bg-white dark:bg-[#131218] p-5 space-y-3">
                        <h3 class="text-xs font-bold uppercase tracking-wider text-stone-500 dark:text-stone-400">
                            Essential Record Data
                        </h3>

                        <dl class="text-xs space-y-2.5">
                            <div class="flex justify-between py-1 border-b border-stone-100 dark:border-stone-800/60">
                                <dt class="text-stone-500">Record ID</dt>
                                <dd class="font-mono font-bold text-stone-900 dark:text-stone-100">#CR-{{ str_pad($culture->id, 5, '0', STR_PAD_LEFT) }}</dd>
                            </div>
                            <div class="flex justify-between py-1 border-b border-stone-100 dark:border-stone-800/60">
                                <dt class="text-stone-500">Record Type</dt>
                                <dd class="font-semibold text-stone-900 dark:text-stone-100">{{ $culture->category }}</dd>
                            </div>
                            @if($culture->cultural_group)
                                <div class="flex justify-between py-1 border-b border-stone-100 dark:border-stone-800/60">
                                    <dt class="text-stone-500">Community / Group</dt>
                                    <dd class="font-semibold text-stone-900 dark:text-stone-100">{{ $culture->cultural_group }}</dd>
                                </div>
                            @endif
                            @if($culture->language)
                                <div class="flex justify-between py-1 border-b border-stone-100 dark:border-stone-800/60">
                                    <dt class="text-stone-500">Language</dt>
                                    <dd class="font-semibold text-stone-900 dark:text-stone-100">{{ $culture->language }}</dd>
                                </div>
                            @endif
                            <div class="flex justify-between py-1 border-b border-stone-100 dark:border-stone-800/60">
                                <dt class="text-stone-500">Region / Origin</dt>
                                <dd class="font-semibold text-stone-900 dark:text-stone-100">{{ $culture->region }}</dd>
                            </div>
                            <div class="flex justify-between py-1 border-b border-stone-100 dark:border-stone-800/60">
                                <dt class="text-stone-500">Review Status</dt>
                                <dd class="font-bold text-amber-600 dark:text-amber-400">{{ $culture->review_badge['label'] }}</dd>
                            </div>
                            <div class="flex justify-between py-1">
                                <dt class="text-stone-500">Submitted On</dt>
                                <dd class="font-mono text-stone-700 dark:text-stone-300">{{ $culture->created_at->format('M d, Y') }}</dd>
                            </div>
                        </dl>
                    </div>

                    {{-- CULTURAL KNOWLEDGE COMPANION INTERACTIVE CARD --}}
                    <div class="rounded-2xl border border-amber-200 dark:border-amber-900/60 bg-gradient-to-br from-amber-500/5 to-transparent p-5 space-y-3"
                         x-data="{
                            question: '',
                            loading: false,
                            response: null,
                            async ask() {
                                if (!this.question.trim() || this.loading) return;
                                this.loading = true;
                                try {
                                    const res = await fetch('/cultural-hub/{{ $culture->id }}/companion', {
                                        method: 'POST',
                                        headers: {
                                            'Content-Type': 'application/json',
                                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                        },
                                        body: JSON.stringify({ question: this.question })
                                    });
                                    this.response = await res.json();
                                } catch(e) {
                                    this.response = { answer: 'Error consulting cultural companion. Please try again.', grounded: false };
                                } finally {
                                    this.loading = false;
                                }
                            }
                         }">
                        <div class="flex items-center gap-2">
                            <div class="w-7 h-7 rounded-lg bg-amber-500 text-white flex items-center justify-center">
                                <i data-lucide="help-circle" class="w-4 h-4"></i>
                            </div>
                            <div>
                                <h3 class="text-xs font-bold uppercase tracking-wider text-stone-900 dark:text-stone-100">Knowledge Companion</h3>
                                <p class="text-[11px] text-stone-500">Ask questions grounded strictly in this archive record</p>
                            </div>
                        </div>

                        <div class="space-y-2">
                            <input type="text"
                                   x-model="question"
                                   @keydown.enter.prevent="ask()"
                                   placeholder="e.g., What are the customary steps in this tradition?"
                                   class="w-full text-xs rounded-lg border border-stone-200 dark:border-stone-800 bg-white dark:bg-stone-900 px-3 py-2 text-stone-800 dark:text-stone-200 focus:outline-none focus:border-amber-500">
                            <button @click="ask()"
                                    :disabled="loading || !question.trim()"
                                    class="w-full py-2 rounded-lg bg-amber-500 hover:bg-amber-600 disabled:opacity-50 text-white text-xs font-bold transition flex items-center justify-center gap-1.5">
                                <span x-show="!loading">Ask Grounded Question</span>
                                <span x-show="loading" class="animate-pulse">Consulting Archive Record...</span>
                            </button>
                        </div>

                        {{-- Companion Answer Display --}}
                        <template x-if="response">
                            <div class="p-3 rounded-xl bg-white dark:bg-stone-900 border border-stone-200 dark:border-stone-800 text-xs space-y-2">
                                <div class="flex items-center justify-between text-[10px] text-stone-500 border-b pb-1">
                                    <span class="font-bold text-amber-700 dark:text-amber-400" x-text="response.distinction || 'Archive Record'"></span>
                                    <span x-text="response.grounded ? 'Grounded in Source' : 'General Inquiry'"></span>
                                </div>
                                <p class="text-stone-800 dark:text-stone-200 leading-relaxed font-archive-body" x-text="response.answer"></p>
                                <template x-if="response.missing_context">
                                    <p class="text-[11px] text-stone-500 italic border-t pt-1" x-text="'Missing archive context: ' + response.missing_context"></p>
                                </template>
                            </div>
                        </template>
                    </div>

                </div>
            </div>
        </div>

        {{-- SECTION 2: ORIGINAL ACCOUNT & PRIMARY MEDIA --}}
        <div x-show="activeSection === 'original_account'" class="space-y-6">
            
            {{-- Primary Audio/Video Narration Player --}}
            @if($culture->audio_path || $culture->audio_url || $culture->video_path || $culture->video_url)
                <div class="rounded-2xl border border-amber-200 dark:border-amber-900/60 bg-white dark:bg-[#131218] p-5 sm:p-7 space-y-4">
                    <div class="flex items-center justify-between flex-wrap gap-2">
                        <div class="flex items-center gap-2">
                            <div class="w-10 h-10 rounded-xl bg-amber-500 text-white flex items-center justify-center">
                                <i data-lucide="{{ $culture->video_path || $culture->video_url ? 'video' : 'headphones' }}" class="w-5 h-5"></i>
                            </div>
                            <div>
                                <h3 class="text-sm font-bold text-stone-900 dark:text-stone-100">
                                    {{ $culture->video_path || $culture->video_url ? 'Primary Video Recording' : 'Original Oral Recording' }}
                                </h3>
                                <p class="text-xs text-stone-500">Preserved community audio/video testimony</p>
                            </div>
                        </div>

                        <span class="px-2.5 py-1 rounded text-[11px] font-mono font-bold bg-amber-500/10 text-amber-800 dark:text-amber-300">
                            Language: {{ $culture->language ?? 'Indigenous Speech' }}
                        </span>
                    </div>

                    {{-- Audio Player --}}
                    @if($culture->audio_path || $culture->audio_url)
                        <div class="p-3 bg-stone-50 dark:bg-stone-900 rounded-xl border border-stone-200 dark:border-stone-800">
                            <audio controls class="w-full">
                                <source src="{{ $culture->audio_path ? Storage::url($culture->audio_path) : $culture->audio_url }}" type="audio/mpeg">
                                Your browser does not support audio playback.
                            </audio>
                        </div>
                    @endif

                    {{-- Video Player --}}
                    @if($culture->video_path)
                        <div class="rounded-xl overflow-hidden bg-black max-h-[420px] flex items-center justify-center">
                            <video controls class="w-full max-h-[420px]">
                                <source src="{{ Storage::url($culture->video_path) }}">
                            </video>
                        </div>
                    @elseif($culture->video_url)
                        <div class="aspect-video rounded-xl overflow-hidden bg-black">
                            <iframe src="{{ $culture->video_url }}" class="w-full h-full" frameborder="0" allowfullscreen></iframe>
                        </div>
                    @endif
                </div>
            @endif

            {{-- Verbatim Transcript & Source Material --}}
            <div class="rounded-2xl border border-stone-200/90 dark:border-stone-800 bg-white dark:bg-[#131218] p-5 sm:p-8 space-y-4">
                <div class="flex items-center justify-between flex-wrap gap-2 border-b border-stone-100 dark:border-stone-800 pb-3">
                    <div>
                        <h2 class="text-sm font-bold uppercase tracking-wider text-amber-600 dark:text-amber-400">
                            Original Community Testimony & Verbatim Transcript
                        </h2>
                        <p class="text-xs text-stone-500">Unfiltered oral history as recited by knowledge keepers</p>
                    </div>

                    <div class="flex items-center gap-2">
                        <span class="text-xs px-2.5 py-1 rounded-md font-semibold bg-stone-100 dark:bg-stone-800 text-stone-700 dark:text-stone-300">
                            {{ $culture->audio_transcript ? 'Transcript Origin: Recorded Testimony' : 'Written Testimony' }}
                        </span>
                    </div>
                </div>

                {{-- Verbatim Text Content --}}
                <div class="font-archive-body leading-relaxed text-stone-800 dark:text-stone-200 space-y-4 text-base sm:text-lg">
                    @if($culture->raw_story)
                        <div class="p-4 rounded-xl bg-stone-50/70 dark:bg-stone-900/60 border border-stone-200/60 dark:border-stone-800/60">
                            <span class="text-[10px] font-bold uppercase tracking-widest text-amber-700 dark:text-amber-400 block mb-2">Original Spoken Narrative</span>
                            {!! nl2br(e($culture->raw_story)) !!}
                        </div>
                    @elseif($culture->audio_transcript)
                        <div class="p-4 rounded-xl bg-stone-50/70 dark:bg-stone-900/60 border border-stone-200/60 dark:border-stone-800/60">
                            <span class="text-[10px] font-bold uppercase tracking-widest text-amber-700 dark:text-amber-400 block mb-2">Preserved Audio Transcript</span>
                            {!! nl2br(e($culture->audio_transcript)) !!}
                        </div>
                    @else
                        <div class="p-4 rounded-xl bg-stone-50/70 dark:bg-stone-900/60 border border-stone-200/60 dark:border-stone-800/60">
                            <span class="text-[10px] font-bold uppercase tracking-widest text-stone-500 block mb-2">Documented Cultural Narrative</span>
                            {!! nl2br(e($culture->description)) !!}
                        </div>
                    @endif
                </div>

                {{-- Documented Sources List --}}
                @if($culture->sources->count() > 0)
                    <div class="pt-4 border-t border-stone-100 dark:border-stone-800 space-y-3">
                        <h3 class="text-xs font-bold uppercase tracking-wider text-stone-500">Documented Source Items</h3>
                        <div class="space-y-2">
                            @foreach($culture->sources as $source)
                                <div class="p-3 rounded-xl border border-stone-200 dark:border-stone-800 bg-stone-50 dark:bg-stone-900/40 text-xs flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                                    <div>
                                        <span class="font-bold text-stone-900 dark:text-stone-100 capitalize">{{ str_replace('_', ' ', $source->source_type) }}</span>
                                        <span class="text-stone-400">•</span>
                                        <span class="text-stone-600 dark:text-stone-400">Speaker: {{ $source->display_speaker }}</span>
                                        @if($source->recording_location)
                                            <span class="text-stone-400">•</span>
                                            <span class="text-stone-500">Location: {{ $source->recording_location }}</span>
                                        @endif
                                    </div>
                                    <span class="text-[10px] font-mono px-2 py-0.5 rounded bg-stone-200 dark:bg-stone-800 text-stone-700 dark:text-stone-300">
                                        {{ ucfirst($source->transcript_origin) }} ({{ $source->transcript_review_status }})
                                    </span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        </div>

        {{-- SECTION 3: HISTORY & CONTEXT --}}
        @if($culture->historical_period || $culture->significance || $culture->description)
            <div x-show="activeSection === 'history'" class="space-y-6">
                <div class="rounded-2xl border border-stone-200/90 dark:border-stone-800 bg-white dark:bg-[#131218] p-5 sm:p-8 space-y-6">
                    <h2 class="text-sm font-bold uppercase tracking-wider text-amber-600 dark:text-amber-400 flex items-center gap-2">
                        <i data-lucide="history" class="w-4 h-4"></i>
                        <span>Historical Origin, Meaning & Context</span>
                    </h2>

                    @php
                        $histMedia = $culture->mediaAssets->where('section', 'history');
                    @endphp
                    @if($histMedia->count() > 0)
                        <div class="p-3.5 rounded-xl bg-amber-500/5 dark:bg-amber-950/20 border border-amber-500/20 flex flex-wrap items-center justify-between gap-2 text-xs">
                            <div class="flex items-center gap-2">
                                <span class="px-2 py-0.5 rounded-md bg-amber-500/15 text-amber-700 dark:text-amber-300 font-bold text-[10px] uppercase">
                                    Section Media &amp; Books
                                </span>
                                <span class="text-stone-700 dark:text-stone-300 font-medium">
                                    {{ $histMedia->count() }} media item{{ $histMedia->count() === 1 ? '' : 's' }} (photos, videos &amp; books) documented for this history
                                </span>
                            </div>
                            <button type="button" @click="activeSection = 'media'; mediaSectionFilter = 'history'" class="font-bold text-amber-600 dark:text-amber-400 hover:underline flex items-center gap-1">
                                <span>Browse in Media Gallery</span>
                                <i data-lucide="arrow-right" class="w-3 h-3"></i>
                            </button>
                        </div>
                    @endif

                    @if($culture->historical_period)
                        <div class="space-y-1">
                            <h3 class="text-xs font-bold uppercase tracking-wider text-stone-500">Historical Era</h3>
                            <p class="text-stone-900 dark:text-stone-100 font-semibold">{{ $culture->historical_period }}</p>
                        </div>
                    @endif

                    @if($culture->significance)
                        <div class="space-y-2">
                            <h3 class="text-xs font-bold uppercase tracking-wider text-stone-500">Cultural & Spiritual Significance</h3>
                            <p class="font-archive-body text-base sm:text-lg leading-relaxed text-stone-800 dark:text-stone-200">
                                {{ $culture->significance }}
                            </p>
                        </div>
                    @endif

                    @if($culture->challenges || $culture->preservation_efforts)
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 pt-4 border-t border-stone-100 dark:border-stone-800">
                            @if($culture->preservation_efforts)
                                <div class="p-4 rounded-xl bg-emerald-500/5 border border-emerald-500/20 space-y-1">
                                    <h4 class="text-xs font-bold text-emerald-800 dark:text-emerald-300 uppercase tracking-wider">Preservation Efforts</h4>
                                    <p class="text-xs text-stone-700 dark:text-stone-300 leading-relaxed">{{ $culture->preservation_efforts }}</p>
                                </div>
                            @endif

                            @if($culture->challenges)
                                <div class="p-4 rounded-xl bg-amber-500/5 border border-amber-500/20 space-y-1">
                                    <h4 class="text-xs font-bold text-amber-800 dark:text-amber-300 uppercase tracking-wider">Documented Threats & Challenges</h4>
                                    <p class="text-xs text-stone-700 dark:text-stone-300 leading-relaxed">{{ $culture->challenges }}</p>
                                </div>
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        @endif

        {{-- SECTION 4: PRACTICES & TRANSMISSION --}}
        @if($culture->rituals || $culture->transmission_methods || $culture->community_role || $culture->current_practitioners)
            <div x-show="activeSection === 'practices'" class="space-y-6">
                <div class="rounded-2xl border border-stone-200/90 dark:border-stone-800 bg-white dark:bg-[#131218] p-5 sm:p-8 space-y-6">
                    <h2 class="text-sm font-bold uppercase tracking-wider text-amber-600 dark:text-amber-400 flex items-center gap-2">
                        <i data-lucide="compass" class="w-4 h-4"></i>
                        <span>Living Practices & Intergenerational Transmission</span>
                    </h2>

                    @php
                        $practiceMedia = $culture->mediaAssets->where('section', 'practices');
                    @endphp
                    @if($practiceMedia->count() > 0)
                        <div class="p-3.5 rounded-xl bg-amber-500/5 dark:bg-amber-950/20 border border-amber-500/20 flex flex-wrap items-center justify-between gap-2 text-xs">
                            <div class="flex items-center gap-2">
                                <span class="px-2 py-0.5 rounded-md bg-amber-500/15 text-amber-700 dark:text-amber-300 font-bold text-[10px] uppercase">
                                    Section Media &amp; Books
                                </span>
                                <span class="text-stone-700 dark:text-stone-300 font-medium">
                                    {{ $practiceMedia->count() }} media item{{ $practiceMedia->count() === 1 ? '' : 's' }} documented for these practices
                                </span>
                            </div>
                            <button type="button" @click="activeSection = 'media'; mediaSectionFilter = 'practices'" class="font-bold text-amber-600 dark:text-amber-400 hover:underline flex items-center gap-1">
                                <span>Browse in Media Gallery</span>
                                <i data-lucide="arrow-right" class="w-3 h-3"></i>
                            </button>
                        </div>
                    @endif

                    @if($culture->rituals)
                        <div class="space-y-2">
                            <h3 class="text-xs font-bold uppercase tracking-wider text-stone-500">Customary Rites & Ceremonial Steps</h3>
                            <p class="font-archive-body text-base sm:text-lg leading-relaxed text-stone-800 dark:text-stone-200">
                                {{ $culture->rituals }}
                            </p>
                        </div>
                    @endif

                    @if($culture->community_role)
                        <div class="space-y-2">
                            <h3 class="text-xs font-bold uppercase tracking-wider text-stone-500">Role in Community Life</h3>
                            <p class="font-archive-body text-base leading-relaxed text-stone-800 dark:text-stone-200">
                                {{ $culture->community_role }}
                            </p>
                        </div>
                    @endif

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 pt-4 border-t border-stone-100 dark:border-stone-800">
                        @if($culture->transmission_methods)
                            <div class="p-4 rounded-xl bg-stone-50 dark:bg-stone-900 border border-stone-200 dark:border-stone-800 space-y-1">
                                <h4 class="text-xs font-bold uppercase tracking-wider text-stone-500">Transmission Mode</h4>
                                <p class="text-xs font-medium text-stone-800 dark:text-stone-200">{{ $culture->transmission_methods }}</p>
                            </div>
                        @endif

                        @if($culture->current_practitioners)
                            <div class="p-4 rounded-xl bg-stone-50 dark:bg-stone-900 border border-stone-200 dark:border-stone-800 space-y-1">
                                <h4 class="text-xs font-bold uppercase tracking-wider text-stone-500">Documented Practitioners</h4>
                                <p class="text-xs font-medium text-stone-800 dark:text-stone-200">{{ $culture->current_practitioners }}</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        @endif

        {{-- SECTION 5: PEOPLE & CUSTODIANS --}}
        <div x-show="activeSection === 'people'" class="space-y-6">
            <div class="rounded-2xl border border-stone-200/90 dark:border-stone-800 bg-white dark:bg-[#131218] p-5 sm:p-8 space-y-6">
                <div>
                    <h2 class="text-sm font-bold uppercase tracking-wider text-amber-600 dark:text-amber-400 flex items-center gap-2">
                        <i data-lucide="users" class="w-4 h-4"></i>
                        <span>Contributors, Speakers & Reviewers</span>
                    </h2>
                    <p class="text-xs text-stone-500 mt-1">
                        Cultural knowledge is preserved through custodians. Individual contributors share testimony but do not solely represent entire communities.
                    </p>
                </div>

                @php
                    $peopleMedia = $culture->mediaAssets->where('section', 'people');
                @endphp
                @if($peopleMedia->count() > 0)
                    <div class="p-3.5 rounded-xl bg-amber-500/5 dark:bg-amber-950/20 border border-amber-500/20 flex flex-wrap items-center justify-between gap-2 text-xs">
                        <div class="flex items-center gap-2">
                            <span class="px-2 py-0.5 rounded-md bg-amber-500/15 text-amber-700 dark:text-amber-300 font-bold text-[10px] uppercase">
                                Section Media &amp; Books
                            </span>
                            <span class="text-stone-700 dark:text-stone-300 font-medium">
                                {{ $peopleMedia->count() }} media item{{ $peopleMedia->count() === 1 ? '' : 's' }} documented for community lineage &amp; custodians
                            </span>
                        </div>
                        <button type="button" @click="activeSection = 'media'; mediaSectionFilter = 'people'" class="font-bold text-amber-600 dark:text-amber-400 hover:underline flex items-center gap-1">
                            <span>Browse in Media Gallery</span>
                            <i data-lucide="arrow-right" class="w-3 h-3"></i>
                        </button>
                    </div>
                @endif

                {{-- Record Submitter Attribution --}}
                <div class="p-4 rounded-xl border border-stone-200 dark:border-stone-800 bg-stone-50/70 dark:bg-stone-900/60 space-y-2">
                    <span class="text-[10px] font-bold uppercase tracking-widest text-stone-500">Submitting Guardian</span>
                    <div class="flex items-center justify-between flex-wrap gap-2">
                        <div class="flex items-center gap-2.5">
                            <div class="w-9 h-9 rounded-full bg-amber-500/20 text-amber-700 dark:text-amber-300 font-bold flex items-center justify-center text-sm">
                                {{ substr($culture->submitter->name ?? 'G', 0, 1) }}
                            </div>
                            <div>
                                <h3 class="text-xs font-bold text-stone-900 dark:text-stone-100">{{ $culture->submitter->name ?? 'Community Guardian' }}</h3>
                                <p class="text-[11px] text-stone-500">Submitted record on {{ $culture->created_at->format('F j, Y') }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Specific Documented Contributors --}}
                @if($culture->culturalContributors->count() > 0)
                    <div class="space-y-3">
                        <h3 class="text-xs font-bold uppercase tracking-wider text-stone-500">Documented Knowledge Contributors</h3>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            @foreach($culture->culturalContributors as $contributor)
                                <div class="p-3.5 rounded-xl border border-stone-200 dark:border-stone-800 bg-white dark:bg-stone-900 space-y-1.5 text-xs">
                                    <div class="flex items-center justify-between">
                                        <span class="font-bold text-stone-900 dark:text-stone-100">{{ $contributor->display_name }}</span>
                                        <span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-stone-100 dark:bg-stone-800 capitalize">{{ $contributor->role }}</span>
                                    </div>
                                    @if($contributor->relationship_to_material)
                                        <p class="text-stone-600 dark:text-stone-400 text-[11px]">
                                            Relationship: {{ $contributor->relationship_to_material }}
                                        </p>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        </div>

        {{-- SECTION 6: MEDIA, VIDEOS & BOOKS (Wikipedia Archive Style) --}}
        @php
            $allMediaAssets = $culture->mediaAssets ?? collect();
            $photoAssets = $allMediaAssets->filter(fn($m) => in_array($m->media_type, ['photograph', 'artefact_image', 'scanned_document']) || (!$m->media_type && $m->file_path && $m->file_path !== 'reference' && $m->file_path !== 'external'));
            $videoAssets = $allMediaAssets->where('media_type', 'video');
            $bookAssets = $allMediaAssets->where('media_type', 'book');
        @endphp

        <div x-show="activeSection === 'media'" class="space-y-6">
            <div class="rounded-2xl border border-stone-200/90 dark:border-stone-800 bg-white dark:bg-[#131218] p-5 sm:p-8 space-y-6">
                
                {{-- Header with Wikipedia-style Counters & Contribution Prompt --}}
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-stone-100 dark:border-stone-800 pb-4">
                    <div>
                        <h2 class="text-sm sm:text-base font-bold uppercase tracking-wider text-amber-600 dark:text-amber-400 flex items-center gap-2">
                            <i data-lucide="film" class="w-4 h-4"></i>
                            <span>Photographs, Field Videos & Books (Wikipedia Media)</span>
                        </h2>
                        <p class="text-xs text-stone-500 mt-1">
                            Media assets, corresponding recordings, and scholarly books organized by topical section.
                        </p>
                    </div>
                    <button type="button" @click="activeSection = 'contribute'" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-gradient-to-r from-amber-500 to-orange-500 hover:from-amber-600 hover:to-orange-600 text-white font-bold text-xs shadow-sm transition active:scale-95 shrink-0">
                        <i data-lucide="plus" class="w-3.5 h-3.5"></i>
                        <span>Contribute Media / Book</span>
                    </button>
                </div>

                {{-- Interactive Media Type & Section Filter Bar --}}
                <div class="space-y-2.5">
                    <div class="flex flex-wrap items-center gap-1.5 text-xs">
                        <span class="text-stone-400 font-semibold text-[11px] mr-1">Type:</span>
                        <button type="button" @click="mediaTypeFilter = 'all'"
                                class="px-3 py-1 rounded-full font-bold transition"
                                :class="mediaTypeFilter === 'all' ? 'bg-stone-900 text-white dark:bg-stone-100 dark:text-black shadow-xs' : 'bg-stone-100 dark:bg-stone-800 text-stone-600 dark:text-stone-400 hover:bg-stone-200 dark:hover:bg-stone-700'">
                            All Media
                        </button>
                        <button type="button" @click="mediaTypeFilter = 'photos'"
                                class="px-3 py-1 rounded-full font-bold transition flex items-center gap-1"
                                :class="mediaTypeFilter === 'photos' ? 'bg-amber-500 text-white shadow-xs' : 'bg-stone-100 dark:bg-stone-800 text-stone-600 dark:text-stone-400 hover:bg-stone-200 dark:hover:bg-stone-700'">
                            <i data-lucide="image" class="w-3 h-3"></i>
                            <span>Photos & Artefacts</span>
                        </button>
                        <button type="button" @click="mediaTypeFilter = 'videos'"
                                class="px-3 py-1 rounded-full font-bold transition flex items-center gap-1"
                                :class="mediaTypeFilter === 'videos' ? 'bg-blue-600 text-white shadow-xs' : 'bg-stone-100 dark:bg-stone-800 text-stone-600 dark:text-stone-400 hover:bg-stone-200 dark:hover:bg-stone-700'">
                            <i data-lucide="video" class="w-3 h-3"></i>
                            <span>Videos & Recordings</span>
                        </button>
                        <button type="button" @click="mediaTypeFilter = 'books'"
                                class="px-3 py-1 rounded-full font-bold transition flex items-center gap-1"
                                :class="mediaTypeFilter === 'books' ? 'bg-emerald-600 text-white shadow-xs' : 'bg-stone-100 dark:bg-stone-800 text-stone-600 dark:text-stone-400 hover:bg-stone-200 dark:hover:bg-stone-700'">
                            <i data-lucide="book" class="w-3 h-3"></i>
                            <span>Books & Bibliography ({{ $bookAssets->count() }})</span>
                        </button>
                    </div>

                    <div class="flex flex-wrap items-center gap-1.5 text-xs pt-1">
                        <span class="text-stone-400 font-semibold text-[11px] mr-1">Topic Section:</span>
                        @foreach(['all' => 'All Sections', 'overview' => 'Overview', 'history' => 'History & Context', 'practices' => 'Practices & Rituals', 'people' => 'People & Custodians', 'sources' => 'Sources & Books'] as $secKey => $secLabel)
                            <button type="button" @click="mediaSectionFilter = '{{ $secKey }}'"
                                    class="px-2.5 py-0.5 rounded-lg text-[11px] font-semibold border transition"
                                    :class="mediaSectionFilter === '{{ $secKey }}' ? 'border-amber-500 bg-amber-500/10 text-amber-600 dark:text-amber-400 font-bold' : 'border-stone-200 dark:border-stone-800 text-stone-500 hover:border-stone-400'">
                                {{ $secLabel }}
                            </button>
                        @endforeach
                    </div>
                </div>

                {{-- 1. PHOTOGRAPHS & ARTEFACTS GALLERY --}}
                <div x-show="mediaTypeFilter === 'all' || mediaTypeFilter === 'photos'" class="space-y-4">
                    <h3 class="text-xs font-bold uppercase tracking-wider text-stone-700 dark:text-stone-300 flex items-center gap-1.5">
                        <i data-lucide="image" class="w-3.5 h-3.5 text-amber-500"></i>
                        <span>Photographic & Artefact Archive</span>
                    </h3>

                    {{-- Primary Cover Image --}}
                    @if($culture->image)
                        <div x-show="mediaSectionFilter === 'all' || mediaSectionFilter === 'overview'" class="rounded-2xl overflow-hidden border border-stone-200 dark:border-stone-800 bg-stone-50 dark:bg-stone-900">
                            <div class="relative overflow-hidden bg-stone-900/5 dark:bg-black/40 flex items-center justify-center min-h-[220px] max-h-[520px] group">
                                <div class="absolute inset-0 bg-cover bg-center filter blur-xl opacity-25 dark:opacity-20 scale-110 pointer-events-none"
                                     style="background-image: url('{{ Storage::url($culture->image) }}');"></div>
                                <img src="{{ Storage::url($culture->image) }}" alt="{{ $culture->name }}" class="relative z-10 w-auto h-auto max-w-full max-h-[500px] object-contain mx-auto">
                            </div>
                            <div class="p-3.5 text-xs flex flex-wrap justify-between items-center gap-2 text-stone-600 dark:text-stone-400 bg-stone-50/80 dark:bg-stone-900/80 border-t border-stone-200 dark:border-stone-800">
                                <div class="flex items-center gap-2">
                                    <span class="px-2 py-0.5 rounded-full bg-amber-500/15 text-amber-700 dark:text-amber-300 font-bold text-[10px]">Overview</span>
                                    <span class="font-bold text-stone-900 dark:text-stone-100">Primary Cultural Photo</span>
                                </div>
                                <span class="font-mono text-[10px] text-stone-500">{{ $culture->license_type ?? 'Cultural Archive Commons' }}</span>
                            </div>
                        </div>
                    @endif

                    {{-- Supporting Gallery Files --}}
                    @if(!empty($culture->media_files) && is_array($culture->media_files))
                        <div x-show="mediaSectionFilter === 'all' || mediaSectionFilter === 'overview'" class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                            @foreach($culture->media_files as $file)
                                <div class="rounded-xl overflow-hidden border border-stone-200 dark:border-stone-800 bg-stone-100 dark:bg-stone-900 group cursor-pointer relative shadow-xs"
                                     onclick="openImageLightbox('{{ Storage::url($file) }}', 'Supporting cultural media - {{ addslashes($culture->name) }}')">
                                    <img src="{{ Storage::url($file) }}" alt="Supporting cultural media" class="aspect-square w-full object-cover transition-transform duration-300 group-hover:scale-105">
                                    <div class="absolute inset-0 bg-black/20 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
                                        <i data-lucide="maximize-2" class="w-5 h-5 text-white"></i>
                                    </div>
                                    <div class="absolute bottom-2 left-2 px-2 py-0.5 rounded bg-black/60 backdrop-blur-xs text-white text-[10px] font-bold">
                                        Overview
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif

                    {{-- Contributed Community Images Linked to Sections --}}
                    @if($photoAssets->count() > 0)
                        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
                            @foreach($photoAssets as $asset)
                                <div x-show="mediaSectionFilter === 'all' || mediaSectionFilter === '{{ $asset->section ?? 'overview' }}'"
                                     class="rounded-xl overflow-hidden border border-stone-200 dark:border-stone-800 bg-stone-50 dark:bg-stone-900/60 flex flex-col justify-between shadow-xs">
                                    <div class="relative overflow-hidden bg-stone-200 dark:bg-stone-800 group cursor-pointer"
                                         onclick="openImageLightbox('{{ Storage::url($asset->file_path) }}', '{{ addslashes($asset->caption ?: $culture->name) }}')">
                                        <img src="{{ Storage::url($asset->file_path) }}" alt="{{ $asset->caption }}" class="w-full h-48 object-cover transition-transform duration-300 group-hover:scale-105">
                                        <div class="absolute inset-0 bg-black/20 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
                                            <i data-lucide="maximize-2" class="w-5 h-5 text-white"></i>
                                        </div>
                                        <span class="absolute top-2 left-2 px-2 py-0.5 rounded-full text-[10px] font-bold bg-black/70 text-amber-400 backdrop-blur-xs uppercase tracking-wide">
                                            {{ ucfirst($asset->section ?? 'Overview') }}
                                        </span>
                                    </div>
                                    <div class="p-3 text-xs space-y-1">
                                        <p class="font-semibold text-stone-800 dark:text-stone-200 line-clamp-2 leading-snug">
                                            {{ $asset->caption ?: 'Field photograph / artefact' }}
                                        </p>
                                        <div class="flex items-center justify-between text-[10px] text-stone-500 pt-1 border-t border-stone-100 dark:border-stone-800/60">
                                            <span>By: {{ $asset->creator_attribution ?: 'Community Contributor' }}</span>
                                            <span>{{ $asset->license_type ?: 'CC BY-SA' }}</span>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

                {{-- 2. CORRESPONDING VIDEOS & RECORDINGS --}}
                <div x-show="mediaTypeFilter === 'all' || mediaTypeFilter === 'videos'" class="space-y-4 pt-4 border-t border-stone-100 dark:border-stone-800">
                    <h3 class="text-xs font-bold uppercase tracking-wider text-stone-700 dark:text-stone-300 flex items-center gap-1.5">
                        <i data-lucide="video" class="w-3.5 h-3.5 text-blue-500"></i>
                        <span>Corresponding Field Videos & Recordings</span>
                    </h3>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        {{-- Legacy Video on Culture --}}
                        @if($culture->video_url || $culture->video_path)
                            <div x-show="mediaSectionFilter === 'all' || mediaSectionFilter === 'overview'" class="p-3.5 rounded-2xl border border-stone-200 dark:border-stone-800 bg-stone-50 dark:bg-stone-900/60 space-y-2">
                                <div class="flex items-center justify-between">
                                    <span class="px-2 py-0.5 rounded-full bg-blue-500/15 text-blue-600 dark:text-blue-400 font-bold text-[10px] uppercase">Overview</span>
                                    <span class="text-[11px] text-stone-400">Primary Cultural Video</span>
                                </div>
                                @if($culture->video_path)
                                    <video src="{{ Storage::url($culture->video_path) }}" controls class="w-full rounded-xl max-h-56 bg-black"></video>
                                @elseif($culture->video_url)
                                    @php
                                        $embedUrl = $culture->video_url;
                                        if (str_contains($embedUrl, 'youtube.com/watch?v=')) {
                                            $embedUrl = str_replace('watch?v=', 'embed/', $embedUrl);
                                        } elseif (str_contains($embedUrl, 'youtu.be/')) {
                                            $embedUrl = str_replace('youtu.be/', 'www.youtube.com/embed/', $embedUrl);
                                        }
                                    @endphp
                                    <div class="aspect-video w-full rounded-xl overflow-hidden bg-black">
                                        <iframe src="{{ $embedUrl }}" class="w-full h-full" allowfullscreen></iframe>
                                    </div>
                                @endif
                                <p class="text-xs font-semibold text-stone-700 dark:text-stone-300">{{ $culture->video_description ?: 'Archival video documenting traditional practice' }}</p>
                            </div>
                        @endif

                        {{-- Contributed Videos Linked to Sections --}}
                        @foreach($videoAssets as $asset)
                            <div x-show="mediaSectionFilter === 'all' || mediaSectionFilter === '{{ $asset->section ?? 'overview' }}'"
                                 class="p-3.5 rounded-2xl border border-stone-200 dark:border-stone-800 bg-stone-50 dark:bg-stone-900/60 space-y-2">
                                <div class="flex items-center justify-between">
                                    <span class="px-2 py-0.5 rounded-full bg-blue-500/15 text-blue-600 dark:text-blue-400 font-bold text-[10px] uppercase">
                                        Linked to: {{ ucfirst($asset->section ?? 'Overview') }}
                                    </span>
                                    <span class="text-[10px] text-stone-400">By: {{ $asset->creator_attribution ?: 'Community' }}</span>
                                </div>
                                @if($asset->file_path && $asset->file_path !== 'external')
                                    <video src="{{ Storage::url($asset->file_path) }}" controls class="w-full rounded-xl max-h-56 bg-black"></video>
                                @elseif($asset->external_url)
                                    @php
                                        $vUrl = $asset->external_url;
                                        if (str_contains($vUrl, 'youtube.com/watch?v=')) {
                                            $vUrl = str_replace('watch?v=', 'embed/', $vUrl);
                                        } elseif (str_contains($vUrl, 'youtu.be/')) {
                                            $vUrl = str_replace('youtu.be/', 'www.youtube.com/embed/', $vUrl);
                                        }
                                    @endphp
                                    <div class="aspect-video w-full rounded-xl overflow-hidden bg-black">
                                        <iframe src="{{ $vUrl }}" class="w-full h-full" allowfullscreen></iframe>
                                    </div>
                                @endif
                                <p class="text-xs font-semibold text-stone-800 dark:text-stone-200">{{ $asset->caption ?: 'Field video recording' }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- 3. WIKIPEDIA-STYLE BOOKS & SCHOLARLY REFERENCES --}}
                <div x-show="mediaTypeFilter === 'all' || mediaTypeFilter === 'books'" class="space-y-4 pt-4 border-t border-stone-100 dark:border-stone-800">
                    <div class="flex items-center justify-between">
                        <h3 class="text-xs font-bold uppercase tracking-wider text-stone-700 dark:text-stone-300 flex items-center gap-1.5">
                            <i data-lucide="book" class="w-3.5 h-3.5 text-emerald-500"></i>
                            <span>Books, Monographs & Bibliography (Wikipedia Citation Format)</span>
                        </h3>
                        <span class="text-[11px] font-bold text-emerald-600 dark:text-emerald-400">{{ $bookAssets->count() }} Reference{{ $bookAssets->count() === 1 ? '' : 's' }}</span>
                    </div>

                    @if($bookAssets->count() > 0)
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            @foreach($bookAssets as $asset)
                                <div x-show="mediaSectionFilter === 'all' || mediaSectionFilter === '{{ $asset->section ?? 'overview' }}'"
                                     class="p-4 rounded-2xl border border-stone-200 dark:border-stone-800 bg-stone-50 dark:bg-stone-900/60 space-y-3 flex flex-col justify-between hover:border-amber-500/40 transition">
                                    <div class="space-y-2">
                                        <div class="flex items-center justify-between gap-2">
                                            <span class="px-2 py-0.5 rounded-md bg-emerald-500/10 text-emerald-700 dark:text-emerald-300 text-[10px] font-bold uppercase">
                                                Citing Section: {{ ucfirst($asset->section ?? 'Overview') }}
                                            </span>
                                            @if($asset->book_year)
                                                <span class="text-[11px] font-mono font-bold text-stone-500">{{ $asset->book_year }}</span>
                                            @endif
                                        </div>

                                        <h4 class="font-serif italic font-bold text-sm text-stone-900 dark:text-stone-100">
                                            "{{ $asset->book_title }}"
                                        </h4>

                                        <div class="text-xs text-stone-600 dark:text-stone-400 space-y-0.5">
                                            @if($asset->book_author)
                                                <p><span class="font-bold text-stone-700 dark:text-stone-300">Author:</span> {{ $asset->book_author }}</p>
                                            @endif
                                            @if($asset->book_publisher)
                                                <p><span class="font-bold text-stone-700 dark:text-stone-300">Publisher:</span> {{ $asset->book_publisher }}</p>
                                            @endif
                                            @if($asset->book_isbn)
                                                <p><span class="font-mono text-[10px] bg-stone-200 dark:bg-stone-800 px-1.5 py-0.5 rounded text-stone-600 dark:text-stone-300">ISBN: {{ $asset->book_isbn }}</span></p>
                                            @endif
                                        </div>

                                        @if($asset->book_excerpt)
                                            <blockquote class="p-2.5 rounded-xl bg-amber-500/5 border-l-2 border-amber-500 text-[11px] text-stone-700 dark:text-stone-300 italic leading-relaxed">
                                                {{ $asset->book_excerpt }}
                                            </blockquote>
                                        @endif
                                    </div>

                                    <div class="pt-2 border-t border-stone-200 dark:border-stone-800 flex items-center justify-between text-[11px]">
                                        <span class="text-stone-400">Added by: {{ $asset->creator_attribution ?: 'Community' }}</span>
                                        @if($asset->external_url)
                                            <a href="{{ $asset->external_url }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-1 font-bold text-amber-600 dark:text-amber-400 hover:underline">
                                                <span>Library Link</span>
                                                <i data-lucide="external-link" class="w-3 h-3"></i>
                                            </a>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="p-4 rounded-xl border border-dashed border-stone-300 dark:border-stone-800 text-center text-xs text-stone-500">
                            No books or publications have been linked yet.
                            <button type="button" @click="activeSection = 'contribute'; contributionType = 'book_reference'" class="text-amber-600 font-bold hover:underline ml-1">
                                Be the first to cite a book for this tradition!
                            </button>
                        </div>
                    @endif
                </div>

                {{-- Bottom Wikipedia Style Quick Action --}}
                <div class="p-4 rounded-xl bg-gradient-to-r from-amber-500/10 via-orange-500/5 to-amber-500/10 border border-amber-500/20 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs">
                    <div>
                        <span class="font-bold text-stone-900 dark:text-stone-100 block">Do you possess photos, documentary recordings, or books about this tradition?</span>
                        <span class="text-stone-500">Contribute them directly to any section to preserve authentic cultural knowledge.</span>
                    </div>
                    <button type="button" @click="activeSection = 'contribute'" class="px-4 py-2 rounded-xl bg-amber-500 hover:bg-amber-600 text-white font-bold transition shadow-xs shrink-0">
                        Add to Wikipedia Archive
                    </button>
                </div>
            </div>
        </div>

        {{-- SECTION 7: RELATED KNOWLEDGE --}}
        @if(!empty($relatedCultures) && count($relatedCultures) > 0)
            <div x-show="activeSection === 'related'" class="space-y-6">
                <div class="rounded-2xl border border-stone-200/90 dark:border-stone-800 bg-white dark:bg-[#131218] p-5 sm:p-8 space-y-6">
                    <div>
                        <h2 class="text-sm font-bold uppercase tracking-wider text-amber-600 dark:text-amber-400 flex items-center gap-2">
                            <i data-lucide="network" class="w-4 h-4"></i>
                            <span>Connected Traditions & Knowledge Graph</span>
                        </h2>
                        <p class="text-xs text-stone-500 mt-1">Discovered through shared language families, ceremonial overlap, and geographic proximity</p>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        @foreach($relatedCultures as $related)
                            <a href="{{ route('cultural-hub.show', $related->id) }}" 
                               class="group block p-4 rounded-xl border border-stone-200 dark:border-stone-800 hover:border-amber-500 bg-stone-50/50 dark:bg-stone-900/50 transition">
                                <span class="text-[10px] font-bold uppercase tracking-wider text-amber-600 dark:text-amber-400 block mb-1">
                                    {{ $related->category }}
                                </span>
                                <h3 class="text-sm font-bold text-stone-900 dark:text-stone-100 group-hover:text-amber-600 transition leading-snug">
                                    {{ $related->name }}
                                </h3>
                                <p class="text-xs text-stone-500 mt-1 flex items-center gap-1">
                                    <i data-lucide="map-pin" class="w-3 h-3"></i>
                                    <span>{{ $related->region }}</span>
                                </p>
                            </a>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif

        {{-- SECTION 8: SOURCES & VERIFICATION --}}
        <div x-show="activeSection === 'sources'" class="space-y-6">
            <div class="rounded-2xl border border-stone-200/90 dark:border-stone-800 bg-white dark:bg-[#131218] p-5 sm:p-8 space-y-6">
                <div>
                    <h2 class="text-sm font-bold uppercase tracking-wider text-amber-600 dark:text-amber-400 flex items-center gap-2">
                        <i data-lucide="check-circle" class="w-4 h-4"></i>
                        <span>Claims, Citations & Source Verification</span>
                    </h2>
                    <p class="text-xs text-stone-500 mt-1">
                        Every factual assertion is traced to source testimony, publications, or oral interviews.
                    </p>
                </div>

                {{-- Claims Table / Cards --}}
                @if($culture->claims->count() > 0)
                    <div class="space-y-3">
                        @foreach($culture->claims as $claim)
                            @php
                                $cBadge = $claim->review_badge;
                            @endphp
                            <div class="p-4 rounded-xl border border-stone-200 dark:border-stone-800 bg-stone-50/40 dark:bg-stone-900/40 space-y-2 text-xs">
                                <div class="flex items-center justify-between flex-wrap gap-2">
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider bg-stone-200 dark:bg-stone-800 capitalize">
                                        {{ str_replace('_', ' ', $claim->claim_type) }}
                                    </span>
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold border {{ $cBadge['class'] }}">
                                        {{ $cBadge['label'] }}
                                    </span>
                                </div>
                                <p class="text-stone-900 dark:text-stone-100 font-medium text-sm leading-relaxed">
                                    {{ $claim->claim_text }}
                                </p>
                                @if($claim->evidence_reference)
                                    <p class="text-[11px] text-stone-500 italic border-l-2 border-stone-300 dark:border-stone-700 pl-2">
                                        Evidence Excerpt: "{{ $claim->evidence_reference }}"
                                    </p>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="p-6 rounded-xl border border-dashed border-stone-200 dark:border-stone-800 text-center space-y-2">
                        <i data-lucide="file-question" class="w-8 h-8 text-stone-400 mx-auto"></i>
                        <h3 class="text-xs font-bold text-stone-700 dark:text-stone-300">No discrete claims catalogued yet</h3>
                        <p class="text-xs text-stone-500 max-w-md mx-auto">
                            This record is preserved as a holistic narrative. Knowledge keepers and researchers can submit itemized claims in the Contribute tab.
                        </p>
                    </div>
                @endif

                {{-- Moderator Review Status Controls (Guardians & Admins) --}}
                @if(auth()->check() && (auth()->id() === $culture->submitted_by || auth()->user()->is_admin))
                    <div class="pt-4 border-t border-stone-200 dark:border-stone-800 space-y-3">
                        <h3 class="text-xs font-bold uppercase tracking-wider text-stone-700 dark:text-stone-300">Guardian / Moderator Review Control</h3>
                        <form action="{{ route('cultural-hub.review-status', $culture->id) }}" method="POST" class="flex flex-wrap items-center gap-2">
                            @csrf
                            <label class="text-xs text-stone-500 font-medium">Record Status:</label>
                            <div class="w-64">
                                <x-custom-select 
                                    name="review_status" 
                                    :options="[
                                        'published' => 'Published Archive',
                                        'under_review' => 'Under Community Review',
                                        'awaiting_contributions' => 'Awaiting Evidence',
                                        'disputed' => 'Disputed Accounts',
                                        'restricted' => 'Restricted Sacred Knowledge'
                                    ]"
                                    :value="$culture->review_status"
                                    placeholder="Choose status..." 
                                    icon="shield-check"
                                    :searchable="false" />
                            </div>
                            <button type="submit" class="px-4 py-2.5 rounded-xl bg-stone-900 dark:bg-stone-100 text-white dark:text-black text-xs font-bold transition">
                                Update Status
                            </button>
                        </form>
                    </div>
                @endif
            </div>
        </div>

        {{-- SECTION 9: CONTRIBUTE (Wikipedia Style) --}}
        <div x-show="activeSection === 'contribute'" class="space-y-6">
            <div class="rounded-2xl border border-stone-200/90 dark:border-stone-800 bg-white dark:bg-[#131218] p-5 sm:p-8 space-y-6">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-stone-100 dark:border-stone-800 pb-4">
                    <div>
                        <h2 class="text-sm sm:text-base font-bold uppercase tracking-wider text-amber-600 dark:text-amber-400 flex items-center gap-2">
                            <i data-lucide="plus-circle" class="w-4 h-4"></i>
                            <span>Contribute Knowledge, Media & Books (Wikipedia Style)</span>
                        </h2>
                        <p class="text-xs text-stone-500 mt-1">
                            Contribute photos, field videos, book citations, or factual context directly to any section of this cultural record.
                        </p>
                    </div>
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-amber-500/10 text-amber-600 dark:text-amber-400 text-xs font-bold shrink-0">
                        <i data-lucide="shield-check" class="w-3.5 h-3.5"></i>
                        <span>Living Wikipedia Archive</span>
                    </span>
                </div>

                <form action="{{ route('cultural-hub.contribute', $culture->id) }}" method="POST" enctype="multipart/form-data" class="space-y-5 max-w-3xl" x-data="{
                    targetSection: 'overview',
                    contributionType: 'additional_context',
                    showImageUpload: true,
                    showVideoUpload: false,
                    showBookReference: false,
                    imagePreview: null,
                    previewImage(e) {
                        const file = e.target.files[0];
                        if (file) {
                            this.imagePreview = URL.createObjectURL(file);
                        }
                    }
                }">
                    @csrf

                    <!-- 1. Target Topic Section (Wikipedia Section Linking) -->
                    <div class="p-4 rounded-xl bg-amber-500/5 dark:bg-amber-950/20 border border-amber-500/20 space-y-2">
                        <label class="block text-xs font-bold text-stone-800 dark:text-stone-200">
                            1. Which section of this cultural record are you talking about? *
                        </label>
                        <div class="grid grid-cols-2 sm:grid-cols-3 gap-2">
                            <label class="flex items-center gap-2 p-2.5 rounded-lg border text-xs font-medium cursor-pointer transition"
                                   :class="targetSection === 'overview' ? 'border-amber-500 bg-amber-500/10 text-amber-600 dark:text-amber-400 font-bold' : 'border-stone-200 dark:border-stone-800 text-stone-600 dark:text-stone-400 hover:border-amber-400'">
                                <input type="radio" name="target_section" value="overview" x-model="targetSection" class="text-amber-500 hidden">
                                <i data-lucide="book-open" class="w-3.5 h-3.5"></i>
                                <span>Overview</span>
                            </label>
                            <label class="flex items-center gap-2 p-2.5 rounded-lg border text-xs font-medium cursor-pointer transition"
                                   :class="targetSection === 'history' ? 'border-amber-500 bg-amber-500/10 text-amber-600 dark:text-amber-400 font-bold' : 'border-stone-200 dark:border-stone-800 text-stone-600 dark:text-stone-400 hover:border-amber-400'">
                                <input type="radio" name="target_section" value="history" x-model="targetSection" class="text-amber-500 hidden">
                                <i data-lucide="history" class="w-3.5 h-3.5"></i>
                                <span>History & Origins</span>
                            </label>
                            <label class="flex items-center gap-2 p-2.5 rounded-lg border text-xs font-medium cursor-pointer transition"
                                   :class="targetSection === 'practices' ? 'border-amber-500 bg-amber-500/10 text-amber-600 dark:text-amber-400 font-bold' : 'border-stone-200 dark:border-stone-800 text-stone-600 dark:text-stone-400 hover:border-amber-400'">
                                <input type="radio" name="target_section" value="practices" x-model="targetSection" class="text-amber-500 hidden">
                                <i data-lucide="compass" class="w-3.5 h-3.5"></i>
                                <span>Practices & Rituals</span>
                            </label>
                            <label class="flex items-center gap-2 p-2.5 rounded-lg border text-xs font-medium cursor-pointer transition"
                                   :class="targetSection === 'people' ? 'border-amber-500 bg-amber-500/10 text-amber-600 dark:text-amber-400 font-bold' : 'border-stone-200 dark:border-stone-800 text-stone-600 dark:text-stone-400 hover:border-amber-400'">
                                <input type="radio" name="target_section" value="people" x-model="targetSection" class="text-amber-500 hidden">
                                <i data-lucide="users" class="w-3.5 h-3.5"></i>
                                <span>People & Lineage</span>
                            </label>
                            <label class="flex items-center gap-2 p-2.5 rounded-lg border text-xs font-medium cursor-pointer transition"
                                   :class="targetSection === 'media' ? 'border-amber-500 bg-amber-500/10 text-amber-600 dark:text-amber-400 font-bold' : 'border-stone-200 dark:border-stone-800 text-stone-600 dark:text-stone-400 hover:border-amber-400'">
                                <input type="radio" name="target_section" value="media" x-model="targetSection" class="text-amber-500 hidden">
                                <i data-lucide="film" class="w-3.5 h-3.5"></i>
                                <span>Media Gallery</span>
                            </label>
                            <label class="flex items-center gap-2 p-2.5 rounded-lg border text-xs font-medium cursor-pointer transition"
                                   :class="targetSection === 'sources' ? 'border-amber-500 bg-amber-500/10 text-amber-600 dark:text-amber-400 font-bold' : 'border-stone-200 dark:border-stone-800 text-stone-600 dark:text-stone-400 hover:border-amber-400'">
                                <input type="radio" name="target_section" value="sources" x-model="targetSection" class="text-amber-500 hidden">
                                <i data-lucide="check-circle" class="w-3.5 h-3.5"></i>
                                <span>Sources & Books</span>
                            </label>
                        </div>
                        <p class="text-[11px] text-stone-500">
                            Media and references you attach below will be saved under the media section and tagged to <span class="font-bold text-amber-600 uppercase" x-text="targetSection"></span>.
                        </p>
                    </div>

                    <!-- 2. Contribution Nature -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-stone-700 dark:text-stone-300 mb-1">Contribution Type *</label>
                            <select name="revision_type" x-model="contributionType" required class="w-full text-xs rounded-xl border border-stone-200 dark:border-stone-800 bg-white dark:bg-stone-900 px-3 py-2 text-stone-800 dark:text-stone-200">
                                <option value="additional_context">Provide Additional Context / History</option>
                                <option value="new_media">Upload Photographs / Artefacts</option>
                                <option value="book_reference">Add Book / Scholarly Reference (Wikipedia)</option>
                                <option value="correction">Suggest Factual Correction</option>
                                <option value="alternative_account">Provide Regional / Clan Variation</option>
                                <option value="additional_source">Cite Supporting Primary Source</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-stone-700 dark:text-stone-300 mb-1">Contribution Heading (Optional)</label>
                            <input type="text" name="title" placeholder="e.g., Elder dialect variation or Field photographs" class="w-full text-xs rounded-xl border border-stone-200 dark:border-stone-800 bg-white dark:bg-stone-900 px-3 py-2 text-stone-800 dark:text-stone-200">
                        </div>
                    </div>

                    <!-- 3. Written Testimony / Narrative -->
                    <div>
                        <label class="block text-xs font-bold text-stone-700 dark:text-stone-300 mb-1">Testimony / Descriptive Narrative *</label>
                        <textarea name="proposed_content" required rows="4" placeholder="Detail the account, variation, historical background, or description of the media you are contributing..." class="w-full text-xs rounded-xl border border-stone-200 dark:border-stone-800 bg-white dark:bg-stone-900 p-3 text-stone-800 dark:text-stone-200 leading-relaxed"></textarea>
                    </div>

                    <!-- 4. Media Section Attachments (Image, Video, Books) -->
                    <div class="space-y-3 pt-2 border-t border-stone-200 dark:border-stone-800">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-bold uppercase tracking-wider text-stone-700 dark:text-stone-300 flex items-center gap-1.5">
                                <i data-lucide="paperclip" class="w-3.5 h-3.5 text-amber-500"></i>
                                <span>Media & Literature for this Section (Wikipedia Style)</span>
                            </span>
                            <div class="flex items-center gap-1.5 text-xs">
                                <button type="button" @click="showImageUpload = !showImageUpload" :class="showImageUpload ? 'bg-amber-500 text-white' : 'bg-stone-100 dark:bg-stone-800 text-stone-600 dark:text-stone-400'" class="px-2.5 py-1 rounded-lg font-bold text-[11px] transition">
                                    Photo
                                </button>
                                <button type="button" @click="showVideoUpload = !showVideoUpload" :class="showVideoUpload ? 'bg-amber-500 text-white' : 'bg-stone-100 dark:bg-stone-800 text-stone-600 dark:text-stone-400'" class="px-2.5 py-1 rounded-lg font-bold text-[11px] transition">
                                    Video
                                </button>
                                <button type="button" @click="showBookReference = !showBookReference" :class="showBookReference ? 'bg-amber-500 text-white' : 'bg-stone-100 dark:bg-stone-800 text-stone-600 dark:text-stone-400'" class="px-2.5 py-1 rounded-lg font-bold text-[11px] transition">
                                    Book / Citation
                                </button>
                            </div>
                        </div>

                        <!-- 4a. Photo / Artefact Attachment -->
                        <div x-show="showImageUpload" x-transition class="p-4 rounded-xl border border-stone-200 dark:border-stone-800 bg-stone-50 dark:bg-stone-900/60 space-y-3">
                            <div class="flex items-center gap-2 text-xs font-bold text-stone-800 dark:text-stone-200">
                                <i data-lucide="image" class="w-4 h-4 text-amber-500"></i>
                                <span>Photographic / Artefact Documentation</span>
                            </div>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-[11px] font-semibold text-stone-600 dark:text-stone-400 mb-1">Upload Photo (PNG, JPG, WEBP up to 15MB)</label>
                                    <input type="file" name="image" accept="image/*" @change="previewImage($event)" class="w-full text-xs text-stone-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-amber-500 file:text-white hover:file:bg-amber-600">
                                </div>
                                <div>
                                    <label class="block text-[11px] font-semibold text-stone-600 dark:text-stone-400 mb-1">Image Caption / Context</label>
                                    <input type="text" name="image_caption" placeholder="e.g., Ceremonial attire worn during the harvest rite" class="w-full text-xs rounded-lg border border-stone-200 dark:border-stone-800 bg-white dark:bg-stone-900 px-3 py-1.5 text-stone-800 dark:text-stone-200">
                                </div>
                            </div>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-[11px] font-semibold text-stone-600 dark:text-stone-400 mb-1">Photographer / Creator Credit</label>
                                    <input type="text" name="image_attribution" placeholder="e.g., Photo by Amara Okafor, 2024" class="w-full text-xs rounded-lg border border-stone-200 dark:border-stone-800 bg-white dark:bg-stone-900 px-3 py-1.5 text-stone-800 dark:text-stone-200">
                                </div>
                                <div>
                                    <label class="block text-[11px] font-semibold text-stone-600 dark:text-stone-400 mb-1">Copyright / License</label>
                                    <select name="license_type" class="w-full text-xs rounded-lg border border-stone-200 dark:border-stone-800 bg-white dark:bg-stone-900 px-3 py-1.5 text-stone-800 dark:text-stone-200">
                                        <option value="Community Commons (CC BY-SA 4.0)">Community Commons (CC BY-SA 4.0)</option>
                                        <option value="Public Domain">Public Domain</option>
                                        <option value="Personal Archive / Permission Granted">Personal Archive / Permission Granted</option>
                                        <option value="Traditional Knowledge Cultural Rights">Traditional Knowledge Cultural Rights</option>
                                    </select>
                                </div>
                            </div>
                            <template x-if="imagePreview">
                                <div class="mt-2">
                                    <span class="text-[10px] uppercase font-bold text-stone-400 block mb-1">Image Preview:</span>
                                    <img :src="imagePreview" class="h-28 rounded-lg object-contain border border-stone-200 dark:border-stone-800 bg-black/10">
                                </div>
                            </template>
                        </div>

                        <!-- 4b. Video Attachment -->
                        <div x-show="showVideoUpload" x-transition class="p-4 rounded-xl border border-stone-200 dark:border-stone-800 bg-stone-50 dark:bg-stone-900/60 space-y-3">
                            <div class="flex items-center gap-2 text-xs font-bold text-stone-800 dark:text-stone-200">
                                <i data-lucide="video" class="w-4 h-4 text-blue-500"></i>
                                <span>Corresponding Video Documentation</span>
                            </div>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-[11px] font-semibold text-stone-600 dark:text-stone-400 mb-1">External Video URL (YouTube, Vimeo, Archive.org)</label>
                                    <input type="url" name="video_url" placeholder="https://www.youtube.com/watch?v=..." class="w-full text-xs rounded-lg border border-stone-200 dark:border-stone-800 bg-white dark:bg-stone-900 px-3 py-1.5 text-stone-800 dark:text-stone-200">
                                </div>
                                <div>
                                    <label class="block text-[11px] font-semibold text-stone-600 dark:text-stone-400 mb-1">Or Upload Video File (MP4, WEBM up to 50MB)</label>
                                    <input type="file" name="video" accept="video/mp4,video/webm,video/quicktime" class="w-full text-xs text-stone-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-blue-600 file:text-white hover:file:bg-blue-700">
                                </div>
                            </div>
                            <div>
                                <label class="block text-[11px] font-semibold text-stone-600 dark:text-stone-400 mb-1">Video Caption / Title</label>
                                <input type="text" name="video_caption" placeholder="e.g., Live demonstration of ancient indigo dyeing process" class="w-full text-xs rounded-lg border border-stone-200 dark:border-stone-800 bg-white dark:bg-stone-900 px-3 py-1.5 text-stone-800 dark:text-stone-200">
                            </div>
                        </div>

                        <!-- 4c. Book & Scholarly Publication (Wikipedia Style) -->
                        <div x-show="showBookReference || contributionType === 'book_reference'" x-transition class="p-4 rounded-xl border border-amber-500/30 bg-amber-500/5 dark:bg-amber-950/20 space-y-3">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-2 text-xs font-bold text-amber-700 dark:text-amber-300">
                                    <i data-lucide="book" class="w-4 h-4 text-amber-500"></i>
                                    <span>Book / Publication Reference (Wikipedia Bibliography)</span>
                                </div>
                                <span class="text-[10px] font-mono text-stone-400">Cited in section: <span class="uppercase font-bold text-amber-600" x-text="targetSection"></span></span>
                            </div>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-[11px] font-semibold text-stone-700 dark:text-stone-300 mb-1">Book / Monograph Title *</label>
                                    <input type="text" name="book_title" placeholder="e.g., The History of the Yorubas" class="w-full text-xs rounded-lg border border-stone-200 dark:border-stone-800 bg-white dark:bg-stone-900 px-3 py-1.5 text-stone-800 dark:text-stone-200">
                                </div>
                                <div>
                                    <label class="block text-[11px] font-semibold text-stone-700 dark:text-stone-300 mb-1">Author(s) / Editor(s)</label>
                                    <input type="text" name="book_author" placeholder="e.g., Samuel Johnson, O. Biobaku" class="w-full text-xs rounded-lg border border-stone-200 dark:border-stone-800 bg-white dark:bg-stone-900 px-3 py-1.5 text-stone-800 dark:text-stone-200">
                                </div>
                            </div>
                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                                <div>
                                    <label class="block text-[11px] font-semibold text-stone-700 dark:text-stone-300 mb-1">Publication Year</label>
                                    <input type="text" name="book_year" placeholder="e.g., 1921 or 1998" class="w-full text-xs rounded-lg border border-stone-200 dark:border-stone-800 bg-white dark:bg-stone-900 px-3 py-1.5 text-stone-800 dark:text-stone-200">
                                </div>
                                <div>
                                    <label class="block text-[11px] font-semibold text-stone-700 dark:text-stone-300 mb-1">Publisher / Press</label>
                                    <input type="text" name="book_publisher" placeholder="e.g., Oxford University Press" class="w-full text-xs rounded-lg border border-stone-200 dark:border-stone-800 bg-white dark:bg-stone-900 px-3 py-1.5 text-stone-800 dark:text-stone-200">
                                </div>
                                <div>
                                    <label class="block text-[11px] font-semibold text-stone-700 dark:text-stone-300 mb-1">ISBN / Catalog #</label>
                                    <input type="text" name="book_isbn" placeholder="e.g., 978-0415301404" class="w-full text-xs rounded-lg border border-stone-200 dark:border-stone-800 bg-white dark:bg-stone-900 px-3 py-1.5 text-stone-800 dark:text-stone-200">
                                </div>
                            </div>
                            <div>
                                <label class="block text-[11px] font-semibold text-stone-700 dark:text-stone-300 mb-1">Key Excerpt / Citation / Page # (Wikipedia Quote)</label>
                                <textarea name="book_excerpt" rows="2" placeholder="e.g., Page 45: 'The oral traditions of the drum language recount that...'" class="w-full text-xs rounded-lg border border-stone-200 dark:border-stone-800 bg-white dark:bg-stone-900 p-2 text-stone-800 dark:text-stone-200"></textarea>
                            </div>
                            <div>
                                <label class="block text-[11px] font-semibold text-stone-700 dark:text-stone-300 mb-1">Digital Link / Archive Catalog URL (Optional)</label>
                                <input type="url" name="book_url" placeholder="https://books.google.com/... or https://archive.org/..." class="w-full text-xs rounded-lg border border-stone-200 dark:border-stone-800 bg-white dark:bg-stone-900 px-3 py-1.5 text-stone-800 dark:text-stone-200">
                            </div>
                        </div>
                    </div>

                    <!-- 5. General Evidence / Provenance Basis -->
                    <div>
                        <label class="block text-xs font-bold text-stone-700 dark:text-stone-300 mb-1">General Evidence / Provenance Basis (Optional)</label>
                        <input type="text" name="evidence_notes" placeholder="e.g., Oral interview with village elder Baba Alabi, or museum field record" class="w-full text-xs rounded-xl border border-stone-200 dark:border-stone-800 bg-white dark:bg-stone-900 px-3 py-2 text-stone-800 dark:text-stone-200">
                    </div>

                    @guest
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-bold text-stone-700 dark:text-stone-300 mb-1">Your Name (Optional)</label>
                                <input type="text" name="contributor_name" placeholder="Community Member" class="w-full text-xs rounded-xl border border-stone-200 dark:border-stone-800 bg-white dark:bg-stone-900 px-3 py-2 text-stone-800 dark:text-stone-200">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-stone-700 dark:text-stone-300 mb-1">Email for Follow-up (Optional)</label>
                                <input type="email" name="contributor_email" placeholder="contact@example.org" class="w-full text-xs rounded-xl border border-stone-200 dark:border-stone-800 bg-white dark:bg-stone-900 px-3 py-2 text-stone-800 dark:text-stone-200">
                            </div>
                        </div>
                    @endguest

                    <button type="submit" class="px-6 py-3 rounded-xl bg-gradient-to-r from-amber-500 to-orange-500 hover:from-amber-600 hover:to-orange-600 text-white font-bold text-xs sm:text-sm transition flex items-center justify-center gap-2 shadow-md shadow-amber-500/20 active:scale-95">
                        <i data-lucide="send" class="w-4 h-4"></i>
                        <span>Save & Link to Media Section</span>
                    </button>
                </form>
            </div>
        </div>

    </main>

    {{-- ACADEMIC CITATION MODAL --}}
    <div x-show="openCitationModal" 
         x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm"
         @click.self="openCitationModal = false">
        <div class="w-full max-w-xl rounded-2xl bg-white dark:bg-[#131218] border border-stone-200 dark:border-stone-800 p-6 space-y-4 shadow-xl">
            <div class="flex items-center justify-between border-b pb-3 border-stone-100 dark:border-stone-800">
                <h3 class="text-sm font-bold uppercase tracking-wider text-stone-900 dark:text-stone-100 flex items-center gap-2">
                    <i data-lucide="quote" class="w-4 h-4 text-amber-500"></i>
                    <span>Cite This Cultural Record</span>
                </h3>
                <button @click="openCitationModal = false" class="text-stone-400 hover:text-stone-600">
                    <i data-lucide="x" class="w-4 h-4"></i>
                </button>
            </div>

            <div class="space-y-3 text-xs">
                <div>
                    <span class="font-bold text-stone-700 dark:text-stone-300 block mb-1">APA Format:</span>
                    <div class="p-3 bg-stone-50 dark:bg-stone-900 rounded-xl font-mono text-[11px] border border-stone-200 dark:border-stone-800 select-all">
                        {{ $culture->submitter->name ?? 'Kulturegram Archive' }}. ({{ $culture->created_at->format('Y') }}). <em>{{ $culture->name }}</em> [Cultural Heritage Record #CR-{{ str_pad($culture->id, 5, '0', STR_PAD_LEFT) }}]. Kulturegram Cultural Archive. {{ url()->current() }}
                    </div>
                </div>

                <div>
                    <span class="font-bold text-stone-700 dark:text-stone-300 block mb-1">Chicago / Turabian:</span>
                    <div class="p-3 bg-stone-50 dark:bg-stone-900 rounded-xl font-mono text-[11px] border border-stone-200 dark:border-stone-800 select-all">
                        "{{ $culture->name }}." Kulturegram Cultural Archive Record #CR-{{ str_pad($culture->id, 5, '0', STR_PAD_LEFT) }}. Accessed {{ now()->format('F j, Y') }}. {{ url()->current() }}.
                    </div>
                </div>
            </div>

            <div class="pt-2 flex justify-end">
                <button @click="copyCitationText(); openCitationModal = false"
                        class="px-4 py-2 rounded-xl bg-amber-500 hover:bg-amber-600 text-white font-bold text-xs transition">
                    Copy Citation
                </button>
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

    function culturalArchiveDossier(config) {
        return {
            cultureId: config.cultureId,
            lockedIn: config.lockedIn,
            lockCount: config.lockCount,
            resonated: config.resonated,
            resonanceCount: config.resonanceCount,
            csrfToken: config.csrfToken,
            activeSection: config.activeSection || 'overview',
            mediaTypeFilter: 'all',
            mediaSectionFilter: 'all',
            fontSize: 17,
            openCitationModal: false,
            isSubmittingLock: false,
            isSubmittingPulse: false,

            async toggleLockIn() {
                if (this.isSubmittingLock) return;
                this.isSubmittingLock = true;
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
                    console.error('Preserve lock-in failed:', e);
                } finally {
                    this.isSubmittingLock = false;
                    this.$nextTick(() => lucide.createIcons());
                }
            },

            async toggleResonance() {
                if (this.isSubmittingPulse) return;
                this.isSubmittingPulse = true;
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
                    console.error('Pulse failed:', e);
                } finally {
                    this.isSubmittingPulse = false;
                    this.$nextTick(() => lucide.createIcons());
                }
            },

            copyShareLink() {
                navigator.clipboard.writeText(window.location.href);
                alert('Archive record link copied to clipboard!');
            },

            copyCitationText() {
                const text = `{{ $culture->submitter->name ?? 'Kulturegram Archive' }}. ({{ $culture->created_at->format('Y') }}). {{ $culture->name }} [Cultural Heritage Record #CR-{{ str_pad($culture->id, 5, '0', STR_PAD_LEFT) }}]. Kulturegram. ${window.location.href}`;
                navigator.clipboard.writeText(text);
                alert('Citation copied to clipboard!');
            }
        };
    }
</script>
@endpush