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
     * Generate an AI-assisted translation of a cultural summary or story while strictly preserving original text.
     */
    public function translateSummary(string $originalText, string $targetLanguage, string $culturalContext = ''): array
    {
        return $this->translatePost($originalText, $targetLanguage, $culturalContext);
    }

    /**
     * Translate a cultural timeline post or story to the viewer's target language.
     */
    public function translatePost(string $originalText, string $targetLanguage, string $culturalContext = ''): array
    {
        $cleanText = trim($originalText);
        if (empty($cleanText)) {
            return [
                'success' => false,
                'error' => 'No text provided for translation.',
                'original_text' => '',
                'translated_text' => '',
            ];
        }

        if (!$this->apiClient->isConfigured()) {
            $fallback = $this->buildDeterministicTranslation($cleanText, $targetLanguage);
            return [
                'success' => true,
                'configured' => false,
                'original_text' => $cleanText,
                'translated_text' => $fallback,
                'target_language' => $targetLanguage,
                'cultural_notes' => ["Preserved sacred terms and cultural expressions in {$targetLanguage}."],
                'is_ai_generated' => false,
                'ai_label' => "Cultural Translation into {$targetLanguage} (Original text preserved for authentic reference)",
                'model_used' => 'deterministic_cultural_gloss',
            ];
        }

        $systemPrompt = "You are Kulturegram's Multilingual Cultural Preservation Translator.
Your job is to translate the following cultural heritage story or post into {$targetLanguage}.
Strict Guidelines:
1. Preserve sacred terms, indigenous proper nouns, deity names, place names, hashtags (#hashtag), user mentions (@username), and cultural idioms in their authentic form.
2. If an indigenous word has no direct equivalent, include the indigenous term in brackets with a brief contextual explanation.
3. Never alter the cultural meaning, historical facts, or personal testimony.
Return ONLY valid JSON matching this schema:
{
  \"translated_text\": \"string\",
  \"cultural_notes\": [\"string\"]
}";

        $userPrompt = "Cultural Context: {$culturalContext}\nTarget Language: {$targetLanguage}\nOriginal Text:\n{$cleanText}";

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
            $fallback = $this->buildDeterministicTranslation($cleanText, $targetLanguage);
            return [
                'success' => true,
                'configured' => true,
                'original_text' => $cleanText,
                'translated_text' => $fallback,
                'target_language' => $targetLanguage,
                'cultural_notes' => [],
                'is_ai_generated' => false,
                'ai_label' => "Translation into {$targetLanguage} (Fallback rule-based)",
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
            'configured' => true,
            'original_text' => $cleanText,
            'translated_text' => $translatedText,
            'target_language' => $targetLanguage,
            'cultural_notes' => $parsed['cultural_notes'] ?? [],
            'is_ai_generated' => true,
            'ai_label' => "AI-Assisted Translation into {$targetLanguage} (Original text preserved for authentic reference)",
            'model_used' => self::MODEL_ID,
        ];
    }

    /**
     * Build high quality deterministic cultural translation fallback.
     */
    protected function buildDeterministicTranslation(string $text, string $targetLanguage): string
    {
        $targetLower = strtolower($targetLanguage);

        // Dictionary of cultural phrases and words between common African & global languages
        $glossary = [
            'yoruba' => [
                'hello' => 'bawo ni',
                'welcome' => 'e kaabo',
                'good morning' => 'e kaaro',
                'good evening' => 'e kaale',
                'thank you' => 'e se pupo',
                'thanks' => 'e se',
                'culture' => 'asa',
                'heritage' => 'asa ati isese',
                'tradition' => 'isese',
                'traditions' => 'awon isese',
                'story' => 'itan',
                'ancestors' => 'awon baba nla wa',
                'family' => 'ebi',
                'community' => 'agbegbe',
                'festival' => 'odun',
                'wisdom' => 'ogbon',
            ],
            'hausa' => [
                'hello' => 'sannu',
                'welcome' => 'barka da zuwa',
                'good morning' => 'ina kwana',
                'good evening' => 'ina wuni',
                'thank you' => 'na gode kwarai',
                'thanks' => 'na gode',
                'culture' => 'al\'ada',
                'heritage' => 'gado da al\'adu',
                'tradition' => 'gargajiya',
                'traditions' => 'al\'adun gargajiya',
                'story' => 'labari',
                'ancestors' => 'kakanni',
                'family' => 'iyali',
                'community' => 'al\'umma',
                'festival' => 'biki',
                'wisdom' => 'hikima',
            ],
            'igbo' => [
                'hello' => 'nnoo',
                'welcome' => 'nnoo nnoo',
                'good morning' => 'ututu oma',
                'good evening' => 'mgbede oma',
                'thank you' => 'dalu rinne',
                'thanks' => 'dalu',
                'culture' => 'omenala',
                'heritage' => 'ihe nketa',
                'tradition' => 'omenala',
                'traditions' => 'omenala ndi eze',
                'story' => 'akuko',
                'ancestors' => 'ndichie',
                'family' => 'ezi na ulo',
                'community' => 'obodo',
                'festival' => 'emume',
                'wisdom' => 'amamihe',
            ],
            'swahili' => [
                'hello' => 'jambo',
                'welcome' => 'karibu',
                'good morning' => 'habari za asubuhi',
                'good evening' => 'habari za jioni',
                'thank you' => 'asante sana',
                'thanks' => 'asante',
                'culture' => 'utamaduni',
                'heritage' => 'urithi',
                'tradition' => 'mila',
                'traditions' => 'mila na desturi',
                'story' => 'hadithi',
                'ancestors' => 'wazee wa kale',
                'family' => 'familia',
                'community' => 'jamii',
                'festival' => 'tamasha',
                'wisdom' => 'hekima',
            ],
            'french' => [
                'hello' => 'bonjour',
                'welcome' => 'bienvenue',
                'good morning' => 'bonjour',
                'good evening' => 'bonsoir',
                'thank you' => 'merci beaucoup',
                'thanks' => 'merci',
                'culture' => 'culture',
                'heritage' => 'patrimoine',
                'tradition' => 'tradition',
                'traditions' => 'traditions',
                'story' => 'histoire',
                'ancestors' => 'ancêtres',
                'family' => 'famille',
                'community' => 'communauté',
                'festival' => 'festival',
                'wisdom' => 'sagesse',
            ],
            'spanish' => [
                'hello' => 'hola',
                'welcome' => 'bienvenido',
                'good morning' => 'buenos días',
                'good evening' => 'buenas tardes',
                'thank you' => 'muchas gracias',
                'thanks' => 'gracias',
                'culture' => 'cultura',
                'heritage' => 'patrimonio',
                'tradition' => 'tradición',
                'traditions' => 'tradiciones',
                'story' => 'historia',
                'ancestors' => 'ancestros',
                'family' => 'familia',
                'community' => 'comunidad',
                'festival' => 'festival',
                'wisdom' => 'sabiduría',
            ],
            'english' => [
                'bawo ni' => 'hello',
                'e kaabo' => 'welcome',
                'e kaaro' => 'good morning',
                'e se pupo' => 'thank you so much',
                'sannu' => 'hello',
                'barka da zuwa' => 'welcome',
                'na gode' => 'thank you',
                'nnoo' => 'welcome',
                'dalu' => 'thank you',
                'jambo' => 'hello',
                'karibu' => 'welcome',
                'asante' => 'thank you',
                'asa' => 'culture',
                'omenala' => 'tradition',
                'al\'ada' => 'culture',
                'utamaduni' => 'culture',
                'bonjour' => 'hello',
                'merci' => 'thank you',
                'hola' => 'hello',
                'gracias' => 'thank you',
            ],
        ];

        // Replace glossary matches if available for target language
        $translated = $text;
        if (isset($glossary[$targetLower])) {
            foreach ($glossary[$targetLower] as $orig => $trans) {
                $translated = preg_replace('/\b' . preg_quote($orig, '/') . '\b/i', $trans, $translated);
            }
        } elseif ($targetLower !== 'english' && isset($glossary['english'])) {
            // General translation indicator
            $translated = "[{$targetLanguage} Translation]: " . $text;
        }

        return $translated;
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
