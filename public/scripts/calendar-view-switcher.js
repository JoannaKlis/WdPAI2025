const serverEvents = window.serverEvents || [];
const calendarTab = document.getElementById('calendar-tab');
const listTab = document.getElementById('list-tab');
const calendarView = document.getElementById('calendar-view');
const listView = document.getElementById('list-view-container');

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