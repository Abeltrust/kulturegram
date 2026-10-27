<?php

// app/Models/LiveStream.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LiveStream extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'user_id',
        'thumbnail',
        'category',
        'viewers_count',
        'is_live',
        'scheduled_at',
        'completed_at',
    ];

    protected $casts = [
        'is_live' => 'boolean',
        'scheduled_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function host()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function messages()
    {
        return $this->hasMany(LiveStreamMessage::class, 'live_stream_id');
    }

    public function flaggedMessages()
    {
        return $this->hasMany(LiveStreamMessage::class, 'live_stream_id')
            ->where('is_flagged', true)
            ->where('flag_status', 'flagged');
    }
}

