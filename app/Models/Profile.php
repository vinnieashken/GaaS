<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Profile extends Model
{

    public static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            do {
                $key = (Str::random(20));
            } while (self::where('key', $key)->exists());

            $model->key = $key;
            do {
                $secret = (Str::random(64));
            } while (self::where('secret', $secret)->exists());

            $model->secret = $secret;
        });
    }
    protected $guarded = [];

    public function gateways()
    {
        return $this->belongsToMany(Gateway::class, 'profile_gateway');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function tokens()
    {
        return $this->hasMany(ProfileToken::class);
    }
}
