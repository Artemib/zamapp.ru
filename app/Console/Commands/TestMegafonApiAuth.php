<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TestMegafonApiAuth extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'megafon:test-auth|mtest';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Тестировать различные способы авторизации с API Мегафон';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $token = '98daf46c-1850-42ef-a40a-db7f29ff08b0';
        $baseUrl = 'https://7280019.megapbx.ru/crmapi/v1';

        $this->info('🔍 Тестируем различные способы авторизации с API Мегафон...');
        $this->newLine();

        $authMethods = [
            ['Authorization', 'Bearer ' . $token, 'Bearer Token'],
            ['X-API-KEY', $token, 'X-API-KEY Header'],
            ['API-Key', $token, 'API-Key Header'],
            ['Token', $token, 'Token Header'],
            ['X-Auth-Token', $token, 'X-Auth-Token Header'],
            ['Auth-Token', $token, 'Auth-Token Header'],
        ];

        foreach ($authMethods as $method) {
            $this->info("🧪 Тестируем: {$method[2]}");
            
            try {
                $response = Http::withHeaders([
                    $method[0] => $method[1],
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                ])
                ->timeout(10)
                ->get($baseUrl . '/status');

                $this->line("   Статус: {$response->status()}");
                $this->line("   Ответ: " . $response->body());
                
                if ($response->successful()) {
                    $this->info("   ✅ Успешно!");
                } else {
                    $this->error("   ❌ Ошибка");
                }

            } catch (\Exception $e) {
                $this->error("   ❌ Исключение: " . $e->getMessage());
            }

            $this->newLine();
        }

        // Тестируем получение истории
        $this->info('📊 Тестируем получение истории звонков...');
        
        foreach ($authMethods as $method) {
            $this->info("🧪 Тестируем получение истории с: {$method[2]}");
            
            try {
                $response = Http::withHeaders([
                    $method[0] => $method[1],
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                ])
                ->timeout(30)
                ->get($baseUrl . '/history', [
                    'date_from' => '2025-09-01 00:00:00',
                    'date_to' => '2025-09-01 23:59:59',
                ]);

                $this->line("   Статус: {$response->status()}");
                $this->line("   Ответ: " . substr($response->body(), 0, 200) . (strlen($response->body()) > 200 ? '...' : ''));
                
                if ($response->successful()) {
                    $this->info("   ✅ Успешно!");
                    $data = $response->json();
                    if (isset($data['data'])) {
                        $this->line("   📈 Найдено звонков: " . count($data['data']));
                    }
                    break; // Если нашли рабочий способ, прекращаем тестирование
                } else {
                    $this->error("   ❌ Ошибка");
                }

            } catch (\Exception $e) {
                $this->error("   ❌ Исключение: " . $e->getMessage());
            }

            $this->newLine();
        }

        return 0;
    }
}
