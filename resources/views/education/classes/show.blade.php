@extends('layouts.app')

@section('title', $class->title . ' - Cultural Learning Studio')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 sm:py-10"
     x-data="culturalClassPlayer({
         subtitles: {{ json_encode($class->timed_subtitles ?: []) }},
         isEnrolled: {{ $isEnrolled ? 'true' : 'false' }},
         isFree: {{ $class->isFree() ? 'true' : 'false' }},
         isInstructor: {{ $isInstructor ? 'true' : 'false' }},
         canWatchFull: {{ $canWatchFull ? 'true' : 'false' }},
         enrollUrl: '{{ route('education.classes.enroll', $class) }}',
         csrfToken: '{{ csrf_token() }}'
     })"
     x-init="initPlayer()">

    {{-- Breadcrumb & Title Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
        <div class="flex items-center space-x-2 text-xs sm:text-sm text-stone-500 dark:text-stone-400">
            <a href="{{ route('education.index') }}" class="hover:text-amber-500 transition">Educational Hub</a>
            <span>/</span>
            <a href="{{ route('education.classes.index') }}" class="hover:text-amber-500 transition">Cultural Classes</a>
            <span>/</span>
            <span class="text-stone-800 dark:text-stone-100 font-semibold truncate max-w-xs">{{ $class->title }}</span>
        </div>

        <div class="flex items-center gap-2">
            <span class="px-3 py-1 rounded-full text-xs font-bold bg-amber-500/10 text-amber-600 dark:text-amber-400 border border-amber-500/20">
                {{ $class->target_language }} Heritage
            </span>
            <span class="px-3 py-1 rounded-full text-xs font-bold {{ $class->isFree() ? 'bg-emerald-500/10 text-emerald-600 border border-emerald-500/20' : 'bg-amber-500 text-white shadow-sm' }}">
                {{ $class->formattedPrice() }}
            </span>
        </div>
    </div>

    @if (session('success'))
        <div class="mb-6 p-4 rounded-2xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-600 dark:text-emerald-400 text-sm font-semibold flex items-center gap-2">
            <i data-lucide="check-circle" class="w-5 h-5"></i>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    {{-- Main Learning Layout: Video & Real-Time Synchronized Subtitles --}}
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 mb-12">

        {{-- Left: Video Player & Controls (7 cols on desktop) --}}
        <div class="lg:col-span-7 space-y-6">

            {{-- Cinematic Video Player Container --}}
            <div class="relative rounded-3xl overflow-hidden bg-stone-950 border border-stone-800 shadow-2xl aspect-video group">
                @php
                    $videoSrc = $class->video_url 
                        ? (Str::startsWith($class->video_url, ['http://', 'https://']) ? $class->video_url : asset('storage/' . $class->video_url))
                        : null;
                    $posterSrc = $class->thumbnail_url 
                        ? (Str::startsWith($class->thumbnail_url, ['http://', 'https://']) ? $class->thumbnail_url : asset('storage/' . $class->thumbnail_url))
                        : ($class->virtual_background_url ?: null);
                @endphp

                @if ($videoSrc)
                    <video id="culturalVideo"
                           class="w-full h-full object-cover"
                           controls
                           preload="metadata"
                           poster="{{ $posterSrc }}"
                           @timeupdate="onTimeUpdate($event)"
                           @play="isPlaying = true"
                           @pause="isPlaying = false">
                        <source src="{{ $videoSrc }}">
                        <track kind="subtitles" 
                               src="{{ route('education.classes.subtitles', $class) }}" 
                               srclang="en" 
                               label="{{ $class->target_language }}" 
                               default>
                        Your browser does not support HTML5 video.
                    </video>
                @else
                    {{-- Ambient Virtual Studio Player Placeholder --}}
                    <div class="relative w-full h-full flex flex-col items-center justify-center p-8 text-center bg-cover bg-center"
                         style="{{ $posterSrc ? "background-image: linear-gradient(rgba(0,0,0,0.65), rgba(0,0,0,0.85)), url('{$posterSrc}');" : '' }}">
                        <div class="w-16 h-16 rounded-full bg-amber-500/90 text-white flex items-center justify-center shadow-xl shadow-amber-500/30 mb-4 animate-pulse">
                            <i data-lucide="headphones" class="w-8 h-8"></i>
                        </div>
                        <h3 class="text-xl font-black text-white mb-2">{{ $class->title }}</h3>
                        <p class="text-xs text-stone-300 max-w-md mb-6 leading-relaxed">
                            {{ $class->ai_summary ?: 'Interactive cultural audio lesson with live synchronized subtitles and vocabulary guides.' }}
                        </p>
                        
                        {{-- Simulated Audio/Video Controls for preview --}}
                        <div class="w-full max-w-md bg-stone-900/90 backdrop-blur-md rounded-2xl p-4 border border-stone-800 space-y-3">
                            <div class="flex items-center justify-between text-xs text-stone-300 font-mono">
                                <span x-text="formatTime(currentTime)">00:00</span>
                                <span class="text-amber-400 font-bold" x-text="currentSubtitleText || 'Listening to lesson...'"></span>
                                <span x-text="formatTime({{ $class->duration_seconds ?: 60 }})"></span>
                            </div>
                            <div class="w-full bg-stone-800 rounded-full h-2 cursor-pointer overflow-hidden" @click="seekByProgress($event)">
                                <div class="bg-gradient-to-r from-amber-500 to-orange-500 h-2 transition-all duration-150"
                                     :style="'width: ' + ((currentTime / ({{ $class->duration_seconds ?: 60 }})) * 100) + '%'"></div>
                            </div>
                            <div class="flex items-center justify-center gap-4">
                                <button type="button" @click="toggleSimulation()" 
                                        class="px-5 py-2 rounded-xl bg-amber-500 hover:bg-amber-600 text-white font-bold text-xs flex items-center gap-2 shadow-md transition">
                                    <i :data-lucide="isPlaying ? 'pause' : 'play'" class="w-4 h-4 fill-current"></i>
                                    <span x-text="isPlaying ? 'Pause Lesson' : 'Start Lesson Simulation'"></span>
                                </button>
                            </div>
                        </div>
                    </div>
                @endif

                {{-- Paywall Overlay for Paid Classes when not Enrolled --}}
                <div x-show="!canWatchFull && currentTime > 45" 
                     x-transition 
                     class="absolute inset-0 bg-stone-950/90 backdrop-blur-md flex flex-col items-center justify-center p-8 text-center z-20">
                    <div class="w-14 h-14 rounded-full bg-amber-500/20 border border-amber-500/30 text-amber-400 flex items-center justify-center mb-4">
                        <i data-lucide="lock" class="w-6 h-6"></i>
                    </div>
                    <h3 class="text-xl sm:text-2xl font-black text-white mb-2">Preview Finished</h3>
                    <p class="text-xs sm:text-sm text-stone-300 max-w-sm mb-6">
                        Unlock the complete {{ $class->target_language }} masterclass, all interactive subtitles, vocabulary cards, and instructor discussions.
                    </p>
                    
                    @auth
                        <button type="button" @click="enrollInClass()" :disabled="isEnrolling"
                                class="px-8 py-3.5 rounded-2xl bg-gradient-to-r from-amber-500 to-orange-600 hover:from-amber-600 hover:to-orange-700 text-white font-black text-sm uppercase tracking-wider shadow-xl shadow-amber-500/25 transition flex items-center gap-2">
                            <span x-show="!isEnrolling">Unlock Class for ${{ number_format($class->price, 2) }}</span>
                            <span x-show="isEnrolling">Unlocking Access...</span>
                        </button>
                    @else
                        <a href="{{ route('login') }}" 
                           class="px-8 py-3.5 rounded-2xl bg-amber-500 hover:bg-amber-600 text-white font-black text-sm uppercase tracking-wider shadow-xl shadow-amber-500/25 transition">
                            Sign In to Unlock (${{ number_format($class->price, 2) }})
                        </a>
                    @endauth
                </div>

                {{-- Live Floating Subtitle Bar on Video Player --}}
                <div class="absolute bottom-4 left-4 right-4 z-10 pointer-events-none" x-show="currentSubtitleText">
                    <div class="mx-auto max-w-xl text-center">
                        <span class="inline-block px-4 py-2 rounded-xl bg-black/80 backdrop-blur-md text-amber-300 text-xs sm:text-sm font-bold shadow-lg border border-amber-500/30 transition-all duration-200">
                            <span x-text="currentSubtitleText"></span>
                        </span>
                    </div>
                </div>
            </div>

            {{-- Class Title & Overview Header --}}
            <div class="bg-white dark:bg-stone-900 rounded-3xl p-6 sm:p-8 border border-stone-200 dark:border-stone-800 shadow-sm space-y-4">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <div class="flex items-center gap-3">
                        <img src="{{ $class->instructor->profile_photo_url }}" 
                             class="w-12 h-12 rounded-2xl object-cover border border-stone-200 dark:border-stone-700" 
                             alt="{{ $class->instructor->name }}">
                        <div>
                            <div class="text-xs font-bold text-stone-500">Taught by</div>
                            <a href="{{ route('profile.user', $class->instructor->id) }}" 
                               class="text-sm sm:text-base font-black text-stone-800 dark:text-stone-100 hover:text-amber-500 transition">
                                {{ $class->instructor->name }}
                            </a>
                        </div>
                    </div>

                    {{-- Enrollment CTA Button --}}
                    @if (!$canWatchFull)
                        @auth
                            <button type="button" @click="enrollInClass()" :disabled="isEnrolling"
                                    class="px-6 py-2.5 rounded-xl bg-amber-500 hover:bg-amber-600 text-white font-bold text-xs sm:text-sm shadow-md transition flex items-center gap-2">
                                <i data-lucide="unlock" class="w-4 h-4"></i>
                                <span>Unlock Masterclass (${{ number_format($class->price, 2) }})</span>
                            </button>
                        @else
                            <a href="{{ route('login') }}" 
                               class="px-6 py-2.5 rounded-xl bg-amber-500 hover:bg-amber-600 text-white font-bold text-xs sm:text-sm transition">
                                Sign In to Enroll
                            </a>
                        @endauth
                    @else
                        <div class="flex items-center gap-2 px-3 py-1.5 rounded-xl bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20 text-xs font-bold">
                            <i data-lucide="check" class="w-4 h-4"></i>
                            <span>{{ $isInstructor ? 'Instructor Access' : 'Enrolled & Active' }}</span>
                        </div>
                    @endif
                </div>

                <h1 class="text-xl sm:text-2xl font-black text-stone-800 dark:text-stone-100">
                    {{ $class->title }}
                </h1>

                <p class="text-xs sm:text-sm text-stone-600 dark:text-stone-300 leading-relaxed">
                    {{ $class->description ?: $class->ai_summary }}
                </p>

                {{-- Lesson Metadata Badges --}}
                <div class="pt-4 border-t border-stone-100 dark:border-stone-800 flex flex-wrap items-center gap-3 text-xs text-stone-500 dark:text-stone-400">
                    <span class="flex items-center gap-1.5 font-semibold">
                        <i data-lucide="clock" class="w-4 h-4 text-amber-500"></i>
                        <span>Duration: {{ $class->formattedDuration() }}</span>
                    </span>
                    <span>·</span>
                    <span class="flex items-center gap-1.5 font-semibold">
                        <i data-lucide="users" class="w-4 h-4 text-amber-500"></i>
                        <span>{{ $class->enrollments_count }} {{ Str::plural('learner', $class->enrollments_count) }}</span>
                    </span>
                    <span>·</span>
                    <span class="flex items-center gap-1.5 font-semibold">
                        <i data-lucide="eye" class="w-4 h-4 text-amber-500"></i>
                        <span>{{ $class->views_count }} views</span>
                    </span>
                </div>
            </div>

            {{-- AI Cultural Summary & Takeaways --}}
            @if (!empty($class->ai_enhancements['key_takeaways']))
                <div class="bg-gradient-to-br from-amber-500/5 via-stone-900/40 to-stone-900 rounded-3xl p-6 sm:p-8 border border-amber-500/20 shadow-sm space-y-4">
                    <h3 class="text-sm sm:text-base font-black text-stone-800 dark:text-stone-100 flex items-center gap-2">
                        <i data-lucide="sparkles" class="w-4 h-4 text-amber-400"></i>
                        <span>AI Cultural Takeaways & Etiquette Guide</span>
                    </h3>

                    <ul class="space-y-2.5">
                        @foreach ($class->ai_enhancements['key_takeaways'] as $point)
                            <li class="flex items-start gap-2.5 text-xs sm:text-sm text-stone-600 dark:text-stone-300">
                                <i data-lucide="check-circle-2" class="w-4 h-4 text-amber-500 shrink-0 mt-0.5"></i>
                                <span>{{ $point }}</span>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- Class Discussions / Questions Container --}}
            <div class="bg-white dark:bg-stone-900 rounded-3xl p-6 sm:p-8 border border-stone-200 dark:border-stone-800 shadow-sm space-y-6">
                <h3 class="text-base font-black text-stone-800 dark:text-stone-100 flex items-center justify-between">
                    <span class="flex items-center gap-2">
                        <i data-lucide="message-square" class="w-5 h-5 text-amber-500"></i>
                        <span>Learner Questions & Discussion</span>
                    </span>
                    <span class="text-xs text-stone-400">({{ $class->discussions->count() }})</span>
                </h3>

                {{-- Post Question Form --}}
                @auth
                    <form method="POST" action="{{ route('education.classes.discussion', $class) }}" class="space-y-3">
                        @csrf
                        <div class="relative">
                            <textarea name="message" rows="2" required
                                      placeholder="Ask the instructor a question about this cultural lesson or pronunciation..."
                                      class="w-full rounded-2xl border border-stone-200 dark:border-stone-800 bg-stone-50 dark:bg-stone-950 p-3.5 text-xs sm:text-sm text-stone-800 dark:text-stone-200 focus:ring-2 focus:ring-amber-500 outline-none leading-relaxed"></textarea>
                            <input type="hidden" name="timestamp_seconds" :value="Math.floor(currentTime)">
                        </div>

                        <div class="flex items-center justify-between">
                            <div class="text-[11px] text-stone-400 flex items-center gap-1">
                                <i data-lucide="clock" class="w-3.5 h-3.5 text-amber-500"></i>
                                <span>Tagging at timestamp: <strong class="font-mono text-amber-400" x-text="formatTime(currentTime)"></strong></span>
                            </div>

                            <button type="submit" 
                                    class="px-5 py-2 rounded-xl bg-stone-800 dark:bg-stone-700 hover:bg-amber-500 text-stone-200 hover:text-white font-bold text-xs transition">
                                Post Question
                            </button>
                        </div>
                    </form>
                @else
                    <div class="p-4 rounded-2xl bg-stone-50 dark:bg-stone-950 border border-stone-200 dark:border-stone-800 text-center text-xs text-stone-500">
                        <a href="{{ route('login') }}" class="font-bold text-amber-500 hover:underline">Sign in</a> to ask questions or discuss with the instructor.
                    </div>
                @endauth

                {{-- Discussion List --}}
                <div class="space-y-4 pt-2">
                    @forelse ($class->discussions as $disc)
                        <div class="p-4 rounded-2xl bg-stone-50 dark:bg-stone-950/60 border border-stone-100 dark:border-stone-800 space-y-2">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <img src="{{ $disc->user->profile_photo_url }}" class="w-6 h-6 rounded-full object-cover" alt="{{ $disc->user->name }}">
                                    <span class="text-xs font-bold text-stone-700 dark:text-stone-300">{{ $disc->user->name }}</span>
                                </div>
                                @if ($disc->timestamp_seconds !== null)
                                    <button type="button" @click="seekTo({{ $disc->timestamp_seconds }})" 
                                            class="px-2 py-0.5 rounded-md bg-amber-500/10 text-amber-500 text-[10px] font-mono font-bold hover:bg-amber-500 hover:text-white transition">
                                        @ {{ sprintf('%02d:%02d', floor($disc->timestamp_seconds / 60), $disc->timestamp_seconds % 60) }}
                                    </button>
                                @endif
                            </div>
                            <p class="text-xs sm:text-sm text-stone-600 dark:text-stone-300 leading-relaxed">
                                {{ $disc->message }}
                            </p>
                        </div>
                    @empty
                        <div class="text-center py-6 text-xs text-stone-400 italic">
                            No questions yet. Be the first to start the discussion!
                        </div>
                    @endforelse
                </div>
            </div>

        </div>

        {{-- Right: Real-time Synchronized Transcript & Bilingual Vocabulary Cards (5 cols) --}}
        <div class="lg:col-span-5 space-y-6">

            {{-- Real-Time Interactive Synchronized Transcript --}}
            <div class="bg-white dark:bg-stone-900 rounded-3xl p-6 sm:p-7 border border-stone-200 dark:border-stone-800 shadow-sm space-y-4">
                <div class="flex items-center justify-between">
                    <h3 class="text-sm sm:text-base font-black text-stone-800 dark:text-stone-100 flex items-center gap-2">
                        <i data-lucide="subtitles" class="w-4 h-4 text-amber-500"></i>
                        <span>Live Synchronized Transcript</span>
                    </h3>
                    <span class="text-[10px] font-black uppercase tracking-wider px-2 py-0.5 rounded-full bg-amber-500/10 text-amber-500 border border-amber-500/20">
                        Interactive
                    </span>
                </div>

                <p class="text-xs text-stone-500 dark:text-stone-400">
                    Follow speech word-for-word. Lines highlight in real-time. <strong>Click any line to jump the video directly to that phrase.</strong>
                </p>

                {{-- Search Transcript Box --}}
                <div class="relative">
                    <input type="text" x-model="transcriptSearch" 
                           placeholder="Search spoken words or phrases in transcript..."
                           class="w-full rounded-xl border border-stone-200 dark:border-stone-800 bg-stone-50 dark:bg-stone-950 pl-8 pr-3 py-2 text-xs text-stone-800 dark:text-stone-200 focus:ring-2 focus:ring-amber-500 outline-none">
                    <i data-lucide="search" class="w-3.5 h-3.5 text-stone-400 absolute left-2.5 top-2.5"></i>
                </div>

                {{-- Interactive Synchronized Transcript Scroll Box --}}
                <div class="max-h-[380px] overflow-y-auto space-y-2 pr-1 scrollbar-thin scrollbar-thumb-stone-700" id="transcriptContainer">
                    <template x-for="(sub, index) in filteredSubtitles" :key="index">
                        <div @click="seekTo(sub.start)"
                             class="p-3 rounded-2xl border transition-all duration-200 cursor-pointer text-left select-none group"
                             :class="isSubActive(sub) 
                                ? 'bg-amber-500/15 border-amber-500 text-stone-900 dark:text-white shadow-sm ring-1 ring-amber-500/40' 
                                : 'bg-stone-50/60 dark:bg-stone-950/60 border-stone-100 dark:border-stone-800/80 text-stone-600 dark:text-stone-300 hover:border-amber-400/60'">
                            
                            <div class="flex items-center justify-between text-[10px] font-mono mb-1">
                                <span class="font-bold flex items-center gap-1" :class="isSubActive(sub) ? 'text-amber-500' : 'text-stone-400'">
                                    <i data-lucide="play" class="w-2.5 h-2.5 fill-current"></i>
                                    <span x-text="formatTime(sub.start) + ' - ' + formatTime(sub.end)"></span>
                                </span>
                                
                                <span x-show="sub.target_phrase" 
                                      class="px-2 py-0.5 rounded-full text-[9px] font-bold bg-amber-500/20 text-amber-400"
                                      x-text="sub.target_phrase"></span>
                            </div>

                            <div class="text-xs sm:text-[13px] leading-relaxed" 
                                 :class="isSubActive(sub) ? 'font-semibold text-stone-900 dark:text-amber-200' : ''"
                                 x-text="sub.text">
                            </div>

                            <div x-show="sub.cultural_note" 
                                 class="mt-1 text-[10px] text-stone-400 italic flex items-center gap-1">
                                <i data-lucide="info" class="w-3 h-3 text-amber-500"></i>
                                <span x-text="sub.cultural_note"></span>
                            </div>
                        </div>
                    </template>

                    <div x-show="filteredSubtitles.length === 0" class="text-center py-8 text-xs text-stone-400">
                        No subtitle matches found for your search.
                    </div>
                </div>
            </div>

            {{-- Bilingual Cultural Vocabulary & Flashcard Cards --}}
            <div class="bg-white dark:bg-stone-900 rounded-3xl p-6 sm:p-7 border border-stone-200 dark:border-stone-800 shadow-sm space-y-4">
                <div class="flex items-center justify-between">
                    <h3 class="text-sm sm:text-base font-black text-stone-800 dark:text-stone-100 flex items-center gap-2">
                        <i data-lucide="languages" class="w-4 h-4 text-amber-500"></i>
                        <span>Bilingual Vocabulary & Phrase Cards</span>
                    </h3>
                    <span class="text-xs font-bold text-amber-500">
                        {{ count($class->bilingual_vocabulary ?: []) }} {{ Str::plural('Phrase', count($class->bilingual_vocabulary ?: [])) }}
                    </span>
                </div>

                <p class="text-xs text-stone-500 dark:text-stone-400">
                    Key phrases taught in this class with phonetics and cultural context. Tap audio to hear pronunciation.
                </p>

                <div class="space-y-3">
                    @forelse ($class->bilingual_vocabulary ?: [] as $vocab)
                        <div class="p-4 rounded-2xl bg-stone-50 dark:bg-stone-950 border border-stone-100 dark:border-stone-800 hover:border-amber-500/40 transition-all space-y-2">
                            <div class="flex items-start justify-between gap-2">
                                <div>
                                    <span class="text-[10px] font-black uppercase tracking-wider text-amber-500 block mb-0.5">
                                        {{ $vocab['language'] ?? $class->target_language }}
                                    </span>
                                    <div class="text-sm font-black text-stone-800 dark:text-stone-100">
                                        {{ $vocab['target'] ?? '' }}
                                    </div>
                                    <div class="text-xs font-semibold text-stone-500 dark:text-stone-400 mt-0.5">
                                        “{{ $vocab['source'] ?? '' }}”
                                    </div>
                                </div>

                                {{-- Speech Synthesis Audio Button --}}
                                <button type="button" 
                                        @click="speakPhrase('{{ addslashes($vocab['target'] ?? '') }}')"
                                        class="p-2 rounded-xl bg-amber-500/10 hover:bg-amber-500 text-amber-500 hover:text-white transition shadow-sm"
                                        title="Hear Pronunciation">
                                    <i data-lucide="volume-2" class="w-4 h-4"></i>
                                </button>
                            </div>

                            @if (!empty($vocab['pronunciation']))
                                <div class="text-[11px] font-mono text-stone-400 flex items-center gap-1.5">
                                    <i data-lucide="mic" class="w-3 h-3 text-amber-500"></i>
                                    <span>Pronunciation: <strong class="text-stone-300">{{ $vocab['pronunciation'] }}</strong></span>
                                </div>
                            @endif

                            @if (!empty($vocab['context']))
                                <div class="text-[11px] text-stone-500 dark:text-stone-400 bg-white dark:bg-stone-900 p-2.5 rounded-xl border border-stone-100 dark:border-stone-800/80 leading-relaxed">
                                    <span class="font-bold text-amber-600 dark:text-amber-400">Etiquette:</span> {{ $vocab['context'] }}
                                </div>
                            @endif
                        </div>
                    @empty
                        <div class="text-center py-6 text-xs text-stone-400 italic">
                            No vocabulary cards extracted for this lesson.
                        </div>
                    @endforelse
                </div>
            </div>

            {{-- Related Cultural Classes --}}
            @if ($relatedClasses->count() > 0)
                <div class="bg-white dark:bg-stone-900 rounded-3xl p-6 border border-stone-200 dark:border-stone-800 shadow-sm space-y-3">
                    <h4 class="text-xs font-black uppercase tracking-wider text-stone-400 mb-2">
                        More in {{ $class->target_language }} Heritage
                    </h4>
                    <div class="space-y-3">
                        @foreach ($relatedClasses as $rel)
                            <a href="{{ route('education.classes.show', $rel->slug) }}" 
                               class="flex items-center gap-3 p-2 rounded-2xl hover:bg-stone-50 dark:hover:bg-stone-800/50 transition group">
                                <div class="w-14 h-10 rounded-xl overflow-hidden bg-stone-950 shrink-0">
                                    @if ($rel->thumbnail_url)
                                        <img src="{{ Str::startsWith($rel->thumbnail_url, ['http://', 'https://']) ? $rel->thumbnail_url : asset('storage/' . $rel->thumbnail_url) }}" 
                                             class="w-full h-full object-cover" alt="{{ $rel->title }}">
                                    @else
                                        <div class="w-full h-full flex items-center justify-center text-amber-500">
                                            <i data-lucide="video" class="w-4 h-4"></i>
                                        </div>
                                    @endif
                                </div>
                                <div class="flex-1 overflow-hidden">
                                    <h5 class="text-xs font-bold text-stone-800 dark:text-stone-200 group-hover:text-amber-500 truncate transition">
                                        {{ $rel->title }}
                                    </h5>
                                    <span class="text-[10px] text-stone-400">{{ $rel->formattedPrice() }} · {{ $rel->formattedDuration() }}</span>
                                </div>
                                <i data-lucide="chevron-right" class="w-4 h-4 text-stone-400 group-hover:text-amber-500 transition"></i>
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif

        </div>

    </div>

</div>
@endsection

@push('scripts')
<script>
    function culturalClassPlayer(config) {
        return {
            subtitles: config.subtitles || [],
            currentTime: 0.0,
            isPlaying: false,
            transcriptSearch: '',
            isEnrolling: false,
            canWatchFull: config.canWatchFull,
            simTimer: null,

            get filteredSubtitles() {
                if (!this.transcriptSearch) return this.subtitles;
                const query = this.transcriptSearch.toLowerCase();
                return this.subtitles.filter(sub => 
                    (sub.text && sub.text.toLowerCase().includes(query)) ||
                    (sub.target_phrase && sub.target_phrase.toLowerCase().includes(query)) ||
                    (sub.source_phrase && sub.source_phrase.toLowerCase().includes(query))
                );
            },

            get currentSubtitleText() {
                const active = this.subtitles.find(sub => this.isSubActive(sub));
                return active ? active.text : '';
            },

            initPlayer() {
                // If HTML5 video exists, hook into its events
                this.$nextTick(() => {
                    const video = document.getElementById('culturalVideo');
                    if (video) {
                        video.addEventListener('timeupdate', () => {
                            this.currentTime = video.currentTime;
                            // Check paywall threshold for non-enrolled paid classes
                            if (!this.canWatchFull && this.currentTime > 45) {
                                video.pause();
                            }
                        });
                    }
                    if (typeof lucide !== 'undefined') lucide.createIcons();
                });
            },

            onTimeUpdate(e) {
                this.currentTime = e.target.currentTime;
            },

            isSubActive(sub) {
                return this.currentTime >= sub.start && this.currentTime <= sub.end;
            },

            seekTo(seconds) {
                this.currentTime = seconds;
                const video = document.getElementById('culturalVideo');
                if (video) {
                    video.currentTime = seconds;
                    video.play();
                    this.isPlaying = true;
                }
            },

            formatTime(seconds) {
                const s = Math.floor(seconds || 0);
                const m = Math.floor(s / 60);
                const remaining = s % 60;
                return `${String(m).padStart(2, '0')}:${String(remaining).padStart(2, '0')}`;
            },

            toggleSimulation() {
                this.isPlaying = !this.isPlaying;
                if (this.isPlaying) {
                    this.simTimer = setInterval(() => {
                        this.currentTime += 0.5;
                        if (!this.canWatchFull && this.currentTime > 45) {
                            this.isPlaying = false;
                            clearInterval(this.simTimer);
                        }
                    }, 500);
                } else {
                    clearInterval(this.simTimer);
                }
            },

            seekByProgress(event) {
                const rect = event.currentTarget.getBoundingClientRect();
                const pos = (event.clientX - rect.left) / rect.width;
                const targetSec = pos * {{ $class->duration_seconds ?: 60 }};
                this.seekTo(targetSec);
            },

            speakPhrase(phrase) {
                if ('speechSynthesis' in window && phrase) {
                    const utterance = new SpeechSynthesisUtterance(phrase);
                    utterance.rate = 0.9;
                    utterance.pitch = 1.0;
                    window.speechSynthesis.speak(utterance);
                }
            },

            async enrollInClass() {
                this.isEnrolling = true;
                try {
                    const res = await fetch(config.enrollUrl, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': config.csrfToken,
                            'Accept': 'application/json'
                        }
                    });
                    const data = await res.json();
                    if (data.success) {
                        this.canWatchFull = true;
                        window.location.reload();
                    }
                } catch (e) {
                    console.error('Enrollment error:', e);
                } finally {
                    this.isEnrolling = false;
                }
            }
        };
    }

    document.addEventListener("DOMContentLoaded", () => {
        if (typeof lucide !== 'undefined') {
            lucide.createIcons();
        }
    });
</script>
<style>
    .scrollbar-thin::-webkit-scrollbar { width: 5px; }
    .scrollbar-thin::-webkit-scrollbar-track { background: transparent; }
    .scrollbar-thin::-webkit-scrollbar-thumb { background: rgba(150, 150, 150, 0.3); border-radius: 9999px; }
</style>
@endpush
