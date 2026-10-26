<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Culture extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'region',
        'description',
        'image',
        'video_url',
        'video_path',
        'video_description',
        'audio_url',
        'audio_path',
        'audio_description',
        'image_license',
        'license_type',
        'license_credit',
        'category',
        'language',
        'historical_period',
        'significance',
        'rituals',
        'community_role',
        'endangerment_level',
        'current_practitioners',
        'transmission_methods',
        'preservation_efforts',
        'challenges',
        'future_vision',
        'contributors',
        'tags',
        'media_files',
        'status',
        'submitted_by',
        'raw_story',
        'audio_transcript',
        'is_ai_assisted',
        'ai_illustration',
        'ai_illustration_prompt',
        'artifact_segmentation',
        'wonder_preservation_plan',
        'ai_metadata',
    ];

    protected $casts = [
        'contributors' => 'array',
        'tags' => 'array',
        'media_files' => 'array',
        'is_ai_assisted' => 'boolean',
        'artifact_segmentation' => 'array',
        'wonder_preservation_plan' => 'array',
        'ai_metadata' => 'array',
    ];

    // Relationships
    public function submitter()
    {
        return $this->belongsTo(User::class, 'submitted_by');
    }

    public function interactions()
    {
        return $this->morphMany(Interaction::class, 'interactable');
    }

    public function lockIns()
    {
        return $this->interactions()->where('type', 'lockin');
    }

    public function resonances()
    {
        return $this->interactions()->where('type', 'resonance');
    }

    // Scopes
    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopePendingReview($query)
    {
        return $query->where('status', 'pending_review');
    }

    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    public function scopeFeatured($query)
    {
        return $query->where('status', 'featured');
    }

    public function scopeByCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    // Helper methods
    public function incrementLockIns()
    {
        $this->increment('locked_in_count');
    }

    public function decrementLockIns()
    {
        $this->decrement('locked_in_count');
    }
}