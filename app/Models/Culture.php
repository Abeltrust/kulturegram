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
        'cultural_group',
        'translated_title',
        'subtype',
        'review_status',
        'sensitivity_level',
        'locality',
        'latitude',
        'longitude',
    ];

    protected $casts = [
        'contributors' => 'array',
        'tags' => 'array',
        'media_files' => 'array',
        'is_ai_assisted' => 'boolean',
        'artifact_segmentation' => 'array',
        'wonder_preservation_plan' => 'array',
        'ai_metadata' => 'array',
        'latitude' => 'float',
        'longitude' => 'float',
    ];

    // Relationships
    public function submitter()
    {
        return $this->belongsTo(User::class, 'submitted_by');
    }

    public function sources()
    {
        return $this->hasMany(CulturalSource::class);
    }

    public function claims()
    {
        return $this->hasMany(CulturalClaim::class);
    }

    public function culturalContributors()
    {
        return $this->hasMany(CulturalContributor::class);
    }

    public function mediaAssets()
    {
        return $this->hasMany(CulturalMedia::class);
    }

    public function translations()
    {
        return $this->hasMany(CulturalTranslation::class);
    }

    public function revisions()
    {
        return $this->hasMany(CulturalRevision::class);
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

    public function scopePublished($query)
    {
        return $query->whereIn('status', ['approved', 'featured'])
            ->where(function ($q) {
                $q->whereNull('review_status')
                  ->orWhere('review_status', 'published');
            });
    }

    public function scopeUnderReview($query)
    {
        return $query->where('review_status', 'under_review')
            ->orWhere('status', 'pending_review');
    }

    public function scopeDisputed($query)
    {
        return $query->where('review_status', 'disputed');
    }

    public function scopeAwaitingContributions($query)
    {
        return $query->where('review_status', 'awaiting_contributions');
    }

    public function getReviewBadgeAttribute(): array
    {
        $status = $this->review_status ?: ($this->status === 'approved' || $this->status === 'featured' ? 'published' : 'under_review');
        return match ($status) {
            'published' => ['label' => 'Published Archive', 'class' => 'bg-emerald-500/10 text-emerald-700 dark:text-emerald-300 border-emerald-500/30'],
            'under_review' => ['label' => 'Under Community Review', 'class' => 'bg-blue-500/10 text-blue-700 dark:text-blue-300 border-blue-500/30'],
            'awaiting_contributions' => ['label' => 'Awaiting Evidence & Context', 'class' => 'bg-amber-500/10 text-amber-700 dark:text-amber-300 border-amber-500/30'],
            'disputed' => ['label' => 'Disputed Accounts', 'class' => 'bg-rose-500/10 text-rose-700 dark:text-rose-300 border-rose-500/30'],
            'restricted' => ['label' => 'Restricted / Sacred Access', 'class' => 'bg-purple-500/10 text-purple-700 dark:text-purple-300 border-purple-500/30'],
            default => ['label' => ucfirst(str_replace('_', ' ', $status)), 'class' => 'bg-stone-500/10 text-stone-700 dark:text-stone-300 border-stone-500/30'],
        };
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