@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-3 sm:px-4 py-6 sm:py-8">

    {{-- Page Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-5 sm:mb-6 space-y-3 sm:space-y-0 text-center sm:text-left">
        <div>
            <h1 class="text-lg sm:text-2xl font-bold text-gray-800 dark:text-gray-100">Personal Vault</h1>
            <p class="text-gray-600 dark:text-gray-400 text-xs sm:text-sm">Your private collection of memories and important moments.</p>
        </div>

        <a href="{{ route('vault.create') }}"
           class="inline-block text-sm sm:text-base px-3 sm:px-4 py-2 glass-orange text-white rounded-lg shadow hover:opacity-90 transition">
            + Add Memory
        </a>
    </div>

    {{-- Filters --}}
    <div class="flex flex-wrap justify-center sm:justify-start gap-2 sm:gap-3 mb-5 sm:mb-6">
        @foreach ($types as $key => $label)
            <a href="{{ route('vault.index', ['type' => $key]) }}"
               class="px-3 sm:px-4 py-1.5 sm:py-2 rounded-full text-xs sm:text-sm font-medium whitespace-nowrap
                      {{ $type === $key 
                        ? 'glass-orange text-white' 
                        : 'bg-gray-100 dark:bg-stone-800 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-stone-700' }}">
                {{ $label }}
            </a>
        @endforeach
    </div>

    {{-- Vault Items Grid --}}
    @if ($items->count())
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-6">
            @foreach ($items as $item)
                <div class="bg-white dark:bg-stone-900 rounded-2xl shadow hover:shadow-lg transition p-4 sm:p-5 flex flex-col justify-between border dark:border-stone-800">

                    {{-- File Type Icon + Title --}}
                    <div class="flex items-start justify-between mb-3">
                        <div class="flex items-center gap-2">
                            @switch($item->type)
                                @case('photos')
                                    <i data-lucide="image" class="w-4 h-4 sm:w-5 sm:h-5 text-purple-500"></i>
                                    @break
                                @case('documents')
                                    <i data-lucide="file-text" class="w-4 h-4 sm:w-5 sm:h-5 text-blue-500"></i>
                                    @break
                                @case('videos')
                                    <i data-lucide="video" class="w-4 h-4 sm:w-5 sm:h-5 text-red-500"></i>
                                    @break
                                @case('audio')
                                    <i data-lucide="music" class="w-4 h-4 sm:w-5 sm:h-5 text-pink-500"></i>
                                    @break
                                @default
                                    <i data-lucide="file" class="w-4 h-4 sm:w-5 sm:h-5 text-gray-500"></i>
                            @endswitch
                            <h2 class="font-semibold text-gray-800 dark:text-gray-100 text-sm sm:text-base truncate max-w-[160px] sm:max-w-[200px]">
                                {{ $item->title }}
                            </h2>
                        </div>
                        <form action="{{ route('vault.destroy', $item) }}" method="POST" 
                              onsubmit="return confirm('Delete this item?')" class="flex-shrink-0">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-gray-400 hover:text-red-500 transition">
                                <i data-lucide="trash-2" class="w-4 h-4 sm:w-5 sm:h-5"></i>
                            </button>
                        </form>
                    </div>

                    {{-- Description --}}
                    <p class="text-gray-600 dark:text-gray-400 text-xs sm:text-sm mb-4 leading-snug">{{ Str::limit($item->description, 100) }}</p>

                    {{-- Meta Info --}}
                    <div class="flex items-center justify-between text-[11px] sm:text-xs text-gray-500 mb-4">
                        <span>{{ $item->created_at->format('M d, Y') }}</span>
                        <span>{{ $item->file_size }}</span>
                    </div>

                    {{-- Actions --}}
                    <div class="flex items-center justify-between">
                        <a href="{{ route('vault.show', $item) }}" 
                           class="text-xs sm:text-sm font-medium text-gray-600 dark:text-gray-400 hover:text-orange-600 dark:hover:text-amber-500">
                            View
                        </a>
                        <a href="{{ route('vault.download', $item) }}" 
                           class="px-3 sm:px-4 py-1.5 text-xs sm:text-sm font-medium rounded-lg glass-orange text-white shadow hover:opacity-90 transition">
                            Download
                        </a>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- Pagination --}}
        <div class="mt-6 sm:mt-8">
            {{ $items->links() }}
        </div>

    @else
        <p class="text-gray-600 dark:text-gray-400 text-center text-sm sm:text-base">No items in your vault yet.</p>
    @endif
</div>
@endsection

@push('scripts')
<script>
    lucide.createIcons(); // render Lucide icons
</script>
@endpush
