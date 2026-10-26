@extends('layouts.app')

@section('title', 'Register - Kulturegram')

@php
    $originOptions ??= app(\App\Services\UserOriginService::class)->options();

    $accountHasErrors = $errors->hasAny(['name', 'username', 'email', 'password']);
    $originHasErrors = collect(['country_id', 'state_id', 'local_government_id', 'local_area', 'language_ids', 'language_ids.*', 'new_languages', 'new_languages.*'])
        ->contains(fn ($field) => $errors->has($field));

    $initialState = [
        'step' => (!$accountHasErrors && $originHasErrors) ? 2 : 1,
        'countryId' => old('country_id'),
        'stateId' => old('state_id'),
        'lgaId' => old('local_government_id'),
        'localArea' => old('local_area', ''),
        'languageIds' => array_map('intval', (array) old('language_ids', [])),
        'newLanguages' => array_values((array) old('new_languages', [])),
    ];
@endphp

@section('content')
    <div class="max-w-md mx-auto mt-10 mb-10 bg-white dark:bg-stone-900 shadow-lg rounded-2xl p-6 border dark:border-stone-800"
        x-data="registerForm(@js($initialState))">

        <h2 class="text-2xl font-bold mb-2 text-center text-amber-500">
            <span x-show="step === 1">Register</span>
            <span x-show="step === 2" x-cloak>Your roots</span>
        </h2>

        <!-- Progress bar -->
        <div class="mb-6">
            <div class="flex gap-2" aria-hidden="true">
                <div class="h-1.5 flex-1 rounded-full bg-amber-500"></div>
                <div class="h-1.5 flex-1 rounded-full transition-colors duration-300"
                    :class="step === 2 ? 'bg-amber-500' : 'bg-stone-200 dark:bg-stone-700'"></div>
            </div>
            <p class="mt-2 text-xs text-center text-stone-500 dark:text-stone-400">
                Step <span x-text="step">1</span> of 2 &middot;
                <span x-text="step === 1 ? 'Account details' : 'Where you\'re from (optional)'">Account details</span>
            </p>
        </div>

        <form method="POST" action="{{ route('register') }}" x-ref="form" @submit="onSubmit($event)" enctype="multipart/form-data" novalidate>
            @csrf

            {{-- ============ STEP 1: Account ============ --}}
            <div x-ref="step1" x-show="step === 1">
                <!-- Profile Picture (Optional) -->
                <div class="mb-5 flex flex-col items-center text-center" x-data="{ avatarPreview: null }">
                    <div class="relative group">
                        <div class="w-20 h-20 rounded-full overflow-hidden border-2 border-stone-200 dark:border-stone-700 bg-stone-50 dark:bg-stone-800 flex items-center justify-center shadow-inner">
                            <template x-if="avatarPreview">
                                <img :src="avatarPreview" alt="Avatar preview" class="w-full h-full object-cover">
                            </template>
                            <template x-if="!avatarPreview">
                                <div class="flex flex-col items-center justify-center text-stone-400">
                                    <i data-lucide="user" class="w-8 h-8"></i>
                                </div>
                            </template>
                        </div>
                        <label for="avatar-input" class="absolute -bottom-1 -right-1 bg-amber-500 hover:bg-amber-600 text-white p-1.5 rounded-full shadow-md cursor-pointer transition-colors" title="Upload profile picture">
                            <i data-lucide="camera" class="w-3.5 h-3.5"></i>
                        </label>
                        <input type="file" id="avatar-input" name="avatar" accept="image/png,image/jpeg,image/jpg,image/webp" class="hidden"
                            @change="const file = $event.target.files[0]; if (file) { const reader = new FileReader(); reader.onload = (e) => { avatarPreview = e.target.result; }; reader.readAsDataURL(file); }">
                    </div>
                    <span class="text-xs text-stone-500 dark:text-stone-400 mt-2 font-medium">Add profile photo <span class="text-stone-400">(optional)</span></span>
                    @error('avatar')
                        <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <!-- Name -->
                <div class="mb-4 relative">
                    <label for="name" class="block text-sm font-medium text-stone-700 dark:text-stone-300 mb-1">Name</label>
                    <div class="mt-1 relative">
                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-stone-400">
                            <i data-lucide="circle-user-round" class="w-5 h-5"></i>
                        </span>
                        <input id="name" type="text" name="name" value="{{ old('name') }}" required autofocus
                            class="pl-10 py-3 block w-full stnd-input px-4 dark:bg-stone-800 dark:border-stone-700 dark:text-stone-200 dark:placeholder-stone-500"
                            placeholder="name">
                        @error('name')
                            <p class="text-sm text-red-500 mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Username -->
                <div class="mb-4 relative">
                    <label for="username"
                        class="block text-sm font-medium text-stone-700 dark:text-stone-300 mb-1">Username</label>
                    <div class="mt-1 relative">
                        <span class="mb-1 absolute inset-y-0 left-0 pl-3 flex items-center text-stone-400">
                            <i data-lucide="user" class="w-5 h-5"></i>
                        </span>
                        <input id="username" type="text" name="username" value="{{ old('username') }}" required
                            class="pl-10 py-3 block w-full stnd-input px-4 dark:bg-stone-800 dark:border-stone-700 dark:text-stone-200 dark:placeholder-stone-500"
                            placeholder="username">
                        @error('username')
                            <p class="text-sm text-red-500 mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Email -->
                <div class="mb-4 relative">
                    <label for="email" class="block text-sm font-medium text-stone-700 dark:text-stone-300 mb-1">Email</label>
                    <div class="mt-1 relative">
                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-stone-400">
                            <i data-lucide="mail" class="w-5 h-5"></i>
                        </span>
                        <input id="email" type="email" name="email" value="{{ old('email') }}" required
                            class="pl-10 py-3 block w-full stnd-input px-4 dark:bg-stone-800 dark:border-stone-700 dark:text-stone-200 dark:placeholder-stone-500"
                            placeholder="your@email.com">
                        @error('email')
                            <p class="text-sm text-red-500 mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Password -->
                <div class="mb-4 relative" x-data="{ show: false }">
                    <label for="password"
                        class="block text-sm font-medium text-stone-700 dark:text-stone-300 mb-1">Password</label>
                    <div class="mt-1 relative">
                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-stone-400">
                            <i data-lucide="lock" class="w-5 h-5"></i>
                        </span>
                        <input id="password" :type="show ? 'text' : 'password'" name="password" required
                            class="pl-10 py-3 block w-full stnd-input px-4 dark:bg-stone-800 dark:border-stone-700 dark:text-stone-200 dark:placeholder-stone-500"
                            placeholder="Your password">
                        <button type="button" class="absolute inset-y-0 right-3 text-sm text-gray-500" @click="show = !show"
                            tabindex="-1">
                            <span x-show="!show">👁</span>
                            <span x-show="show">🙈</span>
                        </button>
                        @error('password')
                            <p class="text-sm text-red-500 mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Confirm Password -->
                <div class="mb-6 relative" x-data="{ show: false }">
                    <label for="password_confirmation"
                        class="block text-sm font-medium text-stone-700 dark:text-stone-300 mb-1">Confirm Password</label>
                    <div class="mt-1 relative">
                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-stone-400">
                            <i data-lucide="lock" class="w-5 h-5"></i>
                        </span>
                        <input id="password_confirmation" :type="show ? 'text' : 'password'" name="password_confirmation"
                            required
                            class="pl-10 py-3 block w-full stnd-input px-4 dark:bg-stone-800 dark:border-stone-700 dark:text-stone-200 dark:placeholder-stone-500"
                            placeholder="confirmation password">
                        <button type="button" class="absolute inset-y-0 right-3 text-sm text-gray-500" @click="show = !show"
                            tabindex="-1">
                            <span x-show="!show">👁</span>
                            <span x-show="show">🙈</span>
                        </button>
                    </div>
                </div>

                <!-- Continue -->
                <button type="button" id="register-continue" @click="next()"
                    class="w-full bg-amber-500 hover:bg-amber-600 text-white font-semibold py-3 rounded-xl transition-colors duration-200 shadow-sm active:scale-[0.99] flex items-center justify-center gap-2 cursor-pointer">
                    Continue
                    <i data-lucide="arrow-right" class="w-4 h-4"></i>
                </button>

                <!-- Extra links -->
                <div class="mt-4 text-center">
                    <a href="{{ route('password.request') }}" class="text-sm text-stone-500 hover:text-amber-600">Forgot your
                        password?</a>
                </div>
                <div class="mt-2 text-center">
                    <p class="text-sm text-stone-600 dark:text-stone-400">
                        Already have an account?
                        <a href="{{ route('login') }}" class="font-medium text-amber-600 hover:underline">Login</a>
                    </p>
                </div>
            </div>

            {{-- ============ STEP 2: Origin & languages (optional) ============ --}}
            <div x-show="step === 2" x-cloak
                x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0 translate-x-4"
                x-transition:enter-end="opacity-100 translate-x-0">

                <p class="text-sm text-stone-600 dark:text-stone-400 mb-5 text-center">
                    Help us connect you with your people and culture. It takes a few seconds, and you can change it any time.
                </p>

                <!-- Country -->
                <div class="mb-4">
                    <label for="origin-country" class="block text-sm font-medium text-stone-700 dark:text-stone-300 mb-1">Country</label>
                    <x-search-select model="countryId" source="countries" input-id="origin-country"
                        placeholder="Search for your country" icon="globe" />
                    <p x-show="detected" x-cloak class="text-xs text-stone-500 dark:text-stone-400 mt-1">
                        📍 Suggested from your device. Change it if needed.
                    </p>
                    @error('country_id')
                        <p class="text-sm text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- State + LGA (countries with official data, e.g. Nigeria) -->
                <div x-show="hasStates" x-cloak x-transition.opacity>
                    <div class="mb-4">
                        <label for="origin-state" class="block text-sm font-medium text-stone-700 dark:text-stone-300 mb-1">State</label>
                        <x-search-select model="stateId" source="states" input-id="origin-state"
                            placeholder="Search for your state" icon="map" />
                        @error('state_id')
                            <p class="text-sm text-red-500 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label for="origin-lga" class="block text-sm font-medium text-stone-700 dark:text-stone-300 mb-1">
                            Local Government Area
                        </label>
                        <x-search-select model="lgaId" source="lgas" input-id="origin-lga"
                            placeholder="Choose a state first" icon="map-pin" disabled="!stateId" />
                        @error('local_government_id')
                            <p class="text-sm text-red-500 mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Free-text local area (countries without official data) -->
                <div x-show="countryId && !hasStates" x-cloak x-transition.opacity class="mb-4 relative"
                    @click.outside="areaOpen = false">
                    <label for="origin-area" class="block text-sm font-medium text-stone-700 dark:text-stone-300 mb-1">
                        City / region / local area
                    </label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-stone-400 pointer-events-none">
                            <i data-lucide="map-pin" class="w-5 h-5"></i>
                        </span>
                        <input id="origin-area" type="text" x-model="localArea" maxlength="120" autocomplete="off"
                            @focus="areaOpen = true; fetchAreas()" @input.debounce.300ms="areaOpen = true; fetchAreas()"
                            @keydown.escape="areaOpen = false" @keydown.enter.prevent="areaOpen = false"
                            class="pl-10 py-3 block w-full stnd-input px-4 dark:bg-stone-800 dark:border-stone-700 dark:text-stone-200 dark:placeholder-stone-500"
                            placeholder="e.g. Accra, Texas, Greater London">
                    </div>
                    <ul x-show="areaOpen && areaMatches.length" x-cloak x-transition.opacity.duration.150ms
                        class="absolute z-30 mt-1 w-full max-h-48 overflow-auto modern-scrollbar rounded-xl border border-stone-200 dark:border-stone-700 bg-white dark:bg-stone-800 shadow-xl py-1">
                        <template x-for="area in areaMatches" :key="area.id">
                            <li @mousedown.prevent="localArea = area.name; areaOpen = false"
                                class="px-4 py-2 cursor-pointer text-sm text-stone-700 dark:text-stone-200 hover:bg-amber-50 dark:hover:bg-stone-700"
                                x-text="area.name"></li>
                        </template>
                    </ul>
                    <p class="text-xs text-stone-500 dark:text-stone-400 mt-1">
                        Not listed? Just type it and we'll save it for others from your area.
                    </p>
                    @error('local_area')
                        <p class="text-sm text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Languages -->
                <div class="mb-5 relative" @click.outside="langOpen = false">
                    <label for="origin-language" class="block text-sm font-medium text-stone-700 dark:text-stone-300 mb-1">
                        Languages you speak
                    </label>

                    <!-- Selected chips -->
                    <div class="flex flex-wrap gap-2 mb-2" x-show="selectedLanguages.length" x-cloak>
                        <template x-for="lang in selectedLanguages" :key="lang.key">
                            <span class="inline-flex items-center gap-1 pl-3 pr-2 py-1 rounded-full text-sm bg-amber-100 dark:bg-amber-500/20 text-amber-800 dark:text-amber-300 border border-amber-200 dark:border-amber-500/30">
                                <span x-text="lang.name"></span>
                                <span x-show="lang.isNew" class="text-[10px] uppercase tracking-wide opacity-70">new</span>
                                <button type="button" @click="removeLanguage(lang)" :aria-label="'Remove ' + lang.name"
                                    class="w-5 h-5 rounded-full flex items-center justify-center hover:bg-amber-200 dark:hover:bg-amber-500/30">&times;</button>
                            </span>
                        </template>
                    </div>

                    <!-- Search / add -->
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-stone-400 pointer-events-none">
                            <i data-lucide="languages" class="w-5 h-5"></i>
                        </span>
                        <input id="origin-language" type="text" x-model="langQuery" autocomplete="off" maxlength="60"
                            :disabled="languagesFull"
                            :placeholder="languagesFull ? 'Maximum of ' + maxLanguages + ' languages' : 'Search or add a language'"
                            @focus="langOpen = true" @input="langOpen = true; langActive = 0"
                            @keydown.arrow-down.prevent="moveLang(1)" @keydown.arrow-up.prevent="moveLang(-1)"
                            @keydown.enter.prevent="pickActiveLanguage()" @keydown.escape="langOpen = false"
                            @keydown.backspace="if (!langQuery) removeLastLanguage()"
                            class="pl-10 py-3 block w-full stnd-input px-4 dark:bg-stone-800 dark:border-stone-700 dark:text-stone-200 dark:placeholder-stone-500 disabled:opacity-50">
                    </div>

                    <ul x-show="langOpen && langQuery.trim() && (languageMatches.length || canAddLanguage)" x-cloak
                        x-transition.opacity.duration.150ms
                        class="absolute z-30 mt-1 w-full max-h-56 overflow-auto modern-scrollbar rounded-xl border border-stone-200 dark:border-stone-700 bg-white dark:bg-stone-800 shadow-xl py-1">
                        <template x-for="(lang, index) in languageMatches" :key="lang.id">
                            <li @mousedown.prevent="addLanguage(lang)" @mouseenter="langActive = index"
                                :class="index === langActive ? 'bg-amber-50 dark:bg-stone-700 text-amber-700 dark:text-amber-400' : 'text-stone-700 dark:text-stone-200'"
                                class="px-4 py-2 cursor-pointer text-sm" x-text="lang.name"></li>
                        </template>
                        <li x-show="canAddLanguage" @mousedown.prevent="addNewLanguage()"
                            @mouseenter="langActive = languageMatches.length"
                            :class="langActive === languageMatches.length ? 'bg-amber-50 dark:bg-stone-700' : ''"
                            class="px-4 py-2 cursor-pointer text-sm text-amber-600 dark:text-amber-400 font-medium border-t border-stone-100 dark:border-stone-700">
                            + Add "<span x-text="langQuery.trim()"></span>"
                        </li>
                    </ul>

                    <!-- Quick picks -->
                    <div class="flex flex-wrap gap-2 mt-3" x-show="popularLanguages.length && !languagesFull">
                        <template x-for="lang in popularLanguages" :key="lang.id">
                            <button type="button" @click="addLanguage(lang)"
                                class="px-3 py-1 rounded-full text-xs border border-stone-300 dark:border-stone-700 text-stone-600 dark:text-stone-300 hover:border-amber-500 hover:text-amber-600 dark:hover:text-amber-400 transition">
                                + <span x-text="lang.name"></span>
                            </button>
                        </template>
                    </div>

                    @if ($errors->has('language_ids') || $errors->has('language_ids.*') || $errors->has('new_languages') || $errors->has('new_languages.*'))
                        <p class="text-sm text-red-500 mt-1">
                            {{ $errors->first('language_ids') ?: $errors->first('language_ids.*') ?: $errors->first('new_languages') ?: $errors->first('new_languages.*') }}
                        </p>
                    @endif
                </div>

                <!-- Summary -->
                <div x-show="originSummary" x-cloak
                    class="mb-5 px-4 py-3 rounded-xl bg-stone-50 dark:bg-stone-800/60 border border-stone-200 dark:border-stone-700 text-sm text-stone-700 dark:text-stone-300 flex items-center gap-2">
                    <span x-text="selectedCountry ? selectedCountry.flag : ''"></span>
                    <span x-text="originSummary"></span>
                </div>

                <!-- Hidden fields submitted with the form -->
                <input type="hidden" name="country_id" :value="countryId ?? ''">
                <input type="hidden" name="state_id" :value="hasStates ? (stateId ?? '') : ''">
                <input type="hidden" name="local_government_id" :value="hasStates ? (lgaId ?? '') : ''">
                <input type="hidden" name="local_area" :value="!hasStates ? localArea : ''">
                <template x-for="id in languageIds" :key="'lid-' + id">
                    <input type="hidden" name="language_ids[]" :value="id">
                </template>
                <template x-for="name in newLanguages" :key="'nl-' + name">
                    <input type="hidden" name="new_languages[]" :value="name">
                </template>

                <!-- Actions -->
                <button type="submit" id="register-submit" :disabled="submitting"
                    class="w-full bg-amber-500 hover:bg-amber-600 text-white font-semibold py-3 rounded-xl transition-colors duration-200 shadow-sm active:scale-[0.99] disabled:opacity-60 flex items-center justify-center gap-2 cursor-pointer">
                    <svg x-show="submitting" x-cloak class="w-4 h-4 animate-spin" viewBox="0 0 24 24" fill="none">
                        <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3" class="opacity-25"></circle>
                        <path d="M22 12a10 10 0 0 0-10-10" stroke="currentColor" stroke-width="3" stroke-linecap="round"></path>
                    </svg>
                    Create account
                </button>

                <div class="mt-3 flex items-center justify-between text-sm">
                    <button type="button" id="register-back" @click="step = 1"
                        class="text-stone-500 hover:text-amber-600 flex items-center gap-1">
                        <i data-lucide="arrow-left" class="w-4 h-4"></i> Back
                    </button>
                    <button type="button" id="register-skip" @click="skip()" :disabled="submitting"
                        class="text-stone-500 hover:text-amber-600 underline-offset-2 hover:underline">
                        Skip for now
                    </button>
                </div>
            </div>
        </form>
    </div>
@endsection

@push('scripts')
    <script>
        window.KG_ORIGIN_OPTIONS = @json($originOptions);
        window.KG_AREAS_URL = @json(url('/locations/countries'));

        /** Searchable single-select component helper. */
        function searchSelect({ source }) {
            return {
                value: null,
                query: '',
                open: false,
                active: 0,

                get items() { return source() || []; },
                get selected() {
                    return this.items.find(i => String(i.id) === String(this.value)) || null;
                },
                get filtered() {
                    const showingSelection = this.selected && this.query === this.selected.name;
                    const q = showingSelection ? '' : this.query.trim().toLowerCase();
                    if (!q) return this.items;
                    return this.items
                        .filter(i => i.name.toLowerCase().includes(q))
                        .sort((a, b) => b.name.toLowerCase().startsWith(q) - a.name.toLowerCase().startsWith(q));
                },

                init() {
                    this.$nextTick(() => this.syncQuery());
                    this.$watch('value', () => this.syncQuery());
                },
                syncQuery() { this.query = this.selected ? this.selected.name : ''; },
                openList() {
                    if (this.open) return;
                    this.open = true;
                    this.active = Math.max(0, this.filtered.findIndex(i => String(i.id) === String(this.value)));
                    this.$nextTick(() => { this.$refs.input.select(); this.scrollActive(); });
                },
                close() {
                    if (!this.open) return;
                    this.open = false;
                    this.syncQuery();
                },
                choose(item) {
                    if (!item) return;
                    this.value = item.id;
                    this.open = false;
                    this.syncQuery();
                },
                clear() {
                    this.value = null;
                    this.query = '';
                    this.$refs.input.focus();
                },
                move(delta) {
                    if (!this.open) return this.openList();
                    const n = this.filtered.length;
                    if (!n) return;
                    this.active = (this.active + delta + n) % n;
                    this.scrollActive();
                },
                scrollActive() {
                    this.$nextTick(() => {
                        const el = this.$root.querySelectorAll('[role=option]')[this.active];
                        if (el) el.scrollIntoView({ block: 'nearest' });
                    });
                },
            };
        }

        /** Two-step registration form. */
        function registerForm(initial) {
            const opts = window.KG_ORIGIN_OPTIONS || {};
            const toId = v => (v === null || v === undefined || v === '') ? null : Number(v);

            return {
                step: initial.step,
                submitting: false,
                detectedCountryId: null,
                maxLanguages: 10,

                countries: opts.countries || [],
                statesByCountry: opts.statesByCountry || {},
                languages: opts.languages || [],
                popularIds: opts.popularLanguageIds || [],

                countryId: toId(initial.countryId),
                stateId: toId(initial.stateId),
                lgaId: toId(initial.lgaId),
                localArea: initial.localArea || '',
                languageIds: initial.languageIds || [],
                newLanguages: initial.newLanguages || [],

                areaOpen: false,
                areaSuggestions: [],
                langQuery: '',
                langOpen: false,
                langActive: 0,

                init() {
                    this.$watch('countryId', () => {
                        this.stateId = null;
                        this.lgaId = null;
                        this.localArea = '';
                        this.areaSuggestions = [];
                    });
                    this.$watch('stateId', () => { this.lgaId = null; });
                },

                // ---------- Location ----------
                get detected() { return this.detectedCountryId !== null && this.countryId === this.detectedCountryId; },
                get selectedCountry() { return this.countries.find(c => c.id === this.countryId) || null; },
                get states() { return (this.countryId && this.statesByCountry[this.countryId]) || []; },
                get hasStates() { return this.states.length > 0; },
                get selectedState() { return this.states.find(s => s.id === this.stateId) || null; },
                get lgas() { return this.selectedState ? this.selectedState.lgas : []; },
                get selectedLga() { return this.lgas.find(l => l.id === this.lgaId) || null; },
                get areaMatches() {
                    const q = this.localArea.trim().toLowerCase();
                    return this.areaSuggestions.filter(a => a.name.toLowerCase() !== q);
                },
                get originSummary() {
                    if (!this.selectedCountry) return '';
                    const local = this.hasStates
                        ? [this.selectedLga?.name, this.selectedState?.name]
                        : [this.localArea.trim()];
                    return [...local, this.selectedCountry.name].filter(Boolean).join(', ');
                },

                detectCountry() {
                    let iso = null;
                    try {
                        const tz = Intl.DateTimeFormat().resolvedOptions().timeZone || '';
                        if (tz === 'Africa/Lagos') iso = 'NG';
                    } catch (e) {}
                    if (!iso) {
                        for (const locale of (navigator.languages || [navigator.language || ''])) {
                            const m = /[-_]([A-Za-z]{2})$/.exec(locale);
                            if (m) { iso = m[1].toUpperCase(); break; }
                        }
                    }
                    const match = this.countries.find(c => c.iso2 === iso);
                    if (match) {
                        this.detectedCountryId = match.id;
                        this.countryId = match.id;
                    }
                },

                async fetchAreas() {
                    if (!this.countryId || this.hasStates) { this.areaSuggestions = []; return; }
                    const url = `${window.KG_AREAS_URL}/${this.countryId}/areas?q=${encodeURIComponent(this.localArea.trim())}`;
                    try {
                        const res = await fetch(url, { headers: { Accept: 'application/json' } });
                        this.areaSuggestions = res.ok ? await res.json() : [];
                    } catch (e) {
                        this.areaSuggestions = [];
                    }
                },

                // ---------- Languages ----------
                get languageCount() { return this.languageIds.length + this.newLanguages.length; },
                get languagesFull() { return this.languageCount >= this.maxLanguages; },
                get selectedLanguages() {
                    return [
                        ...this.languageIds.map(id => ({
                            key: 'id-' + id, id,
                            name: (this.languages.find(l => l.id === id) || {}).name || '',
                        })),
                        ...this.newLanguages.map(name => ({ key: 'new-' + name, name, isNew: true })),
                    ];
                },
                get popularLanguages() {
                    return this.popularIds
                        .map(id => this.languages.find(l => l.id === id))
                        .filter(l => l && !this.languageIds.includes(l.id));
                },
                get languageMatches() {
                    const q = this.langQuery.trim().toLowerCase();
                    if (!q) return [];
                    return this.languages
                        .filter(l => !this.languageIds.includes(l.id) && l.name.toLowerCase().includes(q))
                        .sort((a, b) => b.name.toLowerCase().startsWith(q) - a.name.toLowerCase().startsWith(q))
                        .slice(0, 8);
                },
                get canAddLanguage() {
                    const q = this.langQuery.trim().toLowerCase();
                    return q.length >= 2
                        && !this.languages.some(l => l.name.toLowerCase() === q)
                        && !this.newLanguages.some(n => n.toLowerCase() === q);
                },
                addLanguage(lang) {
                    if (this.languagesFull || this.languageIds.includes(lang.id)) return;
                    this.languageIds.push(lang.id);
                    this.langQuery = '';
                    this.langActive = 0;
                },
                addNewLanguage() {
                    if (this.languagesFull || !this.canAddLanguage) return;
                    this.newLanguages.push(this.langQuery.trim());
                    this.langQuery = '';
                    this.langActive = 0;
                },
                removeLanguage(lang) {
                    if (lang.isNew) this.newLanguages = this.newLanguages.filter(n => n !== lang.name);
                    else this.languageIds = this.languageIds.filter(id => id !== lang.id);
                },
                removeLastLanguage() {
                    const all = this.selectedLanguages;
                    if (all.length) this.removeLanguage(all[all.length - 1]);
                },
                moveLang(delta) {
                    const n = this.languageMatches.length + (this.canAddLanguage ? 1 : 0);
                    if (!n) return;
                    this.langActive = (this.langActive + delta + n) % n;
                },
                pickActiveLanguage() {
                    const matches = this.languageMatches;
                    if (this.langActive < matches.length) this.addLanguage(matches[this.langActive]);
                    else if (this.canAddLanguage) this.addNewLanguage();
                },

                // ---------- Flow ----------
                next() {
                    const fields = this.$refs.step1.querySelectorAll('input[required]');
                    for (const field of fields) {
                        if (!field.checkValidity()) { field.reportValidity(); return; }
                    }
                    const pw = document.getElementById('password');
                    const confirm = document.getElementById('password_confirmation');
                    if (pw.value !== confirm.value) {
                        confirm.setCustomValidity('Passwords do not match.');
                        confirm.reportValidity();
                        confirm.addEventListener('input', () => confirm.setCustomValidity(''), { once: true });
                        return;
                    }
                    this.step = 2;
                    if (!this.countryId) this.detectCountry();
                    this.$nextTick(() => {
                        window.scrollTo({ top: 0, behavior: 'smooth' });
                        if (window.lucide) lucide.createIcons();
                    });
                },
                onSubmit(event) {
                    if (this.step === 1) {
                        event.preventDefault();
                        this.next();
                        return;
                    }
                    this.submitting = true;
                },
                skip() {
                    this.countryId = null;
                    this.$nextTick(() => {
                        this.languageIds = [];
                        this.newLanguages = [];
                        this.$nextTick(() => {
                            this.submitting = true;
                            this.$refs.form.submit();
                        });
                    });
                },
            };
        }

        document.addEventListener('DOMContentLoaded', function () {
            lucide.createIcons();
        });
    </script>
@endpush