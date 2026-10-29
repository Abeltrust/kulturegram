<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'from_user_id',
        'type',
        'title',
        'message',
        'data',
        'read',
    ];

    protected $casts = [
        'data' => 'array',
        'read' => 'boolean',
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function fromUser()
    {
        return $this->belongsTo(User::class, 'from_user_id');
    }

    // Scopes
    public function scopeUnread($query)
    {
        return $query->where('read', false);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    // Helper methods
    public function markAsRead()
    {
        $this->update(['read' => true]);
    }

    public function getTimeAgoAttribute()
    {
        return $this->created_at->diffForHumans();
    }

    /**
     * Get message with commenter/sender name linked to their profile.
     */
    public function getFormattedMessageAttribute(): string
    {
        $msg = e($this->message);

        if ($this->fromUser) {
            $name = e($this->fromUser->name);
            $url = e($this->fromUser->profile_url);
            $link = '<a href="' . $url . '" class="font-bold text-amber-600 dark:text-amber-400 hover:underline transition">' . $name . '</a>';

            if (str_starts_with($msg, $name)) {
                $msg = $link . substr($msg, strlen($name));
            } elseif (str_contains($msg, $name)) {
                $msg = preg_replace('/' . preg_quote($name, '/') . '/', $link, $msg, 1);
            }
        }

        return $msg;
    }
}
