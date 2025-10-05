<?php

declare(strict_types=1);

namespace App\MoonShine\Pages;

use MoonShine\Laravel\Pages\Page;
use MoonShine\Contracts\UI\ComponentContract;
use MoonShine\UI\Components\Table\TableBuilder;
use MoonShine\UI\Fields\ID;
use MoonShine\UI\Fields\Text;
use App\Models\Call;
use MoonShine\Support\Enums\Color;
use MoonShine\UI\Fields\Date;
use MoonShine\UI\Components\ActionButton;
use MoonShine\UI\Components\Heading;
use MoonShine\UI\Components\FormBuilder;
use MoonShine\Support\Enums\FormMethod;
use MoonShine\UI\Components\Modal;



class Calls extends Page
{
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
        return $this->title ?: 'Звонки';
    }

    /**
     * @return list<ComponentContract>
     */
    protected function components(): iterable
	{


        $from = request('from') ? now()->parse(request('from'))->startOfDay() : now()->startOfDay();
        $to = request('to') ? now()->parse(request('to'))->endOfDay() : now()->endOfDay();

        $todayFrom = now()->toDateString();
        $todayTo = $todayFrom;
        $yesterday = now()->subDay()->toDateString();
        $weekFrom = now()->subDays(6)->toDateString();
        $weekTo = now()->toDateString();
        $monthFrom = now()->startOfMonth()->toDateString();
        $monthTo = now()->endOfMonth()->toDateString();

        $isActive = function (string $f, string $t): bool {
            return request('from') === $f && request('to') === $t;
        };

        $headingText = $from->isSameDay($to)
            ? format_date_custom($from, true, 'd MMM yyyy HH:mm', 'UTC')
            : format_date_custom($from, true, 'd MMM yyyy HH:mm', 'UTC') . ' — ' . format_date_custom($to, true, 'd MMM yyyy HH:mm', 'UTC');

        return [
            Heading::make($headingText, 2),
            // Быстрые фильтры по периодам с подсветкой активного
            ActionButton::make('Сегодня', fn() => url()->current())
                ->when(
                    (is_null(request('from')) && is_null(request('to'))) || $isActive($todayFrom, $todayTo),
                    fn($b) => $b->primary()
                ),
            ActionButton::make('Вчера', fn() => url()->current() . '?from=' . $yesterday . '&to=' . $yesterday)
                ->when($isActive($yesterday, $yesterday), fn($b) => $b->primary()),
            ActionButton::make('7 дней', fn() => url()->current() . '?from=' . $weekFrom . '&to=' . $weekTo)
                ->when($isActive($weekFrom, $weekTo), fn($b) => $b->primary()),

            ActionButton::make('Месяц', fn() => url()->current() . '?from=' . $monthFrom . '&to=' . $monthTo)
                ->when($isActive($monthFrom, $monthTo), fn($b) => $b->primary()),

            // Кнопка-обёртка и модальное окно выбора произвольного периода
            Modal::make(
                title: 'Выбор периода',
                content: '',
                outer: ActionButton::make('Выбрать период', '#'),
                components: [
                    FormBuilder::make()
                        ->method(FormMethod::GET)
                        ->action(url()->current())
                        ->fields([
                            Date::make('С', 'from')->default($from->toDateString()),
                            Date::make('По', 'to')->default($to->toDateString()),
                        ])
                        ->submit('Применить'),
                ]
            ),

			TableBuilder::make()
				->fields([
                    Text::make('Дата и время', 'datetime_formatted'),
					Text::make('Время', 'TimeFormatted')->sortable(),
					Text::make('Тип', 'type_name'),
					Text::make('Статус', 'status_name')
						->badge(function ($value) {
							return match ($value) {
								'Успешный' => Color::GREEN,
								'Пропущенный' => Color::RED,
								'Отменённый' => Color::WARNING,
								'Занято' => Color::YELLOW,
								'Недоступен' => Color::PURPLE,
								'Запрещено' => Color::RED,
								'Не найден' => Color::GRAY,
							};
						}),
					Text::make('Телефон клиента', 'client_phone'),
					Text::make('Рекламный', 'diversion_phone'),
					// Text::make('Запись Разговора', 'link_record_pbx'),
				])
                ->items(
                    Call::query()
                        ->whereBetween('datetime', [$from, $to])
                        ->orderByDesc('datetime')
                        ->get()
                )
		];
	}
}
