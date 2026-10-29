<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CulturalRevision extends Model
{
    use HasFactory;

    protected $fillable = [
        'culture_id',
        'user_id',
        'contributor_name',
        'contributor_email',
        'revision_type',
        'target_section',
        'title',
        'proposed_content',
        'image_path',
        'video_url',
        'video_path',
        'book_title',
        'book_author',
        'book_year',
        'book_publisher',
        'book_isbn',
        'book_excerpt',
        'evidence_notes',
        'status',
        'cultural_media_id',
        'reviewer_id',
        'reviewer_notes',
        'reviewed_at',
    ];

    public function culturalMedia()
    {
        return $this->belongsTo(CulturalMedia::class, 'cultural_media_id');
    }

    protected $casts = [
        'reviewed_at' => 'datetime',
    ];

    public function culture()
    {
        return $this->belongsTo(Culture::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewer_id');
    }
}
