{{-- resources/views/profile/activity.blade.php --}}
@extends('layouts.app')

@section('title', $user->name . ' - Activity & Preservation Log - Kulturegram')

@section('content')
<div class="min-h-screen bg-stone-50 dark:bg-stone-950 pb-20 overflow-x-hidden w-full" x-data="{
    currentFilter: 'all',
    searchQuery: ''
}">
    <div class="max-w-5xl mx-auto px-3 sm:px-6 pt-4 sm:pt-8 space-y-6">

        {{-- Top Navigation & Header --}}
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <a href="{{ $isOwner ? route('profile.show') : route('profile.user', $user) }}" 
                    class="inline-flex items-center gap-1.5 text-xs font-bold text-amber-600 dark:text-amber-400 hover:underline uppercase tracking-wider mb-2">
                    <i data-lucide="arrow-left" class="w-3.5 h-3.5"></i> Back to Profile
                </a>
                <h1 class="text-2xl sm:text-3xl font-black text-stone-900 dark:text-white tracking-tight flex items-center gap-2.5">
                    <i data-lucide="activity" class="w-6 h-6 sm:w-7 sm:h-7 text-amber-500"></i>
                    <span>Preservation Journey & Activity Log</span>
                </h1>
                <p class="text-xs sm:text-sm text-stone-500 mt-1 max-w-2xl">
                    Every story told, tradition documented, tribe joined, and heirloom safeguarded by 
                    <span class="font-bold text-stone-800 dark:text-stone-200">{{ $user->name }}</span>
                    @if($user->username)
                        <span class="text-stone-400 dark:text-stone-500 font-medium">({{ '@' . $user->username }})</span>
                    @endif.
                </p>
            </div>

            <div class="flex items-center gap-2">
                @if($isOwner)
                    <a href="{{ route('settings.index', $user->id) }}" class="inline-flex items-center gap-1.5 px-4 py-2 bg-white dark:bg-stone-900 border border-stone-200 dark:border-stone-800 rounded-xl text-xs font-bold text-stone-700 dark:text-stone-300 shadow-sm hover:bg-stone-50">
                        <i data-lucide="settings" class="w-3.5 h-3.5"></i> Settings
                    </a>
                @endif
                <a href="{{ route('timeline.index') }}" class="inline-flex items-center gap-1.5 px-4 py-2 bg-amber-500 hover:bg-amber-600 text-white rounded-xl text-xs font-bold shadow-md transition">
                    <i data-lucide="plus" class="w-3.5 h-3.5"></i> New Memory
                </a>
            </div>
        </div>

        {{-- METRICS & IMPACT STATS CARDS (MOVED TO THIS STANDALONE PAGE) --}}
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-2.5 sm:gap-4">
            
            <div class="bg-white dark:bg-stone-900 rounded-2xl p-4 border border-stone-200/80 dark:border-stone-800 text-center shadow-sm">
                <div class="w-8 h-8 rounded-xl bg-stone-100 dark:bg-stone-800 text-stone-600 dark:text-stone-300 mx-auto flex items-center justify-center mb-1">
                    <i data-lucide="feather" class="w-4 h-4"></i>
                </div>
                <p class="text-2xl font-black text-stone-900 dark:text-white">{{ $stats['stories'] }}</p>
                <p class="text-[11px] font-bold text-stone-400 dark:text-stone-500 uppercase tracking-wider mt-0.5">Stories</p>
            </div>

            <div class="bg-white dark:bg-stone-900 rounded-2xl p-4 border border-stone-200/80 dark:border-stone-800 text-center shadow-sm">
                <div class="w-8 h-8 rounded-xl bg-amber-50 dark:bg-amber-950/40 text-amber-500 mx-auto flex items-center justify-center mb-1">
                    <i data-lucide="user-check" class="w-4 h-4"></i>
                </div>
                <p class="text-2xl font-black text-stone-900 dark:text-white">{{ $stats['locked_in'] }}</p>
                <p class="text-[11px] font-bold text-stone-400 dark:text-stone-500 uppercase tracking-wider mt-0.5">Locked In</p>
            </div>

            <div class="bg-white dark:bg-stone-900 rounded-2xl p-4 border border-stone-200/80 dark:border-stone-800 text-center shadow-sm">
                <div class="w-8 h-8 rounded-xl bg-amber-50 dark:bg-amber-950/40 text-amber-500 mx-auto flex items-center justify-center mb-1">
                    <i data-lucide="zap" class="w-4 h-4 fill-current"></i>
                </div>
                <p class="text-2xl font-black text-amber-500">{{ $stats['vibes'] }}</p>
                <p class="text-[11px] font-bold text-stone-400 dark:text-stone-500 uppercase tracking-wider mt-0.5">Vibes / Taps</p>
            </div>

            <div class="bg-white dark:bg-stone-900 rounded-2xl p-4 border border-stone-200/80 dark:border-stone-800 text-center shadow-sm">
                <div class="w-8 h-8 rounded-xl bg-orange-50 dark:bg-orange-950/40 text-orange-500 mx-auto flex items-center justify-center mb-1">
                    <i data-lucide="archive" class="w-4 h-4"></i>
                </div>
                <p class="text-2xl font-black text-orange-500">{{ $stats['contributions'] }}</p>
                <p class="text-[11px] font-bold text-stone-400 dark:text-stone-500 uppercase tracking-wider mt-0.5">Traditions</p>
            </div>

            <div class="bg-white dark:bg-stone-900 rounded-2xl p-4 border border-stone-200/80 dark:border-stone-800 text-center shadow-sm">
                <div class="w-8 h-8 rounded-xl bg-purple-50 dark:bg-purple-950/40 text-purple-500 mx-auto flex items-center justify-center mb-1">
                    <i data-lucide="lock" class="w-4 h-4"></i>
                </div>
                <p class="text-2xl font-black text-purple-500">{{ $stats['vault'] }}</p>
                <p class="text-[11px] font-bold text-stone-400 dark:text-stone-500 uppercase tracking-wider mt-0.5">Vault Items</p>
            </div>

            <div class="bg-white dark:bg-stone-900 rounded-2xl p-4 border border-stone-200/80 dark:border-stone-800 text-center shadow-sm">
                <div class="w-8 h-8 rounded-xl bg-emerald-50 dark:bg-emerald-950/40 text-emerald-500 mx-auto flex items-center justify-center mb-1">
                    <i data-lucide="award" class="w-4 h-4"></i>
                </div>
                <p class="text-2xl font-black text-emerald-500">{{ $stats['preservation_score'] }}%</p>
                <p class="text-[11px] font-bold text-stone-400 dark:text-stone-500 uppercase tracking-wider mt-0.5">Preserve</p>
            </div>

        </div>

        {{-- Filters Bar (Zero Scrollbars) --}}
        <div class="bg-white dark:bg-stone-900 rounded-2xl sm:rounded-3xl p-2 sm:p-3 border border-stone-200/80 dark:border-stone-800 shadow-sm">
            <div class="flex items-center gap-1.5 sm:gap-2 overflow-x-auto no-scrollbar scrollbar-none py-0.5" style="scrollbar-width: none; -ms-overflow-style: none;">
                
                <button type="button" @click="currentFilter = 'all'"
                    :class="currentFilter === 'all' ? 'bg-amber-500 text-white shadow-md' : 'text-stone-600 dark:text-stone-400 hover:bg-stone-100 dark:hover:bg-stone-800'"
                    class="px-3.5 sm:px-4 py-2 rounded-xl text-xs sm:text-sm font-bold transition whitespace-nowrap flex items-center gap-1.5 shrink-0">
                    <i data-lucide="layers" class="w-3.5 h-3.5"></i>
                    <span>All Actions</span>
                    <span class="px-1.5 py-0.2 rounded-full text-[10px]" :class="currentFilter === 'all' ? 'bg-white/20 text-white' : 'bg-stone-100 dark:bg-stone-800 text-stone-500'">{{ $sortedActivities->count() }}</span>
                </button>

                <button type="button" @click="currentFilter = 'stories'"
                    :class="currentFilter === 'stories' ? 'bg-amber-500 text-white shadow-md' : 'text-stone-600 dark:text-stone-400 hover:bg-stone-100 dark:hover:bg-stone-800'"
                    class="px-3.5 sm:px-4 py-2 rounded-xl text-xs sm:text-sm font-bold transition whitespace-nowrap flex items-center gap-1.5 shrink-0">
                    <i data-lucide="feather" class="w-3.5 h-3.5"></i>
                    <span>Stories</span>
                    <span class="px-1.5 py-0.2 rounded-full text-[10px]" :class="currentFilter === 'stories' ? 'bg-white/20 text-white' : 'bg-stone-100 dark:bg-stone-800 text-stone-500'">{{ $stats['stories'] }}</span>
                </button>

                <button type="button" @click="currentFilter = 'traditions'"
                    :class="currentFilter === 'traditions' ? 'bg-amber-500 text-white shadow-md' : 'text-stone-600 dark:text-stone-400 hover:bg-stone-100 dark:hover:bg-stone-800'"
                    class="px-3.5 sm:px-4 py-2 rounded-xl text-xs sm:text-sm font-bold transition whitespace-nowrap flex items-center gap-1.5 shrink-0">
                    <i data-lucide="archive" class="w-3.5 h-3.5"></i>
                    <span>Traditions</span>
                    <span class="px-1.5 py-0.2 rounded-full text-[10px]" :class="currentFilter === 'traditions' ? 'bg-white/20 text-white' : 'bg-stone-100 dark:bg-stone-800 text-stone-500'">{{ $stats['contributions'] }}</span>
                </button>

                <button type="button" @click="currentFilter = 'chapters'"
                    :class="currentFilter === 'chapters' ? 'bg-amber-500 text-white shadow-md' : 'text-stone-600 dark:text-stone-400 hover:bg-stone-100 dark:hover:bg-stone-800'"
                    class="px-3.5 sm:px-4 py-2 rounded-xl text-xs sm:text-sm font-bold transition whitespace-nowrap flex items-center gap-1.5 shrink-0">
                    <i data-lucide="bookmark" class="w-3.5 h-3.5"></i>
                    <span>Life Chapters</span>
                </button>

                <button type="button" @click="currentFilter = 'communities'"
                    :class="currentFilter === 'communities' ? 'bg-amber-500 text-white shadow-md' : 'text-stone-600 dark:text-stone-400 hover:bg-stone-100 dark:hover:bg-stone-800'"
                    class="px-3.5 sm:px-4 py-2 rounded-xl text-xs sm:text-sm font-bold transition whitespace-nowrap flex items-center gap-1.5 shrink-0">
                    <i data-lucide="users" class="w-3.5 h-3.5"></i>
                    <span>Communities</span>
                    <span class="px-1.5 py-0.2 rounded-full text-[10px]" :class="currentFilter === 'communities' ? 'bg-white/20 text-white' : 'bg-stone-100 dark:bg-stone-800 text-stone-500'">{{ $stats['communities'] }}</span>
                </button>

                <button type="button" @click="currentFilter = 'events'"
                    :class="currentFilter === 'events' ? 'bg-amber-500 text-white shadow-md' : 'text-stone-600 dark:text-stone-400 hover:bg-stone-100 dark:hover:bg-stone-800'"
                    class="px-3.5 sm:px-4 py-2 rounded-xl text-xs sm:text-sm font-bold transition whitespace-nowrap flex items-center gap-1.5 shrink-0">
                    <i data-lucide="calendar" class="w-3.5 h-3.5"></i>
                    <span>Gatherings</span>
                    <span class="px-1.5 py-0.2 rounded-full text-[10px]" :class="currentFilter === 'events' ? 'bg-white/20 text-white' : 'bg-stone-100 dark:bg-stone-800 text-stone-500'">{{ $stats['events'] }}</span>
                </button>

                <button type="button" @click="currentFilter = 'vault'"
                    :class="currentFilter === 'vault' ? 'bg-amber-500 text-white shadow-md' : 'text-stone-600 dark:text-stone-400 hover:bg-stone-100 dark:hover:bg-stone-800'"
                    class="px-3.5 sm:px-4 py-2 rounded-xl text-xs sm:text-sm font-bold transition whitespace-nowrap flex items-center gap-1.5 shrink-0">
                    <i data-lucide="lock" class="w-3.5 h-3.5"></i>
                    <span>Vault</span>
                    <span class="px-1.5 py-0.2 rounded-full text-[10px]" :class="currentFilter === 'vault' ? 'bg-white/20 text-white' : 'bg-stone-100 dark:bg-stone-800 text-stone-500'">{{ $stats['vault'] }}</span>
                </button>

            </div>
        </div>

        {{-- CHRONOLOGICAL ACTIVITY STREAM --}}
        <div class="space-y-3 sm:space-y-4">
            @if($sortedActivities->count() > 0)
                <div class="space-y-3">
                    @foreach($sortedActivities as $activity)
                        <div x-show="currentFilter === 'all' || currentFilter === '{{ $activity['type'] }}'"
                            class="bg-white dark:bg-stone-900 rounded-2xl sm:rounded-3xl p-4 sm:p-5 border border-stone-200/80 dark:border-stone-800 shadow-sm flex items-start gap-3 sm:gap-4 transition hover:border-amber-500/40">
                            
                            {{-- Type Icon Badge --}}
                            <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-xl sm:rounded-2xl shrink-0 flex items-center justify-center font-bold
                                @if($activity['type'] === 'stories') bg-amber-100 dark:bg-amber-950/60 text-amber-600 dark:text-amber-400
                                @elseif($activity['type'] === 'traditions') bg-orange-100 dark:bg-orange-950/60 text-orange-600 dark:text-orange-400
                                @elseif($activity['type'] === 'chapters') bg-amber-500 text-white
                                @elseif($activity['type'] === 'communities') bg-blue-100 dark:bg-blue-950/60 text-blue-600 dark:text-blue-400
                                @elseif($activity['type'] === 'events') bg-purple-100 dark:bg-purple-950/60 text-purple-600 dark:text-purple-400
                                @else bg-rose-100 dark:bg-rose-950/60 text-rose-600 dark:text-rose-400 @endif">
                                <i data-lucide="{{ $activity['icon'] }}" class="w-5 h-5 sm:w-6 sm:h-6"></i>
                            </div>

                            {{-- Content Body --}}
                            <div class="flex-1 min-w-0 space-y-1">
                                <div class="flex flex-wrap items-center justify-between gap-1.5">
                                    <div class="flex items-center gap-2 flex-wrap">
                                        <span class="text-[10px] font-black uppercase tracking-wider px-2 py-0.5 rounded-full bg-stone-100 dark:bg-stone-800 text-stone-600 dark:text-stone-300">
                                            {{ $activity['type_label'] }}
                                        </span>
                                        @if(!empty($activity['badge']))
                                            <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-amber-50 dark:bg-amber-950/40 text-amber-600 dark:text-amber-400 border border-amber-200 dark:border-amber-900">
                                                {{ $activity['badge'] }}
                                            </span>
                                        @endif
                                        @if(!empty($activity['status']))
                                            <span class="text-[10px] font-bold px-2 py-0.5 rounded-full
                                                {{ $activity['status'] === 'approved' ? 'bg-emerald-50 text-emerald-600 border border-emerald-200' : 'bg-amber-50 text-amber-600 border border-amber-200' }}">
                                                {{ ucfirst($activity['status']) }}
                                            </span>
                                        @endif
                                    </div>
                                    <span class="text-[11px] text-stone-400 font-medium">
                                        {{ $activity['created_at']->diffForHumans() }}
                                    </span>
                                </div>

                                <h3 class="font-bold text-sm sm:text-base text-stone-900 dark:text-white">
                                    {{ $activity['title'] }}
                                </h3>

                                @if(!empty($activity['description']))
                                    <p class="text-xs sm:text-sm text-stone-600 dark:text-stone-300 line-clamp-2 leading-relaxed">
                                        {{ $activity['description'] }}
                                    </p>
                                @endif

                                <div class="pt-2 flex items-center justify-between text-xs">
                                    <span class="text-stone-400 text-[11px]">
                                        {{ $activity['metrics'] ?? $activity['created_at']->format('M j, Y') }}
                                    </span>
                                    <a href="{{ $activity['url'] }}" class="font-bold text-amber-600 dark:text-amber-400 hover:underline flex items-center gap-1 text-[11px] sm:text-xs">
                                        View Details <i data-lucide="arrow-right" class="w-3 h-3"></i>
                                    </a>
                                </div>
                            </div>

                        </div>
                    @endforeach
                </div>
            @else
                <div class="bg-white dark:bg-stone-900 rounded-3xl p-12 text-center border border-stone-200/80 dark:border-stone-800 space-y-3">
                    <div class="w-16 h-16 rounded-3xl bg-amber-50 dark:bg-amber-950/40 text-amber-500 mx-auto flex items-center justify-center">
                        <i data-lucide="compass" class="w-8 h-8"></i>
                    </div>
                    <h3 class="text-lg font-bold text-stone-900 dark:text-white">No Activities Recorded Yet</h3>
                    <p class="text-xs sm:text-sm text-stone-500 max-w-sm mx-auto">
                        Your preservation journey starts by documenting your first story, community gathering, or cultural tradition.
                    </p>
                </div>
            @endif
        </div>

    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        if (typeof lucide !== 'undefined') {
            lucide.createIcons();
        }
    });
</script>
@endpush
