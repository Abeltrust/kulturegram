<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CulturalClassEnrollment extends Model
{
    use HasFactory;

    protected $fillable = [
        'cultural_class_id',
        'user_id',
        'price_paid',
        'access_status',
        'progress_seconds',
        'is_completed',
        'last_watched_at',
    ];

    protected $casts = [
        'price_paid' => 'decimal:2',
        'progress_seconds' => 'integer',
        'is_completed' => 'boolean',
        'last_watched_at' => 'datetime',
    ];

    public function culturalClass()
    {
        return $this->belongsTo(CulturalClass::class, 'cultural_class_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
