<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Language extends Model
{
    protected $fillable = ['name', 'is_custom'];

    protected $casts = [
        'is_custom' => 'boolean',
    ];

    public function users()
    {
        return $this->belongsToMany(User::class);
    }
}
