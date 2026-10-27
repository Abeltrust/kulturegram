<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4 md:gap-6 animate-in fade-in duration-200">
    @forelse ($data as $item)
        @if ($tab === 'stories')
            <div class="bg-white dark:bg-stone-900 rounded-xl sm:rounded-2xl shadow-sm overflow-hidden border border-stone-200 dark:border-stone-800 transition hover:shadow-md">
                <img src="{{ Storage::url($item->image) }}" class="w-full h-36 sm:h-44 md:h-48 object-cover" loading="lazy">
                <div class="p-3 sm:p-4">
                    <span
                        class="inline-block text-[10px] sm:text-xs font-medium bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-500 px-2 py-0.5 sm:py-1 rounded-full mb-2">
                        {{ $item->category ?? 'Story' }}
                    </span>
                    <h3 class="font-semibold text-stone-800 dark:text-stone-100 text-sm sm:text-base line-clamp-2">
                        {{ $item->title ?? Str::limit($item->content, 60) }}</h3>
                    <p class="text-xs sm:text-sm text-stone-500 dark:text-stone-400 line-clamp-2">{{ Str::limit($item->content, 80) }}</p>
                    <div class="mt-3 flex items-center justify-between text-xs sm:text-sm text-stone-500 dark:text-stone-500">
                        <div class="flex items-center space-x-2">
                            <span
                                class="w-5 h-5 sm:w-6 sm:h-6 flex items-center justify-center rounded-full bg-amber-500 text-white text-[10px] sm:text-xs">
                                {{ strtoupper(substr($item->user->name, 0, 1)) }}
                            </span>
                            <span class="truncate max-w-[80px] sm:max-w-[100px]">{{ $item->user->name }}</span>
                        </div>
                        <div class="flex items-center space-x-1">
                            <i data-lucide="heart" class="w-3.5 sm:w-4 h-3.5 sm:h-4 text-rose-500"></i>
                            <span>{{ $item->taps_count }}</span>
                        </div>
                    </div>
                </div>
            </div>
        @elseif ($tab === 'cultures')
            <div class="bg-white dark:bg-stone-900 rounded-xl sm:rounded-2xl shadow-sm border border-stone-200 dark:border-stone-800 p-3 sm:p-4 transition hover:shadow-md hover:border-amber-500/40">
                <div class="flex items-start justify-between gap-2 mb-2">
                    <h3 class="font-bold text-stone-800 dark:text-stone-100 text-sm sm:text-base">{{ $item->name }}</h3>
                    @if($item->locked_in_count)
                        <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-amber-500/10 text-amber-600 dark:text-amber-400 border border-amber-500/20 shrink-0">
                            {{ $item->locked_in_count }} custodians
                        </span>
                    @endif
                </div>
                <p class="text-xs sm:text-sm text-stone-500 dark:text-stone-400 line-clamp-3 leading-relaxed">{{ Str::limit($item->description, 120) }}</p>
                <div class="mt-4 pt-3 border-t border-stone-100 dark:border-stone-800 flex items-center justify-between text-xs text-stone-400">
                    <span>Cultural Record</span>
                    <a href="{{ route('cultures.show', $item) }}" class="text-amber-600 dark:text-amber-400 font-bold hover:underline flex items-center gap-1">
                        <span>Explore</span>
                        <i data-lucide="arrow-right" class="w-3 h-3"></i>
                    </a>
                </div>
            </div>
        @elseif ($tab === 'people')
            <div
                class="bg-white dark:bg-stone-900 rounded-xl sm:rounded-2xl shadow-sm border border-stone-200 dark:border-stone-800 p-3 sm:p-4 flex flex-col justify-between h-full transition hover:shadow-md hover:border-amber-500/40">
                <!-- User Info Section -->
                <a href="{{ route('profile.user', $item->id) }}"
                    class="flex items-center space-x-3 mb-3 hover:bg-stone-50 dark:hover:bg-stone-800 p-1 -m-1 rounded-lg transition">
                    <img src="{{ $item->profile_photo_url }}"
                        class="w-10 h-10 sm:w-12 sm:h-12 rounded-full object-cover border border-stone-200 dark:border-stone-800"
                        loading="lazy">
                    <div class="overflow-hidden flex-1">
                        <h3 class="font-semibold text-stone-800 dark:text-stone-100 truncate text-sm sm:text-base">{{ $item->name }}</h3>
                        <p class="text-xs sm:text-sm text-stone-500 truncate">{{ $item->bio ?? 'No bio available' }}</p>
                    </div>
                </a>

                <!-- Interactions Section -->
                <div class="flex items-center justify-between pt-3 border-t border-stone-100 dark:border-stone-800">
                    @auth
                        <!-- Vibe (Tap) Button -->
                        <button onclick="toggleUserTap({{ $item->id }})" id="user-vibe-btn-{{ $item->id }}"
                            class="flex items-center justify-center space-x-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold transition-all shadow-sm flex-1 mr-2
                                            {{ auth()->user()->hasTapped($item)
                                ? 'bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-500 hover:bg-amber-200'
                                : 'bg-stone-100 dark:bg-stone-800 text-stone-700 dark:text-stone-300 hover:bg-stone-200' }}">
                            <i data-lucide="{{ auth()->user()->hasTapped($item) ? 'thumbs-up' : 'thumbs-up' }}" id="user-tap-icon-{{ $item->id }}"
                                class="w-4 h-4 {{ auth()->user()->hasTapped($item) ? 'fill-current' : '' }}"></i>
                            <span id="user-tap-text-{{ $item->id }}">
                                {{ auth()->user()->hasTapped($item) ? 'Vibed' : 'Vibe' }}
                            </span>
                        </button>

                        <!-- Lock-in Button -->
                        @if(auth()->id() !== $item->id)
                            <button onclick="toggleUserLockIn({{ $item->id }})" id="user-lockin-btn-{{ $item->id }}"
                                class="flex items-center justify-center space-x-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold transition-all shadow-sm flex-1
                                                    {{ auth()->user()->hasLockedIn($item)
                                    ? 'bg-stone-100 dark:bg-stone-800 text-stone-700 dark:text-stone-300 hover:bg-red-50 dark:hover:bg-red-900/20 hover:text-red-600'
                                    : 'glass-orange text-white hover:scale-[1.02]' }}">
                                <i data-lucide="{{ auth()->user()->hasLockedIn($item) ? 'user-check' : 'user-plus' }}"
                                    id="user-lockin-icon-{{ $item->id }}" class="w-4 h-4"></i>
                                <span id="user-lockin-text-{{ $item->id }}">
                                    {{ auth()->user()->hasLockedIn($item) ? 'Locked In' : 'Lock In' }}
                                </span>
                            </button>
                        @endif
                    @else
                        <div class="text-xs text-stone-400">Sign in to connect</div>
                    @endauth
                </div>
            </div>
        @elseif ($tab === 'places')
            <div class="bg-white dark:bg-stone-900 rounded-xl sm:rounded-2xl shadow-sm border border-stone-200 dark:border-stone-800 overflow-hidden transition hover:shadow-md hover:border-amber-500/40">
                @if($item->image)
                    <img src="{{ Storage::url($item->image) }}" class="w-full h-36 object-cover" loading="lazy">
                @endif
                <div class="p-3 sm:p-4">
                    <div class="flex items-center gap-1.5 text-xs text-amber-600 dark:text-amber-400 font-bold mb-1">
                        <i data-lucide="map-pin" class="w-3.5 h-3.5"></i>
                        <span class="truncate">{{ $item->location }}</span>
                    </div>
                    <h3 class="font-semibold text-stone-800 dark:text-stone-100 text-sm sm:text-base line-clamp-1 mb-1">{{ $item->title }}</h3>
                    <p class="text-[11px] text-stone-400">By {{ $item->organizer ? $item->organizer->name : 'Community' }}</p>
                    <div class="mt-3 pt-2.5 border-t border-stone-100 dark:border-stone-800 flex items-center justify-between text-xs">
                        <span class="text-stone-500">{{ $item->event_date ? \Carbon\Carbon::parse($item->event_date)->format('M d, Y') : 'Gathering' }}</span>
                        <a href="{{ route('events.show', $item) }}" class="text-amber-600 dark:text-amber-400 font-bold hover:underline flex items-center gap-1">
                            <span>Details</span>
                            <i data-lucide="chevron-right" class="w-3.5 h-3.5"></i>
                        </a>
                    </div>
                </div>
            </div>
        @endif
    @empty
        <div class="col-span-full text-center py-12 px-4 rounded-2xl border border-dashed border-stone-200 dark:border-stone-800 bg-stone-50/50 dark:bg-stone-900/50">
            <i data-lucide="compass" class="w-8 h-8 text-stone-400 mx-auto mb-2"></i>
            <p class="font-bold text-stone-700 dark:text-stone-300 text-sm">No {{ $tab }} found.</p>
            <p class="text-xs text-stone-500 mt-0.5">Try searching with different terms or check back soon.</p>
        </div>
    @endforelse
</div>
