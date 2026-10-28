@extends('layouts.app')

@section('title', 'Refine Cultural Record - ' . $culture->name . ' - Kulturegram')

@section('content')
<div class="max-w-4xl mx-auto p-4 sm:p-6 lg:p-8"
     x-data="{
        recordType: '{{ old('subtype', $culture->subtype ?? 'oral_history') }}',
        description: @js(old('description', $culture->description))
     }">

    <!-- Header -->
    <div class="mb-8">
        <div class="flex items-center space-x-3 mb-2">
            <a href="{{ route('cultural-hub.show', $culture->id) }}"
               class="p-2 text-stone-600 dark:text-stone-400 hover:text-stone-800 dark:hover:text-stone-200 hover:bg-stone-100 dark:hover:bg-stone-800 rounded-lg transition-colors">
                <i data-lucide="arrow-left" class="w-5 h-5"></i>
            </a>
            <div class="flex items-center space-x-2.5">
                <div class="w-8 h-8 rounded-lg bg-amber-500 text-white flex items-center justify-center">
                    <i data-lucide="edit-3" class="w-5 h-5"></i>
                </div>
                <h1 class="page-title text-2xl sm:text-3xl font-extrabold text-amber-500">
                    Refine Cultural Record
                </h1>
            </div>
        </div>
        <p class="text-xs sm:text-sm text-stone-600 dark:text-stone-400 max-w-2xl">
            Update documentation, citations, or community context for <strong>{{ $culture->name }}</strong> (#CR-{{ str_pad($culture->id, 5, '0', STR_PAD_LEFT) }}).
        </p>
    </div>

    @include('partials.image-compression')

    <form action="{{ route('cultural-hub.update', $culture->id) }}" method="POST" enctype="multipart/form-data"
          @submit.prevent="if(await handleFormImageCompression($el)) $el.submit()"
          class="bg-white dark:bg-stone-900 rounded-3xl p-6 sm:p-10 shadow-sm border border-stone-200/80 dark:border-stone-800 space-y-8">
        @csrf
        @method('PUT')

        {{-- 1. RECORD TYPE / SUBTYPE --}}
        <div class="space-y-3">
            <label class="block text-xs font-bold uppercase tracking-wider text-amber-700 dark:text-amber-400">
                1. Cultural Record Type
            </label>
            <div class="grid grid-cols-2 sm:grid-cols-3 gap-2.5">
                <button type="button" 
                        @click="recordType = 'oral_history'"
                        class="p-3 rounded-xl border text-left transition flex flex-col justify-between gap-1"
                        :class="recordType === 'oral_history' 
                            ? 'border-amber-500 bg-amber-500/10 text-amber-900 dark:text-amber-200 font-bold' 
                            : 'border-stone-200 dark:border-stone-800 text-stone-700 dark:text-stone-300 hover:border-amber-400'">
                    <i data-lucide="mic" class="w-4 h-4 text-amber-500"></i>
                    <span class="text-xs">Oral History</span>
                    <span class="text-[10px] text-stone-500 font-normal">Testimony & Memory</span>
                </button>

                <button type="button" 
                        @click="recordType = 'performance'"
                        class="p-3 rounded-xl border text-left transition flex flex-col justify-between gap-1"
                        :class="recordType === 'performance' 
                            ? 'border-amber-500 bg-amber-500/10 text-amber-900 dark:text-amber-200 font-bold' 
                            : 'border-stone-200 dark:border-stone-800 text-stone-700 dark:text-stone-300 hover:border-amber-400'">
                    <i data-lucide="music" class="w-4 h-4 text-amber-500"></i>
                    <span class="text-xs">Song & Performance</span>
                    <span class="text-[10px] text-stone-500 font-normal">Music & Dance</span>
                </button>

                <button type="button" 
                        @click="recordType = 'recipe'"
                        class="p-3 rounded-xl border text-left transition flex flex-col justify-between gap-1"
                        :class="recordType === 'recipe' 
                            ? 'border-amber-500 bg-amber-500/10 text-amber-900 dark:text-amber-200 font-bold' 
                            : 'border-stone-200 dark:border-stone-800 text-stone-700 dark:text-stone-300 hover:border-amber-400'">
                    <i data-lucide="utensils" class="w-4 h-4 text-amber-500"></i>
                    <span class="text-xs">Food Tradition</span>
                    <span class="text-[10px] text-stone-500 font-normal">Culinary Heritage</span>
                </button>

                <button type="button" 
                        @click="recordType = 'craft'"
                        class="p-3 rounded-xl border text-left transition flex flex-col justify-between gap-1"
                        :class="recordType === 'craft' 
                            ? 'border-amber-500 bg-amber-500/10 text-amber-900 dark:text-amber-200 font-bold' 
                            : 'border-stone-200 dark:border-stone-800 text-stone-700 dark:text-stone-300 hover:border-amber-400'">
                    <i data-lucide="hammer" class="w-4 h-4 text-amber-500"></i>
                    <span class="text-xs">Craft & Artefact</span>
                    <span class="text-[10px] text-stone-500 font-normal">Material Culture</span>
                </button>

                <button type="button" 
                        @click="recordType = 'ceremony'"
                        class="p-3 rounded-xl border text-left transition flex flex-col justify-between gap-1"
                        :class="recordType === 'ceremony' 
                            ? 'border-amber-500 bg-amber-500/10 text-amber-900 dark:text-amber-200 font-bold' 
                            : 'border-stone-200 dark:border-stone-800 text-stone-700 dark:text-stone-300 hover:border-amber-400'">
                    <i data-lucide="compass" class="w-4 h-4 text-amber-500"></i>
                    <span class="text-xs">Ceremony & Sacred</span>
                    <span class="text-[10px] text-stone-500 font-normal">Rituals & Rites</span>
                </button>

                <button type="button" 
                        @click="recordType = 'history'"
                        class="p-3 rounded-xl border text-left transition flex flex-col justify-between gap-1"
                        :class="recordType === 'history' 
                            ? 'border-amber-500 bg-amber-500/10 text-amber-900 dark:text-amber-200 font-bold' 
                            : 'border-stone-200 dark:border-stone-800 text-stone-700 dark:text-stone-300 hover:border-amber-400'">
                    <i data-lucide="landmark" class="w-4 h-4 text-amber-500"></i>
                    <span class="text-xs">Historical Account</span>
                    <span class="text-[10px] text-stone-500 font-normal">Chronicles</span>
                </button>
            </div>
            <input type="hidden" name="subtype" :value="recordType">
        </div>

        {{-- 2. CORE IDENTITY & CLASSIFICATION --}}
        <div class="space-y-4 pt-6 border-t border-stone-200 dark:border-stone-800">
            <h3 class="text-xs font-bold uppercase tracking-wider text-amber-700 dark:text-amber-400 flex items-center gap-1.5">
                <i data-lucide="info" class="w-4 h-4"></i>
                <span>2. Cultural Identity & Context</span>
            </h3>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                {{-- Title --}}
                <div>
                    <label class="block text-xs font-semibold text-stone-700 dark:text-stone-300 mb-1">
                        Cultural Record Title *
                    </label>
                    <input type="text" name="name" required
                           value="{{ old('name', $culture->name) }}"
                           class="w-full text-xs rounded-xl border border-stone-300 dark:border-stone-700 bg-white dark:bg-stone-800 p-3">
                    @error('name') <p class="text-red-500 text-[10px] mt-1">{{ $message }}</p> @enderror
                </div>

                {{-- Translated Title --}}
                <div>
                    <label class="block text-xs font-semibold text-stone-700 dark:text-stone-300 mb-1">
                        Translated / Alternative Title (Optional)
                    </label>
                    <input type="text" name="translated_title"
                           value="{{ old('translated_title', $culture->translated_title) }}"
                           placeholder="Widely understood equivalent title"
                           class="w-full text-xs rounded-xl border border-stone-300 dark:border-stone-700 bg-white dark:bg-stone-800 p-3">
                </div>

                {{-- Community / Cultural Group --}}
                <div>
                    <label class="block text-xs font-semibold text-stone-700 dark:text-stone-300 mb-1">
                        Community / Cultural Group (Optional)
                    </label>
                    <input type="text" name="cultural_group"
                           value="{{ old('cultural_group', $culture->cultural_group) }}"
                           placeholder="e.g. Tangale, Yoruba, Oromo..."
                           class="w-full text-xs rounded-xl border border-stone-300 dark:border-stone-700 bg-white dark:bg-stone-800 p-3">
                </div>

                {{-- Category --}}
                <div>
                    <label class="block text-xs font-semibold text-stone-700 dark:text-stone-300 mb-1">
                        Primary Category *
                    </label>
                    <select name="category" required
                            class="w-full text-xs rounded-xl border border-stone-300 dark:border-stone-700 bg-white dark:bg-stone-800 p-3">
                        @foreach($categories as $cat)
                            <option value="{{ $cat }}" {{ (old('category', $culture->category) === $cat) ? 'selected' : '' }}>
                                {{ $cat }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Region --}}
                <div>
                    <label class="block text-xs font-semibold text-stone-700 dark:text-stone-300 mb-1">
                        Region / Country *
                    </label>
                    <input type="text" name="region" required
                           value="{{ old('region', $culture->region) }}"
                           class="w-full text-xs rounded-xl border border-stone-300 dark:border-stone-700 bg-white dark:bg-stone-800 p-3">
                    @error('region') <p class="text-red-500 text-[10px] mt-1">{{ $message }}</p> @enderror
                </div>

                {{-- Locality --}}
                <div>
                    <label class="block text-xs font-semibold text-stone-700 dark:text-stone-300 mb-1">
                        Locality / Town (Optional)
                    </label>
                    <input type="text" name="locality"
                           value="{{ old('locality', $culture->locality) }}"
                           class="w-full text-xs rounded-xl border border-stone-300 dark:border-stone-700 bg-white dark:bg-stone-800 p-3">
                </div>

                {{-- Primary Language --}}
                <div>
                    <label class="block text-xs font-semibold text-stone-700 dark:text-stone-300 mb-1">
                        Primary Language / Dialect (Optional)
                    </label>
                    <input type="text" name="language"
                           value="{{ old('language', $culture->language) }}"
                           class="w-full text-xs rounded-xl border border-stone-300 dark:border-stone-700 bg-white dark:bg-stone-800 p-3">
                </div>

                {{-- Sensitivity Level --}}
                <div>
                    <label class="block text-xs font-semibold text-stone-700 dark:text-stone-300 mb-1">
                        Knowledge Access Control *
                    </label>
                    <select name="sensitivity_level" required
                            class="w-full text-xs rounded-xl border border-stone-300 dark:border-stone-700 bg-white dark:bg-stone-800 p-3">
                        <option value="public" {{ (old('sensitivity_level', $culture->sensitivity_level) === 'public') ? 'selected' : '' }}>Public Archive</option>
                        <option value="community_only" {{ (old('sensitivity_level', $culture->sensitivity_level) === 'community_only') ? 'selected' : '' }}>Community Custodians Only</option>
                        <option value="restricted" {{ (old('sensitivity_level', $culture->sensitivity_level) === 'restricted') ? 'selected' : '' }}>Restricted Sacred Knowledge</option>
                    </select>
                </div>
            </div>

            {{-- Description --}}
            <div>
                <label class="block text-xs font-semibold text-stone-700 dark:text-stone-300 mb-1">
                    Core Cultural Overview & Synthesis *
                </label>
                <textarea name="description" rows="5" required
                          x-model="description"
                          class="w-full text-xs rounded-xl border border-stone-300 dark:border-stone-700 bg-white dark:bg-stone-800 p-3"></textarea>
                @error('description') <p class="text-red-500 text-[10px] mt-1">{{ $message }}</p> @enderror
            </div>
        </div>

        {{-- 3. HISTORICAL & CUSTOMARY CONTEXT --}}
        <div class="space-y-4 pt-6 border-t border-stone-200 dark:border-stone-800">
            <h3 class="text-xs font-bold uppercase tracking-wider text-amber-700 dark:text-amber-400 flex items-center gap-1.5">
                <i data-lucide="history" class="w-4 h-4"></i>
                <span>3. History & Living Practice Context</span>
            </h3>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-stone-700 dark:text-stone-300 mb-1">
                        Historical Period (Optional)
                    </label>
                    <input type="text" name="historical_period"
                           value="{{ old('historical_period', $culture->historical_period) }}"
                           class="w-full text-xs rounded-xl border border-stone-300 dark:border-stone-700 bg-white dark:bg-stone-800 p-3">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-stone-700 dark:text-stone-300 mb-1">
                        Documented Practitioners (Optional)
                    </label>
                    <input type="text" name="current_practitioners"
                           value="{{ old('current_practitioners', $culture->current_practitioners) }}"
                           class="w-full text-xs rounded-xl border border-stone-300 dark:border-stone-700 bg-white dark:bg-stone-800 p-3">
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-stone-700 dark:text-stone-300 mb-1">
                    Cultural Significance / Spiritual Meaning (Optional)
                </label>
                <textarea name="significance" rows="3"
                          class="w-full text-xs rounded-xl border border-stone-300 dark:border-stone-700 bg-white dark:bg-stone-800 p-3">{{ old('significance', $culture->significance) }}</textarea>
            </div>

            <div>
                <label class="block text-xs font-semibold text-stone-700 dark:text-stone-300 mb-1">
                    Customary Rituals & Ceremonial Steps (Optional)
                </label>
                <textarea name="rituals" rows="3"
                          class="w-full text-xs rounded-xl border border-stone-300 dark:border-stone-700 bg-white dark:bg-stone-800 p-3">{{ old('rituals', $culture->rituals) }}</textarea>
            </div>
        </div>

        {{-- 4. MEDIA & LICENSING --}}
        <div class="space-y-4 pt-6 border-t border-stone-200 dark:border-stone-800">
            <h3 class="text-xs font-bold uppercase tracking-wider text-amber-700 dark:text-amber-400 flex items-center gap-1.5">
                <i data-lucide="upload" class="w-4 h-4"></i>
                <span>4. Media & Rights</span>
            </h3>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-stone-700 dark:text-stone-300 mb-1">
                        Replace Cover Image (Optional)
                    </label>
                    <input type="file" name="image" accept="image/*"
                           class="w-full text-xs text-stone-500 file:mr-3 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-amber-500/10 file:text-amber-800 hover:file:bg-amber-500/20">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-stone-700 dark:text-stone-300 mb-1">
                        Cultural Rights / License *
                    </label>
                    <select name="license_type" required
                            class="w-full text-xs rounded-xl border border-stone-300 dark:border-stone-700 bg-white dark:bg-stone-800 p-3">
                        @foreach($licenses as $lic)
                            <option value="{{ $lic }}" {{ (old('license_type', $culture->license_type) === $lic) ? 'selected' : '' }}>
                                {{ $lic }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        {{-- SUBMIT ACTIONS --}}
        <div class="pt-6 border-t border-stone-200 dark:border-stone-800 flex items-center justify-between">
            <a href="{{ route('cultural-hub.show', $culture->id) }}" class="text-xs font-bold text-stone-500 hover:text-stone-700">
                Cancel
            </a>

            <button type="submit"
                    class="px-6 py-2.5 rounded-xl bg-amber-500 hover:bg-amber-600 text-white font-bold text-xs transition shadow-sm flex items-center gap-1.5">
                <i data-lucide="check" class="w-4 h-4"></i>
                <span>Save Archival Updates</span>
            </button>
        </div>

    </form>
</div>
@endsection