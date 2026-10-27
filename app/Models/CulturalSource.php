<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CulturalSource extends Model
{
    use HasFactory;

    protected $fillable = [
        'culture_id',
        'source_type',
        'file_path',
        'external_url',
        'original_text',
        'original_language',
        'speaker_name',
        'speaker_is_anonymous',
        'attribution_preference',
        'recording_date',
        'recording_location',
        'recording_creator',
        'transcript_origin',
        'transcript_review_status',
        'timestamps_data',
        'source_notes',
        'access_restriction',
    ];

    protected $casts = [
        'speaker_is_anonymous' => 'boolean',
        'timestamps_data' => 'array',
    ];

    public function culture()
    {
        return $this->belongsTo(Culture::class);
    }

    public function claims()
    {
        return $this->hasMany(CulturalClaim::class, 'cultural_source_id');
    }

    /**
     * Display name respecting anonymity preferences.
     */
    public function getDisplaySpeakerAttribute(): string
    {
        if ($this->speaker_is_anonymous) {
            return $this->attribution_preference ?: 'Anonymous Community Elder / Speaker';
        }
        return $this->speaker_name ?: ($this->attribution_preference ?: 'Unattributed Community Voice');
    }

    public function scopePublicOnly($query)
    {
        return $query->where('access_restriction', 'public');
    }
}
