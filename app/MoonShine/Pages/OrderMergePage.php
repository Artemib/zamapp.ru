<?php

declare(strict_types=1);

namespace App\MoonShine\Pages;

use App\Models\Order;
use MoonShine\Laravel\Pages\Page;
use Illuminate\Support\Facades\Log;
use MoonShine\UI\Fields\Select;
use MoonShine\UI\Fields\Text;
use MoonShine\UI\Components\Layout\Box;
use MoonShine\UI\Components\TableBuilder;
use MoonShine\UI\Fields\ID;
use MoonShine\UI\Fields\Hidden;
use MoonShine\UI\Components\Layout\Div;
use MoonShine\UI\Components\ActionButton;
use MoonShine\UI\Fields\Checkbox;
#[\MoonShine\MenuManager\Attributes\SkipMenu]

class OrderMergePage extends Page
{
    protected string $title = 'Объединение заказов';
    protected ?string $alias = 'order-merge';

    protected function components(): array
    {
        return [
            Box::make('Объединение заказов', [
                Div::make([
                    '<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; align-items: end;">
                        <div>
                            <label style="display: block; margin-bottom: 8px; font-weight: 500; color: #495057;">Исходный заказ (будет сохранен):</label>
                            <select name="source_order_id" id="source_order_id" style="width: 100%; padding: 10px; border: 1px solid #ced4da; border-radius: 4px; font-size: 14px;" onchange="updateTargetOptions()">
                                <option value="">Выберите заказ...</option>
                            </select>
                        </div>
                        <div>
                            <label style="display: block; margin-bottom: 8px; font-weight: 500; color: #495057;">Заказ для объединения (будет удален):</label>
                            <select name="target_order_id" id="target_order_id" style="width: 100%; padding: 10px; border: 1px solid #ced4da; border-radius: 4px; font-size: 14px;" onchange="updateSourceOptions()">
                                <option value="">Выберите заказ...</option>
                            </select>
                        </div>
                    </div>'
                ])
            ]),
            
            Box::make('Действия', [
                Div::make([
                    "<button type=\"button\" onclick=\"showMergeModal()\" class=\"btn btn-primary\" style=\"background: #3b82f6; color: white; padding: 12px 24px; border: none; border-radius: 6px; cursor: pointer; font-size: 16px;\">
                        🔄 Объединить заказы
                    </button>
                    
                    <!-- Модальное окно -->
                    <div id=\"mergeModal\" class=\"modal\" style=\"display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.5);\">
                        <div class=\"modal-content\" style=\"background-color: #fefefe; margin: 2% auto; padding: 0; border: none; border-radius: 8px; width: 90%; max-width: 1000px; box-shadow: 0 4px 20px rgba(0,0,0,0.3);\">
                            <div class=\"modal-header\" style=\"padding: 20px; border-bottom: 1px solid #dee2e6; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border-radius: 8px 8px 0 0;\">
                                <h3 style=\"margin: 0; font-size: 20px;\">🔄 Объединение заказов</h3>
                                <span class=\"close\" onclick=\"closeMergeModal()\" style=\"color: white; float: right; font-size: 28px; font-weight: bold; cursor: pointer; line-height: 1;\">&times;</span>
                            </div>
                            <div class=\"modal-body\" style=\"padding: 20px; max-height: 75vh; overflow-y: auto;\">
                                <div style=\"display: grid; grid-template-columns: 1fr 1fr; gap: 20px;\">
                                    <!-- Левая колонка - Предварительный просмотр -->
                                    <div id=\"preview-data\" style=\"margin-bottom: 20px;\">
                                        <h5 style=\"color: #495057; margin-bottom: 15px; font-size: 16px;\">📋 Предварительный просмотр изменений:</h5>
                                        <div id=\"preview-content\" style=\"min-height: 300px;\"></div>
                                    </div>
                                    
                                    <!-- Правая колонка - Настройки -->
                                    <div style=\"margin: 20px 0; padding: 15px; background: #f8f9fa; border-radius: 6px; border-left: 4px solid #007bff; height: fit-content;\">
                                        <h5 style=\"margin: 0 0 15px 0; color: #495057; font-size: 16px;\">⚙️ Выберите поля для переноса:</h5>
                                        <div style=\"display: flex; flex-direction: column; gap: 12px;\">
                                            <label class=\"field-checkbox\" style=\"display: flex; align-items: center; cursor: pointer; padding: 12px; border-radius: 6px; transition: all 0.3s ease; border: 2px solid transparent;\">
                                                <input type=\"checkbox\" id=\"merge_city\" checked onchange=\"toggleField('city')\" style=\"margin-right: 12px; transform: scale(1.3);\"> 
                                                <span style=\"font-size: 14px; font-weight: 500;\">🏙️ Город</span>
                                            </label>
                                            <label class=\"field-checkbox\" style=\"display: flex; align-items: center; cursor: pointer; padding: 12px; border-radius: 6px; transition: all 0.3s ease; border: 2px solid transparent;\">
                                                <input type=\"checkbox\" id=\"merge_address\" checked onchange=\"toggleField('address')\" style=\"margin-right: 12px; transform: scale(1.3);\"> 
                                                <span style=\"font-size: 14px; font-weight: 500;\">📍 Адрес</span>
                                            </label>
                                            <label class=\"field-checkbox\" style=\"display: flex; align-items: center; cursor: pointer; padding: 12px; border-radius: 6px; transition: all 0.3s ease; border: 2px solid transparent;\">
                                                <input type=\"checkbox\" id=\"merge_phone\" checked onchange=\"toggleField('phone')\" style=\"margin-right: 12px; transform: scale(1.3);\"> 
                                                <span style=\"font-size: 14px; font-weight: 500;\">📞 Телефон</span>
                                            </label>
                                            <label class=\"field-checkbox\" style=\"display: flex; align-items: center; cursor: pointer; padding: 12px; border-radius: 6px; transition: all 0.3s ease; border: 2px solid transparent;\">
                                                <input type=\"checkbox\" id=\"merge_additional_info\" checked onchange=\"toggleField('additional_info')\" style=\"margin-right: 12px; transform: scale(1.3);\"> 
                                                <span style=\"font-size: 14px; font-weight: 500;\">📝 Дополнительная информация</span>
                                            </label>
                                        </div>
                                        
                                        <div style=\"margin-top: 20px; padding: 15px; background: #fff3cd; border-radius: 6px; border-left: 4px solid #ffc107;\">
                                            <h6 style=\"margin: 0 0 8px 0; color: #856404; font-size: 14px;\">💡 Подсказка:</h6>
                                            <p style=\"margin: 0; color: #856404; font-size: 12px; line-height: 1.4;\">Снимите галочку с поля, чтобы оно не участвовало в слиянии. Поле станет серым и неактивным.</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class=\"modal-footer\" style=\"padding: 20px; border-top: 1px solid #dee2e6; background: #f8f9fa; border-radius: 0 0 8px 8px; text-align: right;\">
                                <button type=\"button\" onclick=\"closeMergeModal()\" class=\"btn btn-secondary\" style=\"background: #6c757d; color: white; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer; margin-right: 10px;\">
                                    ❌ Отмена
                                </button>
                                <button type=\"button\" onclick=\"confirmMerge()\" class=\"btn btn-success\" style=\"background: #28a745; color: white; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer;\">
                                    ✅ Подтвердить слияние
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <style>
                        .field-checkbox:hover {
                            background-color: #e9ecef !important;
                        }
                        .field-checkbox.disabled {
                            opacity: 0.5;
                            background-color: #f8f9fa !important;
                            border-color: #dee2e6 !important;
                        }
                        .field-checkbox.disabled span {
                            color: #6c757d !important;
                        }
                        .field-checkbox.disabled input[type=\"checkbox\"] {
                            opacity: 0.5;
                        }
                        .field-preview {
                            transition: all 0.3s ease;
                            opacity: 1;
                        }
                        .field-preview.disabled {
                            opacity: 0.4;
                            background-color: #f8f9fa !important;
                            border-color: #dee2e6 !important;
                        }
                        .field-preview.disabled .field-label {
                            color: #6c757d !important;
                        }
                        .field-preview.disabled .field-current,
                        .field-preview.disabled .field-new {
                            color: #6c757d !important;
                        }
                        .modal-content {
                            animation: modalSlideIn 0.3s ease-out;
                        }
                        @keyframes modalSlideIn {
                            from {
                                opacity: 0;
                                transform: translateY(-50px);
                            }
                            to {
                                opacity: 1;
                                transform: translateY(0);
                            }
                        }
                    </style>
                    
                    <script>
                        let sourceOrderData = null;
                        let targetOrderData = null;
                        let allOrders = [];
                        
                        // Загружаем данные заказов при загрузке страницы
                        document.addEventListener('DOMContentLoaded', function() {
                            loadOrdersData();
                        });
                        
                        function loadOrdersData() {
                            const sourceSelect = document.getElementById('source_order_id');
                            const targetSelect = document.getElementById('target_order_id');
                            
                            // Показываем индикатор загрузки
                            sourceSelect.innerHTML = '<option value=\"\">⏳ Загружаем заказы...</option>';
                            targetSelect.innerHTML = '<option value=\"\">⏳ Загружаем заказы...</option>';
                            
                            fetch('/admin/page/order-merge/get-orders-list', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': document.querySelector('meta[name=\"csrf-token\"]').getAttribute('content')
                                }
                            })
                            .then(response => response.json())
                            .then(data => {
                                if (data.success) {
                                    allOrders = data.orders;
                                    populateSelects();
                                } else {
                                    sourceSelect.innerHTML = '<option value=\"\">❌ Ошибка загрузки</option>';
                                    targetSelect.innerHTML = '<option value=\"\">❌ Ошибка загрузки</option>';
                                }
                            })
                            .catch(error => {
                                console.error('Error loading orders:', error);
                                sourceSelect.innerHTML = '<option value=\"\">❌ Ошибка загрузки</option>';
                                targetSelect.innerHTML = '<option value=\"\">❌ Ошибка загрузки</option>';
                            });
                        }
                        
                        function populateSelects() {
                            const sourceSelect = document.getElementById('source_order_id');
                            const targetSelect = document.getElementById('target_order_id');
                            
                            // Очищаем селекты
                            sourceSelect.innerHTML = '<option value=\"\">Выберите заказ...</option>';
                            targetSelect.innerHTML = '<option value=\"\">Выберите заказ...</option>';
                            
                            // Заполняем опциями
                            allOrders.forEach(function(order) {
                                const optionText = 'Заказ #' + order.id + ' - ' + order.order_datetime_formatted + ' - ' + order.city;
                                
                                const sourceOption = new Option(optionText, order.id);
                                const targetOption = new Option(optionText, order.id);
                                
                                sourceSelect.add(sourceOption);
                                targetSelect.add(targetOption);
                            });
                        }
                        
                        function updateTargetOptions() {
                            const sourceSelect = document.getElementById('source_order_id');
                            const targetSelect = document.getElementById('target_order_id');
                            const selectedSourceId = sourceSelect.value;
                            const currentTargetValue = targetSelect.value;
                            
                            // Очищаем целевой селект
                            targetSelect.innerHTML = '<option value=\"\">Выберите заказ...</option>';
                            
                            // Заполняем опциями, исключая выбранный в исходном
                            allOrders.forEach(function(order) {
                                if (order.id != selectedSourceId) {
                                    const optionText = 'Заказ #' + order.id + ' - ' + order.order_datetime_formatted + ' - ' + order.city;
                                    const option = new Option(optionText, order.id);
                                    targetSelect.add(option);
                                }
                            });
                            
                            // Если в целевом селекте был выбран тот же заказ, что и в исходном, сбрасываем его
                            if (currentTargetValue == selectedSourceId) {
                                targetSelect.value = '';
                            } else if (currentTargetValue && currentTargetValue != selectedSourceId) {
                                // Восстанавливаем предыдущее значение, если оно не конфликтует
                                targetSelect.value = currentTargetValue;
                            }
                        }
                        
                        function updateSourceOptions() {
                            const sourceSelect = document.getElementById('source_order_id');
                            const targetSelect = document.getElementById('target_order_id');
                            const selectedTargetId = targetSelect.value;
                            const currentSourceValue = sourceSelect.value;
                            
                            // Очищаем исходный селект
                            sourceSelect.innerHTML = '<option value=\"\">Выберите заказ...</option>';
                            
                            // Заполняем опциями, исключая выбранный в целевом
                            allOrders.forEach(function(order) {
                                if (order.id != selectedTargetId) {
                                    const optionText = 'Заказ #' + order.id + ' - ' + order.order_datetime_formatted + ' - ' + order.city;
                                    const option = new Option(optionText, order.id);
                                    sourceSelect.add(option);
                                }
                            });
                            
                            // Если в исходном селекте был выбран тот же заказ, что и в целевом, сбрасываем его
                            if (currentSourceValue == selectedTargetId) {
                                sourceSelect.value = '';
                            } else if (currentSourceValue && currentSourceValue != selectedTargetId) {
                                // Восстанавливаем предыдущее значение, если оно не конфликтует
                                sourceSelect.value = currentSourceValue;
                            }
                        }
                        
                        function showMergeModal() {
                            const sourceId = document.querySelector('select[name=\"source_order_id\"]').value;
                            const targetId = document.querySelector('select[name=\"target_order_id\"]').value;
                            
                            if (!sourceId || !targetId) {
                                alert('Выберите оба заказа для объединения');
                                return;
                            }
                            
                            if (sourceId === targetId) {
                                alert('Нельзя объединить заказ с самим собой');
                                return;
                            }
                            
                            // Получаем данные заказов из селектов
                            const sourceSelect = document.querySelector('select[name=\"source_order_id\"]');
                            const targetSelect = document.querySelector('select[name=\"target_order_id\"]');
                            
                            sourceOrderData = {
                                id: sourceId,
                                text: sourceSelect.options[sourceSelect.selectedIndex].text
                            };
                            
                            targetOrderData = {
                                id: targetId,
                                text: targetSelect.options[targetSelect.selectedIndex].text
                            };
                            
                            // Показываем модальное окно
                            document.getElementById('mergeModal').style.display = 'block';
                            updatePreview();
                        }
                        
                        function closeMergeModal() {
                            document.getElementById('mergeModal').style.display = 'none';
                            // Очищаем глобальные данные
                            window.currentSourceOrder = null;
                            window.currentTargetOrder = null;
                        }
                        
                        // Закрытие модального окна при клике вне его
                        window.onclick = function(event) {
                            const modal = document.getElementById('mergeModal');
                            if (event.target === modal) {
                                closeMergeModal();
                            }
                        }
                        
                        function updatePreview() {
                            if (!sourceOrderData || !targetOrderData) return;
                            
                            const previewContent = document.getElementById('preview-content');
                            
                            // Если данные уже загружены, просто обновляем состояние полей
                            if (window.currentSourceOrder && window.currentTargetOrder) {
                                showDetailedPreview(window.currentSourceOrder, window.currentTargetOrder);
                                return;
                            }
                            
                            previewContent.innerHTML = '<div style=\"text-align: center; padding: 20px;\">⏳ Загружаем данные заказов...</div>';
                            
                            // Получаем данные заказов через AJAX
                            fetch('/admin/page/order-merge/get-orders-data', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': document.querySelector('meta[name=\"csrf-token\"]').getAttribute('content')
                                },
                                body: JSON.stringify({
                                    source_order_id: sourceOrderData.id,
                                    target_order_id: targetOrderData.id
                                })
                            })
                            .then(response => response.json())
                            .then(data => {
                                if (data.success) {
                                    // Сохраняем данные глобально
                                    window.currentSourceOrder = data.source_order;
                                    window.currentTargetOrder = data.target_order;
                                    showDetailedPreview(data.source_order, data.target_order);
                                } else {
                                    previewContent.innerHTML = '<div style=\"color: #dc3545;\">❌ Ошибка загрузки данных заказов</div>';
                                }
                            })
                            .catch(error => {
                                console.error('Error:', error);
                                previewContent.innerHTML = '<div style=\"color: #dc3545;\">❌ Ошибка загрузки данных заказов</div>';
                            });
                        }
                        
                        function showDetailedPreview(sourceOrder, targetOrder) {
                            const previewContent = document.getElementById('preview-content');
                            const mergeFields = ['city', 'address', 'phone', 'additional_info'];
                            const fieldNames = {
                                'city': 'Город',
                                'address': 'Адрес', 
                                'phone': 'Телефон',
                                'additional_info': 'Дополнительная информация'
                            };
                            
                            let previewHtml = '<div style=\"font-size: 14px;\">';
                            previewHtml += '<div style=\"margin-bottom: 20px; padding: 15px; background: #e3f2fd; border-radius: 6px; border-left: 4px solid #2196f3;\">';
                            previewHtml += '<p style=\"margin: 0; font-weight: 500;\"><strong>Заказ #' + sourceOrder.id + '</strong> (основной, дата: ' + sourceOrder.order_datetime_formatted + ')</p>';
                            previewHtml += '<p style=\"margin: 5px 0 0 0;\">будет обновлен данными из <strong>Заказа #' + targetOrder.id + '</strong> (дата: ' + targetOrder.order_datetime_formatted + ')</p>';
                            previewHtml += '</div>';
                            
                            mergeFields.forEach(field => {
                                const checkbox = document.getElementById('merge_' + field);
                                const isChecked = checkbox && checkbox.checked;
                                const currentValue = sourceOrder[field] || '<em>пусто</em>';
                                const newValue = targetOrder[field] || '<em>пусто</em>';
                                
                                const disabledClass = isChecked ? '' : ' disabled';
                                
                                previewHtml += '<div id=\"preview-' + field + '\" class=\"field-preview' + disabledClass + '\" style=\"margin: 12px 0; padding: 15px; border: 1px solid #ddd; border-radius: 6px; background: #f8f9fa; border-left: 4px solid #28a745;\">';
                                previewHtml += '<div class=\"field-label\" style=\"font-weight: 600; margin-bottom: 8px; color: #495057;\">' + fieldNames[field] + ':</div>';
                                previewHtml += '<div class=\"field-current\" style=\"color: #dc3545; margin-bottom: 4px; font-size: 13px;\">❌ Текущее: ' + currentValue + '</div>';
                                previewHtml += '<div class=\"field-new\" style=\"color: #28a745; font-size: 13px;\">✅ Новое: ' + newValue + '</div>';
                                previewHtml += '</div>';
                            });
                            
                            previewHtml += '<div style=\"margin-top: 20px; padding: 15px; background: #fff3cd; border: 1px solid #ffeaa7; border-radius: 6px; border-left: 4px solid #ffc107;\">';
                            previewHtml += '<strong style=\"color: #856404;\">⚠️ Внимание:</strong> <span style=\"color: #856404;\">Заказ #' + targetOrder.id + ' будет удален после слияния</span>';
                            previewHtml += '</div>';
                            previewHtml += '</div>';
                            
                            previewContent.innerHTML = previewHtml;
                        }
                        
                        function toggleField(field) {
                            const checkbox = document.getElementById('merge_' + field);
                            const previewElement = document.getElementById('preview-' + field);
                            const labelElement = checkbox.closest('.field-checkbox');
                            
                            if (checkbox.checked) {
                                // Включаем поле
                                previewElement.classList.remove('disabled');
                                labelElement.classList.remove('disabled');
                            } else {
                                // Отключаем поле
                                previewElement.classList.add('disabled');
                                labelElement.classList.add('disabled');
                            }
                        }
                        
                        function cancelMerge() {
                            closeMergeModal();
                        }
                        
                        function confirmMerge() {
                            const sourceId = document.querySelector('select[name=\"source_order_id\"]').value;
                            const targetId = document.querySelector('select[name=\"target_order_id\"]').value;
                            
                            if (!confirm('Вы уверены, что хотите объединить эти заказы? Второй заказ будет удален.')) {
                                return;
                            }
                            
                            // Отправляем POST запрос
                            const form = document.createElement('form');
                            form.method = 'POST';
                            form.action = '/admin/page/order-merge';
                            
                            const csrfToken = document.createElement('input');
                            csrfToken.type = 'hidden';
                            csrfToken.name = '_token';
                            csrfToken.value = document.querySelector('meta[name=\"csrf-token\"]').getAttribute('content');
                            
                            const sourceField = document.createElement('input');
                            sourceField.type = 'hidden';
                            sourceField.name = 'source_order_id';
                            sourceField.value = sourceId;
                            
                            const targetField = document.createElement('input');
                            targetField.type = 'hidden';
                            targetField.name = 'target_order_id';
                            targetField.value = targetId;
                            
                            // Добавляем выбранные поля
                            const mergeFields = ['city', 'address', 'phone', 'additional_info'];
                            mergeFields.forEach(field => {
                                const checkbox = document.getElementById('merge_' + field);
                                if (checkbox && checkbox.checked) {
                                    const fieldInput = document.createElement('input');
                                    fieldInput.type = 'hidden';
                                    fieldInput.name = 'merge_fields[]';
                                    fieldInput.value = field;
                                    form.appendChild(fieldInput);
                                }
                            });
                            
                            form.appendChild(csrfToken);
                            form.appendChild(sourceField);
                            form.appendChild(targetField);
                            
                            // Закрываем модальное окно
                            closeMergeModal();
                            
                            document.body.appendChild(form);
                            form.submit();
                        }
                    </script>"
                ])
            ])
        ];
    }


    public static function merge_orders()
    {
        // Добавляем логирование для отладки
        Log::info('merge_orders method called', [
            'source_order_id' => request('source_order_id'),
            'target_order_id' => request('target_order_id'),
            'merge_fields' => request('merge_fields', []),
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
        
        Log::info('Found orders', [
            'source_order' => $sourceOrder ? $sourceOrder->toArray() : null,
            'target_order' => $targetOrder ? $targetOrder->toArray() : null
        ]);
        
        if (!$sourceOrder || !$targetOrder) {
            Log::error('One of orders not found', [
                'source_order_id' => $sourceOrderId,
                'target_order_id' => $targetOrderId,
                'source_found' => $sourceOrder !== null,
                'target_found' => $targetOrder !== null
            ]);
            session()->flash('error', 'Один из заказов не найден');
            return;
        }
        
        try {
            Log::info('About to call mergeWith', [
                'source_order_id' => $sourceOrder->id,
                'target_order_id' => $targetOrder->id
            ]);
            
            // Получаем выбранные поля для переноса
            $mergeFields = request('merge_fields', []);
            
            // Используем метод mergeWith из модели с выбранными полями
            $sourceOrder->mergeWith($targetOrder, $mergeFields);
            
            Log::info('mergeWith completed successfully');
            
            $mergeFields = request('merge_fields', []);
            $fieldsText = empty($mergeFields) ? 'все доступные поля' : implode(', ', $mergeFields);
            
            session()->flash('success', "✅ Заказы успешно объединены! Заказ #{$targetOrderId} объединен с заказом #{$sourceOrderId}. Перенесены поля: {$fieldsText}. Заказ #{$targetOrderId} удален.");
            
            return redirect()->route('moonshine.page', ['pageUri' => 'order-merge']);
        } catch (\Exception $e) {
            Log::error('Error merging orders: ' . $e->getMessage());
            session()->flash('error', 'Ошибка при объединении заказов: ' . $e->getMessage());
        }
        
        return redirect()->route('moonshine.page', ['pageUri' => 'order-merge']);
    }

    public static function getOrdersData()
    {
        $sourceOrderId = request('source_order_id');
        $targetOrderId = request('target_order_id');
        
        if (!$sourceOrderId || !$targetOrderId) {
            return response()->json([
                'success' => false,
                'message' => 'Не указаны ID заказов'
            ]);
        }
        
        $sourceOrder = Order::find($sourceOrderId);
        $targetOrder = Order::find($targetOrderId);
        
        if (!$sourceOrder || !$targetOrder) {
            return response()->json([
                'success' => false,
                'message' => 'Один из заказов не найден'
            ]);
        }
        
        return response()->json([
            'success' => true,
            'source_order' => [
                'id' => $sourceOrder->id,
                'city' => $sourceOrder->city,
                'address' => $sourceOrder->address,
                'phone' => $sourceOrder->phone,
                'additional_info' => $sourceOrder->additional_info,
                'order_datetime_formatted' => $sourceOrder->order_datetime_formatted
            ],
            'target_order' => [
                'id' => $targetOrder->id,
                'city' => $targetOrder->city,
                'address' => $targetOrder->address,
                'phone' => $targetOrder->phone,
                'additional_info' => $targetOrder->additional_info,
                'order_datetime_formatted' => $targetOrder->order_datetime_formatted
            ]
        ]);
    }

    public static function getOrdersList()
    {
        $orders = Order::all()->map(function($order) {
            return [
                'id' => $order->id,
                'city' => $order->city,
                'address' => $order->address,
                'phone' => $order->phone,
                'additional_info' => $order->additional_info,
                'order_datetime_formatted' => $order->order_datetime_formatted
            ];
        });
        
        return response()->json([
            'success' => true,
            'orders' => $orders
        ]);
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
        return $this->title ?: 'Объединение заказов';
    }
}
