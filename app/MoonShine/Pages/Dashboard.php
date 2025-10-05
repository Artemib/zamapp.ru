<?php

declare(strict_types=1);

namespace App\MoonShine\Pages;

use App\Models\Call;
use App\Models\Contact;
use App\Models\Order;
use MoonShine\Laravel\Pages\Page;
use MoonShine\UI\Components\Layout\Box;
use MoonShine\UI\Fields\Text;
use MoonShine\Support\Attributes\Icon;
use MoonShine\MenuManager\Attributes\Group;
use MoonShine\MenuManager\Attributes\Order as MenuOrder;

#[Icon('chart-bar')]
#[Group('')]
class Dashboard extends Page
{
    protected string $title = 'Дашборд CRM';

    protected ?string $alias = 'dashboard';

    public function components(): array
    {
        $totalCalls = Call::count();
        $successfulCalls = Call::where('status', 'success')->count();
        $missedCalls = Call::where('status', 'missed')->count();
        $totalContacts = Contact::count();
        $activeOrders = Order::where('status', 'work')->count();
        $completedOrders = Order::where('status', 'completed')->count();

        return [
            Box::make('Статистика CRM', [
                '<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; padding: 20px;">',
                '<div style="background: #f8f9fa; padding: 20px; border-radius: 8px; text-align: center;">',
                '<h3 style="margin: 0 0 10px 0; color: #333;">Всего звонков</h3>',
                '<div style="font-size: 2em; font-weight: bold; color: #007bff;">' . $totalCalls . '</div>',
                '</div>',
                '<div style="background: #f8f9fa; padding: 20px; border-radius: 8px; text-align: center;">',
                '<h3 style="margin: 0 0 10px 0; color: #333;">Успешные звонки</h3>',
                '<div style="font-size: 2em; font-weight: bold; color: #28a745;">' . $successfulCalls . '</div>',
                '</div>',
                '<div style="background: #f8f9fa; padding: 20px; border-radius: 8px; text-align: center;">',
                '<h3 style="margin: 0 0 10px 0; color: #333;">Пропущенные звонки</h3>',
                '<div style="font-size: 2em; font-weight: bold; color: #dc3545;">' . $missedCalls . '</div>',
                '</div>',
                '<div style="background: #f8f9fa; padding: 20px; border-radius: 8px; text-align: center;">',
                '<h3 style="margin: 0 0 10px 0; color: #333;">Всего контактов</h3>',
                '<div style="font-size: 2em; font-weight: bold; color: #6f42c1;">' . $totalContacts . '</div>',
                '</div>',
                '<div style="background: #f8f9fa; padding: 20px; border-radius: 8px; text-align: center;">',
                '<h3 style="margin: 0 0 10px 0; color: #333;">Активные заказы</h3>',
                '<div style="font-size: 2em; font-weight: bold; color: #ffc107;">' . $activeOrders . '</div>',
                '</div>',
                '<div style="background: #f8f9fa; padding: 20px; border-radius: 8px; text-align: center;">',
                '<h3 style="margin: 0 0 10px 0; color: #333;">Завершенные заказы</h3>',
                '<div style="font-size: 2em; font-weight: bold; color: #17a2b8;">' . $completedOrders . '</div>',
                '</div>',
                '</div>',
            ]),
        ];
    }

}