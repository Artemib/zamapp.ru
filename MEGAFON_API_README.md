# Интеграция с API Мегафон

## Настройка

1. **Токен API**: `98daf46c-1850-42ef-a40a-db7f29ff08b0`
2. **URL API**: `https://7280019.megapbx.ru/crmapi/v1`
3. **Эндпоинт истории**: `/history/json`
4. **Заголовок авторизации**: `X-API-KEY`

## Доступные команды

### 🚀 Краткий справочник алиасов
| Алиас | Полная команда | Описание |
|-------|----------------|----------|
| `cc` | `calls:clear` | Очистка данных |
| `sept` | `calls:import-september` | Импорт за сентябрь |
| `mimport` | `calls:import-megafon` | Импорт за период |
| `cs` | `calls:stats` | Статистика |
| `all` | `calls:import-all-megafon` | Импорт всех звонков |
| `mtest` | `megafon:test-auth` | Тест API |

### 1. Очистка данных
```bash
# Полная команда
php artisan calls:clear --force

# Короткий алиас
php artisan cc --force
```
Очищает все данные о звонках, заказах и контактах из БД.

### 2. Импорт звонков за сентябрь (тестовые данные)
```bash
# Полная команда
php artisan calls:import-september --test

# Короткий алиас
php artisan sept --test
```
Импортирует 2410 тестовых звонков за сентябрь 2025.

### 3. Импорт звонков за сентябрь (из API)
```bash
# Полная команда
php artisan calls:import-september

# Короткий алиас
php artisan sept
```
Импортирует реальные звонки за сентябрь 2025 из API Мегафон.

### 4. Импорт звонков за произвольный период
```bash
# Полные команды
php artisan calls:import-megafon --from=2025-09-01 --to=2025-09-30 --tz=msk
php artisan calls:import-megafon --from=2025-09-01 --to=2025-09-30 --tz=utc

# Короткие алиасы
php artisan mimport --from=2025-09-01 --to=2025-09-30 --tz=msk
php artisan mimport --from=2025-09-01 --to=2025-09-30 --tz=utc

# Импорт за текущий месяц (по умолчанию MSK)
php artisan calls:import-megafon
php artisan mimport

# Импорт с очисткой существующих данных
php artisan calls:import-megafon --from=2025-09-01 --to=2025-09-30 --tz=msk --clear
php artisan mimport --from=2025-09-01 --to=2025-09-30 --tz=msk --clear
```

### 5. Статистика звонков
```bash
# Полные команды
php artisan calls:stats
php artisan calls:stats --period=today
php artisan calls:stats --period=week
php artisan calls:stats --period=year

# Короткие алиасы
php artisan cs
php artisan cs --period=today
php artisan cs --period=week
php artisan cs --period=year
```

### 6. Тестирование API
```bash
# Полная команда
php artisan megafon:test-auth

# Короткий алиас
php artisan mtest
```
Тестирует различные способы авторизации с API Мегафон.

## Структура данных API Мегафон

API возвращает массив объектов со следующими полями:

```json
{
  "start": "2025-10-04T23:14:05Z",
  "uid": "S65IDBH830000045",
  "type": "in",
  "status": "success",
  "client": "79811320447",
  "diversion": "79299119251",
  "telnum_name": "Лавр СПБ",
  "destination": "onduty",
  "user": "denis",
  "user_name": "Денис",
  "wait": 20,
  "duration": 6,
  "record": "https://7280019.megapbx.ru/api/v2/call-records/record/..."
}
```

## Формат параметров даты

API Мегафон требует даты в специальном формате:

- **start** - Начало периода: `YYYYmmddTHHMMSSZ`
- **end** - Окончание периода: `YYYYmmddTHHMMSSZ`

Примеры:
- `20250901T000000Z` - 1 сентября 2025, 00:00:00 UTC
- `20250930T235959Z` - 30 сентября 2025, 23:59:59 UTC

## Часовые пояса

Команда поддерживает два часовых пояса:

### MSK (Московское время)
- **Параметр**: `--tz=msk`
- **Часовой пояс**: Europe/Moscow (UTC+3)
- **По умолчанию**: Да
- **Пример**: `--from=2025-09-01 --to=2025-09-30 --tz=msk`

### UTC (Всемирное координированное время)
- **Параметр**: `--tz=utc`
- **Часовой пояс**: UTC (UTC+0)
- **По умолчанию**: Нет
- **Пример**: `--from=2025-09-01 --to=2025-09-30 --tz=utc`

### Разница в результатах
- **MSK**: Включает звонки с 31 августа 21:00 UTC (начало дня в MSK)
- **UTC**: Строго с 1 по 30 сентября UTC
- **Разница**: ~4 звонка (звонки в переходный период)

## Маппинг полей

| API Мегафон | База данных | Описание |
|-------------|-------------|----------|
| `uid` | `callid` | Уникальный ID звонка |
| `start` | `datetime` | Дата и время звонка |
| `type` | `type` | Тип звонка (in/out) |
| `status` | `status` | Статус звонка |
| `client` | `client_phone` | Номер клиента |
| `user` | `user_pbx` | Пользователь АТС |
| `diversion` | `diversion_phone` | Номер АТС |
| `duration` | `duration` | Длительность в секундах |
| `wait` | `wait` | Время ожидания |
| `record` | `link_record_pbx` | Ссылка на запись |

## Статусы звонков

- `success` - Успешный
- `missed` - Пропущенный
- `cancel` - Отмененный
- `busy` - Занято
- `not_available` - Недоступен
- `not_allowed` - Запрещено
- `not_found` - Не найден

## Типы звонков

- `in` - Входящий
- `out` - Исходящий

## Примеры использования

### Полный цикл импорта
```bash
# 1. Очистить данные
php artisan calls:clear --force

# 2. Импортировать данные за сентябрь 2025 (московское время)
php artisan calls:import-megafon --from=2025-09-01 --to=2025-09-30 --tz=msk --clear

# 3. Посмотреть статистику
php artisan calls:stats
```

### Результаты импорта за сентябрь 2025
- ✅ **Импортировано**: 2406 звонков
- 📅 **Период**: 1-30 сентября 2025
- 👥 **Пользователи**: nikolay, nikolay_ads, admin, denis
- 📞 **Типы**: входящие и исходящие звонки
- 🎯 **Статусы**: успешные, пропущенные, отмененные
- 🔗 **Источник**: 100% из API Мегафон

### Импорт с тестовыми данными
```bash
# Очистить и заполнить тестовыми данными
php artisan calls:clear --force
php artisan calls:import-september --test
php artisan calls:stats
```

## Логи

Все операции логируются в `storage/logs/laravel.log` с префиксом `PBX API:`.

## Конфигурация

Настройки API находятся в файле `config/pbx.php`:

```php
'api' => [
    'base_url' => 'https://7280019.megapbx.ru/crmapi/v1',
    'token' => '98daf46c-1850-42ef-a40a-db7f29ff08b0',
    'timeout' => 60,
    'retry_attempts' => 3,
    'retry_delay' => 1000,
],
```
