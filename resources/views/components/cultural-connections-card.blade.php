@auth
<div x-data="culturalDiscoveryWidget()" x-init="init()" 
    class="bg-gradient-to-r from-amber-500/10 via-stone-900/5 to-emerald-500/10 dark:from-amber-950/20 dark:via-stone-900 dark:to-emerald-950/20 rounded-2xl sm:rounded-3xl border border-amber-500/20 dark:border-amber-500/10 shadow-sm mb-6 transition-all duration-300 relative"
    :class="isExpanded ? 'p-5 sm:p-6 shadow-md' : 'p-3 sm:p-4'">
    
    {{-- Top Bar: Header & Controls (Clickable to Expand) --}}
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-3">
        <div class="flex items-center gap-3 cursor-pointer select-none min-w-0" @click="toggleExpand()">
            <div class="w-9 h-9 sm:w-10 sm:h-10 rounded-2xl bg-gradient-to-br from-amber-500 to-amber-600 text-white flex items-center justify-center shadow-md shadow-amber-500/20 shrink-0">
                <i data-lucide="sparkles" class="w-4 h-4 sm:w-5 sm:h-5"></i>
            </div>
            <div class="min-w-0">
                <div class="flex items-center gap-2 flex-wrap">
                    <h3 class="font-bold text-stone-900 dark:text-stone-100 text-sm sm:text-base tracking-tight truncate">
                        Cultural Connections & Discoveries
                    </h3>
                    <span class="inline-flex items-center gap-1 text-[10px] font-black uppercase tracking-wider px-2 py-0.5 rounded-full bg-amber-500/15 text-amber-700 dark:text-amber-400 border border-amber-500/25 shrink-0">
                        <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span>
                        AI Matched
                    </span>
                </div>
                <p class="text-xs text-stone-500 dark:text-stone-400 mt-0.5 line-clamp-1 sm:line-clamp-none">
                    Based on your voluntarily declared cultural background, interests, and languages.
                </p>
            </div>
        </div>

        {{-- Tab Buttons, Privacy Controls & Expand Action --}}
        <div class="flex items-center gap-2 flex-wrap sm:flex-nowrap justify-between lg:justify-end shrink-0">
            <template x-if="!optedOut">
                <div class="inline-flex items-center bg-white/80 dark:bg-stone-900/80 p-1 rounded-2xl border border-stone-200 dark:border-stone-800 shadow-xs text-xs font-bold">
                    <button type="button" 
                        @click="switchSection('users'); isExpanded = true" 
                        :class="isExpanded && section === 'users' ? 'bg-amber-500 text-white shadow-sm' : 'text-stone-600 dark:text-stone-400 hover:text-stone-900 dark:hover:text-stone-200'"
                        class="px-2.5 sm:px-3 py-1.5 rounded-xl transition-all flex items-center gap-1.5">
                        <i data-lucide="users" class="w-3.5 h-3.5"></i>
                        <span>People</span>
                        <span class="text-[10px] px-1.5 py-0.2 rounded-full font-mono font-bold"
                            :class="isExpanded && section === 'users' ? 'bg-white/25 text-white' : 'bg-stone-100 dark:bg-stone-800 text-stone-600 dark:text-stone-400'"
                            x-text="loading ? '...' : users.length"></span>
                    </button>
                    <button type="button" 
                        @click="switchSection('communities'); isExpanded = true" 
                        :class="isExpanded && section === 'communities' ? 'bg-amber-500 text-white shadow-sm' : 'text-stone-600 dark:text-stone-400 hover:text-stone-900 dark:hover:text-stone-200'"
                        class="px-2.5 sm:px-3 py-1.5 rounded-xl transition-all flex items-center gap-1.5">
                        <i data-lucide="circle-dot" class="w-3.5 h-3.5"></i>
                        <span>Circles</span>
                        <span class="text-[10px] px-1.5 py-0.2 rounded-full font-mono font-bold"
                            :class="isExpanded && section === 'communities' ? 'bg-white/25 text-white' : 'bg-stone-100 dark:bg-stone-800 text-stone-600 dark:text-stone-400'"
                            x-text="loading ? '...' : communities.length"></span>
                    </button>
                </div>
            </template>

            <button type="button" 
                @click.stop="openPrivacyModal()" 
                class="px-2.5 sm:px-3 py-1.5 rounded-2xl border border-stone-200 dark:border-stone-800 bg-white/80 dark:bg-stone-900/80 text-stone-600 dark:text-stone-300 hover:text-amber-600 hover:border-amber-500/40 transition text-xs font-bold flex items-center gap-1.5 shadow-xs">
                <i data-lucide="shield-check" class="w-3.5 h-3.5 text-amber-500"></i>
                <span class="hidden sm:inline" x-text="optedOut ? 'Opted Out' : 'Privacy Control'"></span>
                <span class="sm:hidden" x-text="optedOut ? 'Opted Out' : 'Privacy'"></span>
            </button>

            {{-- Expand / Collapse Button --}}
            <button type="button" 
                @click="toggleExpand()"
                :class="isExpanded ? 'bg-stone-200 dark:bg-stone-800 text-stone-700 dark:text-stone-300 hover:bg-stone-300 dark:hover:bg-stone-700' : 'bg-amber-500 hover:bg-amber-600 text-stone-950 font-black shadow-sm'"
                class="px-3 py-1.5 rounded-2xl font-bold text-xs transition flex items-center gap-1.5 shrink-0">
                <span x-text="isExpanded ? 'Collapse' : 'Explore'"></span>
                <i :data-lucide="isExpanded ? 'chevron-up' : 'chevron-down'" class="w-3.5 h-3.5"></i>
            </button>
        </div>
    </div>

    {{-- Expandable Content Drawer --}}
    <div x-show="isExpanded" 
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 -translate-y-2"
        x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="mt-4 pt-4 border-t border-amber-500/20 dark:border-amber-500/10 space-y-4">

        {{-- State: Opted Out --}}
        <template x-if="optedOut">
            <div class="p-6 bg-white/70 dark:bg-stone-950/60 rounded-2xl border border-stone-200/80 dark:border-stone-800/80 text-center space-y-3">
            <div class="w-12 h-12 rounded-2xl bg-stone-100 dark:bg-stone-800 text-stone-400 flex items-center justify-center mx-auto">
                <i data-lucide="eye-off" class="w-6 h-6"></i>
            </div>
            <div>
                <h4 class="text-sm font-bold text-stone-800 dark:text-stone-200">Personalized Cultural Recommendations are Paused</h4>
                <p class="text-xs text-stone-500 max-w-md mx-auto mt-1">
                    Your declared profile interests and languages are currently not being matched with peers or circles. You have full control and can resume discovery at any time.
                </p>
            </div>
            <div class="flex items-center justify-center gap-2 pt-1">
                <button type="button" @click="toggleOptIn(true)" class="px-4 py-2 rounded-xl bg-amber-600 hover:bg-amber-500 text-white font-bold text-xs shadow-md shadow-amber-600/20 transition flex items-center gap-1.5">
                    <i data-lucide="power" class="w-3.5 h-3.5"></i>
                    <span>Turn On Recommendations</span>
                </button>
                <button type="button" @click="openPrivacyModal()" class="px-4 py-2 rounded-xl bg-stone-100 dark:bg-stone-800 hover:bg-stone-200 text-stone-700 dark:text-stone-300 font-bold text-xs transition">
                    Privacy Details
                </button>
            </div>
        </div>
    </template>

    {{-- State: Loading Skeleton --}}
    <template x-if="!optedOut && loading">
        <div class="py-10 text-center text-xs text-stone-400 flex flex-col items-center justify-center gap-3">
            <div class="w-8 h-8 border-3 border-amber-500 border-t-transparent rounded-full animate-spin"></div>
            <span class="font-medium text-stone-500">Matching with cultural custodians, peers, and heritage circles...</span>
        </div>
    </template>

    {{-- State: SECTION = PEOPLE --}}
    <template x-if="!optedOut && !loading && section === 'users'">
        <div>
            <template x-if="users.length === 0">
                <div class="p-6 text-center bg-white/60 dark:bg-stone-950/60 rounded-2xl border border-stone-200/60 dark:border-stone-800/60 space-y-3">
                    <i data-lucide="user-check" class="w-8 h-8 text-amber-500 mx-auto"></i>
                    <p class="text-xs font-bold text-stone-800 dark:text-stone-200">No new cultural peer recommendations right now.</p>
                    <p class="text-[11px] text-stone-500 max-w-md mx-auto">
                        Add more cultural interests, spoken languages, or heritage traditions to your profile to discover more people!
                    </p>
                    <a href="{{ route('profile.edit') }}" class="inline-flex items-center gap-1.5 px-4 py-1.5 rounded-xl bg-amber-500 text-white font-bold text-xs hover:bg-amber-600 transition shadow-xs">
                        <i data-lucide="edit-3" class="w-3.5 h-3.5"></i>
                        <span>Update Cultural Profile</span>
                    </a>
                </div>
            </template>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-3.5">
                <template x-for="item in users" :key="item && item.user ? item.user.id : Math.random()">
                    <template x-if="item && item.user">
                        <div class="bg-white/90 dark:bg-stone-900/90 backdrop-blur-md rounded-2xl p-4 border border-stone-200/90 dark:border-stone-800/90 shadow-xs flex flex-col justify-between gap-3 group relative transition hover:border-amber-500/40 hover:shadow-md">
                            
                            {{-- User Info Header --}}
                            <div class="flex items-start justify-between gap-3">
                                <div class="flex items-start gap-3 min-w-0 flex-1">
                                    <a :href="'/profile/' + item.user.id" class="shrink-0 relative group/avatar">
                                        <img :src="item.user.profile_photo_url || item.user.avatar || ('https://ui-avatars.com/api/?name=' + encodeURIComponent(item.user.name) + '&color=F59E0B&background=FEF3C7')" 
                                            :alt="item.user.name"
                                            class="w-12 h-12 rounded-2xl object-cover border-2 border-amber-500/20 group-hover/avatar:border-amber-500 transition shadow-xs">
                                        <div class="absolute -bottom-1 -right-1 w-3.5 h-3.5 bg-emerald-500 border-2 border-white dark:border-stone-900 rounded-full"></div>
                                    </a>
                                    <div class="min-w-0 flex-1">
                                        <div class="flex items-center gap-1.5 flex-wrap">
                                            <a :href="'/profile/' + item.user.id" class="font-bold text-stone-900 dark:text-stone-100 text-sm hover:text-amber-600 dark:hover:text-amber-400 transition truncate" x-text="item.user.name"></a>
                                            <template x-if="item.user.cultural_background">
                                                <span class="text-[10px] font-bold px-2 py-0.5 rounded-md bg-amber-500/10 text-amber-700 dark:text-amber-400 border border-amber-500/20" x-text="item.user.cultural_background"></span>
                                            </template>
                                        </div>
                                        <p class="text-xs text-stone-400 font-mono mt-0.5 truncate" x-text="'@' + (item.user.username || 'user')"></p>
                                    </div>
                                </div>

                                {{-- Dismiss Button --}}
                                <button type="button" 
                                    @click="dismiss('user', item.user.id)" 
                                    title="Dismiss recommendation"
                                    class="text-stone-400 hover:text-stone-700 dark:hover:text-stone-200 p-1.5 rounded-xl hover:bg-stone-100 dark:hover:bg-stone-800 transition">
                                    <i data-lucide="x" class="w-4 h-4"></i>
                                </button>
                            </div>

                            {{-- Explainable Matching Reason --}}
                            <div class="p-2.5 rounded-xl bg-amber-500/10 dark:bg-amber-950/30 border border-amber-500/20 text-xs text-stone-700 dark:text-stone-300 flex items-start gap-2">
                                <i data-lucide="sparkles" class="w-3.5 h-3.5 text-amber-600 dark:text-amber-400 shrink-0 mt-0.5"></i>
                                <span class="leading-relaxed" x-text="item.explanation"></span>
                            </div>

                            {{-- Matched Tags & Actions Footer --}}
                            <div class="flex items-center justify-between gap-2 pt-1 border-t border-stone-100 dark:border-stone-800/80">
                                <div class="flex flex-wrap gap-1 min-w-0 flex-1">
                                    <template x-if="item.matched_interests && item.matched_interests.length > 0">
                                        <template x-for="tag in item.matched_interests.slice(0, 3)" :key="tag">
                                            <span class="text-[10px] font-medium px-2 py-0.5 rounded-full bg-stone-100 dark:bg-stone-800 text-stone-600 dark:text-stone-400" x-text="'#' + tag"></span>
                                        </template>
                                    </template>
                                </div>

                                <a :href="'/profile/' + item.user.id" 
                                    class="inline-flex items-center gap-1 px-3 py-1.5 rounded-xl bg-stone-900 dark:bg-stone-100 text-white dark:text-stone-900 font-bold text-xs hover:bg-amber-600 dark:hover:bg-amber-500 dark:hover:text-white transition shadow-xs shrink-0">
                                    <span>View Profile</span>
                                    <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                                </a>
                            </div>
                        </div>
                    </template>
                </template>
            </div>
        </div>
    </template>

    {{-- State: SECTION = CIRCLES (COMMUNITIES) --}}
    <template x-if="!optedOut && !loading && section === 'communities'">
        <div>
            <template x-if="communities.length === 0">
                <div class="p-6 text-center bg-white/60 dark:bg-stone-950/60 rounded-2xl border border-stone-200/60 dark:border-stone-800/60 space-y-3">
                    <i data-lucide="circle-dot" class="w-8 h-8 text-amber-500 mx-auto"></i>
                    <p class="text-xs font-bold text-stone-800 dark:text-stone-200">No new circle recommendations right now.</p>
                    <p class="text-[11px] text-stone-500 max-w-md mx-auto">
                        Explore all cultural communities or create a new community circle to preserve your heritage!
                    </p>
                    <a href="{{ route('communities.index') }}" class="inline-flex items-center gap-1.5 px-4 py-1.5 rounded-xl bg-amber-500 text-white font-bold text-xs hover:bg-amber-600 transition shadow-xs">
                        <i data-lucide="compass" class="w-3.5 h-3.5"></i>
                        <span>Explore All Circles</span>
                    </a>
                </div>
            </template>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-3.5">
                <template x-for="item in communities" :key="item && item.community ? item.community.id : Math.random()">
                    <template x-if="item && item.community">
                        <div class="bg-white/90 dark:bg-stone-900/90 backdrop-blur-md rounded-2xl p-4 border border-stone-200/90 dark:border-stone-800/90 shadow-xs flex flex-col justify-between gap-3 group relative transition hover:border-amber-500/40 hover:shadow-md">
                            
                            {{-- Community Info Header --}}
                            <div class="flex items-start justify-between gap-3">
                                <div class="flex items-start gap-3 min-w-0 flex-1">
                                    <template x-if="item.community.image">
                                        <img :src="item.community.image" :alt="item.community.name" class="w-12 h-12 rounded-2xl object-cover border-2 border-amber-500/20 shrink-0">
                                    </template>
                                    <template x-if="!item.community.image">
                                        <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-amber-500/20 to-orange-500/20 text-amber-600 dark:text-amber-400 flex items-center justify-center font-black text-sm shrink-0 border border-amber-500/30">
                                            <i data-lucide="users" class="w-6 h-6"></i>
                                        </div>
                                    </template>

                                    <div class="min-w-0 flex-1">
                                        <div class="flex items-center gap-2 flex-wrap">
                                            <a :href="'/communities/' + item.community.id" class="font-bold text-stone-900 dark:text-stone-100 text-sm hover:text-amber-600 dark:hover:text-amber-400 transition truncate block" x-text="item.community.name"></a>
                                            <span class="text-[10px] font-bold px-2 py-0.5 rounded-md bg-stone-100 dark:bg-stone-800 text-stone-600 dark:text-stone-400 flex items-center gap-1">
                                                <i data-lucide="users" class="w-3 h-3"></i>
                                                <span x-text="(item.community.members_count || 1) + ' members'"></span>
                                            </span>
                                        </div>
                                        <p class="text-xs text-stone-500 dark:text-stone-400 line-clamp-1 mt-0.5" x-text="item.community.description || 'Cultural preservation community circle'"></p>
                                    </div>
                                </div>

                                {{-- Dismiss Button --}}
                                <button type="button" 
                                    @click="dismiss('community', item.community.id)" 
                                    title="Dismiss recommendation"
                                    class="text-stone-400 hover:text-stone-700 dark:hover:text-stone-200 p-1.5 rounded-xl hover:bg-stone-100 dark:hover:bg-stone-800 transition">
                                    <i data-lucide="x" class="w-4 h-4"></i>
                                </button>
                            </div>

                            {{-- Explainable Reason --}}
                            <div class="p-2.5 rounded-xl bg-amber-500/10 dark:bg-amber-950/30 border border-amber-500/20 text-xs text-stone-700 dark:text-stone-300 flex items-start gap-2">
                                <i data-lucide="sparkles" class="w-3.5 h-3.5 text-amber-600 dark:text-amber-400 shrink-0 mt-0.5"></i>
                                <span class="leading-relaxed" x-text="item.explanation"></span>
                            </div>

                            {{-- Actions Footer --}}
                            <div class="flex items-center justify-between gap-2 pt-1 border-t border-stone-100 dark:border-stone-800/80">
                                <span class="text-[11px] text-stone-400">Cultural Preservation Circle</span>
                                <a :href="'/communities/' + item.community.id" 
                                    class="inline-flex items-center gap-1 px-3 py-1.5 rounded-xl bg-amber-500 hover:bg-amber-600 text-white font-bold text-xs transition shadow-xs shrink-0">
                                    <span>Explore Circle</span>
                                    <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                                </a>
                            </div>
                        </div>
                    </template>
                </template>
            </div>
        </div>
    </template>
    </div>

    {{-- Privacy Control Modal --}}
    <div x-show="privacyModalOpen" 
        x-cloak
        class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-stone-950/70 backdrop-blur-sm"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0">
        
        <div @click.away="privacyModalOpen = false" 
            class="bg-white dark:bg-stone-900 border border-stone-200 dark:border-stone-800 rounded-3xl p-6 sm:p-7 max-w-lg w-full shadow-2xl space-y-5 relative">
            
            <div class="flex items-start justify-between gap-3">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-2xl bg-amber-500/20 text-amber-500 flex items-center justify-center shrink-0">
                        <i data-lucide="shield-check" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <h4 class="font-bold text-stone-900 dark:text-stone-100 text-base">Cultural AI & Privacy Controls</h4>
                        <p class="text-xs text-stone-500">Transparent, voluntary cultural discovery</p>
                    </div>
                </div>
                <button type="button" @click="privacyModalOpen = false" class="text-stone-400 hover:text-stone-600 dark:hover:text-stone-200 p-1.5 rounded-xl hover:bg-stone-100 dark:hover:bg-stone-800 transition">
                    <i data-lucide="x" class="w-4 h-4"></i>
                </button>
            </div>

            {{-- Privacy Explanations --}}
            <div class="space-y-3 text-xs text-stone-600 dark:text-stone-400">
                <div class="p-3.5 rounded-2xl bg-amber-500/5 border border-amber-500/15 space-y-1.5">
                    <div class="font-bold text-stone-800 dark:text-stone-200 flex items-center gap-1.5">
                        <i data-lucide="check-circle" class="w-4 h-4 text-emerald-500"></i>
                        <span>How AI Matching Works</span>
                    </div>
                    <p class="leading-relaxed">
                        Kulturegram matches you with peers, elders, and circles strictly using the <strong>voluntary cultural details</strong> you share: your declared cultural heritage, language interests, and cultural topics.
                    </p>
                </div>

                <div class="p-3.5 rounded-2xl bg-stone-50 dark:bg-stone-800/50 border border-stone-200 dark:border-stone-800 space-y-1.5">
                    <div class="font-bold text-stone-800 dark:text-stone-200 flex items-center gap-1.5">
                        <i data-lucide="lock" class="w-4 h-4 text-amber-500"></i>
                        <span>Our Privacy Commitment</span>
                    </div>
                    <ul class="list-disc list-inside space-y-1 pl-1">
                        <li>We never sell your cultural data or profile attributes.</li>
                        <li>We never track your external browsing behavior.</li>
                        <li>You can opt-out at any time with a single click.</li>
                    </ul>
                </div>
            </div>

            {{-- Master Toggle --}}
            <div class="flex items-center justify-between p-4 rounded-2xl bg-stone-100 dark:bg-stone-800/80 border border-stone-200 dark:border-stone-700">
                <div>
                    <p class="font-bold text-stone-900 dark:text-stone-100 text-sm">Personalized Cultural Recommendations</p>
                    <p class="text-xs text-stone-500" x-text="optedOut ? 'Currently turned off (No matching active)' : 'Currently active based on your profile'"></p>
                </div>
                <button type="button" 
                    @click="toggleOptIn(optedOut)" 
                    :class="!optedOut ? 'bg-amber-500' : 'bg-stone-300 dark:bg-stone-600'"
                    class="relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-hidden">
                    <span :class="!optedOut ? 'translate-x-5' : 'translate-x-0'"
                        class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow-md ring-0 transition duration-200 ease-in-out"></span>
                </button>
            </div>

            {{-- Message Toast --}}
            <template x-if="privacyMessage">
                <div class="p-3 rounded-xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-700 dark:text-emerald-300 text-xs font-semibold flex items-center gap-2">
                    <i data-lucide="check" class="w-4 h-4"></i>
                    <span x-text="privacyMessage"></span>
                </div>
            </template>

            {{-- Footer Actions --}}
            <div class="flex items-center justify-between pt-2">
                <a href="{{ route('profile.edit') }}" class="text-xs text-amber-600 hover:text-amber-700 font-bold flex items-center gap-1">
                    <i data-lucide="edit" class="w-3.5 h-3.5"></i>
                    <span>Edit Cultural Profile</span>
                </a>
                <button type="button" @click="privacyModalOpen = false" class="px-4 py-2 rounded-xl bg-stone-900 dark:bg-stone-100 text-white dark:text-stone-900 font-bold text-xs hover:bg-stone-800 transition">
                    Done
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    function culturalDiscoveryWidget() {
        return {
            isExpanded: false,
            section: 'users',
            loading: true,
            optedOut: false,
            users: [],
            communities: [],
            privacyModalOpen: false,
            privacyMessage: '',

            async init() {
                await this.fetchRecommendations();
            },

            toggleExpand() {
                this.isExpanded = !this.isExpanded;
                this.$nextTick(() => {
                    if (window.lucide) {
                        window.lucide.createIcons();
                    }
                });
            },

            switchSection(newSection) {
                this.section = newSection;
                this.isExpanded = true;
                this.$nextTick(() => {
                    if (window.lucide) {
                        window.lucide.createIcons();
                    }
                });
            },

            openPrivacyModal() {
                this.privacyModalOpen = true;
                this.privacyMessage = '';
                this.$nextTick(() => {
                    if (window.lucide) {
                        window.lucide.createIcons();
                    }
                });
            },

            async fetchRecommendations() {
                this.loading = true;
                try {
                    const res = await fetch("{{ route('cultural-discovery.recommendations') }}");
                    const data = await res.json();
                    if (data && data.success) {
                        this.optedOut = !!data.opted_out;
                        
                        // Handle either unwrapped array or envelope recommendations array
                        if (Array.isArray(data.users)) {
                            this.users = data.users;
                        } else if (data.users && Array.isArray(data.users.recommendations)) {
                            this.users = data.users.recommendations;
                        } else {
                            this.users = [];
                        }

                        if (Array.isArray(data.communities)) {
                            this.communities = data.communities;
                        } else if (data.communities && Array.isArray(data.communities.recommendations)) {
                            this.communities = data.communities.recommendations;
                        } else {
                            this.communities = [];
                        }
                    }
                } catch (e) {
                    console.error('Error fetching cultural recommendations:', e);
                } finally {
                    this.loading = false;
                    this.$nextTick(() => {
                        if (window.lucide) {
                            window.lucide.createIcons();
                        }
                    });
                }
            },

            async dismiss(type, id) {
                // Optimistic removal
                if (type === 'user') {
                    this.users = this.users.filter(u => u && u.user && u.user.id !== id);
                } else if (type === 'community') {
                    this.communities = this.communities.filter(c => c && c.community && c.community.id !== id);
                }

                this.$nextTick(() => {
                    if (window.lucide) {
                        window.lucide.createIcons();
                    }
                });

                try {
                    await fetch("{{ route('cultural-discovery.dismiss') }}", {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': "{{ csrf_token() }}"
                        },
                        body: JSON.stringify({ type, id })
                    });
                } catch (e) {
                    console.error('Failed to record dismissal:', e);
                }
            },

            async toggleOptIn(targetEnable) {
                try {
                    const res = await fetch("{{ route('cultural-discovery.toggle-opt-in') }}", {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': "{{ csrf_token() }}"
                        },
                        body: JSON.stringify({ enabled: targetEnable })
                    });
                    const data = await res.json();
                    if (data && data.success) {
                        this.optedOut = !targetEnable;
                        this.privacyMessage = data.message || (targetEnable ? 'AI recommendations enabled!' : 'AI recommendations paused.');
                        
                        if (targetEnable) {
                            await this.fetchRecommendations();
                        } else {
                            this.users = [];
                            this.communities = [];
                        }

                        setTimeout(() => {
                            this.privacyMessage = '';
                        }, 4000);
                    }
                } catch (e) {
                    console.error('Error toggling opt-in:', e);
                } finally {
                    this.$nextTick(() => {
                        if (window.lucide) {
                            window.lucide.createIcons();
                        }
                    });
                }
            }
        };
    }
</script>
@endauth
