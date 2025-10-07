// Утилиты для работы с датами
const DateUtils = {
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
    
    isSameDay(date1, date2) {
        return date1.getTime() === date2.getTime();
    },
    
    isToday(date) {
        const today = new Date();
        today.setHours(0, 0, 0, 0);
        return this.isSameDay(date, today);
    },
    
    isWeekend(date) {
        return date.getDay() === 0 || date.getDay() === 6;
    }
};

// Генератор дней календаря
const CalendarGenerator = {
    generateDays(year, month, selectedStart, selectedEnd) {
        const firstDay = new Date(year, month, 1);
        const startDate = new Date(firstDay);
        
        // Начинаем с понедельника
        const dayOfWeek = firstDay.getDay();
        const mondayOffset = dayOfWeek === 0 ? 6 : dayOfWeek - 1;
        startDate.setDate(startDate.getDate() - mondayOffset);
        
        const days = [];
        
        for (let i = 0; i < 42; i++) {
            const date = new Date(startDate);
            date.setDate(startDate.getDate() + i);
            
            const isCurrentMonth = date.getMonth() === month;
            const isToday = DateUtils.isToday(date);
            const isWeekend = DateUtils.isWeekend(date);
            
            // Определяем классы для Tailwind
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
            } else if (selectedStart && DateUtils.isSameDay(date, selectedStart)) {
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
    }
};

// Основной Alpine.js компонент
function customCalendar(placeholder = 'Выберите дату') {
    return {
        isOpen: false,
        currentDate: new Date(),
        selectedStartDate: null,
        selectedEndDate: null,
        selectedValue: '',
        placeholder: placeholder,
        
        weekDays: ['Пн', 'Вт', 'Ср', 'Чт', 'Пт', 'Сб', 'Вс'],
        monthNames: [
            'Январь', 'Февраль', 'Март', 'Апрель', 'Май', 'Июнь',
            'Июль', 'Август', 'Сентябрь', 'Октябрь', 'Ноябрь', 'Декабрь'
        ],
        
        get displayText() {
            if (this.selectedStartDate && this.selectedEndDate) {
                const start = DateUtils.formatDate(this.selectedStartDate);
                const end = DateUtils.formatDate(this.selectedEndDate);
                return start === end ? start : start + ' - ' + end;
            } else if (this.selectedStartDate) {
                return DateUtils.formatDate(this.selectedStartDate);
            }
            return this.placeholder;
        },
        
        get currentMonthYear() {
            return this.monthNames[this.currentDate.getMonth()] + ' ' + this.currentDate.getFullYear();
        },
        
        get calendarDays() {
            return CalendarGenerator.generateDays(
                this.currentDate.getFullYear(),
                this.currentDate.getMonth(),
                this.selectedStartDate,
                this.selectedEndDate
            );
        },
        
        toggleCalendar() {
            this.isOpen = !this.isOpen;
        },
        
        closeCalendar() {
            this.isOpen = false;
        },
        
        previousMonth() {
            this.currentDate.setMonth(this.currentDate.getMonth() - 1);
        },
        
        nextMonth() {
            this.currentDate.setMonth(this.currentDate.getMonth() + 1);
        },
        
        selectDate(day) {
            const date = day.fullDate;
            
            if (!this.selectedStartDate) {
                // Первый клик - выбираем начальную дату
                this.selectedStartDate = date;
                this.selectedEndDate = null;
            } else if (!this.selectedEndDate) {
                // Второй клик - выбираем конечную дату
                if (date.getTime() < this.selectedStartDate.getTime()) {
                    this.selectedEndDate = this.selectedStartDate;
                    this.selectedStartDate = date;
                } else {
                    this.selectedEndDate = date;
                }
            } else {
                // Третий клик - сбрасываем и выбираем новую дату
                this.selectedStartDate = date;
                this.selectedEndDate = null;
            }
        },
        
        clearSelection() {
            this.selectedStartDate = null;
            this.selectedEndDate = null;
            this.selectedValue = '';
        },
        
        applySelection() {
            if (this.selectedStartDate && this.selectedEndDate) {
                const start = DateUtils.formatDateForInput(this.selectedStartDate);
                const end = DateUtils.formatDateForInput(this.selectedEndDate);
                this.selectedValue = start + '|' + end;
            } else if (this.selectedStartDate) {
                this.selectedValue = DateUtils.formatDateForInput(this.selectedStartDate);
            }
            this.closeCalendar();
        }
    }
}
