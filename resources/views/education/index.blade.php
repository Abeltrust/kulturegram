@extends('layouts.app')

@section('title', 'Educational Hub - Kulturegram')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
    
    {{-- Hero Section --}}
    <div class="relative rounded-3xl overflow-hidden bg-stone-900 border border-stone-800 shadow-2xl mb-12">
        <div class="absolute inset-0 opacity-40">
            <div class="absolute inset-0 bg-gradient-to-r from-stone-900 via-stone-900/40 to-transparent z-10"></div>
            {{-- We could use a specific education background image here if available --}}
            <img src="https://images.unsplash.com/photo-1524178232363-1fb2b075b655?auto=format&fit=crop&q=80&w=2000" 
                 class="w-full h-full object-cover" 
                 alt="Hub Background"
                 loading="lazy">
        </div>
        
        <div class="relative z-20 px-8 py-16 md:py-24 max-w-2xl">
            <h1 class="text-4xl md:text-6xl font-black text-white leading-tight mb-4 tracking-tight">
                Learn, Teach & <span class="bg-gradient-to-r from-amber-400 to-orange-500 bg-clip-text text-transparent italic font-serif">Celebrate Culture</span>
            </h1>
            <p class="text-stone-300 text-lg md:text-xl leading-relaxed mb-8">
                Host traditional rituals, join professional trainings, or attend cultural lectures. The hub for preservation and growth.
            </p>
            <div class="flex flex-wrap gap-4">
                <a href="{{ route('education.classes.create') }}" class="px-8 py-4 bg-gradient-to-r from-amber-500 to-orange-600 text-white rounded-2xl font-black uppercase tracking-wider shadow-lg shadow-amber-500/20 hover:scale-105 transition-transform flex items-center gap-2">
                    <i data-lucide="video" class="w-4 h-4"></i>
                    <span>Teach a Class</span>
                </a>
                <a href="{{ route('education.classes.index') }}" class="px-8 py-4 bg-white/10 backdrop-blur-md border border-white/20 text-white rounded-2xl font-black uppercase tracking-wider hover:bg-white/20 transition-all flex items-center gap-2">
                    <i data-lucide="languages" class="w-4 h-4"></i>
                    <span>Language & Heritage Studio</span>
                </a>
                <a href="#browse-events" class="px-6 py-4 text-stone-400 hover:text-white font-bold text-sm flex items-center gap-1 transition">
                    <span>Live Gatherings</span>
                    <i data-lucide="arrow-down" class="w-3.5 h-3.5"></i>
                </a>
            </div>
        </div>
    </div>

    {{-- Cultural Classes & Language Studio Showcase Section --}}
    <div class="mb-14 space-y-6">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div>
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-amber-500/10 border border-amber-500/20 text-amber-500 text-xs font-bold mb-2">
                    <i data-lucide="sparkles" class="w-3.5 h-3.5"></i>
                    <span>AI Subtitles & Bilingual Vocab</span>
                </div>
                <h2 class="text-2xl sm:text-3xl font-black text-stone-800 dark:text-stone-100 tracking-tight">
                    Cultural Classes & Language Studio
                </h2>
                <p class="text-stone-500 dark:text-stone-400 text-xs sm:text-sm mt-0.5">
                    Watch custodians and instructors teach Hausa, Yoruba, Igbo, and ancestral traditions with synchronized subtitles.
                </p>
            </div>

            <div class="flex items-center gap-3">
                <a href="{{ route('education.classes.index') }}" 
                   class="text-xs sm:text-sm font-bold text-amber-600 dark:text-amber-500 hover:underline flex items-center gap-1">
                    <span>View All Classes</span>
                    <i data-lucide="chevron-right" class="w-4 h-4"></i>
                </a>
            </div>
        </div>

        @if(isset($featuredClasses) && $featuredClasses->count() > 0)
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($featuredClasses as $cls)
                    <div class="group bg-white dark:bg-stone-900 rounded-3xl overflow-hidden border border-stone-200 dark:border-stone-800 shadow-sm hover:shadow-xl transition-all duration-300 flex flex-col h-full hover:border-amber-500/40">
                        {{-- Thumbnail Container --}}
                        <div class="relative h-48 overflow-hidden bg-stone-950">
                            @if($cls->thumbnail_url)
                                <img src="{{ Str::startsWith($cls->thumbnail_url, ['http://', 'https://']) ? $cls->thumbnail_url : asset('storage/' . $cls->thumbnail_url) }}" 
                                     class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" 
                                     alt="{{ $cls->title }}"
                                     loading="lazy">
                            @else
                                <div class="w-full h-full flex flex-col items-center justify-center bg-gradient-to-br from-stone-800 to-stone-950 p-6 text-center">
                                    <i data-lucide="languages" class="w-10 h-10 text-amber-500/60 mb-2"></i>
                                    <span class="text-xs font-bold text-stone-400">{{ $cls->cultural_focus }}</span>
                                </div>
                            @endif

                            <div class="absolute top-3 left-3 flex flex-wrap gap-1.5 z-10">
                                @if($cls->target_language)
                                    <span class="bg-stone-900/90 backdrop-blur-md px-2.5 py-1 rounded-full text-[10px] font-black uppercase tracking-wider text-amber-400 border border-amber-500/30">
                                        {{ $cls->target_language }}
                                    </span>
                                @endif
                            </div>

                            <div class="absolute top-3 right-3 z-10">
                                @if($cls->isFree())
                                    <span class="bg-emerald-500/95 backdrop-blur-md px-2.5 py-1 rounded-full text-[10px] font-black uppercase text-white shadow-sm">
                                        Free
                                    </span>
                                @else
                                    <span class="bg-amber-500/95 backdrop-blur-md px-2.5 py-1 rounded-full text-[10px] font-black uppercase text-white shadow-sm">
                                        ${{ number_format($cls->price, 2) }}
                                    </span>
                                @endif
                            </div>

                            <a href="{{ route('education.classes.show', $cls->slug) }}" 
                               class="absolute inset-0 bg-stone-900/30 group-hover:bg-stone-900/50 flex items-center justify-center transition-all opacity-0 group-hover:opacity-100">
                                <div class="w-12 h-12 rounded-full bg-amber-500 text-white flex items-center justify-center shadow-lg transform group-hover:scale-110 transition-transform">
                                    <i data-lucide="play" class="w-5 h-5 fill-current ml-0.5"></i>
                                </div>
                            </a>
                        </div>

                        {{-- Details --}}
                        <div class="p-5 flex-1 flex flex-col">
                            <div class="text-[10px] font-black uppercase tracking-wider text-amber-600 dark:text-amber-500 mb-1">
                                {{ $cls->cultural_focus }}
                            </div>
                            <h3 class="text-base font-black text-stone-800 dark:text-stone-100 group-hover:text-amber-500 transition line-clamp-1 mb-2">
                                <a href="{{ route('education.classes.show', $cls->slug) }}">
                                    {{ $cls->title }}
                                </a>
                            </h3>
                            <p class="text-xs text-stone-500 dark:text-stone-400 line-clamp-2 mb-4">
                                {{ $cls->description ?: $cls->ai_summary }}
                            </p>

                            <div class="mt-auto pt-3 border-t border-stone-100 dark:border-stone-800 flex items-center justify-between text-xs">
                                <div class="flex items-center gap-2">
                                    <img src="{{ $cls->instructor->profile_photo_url }}" class="w-6 h-6 rounded-full object-cover">
                                    <span class="font-bold text-stone-700 dark:text-stone-300 truncate max-w-[120px]">{{ $cls->instructor->name }}</span>
                                </div>
                                <a href="{{ route('education.classes.show', $cls->slug) }}" class="text-amber-600 dark:text-amber-500 font-bold hover:underline flex items-center gap-1">
                                    <span>Watch</span>
                                    <i data-lucide="chevron-right" class="w-3.5 h-3.5"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="p-8 rounded-3xl bg-stone-50 dark:bg-stone-900/50 border border-dashed border-stone-200 dark:border-stone-800 flex flex-col sm:flex-row items-center justify-between gap-6">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 rounded-2xl bg-amber-500/10 text-amber-500 flex items-center justify-center shrink-0">
                        <i data-lucide="video" class="w-6 h-6"></i>
                    </div>
                    <div>
                        <h4 class="text-sm font-black text-stone-800 dark:text-stone-100">Ready to Teach a Heritage Language or Tradition?</h4>
                        <p class="text-xs text-stone-500 dark:text-stone-400">Post a class with AI-synchronized subtitles, bilingual flashcards, and virtual backdrops.</p>
                    </div>
                </div>
                <a href="{{ route('education.classes.create') }}" class="px-5 py-2.5 rounded-xl bg-amber-500 hover:bg-amber-600 text-white font-bold text-xs transition shadow-md whitespace-nowrap">
                    Teach a Cultural Class
                </a>
            </div>
        @endif
    </div>

    {{-- Category Filters for Live Events --}}
    <div id="browse-events" class="flex flex-wrap items-center gap-3 mb-10 overflow-x-auto pb-4 scrollbar-hide">
        <div class="w-full text-xs font-black uppercase tracking-wider text-stone-400 mb-1">
            Live Gatherings & Rituals
        </div>
        @php
            $currentType = request('type', 'all');
            $categories = [
                'all' => ['label' => 'All Events', 'icon' => 'layout'],
                'class' => ['label' => 'Classes', 'icon' => 'book-open'],
                'training' => ['label' => 'Trainings', 'icon' => 'award'],
                'ritual' => ['label' => 'Rituals', 'icon' => 'sparkles'],
                'wedding' => ['label' => 'Weddings', 'icon' => 'heart'],
                'ceremony' => ['label' => 'Ceremonies', 'icon' => 'sun'],
                'workshop' => ['label' => 'Workshops', 'icon' => 'hammer'],
            ];
        @endphp

        @foreach($categories as $key => $cat)
            <a href="{{ route('education.index', ['type' => $key]) }}#browse-events" 
               class="flex items-center gap-2 px-5 py-3 rounded-2xl border transition-all whitespace-nowrap font-bold text-sm
               {{ $currentType === $key 
                  ? 'bg-amber-500 border-amber-500 text-white shadow-md shadow-amber-500/20' 
                  : 'bg-white dark:bg-stone-900 border-stone-200 dark:border-stone-800 text-stone-600 dark:text-stone-400 hover:border-amber-300 dark:hover:border-amber-700' }}">
                <i data-lucide="{{ $cat['icon'] }}" class="w-4 h-4"></i>
                {{ $cat['label'] }}
            </a>
        @endforeach
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-4 gap-10">
        {{-- Main Events Grid --}}
        <div class="lg:col-span-3">
            @if($events->count() > 0)
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    @foreach($events as $event)
                        <div class="group bg-white dark:bg-stone-900 rounded-3xl overflow-hidden border border-stone-100 dark:border-stone-800 shadow-sm hover:shadow-xl transition-all duration-300 flex flex-col h-full">
                            {{-- Image Container --}}
                            <div class="relative h-56 overflow-hidden bg-stone-100 dark:bg-stone-800">
                                @if($event->image)
                                    <img src="{{ asset('storage/' . $event->image) }}" 
                                         class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" 
                                         alt="{{ $event->title }}"
                                         loading="lazy">
                                @else
                                    <div class="w-full h-full flex items-center justify-center text-stone-300">
                                        <i data-lucide="image" class="w-12 h-12"></i>
                                    </div>
                                @endif
                                
                                {{-- Badges --}}
                                <div class="absolute top-4 left-4 flex gap-2">
                                    <span class="bg-white/90 dark:bg-stone-900/90 backdrop-blur-md px-3 py-1 rounded-full text-[10px] font-black uppercase text-stone-800 dark:text-stone-100 shadow-sm border border-stone-100 dark:border-stone-800">
                                        {{ ucfirst($event->type) }}
                                    </span>
                                    @if($event->price == 0)
                                        <span class="bg-green-500 px-3 py-1 rounded-full text-[10px] font-black uppercase text-white shadow-sm border border-green-400">
                                            Free
                                        </span>
                                    @else
                                        <span class="bg-amber-500 px-3 py-1 rounded-full text-[10px] font-black uppercase text-white shadow-sm border border-amber-400">
                                            ${{ number_format($event->price, 2) }}
                                        </span>
                                    @endif
                                </div>
                            </div>

                            {{-- Content --}}
                            <div class="p-6 flex-1 flex flex-col">
                                <h3 class="text-xl font-black text-stone-800 dark:text-stone-100 group-hover:text-amber-600 transition-colors line-clamp-1 mb-2">
                                    {{ $event->title }}
                                </h3>
                                <p class="text-stone-500 dark:text-stone-400 text-sm line-clamp-2 mb-6 leading-relaxed">
                                    {{ $event->description }}
                                </p>

                                <div class="mt-auto space-y-4">
                                    <div class="flex items-center gap-3 text-xs font-bold text-stone-600 dark:text-stone-400 uppercase tracking-wider bg-stone-50 dark:bg-stone-800/50 p-3 rounded-xl">
                                        <div class="flex items-center gap-1.5 border-r border-stone-200 dark:border-stone-700 pr-3">
                                            <i data-lucide="calendar" class="w-3.5 h-3.5 text-amber-500"></i>
                                            {{ $event->event_date->format('M d') }}
                                        </div>
                                        <div class="flex items-center gap-1.5">
                                            <i data-lucide="clock" class="w-3.5 h-3.5 text-amber-500"></i>
                                            {{ $event->event_time->format('g:i A') }}
                                        </div>
                                    </div>

                                    <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-2">
                                        <img src="{{ $event->organizer->profile_photo_url }}" 
                                             class="w-7 h-7 rounded-full border border-stone-100 dark:border-stone-800" 
                                             alt="{{ $event->organizer->name }}"
                                             loading="lazy">
                                        <span class="text-xs font-bold text-stone-700 dark:text-stone-300">{{ $event->organizer->name }}</span>
                                    </div>
                                        <a href="{{ route('events.show', $event) }}" class="text-sm font-black uppercase tracking-widest text-amber-600 dark:text-amber-500 hover:text-orange-600 transition flex items-center gap-1">
                                            View <i data-lucide="chevron-right" class="w-4 h-4"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
                
                <div class="mt-12">
                    {{ $events->links() }}
                </div>
            @else
                <div class="bg-white dark:bg-stone-900 rounded-3xl border border-stone-100 dark:border-stone-800 py-24 text-center">
                    <div class="w-20 h-20 bg-stone-50 dark:bg-stone-800 rounded-full flex items-center justify-center mx-auto mb-6">
                        <i data-lucide="search-slash" class="w-10 h-10 text-stone-300"></i>
                    </div>
                    <h3 class="text-2xl font-black text-stone-800 dark:text-stone-100 mb-2">No educational events yet</h3>
                    <p class="text-stone-500 dark:text-stone-400 max-w-sm mx-auto">
                        We couldn't find any upcoming classes or rituals in this category. Why not host one yourself?
                    </p>
                    <a href="{{ route('events.create') }}" class="inline-block mt-8 px-8 py-3 bg-stone-800 dark:bg-white text-white dark:text-stone-900 rounded-xl font-bold transition hover:scale-105">
                        Create Event
                    </a>
                </div>
            @endif
        </div>

        {{-- Sidebar: Experts & Info --}}
        <div class="space-y-8">
            {{-- Contributions Help Card --}}
            <div class="bg-gradient-to-br from-amber-500 to-orange-600 rounded-3xl p-6 text-white shadow-xl shadow-amber-500/20">
                <i data-lucide="sparkles" class="w-10 h-10 mb-4 opacity-50"></i>
                <h4 class="text-xl font-black mb-2 leading-tight">Support Our Educators</h4>
                <p class="text-amber-50 text-sm leading-relaxed mb-6">
                    You can now send financial contributions directly to hosts to support their cultural preservation efforts.
                </p>
                <div class="flex items-center gap-2 text-xs font-black uppercase tracking-widest bg-white/20 backdrop-blur-md rounded-xl py-2 px-4 w-fit">
                    <i data-lucide="info" class="w-3.5 h-3.5"></i> Look for the "Contribute" button
                </div>
            </div>

            {{-- Top Experts --}}
            <div class="bg-white dark:bg-stone-900 rounded-3xl border border-stone-100 dark:border-stone-800 p-6 shadow-sm">
                <h4 class="text-lg font-black text-stone-800 dark:text-stone-100 mb-6 flex items-center gap-2">
                    <i data-lucide="award" class="w-5 h-5 text-amber-500"></i> Top Educators
                </h4>
                <div class="space-y-6">
                    @forelse($experts as $expert)
                        <div class="flex items-center gap-4">
                            <img src="{{ $expert->profile_photo_url }}" class="w-12 h-12 rounded-2xl object-cover border border-stone-100 dark:border-stone-800" alt="{{ $expert->name }}">
                            <div class="flex-1">
                                <h5 class="text-sm font-black text-stone-800 dark:text-stone-100">{{ $expert->name }}</h5>
                                <p class="text-[11px] font-bold text-stone-500 uppercase tracking-wider">{{ $expert->events_count }} classes hosted</p>
                            </div>
                            <a href="{{ route('profile.user', $expert) }}" class="p-2 bg-stone-50 dark:bg-stone-800 rounded-lg text-stone-400 hover:text-amber-500 transition">
                                <i data-lucide="chevron-right" class="w-4 h-4"></i>
                            </a>
                        </div>
                    @empty
                        <p class="text-sm text-stone-500 italic">No experts registered yet.</p>
                    @endforelse
                </div>
            </div>

            {{-- What is Educational Hub? --}}
            <div class="bg-stone-50 dark:bg-stone-800/30 rounded-3xl p-6 border border-stone-100 dark:border-stone-800">
                <h4 class="text-xs font-black text-stone-400 uppercase tracking-widest mb-4">About the Hub</h4>
                <p class="text-sm text-stone-600 dark:text-stone-400 leading-relaxed italic">
                    "Knowledge shared is culture preserved. Our hub connects wisdom keepers with the next generation through structured learning and sacred ceremonies."
                </p>
            </div>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
    document.addEventListener("DOMContentLoaded", () => {
        if(typeof lucide !== 'undefined') {
            lucide.createIcons();
        }
    });
</script>
<style>
    .scrollbar-hide::-webkit-scrollbar {
        display: none;
    }
    .scrollbar-hide {
        -ms-overflow-style: none;
        scrollbar-width: none;
    }
</style>
@endpush
