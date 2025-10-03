<?php

namespace App\QueryFilters;

use Spatie\QueryBuilder\Filters\Filter;
use Illuminate\Database\Eloquent\Builder;

class TimeFromUtc implements Filter
{
    public function __invoke(Builder $query, $value, string $property): void
    {
        // ожидаем формат HH:MM
        $time = sprintf('%s:00', $value); // -> HH:MM:SS
        $query->whereRaw("TIME(datetime) >= ?", [$time]);
    }
}