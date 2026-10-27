<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Resonance extends Model
{
    protected $fillable = [
        'post_id',
        'parent_id',
        'user_id',
        'content',
        'taps_count',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function post()
    {
        return $this->belongsTo(Post::class);
    }

    public function parent()
    {
        return $this->belongsTo(Resonance::class, 'parent_id');
    }

    public function replies()
    {
        return $this->hasMany(Resonance::class, 'parent_id')->with(['user'])->oldest();
    }

    public function resonable()
    {
        return $this->morphTo();
    }

    public function interactions()
    {
        return $this->morphMany(Interaction::class, 'interactable');
    }

    public function taps()
    {
        return $this->interactions()->where('type', 'tap');
    }

    public function isTappedBy(?User $user = null): bool
    {
        if (!$user) {
            $user = auth()->user();
        }
        if (!$user) {
            return false;
        }
        return $this->interactions()
            ->where('user_id', $user->id)
            ->where('type', 'tap')
            ->exists();
    }

    public function isAuthorLiked(): bool
    {
        if (!$this->post || !$this->post->user_id) {
            return false;
        }
        return $this->interactions()
            ->where('user_id', $this->post->user_id)
            ->where('type', 'tap')
            ->exists();
    }

    public function getFormattedContentAttribute()
    {
        return \App\Services\ContentParserService::format($this->content);
    }
}
