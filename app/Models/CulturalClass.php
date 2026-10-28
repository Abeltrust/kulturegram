<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class CulturalClass extends Model
{
    use HasFactory;

    protected $fillable = [
        'instructor_id',
        'title',
        'slug',
        'description',
        'cultural_focus',
        'target_language',
        'source_language',
        'level',
        'price_type',
        'price',
        'video_url',
        'preview_video_url',
        'thumbnail_url',
        'virtual_background_url',
        'duration_seconds',
        'transcript',
        'subtitles_vtt',
        'timed_subtitles',
        'bilingual_vocabulary',
        'ai_summary',
        'ai_enhancements',
        'views_count',
        'enrollments_count',
        'is_published',
        'shared_to_timeline',
        'timeline_post_id',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'duration_seconds' => 'integer',
        'views_count' => 'integer',
        'enrollments_count' => 'integer',
        'is_published' => 'boolean',
        'shared_to_timeline' => 'boolean',
        'timed_subtitles' => 'array',
        'bilingual_vocabulary' => 'array',
        'ai_enhancements' => 'array',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($class) {
            if (empty($class->slug)) {
                $baseSlug = Str::slug($class->title);
                $slug = $baseSlug;
                $counter = 1;
                while (static::where('slug', $slug)->exists()) {
                    $slug = $baseSlug . '-' . $counter++;
                }
                $class->slug = $slug;
            }
        });
    }

    // Relationships
    public function instructor()
    {
        return $this->belongsTo(User::class, 'instructor_id');
    }

    public function enrollments()
    {
        return $this->hasMany(CulturalClassEnrollment::class);
    }

    public function enrolledUsers()
    {
        return $this->belongsToMany(User::class, 'cultural_class_enrollments', 'cultural_class_id', 'user_id')
                    ->withPivot('price_paid', 'access_status', 'progress_seconds', 'is_completed', 'last_watched_at')
                    ->withTimestamps();
    }

    public function discussions()
    {
        return $this->hasMany(CulturalClassDiscussion::class)->whereNull('parent_id')->with('replies.user', 'user')->latest();
    }

    public function timelinePost()
    {
        return $this->belongsTo(Post::class, 'timeline_post_id');
    }

    // Scopes
    public function scopePublished($query)
    {
        return $query->where('is_published', true);
    }

    public function scopeFree($query)
    {
        return $query->where('price_type', 'free')->orWhere('price', '<=', 0);
    }

    public function scopePaid($query)
    {
        return $query->where('price_type', 'paid')->where('price', '>', 0);
    }

    public function scopeByLanguage($query, ?string $language)
    {
        if ($language && $language !== 'all') {
            return $query->where(function ($q) use ($language) {
                $q->where('target_language', $language)
                  ->orWhere('cultural_focus', 'like', "%{$language}%");
            });
        }
        return $query;
    }

    // Helper methods
    public function isFree(): bool
    {
        return $this->price_type === 'free' || (float) $this->price <= 0;
    }

    public function isEnrolledBy(?User $user): bool
    {
        if (!$user) {
            return false;
        }

        // The instructor always has full access
        if ($this->instructor_id === $user->id) {
            return true;
        }

        // Admin always has access
        if ($user->is_admin ?? false) {
            return true;
        }

        return $this->enrollments()->where('user_id', $user->id)->exists();
    }

    public function formattedDuration(): string
    {
        if ($this->duration_seconds < 60) {
            return "{$this->duration_seconds}s";
        }
        $minutes = floor($this->duration_seconds / 60);
        $seconds = $this->duration_seconds % 60;
        return sprintf('%02d:%02d', $minutes, $seconds);
    }

    public function formattedPrice(): string
    {
        if ($this->isFree()) {
            return 'Free';
        }
        return '$' . number_format($this->price, 2);
    }

    public static function extractYoutubeId(?string $url): ?string
    {
        if (!$url) {
            return null;
        }

        if (preg_match('/(?:youtube(?:-nocookie)?\.com\/(?:watch\?(?:.*&)?v=|embed\/|shorts\/|live\/|v\/)|youtu\.be\/)([a-zA-Z0-9_-]{11})/i', trim($url), $matches)) {
            return $matches[1];
        }

        return null;
    }

    public function youtubeVideoId(): ?string
    {
        return static::extractYoutubeId($this->video_url);
    }

    public function youtubeEmbedUrl(): ?string
    {
        $id = $this->youtubeVideoId();
        return $id ? "https://www.youtube.com/embed/{$id}?rel=0&modestbranding=1&enablejsapi=1" : null;
    }

    public static function resolveMediaUrl(?string $url): ?string
    {
        if (!$url || !is_string($url)) {
            return null;
        }

        $trimmed = trim($url);
        if ($trimmed === '') {
            return null;
        }

        if (Str::startsWith($trimmed, ['http://', 'https://', 'data:'])) {
            return $trimmed;
        }

        if (Str::startsWith($trimmed, ['/storage/', 'storage/'])) {
            return asset(ltrim($trimmed, '/'));
        }

        return asset('storage/' . ltrim($trimmed, '/'));
    }

    public function resolvedThumbnailUrl(): ?string
    {
        if ($this->thumbnail_url) {
            return static::resolveMediaUrl($this->thumbnail_url);
        }

        $ytId = $this->youtubeVideoId();
        if ($ytId) {
            return "https://img.youtube.com/vi/{$ytId}/hqdefault.jpg";
        }

        if ($this->virtual_background_url) {
            return static::resolveMediaUrl($this->virtual_background_url);
        }

        return null;
    }
}
