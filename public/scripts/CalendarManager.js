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

    async addEventAsync() {
        const form = document.querySelector('#addModal form');
        if (!form) return;
        
        const formData = new FormData(form);
        
        try {
            const response = await fetch('/addEvent', {
                method: 'POST',
                body: formData
            });
            const result = await response.json();
            
            if (result.success) {
                const newEvent = {
                    id: result.id,
                    title: formData.get('name'),
                    date: formData.get('date'),
                    time: formData.get('time'),
                    pet_id: formData.get('pet_id'),
                    pet_name: form.querySelector('select[name="pet_id"] option:checked').text,
                    picture_url: result.picture_url 
                };
                
                this.serverEvents.push(newEvent);
                this.renderCalendar();
                this.injectEventIntoLists(newEvent);
                
                form.reset();
                if (window.closeModal) window.closeModal('addModal');
            } else {
                alert("Error: " + (result.message || "Unknown error"));
            }
        } catch (error) {
            console.error("Fetch Error:", error);
        }
    }

    injectEventIntoLists(event) {
        const listContainer = document.getElementById('list-view-container');
        const todayContainer = document.getElementById('today-events-list');
        const todayDate = DateUtils.toISODate(new Date());
        
        const createEventCard = (idPrefix = 'event') => {
            const imgPath = event.picture_url || 'public/img/others/default_pet.png';
            const displayDate = DateUtils.formatDisplayDate(event.date);
            
            return `
                <div class="event-card" id="${idPrefix}-${event.id}">
                    <div class="event-image-container">
                        <img src="${imgPath}" class="event-image">
                    </div>
                    <div class="event-details">
                        <div class="event-title">${event.title}</div>
                        <div class="event-pet-name">${event.pet_name} • ${displayDate}</div>
                    </div>
                    <div class="event-time">${event.time || ''}</div>
                    <img src="public/img/others/delete_calendar.png" 
                         class="bin-icon" 
                         onclick="prepareDelete('${event.id}', '/deleteEvent')">
                </div>
            `;
        };
        
        if (event.date >= todayDate && listContainer) {
            const emptyMsg = listContainer.querySelector('.events');
            if (emptyMsg) emptyMsg.remove();
            listContainer.insertAdjacentHTML('afterbegin', createEventCard());
        }
        
        if (event.date === todayDate && todayContainer) {
            const emptyToday = todayContainer.querySelector('.noevents');
            if (emptyToday) emptyToday.remove();
            todayContainer.insertAdjacentHTML('afterbegin', createEventCard('today-event'));
        }
    }

    async deleteEventAsync() {
        const idInput = document.getElementById('modalItemId');
        if (!idInput || !idInput.value) return;
        
        const eventId = idInput.value;
        const formData = new FormData();
        formData.append('id', eventId);
        
        try {
            const response = await fetch('/deleteEvent', {
                method: 'POST',
                body: formData
            });
            const result = await response.json();
            
            if (result.success) {
                if (window.closeModal) window.closeModal('deleteModal');
                
                this.serverEvents = this.serverEvents.filter(e => e.id != eventId);
                this.renderCalendar();
                
                document.getElementById(`event-${eventId}`)?.remove();
                document.getElementById(`today-event-${eventId}`)?.remove();
                
                this.checkEmptyLists();
            } else {
                alert("Error deleting event");
            }
        } catch (error) {
            console.error("Fetch Error:", error);
        }
    }

    checkEmptyLists() {
        const containers = [
            { id: 'list-view-container', msg: '<p class="events">There are no upcoming events!</p>' },
            { id: 'today-events-list', msg: '<div class="noevents">No events for today.</div>' }
        ];
        
        containers.forEach(({ id, msg }) => {
            const container = document.getElementById(id);
            if (container && container.querySelectorAll('.event-card').length === 0) {
                container.innerHTML = msg;
            }
        });
    }

    initNavigation() {
        const navButtons = [
            { id: "prev-month", direction: -1 },
            { id: "next-month", direction: 1 }
        ];
        
        navButtons.forEach(({ id, direction }) => {
            document.getElementById(id)?.addEventListener("click", () => {
                this.currentDate.setMonth(this.currentDate.getMonth() + direction);
                this.renderCalendar();
            });
        });
    }

    renderCalendar() {
        if (!this.daysGrid) return;
        
        const year = this.currentDate.getFullYear();
        const month = this.currentDate.getMonth();
        const realToday = new Date();
        realToday.setHours(0, 0, 0, 0);
        
        const monthNames = ["January", "February", "March", "April", "May", "June", 
                           "July", "August", "September", "October", "November", "December"];
        
        if (this.monthLabel) {
            this.monthLabel.textContent = `${monthNames[month]} ${year}`;
        }
        
        const firstDayOfMonth = new Date(year, month, 1).getDay();
        const startDayIndex = firstDayOfMonth === 0 ? 6 : firstDayOfMonth - 1;
        const lastDateOfMonth = new Date(year, month + 1, 0).getDate();
        const lastDateOfPrevMonth = new Date(year, month, 0).getDate();
        
        let daysHTML = "";
        
        // Dni poprzedniego miesiąca
        for (let i = startDayIndex; i > 0; i--) {
            daysHTML += `<div class="day prev-date dimmed">${lastDateOfPrevMonth - i + 1}</div>`;
        }
        
        // Dni bieżącego miesiąca
        for (let i = 1; i <= lastDateOfMonth; i++) {
            const fullDate = DateUtils.toISODate(new Date(year, month, i));
            const renderDate = new Date(year, month, i);
            
            let classes = "day";
            if (renderDate.getTime() === realToday.getTime()) {
                classes += " active-day";
            } else if (renderDate < realToday) {
                classes += " dimmed";
            }
            
            const hasEvent = this.serverEvents.some(e => e.date === fullDate);
            const eventDot = hasEvent ? '<span class="dot"></span>' : '';
            
            daysHTML += `<div class="${classes}" onclick="openModalWithDate('addModal', '${fullDate}')" 
                              style="cursor:pointer; position: relative;">
                            ${i} ${eventDot}
                         </div>`;
        }
        
        // Dni następnego miesiąca
        const totalRendered = startDayIndex + lastDateOfMonth;
        const nextDays = 42 - totalRendered;
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
        
        if (!calendarTab || !listTab || !calendarView || !listView) return;
        
        const switchView = (activeTab, inactiveTab, showView, hideView) => {
            activeTab.classList.add('active');
            inactiveTab.classList.remove('active');
            showView.style.display = 'block';
            hideView.style.display = 'none';
        };
        
        calendarTab.addEventListener('click', (e) => {
            e.preventDefault();
            switchView(calendarTab, listTab, calendarView, listView);
        });
        
        listTab.addEventListener('click', (e) => {
            e.preventDefault();
            switchView(listTab, calendarTab, listView, calendarView);
        });
    }
}

let calendarManager;
document.addEventListener("DOMContentLoaded", () => {
    calendarManager = new CalendarManager();
});

window.deleteEvent = () => calendarManager.deleteEventAsync();