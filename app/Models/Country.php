<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Country extends Model
{
    protected $fillable = ['name', 'iso2'];

    protected $appends = ['flag'];

    public function states()
    {
        return $this->hasMany(State::class);
    }

    public function localGovernments()
    {
        return $this->hasMany(LocalGovernment::class);
    }

    /**
     * Emoji flag built from the ISO alpha-2 code (e.g. "NG" => 🇳🇬).
     */
    public function getFlagAttribute(): string
    {
        $code = strtoupper($this->iso2 ?? '');

        if (strlen($code) !== 2) {
            return '';
        }

        return mb_chr(0x1F1E6 + ord($code[0]) - 65) . mb_chr(0x1F1E6 + ord($code[1]) - 65);
    }
}
