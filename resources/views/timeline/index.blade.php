@extends('layouts.app')

@section('title', 'Kulturegram - Your Cultural Journey')

@section('content')
    <div class="max-w-6xl mx-auto p-4 sm:p-6">
        @guest
            <!-- Welcome Section for Guests -->
            <div
                class="bg-stone-50 dark:bg-stone-850 rounded-2xl p-6 sm:p-8 border border-stone-200 dark:border-stone-700 mb-8">
                <div class="text-center mb-8">
                    <div
                        class="w-14 h-14 sm:w-16 sm:h-16 glass-orange rounded-2xl flex items-center justify-center mx-auto mb-4">
                        <i data-lucide="book-open" class="w-7 h-7 sm:w-8 sm:h-8 text-white"></i>
                    </div>
                    <h2 class="text-2xl sm:text-3xl font-bold text-stone-800 dark:text-white mb-3">
                        Welcome to Kulturegram
                    </h2>
                    <p class="text-base sm:text-lg text-stone-600 dark:text-stone-400 leading-relaxed max-w-2xl mx-auto">
                        Your life. Your culture. Your story. A new kind of social platform designed to capture
                        the complete story of your journey while preserving the heritage that shapes us all.
                    </p>
                </div>

                <!-- Features Grid -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                    <div class="text-center">
                        <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center mx-auto mb-3">
                            <i data-lucide="book-open" class="w-6 h-6 text-blue-600"></i>
                        </div>
                        <h3 class="font-semibold text-stone-800 dark:text-stone-200 mb-2">Authentic Stories</h3>
                        <p class="text-sm text-stone-600 dark:text-stone-400">Share your real journey chronologically, not
                            algorithmically</p>
                    </div>

                    <div class="text-center">
                        <div class="w-12 h-12 bg-green-100 rounded-xl flex items-center justify-center mx-auto mb-3">
                            <i data-lucide="globe" class="w-6 h-6 text-green-600"></i>
                        </div>
                        <h3 class="font-semibold text-stone-800 mb-2">Cultural Heritage</h3>
                        <p class="text-sm text-stone-600">Preserve and celebrate cultures from around the world</p>
                    </div>

                    <div class="text-center">
                        <div class="w-12 h-12 bg-purple-100 rounded-xl flex items-center justify-center mx-auto mb-3">
                            <i data-lucide="users" class="w-6 h-6 text-purple-600"></i>
                        </div>
                        <h3 class="font-semibold text-stone-800 mb-2">Meaningful Connections</h3>
                        <p class="text-sm text-stone-600">Lock-In to timelines that matter, TAP to show appreciation</p>
                    </div>

                    <div class="text-center">
                        <div class="w-12 h-12 bg-orange-100 rounded-xl flex items-center justify-center mx-auto mb-3">
                            <i data-lucide="archive" class="w-6 h-6 text-orange-600"></i>
                        </div>
                        <h3 class="font-semibold text-stone-800 mb-2">Personal Vault</h3>
                        <p class="text-sm text-stone-600">Keep your most precious memories safe and private</p>
                    </div>
                </div>

                <div class="text-center">
                    <p class="text-stone-600 mb-4">Ready to start your timeline?</p>
                    <a href="{{ route('register') }}"
                        class="glass-orange text-white px-6 sm:px-8 py-3 rounded-xl font-semibold transition-all duration-200 shadow-md hover:shadow-lg inline-block">
                        Join Kulturegram
                    </a>
                </div>
            </div>
        @endguest

        @auth
            <div class="mb-8">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h2 class="text-xl sm:text-2xl font-bold text-stone-800 dark:text-white mb-1">Your Timeline</h2>
                        <p class="text-stone-600 dark:text-stone-400 text-sm sm:text-base">Stories unfolding in real time, just
                            as life happens.</p>
                    </div>
                </div>
                <div class="flex space-x-2 overflow-x-auto scrollbar-hide pb-2">
                    <a href="{{ route('timeline.index', ['filter' => 'all']) }}"
                        class="flex-shrink-0 px-4 py-2 rounded-lg text-sm font-medium transition {{ $filter === 'all' ? 'glass-orange text-white' : 'bg-white dark:bg-stone-800 text-stone-600 dark:text-stone-400 border border-stone-200 dark:border-stone-700 hover:border-stone-300' }}">
                        Stories
                    </a>
                    <a href="{{ route('timeline.index', ['filter' => 'lockedin']) }}"
                        class="flex-shrink-0 px-4 py-2 rounded-lg text-sm font-medium transition {{ $filter === 'lockedin' ? 'glass-orange text-white' : 'bg-white dark:bg-stone-800 text-stone-600 dark:text-stone-400 border border-stone-200 dark:border-stone-700 hover:border-stone-300' }}">
                        Locked-In
                    </a>
                    <a href="{{ route('timeline.index', ['filter' => 'cultural']) }}"
                        class="flex-shrink-0 px-4 py-2 rounded-lg text-sm font-medium transition {{ $filter === 'cultural' ? 'glass-orange text-white' : 'bg-white dark:bg-stone-800 text-stone-600 dark:text-stone-400 border border-stone-200 dark:border-stone-700 hover:border-stone-300' }}">
                        Cultural
                    </a>
                </div>
            </div>

            {{-- AI-Powered Cultural Connections & Like-Minded Collaborators --}}
            @include('components.cultural-connections-card')
        @endauth

        @if(request()->filled('tag'))
            <div class="mb-6 flex items-center justify-between bg-amber-50 dark:bg-amber-950/40 border border-amber-200 dark:border-amber-800 px-4 py-2.5 rounded-xl">
                <span class="text-sm font-medium text-amber-800 dark:text-amber-200 flex items-center gap-1.5">
                    <i data-lucide="hash" class="w-4 h-4 text-amber-600"></i>
                    Filtering stories tagged with <span class="font-bold">#{{ request('tag') }}</span>
                </span>
                <a href="{{ route('timeline.index') }}" class="text-xs font-semibold text-amber-700 dark:text-amber-300 hover:underline flex items-center gap-1">
                    <i data-lucide="x" class="w-3.5 h-3.5"></i> Clear filter
                </a>
            </div>
        @endif

        <!-- Feed -->
        <div class="space-y-6">
            @forelse($posts as $post)
                <article id="post-{{ $post->id }}"
                    class="bg-white dark:bg-stone-900/50 backdrop-blur-sm rounded-2xl p-4 sm:p-6 shadow-sm border border-stone-100 dark:border-stone-800 hover:shadow-md transition-all duration-300 overflow-hidden">
                    <div class="w-full">
                        <!-- Post Content -->
                        <div class="w-full">
                            <!-- Header -->
                            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-3">
                                <div class="flex flex-wrap items-center gap-2 sm:gap-3 text-sm sm:text-base">
                                    <!-- User Avatar -->
                                    <div class="flex-shrink-0">
                                        <img src="{{ $post->user->profile_photo_url }}"
                                            class="w-10 h-10 rounded-full object-cover border border-stone-100 shadow-sm"
                                            loading="lazy">
                                    </div>

                                    <!-- User Info -->
                                    <a href="{{ route('profile.user', $post->user) }}"
                                        class="font-semibold text-stone-800 dark:text-stone-200 hover:text-amber-600">
                                        {{ $post->user->name }}
                                    </a>
                                    <span class="text-stone-400 text-xs sm:text-sm">@ {{ $post->user->username }}</span>

                                    @if($post->user->location)
                                        <span
                                            class="text-xs bg-stone-100 dark:bg-stone-800 text-stone-600 dark:text-stone-400 px-2 py-0.5 rounded-full">
                                            {{ $post->user->location }}
                                        </span>
                                    @endif

                                    <!-- Divider Dot -->
                                    <span class="hidden sm:inline text-stone-300">•</span>

                                    <!-- Meta Info -->
                                    <span class="flex items-center space-x-1 text-stone-500">
                                        <i data-lucide="clock" class="w-3 h-3 sm:w-4 sm:h-4"></i>
                                        <span>{{ $post->time_ago }}</span>
                                    </span>

                                    @if($post->location)
                                        <span class="flex items-center space-x-1 text-stone-500">
                                            <i data-lucide="map-pin" class="w-3 h-3 sm:w-4 sm:h-4"></i>
                                            <span>{{ $post->location }}</span>
                                        </span>
                                    @endif

                                    @if($post->chapter)
                                        <span
                                            class="bg-amber-100 text-amber-800 px-2 py-0.5 rounded-full text-[10px] sm:text-xs font-medium">
                                            {{ $post->chapter }}
                                        </span>
                                    @endif
                                </div>

                                @if($post->privacy === 'private')
                                    <i data-lucide="lock" class="w-4 h-4 text-stone-400 mt-2 sm:mt-0"></i>
                                @endif
                            </div>

                            <!-- Body & Cultural Translation -->
                            <div class="mb-4">
                                <div id="post-content-container-{{ $post->id }}">
                                    <div id="post-original-content-{{ $post->id }}" class="text-stone-700 dark:text-stone-300 leading-relaxed text-sm sm:text-base">
                                        {!! $post->formatted_content !!}
                                    </div>
                                    <div id="post-translated-content-{{ $post->id }}" class="hidden text-stone-800 dark:text-stone-100 leading-relaxed text-sm sm:text-base bg-amber-500/5 dark:bg-amber-950/20 p-3 rounded-2xl border border-amber-500/20 mt-1">
                                        <!-- Injected translated content -->
                                    </div>
                                </div>

                                <!-- AI Post Translation Bar -->
                                @php
                                    $viewerLanguage = auth()->check() ? auth()->user()->getPreferredLanguage() : 'English';
                                    $postLanguage = $post->language ?: 'English';
                                    $isDifferentLanguage = strtolower(trim($postLanguage)) !== strtolower(trim($viewerLanguage));
                                @endphp
                                <div class="mt-2.5 flex items-center justify-between flex-wrap gap-2 text-xs">
                                    <div class="flex items-center gap-2">
                                        <button type="button" 
                                            onclick="togglePostTranslation({{ $post->id }}, '{{ $viewerLanguage }}')" 
                                            id="btn-translate-{{ $post->id }}"
                                            class="inline-flex items-center gap-1.5 text-xs font-semibold text-amber-600 dark:text-amber-400 hover:text-amber-700 dark:hover:text-amber-300 transition hover:underline py-0.5"
                                            title="Translate story with KultureAI">
                                            <i data-lucide="globe" class="w-3.5 h-3.5 text-amber-500"></i>
                                            <span id="btn-translate-text-{{ $post->id }}">{{ $isDifferentLanguage ? 'See Translation (to ' . $viewerLanguage . ')' : 'See Translation' }}</span>
                                        </button>
                                        <span id="translation-loader-{{ $post->id }}" class="hidden text-[11px] text-amber-500 items-center gap-1">
                                            <i data-lucide="loader" class="w-3 h-3 animate-spin"></i>
                                            <span>Translating...</span>
                                        </span>
                                        @if($post->language)
                                            <span class="text-[10px] text-stone-400 dark:text-stone-500 flex items-center gap-1">
                                                <span>•</span>
                                                <span class="px-1.5 py-0.2 rounded bg-stone-100 dark:bg-stone-800 font-medium">{{ $post->language }}</span>
                                            </span>
                                        @endif
                                        @if($post->is_ai_formatted)
                                            <span class="text-[10px] text-amber-600/80 dark:text-amber-400/80 font-medium flex items-center gap-0.5" title="Story recorded via Oral Voice Dictation & AI Arranged">
                                                <i data-lucide="sparkles" class="w-2.5 h-2.5"></i>
                                                <span>Oral Story</span>
                                            </span>
                                        @endif
                                    </div>

                                    <!-- Language Picker Dropdown -->
                                    <div class="relative" x-data="{ openLang: false }">
                                        <button type="button" @click="openLang = !openLang" class="text-[11px] text-stone-400 hover:text-stone-600 dark:hover:text-stone-300 transition flex items-center gap-1">
                                            <span>Translate to...</span>
                                            <i data-lucide="chevron-down" class="w-3 h-3"></i>
                                        </button>
                                        <div x-show="openLang" @click.away="openLang = false" x-cloak class="absolute right-0 bottom-full mb-1 z-30 w-40 bg-white dark:bg-stone-900 rounded-2xl shadow-xl border border-stone-200 dark:border-stone-800 py-1.5 text-xs max-h-52 overflow-y-auto">
                                            <div class="px-3 py-1 text-[10px] font-bold text-stone-400 uppercase tracking-wider">Choose Language</div>
                                            @foreach(['English', 'Hausa', 'Yoruba', 'Igbo', 'Swahili', 'French', 'Spanish', 'Nigerian Pidgin', 'Arabic', 'Zulu', 'Amharic'] as $langOpt)
                                                <button type="button" 
                                                    @click="openLang = false; togglePostTranslation({{ $post->id }}, '{{ $langOpt }}')"
                                                    class="w-full text-left px-3 py-1.5 hover:bg-amber-500/10 hover:text-amber-600 text-stone-700 dark:text-stone-300 font-medium transition flex items-center justify-between">
                                                    <span>{{ $langOpt }}</span>
                                                    @if(strtolower($viewerLanguage) === strtolower($langOpt))
                                                        <i data-lucide="check" class="w-3 h-3 text-amber-500"></i>
                                                    @endif
                                                </button>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>

                                @if($post->chapter === 'Cultural Class' || (!empty($post->tags) && in_array('cultural-class', (array)$post->tags)))
                                    <div class="mt-3 p-2.5 sm:p-3.5 rounded-2xl bg-gradient-to-r from-amber-500/10 via-orange-500/10 to-transparent border border-amber-500/30 flex items-center justify-between gap-2.5 overflow-hidden">
                                        <div class="flex items-center gap-2.5 min-w-0 flex-1 overflow-hidden">
                                            <div class="w-8 h-8 sm:w-9 sm:h-9 rounded-xl bg-amber-500 text-white flex items-center justify-center shrink-0 shadow-md">
                                                <i data-lucide="graduation-cap" class="w-4 h-4 sm:w-5 sm:h-5"></i>
                                            </div>
                                            <div class="flex items-center gap-1.5 min-w-0 flex-1 overflow-hidden text-xs sm:text-sm font-bold text-stone-800 dark:text-stone-100 whitespace-nowrap">
                                                <span class="text-[10px] sm:text-xs font-black uppercase tracking-wider text-amber-500 shrink-0">Educational Hub Class</span>
                                                <span class="text-stone-400 dark:text-stone-600 text-xs shrink-0">•</span>
                                                <span class="truncate text-stone-700 dark:text-stone-300 font-semibold">Interactive Subtitles & Bilingual Vocab</span>
                                            </div>
                                        </div>
                                        <a href="{{ route('education.classes.index') }}" 
                                           class="px-3 py-1.5 sm:px-4 sm:py-2 rounded-xl bg-amber-500 hover:bg-amber-600 text-white text-xs font-bold transition shadow-sm whitespace-nowrap flex items-center gap-1 shrink-0">
                                            <span>Watch Class</span>
                                            <i data-lucide="chevron-right" class="w-3.5 h-3.5"></i>
                                        </a>
                                    </div>
                                @endif

                                @if(!empty($post->tags))
                                    <div class="flex flex-wrap gap-1.5 mt-3">
                                        @foreach($post->tags as $tag)
                                            <a href="{{ route('timeline.index', ['tag' => $tag]) }}"
                                                class="inline-flex items-center text-xs font-medium text-amber-600 dark:text-amber-400 bg-amber-50 dark:bg-amber-950/40 px-2.5 py-0.5 rounded-full hover:bg-amber-100 dark:hover:bg-amber-900/60 transition">
                                                #{{ $tag }}
                                            </a>
                                        @endforeach
                                    </div>
                                @endif

                                @if($post->image)
                                    <div class="-mx-4 sm:-mx-6 mt-4 overflow-hidden bg-stone-100 dark:bg-stone-800/50 border-y border-stone-100 dark:border-stone-800">
                                        <img src="{{ Storage::url($post->image) }}"
                                            alt="Story image"
                                            class="w-full h-auto max-h-[650px] object-cover transition-transform duration-500 hover:scale-[1.01]"
                                            loading="lazy">
                                    </div>
                                @endif

                                @if($post->video)
                                    <div class="-mx-4 sm:-mx-6 mt-4 overflow-hidden bg-black border-y border-stone-100 dark:border-stone-800">
                                        <video src="{{ Storage::url($post->video) }}" controls class="w-full max-h-[600px] object-cover"></video>
                                    </div>
                                @endif

                                @if($post->audio)
                                    <div class="mt-4 p-3 bg-stone-100 dark:bg-stone-800 rounded-xl">
                                        <audio src="{{ Storage::url($post->audio) }}" controls class="w-full"></audio>
                                    </div>
                                @endif
                            </div>

                            <!-- Actions -->
                            <div
                                class="flex flex-wrap justify-center items-center gap-2 sm:gap-3 pt-3 sm:pt-4 border-t border-stone-100 dark:border-stone-800 text-xs sm:text-sm md:text-base">
                                @auth
                                    <!-- TAP -->
                                    <button onclick="toggleTap({{ $post->id }})" data-tap-post="{{ $post->id }}"
                                        class="flex items-center space-x-1 sm:space-x-2 px-2 sm:px-3 py-1 sm:py-1.5 rounded-lg transition 
                                                                                    {{ auth()->user()->hasTapped($post) ? 'bg-red-50 text-red-600' : 'text-stone-600 hover:bg-stone-100' }}">
                                        <i data-lucide="heart"
                                            class="w-3 h-3 sm:w-4 sm:h-4 {{ auth()->user()->hasTapped($post) ? 'fill-current' : '' }}"></i>
                                        <span class="font-medium" data-tap-count="{{ $post->id }}">
                                            {{ $post->taps_count }}
                                        </span>
                                    </button>

                                    <!-- Resonance -->
                                    <button onclick="toggleResonanceBox({{ $post->id }})"
                                        class="flex items-center space-x-1 sm:space-x-2 px-2 sm:px-3 py-1 sm:py-1.5 rounded-lg transition text-stone-600 hover:bg-stone-100">
                                        <i data-lucide="message-circle" class="w-3 h-3 sm:w-4 sm:h-4"></i>
                                        <span class="font-medium"
                                            data-resonance-count="{{ $post->id }}">{{ $post->resonances->count() }} Resonance</span>
                                    </button>

                                    <!-- Check-In -->
                                    <button onclick="toggleCheckin({{ $post->id }})" data-checkin-post="{{ $post->id }}"
                                        class="flex items-center space-x-1 sm:space-x-2 px-2 sm:px-3 py-1 sm:py-1.5 rounded-lg transition {{ auth()->user()->hasCheckedIn($post) ? 'text-green-600' : 'text-stone-600 hover:bg-stone-100' }}">
                                        <i data-lucide="check-circle" class="w-3 h-3 sm:w-4 sm:h-4"></i>
                                        <span class="font-medium" data-checkin-count="{{ $post->id }}">
                                            {{ $post->check_ins_count }}
                                        </span>
                                    </button>

                                    <!-- Share -->
                                    <button onclick="openShareModal('{{ url('/timeline/' . $post->id) }}')"
                                        class="flex items-center space-x-1 sm:space-x-2 px-2 sm:px-3 py-1 sm:py-1.5 rounded-lg transition text-stone-600 hover:bg-stone-100">
                                        <i data-lucide="share-2" class="w-3 h-3 sm:w-4 sm:h-4"></i>
                                        <span class="font-medium"></span>
                                    </button>
                                @endauth
                            </div>


                            <!-- Resonance Box -->
                            <div id="resonance-box-{{ $post->id }}" class="hidden mt-4 pt-3 border-t border-stone-100 dark:border-stone-800 space-y-3">
                                <div id="resonance-list-{{ $post->id }}" class="space-y-2.5 sm:space-y-3">
                                    @forelse($post->resonances->whereNull('parent_id') as $resonance)
                                        <div id="resonance-item-{{ $resonance->id }}" class="p-2.5 sm:p-3 bg-stone-50 dark:bg-stone-800/60 rounded-xl border border-stone-100 dark:border-stone-800 text-xs sm:text-sm space-y-1.5 transition-all">
                                            <!-- Author Header -->
                                            <div class="flex items-center justify-between gap-2">
                                                <div class="flex items-center gap-2 min-w-0">
                                                    <img src="{{ $resonance->user->profile_photo_url }}" 
                                                         class="w-6 h-6 rounded-full object-cover border border-stone-200 dark:border-stone-700 shrink-0" 
                                                         alt="{{ $resonance->user->name }}">
                                                    <div class="flex items-center gap-1.5 min-w-0 truncate">
                                                        <span class="font-bold text-stone-900 dark:text-stone-100 truncate">{{ $resonance->user->name }}</span>
                                                        @if($resonance->user_id === $post->user_id)
                                                            <span class="px-1.5 py-0.2 rounded-md bg-amber-500/10 text-amber-600 dark:text-amber-400 text-[10px] font-black uppercase tracking-wider shrink-0 border border-amber-500/20">Author</span>
                                                        @endif
                                                    </div>
                                                </div>
                                                <span class="text-[10px] sm:text-xs text-stone-400 shrink-0">{{ $resonance->created_at->diffForHumans() }}</span>
                                            </div>

                                            <!-- Content -->
                                            <div class="text-stone-700 dark:text-stone-300 leading-relaxed pl-8">
                                                {!! $resonance->formatted_content !!}
                                            </div>

                                            <!-- Actions: Reaction + Reply + Author Liked Badge -->
                                            <div class="flex items-center justify-between pt-1 pl-8">
                                                <div class="flex items-center gap-3">
                                                    @auth
                                                        <!-- Tap / Heart Reaction -->
                                                        <button type="button" 
                                                            onclick="toggleResonanceTap({{ $resonance->id }})" 
                                                            data-resonance-tap-btn="{{ $resonance->id }}"
                                                            class="flex items-center gap-1 text-[11px] sm:text-xs font-semibold transition active:scale-90 {{ auth()->user()->hasTapped($resonance) ? 'text-rose-600 dark:text-rose-400' : 'text-stone-500 dark:text-stone-400 hover:text-rose-600 dark:hover:text-rose-400' }}"
                                                            title="React to comment">
                                                            <i data-lucide="heart" class="w-3.5 h-3.5 {{ auth()->user()->hasTapped($resonance) ? 'fill-current' : '' }}"></i>
                                                            <span data-resonance-tap-count="{{ $resonance->id }}">{{ $resonance->taps_count > 0 ? $resonance->taps_count : '' }}</span>
                                                        </button>

                                                        <!-- Reply Button -->
                                                        <button type="button" 
                                                            onclick="toggleResonanceReplyBox({{ $post->id }}, {{ $resonance->id }}, '{{ addslashes($resonance->user->name) }}')"
                                                            class="flex items-center gap-1 text-[11px] sm:text-xs font-semibold text-stone-500 dark:text-stone-400 hover:text-amber-600 dark:hover:text-amber-400 transition"
                                                            title="Reply to this comment">
                                                            <i data-lucide="reply" class="w-3.5 h-3.5"></i>
                                                            <span>Reply</span>
                                                        </button>
                                                    @endauth
                                                </div>

                                                <!-- Author Liked Badge -->
                                                <div data-resonance-author-badge="{{ $resonance->id }}" 
                                                     class="{{ $resonance->isAuthorLiked() ? 'flex' : 'hidden' }} items-center gap-1 text-[10px] font-bold text-amber-600 dark:text-amber-400 bg-amber-500/10 px-2 py-0.5 rounded-full border border-amber-500/20">
                                                    <i data-lucide="heart" class="w-2.5 h-2.5 fill-current text-rose-500"></i>
                                                    <span>Liked by author</span>
                                                </div>
                                            </div>

                                            <!-- Inline Reply Input Form -->
                                            @auth
                                                <div id="resonance-reply-form-{{ $resonance->id }}" class="hidden pt-2 pl-8">
                                                    <div class="flex items-center gap-1.5 bg-white dark:bg-stone-900 border border-stone-200 dark:border-stone-700 rounded-xl p-1.5 shadow-sm">
                                                        <input type="text" 
                                                            id="resonance-reply-input-{{ $resonance->id }}" 
                                                            placeholder="Reply to {{ $resonance->user->name }}..." 
                                                            class="flex-1 bg-transparent px-2 text-xs text-stone-800 dark:text-stone-200 outline-none"
                                                            onkeydown="if(event.key === 'Enter') submitResonanceReply({{ $post->id }}, {{ $resonance->id }})">
                                                        <button type="button" 
                                                            onclick="submitResonanceReply({{ $post->id }}, {{ $resonance->id }})" 
                                                            class="p-1.5 bg-amber-500 text-white rounded-lg hover:bg-amber-600 transition active:scale-95"
                                                            title="Send reply">
                                                            <i data-lucide="send" class="w-3 h-3"></i>
                                                        </button>
                                                        <button type="button" 
                                                            onclick="toggleResonanceReplyBox({{ $post->id }}, {{ $resonance->id }})" 
                                                            class="p-1.5 text-stone-400 hover:text-stone-600 dark:hover:text-stone-200 rounded-lg transition"
                                                            title="Cancel">
                                                            <i data-lucide="x" class="w-3 h-3"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                            @endauth

                                            <!-- Nested Replies List -->
                                            <div id="resonance-replies-list-{{ $resonance->id }}" class="space-y-2 mt-2 pl-6 sm:pl-8 border-l-2 border-stone-200 dark:border-stone-700 {{ $resonance->replies->count() > 0 ? '' : 'hidden' }}">
                                                @foreach($resonance->replies as $reply)
                                                    <div id="resonance-item-{{ $reply->id }}" class="p-2 bg-white/70 dark:bg-stone-900/60 rounded-xl border border-stone-100 dark:border-stone-800 text-xs space-y-1">
                                                        <div class="flex items-center justify-between gap-2">
                                                            <div class="flex items-center gap-1.5 min-w-0">
                                                                <img src="{{ $reply->user->profile_photo_url }}" 
                                                                     class="w-5 h-5 rounded-full object-cover border border-stone-200 dark:border-stone-700 shrink-0" 
                                                                     alt="{{ $reply->user->name }}">
                                                                <span class="font-bold text-stone-900 dark:text-stone-100 truncate">{{ $reply->user->name }}</span>
                                                                @if($reply->user_id === $post->user_id)
                                                                    <span class="px-1 py-0.2 rounded bg-amber-500/10 text-amber-600 dark:text-amber-400 text-[9px] font-black uppercase tracking-wider shrink-0 border border-amber-500/20">Author</span>
                                                                @endif
                                                            </div>
                                                            <span class="text-[9px] sm:text-[10px] text-stone-400 shrink-0">{{ $reply->created_at->diffForHumans() }}</span>
                                                        </div>
                                                        <div class="text-stone-700 dark:text-stone-300 leading-relaxed pl-6.5">
                                                            {!! $reply->formatted_content !!}
                                                        </div>
                                                        <div class="flex items-center justify-between pt-0.5 pl-6.5">
                                                            @auth
                                                                <button type="button" 
                                                                    onclick="toggleResonanceTap({{ $reply->id }})" 
                                                                    data-resonance-tap-btn="{{ $reply->id }}"
                                                                    class="flex items-center gap-1 text-[10px] font-semibold transition active:scale-90 {{ auth()->user()->hasTapped($reply) ? 'text-rose-600 dark:text-rose-400' : 'text-stone-400 hover:text-rose-600 dark:hover:text-rose-400' }}"
                                                                    title="React to reply">
                                                                    <i data-lucide="heart" class="w-3 h-3 {{ auth()->user()->hasTapped($reply) ? 'fill-current' : '' }}"></i>
                                                                    <span data-resonance-tap-count="{{ $reply->id }}">{{ $reply->taps_count > 0 ? $reply->taps_count : '' }}</span>
                                                                </button>
                                                            @endauth

                                                            <div data-resonance-author-badge="{{ $reply->id }}" 
                                                                 class="{{ $reply->isAuthorLiked() ? 'flex' : 'hidden' }} items-center gap-0.5 text-[9px] font-bold text-amber-600 dark:text-amber-400 bg-amber-500/10 px-1.5 py-0.2 rounded-full border border-amber-500/20">
                                                                <i data-lucide="heart" class="w-2 h-2 fill-current text-rose-500"></i>
                                                                <span>Liked by author</span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    @empty
                                        <p class="text-xs text-stone-400 dark:text-stone-500 py-1 italic">No resonances yet. Be the first to resonate!</p>
                                    @endforelse
                                </div>

                                @auth
                                    <div class="flex items-center mt-2 sm:mt-3 space-x-1 sm:space-x-2">
                                        <input type="text" id="resonance-input-{{ $post->id }}" placeholder="Write a resonance..."
                                            class="flex-1 border dark:border-stone-700 bg-white dark:bg-stone-900 dark:text-stone-200 rounded-lg px-2 sm:px-3 py-1.5 sm:py-2 text-xs sm:text-sm focus:ring-2 focus:ring-amber-500 focus:outline-none"
                                            onkeydown="if(event.key === 'Enter') submitResonance({{ $post->id }})">
                                        <button onclick="submitResonance({{ $post->id }})"
                                            class="p-2 sm:p-3 bg-amber-500 text-white rounded-lg hover:bg-amber-600 transition flex items-center justify-center">
                                            <i data-lucide="send" class="w-4 h-4 sm:w-5 sm:h-5"></i>
                                        </button>
                                    </div>
                                @endauth
                            </div>
                        </div>
                    </div>
                </article>
            @empty
                <div class="text-center py-12">
                    <i data-lucide="book-open" class="w-14 h-14 sm:w-16 sm:h-16 text-stone-300 mx-auto mb-4"></i>
                    <h3 class="text-base sm:text-lg font-semibold text-stone-600 mb-2">No stories yet</h3>
                    <p class="text-stone-500 mb-4">
                        @auth Be the first to share your story! @else Join Kulturegram to start sharing. @endauth
                    </p>
                </div>
            @endforelse
        </div>

        @if($posts->hasPages())
            <div class="mt-8 flex justify-center">{{ $posts->links() }}</div>
        @endif
    </div>

    <!-- Share Modal -->
    <div id="shareModal"
        class="fixed inset-0 bg-black/60 backdrop-blur-sm hidden items-center justify-center z-50 p-3 sm:p-0 animate-backdrop-fade">
        <div
            class="animate-modal-pop-up bg-white dark:bg-stone-900 rounded-xl sm:rounded-2xl shadow-2xl w-full max-w-sm sm:max-w-md p-4 sm:p-6 relative text-xs sm:text-sm border dark:border-stone-800">
            <!-- Close Button -->
            <button onclick="closeShareModal()"
                class="absolute top-2 right-2 sm:top-3 sm:right-3 text-gray-500 hover:text-gray-700">
                <i data-lucide="x" class="w-4 h-4 sm:w-5 sm:h-5"></i>
            </button>

            <!-- Header -->
            <h2
                class="text-base sm:text-lg font-semibold mb-3 sm:mb-4 flex items-center gap-2 text-stone-800 dark:text-stone-100">
                <i data-lucide="share-2" class="w-4 h-4 sm:w-5 sm:h-5"></i>
                Share this story
            </h2>

            <!-- Share Options -->
            <div class="space-y-2 sm:space-y-3">
                <button onclick="copyLink()"
                    class="flex items-center gap-2 sm:gap-3 w-full bg-gray-100 dark:bg-stone-800 hover:bg-gray-200 dark:hover:bg-stone-700 p-2 sm:p-3 rounded-lg transition text-stone-700 dark:text-stone-300">
                    <i data-lucide="copy" class="w-4 h-4 sm:w-5 sm:h-5 text-gray-600 dark:text-stone-400"></i>
                    <span class="text-xs sm:text-sm">Copy Link</span>
                </button>

                <a id="wa-share" href="#" target="_blank"
                    class="flex items-center gap-2 sm:gap-3 w-full bg-green-100 hover:bg-green-200 p-2 sm:p-3 rounded-lg transition">
                    <i data-lucide="message-circle" class="w-4 h-4 sm:w-5 sm:h-5 text-green-600"></i>
                    <span class="text-xs sm:text-sm">Share on WhatsApp</span>
                </a>

                <a id="fb-share" href="#" target="_blank"
                    class="flex items-center gap-2 sm:gap-3 w-full bg-blue-100 hover:bg-blue-200 p-2 sm:p-3 rounded-lg transition">
                    <i data-lucide="facebook" class="w-4 h-4 sm:w-5 sm:h-5 text-blue-600"></i>
                    <span class="text-xs sm:text-sm">Share on Facebook</span>
                </a>

                <a id="tw-share" href="#" target="_blank"
                    class="flex items-center gap-2 sm:gap-3 w-full bg-sky-100 hover:bg-sky-200 p-2 sm:p-3 rounded-lg transition">
                    <i data-lucide="twitter" class="w-4 h-4 sm:w-5 sm:h-5 text-sky-600"></i>
                    <span class="text-xs sm:text-sm">Share on Twitter</span>
                </a>
            </div>
        </div>
    </div>

@endsection

@push('scripts')
    <script>
        document.addEventListener("DOMContentLoaded", () => lucide.createIcons());

        let currentShareUrl = "";

        // Open modal
        function openShareModal(url) {
            currentShareUrl = url;
            document.getElementById("shareModal").classList.remove("hidden");
            document.getElementById("shareModal").classList.add("flex");

            document.getElementById("wa-share").href = `https://wa.me/?text=${encodeURIComponent(url)}`;
            document.getElementById("fb-share").href = `https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(url)}`;
            document.getElementById("tw-share").href = `https://twitter.com/intent/tweet?url=${encodeURIComponent(url)}`;
        }

        // Close modal
        function closeShareModal() {
            document.getElementById("shareModal").classList.add("hidden");
            document.getElementById("shareModal").classList.remove("flex");
        }

        // Copy link function
        function copyLink() {
            navigator.clipboard.writeText(currentShareUrl).then(() => {
                alert("Link copied to clipboard!");
            });
        }

        // Toggle TAP
        function toggleTap(postId) {
            let btn = document.querySelector(`[data-tap-post="${postId}"]`);
            let countSpan = document.querySelector(`[data-tap-count="${postId}"]`);
            let icon = btn.querySelector('i, svg');

            // Optimistic UI update
            let isTapped = btn.classList.contains('text-red-600');
            let currentCount = parseInt(countSpan.textContent) || 0;

            if (isTapped) {
                btn.classList.remove('bg-red-50', 'text-red-600');
                btn.classList.add('text-stone-600', 'hover:bg-stone-100');
                icon.classList.remove('fill-current');
                countSpan.textContent = Math.max(0, currentCount - 1);
            } else {
                btn.classList.add('bg-red-50', 'text-red-600');
                btn.classList.remove('text-stone-600', 'hover:bg-stone-100');
                icon.classList.add('fill-current');
                countSpan.textContent = currentCount + 1;
            }

            fetch(`/timeline/${postId}/tap`, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
            })
                .then(res => res.json())
                .then(data => {
                    // Sync with server data
                    countSpan.textContent = data.count;
                    if (data.tapped) {
                        btn.classList.add('bg-red-50', 'text-red-600');
                        icon.classList.add('fill-current');
                    } else {
                        btn.classList.remove('bg-red-50', 'text-red-600');
                        icon.classList.remove('fill-current');
                    }
                })
                .catch(err => {
                    // Revert on error
                    console.error('TAP error:', err);
                    if (isTapped) {
                        btn.classList.add('bg-red-50', 'text-red-600');
                        icon.classList.add('fill-current');
                        countSpan.textContent = currentCount;
                    } else {
                        btn.classList.remove('bg-red-50', 'text-red-600');
                        icon.classList.remove('fill-current');
                        countSpan.textContent = currentCount;
                    }
                });
        }

        // Toggle Check-In
        function toggleCheckin(postId) {
            let btn = document.querySelector(`[data-checkin-post="${postId}"]`);
            let countSpan = document.querySelector(`[data-checkin-count="${postId}"]`);

            // Optimistic UI update
            let isCheckedIn = btn.classList.contains('text-green-600');
            let currentCount = parseInt(countSpan.textContent) || 0;

            if (isCheckedIn) {
                btn.classList.remove('text-green-600');
                countSpan.textContent = Math.max(0, currentCount - 1);
            } else {
                btn.classList.add('text-green-600');
                countSpan.textContent = currentCount + 1;
            }

            fetch(`/timeline/${postId}/checkin`, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
            })
                .then(res => res.json())
                .then(data => {
                    countSpan.textContent = data.count;
                    if (data.checkedIn) {
                        btn.classList.add('text-green-600');
                    } else {
                        btn.classList.remove('text-green-600');
                    }
                })
                .catch(err => {
                    console.error('Checkin error:', err);
                    if (isCheckedIn) {
                        btn.classList.add('text-green-600');
                    } else {
                        btn.classList.remove('text-green-600');
                    }
                    countSpan.textContent = currentCount;
                });
        }

        // Toggle Resonance Box
        function toggleResonanceBox(postId) {
            const box = document.getElementById(`resonance-box-${postId}`);
            box.classList.toggle("hidden");
        }

        // Submit Resonance
        function submitResonance(postId) {
            const input = document.getElementById(`resonance-input-${postId}`);
            const content = input.value.trim();
            if (!content) return;

            fetch(`/timeline/${postId}/resonance`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ content })
            })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        const list = document.getElementById(`resonance-list-${postId}`);
                        if (list) {
                            const emptyPlaceholder = list.querySelector('.italic');
                            if (emptyPlaceholder) emptyPlaceholder.remove();

                            const res = data.resonance;
                            const authorBadgeHtml = res.is_post_author ? `<span class="px-1.5 py-0.2 rounded-md bg-amber-500/10 text-amber-600 dark:text-amber-400 text-[10px] font-black uppercase tracking-wider shrink-0 border border-amber-500/20">Author</span>` : '';
                            const newDivHtml = `
                                <div id="resonance-item-${res.id}" class="p-2.5 sm:p-3 bg-stone-50 dark:bg-stone-800/60 rounded-xl border border-stone-100 dark:border-stone-800 text-xs sm:text-sm space-y-1.5 transition-all">
                                    <div class="flex items-center justify-between gap-2">
                                        <div class="flex items-center gap-2 min-w-0">
                                            <img src="${res.user.profile_photo_url}" class="w-6 h-6 rounded-full object-cover border border-stone-200 dark:border-stone-700 shrink-0" alt="${res.user.name}">
                                            <div class="flex items-center gap-1.5 min-w-0 truncate">
                                                <span class="font-bold text-stone-900 dark:text-stone-100 truncate">${res.user.name}</span>
                                                ${authorBadgeHtml}
                                            </div>
                                        </div>
                                        <span class="text-[10px] sm:text-xs text-stone-400 shrink-0">${res.time}</span>
                                    </div>
                                    <div class="text-stone-700 dark:text-stone-300 leading-relaxed pl-8">
                                        ${res.formatted_content || res.content}
                                    </div>
                                    <div class="flex items-center justify-between pt-1 pl-8">
                                        <div class="flex items-center gap-3">
                                            <button type="button" 
                                                onclick="toggleResonanceTap(${res.id})" 
                                                data-resonance-tap-btn="${res.id}"
                                                class="flex items-center gap-1 text-[11px] sm:text-xs font-semibold text-stone-500 dark:text-stone-400 hover:text-rose-600 dark:hover:text-rose-400 transition active:scale-90"
                                                title="React to comment">
                                                <i data-lucide="heart" class="w-3.5 h-3.5"></i>
                                                <span data-resonance-tap-count="${res.id}"></span>
                                            </button>
                                            <button type="button" 
                                                onclick="toggleResonanceReplyBox(${postId}, ${res.id}, '${res.user.name.replace(/'/g, "\\'")}')"
                                                class="flex items-center gap-1 text-[11px] sm:text-xs font-semibold text-stone-500 dark:text-stone-400 hover:text-amber-600 dark:hover:text-amber-400 transition"
                                                title="Reply to this comment">
                                                <i data-lucide="reply" class="w-3.5 h-3.5"></i>
                                                <span>Reply</span>
                                            </button>
                                        </div>
                                        <div data-resonance-author-badge="${res.id}" class="hidden items-center gap-1 text-[10px] font-bold text-amber-600 dark:text-amber-400 bg-amber-500/10 px-2 py-0.5 rounded-full border border-amber-500/20">
                                            <i data-lucide="heart" class="w-2.5 h-2.5 fill-current text-rose-500"></i>
                                            <span>Liked by author</span>
                                        </div>
                                    </div>
                                    <div id="resonance-reply-form-${res.id}" class="hidden pt-2 pl-8">
                                        <div class="flex items-center gap-1.5 bg-white dark:bg-stone-900 border border-stone-200 dark:border-stone-700 rounded-xl p-1.5 shadow-sm">
                                            <input type="text" id="resonance-reply-input-${res.id}" placeholder="Reply to ${res.user.name}..." class="flex-1 bg-transparent px-2 text-xs text-stone-800 dark:text-stone-200 outline-none" onkeydown="if(event.key === 'Enter') submitResonanceReply(${postId}, ${res.id})">
                                            <button type="button" onclick="submitResonanceReply(${postId}, ${res.id})" class="p-1.5 bg-amber-500 text-white rounded-lg hover:bg-amber-600 transition active:scale-95"><i data-lucide="send" class="w-3 h-3"></i></button>
                                            <button type="button" onclick="toggleResonanceReplyBox(${postId}, ${res.id})" class="p-1.5 text-stone-400 hover:text-stone-600 dark:hover:text-stone-200 rounded-lg transition"><i data-lucide="x" class="w-3 h-3"></i></button>
                                        </div>
                                    </div>
                                    <div id="resonance-replies-list-${res.id}" class="space-y-2 mt-2 pl-6 sm:pl-8 border-l-2 border-stone-200 dark:border-stone-700 hidden"></div>
                                </div>
                            `;
                            list.insertAdjacentHTML('beforeend', newDivHtml);
                        }
                        input.value = '';
                        const countSpan = document.querySelector(`[data-resonance-count="${postId}"]`);
                        if (countSpan) countSpan.innerText = `${data.count} Resonance`;
                        if (typeof lucide !== 'undefined') lucide.createIcons();
                    }
                })
                .catch(err => console.error('Error submitting resonance:', err));
        }

        // Toggle Tap / Heart Reaction on a Resonance
        function toggleResonanceTap(resonanceId) {
            const btn = document.querySelector(`[data-resonance-tap-btn="${resonanceId}"]`);
            const countSpan = document.querySelector(`[data-resonance-tap-count="${resonanceId}"]`);
            const authorBadge = document.querySelector(`[data-resonance-author-badge="${resonanceId}"]`);
            if (!btn) return;

            const icon = btn.querySelector('i, svg');
            let isTapped = btn.classList.contains('text-rose-600');
            let currentCount = parseInt(countSpan ? countSpan.textContent : '0') || 0;

            // Optimistic UI update
            if (isTapped) {
                btn.classList.remove('text-rose-600', 'dark:text-rose-400');
                btn.classList.add('text-stone-500', 'dark:text-stone-400');
                if (icon) icon.classList.remove('fill-current');
                if (countSpan) countSpan.textContent = Math.max(0, currentCount - 1) || '';
            } else {
                btn.classList.add('text-rose-600', 'dark:text-rose-400');
                btn.classList.remove('text-stone-500', 'dark:text-stone-400');
                if (icon) icon.classList.add('fill-current');
                if (countSpan) countSpan.textContent = currentCount + 1;
            }

            fetch(`/resonances/${resonanceId}/tap`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                }
            })
            .then(res => res.json())
            .then(data => {
                if (countSpan) {
                    countSpan.textContent = data.count > 0 ? data.count : '';
                }
                if (authorBadge) {
                    if (data.is_author_liked) {
                        authorBadge.classList.remove('hidden');
                        authorBadge.classList.add('flex');
                    } else {
                        authorBadge.classList.add('hidden');
                        authorBadge.classList.remove('flex');
                    }
                }
                if (typeof lucide !== 'undefined') lucide.createIcons();
            })
            .catch(err => {
                console.error('Error tapping resonance:', err);
                if (isTapped) {
                    btn.classList.add('text-rose-600', 'dark:text-rose-400');
                    if (icon) icon.classList.add('fill-current');
                    if (countSpan) countSpan.textContent = currentCount || '';
                } else {
                    btn.classList.remove('text-rose-600', 'dark:text-rose-400');
                    if (icon) icon.classList.remove('fill-current');
                    if (countSpan) countSpan.textContent = Math.max(0, currentCount - 1) || '';
                }
            });
        }

        // Toggle Reply Box for a Resonance
        function toggleResonanceReplyBox(postId, resonanceId, authorName = '') {
            const form = document.getElementById(`resonance-reply-form-${resonanceId}`);
            if (!form) return;
            const isHidden = form.classList.contains('hidden');
            form.classList.toggle('hidden');
            if (isHidden) {
                const input = document.getElementById(`resonance-reply-input-${resonanceId}`);
                if (input) {
                    if (authorName && !input.value) {
                        input.value = `@${authorName.replace(/\s+/g, '')} `;
                    }
                    input.focus();
                }
            }
        }

        // Submit Threaded Reply to a Resonance
        function submitResonanceReply(postId, resonanceId) {
            const input = document.getElementById(`resonance-reply-input-${resonanceId}`);
            if (!input) return;
            const content = input.value.trim();
            if (!content) return;

            fetch(`/timeline/${postId}/resonance`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ content, parent_id: resonanceId })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    const repliesList = document.getElementById(`resonance-replies-list-${resonanceId}`);
                    if (repliesList) {
                        repliesList.classList.remove('hidden');
                        const reply = data.resonance;
                        const authorBadgeHtml = reply.is_post_author ? `<span class="px-1 py-0.2 rounded bg-amber-500/10 text-amber-600 dark:text-amber-400 text-[9px] font-black uppercase tracking-wider shrink-0 border border-amber-500/20">Author</span>` : '';
                        const replyHtml = `
                            <div id="resonance-item-${reply.id}" class="p-2 bg-white/70 dark:bg-stone-900/60 rounded-xl border border-stone-100 dark:border-stone-800 text-xs space-y-1">
                                <div class="flex items-center justify-between gap-2">
                                    <div class="flex items-center gap-1.5 min-w-0">
                                        <img src="${reply.user.profile_photo_url}" class="w-5 h-5 rounded-full object-cover border border-stone-200 dark:border-stone-700 shrink-0" alt="${reply.user.name}">
                                        <span class="font-bold text-stone-900 dark:text-stone-100 truncate">${reply.user.name}</span>
                                        ${authorBadgeHtml}
                                    </div>
                                    <span class="text-[9px] sm:text-[10px] text-stone-400 shrink-0">${reply.time}</span>
                                </div>
                                <div class="text-stone-700 dark:text-stone-300 leading-relaxed pl-6.5">
                                    ${reply.formatted_content || reply.content}
                                </div>
                                <div class="flex items-center justify-between pt-0.5 pl-6.5">
                                    <button type="button" 
                                        onclick="toggleResonanceTap(${reply.id})" 
                                        data-resonance-tap-btn="${reply.id}"
                                        class="flex items-center gap-1 text-[10px] font-semibold text-stone-400 hover:text-rose-600 dark:hover:text-rose-400 transition active:scale-90"
                                        title="React to reply">
                                        <i data-lucide="heart" class="w-3 h-3"></i>
                                        <span data-resonance-tap-count="${reply.id}"></span>
                                    </button>
                                    <div data-resonance-author-badge="${reply.id}" class="hidden items-center gap-0.5 text-[9px] font-bold text-amber-600 dark:text-amber-400 bg-amber-500/10 px-1.5 py-0.2 rounded-full border border-amber-500/20">
                                        <i data-lucide="heart" class="w-2 h-2 fill-current text-rose-500"></i>
                                        <span>Liked by author</span>
                                    </div>
                                </div>
                            </div>
                        `;
                        repliesList.insertAdjacentHTML('beforeend', replyHtml);
                    }
                    input.value = '';
                    toggleResonanceReplyBox(postId, resonanceId);
                    const countSpan = document.querySelector(`[data-resonance-count="${postId}"]`);
                    if (countSpan) countSpan.innerText = `${data.count} Resonance`;
                    if (typeof lucide !== 'undefined') lucide.createIcons();
                }
            })
            .catch(err => console.error('Error replying to resonance:', err));
        }

        // Post Translation Handlers
        const postTranslationsCache = {};

        function togglePostTranslation(postId, targetLanguage = 'English') {
            const originalContainer = document.getElementById(`post-original-content-${postId}`);
            const translatedContainer = document.getElementById(`post-translated-content-${postId}`);
            const translateBtnText = document.getElementById(`btn-translate-text-${postId}`);
            const loader = document.getElementById(`translation-loader-${postId}`);

            if (!originalContainer || !translatedContainer) return;

            // If translated container is currently visible and user clicked the same language/toggle button, toggle back to original
            const isCurrentlyTranslated = !translatedContainer.classList.contains('hidden');
            const currentLang = translatedContainer.getAttribute('data-current-lang');

            if (isCurrentlyTranslated && currentLang === targetLanguage) {
                showOriginalPostContent(postId);
                return;
            }

            // Check client-side cache
            if (postTranslationsCache[postId] && postTranslationsCache[postId][targetLanguage]) {
                renderPostTranslation(postId, postTranslationsCache[postId][targetLanguage], targetLanguage);
                return;
            }

            // Show loading indicator
            if (loader) {
                loader.classList.remove('hidden');
                loader.classList.add('inline-flex');
            }

            fetch(`/timeline/${postId}/translate`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ target_language: targetLanguage })
            })
            .then(res => {
                if (!res.ok) throw new Error(`HTTP error! status: ${res.status}`);
                return res.json();
            })
            .then(data => {
                if (!postTranslationsCache[postId]) {
                    postTranslationsCache[postId] = {};
                }
                postTranslationsCache[postId][targetLanguage] = data;
                renderPostTranslation(postId, data, targetLanguage);
            })
            .catch(err => {
                console.error('Translation error:', err);
                alert('Could not translate story. Please try again.');
            })
            .finally(() => {
                if (loader) {
                    loader.classList.add('hidden');
                    loader.classList.remove('inline-flex');
                }
            });
        }

        function renderPostTranslation(postId, data, targetLanguage) {
            const originalContainer = document.getElementById(`post-original-content-${postId}`);
            const translatedContainer = document.getElementById(`post-translated-content-${postId}`);
            const translateBtnText = document.getElementById(`btn-translate-text-${postId}`);

            if (!originalContainer || !translatedContainer) return;

            const contentHtml = data.formatted_translated_content || data.translated_text || '';
            const detectedSource = data.source_language ? `from ${data.source_language} ` : '';

            translatedContainer.innerHTML = `
                <div class="flex items-center justify-between pb-2 mb-2 border-b border-amber-500/20 text-xs">
                    <div class="flex items-center gap-1.5 text-amber-700 dark:text-amber-400 font-bold">
                        <i data-lucide="sparkles" class="w-3.5 h-3.5"></i>
                        <span>Translated ${detectedSource}to <strong>${targetLanguage}</strong></span>
                    </div>
                    <button type="button" 
                        onclick="showOriginalPostContent(${postId})" 
                        class="text-[11px] text-amber-700 dark:text-amber-300 hover:underline font-semibold flex items-center gap-1">
                        <i data-lucide="undo-2" class="w-3 h-3"></i>
                        <span>Show original</span>
                    </button>
                </div>
                <div class="leading-relaxed text-stone-800 dark:text-stone-100">
                    ${contentHtml}
                </div>
            `;

            translatedContainer.setAttribute('data-current-lang', targetLanguage);
            originalContainer.classList.add('hidden');
            translatedContainer.classList.remove('hidden');

            if (translateBtnText) {
                translateBtnText.innerText = 'Show Original';
            }

            if (typeof lucide !== 'undefined') {
                lucide.createIcons();
            }
        }

        function showOriginalPostContent(postId) {
            const originalContainer = document.getElementById(`post-original-content-${postId}`);
            const translatedContainer = document.getElementById(`post-translated-content-${postId}`);
            const translateBtnText = document.getElementById(`btn-translate-text-${postId}`);

            if (!originalContainer || !translatedContainer) return;

            translatedContainer.classList.add('hidden');
            originalContainer.classList.remove('hidden');

            if (translateBtnText) {
                translateBtnText.innerText = 'See Translation';
            }

            if (typeof lucide !== 'undefined') {
                lucide.createIcons();
            }
        }

    </script>
@endpush