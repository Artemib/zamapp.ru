<?php

namespace App\Console\Commands;

use App\Models\Call;
use App\Enums\CallConstants;
use Illuminate\Console\Command;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Carbon\Carbon;

class ImportCallsFromExcel extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:calls {file_path}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Импорт звонков из Excel файла';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $filePath = $this->argument('file_path');
        
        if (!file_exists($filePath)) {
            $this->error("Файл не найден: {$filePath}");
            return 1;
        }

        $this->info("Начинаем импорт звонков из файла: {$filePath}");

        try {
            // Загружаем Excel файл
            $spreadsheet = IOFactory::load($filePath);
            $worksheet = $spreadsheet->getActiveSheet();
            $highestRow = $worksheet->getHighestRow();
            $highestColumn = $worksheet->getHighestColumn();

            $this->info("Найдено строк: {$highestRow}");
            $this->info("Колонки: A-{$highestColumn}");

            // Заголовки находятся в строке 11
            $headerRow = 11;
            $headers = [
                'A' => 'Тип звонка',
                'B' => 'Клиент', 
                'C' => 'Сотрудник',
                'D' => 'Должность',
                'E' => 'Через',
                'F' => 'Имя номера',
                'G' => 'Переадресация',
                'H' => 'Дата',
                'I' => 'Время',
                'J' => 'Ожидание',
                'K' => 'Длительность',
                'L' => 'Оценка',
                'M' => 'Примечание'
            ];

            $this->info("Заголовки: " . implode(', ', $headers));

            $imported = 0;
            $skipped = 0;
            $errors = 0;

            // Обрабатываем данные начиная со строки 12
            for ($row = 12; $row <= $highestRow; $row++) {
                try {
                    $rowData = [];
                    for ($col = 'A'; $col <= $highestColumn; ++$col) {
                        $rowData[$col] = $worksheet->getCell($col . $row)->getValue();
                    }

                    // Пропускаем пустые строки
                    if (empty(array_filter($rowData))) {
                        continue;
                    }

                    // Маппинг данных из Excel в поля модели Call
                    $callData = $this->mapExcelDataToCall($rowData, $headers, $row);

                    if ($callData) {
                        // Проверяем, существует ли уже звонок с таким callid
                        $existingCall = Call::where('callid', $callData['callid'])->first();
                        
                        if ($existingCall) {
                            $this->warn("Звонок с ID {$callData['callid']} уже существует, пропускаем");
                            $skipped++;
                            continue;
                        }

                        // Создаем новый звонок
                        Call::create($callData);
                        $imported++;
                        
                        if ($imported % 100 == 0) {
                            $this->info("Импортировано: {$imported} звонков");
                        }
                    } else {
                        $skipped++;
                    }

                } catch (\Exception $e) {
                    $this->error("Ошибка в строке {$row}: " . $e->getMessage());
                    $errors++;
                }
            }

            $this->info("Импорт завершен!");
            $this->info("Импортировано: {$imported}");
            $this->info("Пропущено: {$skipped}");
            $this->info("Ошибок: {$errors}");

        } catch (\Exception $e) {
            $this->error("Ошибка при чтении файла: " . $e->getMessage());
            return 1;
        }

        return 0;
    }

    /**
     * Маппинг данных из Excel в поля модели Call
     */
    private function mapExcelDataToCall(array $rowData, array $headers, int $rowNumber): ?array
    {
        try {
            // Извлекаем данные из строки
            $type = trim($rowData['A'] ?? ''); // Тип звонка
            $client = trim($rowData['B'] ?? ''); // Клиент (номер телефона)
            $employee = trim($rowData['C'] ?? ''); // Сотрудник
            $position = trim($rowData['D'] ?? ''); // Должность
            $through = trim($rowData['E'] ?? ''); // Через
            $numberName = trim($rowData['F'] ?? ''); // Имя номера
            $forwarding = trim($rowData['G'] ?? ''); // Переадресация
            $date = $rowData['H'] ?? null; // Дата (число Excel)
            $time = $rowData['I'] ?? null; // Время (дробное число Excel)
            $wait = $rowData['J'] ?? null; // Ожидание (дробное число Excel)
            $duration = $rowData['K'] ?? null; // Длительность (дробное число Excel)
            $rating = trim($rowData['L'] ?? ''); // Оценка
            $note = trim($rowData['M'] ?? ''); // Примечание

            // Проверяем обязательные поля
            if (empty($client) || empty($date) || empty($time)) {
                return null;
            }

            // Конвертируем дату из Excel формата
            $excelDate = $this->convertExcelDate($date, $time);
            if (!$excelDate) {
                return null;
            }

            // Генерируем уникальный ID звонка
            $callid = 'excel_' . md5($excelDate . $client . $employee . $rowNumber);

            // Маппинг типа звонка
            $callType = $this->mapCallType($type);

            // Маппинг статуса (по умолчанию успешный, так как звонки есть в отчете)
            $status = 'success';

            // Конвертируем длительность и ожидание из дней в секунды
            $durationSeconds = $this->convertExcelTimeToSeconds($duration);
            $waitSeconds = $this->convertExcelTimeToSeconds($wait);

            $callData = [
                'callid' => $callid,
                'datetime' => $excelDate,
                'type' => $callType,
                'status' => $status,
                'client_phone' => $client,
                'user_pbx' => $employee . ' (' . $position . ')',
                'diversion_phone' => $through,
                'duration' => $durationSeconds,
                'wait' => $waitSeconds,
                'link_record_pbx' => null,
                'from_source_name' => 'Excel Import - сентябрь 2025',
            ];

            return $callData;

        } catch (\Exception $e) {
            $this->error("Ошибка маппинга данных в строке {$rowNumber}: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Конвертация даты и времени из Excel формата
     */
    private function convertExcelDate($date, $time): ?string
    {
        try {
            // Excel хранит даты как количество дней с 1900-01-01
            // Время хранится как дробная часть дня (0.5 = 12:00)
            
            if (is_numeric($date) && is_numeric($time)) {
                // Excel epoch начинается с 1900-01-01, но есть ошибка в 1900 (високосный год)
                $excelEpoch = Carbon::create(1900, 1, 1);
                
                // Добавляем дни (минус 2 из-за ошибки Excel с 1900 годом)
                $dateOnly = $excelEpoch->addDays($date - 2);
                
                // Добавляем время (время в Excel - это дробная часть дня)
                $hours = floor($time * 24);
                $minutes = floor(($time * 24 - $hours) * 60);
                $seconds = floor((($time * 24 - $hours) * 60 - $minutes) * 60);
                
                $dateTime = $dateOnly->setTime($hours, $minutes, $seconds);
                
                // Устанавливаем московский часовой пояс
                $dateTime->setTimezone('Europe/Moscow');
                
                return $dateTime->toDateTimeString();
            }
            
            return null;
            
        } catch (\Exception $e) {
            $this->warn("Не удалось конвертировать дату: {$date}, время: {$time}");
            return null;
        }
    }

    /**
     * Конвертация времени из Excel формата в секунды
     */
    private function convertExcelTimeToSeconds($excelTime): int
    {
        if (!is_numeric($excelTime)) {
            return 0;
        }
        
        // Excel время - это дробная часть дня
        // 1 день = 86400 секунд
        return (int) round($excelTime * 86400);
    }

    /**
     * Маппинг типа звонка
     */
    private function mapCallType($typeString): string
    {
        if (empty($typeString)) {
            return 'in'; // По умолчанию входящий
        }

        $typeString = strtolower(trim($typeString));

        if (strpos($typeString, 'исходящ') !== false || strpos($typeString, 'out') !== false) {
            return 'out';
        }

        return 'in'; // По умолчанию входящий
    }
}