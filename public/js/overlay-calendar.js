// Overlay Calendar Alpine.js Component
function overlayCalendar() {
    return {
        isOpen: false,
        currentDate: new Date(),
        selectedStartDate: null,
        selectedEndDate: null,
        selectedValue: '',
        selectedPeriod: null,
        
        // Снапшот конфигурации на момент инициализации
        config: null,
        // Управление внутренним триггером и внешними биндингами
        showInternalTrigger: true,
        externalTriggerSelector: null,
        externalTriggerEvent: 'click',
        targetSelector: null,
        targetStartSelector: null,
        targetEndSelector: null,
        _externalListenersBound: false,
        readConfig() {
            const element = this.$el;
            const sqp = element.dataset.showQuickPeriods;
            const ssp = element.dataset.showSelectedPeriod;
            const smn = element.dataset.showMonthNames;
            const sit = element.dataset.internalTrigger;
            const sdo = element.dataset.singleDateOnly;
            this.showInternalTrigger = sit === undefined ? true : sit === 'true';
            // Внешние параметры
            this.externalTriggerSelector = element.dataset.triggerSelector || null;
            this.externalTriggerEvent = element.dataset.triggerEvent || 'click';
            this.targetSelector = element.dataset.targetSelector || null;
            this.targetStartSelector = element.dataset.targetStart || null;
            this.targetEndSelector = element.dataset.targetEnd || null;
            const outFmt = element.dataset.outputFormat || 'Y-m-d';
            const rangeDelim = element.dataset.rangeDelimiter || '|';
            const rangeDelimValue = element.dataset.rangeDelimiterValue || rangeDelim;
            const includeEod = element.dataset.includeEndOfDay === 'true';
            const inputClasses = element.dataset.inputClasses || '';
            const inputStyles = element.dataset.inputStyles || '';
            const inputIcon = element.dataset.inputIcon || '<svg class="calendar-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>';
            const inputPlaceholder = element.dataset.inputPlaceholder || '';
            const inputIconColor = element.dataset.inputIconColor || '';
            return {
                placeholder: element.dataset.placeholder || 'Выберите дату',
                showQuickPeriods: sdo === 'true' ? false : (sqp === undefined ? true : sqp === 'true'),
                quickPeriods: element.dataset.quickPeriods ? element.dataset.quickPeriods.split(',') : ['today', 'yesterday', 'current_week', 'last_week', 'current_month', 'last_month'],
                primaryColor: element.dataset.primaryColor || '#3b82f6',
                rangeColor: element.dataset.rangeColor || '#10b981',
                showSelectedPeriod: ssp === undefined ? true : ssp === 'true',
                showMonthNames: smn === undefined ? true : smn === 'true',
                singleDateOnly: sdo === 'true',
                outputFormat: outFmt,
                rangeDelimiter: rangeDelim,
                rangeDelimiterForValue: rangeDelimValue,
                includeEndOfDay: includeEod,
                inputClasses: inputClasses,
                inputStyles: inputStyles,
                inputIcon: inputIcon,
                inputPlaceholder: inputPlaceholder,
                inputIconColor: inputIconColor
            };
        },
        
        init() {
            // Снимаем конфигурацию один раз на экземпляр
            this.config = this.readConfig();
            // Переводы из data-атрибутов (JSON)
            const el = this.$el;
            try {
                if (el.dataset.weekdays) {
                    this.weekDays = JSON.parse(el.dataset.weekdays);
                }
                if (el.dataset.months) {
                    this.monthNames = JSON.parse(el.dataset.months);
                }
                if (el.dataset.labels) {
                    this.config.labels = JSON.parse(el.dataset.labels);
                }
            } catch (e) { /* ignore */ }
            if (!this.weekDays) this.weekDays = ['Пн','Вт','Ср','Чт','Пт','Сб','Вс'];
            if (!this.monthNames) this.monthNames = ['Январь','Февраль','Март','Апрель','Май','Июнь','Июль','Август','Сентябрь','Октябрь','Ноябрь','Декабрь'];
            // Цвета задаются инлайн стилями у корня

            // Назначаем внешний триггер, если задан
            if (this.externalTriggerSelector && !this._externalListenersBound) {
                try {
                    const triggers = document.querySelectorAll(this.externalTriggerSelector);
                    triggers.forEach((t) => {
                        t.addEventListener(this.externalTriggerEvent, this.toggleCalendar.bind(this));
                    });
                    this._externalListenersBound = true;
                } catch (e) { /* ignore */ }
            }
        },
        
        weekDays: ['Пн', 'Вт', 'Ср', 'Чт', 'Пт', 'Сб', 'Вс'],
        monthNames: [
            'Январь', 'Февраль', 'Март', 'Апрель', 'Май', 'Июнь',
            'Июль', 'Август', 'Сентябрь', 'Октябрь', 'Ноябрь', 'Декабрь'
        ],
        
        
        get displayText() {
            if (this.selectedStartDate && this.selectedEndDate) {
                let endDateForOutput = new Date(this.selectedEndDate);
                if (this.config.includeEndOfDay) {
                    endDateForOutput.setHours(23, 59, 59, 999);
                }
                const start = this.formatDateByPattern(this.selectedStartDate, this.config.outputFormat);
                const end = this.formatDateByPattern(endDateForOutput, this.config.outputFormat);
                return start === end ? start : start + this.config.rangeDelimiter + end;
            } else if (this.selectedStartDate) {
                return this.formatDateByPattern(this.selectedStartDate, this.config.outputFormat);
            }
            return this.config.inputPlaceholder || this.config.placeholder;
        },
        
        get currentMonthYear() {
            if (this.config.showMonthNames) {
                return this.monthNames[this.currentDate.getMonth()] + ' ' + this.currentDate.getFullYear();
            } else {
                return (this.currentDate.getMonth() + 1) + '.' + this.currentDate.getFullYear();
            }
        },
        
        get hasSelection() {
            return this.selectedStartDate !== null;
        },
        
        get calendarDays() {
            return this.generateDays(
                this.currentDate.getFullYear(),
                this.currentDate.getMonth(),
                this.selectedStartDate,
                this.selectedEndDate
            );
        },
        
        generateDays(year, month, selectedStart, selectedEnd) {
            const firstDay = new Date(year, month, 1);
            const startDate = new Date(firstDay);
            
            // Начинаем с понедельника
            const dayOfWeek = firstDay.getDay();
            const mondayOffset = dayOfWeek === 0 ? 6 : dayOfWeek - 1;
            startDate.setDate(startDate.getDate() - mondayOffset);
            
            const days = [];
            const today = new Date();
            today.setHours(0, 0, 0, 0);
            
            for (let i = 0; i < 42; i++) {
                const date = new Date(startDate);
                date.setDate(startDate.getDate() + i);
                
                const isCurrentMonth = date.getMonth() === month;
                const isToday = this.isToday(date);
                const isWeekend = date.getDay() === 0 || date.getDay() === 6;
                
                let classes = 'calendar-day';
                
                if (!isCurrentMonth) {
                    classes += ' other-month';
                } else if (isToday) {
                    classes += ' today';
                } else if (isWeekend) {
                    classes += ' weekend';
                } else {
                    classes += ' normal-day';
                }
                
                // Проверяем выбор
                if (selectedStart && selectedEnd) {
                    const dateTime = date.getTime();
                    const startTime = selectedStart.getTime();
                    const endTime = selectedEnd.getTime();
                    
                    if (dateTime === startTime || dateTime === endTime) {
                        classes += ' range-start-end';
                    } else if (dateTime > startTime && dateTime < endTime) {
                        classes += ' in-range';
                    }
                } else if (selectedStart && this.isSameDay(date, selectedStart)) {
                    classes += ' selected';
                }
                
                days.push({
                    date: date.toISOString().split('T')[0],
                    day: date.getDate(),
                    fullDate: date,
                    classes: classes
                });
            }
            
            return days;
        },
        
        toggleCalendar() {
            this.isOpen = !this.isOpen;
            this.toggleBodyScroll();
        },
        
        closeCalendar() {
            this.isOpen = false;
            this.toggleBodyScroll();
        },
        
        toggleBodyScroll() {
            if (this.isOpen) {
                // Сохраняем текущую позицию, фиксируем body и компенсируем top
                this._scrollY = window.scrollY || document.documentElement.scrollTop || 0;
                document.body.classList.add('calendar-open');
                document.body.style.position = 'fixed';
                document.body.style.top = `-${this._scrollY}px`;
                document.body.style.left = '0';
                document.body.style.right = '0';
                document.body.style.width = '100%';
            } else {
                document.body.classList.remove('calendar-open');
                const y = this._scrollY || 0;
                // Сбрасываем стили до возврата
                document.body.style.position = '';
                document.body.style.top = '';
                document.body.style.left = '';
                document.body.style.right = '';
                document.body.style.width = '';
                // Возвращаем прокрутку туда, где была
                window.scrollTo(0, y);
            }
        },
        
        previousMonth() {
            this.currentDate = new Date(this.currentDate.getFullYear(), this.currentDate.getMonth() - 1, 1);
        },
        
        nextMonth() {
            this.currentDate = new Date(this.currentDate.getFullYear(), this.currentDate.getMonth() + 1, 1);
        },
        
        
        
        selectDate(day) {
            const date = day.fullDate;
            
            // Сбрасываем подсветку быстрых периодов при выборе даты в календаре
            this.selectedPeriod = null;
            
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
                    // Если одиночная дата — не разрешаем диапазон
                    if (this.config.singleDateOnly) {
                        this.selectedStartDate = date;
                        this.selectedEndDate = null;
                        return;
                    }
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
        },
        
        selectQuickPeriod(period) {
            this.selectedPeriod = period;
            const today = new Date();
            today.setHours(0, 0, 0, 0);
            
            switch (period) {
                case 'today':
                    this.selectedStartDate = new Date(today);
                    this.selectedEndDate = new Date(today);
                    // Переходим к текущему месяцу
                    this.currentDate = new Date(today);
                    break;
                    
                case 'yesterday':
                    const yesterday = new Date(today);
                    yesterday.setDate(yesterday.getDate() - 1);
                    this.selectedStartDate = yesterday;
                    this.selectedEndDate = new Date(yesterday);
                    // Переходим к месяцу вчерашнего дня
                    this.currentDate = new Date(yesterday);
                    break;
                    
                case 'current_week':
                    const startOfWeek = new Date(today);
                    const dayOfWeek = today.getDay();
                    const mondayOffset = dayOfWeek === 0 ? 6 : dayOfWeek - 1;
                    startOfWeek.setDate(today.getDate() - mondayOffset);
                    this.selectedStartDate = startOfWeek;
                    this.selectedEndDate = new Date(startOfWeek);
                    this.selectedEndDate.setDate(startOfWeek.getDate() + 6);
                    // Переходим к месяцу начала недели
                    this.currentDate = new Date(startOfWeek);
                    break;
                    
                case 'last_week':
                    const lastWeekStart = new Date(today);
                    const lastWeekDayOfWeek = today.getDay();
                    const lastWeekMondayOffset = lastWeekDayOfWeek === 0 ? 6 : lastWeekDayOfWeek - 1;
                    lastWeekStart.setDate(today.getDate() - lastWeekMondayOffset - 7);
                    this.selectedStartDate = lastWeekStart;
                    this.selectedEndDate = new Date(lastWeekStart);
                    this.selectedEndDate.setDate(lastWeekStart.getDate() + 6);
                    // Переходим к месяцу начала прошлой недели
                    this.currentDate = new Date(lastWeekStart);
                    break;
                    
                case 'current_month':
                    const startOfMonth = new Date(today.getFullYear(), today.getMonth(), 1);
                    const endOfMonth = new Date(today.getFullYear(), today.getMonth() + 1, 0);
                    this.selectedStartDate = startOfMonth;
                    this.selectedEndDate = endOfMonth;
                    // Переходим к текущему месяцу
                    this.currentDate = new Date(today);
                    break;
                    
                case 'last_month':
                    const startOfLastMonth = new Date(today.getFullYear(), today.getMonth() - 1, 1);
                    const endOfLastMonth = new Date(today.getFullYear(), today.getMonth(), 0);
                    this.selectedStartDate = startOfLastMonth;
                    this.selectedEndDate = endOfLastMonth;
                    // Переходим к прошлому месяцу
                    this.currentDate = new Date(today.getFullYear(), today.getMonth() - 1, 1);
                    break;
                    
                case 'custom':
                    // Сбрасываем выбор для произвольного периода
                    this.selectedStartDate = null;
                    this.selectedEndDate = null;
                    break;
            }
        },
        
        clearSelection() {
            this.selectedStartDate = null;
            this.selectedEndDate = null;
            this.selectedValue = '';
            this.selectedPeriod = null;
            // Возвращаем календарь к текущему месяцу
            this.currentDate = new Date();
        },
        
        applySelection() {
            if (this.selectedStartDate && this.selectedEndDate) {
                let endDateForOutput = new Date(this.selectedEndDate);
                if (this.config.includeEndOfDay) {
                    endDateForOutput.setHours(23, 59, 59, 999);
                }
                const start = this.formatDateByPattern(this.selectedStartDate, this.config.outputFormat);
                const end = this.formatDateByPattern(endDateForOutput, this.config.outputFormat);
                this.selectedValue = start + this.config.rangeDelimiterForValue + end;
                this._writeToTargets(start, end);
            } else if (this.selectedStartDate) {
                this.selectedValue = this.formatDateByPattern(this.selectedStartDate, this.config.outputFormat);
                this._writeToTargets(this.selectedValue, null);
            }
            this.closeCalendar();
        },

        _writeToTargets(startValue, endValue) {
            const getWritableElement = (selector) => {
                if (!selector) return null;
                let el = null;
                try { el = document.querySelector(selector); } catch(e) { el = null; }
                if (!el) return null;
                // Если это не поле ввода, попробуем найти вложенный input/textarea
                const isWritable = (node) => node && (node.tagName === 'INPUT' || node.tagName === 'TEXTAREA' || 'value' in node);
                if (!isWritable(el)) {
                    const nested = el.querySelector('input, textarea');
                    if (nested) return nested;
                }
                return el;
            };
            // Если заданы два целевых инпута (диапазон)
            if (this.targetStartSelector || this.targetEndSelector) {
                const startEl = getWritableElement(this.targetStartSelector);
                const endEl = getWritableElement(this.targetEndSelector);
                if (startEl) startEl.value = startValue || '';
                if (endEl) endEl.value = endValue || '';
                if (startEl) {
                    startEl.dispatchEvent(new Event('input', { bubbles: true }));
                    startEl.dispatchEvent(new Event('change', { bubbles: true }));
                }
                if (endEl) {
                    endEl.dispatchEvent(new Event('input', { bubbles: true }));
                    endEl.dispatchEvent(new Event('change', { bubbles: true }));
                }
                return;
            }
            // Иначе — единый инпут/элемент
            if (this.targetSelector) {
                const el = getWritableElement(this.targetSelector);
                if (el) {
                    const value = endValue ? `${startValue}${this.config.rangeDelimiter}${endValue}` : startValue || '';
                    if ('value' in el) {
                        el.value = value;
                        el.dispatchEvent(new Event('input', { bubbles: true }));
                        el.dispatchEvent(new Event('change', { bubbles: true }));
                    } else {
                        el.textContent = value;
                    }
                }
            }
        },
        
        getSelectedPeriodText() {
            if (this.selectedStartDate && this.selectedEndDate) {
                const start = this.formatDate(this.selectedStartDate);
                const end = this.formatDate(this.selectedEndDate);
                // Если даты одинаковые, показываем только одну
                if (start === end) {
                    return start;
                }
                return start + ' — ' + end;
            } else if (this.selectedStartDate) {
                // Показываем только одну дату
                return this.formatDate(this.selectedStartDate);
            } else {
                // Показываем текущий день по умолчанию
                const today = new Date();
                return this.formatDate(today);
            }
        },
        
        formatDate(date) {
            return date.getDate().toString().padStart(2, '0') + '.' + 
                   (date.getMonth() + 1).toString().padStart(2, '0') + '.' + 
                   date.getFullYear();
        },
        
        formatDateForInput(date) { // сохраним для обратной совместимости
            return date.getFullYear() + '-' + 
                   (date.getMonth() + 1).toString().padStart(2, '0') + '-' + 
                   date.getDate().toString().padStart(2, '0');
        },

        formatDateByPattern(date, pattern) {
            // Поддерживаем Y, m, d, H, i, s
            const Y = date.getFullYear().toString();
            const m = (date.getMonth() + 1).toString().padStart(2, '0');
            const d = date.getDate().toString().padStart(2, '0');
            const H = date.getHours().toString().padStart(2, '0');
            const i = date.getMinutes().toString().padStart(2, '0');
            const s = date.getSeconds().toString().padStart(2, '0');
            return (pattern || 'Y-m-d')
                .replace(/Y/g, Y)
                .replace(/m/g, m)
                .replace(/d/g, d)
                .replace(/H/g, H)
                .replace(/i/g, i)
                .replace(/s/g, s);
        },
        
        isToday(date) {
            const today = new Date();
            today.setHours(0, 0, 0, 0);
            return this.isSameDay(date, today);
        },
        
        getPeriodLabel(period) {
            const labels = (this.config && this.config.labels) ? this.config.labels : {
                'today': 'Сегодня',
                'yesterday': 'Вчера',
                'current_week': 'Текущая неделя',
                'last_week': 'Прошлая неделя',
                'current_month': 'Текущий месяц',
                'last_month': 'Прошлый месяц'
            };
            return labels[period] || period;
        },
        
        isSameDay(date1, date2) {
            return date1.getTime() === date2.getTime();
        }
    }
}
