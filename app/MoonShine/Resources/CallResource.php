<?php

declare(strict_types=1);

namespace App\MoonShine\Resources;


use App\Models\Call;

use MoonShine\Laravel\Resources\ModelResource;
use MoonShine\Support\Enums\Color;
use MoonShine\UI\Components\Layout\Box;
use MoonShine\UI\Fields\ID;
use MoonShine\Contracts\UI\FieldContract;
use MoonShine\Contracts\UI\ComponentContract;
use MoonShine\UI\Fields\Text;

/**
 * @extends ModelResource<Call>
 */
class CallResource extends ModelResource
{
    protected string $model = Call::class;

    protected string $title = 'Звонки';
    
    /**
     * @return list<FieldContract>
     */
    protected function indexFields(): iterable
    {
        return [
//            ID::make()->sortable(),
            Text::make('Дата и время', 'datetime_formatted'),
            Text::make('Тип', 'type_name'),
            Text::make('Телефон клиента', 'client_phone_name'),
            Text::make('Рекламный', 'diversion_phone_name'),
            Text::make('Пользователь ВАТС', 'user_pbx'),
            Text::make('Статус', 'status_name')
                ->badge(function ($value) {
                    // Делаем match с русскими названиями статусов
                    return match ($value) {
                        'Успешный' => Color::GREEN,
                        'Пропущенный' => Color::RED,
                        'Отменённый' => Color::WARNING,
                        'Занято' => Color::YELLOW,
                        'Недоступен' => Color::PURPLE,
                        'Запрещено' => Color::RED,
                        'Не найден' => Color::GRAY,
                        default => Color::GRAY,
                    };
                }),
            // Text::make('Запись Разговора', 'link_record_crm')->badge(Color::RED),
            // ID::make('callid', 'callid'),
        ];
    }

    /**
     * @return list<ComponentContract|FieldContract>
     */
    protected function formFields(): iterable
    {
        return [
            Box::make([
                ID::make(),
            ])
        ];
    }

    /**
     * @return list<FieldContract>
     */
    protected function detailFields(): iterable
    {
        return [
            Text::make('Дата и время', 'datetime_formatted'),
            Text::make('Тип', 'type_name'),
            Text::make('Телефон клиента', 'client_phone_name'),
            Text::make('Рекламный', 'diversion_phone_name'),
            Text::make('Статус', 'status_name'),
            Text::make('Запись Разговора', 'link_record_crm'),
        ];
    }

    /**
     * @param Call $item
     *
     * @return array<string, string[]|string>
     * @see https://laravel.com/docs/validation#available-validation-rules
     */
    protected function rules(mixed $item): array
    {
        return [];
    }
}
