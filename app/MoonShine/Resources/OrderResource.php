<?php

declare(strict_types=1);

namespace App\MoonShine\Resources;

use App\Models\Order;
use App\Models\Call;
use MoonShine\Laravel\Resources\ModelResource;
use MoonShine\Support\Enums\Color;
use MoonShine\UI\Components\Layout\Box;
use MoonShine\UI\Fields\ID;
use MoonShine\UI\Fields\Text;
use MoonShine\UI\Fields\Textarea;
use MoonShine\UI\Fields\Date;
use MoonShine\UI\Fields\Select;

class OrderResource extends ModelResource
{
    protected string $model = Order::class;
    protected string $title = 'Заказы';
    protected string $column = 'id';
    protected bool $withTrashed = true;

    protected function indexFields(): iterable
    {
        return [
            ID::make()->sortable(),
            Text::make('Дата заказа', 'order_datetime_formatted'),
            Text::make('Город', 'city'),
            Text::make('Адрес', 'address'),
            Text::make('Телефон', 'phone'),
            Text::make('Главный звонок', 'mainCall.datetime_formatted'),
        ];
    }

    protected function formFields(): iterable
    {
        return [
            Box::make([
                Date::make('Дата и время заказа', 'order_datetime')
                    ->withTime()
                    ->required(),
                Text::make('Город', 'city'),
                Textarea::make('Адрес', 'address'),
                Text::make('Телефон', 'phone'),
                Textarea::make('Дополнительная информация', 'additional_info'),
                Select::make('Главный звонок', 'main_call_id')
                    ->options(Call::all()->pluck('datetime_formatted', 'id')->toArray())
                    ->nullable(),
            ])
        ];
    }

    protected function detailFields(): iterable
    {
        return [
            Text::make('Дата заказа', 'order_datetime_formatted'),
            Text::make('Город', 'city'),
            Text::make('Адрес', 'address'),
            Text::make('Телефон', 'phone'),
            Text::make('Дополнительная информация', 'additional_info'),
            Text::make('Главный звонок', 'mainCall.datetime_formatted'),
        ];
    }



    protected function rules(mixed $item): array
    {
        return [
            'order_datetime' => 'required|date',
            'city' => 'nullable|string|max:255',
            'address' => 'nullable|string',
            'phone' => 'nullable|string|max:20',
            'additional_info' => 'nullable|string',
            'main_call_id' => 'nullable|exists:calls,id',
        ];
    }
}
