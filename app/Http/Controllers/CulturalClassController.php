<?php

namespace App\Http\Controllers;

use App\Models\CulturalClass;
use App\Models\CulturalClassEnrollment;
use App\Models\CulturalClassDiscussion;
use App\Models\Post;
use App\Services\MetaAI\CulturalClassTranscriptionService;
use App\Services\MetaAI\CulturalVirtualStudioService;
use App\Services\MetaAI\MetaModelApiClient;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CulturalClassController extends Controller
{
    protected CulturalClassTranscriptionService $transcriptionService;
    protected CulturalVirtualStudioService $studioService;
    protected MetaModelApiClient $apiClient;

    public function __construct(
        CulturalClassTranscriptionService $transcriptionService,
        CulturalVirtualStudioService $studioService,
        MetaModelApiClient $apiClient
    ) {
        $this->transcriptionService = $transcriptionService;
        $this->studioService = $studioService;
        $this->apiClient = $apiClient;
    }

    /**
     * Display all cultural classes in the Educational Hub.
     */
    public function index(Request $request)
    {
        $query = CulturalClass::with('instructor', 'enrollments')
            ->published()
            ->latest();

        if ($request->filled('language') && $request->language !== 'all') {
            $query->byLanguage($request->language);
        }

        if ($request->filled('price_type') && $request->price_type !== 'all') {
            if ($request->price_type === 'free') {
                $query->free();
            } elseif ($request->price_type === 'paid') {
                $query->paid();
            }
        }

        if ($request->filled('level') && $request->level !== 'all') {
            $query->where('level', $request->level);
        }

        if ($request->filled('search')) {
            $term = $request->search;
            $query->where(function ($q) use ($term) {
                $q->where('title', 'like', "%{$term}%")
                  ->orWhere('description', 'like', "%{$term}%")
                  ->orWhere('cultural_focus', 'like', "%{$term}%")
                  ->orWhere('target_language', 'like', "%{$term}%");
            });
        }

        $classes = $query->paginate(12)->withQueryString();
        $presets = $this->studioService->getCuratedPresets();

        return view('education.classes.index', compact('classes', 'presets'));
    }

    /**
     * Show form to create a new cultural video class.
     */
    public function create()
    {
        $presets = $this->studioService->getCuratedPresets();
        $commonLanguages = ['Hausa', 'Yoruba', 'Igbo', 'Swahili', 'Fulani', 'Twi', 'Amharic', 'Zulu', 'Edo', 'Ibibio'];
        $isConfigured = $this->apiClient->isConfigured();
        $maskedKey = $this->apiClient->getMaskedKey();

        return view('education.classes.create', compact('presets', 'commonLanguages', 'isConfigured', 'maskedKey'));
    }

    /**
     * Chunked video upload endpoint so large video files or recordings never exceed PHP post_max_size.
     */
    public function uploadChunk(Request $request): JsonResponse
    {
        $request->validate([
            'chunk' => 'required|file|max:20480',
            'upload_id' => 'required|string|max:100',
            'chunk_index' => 'required|integer|min:0',
            'total_chunks' => 'required|integer|min:1',
            'extension' => 'nullable|string|max:15',
        ]);

        $safeId = preg_replace('/[^a-zA-Z0-9_-]/', '', $request->upload_id);
        $ext = preg_replace('/[^a-zA-Z0-9]/', '', strtolower($request->extension ?: 'mp4')) ?: 'mp4';

        $tempDir = 'cultural_classes/temp';
        Storage::disk('public')->makeDirectory($tempDir);
        $relPath = "{$tempDir}/{$safeId}.{$ext}";
        $absPath = Storage::disk('public')->path($relPath);

        if ((int) $request->chunk_index === 0 && file_exists($absPath)) {
            @unlink($absPath);
        }

        $chunkContent = file_get_contents($request->file('chunk')->getRealPath());
        file_put_contents($absPath, $chunkContent, FILE_APPEND);

        $isComplete = ((int) $request->chunk_index + 1) >= (int) $request->total_chunks;

        return response()->json([
            'success' => true,
            'completed' => $isComplete,
            'temp_video_path' => $isComplete ? $relPath : null,
            'size_bytes' => file_exists($absPath) ? filesize($absPath) : 0,
        ]);
    }

    /**
     * Store a newly created cultural class. Transcription is performed when viewing the video.
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'cultural_focus' => 'nullable|string|max:255',
            'target_language' => 'required|string|max:100',
            'source_language' => 'nullable|string|max:100',
            'level' => 'nullable|string|in:all_levels,beginner,intermediate,advanced',
            'price_type' => 'nullable|string|in:free,paid',
            'price' => 'nullable|numeric|min:0',
            'description' => 'nullable|string|max:5000',
            'video' => 'nullable|file|max:102400',
            'temp_video_path' => 'nullable|string|max:500',
            'sample_video_url' => 'nullable|string|max:500',
            'thumbnail' => 'nullable|image|max:10240',
            'virtual_background_url' => 'nullable|string|max:500',
            'raw_transcript' => 'nullable|string|max:15000',
            'segments_json' => 'nullable|string',
            'video_duration' => 'nullable|numeric|min:0',
            'share_to_timeline' => 'nullable|boolean',
        ]);

        $targetLang = trim((string) ($request->target_language ?: 'Hausa'));
        $title = trim((string) $request->title);
        $culturalFocus = trim((string) ($request->cultural_focus ?: $title));
        $level = $request->level ?: 'all_levels';
        $priceType = $request->price_type ?: 'free';

        $videoPath = null;
        $thumbnailPath = null;

        // Handle video upload, chunked temp video, or external URL
        if ($request->hasFile('video') && $request->file('video')->isValid()) {
            $videoPath = $request->file('video')->store('cultural_classes/videos', 'public');
        } elseif ($request->filled('temp_video_path') && Storage::disk('public')->exists($request->temp_video_path)) {
            $ext = pathinfo($request->temp_video_path, PATHINFO_EXTENSION) ?: 'mp4';
            $newRelativePath = 'cultural_classes/videos/' . Str::random(40) . '.' . $ext;
            Storage::disk('public')->move($request->temp_video_path, $newRelativePath);
            $videoPath = $newRelativePath;
        } elseif ($request->filled('sample_video_url')) {
            $videoPath = trim((string) $request->sample_video_url);
        }

        if (!$videoPath && !$request->filled('raw_transcript')) {
            return back()
                ->withInput()
                ->withErrors(['video' => 'Please select a video file, record a video on the platform, or enter a video URL before publishing.']);
        }

        if ($request->hasFile('thumbnail') && $request->file('thumbnail')->isValid()) {
            $thumbnailPath = $request->file('thumbnail')->store('cultural_classes/thumbnails', 'public');
        } elseif ($request->filled('virtual_background_url')) {
            $thumbnailPath = $request->virtual_background_url;
        } elseif ($videoPath && ($ytId = CulturalClass::extractYoutubeId($videoPath))) {
            $thumbnailPath = "https://img.youtube.com/vi/{$ytId}/hqdefault.jpg";
        }

        $videoDuration = $request->filled('video_duration') && (float) $request->video_duration > 0
            ? (float) $request->video_duration
            : 0;
        $preSegments = $request->filled('segments_json') ? json_decode($request->segments_json, true) : null;

        // Only structure transcript if explicitly provided (e.g. live camera recording speech, text script, or YouTube captions).
        // For uploaded local videos, transcription happens when viewing the video so dummy data is never generated.
        $aiData = [];
        $extractedFromYoutube = false;
        if (!$request->filled('raw_transcript') && empty($preSegments) && $videoPath && CulturalClass::extractYoutubeId($videoPath)) {
            $ytExtracted = $this->transcriptionService->extractYoutubeTranscript($videoPath, $targetLang);
            if ($ytExtracted && !empty($ytExtracted['segments'])) {
                $preSegments = $ytExtracted['segments'];
                $extractedFromYoutube = true;
                if ($videoDuration <= 0 && !empty($ytExtracted['duration_seconds'])) {
                    $videoDuration = (float) $ytExtracted['duration_seconds'];
                }
            }
        }

        if ($request->filled('raw_transcript') || (!empty($preSegments) && is_array($preSegments))) {
            $rawText = $request->raw_transcript ?: implode(' ', array_map(fn($s) => trim((string) ($s['text'] ?? '')), (array) $preSegments));
            $aiData = $extractedFromYoutube
                ? $this->transcriptionService->generateHeuristicBilingualStructure(
                    $rawText,
                    $targetLang,
                    $culturalFocus,
                    is_array($preSegments) ? $preSegments : null,
                    $videoDuration > 0 ? $videoDuration : null
                )
                : $this->transcriptionService->structureBilingualLesson(
                    $rawText,
                    $targetLang,
                    $culturalFocus,
                    is_array($preSegments) ? $preSegments : null,
                    $videoDuration > 0 ? $videoDuration : null
                );
        }

        $price = $priceType === 'paid' ? (float) ($request->price ?: 5.00) : 0.00;

        $class = CulturalClass::create([
            'instructor_id' => auth()->id(),
            'title' => $title,
            'description' => $request->description ?: ($aiData['summary'] ?? null),
            'cultural_focus' => $culturalFocus,
            'target_language' => $targetLang,
            'source_language' => $request->source_language ?: 'English',
            'level' => $level,
            'price_type' => $priceType,
            'price' => $price,
            'video_url' => $videoPath,
            'preview_video_url' => $videoPath,
            'thumbnail_url' => $thumbnailPath,
            'virtual_background_url' => $request->virtual_background_url,
            'duration_seconds' => $aiData['duration_seconds'] ?? ($videoDuration > 0 ? (int) ceil($videoDuration) : 0),
            'transcript' => $aiData['transcript'] ?? ($request->raw_transcript ?: null),
            'subtitles_vtt' => $aiData['subtitles_vtt'] ?? null,
            'timed_subtitles' => $aiData['subtitles'] ?? [],
            'bilingual_vocabulary' => $aiData['vocabulary'] ?? [],
            'ai_summary' => $aiData['summary'] ?? $request->description,
            'ai_enhancements' => !empty($aiData) ? [
                'key_takeaways' => $aiData['key_takeaways'] ?? [],
                'vocabulary_count' => count($aiData['vocabulary'] ?? []),
                'subtitles_count' => count($aiData['subtitles'] ?? []),
            ] : [],
            'is_published' => true,
        ]);

        // Cross-post teaser card to user's Timeline if requested
        if ($request->boolean('share_to_timeline')) {
            try {
                $teaserText = "Published a new Cultural Class in the Educational Hub:\n\n"
                            . "\"{$class->title}\" ({$class->target_language})\n"
                            . ($class->description ? "{$class->description}\n\n" : "\n")
                            . "Watch and learn in the Educational Hub.";

                $timelinePost = Post::create([
                    'user_id' => auth()->id(),
                    'content' => $teaserText,
                    'image' => $class->thumbnail_url,
                    'video' => $class->video_url,
                    'privacy' => 'public',
                    'type' => $class->video_url ? 'video' : ($class->thumbnail_url ? 'image' : 'text'),
                    'chapter' => 'Cultural Class',
                    'tags' => ['education', 'cultural-class', strtolower($targetLang), 'living-heritage'],
                ]);

                $class->update([
                    'shared_to_timeline' => true,
                    'timeline_post_id' => $timelinePost->id,
                ]);
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning("CulturalClassController::store timeline teaser skipped: " . $e->getMessage());
            }
        }

        return redirect()->route('education.classes.show', $class->slug)
            ->with('success', 'Your cultural class video has been published.');
    }

    /**
     * Interactive Class Watch & Learning Player.
     */
    public function show(string $slug)
    {
        $class = CulturalClass::with(['instructor', 'discussions.user', 'discussions.replies.user'])
            ->where('slug', $slug)
            ->firstOrFail();

        $class->increment('views_count');

        $user = auth()->user();
        $isEnrolled = $class->isEnrolledBy($user);
        $isInstructor = $user && $user->id === $class->instructor_id;
        $canWatchFull = $class->isFree() || $isEnrolled || $isInstructor;

        // Related classes from the same language or instructor
        $relatedClasses = CulturalClass::published()
            ->where('id', '!=', $class->id)
            ->where(function ($q) use ($class) {
                $q->where('target_language', $class->target_language)
                  ->orWhere('instructor_id', $class->instructor_id);
            })
            ->take(3)
            ->get();

        return view('education.classes.show', compact('class', 'isEnrolled', 'isInstructor', 'canWatchFull', 'relatedClasses'));
    }

    /**
     * Enroll in a free or paid class.
     */
    public function enroll(Request $request, CulturalClass $class)
    {
        $user = auth()->user();

        if ($class->isEnrolledBy($user)) {
            if ($request->wantsJson()) {
                return response()->json(['success' => true, 'message' => 'Already enrolled!']);
            }
            return back()->with('info', 'You are already enrolled in this class.');
        }

        CulturalClassEnrollment::create([
            'cultural_class_id' => $class->id,
            'user_id' => $user->id,
            'price_paid' => $class->isFree() ? 0.00 : $class->price,
            'access_status' => 'active',
            'progress_seconds' => 0,
            'last_watched_at' => now(),
        ]);

        $class->increment('enrollments_count');

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Successfully enrolled! You now have full access to this cultural class.',
            ]);
        }

        return back()->with('success', 'Welcome to the class! You have full access to all lessons, interactive subtitles, and vocabulary guides.');
    }

    /**
     * Serve WebVTT subtitles directly.
     */
    public function subtitlesVtt(CulturalClass $class)
    {
        $vtt = $class->subtitles_vtt;
        if (empty($vtt)) {
            $vtt = $this->transcriptionService->generateWebVtt($class->timed_subtitles ?: []);
        }

        return response($vtt, 200, [
            'Content-Type' => 'text/vtt; charset=utf-8',
            'Access-Control-Allow-Origin' => '*',
        ]);
    }

    /**
     * AJAX trigger to test or generate AI transcription & bilingual vocabulary during creation.
     */
    public function transcribeAi(Request $request): JsonResponse
    {
        $request->validate([
            'transcript' => 'nullable|string|max:25000',
            'target_language' => 'nullable|string|max:100',
            'cultural_focus' => 'nullable|string|max:255',
            'title' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:5000',
            'segments_json' => 'nullable|string',
            'video_duration' => 'nullable|numeric|min:0',
            'video_frames_json' => 'nullable|string',
            'media' => 'nullable|file|max:102400',
        ]);

        $lang = $request->target_language ?: 'Hausa';
        $focus = $request->cultural_focus ?: "{$lang} Cultural Traditions";
        $preSegments = $request->filled('segments_json') ? json_decode($request->segments_json, true) : null;
        $videoFrames = $request->filled('video_frames_json') ? json_decode($request->video_frames_json, true) : null;
        $videoDuration = $request->filled('video_duration') ? (float) $request->video_duration : null;

        if ($request->hasFile('media') && $request->file('media')->isValid() && empty($request->transcript) && empty($preSegments)) {
            $tempPath = $request->file('media')->store('cultural_classes/temp', 'public');
            $res = $this->transcriptionService->processMedia(
                $tempPath,
                $lang,
                $focus,
                $request->title,
                $request->description
            );
            $res['temp_video_path'] = $tempPath;
        } else {
            $text = $request->transcript;
            if (empty($text) && !empty($preSegments) && is_array($preSegments)) {
                $text = implode(' ', array_map(fn($s) => trim((string) ($s['text'] ?? '')), $preSegments));
            }
            if (empty($text)) {
                $text = $this->transcriptionService->buildDefaultLessonTranscript(
                    $lang,
                    $focus,
                    $request->title,
                    $request->description
                );
            }
            $res = $this->transcriptionService->structureBilingualLesson(
                $text,
                $lang,
                $focus,
                is_array($preSegments) ? $preSegments : null,
                $videoDuration,
                is_array($videoFrames) ? $videoFrames : null
            );
        }

        return response()->json($res);
    }

    /**
     * Extract and return a lightweight 16kHz mono WAV for fast browser speech recognition when viewing the video.
     */
    public function audioWav(CulturalClass $class): JsonResponse
    {
        if (!$class->video_url || Str::startsWith($class->video_url, ['http://', 'https://'])) {
            return response()->json([
                'success' => false,
                'message' => 'No local video file available for server WAV extraction.',
            ]);
        }

        $extracted = $this->transcriptionService->extractWavFromVideo($class->video_url, $class->id);
        if (!$extracted) {
            return response()->json([
                'success' => false,
                'message' => 'Could not extract WAV on server; falling back to direct video decode.',
            ]);
        }

        if (!empty($extracted['duration_seconds']) && (int) $class->duration_seconds !== (int) $extracted['duration_seconds']) {
            $class->update(['duration_seconds' => (int) $extracted['duration_seconds']]);
        }

        return response()->json([
            'success' => true,
            'wav_url' => $extracted['wav_url'],
            'duration_seconds' => $extracted['duration_seconds'],
        ]);
    }

    /**
     * AJAX endpoint to transcribe video audio when viewing or update synchronized subtitles on a published class.
     */
    public function syncSubtitles(Request $request, CulturalClass $class): JsonResponse
    {
        $hasExistingSubtitles = !empty($class->timed_subtitles) && is_array($class->timed_subtitles);
        if ($hasExistingSubtitles && auth()->id() !== $class->instructor_id) {
            return response()->json(['success' => false, 'message' => 'Only the class instructor can edit existing subtitles.'], 403);
        }

        $request->validate([
            'transcript' => 'nullable|string|max:25000',
            'subtitles' => 'nullable|array',
            'subtitles_json' => 'nullable|string',
            'vocabulary_json' => 'nullable|string',
            'segments_json' => 'nullable|string',
            'video_duration' => 'nullable|numeric|min:0',
            'video_frames_json' => 'nullable|string',
            'regenerate_with_ai' => 'nullable|boolean',
            'fetch_external_transcript' => 'nullable|boolean',
        ]);

        $incomingSubtitles = $request->input('subtitles');
        if (!is_array($incomingSubtitles) && $request->filled('subtitles_json')) {
            $incomingSubtitles = json_decode($request->subtitles_json, true);
        }

        $preSegments = $request->filled('segments_json')
            ? json_decode($request->segments_json, true)
            : (is_array($incomingSubtitles) && !empty($incomingSubtitles) ? $incomingSubtitles : null);
        $videoFrames = $request->filled('video_frames_json') ? json_decode($request->video_frames_json, true) : null;
        $videoDuration = $request->filled('video_duration') && (float) $request->video_duration > 0
            ? (float) $request->video_duration
            : (float) ($class->duration_seconds ?: 60);

        $transcript = trim((string) ($request->transcript ?? $class->transcript ?? ''));

        // If this class uses a YouTube link and we are fetching/initializing subtitles, extract directly from YouTube
        if ((empty($preSegments) || $request->boolean('fetch_external_transcript')) && $class->youtubeVideoId()) {
            $ytExtracted = $this->transcriptionService->extractYoutubeTranscript($class->video_url, $class->target_language);
            if ($ytExtracted && !empty($ytExtracted['segments'])) {
                $preSegments = $ytExtracted['segments'];
                $incomingSubtitles = $ytExtracted['segments'];
                $transcript = $ytExtracted['transcript'] ?: $transcript;
                if (!empty($ytExtracted['duration_seconds'])) {
                    $videoDuration = (float) $ytExtracted['duration_seconds'];
                }
            }
        }

        if ($transcript === '' && !empty($preSegments) && is_array($preSegments)) {
            $transcript = implode(' ', array_map(fn($s) => trim((string) ($s['text'] ?? '')), $preSegments));
        }

        $keyTakeaways = $class->ai_enhancements['key_takeaways'] ?? [];

        if ($request->boolean('regenerate_with_ai') || empty($incomingSubtitles)) {
            $aiData = $this->transcriptionService->structureBilingualLesson(
                $transcript ?: ($class->transcript ?: $class->title),
                $class->target_language,
                $class->cultural_focus,
                is_array($preSegments) ? $preSegments : null,
                $videoDuration,
                is_array($videoFrames) ? $videoFrames : null
            );

            $subtitles = $aiData['subtitles'] ?? [];
            $vocabulary = $aiData['vocabulary'] ?? [];
            $vtt = $aiData['subtitles_vtt'] ?? $this->transcriptionService->generateWebVtt($subtitles);
            $summary = $aiData['summary'] ?? $class->ai_summary;
            $keyTakeaways = $aiData['key_takeaways'] ?? [];
            $durationSec = $aiData['duration_seconds'] ?? (int) ceil($videoDuration);
        } else {
            $subtitles = is_array($incomingSubtitles) ? $incomingSubtitles : [];
            $vocabulary = $request->filled('vocabulary_json')
                ? (json_decode($request->vocabulary_json, true) ?: [])
                : ($class->bilingual_vocabulary ?: []);
            $vtt = $this->transcriptionService->generateWebVtt($subtitles);
            $summary = $class->ai_summary;
            $durationSec = (int) ceil($videoDuration ?: (end($subtitles)['end'] ?? 60));
            if ($transcript === '' && !empty($subtitles)) {
                $transcript = implode(' ', array_map(fn($s) => trim((string) ($s['text'] ?? '')), $subtitles));
            }
        }

        $class->update([
            'transcript' => $transcript,
            'timed_subtitles' => $subtitles,
            'bilingual_vocabulary' => $vocabulary,
            'subtitles_vtt' => $vtt,
            'ai_summary' => $summary,
            'duration_seconds' => $durationSec,
            'ai_enhancements' => array_merge($class->ai_enhancements ?: [], [
                'key_takeaways' => $keyTakeaways,
                'vocabulary_count' => count($vocabulary),
                'subtitles_count' => count($subtitles),
            ]),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Subtitles and bilingual vocabulary synchronized with video.',
            'transcript' => $class->transcript,
            'subtitles' => $class->timed_subtitles,
            'timed_subtitles' => $class->timed_subtitles,
            'vocabulary' => $class->bilingual_vocabulary,
            'ai_summary' => $class->ai_summary,
            'key_takeaways' => $keyTakeaways,
            'duration_seconds' => $class->duration_seconds,
        ]);
    }

    /**
     * AJAX trigger to generate an authentic AI virtual cultural studio background.
     */
    public function generateStudioBackground(Request $request): JsonResponse
    {
        $request->validate([
            'cultural_theme' => 'required|string|max:255',
            'art_style' => 'nullable|string|max:100',
        ]);

        $res = $this->studioService->generateCustomStudioBackground(
            $request->cultural_theme,
            $request->art_style
        );

        return response()->json($res);
    }

    /**
     * Post a question or discussion message to the class.
     */
    public function addDiscussion(Request $request, CulturalClass $class)
    {
        $request->validate([
            'message' => 'required|string|max:2000',
            'timestamp_seconds' => 'nullable|integer',
            'parent_id' => 'nullable|exists:cultural_class_discussions,id',
        ]);

        $discussion = CulturalClassDiscussion::create([
            'cultural_class_id' => $class->id,
            'user_id' => auth()->id(),
            'parent_id' => $request->parent_id,
            'timestamp_seconds' => $request->timestamp_seconds,
            'message' => $request->message,
        ]);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'discussion' => $discussion->load('user'),
            ]);
        }

        return back()->with('success', 'Your question has been posted to the class discussion!');
    }
}
