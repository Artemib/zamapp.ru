<?php

namespace App\Console\Commands;

use App\Models\Call;
use App\Models\Order;
use App\Models\Contact;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ClearCallsData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'calls:clear|cc {--force : Принудительная очистка без подтверждения}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Очистить все данные о звонках, заказах и контактах из базы данных';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        if (!$this->option('force')) {
            if (!$this->confirm('Вы уверены, что хотите удалить ВСЕ данные о звонках, заказах и контактах? Это действие необратимо!')) {
                $this->info('Операция отменена.');
                return;
            }
        }

        $this->info('Начинаем очистку данных...');

        try {
            // Отключаем проверку внешних ключей
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');

            // Очищаем связанные таблицы
            $this->info('Очищаем связанные таблицы...');
            DB::table('call_order')->truncate();
            DB::table('contact_order')->truncate();

            // Очищаем основные таблицы
            $this->info('Очищаем таблицу заказов...');
            Order::truncate();

            $this->info('Очищаем таблицу контактов...');
            Contact::truncate();

            $this->info('Очищаем таблицу звонков...');
            Call::truncate();

            // Включаем обратно проверку внешних ключей
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');

            $this->info('✅ Все данные успешно очищены!');

        } catch (\Exception $e) {
            $this->error('❌ Ошибка при очистке данных: ' . $e->getMessage());
            return 1;
        }

        return 0;
    }
}
