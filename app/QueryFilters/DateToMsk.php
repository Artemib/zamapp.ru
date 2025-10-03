<?php

namespace App\QueryFilters;

use Spatie\QueryBuilder\Filters\Filter;
use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;

class DateToMsk implements Filter
{
    public function __invoke(Builder $query, $value, string $property): void
    {
        // Принимаем дату в МСК и конвертируем в UTC, т.к. БД хранит в UTC
        $query->where('datetime', '<=', Carbon::parse($value, 'Europe/Moscow')->utc());
    }
}