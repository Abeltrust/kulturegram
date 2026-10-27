<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CulturalRecordDraft extends Model
{
    use HasFactory;

    protected $fillable = [
        'live_stream_id',
        'contributor_id',
        'title',
        'category',
        'region',
        'language',
        'extracted_story',
        'ai_summary',
        'unanswered_questions',
        'suggested_links',
        'custodian_notes',
        'status',
        'approved_culture_id',
    ];

    protected $casts = [
        'unanswered_questions' => 'array',
        'suggested_links' => 'array',
    ];

    public function liveStream()
    {
        return $this->belongsTo(LiveStream::class);
    }

    public function contributor()
    {
        return $this->belongsTo(User::class, 'contributor_id');
    }

    public function approvedCulture()
    {
        return $this->belongsTo(Culture::class, 'approved_culture_id');
    }

    public function isPendingReview(): bool
    {
        return $this->status === 'submitted_to_moderation';
    }

    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }
}
