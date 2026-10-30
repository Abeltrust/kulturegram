@extends('layouts.app')

@section('title', Str::limit($post->content, 40) . ' - Kulturegram')

@section('content')
<div class="max-w-4xl mx-auto p-4 sm:p-6 space-y-6">

    <!-- Breadcrumb & Back -->
    <div class="flex items-center justify-between">
        <a href="{{ route('timeline.index') }}" 
           class="inline-flex items-center gap-2 text-xs sm:text-sm font-semibold text-stone-600 dark:text-stone-400 hover:text-amber-600 dark:hover:text-amber-400 transition">
            <i data-lucide="arrow-left" class="w-4 h-4"></i>
            <span>Back to Timeline</span>
        </a>

        @if($post->chapter)
            <span class="bg-amber-100 text-amber-800 dark:bg-amber-950/40 dark:text-amber-400 px-3 py-1 rounded-full text-xs font-bold border border-amber-500/20">
                {{ $post->chapter }}
            </span>
        @endif
    </div>

    <!-- Post Article Card -->
    <article id="post-{{ $post->id }}"
             class="bg-white dark:bg-stone-900 rounded-3xl p-5 sm:p-7 shadow-sm border border-stone-100 dark:border-stone-800 space-y-5">
        
        @if($post->sharedPost && $post->sharedPost->user)
            <div class="flex items-center gap-1.5 text-xs font-bold text-amber-600 dark:text-amber-400 pb-2 border-b border-stone-100 dark:border-stone-800">
                <i data-lucide="repeat-2" class="w-4 h-4"></i>
                <span>Reposted from</span>
                <a href="{{ route('profile.user', $post->sharedPost->user) }}" class="hover:underline">
                    {{ $post->sharedPost->user->name }} ({{ '@' . $post->sharedPost->user->username }})
                </a>
            </div>
        @endif

        <!-- Header -->
        <div class="flex items-start justify-between gap-3">
            <div class="flex items-start gap-3 min-w-0 flex-1">
                <img src="{{ $post->user->profile_photo_url }}" 
                     class="w-11 h-11 sm:w-12 sm:h-12 rounded-2xl object-cover border border-stone-100 dark:border-stone-800 shadow-sm flex-shrink-0 mt-0.5" 
                     alt="{{ $post->user->name }}">
                <div class="min-w-0 flex-1">
                    <div class="flex items-center gap-x-2 gap-y-0.5 flex-wrap">
                        <a href="{{ route('profile.user', $post->user) }}" class="font-bold text-stone-900 dark:text-white hover:text-amber-600 transition text-sm sm:text-base truncate max-w-[140px] sm:max-w-none">
                            {{ $post->user->name }}
                        </a>
                        <span class="text-xs text-stone-400">@ {{ $post->user->username }}</span>
                        @include('timeline.partials.post-tagged-badge', ['post' => $post])
                        @if($post->user->location)
                            <span class="text-xs bg-stone-100 dark:bg-stone-800 text-stone-600 dark:text-stone-400 px-2 py-0.5 rounded-full">
                                {{ $post->user->location }}
                            </span>
                        @endif
                    </div>
                    <div class="flex items-center gap-x-2 gap-y-1 text-xs text-stone-400 mt-1 flex-wrap">
                        <span class="flex items-center gap-1">
                            <i data-lucide="clock" class="w-3 h-3 text-stone-400"></i>
                            <span>{{ $post->time_ago }}</span>
                        </span>
                        @if($post->location)
                            <span>•</span>
                            <span class="flex items-center gap-1">
                                <i data-lucide="map-pin" class="w-3 h-3 text-stone-400"></i>
                                <span>{{ $post->location }}</span>
                            </span>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Privacy & Actions -->
            <div class="flex items-center gap-1.5 sm:gap-2 flex-shrink-0 self-start mt-0.5">
                @if($post->privacy === 'private')
                    <span class="flex items-center gap-1 text-xs text-stone-400 bg-stone-100 dark:bg-stone-800 px-2 py-1 rounded-full">
                        <i data-lucide="lock" class="w-3.5 h-3.5"></i>
                        <span class="hidden xs:inline">Private</span>
                    </span>
                @elseif($post->privacy === 'vault')
                    <span class="flex items-center gap-1 text-xs text-amber-600 bg-amber-50 dark:bg-amber-950/40 px-2 py-1 rounded-full">
                        <i data-lucide="shield" class="w-3.5 h-3.5"></i>
                        <span class="hidden xs:inline">Vault</span>
                    </span>
                @endif

                @auth
                    @if((int)auth()->id() === (int)$post->user_id || auth()->user()->is_admin)
                        <div class="relative" x-data="{ openMenu: false }">
                            <button type="button" 
                                @click="openMenu = !openMenu" 
                                class="p-1.5 sm:p-2 rounded-xl text-stone-400 hover:text-stone-700 dark:hover:text-stone-200 hover:bg-stone-100 dark:hover:bg-stone-800 transition -mr-1" 
                                title="Post options">
                                <i data-lucide="more-horizontal" class="w-4 h-4 sm:w-5 sm:h-5"></i>
                            </button>
                            <div x-show="openMenu" 
                                @click.away="openMenu = false" 
                                x-cloak 
                                class="absolute right-0 top-full mt-1 z-30 w-40 bg-white dark:bg-stone-900 rounded-2xl shadow-xl border border-stone-200 dark:border-stone-800 py-1.5 text-xs">
                                <button type="button" 
                                    @click="openMenu = false; openEditTimelinePostModal({{ $post->id }})" 
                                    class="w-full text-left px-3.5 py-2 hover:bg-amber-500/10 hover:text-amber-600 text-stone-700 dark:text-stone-300 font-medium transition flex items-center gap-2">
                                    <i data-lucide="pencil" class="w-3.5 h-3.5 text-amber-500"></i>
                                    <span>Edit Story</span>
                                </button>
                                <button type="button" 
                                    @click="openMenu = false; confirmDeleteTimelinePost({{ $post->id }})" 
                                    class="w-full text-left px-3.5 py-2 hover:bg-red-500/10 hover:text-red-600 text-red-600 dark:text-red-400 font-medium transition flex items-center gap-2">
                                    <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                    <span>Delete Story</span>
                                </button>
                            </div>
                        </div>
                    @endif
                @endauth
            </div>
        </div>

        <!-- Post Content -->
        <div class="space-y-4">
            <div id="post-original-content-{{ $post->id }}" class="text-stone-800 dark:text-stone-100 leading-relaxed text-base sm:text-lg">
                {!! $post->formatted_content !!}
            </div>
            <div id="post-translated-content-{{ $post->id }}" class="hidden text-stone-800 dark:text-stone-100 leading-relaxed text-base bg-amber-500/5 dark:bg-amber-950/20 p-4 rounded-2xl border border-amber-500/20"></div>

            <!-- Translation Bar -->
            @php
                $viewerLanguage = auth()->check() ? auth()->user()->getPreferredLanguage() : 'English';
                $postLanguage = $post->language ?: 'English';
                $isDifferentLanguage = strtolower(trim($postLanguage)) !== strtolower(trim($viewerLanguage));
            @endphp
            <div class="flex items-center justify-between flex-wrap gap-2 text-xs pt-1 border-t border-stone-100 dark:border-stone-800">
                <div class="flex items-center gap-2">
                    <button type="button" 
                        onclick="togglePostTranslation({{ $post->id }}, '{{ $viewerLanguage }}')" 
                        id="btn-translate-{{ $post->id }}"
                        class="inline-flex items-center gap-1.5 font-bold text-amber-600 dark:text-amber-400 hover:underline">
                        <i data-lucide="globe" class="w-3.5 h-3.5"></i>
                        <span id="btn-translate-text-{{ $post->id }}">{{ $isDifferentLanguage ? 'See Translation (to ' . $viewerLanguage . ')' : 'See Translation' }}</span>
                    </button>
                    <span id="translation-loader-{{ $post->id }}" class="hidden text-[11px] text-amber-500 items-center gap-1">
                        <i data-lucide="loader" class="w-3 h-3 animate-spin"></i>
                        <span>Translating...</span>
                    </span>
                    @if($post->language)
                        <span class="text-stone-400">•</span>
                        <span class="px-2 py-0.5 rounded bg-stone-100 dark:bg-stone-800 font-medium text-stone-500">{{ $post->language }}</span>
                    @endif
                    @if($post->is_ai_formatted)
                        <span class="text-amber-600 dark:text-amber-400 font-bold flex items-center gap-1">
                            <i data-lucide="sparkles" class="w-3 h-3"></i>
                            <span>Oral Story</span>
                        </span>
                    @endif
                </div>
            </div>

            <!-- Tags -->
            @if(!empty($post->tags))
                <div class="flex flex-wrap gap-2 pt-2">
                    @foreach($post->tags as $tag)
                        <a href="{{ route('timeline.index', ['tag' => $tag]) }}"
                           class="inline-flex items-center text-xs font-semibold text-amber-600 dark:text-amber-400 bg-amber-50 dark:bg-amber-950/40 px-3 py-1 rounded-full hover:bg-amber-100 transition">
                            #{{ $tag }}
                        </a>
                    @endforeach
                </div>
            @endif

            <!-- Media -->
            @php
                $showPostYoutubeId = $post->video ? \App\Models\CulturalClass::extractYoutubeId($post->video) : null;
                $showPostImageSrc = \App\Models\CulturalClass::resolveMediaUrl($post->image);
                $showPostVideoSrc = \App\Models\CulturalClass::resolveMediaUrl($post->video);
            @endphp
            @if($showPostImageSrc && !$showPostVideoSrc && !$showPostYoutubeId)
                <div class="relative rounded-2xl overflow-hidden bg-stone-900/5 dark:bg-black/40 border border-stone-100 dark:border-stone-800 flex items-center justify-center min-h-[240px] max-h-[780px] group">
                    <div class="absolute inset-0 bg-cover bg-center filter blur-xl opacity-25 dark:opacity-20 scale-110 pointer-events-none"
                         style="background-image: url('{{ $showPostImageSrc }}');"></div>
                    <img src="{{ $showPostImageSrc }}" alt="Story Image" class="relative z-10 w-auto h-auto max-w-full max-h-[740px] object-contain mx-auto">
                </div>

                @if(str_contains($post->image, 'flyers/') || (!empty($post->tags) && in_array('CulturalPassport', (array)$post->tags)))
                    <div class="mt-2.5 p-2.5 sm:p-3 rounded-2xl bg-amber-500/10 border border-amber-500/25 flex flex-wrap items-center justify-between gap-2.5">
                        <div class="flex items-center gap-2 min-w-0">
                            <div class="w-7 h-7 rounded-lg bg-amber-500/20 text-amber-600 dark:text-amber-400 flex items-center justify-center shrink-0">
                                <i data-lucide="award" class="w-4 h-4 text-amber-500"></i>
                            </div>
                            <div class="min-w-0">
                                <p class="text-xs font-bold text-stone-900 dark:text-stone-100 truncate">
                                    {{ str_contains($post->image, 'flyers/') ? 'Official Cultural Welcome Flyer' : 'Official Cultural Profile Avatar' }}
                                </p>
                                <p class="text-[11px] text-stone-500 dark:text-stone-400">Download and share to celebrate cultural identity</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-2 shrink-0">
                            <a href="{{ $showPostImageSrc }}" download="kulturegram_{{ Str::slug($post->user->name) }}_{{ str_contains($post->image, 'flyers/') ? 'flyer' : 'avatar' }}"
                               class="px-3 py-1.5 rounded-xl bg-amber-500 hover:bg-amber-600 text-white text-xs font-bold transition flex items-center gap-1.5 shadow-sm active:scale-95"
                               title="Download {{ str_contains($post->image, 'flyers/') ? 'Welcome Flyer' : 'Cultural Avatar' }}">
                                <i data-lucide="download" class="w-3.5 h-3.5"></i>
                                <span>{{ str_contains($post->image, 'flyers/') ? 'Download Flyer' : 'Download Avatar' }}</span>
                            </a>
                            <button type="button" 
                                    onclick="shareFlyer('{{ addslashes($post->user->name) }}', '{{ route('timeline.show', $post->id) }}')"
                                    class="px-3 py-1.5 rounded-xl bg-stone-200 dark:bg-stone-800 hover:bg-stone-300 dark:hover:bg-stone-700 text-stone-800 dark:text-stone-200 text-xs font-semibold transition flex items-center gap-1.5 active:scale-95"
                                    title="Share Story & Identity">
                                <i data-lucide="share-2" class="w-3.5 h-3.5"></i>
                                <span class="hidden sm:inline">Share</span>
                            </button>
                        </div>
                    </div>
                @endif
            @endif

            @if($showPostYoutubeId)
                <div class="relative rounded-2xl overflow-hidden bg-black border border-stone-100 dark:border-stone-800 aspect-video">
                    <iframe src="https://www.youtube.com/embed/{{ $showPostYoutubeId }}?rel=0&modestbranding=1"
                            class="w-full h-full"
                            frameborder="0"
                            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                            allowfullscreen></iframe>
                </div>
            @elseif($showPostVideoSrc)
                <div class="rounded-2xl overflow-hidden bg-black border border-stone-100 dark:border-stone-800 flex items-center justify-center min-h-[220px]">
                    <video src="{{ $showPostVideoSrc }}{{ !str_contains($showPostVideoSrc, '#') ? '#t=0.1' : '' }}"
                           controls
                           preload="metadata"
                           class="w-auto h-auto max-w-full max-h-[650px] mx-auto"></video>
                </div>
            @endif

            @if($post->audio)
                <div class="p-4 bg-stone-100 dark:bg-stone-800 rounded-2xl">
                    <audio src="{{ Storage::url($post->audio) }}" controls class="w-full"></audio>
                </div>
            @endif

            @include('timeline.partials.quoted-post-card', ['post' => $post])
        </div>

        <!-- Action Bar -->
        <div class="flex items-center justify-between pt-4 border-t border-stone-100 dark:border-stone-800 text-sm">
            <div class="flex items-center gap-4">
                @auth
                    <!-- Tap -->
                    <button onclick="toggleTap({{ $post->id }})" 
                            data-tap-post="{{ $post->id }}"
                            class="flex items-center gap-1.5 px-3 py-1.5 rounded-xl transition font-medium {{ auth()->user()->hasTapped($post) ? 'bg-red-50 text-red-600' : 'text-stone-600 dark:text-stone-300 hover:bg-stone-100 dark:hover:bg-stone-800' }}">
                        <i data-lucide="heart" class="w-4 h-4 {{ auth()->user()->hasTapped($post) ? 'fill-current' : '' }}"></i>
                        <span data-tap-count="{{ $post->id }}">{{ $post->taps_count }}</span>
                    </button>

                    <!-- Check-In -->
                    <button onclick="toggleCheckin({{ $post->id }})" 
                            data-checkin-post="{{ $post->id }}"
                            class="flex items-center gap-1.5 px-3 py-1.5 rounded-xl transition font-medium {{ auth()->user()->hasCheckedIn($post) ? 'text-green-600' : 'text-stone-600 dark:text-stone-300 hover:bg-stone-100 dark:hover:bg-stone-800' }}">
                        <i data-lucide="check-circle" class="w-4 h-4"></i>
                        <span data-checkin-count="{{ $post->id }}">{{ $post->check_ins_count }}</span>
                    </button>
                @endauth
            </div>

            <!-- Share -->
            <button onclick="copyCurrentLink()" class="flex items-center gap-1.5 text-stone-500 hover:text-stone-800 dark:hover:text-stone-200 transition font-medium">
                <i data-lucide="share-2" class="w-4 h-4"></i>
                <span>Share</span>
            </button>
        </div>

        <!-- Resonances Section -->
        <div class="pt-6 border-t border-stone-100 dark:border-stone-800 space-y-4">
            <h4 class="font-bold text-stone-900 dark:text-stone-100 text-sm sm:text-base flex items-center gap-2">
                <i data-lucide="message-square" class="w-4 h-4 text-amber-500"></i>
                <span>Resonances ({{ $post->resonances->count() }})</span>
            </h4>

            @auth
                <div class="flex items-center gap-2 bg-stone-50 dark:bg-stone-800/80 p-2 rounded-2xl border border-stone-200 dark:border-stone-700">
                    <input type="text" id="resonance-input-{{ $post->id }}" placeholder="Add your reflection or resonance..." 
                           class="flex-1 bg-transparent px-3 py-2 text-sm text-stone-800 dark:text-stone-100 outline-none"
                           onkeydown="if(event.key === 'Enter') submitPostResonance({{ $post->id }})">
                    <button type="button" onclick="submitPostResonance({{ $post->id }})" 
                            class="px-4 py-2 bg-amber-500 hover:bg-amber-600 text-white font-bold text-xs rounded-xl shadow transition">
                        Resonate
                    </button>
                </div>
            @endauth

            <div id="resonance-list-{{ $post->id }}" class="space-y-3">
                @forelse($post->resonances->whereNull('parent_id') as $res)
                    <div class="p-3.5 bg-stone-50 dark:bg-stone-800/50 rounded-2xl border border-stone-100 dark:border-stone-800 text-xs sm:text-sm space-y-2">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2 min-w-0">
                                <a href="{{ $res->user->profile_url }}" class="flex items-center gap-2 min-w-0 hover:opacity-85 transition group">
                                    <img src="{{ $res->user->profile_photo_url }}" class="w-6 h-6 rounded-full object-cover border border-stone-200 dark:border-stone-700 shrink-0 group-hover:ring-2 group-hover:ring-amber-500/40" alt="{{ $res->user->name }}">
                                    <span class="font-bold text-stone-900 dark:text-stone-100 group-hover:text-amber-600 dark:group-hover:text-amber-400 transition truncate">{{ $res->user->name }}</span>
                                </a>
                                @if($res->user_id === $post->user_id)
                                    <span class="px-1.5 py-0.2 rounded bg-amber-500/10 text-amber-600 text-[10px] font-black uppercase">Author</span>
                                @endif
                            </div>
                            <span class="text-xs text-stone-400">{{ $res->created_at->diffForHumans() }}</span>
                        </div>
                        <div class="text-stone-700 dark:text-stone-300 leading-relaxed pl-8">
                            {!! $res->formatted_content !!}
                        </div>
                    </div>
                @empty
                    <p class="text-xs text-stone-400 italic">No resonances yet. Be the first to share your reflections.</p>
                @endforelse
            </div>
        </div>
    </article>
</div>

@push('scripts')
<script>
    function copyCurrentLink() {
        navigator.clipboard.writeText(window.location.href).then(() => {
            alert('Link copied to clipboard!');
        });
    }

    function submitPostResonance(postId) {
        const input = document.getElementById(`resonance-input-${postId}`);
        const content = input ? input.value.trim() : '';
        if (!content) return;

        fetch(`/timeline/${postId}/resonance`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ content })
        })
        .then(async res => {
            const data = await res.json();
            return { status: res.status, data };
        })
        .then(({ status, data }) => {
            if (status === 403 && data.requires_lockin) {
                if (typeof window.showPlatformError === 'function') {
                    window.showPlatformError(data.error || 'Lock-In required to tag members.', 'Lock-In Required');
                } else {
                    alert(data.error || 'Lock-In required.');
                }
                return;
            }
            if (data.success) {
                input.value = '';
                window.location.reload();
            } else {
                if (typeof window.showPlatformError === 'function') {
                    window.showPlatformError(data.error || 'Unable to submit resonance.', 'Notice');
                } else {
                    alert(data.error || 'Unable to submit resonance.');
                }
            }
        });
    }

    function toggleTap(postId) {
        fetch(`/timeline/${postId}/tap`, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
        })
        .then(res => res.json())
        .then(data => {
            const count = document.querySelector(`[data-tap-count="${postId}"]`);
            if (count) count.textContent = data.count;
            const btn = document.querySelector(`[data-tap-post="${postId}"]`);
            if (btn) {
                if (data.tapped) {
                    btn.classList.add('bg-red-50', 'text-red-600');
                } else {
                    btn.classList.remove('bg-red-50', 'text-red-600');
                }
            }
        });
    }

    function toggleCheckin(postId) {
        fetch(`/timeline/${postId}/checkin`, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
        })
        .then(res => res.json())
        .then(data => {
            const count = document.querySelector(`[data-checkin-count="${postId}"]`);
            if (count) count.textContent = data.count;
        });
    }

    function shareFlyer(name, postUrl) {
        const text = `✨ Check out ${name}'s Cultural Welcome Flyer on Kulturegram! Connect, celebrate living heritage, and explore diverse traditions: ${postUrl}`;
        if (navigator.share) {
            navigator.share({
                title: `${name} on Kulturegram`,
                text: text,
                url: postUrl
            }).catch(() => {});
        } else {
            navigator.clipboard.writeText(text).then(() => {
                alert('✓ Invitation link copied to clipboard! You can now share it.');
            });
        }
    }
</script>
@endpush
@endsection
