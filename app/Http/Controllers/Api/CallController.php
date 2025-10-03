<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\CallRequest;
use App\Models\Call;
use App\QueryFilters\DateFromMsk;
use App\QueryFilters\DateFromUtc;
use App\QueryFilters\DateToMsk;
use App\QueryFilters\DateToUtc;
use App\QueryFilters\DurationMax;
use App\QueryFilters\DurationMin;
use App\QueryFilters\TimeFromMsk;
use App\QueryFilters\TimeFromUtc;
use App\QueryFilters\TimeToMsk;
use App\QueryFilters\TimeToUtc;
use App\Services\FileStorageService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;


class CallController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $calls = QueryBuilder::for(Call::class)
            ->allowedFilters([
                'id',
                'callid',
                AllowedFilter::exact('status'),
                AllowedFilter::partial('user_pbx'), // LIKE %значение%
                AllowedFilter::partial('client_phone'), // LIKE %значение%

                AllowedFilter::custom('date_from_utc', new DateFromUtc), //
                AllowedFilter::custom('date_to_utc', new DateToUtc), //
                AllowedFilter::custom('date_from_msk', new DateFromMsk), //
                AllowedFilter::custom('date_to_msk', new DateToMsk), //

                AllowedFilter::custom('time_from_utc', new TimeFromUtc),
                AllowedFilter::custom('time_to_utc', new TimeToUtc),
                AllowedFilter::custom('time_from_msk', new TimeFromMsk),
                AllowedFilter::custom('time_to_msk', new TimeToMsk),

                AllowedFilter::custom('duration_min', new DurationMin), //
                AllowedFilter::custom('duration_max', new DurationMax), //
            ])
            ->allowedSorts(['datetime', 'id'])
            ->defaultSort('-datetime') // минус = DESC
            ->paginate(100);

        return $calls;
    }


    /**
     * Store a newly created resource in storage.
     */
    public function store(CallRequest $request, FileStorageService $fileStorageService)
    {
        $data = $request->validated();

        $data['datetime'] = Carbon::createFromFormat('Ymd\THis\Z', $request->input('datetime'), 'UTC');
        $data['from_source_name'] = $request->input('from_source_name') . '_api_v1';

        $call = Call::create($data);

        return response()->json([
            'message' => 'Звонок успешно сохранен!',
            'call'    => $call,
        ], 201);

    }


    // Эти методы пока не нужны
    public function create()
    {
    }
    public function show(string $id)
    {
    }
    public function edit(string $id)
    {
    }
    public function update(Request $request, string $id)
    {
    }
    public function destroy(string $id)
    {
    }
}
