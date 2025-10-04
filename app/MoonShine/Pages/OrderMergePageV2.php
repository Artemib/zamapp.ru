<?php

declare(strict_types=1);

namespace App\MoonShine\Pages;

use App\Models\Order;
use MoonShine\Laravel\Pages\Page;
use Illuminate\Support\Facades\Log;
use MoonShine\UI\Fields\Select;
use MoonShine\UI\Fields\Checkbox;
use MoonShine\UI\Components\Layout\Box;
use MoonShine\UI\Components\Layout\Div;
use MoonShine\UI\Components\ActionButton;
use MoonShine\UI\Fields\Hidden;
use MoonShine\UI\Fields\Text;
use MoonShine\UI\Fields\Textarea;
use MoonShine\UI\Components\Layout\Grid;
use MoonShine\UI\Components\Layout\Column;
use MoonShine\UI\Components\Layout\Row;
use MoonShine\UI\Components\Modal;
use MoonShine\UI\Components\Button;
use MoonShine\UI\Components\Layout\Block;
use MoonShine\UI\Components\Layout\Separator;

#[\MoonShine\MenuManager\Attributes\SkipMenu]

class OrderMergePageV2 extends Page
{
    protected string $title = 'Объединение заказов (V2)';
    protected ?string $alias = 'order-merge-v2';

    protected function components(): array
    {
        return [
            Box::make('🔄 Объединение заказов', [
                Div::make([
                    '<form method="POST" action="/admin/page/order-merge-v2" style="padding: 20px;">
                        ' . csrf_field() . '
                        
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
                            <div>
                                <label style="display: block; margin-bottom: 8px; font-weight: 500; color: #495057;">Исходный заказ (будет сохранен):</label>
                                <select name="source_order_id" required style="width: 100%; padding: 10px; border: 1px solid #ced4da; border-radius: 4px; font-size: 14px;">
                                    <option value="">Выберите заказ...</option>
                                    ' . $this->getOrderOptionsHtml() . '
                                </select>
                            </div>
                            <div>
                                <label style="display: block; margin-bottom: 8px; font-weight: 500; color: #495057;">Заказ для объединения (будет удален):</label>
                                <select name="target_order_id" required style="width: 100%; padding: 10px; border: 1px solid #ced4da; border-radius: 4px; font-size: 14px;">
                                    <option value="">Выберите заказ...</option>
                                    ' . $this->getOrderOptionsHtml() . '
                                </select>
                            </div>
                        </div>
                        
                        <div style="margin: 20px 0; padding: 15px; background: #f8f9fa; border-radius: 6px; border-left: 4px solid #007bff;">
                            <h5 style="margin: 0 0 10px 0; color: #495057;">⚙️ Выберите поля для переноса:</h5>
                            <p style="margin: 0; color: #6c757d; font-size: 14px;">Отметьте поля, которые нужно перенести из второго заказа в первый</p>
                        </div>
                        
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
                            <div style="display: flex; align-items: center; padding: 10px; border: 1px solid #dee2e6; border-radius: 4px; background: #f8f9fa;">
                                <input type="checkbox" name="merge_city" value="1" checked style="margin-right: 10px; transform: scale(1.2);">
                                <label style="margin: 0; font-weight: 500;">Город</label>
                            </div>
                            <div style="display: flex; align-items: center; padding: 10px; border: 1px solid #dee2e6; border-radius: 4px; background: #f8f9fa;">
                                <input type="checkbox" name="merge_address" value="1" checked style="margin-right: 10px; transform: scale(1.2);">
                                <label style="margin: 0; font-weight: 500;">Адрес</label>
                            </div>
                        </div>
                        
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
                            <div style="display: flex; align-items: center; padding: 10px; border: 1px solid #dee2e6; border-radius: 4px; background: #f8f9fa;">
                                <input type="checkbox" name="merge_phone" value="1" checked style="margin-right: 10px; transform: scale(1.2);">
                                <label style="margin: 0; font-weight: 500;">Телефон</label>
                            </div>
                            <div style="display: flex; align-items: center; padding: 10px; border: 1px solid #dee2e6; border-radius: 4px; background: #f8f9fa;">
                                <input type="checkbox" name="merge_additional_info" value="1" checked style="margin-right: 10px; transform: scale(1.2);">
                                <label style="margin: 0; font-weight: 500;">Дополнительная информация</label>
                            </div>
                        </div>
                        
                        <div style="text-align: center; margin-top: 20px;">
                            <button type="submit" class="btn btn-primary" style="background: #007bff; color: white; padding: 12px 30px; border: none; border-radius: 6px; font-size: 16px; cursor: pointer;">
                                🔄 Объединить заказы
                            </button>
                        </div>
                    </form>'
                ])
            ])
        ];
    }

    private function getOrderOptions(): array
    {
        return Order::all()->mapWithKeys(function ($order) {
            return [$order->id => "Заказ #{$order->id} - {$order->order_datetime_formatted} - {$order->city}"];
        })->toArray();
    }

    private function getOrderOptionsHtml(): string
    {
        $html = '';
        foreach (Order::all() as $order) {
            $html .= '<option value="' . $order->id . '">Заказ #' . $order->id . ' - ' . $order->order_datetime_formatted . ' - ' . $order->city . '</option>';
        }
        return $html;
    }

    public function merge_orders()
    {
        Log::info('OrderMergePageV2::merge_orders called', [
            'source_order_id' => request('source_order_id'),
            'target_order_id' => request('target_order_id'),
            'merge_fields' => $this->getSelectedMergeFields(),
            'all_request' => request()->all()
        ]);
        
        $sourceOrderId = request('source_order_id');
        $targetOrderId = request('target_order_id');
        
        if (!$sourceOrderId || !$targetOrderId) {
            session()->flash('error', 'Выберите оба заказа для объединения');
            return;
        }
        
        if ($sourceOrderId === $targetOrderId) {
            session()->flash('error', 'Нельзя объединить заказ с самим собой');
            return;
        }
        
        $sourceOrder = Order::find($sourceOrderId);
        $targetOrder = Order::find($targetOrderId);
        
        if (!$sourceOrder || !$targetOrder) {
            session()->flash('error', 'Один из заказов не найден');
            return;
        }
        
        try {
            $mergeFields = $this->getSelectedMergeFields();
            $sourceOrder->mergeWith($targetOrder, $mergeFields);
            
            $fieldsText = empty($mergeFields) ? 'все доступные поля' : implode(', ', $mergeFields);
            
            session()->flash('success', "✅ Заказы успешно объединены! Заказ #{$targetOrderId} объединен с заказом #{$sourceOrderId}. Перенесены поля: {$fieldsText}. Заказ #{$targetOrderId} удален.");
            
        } catch (\Exception $e) {
            Log::error('Error merging orders: ' . $e->getMessage());
            session()->flash('error', 'Ошибка при объединении заказов: ' . $e->getMessage());
        }
        
        return redirect()->route('moonshine.page', ['pageUri' => 'order-merge-v2']);
    }

    private function getSelectedMergeFields(): array
    {
        $fields = [];
        
        if (request('merge_city')) $fields[] = 'city';
        if (request('merge_address')) $fields[] = 'address';
        if (request('merge_phone')) $fields[] = 'phone';
        if (request('merge_additional_info')) $fields[] = 'additional_info';
        
        return $fields;
    }

    public function getBreadcrumbs(): array
    {
        return [
            route('moonshine.crud.index', ['resourceUri' => 'orders']) => 'Заказы',
            '#' => $this->getTitle()
        ];
    }

    public function getTitle(): string
    {
        return $this->title ?: 'Объединение заказов (V2)';
    }
}
