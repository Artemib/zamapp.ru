# Импорт звонков из АТС

Система для импорта звонков из АТС MegaPBX в базу данных CRM.

## Установка и настройка

### 1. Конфигурация

Создайте файл `.env` или добавьте следующие переменные:

```env
# PBX API Configuration
PBX_API_URL=https://7280019.megapbx.ru/crmapi/v1
PBX_API_TOKEN=5bb722db-43b2-4a8a-a0d5-29f320ae8d0a
PBX_API_TIMEOUT=60
PBX_API_RETRY_ATTEMPTS=3
PBX_API_RETRY_DELAY=1000

# Import Settings
PBX_DEFAULT_SOURCE=PBX API
PBX_AUTO_RETRY_FAILED=true
PBX_BATCH_SIZE=100
```

### 2. Структура базы данных

Убедитесь, что таблица `calls` создана с помощью миграции:

```bash
php artisan migrate
```

## Использование

### Основные команды

#### Тестирование соединения
```bash
php artisan pbx:import-calls --test
```

#### Демо-режим (тестовые данные)
```bash
php artisan pbx:import-calls --demo
```

#### Импорт звонков за сентябрь 2025
```bash
php artisan pbx:import-calls
```

#### Импорт за конкретный месяц
```bash
php artisan pbx:import-calls --month=2025-09
```

#### Принудительное обновление существующих звонков
```bash
php artisan pbx:import-calls --demo --force
```

### Параметры команды

- `--month=YYYY-MM` - Месяц для импорта (по умолчанию: сентябрь 2025)
- `--force` - Принудительно перезаписать существующие звонки
- `--test` - Тестовый режим - только проверить соединение
- `--demo` - Демо режим - использовать тестовые данные

## Структура данных

### Модель Call

```php
// Основные поля
'callid' => 'string',           // Уникальный ID звонка в АТС
'datetime' => 'timestamp',      // Дата и время звонка
'type' => 'enum',              // Тип: 'in' (входящий) или 'out' (исходящий)
'status' => 'enum',            // Статус: 'success', 'missed', 'cancel', 'busy', etc.
'client_phone' => 'string',    // Номер телефона клиента
'user_pbx' => 'string',        // ID пользователя АТС
'diversion_phone' => 'string', // Номер АТС
'duration' => 'integer',       // Длительность в секундах
'wait' => 'integer',           // Время ожидания ответа
'link_record_pbx' => 'string', // Ссылка на запись в АТС
'link_record_crm' => 'string', // Ссылка на запись в CRM
'transcribation' => 'text',    // Расшифровка разговора
'from_source_name' => 'string' // Источник данных
```

### Статусы звонков

- `success` - Успешный
- `missed` - Пропущенный
- `cancel` - Отменённый
- `busy` - Занято
- `not_available` - Недоступен
- `not_allowed` - Запрещено
- `not_found` - Не найден

### Типы звонков

- `in` - Входящий
- `out` - Исходящий

## API Integration

### Сервис PbxApiService

Основной сервис для работы с API АТС:

```php
use App\Services\PbxApiService;

$pbxService = new PbxApiService();

// Получить звонки за период
$calls = $pbxService->getCalls($dateFrom, $dateTo);

// Получить звонки за сентябрь
$calls = $pbxService->getCallsForSeptember();

// Тестовые данные
$calls = $pbxService->getTestCalls();

// Проверить соединение
$isConnected = $pbxService->testConnection();
```

### Конфигурация API

Настройки API находятся в файле `config/pbx.php`:

- `api.base_url` - Базовый URL API
- `api.token` - Токен авторизации
- `api.timeout` - Таймаут запросов
- `api.retry_attempts` - Количество попыток повтора
- `api.retry_delay` - Задержка между попытками

## Логирование

Все операции логируются в `storage/logs/laravel.log`:

- Успешные запросы к API
- Ошибки соединения
- Ошибки обработки данных
- Статистика импорта

## Troubleshooting

### Проблемы с API

1. **"Empty token"** - API Мегафона не поддерживает получение данных через GET запросы
2. **"Not Implemented"** - API эндпоинт не реализован для получения данных
3. **API работает только для отправки данных** - Мегафон API предназначен для получения webhook'ов от АТС

### Важно: API Мегафона

API Мегафона (`https://7280019.megapbx.ru/crmapi/v1`) работает только для **получения данных от АТС** (webhook), а не для **запроса данных из АТС**.

**Для получения звонков нужно:**
1. Настроить webhook в АТС Мегафона для отправки данных на ваш API
2. Использовать другой API для запроса истории звонков
3. Или использовать экспорт данных из админки АТС

### Проблемы с данными

1. **Дубликаты** - Используйте `--force` для обновления существующих записей
2. **Некорректные статусы** - Проверьте маппинг в `config/pbx.php`
3. **Ошибки валидации** - Проверьте структуру данных в API

## Примеры использования

### Импорт за последний месяц
```bash
# Получить текущий месяц
CURRENT_MONTH=$(date +%Y-%m)
php artisan pbx:import-calls --month=$CURRENT_MONTH
```

### Автоматический импорт (cron)
```bash
# Добавить в crontab для ежедневного импорта
0 2 * * * cd /path/to/project && php artisan pbx:import-calls --month=$(date +\%Y-\%m) >> /var/log/pbx-import.log 2>&1
```

### Проверка данных
```bash
# Проверить количество звонков в БД
php artisan tinker --execute="echo 'Звонков в БД: ' . App\Models\Call::count();"

# Посмотреть последние звонки
php artisan tinker --execute="App\Models\Call::latest()->take(5)->get(['callid', 'datetime', 'type', 'status', 'client_phone'])->each(function(\$call) { echo \$call->callid . ' - ' . \$call->datetime . ' - ' . \$call->type_name . ' - ' . \$call->status_name . PHP_EOL; });"
```
