<?php

namespace App\QueryFilters;

use Spatie\QueryBuilder\Filters\Filter;
use Illuminate\Database\Eloquent\Builder;

class TimeToUtc implements Filter
{
    public function __invoke(Builder $query, $value, string $property): void
    {
        $time = sprintf('%s:00', $value);
        $query->whereRaw("TIME(datetime) <= ?", [$time]);
    }
}