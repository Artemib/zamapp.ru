# ⚡ Быстрый старт OverlayCalendar

## 🚀 Установка за 3 шага

### 1️⃣ Копирование файлов
```bash
# Перейдите в папку с компонентом
cd components/overlay-calendar/

# Скопируйте файлы
cp OverlayCalendar.php ../../app/MoonShine/UI/Components/
cp overlay-calendar.css ../../public/css/
cp overlay-calendar.js ../../public/js/
```

### 2️⃣ Интеграция в Resource
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

### 3️⃣ Готово! 🎉
Откройте ваш MoonShine админ-панель и нажмите кнопку "📅 Выбрать период"

## 🎯 Основные возможности

- **Выбор дат**: Одиночная дата → диапазон → сброс
- **Быстрые периоды**: Сегодня, вчера, недели, месяцы
- **Адаптивность**: Полный экран на мобильных, sidebar на десктопе
- **Навигация**: Стрелки влево/вправо для смены месяца

## 📱 Адаптивность

### Десктоп
- Sidebar с быстрыми периодами
- Основной календарь справа
- Ограниченный размер

### Мобильные
- Полный экран (100vw × 100vh)
- Селект быстрых периодов
- Фиксированные кнопки внизу

## 🔧 Получение значения

```javascript
// В JavaScript
const value = document.querySelector('input[name="date_range"]').value;
// Формат: "2024-01-15" (одна дата) или "2024-01-15|2024-01-20" (диапазон)
```

```php
// В PHP контроллере
$dateRange = $request->input('date_range');
if (strpos($dateRange, '|') !== false) {
    [$startDate, $endDate] = explode('|', $dateRange);
} else {
    $startDate = $endDate = $dateRange;
}
```

## 🎨 Кастомизация

### Изменение цветов
```css
.calendar-day.selected {
    background-color: #your-color !important;
}
```

### Изменение размеров
```css
.calendar-overlay-content {
    max-width: 1000px; /* вместо 800px */
}
```

## 🐛 Решение проблем

### Календарь не открывается
1. Проверьте консоль браузера (F12)
2. Убедитесь что Alpine.js загружен
3. Проверьте что файлы подключены

### Стили не применяются
1. Очистите кэш браузера (Ctrl+F5)
2. Проверьте путь к CSS файлу
3. Убедитесь что файл существует

## 📚 Дополнительная документация

- **[README.md](README.md)** - Полная документация
- **[INSTALL.md](INSTALL.md)** - Детальная инструкция по установке
- **[TECHNICAL.md](TECHNICAL.md)** - Техническая документация
- **[examples.php](examples.php)** - Примеры использования
- **[CHECKLIST.md](CHECKLIST.md)** - Чек-лист проверки

---

**Удачного использования!** 🚀
