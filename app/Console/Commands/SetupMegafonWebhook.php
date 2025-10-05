<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class SetupMegafonWebhook extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'megafon:setup-webhook';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Настройка webhook для получения звонков от АТС Мегафона';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔧 Настройка webhook для АТС Мегафона');
        $this->newLine();

        $webhookUrl = config('app.url') . '/api/megafon';
        
        $this->info("📡 URL для webhook: {$webhookUrl}");
        $this->newLine();

        $this->info('📋 Инструкции по настройке:');
        $this->newLine();
        
        $this->line('1. Войдите в админку АТС Мегафона');
        $this->line('2. Найдите раздел "Интеграции" или "API"');
        $this->line('3. Добавьте новый webhook со следующими параметрами:');
        $this->newLine();
        
        $this->line("   URL: {$webhookUrl}");
        $this->line('   Метод: POST');
        $this->line('   Content-Type: application/json');
        $this->line('   События: Звонки (входящие и исходящие)');
        $this->newLine();
        
        $this->line('4. Формат данных, которые должен отправлять webhook:');
        $this->newLine();
        
        $this->line('   {');
        $this->line('     "cmd": "history",');
        $this->line('     "callid": "уникальный_id_звонка",');
        $this->line('     "start": "20250901T091500Z",');
        $this->line('     "type": "incoming|outgoing",');
        $this->line('     "status": "answered|missed|busy|cancel",');
        $this->line('     "phone": "+74951234567",');
        $this->line('     "user": "user_id",');
        $this->line('     "diversion": "+74959876543",');
        $this->line('     "duration": 180,');
        $this->line('     "wait": 5,');
        $this->line('     "link": "https://example.com/record.mp3"');
        $this->line('   }');
        $this->newLine();
        
        $this->info('✅ После настройки webhook звонки будут автоматически сохраняться в БД');
        $this->newLine();
        
        $this->info('🧪 Для тестирования webhook используйте:');
        $this->line('   php artisan megafon:test-api --send-test');
        $this->newLine();
        
        $this->info('📊 Для просмотра полученных звонков:');
        $this->line('   php artisan tinker --execute="echo App\\Models\\Call::count() . \' звонков в БД\';"');
    }
}
