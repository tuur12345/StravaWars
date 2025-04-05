document.addEventListener("DOMContentLoaded", function () {
    const activities = document.querySelectorAll('.activity-map');
    activities.forEach((activityMap) => {
        initializeActivityMap(activityMap)
    });
});
