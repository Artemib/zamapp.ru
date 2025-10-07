<?php

declare(strict_types=1);

namespace App\MoonShine\Pages;

use App\MoonShine\UI\Components\OverlayCalendar;
use MoonShine\Laravel\Pages\Page;
use MoonShine\UI\Components\Layout\Box;
use MoonShine\UI\Fields\Text;
use Illuminate\Support\HtmlString;

class CalendarDemoPage extends Page
{

    public function title(string $title = 'Демо календарей'): static
    {
        return parent::title($title);
    }

    public function components(): array
    {
        return [
            OverlayCalendar::make('standard_calendar2')
                ->render(),

            
            Box::make([
                Text::make('Демонстрация различных вариантов OverlayCalendar')
                    ->class('text-2xl font-bold mb-6'),
                Text::make('Здесь показаны различные настройки и варианты использования календаря')
                    ->class('text-gray-600 mb-8'),
            ]),

            // 1. Стандартный календарь
            Box::make([
                Text::make('1. Стандартный календарь')->class('text-xl font-semibold mb-4'),
                Text::make('Все функции включены по умолчанию')->class('text-gray-600 mb-4'),
                new HtmlString(OverlayCalendar::make('standard_calendar')
                ->render())
            ]),

            // 2. Календарь без быстрых периодов
            Box::make([
                Text::make('2. Календарь без быстрых периодов')->class('text-xl font-semibold mb-4'),
                Text::make('Отключены быстрые периоды, только календарь')->class('text-gray-600 mb-4'),
                new HtmlString(OverlayCalendar::make('no_quick_periods', 'Без быстрых периодов')
                    ->showQuickPeriods(false)
                    ->maxWidth('300px')
                    ->maxHeight('100vh')
                    ->render())
            ]),

            // 3. Кастомные быстрые периоды
            Box::make([
                Text::make('3. Кастомные быстрые периоды')->class('text-xl font-semibold mb-4'),
                Text::make('Только "Сегодня", "Вчера", "Текущий месяц"')->class('text-gray-600 mb-4'),
                new HtmlString(OverlayCalendar::make('custom_quick_periods', 'Кастомные быстрые периоды')
                    ->quickPeriods(['today', 'yesterday', 'current_month'])->render())
            ]),

            // 4. Кастомные цвета
            Box::make([
                Text::make('4. Кастомные цвета')->class('text-xl font-semibold mb-4'),
                Text::make('Фиолетовый для одиночных дат, оранжевый для диапазонов')->class('text-gray-600 mb-4'),
                new HtmlString(OverlayCalendar::make('custom_colors', 'Кастомные цвета')
                    ->primaryColor('#8b5cf6') // Фиолетовый
                    ->rangeColor('#f59e0b')    // Оранжевый
                    ->inRangeColor('#fde68a', '#7c2d12') // светло-жёлтый фон, тёмно-коричневый текст
                    ->quickActiveColor('#2563eb', '#ffffff') // синий активный быстрый период
                    ->applyButtonColor('#111827', '#ffffff') // тёмная кнопка применить
                    ->render())
            ]),

            // 5. Без отображения выбранного периода
            Box::make([
                Text::make('5. Без отображения выбранного периода')->class('text-xl font-semibold mb-4'),
                Text::make('Скрыто отображение периода под календарем')->class('text-gray-600 mb-4'),
                new HtmlString(OverlayCalendar::make('no_selected_period_display', 'Без отображения периода')
                    ->showSelectedPeriod(false)->render())
            ]),

            // 6. Месяцы цифрами
            Box::make([
                Text::make('6. Месяцы цифрами')->class('text-xl font-semibold mb-4'),
                Text::make('Месяцы отображаются цифрами (1.2024 вместо Январь 2024)')->class('text-gray-600 mb-4'),
                new HtmlString(OverlayCalendar::make('numeric_months', 'Месяцы цифрами')
                    ->showMonthNames(false)->render())
            ]),

            // 6.1. Только одна дата (без диапазона) и без быстрых периодов
            Box::make([
                Text::make('6.1. Только одна дата')->class('text-xl font-semibold mb-4'),
                Text::make('Выбор строго одной даты, быстрый выбор скрыт')->class('text-gray-600 mb-4'),
                new HtmlString(OverlayCalendar::make('single_only', 'Только одна дата')
                    ->singleDateOnly(true)
                    ->render())
            ]),

            // 7. Комбинированный пример
            Box::make([
                Text::make('7. Комбинированный пример')->class('text-xl font-semibold mb-4'),
                Text::make('Красные цвета, только сегодня/вчера, без отображения периода, месяцы цифрами')->class('text-gray-600 mb-4'),
                new HtmlString(OverlayCalendar::make('combined_example', 'Комбинированный')
                    ->primaryColor('#ef4444')  // Красный
                    ->rangeColor('#dc2626')    // Темно-красный
                    ->quickPeriods(['today', 'yesterday'])
                    ->showSelectedPeriod(false)
                    ->showMonthNames(false)
                    ->inRangeColor('#fecaca', '#7f1d1d') // светло-красный фон in-range
                    ->quickActiveColor('#ef4444', '#ffffff')
                    ->applyButtonColor('#ef4444', '#ffffff')
                    ->render())
            ]),

            // 8. Пастельные цвета и все быстрые периоды
            Box::make([
                Text::make('8. Пастельные цвета')->class('text-xl font-semibold mb-4'),
                Text::make('Мягкие оттенки для всех состояний')->class('text-gray-600 mb-4'),
                new HtmlString(OverlayCalendar::make('pastel_colors', 'Пастельные цвета')
                    ->primaryColor('#a78bfa') // сиреневый
                    ->rangeColor('#34d399')    // зелёный
                    ->inRangeColor('#e9d5ff', '#4c1d95')
                    ->quickActiveColor('#34d399', '#064e3b')
                    ->applyButtonColor('#a78bfa', '#1f2937')
                    ->render())
            ]),

            // 9. Внешняя кнопка + один целевой инпут
            Box::make([
                Text::make('9. Внешний триггер: кнопка + один инпут')->class('text-xl font-semibold mb-4'),
                Text::make('Кнопка открывает календарь, результат записывается в указанный инпут')->class('text-gray-600 mb-4'),
                new HtmlString(
                    '<div class="flex items-center gap-3 mb-3">'
                    . '<button id="extOpenSingle" type="button" class="px-3 py-2 rounded bg-blue-600 text-white">Открыть календарь</button>'
                    . '<input id="extTargetSingle" type="text" class="border border-gray-300 rounded px-3 py-2" placeholder="Результат" />'
                    . '</div>'
                    . OverlayCalendar::make('demo_external_single')
                        ->openWith('#extOpenSingle', '#extTargetSingle')
                        ->render()
                )
            ]),

            // 10. Внешняя кнопка + два инпута (диапазон)
            Box::make([
                Text::make('10. Внешний триггер: кнопка + два инпута (диапазон)')->class('text-xl font-semibold mb-4'),
                Text::make('Если выбран диапазон, первая дата попадёт в первый инпут, вторая — во второй')->class('text-gray-600 mb-4'),
                new HtmlString(
                    '<div class="flex items-center gap-3 mb-3">'
                    . '<button id="extOpenRange" type="button" class="px-3 py-2 rounded bg-blue-600 text-white">Открыть календарь</button>'
                    . '<input id="extStart" type="text" class="border border-gray-300 rounded px-3 py-2" placeholder="Начало" />'
                    . '<input id="extEnd" type="text" class="border border-gray-300 rounded px-3 py-2" placeholder="Конец" />'
                    . '</div>'
                    . OverlayCalendar::make('demo_external_range')
                        ->openWith('#extOpenRange', '#extStart', '#extEnd')
                        ->render()
                )
            ]),

            // 11. Открытие по событию focus на инпуте
            Box::make([
                Text::make('11. Открытие по focus на инпуте')->class('text-xl font-semibold mb-4'),
                Text::make('Фокус на поле запускает календарь; результат пишется в это же поле')->class('text-gray-600 mb-4'),
                new HtmlString(
                    '<div class="flex items-center gap-3 mb-3">'
                    . '<input id="focusInput" type="text" class="border border-gray-300 rounded px-3 py-2" placeholder="Фокус для открытия" />'
                    . '</div>'
                    . OverlayCalendar::make('demo_focus_open')
                        ->openWith('#focusInput', '#focusInput', null, 'focus')
                        ->render()
                )
            ]),
        ];
    }
}