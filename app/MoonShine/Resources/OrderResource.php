<?php

declare(strict_types=1);

namespace App\MoonShine\Resources;

use App\Models\Order;
use App\Models\Call;
use App\Models\Contact;
use App\MoonShine\Resources\CallResource;
use App\MoonShine\Resources\ContactResource;
use MoonShine\Laravel\Resources\ModelResource;
use MoonShine\Support\Enums\Color;
use MoonShine\Support\Attributes\Icon;
use MoonShine\UI\Components\Layout\Box;
use MoonShine\UI\Fields\ID;
use MoonShine\UI\Fields\Text;
use MoonShine\UI\Fields\Select;
use MoonShine\UI\Fields\Textarea;
use MoonShine\Laravel\Fields\Relationships\BelongsTo;
use MoonShine\Laravel\Fields\Relationships\BelongsToMany;
use MoonShine\Contracts\UI\FieldContract;
use MoonShine\Contracts\UI\ComponentContract;
use MoonShine\MenuManager\Attributes\Group;
use MoonShine\MenuManager\Attributes\Order as MenuOrder;

#[Icon('shopping-cart')]
#[Group('')]
/**
 * @extends ModelResource<Order>
 */
class OrderResource extends ModelResource
{
    protected string $model = Order::class;

    protected string $title = 'Заказы';

    /**
     * @return list<\MoonShine\Contracts\UI\FieldContract>
     */
    protected function filters(): iterable
    {
        return [
            Select::make('Статус заказа', 'status')
                ->options([
                    'work' => 'В работе',
                    'completed' => 'Завершен',
                    'cancelled' => 'Отменен',
                    'pending' => 'Ожидает',
                ])
                ->nullable(),
        ];
    }

    /**
     * @return list<FieldContract>
     */
    protected function indexFields(): iterable
    {
        return [
            ID::make()->sortable(),
            Text::make('Статус', 'status')
                ->badge(function ($value) {
                    return match ($value) {
                        'work' => Color::BLUE,
                        'completed' => Color::GREEN,
                        'cancelled' => Color::RED,
                        'pending' => Color::YELLOW,
                        default => Color::GRAY,
                    };
                }),
            Text::make('Главный звонок', 'mainCall.client_phone')
                ->nullable(),
            Text::make('Основной контакт', 'primary_contact'),
            Text::make('Количество звонков', 'calls_count'),
            Text::make('Количество контактов', 'contacts_count'),
            Text::make('Создан', 'created_at')
,
        ];
    }

    /**
     * @return list<ComponentContract|FieldContract>
     */
    protected function formFields(): iterable
    {
        return [
            Box::make([
                Select::make('Статус', 'status')
                    ->options([
                        'work' => 'В работе',
                        'completed' => 'Завершен',
                        'cancelled' => 'Отменен',
                        'pending' => 'Ожидает',
                    ])
                    ->required(),

                Select::make('Главный звонок', 'main_call_id')
                    ->options(function ($item) {
                        // Получаем ID звонков, для которых уже созданы заказы
                        $usedCallIds = Order::whereNotNull('main_call_id')
                            ->when($item && isset($item->id), function ($query) use ($item) {
                                // При редактировании исключаем текущий заказ из списка занятых звонков
                                return $query->where('id', '!=', $item->id);
                            })
                            ->pluck('main_call_id')
                            ->toArray();
                        
                        // Получаем только те звонки, для которых еще нет заказов
                        return Call::whereNotIn('id', $usedCallIds)
                            ->orderBy('datetime', 'desc')
                            ->get()
                            ->mapWithKeys(function ($call) {
                                return [$call->id => $call->datetime . ' - ' . $call->client_phone];
                            })
                            ->toArray();
                    })
                    ->nullable()
                    ->searchable(),

            ]),

        ];
    }

    /**
     * @return list<FieldContract>
     */
    protected function detailFields(): iterable
    {
        return [
            Text::make('Статус', 'status'),
            Text::make('Главный звонок', 'mainCall.client_phone')
                ->nullable(),
            Text::make('Создан', 'created_at')
,
            Text::make('Обновлен', 'updated_at')
,
        ];
    }

    /**
     * @param Order $item
     *
     * @return array<string, string[]|string>
     */
    protected function rules(mixed $item): array
    {
        return [
            'status' => 'required|in:work,completed,cancelled,pending',
            'main_call_id' => 'nullable|exists:calls,id',
        ];
    }
}
