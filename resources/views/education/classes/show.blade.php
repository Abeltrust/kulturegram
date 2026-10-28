@extends('layouts.app')

@section('title', $class->title . ' - Cultural Learning Studio')

@section('content')
@php
    $resolvedVideoSrc = $class->video_url 
        ? (Str::startsWith($class->video_url, ['http://', 'https://']) ? $class->video_url : '/storage/' . ltrim($class->video_url, '/'))
        : null;
    $youtubeEmbedUrl = $class->youtubeEmbedUrl();
    $isYoutubeVideo = !empty($youtubeEmbedUrl);
@endphp
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 sm:py-10"
     x-data="culturalClassPlayer({
         subtitles: @json($class->timed_subtitles ?: []),
         vocabulary: @json($class->bilingual_vocabulary ?: []),
         keyTakeaways: @json($class->ai_enhancements['key_takeaways'] ?? []),
         aiSummary: @json($class->description ?: $class->ai_summary ?: ''),
         durationSeconds: {{ (int) ($class->duration_seconds ?: 0) }},
         targetLanguage: @json($class->target_language ?: 'Hausa'),
         transcript: @json($class->transcript ?: ''),
         videoSrc: @json($resolvedVideoSrc),
         isYoutubeVideo: {{ $isYoutubeVideo ? 'true' : 'false' }},
         isEnrolled: {{ $isEnrolled ? 'true' : 'false' }},
         isFree: {{ $class->isFree() ? 'true' : 'false' }},
         isInstructor: {{ $isInstructor ? 'true' : 'false' }},
         canWatchFull: {{ $canWatchFull ? 'true' : 'false' }},
         enrollUrl: '{{ route('education.classes.enroll', $class, false) }}',
         audioWavUrl: '{{ route('education.classes.audio-wav', $class, false) }}',
         syncSubtitlesUrl: '{{ route('education.classes.sync-subtitles', $class, false) }}',
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
                    $videoSrc = $resolvedVideoSrc;
                    $posterSrc = $class->resolvedThumbnailUrl();
                @endphp

                @if ($youtubeEmbedUrl)
                    <iframe id="culturalYoutubeIframe"
                            src="{{ $youtubeEmbedUrl }}"
                            class="w-full h-full"
                            frameborder="0"
                            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                            allowfullscreen></iframe>
                @elseif ($videoSrc)
                    <video id="culturalVideo"
                           class="w-full h-full object-contain"
                           controls
                           preload="metadata"
                           poster="{{ $posterSrc }}"
                           @loadedmetadata="onVideoMetadata($event)"
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
                    {{-- Audio / No-Video Placeholder --}}
                    <div class="relative w-full h-full flex flex-col items-center justify-center p-8 text-center bg-cover bg-center"
                         style="{{ $posterSrc ? "background-image: linear-gradient(rgba(0,0,0,0.65), rgba(0,0,0,0.85)), url('{$posterSrc}');" : '' }}">
                        <div class="w-16 h-16 rounded-full bg-amber-500/90 text-white flex items-center justify-center shadow-xl shadow-amber-500/30 mb-4">
                            <i data-lucide="video-off" class="w-8 h-8"></i>
                        </div>
                        <h3 class="text-xl font-black text-white mb-2">{{ $class->title }}</h3>
                        <p class="text-xs text-stone-300 max-w-md leading-relaxed">
                            No video file is attached to this class yet.
                        </p>
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
                <div class="absolute bottom-14 left-4 right-4 z-10 pointer-events-none" x-show="currentSubtitleText">
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

                <p x-show="aiSummary" class="text-xs sm:text-sm text-stone-600 dark:text-stone-300 leading-relaxed" x-text="aiSummary"></p>

                {{-- Lesson Metadata Badges --}}
                <div class="pt-4 border-t border-stone-100 dark:border-stone-800 flex flex-wrap items-center gap-3 text-xs text-stone-500 dark:text-stone-400">
                    <span class="flex items-center gap-1.5 font-semibold">
                        <i data-lucide="clock" class="w-4 h-4 text-amber-500"></i>
                        <span>Duration: <span x-text="durationSeconds > 0 ? formatTime(durationSeconds) : '{{ $class->formattedDuration() }}'"></span></span>
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

            {{-- AI Cultural Summary & Takeaways (Reactive) --}}
            <div x-show="keyTakeaways && keyTakeaways.length > 0" x-transition
                 class="bg-gradient-to-br from-amber-500/5 via-stone-900/40 to-stone-900 rounded-3xl p-6 sm:p-8 border border-amber-500/20 shadow-sm space-y-4">
                <h3 class="text-sm sm:text-base font-black text-stone-800 dark:text-stone-100 flex items-center gap-2">
                    <i data-lucide="sparkles" class="w-4 h-4 text-amber-400"></i>
                    <span>Lesson Key Takeaways</span>
                </h3>

                <ul class="space-y-2.5">
                    <template x-for="(point, pIdx) in keyTakeaways" :key="pIdx">
                        <li class="flex items-start gap-2.5 text-xs sm:text-sm text-stone-600 dark:text-stone-300">
                            <i data-lucide="check-circle-2" class="w-4 h-4 text-amber-500 shrink-0 mt-0.5"></i>
                            <span x-text="point"></span>
                        </li>
                    </template>
                </ul>
            </div>

            {{-- Class Discussions / Questions Container --}}
            <div class="bg-white dark:bg-stone-900 rounded-3xl p-6 sm:p-8 border border-stone-200 dark:border-stone-800 shadow-sm space-y-6">
                <h3 class="text-base font-black text-stone-800 dark:text-stone-100 flex items-center justify-between">
                    <span class="flex items-center gap-2">
                        <i data-lucide="message-square" class="w-5 h-5 text-amber-500"></i>
                        <span>Learner Questions & Discussion</span>
                    </span>
                    <span class="text-xs text-stone-400">({{ $class->discussions->count() }})</span>
                </h3>

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
                <div class="flex items-center justify-between gap-2">
                    <h3 class="text-sm sm:text-base font-black text-stone-800 dark:text-stone-100 flex items-center gap-2">
                        <i data-lucide="subtitles" class="w-4 h-4 text-amber-500"></i>
                        <span>Live Synchronized Transcript</span>
                    </h3>
                    <span class="text-[10px] font-black uppercase tracking-wider px-2 py-0.5 rounded-full bg-amber-500/10 text-amber-500 border border-amber-500/20">
                        Interactive
                    </span>
                </div>

                {{-- Video Audio Transcription & Instructor Subtitle Controls --}}
                @if ($videoSrc || $isInstructor)
                    <div class="p-3.5 rounded-2xl bg-amber-500/5 border border-amber-500/20 space-y-3">
                        <div class="flex flex-wrap items-center justify-between gap-2">
                            <span class="text-[11px] font-bold text-amber-600 dark:text-amber-400 flex items-center gap-1.5">
                                <i data-lucide="audio-lines" class="w-3.5 h-3.5"></i>
                                <span>Video Audio Transcription</span>
                            </span>
                            <div class="flex flex-wrap items-center gap-1.5">
                                @if ($videoSrc)
                                    <button type="button"
                                            @click="analyzePostedVideoAudio()"
                                            :disabled="isAnalyzingAudio || isSavingSubtitles"
                                            class="px-2.5 py-1.5 rounded-xl bg-amber-500 hover:bg-amber-600 disabled:opacity-50 text-white text-[11px] font-bold transition shadow-sm flex items-center gap-1.5 cursor-pointer">
                                        <span x-show="!isAnalyzingAudio" class="flex items-center gap-1.5">
                                            <i data-lucide="audio-waveform" class="w-3.5 h-3.5"></i>
                                            <span x-text="subtitles.length > 0 ? 'Re-Transcribe Video' : 'Transcribe Video Audio'"></span>
                                        </span>
                                        <span x-show="isAnalyzingAudio" class="flex items-center gap-1.5">
                                            <span class="w-3 h-3 border-2 border-white border-t-transparent rounded-full animate-spin"></span>
                                            <span>Transcribing...</span>
                                        </span>
                                    </button>
                                @endif
                                @if ($isInstructor)
                                    <button type="button"
                                            @click="isEditingSubtitles = !isEditingSubtitles"
                                            class="px-2.5 py-1.5 rounded-xl border border-stone-200 dark:border-stone-700 hover:border-amber-500 text-stone-700 dark:text-stone-200 text-[11px] font-bold transition flex items-center gap-1.5 cursor-pointer">
                                        <i data-lucide="pen-line" class="w-3.5 h-3.5"></i>
                                        <span x-text="isEditingSubtitles ? 'Close Editor' : 'Edit Subtitles'"></span>
                                    </button>
                                @endif
                            </div>
                        </div>

                        <div x-show="analysisStatus" x-transition
                             class="text-[11px] font-semibold text-amber-500 flex items-center gap-1.5">
                            <span x-show="isAnalyzingAudio || isSavingSubtitles" class="w-3 h-3 border-2 border-amber-500 border-t-transparent rounded-full animate-spin shrink-0"></span>
                            <span x-text="analysisStatus"></span>
                        </div>

                        @if ($isInstructor)
                            {{-- Inline Instructor Subtitle & Transcript Editor --}}
                            <div x-show="isEditingSubtitles" x-transition class="space-y-3 pt-2 border-t border-amber-500/20">
                                <div>
                                    <label class="block text-[10px] font-black uppercase tracking-wider text-stone-400 mb-1">
                                        Spoken Lesson Script
                                    </label>
                                    <textarea x-model="editableTranscript" rows="3"
                                              placeholder="Type or paste what is spoken in the video..."
                                              class="w-full rounded-xl border border-stone-200 dark:border-stone-800 bg-white dark:bg-stone-950 p-2.5 text-xs text-stone-800 dark:text-stone-200 focus:ring-2 focus:ring-amber-500 outline-none"></textarea>
                                </div>

                                <div class="flex items-center justify-between">
                                    <span class="text-[10px] font-black uppercase tracking-wider text-stone-400">
                                        Timed Subtitle Lines (<span x-text="subtitles.length"></span>)
                                    </span>
                                    <button type="button" @click="addSubtitleLine()"
                                            class="px-2 py-1 rounded-lg bg-amber-500/10 hover:bg-amber-500/20 text-amber-500 text-[10px] font-bold transition flex items-center gap-1 cursor-pointer">
                                        <i data-lucide="plus" class="w-3 h-3"></i>
                                        <span>Add Line at <span x-text="formatTime(currentTime)"></span></span>
                                    </button>
                                </div>

                                <div class="max-h-56 overflow-y-auto space-y-2 pr-1 scrollbar-thin">
                                    <template x-for="(sub, idx) in subtitles" :key="idx">
                                        <div class="p-2.5 rounded-xl bg-white dark:bg-stone-950 border border-stone-200 dark:border-stone-800 space-y-1.5">
                                            <div class="flex items-center justify-between gap-2 text-[10px]">
                                                <div class="flex items-center gap-1 font-mono">
                                                    <input type="number" step="0.5" min="0" x-model.number="sub.start"
                                                           class="w-14 px-1.5 py-0.5 rounded border border-stone-200 dark:border-stone-700 bg-stone-50 dark:bg-stone-900 text-amber-500 font-bold text-[10px]">
                                                    <span class="text-stone-400">to</span>
                                                    <input type="number" step="0.5" min="0" x-model.number="sub.end"
                                                           class="w-14 px-1.5 py-0.5 rounded border border-stone-200 dark:border-stone-700 bg-stone-50 dark:bg-stone-900 text-amber-500 font-bold text-[10px]">
                                                    <button type="button" @click="seekTo(sub.start)"
                                                            title="Play from this timestamp"
                                                            class="px-1.5 py-0.5 rounded bg-amber-500/10 text-amber-500 font-sans font-bold flex items-center">
                                                        <svg class="w-3 h-3 fill-current" viewBox="0 0 24 24"><polygon points="5 3 19 12 5 21 5 3"></polygon></svg>
                                                    </button>
                                                </div>
                                                <button type="button" @click="removeSubtitleLine(idx)"
                                                        class="text-rose-500 hover:underline font-bold">
                                                    Remove
                                                </button>
                                            </div>
                                            <input type="text" x-model="sub.text"
                                                   placeholder="Spoken subtitle line..."
                                                   class="w-full px-2 py-1 rounded-lg border border-stone-200 dark:border-stone-800 bg-stone-50 dark:bg-stone-900 text-xs text-stone-800 dark:text-stone-100">
                                            <input type="text" x-model="sub.target_phrase"
                                                   placeholder="Target heritage phrase (optional)..."
                                                   class="w-full px-2 py-1 rounded-lg border border-stone-200 dark:border-stone-800 bg-stone-50 dark:bg-stone-900 text-[11px] text-amber-600 dark:text-amber-400 font-semibold">
                                        </div>
                                    </template>
                                </div>

                                <div class="flex flex-wrap items-center justify-end gap-2 pt-1">
                                    <button type="button"
                                            @click="saveSyncedSubtitles(true)"
                                            :disabled="isSavingSubtitles"
                                            class="px-3 py-1.5 rounded-xl bg-stone-800 hover:bg-stone-700 text-amber-400 text-[11px] font-bold transition flex items-center gap-1.5 cursor-pointer">
                                        <i data-lucide="wand-2" class="w-3.5 h-3.5"></i>
                                        <span>Structure with Meta AI</span>
                                    </button>
                                    <button type="button"
                                            @click="saveSyncedSubtitles(false)"
                                            :disabled="isSavingSubtitles"
                                            class="px-3.5 py-1.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-[11px] font-bold transition shadow-sm flex items-center gap-1.5 cursor-pointer">
                                        <i data-lucide="save" class="w-3.5 h-3.5"></i>
                                        <span x-show="!isSavingSubtitles">Save Subtitles</span>
                                        <span x-show="isSavingSubtitles">Saving...</span>
                                    </button>
                                </div>
                            </div>
                        @endif
                    </div>
                @endif

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
                                    <svg class="w-2.5 h-2.5 fill-current" viewBox="0 0 24 24"><polygon points="5 3 19 12 5 21 5 3"></polygon></svg>
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
                                <span x-text="sub.cultural_note"></span>
                            </div>
                        </div>
                    </template>

                    <div x-show="filteredSubtitles.length === 0 && !isAnalyzingAudio" class="text-center py-8 text-xs text-stone-400">
                        No transcript lines yet. Click "Transcribe Video Audio" above to transcribe this video.
                    </div>
                </div>
            </div>

            {{-- Bilingual Cultural Vocabulary & Flashcard Cards (Reactive) --}}
            <div class="bg-white dark:bg-stone-900 rounded-3xl p-6 sm:p-7 border border-stone-200 dark:border-stone-800 shadow-sm space-y-4">
                <div class="flex items-center justify-between">
                    <h3 class="text-sm sm:text-base font-black text-stone-800 dark:text-stone-100 flex items-center gap-2">
                        <i data-lucide="languages" class="w-4 h-4 text-amber-500"></i>
                        <span>Bilingual Vocabulary & Phrase Cards</span>
                    </h3>
                    <span class="text-xs font-bold text-amber-500" x-text="vocabulary.length + (vocabulary.length === 1 ? ' Phrase' : ' Phrases')"></span>
                </div>

                <p class="text-xs text-stone-500 dark:text-stone-400">
                    Key phrases spoken in this video with phonetics and context. Tap audio to hear pronunciation.
                </p>

                <div class="space-y-3">
                    <template x-for="(vocab, vIdx) in vocabulary" :key="vIdx">
                        <div class="p-4 rounded-2xl bg-stone-50 dark:bg-stone-950 border border-stone-100 dark:border-stone-800 hover:border-amber-500/40 transition-all space-y-2">
                            <div class="flex items-start justify-between gap-2">
                                <div>
                                    <span class="text-[10px] font-black uppercase tracking-wider text-amber-500 block mb-0.5"
                                          x-text="vocab.language || targetLanguage"></span>
                                    <div class="text-sm font-black text-stone-800 dark:text-stone-100"
                                         x-text="vocab.target || ''"></div>
                                    <div class="text-xs font-semibold text-stone-500 dark:text-stone-400 mt-0.5"
                                         x-text="vocab.source ? '“' + vocab.source + '”' : ''"></div>
                                </div>

                                {{-- Speech Synthesis Audio Button --}}
                                <button type="button" 
                                        @click="speakPhrase(vocab.target || '')"
                                        class="p-2 rounded-xl bg-amber-500/10 hover:bg-amber-500 text-amber-500 hover:text-white transition shadow-sm cursor-pointer"
                                        title="Hear Pronunciation">
                                    <svg class="w-4 h-4 fill-none stroke-current" stroke-width="2" viewBox="0 0 24 24">
                                        <polygon points="11 5 6 9 2 9 2 15 6 15 11 19 11 5"></polygon>
                                        <path d="M15.54 8.46a5 5 0 0 1 0 7.07"></path>
                                        <path d="M19.07 4.93a10 10 0 0 1 0 14.14"></path>
                                    </svg>
                                </button>
                            </div>

                            <div x-show="vocab.pronunciation" class="text-[11px] font-mono text-stone-400 flex items-center gap-1.5">
                                <span>Pronunciation: <strong class="text-stone-300" x-text="vocab.pronunciation"></strong></span>
                            </div>

                            <div x-show="vocab.context" class="text-[11px] text-stone-500 dark:text-stone-400 bg-white dark:bg-stone-900 p-2.5 rounded-xl border border-stone-100 dark:border-stone-800/80 leading-relaxed">
                                <span class="font-bold text-amber-600 dark:text-amber-400">Context:</span>
                                <span x-text="vocab.context"></span>
                            </div>
                        </div>
                    </template>

                    <div x-show="vocabulary.length === 0" class="text-center py-6 text-xs text-stone-400 italic">
                        Vocabulary cards will appear here once the video audio is transcribed.
                    </div>
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
                                    @if ($rel->resolvedThumbnailUrl())
                                        <img src="{{ $rel->resolvedThumbnailUrl() }}" 
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
            vocabulary: config.vocabulary || [],
            keyTakeaways: config.keyTakeaways || [],
            aiSummary: config.aiSummary || '',
            durationSeconds: config.durationSeconds || 0,
            targetLanguage: config.targetLanguage || 'Hausa',
            editableTranscript: config.transcript || '',
            videoSrc: config.videoSrc || null,
            isYoutubeVideo: Boolean(config.isYoutubeVideo),
            audioWavUrl: config.audioWavUrl || '',
            syncSubtitlesUrl: config.syncSubtitlesUrl || '',
            isEditingSubtitles: false,
            isAnalyzingAudio: false,
            isSavingSubtitles: false,
            analysisStatus: '',
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
                this.$nextTick(() => {
                    const video = document.getElementById('culturalVideo');
                    if (video) {
                        video.addEventListener('timeupdate', () => {
                            this.currentTime = video.currentTime;
                            if (!this.canWatchFull && this.currentTime > 45) {
                                video.pause();
                            }
                        });
                    }

                    const ytIframe = document.getElementById('culturalYoutubeIframe');
                    if (ytIframe) {
                        const subscribeToYt = () => {
                            if (ytIframe.contentWindow) {
                                try {
                                    ytIframe.contentWindow.postMessage(JSON.stringify({
                                        event: 'listening',
                                        id: 1,
                                        channel: 'widget'
                                    }), '*');
                                } catch (e) {}
                            }
                        };
                        ytIframe.addEventListener('load', subscribeToYt);
                        setTimeout(subscribeToYt, 800);
                        setTimeout(subscribeToYt, 2500);

                        window.addEventListener('message', (event) => {
                            if (!event.data || typeof event.data !== 'string') return;
                            try {
                                const msg = JSON.parse(event.data);
                                if (msg.event === 'infoDelivery' && msg.info) {
                                    if (typeof msg.info.currentTime === 'number') {
                                        this.currentTime = msg.info.currentTime;
                                    }
                                    if (typeof msg.info.duration === 'number' && msg.info.duration > 0) {
                                        this.durationSeconds = Math.round(msg.info.duration);
                                    }
                                    if (typeof msg.info.playerState === 'number') {
                                        this.isPlaying = (msg.info.playerState === 1);
                                    }
                                }
                            } catch (e) {}
                        });
                    }

                    if (typeof lucide !== 'undefined') lucide.createIcons();

                    // Automatically transcribe video or fetch YouTube captions when viewing if subtitles have not been generated yet
                    if (this.videoSrc && (!this.subtitles || this.subtitles.length === 0)) {
                        setTimeout(() => {
                            this.analyzePostedVideoAudio();
                        }, 400);
                    }
                });
            },

            onVideoMetadata(e) {
                if (e.target && e.target.duration && isFinite(e.target.duration)) {
                    this.durationSeconds = Math.round(e.target.duration);
                }
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
                    return;
                }
                const ytIframe = document.getElementById('culturalYoutubeIframe');
                if (ytIframe && ytIframe.contentWindow) {
                    try {
                        ytIframe.contentWindow.postMessage(JSON.stringify({
                            event: 'command',
                            func: 'seekTo',
                            args: [seconds, true]
                        }), '*');
                        ytIframe.contentWindow.postMessage(JSON.stringify({
                            event: 'command',
                            func: 'playVideo',
                            args: []
                        }), '*');
                        this.isPlaying = true;
                    } catch (e) {}
                }
            },

            formatTime(seconds) {
                const s = Math.floor(seconds || 0);
                const m = Math.floor(s / 60);
                const remaining = s % 60;
                return `${String(m).padStart(2, '0')}:${String(remaining).padStart(2, '0')}`;
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
            },

            addSubtitleLine() {
                const start = Math.round((this.currentTime || 0) * 10) / 10;
                const end = Math.round((start + 5) * 10) / 10;
                this.subtitles.push({
                    start: start,
                    end: end,
                    text: '',
                    target_phrase: '',
                    source_phrase: '',
                    cultural_note: ''
                });
            },

            removeSubtitleLine(index) {
                this.subtitles.splice(index, 1);
            },

            async loadAudioSamples16k() {
                // 1. Try server-extracted lightweight 16kHz mono WAV first (avoids downloading 38MB MP4 in JS)
                if (this.audioWavUrl) {
                    try {
                        this.analysisStatus = 'Extracting 16kHz audio track from video...';
                        const metaRes = await fetch(this.audioWavUrl, {
                            headers: { 'Accept': 'application/json' }
                        });
                        const meta = await metaRes.json();
                        if (meta && meta.success && meta.wav_url) {
                            if (meta.duration_seconds) {
                                this.durationSeconds = meta.duration_seconds;
                            }
                            const wavRes = await fetch(meta.wav_url);
                            const wavBuf = await wavRes.arrayBuffer();
                            if (wavBuf.byteLength > 44) {
                                const int16 = new Int16Array(wavBuf, 44);
                                const float32 = new Float32Array(int16.length);
                                for (let i = 0; i < int16.length; i++) {
                                    float32[i] = int16[i] / 32768.0;
                                }
                                return {
                                    samples: float32,
                                    duration: Math.round(float32.length / 16000)
                                };
                            }
                        }
                    } catch (e) {
                        console.warn('Server WAV extraction fallback:', e);
                    }
                }

                // 2. Fallback: decode videoSrc directly in browser via OfflineAudioContext
                this.analysisStatus = 'Decoding video audio stream (16kHz)...';
                const response = await fetch(this.videoSrc);
                const arrayBuffer = await response.arrayBuffer();
                const AudioCtx = window.AudioContext || window.webkitAudioContext;
                const tempCtx = new AudioCtx();
                const decoded = await tempCtx.decodeAudioData(arrayBuffer);
                try { await tempCtx.close(); } catch (e) {}

                const targetLength = Math.ceil(decoded.duration * 16000);
                const offlineCtx = new OfflineAudioContext(1, targetLength, 16000);
                const source = offlineCtx.createBufferSource();
                source.buffer = decoded;
                source.connect(offlineCtx.destination);
                source.start(0);
                const rendered = await offlineCtx.startRendering();
                const float32 = rendered.getChannelData(0);

                // Normalize peak volume
                let maxAbs = 0;
                for (let i = 0; i < float32.length; i++) {
                    const a = Math.abs(float32[i]);
                    if (a > maxAbs) maxAbs = a;
                }
                if (maxAbs > 0.005 && maxAbs < 0.8) {
                    const scale = 0.9 / maxAbs;
                    for (let i = 0; i < float32.length; i++) {
                        float32[i] *= scale;
                    }
                }

                return {
                    samples: float32,
                    duration: Math.round(decoded.duration || 60)
                };
            },

            async analyzePostedVideoAudio() {
                if (!this.videoSrc || this.isAnalyzingAudio) return;
                this.isAnalyzingAudio = true;

                try {
                    // For YouTube videos, extract timed captions & metadata on the server
                    if (this.isYoutubeVideo) {
                        this.analysisStatus = 'Extracting synchronized captions & transcript from YouTube video...';
                        const res = await fetch(this.syncSubtitlesUrl, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': config.csrfToken,
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify({
                                fetch_external_transcript: true,
                                regenerate_with_ai: false
                            })
                        });
                        const data = await res.json();
                        if (data && data.success && Array.isArray(data.timed_subtitles) && data.timed_subtitles.length > 0) {
                            this.subtitles = data.timed_subtitles;
                            if (Array.isArray(data.vocabulary)) this.vocabulary = data.vocabulary;
                            if (Array.isArray(data.key_takeaways)) this.keyTakeaways = data.key_takeaways;
                            if (data.ai_summary) this.aiSummary = data.ai_summary;
                            if (data.duration_seconds) this.durationSeconds = data.duration_seconds;
                            if (data.transcript) this.editableTranscript = data.transcript;
                            this.analysisStatus = `Synchronized ${data.timed_subtitles.length} subtitle lines from YouTube video.`;
                        } else {
                            this.analysisStatus = 'Could not extract captions automatically. Use Edit Subtitles to enter your script.';
                        }
                        return;
                    }

                    const { samples, duration } = await this.loadAudioSamples16k();
                    if (duration > 0) {
                        this.durationSeconds = duration;
                    }

                    this.analysisStatus = 'Transcribing spoken words from video audio...';
                    if (!window.__kulturegramWhisperPromise) {
                        const { pipeline, env } = await import('https://cdn.jsdelivr.net/npm/@xenova/transformers@2.17.2/dist/transformers.min.js');
                        env.allowLocalModels = false;
                        window.__kulturegramWhisperPromise = pipeline('automatic-speech-recognition', 'Xenova/whisper-tiny.en', {
                            quantized: true
                        });
                    }

                    const transcriber = await window.__kulturegramWhisperPromise;
                    const output = await transcriber(samples, {
                        chunk_length_s: 30,
                        stride_length_s: 5,
                        return_timestamps: true
                    });

                    if (output && Array.isArray(output.chunks) && output.chunks.length > 0) {
                        const extracted = output.chunks
                            .map((chunk, i) => {
                                const start = Array.isArray(chunk.timestamp) && chunk.timestamp[0] !== null ? Number(chunk.timestamp[0]) : i * 5;
                                const end = Array.isArray(chunk.timestamp) && chunk.timestamp[1] !== null ? Number(chunk.timestamp[1]) : start + 5;
                                return {
                                    start: Math.round(start * 10) / 10,
                                    end: Math.round(Math.max(end, start + 1.5) * 10) / 10,
                                    text: (chunk.text || '').trim(),
                                    target_phrase: null,
                                    source_phrase: null,
                                    cultural_note: null
                                };
                            })
                            .filter(s => s.text.length > 0);

                        if (extracted.length > 0) {
                            this.subtitles = extracted;
                            this.editableTranscript = extracted.map(s => s.text).join(' ');
                            this.analysisStatus = `Transcribed ${extracted.length} segments. Structuring bilingual phrases with Meta AI...`;
                            await this.saveSyncedSubtitles(true, duration);
                            return;
                        }
                    }

                    this.analysisStatus = 'Could not detect clear speech segments automatically. Use Edit Subtitles to enter your script.';
                } catch (err) {
                    console.warn('Video audio transcription error:', err);
                    this.analysisStatus = 'Could not complete automatic transcription (' + (err.message || 'error') + ').';
                } finally {
                    this.isAnalyzingAudio = false;
                }
            },

            async saveSyncedSubtitles(regenerateWithAi = false, detectedDuration = null) {
                if (!this.syncSubtitlesUrl || this.isSavingSubtitles) return;
                this.isSavingSubtitles = true;
                this.analysisStatus = regenerateWithAi
                    ? 'Structuring spoken transcript & bilingual vocabulary with Meta AI...'
                    : 'Saving updated subtitles...';

                try {
                    const videoEl = document.getElementById('culturalVideo');
                    const duration = detectedDuration || this.durationSeconds || (videoEl && videoEl.duration ? Math.round(videoEl.duration) : null);

                    const res = await fetch(this.syncSubtitlesUrl, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': config.csrfToken,
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            transcript: this.editableTranscript || this.subtitles.map(s => s.text).join(' '),
                            subtitles: this.subtitles,
                            video_duration: duration,
                            regenerate_with_ai: regenerateWithAi
                        })
                    });

                    const data = await res.json();
                    if (data.success) {
                        if (Array.isArray(data.timed_subtitles)) {
                            this.subtitles = data.timed_subtitles;
                        }
                        if (Array.isArray(data.vocabulary)) {
                            this.vocabulary = data.vocabulary;
                        }
                        if (Array.isArray(data.key_takeaways)) {
                            this.keyTakeaways = data.key_takeaways;
                        }
                        if (data.ai_summary) {
                            this.aiSummary = data.ai_summary;
                        }
                        if (data.duration_seconds) {
                            this.durationSeconds = data.duration_seconds;
                        }
                        if (data.transcript) {
                            this.editableTranscript = data.transcript;
                        }
                        this.analysisStatus = 'Subtitles and vocabulary synchronized with video.';
                    } else {
                        this.analysisStatus = data.message || 'Could not save subtitles.';
                    }
                } catch (e) {
                    console.error('Failed to sync subtitles:', e);
                    this.analysisStatus = 'Error saving subtitles. Please try again.';
                } finally {
                    this.isSavingSubtitles = false;
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
