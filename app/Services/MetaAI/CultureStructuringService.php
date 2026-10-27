<?php

namespace App\Services\MetaAI;

use App\Models\Culture;
use Illuminate\Support\Facades\Log;

class CultureStructuringService
{
    protected MetaModelApiClient $client;
    protected string $model;

    public function __construct(MetaModelApiClient $client)
    {
        $this->client = $client;
        $this->model = (string) config('meta_ai.models.spark', 'muse-spark-1.3');
    }

    /**
     * Propose a structured archival record from submitted source material.
     *
     * @param string $sourceText Raw oral testimony or field account
     * @param array $context Hints such as contributor name, region, language, category
     * @return array Standardized result envelope with traceable provenance
     */
    public function structureRecord(string $sourceText, array $context = []): array
    {
        $cleanSource = trim($sourceText);
        $timestamp = now()->toIso8601String();

        if (empty($cleanSource)) {
            return [
                'success' => false,
                'configured' => $this->client->isConfigured(),
                'data' => null,
                'provenance' => [
                    'task_type' => 'record_structuring',
                    'model' => $this->model,
                    'timestamp' => $timestamp,
                    'status' => 'failed_empty_input',
                ],
                'error' => 'Source narrative or testimony cannot be empty.',
            ];
        }

        if (!$this->client->isConfigured()) {
            return [
                'success' => true,
                'configured' => false,
                'data' => $this->buildDeterministicStructure($cleanSource, $context),
                'provenance' => [
                    'task_type' => 'record_structuring',
                    'model' => 'deterministic_fallback',
                    'timestamp' => $timestamp,
                    'status' => 'unconfigured_fallback',
                ],
                'error' => null,
            ];
        }

        $systemPrompt = <<<PROMPT
You are a cultural archivist assisting in structuring living heritage records for Kulturegram.
CRITICAL PRESERVATION & TRUTHFULNESS MANDATES:
1. ONLY extract information directly grounded in the source text.
2. If any field cannot be verified from the text, return explicitly "unknown" or "review_needed". NEVER guess or invent traditions, dates, or origins.
3. Distinguish verbatim testimony from interpretations.
4. Extract 2-5 distinct claims with the exact source excerpt supporting each claim.
5. Categorize accurately and identify mentioned names, places, and cultural practices.
6. List unresolved questions and missing information for human community review.
7. Return strictly valid JSON with no markdown tags.

JSON schema:
{
  "proposed_title": "string",
  "introduction": "string (concise 2-3 sentence overview)",
  "category": "string (Traditional Crafts, Oral Traditions, Music & Dance, Culinary Heritage, Religious Practices, Festivals & Ceremonies, Language & Literature, Architecture & Design, Agricultural Practices, Healing Traditions, Social Customs, Art & Aesthetics, Games & Sports, Clothing & Textiles)",
  "subtype": "string or unknown",
  "cultural_group": "string or unknown",
  "language": "string or unknown",
  "region": "string or unknown",
  "historical_period": "string or unknown",
  "key_themes": ["string"],
  "names_mentioned": ["string"],
  "places_mentioned": ["string"],
  "cultural_practices": "string or unknown",
  "transmission_methods": "string or unknown",
  "claims": [
    {
      "claim_text": "string",
      "claim_type": "quoted_testimony|contributor_interpretation|historical_fact|oral_tradition",
      "source_excerpt": "string (exact quote from source)",
      "review_status": "unverified"
    }
  ],
  "missing_information": ["string"],
  "follow_up_inquiries": ["string"]
}
PROMPT;

        $userPrompt = "CONTEXT HINTS: " . json_encode($context) . "\n\nSOURCE TESTIMONY / TEXT:\n" . $cleanSource;

        $payload = [
            'model' => $this->model,
            'messages' => [
                ['role' => 'system', 'content' => $systemPrompt],
                ['role' => 'user', 'content' => $userPrompt],
            ],
            'temperature' => 0.1, // Strict factual adherence
            'max_tokens' => 3000,
        ];

        $response = $this->client->postJson('chat/completions', $payload, $this->model);

        if (!$response['success']) {
            return [
                'success' => true,
                'configured' => true,
                'data' => $this->buildDeterministicStructure($cleanSource, $context),
                'provenance' => [
                    'task_type' => 'record_structuring',
                    'model' => 'deterministic_fallback_after_api_error',
                    'timestamp' => $timestamp,
                    'status' => 'fallback',
                    'api_error' => $response['error'] ?? 'API error',
                ],
                'error' => null,
            ];
        }

        $parsed = $this->parseJsonResponse($response['data']);
        if (!$parsed) {
            return [
                'success' => true,
                'configured' => true,
                'data' => $this->buildDeterministicStructure($cleanSource, $context),
                'provenance' => [
                    'task_type' => 'record_structuring',
                    'model' => 'deterministic_fallback_unparseable',
                    'timestamp' => $timestamp,
                    'status' => 'fallback',
                ],
                'error' => null,
            ];
        }

        return [
            'success' => true,
            'configured' => true,
            'data' => $parsed,
            'provenance' => [
                'task_type' => 'record_structuring',
                'model' => $this->model,
                'timestamp' => $timestamp,
                'status' => 'ai_generated',
                'latency_ms' => $response['latency_ms'] ?? 0,
            ],
            'error' => null,
        ];
    }

    /**
     * Parse JSON from AI response payload safely.
     */
    protected function parseJsonResponse(mixed $data): ?array
    {
        if (is_array($data) && isset($data['choices'][0]['message']['content'])) {
            $raw = trim($data['choices'][0]['message']['content']);
        } elseif (is_string($data)) {
            $raw = trim($data);
        } else {
            return null;
        }

        // Strip Markdown codeblocks if present
        $raw = preg_replace('/^```(?:json)?\s*/i', '', $raw);
        $raw = preg_replace('/\s*```$/', '', $raw);

        $decoded = json_decode($raw, true);
        return is_array($decoded) ? $decoded : null;
    }

    /**
     * Deterministic, transparent fallback that preserves original testimony without hallucination.
     */
    protected function buildDeterministicStructure(string $sourceText, array $context): array
    {
        $sentences = preg_split('/(?<=[.?!])\s+/', $sourceText, -1, PREG_SPLIT_NO_EMPTY);
        $firstSentence = !empty($sentences) ? $sentences[0] : 'Cultural Testimony';
        $intro = count($sentences) > 1 ? implode(' ', array_slice($sentences, 0, 2)) : $firstSentence;

        $words = preg_split('/\s+/', $sourceText);
        $title = count($words) > 6 ? implode(' ', array_slice($words, 0, 6)) . '...' : $sourceText;

        return [
            'proposed_title' => $context['title'] ?? $title,
            'introduction' => $intro,
            'category' => $context['category'] ?? 'Oral Traditions',
            'subtype' => 'review_needed',
            'cultural_group' => $context['cultural_group'] ?? 'unknown',
            'language' => $context['language'] ?? 'unknown',
            'region' => $context['region'] ?? 'unknown',
            'historical_period' => 'unknown',
            'key_themes' => ['Living Heritage', 'Oral History'],
            'names_mentioned' => [],
            'places_mentioned' => !empty($context['region']) ? [$context['region']] : [],
            'cultural_practices' => 'review_needed',
            'transmission_methods' => 'Oral narration and community testimony',
            'claims' => [
                [
                    'claim_text' => $firstSentence,
                    'claim_type' => 'quoted_testimony',
                    'source_excerpt' => substr($firstSentence, 0, 160),
                    'review_status' => 'unverified',
                ],
            ],
            'missing_information' => [
                'Specific community custodian lineage to be confirmed by local elders.',
                'Associated seasonal or ceremonial calendar details.',
            ],
            'follow_up_inquiries' => [
                'In what context is this account traditionally recounted?',
                'Who are the recognized knowledge keepers for this tradition?',
            ],
        ];
    }
}
