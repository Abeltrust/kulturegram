<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AiFeedbackLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'feedback_type',
        'target_type',
        'target_id',
        'rating',
        'reason',
        'user_correction',
        'metadata',
        'status',
    ];

    protected $casts = [
        'rating' => 'integer',
        'metadata' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
