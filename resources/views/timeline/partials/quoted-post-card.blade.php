@if($post->sharedPost && $post->sharedPost->user)
    @php
        $orig = $post->sharedPost;
        $origYoutubeId = $orig->video ? \App\Models\CulturalClass::extractYoutubeId($orig->video) : null;
        $origImageSrc = \App\Models\CulturalClass::resolveMediaUrl($orig->image);
        $origVideoSrc = \App\Models\CulturalClass::resolveMediaUrl($orig->video);
    @endphp
    <div class="mt-3 rounded-2xl border border-stone-200 dark:border-stone-800 bg-stone-50/70 dark:bg-stone-950/60 overflow-hidden transition hover:border-amber-500/40">
        <div class="p-3.5 sm:p-4">
            <div class="flex items-center justify-between gap-2 mb-2">
                <a href="{{ route('profile.user', $orig->user) }}" class="flex items-center gap-2 min-w-0">
                    <img src="{{ $orig->user->profile_photo_url }}"
                         alt="{{ $orig->user->name }}"
                         class="w-7 h-7 rounded-full object-cover border border-stone-200 dark:border-stone-700 shrink-0">
                    <div class="flex flex-wrap items-center gap-x-1.5 text-xs min-w-0">
                        <span class="font-bold text-stone-800 dark:text-stone-100 truncate">{{ $orig->user->name }}</span>
                        <span class="text-stone-400 truncate">{{ '@' . $orig->user->username }}</span>
                        <span class="text-stone-300 dark:text-stone-700">•</span>
                        <span class="text-[11px] text-stone-400">{{ $orig->time_ago }}</span>
                    </div>
                </a>
                <a href="{{ route('timeline.show', $orig->id) }}"
                   class="text-[11px] font-semibold text-amber-600 dark:text-amber-400 hover:underline flex items-center gap-1 shrink-0">
                    <span>Original</span>
                    <i data-lucide="arrow-up-right" class="w-3 h-3"></i>
                </a>
            </div>

            <div class="text-xs sm:text-sm text-stone-700 dark:text-stone-300 leading-relaxed">
                {!! $orig->formatted_content !!}
            </div>
        </div>

        @if($origImageSrc && !$origVideoSrc && !$origYoutubeId)
            <div class="border-t border-stone-200/70 dark:border-stone-800 bg-black/5 dark:bg-black/40 flex items-center justify-center max-h-[420px] overflow-hidden">
                <img src="{{ $origImageSrc }}" alt="Shared story image" class="w-auto h-auto max-w-full max-h-[400px] object-contain mx-auto" loading="lazy">
            </div>
        @endif

        @if($origYoutubeId)
            <div class="border-t border-stone-200/70 dark:border-stone-800 bg-black aspect-video">
                <iframe src="https://www.youtube.com/embed/{{ $origYoutubeId }}?rel=0&modestbranding=1"
                        class="w-full h-full"
                        frameborder="0"
                        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                        allowfullscreen></iframe>
            </div>
        @elseif($origVideoSrc)
            <div class="border-t border-stone-200/70 dark:border-stone-800 bg-black flex items-center justify-center">
                <video src="{{ $origVideoSrc }}{{ !str_contains($origVideoSrc, '#') ? '#t=0.1' : '' }}"
                       controls
                       preload="metadata"
                       class="w-auto h-auto max-w-full max-h-[420px] mx-auto"></video>
            </div>
        @endif
    </div>
@endif

