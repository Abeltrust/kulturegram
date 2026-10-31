{{-- resources/views/live-streaming/show.blade.php --}}
@extends('layouts.app')

@section('content')
    <div id="live-stream-root" class="h-[calc(100vh-4rem)] bg-stone-950 flex flex-col md:flex-row overflow-hidden relative"
        x-data="chatComponent()">

        {{-- Main Video Area --}}
        <div class="flex-1 flex flex-col relative transition-all duration-300 h-[60vh] md:h-full">

            {{-- Video Player / Camera Feed --}}
            <div class="w-full h-full bg-black relative flex items-center justify-center overflow-hidden">
                @if(auth()->id() === $stream->user_id && $stream->is_live)
                    <video id="host-video" autoplay playsinline muted class="w-full h-full object-cover"></video>
                @else
                    <img src="{{ $stream->thumbnail ?? 'https://images.unsplash.com/photo-1514362545857-3bc16c4c7d1b?auto=format&fit=crop&q=80&w=1200' }}"
                        class="w-full h-full object-cover opacity-80" alt="Video Feed">
                @endif

                {{-- Pulse Overlay Effect --}}
                <div
                    class="absolute inset-0 bg-gradient-to-t from-black via-transparent to-black/50 mix-blend-multiply pointer-events-none">
                </div>

                {{-- Top Controls Overlay --}}
                <div
                    class="absolute top-0 left-0 right-0 p-4 md:p-6 flex justify-between items-start bg-gradient-to-b from-black/80 to-transparent">
                    <div class="flex items-center gap-3">
                        <a href="{{ route('live-stream.index') }}"
                            class="w-10 h-10 rounded-full bg-white/10 hover:bg-white/20 backdrop-blur-md flex items-center justify-center text-white transition">
                            <i data-lucide="arrow-left" class="w-5 h-5"></i>
                        </a>
                        <div>
                            <h1 class="text-white font-bold text-lg md:text-sm flex items-center gap-2">
                                {{ $stream->title }}
                                @if(auth()->id() === $stream->user_id && $stream->is_live)
                                    <span
                                        class="bg-red-600 text-white text-[10px] uppercase font-black px-2 py-0.5 rounded shadow-lg flex items-center gap-1">
                                        <span class="w-1 h-1 bg-white rounded-full animate-pulse"></span>
                                        You're Live
                                    </span>
                                @elseif($stream->is_live)
                                    <span
                                        class="bg-red-600 text-white text-[10px] uppercase font-black px-2 py-0.5 rounded shadow-lg flex items-center gap-1">
                                        <span class="w-1 h-1 bg-white rounded-full animate-pulse"></span>
                                        Live
                                    </span>
                                @else
                                    <span
                                        class="bg-stone-600 text-white text-[10px] uppercase font-black px-2 py-0.5 rounded shadow-lg">Ended</span>
                                @endif
                            </h1>
                            <a href="{{ route('profile.user', $stream->host) }}" class="text-stone-300 text-sm flex items-center gap-2 hover:text-amber-500 transition-colors group">
                                <img src="{{ $stream->host->profile_photo_url }}"
                                    class="w-5 h-5 rounded-full border border-white/20 group-hover:border-amber-500/50">
                                {{ $stream->host->name }}
                                <i data-lucide="external-link" class="w-3 h-3 opacity-0 group-hover:opacity-100 transition-opacity"></i>
                            </a>
                        </div>
                    </div>

                    <div class="flex items-center gap-2">
                        <div
                            class="bg-black/40 backdrop-blur-md px-3 py-1.5 rounded-lg flex items-center gap-2 text-white text-xs font-semibold border border-white/10">
                            <i data-lucide="eye" class="w-4 h-4 text-stone-300"></i> {{ $stream->viewers_count }}
                        </div>
                        <button @click="showChat = !showChat"
                            class="md:hidden w-10 h-10 rounded-full bg-white/10 hover:bg-white/20 backdrop-blur-md flex items-center justify-center text-white transition">
                            <i data-lucide="message-square" class="w-5 h-5"></i>
                        </button>
                    </div>
                </div>

                {{-- Bottom Controls Overlay (Host only) --}}
                @if(auth()->id() === $stream->user_id && $stream->is_live)
                    <div class="absolute bottom-6 left-1/2 transform -translate-x-1/2 flex items-center gap-4 bg-black/60 backdrop-blur-md px-6 py-3 rounded-2xl border border-white/10"
                        x-data="{ micOn: true, camOn: true }">
                        <button @click="toggleMicGlobal(); micOn = !micOn"
                            :class="micOn ? 'bg-white/10 hover:bg-white/20' : 'bg-red-500/80 hover:bg-red-600/80'"
                            class="w-12 h-12 rounded-full text-white flex items-center justify-center transition"
                            title="Toggle Microphone">
                            <i :data-lucide="micOn ? 'mic' : 'mic-off'" class="w-5 h-5"></i>
                        </button>
                        <button @click="toggleCamGlobal(); camOn = !camOn"
                            :class="camOn ? 'bg-white/10 hover:bg-white/20' : 'bg-red-500/80 hover:bg-red-600/80'"
                            class="w-12 h-12 rounded-full text-white flex items-center justify-center transition"
                            title="Toggle Camera">
                            <i :data-lucide="camOn ? 'video' : 'video-off'" class="w-5 h-5"></i>
                        </button>
                        <form action="{{ route('live-stream.end', $stream) }}" method="POST" class="inline"
                            id="end-stream-form">
                            @csrf
                            <button type="submit" @click="stopStreamGlobal()"
                                class="h-12 px-6 rounded-full bg-red-600 hover:bg-red-700 text-white font-bold text-sm tracking-wide transition flex items-center gap-2">
                                <i data-lucide="phone-off" class="w-4 h-4"></i> End Stream
                            </button>
                        </form>
                    </div>
                @endif
            </div>
        </div>

        {{-- Chat & AI Moderation Sidebar --}}
        <div :class="showChat ? 'translate-y-0 opacity-100' : 'translate-y-full opacity-0 absolute pointer-events-none'"
            class="w-full md:w-84 lg:w-96 bg-stone-900 border-t md:border-t-0 md:border-l border-stone-800 flex flex-col h-[40vh] md:h-full md:translate-y-0 md:opacity-100 md:static transition-all duration-300 z-10 shrink-0">

            {{-- Sidebar Header & Tabs --}}
            <div class="p-3 border-b border-stone-800 flex items-center justify-between bg-stone-900/95 backdrop-blur-sm z-10">
                <div class="flex items-center gap-1">
                    <button @click="activeTab = 'chat'" 
                        :class="activeTab === 'chat' ? 'bg-stone-800 text-amber-500 font-bold' : 'text-stone-400 hover:text-stone-200'"
                        class="px-2.5 py-1.5 rounded-lg text-xs transition flex items-center gap-1.5">
                        <i data-lucide="message-circle" class="w-4 h-4"></i>
                        <span>Live Chat</span>
                    </button>

                    <button @click="activeTab = 'catchup'; fetchCatchUp()" 
                        :class="activeTab === 'catchup' ? 'bg-stone-800 text-amber-500 font-bold' : 'text-stone-400 hover:text-stone-200'"
                        class="px-2.5 py-1.5 rounded-lg text-xs transition flex items-center gap-1.5">
                        <i data-lucide="sparkles" class="w-4 h-4 text-amber-400"></i>
                        <span>Catch-Up</span>
                    </button>

                    @if(auth()->id() === $stream->user_id || auth()->user()?->is_admin)
                    <button @click="activeTab = 'moderation'; fetchFlagged()" 
                        :class="activeTab === 'moderation' ? 'bg-stone-800 text-amber-500 font-bold' : 'text-stone-400 hover:text-stone-200'"
                        class="px-2.5 py-1.5 rounded-lg text-xs transition flex items-center gap-1.5 relative">
                        <i data-lucide="shield-alert" class="w-4 h-4 text-amber-400"></i>
                        <span>AI Monitor</span>
                        <template x-if="flaggedCount > 0">
                            <span class="px-1.5 py-0.2 rounded-full text-[10px] font-black bg-rose-500 text-white animate-pulse" x-text="flaggedCount"></span>
                        </template>
                    </button>
                    @endif
                </div>

                <div class="flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse" title="AI Stream Guard Active"></span>
                    <button @click="showChat = false" class="md:hidden text-stone-400 hover:text-white transition">
                        <i data-lucide="x" class="w-5 h-5"></i>
                    </button>
                </div>
            </div>

            {{-- TAB 1: LIVE CHAT VIEW --}}
            <div x-show="activeTab === 'chat'" class="flex-1 flex flex-col overflow-hidden">
                <div id="chat-messages" class="flex-1 overflow-y-auto p-4 space-y-4 scrollbar-thin scrollbar-thumb-stone-700">
                    <div class="bg-black/20 rounded-lg p-3 text-center border border-stone-800 mb-4">
                        <p class="text-xs text-stone-500">Welcome to {{ $stream->title }}! AI Stream Guard is actively protecting cultural conversations.</p>
                    </div>

                    <div class="text-xs text-stone-400 flex items-center gap-1.5 px-1 py-1">
                        <i data-lucide="sparkles" class="w-3.5 h-3.5 text-amber-500"></i>
                        <span>Topic: <strong class="text-stone-200">{{ $stream->category ?? 'Cultural Heritage' }}</strong></span>
                    </div>
                </div>

                {{-- Reaction Floating Container --}}
                <div id="reaction-container"
                    class="absolute bottom-24 right-4 w-20 h-64 pointer-events-none z-20 overflow-hidden"></div>

                {{-- Reply Preview --}}
                <div x-show="replyingTo" x-cloak
                    class="px-4 py-2 bg-stone-800 border-t border-stone-700 flex items-center justify-between animate-in slide-in-from-bottom-2 duration-200">
                    <div class="flex flex-col">
                        <span class="text-[10px] font-black text-amber-500 uppercase tracking-widest">Replying to</span>
                        <span class="text-xs text-stone-300 truncate max-w-[200px]" x-text="replyAuthor"></span>
                    </div>
                    <button @click="cancelReply" class="text-stone-500 hover:text-white transition">
                        <i data-lucide="x" class="w-4 h-4"></i>
                    </button>
                </div>

                {{-- Input Bar --}}
                <div class="p-3 bg-stone-900 border-t border-stone-800 mt-auto flex items-center gap-2">
                    @if($stream->is_live)
                        <form @submit.prevent="sendMessage" class="relative flex-1">
                            <input type="text" id="chat-input" placeholder="Share your cultural thoughts..." x-model="messageBody"
                                class="w-full bg-stone-950 border border-stone-800 text-stone-100 rounded-full pl-4 pr-12 py-2.5 text-sm focus:outline-none focus:border-amber-500 transition">
                            <button type="submit" :disabled="sending"
                                class="absolute right-2 top-1/2 transform -translate-y-1/2 w-8 h-8 flex items-center justify-center bg-amber-600 hover:bg-amber-500 text-white rounded-full transition disabled:opacity-50">
                                <i data-lucide="send" class="w-4 h-4 ml-0.5"></i>
                            </button>
                        </form>

                        {{-- Reaction Button --}}
                        <button @click="sendReaction"
                            class="w-10 h-10 rounded-full bg-stone-800 hover:bg-stone-700 flex items-center justify-center text-amber-500 border border-stone-700 transition active:scale-90"
                            title="Send Love">
                            <i data-lucide="heart" class="w-5 h-5 fill-current"></i>
                        </button>
                    @else
                        <div class="text-center py-2 text-stone-500 text-sm flex-1">Stream has ended.</div>
                    @endif
                </div>
            </div>

            {{-- TAB 2: AI MODERATION MONITOR (HOST & ADMIN) --}}
            @if(auth()->id() === $stream->user_id || auth()->user()?->is_admin)
            <div x-show="activeTab === 'moderation'" x-cloak class="flex-1 flex flex-col overflow-hidden bg-stone-950/80">
                {{-- Stream Context Card --}}
                <div class="p-3 bg-stone-900 border-b border-stone-800 text-xs">
                    <div class="flex items-center justify-between mb-1.5">
                        <span class="font-bold text-amber-500 flex items-center gap-1">
                            <i data-lucide="shield-check" class="w-3.5 h-3.5"></i>
                            AI Moderation Assistant
                        </span>
                        <span class="text-[10px] text-stone-400">Meta muse-spark-1.3</span>
                    </div>
                    <p class="text-stone-400 line-clamp-2">{{ $stream->description ?: 'Cultural stream on Kulturegram' }}</p>

                    {{-- Stats Bar --}}
                    <div class="grid grid-cols-3 gap-2 mt-3 text-center">
                        <div class="bg-stone-950 p-1.5 rounded-lg border border-stone-800">
                            <div class="text-[10px] text-stone-500">Total</div>
                            <div class="text-xs font-bold text-stone-200" x-text="stats.total_messages || 0">0</div>
                        </div>
                        <div class="bg-stone-950 p-1.5 rounded-lg border border-stone-800">
                            <div class="text-[10px] text-stone-500">Flagged</div>
                            <div class="text-xs font-bold text-rose-400" x-text="flaggedCount">0</div>
                        </div>
                        <div class="bg-stone-950 p-1.5 rounded-lg border border-stone-800">
                            <div class="text-[10px] text-stone-500">Clean Rate</div>
                            <div class="text-xs font-bold text-emerald-400" x-text="stats.total_messages ? Math.round((1 - (flaggedCount / stats.total_messages)) * 100) + '%' : '100%'">100%</div>
                        </div>
                    </div>
                </div>

                {{-- Flagged Messages Queue --}}
                <div class="flex-1 overflow-y-auto p-3 space-y-3 scrollbar-thin scrollbar-thumb-stone-700">
                    <div class="text-[11px] font-bold text-stone-400 flex items-center justify-between">
                        <span>Flagged Messages Review (<span x-text="flaggedMessages.length"></span>)</span>
                        <button @click="fetchFlagged()" class="text-amber-500 hover:underline flex items-center gap-1">
                            <i data-lucide="refresh-cw" class="w-3 h-3"></i> Refresh
                        </button>
                    </div>

                    <template x-if="flaggedMessages.length === 0">
                        <div class="text-center py-8 text-stone-500 text-xs">
                            <i data-lucide="check-circle" class="w-8 h-8 mx-auto text-emerald-500/50 mb-2"></i>
                            <p>All clean! No active flags in this stream.</p>
                            <p class="text-[10px] text-stone-600 mt-1">Disagreements and cultural debates are protected.</p>
                        </div>
                    </template>

                    <template x-for="item in flaggedMessages" :key="item.id">
                        <div class="bg-stone-900 border border-stone-800 rounded-xl p-3 space-y-2">
                            <div class="flex items-center justify-between text-xs">
                                <span class="font-bold text-stone-200" x-text="item.user?.name || 'Viewer'"></span>
                                <span class="px-2 py-0.5 rounded text-[9px] font-black uppercase tracking-wider bg-rose-500/20 text-rose-400 border border-rose-500/30" x-text="item.flag_category || 'Flagged'"></span>
                            </div>

                            <p class="text-xs text-stone-300 bg-black/40 p-2 rounded-lg font-mono border border-stone-800" x-text="item.message"></p>

                            {{-- AI Explanation & Rule --}}
                            <div class="text-[10px] text-stone-400 space-y-0.5 bg-amber-500/5 p-2 rounded-lg border border-amber-500/10">
                                <div><strong class="text-amber-400">Reason:</strong> <span x-text="item.flag_reason || 'Potential policy violation'"></span></div>
                                <div class="text-stone-500"><strong class="text-stone-400">Rule:</strong> <span x-text="item.flag_rule || 'Community Guidelines'"></span></div>
                            </div>

                            {{-- Actions --}}
                            <div class="flex items-center gap-2 pt-1">
                                <button @click="dismissFlag(item.id)" 
                                    class="flex-1 py-1.5 rounded-lg bg-stone-800 hover:bg-stone-700 text-stone-300 font-bold text-[11px] transition flex items-center justify-center gap-1">
                                    <i data-lucide="check" class="w-3.5 h-3.5 text-emerald-400"></i> Dismiss Flag
                                </button>
                                <button @click="removeMessage(item.id)" 
                                    class="flex-1 py-1.5 rounded-lg bg-rose-950/40 hover:bg-rose-900/60 text-rose-300 border border-rose-800/40 font-bold text-[11px] transition flex items-center justify-center gap-1">
                                    <i data-lucide="trash-2" class="w-3.5 h-3.5 text-rose-400"></i> Remove
                                </button>
                            </div>
                        </div>
                    </template>
                </div>
            {{-- TAB 3: AI CATCH-UP ASSISTANT & DISCUSSION SUMMARY --}}
            <div x-show="activeTab === 'catchup'" x-cloak class="flex-1 flex flex-col overflow-hidden bg-stone-950/90">
                <div class="p-3 bg-stone-900 border-b border-stone-800 flex items-center justify-between">
                    <div>
                        <div class="text-xs font-bold text-amber-500 flex items-center gap-1.5">
                            <i data-lucide="sparkles" class="w-3.5 h-3.5 text-amber-400"></i>
                            <span>Catch-Up Assistant</span>
                        </div>
                        <p class="text-[10px] text-stone-400">Contextual summary for late joiners & community members</p>
                    </div>
                    <button @click="refreshSummary()" :disabled="loadingCatchUp"
                        class="px-2.5 py-1 rounded bg-stone-800 hover:bg-stone-700 text-stone-300 text-[11px] font-semibold border border-stone-700 transition flex items-center gap-1">
                        <i data-lucide="refresh-cw" class="w-3 h-3" :class="loadingCatchUp ? 'animate-spin' : ''"></i>
                        <span>Update</span>
                    </button>
                </div>

                <div class="flex-1 overflow-y-auto p-3.5 space-y-4 scrollbar-thin scrollbar-thumb-stone-700">
                    {{-- Loading State --}}
                    <div x-show="loadingCatchUp" class="text-center py-8 text-stone-400 text-xs">
                        <div class="w-6 h-6 border-2 border-amber-500 border-t-transparent rounded-full animate-spin mx-auto mb-2"></div>
                        <p>Analyzing discussion & synthesizing cultural context...</p>
                    </div>

                    <div x-show="!loadingCatchUp && dossier">
                        {{-- AI Disclosure Badge --}}
                        <div class="p-2.5 rounded-lg bg-amber-500/10 border border-amber-500/20 text-[11px] text-amber-300 mb-3 flex items-start gap-2">
                            <i data-lucide="info" class="w-4 h-4 shrink-0 text-amber-400 mt-0.5"></i>
                            <div>
                                <span class="font-bold">Transparent Attribution:</span>
                                <span class="text-stone-300">Participant quotes are attributed verbatim. Synthesis is generated by Meta Llama AI.</span>
                            </div>
                        </div>

                        {{-- Current Discussion Direction --}}
                        <div class="bg-stone-900 border border-stone-800 rounded-xl p-3 mb-3">
                            <div class="text-[10px] font-bold text-stone-400 uppercase tracking-wider mb-1">Current Direction</div>
                            <p class="text-xs text-stone-200" x-text="dossier?.summary?.current_direction || 'Discussion ongoing.'"></p>
                        </div>

                        {{-- AI Synthesis Overview --}}
                        <div class="bg-stone-900 border border-stone-800 rounded-xl p-3 mb-3">
                            <div class="text-[10px] font-bold text-amber-400 uppercase tracking-wider mb-1 flex items-center gap-1">
                                <i data-lucide="cpu" class="w-3 h-3"></i> AI-Generated Summary
                            </div>
                            <p class="text-xs text-stone-300 leading-relaxed" x-text="dossier?.summary?.overview || 'Conversation in progress.'"></p>
                        </div>

                        {{-- Verified Participant Statements --}}
                        <template x-if="dossier?.summary?.participant_highlights && dossier.summary.participant_highlights.length > 0">
                            <div class="mb-3">
                                <div class="text-[10px] font-bold text-stone-400 uppercase tracking-wider mb-2 flex items-center gap-1">
                                    <i data-lucide="message-square" class="w-3 h-3 text-stone-400"></i>
                                    Key Participant Statements
                                </div>
                                <div class="space-y-2">
                                    <template x-for="(stmt, idx) in dossier.summary.participant_highlights" :key="idx">
                                        <div class="p-2.5 rounded-lg bg-stone-900 border border-stone-800 text-xs">
                                            <div class="font-bold text-amber-500 text-[11px] mb-0.5" x-text="stmt.speaker"></div>
                                            <p class="text-stone-300 italic" x-text="'“' + stmt.statement + '”'"></p>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </template>

                        {{-- Unanswered Cultural Questions --}}
                        <template x-if="dossier?.unanswered_questions && dossier.unanswered_questions.length > 0">
                            <div class="mb-3">
                                <div class="text-[10px] font-bold text-amber-400 uppercase tracking-wider mb-2 flex items-center gap-1">
                                    <i data-lucide="help-circle" class="w-3 h-3"></i>
                                    Unanswered Cultural Inquiries
                                </div>
                                <div class="space-y-1.5">
                                    <template x-for="(q, idx) in dossier.unanswered_questions" :key="idx">
                                        <div class="p-2.5 rounded-lg bg-amber-950/20 border border-amber-800/30 text-xs text-amber-200">
                                            <span class="font-bold text-amber-400">?</span> <span x-text="q"></span>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </template>

                        {{-- Relevant Cultural Heritage Records (Knowledge Graph) --}}
                        <template x-if="dossier?.cultural_references && dossier.cultural_references.length > 0">
                            <div class="mb-3">
                                <div class="text-[10px] font-bold text-stone-400 uppercase tracking-wider mb-2 flex items-center gap-1">
                                    <i data-lucide="book-open" class="w-3 h-3 text-stone-400"></i>
                                    Related Kulturegram Records
                                </div>
                                <div class="space-y-2">
                                    <template x-for="ref in dossier.cultural_references" :key="ref.id">
                                        <a :href="ref.url" target="_blank"
                                            class="block p-2.5 rounded-lg bg-stone-900 hover:bg-stone-850 border border-stone-800 hover:border-amber-500/40 transition group">
                                            <div class="font-bold text-stone-200 text-xs group-hover:text-amber-400 flex items-center justify-between">
                                                <span x-text="ref.name"></span>
                                                <i data-lucide="external-link" class="w-3 h-3 opacity-60"></i>
                                            </div>
                                            <div class="text-[10px] text-stone-400 mt-0.5" x-text="ref.category + ' • ' + ref.region"></div>
                                            <div class="text-[10px] text-amber-500/80 mt-1" x-text="ref.relevance_explanation"></div>
                                        </a>
                                    </template>
                                </div>
                            </div>
                        </template>

                        {{-- Discussion Continuity & Heritage Archival Extraction --}}
                        @if(auth()->id() === $stream->user_id || auth()->user()?->is_admin)
                        <div class="p-3 rounded-xl bg-gradient-to-r from-amber-950/40 to-stone-900 border border-amber-500/30 mt-4">
                            <div class="text-xs font-bold text-amber-400 flex items-center gap-1.5 mb-1">
                                <i data-lucide="archive" class="w-3.5 h-3.5"></i>
                                Collective Cultural Preservation
                            </div>
                            <p class="text-[11px] text-stone-300 mb-3">Convert valuable knowledge and elder testimonies from this livestream into a permanent cultural archive draft for community review.</p>
                            <button @click="extractDraft()" :disabled="extractingDraft"
                                class="w-full py-2 px-3 rounded-lg bg-amber-600 hover:bg-amber-500 text-white font-bold text-xs transition flex items-center justify-center gap-1.5 disabled:opacity-50">
                                <i data-lucide="file-plus" class="w-3.5 h-3.5" :class="extractingDraft ? 'animate-pulse' : ''"></i>
                                <span x-text="extractingDraft ? 'Extracting Heritage Draft...' : 'Extract Cultural Record Draft'"></span>
                            </button>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>
    </div>

    <script src="https://unpkg.com/lucide@latest"></script>
    <script>
        let localStream = null;

        async function initCamera() {
            @if(auth()->id() === $stream->user_id && $stream->is_live)
                try {
                    localStream = await navigator.mediaDevices.getUserMedia({
                        video: { facingMode: 'user', width: { ideal: 1280 }, height: { ideal: 720 } },
                        audio: true
                    });
                    const videoElement = document.getElementById('host-video');
                    if (videoElement) {
                        videoElement.srcObject = localStream;
                    }
                } catch (err) {
                    console.error("Error accessing media devices.", err);
                    alert("Could not access your camera or microphone. Please check permissions.");
                }
            @endif
            lucide.createIcons();
        }

        // Global camera controls for host
        window.toggleMicGlobal = () => {
            if (localStream) {
                const audioTrack = localStream.getAudioTracks()[0];
                if (audioTrack) {
                    audioTrack.enabled = !audioTrack.enabled;
                    lucide.createIcons();
                }
            }
        };

        window.toggleCamGlobal = () => {
            if (localStream) {
                const videoTrack = localStream.getVideoTracks()[0];
                if (videoTrack) {
                    videoTrack.enabled = !videoTrack.enabled;
                    lucide.createIcons();
                }
            }
        };

        window.stopStreamGlobal = () => {
            if (localStream) {
                localStream.getTracks().forEach(track => track.stop());
            }
        };

        document.addEventListener('alpine:init', () => {
            Alpine.data('chatComponent', () => ({
                showChat: true,
                activeTab: 'chat',
                replyingTo: null,
                replyAuthor: '',
                messageBody: '',
                sending: false,
                lastMessageId: 0,
                flaggedMessages: [],
                flaggedCount: 0,
                stats: { total_messages: 0, flagged_count: 0 },
                dossier: null,
                loadingCatchUp: false,
                extractingDraft: false,

                init() {
                    initCamera();
                    
                    // Immediately end stream if host leaves
                    @if(auth()->id() === $stream->user_id && $stream->is_live)
                    window.addEventListener('visibilitychange', () => {
                        if (document.visibilityState === 'hidden') {
                            this.endStreamBeacon();
                        }
                    });
                    window.addEventListener('pagehide', () => this.endStreamBeacon());
                    @endif

                    // Expose vibe function globally for onclick inside dynamic HTML
                    window.vibeMessage = (btn) => this.vibeMessage(btn);
                    window.initiateReplyFromGlobal = (author) => this.initiateReply(author);

                    // Fetch existing messages and start polling with tab-visibility awareness
                    this.fetchMessages();
                    setInterval(() => {
                        if (!document.hidden && !this.streamEnded) {
                            this.fetchMessages();
                        }
                    }, 3000);

                    @if(auth()->id() === $stream->user_id || auth()->user()?->is_admin)
                    this.fetchFlagged();
                    setInterval(() => {
                        if (!document.hidden && !this.streamEnded) {
                            this.fetchFlagged();
                        }
                    }, 5000);
                    @endif

                    // Resume immediately when returning to tab
                    document.addEventListener('visibilitychange', () => {
                        if (!document.hidden && !this.streamEnded) {
                            this.fetchMessages();
                            @if(auth()->id() === $stream->user_id || auth()->user()?->is_admin)
                            this.fetchFlagged();
                            @endif
                        }
                    });
                },

                endStreamBeacon() {
                    const url = "{{ route('live-stream.ajax-end', $stream) }}";
                    const data = new FormData();
                    data.append('_token', "{{ csrf_token() }}");
                    navigator.sendBeacon(url, data);
                    stopStreamGlobal();
                },

                async fetchMessages() {
                    try {
                        const url = "{{ route('live-stream.messages.get', $stream) }}?since_id=" + this.lastMessageId;
                        const res = await fetch(url);
                        const data = await res.json();
                        if (data.success && data.messages.length > 0) {
                            const chatContainer = document.getElementById('chat-messages');
                            data.messages.forEach(msg => {
                                // Avoid re-rendering own message if already appended optimistically
                                if (document.getElementById('msg-' + msg.id)) return;

                                const msgEl = document.createElement('div');
                                msgEl.id = 'msg-' + msg.id;
                                msgEl.className = 'group flex flex-col mb-4 transition-all duration-300';
                                
                                const timeStr = new Date(msg.created_at).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
                                const authorName = msg.user ? msg.user.name : 'Participant';
                                const isHost = msg.user_id === {{ $stream->user_id }};

                                msgEl.innerHTML = `
                                    <div class="flex items-start gap-2">
                                        <div class="flex-1 bg-white/5 rounded-2xl rounded-tl-none p-3 border border-white/5">
                                            <div class="flex items-center justify-between mb-1">
                                                <div class="flex items-center gap-1.5">
                                                    <span class="text-amber-500 font-bold text-xs">${authorName}</span>
                                                    ${isHost ? '<span class="px-1.5 py-0.2 rounded text-[9px] font-black uppercase bg-amber-500/20 text-amber-400 border border-amber-500/30">Host</span>' : ''}
                                                </div>
                                                <span class="text-[9px] text-stone-500">${timeStr}</span>
                                            </div>
                                            <p class="text-stone-200 text-sm whitespace-pre-wrap">${msg.message}</p>
                                        </div>

                                        <div class="flex flex-col gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                                            <button onclick="vibeMessage(this)" class="p-1.5 rounded-lg hover:bg-white/10 text-stone-500 hover:text-red-500 transition active:scale-90" title="Vibe">
                                                <i data-lucide="zap" class="w-3.5 h-3.5"></i>
                                            </button>
                                            <button onclick="initiateReplyFromGlobal('${authorName}')" class="p-1.5 rounded-lg hover:bg-white/10 text-stone-500 hover:text-amber-500 transition" title="Reply">
                                                <i data-lucide="reply" class="w-3.5 h-3.5"></i>
                                            </button>
                                        </div>
                                    </div>
                                `;
                                chatContainer.appendChild(msgEl);
                                this.lastMessageId = Math.max(this.lastMessageId, msg.id);
                            });
                            chatContainer.scrollTop = chatContainer.scrollHeight;
                            setTimeout(() => lucide.createIcons(), 50);
                        }
                    } catch (e) {
                        // Silent catch for live poll resilience
                    }
                },

                async sendMessage() {
                    if (!this.messageBody.trim() || this.sending) return;

                    const messageText = this.messageBody.trim();
                    const isReply = this.replyingTo !== null;
                    const targetAuthor = this.replyAuthor;

                    // Optimistic UI Append
                    const chatContainer = document.getElementById('chat-messages');
                    const tempId = 'temp-' + Date.now();
                    const messageDiv = document.createElement('div');
                    messageDiv.id = tempId;
                    messageDiv.className = 'group flex flex-col mb-4 transition-all duration-300';

                    let replyHTML = '';
                    if (isReply) {
                        replyHTML = `
                            <div class="mb-1 text-[10px] text-stone-500 flex items-center gap-1">
                                <i data-lucide="corner-down-right" class="w-2.5 h-2.5"></i>
                                Replying to ${targetAuthor}
                            </div>
                        `;
                    }

                    messageDiv.innerHTML = `
                        ${replyHTML}
                        <div class="flex items-start gap-2">
                            <div class="flex-1 bg-amber-500/10 rounded-2xl rounded-tl-none p-3 border border-amber-500/20">
                                <div class="flex items-center justify-between mb-1">
                                    <span class="text-amber-500 font-bold text-xs">{{ auth()->user()->name }}</span>
                                    <span class="text-[9px] text-stone-500">${new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })}</span>
                                </div>
                                <p class="text-stone-200 text-sm whitespace-pre-wrap">${messageText}</p>
                            </div>
                        </div>
                    `;

                    chatContainer.appendChild(messageDiv);
                    chatContainer.scrollTop = chatContainer.scrollHeight;

                    // Reset Input
                    this.messageBody = '';
                    this.replyingTo = null;
                    this.replyAuthor = '';
                    this.sending = true;

                    try {
                        const response = await fetch("{{ route('live-stream.messages.post', $stream) }}", {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': "{{ csrf_token() }}"
                            },
                            body: JSON.stringify({ message: messageText })
                        });
                        const res = await response.json();
                        if (res.success && res.message) {
                            messageDiv.id = 'msg-' + res.message.id;
                            this.lastMessageId = Math.max(this.lastMessageId, res.message.id);
                            
                            // If flagged and host/mod, refresh moderation view
                            if (res.moderation?.flagged) {
                                this.fetchFlagged();
                            }
                        }
                    } catch (err) {
                        console.error('Failed to post message:', err);
                    } finally {
                        this.sending = false;
                        setTimeout(() => lucide.createIcons(), 50);
                    }
                },

                async fetchFlagged() {
                    try {
                        const res = await fetch("{{ route('live-stream.messages.flagged', $stream) }}");
                        const data = await res.json();
                        if (data.success) {
                            this.flaggedMessages = data.flagged_messages || [];
                            this.flaggedCount = this.flaggedMessages.length;
                            this.stats = data.stats || { total_messages: 0, flagged_count: 0 };
                            setTimeout(() => lucide.createIcons(), 50);
                        }
                    } catch (e) {
                        // ignore
                    }
                },

                async dismissFlag(messageId) {
                    try {
                        const res = await fetch(`/live-stream/{{ $stream->id }}/messages/${messageId}/dismiss`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': "{{ csrf_token() }}"
                            },
                            body: JSON.stringify({ notes: 'Host dismissed AI flag as respectful discussion' })
                        });
                        const data = await res.json();
                        if (data.success) {
                            this.flaggedMessages = this.flaggedMessages.filter(m => m.id !== messageId);
                            this.flaggedCount = this.flaggedMessages.length;
                        }
                    } catch (e) {
                        console.error(e);
                    }
                },

                async removeMessage(messageId) {
                    if (!confirm('Are you sure you want to remove this message from chat?')) return;
                    try {
                        const res = await fetch(`/live-stream/{{ $stream->id }}/messages/${messageId}/remove`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': "{{ csrf_token() }}"
                            },
                            body: JSON.stringify({ reason: 'Removed by host for policy compliance' })
                        });
                        const data = await res.json();
                        if (data.success) {
                            this.flaggedMessages = this.flaggedMessages.filter(m => m.id !== messageId);
                            this.flaggedCount = this.flaggedMessages.length;
                            // Also remove from DOM if visible
                            const domMsg = document.getElementById('msg-' + messageId);
                            if (domMsg) domMsg.remove();
                        }
                    } catch (e) {
                        console.error(e);
                    }
                },

                initiateReply(author) {
                    this.replyAuthor = author;
                    this.replyingTo = Date.now();
                    document.getElementById('chat-input').focus();
                },

                cancelReply() {
                    this.replyingTo = null;
                    this.replyAuthor = '';
                },

                vibeMessage(btn) {
                    const icon = btn.querySelector('i, svg');
                    icon.classList.toggle('text-red-500');
                    if (icon.classList.contains('text-red-500')) {
                        icon.classList.add('fill-red-500');
                    } else {
                        icon.classList.remove('fill-red-500');
                    }

                    // Floating effect
                    const rect = btn.getBoundingClientRect();
                    const vibeHeart = document.createElement('div');
                    vibeHeart.className = 'fixed pointer-events-none transition-all duration-700 ease-out z-[100]';
                    vibeHeart.style.left = `${rect.left + rect.width / 2}px`;
                    vibeHeart.style.top = `${rect.top}px`;
                    vibeHeart.innerHTML = '⚡';
                    vibeHeart.style.fontSize = '20px';
                    document.body.appendChild(vibeHeart);

                    setTimeout(() => {
                        vibeHeart.style.opacity = '0';
                        vibeHeart.style.transform = 'translateY(-40px) scale(1.5)';
                    }, 50);

                    setTimeout(() => vibeHeart.remove(), 700);
                },

                sendReaction() {
                    const container = document.getElementById('reaction-container');
                    const heart = document.createElement('div');
                    const size = Math.random() * 20 + 20;
                    const left = Math.random() * 100;

                    heart.className = 'absolute bottom-0 text-red-500 transition-all duration-1000 ease-out pointer-events-none animate-bounce';
                    heart.style.left = `${left}%`;
                    heart.style.fontSize = `${size}px`;

                    heart.innerHTML = '<i data-lucide="heart" class="fill-current"></i>';
                    setTimeout(() => lucide.createIcons(), 5);

                    container.appendChild(heart);

                    setTimeout(() => {
                        heart.style.bottom = '100%';
                        heart.style.opacity = '0';
                        heart.style.transform = `scale(${Math.random() * 2 + 1}) rotate(${Math.random() * 360}deg)`;
                    }, 50);

                    setTimeout(() => heart.remove(), 1000);
                },

                async fetchCatchUp() {
                    this.loadingCatchUp = true;
                    try {
                        const res = await fetch("{{ route('live-stream.catch-up', $stream) }}");
                        const data = await res.json();
                        if (data.success) {
                            this.dossier = data;
                        }
                    } catch (e) {
                        console.error('Catch-up error:', e);
                    } finally {
                        this.loadingCatchUp = false;
                        setTimeout(() => lucide.createIcons(), 50);
                    }
                },

                async refreshSummary() {
                    this.loadingCatchUp = true;
                    try {
                        const res = await fetch("{{ route('live-stream.generate-summary', $stream) }}", {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': "{{ csrf_token() }}"
                            },
                            body: JSON.stringify({ force: true })
                        });
                        const data = await res.json();
                        if (data.success) {
                            this.dossier = data;
                        }
                    } catch (e) {
                        console.error('Summary error:', e);
                    } finally {
                        this.loadingCatchUp = false;
                        setTimeout(() => lucide.createIcons(), 50);
                    }
                },

                async extractDraft() {
                    if (!confirm('Convert this livestream conversation into a cultural heritage archive draft for community review?')) return;
                    this.extractingDraft = true;
                    try {
                        const res = await fetch("{{ route('live-stream.extract-draft-record', $stream) }}", {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': "{{ csrf_token() }}"
                            }
                        });
                        const data = await res.json();
                        if (data.success && data.redirect_url) {
                            window.location.href = data.redirect_url;
                        } else {
                            alert(data.error || 'Failed to extract draft.');
                        }
                    } catch (e) {
                        console.error(e);
                        alert('An error occurred while preparing the cultural record draft.');
                    } finally {
                        this.extractingDraft = false;
                    }
                }
            }));
        });
    </script>
@endsection