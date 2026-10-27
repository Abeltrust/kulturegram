<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CulturalRelationship extends Model
{
    use HasFactory;

    protected $fillable = [
        'source_type',
        'source_id',
        'target_type',
        'target_id',
        'relationship_type',
        'confidence',
        'explanation',
        'verified_by_human',
        'metadata',
    ];

    protected $casts = [
        'confidence' => 'float',
        'verified_by_human' => 'boolean',
        'metadata' => 'array',
    ];

    /**
     * Polymorphic relation to source entity.
     */
    public function source()
    {
        return $this->morphTo(__FUNCTION__, 'source_type', 'source_id');
    }

    /**
     * Polymorphic relation to target entity.
     */
    public function target()
    {
        return $this->morphTo(__FUNCTION__, 'target_type', 'target_id');
    }

    /**
     * Query scope for human verified connections.
     */
    public function scopeVerified($query)
    {
        return $query->where('verified_by_human', true);
    }
}
