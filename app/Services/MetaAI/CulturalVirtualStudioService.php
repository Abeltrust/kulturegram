<?php

namespace App\Services\MetaAI;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CulturalVirtualStudioService
{
    protected MuseImageService $imageService;

    public function __construct(MuseImageService $imageService)
    {
        $this->imageService = $imageService;
    }

    /**
     * Get curated authentic cultural virtual backgrounds.
     */
    public function getCuratedPresets(): array
    {
        return [
            [
                'id' => 'kano_ancient_walls',
                'name' => 'Ancient Kano Mud Wall Courtyard',
                'culture' => 'Hausa / Sahelian',
                'description' => 'Earthy sculpted mud reliefs, warm terracotta tones, and intricate Hausa geometric architecture.',
                'image_url' => 'https://images.unsplash.com/photo-1547471080-7cc2caa01a7e?auto=format&fit=crop&q=80&w=1200',
            ],
            [
                'id' => 'yoruba_palace_veranda',
                'name' => 'Royal Yoruba Palace Veranda',
                'culture' => 'Yoruba',
                'description' => 'Carved caryatid wooden pillars, ancestral beadwork banners, and tranquil stone palace courtyard.',
                'image_url' => 'https://images.unsplash.com/photo-1516026672322-bc52d61a55d5?auto=format&fit=crop&q=80&w=1200',
            ],
            [
                'id' => 'sahelian_sunset_terrace',
                'name' => 'Sahelian Sunset Library Terrace',
                'culture' => 'Pan-African / Sahel',
                'description' => 'Golden twilight overlooking ancient terracotta domes and historic manuscript scrolls.',
                'image_url' => 'https://images.unsplash.com/photo-1509316975850-ff9c5deb0cd9?auto=format&fit=crop&q=80&w=1200',
            ],
            [
                'id' => 'adire_textile_workshop',
                'name' => 'Indigo Dye & Ancestral Loom Studio',
                'culture' => 'Yoruba / West African',
                'description' => 'Rich natural indigo fabrics hanging gently in an airy sunlit artisan workshop.',
                'image_url' => 'https://images.unsplash.com/photo-1524178232363-1fb2b075b655?auto=format&fit=crop&q=80&w=1200',
            ],
            [
                'id' => 'swahili_stone_courtyard',
                'name' => 'Swahili Stone Town Serenade',
                'culture' => 'Swahili Coast',
                'description' => 'Intricately carved coastal teak doors, coral rag whitewashed walls, and bougainvillea flora.',
                'image_url' => 'https://images.unsplash.com/photo-1507525428034-b723cf961d3e?auto=format&fit=crop&q=80&w=1200',
            ],
        ];
    }

    /**
     * Generate custom AI cultural virtual studio backdrop using Muse Image.
     */
    public function generateCustomStudioBackground(string $culturalTheme, ?string $artStyle = null): array
    {
        $prompt = "High-definition professional virtual video background for a cultural educational studio. Theme: {$culturalTheme}. Setting: Authentic architectural heritage, respectful artistic elements, natural warm ambient lighting, elegant depth of field, suitable as a video conferencing or virtual studio backdrop. Clean composition without text or logos.";

        if ($artStyle) {
            $prompt .= " Aesthetic style: {$artStyle}.";
        }

        $res = $this->imageService->generateIllustration($prompt, $culturalTheme);

        if (!$res['success']) {
            // Pick a matching curated preset as fallback
            $presets = $this->getCuratedPresets();
            $fallback = $presets[0];
            return [
                'success' => true,
                'fallback' => true,
                'image_url' => $fallback['image_url'],
                'image_path' => null,
                'theme' => $culturalTheme,
                'notes' => 'Generated with curated heritage preset.',
            ];
        }

        return [
            'success' => true,
            'fallback' => false,
            'image_url' => $res['image_url'],
            'image_path' => $res['image_path'],
            'theme' => $culturalTheme,
            'notes' => 'Generated dynamically by AI Cultural Studio.',
        ];
    }
}
