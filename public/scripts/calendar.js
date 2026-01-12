document.addEventListener("DOMContentLoaded", () => {
    const daysGrid = document.getElementById("days-grid");
    const currentMonthYear = document.getElementById("current-month-year");
    const prevMonthBtn = document.getElementById("prev-month");
    const nextMonthBtn = document.getElementById("next-month");

    // pobranie danych o wydarzeniach
    let serverEvents = [];
    try {
        const dataElement = document.getElementById('calendar-data');
        if (dataElement) {
            serverEvents = JSON.parse(dataElement.textContent);
        }
    } catch (e) {
        console.error("Error parsing calendar data:", e);
    }

    let currentDate = new Date();

    function renderCalendar() {
        const year = currentDate.getFullYear();
        const month = currentDate.getMonth();

        // obiekt reprezentujący "dzisiaj" z wyzerowanym czasem do porównań
        const realToday = new Date();
        realToday.setHours(0, 0, 0, 0);

        const monthNames = [
            "January", "February", "March", "April", "May", "June",
            "July", "August", "September", "October", "November", "December"
        ];
        currentMonthYear.textContent = `${monthNames[month]} ${year}`;

        const firstDayOfMonth = new Date(year, month, 1).getDay();
        // poniedziałek jako pierwszy dzień tygodnia
        let startDayIndex = firstDayOfMonth === 0 ? 6 : firstDayOfMonth - 1;

        const lastDateOfMonth = new Date(year, month + 1, 0).getDate();
        const lastDateOfPrevMonth = new Date(year, month, 0).getDate();

        let daysHTML = "";

        // dni poprzedniego miesiąca
        for (let i = startDayIndex; i > 0; i--) {
            daysHTML += `<div class="day prev-date dimmed">${lastDateOfPrevMonth - i + 1}</div>`;
        }

        // dni aktualnego miesiąca
        for (let i = 1; i <= lastDateOfMonth; i++) {
            const currentMonthStr = String(month + 1).padStart(2, '0');
            const currentDayStr = String(i).padStart(2, '0');
            const fullDate = `${year}-${currentMonthStr}-${currentDayStr}`;

            // data dla iterowanego dnia, aby sprawdzić przeszłość/teraźniejszość
            const currentRenderDate = new Date(year, month, i);
            currentRenderDate.setHours(0, 0, 0, 0);

            let dayClasses = "day";

            if (currentRenderDate.getTime() === realToday.getTime()) {
                // dzisiejszy dzień podkreślony
                dayClasses += " active-day";
            } else if (currentRenderDate < realToday) {
                // minione dni wyblakłe
                dayClasses += " dimmed";
            }

            // sprawdzenie czy w tym dniu jest jakieś wydarzenie
            let hasEventHTML = "";
            const eventExists = serverEvents.some(event => event.date === fullDate);
            
            if (eventExists) {
                hasEventHTML = '<span class="dot"></span>';
            }

            // renderujemy dzień
            daysHTML += `<div class="${dayClasses}" onclick="openModalWithDate('addModal', '${fullDate}')" style="cursor:pointer; position: relative;">
                            ${i}
                            ${hasEventHTML}
                         </div>`;
        }

        // dni następnego miesiąca
        const totalDaysRendered = startDayIndex + lastDateOfMonth;
        const nextDays = 42 - totalDaysRendered;

        for (let i = 1; i <= nextDays; i++) {
             daysHTML += `<div class="day next-date dimmed">${i}</div>`;
        }

        daysGrid.innerHTML = daysHTML;
    }

    prevMonthBtn.addEventListener("click", () => {
        currentDate.setMonth(currentDate.getMonth() - 1);
        renderCalendar();
    });

    nextMonthBtn.addEventListener("click", () => {
        currentDate.setMonth(currentDate.getMonth() + 1);
        renderCalendar();
    });

    renderCalendar();
});

function openModalWithDate(modalId, dateStr) {
    const dateInput = document.getElementById('modalDateInput');
    if (dateInput) {
        dateInput.value = dateStr;
    }
    openModal(modalId);
}