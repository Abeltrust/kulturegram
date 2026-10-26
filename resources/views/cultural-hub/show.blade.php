@extends('layouts.app')

@section('title', $culture->name . ' - Cultural Hub')

@section('content')
    <div class="min-h-screen bg-stone-50 dark:bg-[#0c0c0f] text-stone-900 dark:text-white transition-colors duration-300">

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 sm:py-12">

            <!-- HEADER -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-8">

                <div class="flex items-center text-xs uppercase tracking-widest text-stone-400 space-x-2">
                    <a href="{{ route('cultural-hub.index') }}" class="hover:text-amber-500 transition">
                        Cultural Hub
                    </a>
                    <i data-lucide="chevron-right" class="w-3 h-3 opacity-40"></i>
                    <span class="text-amber-500">
                        {{ $culture->name }}
                    </span>
                </div>

                @auth
                        <button onclick="toggleCultureLockin({{ $culture->id }})"
                            class="px-5 py-2 rounded-xl text-xs font-semibold uppercase tracking-wider transition-all duration-300
                            {{ auth()->user()->hasLockedIn($culture)
                    ? 'bg-amber-500 text-white shadow-lg shadow-amber-500/20'
                    : 'bg-white dark:bg-[#15151b] border border-stone-200 dark:border-white/10 text-amber-600 dark:text-amber-400 hover:bg-stone-50 dark:hover:bg-[#1c1c23]' }}">
                            <i data-lucide="{{ auth()->user()->hasLockedIn($culture) ? 'check' : 'lock' }}"
                                class="w-4 h-4 mr-2 inline-block"></i>
                            {{ auth()->user()->hasLockedIn($culture) ? 'Locked In' : 'Lock-In Presence' }}
                        </button>
                @endauth
            </div>


            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">

                <!-- MAIN CONTENT -->
                <div class="lg:col-span-7 space-y-8">

                    <!-- HERO -->
                    <div
                        class="relative rounded-2xl overflow-hidden border border-stone-200 dark:border-white/5 shadow-xl group">

                        @if($culture->image)
                            <img src="{{ Storage::url($culture->image) }}"
                                class="w-full h-48 sm:h-64 lg:h-[340px] object-cover group-hover:scale-105 transition duration-700">
                        @else
                            <div
                                class="h-48 sm:h-64 glass-orange flex items-center justify-center">
                                <i data-lucide="globe" class="w-12 h-12 text-white"></i>
                            </div>
                        @endif

                        <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-black/20 to-transparent"></div>

                        <div class="absolute bottom-6 left-6 right-6">
                            <span
                                class="text-[10px] uppercase tracking-widest bg-amber-500 text-black font-semibold px-3 py-1 rounded-full">
                                {{ $culture->category }}
                            </span>

                            <h1 class="text-2xl sm:text-4xl font-bold mt-3">
                                {{ $culture->name }}
                            </h1>

                            <div class="flex items-center text-xs text-amber-400 mt-2 uppercase tracking-wider">
                                <i data-lucide="map-pin" class="w-4 h-4 mr-2"></i>
                                {{ $culture->region }}
                            </div>
                        </div>
                    </div>


                    <!-- DESCRIPTION -->
                    <div
                        class="bg-white dark:bg-[#141419] border border-stone-200 dark:border-white/5 p-6 sm:p-8 rounded-2xl shadow-xl hover:border-amber-500/20 transition">
                        <div class="flex items-center space-x-2 text-amber-500 mb-4 text-xs uppercase tracking-widest">
                            <i data-lucide="scroll" class="w-4 h-4"></i>
                            <span>Cultural Story</span>
                        </div>

                        <p
                            class="text-stone-700 dark:text-stone-300 leading-relaxed text-base sm:text-lg italic border-l-4 border-amber-500 pl-4">
                            "{{ $culture->description }}"
                        </p>
                    </div>


                    <!-- GALLERY -->
                    @if($culture->media_files && count($culture->media_files))
                        <div x-data="{ activeImage: '{{ Storage::url($culture->media_files[0]) }}' }"
                            class="bg-white dark:bg-[#141419] border border-stone-200 dark:border-white/5 p-6 rounded-2xl shadow-xl">

                            <h2 class="text-lg font-semibold text-amber-500 mb-4">Gallery</h2>

                            <div class="rounded-xl overflow-hidden mb-4">
                                <img :src="activeImage" class="w-full h-72 object-cover transition duration-500">
                            </div>

                            <div class="grid grid-cols-4 gap-3">
                                @foreach($culture->media_files as $file)
                                    <button @click="activeImage = '{{ Storage::url($file) }}'"
                                        class="rounded-lg overflow-hidden border border-white/10 hover:border-amber-500 transition">
                                        <img src="{{ Storage::url($file) }}" class="w-full h-16 object-cover">
                                    </button>
                                @endforeach
                            </div>
                        </div>
                    @endif


                    <!-- VIDEO -->
                    @if($culture->video_url || $culture->video_path)
                        <div
                            class="bg-white dark:bg-[#141419] border border-stone-200 dark:border-white/5 p-6 rounded-2xl shadow-xl">
                            <h2 class="text-lg font-semibold text-amber-500 mb-4">
                                Video Highlight
                            </h2>

                            <div class="aspect-video rounded-xl overflow-hidden">
                                @if($culture->video_path)
                                    <video controls class="w-full h-full">
                                        <source src="{{ Storage::url($culture->video_path) }}" type="video/mp4">
                                    </video>
                                @else
                                    <iframe src="{{ $culture->video_url }}" class="w-full h-full" allowfullscreen></iframe>
                                @endif
                            </div>
                        </div>
                    @endif


                    <!-- AUDIO -->
                    @if($culture->audio_path || $culture->audio_url)
                        <div
                            class="bg-white dark:bg-[#141419] border border-stone-200 dark:border-white/5 p-6 rounded-2xl shadow-xl">
                            <h2 class="text-lg font-semibold text-amber-500 mb-4">
                                Audio Archive
                            </h2>

                            @if($culture->audio_path)
                                <audio controls class="w-full">
                                    <source src="{{ Storage::url($culture->audio_path) }}" type="audio/mpeg">
                                </audio>
                            @else
                                <a href="{{ $culture->audio_url }}" target="_blank"
                                    class="inline-block bg-amber-500 text-black px-4 py-2 rounded-lg text-sm font-semibold hover:bg-amber-400 transition">
                                    Listen to External Audio
                                </a>
                            @endif
                        </div>
                    @endif

                </div>


                <!-- SIDEBAR -->
                <div class="lg:col-span-5 space-y-6 order-first lg:order-none">

                    <!-- STATS -->
                    <div class="glass-orange rounded-2xl p-6 shadow-xl text-white">
                        <div class="grid grid-cols-2 gap-4 text-center">
                            <div>
                                <div class="text-3xl font-bold">
                                    {{ $culture->locked_in_count }}
                                </div>
                                <div class="text-xs uppercase tracking-wider">
                                    Locked-In
                                </div>
                            </div>
                            <div>
                                <div class="text-3xl font-bold">
                                    {{ $culture->resonance_count }}
                                </div>
                                <div class="text-xs uppercase tracking-wider">
                                    Pulses
                                </div>
                            </div>
                        </div>
                    </div>


                    <!-- GUARDIAN -->
                    <div
                        class="bg-white dark:bg-[#141419] border border-stone-200 dark:border-white/5 p-6 rounded-2xl shadow-xl">
                        <h3 class="text-xs uppercase tracking-widest text-amber-500 mb-4">
                            Cultural Guardian
                        </h3>

                        <p class="font-semibold text-stone-800 dark:text-stone-100">
                            {{ $culture->submitter->name ?? 'Kulturegram Preserver' }}
                        </p>

                        <div class="mt-4 text-xs text-stone-400 space-y-2">
                            <div class="flex justify-between">
                                <span>Enshrined</span>
                                <span>{{ $culture->created_at->format('M d, Y') }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span>Archive ID</span>
                                <span>#{{ str_pad($culture->id, 5, '0', STR_PAD_LEFT) }}</span>
                            </div>
                        </div>

                        <a href="{{ route('profile.user', $culture->submitted_by) }}"
                            class="block mt-6 text-center bg-stone-900 dark:bg-white text-white dark:text-black py-2 rounded-lg text-xs font-semibold uppercase tracking-wider hover:bg-stone-800 dark:hover:bg-stone-200 transition">
                            Explore Guardian
                        </a>
                    </div>


                    <!-- LICENSE -->
                    <div
                        class="bg-white dark:bg-[#141419] border border-stone-200 dark:border-white/5 p-5 rounded-xl text-xs text-stone-500 dark:text-stone-400">
                        Licensed under
                        <span class="text-amber-500 font-semibold">
                            {{ $culture->license_type ?? 'Standard' }}
                        </span>.
                    </div>

                </div>

            </div>
        </div>
    </div>
@endsection


@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            lucide.createIcons();
        });

        function toggleCultureLockin(id) {
            fetch(`/cultural-hub/${id}/lock-in`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                }
            })
                .then(response => response.json())
                .then(data => {
                    if (data.lockedIn !== undefined) {
                        location.reload();
                    }
                });
        }
    </script>
@endpush