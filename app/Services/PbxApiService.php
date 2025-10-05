<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class PbxApiService
{
    private string $baseUrl;
    private string $token;

    public function __construct()
    {
        $this->baseUrl = config('pbx.api.base_url', 'https://7280019.megapbx.ru/crmapi/v1');
        $this->token = config('pbx.api.token', '98daf46c-1850-42ef-a40a-db7f29ff08b0');
    }

    /**
     * Получить звонки за указанный период
     *
     * @param Carbon $dateFrom
     * @param Carbon $dateTo
     * @return array|null
     */
    public function getCalls(Carbon $dateFrom, Carbon $dateTo): ?array
    {
        try {
            // Конвертируем московское время в UTC для API
            $dateFromUtc = $dateFrom->utc();
            $dateToUtc = $dateTo->utc();
            
            Log::info('PBX API: Запрос звонков', [
                'date_from_msk' => $dateFrom->format('Y-m-d H:i:s'),
                'date_to_msk' => $dateTo->format('Y-m-d H:i:s'),
                'date_from_utc' => $dateFromUtc->format('Y-m-d H:i:s'),
                'date_to_utc' => $dateToUtc->format('Y-m-d H:i:s'),
            ]);

            $response = Http::withHeaders([
                'X-API-KEY' => $this->token,
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ])
            ->timeout(60)
            ->retry(3, 1000)
            ->get($this->baseUrl . '/history/json', [
                'start' => $dateFromUtc->format('Ymd\THis\Z'),
                'end' => $dateToUtc->format('Ymd\THis\Z'),
            ]);

            if (!$response->successful()) {
                Log::error('PBX API Error: ' . $response->status(), [
                    'url' => $this->baseUrl . '/history/json',
                    'response' => $response->body(),
                    'date_from_utc' => $dateFromUtc->format('Y-m-d H:i:s'),
                    'date_to_utc' => $dateToUtc->format('Y-m-d H:i:s'),
                ]);
                
                // Если API не работает, возвращаем null
                Log::error('PBX API: API недоступен, возвращаем null');
                return null;
            }

            $data = $response->json();
            
            // API Мегафон возвращает массив напрямую, а не в поле 'data'
            $callsData = is_array($data) ? $data : ($data['data'] ?? []);
            
            Log::info('PBX API Success', [
                'calls_count' => count($callsData),
                'date_from_utc' => $dateFromUtc->format('Y-m-d H:i:s'),
                'date_to_utc' => $dateToUtc->format('Y-m-d H:i:s'),
            ]);

            return $callsData;

        } catch (\Throwable $e) {
            Log::error('PBX API Exception: ' . $e->getMessage(), [
                'date_from' => $dateFrom->format('Y-m-d H:i:s'),
                'date_to' => $dateTo->format('Y-m-d H:i:s'),
                'trace' => $e->getTraceAsString(),
            ]);
            
            // Если API не работает, возвращаем null
            Log::error('PBX API: Ошибка API, возвращаем null');
            return null;
        }
    }

    /**
     * Отправить звонок в API Мегафона (для получения данных)
     *
     * @param array $callData
     * @return bool
     */
    public function sendCallToMegafon(array $callData): bool
    {
        try {
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ])
            ->timeout(30)
            ->retry(2, 1000)
            ->post($this->baseUrl, $callData);

            if ($response->successful()) {
                Log::info('Megafon API: Звонок успешно отправлен', [
                    'callid' => $callData['callid'] ?? 'unknown',
                    'response' => $response->json(),
                ]);
                return true;
            } else {
                Log::error('Megafon API Error: ' . $response->status(), [
                    'callid' => $callData['callid'] ?? 'unknown',
                    'response' => $response->body(),
                ]);
                return false;
            }

        } catch (\Throwable $e) {
            Log::error('Megafon API Exception: ' . $e->getMessage(), [
                'callid' => $callData['callid'] ?? 'unknown',
                'trace' => $e->getTraceAsString(),
            ]);
            return false;
        }
    }

    /**
     * Получить звонки за сентябрь 2025 года
     *
     * @return array|null
     */
    public function getCallsForSeptember(): ?array
    {
        $dateFrom = Carbon::create(2025, 9, 1, 0, 0, 0);
        $dateTo = Carbon::create(2025, 9, 30, 23, 59, 59);

        return $this->getCalls($dateFrom, $dateTo);
    }

    /**
     * Получить тестовые данные звонков (для демонстрации)
     * Симулирует реальные данные за сентябрь 2025 (2410 звонков)
     *
     * @return array
     */
    public function getTestCalls(): array
    {
        $calls = [];
        
        // Генерируем 2410 звонков за сентябрь 2025
        $startDate = Carbon::create(2025, 9, 1, 0, 0, 0, 'Europe/Moscow');
        $endDate = Carbon::create(2025, 9, 30, 23, 59, 59, 'Europe/Moscow');
        
        $phoneNumbers = [
            '+7 (495) 123-45-67', '+7 (495) 234-56-78', '+7 (495) 345-67-89',
            '+7 (495) 456-78-90', '+7 (495) 567-89-01', '+7 (495) 678-90-12',
            '+7 (495) 789-01-23', '+7 (495) 890-12-34', '+7 (495) 901-23-45',
            '+7 (495) 012-34-56', '+7 (495) 123-45-78', '+7 (495) 234-56-89',
            '+7 (495) 345-67-90', '+7 (495) 456-78-01', '+7 (495) 567-89-12',
            '+7 (495) 678-90-23', '+7 (495) 789-01-34', '+7 (495) 890-12-45',
            '+7 (495) 901-23-56', '+7 (495) 012-34-67', '+7 (495) 123-45-89',
            '+7 (495) 234-56-90', '+7 (495) 345-67-01', '+7 (495) 456-78-12',
            '+7 (495) 567-89-23', '+7 (495) 678-90-34', '+7 (495) 789-01-45',
            '+7 (495) 890-12-56', '+7 (495) 901-23-67', '+7 (495) 012-34-78',
        ];
        
        $users = ['user_001', 'user_002', 'user_003', 'user_004', 'user_005'];
        $types = ['incoming', 'outgoing'];
        $statuses = ['answered', 'missed', 'busy', 'cancel'];
        $diversionPhones = ['+7 (495) 987-65-43', '+7 (495) 987-65-44', '+7 (495) 987-65-45'];
        
        // Генерируем звонки равномерно по всему месяцу
        for ($i = 0; $i < 2410; $i++) {
            // Равномерно распределяем звонки по времени
            $progress = $i / 2409; // 0 до 1
            $randomMinutes = rand(0, 59);
            $randomSeconds = rand(0, 59);
            
            $callTime = $startDate->copy()->addDays($progress * 29)->addMinutes($randomMinutes)->addSeconds($randomSeconds);
            
            // Конвертируем в UTC для API
            $callTimeUtc = $callTime->utc();
            
            $type = $types[array_rand($types)];
            $status = $statuses[array_rand($statuses)];
            
            // Для входящих звонков чаще "answered", для исходящих - "answered" или "busy"
            if ($type === 'incoming') {
                $status = rand(1, 10) <= 7 ? 'answered' : ($status === 'answered' ? 'missed' : $status);
            } else {
                $status = rand(1, 10) <= 8 ? 'answered' : ($status === 'answered' ? 'busy' : $status);
            }
            
            $duration = $status === 'answered' ? rand(10, 1800) : 0;
            $wait = $type === 'incoming' && $status === 'answered' ? rand(1, 30) : 0;
            
            $calls[] = [
                'callid' => 'megafon_api_' . $callTimeUtc->format('YmdHis') . '_' . $i,
                'datetime' => $callTimeUtc->format('Y-m-d H:i:s'),
                'type' => $type,
                'status' => $status,
                'client_phone' => $phoneNumbers[array_rand($phoneNumbers)],
                'user_pbx' => $users[array_rand($users)],
                'diversion_phone' => $diversionPhones[array_rand($diversionPhones)],
                'duration' => $duration,
                'wait' => $wait,
                'link_record_pbx' => $status === 'answered' && $duration > 30 ? 'https://example.com/record_' . $i . '.mp3' : null,
                'transcribation' => $status === 'answered' && $duration > 60 ? 'Разговор с клиентом' : null,
                'from_source_name' => 'Megafon API UTC',
            ];
        }
        
        return $calls;
    }

    /**
     * Получить информацию о пользователях АТС
     *
     * @return array|null
     */
    public function getUsers(): ?array
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->token,
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ])
            ->timeout(30)
            ->retry(2, 1000)
            ->get($this->baseUrl . '/users');

            if (!$response->successful()) {
                Log::error('PBX Users API Error: ' . $response->status(), [
                    'url' => $this->baseUrl . '/users',
                    'response' => $response->body(),
                ]);
                return null;
            }

            return $response->json();

        } catch (\Throwable $e) {
            Log::error('PBX Users API Exception: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);
            return null;
        }
    }

    /**
     * Проверить соединение с API
     *
     * @return bool
     */
    public function testConnection(): bool
    {
        try {
            $dateFrom = Carbon::parse('2025-09-01 00:00:00')->utc();
            $dateTo = Carbon::parse('2025-09-01 00:01:00')->utc();
            
            $response = Http::withHeaders([
                'X-API-KEY' => $this->token,
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ])
            ->timeout(10)
            ->get($this->baseUrl . '/history/json', [
                'start' => $dateFrom->format('Ymd\THis\Z'),
                'end' => $dateTo->format('Ymd\THis\Z'),
            ]);

            return $response->successful();

        } catch (\Throwable $e) {
            Log::error('PBX API Connection Test Failed: ' . $e->getMessage());
            return false;
        }
    }
}
