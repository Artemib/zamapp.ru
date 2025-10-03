<?php

namespace App\QueryFilters;

use Spatie\QueryBuilder\Filters\Filter;
use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;

class DateFromUtc implements Filter
{
    public function __invoke(Builder $query, $value, string $property): void
    {
        $query->where('datetime', '>=', Carbon::parse($value, 'UTC'));
    }
}