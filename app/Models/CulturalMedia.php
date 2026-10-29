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
        'section',
        'file_path',
        'external_url',
        'book_title',
        'book_author',
        'book_year',
        'book_publisher',
        'book_isbn',
        'book_excerpt',
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
