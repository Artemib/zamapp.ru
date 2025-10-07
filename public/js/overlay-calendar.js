// Overlay Calendar Alpine.js Component
function overlayCalendar(placeholder = 'Выберите дату') {
    return {
        isOpen: false,
        currentDate: new Date(),
        selectedStartDate: null,
        selectedEndDate: null,
        selectedValue: '',
        selectedPeriod: null,
        placeholder: placeholder,
        
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
            return this.placeholder;
        },
        
        get currentMonthYear() {
            return this.monthNames[this.currentDate.getMonth()] + ' ' + this.currentDate.getFullYear();
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
                document.body.classList.add('calendar-open');
                // Предотвращаем скролл на мобильных
                document.body.style.overflow = 'hidden';
                document.body.style.position = 'fixed';
                document.body.style.width = '100%';
                document.body.style.height = '100%';
            } else {
                document.body.classList.remove('calendar-open');
                // Восстанавливаем скролл
                document.body.style.overflow = '';
                document.body.style.position = '';
                document.body.style.width = '';
                document.body.style.height = '';
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
                    
                case 'currentWeek':
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
                    
                case 'lastWeek':
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
                    
                case 'currentMonth':
                    const startOfMonth = new Date(today.getFullYear(), today.getMonth(), 1);
                    const endOfMonth = new Date(today.getFullYear(), today.getMonth() + 1, 0);
                    this.selectedStartDate = startOfMonth;
                    this.selectedEndDate = endOfMonth;
                    // Переходим к текущему месяцу
                    this.currentDate = new Date(today);
                    break;
                    
                case 'lastMonth':
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
                return 'С ' + start + ' по ' + end;
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
        
        isSameDay(date1, date2) {
            return date1.getTime() === date2.getTime();
        }
    }
}
