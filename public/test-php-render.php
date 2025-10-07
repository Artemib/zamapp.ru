<?php
require_once __DIR__ . '/../vendor/autoload.php';

use App\MoonShine\UI\Components\OverlayCalendar;

// Тестируем разные конфигурации
$calendars = [
    'standard' => OverlayCalendar::make('standard', 'Стандартный календарь'),
    'no_quick' => OverlayCalendar::make('no_quick', 'Без быстрых периодов')->showQuickPeriods(false),
    'custom_colors' => OverlayCalendar::make('custom_colors', 'Кастомные цвета')
        ->primaryColor('#8b5cf6')
        ->rangeColor('#f59e0b'),
    'custom_periods' => OverlayCalendar::make('custom_periods', 'Кастомные периоды')
        ->quickPeriods(['today', 'yesterday', 'current_month']),
];

foreach ($calendars as $name => $calendar) {
    echo "=== $name ===\n";
    echo $calendar->render();
    echo "\n\n";
}
?>
