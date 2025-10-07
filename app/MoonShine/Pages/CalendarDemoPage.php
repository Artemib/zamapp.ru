<?php

declare(strict_types=1);

namespace App\MoonShine\Pages;

use MoonShine\Laravel\Pages\Page;
use App\MoonShine\UI\Components\CustomCalendar;

class CalendarDemoPage extends Page
{
    protected string $title = 'Демо календаря';

    protected ?string $alias = 'calendar-demo';

    public function components(): array
    {
        return [
            '<div class="container-fluid">
                <div class="row">
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title">Выбор одной даты</h5>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <label class="form-label">Выберите дату</label>
                                    ' . CustomCalendar::make('single_date', 'Нажмите на дату')->render() . '
                                    <div class="form-text">Можно выбрать только одну дату</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title">Выбор диапазона дат</h5>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <label class="form-label">Выберите период</label>
                                    ' . CustomCalendar::make('date_range', 'Выберите дату или диапазон')->render() . '
                                    <div class="form-text">Нажмите на одну дату для выбора дня, или на две даты для выбора диапазона</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="row mt-4">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title">Инструкция по использованию</h5>
                            </div>
                            <div class="card-body">
                                <h6>Логика работы календаря:</h6>
                                <ol>
                                    <li><strong>Первый клик</strong> - выбирается начальная дата</li>
                                    <li><strong>Второй клик</strong> - если дата позже начальной, выбирается конечная дата (создается диапазон)</li>
                                    <li><strong>Третий клик</strong> - сбрасывается предыдущий выбор и выбирается новая дата</li>
                                </ol>
                                
                                <h6 class="mt-3">Особенности:</h6>
                                <ul>
                                    <li>Выходные дни (суббота и воскресенье) выделены красным цветом</li>
                                    <li>Текущий день выделен синим цветом</li>
                                    <li>Выбранная дата выделена зеленым цветом</li>
                                    <li>Диапазон дат выделен светло-зеленым цветом</li>
                                    <li>Начало и конец диапазона выделены темно-зеленым цветом</li>
                                </ul>
                                
                                <h6 class="mt-3">Кнопки:</h6>
                                <ul>
                                    <li><strong>Очистить</strong> - сбрасывает выбор</li>
                                    <li><strong>Применить</strong> - сохраняет выбор и закрывает календарь</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <style>
            .card {
                border: 1px solid #e5e7eb;
                border-radius: 8px;
                box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1);
            }
            
            .card-header {
                background-color: #f9fafb;
                border-bottom: 1px solid #e5e7eb;
                padding: 16px;
            }
            
            .card-title {
                margin: 0;
                font-size: 16px;
                font-weight: 600;
                color: #111827;
            }
            
            .card-body {
                padding: 16px;
            }
            
            .container-fluid {
                padding: 20px;
            }
            
            .row {
                margin-left: -15px;
                margin-right: -15px;
            }
            
            .col-md-6, .col-12 {
                padding-left: 15px;
                padding-right: 15px;
            }
            
            .mt-3 {
                margin-top: 1rem;
            }
            
            .mt-4 {
                margin-top: 1.5rem;
            }
            </style>'
        ];
    }
}
