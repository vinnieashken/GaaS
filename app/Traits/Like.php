<?php

namespace App\Traits;

trait Like
{
    public function scopeLike($query, $column, $value)
    {
        $driver = config('database.default');
        $operator = $driver === 'pgsql' ? 'ilike' : 'like';

        return $query->where($column, $operator, $value);
    }
}
