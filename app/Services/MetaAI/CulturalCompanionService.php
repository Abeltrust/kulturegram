<?php

namespace App\Services\MetaAI;

use App\Models\Culture;
use Illuminate\Support\Facades\Log;

class CulturalCompanionService
{
    protected MetaModelApiClient $client;
    protected string $model;

    public function __construct(MetaModelApiClient $client)
    {
        $this->client = $client;
        $this->model = (string) config('meta_ai.models.spark', 'muse-spark-1.3');
    }

    /**
     * Answer a visitor question grounded in an individual cultural record or archive records.
     *
     * @param Culture $culture The focal cultural record
     * @param string $question Visitor's query
     * @return array Standardized response with evidence grounding and transparency
     */
    public function askCompanion(Culture $culture, string $question): array
    {
        $cleanQuestion = trim($question);
        $timestamp = now()->toIso8601String();

        if (empty($cleanQuestion)) {
            return [
                'success' => false,
                'answer' => 'Please provide a question about this cultural record.',
                'grounded' => false,
                'cited_sources' => [],
                'missing_context' => null,
                'provenance' => [
                    'model' => $this->model,
                    'timestamp' => $timestamp,
                    'task' => 'cultural_companion_inquiry',
                ],
            ];
        }

        // Gather all grounded text from the record and its sources
        $culture->loadMissing(['sources', 'claims']);
        $groundingContext = [
            'title' => $culture->name,
            'cultural_group' => $culture->cultural_group,
            'region' => $culture->region,
            'language' => $culture->language,
            'category' => $culture->category,
            'description' => $culture->description,
            'historical_period' => $culture->historical_period,
            'significance' => $culture->significance,
            'rituals' => $culture->rituals,
            'community_role' => $culture->community_role,
            'transmission_methods' => $culture->transmission_methods,
            'raw_story' => $culture->raw_story,
            'audio_transcript' => $culture->audio_transcript,
            'sources' => $culture->sources->map(function ($s) {
                return [
                    'type' => $s->source_type,
                    'speaker' => $s->display_speaker,
                    'excerpt' => substr((string)$s->original_text, 0, 400),
                ];
            })->all(),
            'claims' => $culture->claims->map(function ($c) {
                return [
                    'text' => $c->claim_text,
                    'type' => $c->claim_type,
                    'review_status' => $c->review_status,
                ];
            })->all(),
        ];

        if (!$this->client->isConfigured()) {
            return $this->buildDeterministicCompanionResponse($culture, $cleanQuestion, $groundingContext);
        }

        $systemPrompt = <<<PROMPT
You are Kulturegram's Cultural Archive Companion.
Your sole job is to answer visitor questions strictly based on the provided archive dossier.

MANDATORY TRUTHFULNESS & GROUNDING RULES:
1. ONLY answer with information directly found in the archive record.
2. If the archive DOES NOT contain the answer, explicitly state: "The current archive record does not document this specific aspect." NEVER invent folklore, dates, genealogies, or rituals.
3. Clearly distinguish:
   - "Direct Testimony": verbatim quotes or recordings from community members.
   - "Documented Interpretation": contributor notes or historical framing.
4. If the question asks about something unknown, identify what further oral history or community review is needed.
5. Provide citations to the record title and specific section.
6. Return your response as JSON matching:
{
  "answer": "string",
  "grounded_in_record": boolean,
  "distinction": "Direct Testimony | Documented Interpretation | Archive Search",
  "cited_sections": ["string"],
  "unknown_aspects": "string or null"
}
PROMPT;

        $userPrompt = "ARCHIVE RECORD DATA:\n" . json_encode($groundingContext) . "\n\nVISITOR QUESTION:\n" . $cleanQuestion;

        $payload = [
            'model' => $this->model,
            'messages' => [
                ['role' => 'system', 'content' => $systemPrompt],
                ['role' => 'user', 'content' => $userPrompt],
            ],
            'temperature' => 0.1,
            'max_tokens' => 2000,
        ];

        $response = $this->client->postJson('chat/completions', $payload, $this->model);

        if (!$response['success']) {
            return $this->buildDeterministicCompanionResponse($culture, $cleanQuestion, $groundingContext);
        }

        $parsed = $this->parseResponse($response['data']);
        if (!$parsed) {
            return $this->buildDeterministicCompanionResponse($culture, $cleanQuestion, $groundingContext);
        }

        return [
            'success' => true,
            'answer' => $parsed['answer'] ?? 'No answer could be retrieved.',
            'grounded' => (bool) ($parsed['grounded_in_record'] ?? true),
            'distinction' => $parsed['distinction'] ?? 'Archive Record',
            'cited_sources' => $parsed['cited_sections'] ?? [$culture->name],
            'missing_context' => $parsed['unknown_aspects'] ?? null,
            'provenance' => [
                'model' => $this->model,
                'timestamp' => $timestamp,
                'task' => 'cultural_companion_inquiry',
                'latency_ms' => $response['latency_ms'] ?? 0,
            ],
        ];
    }

    protected function parseResponse(mixed $data): ?array
    {
        if (is_array($data) && isset($data['choices'][0]['message']['content'])) {
            $raw = trim($data['choices'][0]['message']['content']);
        } elseif (is_string($data)) {
            $raw = trim($data);
        } else {
            return null;
        }

        $raw = preg_replace('/^```(?:json)?\s*/i', '', $raw);
        $raw = preg_replace('/\s*```$/', '', $raw);

        $decoded = json_decode($raw, true);
        return is_array($decoded) ? $decoded : null;
    }

    protected function buildDeterministicCompanionResponse(Culture $culture, string $question, array $grounding): array
    {
        $qLower = strtolower($question);
        $answer = "This cultural record preserves the tradition of '{$culture->name}' from {$culture->region}.";

        if (str_contains($qLower, 'who') || str_contains($qLower, 'practitioner') || str_contains($qLower, 'people')) {
            $answer .= " Practitioners and custodians: " . ($culture->current_practitioners ?: 'Generational community members') . ".";
        } elseif (str_contains($qLower, 'significance') || str_contains($qLower, 'meaning') || str_contains($qLower, 'why')) {
            $answer .= " Cultural significance: " . ($culture->significance ?: $culture->description) . ".";
        } elseif (str_contains($qLower, 'language') || str_contains($qLower, 'speak')) {
            $answer .= " Primary language: " . ($culture->language ?: 'Indigenous local language') . ".";
        } else {
            $answer .= " Context: " . substr(strip_tags((string)$culture->description), 0, 250) . "...";
        }

        return [
            'success' => true,
            'answer' => $answer,
            'grounded' => true,
            'distinction' => 'Archive Record',
            'cited_sources' => ["Record ID #{$culture->id}: {$culture->name}"],
            'missing_context' => 'For deeper inquiries beyond this summary, community consultation and oral submissions are invited.',
            'provenance' => [
                'model' => 'deterministic_archival_retriever',
                'timestamp' => now()->toIso8601String(),
                'task' => 'cultural_companion_inquiry',
            ],
        ];
    }
}
