<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

class ContentParserService
{
    /**
     * Extract hashtags from text.
     *
     * @param string|null $content
     * @return array
     */
    public static function extractHashtags(?string $content): array
    {
        if (empty($content)) {
            return [];
        }

        preg_match_all('/(?:^|\s)#([a-zA-Z0-9_\x{00C0}-\x{00FF}]+)/u', $content, $matches);

        if (empty($matches[1])) {
            return [];
        }

        $tags = array_map(function ($tag) {
            return mb_strtolower(trim($tag));
        }, $matches[1]);

        return array_values(array_unique(array_filter($tags)));
    }

    /**
     * Extract mention usernames from text.
     *
     * @param string|null $content
     * @return array
     */
    public static function extractMentionUsernames(?string $content): array
    {
        if (empty($content)) {
            return [];
        }

        preg_match_all('/(?:^|\s)@([a-zA-Z0-9_]+)/', $content, $matches);

        if (empty($matches[1])) {
            return [];
        }

        $usernames = array_map(function ($username) {
            return trim($username);
        }, $matches[1]);

        return array_values(array_unique(array_filter($usernames)));
    }

    /**
     * Get User models matching mentioned usernames.
     *
     * @param string|null $content
     * @return Collection
     */
    public static function getMentionedUsers(?string $content): Collection
    {
        $usernames = self::extractMentionUsernames($content);

        if (empty($usernames)) {
            return new Collection();
        }

        return User::whereIn('username', $usernames)->get();
    }

    /**
     * Parse content into safe HTML with clickable @mentions and #hashtags.
     *
     * @param string|null $content
     * @return string
     */
    public static function format(?string $content): string
    {
        if (empty($content)) {
            return '';
        }

        // 1. Sanitize to prevent XSS
        $escaped = e($content);

        // 2. Replace @mentions with profile link
        $withMentions = preg_replace_callback(
            '/(^|[\s\(\[\{])@([a-zA-Z0-9_]+)/',
            function ($matches) {
                $prefix = $matches[1];
                $username = $matches[2];
                $url = url('/profile/' . urlencode($username));
                return $prefix . '<a href="' . $url . '" class="font-semibold text-amber-600 dark:text-amber-400 hover:underline">@' . $username . '</a>';
            },
            $escaped
        );

        // 3. Replace #hashtags with timeline tag link
        $withTags = preg_replace_callback(
            '/(^|[\s\(\[\{])#([a-zA-Z0-9_\x{00C0}-\x{00FF}]+)/u',
            function ($matches) {
                $prefix = $matches[1];
                $tag = $matches[2];
                $url = url('/timeline?tag=' . urlencode(mb_strtolower($tag)));
                return $prefix . '<a href="' . $url . '" class="font-medium text-amber-600 dark:text-amber-400 hover:underline">#' . $tag . '</a>';
            },
            $withMentions
        );

        // 4. Convert newlines to <br>
        return nl2br($withTags);
    }
}
