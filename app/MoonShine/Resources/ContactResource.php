<?php

declare(strict_types=1);

namespace App\MoonShine\Resources;

use App\Models\Contact;
use App\MoonShine\Resources\OrderResource;
use MoonShine\Laravel\Resources\ModelResource;
use MoonShine\Support\Enums\Color;
use MoonShine\Support\Attributes\Icon;
use MoonShine\UI\Components\Layout\Box;
use MoonShine\UI\Fields\ID;
use MoonShine\UI\Fields\Text;
use MoonShine\UI\Fields\Select;
use MoonShine\UI\Fields\Textarea;
use MoonShine\UI\Fields\Switcher;
use MoonShine\Laravel\Fields\Relationships\BelongsToMany;
use MoonShine\Contracts\UI\FieldContract;
use MoonShine\Contracts\UI\ComponentContract;
use MoonShine\MenuManager\Attributes\Group;
use MoonShine\MenuManager\Attributes\Order as MenuOrder;

#[Icon('users')]
#[Group('')]
/**
 * @extends ModelResource<Contact>
 */
class ContactResource extends ModelResource
{
    protected string $model = Contact::class;

    protected string $title = 'Контакты';

    /**
     * @return list<\MoonShine\Contracts\UI\FieldContract>
     */
    protected function filters(): iterable
    {
        return [
            Select::make('Тип контакта', 'type')
                ->options([
                    'phone' => 'Телефон',
                    'telegram' => 'Telegram',
                    'whatsapp' => 'WhatsApp',
                    'email' => 'Email',
                    'other' => 'Другое',
                ])
                ->nullable(),
            
            Select::make('Источник контакта', 'source')
                ->options([
                    'auto' => 'Автоматически (из звонка)',
                    'manual' => 'Вручную',
                    'import' => 'Импорт',
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
            Text::make('Тип', 'type')
                ->badge(function ($value) {
                    return match ($value) {
                        'phone' => Color::BLUE,
                        'telegram' => Color::BLUE,
                        'whatsapp' => Color::GREEN,
                        'email' => Color::PURPLE,
                        'other' => Color::GRAY,
                        default => Color::GRAY,
                    };
                }),
            Text::make('Значение', 'value'),
            Text::make('Источник', 'source')
                ->badge(function ($value) {
                    return match ($value) {
                        'auto' => Color::GREEN,
                        'manual' => Color::BLUE,
                        'import' => Color::YELLOW,
                        default => Color::GRAY,
                    };
                }),
            Text::make('Метка', 'label'),
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
                Select::make('Тип контакта', 'type')
                    ->options([
                        'phone' => 'Телефон',
                        'telegram' => 'Telegram',
                        'whatsapp' => 'WhatsApp',
                        'email' => 'Email',
                        'other' => 'Другое',
                    ])
                    ->required(),

                Text::make('Значение', 'value')
                    ->required()
                    ->placeholder('Номер телефона, никнейм, email и т.д.'),

                Select::make('Источник', 'source')
                    ->options([
                        'auto' => 'Автоматически (из звонка)',
                        'manual' => 'Вручную',
                        'import' => 'Импорт',
                    ])
                    ->required(),

                Text::make('Метка', 'label')
                    ->placeholder('Дополнительное описание контакта'),
            ]),

        ];
    }

    /**
     * @return list<FieldContract>
     */
    protected function detailFields(): iterable
    {
        return [
            Text::make('Тип', 'type'),
            Text::make('Значение', 'value'),
            Text::make('Источник', 'source'),
            Text::make('Метка', 'label'),
            Text::make('Создан', 'created_at')
,
            Text::make('Обновлен', 'updated_at')
,
        ];
    }

    /**
     * @param Contact $item
     *
     * @return array<string, string[]|string>
     */
    protected function rules(mixed $item): array
    {
        return [
            'type' => 'required|in:phone,telegram,whatsapp,email,other',
            'value' => 'required|string|max:255',
            'source' => 'required|in:auto,manual,import',
            'label' => 'nullable|string|max:255',
        ];
    }
}
