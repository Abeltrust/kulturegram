@extends('layouts.app')

@section('title', 'Document Cultural Heritage - Cultural Archive - Kulturegram')

@section('content')
<div class="max-w-4xl mx-auto p-4 sm:p-6 lg:p-8"
     x-data="culturalArchiveForm({
        recordType: '{{ old('record_type', 'oral_history') }}',
        description: @js(old('description', '')),
        aiStructuring: false,
        sourceText: '',
        aiResult: null,
        isDraft: false
     })">

    <!-- Header -->
    <div class="mb-8">
        <div class="flex items-center space-x-3 mb-2">
            <a href="{{ route('cultural-hub.index') }}"
               class="p-2 text-stone-600 dark:text-stone-400 hover:text-stone-800 dark:hover:text-stone-200 hover:bg-stone-100 dark:hover:bg-stone-800 rounded-lg transition-colors">
                <i data-lucide="arrow-left" class="w-5 h-5"></i>
            </a>
            <div class="flex items-center space-x-2.5">
                <div class="w-8 h-8 rounded-lg bg-amber-500 text-white flex items-center justify-center">
                    <i data-lucide="archive" class="w-5 h-5"></i>
                </div>
                <h1 class="text-2xl sm:text-3xl font-extrabold text-stone-900 dark:text-stone-100">
                    Document Living Heritage
                </h1>
            </div>
        </div>
        <p class="text-xs sm:text-sm text-stone-600 dark:text-stone-400 max-w-2xl">
            Preserve authentic testimony, practices, languages, and artefacts. The form adapts dynamically based on the cultural record type you are documenting.
        </p>
    </div>

    <!-- AI Assistance Accordion / Quick Import -->
    <div class="mb-8 p-5 bg-amber-50/70 dark:bg-amber-950/30 border border-amber-200 dark:border-amber-900/60 rounded-2xl space-y-3">
        <div class="flex items-center justify-between flex-wrap gap-2">
            <div class="flex items-center space-x-2 text-xs font-bold text-amber-900 dark:text-amber-300">
                <i data-lucide="sparkles" class="w-4 h-4 text-amber-600"></i>
                <span>AI Structuring Assistant (Optional)</span>
            </div>
            <button type="button" 
                    @click="aiStructuring = !aiStructuring"
                    class="text-xs font-bold text-amber-700 dark:text-amber-400 hover:underline flex items-center gap-1">
                <span x-text="aiStructuring ? 'Hide Assistant' : 'Structure From Raw Narrative'"></span>
                <i data-lucide="chevron-down" class="w-3.5 h-3.5 transition-transform" :class="aiStructuring ? 'rotate-180' : ''"></i>
            </button>
        </div>

        <div x-show="aiStructuring" x-cloak class="pt-3 border-t border-amber-200/80 dark:border-amber-900/80 space-y-3">
            <p class="text-xs text-stone-600 dark:text-stone-400 leading-relaxed">
                Paste raw oral history, field notes, or transcribed audio. AI will propose structured fields, traceable claims, and follow-up inquiries.
            </p>
            <textarea x-model="sourceText" 
                      rows="4" 
                      placeholder="Paste unstructured cultural account or testimony here..."
                      class="w-full text-xs rounded-xl border border-amber-300 dark:border-amber-800 bg-white dark:bg-stone-900 p-3 text-stone-800 dark:text-stone-200"></textarea>
            
            <div class="flex items-center justify-between">
                <span class="text-[11px] text-stone-500 italic">No facts will be fabricated; unknown fields will be flagged explicitly.</span>
                <button type="button" 
                        @click="runStructuring()"
                        :disabled="!sourceText.trim() || isProcessingAI"
                        class="px-4 py-2 bg-amber-500 hover:bg-amber-600 disabled:opacity-50 text-white font-bold text-xs rounded-xl transition flex items-center gap-1.5">
                    <i data-lucide="wand-2" class="w-3.5 h-3.5"></i>
                    <span x-text="isProcessingAI ? 'Analyzing...' : 'Structure Narrative'"></span>
                </button>
            </div>
        </div>
    </div>

    @include('partials.image-compression')

    <form action="{{ route('cultural-hub.store') }}" method="POST" enctype="multipart/form-data"
          @submit.prevent="if(await handleFormImageCompression($el)) $el.submit()"
          class="bg-white dark:bg-stone-900 rounded-3xl p-6 sm:p-10 shadow-sm border border-stone-200/80 dark:border-stone-800 space-y-8">
        @csrf

        {{-- Hidden Draft Input --}}
        <input type="hidden" name="status" :value="isDraft ? 'draft' : 'pending_review'">

        {{-- 1. RECORD TYPE SELECTOR --}}
        <div class="space-y-3">
            <label class="block text-xs font-bold uppercase tracking-wider text-amber-700 dark:text-amber-400">
                1. Cultural Record Type *
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
                    <span class="text-[10px] text-stone-500 font-normal">Music, Chants & Dance</span>
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
                    <span class="text-[10px] text-stone-500 font-normal">Chronicles & Archival</span>
                </button>
            </div>
            <input type="hidden" name="subtype" :value="recordType">
        </div>

        {{-- 2. CORE IDENTITY & COMMON FIELDS --}}
        <div class="space-y-4 pt-6 border-t border-stone-200 dark:border-stone-800">
            <h3 class="text-xs font-bold uppercase tracking-wider text-amber-700 dark:text-amber-400 flex items-center gap-1.5">
                <i data-lucide="info" class="w-4 h-4"></i>
                <span>2. Cultural Identity & Classification</span>
            </h3>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                {{-- Title --}}
                <div>
                    <label class="block text-xs font-semibold text-stone-700 dark:text-stone-300 mb-1">
                        Cultural Record Title *
                    </label>
                    <input type="text" name="name" required
                           x-ref="nameInput"
                           value="{{ old('name') }}"
                           placeholder="e.g. Tangale Traditional Marriage & Dowry Customs"
                           class="w-full text-xs rounded-xl border border-stone-300 dark:border-stone-700 bg-white dark:bg-stone-800 p-3">
                    @error('name') <p class="text-red-500 text-[10px] mt-1">{{ $message }}</p> @enderror
                </div>

                {{-- Optional Translated Title --}}
                <div>
                    <label class="block text-xs font-semibold text-stone-700 dark:text-stone-300 mb-1">
                        Translated / Alternative Title (Optional)
                    </label>
                    <input type="text" name="translated_title"
                           x-ref="translatedTitleInput"
                           value="{{ old('translated_title') }}"
                           placeholder="English or widely understood equivalent"
                           class="w-full text-xs rounded-xl border border-stone-300 dark:border-stone-700 bg-white dark:bg-stone-800 p-3">
                </div>

                {{-- Community / Cultural Group --}}
                <div>
                    <label class="block text-xs font-semibold text-stone-700 dark:text-stone-300 mb-1">
                        Community or Cultural Group (Optional)
                    </label>
                    <input type="text" name="cultural_group"
                           x-ref="groupInput"
                           value="{{ old('cultural_group') }}"
                           placeholder="e.g. Tangale, Yoruba, Oromo, Celtic..."
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
                            <option value="{{ $cat }}" {{ old('category') === $cat ? 'selected' : '' }}>{{ $cat }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Region --}}
                <div>
                    <label class="block text-xs font-semibold text-stone-700 dark:text-stone-300 mb-1">
                        Region / Country *
                    </label>
                    <input type="text" name="region" required
                           x-ref="regionInput"
                           value="{{ old('region') }}"
                           placeholder="e.g. Gombe State, Nigeria"
                           class="w-full text-xs rounded-xl border border-stone-300 dark:border-stone-700 bg-white dark:bg-stone-800 p-3">
                    @error('region') <p class="text-red-500 text-[10px] mt-1">{{ $message }}</p> @enderror
                </div>

                {{-- Locality --}}
                <div>
                    <label class="block text-xs font-semibold text-stone-700 dark:text-stone-300 mb-1">
                        Locality / Town (Optional)
                    </label>
                    <input type="text" name="locality"
                           value="{{ old('locality') }}"
                           placeholder="e.g. Billiri Local Government"
                           class="w-full text-xs rounded-xl border border-stone-300 dark:border-stone-700 bg-white dark:bg-stone-800 p-3">
                </div>

                {{-- Primary Language --}}
                <div>
                    <label class="block text-xs font-semibold text-stone-700 dark:text-stone-300 mb-1">
                        Primary Language / Dialect (Optional)
                    </label>
                    <input type="text" name="language"
                           x-ref="languageInput"
                           value="{{ old('language') }}"
                           placeholder="e.g. Tangale (West Chadic)"
                           class="w-full text-xs rounded-xl border border-stone-300 dark:border-stone-700 bg-white dark:bg-stone-800 p-3">
                </div>

                {{-- Sensitivity / Access Restriction --}}
                <div>
                    <label class="block text-xs font-semibold text-stone-700 dark:text-stone-300 mb-1">
                        Knowledge Access Control *
                    </label>
                    <select name="sensitivity_level" required
                            class="w-full text-xs rounded-xl border border-stone-300 dark:border-stone-700 bg-white dark:bg-stone-800 p-3">
                        <option value="public">Public Archive (Open for educational and cultural discovery)</option>
                        <option value="community_only">Community Custodians Only</option>
                        <option value="restricted">Restricted Sacred Knowledge</option>
                    </select>
                </div>
            </div>

            {{-- Core Description with Oral Tradition Voice Assistant --}}
            <div>
                <div class="flex items-center justify-between mb-1.5 flex-wrap gap-2">
                    <label class="block text-xs font-semibold text-stone-700 dark:text-stone-300">
                        Core Cultural Overview & Synthesis *
                    </label>
                    <div class="flex items-center gap-2">
                        <button type="button" 
                                @click="toggleVoiceStudio()"
                                class="inline-flex items-center gap-1.5 text-xs font-bold text-amber-700 dark:text-amber-400 hover:text-amber-800 dark:hover:text-amber-300 px-3 py-1.5 rounded-xl bg-amber-500/10 border border-amber-500/20 transition active:scale-95 shadow-sm">
                            <i data-lucide="mic" class="w-3.5 h-3.5 text-amber-500" :class="isListening ? 'animate-pulse text-red-500' : ''"></i>
                            <span x-text="isListening ? 'Listening... (Click to stop)' : 'Speak to AI (Voice Dictation)'"></span>
                        </button>
                        <button type="button" 
                                @click="formatSpokenCultureRecord()"
                                :disabled="!voiceTranscript && !description || isFormattingVoice"
                                class="inline-flex items-center gap-1.5 text-xs font-bold text-white px-3 py-1.5 rounded-xl bg-gradient-to-r from-amber-500 to-orange-500 hover:from-amber-600 hover:to-orange-600 disabled:opacity-40 transition active:scale-95 shadow-sm">
                            <i data-lucide="sparkles" class="w-3.5 h-3.5"></i>
                            <span x-text="isFormattingVoice ? 'Formatting...' : 'AI Format for Post'"></span>
                        </button>
                    </div>
                </div>

                <!-- Voice Recording Active Visualizer Box -->
                <div x-show="showVoiceStudio" x-cloak class="mb-3.5 p-4 bg-gradient-to-r from-amber-500/15 via-orange-500/10 to-amber-500/5 dark:bg-stone-800/90 rounded-2xl border-2 border-amber-400/50 space-y-3 shadow-md">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <span class="relative flex h-3 w-3">
                                <span x-show="isListening" class="animate-ping absolute inline-flex h-full w-full rounded-full bg-red-400 opacity-75"></span>
                                <span class="relative inline-flex rounded-full h-3 w-3" :class="isListening ? 'bg-red-500' : 'bg-stone-400'"></span>
                            </span>
                            <span class="text-xs font-extrabold text-stone-900 dark:text-stone-100" x-text="isListening ? 'Listening... Speak your story or oral tradition freely' : 'Oral Dictation Ready'"></span>
                        </div>
                        <button type="button" @click="toggleVoiceStudio()" class="p-1 text-stone-400 hover:text-stone-600 dark:hover:text-stone-200 rounded-lg">
                            <i data-lucide="x" class="w-4 h-4"></i>
                        </button>
                    </div>

                    <!-- Live Equalizer Animation while recording -->
                    <div x-show="isListening" class="flex items-center gap-1.5 h-6 py-1">
                        <span class="w-1.5 bg-amber-500 rounded-full animate-bounce h-3"></span>
                        <span class="w-1.5 bg-amber-500 rounded-full animate-bounce h-6" style="animation-delay: 0.1s"></span>
                        <span class="w-1.5 bg-amber-500 rounded-full animate-bounce h-2" style="animation-delay: 0.2s"></span>
                        <span class="w-1.5 bg-amber-500 rounded-full animate-bounce h-5" style="animation-delay: 0.3s"></span>
                        <span class="w-1.5 bg-amber-500 rounded-full animate-bounce h-4" style="animation-delay: 0.15s"></span>
                        <span class="text-xs text-stone-600 dark:text-stone-300 ml-2 font-medium italic">Speak in your mother tongue, Nigerian Pidgin, or English. When finished, AI will arrange it for your post.</span>
                    </div>

                    <!-- Real-time transcript preview -->
                    <div class="text-xs text-stone-800 dark:text-stone-200 p-3 bg-white/90 dark:bg-stone-900/90 rounded-xl border border-amber-300/60 dark:border-amber-800 min-h-[56px] max-h-40 overflow-y-auto leading-relaxed">
                        <span x-show="!voiceTranscript" class="text-stone-400 italic">Spoken words will appear here in real time as you speak... Click "Start Speaking" below.</span>
                        <span x-text="voiceTranscript" class="font-medium"></span>
                    </div>

                    <!-- Actions -->
                    <div class="flex items-center justify-between flex-wrap gap-2 pt-1">
                        <div class="flex items-center gap-2">
                            <button type="button" 
                                    @click="isListening ? stopVoiceDictation() : startVoiceDictation()"
                                    class="px-3.5 py-2 rounded-xl font-bold text-xs flex items-center gap-1.5 shadow-sm transition active:scale-95"
                                    :class="isListening ? 'bg-red-500 hover:bg-red-600 text-white' : 'bg-amber-500 hover:bg-amber-600 text-white'">
                                <i data-lucide="mic" class="w-3.5 h-3.5"></i>
                                <span x-text="isListening ? 'Stop Dictation' : 'Start Speaking'"></span>
                            </button>
                            <button type="button" 
                                    x-show="voiceTranscript" 
                                    @click="voiceTranscript = ''; description = ''"
                                    class="px-2.5 py-1.5 rounded-xl text-stone-500 hover:text-stone-700 text-xs font-semibold">
                                Clear Spoken Text
                            </button>
                        </div>
                        <button type="button" 
                                @click="formatSpokenCultureRecord()"
                                :disabled="!voiceTranscript && !description || isFormattingVoice"
                                class="px-4 py-2 rounded-xl bg-gradient-to-r from-amber-500 to-orange-500 hover:from-amber-600 hover:to-orange-600 text-white font-black text-xs shadow-md flex items-center gap-1.5 disabled:opacity-50 transition active:scale-95">
                            <i data-lucide="sparkles" class="w-4 h-4"></i>
                            <span x-text="isFormattingVoice ? 'AI Structuring & Formatting...' : 'AI Arrange & Format for Post'"></span>
                        </button>
                    </div>
                </div>

                <textarea name="description" rows="5" required
                          x-model="description"
                          placeholder="Provide a thorough, respectful description of the cultural record, its historical origin, and community meaning..."
                          class="w-full text-xs rounded-xl border border-stone-300 dark:border-stone-700 bg-white dark:bg-stone-800 p-3"></textarea>
                @error('description') <p class="text-red-500 text-[10px] mt-1">{{ $message }}</p> @enderror
            </div>
        </div>

        {{-- 3. TYPE-ADAPTIVE SPECIALIZED SECTIONS --}}
        
        {{-- TYPE: Oral History --}}
        <div x-show="recordType === 'oral_history'" class="space-y-4 pt-6 border-t border-stone-200 dark:border-stone-800">
            <h3 class="text-xs font-bold uppercase tracking-wider text-amber-700 dark:text-amber-400 flex items-center gap-1.5">
                <i data-lucide="mic" class="w-4 h-4"></i>
                <span>3. Oral History & Testimony Context</span>
            </h3>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-stone-700 dark:text-stone-300 mb-1">
                        Speaker / Custodian Name
                    </label>
                    <input type="text" name="speaker_name" placeholder="Name of reciting elder or storyteller" class="w-full text-xs rounded-xl border border-stone-300 dark:border-stone-700 bg-white dark:bg-stone-800 p-3">
                </div>

                <div class="flex items-center gap-3 pt-6">
                    <input type="checkbox" name="speaker_is_anonymous" id="speaker_anon" value="1" class="rounded text-amber-500 focus:ring-amber-400">
                    <label for="speaker_anon" class="text-xs text-stone-700 dark:text-stone-300">
                        Protect Identity (Display as Anonymous Community Elder)
                    </label>
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-stone-700 dark:text-stone-300 mb-1">
                    Verbatim Spoken Narrative / Raw Story (Optional)
                </label>
                <textarea name="raw_story" x-ref="rawStoryInput" rows="4" placeholder="Verbatim quote or transcript in original words..." class="w-full text-xs rounded-xl border border-stone-300 dark:border-stone-700 bg-white dark:bg-stone-800 p-3"></textarea>
            </div>
        </div>

        {{-- TYPE: Performance & Song --}}
        <div x-show="recordType === 'performance'" class="space-y-4 pt-6 border-t border-stone-200 dark:border-stone-800">
            <h3 class="text-xs font-bold uppercase tracking-wider text-amber-700 dark:text-amber-400 flex items-center gap-1.5">
                <i data-lucide="music" class="w-4 h-4"></i>
                <span>3. Performance, Song & Dance Context</span>
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-stone-700 dark:text-stone-300 mb-1">Lead Performer / Ensemble</label>
                    <input type="text" name="current_practitioners" placeholder="e.g. Traditional Troupe or Drummer Guild" class="w-full text-xs rounded-xl border border-stone-300 dark:border-stone-700 bg-white dark:bg-stone-800 p-3">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-stone-700 dark:text-stone-300 mb-1">Instruments / Musical Style</label>
                    <input type="text" name="subtype" placeholder="e.g. Talking Drum, Kora, Ceremonial Flute" class="w-full text-xs rounded-xl border border-stone-300 dark:border-stone-700 bg-white dark:bg-stone-800 p-3">
                </div>
            </div>
            <div>
                <label class="block text-xs font-semibold text-stone-700 dark:text-stone-300 mb-1">Lyrics, Stanzas or Chant Phrases (Optional)</label>
                <textarea name="significance" rows="3" placeholder="Include phonetic lyrics and cultural translation if available..." class="w-full text-xs rounded-xl border border-stone-300 dark:border-stone-700 bg-white dark:bg-stone-800 p-3"></textarea>
            </div>
        </div>

        {{-- TYPE: Recipe & Food Tradition --}}
        <div x-show="recordType === 'recipe'" class="space-y-4 pt-6 border-t border-stone-200 dark:border-stone-800">
            <h3 class="text-xs font-bold uppercase tracking-wider text-amber-700 dark:text-amber-400 flex items-center gap-1.5">
                <i data-lucide="utensils" class="w-4 h-4"></i>
                <span>3. Culinary Tradition & Preparation</span>
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-stone-700 dark:text-stone-300 mb-1">Key Indigenous Ingredients</label>
                    <input type="text" name="significance" placeholder="e.g. Fermented locust bean (Dawadawa), Millet" class="w-full text-xs rounded-xl border border-stone-300 dark:border-stone-700 bg-white dark:bg-stone-800 p-3">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-stone-700 dark:text-stone-300 mb-1">Ceremonial Occasions for Preparation</label>
                    <input type="text" name="community_role" placeholder="e.g. Harvest feasts, Post-natal ceremonies" class="w-full text-xs rounded-xl border border-stone-300 dark:border-stone-700 bg-white dark:bg-stone-800 p-3">
                </div>
            </div>
        </div>

        {{-- TYPE: Craft & Artefact --}}
        <div x-show="recordType === 'craft'" class="space-y-4 pt-6 border-t border-stone-200 dark:border-stone-800">
            <h3 class="text-xs font-bold uppercase tracking-wider text-amber-700 dark:text-amber-400 flex items-center gap-1.5">
                <i data-lucide="hammer" class="w-4 h-4"></i>
                <span>3. Material Culture & Craftsmanship</span>
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-stone-700 dark:text-stone-300 mb-1">Materials Used</label>
                    <input type="text" name="transmission_methods" placeholder="e.g. Bronze casting, raffia palm, clay" class="w-full text-xs rounded-xl border border-stone-300 dark:border-stone-700 bg-white dark:bg-stone-800 p-3">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-stone-700 dark:text-stone-300 mb-1">Artisan Lineage or Maker Guild</label>
                    <input type="text" name="current_practitioners" placeholder="e.g. Master smith guild of Bida" class="w-full text-xs rounded-xl border border-stone-300 dark:border-stone-700 bg-white dark:bg-stone-800 p-3">
                </div>
            </div>
        </div>

        {{-- TYPE: Ceremony & Sacred --}}
        <div x-show="recordType === 'ceremony'" class="space-y-4 pt-6 border-t border-stone-200 dark:border-stone-800">
            <h3 class="text-xs font-bold uppercase tracking-wider text-amber-700 dark:text-amber-400 flex items-center gap-1.5">
                <i data-lucide="compass" class="w-4 h-4"></i>
                <span>3. Ceremony, Ritual & Sacred Protocol</span>
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-stone-700 dark:text-stone-300 mb-1">Ceremonial Calendar / Timing</label>
                    <input type="text" name="historical_period" placeholder="e.g. Lunar seventh month, First rains" class="w-full text-xs rounded-xl border border-stone-300 dark:border-stone-700 bg-white dark:bg-stone-800 p-3">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-stone-700 dark:text-stone-300 mb-1">Participant Roles</label>
                    <input type="text" name="community_role" placeholder="e.g. Initiates, Clan elders, Women's circle" class="w-full text-xs rounded-xl border border-stone-300 dark:border-stone-700 bg-white dark:bg-stone-800 p-3">
                </div>
            </div>
            <div>
                <label class="block text-xs font-semibold text-stone-700 dark:text-stone-300 mb-1">Customary Rites & Allowed Steps</label>
                <textarea name="rituals" rows="3" placeholder="Describe the community-authorized sequence of events..." class="w-full text-xs rounded-xl border border-stone-300 dark:border-stone-700 bg-white dark:bg-stone-800 p-3"></textarea>
            </div>
        </div>

        {{-- 4. MEDIA, SOURCES & LICENSING --}}
        <div class="space-y-4 pt-6 border-t border-stone-200 dark:border-stone-800">
            <h3 class="text-xs font-bold uppercase tracking-wider text-amber-700 dark:text-amber-400 flex items-center gap-1.5">
                <i data-lucide="upload" class="w-4 h-4"></i>
                <span>4. Media Uploads & Rights Verification</span>
            </h3>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                {{-- Cover Image --}}
                <div>
                    <label class="block text-xs font-semibold text-stone-700 dark:text-stone-300 mb-1">
                        Cover Photo / Artefact Image (Optional)
                    </label>
                    <input type="file" name="image" accept="image/*"
                           class="w-full text-xs text-stone-500 file:mr-3 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-amber-500/10 file:text-amber-800 hover:file:bg-amber-500/20">
                </div>

                {{-- Audio Recording --}}
                <div>
                    <label class="block text-xs font-semibold text-stone-700 dark:text-stone-300 mb-1">
                        Audio Recording (Optional)
                    </label>
                    <input type="file" name="audio_file" accept="audio/*"
                           class="w-full text-xs text-stone-500 file:mr-3 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-amber-500/10 file:text-amber-800 hover:file:bg-amber-500/20">
                </div>

                {{-- License Type --}}
                <div>
                    <label class="block text-xs font-semibold text-stone-700 dark:text-stone-300 mb-1">
                        Cultural Rights / License Type *
                    </label>
                    <select name="license_type" required
                            class="w-full text-xs rounded-xl border border-stone-300 dark:border-stone-700 bg-white dark:bg-stone-800 p-3">
                        <option value="Community Heritage Commons">Community Heritage Commons (Preserved for non-commercial education)</option>
                        <option value="CC BY-NC 4.0">Creative Commons Attribution-NonCommercial (CC BY-NC)</option>
                        <option value="Public Domain">Public Domain / Ancient Folklore</option>
                        <option value="All Rights Reserved">Custodian Rights Reserved</option>
                    </select>
                </div>

                {{-- Attribution Credit --}}
                <div>
                    <label class="block text-xs font-semibold text-stone-700 dark:text-stone-300 mb-1">
                        Attribution / Source Credit (Optional)
                    </label>
                    <input type="text" name="license_credit" placeholder="e.g. Recorded with permission of Tangale Elder Council" class="w-full text-xs rounded-xl border border-stone-300 dark:border-stone-700 bg-white dark:bg-stone-800 p-3">
                </div>
            </div>

            {{-- Consent and Authority Checkbox --}}
            <div class="p-4 bg-stone-50 dark:bg-stone-800/60 rounded-xl border border-stone-200 dark:border-stone-700 flex items-start gap-3">
                <input type="checkbox" id="consent_check" required class="mt-0.5 rounded text-amber-500 focus:ring-amber-400">
                <label for="consent_check" class="text-xs text-stone-600 dark:text-stone-300 leading-relaxed">
                    I confirm that I have obtained proper consent from the community, knowledge keeper, or family to record and preserve this cultural tradition, and that this account does not disclose prohibited sacred secrets.
                </label>
            </div>
        </div>

        {{-- 5. SUBMISSION ACTIONS --}}
        <div class="pt-6 border-t border-stone-200 dark:border-stone-800 flex flex-wrap items-center justify-between gap-3">
            <a href="{{ route('cultural-hub.index') }}" class="text-xs font-bold text-stone-500 hover:text-stone-700">
                Cancel
            </a>

            <div class="flex items-center gap-2">
                <button type="submit" 
                        @click="isDraft = true"
                        class="px-5 py-2.5 rounded-xl border border-stone-300 dark:border-stone-700 text-stone-700 dark:text-stone-300 hover:border-amber-500 text-xs font-bold transition">
                    Save as Draft
                </button>

                <button type="submit" 
                        @click="isDraft = false"
                        class="px-6 py-2.5 rounded-xl bg-amber-500 hover:bg-amber-600 text-white font-bold text-xs transition shadow-sm flex items-center gap-1.5">
                    <i data-lucide="archive" class="w-4 h-4"></i>
                    <span>Submit for Archival Review</span>
                </button>
            </div>
        </div>

    </form>
</div>
@endsection

@push('scripts')
<script>
    function culturalArchiveForm(config) {
        return {
            recordType: config.recordType,
            description: config.description,
            aiStructuring: config.aiStructuring,
            sourceText: config.sourceText,
            isProcessingAI: false,
            isDraft: false,
            showVoiceStudio: false,
            isListening: false,
            recognition: null,
            voiceTranscript: '',
            isFormattingVoice: false,

            toggleVoiceStudio() {
                this.showVoiceStudio = !this.showVoiceStudio;
                if (this.showVoiceStudio && !this.isListening) {
                    this.startVoiceDictation();
                } else if (!this.showVoiceStudio && this.isListening) {
                    this.stopVoiceDictation();
                }
                this.$nextTick(() => {
                    if (typeof lucide !== 'undefined') lucide.createIcons();
                });
            },

            startVoiceDictation() {
                const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
                if (!SpeechRecognition) {
                    alert('Speech Recognition is not supported directly in this browser. You can type or paste your spoken words directly into the box.');
                    return;
                }

                try {
                    if (this.recognition) {
                        try { this.recognition.stop(); } catch(e) {}
                    }

                    this.recognition = new SpeechRecognition();
                    this.recognition.continuous = true;
                    this.recognition.interimResults = true;
                    this.recognition.lang = this.$refs.languageInput?.value ? this.$refs.languageInput.value.toLowerCase() : 'en-US';

                    this.recognition.onstart = () => {
                        this.isListening = true;
                    };

                    this.recognition.onresult = (event) => {
                        let finalTranscript = '';
                        let interim = '';
                        for (let i = 0; i < event.results.length; ++i) {
                            if (event.results[i].isFinal) {
                                finalTranscript += event.results[i][0].transcript + ' ';
                            } else {
                                interim += event.results[i][0].transcript;
                            }
                        }
                        this.voiceTranscript = (finalTranscript + interim).trim();
                        if (this.voiceTranscript && !this.description) {
                            this.description = this.voiceTranscript;
                        }
                    };

                    this.recognition.onerror = (event) => {
                        console.warn('Speech recognition notice:', event.error);
                        if (event.error === 'not-allowed') {
                            alert('Microphone access was denied. Please allow microphone permissions in your browser.');
                            this.isListening = false;
                        }
                    };

                    this.recognition.onend = () => {
                        this.isListening = false;
                    };

                    this.recognition.start();
                    this.isListening = true;
                } catch(e) {
                    console.error('Speech recognition start error:', e);
                    this.isListening = false;
                }
            },

            stopVoiceDictation() {
                if (this.recognition) {
                    try { this.recognition.stop(); } catch(e) {}
                }
                this.isListening = false;
            },

            async formatSpokenCultureRecord() {
                const textToFormat = (this.voiceTranscript || this.description || this.sourceText).trim();
                if (!textToFormat || this.isFormattingVoice) return;
                this.isFormattingVoice = true;

                try {
                    const res = await fetch('/cultural-hub/ai-voice-format', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({
                            raw_transcript: textToFormat,
                            type: 'culture_record',
                            name: this.$refs.nameInput ? this.$refs.nameInput.value : '',
                            cultural_group: this.$refs.groupInput ? this.$refs.groupInput.value : '',
                            region: this.$refs.regionInput ? this.$refs.regionInput.value : '',
                            language: this.$refs.languageInput ? this.$refs.languageInput.value : '',
                            category: this.recordType
                        })
                    });

                    const result = await res.json();
                    if (result.success && result.data) {
                        const d = result.data;
                        if (d.formatted_description) {
                            this.description = d.formatted_description;
                        }
                        if (d.proposed_title && this.$refs.nameInput && (!this.$refs.nameInput.value || this.$refs.nameInput.value.length < 5)) {
                            this.$refs.nameInput.value = d.proposed_title;
                        }
                        if (d.cultural_group && d.cultural_group !== 'unknown' && this.$refs.groupInput && !this.$refs.groupInput.value) {
                            this.$refs.groupInput.value = d.cultural_group;
                        }
                        if (d.region && d.region !== 'unknown' && this.$refs.regionInput && !this.$refs.regionInput.value) {
                            this.$refs.regionInput.value = d.region;
                        }
                        if (d.language && d.language !== 'unknown' && this.$refs.languageInput && !this.$refs.languageInput.value) {
                            this.$refs.languageInput.value = d.language;
                        }
                        if (this.$refs.rawStoryInput && d.verbatim_transcript) {
                            this.$refs.rawStoryInput.value = d.verbatim_transcript;
                        }
                        alert('Oral story formatted into a structured cultural record! Please review before saving.');
                    }
                } catch(e) {
                    console.error('AI voice formatting error:', e);
                    alert('Unable to format voice record automatically. Original spoken words preserved in description.');
                } finally {
                    this.isFormattingVoice = false;
                    this.stopVoiceDictation();
                }
            },

            async runStructuring() {
                if (!this.sourceText.trim() || this.isProcessingAI) return;
                this.isProcessingAI = true;

                try {
                    const res = await fetch('/cultural-hub/structure-narrative', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({
                            source_text: this.sourceText,
                            category: this.recordType
                        })
                    });
                    const result = await res.json();
                    if (result.success && result.data) {
                        const d = result.data;
                        if (d.proposed_title && this.$refs.nameInput) this.$refs.nameInput.value = d.proposed_title;
                        if (d.introduction) this.description = d.introduction;
                        if (d.cultural_group && d.cultural_group !== 'unknown' && this.$refs.groupInput) {
                            this.$refs.groupInput.value = d.cultural_group;
                        }
                        if (d.language && d.language !== 'unknown' && this.$refs.languageInput) {
                            this.$refs.languageInput.value = d.language;
                        }
                        if (d.region && d.region !== 'unknown' && this.$refs.regionInput) {
                            this.$refs.regionInput.value = d.region;
                        }
                        alert('Archival fields have been suggested from your source narrative. Please review them before saving.');
                    }
                } catch(e) {
                    console.error('Structuring failed:', e);
                    alert('Unable to analyze narrative automatically. You can proceed to fill in the fields directly.');
                } finally {
                    this.isProcessingAI = false;
                }
            }
        };
    }
</script>
@endpush