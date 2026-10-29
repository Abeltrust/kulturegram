<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CommunityPost extends Model
{
    use HasFactory;

    protected $fillable = ['community_id', 'user_id', 'content', 'image', 'video', 'audio'];

    protected $casts = [
        'user_id' => 'integer',
        'community_id' => 'integer',
    ];

    public function community()
    {
        return $this->belongsTo(Community::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

     public function taps()
    {
        return $this->morphMany(Tap::class, 'tappable');
    }

   
    public function comments()
    {
        return $this->morphMany(Comment::class, 'commentable');
    }

    
    public function resonances()
    {
        return $this->morphMany(Resonance::class, 'resonable');
    }



}

