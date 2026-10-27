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
        'title',
        'proposed_content',
        'evidence_notes',
        'status',
        'reviewer_id',
        'reviewer_notes',
        'reviewed_at',
    ];

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
