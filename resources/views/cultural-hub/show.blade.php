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
        activeSection: 'overview'
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
                    <div class="relative w-full min-h-[240px] max-h-[520px] flex items-center justify-center overflow-hidden group cursor-pointer"
                         onclick="openImageLightbox('{{ Storage::url($culture->image) }}', '{{ addslashes($culture->name) }} - Archival Record')">
                        <div class="absolute inset-0 bg-cover bg-center filter blur-xl opacity-25 dark:opacity-20 scale-110 pointer-events-none"
                             style="background-image: url('{{ Storage::url($culture->image) }}');"></div>
                        <img src="{{ Storage::url($culture->image) }}" 
                             alt="{{ $culture->name }}"
                             class="relative z-10 w-auto h-auto max-w-full max-h-[520px] object-contain mx-auto transition-transform duration-300 group-hover:scale-[1.01]">
                        <span class="absolute bottom-3 right-3 z-20 px-2.5 py-1 rounded-lg bg-black/65 backdrop-blur-md text-white text-[11px] font-medium opacity-0 group-hover:opacity-100 transition-opacity duration-200 flex items-center gap-1.5 pointer-events-none">
                            <i data-lucide="maximize-2" class="w-3.5 h-3.5"></i>
                            <span>View full</span>
                        </span>
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

                {{-- 6. Media & Artefacts --}}
                @if($culture->image || $culture->media_files || $culture->audio_path || $culture->video_url || $culture->mediaAssets->count() > 0)
                    <button @click="activeSection = 'media'"
                            class="px-3.5 py-2 rounded-t-lg text-xs font-bold transition flex items-center gap-1.5 border-b-2"
                            :class="activeSection === 'media' 
                                ? 'border-amber-500 text-amber-600 dark:text-amber-400 bg-amber-500/5' 
                                : 'border-transparent text-stone-600 dark:text-stone-400 hover:text-stone-900 dark:hover:text-stone-200'">
                        <i data-lucide="film" class="w-3.5 h-3.5"></i>
                        <span>Media & Artefacts</span>
                    </button>
                @endif

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

        {{-- SECTION 6: MEDIA & ARTEFACTS --}}
        @if($culture->image || $culture->media_files || $culture->audio_path || $culture->video_url || $culture->mediaAssets->count() > 0)
            <div x-show="activeSection === 'media'" class="space-y-6">
                <div class="rounded-2xl border border-stone-200/90 dark:border-stone-800 bg-white dark:bg-[#131218] p-5 sm:p-8 space-y-6">
                    <div>
                        <h2 class="text-sm font-bold uppercase tracking-wider text-amber-600 dark:text-amber-400 flex items-center gap-2">
                            <i data-lucide="film" class="w-4 h-4"></i>
                            <span>Photographs, Artefacts & Supporting Media</span>
                        </h2>
                        <p class="text-xs text-stone-500 mt-1">Every media asset tracks permissions, origin, and license rights</p>
                    </div>

                    {{-- Primary Image --}}
                    @if($culture->image)
                        <div class="rounded-xl overflow-hidden border border-stone-200 dark:border-stone-800 bg-stone-50 dark:bg-stone-900">
                            <div class="relative overflow-hidden bg-stone-900/5 dark:bg-black/40 flex items-center justify-center min-h-[220px] max-h-[520px] group cursor-pointer"
                                 onclick="openImageLightbox('{{ Storage::url($culture->image) }}', '{{ addslashes($culture->name) }} - Archival Record')">
                                <div class="absolute inset-0 bg-cover bg-center filter blur-xl opacity-25 dark:opacity-20 scale-110 pointer-events-none"
                                     style="background-image: url('{{ Storage::url($culture->image) }}');"></div>
                                <img src="{{ Storage::url($culture->image) }}" alt="{{ $culture->name }}" class="relative z-10 w-auto h-auto max-w-full max-h-[500px] object-contain mx-auto transition-transform duration-300 group-hover:scale-[1.01]">
                                <span class="absolute bottom-3 right-3 z-20 px-2.5 py-1 rounded-lg bg-black/65 backdrop-blur-md text-white text-[11px] font-medium opacity-0 group-hover:opacity-100 transition-opacity duration-200 flex items-center gap-1.5 pointer-events-none">
                                    <i data-lucide="maximize-2" class="w-3.5 h-3.5"></i>
                                    <span>View full</span>
                                </span>
                            </div>
                            <div class="p-3 text-xs flex justify-between items-center text-stone-500">
                                <span>Primary Archival Photo</span>
                                <span class="font-mono text-[10px]">{{ $culture->license_type ?? 'Archive Commons' }}</span>
                            </div>
                        </div>
                    @endif

                    {{-- Gallery Images --}}
                    @if(!empty($culture->media_files) && is_array($culture->media_files))
                        <div class="space-y-2">
                            <h3 class="text-xs font-bold uppercase tracking-wider text-stone-500">Supporting Documentation Images</h3>
                            <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                                @foreach($culture->media_files as $file)
                                    <div class="rounded-xl overflow-hidden border border-stone-200 dark:border-stone-800 bg-stone-100 dark:bg-stone-900 group cursor-pointer relative"
                                         onclick="openImageLightbox('{{ Storage::url($file) }}', 'Supporting cultural media - {{ addslashes($culture->name) }}')">
                                        <img src="{{ Storage::url($file) }}" alt="Supporting cultural media" class="aspect-square w-full object-cover transition-transform duration-300 group-hover:scale-105">
                                        <div class="absolute inset-0 bg-black/20 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
                                            <i data-lucide="maximize-2" class="w-5 h-5 text-white drop-shadow"></i>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        @endif

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
                            <label class="text-xs text-stone-500">Record Status:</label>
                            <select name="review_status" class="text-xs rounded-lg border border-stone-200 dark:border-stone-800 bg-white dark:bg-stone-900 px-3 py-1.5 font-bold">
                                <option value="published" {{ $culture->review_status === 'published' ? 'selected' : '' }}>Published Archive</option>
                                <option value="under_review" {{ $culture->review_status === 'under_review' ? 'selected' : '' }}>Under Community Review</option>
                                <option value="awaiting_contributions" {{ $culture->review_status === 'awaiting_contributions' ? 'selected' : '' }}>Awaiting Evidence</option>
                                <option value="disputed" {{ $culture->review_status === 'disputed' ? 'selected' : '' }}>Disputed Accounts</option>
                                <option value="restricted" {{ $culture->review_status === 'restricted' ? 'selected' : '' }}>Restricted Sacred Knowledge</option>
                            </select>
                            <button type="submit" class="px-3 py-1.5 rounded-lg bg-stone-900 dark:bg-stone-100 text-white dark:text-black text-xs font-bold transition">
                                Update Status
                            </button>
                        </form>
                    </div>
                @endif
            </div>
        </div>

        {{-- SECTION 9: CONTRIBUTE --}}
        <div x-show="activeSection === 'contribute'" class="space-y-6">
            <div class="rounded-2xl border border-stone-200/90 dark:border-stone-800 bg-white dark:bg-[#131218] p-5 sm:p-8 space-y-6">
                <div>
                    <h2 class="text-sm font-bold uppercase tracking-wider text-amber-600 dark:text-amber-400 flex items-center gap-2">
                        <i data-lucide="plus-circle" class="w-4 h-4"></i>
                        <span>Submit Community Contribution or Correction</span>
                    </h2>
                    <p class="text-xs text-stone-500 mt-1">
                        Help keep this cultural knowledge accurate. Submitted context is reviewed by community members before merging into the main record.
                    </p>
                </div>

                <form action="{{ route('cultural-hub.contribute', $culture->id) }}" method="POST" class="space-y-4 max-w-2xl">
                    @csrf
                    <div>
                        <label class="block text-xs font-bold text-stone-700 dark:text-stone-300 mb-1">Contribution Type *</label>
                        <select name="revision_type" required class="w-full text-xs rounded-lg border border-stone-200 dark:border-stone-800 bg-white dark:bg-stone-900 px-3 py-2 text-stone-800 dark:text-stone-200">
                            <option value="additional_context">Provide Additional Context / History</option>
                            <option value="correction">Suggest Factual Correction</option>
                            <option value="alternative_account">Provide Alternative Regional Account</option>
                            <option value="additional_source">Cite Supporting Primary Source</option>
                            <option value="new_media">Submit New Audio / Visual Documentation</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-stone-700 dark:text-stone-300 mb-1">Contribution Title (Optional)</label>
                        <input type="text" name="title" placeholder="e.g., Elder dialect variation in neighboring village" class="w-full text-xs rounded-lg border border-stone-200 dark:border-stone-800 bg-white dark:bg-stone-900 px-3 py-2 text-stone-800 dark:text-stone-200">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-stone-700 dark:text-stone-300 mb-1">Proposed Testimony / Narrative *</label>
                        <textarea name="proposed_content" required rows="4" placeholder="Detail the account, variation, or correction clearly..." class="w-full text-xs rounded-lg border border-stone-200 dark:border-stone-800 bg-white dark:bg-stone-900 px-3 py-2 text-stone-800 dark:text-stone-200"></textarea>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-stone-700 dark:text-stone-300 mb-1">Evidence / Source Basis (Optional)</label>
                        <input type="text" name="evidence_notes" placeholder="e.g., Oral interview with village elder, or book citation" class="w-full text-xs rounded-lg border border-stone-200 dark:border-stone-800 bg-white dark:bg-stone-900 px-3 py-2 text-stone-800 dark:text-stone-200">
                    </div>

                    @guest
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-bold text-stone-700 dark:text-stone-300 mb-1">Your Name (Optional)</label>
                                <input type="text" name="contributor_name" placeholder="Community Member" class="w-full text-xs rounded-lg border border-stone-200 dark:border-stone-800 bg-white dark:bg-stone-900 px-3 py-2 text-stone-800 dark:text-stone-200">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-stone-700 dark:text-stone-300 mb-1">Email for Follow-up (Optional)</label>
                                <input type="email" name="contributor_email" placeholder="contact@example.org" class="w-full text-xs rounded-lg border border-stone-200 dark:border-stone-800 bg-white dark:bg-stone-900 px-3 py-2 text-stone-800 dark:text-stone-200">
                            </div>
                        </div>
                    @endguest

                    <button type="submit" class="px-5 py-2.5 rounded-xl bg-amber-500 hover:bg-amber-600 text-white font-bold text-xs transition flex items-center gap-1.5 shadow-sm">
                        <i data-lucide="send" class="w-4 h-4"></i>
                        <span>Submit for Archival Review</span>
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