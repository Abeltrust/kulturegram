@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-stone-950 text-stone-100 py-8 px-4 sm:px-6 lg:px-8">
    <div class="max-w-5xl mx-auto space-y-6">

        {{-- Top Navigation & Status Bar --}}
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 border-b border-stone-800 pb-5">
            <div class="flex items-center gap-3">
                <a href="{{ route('cultural-hub.drafts.index') }}" 
                   class="w-9 h-9 rounded-xl bg-stone-900 hover:bg-stone-800 text-stone-300 border border-stone-800 flex items-center justify-center transition">
                    <i data-lucide="arrow-left" class="w-4 h-4"></i>
                </a>
                <div>
                    <div class="flex items-center gap-2">
                        <span class="text-xs font-bold text-amber-500 uppercase tracking-widest">Cultural Preservation Draft</span>
                        <span class="text-[10px] font-black uppercase px-2 py-0.5 rounded-full
                            @if($draft->status === 'approved') bg-emerald-500/20 text-emerald-400 border border-emerald-500/30
                            @elseif($draft->status === 'submitted_to_moderation') bg-amber-500/20 text-amber-400 border border-amber-500/30
                            @else bg-stone-800 text-stone-300 border border-stone-700 @endif">
                            {{ str_replace('_', ' ', ucfirst($draft->status)) }}
                        </span>
                    </div>
                    <h1 class="text-xl sm:text-2xl font-black text-stone-100 mt-0.5">{{ $draft->title }}</h1>
                </div>
            </div>

            <div class="flex items-center gap-2">
                @if($draft->status !== 'submitted_to_moderation' && $draft->status !== 'approved')
                    <form action="{{ route('cultural-hub.drafts.submit', $draft->id) }}" method="POST" onsubmit="return confirm('Submit this reviewed draft to the cultural archive curation queue?')">
                        @csrf
                        <button type="submit" class="px-4 py-2 rounded-xl bg-amber-600 hover:bg-amber-500 text-white text-xs font-bold transition flex items-center gap-1.5 shadow-lg shadow-amber-900/30">
                            <i data-lucide="send" class="w-3.5 h-3.5"></i>
                            <span>Submit to Moderation</span>
                        </button>
                    </form>
                @endif
                <form action="{{ route('cultural-hub.drafts.destroy', $draft->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to discard this draft?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="p-2 rounded-xl bg-stone-900 hover:bg-rose-950/60 text-stone-400 hover:text-rose-400 border border-stone-800 transition" title="Discard Draft">
                        <i data-lucide="trash-2" class="w-4 h-4"></i>
                    </button>
                </form>
            </div>
        </div>

        @if(session('success'))
            <div class="p-4 rounded-xl bg-emerald-950/60 border border-emerald-700/50 text-emerald-200 text-xs flex items-center gap-2">
                <i data-lucide="check-circle" class="w-4 h-4 text-emerald-400"></i>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        {{-- Verification Transparency Banner --}}
        <div class="p-4 rounded-2xl bg-amber-500/10 border border-amber-500/20 text-xs text-amber-300 flex items-start gap-3">
            <i data-lucide="info" class="w-5 h-5 shrink-0 text-amber-400 mt-0.5"></i>
            <div>
                <span class="font-bold">Strict Fact-Checking & Attribution:</span>
                <span class="text-stone-300">This draft was prepared from participant discussions and testimonies. AI assists with transcription and synthesis, but never fabricates historical facts. Please enrich or correct any names, ritual details, and nuances below.</span>
            </div>
        </div>

        {{-- Draft Edit Form --}}
        <form action="{{ route('cultural-hub.drafts.update', $draft->id) }}" method="POST" class="space-y-6">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

                {{-- Left 2 Columns: Main Content --}}
                <div class="lg:col-span-2 space-y-5">

                    {{-- Title & Metadata --}}
                    <div class="bg-stone-900/80 border border-stone-800 rounded-2xl p-5 space-y-4">
                        <div>
                            <label class="block text-xs font-bold text-stone-300 uppercase tracking-wider mb-1.5">Draft Archive Title</label>
                            <input type="text" name="title" value="{{ old('title', $draft->title) }}" required
                                class="w-full bg-stone-950 border border-stone-800 rounded-xl px-4 py-2.5 text-sm text-stone-100 focus:outline-none focus:border-amber-500">
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                            <div>
                                <label class="block text-xs font-bold text-stone-400 mb-1">Category</label>
                                <input type="text" name="category" value="{{ old('category', $draft->category) }}"
                                    class="w-full bg-stone-950 border border-stone-800 rounded-xl px-3 py-2 text-xs text-stone-100 focus:outline-none focus:border-amber-500">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-stone-400 mb-1">Region / Origin</label>
                                <input type="text" name="region" value="{{ old('region', $draft->region) }}"
                                    class="w-full bg-stone-950 border border-stone-800 rounded-xl px-3 py-2 text-xs text-stone-100 focus:outline-none focus:border-amber-500">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-stone-400 mb-1">Language</label>
                                <input type="text" name="language" value="{{ old('language', $draft->language) }}"
                                    class="w-full bg-stone-950 border border-stone-800 rounded-xl px-3 py-2 text-xs text-stone-100 focus:outline-none focus:border-amber-500">
                            </div>
                        </div>
                    </div>

                    {{-- Section 1: AI-Generated Synthesis --}}
                    <div class="bg-stone-900/80 border border-stone-800 rounded-2xl p-5 space-y-2">
                        <div class="flex items-center justify-between">
                            <label class="text-xs font-bold text-amber-400 uppercase tracking-wider flex items-center gap-1.5">
                                <i data-lucide="sparkles" class="w-3.5 h-3.5 text-amber-400"></i>
                                <span>AI-Generated Cultural Summary</span>
                            </label>
                            <span class="text-[10px] bg-amber-500/10 text-amber-300 border border-amber-500/30 px-2 py-0.5 rounded-full font-bold">Meta Llama 3.3</span>
                        </div>
                        <p class="text-[11px] text-stone-400">Synthesized cultural interpretation. You can edit or refine this text directly.</p>
                        <textarea name="ai_summary" rows="4"
                            class="w-full bg-stone-950 border border-stone-800 rounded-xl p-3 text-xs text-stone-200 leading-relaxed focus:outline-none focus:border-amber-500">{{ old('ai_summary', $draft->ai_summary) }}</textarea>
                    </div>

                    {{-- Section 2: Authentic Participant Testimony & Narrative --}}
                    <div class="bg-stone-900/80 border border-stone-800 rounded-2xl p-5 space-y-2">
                        <div class="flex items-center justify-between">
                            <label class="text-xs font-bold text-stone-200 uppercase tracking-wider flex items-center gap-1.5">
                                <i data-lucide="quote" class="w-3.5 h-3.5 text-amber-400"></i>
                                <span>Original Testimony & Discussion Record</span>
                            </label>
                            <span class="text-[10px] bg-stone-800 text-stone-300 border border-stone-700 px-2 py-0.5 rounded-full font-bold">Verbatim Source</span>
                        </div>
                        <p class="text-[11px] text-stone-400">Primary oral testimony captured from community participants.</p>
                        <textarea name="extracted_story" rows="8" required
                            class="w-full bg-stone-950 border border-stone-800 rounded-xl p-3 text-xs text-stone-200 leading-relaxed font-mono focus:outline-none focus:border-amber-500">{{ old('extracted_story', $draft->extracted_story) }}</textarea>
                    </div>

                    {{-- Section 3: Custodian Notes & Enrichment --}}
                    <div class="bg-stone-900/80 border border-amber-500/30 rounded-2xl p-5 space-y-2">
                        <div class="flex items-center justify-between">
                            <label class="text-xs font-bold text-amber-400 uppercase tracking-wider flex items-center gap-1.5">
                                <i data-lucide="pen-tool" class="w-3.5 h-3.5 text-amber-400"></i>
                                <span>Custodian Notes & Community Enrichments</span>
                            </label>
                            <span class="text-[10px] bg-amber-500/20 text-amber-400 border border-amber-500/40 px-2 py-0.5 rounded-full font-bold">Required for Archive</span>
                        </div>
                        <p class="text-[11px] text-stone-400">Add elder verification notes, corrections of names/spelling, clan lineage, sacred restrictions, or additional context.</p>
                        <textarea name="custodian_notes" rows="4" placeholder="Enter elder verification notes, clan context, and traditional confirmations..."
                            class="w-full bg-stone-950 border border-stone-800 rounded-xl p-3 text-xs text-stone-200 leading-relaxed focus:outline-none focus:border-amber-500">{{ old('custodian_notes', $draft->custodian_notes) }}</textarea>
                    </div>

                </div>

                {{-- Right Column: Inquiries, Links, and Actions --}}
                <div class="space-y-5">

                    {{-- Save Changes Card --}}
                    <div class="bg-stone-900/80 border border-stone-800 rounded-2xl p-4 space-y-3">
                        <button type="submit" class="w-full py-2.5 px-4 rounded-xl bg-amber-600 hover:bg-amber-500 text-white font-bold text-xs transition flex items-center justify-center gap-2 shadow-lg shadow-amber-900/20">
                            <i data-lucide="save" class="w-4 h-4"></i>
                            <span>Save Draft Changes</span>
                        </button>
                        <p class="text-[11px] text-stone-400 text-center">Saving preserves draft progress without submitting to public moderation.</p>
                    </div>

                    {{-- Unanswered Inquiries for Custodians --}}
                    <div class="bg-stone-900/80 border border-stone-800 rounded-2xl p-4 space-y-3">
                        <div class="text-xs font-bold text-amber-400 uppercase tracking-wider flex items-center gap-1.5">
                            <i data-lucide="help-circle" class="w-4 h-4 text-amber-400"></i>
                            <span>Inquiries for Community Elders</span>
                        </div>
                        <p class="text-[11px] text-stone-400">Questions extracted from the community discussion that require authoritative custodian clarification:</p>

                        @if(!empty($draft->unanswered_questions))
                            <div class="space-y-2">
                                @foreach($draft->unanswered_questions as $idx => $q)
                                    <div class="p-2.5 rounded-xl bg-amber-950/20 border border-amber-800/30 text-xs text-amber-200 flex items-start gap-2">
                                        <span class="font-bold text-amber-400 mt-0.5">?</span>
                                        <div class="flex-1">
                                            <input type="text" name="unanswered_questions[]" value="{{ $q }}"
                                                class="w-full bg-transparent border-0 p-0 text-xs text-amber-200 focus:ring-0">
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <p class="text-xs text-stone-500 italic">No unanswered cultural questions flagged.</p>
                        @endif
                    </div>

                    {{-- Suggested Heritage Records (Knowledge Graph) --}}
                    @if(!empty($draft->suggested_links))
                        <div class="bg-stone-900/80 border border-stone-800 rounded-2xl p-4 space-y-3">
                            <div class="text-xs font-bold text-stone-300 uppercase tracking-wider flex items-center gap-1.5">
                                <i data-lucide="network" class="w-4 h-4 text-stone-400"></i>
                                <span>Related Heritage Records</span>
                            </div>
                            <div class="space-y-2">
                                @foreach($draft->suggested_links as $link)
                                    <a href="{{ $link['url'] ?? '#' }}" target="_blank"
                                        class="block p-2.5 rounded-xl bg-stone-950 hover:bg-stone-900 border border-stone-800 hover:border-amber-500/40 transition group">
                                        <div class="text-xs font-bold text-stone-200 group-hover:text-amber-400 flex items-center justify-between">
                                            <span>{{ $link['name'] ?? 'Cultural Heritage Record' }}</span>
                                            <i data-lucide="external-link" class="w-3 h-3 opacity-60"></i>
                                        </div>
                                        <p class="text-[10px] text-stone-400 mt-0.5">{{ $link['category'] ?? '' }} • {{ $link['region'] ?? '' }}</p>
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    @endif

                </div>

            </div>
        </form>

    </div>
</div>
@endsection
