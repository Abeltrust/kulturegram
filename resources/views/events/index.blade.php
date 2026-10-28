{{-- resources/views/events/index.blade.php --}}
@extends('layouts.app')

@section('content')
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-12">

        {{-- Page Header --}}
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <h1 class="text-2xl sm:text-3xl lg:text-4xl font-extrabold text-gray-900 dark:text-stone-100 tracking-tight">
                Cultural Events</h1>

            {{-- Search --}}
            <form action="{{ route('events.index') }}" method="GET"
                class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2 w-full sm:w-80">
                <div class="relative flex-1">
                    <i data-lucide="search" class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-stone-400"></i>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Find your tribe..."
                        class="w-full pl-10 pr-4 py-2 bg-stone-100 dark:bg-stone-800 border-none rounded-xl focus:ring-2 focus:ring-amber-500 focus:outline-none text-sm trans-all">
                </div>
            </form>
        </div>

        {{-- AI-Recommended Cultural Events --}}
        @if(isset($recommendedEvents) && $recommendedEvents->count() > 0)
            <section class="bg-gradient-to-r from-amber-500/10 via-stone-900/5 to-emerald-500/10 dark:from-amber-950/20 dark:via-stone-900 dark:to-emerald-950/20 rounded-3xl p-6 sm:p-8 border border-amber-500/20 dark:border-amber-500/10 shadow-sm">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="px-2.5 py-0.5 rounded-full text-xs font-black uppercase tracking-wider bg-amber-500/20 text-amber-500 border border-amber-500/30 flex items-center gap-1">
                                <i data-lucide="sparkles" class="w-3.5 h-3.5"></i>
                                AI-Curated For You
                            </span>
                            <span class="text-xs text-stone-500">Voluntary cultural interest matching</span>
                        </div>
                        <h2 class="text-xl sm:text-2xl font-black page-title text-amber-500 mt-1">Recommended Cultural Gatherings</h2>
                    </div>
                    <a href="{{ route('events.create') }}" class="px-4 py-2 rounded-xl bg-amber-600 hover:bg-amber-500 text-white font-bold text-xs shadow transition flex items-center gap-1.5 w-fit">
                        <i data-lucide="plus" class="w-4 h-4"></i> Create Event
                    </a>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
                    @foreach($recommendedEvents as $rec)
                        @php $ev = $rec['event']; @endphp
                        <div class="bg-white dark:bg-stone-900 rounded-2xl overflow-hidden border border-stone-200 dark:border-stone-800 shadow-sm hover:shadow-md transition flex flex-col justify-between group">
                            <div>
                                <div class="relative h-40 w-full overflow-hidden bg-stone-100 dark:bg-stone-800">
                                    @if(!empty($ev['image']))
                                        <img src="{{ $ev['image'] }}" alt="{{ $ev['title'] }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                                    @else
                                        <div class="w-full h-full flex items-center justify-center bg-amber-50 dark:bg-amber-950/30 text-amber-600">
                                            <i data-lucide="calendar" class="w-10 h-10"></i>
                                        </div>
                                    @endif
                                    <span class="absolute top-3 left-3 bg-stone-900/80 backdrop-blur text-white text-[10px] font-bold px-2 py-0.5 rounded-full">
                                        {{ $ev['type'] }}
                                    </span>
                                </div>
                                <div class="p-5">
                                    <h3 class="font-bold text-stone-900 dark:text-stone-100 text-base mb-1 group-hover:text-amber-600 transition-colors">
                                        <a href="{{ route('events.show', $ev['id']) }}">{{ $ev['title'] }}</a>
                                    </h3>
                                    <div class="flex items-center gap-2 text-xs text-stone-500 mb-3">
                                        <span>{{ $ev['event_date'] }}</span>
                                        <span>•</span>
                                        <span>{{ $ev['location'] }}</span>
                                    </div>

                                    {{-- Explainable Recommendation Reason --}}
                                    <div class="p-2.5 rounded-xl bg-amber-500/10 border border-amber-500/20 text-xs text-amber-900 dark:text-amber-300 flex items-start gap-2">
                                        <i data-lucide="info" class="w-4 h-4 text-amber-500 shrink-0 mt-0.5"></i>
                                        <span class="leading-snug">{{ $rec['explanation'] }}</span>
                                    </div>

                                    @if(!empty($rec['matched_tags']))
                                    <div class="flex flex-wrap gap-1 mt-2.5">
                                        @foreach($rec['matched_tags'] as $tag)
                                            <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-stone-100 dark:bg-stone-800 text-stone-600 dark:text-stone-400">#{{ $tag }}</span>
                                        @endforeach
                                    </div>
                                    @endif
                                </div>
                            </div>
                            <div class="px-5 pb-5 pt-0">
                                <a href="{{ route('events.show', $ev['id']) }}" class="w-full py-2 rounded-xl bg-stone-100 hover:bg-amber-500 hover:text-white dark:bg-stone-800 dark:hover:bg-amber-600 text-stone-700 dark:text-stone-300 font-bold text-xs text-center block transition">
                                    View Gathering Details
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>
            </section>
        @endif

        {{-- Live Now Section --}}
        @if($liveStreams->count() > 0)
            <section>
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-xl font-bold text-gray-800 dark:text-stone-100 flex items-center gap-2">
                        <span class="flex h-3 w-3 relative">
                            <span
                                class="animate-ping absolute inline-flex h-full w-full rounded-full bg-red-400 opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-3 w-3 bg-red-500"></span>
                        </span>
                        Ongoing Cultural Broadcasts
                    </h2>
                    <a href="{{ route('live-stream.index') }}"
                        class="text-sm font-bold text-amber-600 dark:text-amber-500 hover:text-amber-700">View All Streams</a>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach($liveStreams as $stream)
                        <a href="{{ route('live-stream.show', $stream) }}"
                            class="group relative aspect-video bg-stone-900 rounded-2xl overflow-hidden shadow-lg hover:scale-[1.02] transition-all duration-300">
                            <img src="{{ $stream->thumbnail ?? 'https://images.unsplash.com/photo-1514362545857-3bc16c4c7d1b?auto=format&fit=crop&q=80&w=800' }}"
                                class="w-full h-full object-cover opacity-70 group-hover:scale-110 transition-transform duration-700">
                            <div class="absolute inset-0 bg-gradient-to-t from-black via-black/20 to-transparent"></div>

                            <div class="absolute top-4 left-4 flex gap-2">
                                <span
                                    class="bg-red-600 text-white text-[10px] uppercase font-black px-2 py-0.5 rounded shadow">Live</span>
                                <span
                                    class="bg-black/40 backdrop-blur-md text-white text-[10px] px-2 py-0.5 rounded flex items-center gap-1">
                                    <i data-lucide="eye" class="w-3 h-3 text-stone-300"></i> {{ $stream->viewers_count }}
                                </span>
                            </div>

                            <div class="absolute bottom-4 left-4 right-4">
                                <h3 class="text-white font-bold text-lg leading-snug group-hover:text-amber-400 transition-colors">
                                    {{ $stream->title }}</h3>
                                <p class="text-stone-300 text-xs flex items-center gap-2 mt-1">
                                    <img src="{{ $stream->host->profile_photo_url }}"
                                        class="w-4 h-4 rounded-full border border-white/20">
                                    {{ $stream->host->name }}
                                </p>
                            </div>
                        </a>
                    @endforeach
                </div>
            </section>
        @endif

        {{-- Upcoming & All Events --}}
        <section class="space-y-6">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <h2 class="text-xl font-bold text-gray-800 dark:text-stone-100">Discovery Events</h2>

                {{-- Unified Filter Navigation --}}
                <nav class="flex p-1 bg-stone-100 dark:bg-stone-800 rounded-xl w-fit">
                    @foreach (['all' => 'Exploration', 'attending' => 'My Journey', 'hosting' => 'Organizing'] as $key => $label)
                                <a href="{{ route('events.index', array_merge(request()->except('page'), ['filter' => $key])) }}" class="px-5 py-2 rounded-lg text-xs font-bold tracking-tight uppercase transition-all
                                              {{ request('filter', 'all') === $key
                        ? 'bg-white dark:bg-stone-700 text-amber-600 dark:text-amber-400 shadow-sm'
                        : 'text-stone-500 hover:text-stone-700 dark:hover:text-stone-300' }}">
                                    {{ $label }}
                                </a>
                    @endforeach
                </nav>
            </div>

            {{-- Unified Results Grid --}}
            @if ($events->count() || $upcomingStreams->count())
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">

                    {{-- Integrated Upcoming Streams --}}
                    @foreach ($upcomingStreams as $stream)
                        <div
                            class="bg-white dark:bg-stone-900 rounded-3xl overflow-hidden shadow-sm hover:shadow-md border border-stone-100 dark:border-stone-800 flex flex-col group trans-all">
                            <div class="relative h-48 w-full overflow-hidden">
                                <span
                                    class="absolute top-4 left-4 z-10 bg-amber-500 text-white text-[10px] uppercase font-black px-2 py-1 rounded-lg shadow-lg">Upcoming
                                    Stream</span>
                                <img src="{{ $stream->thumbnail ?? 'https://images.unsplash.com/photo-1541462608141-ad60397d5873?auto=format&fit=crop&q=80&w=800' }}"
                                    class="h-full w-full object-cover group-hover:scale-110 transition-transform duration-500 opacity-90">
                            </div>
                            <div class="p-6 flex-1 flex flex-col">
                                <h3
                                    class="font-black text-gray-900 dark:text-stone-100 text-lg mb-2 group-hover:text-amber-600 transition-colors">
                                    {{ $stream->title }}</h3>
                                <div class="space-y-2 mb-6">
                                    <div
                                        class="flex items-center gap-2 text-stone-500 dark:text-stone-400 text-xs font-bold uppercase tracking-wider">
                                        <i data-lucide="calendar" class="w-3.5 h-3.5"></i>
                                        <span>{{ $stream->scheduled_at->format('M d • h:i A') }}</span>
                                    </div>
                                    <div class="flex items-center gap-2 text-stone-400 text-xs font-medium">
                                        <i data-lucide="heart" class="w-3.5 h-3.5"></i>
                                        <span>{{ $stream->category ?? 'Cultural Heritage' }}</span>
                                    </div>
                                </div>
                                <a href="{{ route('live-stream.show', $stream) }}"
                                    class="mt-auto w-full py-3 bg-stone-100 dark:bg-stone-800 hover:bg-amber-50 text-stone-600 dark:text-stone-300 font-bold text-xs uppercase tracking-widest rounded-2xl text-center trans-all">Get
                                    Notified</a>
                            </div>
                        </div>
                    @endforeach

                    {{-- Traditional Events --}}
                    @foreach ($events as $event)
                        <div
                            class="bg-white dark:bg-stone-900 rounded-3xl overflow-hidden shadow-sm hover:shadow-md border border-stone-100 dark:border-stone-800 flex flex-col group trans-all">
                            @if($event->image)
                                <div class="h-48 w-full overflow-hidden relative">
                                    <img src="{{ asset('storage/' . $event->image) }}"
                                        class="h-full w-full object-cover group-hover:scale-110 transition-transform duration-500">
                                    <div class="absolute top-4 right-4 flex flex-col items-end gap-2">
                                        <span class="bg-white/90 dark:bg-stone-900/90 backdrop-blur px-3 py-1 rounded-full text-[10px] font-black uppercase text-stone-600 dark:text-stone-300 border border-stone-200 dark:border-stone-800">{{ $event->type }}</span>
                                        @if($event->price > 0)
                                            <span class="bg-amber-500 text-white px-3 py-1 rounded-full text-[10px] font-black uppercase border border-amber-400 shadow-lg shadow-amber-500/20">${{ number_format($event->price, 2) }}</span>
                                        @else
                                            <span class="bg-green-500 text-white px-3 py-1 rounded-full text-[10px] font-black uppercase border border-green-400 shadow-lg shadow-green-500/20">Free</span>
                                        @endif
                                    </div>
                                </div>
                            @endif

                            <div class="p-6 flex-1 flex flex-col">
                                <h3
                                    class="font-black text-gray-900 dark:text-stone-100 text-xl mb-3 line-clamp-1 group-hover:text-amber-600 transition-colors">
                                    {{ $event->title }}</h3>

                                <div class="grid grid-cols-2 gap-y-4 mb-6">
                                    <div class="flex items-center gap-2.5">
                                        <div
                                            class="w-8 h-8 rounded-xl bg-amber-50 dark:bg-amber-900/20 flex items-center justify-center text-amber-600 dark:text-amber-400">
                                            <i data-lucide="calendar" class="w-4 h-4"></i>
                                        </div>
                                        <div>
                                            <p
                                                class="text-[10px] uppercase font-black text-stone-400 dark:text-stone-500 leading-none mb-1">
                                                Date</p>
                                            <p class="text-xs font-bold text-stone-700 dark:text-stone-300">
                                                {{ $event->event_date->format('M d, Y') }}</p>
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-2.5">
                                        <div
                                            class="w-8 h-8 rounded-xl bg-orange-50 dark:bg-orange-900/20 flex items-center justify-center text-orange-600 dark:text-orange-400">
                                            <i data-lucide="map-pin" class="w-4 h-4"></i>
                                        </div>
                                        <div>
                                            <p
                                                class="text-[10px] uppercase font-black text-stone-400 dark:text-stone-500 leading-none mb-1">
                                                Location</p>
                                            <p class="text-xs font-bold text-stone-700 dark:text-stone-300 line-clamp-1">
                                                {{ $event->location }}</p>
                                        </div>
                                    </div>
                                </div>

                                <div class="mt-auto flex items-center justify-between gap-4">
                                    <a href="{{ route('events.show', $event) }}"
                                        class="flex-1 py-3 glass-orange text-white font-black text-xs uppercase tracking-widest rounded-2xl text-center active:scale-95 transition-all">Attend</a>
                                    <div @click="/* share functionality */"
                                        class="w-12 h-12 rounded-2xl bg-stone-100 dark:bg-stone-800 flex items-center justify-center text-stone-500 hover:bg-stone-200 dark:hover:bg-stone-700 cursor-pointer trans-all">
                                        <i data-lucide="share-2" class="w-4 h-4"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                {{-- Pagination --}}
                <div class="mt-12 flex justify-center">
                    {{ $events->links() }}
                </div>

            @else
                <div class="py-20 text-center">
                    <div
                        class="w-20 h-20 bg-stone-100 dark:bg-stone-800 rounded-full flex items-center justify-center mx-auto mb-6 text-stone-400">
                        <i data-lucide="calendar-off" class="w-10 h-10"></i>
                    </div>
                    <h3 class="text-xl font-black text-gray-900 dark:text-stone-100">No Experiences Found</h3>
                    <p class="text-stone-500 dark:text-stone-400 mt-2 max-w-sm mx-auto">Try broadening your exploration or check
                        back later for new cultural gatherings.</p>
                </div>
            @endif
        </section>
    </div>
@endsection

@push('scripts')
    <script>
        lucide.createIcons(); // initialize lucide icons
    </script>
@endpush