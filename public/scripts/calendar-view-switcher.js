document.addEventListener('DOMContentLoaded', function() {
    const calendarButton = document.getElementById('calendar-button');
    const listButton = document.getElementById('list-button');
    const calendarWidget = document.getElementById('calendar-widget');
    const listContainer = document.getElementById('list-view-container');

    // początkowy stan: Widok kalendarza jest aktywny.
    function initializeView() {
        calendarWidget.style.display = 'block';
        listContainer.style.display = 'none';
        calendarButton.classList.add('active');
        listButton.classList.remove('active');
    }

    // przełączenie na widok kalendarza
    function switchToCalendar(e) {
        if (e) e.preventDefault();
        calendarButton.classList.add('active');
        listButton.classList.remove('active');
        calendarWidget.style.display = 'block';
        listContainer.style.display = 'none';
    }

    // przełączenie na widok listy
    function switchToList(e) {
        if (e) e.preventDefault();
        listButton.classList.add('active');
        calendarButton.classList.remove('active');
        calendarWidget.style.display = 'none';
        listContainer.style.display = 'block';
    }

    // nasłuchiwanie zdarzeń do przycisków
    if (calendarButton && listButton) {
        calendarButton.addEventListener('click', switchToCalendar);
        listButton.addEventListener('click', switchToList);
    }

    // inicjalizacja widoku
    if (calendarWidget && listContainer) {
        initializeView();
    }
});