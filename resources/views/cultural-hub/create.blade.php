@extends('layouts.app')

@section('title', 'Share Your Culture - Cultural Hub')

@section('content')
    <div class="max-w-3xl mx-auto p-4 sm:p-6 lg:p-8">

        <!-- Header -->
        <div class="mb-8 text-center sm:text-left">
            <div class="flex flex-col sm:flex-row sm:items-center sm:space-x-4 mb-4">
                <a href="{{ route('cultural-hub.index') }}"
                    class="p-2 text-stone-600 dark:text-stone-400 hover:text-stone-800 dark:hover:text-stone-200 hover:bg-stone-100 dark:hover:bg-stone-800 rounded-lg transition-colors mb-2 sm:mb-0 w-fit">
                    <i data-lucide="arrow-left" class="w-5 h-5"></i>
                </a>
                <div class="flex items-center space-x-3">
                    <i data-lucide="globe" class="w-8 h-8 text-amber-600"></i>
                    <h1 class="text-2xl sm:text-3xl font-bold text-stone-800 dark:text-stone-100">Share Your Culture</h1>
                </div>
            </div>
            <p class="text-stone-600 dark:text-stone-400 text-sm sm:text-base">Contribute to global heritage by sharing your
                traditions and
                stories.</p>
        </div>

        <!-- Living Heritage AI Workflow Banner -->
        <div class="mb-8 p-5 bg-amber-50 dark:bg-amber-950/40 border border-amber-200 dark:border-amber-800/60 rounded-2xl flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="space-y-1">
                <div class="flex items-center space-x-2 text-xs font-bold text-amber-800 dark:text-amber-300">
                    <i data-lucide="sparkles" class="w-4 h-4 text-amber-600"></i>
                    <span>Preserving an Oral Story or Recording?</span>
                </div>
                <p class="text-xs text-stone-600 dark:text-stone-400">
                    Use our AI-assisted <strong>Living Heritage Capture</strong> workflow with Muse Voice Transcribe, Muse Spark structuring, Muse Wonder, and SAM 3.1 artifact segmentation.
                </p>
            </div>
            <a href="{{ route('cultural-hub.living-heritage.create') }}"
               class="inline-flex items-center space-x-2 px-4 py-2.5 bg-amber-500 hover:bg-amber-600 text-white font-bold rounded-xl text-xs uppercase tracking-wider flex-shrink-0 transition-colors">
                <span>Try Living Heritage</span>
                <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
            </a>
        </div>

        @include('partials.image-compression')

        <form action="{{ route('cultural-hub.store') }}" method="POST" enctype="multipart/form-data"
            @submit.prevent="if(await handleFormImageCompression($el)) $el.submit()"
            class="bg-white dark:bg-stone-900 rounded-[2.5rem] p-8 sm:p-12 shadow-2xl border border-stone-100 dark:border-stone-800 space-y-10">
            @csrf
            <div class="space-y-8">
                <!-- Basic Info Section -->
                <div class="space-y-6">
                    <div
                        class="flex items-center space-x-2 text-amber-600 uppercase tracking-tighter text-xs font-black mb-4">
                        <i data-lucide="info" class="w-4 h-4"></i>
                        <span>General Information</span>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Culture Name -->
                        <div>
                            <label for="name"
                                class="block text-[11px] font-black uppercase tracking-widest text-stone-500 mb-2">Culture
                                Name</label>
                            <input type="text" name="name" id="name" required
                                class="w-full stnd-input py-3 px-4 @error('name') border-red-500 @enderror"
                                placeholder="e.g. Maasai, Yoruba, Celtic...">
                            @error('name') <p class="text-red-500 text-[10px] mt-1 font-bold">{{ $message }}</p> @enderror
                        </div>

                        <!-- Region -->
                        <div>
                            <label for="region"
                                class="block text-[11px] font-black uppercase tracking-widest text-stone-500 mb-2">Region/Origin</label>
                            <input type="text" name="region" id="region" required
                                class="w-full stnd-input py-3 px-4 @error('region') border-red-500 @enderror"
                                placeholder="e.g. East Africa, West Africa, Europe...">
                            @error('region') <p class="text-red-500 text-[10px] mt-1 font-bold">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <!-- Category -->
                    <div>
                        <label for="category"
                            class="block text-[11px] font-black uppercase tracking-widest text-stone-500 mb-2">Category</label>
                        <select name="category" id="category" required
                            class="w-full stnd-input py-3 px-4 appearance-none cursor-pointer">
                            @foreach($categories as $cat)
                                <option value="{{ $cat }}">{{ $cat }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Description -->
                    <div x-data="{ 
                                                    content: '{{ old('description') }}',
                                                    wordCount() {
                                                        return this.content.trim() ? this.content.trim().split(/\s+/).length : 0;
                                                    }
                                                }">
                        <label for="description"
                            class="block text-[11px] font-black uppercase tracking-widest text-stone-500 mb-2 flex justify-between">
                            <span>Core Story / Description</span>
                            <span :class="wordCount() < 20 ? 'text-amber-500' : 'text-green-600'"
                                class="text-[9px] font-black">
                                <span x-text="wordCount()"></span> / 20 WORDS
                            </span>
                        </label>
                        <textarea name="description" id="description" rows="5" required x-model="content"
                            class="w-full stnd-input py-4 px-4 @error('description') border-red-500 @enderror"
                            placeholder="Tell us about the history, traditions, and significance..."></textarea>
                        @error('description') <p class="text-red-500 text-[10px] mt-1 font-bold">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Video Section -->
                <div class="pt-10 border-t border-stone-100 dark:border-stone-800 space-y-6" x-data="{ mode: 'file' }">
                    <div class="flex items-center justify-between mb-4">
                        <div
                            class="flex items-center space-x-2 text-amber-600 uppercase tracking-tighter text-xs font-black">
                            <i data-lucide="video" class="w-4 h-4"></i>
                            <span>Video Highlight</span>
                        </div>
                        <div class="flex bg-stone-100 dark:bg-stone-800 p-1 rounded-xl">
                            <button type="button" @click="mode = 'file'"
                                :class="mode === 'file' ? 'bg-white dark:bg-stone-700 shadow-sm text-amber-600' : 'text-stone-500 dark:text-stone-400'"
                                class="px-3 py-1 rounded-lg text-[10px] font-black uppercase transition-all">UPLOAD</button>
                            <button type="button" @click="mode = 'link'"
                                :class="mode === 'link' ? 'bg-white dark:bg-stone-700 shadow-sm text-amber-600' : 'text-stone-500 dark:text-stone-400'"
                                class="px-3 py-1 rounded-lg text-[10px] font-black uppercase transition-all ml-1">LINK</button>
                        </div>
                    </div>

                    <div x-show="mode === 'file'">
                        <label class="block text-[11px] font-black uppercase tracking-widest text-stone-500 mb-2">Video
                            File</label>
                        <div
                            class="border-2 border-amber-200 dark:border-amber-900/50 border-dashed rounded-[1.5rem] p-8 text-center hover:border-amber-400 transition-colors bg-amber-50/30 dark:bg-amber-900/5">
                            <input type="file" name="video_file" id="video_file" class="hidden" accept="video/*"
                                x-on:change="$refs.vLabel.innerText = $el.files[0].name">
                            <label for="video_file" class="cursor-pointer group">
                                <i data-lucide="upload-cloud"
                                    class="w-10 h-10 text-amber-400 mx-auto mb-3 group-hover:scale-110 transition-transform"></i>
                                <p class="text-xs font-bold text-stone-600 dark:text-stone-400" x-ref="vLabel">DROP VIDEO OR
                                    CLICK TO BROWSE</p>
                                <p
                                    class="text-[9px] text-stone-400 dark:text-stone-500 mt-1 uppercase tracking-widest font-black">
                                    MP4, MOV up
                                    to 50MB</p>
                            </label>
                        </div>
                    </div>

                    <div x-show="mode === 'link'" x-cloak>
                        <label for="video_url"
                            class="block text-[11px] font-black uppercase tracking-widest text-stone-500 mb-2">External
                            Video URL</label>
                        <input type="url" name="video_url" id="video_url" class="w-full stnd-input py-3 px-4"
                            placeholder="YouTube or Vimeo link...">
                    </div>

                    <textarea name="video_description" rows="2" class="w-full stnd-input py-3 px-4 text-sm"
                        placeholder="Tell us what makes this video special..."></textarea>
                </div>

                <!-- Audio Section -->
                <div class="pt-10 border-t border-stone-100 dark:border-stone-800 space-y-6" x-data="{ mode: 'file' }">
                    <div class="flex items-center justify-between mb-4">
                        <div
                            class="flex items-center space-x-2 text-amber-600 uppercase tracking-tighter text-xs font-black">
                            <i data-lucide="music" class="w-4 h-4"></i>
                            <span>Audio Archive</span>
                        </div>
                        <div class="flex bg-stone-100 dark:bg-stone-800 p-1 rounded-xl">
                            <button type="button" @click="mode = 'file'"
                                :class="mode === 'file' ? 'bg-white dark:bg-stone-700 shadow-sm text-amber-600' : 'text-stone-500 dark:text-stone-400'"
                                class="px-3 py-1 rounded-lg text-[10px] font-black uppercase transition-all">UPLOAD</button>
                            <button type="button" @click="mode = 'link'"
                                :class="mode === 'link' ? 'bg-white dark:bg-stone-700 shadow-sm text-amber-600' : 'text-stone-500 dark:text-stone-400'"
                                class="px-3 py-1 rounded-lg text-[10px] font-black uppercase transition-all ml-1">LINK</button>
                        </div>
                    </div>

                    <div x-show="mode === 'file'">
                        <label class="block text-[11px] font-black uppercase tracking-widest text-stone-500 mb-2">Audio
                            File</label>
                        <div
                            class="border-2 border-amber-200 dark:border-amber-900/50 border-dashed rounded-[1.5rem] p-8 text-center hover:border-amber-400 transition-colors bg-amber-50/30 dark:bg-amber-900/5">
                            <input type="file" name="audio_file" id="audio_file" class="hidden" accept="audio/*"
                                x-on:change="$refs.aLabel.innerText = $el.files[0].name">
                            <label for="audio_file" class="cursor-pointer group">
                                <i data-lucide="mic-2"
                                    class="w-10 h-10 text-amber-400 mx-auto mb-3 group-hover:scale-110 transition-transform"></i>
                                <p class="text-xs font-bold text-stone-600 dark:text-stone-400" x-ref="aLabel">DROP AUDIO OR
                                    CLICK TO BROWSE</p>
                                <p
                                    class="text-[9px] text-stone-400 dark:text-stone-500 mt-1 uppercase tracking-widest font-black">
                                    MP3, WAV up
                                    to 20MB</p>
                            </label>
                        </div>
                    </div>

                    <div x-show="mode === 'link'" x-cloak>
                        <label for="audio_url"
                            class="block text-[11px] font-black uppercase tracking-widest text-stone-500 mb-2">External
                            Audio URL</label>
                        <input type="url" name="audio_url" id="audio_url" class="w-full stnd-input py-3 px-4"
                            placeholder="SoundCloud or Podcast link...">
                    </div>

                    <textarea name="audio_description" rows="2" class="w-full stnd-input py-3 px-4 text-sm"
                        placeholder="Tell us about this sound recording..."></textarea>
                </div>

                <!-- Media & Licensing Section -->
                <div class="pt-10 border-t border-stone-100 dark:border-stone-800 space-y-6">
                    <div
                        class="flex items-center space-x-2 text-amber-600 uppercase tracking-tighter text-xs font-black mb-4">
                        <i data-lucide="copyright" class="w-4 h-4"></i>
                        <span>Licensing & Cover</span>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8 items-start">
                        <div class="grid grid-cols-1 gap-8 items-start" x-data="{ 
                                        galleryPreviews: [],
                                        handleGallery(e) {
                                            const files = Array.from(e.target.files).slice(0, 5);
                                            this.galleryPreviews = [];
                                            files.forEach(file => {
                                                const reader = new FileReader();
                                                reader.onload = (e) => this.galleryPreviews.push(e.target.result);
                                                reader.readAsDataURL(file);
                                            });
                                        }
                                    }">
                            <!-- Cover Image -->
                            <div>
                                <label
                                    class="block text-[11px] font-black uppercase tracking-widest text-stone-500 mb-2">Primary
                                    Cover Image</label>
                                <div
                                    class="border-2 border-amber-200 dark:border-amber-900/50 border-dashed rounded-2xl p-6 text-center hover:border-amber-400 transition-colors bg-amber-50/10">
                                    <input id="image" name="image" type="file" class="hidden" accept="image/*"
                                        x-on:change="$refs.iLabel.innerText = $el.files[0].name">
                                    <label for="image" class="cursor-pointer group">
                                        <i data-lucide="image"
                                            class="w-8 h-8 text-amber-400 mx-auto mb-2 group-hover:scale-110 transition-transform"></i>
                                        <p class="text-[10px] font-black text-stone-600 dark:text-stone-400 uppercase"
                                            x-ref="iLabel">Select
                                            Story Cover</p>
                                    </label>
                                </div>
                            </div>

                            <!-- Gallery Images -->
                            <div class="pt-6 border-t border-stone-100 dark:border-stone-800">
                                <label
                                    class="block text-[11px] font-black uppercase tracking-widest text-stone-500 mb-2 flex justify-between">
                                    <span>Visual Gallery (Up to 5 Images)</span>
                                    <span class="text-amber-600" x-text="galleryPreviews.length + '/5'"></span>
                                </label>
                                <div
                                    class="border-2 border-stone-200 dark:border-stone-700 border-dashed rounded-2xl p-6 text-center hover:border-amber-400 transition-colors">
                                    <input id="images" name="images[]" type="file" class="hidden" accept="image/*" multiple
                                        @change="handleGallery">
                                    <label for="images" class="cursor-pointer group">
                                        <i data-lucide="layout-grid"
                                            class="w-8 h-8 text-stone-300 mx-auto mb-2 group-hover:text-amber-400 transition-colors"></i>
                                        <p class="text-[10px] font-black text-stone-500 uppercase">Add Gallery Photos</p>
                                    </label>
                                </div>

                                <!-- Gallery Previews -->
                                <div class="grid grid-cols-5 gap-2 mt-4" x-show="galleryPreviews.length > 0">
                                    <template x-for="(src, index) in galleryPreviews" :key="index">
                                        <div
                                            class="aspect-square rounded-lg overflow-hidden border border-stone-200 dark:border-stone-700 bg-stone-50 dark:bg-stone-800 relative group">
                                            <img :src="src" class="w-full h-full object-cover">
                                            <div
                                                class="absolute inset-0 bg-black/40 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity">
                                                <span class="text-white text-[10px] font-bold"
                                                    x-text="'#' + (index + 1)"></span>
                                            </div>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </div>
                        <div class="space-y-4">
                            <div>
                                <label for="license_type"
                                    class="block text-[11px] font-black uppercase tracking-widest text-stone-500 mb-2 font-bold">License
                                    Type</label>
                                <select name="license_type" id="license_type"
                                    class="w-full stnd-input py-3 px-4 text-sm font-bold appearance-none cursor-pointer">
                                    @foreach($licenses as $license)
                                        <option value="{{ $license }}">{{ $license }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label for="license_credit"
                                    class="block text-[11px] font-black uppercase tracking-widest text-stone-500 mb-2 font-bold">Attribution
                                    / Credit</label>
                                <input type="text" name="license_credit" id="license_credit"
                                    class="w-full stnd-input py-3 px-4 text-sm font-medium"
                                    placeholder="e.g. Photo by John Doe, Archives of Maasai...">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Submit Button -->
            <div class="pt-10">
                <button type="submit"
                    class="w-full flex items-center justify-center space-x-3 glass-orange text-white px-8 py-5 rounded-[1.5rem] font-black text-lg transition-all duration-300 shadow-xl hover:scale-[1.01] transform uppercase tracking-widest">
                    <i data-lucide="send" class="w-6 h-6"></i>
                    <span>Preserve Culture</span>
                </button>
            </div>
        </form>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => lucide.createIcons());
    </script>
@endpush