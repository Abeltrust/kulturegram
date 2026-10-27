@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-stone-950 text-stone-100 py-8 px-4 sm:px-6 lg:px-8">
    <div class="max-w-6xl mx-auto space-y-6">

        {{-- Header --}}
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 border-b border-stone-800 pb-6">
            <div>
                <div class="flex items-center gap-2 text-amber-500 text-xs font-bold uppercase tracking-widest mb-1">
                    <i data-lucide="archive" class="w-4 h-4"></i>
                    <span>Living Heritage Archive Workflows</span>
                </div>
                <h1 class="text-2xl sm:text-3xl font-black text-stone-100">My Cultural Heritage Drafts</h1>
                <p class="text-xs sm:text-sm text-stone-400 mt-1">Review, enrich, and verify community-extracted cultural records before submitting to archive curators.</p>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('cultural-hub.index') }}" 
                   class="px-4 py-2 rounded-xl bg-stone-900 hover:bg-stone-800 text-stone-300 text-xs font-semibold border border-stone-800 transition flex items-center gap-1.5">
                    <i data-lucide="arrow-left" class="w-4 h-4"></i>
                    <span>Cultural Hub</span>
                </a>
                <a href="{{ route('cultural-hub.living-heritage.create') }}" 
                   class="px-4 py-2 rounded-xl bg-amber-600 hover:bg-amber-500 text-white text-xs font-bold transition flex items-center gap-1.5 shadow-lg shadow-amber-900/30">
                    <i data-lucide="plus" class="w-4 h-4"></i>
                    <span>Record Living Heritage</span>
                </a>
            </div>
        </div>

        {{-- Explanatory Banner --}}
        <div class="p-4 rounded-2xl bg-amber-500/10 border border-amber-500/20 text-xs text-amber-300 flex items-start gap-3">
            <i data-lucide="shield-check" class="w-5 h-5 shrink-0 text-amber-400 mt-0.5"></i>
            <div>
                <span class="font-bold">Custodian Verification First:</span>
                <span class="text-stone-300">Drafts extracted from community conversations or recordings are never published automatically. Authentic oral testimony must be reviewed and enriched by authorized cultural custodians.</span>
            </div>
        </div>

        {{-- Drafts List --}}
        @if($drafts->isEmpty())
            <div class="p-12 text-center bg-stone-900/60 rounded-3xl border border-stone-800/80">
                <i data-lucide="book-marked" class="w-12 h-12 text-stone-600 mx-auto mb-3"></i>
                <h3 class="text-base font-bold text-stone-300">No cultural record drafts yet</h3>
                <p class="text-xs text-stone-500 max-w-md mx-auto mt-1 mb-5">Start by capturing living heritage via audio or livestream discussions. Community testimonies can be preserved here for review.</p>
                <a href="{{ route('cultural-hub.living-heritage.create') }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-amber-600 hover:bg-amber-500 text-white font-bold text-xs transition">
                    <i data-lucide="mic" class="w-4 h-4"></i>
                    <span>Begin Documentation</span>
                </a>
            </div>
        @else
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
                @foreach($drafts as $draft)
                    <div class="bg-stone-900/80 rounded-2xl border border-stone-800 p-5 flex flex-col justify-between hover:border-amber-500/40 transition group">
                        <div>
                            <div class="flex items-center justify-between gap-2 mb-3">
                                <span class="text-[10px] uppercase font-black px-2 py-0.5 rounded-full 
                                    @if($draft->status === 'approved') bg-emerald-500/20 text-emerald-400 border border-emerald-500/30
                                    @elseif($draft->status === 'submitted_to_moderation') bg-amber-500/20 text-amber-400 border border-amber-500/30
                                    @elseif($draft->status === 'reviewed') bg-sky-500/20 text-sky-400 border border-sky-500/30
                                    @else bg-stone-800 text-stone-400 border border-stone-700 @endif">
                                    {{ str_replace('_', ' ', ucfirst($draft->status)) }}
                                </span>
                                <span class="text-[10px] text-stone-500">{{ $draft->created_at->diffForHumans() }}</span>
                            </div>

                            <h3 class="text-base font-bold text-stone-100 group-hover:text-amber-400 transition-colors line-clamp-2 mb-1.5">
                                {{ $draft->title }}
                            </h3>

                            <p class="text-xs text-stone-400 mb-3 flex items-center gap-1.5">
                                <i data-lucide="map-pin" class="w-3.5 h-3.5 text-stone-500"></i>
                                <span>{{ $draft->region ?? 'Regional Heritage' }} • {{ $draft->category ?? 'Tradition' }}</span>
                            </p>

                            <p class="text-xs text-stone-300 line-clamp-3 bg-stone-950/60 p-2.5 rounded-xl border border-stone-800/80 mb-4 font-mono text-[11px]">
                                {{ $draft->ai_summary ?: \Illuminate\Support\Str::limit($draft->extracted_story, 150) }}
                            </p>

                            @if(!empty($draft->unanswered_questions))
                                <div class="text-[11px] text-amber-300/90 mb-3 flex items-center gap-1">
                                    <i data-lucide="help-circle" class="w-3.5 h-3.5 text-amber-400"></i>
                                    <span>{{ count($draft->unanswered_questions) }} inquiries awaiting elder notes</span>
                                </div>
                            @endif
                        </div>

                        <div class="pt-3 border-t border-stone-800/80 flex items-center justify-between gap-2">
                            <a href="{{ route('cultural-hub.drafts.show', $draft->id) }}" 
                               class="flex-1 py-2 text-center rounded-xl bg-amber-600/90 hover:bg-amber-500 text-white text-xs font-bold transition flex items-center justify-center gap-1.5">
                                <i data-lucide="edit-3" class="w-3.5 h-3.5"></i>
                                <span>Review & Edit</span>
                            </a>
                            @if($draft->status === 'approved' && $draft->approved_culture_id)
                                <a href="{{ route('cultural-hub.show', $draft->approved_culture_id) }}" 
                                   class="px-3 py-2 rounded-xl bg-stone-800 hover:bg-stone-700 text-stone-200 text-xs font-semibold transition" title="View Published Record">
                                    <i data-lucide="external-link" class="w-3.5 h-3.5"></i>
                                </a>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="mt-6">
                {{ $drafts->links() }}
            </div>
        @endif

    </div>
</div>
@endsection
