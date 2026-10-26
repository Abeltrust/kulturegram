<?php

namespace App\Http\Controllers;

use App\Models\Culture;
use App\Services\MetaAI\MetaModelApiClient;
use App\Services\MetaAI\MuseImageService;
use App\Services\MetaAI\MuseSparkService;
use App\Services\MetaAI\MuseVoiceTranscribeService;
use App\Services\MetaAI\MuseWonderService;
use App\Services\MetaAI\SamSegmentationService;
use App\Services\MetaAI\MuseGlimmerService;
use App\Services\MetaAI\MuseSparkOpenService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class LivingHeritageController extends Controller
{
    protected MetaModelApiClient $apiClient;
    protected MuseVoiceTranscribeService $transcribeService;
    protected MuseSparkService $sparkService;
    protected MuseWonderService $wonderService;
    protected MuseImageService $imageService;
    protected SamSegmentationService $samService;
    protected MuseGlimmerService $glimmerService;
    protected MuseSparkOpenService $sparkOpenService;

    public function __construct(
        MetaModelApiClient $apiClient,
        MuseVoiceTranscribeService $transcribeService,
        MuseSparkService $sparkService,
        MuseWonderService $wonderService,
        MuseImageService $imageService,
        SamSegmentationService $samService,
        MuseGlimmerService $glimmerService,
        MuseSparkOpenService $sparkOpenService
    ) {
        $this->apiClient = $apiClient;
        $this->transcribeService = $transcribeService;
        $this->sparkService = $sparkService;
        $this->wonderService = $wonderService;
        $this->imageService = $imageService;
        $this->samService = $samService;
        $this->glimmerService = $glimmerService;
        $this->sparkOpenService = $sparkOpenService;
    }

    /**
     * Living Heritage Capture feature landing page.
     */
    public function index()
    {
        $isConfigured = $this->apiClient->isConfigured();
        $maskedKey = $this->apiClient->getMaskedKey();

        // Contributor's pending drafts and AI-assisted stories
        $userDrafts = Culture::where('submitted_by', Auth::id())
            ->whereIn('status', ['draft', 'pending_review'])
            ->latest()
            ->take(6)
            ->get();

        $recentLivingHeritage = Culture::where('is_ai_assisted', true)
            ->whereIn('status', ['approved', 'featured'])
            ->with('submitter')
            ->latest()
            ->take(4)
            ->get();

        return view('cultural-hub.living-heritage.index', compact(
            'isConfigured',
            'maskedKey',
            'userDrafts',
            'recentLivingHeritage'
        ));
    }

    /**
     * Interactive 5-Stage Living Heritage Capture Wizard.
     */
    public function create()
    {
        $categories = MuseSparkService::VALID_CATEGORIES;
        $isConfigured = $this->apiClient->isConfigured();
        $maskedKey = $this->apiClient->getMaskedKey();

        $licenses = [
            'Public Domain',
            'CC0 (Creative Commons Zero)',
            'CC BY (Attribution)',
            'CC BY-SA (Attribution-ShareAlike)',
            'CC BY-NC (Attribution-NonCommercial)',
            'CC BY-ND (Attribution-NoDerivs)',
            'All Rights Reserved',
        ];

        return view('cultural-hub.living-heritage.wizard', compact(
            'categories',
            'licenses',
            'isConfigured',
            'maskedKey'
        ));
    }

    /**
     * Transcribe uploaded cultural audio using Muse Voice Transcribe.
     */
    public function transcribeAudio(Request $request): JsonResponse
    {
        $request->validate([
            'audio' => 'required|file|mimes:mp3,wav,m4a,aac,ogg,webm|max:25600',
            'language_hint' => 'nullable|string|max:20',
            'consent' => 'required|accepted',
        ], [
            'consent.accepted' => 'You must grant cultural consent before audio is submitted for AI processing.',
            'audio.max' => 'Audio file size cannot exceed 25MB.',
        ]);

        $audioFile = $request->file('audio');

        // Preserve authentic audio recording permanently in public storage
        $storedPath = $audioFile->store('cultures/audios', 'public');

        $result = $this->transcribeService->transcribe($storedPath, $request->input('language_hint'));

        return response()->json([
            'success' => $result['success'],
            'configured' => $result['configured'],
            'transcript' => $result['transcript'] ?? '',
            'audio_path' => $storedPath,
            'audio_url' => Storage::disk('public')->url($storedPath),
            'language' => $result['language'] ?? null,
            'model' => $result['model'],
            'latency_ms' => $result['latency_ms'] ?? 0.0,
            'error' => $result['error'] ?? null,
        ], $result['success'] ? 200 : ($result['status_code'] ?? 400));
    }

    /**
     * Prepare structured cultural record using Muse Spark.
     */
    public function prepareStory(Request $request): JsonResponse
    {
        $request->validate([
            'story' => 'required|string|min:20',
            'region' => 'nullable|string|max:255',
            'language' => 'nullable|string|max:100',
            'consent' => 'required|accepted',
        ], [
            'story.min' => 'Please provide at least 20 words for the story narrative to extract meaningful cultural context.',
            'consent.accepted' => 'You must confirm cultural consent before submitting stories for AI structuring.',
        ]);

        $context = [
            'region' => $request->input('region'),
            'language' => $request->input('language'),
        ];

        $result = $this->sparkService->prepareStory($request->input('story'), $context);

        return response()->json([
            'success' => $result['success'],
            'configured' => $result['configured'],
            'record' => $result['record'] ?? null,
            'model' => $result['model'],
            'latency_ms' => $result['latency_ms'] ?? 0.0,
            'error' => $result['error'] ?? null,
        ], $result['success'] ? 200 : ($result['status_code'] ?? 400));
    }

    /**
     * Bounded preservation plan assessment using Muse Wonder.
     */
    public function preservationPlan(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'region' => 'nullable|string|max:255',
            'category' => 'nullable|string',
            'description' => 'required|string',
            'raw_story' => 'nullable|string',
        ]);

        $draft = [
            'name' => $request->input('name'),
            'region' => $request->input('region'),
            'category' => $request->input('category'),
            'description' => $request->input('description'),
        ];

        $result = $this->wonderService->assessPreservationPlan($draft, $request->input('raw_story'));

        return response()->json([
            'success' => $result['success'],
            'configured' => $result['configured'],
            'preservation_plan' => $result['preservation_plan'] ?? null,
            'model' => $result['model'],
            'latency_ms' => $result['latency_ms'] ?? 0.0,
            'error' => $result['error'] ?? null,
        ], $result['success'] ? 200 : ($result['status_code'] ?? 400));
    }

    /**
     * Generate cultural illustration using Muse Image.
     */
    public function generateIllustration(Request $request): JsonResponse
    {
        $request->validate([
            'story' => 'required|string|min:15',
            'cultural_context' => 'nullable|string|max:255',
            'custom_prompt' => 'nullable|string|max:255',
        ]);

        $result = $this->imageService->generateIllustration(
            $request->input('story'),
            $request->input('cultural_context'),
            $request->input('custom_prompt')
        );

        return response()->json([
            'success' => $result['success'],
            'configured' => $result['configured'],
            'image_path' => $result['image_path'] ?? null,
            'image_url' => $result['image_url'] ?? null,
            'prompt' => $result['prompt'] ?? null,
            'model' => $result['model'],
            'is_ai_generated' => true,
            'disclaimer' => $result['attribution_disclaimer'] ?? 'AI-Generated Cultural Illustration. Artistic representation only.',
            'latency_ms' => $result['latency_ms'] ?? 0.0,
            'error' => $result['error'] ?? null,
        ], $result['success'] ? 200 : ($result['status_code'] ?? 400));
    }

    /**
     * Segment authentic artifact photo using SAM 3.1.
     */
    public function segmentArtifact(Request $request): JsonResponse
    {
        $request->validate([
            'artifact_image' => 'required|image|mimes:jpeg,png,webp|max:10240',
            'object_prompt' => 'required|string|max:100',
        ], [
            'object_prompt.required' => 'Please name the object to segment (e.g. basket, beaded crown, pottery, carving).',
        ]);

        $result = $this->samService->segmentArtifact(
            $request->file('artifact_image'),
            $request->input('object_prompt')
        );

        return response()->json([
            'success' => $result['success'],
            'configured' => $result['configured'],
            'original_url' => $result['original_url'] ?? null,
            'original_path' => $result['original_path'] ?? null,
            'segmented_url' => $result['segmented_url'] ?? null,
            'segmented_path' => $result['segmented_path'] ?? null,
            'mask_path' => $result['mask_path'] ?? null,
            'object_label' => $result['object_label'] ?? $request->input('object_prompt'),
            'disclaimer' => $result['disclaimer'] ?? 'Visual artifact boundary isolation. Not historical proof of authenticity.',
            'model' => $result['model'],
            'latency_ms' => $result['latency_ms'] ?? 0.0,
            'error' => $result['error'] ?? null,
        ], $result['success'] ? 200 : ($result['status_code'] ?? 400));
    }

    /**
     * Save draft or submit completed Living Heritage record into moderation queue.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'region' => 'required|string|max:255',
            'category' => 'required|string',
            'description' => 'required|string',
            'raw_story' => 'nullable|string',
            'audio_transcript' => 'nullable|string',
            'audio_path' => 'nullable|string',
            'image' => 'nullable|image|max:10240',
            'ai_illustration' => 'nullable|string',
            'ai_illustration_prompt' => 'nullable|string',
            'artifact_segmented_path' => 'nullable|string',
            'artifact_label' => 'nullable|string',
            'wonder_plan_json' => 'nullable|string',
            'tags' => 'nullable|string',
            'action' => 'required|in:draft,submit',
        ]);

        // Word count verification for submission
        if ($request->input('action') === 'submit') {
            $wordCount = str_word_count(strip_tags($request->input('description')));
            if ($wordCount < 20) {
                return back()->withInput()->withErrors([
                    'description' => 'Description must contain at least 20 words for archival quality before submission.',
                ]);
            }
        }

        // Tags parsing
        $tags = [];
        if ($request->filled('tags')) {
            $rawTags = is_array($request->input('tags')) ? $request->input('tags') : explode(',', (string) $request->input('tags'));
            foreach ($rawTags as $tag) {
                $clean = trim(preg_replace('/[^a-zA-Z0-9_\-\s]/', '', $tag));
                if (!empty($clean)) {
                    $tags[] = strtolower($clean);
                }
            }
        }

        // Artifact segmentation json assembly
        $artifactData = null;
        if ($request->filled('artifact_segmented_path')) {
            $artifactData = [
                'segmented_path' => $request->input('artifact_segmented_path'),
                'label' => $request->input('artifact_label', 'Artifact'),
                'model' => config('meta_ai.models.sam', 'sam-3.1'),
                'disclaimer' => 'Visual isolation via SAM 3.1; not historical verification.',
            ];
        }

        // Wonder preservation plan decoding
        $wonderPlan = null;
        if ($request->filled('wonder_plan_json')) {
            $wonderPlan = json_decode($request->input('wonder_plan_json'), true);
        }

        $culture = new Culture();
        $culture->name = $request->input('name');
        $culture->region = $request->input('region');
        $culture->category = $request->input('category');
        $culture->description = $request->input('description');
        $culture->language = $request->input('language');
        $culture->historical_period = $request->input('historical_period');
        $culture->significance = $request->input('significance');
        $culture->rituals = $request->input('rituals');
        $culture->community_role = $request->input('community_role');
        $culture->transmission_methods = $request->input('transmission_methods');
        $culture->challenges = $request->input('challenges');
        $culture->raw_story = $request->input('raw_story');
        $culture->audio_transcript = $request->input('audio_transcript');
        $culture->audio_path = $request->input('audio_path');
        $culture->ai_illustration = $request->input('ai_illustration');
        $culture->ai_illustration_prompt = $request->input('ai_illustration_prompt');
        $culture->artifact_segmentation = $artifactData;
        $culture->wonder_preservation_plan = $wonderPlan;
        $culture->tags = array_values(array_unique($tags));
        $culture->is_ai_assisted = true;
        $culture->submitted_by = Auth::id();

        // Model provenance metadata
        $culture->ai_metadata = [
            'meta_client' => 'MetaModelApiClient',
            'models_used' => [
                'spark' => config('meta_ai.models.spark'),
                'wonder' => config('meta_ai.models.wonder'),
                'voice_transcribe' => config('meta_ai.models.voice_transcribe'),
                'image' => config('meta_ai.models.image'),
                'sam' => config('meta_ai.models.sam'),
            ],
            'consent_granted' => true,
            'submitted_at' => now()->toIso8601String(),
        ];

        // Handle primary uploaded authentic photo
        if ($request->hasFile('image')) {
            $culture->image = $request->file('image')->store('cultures', 'public');
        } elseif ($request->filled('ai_illustration') && empty($culture->image)) {
            // If contributor approved AI illustration as visual presentation
            $culture->image = $request->input('ai_illustration');
            $culture->image_license = 'AI-Generated Illustration (Meta Muse Image)';
        }

        // Status assignment: CRITICAL moderation boundary
        // NEVER auto-publish!
        if ($request->input('action') === 'draft') {
            $culture->status = 'draft';
            $message = 'Living Heritage draft saved privately. You can resume editing anytime before submission.';
        } else {
            $culture->status = 'pending_review';
            $message = 'Heritage record submitted successfully to the community moderation queue for review!';
            Auth::user()->increment('cultures_contributed');
        }

        $culture->save();

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'status' => $culture->status,
                'culture_id' => $culture->id,
                'redirect_url' => route('cultural-hub.show', $culture->id),
                'message' => $message,
            ]);
        }

        return redirect()->route('cultural-hub.show', $culture->id)->with('success', $message);
    }

    /**
     * Side-by-side Model Comparison Interface.
     */
    public function compare(Request $request)
    {
        $sampleStory = "In the hills of Idanre, the Owa (King) wears the beaded crown adorned with seven sacred birds only during the annual Orosun festival. Elders recount that the priests climb 682 steps to the ancient stone shrine carrying calabashes of palm wine. Modern youth have begun moving to coastal cities, placing the intricate bead-weaving lineage and festival songs at risk of fading.";

        return view('cultural-hub.living-heritage.compare', compact('sampleStory'));
    }

    /**
     * Run comparative inference across hosted Muse Spark and open-weight models.
     */
    public function runComparison(Request $request): JsonResponse
    {
        $request->validate([
            'story' => 'required|string|min:20',
        ]);

        $story = $request->input('story');

        // 1. Hosted Muse Spark (Shared Meta Model API key)
        $sparkResult = $this->sparkService->prepareStory($story);

        // 2. Open-Weight Muse Glimmer (Independent endpoint)
        $glimmerResult = $this->glimmerService->analyzeTagsAndClaims($story);

        // 3. Open-Weight Muse Spark Open (Independent endpoint)
        $sparkOpenResult = $this->sparkOpenService->generateRecord($story);

        return response()->json([
            'source_story' => $story,
            'models' => [
                'muse_spark' => [
                    'name' => 'Muse Spark (Hosted Meta Model API)',
                    'provider' => 'Meta Model API (Hosted)',
                    'configured' => $sparkResult['configured'],
                    'success' => $sparkResult['success'],
                    'output' => $sparkResult['record'],
                    'latency_ms' => $sparkResult['latency_ms'],
                    'error' => $sparkResult['error'],
                ],
                'muse_glimmer' => [
                    'name' => 'Muse Glimmer (Open-Weight)',
                    'provider' => $this->glimmerService->getProviderName(),
                    'configured' => $glimmerResult['configured'],
                    'success' => $glimmerResult['success'],
                    'output' => [
                        'tags' => $glimmerResult['tags'] ?? [],
                        'claim_flags' => $glimmerResult['claim_flags'] ?? [],
                        'raw' => $glimmerResult['raw_output'] ?? null,
                    ],
                    'latency_ms' => $glimmerResult['latency_ms'],
                    'error' => $glimmerResult['error'],
                ],
                'muse_spark_open' => [
                    'name' => 'Muse Spark Open (Open-Weight)',
                    'provider' => $this->sparkOpenService->getProviderName(),
                    'configured' => $sparkOpenResult['configured'],
                    'success' => $sparkOpenResult['success'],
                    'output' => $sparkOpenResult['record'],
                    'latency_ms' => $sparkOpenResult['latency_ms'],
                    'error' => $sparkOpenResult['error'],
                ],
            ],
        ]);
    }
}
