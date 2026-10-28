{{-- resources/views/communities/show.blade.php --}}
@extends('layouts.app')

@section('content')
    <div class="max-w-7xl mx-auto p-4 sm:p-6 lg:p-8 grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- Left Column: Main Content Area --}}
        <div class="lg:col-span-2 space-y-6">

            {{-- Tabs --}}
            <div class="flex gap-4 border-b border-stone-200 dark:border-stone-800 pb-2">
                <button onclick="switchCommunityTab('feed')" id="tab-feed" class="px-4 py-2 text-sm sm:text-base font-semibold border-b-2 border-amber-500 text-amber-600 dark:text-amber-500 transition">Feed</button>
                <button onclick="switchCommunityTab('meetings')" id="tab-meetings" class="px-4 py-2 text-sm sm:text-base font-semibold border-b-2 border-transparent text-stone-500 hover:text-stone-800 dark:hover:text-stone-200 transition">Meetings / Lectures</button>
            </div>

            {{-- Feed Section --}}
            <div id="section-feed" class="space-y-6 block">

            {{-- Post Form --}}
            @auth
                <div class="bg-white dark:bg-stone-900 rounded-2xl shadow-md p-6 border border-stone-100 dark:border-stone-800">
                    <form id="community-post-form" action="{{ route('communities.posts.store', $community->id) }}" method="POST"
                        enctype="multipart/form-data" class="space-y-4">
                        @csrf
                        <input type="hidden" name="community_id" value="{{ $community->id }}">
                        <textarea name="content" rows="5"
                            class="w-full p-4 border border-stone-300 dark:border-stone-700 bg-stone-50 dark:bg-stone-800 text-stone-900 dark:text-stone-100 rounded-2xl resize-none focus:ring-2 focus:ring-amber-300 text-sm sm:text-base placeholder-stone-400 dark:placeholder-stone-500"
                            placeholder="Share something with {{ $community->name }}..."></textarea>

                        <div class="flex items-center justify-between gap-3 flex-wrap">
                            <div class="flex items-center gap-3 text-stone-600 dark:text-stone-400">
                                <label title="Add image"
                                    class="cursor-pointer p-2 rounded-md hover:bg-stone-50 dark:hover:bg-stone-800 transition">
                                    <i data-lucide="image" class="w-6 h-6"></i>
                                    <input id="community-image-input" type="file" name="image" accept="image/*" class="hidden"
                                        onchange="previewImage(event,'image-preview')">
                                </label>
                                <label title="Add video"
                                    class="cursor-pointer p-2 rounded-md hover:bg-stone-50 dark:hover:bg-stone-800 transition">
                                    <i data-lucide="video" class="w-6 h-6"></i>
                                    <input type="file" name="video" accept="video/*" class="hidden">
                                </label>
                                <label title="Add audio"
                                    class="cursor-pointer p-2 rounded-md hover:bg-stone-50 dark:hover:bg-stone-800 transition">
                                    <i data-lucide="mic" class="w-6 h-6"></i>
                                    <input type="file" name="audio" accept="audio/*" class="hidden">
                                </label>
                            </div>

                            <button type="submit"
                                class="px-5 py-2 glass-orange text-white rounded-xl text-sm sm:text-base font-semibold transition">
                                Post
                            </button>
                        </div>

                        <div id="image-preview" class="hidden mt-3">
                            <img id="image-preview-img" src=""
                                class="max-h-72 sm:max-h-96 rounded-lg object-contain border w-full" alt="Preview">
                            <div class="mt-2 text-xs text-stone-500">Preview — image will upload with the post</div>
                        </div>
                    </form>
                </div>
            @endauth

            {{-- Posts Feed --}}
            <div class="space-y-6" id="posts-feed">
                @forelse($posts as $post)
                    <div class="bg-white dark:bg-stone-900 rounded-2xl shadow-md border border-stone-100 dark:border-stone-800 p-5 post-card"
                        id="post-{{ $post->id }}">

                        {{-- User Info & Actions --}}
                        <div class="flex items-start justify-between gap-3 mb-3">
                            <div class="flex items-start gap-2.5 sm:gap-3 min-w-0 flex-1">
                                <img src="{{ $post->user->profile_photo_url }}" alt="{{ $post->user->name }}"
                                    class="w-10 h-10 sm:w-11 sm:h-11 rounded-full object-cover flex-shrink-0 mt-0.5"
                                    loading="lazy">
                                <div class="min-w-0 flex-1">
                                    <p class="font-semibold text-sm sm:text-base text-stone-800 dark:text-stone-100 truncate">
                                        {{ $post->user->name }}</p>
                                    <p class="text-xs sm:text-sm text-stone-500 dark:text-stone-400 mt-0.5">
                                        {{ $post->created_at->diffForHumans() }}</p>
                                </div>
                            </div>

                            <div class="flex items-center gap-1.5 flex-shrink-0 self-start mt-0.5">
                                @auth
                                    @if(auth()->id() === $post->user_id || auth()->user()->is_admin || $community->user_id === auth()->id())
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
                                                    @click="openMenu = false; openEditCommunityPostModal({{ $community->id }}, {{ $post->id }})" 
                                                    class="w-full text-left px-3.5 py-1.5 hover:bg-orange-500/10 hover:text-orange-600 text-stone-700 dark:text-stone-300 font-medium transition flex items-center gap-2">
                                                    <i data-lucide="pencil" class="w-3.5 h-3.5 text-orange-500"></i>
                                                    <span>Edit Post</span>
                                                </button>
                                                <button type="button" 
                                                    @click="openMenu = false; confirmDeleteCommunityPost({{ $community->id }}, {{ $post->id }})" 
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

                        {{-- Post Content --}}
                        <p
                            class="mt-2 text-sm sm:text-base text-stone-700 dark:text-stone-300 leading-relaxed whitespace-pre-wrap text-left">
                            {{ $post->content }}
                        </p>

                        {{-- Media --}}
                        <div class="mt-3 flex flex-col gap-4">
                            @if($post->image)
                                <div class="relative w-full bg-stone-900/5 dark:bg-black/40 rounded-xl overflow-hidden flex items-center justify-center border border-stone-100 dark:border-stone-800 group cursor-pointer min-h-[220px] max-h-[36rem]"
                                     onclick="openImageLightbox('{{ asset('storage/' . $post->image) }}', 'Community post photo')">
                                    <div class="absolute inset-0 bg-cover bg-center filter blur-xl opacity-25 dark:opacity-20 scale-110 pointer-events-none"
                                         style="background-image: url('{{ asset('storage/' . $post->image) }}');"></div>
                                    <img src="{{ asset('storage/' . $post->image) }}"
                                        class="relative z-10 w-auto h-auto max-w-full max-h-[34rem] object-contain mx-auto transition-transform duration-300 group-hover:scale-[1.01]" alt="post image"
                                        loading="lazy">
                                    <span class="absolute bottom-3 right-3 z-20 px-2.5 py-1 rounded-lg bg-black/65 backdrop-blur-md text-white text-[11px] font-medium opacity-0 group-hover:opacity-100 transition-opacity duration-200 flex items-center gap-1.5 pointer-events-none">
                                        <i data-lucide="maximize-2" class="w-3.5 h-3.5"></i>
                                        <span>View full</span>
                                    </span>
                                </div>
                            @endif
                            @if($post->video)
                                <div class="rounded-xl overflow-hidden bg-black flex items-center justify-center min-h-[200px]">
                                    <video controls class="w-auto h-auto max-w-full max-h-[30rem] mx-auto">
                                        <source src="{{ asset('storage/' . $post->video) }}" type="video/mp4">
                                    </video>
                                </div>
                            @endif
                            @if($post->audio)
                                <audio controls class="w-full">
                                    <source src="{{ asset('storage/' . $post->audio) }}" type="audio/mpeg">
                                </audio>
                            @endif
                        </div>

                        {{-- Interaction Icons (Single Line, Centered) --}}
                        <div
                            class="flex items-center justify-center gap-6 mt-4 border-t border-stone-100 dark:border-stone-800 pt-3 flex-wrap">
                            @auth
                                <button title="Tap" data-tap-post="{{ $post->id }}"
                                    onclick="toggleCommunityTap({{ $post->id }}, this)"
                                    class="flex items-center gap-2 px-3 py-1 rounded-full text-sm transition-all transform active:scale-95 {{ $post->taps()->where('user_id', auth()->id())->exists() ? 'bg-red-50 dark:bg-red-900/20 text-red-600 dark:text-red-400' : 'text-stone-600 dark:text-stone-400 hover:bg-stone-50 dark:hover:bg-stone-800' }}">
                                    <i data-lucide="heart"
                                        class="w-5 h-5 transition-transform duration-300 {{ $post->taps()->where('user_id', auth()->id())->exists() ? 'fill-current scale-110' : 'scale-100' }}"></i>
                                    <span id="tap-count-{{ $post->id }}"
                                        class="text-sm font-medium">{{ $post->taps()->count() }}</span>
                                </button>
                            @else
                                <a href="{{ route('login') }}"
                                    class="flex items-center gap-2 px-3 py-1 rounded-full text-sm text-stone-400 dark:text-stone-500 hover:text-amber-600 dark:hover:text-amber-500 transition">
                                    <i data-lucide="heart" class="w-5 h-5"></i>
                                    <span class="text-sm font-medium">{{ $post->taps()->count() }}</span>
                                </a>
                            @endauth

                            <button title="Comments" onclick="toggleCommentsBox({{ $post->id }})"
                                class="flex items-center gap-2 px-3 py-1 rounded-full text-sm text-stone-600 dark:text-stone-400 hover:bg-stone-50 dark:hover:bg-stone-800 transition">
                                <i data-lucide="message-square" class="w-5 h-5"></i>
                                <span id="comment-count-{{ $post->id }}"
                                    class="text-sm font-medium">{{ $post->comments()->count() }}</span>
                            </button>

                            <button title="Save"
                                class="flex items-center gap-2 px-3 py-1 rounded-full text-sm text-stone-600 dark:text-stone-400 hover:bg-stone-50 dark:hover:bg-stone-800 transition">
                                <i data-lucide="bookmark" class="w-5 h-5"></i>
                                <span class="text-sm"></span>
                            </button>

                            <button title="Share"
                                onclick="openShareModal('{{ url('/communities/' . $community->id . '?post=' . $post->id) }}', '{{ addslashes(Str::limit($post->content ?? '', 120)) }}')"
                                class="flex items-center gap-2 px-3 py-1 rounded-full text-sm text-stone-600 dark:text-stone-400 hover:bg-stone-50 dark:hover:bg-stone-800 transition">
                                <i data-lucide="share-2" class="w-5 h-5"></i>
                                <span class="text-sm"></span>
                            </button>
                        </div>

                        {{-- Comments Box --}}
                        <div id="comments-box-{{ $post->id }}" class="hidden mt-3 space-y-3">
                            @foreach($post->comments()->whereNull('parent_id')->latest()->limit(50)->get() as $comment)
                                <div class="flex gap-3 items-start">
                                    <img src="{{ $comment->user->profile_photo_url }}" class="w-9 h-9 rounded-full object-cover">
                                    <div class="flex-1 space-y-2">
                                        <div class="bg-stone-50 dark:bg-stone-800 rounded-2xl p-4">
                                            <div class="flex items-center justify-between">
                                                <div>
                                                    <p class="text-sm font-medium text-stone-800 dark:text-stone-100">{{ $comment->user->name }}</p>
                                                    <p class="text-xs text-stone-400 dark:text-stone-500">{{ $comment->created_at->diffForHumans() }}</p>
                                                </div>
                                            </div>
                                            <p class="mt-2 text-sm text-stone-700 dark:text-stone-300">{{ $comment->content }}</p>
                                            <div class="mt-2 text-xs text-stone-500 flex gap-3">
                                                <button onclick="toggleReplyBox({{ $comment->id }})" class="hover:text-amber-600">Reply</button>
                                            </div>
                                        </div>

                                        {{-- Reply Form --}}
                                        @auth
                                            <form id="reply-form-{{ $comment->id }}" class="hidden flex gap-2" onsubmit="submitReply(event, {{ $post->id }}, {{ $comment->id }})">
                                                @csrf
                                                <input type="text" id="reply-input-{{ $comment->id }}" placeholder="Write a reply..." class="flex-1 px-3 py-2 text-sm rounded-xl border border-stone-300 dark:border-stone-700 bg-white dark:bg-stone-900 text-stone-900 dark:text-stone-100 placeholder-stone-400 focus:ring-2 focus:ring-amber-300 transition-all outline-none">
                                                <button type="submit" class="px-3 py-2 glass-orange text-white rounded-xl text-sm"><i data-lucide="send" class="w-4 h-4"></i></button>
                                            </form>
                                        @endauth

                                        {{-- Replies --}}
                                        <div id="replies-box-{{ $comment->id }}" class="space-y-2 pl-4 border-l-2 border-stone-100 dark:border-stone-800 mt-2">
                                            @foreach($comment->replies()->oldest()->get() as $reply)
                                                <div class="flex gap-3 items-start">
                                                    <img src="{{ $reply->user->profile_photo_url }}" class="w-7 h-7 rounded-full object-cover">
                                                    <div class="bg-stone-50 dark:bg-stone-800 rounded-2xl p-3 flex-1">
                                                        <div class="flex items-center justify-between">
                                                            <div>
                                                                <p class="text-sm font-medium text-stone-800 dark:text-stone-100">{{ $reply->user->name }}</p>
                                                                <p class="text-xs text-stone-400 dark:text-stone-500">{{ $reply->created_at->diffForHumans() }}</p>
                                                            </div>
                                                        </div>
                                                        <p class="mt-1 text-sm text-stone-700 dark:text-stone-300">{{ $reply->content }}</p>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            @endforeach

                            @auth
                                <form onsubmit="submitComment(event, {{ $post->id }})" class="mt-3 flex items-center gap-3">
                                    @csrf
                                    <input id="comment-input-{{ $post->id }}" name="content" type="text"
                                        placeholder="Write a comment..."
                                        class="flex-1 px-4 py-3 rounded-full border border-stone-300 dark:border-stone-700 bg-white dark:bg-stone-900 text-stone-900 dark:text-stone-100 placeholder-stone-400 align-middle focus:outline-none focus:ring-2 focus:ring-amber-300 transition-all text-sm md:text-base">
                                    <button type="submit"
                                        class="inline-flex items-center gap-2 px-4 py-2 glass-orange text-white rounded-full text-sm sm:text-base font-semibold">
                                        <i data-lucide="send" class="w-5 h-5"></i>
                                    </button>
                                </form>
                            @else
                                <div class="mt-3 text-xs sm:text-sm text-stone-500 dark:text-stone-400">
                                    <a href="{{ route('login') }}" class="text-amber-600 dark:text-amber-500">Log in</a> to comment.
                                </div>
                            @endauth
                        </div>
                    </div>
                @empty
                    <div class="text-center py-12">
                        <i data-lucide="box" class="w-14 h-14 text-stone-300 mx-auto mb-3"></i>
                        <p class="text-sm sm:text-base text-stone-600">No posts yet. Be the first to post!</p>
                    </div>
                @endforelse
            </div>

            {{-- Pagination --}}
            <div class="mt-6">
                {{ $posts->links() }}
            </div>
            
            </div> {{-- End of Feed Section --}}

            {{-- Meetings Section --}}
            <div id="section-meetings" class="hidden space-y-6">
                <div class="flex items-center justify-between bg-white dark:bg-stone-900 rounded-2xl shadow-md border border-stone-100 dark:border-stone-800 p-5">
                    <div>
                        <h3 class="text-lg font-bold text-stone-800 dark:text-stone-100">Community Meetings</h3>
                        <p class="text-sm text-stone-500 dark:text-stone-400">Host and join live lectures or gatherings.</p>
                    </div>
                    @auth
                        <a href="{{ route('events.create', ['community_id' => $community->id]) }}" class="px-4 py-2 glass-orange text-white rounded-xl text-sm sm:text-base font-semibold transition">
                            Create Meeting
                        </a>
                    @endauth
                </div>

                <div class="space-y-4">
                    @forelse($community->events()->upcoming()->get() as $event)
                        <div class="bg-white dark:bg-stone-900 rounded-2xl shadow-sm border border-stone-100 dark:border-stone-800 p-4 flex flex-col sm:flex-row gap-4">
                            @if($event->image)
                                <img src="{{ asset('storage/' . $event->image) }}" class="w-full sm:w-32 h-32 object-cover rounded-xl shrink-0" alt="{{ $event->title }}">
                            @else
                                <div class="w-full sm:w-32 h-32 bg-stone-100 dark:bg-stone-800 rounded-xl flex items-center justify-center shrink-0">
                                    <i data-lucide="calendar" class="w-8 h-8 text-stone-400"></i>
                                </div>
                            @endif
                            <div class="flex-1">
                                <h4 class="text-lg font-bold text-stone-800 dark:text-stone-100">{{ $event->title }}</h4>
                                <p class="text-sm text-stone-600 dark:text-stone-400 mt-1">{{ Str::limit($event->description, 100) }}</p>
                                <div class="mt-3 flex flex-wrap gap-3 text-xs font-medium text-stone-500">
                                    <span class="flex items-center gap-1"><i data-lucide="clock" class="w-4 h-4"></i> {{ $event->event_date->format('M d, Y') }} at {{ $event->event_time->format('H:i') }}</span>
                                    <span class="flex items-center gap-1"><i data-lucide="map-pin" class="w-4 h-4"></i> {{ $event->is_online ? 'Online' : $event->location }}</span>
                                    <span class="flex items-center gap-1"><i data-lucide="users" class="w-4 h-4"></i> {{ $event->attendees_count }} / {{ $event->max_attendees ?: '∞' }} attending</span>
                                </div>
                            </div>
                            <div class="flex flex-col justify-end">
                                <a href="{{ route('events.show', $event) }}" class="px-4 py-2 border border-stone-200 dark:border-stone-700 rounded-xl text-sm text-stone-800 dark:text-stone-200 hover:bg-stone-50 dark:hover:bg-stone-800 transition text-center text-nowrap">View Event</a>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-10 bg-white dark:bg-stone-900 rounded-2xl shadow-sm border border-stone-100 dark:border-stone-800">
                            <i data-lucide="calendar-x" class="w-12 h-12 text-stone-300 mx-auto mb-3"></i>
                            <p class="text-stone-500">No upcoming meetings in this community.</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- Right Column: Community Info + Members --}}
        <div class="space-y-6">

            {{-- Community Info --}}
            <div class="bg-white dark:bg-stone-900 rounded-2xl shadow-md border border-stone-100 dark:border-stone-800">
                {{-- Community Image --}}
                <div class="relative h-56 sm:h-64 lg:h-72 rounded-t-2xl overflow-hidden bg-stone-100 dark:bg-stone-800">
                    <img src="{{ $community->image ? asset('storage/' . $community->image) : asset('images/community-default.jpg') }}"
                        class="w-full h-full object-cover" alt="{{ $community->name }}"
                        loading="lazy">
                </div>

                {{-- Community Info --}}
                <div class="p-5">
                    <h2 class="text-lg sm:text-xl font-semibold text-stone-800 dark:text-stone-100">{{ $community->name }}
                    </h2>
                    <p class="text-xs sm:text-sm text-stone-500 dark:text-stone-400 mt-1">
                        {{ $community->members()->count() }} members •
                        {{ $posts->total() ?? $community->communityPosts()->count() }} posts
                    </p>
                    <p class="text-sm sm:text-base text-stone-600 dark:text-stone-300 mt-2">{{ $community->description }}
                    </p>

                    {{-- Action Buttons --}}
                    <div class="mt-4 flex flex-col gap-2">
                        @auth
                            @if(auth()->id() === $community->created_by || $community->members()->where('user_id', auth()->id())->wherePivot('role', 'admin')->exists())
                                <a href="{{ route('communities.edit', $community) }}"
                                    class="w-full px-4 py-2 rounded-lg border border-amber-300 dark:border-amber-700 text-amber-700 dark:text-amber-400 font-semibold text-sm sm:text-base hover:bg-amber-50 dark:hover:bg-amber-950/30 text-center transition flex items-center justify-center gap-2">
                                    <i data-lucide="settings" class="w-4 h-4"></i>
                                    Community Settings / Edit
                                </a>
                            @endif
                        @endauth

                        @auth
                            @if($community->members->contains(auth()->id()))
                                <form action="{{ route('communities.leave', $community) }}" method="POST">
                                    @csrf @method('DELETE')
                                    <button type="submit"
                                        class="w-full px-4 py-2 rounded-lg border border-red-400 text-red-600 text-sm sm:text-base hover:bg-red-50 transition">
                                        Leave Community
                                    </button>
                                </form>
                            @else
                                <form action="{{ route('communities.join', $community) }}" method="POST">
                                    @csrf
                                    <button type="submit"
                                        class="w-full px-4 py-2 rounded-lg glass-orange text-white text-sm sm:text-base font-semibold transition">
                                        Join Community
                                    </button>
                                </form>
                            @endif
                        @endauth
                    </div>
                </div>
            </div>

            {{-- Members --}}
            <div class="bg-white dark:bg-stone-900 rounded-2xl shadow-md border border-stone-100 dark:border-stone-800 p-5">
                <h3 class="font-semibold text-stone-800 dark:text-stone-100 mb-3">Members</h3>
                <div class="grid grid-cols-4 gap-3">
                    @foreach($community->members->take(8) as $member)
                        <img src="{{ $member->profile_photo_url }}"
                            class="w-14 h-14 rounded-full mx-auto object-cover border border-stone-100 dark:border-stone-700">
                    @endforeach
                </div>
            </div>

        </div>
    </div>

    {{-- Scripts --}}
    <script src="https://unpkg.com/lucide@latest"></script>
    <script>
        document.addEventListener("DOMContentLoaded", () => { lucide.createIcons() });
        const csrfToken = '{{ csrf_token() }}';

        function toggleCommentsBox(postId) { document.getElementById(`comments-box-${postId}`).classList.toggle("hidden"); }

        function toggleCommunityTap(postId, btn) {
            if(btn.disabled) return;
            btn.disabled = true;
            
            // Interaction animation feedback
            const icon = btn.querySelector('i');
            icon.classList.add('scale-125');
            setTimeout(() => icon.classList.remove('scale-125'), 150);

            fetch(`/community-posts/${postId}/tap`, { method: 'POST', headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' } })
                .then(res => res.json()).then(data => {
                    const countEl = document.getElementById(`tap-count-${postId}`);
                    if (countEl) countEl.textContent = data.count;
                    if (data.tapped) { 
                        btn.classList.add('bg-red-50', 'text-red-600', 'dark:bg-red-900/20', 'dark:text-red-400');
                        btn.classList.remove('text-stone-600', 'dark:text-stone-400', 'hover:bg-stone-50', 'dark:hover:bg-stone-800');
                        icon.classList.add('fill-current', 'scale-110');
                        icon.classList.remove('scale-100');
                    }
                    else { 
                        btn.classList.remove('bg-red-50', 'text-red-600', 'dark:bg-red-900/20', 'dark:text-red-400');
                        btn.classList.add('text-stone-600', 'dark:text-stone-400', 'hover:bg-stone-50', 'dark:hover:bg-stone-800');
                        icon.classList.remove('fill-current', 'scale-110');
                        icon.classList.add('scale-100');
                    }
                }).finally(() => btn.disabled = false);
        }

        function submitComment(e, postId) {
            e.preventDefault();
            const input = document.getElementById(`comment-input-${postId}`);
            const content = input.value.trim();
            if (!content) return;
            input.disabled = true;

            fetch(`/community-posts/${postId}/comment`, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': csrfToken, 'Content-Type': 'application/json', 'Accept': 'application/json' },
                body: JSON.stringify({ content })
            }).then(res => res.json()).then(data => {
                const commentsBox = document.getElementById(`comments-box-${postId}`);
                const newComment = document.createElement('div');
                newComment.className = 'flex gap-3 items-start';
                newComment.innerHTML = `
                    <img src="${data.user.profile_photo_url || '/images/default-avatar.png'}" class="w-8 h-8 rounded-full">
                    <div class="bg-stone-50 rounded-2xl p-4 flex-1">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-stone-800">${data.user.name}</p>
                                <p class="text-xs text-stone-400">Just now</p>
                            </div>
                        </div>
                        <p class="mt-2 text-sm text-stone-700">${data.content}</p>
                    </div>
                `;
                commentsBox.prepend(newComment);
                document.getElementById(`comment-count-${postId}`).textContent = parseInt(document.getElementById(`comment-count-${postId}`).textContent) + 1;
                input.value = '';
            }).finally(() => input.disabled = false);
        }

        function previewImage(event, previewId) {
            const file = event.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function (e) {
                    const img = document.getElementById('image-preview-img');
                    img.src = e.target.result;
                    document.getElementById(previewId).classList.remove('hidden');
                }
                reader.readAsDataURL(file);
            }
        }

        function toggleReplyBox(commentId) { 
            const box = document.getElementById(`reply-form-${commentId}`);
            if(box) box.classList.toggle('hidden'); 
        }

        function submitReply(e, postId, parentId) {
            e.preventDefault();
            const input = document.getElementById(`reply-input-${parentId}`);
            const content = input.value.trim();
            if (!content) return;
            input.disabled = true;

            fetch(`/community-posts/${postId}/comment`, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': csrfToken, 'Content-Type': 'application/json', 'Accept': 'application/json' },
                body: JSON.stringify({ content, parent_id: parentId })
            }).then(res => res.json()).then(data => {
                const repliesBox = document.getElementById(`replies-box-${parentId}`);
                const newReply = document.createElement('div');
                newReply.className = 'flex gap-3 items-start';
                newReply.innerHTML = `
                    <img src="${data.user.profile_photo_url || '/images/default-avatar.png'}" class="w-7 h-7 rounded-full object-cover">
                    <div class="bg-stone-50 dark:bg-stone-800 rounded-2xl p-3 flex-1">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-stone-800 dark:text-stone-100">${data.user.name}</p>
                                <p class="text-xs text-stone-400 dark:text-stone-500">Just now</p>
                            </div>
                        </div>
                        <p class="mt-1 text-sm text-stone-700 dark:text-stone-300">${data.content}</p>
                    </div>
                `;
                repliesBox.appendChild(newReply);
                document.getElementById(`comment-count-${postId}`).textContent = parseInt(document.getElementById(`comment-count-${postId}`).textContent) + 1;
                input.value = '';
                toggleReplyBox(parentId);
            }).finally(() => input.disabled = false);
        }

        function switchCommunityTab(tab) {
            document.getElementById('section-feed').classList.add('hidden');
            document.getElementById('section-meetings').classList.add('hidden');
            
            document.getElementById('tab-feed').classList.remove('border-amber-500', 'text-amber-600', 'dark:text-amber-500');
            document.getElementById('tab-feed').classList.add('border-transparent', 'text-stone-500');
            document.getElementById('tab-meetings').classList.remove('border-amber-500', 'text-amber-600', 'dark:text-amber-500');
            document.getElementById('tab-meetings').classList.add('border-transparent', 'text-stone-500');

            document.getElementById(`section-${tab}`).classList.remove('hidden');
            document.getElementById(`tab-${tab}`).classList.remove('border-transparent', 'text-stone-500');
            document.getElementById(`tab-${tab}`).classList.add('border-amber-500', 'text-amber-600', 'dark:text-amber-500');
        }
    </script>
@endsection