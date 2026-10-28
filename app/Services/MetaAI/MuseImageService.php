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
     * Generate an authentic cultural event poster/flyer with exact real event details, photos, and logos.
     */
    public function generateFlyer(
        string $title,
        ?string $description = null,
        ?string $type = null,
        ?string $culturalContext = null,
        array $details = []
    ): array {
        $occasion = $details['occasion'] ?? $type ?? 'Cultural Gathering';
        $context = $culturalContext ? "representing {$culturalContext} traditions" : "celebrating traditional cultural heritage";
        $prompt = "A stunning cultural event poster background for '{$title}' ({$occasion}), {$context}. Traditional heritage artwork, rich ceremonial motifs, high-resolution graphic design.";

        Storage::disk('public')->makeDirectory('events/flyers');

        // 1. If the browser canvas rendered a high-resolution custom PNG flyer (with exact user photo, logo, date, time, venue), store and return it directly
        if (!empty($details['rendered_flyer_data_url']) && is_string($details['rendered_flyer_data_url'])) {
            if (preg_match('/^data:image\/(png|jpeg|jpg|webp);base64,(.+)$/i', $details['rendered_flyer_data_url'], $m)) {
                $ext = strtolower($m[1]) === 'jpeg' ? 'jpg' : strtolower($m[1]);
                $bytes = base64_decode($m[2]);
                if ($bytes && strlen($bytes) > 200) {
                    $filename = 'events/flyers/ai_flyer_' . Str::uuid() . '.' . $ext;
                    Storage::disk('public')->put($filename, $bytes);

                    return [
                        'success' => true,
                        'configured' => $this->client->isConfigured(),
                        'image_path' => $filename,
                        'image_url' => Storage::disk('public')->url($filename),
                        'prompt' => $prompt,
                        'is_ai_generated' => true,
                        'attribution_disclaimer' => 'AI-Designed Cultural Event Flyer with verified event details.',
                    ];
                }
            }
        }

        // 2. Optionally fetch AI background artwork if configured and not skipped
        $aiBackgroundDataUri = null;
        if ($this->client->isConfigured() && empty($details['skip_remote_ai'])) {
            $payload = [
                'model' => $this->model,
                'prompt' => $prompt,
                'n' => 1,
                'size' => '1024x1024',
                'response_format' => 'b64_json',
            ];

            $response = $this->client->postJson('images/generations', $payload, $this->model);
            if ($response['success']) {
                // If no custom details/photo were passed (e.g. legacy API call), store image directly if available
                $hasCustomDetails = !empty($details['event_date']) || !empty($details['event_time']) || !empty($details['location']) || !empty($details['subtitle']) || !empty($details['subject_photo_data_url']) || !empty($details['logo_data_url']);
                if (!$hasCustomDetails) {
                    $imageData = $this->extractAndStoreImage($response['data'], 'events/flyers');
                    if ($imageData) {
                        return [
                            'success' => true,
                            'configured' => true,
                            'image_path' => $imageData['path'],
                            'image_url' => $imageData['url'],
                            'prompt' => $prompt,
                            'is_ai_generated' => true,
                            'attribution_disclaimer' => 'AI-Generated Cultural Event Flyer.',
                        ];
                    }
                } elseif (!empty($response['data']['data'][0]['b64_json'])) {
                    $aiBackgroundDataUri = 'data:image/png;base64,' . $response['data']['data'][0]['b64_json'];
                }
            }
        }

        // 3. Build bespoke cultural flyer with exact real event details (Date, Time, Venue, Host/Subtitle, Photo, Logo)
        $flyerAsset = $this->generateDetailedFlyerSvg($title, $type, array_merge($details, [
            'description' => $description,
            'ai_background_data_uri' => $aiBackgroundDataUri,
        ]), 'events/flyers');

        return [
            'success' => true,
            'configured' => $this->client->isConfigured(),
            'image_path' => $flyerAsset['path'],
            'image_url' => $flyerAsset['url'],
            'background_data_uri' => $aiBackgroundDataUri,
            'prompt' => $prompt,
            'is_ai_generated' => true,
            'attribution_disclaimer' => 'AI-Curated Cultural Event Flyer.',
        ];
    }

    /**
     * Generate an authentic cultural thumbnail for live streaming.
     */
    public function generateStreamThumbnail(string $title, ?string $category = null, ?string $notes = null): array
    {
        $prompt = "A live cultural broadcast cover thumbnail for '{$title}' ({$category}). Dynamic streaming presentation, vibrant African cultural motifs, rich lighting, warm amber and crimson tones, broadcast quality.";

        if ($this->client->isConfigured()) {
            $payload = [
                'model' => $this->model,
                'prompt' => $prompt,
                'n' => 1,
                'size' => '1024x1024',
                'response_format' => 'b64_json',
            ];

            $response = $this->client->postJson('images/generations', $payload, $this->model);
            if ($response['success']) {
                $imageData = $this->extractAndStoreImage($response['data'], 'stream-thumbnails');
                if ($imageData) {
                    return [
                        'success' => true,
                        'configured' => true,
                        'image_path' => $imageData['path'],
                        'image_url' => $imageData['url'],
                        'prompt' => $prompt,
                        'is_ai_generated' => true,
                    ];
                }
            }
        }

        $fallback = $this->generateFallbackAsset($title, $category, 'stream-thumbnails');

        return [
            'success' => true,
            'configured' => $this->client->isConfigured(),
            'image_path' => $fallback['path'],
            'image_url' => $fallback['url'],
            'prompt' => $prompt,
            'is_ai_generated' => true,
        ];
    }

    /**
     * Generate a detailed cultural event flyer SVG containing exact Date, Time, Venue, Host/Subtitle, Dress Code, RSVP, Photo, and Logo.
     */
    protected function generateDetailedFlyerSvg(string $title, ?string $type, array $details, string $folder): array
    {
        Storage::disk('public')->makeDirectory($folder);

        $palette = strtolower((string) ($details['theme_palette'] ?? 'royal_gold'));
        $colors = match ($palette) {
            'emerald_ivory' => ['bg1' => '#064e3b', 'bg2' => '#022c22', 'bg3' => '#0f172a', 'accent' => '#34d399', 'gold' => '#fbbf24', 'card' => 'rgba(2, 44, 34, 0.78)'],
            'terracotta_bronze' => ['bg1' => '#7c2d12', 'bg2' => '#431407', 'bg3' => '#1c1917', 'accent' => '#fb923c', 'gold' => '#f59e0b', 'card' => 'rgba(28, 25, 23, 0.78)'],
            'indigo_silver' => ['bg1' => '#1e1b4b', 'bg2' => '#312e81', 'bg3' => '#090d16', 'accent' => '#818cf8', 'gold' => '#e2e8f0', 'card' => 'rgba(15, 23, 42, 0.78)'],
            'crimson_amber' => ['bg1' => '#7f1d1d', 'bg2' => '#450a0a', 'bg3' => '#18181b', 'accent' => '#f87171', 'gold' => '#fbbf24', 'card' => 'rgba(24, 24, 27, 0.78)'],
            default => ['bg1' => '#78350f', 'bg2' => '#451a03', 'bg3' => '#0c0a09', 'accent' => '#f59e0b', 'gold' => '#fbbf24', 'card' => 'rgba(12, 10, 9, 0.78)'],
        };

        $occasionLabel = strtoupper(trim((string) ($details['occasion_label'] ?? $details['occasion'] ?? $type ?? 'CULTURAL GATHERING')));
        $safeOccasion = htmlspecialchars(Str::limit($occasionLabel, 45), ENT_QUOTES, 'UTF-8');
        $safeTitle = htmlspecialchars(Str::limit($title, 52), ENT_QUOTES, 'UTF-8');
        $safeSubtitle = htmlspecialchars(Str::limit((string) ($details['subtitle'] ?? ''), 75), ENT_QUOTES, 'UTF-8');
        $safeDate = htmlspecialchars(Str::limit((string) ($details['event_date'] ?? 'Date TBA'), 40), ENT_QUOTES, 'UTF-8');
        $safeTime = htmlspecialchars(Str::limit((string) ($details['event_time'] ?? 'Time TBA'), 30), ENT_QUOTES, 'UTF-8');
        $safeVenue = htmlspecialchars(Str::limit((string) ($details['location'] ?? 'Venue TBA'), 65), ENT_QUOTES, 'UTF-8');
        $safeDressCode = htmlspecialchars(Str::limit((string) ($details['dress_code'] ?? ''), 55), ENT_QUOTES, 'UTF-8');
        $safeRsvp = htmlspecialchars(Str::limit((string) ($details['rsvp'] ?? ''), 55), ENT_QUOTES, 'UTF-8');

        $subjectPhoto = !empty($details['subject_photo_data_url']) && str_starts_with((string) $details['subject_photo_data_url'], 'data:image/')
            ? $details['subject_photo_data_url']
            : null;
        $logoImage = !empty($details['logo_data_url']) && str_starts_with((string) $details['logo_data_url'], 'data:image/')
            ? $details['logo_data_url']
            : null;
        $bgImage = !empty($details['ai_background_data_uri']) && str_starts_with((string) $details['ai_background_data_uri'], 'data:image/')
            ? $details['ai_background_data_uri']
            : null;

        $bgImageTag = $bgImage
            ? '<image href="' . htmlspecialchars($bgImage, ENT_QUOTES, 'UTF-8') . '" x="0" y="0" width="1200" height="1350" preserveAspectRatio="xMidYMid slice" opacity="0.32"/>'
            : '';

        $logoTag = $logoImage
            ? '<g><circle cx="600" cy="145" r="54" fill="rgba(0,0,0,0.55)" stroke="' . $colors['gold'] . '" stroke-width="3"/><clipPath id="logoClip"><circle cx="600" cy="145" r="50"/></clipPath><image href="' . htmlspecialchars($logoImage, ENT_QUOTES, 'UTF-8') . '" x="550" y="95" width="100" height="100" preserveAspectRatio="xMidYMid slice" clip-path="url(#logoClip)"/></g>'
            : '<g><circle cx="600" cy="145" r="44" fill="rgba(245, 158, 11, 0.16)" stroke="' . $colors['gold'] . '" stroke-width="2.5"/><path d="M 578 145 L 600 115 L 622 145 L 600 175 Z" fill="' . $colors['gold'] . '"/><circle cx="600" cy="145" r="10" fill="#ffffff"/></g>';

        $photoTag = $subjectPhoto
            ? '<g><circle cx="600" cy="610" r="176" fill="none" stroke="' . $colors['gold'] . '" stroke-width="6"/><circle cx="600" cy="610" r="186" fill="none" stroke="' . $colors['accent'] . '" stroke-width="2" stroke-dasharray="10 6"/><clipPath id="subjectClip"><circle cx="600" cy="610" r="172"/></clipPath><image href="' . htmlspecialchars($subjectPhoto, ENT_QUOTES, 'UTF-8') . '" x="428" y="438" width="344" height="344" preserveAspectRatio="xMidYMid slice" clip-path="url(#subjectClip)"/></g>'
            : '<g><circle cx="600" cy="610" r="110" fill="rgba(255,255,255,0.04)" stroke="' . $colors['gold'] . '" stroke-width="2.5" stroke-dasharray="8 6"/><path d="M 540 610 L 600 550 L 660 610 L 600 670 Z" fill="none" stroke="' . $colors['gold'] . '" stroke-width="3"/><circle cx="600" cy="610" r="24" fill="' . $colors['gold'] . '"/></g>';

        $footerLine = trim(($safeDressCode ? "DRESS CODE: {$safeDressCode}" : '') . ($safeDressCode && $safeRsvp ? '   •   ' : '') . ($safeRsvp ? "RSVP: {$safeRsvp}" : ''));
        if ($footerLine === '') {
            $footerLine = 'KULTUREGRAM CULTURAL PRESERVATION & COMMUNITY HERITAGE';
        }

        $svg = <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1200 1350" width="1200" height="1350">
  <defs>
    <linearGradient id="grad" x1="0%" y1="0%" x2="100%" y2="100%">
      <stop offset="0%" style="stop-color:{$colors['bg1']};stop-opacity:1" />
      <stop offset="50%" style="stop-color:{$colors['bg2']};stop-opacity:1" />
      <stop offset="100%" style="stop-color:{$colors['bg3']};stop-opacity:1" />
    </linearGradient>
    <pattern id="pattern" width="70" height="70" patternUnits="userSpaceOnUse">
      <path d="M 0 35 L 35 0 L 70 35 L 35 70 Z" fill="none" stroke="rgba(251, 191, 36, 0.12)" stroke-width="2"/>
      <circle cx="35" cy="35" r="5" fill="rgba(251, 191, 36, 0.16)"/>
    </pattern>
  </defs>
  <rect width="1200" height="1350" fill="url(#grad)" />
  {$bgImageTag}
  <rect width="1200" height="1350" fill="url(#pattern)" />

  <rect x="44" y="44" width="1112" height="1262" rx="34" fill="none" stroke="{$colors['gold']}" stroke-opacity="0.55" stroke-width="3" stroke-dasharray="14 8"/>
  <rect x="66" y="66" width="1068" height="1218" rx="26" fill="{$colors['card']}" stroke="{$colors['gold']}" stroke-opacity="0.3" stroke-width="2"/>

  {$logoTag}

  <rect x="370" y="220" width="460" height="44" rx="22" fill="rgba(251, 191, 36, 0.16)" stroke="{$colors['gold']}" stroke-width="1.5"/>
  <text x="600" y="248" font-family="system-ui, -apple-system, sans-serif" font-size="16" font-weight="900" fill="{$colors['gold']}" text-anchor="middle" letter-spacing="4">{$safeOccasion}</text>

  <text x="600" y="340" font-family="Georgia, 'Times New Roman', serif" font-size="52" font-weight="900" fill="#ffffff" text-anchor="middle">{$safeTitle}</text>
  <text x="600" y="392" font-family="system-ui, -apple-system, sans-serif" font-size="22" font-weight="600" fill="#e2e8f0" text-anchor="middle">{$safeSubtitle}</text>

  {$photoTag}

  <rect x="120" y="845" width="460" height="115" rx="20" fill="rgba(255,255,255,0.06)" stroke="{$colors['gold']}" stroke-opacity="0.35" stroke-width="1.5"/>
  <text x="350" y="888" font-family="system-ui, -apple-system, sans-serif" font-size="14" font-weight="800" fill="{$colors['gold']}" text-anchor="middle" letter-spacing="3">DATE</text>
  <text x="350" y="930" font-family="system-ui, -apple-system, sans-serif" font-size="26" font-weight="900" fill="#ffffff" text-anchor="middle">{$safeDate}</text>

  <rect x="620" y="845" width="460" height="115" rx="20" fill="rgba(255,255,255,0.06)" stroke="{$colors['gold']}" stroke-opacity="0.35" stroke-width="1.5"/>
  <text x="850" y="888" font-family="system-ui, -apple-system, sans-serif" font-size="14" font-weight="800" fill="{$colors['gold']}" text-anchor="middle" letter-spacing="3">TIME</text>
  <text x="850" y="930" font-family="system-ui, -apple-system, sans-serif" font-size="26" font-weight="900" fill="#ffffff" text-anchor="middle">{$safeTime}</text>

  <rect x="120" y="985" width="960" height="125" rx="20" fill="rgba(255,255,255,0.06)" stroke="{$colors['gold']}" stroke-opacity="0.35" stroke-width="1.5"/>
  <text x="600" y="1028" font-family="system-ui, -apple-system, sans-serif" font-size="14" font-weight="800" fill="{$colors['gold']}" text-anchor="middle" letter-spacing="3">VENUE / LOCATION</text>
  <text x="600" y="1074" font-family="system-ui, -apple-system, sans-serif" font-size="28" font-weight="900" fill="#ffffff" text-anchor="middle">{$safeVenue}</text>

  <line x1="200" y1="1155" x2="1000" y2="1155" stroke="{$colors['gold']}" stroke-opacity="0.35" stroke-width="1.5"/>
  <text x="600" y="1205" font-family="system-ui, -apple-system, sans-serif" font-size="18" font-weight="700" fill="{$colors['gold']}" text-anchor="middle" letter-spacing="1.5">{$footerLine}</text>
</svg>
SVG;

        $filename = "{$folder}/ai_flyer_" . Str::uuid() . ".svg";
        Storage::disk('public')->put($filename, $svg);

        return [
            'path' => $filename,
            'url' => Storage::disk('public')->url($filename),
        ];
    }

    /**
     * Fallback cultural asset generation when external model inference is not active.
     */
    protected function generateFallbackAsset(string $title, ?string $subtitle, string $folder): array
    {
        return $this->generateDetailedFlyerSvg($title, $subtitle, [
            'occasion_label' => $subtitle ?? 'Cultural Gathering',
        ], $folder);
    }

    /**
     * Securely store returned image bytes into public disk.
     */
    protected function extractAndStoreImage(array $data, string $folder = 'cultures/illustrations'): ?array
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

        $filename = $folder . '/ai_' . Str::uuid() . '.png';
        Storage::disk('public')->put($filename, $bytes);

        return [
            'path' => $filename,
            'url' => Storage::disk('public')->url($filename),
        ];
    }
}
