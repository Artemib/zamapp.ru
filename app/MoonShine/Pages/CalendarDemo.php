<?php

declare(strict_types=1);

namespace App\MoonShine\Pages;

use MoonShine\Laravel\Pages\Page;
use MoonShine\Contracts\UI\ComponentContract;
use App\MoonShine\UI\Components\OverlayCalendar;
use MoonShine\UI\Components\Layout\Box;
use MoonShine\UI\Components\Layout\Divider;

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
            Box::make('Standard Calendar - Стандартный календарь', [
                OverlayCalendar::make('standard_calendar')->render(),
            ]),

            Divider::make(),
            

            Box::make('No Quick Periods - Без быстрых периодов', [
                OverlayCalendar::make('no_quick_periods')
                ->showQuickPeriods(false)
                ->render(),
            ]),

            Divider::make(),

            Box::make('Custom Quick Periods - Кастомные быстрые периоды', [
                OverlayCalendar::make('custom_quick_periods')
                ->quickPeriods(['today', 'yesterday', 'current_month'])
                ->render(),
            ]),

            Divider::make(),

            Box::make('Custom Colors - Кастомные цвета', [
                OverlayCalendar::make('custom_colors')
                ->primaryColor('#8b5cf6')
                ->rangeColor('#f59e0b')
                ->render(),
            ]),

            Divider::make(),


            Box::make('No Selected Period Display - Без отображения выбранного периода', [
                OverlayCalendar::make('no_selected_period_display')
                ->showSelectedPeriod(false)
                ->render(),
            ]),

            Divider::make(),

            Box::make('Months in Numbers - Месяцы цифрами', [
                OverlayCalendar::make('months_in_numbers')
                ->showMonthNames(false)
                ->render(),
            ]),

            Divider::make(),

            Box::make('Custom Quick Title - Кастомный заголовок быстрых периодов', [

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

            Box::make('Max Width 500px - Максимальная ширина 500px', [
                OverlayCalendar::make('max_width')
                ->maxWidth('500px')
                ->render(),
            ]),

            Divider::make(),

            Box::make('Max Height 40vh - Максимальная высота 40vh', [
                OverlayCalendar::make('max_height')
                ->showQuickPeriods(false)
                ->maxHeight('40vh')
                ->render(),
            ]),

            Divider::make(),

            

        ];
	}
}
