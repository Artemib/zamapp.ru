<?php

declare(strict_types=1);

namespace App\MoonShine\UI\Components;

use Illuminate\Contracts\Support\Renderable;
use MoonShine\UI\Components\Icon;

class OverlayCalendar implements Renderable
{
    protected string $name;
    protected string $placeholder;
    protected ?string $value = null;
    
    // Внешний триггер и целевые инпуты
    protected ?string $externalTriggerSelector = null; // CSS-селектор кнопки/элемента для открытия
    protected string $externalTriggerEvent = 'click';
    protected ?string $targetInputSelector = null; // единый инпут для результата (одиночная дата или диапазон в строке)
    protected ?string $targetStartInputSelector = null; // при диапазоне — начало
    protected ?string $targetEndInputSelector = null;   // при диапазоне — конец
    protected bool $showInternalTrigger = true; // показывать ли встроенную кнопку-триггер

    // Режим только одиночной даты (без диапазона)
    protected bool $singleDateOnly = false;

    // Формат вывода и разделитель диапазона
    protected string $outputFormat = 'Y-m-d';
    protected string $rangeDelimiter = ' — ';
    protected string $rangeDelimiterForValue = ' — '; // разделитель для value (по умолчанию такой же как для отображения)
    protected bool $includeEndOfDay = false; // включительно считать последний день (23:59:59)
    
    // Кастомизация базового инпута
    protected ?string $inputClasses = null; // дополнительные CSS классы для инпута
    protected ?string $inputStyles = null; // инлайн стили для инпута
    protected ?string $inputIcon = null; // кастомная иконка (SVG код или класс)
    protected ?string $inputIconColor = null; // цвет иконки
    protected ?string $inputPlaceholder = null; // кастомный placeholder
    
    // Новые параметры настройки
    protected bool $showQuickPeriods = true;
    protected array $quickPeriods = ['today', 'yesterday', 'current_week', 'last_week', 'current_month', 'last_month'];
    protected string $primaryColor = '#3b82f6';
    protected string $rangeColor = '#10b981';
    protected ?string $inRangeColor = null; // фон диапазона между датами
    protected ?string $inRangeTextColor = null; // цвет текста в диапазоне
    protected ?string $quickActiveBg = null; // фон активного быстрого периода
    protected ?string $quickActiveText = null; // текст активного быстрого периода
    protected ?string $applyBg = null; // фон кнопки Применить
    protected ?string $applyText = null; // текст кнопки Применить
    protected bool $showSelectedPeriod = true;
    protected bool $showMonthNames = true;
    protected ?string $quickTitle = null;
    protected ?string $maxWidth = null; // например '720px'
    protected ?string $maxHeight = null; // например '80vh'

    public function __construct(string $name, string $placeholder = null)
    {
        $this->name = $name;
        // Храним строковый ключ перевода, чтобы перевод применялся в render() по текущей локали
        $this->placeholder = $placeholder ?? 'overlay_calendar.placeholder';
    }

    public static function make(string $name, ?string $placeholder = null): self
    {
        return new self($name, $placeholder);
    }

    // Настройка внешнего триггера
    public function openWith(string $triggerSelector, string $targetSelector, ?string $targetEndSelector = null, string $event = 'click'): self
    {
        $this->externalTriggerSelector = $triggerSelector;
        $this->externalTriggerEvent = $event;
        // Если указан второй инпут, используем пару start/end
        if ($targetEndSelector) {
            $this->targetStartInputSelector = $targetSelector;
            $this->targetEndInputSelector = $targetEndSelector;
            $this->targetInputSelector = null;
        } else {
            $this->targetInputSelector = $targetSelector;
            $this->targetStartInputSelector = null;
            $this->targetEndInputSelector = null;
        }
        // По умолчанию скрываем встроенный триггер, раз есть внешний
        $this->showInternalTrigger = false;
        return $this;
    }

    public function triggerSelector(string $selector, string $event = 'click'): self
    {
        $this->externalTriggerSelector = $selector;
        $this->externalTriggerEvent = $event;
        return $this;
    }

    public function targetInput(string $selector): self
    {
        $this->targetInputSelector = $selector;
        $this->targetStartInputSelector = null;
        $this->targetEndInputSelector = null;
        return $this;
    }

    public function targetRangeInputs(string $startSelector, string $endSelector): self
    {
        $this->targetStartInputSelector = $startSelector;
        $this->targetEndInputSelector = $endSelector;
        $this->targetInputSelector = null;
        return $this;
    }

    public function showInternalTrigger(bool $show = true): self
    {
        $this->showInternalTrigger = $show;
        return $this;
    }

    public function value(?string $value): self
    {
        $this->value = $value;
        return $this;
    }

    // Методы настройки быстрых периодов
    public function showQuickPeriods(bool $show = true): self
    {
        $this->showQuickPeriods = $show;
        return $this;
    }

    public function quickPeriods(array $periods): self
    {
        $this->quickPeriods = $periods;
        return $this;
    }

    // Методы настройки цветов
    public function primaryColor(string $color): self
    {
        $this->primaryColor = $color;
        return $this;
    }

    public function rangeColor(string $color): self
    {
        $this->rangeColor = $color;
        return $this;
    }

    // Дополнительные цвета
    public function inRangeColor(string $bg, ?string $text = null): self
    {
        $this->inRangeColor = $bg;
        $this->inRangeTextColor = $text;
        return $this;
    }

    public function quickActiveColor(string $bg, ?string $text = null): self
    {
        $this->quickActiveBg = $bg;
        $this->quickActiveText = $text;
        return $this;
    }

    public function applyButtonColor(string $bg, ?string $text = null): self
    {
        $this->applyBg = $bg;
        $this->applyText = $text;
        return $this;
    }

    // Методы настройки отображения
    public function showSelectedPeriod(bool $show = true): self
    {
        $this->showSelectedPeriod = $show;
        return $this;
    }

    public function showMonthNames(bool $show = true): self
    {
        $this->showMonthNames = $show;
        return $this;
    }

    public function quickTitle(string $title): self
    {
        $this->quickTitle = $title;
        return $this;
    }

    // Размеры/масштаб
    public function maxWidth(string $width): self
    {
        $this->maxWidth = $width;
        return $this;
    }

    public function maxHeight(string $height): self
    {
        $this->maxHeight = $height;
        return $this;
    }

    // Только одна дата, без диапазона, прячем быстрый выбор
    public function singleDateOnly(bool $single = true): self
    {
        $this->singleDateOnly = $single;
        if ($single) {
            $this->showQuickPeriods = false;
        }
        return $this;
    }

    // Формат вывода результата (например: 'Y.mm.dd H:i:s')
    public function format(string $format): self
    {
        $this->outputFormat = $format;
        return $this;
    }

    // Кастомный разделитель диапазона (по умолчанию '|')
    // Первый параметр - для отображения, второй опциональный - для value
    public function rangeDelimiter(string $delimiter, ?string $delimiterForValue = null): self
    {
        $this->rangeDelimiter = $delimiter;
        $this->rangeDelimiterForValue = $delimiterForValue ?? $delimiter;
        return $this;
    }

    // Включительно считать последний день (ставит время 23:59:59 у конечной даты диапазона)
    public function includeEndOfDay(bool $include = true): self
    {
        $this->includeEndOfDay = $include;
        return $this;
    }

    // Кастомизация базового инпута
    public function addClass(string $classes): self
    {
        $this->inputClasses = $classes;
        return $this;
    }

    public function addStyles(string $styles): self
    {
        $this->inputStyles = $styles;
        return $this;
    }

    public function icon(string $icon, ?string $color = null): self
    {
        $this->inputIcon = $icon;
        $this->inputIconColor = $color;
        return $this;
    }

    public function placeholder(string $placeholder): self
    {
        $this->inputPlaceholder = $placeholder;
        return $this;
    }

    public function render(): string
    {
        $calendarId = 'overlay-calendar-' . uniqid();
        $inputId = 'input-' . uniqid();
        
        // Подключаем ассеты
        $this->includeAssets();
        
        // Подготавливаем data-атрибуты для конфигурации
        $translations = [
            'weekdays' => json_encode(__('overlay_calendar.weekdays'), JSON_UNESCAPED_UNICODE),
            'months' => json_encode(__('overlay_calendar.months'), JSON_UNESCAPED_UNICODE),
            'labels' => json_encode([
                'today' => __('overlay_calendar.today'),
                'yesterday' => __('overlay_calendar.yesterday'),
                'current_week' => __('overlay_calendar.current_week'),
                'last_week' => __('overlay_calendar.last_week'),
                'current_month' => __('overlay_calendar.current_month'),
                'last_month' => __('overlay_calendar.last_month'),
                'apply' => __('overlay_calendar.apply'),
                'clear' => __('overlay_calendar.clear'),
            ], JSON_UNESCAPED_UNICODE),
        ];

        $dataAttributes = [
            'data-placeholder' => ($this->placeholder ? __($this->placeholder) : __('overlay_calendar.placeholder')),
            'data-show-quick-periods' => $this->showQuickPeriods ? 'true' : 'false',
            'data-quick-periods' => implode(',', $this->quickPeriods),
            'data-primary-color' => $this->primaryColor,
            'data-range-color' => $this->rangeColor,
            'data-show-selected-period' => $this->showSelectedPeriod ? 'true' : 'false',
            'data-show-month-names' => $this->showMonthNames ? 'true' : 'false',
            'data-weekdays' => $translations['weekdays'],
            'data-months' => $translations['months'],
            'data-labels' => $translations['labels'],
            'data-internal-trigger' => $this->showInternalTrigger ? 'true' : 'false',
            'data-single-date-only' => $this->singleDateOnly ? 'true' : 'false',
            'data-output-format' => $this->outputFormat,
            'data-range-delimiter' => $this->rangeDelimiter,
            'data-range-delimiter-value' => $this->rangeDelimiterForValue,
            'data-include-end-of-day' => $this->includeEndOfDay ? 'true' : 'false',
            'data-input-classes' => $this->inputClasses,
            'data-input-styles' => $this->inputStyles,
            'data-input-icon' => $this->inputIcon ? $this->renderIcon($this->inputIcon) : null,
            'data-input-icon-color' => $this->inputIconColor,
            'data-input-placeholder' => $this->inputPlaceholder,
        ];

        // Внешние биндинги
        if ($this->externalTriggerSelector) {
            $dataAttributes['data-trigger-selector'] = $this->externalTriggerSelector;
            $dataAttributes['data-trigger-event'] = $this->externalTriggerEvent;
        }
        if ($this->targetInputSelector) {
            $dataAttributes['data-target-selector'] = $this->targetInputSelector;
        }
        if ($this->targetStartInputSelector) {
            $dataAttributes['data-target-start'] = $this->targetStartInputSelector;
        }
        if ($this->targetEndInputSelector) {
            $dataAttributes['data-target-end'] = $this->targetEndInputSelector;
        }
        
        $dataAttrString = '';
        foreach ($dataAttributes as $key => $value) {
            if ($value === null) {
                continue;
            }
            $dataAttrString .= ' ' . $key . '="' . htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8') . '"';
        }
        
        // Передаём цвета как инлайн CSS-переменные на корневой элемент календаря,
        // чтобы каждый экземпляр имел независимые цвета
        $inlineStyle = '--calendar-primary-color: ' . $this->primaryColor . '; --calendar-range-color: ' . $this->rangeColor . ';';
        if ($this->inRangeColor) {
            $inlineStyle .= ' --calendar-inrange-color: ' . $this->inRangeColor . ';';
        }
        if ($this->inRangeTextColor) {
            $inlineStyle .= ' --calendar-inrange-text-color: ' . $this->inRangeTextColor . ';';
        }
        if ($this->quickActiveBg) {
            $inlineStyle .= ' --calendar-quick-active-bg: ' . $this->quickActiveBg . ';';
        }
        if ($this->quickActiveText) {
            $inlineStyle .= ' --calendar-quick-active-text: ' . $this->quickActiveText . ';';
        }
        if ($this->applyBg) {
            $inlineStyle .= ' --calendar-apply-bg: ' . $this->applyBg . ';';
        }
        if ($this->applyText) {
            $inlineStyle .= ' --calendar-apply-text: ' . $this->applyText . ';';
        }
        if ($this->maxWidth) {
            $inlineStyle .= ' --calendar-max-width: ' . $this->maxWidth . ';';
        }
        if ($this->maxHeight) {
            $inlineStyle .= ' --calendar-max-height: ' . $this->maxHeight . ';';
        }
        // Если быстрые периоды отключены, задаём дефолтный масштаб
        if (! $this->showQuickPeriods) {
            if (is_null($this->maxWidth)) {
                $inlineStyle .= ' --calendar-max-width: 500px;';
            }
            if (is_null($this->maxHeight)) {
                $inlineStyle .= ' --calendar-max-height: 100vh;';
            }
        }

        return '
        <div class="relative" x-data="overlayCalendar()" style="' . $inlineStyle . '" ' . $dataAttrString . '>
            <!-- Скрытое поле для хранения значения -->
            <input type="hidden" 
                   name="' . $this->name . '" 
                   id="' . $inputId . '" 
                   x-model="selectedValue"
                   :value="selectedValue">
            
            <!-- Кнопка для открытия календаря -->
            <template x-if="showInternalTrigger">
                <div class="calendar-trigger" 
                     :class="config.inputClasses" 
                     :style="config.inputStyles"
                     @click="toggleCalendar()">
                    <div class="calendar-display">
                        <span x-text="displayText" class="calendar-text"></span>
                        <div x-html="config.inputIcon" class="calendar-icon" :style="{ color: config.inputIconColor }"></div>
                    </div>
                </div>
            </template>
            
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
                    <!-- Мобильный крестик закрытия (всегда доступен) -->
                    <button type="button" @click="closeCalendar()" class="close-btn close-btn-mobile" style="position:absolute; right:12px; top:12px; z-index: 20;">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <line x1="18" y1="6" x2="6" y2="18"></line>
                            <line x1="6" y1="6" x2="18" y2="18"></line>
                        </svg>
                    </button>
                    
                    <!-- Боковая панель с быстрыми периодами -->
                    <div class="calendar-sidebar" x-show="config.showQuickPeriods">
                        <div class="sidebar-header">
                            <h4 class="sidebar-title">' . htmlspecialchars(__($this->quickTitle ?: 'overlay_calendar.quick_title'), ENT_QUOTES, 'UTF-8') . '</h4>
                        </div>
                        
                        <!-- Мобильный селект -->
                        <select x-model="selectedPeriod" @change="selectQuickPeriod(selectedPeriod)" class="period-selector">
                            <option value="">Выберите период</option>
                            <template x-for="period in config.quickPeriods" :key="period">
                                <option :value="period" x-text="getPeriodLabel(period)"></option>
                            </template>
                        </select>
                        
                        <!-- Десктопные кнопки -->
                        <div class="period-options">
                            <template x-for="period in config.quickPeriods" :key="period">
                                <div class="period-option" 
                                     :class="{ \'active\': selectedPeriod === period }"
                                     @click="selectQuickPeriod(period)" x-text="getPeriodLabel(period)">
                                </div>
                            </template>
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
                    <div class="selected-period desktop-only" x-show="config.showSelectedPeriod">
                        <span x-text="getSelectedPeriodText()"></span>
                    </div>
                        
                        <!-- Кнопки действий -->
                        <div class="calendar-actions">
                            <button type="button" @click="clearSelection()" class="calendar-btn calendar-btn-secondary" :disabled="!hasSelection">
                                ' . e(__('overlay_calendar.clear')) . '
                            </button>
                            <button type="button" @click="applySelection()" class="calendar-btn calendar-btn-primary" :disabled="!hasSelection">
                                ' . e(__('overlay_calendar.apply')) . '
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

    private function renderIcon(string $icon): string
    {
        // Если это HTML иконка, возвращаем как есть
        if (str_contains($icon, '<') || str_contains($icon, 'svg')) {
            return $icon;
        }
        
        // Если это иконка MoonShine, рендерим через буферизацию
        ob_start();
        echo (new Icon($icon))->render();
        $rendered = ob_get_clean();
        
        
        return $rendered;
    }
}
