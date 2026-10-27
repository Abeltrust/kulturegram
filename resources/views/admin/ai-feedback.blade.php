@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-stone-950 text-stone-100 py-8 px-4 sm:px-6 lg:px-8">
    <div class="max-w-7xl mx-auto space-y-6">

        {{-- Header --}}
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 border-b border-stone-800 pb-6">
            <div>
                <div class="flex items-center gap-2 text-amber-500 text-xs font-bold uppercase tracking-widest mb-1">
                    <i data-lucide="shield-check" class="w-4 h-4"></i>
                    <span>Governance & Trust</span>
                </div>
                <h1 class="text-2xl sm:text-3xl font-black text-stone-100">AI Feedback & Continuous Improvement</h1>
                <p class="text-xs sm:text-sm text-stone-400 mt-1">Controlled feedback loops for moderation decisions, recommendation accuracy, and cultural record corrections.</p>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('admin.cultures.index') }}" 
                   class="px-4 py-2 rounded-xl bg-stone-900 hover:bg-stone-800 text-stone-300 text-xs font-semibold border border-stone-800 transition flex items-center gap-1.5">
                    <i data-lucide="layers" class="w-4 h-4"></i>
                    <span>Curation Queue</span>
                </a>
                <a href="{{ route('admin.privacy-audit.index') }}" 
                   class="px-4 py-2 rounded-xl bg-stone-900 hover:bg-stone-800 text-stone-300 text-xs font-semibold border border-stone-800 transition flex items-center gap-1.5">
                    <i data-lucide="lock" class="w-4 h-4"></i>
                    <span>Privacy Audit</span>
                </a>
            </div>
        </div>

        {{-- Governance Notice --}}
        <div class="p-4 rounded-2xl bg-amber-500/10 border border-amber-500/20 text-xs text-amber-300 flex items-start gap-3">
            <i data-lucide="info" class="w-5 h-5 shrink-0 text-amber-400 mt-0.5"></i>
            <div>
                <span class="font-bold">Safe Controlled Evolution:</span>
                <span class="text-stone-300">Models and moderation rules are never automatically retrained on unreviewed feedback. User feedback is isolated and audited here by platform administrators to prevent manipulation or suppression of less-represented communities.</span>
            </div>
        </div>

        {{-- Metrics Grid --}}
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
            <div class="bg-stone-900/80 border border-stone-800 rounded-2xl p-4">
                <span class="text-[10px] font-bold text-stone-400 uppercase tracking-wider block">Total Logged Events</span>
                <span class="text-2xl font-black text-stone-100 block mt-1">{{ $metrics['total_feedback'] }}</span>
            </div>
            <div class="bg-stone-900/80 border border-stone-800 rounded-2xl p-4">
                <span class="text-[10px] font-bold text-stone-400 uppercase tracking-wider block">Moderation Disputes</span>
                <span class="text-2xl font-black text-rose-400 block mt-1">{{ $metrics['by_type']['moderation_dispute'] ?? 0 }}</span>
            </div>
            <div class="bg-stone-900/80 border border-stone-800 rounded-2xl p-4">
                <span class="text-[10px] font-bold text-stone-400 uppercase tracking-wider block">Recommendation Flags</span>
                <span class="text-2xl font-black text-amber-400 block mt-1">
                    {{ ($metrics['by_type']['recommendation_irrelevant'] ?? 0) + ($metrics['by_type']['recommendation_inappropriate'] ?? 0) }}
                </span>
            </div>
            <div class="bg-stone-900/80 border border-stone-800 rounded-2xl p-4">
                <span class="text-[10px] font-bold text-stone-400 uppercase tracking-wider block">Summary Corrections</span>
                <span class="text-2xl font-black text-emerald-400 block mt-1">{{ $metrics['by_type']['summary_correction'] ?? 0 }}</span>
            </div>
        </div>

        {{-- Feedback Log Table --}}
        <div class="bg-stone-900/80 border border-stone-800 rounded-2xl overflow-hidden">
            <div class="p-4 border-b border-stone-800 flex items-center justify-between">
                <h3 class="font-bold text-sm text-stone-200">Recent Human Feedback & Corrections</h3>
                <span class="text-xs text-stone-500">{{ $feedbackLogs->total() }} events recorded</span>
            </div>

            @if($feedbackLogs->isEmpty())
                <div class="p-8 text-center text-stone-500 text-xs">
                    No feedback events recorded yet.
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-stone-950/60 text-stone-400 uppercase font-mono text-[10px]">
                            <tr>
                                <th class="p-3">Type</th>
                                <th class="p-3">User</th>
                                <th class="p-3">Target</th>
                                <th class="p-3">User Correction / Notes</th>
                                <th class="p-3">Status</th>
                                <th class="p-3">Timestamp</th>
                                <th class="p-3 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-stone-800 text-stone-300">
                            @foreach($feedbackLogs as $log)
                                <tr class="hover:bg-stone-850 transition">
                                    <td class="p-3">
                                        <span class="text-[10px] uppercase font-black px-2 py-0.5 rounded-full
                                            @if($log->feedback_type === 'moderation_dispute') bg-rose-500/20 text-rose-400 border border-rose-500/30
                                            @elseif($log->feedback_type === 'summary_correction') bg-emerald-500/20 text-emerald-400 border border-emerald-500/30
                                            @elseif(str_starts_with($log->feedback_type, 'recommendation')) bg-amber-500/20 text-amber-400 border border-amber-500/30
                                            @else bg-stone-800 text-stone-400 @endif">
                                            {{ str_replace('_', ' ', $log->feedback_type) }}
                                        </span>
                                    </td>
                                    <td class="p-3 font-medium text-stone-200">{{ $log->user->name ?? 'Community Member' }}</td>
                                    <td class="p-3 font-mono text-stone-400 text-[11px]">{{ $log->target_type }}:{{ $log->target_id }}</td>
                                    <td class="p-3 max-w-xs truncate text-stone-300" title="{{ $log->user_correction }}">
                                        {{ $log->user_correction ?: ($log->reason ?: 'No notes') }}
                                    </td>
                                    <td class="p-3">
                                        <span class="text-[10px] font-bold uppercase
                                            @if($log->status === 'applied') text-emerald-400
                                            @elseif($log->status === 'reviewed') text-sky-400
                                            @else text-amber-400 @endif">
                                            {{ $log->status }}
                                        </span>
                                    </td>
                                    <td class="p-3 text-stone-500">{{ $log->created_at->diffForHumans() }}</td>
                                    <td class="p-3 text-right">
                                        @if($log->status === 'logged')
                                            <form action="{{ route('admin.ai-feedback.status', $log->id) }}" method="POST" class="inline">
                                                @csrf
                                                <input type="hidden" name="status" value="reviewed">
                                                <button type="submit" class="px-2 py-1 rounded bg-stone-800 hover:bg-stone-700 text-stone-300 text-[10px] font-bold">
                                                    Mark Reviewed
                                                </button>
                                            </form>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="p-3 border-t border-stone-800">
                    {{ $feedbackLogs->links() }}
                </div>
            @endif
        </div>

    </div>
</div>
@endsection
