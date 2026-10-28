@extends('layouts.app')

@section('title', $vaultItem->title . ' - Kulturegram Vault')

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8 md:py-12">

    {{-- Back Link --}}
    <a href="{{ route('vault.index') }}" 
       class="inline-flex items-center text-sm font-semibold text-stone-500 hover:text-amber-600 transition mb-6">
        <i data-lucide="arrow-left" class="w-4 h-4 mr-1"></i> Back to Vault
    </a>

    {{-- Main Vault Card --}}
    <div class="bg-white dark:bg-stone-900 rounded-3xl overflow-hidden shadow-xl border border-stone-100 dark:border-stone-800">
        
        {{-- Header Bar --}}
        <div class="p-6 sm:p-8 border-b border-stone-100 dark:border-stone-800 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 rounded-2xl bg-amber-50 dark:bg-amber-950/40 text-amber-600 dark:text-amber-400 flex items-center justify-center shrink-0 border border-amber-200 dark:border-amber-800/50">
                    @switch($vaultItem->type)
                        @case('photos')
                            <i data-lucide="image" class="w-6 h-6"></i>
                            @break
                        @case('documents')
                            <i data-lucide="file-text" class="w-6 h-6"></i>
                            @break
                        @case('videos')
                            <i data-lucide="video" class="w-6 h-6"></i>
                            @break
                        @case('audio')
                            <i data-lucide="music" class="w-6 h-6"></i>
                            @break
                        @default
                            <i data-lucide="archive" class="w-6 h-6"></i>
                    @endswitch
                </div>
                <div>
                    <h1 class="page-title text-2xl sm:text-3xl font-black text-amber-500">
                        {{ $vaultItem->title }}
                    </h1>
                    <div class="flex items-center gap-2 mt-1">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-stone-100 dark:bg-stone-800 text-stone-600 dark:text-stone-300">
                            {{ ucfirst($vaultItem->type) }}
                        </span>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-amber-100 dark:bg-amber-950/50 text-amber-700 dark:text-amber-400">
                            {{ ucfirst($vaultItem->access_level) }}
                        </span>
                        @if($vaultItem->is_hidden)
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-red-100 dark:bg-red-950/50 text-red-700 dark:text-red-400">
                                Hidden
                            </span>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Action Controls --}}
            <div class="flex items-center gap-3">
                <a href="{{ route('vault.download', $vaultItem) }}" 
                   class="inline-flex items-center gap-2 px-4 py-2.5 glass-orange text-white font-bold text-sm rounded-xl transition shadow-md">
                    <i data-lucide="download" class="w-4 h-4"></i> Download
                </a>

                @can('update', $vaultItem)
                    <a href="{{ route('vault.edit', $vaultItem) }}" 
                       class="inline-flex items-center gap-2 px-4 py-2.5 bg-stone-100 dark:bg-stone-800 hover:bg-stone-200 dark:hover:bg-stone-700 text-stone-700 dark:text-stone-200 font-bold text-sm rounded-xl transition border border-stone-200 dark:border-stone-700">
                        <i data-lucide="edit-3" class="w-4 h-4"></i> Edit
                    </a>
                @endcan

                @can('delete', $vaultItem)
                    <form action="{{ route('vault.destroy', $vaultItem) }}" method="POST" onsubmit="return confirm('Permanently remove this memory from your vault?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="p-2.5 text-stone-400 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-950/30 rounded-xl transition">
                            <i data-lucide="trash-2" class="w-5 h-5"></i>
                        </button>
                    </form>
                @endcan
            </div>
        </div>

        {{-- Content Body --}}
        <div class="p-6 sm:p-8 space-y-6">
            @if($vaultItem->description)
                <div>
                    <h3 class="text-xs font-bold uppercase tracking-widest text-stone-400 mb-2">Description & Notes</h3>
                    <p class="text-stone-700 dark:text-stone-300 leading-relaxed text-base">
                        {{ $vaultItem->description }}
                    </p>
                </div>
            @endif

            @if($vaultItem->cultural_significance)
                <div class="bg-amber-50/50 dark:bg-amber-950/20 border border-amber-200/50 dark:border-amber-800/40 rounded-2xl p-5">
                    <h3 class="text-xs font-black uppercase tracking-widest text-amber-700 dark:text-amber-400 mb-2 flex items-center gap-1.5">
                        <i data-lucide="feather" class="w-4 h-4"></i> Cultural Significance
                    </h3>
                    <p class="text-stone-700 dark:text-stone-300 text-sm leading-relaxed">
                        {{ $vaultItem->cultural_significance }}
                    </p>
                </div>
            @endif

            {{-- Metadata Grid --}}
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 pt-4 border-t border-stone-100 dark:border-stone-800">
                <div class="bg-stone-50 dark:bg-stone-800/50 rounded-xl p-3 border border-stone-100 dark:border-stone-800">
                    <p class="text-xs text-stone-400">File Size</p>
                    <p class="text-sm font-bold text-stone-800 dark:text-stone-200 mt-0.5">{{ $vaultItem->file_size ?? 'Unknown' }}</p>
                </div>
                <div class="bg-stone-50 dark:bg-stone-800/50 rounded-xl p-3 border border-stone-100 dark:border-stone-800">
                    <p class="text-xs text-stone-400">MIME Type</p>
                    <p class="text-sm font-bold text-stone-800 dark:text-stone-200 mt-0.5 truncate">{{ $vaultItem->mime_type ?? 'N/A' }}</p>
                </div>
                <div class="bg-stone-50 dark:bg-stone-800/50 rounded-xl p-3 border border-stone-100 dark:border-stone-800">
                    <p class="text-xs text-stone-400">Archived Date</p>
                    <p class="text-sm font-bold text-stone-800 dark:text-stone-200 mt-0.5">{{ $vaultItem->created_at->format('M d, Y') }}</p>
                </div>
                <div class="bg-stone-50 dark:bg-stone-800/50 rounded-xl p-3 border border-stone-100 dark:border-stone-800">
                    <p class="text-xs text-stone-400">Access Level</p>
                    <p class="text-sm font-bold text-stone-800 dark:text-stone-200 mt-0.5">{{ ucfirst($vaultItem->access_level) }}</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
