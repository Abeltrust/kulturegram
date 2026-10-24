{{-- resources/views/profile/index.blade.php --}}
@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-stone-50 dark:bg-stone-950 pb-16" x-data="{ 
    activeTab: 'timeline',
    showChapterModal: false,
    showPhotoModal: false,
    avatarPreview: null,
    coverPreview: null
}">
    <div class="max-w-6xl mx-auto px-3 sm:px-6 pt-4 sm:pt-6 space-y-6">

        {{-- Session Flash Notifications --}}
        @if(session('success'))
            <div class="p-4 rounded-2xl bg-green-50 dark:bg-green-950/40 border border-green-200 dark:border-green-800 text-green-700 dark:text-green-300 text-sm font-semibold flex items-center justify-between shadow-sm animate-fade-in">
                <div class="flex items-center gap-2">
                    <i data-lucide="check-circle" class="w-5 h-5 text-green-500"></i>
                    <span>{{ session('success') }}</span>
                </div>
                <button type="button" @click="$el.parentElement.remove()" class="text-green-500 hover:text-green-700">
                    <i data-lucide="x" class="w-4 h-4"></i>
                </button>
            </div>
        @endif

        {{-- Profile Hero Card --}}
        <div class="bg-white dark:bg-stone-900 rounded-3xl shadow-sm border border-stone-200/80 dark:border-stone-800 overflow-hidden relative">
            
            {{-- Cover Banner --}}
            <div class="w-full h-36 sm:h-64 relative bg-gradient-to-r from-amber-500 via-orange-600 to-rose-600 overflow-hidden group">
                @if($user->cover_photo_url)
                    <img id="profile-cover-display" src="{{ $user->cover_photo_url }}" class="w-full h-full object-cover">
                @else
                    {{-- Heritage Geometric Motif Overlay --}}
                    <div class="absolute inset-0 opacity-20 bg-[radial-gradient(#fff_1px,transparent_1px)] [background-size:16px_16px]"></div>
                @endif
                <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-transparent to-black/20"></div>

                {{-- Cover Photo Upload Button (Owner Only) --}}
                @auth
                    @if(auth()->id() === $user->id)
                        <form id="cover-form" action="{{ route('profile.cover.upload') }}" method="POST" enctype="multipart/form-data" class="absolute top-4 right-4 z-20">
                            @csrf
                            <label class="cursor-pointer inline-flex items-center gap-2 px-3 sm:px-4 py-2 bg-black/50 hover:bg-black/70 backdrop-blur-md text-white rounded-xl text-xs sm:text-sm font-bold border border-white/20 transition-all shadow-lg active:scale-95">
                                <i data-lucide="image" class="w-4 h-4"></i>
                                <span class="hidden sm:inline">Change Banner</span>
                                <input type="file" name="cover_photo" class="hidden" accept="image/*" onchange="document.getElementById('cover-form').submit()">
                            </label>
                        </form>
                    @endif
                @endauth
            </div>

            {{-- Profile Details Section --}}
            <div class="px-4 sm:px-8 pb-6 sm:pb-8 pt-0 relative">
                <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between -mt-16 sm:-mt-20 gap-4 mb-6">
                    
                    {{-- Avatar & Core Info --}}
                    <div class="flex flex-col sm:flex-row sm:items-end gap-4 text-center sm:text-left items-center sm:items-start">
                        
                        {{-- Avatar with Upload Trigger --}}
                        <div class="relative w-28 h-28 sm:w-40 sm:h-40 rounded-3xl border-4 border-white dark:border-stone-900 shadow-xl overflow-hidden bg-stone-100 dark:bg-stone-800 flex-shrink-0 group z-10">
                            <img id="profile-avatar-display" src="{{ $user->profile_photo_url }}" alt="{{ $user->name }}" class="w-full h-full object-cover">

                            {{-- Avatar Upload Form (Owner Only) --}}
                            @auth
                                @if(auth()->id() === $user->id)
                                    <form id="avatar-form" action="{{ route('profile.photo.upload') }}" method="POST" enctype="multipart/form-data"
                                        class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity flex flex-col items-center justify-center cursor-pointer text-white">
                                        @csrf
                                        <label class="w-full h-full flex flex-col items-center justify-center cursor-pointer">
                                            <i data-lucide="camera" class="w-6 h-6 mb-1"></i>
                                            <span class="text-[11px] font-bold">Update Photo</span>
                                            <input type="file" name="profile_photo" class="hidden" accept="image/*" onchange="document.getElementById('avatar-form').submit()">
                                        </label>
                                    </form>
                                @endif
                            @endauth
                        </div>

                        {{-- Name, Username & Tags --}}
                        <div class="space-y-1 sm:mb-2">
                            <div class="flex flex-wrap items-center justify-center sm:justify-start gap-2">
                                <h1 class="text-xl sm:text-3xl font-black text-stone-900 dark:text-white tracking-tight">
                                    {{ $user->name }}
                                </h1>
                                @if($user->is_admin)
                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider bg-amber-500/10 text-amber-600 dark:text-amber-400 border border-amber-500/20 flex items-center gap-1">
                                        <i data-lucide="shield" class="w-3 h-3"></i> Guardian
                                    </span>
                                @endif
                            </div>
                            <p class="text-xs sm:text-sm font-semibold text-stone-500 dark:text-stone-400">
                                {{ '@' . ($user->username ?: 'kulture_' . $user->id) }}
                            </p>
                        </div>
                    </div>

                    {{-- Action Buttons --}}
                    <div class="flex flex-wrap items-center justify-center sm:justify-end gap-2.5 z-10">
                        @auth
                            @if(auth()->id() === $user->id)
                                <a href="{{ route('settings.index', $user->id) }}"
                                    class="inline-flex items-center gap-2 px-5 py-2.5 bg-stone-900 hover:bg-stone-800 dark:bg-white dark:hover:bg-stone-100 text-white dark:text-stone-900 rounded-xl text-xs sm:text-sm font-bold shadow-md hover:shadow-lg transition-all active:scale-95">
                                    <i data-lucide="settings" class="w-4 h-4"></i>
                                    Account Settings
                                </a>
                                <a href="{{ route('privacy') }}"
                                    class="inline-flex items-center gap-1.5 px-4 py-2.5 bg-stone-100 hover:bg-stone-200 dark:bg-stone-800 dark:hover:bg-stone-700 text-stone-700 dark:text-stone-300 rounded-xl text-xs sm:text-sm font-semibold transition-all">
                                    <i data-lucide="shield-check" class="w-4 h-4 text-amber-500"></i>
                                    Privacy
                                </a>
                            @else
                                {{-- Vibe Button --}}
                                <button onclick="toggleUserTap({{ $user->id }})" id="profile-vibe-btn"
                                    class="flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl text-xs sm:text-sm font-bold transition-all shadow-sm
                                    {{ isset($hasVibed) && $hasVibed ? 'bg-amber-100 dark:bg-amber-950/60 text-amber-700 dark:text-amber-400 border border-amber-200 dark:border-amber-800' : 'bg-white dark:bg-stone-800 border border-stone-200 dark:border-stone-700 text-stone-700 dark:text-stone-300 hover:bg-stone-50' }}">
                                    <i data-lucide="zap" id="profile-vibe-icon" class="w-4 h-4 {{ isset($hasVibed) && $hasVibed ? 'fill-current text-amber-500' : '' }}"></i>
                                    <span id="profile-vibe-text">{{ isset($hasVibed) && $hasVibed ? 'Vibed' : 'Vibe' }}</span>
                                </button>

                                {{-- Lock-In Button --}}
                                <button onclick="toggleUserLockIn({{ $user->id }})" id="profile-lockin-btn"
                                    class="flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl text-xs sm:text-sm font-bold transition-all shadow-md active:scale-95
                                    {{ isset($isLockedIn) && $isLockedIn ? 'bg-stone-100 dark:bg-stone-800 text-stone-700 dark:text-stone-300 hover:bg-red-50 dark:hover:bg-red-950/30 hover:text-red-600 border border-stone-200 dark:border-stone-700' : 'bg-gradient-to-r from-amber-500 to-orange-600 text-white hover:opacity-95' }}">
                                    <i data-lucide="{{ isset($isLockedIn) && $isLockedIn ? 'user-check' : 'user-plus' }}" class="w-4 h-4"></i>
                                    <span id="profile-lockin-text">{{ isset($isLockedIn) && $isLockedIn ? 'Locked In' : 'Lock In' }}</span>
                                </button>

                                {{-- Direct Message Button --}}
                                @if(isset($isLockedIn) && $isLockedIn)
                                    <a href="{{ route('messages.start', $user->id) }}"
                                        class="flex items-center justify-center gap-2 px-4 py-2.5 bg-stone-900 text-white dark:bg-white dark:text-stone-900 rounded-xl text-xs sm:text-sm font-bold shadow-md hover:opacity-90 transition-all">
                                        <i data-lucide="message-square" class="w-4 h-4"></i>
                                        <span>Message</span>
                                    </a>
                                @endif
                            @endif
                        @endauth
                    </div>
                </div>

                {{-- Bio & Metadata Bar --}}
                <div class="space-y-4 pt-2 border-t border-stone-100 dark:border-stone-800">
                    <p class="text-sm sm:text-base text-stone-700 dark:text-stone-300 leading-relaxed max-w-3xl">
                        {{ $user->bio ?: 'Cultural preservationist and heritage custodian documenting stories, traditions, and generational memories on Kulturegram.' }}
                    </p>

                    <div class="flex flex-wrap items-center gap-y-2 gap-x-4 text-xs sm:text-sm text-stone-500 dark:text-stone-400">
                        @if($user->cultural_background)
                            <div class="flex items-center gap-1.5 font-medium text-amber-600 dark:text-amber-400">
                                <i data-lucide="globe" class="w-4 h-4"></i>
                                <span>{{ $user->cultural_background }}</span>
                            </div>
                        @endif

                        @if($user->location)
                            <div class="flex items-center gap-1.5">
                                <i data-lucide="map-pin" class="w-4 h-4 text-stone-400"></i>
                                <span>{{ $user->location }}</span>
                            </div>
                        @endif

                        @if($user->website)
                            <a href="{{ $user->website }}" target="_blank" rel="noopener noreferrer"
                                class="flex items-center gap-1.5 text-blue-600 dark:text-blue-400 hover:underline">
                                <i data-lucide="link" class="w-4 h-4"></i>
                                <span>{{ parse_url($user->website, PHP_URL_HOST) ?: $user->website }}</span>
                            </a>
                        @endif

                        <div class="flex items-center gap-1.5">
                            <i data-lucide="calendar" class="w-4 h-4 text-stone-400"></i>
                            <span>Joined {{ $user->created_at->format('M Y') }}</span>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        {{-- Metrics & Impact Dashboard Cards --}}
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3 sm:gap-4">
            
            <div class="bg-white dark:bg-stone-900 rounded-2xl p-4 border border-stone-200/80 dark:border-stone-800 text-center shadow-sm">
                <p class="text-2xl font-black text-stone-900 dark:text-white">{{ $stats['stories'] }}</p>
                <p class="text-[11px] font-bold text-stone-400 dark:text-stone-500 uppercase tracking-wider mt-0.5">Stories</p>
            </div>

            <div class="bg-white dark:bg-stone-900 rounded-2xl p-4 border border-stone-200/80 dark:border-stone-800 text-center shadow-sm">
                <p class="text-2xl font-black text-stone-900 dark:text-white" id="profile-locked-in-count">{{ $stats['locked_in'] }}</p>
                <p class="text-[11px] font-bold text-stone-400 dark:text-stone-500 uppercase tracking-wider mt-0.5">Locked In</p>
            </div>

            <div class="bg-white dark:bg-stone-900 rounded-2xl p-4 border border-stone-200/80 dark:border-stone-800 text-center shadow-sm">
                <p class="text-2xl font-black text-amber-500" id="profile-vibes-count">{{ $stats['vibes'] }}</p>
                <p class="text-[11px] font-bold text-stone-400 dark:text-stone-500 uppercase tracking-wider mt-0.5">Vibes / Taps</p>
            </div>

            <div class="bg-white dark:bg-stone-900 rounded-2xl p-4 border border-stone-200/80 dark:border-stone-800 text-center shadow-sm">
                <p class="text-2xl font-black text-orange-500">{{ $stats['contributions'] }}</p>
                <p class="text-[11px] font-bold text-stone-400 dark:text-stone-500 uppercase tracking-wider mt-0.5">Traditions</p>
            </div>

            <div class="bg-white dark:bg-stone-900 rounded-2xl p-4 border border-stone-200/80 dark:border-stone-800 text-center shadow-sm">
                <p class="text-2xl font-black text-purple-500">{{ $stats['vault'] }}</p>
                <p class="text-[11px] font-bold text-stone-400 dark:text-stone-500 uppercase tracking-wider mt-0.5">Vault Items</p>
            </div>

            <div class="bg-white dark:bg-stone-900 rounded-2xl p-4 border border-stone-200/80 dark:border-stone-800 text-center shadow-sm">
                <p class="text-2xl font-black text-emerald-500">{{ $stats['preservation_score'] }}%</p>
                <p class="text-[11px] font-bold text-stone-400 dark:text-stone-500 uppercase tracking-wider mt-0.5">Preservation</p>
            </div>

        </div>

        {{-- Interactive Tab Navigation --}}
        <div class="bg-white dark:bg-stone-900 rounded-3xl shadow-sm border border-stone-200/80 dark:border-stone-800 p-2 sm:p-3">
            <div class="flex items-center gap-1 sm:gap-2 overflow-x-auto scrollbar-hide py-1">
                
                <button @click="activeTab = 'timeline'"
                    :class="activeTab === 'timeline' ? 'bg-amber-500 text-white shadow-md' : 'text-stone-600 dark:text-stone-400 hover:bg-stone-100 dark:hover:bg-stone-800'"
                    class="px-4 py-2.5 rounded-2xl text-xs sm:text-sm font-bold transition-all whitespace-nowrap flex items-center gap-2">
                    <i data-lucide="clock" class="w-4 h-4"></i>
                    <span>Stories Feed</span>
                    <span class="px-1.5 py-0.5 rounded-full text-[10px]" :class="activeTab === 'timeline' ? 'bg-white/20 text-white' : 'bg-stone-100 dark:bg-stone-800 text-stone-500'">{{ $stats['stories'] }}</span>
                </button>

                <button @click="activeTab = 'chapters'"
                    :class="activeTab === 'chapters' ? 'bg-amber-500 text-white shadow-md' : 'text-stone-600 dark:text-stone-400 hover:bg-stone-100 dark:hover:bg-stone-800'"
                    class="px-4 py-2.5 rounded-2xl text-xs sm:text-sm font-bold transition-all whitespace-nowrap flex items-center gap-2">
                    <i data-lucide="book-open" class="w-4 h-4"></i>
                    <span>Life Chapters</span>
                    <span class="px-1.5 py-0.5 rounded-full text-[10px]" :class="activeTab === 'chapters' ? 'bg-white/20 text-white' : 'bg-stone-100 dark:bg-stone-800 text-stone-500'">{{ $lifeChapters->count() }}</span>
                </button>

                <button @click="activeTab = 'cultures'"
                    :class="activeTab === 'cultures' ? 'bg-amber-500 text-white shadow-md' : 'text-stone-600 dark:text-stone-400 hover:bg-stone-100 dark:hover:bg-stone-800'"
                    class="px-4 py-2.5 rounded-2xl text-xs sm:text-sm font-bold transition-all whitespace-nowrap flex items-center gap-2">
                    <i data-lucide="archive" class="w-4 h-4"></i>
                    <span>Cultural Stories</span>
                    <span class="px-1.5 py-0.5 rounded-full text-[10px]" :class="activeTab === 'cultures' ? 'bg-white/20 text-white' : 'bg-stone-100 dark:bg-stone-800 text-stone-500'">{{ $cultures->count() }}</span>
                </button>

                <button @click="activeTab = 'communities'"
                    :class="activeTab === 'communities' ? 'bg-amber-500 text-white shadow-md' : 'text-stone-600 dark:text-stone-400 hover:bg-stone-100 dark:hover:bg-stone-800'"
                    class="px-4 py-2.5 rounded-2xl text-xs sm:text-sm font-bold transition-all whitespace-nowrap flex items-center gap-2">
                    <i data-lucide="users" class="w-4 h-4"></i>
                    <span>Communities</span>
                    <span class="px-1.5 py-0.5 rounded-full text-[10px]" :class="activeTab === 'communities' ? 'bg-white/20 text-white' : 'bg-stone-100 dark:bg-stone-800 text-stone-500'">{{ $communities->count() }}</span>
                </button>

                <button @click="activeTab = 'events'"
                    :class="activeTab === 'events' ? 'bg-amber-500 text-white shadow-md' : 'text-stone-600 dark:text-stone-400 hover:bg-stone-100 dark:hover:bg-stone-800'"
                    class="px-4 py-2.5 rounded-2xl text-xs sm:text-sm font-bold transition-all whitespace-nowrap flex items-center gap-2">
                    <i data-lucide="calendar" class="w-4 h-4"></i>
                    <span>Gatherings</span>
                    <span class="px-1.5 py-0.5 rounded-full text-[10px]" :class="activeTab === 'events' ? 'bg-white/20 text-white' : 'bg-stone-100 dark:bg-stone-800 text-stone-500'">{{ $organizedEvents->count() + $attendingEvents->count() }}</span>
                </button>

                @if(auth()->id() === $user->id || $vaultItems->count() > 0)
                    <button @click="activeTab = 'vault'"
                        :class="activeTab === 'vault' ? 'bg-amber-500 text-white shadow-md' : 'text-stone-600 dark:text-stone-400 hover:bg-stone-100 dark:hover:bg-stone-800'"
                        class="px-4 py-2.5 rounded-2xl text-xs sm:text-sm font-bold transition-all whitespace-nowrap flex items-center gap-2">
                        <i data-lucide="lock" class="w-4 h-4"></i>
                        <span>Heritage Vault</span>
                        <span class="px-1.5 py-0.5 rounded-full text-[10px]" :class="activeTab === 'vault' ? 'bg-white/20 text-white' : 'bg-stone-100 dark:bg-stone-800 text-stone-500'">{{ $vaultCount }}</span>
                    </button>
                @endif

                @if(auth()->id() === $user->id)
                    <button @click="activeTab = 'privacy_hub'"
                        :class="activeTab === 'privacy_hub' ? 'bg-amber-500 text-white shadow-md' : 'text-stone-600 dark:text-stone-400 hover:bg-stone-100 dark:hover:bg-stone-800'"
                        class="px-4 py-2.5 rounded-2xl text-xs sm:text-sm font-bold transition-all whitespace-nowrap flex items-center gap-2">
                        <i data-lucide="shield" class="w-4 h-4"></i>
                        <span>Privacy & Sovereignty</span>
                    </button>
                @endif

            </div>
        </div>

        {{-- TAB 1: STORIES / TIMELINE FEED --}}
        <div x-show="activeTab === 'timeline'" class="space-y-4">
            
            {{-- Quick Post Trigger (Owner Only) --}}
            @auth
                @if(auth()->id() === $user->id)
                    <div class="bg-white dark:bg-stone-900 rounded-3xl p-5 border border-stone-200/80 dark:border-stone-800 shadow-sm flex items-center gap-4">
                        <img src="{{ $user->profile_photo_url }}" class="w-11 h-11 rounded-2xl object-cover">
                        <button onclick="document.querySelector('[data-modal-target=\'create-post-modal\']')?.click() || window.location.href='{{ route('timeline.index') }}'"
                            class="flex-1 text-left px-5 py-3 rounded-2xl bg-stone-100 dark:bg-stone-800 hover:bg-stone-200 dark:hover:bg-stone-700/80 text-stone-500 dark:text-stone-400 text-sm font-medium transition">
                            Record a cultural memory or authentic story...
                        </button>
                        <a href="{{ route('timeline.index') }}" class="px-4 py-3 bg-amber-500 hover:bg-amber-600 text-white rounded-2xl text-xs sm:text-sm font-bold transition flex items-center gap-1.5 shrink-0">
                            <i data-lucide="plus" class="w-4 h-4"></i>
                            <span class="hidden sm:inline">Post</span>
                        </a>
                    </div>
                @endif
            @endauth

            {{-- Posts List --}}
            @if($posts->count() > 0)
                <div class="space-y-4">
                    @foreach($posts as $post)
                        <div class="bg-white dark:bg-stone-900 rounded-3xl p-6 border border-stone-200/80 dark:border-stone-800 shadow-sm space-y-4">
                            
                            {{-- Post Author Header --}}
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <img src="{{ $user->profile_photo_url }}" class="w-10 h-10 rounded-xl object-cover">
                                    <div>
                                        <h3 class="font-bold text-stone-900 dark:text-white text-sm">{{ $user->name }}</h3>
                                        <p class="text-xs text-stone-400">{{ $post->created_at->diffForHumans() }}</p>
                                    </div>
                                </div>
                                @if($post->chapter)
                                    <span class="px-3 py-1 rounded-full text-xs font-bold bg-amber-50 dark:bg-amber-950/40 text-amber-600 dark:text-amber-400 border border-amber-200 dark:border-amber-900">
                                        {{ $post->chapter }}
                                    </span>
                                @endif
                            </div>

                            {{-- Post Content --}}
                            <div class="text-stone-800 dark:text-stone-200 text-sm sm:text-base whitespace-pre-line leading-relaxed">
                                {{ $post->content }}
                            </div>

                            {{-- Media Attachment --}}
                            @if($post->image)
                                <div class="rounded-2xl overflow-hidden max-h-96 bg-stone-100 dark:bg-stone-800">
                                    <img src="{{ asset('storage/' . $post->image) }}" class="w-full h-full object-cover">
                                </div>
                            @endif

                            {{-- Video Attachment --}}
                            @if($post->video)
                                <div class="rounded-2xl overflow-hidden max-h-96 bg-black">
                                    <video src="{{ asset('storage/' . $post->video) }}" controls class="w-full max-h-96"></video>
                                </div>
                            @endif

                            {{-- Audio Attachment --}}
                            @if($post->audio)
                                <div class="p-3 rounded-2xl bg-stone-100 dark:bg-stone-800">
                                    <audio src="{{ asset('storage/' . $post->audio) }}" controls class="w-full"></audio>
                                </div>
                            @endif

                            {{-- Reactions Bar --}}
                            <div class="pt-3 border-t border-stone-100 dark:border-stone-800 flex items-center justify-between text-xs text-stone-500 dark:text-stone-400">
                                <div class="flex items-center gap-4">
                                    <span class="flex items-center gap-1.5 font-bold text-amber-500">
                                        <i data-lucide="zap" class="w-4 h-4 fill-current"></i>
                                        {{ $post->taps_count ?? 0 }} Taps
                                    </span>
                                    <span class="flex items-center gap-1.5 font-bold text-purple-500">
                                        <i data-lucide="radio" class="w-4 h-4"></i>
                                        {{ $post->resonance_count ?? 0 }} Resonance
                                    </span>
                                    <span class="flex items-center gap-1.5">
                                        <i data-lucide="message-square" class="w-4 h-4"></i>
                                        {{ $post->comments->count() }} Comments
                                    </span>
                                </div>
                                <a href="{{ route('timeline.index') }}" class="hover:text-amber-500 font-bold">View in Timeline</a>
                            </div>

                        </div>
                    @endforeach

                    {{-- Pagination --}}
                    <div class="pt-4">
                        {{ $posts->links() }}
                    </div>
                </div>
            @else
                <div class="bg-white dark:bg-stone-900 rounded-3xl p-12 text-center border border-stone-200/80 dark:border-stone-800 space-y-3">
                    <div class="w-16 h-16 rounded-3xl bg-amber-50 dark:bg-amber-950/40 text-amber-500 mx-auto flex items-center justify-center">
                        <i data-lucide="feather" class="w-8 h-8"></i>
                    </div>
                    <h3 class="text-lg font-bold text-stone-900 dark:text-white">No Stories Preserved Yet</h3>
                    <p class="text-xs sm:text-sm text-stone-500 max-w-sm mx-auto">Document authentic memories, family accounts, and milestones to create your living heritage timeline.</p>
                </div>
            @endif

        </div>

        {{-- TAB 2: LIFE CHAPTERS --}}
        <div x-show="activeTab === 'chapters'" class="space-y-4">
            
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-lg sm:text-xl font-bold text-stone-900 dark:text-white">Life Chapters & Milestones</h2>
                    <p class="text-xs sm:text-sm text-stone-500">Chronological narrative of defining cultural eras and historical passages.</p>
                </div>
                @auth
                    @if(auth()->id() === $user->id)
                        <button @click="showChapterModal = true"
                            class="px-4 py-2.5 bg-amber-500 hover:bg-amber-600 text-white rounded-xl text-xs sm:text-sm font-bold transition flex items-center gap-2 shadow-sm">
                            <i data-lucide="plus" class="w-4 h-4"></i>
                            <span>Add Chapter</span>
                        </button>
                    @endif
                @endauth
            </div>

            @if($lifeChapters->count() > 0)
                <div class="space-y-4 relative before:absolute before:inset-0 before:left-5 sm:before:left-7 before:w-0.5 before:bg-stone-200 dark:before:bg-stone-800">
                    @foreach($lifeChapters as $chapter)
                        <div class="relative flex items-start gap-4 sm:gap-6 group">
                            
                            {{-- Milestone Marker --}}
                            <div class="w-10 h-10 sm:w-14 sm:h-14 rounded-2xl bg-amber-500 text-white flex items-center justify-center font-bold text-xs sm:text-sm shadow-md shrink-0 z-10">
                                <i data-lucide="bookmark" class="w-5 h-5 sm:w-6 sm:h-6"></i>
                            </div>

                            {{-- Chapter Card --}}
                            <div class="flex-1 bg-white dark:bg-stone-900 rounded-3xl p-5 sm:p-6 border border-stone-200/80 dark:border-stone-800 shadow-sm space-y-2">
                                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-1">
                                    <h3 class="font-black text-base sm:text-lg text-stone-900 dark:text-white">
                                        {{ $chapter->title }}
                                    </h3>
                                    <span class="text-xs font-bold px-3 py-1 rounded-full bg-stone-100 dark:bg-stone-800 text-stone-600 dark:text-stone-300 w-fit">
                                        {{ $chapter->period ?: 'Historical Era' }}
                                    </span>
                                </div>

                                @if($chapter->location)
                                    <p class="text-xs font-semibold text-amber-600 dark:text-amber-400 flex items-center gap-1">
                                        <i data-lucide="map-pin" class="w-3.5 h-3.5"></i>
                                        {{ $chapter->location }}
                                    </p>
                                @endif

                                @if($chapter->description)
                                    <p class="text-sm text-stone-600 dark:text-stone-300 leading-relaxed pt-1">
                                        {{ $chapter->description }}
                                    </p>
                                @endif

                                @auth
                                    @if(auth()->id() === $user->id)
                                        <div class="pt-3 flex justify-end">
                                            <form action="{{ route('profile.chapters.destroy', $chapter) }}" method="POST" onsubmit="return confirm('Remove this life chapter?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-xs text-red-500 hover:text-red-700 font-semibold flex items-center gap-1">
                                                    <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                                    Delete Chapter
                                                </button>
                                            </form>
                                        </div>
                                    @endif
                                @endauth
                            </div>

                        </div>
                    @endforeach
                </div>
            @else
                <div class="bg-white dark:bg-stone-900 rounded-3xl p-12 text-center border border-stone-200/80 dark:border-stone-800 space-y-3">
                    <div class="w-16 h-16 rounded-3xl bg-amber-50 dark:bg-amber-950/40 text-amber-500 mx-auto flex items-center justify-center">
                        <i data-lucide="book" class="w-8 h-8"></i>
                    </div>
                    <h3 class="text-lg font-bold text-stone-900 dark:text-white">No Life Chapters Added Yet</h3>
                    <p class="text-xs sm:text-sm text-stone-500 max-w-sm mx-auto">Organize your journey into memorable milestones, migrations, and traditional rites of passage.</p>
                </div>
            @endif

        </div>

        {{-- TAB 3: CULTURAL STORIES & HUB CONTRIBUTIONS --}}
        <div x-show="activeTab === 'cultures'" class="space-y-4">
            
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-lg sm:text-xl font-bold text-stone-900 dark:text-white">Cultural Stories & Traditions</h2>
                    <p class="text-xs sm:text-sm text-stone-500">Documented oral histories, folkloric practices, and sacred knowledge submitted to Kulturegram.</p>
                </div>
                <a href="{{ route('cultural-hub.create') }}"
                    class="px-4 py-2.5 bg-amber-500 hover:bg-amber-600 text-white rounded-xl text-xs sm:text-sm font-bold transition flex items-center gap-2 shadow-sm">
                    <i data-lucide="plus" class="w-4 h-4"></i>
                    <span>Submit Tradition</span>
                </a>
            </div>

            @if($cultures->count() > 0)
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach($cultures as $culture)
                        <div class="bg-white dark:bg-stone-900 rounded-3xl p-5 border border-stone-200/80 dark:border-stone-800 shadow-sm flex flex-col justify-between space-y-4 hover:border-amber-500/40 transition">
                            
                            <div class="space-y-2">
                                <div class="flex items-center justify-between">
                                    <span class="text-[10px] font-black uppercase tracking-wider px-2.5 py-1 rounded-full bg-stone-100 dark:bg-stone-800 text-stone-600 dark:text-stone-300">
                                        {{ $culture->category }}
                                    </span>
                                    
                                    {{-- Status Badge --}}
                                    @if($culture->status === 'approved')
                                        <span class="text-[10px] font-black uppercase tracking-wider px-2.5 py-1 rounded-full bg-emerald-50 text-emerald-600 dark:bg-emerald-950/40 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800 flex items-center gap-1">
                                            <i data-lucide="check-circle" class="w-3 h-3"></i> Approved
                                        </span>
                                    @elseif($culture->status === 'featured')
                                        <span class="text-[10px] font-black uppercase tracking-wider px-2.5 py-1 rounded-full bg-purple-50 text-purple-600 dark:bg-purple-950/40 dark:text-purple-400 border border-purple-200 dark:border-purple-800 flex items-center gap-1">
                                            <i data-lucide="sparkles" class="w-3 h-3"></i> Featured
                                        </span>
                                    @else
                                        <span class="text-[10px] font-black uppercase tracking-wider px-2.5 py-1 rounded-full bg-amber-50 text-amber-600 dark:bg-amber-950/40 dark:text-amber-400 border border-amber-200 dark:border-amber-800 flex items-center gap-1">
                                            <i data-lucide="clock" class="w-3 h-3"></i> Under Review
                                        </span>
                                    @endif
                                </div>

                                <h3 class="font-bold text-base text-stone-900 dark:text-white line-clamp-1">
                                    {{ $culture->name }}
                                </h3>

                                <p class="text-xs text-stone-500 dark:text-stone-400 flex items-center gap-1">
                                    <i data-lucide="map-pin" class="w-3.5 h-3.5"></i>
                                    {{ $culture->region }}
                                </p>

                                <p class="text-xs text-stone-600 dark:text-stone-300 line-clamp-3 leading-relaxed">
                                    {{ $culture->description }}
                                </p>
                            </div>

                            <div class="pt-3 border-t border-stone-100 dark:border-stone-800 flex items-center justify-between">
                                <a href="{{ route('cultural-hub.show', $culture) }}" class="text-xs font-bold text-amber-600 dark:text-amber-400 hover:underline">
                                    View Story
                                </a>

                                @auth
                                    @if(auth()->id() === $culture->submitted_by)
                                        <a href="{{ route('cultural-hub.edit', $culture) }}" class="text-xs font-semibold text-stone-500 hover:text-stone-900 dark:hover:text-white flex items-center gap-1">
                                            <i data-lucide="edit-3" class="w-3.5 h-3.5"></i>
                                            Edit
                                        </a>
                                    @endif
                                @endauth
                            </div>

                        </div>
                    @endforeach
                </div>
            @else
                <div class="bg-white dark:bg-stone-900 rounded-3xl p-12 text-center border border-stone-200/80 dark:border-stone-800 space-y-3">
                    <div class="w-16 h-16 rounded-3xl bg-amber-50 dark:bg-amber-950/40 text-amber-500 mx-auto flex items-center justify-center">
                        <i data-lucide="award" class="w-8 h-8"></i>
                    </div>
                    <h3 class="text-lg font-bold text-stone-900 dark:text-white">No Cultural Stories Submitted</h3>
                    <p class="text-xs sm:text-sm text-stone-500 max-w-sm mx-auto">Document authentic dances, recipes, dialects, crafts, or rituals to preserve them for future generations.</p>
                </div>
            @endif

        </div>

        {{-- TAB 4: COMMUNITIES & TRIBES --}}
        <div x-show="activeTab === 'communities'" class="space-y-4">
            
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-lg sm:text-xl font-bold text-stone-900 dark:text-white">Tribes & Communities</h2>
                    <p class="text-xs sm:text-sm text-stone-500">Cultural circles and preservation collectives you participate in.</p>
                </div>
                <a href="{{ route('communities.create') }}"
                    class="px-4 py-2.5 bg-amber-500 hover:bg-amber-600 text-white rounded-xl text-xs sm:text-sm font-bold transition flex items-center gap-2 shadow-sm">
                    <i data-lucide="plus" class="w-4 h-4"></i>
                    <span>Create Tribe</span>
                </a>
            </div>

            @if($communities->count() > 0)
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach($communities as $community)
                        <div class="bg-white dark:bg-stone-900 rounded-3xl p-5 border border-stone-200/80 dark:border-stone-800 shadow-sm flex flex-col justify-between space-y-4 hover:border-amber-500/40 transition">
                            
                            <div class="flex items-start gap-3">
                                <div class="w-12 h-12 rounded-2xl bg-amber-100 dark:bg-amber-950/60 text-amber-600 flex items-center justify-center shrink-0 overflow-hidden">
                                    @if($community->image)
                                        <img src="{{ asset('storage/' . $community->image) }}" class="w-full h-full object-cover">
                                    @else
                                        <i data-lucide="users" class="w-6 h-6"></i>
                                    @endif
                                </div>
                                <div class="flex-1">
                                    <div class="flex items-center justify-between">
                                        <h3 class="font-bold text-stone-900 dark:text-white text-sm line-clamp-1">
                                            {{ $community->name }}
                                        </h3>
                                        @if($community->created_by === $user->id)
                                            <span class="text-[9px] font-black uppercase tracking-wider px-2 py-0.5 rounded-full bg-amber-500 text-white">Founder</span>
                                        @endif
                                    </div>
                                    <p class="text-xs text-stone-400 mt-0.5">
                                        {{ $community->members_count ?? 1 }} members • {{ $community->category }}
                                    </p>
                                </div>
                            </div>

                            <p class="text-xs text-stone-600 dark:text-stone-300 line-clamp-2 leading-relaxed">
                                {{ $community->description }}
                            </p>

                            <div class="pt-3 border-t border-stone-100 dark:border-stone-800 flex items-center justify-between">
                                <a href="{{ route('communities.show', $community) }}" class="text-xs font-bold text-amber-600 dark:text-amber-400 hover:underline">
                                    Open Community
                                </a>
                                @auth
                                    @if($community->created_by === auth()->id())
                                        <a href="{{ route('communities.edit', $community) }}" class="text-xs text-stone-400 hover:text-stone-700 dark:hover:text-stone-200 flex items-center gap-1 font-semibold">
                                            <i data-lucide="settings" class="w-3.5 h-3.5"></i> Settings
                                        </a>
                                    @endif
                                @endauth
                            </div>

                        </div>
                    @endforeach
                </div>
            @else
                <div class="bg-white dark:bg-stone-900 rounded-3xl p-12 text-center border border-stone-200/80 dark:border-stone-800 space-y-3">
                    <div class="w-16 h-16 rounded-3xl bg-amber-50 dark:bg-amber-950/40 text-amber-500 mx-auto flex items-center justify-center">
                        <i data-lucide="users" class="w-8 h-8"></i>
                    </div>
                    <h3 class="text-lg font-bold text-stone-900 dark:text-white">No Communities Joined Yet</h3>
                    <p class="text-xs sm:text-sm text-stone-500 max-w-sm mx-auto">Join heritage circles or start your own cultural collective.</p>
                    <a href="{{ route('communities.index') }}" class="inline-flex items-center gap-2 px-5 py-2.5 bg-stone-900 text-white rounded-xl text-xs font-bold mt-2">
                        Browse Tribes
                    </a>
                </div>
            @endif

        </div>

        {{-- TAB 5: GATHERINGS & WORKSHOPS --}}
        <div x-show="activeTab === 'events'" class="space-y-6">
            
            {{-- Organized Gatherings --}}
            <div class="space-y-4">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-lg sm:text-xl font-bold text-stone-900 dark:text-white">Gatherings You Host</h2>
                        <p class="text-xs sm:text-sm text-stone-500">Cultural masterclasses, festivals, and ceremonies organized by you.</p>
                    </div>
                    @auth
                        @if(auth()->id() === $user->id)
                            <a href="{{ route('events.create') }}"
                                class="px-4 py-2.5 bg-amber-500 hover:bg-amber-600 text-white rounded-xl text-xs sm:text-sm font-bold transition flex items-center gap-2 shadow-sm">
                                <i data-lucide="plus" class="w-4 h-4"></i>
                                <span>Host Gathering</span>
                            </a>
                        @endif
                    @endauth
                </div>

                @if($organizedEvents->count() > 0)
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                        @foreach($organizedEvents as $event)
                            <div class="bg-white dark:bg-stone-900 rounded-3xl p-5 border border-stone-200/80 dark:border-stone-800 shadow-sm flex flex-col justify-between space-y-4 hover:border-amber-500/40 transition">
                                <div class="space-y-2">
                                    <div class="flex items-center justify-between text-xs">
                                        <span class="font-bold text-amber-500 uppercase tracking-wider text-[10px] px-2 py-0.5 rounded bg-amber-50 dark:bg-amber-950/40">
                                            {{ $event->type }}
                                        </span>
                                        <span class="font-semibold text-stone-400">
                                            {{ $event->event_date->format('M j, Y') }}
                                        </span>
                                    </div>
                                    <h3 class="font-bold text-base text-stone-900 dark:text-white line-clamp-1">{{ $event->title }}</h3>
                                    <p class="text-xs text-stone-500 flex items-center gap-1">
                                        <i data-lucide="{{ $event->is_online ? 'video' : 'map-pin' }}" class="w-3.5 h-3.5"></i>
                                        {{ $event->location }}
                                    </p>
                                </div>
                                <div class="pt-3 border-t border-stone-100 dark:border-stone-800 flex items-center justify-between text-xs font-bold">
                                    <a href="{{ route('events.show', $event) }}" class="text-amber-600 dark:text-amber-400 hover:underline">Manage Gathering</a>
                                    @if($event->virtual_room_id)
                                        <a href="{{ route('events.meet', $event) }}" class="px-2.5 py-1 bg-blue-600 text-white rounded-lg flex items-center gap-1">
                                            <i data-lucide="video" class="w-3 h-3"></i> Classroom
                                        </a>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="bg-white dark:bg-stone-900 rounded-3xl p-8 text-center border border-stone-200/80 dark:border-stone-800 text-xs text-stone-500">
                        No hosted gatherings currently scheduled.
                    </div>
                @endif
            </div>

            {{-- Attending Gatherings --}}
            <div class="space-y-4 pt-4 border-t border-stone-200/80 dark:border-stone-800">
                <div>
                    <h2 class="text-lg sm:text-xl font-bold text-stone-900 dark:text-white">Attending Gatherings & Tickets</h2>
                    <p class="text-xs sm:text-sm text-stone-500">Upcoming gatherings, masterclasses, and tickets you have reserved.</p>
                </div>

                @if($attendingEvents->count() > 0)
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                        @foreach($attendingEvents as $event)
                            <div class="bg-white dark:bg-stone-900 rounded-3xl p-5 border border-stone-200/80 dark:border-stone-800 shadow-sm flex flex-col justify-between space-y-4">
                                <div class="space-y-2">
                                    <div class="flex items-center justify-between text-xs">
                                        <span class="font-bold text-emerald-600 dark:text-emerald-400 px-2 py-0.5 rounded bg-emerald-50 dark:bg-emerald-950/40 text-[10px] uppercase">
                                            <i data-lucide="ticket" class="w-3 h-3 inline"></i> Active Pass
                                        </span>
                                        <span class="font-semibold text-stone-400">
                                            {{ $event->event_date->format('M j, Y') }}
                                        </span>
                                    </div>
                                    <h3 class="font-bold text-base text-stone-900 dark:text-white line-clamp-1">{{ $event->title }}</h3>
                                    <p class="text-xs text-stone-500 flex items-center gap-1">
                                        <i data-lucide="{{ $event->is_online ? 'video' : 'map-pin' }}" class="w-3.5 h-3.5"></i>
                                        {{ $event->location }}
                                    </p>
                                </div>
                                <div class="pt-3 border-t border-stone-100 dark:border-stone-800 flex items-center justify-between text-xs font-bold">
                                    <a href="{{ route('events.show', $event) }}" class="text-amber-600 dark:text-amber-400 hover:underline">View Pass</a>
                                    @if($event->virtual_room_id)
                                        <a href="{{ route('events.meet', $event) }}" class="px-3 py-1 bg-blue-600 text-white rounded-lg flex items-center gap-1 shadow-sm">
                                            <i data-lucide="video" class="w-3.5 h-3.5"></i> Enter Classroom
                                        </a>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="bg-white dark:bg-stone-900 rounded-3xl p-8 text-center border border-stone-200/80 dark:border-stone-800 text-xs text-stone-500">
                        You have not registered for any upcoming gatherings yet. <a href="{{ route('events.index') }}" class="text-amber-500 font-bold underline">Discover gatherings</a>.
                    </div>
                @endif
            </div>

        </div>

        {{-- TAB 6: HERITAGE VAULT (OWNER & PUBLIC) --}}
        <div x-show="activeTab === 'vault'" class="space-y-4">
            
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-lg sm:text-xl font-bold text-stone-900 dark:text-white">Personal Heritage Vault</h2>
                    <p class="text-xs sm:text-sm text-stone-500">Encrypted personal vault for family documents, ancestral heirlooms, and historical media.</p>
                </div>
                <div class="flex items-center gap-2">
                    <a href="{{ route('vault.create') }}"
                        class="px-4 py-2 bg-amber-500 hover:bg-amber-600 text-white rounded-xl text-xs sm:text-sm font-bold transition flex items-center gap-2 shadow-sm">
                        <i data-lucide="upload" class="w-4 h-4"></i>
                        <span>Upload Heirloom</span>
                    </a>
                    <a href="{{ route('vault.index') }}"
                        class="px-4 py-2 bg-stone-100 dark:bg-stone-800 hover:bg-stone-200 dark:hover:bg-stone-700 text-stone-700 dark:text-stone-300 rounded-xl text-xs sm:text-sm font-bold transition">
                        Full Vault
                    </a>
                </div>
            </div>

            @if($vaultItems->count() > 0)
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                    @foreach($vaultItems as $item)
                        <div class="bg-white dark:bg-stone-900 rounded-3xl p-4 border border-stone-200/80 dark:border-stone-800 shadow-sm flex flex-col justify-between space-y-3 hover:border-amber-500/40 transition">
                            <div class="flex items-center justify-between">
                                <span class="p-2 rounded-xl bg-amber-50 dark:bg-amber-950/40 text-amber-500">
                                    <i data-lucide="{{ $item->type === 'photos' ? 'image' : ($item->type === 'audio' ? 'music' : ($item->type === 'videos' ? 'video' : 'file-text')) }}" class="w-4 h-4"></i>
                                </span>
                                <span class="text-[9px] font-black uppercase tracking-wider px-2 py-0.5 rounded-full bg-stone-100 dark:bg-stone-800 text-stone-500">
                                    {{ $item->access_level }}
                                </span>
                            </div>

                            <div>
                                <h3 class="font-bold text-sm text-stone-900 dark:text-white line-clamp-1">{{ $item->title }}</h3>
                                <p class="text-[11px] text-stone-400 mt-0.5">{{ $item->file_size }} • {{ $item->created_at->format('M j, Y') }}</p>
                            </div>

                            <div class="pt-2 border-t border-stone-100 dark:border-stone-800 flex items-center justify-between">
                                <a href="{{ route('vault.show', $item) }}" class="text-xs font-bold text-amber-600 dark:text-amber-400 hover:underline">Inspect</a>
                                <a href="{{ route('vault.download', $item) }}" class="text-stone-400 hover:text-stone-700 dark:hover:text-stone-200">
                                    <i data-lucide="download" class="w-4 h-4"></i>
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="bg-white dark:bg-stone-900 rounded-3xl p-12 text-center border border-stone-200/80 dark:border-stone-800 space-y-3">
                    <div class="w-16 h-16 rounded-3xl bg-amber-50 dark:bg-amber-950/40 text-amber-500 mx-auto flex items-center justify-center">
                        <i data-lucide="lock" class="w-8 h-8"></i>
                    </div>
                    <h3 class="text-lg font-bold text-stone-900 dark:text-white">Vault is Empty</h3>
                    <p class="text-xs sm:text-sm text-stone-500 max-w-sm mx-auto">Upload ancestral documents, audio narratives, and photographic records to your private vault.</p>
                </div>
            @endif

        </div>

        {{-- TAB 7: PRIVACY, SOVEREIGNTY & SETTINGS HUB (OWNER ONLY) --}}
        @if(auth()->id() === $user->id)
            <div x-show="activeTab === 'privacy_hub'" class="space-y-6">
                
                <div class="bg-white dark:bg-stone-900 rounded-3xl p-6 sm:p-8 border border-stone-200/80 dark:border-stone-800 shadow-sm space-y-6">
                    <div class="flex items-center gap-3 pb-4 border-b border-stone-100 dark:border-stone-800">
                        <div class="w-12 h-12 rounded-2xl bg-amber-500/10 text-amber-600 dark:text-amber-400 flex items-center justify-center shrink-0">
                            <i data-lucide="shield-check" class="w-6 h-6"></i>
                        </div>
                        <div>
                            <h2 class="text-xl font-bold text-stone-900 dark:text-white">Cultural Sovereignty & Privacy Controls</h2>
                            <p class="text-xs sm:text-sm text-stone-500">Your rights, data ownership guarantees, and platform transparency.</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        
                        <div class="p-5 rounded-2xl bg-stone-50 dark:bg-stone-800/50 border border-stone-200 dark:border-stone-700/60 space-y-3">
                            <h3 class="font-bold text-stone-900 dark:text-white flex items-center gap-2 text-sm sm:text-base">
                                <i data-lucide="award" class="w-4 h-4 text-amber-500"></i>
                                Indigenous Data Sovereignty
                            </h3>
                            <p class="text-xs text-stone-600 dark:text-stone-300 leading-relaxed">
                                You retain 100% intellectual property of all oral histories, folklore, recipes, and traditional knowledge uploaded to Kulturegram. We never sell or broker your data.
                            </p>
                            <a href="{{ route('privacy') }}" class="inline-flex items-center gap-1 text-xs font-bold text-amber-600 dark:text-amber-400 hover:underline">
                                Read Privacy Policy <i data-lucide="arrow-right" class="w-3 h-3"></i>
                            </a>
                        </div>

                        <div class="p-5 rounded-2xl bg-stone-50 dark:bg-stone-800/50 border border-stone-200 dark:border-stone-700/60 space-y-3">
                            <h3 class="font-bold text-stone-900 dark:text-white flex items-center gap-2 text-sm sm:text-base">
                                <i data-lucide="lock" class="w-4 h-4 text-amber-500"></i>
                                Private Vault Isolation
                            </h3>
                            <p class="text-xs text-stone-600 dark:text-stone-300 leading-relaxed">
                                Your personal vault items marked as "Private" are strictly confined to your authenticated account and are inaccessible to public crawlers or search engines.
                            </p>
                            <a href="{{ route('vault.index') }}" class="inline-flex items-center gap-1 text-xs font-bold text-amber-600 dark:text-amber-400 hover:underline">
                                Manage Vault Permissions <i data-lucide="arrow-right" class="w-3 h-3"></i>
                            </a>
                        </div>

                        <div class="p-5 rounded-2xl bg-stone-50 dark:bg-stone-800/50 border border-stone-200 dark:border-stone-700/60 space-y-3">
                            <h3 class="font-bold text-stone-900 dark:text-white flex items-center gap-2 text-sm sm:text-base">
                                <i data-lucide="scale" class="w-4 h-4 text-amber-500"></i>
                                Community Standards
                            </h3>
                            <p class="text-xs text-stone-600 dark:text-stone-300 leading-relaxed">
                                Review our terms regarding cultural respect, authenticity standards, fair use of historical media, and non-commercial preservation principles.
                            </p>
                            <a href="{{ route('terms') }}" class="inline-flex items-center gap-1 text-xs font-bold text-amber-600 dark:text-amber-400 hover:underline">
                                Read Terms of Service <i data-lucide="arrow-right" class="w-3 h-3"></i>
                            </a>
                        </div>

                        <div class="p-5 rounded-2xl bg-stone-50 dark:bg-stone-800/50 border border-stone-200 dark:border-stone-700/60 space-y-3">
                            <h3 class="font-bold text-stone-900 dark:text-white flex items-center gap-2 text-sm sm:text-base">
                                <i data-lucide="sliders" class="w-4 h-4 text-amber-500"></i>
                                Account & Security Center
                            </h3>
                            <p class="text-xs text-stone-600 dark:text-stone-300 leading-relaxed">
                                Update password, change email address, manage notification alerts, customize appearance themes, or export account data.
                            </p>
                            <a href="{{ route('settings.index', $user->id) }}" class="inline-flex items-center gap-1 text-xs font-bold text-amber-600 dark:text-amber-400 hover:underline">
                                Go to Settings <i data-lucide="arrow-right" class="w-3 h-3"></i>
                            </a>
                        </div>

                    </div>
                </div>

            </div>
        @endif

    </div>

    {{-- INLINE MODAL: ADD LIFE CHAPTER --}}
    @auth
        @if(auth()->id() === $user->id)
            <div x-show="showChapterModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm"
                @keydown.escape.window="showChapterModal = false">
                <div class="bg-white dark:bg-stone-900 rounded-3xl max-w-lg w-full p-6 sm:p-8 border border-stone-200 dark:border-stone-800 shadow-2xl relative"
                    @click.away="showChapterModal = false">
                    
                    <div class="flex items-center justify-between pb-4 border-b border-stone-100 dark:border-stone-800 mb-5">
                        <h3 class="text-lg font-black text-stone-900 dark:text-white flex items-center gap-2">
                            <i data-lucide="bookmark" class="w-5 h-5 text-amber-500"></i>
                            Record Life Chapter
                        </h3>
                        <button type="button" @click="showChapterModal = false" class="text-stone-400 hover:text-stone-600">
                            <i data-lucide="x" class="w-5 h-5"></i>
                        </button>
                    </div>

                    <form action="{{ route('profile.chapters.store') }}" method="POST" class="space-y-4">
                        @csrf
                        <div>
                            <label class="block text-xs font-bold text-stone-700 dark:text-stone-300 uppercase tracking-wider mb-1.5">Title *</label>
                            <input type="text" name="title" required placeholder="e.g., Apprenticeship in Benin City"
                                class="w-full px-4 py-2.5 bg-stone-50 dark:bg-stone-800 border border-stone-200 dark:border-stone-700 rounded-xl text-sm font-semibold text-stone-900 dark:text-white focus:ring-2 focus:ring-amber-500 focus:outline-none">
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-bold text-stone-700 dark:text-stone-300 uppercase tracking-wider mb-1.5">Era / Period</label>
                                <input type="text" name="period" placeholder="e.g., 2010 - 2015"
                                    class="w-full px-4 py-2.5 bg-stone-50 dark:bg-stone-800 border border-stone-200 dark:border-stone-700 rounded-xl text-sm text-stone-900 dark:text-white focus:ring-2 focus:ring-amber-500 focus:outline-none">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-stone-700 dark:text-stone-300 uppercase tracking-wider mb-1.5">Location</label>
                                <input type="text" name="location" placeholder="e.g., Edo State"
                                    class="w-full px-4 py-2.5 bg-stone-50 dark:bg-stone-800 border border-stone-200 dark:border-stone-700 rounded-xl text-sm text-stone-900 dark:text-white focus:ring-2 focus:ring-amber-500 focus:outline-none">
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-stone-700 dark:text-stone-300 uppercase tracking-wider mb-1.5">Significance & Lessons</label>
                            <textarea name="description" rows="3" placeholder="Describe cultural rites, mentors, or traditional craft learned..."
                                class="w-full px-4 py-2.5 bg-stone-50 dark:bg-stone-800 border border-stone-200 dark:border-stone-700 rounded-xl text-sm text-stone-900 dark:text-white focus:ring-2 focus:ring-amber-500 focus:outline-none"></textarea>
                        </div>

                        <div class="pt-3 flex justify-end gap-2">
                            <button type="button" @click="showChapterModal = false" class="px-4 py-2 text-sm font-bold text-stone-500 hover:text-stone-700">Cancel</button>
                            <button type="submit" class="px-5 py-2 bg-amber-500 hover:bg-amber-600 text-white text-sm font-bold rounded-xl shadow-md">Save Chapter</button>
                        </div>
                    </form>
                </div>
            </div>
        @endif
    @endauth

</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        lucide.createIcons();
    });

    @auth
        function toggleUserTap(userId) {
            fetch(`/users/${userId}/tap`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                }
            })
            .then(res => res.json())
            .then(data => {
                if (data.error) return;
                const count = document.getElementById('profile-vibes-count');
                const text = document.getElementById('profile-vibe-text');
                const icon = document.getElementById('profile-vibe-icon');
                if (count) count.textContent = data.count;
                if (data.tapped) {
                    text.textContent = 'Vibed';
                    icon.classList.add('fill-current', 'text-amber-500');
                } else {
                    text.textContent = 'Vibe';
                    icon.classList.remove('fill-current', 'text-amber-500');
                }
            })
            .catch(err => console.error(err));
        }

        function toggleUserLockIn(userId) {
            fetch(`/users/${userId}/lockin`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                }
            })
            .then(res => res.json())
            .then(data => {
                if (data.error) return;
                window.location.reload();
            })
            .catch(err => console.error(err));
        }
    @endauth
</script>
@endpush