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
        readConfig() {
            const element = this.$el;
            const sqp = element.dataset.showQuickPeriods;
            const ssp = element.dataset.showSelectedPeriod;
            const smn = element.dataset.showMonthNames;
            return {
                placeholder: element.dataset.placeholder || 'Выберите дату',
                showQuickPeriods: sqp === undefined ? true : sqp === 'true',
                quickPeriods: element.dataset.quickPeriods ? element.dataset.quickPeriods.split(',') : ['today', 'yesterday', 'current_week', 'last_week', 'current_month', 'last_month'],
                primaryColor: element.dataset.primaryColor || '#3b82f6',
                rangeColor: element.dataset.rangeColor || '#10b981',
                showSelectedPeriod: ssp === undefined ? true : ssp === 'true',
                showMonthNames: smn === undefined ? true : smn === 'true'
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
        },
        
        weekDays: ['Пн', 'Вт', 'Ср', 'Чт', 'Пт', 'Сб', 'Вс'],
        monthNames: [
            'Январь', 'Февраль', 'Март', 'Апрель', 'Май', 'Июнь',
            'Июль', 'Август', 'Сентябрь', 'Октябрь', 'Ноябрь', 'Декабрь'
        ],
        
        
        get displayText() {
            if (this.selectedStartDate && this.selectedEndDate) {
                const start = this.formatDate(this.selectedStartDate);
                const end = this.formatDate(this.selectedEndDate);
                return start === end ? start : start + ' - ' + end;
            } else if (this.selectedStartDate) {
                return this.formatDate(this.selectedStartDate);
            }
            return this.config.placeholder;
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
                const start = this.formatDateForInput(this.selectedStartDate);
                const end = this.formatDateForInput(this.selectedEndDate);
                this.selectedValue = start + '|' + end;
            } else if (this.selectedStartDate) {
                this.selectedValue = this.formatDateForInput(this.selectedStartDate);
            }
            this.closeCalendar();
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
        
        formatDateForInput(date) {
            return date.getFullYear() + '-' + 
                   (date.getMonth() + 1).toString().padStart(2, '0') + '-' + 
                   date.getDate().toString().padStart(2, '0');
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
