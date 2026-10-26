{{-- resources/views/profile/index.blade.php --}}
@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-stone-50 dark:bg-stone-950 pb-20 overflow-x-hidden w-full" x-data="{ 
    activeTab: 'timeline',
    showChapterModal: false,
    
    // Avatar modal state
    showAvatarModal: false,
    avatarFile: null,
    avatarFilename: '',
    avatarFilesize: '',
    avatarModalImg: '',
    avatarScale: 1.0,
    avatarRotation: 0,
    isSavingAvatar: false,
    
    // Cover modal state
    showCoverModal: false,
    coverFile: null,
    coverFilename: '',
    coverFilesize: '',
    coverModalImg: '',
    coverScale: 1.0,
    coverOffsetY: 0,
    isSavingCover: false,

    // Toast feedback
    toastMessage: '',
    showToast: false,

    notify(msg) {
        this.toastMessage = msg;
        this.showToast = true;
        setTimeout(() => { this.showToast = false; }, 4000);
    },

    triggerAvatarSelect() {
        document.getElementById('avatar-file-input')?.click();
    },

    handleAvatarSelected(event) {
        const file = event.target.files[0];
        if (!file) return;
        this.avatarFile = file;
        this.avatarFilename = file.name;
        this.avatarFilesize = (file.size / (1024 * 1024)).toFixed(2) + ' MB';
        this.avatarScale = 1.0;
        this.avatarRotation = 0;
        
        const reader = new FileReader();
        reader.onload = (e) => {
            this.avatarModalImg = e.target.result;
            this.showAvatarModal = true;
            this.$nextTick(() => lucide.createIcons());
        };
        reader.readAsDataURL(file);
    },

    closeAvatarModal() {
        this.showAvatarModal = false;
        this.avatarFile = null;
        this.avatarModalImg = '';
        const input = document.getElementById('avatar-file-input');
        if (input) input.value = '';
    },

    saveAvatar() {
        if (!this.avatarFile || this.isSavingAvatar) return;
        this.isSavingAvatar = true;

        const canvas = document.createElement('canvas');
        canvas.width = 512;
        canvas.height = 512;
        const ctx = canvas.getContext('2d');

        const img = new Image();
        img.onload = () => {
            ctx.clearRect(0, 0, 512, 512);
            ctx.save();
            ctx.translate(256, 256);
            ctx.rotate((this.avatarRotation * Math.PI) / 180);
            ctx.scale(this.avatarScale, this.avatarScale);
            ctx.drawImage(img, -256, -256, 512, 512);
            ctx.restore();

            canvas.toBlob((blob) => {
                const formData = new FormData();
                formData.append('profile_photo', blob || this.avatarFile, 'avatar.jpg');
                formData.append('_token', '{{ csrf_token() }}');

                fetch('{{ route('profile.photo.upload') }}', {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: formData
                })
                .then(res => res.json())
                .then(data => {
                    this.isSavingAvatar = false;
                    if (data.success && data.avatar_url) {
                        const avatarImg = document.getElementById('profile-avatar-display');
                        if (avatarImg) avatarImg.src = data.avatar_url;
                        document.querySelectorAll('.user-avatar-sync').forEach(el => el.src = data.avatar_url);
                        this.notify(data.message || 'Profile picture updated successfully!');
                        this.closeAvatarModal();
                    } else {
                        window.location.reload();
                    }
                })
                .catch(err => {
                    console.error('Fetch avatar upload fallback', err);
                    this.isSavingAvatar = false;
                    window.location.reload();
                });
            }, 'image/jpeg', 0.92);
        };
        img.src = this.avatarModalImg;
    },

    triggerCoverSelect() {
        document.getElementById('cover-file-input')?.click();
    },

    handleCoverSelected(event) {
        const file = event.target.files[0];
        if (!file) return;
        this.coverFile = file;
        this.coverFilename = file.name;
        this.coverFilesize = (file.size / (1024 * 1024)).toFixed(2) + ' MB';
        this.coverScale = 1.0;
        this.coverOffsetY = 0;

        const reader = new FileReader();
        reader.onload = (e) => {
            this.coverModalImg = e.target.result;
            this.showCoverModal = true;
            this.$nextTick(() => lucide.createIcons());
        };
        reader.readAsDataURL(file);
    },

    closeCoverModal() {
        this.showCoverModal = false;
        this.coverFile = null;
        this.coverModalImg = '';
        const input = document.getElementById('cover-file-input');
        if (input) input.value = '';
    },

    saveCover() {
        if (!this.coverFile || this.isSavingCover) return;
        this.isSavingCover = true;

        const canvas = document.createElement('canvas');
        canvas.width = 1200;
        canvas.height = 450;
        const ctx = canvas.getContext('2d');

        const img = new Image();
        img.onload = () => {
            ctx.clearRect(0, 0, 1200, 450);
            ctx.save();
            ctx.translate(600, 225);
            ctx.scale(this.coverScale, this.coverScale);
            ctx.translate(0, this.coverOffsetY * 2.5);
            ctx.drawImage(img, -600, -225, 1200, 450);
            ctx.restore();

            canvas.toBlob((blob) => {
                const formData = new FormData();
                formData.append('cover_photo', blob || this.coverFile, 'cover.jpg');
                formData.append('_token', '{{ csrf_token() }}');

                fetch('{{ route('profile.cover.upload') }}', {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: formData
                })
                .then(res => res.json())
                .then(data => {
                    this.isSavingCover = false;
                    if (data.success && data.cover_url) {
                        const coverImg = document.getElementById('profile-cover-display');
                        if (coverImg) {
                            coverImg.src = data.cover_url;
                        } else {
                            window.location.reload();
                        }
                        this.notify(data.message || 'Cover banner updated successfully!');
                        this.closeCoverModal();
                    } else {
                        window.location.reload();
                    }
                })
                .catch(err => {
                    console.error('Fetch cover upload fallback', err);
                    this.isSavingCover = false;
                    window.location.reload();
                });
            }, 'image/jpeg', 0.92);
        };
        img.src = this.coverModalImg;
    }
}">

    {{-- Toast Notification --}}
    <div x-show="showToast" x-cloak 
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 translate-y-4"
        x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100 translate-y-0"
        x-transition:leave-end="opacity-0 translate-y-4"
        class="fixed bottom-6 right-4 sm:right-6 z-50 bg-stone-900 text-white dark:bg-white dark:text-stone-900 px-4 sm:px-5 py-3 rounded-2xl shadow-2xl flex items-center gap-3 border border-stone-800 dark:border-stone-200">
        <div class="w-6 h-6 rounded-full bg-emerald-500 text-white flex items-center justify-center shrink-0">
            <i data-lucide="check" class="w-3.5 h-3.5"></i>
        </div>
        <span class="text-xs sm:text-sm font-bold" x-text="toastMessage"></span>
    </div>

    <div class="max-w-6xl mx-auto px-2.5 sm:px-6 pt-3 sm:pt-6 space-y-4 sm:space-y-6 w-full max-w-full overflow-x-hidden">

        {{-- Session Flash Notifications --}}
        @if(session('success'))
            <div class="p-3.5 sm:p-4 rounded-2xl bg-green-50 dark:bg-green-950/40 border border-green-200 dark:border-green-800 text-green-700 dark:text-green-300 text-xs sm:text-sm font-semibold flex items-center justify-between shadow-sm animate-fade-in">
                <div class="flex items-center gap-2">
                    <i data-lucide="check-circle" class="w-4 h-4 sm:w-5 sm:h-5 text-green-500 shrink-0"></i>
                    <span>{{ session('success') }}</span>
                </div>
                <button type="button" @click="$el.parentElement.remove()" class="text-green-500 hover:text-green-700 p-1">
                    <i data-lucide="x" class="w-4 h-4"></i>
                </button>
            </div>
        @endif

        {{-- Profile Hero Card --}}
        <div class="bg-white dark:bg-stone-900 rounded-2xl sm:rounded-3xl shadow-sm border border-stone-200/80 dark:border-stone-800 overflow-hidden relative">
            
            {{-- Cover Banner --}}
            <div class="w-full h-36 sm:h-56 md:h-64 relative bg-orange-500 overflow-hidden group shadow-inner">
                @if($user->cover_photo_url)
                    <img id="profile-cover-display" src="{{ $user->cover_photo_url }}" class="w-full h-full object-cover">
                @else
                    {{-- Heritage Geometric Motif Overlay --}}
                    <div class="absolute inset-0 opacity-20 bg-[radial-gradient(#fff_1px,transparent_1px)] [background-size:16px_16px]"></div>
                @endif
                <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-transparent to-black/20"></div>

                {{-- Direct Cover Photo Change Button (Owner Only) --}}
                @auth
                    @if(auth()->id() === $user->id)
                        <button type="button" 
                            @click="triggerCoverSelect()" 
                            id="cover-change-btn"
                            title="Change Cover Banner"
                            class="absolute top-3 right-3 sm:top-4 sm:right-4 z-20 inline-flex items-center gap-1.5 px-3 py-1.5 sm:px-4 sm:py-2 bg-black/60 hover:bg-black/80 active:scale-95 backdrop-blur-md text-white rounded-xl text-xs sm:text-sm font-bold border border-white/20 transition-all shadow-lg cursor-pointer">
                            <i data-lucide="camera" class="w-3.5 h-3.5 sm:w-4 sm:h-4 text-amber-400"></i>
                            <span>Change Banner</span>
                        </button>
                        <input type="file" id="cover-file-input" class="hidden" accept="image/*" @change="handleCoverSelected($event)">
                    @endif
                @endauth
            </div>

            {{-- Profile Details Section --}}
            <div class="px-3.5 sm:px-8 pb-5 sm:pb-8 pt-0 relative">
                <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between -mt-14 sm:-mt-20 gap-3 sm:gap-4 mb-4 sm:mb-6">
                    
                    {{-- Avatar & Core Info --}}
                    <div class="flex flex-col sm:flex-row sm:items-end gap-3 sm:gap-4 text-center sm:text-left items-center sm:items-start">
                        
                        {{-- Avatar with Permanent Camera Button --}}
                        <div class="relative w-28 h-28 sm:w-36 sm:h-36 md:w-40 md:h-40 flex-shrink-0 z-10">
                            {{-- Inner Avatar Image Frame --}}
                            <div class="w-full h-full rounded-2xl sm:rounded-3xl border-4 border-white dark:border-stone-900 shadow-xl overflow-hidden bg-stone-100 dark:bg-stone-800">
                                <img id="profile-avatar-display" src="{{ $user->profile_photo_url }}" alt="{{ $user->name }}" class="w-full h-full object-cover">
                            </div>

                            {{-- Direct Camera Badge Button (Always Visible, Mobile & Desktop) --}}
                            @auth
                                @if(auth()->id() === $user->id)
                                    <button type="button" 
                                        @click="triggerAvatarSelect()" 
                                        id="avatar-camera-btn"
                                        title="Update Profile Picture"
                                        class="absolute -bottom-1 -right-1 sm:bottom-0 sm:right-0 w-8 h-8 sm:w-10 sm:h-10 rounded-xl sm:rounded-2xl bg-amber-500 hover:bg-amber-600 active:scale-95 text-white shadow-xl border-2 border-white dark:border-stone-900 transition-all flex items-center justify-center cursor-pointer z-20">
                                        <i data-lucide="camera" class="w-4 h-4 sm:w-5 sm:h-5"></i>
                                    </button>
                                    <input type="file" id="avatar-file-input" class="hidden" accept="image/*" @change="handleAvatarSelected($event)">
                                @endif
                            @endauth
                        </div>

                        {{-- Name, Username & Tags --}}
                        <div class="space-y-1 sm:mb-2">
                            <div class="flex flex-wrap items-center justify-center sm:justify-start gap-1.5 sm:gap-2">
                                <h1 class="text-xl sm:text-3xl font-black text-stone-900 dark:text-white tracking-tight">
                                    {{ $user->name }}
                                </h1>
                                @if($user->is_admin)
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider bg-amber-500/10 text-amber-600 dark:text-amber-400 border border-amber-500/20 flex items-center gap-1">
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
                    <div class="flex items-center justify-center sm:justify-end gap-2 z-10 w-full sm:w-auto mt-2 sm:mt-0">
                        @auth
                            @if(auth()->id() === $user->id)
                                <a href="{{ route('settings.index', $user->id) }}"
                                    class="flex-1 sm:flex-none inline-flex items-center justify-center gap-1.5 px-4 sm:px-5 py-2.5 bg-stone-900 hover:bg-stone-800 dark:bg-white dark:hover:bg-stone-100 text-white dark:text-stone-900 rounded-xl text-xs sm:text-sm font-bold shadow-md hover:shadow-lg transition-all active:scale-95">
                                    <i data-lucide="settings" class="w-4 h-4"></i>
                                    <span>Account Settings</span>
                                </a>
                                <a href="{{ route('privacy') }}"
                                    class="inline-flex items-center justify-center gap-1.5 px-3.5 sm:px-4 py-2.5 bg-stone-100 hover:bg-stone-200 dark:bg-stone-800 dark:hover:bg-stone-700 text-stone-700 dark:text-stone-300 rounded-xl text-xs sm:text-sm font-semibold transition-all">
                                    <i data-lucide="shield-check" class="w-4 h-4 text-amber-500"></i>
                                    <span>Privacy</span>
                                </a>

                                @if(auth()->user()->is_admin)
                                    <a href="{{ route('admin.cultures.index') }}" class="inline-flex items-center justify-center gap-1.5 px-3.5 sm:px-4 py-2.5 bg-amber-500/10 hover:bg-amber-500/20 text-amber-600 dark:text-amber-400 rounded-xl text-xs sm:text-sm font-semibold transition-all">
                                        <i data-lucide="shield-alert" class="w-4 h-4"></i>
                                        <span>Admin</span>
                                    </a>
                                @endif
                            @else
                                {{-- Vibe Button --}}
                                <button onclick="toggleUserTap({{ $user->id }})" id="profile-vibe-btn"
                                    class="flex-1 sm:flex-none flex items-center justify-center gap-1.5 px-4 py-2.5 rounded-xl text-xs sm:text-sm font-bold transition-all shadow-sm
                                    {{ isset($hasVibed) && $hasVibed ? 'bg-amber-100 dark:bg-amber-950/60 text-amber-700 dark:text-amber-400 border border-amber-200 dark:border-amber-800' : 'bg-white dark:bg-stone-800 border border-stone-200 dark:border-stone-700 text-stone-700 dark:text-stone-300 hover:bg-stone-50' }}">
                                    <i data-lucide="zap" id="profile-vibe-icon" class="w-4 h-4 {{ isset($hasVibed) && $hasVibed ? 'fill-current text-amber-500' : '' }}"></i>
                                    <span id="profile-vibe-text">{{ isset($hasVibed) && $hasVibed ? 'Vibed' : 'Vibe' }}</span>
                                </button>

                                {{-- Lock-In Button --}}
                                <button onclick="toggleUserLockIn({{ $user->id }})" id="profile-lockin-btn"
                                    class="flex-1 sm:flex-none flex items-center justify-center gap-1.5 px-4 sm:px-5 py-2.5 rounded-xl text-xs sm:text-sm font-bold transition-all shadow-md active:scale-95
                                    {{ isset($isLockedIn) && $isLockedIn ? 'bg-stone-100 dark:bg-stone-800 text-stone-700 dark:text-stone-300 hover:bg-red-50 dark:hover:bg-red-950/30 hover:text-red-600 border border-stone-200 dark:border-stone-700' : 'glass-orange text-white' }}">
                                    <i data-lucide="{{ isset($isLockedIn) && $isLockedIn ? 'user-check' : 'user-plus' }}" class="w-4 h-4"></i>
                                    <span id="profile-lockin-text">{{ isset($isLockedIn) && $isLockedIn ? 'Locked In' : 'Lock In' }}</span>
                                </button>

                                {{-- Direct Message Button --}}
                                @if(isset($isLockedIn) && $isLockedIn)
                                    <a href="{{ route('messages.start', $user->id) }}"
                                        class="flex items-center justify-center gap-1.5 px-3.5 py-2.5 bg-stone-900 text-white dark:bg-white dark:text-stone-900 rounded-xl text-xs sm:text-sm font-bold shadow-md hover:opacity-90 transition-all">
                                        <i data-lucide="message-square" class="w-4 h-4"></i>
                                        <span class="hidden sm:inline">Message</span>
                                    </a>
                                @endif
                            @endif
                        @endauth
                    </div>
                </div>

                {{-- Bio & Metadata Bar --}}
                <div class="space-y-3 pt-2 border-t border-stone-100 dark:border-stone-800">
                    <p class="text-xs sm:text-base text-stone-700 dark:text-stone-300 leading-relaxed max-w-3xl">
                        {{ $user->bio ?: 'Cultural preservationist and heritage custodian documenting stories, traditions, and generational memories on Kulturegram.' }}
                    </p>

                    <div class="flex flex-wrap items-center justify-center sm:justify-start gap-y-1.5 gap-x-3 sm:gap-x-4 text-xs sm:text-sm text-stone-500 dark:text-stone-400">
                        @if($user->cultural_background)
                            <div class="flex items-center gap-1.5 font-medium text-amber-600 dark:text-amber-400">
                                <i data-lucide="globe" class="w-3.5 h-3.5 sm:w-4 sm:h-4"></i>
                                <span>{{ $user->cultural_background }}</span>
                            </div>
                        @endif

                        @if($user->location)
                            <div class="flex items-center gap-1.5">
                                <i data-lucide="map-pin" class="w-3.5 h-3.5 sm:w-4 sm:h-4 text-stone-400"></i>
                                <span>{{ $user->location }}</span>
                            </div>
                        @endif

                        @if($user->website)
                            <a href="{{ $user->website }}" target="_blank" rel="noopener noreferrer"
                                class="flex items-center gap-1.5 text-blue-600 dark:text-blue-400 hover:underline">
                                <i data-lucide="link" class="w-3.5 h-3.5 sm:w-4 sm:h-4"></i>
                                <span>{{ parse_url($user->website, PHP_URL_HOST) ?: $user->website }}</span>
                            </a>
                        @endif

                        <div class="flex items-center gap-1.5">
                            <i data-lucide="calendar" class="w-3.5 h-3.5 sm:w-4 sm:h-4 text-stone-400"></i>
                            <span>Joined {{ $user->created_at->format('M Y') }}</span>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        {{-- Activity & Preservation Log Button (Mobile & Desktop) --}}
        <div class="bg-white dark:bg-stone-900 rounded-2xl border border-stone-200/80 dark:border-stone-800 p-3 sm:p-4 shadow-sm flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 hover:border-amber-500/40 transition">
            <div class="flex items-center gap-3 min-w-0">
                <div class="w-9 h-9 sm:w-10 sm:h-10 rounded-xl bg-amber-500/10 text-amber-600 dark:text-amber-400 flex items-center justify-center shrink-0">
                    <i data-lucide="activity" class="w-4 h-4 sm:w-5 sm:h-5"></i>
                </div>
                <div class="min-w-0">
                    <h3 class="text-xs sm:text-sm font-black text-stone-900 dark:text-white flex items-center gap-2 flex-wrap">
                        <span>Activity & Preservation Log</span>
                        <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-amber-500/10 text-amber-600 dark:text-amber-400 border border-amber-500/20 shrink-0">
                            {{ $stats['preservation_score'] }}% Score
                        </span>
                    </h3>
                    <p class="text-[11px] sm:text-xs text-stone-500">
                        Explore complete history of stories, traditions, gatherings, and heritage contributions.
                    </p>
                </div>
            </div>

            <a href="{{ auth()->id() === $user->id ? route('profile.activity') : route('profile.user.activity', $user) }}"
                class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-4 py-2 sm:px-5 sm:py-2.5 bg-amber-500 hover:bg-amber-600 active:scale-95 text-white rounded-xl text-xs sm:text-sm font-bold shadow-md transition shrink-0">
                <i data-lucide="list" class="w-4 h-4"></i>
                <span>View All Activities & Stats</span>
                <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
            </a>
        </div>

        {{-- Section Navigation Dropdown (Mobile & Desktop) --}}
        <div class="relative w-full z-30" x-data="{ isTabDropdownOpen: false }" @click.away="isTabDropdownOpen = false">
            
            {{-- Dropdown Trigger Button --}}
            <button type="button" 
                @click="isTabDropdownOpen = !isTabDropdownOpen; $nextTick(() => { if (typeof lucide !== 'undefined') lucide.createIcons(); });"
                class="w-full bg-white dark:bg-stone-900 border border-stone-200/80 dark:border-stone-800 rounded-2xl p-3 sm:p-3.5 shadow-sm flex items-center justify-between text-left hover:border-amber-500 transition active:scale-[0.99] cursor-pointer gap-2.5">
                
                <div class="flex items-center gap-2.5 sm:gap-3 min-w-0 flex-1">
                    <div class="w-9 h-9 sm:w-10 sm:h-10 rounded-xl bg-amber-500 text-white flex items-center justify-center shrink-0 shadow-sm">
                        <template x-if="activeTab === 'timeline'">
                            <i data-lucide="clock" class="w-4 h-4 sm:w-5 sm:h-5"></i>
                        </template>
                        <template x-if="activeTab === 'chapters'">
                            <i data-lucide="book-open" class="w-4 h-4 sm:w-5 sm:h-5"></i>
                        </template>
                        <template x-if="activeTab === 'cultures'">
                            <i data-lucide="archive" class="w-4 h-4 sm:w-5 sm:h-5"></i>
                        </template>
                        <template x-if="activeTab === 'communities'">
                            <i data-lucide="users" class="w-4 h-4 sm:w-5 sm:h-5"></i>
                        </template>
                        <template x-if="activeTab === 'events'">
                            <i data-lucide="calendar" class="w-4 h-4 sm:w-5 sm:h-5"></i>
                        </template>
                        <template x-if="activeTab === 'vault'">
                            <i data-lucide="lock" class="w-4 h-4 sm:w-5 sm:h-5"></i>
                        </template>
                        <template x-if="activeTab === 'privacy_hub'">
                            <i data-lucide="shield" class="w-4 h-4 sm:w-5 sm:h-5"></i>
                        </template>
                    </div>

                    <div class="min-w-0 flex-1">
                        <span class="text-[10px] uppercase font-bold text-stone-400 block tracking-wider truncate">Current Section</span>
                        <div class="flex items-center gap-1.5 sm:gap-2 min-w-0">
                            <h2 class="text-sm sm:text-base font-black text-stone-900 dark:text-white truncate"
                                x-text="activeTab === 'timeline' ? 'Stories Feed' : 
                                       (activeTab === 'chapters' ? 'Life Chapters' : 
                                       (activeTab === 'cultures' ? 'Cultural Stories' : 
                                       (activeTab === 'communities' ? 'Communities & Tribes' : 
                                       (activeTab === 'events' ? 'Gatherings & Workshops' : 
                                       (activeTab === 'vault' ? 'Personal Heritage Vault' : 'Privacy & Sovereignty')))))">
                            </h2>

                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-500/10 text-amber-600 dark:text-amber-400 border border-amber-500/20 shrink-0"
                                x-text="activeTab === 'timeline' ? '{{ $stats['stories'] }}' : 
                                       (activeTab === 'chapters' ? '{{ $lifeChapters->count() }}' : 
                                       (activeTab === 'cultures' ? '{{ $cultures->count() }}' : 
                                       (activeTab === 'communities' ? '{{ $communities->count() }}' : 
                                       (activeTab === 'events' ? '{{ $organizedEvents->count() + $attendingEvents->count() }}' : 
                                       (activeTab === 'vault' ? '{{ $vaultCount }}' : 'Hub')))))">
                            </span>
                        </div>
                    </div>
                </div>

                <div class="flex items-center gap-1.5 sm:gap-2 text-stone-400 shrink-0 ml-1">
                    <span class="text-xs font-semibold hidden md:inline text-stone-500">Switch Section</span>
                    <div class="w-7 h-7 sm:w-8 sm:h-8 rounded-lg bg-stone-100 dark:bg-stone-800 flex items-center justify-center transition-transform duration-200"
                        :class="isTabDropdownOpen ? 'rotate-180 bg-amber-500 text-white' : ''">
                        <i data-lucide="chevron-down" class="w-4 h-4"></i>
                    </div>
                </div>
            </button>

            {{-- Dropdown Menu (Mobile & Desktop) --}}
            <div x-show="isTabDropdownOpen" x-cloak 
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 translate-y-2 scale-95"
                x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                x-transition:leave-end="opacity-0 translate-y-2 scale-95"
                class="absolute left-0 right-0 mt-2 z-50 bg-white dark:bg-stone-900 border border-stone-200/80 dark:border-stone-800 rounded-2xl shadow-2xl p-2 space-y-1">

                {{-- Option 1: Stories Feed --}}
                <button type="button" @click="activeTab = 'timeline'; isTabDropdownOpen = false; $nextTick(() => { if (typeof lucide !== 'undefined') lucide.createIcons(); });"
                    :class="activeTab === 'timeline' ? 'bg-amber-50 dark:bg-amber-950/40 text-amber-600 dark:text-amber-400 font-bold' : 'text-stone-700 dark:text-stone-300 hover:bg-stone-50 dark:hover:bg-stone-800'"
                    class="w-full flex items-center justify-between px-3 py-2.5 rounded-xl text-xs sm:text-sm font-semibold transition text-left cursor-pointer gap-2">
                    <div class="flex items-center gap-2.5 sm:gap-3 min-w-0 flex-1">
                        <div class="w-7 h-7 rounded-lg bg-amber-100 dark:bg-amber-950/60 text-amber-600 flex items-center justify-center shrink-0">
                            <i data-lucide="clock" class="w-4 h-4"></i>
                        </div>
                        <div class="min-w-0 flex-1">
                            <span class="block truncate">Stories Feed</span>
                            <span class="text-[10px] text-stone-400 font-normal truncate block">Living memories & personal chronicle</span>
                        </div>
                    </div>
                    <div class="flex items-center gap-2 shrink-0">
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold"
                            :class="activeTab === 'timeline' ? 'bg-amber-500 text-white' : 'bg-stone-100 dark:bg-stone-800 text-stone-500'">
                            {{ $stats['stories'] }}
                        </span>
                        <template x-if="activeTab === 'timeline'">
                            <i data-lucide="check" class="w-4 h-4 text-amber-500"></i>
                        </template>
                    </div>
                </button>

                {{-- Option 2: Life Chapters --}}
                <button type="button" @click="activeTab = 'chapters'; isTabDropdownOpen = false; $nextTick(() => { if (typeof lucide !== 'undefined') lucide.createIcons(); });"
                    :class="activeTab === 'chapters' ? 'bg-amber-50 dark:bg-amber-950/40 text-amber-600 dark:text-amber-400 font-bold' : 'text-stone-700 dark:text-stone-300 hover:bg-stone-50 dark:hover:bg-stone-800'"
                    class="w-full flex items-center justify-between px-3 py-2.5 rounded-xl text-xs sm:text-sm font-semibold transition text-left cursor-pointer gap-2">
                    <div class="flex items-center gap-2.5 sm:gap-3 min-w-0 flex-1">
                        <div class="w-7 h-7 rounded-lg bg-amber-500 text-white flex items-center justify-center shrink-0">
                            <i data-lucide="book-open" class="w-4 h-4"></i>
                        </div>
                        <div class="min-w-0 flex-1">
                            <span class="block truncate">Life Chapters</span>
                            <span class="text-[10px] text-stone-400 font-normal truncate block">Milestones & historical transitions</span>
                        </div>
                    </div>
                    <div class="flex items-center gap-2 shrink-0">
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold"
                            :class="activeTab === 'chapters' ? 'bg-amber-500 text-white' : 'bg-stone-100 dark:bg-stone-800 text-stone-500'">
                            {{ $lifeChapters->count() }}
                        </span>
                        <template x-if="activeTab === 'chapters'">
                            <i data-lucide="check" class="w-4 h-4 text-amber-500"></i>
                        </template>
                    </div>
                </button>

                {{-- Option 3: Cultural Stories --}}
                <button type="button" @click="activeTab = 'cultures'; isTabDropdownOpen = false; $nextTick(() => { if (typeof lucide !== 'undefined') lucide.createIcons(); });"
                    :class="activeTab === 'cultures' ? 'bg-amber-50 dark:bg-amber-950/40 text-amber-600 dark:text-amber-400 font-bold' : 'text-stone-700 dark:text-stone-300 hover:bg-stone-50 dark:hover:bg-stone-800'"
                    class="w-full flex items-center justify-between px-3 py-2.5 rounded-xl text-xs sm:text-sm font-semibold transition text-left cursor-pointer gap-2">
                    <div class="flex items-center gap-2.5 sm:gap-3 min-w-0 flex-1">
                        <div class="w-7 h-7 rounded-lg bg-orange-100 dark:bg-orange-950/60 text-orange-600 flex items-center justify-center shrink-0">
                            <i data-lucide="archive" class="w-4 h-4"></i>
                        </div>
                        <div class="min-w-0 flex-1">
                            <span class="block truncate">Cultural Stories</span>
                            <span class="text-[10px] text-stone-400 font-normal truncate block">Folklore, rituals & oral histories</span>
                        </div>
                    </div>
                    <div class="flex items-center gap-2 shrink-0">
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold"
                            :class="activeTab === 'cultures' ? 'bg-amber-500 text-white' : 'bg-stone-100 dark:bg-stone-800 text-stone-500'">
                            {{ $cultures->count() }}
                        </span>
                        <template x-if="activeTab === 'cultures'">
                            <i data-lucide="check" class="w-4 h-4 text-amber-500"></i>
                        </template>
                    </div>
                </button>

                {{-- Option 4: Communities --}}
                <button type="button" @click="activeTab = 'communities'; isTabDropdownOpen = false; $nextTick(() => { if (typeof lucide !== 'undefined') lucide.createIcons(); });"
                    :class="activeTab === 'communities' ? 'bg-amber-50 dark:bg-amber-950/40 text-amber-600 dark:text-amber-400 font-bold' : 'text-stone-700 dark:text-stone-300 hover:bg-stone-50 dark:hover:bg-stone-800'"
                    class="w-full flex items-center justify-between px-3 py-2.5 rounded-xl text-xs sm:text-sm font-semibold transition text-left cursor-pointer gap-2">
                    <div class="flex items-center gap-2.5 sm:gap-3 min-w-0 flex-1">
                        <div class="w-7 h-7 rounded-lg bg-blue-100 dark:bg-blue-950/60 text-blue-600 flex items-center justify-center shrink-0">
                            <i data-lucide="users" class="w-4 h-4"></i>
                        </div>
                        <div class="min-w-0 flex-1">
                            <span class="block truncate">Communities & Tribes</span>
                            <span class="text-[10px] text-stone-400 font-normal truncate block">Heritage circles and craft collectives</span>
                        </div>
                    </div>
                    <div class="flex items-center gap-2 shrink-0">
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold"
                            :class="activeTab === 'communities' ? 'bg-amber-500 text-white' : 'bg-stone-100 dark:bg-stone-800 text-stone-500'">
                            {{ $communities->count() }}
                        </span>
                        <template x-if="activeTab === 'communities'">
                            <i data-lucide="check" class="w-4 h-4 text-amber-500"></i>
                        </template>
                    </div>
                </button>

                {{-- Option 5: Gatherings --}}
                <button type="button" @click="activeTab = 'events'; isTabDropdownOpen = false; $nextTick(() => { if (typeof lucide !== 'undefined') lucide.createIcons(); });"
                    :class="activeTab === 'events' ? 'bg-amber-50 dark:bg-amber-950/40 text-amber-600 dark:text-amber-400 font-bold' : 'text-stone-700 dark:text-stone-300 hover:bg-stone-50 dark:hover:bg-stone-800'"
                    class="w-full flex items-center justify-between px-3 py-2.5 rounded-xl text-xs sm:text-sm font-semibold transition text-left cursor-pointer gap-2">
                    <div class="flex items-center gap-2.5 sm:gap-3 min-w-0 flex-1">
                        <div class="w-7 h-7 rounded-lg bg-purple-100 dark:bg-purple-950/60 text-purple-600 flex items-center justify-center shrink-0">
                            <i data-lucide="calendar" class="w-4 h-4"></i>
                        </div>
                        <div class="min-w-0 flex-1">
                            <span class="block truncate">Gatherings & Workshops</span>
                            <span class="text-[10px] text-stone-400 font-normal truncate block">Masterclasses, tickets & passes</span>
                        </div>
                    </div>
                    <div class="flex items-center gap-2 shrink-0">
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold"
                            :class="activeTab === 'events' ? 'bg-amber-500 text-white' : 'bg-stone-100 dark:bg-stone-800 text-stone-500'">
                            {{ $organizedEvents->count() + $attendingEvents->count() }}
                        </span>
                        <template x-if="activeTab === 'events'">
                            <i data-lucide="check" class="w-4 h-4 text-amber-500"></i>
                        </template>
                    </div>
                </button>

                {{-- Option 6: Heritage Vault --}}
                @if(auth()->id() === $user->id || $vaultItems->count() > 0)
                    <button type="button" @click="activeTab = 'vault'; isTabDropdownOpen = false; $nextTick(() => { if (typeof lucide !== 'undefined') lucide.createIcons(); });"
                        :class="activeTab === 'vault' ? 'bg-amber-50 dark:bg-amber-950/40 text-amber-600 dark:text-amber-400 font-bold' : 'text-stone-700 dark:text-stone-300 hover:bg-stone-50 dark:hover:bg-stone-800'"
                        class="w-full flex items-center justify-between px-3 py-2.5 rounded-xl text-xs sm:text-sm font-semibold transition text-left cursor-pointer gap-2">
                        <div class="flex items-center gap-2.5 sm:gap-3 min-w-0 flex-1">
                            <div class="w-7 h-7 rounded-lg bg-rose-100 dark:bg-rose-950/60 text-rose-600 flex items-center justify-center shrink-0">
                                <i data-lucide="lock" class="w-4 h-4"></i>
                            </div>
                            <div class="min-w-0 flex-1">
                                <span class="block truncate">Personal Heritage Vault</span>
                                <span class="text-[10px] text-stone-400 font-normal truncate block">Encrypted documents & heirlooms</span>
                            </div>
                        </div>
                        <div class="flex items-center gap-2 shrink-0">
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold"
                                :class="activeTab === 'vault' ? 'bg-amber-500 text-white' : 'bg-stone-100 dark:bg-stone-800 text-stone-500'">
                                {{ $vaultCount }}
                            </span>
                            <template x-if="activeTab === 'vault'">
                                <i data-lucide="check" class="w-4 h-4 text-amber-500"></i>
                            </template>
                        </div>
                    </button>
                @endif

                {{-- Option 7: Privacy Hub (Owner Only) --}}
                @if(auth()->id() === $user->id)
                    <button type="button" @click="activeTab = 'privacy_hub'; isTabDropdownOpen = false; $nextTick(() => { if (typeof lucide !== 'undefined') lucide.createIcons(); });"
                        :class="activeTab === 'privacy_hub' ? 'bg-amber-50 dark:bg-amber-950/40 text-amber-600 dark:text-amber-400 font-bold' : 'text-stone-700 dark:text-stone-300 hover:bg-stone-50 dark:hover:bg-stone-800'"
                        class="w-full flex items-center justify-between px-3 py-2.5 rounded-xl text-xs sm:text-sm font-semibold transition text-left cursor-pointer gap-2">
                        <div class="flex items-center gap-2.5 sm:gap-3 min-w-0 flex-1">
                            <div class="w-7 h-7 rounded-lg bg-emerald-100 dark:bg-emerald-950/60 text-emerald-600 flex items-center justify-center shrink-0">
                                <i data-lucide="shield" class="w-4 h-4"></i>
                            </div>
                            <div class="min-w-0 flex-1">
                                <span class="block truncate">Privacy & Sovereignty</span>
                                <span class="text-[10px] text-stone-400 font-normal truncate block">Data rights & vault protection controls</span>
                            </div>
                        </div>
                        <template x-if="activeTab === 'privacy_hub'">
                            <i data-lucide="check" class="w-4 h-4 text-amber-500 shrink-0"></i>
                        </template>
                    </button>
                @endif

            </div>
        </div>

        {{-- TAB 1: STORIES / TIMELINE FEED --}}
        <div x-show="activeTab === 'timeline'" class="space-y-4">
            
            {{-- Quick Post Trigger (Owner Only) --}}
            @auth
                @if(auth()->id() === $user->id)
                    <div class="bg-white dark:bg-stone-900 rounded-2xl sm:rounded-3xl p-3 sm:p-5 border border-stone-200/80 dark:border-stone-800 shadow-sm flex items-center gap-2.5 sm:gap-4 overflow-hidden">
                        <img src="{{ $user->profile_photo_url }}" class="w-9 h-9 sm:w-11 sm:h-11 rounded-xl sm:rounded-2xl object-cover shrink-0 user-avatar-sync">
                        <a href="{{ route('timeline.index') }}"
                            class="flex-1 min-w-0 text-left px-3 sm:px-5 py-2.5 sm:py-3 rounded-xl sm:rounded-2xl bg-stone-100 dark:bg-stone-800 hover:bg-stone-200 dark:hover:bg-stone-700/80 text-stone-500 dark:text-stone-400 text-xs sm:text-sm font-medium transition truncate">
                            Record a cultural memory or authentic story...
                        </a>
                        <a href="{{ route('timeline.index') }}" class="px-3 sm:px-4 py-2.5 sm:py-3 bg-amber-500 hover:bg-amber-600 text-white rounded-xl sm:rounded-2xl text-xs sm:text-sm font-bold transition flex items-center gap-1.5 shrink-0">
                            <i data-lucide="plus" class="w-4 h-4"></i>
                            <span class="hidden sm:inline">Post</span>
                        </a>
                    </div>
                @endif
            @endauth

            {{-- Posts List --}}
            @if($posts->count() > 0)
                <div class="space-y-3 sm:space-y-4">
                    @foreach($posts as $post)
                        <div class="bg-white dark:bg-stone-900 rounded-2xl sm:rounded-3xl p-4 sm:p-6 border border-stone-200/80 dark:border-stone-800 shadow-sm space-y-3 sm:space-y-4">
                            
                            {{-- Post Author Header --}}
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-2.5 sm:gap-3">
                                    <img src="{{ $user->profile_photo_url }}" class="w-9 h-9 sm:w-10 sm:h-10 rounded-xl object-cover user-avatar-sync">
                                    <div>
                                        <h3 class="font-bold text-stone-900 dark:text-white text-xs sm:text-sm">{{ $user->name }}</h3>
                                        <p class="text-[11px] sm:text-xs text-stone-400">{{ $post->created_at->diffForHumans() }}</p>
                                    </div>
                                </div>
                                @if($post->chapter)
                                    <span class="px-2.5 py-0.5 sm:px-3 sm:py-1 rounded-full text-[10px] sm:text-xs font-bold bg-amber-50 dark:bg-amber-950/40 text-amber-600 dark:text-amber-400 border border-amber-200 dark:border-amber-900">
                                        {{ $post->chapter }}
                                    </span>
                                @endif
                            </div>

                            {{-- Post Content --}}
                            <div class="text-stone-800 dark:text-stone-200 text-xs sm:text-sm leading-relaxed whitespace-pre-line">
                                {!! $post->formatted_content !!}
                            </div>

                            @if(!empty($post->tags))
                                <div class="flex flex-wrap gap-1.5 pt-1">
                                    @foreach($post->tags as $tag)
                                        <a href="{{ route('timeline.index', ['tag' => $tag]) }}"
                                            class="inline-flex items-center text-[11px] font-medium text-amber-600 dark:text-amber-400 bg-amber-50 dark:bg-amber-950/40 px-2 py-0.5 rounded-full hover:bg-amber-100 dark:hover:bg-amber-900/60 transition">
                                            #{{ $tag }}
                                        </a>
                                    @endforeach
                                </div>
                            @endif

                            {{-- Media Attachment (Full viewable, not sliced) --}}
                            @if($post->image)
                                <div class="rounded-xl sm:rounded-2xl overflow-hidden bg-stone-100 dark:bg-stone-800/60 border border-stone-100 dark:border-stone-800 group relative">
                                    <a href="{{ route('timeline.index') }}#post-{{ $post->id }}" 
                                       title="View story on Timeline"
                                       class="block cursor-pointer">
                                        <img src="{{ asset('storage/' . $post->image) }}" 
                                             alt="Story photo"
                                             class="w-full h-auto max-h-[650px] object-contain rounded-xl sm:rounded-2xl mx-auto transition-transform duration-300 group-hover:scale-[1.01]" 
                                             loading="lazy">
                                    </a>
                                </div>
                            @endif

                            {{-- Video Attachment --}}
                            @if($post->video)
                                <div class="rounded-xl sm:rounded-2xl overflow-hidden bg-black border border-stone-100 dark:border-stone-800">
                                    <video src="{{ asset('storage/' . $post->video) }}" controls class="w-full max-h-[600px]"></video>
                                </div>
                            @endif

                            {{-- Audio Attachment --}}
                            @if($post->audio)
                                <div class="p-2 sm:p-3 rounded-xl sm:rounded-2xl bg-stone-100 dark:bg-stone-800">
                                    <audio src="{{ asset('storage/' . $post->audio) }}" controls class="w-full"></audio>
                                </div>
                            @endif

                            {{-- Reactions Bar --}}
                            <div class="pt-2.5 sm:pt-3 border-t border-stone-100 dark:border-stone-800 flex items-center justify-between text-xs text-stone-500 dark:text-stone-400">
                                <div class="flex items-center gap-3 sm:gap-4">
                                    <span class="flex items-center gap-1 font-bold text-amber-500 text-[11px] sm:text-xs">
                                        <i data-lucide="zap" class="w-3.5 h-3.5 sm:w-4 sm:h-4 fill-current"></i>
                                        {{ $post->taps_count ?? 0 }} Taps
                                    </span>
                                    <button type="button" onclick="toggleProfileResonanceBox({{ $post->id }})" 
                                        class="flex items-center gap-1 font-bold text-purple-600 dark:text-purple-400 hover:text-purple-700 dark:hover:text-purple-300 text-[11px] sm:text-xs transition cursor-pointer">
                                        <i data-lucide="message-circle" class="w-3.5 h-3.5 sm:w-4 sm:h-4"></i>
                                        <span data-resonance-count="{{ $post->id }}">{{ $post->resonance_count ?? $post->resonances->count() }} Resonance</span>
                                    </button>
                                </div>
                                <a href="{{ route('timeline.index') }}#post-{{ $post->id }}" class="hover:text-amber-500 font-bold text-[11px] sm:text-xs flex items-center gap-1 transition-colors">
                                    <span>View on Timeline</span>
                                    <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                                </a>
                            </div>

                            {{-- Resonance (Comments) Box --}}
                            <div id="resonance-box-{{ $post->id }}" class="hidden pt-3 border-t border-stone-100 dark:border-stone-800 space-y-3">
                                <div id="resonance-list-{{ $post->id }}" class="space-y-2 sm:space-y-2.5">
                                    @forelse($post->resonances as $resonance)
                                        <div class="p-2.5 sm:p-3 bg-stone-50 dark:bg-stone-800/60 rounded-xl border border-stone-100 dark:border-stone-800 text-xs">
                                            <div class="flex items-center justify-between mb-1">
                                                <span class="font-bold text-stone-900 dark:text-stone-200">{{ $resonance->user->name }}</span>
                                                <span class="text-[10px] text-stone-400">{{ $resonance->created_at->diffForHumans() }}</span>
                                            </div>
                                            <div class="text-stone-700 dark:text-stone-300 leading-relaxed">{!! $resonance->formatted_content !!}</div>
                                        </div>
                                    @empty
                                        <p class="text-xs text-stone-400 dark:text-stone-500 py-1 italic">No resonances yet. Be the first to resonate!</p>
                                    @endforelse
                                </div>

                                @auth
                                    <div class="flex items-center gap-2 pt-1">
                                        <input type="text" id="resonance-input-{{ $post->id }}" placeholder="Write a resonance..."
                                            class="flex-1 border border-stone-200 dark:border-stone-700 bg-stone-50 dark:bg-stone-800/80 dark:text-stone-200 rounded-xl px-3 py-2 text-xs focus:ring-2 focus:ring-amber-500 focus:outline-none">
                                        <button type="button" onclick="submitProfileResonance({{ $post->id }})"
                                            class="px-3.5 py-2 glass-orange text-white rounded-xl text-xs font-bold transition flex items-center justify-center shrink-0 active:scale-95">
                                            <i data-lucide="send" class="w-3.5 h-3.5"></i>
                                        </button>
                                    </div>
                                @endauth
                            </div>

                        </div>
                    @endforeach

                    {{-- Pagination --}}
                    <div class="pt-2">
                        {{ $posts->links() }}
                    </div>
                </div>
            @else
                <div class="bg-white dark:bg-stone-900 rounded-2xl sm:rounded-3xl p-8 sm:p-12 text-center border border-stone-200/80 dark:border-stone-800 space-y-3">
                    <div class="w-14 h-14 sm:w-16 sm:h-16 rounded-2xl sm:rounded-3xl bg-amber-50 dark:bg-amber-950/40 text-amber-500 mx-auto flex items-center justify-center">
                        <i data-lucide="feather" class="w-7 h-7 sm:w-8 sm:h-8"></i>
                    </div>
                    <h3 class="text-base sm:text-lg font-bold text-stone-900 dark:text-white">No Stories Preserved Yet</h3>
                    <p class="text-xs sm:text-sm text-stone-500 max-w-sm mx-auto">Document authentic memories, family accounts, and milestones to create your living heritage timeline.</p>
                </div>
            @endif

        </div>

        {{-- TAB 2: LIFE CHAPTERS --}}
        <div x-show="activeTab === 'chapters'" class="space-y-4">
            
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-base sm:text-xl font-bold text-stone-900 dark:text-white">Life Chapters & Milestones</h2>
                    <p class="text-xs sm:text-sm text-stone-500">Chronological narrative of defining cultural eras and historical passages.</p>
                </div>
                @auth
                    @if(auth()->id() === $user->id)
                        <button @click="showChapterModal = true"
                            class="px-3.5 sm:px-4 py-2 sm:py-2.5 bg-amber-500 hover:bg-amber-600 text-white rounded-xl text-xs sm:text-sm font-bold transition flex items-center gap-1.5 shadow-sm shrink-0">
                            <i data-lucide="plus" class="w-4 h-4"></i>
                            <span>Add Chapter</span>
                        </button>
                    @endif
                @endauth
            </div>

            @if($lifeChapters->count() > 0)
                <div class="space-y-4 relative before:absolute before:inset-0 before:left-4 sm:before:left-7 before:w-0.5 before:bg-stone-200 dark:before:bg-stone-800">
                    @foreach($lifeChapters as $chapter)
                        <div class="relative flex items-start gap-3 sm:gap-6 group">
                            
                            {{-- Milestone Marker --}}
                            <div class="w-9 h-9 sm:w-14 sm:h-14 rounded-xl sm:rounded-2xl bg-amber-500 text-white flex items-center justify-center font-bold text-xs sm:text-sm shadow-md shrink-0 z-10">
                                <i data-lucide="bookmark" class="w-4 h-4 sm:w-6 sm:h-6"></i>
                            </div>

                            {{-- Chapter Card --}}
                            <div class="flex-1 bg-white dark:bg-stone-900 rounded-2xl sm:rounded-3xl p-4 sm:p-6 border border-stone-200/80 dark:border-stone-800 shadow-sm space-y-2">
                                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-1">
                                    <h3 class="font-black text-sm sm:text-lg text-stone-900 dark:text-white">
                                        {{ $chapter->title }}
                                    </h3>
                                    <span class="text-[11px] sm:text-xs font-bold px-2.5 py-0.5 rounded-full bg-stone-100 dark:bg-stone-800 text-stone-600 dark:text-stone-300 w-fit">
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
                                    <p class="text-xs sm:text-sm text-stone-600 dark:text-stone-300 leading-relaxed pt-1">
                                        {{ $chapter->description }}
                                    </p>
                                @endif

                                @auth
                                    @if(auth()->id() === $user->id)
                                        <div class="pt-2 flex justify-end">
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
                <div class="bg-white dark:bg-stone-900 rounded-2xl sm:rounded-3xl p-8 sm:p-12 text-center border border-stone-200/80 dark:border-stone-800 space-y-3">
                    <div class="w-14 h-14 sm:w-16 sm:h-16 rounded-2xl sm:rounded-3xl bg-amber-50 dark:bg-amber-950/40 text-amber-500 mx-auto flex items-center justify-center">
                        <i data-lucide="book" class="w-7 h-7 sm:w-8 sm:h-8"></i>
                    </div>
                    <h3 class="text-base sm:text-lg font-bold text-stone-900 dark:text-white">No Life Chapters Added Yet</h3>
                    <p class="text-xs sm:text-sm text-stone-500 max-w-sm mx-auto">Organize your journey into memorable milestones, migrations, and traditional rites of passage.</p>
                </div>
            @endif

        </div>

        {{-- TAB 3: CULTURAL STORIES & HUB CONTRIBUTIONS --}}
        <div x-show="activeTab === 'cultures'" class="space-y-4">
            
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-base sm:text-xl font-bold text-stone-900 dark:text-white">Cultural Stories & Traditions</h2>
                    <p class="text-xs sm:text-sm text-stone-500">Documented oral histories, folkloric practices, and sacred knowledge submitted to Kulturegram.</p>
                </div>
                <a href="{{ route('cultural-hub.create') }}"
                    class="px-3.5 sm:px-4 py-2 sm:py-2.5 bg-amber-500 hover:bg-amber-600 text-white rounded-xl text-xs sm:text-sm font-bold transition flex items-center gap-1.5 shadow-sm shrink-0">
                    <i data-lucide="plus" class="w-4 h-4"></i>
                    <span>Submit Tradition</span>
                </a>
            </div>

            @if($cultures->count() > 0)
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3 sm:gap-4">
                    @foreach($cultures as $culture)
                        <div class="bg-white dark:bg-stone-900 rounded-2xl sm:rounded-3xl p-4 sm:p-5 border border-stone-200/80 dark:border-stone-800 shadow-sm flex flex-col justify-between space-y-3 sm:space-y-4 hover:border-amber-500/40 transition">
                            
                            <div class="space-y-2">
                                <div class="flex items-center justify-between">
                                    <span class="text-[10px] font-black uppercase tracking-wider px-2 py-0.5 rounded-full bg-stone-100 dark:bg-stone-800 text-stone-600 dark:text-stone-300">
                                        {{ $culture->category }}
                                    </span>
                                    
                                    {{-- Status Badge --}}
                                    @if($culture->status === 'approved')
                                        <span class="text-[10px] font-black uppercase tracking-wider px-2 py-0.5 rounded-full bg-emerald-50 text-emerald-600 dark:bg-emerald-950/40 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800 flex items-center gap-1">
                                            <i data-lucide="check-circle" class="w-3 h-3"></i> Approved
                                        </span>
                                    @elseif($culture->status === 'featured')
                                        <span class="text-[10px] font-black uppercase tracking-wider px-2 py-0.5 rounded-full bg-purple-50 text-purple-600 dark:bg-purple-950/40 dark:text-purple-400 border border-purple-200 dark:border-purple-800 flex items-center gap-1">
                                            <i data-lucide="sparkles" class="w-3 h-3"></i> Featured
                                        </span>
                                    @else
                                        <span class="text-[10px] font-black uppercase tracking-wider px-2 py-0.5 rounded-full bg-amber-50 text-amber-600 dark:bg-amber-950/40 dark:text-amber-400 border border-amber-200 dark:border-amber-800 flex items-center gap-1">
                                            <i data-lucide="clock" class="w-3 h-3"></i> Under Review
                                        </span>
                                    @endif
                                </div>

                                <h3 class="font-bold text-sm sm:text-base text-stone-900 dark:text-white line-clamp-1">
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

                            <div class="pt-2.5 border-t border-stone-100 dark:border-stone-800 flex items-center justify-between">
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
                <div class="bg-white dark:bg-stone-900 rounded-2xl sm:rounded-3xl p-8 sm:p-12 text-center border border-stone-200/80 dark:border-stone-800 space-y-3">
                    <div class="w-14 h-14 sm:w-16 sm:h-16 rounded-2xl sm:rounded-3xl bg-amber-50 dark:bg-amber-950/40 text-amber-500 mx-auto flex items-center justify-center">
                        <i data-lucide="award" class="w-7 h-7 sm:w-8 sm:h-8"></i>
                    </div>
                    <h3 class="text-base sm:text-lg font-bold text-stone-900 dark:text-white">No Cultural Stories Submitted</h3>
                    <p class="text-xs sm:text-sm text-stone-500 max-w-sm mx-auto">Document authentic dances, recipes, dialects, crafts, or rituals to preserve them for future generations.</p>
                </div>
            @endif

        </div>

        {{-- TAB 4: COMMUNITIES & TRIBES --}}
        <div x-show="activeTab === 'communities'" class="space-y-4">
            
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-base sm:text-xl font-bold text-stone-900 dark:text-white">Tribes & Communities</h2>
                    <p class="text-xs sm:text-sm text-stone-500">Cultural circles and preservation collectives you participate in.</p>
                </div>
                <a href="{{ route('communities.create') }}"
                    class="px-3.5 sm:px-4 py-2 sm:py-2.5 bg-amber-500 hover:bg-amber-600 text-white rounded-xl text-xs sm:text-sm font-bold transition flex items-center gap-1.5 shadow-sm shrink-0">
                    <i data-lucide="plus" class="w-4 h-4"></i>
                    <span>Create Tribe</span>
                </a>
            </div>

            @if($communities->count() > 0)
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3 sm:gap-4">
                    @foreach($communities as $community)
                        <div class="bg-white dark:bg-stone-900 rounded-2xl sm:rounded-3xl p-4 sm:p-5 border border-stone-200/80 dark:border-stone-800 shadow-sm flex flex-col justify-between space-y-3 sm:space-y-4 hover:border-amber-500/40 transition">
                            
                            <div class="flex items-start gap-3">
                                <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-xl sm:rounded-2xl bg-amber-100 dark:bg-amber-950/60 text-amber-600 flex items-center justify-center shrink-0 overflow-hidden">
                                    @if($community->image)
                                        <img src="{{ asset('storage/' . $community->image) }}" class="w-full h-full object-cover">
                                    @else
                                        <i data-lucide="users" class="w-5 h-5 sm:w-6 sm:h-6"></i>
                                    @endif
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center justify-between gap-1">
                                        <h3 class="font-bold text-stone-900 dark:text-white text-xs sm:text-sm truncate">
                                            {{ $community->name }}
                                        </h3>
                                        @if($community->created_by === $user->id)
                                            <span class="text-[9px] font-black uppercase tracking-wider px-2 py-0.5 rounded-full bg-amber-500 text-white shrink-0">Founder</span>
                                        @endif
                                    </div>
                                    <p class="text-[11px] sm:text-xs text-stone-400 mt-0.5 truncate">
                                        {{ $community->members_count ?? 1 }} members • {{ $community->category }}
                                    </p>
                                </div>
                            </div>

                            <p class="text-xs text-stone-600 dark:text-stone-300 line-clamp-2 leading-relaxed">
                                {{ $community->description }}
                            </p>

                            <div class="pt-2.5 border-t border-stone-100 dark:border-stone-800 flex items-center justify-between">
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
                <div class="bg-white dark:bg-stone-900 rounded-2xl sm:rounded-3xl p-8 sm:p-12 text-center border border-stone-200/80 dark:border-stone-800 space-y-3">
                    <div class="w-14 h-14 sm:w-16 sm:h-16 rounded-2xl sm:rounded-3xl bg-amber-50 dark:bg-amber-950/40 text-amber-500 mx-auto flex items-center justify-center">
                        <i data-lucide="users" class="w-7 h-7 sm:w-8 sm:h-8"></i>
                    </div>
                    <h3 class="text-base sm:text-lg font-bold text-stone-900 dark:text-white">No Communities Joined Yet</h3>
                    <p class="text-xs sm:text-sm text-stone-500 max-w-sm mx-auto">Join heritage circles or start your own cultural collective.</p>
                    <a href="{{ route('communities.index') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-stone-900 text-white rounded-xl text-xs font-bold mt-2">
                        Browse Tribes
                    </a>
                </div>
            @endif

        </div>

        {{-- TAB 5: GATHERINGS & WORKSHOPS --}}
        <div x-show="activeTab === 'events'" class="space-y-5 sm:space-y-6">
            
            {{-- Organized Gatherings --}}
            <div class="space-y-3 sm:space-y-4">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-base sm:text-xl font-bold text-stone-900 dark:text-white">Gatherings You Host</h2>
                        <p class="text-xs sm:text-sm text-stone-500">Cultural masterclasses, festivals, and ceremonies organized by you.</p>
                    </div>
                    @auth
                        @if(auth()->id() === $user->id)
                            <a href="{{ route('events.create') }}"
                                class="px-3.5 sm:px-4 py-2 sm:py-2.5 bg-amber-500 hover:bg-amber-600 text-white rounded-xl text-xs sm:text-sm font-bold transition flex items-center gap-1.5 shadow-sm shrink-0">
                                <i data-lucide="plus" class="w-4 h-4"></i>
                                <span>Host Gathering</span>
                            </a>
                        @endif
                    @endauth
                </div>

                @if($organizedEvents->count() > 0)
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3 sm:gap-4">
                        @foreach($organizedEvents as $event)
                            <div class="bg-white dark:bg-stone-900 rounded-2xl sm:rounded-3xl p-4 sm:p-5 border border-stone-200/80 dark:border-stone-800 shadow-sm flex flex-col justify-between space-y-3 sm:space-y-4 hover:border-amber-500/40 transition">
                                <div class="space-y-2">
                                    <div class="flex items-center justify-between text-xs">
                                        <span class="font-bold text-amber-500 uppercase tracking-wider text-[10px] px-2 py-0.5 rounded bg-amber-50 dark:bg-amber-950/40">
                                            {{ $event->type }}
                                        </span>
                                        <span class="font-semibold text-stone-400 text-xs">
                                            {{ $event->event_date->format('M j, Y') }}
                                        </span>
                                    </div>
                                    <h3 class="font-bold text-sm sm:text-base text-stone-900 dark:text-white line-clamp-1">{{ $event->title }}</h3>
                                    <p class="text-xs text-stone-500 flex items-center gap-1">
                                        <i data-lucide="{{ $event->is_online ? 'video' : 'map-pin' }}" class="w-3.5 h-3.5"></i>
                                        <span class="truncate">{{ $event->location }}</span>
                                    </p>
                                </div>
                                <div class="pt-2.5 border-t border-stone-100 dark:border-stone-800 flex items-center justify-between text-xs font-bold">
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
                    <div class="bg-white dark:bg-stone-900 rounded-2xl sm:rounded-3xl p-6 sm:p-8 text-center border border-stone-200/80 dark:border-stone-800 text-xs text-stone-500">
                        No hosted gatherings currently scheduled.
                    </div>
                @endif
            </div>

            {{-- Attending Gatherings --}}
            <div class="space-y-3 sm:space-y-4 pt-4 border-t border-stone-200/80 dark:border-stone-800">
                <div>
                    <h2 class="text-base sm:text-xl font-bold text-stone-900 dark:text-white">Attending Gatherings & Tickets</h2>
                    <p class="text-xs sm:text-sm text-stone-500">Upcoming gatherings, masterclasses, and tickets you have reserved.</p>
                </div>

                @if($attendingEvents->count() > 0)
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3 sm:gap-4">
                        @foreach($attendingEvents as $event)
                            <div class="bg-white dark:bg-stone-900 rounded-2xl sm:rounded-3xl p-4 sm:p-5 border border-stone-200/80 dark:border-stone-800 shadow-sm flex flex-col justify-between space-y-3 sm:space-y-4">
                                <div class="space-y-2">
                                    <div class="flex items-center justify-between text-xs">
                                        <span class="font-bold text-emerald-600 dark:text-emerald-400 px-2 py-0.5 rounded bg-emerald-50 dark:bg-emerald-950/40 text-[10px] uppercase">
                                            <i data-lucide="ticket" class="w-3 h-3 inline"></i> Active Pass
                                        </span>
                                        <span class="font-semibold text-stone-400 text-xs">
                                            {{ $event->event_date->format('M j, Y') }}
                                        </span>
                                    </div>
                                    <h3 class="font-bold text-sm sm:text-base text-stone-900 dark:text-white line-clamp-1">{{ $event->title }}</h3>
                                    <p class="text-xs text-stone-500 flex items-center gap-1">
                                        <i data-lucide="{{ $event->is_online ? 'video' : 'map-pin' }}" class="w-3.5 h-3.5"></i>
                                        <span class="truncate">{{ $event->location }}</span>
                                    </p>
                                </div>
                                <div class="pt-2.5 border-t border-stone-100 dark:border-stone-800 flex items-center justify-between text-xs font-bold">
                                    <a href="{{ route('events.show', $event) }}" class="text-amber-600 dark:text-amber-400 hover:underline">View Pass</a>
                                    @if($event->virtual_room_id)
                                        <a href="{{ route('events.meet', $event) }}" class="px-2.5 sm:px-3 py-1 bg-blue-600 text-white rounded-lg flex items-center gap-1 shadow-sm">
                                            <i data-lucide="video" class="w-3.5 h-3.5"></i> Enter Classroom
                                        </a>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="bg-white dark:bg-stone-900 rounded-2xl sm:rounded-3xl p-6 sm:p-8 text-center border border-stone-200/80 dark:border-stone-800 text-xs text-stone-500">
                        You have not registered for any upcoming gatherings yet. <a href="{{ route('events.index') }}" class="text-amber-500 font-bold underline">Discover gatherings</a>.
                    </div>
                @endif
            </div>

        </div>

        {{-- TAB 6: HERITAGE VAULT (OWNER & PUBLIC) --}}
        <div x-show="activeTab === 'vault'" class="space-y-4">
            
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-base sm:text-xl font-bold text-stone-900 dark:text-white">Personal Heritage Vault</h2>
                    <p class="text-xs sm:text-sm text-stone-500">Encrypted personal vault for family documents, ancestral heirlooms, and historical media.</p>
                </div>
                <div class="flex items-center gap-2">
                    <a href="{{ route('vault.create') }}"
                        class="px-3.5 sm:px-4 py-2 bg-amber-500 hover:bg-amber-600 text-white rounded-xl text-xs sm:text-sm font-bold transition flex items-center gap-1.5 shadow-sm shrink-0">
                        <i data-lucide="upload" class="w-4 h-4"></i>
                        <span>Upload Heirloom</span>
                    </a>
                    <a href="{{ route('vault.index') }}"
                        class="px-3.5 sm:px-4 py-2 bg-stone-100 dark:bg-stone-800 hover:bg-stone-200 dark:hover:bg-stone-700 text-stone-700 dark:text-stone-300 rounded-xl text-xs sm:text-sm font-bold transition">
                        Full Vault
                    </a>
                </div>
            </div>

            @if($vaultItems->count() > 0)
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
                    @foreach($vaultItems as $item)
                        <div class="bg-white dark:bg-stone-900 rounded-2xl sm:rounded-3xl p-4 border border-stone-200/80 dark:border-stone-800 shadow-sm flex flex-col justify-between space-y-3 hover:border-amber-500/40 transition">
                            <div class="flex items-center justify-between">
                                <span class="p-2 rounded-xl bg-amber-50 dark:bg-amber-950/40 text-amber-500">
                                    <i data-lucide="{{ $item->type === 'photos' ? 'image' : ($item->type === 'audio' ? 'music' : ($item->type === 'videos' ? 'video' : 'file-text')) }}" class="w-4 h-4"></i>
                                </span>
                                <span class="text-[9px] font-black uppercase tracking-wider px-2 py-0.5 rounded-full bg-stone-100 dark:bg-stone-800 text-stone-500">
                                    {{ $item->access_level }}
                                </span>
                            </div>

                            <div>
                                <h3 class="font-bold text-xs sm:text-sm text-stone-900 dark:text-white line-clamp-1">{{ $item->title }}</h3>
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
                <div class="bg-white dark:bg-stone-900 rounded-2xl sm:rounded-3xl p-8 sm:p-12 text-center border border-stone-200/80 dark:border-stone-800 space-y-3">
                    <div class="w-14 h-14 sm:w-16 sm:h-16 rounded-2xl sm:rounded-3xl bg-amber-50 dark:bg-amber-950/40 text-amber-500 mx-auto flex items-center justify-center">
                        <i data-lucide="lock" class="w-7 h-7 sm:w-8 sm:h-8"></i>
                    </div>
                    <h3 class="text-base sm:text-lg font-bold text-stone-900 dark:text-white">Vault is Empty</h3>
                    <p class="text-xs sm:text-sm text-stone-500 max-w-sm mx-auto">Upload ancestral documents, audio narratives, and photographic records to your private vault.</p>
                </div>
            @endif

        </div>

        {{-- TAB 7: PRIVACY, SOVEREIGNTY & SETTINGS HUB (OWNER ONLY) --}}
        @if(auth()->id() === $user->id)
            <div x-show="activeTab === 'privacy_hub'" class="space-y-4 sm:space-y-6">
                
                <div class="bg-white dark:bg-stone-900 rounded-2xl sm:rounded-3xl p-5 sm:p-8 border border-stone-200/80 dark:border-stone-800 shadow-sm space-y-5 sm:space-y-6">
                    <div class="flex items-center gap-3 pb-4 border-b border-stone-100 dark:border-stone-800">
                        <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-xl sm:rounded-2xl bg-amber-500/10 text-amber-600 dark:text-amber-400 flex items-center justify-center shrink-0">
                            <i data-lucide="shield-check" class="w-5 h-5 sm:w-6 sm:h-6"></i>
                        </div>
                        <div>
                            <h2 class="text-lg sm:text-xl font-bold text-stone-900 dark:text-white">Cultural Sovereignty & Privacy Controls</h2>
                            <p class="text-xs sm:text-sm text-stone-500">Your rights, data ownership guarantees, and platform transparency.</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4">
                        
                        <div class="p-4 sm:p-5 rounded-2xl bg-stone-50 dark:bg-stone-800/50 border border-stone-200 dark:border-stone-700/60 space-y-2.5">
                            <h3 class="font-bold text-stone-900 dark:text-white flex items-center gap-2 text-xs sm:text-base">
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

                        <div class="p-4 sm:p-5 rounded-2xl bg-stone-50 dark:bg-stone-800/50 border border-stone-200 dark:border-stone-700/60 space-y-2.5">
                            <h3 class="font-bold text-stone-900 dark:text-white flex items-center gap-2 text-xs sm:text-base">
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

                        <div class="p-4 sm:p-5 rounded-2xl bg-stone-50 dark:bg-stone-800/50 border border-stone-200 dark:border-stone-700/60 space-y-2.5">
                            <h3 class="font-bold text-stone-900 dark:text-white flex items-center gap-2 text-xs sm:text-base">
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

                        <div class="p-4 sm:p-5 rounded-2xl bg-stone-50 dark:bg-stone-800/50 border border-stone-200 dark:border-stone-700/60 space-y-2.5">
                            <h3 class="font-bold text-stone-900 dark:text-white flex items-center gap-2 text-xs sm:text-base">
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

    {{-- AVATAR ADJUSTMENT & PREVIEW MODAL --}}
    @auth
        @if(auth()->id() === $user->id)
            <div x-show="showAvatarModal" x-cloak 
                class="fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-4 bg-black/75 backdrop-blur-md"
                @keydown.escape.window="closeAvatarModal()">
                <div class="bg-white dark:bg-stone-900 rounded-3xl max-w-md w-full p-5 sm:p-6 border border-stone-200 dark:border-stone-800 shadow-2xl space-y-4 relative"
                    @click.away="closeAvatarModal()">
                    
                    {{-- Modal Header --}}
                    <div class="flex items-center justify-between pb-3 border-b border-stone-100 dark:border-stone-800">
                        <div>
                            <h3 class="text-base sm:text-lg font-black text-stone-900 dark:text-white flex items-center gap-2">
                                <i data-lucide="camera" class="w-5 h-5 text-amber-500"></i>
                                Adjust Profile Picture
                            </h3>
                            <p class="text-[11px] sm:text-xs text-stone-500">Scale, rotate, and frame your avatar.</p>
                        </div>
                        <button type="button" @click="closeAvatarModal()" class="w-8 h-8 rounded-full bg-stone-100 dark:bg-stone-800 flex items-center justify-center text-stone-400 hover:text-stone-600 dark:hover:text-stone-200 transition">
                            <i data-lucide="x" class="w-4 h-4"></i>
                        </button>
                    </div>

                    {{-- Interactive Crop/Preview Viewport --}}
                    <div class="relative w-52 h-52 sm:w-64 sm:h-64 mx-auto rounded-3xl overflow-hidden bg-stone-950 flex items-center justify-center border-2 border-stone-800 shadow-inner group select-none">
                        {{-- Selected Image with Scale & Rotation --}}
                        <img :src="avatarModalImg" 
                            class="max-w-none origin-center pointer-events-none select-none transition-transform duration-75"
                            :style="'transform: scale(' + avatarScale + ') rotate(' + avatarRotation + 'deg); width: 100%; height: 100%; object-fit: cover;'">

                        {{-- Circular Avatar Cutout Overlay --}}
                        <div class="absolute inset-0 rounded-full border-[34px] sm:border-[44px] border-black/60 pointer-events-none shadow-[inset_0_0_0_2px_rgba(245,158,11,0.6)]"></div>
                        
                        {{-- Target Guide Label --}}
                        <span class="absolute bottom-2 text-[10px] font-bold text-white/70 bg-black/50 px-2.5 py-0.5 rounded-full pointer-events-none">
                            Preview Crop
                        </span>
                    </div>

                    {{-- Controls: Zoom Scale & Rotate --}}
                    <div class="space-y-2.5 bg-stone-50 dark:bg-stone-800/60 p-3.5 rounded-2xl border border-stone-200/60 dark:border-stone-700/60">
                        
                        {{-- Zoom Slider --}}
                        <div class="space-y-1">
                            <div class="flex items-center justify-between text-xs font-bold text-stone-600 dark:text-stone-300">
                                <span class="flex items-center gap-1.5">
                                    <i data-lucide="zoom-in" class="w-3.5 h-3.5 text-amber-500"></i>
                                    Size / Scale
                                </span>
                                <span class="text-amber-500 font-mono text-[11px]" x-text="Math.round(avatarScale * 100) + '%'"></span>
                            </div>
                            <div class="flex items-center gap-2">
                                <button type="button" @click="avatarScale = Math.max(0.5, +(avatarScale - 0.1).toFixed(2))"
                                    class="w-7 h-7 rounded-lg bg-white dark:bg-stone-700 border border-stone-200 dark:border-stone-600 flex items-center justify-center font-bold text-stone-700 dark:text-stone-200 text-xs hover:bg-stone-100 active:scale-95">
                                    -
                                </button>
                                <input type="range" min="0.5" max="2.5" step="0.05" x-model.number="avatarScale"
                                    class="w-full accent-amber-500 cursor-pointer h-1.5 bg-stone-200 dark:bg-stone-700 rounded-lg">
                                <button type="button" @click="avatarScale = Math.min(2.5, +(avatarScale + 0.1).toFixed(2))"
                                    class="w-7 h-7 rounded-lg bg-white dark:bg-stone-700 border border-stone-200 dark:border-stone-600 flex items-center justify-center font-bold text-stone-700 dark:text-stone-200 text-xs hover:bg-stone-100 active:scale-95">
                                    +
                                </button>
                            </div>
                        </div>

                        {{-- Tool Buttons (Rotate & Reset) --}}
                        <div class="flex items-center justify-between pt-1 border-t border-stone-200/50 dark:border-stone-700/50 text-xs">
                            <button type="button" @click="avatarRotation = (avatarRotation + 90) % 360"
                                class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-xl bg-white dark:bg-stone-700 border border-stone-200 dark:border-stone-600 font-semibold text-stone-700 dark:text-stone-200 hover:bg-stone-100 active:scale-95 text-[11px]">
                                <i data-lucide="rotate-cw" class="w-3.5 h-3.5 text-amber-500"></i>
                                Rotate 90°
                            </button>

                            <button type="button" @click="avatarScale = 1.0; avatarRotation = 0;"
                                class="inline-flex items-center gap-1 px-2 py-1.5 rounded-xl text-stone-500 hover:text-stone-800 dark:hover:text-stone-200 font-semibold active:scale-95 text-[11px]">
                                <i data-lucide="refresh-cw" class="w-3 h-3"></i>
                                Reset
                            </button>
                        </div>

                    </div>

                    {{-- File Info Badge --}}
                    <div class="flex items-center justify-between text-[11px] text-stone-400 px-1">
                        <span class="truncate max-w-[200px]" x-text="avatarFilename"></span>
                        <span x-text="avatarFilesize"></span>
                    </div>

                    {{-- Action Buttons --}}
                    <div class="flex items-center justify-end gap-2 pt-1">
                        <button type="button" @click="closeAvatarModal()"
                            class="px-4 py-2.5 rounded-xl text-xs sm:text-sm font-bold text-stone-600 dark:text-stone-400 hover:bg-stone-100 dark:hover:bg-stone-800 transition">
                            Cancel
                        </button>
                        <button type="button" @click="saveAvatar()" :disabled="isSavingAvatar"
                            class="px-4 sm:px-5 py-2.5 rounded-xl bg-amber-500 hover:bg-amber-600 active:scale-95 text-white text-xs sm:text-sm font-bold transition shadow-lg flex items-center gap-2 disabled:opacity-50">
                            <span x-show="!isSavingAvatar" class="flex items-center gap-1.5">
                                <i data-lucide="check" class="w-4 h-4"></i>
                                Apply & Save Avatar
                            </span>
                            <span x-show="isSavingAvatar" class="flex items-center gap-1.5">
                                <i data-lucide="loader" class="w-4 h-4 animate-spin"></i>
                                Saving...
                            </span>
                        </button>
                    </div>

                </div>
            </div>
        @endif
    @endauth

    {{-- COVER BANNER PREVIEW & ADJUSTMENT MODAL --}}
    @auth
        @if(auth()->id() === $user->id)
            <div x-show="showCoverModal" x-cloak 
                class="fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-4 bg-black/75 backdrop-blur-md"
                @keydown.escape.window="closeCoverModal()">
                <div class="bg-white dark:bg-stone-900 rounded-3xl max-w-xl w-full p-5 sm:p-6 border border-stone-200 dark:border-stone-800 shadow-2xl space-y-4 relative"
                    @click.away="closeCoverModal()">
                    
                    {{-- Header --}}
                    <div class="flex items-center justify-between pb-3 border-b border-stone-100 dark:border-stone-800">
                        <div>
                            <h3 class="text-base sm:text-lg font-black text-stone-900 dark:text-white flex items-center gap-2">
                                <i data-lucide="image" class="w-5 h-5 text-amber-500"></i>
                                Preview Cover Banner
                            </h3>
                            <p class="text-[11px] sm:text-xs text-stone-500">Check how your banner appears on mobile and desktop.</p>
                        </div>
                        <button type="button" @click="closeCoverModal()" class="w-8 h-8 rounded-full bg-stone-100 dark:bg-stone-800 flex items-center justify-center text-stone-400 hover:text-stone-600 dark:hover:text-stone-200 transition">
                            <i data-lucide="x" class="w-4 h-4"></i>
                        </button>
                    </div>

                    {{-- Widescreen Banner Preview Frame --}}
                    <div class="relative w-full aspect-[16/7] rounded-2xl overflow-hidden bg-stone-950 flex items-center justify-center border-2 border-stone-800 shadow-inner group select-none">
                        <img :src="coverModalImg" 
                            class="max-w-none origin-center pointer-events-none select-none transition-transform duration-75"
                            :style="'transform: scale(' + coverScale + ') translateY(' + coverOffsetY + 'px); width: 100%; height: 100%; object-fit: cover;'">

                        {{-- Gradient overlay simulation --}}
                        <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-transparent to-black/20 pointer-events-none"></div>

                        {{-- Avatar silhouette simulation in corner --}}
                        <div class="absolute bottom-2 left-3 sm:bottom-3 sm:left-5 flex items-end gap-2 pointer-events-none">
                            <div class="w-9 h-9 sm:w-12 sm:h-12 rounded-2xl border-2 border-white/80 bg-stone-800/80 backdrop-blur-sm overflow-hidden flex items-center justify-center shadow-lg">
                                <i data-lucide="user" class="w-5 h-5 text-white/70"></i>
                            </div>
                            <span class="text-[10px] font-bold text-white drop-shadow pb-0.5 hidden sm:inline">Profile Position</span>
                        </div>

                        <span class="absolute top-2 right-2 text-[10px] font-bold text-white/80 bg-black/60 backdrop-blur-sm px-2.5 py-1 rounded-full pointer-events-none">
                            Header Preview
                        </span>
                    </div>

                    {{-- Scale and Vertical Positioning Controls --}}
                    <div class="space-y-2.5 bg-stone-50 dark:bg-stone-800/60 p-3.5 rounded-2xl border border-stone-200/60 dark:border-stone-700/60">
                        <div class="space-y-1">
                            <div class="flex items-center justify-between text-xs font-bold text-stone-600 dark:text-stone-300">
                                <span class="flex items-center gap-1.5">
                                    <i data-lucide="zoom-in" class="w-3.5 h-3.5 text-amber-500"></i>
                                    Banner Zoom
                                </span>
                                <span class="text-amber-500 font-mono text-[11px]" x-text="Math.round(coverScale * 100) + '%'"></span>
                            </div>
                            <div class="flex items-center gap-2">
                                <button type="button" @click="coverScale = Math.max(0.8, +(coverScale - 0.1).toFixed(2))"
                                    class="w-7 h-7 rounded-lg bg-white dark:bg-stone-700 border border-stone-200 dark:border-stone-600 flex items-center justify-center font-bold text-stone-700 dark:text-stone-200 text-xs hover:bg-stone-100 active:scale-95">
                                    -
                                </button>
                                <input type="range" min="0.8" max="2.0" step="0.05" x-model.number="coverScale"
                                    class="w-full accent-amber-500 cursor-pointer h-1.5 bg-stone-200 dark:bg-stone-700 rounded-lg">
                                <button type="button" @click="coverScale = Math.min(2.0, +(coverScale + 0.1).toFixed(2))"
                                    class="w-7 h-7 rounded-lg bg-white dark:bg-stone-700 border border-stone-200 dark:border-stone-600 flex items-center justify-center font-bold text-stone-700 dark:text-stone-200 text-xs hover:bg-stone-100 active:scale-95">
                                    +
                                </button>
                            </div>
                        </div>

                        {{-- Vertical Reposition Buttons --}}
                        <div class="flex items-center justify-between pt-1 border-t border-stone-200/50 dark:border-stone-700/50 text-xs">
                            <div class="flex items-center gap-1.5">
                                <button type="button" @click="coverOffsetY = Math.min(50, coverOffsetY + 10)"
                                    class="inline-flex items-center gap-1 px-2.5 py-1 rounded-xl bg-white dark:bg-stone-700 border border-stone-200 dark:border-stone-600 font-semibold text-stone-700 dark:text-stone-200 hover:bg-stone-100 active:scale-95 text-[11px]">
                                    <i data-lucide="arrow-up" class="w-3.5 h-3.5"></i> Up
                                </button>
                                <button type="button" @click="coverOffsetY = Math.max(-50, coverOffsetY - 10)"
                                    class="inline-flex items-center gap-1 px-2.5 py-1 rounded-xl bg-white dark:bg-stone-700 border border-stone-200 dark:border-stone-600 font-semibold text-stone-700 dark:text-stone-200 hover:bg-stone-100 active:scale-95 text-[11px]">
                                    <i data-lucide="arrow-down" class="w-3.5 h-3.5"></i> Down
                                </button>
                            </div>

                            <button type="button" @click="coverScale = 1.0; coverOffsetY = 0;"
                                class="inline-flex items-center gap-1 px-2 py-1 rounded-xl text-stone-500 hover:text-stone-800 dark:hover:text-stone-200 font-semibold active:scale-95 text-[11px]">
                                <i data-lucide="refresh-cw" class="w-3 h-3"></i> Reset
                            </button>
                        </div>
                    </div>

                    {{-- File Info Badge --}}
                    <div class="flex items-center justify-between text-[11px] text-stone-400 px-1">
                        <span class="truncate max-w-[200px]" x-text="coverFilename"></span>
                        <span x-text="coverFilesize"></span>
                    </div>

                    {{-- Action Buttons --}}
                    <div class="flex items-center justify-end gap-2 pt-1">
                        <button type="button" @click="closeCoverModal()"
                            class="px-4 py-2.5 rounded-xl text-xs sm:text-sm font-bold text-stone-600 dark:text-stone-400 hover:bg-stone-100 dark:hover:bg-stone-800 transition">
                            Cancel
                        </button>
                        <button type="button" @click="saveCover()" :disabled="isSavingCover"
                            class="px-4 sm:px-5 py-2.5 rounded-xl bg-amber-500 hover:bg-amber-600 active:scale-95 text-white text-xs sm:text-sm font-bold transition shadow-lg flex items-center gap-2 disabled:opacity-50">
                            <span x-show="!isSavingCover" class="flex items-center gap-1.5">
                                <i data-lucide="check" class="w-4 h-4"></i>
                                Apply & Save Banner
                            </span>
                            <span x-show="isSavingCover" class="flex items-center gap-1.5">
                                <i data-lucide="loader" class="w-4 h-4 animate-spin"></i>
                                Saving...
                            </span>
                        </button>
                    </div>

                </div>
            </div>
        @endif
    @endauth

    {{-- INLINE MODAL: ADD LIFE CHAPTER --}}
    @auth
        @if(auth()->id() === $user->id)
            <div x-show="showChapterModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-4 bg-black/60 backdrop-blur-sm"
                @keydown.escape.window="showChapterModal = false">
                <div class="bg-white dark:bg-stone-900 rounded-3xl max-w-lg w-full p-5 sm:p-8 border border-stone-200 dark:border-stone-800 shadow-2xl relative"
                    @click.away="showChapterModal = false">
                    
                    <div class="flex items-center justify-between pb-3 sm:pb-4 border-b border-stone-100 dark:border-stone-800 mb-4 sm:mb-5">
                        <h3 class="text-base sm:text-lg font-black text-stone-900 dark:text-white flex items-center gap-2">
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

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
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

                        <div class="pt-2 flex justify-end gap-2">
                            <button type="button" @click="showChapterModal = false" class="px-4 py-2 text-xs sm:text-sm font-bold text-stone-500 hover:text-stone-700">Cancel</button>
                            <button type="submit" class="px-4 sm:px-5 py-2 bg-amber-500 hover:bg-amber-600 text-white text-xs sm:text-sm font-bold rounded-xl shadow-md">Save Chapter</button>
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
        if (typeof lucide !== 'undefined') {
            lucide.createIcons();
        }
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

        function submitProfileResonance(postId) {
            const input = document.getElementById(`resonance-input-${postId}`);
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
                body: JSON.stringify({ content })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    const list = document.getElementById(`resonance-list-${postId}`);
                    if (list) {
                        const emptyPlaceholder = list.querySelector('.italic');
                        if (emptyPlaceholder) emptyPlaceholder.remove();

                        const newDiv = document.createElement('div');
                        newDiv.className = 'p-2.5 sm:p-3 bg-stone-50 dark:bg-stone-800/60 rounded-xl border border-stone-100 dark:border-stone-800 text-xs';
                        newDiv.innerHTML = `
                            <div class="flex items-center justify-between mb-1">
                                <span class="font-bold text-stone-900 dark:text-stone-200">${data.resonance.user.name}</span>
                                <span class="text-[10px] text-stone-400">Just now</span>
                            </div>
                            <div class="text-stone-700 dark:text-stone-300 leading-relaxed">${data.resonance.formatted_content || data.resonance.content}</div>
                        `;
                        list.appendChild(newDiv);
                    }
                    input.value = '';
                    const countBadge = document.querySelector(`[data-resonance-count="${postId}"]`);
                    if (countBadge) {
                        countBadge.textContent = `${data.count} Resonance`;
                    }
                }
            })
            .catch(err => console.error('Profile resonance error:', err));
        }
    @endauth

    function toggleProfileResonanceBox(postId) {
        const box = document.getElementById(`resonance-box-${postId}`);
        if (box) {
            box.classList.toggle('hidden');
        }
    }
</script>
@endpush