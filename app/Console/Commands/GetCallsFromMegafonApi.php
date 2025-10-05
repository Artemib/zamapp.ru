<?php

namespace App\Console\Commands;

use App\Models\Call;
use App\Services\PbxApiService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Carbon\Carbon;

class GetCallsFromMegafonApi extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'megafon:get-calls 
                            {--month=2025-09 : Месяц для получения звонков (формат: YYYY-MM)}
                            {--force : Принудительно перезаписать существующие звонки}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Получение звонков из API Мегафона';

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
        $this->info('🔍 Получаем звонки из API Мегафона...');

        $month = $this->option('month');
        $dateFrom = Carbon::createFromFormat('Y-m', $month)->startOfMonth();
        $dateTo = Carbon::createFromFormat('Y-m', $month)->endOfMonth();

        $this->info("📅 Период: {$dateFrom->format('d.m.Y')} - {$dateTo->format('d.m.Y')}");

        // Попробуем разные методы получения данных
        $methods = [
            'GET с Bearer токеном' => fn() => $this->getCallsWithBearerToken($dateFrom, $dateTo),
            'GET с токеном в параметрах' => fn() => $this->getCallsWithTokenParam($dateFrom, $dateTo),
            'POST с токеном в теле' => fn() => $this->getCallsWithPostToken($dateFrom, $dateTo),
            'POST с командой get_calls' => fn() => $this->getCallsWithCommand($dateFrom, $dateTo),
            'GET /api/v1/calls' => fn() => $this->getCallsFromApiV1($dateFrom, $dateTo),
            'GET /crmapi/v1/history' => fn() => $this->getCallsFromHistory($dateFrom, $dateTo),
        ];

        foreach ($methods as $methodName => $method) {
            $this->info("\n🔄 Пробуем: {$methodName}");
            
            try {
                $calls = $method();
                
                if ($calls && is_array($calls) && count($calls) > 0) {
                    $this->info("✅ Успешно! Получено звонков: " . count($calls));
                    
                    // Импортируем полученные звонки
                    $imported = $this->importCalls($calls, $this->option('force'));
                    
                    $this->info("📊 Импорт завершен. Обработано: {$imported['processed']}, Добавлено: {$imported['created']}, Обновлено: {$imported['updated']}, Пропущено: {$imported['skipped']}");
                    
                    return 0;
                } else {
                    $this->warn("⚠️  Данные не получены или пустые");
                }
                
            } catch (\Throwable $e) {
                $this->error("❌ Ошибка: " . $e->getMessage());
            }
        }

        $this->error("\n❌ Не удалось получить данные ни одним из методов");
        $this->info("💡 Рекомендации:");
        $this->info("   1. Проверьте правильность токена");
        $this->info("   2. Обратитесь к провайдеру АТС за документацией API");
        $this->info("   3. Используйте webhook для получения данных от АТС");

        return 1;
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
        ->timeout(30)
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
        $response = Http::timeout(30)
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
        ->timeout(30)
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
     * POST запрос с командой get_calls
     */
    private function getCallsWithCommand(Carbon $dateFrom, Carbon $dateTo): ?array
    {
        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
        ])
        ->timeout(30)
        ->post(config('pbx.api.base_url'), [
            'cmd' => 'get_calls',
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
     * GET запрос к /api/v1/calls
     */
    private function getCallsFromApiV1(Carbon $dateFrom, Carbon $dateTo): ?array
    {
        $baseUrl = str_replace('/crmapi/v1', '', config('pbx.api.base_url'));
        
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . config('pbx.api.token'),
        ])
        ->timeout(30)
        ->get($baseUrl . '/api/v1/calls', [
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
     * GET запрос к /crmapi/v1/history
     */
    private function getCallsFromHistory(Carbon $dateFrom, Carbon $dateTo): ?array
    {
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . config('pbx.api.token'),
        ])
        ->timeout(30)
        ->get(config('pbx.api.base_url') . '/history', [
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
            'callid' => $callData['callid'] ?? uniqid('megafon_'),
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
            'from_source_name' => 'Megafon API',
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
