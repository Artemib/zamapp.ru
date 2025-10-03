<?php

namespace App\QueryFilters;

use Spatie\QueryBuilder\Filters\Filter;
use Illuminate\Database\Eloquent\Builder;

class DurationMin implements Filter
{
    public function __invoke(Builder $query, $value, string $property): void
    {
        $query->where('duration', '>=', (int)$value);
    }
}