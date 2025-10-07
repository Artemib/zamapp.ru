<?php

declare(strict_types=1);

namespace App\MoonShine\UI\Components;

class CustomCalendar
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
        $calendarId = 'calendar-' . uniqid();
        $inputId = 'input-' . uniqid();
        
        // Подключаем CSS и JS файлы
        $this->includeAssets();
        
        return '
        <div class="relative inline-block" x-data="customCalendar(\'' . $this->placeholder . '\')">
            <!-- Скрытое поле для хранения значения -->
            <input type="hidden" 
                   name="' . $this->name . '" 
                   id="' . $inputId . '" 
                   x-model="selectedValue"
                   :value="selectedValue">
            
            <!-- Кнопка для открытия календаря -->
            <div class="border border-gray-300 rounded-md px-3 py-2 bg-white cursor-pointer min-w-[200px] hover:border-gray-400 transition-colors" 
                 @click="toggleCalendar()">
                <div class="flex justify-between items-center">
                    <span x-text="displayText" class="text-gray-700"></span>
                    <svg class="w-4 h-4 text-gray-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                        <line x1="16" y1="2" x2="16" y2="6"></line>
                        <line x1="8" y1="2" x2="8" y2="6"></line>
                        <line x1="3" y1="10" x2="21" y2="10"></line>
                    </svg>
                </div>
            </div>
            
            <!-- Календарь -->
            <div x-show="isOpen" 
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100"
                 x-transition:leave="transition ease-in duration-150"
                 x-transition:leave-start="opacity-100 scale-100"
                 x-transition:leave-end="opacity-0 scale-95"
                 class="absolute top-full left-0 z-50 bg-white border border-gray-300 rounded-lg shadow-lg p-4 min-w-[300px] mt-1" 
                 id="' . $calendarId . '"
                 @click.away="closeCalendar()">
                
                <!-- Заголовок календаря -->
                <div class="flex justify-between items-center mb-4">
                    <button type="button" @click="previousMonth()" 
                            class="p-1 rounded hover:bg-gray-100 text-gray-600 transition-colors">
                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polyline points="15,18 9,12 15,6"></polyline>
                        </svg>
                    </button>
                    <h3 class="text-base font-semibold text-gray-900" x-text="currentMonthYear"></h3>
                    <button type="button" @click="nextMonth()" 
                            class="p-1 rounded hover:bg-gray-100 text-gray-600 transition-colors">
                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polyline points="9,18 15,12 9,6"></polyline>
                        </svg>
                    </button>
                </div>
                
                <!-- Дни недели -->
                <div class="grid grid-cols-7 gap-1 mb-2">
                    <template x-for="day in weekDays" :key="day">
                        <div class="text-center text-xs font-medium text-gray-500 py-2" x-text="day"></div>
                    </template>
                </div>
                
                <!-- Дни месяца -->
                <div class="grid grid-cols-7 gap-1 mb-4">
                    <template x-for="day in calendarDays" :key="day.date">
                        <div :class="day.classes"
                             @click="selectDate(day)"
                             x-text="day.day">
                        </div>
                    </template>
                </div>
                
                <!-- Кнопки действий -->
                <div class="flex justify-between gap-2">
                    <button type="button" @click="clearSelection()" 
                            class="px-4 py-2 rounded-md bg-gray-100 text-gray-700 hover:bg-gray-200 transition-colors text-sm font-medium">
                        Очистить
                    </button>
                    <button type="button" @click="applySelection()" 
                            class="px-4 py-2 rounded-md bg-blue-500 text-white hover:bg-blue-600 transition-colors text-sm font-medium">
                        Применить
                    </button>
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
            // Подключаем только JS (CSS заменен на Tailwind)
            echo '<script src="/js/custom-calendar.js"></script>';
            
            $assetsIncluded = true;
        }
    }
}
