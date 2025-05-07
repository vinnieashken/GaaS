<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Order extends Model
{
    const STATUS_SUCCESS = 'SUCCESS';
    const STATUS_PENDING = 'PENDING';
    const STATUS_FAILED = 'FAILED';
    const STATUS_PROCESSING = 'PROCESSING';

    protected $guarded = [];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            do {
                $uuid = strtoupper(Str::random(8));
            } while (self::where('identifier', $uuid)->exists());

            $model->identifier = $uuid;

            do {
                $uuid = (string) Str::uuid();
            } while (self::where('uuid', $uuid)->exists());

            $model->uuid = $uuid;
        });
    }

    public function getRouteKeyName()
    {
        return 'uuid';
    }


    public function gateway()
    {
        return $this->belongsTo(Gateway::class);
    }

    public function transaction()
    {
        return $this->hasOne(Transaction::class);
    }
}
