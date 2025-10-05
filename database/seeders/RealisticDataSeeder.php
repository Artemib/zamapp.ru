<?php

namespace Database\Seeders;

use App\Models\Call;
use App\Models\Contact;
use App\Models\Order;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class RealisticDataSeeder extends Seeder
{
    public function run(): void
    {
        // Очищаем существующие данные (с учетом внешних ключей)
        \DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        \DB::table('call_order')->truncate();
        \DB::table('contact_order')->truncate();
        Order::truncate();
        Call::truncate();
        Contact::truncate();
        \DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // Создаем контакты
        $contacts = [
            [
                'type' => 'phone',
                'value' => '+7 (495) 123-45-67',
                'source' => 'auto',
                'label' => 'Основной телефон',
            ],
            [
                'type' => 'phone',
                'value' => '+7 (916) 234-56-78',
                'source' => 'auto',
                'label' => 'Мобильный телефон',
            ],
            [
                'type' => 'email',
                'value' => 'ivan.petrov@example.com',
                'source' => 'manual',
                'label' => 'Рабочий email',
            ],
            [
                'type' => 'telegram',
                'value' => '@ivan_petrov',
                'source' => 'manual',
                'label' => 'Telegram',
            ],
            [
                'type' => 'phone',
                'value' => '+7 (495) 987-65-43',
                'source' => 'auto',
                'label' => 'Офисный телефон',
            ],
            [
                'type' => 'whatsapp',
                'value' => '+7 (916) 345-67-89',
                'source' => 'manual',
                'label' => 'WhatsApp',
            ],
            [
                'type' => 'email',
                'value' => 'maria.sidorova@company.ru',
                'source' => 'manual',
                'label' => 'Корпоративный email',
            ],
            [
                'type' => 'phone',
                'value' => '+7 (495) 555-12-34',
                'source' => 'auto',
                'label' => 'Главный офис',
            ],
            [
                'type' => 'telegram',
                'value' => '@maria_sidorova',
                'source' => 'manual',
                'label' => 'Telegram менеджера',
            ],
            [
                'type' => 'phone',
                'value' => '+7 (916) 456-78-90',
                'source' => 'auto',
                'label' => 'Личный телефон',
            ],
        ];

        $createdContacts = [];
        foreach ($contacts as $contactData) {
            $createdContacts[] = Contact::create($contactData);
        }

        // Создаем звонки
        $calls = [
            [
                'callid' => 'CALL_' . str_pad(1, 6, '0', STR_PAD_LEFT),
                'datetime' => Carbon::now()->subDays(5)->setTime(10, 30),
                'type' => 'in',
                'status' => 'success',
                'client_phone' => '+7 (495) 123-45-67',
                'user_pbx' => 'operator1',
                'diversion_phone' => '+7 (495) 111-11-11',
                'duration' => 245,
                'wait' => 8,
                'link_record_pbx' => 'https://records.example.com/call_001.wav',
                'transcribation' => 'Клиент интересовался услугами веб-разработки. Обсудили техническое задание и сроки реализации проекта.',
                'from_source_name' => 'Реклама Google',
            ],
            [
                'callid' => 'CALL_' . str_pad(2, 6, '0', STR_PAD_LEFT),
                'datetime' => Carbon::now()->subDays(4)->setTime(14, 15),
                'type' => 'out',
                'status' => 'success',
                'client_phone' => '+7 (916) 234-56-78',
                'user_pbx' => 'manager1',
                'diversion_phone' => '+7 (495) 111-11-11',
                'duration' => 180,
                'wait' => 0,
                'link_record_pbx' => 'https://records.example.com/call_002.wav',
                'transcribation' => 'Обратный звонок клиенту. Подтвердили встречу на завтра в 15:00.',
                'from_source_name' => 'Обратный звонок',
            ],
            [
                'callid' => 'CALL_' . str_pad(3, 6, '0', STR_PAD_LEFT),
                'datetime' => Carbon::now()->subDays(3)->setTime(16, 45),
                'type' => 'in',
                'status' => 'missed',
                'client_phone' => '+7 (495) 987-65-43',
                'user_pbx' => 'operator2',
                'diversion_phone' => '+7 (495) 111-11-11',
                'duration' => 0,
                'wait' => 25,
                'from_source_name' => 'Реклама Яндекс',
            ],
            [
                'callid' => 'CALL_' . str_pad(4, 6, '0', STR_PAD_LEFT),
                'datetime' => Carbon::now()->subDays(2)->setTime(11, 20),
                'type' => 'in',
                'status' => 'success',
                'client_phone' => '+7 (495) 555-12-34',
                'user_pbx' => 'operator1',
                'diversion_phone' => '+7 (495) 111-11-11',
                'duration' => 420,
                'wait' => 3,
                'link_record_pbx' => 'https://records.example.com/call_004.wav',
                'transcribation' => 'Детальное обсуждение проекта мобильного приложения. Клиент готов к подписанию договора.',
                'from_source_name' => 'Прямой звонок',
            ],
            [
                'callid' => 'CALL_' . str_pad(5, 6, '0', STR_PAD_LEFT),
                'datetime' => Carbon::now()->subDays(1)->setTime(9, 10),
                'type' => 'out',
                'status' => 'busy',
                'client_phone' => '+7 (916) 345-67-89',
                'user_pbx' => 'manager2',
                'diversion_phone' => '+7 (495) 111-11-11',
                'duration' => 0,
                'wait' => 0,
                'from_source_name' => 'Плановый звонок',
            ],
            [
                'callid' => 'CALL_' . str_pad(6, 6, '0', STR_PAD_LEFT),
                'datetime' => Carbon::now()->subHours(6)->setTime(13, 30),
                'type' => 'in',
                'status' => 'success',
                'client_phone' => '+7 (916) 456-78-90',
                'user_pbx' => 'operator2',
                'diversion_phone' => '+7 (495) 111-11-11',
                'duration' => 195,
                'wait' => 12,
                'link_record_pbx' => 'https://records.example.com/call_006.wav',
                'transcribation' => 'Консультация по интеграции с внешними сервисами. Клиент просит подготовить коммерческое предложение.',
                'from_source_name' => 'Рекомендация',
            ],
            [
                'callid' => 'CALL_' . str_pad(7, 6, '0', STR_PAD_LEFT),
                'datetime' => Carbon::now()->subHours(3)->setTime(15, 45),
                'type' => 'out',
                'status' => 'success',
                'client_phone' => '+7 (495) 123-45-67',
                'user_pbx' => 'manager1',
                'diversion_phone' => '+7 (495) 111-11-11',
                'duration' => 320,
                'wait' => 0,
                'link_record_pbx' => 'https://records.example.com/call_007.wav',
                'transcribation' => 'Обсуждение деталей проекта и подписание договора. Клиент согласен на все условия.',
                'from_source_name' => 'Договор',
            ],
            [
                'callid' => 'CALL_' . str_pad(8, 6, '0', STR_PAD_LEFT),
                'datetime' => Carbon::now()->subHours(1)->setTime(17, 20),
                'type' => 'in',
                'status' => 'cancel',
                'client_phone' => '+7 (495) 987-65-43',
                'user_pbx' => 'operator1',
                'diversion_phone' => '+7 (495) 111-11-11',
                'duration' => 0,
                'wait' => 5,
                'from_source_name' => 'Повторный звонок',
            ],
            [
                'callid' => 'CALL_' . str_pad(9, 6, '0', STR_PAD_LEFT),
                'datetime' => Carbon::now()->subMinutes(30)->setTime(18, 0),
                'type' => 'in',
                'status' => 'success',
                'client_phone' => '+7 (916) 234-56-78',
                'user_pbx' => 'operator2',
                'diversion_phone' => '+7 (495) 111-11-11',
                'duration' => 150,
                'wait' => 7,
                'link_record_pbx' => 'https://records.example.com/call_009.wav',
                'transcribation' => 'Уточнение технических требований к проекту. Клиент просит добавить новые функции.',
                'from_source_name' => 'Техподдержка',
            ],
            [
                'callid' => 'CALL_' . str_pad(10, 6, '0', STR_PAD_LEFT),
                'datetime' => Carbon::now()->subMinutes(10)->setTime(18, 30),
                'type' => 'out',
                'status' => 'not_available',
                'client_phone' => '+7 (495) 555-12-34',
                'user_pbx' => 'manager2',
                'diversion_phone' => '+7 (495) 111-11-11',
                'duration' => 0,
                'wait' => 0,
                'from_source_name' => 'Напоминание',
            ],
        ];

        $createdCalls = [];
        foreach ($calls as $callData) {
            $createdCalls[] = Call::create($callData);
        }

        // Создаем заказы
        $orders = [
            [
                'main_call_id' => $createdCalls[0]->id, // CALL_000001
                'status' => 'work',
            ],
            [
                'main_call_id' => $createdCalls[1]->id, // CALL_000002
                'status' => 'completed',
            ],
            [
                'main_call_id' => $createdCalls[3]->id, // CALL_000004
                'status' => 'work',
            ],
            [
                'main_call_id' => $createdCalls[5]->id, // CALL_000006
                'status' => 'pending',
            ],
            [
                'main_call_id' => $createdCalls[6]->id, // CALL_000007
                'status' => 'work',
            ],
            [
                'main_call_id' => $createdCalls[8]->id, // CALL_000009
                'status' => 'work',
            ],
        ];

        $createdOrders = [];
        foreach ($orders as $orderData) {
            $createdOrders[] = Order::create($orderData);
        }

        // Связываем заказы с контактами
        $orderContactRelations = [
            // Заказ 1 - веб-разработка
            [0, 0, true, 'Основной контакт клиента'],
            [0, 2, false, 'Email для документооборота'],
            [0, 3, false, 'Telegram для быстрой связи'],
            
            // Заказ 2 - завершенный проект
            [1, 1, true, 'Мобильный телефон клиента'],
            [1, 2, false, 'Email для отчетов'],
            
            // Заказ 3 - мобильное приложение
            [2, 3, true, 'Основной контакт'],
            [2, 6, false, 'Корпоративный email'],
            [2, 8, false, 'Telegram менеджера'],
            
            // Заказ 4 - интеграция
            [3, 4, true, 'Офисный телефон'],
            [3, 5, false, 'WhatsApp для консультаций'],
            
            // Заказ 5 - договор
            [4, 0, true, 'Основной телефон'],
            [4, 2, false, 'Email для договоров'],
            
            // Заказ 6 - техподдержка
            [5, 1, true, 'Мобильный телефон'],
            [5, 2, false, 'Email для технических вопросов'],
        ];

        foreach ($orderContactRelations as [$orderIndex, $contactIndex, $isPrimary, $comment]) {
            $createdOrders[$orderIndex]->contacts()->attach($createdContacts[$contactIndex]->id, [
                'is_primary' => $isPrimary,
                'comment' => $comment,
            ]);
        }

        // Связываем заказы со звонками
        $orderCallRelations = [
            // Заказ 1 - связанные звонки
            [0, 0, 'main'], // основной звонок
            [0, 1, 'callback'], // обратный звонок
            
            // Заказ 2 - завершенный
            [1, 1, 'main'], // основной звонок
            
            // Заказ 3 - мобильное приложение
            [2, 3, 'main'], // основной звонок
            [2, 4, 'extra'], // дополнительный звонок
            
            // Заказ 4 - интеграция
            [3, 5, 'main'], // основной звонок
            
            // Заказ 5 - договор
            [4, 6, 'main'], // основной звонок
            [4, 7, 'manual'], // звонок по договору
            
            // Заказ 6 - техподдержка
            [5, 8, 'main'], // основной звонок
            [5, 9, 'auto'], // звонок поддержки
        ];

        foreach ($orderCallRelations as [$orderIndex, $callIndex, $relationType]) {
            $createdOrders[$orderIndex]->calls()->attach($createdCalls[$callIndex]->id, [
                'relation_type' => $relationType,
            ]);
        }

        $this->command->info('Реалистичные тестовые данные созданы успешно!');
        $this->command->info('Создано:');
        $this->command->info('- ' . count($createdContacts) . ' контактов');
        $this->command->info('- ' . count($createdCalls) . ' звонков');
        $this->command->info('- ' . count($createdOrders) . ' заказов');
    }
}
