<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CulturalClassDiscussion extends Model
{
    use HasFactory;

    protected $fillable = [
        'cultural_class_id',
        'user_id',
        'parent_id',
        'timestamp_seconds',
        'message',
    ];

    protected $casts = [
        'timestamp_seconds' => 'integer',
    ];

    public function culturalClass()
    {
        return $this->belongsTo(CulturalClass::class, 'cultural_class_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function parent()
    {
        return $this->belongsTo(CulturalClassDiscussion::class, 'parent_id');
    }

    public function replies()
    {
        return $this->hasMany(CulturalClassDiscussion::class, 'parent_id')->with('user')->oldest();
    }
}
