@extends('layouts.app')

@section('content')
    <div class="max-w-7xl mx-auto p-6 space-y-8">

        <!-- HEADER -->
        <div>
            <h1 class="page-title text-2xl font-bold text-amber-500">Story Analytics</h1>
            <p class="text-stone-500 dark:text-stone-400 text-sm">Insights into your cultural impact and storytelling reach.
            </p>
        </div>

        <!-- METRIC CARDS -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-4">

            <!-- Total Stories -->
            <div class="bg-white dark:bg-stone-900 rounded-2xl shadow-md border border-stone-100 dark:border-stone-800 p-4">
                <div class="flex items-center gap-3">
                    <i data-lucide="file-text" class="w-6 h-6 text-amber-500"></i>
                    <p class="text-sm font-medium text-stone-600 dark:text-stone-400">Total Stories</p>
                </div>
                <p class="text-2xl font-bold text-stone-900 dark:text-stone-100 mt-2">{{ $stats['total_stories'] }}</p>
                <p class="text-xs text-amber-600 dark:text-amber-500 mt-1">+12%</p>
            </div>

            <!-- TAPs Received -->
            <div class="bg-white dark:bg-stone-900 rounded-2xl shadow-md border border-stone-100 dark:border-stone-800 p-4">
                <div class="flex items-center gap-3">
                    <i data-lucide="heart" class="w-6 h-6 text-rose-500"></i>
                    <p class="text-sm font-medium text-stone-600 dark:text-stone-400">TAPs Received</p>
                </div>
                <p class="text-2xl font-bold text-stone-900 dark:text-stone-100 mt-2">{{ $stats['taps_received'] }}</p>
                <p class="text-xs text-rose-600 dark:text-rose-500 mt-1">+23%</p>
            </div>

            <!-- Locked-in Connections -->
            <div class="bg-white dark:bg-stone-900 rounded-2xl shadow-md border border-stone-100 dark:border-stone-800 p-4">
                <div class="flex items-center gap-3">
                    <i data-lucide="user-check" class="w-6 h-6 text-green-500"></i>
                    <p class="text-sm font-medium text-stone-600 dark:text-stone-400">Locked-in Connections</p>
                </div>
                <p class="text-2xl font-bold text-stone-900 dark:text-stone-100 mt-2">{{ $stats['locked_in_connections'] }}
                </p>
                <p class="text-xs text-green-600 dark:text-green-500 mt-1">+8%</p>
            </div>

            <!-- Story Views -->
            <div class="bg-white dark:bg-stone-900 rounded-2xl shadow-md border border-stone-100 dark:border-stone-800 p-4">
                <div class="flex items-center gap-3">
                    <i data-lucide="eye" class="w-6 h-6 text-violet-500"></i>
                    <p class="text-sm font-medium text-stone-600 dark:text-stone-400">Story Views</p>
                </div>
                <p class="text-2xl font-bold text-stone-900 dark:text-stone-100 mt-2">{{ $stats['story_views'] }}</p>
                <p class="text-xs text-violet-600 dark:text-violet-500 mt-1">+15%</p>
            </div>

            <!-- Resonance Points -->
            <div class="bg-white dark:bg-stone-900 rounded-2xl shadow-md border border-stone-100 dark:border-stone-800 p-4">
                <div class="flex items-center gap-3">
                    <i data-lucide="message-circle" class="w-6 h-6 text-yellow-500"></i>
                    <p class="text-sm font-medium text-stone-600 dark:text-stone-400">Resonance Points</p>
                </div>
                <p class="text-2xl font-bold text-stone-900 dark:text-stone-100 mt-2">{{ $stats['resonance_points'] }}</p>
                <p class="text-xs text-yellow-600 dark:text-yellow-500 mt-1">+34%</p>
            </div>

            <!-- Cultural Contributions -->
            <div class="bg-white dark:bg-stone-900 rounded-2xl shadow-md border border-gray-100 dark:border-stone-800 p-4">
                <div class="flex items-center gap-3">
                    <i data-lucide="globe" class="w-6 h-6 text-gray-500 dark:text-stone-400"></i>
                    <p class="text-sm font-medium text-gray-600 dark:text-stone-400">Cultural Contributions</p>
                </div>
                <p class="text-2xl font-bold text-gray-900 dark:text-stone-100 mt-2">{{ $stats['cultural_contributions'] }}
                </p>
                <p class="text-xs text-gray-600 dark:text-stone-500 mt-1">+67%</p>
            </div>
        </div>

        <!-- MAIN GRID -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

            <!-- TOP PERFORMING STORIES -->
            <div class="bg-white dark:bg-stone-900 rounded-2xl p-6 shadow border border-stone-100 dark:border-stone-800">
                <h2 class="text-lg font-semibold text-stone-800 dark:text-stone-100 mb-4 flex items-center gap-2">
                    <i data-lucide="flame" class="w-5 h-5 text-amber-500"></i>
                    Top Performing Stories
                </h2>
                <div class="space-y-4">
                    @foreach($topStories as $story)
                        <div
                            class="flex items-center justify-between p-3 rounded-lg border border-stone-100 dark:border-stone-800 hover:bg-stone-50 dark:hover:bg-stone-800/50 transition">
                            <div>
                                <p class="text-sm font-medium text-stone-800 dark:text-stone-200">{{ $story['title'] }}</p>
                                <div class="flex gap-4 text-xs text-stone-500 dark:text-stone-500 mt-1">
                                    <span>{{ $story['taps'] }} TAPs</span>
                                    <span>{{ $story['resonance'] }} Resonance</span>
                                    <span>{{ $story['views'] }} Views</span>
                                </div>
                            </div>
                            <p class="text-xs text-stone-400 dark:text-stone-600">{{ $story['date'] }}</p>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- CULTURAL IMPACT -->
            <div
                class="bg-white dark:bg-stone-900 rounded-2xl p-6 shadow border border-gray-100 dark:border-stone-800 space-y-6">
                <h2 class="text-lg font-semibold text-gray-800 dark:text-stone-100 mb-2 flex items-center gap-2">
                    <i data-lucide="landmark" class="w-5 h-5 text-amber-500"></i>
                    Cultural Impact
                </h2>

                <!-- LIST -->
                <div class="space-y-3">
                    @foreach($culturalImpact as $impact)
                        <div
                            class="p-4 border border-yellow-100 dark:border-amber-900/30 rounded-lg bg-yellow-50 dark:bg-amber-900/10">
                            <p class="font-medium text-gray-800 dark:text-stone-200">{{ $impact['culture'] }}</p>
                            <p class="text-xs text-gray-600 dark:text-stone-500">
                                {{ $impact['contributions'] }} contributions • {{ $impact['resonance'] }} resonance
                            </p>
                        </div>
                    @endforeach
                </div>

                <!-- PRESERVATION SCORE -->
                <div
                    class="p-4 rounded-lg bg-green-50 dark:bg-green-900/10 border border-green-100 dark:border-green-900/30">
                    <p class="font-medium text-gray-800 dark:text-stone-200 flex items-center gap-2">
                        <i data-lucide="shield-check" class="w-5 h-5 text-green-600 dark:text-green-500"></i>
                        Heritage Preservation Score
                    </p>
                    <p class="text-2xl font-bold text-green-700 dark:text-green-500 mt-1">{{ $preservationScore }}/100</p>
                    <p class="text-xs text-green-600 dark:text-green-500">Exceptional cultural preservation impact</p>
                </div>
            </div>
        </div>

        <!-- ENGAGEMENT OVER TIME -->
        <div class="bg-white dark:bg-stone-900 rounded-2xl p-6 shadow border border-stone-100 dark:border-stone-800">
            <h2 class="text-lg font-semibold text-stone-800 dark:text-stone-100 mb-4 flex items-center gap-2">
                <i data-lucide="activity" class="w-5 h-5 text-amber-500"></i>
                Engagement Over Time
            </h2>
            <div id="engagementChart" class="h-64 flex items-center justify-center text-stone-400 dark:text-stone-600">
                Interactive engagement chart will be displayed here
            </div>
        </div>
    </div>
@endsection