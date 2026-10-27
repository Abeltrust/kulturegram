<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CulturalTranslation extends Model
{
    use HasFactory;

    protected $fillable = [
        'culture_id',
        'language',
        'dialect',
        'translated_title',
        'translated_content',
        'translator_name',
        'provenance',
        'review_status',
        'alternative_spellings',
        'dialect_notes',
    ];

    public function culture()
    {
        return $this->belongsTo(Culture::class);
    }
}
