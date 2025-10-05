<?php

namespace App\Console\Commands;

use App\Models\Call;
use App\Services\PbxApiService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class ImportSeptemberCalls extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'calls:import-september|sept {--test : Использовать тестовые данные вместо API}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Импортировать звонки за сентябрь 2025 года из API Мегафон';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🚀 Начинаем импорт звонков за сентябрь 2025...');

        $pbxService = new PbxApiService();

        // Проверяем соединение с API
        if (!$this->option('test')) {
            $this->info('🔍 Проверяем соединение с API Мегафон...');
            if (!$pbxService->testConnection()) {
                $this->error('❌ Не удалось подключиться к API Мегафон. Проверьте настройки.');
                return 1;
            }
            $this->info('✅ Соединение с API установлено');
        }

        try {
            // Получаем данные звонков
            if ($this->option('test')) {
                $this->info('📊 Получаем тестовые данные...');
                $callsData = $pbxService->getTestCalls();
            } else {
                $this->info('📊 Получаем данные из API Мегафон...');
                $callsData = $pbxService->getCallsForSeptember();
                
                if ($callsData === null) {
                    $this->error('❌ Не удалось получить данные из API. Попробуйте с флагом --test для тестовых данных.');
                    return 1;
                }
            }

            if (empty($callsData)) {
                $this->warn('⚠️ Данные о звонках не найдены');
                return 0;
            }

            $this->info("📈 Найдено звонков: " . count($callsData));

            // Импортируем данные
            $this->importCalls($callsData);

            $this->info('✅ Импорт завершен успешно!');

        } catch (\Exception $e) {
            $this->error('❌ Ошибка при импорте: ' . $e->getMessage());
            Log::error('Import September Calls Error: ' . $e->getMessage(), [
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
