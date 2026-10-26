<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LocalGovernment extends Model
{
    protected $fillable = ['country_id', 'state_id', 'name', 'is_custom', 'created_by'];

    protected $casts = [
        'is_custom' => 'boolean',
    ];

    public function country()
    {
        return $this->belongsTo(Country::class);
    }

    public function state()
    {
        return $this->belongsTo(State::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
