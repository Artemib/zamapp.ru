# OverlayCalendar - Кастомный календарь для MoonShine

## 📋 Описание

OverlayCalendar - это кастомный компонент календаря для MoonShine Admin Panel с уникальной логикой выбора дат в стиле "Мегафон". Календарь отображается как overlay (перекрытие) с полным экраном на мобильных устройствах и sidebar на десктопе.

## ✨ Особенности

- **Уникальная логика выбора**: одиночная дата → диапазон → сброс
- **Overlay интерфейс**: не модальное окно, а перекрытие контента
- **Адаптивный дизайн**: полный экран на мобильных, sidebar на десктопе
- **Быстрые периоды**: сегодня, вчера, недели, месяцы
- **Навигация стрелками**: смена месяца влево/вправо
- **Отключение скролла**: фона при открытом календаре на мобильных
- **Деактивация кнопок**: когда ничего не выбрано
- **Интеграция с MoonShine**: использует Alpine.js

## 📁 Структура файлов

```
components/overlay-calendar/
├── OverlayCalendar.php          # PHP компонент
├── overlay-calendar.css         # Стили
├── overlay-calendar.js          # JavaScript логика
└── README.md                    # Документация
```

## 🚀 Установка

### 1. Копирование файлов

```bash
# Скопируйте файлы в ваш проект
cp OverlayCalendar.php app/MoonShine/UI/Components/
cp overlay-calendar.css public/css/
cp overlay-calendar.js public/js/
```

### 2. Подключение в MoonShine Resource

```php
<?php

namespace App\MoonShine\Resources;

use App\MoonShine\UI\Components\OverlayCalendar;
use MoonShine\Resources\ModelResource;
use MoonShine\Fields\ID;
use MoonShine\Actions\ActionButton;

class YourResource extends ModelResource
{
    public function topButtons(): array
    {
        return [
            ActionButton::make('Выбрать период')
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

### 3. Альтернативное подключение в Blade шаблоне

```blade
<!-- В любом Blade шаблоне -->
{!! \App\MoonShine\UI\Components\OverlayCalendar::make('date_range', 'Выберите период')->render() !!}
```

## 🎯 Использование

### Базовое использование

```php
use App\MoonShine\UI\Components\OverlayCalendar;

// Создание компонента
$calendar = OverlayCalendar::make('date_range', 'Выберите период');

// Рендеринг
echo $calendar->render();
```

### С предустановленным значением

```php
$calendar = OverlayCalendar::make('date_range', 'Выберите период')
    ->value('2024-01-15|2024-01-20');
```

### Получение выбранного значения

```javascript
// В JavaScript
const selectedValue = document.getElementById('overlay-input-xxx').value;
// Формат: "2024-01-15" (одна дата) или "2024-01-15|2024-01-20" (диапазон)
```

## 🎨 Логика работы

### Выбор дат

1. **Первый клик**: Выбирается одиночная дата (синий цвет)
2. **Второй клик**: 
   - На другую дату → создается диапазон (зеленый цвет)
   - На ту же дату → сброс выбора
3. **Третий клик**: Сброс и выбор новой даты

### Быстрые периоды

- **Сегодня**: Текущий день
- **Вчера**: Вчерашний день
- **Текущая неделя**: Понедельник-воскресенье текущей недели
- **Прошлая неделя**: Понедельник-воскресенье прошлой недели
- **Текущий месяц**: 1-е число - последний день текущего месяца
- **Прошлый месяц**: 1-е число - последний день прошлого месяца

### Навигация

- **Стрелки влево/вправо**: Смена месяца
- **Быстрые периоды**: Автоматический переход к нужному месяцу

## 📱 Адаптивность

### Десктоп (≥769px)
- **Sidebar**: Боковая панель с быстрыми периодами
- **Основной календарь**: Справа от sidebar
- **Размер**: Ограниченный, не на весь экран

### Мобильные (<768px)
- **Полный экран**: 100vw × 100vh
- **Селект быстрых периодов**: Вместо кнопок
- **Фиксированные кнопки**: Внизу экрана
- **Отключен скролл**: Фона при открытом календаре

## 🎛️ API

### PHP класс

```php
class OverlayCalendar
{
    public function __construct(string $name, string $placeholder = 'Выберите дату')
    public static function make(string $name, string $placeholder = 'Выберите дату'): self
    public function value(?string $value): self
    public function render(): string
    public function getName(): string
}
```

### JavaScript компонент

```javascript
function overlayCalendar(placeholder = 'Выберите дату') {
    return {
        // Данные
        isOpen: false,
        currentDate: new Date(),
        selectedStartDate: null,
        selectedEndDate: null,
        selectedValue: '',
        selectedPeriod: null,
        
        // Методы
        toggleCalendar(),
        closeCalendar(),
        previousMonth(),
        nextMonth(),
        selectDate(day),
        selectQuickPeriod(period),
        clearSelection(),
        applySelection(),
        
        // Computed свойства
        get displayText(),
        get currentMonthYear(),
        get hasSelection(),
        get calendarDays()
    }
}
```

## 🎨 Кастомизация

### CSS переменные

```css
:root {
    --calendar-primary: #8b5cf6;
    --calendar-secondary: #f3f4f6;
    --calendar-text: #374151;
    --calendar-border: #d1d5db;
}
```

### Изменение цветов

```css
/* Цвет выбранной даты */
.calendar-day.selected {
    background-color: #your-color !important;
}

/* Цвет диапазона */
.calendar-day.range-start-end {
    background-color: #your-color !important;
}
```

### Изменение размеров

```css
/* Размер календаря на десктопе */
.calendar-overlay-content {
    max-width: 1000px; /* вместо 800px */
}

/* Размер шрифта */
.calendar-month-year {
    font-size: 20px; /* вместо 18px */
}
```

## 🔧 Интеграция с формами

### Laravel форма

```php
// В контроллере
public function store(Request $request)
{
    $dateRange = $request->input('date_range');
    
    if (strpos($dateRange, '|') !== false) {
        // Диапазон дат
        [$startDate, $endDate] = explode('|', $dateRange);
    } else {
        // Одна дата
        $startDate = $endDate = $dateRange;
    }
    
    // Обработка дат...
}
```

### JavaScript обработка

```javascript
// Обработка изменения значения
document.addEventListener('change', function(e) {
    if (e.target.name === 'date_range') {
        const value = e.target.value;
        console.log('Выбранный период:', value);
        
        // Отправка на сервер или другая обработка
        fetch('/api/process-dates', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({ date_range: value })
        });
    }
});
```

## 🐛 Отладка

### Проверка инициализации

```javascript
// В консоли браузера
console.log('Calendar data:', document.querySelector('[x-data*="overlayCalendar"]').__x.$data);
```

### Проверка стилей

```css
/* Временно добавить для отладки */
.calendar-overlay {
    border: 2px solid red !important;
}
```

### Проверка событий

```javascript
// Логирование событий
document.addEventListener('click', function(e) {
    if (e.target.closest('.calendar-day')) {
        console.log('Calendar day clicked:', e.target);
    }
});
```

## 📋 Требования

- **PHP**: 8.0+
- **Laravel**: 9.0+
- **MoonShine**: 3.0+
- **Alpine.js**: 3.0+ (входит в MoonShine)
- **Браузеры**: Chrome 90+, Firefox 88+, Safari 14+

## 🚨 Известные ограничения

1. **Только один календарь**: На странице может быть только один экземпляр
2. **Нет валидации**: Не проверяет корректность дат
3. **Нет локализации**: Только русский язык
4. **Нет темной темы**: Только светлая тема

## 🔄 Обновления

### Версия 1.0.0
- ✅ Базовая функциональность
- ✅ Адаптивный дизайн
- ✅ Интеграция с MoonShine
- ✅ Быстрые периоды
- ✅ Уникальная логика выбора дат

### Планируемые обновления
- 🔄 Поддержка множественных календарей
- 🔄 Валидация дат
- 🔄 Локализация
- 🔄 Темная тема
- 🔄 Кастомные быстрые периоды

## 📞 Поддержка

При возникновении проблем:

1. Проверьте консоль браузера на ошибки
2. Убедитесь что все файлы подключены
3. Проверьте версии зависимостей
4. Создайте issue с описанием проблемы

## 📄 Лицензия

MIT License - свободно используйте в своих проектах.

---

**Создано для MoonShine Admin Panel** 🚀
