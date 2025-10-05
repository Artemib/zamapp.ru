<?php

declare(strict_types=1);

namespace App\MoonShine\Resources;


use App\Models\Call;
use MoonShine\Laravel\Resources\ModelResource;
use MoonShine\Support\Enums\Color;
use MoonShine\Contracts\UI\FieldContract;
use MoonShine\Contracts\UI\ComponentContract;
use MoonShine\UI\Fields\Text;
use MoonShine\UI\Fields\Select;
use MoonShine\Support\Attributes\Icon;
use MoonShine\MenuManager\Attributes\Group;
use MoonShine\Laravel\QueryTags\QueryTag;
use Illuminate\Contracts\Database\Eloquent\Builder;
use MoonShine\Support\ListOf;
use MoonShine\Laravel\Enums\Action;
use MoonShine\UI\Components\ActionButton;
use App\Enums\CallConstants;



#[Icon('phone')]
#[Group('')]
/**
 * @extends ModelResource<Call>
 */
class CallResource extends ModelResource
{
    protected string $model = Call::class;

    protected string $title = 'Звонки';

    protected function modifyQueryBuilder(Builder $builder): Builder
    {
        /** @var \Illuminate\Database\Eloquent\Builder $builder */
        // Получаем московское время и конвертируем в UTC для сравнения с БД
        $moscowTodayStart = now('Europe/Moscow')->startOfDay()->utc();
        $moscowNow = now('Europe/Moscow')->utc();

        return $builder
            ->where('datetime', '>=', $moscowTodayStart)
            ->where('datetime', '<=', $moscowNow);
    }
    protected function activeActions(): ListOf
    {
        return parent::activeActions()
            ->only();
    }

    protected function topButtons(): ListOf
    {
        $now = 'Переуд '. format_date_custom(now(), true, 'd MMM yyyy') . ' - ' . format_date_custom(now(), true, 'd MMM yyyy'); 


        return parent::topButtons()->add(
            ActionButton::make($now, '#')->icon('calendar-days'),
            // ActionButton::make('Refresh 2', '#'),
        );
    }

    protected function indexButtons(): ListOf
    {
        return parent::indexButtons()
            ->prepend(
                ActionButton::make('',fn(Call $item) => '/endpoint?id=' . $item->link_record_pbx)->icon('play'),
                // ActionButton::make('Button 2', '/')
                //     ->showInDropdown(),
        );
    }


    /**
     * @return list<\MoonShine\Contracts\UI\FieldContract>
     */
    protected function filters(): iterable
    {
        return [
            Select::make('Статус звонка', 'status')
                ->options(CallConstants::STATUSES)
                ->placeholder('Все')
                ->nullable(),
            
            Select::make('Тип звонка', 'type')
                ->options(CallConstants::TYPES)
                ->placeholder('Все')
                ->nullable(),
        ];
    }

    protected function queryTags(): array
    {
        $all = Call::count();
        $missed = Call::where('status', 'missed')->count();
        $in = Call::where('type', 'in')->count();
        $out = Call::where('type', 'out')->count();


        $result = [];

        if($all > 0) {
            $result[] = QueryTag::make('Все ' . $all, fn ($q) => $q)->default();
        }

        $result[] = QueryTag::make('Входящие ' . $in, fn ($q) => $q->where('type', 'in'))->icon('arrow-down-right');

        if($missed > 0) {
            $result[] = QueryTag::make('Пропущенные ' . $missed, fn ($q) => $q->where('status', 'missed'))->icon('x-mark');
        }

        $result[] = QueryTag::make('Исходящие ' . $out, fn ($q) => $q->where('type', 'out'))->icon('arrow-up-left');


        return $result;
    }
    
    /**
     * @return list<FieldContract>
     */
    protected function indexFields(): iterable
    {
        return [
            Text::make('Дата', 'datetime_formatted'),
            Text::make('Время', 'time_formatted'),
            Text::make('Тип', 'type_name'),
            Text::make('Телефон клиента', 'client_phone_name'),
            Text::make('Рекламный', 'diversion_phone_name'),
            Text::make('Ожидание', 'wait'),
            Text::make('Длительность', 'duration'),
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
                        'Не найден' => Color::GRAY
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
        return [];
    }

    /**
     * @return list<FieldContract>
     */
    protected function detailFields(): iterable
    {
        return [];
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
