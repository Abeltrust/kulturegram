@extends('layouts.app')

@section('content')
    <div class="max-w-4xl mx-auto p-4 sm:p-6 lg:p-8">
        <h1 class="text-xl sm:text-xl font-bold text-amber-600 mb-6 sm:mb-4 text-center sm:text-left">Add to Your Vault</h1>

        {{-- Validation Errors --}}
        @if ($errors->any())
            <div
                class="bg-red-100 border border-red-400 text-red-700 px-3 sm:px-4 py-2 sm:py-3 rounded-xl mb-4 sm:mb-6 text-sm sm:text-base">
                <ul class="list-disc pl-4 sm:pl-6">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('vault.store') }}" method="POST" enctype="multipart/form-data"
            class="space-y-6 sm:space-y-8 bg-white dark:bg-stone-900 border dark:border-stone-800 p-4 sm:p-8 shadow rounded-2xl">
            @csrf

            {{-- Title --}}
            <div>
                <label for="title"
                    class="block text-gray-700 dark:text-gray-300 font-semibold mb-2 text-sm sm:text-base">Title</label>
                <input type="text" name="title" id="title" value="{{ old('title') }}"
                    class="w-full border border-stone-300 dark:border-stone-700 dark:bg-stone-800 dark:text-gray-100 rounded-xl px-3 sm:px-4 py-2 sm:py-3 focus:ring-2 focus:ring-amber-500 focus:outline-none text-sm sm:text-base"
                    placeholder="Give your vault item a title" required>
                @error('title')
                    <p class="text-red-500 text-xs sm:text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- Category / Type --}}
            <div>
                <label
                    class="block text-gray-700 dark:text-gray-300 font-semibold mb-3 sm:mb-4 text-sm sm:text-base">Type</label>
                <ul class="grid gap-3 sm:gap-4 grid-cols-2 sm:grid-cols-2">
                    @php
                        $types = [
                            ['id' => 'photos', 'label' => 'Photos', 'desc' => 'Images & memories', 'icon' => 'image'],
                            ['id' => 'documents', 'label' => 'Documents', 'desc' => 'Records & archives', 'icon' => 'file-text'],
                            ['id' => 'videos', 'label' => 'Videos', 'desc' => 'Clips & stories', 'icon' => 'video'],
                            ['id' => 'audio', 'label' => 'Audio', 'desc' => 'Voices & music', 'icon' => 'music'],
                        ];
                    @endphp

                    @foreach ($types as $type)
                        <li>
                            <input type="radio" id="{{ $type['id'] }}" name="type" value="{{ $type['id'] }}"
                                class="hidden peer">
                            <label for="{{ $type['id'] }}"
                                class="inline-flex items-center justify-between w-full p-3 sm:p-4 text-gray-600 dark:text-gray-400 bg-white dark:bg-stone-800 border border-gray-200 dark:border-stone-700 cursor-pointer hover:bg-gray-50 dark:hover:bg-stone-700 peer-checked:border-amber-500 peer-checked:text-amber-600 dark:peer-checked:text-amber-400 peer-checked:bg-amber-50 dark:peer-checked:bg-amber-500/10 transition-all">
                                <div>
                                    <div class="text-sm sm:text-md font-semibold">{{ $type['label'] }}</div>
                                    <div class="text-xs sm:text-sm">{{ $type['desc'] }}</div>
                                </div>
                                <i data-lucide="{{ $type['icon'] }}" class="w-4 h-4 sm:w-5 sm:h-5 text-amber-500"></i>
                            </label>
                        </li>
                    @endforeach
                </ul>
            </div>

            {{-- File Upload --}}
            <div>
                <label class="block text-gray-700 dark:text-gray-300 font-semibold mb-2 text-sm sm:text-base">Upload Files
                    (max 10)</label>
                <div id="file-dropzone"
                    class="flex flex-col items-center justify-center w-full p-6 sm:p-8 border-2 border-dashed border-stone-300 dark:border-stone-700 rounded-xl cursor-pointer hover:border-amber-500 bg-amber-50 dark:bg-stone-800/50 transition">
                    <p class="text-gray-600 dark:text-gray-400 text-sm sm:text-base text-center">Drag & drop files here, or
                        click to browse</p>
                    <input type="file" name="files[]" multiple accept="image/*,video/*,audio/*,.pdf,.doc,.docx"
                        class="hidden" id="file-input">
                </div>
                <div id="file-preview" class="mt-3 sm:mt-4 grid grid-cols-2 sm:grid-cols-3 gap-3 sm:gap-4"></div>
            </div>

            {{-- Description --}}
            <div>
                <label for="description"
                    class="block text-gray-700 dark:text-gray-300 font-semibold mb-2 text-sm sm:text-base">Description</label>
                <textarea name="description" id="description" rows="4"
                    class="w-full border border-stone-300 dark:border-stone-700 dark:bg-stone-800 dark:text-gray-100 rounded-xl px-3 sm:px-4 py-2 sm:py-3 focus:ring-2 focus:ring-amber-500 focus:outline-none text-sm sm:text-base"
                    placeholder="What is this item about?" required>{{ old('description') }}</textarea>
                @error('description')
                    <p class="text-red-500 text-xs sm:text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- Access Level --}}
            <div>
                <label class="block text-gray-700 dark:text-gray-300 font-semibold mb-2 text-sm sm:text-base">Access
                    Level</label>
                <ul class="grid grid-cols-2 gap-3 sm:gap-4">
                    @php
                        $levels = [
                            ['id' => 'private', 'label' => 'Private', 'desc' => 'Only you', 'icon' => 'lock'],
                            ['id' => 'family', 'label' => 'Family', 'desc' => 'Trusted circle', 'icon' => 'users'],
                            ['id' => 'research', 'label' => 'Research', 'desc' => 'Scholars only', 'icon' => 'book-open'],
                            ['id' => 'public', 'label' => 'Public', 'desc' => 'Open to all', 'icon' => 'globe']
                        ];
                    @endphp
                    @foreach ($levels as $level)
                        <li>
                            <input type="radio" id="{{ $level['id'] }}" name="access_level" value="{{ $level['id'] }}"
                                class="hidden peer">
                            <label for="{{ $level['id'] }}"
                                class="inline-flex items-center justify-between w-full p-3 sm:p-4 text-gray-600 dark:text-gray-400 bg-white dark:bg-stone-800 border border-gray-200 dark:border-stone-700 cursor-pointer hover:bg-gray-50 dark:hover:bg-stone-700 peer-checked:border-amber-500 peer-checked:text-amber-600 dark:peer-checked:text-amber-400 peer-checked:bg-amber-50 dark:peer-checked:bg-amber-500/10 transition-all">
                                <div>
                                    <div class="text-sm sm:text-md font-semibold">{{ $level['label'] }}</div>
                                    <div class="text-xs sm:text-sm">{{ $level['desc'] }}</div>
                                </div>
                                <i data-lucide="{{ $level['icon'] }}" class="w-4 h-4 sm:w-5 sm:h-5 text-amber-500"></i>
                            </label>
                        </li>
                    @endforeach
                </ul>
            </div>

            {{-- Buttons --}}
            <div class="flex flex-col sm:flex-row gap-3 sm:gap-4">
                <button type="submit" name="is_draft" value="1"
                    class="w-full sm:w-1/2 bg-gray-200 dark:bg-stone-800 text-gray-700 dark:text-gray-300 px-4 sm:px-6 py-2 sm:py-3 rounded-xl shadow hover:bg-gray-300 dark:hover:bg-stone-700 text-sm sm:text-base transition-all">
                    Save Draft
                </button>
                <button type="submit"
                    class="w-full sm:w-1/2 glass-orange text-white px-4 sm:px-6 py-2 sm:py-3 rounded-xl shadow text-sm sm:text-base transition-all">
                    Save to Vault
                </button>
            </div>
        </form>
    </div>

    {{-- File upload JS --}}
    <script>
        const dropzone = document.getElementById('file-dropzone');
        const fileInput = document.getElementById('file-input');
        const preview = document.getElementById('file-preview');

        dropzone.addEventListener('click', () => fileInput.click());

        fileInput.addEventListener('change', () => {
            preview.innerHTML = '';
            Array.from(fileInput.files).slice(0, 10).forEach(file => {
                const div = document.createElement('div');
                div.classList.add('border', 'rounded-xl', 'p-2', 'bg-white', 'shadow');
                div.innerHTML = `<p class="text-xs sm:text-sm text-gray-700 truncate">${file.name}</p>`;
                preview.appendChild(div);
            });
        });
    </script>
@endsection