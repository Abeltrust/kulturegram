<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'shared_post_id',
        'content',
        'language',
        'oral_transcript',
        'is_ai_formatted',
        'image',
        'video',
        'audio',
        'location',
        'chapter',
        'community_id',
        'privacy',
        'type',
        'tags',
        'tagged_users',
        'shares_count',
        'is_featured',
    ];

    protected $casts = [
        'tags' => 'array',
        'tagged_users' => 'array',
        'shares_count' => 'integer',
        'is_featured' => 'boolean',
        'is_ai_formatted' => 'boolean',
    ];

    protected ?\Illuminate\Support\Collection $resolvedTaggedUsers = null;

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function sharedPost()
    {
        return $this->belongsTo(Post::class, 'shared_post_id');
    }

    public function reposts()
    {
        return $this->hasMany(Post::class, 'shared_post_id');
    }

    public function community()
    {
        return $this->belongsTo(Community::class);
    }

    public function interactions()
    {
        return $this->morphMany(Interaction::class, 'interactable');
    }

    public function taps()
    {
        return $this->interactions()->where('type', 'tap');
    }

    // Resonances filtered from interactions
    public function resonanceInteractions()
    {
        return $this->interactions()->where('type', 'resonance');
    }

    // Direct relation with Resonance model
    public function resonances()
    {
        return $this->hasMany(Resonance::class);
    }

    public function comments()
    {
        return $this->morphMany(Comment::class, 'commentable');
    }

    public function lockIns()
    {
        return $this->interactions()->where('type', 'lockin');
    }

    public function checkIns()
    {
        return $this->interactions()->where('type', 'checkin');
    }

    public function bookmarks()
    {
        return $this->interactions()->where('type', 'bookmark');
    }

    // Scopes
    public function scopePublic($query)
    {
        return $query->where('privacy', 'public');
    }

    public function scopeByChapter($query, $chapter)
    {
        return $query->where('chapter', $chapter);
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    // Helper methods
    public function getTimeAgoAttribute()
    {
        return $this->created_at->diffForHumans();
    }

    public function getFormattedContentAttribute()
    {
        return \App\Services\ContentParserService::format($this->content);
    }

    public function incrementTaps()
    {
        $this->increment('taps_count');
    }

    public function decrementTaps()
    {
        $this->decrement('taps_count');
    }

    public function isOwnedBy(?User $user): bool
    {
        return $user !== null && (int) $this->user_id === (int) $user->id;
    }

    public function isLockedInBy(?User $user): bool
    {
        if (!$user || !$this->user) {
            return false;
        }

        return $user->hasLockedIn($this->user);
    }

    /**
     * Post Owners and members who Locked-In to the poster can repost to timeline and tag people.
     * Others who have not Locked-In can only share externally and cannot tag people on the post.
     */
    public function canRepostAndTag(?User $user): bool
    {
        if (!$user) {
            return false;
        }

        return $this->isOwnedBy($user) || $this->isLockedInBy($user);
    }

    public function getTaggedUsersModelsAttribute(): \Illuminate\Support\Collection
    {
        if ($this->resolvedTaggedUsers !== null) {
            return $this->resolvedTaggedUsers;
        }

        $ids = is_array($this->tagged_users)
            ? array_values(array_unique(array_filter(array_map('intval', $this->tagged_users))))
            : [];

        if (empty($ids)) {
            return $this->resolvedTaggedUsers = collect();
        }

        return $this->resolvedTaggedUsers = User::whereIn('id', $ids)
            ->get(['id', 'name', 'username', 'avatar']);
    }
}
