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
            {{-- AI suggested users and authentic stories are now embedded seamlessly inside the timeline feed --}}
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
                            @if($post->sharedPost && $post->sharedPost->user)
                                <div class="flex items-center gap-1.5 text-[11px] sm:text-xs font-bold text-amber-600 dark:text-amber-400 mb-2.5 pb-2 border-b border-stone-100 dark:border-stone-800/80">
                                    <i data-lucide="repeat-2" class="w-3.5 h-3.5"></i>
                                    <span>Reposted from</span>
                                    <a href="{{ route('profile.user', $post->sharedPost->user) }}" class="hover:underline">
                                        {{ $post->sharedPost->user->name }} ({{ '@' . $post->sharedPost->user->username }})
                                    </a>
                                </div>
                            @endif

                            <!-- Header -->
                            <div class="flex items-start justify-between gap-3 mb-3">
                                <div class="flex items-start gap-2.5 sm:gap-3 min-w-0 flex-1">
                                    <!-- User Avatar -->
                                    <a href="{{ route('profile.user', $post->user) }}" class="flex-shrink-0 mt-0.5">
                                        <img src="{{ $post->user->profile_photo_url }}"
                                            alt="{{ $post->user->name }}"
                                            class="w-10 h-10 rounded-full object-cover border border-stone-100 dark:border-stone-800 shadow-sm"
                                            loading="lazy">
                                    </a>

                                    <!-- User & Post Meta Details -->
                                    <div class="min-w-0 flex-1">
                                        <div class="flex flex-wrap items-center gap-x-2 gap-y-0.5 text-xs sm:text-sm">
                                            <a href="{{ route('profile.user', $post->user) }}"
                                                class="font-semibold text-stone-800 dark:text-stone-200 hover:text-amber-600 transition truncate max-w-[130px] sm:max-w-none">
                                                {{ $post->user->name }}
                                            </a>
                                            <span class="text-stone-400 text-xs sm:text-sm">@ {{ $post->user->username }}</span>

                                            @include('timeline.partials.post-tagged-badge', ['post' => $post])

                                            @if($post->user->location)
                                                <span
                                                    class="text-[10px] sm:text-xs bg-stone-100 dark:bg-stone-800 text-stone-600 dark:text-stone-400 px-2 py-0.5 rounded-full">
                                                    {{ $post->user->location }}
                                                </span>
                                            @endif
                                        </div>

                                        <div class="flex flex-wrap items-center gap-x-2 gap-y-1 text-xs text-stone-500 dark:text-stone-400 mt-1">
                                            <!-- Meta Info -->
                                            <span class="flex items-center space-x-1">
                                                <i data-lucide="clock" class="w-3 h-3 text-stone-400"></i>
                                                <span>{{ $post->time_ago }}</span>
                                            </span>

                                            @if($post->location)
                                                <span class="text-stone-300 dark:text-stone-700">•</span>
                                                <span class="flex items-center space-x-1">
                                                    <i data-lucide="map-pin" class="w-3 h-3 text-stone-400"></i>
                                                    <span>{{ $post->location }}</span>
                                                </span>
                                            @endif

                                            @if($post->chapter)
                                                <span class="text-stone-300 dark:text-stone-700">•</span>
                                                <span
                                                    class="bg-amber-100 dark:bg-amber-950/60 text-amber-800 dark:text-amber-300 px-2 py-0.5 rounded-full text-[10px] sm:text-xs font-medium border border-amber-200/60 dark:border-amber-900/40">
                                                    {{ $post->chapter }}
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                </div>

                                <!-- Right: Fixed Top Side Actions & Privacy -->
                                <div class="flex items-center gap-1.5 flex-shrink-0 self-start mt-0.5">
                                    @if($post->privacy === 'private')
                                        <i data-lucide="lock" class="w-4 h-4 text-stone-400" title="Private story"></i>
                                    @elseif($post->privacy === 'vault')
                                        <i data-lucide="shield" class="w-4 h-4 text-amber-500" title="Personal vault"></i>
                                    @endif

                                    @auth
                                        @if(auth()->id() === $post->user_id || auth()->user()->is_admin)
                                            <div class="relative" x-data="{ openMenu: false }">
                                                <button type="button" 
                                                    @click="openMenu = !openMenu" 
                                                    class="p-1.5 rounded-xl text-stone-400 hover:text-stone-700 dark:hover:text-stone-200 hover:bg-stone-100 dark:hover:bg-stone-800 transition -mr-1" 
                                                    title="Post options">
                                                    <i data-lucide="more-horizontal" class="w-4 h-4 sm:w-5 sm:h-5"></i>
                                                </button>
                                                <div x-show="openMenu" 
                                                    @click.away="openMenu = false" 
                                                    x-cloak 
                                                    class="absolute right-0 top-full mt-1 z-30 w-36 bg-white dark:bg-stone-900 rounded-2xl shadow-xl border border-stone-200 dark:border-stone-800 py-1.5 text-xs">
                                                    <button type="button" 
                                                        @click="openMenu = false; openEditTimelinePostModal({{ $post->id }})" 
                                                        class="w-full text-left px-3.5 py-1.5 hover:bg-amber-500/10 hover:text-amber-600 text-stone-700 dark:text-stone-300 font-medium transition flex items-center gap-2">
                                                        <i data-lucide="pencil" class="w-3.5 h-3.5 text-amber-500"></i>
                                                        <span>Edit Story</span>
                                                    </button>
                                                    <button type="button" 
                                                        @click="openMenu = false; confirmDeleteTimelinePost({{ $post->id }})" 
                                                        class="w-full text-left px-3.5 py-1.5 hover:bg-red-500/10 hover:text-red-600 text-red-600 dark:text-red-400 font-medium transition flex items-center gap-2">
                                                        <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                                        <span>Delete</span>
                                                    </button>
                                                </div>
                                            </div>
                                        @endif
                                    @endauth
                                </div>
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

                                @php
                                    $postYoutubeId = $post->video ? \App\Models\CulturalClass::extractYoutubeId($post->video) : null;
                                    $postImageSrc = \App\Models\CulturalClass::resolveMediaUrl($post->image);
                                    $postVideoSrc = \App\Models\CulturalClass::resolveMediaUrl($post->video);
                                @endphp

                                @if($postImageSrc && !$postVideoSrc && !$postYoutubeId)
                                    <div class="-mx-4 sm:-mx-6 mt-4 relative overflow-hidden bg-stone-900/5 dark:bg-black/40 border-y border-stone-100 dark:border-stone-800 flex items-center justify-center min-h-[220px] max-h-[720px] group cursor-pointer"
                                         onclick="openImageLightbox('{{ $postImageSrc }}', 'Story photo by {{ addslashes($post->user->name) }}')">
                                        {{-- Ambient blurred backdrop for portrait / non-full-width images --}}
                                        <div class="absolute inset-0 bg-cover bg-center filter blur-xl opacity-25 dark:opacity-20 scale-110 pointer-events-none"
                                             style="background-image: url('{{ $postImageSrc }}');"></div>
                                        
                                        {{-- Main Sharp Image: Keeps true aspect ratio (portrait or landscape) without cropping --}}
                                        <img src="{{ $postImageSrc }}"
                                            alt="Story image"
                                            class="relative z-10 w-auto h-auto max-w-full max-h-[680px] object-contain mx-auto transition-transform duration-300 group-hover:scale-[1.01]"
                                            loading="lazy">
                                            
                                        {{-- View Full / Expand Indicator on Hover --}}
                                        <span class="absolute bottom-3 right-3 z-20 px-2.5 py-1 rounded-lg bg-black/65 backdrop-blur-md text-white text-[11px] font-medium opacity-0 group-hover:opacity-100 transition-opacity duration-200 flex items-center gap-1.5 pointer-events-none">
                                            <i data-lucide="maximize-2" class="w-3.5 h-3.5"></i>
                                            <span>View full</span>
                                        </span>
                                    </div>
                                @endif

                                @if($postYoutubeId)
                                    <div class="-mx-4 sm:-mx-6 mt-4 relative overflow-hidden bg-black border-y border-stone-100 dark:border-stone-800 aspect-video">
                                        <iframe src="https://www.youtube.com/embed/{{ $postYoutubeId }}?rel=0&modestbranding=1"
                                                class="w-full h-full"
                                                frameborder="0"
                                                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                                                allowfullscreen></iframe>
                                    </div>
                                @elseif($postVideoSrc)
                                    <div class="-mx-4 sm:-mx-6 mt-4 relative overflow-hidden bg-black border-y border-stone-100 dark:border-stone-800 flex items-center justify-center min-h-[220px]">
                                        <video src="{{ $postVideoSrc }}{{ !str_contains($postVideoSrc, '#') ? '#t=0.1' : '' }}"
                                               controls
                                               preload="metadata"
                                               class="w-auto h-auto max-w-full max-h-[650px] mx-auto"></video>
                                    </div>
                                @endif

                                @if($post->audio)
                                    <div class="mt-4 p-3 bg-stone-100 dark:bg-stone-800 rounded-xl">
                                        <audio src="{{ Storage::url($post->audio) }}" controls class="w-full"></audio>
                                    </div>
                                @endif

                                @include('timeline.partials.quoted-post-card', ['post' => $post])
                            </div>

                            <!-- Actions -->
                            @php
                                $viewerCanRepostAndTag = auth()->check() && $post->canRepostAndTag(auth()->user());
                                $viewerIsOwner = auth()->check() && $post->isOwnedBy(auth()->user());
                                $postTaggedJson = $post->tagged_users_models->map(fn ($u) => [
                                    'id' => $u->id,
                                    'name' => $u->name,
                                    'username' => $u->username,
                                    'avatar' => $u->profile_photo_url,
                                ])->values();
                            @endphp
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
                                            data-resonance-count="{{ $post->id }}">{{ $post->resonances->count() }}</span>
                                    </button>

                                    <!-- Check-In -->
                                    <button onclick="toggleCheckin({{ $post->id }})" data-checkin-post="{{ $post->id }}"
                                        class="flex items-center space-x-1 sm:space-x-2 px-2 sm:px-3 py-1 sm:py-1.5 rounded-lg transition {{ auth()->user()->hasCheckedIn($post) ? 'text-green-600' : 'text-stone-600 hover:bg-stone-100' }}">
                                        <i data-lucide="check-circle" class="w-3 h-3 sm:w-4 sm:h-4"></i>
                                        <span class="font-medium" data-checkin-count="{{ $post->id }}">
                                            {{ $post->check_ins_count }}
                                        </span>
                                    </button>

                                    <!-- Tag People (Owner & Locked-In members can tag; others prompted to Lock-In) -->
                                    <button type="button"
                                        onclick='openTagPeopleModal({{ $post->id }}, {{ $viewerCanRepostAndTag ? "true" : "false" }}, {{ $viewerIsOwner ? "true" : "false" }}, {{ $post->user_id }}, "{{ addslashes($post->user->name) }}", "{{ addslashes($post->user->username) }}", @json($postTaggedJson))'
                                        data-tag-btn-post="{{ $post->id }}"
                                        class="flex items-center space-x-1 sm:space-x-2 px-2 sm:px-3 py-1 sm:py-1.5 rounded-lg transition {{ $post->tagged_users_models->count() > 0 ? 'text-amber-600 dark:text-amber-400' : 'text-stone-600 hover:bg-stone-100' }}"
                                        title="{{ $viewerCanRepostAndTag ? 'Tag people on this story' : 'Lock-In to @' . $post->user->username . ' to tag people' }}">
                                        <i data-lucide="user-plus" class="w-3 h-3 sm:w-4 sm:h-4"></i>
                                        <span class="font-medium" data-tag-count="{{ $post->id }}">{{ $post->tagged_users_models->count() > 0 ? $post->tagged_users_models->count() : '' }}</span>
                                    </button>

                                    <!-- Share / Repost -->
                                    <button type="button"
                                        onclick="openShareModal('{{ url('/timeline/' . $post->id) }}', {{ $post->id }}, {{ $viewerCanRepostAndTag ? 'true' : 'false' }}, {{ $post->user_id }}, '{{ addslashes($post->user->name) }}', '{{ addslashes($post->user->username) }}')"
                                        data-share-btn-post="{{ $post->id }}"
                                        class="flex items-center space-x-1 sm:space-x-2 px-2 sm:px-3 py-1 sm:py-1.5 rounded-lg transition text-stone-600 hover:bg-stone-100"
                                        title="Share or Repost story">
                                        <i data-lucide="share-2" class="w-3 h-3 sm:w-4 sm:h-4"></i>
                                        <span class="font-medium" data-share-count="{{ $post->id }}">{{ ($post->shares_count ?? 0) > 0 ? $post->shares_count : '' }}</span>
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
                                        <input type="text" id="resonance-input-{{ $post->id }}"
                                            placeholder="{{ $viewerCanRepostAndTag ? 'Write a resonance... (use @username to tag)' : 'Write a resonance... (Lock-In to @' . $post->user->username . ' to tag @members)' }}"
                                            class="flex-1 border dark:border-stone-700 bg-white dark:bg-stone-900 dark:text-stone-200 rounded-lg px-2 sm:px-3 py-1.5 sm:py-2 text-xs sm:text-sm focus:ring-2 focus:ring-amber-500 focus:outline-none"
                                            onkeydown="if(event.key === 'Enter') submitResonance({{ $post->id }})">
                                        <button onclick="submitResonance({{ $post->id }})"
                                            class="p-2 sm:p-3 bg-amber-500 text-white rounded-lg hover:bg-amber-600 transition flex items-center justify-center">
                                            <i data-lucide="send" class="w-4 h-4 sm:w-5 sm:h-5"></i>
                                        </button>
                                    </div>
                                    <div id="resonance-lockin-notice-{{ $post->id }}" class="hidden mt-2 p-2.5 rounded-xl bg-amber-500/10 border border-amber-500/30 text-xs text-amber-800 dark:text-amber-300 items-center justify-between gap-2">
                                        <span id="resonance-lockin-text-{{ $post->id }}">You must Lock-In to {{ '@' . $post->user->username }} to tag members on their story.</span>
                                        <button type="button"
                                                onclick="quickLockInAuthor({{ $post->user_id }}, {{ $post->id }})"
                                                class="px-3 py-1 rounded-lg bg-amber-500 hover:bg-amber-600 text-white font-bold text-[11px] shrink-0 transition cursor-pointer">
                                            Lock-In to {{ '@' . $post->user->username }}
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

    <!-- Share & Repost Modal -->
    <div id="shareModal"
        class="fixed inset-0 bg-black/60 backdrop-blur-sm hidden items-center justify-center z-50 p-3 sm:p-4 animate-backdrop-fade">
        <div
            class="animate-modal-pop-up bg-white dark:bg-stone-900 rounded-2xl shadow-2xl w-full max-w-md p-4 sm:p-6 relative text-xs sm:text-sm border border-stone-200 dark:border-stone-800 max-h-[90vh] overflow-y-auto">
            <!-- Close Button -->
            <button type="button" onclick="closeShareModal()"
                class="absolute top-3 right-3 text-stone-400 hover:text-stone-700 dark:hover:text-stone-200 p-1 rounded-lg">
                <i data-lucide="x" class="w-4 h-4 sm:w-5 sm:h-5"></i>
            </button>

            <!-- Header -->
            <h2 class="text-base sm:text-lg font-bold mb-3 sm:mb-4 flex items-center gap-2 text-stone-800 dark:text-stone-100">
                <i data-lucide="share-2" class="w-4 h-4 sm:w-5 sm:h-5 text-amber-500"></i>
                <span>Share Story</span>
            </h2>

            @auth
                <!-- Section A: Repost to Kulturegram Timeline (Owner & Locked-In Members) -->
                <div id="shareRepostUnlockedSection" class="mb-4 pb-4 border-b border-stone-100 dark:border-stone-800 space-y-3">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold uppercase tracking-wider text-amber-600 dark:text-amber-400 flex items-center gap-1.5">
                            <i data-lucide="repeat-2" class="w-3.5 h-3.5"></i>
                            <span>Repost to Your Kulturegram Timeline</span>
                        </span>
                    </div>

                    <textarea id="repostQuoteInput"
                              rows="2"
                              placeholder="Add your thoughts or cultural reflection... (optional, use @username to tag)"
                              class="w-full rounded-xl border border-stone-200 dark:border-stone-700 bg-stone-50 dark:bg-stone-950 p-2.5 text-xs sm:text-sm text-stone-800 dark:text-stone-100 focus:ring-2 focus:ring-amber-500 outline-none resize-none"></textarea>

                    <!-- Tag People on Repost -->
                    <div class="space-y-1.5 relative">
                        <label class="block text-[11px] font-bold text-stone-600 dark:text-stone-400">
                            Tag People on Your Repost (Optional)
                        </label>
                        <div id="repostSelectedTags" class="flex flex-wrap gap-1.5 empty:hidden"></div>
                        <div class="relative">
                            <i data-lucide="user-plus" class="w-3.5 h-3.5 text-stone-400 absolute left-3 top-2.5"></i>
                            <input type="text"
                                   id="repostTagSearchInput"
                                   onfocus="searchShareTagUsers(this.value)"
                                   oninput="searchShareTagUsers(this.value)"
                                   placeholder="Search Locked-In connections or @username..."
                                   class="w-full rounded-xl border border-stone-200 dark:border-stone-700 bg-stone-50 dark:bg-stone-950 pl-8 pr-3 py-1.5 text-xs text-stone-800 dark:text-stone-100 focus:ring-2 focus:ring-amber-500 outline-none">
                        </div>
                        <div id="repostTagDropdown" class="hidden max-h-36 overflow-y-auto rounded-xl border border-stone-200 dark:border-stone-700 bg-white dark:bg-stone-900 shadow-lg p-1 space-y-0.5"></div>
                    </div>

                    <button type="button"
                            id="submitRepostBtn"
                            onclick="submitRepost()"
                            class="w-full py-2.5 px-4 rounded-xl bg-gradient-to-r from-amber-500 to-orange-600 hover:from-amber-600 hover:to-orange-700 text-white font-bold text-xs sm:text-sm shadow-md transition flex items-center justify-center gap-2 cursor-pointer">
                        <i data-lucide="repeat-2" class="w-4 h-4"></i>
                        <span>Repost to My Timeline</span>
                    </button>
                </div>

                <!-- Section B: Lock-In Required Gate (For Non-Locked-In Members) -->
                <div id="shareRepostLockedGate" class="hidden mb-4 pb-4 border-b border-stone-100 dark:border-stone-800">
                    <div class="p-3.5 rounded-2xl bg-amber-500/10 border border-amber-500/30 space-y-2.5">
                        <div class="flex items-start gap-2.5">
                            <div class="w-8 h-8 rounded-xl bg-amber-500 text-white flex items-center justify-center shrink-0 mt-0.5">
                                <i data-lucide="lock" class="w-4 h-4"></i>
                            </div>
                            <div>
                                <h4 class="font-bold text-xs sm:text-sm text-stone-900 dark:text-white">Lock-In to Repost & Tag</h4>
                                <p id="shareLockedGateDesc" class="text-[11px] text-stone-600 dark:text-stone-300 leading-relaxed mt-0.5">
                                    Only members who are Locked-In to this story's author can repost it to their Kulturegram Timeline or tag members on it. You can still share the link externally below.
                                </p>
                            </div>
                        </div>
                        <button type="button"
                                id="shareModalLockInBtn"
                                onclick="lockInFromShareModal()"
                                class="w-full py-2 px-3 rounded-xl bg-amber-500 hover:bg-amber-600 text-white font-bold text-xs transition flex items-center justify-center gap-1.5 cursor-pointer">
                            <i data-lucide="user-check" class="w-3.5 h-3.5"></i>
                            <span id="shareModalLockInBtnText">Lock-In to Author to Unlock Repost</span>
                        </button>
                    </div>
                </div>
            @endauth

            <!-- Section C: External Share Options (Available to All on Public Posts) -->
            <div class="space-y-2">
                <div class="text-[11px] font-bold uppercase tracking-wider text-stone-400 mb-1">Share Externally</div>
                <button type="button" onclick="copyLink()"
                    class="flex items-center gap-2 sm:gap-3 w-full bg-stone-100 dark:bg-stone-800 hover:bg-stone-200 dark:hover:bg-stone-700 p-2.5 rounded-xl transition text-stone-700 dark:text-stone-200 cursor-pointer">
                    <i data-lucide="copy" class="w-4 h-4 text-stone-500 dark:text-stone-400"></i>
                    <span class="text-xs sm:text-sm font-medium">Copy Story Link</span>
                </button>

                <a id="wa-share" href="#" target="_blank"
                    class="flex items-center gap-2 sm:gap-3 w-full bg-green-500/10 hover:bg-green-500/20 text-green-700 dark:text-green-400 p-2.5 rounded-xl transition">
                    <i data-lucide="message-circle" class="w-4 h-4 text-green-600"></i>
                    <span class="text-xs sm:text-sm font-medium">Share on WhatsApp</span>
                </a>

                <a id="fb-share" href="#" target="_blank"
                    class="flex items-center gap-2 sm:gap-3 w-full bg-blue-500/10 hover:bg-blue-500/20 text-blue-700 dark:text-blue-400 p-2.5 rounded-xl transition">
                    <i data-lucide="facebook" class="w-4 h-4 text-blue-600"></i>
                    <span class="text-xs sm:text-sm font-medium">Share on Facebook</span>
                </a>

                <a id="tw-share" href="#" target="_blank"
                    class="flex items-center gap-2 sm:gap-3 w-full bg-sky-500/10 hover:bg-sky-500/20 text-sky-700 dark:text-sky-400 p-2.5 rounded-xl transition">
                    <i data-lucide="twitter" class="w-4 h-4 text-sky-600"></i>
                    <span class="text-xs sm:text-sm font-medium">Share on X / Twitter</span>
                </a>
            </div>
        </div>
    </div>

    <!-- Tag People on Story Modal -->
    @auth
    <div id="tagPeopleModal"
         class="fixed inset-0 bg-black/60 backdrop-blur-sm hidden items-center justify-center z-50 p-3 sm:p-4 animate-backdrop-fade">
        <div class="animate-modal-pop-up bg-white dark:bg-stone-900 rounded-2xl shadow-2xl w-full max-w-md p-4 sm:p-6 relative text-xs sm:text-sm border border-stone-200 dark:border-stone-800">
            <button type="button" onclick="closeTagPeopleModal()"
                    class="absolute top-3 right-3 text-stone-400 hover:text-stone-700 dark:hover:text-stone-200 p-1 rounded-lg">
                <i data-lucide="x" class="w-4 h-4 sm:w-5 sm:h-5"></i>
            </button>

            <h2 class="text-base sm:text-lg font-bold mb-1 flex items-center gap-2 text-stone-800 dark:text-stone-100">
                <i data-lucide="user-plus" class="w-4 h-4 sm:w-5 sm:h-5 text-amber-500"></i>
                <span>Tag People on Story</span>
            </h2>
            <p id="tagPeopleModalSubtitle" class="text-[11px] text-stone-500 dark:text-stone-400 mb-4">
                Tagged members will be notified and shown in the story header.
            </p>

            <!-- Unlocked Tag Picker (Owner & Locked-In Members) -->
            <div id="tagPeopleUnlockedSection" class="space-y-3">
                <div id="postModalSelectedTags" class="flex flex-wrap gap-1.5 min-h-[28px]"></div>

                <div class="relative">
                    <i data-lucide="search" class="w-3.5 h-3.5 text-stone-400 absolute left-3 top-2.5"></i>
                    <input type="text"
                           id="postModalTagSearchInput"
                           onfocus="searchPostModalTagUsers(this.value)"
                           oninput="searchPostModalTagUsers(this.value)"
                           placeholder="Search Locked-In members or @username..."
                           class="w-full rounded-xl border border-stone-200 dark:border-stone-700 bg-stone-50 dark:bg-stone-950 pl-8 pr-3 py-2 text-xs text-stone-800 dark:text-stone-100 focus:ring-2 focus:ring-amber-500 outline-none">
                </div>

                <div id="postModalTagDropdown" class="max-h-44 overflow-y-auto rounded-xl border border-stone-200 dark:border-stone-700 bg-stone-50/50 dark:bg-stone-950/60 p-1.5 space-y-1"></div>

                <div class="flex items-center justify-end gap-2 pt-2">
                    <button type="button" onclick="closeTagPeopleModal()"
                            class="px-3.5 py-2 rounded-xl text-xs font-bold text-stone-500 hover:bg-stone-100 dark:hover:bg-stone-800 transition cursor-pointer">
                        Cancel
                    </button>
                    <button type="button" id="savePostTagsBtn" onclick="submitPostTaggedUsers()"
                            class="px-4 py-2 rounded-xl bg-amber-500 hover:bg-amber-600 text-white text-xs font-bold shadow-md transition flex items-center gap-1.5 cursor-pointer">
                        <i data-lucide="check" class="w-3.5 h-3.5"></i>
                        <span>Save Tagged People</span>
                    </button>
                </div>
            </div>

            <!-- Locked Gate (Non-Locked-In Members) -->
            <div id="tagPeopleLockedGate" class="hidden">
                <div class="p-4 rounded-2xl bg-amber-500/10 border border-amber-500/30 space-y-3">
                    <div class="flex items-start gap-2.5">
                        <div class="w-8 h-8 rounded-xl bg-amber-500 text-white flex items-center justify-center shrink-0 mt-0.5">
                            <i data-lucide="lock" class="w-4 h-4"></i>
                        </div>
                        <div>
                            <h4 class="font-bold text-xs sm:text-sm text-stone-900 dark:text-white">Lock-In Required to Tag Members</h4>
                            <p id="tagPeopleLockedDesc" class="text-[11px] text-stone-600 dark:text-stone-300 leading-relaxed mt-0.5">
                                Only the story owner and members who have Locked-In to the author can tag people on this story.
                            </p>
                        </div>
                    </div>
                    <button type="button"
                            id="tagModalLockInBtn"
                            onclick="lockInFromTagModal()"
                            class="w-full py-2.5 px-4 rounded-xl bg-amber-500 hover:bg-amber-600 text-white font-bold text-xs transition flex items-center justify-center gap-1.5 cursor-pointer">
                        <i data-lucide="user-check" class="w-4 h-4"></i>
                        <span id="tagModalLockInBtnText">Lock-In to Author to Tag People</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endauth

@endsection

@push('scripts')
    <script>
        document.addEventListener("DOMContentLoaded", () => lucide.createIcons());

        let currentShareUrl = "";
        let currentSharePostId = null;
        let currentShareAuthorId = null;
        let currentShareAuthorName = "";
        let currentShareAuthorUsername = "";
        let shareSelectedTaggedUsers = [];

        let currentTagPostId = null;
        let currentTagIsOwner = false;
        let currentTagAuthorId = null;
        let currentTagAuthorName = "";
        let currentTagAuthorUsername = "";
        let postModalSelectedTaggedUsers = [];

        // Open Share & Repost modal
        function openShareModal(url, postId = null, canRepost = false, authorId = null, authorName = '', authorUsername = '') {
            currentShareUrl = url;
            currentSharePostId = postId;
            currentShareAuthorId = authorId;
            currentShareAuthorName = authorName;
            currentShareAuthorUsername = authorUsername;
            shareSelectedTaggedUsers = [];

            const quoteInput = document.getElementById('repostQuoteInput');
            if (quoteInput) quoteInput.value = '';
            const tagInput = document.getElementById('repostTagSearchInput');
            if (tagInput) tagInput.value = '';
            const tagDrop = document.getElementById('repostTagDropdown');
            if (tagDrop) tagDrop.classList.add('hidden');
            renderShareSelectedTags();

            const unlockedSection = document.getElementById('shareRepostUnlockedSection');
            const lockedGate = document.getElementById('shareRepostLockedGate');
            if (unlockedSection && lockedGate) {
                if (canRepost && postId) {
                    unlockedSection.classList.remove('hidden');
                    lockedGate.classList.add('hidden');
                } else if (postId) {
                    unlockedSection.classList.add('hidden');
                    lockedGate.classList.remove('hidden');
                    const desc = document.getElementById('shareLockedGateDesc');
                    if (desc) {
                        desc.textContent = `Only ${authorName} (@${authorUsername}) and members who Locked-In to them can repost this story to their Kulturegram Timeline or tag members on it.`;
                    }
                    const btnText = document.getElementById('shareModalLockInBtnText');
                    if (btnText) {
                        btnText.textContent = `Lock-In to @${authorUsername} to Repost & Tag`;
                    }
                }
            }

            document.getElementById("shareModal").classList.remove("hidden");
            document.getElementById("shareModal").classList.add("flex");

            document.getElementById("wa-share").href = `https://wa.me/?text=${encodeURIComponent(url)}`;
            document.getElementById("fb-share").href = `https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(url)}`;
            document.getElementById("tw-share").href = `https://twitter.com/intent/tweet?url=${encodeURIComponent(url)}`;
            if (typeof lucide !== 'undefined') lucide.createIcons();
        }

        // Close Share modal
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

        function searchShareTagUsers(query) {
            const dropdown = document.getElementById('repostTagDropdown');
            if (!dropdown) return;
            fetch('/timeline/taggable-users?q=' + encodeURIComponent(query || ''), {
                headers: { 'Accept': 'application/json' }
            })
            .then(r => r.json())
            .then(data => {
                if (!data.success) return;
                const available = data.users.filter(u => !shareSelectedTaggedUsers.some(s => s.id === u.id));
                if (available.length === 0) {
                    dropdown.classList.add('hidden');
                    return;
                }
                dropdown.innerHTML = available.map(u => `
                    <button type="button"
                            onclick='addShareTaggedUser(${JSON.stringify(u).replace(/'/g, "&#39;")})'
                            class="w-full flex items-center justify-between gap-2 p-1.5 rounded-lg hover:bg-amber-500/10 text-left transition cursor-pointer">
                        <div class="flex items-center gap-2 min-w-0">
                            <img src="${u.avatar}" class="w-6 h-6 rounded-full object-cover shrink-0" alt="">
                            <div class="min-w-0 truncate">
                                <div class="text-xs font-bold text-stone-800 dark:text-stone-100 truncate">${u.name}</div>
                                <div class="text-[10px] text-stone-400 truncate">@${u.username}</div>
                            </div>
                        </div>
                        ${u.is_locked_in ? '<span class="px-1.5 py-0.5 rounded-full bg-amber-500/15 text-amber-600 dark:text-amber-400 text-[9px] font-black uppercase shrink-0">Locked-In</span>' : ''}
                    </button>
                `).join('');
                dropdown.classList.remove('hidden');
            });
        }

        function addShareTaggedUser(user) {
            if (!shareSelectedTaggedUsers.some(u => u.id === user.id)) {
                shareSelectedTaggedUsers.push(user);
            }
            const input = document.getElementById('repostTagSearchInput');
            if (input) input.value = '';
            const dropdown = document.getElementById('repostTagDropdown');
            if (dropdown) dropdown.classList.add('hidden');
            renderShareSelectedTags();
        }

        function removeShareTaggedUser(userId) {
            shareSelectedTaggedUsers = shareSelectedTaggedUsers.filter(u => u.id !== userId);
            renderShareSelectedTags();
        }

        function renderShareSelectedTags() {
            const container = document.getElementById('repostSelectedTags');
            if (!container) return;
            container.innerHTML = shareSelectedTaggedUsers.map(u => `
                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-amber-500/10 border border-amber-500/30 text-amber-700 dark:text-amber-300 text-[11px] font-semibold">
                    <span>@${u.username}</span>
                    <button type="button" onclick="removeShareTaggedUser(${u.id})" class="hover:text-red-500 ml-0.5 cursor-pointer">&times;</button>
                </span>
            `).join('');
        }

        function submitRepost() {
            if (!currentSharePostId) return;
            const btn = document.getElementById('submitRepostBtn');
            const quoteInput = document.getElementById('repostQuoteInput');
            const content = quoteInput ? quoteInput.value.trim() : '';
            const taggedIds = shareSelectedTaggedUsers.map(u => u.id);

            if (btn) btn.disabled = true;

            fetch(`/timeline/${currentSharePostId}/repost`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    content: content,
                    tagged_users: taggedIds
                })
            })
            .then(async res => {
                const data = await res.json();
                return { status: res.status, data };
            })
            .then(({ status, data }) => {
                if (status === 403 && data.requires_lockin) {
                    document.getElementById('shareRepostUnlockedSection')?.classList.add('hidden');
                    document.getElementById('shareRepostLockedGate')?.classList.remove('hidden');
                    if (typeof window.showPlatformError === 'function') {
                        window.showPlatformError(data.error || 'Lock-In required to repost story.', 'Lock-In Required', {
                            label: 'Lock-In to @' + (data.author_username || 'Author'),
                            onClick: () => lockInFromShareModal()
                        });
                    }
                    return;
                }
                if (data.success) {
                    const shareCountEl = document.querySelector(`[data-share-count="${currentSharePostId}"]`);
                    if (shareCountEl) {
                        shareCountEl.textContent = data.shares_count > 0 ? data.shares_count : '';
                    }
                    closeShareModal();
                    window.location.reload();
                } else {
                    if (typeof window.showPlatformError === 'function') {
                    window.showPlatformError(data.error || 'Could not share story.', 'Unable to Share');
                } else {
                    alert(data.error || 'Could not share story.');
                }
                }
            })
            .catch(err => {
                console.error('Repost error:', err);
                if (typeof window.showPlatformError === 'function') {
                window.showPlatformError('Could not share story. Please check your connection and try again.', 'Unable to Share');
            } else {
                alert('Could not share story. Please try again.');
            }
            })
            .finally(() => {
                if (btn) btn.disabled = false;
            });
        }

        function lockInFromShareModal() {
            if (!currentShareAuthorId) return;
            const btn = document.getElementById('shareModalLockInBtn');
            if (btn) btn.disabled = true;

            fetch(`/users/${currentShareAuthorId}/lockin`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                }
            })
            .then(r => r.json())
            .then(data => {
                if (data.lockedIn) {
                    document.getElementById('shareRepostLockedGate')?.classList.add('hidden');
                    document.getElementById('shareRepostUnlockedSection')?.classList.remove('hidden');
                    if (typeof lucide !== 'undefined') lucide.createIcons();
                }
            })
            .finally(() => {
                if (btn) btn.disabled = false;
            });
        }

        // Open Tag People Modal
        function openTagPeopleModal(postId, canTag, isOwner, authorId, authorName, authorUsername, existingTags = []) {
            currentTagPostId = postId;
            currentTagIsOwner = isOwner;
            currentTagAuthorId = authorId;
            currentTagAuthorName = authorName;
            currentTagAuthorUsername = authorUsername;
            postModalSelectedTaggedUsers = Array.isArray(existingTags) ? [...existingTags] : [];

            const unlocked = document.getElementById('tagPeopleUnlockedSection');
            const locked = document.getElementById('tagPeopleLockedGate');
            if (unlocked && locked) {
                if (canTag) {
                    unlocked.classList.remove('hidden');
                    locked.classList.add('hidden');
                    renderPostModalSelectedTags();
                    searchPostModalTagUsers('');
                } else {
                    unlocked.classList.add('hidden');
                    locked.classList.remove('hidden');
                    const desc = document.getElementById('tagPeopleLockedDesc');
                    if (desc) {
                        desc.textContent = `Only ${authorName} (@${authorUsername}) and members who Locked-In to them can tag people on this story.`;
                    }
                    const btnText = document.getElementById('tagModalLockInBtnText');
                    if (btnText) {
                        btnText.textContent = `Lock-In to @${authorUsername} to Tag People`;
                    }
                }
            }

            const modal = document.getElementById('tagPeopleModal');
            if (modal) {
                modal.classList.remove('hidden');
                modal.classList.add('flex');
            }
            if (typeof lucide !== 'undefined') lucide.createIcons();
        }

        function closeTagPeopleModal() {
            const modal = document.getElementById('tagPeopleModal');
            if (modal) {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
            }
        }

        function searchPostModalTagUsers(query) {
            const dropdown = document.getElementById('postModalTagDropdown');
            if (!dropdown) return;
            fetch('/timeline/taggable-users?q=' + encodeURIComponent(query || ''), {
                headers: { 'Accept': 'application/json' }
            })
            .then(r => r.json())
            .then(data => {
                if (!data.success) return;
                const available = data.users.filter(u => !postModalSelectedTaggedUsers.some(s => s.id === u.id));
                if (available.length === 0) {
                    dropdown.innerHTML = `<div class="p-2 text-center text-[11px] text-stone-400">No matching members found.</div>`;
                    return;
                }
                dropdown.innerHTML = available.map(u => `
                    <button type="button"
                            onclick='addPostModalTaggedUser(${JSON.stringify(u).replace(/'/g, "&#39;")})'
                            class="w-full flex items-center justify-between gap-2 p-2 rounded-xl hover:bg-amber-500/10 text-left transition cursor-pointer">
                        <div class="flex items-center gap-2 min-w-0">
                            <img src="${u.avatar}" class="w-7 h-7 rounded-full object-cover shrink-0" alt="">
                            <div class="min-w-0 truncate">
                                <div class="text-xs font-bold text-stone-800 dark:text-stone-100 truncate">${u.name}</div>
                                <div class="text-[10px] text-stone-400 truncate">@${u.username}</div>
                            </div>
                        </div>
                        ${u.is_locked_in ? '<span class="px-2 py-0.5 rounded-full bg-amber-500/15 text-amber-600 dark:text-amber-400 text-[9px] font-black uppercase shrink-0">Locked-In</span>' : ''}
                    </button>
                `).join('');
            });
        }

        function addPostModalTaggedUser(user) {
            if (!postModalSelectedTaggedUsers.some(u => u.id === user.id)) {
                postModalSelectedTaggedUsers.push(user);
            }
            const input = document.getElementById('postModalTagSearchInput');
            if (input) input.value = '';
            renderPostModalSelectedTags();
            searchPostModalTagUsers('');
        }

        function removePostModalTaggedUser(userId) {
            postModalSelectedTaggedUsers = postModalSelectedTaggedUsers.filter(u => u.id !== userId);
            renderPostModalSelectedTags();
            searchPostModalTagUsers(document.getElementById('postModalTagSearchInput')?.value || '');
        }

        function renderPostModalSelectedTags() {
            const container = document.getElementById('postModalSelectedTags');
            if (!container) return;
            if (postModalSelectedTaggedUsers.length === 0) {
                container.innerHTML = `<span class="text-[11px] text-stone-400 italic">No members tagged yet. Select members below to tag them.</span>`;
                return;
            }
            container.innerHTML = postModalSelectedTaggedUsers.map(u => `
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-amber-500/10 border border-amber-500/30 text-amber-700 dark:text-amber-300 text-xs font-semibold">
                    <img src="${u.avatar}" class="w-4 h-4 rounded-full object-cover" alt="">
                    <span>@${u.username}</span>
                    <button type="button" onclick="removePostModalTaggedUser(${u.id})" class="hover:text-red-500 ml-0.5 cursor-pointer">&times;</button>
                </span>
            `).join('');
        }

        function submitPostTaggedUsers() {
            if (!currentTagPostId) return;
            const btn = document.getElementById('savePostTagsBtn');
            if (btn) btn.disabled = true;

            fetch(`/timeline/${currentTagPostId}/tags`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    tagged_users: postModalSelectedTaggedUsers.map(u => u.id)
                })
            })
            .then(async res => {
                const data = await res.json();
                return { status: res.status, data };
            })
            .then(({ status, data }) => {
                if (status === 403 && data.requires_lockin) {
                    document.getElementById('tagPeopleUnlockedSection')?.classList.add('hidden');
                    document.getElementById('tagPeopleLockedGate')?.classList.remove('hidden');
                    return;
                }
                if (data.success) {
                    closeTagPeopleModal();
                    window.location.reload();
                } else {
                    if (typeof window.showPlatformError === 'function') {
                    window.showPlatformError(data.error || 'Could not update tagged people.', 'Tagging Failed');
                } else {
                    alert(data.error || 'Could not update tagged people.');
                }
                }
            })
            .finally(() => {
                if (btn) btn.disabled = false;
            });
        }

        function lockInFromTagModal() {
            if (!currentTagAuthorId) return;
            const btn = document.getElementById('tagModalLockInBtn');
            if (btn) btn.disabled = true;

            fetch(`/users/${currentTagAuthorId}/lockin`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                }
            })
            .then(r => r.json())
            .then(data => {
                if (data.lockedIn) {
                    document.getElementById('tagPeopleLockedGate')?.classList.add('hidden');
                    document.getElementById('tagPeopleUnlockedSection')?.classList.remove('hidden');
                    renderPostModalSelectedTags();
                    searchPostModalTagUsers('');
                }
            })
            .finally(() => {
                if (btn) btn.disabled = false;
            });
        }

        function removeSelfTagFromPost(postId, userId = null) {
            fetch(`/timeline/${postId}/tags/self`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify(userId ? { user_id: userId } : {})
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    window.location.reload();
                }
            });
        }

        function quickLockInAuthor(authorId, postId) {
            fetch(`/users/${authorId}/lockin`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                }
            })
            .then(r => r.json())
            .then(data => {
                if (data.lockedIn) {
                    const notice = document.getElementById(`resonance-lockin-notice-${postId}`);
                    if (notice) {
                        notice.classList.add('hidden');
                        notice.classList.remove('flex');
                    }
                    const input = document.getElementById(`resonance-input-${postId}`);
                    if (input) {
                        input.placeholder = 'Write a resonance... (use @username to tag)';
                        input.focus();
                    }
                }
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
                    if (data.requires_lockin) {
                        const notice = document.getElementById(`resonance-lockin-notice-${postId}`);
                        if (notice) {
                            notice.classList.remove('hidden');
                            notice.classList.add('flex');
                        } else {
                            alert(data.error);
                        }
                        return;
                    }
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
                        if (countSpan) countSpan.innerText = `${data.count}`;
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
                if (data.requires_lockin) {
                    const notice = document.getElementById(`resonance-lockin-notice-${postId}`);
                    if (notice) {
                        notice.classList.remove('hidden');
                        notice.classList.add('flex');
                    } else {
                        alert(data.error);
                    }
                    return;
                }
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
                    if (countSpan) countSpan.innerText = `${data.count}`;
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