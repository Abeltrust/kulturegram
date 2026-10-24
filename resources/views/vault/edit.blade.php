@extends('layouts.app')

@section('title', 'Edit Vault Item - ' . $vaultItem->title)

@section('content')
    <div class="max-w-4xl mx-auto p-4 sm:p-6 lg:p-8">
        <div class="flex items-center justify-between mb-6">
            <div>
                <a href="{{ route('vault.show', $vaultItem) }}" class="inline-flex items-center gap-1.5 text-xs font-bold text-amber-600 hover:underline uppercase tracking-widest mb-2">
                    <i data-lucide="arrow-left" class="w-4 h-4"></i> Back to Vault Item
                </a>
                <h1 class="text-2xl font-bold text-stone-900 dark:text-stone-100">Edit Vault Item</h1>
            </div>
            <form action="{{ route('vault.destroy', $vaultItem) }}" method="POST" onsubmit="return confirm('Are you sure you want to permanently delete this item from your private vault?');">
                @csrf
                @method('DELETE')
                <button type="submit" class="px-4 py-2 bg-red-50 hover:bg-red-100 dark:bg-red-950/30 dark:hover:bg-red-900/50 text-red-600 font-semibold text-sm rounded-xl transition border border-red-200 dark:border-red-900 flex items-center gap-2">
                    <i data-lucide="trash-2" class="w-4 h-4"></i> Delete
                </button>
            </form>
        </div>

        <form action="{{ route('vault.update', $vaultItem) }}" method="POST"
            class="space-y-6 sm:space-y-8 bg-white dark:bg-stone-900 border dark:border-stone-800 p-6 sm:p-8 shadow rounded-2xl">
            @csrf
            @method('PUT')

            {{-- Title --}}
            <div>
                <label for="title" class="block text-gray-700 dark:text-gray-300 font-semibold mb-2">Title</label>
                <input type="text" name="title" id="title" value="{{ old('title', $vaultItem->title) }}"
                    class="w-full border border-stone-300 dark:border-stone-700 dark:bg-stone-800 dark:text-gray-100 rounded-xl px-4 py-3 focus:ring-2 focus:ring-amber-500 focus:outline-none"
                    placeholder="Title for this vault item" required>
                @error('title')
                    <p class="text-red-500 text-xs sm:text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- Description --}}
            <div>
                <label for="description" class="block text-gray-700 dark:text-gray-300 font-semibold mb-2">Description</label>
                <textarea name="description" id="description" rows="4"
                    class="w-full border border-stone-300 dark:border-stone-700 dark:bg-stone-800 dark:text-gray-100 rounded-xl px-4 py-3 focus:ring-2 focus:ring-amber-500 focus:outline-none"
                    placeholder="Add notes, history, or context...">{{ old('description', $vaultItem->description) }}</textarea>
                @error('description')
                    <p class="text-red-500 text-xs sm:text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- Cultural Significance --}}
            <div>
                <label for="cultural_significance" class="block text-gray-700 dark:text-gray-300 font-semibold mb-2">
                    Cultural Significance (Optional)
                </label>
                <textarea name="cultural_significance" id="cultural_significance" rows="3"
                    class="w-full border border-stone-300 dark:border-stone-700 dark:bg-stone-800 dark:text-gray-100 rounded-xl px-4 py-3 focus:ring-2 focus:ring-amber-500 focus:outline-none"
                    placeholder="Why is this meaningful to your heritage or family?">{{ old('cultural_significance', $vaultItem->cultural_significance) }}</textarea>
                @error('cultural_significance')
                    <p class="text-red-500 text-xs sm:text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- Access Level --}}
            <div>
                <label for="access_level" class="block text-gray-700 dark:text-gray-300 font-semibold mb-2">Access Level</label>
                <select name="access_level" id="access_level"
                    class="w-full border border-stone-300 dark:border-stone-700 dark:bg-stone-800 dark:text-gray-100 rounded-xl px-4 py-3 focus:ring-2 focus:ring-amber-500 focus:outline-none">
                    <option value="private" {{ old('access_level', $vaultItem->access_level) == 'private' ? 'selected' : '' }}>Private (Only You)</option>
                    <option value="family" {{ old('access_level', $vaultItem->access_level) == 'family' ? 'selected' : '' }}>Family / Trusted Circle</option>
                    <option value="research" {{ old('access_level', $vaultItem->access_level) == 'research' ? 'selected' : '' }}>Cultural Researchers</option>
                    <option value="public" {{ old('access_level', $vaultItem->access_level) == 'public' ? 'selected' : '' }}>Public Archive</option>
                </select>
                @error('access_level')
                    <p class="text-red-500 text-xs sm:text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- Is Hidden --}}
            <div class="flex items-center gap-3 p-4 rounded-xl border border-stone-200 dark:border-stone-800 bg-stone-50 dark:bg-stone-800/50">
                <input type="checkbox" name="is_hidden" id="is_hidden" value="1" class="w-5 h-5 rounded text-amber-500 focus:ring-amber-500" {{ old('is_hidden', $vaultItem->is_hidden) ? 'checked' : '' }}>
                <label for="is_hidden" class="text-sm font-semibold text-stone-800 dark:text-stone-200">
                    Hide this item from the main vault listing (accessible only via direct link or search)
                </label>
            </div>

            <div class="flex items-center justify-end gap-4 pt-4">
                <a href="{{ route('vault.show', $vaultItem) }}" class="px-6 py-3 rounded-xl border border-stone-300 dark:border-stone-700 font-bold text-stone-700 dark:text-stone-300 hover:bg-stone-100 dark:hover:bg-stone-800 transition">
                    Cancel
                </a>
                <button type="submit"
                    class="px-8 py-3 bg-gradient-to-r from-amber-500 to-orange-600 text-white font-bold rounded-xl shadow-lg hover:from-amber-600 hover:to-orange-700 transition">
                    Update Vault Item
                </button>
            </div>
        </form>
    </div>
@endsection
