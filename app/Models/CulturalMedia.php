<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CulturalMedia extends Model
{
    use HasFactory;

    protected $table = 'cultural_media';

    protected $fillable = [
        'culture_id',
        'media_type',
        'file_path',
        'caption',
        'creator_attribution',
        'creation_date',
        'copyright_info',
        'license_type',
        'has_consent',
        'access_restriction',
        'processing_status',
    ];

    protected $casts = [
        'has_consent' => 'boolean',
    ];

    public function culture()
    {
        return $this->belongsTo(Culture::class);
    }

    public function scopePublicOnly($query)
    {
        return $query->where('access_restriction', 'public');
    }
}
