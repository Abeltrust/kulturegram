<?php

namespace App\Services\MetaAI;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MuseImageService
{
    protected MetaModelApiClient $client;
    protected string $model;

    public function __construct(MetaModelApiClient $client)
    {
        $this->client = $client;
        $this->model = (string) config('meta_ai.models.image', 'muse-image');
    }

    /**
     * Generate a respectful cultural illustration.
     *
     * @param string $story Narrative or description to base the illustration on
     * @param string|null $culturalContext Region or culture name
     * @param string|null $customPrompt Optional additional art direction from contributor
     * @return array Standardized result envelope
     */
    public function generateIllustration(string $story, ?string $culturalContext = null, ?string $customPrompt = null): array
    {
        if (!$this->client->isConfigured()) {
            return [
                'success' => false,
                'configured' => false,
                'image_path' => null,
                'image_url' => null,
                'prompt' => null,
                'model' => $this->model,
                'error' => 'Meta Model API key is not configured. Please add META_MODEL_API_KEY to your .env file.',
                'latency_ms' => 0.0,
            ];
        }

        $prompt = $this->buildIllustrationPrompt($story, $culturalContext, $customPrompt);

        $payload = [
            'model' => $this->model,
            'prompt' => $prompt,
            'n' => 1,
            'size' => '1024x1024',
            'response_format' => 'b64_json', // Or url
        ];

        $response = $this->client->postJson('images/generations', $payload, $this->model);

        if (!$response['success']) {
            return [
                'success' => false,
                'configured' => true,
                'image_path' => null,
                'image_url' => null,
                'prompt' => $prompt,
                'model' => $this->model,
                'error' => $response['error'] ?? 'Muse Image illustration generation failed.',
                'status_code' => $response['status_code'],
                'latency_ms' => $response['latency_ms'],
            ];
        }

        // Store image data securely
        $imageData = $this->extractAndStoreImage($response['data']);

        if (!$imageData) {
            return [
                'success' => false,
                'configured' => true,
                'image_path' => null,
                'image_url' => null,
                'prompt' => $prompt,
                'model' => $this->model,
                'error' => 'Failed to parse and store illustration image data returned by Muse Image.',
                'status_code' => 200,
                'latency_ms' => $response['latency_ms'],
            ];
        }

        return [
            'success' => true,
            'configured' => true,
            'image_path' => $imageData['path'],
            'image_url' => $imageData['url'],
            'prompt' => $prompt,
            'model' => $this->model,
            'is_ai_generated' => true,
            'attribution_disclaimer' => 'AI-Generated Cultural Illustration. This is an artistic representation and not an authentic historical photograph.',
            'error' => null,
            'status_code' => 200,
            'latency_ms' => $response['latency_ms'],
        ];
    }

    /**
     * Build an ethical, culturally respectful prompt.
     */
    protected function buildIllustrationPrompt(string $story, ?string $culturalContext, ?string $customPrompt): string
    {
        $context = $culturalContext ? "representing the heritage of {$culturalContext}" : "representing traditional cultural heritage";
        $excerpt = Str::limit(strip_tags($story), 300);

        $prompt = "A respectful and beautiful artistic illustration {$context}: \"{$excerpt}\". Traditional heritage artwork style, vibrant earth tones, dignifying cultural representation, museum-quality visual documentation.";

        if (!empty($customPrompt)) {
            $prompt .= " Additional focus: " . strip_tags($customPrompt);
        }

        return $prompt;
    }

    /**
     * Securely store returned image bytes into public disk.
     */
    protected function extractAndStoreImage(array $data): ?array
    {
        $bytes = null;

        // Check b64_json
        if (!empty($data['data'][0]['b64_json'])) {
            $bytes = base64_decode($data['data'][0]['b64_json']);
        } elseif (!empty($data['data'][0]['url'])) {
            // Fetch safely
            $imageUrl = $data['data'][0]['url'];
            try {
                $download = Http::timeout(20)->get($imageUrl);
                if ($download->successful()) {
                    $bytes = $download->body();
                }
            } catch (\Throwable $e) {
                Log::warning('[MuseImage] Failed to download generated image URL', ['error' => $e->getMessage()]);
                return null;
            }
        }

        if (empty($bytes)) {
            return null;
        }

        $filename = 'cultures/illustrations/ai_illustration_' . Str::uuid() . '.png';
        Storage::disk('public')->put($filename, $bytes);

        return [
            'path' => $filename,
            'url' => Storage::disk('public')->url($filename),
        ];
    }
}
