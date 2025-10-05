<?php

namespace Database\Seeders;

use App\Models\Call;
use App\Models\Contact;
use App\Models\Order;
use Illuminate\Database\Seeder;

class TestDataSeeder extends Seeder
{
    public function run(): void
    {
        // Создаем тестовые контакты
        $contact1 = Contact::firstOrCreate(
            ['type' => 'phone', 'value' => '+7 (999) 123-45-67'],
            [
                'source' => 'auto',
                'label' => 'Основной телефон',
            ]
        );

        $contact2 = Contact::firstOrCreate(
            ['type' => 'telegram', 'value' => '@testuser'],
            [
                'source' => 'manual',
                'label' => 'Telegram',
            ]
        );

        $contact3 = Contact::firstOrCreate(
            ['type' => 'phone', 'value' => '+7 (999) 765-43-21'],
            [
                'source' => 'auto',
                'label' => 'Дополнительный телефон',
            ]
        );

        // Создаем тестовые звонки
        $call1 = Call::firstOrCreate(
            ['callid' => 'CALL001'],
            [
                'datetime' => now()->subHours(2),
                'type' => 'in',
                'status' => 'success',
                'client_phone' => '+7 (999) 123-45-67',
                'user_pbx' => 'user1',
                'diversion_phone' => '+7 (495) 123-45-67',
                'duration' => 180,
                'wait' => 5,
                'link_record_pbx' => 'https://example.com/record1.wav',
                'from_source_name' => 'Реклама Google',
            ]
        );

        $call2 = Call::firstOrCreate(
            ['callid' => 'CALL002'],
            [
                'datetime' => now()->subHours(1),
                'type' => 'out',
                'status' => 'missed',
                'client_phone' => '+7 (999) 765-43-21',
                'user_pbx' => 'user2',
                'diversion_phone' => '+7 (495) 123-45-67',
                'duration' => 0,
                'wait' => 30,
                'from_source_name' => 'Обратный звонок',
            ]
        );

        $call3 = Call::firstOrCreate(
            ['callid' => 'CALL003'],
            [
                'datetime' => now()->subMinutes(30),
                'type' => 'in',
                'status' => 'success',
                'client_phone' => '+7 (999) 123-45-67',
                'user_pbx' => 'user1',
                'diversion_phone' => '+7 (495) 123-45-67',
                'duration' => 300,
                'wait' => 2,
                'transcribation' => 'Клиент интересовался услугами компании. Обсудили условия сотрудничества.',
                'from_source_name' => 'Реклама Яндекс',
            ]
        );

        // Создаем тестовые заказы
        $order1 = Order::create([
            'main_call_id' => $call1->id,
            'status' => 'work',
        ]);

        $order2 = Order::create([
            'main_call_id' => $call3->id,
            'status' => 'completed',
        ]);

        // Связываем заказы с контактами
        $order1->contacts()->attach($contact1->id, [
            'is_primary' => true,
            'comment' => 'Основной контакт клиента',
        ]);

        $order1->contacts()->attach($contact2->id, [
            'is_primary' => false,
            'comment' => 'Дополнительный контакт',
        ]);

        $order2->contacts()->attach($contact1->id, [
            'is_primary' => true,
            'comment' => 'Основной контакт',
        ]);

        // Связываем заказы со звонками
        $order1->calls()->attach($call1->id, [
            'relation_type' => 'main',
        ]);

        $order1->calls()->attach($call2->id, [
            'relation_type' => 'extra',
        ]);

        $order2->calls()->attach($call3->id, [
            'relation_type' => 'main',
        ]);

        $this->command->info('Тестовые данные созданы успешно!');
    }
}
