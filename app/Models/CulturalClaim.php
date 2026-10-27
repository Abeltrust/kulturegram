<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CulturalClaim extends Model
{
    use HasFactory;

    protected $fillable = [
        'culture_id',
        'cultural_source_id',
        'claim_text',
        'claim_type',
        'evidence_reference',
        'review_status',
        'notes',
    ];

    public function culture()
    {
        return $this->belongsTo(Culture::class);
    }

    public function source()
    {
        return $this->belongsTo(CulturalSource::class, 'cultural_source_id');
    }

    /**
     * Readable badge label for review status.
     */
    public function getReviewBadgeAttribute(): array
    {
        return match ($this->review_status) {
            'source_verified' => ['label' => 'Source Verified', 'class' => 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950/70 dark:text-emerald-300 border-emerald-300 dark:border-emerald-700'],
            'community_reviewed' => ['label' => 'Community Reviewed', 'class' => 'bg-blue-100 text-blue-800 dark:bg-blue-950/70 dark:text-blue-300 border-blue-300 dark:border-blue-700'],
            'disputed' => ['label' => 'Disputed Claim', 'class' => 'bg-rose-100 text-rose-800 dark:bg-rose-950/70 dark:text-rose-300 border-rose-300 dark:border-rose-700'],
            default => ['label' => 'Awaiting Evidence', 'class' => 'bg-stone-100 text-stone-700 dark:bg-stone-800 dark:text-stone-300 border-stone-300 dark:border-stone-700'],
        };
    }
}
