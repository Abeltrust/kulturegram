@extends('layouts.app')

@section('title', 'Teach a Cultural Class - Educational Studio')

@section('content')
<script>
    // Ensure classStudio is globally defined before Alpine evaluates x-data
    window.classStudio = function(config) {
        return {
            classTitle: '{{ addslashes(old('title', '')) }}',
            targetLanguage: '{{ addslashes(old('target_language', 'Hausa')) }}',
            culturalFocus: '{{ addslashes(old('cultural_focus', 'Hausa Cultural Greetings & Etiquette')) }}',
            priceType: '{{ addslashes(old('price_type', 'free')) }}',
            priceAmount: '{{ addslashes(old('price', '4.99')) }}',
            sampleVideoUrl: '{{ addslashes(old('sample_video_url', '')) }}',
            draftTranscript: '{{ addslashes(old('raw_transcript', '')) }}',
            selectedBackgroundUrl: '{{ addslashes(old('virtual_background_url', '')) }}',
            customBackdropPrompt: '',
            isGeneratingSubtitles: false,
            isGeneratingBackdrop: false,
            isSubmitting: false,
            generatedSubtitles: [],
            generatedVocabulary: [],
            mediaFile: null,
            videoPreviewUrl: null,
            fileSizeMb: null,
            isOverLimit: false,

            handleFileSelected(event) {
                if (event.target.files && event.target.files[0]) {
                    const file = event.target.files[0];
                    this.mediaFile = file;
                    const mb = file.size / (1024 * 1024);
                    this.fileSizeMb = mb.toFixed(1);
                    this.isOverLimit = mb > 40;

                    if (file.type.startsWith('video/')) {
                        try {
                            this.videoPreviewUrl = URL.createObjectURL(file);
                        } catch(e) {
                            this.videoPreviewUrl = null;
                        }
                    } else {
                        this.videoPreviewUrl = null;
                    }
                }
            },

            async generateAiSubtitles() {
                this.isGeneratingSubtitles = true;
                try {
                    const formData = new FormData();
                    formData.append('target_language', this.targetLanguage);
                    formData.append('cultural_focus', this.culturalFocus);
                    if (this.draftTranscript) {
                        formData.append('transcript', this.draftTranscript);
                    }
                    if (this.mediaFile) {
                        formData.append('media', this.mediaFile);
                    }

                    const response = await fetch(config.generateSubtitlesUrl, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': config.csrfToken,
                            'Accept': 'application/json'
                        },
                        body: formData
                    });

                    const data = await response.json();
                    if (data.success) {
                        this.generatedSubtitles = data.subtitles || [];
                        this.generatedVocabulary = data.vocabulary || [];
                        if (data.transcript && !this.draftTranscript) {
                            this.draftTranscript = data.transcript;
                        }
                    }
                } catch (e) {
                    console.error('Error generating AI subtitles:', e);
                } finally {
                    this.isGeneratingSubtitles = false;
                    this.$nextTick(() => {
                        if (typeof lucide !== 'undefined') lucide.createIcons();
                    });
                }
            },

            async generateCustomBackdrop() {
                if (!this.customBackdropPrompt) return;
                this.isGeneratingBackdrop = true;

                try {
                    const response = await fetch(config.generateBackgroundUrl, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': config.csrfToken,
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            cultural_theme: this.customBackdropPrompt
                        })
                    });

                    const data = await response.json();
                    if (data.success && data.image_url) {
                        this.selectedBackgroundUrl = data.image_url;
                    }
                } catch (e) {
                    console.error('Error generating studio background:', e);
                } finally {
                    this.isGeneratingBackdrop = false;
                    this.$nextTick(() => {
                        if (typeof lucide !== 'undefined') lucide.createIcons();
                    });
                }
            }
        };
    };
</script>

<div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8 sm:py-12" 
     x-data="classStudio({
         generateSubtitlesUrl: '{{ route('education.classes.ai-transcribe') }}',
         generateBackgroundUrl: '{{ route('education.classes.ai-studio-background') }}',
         csrfToken: '{{ csrf_token() }}'
     })">

    {{-- Breadcrumb --}}
    <div class="flex items-center space-x-2 text-xs sm:text-sm text-stone-500 dark:text-stone-400 mb-6">
        <a href="{{ route('education.index') }}" class="hover:text-amber-500 transition">Educational Hub</a>
        <span>/</span>
        <a href="{{ route('education.classes.index') }}" class="hover:text-amber-500 transition">Cultural Classes</a>
        <span>/</span>
        <span class="text-stone-800 dark:text-stone-100 font-semibold">Creator Studio</span>
    </div>

    {{-- Header Banner --}}
    <div class="bg-gradient-to-r from-stone-900 via-stone-900 to-stone-950 rounded-3xl border border-stone-800 p-6 sm:p-8 mb-8 shadow-xl relative overflow-hidden">
        <div class="absolute -right-10 -bottom-10 w-60 h-60 bg-amber-500/10 rounded-full blur-2xl pointer-events-none"></div>

        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 relative z-10">
            <div>
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-amber-500/10 border border-amber-500/20 text-amber-400 text-xs font-bold mb-2">
                    <i data-lucide="video" class="w-3.5 h-3.5"></i>
                    <span>AI Cultural Studio & Bilingual Subtitles</span>
                </div>
                <h1 class="text-2xl sm:text-3xl font-black text-white tracking-tight">
                    Teach a Cultural Heritage Class
                </h1>
                <p class="text-stone-400 text-xs sm:text-sm mt-1 max-w-xl">
                    Share language lessons, cultural etiquette, or traditional wisdom. AI automatically generates synchronized subtitles, dual-language vocabulary flashcards, and virtual heritage backgrounds.
                </p>
            </div>

            <div class="flex items-center gap-3">
                <span class="text-xs font-bold px-3 py-1.5 rounded-xl bg-stone-800 text-stone-300 border border-stone-700/80">
                    Free or Paid Monetization
                </span>
            </div>
        </div>
    </div>

    @if ($errors->any())
        <div class="mb-6 p-4 rounded-2xl bg-red-500/10 border border-red-500/20 text-red-400 text-xs sm:text-sm">
            <div class="font-bold mb-1 flex items-center gap-1.5">
                <i data-lucide="alert-triangle" class="w-4 h-4 text-red-500"></i>
                <span>Please check the following before publishing:</span>
            </div>
            <ul class="list-disc list-inside space-y-0.5 mt-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Main Studio Form --}}
    <form method="POST" action="{{ route('education.classes.store') }}" enctype="multipart/form-data" @submit="isSubmitting = true" class="space-y-8">
        @csrf

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
            
            {{-- Left Column: Class Information & Pricing (7 cols) --}}
            <div class="lg:col-span-7 space-y-6">
                
                {{-- Basic Information Card --}}
                <div class="bg-white dark:bg-stone-900 rounded-3xl p-6 sm:p-8 border border-stone-200 dark:border-stone-800 shadow-sm space-y-5">
                    <h2 class="text-base sm:text-lg font-black text-stone-800 dark:text-stone-100 flex items-center gap-2">
                        <i data-lucide="book-open" class="w-5 h-5 text-amber-500"></i>
                        <span>Class Overview</span>
                    </h2>

                    {{-- Class Title --}}
                    <div>
                        <label class="block text-xs font-black uppercase tracking-wider text-stone-700 dark:text-stone-300 mb-1.5">
                            Class Title *
                        </label>
                        <input type="text" name="title" x-model="classTitle" required
                               placeholder="e.g. Hausa Everyday Greetings & Market Etiquette"
                               class="w-full rounded-xl border border-stone-200 dark:border-stone-800 bg-stone-50 dark:bg-stone-950 px-4 py-3 text-sm text-stone-800 dark:text-stone-100 focus:ring-2 focus:ring-amber-500 outline-none">
                        @error('title')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Cultural Focus & Target Language --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-black uppercase tracking-wider text-stone-700 dark:text-stone-300 mb-1.5">
                                Target Language *
                            </label>
                            <select name="target_language" x-model="targetLanguage" required
                                    class="w-full rounded-xl border border-stone-200 dark:border-stone-800 bg-stone-50 dark:bg-stone-950 px-3.5 py-3 text-sm text-stone-800 dark:text-stone-100 focus:ring-2 focus:ring-amber-500 outline-none">
                                @foreach ($commonLanguages as $lang)
                                    <option value="{{ $lang }}">{{ $lang }}</option>
                                @endforeach
                            </select>
                            @error('target_language')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-black uppercase tracking-wider text-stone-700 dark:text-stone-300 mb-1.5">
                                Cultural Focus *
                            </label>
                            <input type="text" name="cultural_focus" x-model="culturalFocus" required
                                   placeholder="e.g. Hausa Northern Heritage & Respect"
                                   class="w-full rounded-xl border border-stone-200 dark:border-stone-800 bg-stone-50 dark:bg-stone-950 px-4 py-3 text-sm text-stone-800 dark:text-stone-100 focus:ring-2 focus:ring-amber-500 outline-none">
                            @error('cultural_focus')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    {{-- Level & Description --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-black uppercase tracking-wider text-stone-700 dark:text-stone-300 mb-1.5">
                                Difficulty Level
                            </label>
                            <select name="level" class="w-full rounded-xl border border-stone-200 dark:border-stone-800 bg-stone-50 dark:bg-stone-950 px-3.5 py-3 text-sm text-stone-800 dark:text-stone-100 focus:ring-2 focus:ring-amber-500 outline-none">
                                <option value="all_levels">All Levels / General</option>
                                <option value="beginner">Beginner Friendly</option>
                                <option value="intermediate">Intermediate</option>
                                <option value="advanced">Advanced Cultural Knowledge</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-black uppercase tracking-wider text-stone-700 dark:text-stone-300 mb-1.5">
                                Explanation Language
                            </label>
                            <input type="text" name="source_language" value="{{ old('source_language', 'English') }}"
                                   class="w-full rounded-xl border border-stone-200 dark:border-stone-800 bg-stone-50 dark:bg-stone-950 px-4 py-3 text-sm text-stone-800 dark:text-stone-100 focus:ring-2 focus:ring-amber-500 outline-none">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-black uppercase tracking-wider text-stone-700 dark:text-stone-300 mb-1.5">
                            Lesson Description & Background Notes
                        </label>
                        <textarea name="description" rows="3"
                                  placeholder="Describe what learners will discover in this class..."
                                  class="w-full rounded-xl border border-stone-200 dark:border-stone-800 bg-stone-50 dark:bg-stone-950 p-4 text-sm text-stone-800 dark:text-stone-100 focus:ring-2 focus:ring-amber-500 outline-none leading-relaxed">{{ old('description') }}</textarea>
                    </div>
                </div>

                {{-- Access & Pricing Card --}}
                <div class="bg-white dark:bg-stone-900 rounded-3xl p-6 sm:p-8 border border-stone-200 dark:border-stone-800 shadow-sm space-y-4">
                    <h2 class="text-base sm:text-lg font-black text-stone-800 dark:text-stone-100 flex items-center gap-2">
                        <i data-lucide="tag" class="w-5 h-5 text-amber-500"></i>
                        <span>Access & Monetization</span>
                    </h2>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        {{-- Free Tier Option --}}
                        <label class="relative flex flex-col p-4 rounded-2xl border-2 cursor-pointer transition-all"
                               :class="priceType === 'free' ? 'border-emerald-500 bg-emerald-500/5' : 'border-stone-200 dark:border-stone-800 hover:border-stone-300'">
                            <div class="flex items-center justify-between mb-2">
                                <span class="font-black text-sm text-stone-800 dark:text-stone-100">Free Heritage Class</span>
                                <input type="radio" name="price_type" value="free" x-model="priceType" class="text-emerald-500 focus:ring-emerald-500">
                            </div>
                            <p class="text-xs text-stone-500 dark:text-stone-400">
                                Open to all community members and cultural enthusiasts worldwide.
                            </p>
                        </label>

                        {{-- Paid Tier Option --}}
                        <label class="relative flex flex-col p-4 rounded-2xl border-2 cursor-pointer transition-all"
                               :class="priceType === 'paid' ? 'border-amber-500 bg-amber-500/5' : 'border-stone-200 dark:border-stone-800 hover:border-stone-300'">
                            <div class="flex items-center justify-between mb-2">
                                <span class="font-black text-sm text-stone-800 dark:text-stone-100">Paid Masterclass</span>
                                <input type="radio" name="price_type" value="paid" x-model="priceType" class="text-amber-500 focus:ring-amber-500">
                            </div>
                            <p class="text-xs text-stone-500 dark:text-stone-400">
                                Learners purchase access. 45-second preview trailer is shown for free.
                            </p>
                        </label>
                    </div>

                    {{-- Price Amount Input --}}
                    <div x-show="priceType === 'paid'" x-transition class="pt-2">
                        <label class="block text-xs font-black uppercase tracking-wider text-stone-700 dark:text-stone-300 mb-1.5">
                            Class Enrollment Price ($ USD)
                        </label>
                        <div class="relative max-w-xs">
                            <span class="absolute left-3.5 top-3 text-stone-400 font-bold">$</span>
                            <input type="number" step="0.50" min="1.00" name="price" x-model="priceAmount"
                                   placeholder="9.99"
                                   class="w-full rounded-xl border border-stone-200 dark:border-stone-800 bg-stone-50 dark:bg-stone-950 pl-8 pr-4 py-2.5 text-sm text-stone-800 dark:text-stone-100 focus:ring-2 focus:ring-amber-500 outline-none font-bold">
                        </div>
                    </div>
                </div>

                {{-- Video Media Card --}}
                <div class="bg-white dark:bg-stone-900 rounded-3xl p-6 sm:p-8 border border-stone-200 dark:border-stone-800 shadow-sm space-y-4">
                    <h2 class="text-base sm:text-lg font-black text-stone-800 dark:text-stone-100 flex items-center gap-2">
                        <i data-lucide="film" class="w-5 h-5 text-amber-500"></i>
                        <span>Video / Audio Lesson File</span>
                    </h2>

                    <div>
                        <label class="block text-xs font-black uppercase tracking-wider text-stone-700 dark:text-stone-300 mb-1.5">
                            Upload Video or Audio File
                        </label>
                        <input type="file" name="video" id="video-file-input" accept="video/*,audio/*" @change="handleFileSelected($event)"
                               class="w-full text-xs text-stone-500 file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-amber-500 file:text-white hover:file:bg-amber-600 transition cursor-pointer">
                        
                        {{-- File size and limit notice --}}
                        <div class="flex items-center justify-between text-[11px] text-stone-400 mt-1.5">
                            <span>Supports MP4, WebM, MOV, MP3, WAV.</span>
                            <span x-show="fileSizeMb" class="font-bold text-amber-400">
                                Selected: <span x-text="fileSizeMb"></span> MB
                            </span>
                        </div>

                        {{-- Over Limit Alert --}}
                        <div x-show="isOverLimit" x-transition class="mt-2.5 p-3 rounded-xl bg-amber-500/10 border border-amber-500/30 text-amber-400 text-xs">
                            <span class="font-bold">⚠️ Large File Notice:</span> The selected file is <span x-text="fileSizeMb"></span>MB. For files over 40MB, please provide a video link below or upload a compressed clip under 40MB.
                        </div>

                        {{-- Live Video Preview --}}
                        <div x-show="videoPreviewUrl" x-transition class="mt-4 rounded-2xl overflow-hidden bg-black border border-stone-800 aspect-video relative">
                            <video :src="videoPreviewUrl" controls class="w-full h-full object-contain"></video>
                        </div>
                        @error('video')
                            <p class="text-red-500 text-xs mt-1.5">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="relative flex py-1 items-center">
                        <div class="flex-grow border-t border-stone-200 dark:border-stone-800"></div>
                        <span class="flex-shrink mx-4 text-[10px] font-black uppercase tracking-wider text-stone-400">Or use video URL</span>
                        <div class="flex-grow border-t border-stone-200 dark:border-stone-800"></div>
                    </div>

                    <div>
                        <input type="text" name="sample_video_url" x-model="sampleVideoUrl"
                               placeholder="https://example.com/cultural_lesson.mp4"
                               class="w-full rounded-xl border border-stone-200 dark:border-stone-800 bg-stone-50 dark:bg-stone-950 px-4 py-2.5 text-xs sm:text-sm text-stone-800 dark:text-stone-100 focus:ring-2 focus:ring-amber-500 outline-none">
                    </div>

                    {{-- Social Cross-Posting Option --}}
                    <div class="pt-4 border-t border-stone-100 dark:border-stone-800">
                        <label class="flex items-start gap-3 cursor-pointer select-none">
                            <input type="checkbox" name="share_to_timeline" value="1" checked
                                   class="rounded text-amber-500 focus:ring-amber-500 mt-1">
                            <div>
                                <span class="text-xs sm:text-sm font-bold text-stone-800 dark:text-stone-200 block">
                                    Share interactive preview teaser to my Timeline feed
                                </span>
                                <span class="text-xs text-stone-500 dark:text-stone-400 block">
                                    Normal people scrolling your feed will see a preview card and can tap directly into the Educational Hub to learn.
                                </span>
                            </div>
                        </label>
                    </div>
                </div>

            </div>

            {{-- Right Column: AI Subtitles Studio & Cultural Virtual Backdrops (5 cols) --}}
            <div class="lg:col-span-5 space-y-6">

                {{-- AI Transcription & Bilingual Subtitles Generator --}}
                <div class="bg-gradient-to-br from-amber-500/5 via-stone-900/50 to-stone-900 rounded-3xl p-6 sm:p-7 border border-amber-500/30 shadow-md space-y-4">
                    <div class="flex items-center justify-between">
                        <h3 class="text-sm sm:text-base font-black text-stone-800 dark:text-stone-100 flex items-center gap-2">
                            <i data-lucide="sparkles" class="w-4 h-4 text-amber-400"></i>
                            <span>AI Subtitles & Phrase Extraction</span>
                        </h3>
                        <span class="text-[10px] font-black uppercase tracking-wider px-2 py-0.5 rounded-full bg-amber-500/20 text-amber-400 border border-amber-500/30">
                            Smart Sync
                        </span>
                    </div>

                    <p class="text-xs text-stone-600 dark:text-stone-400 leading-relaxed">
                        When teaching, AI transcribes the video, detects bilingual moments (e.g., <em>"In Hausa this is called..."</em>), and builds interactive timestamped subtitles.
                    </p>

                    <div>
                        <label class="block text-xs font-bold text-stone-700 dark:text-stone-300 mb-1">
                            Lesson Script or Spoken Highlights (Optional)
                        </label>
                        <textarea name="raw_transcript" x-model="draftTranscript" rows="4"
                                  placeholder="Type or paste what is spoken in the video, or let AI transcribe the audio track automatically..."
                                  class="w-full rounded-xl border border-stone-200 dark:border-stone-800 bg-white dark:bg-stone-950 p-3 text-xs text-stone-800 dark:text-stone-200 focus:ring-2 focus:ring-amber-500 outline-none leading-relaxed"></textarea>
                    </div>

                    <button type="button" @click="generateAiSubtitles()" :disabled="isGeneratingSubtitles"
                            class="w-full py-2.5 px-4 rounded-xl bg-amber-500 hover:bg-amber-600 disabled:opacity-50 text-white font-bold text-xs shadow-md transition flex items-center justify-center gap-2">
                        <span x-show="!isGeneratingSubtitles" class="flex items-center gap-2">
                            <i data-lucide="wand-2" class="w-4 h-4"></i>
                            <span>Test & Preview AI Subtitles</span>
                        </span>
                        <span x-show="isGeneratingSubtitles" class="flex items-center gap-2">
                            <span class="w-3.5 h-3.5 border-2 border-white border-t-transparent rounded-full animate-spin"></span>
                            <span>Transcribing & Extracting Phrases...</span>
                        </span>
                    </button>

                    {{-- Live Subtitles Preview Box --}}
                    <div x-show="generatedSubtitles.length > 0" x-transition class="space-y-3 pt-3 border-t border-stone-800">
                        <div class="text-xs font-bold text-amber-400 flex items-center justify-between">
                            <span>Extracted Subtitles (<span x-text="generatedSubtitles.length"></span>)</span>
                            <span class="text-[10px] text-stone-400">Timestamped</span>
                        </div>

                        <div class="max-h-48 overflow-y-auto space-y-1.5 pr-1">
                            <template x-for="(sub, idx) in generatedSubtitles" :key="idx">
                                <div class="p-2 rounded-lg bg-stone-950/80 border border-stone-800 text-[11px] text-stone-300">
                                    <div class="flex items-center justify-between text-[10px] text-amber-400/80 font-mono mb-0.5">
                                        <span x-text="sub.start + 's - ' + sub.end + 's'"></span>
                                        <span x-show="sub.target_phrase" class="text-emerald-400 font-bold" x-text="sub.target_phrase"></span>
                                    </div>
                                    <div x-text="sub.text"></div>
                                </div>
                            </template>
                        </div>
                    </div>

                    {{-- Live Vocabulary Preview Box --}}
                    <div x-show="generatedVocabulary.length > 0" x-transition class="space-y-2 pt-2 border-t border-stone-800">
                        <div class="text-xs font-bold text-amber-400">
                            Extracted Cultural Vocabulary (<span x-text="generatedVocabulary.length"></span>)
                        </div>
                        <div class="space-y-1.5">
                            <template x-for="(voc, idx) in generatedVocabulary" :key="idx">
                                <div class="p-2 rounded-lg bg-amber-500/10 border border-amber-500/20 text-xs">
                                    <div class="font-bold text-amber-300" x-text="voc.target + ' · ' + voc.source"></div>
                                    <div class="text-[10px] text-stone-400 mt-0.5" x-text="voc.context"></div>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>

                {{-- AI Cultural Virtual Studio Backdrops --}}
                <div class="bg-white dark:bg-stone-900 rounded-3xl p-6 sm:p-7 border border-stone-200 dark:border-stone-800 shadow-sm space-y-4">
                    <div class="flex items-center justify-between">
                        <h3 class="text-sm sm:text-base font-black text-stone-800 dark:text-stone-100 flex items-center gap-2">
                            <i data-lucide="palette" class="w-4 h-4 text-amber-500"></i>
                            <span>Virtual Cultural Studio Backdrop</span>
                        </h3>
                    </div>

                    <p class="text-xs text-stone-500 dark:text-stone-400">
                        Enhance your video environment with authentic ancestral backdrops. Select a preset or generate a custom setting with AI.
                    </p>

                    <input type="hidden" name="virtual_background_url" :value="selectedBackgroundUrl">

                    {{-- Presets Grid --}}
                    <div class="grid grid-cols-2 gap-2.5">
                        @foreach ($presets as $preset)
                            <div @click="selectedBackgroundUrl = '{{ $preset['image_url'] }}'"
                                 class="relative rounded-xl overflow-hidden border-2 cursor-pointer transition-all aspect-video group"
                                 :class="selectedBackgroundUrl === '{{ $preset['image_url'] }}' ? 'border-amber-500 ring-2 ring-amber-500/40' : 'border-stone-200 dark:border-stone-800 opacity-80 hover:opacity-100'">
                                <img src="{{ $preset['image_url'] }}" class="w-full h-full object-cover" alt="{{ $preset['name'] }}">
                                <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/20 to-transparent flex items-end p-2">
                                    <span class="text-[10px] font-bold text-white line-clamp-1">{{ $preset['name'] }}</span>
                                </div>
                                <div x-show="selectedBackgroundUrl === '{{ $preset['image_url'] }}'" 
                                     class="absolute top-1.5 right-1.5 w-4 h-4 rounded-full bg-amber-500 text-white flex items-center justify-center text-[10px]">
                                    <i data-lucide="check" class="w-3 h-3"></i>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    {{-- Custom AI Backdrop Generator --}}
                    <div class="pt-2 border-t border-stone-100 dark:border-stone-800 space-y-2">
                        <label class="block text-xs font-bold text-stone-700 dark:text-stone-300">
                            Or Generate Custom Heritage Backdrop with AI
                        </label>
                        <div class="flex gap-2">
                            <input type="text" x-model="customBackdropPrompt"
                                   placeholder="e.g. Traditional Zaria mud relief wall with carved mahogany doors"
                                   class="flex-1 rounded-xl border border-stone-200 dark:border-stone-800 bg-stone-50 dark:bg-stone-950 px-3 py-2 text-xs text-stone-800 dark:text-stone-200 focus:ring-2 focus:ring-amber-500 outline-none">
                            <button type="button" @click="generateCustomBackdrop()" :disabled="isGeneratingBackdrop"
                                    class="px-3.5 py-2 rounded-xl bg-stone-800 hover:bg-stone-700 text-stone-200 text-xs font-bold transition flex items-center gap-1.5 disabled:opacity-50">
                                <span x-show="!isGeneratingBackdrop">Generate</span>
                                <span x-show="isGeneratingBackdrop" class="w-3 h-3 border-2 border-white border-t-transparent rounded-full animate-spin"></span>
                            </button>
                        </div>
                    </div>
                </div>

                {{-- Submit Action Card --}}
                <div class="bg-white dark:bg-stone-900 rounded-3xl p-6 border border-stone-200 dark:border-stone-800 shadow-sm space-y-3">
                    <button type="submit" id="publish-cultural-class-btn"
                            :disabled="isSubmitting || isOverLimit"
                            class="w-full py-4 rounded-2xl bg-gradient-to-r from-amber-500 to-orange-600 hover:from-amber-600 hover:to-orange-700 disabled:opacity-50 text-white font-black text-sm uppercase tracking-wider shadow-xl shadow-amber-500/25 transition-all transform hover:scale-[1.01] flex items-center justify-center gap-2 cursor-pointer">
                        <span x-show="!isSubmitting" class="flex items-center gap-2">
                            <i data-lucide="upload-cloud" class="w-5 h-5"></i>
                            <span>Publish Cultural Class</span>
                        </span>
                        <span x-show="isSubmitting" class="flex items-center gap-2">
                            <span class="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></span>
                            <span>Publishing Cultural Class...</span>
                        </span>
                    </button>
                    <p class="text-[11px] text-center text-stone-400">
                        Your lesson will be published to the Educational Hub with instant search, interactive subtitles, and enrolled learner tracking.
                    </p>
                </div>

            </div>

        </div>
    </form>

</div>
@endsection

@push('scripts')
<script>
    document.addEventListener("DOMContentLoaded", () => {
        if (typeof lucide !== 'undefined') {
            lucide.createIcons();
        }
    });
</script>
@endpush
