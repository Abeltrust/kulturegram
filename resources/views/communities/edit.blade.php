@extends('layouts.app')

@section('title', 'Edit Community - ' . $community->name)

@section('content')
  <div class="max-w-3xl mx-auto bg-white dark:bg-stone-900 border border-stone-100 dark:border-stone-800 shadow-lg rounded-2xl p-6 sm:p-8 mt-10">
    <div class="flex items-center justify-between mb-8">
      <div>
        <a href="{{ route('communities.show', $community) }}" class="inline-flex items-center gap-1.5 text-xs font-bold text-amber-600 hover:underline uppercase tracking-widest mb-2">
          <i data-lucide="arrow-left" class="w-4 h-4"></i> Back to Community
        </a>
        <h2 class="text-3xl font-bold text-gray-800 dark:text-stone-100">
          Edit Community
        </h2>
      </div>
      @if(auth()->id() === $community->created_by)
      <form action="{{ route('communities.destroy', $community) }}" method="POST" onsubmit="return confirm('Are you sure you want to permanently delete this community and its discussion feed?');">
        @csrf
        @method('DELETE')
        <button type="submit" class="px-4 py-2 bg-red-50 hover:bg-red-100 dark:bg-red-950/30 dark:hover:bg-red-900/50 text-red-600 font-semibold text-sm rounded-xl transition border border-red-200 dark:border-red-900 flex items-center gap-2">
          <i data-lucide="trash-2" class="w-4 h-4"></i>
          Delete
        </button>
      </form>
      @endif
    </div>

    <form action="{{ route('communities.update', $community) }}" method="POST" enctype="multipart/form-data">
      @csrf
      @method('PUT')

      <!-- Community Name -->
      <div class="mb-6">
        <label for="name" class="block text-gray-700 dark:text-stone-300 font-semibold mb-2">Community Name</label>
        <input type="text" name="name" id="name" value="{{ old('name', $community->name) }}"
          class="w-full border border-stone-300 dark:border-stone-700 bg-white dark:bg-stone-800 text-stone-900 dark:text-stone-100 rounded-xl px-4 py-3 focus:ring-2 focus:ring-amber-500 focus:outline-none"
          placeholder="Name your community..." required>
        @error('name')
          <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
        @enderror
      </div>

      <!-- Description -->
      <div class="mb-6">
        <label for="description" class="block text-gray-700 dark:text-stone-300 font-semibold mb-2">Description</label>
        <textarea name="description" id="description" rows="4"
          class="w-full border border-stone-300 dark:border-stone-700 bg-white dark:bg-stone-800 text-stone-900 dark:text-stone-100 rounded-xl px-4 py-3 focus:ring-2 focus:ring-amber-500 focus:outline-none"
          placeholder="What is this community about?" required>{{ old('description', $community->description) }}</textarea>
        @error('description')
          <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
        @enderror
      </div>

      <!-- Category -->
      <div class="mb-6">
        <label for="category" class="block mb-2 text-sm font-semibold text-gray-900 dark:text-stone-100">Category</label>
        <x-custom-select 
            name="category" 
            id="category"
            :options="[
                'heritage' => 'Heritage & History',
                'art' => 'Art & Crafts',
                'music' => 'Music & Dance',
                'language' => 'Language & Literature',
                'culinary' => 'Culinary & Food',
                'technology' => 'Technology & Innovation',
                'general' => 'General Community'
            ]"
            :value="old('category', $community->category)"
            placeholder="Select community category..." 
            icon="users"
            :searchable="false" />
        @error('category')
          <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
        @enderror
      </div>

      <!-- Community Rules -->
      <div class="mb-6">
        <label for="rules" class="block text-gray-700 dark:text-stone-300 font-semibold mb-2">Community Rules & Guidelines (Optional)</label>
        <textarea name="rules" id="rules" rows="3"
          class="w-full border border-stone-300 dark:border-stone-700 bg-white dark:bg-stone-800 text-stone-900 dark:text-stone-100 rounded-xl px-4 py-3 focus:border-amber-500 focus:outline-none"
          placeholder="Respect each other, stay on topic...">{{ old('rules', $community->rules) }}</textarea>
      </div>

      <!-- Privacy -->
      <div class="mb-6 p-4 rounded-xl border border-stone-200 dark:border-stone-800 bg-stone-50 dark:bg-stone-800/50 flex items-center gap-3">
        <input type="checkbox" name="is_private" id="is_private" value="1" class="w-5 h-5 rounded text-amber-500 focus:ring-amber-500" {{ old('is_private', $community->is_private) ? 'checked' : '' }}>
        <label for="is_private" class="text-sm font-semibold text-stone-800 dark:text-stone-200">
          Make this community private (invite / approval required)
        </label>
      </div>

      <!-- Current Image & Upload -->
      <div class="mb-8">
        <label for="image" class="block text-gray-700 dark:text-stone-300 font-semibold mb-2">Community Cover Image</label>
        @if($community->image)
          <div class="mb-3 relative w-48 h-32 rounded-xl overflow-hidden border border-stone-200 dark:border-stone-700">
            <img src="{{ asset('storage/' . $community->image) }}" class="w-full h-full object-cover">
          </div>
        @endif
        <input type="file" name="image" id="image" accept="image/*"
          class="w-full border border-stone-300 dark:border-stone-700 bg-white dark:bg-stone-800 text-stone-900 dark:text-stone-100 rounded-xl px-4 py-3">
        @error('image')
          <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
        @enderror
      </div>

      <div class="flex items-center justify-end gap-4">
        <a href="{{ route('communities.show', $community) }}" class="px-6 py-3 rounded-xl border border-stone-300 dark:border-stone-700 font-bold text-stone-700 dark:text-stone-300 hover:bg-stone-100 dark:hover:bg-stone-800 transition">
          Cancel
        </a>
        <button type="submit"
          class="px-8 py-3 glass-orange text-white font-bold rounded-xl shadow-lg transition">
          Update Community
        </button>
      </div>
    </form>
  </div>
@endsection
