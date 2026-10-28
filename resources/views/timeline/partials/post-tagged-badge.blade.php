@php
    $taggedModels = $post->tagged_users_models;
    $taggedCount = $taggedModels->count();
    $viewerIsTagged = auth()->check() && $taggedModels->contains('id', auth()->id());
    $viewerIsPostOwner = auth()->check() && $post->isOwnedBy(auth()->user());
@endphp

<span id="post-tagged-badge-{{ $post->id }}" class="{{ $taggedCount > 0 ? 'inline-flex' : 'hidden' }} flex-wrap items-center gap-1 text-xs text-stone-500 dark:text-stone-400 relative" x-data="{ openTaggedList: false }">
    @if($taggedCount > 0)
        <span>is with</span>
        @php $firstTagged = $taggedModels->first(); @endphp
        <a href="{{ route('profile.user', $firstTagged->username ?: $firstTagged->id) }}"
           class="font-semibold text-amber-600 dark:text-amber-400 hover:underline">
            {{ '@' . $firstTagged->username }}
        </a>

        @if($taggedCount === 2)
            @php $secondTagged = $taggedModels->skip(1)->first(); @endphp
            <span>and</span>
            <a href="{{ route('profile.user', $secondTagged->username ?: $secondTagged->id) }}"
               class="font-semibold text-amber-600 dark:text-amber-400 hover:underline">
                {{ '@' . $secondTagged->username }}
            </a>
        @elseif($taggedCount > 2)
            <span>and</span>
            <button type="button"
                    @click="openTaggedList = !openTaggedList"
                    class="font-bold text-amber-600 dark:text-amber-400 hover:underline cursor-pointer">
                {{ $taggedCount - 1 }} others
            </button>
        @endif

        @if($viewerIsTagged)
            <button type="button"
                    onclick="removeSelfTagFromPost({{ $post->id }})"
                    class="ml-1 px-1.5 py-0.5 rounded bg-stone-100 dark:bg-stone-800 hover:bg-red-500/10 text-stone-500 hover:text-red-500 text-[10px] font-semibold transition cursor-pointer"
                    title="Remove yourself from this story">
                Untag me
            </button>
        @endif

        @if($taggedCount > 1)
            <div x-show="openTaggedList"
                 @click.away="openTaggedList = false"
                 x-cloak
                 class="absolute left-0 top-full mt-1.5 z-30 w-60 bg-white dark:bg-stone-900 rounded-2xl shadow-xl border border-stone-200 dark:border-stone-800 p-2.5 space-y-1.5">
                <div class="flex items-center justify-between px-1 pb-1 border-b border-stone-100 dark:border-stone-800">
                    <span class="text-[10px] font-black uppercase tracking-wider text-stone-400">Tagged in Story ({{ $taggedCount }})</span>
                    <button type="button" @click="openTaggedList = false" class="text-stone-400 hover:text-stone-600">
                        <i data-lucide="x" class="w-3.5 h-3.5"></i>
                    </button>
                </div>
                <div class="max-h-44 overflow-y-auto space-y-1">
                    @foreach($taggedModels as $tUser)
                        <div class="flex items-center justify-between gap-2 p-1.5 rounded-xl hover:bg-stone-50 dark:hover:bg-stone-800/60 transition">
                            <a href="{{ route('profile.user', $tUser->username ?: $tUser->id) }}" class="flex items-center gap-2 min-w-0 flex-1">
                                <img src="{{ $tUser->profile_photo_url }}" alt="{{ $tUser->name }}" class="w-6 h-6 rounded-full object-cover shrink-0">
                                <div class="min-w-0 truncate">
                                    <div class="text-xs font-bold text-stone-800 dark:text-stone-100 truncate">{{ $tUser->name }}</div>
                                    <div class="text-[10px] text-amber-600 dark:text-amber-400 truncate">{{ '@' . $tUser->username }}</div>
                                </div>
                            </a>
                            @if($viewerIsPostOwner || (auth()->check() && auth()->id() === $tUser->id))
                                <button type="button"
                                        onclick="removeSelfTagFromPost({{ $post->id }}, {{ $tUser->id }})"
                                        class="text-[10px] font-bold text-red-500 hover:underline px-1.5 py-0.5 shrink-0 cursor-pointer">
                                    Remove
                                </button>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    @endif
</span>

