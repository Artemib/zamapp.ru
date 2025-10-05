<?php

namespace App\Console\Commands;

use App\Services\PbxApiService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class TestMegafonApi extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'megafon:test-api 
                            {--send-test : Отправить тестовый звонок в API}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Тестирование API Мегафона';

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
        $this->info('🔍 Тестируем API Мегафона...');

        $baseUrl = config('pbx.api.base_url', 'https://7280019.megapbx.ru/crmapi/v1');
        $token = config('pbx.api.token', '5bb722db-43b2-4a8a-a0d5-29f320ae8d0a');

        $this->info("📡 URL: {$baseUrl}");
        $this->info("🔑 Token: {$token}");

        // Тест 1: Проверка доступности
        $this->info("\n1️⃣ Проверяем доступность API...");
        $this->testApiAvailability($baseUrl);

        // Тест 2: Различные варианты авторизации
        $this->info("\n2️⃣ Тестируем варианты авторизации...");
        $this->testAuthorizationMethods($baseUrl, $token);

        // Тест 3: Отправка тестового звонка
        if ($this->option('send-test')) {
            $this->info("\n3️⃣ Отправляем тестовый звонок...");
            $this->sendTestCall();
        }

        $this->info("\n✅ Тестирование завершено!");
    }

    /**
     * Проверка доступности API
     */
    private function testApiAvailability(string $baseUrl): void
    {
        try {
            $response = Http::timeout(10)->get($baseUrl);
            
            $this->info("   Статус: {$response->status()}");
            $this->info("   Ответ: " . substr($response->body(), 0, 100) . "...");
            
            if ($response->status() === 301 || $response->status() === 302) {
                $this->warn("   ⚠️  Получен редирект: " . $response->header('Location'));
            }
            
        } catch (\Throwable $e) {
            $this->error("   ❌ Ошибка: " . $e->getMessage());
        }
    }

    /**
     * Тестирование различных методов авторизации
     */
    private function testAuthorizationMethods(string $baseUrl, string $token): void
    {
        $methods = [
            'Bearer Token' => ['Authorization' => "Bearer {$token}"],
            'Token' => ['Authorization' => "Token {$token}"],
            'X-API-Key' => ['X-API-Key' => $token],
            'X-API-Token' => ['X-API-Token' => $token],
            'X-Auth-Token' => ['X-Auth-Token' => $token],
        ];

        foreach ($methods as $name => $headers) {
            $this->info("   Тестируем: {$name}");
            
            try {
                $response = Http::withHeaders($headers)
                    ->timeout(10)
                    ->get($baseUrl . '/calls');
                
                $this->info("     Статус: {$response->status()}");
                $this->info("     Ответ: " . substr($response->body(), 0, 50) . "...");
                
                if ($response->successful()) {
                    $this->info("     ✅ Успешно!");
                    return;
                }
                
            } catch (\Throwable $e) {
                $this->error("     ❌ Ошибка: " . $e->getMessage());
            }
        }
    }

    /**
     * Отправка тестового звонка
     */
    private function sendTestCall(): void
    {
        $testCall = [
            'cmd' => 'history',
            'callid' => 'test_' . time(),
            'start' => '20250901T091500Z',
            'type' => 'incoming',
            'status' => 'answered',
            'phone' => '+74951234567',
            'user' => 'user_001',
            'diversion' => '+74959876543',
            'duration' => 180,
            'wait' => 5,
            'link' => 'https://example.com/record.mp3'
        ];

        $this->info("   Отправляем звонок: " . $testCall['callid']);
        
        $success = $this->pbxService->sendCallToMegafon($testCall);
        
        if ($success) {
            $this->info("   ✅ Звонок успешно отправлен!");
        } else {
            $this->error("   ❌ Ошибка при отправке звонка");
        }
    }
}
