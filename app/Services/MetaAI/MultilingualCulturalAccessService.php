<?php

namespace App\Services\MetaAI;

use Illuminate\Support\Facades\Log;

class MultilingualCulturalAccessService
{
    protected MetaModelApiClient $apiClient;

    public const MODEL_ID = 'meta/llama-3.3-70b-instruct';

    public function __construct(MetaModelApiClient $apiClient)
    {
        $this->apiClient = $apiClient;
    }

    /**
     * Generate an AI-assisted translation of a cultural summary while strictly preserving original text.
     */
    public function translateSummary(string $originalText, string $targetLanguage, string $culturalContext = ''): array
    {
        if (empty(trim($originalText))) {
            return [
                'success' => false,
                'error' => 'No text provided for translation.',
                'original_text' => '',
                'translated_text' => '',
            ];
        }

        if (!$this->apiClient->isConfigured()) {
            return [
                'success' => false,
                'original_text' => $originalText,
                'translated_text' => $originalText,
                'target_language' => $targetLanguage,
                'error' => 'Multilingual translation service unavailable (Meta API key not configured). Original text preserved.',
                'is_ai_generated' => false,
            ];
        }

        $systemPrompt = "You are Kulturegram's Multilingual Cultural Preservation Translator.
Your job is to translate the following cultural heritage description into {$targetLanguage}.
Strict Guidelines:
1. Preserve sacred terms, indigenous proper nouns, deity names, place names, and cultural idioms in their authentic form.
2. If an indigenous word has no direct equivalent, include the indigenous term in brackets with a brief contextual explanation.
3. Never alter the cultural meaning, historical facts, or personal testimony.
Return ONLY valid JSON matching this schema:
{
  \"translated_text\": \"string\",
  \"cultural_notes\": [\"string\"]
}";

        $userPrompt = "Cultural Context: {$culturalContext}\nTarget Language: {$targetLanguage}\nOriginal Text:\n{$originalText}";

        $payload = [
            'model' => self::MODEL_ID,
            'messages' => [
                ['role' => 'system', 'content' => $systemPrompt],
                ['role' => 'user', 'content' => $userPrompt],
            ],
            'temperature' => 0.2,
            'max_tokens' => 800,
        ];

        $response = $this->apiClient->postJson('/chat/completions', $payload, self::MODEL_ID);

        if (!$response['success'] || empty($response['data']['choices'][0]['message']['content'])) {
            return [
                'success' => false,
                'original_text' => $originalText,
                'translated_text' => $originalText,
                'target_language' => $targetLanguage,
                'error' => $response['error'] ?? 'Translation service encountered an error. Original text preserved.',
                'is_ai_generated' => false,
            ];
        }

        $rawContent = trim($response['data']['choices'][0]['message']['content']);
        if (preg_match('/```(?:json)?\s*([\s\S]*?)\s*```/', $rawContent, $matches)) {
            $rawContent = trim($matches[1]);
        }

        $parsed = json_decode($rawContent, true);
        $translatedText = is_array($parsed) && !empty($parsed['translated_text'])
            ? $parsed['translated_text']
            : $rawContent;

        return [
            'success' => true,
            'original_text' => $originalText,
            'translated_text' => $translatedText,
            'target_language' => $targetLanguage,
            'cultural_notes' => $parsed['cultural_notes'] ?? [],
            'is_ai_generated' => true,
            'ai_label' => "AI-Assisted Translation into {$targetLanguage} (Original text preserved for authentic reference)",
            'model_used' => self::MODEL_ID,
        ];
    }

    /**
     * Adapt cultural descriptions for different reading levels (e.g., student/learner, concise elder overview).
     */
    public function adaptReadingLevel(string $originalText, string $level = 'learner'): array
    {
        if (empty(trim($originalText))) {
            return [
                'success' => false,
                'error' => 'No text provided.',
                'original_text' => '',
                'adapted_text' => '',
            ];
        }

        $levelPrompt = match ($level) {
            'learner' => 'Rewrite this cultural account using clear, engaging language suitable for younger readers and cultural learners. Keep indigenous terminology and define terms in simple words.',
            'concise' => 'Create a concise, structured bulleted summary of this cultural account highlighting key rites, historical origin, community role, and current preservation status.',
            default => 'Provide an accessible educational summary of this cultural record while preserving all cultural nuances.',
        };

        if (!$this->apiClient->isConfigured()) {
            return [
                'success' => true,
                'original_text' => $originalText,
                'adapted_text' => $originalText,
                'level' => $level,
                'is_ai_generated' => false,
                'note' => 'Original text rendered directly.',
            ];
        }

        $payload = [
            'model' => self::MODEL_ID,
            'messages' => [
                ['role' => 'system', 'content' => "You are Kulturegram Educational AI. {$levelPrompt}"],
                ['role' => 'user', 'content' => $originalText],
            ],
            'temperature' => 0.3,
            'max_tokens' => 600,
        ];

        $response = $this->apiClient->postJson('/chat/completions', $payload, self::MODEL_ID);

        $adaptedText = $originalText;
        if ($response['success'] && !empty($response['data']['choices'][0]['message']['content'])) {
            $adaptedText = trim($response['data']['choices'][0]['message']['content']);
        }

        return [
            'success' => true,
            'original_text' => $originalText,
            'adapted_text' => $adaptedText,
            'level' => $level,
            'is_ai_generated' => true,
            'ai_label' => "AI Adapted Reading Level ({$level}) — Original testimony preserved unchanged.",
        ];
    }
}
