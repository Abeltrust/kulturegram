<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Carbon\Carbon;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'username',
        'email',
        'password',
        'bio',
        'location',
        'website',
        'avatar',
        'cover_photo',
        'cultural_background',
        'languages',
        'cultural_interests',
        'is_online',
        'last_seen',
        'preferences',
        'is_admin',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'languages' => 'array',
        'cultural_interests' => 'array',
        'is_online' => 'boolean',
        'is_admin' => 'boolean',
        'last_seen' => 'datetime',
        'preferences' => 'array',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */
    public function posts()
    {
        return $this->hasMany(Post::class);
    }

    public function lifeChapters()
    {
        return $this->hasMany(LifeChapter::class);
    }

    public function vaultItems()
    {
        return $this->hasMany(VaultItem::class);
    }

    public function cultures()
    {
        return $this->hasMany(Culture::class, 'submitted_by');
    }

    public function communities()
    {
        return $this->belongsToMany(Community::class, 'community_members');
    }

    public function events()
    {
        return $this->hasMany(Event::class, 'organizer_id');
    }

    public function attendingEvents()
    {
        return $this->belongsToMany(Event::class, 'event_attendees');
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }

    public function interactions()
    {
        return $this->hasMany(Interaction::class);
    }

    public function conversations()
    {
        return $this->belongsToMany(Conversation::class, 'conversation_participants');
    }

    public function messages()
    {
        return $this->hasMany(Message::class);
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    public function transactionsReceived()
    {
        return $this->hasMany(Transaction::class, 'host_id');
    }

    public function userRelationships()
    {
        return $this->hasMany(UserRelationship::class);
    }

    public function incrementRelationshipWith(User $user, $points = 1)
    {
        $relationship = $this->userRelationships()
            ->firstOrCreate(['target_user_id' => $user->id]);

        $relationship->increment('points', $points);

        // Level up logic (example: 10 points per level)
        $newLevel = floor($relationship->points / 10) + 1;
        if ($newLevel > $relationship->level) {
            $relationship->update(['level' => $newLevel]);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Helper Methods
    |--------------------------------------------------------------------------
    */
    public function hasTapped($model)
    {
        return $this->interactions()
            ->where('interactable_type', get_class($model))
            ->where('interactable_id', $model->id)
            ->where('type', 'tap')
            ->exists();
    }

    public function hasLockedIn($model)
    {
        return $this->interactions()
            ->where('interactable_type', get_class($model))
            ->where('interactable_id', $model->id)
            ->where('type', 'lockin')
            ->exists();
    }

    public function hasResonated($model)
    {
        return $this->interactions()
            ->where('interactable_type', get_class($model))
            ->where('interactable_id', $model->id)
            ->where('type', 'resonance')
            ->exists();
    }

    public function hasCheckedIn($model)
    {
        return $this->interactions()
            ->where('interactable_type', get_class($model))
            ->where('interactable_id', $model->id)
            ->where('type', 'checkin')
            ->exists();
    }

    public function getInitialsAttribute()
    {
        $names = explode(' ', $this->name);
        $initials = '';
        foreach ($names as $name) {
            $initials .= strtoupper(substr($name, 0, 1));
        }
        return $initials;
    }

    /*
    |--------------------------------------------------------------------------
    | Online/Status Helpers
    |--------------------------------------------------------------------------
    */
    public function getProfilePhotoUrlAttribute()
    {
        return $this->avatar
            ? asset('storage/avatars/' . $this->avatar)
            : 'https://ui-avatars.com/api/?name=' . urlencode($this->name) . '&color=F59E0B&background=FEF3C7';
    }

    public function getCoverPhotoUrlAttribute()
    {
        return $this->cover_photo
            ? asset('storage/covers/' . $this->cover_photo)
            : null;
    }

    public function markOnline()
    {
        $this->update([
            'is_online' => true,
            'last_seen' => now(),
        ]);
    }

    public function markOffline()
    {
        $this->update([
            'is_online' => false,
            'last_seen' => now(),
        ]);
    }

    public function getStatusAttribute()
    {
        if ($this->is_online) {
            return 'online';
        }


        if ($this->last_seen && $this->last_seen->gt(Carbon::now()->subMinutes(5))) {
            return 'recent';
        }

        return 'offline';
    }
}
