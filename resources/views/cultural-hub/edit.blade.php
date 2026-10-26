@extends('layouts.app')

@section('title', 'Refine Heritage Story - Cultural Hub')

@section('content')
    <div class="max-w-3xl mx-auto p-4 sm:p-6 lg:p-8">

        <!-- Header -->
        <div class="mb-8 text-center sm:text-left">
            <div class="flex flex-col sm:flex-row sm:items-center sm:space-x-4 mb-4">
                <a href="{{ route('cultural-hub.show', $culture->id) }}"
                    class="p-2 text-stone-600 hover:text-stone-800 hover:bg-stone-100 rounded-lg transition-colors mb-2 sm:mb-0 w-fit">
                    <i data-lucide="arrow-left" class="w-5 h-5"></i>
                </a>
                <div class="flex items-center space-x-3">
                    <i data-lucide="edit-3" class="w-8 h-8 text-amber-600"></i>
                    <h1 class="text-2xl sm:text-3xl font-bold text-stone-800">Refine Legend</h1>
                </div>
            </div>
            <p class="text-stone-600 text-sm sm:text-base">Enhance and clarify the details of this cultural enshrinement.
            </p>
        </div>

        @include('partials.image-compression')

        <form action="{{ route('cultural-hub.update', $culture->id) }}" method="POST" enctype="multipart/form-data"
            @submit.prevent="if(await handleFormImageCompression($el)) $el.submit()"
            class="bg-white rounded-[2.5rem] p-8 sm:p-12 shadow-2xl border border-stone-100 space-y-10">
            @csrf
            @method('PUT')

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
                            <input type="text" name="name" id="name" required value="{{ old('name', $culture->name) }}"
                                class="w-full stnd-input py-3 px-4 @error('name') border-red-500 @enderror"
                                placeholder="e.g. Maasai, Yoruba, Celtic...">
                            @error('name') <p class="text-red-500 text-[10px] mt-1 font-bold">{{ $message }}</p> @enderror
                        </div>

                        <!-- Region -->
                        <div>
                            <label for="region"
                                class="block text-[11px] font-black uppercase tracking-widest text-stone-500 mb-2">Region/Origin</label>
                            <input type="text" name="region" id="region" required
                                value="{{ old('region', $culture->region) }}"
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
                                <option value="{{ $cat }}" {{ (old('category', $culture->category) === $cat) ? 'selected' : '' }}>
                                    {{ $cat }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Description -->
                    <div x-data="{ 
                                                content: @js(old('description', $culture->description)),
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
                <div class="pt-10 border-t border-stone-100 space-y-6"
                    x-data="{ mode: '{{ $culture->video_url ? 'link' : 'file' }}' }">
                    <div class="flex items-center justify-between mb-4">
                        <div
                            class="flex items-center space-x-2 text-amber-600 uppercase tracking-tighter text-xs font-black">
                            <i data-lucide="video" class="w-4 h-4"></i>
                            <span>Video Highlight</span>
                        </div>
                        <div class="flex bg-stone-100 p-1 rounded-xl">
                            <button type="button" @click="mode = 'file'"
                                :class="mode === 'file' ? 'bg-white shadow-sm text-amber-600' : 'text-stone-500'"
                                class="px-3 py-1 rounded-lg text-[10px] font-black uppercase transition-all">UPLOAD</button>
                            <button type="button" @click="mode = 'link'"
                                :class="mode === 'link' ? 'bg-white shadow-sm text-amber-600' : 'text-stone-500'"
                                class="px-3 py-1 rounded-lg text-[10px] font-black uppercase transition-all ml-1">LINK</button>
                        </div>
                    </div>

                    <div x-show="mode === 'file'">
                        <label class="block text-[11px] font-black uppercase tracking-widest text-stone-500 mb-2">Video
                            File</label>
                        @if($culture->video_path)
                            <p class="text-[9px] font-black text-amber-600 mb-2 uppercase tracking-widest">CURRENT:
                                {{ basename($culture->video_path) }}
                            </p>
                        @endif
                        <div
                            class="border-2 border-amber-200 border-dashed rounded-[1.5rem] p-8 text-center hover:border-amber-400 transition-colors bg-amber-50/30">
                            <input type="file" name="video_file" id="video_file" class="hidden" accept="video/*"
                                x-on:change="$refs.vLabel.innerText = $el.files[0].name">
                            <label for="video_file" class="cursor-pointer group">
                                <i data-lucide="upload-cloud"
                                    class="w-10 h-10 text-amber-400 mx-auto mb-3 group-hover:scale-110 transition-transform"></i>
                                <p class="text-xs font-bold text-stone-600" x-ref="vLabel">DROP NEW VIDEO OR CLICK TO BROWSE
                                </p>
                                <p class="text-[9px] text-stone-400 mt-1 uppercase tracking-widest font-black">MP4, MOV up
                                    to 50MB</p>
                            </label>
                        </div>
                    </div>

                    <div x-show="mode === 'link'" x-cloak>
                        <label for="video_url"
                            class="block text-[11px] font-black uppercase tracking-widest text-stone-500 mb-2">External
                            Video URL</label>
                        <input type="url" name="video_url" id="video_url" class="w-full stnd-input py-3 px-4"
                            placeholder="YouTube or Vimeo link..." value="{{ old('video_url', $culture->video_url) }}">
                    </div>

                    <textarea name="video_description" rows="2" class="w-full stnd-input py-3 px-4 text-sm"
                        placeholder="Tell us what makes this video special...">{{ old('video_description', $culture->video_description) }}</textarea>
                </div>

                <!-- Audio Section -->
                <div class="pt-10 border-t border-stone-100 space-y-6"
                    x-data="{ mode: '{{ $culture->audio_url ? 'link' : 'file' }}' }">
                    <div class="flex items-center justify-between mb-4">
                        <div
                            class="flex items-center space-x-2 text-amber-600 uppercase tracking-tighter text-xs font-black">
                            <i data-lucide="music" class="w-4 h-4"></i>
                            <span>Audio Archive</span>
                        </div>
                        <div class="flex bg-stone-100 p-1 rounded-xl">
                            <button type="button" @click="mode = 'file'"
                                :class="mode === 'file' ? 'bg-white shadow-sm text-amber-600' : 'text-stone-500'"
                                class="px-3 py-1 rounded-lg text-[10px] font-black uppercase transition-all">UPLOAD</button>
                            <button type="button" @click="mode = 'link'"
                                :class="mode === 'link' ? 'bg-white shadow-sm text-amber-600' : 'text-stone-500'"
                                class="px-3 py-1 rounded-lg text-[10px] font-black uppercase transition-all ml-1">LINK</button>
                        </div>
                    </div>

                    <div x-show="mode === 'file'">
                        <label class="block text-[11px] font-black uppercase tracking-widest text-stone-500 mb-2">Audio
                            File</label>
                        @if($culture->audio_path)
                            <p class="text-[9px] font-black text-amber-600 mb-2 uppercase tracking-widest">CURRENT:
                                {{ basename($culture->audio_path) }}
                            </p>
                        @endif
                        <div
                            class="border-2 border-amber-200 border-dashed rounded-[1.5rem] p-8 text-center hover:border-amber-400 transition-colors bg-amber-50/30">
                            <input type="file" name="audio_file" id="audio_file" class="hidden" accept="audio/*"
                                x-on:change="$refs.aLabel.innerText = $el.files[0].name">
                            <label for="audio_file" class="cursor-pointer group">
                                <i data-lucide="mic-2"
                                    class="w-10 h-10 text-amber-400 mx-auto mb-3 group-hover:scale-110 transition-transform"></i>
                                <p class="text-xs font-bold text-stone-600" x-ref="aLabel">DROP NEW AUDIO OR CLICK TO BROWSE
                                </p>
                                <p class="text-[9px] text-stone-400 mt-1 uppercase tracking-widest font-black">MP3, WAV up
                                    to 20MB</p>
                            </label>
                        </div>
                    </div>

                    <div x-show="mode === 'link'" x-cloak>
                        <label for="audio_url"
                            class="block text-[11px] font-black uppercase tracking-widest text-stone-500 mb-2">External
                            Audio URL</label>
                        <input type="url" name="audio_url" id="audio_url" class="w-full stnd-input py-3 px-4"
                            placeholder="SoundCloud or Podcast link..." value="{{ old('audio_url', $culture->audio_url) }}">
                    </div>

                    <textarea name="audio_description" rows="2" class="w-full stnd-input py-3 px-4 text-sm"
                        placeholder="Tell us about this sound recording...">{{ old('audio_description', $culture->audio_description) }}</textarea>
                </div>

                <!-- Media & Licensing Section -->
                <div class="pt-10 border-t border-stone-100 space-y-6">
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
                                @if($culture->image)
                                    <div class="mb-4 aspect-video rounded-xl overflow-hidden border border-amber-200">
                                        <img src="{{ Storage::url($culture->image) }}" class="w-full h-full object-cover">
                                    </div>
                                @endif
                                <div
                                    class="border-2 border-amber-200 border-dashed rounded-2xl p-6 text-center hover:border-amber-400 transition-colors bg-amber-50/10">
                                    <input id="image" name="image" type="file" class="hidden" accept="image/*"
                                        x-on:change="$refs.iLabel.innerText = $el.files[0].name">
                                    <label for="image" class="cursor-pointer group">
                                        <i data-lucide="image"
                                            class="w-8 h-8 text-amber-400 mx-auto mb-2 group-hover:scale-110 transition-transform"></i>
                                        <p class="text-[10px] font-black text-stone-600 uppercase" x-ref="iLabel">Replace
                                            Story Cover</p>
                                    </label>
                                </div>
                            </div>

                            <!-- Gallery Images -->
                            <div class="pt-6 border-t border-stone-100">
                                <label
                                    class="block text-[11px] font-black uppercase tracking-widest text-stone-500 mb-2 flex justify-between">
                                    <span>Visual Gallery (Up to 5 Images)</span>
                                    <span class="text-amber-600" x-text="galleryPreviews.length + '/5'"></span>
                                </label>
                                @if($culture->media_files && count($culture->media_files) > 0)
                                    <div class="grid grid-cols-5 gap-2 mb-4">
                                        @foreach($culture->media_files as $file)
                                            <div class="aspect-square rounded-lg overflow-hidden border border-stone-100">
                                                <img src="{{ Storage::url($file) }}" class="w-full h-full object-cover">
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                                <div
                                    class="border-2 border-stone-200 border-dashed rounded-2xl p-6 text-center hover:border-amber-400 transition-colors">
                                    <input id="images" name="images[]" type="file" class="hidden" accept="image/*" multiple
                                        @change="handleGallery">
                                    <label for="images" class="cursor-pointer group">
                                        <i data-lucide="layout-grid"
                                            class="w-8 h-8 text-stone-300 mx-auto mb-2 group-hover:text-amber-400 transition-colors"></i>
                                        <p class="text-[10px] font-black text-stone-500 uppercase">Replace Gallery Photos
                                        </p>
                                    </label>
                                </div>

                                <!-- Gallery Previews -->
                                <div class="grid grid-cols-5 gap-2 mt-4" x-show="galleryPreviews.length > 0">
                                    <template x-for="(src, index) in galleryPreviews" :key="index">
                                        <div
                                            class="aspect-square rounded-lg overflow-hidden border border-stone-200 bg-stone-50 relative group">
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
                                        <option value="{{ $license }}" {{ (old('license_type', $culture->license_type) === $license) ? 'selected' : '' }}>{{ $license }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label for="license_credit"
                                    class="block text-[11px] font-black uppercase tracking-widest text-stone-500 mb-2 font-bold">Attribution
                                    / Credit</label>
                                <input type="text" name="license_credit" id="license_credit"
                                    class="w-full stnd-input py-3 px-4 text-sm font-medium"
                                    placeholder="e.g. Photo by John Doe, Archives of Maasai..."
                                    value="{{ old('license_credit', $culture->license_credit) }}">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Submit Button -->
            <div class="pt-10">
                <button type="submit"
                    class="w-full flex items-center justify-center space-x-3 glass-orange text-white px-8 py-5 rounded-[1.5rem] font-black text-lg transition-all duration-300 shadow-xl hover:scale-[1.01] transform uppercase tracking-widest">
                    <i data-lucide="save" class="w-6 h-6"></i>
                    <span>Update Record</span>
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