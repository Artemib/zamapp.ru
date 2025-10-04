<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Order;
use App\Models\Call;

class OrderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Создаем несколько тестовых заказов
        $calls = Call::take(5)->get();
        
        if ($calls->count() > 0) {
            Order::create([
                'order_datetime' => now()->subDays(1),
                'city' => 'Москва',
                'address' => 'ул. Тверская, д. 1',
                'phone' => '+7 (495) 123-45-67',
                'additional_info' => 'Доставка до 18:00',
                'main_call_id' => $calls->first()->id,
            ]);

            Order::create([
                'order_datetime' => now()->subHours(5),
                'city' => 'Санкт-Петербург',
                'address' => 'Невский проспект, д. 28',
                'phone' => '+7 (812) 987-65-43',
                'additional_info' => 'Срочный заказ',
                'main_call_id' => $calls->skip(1)->first()?->id,
            ]);

            Order::create([
                'order_datetime' => now()->subHours(2),
                'city' => 'Екатеринбург',
                'address' => 'ул. Ленина, д. 15',
                'phone' => '+7 (343) 555-12-34',
                'additional_info' => 'Контактное лицо: Иван Петров',
                'main_call_id' => $calls->skip(2)->first()?->id,
            ]);
        }
    }
}