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
        const todayDate = new Date().toLocaleDateString('sv-SE');

        // Użycie zdjęcia z obiektu lub domyślnego jeśli brak
        const imgPath = event.picture_url ? event.picture_url : 'public/img/others/default_pet.png';

        const cardHTML = `
            <div class="event-card" id="event-${event.id}">
                <div class="event-image-container">
                    <img src="${imgPath}" class="event-image">
                </div>
                <div class="event-details">
                    <div class="event-title">${event.title}</div>
                    <div class="event-pet-name">${event.pet_name} • ${event.date}</div>
                </div>
                <div class="event-time">${event.time || ''}</div>
                <img src="public/img/others/delete_calendar.png" 
                     class="bin-icon" 
                     onclick="prepareDelete('${event.id}', '/deleteEvent')">
            </div>
        `;

        if (event.date >= todayDate && listContainer) {
            const emptyMsg = listContainer.querySelector('.events');
            if (emptyMsg) emptyMsg.remove();
            listContainer.insertAdjacentHTML('afterbegin', cardHTML);
        }

        if (event.date === todayDate && todayContainer) {
            const emptyToday = todayContainer.querySelector('.noevents');
            if (emptyToday) emptyToday.remove();
            // Kopia dla listy today's events z innym ID, by nie duplikować ID w DOM
            const todayCardHTML = cardHTML.replace(`id="event-${event.id}"`, `id="today-event-${event.id}"`);
            todayContainer.insertAdjacentHTML('afterbegin', todayCardHTML);
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
                
                // Usunięcie z tablicy serverEvents (by kropka zniknęła po renderze)
                this.serverEvents = this.serverEvents.filter(e => e.id != eventId);
                this.renderCalendar();

                // Usunięcie z DOM we wszystkich widokach
                document.getElementById(`event-${eventId}`)?.remove();
                document.getElementById(`today-event-${eventId}`)?.remove();
                
                // Sprawdzenie czy listy nie są puste, by przywrócić komunikaty "No events"
                this.checkEmptyLists();

            } else {
                alert("Error deleting event");
            }
        } catch (error) {
            console.error("Fetch Error:", error);
        }
    }

    checkEmptyLists() {
        const listContainer = document.getElementById('list-view-container');
        if (listContainer && listContainer.querySelectorAll('.event-card').length === 0) {
            listContainer.innerHTML = '<p class="events">There are no upcoming events!</p>';
        }
        const todayContainer = document.getElementById('today-events-list');
        if (todayContainer && todayContainer.querySelectorAll('.event-card').length === 0) {
            todayContainer.innerHTML = '<div class="noevents">No events for today.</div>';
        }
    }

    initNavigation() {
        const prevBtn = document.getElementById("prev-month");
        const nextBtn = document.getElementById("next-month");

        prevBtn?.addEventListener("click", () => {
            this.currentDate.setMonth(this.currentDate.getMonth() - 1);
            this.renderCalendar();
        });

        nextBtn?.addEventListener("click", () => {
            this.currentDate.setMonth(this.currentDate.getMonth() + 1);
            this.renderCalendar();
        });
    }

    renderCalendar() {
        if (!this.daysGrid) return;

        const year = this.currentDate.getFullYear();
        const month = this.currentDate.getMonth();
        const realToday = new Date();
        realToday.setHours(0, 0, 0, 0);

        const monthNames = ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"];
        if (this.monthLabel) this.monthLabel.textContent = `${monthNames[month]} ${year}`;

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
            const currentMonthStr = String(month + 1).padStart(2, '0');
            const currentDayStr = String(i).padStart(2, '0');
            const fullDate = `${year}-${currentMonthStr}-${currentDayStr}`;
            
            const renderDate = new Date(year, month, i);
            let classes = "day";
            
            if (renderDate.getTime() === realToday.getTime()) classes += " active-day";
            else if (renderDate < realToday) classes += " dimmed";

            const hasEvent = this.serverEvents.some(e => e.date === fullDate);
            const eventDot = hasEvent ? '<span class="dot"></span>' : '';

            daysHTML += `<div class="${classes}" onclick="openModalWithDate('addModal', '${fullDate}')" style="cursor:pointer; position: relative;">
                            ${i} ${eventDot}
                         </div>`;
        }

        // Dni następnego miesiąca (dopełnienie siatki)
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

        // Jeśli brakuje elementów w HTML, przerywamy by nie sypać błędami w konsoli
        if (!calendarTab || !listTab || !calendarView || !listView) return;

        calendarTab.addEventListener('click', (e) => {
            e.preventDefault();
            calendarTab.classList.add('active');
            listTab.classList.remove('active');
            calendarView.style.display = 'block';
            listView.style.display = 'none';
        });

        listTab.addEventListener('click', (e) => {
            e.preventDefault();
            listTab.classList.add('active');
            calendarTab.classList.remove('active');
            calendarView.style.display = 'none';
            listView.style.display = 'block';
        });
    }
}

// Inicjalizacja globalna
let calendarManager;
document.addEventListener("DOMContentLoaded", () => {
    calendarManager = new CalendarManager();
});

// Eksporty dla onclick w HTML
window.deleteEvent = () => calendarManager.deleteEventAsync();