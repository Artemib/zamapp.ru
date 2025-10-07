<?php

declare(strict_types=1);

namespace App\MoonShine\Pages;

use MoonShine\Laravel\Pages\Page;
use MoonShine\Contracts\UI\ComponentContract;
use App\MoonShine\UI\Components\OverlayCalendar;
use MoonShine\UI\Components\Layout\Box;
use MoonShine\UI\Components\Layout\Divider;
use MoonShine\UI\Fields\Text;
use MoonShine\UI\Components\ActionButton;


class CalendarDemo extends Page
{


    protected ?string $alias = 'calendar-demo';
    /**
     * @return array<string, string>
     */
    public function getBreadcrumbs(): array
    {
        return [
            '#' => $this->getTitle()
        ];
    }

    public function getTitle(): string
    {
        return $this->title ?: 'CalendarDemo';
    }

    /**
     * @return list<ComponentContract>
     */
    protected function components(): iterable
	{
		return [
            Box::make('1. Standard Calendar - Стандартный календарь', [
                OverlayCalendar::make('standard_calendar')->render(),
            ]),

            Divider::make(),
            

            Box::make('2.No Quick Periods - Без быстрых периодов', [
                OverlayCalendar::make('no_quick_periods')
                ->showQuickPeriods(false)
                ->render(),
            ]),

            Divider::make(),

            Box::make('3. Custom Quick Periods - Кастомные быстрые периоды', [
                OverlayCalendar::make('custom_quick_periods')
                ->quickPeriods(['today', 'yesterday', 'current_month'])
                ->render(),
            ]),

            Divider::make(),

            Box::make('4. Custom Colors - Кастомные цвета', [
                OverlayCalendar::make('custom_colors')
                ->primaryColor('#8b5cf6')
                ->rangeColor('#f59e0b')
                ->render(),
            ]),

            Divider::make(),


            Box::make('5. No Selected Period Display - Без отображения выбранного периода', [
                OverlayCalendar::make('no_selected_period_display')
                ->showSelectedPeriod(false)
                ->render(),
            ]),

            Divider::make(),

            Box::make('6. Months in Numbers - Месяцы цифрами', [
                OverlayCalendar::make('months_in_numbers')
                ->showMonthNames(false)
                ->render(),
            ]),

            Divider::make(),

            Box::make('7. Custom Quick Title - Кастомный заголовок быстрых периодов', [

                OverlayCalendar::make('quick_title')
                ->quickTitle('Custom Quick Title')
                ->primaryColor('#8b5cf6') // Фиолетовый
                ->rangeColor('#f59e0b')    // Оранжевый
                ->inRangeColor('#fde68a', '#7c2d12') // светло-жёлтый фон, тёмно-коричневый текст
                ->quickActiveColor('#2563eb', '#ffffff') // синий активный быстрый период
                ->applyButtonColor('#111827', '#ffffff') // тёмная кнопка применить
                ->render(),
            ]),

            Divider::make(),

            Box::make('8. Max Width 500px - Максимальная ширина 500px', [
                OverlayCalendar::make('max_width')
                ->maxWidth('500px')
                ->render(),
            ]),

            Divider::make(),

            Box::make('9. Max Height 40vh - Максимальная высота 40vh', [
                OverlayCalendar::make('max_height')
                ->showQuickPeriods(false)
                ->maxHeight('40vh')
                ->render(),
            ]),

            Divider::make(),

            // Single date only
            Box::make('10. Single Date Only - Только одна дата', [
                OverlayCalendar::make('single_only_demo', 'Только одна дата')
                ->singleDateOnly(true)
                ->render()
            ]),

            Divider::make(),

            // External trigger + single input
            Box::make('11. External Trigger (Single Input) - Внешняя кнопка + один инпут', [
                ActionButton::make('Открыть календарь')
                ->setAttribute('id', 'extBtnSingle2')
                ->primary()
                ->icon('calendar-days'),

                
                Text::make()
                ->placeholder('Начало')
                ->class('text-xl font-semibold mb-4')
                ->setAttribute('id', 'extInputSingle2'),

                OverlayCalendar::make('external_single_demo')
                ->openWith('#extBtnSingle2', '#extInputSingle2')
                ->render()
            ]),

            Divider::make(),

            // External trigger + range inputs
            Box::make('12. External Trigger (Range Inputs) - Внешняя кнопка + два инпута', [

                ActionButton::make('Открыть календарь')
                ->setAttribute('id', 'extBtnRange2')
                ->primary()
                ->icon('calendar-days'),


                Text::make()
                ->placeholder('Начало')
                ->class('text-xl font-semibold mb-4')
                ->setAttribute('id', 'extStart2'),

                Text::make()
                ->placeholder('Конец')
                ->class('text-xl font-semibold mb-4')
                ->setAttribute('id', 'extEnd2'),


                OverlayCalendar::make('external_range_demo')
                ->openWith('#extBtnRange2', '#extStart2', '#extEnd2')
                ->render(),

            ]),

            Divider::make(),

                 // Focus trigger
                 Box::make('13. Focus Trigger - Открытие по фокусу', [
                     Text::make('Нажмите на поле ниже - календарь откроется при фокусе, а не при клике')->class('text-gray-600 mb-4'),
                     
                     new \Illuminate\Support\HtmlString(
                         '<input id="focusInput2" type="text" class="border border-gray-300 rounded-md px-3 py-2 w-full" placeholder="Фокус для открытия календаря" readonly />'
                     ),

                     OverlayCalendar::make('focus_open_demo')
                         ->openWith('#focusInput2', '#focusInput2', null, 'focus')
                         ->render(),

                 ]),

            Divider::make(),

            // 14. Custom format for single date
            Box::make('14. Custom Format (Single Date) - Формат для одиночной даты', [
                OverlayCalendar::make('single_with_time', 'Дата и время')
                    ->singleDateOnly(true)
                    ->format('Y.m.d H:i:s')
                    ->render(),
            ]),

            Divider::make(),

            // 15. Custom delimiter and format for range
            Box::make('15. Custom Delimiter + Format (Range) - Кастомный разделитель и формат', [
                OverlayCalendar::make('range_custom_format', 'Диапазон дат')
                    ->format('d.m.Y H:i:s')
                    ->rangeDelimiter(' z ')
                    ->includeEndOfDay(true)
                    ->render(),
            ]),

            Divider::make(),

            // 16. Output to DIV with custom delimiter + include end of day
            Box::make('16. Output to DIV (Range) - Вывод в DIV с концом дня', [

                ActionButton::make('Открыть календарь')
                ->setAttribute('id', 'extBtnDiv')
                ->primary()
                ->icon('calendar-days'),

                Text::make('')
                ->class('text-xl font-semibold mb-4')
                ->placeholder('Кстомный инпут')
                ->setAttribute('id', 'resultDiv'),

                OverlayCalendar::make('range_to_div', 'Диапазон в DIV')
                    ->openWith('#extBtnDiv', '#resultDiv')
                    ->format('d.m.Y H:i:s')
                    ->rangeDelimiter(' z ')
                    ->includeEndOfDay(true)
                    ->render(),
            ]),


                 // 17. Output to DIV with custom delimiter + include end of day
                 Box::make('17. Output to DIV (Range) - Вывод в DIV с концом дня', [

                     OverlayCalendar::make('range_to_div_input_base', 'Диапазон в DIV')
                         ->format('d.m.Y H:i:s')
                         ->rangeDelimiter(' z ')
                         ->includeEndOfDay(true)
                         ->render(),
                 ]),

                 Divider::make(),

                 // 18. Different delimiters for display and value
                 Box::make('18. Different Delimiters - Разные разделители для отображения и value', [
                     OverlayCalendar::make('different_delimiters', 'Разные разделители')
                         ->format('d.m.Y H:i:s')
                         ->rangeDelimiter(' — ', '|') // отображение " — ", value "|"
                         ->includeEndOfDay(true)
                         ->render(),
                 ]),

                 Divider::make(),

                 // 19. Custom styling with MoonShine icons
                 Box::make('19. Custom Styling - Кастомные стили с иконками MoonShine', [
                     OverlayCalendar::make('custom_styled', 'Кастомный стиль')
                         ->addClass('w-80 border-2 border-blue-500 rounded-lg shadow-md')
                         ->addStyles('padding: 12px; background: linear-gradient(135deg, #667eea 0%,rgb(199, 183, 214) 100%); color: white;')
                         ->icon('academic-cap')
                         ->placeholder('Выберите дату с красивым стилем')
                         ->render(),
                 ]),

                 Divider::make(),

                 // 20. MoonShine calendar icon
                 Box::make('20. MoonShine Calendar Icon - Иконка календаря MoonShine', [
                     OverlayCalendar::make('moonshine_icon', 'Иконка MoonShine')
                         ->addClass('border border-gray-300 rounded-md hover:border-gray-400 transition-colors')
                         ->icon('arrow-left-on-rectangle')
                         ->placeholder('Календарь с иконкой MoonShine')
                         ->render(),
                 ]),

                 Divider::make(),

                 // 21. Colored icons examples
                 Box::make('21. Colored Icons - Цветные иконки', [
                     OverlayCalendar::make('colored_icon_1', 'Фиолетовая иконка')
                         ->addClass('border border-gray-300 rounded-md mb-3')
                         ->icon('calendar-days', '#8b5cf6')
                         ->placeholder('Фиолетовая иконка календаря')
                         ->render(),

                     OverlayCalendar::make('colored_icon_2', 'Красная иконка')
                         ->addClass('border border-gray-300 rounded-md mb-3')
                         ->icon('clock', '#ef4444')
                         ->placeholder('Красная иконка часов')
                         ->render(),

                     OverlayCalendar::make('colored_icon_3', 'Зеленая иконка')
                         ->addClass('border border-gray-300 rounded-md')
                         ->icon('academic-cap', '#10b981')
                         ->placeholder('Зеленая академическая шапка')
                         ->render(),
                 ]),

        ];
	}
}
