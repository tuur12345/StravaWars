//document.addEventListener("DOMContentLoaded", function () {
    const calendar = document.getElementById('calendar');
    const date = new Date();
    const year = date.getFullYear();
    const month = date.getMonth();

    const monthNames = [
        "January", "February", "March", "April", "May", "June",
        "July", "August", "September", "October", "November", "December"
    ];

    // Get first day and number of days
    const firstDay = new Date(year, month, 1).getDay(); // Sunday = 0
    const daysInMonth = new Date(year, month + 1, 0).getDate();

    // Start building HTML
    let calendarHTML = `<div class="calendar-header">${monthNames[month]} ${year}</div>`;
    calendarHTML += `<div class="calendar-grid">`;

    // Weekday labels
    const weekdays = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
    weekdays.forEach(day => {
        calendarHTML += `<div class="calendar-day-label">${day}</div>`;
    });

    // Empty slots before the 1st
    for (let i = 0; i < firstDay; i++) {
        calendarHTML += `<div class="calendar-day empty"></div>`;
    }

    // Fill the days
    for (let i = 1; i <= daysInMonth; i++) {
        calendarHTML += `<div class="calendar-day">${i}</div>`;
    }

    calendarHTML += `</div>`;
    calendar.innerHTML = calendarHTML;
//});
