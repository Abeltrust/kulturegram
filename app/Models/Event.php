<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    use HasFactory;

     
    
    protected $fillable = [
        'title',
        'description',
        'event_date',
        'event_time',
        'location',
        'type',
        'image',
        'max_attendees',
        'price',
        'is_online',
        'meeting_link',
        'requirements',
        'organizer_id',
        'community_id',
        'accepts_contributions',
    ];

    protected $casts = [
        'event_date' => 'date',
        'event_time' => 'datetime:H:i',
        'price' => 'decimal:2',
        'is_online' => 'boolean',
        'requirements' => 'array',
    ];

    // Relationships
    public function organizer()
    {
        return $this->belongsTo(User::class, 'organizer_id');
    }

    public function attendees()
    {
        return $this->belongsToMany(User::class, 'event_attendees');
    }

    public function community()
    {
        return $this->belongsTo(Community::class);
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    // Scopes
    public function scopeUpcoming($query)
    {
        return $query->where('event_date', '>=', now()->toDateString());
    }

    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    // Helper methods
    public function incrementAttendees()
    {
        $this->increment('attendees_count');
    }

    public function decrementAttendees()
    {
        $this->decrement('attendees_count');
    }

    public function isFull()
    {
        return $this->max_attendees && $this->attendees_count >= $this->max_attendees;
    }
}