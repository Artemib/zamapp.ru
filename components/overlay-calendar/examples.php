<?php

/**
 * Примеры использования OverlayCalendar
 * 
 * Этот файл содержит различные примеры интеграции календаря
 * в MoonShine ресурсы и другие части приложения.
 */

namespace App\MoonShine\Examples;

use App\MoonShine\UI\Components\OverlayCalendar;
use MoonShine\Resources\ModelResource;
use MoonShine\Actions\ActionButton;
use MoonShine\Fields\Text;
use MoonShine\Fields\Date;

// ========================================
// ПРИМЕР 1: Базовая интеграция в Resource
// ========================================

class BasicResourceExample extends ModelResource
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
            OverlayCalendar::make('date_range', 'Выберите период для фильтрации')->render()
        ];
    }
}

// ========================================
// ПРИМЕР 2: С предустановленным значением
// ========================================

class PrefilledResourceExample extends ModelResource
{
    public function pageComponents(): array
    {
        // Устанавливаем текущий месяц как значение по умолчанию
        $currentMonth = now()->format('Y-m-01') . '|' . now()->format('Y-m-t');
        
        return [
            OverlayCalendar::make('date_range', 'Период отчета')
                ->value($currentMonth)
                ->render()
        ];
    }
}

// ========================================
// ПРИМЕР 3: Множественные календари (если нужно)
// ========================================

class MultipleCalendarsExample extends ModelResource
{
    public function pageComponents(): array
    {
        return [
            // Календарь для начала периода
            OverlayCalendar::make('start_date', 'Дата начала')->render(),
            
            // Календарь для конца периода  
            OverlayCalendar::make('end_date', 'Дата окончания')->render(),
        ];
    }
}

// ========================================
// ПРИМЕР 4: Интеграция с фильтрами
// ========================================

class FilteredResourceExample extends ModelResource
{
    public function filters(): array
    {
        return [
            // Обычные фильтры
            Text::make('Название'),
            Date::make('Создано'),
            
            // Календарь как фильтр
            OverlayCalendar::make('date_filter', 'Фильтр по дате')->render()
        ];
    }
}

// ========================================
// ПРИМЕР 5: Обработка в контроллере
// ========================================

class CalendarController
{
    public function processDateRange(Request $request)
    {
        $dateRange = $request->input('date_range');
        
        if (empty($dateRange)) {
            return response()->json(['error' => 'Период не выбран'], 400);
        }
        
        // Парсинг диапазона дат
        if (strpos($dateRange, '|') !== false) {
            // Диапазон дат
            [$startDate, $endDate] = explode('|', $dateRange);
            $startDate = \Carbon\Carbon::parse($startDate);
            $endDate = \Carbon\Carbon::parse($endDate);
        } else {
            // Одна дата
            $startDate = $endDate = \Carbon\Carbon::parse($dateRange);
        }
        
        // Пример обработки
        $records = YourModel::whereBetween('created_at', [$startDate, $endDate])->get();
        
        return response()->json([
            'success' => true,
            'start_date' => $startDate->format('Y-m-d'),
            'end_date' => $endDate->format('Y-m-d'),
            'records_count' => $records->count()
        ]);
    }
}

// ========================================
// ПРИМЕР 6: JavaScript интеграция
// ========================================

/*
// В вашем JavaScript файле или в <script> теге

document.addEventListener('DOMContentLoaded', function() {
    // Обработка изменения значения календаря
    document.addEventListener('change', function(e) {
        if (e.target.name === 'date_range') {
            const value = e.target.value;
            console.log('Выбранный период:', value);
            
            // Отправка на сервер
            fetch('/api/process-dates', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({ 
                    date_range: value,
                    action: 'filter_records'
                })
            })
            .then(response => response.json())
            .then(data => {
                console.log('Результат:', data);
                // Обновление таблицы или других элементов
                updateTable(data.records);
            })
            .catch(error => {
                console.error('Ошибка:', error);
            });
        }
    });
    
    // Программное открытие календаря
    function openCalendar() {
        const calendar = document.querySelector('[x-data*="overlayCalendar"]');
        if (calendar && calendar.__x) {
            calendar.__x.$data.toggleCalendar();
        }
    }
    
    // Программное закрытие календаря
    function closeCalendar() {
        const calendar = document.querySelector('[x-data*="overlayCalendar"]');
        if (calendar && calendar.__x) {
            calendar.__x.$data.closeCalendar();
        }
    }
    
    // Получение текущего значения
    function getSelectedDateRange() {
        const input = document.querySelector('input[name="date_range"]');
        return input ? input.value : null;
    }
    
    // Установка значения программно
    function setSelectedDateRange(value) {
        const input = document.querySelector('input[name="date_range"]');
        if (input) {
            input.value = value;
            // Обновляем отображение
            const calendar = document.querySelector('[x-data*="overlayCalendar"]');
            if (calendar && calendar.__x) {
                calendar.__x.$data.selectedValue = value;
            }
        }
    }
});
*/

// ========================================
// ПРИМЕР 7: Кастомизация стилей
// ========================================

/*
// Добавьте в ваш CSS файл для кастомизации

:root {
    --calendar-primary: #your-primary-color;
    --calendar-secondary: #your-secondary-color;
    --calendar-accent: #your-accent-color;
}

/* Изменение цветовой схемы */
.calendar-day.selected {
    background-color: var(--calendar-primary) !important;
}

.calendar-day.range-start-end {
    background-color: var(--calendar-accent) !important;
}

.calendar-btn-primary {
    background-color: var(--calendar-primary);
}

/* Изменение размеров */
.calendar-overlay-content {
    max-width: 1000px; /* Увеличить ширину */
}

.calendar-month-year {
    font-size: 20px; /* Увеличить размер шрифта */
}

/* Кастомные анимации */
.calendar-overlay {
    transition: all 0.3s ease-in-out;
}

.calendar-day {
    transition: all 0.2s ease;
}

.calendar-day:hover {
    transform: scale(1.05);
}
*/

// ========================================
// ПРИМЕР 8: Интеграция с формами
// ========================================

class FormIntegrationExample
{
    public function createForm()
    {
        return [
            // Обычные поля формы
            Text::make('Название отчета'),
            Text::make('Описание'),
            
            // Календарь для выбора периода
            OverlayCalendar::make('report_period', 'Период отчета')->render(),
            
            // Кнопка отправки
            ActionButton::make('Создать отчет')
                ->onClick('submitReport()')
        ];
    }
}

// ========================================
// ПРИМЕР 9: API endpoints
// ========================================

/*
// В routes/api.php

Route::post('/calendar/process', [CalendarController::class, 'processDateRange']);
Route::get('/calendar/quick-periods', [CalendarController::class, 'getQuickPeriods']);

// В CalendarController

public function getQuickPeriods()
{
    $today = now();
    
    return response()->json([
        'today' => $today->format('Y-m-d'),
        'yesterday' => $today->subDay()->format('Y-m-d'),
        'current_week' => [
            'start' => $today->startOfWeek()->format('Y-m-d'),
            'end' => $today->endOfWeek()->format('Y-m-d')
        ],
        'current_month' => [
            'start' => $today->startOfMonth()->format('Y-m-d'),
            'end' => $today->endOfMonth()->format('Y-m-d')
        ]
    ]);
}
*/

// ========================================
// ПРИМЕР 10: Тестирование
// ========================================

/*
// В тестах

class CalendarTest extends TestCase
{
    public function test_calendar_renders()
    {
        $calendar = OverlayCalendar::make('test_date', 'Test Calendar');
        $html = $calendar->render();
        
        $this->assertStringContainsString('overlay-calendar', $html);
        $this->assertStringContainsString('x-data="overlayCalendar', $html);
    }
    
    public function test_calendar_with_value()
    {
        $calendar = OverlayCalendar::make('test_date', 'Test Calendar')
            ->value('2024-01-15|2024-01-20');
            
        $html = $calendar->render();
        
        $this->assertStringContainsString('2024-01-15|2024-01-20', $html);
    }
}
*/
