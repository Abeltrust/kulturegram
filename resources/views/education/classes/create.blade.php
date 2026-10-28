@extends('layouts.app')

@section('title', 'Teach a Cultural Class - Educational Studio')

@section('content')
@php
    $initialTitle = old('title', '');
    $initialTargetLanguage = old('target_language', 'Hausa');
    $initialCulturalFocus = old('cultural_focus', '');
    $initialPriceType = old('price_type', 'free');
    $initialPriceAmount = old('price', '5.00');
    $initialSampleVideoUrl = old('sample_video_url', '');
    $initialSelectedBg = old('virtual_background_url', '');
    $initialStudioMode = request('mode') === 'live' ? 'live' : 'video';
    $initialLiveMode = request('live') === 'schedule' ? 'schedule' : 'now';
    $reqVideoMode = request('video');
    $initialVideoMode = in_array($reqVideoMode, ['upload', 'record', 'url'], true) ? $reqVideoMode : 'upload';
@endphp
<script>
    window.classStudio = function(config) {
        return {
            classTitle: @json($initialTitle),
            targetLanguage: @json($initialTargetLanguage),
            culturalFocus: @json($initialCulturalFocus),
            priceType: @json($initialPriceType),
            priceAmount: @json($initialPriceAmount),
            sampleVideoUrl: @json($initialSampleVideoUrl),
            selectedBackgroundUrl: @json($initialSelectedBg),
            customBackdropPrompt: '',
            isGeneratingBackdrop: false,
            isSubmitting: false,
            uploadProgress: 0,
            uploadStatusText: '',
            backdropStatusNotice: null,
            isConfigured: config.isConfigured || false,
            maskedKey: config.maskedKey || '',

            studioMode: @json($initialStudioMode), // 'video' | 'live'
            liveMode: @json($initialLiveMode), // 'now' | 'schedule'
            videoMode: @json($initialVideoMode), // 'upload' | 'record' | 'url'
            mediaFile: null,
            tempVideoPath: '',
            videoPreviewUrl: null,
            videoDuration: 0,
            fileSizeMb: null,

            init() {
                if (this.studioMode === 'video' && this.videoMode === 'record') {
                    this.$nextTick(() => {
                        this.startCamera();
                    });
                }
            },

            // Direct in-browser camera recording state
            isCameraActive: false,
            isRecording: false,
            recordingSeconds: 0,
            recordingTimer: null,
            mediaStream: null,
            mediaRecorder: null,
            recordedChunks: [],
            recordedSegments: [],
            speechRecognizer: null,
            cameraError: null,

            clearSelectedFile() {
                this.mediaFile = null;
                this.tempVideoPath = '';
                this.fileSizeMb = null;
                this.videoDuration = 0;
                this.recordedSegments = [];
                if (this.videoPreviewUrl) {
                    try { URL.revokeObjectURL(this.videoPreviewUrl); } catch (e) {}
                    this.videoPreviewUrl = null;
                }
                const input = document.getElementById('video-file-input');
                if (input) input.value = '';
            },

            handleFileSelected(event) {
                if (event.target.files && event.target.files[0]) {
                    const file = event.target.files[0];
                    this.mediaFile = file;
                    this.tempVideoPath = '';
                    this.recordedSegments = [];
                    const mb = file.size / (1024 * 1024);
                    this.fileSizeMb = mb.toFixed(1);

                    if (this.videoPreviewUrl) {
                        try { URL.revokeObjectURL(this.videoPreviewUrl); } catch (e) {}
                    }
                    try {
                        this.videoPreviewUrl = URL.createObjectURL(file);
                    } catch (e) {
                        this.videoPreviewUrl = null;
                    }
                } else {
                    this.clearSelectedFile();
                }
            },

            onPreviewMetadataLoaded(event) {
                const vid = event.target;
                if (vid && vid.duration && isFinite(vid.duration)) {
                    this.videoDuration = Math.round(vid.duration * 10) / 10;
                }
            },

            getYoutubeId(url) {
                if (!url || typeof url !== 'string') return null;
                const match = url.trim().match(/(?:youtube(?:-nocookie)?\.com\/(?:watch\?(?:.*&)?v=|embed\/|shorts\/|live\/|v\/)|youtu\.be\/)([a-zA-Z0-9_-]{11})/i);
                return match ? match[1] : null;
            },

            getYoutubeEmbedUrl(url) {
                const id = this.getYoutubeId(url);
                return id ? 'https://www.youtube.com/embed/' + id + '?rel=0&modestbranding=1' : null;
            },

            formatTimer(sec) {
                const s = Math.floor(sec || 0);
                const m = Math.floor(s / 60);
                const r = s % 60;
                return String(m).padStart(2, '0') + ':' + String(r).padStart(2, '0');
            },

            async startCamera() {
                this.cameraError = null;
                if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
                    this.cameraError = 'Camera access is not supported in this browser.';
                    return;
                }
                try {
                    this.mediaStream = await navigator.mediaDevices.getUserMedia({
                        video: { width: { ideal: 1280 }, height: { ideal: 720 } },
                        audio: true
                    });
                    this.isCameraActive = true;
                    this.$nextTick(() => {
                        const liveVideo = document.getElementById('studio-camera-live');
                        if (liveVideo) {
                            liveVideo.srcObject = this.mediaStream;
                            liveVideo.play().catch(() => {});
                        }
                        if (typeof lucide !== 'undefined') lucide.createIcons();
                    });
                } catch (err) {
                    this.cameraError = 'Could not access camera/microphone: ' + (err.message || 'Permission denied');
                }
            },

            stopCameraStream() {
                if (this.speechRecognizer) {
                    try { this.speechRecognizer.stop(); } catch (e) {}
                    this.speechRecognizer = null;
                }
                if (this.recordingTimer) {
                    clearInterval(this.recordingTimer);
                    this.recordingTimer = null;
                }
                if (this.mediaStream) {
                    this.mediaStream.getTracks().forEach(track => track.stop());
                    this.mediaStream = null;
                }
                this.isCameraActive = false;
                this.isRecording = false;
            },

            async startRecording() {
                if (!this.mediaStream) {
                    await this.startCamera();
                    if (!this.mediaStream) return;
                }

                this.clearSelectedFile();
                this.recordedChunks = [];
                this.recordedSegments = [];
                this.recordingSeconds = 0;

                const mimeTypes = [
                    'video/webm;codecs=vp9,opus',
                    'video/webm;codecs=vp8,opus',
                    'video/webm',
                    'video/mp4'
                ];
                let selectedMime = '';
                for (const mime of mimeTypes) {
                    if (window.MediaRecorder && MediaRecorder.isTypeSupported(mime)) {
                        selectedMime = mime;
                        break;
                    }
                }

                try {
                    this.mediaRecorder = selectedMime
                        ? new MediaRecorder(this.mediaStream, { mimeType: selectedMime })
                        : new MediaRecorder(this.mediaStream);
                } catch (e) {
                    this.cameraError = 'Could not initialize video recorder: ' + e.message;
                    return;
                }

                this.mediaRecorder.ondataavailable = (event) => {
                    if (event.data && event.data.size > 0) {
                        this.recordedChunks.push(event.data);
                    }
                };

                this.mediaRecorder.onstop = () => {
                    const ext = selectedMime.includes('mp4') ? 'mp4' : 'webm';
                    const blob = new Blob(this.recordedChunks, { type: selectedMime || 'video/webm' });
                    const file = new File([blob], `recorded_lesson_${Date.now()}.${ext}`, {
                        type: selectedMime || 'video/webm'
                    });

                    this.mediaFile = file;
                    this.fileSizeMb = (file.size / (1024 * 1024)).toFixed(1);
                    this.videoDuration = this.recordingSeconds;
                    this.videoPreviewUrl = URL.createObjectURL(blob);

                    // Attach to file input if small enough for standard submit
                    try {
                        const dt = new DataTransfer();
                        dt.items.add(file);
                        const input = document.getElementById('video-file-input');
                        if (input) input.files = dt.files;
                    } catch (e) {}

                    this.stopCameraStream();
                };

                // Start live speech recognition while recording so spoken phrases are captured with timestamps
                const SpeechRec = window.SpeechRecognition || window.webkitSpeechRecognition;
                if (SpeechRec) {
                    try {
                        const rec = new SpeechRec();
                        rec.continuous = true;
                        rec.interimResults = false;
                        rec.lang = 'en-US';
                        let lastStart = 0;
                        rec.onresult = (ev) => {
                            for (let i = ev.resultIndex; i < ev.results.length; i++) {
                                if (ev.results[i].isFinal) {
                                    const text = ev.results[i][0].transcript.trim();
                                    if (text) {
                                        const endSec = Math.max(lastStart + 2, this.recordingSeconds);
                                        this.recordedSegments.push({
                                            start: lastStart,
                                            end: endSec,
                                            text: text
                                        });
                                        lastStart = endSec;
                                    }
                                }
                            }
                        };
                        rec.start();
                        this.speechRecognizer = rec;
                    } catch (e) {}
                }

                this.mediaRecorder.start(1000);
                this.isRecording = true;
                this.recordingTimer = setInterval(() => {
                    this.recordingSeconds++;
                }, 1000);
            },

            stopRecording() {
                if (this.mediaRecorder && this.isRecording) {
                    this.mediaRecorder.stop();
                }
            },

            async uploadFileInChunks(file) {
                const chunkSize = 5 * 1024 * 1024; // 5MB per chunk (well below 40MB PHP limit)
                const totalChunks = Math.max(1, Math.ceil(file.size / chunkSize));
                const uploadId = 'cls_' + Date.now() + '_' + Math.random().toString(36).substring(2, 9);
                const ext = (file.name.split('.').pop() || 'mp4').toLowerCase();

                for (let idx = 0; idx < totalChunks; idx++) {
                    const start = idx * chunkSize;
                    const end = Math.min(file.size, start + chunkSize);
                    const chunkBlob = file.slice(start, end);

                    const formData = new FormData();
                    formData.append('chunk', chunkBlob, `chunk_${idx}.${ext}`);
                    formData.append('upload_id', uploadId);
                    formData.append('chunk_index', idx);
                    formData.append('total_chunks', totalChunks);
                    formData.append('extension', ext);

                    const res = await fetch(config.uploadChunkUrl, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': config.csrfToken,
                            'Accept': 'application/json'
                        },
                        body: formData
                    });

                    if (!res.ok) {
                        throw new Error('Chunk upload failed with status ' + res.status);
                    }

                    const data = await res.json();
                    this.uploadProgress = Math.round(((idx + 1) / totalChunks) * 100);
                    this.uploadStatusText = `Uploading video (${this.uploadProgress}%)...`;

                    if (data.completed && data.temp_video_path) {
                        this.tempVideoPath = data.temp_video_path;
                    }
                }
            },

            async handleSubmit(event) {
                if (this.isSubmitting) {
                    event.preventDefault();
                    return false;
                }

                if (!this.classTitle || !this.classTitle.trim()) {
                    event.preventDefault();
                    alert('Please enter a Class Title.');
                    return false;
                }

                if (!this.mediaFile && !this.tempVideoPath && (!this.sampleVideoUrl || !this.sampleVideoUrl.trim())) {
                    event.preventDefault();
                    alert('Please upload a video file, record a video directly on the platform, or provide a video URL.');
                    return false;
                }

                // For files larger than 5MB, upload in 5MB chunks first so PHP post_max_size is never exceeded
                if (this.mediaFile && !this.tempVideoPath && this.mediaFile.size > 5 * 1024 * 1024) {
                    event.preventDefault();
                    this.isSubmitting = true;
                    this.uploadProgress = 1;
                    this.uploadStatusText = 'Uploading video (1%)...';

                    try {
                        await this.uploadFileInChunks(this.mediaFile);
                        this.uploadStatusText = 'Publishing class...';
                        // Clear raw file input so the final form POST only sends the lightweight temp_video_path
                        const input = document.getElementById('video-file-input');
                        if (input) input.value = '';

                        this.$nextTick(() => {
                            event.target.submit();
                        });
                    } catch (err) {
                        console.error('Video upload error:', err);
                        this.isSubmitting = false;
                        this.uploadStatusText = '';
                        alert('Could not upload the video file. Please try again.');
                    }
                    return false;
                }

                setTimeout(() => {
                    this.isSubmitting = true;
                    this.uploadStatusText = 'Publishing class...';
                }, 30);

                return true;
            },

            async generateCustomBackdrop() {
                if (!this.customBackdropPrompt) {
                    alert('Please enter a cultural backdrop prompt (e.g., Traditional Zaria mud relief wall).');
                    return;
                }
                this.isGeneratingBackdrop = true;
                this.backdropStatusNotice = null;

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
                        this.backdropStatusNotice = {
                            type: 'success',
                            text: 'Generated AI cultural studio backdrop and set as active.'
                        };
                    } else {
                        this.backdropStatusNotice = {
                            type: 'error',
                            text: data.error || data.message || 'Could not generate backdrop. Please try another heritage theme.'
                        };
                    }
                } catch (e) {
                    console.error('Error generating studio background:', e);
                    this.backdropStatusNotice = {
                        type: 'error',
                        text: 'Connection error while communicating with Meta Muse Image generator.'
                    };
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

<div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8 sm:py-12"
     x-data="classStudio({
         uploadChunkUrl: '{{ route('education.classes.upload-chunk', [], false) }}',
         generateBackgroundUrl: '{{ route('education.classes.ai-studio-background', [], false) }}',
         csrfToken: '{{ csrf_token() }}',
         isConfigured: {{ ($isConfigured ?? false) ? 'true' : 'false' }},
         maskedKey: '{{ $maskedKey ?? '' }}'
     })">

    {{-- Breadcrumb --}}
    <div class="flex items-center space-x-2 text-xs sm:text-sm text-stone-500 dark:text-stone-400 mb-6">
        <a href="{{ route('education.index') }}" class="hover:text-amber-500 transition">Educational Hub</a>
        <span>/</span>
        <a href="{{ route('education.classes.index') }}" class="hover:text-amber-500 transition">Cultural Classes</a>
        <span>/</span>
        <span class="text-stone-800 dark:text-stone-100 font-semibold">Publish Video Class</span>
    </div>

    {{-- Header Banner --}}
    <div class="bg-gradient-to-r from-stone-900 via-stone-900 to-stone-950 rounded-3xl border border-stone-800 p-6 sm:p-8 mb-8 shadow-xl relative overflow-hidden">
        <div class="absolute -right-10 -bottom-10 w-60 h-60 bg-amber-500/10 rounded-full blur-2xl pointer-events-none"></div>

        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 relative z-10">
            <div>
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-amber-500/10 border border-amber-500/20 text-amber-400 text-xs font-bold mb-2">
                    <i data-lucide="video" class="w-3.5 h-3.5"></i>
                    <span>Cultural Video Studio</span>
                </div>
                <h1 class="text-2xl sm:text-3xl font-black text-white tracking-tight">
                    Publish a Cultural Heritage Class
                </h1>
                <p class="text-stone-400 text-xs sm:text-sm mt-1 max-w-xl">
                    Upload a lesson video or record directly from your camera. Synchronized subtitles and vocabulary are transcribed from your video when viewing.
                </p>
            </div>

            <div class="flex flex-wrap items-center gap-2.5">
                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl border text-xs font-bold shadow-xs"
                      :class="isConfigured ? 'bg-emerald-500/10 border-emerald-500/30 text-emerald-400' : 'bg-amber-500/10 border-amber-500/30 text-amber-400'">
                    <i data-lucide="cpu" class="w-3.5 h-3.5"></i>
                    <span x-text="isConfigured ? 'Meta AI Connected' : 'Offline Mode'"></span>
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

    {{-- Teaching Format Selector: Go Live (/live-stream) or Video Class (Upload / Record) --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4 mb-8">
        <a href="{{ route('live-stream.index', ['mode' => 'now']) }}"
           class="p-4 rounded-2xl border-2 border-stone-200 dark:border-stone-800 bg-white dark:bg-stone-900 hover:border-red-500 dark:hover:border-red-500 hover:bg-red-500/5 text-left transition-all flex flex-col justify-between gap-2 cursor-pointer">
            <div class="flex items-center justify-between">
                <div class="w-9 h-9 rounded-xl bg-red-500/15 text-red-500 flex items-center justify-center">
                    <i data-lucide="radio" class="w-4 h-4"></i>
                </div>
                <span class="px-2 py-0.5 rounded-full bg-red-500 text-white text-[10px] font-black uppercase">Live</span>
            </div>
            <div>
                <div class="text-xs sm:text-sm font-black text-stone-900 dark:text-white">Go Live Instantly</div>
                <p class="text-[11px] text-stone-500 dark:text-stone-400 mt-0.5 line-clamp-1">Start a real-time live stream now</p>
            </div>
        </a>

        <a href="{{ route('live-stream.index', ['mode' => 'schedule']) }}"
           class="p-4 rounded-2xl border-2 border-stone-200 dark:border-stone-800 bg-white dark:bg-stone-900 hover:border-purple-500 dark:hover:border-purple-500 hover:bg-purple-500/5 text-left transition-all flex flex-col justify-between gap-2 cursor-pointer">
            <div class="flex items-center justify-between">
                <div class="w-9 h-9 rounded-xl bg-purple-500/15 text-purple-500 flex items-center justify-center">
                    <i data-lucide="calendar-clock" class="w-4 h-4"></i>
                </div>
                <span class="px-2 py-0.5 rounded-full bg-purple-500/15 text-purple-500 text-[10px] font-black uppercase">Schedule</span>
            </div>
            <div>
                <div class="text-xs sm:text-sm font-black text-stone-900 dark:text-white">Schedule Live Class</div>
                <p class="text-[11px] text-stone-500 dark:text-stone-400 mt-0.5 line-clamp-1">Schedule a future live stream</p>
            </div>
        </a>

        <button type="button"
                @click="stopCameraStream(); videoMode = 'upload'"
                class="p-4 rounded-2xl border-2 text-left transition-all flex flex-col justify-between gap-2 cursor-pointer"
                :class="videoMode === 'upload' ? 'border-amber-500 bg-amber-500/10 shadow-md' : 'border-stone-200 dark:border-stone-800 bg-white dark:bg-stone-900 hover:border-amber-500/50'">
            <div class="flex items-center justify-between">
                <div class="w-9 h-9 rounded-xl bg-amber-500/15 text-amber-500 flex items-center justify-center">
                    <i data-lucide="upload-cloud" class="w-4 h-4"></i>
                </div>
                <span class="px-2 py-0.5 rounded-full bg-amber-500/15 text-amber-500 text-[10px] font-black uppercase">Video</span>
            </div>
            <div>
                <div class="text-xs sm:text-sm font-black text-stone-900 dark:text-white">Upload Video Class</div>
                <p class="text-[11px] text-stone-500 dark:text-stone-400 mt-0.5 line-clamp-1">Upload pre-recorded lesson</p>
            </div>
        </button>

        <button type="button"
                @click="videoMode = 'record'; startCamera()"
                class="p-4 rounded-2xl border-2 text-left transition-all flex flex-col justify-between gap-2 cursor-pointer"
                :class="videoMode === 'record' ? 'border-emerald-500 bg-emerald-500/10 shadow-md' : 'border-stone-200 dark:border-stone-800 bg-white dark:bg-stone-900 hover:border-emerald-500/50'">
            <div class="flex items-center justify-between">
                <div class="w-9 h-9 rounded-xl bg-emerald-500/15 text-emerald-500 flex items-center justify-center">
                    <i data-lucide="camera" class="w-4 h-4"></i>
                </div>
                <span class="px-2 py-0.5 rounded-full bg-emerald-500/15 text-emerald-500 text-[10px] font-black uppercase">Camera</span>
            </div>
            <div>
                <div class="text-xs sm:text-sm font-black text-stone-900 dark:text-white">Record Video</div>
                <p class="text-[11px] text-stone-500 dark:text-stone-400 mt-0.5 line-clamp-1">Record directly in studio</p>
            </div>
        </button>
    </div>

    {{-- Main Video Class Studio Form (Upload File or Record Video Directly) --}}
    <form method="POST" action="{{ route('education.classes.store') }}" enctype="multipart/form-data" @submit="handleSubmit($event)" novalidate class="space-y-8">
        @csrf
        <input type="hidden" name="temp_video_path" :value="tempVideoPath">
        <input type="hidden" name="video_duration" :value="videoDuration">
        <input type="hidden" name="segments_json" :value="recordedSegments.length > 0 ? JSON.stringify(recordedSegments) : ''">

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">

            {{-- Left Column: Video Upload / Direct Camera Recording & Class Info (7 cols) --}}
            <div class="lg:col-span-7 space-y-6">

                {{-- Video Upload or Direct Recording Card --}}
                <div class="bg-white dark:bg-stone-900 rounded-3xl p-6 sm:p-8 border border-stone-200 dark:border-stone-800 shadow-sm space-y-5">
                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <h2 class="text-base sm:text-lg font-black text-stone-800 dark:text-stone-100 flex items-center gap-2">
                            <i data-lucide="film" class="w-5 h-5 text-amber-500"></i>
                            <span>Lesson Video</span>
                        </h2>

                        {{-- Mode Switcher Tabs --}}
                        <div class="inline-flex rounded-xl bg-stone-100 dark:bg-stone-950 p-1 border border-stone-200 dark:border-stone-800">
                            <button type="button"
                                    @click="stopCameraStream(); videoMode = 'upload'"
                                    class="px-3 py-1.5 rounded-lg text-xs font-bold flex items-center gap-1.5 transition cursor-pointer"
                                    :class="videoMode === 'upload' ? 'bg-amber-500 text-white shadow-xs' : 'text-stone-600 dark:text-stone-400 hover:text-stone-900 dark:hover:text-white'">
                                <i data-lucide="upload" class="w-3.5 h-3.5"></i>
                                <span>Upload File</span>
                            </button>
                            <button type="button"
                                    @click="videoMode = 'record'; startCamera()"
                                    class="px-3 py-1.5 rounded-lg text-xs font-bold flex items-center gap-1.5 transition cursor-pointer"
                                    :class="videoMode === 'record' ? 'bg-amber-500 text-white shadow-xs' : 'text-stone-600 dark:text-stone-400 hover:text-stone-900 dark:hover:text-white'">
                                <i data-lucide="camera" class="w-3.5 h-3.5"></i>
                                <span>Record Video</span>
                            </button>
                            <button type="button"
                                    @click="stopCameraStream(); videoMode = 'url'"
                                    class="px-3 py-1.5 rounded-lg text-xs font-bold flex items-center gap-1.5 transition cursor-pointer"
                                    :class="videoMode === 'url' ? 'bg-amber-500 text-white shadow-xs' : 'text-stone-600 dark:text-stone-400 hover:text-stone-900 dark:hover:text-white'">
                                <i data-lucide="link" class="w-3.5 h-3.5"></i>
                                <span>Video URL</span>
                            </button>
                        </div>
                    </div>

                    {{-- Tab 1: Upload Video File --}}
                    <div x-show="videoMode === 'upload'" class="space-y-3">
                        <label class="block text-xs font-black uppercase tracking-wider text-stone-700 dark:text-stone-300">
                            Select Video or Audio File *
                        </label>
                        <input type="file" name="video" id="video-file-input" accept="video/*,audio/*" @change="handleFileSelected($event)"
                               class="w-full text-xs text-stone-500 file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-amber-500 file:text-white hover:file:bg-amber-600 transition cursor-pointer">
                        <div class="flex items-center justify-between text-[11px] text-stone-400">
                            <span>Supports MP4, WebM, MOV, MP3, WAV (up to 100MB via chunked upload).</span>
                            <span x-show="fileSizeMb" class="font-bold text-amber-500">
                                Selected: <span x-text="fileSizeMb"></span> MB
                            </span>
                        </div>
                    </div>

                    {{-- Tab 2: Record Video Directly on Platform --}}
                    <div x-show="videoMode === 'record'" class="space-y-4">
                        <div x-show="cameraError" class="p-3 rounded-xl bg-red-500/10 border border-red-500/30 text-red-400 text-xs flex items-center gap-2">
                            <i data-lucide="alert-circle" class="w-4 h-4 shrink-0"></i>
                            <span x-text="cameraError"></span>
                        </div>

                        <div x-show="isCameraActive" class="relative rounded-2xl overflow-hidden bg-stone-950 border border-stone-800 aspect-video">
                            <video id="studio-camera-live" muted playsinline class="w-full h-full object-contain"></video>

                            <div x-show="isRecording" class="absolute top-3 left-3 px-3 py-1 rounded-full bg-red-600 text-white text-xs font-bold flex items-center gap-2 shadow-lg">
                                <span class="w-2 h-2 rounded-full bg-white animate-ping"></span>
                                <span>REC</span>
                                <span class="font-mono" x-text="formatTimer(recordingSeconds)"></span>
                            </div>
                        </div>

                        <div class="flex flex-wrap items-center gap-3">
                            <button type="button"
                                    x-show="!isRecording"
                                    @click="startRecording()"
                                    class="px-4 py-2.5 rounded-xl bg-red-600 hover:bg-red-700 text-white text-xs font-bold flex items-center gap-2 shadow-md transition cursor-pointer">
                                <i data-lucide="circle-dot" class="w-4 h-4"></i>
                                <span>Start Recording</span>
                            </button>

                            <button type="button"
                                    x-show="isRecording"
                                    @click="stopRecording()"
                                    class="px-4 py-2.5 rounded-xl bg-stone-800 hover:bg-stone-700 text-white text-xs font-bold flex items-center gap-2 shadow-md transition cursor-pointer">
                                <i data-lucide="square" class="w-4 h-4"></i>
                                <span>Stop & Save Recording</span>
                            </button>

                            <button type="button"
                                    x-show="!isCameraActive && !isRecording"
                                    @click="startCamera()"
                                    class="px-4 py-2.5 rounded-xl border border-stone-300 dark:border-stone-700 text-stone-700 dark:text-stone-200 text-xs font-bold flex items-center gap-2 transition cursor-pointer">
                                <i data-lucide="camera" class="w-4 h-4"></i>
                                <span>Open Camera</span>
                            </button>
                        </div>
                    </div>

                    {{-- Tab 3: External Video URL --}}
                    <div x-show="videoMode === 'url'" class="space-y-3">
                        <div>
                            <label class="block text-xs font-black uppercase tracking-wider text-stone-700 dark:text-stone-300 mb-1.5">
                                YouTube or External Video URL *
                            </label>
                            <input type="text" name="sample_video_url" x-model="sampleVideoUrl"
                                   placeholder="https://www.youtube.com/watch?v=... or https://example.com/cultural_lesson.mp4"
                                   class="w-full rounded-xl border border-stone-200 dark:border-stone-800 bg-stone-50 dark:bg-stone-950 px-4 py-2.5 text-xs sm:text-sm text-stone-800 dark:text-stone-100 focus:ring-2 focus:ring-amber-500 outline-none">
                            <p class="text-[11px] text-stone-400 mt-1">
                                Supports YouTube links (watch, youtu.be, shorts, embed) and direct MP4/WebM video URLs.
                            </p>
                        </div>

                        {{-- Live Preview for External / YouTube Video URL --}}
                        <div x-show="sampleVideoUrl && sampleVideoUrl.trim().length > 5" x-transition class="space-y-2.5 pt-1">
                            <div class="flex items-center justify-between text-xs p-2.5 bg-stone-100 dark:bg-stone-800/80 rounded-xl border border-stone-200 dark:border-stone-700">
                                <div class="flex items-center gap-2 text-stone-700 dark:text-stone-200 truncate">
                                    <i data-lucide="play-circle" class="w-4 h-4 text-amber-500 shrink-0"></i>
                                    <span class="font-semibold truncate" x-text="getYoutubeId(sampleVideoUrl) ? 'YouTube Video Preview (' + getYoutubeId(sampleVideoUrl) + ')' : 'External Video Link Preview'"></span>
                                </div>
                                <button type="button" @click="sampleVideoUrl = ''" class="text-xs text-red-500 hover:text-red-700 dark:text-red-400 font-bold px-2 py-1 rounded-lg hover:bg-red-50 dark:hover:bg-red-950/40 transition shrink-0 flex items-center gap-1 cursor-pointer">
                                    <i data-lucide="x" class="w-3.5 h-3.5"></i>
                                    <span>Clear</span>
                                </button>
                            </div>

                            <div class="rounded-2xl overflow-hidden bg-black border border-stone-800 aspect-video relative shadow-inner">
                                <template x-if="getYoutubeEmbedUrl(sampleVideoUrl)">
                                    <iframe :src="getYoutubeEmbedUrl(sampleVideoUrl)"
                                            class="w-full h-full"
                                            frameborder="0"
                                            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                                            allowfullscreen></iframe>
                                </template>
                                <template x-if="sampleVideoUrl && sampleVideoUrl.trim().length > 5 && !getYoutubeEmbedUrl(sampleVideoUrl)">
                                    <video :src="sampleVideoUrl.trim()"
                                           controls
                                           @loadedmetadata="onPreviewMetadataLoaded($event)"
                                           class="w-full h-full object-contain"></video>
                                </template>
                            </div>
                        </div>
                    </div>

                    {{-- Selected / Recorded Video Preview & Pill --}}
                    <div x-show="mediaFile" x-transition class="space-y-3 pt-2">
                        <div class="flex items-center justify-between text-xs p-2.5 bg-stone-100 dark:bg-stone-800/80 rounded-xl border border-stone-200 dark:border-stone-700">
                            <div class="flex items-center gap-2 text-stone-700 dark:text-stone-200 truncate">
                                <i data-lucide="file-video" class="w-4 h-4 text-amber-500 shrink-0"></i>
                                <span class="font-semibold truncate" x-text="mediaFile ? mediaFile.name : ''"></span>
                                <span class="text-stone-400 text-[11px]" x-text="'(' + fileSizeMb + ' MB)'"></span>
                            </div>
                            <button type="button" @click="clearSelectedFile()" class="text-xs text-red-500 hover:text-red-700 dark:text-red-400 font-bold px-2 py-1 rounded-lg hover:bg-red-50 dark:hover:bg-red-950/40 transition shrink-0 flex items-center gap-1 cursor-pointer">
                                <i data-lucide="x" class="w-3.5 h-3.5"></i>
                                <span>Remove</span>
                            </button>
                        </div>

                        <div x-show="videoPreviewUrl" class="rounded-2xl overflow-hidden bg-black border border-stone-800 aspect-video relative">
                            <video id="studio-preview-video"
                                   :src="videoPreviewUrl"
                                   controls
                                   @loadedmetadata="onPreviewMetadataLoaded($event)"
                                   class="w-full h-full object-contain"></video>
                        </div>
                    </div>

                    @error('video')
                        <p class="text-red-500 text-xs mt-1.5">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Basic Class Information Card --}}
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
                               placeholder="Enter your lesson title (e.g. Hausa Greetings)"
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
                        </div>

                        <div>
                            <label class="block text-xs font-black uppercase tracking-wider text-stone-700 dark:text-stone-300 mb-1.5">
                                Cultural Focus (Optional)
                            </label>
                            <input type="text" name="cultural_focus" x-model="culturalFocus"
                                   placeholder="e.g. Conversational Greetings"
                                   class="w-full rounded-xl border border-stone-200 dark:border-stone-800 bg-stone-50 dark:bg-stone-950 px-4 py-3 text-sm text-stone-800 dark:text-stone-100 focus:ring-2 focus:ring-amber-500 outline-none">
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
                            Lesson Description (Optional)
                        </label>
                        <textarea name="description" rows="2"
                                  placeholder="Briefly describe what learners will learn in this video..."
                                  class="w-full rounded-xl border border-stone-200 dark:border-stone-800 bg-stone-50 dark:bg-stone-950 p-3.5 text-sm text-stone-800 dark:text-stone-100 focus:ring-2 focus:ring-amber-500 outline-none leading-relaxed">{{ old('description') }}</textarea>
                    </div>
                </div>

            </div>

            {{-- Right Column: Pricing, Backdrop & Publish Action (5 cols) --}}
            <div class="lg:col-span-5 space-y-6">

                {{-- Access & Pricing Card --}}
                <div class="bg-white dark:bg-stone-900 rounded-3xl p-6 sm:p-7 border border-stone-200 dark:border-stone-800 shadow-sm space-y-4">
                    <h2 class="text-sm sm:text-base font-black text-stone-800 dark:text-stone-100 flex items-center gap-2">
                        <i data-lucide="tag" class="w-4 h-4 text-amber-500"></i>
                        <span>Access & Monetization</span>
                    </h2>

                    <div class="grid grid-cols-1 gap-3">
                        <label class="relative flex flex-col p-3.5 rounded-2xl border-2 cursor-pointer transition-all"
                               :class="priceType === 'free' ? 'border-emerald-500 bg-emerald-500/5' : 'border-stone-200 dark:border-stone-800 hover:border-stone-300'">
                            <div class="flex items-center justify-between mb-1">
                                <span class="font-black text-xs sm:text-sm text-stone-800 dark:text-stone-100">Free Heritage Class</span>
                                <input type="radio" name="price_type" value="free" x-model="priceType" :checked="priceType === 'free'" checked class="text-emerald-500 focus:ring-emerald-500">
                            </div>
                            <p class="text-[11px] text-stone-500 dark:text-stone-400">
                                Open to all community members and learners.
                            </p>
                        </label>

                        <label class="relative flex flex-col p-3.5 rounded-2xl border-2 cursor-pointer transition-all"
                               :class="priceType === 'paid' ? 'border-amber-500 bg-amber-500/5' : 'border-stone-200 dark:border-stone-800 hover:border-stone-300'">
                            <div class="flex items-center justify-between mb-1">
                                <span class="font-black text-xs sm:text-sm text-stone-800 dark:text-stone-100">Paid Masterclass</span>
                                <input type="radio" name="price_type" value="paid" x-model="priceType" :checked="priceType === 'paid'" class="text-amber-500 focus:ring-amber-500">
                            </div>
                            <p class="text-[11px] text-stone-500 dark:text-stone-400">
                                Learners purchase access. 45-second preview is shown for free.
                            </p>
                        </label>
                    </div>

                    <div x-show="priceType === 'paid'" x-transition class="pt-2">
                        <label class="block text-xs font-black uppercase tracking-wider text-stone-700 dark:text-stone-300 mb-1.5">
                            Enrollment Price (USD)
                        </label>
                        <div class="relative">
                            <span class="absolute left-3.5 top-2.5 text-stone-400 font-bold">$</span>
                            <input type="number" step="0.01" min="0" name="price" x-model="priceAmount" :disabled="priceType !== 'paid'"
                                    placeholder="9.99"
                                    class="w-full rounded-xl border border-stone-200 dark:border-stone-800 bg-stone-50 dark:bg-stone-950 pl-8 pr-4 py-2 text-sm text-stone-800 dark:text-stone-100 focus:ring-2 focus:ring-amber-500 outline-none font-bold">
                        </div>
                    </div>

                    <div class="pt-3 border-t border-stone-100 dark:border-stone-800">
                        <label class="flex items-start gap-2.5 cursor-pointer select-none">
                            <input type="checkbox" name="share_to_timeline" value="1" checked
                                   class="rounded text-amber-500 focus:ring-amber-500 mt-0.5">
                            <span class="text-xs font-semibold text-stone-700 dark:text-stone-300">
                                Share class preview to my Timeline feed
                            </span>
                        </label>
                    </div>
                </div>

                {{-- Submit Action Card --}}
                <div class="bg-white dark:bg-stone-900 rounded-3xl p-6 border border-stone-200 dark:border-stone-800 shadow-sm space-y-3">
                    <div x-show="isSubmitting && uploadProgress > 0" class="space-y-1.5">
                        <div class="flex items-center justify-between text-xs font-bold text-amber-500">
                            <span x-text="uploadStatusText"></span>
                            <span x-text="uploadProgress + '%'"></span>
                        </div>
                        <div class="w-full bg-stone-200 dark:bg-stone-800 rounded-full h-2 overflow-hidden">
                            <div class="bg-amber-500 h-2 transition-all duration-150" :style="'width: ' + uploadProgress + '%'"></div>
                        </div>
                    </div>

                    <button type="submit" id="publish-cultural-class-btn"
                            :disabled="isSubmitting"
                            class="w-full py-4 rounded-2xl bg-gradient-to-r from-amber-500 to-orange-600 hover:from-amber-600 hover:to-orange-700 disabled:opacity-50 text-white font-black text-sm uppercase tracking-wider shadow-xl shadow-amber-500/25 transition-all transform hover:scale-[1.01] flex items-center justify-center gap-2 cursor-pointer">
                        <span x-show="!isSubmitting" class="flex items-center gap-2">
                            <i data-lucide="upload-cloud" class="w-5 h-5"></i>
                            <span>Publish Cultural Class</span>
                        </span>
                        <span x-show="isSubmitting" class="flex items-center gap-2">
                            <span class="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></span>
                            <span x-text="uploadStatusText || 'Publishing Cultural Class...'"></span>
                        </span>
                    </button>
                    <p class="text-[11px] text-center text-stone-400">
                        Subtitles and bilingual vocabulary are transcribed from your video when viewing the lesson.
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
