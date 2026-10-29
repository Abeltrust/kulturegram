<?php

namespace App\Services;

use App\Models\Post;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class WelcomeFlyerService
{
    /**
     * Assign a unique cultural avatar to the user if they did not upload one,
     * and save it as their official profile picture in storage and database.
     */
    public function assignCulturalAvatar(User $user): string
    {
        $filename = 'cultural_avatar_' . $user->id . '_' . time() . '.svg';
        $svg = $this->generateCulturalAvatarSvg($user);

        Storage::disk('public')->put('avatars/' . $filename, $svg);

        $user->avatar = $filename;
        $user->save();

        return $filename;
    }

    /**
     * Generate a rich, authentic cultural avatar SVG based on user identity.
     */
    public function generateCulturalAvatarSvg(User $user): string
    {
        $initials = strtoupper(substr($user->name ?: 'KG', 0, 2));
        $seed = ($user->id ?: crc32($user->email ?: 'kulturegram')) % 6;

        $themes = [
            0 => [ // Ancestral Bronze & Gold
                'bg1' => '#1c1917', 'bg2' => '#292524',
                'primary' => '#f59e0b', 'secondary' => '#d97706', 'accent' => '#fbbf24',
                'patternColor' => '#f59e0b',
            ],
            1 => [ // Royal Indigo & Terracotta
                'bg1' => '#0f172a', 'bg2' => '#1e1b4b',
                'primary' => '#c2410c', 'secondary' => '#b45309', 'accent' => '#fb923c',
                'patternColor' => '#60a5fa',
            ],
            2 => [ // Emerald Heritage & Brass
                'bg1' => '#064e3b', 'bg2' => '#022c22',
                'primary' => '#f59e0b', 'secondary' => '#059669', 'accent' => '#fde047',
                'patternColor' => '#34d399',
            ],
            3 => [ // Coral Kingdom (Benin / Ife inspired)
                'bg1' => '#450a0a', 'bg2' => '#292524',
                'primary' => '#dc2626', 'secondary' => '#ea580c', 'accent' => '#fca5a5',
                'patternColor' => '#f87171',
            ],
            4 => [ // Golden Savannah & Ochre
                'bg1' => '#18181b', 'bg2' => '#27272a',
                'primary' => '#eab308', 'secondary' => '#ca8a04', 'accent' => '#fef08a',
                'patternColor' => '#eab308',
            ],
            5 => [ // Nile Azure & Amun Gold
                'bg1' => '#111827', 'bg2' => '#1f2937',
                'primary' => '#f59e0b', 'secondary' => '#0284c7', 'accent' => '#38bdf8',
                'patternColor' => '#38bdf8',
            ],
        ];

        $theme = $themes[$seed] ?? $themes[0];

        return <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 400 400" width="400" height="400">
    <defs>
        <linearGradient id="avatarBgGrad" x1="0%" y1="0%" x2="100%" y2="100%">
            <stop offset="0%" stop-color="{$theme['bg1']}" />
            <stop offset="100%" stop-color="{$theme['bg2']}" />
        </linearGradient>

        <linearGradient id="avatarGoldGrad" x1="0%" y1="0%" x2="100%" y2="100%">
            <stop offset="0%" stop-color="{$theme['accent']}" />
            <stop offset="50%" stop-color="{$theme['primary']}" />
            <stop offset="100%" stop-color="{$theme['secondary']}" />
        </linearGradient>

        <pattern id="avatarPat" width="20" height="20" patternUnits="userSpaceOnUse">
            <path d="M 0 10 L 10 0 L 20 10 L 10 20 Z" fill="none" stroke="{$theme['patternColor']}" stroke-width="0.8" opacity="0.15" />
        </pattern>

        <clipPath id="circleClip">
            <circle cx="200" cy="200" r="190" />
        </clipPath>
    </defs>

    <!-- Base Circular Container -->
    <g clip-path="url(#circleClip)">
        <rect width="400" height="400" fill="url(#avatarBgGrad)" />
        <rect width="400" height="400" fill="url(#avatarPat)" />

        <!-- Radial Decorative Sunburst / Aura -->
        <circle cx="200" cy="200" r="160" fill="none" stroke="{$theme['primary']}" stroke-width="1.5" stroke-dasharray="8 6" opacity="0.3" />
        <circle cx="200" cy="200" r="140" fill="none" stroke="{$theme['accent']}" stroke-width="1" opacity="0.2" />

        <!-- Cultural Headwear / Headdress Geometry -->
        <g transform="translate(200, 105)">
            <path d="M -70 50 Q 0 -20 70 50 Q 0 25 -70 50 Z" fill="url(#avatarGoldGrad)" opacity="0.9" />
            <circle cx="0" cy="18" r="8" fill="{$theme['accent']}" />
            <polygon points="-40,40 -20,20 0,35 20,20 40,40 0,55" fill="{$theme['secondary']}" opacity="0.85" />
        </g>

        <!-- Central Medallion Shield -->
        <circle cx="200" cy="210" r="75" fill="{$theme['bg1']}" stroke="url(#avatarGoldGrad)" stroke-width="5" />
        <circle cx="200" cy="210" r="66" fill="none" stroke="{$theme['accent']}" stroke-width="1.5" stroke-dasharray="4 3" opacity="0.7" />

        <!-- Initials -->
        <text x="200" y="228" font-family="system-ui, -apple-system, sans-serif" font-weight="900" font-size="52" fill="{$theme['accent']}" text-anchor="middle" letter-spacing="2">{$initials}</text>

        <!-- Cultural Ceremonial Beaded Collar / Torq -->
        <g transform="translate(200, 295)">
            <path d="M -110 30 Q 0 85 110 30" fill="none" stroke="url(#avatarGoldGrad)" stroke-width="9" stroke-linecap="round" />
            <path d="M -90 55 Q 0 105 90 55" fill="none" stroke="{$theme['primary']}" stroke-width="6" stroke-linecap="round" opacity="0.85" />
            <path d="M -65 75 Q 0 120 65 75" fill="none" stroke="{$theme['accent']}" stroke-width="4" stroke-linecap="round" opacity="0.7" />
            <circle cx="-60" cy="48" r="4.5" fill="{$theme['accent']}" />
            <circle cx="-30" cy="62" r="4.5" fill="{$theme['accent']}" />
            <circle cx="0" cy="68" r="5.5" fill="{$theme['accent']}" />
            <circle cx="30" cy="62" r="4.5" fill="{$theme['accent']}" />
            <circle cx="60" cy="48" r="4.5" fill="{$theme['accent']}" />
        </g>
    </g>

    <!-- Outer Golden Border Ring -->
    <circle cx="200" cy="200" r="194" fill="none" stroke="url(#avatarGoldGrad)" stroke-width="8" />
    <circle cx="200" cy="200" r="186" fill="none" stroke="{$theme['bg1']}" stroke-width="2" />
</svg>
SVG;
    }

    /**
     * Create the first-time welcome flyer and post for a newly registered user.
     */
    public function createWelcomePost(User $user): ?Post
    {
        try {
            $user->loadMissing(['country', 'state', 'localGovernment', 'spokenLanguages']);

            // If user has no avatar, assign a cultural avatar first
            if (!$user->avatar) {
                $this->assignCulturalAvatar($user);
            }

            $flyerRelativePath = $this->saveFlyer($user);

            $location = $this->resolveLocationText($user);

            $content = "✨ Hi everyone! I am now on Kulturegram! 🌍🎉\n\n"
                . "Proud of my cultural roots and heritage from " . $location . ". "
                . "Excited to connect, share my story, and celebrate our diverse living heritage with this vibrant community! "
                . "Connect with me and vibe with my journey! 💛\n\n"
                . "#WelcomeToKulturegram #LivingHeritage #CulturalPassport #ConnectAndShare";

            $post = Post::create([
                'user_id' => $user->id,
                'content' => $content,
                'type' => 'image',
                'image' => $flyerRelativePath,
                'location' => $location,
                'tags' => ['WelcomeToKulturegram', 'LivingHeritage', 'CulturalPassport', 'Kulturegram'],
                'privacy' => 'public',
            ]);

            $user->increment('posts_count');

            return $post;
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('Failed to generate welcome flyer or post: ' . $e->getMessage(), [
                'user_id' => $user->id,
            ]);
            return null;
        }
    }

    /**
     * Save the generated flyer SVG to the public disk and return its relative path.
     */
    public function saveFlyer(User $user): string
    {
        $svg = $this->generateFlyerSvg($user);
        $filename = 'flyers/welcome_flyer_' . $user->id . '_' . time() . '.svg';

        Storage::disk('public')->put($filename, $svg);

        return $filename;
    }

    /**
     * Generate a self-contained, high-resolution (1080x1080) SVG cultural flyer.
     */
    public function generateFlyerSvg(User $user): string
    {
        $name = htmlspecialchars($user->name ?? 'Cultural Preserver', ENT_QUOTES, 'UTF-8');
        $username = htmlspecialchars($user->username ?? 'kulturegram', ENT_QUOTES, 'UTF-8');
        $location = htmlspecialchars($this->resolveLocationText($user), ENT_QUOTES, 'UTF-8');
        $flag = htmlspecialchars($user->country?->flag ?? '🌍', ENT_QUOTES, 'UTF-8');

        $languages = $user->spokenLanguages->pluck('name')->all();
        if (empty($languages) && is_array($user->languages)) {
            $languages = $user->languages;
        }
        $languagesText = htmlspecialchars(!empty($languages) ? implode(' • ', array_slice($languages, 0, 3)) : 'Cultural Explorer', ENT_QUOTES, 'UTF-8');

        $avatarDataUri = $this->resolveAvatarDataUri($user);
        $logoDataUri = $this->resolveLogoDataUri();

        $logoElement = $logoDataUri
            ? '<image href="' . $logoDataUri . '" x="40" y="40" width="70" height="70" preserveAspectRatio="xMidYMid meet" />'
            : '<rect x="40" y="40" width="70" height="70" rx="16" fill="#f59e0b" /><text x="75" y="85" font-family="system-ui, sans-serif" font-weight="900" font-size="32" fill="#1c1917" text-anchor="middle">K</text>';

        return <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1080 1080" width="1080" height="1080">
    <defs>
        <linearGradient id="bgGrad" x1="0%" y1="0%" x2="100%" y2="100%">
            <stop offset="0%" stop-color="#141210" />
            <stop offset="50%" stop-color="#1c1917" />
            <stop offset="100%" stop-color="#292524" />
        </linearGradient>

        <linearGradient id="goldGrad" x1="0%" y1="0%" x2="100%" y2="0%">
            <stop offset="0%" stop-color="#f59e0b" />
            <stop offset="50%" stop-color="#fbbf24" />
            <stop offset="100%" stop-color="#d97706" />
        </linearGradient>

        <linearGradient id="cardGrad" x1="0%" y1="0%" x2="0%" y2="100%">
            <stop offset="0%" stop-color="#292524" stop-opacity="0.9" />
            <stop offset="100%" stop-color="#1c1917" stop-opacity="0.95" />
        </linearGradient>

        <clipPath id="avatarClip">
            <circle cx="540" cy="430" r="160" />
        </clipPath>

        <filter id="shadow" x="-10%" y="-10%" width="120%" height="120%">
            <feDropShadow dx="0" dy="12" stdDeviation="16" flood-color="#000000" flood-opacity="0.6" />
        </filter>

        <pattern id="culturalGrid" width="60" height="60" patternUnits="userSpaceOnUse">
            <path d="M 60 0 L 0 60 M 0 0 L 60 60" fill="none" stroke="#f59e0b" stroke-width="0.7" opacity="0.08" />
            <circle cx="30" cy="30" r="2" fill="#f59e0b" opacity="0.12" />
        </pattern>
    </defs>

    <!-- Background -->
    <rect width="1080" height="1080" fill="url(#bgGrad)" />
    <rect width="1080" height="1080" fill="url(#culturalGrid)" />

    <!-- Top Accent Bar -->
    <rect x="0" y="0" width="1080" height="14" fill="url(#goldGrad)" />

    <!-- Corner Decorative Accents -->
    <path d="M 30 50 L 30 30 L 50 30" fill="none" stroke="#f59e0b" stroke-width="3" opacity="0.6" />
    <path d="M 1050 50 L 1050 30 L 1030 30" fill="none" stroke="#f59e0b" stroke-width="3" opacity="0.6" />
    <path d="M 30 1030 L 30 1050 L 50 1050" fill="none" stroke="#f59e0b" stroke-width="3" opacity="0.6" />
    <path d="M 1050 1030 L 1050 1050 L 1030 1050" fill="none" stroke="#f59e0b" stroke-width="3" opacity="0.6" />

    <!-- Header Area -->
    {$logoElement}
    <text x="130" y="75" font-family="system-ui, -apple-system, sans-serif" font-weight="900" font-size="32" fill="#f59e0b" letter-spacing="3">KULTUREGRAM</text>
    <text x="130" y="100" font-family="system-ui, -apple-system, sans-serif" font-weight="600" font-size="14" fill="#a8a29e" letter-spacing="2">LIVING HERITAGE • PRESERVING CULTURE</text>

    <!-- Top Badge (Cultural Passport) -->
    <g transform="translate(640, 52)">
        <rect width="400" height="44" rx="22" fill="#f59e0b" fill-opacity="0.12" stroke="#f59e0b" stroke-width="1.5" stroke-opacity="0.4" />
        <text x="200" y="28" font-family="system-ui, -apple-system, sans-serif" font-weight="800" font-size="13" fill="#fbbf24" letter-spacing="3" text-anchor="middle">OFFICIAL CULTURAL PASSPORT</text>
    </g>

    <!-- Center Avatar Frame -->
    <g filter="url(#shadow)">
        <!-- Outer Glowing Ring -->
        <circle cx="540" cy="430" r="182" fill="none" stroke="#f59e0b" stroke-width="3" opacity="0.4" stroke-dasharray="8 6" />
        <circle cx="540" cy="430" r="172" fill="none" stroke="url(#goldGrad)" stroke-width="8" />
        <circle cx="540" cy="430" r="160" fill="#292524" />

        <!-- User Image -->
        <image href="{$avatarDataUri}" x="380" y="270" width="320" height="320" preserveAspectRatio="xMidYMid slice" clip-path="url(#avatarClip)" />

        <!-- Flag Badge Overlay -->
        <g transform="translate(650, 530)">
            <circle cx="32" cy="32" r="32" fill="#1c1917" stroke="#f59e0b" stroke-width="4" />
            <text x="32" y="44" font-size="34" text-anchor="middle">{$flag}</text>
        </g>
    </g>

    <!-- User Identity -->
    <text x="540" y="660" font-family="system-ui, -apple-system, sans-serif" font-weight="900" font-size="46" fill="#ffffff" text-anchor="middle" letter-spacing="0.5">{$name}</text>
    <text x="540" y="705" font-family="system-ui, -apple-system, sans-serif" font-weight="700" font-size="24" fill="#f59e0b" text-anchor="middle" letter-spacing="1">@{$username}</text>

    <!-- Cultural Declaration Banner -->
    <g transform="translate(100, 745)">
        <rect width="880" height="135" rx="24" fill="url(#cardGrad)" stroke="#f59e0b" stroke-width="2" stroke-opacity="0.35" filter="url(#shadow)" />
        <rect x="0" y="0" width="880" height="6" rx="3" fill="url(#goldGrad)" />
        <text x="440" y="55" font-family="system-ui, -apple-system, sans-serif" font-weight="800" font-size="24" fill="#ffffff" text-anchor="middle">
            &ldquo;Hello! I am now on Kulturegram.&rdquo;
        </text>
        <text x="440" y="95" font-family="system-ui, -apple-system, sans-serif" font-weight="600" font-size="18" fill="#fbbf24" text-anchor="middle">
            This is my culture, follow my journey and let&rsquo;s vibe!
        </text>
    </g>

    <!-- Origin & Languages Badges -->
    <g transform="translate(100, 915)">
        <!-- Origin Badge -->
        <g>
            <rect width="425" height="48" rx="24" fill="#292524" stroke="#44403c" stroke-width="1.5" />
            <text x="212" y="31" font-family="system-ui, -apple-system, sans-serif" font-weight="700" font-size="16" fill="#e7e5e4" text-anchor="middle">
                📍 {$location}
            </text>
        </g>

        <!-- Languages Badge -->
        <g transform="translate(455, 0)">
            <rect width="425" height="48" rx="24" fill="#292524" stroke="#44403c" stroke-width="1.5" />
            <text x="212" y="31" font-family="system-ui, -apple-system, sans-serif" font-weight="700" font-size="16" fill="#e7e5e4" text-anchor="middle">
                🗣️ {$languagesText}
            </text>
        </g>
    </g>

    <!-- Bottom Footer -->
    <line x1="80" y1="995" x2="1000" y2="995" stroke="#44403c" stroke-width="1" opacity="0.6" />
    <text x="100" y="1030" font-family="system-ui, -apple-system, sans-serif" font-weight="800" font-size="14" fill="#78716c" letter-spacing="2">KULTUREGRAM.COM</text>
    <text x="540" y="1030" font-family="system-ui, -apple-system, sans-serif" font-weight="700" font-size="13" fill="#f59e0b" letter-spacing="3" text-anchor="middle">CONNECT • PRESERVE • CELEBRATE</text>
    <text x="980" y="1030" font-family="system-ui, -apple-system, sans-serif" font-weight="800" font-size="14" fill="#78716c" letter-spacing="2" text-anchor="end">HERITAGE FOR ALL</text>
</svg>
SVG;
    }

    /**
     * Resolve user location text string.
     */
    protected function resolveLocationText(User $user): string
    {
        $parts = array_filter([
            $user->localGovernment?->name,
            $user->state?->name,
            $user->country?->name ?: $user->location,
        ]);

        return !empty($parts) ? implode(', ', $parts) : ($user->location ?: 'Global Village');
    }

    /**
     * Resolve user avatar into a self-contained data URI.
     */
    protected function resolveAvatarDataUri(User $user): string
    {
        if ($user->avatar && Storage::disk('public')->exists('avatars/' . $user->avatar)) {
            $contents = Storage::disk('public')->get('avatars/' . $user->avatar);
            if ($contents !== null && $contents !== false && strlen($contents) > 0) {
                $extension = strtolower(pathinfo($user->avatar, PATHINFO_EXTENSION));
                $mime = match ($extension) {
                    'svg' => 'image/svg+xml',
                    'png' => 'image/png',
                    'webp' => 'image/webp',
                    'gif' => 'image/gif',
                    default => 'image/jpeg',
                };
                return 'data:' . $mime . ';base64,' . base64_encode($contents);
            }
        }

        // Fallback to cultural avatar SVG
        $fallbackSvg = $this->generateCulturalAvatarSvg($user);
        return 'data:image/svg+xml;base64,' . base64_encode($fallbackSvg);
    }

    /**
     * Resolve logo image into a base64 data URI.
     */
    protected function resolveLogoDataUri(): ?string
    {
        $candidates = [
            public_path('images/logo.png'),
            public_path('logo.png'),
            storage_path('app/public/logo.png'),
        ];

        foreach ($candidates as $candidate) {
            if (file_exists($candidate)) {
                $contents = file_get_contents($candidate);
                if ($contents !== false) {
                    return 'data:image/png;base64,' . base64_encode($contents);
                }
            }
        }

        return null;
    }
}
