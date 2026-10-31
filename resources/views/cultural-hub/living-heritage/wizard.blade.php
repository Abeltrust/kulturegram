@extends('layouts.app')

@section('title', 'Living Heritage Capture Wizard - Kulturegram')

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 py-8"
     x-data="livingHeritageWizard({
        isConfigured: {{ $isConfigured ? 'true' : 'false' }},
        maskedKey: '{{ $maskedKey }}',
        csrfToken: '{{ csrf_token() }}'
     })">

    <!-- Top Navigation & Stage Progress -->
    <div class="mb-8 space-y-4">
        <div class="flex items-center justify-between">
            <a href="{{ route('cultural-hub.living-heritage.index') }}"
               class="inline-flex items-center text-xs font-semibold text-stone-500 hover:text-stone-800 dark:hover:text-stone-200">
                <i data-lucide="arrow-left" class="w-4 h-4 mr-1"></i>
                <span>Living Heritage Hub</span>
            </a>
            <div class="flex items-center space-x-2">
                <span class="text-xs font-bold text-stone-500">STAGE <span x-text="step"></span> OF 5</span>
                <span class="w-2 h-2 rounded-full" :class="isConfigured ? 'bg-green-500' : 'bg-amber-500'"></span>
            </div>
        </div>

        <div>
            <div class="inline-flex items-center space-x-1.5 px-2.5 py-1 rounded-full bg-amber-50 dark:bg-amber-950/60 border border-amber-200 dark:border-amber-800 text-[10px] font-bold text-amber-700 dark:text-amber-400 uppercase tracking-widest mb-2">
                <i data-lucide="sparkles" class="w-3 h-3"></i>
                <span>Cultural Preservation Intelligence</span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-stone-900 dark:text-stone-100 tracking-tight">
                Living Heritage Capture
            </h1>
            <p class="text-xs sm:text-sm text-stone-500 dark:text-stone-400">
                Preserve oral traditions, languages, rituals, and artifacts with AI assistance.
            </p>
        </div>

        <!-- Multi-step Indicator Bar -->
        <div class="grid grid-cols-5 gap-2 pt-2">
            <template x-for="(label, idx) in ['Capture', 'Transcribe', 'Prepare Record', 'Enrich & Protect', 'Review & Save']">
                <div class="space-y-1">
                    <div class="h-1.5 rounded-full transition-all duration-300"
                         :class="step > (idx + 1) ? 'bg-amber-600' : (step === (idx + 1) ? 'bg-amber-500' : 'bg-stone-200 dark:bg-stone-800')"></div>
                    <span class="block text-[10px] font-bold truncate"
                          :class="step === (idx + 1) ? 'text-amber-600 dark:text-amber-400' : 'text-stone-400'"
                          x-text="(idx + 1) + '. ' + label"></span>
                </div>
            </template>
        </div>
    </div>

    <!-- Notice if API key is pending -->
    <div x-show="!isConfigured" class="mb-6 p-4 rounded-2xl bg-amber-50 dark:bg-amber-950/30 border border-amber-200 dark:border-amber-800 text-xs text-amber-800 dark:text-amber-300 flex items-start space-x-3">
        <i data-lucide="info" class="w-5 h-5 flex-shrink-0 mt-0.5 text-amber-600"></i>
        <div>
            <span class="font-bold">Meta Model API Key Pending / Offline Archival Mode:</span>
            <span>You can upload oral audio, manually enter and correct transcripts, fill all structured fields, and save or submit drafts. AI buttons will display clean diagnostic notices if triggered without active credentials.</span>
        </div>
    </div>

    <!-- Error Banner -->
    <div x-show="errorMessage" x-cloak class="mb-6 p-4 rounded-2xl bg-red-50 dark:bg-red-950/40 border border-red-200 dark:border-red-800 text-xs text-red-700 dark:text-red-300 flex items-start justify-between">
        <div class="flex items-center space-x-2">
            <i data-lucide="alert-circle" class="w-4 h-4 flex-shrink-0 text-red-500"></i>
            <span x-text="errorMessage"></span>
        </div>
        <button type="button" @click="errorMessage = ''" class="text-red-500 hover:text-red-700 font-bold">&times;</button>
    </div>

    <!-- MAIN FORM CONTAINER -->
    <div class="bg-white dark:bg-stone-900 border border-stone-200 dark:border-stone-800 rounded-3xl p-6 sm:p-10 shadow-sm">

        <!-- ================================================================= -->
        <!-- STAGE 1: CAPTURE STORY                                            -->
        <!-- ================================================================= -->
        <div x-show="step === 1" class="space-y-6">
            <div class="border-b border-stone-100 dark:border-stone-800 pb-4">
                <h2 class="text-lg font-bold text-stone-900 dark:text-stone-100 flex items-center space-x-2">
                    <i data-lucide="mic" class="w-5 h-5 text-amber-600"></i>
                    <span>Stage 1: Capture Oral Story or Folktale</span>
                </h2>
                <p class="text-xs text-stone-500">
                    Upload an oral recording of an elder, traditional custodian, or your own retelling, or type the story narrative directly.
                </p>
            </div>

            <!-- Input Mode Selector -->
            <div class="flex items-center space-x-2 p-1 bg-stone-100 dark:bg-stone-800 rounded-xl w-fit">
                <button type="button"
                        @click="captureMode = 'audio'"
                        :class="captureMode === 'audio' ? 'bg-white dark:bg-stone-700 text-amber-600 font-bold shadow-sm' : 'text-stone-600 dark:text-stone-400'"
                        class="px-4 py-2 rounded-lg text-xs transition-all flex items-center space-x-1.5">
                    <i data-lucide="music-2" class="w-3.5 h-3.5"></i>
                    <span>Audio Recording Upload</span>
                </button>
                <button type="button"
                        @click="captureMode = 'text'"
                        :class="captureMode === 'text' ? 'bg-white dark:bg-stone-700 text-amber-600 font-bold shadow-sm' : 'text-stone-600 dark:text-stone-400'"
                        class="px-4 py-2 rounded-lg text-xs transition-all flex items-center space-x-1.5">
                    <i data-lucide="file-text" class="w-3.5 h-3.5"></i>
                    <span>Written Oral Story</span>
                </button>
            </div>

            <!-- Audio Upload Panel -->
            <div x-show="captureMode === 'audio'" class="space-y-4">
                <label class="block text-xs font-bold uppercase tracking-wider text-stone-600 dark:text-stone-400">
                    Oral Audio Recording File (MP3, WAV, M4A, OGG, AAC - Max 25MB)
                </label>
                <div class="border-2 border-dashed border-stone-300 dark:border-stone-700 rounded-2xl p-6 text-center hover:border-amber-500 transition-colors bg-stone-50/50 dark:bg-stone-800/30">
                    <input type="file"
                           id="audioFileInput"
                           accept="audio/*,.mp3,.wav,.m4a,.aac,.ogg,.webm"
                           @change="handleAudioSelect($event)"
                           class="hidden">
                    <label for="audioFileInput" class="cursor-pointer space-y-2 block">
                        <i data-lucide="upload-cloud" class="w-10 h-10 mx-auto text-amber-600"></i>
                        <p class="text-xs font-bold text-stone-800 dark:text-stone-200">
                            <span x-text="audioFileName ? audioFileName : 'Click to select or drop an oral history recording'"></span>
                        </p>
                        <p class="text-[11px] text-stone-400">
                            Audio files are preserved authentically on Kulturegram and never altered.
                        </p>
                    </label>
                </div>
            </div>

            <!-- Written Narrative Panel -->
            <div x-show="captureMode === 'text' || transcript" class="space-y-2">
                <div class="flex items-center justify-between">
                    <label class="block text-xs font-bold uppercase tracking-wider text-stone-600 dark:text-stone-400">
                        Story Narrative / Spoken Folktale
                    </label>
                    <span class="text-[11px] font-mono" :class="rawStoryWordCount() < 20 ? 'text-amber-500' : 'text-green-600'">
                        <span x-text="rawStoryWordCount()"></span> / 20 words min
                    </span>
                </div>
                <textarea x-model="rawStory"
                          rows="6"
                          placeholder="Type or paste the oral account, story of origin, sacred festival narrative, or folk wisdom..."
                          class="w-full stnd-input p-4 text-sm"></textarea>
            </div>

            <!-- Regional & Language Context Hints -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-stone-600 dark:text-stone-400 mb-1">
                        Community / Origin Region Hint
                    </label>
                    <input type="text"
                           x-model="regionHint"
                           placeholder="e.g. Idanre Hills, Ondo, Nigeria or Maasai Mara"
                           class="w-full stnd-input px-3.5 py-2.5 text-xs">
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-stone-600 dark:text-stone-400 mb-1">
                        Language / Dialect Spoken
                    </label>
                    <input type="text"
                           x-model="languageHint"
                           placeholder="e.g. Yoruba, Hausa, Igbo, Swahili, Gaelic"
                           class="w-full stnd-input px-3.5 py-2.5 text-xs">
                </div>
            </div>

            <!-- Cultural Consent Disclaimer & Agreement -->
            <div class="p-4 rounded-2xl bg-stone-50 dark:bg-stone-800/60 border border-stone-200 dark:border-stone-700/80 space-y-3">
                <div class="flex items-center space-x-2 text-stone-800 dark:text-stone-200 text-xs font-bold">
                    <i data-lucide="shield-check" class="w-4 h-4 text-amber-600"></i>
                    <span>Cultural Consent & Safeguarding Agreement</span>
                </div>
                <p class="text-[11px] text-stone-500 dark:text-stone-400 leading-relaxed">
                    By submitting oral histories or recordings, you confirm that you have permission from the community or elder to document this knowledge. AI is used solely as an assistive transcription and organization tool. Kulturegram maintains strict community ownership standards.
                </p>
                <label class="flex items-start space-x-2.5 cursor-pointer pt-1">
                    <input type="checkbox"
                           x-model="consentGranted"
                           class="rounded border-stone-300 text-amber-600 focus:ring-amber-500 mt-0.5">
                    <span class="text-xs font-medium text-stone-700 dark:text-stone-300">
                        I confirm cultural consent and agree to process this material with AI assistance.
                    </span>
                </label>
            </div>

            <!-- Stage 1 Actions -->
            <div class="flex items-center justify-between pt-4 border-t border-stone-100 dark:border-stone-800">
                <span class="text-xs text-stone-400">Step 1 of 5</span>
                <button type="button"
                        @click="proceedFromStage1()"
                        :disabled="!consentGranted || (captureMode === 'audio' && !audioFile && !rawStory.trim()) || (captureMode === 'text' && !rawStory.trim())"
                        class="px-6 py-3 bg-amber-500 hover:bg-amber-600 disabled:opacity-40 disabled:cursor-not-allowed text-white font-bold rounded-xl text-xs uppercase tracking-wider transition-colors">
                    Continue to Transcription &rarr;
                </button>
            </div>
        </div>

        <!-- ================================================================= -->
        <!-- STAGE 2: TRANSCRIBE & REVIEW (MUSE VOICE TRANSCRIBE)               -->
        <!-- ================================================================= -->
        <div x-show="step === 2" class="space-y-6">
            <div class="border-b border-stone-100 dark:border-stone-800 pb-4">
                <h2 class="text-lg font-bold text-stone-900 dark:text-stone-100 flex items-center space-x-2">
                    <i data-lucide="headphones" class="w-5 h-5 text-amber-600"></i>
                    <span>Stage 2: Muse Voice Transcribe & Review</span>
                </h2>
                <p class="text-xs text-stone-500">
                    Transcribe audio with Meta Model API. Review names, dialects, and sacred expressions before finalizing.
                </p>
            </div>

            <!-- Trigger Transcription Button if Audio Uploaded -->
            <div x-show="audioFile" class="p-4 rounded-2xl bg-amber-50/50 dark:bg-amber-950/20 border border-amber-200/80 dark:border-amber-800/40 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div class="space-y-1">
                    <div class="flex items-center space-x-2 text-xs font-bold text-amber-800 dark:text-amber-300">
                        <i data-lucide="file-audio" class="w-4 h-4 text-amber-600"></i>
                        <span x-text="audioFileName || 'Audio recording ready'"></span>
                    </div>
                    <p class="text-[11px] text-stone-500">
                        Muse Voice Transcribe converts speech while preserving authentic dialect cadence.
                    </p>
                </div>
                <button type="button"
                        @click="runTranscription()"
                        :disabled="isTranscribing"
                        class="px-5 py-2.5 bg-amber-500 hover:bg-amber-600 disabled:opacity-50 text-white font-bold rounded-xl text-xs uppercase tracking-wider transition-colors inline-flex items-center space-x-2 flex-shrink-0">
                    <template x-if="isTranscribing">
                        <span class="inline-flex items-center space-x-2">
                            <i data-lucide="loader-2" class="w-3.5 h-3.5 animate-spin"></i>
                            <span>Transcribing...</span>
                        </span>
                    </template>
                    <template x-if="!isTranscribing">
                        <span class="inline-flex items-center space-x-2">
                            <i data-lucide="play" class="w-3.5 h-3.5"></i>
                            <span>Transcribe Audio with AI</span>
                        </span>
                    </template>
                </button>
            </div>

            <!-- Audio Player Preview if local url exists -->
            <div x-show="audioPreviewUrl" class="space-y-1">
                <label class="block text-[11px] font-bold uppercase tracking-wider text-stone-500">Original Audio Playback</label>
                <audio :src="audioPreviewUrl" controls class="w-full h-10 rounded-xl bg-stone-100 dark:bg-stone-800"></audio>
            </div>

            <!-- Editable Transcript Textarea -->
            <div class="space-y-2">
                <div class="flex items-center justify-between">
                    <label class="block text-xs font-bold uppercase tracking-wider text-stone-600 dark:text-stone-400">
                        Transcript (Editable by Contributor)
                    </label>
                    <span class="text-[11px] text-amber-600 font-semibold">
                        Correct dialect spelling, names, or missing words
                    </span>
                </div>
                <textarea x-model="transcript"
                          rows="8"
                          placeholder="The transcription will appear here. You can also type or paste text directly..."
                          class="w-full stnd-input p-4 text-sm font-sans"></textarea>
            </div>

            <div x-show="detectedLanguage" class="text-xs text-stone-500">
                <span>Detected Language / Dialect:</span>
                <strong class="text-stone-800 dark:text-stone-200" x-text="detectedLanguage"></strong>
            </div>

            <!-- Stage 2 Navigation -->
            <div class="flex items-center justify-between pt-4 border-t border-stone-100 dark:border-stone-800">
                <button type="button"
                        @click="step = 1"
                        class="px-5 py-2.5 text-stone-600 dark:text-stone-400 hover:text-stone-900 font-bold text-xs uppercase tracking-wider">
                    &larr; Back
                </button>
                <button type="button"
                        @click="proceedFromStage2()"
                        :disabled="!transcript.trim()"
                        class="px-6 py-3 bg-amber-500 hover:bg-amber-600 disabled:opacity-40 disabled:cursor-not-allowed text-white font-bold rounded-xl text-xs uppercase tracking-wider transition-colors">
                    Continue to Record Preparation &rarr;
                </button>
            </div>
        </div>

        <!-- ================================================================= -->
        <!-- STAGE 3: PREPARE WITH AI (MUSE SPARK)                             -->
        <!-- ================================================================= -->
        <div x-show="step === 3" class="space-y-6">
            <div class="border-b border-stone-100 dark:border-stone-800 pb-4">
                <h2 class="text-lg font-bold text-stone-900 dark:text-stone-100 flex items-center space-x-2">
                    <i data-lucide="sparkles" class="w-5 h-5 text-amber-600"></i>
                    <span>Stage 3: Muse Spark Cultural Structuring</span>
                </h2>
                <p class="text-xs text-stone-500">
                    Muse Spark extracts structured archival fields without inventing dates or rituals. Every field remains fully editable.
                </p>
            </div>

            <!-- Prepare Button Trigger -->
            <div class="p-4 rounded-2xl bg-stone-50 dark:bg-stone-800/40 border border-stone-200 dark:border-stone-700/80 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div class="space-y-1">
                    <h3 class="text-xs font-bold text-stone-900 dark:text-stone-100">AI Cultural Record Synthesis</h3>
                    <p class="text-[11px] text-stone-500">
                        Source narrative: <span class="font-bold text-stone-700 dark:text-stone-300" x-text="transcriptWordCount() + ' words'"></span> ready for processing.
                    </p>
                </div>
                <button type="button"
                        @click="runSparkPrepare()"
                        :disabled="isSparkLoading"
                        class="px-5 py-2.5 bg-amber-500 hover:bg-amber-600 disabled:opacity-50 text-white font-bold rounded-xl text-xs uppercase tracking-wider transition-colors inline-flex items-center space-x-2 flex-shrink-0">
                    <template x-if="isSparkLoading">
                        <span class="inline-flex items-center space-x-2">
                            <i data-lucide="loader-2" class="w-3.5 h-3.5 animate-spin"></i>
                            <span>Structuring Story...</span>
                        </span>
                    </template>
                    <template x-if="!isSparkLoading">
                        <span class="inline-flex items-center space-x-2">
                            <i data-lucide="wand-2" class="w-3.5 h-3.5"></i>
                            <span>Prepare Story with AI</span>
                        </span>
                    </template>
                </button>
            </div>

            <!-- Missing Information Warnings & Suggested Questions -->
            <div x-show="sparkRecord && (sparkRecord.missing_information_warnings?.length || sparkRecord.suggested_follow_up_questions?.length)"
                 class="p-4 rounded-2xl bg-amber-50/60 dark:bg-amber-950/20 border border-amber-200 dark:border-amber-900/40 space-y-3">
                <div class="flex items-center space-x-2 text-xs font-bold text-amber-800 dark:text-amber-300">
                    <i data-lucide="alert-triangle" class="w-4 h-4 text-amber-600"></i>
                    <span>Archival Context Audit</span>
                </div>
                <template x-if="sparkRecord.missing_information_warnings?.length">
                    <div class="space-y-1">
                        <span class="text-[11px] font-bold text-stone-600 dark:text-stone-400">Context Gaps:</span>
                        <ul class="list-disc list-inside text-xs text-stone-600 dark:text-stone-300 space-y-0.5">
                            <template x-for="gap in sparkRecord.missing_information_warnings">
                                <li x-text="gap"></li>
                            </template>
                        </ul>
                    </div>
                </template>
                <template x-if="sparkRecord.suggested_follow_up_questions?.length">
                    <div class="space-y-1 pt-2 border-t border-amber-200/50 dark:border-amber-900/30">
                        <span class="text-[11px] font-bold text-stone-600 dark:text-stone-400">Follow-up Questions for Custodians:</span>
                        <ul class="list-disc list-inside text-xs text-stone-600 dark:text-stone-300 space-y-0.5">
                            <template x-for="q in sparkRecord.suggested_follow_up_questions">
                                <li x-text="q"></li>
                            </template>
                        </ul>
                    </div>
                </template>
            </div>

            <!-- Structured Editable Fields -->
            <div class="space-y-4">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-stone-600 dark:text-stone-400 mb-1">
                            Heritage Title / Name *
                        </label>
                        <input type="text" x-model="cultureName" required class="w-full stnd-input px-3.5 py-2.5 text-xs">
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-stone-600 dark:text-stone-400 mb-1">
                            Region / Community *
                        </label>
                        <input type="text" x-model="cultureRegion" required class="w-full stnd-input px-3.5 py-2.5 text-xs">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-stone-600 dark:text-stone-400 mb-1">
                            Category *
                        </label>
                        <x-custom-select 
                            x-model="cultureCategory" 
                            :options="$categories" 
                            icon="tag" 
                            placeholder="Select category..." 
                            :searchable="false" />
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-stone-600 dark:text-stone-400 mb-1">
                            Language / Dialect
                        </label>
                        <input type="text" x-model="cultureLanguage" class="w-full stnd-input px-3.5 py-2.5 text-xs">
                    </div>
                </div>

                <!-- Description / Core Narrative -->
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-stone-600 dark:text-stone-400 mb-1">
                        Archival Description / Core Story *
                    </label>
                    <textarea x-model="cultureDescription" rows="5" class="w-full stnd-input p-3.5 text-xs leading-relaxed"></textarea>
                </div>

                <!-- Significance & Rituals -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-stone-600 dark:text-stone-400 mb-1">
                            Cultural Significance
                        </label>
                        <textarea x-model="cultureSignificance" rows="3" class="w-full stnd-input p-3 text-xs"></textarea>
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-stone-600 dark:text-stone-400 mb-1">
                            Rituals & Ceremonial Steps
                        </label>
                        <textarea x-model="cultureRituals" rows="3" class="w-full stnd-input p-3 text-xs"></textarea>
                    </div>
                </div>

                <!-- Transmission & Challenges -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-stone-600 dark:text-stone-400 mb-1">
                            Transmission Methods (How it is taught)
                        </label>
                        <textarea x-model="cultureTransmission" rows="3" class="w-full stnd-input p-3 text-xs"></textarea>
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-stone-600 dark:text-stone-400 mb-1">
                            Preservation Challenges (Threats)
                        </label>
                        <textarea x-model="cultureChallenges" rows="3" class="w-full stnd-input p-3 text-xs"></textarea>
                    </div>
                </div>

                <!-- Tags -->
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-stone-600 dark:text-stone-400 mb-1">
                        Cultural Archive Tags (Comma separated)
                    </label>
                    <input type="text" x-model="cultureTags" placeholder="e.g. folktale, festival, oral-history, yoruba, crown" class="w-full stnd-input px-3.5 py-2.5 text-xs">
                </div>
            </div>

            <!-- Stage 3 Navigation -->
            <div class="flex items-center justify-between pt-4 border-t border-stone-100 dark:border-stone-800">
                <button type="button"
                        @click="step = 2"
                        class="px-5 py-2.5 text-stone-600 dark:text-stone-400 hover:text-stone-900 font-bold text-xs uppercase tracking-wider">
                    &larr; Back
                </button>
                <button type="button"
                        @click="proceedFromStage3()"
                        :disabled="!cultureName.trim() || !cultureDescription.trim()"
                        class="px-6 py-3 bg-amber-500 hover:bg-amber-600 disabled:opacity-40 disabled:cursor-not-allowed text-white font-bold rounded-xl text-xs uppercase tracking-wider transition-colors">
                    Continue to Enrichment & Protection &rarr;
                </button>
            </div>
        </div>

        <!-- ================================================================= -->
        <!-- STAGE 4: ENRICH & PROTECT (WONDER, IMAGE, SAM 3.1)                 -->
        <!-- ================================================================= -->
        <div x-show="step === 4" class="space-y-8">
            <div class="border-b border-stone-100 dark:border-stone-800 pb-4">
                <h2 class="text-lg font-bold text-stone-900 dark:text-stone-100 flex items-center space-x-2">
                    <i data-lucide="shield" class="w-5 h-5 text-amber-600"></i>
                    <span>Stage 4: Multi-Model Enrichment & Safeguarding</span>
                </h2>
                <p class="text-xs text-stone-500">
                    Enhance the record with Muse Wonder preservation tasks, Muse Image illustrations, or SAM 3.1 artifact segmentation.
                </p>
            </div>

            <!-- SUB-FEATURE A: MUSE WONDER PRESERVATION ASSISTANT -->
            <div class="p-5 rounded-2xl bg-stone-50 dark:bg-stone-800/40 border border-stone-200 dark:border-stone-700/80 space-y-4">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                    <div>
                        <h3 class="text-sm font-bold text-stone-900 dark:text-stone-100 flex items-center space-x-2">
                            <i data-lucide="compass" class="w-4 h-4 text-amber-600"></i>
                            <span>Muse Wonder Bounded Preservation Assistant</span>
                        </h3>
                        <p class="text-xs text-stone-500">
                            Generates an archival preservation summary, community verification checklist, and elder questions.
                        </p>
                    </div>
                    <button type="button"
                            @click="runWonderPlan()"
                            :disabled="isWonderLoading"
                            class="px-4 py-2 bg-stone-900 dark:bg-stone-100 text-white dark:text-stone-900 font-bold rounded-xl text-xs hover:bg-stone-800 transition-colors inline-flex items-center space-x-1.5 flex-shrink-0">
                        <template x-if="isWonderLoading">
                            <span>Analyzing...</span>
                        </template>
                        <template x-if="!isWonderLoading">
                            <span>Run Preservation Audit</span>
                        </template>
                    </button>
                </div>

                <!-- Wonder Output Panel -->
                <div x-show="wonderPlan" class="space-y-3 pt-3 border-t border-stone-200 dark:border-stone-700">
                    <div x-show="wonderPlan?.preservation_summary" class="text-xs text-stone-700 dark:text-stone-300 italic p-3 bg-white dark:bg-stone-900 rounded-xl border border-stone-200 dark:border-stone-800">
                        "<span x-text="wonderPlan?.preservation_summary"></span>"
                    </div>

                    <div x-show="wonderPlan?.preservation_checklist?.length" class="space-y-1">
                        <span class="text-[11px] font-bold uppercase tracking-wider text-stone-500">Actionable Preservation Tasks:</span>
                        <div class="space-y-1.5">
                            <template x-for="(item, i) in wonderPlan?.preservation_checklist || []">
                                <label class="flex items-center space-x-2 p-2 bg-white dark:bg-stone-900 rounded-lg border border-stone-100 dark:border-stone-800 text-xs">
                                    <input type="checkbox" x-model="item.completed" class="rounded text-amber-600 focus:ring-amber-500">
                                    <span :class="item.completed ? 'line-through text-stone-400' : 'text-stone-700 dark:text-stone-300'" x-text="item.task"></span>
                                    <span class="ml-auto text-[9px] uppercase px-1.5 py-0.5 rounded font-bold"
                                          :class="item.priority === 'high' ? 'bg-red-100 text-red-700' : 'bg-stone-100 text-stone-600'"
                                          x-text="item.priority"></span>
                                </label>
                            </template>
                        </div>
                    </div>
                </div>
            </div>

            <!-- SUB-FEATURE: MUSE WONDER FACT-CHECK & PLAGIARISM AUDIT -->
            <div class="p-5 rounded-2xl bg-stone-50 dark:bg-stone-800/40 border border-stone-200 dark:border-stone-700/80 space-y-4">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                    <div>
                        <h3 class="text-sm font-bold text-stone-900 dark:text-stone-100 flex items-center space-x-2">
                            <i data-lucide="shield-check" class="w-4 h-4 text-amber-600"></i>
                            <span>Muse Wonder Fact-Check & Originality Audit</span>
                        </h3>
                        <p class="text-xs text-stone-500">
                            Evaluates oral tradition authenticity, generic web copy/plagiarism risk, and flags historical claims for elder corroboration.
                        </p>
                    </div>
                    <button type="button"
                            @click="runOriginalityAudit()"
                            :disabled="isAuditLoading"
                            class="px-4 py-2 bg-stone-900 dark:bg-stone-100 text-white dark:text-stone-900 font-bold rounded-xl text-xs hover:bg-stone-800 transition-colors inline-flex items-center space-x-1.5 flex-shrink-0">
                        <template x-if="isAuditLoading">
                            <span>Auditing Originality...</span>
                        </template>
                        <template x-if="!isAuditLoading">
                            <span>Run Fact-Check & Plagiarism Screen</span>
                        </template>
                    </button>
                </div>

                <!-- Audit Output Panel -->
                <div x-show="originalityAudit" class="space-y-4 pt-3 border-t border-stone-200 dark:border-stone-700">
                    <!-- Metrics Row -->
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                        <div class="p-3 bg-white dark:bg-stone-900 rounded-xl border border-stone-200 dark:border-stone-800 space-y-1">
                            <span class="text-[10px] font-bold uppercase text-stone-500">Oral Authenticity Score</span>
                            <div class="flex items-baseline space-x-2">
                                <span class="text-2xl font-black text-amber-600" x-text="(originalityAudit?.oral_authenticity_score ?? 0) + '/100'"></span>
                            </div>
                            <span class="inline-block text-[10px] font-semibold text-stone-600 dark:text-stone-400" x-text="originalityAudit?.authenticity_label"></span>
                        </div>

                        <div class="p-3 bg-white dark:bg-stone-900 rounded-xl border border-stone-200 dark:border-stone-800 space-y-1">
                            <span class="text-[10px] font-bold uppercase text-stone-500">Plagiarism / Copy Risk</span>
                            <div class="flex items-baseline space-x-2">
                                <span class="text-lg font-black uppercase"
                                      :class="{
                                          'text-green-600 dark:text-green-400': originalityAudit?.plagiarism_risk === 'low',
                                          'text-amber-600 dark:text-amber-400': originalityAudit?.plagiarism_risk === 'medium',
                                          'text-red-600 dark:text-red-400': originalityAudit?.plagiarism_risk === 'high'
                                      }"
                                      x-text="(originalityAudit?.plagiarism_risk || 'low') + ' RISK'"></span>
                            </div>
                            <span class="inline-block text-[10px] text-stone-500"
                                  x-text="originalityAudit?.plagiarism_risk === 'low' ? 'Original oral cadence detected' : 'Requires closer review'"></span>
                        </div>

                        <div class="p-3 bg-white dark:bg-stone-900 rounded-xl border border-stone-200 dark:border-stone-800 space-y-1">
                            <span class="text-[10px] font-bold uppercase text-stone-500">Source Fidelity</span>
                            <div class="flex items-center space-x-1.5 pt-1">
                                <i data-lucide="check-circle-2" class="w-4 h-4 text-green-500"></i>
                                <span class="text-xs font-bold text-stone-800 dark:text-stone-200"
                                      x-text="originalityAudit?.fidelity_check?.is_faithful_to_source ? 'Faithful to Source' : 'Discrepancies Noted'"></span>
                            </div>
                            <span class="inline-block text-[10px] text-stone-500">No AI-invented claims</span>
                        </div>
                    </div>

                    <!-- Plagiarism & Oral Analysis Note -->
                    <div x-show="originalityAudit?.plagiarism_risk_reason" class="text-xs text-stone-700 dark:text-stone-300 p-3 bg-white dark:bg-stone-900 rounded-xl border border-stone-200 dark:border-stone-800">
                        <span class="font-bold text-stone-900 dark:text-stone-100 block mb-0.5">Integrity Analysis:</span>
                        <p class="text-xs leading-relaxed" x-text="originalityAudit?.plagiarism_risk_reason"></p>
                    </div>

                    <!-- Historical Fact-Check Flags -->
                    <div x-show="originalityAudit?.historical_fact_check_flags?.length" class="space-y-2">
                        <span class="text-[11px] font-bold uppercase tracking-wider text-stone-500">Historical & Elder Verification Flags:</span>
                        <div class="space-y-1.5">
                            <template x-for="(flag, idx) in originalityAudit?.historical_fact_check_flags || []">
                                <div class="p-2.5 bg-white dark:bg-stone-900 rounded-xl border border-stone-200 dark:border-stone-800 text-xs space-y-1">
                                    <div class="flex items-start justify-between gap-2">
                                        <span class="font-semibold text-stone-800 dark:text-stone-200" x-text="flag.claim"></span>
                                        <span class="text-[9px] uppercase px-1.5 py-0.5 rounded font-bold shrink-0"
                                              :class="flag.verification_status === 'consistent_with_known_history' ? 'bg-green-100 text-green-700' : 'bg-amber-100 text-amber-800'"
                                              x-text="(flag.verification_status || '').replace(/_/g, ' ')"></span>
                                    </div>
                                    <p class="text-[11px] text-stone-500 italic" x-text="flag.guidance"></p>
                                </div>
                            </template>
                        </div>
                    </div>

                    <!-- Cultural Advisory Disclaimer -->
                    <div class="p-2.5 rounded-xl bg-amber-50 dark:bg-amber-950/30 border border-amber-200 dark:border-amber-800 text-[11px] text-amber-800 dark:text-amber-300 flex items-start space-x-2">
                        <i data-lucide="info" class="w-3.5 h-3.5 flex-shrink-0 mt-0.5 text-amber-600"></i>
                        <span>AI advisory screen to support community stewardship — not legal copyright certification. Community elders and moderators retain final authority.</span>
                    </div>
                </div>
            </div>

            <!-- SUB-FEATURE B: MUSE IMAGE CULTURAL ILLUSTRATION -->
            <div class="p-5 rounded-2xl bg-stone-50 dark:bg-stone-800/40 border border-stone-200 dark:border-stone-700/80 space-y-4">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                    <div>
                        <h3 class="text-sm font-bold text-stone-900 dark:text-stone-100 flex items-center space-x-2">
                            <i data-lucide="image" class="w-4 h-4 text-amber-600"></i>
                            <span>Muse Image Cultural Illustration (Optional)</span>
                        </h3>
                        <p class="text-xs text-stone-500">
                            Generate artistic artwork representing the story. Strictly labeled as AI-generated; never presented as authentic historical photos.
                        </p>
                    </div>
                    <button type="button"
                            @click="runGenerateIllustration()"
                            :disabled="isImageLoading"
                            class="px-4 py-2 bg-amber-500 hover:bg-amber-600 disabled:opacity-50 text-white font-bold rounded-xl text-xs uppercase tracking-wider transition-colors inline-flex items-center space-x-1.5 flex-shrink-0">
                        <template x-if="isImageLoading">
                            <span>Generating Artwork...</span>
                        </template>
                        <template x-if="!isImageLoading">
                            <span>Generate Illustration</span>
                        </template>
                    </button>
                </div>

                <!-- Illustration Preview -->
                <div x-show="generatedImageUrl" class="space-y-2 pt-2 border-t border-stone-200 dark:border-stone-700">
                    <div class="relative rounded-2xl overflow-hidden max-w-sm border border-stone-200 dark:border-stone-700">
                        <img :src="generatedImageUrl" alt="AI Cultural Illustration" class="w-full h-auto object-cover">
                        <div class="absolute bottom-2 left-2 right-2 px-2.5 py-1.5 bg-black/75 backdrop-blur-md rounded-lg text-[10px] text-white font-semibold flex items-center space-x-1.5">
                            <i data-lucide="sparkles" class="w-3 h-3 text-amber-400"></i>
                            <span>AI-Generated Cultural Illustration • Artistic Interpretation</span>
                        </div>
                    </div>
                    <div class="flex items-center space-x-3 text-xs">
                        <button type="button" @click="generatedImageUrl = ''; generatedImagePath = '';" class="text-red-500 hover:text-red-700 font-semibold">
                            Discard Illustration
                        </button>
                    </div>
                </div>
            </div>

            <!-- SUB-FEATURE C: SAM 3.1 ARTIFACT SEGMENTATION -->
            <div class="p-5 rounded-2xl bg-stone-50 dark:bg-stone-800/40 border border-stone-200 dark:border-stone-700/80 space-y-4">
                <div>
                    <h3 class="text-sm font-bold text-stone-900 dark:text-stone-100 flex items-center space-x-2">
                        <i data-lucide="scissors" class="w-4 h-4 text-amber-600"></i>
                        <span>SAM 3.1 Authentic Artifact Segmentation (Optional)</span>
                    </h3>
                    <p class="text-xs text-stone-500">
                        Isolate and extract an authentic artifact (beaded crown, mask, carved stool, calabash) from a real photograph.
                    </p>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-stone-600 dark:text-stone-400 mb-1">
                            Artifact Photo (JPG, PNG, WEBP)
                        </label>
                        <input type="file"
                               id="samImageInput"
                               accept="image/jpeg,image/png,image/webp"
                               @change="handleSamImageSelect($event)"
                               class="text-xs text-stone-500 file:mr-2 file:py-2 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-stone-200 dark:file:bg-stone-700 file:text-stone-700 dark:file:text-stone-200 hover:file:bg-stone-300">
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-stone-600 dark:text-stone-400 mb-1">
                            Object Description Prompt
                        </label>
                        <div class="flex space-x-2">
                            <input type="text"
                                   x-model="samPrompt"
                                   placeholder="e.g. beaded crown, carved mask, wooden stool"
                                   class="w-full stnd-input px-3.5 py-2 text-xs">
                            <button type="button"
                                    @click="runSamSegmentation()"
                                    :disabled="isSamLoading || !samFile || !samPrompt.trim()"
                                    class="px-4 py-2 bg-amber-500 hover:bg-amber-600 disabled:opacity-50 text-white font-bold rounded-xl text-xs flex-shrink-0 transition-colors">
                                <span x-text="isSamLoading ? 'Segmenting...' : 'Segment'"></span>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Segmentation Result Preview -->
                <div x-show="samSegmentedUrl" class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-3 border-t border-stone-200 dark:border-stone-700">
                    <div class="space-y-1">
                        <span class="text-[10px] font-bold uppercase text-stone-500">Original Authentic Photo</span>
                        <img :src="samOriginalUrl" class="w-full h-44 object-cover rounded-xl border border-stone-200 dark:border-stone-700">
                    </div>
                    <div class="space-y-1">
                        <span class="text-[10px] font-bold uppercase text-amber-600 font-bold">SAM 3.1 Segmented Cutout</span>
                        <div class="w-full h-44 rounded-xl border border-amber-300 dark:border-amber-700 bg-stone-100 dark:bg-stone-950 flex items-center justify-center p-2">
                            <img :src="samSegmentedUrl" class="max-h-full max-w-full object-contain">
                        </div>
                    </div>
                    <div class="col-span-full text-[11px] text-stone-500 italic">
                        Visual artifact boundary isolation via SAM 3.1. Not proof of historical antiquity.
                    </div>
                </div>
            </div>

            <!-- Stage 4 Navigation -->
            <div class="flex items-center justify-between pt-4 border-t border-stone-100 dark:border-stone-800">
                <button type="button"
                        @click="step = 3"
                        class="px-5 py-2.5 text-stone-600 dark:text-stone-400 hover:text-stone-900 font-bold text-xs uppercase tracking-wider">
                    &larr; Back
                </button>
                <button type="button"
                        @click="step = 5"
                        class="px-6 py-3 bg-amber-500 hover:bg-amber-600 text-white font-bold rounded-xl text-xs uppercase tracking-wider transition-colors">
                    Continue to Final Review &rarr;
                </button>
            </div>
        </div>

        <!-- ================================================================= -->
        <!-- STAGE 5: FINAL REVIEW & MODERATION SUBMISSION                     -->
        <!-- ================================================================= -->
        <div x-show="step === 5" class="space-y-6">
            <div class="border-b border-stone-100 dark:border-stone-800 pb-4">
                <h2 class="text-lg font-bold text-stone-900 dark:text-stone-100 flex items-center space-x-2">
                    <i data-lucide="check-circle" class="w-5 h-5 text-amber-600"></i>
                    <span>Stage 5: Final Review & Submission</span>
                </h2>
                <p class="text-xs text-stone-500">
                    Review your Living Heritage entry. You can save it privately as a draft or submit it to Kulturegram's community moderation queue.
                </p>
            </div>

            <!-- Summary Card -->
            <div class="border border-stone-200 dark:border-stone-800 rounded-2xl p-5 space-y-4 bg-stone-50/50 dark:bg-stone-900/50">
                <div class="flex items-start justify-between">
                    <div>
                        <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-amber-100 text-amber-800 uppercase tracking-wider" x-text="cultureCategory"></span>
                        <h3 class="text-xl font-bold text-stone-900 dark:text-stone-100 mt-1" x-text="cultureName"></h3>
                        <p class="text-xs text-stone-500" x-text="cultureRegion + (cultureLanguage ? ' • ' + cultureLanguage : '')"></p>
                    </div>
                    <span class="text-xs px-2.5 py-1 rounded-full bg-stone-200 dark:bg-stone-800 text-stone-700 dark:text-stone-300 font-semibold">
                        AI-Assisted Living Heritage
                    </span>
                </div>

                <div class="text-xs text-stone-700 dark:text-stone-300 leading-relaxed" x-text="cultureDescription"></div>

                <!-- Provenance & Media badges -->
                <div class="pt-3 border-t border-stone-200 dark:border-stone-800 flex flex-wrap gap-2 text-[11px]">
                    <template x-if="audioPreviewUrl">
                        <span class="px-2 py-1 bg-white dark:bg-stone-800 border border-stone-200 dark:border-stone-700 rounded-lg text-stone-600 dark:text-stone-400">
                            🎵 Authentic Audio Attached
                        </span>
                    </template>
                    <template x-if="transcript">
                        <span class="px-2 py-1 bg-white dark:bg-stone-800 border border-stone-200 dark:border-stone-700 rounded-lg text-stone-600 dark:text-stone-400">
                            📜 Verified Transcript Attached
                        </span>
                    </template>
                    <template x-if="generatedImageUrl">
                        <span class="px-2 py-1 bg-amber-50 dark:bg-amber-950/40 border border-amber-200 dark:border-amber-800 rounded-lg text-amber-700 dark:text-amber-400">
                            🎨 Muse Image Illustration Attached
                        </span>
                    </template>
                    <template x-if="samSegmentedUrl">
                        <span class="px-2 py-1 bg-stone-100 dark:bg-stone-800 border border-stone-200 dark:border-stone-700 rounded-lg text-stone-700 dark:text-stone-300">
                            🏺 SAM 3.1 Artifact Segmented
                        </span>
                    </template>
                </div>
            </div>

            <!-- Optional Primary Authentic Photo Upload -->
            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-stone-600 dark:text-stone-400 mb-1">
                    Primary Cover Photo (Authentic Photograph)
                </label>
                <input type="file"
                       id="primaryCoverImage"
                       accept="image/*"
                       @change="handleCoverImageSelect($event)"
                       class="text-xs text-stone-500 file:mr-2 file:py-2 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-stone-200 dark:file:bg-stone-700 file:text-stone-700 dark:file:text-stone-200 hover:file:bg-stone-300">
                <p class="text-[11px] text-stone-400 mt-1">If empty, the generated illustration will be used as the presentation cover.</p>
            </div>

            <!-- Moderation & Publishing Notice -->
            <div class="p-4 rounded-2xl bg-amber-50/70 dark:bg-amber-950/30 border border-amber-200 dark:border-amber-800/60 text-xs text-stone-600 dark:text-stone-400 space-y-1">
                <span class="font-bold text-stone-900 dark:text-stone-100 flex items-center space-x-1.5">
                    <i data-lucide="shield-alert" class="w-4 h-4 text-amber-600"></i>
                    <span>Moderation Safeguard:</span>
                </span>
                <p>
                    AI generation does NOT automatically approve or publish any entry. All submissions are directed to Kulturegram's community moderation team for cultural integrity and verification.
                </p>
            </div>

            <!-- Dual Submission Buttons -->
            <div class="flex flex-col sm:flex-row items-center justify-between gap-3 pt-4 border-t border-stone-100 dark:border-stone-800">
                <button type="button"
                        @click="step = 4"
                        class="px-5 py-2.5 text-stone-600 dark:text-stone-400 hover:text-stone-900 font-bold text-xs uppercase tracking-wider">
                    &larr; Back to Enrich
                </button>

                <div class="flex items-center space-x-3 w-full sm:w-auto">
                    <!-- Save as Draft -->
                    <button type="button"
                            @click="submitFinal('draft')"
                            :disabled="isSubmitting"
                            class="flex-1 sm:flex-initial px-5 py-3 bg-stone-100 dark:bg-stone-800 hover:bg-stone-200 dark:hover:bg-stone-700 text-stone-800 dark:text-stone-200 font-bold rounded-xl text-xs uppercase tracking-wider transition-colors border border-stone-200 dark:border-stone-700">
                        <span x-text="isSubmitting ? 'Saving...' : 'Save Private Draft'"></span>
                    </button>

                    <!-- Submit to Moderation -->
                    <button type="button"
                            @click="submitFinal('submit')"
                            :disabled="isSubmitting"
                            class="flex-1 sm:flex-initial px-6 py-3 bg-amber-500 hover:bg-amber-600 active:bg-amber-700 text-white font-bold rounded-xl text-xs uppercase tracking-wider transition-colors shadow-sm">
                        <span x-text="isSubmitting ? 'Submitting...' : 'Submit to Community Moderation'"></span>
                    </button>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection

@push('scripts')
<script>
function livingHeritageWizard(config) {
    return {
        step: 1,
        isConfigured: config.isConfigured,
        maskedKey: config.maskedKey,
        csrfToken: config.csrfToken,
        errorMessage: '',

        // Stage 1 State
        captureMode: 'audio',
        audioFile: null,
        audioFileName: '',
        audioPreviewUrl: '',
        storedAudioPath: '',
        rawStory: '',
        regionHint: '',
        languageHint: '',
        consentGranted: true,

        // Stage 2 State (Voice Transcribe)
        transcript: '',
        isTranscribing: false,
        detectedLanguage: '',

        // Stage 3 State (Spark)
        isSparkLoading: false,
        sparkRecord: null,
        cultureName: '',
        cultureRegion: '',
        cultureCategory: 'Oral Traditions',
        cultureDescription: '',
        cultureLanguage: '',
        culturePeriod: '',
        cultureSignificance: '',
        cultureRituals: '',
        cultureTransmission: '',
        cultureChallenges: '',
        cultureTags: '',

        // Stage 4 State (Wonder, Image, SAM, Audit)
        isWonderLoading: false,
        wonderPlan: null,
        isAuditLoading: false,
        originalityAudit: null,
        isImageLoading: false,
        generatedImageUrl: '',
        generatedImagePath: '',
        isSamLoading: false,
        samFile: null,
        samPrompt: '',
        samOriginalUrl: '',
        samSegmentedUrl: '',
        samSegmentedPath: '',

        // Stage 5 State
        coverImageFile: null,
        isSubmitting: false,

        rawStoryWordCount() {
            return this.rawStory.trim() ? this.rawStory.trim().split(/\s+/).length : 0;
        },

        transcriptWordCount() {
            return this.transcript.trim() ? this.transcript.trim().split(/\s+/).length : 0;
        },

        handleAudioSelect(event) {
            const file = event.target.files[0];
            if (!file) return;

            if (file.size > 26214400) { // 25MB
                this.errorMessage = 'Audio file exceeds 25MB limit.';
                return;
            }

            this.audioFile = file;
            this.audioFileName = file.name;
            this.audioPreviewUrl = URL.createObjectURL(file);
            this.errorMessage = '';
        },

        handleSamImageSelect(event) {
            const file = event.target.files[0];
            if (!file) return;
            this.samFile = file;
        },

        handleCoverImageSelect(event) {
            const file = event.target.files[0];
            if (!file) return;
            this.coverImageFile = file;
        },

        proceedFromStage1() {
            if (!this.consentGranted) {
                this.errorMessage = 'Please confirm cultural consent before proceeding.';
                return;
            }

            // If audio uploaded, move to Stage 2 for transcription
            if (this.audioFile) {
                this.step = 2;
            } else if (this.rawStory.trim()) {
                // If written story entered directly, copy to transcript and proceed to Stage 2 or 3
                this.transcript = this.rawStory;
                this.step = 2;
            }
        },

        async runTranscription() {
            if (!this.audioFile) {
                this.errorMessage = 'Please select an audio file first.';
                return;
            }

            this.isTranscribing = true;
            this.errorMessage = '';

            const formData = new FormData();
            formData.append('audio', this.audioFile);
            if (this.languageHint) formData.append('language_hint', this.languageHint);
            formData.append('consent', '1');

            try {
                const response = await fetch("{{ route('cultural-hub.living-heritage.transcribe') }}", {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': this.csrfToken,
                        'Accept': 'application/json'
                    },
                    body: formData
                });

                const data = await response.json();
                if (data.success) {
                    this.transcript = data.transcript;
                    this.storedAudioPath = data.audio_path;
                    this.detectedLanguage = data.language || this.languageHint;
                } else {
                    this.errorMessage = data.error || 'Audio transcription could not be completed.';
                }
            } catch (err) {
                this.errorMessage = 'Failed to connect to transcription service: ' + err.message;
            } finally {
                this.isTranscribing = false;
            }
        },

        proceedFromStage2() {
            if (!this.transcript.trim()) {
                this.errorMessage = 'Please provide or transcribe a story narrative.';
                return;
            }
            this.step = 3;
        },

        async runSparkPrepare() {
            const textToProcess = this.transcript.trim() || this.rawStory.trim();
            if (!textToProcess) {
                this.errorMessage = 'No narrative text found to process.';
                return;
            }

            this.isSparkLoading = true;
            this.errorMessage = '';

            try {
                const response = await fetch("{{ route('cultural-hub.living-heritage.prepare') }}", {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': this.csrfToken,
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        story: textToProcess,
                        region: this.regionHint,
                        language: this.languageHint,
                        consent: '1'
                    })
                });

                const data = await response.json();
                if (data.success && data.record) {
                    this.sparkRecord = data.record;
                    this.cultureName = data.record.name || this.cultureName;
                    this.cultureRegion = data.record.region || this.regionHint;
                    this.cultureCategory = data.record.category || this.cultureCategory;
                    this.cultureDescription = data.record.description || textToProcess;
                    this.cultureLanguage = data.record.language || this.languageHint;
                    this.culturePeriod = data.record.historical_period || '';
                    this.cultureSignificance = data.record.significance || '';
                    this.cultureRituals = data.record.rituals || '';
                    this.cultureTransmission = data.record.transmission_methods || '';
                    this.cultureChallenges = data.record.challenges || '';
                    if (Array.isArray(data.record.tags)) {
                        this.cultureTags = data.record.tags.join(', ');
                    }
                } else {
                    this.errorMessage = data.error || 'Muse Spark could not structure the story.';
                }
            } catch (err) {
                this.errorMessage = 'Failed to contact Muse Spark: ' + err.message;
            } finally {
                this.isSparkLoading = false;
            }
        },

        proceedFromStage3() {
            if (!this.cultureName.trim() || !this.cultureDescription.trim()) {
                this.errorMessage = 'Please provide both a Title and Description for the record.';
                return;
            }
            this.step = 4;
        },

        async runWonderPlan() {
            this.isWonderLoading = true;
            this.errorMessage = '';

            try {
                const response = await fetch("{{ route('cultural-hub.living-heritage.wonder-plan') }}", {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': this.csrfToken,
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        name: this.cultureName,
                        region: this.cultureRegion,
                        category: this.cultureCategory,
                        description: this.cultureDescription,
                        raw_story: this.transcript || this.rawStory
                    })
                });

                const data = await response.json();
                if (data.success && data.preservation_plan) {
                    this.wonderPlan = data.preservation_plan;
                } else {
                    this.errorMessage = data.error || 'Muse Wonder preservation audit could not run.';
                }
            } catch (err) {
                this.errorMessage = 'Failed to run Muse Wonder: ' + err.message;
            } finally {
                this.isWonderLoading = false;
            }
        },

        async runOriginalityAudit() {
            const story = this.rawStory || this.transcript || this.cultureDescription;
            if (!story || story.trim().length < 20) {
                this.errorMessage = 'Please provide at least 20 characters of story or transcript to audit.';
                return;
            }

            this.isAuditLoading = true;
            this.errorMessage = '';

            try {
                const response = await fetch("{{ route('cultural-hub.living-heritage.audit') }}", {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': this.csrfToken,
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        story: story,
                        name: this.cultureName,
                        region: this.cultureRegion,
                        category: this.cultureCategory,
                        description: this.cultureDescription
                    })
                });

                const data = await response.json();
                if (data.success && data.audit) {
                    this.originalityAudit = data.audit;
                } else {
                    this.errorMessage = data.error || 'Muse Wonder originality audit could not run.';
                }
            } catch (err) {
                this.errorMessage = 'Failed to run Fact-Check & Originality Audit: ' + err.message;
            } finally {
                this.isAuditLoading = false;
            }
        },

        async runGenerateIllustration() {
            const story = this.cultureDescription || this.transcript || this.rawStory;
            if (!story) {
                this.errorMessage = 'Please provide a story description before generating artwork.';
                return;
            }

            this.isImageLoading = true;
            this.errorMessage = '';

            try {
                const response = await fetch("{{ route('cultural-hub.living-heritage.illustration') }}", {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': this.csrfToken,
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        story: story,
                        cultural_context: this.cultureRegion || this.cultureName
                    })
                });

                const data = await response.json();
                if (data.success && data.image_url) {
                    this.generatedImageUrl = data.image_url;
                    this.generatedImagePath = data.image_path;
                } else {
                    this.errorMessage = data.error || 'Muse Image could not generate illustration.';
                }
            } catch (err) {
                this.errorMessage = 'Failed to generate illustration: ' + err.message;
            } finally {
                this.isImageLoading = false;
            }
        },

        async runSamSegmentation() {
            if (!this.samFile || !this.samPrompt.trim()) {
                this.errorMessage = 'Please choose an artifact image and describe the object.';
                return;
            }

            this.isSamLoading = true;
            this.errorMessage = '';

            const formData = new FormData();
            formData.append('artifact_image', this.samFile);
            formData.append('object_prompt', this.samPrompt);

            try {
                const response = await fetch("{{ route('cultural-hub.living-heritage.segment') }}", {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': this.csrfToken,
                        'Accept': 'application/json'
                    },
                    body: formData
                });

                const data = await response.json();
                if (data.success) {
                    this.samOriginalUrl = data.original_url;
                    this.samSegmentedUrl = data.segmented_url;
                    this.samSegmentedPath = data.segmented_path;
                } else {
                    this.errorMessage = data.error || 'SAM 3.1 segmentation failed.';
                }
            } catch (err) {
                this.errorMessage = 'Failed to contact SAM segmentation: ' + err.message;
            } finally {
                this.isSamLoading = false;
            }
        },

        async submitFinal(actionType) {
            this.isSubmitting = true;
            this.errorMessage = '';

            const formData = new FormData();
            formData.append('name', this.cultureName);
            formData.append('region', this.cultureRegion || 'Global Heritage');
            formData.append('category', this.cultureCategory);
            formData.append('description', this.cultureDescription);
            formData.append('language', this.cultureLanguage || '');
            formData.append('historical_period', this.culturePeriod || '');
            formData.append('significance', this.cultureSignificance || '');
            formData.append('rituals', this.cultureRituals || '');
            formData.append('transmission_methods', this.cultureTransmission || '');
            formData.append('challenges', this.cultureChallenges || '');
            formData.append('raw_story', this.rawStory || '');
            formData.append('audio_transcript', this.transcript || '');
            formData.append('audio_path', this.storedAudioPath || '');
            formData.append('action', actionType);

            if (this.cultureTags) {
                formData.append('tags', this.cultureTags);
            }

            if (this.generatedImagePath) {
                formData.append('ai_illustration', this.generatedImagePath);
                formData.append('ai_illustration_prompt', 'Muse Image Cultural Artwork');
            }

            if (this.samSegmentedPath) {
                formData.append('artifact_segmented_path', this.samSegmentedPath);
                formData.append('artifact_label', this.samPrompt || 'Artifact');
            }

            if (this.wonderPlan) {
                formData.append('wonder_plan_json', JSON.stringify(this.wonderPlan));
            }

            if (this.originalityAudit) {
                formData.append('originality_audit_json', JSON.stringify(this.originalityAudit));
            }

            if (this.coverImageFile) {
                formData.append('image', this.coverImageFile);
            }

            try {
                const response = await fetch("{{ route('cultural-hub.living-heritage.store') }}", {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': this.csrfToken,
                        'Accept': 'application/json'
                    },
                    body: formData
                });

                const data = await response.json();
                if (data.success && data.redirect_url) {
                    window.location.href = data.redirect_url;
                } else if (data.errors) {
                    const firstKey = Object.keys(data.errors)[0];
                    this.errorMessage = data.errors[firstKey][0];
                } else {
                    this.errorMessage = data.message || 'Failed to submit entry.';
                }
            } catch (err) {
                this.errorMessage = 'Submission failed: ' + err.message;
            } finally {
                this.isSubmitting = false;
            }
        }
    }
}
</script>
@endpush
