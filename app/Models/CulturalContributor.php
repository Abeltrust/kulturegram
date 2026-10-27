<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CulturalContributor extends Model
{
    use HasFactory;

    protected $fillable = [
        'culture_id',
        'user_id',
        'public_name',
        'role',
        'attribution_preference',
        'relationship_to_material',
        'review_responsibility',
        'review_decision',
        'review_notes',
        'reviewed_at',
        'permission_to_display',
    ];

    protected $casts = [
        'reviewed_at' => 'datetime',
        'permission_to_display' => 'boolean',
    ];

    public function culture()
    {
        return $this->belongsTo(Culture::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getDisplayNameAttribute(): string
    {
        if (!$this->permission_to_display) {
            return 'Community Member (Identity Protected)';
        }
        if ($this->public_name) {
            return $this->public_name;
        }
        if ($this->user) {
            return $this->user->name;
        }
        return $this->attribution_preference ?: 'Unattributed Contributor';
    }

    public function scopePubliclyVisible($query)
    {
        return $query->where('permission_to_display', true);
    }
}
