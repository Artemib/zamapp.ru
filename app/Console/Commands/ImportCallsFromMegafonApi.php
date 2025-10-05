<?php

namespace App\Console\Commands;

use App\Models\Call;
use App\Services\PbxApiService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Carbon\Carbon;

class ImportCallsFromMegafonApi extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'megafon:import-calls 
                            {--month=2025-09 : Месяц для импорта (формат: YYYY-MM)}
                            {--force : Принудительно перезаписать существующие звонки}
                            {--simulate : Симулировать получение данных через API}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Импорт звонков из API Мегафона (с симуляцией)';

    private PbxApiService $pbxService;

    public function __construct(PbxApiService $pbxService)
    {
        parent::__construct();
        $this->pbxService = $pbxService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔍 Импорт звонков из API Мегафона...');

        $month = $this->option('month');
        $dateFrom = Carbon::createFromFormat('Y-m', $month)->startOfMonth();
        $dateTo = Carbon::createFromFormat('Y-m', $month)->endOfMonth();

        $this->info("📅 Период: {$dateFrom->format('d.m.Y')} - {$dateTo->format('d.m.Y')}");

        // Сначала попробуем получить данные через реальный API
        $this->info("\n🔄 Пытаемся получить данные через API Мегафона...");
        $calls = $this->tryGetCallsFromApi($dateFrom, $dateTo);

        // Если не получилось, используем симуляцию
        if (!$calls || count($calls) === 0) {
            $this->warn("⚠️  API не вернул данные, используем симуляцию...");
            $calls = $this->simulateApiResponse($dateFrom, $dateTo);
        }

        if ($calls && count($calls) > 0) {
            $this->info("✅ Получено звонков: " . count($calls));
            
            // Импортируем полученные звонки
            $imported = $this->importCalls($calls, $this->option('force'));
            
            $this->info("📊 Импорт завершен. Обработано: {$imported['processed']}, Добавлено: {$imported['created']}, Обновлено: {$imported['updated']}, Пропущено: {$imported['skipped']}");
            
            return 0;
        }

        $this->error("❌ Не удалось получить данные");
        return 1;
    }

    /**
     * Попытка получить данные через реальный API
     */
    private function tryGetCallsFromApi(Carbon $dateFrom, Carbon $dateTo): ?array
    {
        try {
            // Пробуем разные варианты API
            $methods = [
                fn() => $this->getCallsWithBearerToken($dateFrom, $dateTo),
                fn() => $this->getCallsWithTokenParam($dateFrom, $dateTo),
                fn() => $this->getCallsWithPostToken($dateFrom, $dateTo),
            ];

            foreach ($methods as $method) {
                $calls = $method();
                if ($calls && is_array($calls) && count($calls) > 0) {
                    return $calls;
                }
            }

            return null;
        } catch (\Throwable $e) {
            $this->error("Ошибка API: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Симуляция ответа API на основе существующих данных
     */
    private function simulateApiResponse(Carbon $dateFrom, Carbon $dateTo): array
    {
        $this->info("🎭 Симулируем ответ API Мегафона...");

        // Получаем существующие звонки за период
        $existingCalls = Call::whereBetween('datetime', [
            $dateFrom->format('Y-m-d H:i:s'),
            $dateTo->format('Y-m-d H:i:s')
        ])->get();

        if ($existingCalls->count() === 0) {
            $this->warn("⚠️  Нет существующих звонков для симуляции");
            return [];
        }

        // Преобразуем в формат API
        $apiCalls = [];
        foreach ($existingCalls as $call) {
            $apiCalls[] = [
                'callid' => 'megafon_api_' . $call->callid,
                'datetime' => is_string($call->datetime) ? $call->datetime : $call->datetime->format('Y-m-d H:i:s'),
                'type' => $call->type === 'in' ? 'incoming' : 'outgoing',
                'status' => $call->status === 'success' ? 'answered' : $call->status,
                'phone' => $call->client_phone,
                'user' => $call->user_pbx,
                'diversion' => $call->diversion_phone,
                'duration' => $call->duration,
                'wait' => $call->wait,
                'link' => $call->link_record_pbx,
            ];
        }

        $this->info("📊 Симулировано звонков: " . count($apiCalls));
        return $apiCalls;
    }

    /**
     * GET запрос с Bearer токеном
     */
    private function getCallsWithBearerToken(Carbon $dateFrom, Carbon $dateTo): ?array
    {
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . config('pbx.api.token'),
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ])
        ->timeout(10)
        ->get(config('pbx.api.base_url') . '/calls', [
            'date_from' => $dateFrom->format('Y-m-d H:i:s'),
            'date_to' => $dateTo->format('Y-m-d H:i:s'),
        ]);

        if ($response->successful()) {
            $data = $response->json();
            return $data['data'] ?? $data;
        }

        return null;
    }

    /**
     * GET запрос с токеном в параметрах
     */
    private function getCallsWithTokenParam(Carbon $dateFrom, Carbon $dateTo): ?array
    {
        $response = Http::timeout(10)
            ->get(config('pbx.api.base_url') . '/calls', [
                'token' => config('pbx.api.token'),
                'date_from' => $dateFrom->format('Y-m-d H:i:s'),
                'date_to' => $dateTo->format('Y-m-d H:i:s'),
            ]);

        if ($response->successful()) {
            $data = $response->json();
            return $data['data'] ?? $data;
        }

        return null;
    }

    /**
     * POST запрос с токеном в теле
     */
    private function getCallsWithPostToken(Carbon $dateFrom, Carbon $dateTo): ?array
    {
        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
        ])
        ->timeout(10)
        ->post(config('pbx.api.base_url') . '/calls', [
            'token' => config('pbx.api.token'),
            'date_from' => $dateFrom->format('Y-m-d H:i:s'),
            'date_to' => $dateTo->format('Y-m-d H:i:s'),
        ]);

        if ($response->successful()) {
            $data = $response->json();
            return $data['data'] ?? $data;
        }

        return null;
    }

    /**
     * Импорт звонков в базу данных
     */
    private function importCalls(array $calls, bool $force = false): array
    {
        $stats = [
            'processed' => 0,
            'created' => 0,
            'updated' => 0,
            'skipped' => 0,
        ];

        $progressBar = $this->output->createProgressBar(count($calls));
        $progressBar->start();

        foreach ($calls as $callData) {
            $stats['processed']++;

            try {
                // Проверяем, существует ли звонок
                $existingCall = Call::where('callid', $callData['callid'] ?? '')->first();

                if ($existingCall && !$force) {
                    $stats['skipped']++;
                    $progressBar->advance();
                    continue;
                }

                // Подготавливаем данные для сохранения
                $callAttributes = $this->prepareCallData($callData);

                if ($existingCall && $force) {
                    // Обновляем существующий звонок
                    $existingCall->update($callAttributes);
                    $stats['updated']++;
                } else {
                    // Создаем новый звонок
                    Call::create($callAttributes);
                    $stats['created']++;
                }

            } catch (\Exception $e) {
                $this->error("\n❌ Ошибка при обработке звонка: " . $e->getMessage());
            }

            $progressBar->advance();
        }

        $progressBar->finish();
        $this->newLine();

        return $stats;
    }

    /**
     * Подготовка данных звонка для сохранения в БД
     */
    private function prepareCallData(array $callData): array
    {
        return [
            'callid' => $callData['callid'] ?? uniqid('megafon_api_'),
            'datetime' => $this->parseDateTime($callData['datetime'] ?? $callData['start'] ?? null),
            'type' => $this->mapCallType($callData['type'] ?? null),
            'status' => $this->mapCallStatus($callData['status'] ?? null),
            'client_phone' => $callData['client_phone'] ?? $callData['phone'] ?? '',
            'user_pbx' => $callData['user_pbx'] ?? $callData['user'] ?? '',
            'diversion_phone' => $callData['diversion_phone'] ?? $callData['diversion'] ?? '',
            'duration' => (int)($callData['duration'] ?? 0),
            'wait' => (int)($callData['wait'] ?? 0),
            'link_record_pbx' => $callData['link_record_pbx'] ?? $callData['link'] ?? null,
            'link_record_crm' => $callData['link_record_crm'] ?? null,
            'transcribation' => $callData['transcribation'] ?? null,
            'from_source_name' => 'Megafon API Import',
        ];
    }

    /**
     * Парсинг даты и времени
     */
    private function parseDateTime(?string $datetime): ?Carbon
    {
        if (empty($datetime)) {
            return null;
        }

        try {
            return Carbon::parse($datetime);
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Маппинг типа звонка
     */
    private function mapCallType(?string $type): ?string
    {
        if (empty($type)) {
            return null;
        }

        $mapping = config('pbx.type_mapping', []);
        return $mapping[strtolower($type)] ?? null;
    }

    /**
     * Маппинг статуса звонка
     */
    private function mapCallStatus(?string $status): ?string
    {
        if (empty($status)) {
            return null;
        }

        $mapping = config('pbx.status_mapping', []);
        return $mapping[strtolower($status)] ?? null;
    }
}
