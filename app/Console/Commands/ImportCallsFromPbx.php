<?php

namespace App\Console\Commands;

use App\Models\Call;
use App\Services\PbxApiService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ImportCallsFromPbx extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'pbx:import-calls 
                            {--month= : Месяц для импорта (формат: YYYY-MM)}
                            {--force : Принудительно перезаписать существующие звонки}
                            {--test : Тестовый режим - только проверить соединение}
                            {--demo : Демо режим - использовать тестовые данные}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Импорт звонков из АТС в базу данных';

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
        $this->info('🚀 Начинаем импорт звонков из АТС...');

        // Тестовый режим
        if ($this->option('test')) {
            return $this->testConnection();
        }

        // Демо режим
        if ($this->option('demo')) {
            return $this->importDemoData();
        }

        // Определяем период для импорта
        $month = $this->option('month');
        if ($month) {
            try {
                $dateFrom = Carbon::createFromFormat('Y-m', $month)->startOfMonth();
                $dateTo = Carbon::createFromFormat('Y-m', $month)->endOfMonth();
            } catch (\Exception $e) {
                $this->error('❌ Неверный формат месяца. Используйте YYYY-MM (например: 2025-09)');
                return 1;
            }
        } else {
            // По умолчанию - сентябрь 2025
            $dateFrom = Carbon::create(2025, 9, 1, 0, 0, 0);
            $dateTo = Carbon::create(2025, 9, 30, 23, 59, 59);
        }

        $this->info("📅 Период импорта: {$dateFrom->format('d.m.Y')} - {$dateTo->format('d.m.Y')}");

        // Проверяем соединение
        if (!$this->testConnection()) {
            return 1;
        }

        // Получаем звонки из API
        $this->info('📞 Получаем звонки из АТС...');
        $calls = $this->pbxService->getCalls($dateFrom, $dateTo);

        if ($calls === null) {
            $this->error('❌ Не удалось получить данные из АТС');
            return 1;
        }

        if (empty($calls)) {
            $this->warn('⚠️  Звонки за указанный период не найдены');
            return 0;
        }

        $this->info("📊 Найдено звонков: " . count($calls));

        // Импортируем звонки
        $imported = $this->importCalls($calls, $this->option('force'));

        $this->info("✅ Импорт завершен. Обработано: {$imported['processed']}, Добавлено: {$imported['created']}, Обновлено: {$imported['updated']}, Пропущено: {$imported['skipped']}");

        return 0;
    }

    /**
     * Тестирование соединения с API
     */
    private function testConnection(): int
    {
        $this->info('🔍 Проверяем соединение с АТС...');

        if ($this->pbxService->testConnection()) {
            $this->info('✅ Соединение с АТС установлено');
            return 0;
        } else {
            $this->error('❌ Не удалось подключиться к АТС');
            return 1;
        }
    }

    /**
     * Импорт демо-данных
     */
    private function importDemoData(): int
    {
        $this->info('🎭 Демо режим: импортируем тестовые данные...');

        $calls = $this->pbxService->getTestCalls();
        $this->info("📊 Тестовых звонков: " . count($calls));

        $imported = $this->importCalls($calls, $this->option('force'));

        $this->info("✅ Демо импорт завершен. Обработано: {$imported['processed']}, Добавлено: {$imported['created']}, Обновлено: {$imported['updated']}, Пропущено: {$imported['skipped']}");

        return 0;
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

        DB::transaction(function () use ($calls, $force, &$stats, $progressBar) {
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
                    $callId = $callData['callid'] ?? 'unknown';
                    $this->error("\n❌ Ошибка при обработке звонка {$callId}: " . $e->getMessage());
                }

                $progressBar->advance();
            }
        });

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
            'callid' => $callData['callid'] ?? '',
            'datetime' => $this->parseDateTime($callData['datetime'] ?? null),
            'type' => $this->mapCallType($callData['type'] ?? null),
            'status' => $this->mapCallStatus($callData['status'] ?? null),
            'client_phone' => $callData['client_phone'] ?? '',
            'user_pbx' => $callData['user_pbx'] ?? '',
            'diversion_phone' => $callData['diversion_phone'] ?? '',
            'duration' => (int)($callData['duration'] ?? 0),
            'wait' => (int)($callData['wait'] ?? 0),
            'link_record_pbx' => $callData['link_record_pbx'] ?? null,
            'link_record_crm' => $callData['link_record_crm'] ?? null,
            'transcribation' => $callData['transcribation'] ?? null,
            'from_source_name' => $callData['from_source_name'] ?? 'PBX API',
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
