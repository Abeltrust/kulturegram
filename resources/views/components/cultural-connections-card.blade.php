@auth
<div x-data="culturalDiscoveryWidget()" x-init="init()" class="bg-gradient-to-r from-amber-500/10 via-stone-900/5 to-emerald-500/10 dark:from-amber-950/20 dark:via-stone-900 dark:to-emerald-950/20 rounded-3xl p-5 sm:p-6 border border-amber-500/20 dark:border-amber-500/10 shadow-sm mb-8 transition-all">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-4">
        <div class="flex items-center gap-2.5">
            <div class="w-9 h-9 rounded-xl bg-amber-500/20 text-amber-500 flex items-center justify-center shrink-0">
                <i data-lucide="compass" class="w-5 h-5"></i>
            </div>
            <div>
                <h3 class="font-bold text-stone-900 dark:text-stone-100 text-sm sm:text-base flex items-center gap-2">
                    Cultural Connections & Discoveries
                    <span class="text-[10px] font-black uppercase tracking-wider px-2 py-0.2 rounded-full bg-amber-500/20 text-amber-500 border border-amber-500/30">AI Matched</span>
                </h3>
                <p class="text-xs text-stone-500">Based on your voluntarily declared cultural background, interests, and languages.</p>
            </div>
        </div>

        {{-- Privacy & Tab Controls --}}
        <div class="flex items-center gap-2">
            <template x-if="!optedOut">
                <div class="flex items-center bg-white/60 dark:bg-stone-900/60 p-1 rounded-xl border border-stone-200 dark:border-stone-800 text-xs font-bold">
                    <button @click="section = 'users'" 
                        :class="section === 'users' ? 'bg-amber-500 text-white shadow-sm' : 'text-stone-500 hover:text-stone-800 dark:hover:text-stone-200'"
                        class="px-2.5 py-1 rounded-lg transition">People</button>
                    <button @click="section = 'communities'" 
                        :class="section === 'communities' ? 'bg-amber-500 text-white shadow-sm' : 'text-stone-500 hover:text-stone-800 dark:hover:text-stone-200'"
                        class="px-2.5 py-1 rounded-lg transition">Circles</button>
                </div>
            </template>

            <button @click="toggleOptIn()" 
                :title="optedOut ? 'Enable recommendations' : 'Disable recommendations (Privacy Opt-out)'"
                class="p-2 rounded-xl border border-stone-200 dark:border-stone-800 bg-white/60 dark:bg-stone-900/60 text-stone-500 hover:text-amber-500 transition text-xs flex items-center gap-1">
                <i data-lucide="shield" class="w-3.5 h-3.5"></i>
                <span class="hidden sm:inline" x-text="optedOut ? 'Opted Out' : 'Privacy Control'"></span>
            </button>
        </div>
    </div>

    {{-- State: Opted Out --}}
    <template x-if="optedOut">
        <div class="p-6 bg-white/40 dark:bg-stone-950/40 rounded-2xl border border-stone-200/50 dark:border-stone-800/50 text-center space-y-2">
            <i data-lucide="eye-off" class="w-8 h-8 mx-auto text-stone-400"></i>
            <p class="text-xs font-bold text-stone-700 dark:text-stone-300">Personalized AI recommendations are turned off.</p>
            <p class="text-[11px] text-stone-500 max-w-md mx-auto">Your declared profile interests are currently not matched with others. You can turn this back on whenever you wish.</p>
            <button @click="toggleOptIn()" class="mt-2 px-4 py-1.5 rounded-xl bg-amber-600 hover:bg-amber-500 text-white font-bold text-xs shadow transition">
                Turn On Recommendations
            </button>
        </div>
    </template>

    {{-- State: Loading --}}
    <template x-if="!optedOut && loading">
        <div class="py-8 text-center text-xs text-stone-400 flex items-center justify-center gap-2">
            <span class="w-4 h-4 border-2 border-amber-500 border-t-transparent rounded-full animate-spin"></span>
            <span>Finding like-minded cultural connections...</span>
        </div>
    </template>

    {{-- State: SECTION = USERS --}}
    <template x-if="!optedOut && !loading && section === 'users'">
        <div>
            <template x-if="users.length === 0">
                <div class="p-4 text-center text-xs text-stone-400 bg-white/40 dark:bg-stone-950/40 rounded-xl">
                    No new user recommendations right now. Add more cultural interests to your profile to find collaborators!
                </div>
            </template>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                <template x-for="item in users" :key="item.user.id">
                    <div class="bg-white dark:bg-stone-900 rounded-2xl p-4 border border-stone-200 dark:border-stone-800 shadow-sm flex items-start justify-between gap-3 group relative transition hover:border-amber-500/30">
                        <div class="flex items-start gap-3 flex-1 min-w-0">
                            <img :src="item.user.profile_photo_url || 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?auto=format&fit=crop&q=80&w=200'" 
                                class="w-11 h-11 rounded-full object-cover border border-amber-500/20 shrink-0">
                            <div class="min-w-0 flex-1">
                                <div class="flex items-center gap-1.5">
                                    <a :href="'/profile/' + item.user.id" class="font-bold text-stone-900 dark:text-stone-100 text-sm hover:text-amber-500 truncate" x-text="item.user.name"></a>
                                    <template x-if="item.user.cultural_background">
                                        <span class="text-[10px] font-bold px-1.5 py-0.2 rounded bg-amber-500/10 text-amber-600 dark:text-amber-400 truncate" x-text="item.user.cultural_background"></span>
                                    </template>
                                </div>
                                <p class="text-[11px] text-stone-400 truncate" x-text="'@' + item.user.username"></p>

                                {{-- Explainable Matching Reason --}}
                                <div class="mt-2 p-2 rounded-lg bg-amber-500/5 border border-amber-500/10 text-[11px] text-stone-600 dark:text-stone-400 flex items-start gap-1.5">
                                    <i data-lucide="sparkles" class="w-3.5 h-3.5 text-amber-500 shrink-0 mt-0.5"></i>
                                    <span class="leading-tight" x-text="item.explanation"></span>
                                </div>

                                {{-- Matched Interests Tags --}}
                                <template x-if="item.matched_interests && item.matched_interests.length > 0">
                                    <div class="flex flex-wrap gap-1 mt-1.5">
                                        <template x-for="tag in item.matched_interests" :key="tag">
                                            <span class="text-[9px] font-bold px-1.5 py-0.2 rounded-full bg-stone-100 dark:bg-stone-800 text-stone-600 dark:text-stone-400" x-text="'#' + tag"></span>
                                        </template>
                                    </div>
                                </template>
                            </div>
                        </div>

                        {{-- Dismiss Button --}}
                        <button @click="dismiss('user', item.user.id)" 
                            title="Dismiss recommendation"
                            class="text-stone-400 hover:text-stone-600 dark:hover:text-stone-200 p-1 rounded-lg hover:bg-stone-100 dark:hover:bg-stone-800 transition">
                            <i data-lucide="x" class="w-3.5 h-3.5"></i>
                        </button>
                    </div>
                </template>
            </div>
        </div>
    </template>

    {{-- State: SECTION = COMMUNITIES --}}
    <template x-if="!optedOut && !loading && section === 'communities'">
        <div>
            <template x-if="communities.length === 0">
                <div class="p-4 text-center text-xs text-stone-400 bg-white/40 dark:bg-stone-950/40 rounded-xl">
                    No new circle recommendations right now.
                </div>
            </template>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                <template x-for="item in communities" :key="item.community.id">
                    <div class="bg-white dark:bg-stone-900 rounded-2xl p-4 border border-stone-200 dark:border-stone-800 shadow-sm flex items-start justify-between gap-3 group relative transition hover:border-amber-500/30">
                        <div class="flex items-start gap-3 flex-1 min-w-0">
                            <div class="w-11 h-11 rounded-2xl bg-amber-500/10 text-amber-500 flex items-center justify-center font-black text-sm shrink-0 border border-amber-500/20">
                                <i data-lucide="users" class="w-5 h-5"></i>
                            </div>
                            <div class="min-w-0 flex-1">
                                <a :href="'/communities/' + item.community.id" class="font-bold text-stone-900 dark:text-stone-100 text-sm hover:text-amber-500 truncate block" x-text="item.community.name"></a>
                                <p class="text-[11px] text-stone-400 line-clamp-1" x-text="item.community.description || 'Cultural community'"></p>

                                {{-- Explainable Reason --}}
                                <div class="mt-2 p-2 rounded-lg bg-amber-500/5 border border-amber-500/10 text-[11px] text-stone-600 dark:text-stone-400 flex items-start gap-1.5">
                                    <i data-lucide="sparkles" class="w-3.5 h-3.5 text-amber-500 shrink-0 mt-0.5"></i>
                                    <span class="leading-tight" x-text="item.explanation"></span>
                                </div>
                            </div>
                        </div>

                        {{-- Dismiss Button --}}
                        <button @click="dismiss('community', item.community.id)" 
                            title="Dismiss recommendation"
                            class="text-stone-400 hover:text-stone-600 dark:hover:text-stone-200 p-1 rounded-lg hover:bg-stone-100 dark:hover:bg-stone-800 transition">
                            <i data-lucide="x" class="w-3.5 h-3.5"></i>
                        </button>
                    </div>
                </template>
            </div>
        </div>
    </template>
</div>

<script>
    function culturalDiscoveryWidget() {
        return {
            section: 'users',
            loading: true,
            optedOut: false,
            users: [],
            communities: [],

            async init() {
                await this.fetchRecommendations();
            },

            async fetchRecommendations() {
                this.loading = true;
                try {
                    const res = await fetch("{{ route('cultural-discovery.recommendations') }}");
                    const data = await res.json();
                    if (data.success) {
                        this.optedOut = data.opted_out;
                        this.users = data.users || [];
                        this.communities = data.communities || [];
                    }
                } catch (e) {
                    console.error('Error fetching cultural recommendations:', e);
                } finally {
                    this.loading = false;
                    setTimeout(() => lucide.createIcons(), 50);
                }
            },

            async dismiss(type, id) {
                // Optimistic removal
                if (type === 'user') {
                    this.users = this.users.filter(u => u.user.id !== id);
                } else if (type === 'community') {
                    this.communities = this.communities.filter(c => c.community.id !== id);
                }

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

            async toggleOptIn() {
                const targetState = this.optedOut; // if currently opted out, we want to enable
                try {
                    const res = await fetch("{{ route('cultural-discovery.toggle-opt-in') }}", {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': "{{ csrf_token() }}"
                        },
                        body: JSON.stringify({ enabled: targetState })
                    });
                    const data = await res.json();
                    if (data.success) {
                        this.optedOut = !targetState;
                        if (targetState) {
                            await this.fetchRecommendations();
                        }
                    }
                } catch (e) {
                    console.error('Error toggling opt-in:', e);
                }
            }
        };
    }
</script>
@endauth
