@extends('layouts.app')

@section('content')
  <div class="max-w-3xl mx-auto bg-white dark:bg-stone-900 border dark:border-stone-800 shadow-lg rounded-2xl p-8 mt-10">
    <h1 class="page-title text-2xl sm:text-3xl font-bold mb-8 text-amber-500">Create Community</h1>

    <form action="{{ route('communities.store') }}" method="POST" enctype="multipart/form-data">
      @csrf

      <!-- Community Name -->
      <div class="mb-6">
        <label for="name" class="block text-gray-700 dark:text-stone-300 font-semibold mb-2">Community Name</label>
        <input type="text" name="name" id="name"
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
          placeholder="What is this community about?" required></textarea>
        @error('description')
          <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
        @enderror
      </div>

      <!-- Category -->
      <div class="mb-6">
        <h3 class="mb-4 text-lg font-semibold text-gray-900 dark:text-stone-100">Select Category</h3>
        <ul class="grid w-full gap-4 md:grid-cols-2">

          <!-- Heritage -->
          <li>
            <input type="radio" id="heritage" name="category" value="heritage" class="hidden peer" required>
            <label for="heritage"
              class="inline-flex items-center justify-between w-full p-4 text-gray-600 dark:text-stone-400 bg-white dark:bg-stone-800 border border-gray-200 dark:border-stone-700 rounded-xl cursor-pointer hover:bg-gray-50 dark:hover:bg-stone-700 peer-checked:border-amber-500 peer-checked:text-amber-600 dark:peer-checked:text-amber-400 peer-checked:bg-amber-50 dark:peer-checked:bg-amber-500/10">
              <div class="block">
                <div class="w-full text-md font-semibold">Heritage</div>
                <div class="w-full text-sm">Preserve cultural heritage</div>
              </div>
              <i data-lucide="landmark" class="w-5 h-5 text-amber-500 peer-checked:text-amber-500"></i>
            </label>
          </li>

          <!-- Technology -->
          <li>
            <input type="radio" id="technology" name="category" value="technology" class="hidden peer">
            <label for="technology"
              class="inline-flex items-center justify-between w-full p-4 text-gray-600 dark:text-stone-400 bg-white dark:bg-stone-800 border border-gray-200 dark:border-stone-700 rounded-xl cursor-pointer hover:bg-gray-50 dark:hover:bg-stone-700 peer-checked:border-amber-500 peer-checked:text-amber-600 dark:peer-checked:text-amber-400 peer-checked:bg-amber-50 dark:peer-checked:bg-amber-500/10">
              <div class="block">
                <div class="w-full text-md font-semibold">Technology</div>
                <div class="w-full text-sm">Digital tools for culture</div>
              </div>
              <i data-lucide="cpu" class="w-5 h-5 text-amber-500 peer-checked:text-amber-500"></i>
            </label>
          </li>

          <!-- Crafts -->
          <li>
            <input type="radio" id="crafts" name="category" value="crafts" class="hidden peer">
            <label for="crafts"
              class="inline-flex items-center justify-between w-full p-4 text-gray-600 dark:text-stone-400 bg-white dark:bg-stone-800 border border-gray-200 dark:border-stone-700 rounded-xl cursor-pointer hover:bg-gray-50 dark:hover:bg-stone-700 peer-checked:border-amber-500 peer-checked:text-amber-600 dark:peer-checked:text-amber-400 peer-checked:bg-amber-50 dark:peer-checked:bg-amber-500/10">
              <div class="block">
                <div class="w-full text-md font-semibold">Crafts</div>
                <div class="w-full text-sm">Traditional arts & skills</div>
              </div>
              <i data-lucide="scissors" class="w-5 h-5 text-amber-500 peer-checked:text-amber-500"></i>
            </label>
          </li>

          <!-- Rituals -->
          <li>
            <input type="radio" id="rituals" name="category" value="rituals" class="hidden peer">
            <label for="rituals"
              class="inline-flex items-center justify-between w-full p-4 text-gray-600 dark:text-stone-400 bg-white dark:bg-stone-800 border border-gray-200 dark:border-stone-700 rounded-xl cursor-pointer hover:bg-gray-50 dark:hover:bg-stone-700 peer-checked:border-amber-500 peer-checked:text-amber-600 dark:peer-checked:text-amber-400 peer-checked:bg-amber-50 dark:peer-checked:bg-amber-500/10">
              <div class="block">
                <div class="w-full text-md font-semibold">Rituals</div>
                <div class="w-full text-sm">Ceremonies & traditions</div>
              </div>
              <i data-lucide="flame" class="w-5 h-5 text-amber-500 peer-checked:text-amber-500"></i>
            </label>
          </li>

        </ul>
      </div>





      <!-- Community Rules -->
      <div class="mb-6">
        <label for="rules" class="block text-gray-700 dark:text-stone-300 font-semibold mb-2">Community Rules</label>
        <textarea name="rules" id="rules" rows="4"
          class="w-full border border-stone-300 dark:border-stone-700 bg-white dark:bg-stone-800 text-stone-900 dark:text-stone-100 rounded-xl px-4 py-3 focus:ring-2 focus:ring-amber-500 focus:outline-none"
          placeholder="Set guidelines for your community..." required></textarea>
        @error('rules')
          <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
        @enderror
      </div>

      <!-- Upload Image -->
      <div class="mb-6">
        <label for="image" class="block text-gray-700 dark:text-stone-300 font-semibold mb-2">Community Image</label>

        <!-- Custom file input -->
        <div id="image-upload-box"
          class="w-full border-2 border-dashed border-stone-300 dark:border-stone-700 bg-stone-50 dark:bg-stone-800/50 rounded-xl px-6 py-8 text-center cursor-pointer hover:border-amber-500 transition relative"
          onclick="document.getElementById('image').click()">
          <input type="file" name="image" id="image" accept="image/*" class="hidden" onchange="previewImage(event)">

          <!-- Preview -->
          <div id="preview" class="hidden">
            <img id="preview-img" class="mx-auto max-h-40 rounded-lg shadow" />
            <p class="text-gray-500 text-sm mt-2">Click to change image</p>
          </div>

          <!-- Default state -->
          <div id="upload-text" class="text-gray-500">
            <svg class="w-12 h-12 mx-auto mb-3 text-gray-400" fill="none" stroke="currentColor" stroke-width="2"
              viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round"
                d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6h.1a5 5 0 010 10h-1M12 12v9m0 0l-3-3m3 3l3-3" />
            </svg>
            <p class="text-base">Click to upload an image</p>
            <p class="text-xs text-gray-400">PNG, JPG up to 2MB</p>
          </div>
        </div>

        <!-- Error -->
        @error('image')
          <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
        @enderror
      </div>

      <script>
        function previewImage(event) {
          const file = event.target.files[0];
          const previewContainer = document.getElementById('preview');
          const previewImage = document.getElementById('preview-img');
          const uploadText = document.getElementById('upload-text');

          if (file) {
            const reader = new FileReader();
            reader.onload = function (e) {
              previewImage.src = e.target.result;
              previewContainer.classList.remove('hidden');
              uploadText.classList.add('hidden');
            }
            reader.readAsDataURL(file);
          }
        }
      </script>


      <!-- Submit -->
      <div class="flex justify-end">
        <button type="submit"
          class="w-full glass-orange text-white font-bold px-6 py-3 rounded-xl shadow transition-all duration-200">
          Create Community
        </button>
      </div>
    </form>
  </div>
@endsection