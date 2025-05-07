<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Gateway extends Model
{
    protected $guarded = [];

    protected $casts = [
        'config' => 'object',
    ];

    public function currencies()
    {
        return $this->belongsToMany(Currency::class, 'gateway_currency');
    }

    public function profiles()
    {
        return $this->belongsToMany(Profile::class, 'profile_gateway');
    }

}
