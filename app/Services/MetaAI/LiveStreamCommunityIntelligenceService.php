<?php

namespace App\Services\MetaAI;

use App\Models\Culture;
use App\Models\CulturalRecordDraft;
use App\Models\LiveStream;
use App\Models\LiveStreamMessage;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class LiveStreamCommunityIntelligenceService
{
    protected MetaModelApiClient $apiClient;

    public const MODEL_ID = 'meta/llama-3.3-70b-instruct';

    public function __construct(MetaModelApiClient $apiClient)
    {
        $this->apiClient = $apiClient;
    }

    /**
     * Generate or refresh the live conversation summary and cultural intelligence dossier.
     *
     * @param LiveStream $stream
     * @param bool $force
     * @return array
     */
    public function generateLiveSummary(LiveStream $stream, bool $force = false): array
    {
        // Rate-limit re-generation unless forced or older than 90 seconds
        if (!$force && $stream->summary_generated_at && $stream->summary_generated_at->gt(now()->subSeconds(90))) {
            return $this->buildDossierPayload($stream);
        }

        $messages = LiveStreamMessage::where('live_stream_id', $stream->id)
            ->whereIn('flag_status', ['clean', 'dismissed'])
            ->with('user:id,name,username')
            ->orderBy('id', 'asc')
            ->limit(100)
            ->get();

        if ($messages->isEmpty()) {
            return [
                'success' => true,
                'has_content' => false,
                'message' => 'No public messages yet to summarize.',
                'summary' => null,
                'unanswered_questions' => [],
                'cultural_references' => [],
                'current_direction' => 'Waiting for stream conversation to begin.',
                'ai_disclosure' => 'Summaries are generated with explicit separation of participant statements and AI analysis.',
            ];
        }

        // Format conversation transcript for model
        $transcriptLines = [];
        foreach ($messages as $msg) {
            $sender = $msg->user ? $msg->user->name : 'Participant';
            $transcriptLines[] = "{$sender}: {$msg->message}";
        }
        $transcriptText = implode("\n", $transcriptLines);

        $aiResult = null;
        if ($this->apiClient->isConfigured()) {
            $aiResult = $this->queryModelForSummary($stream, $transcriptText);
        }

        // Fallback / heuristic extractor if API offline or failed
        if (!$aiResult || empty($aiResult['success'])) {
            $aiResult = $this->fallbackExtraction($stream, $messages);
        }

        // Query relevant public heritage records in Kulturegram
        $culturalRefs = $this->findRelevantCulturalReferences($stream, $aiResult['key_topics'] ?? []);

        // Store structured summary on the stream
        $summaryData = [
            'overview' => $aiResult['ai_interpretation'],
            'current_direction' => $aiResult['current_direction'],
            'participant_highlights' => $aiResult['participant_statements'],
            'key_topics' => $aiResult['key_topics'] ?? [],
        ];

        $stream->update([
            'ai_summary' => json_encode($summaryData),
            'unanswered_questions' => $aiResult['unanswered_questions'],
            'cultural_references' => $culturalRefs,
            'summary_generated_at' => now(),
            'summary_status' => 'ready',
        ]);

        return $this->buildDossierPayload($stream->fresh());
    }

    /**
     * Get the catch-up dossier for late joiners or viewers.
     */
    public function getCatchUpDossier(LiveStream $stream, ?User $viewer = null): array
    {
        // If never generated or stream has new messages, trigger generation
        if (!$stream->summary_generated_at || $stream->summary_status !== 'ready') {
            return $this->generateLiveSummary($stream);
        }

        return $this->buildDossierPayload($stream);
    }

    /**
     * Query hosted Meta Llama 3.3 70B Instruct for conversational intelligence.
     */
    protected function queryModelForSummary(LiveStream $stream, string $transcriptText): ?array
    {
        $systemPrompt = "You are Kulturegram AI Community Intelligence, a cultural preservation assistant.
Analyze this public cultural livestream discussion.
Strict Guidelines:
1. Maintain strict separation between statements actually made by participants and your own AI interpretation.
2. Under 'participant_statements', list direct quotes or views verbatim with speaker attribution. Never invent words people did not say.
3. Under 'ai_interpretation', provide a concise objective summary of the cultural themes being discussed.
4. Under 'current_direction', summarize what participants are presently discussing or asking.
5. Under 'unanswered_questions', extract specific inquiries about traditions, languages, historical rituals, and cultural practices that remain unanswered.
6. Under 'key_topics', list 3-6 relevant cultural keywords/concepts.
Return ONLY valid JSON matching this schema:
{
  \"participant_statements\": [{\"speaker\": \"string\", \"statement\": \"string\"}],
  \"ai_interpretation\": \"string\",
  \"current_direction\": \"string\",
  \"unanswered_questions\": [\"string\"],
  \"key_topics\": [\"string\"]
}";

        $userPrompt = "Livestream Title: {$stream->title}\nCategory: {$stream->category}\nDescription: {$stream->description}\n\nTranscript:\n{$transcriptText}";

        $payload = [
            'model' => self::MODEL_ID,
            'messages' => [
                ['role' => 'system', 'content' => $systemPrompt],
                ['role' => 'user', 'content' => $userPrompt],
            ],
            'temperature' => 0.2,
            'max_tokens' => 1000,
        ];

        $response = $this->apiClient->postJson('/chat/completions', $payload, self::MODEL_ID);

        if (!$response['success'] || empty($response['data']['choices'][0]['message']['content'])) {
            return null;
        }

        $rawContent = trim($response['data']['choices'][0]['message']['content']);
        // Strip markdown code fences if model returned ```json ... ```
        if (preg_match('/```(?:json)?\s*([\s\S]*?)\s*```/', $rawContent, $matches)) {
            $rawContent = trim($matches[1]);
        }

        $parsed = json_decode($rawContent, true);
        if (!is_array($parsed) || empty($parsed['ai_interpretation'])) {
            return null;
        }

        return [
            'success' => true,
            'participant_statements' => $parsed['participant_statements'] ?? [],
            'ai_interpretation' => $parsed['ai_interpretation'],
            'current_direction' => $parsed['current_direction'] ?? 'Discussion ongoing.',
            'unanswered_questions' => $parsed['unanswered_questions'] ?? [],
            'key_topics' => $parsed['key_topics'] ?? [],
        ];
    }

    /**
     * Resilient fallback extraction using local deterministic parsing.
     */
    protected function fallbackExtraction(LiveStream $stream, $messages): array
    {
        $participantStatements = [];
        $unansweredQuestions = [];
        $topics = array_filter([$stream->category, $stream->title]);

        foreach ($messages as $msg) {
            $sender = $msg->user ? $msg->user->name : 'Community Member';
            $text = trim($msg->message);

            // Question extraction: detect interrogatives and question marks
            if (str_contains($text, '?') || preg_match('/\b(how|what|why|who|when|where|which|meaning of|tradition of)\b/i', $text)) {
                if (strlen($text) >= 15 && count($unansweredQuestions) < 5) {
                    $unansweredQuestions[] = "{$sender} asked: \"{$text}\"";
                }
            }

            // Participant statements
            if (count($participantStatements) < 4 && strlen($text) > 20) {
                $participantStatements[] = [
                    'speaker' => $sender,
                    'statement' => $text,
                ];
            }
        }

        // Add cultural topic keywords from category and description
        if ($stream->description) {
            $words = array_filter(explode(' ', strtolower($stream->description)), fn($w) => strlen($w) > 5);
            $topics = array_unique(array_merge($topics, array_slice($words, 0, 4)));
        }

        $count = $messages->count();
        $aiInterpretation = "Discussion focused on {$stream->title} with {$count} community contributions covering {$stream->category} practices.";
        $currentDirection = "Participants are currently sharing local cultural perspectives on {$stream->title}.";

        return [
            'success' => true,
            'participant_statements' => $participantStatements,
            'ai_interpretation' => $aiInterpretation,
            'current_direction' => $currentDirection,
            'unanswered_questions' => $unansweredQuestions,
            'key_topics' => array_values($topics),
        ];
    }

    /**
     * Find relevant public Kulturegram heritage records to recommend in discussion.
     */
    public function findRelevantCulturalReferences(LiveStream $stream, array $topics = []): array
    {
        $keywords = array_filter(array_merge(
            [$stream->category, $stream->title],
            $topics
        ));

        $query = Culture::where('status', 'approved');

        if (!empty($keywords)) {
            $query->where(function ($q) use ($keywords) {
                foreach ($keywords as $kw) {
                    if (strlen($kw) >= 3) {
                        $q->orWhere('name', 'like', "%{$kw}%")
                          ->orWhere('category', 'like', "%{$kw}%")
                          ->orWhere('language', 'like', "%{$kw}%")
                          ->orWhere('region', 'like', "%{$kw}%");
                    }
                }
            });
        }

        $records = $query->limit(4)->get(['id', 'name', 'category', 'region', 'language', 'image', 'description']);

        return $records->map(function ($record) {
            return [
                'id' => $record->id,
                'name' => $record->name,
                'category' => $record->category,
                'region' => $record->region,
                'language' => $record->language,
                'image' => $record->image,
                'snippet' => \Illuminate\Support\Str::limit($record->description, 100),
                'url' => route('cultural-hub.show', $record->id),
                'relevance_explanation' => "Referenced because this record documents {$record->category} traditions from {$record->region}.",
            ];
        })->toArray();
    }

    /**
     * Extract a draft cultural heritage record from an authorized livestream for community review.
     * (AI-Assisted Collective Cultural Preservation Workflow)
     */
    public function extractDraftRecord(LiveStream $stream, User $contributor): CulturalRecordDraft
    {
        $messages = LiveStreamMessage::where('live_stream_id', $stream->id)
            ->whereIn('flag_status', ['clean', 'dismissed'])
            ->with('user:id,name')
            ->orderBy('id', 'asc')
            ->get();

        $testimonies = [];
        foreach ($messages as $msg) {
            $sender = $msg->user ? $msg->user->name : 'Participant';
            $testimonies[] = "{$sender}: {$msg->message}";
        }
        $rawStory = "Source: Live Discussion \"{$stream->title}\"\nHost: " . ($stream->host ? $stream->host->name : 'Community') . "\nDate: " . now()->toFormattedDateString() . "\n\nTestimonies & Transcript:\n" . implode("\n", $testimonies);

        // Fetch or generate summary and unanswered questions
        $dossier = $this->generateLiveSummary($stream);
        $summary = $dossier['summary'];
        $unanswered = $dossier['unanswered_questions'] ?? [];
        $links = $dossier['cultural_references'] ?? [];

        return CulturalRecordDraft::create([
            'live_stream_id' => $stream->id,
            'contributor_id' => $contributor->id,
            'title' => $stream->title . ' (Community Archive Draft)',
            'category' => $stream->category ?? 'Oral Tradition',
            'region' => 'Documented via Community Livestream',
            'language' => 'Multilingual / Local Dialects',
            'extracted_story' => $rawStory,
            'ai_summary' => is_array($summary) ? ($summary['overview'] ?? '') : (string) $summary,
            'unanswered_questions' => $unanswered,
            'suggested_links' => $links,
            'custodian_notes' => 'Awaiting community elder and custodian verification before formal archive moderation.',
            'status' => 'draft',
        ]);
    }

    /**
     * Build the standardized response payload with transparency labels.
     */
    protected function buildDossierPayload(LiveStream $stream): array
    {
        $summaryData = json_decode($stream->ai_summary, true);
        if (!is_array($summaryData)) {
            $summaryData = [
                'overview' => $stream->ai_summary,
                'current_direction' => 'Discussion ongoing.',
                'participant_highlights' => [],
                'key_topics' => [],
            ];
        }

        $totalMessages = LiveStreamMessage::where('live_stream_id', $stream->id)
            ->whereIn('flag_status', ['clean', 'dismissed'])
            ->count();

        return [
            'success' => true,
            'has_content' => true,
            'stream_id' => $stream->id,
            'stream_title' => $stream->title,
            'summary' => $summaryData,
            'unanswered_questions' => $stream->unanswered_questions ?? [],
            'cultural_references' => $stream->cultural_references ?? [],
            'generated_at' => $stream->summary_generated_at?->toIso8601String(),
            'total_messages_analyzed' => $totalMessages,
            'ai_disclosure' => 'AI-generated synthesis. Participant statements are quoted verbatim; interpretive synthesis is generated by AI.',
            'model_used' => self::MODEL_ID,
        ];
    }
}
