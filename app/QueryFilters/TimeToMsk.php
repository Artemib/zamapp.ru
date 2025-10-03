<?php

namespace App\QueryFilters;

use Spatie\QueryBuilder\Filters\Filter;
use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;

class TimeToMsk implements Filter
{
    public function __invoke(Builder $query, $value, string $property): void
    {
        $timeUtc = Carbon::parse($value, 'Europe/Moscow')->setTimezone('UTC')->format('H:i:s');
        $query->whereRaw("TIME(datetime) <= ?", [$timeUtc]);
    }
}