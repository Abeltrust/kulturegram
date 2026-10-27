<?php

namespace App\Http\Controllers;

use App\Models\CulturalClass;
use App\Models\CulturalClassEnrollment;
use App\Models\CulturalClassDiscussion;
use App\Models\Post;
use App\Services\MetaAI\CulturalClassTranscriptionService;
use App\Services\MetaAI\CulturalVirtualStudioService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CulturalClassController extends Controller
{
    protected CulturalClassTranscriptionService $transcriptionService;
    protected CulturalVirtualStudioService $studioService;

    public function __construct(
        CulturalClassTranscriptionService $transcriptionService,
        CulturalVirtualStudioService $studioService
    ) {
        $this->transcriptionService = $transcriptionService;
        $this->studioService = $studioService;
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

        return view('education.classes.create', compact('presets', 'commonLanguages'));
    }

    /**
     * Store a newly created cultural class with AI transcription and bilingual extraction.
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'cultural_focus' => 'required|string|max:255',
            'target_language' => 'required|string|max:100',
            'source_language' => 'nullable|string|max:100',
            'level' => 'required|string|in:all_levels,beginner,intermediate,advanced',
            'price_type' => 'required|string|in:free,paid',
            'price' => 'nullable|numeric|min:0',
            'description' => 'nullable|string|max:5000',
            'video' => 'nullable|file|mimes:mp4,webm,mov,avi,m4v,mp3,wav,m4a|max:102400', // 100MB
            'sample_video_url' => 'nullable|string|max:500',
            'thumbnail' => 'nullable|image|max:10240',
            'virtual_background_url' => 'nullable|string|max:500',
            'raw_transcript' => 'nullable|string|max:15000',
            'share_to_timeline' => 'nullable|boolean',
        ]);

        $videoPath = null;
        $previewVideoPath = null;
        $thumbnailPath = null;

        // Handle video upload or sample URL
        if ($request->hasFile('video')) {
            $videoPath = $request->file('video')->store('cultural_classes/videos', 'public');
        } elseif ($request->filled('sample_video_url')) {
            $videoPath = $request->sample_video_url;
        }

        if ($request->hasFile('thumbnail')) {
            $thumbnailPath = $request->file('thumbnail')->store('cultural_classes/thumbnails', 'public');
        } elseif ($request->filled('virtual_background_url')) {
            $thumbnailPath = $request->virtual_background_url;
        }

        // Process AI Transcription & Bilingual Structure
        $aiData = [];
        $targetLang = $request->target_language ?: 'Hausa';
        $culturalFocus = $request->cultural_focus;

        if ($request->filled('raw_transcript')) {
            $aiData = $this->transcriptionService->structureBilingualLesson(
                $request->raw_transcript,
                $targetLang,
                $culturalFocus
            );
        } elseif ($request->hasFile('video')) {
            $aiData = $this->transcriptionService->processMedia(
                $request->file('video'),
                $targetLang,
                $culturalFocus
            );
        } else {
            // Default introductory bilingual lesson structure
            $defaultScript = "Welcome to Kulturegram's cultural masterclass on {$targetLang}. Today we learn the sacred art of respectful conversation. In {$targetLang}, greeting an elder with sincerity is essential. You say 'Ina kwana' which means 'Good morning, how is your household?'. When receiving kindness, say 'Nagode kwarai' which translates to 'Thank you with deep gratitude'.";
            $aiData = $this->transcriptionService->structureBilingualLesson(
                $defaultScript,
                $targetLang,
                $culturalFocus
            );
        }

        $price = $request->price_type === 'paid' ? (float) ($request->price ?: 5.00) : 0.00;

        $class = CulturalClass::create([
            'instructor_id' => auth()->id(),
            'title' => $request->title,
            'description' => $request->description ?: ($aiData['summary'] ?? null),
            'cultural_focus' => $culturalFocus,
            'target_language' => $targetLang,
            'source_language' => $request->source_language ?: 'English',
            'level' => $request->level,
            'price_type' => $request->price_type,
            'price' => $price,
            'video_url' => $videoPath,
            'preview_video_url' => $videoPath,
            'thumbnail_url' => $thumbnailPath,
            'virtual_background_url' => $request->virtual_background_url,
            'duration_seconds' => $aiData['duration_seconds'] ?? 60,
            'transcript' => $aiData['transcript'] ?? $request->raw_transcript,
            'subtitles_vtt' => $aiData['subtitles_vtt'] ?? null,
            'timed_subtitles' => $aiData['subtitles'] ?? [],
            'bilingual_vocabulary' => $aiData['vocabulary'] ?? [],
            'ai_summary' => $aiData['summary'] ?? null,
            'ai_enhancements' => [
                'key_takeaways' => $aiData['key_takeaways'] ?? [],
                'vocabulary_count' => count($aiData['vocabulary'] ?? []),
                'subtitles_count' => count($aiData['subtitles'] ?? []),
            ],
            'is_published' => true,
        ]);

        // Cross-post teaser card to user's Timeline if requested
        if ($request->boolean('share_to_timeline')) {
            $teaserText = "🎓 Just published a new Cultural Class in the Educational Hub!\n\n"
                        . "« {$class->title} » ({$class->target_language})\n"
                        . ($class->ai_summary ? "✨ {$class->ai_summary}\n\n" : "\n")
                        . "Learn authentic phrases, interactive subtitles, and living traditions with me. Watch now in the Educational Hub!";

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
        }

        return redirect()->route('education.classes.show', $class->slug)
            ->with('success', 'Your cultural class was published successfully with AI subtitles and bilingual vocabulary cards!');
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
            'transcript' => 'nullable|string|max:15000',
            'target_language' => 'nullable|string|max:100',
            'cultural_focus' => 'nullable|string|max:255',
            'media' => 'nullable|file|mimes:mp3,wav,m4a,mp4,webm|max:51200',
        ]);

        $lang = $request->target_language ?: 'Hausa';
        $focus = $request->cultural_focus;

        if ($request->hasFile('media')) {
            $res = $this->transcriptionService->processMedia($request->file('media'), $lang, $focus);
        } else {
            $text = $request->transcript ?: "In {$lang}, when you greet someone, you say 'Ina kwana' which means 'Good morning'.";
            $res = $this->transcriptionService->structureBilingualLesson($text, $lang, $focus);
        }

        return response()->json($res);
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
