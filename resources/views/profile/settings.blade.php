{{-- resources/views/profile/settings.blade.php --}}
@extends('layouts.app')

@section('content')
<div class="max-w-6xl mx-auto px-3 sm:px-6 py-4 sm:py-8 overflow-x-hidden w-full" x-data="{
    activeTab: '{{ $errors->has('password') ? 'data' : 'profile' }}',
    avatarPreview: null,
    coverPreview: null,
    showSignOutModal: false,
    showDeleteModal: {{ $errors->has('password') ? 'true' : 'false' }},

    openSignOutModal() {
        this.showSignOutModal = true;
        this.$nextTick(() => { if (typeof lucide !== 'undefined') lucide.createIcons(); });
    },

    openDeleteModal() {
        this.showDeleteModal = true;
        this.$nextTick(() => { if (typeof lucide !== 'undefined') lucide.createIcons(); });
    },

    // Avatar adjustment modal in settings
    showAvatarModal: false,
    avatarFile: null,
    avatarFilename: '',
    avatarFilesize: '',
    avatarModalImg: '',
    avatarScale: 1.0,
    avatarRotation: 0,
    isSavingAvatar: false,

    // Cover adjustment modal in settings
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
        document.getElementById('settings-avatar-input')?.click();
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
            this.$nextTick(() => { if (typeof lucide !== 'undefined') lucide.createIcons(); });
        };
        reader.readAsDataURL(file);
    },

    closeAvatarModal() {
        this.showAvatarModal = false;
        this.avatarFile = null;
        this.avatarModalImg = '';
        const input = document.getElementById('settings-avatar-input');
        if (input) input.value = '';
    },

    saveAvatarDirect() {
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
                        this.avatarPreview = data.avatar_url;
                        const avatarImg = document.getElementById('settings-avatar-img');
                        if (avatarImg) avatarImg.src = data.avatar_url;
                        this.notify(data.message || 'Profile picture updated successfully!');
                        this.closeAvatarModal();
                    } else {
                        window.location.reload();
                    }
                })
                .catch(err => {
                    console.error('Avatar update failed', err);
                    this.isSavingAvatar = false;
                    this.closeAvatarModal();
                });
            }, 'image/jpeg', 0.92);
        };
        img.src = this.avatarModalImg;
    },

    triggerCoverSelect() {
        document.getElementById('settings-cover-input')?.click();
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
            this.$nextTick(() => { if (typeof lucide !== 'undefined') lucide.createIcons(); });
        };
        reader.readAsDataURL(file);
    },

    closeCoverModal() {
        this.showCoverModal = false;
        this.coverFile = null;
        this.coverModalImg = '';
        const input = document.getElementById('settings-cover-input');
        if (input) input.value = '';
    },

    saveCoverDirect() {
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
                        this.coverPreview = data.cover_url;
                        const coverImg = document.getElementById('settings-cover-img');
                        if (coverImg) coverImg.src = data.cover_url;
                        this.notify(data.message || 'Cover banner updated successfully!');
                        this.closeCoverModal();
                    } else {
                        window.location.reload();
                    }
                })
                .catch(err => {
                    console.error('Cover update failed', err);
                    this.isSavingCover = false;
                    this.closeCoverModal();
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

    {{-- Header --}}
    <div class="mb-4 sm:mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 sm:gap-4">
        <div>
            <div class="flex items-center gap-2 text-xs font-bold text-amber-600 dark:text-amber-400 uppercase tracking-wider mb-1">
                <a href="{{ route('profile.show') }}" class="hover:underline flex items-center gap-1">
                    <i data-lucide="arrow-left" class="w-3.5 h-3.5"></i> Back to Profile
                </a>
            </div>
            <h1 class="page-title text-xl sm:text-3xl font-black text-amber-500">Account & Privacy Settings</h1>
            <p class="text-xs sm:text-sm text-stone-500">Manage your profile identity, security, notifications, and cultural data sovereignty.</p>
        </div>

        <a href="{{ route('privacy') }}" class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl bg-stone-100 hover:bg-stone-200 dark:bg-stone-800 dark:hover:bg-stone-700 text-stone-700 dark:text-stone-300 text-xs font-bold transition w-fit">
            <i data-lucide="shield-check" class="w-4 h-4 text-amber-500"></i>
            Privacy Policy
        </a>
    </div>

    @if(session('success'))
        <div class="mb-4 sm:mb-6 p-3.5 sm:p-4 rounded-2xl bg-green-50 dark:bg-green-950/40 border border-green-200 dark:border-green-800 text-green-700 dark:text-green-300 text-xs sm:text-sm font-semibold flex items-center justify-between shadow-sm">
            <div class="flex items-center gap-2">
                <i data-lucide="check-circle" class="w-4 h-4 sm:w-5 sm:h-5 text-green-500 shrink-0"></i>
                <span>{{ session('success') }}</span>
            </div>
            <button type="button" @click="$el.parentElement.remove()" class="text-green-500 hover:text-green-700 p-1">
                <i data-lucide="x" class="w-4 h-4"></i>
            </button>
        </div>
    @endif

    @if($errors->any())
        <div class="mb-4 sm:mb-6 p-3.5 sm:p-4 rounded-2xl bg-red-50 dark:bg-red-950/40 border border-red-200 dark:border-red-800 text-red-700 dark:text-red-300 text-xs sm:text-sm font-semibold space-y-1">
            <div class="flex items-center gap-2 font-bold mb-1">
                <i data-lucide="alert-triangle" class="w-4 h-4 sm:w-5 sm:h-5 text-red-500 shrink-0"></i>
                <span>Please correct the errors below:</span>
            </div>
            <ul class="list-disc list-inside text-xs space-y-1 pl-4">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Mobile Tab Navigation (Icon-Only on Mobile) --}}
    <div class="md:hidden grid grid-cols-6 gap-2 py-1 mb-4">
        <button type="button" @click="activeTab = 'profile'" title="Profile & Identity" aria-label="Profile & Identity"
            :class="activeTab === 'profile' ? 'bg-amber-500 text-white shadow-md' : 'bg-white dark:bg-stone-900 text-stone-600 dark:text-stone-400 border border-stone-200 dark:border-stone-800'"
            class="py-2.5 rounded-xl transition flex items-center justify-center">
            <i data-lucide="user" class="w-4 h-4"></i>
        </button>
        <button type="button" @click="activeTab = 'security'" title="Security & Password" aria-label="Security & Password"
            :class="activeTab === 'security' ? 'bg-amber-500 text-white shadow-md' : 'bg-white dark:bg-stone-900 text-stone-600 dark:text-stone-400 border border-stone-200 dark:border-stone-800'"
            class="py-2.5 rounded-xl transition flex items-center justify-center">
            <i data-lucide="key" class="w-4 h-4"></i>
        </button>
        <button type="button" @click="activeTab = 'privacy'" title="Privacy & Sovereignty" aria-label="Privacy & Sovereignty"
            :class="activeTab === 'privacy' ? 'bg-amber-500 text-white shadow-md' : 'bg-white dark:bg-stone-900 text-stone-600 dark:text-stone-400 border border-stone-200 dark:border-stone-800'"
            class="py-2.5 rounded-xl transition flex items-center justify-center">
            <i data-lucide="shield" class="w-4 h-4"></i>
        </button>
        <button type="button" @click="activeTab = 'notifications'" title="Notifications" aria-label="Notifications"
            :class="activeTab === 'notifications' ? 'bg-amber-500 text-white shadow-md' : 'bg-white dark:bg-stone-900 text-stone-600 dark:text-stone-400 border border-stone-200 dark:border-stone-800'"
            class="py-2.5 rounded-xl transition flex items-center justify-center">
            <i data-lucide="bell" class="w-4 h-4"></i>
        </button>
        <button type="button" @click="activeTab = 'appearance'" title="Appearance" aria-label="Appearance"
            :class="activeTab === 'appearance' ? 'bg-amber-500 text-white shadow-md' : 'bg-white dark:bg-stone-900 text-stone-600 dark:text-stone-400 border border-stone-200 dark:border-stone-800'"
            class="py-2.5 rounded-xl transition flex items-center justify-center">
            <i data-lucide="palette" class="w-4 h-4"></i>
        </button>
        <button type="button" @click="activeTab = 'data'" title="Data & Account" aria-label="Data & Account"
            :class="activeTab === 'data' ? 'bg-amber-500 text-white shadow-md' : 'bg-white dark:bg-stone-900 text-stone-600 dark:text-stone-400 border border-stone-200 dark:border-stone-800'"
            class="py-2.5 rounded-xl transition flex items-center justify-center">
            <i data-lucide="database" class="w-4 h-4"></i>
        </button>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-12 gap-4 sm:gap-6 items-start">

        <!-- DESKTOP SIDEBAR NAVIGATION -->
        <aside class="hidden md:block md:col-span-4 bg-white dark:bg-stone-900 border border-stone-200/80 dark:border-stone-800 rounded-3xl p-3 sm:p-4 shadow-sm space-y-1">
            
            <button @click="activeTab = 'profile'"
                :class="activeTab === 'profile' ? 'bg-amber-500 text-white shadow-md' : 'text-stone-600 dark:text-stone-400 hover:bg-stone-100 dark:hover:bg-stone-800'"
                class="flex items-center gap-3 px-4 py-3 w-full text-left rounded-2xl text-xs sm:text-sm font-bold transition-all">
                <i data-lucide="user" class="w-4 h-4"></i>
                <span>Profile & Identity</span>
            </button>

            <button @click="activeTab = 'security'"
                :class="activeTab === 'security' ? 'bg-amber-500 text-white shadow-md' : 'text-stone-600 dark:text-stone-400 hover:bg-stone-100 dark:hover:bg-stone-800'"
                class="flex items-center gap-3 px-4 py-3 w-full text-left rounded-2xl text-xs sm:text-sm font-bold transition-all">
                <i data-lucide="key" class="w-4 h-4"></i>
                <span>Security & Password</span>
            </button>

            <button @click="activeTab = 'privacy'"
                :class="activeTab === 'privacy' ? 'bg-amber-500 text-white shadow-md' : 'text-stone-600 dark:text-stone-400 hover:bg-stone-100 dark:hover:bg-stone-800'"
                class="flex items-center gap-3 px-4 py-3 w-full text-left rounded-2xl text-xs sm:text-sm font-bold transition-all">
                <i data-lucide="shield" class="w-4 h-4"></i>
                <span>Privacy & Sovereignty</span>
            </button>

            <button @click="activeTab = 'notifications'"
                :class="activeTab === 'notifications' ? 'bg-amber-500 text-white shadow-md' : 'text-stone-600 dark:text-stone-400 hover:bg-stone-100 dark:hover:bg-stone-800'"
                class="flex items-center gap-3 px-4 py-3 w-full text-left rounded-2xl text-xs sm:text-sm font-bold transition-all">
                <i data-lucide="bell" class="w-4 h-4"></i>
                <span>Notifications</span>
            </button>

            <button @click="activeTab = 'appearance'"
                :class="activeTab === 'appearance' ? 'bg-amber-500 text-white shadow-md' : 'text-stone-600 dark:text-stone-400 hover:bg-stone-100 dark:hover:bg-stone-800'"
                class="flex items-center gap-3 px-4 py-3 w-full text-left rounded-2xl text-xs sm:text-sm font-bold transition-all">
                <i data-lucide="palette" class="w-4 h-4"></i>
                <span>Appearance</span>
            </button>

            <button @click="activeTab = 'data'"
                :class="activeTab === 'data' ? 'bg-amber-500 text-white shadow-md' : 'text-stone-600 dark:text-stone-400 hover:bg-stone-100 dark:hover:bg-stone-800'"
                class="flex items-center gap-3 px-4 py-3 w-full text-left rounded-2xl text-xs sm:text-sm font-bold transition-all">
                <i data-lucide="database" class="w-4 h-4"></i>
                <span>Data & Account</span>
            </button>

        </aside>

        <!-- MAIN FORM AREA -->
        <main class="md:col-span-8 bg-white dark:bg-stone-900 border border-stone-200/80 dark:border-stone-800 rounded-2xl sm:rounded-3xl p-4 sm:p-8 shadow-sm">
            
            <form action="{{ route('settings.update', $user->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PATCH')

                <!-- TAB 1: PROFILE & IDENTITY -->
                <div x-show="activeTab === 'profile'" class="space-y-5 sm:space-y-6">
                    <div class="border-b border-stone-100 dark:border-stone-800 pb-3 sm:pb-4">
                        <h2 class="text-base sm:text-xl font-black text-stone-900 dark:text-white">Profile Details</h2>
                        <p class="text-xs text-stone-500">Your public identity on Kulturegram.</p>
                    </div>

                    {{-- Avatar & Banner Upload Controls --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4 pb-4 border-b border-stone-100 dark:border-stone-800">
                        {{-- Avatar --}}
                        <div class="flex items-center gap-3 sm:gap-4 p-3.5 sm:p-4 rounded-2xl bg-stone-50 dark:bg-stone-800/60 border border-stone-200/60 dark:border-stone-700/60">
                            <div class="relative w-14 h-14 sm:w-16 sm:h-16 rounded-2xl overflow-hidden bg-stone-200 dark:bg-stone-700 shrink-0 border-2 border-white dark:border-stone-800 shadow">
                                <img id="settings-avatar-img" 
                                     src="{{ $user->profile_photo_url }}" 
                                     :src="avatarPreview || '{{ $user->profile_photo_url }}'" 
                                     alt="{{ $user->name }}"
                                     class="w-full h-full object-cover">
                            </div>
                            <div class="flex-1 min-w-0">
                                <h4 class="text-xs font-bold text-stone-900 dark:text-white uppercase tracking-wider mb-1">Avatar</h4>
                                <button type="button" @click="triggerAvatarSelect()"
                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-amber-500 hover:bg-amber-600 active:scale-95 text-white rounded-xl text-xs font-bold transition shadow-sm cursor-pointer">
                                    <i data-lucide="camera" class="w-3.5 h-3.5"></i> Change Photo
                                </button>
                                <input type="file" id="settings-avatar-input" name="avatar" class="hidden" accept="image/*"
                                    @change="handleAvatarSelected($event)">
                            </div>
                        </div>

                        {{-- Cover Banner --}}
                        <div class="flex items-center gap-3 sm:gap-4 p-3.5 sm:p-4 rounded-2xl bg-stone-50 dark:bg-stone-800/60 border border-stone-200/60 dark:border-stone-700/60">
                            <div class="relative w-14 h-14 sm:w-16 sm:h-16 rounded-2xl overflow-hidden bg-orange-500 shrink-0 border-2 border-white dark:border-stone-800 shadow">
                                @if($user->cover_photo_url)
                                    <img id="settings-cover-img" 
                                         src="{{ $user->cover_photo_url }}" 
                                         :src="coverPreview || '{{ $user->cover_photo_url }}'" 
                                         alt="Cover Banner"
                                         class="w-full h-full object-cover">
                                @else
                                    <template x-if="coverPreview">
                                        <img id="settings-cover-img" 
                                             :src="coverPreview" 
                                             alt="Cover Banner"
                                             class="w-full h-full object-cover">
                                    </template>
                                    <div x-show="!coverPreview" class="w-full h-full flex items-center justify-center text-white/70">
                                        <i data-lucide="image" class="w-6 h-6"></i>
                                    </div>
                                @endif
                            </div>
                            <div class="flex-1 min-w-0">
                                <h4 class="text-xs font-bold text-stone-900 dark:text-white uppercase tracking-wider mb-1">Cover Banner</h4>
                                <button type="button" @click="triggerCoverSelect()"
                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-stone-800 hover:bg-stone-900 active:scale-95 text-white dark:bg-stone-700 dark:hover:bg-stone-600 rounded-xl text-xs font-bold transition shadow-sm cursor-pointer">
                                    <i data-lucide="image" class="w-3.5 h-3.5"></i> Change Banner
                                </button>
                                <input type="file" id="settings-cover-input" name="cover_photo" class="hidden" accept="image/*"
                                    @change="handleCoverSelected($event)">
                            </div>
                        </div>
                    </div>

                    {{-- Name & Username --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4">
                        <div>
                            <label class="block text-xs font-bold text-stone-700 dark:text-stone-300 uppercase tracking-wider mb-1.5">Full Name *</label>
                            <input type="text" name="name" value="{{ old('name', $user->name) }}" required
                                class="w-full px-4 py-2.5 bg-stone-50 dark:bg-stone-800 border border-stone-200 dark:border-stone-700 rounded-xl text-sm font-semibold text-stone-900 dark:text-white focus:ring-2 focus:ring-amber-500 focus:outline-none">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-stone-700 dark:text-stone-300 uppercase tracking-wider mb-1.5">Username *</label>
                            <input type="text" name="username" value="{{ old('username', $user->username) }}" required
                                class="w-full px-4 py-2.5 bg-stone-50 dark:bg-stone-800 border border-stone-200 dark:border-stone-700 rounded-xl text-sm font-semibold text-stone-900 dark:text-white focus:ring-2 focus:ring-amber-500 focus:outline-none">
                        </div>
                    </div>

                    {{-- Email & Website --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4">
                        <div>
                            <label class="block text-xs font-bold text-stone-700 dark:text-stone-300 uppercase tracking-wider mb-1.5">Email Address *</label>
                            <input type="email" name="email" value="{{ old('email', $user->email) }}" required
                                class="w-full px-4 py-2.5 bg-stone-50 dark:bg-stone-800 border border-stone-200 dark:border-stone-700 rounded-xl text-sm font-semibold text-stone-900 dark:text-white focus:ring-2 focus:ring-amber-500 focus:outline-none">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-stone-700 dark:text-stone-300 uppercase tracking-wider mb-1.5">Personal Website</label>
                            <input type="url" name="website" value="{{ old('website', $user->website) }}" placeholder="https://example.com"
                                class="w-full px-4 py-2.5 bg-stone-50 dark:bg-stone-800 border border-stone-200 dark:border-stone-700 rounded-xl text-sm text-stone-900 dark:text-white focus:ring-2 focus:ring-amber-500 focus:outline-none">
                        </div>
                    </div>

                    {{-- Location & Cultural Roots --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4">
                        <div>
                            <label class="block text-xs font-bold text-stone-700 dark:text-stone-300 uppercase tracking-wider mb-1.5">Current Location</label>
                            <input type="text" name="location" value="{{ old('location', $user->location) }}" placeholder="e.g., Lagos, Nigeria"
                                class="w-full px-4 py-2.5 bg-stone-50 dark:bg-stone-800 border border-stone-200 dark:border-stone-700 rounded-xl text-sm text-stone-900 dark:text-white focus:ring-2 focus:ring-amber-500 focus:outline-none">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-stone-700 dark:text-stone-300 uppercase tracking-wider mb-1.5">Cultural Origin / Heritage</label>
                            <input type="text" name="cultural_background" value="{{ old('cultural_background', $user->cultural_background) }}" placeholder="e.g., Yoruba • Edo • Berom"
                                class="w-full px-4 py-2.5 bg-stone-50 dark:bg-stone-800 border border-stone-200 dark:border-stone-700 rounded-xl text-sm text-stone-900 dark:text-white focus:ring-2 focus:ring-amber-500 focus:outline-none">
                        </div>
                    </div>

                    {{-- Bio --}}
                    <div>
                        <label class="block text-xs font-bold text-stone-700 dark:text-stone-300 uppercase tracking-wider mb-1.5">Bio</label>
                        <textarea name="bio" rows="3" placeholder="Share your cultural perspective and heritage journey..."
                            class="w-full px-4 py-2.5 bg-stone-50 dark:bg-stone-800 border border-stone-200 dark:border-stone-700 rounded-xl text-sm text-stone-900 dark:text-white focus:ring-2 focus:ring-amber-500 focus:outline-none leading-relaxed">{{ old('bio', $user->bio) }}</textarea>
                    </div>

                    <div class="pt-4 flex justify-end">
                        <button type="submit" class="w-full sm:w-auto px-6 py-2.5 bg-amber-500 hover:bg-amber-600 text-white font-bold text-sm rounded-xl shadow-md transition active:scale-95">
                            Save Profile
                        </button>
                    </div>
                </div>

                <!-- TAB 2: SECURITY & PASSWORD -->
                <div x-show="activeTab === 'security'" class="space-y-5 sm:space-y-6">
                    <div class="border-b border-stone-100 dark:border-stone-800 pb-3 sm:pb-4">
                        <h2 class="text-base sm:text-xl font-black text-stone-900 dark:text-white">Security & Password</h2>
                        <p class="text-xs text-stone-500">Ensure your account and heritage vault remain protected.</p>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-stone-700 dark:text-stone-300 uppercase tracking-wider mb-1.5">Current Password</label>
                        <input type="password" name="current_password" placeholder="Enter current password to change"
                            class="w-full px-4 py-2.5 bg-stone-50 dark:bg-stone-800 border border-stone-200 dark:border-stone-700 rounded-xl text-sm text-stone-900 dark:text-white focus:ring-2 focus:ring-amber-500 focus:outline-none">
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4">
                        <div>
                            <label class="block text-xs font-bold text-stone-700 dark:text-stone-300 uppercase tracking-wider mb-1.5">New Password</label>
                            <input type="password" name="password" placeholder="At least 8 characters"
                                class="w-full px-4 py-2.5 bg-stone-50 dark:bg-stone-800 border border-stone-200 dark:border-stone-700 rounded-xl text-sm text-stone-900 dark:text-white focus:ring-2 focus:ring-amber-500 focus:outline-none">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-stone-700 dark:text-stone-300 uppercase tracking-wider mb-1.5">Confirm New Password</label>
                            <input type="password" name="password_confirmation" placeholder="Repeat new password"
                                class="w-full px-4 py-2.5 bg-stone-50 dark:bg-stone-800 border border-stone-200 dark:border-stone-700 rounded-xl text-sm text-stone-900 dark:text-white focus:ring-2 focus:ring-amber-500 focus:outline-none">
                        </div>
                    </div>

                    <div class="pt-4 flex justify-end">
                        <button type="submit" class="w-full sm:w-auto px-6 py-2.5 bg-amber-500 hover:bg-amber-600 text-white font-bold text-sm rounded-xl shadow-md transition active:scale-95">
                            Update Password
                        </button>
                    </div>
                </div>

                <!-- TAB 3: PRIVACY & SOVEREIGNTY -->
                <div x-show="activeTab === 'privacy'" class="space-y-5 sm:space-y-6">
                    <div class="border-b border-stone-100 dark:border-stone-800 pb-3 sm:pb-4">
                        <h2 class="text-base sm:text-xl font-black text-stone-900 dark:text-white">Privacy & Cultural Sovereignty</h2>
                        <p class="text-xs text-stone-500">Configure visibility and communication permissions.</p>
                    </div>

                    <div class="space-y-3 sm:space-y-4">
                        <div class="p-3.5 sm:p-4 rounded-2xl bg-stone-50 dark:bg-stone-800/60 border border-stone-200/60 dark:border-stone-700/60 space-y-3">
                            <label class="flex items-center justify-between cursor-pointer">
                                <div class="pr-3">
                                    <p class="font-bold text-stone-900 dark:text-white text-xs sm:text-sm">Lock-In Requirement for Direct Messages</p>
                                    <p class="text-[11px] sm:text-xs text-stone-500">Only storytellers you have locked-in with can send you messages.</p>
                                </div>
                                <input type="checkbox" name="preferences[direct_message_locked_only]" value="1"
                                    {{ isset($user->preferences['direct_message_locked_only']) && $user->preferences['direct_message_locked_only'] ? 'checked' : 'checked' }}
                                    class="w-5 h-5 rounded text-amber-500 focus:ring-amber-400 shrink-0">
                            </label>
                        </div>

                        <div class="p-3.5 sm:p-4 rounded-2xl bg-stone-50 dark:bg-stone-800/60 border border-stone-200/60 dark:border-stone-700/60 space-y-3">
                            <label class="flex items-center justify-between cursor-pointer">
                                <div class="pr-3">
                                    <p class="font-bold text-stone-900 dark:text-white text-xs sm:text-sm">Public Profile Visibility</p>
                                    <p class="text-[11px] sm:text-xs text-stone-500">Allow cultural enthusiasts worldwide to discover your profile and contributions.</p>
                                </div>
                                <input type="checkbox" name="preferences[public_profile]" value="1"
                                    {{ !isset($user->preferences['public_profile']) || $user->preferences['public_profile'] ? 'checked' : '' }}
                                    class="w-5 h-5 rounded text-amber-500 focus:ring-amber-400 shrink-0">
                            </label>
                        </div>

                        {{-- Transparency Notice & Link --}}
                        <div class="p-4 sm:p-5 rounded-2xl bg-amber-500/10 border border-amber-500/20 text-stone-800 dark:text-stone-200 space-y-2">
                            <div class="flex items-center gap-2 font-bold text-amber-600 dark:text-amber-400 text-xs sm:text-sm">
                                <i data-lucide="shield-check" class="w-4 h-4 sm:w-5 sm:h-5"></i>
                                <span>Indigenous Data Sovereignty</span>
                            </div>
                            <p class="text-xs leading-relaxed text-stone-600 dark:text-stone-300">
                                Kulturegram commits to protecting your ancestral heritage. We never sell or license your content to third parties. Read our complete policies:
                            </p>
                            <div class="flex gap-4 pt-1">
                                <a href="{{ route('privacy') }}" class="text-xs font-bold text-amber-600 dark:text-amber-400 underline">Privacy Policy</a>
                                <a href="{{ route('terms') }}" class="text-xs font-bold text-amber-600 dark:text-amber-400 underline">Terms of Service</a>
                            </div>
                        </div>
                    </div>

                    <div class="pt-4 flex justify-end">
                        <button type="submit" class="w-full sm:w-auto px-6 py-2.5 bg-amber-500 hover:bg-amber-600 text-white font-bold text-sm rounded-xl shadow-md transition active:scale-95">
                            Save Privacy Preferences
                        </button>
                    </div>
                </div>

                <!-- TAB 4: NOTIFICATIONS -->
                <div x-show="activeTab === 'notifications'" class="space-y-5 sm:space-y-6">
                    <div class="border-b border-stone-100 dark:border-stone-800 pb-3 sm:pb-4">
                        <h2 class="text-base sm:text-xl font-black text-stone-900 dark:text-white">Notification Preferences</h2>
                        <p class="text-xs text-stone-500">Choose which updates and alerts you wish to receive.</p>
                    </div>

                    <div class="space-y-3">
                        <label class="flex items-center justify-between p-3.5 sm:p-4 rounded-2xl bg-stone-50 dark:bg-stone-800/60 border border-stone-200/60 dark:border-stone-700/60 cursor-pointer">
                            <div class="pr-3">
                                <p class="font-bold text-stone-900 dark:text-white text-xs sm:text-sm">Cultural Story Reviews</p>
                                <p class="text-[11px] sm:text-xs text-stone-500">Alerts when your traditions are reviewed, approved, or featured by Guardians.</p>
                            </div>
                            <input type="checkbox" name="preferences[notify_culture_approval]" value="1"
                                {{ !isset($user->preferences['notify_culture_approval']) || $user->preferences['notify_culture_approval'] ? 'checked' : '' }}
                                class="w-5 h-5 rounded text-amber-500 focus:ring-amber-400 shrink-0">
                        </label>

                        <label class="flex items-center justify-between p-3.5 sm:p-4 rounded-2xl bg-stone-50 dark:bg-stone-800/60 border border-stone-200/60 dark:border-stone-700/60 cursor-pointer">
                            <div class="pr-3">
                                <p class="font-bold text-stone-900 dark:text-white text-xs sm:text-sm">Gathering & Workshop Reminders</p>
                                <p class="text-[11px] sm:text-xs text-stone-500">Reminders for events you have registered for or are hosting.</p>
                            </div>
                            <input type="checkbox" name="preferences[notify_events]" value="1"
                                {{ !isset($user->preferences['notify_events']) || $user->preferences['notify_events'] ? 'checked' : '' }}
                                class="w-5 h-5 rounded text-amber-500 focus:ring-amber-400 shrink-0">
                        </label>

                        <label class="flex items-center justify-between p-3.5 sm:p-4 rounded-2xl bg-stone-50 dark:bg-stone-800/60 border border-stone-200/60 dark:border-stone-700/60 cursor-pointer">
                            <div class="pr-3">
                                <p class="font-bold text-stone-900 dark:text-white text-xs sm:text-sm">Tribal Community Posts</p>
                                <p class="text-[11px] sm:text-xs text-stone-500">Notifications when discussions occur in your joined communities.</p>
                            </div>
                            <input type="checkbox" name="preferences[notify_community]" value="1"
                                {{ !isset($user->preferences['notify_community']) || $user->preferences['notify_community'] ? 'checked' : '' }}
                                class="w-5 h-5 rounded text-amber-500 focus:ring-amber-400 shrink-0">
                        </label>
                    </div>

                    <div class="pt-4 flex justify-end">
                        <button type="submit" class="w-full sm:w-auto px-6 py-2.5 bg-amber-500 hover:bg-amber-600 text-white font-bold text-sm rounded-xl shadow-md transition active:scale-95">
                            Save Notifications
                        </button>
                    </div>
                </div>

                <!-- TAB 5: APPEARANCE -->
                <div x-show="activeTab === 'appearance'" class="space-y-5 sm:space-y-6">
                    <div class="border-b border-stone-100 dark:border-stone-800 pb-3 sm:pb-4">
                        <h2 class="text-base sm:text-xl font-black text-stone-900 dark:text-white">Appearance & Theme</h2>
                        <p class="text-xs text-stone-500">Customize the visual presentation of Kulturegram.</p>
                    </div>

                    <div class="p-4 sm:p-5 rounded-2xl bg-stone-50 dark:bg-stone-800/60 border border-stone-200/60 dark:border-stone-700/60 flex items-center justify-between">
                        <div>
                            <h3 class="font-bold text-stone-900 dark:text-white text-xs sm:text-base">Dark Mode Interface</h3>
                            <p class="text-[11px] sm:text-xs text-stone-500 mt-0.5">Toggle between ambient dark mode and daylight mode.</p>
                        </div>

                        <button type="button" id="theme-toggle-settings"
                            class="relative inline-flex items-center h-7 rounded-full w-14 transition-colors focus:outline-none focus:ring-2 focus:ring-amber-500 bg-stone-200 dark:bg-amber-500 shrink-0">
                            <span id="theme-toggle-slider-settings"
                                class="inline-block w-5 h-5 transform bg-white rounded-full transition-transform translate-x-1 dark:translate-x-8 shadow-sm"></span>
                        </button>
                    </div>
                </div>

                    <!-- TAB 6: DATA & ACCOUNT -->
                <div x-show="activeTab === 'data'" class="space-y-5 sm:space-y-6">
                    <div class="border-b border-stone-100 dark:border-stone-800 pb-3 sm:pb-4">
                        <h2 class="text-base sm:text-xl font-black text-stone-900 dark:text-white">Data & Account Deletion</h2>
                        <p class="text-xs text-stone-500">Export your archive or permanently close your account.</p>
                    </div>

                    <div class="p-4 sm:p-5 rounded-2xl bg-stone-50 dark:bg-stone-800/60 border border-stone-200/60 dark:border-stone-700/60 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 sm:gap-4">
                        <div>
                            <h4 class="font-bold text-stone-900 dark:text-white text-xs sm:text-sm">Download Legacy Archive</h4>
                            <p class="text-[11px] sm:text-xs text-stone-500 mt-0.5">Your photos, vault items, and stories are available anytime.</p>
                        </div>
                        <a href="{{ route('vault.index') }}" class="px-4 py-2 bg-stone-200 dark:bg-stone-700 hover:bg-stone-300 text-stone-800 dark:text-white rounded-xl text-xs font-bold transition w-fit">
                            Access Vault Archive
                        </a>
                    </div>

                    <!-- Active Session & Sign Out -->
                    <div class="p-4 sm:p-5 rounded-2xl bg-stone-50 dark:bg-stone-800/60 border border-stone-200/60 dark:border-stone-700/60 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 sm:gap-4">
                        <div>
                            <h4 class="font-bold text-stone-800 dark:text-stone-200 text-xs sm:text-sm flex items-center gap-1.5">
                                <i data-lucide="log-out" class="w-4 h-4 text-stone-500"></i>
                                <span>Active Device Session</span>
                            </h4>
                            <p class="text-[11px] sm:text-xs text-stone-500 mt-0.5">Terminate and sign out of your active authenticated session on this device.</p>
                        </div>
                        <button type="button" @click.stop="openSignOutModal()" class="px-4 py-2 bg-stone-200 hover:bg-stone-300 dark:bg-stone-700 dark:hover:bg-stone-600 text-stone-800 dark:text-white rounded-xl text-xs font-bold transition shadow-sm w-fit">
                            Sign Out
                        </button>
                    </div>

                    <div class="p-4 sm:p-5 rounded-2xl bg-red-50/60 dark:bg-red-950/20 border border-red-200 dark:border-red-900/40 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 sm:gap-4">
                        <div>
                            <h4 class="font-bold text-red-700 dark:text-red-400 text-xs sm:text-sm">Delete Account</h4>
                            <p class="text-[11px] sm:text-xs text-red-600/80 dark:text-red-400/80 mt-0.5">Permanently erase your account, private vault, and personal memories.</p>
                        </div>
                        <button type="button" @click.stop="openDeleteModal()" class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-xl text-xs font-bold transition shadow-sm w-fit">
                            Delete Account
                        </button>
                    </div>
                </div>

            </form>
        </main>

    </div>

    <!-- AVATAR ADJUSTMENT & PREVIEW MODAL IN SETTINGS -->
    <div x-show="showAvatarModal" x-cloak 
        class="fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-4 bg-black/75 backdrop-blur-md"
        @keydown.escape.window="closeAvatarModal()">
        <div class="bg-white dark:bg-stone-900 rounded-3xl max-w-md w-full p-5 sm:p-6 border border-stone-200 dark:border-stone-800 shadow-2xl space-y-4 relative"
            @click.away="closeAvatarModal()">
            
            <div class="flex items-center justify-between pb-3 border-b border-stone-100 dark:border-stone-800">
                <div>
                    <h3 class="text-base sm:text-lg font-black text-stone-900 dark:text-white flex items-center gap-2">
                        <i data-lucide="camera" class="w-5 h-5 text-amber-500"></i>
                        Adjust Avatar
                    </h3>
                    <p class="text-[11px] sm:text-xs text-stone-500">Scale, rotate, and preview your profile picture.</p>
                </div>
                <button type="button" @click="closeAvatarModal()" class="w-8 h-8 rounded-full bg-stone-100 dark:bg-stone-800 flex items-center justify-center text-stone-400 hover:text-stone-600 dark:hover:text-stone-200 transition">
                    <i data-lucide="x" class="w-4 h-4"></i>
                </button>
            </div>

            <div class="relative w-52 h-52 sm:w-64 sm:h-64 mx-auto rounded-3xl overflow-hidden bg-stone-950 flex items-center justify-center border-2 border-stone-800 shadow-inner group select-none">
                <img :src="avatarModalImg" 
                    class="max-w-none origin-center pointer-events-none select-none transition-transform duration-75"
                    :style="'transform: scale(' + avatarScale + ') rotate(' + avatarRotation + 'deg); width: 100%; height: 100%; object-fit: cover;'">

                <div class="absolute inset-0 rounded-full border-[34px] sm:border-[44px] border-black/60 pointer-events-none shadow-[inset_0_0_0_2px_rgba(245,158,11,0.6)]"></div>
                
                <span class="absolute bottom-2 text-[10px] font-bold text-white/70 bg-black/50 px-2.5 py-0.5 rounded-full pointer-events-none">
                    Preview Crop
                </span>
            </div>

            <div class="space-y-2.5 bg-stone-50 dark:bg-stone-800/60 p-3.5 rounded-2xl border border-stone-200/60 dark:border-stone-700/60">
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

            <div class="flex items-center justify-between text-[11px] text-stone-400 px-1">
                <span class="truncate max-w-[200px]" x-text="avatarFilename"></span>
                <span x-text="avatarFilesize"></span>
            </div>

            <div class="flex items-center justify-end gap-2 pt-1">
                <button type="button" @click="closeAvatarModal()"
                    class="px-4 py-2.5 rounded-xl text-xs sm:text-sm font-bold text-stone-600 dark:text-stone-400 hover:bg-stone-100 dark:hover:bg-stone-800 transition">
                    Cancel
                </button>
                <button type="button" @click="saveAvatarDirect()" :disabled="isSavingAvatar"
                    class="px-4 sm:px-5 py-2.5 rounded-xl bg-amber-500 hover:bg-amber-600 active:scale-95 text-white text-xs sm:text-sm font-bold transition shadow-lg flex items-center gap-2 disabled:opacity-50">
                    <span x-show="!isSavingAvatar" class="flex items-center gap-1.5">
                        <i data-lucide="check" class="w-4 h-4"></i>
                        Apply Avatar
                    </span>
                    <span x-show="isSavingAvatar" class="flex items-center gap-1.5">
                        <i data-lucide="loader" class="w-4 h-4 animate-spin"></i>
                        Saving...
                    </span>
                </button>
            </div>

        </div>
    </div>

    <!-- COVER BANNER PREVIEW MODAL IN SETTINGS -->
    <div x-show="showCoverModal" x-cloak 
        class="fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-4 bg-black/75 backdrop-blur-md"
        @keydown.escape.window="closeCoverModal()">
        <div class="bg-white dark:bg-stone-900 rounded-3xl max-w-xl w-full p-5 sm:p-6 border border-stone-200 dark:border-stone-800 shadow-2xl space-y-4 relative"
            @click.away="closeCoverModal()">
            
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

            <div class="relative w-full aspect-[16/7] rounded-2xl overflow-hidden bg-stone-950 flex items-center justify-center border-2 border-stone-800 shadow-inner group select-none">
                <img :src="coverModalImg" 
                    class="max-w-none origin-center pointer-events-none select-none transition-transform duration-75"
                    :style="'transform: scale(' + coverScale + ') translateY(' + coverOffsetY + 'px); width: 100%; height: 100%; object-fit: cover;'">

                <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-transparent to-black/20 pointer-events-none"></div>

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

            <div class="flex items-center justify-between text-[11px] text-stone-400 px-1">
                <span class="truncate max-w-[200px]" x-text="coverFilename"></span>
                <span x-text="coverFilesize"></span>
            </div>

            <div class="flex items-center justify-end gap-2 pt-1">
                <button type="button" @click="closeCoverModal()"
                    class="px-4 py-2.5 rounded-xl text-xs sm:text-sm font-bold text-stone-600 dark:text-stone-400 hover:bg-stone-100 dark:hover:bg-stone-800 transition">
                    Cancel
                </button>
                <button type="button" @click="saveCoverDirect()" :disabled="isSavingCover"
                    class="px-4 sm:px-5 py-2.5 rounded-xl bg-amber-500 hover:bg-amber-600 active:scale-95 text-white text-xs sm:text-sm font-bold transition shadow-lg flex items-center gap-2 disabled:opacity-50">
                    <span x-show="!isSavingCover" class="flex items-center gap-1.5">
                        <i data-lucide="check" class="w-4 h-4"></i>
                        Apply Banner
                    </span>
                    <span x-show="isSavingCover" class="flex items-center gap-1.5">
                        <i data-lucide="loader" class="w-4 h-4 animate-spin"></i>
                        Saving...
                    </span>
                </button>
            </div>

        </div>
    </div>

    <!-- SIGN OUT CONFIRMATION MODAL -->
    <div x-show="showSignOutModal" x-cloak
        class="fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-4 bg-black/60 backdrop-blur-sm"
        @click.self="showSignOutModal = false"
        @keydown.escape.window="showSignOutModal = false">
        <div class="bg-white dark:bg-stone-900 rounded-3xl max-w-md w-full p-5 sm:p-8 border border-stone-200 dark:border-stone-800 shadow-2xl space-y-4">
            <div class="w-12 h-12 rounded-2xl bg-amber-100 dark:bg-amber-950/50 text-amber-600 dark:text-amber-400 flex items-center justify-center">
                <i data-lucide="log-out" class="w-6 h-6"></i>
            </div>
            <div>
                <h3 class="text-base sm:text-lg font-black text-stone-900 dark:text-white">Confirm Sign Out</h3>
                <p class="text-xs text-stone-500 mt-1 leading-relaxed">
                    Are you sure you want to sign out of your active session on this device? You can sign back in anytime.
                </p>
            </div>

            <form action="{{ route('logout') }}" method="POST" class="flex justify-end gap-2 pt-2">
                @csrf
                <button type="button" @click="showSignOutModal = false" class="px-4 py-2 text-xs sm:text-sm font-bold text-stone-500 hover:text-stone-700 dark:hover:text-stone-300 rounded-xl hover:bg-stone-100 dark:hover:bg-stone-800 transition">
                    Cancel
                </button>
                <button type="submit" class="px-4 sm:px-5 py-2 bg-amber-500 hover:bg-amber-600 text-white text-xs sm:text-sm font-bold rounded-xl shadow-md transition">
                    Confirm Sign Out
                </button>
            </form>
        </div>
    </div>

    <!-- DELETE ACCOUNT MODAL -->
    <div x-show="showDeleteModal" x-cloak
        class="fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-4 bg-black/60 backdrop-blur-sm"
        @click.self="showDeleteModal = false"
        @keydown.escape.window="showDeleteModal = false">
        <div class="bg-white dark:bg-stone-900 rounded-3xl max-w-md w-full p-5 sm:p-8 border border-stone-200 dark:border-stone-800 shadow-2xl space-y-4">
            <div class="w-12 h-12 rounded-2xl bg-red-100 dark:bg-red-950/50 text-red-600 flex items-center justify-center">
                <i data-lucide="alert-triangle" class="w-6 h-6"></i>
            </div>
            <div>
                <h3 class="text-base sm:text-lg font-black text-stone-900 dark:text-white">Confirm Account Deletion</h3>
                <p class="text-xs text-stone-500 mt-1 leading-relaxed">
                    Are you sure you want to permanently delete your account? This action cannot be undone and all stories, personal vault files, and connections will be permanently wiped.
                </p>
            </div>

            <form action="{{ route('profile.destroy') }}" method="POST" class="space-y-4 pt-2">
                @csrf
                @method('DELETE')
                <div>
                    <label class="block text-xs font-bold text-stone-700 dark:text-stone-300 uppercase tracking-wider mb-1">Confirm with Password</label>
                    <input type="password" name="password" required placeholder="Enter your current password to confirm"
                        class="w-full px-4 py-2.5 bg-stone-50 dark:bg-stone-800 border border-stone-200 dark:border-stone-700 rounded-xl text-sm text-stone-900 dark:text-white focus:ring-2 focus:ring-red-500 focus:outline-none">
                    @error('password')
                        <p class="text-xs text-red-600 dark:text-red-400 font-semibold mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" @click="showDeleteModal = false" class="px-4 py-2 text-xs sm:text-sm font-bold text-stone-500 hover:text-stone-700 dark:hover:text-stone-300 rounded-xl hover:bg-stone-100 dark:hover:bg-stone-800 transition">Cancel</button>
                    <button type="submit" class="px-4 sm:px-5 py-2 bg-red-600 hover:bg-red-700 text-white text-xs sm:text-sm font-bold rounded-xl shadow-md transition">Confirm & Delete Account</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener("DOMContentLoaded", () => {
        if (typeof lucide !== 'undefined') {
            lucide.createIcons();
        }

        const themeToggle = document.getElementById('theme-toggle-settings');
        if (themeToggle) {
            themeToggle.addEventListener('click', () => {
                const isDark = document.documentElement.classList.toggle('dark');
                localStorage.setItem('theme', isDark ? 'dark' : 'light');
            });
        }
    });
</script>
@endpush