# 🚀 Инструкция по установке OverlayCalendar

## 📋 Быстрая установка

### Шаг 1: Копирование файлов

```bash
# Перейдите в папку с компонентом
cd components/overlay-calendar/

# Скопируйте PHP компонент
cp OverlayCalendar.php ../../app/MoonShine/UI/Components/

# Скопируйте CSS файл
cp overlay-calendar.css ../../public/css/

# Скопируйте JavaScript файл
cp overlay-calendar.js ../../public/js/
```

### Шаг 2: Интеграция в Resource

Откройте ваш MoonShine Resource (например, `app/MoonShine/Resources/CallResource.php`) и добавьте:

```php
<?php

namespace App\MoonShine\Resources;

use App\MoonShine\UI\Components\OverlayCalendar;
use MoonShine\Actions\ActionButton;

class YourResource extends ModelResource
{
    public function topButtons(): array
    {
        return [
            ActionButton::make('📅 Выбрать период')
                ->onClick(fn() => 'document.querySelector(\'[x-data*="overlayCalendar"]\').__x.$data.toggleCalendar()')
        ];
    }

    public function pageComponents(): array
    {
        return [
            OverlayCalendar::make('date_range', 'Выберите период')->render()
        ];
    }
}
```

### Шаг 3: Проверка

1. Откройте ваш MoonShine админ-панель
2. Перейдите на страницу с Resource
3. Нажмите кнопку "📅 Выбрать период"
4. Календарь должен открыться как overlay

## 🔧 Детальная установка

### Структура файлов после установки

```
your-project/
├── app/MoonShine/UI/Components/
│   └── OverlayCalendar.php          # ✅ Скопирован
├── public/css/
│   └── overlay-calendar.css         # ✅ Скопирован
├── public/js/
│   └── overlay-calendar.js          # ✅ Скопирован
└── app/MoonShine/Resources/
    └── YourResource.php             # ✅ Модифицирован
```

### Проверка зависимостей

Убедитесь что у вас установлены:

- **PHP**: 8.0+
- **Laravel**: 9.0+
- **MoonShine**: 3.0+
- **Alpine.js**: 3.0+ (входит в MoonShine)

### Проверка файлов

```bash
# Проверьте что файлы скопированы
ls -la app/MoonShine/UI/Components/OverlayCalendar.php
ls -la public/css/overlay-calendar.css
ls -la public/js/overlay-calendar.js

# Проверьте права доступа
chmod 644 app/MoonShine/UI/Components/OverlayCalendar.php
chmod 644 public/css/overlay-calendar.css
chmod 644 public/js/overlay-calendar.js
```

## 🎯 Настройка

### Изменение названия кнопки

```php
ActionButton::make('Ваш текст кнопки')
    ->onClick(fn() => 'document.querySelector(\'[x-data*="overlayCalendar"]\').__x.$data.toggleCalendar()')
```

### Изменение placeholder

```php
OverlayCalendar::make('date_range', 'Ваш placeholder текст')->render()
```

### Предустановленное значение

```php
OverlayCalendar::make('date_range', 'Выберите период')
    ->value('2024-01-15|2024-01-20')
    ->render()
```

## 🎨 Кастомизация

### Изменение цветов

Откройте `public/css/overlay-calendar.css` и измените:

```css
/* Основной цвет */
.calendar-day.selected {
    background-color: #your-color !important;
}

/* Цвет диапазона */
.calendar-day.range-start-end {
    background-color: #your-color !important;
}

/* Цвет кнопок */
.calendar-btn-primary {
    background-color: #your-color;
}
```

### Изменение размеров

```css
/* Размер календаря */
.calendar-overlay-content {
    max-width: 1000px; /* вместо 800px */
}

/* Размер шрифта */
.calendar-month-year {
    font-size: 20px; /* вместо 18px */
}
```

## 🐛 Решение проблем

### Календарь не открывается

1. **Проверьте консоль браузера** на ошибки JavaScript
2. **Убедитесь что Alpine.js загружен**:
   ```html
   <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
   ```
3. **Проверьте что файлы подключены**:
   ```html
   <link rel="stylesheet" href="/css/overlay-calendar.css">
   <script src="/js/overlay-calendar.js"></script>
   ```

### Стили не применяются

1. **Очистите кэш браузера** (Ctrl+F5)
2. **Проверьте путь к CSS файлу**:
   ```bash
   curl http://your-domain.com/css/overlay-calendar.css
   ```
3. **Убедитесь что CSS файл существует**:
   ```bash
   ls -la public/css/overlay-calendar.css
   ```

### JavaScript ошибки

1. **Откройте консоль браузера** (F12)
2. **Проверьте ошибки** в консоли
3. **Убедитесь что файл загружается**:
   ```bash
   curl http://your-domain.com/js/overlay-calendar.js
   ```

### Календарь не адаптивный

1. **Проверьте viewport meta тег**:
   ```html
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   ```
2. **Убедитесь что CSS медиа-запросы работают**
3. **Проверьте размер экрана** в DevTools

## 📱 Тестирование

### На десктопе

1. Откройте календарь
2. Проверьте навигацию стрелками
3. Выберите дату/диапазон
4. Проверьте быстрые периоды
5. Проверьте кнопки "Очистить" и "Применить"

### На мобильных

1. Откройте на телефоне/планшете
2. Проверьте полный экран
3. Проверьте селект быстрых периодов
4. Проверьте фиксированные кнопки внизу
5. Проверьте что скролл фона отключен

### В разных браузерах

- ✅ Chrome 90+
- ✅ Firefox 88+
- ✅ Safari 14+
- ✅ Edge 90+

## 🔄 Обновление

### Обновление компонента

```bash
# Сделайте бэкап текущих файлов
cp app/MoonShine/UI/Components/OverlayCalendar.php app/MoonShine/UI/Components/OverlayCalendar.php.backup
cp public/css/overlay-calendar.css public/css/overlay-calendar.css.backup
cp public/js/overlay-calendar.js public/js/overlay-calendar.js.backup

# Скопируйте новые файлы
cp components/overlay-calendar/OverlayCalendar.php app/MoonShine/UI/Components/
cp components/overlay-calendar/overlay-calendar.css public/css/
cp components/overlay-calendar/overlay-calendar.js public/js/
```

### Откат изменений

```bash
# Восстановите из бэкапа
cp app/MoonShine/UI/Components/OverlayCalendar.php.backup app/MoonShine/UI/Components/OverlayCalendar.php
cp public/css/overlay-calendar.css.backup public/css/overlay-calendar.css
cp public/js/overlay-calendar.js.backup public/js/overlay-calendar.js
```

## 📞 Поддержка

### Логи для отладки

```javascript
// Добавьте в консоль браузера для отладки
console.log('Calendar data:', document.querySelector('[x-data*="overlayCalendar"]').__x.$data);
```

### Проверка состояния

```javascript
// Проверка открыт ли календарь
const isOpen = document.querySelector('[x-data*="overlayCalendar"]').__x.$data.isOpen;
console.log('Calendar is open:', isOpen);

// Проверка выбранных дат
const selectedStart = document.querySelector('[x-data*="overlayCalendar"]').__x.$data.selectedStartDate;
const selectedEnd = document.querySelector('[x-data*="overlayCalendar"]').__x.$data.selectedEndDate;
console.log('Selected dates:', selectedStart, selectedEnd);
```

### Создание issue

При создании issue укажите:

1. **Версию PHP**: `php -v`
2. **Версию Laravel**: `php artisan --version`
3. **Версию MoonShine**: в composer.json
4. **Браузер и версию**: из DevTools
5. **Ошибки из консоли**: скриншот или текст
6. **Шаги воспроизведения**: что делали когда появилась ошибка

---

**Удачной установки!** 🚀
