class CalendarManager {
    constructor() {
        this.currentDate = new Date();
        this.daysGrid = document.getElementById("days-grid");
        this.monthLabel = document.getElementById("current-month-year");
        this.serverEvents = this.fetchEvents();
        
        this.init();
    }

    fetchEvents() {
        try {
            const dataElement = document.getElementById('calendar-data');
            return dataElement ? JSON.parse(dataElement.textContent) : [];
        } catch (e) {
            console.error("Error parsing calendar data:", e);
            return [];
        }
    }

    init() {
        if (this.daysGrid) {
            this.renderCalendar();
            this.initNavigation();
        }
        this.initViewSwitcher();
    }

    initNavigation() {
        document.getElementById("prev-month")?.addEventListener("click", () => {
            this.currentDate.setMonth(this.currentDate.getMonth() - 1);
            this.renderCalendar();
        });

        document.getElementById("next-month")?.addEventListener("click", () => {
            this.currentDate.setMonth(this.currentDate.getMonth() + 1);
            this.renderCalendar();
        });
    }

    renderCalendar() {
        const year = this.currentDate.getFullYear();
        const month = this.currentDate.getMonth();
        const realToday = new Date();
        realToday.setHours(0, 0, 0, 0);

        const monthNames = ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"];
        if(this.monthLabel) this.monthLabel.textContent = `${monthNames[month]} ${year}`;

        const firstDayOfMonth = new Date(year, month, 1).getDay();
        const startDayIndex = firstDayOfMonth === 0 ? 6 : firstDayOfMonth - 1; // Poniedziałek = 0
        const lastDateOfMonth = new Date(year, month + 1, 0).getDate();
        const lastDateOfPrevMonth = new Date(year, month, 0).getDate();

        let daysHTML = "";

        // Dni poprzedniego miesiąca
        for (let i = startDayIndex; i > 0; i--) {
            daysHTML += `<div class="day prev-date dimmed">${lastDateOfPrevMonth - i + 1}</div>`;
        }

        // Dni bieżącego miesiąca
        for (let i = 1; i <= lastDateOfMonth; i++) {
            const currentMonthStr = String(month + 1).padStart(2, '0');
            const currentDayStr = String(i).padStart(2, '0');
            const fullDate = `${year}-${currentMonthStr}-${currentDayStr}`;
            
            const renderDate = new Date(year, month, i);
            let classes = "day";
            
            if (renderDate.getTime() === realToday.getTime()) classes += " active-day";
            else if (renderDate < realToday) classes += " dimmed";

            const hasEvent = this.serverEvents.some(e => e.date === fullDate);
            const eventDot = hasEvent ? '<span class="dot"></span>' : '';

            // Globalny wrappera z ModalHandler
            daysHTML += `<div class="${classes}" onclick="openModalWithDate('addModal', '${fullDate}')" style="cursor:pointer; position: relative;">
                            ${i} ${eventDot}
                         </div>`;
        }

        // Dni następnego miesiąca (dopełnienie siatki)
        const totalRendered = startDayIndex + lastDateOfMonth;
        const nextDays = 42 - totalRendered; // 6 rzędów po 7 dni
        for (let i = 1; i <= nextDays; i++) {
            daysHTML += `<div class="day next-date dimmed">${i}</div>`;
        }

        this.daysGrid.innerHTML = daysHTML;
    }

    initViewSwitcher() {
        const calendarTab = document.getElementById('calendar-tab');
        const listTab = document.getElementById('list-tab');
        const calendarView = document.getElementById('calendar-view');
        const listView = document.getElementById('list-view-container');

        if (!calendarTab || !listTab) return;

        calendarTab.addEventListener('click', (e) => {
            e.preventDefault();
            calendarTab.classList.add('active');
            listTab.classList.remove('active');
            if(calendarView) calendarView.style.display = 'block';
            if(listView) listView.style.display = 'none';
        });

        listTab.addEventListener('click', (e) => {
            e.preventDefault();
            listTab.classList.add('active');
            calendarTab.classList.remove('active');
            if(calendarView) calendarView.style.display = 'none';
            if(listView) listView.style.display = 'block';
        });
    }
}

document.addEventListener("DOMContentLoaded", () => {
    new CalendarManager();
});