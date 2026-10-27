@extends('layouts.app')

@section('title', 'Living Heritage Capture - Kulturegram')

@section('content')
<div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-10">

    <!-- Header & Hero Section -->
    <div class="bg-white dark:bg-stone-900 border border-stone-200 dark:border-stone-800 rounded-3xl p-6 sm:p-10 shadow-sm relative overflow-hidden">
        <div class="max-w-3xl space-y-4">
            <div class="inline-flex items-center space-x-2 px-3 py-1.5 rounded-full bg-amber-50 dark:bg-amber-950/40 border border-amber-200 dark:border-amber-800/60 text-amber-700 dark:text-amber-400 text-xs font-bold uppercase tracking-wider">
                <i data-lucide="sparkles" class="w-3.5 h-3.5"></i>
                <span>Cultural Preservation Intelligence</span>
            </div>

            <h1 class="text-3xl sm:text-4xl font-extrabold text-stone-900 dark:text-stone-100 tracking-tight">
                Living Heritage Capture
            </h1>
            <p class="text-lg sm:text-xl font-medium text-amber-600 dark:text-amber-500">
                Preserve the stories. Protect the knowledge. Pass the heritage forward.
            </p>
            <p class="text-sm sm:text-base text-stone-600 dark:text-stone-400 leading-relaxed">
                Empowering communities, custodians, and cultural enthusiasts to capture endangered oral histories, songs, rituals, and artifacts. AI assists your memory without replacing community authority.
            </p>

            <div class="pt-4 flex flex-wrap gap-3">
                <a href="{{ route('cultural-hub.living-heritage.create') }}"
                   class="inline-flex items-center space-x-2 px-6 py-3.5 bg-amber-500 hover:bg-amber-600 active:bg-amber-700 text-white font-bold rounded-xl text-sm transition-colors shadow-sm">
                    <i data-lucide="mic" class="w-4 h-4"></i>
                    <span>Start Living Heritage Capture</span>
                </a>
                <a href="{{ route('cultural-hub.living-heritage.compare') }}"
                   class="inline-flex items-center space-x-2 px-6 py-3.5 bg-stone-100 dark:bg-stone-800 hover:bg-stone-200 dark:hover:bg-stone-700 text-stone-800 dark:text-stone-200 font-bold rounded-xl text-sm transition-colors border border-stone-200 dark:border-stone-700">
                    <i data-lucide="git-compare" class="w-4 h-4"></i>
                    <span>Model Comparison Lab</span>
                </a>
                <a href="{{ route('cultural-hub.index') }}"
                   class="inline-flex items-center space-x-2 px-6 py-3.5 text-stone-600 dark:text-stone-400 hover:text-stone-900 dark:hover:text-stone-100 font-medium rounded-xl text-sm transition-colors">
                    <i data-lucide="arrow-left" class="w-4 h-4"></i>
                    <span>Back to Cultural Hub</span>
                </a>
            </div>
        </div>
    </div>

    <!-- API & Model Status Banner -->
    <div class="bg-stone-50 dark:bg-stone-900/60 border border-stone-200 dark:border-stone-800 rounded-2xl p-5">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="flex items-start sm:items-center space-x-3">
                <div class="w-3 h-3 rounded-full mt-1 sm:mt-0 {{ $isConfigured ? 'bg-green-500 animate-pulse' : 'bg-amber-500' }}"></div>
                <div>
                    <h3 class="text-sm font-bold text-stone-900 dark:text-stone-100">
                        {{ $isConfigured ? 'Shared Meta Model API Connected' : 'Meta Model API Key Pending / Offline Archival Mode' }}
                    </h3>
                    <p class="text-xs text-stone-500 dark:text-stone-400">
                        @if($isConfigured)
                            One shared credentials client (<code class="font-mono text-stone-700 dark:text-stone-300">{{ $maskedKey }}</code>) powers Muse Spark, Muse Wonder, Muse Image, Muse Voice Transcribe, and SAM 3.1.
                        @else
                            Awaiting configured <code class="font-mono text-amber-700 dark:text-amber-400">META_MODEL_API_KEY</code>. All manual capture forms, recording uploads, and offline draft workflows remain 100% operational.
                        @endif
                    </p>
                </div>
            </div>
            <div class="flex items-center space-x-2 text-xs font-semibold text-stone-600 dark:text-stone-400">
                <span class="px-2.5 py-1 bg-white dark:bg-stone-800 border border-stone-200 dark:border-stone-700 rounded-lg">5 Hosted Models</span>
                <span class="px-2.5 py-1 bg-white dark:bg-stone-800 border border-stone-200 dark:border-stone-700 rounded-lg">2 Open-Weight</span>
            </div>
        </div>
    </div>

    <!-- 5-Step Preservation Workflow Highlights -->
    <div class="space-y-4">
        <div class="flex items-center justify-between">
            <h2 class="text-lg font-bold text-stone-900 dark:text-stone-100 flex items-center space-x-2">
                <i data-lucide="layers" class="w-5 h-5 text-amber-600"></i>
                <span>The Living Heritage Pipeline</span>
            </h2>
            <span class="text-xs text-stone-500">Human-Assisted AI Preservation</span>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
            <!-- Step 1 & 2 -->
            <div class="bg-white dark:bg-stone-900 border border-stone-200 dark:border-stone-800 rounded-2xl p-5 space-y-3">
                <div class="w-10 h-10 rounded-xl bg-amber-100 dark:bg-amber-950/60 text-amber-700 dark:text-amber-400 flex items-center justify-center font-bold text-sm">
                    01
                </div>
                <h3 class="font-bold text-stone-900 dark:text-stone-100 text-base">Oral Capture & Transcription</h3>
                <p class="text-xs text-stone-600 dark:text-stone-400 leading-relaxed">
                    Upload voice recordings of folktales or songs. <strong class="text-stone-800 dark:text-stone-200">Muse Voice Transcribe</strong> converts audio to text, keeping original dialect and words editable.
                </p>
                <div class="pt-2 text-[11px] font-mono text-stone-500">Model: Muse Voice Transcribe</div>
            </div>

            <!-- Step 3 & 4 -->
            <div class="bg-white dark:bg-stone-900 border border-stone-200 dark:border-stone-800 rounded-2xl p-5 space-y-3">
                <div class="w-10 h-10 rounded-xl bg-amber-100 dark:bg-amber-950/60 text-amber-700 dark:text-amber-400 flex items-center justify-center font-bold text-sm">
                    02
                </div>
                <h3 class="font-bold text-stone-900 dark:text-stone-100 text-base">Structured Extraction & Guard</h3>
                <p class="text-xs text-stone-600 dark:text-stone-400 leading-relaxed">
                    <strong class="text-stone-800 dark:text-stone-200">Muse Spark</strong> organizes oral stories into verified records. <strong class="text-stone-800 dark:text-stone-200">Muse Wonder</strong> provides neutral follow-up questions and preservation checklists.
                </p>
                <div class="pt-2 text-[11px] font-mono text-stone-500">Models: Muse Spark & Muse Wonder</div>
            </div>

            <!-- Step 5 & 6 -->
            <div class="bg-white dark:bg-stone-900 border border-stone-200 dark:border-stone-800 rounded-2xl p-5 space-y-3">
                <div class="w-10 h-10 rounded-xl bg-amber-100 dark:bg-amber-950/60 text-amber-700 dark:text-amber-400 flex items-center justify-center font-bold text-sm">
                    03
                </div>
                <h3 class="font-bold text-stone-900 dark:text-stone-100 text-base">Visuals, Artifacts & Moderation</h3>
                <p class="text-xs text-stone-600 dark:text-stone-400 leading-relaxed">
                    Segment physical artifacts using <strong class="text-stone-800 dark:text-stone-200">SAM 3.1</strong> or generate clearly labeled cultural artwork with <strong class="text-stone-800 dark:text-stone-200">Muse Image</strong>. All items proceed through community moderation.
                </p>
                <div class="pt-2 text-[11px] font-mono text-stone-500">Models: SAM 3.1 & Muse Image</div>
            </div>
        </div>
    </div>

    <!-- Contributor's Drafts & Submissions -->
    @if(Auth::check() && $userDrafts->count() > 0)
        <div class="space-y-4">
            <h2 class="text-lg font-bold text-stone-900 dark:text-stone-100 flex items-center space-x-2">
                <i data-lucide="file-clock" class="w-5 h-5 text-amber-600"></i>
                <span>Your Living Heritage Drafts & Submissions</span>
            </h2>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($userDrafts as $draft)
                    <div class="bg-white dark:bg-stone-900 border border-stone-200 dark:border-stone-800 rounded-2xl p-4 flex flex-col justify-between space-y-3">
                        <div>
                            <div class="flex items-center justify-between mb-2">
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider {{ $draft->status === 'draft' ? 'bg-stone-100 dark:bg-stone-800 text-stone-600 dark:text-stone-400' : 'bg-amber-100 dark:bg-amber-950/60 text-amber-700 dark:text-amber-400' }}">
                                    {{ $draft->status === 'draft' ? 'Private Draft' : 'In Moderation' }}
                                </span>
                                @if($draft->is_ai_assisted)
                                    <span class="inline-flex items-center text-[10px] font-semibold text-amber-600">
                                        <i data-lucide="sparkles" class="w-3 h-3 mr-1"></i> AI Assisted
                                    </span>
                                @endif
                            </div>
                            <h3 class="font-bold text-stone-900 dark:text-stone-100 text-sm truncate">{{ $draft->name }}</h3>
                            <p class="text-xs text-stone-500 truncate">{{ $draft->region ?? 'Unknown Region' }} • {{ $draft->category }}</p>
                        </div>
                        <div class="pt-2 border-t border-stone-100 dark:border-stone-800 flex items-center justify-between">
                            <span class="text-[11px] text-stone-400">{{ $draft->created_at->diffForHumans() }}</span>
                            <a href="{{ route('cultural-hub.show', $draft->id) }}" class="text-xs font-bold text-amber-600 hover:text-amber-700">
                                View Details &rarr;
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

</div>
@endsection
