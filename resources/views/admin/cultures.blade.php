@extends('layouts.app')

@section('title', 'Admin Moderation - Kulturegram')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 md:py-12">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-8">
        <div>
            <div class="flex items-center gap-2">
                <span class="px-2.5 py-0.5 rounded-full text-xs font-black uppercase tracking-wider bg-amber-100 dark:bg-amber-950/50 text-amber-800 dark:text-amber-400 border border-amber-200 dark:border-amber-800">
                    Administrator Center
                </span>
            </div>
            <h1 class="text-3xl font-black text-stone-900 dark:text-stone-100 mt-2">
                Cultural Hub Moderation
            </h1>
            <p class="text-stone-600 dark:text-stone-400 text-sm mt-1">
                Review submitted heritage stories, traditions, rituals, and artifacts.
            </p>
        </div>

        {{-- Overall Stats --}}
        <div class="grid grid-cols-3 gap-3">
            <div class="bg-white dark:bg-stone-900 p-3 sm:p-4 rounded-2xl border border-stone-200 dark:border-stone-800 text-center">
                <p class="text-xs text-stone-400">Total Users</p>
                <p class="text-lg font-black text-stone-800 dark:text-stone-100">{{ $counts['total_users'] }}</p>
            </div>
            <div class="bg-white dark:bg-stone-900 p-3 sm:p-4 rounded-2xl border border-stone-200 dark:border-stone-800 text-center">
                <p class="text-xs text-stone-400">Communities</p>
                <p class="text-lg font-black text-stone-800 dark:text-stone-100">{{ $counts['total_communities'] }}</p>
            </div>
            <div class="bg-white dark:bg-stone-900 p-3 sm:p-4 rounded-2xl border border-stone-200 dark:border-stone-800 text-center">
                <p class="text-xs text-stone-400">Events</p>
                <p class="text-lg font-black text-stone-800 dark:text-stone-100">{{ $counts['total_events'] }}</p>
            </div>
        </div>
    </div>

    {{-- Tabs / Filter Bar --}}
    <div class="flex items-center gap-2 mb-6 border-b border-stone-200 dark:border-stone-800 pb-3 overflow-x-auto">
        <a href="{{ route('admin.cultures.index', ['status' => 'pending_review']) }}"
           class="px-4 py-2 rounded-xl text-sm font-bold transition flex items-center gap-2 {{ $status === 'pending_review' ? 'bg-amber-500 text-white shadow-sm' : 'bg-stone-100 dark:bg-stone-800 text-stone-600 dark:text-stone-400 hover:text-stone-900' }}">
            <span>Pending Review</span>
            <span class="px-2 py-0.5 rounded-full text-xs {{ $status === 'pending_review' ? 'bg-amber-600 text-white' : 'bg-stone-200 dark:bg-stone-700 text-stone-700 dark:text-stone-300' }}">
                {{ $counts['pending'] }}
            </span>
        </a>
        <a href="{{ route('admin.cultures.index', ['status' => 'approved']) }}"
           class="px-4 py-2 rounded-xl text-sm font-bold transition flex items-center gap-2 {{ $status === 'approved' ? 'bg-amber-500 text-white shadow-sm' : 'bg-stone-100 dark:bg-stone-800 text-stone-600 dark:text-stone-400 hover:text-stone-900' }}">
            <span>Approved</span>
            <span class="px-2 py-0.5 rounded-full text-xs {{ $status === 'approved' ? 'bg-amber-600 text-white' : 'bg-stone-200 dark:bg-stone-700 text-stone-700 dark:text-stone-300' }}">
                {{ $counts['approved'] }}
            </span>
        </a>
        <a href="{{ route('admin.cultures.index', ['status' => 'featured']) }}"
           class="px-4 py-2 rounded-xl text-sm font-bold transition flex items-center gap-2 {{ $status === 'featured' ? 'bg-amber-500 text-white shadow-sm' : 'bg-stone-100 dark:bg-stone-800 text-stone-600 dark:text-stone-400 hover:text-stone-900' }}">
            <span>Featured</span>
            <span class="px-2 py-0.5 rounded-full text-xs {{ $status === 'featured' ? 'bg-amber-600 text-white' : 'bg-stone-200 dark:bg-stone-700 text-stone-700 dark:text-stone-300' }}">
                {{ $counts['featured'] }}
            </span>
        </a>
        <a href="{{ route('admin.cultures.index', ['status' => 'all']) }}"
           class="px-4 py-2 rounded-xl text-sm font-bold transition {{ $status === 'all' ? 'bg-amber-500 text-white shadow-sm' : 'bg-stone-100 dark:bg-stone-800 text-stone-600 dark:text-stone-400 hover:text-stone-900' }}">
            All Submissions
        </a>
        <a href="{{ route('admin.ai-feedback.index') }}"
           class="px-4 py-2 rounded-xl text-sm font-bold transition text-stone-600 dark:text-stone-400 hover:text-stone-900 dark:hover:text-stone-100 bg-stone-100 dark:bg-stone-800 flex items-center gap-1.5 ml-auto">
            <i data-lucide="sparkles" class="w-4 h-4 text-amber-500"></i>
            <span>AI Feedback & Metrics</span>
        </a>
        <a href="{{ route('admin.privacy-audit.index') }}"
           class="px-4 py-2 rounded-xl text-sm font-bold transition text-stone-600 dark:text-stone-400 hover:text-stone-900 dark:hover:text-stone-100 bg-stone-100 dark:bg-stone-800 flex items-center gap-1.5">
            <i data-lucide="shield-check" class="w-4 h-4 text-emerald-500"></i>
            <span>Privacy Audit</span>
        </a>
    </div>

    {{-- Submitted Cultural Record Drafts Queue --}}
    @if(isset($submittedDrafts) && $submittedDrafts->count() > 0)
        <div class="mb-8 p-5 rounded-2xl bg-gradient-to-r from-amber-950/40 via-stone-900 to-stone-900 border border-amber-500/30 space-y-4">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-amber-500 animate-pulse"></span>
                    <h2 class="font-bold text-base text-stone-100">Living Heritage Drafts Pending Final Curatorial Approval ({{ $submittedDrafts->count() }})</h2>
                </div>
                <span class="text-xs text-amber-400 font-bold">Community Preserved</span>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @foreach($submittedDrafts as $draft)
                    <div class="p-4 rounded-xl bg-stone-950/80 border border-stone-800 space-y-3">
                        <div class="flex items-start justify-between gap-2">
                            <div>
                                <h3 class="font-bold text-sm text-stone-100">{{ $draft->title }}</h3>
                                <p class="text-xs text-stone-400">{{ $draft->region ?? 'Regional' }} • {{ $draft->category ?? 'Tradition' }}</p>
                            </div>
                            <span class="text-[10px] font-mono text-stone-500">{{ $draft->created_at->diffForHumans() }}</span>
                        </div>

                        <p class="text-xs text-stone-300 line-clamp-2 bg-stone-900/60 p-2 rounded-lg font-mono text-[11px]">
                            {{ $draft->ai_summary ?: \Illuminate\Support\Str::limit($draft->extracted_story, 120) }}
                        </p>

                        @if($draft->custodian_notes)
                            <div class="text-[11px] text-amber-300/90 italic">
                                <strong>Custodian note:</strong> {{ \Illuminate\Support\Str::limit($draft->custodian_notes, 100) }}
                            </div>
                        @endif

                        <div class="flex items-center gap-2 pt-2 border-t border-stone-800/80">
                            <a href="{{ route('cultural-hub.drafts.show', $draft->id) }}" 
                               class="flex-1 py-1.5 text-center rounded-lg bg-stone-800 hover:bg-stone-700 text-stone-200 text-xs font-semibold transition">
                                Inspect Full Draft
                            </a>
                            <form action="{{ route('admin.drafts.approve', $draft->id) }}" method="POST" onsubmit="return confirm('Approve and publish this draft into the permanent public cultural archive?')">
                                @csrf
                                <button type="submit" class="px-4 py-1.5 rounded-lg bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-bold transition flex items-center gap-1">
                                    <i data-lucide="check" class="w-3.5 h-3.5"></i>
                                    <span>Approve & Publish</span>
                                </button>
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    {{-- Submissions List --}}
    @if($cultures->count() > 0)
        <div class="space-y-4">
            @foreach($cultures as $culture)
                <div class="bg-white dark:bg-stone-900 rounded-2xl border border-stone-200 dark:border-stone-800 p-5 sm:p-6 shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-6">
                    <div class="flex items-start gap-4">
                        @if($culture->image)
                            <img src="{{ asset('storage/' . $culture->image) }}" class="w-20 h-20 sm:w-24 sm:h-24 rounded-2xl object-cover shrink-0 border border-stone-200 dark:border-stone-700">
                        @else
                            <div class="w-20 h-20 sm:w-24 sm:h-24 rounded-2xl bg-amber-50 dark:bg-amber-950/40 text-amber-600 flex items-center justify-center shrink-0 border border-amber-200 dark:border-amber-800">
                                <i data-lucide="globe" class="w-8 h-8"></i>
                            </div>
                        @endif

                        <div class="space-y-1">
                            <div class="flex items-center gap-2 flex-wrap">
                                <h3 class="text-lg font-black text-stone-900 dark:text-stone-100">
                                    {{ $culture->name }}
                                </h3>
                                <span class="px-2 py-0.5 rounded-md text-[11px] font-bold uppercase tracking-wider bg-stone-100 dark:bg-stone-800 text-stone-600 dark:text-stone-300">
                                    {{ $culture->category }}
                                </span>
                                <span class="px-2 py-0.5 rounded-md text-[11px] font-bold uppercase tracking-wider
                                    {{ $culture->status === 'approved' ? 'bg-green-100 text-green-700 dark:bg-green-950/40 dark:text-green-400' : '' }}
                                    {{ $culture->status === 'featured' ? 'bg-amber-100 text-amber-700 dark:bg-amber-950/40 dark:text-amber-400' : '' }}
                                    {{ $culture->status === 'pending_review' ? 'bg-yellow-100 text-yellow-700 dark:bg-yellow-950/40 dark:text-yellow-400' : '' }}">
                                    {{ str_replace('_', ' ', $culture->status) }}
                                </span>
                                @if($culture->is_ai_assisted)
                                    <span class="px-2 py-0.5 rounded-md text-[11px] font-bold uppercase tracking-wider bg-amber-50 dark:bg-amber-950/40 text-amber-700 dark:text-amber-400 border border-amber-200 dark:border-amber-800 flex items-center gap-1">
                                        <i data-lucide="sparkles" class="w-3 h-3"></i> AI Assisted
                                    </span>
                                @endif
                                @if(!empty($culture->wonder_preservation_plan['originality_audit']))
                                    @php $audit = $culture->wonder_preservation_plan['originality_audit']; @endphp
                                    <span class="px-2 py-0.5 rounded-md text-[11px] font-bold tracking-wider bg-stone-100 dark:bg-stone-800 text-stone-700 dark:text-stone-300">
                                        Authenticity: {{ $audit['oral_authenticity_score'] ?? 0 }}%
                                    </span>
                                    <span class="px-2 py-0.5 rounded-md text-[11px] font-bold uppercase tracking-wider
                                        {{ ($audit['plagiarism_risk'] ?? '') === 'low' ? 'bg-green-100 text-green-700 dark:bg-green-950/40 dark:text-green-400' : 'bg-red-100 text-red-700 dark:bg-red-950/40 dark:text-red-400' }}">
                                        Plagiarism Risk: {{ $audit['plagiarism_risk'] ?? 'low' }}
                                    </span>
                                @endif
                            </div>

                            <p class="text-stone-600 dark:text-stone-400 text-sm line-clamp-2">
                                {{ $culture->description }}
                            </p>

                            <div class="flex items-center gap-4 text-xs text-stone-500 dark:text-stone-500 pt-1">
                                <span>Region: <strong class="text-stone-700 dark:text-stone-300">{{ $culture->region }}</strong></span>
                                <span>Submitted by: <strong class="text-stone-700 dark:text-stone-300">{{ $culture->submitter->name ?? 'Unknown' }}</strong></span>
                                <span>Date: {{ $culture->created_at->format('M d, Y') }}</span>
                            </div>
                        </div>
                    </div>

                    {{-- Actions --}}
                    <div class="flex items-center gap-2 shrink-0 self-end md:self-center">
                        <a href="{{ route('cultural-hub.show', $culture) }}" target="_blank"
                           class="px-3.5 py-2 rounded-xl border border-stone-200 dark:border-stone-700 text-stone-700 dark:text-stone-300 hover:bg-stone-100 dark:hover:bg-stone-800 font-bold text-xs transition flex items-center gap-1.5">
                            <i data-lucide="external-link" class="w-3.5 h-3.5"></i> Preview
                        </a>

                        @if($culture->status === 'pending_review')
                            <form action="{{ route('admin.cultures.approve', $culture) }}" method="POST">
                                @csrf
                                <button type="submit" class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white font-bold text-xs rounded-xl transition shadow-sm flex items-center gap-1.5">
                                    <i data-lucide="check-circle" class="w-3.5 h-3.5"></i> Approve
                                </button>
                            </form>
                        @endif

                        <form action="{{ route('admin.cultures.feature', $culture) }}" method="POST">
                            @csrf
                            <button type="submit" class="px-3.5 py-2 {{ $culture->status === 'featured' ? 'bg-amber-100 text-amber-700 dark:bg-amber-950/50 dark:text-amber-400' : 'bg-stone-100 dark:bg-stone-800 text-stone-700 dark:text-stone-300' }} hover:bg-amber-200 dark:hover:bg-amber-900/40 font-bold text-xs rounded-xl transition flex items-center gap-1.5">
                                <i data-lucide="star" class="w-3.5 h-3.5 {{ $culture->status === 'featured' ? 'fill-current' : '' }}"></i>
                                {{ $culture->status === 'featured' ? 'Featured' : 'Feature' }}
                            </button>
                        </form>

                        <form action="{{ route('admin.cultures.destroy', $culture) }}" method="POST" onsubmit="return confirm('Reject and remove this submission?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="p-2 text-stone-400 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-950/30 rounded-xl transition">
                                <i data-lucide="trash-2" class="w-4 h-4"></i>
                            </button>
                        </form>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="mt-8">
            {{ $cultures->links() }}
        </div>
    @else
        <div class="text-center py-16 bg-white dark:bg-stone-900 rounded-3xl border border-stone-200 dark:border-stone-800">
            <div class="w-16 h-16 bg-amber-50 dark:bg-amber-950/40 text-amber-600 rounded-full flex items-center justify-center mx-auto mb-4">
                <i data-lucide="check" class="w-8 h-8"></i>
            </div>
            <h3 class="text-xl font-black text-stone-800 dark:text-stone-200">All caught up!</h3>
            <p class="text-sm text-stone-500 dark:text-stone-400 mt-1">No cultural submissions found under this status filter.</p>
        </div>
    @endif
</div>
@endsection
