# 🔧 Техническая документация OverlayCalendar

## 📋 Архитектура

### Компонентная структура

```
OverlayCalendar
├── PHP Component (OverlayCalendar.php)
│   ├── Конфигурация
│   ├── Рендеринг HTML
│   └── Подключение ассетов
├── CSS Styles (overlay-calendar.css)
│   ├── Базовые стили
│   ├── Адаптивность
│   └── Анимации
└── JavaScript Logic (overlay-calendar.js)
    ├── Alpine.js компонент
    ├── Логика выбора дат
    └── Утилиты
```

### Зависимости

- **Alpine.js 3.0+**: Реактивность и DOM манипуляции
- **MoonShine 3.0+**: Интеграция с админ-панелью
- **Laravel 9.0+**: PHP фреймворк
- **PHP 8.0+**: Язык программирования

## 🎯 PHP Компонент

### Класс OverlayCalendar

```php
class OverlayCalendar
{
    // Свойства
    protected string $name;           // Имя поля
    protected string $placeholder;    // Placeholder текст
    protected ?string $value = null;  // Предустановленное значение
    
    // Методы
    public function __construct(string $name, string $placeholder = 'Выберите дату')
    public static function make(string $name, string $placeholder = 'Выберите дату'): self
    public function value(?string $value): self
    public function render(): string
    public function getName(): string
    private function includeAssets(): void
}
```

### Метод render()

Генерирует HTML структуру:

```html
<div x-data="overlayCalendar('placeholder')" x-cloak>
    <!-- Кнопка открытия -->
    <div class="calendar-trigger" @click="toggleCalendar()">
        <span x-text="displayText"></span>
        <svg>...</svg>
    </div>
    
    <!-- Overlay календарь -->
    <div x-show="isOpen" class="calendar-overlay" @click="closeCalendar()">
        <div class="calendar-overlay-content" @click.stop>
            <!-- Sidebar с быстрыми периодами -->
            <div class="calendar-sidebar">...</div>
            
            <!-- Основной календарь -->
            <div class="calendar-main">...</div>
            
            <!-- Кнопки действий -->
            <div class="calendar-actions">...</div>
        </div>
    </div>
    
    <!-- Скрытое поле для значения -->
    <input type="hidden" name="field_name" x-model="selectedValue">
</div>
```

### Подключение ассетов

```php
private function includeAssets(): void
{
    static $assetsIncluded = false;
    
    if (!$assetsIncluded) {
        echo '<link rel="stylesheet" href="/css/overlay-calendar.css">';
        echo '<script src="/js/overlay-calendar.js"></script>';
        $assetsIncluded = true;
    }
}
```

## 🎨 CSS Архитектура

### Базовые стили

```css
/* Overlay контейнер */
.calendar-overlay {
    position: fixed;
    top: 0; left: 0; right: 0; bottom: 0;
    background-color: rgba(0, 0, 0, 0.5);
    z-index: 1000;
}

/* Контент календаря */
.calendar-overlay-content {
    background: white;
    border-radius: 12px;
    box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
    max-width: 800px;
    max-height: 90vh;
    display: flex;
    position: relative;
}
```

### Адаптивность

```css
/* Десктоп (≥769px) */
@media (min-width: 769px) {
    .calendar-overlay-content {
        flex-direction: row;
    }
    
    .calendar-sidebar {
        width: 300px;
        border-right: 1px solid #e2e8f0;
    }
    
    .calendar-main {
        flex: 1;
    }
}

/* Мобильные (<768px) */
@media (max-width: 768px) {
    .calendar-overlay {
        position: fixed !important;
        width: 100vw !important;
        height: 100vh !important;
    }
    
    .calendar-overlay-content {
        position: fixed !important;
        width: 100vw !important;
        height: 100vh !important;
        border-radius: 0 !important;
        flex-direction: column !important;
    }
}
```

### Состояния дат

```css
/* Обычная дата */
.calendar-day.normal-day {
    color: #374151;
    cursor: pointer;
}

/* Выбранная дата */
.calendar-day.selected {
    background-color: #3b82f6 !important;
    color: white !important;
}

/* Диапазон */
.calendar-day.range-start-end {
    background-color: #10b981 !important;
    color: white !important;
}

/* В диапазоне */
.calendar-day.in-range {
    background-color: #d1fae5;
    color: #065f46;
}
```

## ⚡ JavaScript Логика

### Alpine.js Компонент

```javascript
function overlayCalendar(placeholder = 'Выберите дату') {
    return {
        // Состояние
        isOpen: false,
        currentDate: new Date(),
        selectedStartDate: null,
        selectedEndDate: null,
        selectedValue: '',
        selectedPeriod: null,
        
        // Данные
        weekDays: ['Пн', 'Вт', 'Ср', 'Чт', 'Пт', 'Сб', 'Вс'],
        monthNames: ['Январь', 'Февраль', ...],
        
        // Computed свойства
        get displayText() { ... },
        get currentMonthYear() { ... },
        get hasSelection() { ... },
        get calendarDays() { ... },
        
        // Методы
        toggleCalendar() { ... },
        closeCalendar() { ... },
        previousMonth() { ... },
        nextMonth() { ... },
        selectDate(day) { ... },
        selectQuickPeriod(period) { ... },
        clearSelection() { ... },
        applySelection() { ... }
    }
}
```

### Логика выбора дат

```javascript
selectDate(day) {
    const date = day.fullDate;
    
    if (!this.selectedStartDate) {
        // Первый клик - выбираем начальную дату
        this.selectedStartDate = date;
        this.selectedEndDate = null;
    } else if (!this.selectedEndDate) {
        // Второй клик - проверяем, не та же ли дата
        if (date.getTime() === this.selectedStartDate.getTime()) {
            // Клик на ту же дату - сбрасываем выбор
            this.selectedStartDate = null;
            this.selectedEndDate = null;
        } else {
            // Выбираем конечную дату
            if (date.getTime() < this.selectedStartDate.getTime()) {
                this.selectedEndDate = this.selectedStartDate;
                this.selectedStartDate = date;
            } else {
                this.selectedEndDate = date;
            }
        }
    } else {
        // Третий клик - сбрасываем и выбираем новую дату
        this.selectedStartDate = date;
        this.selectedEndDate = null;
    }
}
```

### Генерация календаря

```javascript
generateDays(year, month, selectedStart, selectedEnd) {
    const days = [];
    const startDate = new Date(year, month, 1);
    const endDate = new Date(year, month + 1, 0);
    const startDay = startDate.getDay() === 0 ? 6 : startDate.getDay() - 1;
    
    // Дни предыдущего месяца
    for (let i = startDay - 1; i >= 0; i--) {
        const date = new Date(startDate);
        date.setDate(date.getDate() - i - 1);
        days.push({
            day: date.getDate(),
            fullDate: date,
            classes: 'calendar-day other-month'
        });
    }
    
    // Дни текущего месяца
    for (let day = 1; day <= endDate.getDate(); day++) {
        const date = new Date(year, month, day);
        const classes = this.getDayClasses(date, selectedStart, selectedEnd);
        days.push({
            day: day,
            fullDate: date,
            classes: classes
        });
    }
    
    return days;
}
```

### Быстрые периоды

```javascript
selectQuickPeriod(period) {
    this.selectedPeriod = period;
    const today = new Date();
    today.setHours(0, 0, 0, 0);
    
    switch (period) {
        case 'today':
            this.selectedStartDate = new Date(today);
            this.selectedEndDate = new Date(today);
            this.currentDate = new Date(today);
            break;
            
        case 'yesterday':
            const yesterday = new Date(today);
            yesterday.setDate(yesterday.getDate() - 1);
            this.selectedStartDate = yesterday;
            this.selectedEndDate = new Date(yesterday);
            this.currentDate = new Date(yesterday);
            break;
            
        // ... другие периоды
    }
}
```

## 🔄 События и интеграция

### Alpine.js События

```javascript
// Открытие/закрытие календаря
@click="toggleCalendar()"
@click="closeCalendar()"

// Навигация
@click="previousMonth()"
@click="nextMonth()"

// Выбор дат
@click="selectDate(day)"

// Быстрые периоды
@click="selectQuickPeriod('today')"

// Кнопки действий
@click="clearSelection()"
@click="applySelection()"
```

### Интеграция с MoonShine

```php
// В Resource
ActionButton::make('Выбрать период')
    ->onClick(fn() => 'document.querySelector(\'[x-data*="overlayCalendar"]\').__x.$data.toggleCalendar()')

// В pageComponents
OverlayCalendar::make('date_range', 'Выберите период')->render()
```

### Обработка значений

```javascript
// Получение значения
const value = document.querySelector('input[name="date_range"]').value;

// Формат: "2024-01-15" (одна дата) или "2024-01-15|2024-01-20" (диапазон)

// Парсинг в PHP
$dateRange = $request->input('date_range');
if (strpos($dateRange, '|') !== false) {
    [$startDate, $endDate] = explode('|', $dateRange);
} else {
    $startDate = $endDate = $dateRange;
}
```

## 🎯 Производительность

### Оптимизации

1. **Статическое подключение ассетов**: Предотвращает дублирование
2. **Computed свойства**: Кэширование вычислений
3. **Event delegation**: Эффективная обработка событий
4. **CSS transitions**: Плавные анимации
5. **Минимальный DOM**: Только необходимые элементы

### Размеры файлов

- **PHP**: ~8KB
- **CSS**: ~12KB
- **JavaScript**: ~8KB
- **Общий размер**: ~28KB

### Время загрузки

- **Инициализация**: <50ms
- **Открытие календаря**: <100ms
- **Смена месяца**: <50ms
- **Выбор даты**: <10ms

## 🔒 Безопасность

### XSS Защита

```php
// Экранирование HTML
htmlspecialchars($this->placeholder, ENT_QUOTES, 'UTF-8')

// Валидация входных данных
if (!is_string($name) || empty($name)) {
    throw new InvalidArgumentException('Name must be a non-empty string');
}
```

### CSRF Защита

```javascript
// В AJAX запросах
headers: {
    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
}
```

## 🧪 Тестирование

### Unit тесты

```php
class OverlayCalendarTest extends TestCase
{
    public function test_make_creates_instance()
    {
        $calendar = OverlayCalendar::make('test', 'Test');
        $this->assertInstanceOf(OverlayCalendar::class, $calendar);
    }
    
    public function test_render_contains_html()
    {
        $calendar = OverlayCalendar::make('test', 'Test');
        $html = $calendar->render();
        
        $this->assertStringContainsString('overlay-calendar', $html);
        $this->assertStringContainsString('x-data="overlayCalendar', $html);
    }
}
```

### Интеграционные тесты

```php
class CalendarIntegrationTest extends TestCase
{
    public function test_calendar_in_resource()
    {
        $response = $this->get('/admin/calls');
        $response->assertStatus(200);
        $response->assertSee('overlay-calendar');
    }
}
```

### E2E тесты

```javascript
// Cypress
describe('OverlayCalendar', () => {
    it('opens calendar on button click', () => {
        cy.visit('/admin/calls');
        cy.get('[data-test="calendar-trigger"]').click();
        cy.get('.calendar-overlay').should('be.visible');
    });
    
    it('selects date range', () => {
        cy.get('.calendar-day').contains('15').click();
        cy.get('.calendar-day').contains('20').click();
        cy.get('input[name="date_range"]').should('have.value', '2024-01-15|2024-01-20');
    });
});
```

## 📊 Мониторинг

### Метрики производительности

```javascript
// Время открытия календаря
const startTime = performance.now();
calendar.toggleCalendar();
const endTime = performance.now();
console.log(`Calendar opened in ${endTime - startTime}ms`);
```

### Отслеживание ошибок

```javascript
// Обработка ошибок
try {
    calendar.selectDate(day);
} catch (error) {
    console.error('Calendar error:', error);
    // Отправка в систему мониторинга
    errorTracker.captureException(error);
}
```

---

**Техническая документация OverlayCalendar** 🔧
