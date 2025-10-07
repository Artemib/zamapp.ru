<?php

declare(strict_types=1);

namespace App\MoonShine\UI\Components;

class OverlayCalendar
{
    protected string $name;
    protected string $placeholder;
    protected ?string $value = null;

    public function __construct(string $name, string $placeholder = 'Выберите дату')
    {
        $this->name = $name;
        $this->placeholder = $placeholder;
    }

    public static function make(string $name, string $placeholder = 'Выберите дату'): self
    {
        return new self($name, $placeholder);
    }

    public function value(?string $value): self
    {
        $this->value = $value;
        return $this;
    }

    public function render(): string
    {
        $calendarId = 'overlay-calendar-' . uniqid();
        $inputId = 'input-' . uniqid();
        
        // Подключаем ассеты
        $this->includeAssets();
        
        return '
        <div class="relative" x-data="overlayCalendar(\'' . $this->placeholder . '\')">
            <!-- Скрытое поле для хранения значения -->
            <input type="hidden" 
                   name="' . $this->name . '" 
                   id="' . $inputId . '" 
                   x-model="selectedValue"
                   :value="selectedValue">
            
            <!-- Кнопка для открытия календаря -->
            <div class="calendar-trigger" @click="toggleCalendar()">
                <div class="calendar-display">
                    <span x-text="displayText" class="calendar-text"></span>
                    <svg class="calendar-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                        <line x1="16" y1="2" x2="16" y2="6"></line>
                        <line x1="8" y1="2" x2="8" y2="6"></line>
                        <line x1="3" y1="10" x2="21" y2="10"></line>
                    </svg>
                </div>
            </div>
            
            <!-- Overlay календарь -->
            <div x-show="isOpen" 
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="transition ease-in duration-150"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 class="calendar-overlay" 
                 id="' . $calendarId . '"
                 @click="closeCalendar()"
                 @keydown.escape.window="closeCalendar()"
                 @touchmove.prevent
                 @touchstart.prevent>
                
                <div class="calendar-overlay-content" @click.stop @touchmove.stop @touchstart.stop>
                    
                    <!-- Боковая панель с быстрыми периодами -->
                    <div class="calendar-sidebar">
                        <div class="sidebar-header">
                            <h4 class="sidebar-title">Быстрый выбор</h4>
                            <button type="button" @click="closeCalendar()" class="close-btn close-btn-mobile">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <line x1="18" y1="6" x2="6" y2="18"></line>
                                    <line x1="6" y1="6" x2="18" y2="18"></line>
                                </svg>
                            </button>
                        </div>
                        
                        <!-- Мобильный селект -->
                        <select x-model="selectedPeriod" @change="selectQuickPeriod(selectedPeriod)" class="period-selector">
                            <option value="">Выберите период</option>
                            <option value="today">Сегодня</option>
                            <option value="yesterday">Вчера</option>
                            <option value="currentWeek">Текущая неделя</option>
                            <option value="lastWeek">Прошлая неделя</option>
                            <option value="currentMonth">Текущий месяц</option>
                            <option value="lastMonth">Прошлый месяц</option>
                        </select>
                        
                        <!-- Десктопные кнопки -->
                        <div class="period-options">
                            <div class="period-option" 
                                 :class="{ \'active\': selectedPeriod === \'today\' }"
                                 @click="selectQuickPeriod(\'today\')">
                                Сегодня
                            </div>
                            <div class="period-option" 
                                 :class="{ \'active\': selectedPeriod === \'yesterday\' }"
                                 @click="selectQuickPeriod(\'yesterday\')">
                                Вчера
                            </div>
                            <div class="period-option" 
                                 :class="{ \'active\': selectedPeriod === \'currentWeek\' }"
                                 @click="selectQuickPeriod(\'currentWeek\')">
                                Текущая неделя
                            </div>
                            <div class="period-option" 
                                 :class="{ \'active\': selectedPeriod === \'lastWeek\' }"
                                 @click="selectQuickPeriod(\'lastWeek\')">
                                Прошлая неделя
                            </div>
                            <div class="period-option" 
                                 :class="{ \'active\': selectedPeriod === \'currentMonth\' }"
                                 @click="selectQuickPeriod(\'currentMonth\')">
                                Текущий месяц
                            </div>
                            <div class="period-option" 
                                 :class="{ \'active\': selectedPeriod === \'lastMonth\' }"
                                 @click="selectQuickPeriod(\'lastMonth\')">
                                Прошлый месяц
                            </div>
                        </div>
                    </div>
                    
                    <!-- Основной календарь -->
                    <div class="calendar-main">
                        <!-- Заголовок календаря -->
                        <div class="calendar-header">
                            <button type="button" @click="previousMonth()" class="calendar-nav-btn">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <polyline points="15,18 9,12 15,6"></polyline>
                                </svg>
                            </button>
                            
                            <h3 class="calendar-month-year" x-text="currentMonthYear"></h3>
                            
                            <button type="button" @click="nextMonth()" class="calendar-nav-btn">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <polyline points="9,18 15,12 9,6"></polyline>
                                </svg>
                            </button>
                        </div>
                        
                        <!-- Дни недели -->
                        <div class="calendar-weekdays">
                            <template x-for="day in weekDays" :key="day">
                                <div class="calendar-weekday" x-text="day"></div>
                            </template>
                        </div>
                        
                        <!-- Дни месяца -->
                        <div class="calendar-days">
                            <template x-for="day in calendarDays" :key="day.date">
                                <div :class="day.classes"
                                     @click="selectDate(day)"
                                     x-text="day.day">
                                </div>
                            </template>
                        </div>
                        
                    <!-- Выбранный период -->
                    <div class="selected-period desktop-only">
                        <span x-text="getSelectedPeriodText()"></span>
                    </div>
                        
                        <!-- Кнопки действий -->
                        <div class="calendar-actions">
                            <button type="button" @click="clearSelection()" class="calendar-btn calendar-btn-secondary" :disabled="!hasSelection">
                                Очистить
                            </button>
                            <button type="button" @click="applySelection()" class="calendar-btn calendar-btn-primary" :disabled="!hasSelection">
                                Применить
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>';
    }

    public function getName(): string
    {
        return $this->name;
    }

    private function includeAssets(): void
    {
        static $assetsIncluded = false;
        
        if (!$assetsIncluded) {
            // Подключаем CSS и JS
            echo '<link rel="stylesheet" href="/css/overlay-calendar.css">';
            echo '<script src="/js/overlay-calendar.js"></script>';
            
            $assetsIncluded = true;
        }
    }
}
