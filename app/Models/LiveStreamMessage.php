<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LiveStreamMessage extends Model
{
    use HasFactory;

    protected $fillable = [
        'live_stream_id',
        'user_id',
        'message',
        'is_flagged',
        'flag_status',
        'flag_category',
        'flag_reason',
        'violated_rule',
        'flag_rule',
        'reviewed_by',
        'reviewed_at',
    ];

    public function getFlagRuleAttribute(): ?string
    {
        return $this->violated_rule;
    }

    public function setFlagRuleAttribute(?string $value): void
    {
        $this->attributes['violated_rule'] = $value;
    }

    protected $casts = [
        'is_flagged' => 'boolean',
        'reviewed_at' => 'datetime',
    ];

    public function stream()
    {
        return $this->belongsTo(LiveStream::class, 'live_stream_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function scopeClean($query)
    {
        return $query->where('flag_status', '!=', 'removed');
    }

    public function scopeFlagged($query)
    {
        return $query->where('is_flagged', true)->where('flag_status', 'flagged');
    }
}
