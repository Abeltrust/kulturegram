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

                            <!-- Body -->
                            <div class="mb-4">
                                <div class="text-stone-700 dark:text-stone-300 leading-relaxed text-sm sm:text-base">
                                    {!! $post->formatted_content !!}
                                </div>

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
                            <div id="resonance-box-{{ $post->id }}" class="hidden mt-4">
                                <div class="space-y-2 sm:space-y-3">
                                    @foreach($post->resonances as $resonance)
                                        <div class="p-2 sm:p-3 bg-stone-50 dark:bg-stone-800/50 rounded-lg">
                                            <span
                                                class="font-medium text-stone-800 dark:text-stone-200 text-xs sm:text-sm text-strong">{{ $resonance->user->name }}</span>
                                            <div class="text-stone-700 dark:text-stone-400 text-xs sm:text-sm leading-snug">
                                                {!! $resonance->formatted_content !!}</div>
                                            <span
                                                class="text-[10px] sm:text-xs text-stone-500 dark:text-stone-500">{{ $resonance->created_at->diffForHumans() }}</span>
                                        </div>
                                    @endforeach
                                </div>

                                @auth
                                    <div class="flex items-center mt-2 sm:mt-3 space-x-1 sm:space-x-2">
                                        <input type="text" id="resonance-input-{{ $post->id }}" placeholder="Write a resonance..."
                                            class="flex-1 border dark:border-stone-700 bg-white dark:bg-stone-900 dark:text-stone-200 rounded-lg px-2 sm:px-3 py-1.5 sm:py-2 text-xs sm:text-sm focus:ring-2 focus:ring-amber-500 focus:outline-none">
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
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ content })
            })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        const box = document.querySelector(`#resonance-box-${postId} .space-y-2, #resonance-box-${postId} .space-y-3`);
                        if (box) {
                            box.insertAdjacentHTML('beforeend', `
                                        <div class="p-2 sm:p-3 bg-stone-50 dark:bg-stone-800/50 rounded-lg">
                                            <span class="font-medium text-stone-800 dark:text-stone-200 text-xs sm:text-sm">${data.resonance.user.name}</span>
                                            <div class="text-stone-700 dark:text-stone-400 text-xs sm:text-sm leading-snug">${data.resonance.formatted_content || data.resonance.content}</div>
                                            <span class="text-[10px] sm:text-xs text-stone-500 dark:text-stone-500">${data.resonance.time}</span>
                                        </div>
                                    `);
                        }
                        input.value = '';
                        document.querySelector(`[data-resonance-count="${postId}"]`).innerText = `${data.count} Resonance`;
                        lucide.createIcons(); // re-render icon if needed
                    }
                })
                .catch(err => console.error('Error:', err));
        }

    </script>
@endpush