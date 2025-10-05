<?php

namespace App\Console\Commands;

use App\Models\Call;
use App\Services\PbxApiService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class ImportCallsFromMegafon extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'calls:import-megafon|mimport 
                            {--from= : Дата начала (YYYY-MM-DD)}
                            {--to= : Дата окончания (YYYY-MM-DD)}
                            {--tz=msk : Часовой пояс (msk или utc)}
                            {--clear : Очистить существующие данные перед импортом}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Импортировать звонки из API Мегафон за указанный период';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🚀 Начинаем импорт звонков из API Мегафон...');

        // Получаем параметры
        $dateFrom = $this->option('from');
        $dateTo = $this->option('to');
        $timezone = $this->option('tz');
        $clear = $this->option('clear');

        // Валидация часового пояса
        if (!in_array($timezone, ['msk', 'utc'])) {
            $this->error('❌ Неверный часовой пояс. Используйте: msk или utc');
            return 1;
        }

        // Если даты не указаны, используем текущий месяц
        if (!$dateFrom) {
            $dateFrom = Carbon::now()->startOfMonth()->format('Y-m-d');
        }
        if (!$dateTo) {
            $dateTo = Carbon::now()->endOfMonth()->format('Y-m-d');
        }

        try {
            // Парсим даты в указанном часовом поясе
            if ($timezone === 'msk') {
                $dateFromCarbon = Carbon::parse($dateFrom . ' 00:00:00', 'Europe/Moscow');
                $dateToCarbon = Carbon::parse($dateTo . ' 23:59:59', 'Europe/Moscow');
            } else {
                $dateFromCarbon = Carbon::parse($dateFrom . ' 00:00:00', 'UTC');
                $dateToCarbon = Carbon::parse($dateTo . ' 23:59:59', 'UTC');
            }

            $this->info("📅 Период: {$dateFromCarbon->format('d.m.Y H:i')} - {$dateToCarbon->format('d.m.Y H:i')} ({$timezone})");
            $this->info("🌍 Часовой пояс: " . ($timezone === 'msk' ? 'Europe/Moscow' : 'UTC'));

            // Очищаем данные если нужно
            if ($clear) {
                $this->info('🧹 Очищаем существующие данные...');
                $this->call('calls:clear', ['--force' => true]);
            }

            $pbxService = new PbxApiService();

            // Проверяем соединение с API
            $this->info('🔍 Проверяем соединение с API Мегафон...');
            if (!$pbxService->testConnection()) {
                $this->error('❌ Не удалось подключиться к API Мегафон. Проверьте настройки.');
                return 1;
            }
            $this->info('✅ Соединение с API установлено');

            // Получаем данные звонков
            $this->info('📊 Получаем данные из API Мегафон...');
            $this->info("🔍 Запрашиваем период: {$dateFromCarbon->format('Y-m-d H:i:s')} - {$dateToCarbon->format('Y-m-d H:i:s')} ({$timezone})");
            $this->info("🔍 API получит период: {$dateFromCarbon->utc()->format('Ymd\THis\Z')} - {$dateToCarbon->utc()->format('Ymd\THis\Z')} (UTC)");
            
            $callsData = $pbxService->getCalls($dateFromCarbon, $dateToCarbon);
            
            if ($callsData === null) {
                $this->error('❌ Не удалось получить данные из API.');
                return 1;
            }

            if (empty($callsData)) {
                $this->warn('⚠️ Данные о звонках не найдены за указанный период');
                return 0;
            }

            $this->info("📈 Найдено звонков (всего): " . count($callsData));
            
            // Фильтруем данные по дате на стороне клиента
            $filteredCallsData = $this->filterCallsByDate($callsData, $dateFromCarbon, $dateToCarbon);
            $this->info("📅 Звонков в указанном периоде: " . count($filteredCallsData));
            
            // Показываем примеры данных
            if (count($callsData) > 0) {
                $this->info("📋 Пример данных (первый звонок):");
                $example = $callsData[0];
                $this->line("   CallID: " . ($example['uid'] ?? 'N/A'));
                $this->line("   DateTime: " . ($example['start'] ?? 'N/A'));
                $this->line("   Type: " . ($example['type'] ?? 'N/A'));
                $this->line("   Status: " . ($example['status'] ?? 'N/A'));
            }
            
            if (empty($filteredCallsData)) {
                $this->warn('⚠️ В указанном периоде звонков не найдено');
                return 0;
            }
            
            $callsData = $filteredCallsData;

            // Импортируем данные
            $this->importCalls($callsData);

            $this->info('✅ Импорт завершен успешно!');

        } catch (\Exception $e) {
            $this->error('❌ Ошибка при импорте: ' . $e->getMessage());
            Log::error('Import Megafon Calls Error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);
            return 1;
        }

        return 0;
    }

    /**
     * Импортировать звонки в базу данных
     *
     * @param array $callsData
     */
    private function importCalls(array $callsData): void
    {
        $bar = $this->output->createProgressBar(count($callsData));
        $bar->start();

        $imported = 0;
        $skipped = 0;
        $errors = 0;

        DB::beginTransaction();

        try {
            foreach ($callsData as $callData) {
                try {
                    // Преобразуем данные в формат модели
                    $callAttributes = $this->transformCallData($callData);

                    // Проверяем, существует ли уже такой звонок
                    $existingCall = Call::where('callid', $callAttributes['callid'])->first();
                    
                    if ($existingCall) {
                        $skipped++;
                        $bar->advance();
                        continue;
                    }

                    // Создаем новый звонок
                    Call::create($callAttributes);
                    $imported++;

                } catch (\Exception $e) {
                    $errors++;
                    Log::error('Error importing call: ' . $e->getMessage(), [
                        'call_data' => $callData,
                    ]);
                }

                $bar->advance();
            }

            DB::commit();

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }

        $bar->finish();
        $this->newLine();

        $this->info("📊 Результаты импорта:");
        $this->info("   ✅ Импортировано: {$imported}");
        $this->info("   ⏭️ Пропущено (дубликаты): {$skipped}");
        $this->info("   ❌ Ошибок: {$errors}");
    }

    /**
     * Преобразовать данные из API в формат модели Call
     *
     * @param array $callData
     * @return array
     */
    private function transformCallData(array $callData): array
    {
        // Маппинг статусов из API в статусы системы
        $statusMapping = config('pbx.status_mapping', []);
        $typeMapping = config('pbx.type_mapping', []);

        // API Мегафон использует другие названия полей
        return [
            'callid' => $callData['uid'] ?? 'unknown_' . time(),
            'datetime' => $this->parseDateTime($callData['start'] ?? null),
            'type' => $typeMapping[$callData['type'] ?? 'in'] ?? 'in',
            'status' => $statusMapping[$callData['status'] ?? 'success'] ?? 'success',
            'client_phone' => $callData['client'] ?? '',
            'user_pbx' => $callData['user'] ?? '',
            'diversion_phone' => $callData['diversion'] ?? '',
            'duration' => (int) ($callData['duration'] ?? 0),
            'wait' => (int) ($callData['wait'] ?? 0),
            'link_record_pbx' => $callData['record'] ?? null,
            'link_record_crm' => null,
            'transcribation' => null,
            'from_source_name' => 'Megafon API',
        ];
    }

    /**
     * Фильтровать звонки по дате
     *
     * @param array $callsData
     * @param Carbon $dateFrom
     * @param Carbon $dateTo
     * @return array
     */
    private function filterCallsByDate(array $callsData, Carbon $dateFrom, Carbon $dateTo): array
    {
        $filtered = [];
        
        foreach ($callsData as $call) {
            if (empty($call['start'])) {
                continue;
            }
            
            try {
                $callDate = Carbon::parse($call['start']);
                
                if ($callDate->between($dateFrom, $dateTo)) {
                    $filtered[] = $call;
                }
            } catch (\Exception $e) {
                // Пропускаем звонки с некорректной датой
                continue;
            }
        }
        
        return $filtered;
    }

    /**
     * Парсить дату и время из различных форматов
     *
     * @param mixed $dateTime
     * @return string|null
     */
    private function parseDateTime($dateTime): ?string
    {
        if (empty($dateTime)) {
            return null;
        }

        try {
            // Если это уже строка в правильном формате
            if (is_string($dateTime)) {
                // API Мегафон возвращает дату в ISO формате: 2025-10-04T23:14:05Z
                $carbon = Carbon::parse($dateTime);
                return $carbon->format('Y-m-d H:i:s');
            }

            // Если это timestamp
            if (is_numeric($dateTime)) {
                $carbon = Carbon::createFromTimestamp($dateTime);
                return $carbon->format('Y-m-d H:i:s');
            }

            return null;

        } catch (\Exception $e) {
            Log::warning('Failed to parse datetime: ' . $e->getMessage(), [
                'datetime' => $dateTime,
            ]);
            return null;
        }
    }
}
