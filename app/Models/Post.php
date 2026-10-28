<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'content',
        'language',
        'oral_transcript',
        'is_ai_formatted',
        'image',
        'video',
        'audio',
        'location',
        'chapter',
        'privacy',
        'type',
        'tags',
        'is_featured',
    ];

    protected $casts = [
        'tags' => 'array',
        'is_featured' => 'boolean',
        'is_ai_formatted' => 'boolean',
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
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
}