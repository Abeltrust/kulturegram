<?php

namespace App\Http\Controllers;

use App\Models\Culture;
use App\Models\CulturalSource;
use App\Models\CulturalClaim;
use App\Models\CulturalContributor;
use App\Models\CulturalMedia;
use App\Models\CulturalTranslation;
use App\Models\CulturalRevision;
use App\Services\MetaAI\CultureStructuringService;
use App\Services\MetaAI\CulturalCompanionService;
use App\Services\MetaAI\CulturalKnowledgeGraphService;
use App\Services\MetaAI\MultilingualCulturalAccessService;
use App\Services\MetaAI\OralTraditionVoiceService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CulturalHubController extends Controller
{
    public function index(Request $request)
    {
        $category = $request->get('category', 'all');
        $search = $request->get('search');
        $culturalGroup = $request->get('cultural_group');
        $language = $request->get('language');
        $reviewStatus = $request->get('review_status', 'all');

        $query = Culture::with(['submitter', 'lockIns.user', 'resonances.user', 'sources', 'claims'])
            ->where(function ($q) {
                $q->where('status', 'approved')
                    ->orWhere('status', 'featured');
                if (Auth::check()) {
                    $q->orWhere('submitted_by', Auth::id());
                }
            });

        // Respect sensitivity and restriction levels
        if (!Auth::check() || (!Auth::user()->is_admin)) {
            $query->where(function ($q) {
                $q->whereNull('sensitivity_level')
                    ->orWhere('sensitivity_level', 'public')
                    ->orWhere('submitted_by', Auth::id());
            });
        }

        // Multi-facet search across real archival attributes
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('translated_title', 'like', "%{$search}%")
                    ->orWhere('region', 'like', "%{$search}%")
                    ->orWhere('locality', 'like', "%{$search}%")
                    ->orWhere('cultural_group', 'like', "%{$search}%")
                    ->orWhere('language', 'like', "%{$search}%")
                    ->orWhere('historical_period', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhere('raw_story', 'like', "%{$search}%");
            });
        }

        if ($category !== 'all') {
            $query->where('category', $category);
        }

        if ($culturalGroup) {
            $query->where('cultural_group', 'like', "%{$culturalGroup}%");
        }

        if ($language) {
            $query->where('language', 'like', "%{$language}%");
        }

        if ($reviewStatus !== 'all') {
            $query->where(function ($q) use ($reviewStatus) {
                $q->where('review_status', $reviewStatus)
                    ->orWhere('status', $reviewStatus);
            });
        }

        $cultures = $query->latest()->paginate(18)->withQueryString();

        // Calculate verified counts directly from database without fabrication
        $totalRecordsCount = Culture::whereIn('status', ['approved', 'featured'])->count();
        $distinctLanguagesCount = Culture::whereNotNull('language')->where('language', '!=', '')->distinct('language')->count('language');
        $distinctRegionsCount = Culture::whereNotNull('region')->where('region', '!=', '')->distinct('region')->count('region');

        $categories = [
            'all' => 'All Cultural Records',
            'Oral Traditions' => 'Oral Traditions',
            'Traditional Crafts' => 'Traditional Crafts',
            'Music & Dance' => 'Music & Dance',
            'Culinary Heritage' => 'Culinary Heritage',
            'Religious Practices' => 'Religious Practices',
            'Festivals & Ceremonies' => 'Festivals & Ceremonies',
            'Language & Literature' => 'Language & Literature',
            'Architecture & Design' => 'Architecture & Design',
            'Agricultural Practices' => 'Agricultural Practices',
            'Healing Traditions' => 'Healing Traditions',
            'Social Customs' => 'Social Customs',
            'Art & Aesthetics' => 'Art & Aesthetics',
            'Clothing & Textiles' => 'Clothing & Textiles',
        ];

        return view('cultural-hub.index', compact(
            'cultures',
            'categories',
            'category',
            'search',
            'culturalGroup',
            'language',
            'reviewStatus',
            'totalRecordsCount',
            'distinctLanguagesCount',
            'distinctRegionsCount'
        ));
    }

    public function show(Culture $culture, CulturalKnowledgeGraphService $graphService)
    {
        if ($culture->status !== 'approved' && $culture->status !== 'featured') {
            if (!Auth::check() || ($culture->submitted_by !== Auth::id() && !Auth::user()->is_admin)) {
                abort(404);
            }
        }

        // Sensitive / Restricted access check
        if ($culture->sensitivity_level === 'restricted' || $culture->review_status === 'restricted') {
            if (!Auth::check() || ($culture->submitted_by !== Auth::id() && !Auth::user()->is_admin)) {
                abort(403, 'This cultural record contains sacred or restricted community material and is limited to authorized custodians.');
            }
        }

        $culture->load([
            'submitter',
            'lockIns',
            'resonances',
            'sources.claims',
            'claims.source',
            'culturalContributors.user',
            'mediaAssets',
            'translations',
            'revisions' => function ($q) {
                $q->latest();
            },
        ]);

        $relatedConnections = $graphService->getRelatedRecords($culture, 4);
        $relatedCultures = collect($relatedConnections)->pluck('culture')->filter();
        if ($relatedCultures->isEmpty()) {
            $relatedCultures = Culture::where('id', '!=', $culture->id)
                ->where(function ($q) use ($culture) {
                    if (!empty($culture->category)) {
                        $q->where('category', $culture->category);
                    }
                    if (!empty($culture->region)) {
                        $q->orWhere('region', $culture->region);
                    }
                    if (!empty($culture->cultural_group)) {
                        $q->orWhere('cultural_group', $culture->cultural_group);
                    }
                })
                ->whereIn('status', ['approved', 'featured'])
                ->take(3)
                ->get();
        }

        return view('cultural-hub.show', compact('culture', 'relatedCultures', 'relatedConnections'));
    }

    public function edit(Culture $culture)
    {
        if ($culture->submitted_by !== Auth::id() && (!Auth::user()->is_admin)) {
            abort(403, 'You are not the guardian of this story.');
        }

        $categories = [
            'Traditional Crafts',
            'Oral Traditions',
            'Music & Dance',
            'Culinary Heritage',
            'Religious Practices',
            'Festivals & Ceremonies',
            'Language & Literature',
            'Architecture & Design',
            'Agricultural Practices',
            'Healing Traditions',
            'Social Customs',
            'Art & Aesthetics',
            'Games & Sports',
            'Clothing & Textiles',
        ];

        $licenses = [
            'Public Domain',
            'CC0 (Creative Commons Zero)',
            'CC BY (Attribution)',
            'CC BY-SA (Attribution-ShareAlike)',
            'CC BY-NC (Attribution-NonCommercial)',
            'CC BY-ND (Attribution-NoDerivs)',
            'All Rights Reserved',
        ];

        return view('cultural-hub.edit', compact('culture', 'categories', 'licenses'));
    }

    public function create()
    {
        $categories = [
            'Traditional Crafts',
            'Oral Traditions',
            'Music & Dance',
            'Culinary Heritage',
            'Religious Practices',
            'Festivals & Ceremonies',
            'Language & Literature',
            'Architecture & Design',
            'Agricultural Practices',
            'Healing Traditions',
            'Social Customs',
            'Art & Aesthetics',
            'Games & Sports',
            'Clothing & Textiles'
        ];

        $licenses = [
            'Public Domain',
            'CC0 (Creative Commons Zero)',
            'CC BY (Attribution)',
            'CC BY-SA (Attribution-ShareAlike)',
            'CC BY-NC (Attribution-NonCommercial)',
            'CC BY-ND (Attribution-NoDerivs)',
            'All Rights Reserved'
        ];

        return view('cultural-hub.create', compact('categories', 'licenses'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'region' => 'required|string|max:255',
            'description' => 'required|string',
            'category' => 'required|string',
            'cultural_group' => 'nullable|string|max:255',
            'translated_title' => 'nullable|string|max:255',
            'subtype' => 'nullable|string|max:255',
            'language' => 'nullable|string|max:255',
            'locality' => 'nullable|string|max:255',
            'sensitivity_level' => 'nullable|string|in:public,community_only,restricted',
            'image' => 'nullable|image|max:10240',
            'images.*' => 'nullable|image|max:10240',
            'video_url' => 'nullable|url',
            'video_file' => 'nullable|file|mimes:mp4,mov,ogg,qt|max:51200',
            'video_description' => 'nullable|string|max:1000',
            'audio_url' => 'nullable|url',
            'audio_file' => 'nullable|file|mimes:mp3,wav,aac|max:20480',
            'audio_description' => 'nullable|string|max:1000',
            'license_type' => 'nullable|string',
            'license_credit' => 'nullable|string|max:255',
        ]);

        $wordCount = str_word_count(strip_tags((string)$request->description));
        if ($wordCount < 10 && $request->input('status') !== 'draft') {
            return back()->withInput()->withErrors(['description' => 'Description must contain at least 10 words of cultural context.']);
        }

        $culture = new Culture($request->except(['video_file', 'audio_file', 'images', 'image', 'claims', 'speaker_name', 'speaker_is_anonymous', 'contributor_name', 'contributor_role', 'relationship_to_material']));
        $culture->submitted_by = Auth::id();
        $isDraft = $request->input('is_draft') || $request->input('status') === 'draft';
        $culture->status = $isDraft ? 'draft' : 'pending_review';
        $culture->review_status = $isDraft ? 'draft' : 'submitted';

        // Process Video Embed URL
        if ($request->video_url) {
            $culture->video_url = $this->convertToEmbedUrl($request->video_url);
        }

        // Handle Primary Image with optimization
        if ($request->hasFile('image')) {
            $culture->image = $this->optimizeAndStore($request->file('image'), 'cultures');
        }

        // Handle Multiple Gallery Images (Max 5)
        if ($request->hasFile('images')) {
            $gallery = [];
            foreach (array_slice($request->file('images'), 0, 5) as $file) {
                $gallery[] = $this->optimizeAndStore($file, 'cultures/gallery');
            }
            $culture->media_files = $gallery;
        }

        // Handle Video File
        if ($request->hasFile('video_file')) {
            $culture->video_path = $request->file('video_file')->store('cultures/videos', 'public');
        }

        // Handle Audio File
        if ($request->hasFile('audio_file')) {
            $culture->audio_path = $request->file('audio_file')->store('cultures/audios', 'public');
        }

        $culture->save();

        // 1. Primary Cultural Source Record
        $sourceType = $request->input('source_type');
        if (!$sourceType) {
            if ($culture->audio_path || $request->hasFile('audio_file')) {
                $sourceType = 'audio_recording';
            } elseif ($culture->video_path || $request->hasFile('video_file')) {
                $sourceType = 'video_recording';
            } elseif (!empty($culture->raw_story)) {
                $sourceType = 'oral_testimony';
            } else {
                $sourceType = 'written_account';
            }
        }

        $primarySource = $culture->sources()->create([
            'source_type' => $sourceType,
            'file_path' => $culture->audio_path ?: ($culture->video_path ?: null),
            'external_url' => $culture->audio_url ?: ($culture->video_url ?: null),
            'original_text' => $culture->raw_story ?: $culture->description,
            'original_language' => $culture->language,
            'speaker_name' => $request->input('speaker_name'),
            'speaker_is_anonymous' => $request->boolean('speaker_is_anonymous'),
            'attribution_preference' => $request->input('attribution_preference'),
            'recording_location' => $culture->locality ?: $culture->region,
            'recording_date' => $request->input('recording_date'),
            'transcript_origin' => $culture->is_ai_assisted ? 'ai_generated' : 'human_authored',
            'transcript_review_status' => 'unreviewed',
            'access_restriction' => $culture->sensitivity_level ?: 'public',
        ]);

        // 2. Initial Claims if provided
        if ($request->filled('claims')) {
            $claims = is_array($request->input('claims')) ? $request->input('claims') : json_decode($request->input('claims'), true);
            if (is_array($claims)) {
                foreach ($claims as $claim) {
                    if (!empty($claim['claim_text'])) {
                        $culture->claims()->create([
                            'cultural_source_id' => $primarySource->id,
                            'claim_text' => $claim['claim_text'],
                            'claim_type' => $claim['claim_type'] ?? 'quoted_testimony',
                            'evidence_reference' => $claim['source_excerpt'] ?? ($claim['evidence_reference'] ?? null),
                            'review_status' => 'unverified',
                        ]);
                    }
                }
            }
        }

        // 3. Documented Contributor Attribution
        $culture->culturalContributors()->create([
            'user_id' => Auth::id(),
            'public_name' => $request->input('contributor_name', Auth::user()->name),
            'role' => $request->input('contributor_role', 'contributor'),
            'attribution_preference' => $request->input('attribution_preference'),
            'relationship_to_material' => $request->input('relationship_to_material', 'Community contributor'),
            'review_decision' => 'approved',
            'reviewed_at' => now(),
            'permission_to_display' => !$request->boolean('contributor_is_private'),
        ]);

        // Update user's cultures contributed count
        Auth::user()->increment('cultures_contributed');

        $msg = $isDraft 
            ? 'Draft record saved successfully. You can return to refine evidence before submitting.'
            : 'Cultural record submitted to archive for community and custodian verification.';

        return redirect()->route('cultural-hub.index')->with('success', $msg);
    }

    public function update(Request $request, Culture $culture)
    {
        if ($culture->submitted_by !== Auth::id() && (!Auth::user()->is_admin)) {
            abort(403, 'You are not the guardian of this story.');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'region' => 'required|string|max:255',
            'description' => 'required|string',
            'category' => 'required|string',
            'cultural_group' => 'nullable|string|max:255',
            'translated_title' => 'nullable|string|max:255',
            'subtype' => 'nullable|string|max:255',
            'language' => 'nullable|string|max:255',
            'locality' => 'nullable|string|max:255',
            'sensitivity_level' => 'nullable|string|in:public,community_only,restricted',
            'image' => 'nullable|image|max:10240',
            'images.*' => 'nullable|image|max:10240',
            'video_url' => 'nullable|url',
            'video_description' => 'nullable|string|max:1000',
            'audio_url' => 'nullable|url',
            'audio_description' => 'nullable|string|max:1000',
            'license_type' => 'nullable|string',
            'license_credit' => 'nullable|string|max:255',
        ]);

        $culture->fill($request->except(['video_file', 'audio_file', 'images', 'image', 'claims']));

        // Process Video Embed URL
        if ($request->video_url && $request->video_url !== $culture->getOriginal('video_url')) {
            $culture->video_url = $this->convertToEmbedUrl($request->video_url);
        }

        // Handle Primary Image with optimization
        if ($request->hasFile('image')) {
            $culture->image = $this->optimizeAndStore($request->file('image'), 'cultures');
        }

        // Handle Multiple Gallery Images (Max 5)
        if ($request->hasFile('images')) {
            $gallery = [];
            foreach (array_slice($request->file('images'), 0, 5) as $file) {
                $gallery[] = $this->optimizeAndStore($file, 'cultures/gallery');
            }
            $culture->media_files = $gallery;
        }

        $culture->save();

        return redirect()->route('cultural-hub.show', $culture->id)
            ->with('success', 'Cultural archive record updated successfully.');
    }

    public function destroy(Culture $culture)
    {
        if ($culture->submitted_by !== Auth::id()) {
            abort(403, 'You cannot dissolve a story you did not enshrine.');
        }

        $culture->delete();

        // Optional: decrement the count
        Auth::user()->decrement('cultures_contributed');

        return redirect()->route('cultural-hub.index')
            ->with('success', 'The story has been dissolved into the mists of time.');
    }

    private function convertToEmbedUrl($url)
    {
        if (str_contains($url, 'youtube.com/') || str_contains($url, 'youtu.be/')) {
            $id = '';
            if (str_contains($url, 'v=')) {
                parse_str(parse_url($url, PHP_URL_QUERY), $vars);
                $id = $vars['v'] ?? '';
            } elseif (str_contains($url, 'youtu.be/')) {
                $id = ltrim(parse_url($url, PHP_URL_PATH), '/');
            }
            return $id ? "https://www.youtube.com/embed/{$id}" : $url;
        }

        if (str_contains($url, 'vimeo.com/')) {
            $id = ltrim(parse_url($url, PHP_URL_PATH), '/');
            return $id ? "https://player.vimeo.com/video/{$id}" : $url;
        }

        return $url;
    }

    private function optimizeAndStore($file, $directory)
    {
        $path = $file->hashName($directory);
        $fullPath = storage_path('app/public/' . $path);

        // Ensure directory exists
        if (!file_exists(dirname($fullPath))) {
            mkdir(dirname($fullPath), 0755, true);
        }

        $image = null;
        $extension = strtolower($file->getClientOriginalExtension());

        // Check if GD functions are available
        $hasGD = function_exists('imagecreatefromjpeg');

        if ($hasGD) {
            if ($extension === 'jpg' || $extension === 'jpeg') {
                $image = @imagecreatefromjpeg($file->getRealPath());
            } elseif ($extension === 'png') {
                $image = @imagecreatefrompng($file->getRealPath());
            } elseif ($extension === 'webp') {
                $image = @imagecreatefromwebp($file->getRealPath());
            }
        }

        if ($image) {
            $width = imagesx($image);
            $height = imagesy($image);
            $maxDim = 1200;

            if ($width > $maxDim || $height > $maxDim) {
                $ratio = $width / $height;
                if ($ratio > 1) {
                    $newWidth = $maxDim;
                    $newHeight = $maxDim / $ratio;
                } else {
                    $newHeight = $maxDim;
                    $newWidth = $maxDim * $ratio;
                }
                $newImage = imagecreatetruecolor($newWidth, $newHeight);
                imagecopyresampled($newImage, $image, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
                imagedestroy($image);
                $image = $newImage;
            }

            // Save as WebP for best quality/size ratio
            $webpPath = str_replace(['.jpg', '.jpeg', '.png'], '.webp', $path);
            $fullWebpPath = storage_path('app/public/' . $webpPath);
            imagewebp($image, $fullWebpPath, 80);
            imagedestroy($image);

            return $webpPath;
        }

        return $file->store($directory, 'public');
    }

    public function lockin(Culture $culture)
    {
        $user = Auth::user();

        $interaction = $user->interactions()
            ->where('interactable_type', Culture::class)
            ->where('interactable_id', $culture->id)
            ->where('type', 'lockin')
            ->first();

        if ($interaction) {
            $interaction->delete();
            $culture->decrement('locked_in_count');
            $lockedIn = false;
        } else {
            $user->interactions()->create([
                'interactable_type' => Culture::class,
                'interactable_id' => $culture->id,
                'type' => 'lockin',
            ]);
            $culture->increment('locked_in_count');
            $lockedIn = true;

            // Create notification
            if ($culture->submitted_by !== $user->id) {
                $culture->submitter->notifications()->create([
                    'from_user_id' => $user->id,
                    'type' => 'lockin',
                    'title' => 'New Lock-In',
                    'message' => $user->name . ' locked-in to ' . $culture->name,
                    'data' => ['culture_id' => $culture->id],
                ]);
            }
        }

        return response()->json([
            'lockedIn' => $lockedIn,
            'count' => $culture->fresh()->locked_in_count,
        ]);
    }

    public function resonance(Culture $culture)
    {
        $user = Auth::user();

        $interaction = $user->interactions()
            ->where('interactable_type', Culture::class)
            ->where('interactable_id', $culture->id)
            ->where('type', 'resonance')
            ->first();

        if ($interaction) {
            $interaction->delete();
            $culture->decrement('resonance_count');
            $resonated = false;
        } else {
            $user->interactions()->create([
                'interactable_type' => Culture::class,
                'interactable_id' => $culture->id,
                'type' => 'resonance',
            ]);
            $culture->increment('resonance_count');
            $resonated = true;

            // Create notification
            if ($culture->submitted_by !== $user->id) {
                $culture->submitter->notifications()->create([
                    'from_user_id' => $user->id,
                    'type' => 'resonance',
                    'title' => 'New Resonance',
                    'message' => $user->name . ' resonated with ' . $culture->name,
                    'data' => ['culture_id' => $culture->id],
                ]);
            }
        }

        return response()->json([
            'resonated' => $resonated,
            'count' => $culture->fresh()->resonance_count,
        ]);
    }

    /**
     * AI-assisted translation of cultural heritage account with original text preserved.
     */
    public function translate(
        Culture $culture,
        Request $request,
        \App\Services\MetaAI\MultilingualCulturalAccessService $accessService
    ) {
        $targetLanguage = $request->input('target_language', 'French');
        $result = $accessService->translateSummary(
            $culture->description ?? $culture->name,
            $targetLanguage,
            $culture->name . ' (' . $culture->region . ')'
        );

        return response()->json($result);
    }

    /**
     * AI adapted reading level (learner or concise) with authentic testimony preserved.
     */
    public function adaptReading(
        Culture $culture,
        Request $request,
        \App\Services\MetaAI\MultilingualCulturalAccessService $accessService
    ) {
        $level = $request->input('level', 'learner');
        $result = $accessService->adaptReadingLevel(
            $culture->description ?? $culture->name,
            $level
        );

        return response()->json($result);
    }

    /**
     * AI-assisted structuring of submitted source material / oral narrative.
     */
    public function structureNarrative(Request $request, CultureStructuringService $structuringService)
    {
        $sourceText = (string) $request->input('source_text', '');
        $context = $request->only(['title', 'region', 'language', 'category', 'cultural_group']);

        $result = $structuringService->structureRecord($sourceText, $context);
        return response()->json($result);
    }

    /**
     * AI-assisted spoken voice dictation and formatting for cultural preservation.
     */
    public function aiVoiceFormat(Request $request, OralTraditionVoiceService $voiceService)
    {
        $rawTranscript = (string) $request->input('raw_transcript', '');

        // If audio file is provided (e.g. uploaded voice memo)
        if ($request->hasFile('audio_file')) {
            $transcription = $voiceService->transcribeAudio($request->file('audio_file'));
            if (!empty($transcription['transcript'])) {
                $rawTranscript = $transcription['transcript'];
            }
        }

        if (empty(trim($rawTranscript))) {
            return response()->json([
                'success' => false,
                'error' => 'Please speak into the microphone or provide an audio recording.',
            ], 422);
        }

        $context = $request->only(['name', 'title', 'region', 'language', 'category', 'cultural_group']);
        $result = $voiceService->formatOralRecord($rawTranscript, $context);
        $result['raw_transcript'] = $rawTranscript;

        return response()->json($result);
    }

    /**
     * Submit a community contribution / revision (correction, additional context, alternative account).
     */
    public function submitContribution(Request $request, Culture $culture)
    {
        $request->validate([
            'revision_type' => 'required|string|in:correction,additional_context,alternative_account,additional_source,new_media,suggested_relationship',
            'title' => 'nullable|string|max:255',
            'proposed_content' => 'required|string',
            'evidence_notes' => 'nullable|string',
            'contributor_name' => 'nullable|string|max:255',
            'contributor_email' => 'nullable|email|max:255',
        ]);

        $revision = $culture->revisions()->create([
            'user_id' => Auth::id(),
            'contributor_name' => $request->input('contributor_name', Auth::check() ? Auth::user()->name : 'Anonymous Community Member'),
            'contributor_email' => $request->input('contributor_email', Auth::check() ? Auth::user()->email : null),
            'revision_type' => $request->input('revision_type'),
            'title' => $request->input('title'),
            'proposed_content' => $request->input('proposed_content'),
            'evidence_notes' => $request->input('evidence_notes'),
            'status' => 'submitted',
        ]);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Your contribution has been received for community review. Approved contributions will enrich this archive record.',
                'revision' => $revision,
            ]);
        }

        return redirect()->route('cultural-hub.show', $culture->id)
            ->with('success', 'Your contribution has been received for community review. Thank you for helping preserve living heritage accurately.');
    }

    /**
     * Update archive review status (draft, submitted, under_review, published, awaiting_contributions, disputed, restricted, archived).
     */
    public function updateReviewStatus(Request $request, Culture $culture)
    {
        $user = Auth::user();
        $isGuardian = $culture->submitted_by === $user->id;
        $isAdmin = (bool) $user->is_admin;

        if (!$isGuardian && !$isAdmin) {
            abort(403, 'Only recognized guardians or archive moderators can alter review status.');
        }

        $validStatuses = ['draft', 'submitted', 'under_review', 'published', 'awaiting_contributions', 'disputed', 'restricted', 'archived'];
        $request->validate([
            'review_status' => 'required|string|in:' . implode(',', $validStatuses),
            'notes' => 'nullable|string',
        ]);

        $status = $request->input('review_status');
        $culture->review_status = $status;
        if ($status === 'published') {
            $culture->status = 'approved';
        } elseif ($status === 'draft') {
            $culture->status = 'draft';
        }

        $culture->save();

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'review_status' => $culture->review_status,
                'badge' => $culture->review_badge,
            ]);
        }

        return redirect()->route('cultural-hub.show', $culture->id)
            ->with('success', "Archive record status updated to: {$culture->review_badge['label']}.");
    }

    /**
     * Ask the Cultural Archive Knowledge Companion grounded questions.
     */
    public function askCompanion(Request $request, Culture $culture, CulturalCompanionService $companionService)
    {
        $question = (string) $request->input('question', '');
        $result = $companionService->askCompanion($culture, $question);

        return response()->json($result);
    }
}